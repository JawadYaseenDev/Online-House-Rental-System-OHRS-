<?php
$admin_title      = 'Special Offers';
$admin_breadcrumb = ['Offers' => null];
require_once '../includes/admin-header.php';

// Save offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $hid  = (int)($_POST['house_id'] ?? 0) ?: null;
    $data = [
        ':t'  => trim($_POST['title'] ?? ''),
        ':d'  => trim($_POST['description'] ?? ''),
        ':dp' => (float)($_POST['discount_pct'] ?? 0),
        ':s'  => $_POST['start_date'] ?? '',
        ':e'  => $_POST['end_date'] ?? '',
        ':st' => $_POST['status'] ?? 'active',
        ':h'  => $hid,
    ];
    if (isset($_POST['offer_id']) && $_POST['offer_id']) {
        $data[':id'] = (int)$_POST['offer_id'];
        db()->prepare("UPDATE offers SET title=:t,description=:d,discount_pct=:dp,start_date=:s,end_date=:e,status=:st,house_id=:h WHERE id=:id")->execute($data);
        flash('success','Offer updated.');
    } else {
        db()->prepare("INSERT INTO offers (house_id,title,description,discount_pct,start_date,end_date,status) VALUES (:h,:t,:d,:dp,:s,:e,:st)")->execute($data);
        flash('success','Offer added.');
    }
    redirect('offers.php');
}

// Delete
if (isset($_GET['del'], $_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    db()->prepare("DELETE FROM offers WHERE id=:id")->execute([':id'=>(int)$_GET['del']]);
    flash('success','Offer deleted.'); redirect('offers.php');
}

$offers = db()->query(
    "SELECT o.*, h.title AS house_title FROM offers o LEFT JOIN houses h ON h.id = o.house_id ORDER BY o.end_date DESC"
)->fetchAll();
$houses = db()->query("SELECT id, title FROM houses WHERE status != 'inactive' ORDER BY title")->fetchAll();

// Auto-expire old offers
db()->query("UPDATE offers SET status='expired' WHERE end_date < CURDATE() AND status = 'active'");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <span style="font-size:.875rem;color:var(--neutral-500);"><?= count($offers) ?> offers</span>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#offerModal">
    <i class="bi bi-plus-lg me-2"></i>Add Offer
  </button>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table ohrs-table mb-0">
      <thead>
        <tr><th>Title</th><th>Property</th><th>Discount</th><th>Period</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($offers as $o): ?>
          <tr>
            <td style="font-weight:600;"><?= e($o['title']) ?></td>
            <td><?= e($o['house_title'] ?? 'All Properties') ?></td>
            <td><?= number_format($o['discount_pct'],0) ?>%</td>
            <td style="font-size:.8rem;"><?= date('d M Y', strtotime($o['start_date'])) ?> – <?= date('d M Y', strtotime($o['end_date'])) ?></td>
            <td><?= status_badge($o['status']) ?></td>
            <td>
              <button class="btn btn-xs btn-outline-primary edit-offer-btn"
                      data-offer='<?= htmlspecialchars(json_encode($o), ENT_QUOTES) ?>'
                      style="font-size:.72rem;padding:.2rem .55rem;">Edit</button>
              <a href="?del=<?= $o['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                 class="btn btn-xs btn-outline-danger confirm-action" data-confirm="Delete this offer?"
                 style="font-size:.72rem;padding:.2rem .55rem;margin-left:2px;">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="offerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="offer_id" id="offer_id" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="offerModalLabel">Add Offer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="o_title" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" id="o_desc" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Discount (%)</label>
              <input type="number" name="discount_pct" id="o_disc" class="form-control" value="5" min="0" max="100" step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">Property (optional)</label>
              <select name="house_id" id="o_house" class="form-select">
                <option value="">All Properties</option>
                <?php foreach ($houses as $h): ?>
                  <option value="<?= $h['id'] ?>"><?= e($h['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" id="o_start" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" id="o_end" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Status</label>
              <select name="status" id="o_status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Offer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.edit-offer-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var o = JSON.parse(this.dataset.offer);
    document.getElementById('offer_id').value   = o.id;
    document.getElementById('o_title').value    = o.title;
    document.getElementById('o_desc').value     = o.description || '';
    document.getElementById('o_disc').value     = o.discount_pct;
    document.getElementById('o_house').value    = o.house_id || '';
    document.getElementById('o_start').value    = o.start_date;
    document.getElementById('o_end').value      = o.end_date;
    document.getElementById('o_status').value   = o.status;
    document.getElementById('offerModalLabel').textContent = 'Edit Offer';
    new bootstrap.Modal(document.getElementById('offerModal')).show();
  });
});
</script>

<?php require_once '../includes/admin-footer.php'; ?>
