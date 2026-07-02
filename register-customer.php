<?php
require_once 'includes/init.php';
if (is_logged_in()) redirect('houses.php');

$page_title = 'Register as Customer';
include_once 'includes/header.php';
?>

<div class="auth-wrapper" style="padding:2rem 0;">
  <div class="container">
    <div class="auth-card mx-auto" style="max-width:640px;">

      <div class="auth-logo"><img src="assets/img/logo.png" alt="OHRS" style="height:48px;"></div>

      <!-- Role badge -->
      <div class="text-center mb-3">
        <span class="badge" style="background:var(--primary-light);color:var(--primary);font-size:.8rem;padding:.4rem .9rem;border-radius:20px;font-weight:600;">
          <i class="bi bi-person-heart me-1"></i> Customer Account
        </span>
      </div>

      <h2 style="text-align:center;">Create your account</h2>
      <p class="sub" style="text-align:center;">Join OHRS to start browsing and reserving properties</p>

      <?php render_flash(); ?>

      <form action="actions/register.php" method="POST" enctype="multipart/form-data" novalidate id="reg-form">
        <?= csrf_field() ?>
        <input type="hidden" name="role" value="customer">

        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
            <input type="text" id="first_name" name="first_name" class="form-control" required
                   placeholder="Ahmed" maxlength="60">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
            <input type="text" id="last_name" name="last_name" class="form-control" required
                   placeholder="Khan" maxlength="60">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="cnic">CNIC Number <span class="text-danger">*</span></label>
            <input type="text" id="cnic" name="cnic" class="form-control" required
                   placeholder="35201-1234567-1" maxlength="15"
                   pattern="\d{5}-\d{7}-\d{1}">
            <div class="form-text">Format: 35201-1234567-1</div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="dob">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" id="dob" name="dob" class="form-control" required
                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <i class="bi bi-envelope"></i>
              <input type="email" id="email" name="email" class="form-control" required
                     placeholder="you@example.com" autocomplete="email">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
            <input type="tel" id="phone" name="phone" class="form-control" required
                   placeholder="+92-300-0000000" maxlength="20">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="family_members">Family Members</label>
            <input type="number" id="family_members" name="family_members" class="form-control"
                   value="1" min="1" max="30">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="profile_pic">Profile Picture</label>
            <input type="file" id="profile_pic" name="profile_pic" class="form-control img-preview-input"
                   accept="image/jpeg,image/png,image/webp" data-preview="#pic-preview">
          </div>

          <div class="col-12 d-flex align-items-center gap-3">
            <img id="pic-preview" src="" alt="" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--neutral-200);">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock"></i>
              <input type="password" id="password" name="password" class="form-control" required
                     placeholder="Min 8 characters" minlength="8" autocomplete="new-password">
              <button type="button" class="toggle-password btn btn-sm" data-target="#password"
                      style="position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--neutral-400);padding:.25rem;">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock-fill"></i>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                     placeholder="Repeat password" autocomplete="new-password">
            </div>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required>
              <label class="form-check-label" for="terms" style="font-size:.875rem;">
                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
              </label>
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-person-plus me-2"></i>Create Customer Account
            </button>
          </div>

          <div class="col-12 text-center">
            <p style="font-size:.875rem;color:var(--neutral-500);">
              Want to list a property instead?
              <a href="register-owner.php" style="font-weight:600;">Register as Owner</a>
            </p>
            <p style="font-size:.875rem;color:var(--neutral-500);">
              Already have an account? <a href="login.php" style="font-weight:600;">Sign in</a>
            </p>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('reg-form').addEventListener('submit', function(e) {
  var pw  = document.getElementById('password').value;
  var cpw = document.getElementById('confirm_password').value;
  if (pw !== cpw) {
    e.preventDefault();
    alert('Passwords do not match. Please check and try again.');
  }
});
</script>

<?php include_once 'includes/footer.php'; ?>
