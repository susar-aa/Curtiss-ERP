SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS route_loads;
DROP TABLE IF EXISTS route_expenses;
DROP TABLE IF EXISTS rep_routes;

ALTER TABLE orders ADD COLUMN rep_session_id INT NULL AFTER rep_id;
ALTER TABLE orders ADD COLUMN dispatch_id INT NULL AFTER assignment_id;

ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_3;
ALTER TABLE orders DROP COLUMN assignment_id;

CREATE TABLE IF NOT EXISTS rep_sessions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE orders ADD CONSTRAINT fk_order_session FOREIGN KEY (rep_session_id) REFERENCES rep_sessions(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS delivery_dispatches (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE orders ADD CONSTRAINT fk_order_dispatch FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS dispatch_sessions (
    dispatch_id INT NOT NULL,
    session_id INT NOT NULL,
    PRIMARY KEY (dispatch_id, session_id),
    FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES rep_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dispatch_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dispatch_id INT NOT NULL,
    customer_id INT NOT NULL,
    FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS route_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dispatch_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dispatch_id) REFERENCES delivery_dispatches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE orders;
TRUNCATE TABLE order_items;
TRUNCATE TABLE cheques;

SET FOREIGN_KEY_CHECKS = 1;
