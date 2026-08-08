<?php
require 'config/database.php';
require 'core/Database.php';
$db = new Database();

$db->query("SELECT SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_sales 
FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.status != 'Voided'");
$cust_total = $db->single()->total_sales;

$db->query("SELECT SUM(
    ii.total 
    - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (ii.total * i.global_discount_val / 100) ELSE (ii.total / NULLIF(i.total_amount, 0) * i.global_discount_val) END, 0) 
    + COALESCE((ii.total / NULLIF(i.total_amount, 0)) * i.tax_amount, 0)
) as total_revenue
FROM invoice_items ii JOIN invoices i ON ii.invoice_id = i.id LEFT JOIN items it ON ii.item_id = it.id WHERE i.status != 'Voided'");
$item_total = $db->single()->total_revenue;

echo "Customer Total: $cust_total\n";
echo "Item Total: $item_total\n";

$db->query("SELECT id, total_amount FROM invoices WHERE id NOT IN (SELECT invoice_id FROM invoice_items)");
$empty_invoices = $db->resultSet();
echo "Invoices without items: " . count($empty_invoices) . "\n";

$db->query("SELECT id, customer_id FROM invoices WHERE customer_id NOT IN (SELECT id FROM customers)");
$bad_customers = $db->resultSet();
echo "Invoices with invalid customers: " . count($bad_customers) . "\n";
