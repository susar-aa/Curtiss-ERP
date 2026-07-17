<?php
define('ROOT_DIR', 'c:/xampp/htdocs/CURTISS/Curtiss-ERP');
require_once ROOT_DIR . '/config/database.php';
require_once ROOT_DIR . '/core/Cache.php';
require_once ROOT_DIR . '/core/Database.php';
require_once ROOT_DIR . '/app/Models/StockAdjustment.php';

$db = new Database();
$adjModel = new StockAdjustment();

try {
    echo "Attempting to approve adjustment 16...\n";
    $approved = $adjModel->approveAdjustment(16, 1);
    if ($approved) {
        echo "Success! Adjustment approved.\n";
    } else {
        echo "Failure! approveAdjustment returned false.\n";
    }
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
}
