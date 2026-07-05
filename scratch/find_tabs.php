<?php
// Let's find functions in index.php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
preg_match_all('/function\s+loadTab[a-zA-Z0-9_]*/i', $content, $matches, PREG_OFFSET_CAPTURE);
foreach ($matches[0] as $match) {
    $offset = $match[1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Line $line: " . $match[0] . "\n";
}
