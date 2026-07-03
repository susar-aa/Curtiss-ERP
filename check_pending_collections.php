<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=curtiss_erp', 'root', '');
    $stmt = $pdo->query('DESCRIBE pending_collections');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
