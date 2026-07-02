<?php
require_once 'includes/db.php';

try {
    $pdo = db();
    
    // Fix missing owner_id column
    try {
        $pdo->exec("ALTER TABLE houses ADD COLUMN owner_id INT(11) UNSIGNED DEFAULT NULL AFTER id");
    } catch (PDOException $e) {
        // ignore if already exists
    }

    echo "<h3 style='color:green;'>🎉 Database Schema Fixed!</h3>";
    echo "<p>The missing 'owner_id' column has been added.</p>";
    echo "<br><a href='admin/index.php'>Go to Admin Dashboard</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
