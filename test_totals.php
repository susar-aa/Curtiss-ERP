<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/app/Services/ReportEngine.php';

$engine = new ReportEngine();
$filters = ['start_date' => '2026-08-01', 'end_date' => '2026-08-31'];

$resCust = $engine->fetchData('sales_by_customer', $filters, 1, 100000);
$resItem = $engine->fetchData('sales_by_item', $filters, 1, 100000);

file_put_contents('customer_test.json', json_encode($resCust['rows']));
file_put_contents('item_test.json', json_encode($resItem['rows']));

echo "Cust Rows: " . count($resCust['rows']) . "\n";
echo "Item Rows: " . count($resItem['rows']) . "\n";

$cSum = 0;
foreach($resCust['rows'] as $r) $cSum += (float)$r->total_sales;
echo "Cust Row Sum: $cSum\n";

$iSum = 0;
foreach($resItem['rows'] as $r) $iSum += (float)$r->total_revenue;
echo "Item Row Sum: $iSum\n";
