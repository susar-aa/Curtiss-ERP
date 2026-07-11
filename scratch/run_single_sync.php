<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir(__DIR__ . '/../public');

$uid = isset($argv[1]) ? intval($argv[1]) : 16;
$_GET['user_id'] = $uid;
$_GET['api_sync'] = 1;

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Cache.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../app/Controllers/RepDashboardController.php';

$controller = new RepDashboardController();
$controller->sync_pull();
