<?php
require_once 'includes/init.php';
$page_title = 'About Us';
include_once 'includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>About OHRS</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">About Us</li>
    </ol></nav>
  </div>
</div>

<!-- Mission -->
<section class="py-5">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <p class="section-tag">Our Story</p>
        <h2 class="section-title">Redefining How Pakistan Rents Homes</h2>
        <p style="color:var(--neutral-600);line-height:1.8;margin-top:1rem;">
          OHRS was built to solve a fundamental problem in Pakistan's rental market —
          lack of transparency, trust, and convenience. We bring landlords and tenants
          together on a single, secure, digital platform.
        </p>
        <p style="color:var(--neutral-600);line-height:1.8;">
          Whether you're a family searching for your next home or a professional looking
          for a comfortable apartment, OHRS makes the process simple, fast, and reliable.
        </p>
        <div class="row g-3 mt-2">
          <?php foreach (['500+ Properties Listed','1,200+ Happy Tenants','99% Satisfaction Rate','24/7 Support'] as $item): ?>
            <div class="col-6">
              <div style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;font-weight:600;color:var(--neutral-800);">
                <i class="bi bi-check-circle-fill text-primary"></i><?= $item ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div style="background:var(--primary-light);border-radius:var(--radius-xl);padding:3rem;text-align:center;">
          <i class="bi bi-house-heart-fill" style="font-size:6rem;color:var(--primary);opacity:.7;"></i>
          <h3 style="margin-top:1rem;color:var(--primary);">Trusted Since 2024</h3>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Values -->
<section class="py-5" style="background:var(--neutral-50);">
  <div class="container">
    <div class="text-center mb-5">
      <p class="section-tag">Values</p>
      <h2 class="section-title">What We Stand For</h2>
    </div>
    <div class="row g-4">
      <?php
      $values = [
        ['bi-shield-check','Trust & Transparency','Every listing is verified. Every transaction is secure. No hidden fees.'],
        ['bi-lightning-charge','Speed & Convenience','From search to reservation in minutes. Our platform is built for speed.'],
        ['bi-people','Community First','We serve both landlords and tenants equally, building long-term relationships.'],
        ['bi-headset','Dedicated Support','Our team is available to help with every step of your rental journey.'],
      ];
      foreach ($values as [$icon, $title, $desc]):
      ?>
        <div class="col-md-6 col-lg-3">
          <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.5rem;height:100%;box-shadow:var(--shadow-sm);text-align:center;">
            <div style="width:52px;height:52px;background:var(--primary-light);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.4rem;color:var(--primary);">
              <i class="bi <?= $icon ?>"></i>
            </div>
            <h6 style="font-weight:700;"><?= $title ?></h6>
            <p style="font-size:.875rem;color:var(--neutral-500);line-height:1.7;margin:0;"><?= $desc ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-5 text-center">
  <div class="container">
    <h2 class="section-title mb-2">Join the OHRS Community</h2>
    <p class="section-sub mx-auto mb-4">Start your journey towards hassle-free renting today.</p>
    <a href="register.php" class="btn btn-primary btn-lg me-2">
      <i class="bi bi-person-plus me-2"></i>Register Free
    </a>
    <a href="contact.php" class="btn btn-outline-primary btn-lg">Contact Us</a>
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
