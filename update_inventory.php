<?php
$file = 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/InventoryController.php';
$content = file_get_contents($file);

if (strpos($content, 'require_once __DIR__ . \'/../Services/FirebaseStockService.php\';') === false) {
    $content = preg_replace('/class InventoryController extends Controller \{/', "require_once __DIR__ . '/../Services/FirebaseStockService.php';\n\nclass InventoryController extends Controller {", $content);
}

$injection = <<<PHP
            if (\$res) {
                // [Firebase RTDB & FCM] Broadcast stock update
                \$this->db->query("SELECT quantity_on_hand, quantity_reserved FROM items WHERE id = :id");
                \$this->db->bind(':id', \$id);
                \$itRow = \$this->db->single();
                if (\$itRow) {
                    (new FirebaseStockService())->broadcast_stock_update(\$id, floatval(\$itRow->quantity_on_hand), floatval(\$itRow->quantity_reserved));
                }
PHP;

$content = str_replace(
    "if (\$res) {",
    $injection,
    $content
);

file_put_contents($file, $content);
echo "Updated InventoryController.php";
