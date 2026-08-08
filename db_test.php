<?php
require 'config/database.php';
require 'core/Database.php';
$db = new Database();
$db->query("DESCRIBE customer_payments");
print_r($db->resultSet());
