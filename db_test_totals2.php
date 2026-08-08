<?php
$host = '127.0.0.1';
$db   = 'curtiss_erp';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

$stmt = $pdo->query("SELECT id, total_amount FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE id NOT IN (SELECT invoice_id FROM invoice_items) AND i.status != 'Voided'");
$empty = $stmt->fetchAll();
echo "Empty invoices WITH valid customers: " . count($empty) . "\n";
$sum = 0;
foreach($empty as $inv) $sum += $inv['total_amount'];
echo "Sum of empty invoices total_amount: $sum\n";

