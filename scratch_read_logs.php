<?php
$files = [
    'C:/xampp/php/logs/php_error.log',
    'C:/xampp/php/logs/php_error_log',
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/apache/logs/access.log',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "=== XAMPP Log File: $file ===\n";
        $content = file($file);
        $last_lines = array_slice($content, -30);
        echo implode("", $last_lines);
        echo "\n";
    }
}
