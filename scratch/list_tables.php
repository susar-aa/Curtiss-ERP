<?php
require 'config/database.php';
require 'core/Database.php';

try {
    $db = new Database();
    $db->query("SHOW TABLES");
    $tables = $db->resultSet();
    foreach ($tables as $t) {
        $arr = (array)$t;
        echo current($arr) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
