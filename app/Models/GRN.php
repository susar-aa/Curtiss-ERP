<?php
class GRN {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getGRNsPaginated($search = '', $limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT g.*, v.name as vendor_name, u.username as creator_name, p.po_number 
                FROM goods_receipt_notes g 
                JOIN vendors v ON g.vendor_id = v.id 
                LEFT JOIN users u ON g.created_by = u.id
                LEFT JOIN purchase_orders p ON g.po_id = p.id
                WHERE (g.grn_number LIKE :search OR v.name LIKE :search OR p.po_number LIKE :search)";
        
        if (!empty($filters['vendor_id'])) { $sql .= " AND g.vendor_id = :vid"; }
        
        $sql .= " ORDER BY g.created_at DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);
        $this->db->bind(':search', "%$search%");
        if (!empty($filters['vendor_id'])) { $this->db->bind(':vid', $filters['vendor_id']); }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalGRNs($search = '', $filters = []) {
        $sql = "SELECT COUNT(*) as total FROM goods_receipt_notes g JOIN vendors v ON g.vendor_id = v.id LEFT JOIN purchase_orders p ON g.po_id = p.id WHERE (g.grn_number LIKE :search OR v.name LIKE :search OR p.po_number LIKE :search)";
        if (!empty($filters['vendor_id'])) { $sql .= " AND g.vendor_id = :vid"; }
        
        $this->db->query($sql);
        $this->db->bind(':search', "%$search%");
        if (!empty($filters['vendor_id'])) { $this->db->bind(':vid', $filters['vendor_id']); }
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getGRNById($id) {
        $this->db->query("SELECT g.*, v.name as vendor_name, v.email, v.phone, v.address, 
                                 u.username as creator_name, u.signature_path as creator_signature,
                                 p.po_number
                          FROM goods_receipt_notes g 
                          JOIN vendors v ON g.vendor_id = v.id 
                          LEFT JOIN users u ON g.created_by = u.id
                          LEFT JOIN purchase_orders p ON g.po_id = p.id
                          WHERE g.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getGRNItems($id) {
        $this->db->query("SELECT * FROM grn_items WHERE grn_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    public function createGRN($grnData, $items, $userId) {
        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO goods_receipt_notes (grn_number, po_id, vendor_id, grn_date, notes, created_by) 
                              VALUES (:num, :pid, :vid, :gdate, :notes, :uid)");
            $this->db->bind(':num', $grnData['grn_number']);
            $this->db->bind(':pid', $grnData['po_id']);
            $this->db->bind(':vid', $grnData['vendor_id']);
            $this->db->bind(':gdate', $grnData['grn_date']);
            $this->db->bind(':notes', $grnData['notes']);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            $grnId = $this->db->lastInsertId();

            foreach ($items as $item) {
                // 1. Insert Line Item
                $this->db->query("INSERT INTO grn_items (grn_id, item_id, item_variation_option_id, description, quantity, unit_cost, total) 
                                  VALUES (:gid, :iid, :vid, :desc, :qty, :cost, :total)");
                $this->db->bind(':gid', $grnId);
                $this->db->bind(':iid', $item['item_id']);
                $this->db->bind(':vid', $item['var_opt_id']);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':cost', $item['price']);
                $this->db->bind(':total', ($item['qty'] * $item['price']));
                $this->db->execute();

                // 2. Update Master Item Stock & Selling Price
                $this->db->query("UPDATE items SET quantity_on_hand = quantity_on_hand + :qty, price = :sprice WHERE id = :iid");
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':sprice', $item['selling_price']);
                $this->db->bind(':iid', $item['item_id']);
                $this->db->execute();

                // 3. Update Specific Variation Stock & Selling Price (If applicable)
                if ($item['var_opt_id']) {
                    $this->db->query("UPDATE item_variation_options SET quantity_on_hand = quantity_on_hand + :qty, price = :sprice WHERE id = :vid");
                    $this->db->bind(':qty', $item['qty']);
                    $this->db->bind(':sprice', $item['selling_price']);
                    $this->db->bind(':vid', $item['var_opt_id']);
                    $this->db->execute();
                }
            }

            // 4. Update PO Status if linked
            if (!empty($grnData['po_id'])) {
                $this->db->query("UPDATE purchase_orders SET status = 'Received' WHERE id = :id");
                $this->db->bind(':id', $grnData['po_id']);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) { $this->db->rollBack(); return false; }
    }

    public function deleteGRN($id) {
        try {
            $this->db->beginTransaction();
            $grn = $this->getGRNById($id);
            $items = $this->getGRNItems($id);

            // Revert Stock
            foreach($items as $item) {
                $this->db->query("UPDATE items SET quantity_on_hand = quantity_on_hand - :qty WHERE id = :iid");
                $this->db->bind(':qty', $item->quantity);
                $this->db->bind(':iid', $item->item_id);
                $this->db->execute();

                if ($item->item_variation_option_id) {
                    $this->db->query("UPDATE item_variation_options SET quantity_on_hand = quantity_on_hand - :qty WHERE id = :vid");
                    $this->db->bind(':qty', $item->quantity);
                    $this->db->bind(':vid', $item->item_variation_option_id);
                    $this->db->execute();
                }
            }

            // Un-link PO
            if ($grn->po_id) {
                $this->db->query("UPDATE purchase_orders SET status = 'Sent' WHERE id = :id");
                $this->db->bind(':id', $grn->po_id);
                $this->db->execute();
            }

            $this->db->query("DELETE FROM goods_receipt_notes WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) { $this->db->rollBack(); return false; }
    }
}