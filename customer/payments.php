<?php
require_once '../includes/init.php';
require_login('../login.php');
if (!is_customer()) redirect('../admin/index.php');

$uid = current_user()['id'];
$page_title = 'My Payments';

// Pre-fill reservation_id from query
$pre_rid = (int)($_GET['reservation_id'] ?? 0);

// Load approved reservations without a paid payment
$approved = db()->prepare(
    "SELECT r.id, h.title, h.rent
     FROM reservations r
     JOIN houses h ON h.id = r.house_id
     WHERE r.user_id = :u AND r.status = 'approved'
     ORDER BY r.created_at DESC"
);
$approved->execute([':u' => $uid]);
$approved = $approved->fetchAll();

// Payment history
$payments = db()->prepare(
    "SELECT p.*, h.title AS house_title
     FROM payments p
     JOIN reservations r ON r.id = p.reservation_id
     JOIN houses h ON h.id = r.house_id
     WHERE p.user_id = :u ORDER BY p.created_at DESC"
);
$payments->execute([':u' => $uid]);
$payments = $payments->fetchAll();

include_once '../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Payments</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Payments</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>
    <div class="row g-4">

      <!-- Pay Form -->
      <div class="col-lg-4">
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow-sm);">
          <h5 style="font-weight:700;margin-bottom:1.25rem;"><i class="bi bi-credit-card text-primary me-2"></i>Submit Payment</h5>

          <?php if (empty($approved)): ?>
            <div class="alert alert-info" style="font-size:.85rem;">
              No approved reservations to pay for. Your reservation must be approved by admin first.
            </div>
          <?php else: ?>
            <form action="../actions/payment.php" method="POST">
              <?= csrf_field() ?>

              <div class="mb-3">
                <label class="form-label">Select Reservation <span class="text-danger">*</span></label>
                <select name="reservation_id" class="form-select" required>
                  <option value="">— Choose —</option>
                  <?php foreach ($approved as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $pre_rid === $a['id'] ? 'selected' : '' ?>>
                      <?= e($a['title']) ?> — <?= fmt_money($a['rent']) ?>/mo
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" class="form-control" value="<?= e(current_user()['name']) ?>" readonly>
              </div>

              <div class="mb-3">
                <label class="form-label">Account Number <span class="text-danger">*</span></label>
                <input type="text" name="account_number" class="form-control" required
                       placeholder="PK00BANK0000000000000000">
              </div>

              <div class="mb-3">
                <label class="form-label">Payment Amount (Rs.) <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control" required min="1" step="0.01"
                       placeholder="e.g. 85000">
              </div>

              <div class="mb-3">
                <label class="form-label">Transaction ID <span class="text-danger">*</span></label>
                <input type="text" name="transaction_id" class="form-control" required
                       placeholder="TXN...">
              </div>

              <div class="mb-3">
                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                <input type="date" name="payment_date" class="form-control" required
                       max="<?= date('Y-m-d') ?>">
              </div>

              <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-send me-2"></i>Submit Payment
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <!-- History -->
      <div class="col-lg-8">
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);overflow:hidden;">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--neutral-100);">
            <h5 style="margin:0;font-weight:700;">Payment History</h5>
          </div>
          <?php if (empty($payments)): ?>
            <div class="empty-state py-4">
              <span class="icon" style="font-size:2rem;"><i class="bi bi-receipt"></i></span>
              <p>No payments recorded yet.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table ohrs-table mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Property</th>
                    <th>Amount</th>
                    <th>Txn ID</th>
                    <th>Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($payments as $i => $p): ?>
                    <tr>
                      <td><?= $i+1 ?></td>
                      <td><?= e($p['house_title']) ?></td>
                      <td><?= fmt_money($p['amount']) ?></td>
                      <td><code><?= e($p['transaction_id']) ?></code></td>
                      <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                      <td><?= status_badge($p['status']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include_once '../includes/footer.php'; ?>
