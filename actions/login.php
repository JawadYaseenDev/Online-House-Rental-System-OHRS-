<?php
require_once '../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../login.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../login.php'); }

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']);

if (!$email || !$password) {
    flash('danger', 'Please enter your email and password.');
    redirect('../login.php');
}

$stmt = db()->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    flash('danger', 'Invalid email or password. Please try again.');
    redirect('../login.php');
}

login_user($user);

if ($remember) {
    $token = bin2hex(random_bytes(32));
    db()->prepare("UPDATE users SET remember_token = :t WHERE id = :id")
        ->execute([':t' => hash('sha256', $token), ':id' => $user['id']]);
    setcookie('ohrs_remember', $token, time() + (86400 * 30), '/', '', false, true);
}

// Redirect to previous page if set
$dest = $_SESSION['login_redirect'] ?? null;
unset($_SESSION['login_redirect']);

if ($dest) {
    header("Location: $dest"); exit;
}

redirect($user['role'] === 'admin' ? '../admin/index.php' : '../customer/dashboard.php');
