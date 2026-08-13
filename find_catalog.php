<?php
$lines = explode("\n", file_get_contents('app/Controllers/SalesController.php')); 
foreach($lines as $i => $line) { 
    if (stripos($line, 'catalog_items') !== false) { 
        echo ($i+1) . ': ' . trim($line) . "\n"; 
    } 
}
