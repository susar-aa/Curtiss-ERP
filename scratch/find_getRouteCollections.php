<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, '.php') === false) continue;
    
    $content = file_get_contents($path);
    if (strpos($content, 'function getRouteCollections') !== false) {
        echo "Found getRouteCollections in $path\n";
        $lines = explode("\n", $content);
        // Find line number
        foreach ($lines as $idx => $l) {
            if (strpos($l, 'function getRouteCollections') !== false) {
                $start = max(0, $idx - 5);
                $end = min(count($lines), $idx + 45);
                for ($i = $start; $i < $end; $i++) {
                    echo ($i + 1) . ": " . $lines[$i] . "\n";
                }
            }
        }
    }
}
