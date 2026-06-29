<?php
$admin_title      = 'Advertisements';
$admin_breadcrumb = ['Advertisements' => null];
require_once '../includes/admin-header.php';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $title    = trim($_POST['title'] ?? '');
    $link     = trim($_POST['link'] ?? '');
    $position = $_POST['position'] ?? 'home';
    $status   = $_POST['status'] ?? 'active';
    $ad_id    = (int)($_POST['ad_id'] ?? 0);

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = upload_image($_FILES['image'], dirname(__DIR__) . '/assets/uploads/ads');
    }

    if ($ad_id) {
        $img_sql = $image ? ",image=:img" : "";
        $params  = [':t'=>$title,':l'=>$link,':p'=>$position,':st'=>$status,':id'=>$ad_id];
        if ($image) $params[':img'] = $image;
        db()->prepare("UPDATE advertisements SET title=:t,link=:l,position=:p,status=:st$img_sql WHERE id=:id")->execute($params);
        flash('success','Ad updated.');
    } else {
        if (!$image) { flash('danger','Image is required.'); redirect('advertisements.php'); }
        db()->prepare("INSERT INTO advertisements (title,image,link,position,status) VALUES (:t,:img,:l,:p,:st)")
            ->execute([':t'=>$title,':img'=>$image,':l'=>$link,':p'=>$position,':st'=>$status]);
        flash('success','Advertisement added.');
    }
    redirect('advertisements.php');
}

// Delete
if (isset($_GET['del'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    db()->prepare("DELETE FROM advertisements WHERE id=:id")->execute([':id'=>(int)$_GET['del']]);
    flash('success','Ad deleted.'); redirect('advertisements.php');
}

$ads = db()->query("SELECT * FROM advertisements ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-end mb-4">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adModal">
    <i class="bi bi-plus-lg me-2"></i>Add Advertisement
  </button>
</div>

<div class="row g-4">
  <?php foreach ($ads as $ad): ?>
    <div class="col-md-6 col-lg-4">
      <div class="admin-card">
        <div style="height:160px;overflow:hidden;background:var(--neutral-100);">
          <img src="../assets/uploads/ads/<?= e($ad['image']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
        </div>
        <div class="admin-card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <strong style="font-size:.9rem;"><?= e($ad['title']) ?></strong>
            <?= status_badge($ad['status']) ?>
          </div>
          <div style="font-size:.78rem;color:var(--neutral-500);">
            <i class="bi bi-grid me-1"></i><?= ucfirst($ad['position']) ?>
            <?php if ($ad['link']): ?>
              · <a href="<?= e($ad['link']) ?>" target="_blank" style="font-size:.78rem;">Link</a>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-sm btn-outline-primary edit-ad-btn flex-fill"
                    data-ad='<?= htmlspecialchars(json_encode($ad), ENT_QUOTES) ?>'>Edit</button>
            <a href="?del=<?= $ad['id'] ?>&csrf=<?= e(csrf_token()) ?>"
               class="btn btn-sm btn-outline-danger flex-fill confirm-action" data-confirm="Delete this ad?">Delete</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="adModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="ad_id" id="ad_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">Add Advertisement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="ad_title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label">Link URL</label>
            <input type="url" name="link" id="ad_link" class="form-control" placeholder="https://…">
          </div>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">Position</label>
              <select name="position" id="ad_pos" class="form-select">
                <?php foreach (['home','header','sidebar','footer'] as $p): ?>
                  <option value="<?= $p ?>"><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Status</label>
              <select name="status" id="ad_status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Ad</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.edit-ad-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var a = JSON.parse(this.dataset.ad);
    document.getElementById('ad_id').value     = a.id;
    document.getElementById('ad_title').value  = a.title;
    document.getElementById('ad_link').value   = a.link || '';
    document.getElementById('ad_pos').value    = a.position;
    document.getElementById('ad_status').value = a.status;
    new bootstrap.Modal(document.getElementById('adModal')).show();
  });
});
</script>

<?php require_once '../includes/admin-footer.php'; ?>
