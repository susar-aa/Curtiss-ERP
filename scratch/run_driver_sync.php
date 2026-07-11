<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir(__DIR__ . '/../public');

$_GET['user_id'] = 21; // Driver user ID
$_GET['api_sync'] = 1;
$_GET['url'] = 'api_sync_pull';

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Cache.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../app/Controllers/DriverDashboardController.php';

$controller = new DriverDashboardController();
$controller->api_sync_pull();
