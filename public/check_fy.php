<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
$db = new Database();
$db->query("SELECT * FROM financial_years");
print_r($db->resultSet());
