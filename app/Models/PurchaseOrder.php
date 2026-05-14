<?php
class PurchaseOrder {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllPOs() {
        $this->db->query("SELECT p.*, v.name as vendor_name 
                          FROM purchase_orders p 
                          JOIN vendors v ON p.vendor_id = v.id 
                          ORDER BY p.created_at DESC");
        return $this->db->resultSet();
    }

    public function getPOById($id) {
        $this->db->query("SELECT p.*, v.name as vendor_name, v.email, v.phone, v.address 
                          FROM purchase_orders p 
                          JOIN vendors v ON p.vendor_id = v.id 
                          WHERE p.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getPOItems($id) {
        $this->db->query("SELECT * FROM purchase_order_items WHERE po_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    public function createPO($poData, $items, $userId) {
        try {
            $this->db->beginTransaction();

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += ($item['qty'] * $item['price']);
            }

            // 1. Create PO Header
            $this->db->query("INSERT INTO purchase_orders (po_number, vendor_id, po_date, expected_date, total_amount, created_by) 
                              VALUES (:po_num, :vid, :pdate, :edate, :total, :uid)");
            $this->db->bind(':po_num', $poData['po_number']);
            $this->db->bind(':vid', $poData['vendor_id']);
            $this->db->bind(':pdate', $poData['po_date']);
            $this->db->bind(':edate', $poData['expected_date']);
            $this->db->bind(':total', $totalAmount);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            
            $poId = $this->db->lastInsertId();

            // 2. Create PO Items
            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                $this->db->query("INSERT INTO purchase_order_items (po_id, description, quantity, unit_price, total) 
                                  VALUES (:pid, :desc, :qty, :price, :total)");
                $this->db->bind(':pid', $poId);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':price', $item['price']);
                $this->db->bind(':total', $itemTotal);
                $this->db->execute();
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}