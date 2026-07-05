<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
preg_match_all('/workspace-tab-panel/i', $content, $matches, PREG_OFFSET_CAPTURE);
foreach ($matches[0] as $match) {
    $offset = $match[1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    $lines = explode("\n", $content);
    echo "Line $line: " . trim($lines[$line - 1]) . "\n";
}
