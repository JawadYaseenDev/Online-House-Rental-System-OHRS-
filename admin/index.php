<?php
$admin_title = 'Dashboard';
require_once '../includes/admin-header.php';

// KPIs
$kpis = db()->query(
    "SELECT
       (SELECT COUNT(*) FROM houses WHERE status != 'inactive') AS total_houses,
       (SELECT COUNT(*) FROM houses WHERE status = 'available') AS available,
       (SELECT COUNT(*) FROM houses WHERE status = 'occupied') AS occupied,
       (SELECT COUNT(*) FROM houses WHERE status = 'pending') AS pending_houses,
       (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers,
       (SELECT COUNT(*) FROM users WHERE role = 'owner' AND status = 'inactive') AS pending_owners,
       (SELECT COUNT(*) FROM reservations WHERE status = 'pending') AS pending_res,
       (SELECT COUNT(*) FROM reservations) AS total_res,
       (SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'paid') AS revenue,
       (SELECT COUNT(*) FROM contact_messages WHERE is_read = 0) AS unread_msgs"
)->fetch();

// Monthly revenue for chart (last 6 months)
$revenue_data = db()->query(
    "SELECT DATE_FORMAT(created_at,'%b') AS month,
            COALESCE(SUM(amount),0) AS total
     FROM payments
     WHERE status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY YEAR(created_at), MONTH(created_at)
     ORDER BY created_at ASC"
)->fetchAll();

$chart_labels  = json_encode(array_column($revenue_data, 'month'));
$chart_values  = json_encode(array_column($revenue_data, 'total'));

// House status distribution for doughnut
$h_status = db()->query(
    "SELECT status, COUNT(*) AS cnt FROM houses GROUP BY status"
)->fetchAll();
$hs_labels = json_encode(array_column($h_status, 'status'));
$hs_counts = json_encode(array_column($h_status, 'cnt'));

// Recent reservations
$recent_res = db()->query(
    "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS tenant, h.title AS house
     FROM reservations r
     JOIN users u ON u.id = r.user_id
     JOIN houses h ON h.id = r.house_id
     ORDER BY r.created_at DESC LIMIT 8"
)->fetchAll();
?>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
  <?php
  $cards = [
    ['bi-house-fill','Total Properties',$kpis['total_houses'],'background:#eff6ff;color:#1a56db','Up from last month','up'],
    ['bi-check-circle-fill','Available Houses',$kpis['available'],'background:#ecfdf5;color:#0e9f6e','Ready to rent','up'],
    ['bi-people-fill','Customers',$kpis['customers'],'background:#fdf4ff;color:#7c3aed','Registered users','up'],
    ['bi-cash-stack','Total Revenue','Rs. '.number_format($kpis['revenue'],0),'background:#fffbeb;color:#d97706','From paid invoices','up'],
  ];
  foreach ($cards as [$icon,$lbl,$val,$bg,$sub,$diff]):
  ?>
    <div class="col-6 col-md-3">
      <div class="kpi-card">
        <div class="kpi-icon" style="<?= $bg ?>;"><i class="bi <?= $icon ?>" style="font-size:1.5rem;"></i></div>
        <div>
          <div class="num"><?= e((string)$val) ?></div>
          <div class="lbl"><?= $lbl ?></div>
          <div class="diff diff-up"><i class="bi bi-arrow-up-right"></i><?= $sub ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Alert: Pending reservations -->
<?php if ($kpis['pending_res'] > 0): ?>
  <div class="alert alert-warning d-flex align-items-center gap-2 mb-3 auto-dismiss">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong><?= $kpis['pending_res'] ?> reservation(s)</strong> are awaiting your approval.
    <a href="reservations.php" class="ms-auto btn btn-sm btn-warning">Review Now</a>
  </div>
<?php endif; ?>

<?php if ($kpis['pending_owners'] > 0): ?>
  <div class="alert alert-info d-flex align-items-center gap-2 mb-3 auto-dismiss">
    <i class="bi bi-person-badge-fill"></i>
    <strong><?= $kpis['pending_owners'] ?> owner account(s)</strong> are waiting for your approval.
    <a href="users.php" class="ms-auto btn btn-sm btn-info text-white">Review Users</a>
  </div>
<?php endif; ?>

<?php if ($kpis['pending_houses'] > 0): ?>
  <div class="alert alert-secondary d-flex align-items-center gap-2 mb-3 auto-dismiss">
    <i class="bi bi-house-exclamation-fill"></i>
    <strong><?= $kpis['pending_houses'] ?> house listing(s)</strong> are pending your approval.
    <a href="approvals.php" class="ms-auto btn btn-sm btn-secondary">Review Houses</a>
  </div>
<?php endif; ?>

<!-- Charts + Recent Reservations -->
<div class="row g-4 mb-4">

  <div class="col-lg-8">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5>Revenue (Last 6 Months)</h5>
      </div>
      <div class="admin-card-body">
        <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="admin-card h-100">
      <div class="admin-card-header"><h5>Property Status</h5></div>
      <div class="admin-card-body d-flex align-items-center justify-content-center" style="min-height:200px;">
        <div class="chart-wrap" style="height:220px;width:100%;"><canvas id="statusChart"></canvas></div>
      </div>
    </div>
  </div>

</div>

<!-- Recent Reservations Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h5>Recent Reservations</h5>
    <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All</a>
  </div>
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr>
          <th>#</th><th>Tenant</th><th>Property</th><th>Move-in</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_res as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= e($r['tenant']) ?></td>
            <td><?= e($r['house']) ?></td>
            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td>
              <a href="reservations.php" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:.2rem .6rem;">Manage</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
createLineChart('revenueChart', <?= $chart_labels ?>, <?= $chart_values ?>, 'Revenue (Rs.)', '#1a56db');
createDoughnutChart('statusChart',
  <?= $hs_labels ?>,
  <?= $hs_counts ?>,
  ['#0e9f6e','#f59e0b','#e02424','#9ca3af']
);
</script>

<?php require_once '../includes/admin-footer.php'; ?>
