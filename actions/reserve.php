<?php
require_once '../includes/init.php';
require_login('../login.php');
if (!is_customer()) redirect('../index.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../houses.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../houses.php'); }

$house_id   = (int)($_POST['house_id'] ?? 0);
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?: null;
$notes      = trim($_POST['notes'] ?? '');
$user_id    = current_user()['id'];

// Validate house
$house = db()->prepare("SELECT id, status FROM houses WHERE id = :id");
$house->execute([':id' => $house_id]);
$house = $house->fetch();

if (!$house || $house['status'] !== 'available') {
    flash('danger', 'This property is not available for reservation.');
    redirect('../house-detail.php?id=' . $house_id);
}

if (!$start_date || strtotime($start_date) < strtotime(date('Y-m-d'))) {
    flash('danger', 'Please select a valid move-in date (today or later).');
    redirect('../house-detail.php?id=' . $house_id);
}

// Check if user already has a pending/approved reservation for this house
$existing = db()->prepare(
    "SELECT id FROM reservations
     WHERE user_id = :u AND house_id = :h AND status IN ('pending','approved') LIMIT 1"
);
$existing->execute([':u' => $user_id, ':h' => $house_id]);
if ($existing->fetch()) {
    flash('warning', 'You already have an active reservation for this property.');
    redirect('../house-detail.php?id=' . $house_id);
}

// Insert reservation
db()->prepare(
    "INSERT INTO reservations (user_id, house_id, start_date, end_date, notes)
     VALUES (:u, :h, :s, :e, :n)"
)->execute([
    ':u' => $user_id,
    ':h' => $house_id,
    ':s' => $start_date,
    ':e' => $end_date,
    ':n' => $notes,
]);

flash('success', 'Your reservation has been submitted! The administrator will review and approve it shortly.');
redirect('../customer/reservations.php');
