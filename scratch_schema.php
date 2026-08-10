<?php
$host = 'localhost';
$db   = 'curtiss_erp';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $stmt = $pdo->query("SHOW CREATE TABLE items");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Create Table'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
