<?php
$admin_title      = 'Add New House';
$admin_breadcrumb = ['Houses' => 'houses.php', 'Add House' => null];
require_once '../includes/admin-header.php';
?>

<div class="admin-card" style="max-width:860px;">
  <div class="admin-card-header"><h5>House Details</h5></div>
  <div class="admin-card-body">
    <form action="../actions/admin/house-save.php" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="mode" value="add">

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required placeholder="e.g. Modern Family Villa" maxlength="150">
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="available">Available</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Location <span class="text-danger">*</span></label>
          <input type="text" name="location" class="form-control" required placeholder="City, e.g. Lahore" maxlength="120">
        </div>
        <div class="col-md-6">
          <label class="form-label">Monthly Rent (Rs.) <span class="text-danger">*</span></label>
          <input type="number" name="rent" class="form-control" required min="1" step="0.01" placeholder="e.g. 85000">
        </div>
        <div class="col-12">
          <label class="form-label">Full Address <span class="text-danger">*</span></label>
          <input type="text" name="address" class="form-control" required placeholder="House No., Street, Block, City" maxlength="255">
        </div>
        <div class="col-md-3">
          <label class="form-label">Bedrooms</label>
          <input type="number" name="bedrooms" class="form-control" value="1" min="0" max="20">
        </div>
        <div class="col-md-3">
          <label class="form-label">Bathrooms</label>
          <input type="number" name="bathrooms" class="form-control" value="1" min="0" max="20">
        </div>
        <div class="col-md-3">
          <label class="form-label">Capacity (people)</label>
          <input type="number" name="capacity" class="form-control" value="2" min="1" max="50">
        </div>
        <div class="col-md-3">
          <label class="form-label">Area (sq ft)</label>
          <input type="number" name="area" class="form-control" placeholder="e.g. 1800" step="0.01">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Describe the property…"></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Amenities <span style="font-size:.8rem;color:var(--neutral-500);">(comma-separated)</span></label>
          <input type="text" name="amenities" class="form-control"
                 placeholder="WiFi, Air Conditioning, Generator Backup, Parking">
        </div>
        <div class="col-12">
          <label class="form-label">House Images <span style="font-size:.8rem;color:var(--neutral-500);">(first image = primary)</span></label>
          <input type="file" name="images[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="col-12 d-flex gap-2 pt-2">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save House</button>
          <a href="houses.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>

    </form>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
