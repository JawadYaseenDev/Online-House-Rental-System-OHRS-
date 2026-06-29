<?php
require_once '../includes/init.php';
require_login('../login.php');
if (!is_customer()) redirect('../admin/index.php');

$uid  = current_user()['id'];
$page_title = 'My Reservations';

// Cancel action
if (isset($_GET['cancel']) && csrf_check()) {
    $rid = (int)$_GET['cancel'];
    db()->prepare(
        "UPDATE reservations SET status = 'cancelled' WHERE id = :id AND user_id = :u AND status = 'pending'"
    )->execute([':id' => $rid, ':u' => $uid]);
    flash('success', 'Reservation cancelled.');
    redirect('reservations.php');
}

$reservations = db()->prepare(
    "SELECT r.*, h.title, h.location, h.rent, hi.image_path
     FROM reservations r
     JOIN houses h ON h.id = r.house_id
     LEFT JOIN house_images hi ON hi.house_id = h.id AND hi.is_primary = 1
     WHERE r.user_id = :u ORDER BY r.created_at DESC"
);
$reservations->execute([':u' => $uid]);
$reservations = $reservations->fetchAll();

include_once '../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>My Reservations</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Reservations</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>

    <?php if (empty($reservations)): ?>
      <div class="empty-state">
        <span class="icon"><i class="bi bi-calendar-x"></i></span>
        <h5>No reservations found</h5>
        <p>You haven't made any reservations yet.</p>
        <a href="../houses.php" class="btn btn-primary mt-2">Browse Houses</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($reservations as $r): ?>
          <div class="col-md-6 col-lg-4">
            <div class="house-card">
              <div class="house-card-img-wrap">
                <img src="<?= $r['image_path'] ? '../assets/uploads/houses/' . e($r['image_path']) : '../assets/img/house-placeholder.jpg' ?>"
                     alt="<?= e($r['title']) ?>" loading="lazy">
                <div class="house-card-status"><?= status_badge($r['status']) ?></div>
              </div>
              <div class="house-card-body">
                <div class="house-card-title"><?= e($r['title']) ?></div>
                <div class="house-card-location"><i class="bi bi-geo-alt"></i><?= e($r['location']) ?></div>
                <div style="font-size:.82rem;color:var(--neutral-600);margin:.5rem 0;">
                  <i class="bi bi-calendar me-1"></i>
                  Move-in: <?= date('d M Y', strtotime($r['start_date'])) ?>
                  <?php if ($r['end_date']): ?>
                    → <?= date('d M Y', strtotime($r['end_date'])) ?>
                  <?php endif; ?>
                </div>
                <div class="house-card-price"><?= fmt_money($r['rent']) ?><span>/mo</span></div>
                <?php if ($r['notes']): ?>
                  <p style="font-size:.78rem;color:var(--neutral-500);margin:.5rem 0 0;border-top:1px solid var(--neutral-100);padding-top:.5rem;">
                    <?= e($r['notes']) ?>
                  </p>
                <?php endif; ?>
                <div class="d-flex gap-2 mt-3">
                  <a href="../house-detail.php?id=<?= $r['house_id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-eye"></i> View
                  </a>
                  <?php if ($r['status'] === 'pending'): ?>
                    <a href="reservations.php?cancel=<?= $r['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                       class="btn btn-sm btn-outline-danger flex-fill confirm-delete">
                      <i class="bi bi-x"></i> Cancel
                    </a>
                  <?php endif; ?>
                  <?php if ($r['status'] === 'approved'): ?>
                    <a href="payments.php?reservation_id=<?= $r['id'] ?>" class="btn btn-sm btn-accent flex-fill">
                      <i class="bi bi-credit-card"></i> Pay
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include_once '../includes/footer.php'; ?>
