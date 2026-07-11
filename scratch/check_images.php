<?php
chdir(__DIR__ . '/../public');
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Database.php';

$db = new Database();

// Search items table
$db->query("SELECT id, name, image_path FROM items WHERE image_path LIKE '%falconstationery%' OR description LIKE '%falconstationery%'");
$res = $db->resultSet();
echo "Items matches:\n";
print_r($res);

// Search item_images table
$db->query("SELECT id, item_id, image_path FROM item_images WHERE image_path LIKE '%falconstationery%'");
$res2 = $db->resultSet();
echo "Item images matches:\n";
print_r($res2);
