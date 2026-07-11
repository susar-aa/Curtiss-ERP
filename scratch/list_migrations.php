<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SELECT migration FROM migrations");
$res = $db->resultSet();
foreach ($res as $row) {
    echo $row->migration . "\n";
}
