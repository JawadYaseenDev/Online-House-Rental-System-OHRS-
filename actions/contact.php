<?php
require_once '../includes/init.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../contact.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../contact.php'); }

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$subject || !$message) {
    flash('danger', 'All fields are required.');
    redirect('../contact.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('danger', 'Invalid email address.');
    redirect('../contact.php');
}

db()->prepare(
    "INSERT INTO contact_messages (name, email, subject, message) VALUES (:n, :e, :s, :m)"
)->execute([':n'=>$name, ':e'=>$email, ':s'=>$subject, ':m'=>$message]);

flash('success', 'Your message has been sent. We will get back to you shortly!');
redirect('../contact.php');
