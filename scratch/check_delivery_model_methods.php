<?php
$file = 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/Delivery.php';
if (file_exists($file)) {
    echo "Delivery.php exists!\n";
    $content = file_get_contents($file);
    preg_match_all('/function\s+[a-zA-Z0-9_]*/i', $content, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $match) {
        $offset = $match[1];
        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
        echo "Line $line: " . $match[0] . "\n";
    }
} else {
    echo "Delivery.php NOT found.\n";
}
