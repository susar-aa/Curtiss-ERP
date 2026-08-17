-- Employee Loans Table
CREATE TABLE IF NOT EXISTS `employee_loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `loan_number` varchar(50) DEFAULT NULL,
  `principal_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,2) DEFAULT '0.00',
  `loan_start_date` date DEFAULT NULL,
  `loan_term_months` int(11) DEFAULT '1',
  `repayment_frequency` enum('Monthly','Weekly') DEFAULT 'Monthly',
  `repayment_amount` decimal(15,2) DEFAULT '0.00',
  `status` enum('Pending','Approved','Active','Closed','Rejected') DEFAULT 'Pending',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_emp_loan_employee` (`employee_id`),
  CONSTRAINT `fk_emp_loan_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee Loan Repayments Table
CREATE TABLE IF NOT EXISTS `employee_loan_repayments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_loan_id` int(11) NOT NULL,
  `payroll_slip_id` int(11) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_el_repayment_loan` (`employee_loan_id`),
  CONSTRAINT `fk_el_repayment_loan` FOREIGN KEY (`employee_loan_id`) REFERENCES `employee_loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alter Payroll Runs to add status if not exists
SET @exist := (SELECT count(*) FROM information_schema.columns WHERE table_name = 'payroll_runs' AND column_name = 'status' AND table_schema = DATABASE());
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE payroll_runs ADD COLUMN status enum("Draft","Approved","Paid") DEFAULT "Draft"', 'SELECT "Already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Payroll Slips Table
CREATE TABLE IF NOT EXISTS `payroll_slips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `allowances` decimal(15,2) NOT NULL DEFAULT '0.00',
  `commissions` decimal(15,2) NOT NULL DEFAULT '0.00',
  `overtime` decimal(15,2) NOT NULL DEFAULT '0.00',
  `loan_deduction` decimal(15,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(15,2) NOT NULL DEFAULT '0.00',
  `gross_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('Draft','Approved','Paid') DEFAULT 'Draft',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ps_run` (`payroll_run_id`),
  KEY `fk_ps_employee` (`employee_id`),
  CONSTRAINT `fk_ps_run` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Link loan repayments to payroll slips
ALTER TABLE `employee_loan_repayments` ADD CONSTRAINT `fk_el_repayment_slip` FOREIGN KEY (`payroll_slip_id`) REFERENCES `payroll_slips` (`id`) ON DELETE SET NULL;
