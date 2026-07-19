<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/core/Database.php';

try {
    $db = new Database();
    $db->query("UPDATE items SET updated_at = NOW()");
    $db->execute();
    echo "Successfully updated updated_at to NOW() for all items to force sync!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
