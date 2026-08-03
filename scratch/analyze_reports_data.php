<?php
require_once 'config/database.php';
require_once 'core/Database.php';
$db = new Database();

echo "=== USER ROLES ===\n";
$db->query("SELECT id, username, role, employee_id FROM users");
$users = $db->resultSet();
foreach ($users as $u) {
    echo "ID: {$u->id}, Username: {$u->username}, Role: {$u->role}, EmpID: " . ($u->employee_id ?? 'NULL') . "\n";
}

echo "\n=== EMPLOYEES ROLES / DESIGNATIONS ===\n";
$db->query("SELECT id, first_name, last_name, job_title, status FROM employees");
$emps = $db->resultSet();
foreach ($emps as $e) {
    echo "ID: {$e->id}, Name: {$e->first_name} {$e->last_name}, Title: {$e->job_title}, Status: {$e->status}\n";
}

echo "\n=== INVOICES CREATED_BY / REP_ROUTE_ID / REP COLUMNS ===\n";
$db->query("SHOW COLUMNS FROM invoices");
$invCols = $db->resultSet();
foreach ($invCols as $col) {
    if (stripos($col->Field, 'rep') !== false || stripos($col->Field, 'user') !== false || stripos($col->Field, 'created') !== false || stripos($col->Field, 'route') !== false) {
        echo "Invoice col: {$col->Field} ({$col->Type})\n";
    }
}

echo "\n=== ROUTES TABLE vs REP_DAILY_ROUTES ===\n";
$db->query("SHOW TABLES LIKE '%route%'");
$routeTables = $db->resultSet();
print_r($routeTables);

$db->query("SELECT * FROM routes LIMIT 5");
$routes = $db->resultSet();
echo "Routes master sample:\n";
print_r($routes);

echo "\n=== CUSTOMER OUTSTANDING COMPARISON ===\n";
$db->query("
    SELECT c.id, c.name,
        COALESCE((SELECT SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) 
                  FROM invoices i WHERE i.customer_id = c.id AND i.status != 'Voided'), 0) as total_invoiced,
        COALESCE((SELECT SUM(p.amount) FROM customer_payments p WHERE p.customer_id = c.id AND p.status = 'Active'), 0) as total_paid,
        COALESCE((SELECT SUM(cn.total_amount) FROM credit_notes cn WHERE cn.customer_id = c.id AND cn.status = 'Approved'), 0) as total_cn,
        COALESCE((SELECT SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) 
                  FROM invoices i WHERE i.customer_id = c.id AND i.status != 'Paid' AND i.status != 'Voided'), 0) as unpaid_invoices_sum
    FROM customers c
    LIMIT 10
");
$custSample = $db->resultSet();
foreach ($custSample as $cs) {
    $custCenterOutstanding = $cs->total_invoiced - $cs->total_paid - $cs->total_cn;
    echo "Customer: {$cs->name} | CustCenter Out: {$custCenterOutstanding} | Unpaid Invoices Total: {$cs->unpaid_invoices_sum} | Invoiced: {$cs->total_invoiced} | Paid: {$cs->total_paid} | CN: {$cs->total_cn}\n";
}
