<?php
// Concurrency test for createInvoiceWithAccounting
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/Models/Invoice.php';
require_once __DIR__ . '/../app/Models/Item.php';
require_once __DIR__ . '/../app/Models/FIFO.php';
require_once __DIR__ . '/../app/Models/StockLedger.php';

$db = new Database();
$workerId = isset($argv[1]) ? $argv[1] : 0;

if ($workerId == 0) {
    // 1. Setup exactly 1 unit of stock
    $db->query("SELECT id FROM items LIMIT 1");
    $row = $db->single();
    if (!$row) die("No items without variations found.");
    $itemId = $row->id;

    $db->query("UPDATE items SET quantity_on_hand = 1 WHERE id = :id");
    $db->bind(':id', $itemId);
    $db->execute();

    echo "Set stock for item $itemId to 1.\n";
} else {
    // Worker needs itemId
    $db->query("SELECT id FROM items LIMIT 1");
    $row = $db->single();
    $itemId = $row->id;
}
$invoiceData = [
    'invoice_number' => 'TEST-' . time() . '-' . $workerId,
    'customer_id' => 1,
    'invoice_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d'),
    'subtotal' => 100,
    'global_discount_val' => 0,
    'global_discount_type' => 'Rs',
    'notes' => 'Concurrency Test'
];

$items = [
    [
        'item_selection' => $itemId . '|0',
        'quantity' => 1,
        'unit_price' => 100,
        'discount_value' => 0,
        'discount_type' => 'Rs',
        'total' => 100,
        'description' => 'Test Item'
    ]
];

$arAccountId = 1;
$revenueAccountId = 2;
$userId = 1;

// 3. Create a secondary process to simulate concurrent request
$script = __FILE__;
if (!isset($argv[1])) {
    echo "Starting concurrent requests...\n";
    $pipes = [];
    $proc1 = proc_open("c:\\xampp\\php\\php.exe $script 1", [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ], $pipes1);
    
    $proc2 = proc_open("c:\\xampp\\php\\php.exe $script 2", [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ], $pipes2);
    
    $out1 = stream_get_contents($pipes1[1]);
    $err1 = stream_get_contents($pipes1[2]);
    $out2 = stream_get_contents($pipes2[1]);
    $err2 = stream_get_contents($pipes2[2]);
    
    fclose($pipes1[1]); fclose($pipes1[2]);
    fclose($pipes2[1]); fclose($pipes2[2]);
    proc_close($proc1);
    proc_close($proc2);
    
    echo "Process 1 Output: " . trim($out1) . "\n";
    if ($err1) echo "Process 1 STDERR: " . trim($err1) . "\n";
    echo "Process 2 Output: " . trim($out2) . "\n";
    if ($err2) echo "Process 2 STDERR: " . trim($err2) . "\n";
    
    $db->query("SELECT quantity_on_hand FROM items WHERE id = :id");
    $db->bind(':id', $itemId);
    $finalRow = $db->single();
    echo "Final Stock: " . $finalRow->quantity_on_hand . "\n";
} else {
    // Worker
    $invoiceModel = new Invoice();
    try {
        $id = $invoiceModel->createInvoiceWithAccounting($invoiceData, $items, $arAccountId, $revenueAccountId, $userId);
        if ($id) {
            echo "Success: Created Invoice $id";
        } else {
            session_start();
            echo "Failed (returned false): " . ($_SESSION['invoice_error'] ?? '');
        }
    } catch (Exception $e) {
        echo "Failed (Exception): " . $e->getMessage();
    }
}
