<?php
// Since this runs from CLI, ensure we are in the root directory
$rootDir = dirname(dirname(__DIR__));
chdir($rootDir);

require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'core/Database.php';
require_once 'app/Services/FirebaseStockService.php';

$db = new Database();
$firebaseService = new FirebaseStockService();

echo "Starting Real-Time Stock Sync Daemon...\n";

while (true) {
    try {
        // Fetch pending queue rows
        $db->query("SELECT id, item_id FROM firebase_stock_sync_queue");
        $pendingRows = $db->resultSet();

        if (empty($pendingRows)) {
            // Sleep for 1 second if no updates
            sleep(1);
            continue;
        }

        // Group queue IDs by item_id
        $itemsToProcess = [];
        foreach ($pendingRows as $row) {
            $itemId = $row->item_id;
            if (!isset($itemsToProcess[$itemId])) {
                $itemsToProcess[$itemId] = [];
            }
            $itemsToProcess[$itemId][] = $row->id;
        }

        foreach ($itemsToProcess as $itemId => $queueIds) {
            // Get current stock
            $db->query("SELECT quantity_on_hand, quantity_reserved FROM items WHERE id = :id");
            $db->bind(':id', $itemId);
            $item = $db->single();

            if ($item) {
                // Broadcast to Firebase
                // Note: We don't have the user ID of who caused this, so it broadcasts to everyone
                $firebaseService->broadcast_stock_update($itemId, $item->quantity_on_hand, 0);
                echo "[" . date('Y-m-d H:i:s') . "] Synced Item $itemId -> Stock: " . $item->quantity_on_hand . ", Reserved: 0\n";
                @ob_flush(); flush();
            }

            // Remove specifically fetched IDs from queue to prevent race conditions
            $placeholders = [];
            foreach ($queueIds as $idx => $qId) {
                $placeholders[] = ':qid_' . $idx;
            }
            $placeholdersStr = implode(',', $placeholders);
            
            $db->query("DELETE FROM firebase_stock_sync_queue WHERE id IN ($placeholdersStr)");
            foreach ($queueIds as $idx => $qId) {
                $db->bind(':qid_' . $idx, $qId);
            }
            $db->execute();
        }

    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] Daemon Error: " . $e->getMessage() . "\n";
        sleep(5); // Sleep longer on error to prevent CPU thrashing
    }
}
