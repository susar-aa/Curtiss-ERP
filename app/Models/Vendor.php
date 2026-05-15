<?php
class Vendor {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllVendors() {
        $this->db->query("SELECT * FROM vendors ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function getVendorById($id) {
        $this->db->query("SELECT * FROM vendors WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addVendor($data) {
        $this->db->query("INSERT INTO vendors (name, email, phone, address) VALUES (:name, :email, :phone, :address)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }

    public function updateVendor($data) {
        $this->db->query("UPDATE vendors SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }

    // History Fetchers for the Vendor Center Dashboard
    public function getVendorExpenses($vendorId) {
        $this->db->query("SELECT * FROM expenses WHERE vendor_id = :vid ORDER BY expense_date DESC");
        $this->db->bind(':vid', $vendorId);
        return $this->db->resultSet();
    }

    public function getVendorPOs($vendorId) {
        $this->db->query("SELECT * FROM purchase_orders WHERE vendor_id = :vid ORDER BY po_date DESC");
        $this->db->bind(':vid', $vendorId);
        return $this->db->resultSet();
    }
}