<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/RepTrackingController.php');
if (preg_match('/function\s+api_get_invoice_for_delivery/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found api_get_invoice_for_delivery at line $line\n";
} else {
    echo "api_get_invoice_for_delivery not found.\n";
}
