<?php
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: houses.php'); exit; }

require_once dirname(__DIR__) . '/includes/init.php';
require_login('../login.php');
$u = current_user();
if ($u['role'] !== 'owner') redirect('../index.php');

$house = db()->prepare("SELECT * FROM houses WHERE id=:id AND owner_id=:uid");
$house->execute([':id'=>$id, ':uid'=>$u['id']]);
$house = $house->fetch();
if (!$house) { flash('danger','House not found or unauthorized.'); redirect('houses.php'); }

$images = db()->prepare("SELECT * FROM house_images WHERE house_id=:id ORDER BY is_primary DESC");
$images->execute([':id'=>$id]);
$images = $images->fetchAll();

$am = json_decode($house['amenities'] ?? '[]', true) ?: [];
$amenities_str = implode(', ', $am);

$owner_title      = 'Edit House';
$owner_breadcrumb = ['Houses' => 'houses.php', 'Edit House' => null];

require_once '../includes/owner-header.php';
?>

<div class="admin-card" style="max-width:860px;">
  <div class="admin-card-header"><h5><?= e($house['title']) ?></h5></div>
  <div class="admin-card-body">
    <form action="../actions/owner/house-save.php" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="mode" value="edit">
      <input type="hidden" name="house_id" value="<?= $id ?>">

      <div class="row g-3">
        <div class="col-md-12">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required value="<?= e($house['title']) ?>" maxlength="150">
        </div>
        <div class="col-md-6">
          <label class="form-label">Location <span class="text-danger">*</span></label>
          <input type="text" name="location" class="form-control" required value="<?= e($house['location']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Monthly Rent (Rs.) <span class="text-danger">*</span></label>
          <input type="number" name="rent" class="form-control" required value="<?= $house['rent'] ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Full Address <span class="text-danger">*</span></label>
          <input type="text" name="address" class="form-control" required value="<?= e($house['address']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Bedrooms</label>
          <input type="number" name="bedrooms" class="form-control" value="<?= (int)$house['bedrooms'] ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Bathrooms</label>
          <input type="number" name="bathrooms" class="form-control" value="<?= (int)$house['bathrooms'] ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Capacity</label>
          <input type="number" name="capacity" class="form-control" value="<?= (int)$house['capacity'] ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Area (sq ft)</label>
          <input type="number" name="area" class="form-control" value="<?= $house['area'] ?>" step="0.01">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= e($house['description'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Amenities (comma-separated)</label>
          <input type="text" name="amenities" class="form-control" value="<?= e($amenities_str) ?>">
        </div>

        <!-- Existing images -->
        <?php if (!empty($images)): ?>
          <div class="col-12">
            <label class="form-label">Current Images</label>
            <div class="d-flex gap-2 flex-wrap">
              <?php foreach ($images as $img): ?>
                <div style="position:relative;">
                  <img src="../assets/uploads/houses/<?= e($img['image_path']) ?>"
                       style="width:90px;height:70px;object-fit:cover;border-radius:var(--radius-sm);border:2px solid var(--neutral-200);" alt="">
                  <?php if ($img['is_primary']): ?>
                    <span style="position:absolute;top:4px;left:4px;font-size:.65rem;background:var(--primary);color:#fff;padding:.1rem .4rem;border-radius:20px;">Primary</span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="col-12">
          <label class="form-label">Upload Additional Images</label>
          <input type="file" name="images[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="col-12 d-flex gap-2 pt-2">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update House</button>
          <a href="houses.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>

    </form>
  </div>
</div>

<?php require_once '../includes/owner-footer.php'; ?>
