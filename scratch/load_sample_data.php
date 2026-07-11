<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$sqlFile = __DIR__ . '/../populate_sample_data.sql';
if (!file_exists($sqlFile)) {
    die("Error: populate_sample_data.sql does not exist.\n");
}

$sql = file_get_contents($sqlFile);
// Split SQL by semicolon, ignoring semicolons within quotes or comments can be tricky, but since our script is simple we can do basic splitting or use PDO query.
// A safer way is to use PDO execute on the entire multi-query block if MySQL driver allows it.
try {
    $dbh = $db->getDbHandler();
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
    $dbh->exec($sql);
    echo "Sample data successfully loaded into local database!\n";
} catch (Throwable $e) {
    echo "Error loading sample data: " . $e->getMessage() . "\n";
}
