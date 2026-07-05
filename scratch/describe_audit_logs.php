<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/core/Database.php';
try {
    $db = new Database();
    $db->query("DESCRIBE audit_logs");
    $columns = $db->resultSet();
    foreach ($columns as $col) {
        echo $col->Field . " - " . $col->Type . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
