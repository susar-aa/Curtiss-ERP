<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../app/Models/AuditLog.php';
require_once __DIR__ . '/../app/Models/JournalEntry.php';
require_once __DIR__ . '/../app/Models/PettyCashTransaction.php';

$txModel = new PettyCashTransaction();

$data = [
    'amount' => 1000,
    'account_id' => 34,
    'paid_to' => 'aaa',
    'transaction_date' => '2026-07-14',
    'reference' => 'aa',
    'description' => 'aaa',
    'attachment_path' => null
];

$res = $txModel->recordExpense($data, 2);
if ($res === true) {
    echo "SUCCESS: Recorded successfully!\n";
} else {
    echo "FAILED: " . var_export($res, true) . "\n";
}
