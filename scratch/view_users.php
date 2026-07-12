<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("DESCRIBE users");
$cols = $db->resultSet() ?: [];
foreach ($cols as $c) {
    echo "{$c->Field} - {$c->Type}\n";
}
