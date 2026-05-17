<?php
class RepCatalog {
    private $db;

    public function __construct() {
        $this->db = new Database(); 
    }

    public function getCategories() {
        $this->db->query("SELECT * FROM item_categories ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function getVisualCatalog() {
        // Fetch base items and their primary display image
        $this->db->query("
            SELECT i.*, c.name as category_name,
                   (SELECT image_path FROM item_images WHERE item_id = i.id AND variation_value_id IS NULL ORDER BY is_primary DESC, id ASC LIMIT 1) as image_path
            FROM items i
            LEFT JOIN item_categories c ON i.category_id = c.id
            ORDER BY c.name ASC, i.name ASC
        ");
        $items = $this->db->resultSet();
        
        // Embed variations inside each item for the modal selection screen
        foreach($items as $item) {
            $this->db->query("
                SELECT ivo.*, v.name as variation_name, vv.value_name,
                       (SELECT image_path FROM item_images WHERE item_id = :id AND variation_value_id = vv.id ORDER BY is_primary DESC, id ASC LIMIT 1) as var_image
                FROM item_variation_options ivo
                JOIN variations v ON ivo.variation_id = v.id
                JOIN variation_values vv ON ivo.variation_value_id = vv.id
                WHERE ivo.item_id = :id
            ");
            $this->db->bind(':id', $item->id);
            $item->variations = $this->db->resultSet();
        }
        return $items;
    }
}