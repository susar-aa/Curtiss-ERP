<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private static $sharedDbh = null;
    private $dbh; // Database Handler
    public $stmt; // Changed to public so models can access it for complex executions
    private $error;
    private $currentSql = '';
    private $boundParams = [];

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );

        try {
            if (self::$sharedDbh === null) {
                // 1. Create a separate, temporary connection to run migrations
                $migrationDbh = new PDO($dsn, $this->user, $this->pass, $options);
                
                // Centralized, cached migration management system
                require_once __DIR__ . '/MigrationManager.php';
                MigrationManager::run($migrationDbh);
                
                // 2. Explicitly close the migration connection
                $migrationDbh = null;

                // 3. Create a clean connection for the main application queries
                self::$sharedDbh = new PDO($dsn, $this->user, $this->pass, $options);
            }
            
            $this->dbh = self::$sharedDbh;

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
        $this->currentSql = $sql;
        $this->boundParams = [];
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
        $this->boundParams[$param] = $value;
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        if (strpos(strtolower($this->currentSql), 'insert into journal_entries') !== false) {
            $date = date('Y-m-d');
            if (isset($this->boundParams[':date'])) {
                $date = $this->boundParams[':date'];
            } elseif (isset($this->boundParams[':entry_date'])) {
                $date = $this->boundParams[':entry_date'];
            }

            $manualRef = '';
            if (isset($this->boundParams[':ref']) && !is_null($this->boundParams[':ref'])) {
                $manualRef = trim((string)$this->boundParams[':ref']);
            } elseif (isset($this->boundParams[':reference']) && !is_null($this->boundParams[':reference'])) {
                $manualRef = trim((string)$this->boundParams[':reference']);
            }

            // Only auto-generate when no reference is provided
            if (empty($manualRef)) {
                $ref = $this->generateJournalReference($date);
                
                if (isset($this->boundParams[':ref'])) {
                    $this->stmt->bindValue(':ref', $ref, PDO::PARAM_STR);
                    $this->boundParams[':ref'] = $ref;
                }
                if (isset($this->boundParams[':reference'])) {
                    $this->stmt->bindValue(':reference', $ref, PDO::PARAM_STR);
                    $this->boundParams[':reference'] = $ref;
                }
            }
        }
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Database execution error: " . $e->getMessage() . " | SQL: " . $this->currentSql . " | Params: " . json_encode($this->boundParams));
        }
    }

    public function generateJournalReference($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $year = date('Y', strtotime($date));
        
        $stmt = $this->dbh->prepare("SELECT reference FROM journal_entries WHERE reference LIKE :pattern ORDER BY reference DESC LIMIT 1");
        $pattern = $year . '%';
        $stmt->bindValue(':pattern', $pattern, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $stmt->closeCursor();
        
        if ($row && !empty($row->reference)) {
            $lastRef = $row->reference;
            if (preg_match('/^' . $year . '(\d+)$/', $lastRef, $matches)) {
                $seq = intval($matches[1]) + 1;
                return $year . str_pad($seq, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $year . '001';
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        $row = $this->stmt->fetch(PDO::FETCH_OBJ);
        if ($this->stmt) {
            $this->stmt->closeCursor();
        }
        return $row;
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

    public function getDbHandler() {
        return $this->dbh;
    }
}