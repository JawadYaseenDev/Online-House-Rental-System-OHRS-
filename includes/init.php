<?php
/**
 * Global bootstrap file — included by every page
 */
if (session_status() === PHP_SESSION_NONE) session_start();

define('ROOT_PATH', dirname(__DIR__, 0) . '/');  // adjust depth as needed
define('ROOT_URL', '/');  // Change to '/ohrs/' if placed in a subdirectory

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
