<?php
require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../app/libraries/Database.php';
require __DIR__ . '/../app/Models/Supplier.php';

$sm = new Supplier();
try {
    print_r($sm->getSupplierStats(11));
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
}
