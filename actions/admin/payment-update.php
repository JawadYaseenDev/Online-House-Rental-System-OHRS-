<?php
require_once '../../includes/init.php';
require_admin();
if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../../admin/payments.php'); }

$id = (int)($_POST['id'] ?? 0);
$st = $_POST['status'] ?? '';
if ($id && in_array($st, ['pending','paid','failed'])) {
    db()->prepare("UPDATE payments SET status=:s WHERE id=:id")->execute([':s'=>$st,':id'=>$id]);
    if (!empty($_POST['ajax'])) {
        echo json_encode(['success'=>true,'message'=>'Payment status updated.']);
        exit;
    }
    flash('success','Payment status updated.');
}
redirect('../../admin/payments.php');
