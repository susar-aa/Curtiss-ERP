<?php
require 'vendor/autoload.php';
require 'config/database.php';
require 'core/Database.php';
require 'core/Cache.php';
require 'app/Models/Item.php';

$db = new Database();
$itemModel = new Item();

echo "Starting Migration: Removing Reserved Stock System\n";

// 1. Process all invoices with stock_status = 'reserved'
$db->query("SELECT id, invoice_number FROM invoices WHERE stock_status = 'reserved'");
$reservedInvoices = $db->resultSet();

echo "Found " . count($reservedInvoices) . " reserved invoices to process.\n";

foreach ($reservedInvoices as $inv) {
    echo "Processing Invoice: {$inv->invoice_number}\n";
    $db->query("SELECT item_id, variation_option_id, quantity FROM invoice_items WHERE invoice_id = :id");
    $db->bind(':id', $inv->id);
    $items = $db->resultSet();

    foreach ($items as $item) {
        if ($item->item_id) {
            // Deduct the physical stock since it wasn't deducted during creation
            $itemModel->updateStockDelta($item->item_id, -floatval($item->quantity), $item->variation_option_id);
            echo "  - Deducted {$item->quantity} for item {$item->item_id}\n";
        }
    }

    // Mark as deducted
    $db->query("UPDATE invoices SET stock_status = 'deducted' WHERE id = :id");
    $db->bind(':id', $inv->id);
    $db->execute();
}

// (Deliveries/Driver invoices don't have stock_status directly, it's tied to invoices)

// 3. Zero out all quantity_reserved columns in the database
echo "Zeroing out quantity_reserved globally...\n";
try {
    $db->query("UPDATE items SET quantity_reserved = 0");
    $db->execute();
    echo " - Items table quantity_reserved zeroed.\n";
} catch (Exception $e) {
    echo " - Items table error: " . $e->getMessage() . "\n";
}

try {
    $db->query("UPDATE item_variation_options SET quantity_reserved = 0");
    $db->execute();
    echo " - item_variation_options table quantity_reserved zeroed.\n";
} catch (Exception $e) {
    echo " - item_variation_options table error: " . $e->getMessage() . "\n";
}

echo "Migration Complete!\n";
