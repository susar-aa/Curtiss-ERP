<?php
$content = file_get_contents('c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Controllers/RepTrackingController.php');
if (preg_match('/deliveryModel/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
    $offset = $matches[0][1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    echo "Found deliveryModel around line $line\n";
} else {
    echo "deliveryModel not found.\n";
}
