<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->query("SELECT account_code, account_name, account_type FROM chart_of_accounts");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($accounts as $acc) {
        echo $acc['account_code'] . " - " . $acc['account_name'] . " (" . $acc['account_type'] . ")\n";
    }
} catch (Exception $e) { echo $e->getMessage(); }
