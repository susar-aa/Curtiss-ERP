<?php
// Mock variables that the view expects
$data = [
    'suppliers' => [],
    'selected_supplier' => null,
    'stats' => (object)['total_pos' => 0, 'total_po_amount' => 0, 'total_billed' => 0, 'total_paid' => 0, 'total_returned' => 0, 'outstanding' => 0, 'opening_balance' => 0],
    'ledger' => [],
    'pos' => [],
    'products' => [
        (object)['sku' => '123', 'product_name' => 'Test Product', 'price' => 10, 'quantity_on_hand' => 5] // missing cost_price
    ],
    'error' => '',
    'success' => ''
];
$sup = $data['selected_supplier'];
define('APP_URL', 'http://localhost/CURTISS');

ob_start();
try {
    include __DIR__ . '/../app/Views/suppliers/index.php';
    $output = ob_get_clean();
    
    if (strpos($output, '<script>') !== false) {
        echo "Script tag found in output.\n";
        // Show the last 500 characters of the output
        echo substr($output, -500);
    } else {
        echo "Script tag NOT found in output!\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
