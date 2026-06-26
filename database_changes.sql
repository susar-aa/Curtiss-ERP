-- Database Schema Upgrades for Sales Orders & Invoice Deletion Audit Log

-- 1. Create Deleted Invoices Audit Trail Table
CREATE TABLE IF NOT EXISTS deleted_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    deleted_user_name VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delete_reason TEXT NOT NULL,
    record_type VARCHAR(20) NOT NULL DEFAULT 'Invoice'
);
