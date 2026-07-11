<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir(__DIR__ . '/../public');
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Cache.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../app/Controllers/CustomerController.php';
require_once '../app/Controllers/CustomerPaymentController.php';

// Mock session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';

echo "Testing CustomerController constructor...\n";
try {
    $cc = new CustomerController();
    echo "Testing CustomerController::index...\n";
    // We will intercept view() method to prevent it from failing on layout/main
    // Since view() is in Controller.php, we can see if it throws any error before view()
    // Let's run a modified reflection or run it directly.
    // If it successfully reaches view(), it's fine.
    $cc->index();
    echo "CustomerController index executed successfully!\n";
} catch (Throwable $e) {
    echo "CustomerController Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\nTesting CustomerPaymentController constructor...\n";
try {
    $cpc = new CustomerPaymentController();
    echo "Testing CustomerPaymentController::index...\n";
    $cpc->index();
    echo "CustomerPaymentController index executed successfully!\n";
} catch (Throwable $e) {
    echo "CustomerPaymentController Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
