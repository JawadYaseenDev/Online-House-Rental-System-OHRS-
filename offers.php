<?php
require_once 'includes/init.php';
$page_title = 'Special Offers';

$offers = db()->query(
    "SELECT o.*, h.title AS house_title, h.id AS house_id_ref
     FROM offers o
     LEFT JOIN houses h ON h.id = o.house_id
     WHERE o.status = 'active' AND o.end_date >= CURDATE()
     ORDER BY o.end_date ASC"
)->fetchAll();

include_once 'includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Special Offers</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">Special Offers</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php if (empty($offers)): ?>
      <div class="empty-state">
        <span class="icon"><i class="bi bi-tag"></i></span>
        <h5>No active offers at the moment</h5>
        <p>Check back soon for exciting deals on our properties.</p>
        <a href="houses.php" class="btn btn-primary mt-2">Browse Houses</a>
      </div>
    <?php else: ?>
      <div class="text-center mb-5">
        <p class="section-tag">Promotions</p>
        <h2 class="section-title">Current Offers &amp; Discounts</h2>
        <p class="section-sub mx-auto">Grab these limited-time deals before they expire.</p>
      </div>
      <div class="row g-4">
        <?php foreach ($offers as $i => $o): ?>
          <div class="col-md-6 col-lg-4">
            <div class="offer-card <?= $i % 3 === 1 ? 'green-card' : '' ?>">
              <div class="offer-bg-shape"></div>
              <div style="position:relative;z-index:1;">
                <div class="disc"><?= number_format($o['discount_pct'], 0) ?><span>% OFF</span></div>
                <h4 style="color:#fff;font-weight:800;margin:.75rem 0 .4rem;"><?= e($o['title']) ?></h4>
                <?php if ($o['description']): ?>
                  <p style="font-size:.875rem;color:rgba(255,255,255,.82);margin:0 0 .75rem;line-height:1.6;"><?= e($o['description']) ?></p>
                <?php endif; ?>
                <?php if ($o['house_title']): ?>
                  <div style="font-size:.8rem;color:rgba(255,255,255,.7);margin-bottom:.5rem;">
                    <i class="bi bi-house me-1"></i><?= e($o['house_title']) ?>
                  </div>
                <?php endif; ?>
                <div style="font-size:.78rem;color:rgba(255,255,255,.65);margin-bottom:1rem;">
                  <i class="bi bi-calendar me-1"></i>
                  <?= date('d M Y', strtotime($o['start_date'])) ?> —
                  <?= date('d M Y', strtotime($o['end_date'])) ?>
                </div>
                <?php if ($o['house_id_ref']): ?>
                  <a href="house-detail.php?id=<?= (int)$o['house_id_ref'] ?>"
                     class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);">
                    View Property <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                <?php else: ?>
                  <a href="houses.php" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);">
                    Browse All <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
