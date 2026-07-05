<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
// Search for inputs or headers related to delivered quantity
preg_match_all('/delivered/i', $content, $matches, PREG_OFFSET_CAPTURE);
$shown_lines = [];
foreach ($matches[0] as $match) {
    $offset = $match[1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    if (!isset($shown_lines[$line])) {
        $shown_lines[$line] = true;
        // get the line content
        $lines = explode("\n", $content);
        echo "Line $line: " . trim($lines[$line - 1]) . "\n";
    }
}
