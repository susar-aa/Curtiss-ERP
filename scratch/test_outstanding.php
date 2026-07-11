<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../app/Models/Payment.php';

try {
    $pay = new Payment();
    $res = $pay->getCustomerOutstandingList();
    echo "getCustomerOutstandingList count: " . count($res) . "\n";
    foreach ($res as $r) {
        echo "Customer: " . $r->name . ", Outstanding Balance: " . $r->outstanding_balance . "\n";
    }
} catch (Throwable $e) {
    echo "Error calling getCustomerOutstandingList: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
