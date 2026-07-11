<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SHOW TABLES");
$tables = $db->resultSet();
foreach ($tables as $t) {
    $row = (array)$t;
    $tableName = current($row);
    try {
        $db->query("SELECT COUNT(*) as cnt FROM `$tableName`");
        $cnt = $db->single()->cnt ?? 0;
        if ($cnt > 0) {
            echo "Table '$tableName' has $cnt rows\n";
        }
    } catch (Throwable $e) {}
}
