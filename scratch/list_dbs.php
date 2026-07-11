<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SHOW DATABASES");
$dbs = $db->resultSet();
foreach ($dbs as $d) {
    $dbName = $d->Database ?? $d->database ?? '';
    echo "Database: $dbName\n";
}
