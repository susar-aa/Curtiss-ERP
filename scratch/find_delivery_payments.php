<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/RepTrackingController.php');
if (preg_match('/public\s+function\s+api_process_delivery_visit/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found api_process_delivery_visit at line $line\n";
    $lines = explode("\n", $content);
    for ($i = $line - 1; $i < $line + 150; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
    }
} else {
    echo "api_process_delivery_visit not found.\n";
}
