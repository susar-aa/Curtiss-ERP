-- SQL Migration: Add opening_balance_date column to customers table
-- Run this on your MySQL server to support Customer opening balance tracking.

ALTER TABLE `customers` ADD COLUMN `opening_balance_date` DATE DEFAULT NULL AFTER `opening_balance`;
