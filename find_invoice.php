<?php
$lines = explode("\n", file_get_contents('app/Views/estimates/show.php')); 
foreach($lines as $i => $line) { 
    if (stripos($line, 'invoice') !== false) { 
        echo ($i+1) . ': ' . trim($line) . "\n"; 
    } 
}
