<?php
define('ROOT_DIR', 'c:/xampp/htdocs/CURTISS/Curtiss-ERP');
require_once ROOT_DIR . '/config/database.php';
require_once ROOT_DIR . '/core/Cache.php';
require_once ROOT_DIR . '/core/Database.php';

$db = new Database();
$db->query("SELECT * FROM stock_ledger WHERE item_id = 12 ORDER BY id ASC");
$ledgers = $db->resultSet() ?: [];

echo "=== Stock Ledger for Item 12 ===\n";
foreach ($ledgers as $ledger) {
    echo "ID: {$ledger->id} | Var ID: {$ledger->variation_option_id} | Type: {$ledger->transaction_type} | Ref: {$ledger->reference_number} | In: {$ledger->quantity_in} | Out: {$ledger->quantity_out} | Bal: {$ledger->running_balance} | Date: {$ledger->created_at}\n";
}
