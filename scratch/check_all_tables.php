<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SHOW TABLES");
$tables = $db->resultSet();
echo "Tables in database:\n";
foreach ($tables as $table) {
    $arr = (array)$table;
    echo "- " . reset($arr) . "\n";
}
