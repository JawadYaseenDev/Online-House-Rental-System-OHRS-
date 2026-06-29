<?php
require_once '../../includes/init.php';
require_admin();
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../../admin/reservations.php'); }

$id     = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$status = $_GET['status'] ?? $_POST['status'] ?? '';
$valid  = ['pending','approved','cancelled','completed'];

if ($id && in_array($status, $valid)) {
    db()->prepare("UPDATE reservations SET status=:s WHERE id=:id")->execute([':s'=>$status,':id'=>$id]);
    if ($status === 'approved') {
        $hid = db()->query("SELECT house_id FROM reservations WHERE id=$id")->fetchColumn();
        db()->prepare("UPDATE houses SET status='reserved' WHERE id=:id")->execute([':id'=>$hid]);
    }
    if (in_array($status, ['completed','cancelled'])) {
        $hid = db()->query("SELECT house_id FROM reservations WHERE id=$id")->fetchColumn();
        db()->prepare("UPDATE houses SET status='available' WHERE id=:id")->execute([':id'=>$hid]);
    }
    if (!empty($_POST['ajax'])) {
        echo json_encode(['success'=>true,'message'=>'Status updated.']);
        exit;
    }
    flash('success','Reservation updated.');
}
redirect('../../admin/reservations.php');
