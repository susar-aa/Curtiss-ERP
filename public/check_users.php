<?php
require_once '../config/database.php';
require_once '../core/Database.php';

$db = new Database();
$db->query("SELECT id, username, email, role FROM users");
print_r($db->resultSet());
