<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=curtiss_erp', 'root', '');
$s = $pdo->query('SELECT DISTINCT status FROM customer_payments');
print_r($s->fetchAll(PDO::FETCH_COLUMN));
