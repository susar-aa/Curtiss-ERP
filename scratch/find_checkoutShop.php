<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/DriverInvoice.php');
if (preg_match('/function\s+checkoutShop/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found checkoutShop at line $line\n";
    $lines = explode("\n", $content);
    for ($i = $line - 1; $i < $line + 95; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
    }
} else {
    echo "checkoutShop not found.\n";
}
