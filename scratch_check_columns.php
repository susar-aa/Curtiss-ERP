<?php
define('ROOT_DIR', 'c:/xampp/htdocs/CURTISS/Curtiss-ERP');
require_once ROOT_DIR . '/config/database.php';
require_once ROOT_DIR . '/core/Cache.php';
require_once ROOT_DIR . '/core/Database.php';

$db = new Database();

echo "=== DESCRIBE stock_audit_items ===\n";
$db->query("DESCRIBE stock_audit_items");
$cols = $db->resultSet() ?: [];
foreach ($cols as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key} | Default: {$col->Default}\n";
}
