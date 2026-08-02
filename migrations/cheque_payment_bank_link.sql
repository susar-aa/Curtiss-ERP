-- ==============================================================================
-- Curtiss-ERP: Cheque Payment Bank Link & Accounting Lifecycle Migration
-- Run this SQL on your server database (curtiss_erp)
-- ==============================================================================

-- 1. Add bank_account_id to supplier_payments to record chosen bank account
ALTER TABLE `supplier_payments` 
ADD COLUMN `bank_account_id` INT NULL DEFAULT NULL AFTER `journal_entry_id`, 
ADD INDEX (`bank_account_id`);

-- 2. Add supplier_payment_id to cheques to link issued cheques to supplier payout records
ALTER TABLE `cheques` 
ADD COLUMN `supplier_payment_id` INT NULL DEFAULT NULL AFTER `bank_account_id`, 
ADD INDEX (`supplier_payment_id`);

-- 3. Ensure 2050 Outstanding Cheques (Issued) Liability Account exists in Chart of Accounts
INSERT IGNORE INTO `chart_of_accounts` (`account_code`, `account_name`, `account_type`, `account_category`, `balance`, `is_active`) 
SELECT '2050', 'Outstanding Cheques (Issued)', 'Liability', 'Current Liability', 0.00, 1
WHERE NOT EXISTS (
    SELECT 1 FROM `chart_of_accounts` WHERE `account_code` = '2050'
);

-- 4. Backfill existing cheques with supplier_payment_id if matches exist
UPDATE `cheques` c 
JOIN `supplier_payments` p ON (c.vendor_id = p.vendor_id OR c.service_provider_id = p.service_provider_id) 
    AND c.amount = p.amount 
    AND ABS(TIMESTAMPDIFF(SECOND, c.created_at, p.created_at)) < 60
SET c.supplier_payment_id = p.id
WHERE c.supplier_payment_id IS NULL;
