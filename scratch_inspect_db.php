<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = new Database();

$tables = [
    'sales_orders' => ['id', 'order_number'],
    'invoices' => ['id', 'invoice_number'],
    'estimates' => ['id', 'estimate_number'],
    'goods_receipt_notes' => ['id', 'grn_number'],
    'purchase_orders' => ['id', 'po_number'],
    'customer_payments' => ['id', 'reference'],
    'supplier_payments' => ['id', 'reference'],
    'deposits' => ['id', 'deposit_number'],
    'journal_entries' => ['id', 'reference'],
    'credit_notes' => ['id', 'credit_note_number'],
    'debit_notes' => ['id', 'debit_note_number'],
    'stock_transfers' => ['id', 'transfer_number'],
    'stock_adjustments' => ['id', 'adjustment_number'],
    'petty_cash_transactions' => ['id', 'reference'],
    'petty_cash_reimbursements' => ['id', 'id'],
    'expenses' => ['id', 'expense_number'],
    'cheques' => ['id', 'cheque_number']
];

foreach ($tables as $table => $cols) {
    try {
        $db->query("SHOW TABLES LIKE :table");
        $db->bind(':table', $table);
        $exists = $db->single();
        if (!$exists) {
            echo "Table '$table' does NOT exist.\n";
            continue;
        }

        // Get columns to make sure they exist
        $db->query("SHOW COLUMNS FROM `$table`");
        $allCols = $db->resultSet();
        $colNames = array_map(function($c) { return $c->Field; }, $allCols);
        
        $selectCols = [];
        foreach ($cols as $col) {
            if (in_array($col, $colNames)) {
                $selectCols[] = $col;
            }
        }
        
        if (empty($selectCols)) {
            echo "Table '$table' exists but none of target columns exist. Columns: " . implode(', ', $colNames) . "\n";
            continue;
        }
        
        $sql = "SELECT " . implode(', ', $selectCols) . " FROM `$table` ORDER BY id DESC LIMIT 3";
        $db->query($sql);
        $rows = $db->resultSet();
        echo "Table '$table' records (last 3):\n";
        if (empty($rows)) {
            echo "  (No records)\n";
        } else {
            foreach ($rows as $row) {
                $parts = [];
                foreach ($selectCols as $col) {
                    $parts[] = "$col: " . ($row->$col ?? 'NULL');
                }
                echo "  " . implode(' | ', $parts) . "\n";
            }
        }
    } catch (Exception $e) {
        echo "Error checking table '$table': " . $e->getMessage() . "\n";
    }
}
