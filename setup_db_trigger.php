<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'core/Database.php';

$db = new Database();

// 1. Create the queue table
$sqlTable = "
CREATE TABLE IF NOT EXISTS firebase_stock_sync_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$db->query($sqlTable);
$db->execute();
echo "Table firebase_stock_sync_queue created or already exists.\n";

// 2. Drop trigger if exists
$db->query("DROP TRIGGER IF EXISTS trg_items_after_update");
$db->execute();

// 3. Create the trigger
$sqlTrigger = "
CREATE TRIGGER trg_items_after_update
AFTER UPDATE ON items
FOR EACH ROW
BEGIN
    IF (NEW.quantity_on_hand != OLD.quantity_on_hand) OR (NEW.quantity_reserved != OLD.quantity_reserved) THEN
        INSERT INTO firebase_stock_sync_queue (item_id) VALUES (NEW.id);
    END IF;
END;
";
$db->query($sqlTrigger);
$db->execute();
echo "Trigger trg_items_after_update created.\n";

?>
