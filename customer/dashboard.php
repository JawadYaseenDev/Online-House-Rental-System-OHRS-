<?php
require_once '../includes/init.php';
require_login('../login.php');
if (!is_customer()) redirect('../admin/index.php');

$uid = current_user()['id'];
$u   = current_user();

// KPIs
$stats = db()->prepare(
    "SELECT
       (SELECT COUNT(*) FROM reservations WHERE user_id = :u) AS total_res,
       (SELECT COUNT(*) FROM reservations WHERE user_id = :u2 AND status = 'approved') AS active_res,
       (SELECT COUNT(*) FROM payments WHERE user_id = :u3 AND status = 'paid') AS total_paid,
       (SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id = :u4 AND status = 'paid') AS total_amount"
);
$stats->execute([':u'=>$uid,':u2'=>$uid,':u3'=>$uid,':u4'=>$uid]);
$s = $stats->fetch();

// Recent reservations
$recent = db()->prepare(
    "SELECT r.*, h.title, h.location, h.rent
     FROM reservations r JOIN houses h ON h.id = r.house_id
     WHERE r.user_id = :u ORDER BY r.created_at DESC LIMIT 5"
);
$recent->execute([':u' => $uid]);
$recent = $recent->fetchAll();

$admin_title = 'My Dashboard';
$page_title  = 'Customer Dashboard';
include_once '../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Welcome, <?= e($u['name']) ?> 👋</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
      <?php
      $kpis = [
        ['bi-calendar-check','Total Reservations',$s['total_res'],'primary'],
        ['bi-check-circle','Active Reservations',$s['active_res'],'success'],
        ['bi-credit-card','Payments Made',$s['total_paid'],'info'],
        ['bi-cash','Total Paid','Rs. '.number_format($s['total_amount'],0),'warning'],
      ];
      foreach ($kpis as [$icon,$lbl,$val,$c]):
      ?>
        <div class="col-6 col-md-3">
          <div class="dash-kpi">
            <div class="icon-box" style="background:var(--<?= $c === 'primary' ? 'primary-light' : ($c === 'success' ? 'accent' : ($c === 'warning' ? 'warning' : 'primary-light')) ?>);<?= $c !== 'primary' && $c !== 'info' ? '' : '' ?>">
              <i class="bi <?= $icon ?>" style="color:var(--<?= $c === 'success' ? 'accent' : ($c === 'warning' ? 'warning' : 'primary') ?>);font-size:1.3rem;"></i>
            </div>
            <div class="num"><?= e((string)$val) ?></div>
            <div class="lbl"><?= e($lbl) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Quick links + Recent reservations -->
    <div class="row g-4">
      <div class="col-lg-3">
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);">
          <h6 style="font-weight:700;margin-bottom:1rem;">Quick Actions</h6>
          <div class="d-flex flex-column gap-2">
            <a href="../houses.php" class="btn btn-primary btn-sm w-100 text-start">
              <i class="bi bi-search me-2"></i>Browse Houses
            </a>
            <a href="reservations.php" class="btn btn-outline-primary btn-sm w-100 text-start">
              <i class="bi bi-calendar-check me-2"></i>My Reservations
            </a>
            <a href="payments.php" class="btn btn-outline-primary btn-sm w-100 text-start">
              <i class="bi bi-credit-card me-2"></i>My Payments
            </a>
            <a href="feedback.php" class="btn btn-outline-primary btn-sm w-100 text-start">
              <i class="bi bi-chat-left-dots me-2"></i>Give Feedback
            </a>
            <a href="profile.php" class="btn btn-outline-secondary btn-sm w-100 text-start">
              <i class="bi bi-person me-2"></i>Edit Profile
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-9">
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);overflow:hidden;">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--neutral-100);display:flex;align-items:center;justify-content:space-between;">
            <h6 style="margin:0;font-weight:700;">Recent Reservations</h6>
            <a href="reservations.php" style="font-size:.8rem;">View all</a>
          </div>
          <?php if (empty($recent)): ?>
            <div class="empty-state py-4">
              <span class="icon" style="font-size:2rem;"><i class="bi bi-calendar-x"></i></span>
              <p>No reservations yet. <a href="../houses.php">Browse houses</a> to get started.</p>
            </div>
          <?php else: ?>
            <table class="table ohrs-table mb-0">
              <thead>
                <tr>
                  <th>Property</th>
                  <th>Location</th>
                  <th>Move-in</th>
                  <th>Rent</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent as $r): ?>
                  <tr>
                    <td><a href="../house-detail.php?id=<?= $r['house_id'] ?>"><?= e($r['title']) ?></a></td>
                    <td><?= e($r['location']) ?></td>
                    <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
                    <td><?= fmt_money($r['rent']) ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include_once '../includes/footer.php'; ?>
