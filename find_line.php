<?php
$lines = explode("\n", file_get_contents('app/Views/sales/index.php')); 
foreach($lines as $i => $line) { 
    if (stripos($line, 'const catalog') !== false) { 
        echo ($i+1) . ': ' . trim($line) . "\n"; 
    } 
}
