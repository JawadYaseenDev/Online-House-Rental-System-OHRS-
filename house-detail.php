<?php
require_once 'includes/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('houses.php');

$house = db()->prepare(
    "SELECT * FROM houses WHERE id = :id AND status != 'inactive'"
);
$house->execute([':id' => $id]);
$house = $house->fetch();
if (!$house) redirect('houses.php');

// Images
$images = db()->prepare("SELECT * FROM house_images WHERE house_id = :id ORDER BY is_primary DESC, sort_order ASC");
$images->execute([':id' => $id]);
$images = $images->fetchAll();

// Active offer for this house
$offer = db()->prepare(
    "SELECT * FROM offers WHERE (house_id = :id OR house_id IS NULL) AND status = 'active' AND end_date >= CURDATE() LIMIT 1"
);
$offer->execute([':id' => $id]);
$offer = $offer->fetch();

// Reviews
$reviews = db()->prepare(
    "SELECT f.*, CONCAT(u.first_name,' ',u.last_name) AS name
     FROM feedback f JOIN users u ON u.id = f.user_id
     WHERE f.house_id = :id ORDER BY f.created_at DESC LIMIT 10"
);
$reviews->execute([':id' => $id]);
$reviews = $reviews->fetchAll();
$avg_rating = count($reviews) > 0 ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;

$amenities = json_decode($house['amenities'] ?? '[]', true) ?: [];

$page_title = $house['title'];
$page_meta  = substr(strip_tags($house['description'] ?? ''), 0, 155);

include_once 'includes/header.php';
?>

