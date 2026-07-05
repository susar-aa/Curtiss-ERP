<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, '.php') === false) continue;
    
    $content = file_get_contents($path);
    if (strpos($content, 'pending_collections') !== false) {
        echo "File: $path\n";
        // Print the lines
        $lines = explode("\n", $content);
        foreach ($lines as $idx => $l) {
            if (strpos($l, 'pending_collections') !== false) {
                echo "  Line " . ($idx + 1) . ": " . trim($l) . "\n";
            }
        }
    }
}
