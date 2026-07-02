<?php
$admin_title      = 'Users';
$admin_breadcrumb = ['Users' => null];
require_once '../includes/admin-header.php';

// Toggle status
if (isset($_GET['toggle'], $_GET['id'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id   = (int)$_GET['id'];
    $cur  = db()->query("SELECT status FROM users WHERE id=$id")->fetchColumn();
    $new  = $cur === 'active' ? 'inactive' : 'active';
    db()->prepare("UPDATE users SET status=:s WHERE id=:id AND role IN ('customer','owner')")->execute([':s'=>$new,':id'=>$id]);
    flash('success',"User status changed to $new.");
    redirect('users.php');
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$where  = match($filter) {
    'customers' => "WHERE u.role = 'customer'",
    'owners'    => "WHERE u.role = 'owner'",
    'pending'   => "WHERE u.role = 'owner' AND u.status = 'inactive'",
    default     => '',
};

$users = db()->query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM reservations WHERE user_id = u.id) AS res_count,
            (SELECT COUNT(*) FROM houses WHERE owner_id = u.id) AS house_count
     FROM users u $where ORDER BY u.status ASC, u.role ASC, u.created_at DESC"
)->fetchAll();
?>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-4 gap-2" style="font-size:.875rem;">
  <?php foreach(['all'=>'All','customers'=>'Customers','owners'=>'Owners','pending'=>'Pending Owners'] as $k=>$lbl): ?>
    <li class="nav-item">
      <a class="nav-link <?= $filter===$k?'active':'' ?>" href="?filter=<?= $k ?>"><?= $lbl ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="admin-card mb-3">
  <div class="admin-card-body d-flex align-items-center justify-content-between" style="padding:.875rem 1.25rem;">
    <div class="input-icon-wrap" style="width:100%; max-width:360px;">
      <i class="bi bi-search"></i>
      <input type="text" id="table-search" class="form-control" placeholder="Search users…">
    </div>
    <a href="admin-add.php" class="btn btn-primary btn-sm ms-3" style="white-space:nowrap;">
      <i class="bi bi-person-plus me-1"></i> Add Admin
    </a>
  </div>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr>
          <th>User</th><th>CNIC</th><th>Phone</th><th>City</th>
          <th>Properties / Reservations</th><th>Joined</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr <?= ($u['role']==='owner' && $u['status']==='inactive') ? 'style="background:#fffbeb;"' : '' ?>>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="../assets/uploads/profiles/<?= e($u['profile_pic']) ?>"
                     onerror="this.src='../assets/img/default-avatar.png'"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
                <div>
                  <div style="font-weight:600;font-size:.875rem;">
                    <?= e($u['first_name'].' '.$u['last_name']) ?>
                    <?php if ($u['role'] === 'admin'): ?>
                      <span class="badge bg-primary ms-1" style="font-size:0.65rem;">Admin</span>
                    <?php elseif ($u['role'] === 'owner'): ?>
                      <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Owner</span>
                      <?php if ($u['owner_type']): ?>
                        <span class="badge bg-light text-dark ms-1" style="font-size:0.6rem;border:1px solid #e5e7eb;">
                          <?= $u['owner_type']==='agency' ? '🏢 Agency' : '👤 Individual' ?>
                        </span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Customer</span>
                    <?php endif; ?>
                  </div>
                  <div style="font-size:.75rem;color:var(--neutral-500);"><?= e($u['email']) ?></div>
                  <?php if ($u['role']==='owner' && $u['agency_name']): ?>
                    <div style="font-size:.72rem;color:var(--neutral-400);">🏢 <?= e($u['agency_name']) ?></div>
                  <?php endif; ?>
                  <!-- Owner document links -->
                  <?php if ($u['role']==='owner'): ?>
                    <div class="d-flex gap-1 mt-1 flex-wrap">
                      <?php if ($u['cnic_front_image']): ?>
                        <a href="../assets/uploads/documents/<?= e($u['cnic_front_image']) ?>" target="_blank"
                           class="badge bg-light border text-dark" style="font-size:.65rem;text-decoration:none;">
                          <i class="bi bi-card-image me-1"></i>CNIC Front
                        </a>
                      <?php endif; ?>
                      <?php if ($u['cnic_back_image']): ?>
                        <a href="../assets/uploads/documents/<?= e($u['cnic_back_image']) ?>" target="_blank"
                           class="badge bg-light border text-dark" style="font-size:.65rem;text-decoration:none;">
                          <i class="bi bi-card-image me-1"></i>CNIC Back
                        </a>
                      <?php endif; ?>
                      <?php if ($u['proof_ownership_doc']): ?>
                        <a href="../assets/uploads/documents/<?= e($u['proof_ownership_doc']) ?>" target="_blank"
                           class="badge bg-info text-white" style="font-size:.65rem;text-decoration:none;">
                          <i class="bi bi-file-earmark-text me-1"></i>Proof Doc
                        </a>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td style="font-size:.8rem;"><?= e($u['cnic']) ?></td>
            <td style="font-size:.8rem;"><?= e($u['phone']) ?></td>
            <td style="font-size:.8rem;"><?= e($u['city'] ?? '—') ?></td>
            <td>
              <?php if ($u['role']==='owner'): ?>
                <span style="font-size:.8rem;"><i class="bi bi-house me-1 text-primary"></i><?= (int)$u['house_count'] ?> houses</span>
              <?php else: ?>
                <span style="font-size:.8rem;"><i class="bi bi-calendar me-1 text-primary"></i><?= (int)$u['res_count'] ?> bookings</span>
              <?php endif; ?>
            </td>
            <td style="font-size:.8rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td><?= status_badge($u['status']) ?></td>
            <td>
              <?php if ($u['role'] !== 'admin'): ?>
                <a href="?toggle=1&id=<?= $u['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                   class="btn btn-xs <?= $u['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                   style="font-size:.72rem;padding:.2rem .55rem;">
                  <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                </a>
              <?php else: ?>
                <span style="font-size:.75rem;color:var(--neutral-400);">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
