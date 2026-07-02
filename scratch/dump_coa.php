<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'curtiss_erp');

$db = new Database();
$db->query("SELECT id, account_code, account_name, parent_id, account_type FROM chart_of_accounts ORDER BY account_code ASC");
foreach ($db->resultSet() as $row) {
    echo "ID: " . $row->id . " | Code: " . $row->account_code . " | Name: " . $row->account_name . " | Parent: " . $row->parent_id . " | Type: " . $row->account_type . "\n";
}
