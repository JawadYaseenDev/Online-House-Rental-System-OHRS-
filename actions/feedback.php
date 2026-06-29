<?php
require_once '../includes/init.php';
require_login('../login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../customer/feedback.php');
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../customer/feedback.php'); }

$uid        = current_user()['id'];
$house_id   = (int)($_POST['house_id'] ?? 0) ?: null;
$rating     = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$review     = trim($_POST['review'] ?? '');
$comment    = trim($_POST['comment'] ?? '');
$suggestion = trim($_POST['suggestion'] ?? '');

if (!$review && !$comment) {
    flash('danger', 'Please write a review or comment.');
    redirect('../customer/feedback.php');
}

db()->prepare(
    "INSERT INTO feedback (user_id, house_id, rating, review, comment, suggestion)
     VALUES (:u, :h, :r, :rv, :c, :s)"
)->execute([
    ':u'  => $uid,
    ':h'  => $house_id,
    ':r'  => $rating,
    ':rv' => $review,
    ':c'  => $comment,
    ':s'  => $suggestion,
]);

flash('success', 'Thank you for your feedback!');
redirect('../customer/feedback.php');
