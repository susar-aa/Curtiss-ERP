<?php
$ch = curl_init('http://localhost/CURTISS/Curtiss-ERP/loan');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo "RESPONSE:\n" . $res;
