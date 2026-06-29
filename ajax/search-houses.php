<?php
/**
 * AJAX house search endpoint
 * Returns JSON array of matching houses
 */
require_once '../includes/init.php';
header('Content-Type: application/json; charset=utf-8');

$where  = ["h.status != 'inactive'"];
$params = [];

if (!empty($_GET['location'])) {
    $where[]  = 'h.location LIKE :loc';
    $params[':loc'] = '%' . $_GET['location'] . '%';
}
if (isset($_GET['min_rent']) && $_GET['min_rent'] !== '') {
    $where[]  = 'h.rent >= :min';
    $params[':min'] = (float)$_GET['min_rent'];
}
if (isset($_GET['max_rent']) && $_GET['max_rent'] !== '') {
    $where[]  = 'h.rent <= :max';
    $params[':max'] = (float)$_GET['max_rent'];
}
if (!empty($_GET['bedrooms'])) {
    $op = (int)$_GET['bedrooms'] >= 4 ? '>=' : '=';
    $where[]  = "h.bedrooms $op :bed";
    $params[':bed'] = (int)$_GET['bedrooms'];
}
if (!empty($_GET['capacity'])) {
    $where[]  = 'h.capacity >= :cap';
    $params[':cap'] = (int)$_GET['capacity'];
}
if (!empty($_GET['status'])) {
    $where[]  = 'h.status = :st';
    $params[':st'] = $_GET['status'];
}

$sql  = "SELECT h.id, h.title, h.location, h.rent, h.capacity, h.bedrooms, h.bathrooms, h.status,
                hi.image_path AS image
         FROM houses h
         LEFT JOIN house_images hi ON hi.house_id = h.id AND hi.is_primary = 1
         WHERE " . implode(' AND ', $where) . "
         ORDER BY h.status ASC, h.rent ASC
         LIMIT 50";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$houses = $stmt->fetchAll();

echo json_encode(['houses' => $houses]);
