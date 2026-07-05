<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, '.php') === false) continue;
    
    $content = file_get_contents($path);
    if (strpos($content, 'INSERT INTO pending_collections') !== false) {
        echo "Found in: $path\n";
        // Print the query and surrounding lines
        $lines = explode("\n", $content);
        foreach ($lines as $idx => $l) {
            if (strpos($l, 'INSERT INTO pending_collections') !== false) {
                $start = max(0, $idx - 2);
                $end = min(count($lines), $idx + 10);
                for ($i = $start; $i < $end; $i++) {
                    echo ($i + 1) . ": " . $lines[$i] . "\n";
                }
            }
        }
    }
}
