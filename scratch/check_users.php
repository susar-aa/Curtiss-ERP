<?php
require 'config/database.php';
require 'core/Database.php';

try {
    $db = new Database();
    $db->query("SELECT * FROM users");
    $users = $db->resultSet();
    echo "Users count: " . count($users) . "\n";
    foreach ($users as $u) {
        print_r($u);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
