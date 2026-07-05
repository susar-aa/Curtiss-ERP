<?php
$_SESSION['user_id'] = 1; // mock session
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/core/Database.php';

$deliveryId = 48;
$returnStockData = [
    [
        'item_name' => 'Fabric Paint 6 Colors',
        'item_id' => 5495,
        'variation_option_id' => 0,
        'loaded_qty' => 5,
        'delivered_qty' => 4,
        'actual_returned_qty' => 1
    ],
    [
        'item_name' => 'Fabric Paint 12 Colors',
        'item_id' => 5496,
        'variation_option_id' => 0,
        'loaded_qty' => 12,
        'delivered_qty' => 12,
        'actual_returned_qty' => 0
    ],
    [
        'item_name' => 'Tane Tape 48mm*100mt',
        'item_id' => 5398,
        'variation_option_id' => 0,
        'loaded_qty' => 1,
        'delivered_qty' => 0,
        'actual_returned_qty' => 1
    ]
];

try {
    $db = new Database();
    
    // Check if return stock has already been saved
    $db->query("SELECT return_stock_json FROM deliveries WHERE id = :id LIMIT 1");
    $db->bind(':id', $deliveryId);
    $existing = $db->single();
    if ($existing && !empty($existing->return_stock_json)) {
        echo "Error: Return stock has already been verified and saved.\n";
        exit;
    }

    $db->beginTransaction();
    echo "Transaction started.\n";

    // 1. Update return stock JSON and verified fields
    $db->query("UPDATE deliveries SET 
                return_stock_json = :ret,
                return_stock_verified_by = :uid,
                return_stock_verified_at = NOW()
                WHERE id = :id");
    $db->bind(':ret', json_encode($returnStockData));
    $db->bind(':uid', 1);
    $db->bind(':id', $deliveryId);
    $db->execute();
    echo "Update deliveries table return_stock_json succeeded.\n";

    // 2. Fetch the delivery routes
    $db->query("SELECT rep_route_id, secondary_rep_route_id FROM deliveries WHERE id = :id LIMIT 1");
    $db->bind(':id', $deliveryId);
    $delivery = $db->single();

    if ($delivery) {
        $routeIds = [];
        if ($delivery->rep_route_id) { $routeIds[] = intval($delivery->rep_route_id); }
        if ($delivery->secondary_rep_route_id) { $routeIds[] = intval($delivery->secondary_rep_route_id); }

        if (!empty($routeIds)) {
            $routeIdsStr = implode(',', $routeIds);
            echo "Route IDs: $routeIdsStr\n";
            $db->query("SELECT id, stock_status FROM invoices WHERE rep_route_id IN ($routeIdsStr) AND status != 'Voided'");
            $invoices = $db->resultSet() ?: [];
            echo "Invoices Count: " . count($invoices) . "\n";

            require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/FIFO.php';
            $fifo = new FIFO();

            foreach ($invoices as $invoice) {
                echo "Invoice ID: {$invoice->id}, Stock Status: {$invoice->stock_status}\n";
                if ($invoice->stock_status === 'deducted' || $invoice->stock_status === 'returned') {
                    echo "Skipping invoice {$invoice->id} because stock_status is {$invoice->stock_status}\n";
                    continue;
                }

                $db->query("SELECT * FROM invoice_items WHERE invoice_id = :iid");
                $db->bind(':iid', $invoice->id);
                $items = $db->resultSet() ?: [];
                echo "  Items Count: " . count($items) . "\n";

                foreach ($items as $item) {
                    $deliveredQty = floatval($item->quantity);
                    $loadedQty = floatval($item->loaded_quantity);
                    $itemId = $item->item_id;
                    $varId = $item->variation_option_id;
                    
                    echo "    Item ID: {$itemId}, Var ID: {$varId}, Loaded: {$loadedQty}, Delivered: {$deliveredQty}\n";

                    if (!$itemId && !empty($item->description)) {
                        $db->query("SELECT id FROM items WHERE name = :name LIMIT 1");
                        $db->bind(':name', $item->description);
                        $rowItem = $db->single();
                        if ($rowItem) {
                            $itemId = $rowItem->id;
                            echo "      Resolved Item ID from description: {$itemId}\n";
                        }
                    }

                    if ($itemId) {
                        // Deduct delivered quantity from physical stock
                        if ($deliveredQty > 0) {
                            if ($varId) {
                                $db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                                $db->bind(':qty', $deliveredQty);
                                $db->bind(':id', $varId);
                                $db->execute();
                                echo "      Deducted item variation option ID {$varId} quantity_on_hand by {$deliveredQty}\n";
                            } else {
                                $db->query("UPDATE items SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                                $db->bind(':qty', $deliveredQty);
                                $db->bind(':id', $itemId);
                                $db->execute();
                                echo "      Deducted item ID {$itemId} quantity_on_hand by {$deliveredQty}\n";
                            }

                            // Deduct FIFO costing
                            try {
                                $fifo->depleteStock($itemId, $varId ?: null, $deliveredQty, $item->id, null);
                                echo "      FIFO depletion for item {$itemId} succeeded.\n";
                            } catch (Exception $e) {
                                echo "      FIFO depletion error for item {$itemId}: " . $e->getMessage() . "\n";
                            }
                        }

                        // Release/clear loaded quantity from reserved stock
                        if ($loadedQty > 0) {
                            if ($varId) {
                                $db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                                $db->bind(':qty', $loadedQty);
                                $db->bind(':id', $varId);
                                $db->execute();
                                echo "      Released item variation option ID {$varId} quantity_reserved by {$loadedQty}\n";
                            } else {
                                $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                                $db->bind(':qty', $loadedQty);
                                $db->bind(':id', $itemId);
                                $db->execute();
                                echo "      Released item ID {$itemId} quantity_reserved by {$loadedQty}\n";
                            }
                        }
                    }
                }

                $db->query("UPDATE invoices SET stock_status = 'deducted' WHERE id = :id");
                $db->bind(':id', $invoice->id);
                $db->execute();
                echo "  Invoice ID {$invoice->id} stock_status set to deducted.\n";
            }
        }
    }

    $db->rollBack(); // Rollback so we don't pollute data while testing!
    echo "Test complete. Transaction rolled back successfully.\n";

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
