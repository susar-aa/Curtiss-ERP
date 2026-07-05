<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/inventory/index.php');
preg_match_all('/\bquantity_on_hand\b/i', $content, $m1, PREG_OFFSET_CAPTURE);
preg_match_all('/\bqty\b/i', $content, $m2, PREG_OFFSET_CAPTURE);

echo "quantity_on_hand occurrences: " . count($m1[0]) . "\n";
foreach ($m1[0] as $match) {
    $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
    echo "Line $line: " . trim(explode("\n", $content)[$line - 1]) . "\n";
}

echo "\nqty occurrences: " . count($m2[0]) . "\n";
foreach ($m2[0] as $match) {
    $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
    echo "Line $line: " . trim(explode("\n", $content)[$line - 1]) . "\n";
}
