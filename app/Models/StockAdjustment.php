<?php

class StockAdjustment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all stock adjustments, optionally filtered
     */
    public function getAllAdjustments($filters = []) {
        $sql = "SELECT sa.*, w.name as warehouse_name, 
                       u_created.username as creator_name,
                       u_approved.username as approver_name,
                       je.reference as journal_reference,
                       audit.audit_number
                FROM stock_adjustments sa
                JOIN warehouses w ON sa.warehouse_id = w.id
                LEFT JOIN users u_created ON sa.created_by = u_created.id
                LEFT JOIN users u_approved ON sa.approved_by = u_approved.id
                LEFT JOIN journal_entries je ON sa.journal_entry_id = je.id
                LEFT JOIN stock_audits audit ON sa.stock_audit_id = audit.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['warehouse_id'])) {
            $conditions[] = "sa.warehouse_id = :warehouse_id";
            $params[':warehouse_id'] = intval($filters['warehouse_id']);
        }
        if (!empty($filters['status'])) {
            $conditions[] = "sa.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['reason'])) {
            $conditions[] = "sa.reason = :reason";
            $params[':reason'] = $filters['reason'];
        }
        if (!empty($filters['start_date'])) {
            $conditions[] = "sa.adjustment_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = "sa.adjustment_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY sa.id DESC";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet() ?: [];
    }

    /**
     * Get details of a single adjustment
     */
    public function getAdjustmentById($id) {
        $this->db->query("
            SELECT sa.*, w.name as warehouse_name,
                   u_created.username as creator_name,
                   u_approved.username as approver_name,
                   je.reference as journal_reference,
                   audit.audit_number
            FROM stock_adjustments sa
            JOIN warehouses w ON sa.warehouse_id = w.id
            LEFT JOIN users u_created ON sa.created_by = u_created.id
            LEFT JOIN users u_approved ON sa.approved_by = u_approved.id
            LEFT JOIN journal_entries je ON sa.journal_entry_id = je.id
            LEFT JOIN stock_audits audit ON sa.stock_audit_id = audit.id
            WHERE sa.id = :id
        ");
        $this->db->bind(':id', intval($id));
        return $this->db->single();
    }

    /**
     * Get items under a specific stock adjustment
     */
    public function getAdjustmentItems($adjustmentId) {
        $this->db->query("
            SELECT sai.*, 
                   i.name as base_item_name, 
                   i.item_code as base_item_code, 
                   i.barcode as base_barcode,
                   i.unit, 
                   c.name as category_name,
                   ivo.sku as variation_sku,
                   vv.value_name as variation_value
            FROM stock_adjustment_items sai
            JOIN items i ON sai.item_id = i.id
            LEFT JOIN item_categories c ON i.category_id = c.id
            LEFT JOIN item_variation_options ivo ON sai.variation_option_id = ivo.id
            LEFT JOIN variation_values vv ON ivo.variation_value_id = vv.id
            WHERE sai.adjustment_id = :adjustment_id
            ORDER BY i.name ASC
        ");
        $this->db->bind(':adjustment_id', intval($adjustmentId));
        $items = $this->db->resultSet() ?: [];

        foreach ($items as $item) {
            if ($item->variation_option_id && $item->variation_value) {
                $item->item_name = $item->base_item_name . ' - ' . $item->variation_value;
                $item->item_code = $item->variation_sku ?: $item->base_item_code;
                $item->barcode = $item->variation_sku ?: $item->base_barcode;
            } else {
                $item->item_name = $item->base_item_name;
                $item->item_code = $item->base_item_code;
                $item->barcode = $item->base_barcode;
            }
        }
        return $items;
    }

    /**
     * Create a new manual or automatic stock adjustment
     */
    public function createAdjustment($data) {
        try {
            $this->db->beginTransaction();

            // 1. Generate unique adjustment number
            $this->db->query("SELECT MAX(id) as max_id FROM stock_adjustments");
            $last = $this->db->single();
            $nextId = $last ? ($last->max_id + 1) : 1;
            $adjNumber = 'ADJ-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 2. Insert header
            $this->db->query("
                INSERT INTO stock_adjustments (adjustment_number, warehouse_id, reason, adjustment_date, status, created_by, remarks, attachment_path, stock_audit_id)
                VALUES (:adjustment_number, :warehouse_id, :reason, :adjustment_date, 'Pending', :created_by, :remarks, :attachment_path, :stock_audit_id)
            ");
            $this->db->bind(':adjustment_number', $adjNumber);
            $this->db->bind(':warehouse_id', intval($data['warehouse_id']));
            $this->db->bind(':reason', $data['reason']);
            $this->db->bind(':adjustment_date', $data['adjustment_date'] ?: date('Y-m-d'));
            $this->db->bind(':created_by', intval($data['created_by']));
            $this->db->bind(':remarks', $data['remarks'] ?? '');
            $this->db->bind(':attachment_path', $data['attachment_path'] ?? null);
            $this->db->bind(':stock_audit_id', !empty($data['stock_audit_id']) ? intval($data['stock_audit_id']) : null);
            $this->db->execute();

            $adjustmentId = $this->db->lastInsertId();

            // 3. Insert items
            foreach ($data['items'] as $item) {
                $qty = floatval($item['quantity']);
                $unitCost = floatval($item['unit_cost']);
                $totalVal = abs($qty * $unitCost);

                $this->db->query("
                    INSERT INTO stock_adjustment_items (adjustment_id, item_id, variation_option_id, quantity, unit_cost, total_value, remarks)
                    VALUES (:adjustment_id, :item_id, :variation_option_id, :quantity, :unit_cost, :total_value, :remarks)
                ");
                $this->db->bind(':adjustment_id', $adjustmentId);
                $this->db->bind(':item_id', intval($item['item_id']));
                $this->db->bind(':variation_option_id', !empty($item['variation_option_id']) ? intval($item['variation_option_id']) : null);
                $this->db->bind(':quantity', $qty);
                $this->db->bind(':unit_cost', $unitCost);
                $this->db->bind(':total_value', $totalVal);
                $this->db->bind(':remarks', $item['remarks'] ?? '');
                $this->db->execute();
            }

            $this->db->commit();
            return $adjustmentId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating stock adjustment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve a pending adjustment, applying stock changes and ledger postings
     */
    public function approveAdjustment($id, $userId) {
        try {
            $this->db->beginTransaction();

            // Fetch adjustment header
            $adj = $this->getAdjustmentById($id);
            if (!$adj || $adj->status !== 'Pending') {
                throw new Exception("Adjustment not found or not in pending state.");
            }

            // Fetch items
            $items = $this->getAdjustmentItems($id);
            if (empty($items)) {
                throw new Exception("Cannot approve an adjustment with no items.");
            }

            // Instantiate dependent models
            require_once __DIR__ . '/Item.php';
            require_once __DIR__ . '/StockLedger.php';
            $itemModel = new Item();
            $ledgerModel = new StockLedger();

            foreach ($items as $item) {
                $qty = floatval($item->quantity);
                $unitCost = floatval($item->unit_cost);

                // 1. Update system stock quantity
                // Note: updateStockDelta is schema-aware and prevents negative values
                $itemModel->updateStockDelta($item->item_id, $qty, $item->variation_option_id);

                // 2. Log movement to trigger ledger & double entry
                $ledgerType = ($qty > 0) ? 'Stock Adjustment Increase' : 'Stock Adjustment Decrease';
                $qtyIn = ($qty > 0) ? $qty : 0.00;
                $qtyOut = ($qty < 0) ? abs($qty) : 0.00;

                // Log movement (this inserts into stock_ledger and automatically posts the journal entry)
                $ledgerModel->logMovement(
                    $item->item_id,
                    $item->variation_option_id, // variation_option_id
                    $qtyIn,
                    $qtyOut,
                    $ledgerType,
                    $adj->adjustment_number,
                    $adj->warehouse_id,
                    $userId,
                    "Stock Adjustment: " . $adj->reason . " - " . ($item->remarks ?: $adj->remarks),
                    $unitCost
                );
            }

            // 3. Retrieve the journal entry id created by StockLedger
            $this->db->query("SELECT journal_entry_id FROM stock_ledger WHERE reference_number = :ref LIMIT 1");
            $this->db->bind(':ref', $adj->adjustment_number);
            $ledgerRow = $this->db->single();
            $journalId = $ledgerRow ? $ledgerRow->journal_entry_id : null;

            // 4. Update adjustment header status
            $this->db->query("
                UPDATE stock_adjustments 
                SET status = 'Approved', approved_by = :approved_by, approved_at = CURRENT_TIMESTAMP, journal_entry_id = :journal_id
                WHERE id = :id
            ");
            $this->db->bind(':approved_by', intval($userId));
            $this->db->bind(':journal_id', $journalId);
            $this->db->bind(':id', intval($id));
            $this->db->execute();

            // 5. If this is linked to a stock audit, approve the audit too!
            if ($adj->stock_audit_id) {
                $this->db->query("
                    UPDATE stock_audits 
                    SET status = 'Approved', approved_by = :approved_by, approved_at = CURRENT_TIMESTAMP
                    WHERE id = :audit_id AND status = 'Completed'
                ");
                $this->db->bind(':approved_by', intval($userId));
                $this->db->bind(':audit_id', $adj->stock_audit_id);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error approving stock adjustment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject a pending adjustment
     */
    public function rejectAdjustment($id, $userId) {
        $this->db->query("
            UPDATE stock_adjustments 
            SET status = 'Rejected', approved_by = :approved_by, approved_at = CURRENT_TIMESTAMP
            WHERE id = :id AND status = 'Pending'
        ");
        $this->db->bind(':approved_by', intval($userId));
        $this->db->bind(':id', intval($id));
        return $this->db->execute();
    }

    /**
     * Delete/cancel stock adjustment and reverse any stock/journal changes if approved
     */
    public function deleteAdjustment($id, $userId) {
        try {
            $this->db->beginTransaction();

            $adj = $this->getAdjustmentById($id);
            if (!$adj) {
                throw new Exception("Adjustment not found.");
            }

            if ($adj->status === 'Pending' || $adj->status === 'Rejected') {
                // Delete items
                $this->db->query("DELETE FROM stock_adjustment_items WHERE adjustment_id = :id");
                $this->db->bind(':id', intval($id));
                $this->db->execute();

                // Delete header
                $this->db->query("DELETE FROM stock_adjustments WHERE id = :id");
                $this->db->bind(':id', intval($id));
                $this->db->execute();

                $this->db->commit();
                return true;
            }

            if ($adj->status === 'Approved') {
                // To undo:
                // 1. Fetch items
                $items = $this->getAdjustmentItems($id);
                if (empty($items)) {
                    throw new Exception("No items found for adjustment.");
                }

                require_once __DIR__ . '/Item.php';
                require_once __DIR__ . '/StockLedger.php';
                $itemModel = new Item();
                $ledgerModel = new StockLedger();

                foreach ($items as $item) {
                    $qty = floatval($item->quantity);
                    $unitCost = floatval($item->unit_cost);

                    // Revert the stock qty
                    $undoQty = -$qty;
                    $itemModel->updateStockDelta($item->item_id, $undoQty, $item->variation_option_id);

                    // Revert ledger & double entry
                    $ledgerType = ($undoQty > 0) ? 'Stock Adjustment Increase' : 'Stock Adjustment Decrease';
                    $qtyIn = ($undoQty > 0) ? $undoQty : 0.00;
                    $qtyOut = ($undoQty < 0) ? abs($undoQty) : 0.00;

                    $ledgerModel->logMovement(
                        $item->item_id,
                        $item->variation_option_id,
                        $qtyIn,
                        $qtyOut,
                        $ledgerType,
                        $adj->adjustment_number . '-REV',
                        $adj->warehouse_id,
                        $userId,
                        "Stock Adjustment Reversal: " . $adj->reason . " - " . ($item->remarks ?: $adj->remarks),
                        $unitCost
                    );
                }

                // Delete items
                $this->db->query("DELETE FROM stock_adjustment_items WHERE adjustment_id = :id");
                $this->db->bind(':id', intval($id));
                $this->db->execute();

                // Delete header
                $this->db->query("DELETE FROM stock_adjustments WHERE id = :id");
                $this->db->bind(':id', intval($id));
                $this->db->execute();

                $this->db->commit();
                return true;
            }

            throw new Exception("Unsupported status for deletion.");
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting stock adjustment: " . $e->getMessage());
            return false;
        }
    }
}
