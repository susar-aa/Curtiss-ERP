<?php
$files = [
    'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/RepTrackingController.php',
    'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/DriverInvoice.php',
    'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/Delivery.php'
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    echo "=== File: $f ===\n";
    $content = file_get_contents($f);
    preg_match_all('/stock_status/i', $content, $matches, PREG_OFFSET_CAPTURE);
    $shown = [];
    foreach ($matches[0] as $match) {
        $offset = $match[1];
        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
        $lines = explode("\n", $content);
        echo "Line $line: " . trim($lines[$line - 1]) . "\n";
    }
}
