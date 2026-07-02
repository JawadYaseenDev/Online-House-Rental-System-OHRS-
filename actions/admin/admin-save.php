<?php
require_once '../../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../../admin/users.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../../admin/admin-add.php'); }

// Collect & sanitize
$data = [
    'first_name'     => trim($_POST['first_name'] ?? ''),
    'last_name'      => trim($_POST['last_name'] ?? ''),
    'cnic'           => trim($_POST['cnic'] ?? ''),
    'dob'            => $_POST['dob'] ?? '',
    'email'          => trim(strtolower($_POST['email'] ?? '')),
    'phone'          => trim($_POST['phone'] ?? ''),
    'family_members' => 1,
];
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// Basic validation
$errors = [];
foreach (['first_name','last_name','email','password'] as $f) {
    if (empty($_POST[$f])) $errors[] = ucfirst(str_replace('_',' ',$f)) . ' is required.';
}
if ($password !== $confirm)    $errors[] = 'Passwords do not match.';
if ($password && strlen($password) < 8)    $errors[] = 'Password must be at least 8 characters.';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

// Duplicate check
$dup = db()->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
$dup->execute([':e' => $data['email']]);
if ($dup->fetch()) $errors[] = 'An account with this email already exists.';

if ($errors) {
    flash('danger', implode('<br>', $errors));
    redirect('../../admin/admin-add.php');
}

// Profile picture upload
$pic = 'default-avatar.png';
if (!empty($_FILES['profile_pic']['name'])) {
    $uploaded = upload_image(
        $_FILES['profile_pic'],
        __DIR__ . '/../../assets/uploads/profiles'
    );
    if ($uploaded) $pic = $uploaded;
}

// Insert user as admin
$stmt = db()->prepare(
    "INSERT INTO users (first_name, last_name, cnic, dob, email, phone, password, profile_pic, family_members, role)
     VALUES (:fn, :ln, :cnic, :dob, :email, :phone, :pw, :pic, :fm, 'admin')"
);
$stmt->execute([
    ':fn'   => $data['first_name'],
    ':ln'   => $data['last_name'],
    ':cnic' => $data['cnic'],
    ':dob'  => empty($data['dob']) ? null : $data['dob'],
    ':email'=> $data['email'],
    ':phone'=> $data['phone'],
    ':pw'   => password_hash($password, PASSWORD_DEFAULT),
    ':pic'  => $pic,
    ':fm'   => $data['family_members'],
]);

flash('success', 'Admin account created successfully.');
redirect('../../admin/users.php');
