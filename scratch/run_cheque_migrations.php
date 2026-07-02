<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'curtiss_erp');

$db = new Database();
try {
    echo "Modifying customer_id...\n";
    $db->query("ALTER TABLE cheques MODIFY customer_id INT(11) NULL");
    $db->execute();
    
    echo "Adding vendor_id...\n";
    $db->query("ALTER TABLE cheques ADD COLUMN vendor_id INT(11) NULL AFTER customer_id");
    $db->execute();
    
    echo "Adding key for vendor_id...\n";
    $db->query("ALTER TABLE cheques ADD KEY (vendor_id)");
    $db->execute();
    
    echo "Success!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
