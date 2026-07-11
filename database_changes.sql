-- =========================================================================
-- CURTISS ERP - CONSOLIDATED DATABASE SCHEMA UPGRADES & MIGRATIONS
-- =========================================================================
-- Execute these queries on your server/production database (curtiss.suzxlabs.com)
-- to bring it to a 100% stable state matching the latest code features.
-- Note: Standard MySQL does not support "ADD COLUMN IF NOT EXISTS".
-- If a column already exists, you can ignore the "duplicate column" warning/error.
-- =========================================================================

-- 1. Audit Trail: Create Deleted Invoices Table
CREATE TABLE IF NOT EXISTS deleted_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    deleted_user_name VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delete_reason TEXT NOT NULL,
    record_type VARCHAR(20) NOT NULL DEFAULT 'Invoice'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Sales Orders Module: Create Sales Orders & Items Tables
CREATE TABLE IF NOT EXISTS sales_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50) NULL,
    billing_type ENUM('retail', 'wholesale') NOT NULL DEFAULT 'retail',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    rep_name VARCHAR(100) NULL,
    mca VARCHAR(100) NULL,
    rep_tp VARCHAR(50) NULL,
    po_number VARCHAR(50) NULL,
    order_date DATE NOT NULL,
    due_date DATE NOT NULL,
    payment_term_id INT NULL DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_order_id INT NOT NULL,
    item_id INT NOT NULL,
    variation_option_id INT NULL DEFAULT NULL,
    sku VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    billing_price DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL,
    discount_value DECIMAL(10,2) DEFAULT 0.00,
    discount_type VARCHAR(10) DEFAULT 'Rs',
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Representative App: Add accessible_apps column to users table
ALTER TABLE users ADD COLUMN accessible_apps VARCHAR(255) DEFAULT 'ERP System' AFTER status;
ALTER TABLE users MODIFY email VARCHAR(100) NULL;

-- 4. Customer Management: Add Credit Limits, Customer Type, Notes, Opening Balance & sync tracking
ALTER TABLE customers ADD COLUMN credit_limit DECIMAL(15,2) DEFAULT 0.00 AFTER territory;
ALTER TABLE customers ADD COLUMN customer_type VARCHAR(50) DEFAULT 'Standard' AFTER credit_limit;
ALTER TABLE customers ADD COLUMN notes TEXT NULL AFTER customer_type;
ALTER TABLE customers ADD COLUMN opening_balance DECIMAL(15,2) DEFAULT 0.00 AFTER credit_limit;
ALTER TABLE customers ADD COLUMN uuid VARCHAR(255) NULL;
ALTER TABLE customers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 5. Mobile Synchronization: Add updated_at columns for incremental (delta) sync tracking
ALTER TABLE items ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE item_categories ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE mca_areas ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 6. Logistics & Deliveries: Create Delivery Picking Items Table & Final Loading Verification
CREATE TABLE IF NOT EXISTS delivery_picking_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_id INT DEFAULT NULL,
    variation_option_id INT DEFAULT NULL,
    required_qty DECIMAL(10,2) NOT NULL,
    loaded_qty DECIMAL(10,2) NOT NULL,
    is_picked TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (delivery_id),
    FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE delivery_picking_items ADD COLUMN final_loaded_qty DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE delivery_picking_items ADD COLUMN is_verified TINYINT(1) DEFAULT 0;
ALTER TABLE delivery_picking_items ADD COLUMN variance DECIMAL(10,2) DEFAULT 0;
ALTER TABLE delivery_picking_items ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE delivery_picking_items ADD COLUMN verified_by INT(11) DEFAULT NULL;

-- 7. Route Settlement: Add verification, audit, and mobile tracking to pending_collections table
ALTER TABLE pending_collections ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE pending_collections ADD COLUMN is_flagged TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE pending_collections ADD COLUMN adjusted_amount DECIMAL(12,2) NULL;
ALTER TABLE pending_collections ADD COLUMN verification_notes TEXT NULL;
ALTER TABLE pending_collections ADD COLUMN verified_by INT NULL;
ALTER TABLE pending_collections ADD COLUMN verified_at DATETIME NULL;
ALTER TABLE pending_collections ADD COLUMN mobile_local_id INT NULL;
ALTER TABLE pending_collections ADD COLUMN mobile_rep_id INT NULL;
ALTER TABLE pending_collections ADD COLUMN uuid VARCHAR(255) NULL;
ALTER TABLE pending_collections ADD COLUMN debit_account_id INT NULL;
ALTER TABLE pending_collections ADD COLUMN credit_account_id INT NULL;

-- 8. Supplier Management & GRN: Add Service Bills tracking to Goods Receipt Notes
ALTER TABLE goods_receipt_notes ADD COLUMN due_date DATE NULL;
ALTER TABLE goods_receipt_notes ADD COLUMN service_period VARCHAR(100) NULL;
ALTER TABLE goods_receipt_notes ADD COLUMN amount DECIMAL(15,2) NULL;
ALTER TABLE goods_receipt_notes ADD COLUMN tax DECIMAL(15,2) NULL;
ALTER TABLE goods_receipt_notes ADD COLUMN total_amount DECIMAL(15,2) NULL;
ALTER TABLE goods_receipt_notes ADD COLUMN status ENUM('Unpaid', 'Partially Paid', 'Paid') DEFAULT 'Unpaid';
ALTER TABLE goods_receipt_notes ADD COLUMN attachment VARCHAR(255) NULL;

-- 9. App Release Management: Create app_releases Table
CREATE TABLE IF NOT EXISTS app_releases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL UNIQUE,
    build_version INT NULL,
    version_name VARCHAR(50) NULL,
    package_name VARCHAR(255) NULL,
    app_name VARCHAR(255) NULL,
    major INT NOT NULL,
    minor INT NOT NULL,
    patch INT NOT NULL,
    release_notes TEXT NULL,
    apk_path VARCHAR(255) NOT NULL,
    force_update TINYINT(1) NOT NULL DEFAULT 0,
    is_latest TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Reporting Module: Create saved_reports and scheduled_reports Tables
CREATE TABLE IF NOT EXISTS saved_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_key VARCHAR(100) NOT NULL,
    view_name VARCHAR(255) NOT NULL,
    filters TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scheduled_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_key VARCHAR(100) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    email_recipient VARCHAR(255) NOT NULL,
    filters TEXT NOT NULL,
    last_run_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Status Fields additions (Critical for Customers list and mobile synchronization)
ALTER TABLE customers ADD COLUMN status VARCHAR(20) DEFAULT 'active';
ALTER TABLE item_categories ADD COLUMN status VARCHAR(20) DEFAULT 'active';
ALTER TABLE mca_areas ADD COLUMN status VARCHAR(20) DEFAULT 'active';
ALTER TABLE customer_payments ADD COLUMN status VARCHAR(20) DEFAULT 'Active';

-- 12. Register these migrations in the system table to prevent rerun triggers
INSERT IGNORE INTO migrations (migration) VALUES ('add_status_to_customers');
INSERT IGNORE INTO migrations (migration) VALUES ('add_status_to_item_categories');
INSERT IGNORE INTO migrations (migration) VALUES ('add_status_to_mca_areas');
INSERT IGNORE INTO migrations (migration) VALUES ('add_status_to_customer_payments');
