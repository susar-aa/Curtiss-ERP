<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/core/Database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/core/Cache.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/Item.php';

try {
    $db = new Database();
    $itemModel = new Item();
    
    // Fetch all item IDs
    $db->query("SELECT id FROM items");
    $items = $db->resultSet() ?: [];
    
    echo "Rebuilding variations_json for " . count($items) . " items...\n";
    
    $count = 0;
    foreach ($items as $item) {
        if ($itemModel->syncVariationsJsonColumn($item->id)) {
            $count++;
        }
    }
    
    echo "Successfully rebuilt variations_json for $count items!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
