<?php
/**
 * Database Connection — PDO Singleton
 * OHRS — Online House Rental System
 */

function get_env_var($key, $default = '') {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

define('DB_HOST', get_env_var('MYSQLHOST', 'localhost'));
define('DB_NAME', get_env_var('MYSQLDATABASE', 'ohrs'));
define('DB_USER', get_env_var('MYSQLUSER', 'root'));
define('DB_PASS', get_env_var('MYSQLPASSWORD', ''));
define('DB_PORT', get_env_var('MYSQLPORT', '3306'));
define('DB_CHARSET', 'utf8mb4');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Disable strict ONLY_FULL_GROUP_BY mode for this session
            $pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        } catch (PDOException $e) {
            // In production: log error, show friendly page
            die(json_encode(['error' => 'Database connection failed.', 'details' => $e->getMessage()]));
        }
    }
    return $pdo;
}
