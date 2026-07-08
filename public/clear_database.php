<?php
// To run this script, start your MySQL server and visit:
// http://localhost/CURTISS/Curtiss-ERP/public/clear_database.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

echo "<h2>Curtiss ERP Database Cleaner</h2>";

try {
    $db = new Database();
    $pdo = $db->getDbHandler();
    
    // Get all tables
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $keepTables = [
        'users',
        'roles',
        'role_permissions',
        'user_roles',
        'migrations',
        'company_settings',
        'chart_of_accounts'
    ];
    
    $sql = "SET FOREIGN_KEY_CHECKS = 0;\n";
    foreach ($tables as $table) {
        if (in_array(strtolower($table), $keepTables)) {
            continue;
        }
        $sql .= "TRUNCATE TABLE `$table`;\n";
    }
    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    
    echo "<h3>SQL Code to run on your Server Database:</h3>";
    echo "<textarea style='width: 100%; height: 350px; font-family: monospace; padding: 10px;'>" . htmlspecialchars($sql) . "</textarea>";
    
    // Try executing it locally on XAMPP
    $pdo->exec($sql);
    echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>Local XAMPP database cleared successfully (except users, roles, migrations, and settings)!</p>";
} catch (Exception $e) {
    echo "<p style='color: #d32f2f; font-weight: bold;'>Could not execute locally (is MySQL/XAMPP started?): " . htmlspecialchars($e->getMessage()) . "</p>";
}
