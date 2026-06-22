<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/Curtiss-ERP/app');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/UPDATE\s+items\s+SET/i', $content)) {
            echo "File: " . $file->getPathname() . "\n";
            preg_match_all('/.*UPDATE\s+items\s+SET.*/i', $content, $matches);
            foreach ($matches[0] as $match) {
                echo "  " . trim($match) . "\n";
            }
        }
    }
}
