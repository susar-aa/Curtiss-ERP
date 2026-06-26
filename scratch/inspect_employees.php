<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/core/Database.php';

$db = new Database();
$db->query("DESCRIBE employees");
foreach ($db->resultSet() as $row) {
    echo "Field: " . $row->Field . " | Type: " . $row->Type . " | Null: " . $row->Null . " | Key: " . $row->Key . " | Default: " . $row->Default . " | Extra: " . $row->Extra . "\n";
}
