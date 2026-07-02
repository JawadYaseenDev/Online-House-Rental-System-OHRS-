<?php
$admin_title      = 'Pending Approvals';
$admin_breadcrumb = ['Approvals' => null];
require_once '../includes/admin-header.php';

// Handle approval/rejection
if (isset($_GET['action'], $_GET['id'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'approve') {
        db()->prepare("UPDATE houses SET status='available' WHERE id=:id")->execute([':id'=>$id]);
        flash('success', 'House approved and is now public.');
    } elseif ($_GET['action'] === 'reject') {
        db()->prepare("UPDATE houses SET status='inactive' WHERE id=:id")->execute([':id'=>$id]);
        flash('success', 'House rejected (set to inactive).');
    }
    redirect('approvals.php');
}

$pending_houses = db()->query(
    "SELECT h.*, u.first_name, u.last_name, u.email 
     FROM houses h 
     JOIN users u ON u.id = h.owner_id 
     WHERE h.status = 'pending' 
     ORDER BY h.created_at DESC"
)->fetchAll();
?>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr>
          <th>House</th>
          <th>Location</th>
          <th>Owner</th>
          <th>Rent</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pending_houses)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">No pending house approvals.</td></tr>
        <?php else: ?>
          <?php foreach ($pending_houses as $h): ?>
            <tr>
              <td>
                <a href="../house-detail.php?id=<?= $h['id'] ?>" target="_blank"><strong><?= e($h['title']) ?></strong></a>
              </td>
              <td><?= e($h['location']) ?></td>
              <td>
                <div><?= e($h['first_name'] . ' ' . $h['last_name']) ?></div>
                <div style="font-size:.75rem;color:var(--neutral-500);"><?= e($h['email']) ?></div>
              </td>
              <td><?= fmt_money($h['rent']) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="?action=approve&id=<?= $h['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                     class="btn btn-xs btn-success confirm-action" data-confirm="Approve this house?"
                     style="font-size:.75rem;padding:.25rem .55rem;">
                    <i class="bi bi-check-lg"></i> Approve
                  </a>
                  <a href="?action=reject&id=<?= $h['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                     class="btn btn-xs btn-danger confirm-action" data-confirm="Reject this house?"
                     style="font-size:.75rem;padding:.25rem .55rem;">
                    <i class="bi bi-x-lg"></i> Reject
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
