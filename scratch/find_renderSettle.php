<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
if (preg_match('/function\s+renderSettleDoubleEntries/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found renderSettleDoubleEntries at line $line\n";
    $lines = explode("\n", $content);
    for ($i = $line - 1; $i < $line + 95; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
    }
} else {
    echo "renderSettleDoubleEntries not found.\n";
}
