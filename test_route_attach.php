<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Cache.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/app/Models/Invoice.php';
require_once __DIR__ . '/app/Services/RepRouteService.php';

try {
    session_start();
    $db = new Database();
    $db->query("SELECT id FROM sales_orders WHERE status != 'Transferred' ORDER BY id DESC LIMIT 1");
    $so = $db->single();
    if (!$so) {
        die("No Sales Order found.\n");
    }
    
    $db->query("SELECT id FROM rep_daily_routes ORDER BY id DESC LIMIT 1");
    $route = $db->single();
    if (!$route) {
        die("No Route found.\n");
    }
    
    $userId = 1;
    echo "Testing attaching SO " . $so->id . " to Route " . $route->id . "\n";
    
    RepRouteService::attachInvoices($route->id, ['standard:' . $so->id], $userId);
    echo "Success!\n";
} catch (Throwable $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    if (isset($_SESSION['invoice_error'])) {
        echo "Session invoice error: " . $_SESSION['invoice_error'] . "\n";
    }
}
