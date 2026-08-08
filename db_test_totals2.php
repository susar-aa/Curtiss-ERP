<?php
$json = file_get_contents('c:\Users\susar.aa\Downloads\sales_by_customer_20260808_191811.json');
$data = json_decode($json, true);
echo "Count: " . count($data) . "\n";
