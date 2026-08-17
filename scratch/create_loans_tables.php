<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `loans` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `bank_account_id` int(11) NULL, 
          `liability_account_id` int(11) NOT NULL, 
          `lender_name` varchar(150) NOT NULL,
          `loan_number` varchar(100) DEFAULT NULL,
          `principal_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          `interest_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
          `loan_start_date` date NOT NULL,
          `loan_term_months` int(11) DEFAULT NULL,
          `repayment_frequency` enum('Monthly','Weekly','Quarterly','Yearly') DEFAULT 'Monthly',
          `first_payment_date` date DEFAULT NULL,
          `maturity_date` date DEFAULT NULL,
          `processing_fees` decimal(15,2) DEFAULT '0.00',
          `status` enum('Pending','Active','Closed','Defaulted') DEFAULT 'Pending',
          `notes` text,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `loan_repayments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `loan_id` int(11) NOT NULL,
          `payment_date` date NOT NULL,
          `principal_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          `interest_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          `bank_charges` decimal(15,2) NOT NULL DEFAULT '0.00',
          `total_payment` decimal(15,2) NOT NULL DEFAULT '0.00',
          `bank_account_id` int(11) NOT NULL,
          `reference` varchar(100) DEFAULT NULL,
          `notes` text,
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          FOREIGN KEY (`loan_id`) REFERENCES `loans`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Tables created successfully.\n";
} catch (Exception $e) { echo $e->getMessage(); }
