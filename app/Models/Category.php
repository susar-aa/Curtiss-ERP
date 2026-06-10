<?php

class Category {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureWooCategoryIdColumn();
    }

    /**
     * Self-healing migration checking for and appending woo_category_id column dynamically
     */
    private function ensureWooCategoryIdColumn() {
        try {
            $this->db->query("DESCRIBE item_categories");
            $columns = $this->db->resultSet();
            if ($columns) {
                $fields = array_map(function($col) {
                    return strtolower($col->Field ?? $col->field ?? '');
                }, $columns);

                if (!in_array('woo_category_id', $fields)) {
                    $this->db->query("ALTER TABLE item_categories ADD woo_category_id INT NULL DEFAULT NULL");
                    $this->db->execute();
                }
                
                if (!in_array('description', $fields)) {
                    $this->db->query("ALTER TABLE item_categories ADD description TEXT NULL");
                    $this->db->execute();
                }
            }
        } catch (Exception $e) {
            // Safe fallback
        }
    }

    public function getCategories() {
        $this->db->query("SELECT cat.*, COUNT(i.id) AS product_count 
                          FROM item_categories cat 
                          LEFT JOIN items i ON cat.id = i.category_id 
                          GROUP BY cat.id 
                          ORDER BY cat.name ASC");
        return $this->db->resultSet();
    }

    public function getCategoryById($id) {
        $this->db->query("SELECT * FROM item_categories WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addCategory($name, $description, $wooCategoryId = null) {
        $this->db->query("INSERT INTO item_categories (name, description, woo_category_id) VALUES (:name, :description, :woo_category_id)");
        $this->db->bind(':name', $name);
        $this->db->bind(':description', $description);
        $this->db->bind(':woo_category_id', $wooCategoryId);
        return $this->db->execute();
    }

    public function updateCategory($id, $name, $description, $wooCategoryId = null) {
        $this->db->query("UPDATE item_categories SET name = :name, description = :description, woo_category_id = :woo_category_id WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $name);
        $this->db->bind(':description', $description);
        $this->db->bind(':woo_category_id', $wooCategoryId);
        return $this->db->execute();
    }

    public function deleteCategory($id) {
        // Prevent deleting categories that have linked products
        $this->db->query("SELECT COUNT(*) as count FROM items WHERE category_id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        if ($row && intval($row->count) > 0) {
            return false;
        }

        $this->db->query("DELETE FROM item_categories WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}