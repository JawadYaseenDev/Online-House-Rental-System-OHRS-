<?php
/**
 * Global bootstrap file — included by every page
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();

define('ROOT_PATH', dirname(__DIR__) . '/');  // adjust depth as needed
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
    define('ROOT_URL', '/ohrs/');
} else {
    define('ROOT_URL', '/');
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Auto-fix the missing database column silently on every page load
try {
    db()->exec("ALTER TABLE houses ADD COLUMN owner_id INT(11) UNSIGNED DEFAULT NULL AFTER id");
} catch(Exception $e){}

try {
    $db = db();
    $db->exec("ALTER TABLE users ADD COLUMN current_address VARCHAR(255) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN city VARCHAR(100) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN owner_type ENUM('individual','agency') DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN agency_name VARCHAR(150) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN cnic_front_image VARCHAR(255) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN cnic_back_image VARCHAR(255) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN proof_ownership_doc VARCHAR(255) DEFAULT NULL");
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','owner','customer') NOT NULL DEFAULT 'customer'");
} catch(Exception $e){}
