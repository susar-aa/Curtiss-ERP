<?php
define('ROOT_DIR', 'c:/xampp/htdocs/CURTISS/Curtiss-ERP');
require_once ROOT_DIR . '/config/database.php';
require_once ROOT_DIR . '/core/Cache.php';
require_once ROOT_DIR . '/core/Database.php';

$db = new Database();
$db->query("DESCRIBE item_variation_options");
$columns = $db->resultSet();

echo "=== Columns of item_variation_options ===\n";
foreach ($columns as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key} | Default: {$col->Default}\n";
}
