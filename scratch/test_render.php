<?php
$data = [
    'suppliers' => [],
    'selected_supplier' => null,
    'stats' => null,
    'ledger' => [],
    'pos' => [],
    'products' => []
];
define('APP_URL', 'http://localhost');
ob_start();
try {
    require 'app/Views/suppliers/index.php';
    $content = ob_get_clean();
    if (strpos($content, 'showSupplierProfile') !== false) {
        echo "Function found in output";
    } else {
        echo "Function MISSING in output!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
}
