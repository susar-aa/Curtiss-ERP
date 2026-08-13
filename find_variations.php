<?php
$lines = explode("\n", file_get_contents('app/Models/Item.php')); 
foreach($lines as $i => $line) { 
    if (stripos($line, 'variations') !== false) { 
        echo ($i+1) . ': ' . trim($line) . "\n"; 
    } 
}
