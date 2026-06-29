<?php
$admin_title      = 'Feedback';
$admin_breadcrumb = ['Feedback' => null];
require_once '../includes/admin-header.php';

$feedback = db()->query(
    "SELECT f.*, CONCAT(u.first_name,' ',u.last_name) AS tenant, h.title AS house_title
     FROM feedback f
     JOIN users u ON u.id = f.user_id
     LEFT JOIN houses h ON h.id = f.house_id
     ORDER BY f.created_at DESC"
)->fetchAll();

$avg = db()->query("SELECT ROUND(AVG(rating),1) FROM feedback")->fetchColumn();
?>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="kpi-card">
      <div class="kpi-icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-star-fill" style="font-size:1.4rem;"></i></div>
      <div><div class="num"><?= $avg ?: '—' ?></div><div class="lbl">Average Rating</div></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card">
      <div class="kpi-icon" style="background:#eff6ff;color:#1a56db;"><i class="bi bi-chat-left-dots" style="font-size:1.4rem;"></i></div>
      <div><div class="num"><?= count($feedback) ?></div><div class="lbl">Total Feedback</div></div>
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr><th>Tenant</th><th>Property</th><th>Rating</th><th>Review</th><th>Comment</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php if (empty($feedback)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">No feedback submitted yet.</td></tr>
        <?php else: ?>
          <?php foreach ($feedback as $f): ?>
            <tr>
              <td style="font-weight:600;font-size:.875rem;"><?= e($f['tenant']) ?></td>
              <td style="font-size:.82rem;"><?= e($f['house_title'] ?? 'General') ?></td>
              <td><span style="color:#f59e0b;"><?= str_repeat('★',(int)$f['rating']) ?></span> <?= $f['rating'] ?>/5</td>
              <td style="font-size:.82rem;"><?= e($f['review'] ?? '—') ?></td>
              <td style="font-size:.82rem;max-width:220px;white-space:normal;"><?= e(substr($f['comment'] ?? '',0,100)) ?>…</td>
              <td style="font-size:.78rem;"><?= date('d M Y', strtotime($f['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/admin-footer.php'; ?>
