<?php
require_once '../includes/init.php';
require_login('../login.php');
if (!is_customer()) redirect('../admin/index.php');

$uid = current_user()['id'];
$page_title = 'My Profile';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $fn   = trim($_POST['first_name'] ?? '');
        $ln   = trim($_POST['last_name'] ?? '');
        $phone= trim($_POST['phone'] ?? '');
        $fm   = max(1,(int)($_POST['family_members'] ?? 1));

        if (!$fn || !$ln || !$phone) {
            flash('danger','All fields are required.'); redirect('profile.php');
        }

        $pic = $_SESSION['user_pic'];
        if (!empty($_FILES['profile_pic']['name'])) {
            $up = upload_image($_FILES['profile_pic'], __DIR__ . '/../assets/uploads/profiles');
            if ($up) $pic = $up;
        }

        db()->prepare("UPDATE users SET first_name=:fn,last_name=:ln,phone=:ph,family_members=:fm,profile_pic=:pic WHERE id=:id")
            ->execute([':fn'=>$fn,':ln'=>$ln,':ph'=>$phone,':fm'=>$fm,':pic'=>$pic,':id'=>$uid]);

        $_SESSION['user_name'] = "$fn $ln";
        $_SESSION['user_pic']  = $pic;
        flash('success','Profile updated successfully.');
        redirect('profile.php');
    }

    if ($action === 'password') {
        $cur = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cnf = $_POST['confirm_password'] ?? '';

        $user = db()->prepare("SELECT password FROM users WHERE id=:id")->execute([':id'=>$uid]);
        $user = db()->query("SELECT password FROM users WHERE id=$uid")->fetch();

        if (!password_verify($cur, $user['password'])) {
            flash('danger','Current password is incorrect.'); redirect('profile.php');
        }
        if ($new !== $cnf) {
            flash('danger','New passwords do not match.'); redirect('profile.php');
        }
        if (strlen($new) < 8) {
            flash('danger','Password must be at least 8 characters.'); redirect('profile.php');
        }

        db()->prepare("UPDATE users SET password=:pw WHERE id=:id")
            ->execute([':pw'=>password_hash($new, PASSWORD_DEFAULT),':id'=>$uid]);
        flash('success','Password changed successfully.');
        redirect('profile.php');
    }
}

$profile = db()->query("SELECT * FROM users WHERE id=$uid")->fetch();
include_once '../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>My Profile</h1>
    <nav><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Profile</li>
    </ol></nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <?php render_flash(); ?>
    <div class="row g-4 justify-content-center">
      <div class="col-lg-7">

        <!-- Profile Info -->
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow-sm);margin-bottom:1.5rem;">
          <div class="d-flex align-items-center gap-3 mb-4">
            <img src="../assets/uploads/profiles/<?= e($profile['profile_pic']) ?>"
                 onerror="this.src='../assets/img/default-avatar.png'"
                 id="pic-preview-display"
                 style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--neutral-200);" alt="">
            <div>
              <h5 style="margin:0;font-weight:700;"><?= e($profile['first_name'].' '.$profile['last_name']) ?></h5>
              <div style="font-size:.85rem;color:var(--neutral-500);"><?= e($profile['email']) ?></div>
            </div>
          </div>

          <h6 style="font-weight:700;margin-bottom:1rem;">Edit Profile</h6>
          <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="profile">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required value="<?= e($profile['first_name']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" required value="<?= e($profile['last_name']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control" required value="<?= e($profile['phone']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Family Members</label>
                <input type="number" name="family_members" class="form-control" min="1" max="30" value="<?= (int)$profile['family_members'] ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control img-preview-input"
                       accept="image/jpeg,image/png,image/webp" data-preview="#pic-preview-display">
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Save Changes</button>
              </div>
            </div>
          </form>
        </div>

        <!-- Change Password -->
        <div style="background:var(--white);border:1px solid var(--neutral-200);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow-sm);">
          <h6 style="font-weight:700;margin-bottom:1rem;">Change Password</h6>
          <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-outline-primary">Update Password</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include_once '../includes/footer.php'; ?>
