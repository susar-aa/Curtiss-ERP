<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/app/Services/ReportEngine.php';
require_once __DIR__ . '/app/Models/RepPerformance.php';

$engine = new ReportEngine();
$repPerf = new RepPerformance();

$filters = ['start_date' => '2026-08-01', 'end_date' => '2026-08-31', 'rep' => 11];
$resColl = $engine->fetchData('credit_collection', $filters, 1, 100000);

$engineSum = 0;
foreach($resColl['rows'] as $r) {
    $engineSum += (float)$r->amount;
}
echo "ReportEngine Sum: $engineSum\n";

$perfData = $repPerf->calculatePerformance(11, '2026-08-01', '2026-08-31');
echo "RepPerformance Total Collections: " . $perfData['total_collections'] . "\n";
echo "RepPerformance Cash: " . $perfData['cash_collections'] . "\n";
echo "RepPerformance Cheque: " . $perfData['cheque_collections'] . "\n";
echo "RepPerformance Bank: " . $perfData['bank_collections'] . "\n";

