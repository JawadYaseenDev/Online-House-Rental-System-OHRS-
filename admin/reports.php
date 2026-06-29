<?php
$admin_title      = 'Reports';
$admin_breadcrumb = ['Reports' => null];
require_once '../includes/admin-header.php';

$period = $_GET['period'] ?? 'monthly';
$year   = (int)($_GET['year'] ?? date('Y'));
$month  = (int)($_GET['month'] ?? date('n'));
$qtr    = (int)($_GET['qtr'] ?? ceil(date('n') / 3));

// Date range based on period
switch ($period) {
    case 'monthly':
        $from = date("$year-$month-01");
        $to   = date('Y-m-t', strtotime($from));
        $label = date('F Y', strtotime($from));
        break;
    case 'quarterly':
        $qm_start = ($qtr - 1) * 3 + 1;
        $from     = "$year-" . str_pad($qm_start, 2, '0', STR_PAD_LEFT) . "-01";
        $to       = date('Y-m-t', strtotime("$year-" . str_pad($qm_start + 2, 2, '0', STR_PAD_LEFT) . "-01"));
        $label    = "Q$qtr $year";
        break;
    default: // yearly
        $from  = "$year-01-01";
        $to    = "$year-12-31";
        $label = "Year $year";
}

$db = db();

$report = [
    'total_houses'     => $db->query("SELECT COUNT(*) FROM houses WHERE status != 'inactive'")->fetchColumn(),
    'available'        => $db->query("SELECT COUNT(*) FROM houses WHERE status = 'available'")->fetchColumn(),
    'occupied'         => $db->query("SELECT COUNT(*) FROM houses WHERE status IN ('reserved','occupied')")->fetchColumn(),
    'new_reservations' => $db->prepare("SELECT COUNT(*) FROM reservations WHERE created_at BETWEEN :f AND :t") ->execute([':f'=>$from.' 00:00:00', ':t'=>$to.' 23:59:59']) || $db->prepare("SELECT COUNT(*) FROM reservations WHERE created_at BETWEEN :f AND :t")->execute([':f'=>$from.' 00:00:00', ':t'=>$to.' 23:59:59']),
    'revenue'          => $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND payment_date BETWEEN :f AND :t"),
    'new_users'        => $db->prepare("SELECT COUNT(*) FROM users WHERE role='customer' AND created_at BETWEEN :f AND :t"),
];

// Simpler approach
$stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE created_at BETWEEN :f AND :t");
$stmt->execute([':f'=>"$from 00:00:00", ':t'=>"$to 23:59:59"]);
$report['new_reservations'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND payment_date BETWEEN :f AND :t");
$stmt->execute([':f'=>$from, ':t'=>$to]);
$report['revenue'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role='customer' AND created_at BETWEEN :f AND :t");
$stmt->execute([':f'=>"$from 00:00:00", ':t'=>"$to 23:59:59"]);
$report['new_users'] = $stmt->fetchColumn();

// Top reserved houses
$top_houses = $db->prepare(
    "SELECT h.title, h.location, COUNT(*) AS reservations, COALESCE(SUM(p.amount),0) AS revenue
     FROM reservations r
     JOIN houses h ON h.id = r.house_id
     LEFT JOIN payments p ON p.reservation_id = r.id AND p.status = 'paid'
     WHERE r.created_at BETWEEN :f AND :t
     GROUP BY h.id ORDER BY reservations DESC LIMIT 8"
);
$top_houses->execute([':f'=>"$from 00:00:00", ':t'=>"$to 23:59:59"]);
$top_houses = $top_houses->fetchAll();
?>

<!-- Report Controls -->
<div class="admin-card mb-4">
  <div class="admin-card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Report Period</label>
        <select name="period" class="form-select">
          <option value="monthly"   <?= $period === 'monthly' ? 'selected' : '' ?>>Monthly</option>
          <option value="quarterly" <?= $period === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
          <option value="yearly"    <?= $period === 'yearly' ? 'selected' : '' ?>>Yearly</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Year</label>
        <select name="year" class="form-select">
          <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
            <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2" id="month-ctrl" <?= $period !== 'monthly' ? 'style="display:none"' : '' ?>>
        <label class="form-label">Month</label>
        <select name="month" class="form-select">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2" id="qtr-ctrl" <?= $period !== 'quarterly' ? 'style="display:none"' : '' ?>>
        <label class="form-label">Quarter</label>
        <select name="qtr" class="form-select">
          <?php for ($q = 1; $q <= 4; $q++): ?>
            <option value="<?= $q ?>" <?= $qtr === $q ? 'selected' : '' ?>>Q<?= $q ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Generate</button>
      </div>
      <div class="col-md-1">
        <button type="button" id="print-report" class="btn btn-outline-secondary w-100" data-bs-toggle="tooltip" title="Print">
          <i class="bi bi-printer"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Report Output -->
<div id="report-output">
  <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;box-shadow:var(--shadow-sm);">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
      <div>
        <h3 style="font-weight:800;margin:0;">OHRS — <?= ucfirst($period) ?> Report</h3>
        <p style="color:var(--neutral-500);margin:0;font-size:.9rem;"><?= $label ?> &nbsp;·&nbsp; Generated: <?= date('d M Y, H:i') ?></p>
      </div>
      <div style="font-size:1.2rem;font-weight:800;color:var(--primary);">
        <i class="bi bi-house-heart-fill me-1"></i>OHRS
      </div>
    </div>

    <div class="row g-3 mb-4">
      <?php
      $cells = [
        ['Total Houses', $report['total_houses'], 'bi-house'],
        ['Available', $report['available'], 'bi-check-circle'],
        ['Occupied/Reserved', $report['occupied'], 'bi-people'],
        ['New Reservations', $report['new_reservations'], 'bi-calendar-plus'],
        ['New Customers', $report['new_users'], 'bi-person-plus'],
        ['Revenue', 'Rs. '.number_format($report['revenue'],0), 'bi-cash'],
      ];
      foreach ($cells as [$l,$v,$i]):
      ?>
        <div class="col-6 col-md-2">
          <div style="background:var(--neutral-50);border:1px solid var(--neutral-200);border-radius:var(--radius);padding:1rem;text-align:center;">
            <i class="bi <?= $i ?> text-primary" style="font-size:1.2rem;margin-bottom:.4rem;display:block;"></i>
            <div style="font-size:1.1rem;font-weight:800;color:var(--neutral-900);"><?= $v ?></div>
            <div style="font-size:.72rem;color:var(--neutral-500);margin-top:.1rem;"><?= $l ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($top_houses)): ?>
      <h5 style="font-weight:700;margin-bottom:1rem;">Top Properties by Reservations</h5>
      <table class="table ohrs-table">
        <thead>
          <tr><th>#</th><th>Property</th><th>Location</th><th>Reservations</th><th>Revenue</th></tr>
        </thead>
        <tbody>
          <?php foreach ($top_houses as $i => $h): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= e($h['title']) ?></td>
              <td><?= e($h['location']) ?></td>
              <td><?= $h['reservations'] ?></td>
              <td><?= fmt_money($h['revenue']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>
</div>

<script>
document.querySelector('[name=period]').addEventListener('change', function(){
  document.getElementById('month-ctrl').style.display = this.value === 'monthly'   ? '' : 'none';
  document.getElementById('qtr-ctrl').style.display   = this.value === 'quarterly' ? '' : 'none';
});
</script>

<?php require_once '../includes/admin-footer.php'; ?>
