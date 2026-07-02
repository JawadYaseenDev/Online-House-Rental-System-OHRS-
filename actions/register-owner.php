<?php
require_once '../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../register-owner.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../register-owner.php'); }

// ── Collect & sanitize ────────────────────────────────────────
$data = [
    'first_name'      => trim($_POST['first_name']      ?? ''),
    'last_name'       => trim($_POST['last_name']       ?? ''),
    'cnic'            => trim($_POST['cnic']            ?? ''),
    'dob'             => $_POST['dob']                  ?? '',
    'email'           => trim(strtolower($_POST['email'] ?? '')),
    'phone'           => trim($_POST['phone']           ?? ''),
    'current_address' => trim($_POST['current_address'] ?? ''),
    'city'            => trim($_POST['city']            ?? ''),
    'owner_type'      => $_POST['owner_type']           ?? '',
    'agency_name'     => trim($_POST['agency_name']     ?? '') ?: null,
];
$password = $_POST['password']         ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// ── Validation ────────────────────────────────────────────────
$errors = [];
foreach (['first_name','last_name','cnic','dob','email','phone','current_address','city'] as $f) {
    if (empty($data[$f])) $errors[] = ucfirst(str_replace('_',' ',$f)) . ' is required.';
}
if (!in_array($data['owner_type'], ['individual','agency'])) {
    $errors[] = 'Please select owner type (Individual or Agency).';
}
if ($data['owner_type'] === 'agency' && empty($data['agency_name'])) {
    $errors[] = 'Agency/Business name is required for agency accounts.';
}
if ($password !== $confirm)   $errors[] = 'Passwords do not match.';
if (strlen($password) < 8)   $errors[] = 'Password must be at least 8 characters.';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (!preg_match('/^\d{5}-\d{7}-\d{1}$/', $data['cnic'])) {
    $errors[] = 'CNIC must be in format: 35201-1234567-1';
}
if (empty($_POST['terms'])) $errors[] = 'You must accept the Terms of Service.';

// ── Duplicate check ────────────────────────────────────────────
$dup = db()->prepare("SELECT id FROM users WHERE email = :e OR cnic = :c LIMIT 1");
$dup->execute([':e' => $data['email'], ':c' => $data['cnic']]);
if ($dup->fetch()) $errors[] = 'An account with this email or CNIC already exists.';

if ($errors) {
    flash('danger', implode('<br>', $errors));
    redirect('../register-owner.php');
}

// ── Upload folder setup ───────────────────────────────────────
$doc_dir     = __DIR__ . '/../assets/uploads/documents';
$profile_dir = __DIR__ . '/../assets/uploads/profiles';
if (!is_dir($doc_dir)) mkdir($doc_dir, 0755, true);

// ── Profile picture ────────────────────────────────────────────
$pic = 'default-avatar.png';
if (!empty($_FILES['profile_pic']['name'])) {
    $up = upload_image($_FILES['profile_pic'], $profile_dir);
    if ($up) $pic = $up;
}

// ── CNIC front ─────────────────────────────────────────────────
$cnic_front = null;
if (!empty($_FILES['cnic_front_image']['name'])) {
    $up = upload_image($_FILES['cnic_front_image'], $doc_dir);
    if ($up) $cnic_front = $up;
}
if (!$cnic_front) { flash('danger','CNIC front image is required.'); redirect('../register-owner.php'); }

// ── CNIC back ──────────────────────────────────────────────────
$cnic_back = null;
if (!empty($_FILES['cnic_back_image']['name'])) {
    $up = upload_image($_FILES['cnic_back_image'], $doc_dir);
    if ($up) $cnic_back = $up;
}
if (!$cnic_back) { flash('danger','CNIC back image is required.'); redirect('../register-owner.php'); }

// ── Proof of ownership (optional, allow PDF too) ───────────────
$proof = null;
if (!empty($_FILES['proof_ownership_doc']['name'])) {
    $f = $_FILES['proof_ownership_doc'];
    // Allow PDF in addition to images
    if ($f['type'] === 'application/pdf' && $f['error'] === UPLOAD_ERR_OK && $f['size'] <= 5 * 1048576) {
        $filename = 'doc_' . uniqid('', true) . '.pdf';
        $dest     = $doc_dir . '/' . $filename;
        if (move_uploaded_file($f['tmp_name'], $dest)) $proof = $filename;
    } else {
        $up = upload_image($f, $doc_dir);
        if ($up) $proof = $up;
    }
}

// ── Insert user (status = inactive → awaiting admin approval) ──
$stmt = db()->prepare(
    "INSERT INTO users
       (first_name, last_name, cnic, dob, email, phone, password, profile_pic,
        family_members, role, status,
        current_address, city, owner_type, agency_name,
        cnic_front_image, cnic_back_image, proof_ownership_doc)
     VALUES
       (:fn, :ln, :cnic, :dob, :email, :phone, :pw, :pic,
        1, 'owner', 'inactive',
        :addr, :city, :otype, :aname,
        :cfront, :cback, :proof)"
);
$stmt->execute([
    ':fn'    => $data['first_name'],
    ':ln'    => $data['last_name'],
    ':cnic'  => $data['cnic'],
    ':dob'   => $data['dob'],
    ':email' => $data['email'],
    ':phone' => $data['phone'],
    ':pw'    => password_hash($password, PASSWORD_DEFAULT),
    ':pic'   => $pic,
    ':addr'  => $data['current_address'],
    ':city'  => $data['city'],
    ':otype' => $data['owner_type'],
    ':aname' => $data['agency_name'],
    ':cfront'=> $cnic_front,
    ':cback' => $cnic_back,
    ':proof' => $proof,
]);

flash('success',
    'Your owner application has been submitted successfully! ' .
    'Our team will review your documents within 24 hours. ' .
    'You will receive access to your account once approved.'
);
redirect('../login.php');
