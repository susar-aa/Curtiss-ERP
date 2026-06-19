<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'curtiss_erp');

class Database {
    private $dbh;
    private $stmt;
    public function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        try {
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
        } catch(PDOException $e) {
            echo $e->getMessage();
        }
    }
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    public function execute() {
        return $this->stmt->execute();
    }
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
}

$db = new Database();
$tables = ['customers', 'rep_daily_routes', 'invoices', 'pending_collections'];
foreach ($tables as $tbl) {
    echo "--- Table: {$tbl} ---\n";
    try {
        $db->query("SHOW COLUMNS FROM {$tbl}");
        $cols = $db->resultSet();
        foreach ($cols as $col) {
            if (strtolower($col->Field) === 'uuid') {
                echo "FOUND UUID column!\n";
            }
            if (strtolower($col->Field) === 'mobile_local_id') {
                echo "FOUND mobile_local_id column!\n";
            }
        }
        // Let's print all columns
        $names = [];
        foreach ($cols as $col) {
            $names[] = $col->Field;
        }
        echo "Columns: " . implode(', ', $names) . "\n";
    } catch (Exception $e) {
        echo "Error checking table {$tbl}: " . $e->getMessage() . "\n";
    }
}
