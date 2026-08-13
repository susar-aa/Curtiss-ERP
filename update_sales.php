<?php
$file = 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/SalesController.php';
$content = file_get_contents($file);

if (strpos($content, 'require_once __DIR__ . \'/../Services/FirebaseStockService.php\';') === false) {
    $content = preg_replace('/class SalesController extends Controller \{/', "require_once __DIR__ . '/../Services/FirebaseStockService.php';\n\nclass SalesController extends Controller {", $content);
}

// After $success = $invoiceModel->updateInvoiceWithAccounting(...
// And after $invoiceId = $invoiceModel->createInvoiceWithAccounting(...
$injection = <<<PHP

                            // [Firebase RTDB & FCM] Broadcast stock update for all items in the invoice
                            \$fbs = new FirebaseStockService();
                            \$notifiedItems = [];
                            foreach (\$itemsPayload as \$pl) {
                                \$parts = explode('|', \$pl['item_selection'] ?? '');
                                \$itemId = intval(\$parts[0] ?? 0);
                                if (\$itemId > 0 && !in_array(\$itemId, \$notifiedItems)) {
                                    \$this->db->query("SELECT quantity_on_hand, quantity_reserved FROM items WHERE id = :id");
                                    \$this->db->bind(':id', \$itemId);
                                    \$itRow = \$this->db->single();
                                    if (\$itRow) {
                                        \$fbs->broadcast_stock_update(\$itemId, floatval(\$itRow->quantity_on_hand), floatval(\$itRow->quantity_reserved));
                                        \$notifiedItems[] = \$itemId;
                                    }
                                }
                            }
PHP;

$content = str_replace(
    "\$this->logActivity('Edit Invoice', 'Billing'",
    $injection . "\n                            \$this->logActivity('Edit Invoice', 'Billing'",
    $content
);

$content = str_replace(
    "\$this->logActivity('Create Invoice', 'Billing'",
    $injection . "\n                            \$this->logActivity('Create Invoice', 'Billing'",
    $content
);

file_put_contents($file, $content);
echo "Updated SalesController.php";
