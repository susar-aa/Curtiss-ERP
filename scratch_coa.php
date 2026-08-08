<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$db = new Database();
$db->query("SELECT id, account_code, account_name, account_type FROM chart_of_accounts");
$accounts = $db->resultSet();

foreach ($accounts as $acc) {
    echo "{$acc->id}: {$acc->account_code} - {$acc->account_name} ({$acc->account_type})\n";
}
