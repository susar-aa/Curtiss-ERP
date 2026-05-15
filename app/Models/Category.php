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

    public function getCategoriesPaginated($search = '', $limit = 10, $offset = 0) {
        $this->db->query("SELECT c.*, (SELECT COUNT(*) FROM items WHERE category_id = c.id) as item_count 
                          FROM item_categories c 
                          WHERE c.name LIKE :search OR c.description LIKE :search 
                          ORDER BY c.name ASC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':search', "%$search%");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalCategories($search = '') {
        $this->db->query("SELECT COUNT(*) as total FROM item_categories WHERE name LIKE :search OR description LIKE :search");
        $this->db->bind(':search', "%$search%");
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function addCategory($name, $description = '') {
        $this->db->query("INSERT INTO item_categories (name, description) VALUES (:name, :desc)");
        $this->db->bind(':name', $name);
        $this->db->bind(':desc', $description);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    public function updateCategory($id, $name, $description = '') {
        $this->db->query("UPDATE item_categories SET name = :name, description = :desc WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $name);
        $this->db->bind(':desc', $description);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    public function deleteCategory($id) {
        $this->db->query("DELETE FROM item_categories WHERE id = :id");
        $this->db->bind(':id', $id);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }
}