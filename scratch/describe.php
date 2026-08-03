<?php
require 'config/database.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->query('DESCRIBE company_settings');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
