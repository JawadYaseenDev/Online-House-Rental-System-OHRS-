<?php
echo "<h2>Railway Database Setup Wizard</h2>";

$host = getenv('MYSQLHOST') ?: $_ENV['MYSQLHOST'] ?? null;

if (!$host) {
    echo "<h3 style='color:red;'>Step 1 Incomplete: Database Variables Missing!</h3>";
    echo "<p>Your app cannot see the database yet. Please do this in your Railway Dashboard:</p>";
    echo "<ol>";
    echo "<li>Click on your <b>GitHub Web App block</b> (not the MySQL one).</li>";
    echo "<li>Click the <b>Variables</b> tab at the top.</li>";
    echo "<li>Click <b>+ New Variable</b> -> <b>Add Reference</b>.</li>";
    echo "<li>Select the MySQL variables (MYSQLHOST, MYSQLUSER, etc).</li>";
    echo "<li>Wait 1 minute for Railway to deploy, then refresh this page!</li>";
    echo "</ol>";
    exit;
}

require_once 'includes/db.php';

try {
    $pdo = db();
    $sql = file_get_contents(__DIR__ . '/database/ohrs.sql');
    
    // Allow running multiple SQL statements at once
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    
    // Run the SQL
    $pdo->exec($sql);
    
    echo "<h3 style='color:green;'>🎉 Success! The database has been imported perfectly!</h3>";
    echo "<p>All your tables and test data are now live on Railway.</p>";
    echo "<p>You can now use your website: <a href='index.php'>Go to Homepage</a></p>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error importing tables</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
