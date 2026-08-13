<?php
require_once __DIR__ . '/app/Services/FirebaseStockService.php';

$file = 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/RepDashboardController.php';
$content = file_get_contents($file);

// Replace get_fcm_access_token and broadcast_stock_update
$content = preg_replace('/private function get_fcm_access_token.*?public function broadcast_stock_update[^{]*\{(?:[^{}]*|\{(?:[^{}]*|\{[^{}]*\})*\})*\}/s', '', $content);

// Replace $this->broadcast_stock_update with $firebaseStockService->broadcast_stock_update
$content = str_replace('$this->broadcast_stock_update(', '(new FirebaseStockService())->broadcast_stock_update(', $content);

file_put_contents($file, $content);
echo "Updated RepDashboardController.php";
