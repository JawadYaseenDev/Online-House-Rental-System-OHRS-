<?php
$owner_title      = 'Manage Houses';
$owner_breadcrumb = ['Houses' => null];
require_once '../includes/owner-header.php';

$uid = current_user()['id'];

$houses = db()->prepare(
    "SELECT h.*, COUNT(hi.id) AS img_count
     FROM houses h
     LEFT JOIN house_images hi ON hi.house_id = h.id
     WHERE h.owner_id = :u
     GROUP BY h.id
     ORDER BY h.created_at DESC"
);
$houses->execute([':u' => $uid]);
$houses = $houses->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <p style="margin:0;font-size:.875rem;color:var(--neutral-500);"><?= count($houses) ?> properties listed</p>
  </div>
  <a href="house-add.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-2"></i>Add New House
  </a>
</div>

<!-- Search bar -->
<div class="admin-card mb-4">
  <div class="admin-card-body" style="padding:.875rem 1.25rem;">
    <div class="input-icon-wrap" style="max-width:360px;">
      <i class="bi bi-search"></i>
      <input type="text" id="table-search" class="form-control" placeholder="Search houses…">
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr>
          <th>Image</th>
          <th>Title</th>
          <th>Location</th>
          <th>Rent</th>
          <th>Beds</th>
          <th>Capacity</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($houses as $h): ?>
          <?php
          $img = db()->prepare("SELECT image_path FROM house_images WHERE house_id=:id AND is_primary=1 LIMIT 1");
          $img->execute([':id' => $h['id']]);
          $img = $img->fetchColumn();
          ?>
          <tr>
            <td>
              <?php if ($img): ?>
                <img src="../assets/uploads/houses/<?= e($img) ?>" class="admin-table-img" alt="">
              <?php else: ?>
                <div style="width:48px;height:40px;background:var(--neutral-100);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--neutral-400);">
                  <i class="bi bi-image"></i>
                </div>
              <?php endif; ?>
            </td>
            <td><strong><?= e($h['title']) ?></strong></td>
            <td><?= e($h['location']) ?></td>
            <td><?= fmt_money($h['rent']) ?>/mo</td>
            <td><?= (int)$h['bedrooms'] ?></td>
            <td><?= (int)$h['capacity'] ?></td>
            <td>
              <?php
              $st = $h['status'];
              $bg = 'secondary';
              if ($st === 'available') $bg = 'success';
              elseif ($st === 'pending') $bg = 'warning text-dark';
              elseif ($st === 'reserved') $bg = 'info text-dark';
              elseif ($st === 'occupied') $bg = 'primary';
              ?>
              <span class="badge bg-<?= $bg ?>"><?= ucfirst($st) ?></span>
            </td>
            <td>
              <div class="d-flex gap-1 flex-nowrap">
                <?php if ($st !== 'pending'): ?>
                <a href="../house-detail.php?id=<?= $h['id'] ?>" target="_blank"
                   class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:.25rem .55rem;"
                   data-bs-toggle="tooltip" title="Preview"><i class="bi bi-eye"></i></a>
                <?php endif; ?>
                <a href="house-edit.php?id=<?= $h['id'] ?>"
                   class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:.25rem .55rem;"
                   data-bs-toggle="tooltip" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="../actions/owner/house-delete.php?id=<?= $h['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                   class="btn btn-xs btn-outline-danger confirm-action"
                   data-confirm="Delete this house and all its data?"
                   style="font-size:.75rem;padding:.25rem .55rem;"
                   data-bs-toggle="tooltip" title="Delete"><i class="bi bi-trash"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/owner-footer.php'; ?>
