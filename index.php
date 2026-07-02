<?php
require_once 'includes/init.php';
$page_title = 'Find Your Perfect Home';
$page_meta  = 'Browse, search and rent houses online in Pakistan. Affordable rentals in Lahore, Karachi, Islamabad and more.';

// Fetch featured available houses
$featured = db()->query(
    "SELECT h.*, hi.image_path
     FROM houses h
     LEFT JOIN house_images hi ON hi.house_id = h.id AND hi.is_primary = 1
     WHERE h.status = 'available'
     ORDER BY h.created_at DESC LIMIT 6"
)->fetchAll();

// Stats
$stats = db()->query(
    "SELECT
       (SELECT COUNT(*) FROM houses WHERE status != 'inactive') AS total_houses,
       (SELECT COUNT(*) FROM houses WHERE status = 'available') AS available,
       (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers,
       (SELECT COUNT(*) FROM reservations WHERE status = 'completed') AS completed"
)->fetch();

// Active offers
$offers = active_offers(db());

// Recent feedback
$reviews = db()->query(
    "SELECT f.*, CONCAT(u.first_name,' ',u.last_name) AS name, h.title AS house_title
     FROM feedback f
     JOIN users u ON u.id = f.user_id
     LEFT JOIN houses h ON h.id = f.house_id
     ORDER BY f.created_at DESC LIMIT 3"
)->fetchAll();

include_once 'includes/header.php';
?>

<!-- ── Hero ─────────────────────────────────────────────────── -->
<section class="hero">
  <div class="container position-relative">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <p class="section-tag mb-3" style="color:rgba(255,255,255,.75);">
          <i class="bi bi-shield-check me-1"></i> Trusted Rental Platform
        </p>
        <h1 class="hero-title">Welcome to Your<br>Next Chapter</h1>
        <p class="hero-sub">
          The easiest way to find and rent your perfect property locally. Fast, secure, and hassle-free.
        </p>
        <div class="d-flex gap-3 mt-4 flex-wrap">
          <a href="houses.php" class="btn btn-accent btn-lg hover-bounce">
            <i class="bi bi-search me-2"></i>Browse Houses
          </a>
          <a href="register.php" class="btn btn-outline-light btn-lg">
            Get Started
          </a>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="hero-search-box">
          <p style="font-size:.85rem;font-weight:700;color:var(--neutral-700);margin-bottom:1rem;">
            <i class="bi bi-search me-1 text-primary"></i> Quick Property Search
          </p>
          <form action="houses.php" method="GET">
            <div class="row g-3">
              <div class="col-12">
                <input type="text" name="location" class="form-control" placeholder="Location (e.g. Lahore, Karachi…)">
              </div>
              <div class="col-6">
                <input type="number" name="min_rent" class="form-control" placeholder="Min Rent (Rs.)">
              </div>
              <div class="col-6">
                <input type="number" name="max_rent" class="form-control" placeholder="Max Rent (Rs.)">
              </div>
              <div class="col-6">
                <select name="bedrooms" class="form-select">
                  <option value="">Any Bedrooms</option>
                  <option value="1">1 Bedroom</option>
                  <option value="2">2 Bedrooms</option>
                  <option value="3">3 Bedrooms</option>
                  <option value="4">4+ Bedrooms</option>
                </select>
              </div>
              <div class="col-6">
                <select name="capacity" class="form-select">
                  <option value="">Any Capacity</option>
                  <option value="1">1 Person</option>
                  <option value="2">2 People</option>
                  <option value="4">4 People</option>
                  <option value="6">6+ People</option>
                </select>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-search me-2"></i>Search Properties
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── Stats Strip ────────────────────────────────────────── -->
<section class="stats-strip">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3 stat-item">
        <div class="num" data-count="<?= $stats['total_houses'] ?>">0</div>
        <div class="lbl">Total Properties</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="num" data-count="<?= $stats['available'] ?>">0</div>
        <div class="lbl">Available Now</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="num" data-count="<?= $stats['customers'] ?>">0</div>
        <div class="lbl">Happy Tenants</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="num" data-count="<?= $stats['completed'] ?>">0</div>
        <div class="lbl">Rentals Completed</div>
      </div>
    </div>
  </div>
</section>

<!-- ── Featured Houses ────────────────────────────────────── -->
<section class="py-5 mt-2">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <p class="section-tag">Properties</p>
        <h2 class="section-title mb-1">Featured Houses</h2>
        <p class="section-sub">Handpicked available properties ready for immediate reservation.</p>
      </div>
      <a href="houses.php" class="btn btn-outline-primary">
        View All <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4" id="featured-grid">
      <?php if (empty($featured)): ?>
        <div class="col-12">
          <div class="empty-state">
            <span class="icon"><i class="bi bi-house"></i></span>
            <h5>No properties listed yet</h5>
            <p>Check back soon or contact the admin.</p>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($featured as $h): ?>
          <div class="col-sm-6 col-lg-4 card-appear">
            <div class="house-card">
              <div class="house-card-img-wrap">
                <img src="<?= $h['image_path'] ? 'assets/uploads/houses/' . e($h['image_path']) : 'assets/img/house-placeholder.jpg' ?>"
                     alt="<?= e($h['title']) ?>" loading="lazy">
                <div class="house-card-status"><?= status_badge($h['status']) ?></div>
              </div>
              <div class="house-card-body">
                <div class="house-card-price"><?= fmt_money($h['rent']) ?><span>/month</span></div>
                <div class="house-card-title"><?= e($h['title']) ?></div>
                <div class="house-card-location"><i class="bi bi-geo-alt"></i><?= e($h['location']) ?></div>
                <div class="house-card-meta">
                  <span><i class="bi bi-people"></i><?= (int)$h['capacity'] ?> guests</span>
                  <span><i class="bi bi-door-open"></i><?= (int)$h['bedrooms'] ?> bed</span>
                  <span><i class="bi bi-droplet"></i><?= (int)$h['bathrooms'] ?> bath</span>
                </div>
                <a href="house-detail.php?id=<?= (int)$h['id'] ?>" class="btn btn-primary w-100 mt-3">View Details</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── Special Offers ─────────────────────────────────────── -->
<?php if (!empty($offers)): ?>
<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <p class="section-tag">Promotions</p>
        <h2 class="section-title mb-1">Special Offers</h2>
        <p class="section-sub">Limited-time deals on selected properties.</p>
      </div>
      <a href="offers.php" class="btn btn-outline-primary">All Offers <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach (array_slice($offers, 0, 3) as $i => $offer): ?>
        <div class="col-md-4">
          <div class="offer-card <?= $i === 1 ? 'green-card' : '' ?>">
            <div class="offer-bg-shape"></div>
            <p class="disc"><?= number_format($offer['discount_pct'], 0) ?><span>% OFF</span></p>
            <h5 style="color:#fff;font-weight:700;margin:.5rem 0 .25rem;"><?= e($offer['title']) ?></h5>
            <?php if ($offer['house_title']): ?>
              <p style="font-size:.8rem;color:rgba(255,255,255,.75);margin:0 0 .5rem;">
                <?= e($offer['house_title']) ?>
              </p>
            <?php endif; ?>
            <p style="font-size:.78rem;color:rgba(255,255,255,.65);margin:0;">
              Expires: <?= date('d M Y', strtotime($offer['end_date'])) ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── How It Works ───────────────────────────────────────── -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-tag">Process</p>
      <h2 class="section-title">How OHRS Works</h2>
      <p class="section-sub mx-auto">Get into your new home in four simple steps.</p>
    </div>
    <div class="row g-4 text-center">
      <?php
      $steps = [
        ['bi-search','Search','Browse our curated listings by location, price, and size.'],
        ['bi-house-check','Choose','View full details, images, and amenities of any property.'],
        ['bi-calendar-plus','Reserve','Submit a reservation request in minutes.'],
        ['bi-check-circle','Move In','Admin approves, you pay, and you move in!'],
      ];
      foreach ($steps as $i => [$icon, $title, $desc]):
      ?>
        <div class="col-6 col-md-3">
          <div style="width:56px;height:56px;background:var(--primary-light);border-radius:var(--radius-lg);
                      display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;color:var(--primary);">
            <i class="bi <?= $icon ?>"></i>
          </div>
          <div style="font-size:.72rem;font-weight:700;color:var(--primary);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.35rem;">
            Step <?= $i+1 ?>
          </div>
          <h6 style="font-weight:700;"><?= $title ?></h6>
          <p style="font-size:.85rem;color:var(--neutral-500);line-height:1.6;"><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Testimonials ───────────────────────────────────────── -->
<?php if (!empty($reviews)): ?>
<section class="py-5" style="background:var(--neutral-50);">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-tag">Reviews</p>
      <h2 class="section-title">What Our Tenants Say</h2>
    </div>
    <div class="row g-4">
      <?php foreach ($reviews as $r): ?>
        <div class="col-md-4">
          <div class="testimonial-card">
            <div class="stars">
              <?= str_repeat('<i class="bi bi-star-fill"></i>', (int)$r['rating']) ?>
            </div>
            <p class="quote">"<?= e($r['review'] ?: $r['comment']) ?>"</p>
            <div class="author"><?= e($r['name']) ?></div>
            <?php if ($r['house_title']): ?>
              <div class="loc"><i class="bi bi-house me-1"></i><?= e($r['house_title']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CTA Banner ─────────────────────────────────────────── -->
<section style="background:linear-gradient(135deg,#1a3a6b 0%,#1a56db 100%);padding:70px 0;color:#fff;text-align:center;">
  <div class="container">
    <h2 style="font-size:2rem;font-weight:800;margin-bottom:.75rem;">Ready to Find Your New Home?</h2>
    <p style="opacity:.85;max-width:480px;margin:0 auto 2rem;font-size:1rem;">
      Join hundreds of satisfied tenants. Register for free and start browsing today.
    </p>
    <a href="register.php" class="btn btn-accent btn-lg me-2 hover-bounce">
      <i class="bi bi-person-plus me-2"></i>Create Account
    </a>
    <a href="houses.php" class="btn btn-outline-light btn-lg">Browse Houses</a>
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
