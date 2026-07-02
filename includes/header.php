<?php
/**
 * Public site header — nav + CDN links
 * Usage: include_once 'includes/header.php';
 * Expects $page_title to be set by the calling page.
 */
$page_title = $page_title ?? 'Online House Rental System';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($page_meta ?? 'Find and rent the perfect house with OHRS — the modern online house rental platform.') ?>">
  <meta name="csrf" content="<?= e(csrf_token()) ?>">
  <title><?= e($page_title) ?> — OHRS</title>
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- GLightbox -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
  <!-- OHRS Styles -->
  <link rel="stylesheet" href="<?= ROOT_URL ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= ROOT_URL ?>assets/css/animations.css">
</head>
<body>

<!-- ── Navigation ─────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg ohrs-nav">
  <div class="container">

    <a class="navbar-brand" href="<?= ROOT_URL ?>index.php">
      <img src="<?= ROOT_URL ?>assets/img/logo.png" alt="OHRS" style="height:40px;">
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?= ROOT_URL ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= ROOT_URL ?>houses.php">Houses</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= ROOT_URL ?>offers.php">Offers</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= ROOT_URL ?>about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= ROOT_URL ?>contact.php">Contact</a></li>

        <?php if (is_logged_in()): ?>
          <li class="nav-item dropdown ms-lg-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
              <img src="<?= ROOT_URL ?>assets/uploads/profiles/<?= e($user['pic']) ?>"
                   onerror="this.src='<?= ROOT_URL ?>assets/img/default-avatar.png'"
                   style="width:30px;height:30px;border-radius:50%;object-fit:cover;" alt="Avatar">
              <?= e($user['name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (is_admin()): ?>
                <li><a class="dropdown-item" href="<?= ROOT_URL ?>admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="<?= ROOT_URL ?>customer/dashboard.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= ROOT_URL ?>customer/reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservations</a></li>
                <li><a class="dropdown-item" href="<?= ROOT_URL ?>customer/payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a></li>
                <li><a class="dropdown-item" href="<?= ROOT_URL ?>customer/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              <li><a class="dropdown-item text-danger" href="<?= ROOT_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>

        <?php else: ?>
          <li class="nav-item ms-lg-2">
            <a class="nav-link" href="<?= ROOT_URL ?>login.php">Login</a>
          </li>
          <li class="nav-item ms-lg-1">
            <a class="btn btn-primary btn-sm" href="<?= ROOT_URL ?>register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>

  </div>
</nav>

<main>
