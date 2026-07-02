<?php
require_once 'includes/init.php';
if (is_logged_in()) redirect('houses.php');

$page_title = 'Register as House Owner';
include_once 'includes/header.php';
?>

<style>
.owner-reg-section { background: linear-gradient(135deg,#f0fdf4 0%,#fafbff 100%); padding: 2.5rem 0; }
.section-divider {
  display: flex; align-items: center; gap: 1rem;
  margin: 1.5rem 0 1rem;
  font-size: .78rem; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--neutral-400);
}
.section-divider::before, .section-divider::after {
  content: ''; flex: 1; height: 1px; background: var(--neutral-200);
}
.upload-zone {
  border: 2px dashed var(--neutral-300);
  border-radius: 12px;
  padding: 1.25rem;
  text-align: center;
  cursor: pointer;
  transition: all .2s ease;
  background: var(--neutral-50);
}
.upload-zone:hover { border-color: #059669; background: #f0fdf4; }
.upload-zone input[type=file] { display: none; }
.upload-zone label { cursor: pointer; margin: 0; }
.upload-preview {
  width: 100%; height: 80px; object-fit: cover;
  border-radius: 8px; margin-top: .75rem; display: none;
  border: 2px solid var(--neutral-200);
}
.owner-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.owner-type-option { display: none; }
.owner-type-label {
  display: flex; flex-direction: column; align-items: center; gap: .5rem;
  padding: 1rem; border: 2px solid var(--neutral-200); border-radius: 12px;
  cursor: pointer; transition: all .2s ease; text-align: center;
  font-weight: 600; font-size: .875rem; color: var(--neutral-700);
}
.owner-type-label i { font-size: 1.5rem; color: var(--neutral-400); transition: color .2s; }
.owner-type-option:checked + .owner-type-label {
  border-color: #059669; background: #f0fdf4; color: #059669;
}
.owner-type-option:checked + .owner-type-label i { color: #059669; }
.owner-type-label:hover { border-color: #059669; }
.info-alert {
  background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
  padding: 1rem 1.25rem; display: flex; gap: .75rem; align-items: flex-start;
  font-size: .85rem; color: #92400e;
}
.info-alert i { color: #f59e0b; font-size: 1.1rem; flex-shrink: 0; margin-top: .1rem; }
</style>

<div class="owner-reg-section">
  <div class="container">
    <div class="auth-card mx-auto" style="max-width:700px;">

      <div class="auth-logo"><img src="assets/img/logo.png" alt="OHRS" style="height:48px;"></div>

      <!-- Role badge -->
      <div class="text-center mb-3">
        <span class="badge" style="background:#dcfce7;color:#059669;font-size:.8rem;padding:.4rem .9rem;border-radius:20px;font-weight:600;">
          <i class="bi bi-building me-1"></i> House Owner Account
        </span>
      </div>

      <h2 style="text-align:center;">Register as a House Owner</h2>
      <p class="sub" style="text-align:center;">List your properties and connect with verified tenants</p>

      <!-- Pending approval notice -->
      <div class="info-alert mb-4">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          <strong>Account requires admin approval.</strong><br>
          After submitting, your application will be reviewed by our team within 24 hours. You'll be able to log in and upload properties once approved.
        </div>
      </div>

      <?php render_flash(); ?>

      <form action="actions/register-owner.php" method="POST" enctype="multipart/form-data" novalidate id="owner-reg-form">
        <?= csrf_field() ?>

        <!-- ── Personal Info ─────────────────────────── -->
        <div class="section-divider"><span>Personal Information</span></div>
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label" for="o_first_name">First Name <span class="text-danger">*</span></label>
            <input type="text" id="o_first_name" name="first_name" class="form-control" required
                   placeholder="Ahmed" maxlength="60">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_last_name">Last Name <span class="text-danger">*</span></label>
            <input type="text" id="o_last_name" name="last_name" class="form-control" required
                   placeholder="Khan" maxlength="60">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_cnic">CNIC Number <span class="text-danger">*</span></label>
            <input type="text" id="o_cnic" name="cnic" class="form-control" required
                   placeholder="35201-1234567-1" maxlength="15" pattern="\d{5}-\d{7}-\d{1}">
            <div class="form-text">Format: 35201-1234567-1</div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_dob">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" id="o_dob" name="dob" class="form-control" required
                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_email">Email Address <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <i class="bi bi-envelope"></i>
              <input type="email" id="o_email" name="email" class="form-control" required
                     placeholder="owner@example.com" autocomplete="email">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_phone">Phone Number <span class="text-danger">*</span></label>
            <input type="tel" id="o_phone" name="phone" class="form-control" required
                   placeholder="+92-300-0000000" maxlength="20">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_profile_pic">Profile Picture</label>
            <input type="file" id="o_profile_pic" name="profile_pic" class="form-control img-preview-input"
                   accept="image/jpeg,image/png,image/webp" data-preview="#o-pic-preview">
          </div>

          <div class="col-md-6 d-flex align-items-center">
            <img id="o-pic-preview" src="" alt="" style="display:none;width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--neutral-200);">
          </div>

        </div>

        <!-- ── Address & Location ─────────────────────── -->
        <div class="section-divider"><span>Location Details</span></div>
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label" for="o_address">Current Address <span class="text-danger">*</span></label>
            <input type="text" id="o_address" name="current_address" class="form-control" required
                   placeholder="House 12, Block A, DHA Phase 5" maxlength="255">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_city">City <span class="text-danger">*</span></label>
            <select id="o_city" name="city" class="form-select" required>
              <option value="">Select city…</option>
              <?php foreach(['Lahore','Karachi','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta','Sialkot','Gujranwala','Other'] as $c): ?>
                <option value="<?= $c ?>"><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>

        <!-- ── Owner Type ─────────────────────────────── -->
        <div class="section-divider"><span>Owner Type</span></div>
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">I am registering as <span class="text-danger">*</span></label>
            <div class="owner-type-grid">
              <div>
                <input class="owner-type-option" type="radio" name="owner_type" id="type_individual" value="individual" required>
                <label class="owner-type-label" for="type_individual">
                  <i class="bi bi-person-badge"></i>
                  Individual Owner
                  <small style="font-size:.72rem;font-weight:400;color:var(--neutral-500);">Personal property owner</small>
                </label>
              </div>
              <div>
                <input class="owner-type-option" type="radio" name="owner_type" id="type_agency" value="agency">
                <label class="owner-type-label" for="type_agency">
                  <i class="bi bi-buildings"></i>
                  Property Agency
                  <small style="font-size:.72rem;font-weight:400;color:var(--neutral-500);">Real estate business</small>
                </label>
              </div>
            </div>
          </div>

          <div class="col-12" id="agency-name-wrap" style="display:none;">
            <label class="form-label" for="o_agency">Agency / Business Name</label>
            <input type="text" id="o_agency" name="agency_name" class="form-control"
                   placeholder="e.g. Pak Realty Group" maxlength="150">
          </div>

        </div>

        <!-- ── Documents ──────────────────────────────── -->
        <div class="section-divider"><span>Identity Verification Documents</span></div>
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label">CNIC Front Image <span class="text-danger">*</span></label>
            <div class="upload-zone" onclick="document.getElementById('cnic_front').click()">
              <label>
                <i class="bi bi-card-image" style="font-size:1.5rem;color:var(--neutral-400);display:block;margin-bottom:.4rem;"></i>
                <span style="font-size:.82rem;color:var(--neutral-500);">Click to upload CNIC front</span>
              </label>
              <input type="file" id="cnic_front" name="cnic_front_image" accept="image/jpeg,image/png,image/webp" required
                     onchange="previewUpload(this,'#prev-cnic-front')">
            </div>
            <img id="prev-cnic-front" class="upload-preview" alt="CNIC Front">
          </div>

          <div class="col-md-6">
            <label class="form-label">CNIC Back Image <span class="text-danger">*</span></label>
            <div class="upload-zone" onclick="document.getElementById('cnic_back').click()">
              <label>
                <i class="bi bi-card-image" style="font-size:1.5rem;color:var(--neutral-400);display:block;margin-bottom:.4rem;"></i>
                <span style="font-size:.82rem;color:var(--neutral-500);">Click to upload CNIC back</span>
              </label>
              <input type="file" id="cnic_back" name="cnic_back_image" accept="image/jpeg,image/png,image/webp" required
                     onchange="previewUpload(this,'#prev-cnic-back')">
            </div>
            <img id="prev-cnic-back" class="upload-preview" alt="CNIC Back">
          </div>

          <div class="col-12">
            <label class="form-label">Proof of Property Ownership <span style="font-size:.78rem;font-weight:400;color:var(--neutral-400);">(Optional)</span></label>
            <div class="upload-zone" onclick="document.getElementById('proof_doc').click()">
              <label>
                <i class="bi bi-file-earmark-text" style="font-size:1.5rem;color:var(--neutral-400);display:block;margin-bottom:.4rem;"></i>
                <span style="font-size:.82rem;color:var(--neutral-500);">Upload ownership deed, registry, or any supporting document</span><br>
                <span style="font-size:.72rem;color:var(--neutral-400);">JPG, PNG or PDF accepted</span>
              </label>
              <input type="file" id="proof_doc" name="proof_ownership_doc" accept="image/jpeg,image/png,image/webp,application/pdf"
                     onchange="previewUpload(this,'#prev-proof',true)">
            </div>
            <img id="prev-proof" class="upload-preview" alt="Proof Doc">
            <span id="prev-proof-name" style="display:none;font-size:.8rem;color:var(--neutral-500);margin-top:.5rem;"></span>
          </div>

        </div>

        <!-- ── Password ───────────────────────────────── -->
        <div class="section-divider"><span>Set Password</span></div>
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label" for="o_password">Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock"></i>
              <input type="password" id="o_password" name="password" class="form-control" required
                     placeholder="Min 8 characters" minlength="8" autocomplete="new-password">
              <button type="button" class="toggle-password btn btn-sm" data-target="#o_password"
                      style="position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--neutral-400);padding:.25rem;">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="o_confirm_password">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock-fill"></i>
              <input type="password" id="o_confirm_password" name="confirm_password" class="form-control" required
                     placeholder="Repeat password" autocomplete="new-password">
            </div>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="o_terms" name="terms" value="1" required>
              <label class="form-check-label" for="o_terms" style="font-size:.875rem;">
                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>, and confirm all information is accurate.
              </label>
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn w-100" style="background:#059669;color:#fff;font-weight:700;padding:.75rem;">
              <i class="bi bi-building-add me-2"></i>Submit Owner Application
            </button>
          </div>

          <div class="col-12 text-center">
            <p style="font-size:.875rem;color:var(--neutral-500);">
              Looking to rent instead?
              <a href="register-customer.php" style="font-weight:600;">Register as Customer</a>
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
// Show/hide agency name field
document.querySelectorAll('input[name="owner_type"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    document.getElementById('agency-name-wrap').style.display =
      this.value === 'agency' ? 'block' : 'none';
  });
});

// Upload preview helper
function previewUpload(input, previewSelector, isPdf) {
  var preview = document.querySelector(previewSelector);
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  if (isPdf && file.type === 'application/pdf') {
    if (preview) preview.style.display = 'none';
    var nameEl = document.getElementById('prev-proof-name');
    if (nameEl) { nameEl.style.display = 'block'; nameEl.textContent = '📄 ' + file.name; }
    return;
  }
  var reader = new FileReader();
  reader.onload = function(e) {
    if (preview) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    }
  };
  reader.readAsDataURL(file);
}

// Password match
document.getElementById('owner-reg-form').addEventListener('submit', function(e) {
  var pw  = document.getElementById('o_password').value;
  var cpw = document.getElementById('o_confirm_password').value;
  if (pw !== cpw) {
    e.preventDefault();
    alert('Passwords do not match. Please check and try again.');
  }
});
</script>

<?php include_once 'includes/footer.php'; ?>
