<?php
require_once '../config/database.php';
require_once '../core/Database.php';

$db = new Database();
$db->query("SELECT * FROM petty_cash_transactions ORDER BY id DESC LIMIT 5");
$rows = $db->resultSet();
echo "Last 5 transactions:\n";
print_r($rows);
