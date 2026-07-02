<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_login('../../login.php');

$u = current_user();
if ($u['role'] !== 'owner') {
    redirect('../../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'])) {
    if (!csrf_check()) { flash('danger','Invalid request.'); redirect('../../owner/houses.php'); }

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
        // For owners, new houses are always pending.
        'status'      => 'pending',
    ];

    // Build amenities JSON
    $am_raw = trim($_POST['amenities'] ?? '');
    $amenities = $am_raw ? json_encode(array_map('trim', explode(',', $am_raw))) : '[]';

    if (!$data['title'] || !$data['location'] || !$data['address'] || $data['rent'] <= 0) {
        flash('danger','Title, location, address, and rent are required.');
        redirect('../../owner/houses.php');
    }

    if ($_POST['mode'] === 'add') {
        $stmt = db()->prepare(
            "INSERT INTO houses (owner_id, title,description,location,address,rent,capacity,bedrooms,bathrooms,area,status,amenities)
             VALUES (:own,:t,:d,:l,:a,:r,:cap,:bed,:bath,:ar,:st,:am)"
        );
        $stmt->execute([
            ':own'=>$u['id'],
            ':t'=>$data['title'],':d'=>$data['description'],':l'=>$data['location'],
            ':a'=>$data['address'],':r'=>$data['rent'],':cap'=>$data['capacity'],
            ':bed'=>$data['bedrooms'],':bath'=>$data['bathrooms'],':ar'=>$data['area'],
            ':st'=>$data['status'],':am'=>$amenities,
        ]);
        $house_id = db()->lastInsertId();
    } else {
        $house_id = (int)$_POST['house_id'];
        
        // Ensure owner owns the house
        $own = db()->prepare("SELECT owner_id FROM houses WHERE id = :id");
        $own->execute([':id' => $house_id]);
        if ($own->fetchColumn() != $u['id']) {
            flash('danger','Unauthorized access.');
            redirect('../../owner/houses.php');
        }

        db()->prepare(
            "UPDATE houses SET title=:t,description=:d,location=:l,address=:a,rent=:r,
             capacity=:cap,bedrooms=:bed,bathrooms=:bath,area=:ar,amenities=:am WHERE id=:id"
        )->execute([
            ':t'=>$data['title'],':d'=>$data['description'],':l'=>$data['location'],
            ':a'=>$data['address'],':r'=>$data['rent'],':cap'=>$data['capacity'],
            ':bed'=>$data['bedrooms'],':bath'=>$data['bathrooms'],':ar'=>$data['area'],
            ':am'=>$amenities,':id'=>$house_id,
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
                // Determine if there's already a primary image
                if (!$is_first) {
                    $has_primary = db()->query("SELECT COUNT(*) FROM house_images WHERE house_id=$house_id AND is_primary=1")->fetchColumn();
                    if ($has_primary == 0) $is_first = true;
                }
                
                db()->prepare("INSERT INTO house_images (house_id,image_path,is_primary,sort_order) VALUES (:h,:p,:pr,:o)")
                    ->execute([':h'=>$house_id,':p'=>$path,':pr'=>$is_first?1:0,':o'=>$idx]);
                $is_first = false;
            }
        }
    }

    flash('success', 'House saved successfully. It is now pending admin approval.');
    redirect('../../owner/houses.php');
}

// Status changes (e.g. from reservations page dropdown if any)
if (isset($_GET['action']) && $_GET['action'] === 'status' && isset($_POST['id'], $_POST['status']) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    if (!hash_equals(csrf_token(), $_SERVER['HTTP_X_CSRF_TOKEN'])) {
        http_response_code(403); exit('Invalid CSRF token');
    }
    $id = (int)$_POST['id'];
    $st = $_POST['status'];
    
    // Check ownership
    $own = db()->prepare("SELECT owner_id FROM houses WHERE id = :id");
    $own->execute([':id' => $id]);
    if ($own->fetchColumn() != $u['id']) {
        http_response_code(403); exit('Unauthorized');
    }

    $valid = ['available','reserved','occupied','inactive','pending'];
    if (in_array($st, $valid)) {
        db()->prepare("UPDATE houses SET status=:s WHERE id=:id")->execute([':s'=>$st,':id'=>$id]);
        echo json_encode(['success'=>true]);
    } else {
        http_response_code(400); echo json_encode(['error'=>'Invalid status']);
    }
    exit;
}

redirect('../../owner/houses.php');
