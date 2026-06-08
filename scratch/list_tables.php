<?php
require_once 'config/database.php';
require_once 'core/Database.php';
$db = new Database();

echo "--- cheques ---\n";
$db->query('DESCRIBE cheques');
$rows = $db->resultSet();
foreach ($rows as $row) {
    echo "{$row->Field} | {$row->Type} | {$row->Null} | {$row->Key} | {$row->Default}\n";
}

echo "--- expenses ---\n";
$db->query('DESCRIBE expenses');
$rows = $db->resultSet();
foreach ($rows as $row) {
    echo "{$row->Field} | {$row->Type} | {$row->Null} | {$row->Key} | {$row->Default}\n";
}
