<?php
require_once 'includes/init.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'customer/dashboard.php');
}

$page_title = 'Login';
include_once 'includes/header.php';
?>

<div class="auth-wrapper">
  <div class="container">
    <div class="auth-card mx-auto">
      <div class="auth-logo"><i class="bi bi-house-heart-fill"></i> OHRS</div>
      <h2>Welcome back</h2>
      <p class="sub">Sign in to your account to continue</p>

      <?php render_flash(); ?>

      <form action="actions/login.php" method="POST" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label" for="email">Email Address</label>
          <div class="input-icon-wrap">
            <i class="bi bi-envelope"></i>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="you@example.com" autocomplete="email" required
                   value="<?= e($_GET['email'] ?? '') ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label d-flex justify-content-between" for="password">
            Password
            <a href="#" style="font-size:.8rem;">Forgot password?</a>
          </label>
          <div class="input-icon-wrap">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="••••••••" autocomplete="current-password" required>
            <button type="button" class="toggle-password btn btn-sm" data-target="#password"
                    style="position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--neutral-400);padding:.25rem;">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="mb-4 d-flex align-items-center justify-content-between">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label" for="remember" style="font-size:.875rem;">Remember me</label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>

        <p style="text-align:center;font-size:.875rem;color:var(--neutral-500);">
          Don't have an account?
          <a href="register.php" style="font-weight:600;">Create one</a>
        </p>
      </form>

      <!-- Demo credentials note -->
      <div class="alert alert-info mt-3" style="font-size:.8rem;">
        <strong>Demo:</strong> Admin — <code>admin@ohrs.com</code> / <code>Admin@123</code><br>
        Customer — <code>ali@example.com</code> / <code>Test@1234</code>
      </div>
    </div>
  </div>
</div>

<?php include_once 'includes/footer.php'; ?>
