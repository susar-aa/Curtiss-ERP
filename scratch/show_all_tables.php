<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("DESCRIBE supplier_payment_allocations");
foreach ($db->resultSet() as $row) {
    echo "Field: " . $row->Field . " | Type: " . $row->Type . "\n";
}
echo "\n--- SUPPLIER PAYMENTS ---\n";
$db->query("DESCRIBE supplier_payments");
foreach ($db->resultSet() as $row) {
    echo "Field: " . $row->Field . " | Type: " . $row->Type . "\n";
}
