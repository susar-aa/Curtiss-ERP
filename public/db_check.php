<?php
header('Content-Type: text/plain');
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/core/Database.php';

try {
    $db = new Database();
    
    $tables = ['vehicles', 'fuel_types', 'fuel_records', 'vehicle_history'];
    echo "=== Table Status ===\n";
    foreach ($tables as $table) {
        $db->query("SHOW TABLES LIKE :table");
        $db->bind(':table', $table);
        $res = $db->resultSet();
        if (count($res) > 0) {
            echo "Table '$table': EXISTS\n";
            // Check columns
            $db->query("DESCRIBE `$table`");
            $cols = $db->resultSet();
            echo "Columns in '$table':\n";
            foreach ($cols as $c) {
                echo "  - {$c->Field} ({$c->Type})\n";
            }
        } else {
            echo "Table '$table': MISSING\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}
