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

    public function addVendor($data) {
        $this->db->query("INSERT INTO vendors (name, email, phone, address) VALUES (:name, :email, :phone, :address)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }
}