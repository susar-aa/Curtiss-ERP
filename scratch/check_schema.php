<?php
$db = new PDO('mysql:host=localhost;dbname=curtiss', 'root', '');
$stmt = $db->query('DESCRIBE invoices');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
