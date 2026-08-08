<?php
$host = '127.0.0.1';
$db   = 'curtiss_erp';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

// Find the date range
$stmt = $pdo->query("
    SELECT MIN(invoice_date), MAX(invoice_date), SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total
    FROM invoices i JOIN customers c ON i.customer_id = c.id
    WHERE i.status != 'Voided'
    GROUP BY MONTH(invoice_date), YEAR(invoice_date)
");
$ranges = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($ranges as $r) {
    echo $r['MIN(invoice_date)'] . " to " . $r['MAX(invoice_date)'] . " = " . $r['total'] . "\n";
}
