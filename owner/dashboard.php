<?php
$owner_title      = 'Dashboard';
$owner_breadcrumb = ['Dashboard' => null];
require_once '../includes/owner-header.php';

$uid = current_user()['id'];

// KPIs
$stats = db()->prepare(
    "SELECT
       (SELECT COUNT(*) FROM houses WHERE owner_id = :u1) AS total_houses,
       (SELECT COUNT(*) FROM houses WHERE owner_id = :u2 AND status = 'pending') AS pending_houses,
       (SELECT COUNT(r.id) FROM reservations r JOIN houses h ON r.house_id = h.id WHERE h.owner_id = :u3) AS total_res,
       (SELECT COUNT(r.id) FROM reservations r JOIN houses h ON r.house_id = h.id WHERE h.owner_id = :u4 AND r.status = 'approved') AS active_res"
);
$stats->execute([':u1'=>$uid,':u2'=>$uid,':u3'=>$uid,':u4'=>$uid]);
$s = $stats->fetch();
?>

<div class="row g-3 mb-4">
  <?php
  $kpis = [
    ['bi-house', 'Total Properties', $s['total_houses'], 'primary'],
    ['bi-hourglass-split', 'Pending Approval', $s['pending_houses'], 'warning'],
    ['bi-calendar-check', 'Total Reservations', $s['total_res'], 'info'],
    ['bi-check-circle', 'Active Reservations', $s['active_res'], 'success'],
  ];
  foreach ($kpis as [$icon, $lbl, $val, $c]):
  ?>
    <div class="col-6 col-md-3">
      <div class="admin-card text-center" style="padding:1.5rem 1rem;">
        <div style="font-size:2rem;color:var(--<?= $c ?>);margin-bottom:.5rem;">
          <i class="bi <?= $icon ?>"></i>
        </div>
        <div style="font-size:1.5rem;font-weight:700;line-height:1;margin-bottom:.25rem;"><?= (int)$val ?></div>
        <div style="font-size:.8rem;color:var(--neutral-500);font-weight:500;text-transform:uppercase;letter-spacing:.5px;">
          <?= e($lbl) ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <h5 class="mb-0">Getting Started</h5>
  </div>
  <div class="admin-card-body">
    <p>Welcome to your Owner Dashboard! Here you can manage the properties you wish to list for rent.</p>
    <ul>
      <li>Click <strong>Add House</strong> to submit a new property.</li>
      <li>Submitted properties will be reviewed by an Administrator. Once approved, they will appear on the public site.</li>
      <li>You can track reservations made for your properties in the <strong>Reservations</strong> tab.</li>
    </ul>
    <a href="house-add.php" class="btn btn-primary mt-2"><i class="bi bi-plus-circle me-2"></i> Add Your First House</a>
  </div>
</div>

<?php require_once '../includes/owner-footer.php'; ?>
