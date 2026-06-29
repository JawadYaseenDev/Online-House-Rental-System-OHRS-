<?php
$admin_title      = 'Payments';
$admin_breadcrumb = ['Payments' => null];
require_once '../includes/admin-header.php';

// Status update
if (isset($_GET['set_status'], $_GET['id'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id = (int)$_GET['id'];
    $st = in_array($_GET['set_status'], ['pending','paid','failed']) ? $_GET['set_status'] : 'pending';
    db()->prepare("UPDATE payments SET status=:s WHERE id=:id")->execute([':s'=>$st,':id'=>$id]);
    flash('success','Payment status updated.');
    redirect('payments.php');
}

$payments = db()->query(
    "SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS tenant, h.title AS house
     FROM payments p
     JOIN users u ON u.id = p.user_id
     JOIN reservations r ON r.id = p.reservation_id
     JOIN houses h ON h.id = r.house_id
     ORDER BY p.created_at DESC"
)->fetchAll();

$total_revenue = db()->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
?>

<div class="row g-3 mb-4">
  <?php
  $pstat = db()->query("SELECT status, COUNT(*) cnt, COALESCE(SUM(amount),0) amt FROM payments GROUP BY status")->fetchAll();
  foreach ($pstat as $ps):
    $colors = ['paid'=>'success','pending'=>'warning','failed'=>'danger'];
    $c = $colors[$ps['status']] ?? 'secondary';
  ?>
    <div class="col-md-4">
      <div class="kpi-card">
        <div class="kpi-icon" style="background:var(--<?= $c === 'success' ? 'accent' : ($c === 'warning' ? 'warning' : 'danger') ?>)22;color:var(--<?= $c === 'success' ? 'accent' : ($c === 'warning' ? 'warning' : 'danger') ?>);">
          <i class="bi bi-credit-card" style="font-size:1.5rem;"></i>
        </div>
        <div>
          <div class="num"><?= (int)$ps['cnt'] ?></div>
          <div class="lbl"><?= ucfirst($ps['status']) ?> Payments — Rs. <?= number_format($ps['amt'],0) ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr>
          <th>#</th><th>Tenant</th><th>Property</th><th>Amount</th><th>Txn ID</th><th>Date</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= e($p['tenant']) ?></td>
            <td><?= e($p['house']) ?></td>
            <td style="font-weight:700;"><?= fmt_money($p['amount']) ?></td>
            <td><code style="font-size:.75rem;"><?= e($p['transaction_id']) ?></code></td>
            <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
            <td><?= status_badge($p['status']) ?></td>
            <td>
              <?php $csrf = csrf_token(); ?>
              <?php if ($p['status'] === 'pending'): ?>
                <a href="?set_status=paid&id=<?= $p['id'] ?>&csrf=<?= $csrf ?>"
                   class="btn btn-xs btn-success confirm-action" data-confirm="Mark as paid?"
                   style="font-size:.72rem;padding:.2rem .55rem;">
                  <i class="bi bi-check-lg"></i> Mark Paid
                </a>
                <a href="?set_status=failed&id=<?= $p['id'] ?>&csrf=<?= $csrf ?>"
                   class="btn btn-xs btn-danger confirm-action" data-confirm="Mark as failed?"
                   style="font-size:.72rem;padding:.2rem .55rem;margin-left:2px;">
                  <i class="bi bi-x-lg"></i> Failed
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
