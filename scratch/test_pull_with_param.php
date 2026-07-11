<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir(__DIR__ . '/../public');

$_GET['user_id'] = 16;
$_GET['api_sync'] = 1;
$_GET['last_sync'] = '2020-01-01 00:00:00';

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Cache.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../app/Controllers/RepDashboardController.php';

echo "Running sync_pull with user_id = 16 and last_sync = 2020-01-01 00:00:00\n";
try {
    $controller = new RepDashboardController();
    $controller->sync_pull();
} catch (Throwable $e) {
    echo "Caught exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
