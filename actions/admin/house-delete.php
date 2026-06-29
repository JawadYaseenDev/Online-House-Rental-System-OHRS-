<?php
require_once '../../includes/admin-header.php' === false
    ? require_once dirname(__DIR__, 2) . '/includes/init.php'
    : null;

// This file handles: house save (add/edit), house delete, status update
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'])) {
    if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../../admin/houses.php'); }

    $data = [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'location'    => trim($_POST['location'] ?? ''),
        'address'     => trim($_POST['address'] ?? ''),
        'rent'        => (float)($_POST['rent'] ?? 0),
        'capacity'    => (int)($_POST['capacity'] ?? 1),
        'bedrooms'    => (int)($_POST['bedrooms'] ?? 1),
        'bathrooms'   => (int)($_POST['bathrooms'] ?? 1),
        'area'        => ($_POST['area'] !== '' ? (float)$_POST['area'] : null),
        'status'      => $_POST['status'] ?? 'available',
    ];

    // Build amenities JSON
    $am_raw = trim($_POST['amenities'] ?? '');
    $amenities = $am_raw ? json_encode(array_map('trim', explode(',', $am_raw))) : '[]';

    if (!$data['title'] || !$data['location'] || !$data['address'] || $data['rent'] <= 0) {
        flash('danger','Title, location, address, and rent are required.');
        redirect('../../admin/houses.php');
    }

    if ($_POST['mode'] === 'add') {
        $stmt = db()->prepare(
            "INSERT INTO houses (title,description,location,address,rent,capacity,bedrooms,bathrooms,area,status,amenities)
             VALUES (:t,:d,:l,:a,:r,:cap,:bed,:bath,:ar,:st,:am)"
        );
        $stmt->execute([
            ':t'=>$data['title'],':d'=>$data['description'],':l'=>$data['location'],
            ':a'=>$data['address'],':r'=>$data['rent'],':cap'=>$data['capacity'],
            ':bed'=>$data['bedrooms'],':bath'=>$data['bathrooms'],':ar'=>$data['area'],
            ':st'=>$data['status'],':am'=>$amenities,
        ]);
        $house_id = db()->lastInsertId();
    } else {
        $house_id = (int)$_POST['house_id'];
        db()->prepare(
            "UPDATE houses SET title=:t,description=:d,location=:l,address=:a,rent=:r,
             capacity=:cap,bedrooms=:bed,bathrooms=:bath,area=:ar,status=:st,amenities=:am WHERE id=:id"
        )->execute([
            ':t'=>$data['title'],':d'=>$data['description'],':l'=>$data['location'],
            ':a'=>$data['address'],':r'=>$data['rent'],':cap'=>$data['capacity'],
            ':bed'=>$data['bedrooms'],':bath'=>$data['bathrooms'],':ar'=>$data['area'],
            ':st'=>$data['status'],':am'=>$amenities,':id'=>$house_id,
        ]);
    }

    // Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
        $is_first = ($_POST['mode'] === 'add');
        foreach ($_FILES['images']['name'] as $idx => $name) {
            if (!$name) continue;
            $file = [
                'name'     => $name,
                'type'     => $_FILES['images']['type'][$idx],
                'tmp_name' => $_FILES['images']['tmp_name'][$idx],
                'error'    => $_FILES['images']['error'][$idx],
                'size'     => $_FILES['images']['size'][$idx],
            ];
            $path = upload_image($file, dirname(__DIR__, 2) . '/assets/uploads/houses');
            if ($path) {
                db()->prepare("INSERT INTO house_images (house_id,image_path,is_primary,sort_order) VALUES (:h,:p,:pr,:o)")
                    ->execute([':h'=>$house_id,':p'=>$path,':pr'=>$is_first?1:0,':o'=>$idx]);
                $is_first = false;
            }
        }
    }

    flash('success', 'House saved successfully.');
    redirect('../../admin/houses.php');
}

// DELETE house
if (isset($_GET['id']) && isset($_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    $id = (int)$_GET['id'];
    db()->prepare("DELETE FROM houses WHERE id=:id")->execute([':id'=>$id]);
    flash('success','House deleted.');
}

redirect('../../admin/houses.php');
