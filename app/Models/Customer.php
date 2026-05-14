<?php
class Customer {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllCustomers() {
        $this->db->query("SELECT * FROM customers ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function addCustomer($data) {
        $this->db->query("INSERT INTO customers (name, email, phone, address) VALUES (:name, :email, :phone, :address)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }
}