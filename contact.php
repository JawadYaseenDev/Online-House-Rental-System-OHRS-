<?php
require_once 'includes/init.php';
$page_title = 'Contact Us';
include_once 'includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Contact Us</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">Contact</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>
    <div class="row g-5">

      <!-- Info -->
      <div class="col-lg-4">
        <p class="section-tag">Get In Touch</p>
        <h2 class="section-title mb-3">We'd Love to Hear From You</h2>
        <p style="color:var(--neutral-500);font-size:.9rem;line-height:1.8;margin-bottom:2rem;">
          Have questions about a property, your reservation, or anything else?
          Our team typically responds within one business day.
        </p>

        <div class="contact-info-item">
          <div class="icon-box"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <div style="font-weight:700;font-size:.9rem;margin-bottom:.2rem;">Office Address</div>
            <div style="font-size:.875rem;color:var(--neutral-500);">123 Gulberg III, Lahore, Pakistan</div>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="icon-box"><i class="bi bi-telephone-fill"></i></div>
          <div>
            <div style="font-weight:700;font-size:.9rem;margin-bottom:.2rem;">Phone</div>
            <div style="font-size:.875rem;color:var(--neutral-500);">+92 300 000 0000</div>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="icon-box"><i class="bi bi-envelope-fill"></i></div>
          <div>
            <div style="font-weight:700;font-size:.9rem;margin-bottom:.2rem;">Email</div>
            <div style="font-size:.875rem;color:var(--neutral-500);">info@ohrs.com</div>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="icon-box"><i class="bi bi-clock-fill"></i></div>
          <div>
            <div style="font-weight:700;font-size:.9rem;margin-bottom:.2rem;">Working Hours</div>
            <div style="font-size:.875rem;color:var(--neutral-500);">Monday–Saturday: 9:00 AM – 6:00 PM</div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="col-lg-8">
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-xl);padding:2rem;box-shadow:var(--shadow-sm);">
          <h5 style="font-weight:700;margin-bottom:1.5rem;">Send us a Message</h5>
          <form action="actions/contact.php" method="POST" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="c_name">Your Name <span class="text-danger">*</span></label>
                <input type="text" id="c_name" name="name" class="form-control" required
                       placeholder="Ahmed Khan" maxlength="100">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="c_email">Email <span class="text-danger">*</span></label>
                <input type="email" id="c_email" name="email" class="form-control" required
                       placeholder="you@example.com">
              </div>
              <div class="col-12">
                <label class="form-label" for="c_subject">Subject <span class="text-danger">*</span></label>
                <input type="text" id="c_subject" name="subject" class="form-control" required
                       placeholder="e.g. Question about DHA Villa" maxlength="200">
              </div>
              <div class="col-12">
                <label class="form-label" for="c_message">Message <span class="text-danger">*</span></label>
                <textarea id="c_message" name="message" class="form-control" rows="6" required
                          placeholder="Tell us how we can help…"></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-send me-2"></i>Send Message
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
