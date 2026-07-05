<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Views/rep-tracking/index.php');
$queries = ['sdpInvoiceTotal', 'sdpRemainingBalance'];
foreach ($queries as $q) {
    preg_match_all('/' . $q . '/i', $content, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $match) {
        $offset = $match[1];
        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
        $lines = explode("\n", $content);
        echo "$q Line $line: " . trim($lines[$line - 1]) . "\n";
    }
}
