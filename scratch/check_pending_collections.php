<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
try {
    $db->query("DESCRIBE pending_collections");
    $cols = $db->resultSet();
    foreach ($cols as $c) {
        echo "{$c->Field} - {$c->Type}\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
