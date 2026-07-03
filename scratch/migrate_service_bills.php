<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    
    // Check if the columns already exist
    $db->query("SHOW COLUMNS FROM goods_receipt_notes LIKE 'due_date'");
    $exists = $db->single();
    
    if (!$exists) {
        $db->query("ALTER TABLE goods_receipt_notes 
            ADD COLUMN due_date DATE NULL,
            ADD COLUMN service_period VARCHAR(100) NULL,
            ADD COLUMN amount DECIMAL(15,2) NULL,
            ADD COLUMN tax DECIMAL(15,2) NULL,
            ADD COLUMN total_amount DECIMAL(15,2) NULL,
            ADD COLUMN status ENUM('Unpaid', 'Partially Paid', 'Paid') DEFAULT 'Unpaid',
            ADD COLUMN attachment VARCHAR(255) NULL
        ");
        $db->execute();
        echo "Migration successful: columns added to goods_receipt_notes table.\n";
    } else {
        echo "Migration skipped: columns already exist in goods_receipt_notes.\n";
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
