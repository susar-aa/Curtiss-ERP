<?php
require 'config/db.php';

try {
    $pdo->beginTransaction();

    // 1. Drop constraints and tables that depend on rep_routes
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS route_loads;");
    $pdo->exec("DROP TABLE IF EXISTS route_expenses;");
    $pdo->exec("DROP TABLE IF EXISTS rep_routes;");

    // 2. Add new columns to orders
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN rep_session_id INT NULL AFTER rep_id;");
    } catch(Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN dispatch_id INT NULL AFTER assignment_id;");
    } catch(Exception $e) {}
    // Drop old assignment_id if exists
    try {
        $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_3;"); // Often foreign keys are named like this
    } catch(Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE orders DROP COLUMN assignment_id;");
    } catch(Exception $e) {}

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
        FOREIGN KEY (rep_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Add foreign key to orders for rep_session_id
    try {
        $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_order_session FOREIGN KEY (rep_session_id) REFERENCES rep_sessions(id) ON DELETE SET NULL;");
    } catch(Exception $e) {}

    // 4. Create delivery_dispatches
    $pdo->exec("CREATE TABLE IF NOT EXISTS delivery_dispatches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT NULL,
        partner_id INT NULL,
        vehicle_id VARCHAR(50) NULL,
        start_meter DECIMAL(8,1) NULL,
        end_meter DECIMAL(8,1) NULL,
        cash_collected DECIMAL(12,2) DEFAULT 0.00,
        cheque_amount DECIMAL(12,2) DEFAULT 0.00,
        credit_collected DECIMAL(12,2) DEFAULT 0.00,
        date DATE NOT NULL,
        status ENUM('loading', 'dispatched', 'completed') DEFAULT 'loading',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (driver_id) REFERENCES employees(id) ON DELETE SET NULL,
        FOREIGN KEY (partner_id) REFERENCES employees(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Add foreign key to orders for dispatch_id
    try {
        $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_order_dispatch FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE SET NULL;");
    } catch(Exception $e) {}

    // 5. Create dispatch_sessions mapping
    $pdo->exec("CREATE TABLE IF NOT EXISTS dispatch_sessions (
        dispatch_id INT NOT NULL,
        session_id INT NOT NULL,
        PRIMARY KEY (dispatch_id, session_id),
        FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE,
        FOREIGN KEY (session_id) REFERENCES rep_sessions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 6. Create dispatch_collections mapping (for selected outstanding customers)
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

    // Clear old data as requested
    $pdo->exec("TRUNCATE TABLE orders;");
    $pdo->exec("TRUNCATE TABLE order_items;");
    $pdo->exec("TRUNCATE TABLE cheques;");
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    $pdo->commit();
    echo "Migration Successful!";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Migration Failed: " . $e->getMessage();
}
