<?php
$owner_title      = 'Reservations';
$owner_breadcrumb = ['Reservations' => null];
require_once '../includes/owner-header.php';

$uid = current_user()['id'];

// Status update
if (isset($_GET['set_status'], $_GET['id'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['set_status'];
    $valid  = ['pending','approved','cancelled','completed'];
    
    // Check if owner actually owns this house
    $check = db()->prepare("SELECT h.owner_id, h.id as hid FROM reservations r JOIN houses h ON r.house_id = h.id WHERE r.id = :id");
    $check->execute([':id' => $id]);
    $resInfo = $check->fetch();
    
    if ($resInfo && $resInfo['owner_id'] == $uid && in_array($status, $valid)) {
        db()->prepare("UPDATE reservations SET status=:s WHERE id=:id")->execute([':s'=>$status,':id'=>$id]);
        $hid = $resInfo['hid'];
        // If approved → mark house as reserved
        if ($status === 'approved') {
            db()->prepare("UPDATE houses SET status='reserved' WHERE id=:id")->execute([':id'=>$hid]);
        }
        // If completed/cancelled → mark house available again
        if (in_array($status, ['completed','cancelled'])) {
            db()->prepare("UPDATE houses SET status='available' WHERE id=:id")->execute([':id'=>$hid]);
        }
        flash('success','Reservation status updated.');
    } else {
        flash('danger','Invalid action or unauthorized.');
    }
    redirect('reservations.php');
}

$filter = $_GET['filter'] ?? 'all';
$where  = $filter !== 'all' ? "AND r.status = :f" : '';

$stmt = db()->prepare(
    "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS tenant, u.email, u.phone,
            h.title AS house, h.location, h.rent
     FROM reservations r
     JOIN users u ON u.id = r.user_id
     JOIN houses h ON h.id = r.house_id
     WHERE h.owner_id = :u $where
     ORDER BY r.created_at DESC"
);
$params = [':u' => $uid];
if ($filter !== 'all') $params[':f'] = $filter;
$stmt->execute($params);
$reservations = $stmt->fetchAll();
?>

<!-- Filter tabs -->
<ul class="nav nav-pills mb-4 gap-2" style="font-size:.875rem;">
  <?php foreach (['all','pending','approved','cancelled','completed'] as $f): ?>
    <li class="nav-item">
      <a class="nav-link <?= $filter === $f ? 'active' : '' ?>" href="?filter=<?= $f ?>"><?= ucfirst($f) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="table ohrs-table mb-0">
        <thead>
          <tr>
            <th>#</th><th>Tenant</th><th>Property</th><th>Move-in</th><th>Rent</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reservations)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No reservations found.</td></tr>
          <?php else: ?>
            <?php foreach ($reservations as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td>
                  <div style="font-weight:600;font-size:.875rem;"><?= e($r['tenant']) ?></div>
                  <div style="font-size:.75rem;color:var(--neutral-500);"><?= e($r['email']) ?></div>
                </td>
                <td>
                  <div style="font-weight:600;font-size:.875rem;"><?= e($r['house']) ?></div>
                  <div style="font-size:.75rem;color:var(--neutral-500);"><?= e($r['location']) ?></div>
                </td>
                <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
                <td><?= fmt_money($r['rent']) ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td>
                  <div class="d-flex gap-1 flex-wrap">
                    <?php $csrf = csrf_token(); ?>
                    <?php if ($r['status'] === 'pending'): ?>
                      <a href="?set_status=approved&id=<?= $r['id'] ?>&csrf=<?= $csrf ?>"
                         class="btn btn-xs btn-success confirm-action" data-confirm="Approve this reservation?"
                         style="font-size:.72rem;padding:.2rem .55rem;">
                        <i class="bi bi-check-lg"></i> Approve
                      </a>
                      <a href="?set_status=cancelled&id=<?= $r['id'] ?>&csrf=<?= $csrf ?>"
                         class="btn btn-xs btn-danger confirm-action" data-confirm="Cancel this reservation?"
                         style="font-size:.72rem;padding:.2rem .55rem;">
                        <i class="bi bi-x-lg"></i> Cancel
                      </a>
                    <?php endif; ?>
                    <?php if ($r['status'] === 'approved'): ?>
                      <a href="?set_status=completed&id=<?= $r['id'] ?>&csrf=<?= $csrf ?>"
                         class="btn btn-xs btn-info confirm-action" data-confirm="Mark as completed?"
                         style="font-size:.72rem;padding:.2rem .55rem;color:#fff;">
                        <i class="bi bi-check2-all"></i> Complete
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../includes/owner-footer.php'; ?>
