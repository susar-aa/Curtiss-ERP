<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach($tables as $table) {
        if(in_array($table, ['bank_accounts', 'chart_of_accounts', 'journal_entries', 'transactions', 'loans', 'loan_repayments'])) {
            echo "Table: $table\n";
            $cols = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            foreach($cols as $c) {
                echo "  " . $c['Field'] . " => " . $c['Type'] . "\n";
            }
        }
    }
} catch (Exception $e) { echo $e->getMessage(); }
