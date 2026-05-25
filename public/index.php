<?php
session_start();

require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../core/App.php';

// Include the new Rep App Core files
require_once '../core/RepController.php';
require_once '../core/RepAppRouter.php';

// Check if the URL is trying to access the Mobile Rep or Driver App
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
$urlParts = $url !== '' ? explode('/', $url) : [];
$firstSegment = strtolower($urlParts[0] ?? '');

// Match route segment only — "report" must not be treated as "rep"
if ($firstSegment === 'rep') {
    // Boot the Isolated Mobile Application
    $repApp = new RepAppRouter();
} elseif ($firstSegment === 'driver') {
    // Include the new Driver App Core files
    require_once '../core/DriverController.php';
    require_once '../core/DriverAppRouter.php';
    // Boot the Isolated Driver Mobile Application
    $driverApp = new DriverAppRouter();
} else {
    // Boot the Standard Admin ERP
    $app = new App();
}

