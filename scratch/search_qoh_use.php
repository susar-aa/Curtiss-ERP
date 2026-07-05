<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, '.php') === false) continue;
    
    $content = file_get_contents($path);
    $hasQoh = strpos($content, 'quantity_on_hand') !== false;
    $hasQty = strpos($content, '->qty') !== false || strpos($content, "'qty'") !== false || strpos($content, '"qty"') !== false;
    
    if ($hasQoh) {
        echo "File: $path\n";
        // Check for queries containing quantity_on_hand
        preg_match_all('/quantity_on_hand/i', $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            echo "  Line $line: " . trim(explode("\n", $content)[$line - 1]) . "\n";
        }
    }
}
