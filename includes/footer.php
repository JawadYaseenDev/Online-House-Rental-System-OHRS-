<?php
/**
 * Public site footer
 */
?>
</main>

<!-- ── Footer ─────────────────────────────────────────────── -->
<footer class="ohrs-footer">
  <div class="container">
    <div class="row g-4">

      <div class="col-lg-4">
        <div class="footer-brand"><img src="<?= ROOT_URL ?>assets/img/logo.png" alt="OHRS" style="height:32px; margin-bottom: 0.5rem;"></div>
        <p style="font-size:.875rem;line-height:1.8;max-width:300px;">
          Pakistan's trusted online house rental platform. Find your perfect home with ease,
          transparency, and security.
        </p>
        <div class="d-flex gap-2 mt-3">
          <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h6>Quick Links</h6>
        <ul>
          <li><a href="<?= ROOT_URL ?>index.php">Home</a></li>
          <li><a href="<?= ROOT_URL ?>houses.php">Browse Houses</a></li>
          <li><a href="<?= ROOT_URL ?>offers.php">Special Offers</a></li>
          <li><a href="<?= ROOT_URL ?>about.php">About Us</a></li>
          <li><a href="<?= ROOT_URL ?>contact.php">Contact</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h6>Account</h6>
        <ul>
          <li><a href="<?= ROOT_URL ?>register.php">Register</a></li>
          <li><a href="<?= ROOT_URL ?>login.php">Login</a></li>
          <li><a href="<?= ROOT_URL ?>customer/dashboard.php">My Dashboard</a></li>
          <li><a href="<?= ROOT_URL ?>customer/reservations.php">My Reservations</a></li>
          <li><a href="<?= ROOT_URL ?>customer/payments.php">My Payments</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h6>Contact Info</h6>
        <ul>
          <li style="margin-bottom:.6rem">
            <i class="bi bi-geo-alt text-primary me-2"></i>
            <span style="font-size:.875rem">Rahim Yar Khan</span>
          </li>
          <li style="margin-bottom:.6rem">
            <i class="bi bi-telephone text-primary me-2"></i>
            <span style="font-size:.875rem">+92 300 000 0000</span>
          </li>
          <li style="margin-bottom:.6rem">
            <i class="bi bi-envelope text-primary me-2"></i>
            <span style="font-size:.875rem">info@ohrs.com</span>
          </li>
          <li>
            <i class="bi bi-clock text-primary me-2"></i>
            <span style="font-size:.875rem">Mon–Sat, 9:00 AM – 6:00 PM</span>
          </li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
      <span>&copy; <?= date('Y') ?> OHRS — Online House Rental. All rights reserved.</span>
      <span>Designed &amp; developed for a university Final Year Project.</span>
    </div>
  </div>
</footer>

<!-- ── Scripts ─────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script src="<?= ROOT_URL ?>assets/js/main.js"></script>
<?php if (!empty($extra_js)): ?>
  <?php foreach ((array)$extra_js as $js): ?>
    <script src="<?= e($js) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

<script>
  // Init GLightbox if gallery exists
  if (document.querySelector('.glightbox')) {
    GLightbox({ touchNavigation: true, loop: true });
  }
</script>

</body>
</html>
