<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("DESCRIBE supplier_returns");
$cols = $db->resultSet();
echo "SUPPLIER_RETURNS COLUMNS:\n";
foreach ($cols as $c) {
    echo " - " . $c->Field . " (" . $c->Type . ")\n";
}

$db->query("DESCRIBE supplier_return_items");
$cols2 = $db->resultSet();
echo "\nSUPPLIER_RETURN_ITEMS COLUMNS:\n";
foreach ($cols2 as $c) {
    echo " - " . $c->Field . " (" . $c->Type . ")\n";
}
