<?php
/**
 * Fintrix - Database Connection Configuration
 * Uses PDO for secure, prepared statement interactions.
 */

// Database credentials
$host = 'localhost';
$db_name = 'fintrix_db';
$username = 'suzxlabs';
$password = 'Susara@200611003614';

// Set DSN (Data Source Name)
$dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";

// PDO Options for error handling and data fetching
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Prevent SQL injection by turning off emulation
];

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// --- AUTO MIGRATION FOR PRE-SALES ROUTE BINDING ---
try {
    $pdo->query("SELECT 1 FROM rep_sessions LIMIT 1");
} catch (PDOException $e) {
    // If rep_sessions doesn't exist, we must run the full migration
    try {
        $pdo->beginTransaction();

        // 1. Drop constraints and tables that depend on rep_routes
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS route_loads;");
        $pdo->exec("DROP TABLE IF EXISTS route_expenses;");
        $pdo->exec("DROP TABLE IF EXISTS rep_routes;");

        // 2. Add new columns to orders
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN rep_session_id INT NULL AFTER rep_id;"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN dispatch_id INT NULL AFTER assignment_id;"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_3;"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE orders DROP COLUMN assignment_id;"); } catch(Exception $e) {}

        // 3. Create rep_sessions
        $pdo->exec("CREATE TABLE IF NOT EXISTS rep_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rep_id INT NOT NULL,
            route_id INT NOT NULL,
            start_meter DECIMAL(8,1) NULL,
            end_meter DECIMAL(8,1) NULL,
            cash_collected DECIMAL(12,2) DEFAULT 0.00,
            cheque_amount DECIMAL(12,2) DEFAULT 0.00,
            cheque_count INT DEFAULT 0,
            date DATE NOT NULL,
            status ENUM('active', 'ended', 'dispatched') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (rep_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        try { $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_order_session FOREIGN KEY (rep_session_id) REFERENCES rep_sessions(id) ON DELETE SET NULL;"); } catch(Exception $e) {}

        // Add updated_at columns if missing
        try { $pdo->exec("ALTER TABLE rep_sessions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE delivery_dispatches ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;"); } catch(Exception $e) {}

        // 4. Create delivery_dispatches
        $pdo->exec("CREATE TABLE IF NOT EXISTS delivery_dispatches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dispatch_ref VARCHAR(50) NOT NULL,
            driver_id INT NULL,
            partner_id INT NULL,
            vehicle_id VARCHAR(50) NULL,
            start_meter DECIMAL(8,1) NULL,
            end_meter DECIMAL(8,1) NULL,
            cash_collected DECIMAL(12,2) DEFAULT 0.00,
            cheque_amount DECIMAL(12,2) DEFAULT 0.00,
            credit_collected DECIMAL(12,2) DEFAULT 0.00,
            date DATE NOT NULL DEFAULT CURRENT_DATE,
            status ENUM('draft', 'loading', 'dispatched', 'completed') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (driver_id) REFERENCES employees(id) ON DELETE SET NULL,
            FOREIGN KEY (partner_id) REFERENCES employees(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        try { $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_order_dispatch FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE SET NULL;"); } catch(Exception $e) {}

        // 5. Create dispatch_sessions mapping
        $pdo->exec("CREATE TABLE IF NOT EXISTS dispatch_sessions (
            dispatch_id INT NOT NULL,
            session_id INT NOT NULL,
            PRIMARY KEY (dispatch_id, session_id),
            FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE,
            FOREIGN KEY (session_id) REFERENCES rep_sessions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 6. Create dispatch_collections mapping
        $pdo->exec("CREATE TABLE IF NOT EXISTS dispatch_collections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dispatch_id INT NOT NULL,
            customer_id INT NOT NULL,
            FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 7. Create route_expenses (recreate for dispatch)
        $pdo->exec("CREATE TABLE IF NOT EXISTS route_expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dispatch_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            description VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("TRUNCATE TABLE orders;");
        $pdo->exec("TRUNCATE TABLE order_items;");
        $pdo->exec("TRUNCATE TABLE cheques;");
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

// Ensure updated_at exists for session tracking
try { $pdo->exec("ALTER TABLE rep_sessions ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;"); } catch(Exception $e) {}
try { $pdo->exec("ALTER TABLE delivery_dispatches ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;"); } catch(Exception $e) {}
?>
