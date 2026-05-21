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
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (strpos($url, 'rep') === 0) {
    // Boot the Isolated Mobile Application
    $repApp = new RepAppRouter();
} elseif (strpos($url, 'driver') === 0) {
    // Include the new Driver App Core files
    require_once '../core/DriverController.php';
    require_once '../core/DriverAppRouter.php';
    // Boot the Isolated Driver Mobile Application
    $driverApp = new DriverAppRouter();
} else {
    // Boot the Standard Admin ERP
    $app = new App();
}

