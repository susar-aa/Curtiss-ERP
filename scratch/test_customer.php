<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../app/Models/Customer.php';

try {
    $cust = new Customer();
    $res = $cust->getAllCustomers();
    echo "getAllCustomers count: " . count($res) . "\n";
    foreach ($res as $r) {
        echo "Customer: " . $r->name . ", Outstanding Balance: " . $r->outstanding_balance . "\n";
    }
} catch (Throwable $e) {
    echo "Error calling getAllCustomers: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
