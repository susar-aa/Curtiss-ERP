<?php
$data = [
    'suppliers' => [],
    'selected_supplier' => (object)[
        'id' => 11,
        'name' => 'Test Supplier',
        'email' => 'test@test.com',
        'phone' => '1234567890',
        'address' => '123 Test St',
        'outstanding_balance' => 0
    ],
    'stats' => (object)[
        'total_billed' => 100,
        'total_paid' => 50,
        'outstanding' => 50
    ],
    'ledger' => [],
    'pos' => [],
    'products' => []
];
define('APP_URL', 'http://localhost');
ob_start();
try {
    require 'app/Views/suppliers/index.php';
    file_put_contents('scratch/output.html', ob_get_clean());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
}
