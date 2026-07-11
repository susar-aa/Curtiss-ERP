<?php
require_once __DIR__ . '/../config/database.php';
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $dbh = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "CONNECTED TO DB\n\n";
    
    echo "=== TABLES ===\n";
    $stmt = $dbh->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        echo "- $t\n";
    }
    
    echo "\n=== DESCRIBE CUSTOMER_PAYMENTS ===\n";
    try {
        $stmt = $dbh->query("DESCRIBE customer_payments");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
        }
    } catch (Exception $e) {
        echo "Error describing customer_payments: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
