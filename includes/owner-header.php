<?php
/**
 * Owner panel header
 * Expects $owner_title (page title) and $owner_breadcrumb (optional array)
 */
// Bootstrap: handle being called from owner/ or deeper paths
$_ohrs_root = dirname(__DIR__);
if (!defined('ROOT_URL')) {
    require_once $_ohrs_root . '/includes/init.php';
}
require_login('../login.php');
if (!is_owner()) redirect('../index.php');

$owner_title      = $owner_title ?? 'Dashboard';
$owner_breadcrumb = $owner_breadcrumb ?? [];
$u = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <meta name="csrf" content="<?= e(csrf_token()) ?>">
  <title><?= e($owner_title) ?> — OHRS Owner</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="../assets/css/animations.css">
</head>
<body class="admin-body">

<!-- Overlay for mobile sidebar -->
<div id="sidebar-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1035;"></div>

<div class="admin-wrapper">

<!-- ── Sidebar ────────────────────────────────────────────── -->
<aside class="admin-sidebar">

  <div class="sidebar-logo">
    <img src="../assets/img/logo.png" alt="OHRS" style="height:32px; margin-right:.5rem;">
    <span class="logo-badge" style="background:#dcfce7;color:#059669;">Owner</span>
  </div>

  <div class="sidebar-section-label">Main</div>
  <ul class="sidebar-nav">
    <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
  </ul>

  <div class="sidebar-section-label">My Properties</div>
  <ul class="sidebar-nav">
    <li><a href="houses.php"><i class="bi bi-house"></i> Manage Houses</a></li>
    <li><a href="house-add.php"><i class="bi bi-plus-circle"></i> Add House</a></li>
  </ul>

  <div class="sidebar-section-label">Bookings</div>
  <ul class="sidebar-nav">
    <li><a href="reservations.php"><i class="bi bi-calendar-check"></i> Reservations</a></li>
  </ul>

  <div class="sidebar-section-label">System</div>
  <ul class="sidebar-nav">
    <li><a href="../logout.php" style="color:rgba(255,255,255,.5);" onclick="return confirm('Logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
  </ul>

  <div class="sidebar-user">
    <img src="../assets/uploads/profiles/<?= e($u['pic']) ?>"
         onerror="this.src='../assets/img/default-avatar.png'" alt="Admin">
    <div>
      <div class="name"><?= e($u['name']) ?></div>
      <div class="role">House Owner</div>
    </div>
  </div>

</aside>

<!-- ── Main ───────────────────────────────────────────────── -->
<div class="admin-main">

  <!-- Topbar -->
  <header class="admin-topbar">
    <button id="sidebar-toggle" class="topbar-icon-btn d-lg-none border-0">
      <i class="bi bi-list" style="font-size:1.25rem;"></i>
    </button>
    <div>
      <?php if ($owner_breadcrumb): ?>
        <div class="admin-breadcrumb">
          <a href="dashboard.php">Owner</a>
          <?php foreach ($owner_breadcrumb as $label => $href): ?>
            &rsaquo;
            <?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><?= e($label) ?><?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <h1 class="page-title"><?= e($owner_title) ?></h1>
    </div>

    <div class="topbar-actions">
      <a href="../index.php" class="topbar-icon-btn" data-bs-toggle="tooltip" title="View Site" target="_blank">
        <i class="bi bi-box-arrow-up-right"></i>
      </a>
      <div class="topbar-icon-btn" style="cursor:default;">
        <img src="../assets/uploads/profiles/<?= e($u['pic']) ?>"
             onerror="this.src='../assets/img/default-avatar.png'"
             style="width:26px;height:26px;border-radius:50%;object-fit:cover;" alt="">
      </div>
    </div>
  </header>

  <div class="admin-content">
    <?php render_flash(); ?>
