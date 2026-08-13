<?php

namespace App\Services;

use Exception;

class StockMovementService {
    private $db;

    public function __construct() {
        $this->db = new \Database();
    }

    /**
     * Group demands, lock rows in deterministic order, and verify sufficient stock.
     * 
     * @param array $demands Array of ['item_id' => int, 'variation_option_id' => int|null, 'quantity' => float]
     * @throws Exception If stock is insufficient.
     */
    public function lockAndVerifyAvailability(array $demands) {
        if (empty($demands)) {
            return;
        }

        // 1. Aggregate demands to handle duplicate lines for the same item/variation
        $aggregatedDemands = [];
        foreach ($demands as $demand) {
            $itemId = intval($demand['item_id']);
            $varId = isset($demand['variation_option_id']) && $demand['variation_option_id'] ? intval($demand['variation_option_id']) : null;
            $qty = floatval($demand['quantity']);

            if ($itemId <= 0 || $qty <= 0) continue;

            $key = $itemId . '_' . ($varId ?: '0');
            if (!isset($aggregatedDemands[$key])) {
                $aggregatedDemands[$key] = [
                    'item_id' => $itemId,
                    'var_id' => $varId,
                    'total_qty' => 0
                ];
            }
            $aggregatedDemands[$key]['total_qty'] += $qty;
        }

        if (empty($aggregatedDemands)) {
            return;
        }

        // 2. Extract unique item IDs and variation IDs
        $itemIds = [];
        $varIds = [];
        foreach ($aggregatedDemands as $agg) {
            $itemIds[] = $agg['item_id'];
            if ($agg['var_id']) {
                $varIds[] = $agg['var_id'];
            }
        }
        
        $itemIds = array_unique($itemIds);
        $varIds = array_unique($varIds);

        // 3. Sort IDs numerically to guarantee deterministic lock order and prevent deadlocks
        sort($itemIds, SORT_NUMERIC);
        sort($varIds, SORT_NUMERIC);

        // 4. Lock parents (items) first
        if (!empty($itemIds)) {
            $itemPlaceholders = implode(',', array_fill(0, count($itemIds), '?'));
            $sql = "SELECT id, quantity_on_hand FROM items WHERE id IN ($itemPlaceholders) ORDER BY id ASC FOR UPDATE";
            $this->db->query($sql);
            foreach ($itemIds as $index => $id) {
                $this->db->bind($index + 1, $id);
            }
            $lockedItems = $this->db->resultSet();
            
            $itemStock = [];
            foreach ($lockedItems as $row) {
                $itemStock[$row->id] = floatval($row->quantity_on_hand);
            }

            // Verify parent stock (only for those without variations, as variations have their own stock)
            foreach ($aggregatedDemands as $agg) {
                if (!$agg['var_id']) {
                    $available = $itemStock[$agg['item_id']] ?? 0;
                    if ($available < $agg['total_qty']) {
                        throw new Exception("Insufficient stock for Product ID {$agg['item_id']}. Requested: {$agg['total_qty']}, Available: {$available}");
                    }
                }
            }
        }

        // 5. Lock variations
        if (!empty($varIds)) {
            $varPlaceholders = implode(',', array_fill(0, count($varIds), '?'));
            $sql = "SELECT id, item_id, quantity_on_hand FROM item_variation_options WHERE id IN ($varPlaceholders) ORDER BY id ASC FOR UPDATE";
            $this->db->query($sql);
            foreach ($varIds as $index => $id) {
                $this->db->bind($index + 1, $id);
            }
            $lockedVars = $this->db->resultSet();

            $varStock = [];
            foreach ($lockedVars as $row) {
                $varStock[$row->id] = floatval($row->quantity_on_hand);
            }

            // Verify variation stock
            foreach ($aggregatedDemands as $agg) {
                if ($agg['var_id']) {
                    $available = $varStock[$agg['var_id']] ?? 0;
                    if ($available < $agg['total_qty']) {
                        throw new Exception("Insufficient stock for Product Variation ID {$agg['var_id']}. Requested: {$agg['total_qty']}, Available: {$available}");
                    }
                }
            }
        }

        // If we reach here, locks are acquired in deterministic order, and stock is strictly verified as sufficient.
        return true;
    }
}
                $qtyUpdates[] = "quantity_on_hand = CAST(quantity_on_hand AS SIGNED) + :delta";
            }
            if (empty($qtyUpdates)) {
                $qtyUpdates[] = "quantity_on_hand = CAST(quantity_on_hand AS SIGNED) + :delta";
            }
            $qtyUpdates[] = "stock_version = stock_version + 1";
            $qtyUpdatesStr = implode(', ', $qtyUpdates);

            $sql = "UPDATE items SET {$qtyUpdatesStr} WHERE id = :id";
            if ($delta < 0) {
                $checkCol = $hasQuantityOnHandColumn ? 'quantity_on_hand' : ($hasQtyColumn ? 'qty' : 'quantity_on_hand');
                $sql .= " AND (CAST({$checkCol} AS SIGNED) + :delta_check) >= 0";
            }
            $this->db->query($sql);
            $this->db->bind(':id', $itemId);
            $this->db->bind(':delta', $delta);
            if ($delta < 0) {
                $this->db->bind(':delta_check', $delta);
            }
            $res = $this->db->execute();

            // Fetch the updated item and log to stock_events
            $this->db->query("SELECT quantity_on_hand, quantity_reserved, stock_version, warehouse_id FROM items WHERE id = :id");
            $this->db->bind(':id', $itemId);
            $parentItem = $this->db->single();

            if ($parentItem) {
                $onHand = floatval($parentItem->quantity_on_hand);
                $reserved = floatval($parentItem->quantity_reserved ?? 0);
                $available = $onHand - $reserved;

                $this->db->query("INSERT INTO stock_events (stock_version, warehouse_id, item_id, variation_id, on_hand, reserved, available, status) VALUES (:version, :wh, :item, NULL, :on_hand, :res, :avail, 'pending')");
                $this->db->bind(':version', $parentItem->stock_version);
                $this->db->bind(':wh', $parentItem->warehouse_id);
                $this->db->bind(':item', $itemId);
                $this->db->bind(':on_hand', $onHand);
                $this->db->bind(':res', $reserved);
                $this->db->bind(':avail', $available);
                $this->db->execute();
            }
            
            if ($delta < 0 && $this->db->rowCount() == 0) {
                throw new Exception("Insufficient stock for item ID {$itemId}");
            }
            return $res;
        }
    }
}
