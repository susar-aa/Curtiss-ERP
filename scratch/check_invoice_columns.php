<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
$db = new Database();
$db->query("DESCRIBE invoices");
$cols = $db->resultSet();
foreach ($cols as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
