<?php
$filepath = 'c:/xampp/htdocs/Curtiss-ERP/app/Controllers/InventoryController.php';
$content = file_get_contents($filepath);
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'UPDATE items') !== false || stripos($line, 'updateItem') !== false || stripos($line, 'addItem') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
