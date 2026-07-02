<?php
require_once 'includes/db.php';

try {
    $pdo = db();
    
    // Update admin password
    $admin_hash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@ohrs.com'")->execute([$admin_hash]);
    
    // Update customer password
    $cust_hash = password_hash('Test@1234', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE email = 'ali@example.com'")->execute([$cust_hash]);
    
    echo "<h3 style='color:green;'>🎉 Passwords successfully fixed!</h3>";
    echo "<p><b>Admin Login:</b> admin@ohrs.com / Admin@123</p>";
    echo "<p><b>Customer Login:</b> ali@example.com / Test@1234</p>";
    echo "<br><a href='login.php'>Click here to login</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
