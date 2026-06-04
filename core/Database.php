<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler
    public $stmt; // Changed to public so models can access it for complex executions
    private $error;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            
            // Self-healing database schema migrations
            try {
                // Ensure customers table has username and password columns
                $stmt = $this->dbh->query("SHOW COLUMNS FROM customers LIKE 'username'");
                if (!$stmt->fetch(PDO::FETCH_OBJ)) {
                    $this->dbh->exec("ALTER TABLE customers ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER email");
                }
                $stmt = $this->dbh->query("SHOW COLUMNS FROM customers LIKE 'password'");
                if (!$stmt->fetch(PDO::FETCH_OBJ)) {
                    $this->dbh->exec("ALTER TABLE customers ADD COLUMN password VARCHAR(255) NULL AFTER username");
                }

                // Ensure wholesaler_requests table exists
                $this->dbh->exec("CREATE TABLE IF NOT EXISTS wholesaler_requests (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Ensure ecommerce_retail_customers table exists
                $this->dbh->exec("CREATE TABLE IF NOT EXISTS ecommerce_retail_customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) NOT NULL UNIQUE,
                    username VARCHAR(100) UNIQUE NULL,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(50) NULL,
                    address TEXT NULL,
                    city VARCHAR(100) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Ensure company_settings has ecommerce_store_url
                $stmt = $this->dbh->query("SHOW TABLES LIKE 'company_settings'");
                if ($stmt->fetch(PDO::FETCH_OBJ)) {
                    $stmtCol = $this->dbh->query("SHOW COLUMNS FROM company_settings LIKE 'ecommerce_store_url'");
                    if (!$stmtCol->fetch(PDO::FETCH_OBJ)) {
                        $this->dbh->exec("ALTER TABLE company_settings ADD COLUMN ecommerce_store_url VARCHAR(255) NULL DEFAULT ''");
                    }
                }
            } catch (Exception $schemaEx) {
                // Safe fallback in case of locks/permissions
            }

        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Return JSON error if this is an AJAX request
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Database connection failed']);
                exit;
            }
            die("Database Connection Failed: " . $this->error . "<br><br>Make sure you have created the 'curtiss_erp' database in phpMyAdmin!");
        }
    }

    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    public function commit() {
        return $this->dbh->commit();
    }

    public function rollBack() {
        if ($this->dbh->inTransaction()) {
            return $this->dbh->rollBack();
        }
        return false;
    }

    public function inTransaction() {
        return $this->dbh->inTransaction();
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}