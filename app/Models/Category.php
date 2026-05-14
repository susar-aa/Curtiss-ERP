<?php
class Category {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllCategories() {
        $this->db->query("SELECT * FROM item_categories ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function addCategory($name, $description = '') {
        $this->db->query("INSERT INTO item_categories (name, description) VALUES (:name, :desc)");
        $this->db->bind(':name', $name);
        $this->db->bind(':desc', $description);
        
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false; // Fails if category name already exists
        }
    }
}