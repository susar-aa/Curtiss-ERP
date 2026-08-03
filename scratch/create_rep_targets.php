<?php
require 'config/database.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$sql = "CREATE TABLE IF NOT EXISTS rep_targets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    month VARCHAR(2) NOT NULL,
    year VARCHAR(4) NOT NULL,
    sales_target DECIMAL(15,2) DEFAULT 0.00,
    productive_visits_target INT DEFAULT 0,
    total_visits_target INT DEFAULT 0,
    working_days_target INT DEFAULT 0,
    collection_efficiency_target DECIMAL(5,2) DEFAULT 80.00,
    new_customers_target INT DEFAULT 5,
    credit_limit DECIMAL(15,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY rep_month_year (user_id, month, year)
);";
$pdo->exec($sql);
echo "Table created.\n";
