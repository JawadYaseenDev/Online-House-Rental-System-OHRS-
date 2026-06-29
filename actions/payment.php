<?php
require_once '../includes/init.php';
require_login('../login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../customer/payments.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../customer/payments.php'); }

$uid            = current_user()['id'];
$reservation_id = (int)($_POST['reservation_id'] ?? 0);
$account_number = trim($_POST['account_number'] ?? '');
$amount         = (float)($_POST['amount'] ?? 0);
$transaction_id = trim($_POST['transaction_id'] ?? '');
$payment_date   = $_POST['payment_date'] ?? '';

// Validate reservation belongs to this user
$res = db()->prepare("SELECT r.*, h.rent FROM reservations r JOIN houses h ON h.id = r.house_id WHERE r.id = :id AND r.user_id = :u AND r.status = 'approved'");
$res->execute([':id' => $reservation_id, ':u' => $uid]);
$res = $res->fetch();

if (!$res) {
    flash('danger', 'Invalid reservation or reservation not yet approved.');
    redirect('../customer/payments.php');
}

$errors = [];
if (!$account_number) $errors[] = 'Account number is required.';
if ($amount <= 0)     $errors[] = 'Enter a valid payment amount.';
if (!$transaction_id) $errors[] = 'Transaction ID is required.';
if (!$payment_date)   $errors[] = 'Payment date is required.';

// Check duplicate transaction
if ($transaction_id) {
    $dup = db()->prepare("SELECT id FROM payments WHERE transaction_id = :t LIMIT 1");
    $dup->execute([':t' => $transaction_id]);
    if ($dup->fetch()) $errors[] = 'This transaction ID has already been used.';
}

if ($errors) {
    flash('danger', implode('<br>', $errors));
    redirect('../customer/payments.php');
}

db()->prepare(
    "INSERT INTO payments (reservation_id, user_id, account_number, amount, transaction_id, payment_date, status)
     VALUES (:r, :u, :a, :am, :t, :d, 'pending')"
)->execute([
    ':r'  => $reservation_id,
    ':u'  => $uid,
    ':a'  => $account_number,
    ':am' => $amount,
    ':t'  => $transaction_id,
    ':d'  => $payment_date,
]);

flash('success', 'Payment submitted successfully! It will be verified by the administrator.');
redirect('../customer/payments.php');
