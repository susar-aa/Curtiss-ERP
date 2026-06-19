<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'curtiss_erp');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $dbh = new PDO($dsn, DB_USER, DB_PASS, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));

    echo "Altering tables to add uuid columns...\n";

    // 1. Alter customers
    try {
        $dbh->exec("ALTER TABLE customers ADD COLUMN uuid VARCHAR(255) UNIQUE NULL AFTER notes");
        echo "Added uuid to customers table.\n";
    } catch (Exception $e) {
        echo "customers: " . $e->getMessage() . "\n";
    }

    // 2. Alter rep_daily_routes
    try {
        $dbh->exec("ALTER TABLE rep_daily_routes ADD COLUMN uuid VARCHAR(255) UNIQUE NULL");
        echo "Added uuid to rep_daily_routes table.\n";
    } catch (Exception $e) {
        echo "rep_daily_routes: " . $e->getMessage() . "\n";
    }

    // 3. Alter invoices
    try {
        $dbh->exec("ALTER TABLE invoices ADD COLUMN uuid VARCHAR(255) UNIQUE NULL AFTER invoice_number");
        echo "Added uuid to invoices table.\n";
    } catch (Exception $e) {
        echo "invoices: " . $e->getMessage() . "\n";
    }

    // 4. Alter pending_collections
    try {
        $dbh->exec("ALTER TABLE pending_collections ADD COLUMN uuid VARCHAR(255) UNIQUE NULL");
        echo "Added uuid to pending_collections table.\n";
    } catch (Exception $e) {
        echo "pending_collections: " . $e->getMessage() . "\n";
    }

    echo "Database alignment completed successfully!\n";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
