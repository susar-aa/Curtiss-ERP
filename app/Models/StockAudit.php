<?php

class StockAudit {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all stock audits, optionally filtered
     */
    public function getAllAudits($filters = []) {
        $sql = "SELECT sa.*, w.name as warehouse_name, 
                       u_created.username as creator_name,
                       u_counted.username as counter_name,
                       u_approved.username as approver_name
                FROM stock_audits sa
                JOIN warehouses w ON sa.warehouse_id = w.id
                LEFT JOIN users u_created ON sa.created_by = u_created.id
                LEFT JOIN users u_counted ON sa.counted_by = u_counted.id
                LEFT JOIN users u_approved ON sa.approved_by = u_approved.id";

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
        if (!empty($filters['start_date'])) {
            $conditions[] = "sa.created_at >= :start_date";
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = "sa.created_at <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
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
     * Get details of a single audit
     */
    public function getAuditById($id) {
        $this->db->query("
            SELECT sa.*, w.name as warehouse_name,
                   u_created.username as creator_name,
                   u_counted.username as counter_name,
                   u_reviewed.username as reviewer_name,
                   u_approved.username as approver_name
            FROM stock_audits sa
            JOIN warehouses w ON sa.warehouse_id = w.id
            LEFT JOIN users u_created ON sa.created_by = u_created.id
            LEFT JOIN users u_counted ON sa.counted_by = u_counted.id
            LEFT JOIN users u_reviewed ON sa.reviewed_by = u_reviewed.id
            LEFT JOIN users u_approved ON sa.approved_by = u_approved.id
            WHERE sa.id = :id
        ");
        $this->db->bind(':id', intval($id));
        return $this->db->single();
    }

    /**
     * Get items under a specific stock audit
     */
    public function getAuditItems($auditId) {
        $this->db->query("
            SELECT sai.*, i.name as item_name, i.item_code, i.barcode, i.unit, c.name as category_name
            FROM stock_audit_items sai
            JOIN items i ON sai.item_id = i.id
            LEFT JOIN item_categories c ON i.category_id = c.id
            WHERE sai.audit_id = :audit_id
            ORDER BY i.name ASC
        ");
        $this->db->bind(':audit_id', intval($auditId));
        return $this->db->resultSet() ?: [];
    }

    /**
     * Create a new stock audit from filters and populate audit items
     */
    public function createAudit($data) {
        try {
            $this->db->beginTransaction();

            // 1. Generate unique audit number
            $this->db->query("SELECT MAX(id) as max_id FROM stock_audits");
            $last = $this->db->single();
            $nextId = $last ? ($last->max_id + 1) : 1;
            $auditNumber = 'AUD-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 2. Insert audit header
            $this->db->query("
                INSERT INTO stock_audits (audit_number, warehouse_id, category_id, brand, supplier_id, status, created_by, remarks)
                VALUES (:audit_number, :warehouse_id, :category_id, :brand, :supplier_id, 'Draft', :created_by, :remarks)
            ");
            $this->db->bind(':audit_number', $auditNumber);
            $this->db->bind(':warehouse_id', intval($data['warehouse_id']));
            $this->db->bind(':category_id', !empty($data['category_id']) ? intval($data['category_id']) : null);
            $this->db->bind(':brand', !empty($data['brand']) ? $data['brand'] : null);
            $this->db->bind(':supplier_id', !empty($data['supplier_id']) ? intval($data['supplier_id']) : null);
            $this->db->bind(':created_by', intval($data['created_by']));
            $this->db->bind(':remarks', $data['remarks'] ?? '');
            $this->db->execute();

            $auditId = $this->db->lastInsertId();

            // 3. Find items matching filters in this warehouse
            $sql = "SELECT id, quantity_on_hand, cost_price FROM items WHERE warehouse_id = :warehouse_id";
            $params = [':warehouse_id' => intval($data['warehouse_id'])];

            if (!empty($data['category_id'])) {
                $sql .= " AND category_id = :category_id";
                $params[':category_id'] = intval($data['category_id']);
            }
            if (!empty($data['brand'])) {
                $sql .= " AND brand = :brand";
                $params[':brand'] = $data['brand'];
            }
            if (!empty($data['supplier_id'])) {
                $sql .= " AND vendor_id = :supplier_id"; // vendor_id holds the supplier in items table
                $params[':supplier_id'] = intval($data['supplier_id']);
            }

            $this->db->query($sql);
            foreach ($params as $k => $v) {
                $this->db->bind($k, $v);
            }
            $items = $this->db->resultSet() ?: [];

            // 4. Insert matched items into stock_audit_items
            foreach ($items as $item) {
                $systemQty = floatval($item->quantity_on_hand);
                $unitCost = floatval($item->cost_price);
                // Difference is initially system_qty difference, so physical_qty = 0 initially
                $diff = 0 - $systemQty; 
                $varianceVal = $diff * $unitCost;

                $this->db->query("
                    INSERT INTO stock_audit_items (audit_id, item_id, system_qty, physical_qty, difference, unit_cost, variance_value)
                    VALUES (:audit_id, :item_id, :system_qty, 0, :difference, :unit_cost, :variance_value)
                ");
                $this->db->bind(':audit_id', $auditId);
                $this->db->bind(':item_id', $item->id);
                $this->db->bind(':system_qty', $systemQty);
                $this->db->bind(':difference', $diff);
                $this->db->bind(':unit_cost', $unitCost);
                $this->db->bind(':variance_value', $varianceVal);
                $this->db->execute();
            }

            $this->db->commit();
            return $auditId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating stock audit: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save count as draft
     */
    public function saveDraftCount($auditId, $counts, $remarks, $overallRemarks) {
        try {
            $this->db->beginTransaction();

            // Update items
            foreach ($counts as $itemId => $physicalQty) {
                $physicalQty = floatval($physicalQty);
                $itemRemark = $remarks[$itemId] ?? '';

                // Get system qty & unit cost
                $this->db->query("SELECT system_qty, unit_cost FROM stock_audit_items WHERE audit_id = :audit_id AND item_id = :item_id");
                $this->db->bind(':audit_id', intval($auditId));
                $this->db->bind(':item_id', intval($itemId));
                $row = $this->db->single();

                if ($row) {
                    $systemQty = floatval($row->system_qty);
                    $unitCost = floatval($row->unit_cost);
                    $diff = $physicalQty - $systemQty;
                    $varianceVal = $diff * $unitCost;

                    $this->db->query("
                        UPDATE stock_audit_items 
                        SET physical_qty = :physical_qty, difference = :difference, variance_value = :variance_value, remarks = :remarks
                        WHERE audit_id = :audit_id AND item_id = :item_id
                    ");
                    $this->db->bind(':physical_qty', $physicalQty);
                    $this->db->bind(':difference', $diff);
                    $this->db->bind(':variance_value', $varianceVal);
                    $this->db->bind(':remarks', $itemRemark);
                    $this->db->bind(':audit_id', intval($auditId));
                    $this->db->bind(':item_id', intval($itemId));
                    $this->db->execute();
                }
            }

            // Update status to In Progress
            $this->db->query("
                UPDATE stock_audits 
                SET status = 'In Progress', overall_remarks = :overall_remarks
                WHERE id = :id AND status = 'Draft'
            ");
            $this->db->bind(':overall_remarks', $overallRemarks);
            $this->db->bind(':id', intval($auditId));
            $this->db->execute();

            // Always update overall remarks even if status was already In Progress
            $this->db->query("
                UPDATE stock_audits 
                SET overall_remarks = :overall_remarks
                WHERE id = :id
            ");
            $this->db->bind(':overall_remarks', $overallRemarks);
            $this->db->bind(':id', intval($auditId));
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error saving draft count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finalize and complete counting
     */
    public function completeCount($auditId, $counts, $remarks, $overallRemarks, $userId) {
        try {
            $this->db->beginTransaction();

            // Update items
            foreach ($counts as $itemId => $physicalQty) {
                $physicalQty = floatval($physicalQty);
                $itemRemark = $remarks[$itemId] ?? '';

                $this->db->query("SELECT system_qty, unit_cost FROM stock_audit_items WHERE audit_id = :audit_id AND item_id = :item_id");
                $this->db->bind(':audit_id', intval($auditId));
                $this->db->bind(':item_id', intval($itemId));
                $row = $this->db->single();

                if ($row) {
                    $systemQty = floatval($row->system_qty);
                    $unitCost = floatval($row->unit_cost);
                    $diff = $physicalQty - $systemQty;
                    $varianceVal = $diff * $unitCost;

                    $this->db->query("
                        UPDATE stock_audit_items 
                        SET physical_qty = :physical_qty, difference = :difference, variance_value = :variance_value, remarks = :remarks
                        WHERE audit_id = :audit_id AND item_id = :item_id
                    ");
                    $this->db->bind(':physical_qty', $physicalQty);
                    $this->db->bind(':difference', $diff);
                    $this->db->bind(':variance_value', $varianceVal);
                    $this->db->bind(':remarks', $itemRemark);
                    $this->db->bind(':audit_id', intval($auditId));
                    $this->db->bind(':item_id', intval($itemId));
                    $this->db->execute();
                }
            }

            // Update status to Completed
            $this->db->query("
                UPDATE stock_audits 
                SET status = 'Completed', overall_remarks = :overall_remarks, counted_by = :counted_by, completed_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $this->db->bind(':overall_remarks', $overallRemarks);
            $this->db->bind(':counted_by', intval($userId));
            $this->db->bind(':id', intval($auditId));
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error completing count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel an audit
     */
    public function cancelAudit($auditId) {
        $this->db->query("UPDATE stock_audits SET status = 'Cancelled' WHERE id = :id AND status IN ('Draft', 'In Progress', 'Completed')");
        $this->db->bind(':id', intval($auditId));
        return $this->db->execute();
    }
}
