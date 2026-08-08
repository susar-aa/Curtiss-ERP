<?php
$host = '127.0.0.1';
$db   = 'curtiss';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$stmt = $pdo->query("SELECT SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_sales 
FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.status != 'Voided'");
$cust_total = $stmt->fetch()['total_sales'];

$stmt = $pdo->query("SELECT SUM(
    ii.total 
    - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (ii.total * i.global_discount_val / 100) ELSE (ii.total / NULLIF(i.total_amount, 0) * i.global_discount_val) END, 0) 
    + COALESCE((ii.total / NULLIF(i.total_amount, 0)) * i.tax_amount, 0)
) as total_revenue
FROM invoice_items ii JOIN invoices i ON ii.invoice_id = i.id LEFT JOIN items it ON ii.item_id = it.id WHERE i.status != 'Voided'");
$item_total = $stmt->fetch()['total_revenue'];

echo "Customer Total: $cust_total\n";
echo "Item Total: $item_total\n";

$stmt = $pdo->query("SELECT id, total_amount FROM invoices WHERE id NOT IN (SELECT invoice_id FROM invoice_items)");
$empty_invoices = $stmt->fetchAll();
echo "Invoices without items: " . count($empty_invoices) . "\n";
$sum_empty = 0;
foreach($empty_invoices as $inv) $sum_empty += $inv['total_amount'];
echo "Sum of empty invoices total_amount: $sum_empty\n";

$stmt = $pdo->query("SELECT id, customer_id, total_amount FROM invoices WHERE customer_id NOT IN (SELECT id FROM customers)");
$bad_customers = $stmt->fetchAll();
echo "Invoices with invalid customers: " . count($bad_customers) . "\n";
$sum_bad = 0;
foreach($bad_customers as $inv) $sum_bad += $inv['total_amount'];
echo "Sum of bad customer invoices total_amount: $sum_bad\n";

