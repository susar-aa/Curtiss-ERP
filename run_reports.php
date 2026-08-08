<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/app/Services/ReportEngine.php';

$engine = new ReportEngine();
$pdo = new PDO("mysql:host=127.0.0.1;dbname=curtiss_erp;charset=utf8mb4", 'root', '');

// Reps
$stmt = $pdo->query("SELECT DISTINCT rep_route_id FROM invoices WHERE invoice_date BETWEEN '2026-08-01' AND '2026-08-31' AND rep_route_id IS NOT NULL");
$reps = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach($reps as $r) {
    $filters = ['start_date' => '2026-08-01', 'end_date' => '2026-08-31', 'rep' => $r];
    $resCust = $engine->fetchData('sales_by_customer', $filters, 1, 100);
    $resItem = $engine->fetchData('sales_by_item', $filters, 1, 100);
    $cTot = round((float)($resCust['grand_totals']['total_sales'] ?? 0), 2);
    $iTot = round((float)($resItem['grand_totals']['total_revenue'] ?? 0), 2);
    if ($cTot !== $iTot) echo "MISMATCH for Rep $r! Cust: $cTot, Item: $iTot\n";
}

echo "Done checking reps.\n";
