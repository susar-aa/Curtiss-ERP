<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=curtiss_erp', 'root', '');
$stmt = $pdo->query('SHOW COLUMNS FROM employees');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
