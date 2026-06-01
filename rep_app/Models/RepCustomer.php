<?php
class RepCustomer {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function getAllCustomers() {
        $this->db->query("
            SELECT c.*,
                COALESCE(
                    (SELECT SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) FROM invoices i WHERE i.customer_id = c.id AND i.status != 'Voided'), 0
                ) - COALESCE(
                    (SELECT SUM(p.amount) FROM customer_payments p WHERE p.customer_id = c.id), 0
                ) - COALESCE(
                    (SELECT SUM(cn.total_amount) FROM credit_notes cn WHERE cn.customer_id = c.id), 0
                ) AS outstanding
            FROM customers c
            ORDER BY c.name ASC
        ");
        return $this->db->resultSet();
    }

    // NEW: Fetch only customers in the currently active territory
    public function getCustomersByTerritory($territoryName) {
        $this->db->query("SELECT * FROM customers WHERE territory = :terr ORDER BY name ASC");
        $this->db->bind(':terr', $territoryName);
        return $this->db->resultSet();
    }

    public function addCustomer($data) {
        $this->db->query("INSERT INTO customers (name, phone, whatsapp, address, territory, latitude, longitude) 
                          VALUES (:name, :phone, :whatsapp, :address, :territory, :lat, :lng)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':whatsapp', $data['whatsapp']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':territory', $data['territory']);
        $this->db->bind(':lat', $data['lat']);
        $this->db->bind(':lng', $data['lng']);
        return $this->db->execute();
    }

    public function updateCustomer($data) {
        if ($data['update_location']) {
            $this->db->query("UPDATE customers SET name = :name, phone = :phone, whatsapp = :whatsapp, address = :address, latitude = :lat, longitude = :lng WHERE id = :id");
            $this->db->bind(':lat', $data['lat']);
            $this->db->bind(':lng', $data['lng']);
        } else {
            $this->db->query("UPDATE customers SET name = :name, phone = :phone, whatsapp = :whatsapp, address = :address WHERE id = :id");
        }
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':whatsapp', $data['whatsapp']);
        $this->db->bind(':address', $data['address']);
        
        return $this->db->execute();
    }

    public function deleteCustomer($id) {
        $this->db->query("DELETE FROM customers WHERE id = :id");
        $this->db->bind(':id', $id);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }
}