<?php
require_once '../includes/init.php';
require_login('../login.php');
if (!is_customer()) redirect('../admin/index.php');

$uid = current_user()['id'];
$page_title = 'Feedback';

// My past feedback
$my_feedback = db()->prepare(
    "SELECT f.*, h.title AS house_title
     FROM feedback f
     LEFT JOIN houses h ON h.id = f.house_id
     WHERE f.user_id = :u ORDER BY f.created_at DESC"
);
$my_feedback->execute([':u' => $uid]);
$my_feedback = $my_feedback->fetchAll();

// Houses user has rented (for dropdown)
$my_houses = db()->prepare(
    "SELECT DISTINCT h.id, h.title
     FROM reservations r JOIN houses h ON h.id = r.house_id
     WHERE r.user_id = :u AND r.status IN ('approved','completed')"
);
$my_houses->execute([':u' => $uid]);
$my_houses = $my_houses->fetchAll();

include_once '../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Feedback</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Feedback</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>
    <div class="row g-4">

      <!-- Submit Form -->
      <div class="col-lg-4">
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow-sm);">
          <h5 style="font-weight:700;margin-bottom:1.25rem;"><i class="bi bi-chat-left-dots text-primary me-2"></i>Submit Feedback</h5>
          <form action="../actions/feedback.php" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
              <label class="form-label">Property (optional)</label>
              <select name="house_id" class="form-select">
                <option value="">General Feedback</option>
                <?php foreach ($my_houses as $mh): ?>
                  <option value="<?= $mh['id'] ?>"><?= e($mh['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Rating <span class="text-danger">*</span></label>
              <div class="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                  <label for="star<?= $i ?>">&#9733;</label>
                <?php endfor; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Short Review</label>
              <input type="text" name="review" class="form-control" placeholder="e.g. Excellent property!" maxlength="255">
            </div>

            <div class="mb-3">
              <label class="form-label">Detailed Comment</label>
              <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience…"></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Suggestion</label>
              <textarea name="suggestion" class="form-control" rows="2" placeholder="Any improvements you'd suggest…"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-send me-2"></i>Submit Feedback
            </button>
          </form>
        </div>
      </div>

      <!-- My Feedback List -->
      <div class="col-lg-8">
        <h5 style="font-weight:700;margin-bottom:1rem;">My Previous Feedback</h5>
        <?php if (empty($my_feedback)): ?>
          <div class="empty-state">
            <span class="icon"><i class="bi bi-chat-square-dots"></i></span>
            <p>You haven't submitted any feedback yet.</p>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($my_feedback as $f): ?>
              <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span style="color:#f59e0b;font-size:.9rem;"><?= str_repeat('★',(int)$f['rating']) ?><?= str_repeat('☆',5-(int)$f['rating']) ?></span>
                    <?php if ($f['review']): ?>
                      <strong style="margin-left:.5rem;font-size:.9rem;"><?= e($f['review']) ?></strong>
                    <?php endif; ?>
                  </div>
                  <span style="font-size:.75rem;color:var(--neutral-400);"><?= date('d M Y', strtotime($f['created_at'])) ?></span>
                </div>
                <?php if ($f['house_title']): ?>
                  <div style="font-size:.78rem;color:var(--primary);margin-bottom:.5rem;">
                    <i class="bi bi-house me-1"></i><?= e($f['house_title']) ?>
                  </div>
                <?php endif; ?>
                <?php if ($f['comment']): ?>
                  <p style="font-size:.875rem;color:var(--neutral-600);margin:0;"><?= e($f['comment']) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<?php include_once '../includes/footer.php'; ?>
