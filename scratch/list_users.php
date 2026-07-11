<?php
chdir(__DIR__ . '/../public');
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Database.php';

$db = new Database();
$db->query("SELECT id, username, role, status FROM users");
$users = $db->resultSet();
print_r($users);
