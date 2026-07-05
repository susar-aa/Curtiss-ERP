<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/DriverInvoice.php');
if (preg_match('/function\s+updateInvoiceItemQty/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found updateInvoiceItemQty at line $line\n";
} else {
    echo "updateInvoiceItemQty not found.\n";
}

if (preg_match('/function\s+deleteInvoiceItem/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found deleteInvoiceItem at line $line\n";
} else {
    echo "deleteInvoiceItem not found.\n";
}
