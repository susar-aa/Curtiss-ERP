<?php
$content = file_get_contents('scratch/redesign_suppliers2.php');

// Simple replacements
$replacements = [
    'Customer' => 'Supplier',
    'customer' => 'supplier',
    'Customers' => 'Suppliers',
    'customers' => 'suppliers',
    'CUST-' => 'SUPP-',
    'cust-root' => 'sup-root',
    'cust-wrap' => 'sup-wrap',
    'cust-table' => 'sup-table',
    'showCustomerProfile' => 'showSupplierProfile',
    'closeCustomerProfile' => 'closeSupplierProfile',
    'customerProfileModal' => 'supplierProfileModal',
    'Customer Profile' => 'Supplier Profile',
    'Total Customers' => 'Total Suppliers',
    'Total Outstanding' => 'Total Payable Outstanding',
    'Owed Accounts' => 'Accounts to Settle',
    'addCustomerModal' => 'addSupplierModal',
    'add_customer' => 'add_supplier',
    'update_customer' => 'update_supplier',
    'deleteCustomerModal' => 'deleteSupplierModal',
    'delete_customer' => 'delete_supplier',
    'confirmDeleteCustomer' => 'confirmDeleteSupplier',
    'submitDeleteCustomer' => 'submitDeleteSupplier',
    'Customer Details' => 'Supplier Details',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents('scratch/redesign_suppliers2.php', $content);
echo "Replacements done.";
?>
