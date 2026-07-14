<?php
require_once '../config/database.php';
require_once '../core/Database.php';

$db = new Database();
$db->query("SELECT * FROM chart_of_accounts WHERE account_code = '1020'");
$row = $db->single();
echo "Account 1020:\n";
print_r($row);

$db->query("SELECT * FROM petty_cash_config");
$config = $db->resultSet();
echo "\nConfig table rows:\n";
print_r($config);
