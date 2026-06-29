<?php
require 'includes/db.php';

try {
    $sql = file_get_contents('database/ohrs.sql');
    
    // PDO can execute multiple statements at once natively in MySQL
    db()->exec($sql);
    
    echo "<h1>Database Imported Successfully!</h1>";
    echo "<p>You can now go to your main website URL and the system should be fully working.</p>";
    echo "<p><strong>Security Warning:</strong> Please delete this file (import.php) from your server after successful import.</p>";
} catch (PDOException $e) {
    echo "<h1>Import Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
