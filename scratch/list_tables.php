<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SHOW TABLES");
$tables = $db->resultSet();
foreach ($tables as $t) {
    $row = (array)$t;
    echo current($row) . "\n";
}
