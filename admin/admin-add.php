<?php
$admin_title      = 'Add New Admin';
$admin_breadcrumb = ['Users' => 'users.php', 'Add Admin' => null];
require_once '../includes/admin-header.php';
?>

<div class="admin-card" style="max-width:860px;">
  <div class="admin-card-header"><h5>Admin Details</h5></div>
  <div class="admin-card-body">
    <form action="../actions/admin/admin-save.php" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">First Name <span class="text-danger">*</span></label>
          <input type="text" name="first_name" class="form-control" required placeholder="e.g. John" maxlength="50">
        </div>
        <div class="col-md-6">
          <label class="form-label">Last Name <span class="text-danger">*</span></label>
          <input type="text" name="last_name" class="form-control" required placeholder="e.g. Doe" maxlength="50">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required placeholder="admin@ohrs.com" maxlength="150">
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control" placeholder="e.g. +92 300 0000000" maxlength="20">
        </div>
        <div class="col-md-6">
          <label class="form-label">CNIC</label>
          <input type="text" name="cnic" class="form-control" placeholder="Optional for Admins" maxlength="20">
        </div>
        <div class="col-md-6">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="dob" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min 8 characters">
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" class="form-control" required minlength="8" placeholder="Re-type password">
        </div>
        <div class="col-12">
          <label class="form-label">Profile Picture (Optional)</label>
          <input type="file" name="profile_pic" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>
        
        <div class="col-12 d-flex gap-2 pt-2">
          <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Create Admin</button>
          <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>

    </form>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
