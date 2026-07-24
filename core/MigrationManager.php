<?php

class MigrationManager {
    private static $initialized = false;

    /**
     * List of all system migrations.
     * The key is the unique migration identifier.
     * The value is either a SQL string or a callable function.
     */
    private static function getMigrations() {
        return [
            'create_migrations_table' => "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) UNIQUE NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'customers_username' => "ALTER TABLE customers ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER email",
            'customers_password' => "ALTER TABLE customers ADD COLUMN password VARCHAR(255) NULL AFTER username",
            'create_wholesaler_requests' => "
                CREATE TABLE IF NOT EXISTS wholesaler_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    business_name VARCHAR(150) NOT NULL,
                    address TEXT NOT NULL,
                    contact_number VARCHAR(50) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    email_address VARCHAR(150) NOT NULL UNIQUE,
                    username VARCHAR(100) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    notes TEXT NULL,
                    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
                    linked_customer_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (linked_customer_id) REFERENCES customers(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_ecommerce_retail_customers' => "
                CREATE TABLE IF NOT EXISTS ecommerce_retail_customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) NOT NULL UNIQUE,
                    username VARCHAR(100) UNIQUE NULL,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(50) NULL,
                    address TEXT NULL,
                    city VARCHAR(100) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'add_username_to_ecommerce_retail_customers' => "ALTER TABLE ecommerce_retail_customers ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER email",
            'add_ecommerce_store_url_to_company_settings' => "ALTER TABLE company_settings ADD COLUMN ecommerce_store_url VARCHAR(255) NULL DEFAULT ''",
            'add_facebook_page_id_to_company_settings' => "ALTER TABLE company_settings ADD COLUMN facebook_page_id VARCHAR(100) NULL DEFAULT ''",
            'add_facebook_access_token_to_company_settings' => "ALTER TABLE company_settings ADD COLUMN facebook_access_token TEXT NULL",
            'add_tax_number_to_company_settings' => "ALTER TABLE company_settings ADD COLUMN tax_number VARCHAR(100) NULL",
            'customers_status' => "ALTER TABLE customers ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER notes",
            'item_categories_status' => "ALTER TABLE item_categories ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
            'item_categories_woo_category_id' => "ALTER TABLE item_categories ADD COLUMN woo_category_id INT NULL DEFAULT NULL",
            'item_categories_description' => "ALTER TABLE item_categories ADD COLUMN description TEXT NULL",
            'mca_areas_status' => "ALTER TABLE mca_areas ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
            'items_quantity_reserved' => "ALTER TABLE items ADD COLUMN quantity_reserved INT DEFAULT 0 AFTER quantity_on_hand",
            'item_variation_options_quantity_reserved' => "ALTER TABLE item_variation_options ADD COLUMN quantity_reserved INT DEFAULT 0 AFTER quantity_on_hand",
            'invoice_items_variation_option_id' => "ALTER TABLE invoice_items ADD COLUMN variation_option_id INT NULL DEFAULT NULL AFTER item_id",
            'audit_logs_record_id' => "ALTER TABLE audit_logs ADD COLUMN record_id INT NULL DEFAULT NULL",
            'audit_logs_old_values' => "ALTER TABLE audit_logs ADD COLUMN old_values LONGTEXT NULL DEFAULT NULL",
            'audit_logs_new_values' => "ALTER TABLE audit_logs ADD COLUMN new_values LONGTEXT NULL DEFAULT NULL",
            'audit_logs_browser_device' => "ALTER TABLE audit_logs ADD COLUMN browser_device VARCHAR(255) NULL DEFAULT NULL",
            'deliveries_selected_credit_invoices' => "ALTER TABLE deliveries ADD COLUMN selected_credit_invoices TEXT NULL",
            'deliveries_secondary_rep_route_id' => "ALTER TABLE deliveries ADD COLUMN secondary_rep_route_id INT NULL AFTER rep_route_id",
            'deliveries_reconciliation_json' => "ALTER TABLE deliveries ADD COLUMN reconciliation_json TEXT NULL",
            'deliveries_return_stock_json' => "ALTER TABLE deliveries ADD COLUMN return_stock_json TEXT NULL",
            'deliveries_accounting_entries_json' => "ALTER TABLE deliveries ADD COLUMN accounting_entries_json TEXT NULL",
            'rep_daily_routes_notes' => "ALTER TABLE rep_daily_routes ADD COLUMN notes TEXT NULL",
            'invoices_stock_status' => "ALTER TABLE invoices ADD COLUMN stock_status VARCHAR(20) DEFAULT 'deducted' AFTER status",
            'invoices_notes' => "ALTER TABLE invoices ADD COLUMN notes TEXT NULL AFTER global_discount_type",
            'invoice_items_item_id' => "ALTER TABLE invoice_items ADD COLUMN item_id INT NULL DEFAULT NULL AFTER invoice_id",
            'invoices_cheque_date' => "ALTER TABLE invoices ADD COLUMN cheque_date DATE NULL AFTER due_date",
            'invoices_payment_term_id' => "ALTER TABLE invoices ADD COLUMN payment_term_id INT NULL DEFAULT NULL AFTER due_date",
            'invoices_uuid' => "ALTER TABLE invoices ADD COLUMN uuid VARCHAR(100) UNIQUE NULL AFTER invoice_number",
            'sales_orders_payment_term_id' => "ALTER TABLE sales_orders ADD COLUMN payment_term_id INT NULL DEFAULT NULL AFTER due_date",
            'goods_receipt_notes_receipt_number' => "ALTER TABLE goods_receipt_notes ADD COLUMN receipt_number VARCHAR(100) NULL AFTER grn_number",
            'goods_receipt_notes_is_approved' => "ALTER TABLE goods_receipt_notes ADD COLUMN is_approved TINYINT(1) DEFAULT 0 AFTER notes",
            'goods_receipt_notes_approved_by' => "ALTER TABLE goods_receipt_notes ADD COLUMN approved_by INT NULL AFTER is_approved",
            'coa_account_category' => "ALTER TABLE chart_of_accounts ADD COLUMN account_category VARCHAR(50) NULL DEFAULT NULL AFTER account_type",
            'coa_account_category_seed' => function(PDO $dbh) {
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Current Asset' WHERE account_type = 'Asset' AND (account_code < '1500' OR account_code >= '1600')");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Fixed Asset' WHERE account_type = 'Asset' AND account_code >= '1500' AND account_code < '1600'");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Current Liability' WHERE account_type = 'Liability' AND account_code < '2500'");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Long-term Liability' WHERE account_type = 'Liability' AND account_code >= '2500'");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Equity' WHERE account_type = 'Equity'");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Revenue' WHERE account_type = 'Revenue'");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Cost of Goods Sold' WHERE account_type = 'Expense' AND account_code = '5000'");
                $dbh->exec("UPDATE chart_of_accounts SET account_category = 'Operating Expense' WHERE account_type = 'Expense' AND account_code != '5000'");
                return true;
            },
            'transactions_description' => "ALTER TABLE transactions ADD COLUMN description VARCHAR(255) NULL DEFAULT NULL AFTER credit",
            'fy_start_date' => function(PDO $dbh) {
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM financial_years LIKE 'start_date'");
                    $rowCount = $q->rowCount();
                    $q->closeCursor();
                    if ($rowCount == 0) {
                        $dbh->exec("ALTER TABLE financial_years ADD COLUMN start_date DATE NULL AFTER year_name");
                        $dbh->exec("UPDATE financial_years SET start_date = DATE_SUB(end_date, INTERVAL 1 YEAR) WHERE start_date IS NULL");
                        $dbh->exec("ALTER TABLE financial_years MODIFY COLUMN start_date DATE NOT NULL");
                    }
                } catch (PDOException $e) {
                    return false;
                }
                return true;
            },
            'accounting_indexes' => function(PDO $dbh) {
                try {
                    $q1 = $dbh->query("SHOW INDEX FROM transactions WHERE Key_name = 'idx_account_journal'");
                    $rowCount1 = $q1->rowCount();
                    $q1->closeCursor();
                    if ($rowCount1 == 0) {
                        $dbh->exec("CREATE INDEX idx_account_journal ON transactions (account_id, journal_entry_id)");
                    }

                    $q2 = $dbh->query("SHOW INDEX FROM journal_entries WHERE Key_name = 'idx_date_closed'");
                    $rowCount2 = $q2->rowCount();
                    $q2->closeCursor();
                    if ($rowCount2 == 0) {
                        $dbh->exec("CREATE INDEX idx_date_closed ON journal_entries (entry_date, is_closed)");
                    }

                    $dbh->exec("UPDATE transactions SET debit = 0.00 WHERE debit IS NULL");
                    $dbh->exec("UPDATE transactions SET credit = 0.00 WHERE credit IS NULL");
                    
                    $dbh->exec("ALTER TABLE transactions MODIFY COLUMN debit DECIMAL(15,2) NOT NULL DEFAULT 0.00");
                    $dbh->exec("ALTER TABLE transactions MODIFY COLUMN credit DECIMAL(15,2) NOT NULL DEFAULT 0.00");
                } catch (PDOException $e) {
                    return false;
                }
                return true;
            },
            'drop_journal_lines' => "DROP TABLE IF EXISTS journal_lines",
            
            // Item model self-healing columns
            'items_variations_json' => "ALTER TABLE items ADD COLUMN variations_json TEXT NULL",
            'items_image_path' => "ALTER TABLE items ADD COLUMN image_path VARCHAR(255) NULL",
            'items_additional_images' => "ALTER TABLE items ADD COLUMN additional_images TEXT NULL",
            'items_barcode' => "ALTER TABLE items ADD COLUMN barcode VARCHAR(100) NULL",
            'items_category_id' => "ALTER TABLE items ADD COLUMN category_id INT NULL",
            'items_warehouse_id' => "ALTER TABLE items ADD COLUMN warehouse_id INT NULL",
            'items_vendor_id' => "ALTER TABLE items ADD COLUMN vendor_id INT NULL",
            'items_cost_price' => "ALTER TABLE items ADD COLUMN cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'items_brand' => "ALTER TABLE items ADD COLUMN brand VARCHAR(100) NULL",
            'items_warehouse' => "ALTER TABLE items ADD COLUMN warehouse VARCHAR(100) NULL",
            'items_alert_qty' => "ALTER TABLE items ADD COLUMN alert_qty INT NOT NULL DEFAULT 5",
            'items_unit' => "ALTER TABLE items ADD COLUMN unit VARCHAR(20) NOT NULL DEFAULT 'pcs'",
            'items_status' => "ALTER TABLE items ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'",
            'items_weight' => "ALTER TABLE items ADD COLUMN weight VARCHAR(50) NULL",
            'items_sync_woo' => "ALTER TABLE items ADD COLUMN sync_woo TINYINT NOT NULL DEFAULT 1",
            'items_sample_code' => "ALTER TABLE items ADD COLUMN sample_code VARCHAR(100) NULL",
            'items_price' => "ALTER TABLE items ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'items_wholesale_price' => "ALTER TABLE items ADD COLUMN wholesale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'items_item_code' => "ALTER TABLE items ADD COLUMN item_code VARCHAR(100) NULL",
            'items_name' => "ALTER TABLE items ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT ''",
            'items_qty' => "SELECT 1",
            'items_description' => "ALTER TABLE items ADD COLUMN description TEXT NULL",
            'create_stock_batches' => "
                CREATE TABLE IF NOT EXISTS stock_batches (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    item_id INT NOT NULL,
                    variation_option_id INT NULL DEFAULT NULL,
                    grn_id INT NULL DEFAULT NULL,
                    quantity_received DECIMAL(15,2) NOT NULL,
                    quantity_remaining DECIMAL(15,2) NOT NULL,
                    unit_cost DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (item_id),
                    INDEX (variation_option_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_invoice_item_batches' => "
                CREATE TABLE IF NOT EXISTS invoice_item_batches (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_item_id INT NULL DEFAULT NULL,
                    sales_invoice_item_id INT NULL DEFAULT NULL,
                    stock_batch_id INT NOT NULL,
                    quantity DECIMAL(15,2) NOT NULL,
                    unit_cost DECIMAL(15,2) NOT NULL,
                    INDEX (invoice_item_id),
                    INDEX (sales_invoice_item_id),
                    INDEX (stock_batch_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'invoice_items_cost_at_sale' => "ALTER TABLE invoice_items ADD COLUMN cost_at_sale DECIMAL(15,2) DEFAULT 0.00",
            'sales_invoice_items_cost_at_sale' => "ALTER TABLE sales_invoice_items ADD COLUMN cost_at_sale DECIMAL(15,2) DEFAULT 0.00",
            'add_indexes_for_performance' => function(PDO $dbh) {
                $indexes = [
                    ['items', 'category_id', 'idx_items_category_id'],
                    ['items', 'vendor_id', 'idx_items_vendor_id'],
                    ['items', 'status', 'idx_items_status'],
                    ['items', 'barcode', 'idx_items_barcode'],
                    ['invoices', 'customer_id', 'idx_invoices_customer_id'],
                    ['invoices', 'rep_route_id', 'idx_invoices_rep_route_id'],
                    ['invoices', 'status', 'idx_invoices_status'],
                    ['invoice_items', 'invoice_id', 'idx_invoice_items_invoice_id'],
                    ['invoice_items', 'item_id', 'idx_invoice_items_item_id'],
                    ['rep_daily_routes', 'user_id', 'idx_rep_routes_user_id'],
                    ['customers', 'mca_id', 'idx_customers_mca_id'],
                    ['customers', 'status', 'idx_customers_status']
                ];
                foreach ($indexes as $idx) {
                    list($table, $col, $idxName) = $idx;
                    try {
                        $stmt = $dbh->prepare("SHOW INDEX FROM `$table` WHERE Key_name = :idx");
                        $stmt->execute([':idx' => $idxName]);
                        $hasIndex = (bool)$stmt->fetch();
                        $stmt->closeCursor();
                        if (!$hasIndex) {
                            $dbh->exec("CREATE INDEX `$idxName` ON `$table` (`$col`)");
                        }
                    } catch (PDOException $e) {
                        // Ignore if table/column does not exist
                    }
                }
                return true;
            },
            'rbac_setup' => function(PDO $dbh) {
                // 1. Create tables
                $dbh->exec("CREATE TABLE IF NOT EXISTS roles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    description VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $dbh->exec("CREATE TABLE IF NOT EXISTS role_permissions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    role_id INT NOT NULL,
                    module VARCHAR(50) NOT NULL,
                    can_view TINYINT(1) DEFAULT 0,
                    can_create_edit TINYINT(1) DEFAULT 0,
                    can_delete TINYINT(1) DEFAULT 0,
                    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                    UNIQUE KEY role_module (role_id, module)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $dbh->exec("CREATE TABLE IF NOT EXISTS user_roles (
                    user_id INT NOT NULL,
                    role_id INT NOT NULL,
                    PRIMARY KEY (user_id, role_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // 2. Seed default roles
                $defaultRoles = [
                    ['Admin', 'Full System Administrator Access'],
                    ['Office Staff', 'Standard back-office operation access'],
                    ['Driver', 'Logistics and delivery application access'],
                    ['Rep (Sales Representative)', 'Mobile sales representative app access'],
                    ['Accountant', 'Full accounting, budgeting, and financial reports access']
                ];

                $roleIds = [];
                $stmt = $dbh->prepare("INSERT INTO roles (name, description) VALUES (:name, :description) ON DUPLICATE KEY UPDATE description = VALUES(description)");
                foreach ($defaultRoles as $r) {
                    $stmt->execute([':name' => $r[0], ':description' => $r[1]]);
                }

                // Get role IDs
                $stmt = $dbh->query("SELECT id, name FROM roles");
                if ($stmt) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $roleIds[strtolower($row['name'])] = $row['id'];
                    }
                    $stmt->closeCursor();
                }

                // Seed Admin permissions: grant view, create_edit, delete to all modules
                $modules = [
                    'crm', 'customer', 'estimate', 'sales', 'creditnote', 'dunning', 'discount',
                    'reptracking', 'delivery', 'territory', 'inventory', 'category', 'variation',
                    'warehouse', 'supplier', 'purchase', 'grn', 'supplier_return', 'expenses',
                    'hrm', 'project', 'vehicle', 'cheque', 'accounting', 'customerpayment',
                    'supplierpayment', 'asset', 'report', 'ecommerce', 'settings', 'user', 'tax',
                    'paymentterm', 'audit'
                ];

                if (isset($roleIds['admin'])) {
                    $adminId = $roleIds['admin'];
                    $permStmt = $dbh->prepare("INSERT INTO role_permissions (role_id, module, can_view, can_create_edit, can_delete) 
                                               VALUES (:role_id, :module, 1, 1, 1) 
                                               ON DUPLICATE KEY UPDATE can_view = 1, can_create_edit = 1, can_delete = 1");
                    foreach ($modules as $m) {
                        $permStmt->execute([':role_id' => $adminId, ':module' => $m]);
                    }
                }

                // Seed Accountant permissions
                if (isset($roleIds['accountant'])) {
                    $accId = $roleIds['accountant'];
                    $accPerms = [
                        'accounting' => [1, 1, 1],
                        'customerpayment' => [1, 1, 1],
                        'supplierpayment' => [1, 1, 1],
                        'expenses' => [1, 1, 1],
                        'report' => [1, 1, 0],
                        'sales' => [1, 1, 0]
                    ];
                    $permStmt = $dbh->prepare("INSERT INTO role_permissions (role_id, module, can_view, can_create_edit, can_delete) 
                                               VALUES (:role_id, :module, :can_view, :can_create_edit, :can_delete) 
                                               ON DUPLICATE KEY UPDATE can_view = VALUES(can_view), can_create_edit = VALUES(can_create_edit), can_delete = VALUES(can_delete)");
                    foreach ($accPerms as $m => $p) {
                        $permStmt->execute([':role_id' => $accId, ':module' => $m, ':can_view' => $p[0], ':can_create_edit' => $p[1], ':can_delete' => $p[2]]);
                    }
                }

                // Seed Representative permissions
                if (isset($roleIds['rep (sales representative)'])) {
                    $repId = $roleIds['rep (sales representative)'];
                    $repPerms = [
                        'crm' => [1, 1, 0],
                        'customer' => [1, 1, 0],
                        'estimate' => [1, 1, 0],
                        'sales' => [1, 1, 0],
                        'reptracking' => [1, 1, 0]
                    ];
                    $permStmt = $dbh->prepare("INSERT INTO role_permissions (role_id, module, can_view, can_create_edit, can_delete) 
                                               VALUES (:role_id, :module, :can_view, :can_create_edit, :can_delete) 
                                               ON DUPLICATE KEY UPDATE can_view = VALUES(can_view), can_create_edit = VALUES(can_create_edit), can_delete = VALUES(can_delete)");
                    foreach ($repPerms as $m => $p) {
                        $permStmt->execute([':role_id' => $repId, ':module' => $m, ':can_view' => $p[0], ':can_create_edit' => $p[1], ':can_delete' => $p[2]]);
                    }
                }

                // Seed Driver permissions
                if (isset($roleIds['driver'])) {
                    $driverId = $roleIds['driver'];
                    $driverPerms = [
                        'delivery' => [1, 1, 0]
                    ];
                    $permStmt = $dbh->prepare("INSERT INTO role_permissions (role_id, module, can_view, can_create_edit, can_delete) 
                                               VALUES (:role_id, :module, :can_view, :can_create_edit, :can_delete) 
                                               ON DUPLICATE KEY UPDATE can_view = VALUES(can_view), can_create_edit = VALUES(can_create_edit), can_delete = VALUES(can_delete)");
                    foreach ($driverPerms as $m => $p) {
                        $permStmt->execute([':role_id' => $driverId, ':module' => $m, ':can_view' => $p[0], ':can_create_edit' => $p[1], ':can_delete' => $p[2]]);
                    }
                }

                // 3. Migrate existing users from `users.role` to `user_roles`
                $userStmt = $dbh->query("SELECT id, role FROM users");
                if ($userStmt) {
                    $userRolesStmt = $dbh->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
                    while ($user = $userStmt->fetch(PDO::FETCH_ASSOC)) {
                        $roleName = strtolower(trim($user['role']));
                        $targetRoleId = null;
                        if ($roleName === 'admin') {
                            $targetRoleId = $roleIds['admin'] ?? null;
                        } elseif ($roleName === 'accountant') {
                            $targetRoleId = $roleIds['accountant'] ?? null;
                        } elseif ($roleName === 'driver') {
                            $targetRoleId = $roleIds['driver'] ?? null;
                        } elseif ($roleName === 'rep') {
                            $targetRoleId = $roleIds['rep (sales representative)'] ?? null;
                        } else {
                            $targetRoleId = $roleIds['office staff'] ?? null;
                        }

                        if ($targetRoleId) {
                            $userRolesStmt->execute([':user_id' => $user['id'], ':role_id' => $targetRoleId]);
                        }
                    }
                    $userStmt->closeCursor();
                }
                return true;
            },
            'create_recurring_journal_tables' => "
                CREATE TABLE IF NOT EXISTS recurring_journal_templates (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    template_name VARCHAR(150) NOT NULL,
                    frequency VARCHAR(50) NOT NULL,
                    day_of_month INT NOT NULL DEFAULT 1,
                    description TEXT,
                    is_active TINYINT(1) DEFAULT 1,
                    last_posted_date DATE DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'create_recurring_journal_lines' => "
                CREATE TABLE IF NOT EXISTS recurring_journal_lines (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    template_id INT NOT NULL,
                    account_id INT NOT NULL,
                    debit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    credit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    description VARCHAR(255) NULL,
                    FOREIGN KEY (template_id) REFERENCES recurring_journal_templates(id) ON DELETE CASCADE,
                    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'drop_items_qty' => function(PDO $dbh) {
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM items LIKE 'qty'");
                    $rowCount = $q->rowCount();
                    $q->closeCursor();
                    if ($rowCount > 0) {
                        $dbh->exec("ALTER TABLE items DROP COLUMN qty");
                    }
                } catch (PDOException $e) {
                    return false;
                }
                return true;
            },
            'drop_items_cost' => function(PDO $dbh) {
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM items LIKE 'cost'");
                    $rowCount = $q->rowCount();
                    $q->closeCursor();
                    if ($rowCount > 0) {
                        $dbh->exec("ALTER TABLE items DROP COLUMN cost");
                    }
                } catch (PDOException $e) {
                    return false;
                }
                return true;
            },
            'make_users_email_nullable' => "ALTER TABLE users MODIFY email VARCHAR(100) NULL",
            'create_deleted_invoices_table' => "
                CREATE TABLE IF NOT EXISTS deleted_invoices (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_number VARCHAR(50) NOT NULL,
                    customer_name VARCHAR(150) NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    deleted_user_name VARCHAR(100) NOT NULL,
                    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    delete_reason TEXT NOT NULL,
                    record_type VARCHAR(20) NOT NULL DEFAULT 'Invoice'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_sales_orders_table' => "
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_sales_order_items_table' => "
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'add_accessible_apps_to_users_table' => "ALTER TABLE users ADD COLUMN accessible_apps VARCHAR(255) DEFAULT 'ERP System' AFTER status",
            'customers_credit_limit' => "ALTER TABLE customers ADD COLUMN credit_limit DECIMAL(15,2) DEFAULT 0.00 AFTER territory",
            'customers_customer_type' => "ALTER TABLE customers ADD COLUMN customer_type VARCHAR(50) DEFAULT 'Standard' AFTER credit_limit",
            'customers_notes' => "ALTER TABLE customers ADD COLUMN notes TEXT NULL AFTER customer_type",
            'customers_opening_balance' => "ALTER TABLE customers ADD COLUMN opening_balance DECIMAL(15,2) DEFAULT 0.00 AFTER credit_limit",
            'customers_uuid' => "ALTER TABLE customers ADD COLUMN uuid VARCHAR(255) NULL",
            'customers_updated_at' => "ALTER TABLE customers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            'items_updated_at' => "ALTER TABLE items ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            'item_categories_updated_at' => "ALTER TABLE item_categories ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            'mca_areas_updated_at' => "ALTER TABLE mca_areas ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            'create_delivery_picking_items' => "
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'delivery_picking_items_final_verify' => "
                ALTER TABLE delivery_picking_items
                ADD COLUMN final_loaded_qty DECIMAL(10,2) DEFAULT NULL,
                ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
                ADD COLUMN variance DECIMAL(10,2) DEFAULT 0,
                ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL,
                ADD COLUMN verified_by INT(11) DEFAULT NULL
            ",
            'pending_collections_is_verified' => "ALTER TABLE pending_collections ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0",
            'pending_collections_is_flagged' => "ALTER TABLE pending_collections ADD COLUMN is_flagged TINYINT(1) NOT NULL DEFAULT 0",
            'pending_collections_adjusted_amount' => "ALTER TABLE pending_collections ADD COLUMN adjusted_amount DECIMAL(12,2) NULL",
            'pending_collections_verification_notes' => "ALTER TABLE pending_collections ADD COLUMN verification_notes TEXT NULL",
            'pending_collections_verified_by' => "ALTER TABLE pending_collections ADD COLUMN verified_by INT NULL",
            'pending_collections_verified_at' => "ALTER TABLE pending_collections ADD COLUMN verified_at DATETIME NULL",
            'pending_collections_mobile_local_id' => "ALTER TABLE pending_collections ADD COLUMN mobile_local_id INT NULL",
            'pending_collections_mobile_rep_id' => "ALTER TABLE pending_collections ADD COLUMN mobile_rep_id INT NULL",
            'pending_collections_uuid' => "ALTER TABLE pending_collections ADD COLUMN uuid VARCHAR(255) NULL",
            'pending_collections_debit_account_id' => "ALTER TABLE pending_collections ADD COLUMN debit_account_id INT NULL",
            'pending_collections_credit_account_id' => "ALTER TABLE pending_collections ADD COLUMN credit_account_id INT NULL",
            'grn_service_bills' => "
                ALTER TABLE goods_receipt_notes 
                ADD COLUMN due_date DATE NULL,
                ADD COLUMN service_period VARCHAR(100) NULL,
                ADD COLUMN amount DECIMAL(15,2) NULL,
                ADD COLUMN tax DECIMAL(15,2) NULL,
                ADD COLUMN total_amount DECIMAL(15,2) NULL,
                ADD COLUMN status ENUM('Unpaid', 'Partially Paid', 'Paid') DEFAULT 'Unpaid',
                ADD COLUMN attachment VARCHAR(255) NULL
            ",
            'create_app_releases' => "
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_saved_reports' => "
                CREATE TABLE IF NOT EXISTS saved_reports (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    report_key VARCHAR(100) NOT NULL,
                    view_name VARCHAR(255) NOT NULL,
                    filters TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_scheduled_reports' => "
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'add_status_to_customers' => "ALTER TABLE customers ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
            'add_status_to_item_categories' => "ALTER TABLE item_categories ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
            'add_status_to_mca_areas' => "ALTER TABLE mca_areas ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
            'add_status_to_customer_payments' => "ALTER TABLE customer_payments ADD COLUMN status VARCHAR(20) DEFAULT 'Active'",
            'create_petty_cash_config' => "
                CREATE TABLE IF NOT EXISTS petty_cash_config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    limit_amount DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
                    custodian_id INT NOT NULL,
                    require_approval TINYINT(1) DEFAULT 1,
                    default_funding_account_id INT NOT NULL,
                    reimbursement_threshold DECIMAL(15,2) DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'seed_petty_cash_config' => function(PDO $dbh) {
                // Find a default custodian (first Admin user, or first user in users table)
                $custodianId = 1;
                $stmt = $dbh->query("
                    SELECT u.id FROM users u 
                    LEFT JOIN user_roles ur ON u.id = ur.user_id 
                    LEFT JOIN roles r ON ur.role_id = r.id 
                    WHERE r.name = 'Admin' 
                    LIMIT 1
                ");
                if ($stmt) {
                    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $stmt->closeCursor();
                    if (!empty($rows)) {
                        $custodianId = intval($rows[0]->id);
                    }
                }

                // Find a default funding account (Asset account starting with 10 or 11, e.g. Cheque in Hand, Bank)
                $fundingAccId = 1;
                $stmt = $dbh->query("
                    SELECT id FROM chart_of_accounts 
                    WHERE account_type = 'Asset' AND (account_code LIKE '10%' OR account_code LIKE '11%') 
                    ORDER BY account_code ASC LIMIT 1
                ");
                if ($stmt) {
                    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $stmt->closeCursor();
                    if (!empty($rows)) {
                        $fundingAccId = intval($rows[0]->id);
                    } else {
                        // fallback to any asset account
                        $stmt2 = $dbh->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Asset' LIMIT 1");
                        if ($stmt2) {
                            $rows2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
                            $stmt2->closeCursor();
                            if (!empty($rows2)) {
                                $fundingAccId = intval($rows2[0]->id);
                            }
                        }
                    }
                }

                // Insert the initial config if not exists
                $stmtInsert = $dbh->prepare("
                    INSERT IGNORE INTO petty_cash_config (id, limit_amount, custodian_id, require_approval, default_funding_account_id, reimbursement_threshold) 
                    VALUES (1, 50000.00, :custodian_id, 1, :funding_acc_id, 10000.00)
                ");
                $stmtInsert->execute([
                    ':custodian_id' => $custodianId,
                    ':funding_acc_id' => $fundingAccId
                ]);
                $stmtInsert->closeCursor();
                return true;
            },
            'create_petty_cash_reimbursements' => "
                CREATE TABLE IF NOT EXISTS petty_cash_reimbursements (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    reimbursement_date DATE NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    bank_account_id INT NOT NULL,
                    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
                    description TEXT NULL,
                    created_by INT NOT NULL,
                    approved_by INT NULL,
                    approved_at DATETIME NULL,
                    journal_entry_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_petty_cash_transactions' => "
                CREATE TABLE IF NOT EXISTS petty_cash_transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    transaction_date DATE NOT NULL,
                    type ENUM('allocation', 'expense', 'reimbursement') NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    reference VARCHAR(100) NULL,
                    description TEXT NOT NULL,
                    paid_to VARCHAR(150) NULL,
                    account_id INT NULL,
                    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
                    attachment_path VARCHAR(255) NULL,
                    created_by INT NOT NULL,
                    approved_by INT NULL,
                    approved_at DATETIME NULL,
                    journal_entry_id INT NULL,
                    reimbursement_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (reimbursement_id) REFERENCES petty_cash_reimbursements(id) ON DELETE SET NULL,
                    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_petty_cash_config_history' => "
                CREATE TABLE IF NOT EXISTS petty_cash_config_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    limit_amount DECIMAL(15,2) NOT NULL,
                    custodian_id INT NOT NULL,
                    require_approval TINYINT(1) DEFAULT 1,
                    default_funding_account_id INT NOT NULL,
                    reimbursement_threshold DECIMAL(15,2) DEFAULT NULL,
                    changed_by INT NOT NULL,
                    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    action VARCHAR(50) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'upgrade_petty_cash_config_table' => function(PDO $dbh) {
                // 1. Check if table has cash_limit column (outdated schema) or lacks limit_amount
                $hasLimitAmount = false;
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM petty_cash_config LIKE 'limit_amount'");
                    if ($q) {
                        $cols = $q->fetchAll();
                        $q->closeCursor();
                        if (count($cols) > 0) {
                            $hasLimitAmount = true;
                        }
                    }
                } catch (Exception $e) {
                    // Table might not exist yet
                }

                if (!$hasLimitAmount) {
                    // Drop old table to clean up
                    $dbh->exec("DROP TABLE IF EXISTS petty_cash_config");

                    // Create table with correct schema
                    $dbh->exec("
                        CREATE TABLE petty_cash_config (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            limit_amount DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
                            custodian_id INT NOT NULL,
                            require_approval TINYINT(1) DEFAULT 1,
                            default_funding_account_id INT NOT NULL,
                            reimbursement_threshold DECIMAL(15,2) DEFAULT NULL,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                    ");

                    // Seed with defaults
                    // Find a default custodian (first Admin user, or first user in users table)
                    $custodianId = 1;
                    $stmt = $dbh->query("
                        SELECT u.id FROM users u 
                        LEFT JOIN user_roles ur ON u.id = ur.user_id 
                        LEFT JOIN roles r ON ur.role_id = r.id 
                        WHERE r.name = 'Admin' 
                        LIMIT 1
                    ");
                    if ($stmt) {
                        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                        $stmt->closeCursor();
                        if (!empty($rows)) {
                            $custodianId = intval($rows[0]->id);
                        }
                    }

                    // Find a default funding account (Asset account starting with 10 or 11, e.g. Cheque in Hand, Bank)
                    $fundingAccId = 1;
                    $stmt = $dbh->query("
                        SELECT id FROM chart_of_accounts 
                        WHERE account_type = 'Asset' AND (account_code LIKE '10%' OR account_code LIKE '11%') 
                        ORDER BY account_code ASC LIMIT 1
                    ");
                    if ($stmt) {
                        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                        $stmt->closeCursor();
                        if (!empty($rows)) {
                            $fundingAccId = intval($rows[0]->id);
                        } else {
                            // fallback to any asset account
                            $stmt2 = $dbh->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Asset' LIMIT 1");
                            if ($stmt2) {
                                $rows2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
                                $stmt2->closeCursor();
                                if (!empty($rows2)) {
                                    $fundingAccId = intval($rows2[0]->id);
                                }
                            }
                        }
                    }

                    // Insert the initial config
                    $stmtInsert = $dbh->prepare("
                        INSERT INTO petty_cash_config (id, limit_amount, custodian_id, require_approval, default_funding_account_id, reimbursement_threshold) 
                        VALUES (1, 50000.00, :custodian_id, 1, :funding_acc_id, 10000.00)
                        ON DUPLICATE KEY UPDATE limit_amount = 50000.00
                    ");
                    $stmtInsert->execute([
                        ':custodian_id' => $custodianId,
                        ':funding_acc_id' => $fundingAccId
                    ]);
                    $stmtInsert->closeCursor();
                }
                return true;
            },
            'upgrade_petty_cash_tables_columns' => function(PDO $dbh) {
                // Check and fix petty_cash_reimbursements columns
                $reimCols = [
                    'reimbursement_date' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN reimbursement_date DATE NOT NULL",
                    'amount' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN amount DECIMAL(15,2) NOT NULL",
                    'bank_account_id' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN bank_account_id INT NOT NULL",
                    'status' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'",
                    'description' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN description TEXT NULL",
                    'created_by' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN created_by INT NOT NULL",
                    'approved_by' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN approved_by INT NULL",
                    'approved_at' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN approved_at DATETIME NULL",
                    'journal_entry_id' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN journal_entry_id INT NULL",
                    'created_at' => "ALTER TABLE petty_cash_reimbursements ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
                ];

                $existingReim = [];
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM petty_cash_reimbursements");
                    if ($q) {
                        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                        $q->closeCursor();
                        foreach ($rows as $row) {
                            $existingReim[] = strtolower($row['Field']);
                        }
                    }
                } catch (Exception $e) {}

                if (!empty($existingReim)) {
                    foreach ($reimCols as $col => $sql) {
                        if (!in_array(strtolower($col), $existingReim)) {
                            try {
                                $dbh->exec($sql);
                            } catch (Exception $e) {}
                        }
                    }
                }

                // Check and fix petty_cash_transactions columns
                $txCols = [
                    'transaction_date' => "ALTER TABLE petty_cash_transactions ADD COLUMN transaction_date DATE NOT NULL",
                    'type' => "ALTER TABLE petty_cash_transactions ADD COLUMN type ENUM('allocation', 'expense', 'reimbursement') NOT NULL",
                    'amount' => "ALTER TABLE petty_cash_transactions ADD COLUMN amount DECIMAL(15,2) NOT NULL",
                    'reference' => "ALTER TABLE petty_cash_transactions ADD COLUMN reference VARCHAR(100) NULL",
                    'description' => "ALTER TABLE petty_cash_transactions ADD COLUMN description TEXT NOT NULL",
                    'paid_to' => "ALTER TABLE petty_cash_transactions ADD COLUMN paid_to VARCHAR(150) NULL",
                    'account_id' => "ALTER TABLE petty_cash_transactions ADD COLUMN account_id INT NULL",
                    'status' => "ALTER TABLE petty_cash_transactions ADD COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'",
                    'attachment_path' => "ALTER TABLE petty_cash_transactions ADD COLUMN attachment_path VARCHAR(255) NULL",
                    'created_by' => "ALTER TABLE petty_cash_transactions ADD COLUMN created_by INT NOT NULL",
                    'approved_by' => "ALTER TABLE petty_cash_transactions ADD COLUMN approved_by INT NULL",
                    'approved_at' => "ALTER TABLE petty_cash_transactions ADD COLUMN approved_at DATETIME NULL",
                    'journal_entry_id' => "ALTER TABLE petty_cash_transactions ADD COLUMN journal_entry_id INT NULL",
                    'reimbursement_id' => "ALTER TABLE petty_cash_transactions ADD COLUMN reimbursement_id INT NULL"
                ];

                $existingTx = [];
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM petty_cash_transactions");
                    if ($q) {
                        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                        $q->closeCursor();
                        foreach ($rows as $row) {
                            $existingTx[] = strtolower($row['Field']);
                        }
                    }
                } catch (Exception $e) {}

                if (!empty($existingTx)) {
                    foreach ($txCols as $col => $sql) {
                        if (!in_array(strtolower($col), $existingTx)) {
                            try {
                                $dbh->exec($sql);
                            } catch (Exception $e) {}
                        }
                    }
                }
                return true;
            },
            'create_stock_audits_tables' => "
                CREATE TABLE IF NOT EXISTS stock_audits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    audit_number VARCHAR(50) UNIQUE NOT NULL,
                    warehouse_id INT NOT NULL,
                    status ENUM('Draft', 'In Progress', 'Completed', 'Approved', 'Cancelled') DEFAULT 'Draft',
                    category_id INT NULL,
                    brand VARCHAR(100) NULL,
                    supplier_id INT NULL,
                    created_by INT NOT NULL,
                    counted_by INT NULL,
                    reviewed_by INT NULL,
                    approved_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    completed_at DATETIME NULL,
                    approved_at DATETIME NULL,
                    remarks TEXT NULL,
                    overall_remarks TEXT NULL,
                    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'create_stock_audit_items_table' => "
                CREATE TABLE IF NOT EXISTS stock_audit_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    audit_id INT NOT NULL,
                    item_id INT NOT NULL,
                    variation_option_id INT NULL DEFAULT NULL,
                    system_qty DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    physical_qty DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    difference DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    variance_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    remarks VARCHAR(255) NULL,
                    FOREIGN KEY (audit_id) REFERENCES stock_audits(id) ON DELETE CASCADE,
                    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'create_stock_adjustments_tables' => "
                CREATE TABLE IF NOT EXISTS stock_adjustments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    adjustment_number VARCHAR(50) UNIQUE NOT NULL,
                    warehouse_id INT NOT NULL,
                    reason VARCHAR(100) NOT NULL,
                    adjustment_date DATE NOT NULL,
                    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
                    created_by INT NOT NULL,
                    approved_by INT NULL,
                    approved_at DATETIME NULL,
                    remarks TEXT NULL,
                    attachment_path VARCHAR(255) NULL,
                    journal_entry_id INT NULL,
                    stock_audit_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
                    FOREIGN KEY (stock_audit_id) REFERENCES stock_audits(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'create_stock_adjustment_items_table' => "
                CREATE TABLE IF NOT EXISTS stock_adjustment_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    adjustment_id INT NOT NULL,
                    item_id INT NOT NULL,
                    variation_option_id INT NULL DEFAULT NULL,
                    quantity DECIMAL(15,2) NOT NULL,
                    unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    total_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    remarks VARCHAR(255) NULL,
                    FOREIGN KEY (adjustment_id) REFERENCES stock_adjustments(id) ON DELETE CASCADE,
                    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            'seed_stock_adjustment_accounts' => function(PDO $dbh) {
                // Check and seed 4910 Stock Adjustment Gain
                $stmt = $dbh->prepare("SELECT id FROM chart_of_accounts WHERE account_code = '4910'");
                $stmt->execute();
                if (!$stmt->fetch()) {
                    $stmtInsert = $dbh->prepare("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('4910', 'Stock Adjustment Gain', 'Revenue', 'Revenue', 0.00, 1)");
                    $stmtInsert->execute();
                }
                // Check and seed 5090 Stock Adjustment Loss
                $stmt = $dbh->prepare("SELECT id FROM chart_of_accounts WHERE account_code = '5090'");
                $stmt->execute();
                if (!$stmt->fetch()) {
                    $stmtInsert = $dbh->prepare("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('5090', 'Stock Adjustment Loss', 'Expense', 'Cost of Goods Sold', 0.00, 1)");
                    $stmtInsert->execute();
                }
                return true;
            },
            'create_route_expenses' => "
                CREATE TABLE IF NOT EXISTS route_expenses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rep_route_id INT NOT NULL,
                    vehicle_number VARCHAR(50) NULL,
                    rep_user_id INT NOT NULL,
                    expense_date DATETIME NOT NULL,
                    expense_type VARCHAR(50) NOT NULL,
                    amount DECIMAL(15,2) NOT NULL,
                    description TEXT NOT NULL,
                    payment_source VARCHAR(50) NOT NULL,
                    receipt_number VARCHAR(50) NULL,
                    petty_cash_transaction_id INT NULL,
                    journal_entry_id INT NULL,
                    created_by INT NOT NULL,
                    created_at DATETIME NOT NULL,
                    INDEX (rep_route_id),
                    INDEX (rep_user_id),
                    INDEX (petty_cash_transaction_id),
                    INDEX (journal_entry_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_fuel_types' => "
                CREATE TABLE IF NOT EXISTS fuel_types (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fuel_type VARCHAR(100) NOT NULL UNIQUE,
                    price_per_liter DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'seed_fuel_types' => function(PDO $dbh) {
                $stmt = $dbh->prepare("SELECT COUNT(*) as count FROM fuel_types");
                $stmt->execute();
                $count = intval($stmt->fetch(PDO::FETCH_OBJ)->count ?? 0);
                if ($count === 0) {
                    $defaultTypes = [
                        ['Petrol 92', 310.00],
                        ['Petrol 95', 365.00],
                        ['Auto Diesel', 320.00],
                        ['Super Diesel', 350.00]
                    ];
                    foreach ($defaultTypes as $ft) {
                        $stmtInsert = $dbh->prepare("INSERT INTO fuel_types (fuel_type, price_per_liter) VALUES (?, ?)");
                        $stmtInsert->execute($ft);
                    }
                }
                return true;
            },
            'create_fuel_price_history' => "
                CREATE TABLE IF NOT EXISTS fuel_price_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fuel_type_id INT NOT NULL,
                    price_per_liter DECIMAL(10,2) NOT NULL,
                    effective_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    created_by INT NULL,
                    INDEX (fuel_type_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'alter_vehicles_table_v2' => function(PDO $dbh) {
                $alters = [
                    'registration_number' => "ALTER TABLE vehicles ADD COLUMN registration_number VARCHAR(100) NULL AFTER vehicle_number",
                    'chassis_number' => "ALTER TABLE vehicles ADD COLUMN chassis_number VARCHAR(100) NULL AFTER registration_number",
                    'engine_number' => "ALTER TABLE vehicles ADD COLUMN engine_number VARCHAR(100) NULL AFTER chassis_number",
                    'assigned_driver_id' => "ALTER TABLE vehicles ADD COLUMN assigned_driver_id INT NULL AFTER engine_number",
                    'fuel_type_id' => "ALTER TABLE vehicles ADD COLUMN fuel_type_id INT NULL AFTER assigned_driver_id",
                    'fuel_tank_capacity' => "ALTER TABLE vehicles ADD COLUMN fuel_tank_capacity DECIMAL(10,2) NULL AFTER fuel_type_id",
                    'avg_fuel_consumption' => "ALTER TABLE vehicles ADD COLUMN avg_fuel_consumption DECIMAL(10,2) NULL AFTER fuel_tank_capacity",
                    'current_odometer' => "ALTER TABLE vehicles ADD COLUMN current_odometer INT NOT NULL DEFAULT 0 AFTER avg_fuel_consumption",
                    'next_service_mileage' => "ALTER TABLE vehicles ADD COLUMN next_service_mileage INT NULL AFTER current_odometer",
                    'insurance_expiry' => "ALTER TABLE vehicles ADD COLUMN insurance_expiry DATE NULL AFTER next_service_mileage",
                    'license_expiry' => "ALTER TABLE vehicles ADD COLUMN license_expiry DATE NULL AFTER insurance_expiry"
                ];
                foreach ($alters as $col => $sql) {
                    $stmt = $dbh->prepare("SHOW COLUMNS FROM vehicles LIKE ?");
                    $stmt->execute([$col]);
                    $exists = $stmt->fetch();
                    $stmt->closeCursor();
                    if (!$exists) {
                        $dbh->exec($sql);
                    }
                }
                $dbh->exec("ALTER TABLE vehicles MODIFY COLUMN status ENUM('Active', 'Inactive', 'Under Maintenance') DEFAULT 'Active'");
                return true;
            },
            'create_fuel_records' => "
                CREATE TABLE IF NOT EXISTS fuel_records (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    vehicle_id INT NOT NULL,
                    driver_id INT NULL,
                    odometer_reading INT NOT NULL,
                    fuel_type_id INT NOT NULL,
                    quantity DECIMAL(10,2) NOT NULL,
                    price_per_liter DECIMAL(10,2) NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL,
                    fuel_station VARCHAR(255) NULL,
                    payment_source ENUM('Petty Cash', 'Cash in Hand', 'Bank Account') NOT NULL DEFAULT 'Petty Cash',
                    bank_account_id INT NULL,
                    petty_cash_transaction_id INT NULL,
                    journal_entry_id INT NULL,
                    rep_route_id INT NULL,
                    remarks TEXT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (vehicle_id),
                    INDEX (driver_id),
                    INDEX (fuel_type_id),
                    INDEX (bank_account_id),
                    INDEX (petty_cash_transaction_id),
                    INDEX (journal_entry_id),
                    INDEX (rep_route_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'create_vehicle_history' => "
                CREATE TABLE IF NOT EXISTS vehicle_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    vehicle_id INT NOT NULL,
                    event_type VARCHAR(100) NOT NULL,
                    description TEXT NOT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (vehicle_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'item_variation_options_wholesale_price' => "ALTER TABLE item_variation_options ADD COLUMN wholesale_price DECIMAL(15,2) DEFAULT 0.00 AFTER price",
            'create_item_suppliers_table' => "
                CREATE TABLE IF NOT EXISTS item_suppliers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    item_id INT NOT NULL,
                    supplier_id INT NOT NULL,
                    supplier_sku VARCHAR(100) NULL,
                    last_cost_price DECIMAL(15,2) DEFAULT 0.00,
                    is_primary TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY item_supplier_unique (item_id, supplier_id),
                    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
                    FOREIGN KEY (supplier_id) REFERENCES vendors(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ",
            'seed_initial_item_suppliers' => "
                INSERT IGNORE INTO item_suppliers (item_id, supplier_id, last_cost_price, is_primary)
                SELECT id, vendor_id, cost_price, 1 FROM items WHERE vendor_id IS NOT NULL AND vendor_id > 0
            ",
            'item_variation_options_image_path' => "ALTER TABLE item_variation_options ADD COLUMN image_path VARCHAR(255) NULL DEFAULT NULL AFTER quantity_reserved"
        ];

    }

    /**
     * Executes pending migrations.
     */
    public static function run(PDO $dbh) {
        if (self::$initialized) {
            return;
        }

        // 1. Ensure migrations table exists
        try {
            $dbh->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) UNIQUE NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (PDOException $e) {
            // If we can't even create migrations table, return silently to preserve system uptime
            return;
        }

        // 2. Fetch already executed migrations
        $executed = [];
        try {
            $stmt = $dbh->query("SELECT migration FROM migrations");
            if ($stmt) {
                $executed = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $stmt->closeCursor();
            }
        } catch (PDOException $e) {
            return;
        }

        // 3. Run pending migrations in order
        $migrations = self::getMigrations();
        $anyRan = false;
        foreach ($migrations as $name => $sql) {
            if (in_array($name, $executed)) {
                continue;
            }

            $success = false;
            try {
                if (is_callable($sql)) {
                    $success = call_user_func($sql, $dbh);
                } else {
                    try {
                        $dbh->exec($sql);
                        $success = true;
                    } catch (PDOException $e) {
                        // Self-healing: if an ALTER TABLE ADD COLUMN ... AFTER column statement fails,
                        // try running it without the AFTER clause in case the target column order reference is missing.
                        if (strpos(strtolower($sql), ' add column ') !== false && strpos(strtolower($sql), ' after ') !== false) {
                            $cleanSql = preg_replace('/\s+after\s+\S+/i', '', $sql);
                            $dbh->exec($cleanSql);
                            $success = true;
                        } else {
                            throw $e;
                        }
                    }
                }
            } catch (PDOException $e) {
                // If migration fails because column/table already exists, we count it as success (previously manually run)
                // MySQL Error Codes: 1050 (table exists), 1060 (column exists), 1061 (duplicate key)
                $errorCode = $e->errorInfo[1] ?? 0;
                if (in_array($errorCode, [1050, 1060, 1061])) {
                    $success = true;
                } else {
                    // Log the failure to app_errors.log
                    $logFile = dirname(__DIR__) . '/app_errors.log';
                    $logContent = "[" . date('Y-m-d H:i:s') . "] Migration '$name' failed: " . $e->getMessage() . "\nSQL: " . (is_string($sql) ? $sql : "Callable") . "\n\n";
                    @file_put_contents($logFile, $logContent, FILE_APPEND);
                }
            } catch (Throwable $t) {
                // Log standard exception or error
                $logFile = dirname(__DIR__) . '/app_errors.log';
                $logContent = "[" . date('Y-m-d H:i:s') . "] Migration '$name' threw non-PDO exception: " . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n\n";
                @file_put_contents($logFile, $logContent, FILE_APPEND);
            }

            if ($success) {
                $anyRan = true;
                try {
                    $stmt = $dbh->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
                    $stmt->execute([':migration' => $name]);
                } catch (PDOException $e) {
                    // Ignore duplicate key or other errors inserting log
                }
            }
        }

        if ($anyRan) {
            if (class_exists('Cache')) {
                Cache::clear();
            }
        }

        self::$initialized = true;
    }
}
