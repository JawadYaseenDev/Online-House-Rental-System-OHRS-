<?php
require_once 'includes/init.php';
if (is_logged_in()) redirect('houses.php');

$page_title = 'Get Started — Choose Account Type';
include_once 'includes/header.php';
?>

<style>
.reg-select-wrapper {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  background: linear-gradient(135deg, #f0f5ff 0%, #fafbff 100%);
}
.reg-select-inner { max-width: 760px; width: 100%; }
.reg-select-header { text-align: center; margin-bottom: 2.5rem; }
.reg-select-header .badge-tag {
  display: inline-block;
  background: var(--primary-light);
  color: var(--primary);
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  padding: .3rem .9rem;
  border-radius: 20px;
  margin-bottom: .75rem;
}
.reg-select-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: var(--neutral-900);
  margin-bottom: .5rem;
}
.reg-select-header p {
  color: var(--neutral-500);
  font-size: 1rem;
}
.role-card {
  background: #fff;
  border: 2px solid var(--neutral-200);
  border-radius: 20px;
  padding: 2.25rem 2rem;
  text-align: center;
  text-decoration: none;
  color: inherit;
  display: block;
  transition: all .25s cubic-bezier(.4,0,.2,1);
  position: relative;
  overflow: hidden;
  height: 100%;
}
.role-card::before {
  content: '';
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity .25s ease;
  border-radius: 18px;
}
.role-card.customer::before { background: linear-gradient(135deg,#eff6ff,#dbeafe); }
.role-card.owner::before    { background: linear-gradient(135deg,#f0fdf4,#dcfce7); }
.role-card:hover { border-color: transparent; transform: translateY(-6px); box-shadow: 0 20px 48px rgba(0,0,0,.12); color: inherit; text-decoration: none; }
.role-card:hover::before { opacity: 1; }
.role-card .card-inner { position: relative; z-index: 1; }
.role-icon {
  width: 72px; height: 72px;
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.25rem;
  font-size: 1.75rem;
  transition: transform .25s ease;
}
.role-card:hover .role-icon { transform: scale(1.1); }
.customer .role-icon { background: #dbeafe; color: #1a56db; }
.owner    .role-icon { background: #dcfce7; color: #059669; }
.role-card h3 { font-size: 1.2rem; font-weight: 800; margin-bottom: .4rem; }
.role-card .role-desc { font-size: .875rem; color: var(--neutral-500); line-height: 1.6; margin-bottom: 1.25rem; }
.role-card .role-features { list-style: none; padding: 0; margin: 0 0 1.5rem; text-align: left; }
.role-card .role-features li { font-size: .82rem; color: var(--neutral-600); padding: .3rem 0; display: flex; align-items: center; gap: .5rem; }
.role-card .role-features li i { color: #10b981; flex-shrink: 0; font-size: .9rem; }
.role-card .btn-register {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .65rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: .875rem;
  border: 2px solid;
  transition: all .2s ease;
  width: 100%;
  justify-content: center;
}
.customer .btn-register { background: var(--primary); color: #fff; border-color: var(--primary); }
.customer .btn-register:hover { background: #1447e6; border-color: #1447e6; }
.owner .btn-register    { background: #059669; color: #fff; border-color: #059669; }
.owner .btn-register:hover { background: #047857; border-color: #047857; }
.reg-select-footer { text-align: center; margin-top: 2rem; font-size: .875rem; color: var(--neutral-500); }
.reg-select-footer a { font-weight: 600; color: var(--primary); }
</style>

<div class="reg-select-wrapper">
  <div class="reg-select-inner">

    <div class="reg-select-header">
      <img src="assets/img/logo.png" alt="OHRS" style="height:48px; margin-bottom:1rem;">
      <h1>How would you like to join?</h1>
      <p>Choose your account type to get started. Each role has its own tailored experience.</p>
    </div>

    <?php render_flash(); ?>

    <div class="row g-4">

      <!-- Customer Card -->
      <div class="col-md-6">
        <a href="register-customer.php" class="role-card customer" id="card-customer">
          <div class="card-inner">
            <div class="role-icon"><i class="bi bi-person-heart"></i></div>
            <h3>I'm a Customer</h3>
            <p class="role-desc">Looking for a property to rent? Browse hundreds of verified listings and reserve your ideal home.</p>
            <ul class="role-features">
              <li><i class="bi bi-check-circle-fill"></i> Browse available properties</li>
              <li><i class="bi bi-check-circle-fill"></i> Submit reservation requests</li>
              <li><i class="bi bi-check-circle-fill"></i> Track booking status</li>
              <li><i class="bi bi-check-circle-fill"></i> Account activated instantly</li>
            </ul>
            <span class="btn-register">
              <i class="bi bi-person-plus"></i> Register as Customer
            </span>
          </div>
        </a>
      </div>

      <!-- Owner Card -->
      <div class="col-md-6">
        <a href="register-owner.php" class="role-card owner" id="card-owner">
          <div class="card-inner">
            <div class="role-icon"><i class="bi bi-building"></i></div>
            <h3>I'm a House Owner</h3>
            <p class="role-desc">Want to list your property for rent? Upload your properties and connect with qualified tenants.</p>
            <ul class="role-features">
              <li><i class="bi bi-check-circle-fill"></i> List multiple properties</li>
              <li><i class="bi bi-check-circle-fill"></i> Manage reservations</li>
              <li><i class="bi bi-check-circle-fill"></i> Approve or reject bookings</li>
              <li><i class="bi bi-check-circle-fill"></i> Reviewed &amp; verified by admin</li>
            </ul>
            <span class="btn-register">
              <i class="bi bi-building-add"></i> Register as Owner
            </span>
          </div>
        </a>
      </div>

    </div>

    <div class="reg-select-footer">
      Already have an account? <a href="login.php">Sign in here</a>
    </div>

  </div>
</div>

<?php include_once 'includes/footer.php'; ?>
