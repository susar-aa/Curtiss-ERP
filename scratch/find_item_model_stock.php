<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/Item.php');
if (preg_match('/function\s+updateStockDelta/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found updateStockDelta at line $line\n";
    $lines = explode("\n", $content);
    for ($i = $line - 1; $i < $line + 20; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
    }
} else {
    echo "updateStockDelta not found.\n";
}
