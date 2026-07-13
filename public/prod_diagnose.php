<?php
// Secure diagnostics and migration utility for production debugging
if (!isset($_GET['secret']) || $_GET['secret'] !== 'curtiss_debug_123') {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

echo "=== CURTISS ERP PRODUCTION DIAGNOSTICS ===\n\n";
echo "Database Host: " . DB_HOST . "\n";
echo "Database Name: " . DB_NAME . "\n";
echo "Database User: " . DB_USER . "\n\n";

$dbh = null;
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $dbh = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);
    echo "Database Connection: SUCCESS\n\n";
} catch (Exception $e) {
    echo "Database Connection: FAILED - " . $e->getMessage() . "\n\n";
}

if ($dbh) {
    $action = $_GET['action'] ?? '';
    if ($action === 'fix') {
        echo "=== RUNNING SCHEMA MIGRATIONS & FIXES ===\n";
        try {
            // 1. Ensure migrations table exists
            $dbh->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) UNIQUE NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "1. Ensured 'migrations' table exists.\n";

            // 2. Add columns manually if missing
            $missingColumnsFixes = [
                'customers' => [
                    'credit_limit' => "ALTER TABLE customers ADD COLUMN credit_limit DECIMAL(15,2) DEFAULT 0.00",
                    'customer_type' => "ALTER TABLE customers ADD COLUMN customer_type VARCHAR(50) DEFAULT 'Standard'",
                    'notes' => "ALTER TABLE customers ADD COLUMN notes TEXT NULL",
                    'opening_balance' => "ALTER TABLE customers ADD COLUMN opening_balance DECIMAL(15,2) DEFAULT 0.00",
                    'uuid' => "ALTER TABLE customers ADD COLUMN uuid VARCHAR(255) NULL",
                    'status' => "ALTER TABLE customers ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
                    'updated_at' => "ALTER TABLE customers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
                ],
                'products' => [
                    'sku' => "ALTER TABLE products ADD COLUMN sku VARCHAR(100) NULL",
                    'sample_code' => "ALTER TABLE products ADD COLUMN sample_code VARCHAR(100) NULL",
                    'variations_json' => "ALTER TABLE products ADD COLUMN variations_json TEXT NULL",
                    'brand' => "ALTER TABLE products ADD COLUMN brand VARCHAR(100) NULL",
                    'description' => "ALTER TABLE products ADD COLUMN description TEXT NULL",
                    'status' => "ALTER TABLE products ADD COLUMN status VARCHAR(20) DEFAULT 'active'"
                ],
                'categories' => [
                    'status' => "ALTER TABLE categories ADD COLUMN status VARCHAR(20) DEFAULT 'active'"
                ],
                'server_routes' => [
                    'main_area_id' => "ALTER TABLE server_routes ADD COLUMN main_area_id INT DEFAULT 0",
                    'status' => "ALTER TABLE server_routes ADD COLUMN status VARCHAR(20) DEFAULT 'active'"
                ],
                'customer_payments' => [
                    'status' => "ALTER TABLE customer_payments ADD COLUMN status VARCHAR(20) DEFAULT 'Active'"
                ]
            ];

            foreach ($missingColumnsFixes as $table => $cols) {
                // Check existing columns
                $existing = [];
                try {
                    $q = $dbh->query("SHOW COLUMNS FROM `$table`");
                    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                        $existing[] = strtolower($row['Field']);
                    }
                } catch (Exception $ex) {
                    echo "Table `$table` check failed (might not exist yet): " . $ex->getMessage() . "\n";
                    continue;
                }

                foreach ($cols as $colName => $alterSql) {
                    if (!in_array(strtolower($colName), $existing)) {
                        try {
                            $dbh->exec($alterSql);
                            echo "Successfully added column `$colName` to `$table`.\n";
                        } catch (Exception $colEx) {
                            echo "Failed adding column `$colName` to `$table`: " . $colEx->getMessage() . "\n";
                        }
                    }
                }
            }

            // 3. Run any remaining migrations through MigrationManager
            if (file_exists(__DIR__ . '/../core/MigrationManager.php')) {
                require_once __DIR__ . '/../core/MigrationManager.php';
                MigrationManager::run($dbh);
                echo "Successfully ran MigrationManager::run().\n";
            } else {
                echo "MigrationManager.php not found.\n";
            }
            
            echo "Database Schema Fix completed successfully!\n\n";

        } catch (Exception $e) {
            echo "Fix Error: " . $e->getMessage() . "\n\n";
        }
    }

    // Database Audit Report
    echo "=== DATABASE AUDIT & ROW COUNTS ===\n";
    $tablesToAudit = [
        'customers' => ['id', 'name', 'phone', 'whatsapp', 'address', 'territory', 'latitude', 'longitude', 'opening_balance', 'credit_limit', 'customer_type', 'notes', 'status', 'uuid', 'updated_at'],
        'items' => ['id', 'woocommerce_product_id', 'item_code', 'name', 'category_id', 'price', 'quantity_on_hand', 'quantity_reserved', 'wholesale_price', 'variations_json', 'image_path', 'brand', 'status', 'cost_price', 'sample_code', 'updated_at'],
        'item_categories' => ['id', 'name', 'description', 'updated_at', 'status'],
        'mca_areas' => ['id', 'name', 'status', 'updated_at'],
        'customer_payments' => ['id', 'customer_id', 'amount', 'status', 'payment_date'],
        'migrations' => ['id', 'migration', 'executed_at'],
        'invoices' => ['id', 'invoice_number', 'customer_id', 'invoice_date', 'total_amount', 'status'],
        'invoice_items' => ['id', 'invoice_id', 'item_id', 'quantity', 'unit_price'],
        'petty_cash_config' => ['id', 'limit_amount', 'custodian_id', 'require_approval', 'default_funding_account_id', 'reimbursement_threshold', 'created_at', 'updated_at'],
        'petty_cash_reimbursements' => ['id', 'reimbursement_date', 'amount', 'bank_account_id', 'status', 'description', 'created_by', 'approved_by', 'approved_at', 'journal_entry_id', 'created_at'],
        'petty_cash_transactions' => ['id', 'transaction_date', 'type', 'amount', 'reference', 'description', 'paid_to', 'account_id', 'status', 'attachment_path', 'created_by', 'approved_by', 'approved_at', 'journal_entry_id', 'reimbursement_id', 'created_at']
    ];

    foreach ($tablesToAudit as $table => $columns) {
        try {
            $q = $dbh->query("SHOW TABLES LIKE '$table'");
            if ($q->rowCount() === 0) {
                echo "Table `$table`: MISSING\n";
            } else {
                $countQ = $dbh->query("SELECT COUNT(*) FROM `$table`");
                $rowCount = $countQ->fetchColumn();
                echo "Table `$table`: EXISTS ($rowCount rows)\n";
                $existingColumns = [];
                $colQuery = $dbh->query("SHOW COLUMNS FROM `$table`");
                while ($col = $colQuery->fetch(PDO::FETCH_ASSOC)) {
                    $existingColumns[] = strtolower($col['Field']);
                }
                foreach ($columns as $c) {
                    $present = in_array(strtolower($c), $existingColumns);
                    echo "  - Column `$c`: " . ($present ? "OK" : "MISSING ✗") . "\n";
                }
            }
        } catch (Exception $e) {
            echo "Table `$table` Audit Error: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Log view utility
echo "=== RECENT APP ERRORS (app_errors.log) ===\n";
$appLog = __DIR__ . '/../app_errors.log';
if (file_exists($appLog)) {
    $lines = file($appLog);
    $last_lines = array_slice($lines, -50);
    echo implode("", $last_lines);
} else {
    echo "No app_errors.log file found.\n";
}
echo "\n";

echo "=== RECENT SYNC ERRORS (sync_errors.log) ===\n";
$syncLog = __DIR__ . '/../sync_errors.log';
if (file_exists($syncLog)) {
    $lines = file($syncLog);
    $last_lines = array_slice($lines, -50);
    echo implode("", $last_lines);
} else {
    echo "No sync_errors.log file found.\n";
}
echo "\n";
