<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    echo "Database connected successfully.\n\n";

    $db->query("SELECT COUNT(*) as count FROM invoices");
    $res = $db->single();
    echo "Total invoices in database: " . $res->count . "\n\n";

    echo "--- Search by ID 277565 ---\n";
    $db->query("SELECT id, invoice_number, uuid, stock_status FROM invoices WHERE id = 277565");
    $row = $db->single();
    if ($row) {
        printf("ID: %d | Num: %s | UUID: %s | Status: %s\n", $row->id, $row->invoice_number, $row->uuid ?? 'NULL', $row->stock_status ?? 'NULL');
    } else {
        echo "Invoice with ID 277565 not found.\n";
    }

    echo "\n--- Search by invoice_number 202607170009 ---\n";
    $db->query("SELECT id, invoice_number, uuid, stock_status FROM invoices WHERE invoice_number = '202607170009'");
    $row = $db->single();
    if ($row) {
        printf("ID: %d | Num: %s | UUID: %s | Status: %s\n", $row->id, $row->invoice_number, $row->uuid ?? 'NULL', $row->stock_status ?? 'NULL');
    } else {
        echo "Invoice with number 202607170009 not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
