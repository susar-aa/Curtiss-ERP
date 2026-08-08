<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

$pdo = new PDO('mysql:host=127.0.0.1;dbname=curtiss_erp', 'root', '');
$stmt = $pdo->query("SELECT DISTINCT created_by FROM customer_payments WHERE payment_date BETWEEN '2026-08-01' AND '2026-08-31' AND status='Active'");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