<!-- ── Page Header ───────────────────────────────────────── -->
<div class="page-header">
  <div class="container">
    <h1><?= e($house['title']) ?></h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="houses.php">Houses</a></li>
      <li class="breadcrumb-item active"><?= e($house['title']) ?></li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>

    <div class="row g-5">

      <!-- ── Left: Gallery + Details ─────────────────────── -->
      <div class="col-lg-8">

        <!-- Gallery -->
        <?php if (!empty($images)): ?>
          <div class="detail-gallery-main mb-2">
            <a href="assets/uploads/houses/<?= e($images[0]['image_path']) ?>"
               class="glightbox" data-gallery="house-gallery">
              <img src="assets/uploads/houses/<?= e($images[0]['image_path']) ?>"
                   alt="<?= e($house['title']) ?>">
            </a>
          </div>
          <?php if (count($images) > 1): ?>
            <div class="detail-gallery-thumbs">
              <?php foreach (array_slice($images, 1) as $img): ?>
                <a href="assets/uploads/houses/<?= e($img['image_path']) ?>"
                   class="glightbox" data-gallery="house-gallery">
                  <img src="assets/uploads/houses/<?= e($img['image_path']) ?>" alt="">
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="detail-gallery-main">
            <img src="assets/img/house-placeholder.jpg" alt="No image available">
          </div>
        <?php endif; ?>

        <!-- Meta grid -->
        <div class="detail-meta-row mt-4">
          <div class="detail-meta-item">
            <div class="val"><?= (int)$house['bedrooms'] ?></div>
            <div class="key"><i class="bi bi-door-open me-1"></i>Bedrooms</div>
          </div>
          <div class="detail-meta-item">
            <div class="val"><?= (int)$house['bathrooms'] ?></div>
            <div class="key"><i class="bi bi-droplet me-1"></i>Bathrooms</div>
          </div>
          <div class="detail-meta-item">
            <div class="val"><?= (int)$house['capacity'] ?></div>
            <div class="key"><i class="bi bi-people me-1"></i>Capacity</div>
          </div>
          <div class="detail-meta-item">
            <div class="val"><?= $house['area'] ? number_format($house['area'],0) : '—' ?></div>
            <div class="key"><i class="bi bi-aspect-ratio me-1"></i>Sq Ft</div>
          </div>
          <div class="detail-meta-item">
            <div class="val"><?= $avg_rating > 0 ? $avg_rating : '—' ?></div>
            <div class="key"><i class="bi bi-star me-1"></i>Rating</div>
          </div>
        </div>

        <!-- Description -->
        <h4 class="mt-4 mb-2">About This Property</h4>
        <p style="color:var(--neutral-600);line-height:1.8;"><?= nl2br(e($house['description'] ?? '')) ?></p>

        <!-- Location -->
        <h5 class="mt-4 mb-2">Location</h5>
        <p style="color:var(--neutral-600);">
          <i class="bi bi-geo-alt-fill text-primary me-1"></i>
          <?= e($house['address']) ?>
        </p>

        <!-- Amenities -->
        <?php if (!empty($amenities)): ?>
          <h5 class="mt-4 mb-2">Amenities</h5>
          <div>
            <?php foreach ($amenities as $a): ?>
              <span class="amenity-chip"><i class="bi bi-check-circle-fill"></i><?= e($a) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Offer badge -->
        <?php if ($offer): ?>
          <div class="alert alert-success mt-4 d-flex align-items-center gap-2">
            <i class="bi bi-tag-fill fs-5"></i>
            <div>
              <strong><?= e($offer['title']) ?></strong> —
              <?= number_format($offer['discount_pct'], 0) ?>% off!
              Expires <?= date('d M Y', strtotime($offer['end_date'])) ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Reviews -->
        <?php if (!empty($reviews)): ?>
          <h5 class="mt-5 mb-3">Tenant Reviews (<?= count($reviews) ?>)</h5>
          <?php foreach ($reviews as $r): ?>
            <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--neutral-100);">
              <div style="width:40px;height:40px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);flex-shrink:0;">
                <?= strtoupper(substr($r['name'],0,1)) ?>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <strong style="font-size:.9rem;"><?= e($r['name']) ?></strong>
                  <span style="color:#f59e0b;font-size:.8rem;">
                    <?= str_repeat('★', (int)$r['rating']) ?>
                  </span>
                  <span style="font-size:.75rem;color:var(--neutral-400);"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                </div>
                <?php if ($r['review']): ?>
                  <p style="font-size:.9rem;margin:0 0 .25rem;font-weight:600;"><?= e($r['review']) ?></p>
                <?php endif; ?>
                <?php if ($r['comment']): ?>
                  <p style="font-size:.875rem;color:var(--neutral-600);margin:0;"><?= e($r['comment']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

      <!-- ── Right: Sidebar ──────────────────────────────── -->
      <div class="col-lg-4">
        <div class="sidebar-card">
          <div class="sidebar-price"><?= fmt_money($house['rent']) ?><span>/month</span></div>
          <div class="mt-2 mb-3"><?= status_badge($house['status']) ?></div>

          <ul style="list-style:none;padding:0;margin:0 0 1.25rem;font-size:.875rem;color:var(--neutral-600);">
            <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><?= e($house['location']) ?></li>
            <li class="mb-2"><i class="bi bi-people text-primary me-2"></i>Up to <?= (int)$house['capacity'] ?> people</li>
            <li class="mb-2"><i class="bi bi-door-open text-primary me-2"></i><?= (int)$house['bedrooms'] ?> Bedrooms</li>
            <li class="mb-2"><i class="bi bi-droplet text-primary me-2"></i><?= (int)$house['bathrooms'] ?> Bathrooms</li>
            <?php if ($house['area']): ?>
              <li><i class="bi bi-aspect-ratio text-primary me-2"></i><?= number_format($house['area'],0) ?> sq ft</li>
            <?php endif; ?>
          </ul>

          <?php if ($house['status'] === 'available'): ?>
            <?php if (is_logged_in() && is_customer()): ?>
              <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#reserveModal">
                <i class="bi bi-calendar-plus me-2"></i>Reserve This House
              </button>
            <?php else: ?>
              <a href="login.php?redirect=house-detail.php?id=<?= $id ?>" class="btn btn-primary w-100 mb-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login to Reserve
              </a>
            <?php endif; ?>
          <?php else: ?>
            <button class="btn btn-secondary w-100 mb-2" disabled>
              <?= ucfirst($house['status']) ?> — Not Available
            </button>
          <?php endif; ?>

          <a href="contact.php" class="btn btn-outline-primary w-100">
            <i class="bi bi-chat me-2"></i>Enquire Now
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── Reserve Modal ──────────────────────────────────────── -->
<?php if (is_logged_in() && is_customer() && $house['status'] === 'available'): ?>
<div class="modal fade" id="reserveModal" tabindex="-1" aria-labelledby="reserveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="actions/reserve.php" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="house_id" value="<?= $id ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="reserveModalLabel">
            <i class="bi bi-calendar-plus me-2 text-primary"></i>Reserve Property
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Move-in Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" class="form-control" required
                   min="<?= date('Y-m-d') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Expected Move-out Date (optional)</label>
            <input type="date" name="end_date" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes / Special Requests</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Any specific requirements…"></textarea>
          </div>
          <div class="alert alert-info" style="font-size:.82rem;">
            <i class="bi bi-info-circle me-1"></i>
            Your reservation will be <strong>Pending</strong> until the administrator approves it.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>Submit Reservation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
