<?php
$admin_title      = 'Users';
$admin_breadcrumb = ['Users' => null];
require_once '../includes/admin-header.php';

// Toggle status
if (isset($_GET['toggle'], $_GET['id'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id   = (int)$_GET['id'];
    $cur  = db()->query("SELECT status FROM users WHERE id=$id")->fetchColumn();
    $new  = $cur === 'active' ? 'inactive' : 'active';
    db()->prepare("UPDATE users SET status=:s WHERE id=:id AND role='customer'")->execute([':s'=>$new,':id'=>$id]);
    flash('success',"User status changed to $new.");
    redirect('users.php');
}

$users = db()->query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM reservations WHERE user_id = u.id) AS res_count
     FROM users u WHERE u.role = 'customer' ORDER BY u.created_at DESC"
)->fetchAll();
?>

<div class="admin-card mb-3">
  <div class="admin-card-body" style="padding:.875rem 1.25rem;">
    <div class="input-icon-wrap" style="max-width:360px;">
      <i class="bi bi-search"></i>
      <input type="text" id="table-search" class="form-control" placeholder="Search users…">
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr>
          <th>User</th><th>CNIC</th><th>Phone</th><th>Family</th><th>Reservations</th><th>Joined</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="../assets/uploads/profiles/<?= e($u['profile_pic']) ?>"
                     onerror="this.src='../assets/img/default-avatar.png'"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
                <div>
                  <div style="font-weight:600;font-size:.875rem;"><?= e($u['first_name'].' '.$u['last_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--neutral-500);"><?= e($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td style="font-size:.8rem;"><?= e($u['cnic']) ?></td>
            <td style="font-size:.8rem;"><?= e($u['phone']) ?></td>
            <td><?= (int)$u['family_members'] ?></td>
            <td><?= (int)$u['res_count'] ?></td>
            <td style="font-size:.8rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td><?= status_badge($u['status']) ?></td>
            <td>
              <a href="?toggle=1&id=<?= $u['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                 class="btn btn-xs <?= $u['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                 style="font-size:.72rem;padding:.2rem .55rem;">
                <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
