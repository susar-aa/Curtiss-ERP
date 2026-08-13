<?php
require 'vendor/autoload.php';
require 'config/database.php';
require 'core/Database.php';

$db = new Database();
$db->query('UPDATE items SET quantity_on_hand = quantity_on_hand - 1 WHERE id = 1');
$db->execute();
echo "Updated stock for item 1.\n";
