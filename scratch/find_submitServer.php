<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
if (preg_match('/function\s+submitServerDeliveryProcess/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found submitServerDeliveryProcess at line $line\n";
    $lines = explode("\n", $content);
    for ($i = $line - 1; $i < $line + 70; $i++) {
        echo ($i + 1) . ": " . $lines[$i] . "\n";
    }
} else {
    echo "submitServerDeliveryProcess not found.\n";
}
