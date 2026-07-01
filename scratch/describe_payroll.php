<?php
class Database {
    public function __construct() {
        $this->dbh = new PDO('mysql:host=localhost;dbname=curtiss_erp', 'root', '');
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
$db->query("SHOW COLUMNS FROM expenses");
print_r($db->resultSet());
