<?php
require 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Services/FirebaseStockService.php';
$f = new FirebaseStockService();
$ref = new ReflectionMethod('FirebaseStockService', 'get_firebase_access_token');
$ref->setAccessible(true);
$t = $ref->invoke($f);
$ch = curl_init('https://curtiss-erp-cc0c0-default-rtdb.firebaseio.com/.json?shallow=true');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $t]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($ch);
