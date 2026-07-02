<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_admin();

if (isset($_GET['id']) && isset($_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id = (int)$_GET['id'];
    db()->prepare("DELETE FROM houses WHERE id=:id")->execute([':id'=>$id]);
    flash('success','House deleted.');
}

redirect('../../admin/houses.php');
