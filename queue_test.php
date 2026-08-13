<?php
require 'vendor/autoload.php';
require 'config/database.php';
require 'core/Database.php';

$db = new Database();
$db->query('SELECT * FROM firebase_stock_sync_queue');
$queue = $db->resultSet();

if (empty($queue)) {
    echo "Queue is empty!\n";
} else {
    print_r($queue);
}
