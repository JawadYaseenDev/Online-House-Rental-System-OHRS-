<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_login('../../login.php');

$u = current_user();
if ($u['role'] !== 'owner') {
    redirect('../../index.php');
}

if (isset($_GET['id']) && isset($_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id = (int)$_GET['id'];
    
    // Check ownership
    $own = db()->prepare("SELECT owner_id FROM houses WHERE id = :id");
    $own->execute([':id' => $id]);
    if ($own->fetchColumn() == $u['id']) {
        db()->prepare("DELETE FROM houses WHERE id=:id")->execute([':id'=>$id]);
        flash('success','House deleted.');
    } else {
        flash('danger','Unauthorized access.');
    }
}

redirect('../../owner/houses.php');
