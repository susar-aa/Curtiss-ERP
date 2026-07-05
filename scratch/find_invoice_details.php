<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/DriverInvoice.php');
if (preg_match('/function\s+getInvoiceDetails/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found getInvoiceDetails at line $line\n";
} else {
    echo "getInvoiceDetails not found.\n";
}
