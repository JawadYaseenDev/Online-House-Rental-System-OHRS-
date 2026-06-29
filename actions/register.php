<?php
require_once '../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../register.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../register.php'); }

// Collect & sanitize
$data = [
    'first_name'     => trim($_POST['first_name'] ?? ''),
    'last_name'      => trim($_POST['last_name'] ?? ''),
    'cnic'           => trim($_POST['cnic'] ?? ''),
    'dob'            => $_POST['dob'] ?? '',
    'email'          => trim(strtolower($_POST['email'] ?? '')),
    'phone'          => trim($_POST['phone'] ?? ''),
    'family_members' => max(1, (int)($_POST['family_members'] ?? 1)),
];
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// Basic validation
$errors = [];
foreach (['first_name','last_name','cnic','dob','email','phone'] as $f) {
    if (empty($data[$f])) $errors[] = ucfirst(str_replace('_',' ',$f)) . ' is required.';
}
if ($password !== $confirm)    $errors[] = 'Passwords do not match.';
if (strlen($password) < 8)    $errors[] = 'Password must be at least 8 characters.';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

// CNIC format check: #####-#######-#
if (!preg_match('/^\d{5}-\d{7}-\d{1}$/', $data['cnic'])) {
    $errors[] = 'CNIC must be in format: 35201-1234567-1';
}

// Duplicate email / CNIC check
$dup = db()->prepare("SELECT id FROM users WHERE email = :e OR cnic = :c LIMIT 1");
$dup->execute([':e' => $data['email'], ':c' => $data['cnic']]);
if ($dup->fetch()) $errors[] = 'An account with this email or CNIC already exists.';

if ($errors) {
    flash('danger', implode('<br>', $errors));
    redirect('../register.php');
}

// Profile picture upload
$pic = 'default-avatar.png';
if (!empty($_FILES['profile_pic']['name'])) {
    $uploaded = upload_image(
        $_FILES['profile_pic'],
        __DIR__ . '/../assets/uploads/profiles'
    );
    if ($uploaded) $pic = $uploaded;
}

// Insert user
$stmt = db()->prepare(
    "INSERT INTO users (first_name, last_name, cnic, dob, email, phone, password, profile_pic, family_members)
     VALUES (:fn, :ln, :cnic, :dob, :email, :phone, :pw, :pic, :fm)"
);
$stmt->execute([
    ':fn'   => $data['first_name'],
    ':ln'   => $data['last_name'],
    ':cnic' => $data['cnic'],
    ':dob'  => $data['dob'],
    ':email'=> $data['email'],
    ':phone'=> $data['phone'],
    ':pw'   => password_hash($password, PASSWORD_DEFAULT),
    ':pic'  => $pic,
    ':fm'   => $data['family_members'],
]);

flash('success', 'Account created successfully! Please login.');
redirect('../login.php');
