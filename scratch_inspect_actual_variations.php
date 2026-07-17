<?php
define('ROOT_DIR', 'c:/xampp/htdocs/CURTISS/Curtiss-ERP');
require_once ROOT_DIR . '/config/database.php';
require_once ROOT_DIR . '/core/Cache.php';
require_once ROOT_DIR . '/core/Database.php';

$db = new Database();

// Get items that have variation options
$db->query("SELECT DISTINCT item_id FROM item_variation_options LIMIT 5");
$optItems = $db->resultSet();

echo "=== Items with Relational Variation Options ===\n";
foreach ($optItems as $optItem) {
    $db->query("SELECT id, name, item_code, quantity_on_hand, variations_json FROM items WHERE id = :id");
    $db->bind(':id', $optItem->item_id);
    $item = $db->single();
    if ($item) {
        echo "Item ID: {$item->id} | Name: {$item->name} | Code: {$item->item_code} | Total Qty: {$item->quantity_on_hand}\n";
        echo "JSON: {$item->variations_json}\n";
        
        // Relational options
        $db->query("SELECT ivo.*, vv.value_name FROM item_variation_options ivo JOIN variation_values vv ON ivo.variation_value_id = vv.id WHERE ivo.item_id = :item_id");
        $db->bind(':item_id', $item->id);
        $options = $db->resultSet();
        echo "Relational Options:\n";
        foreach ($options as $opt) {
            echo "  - ID: {$opt->id} | SKU: {$opt->sku} | Value: {$opt->value_name} | Qty: {$opt->quantity_on_hand}\n";
        }
        echo "----------------------------------------\n";
    }
}
