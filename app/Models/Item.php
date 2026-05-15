<?php
class Item {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllItems() {
        $this->db->query("SELECT i.*, cat.name as category_name, v.name as vendor_name, w.name as warehouse_name 
                          FROM items i 
                          LEFT JOIN item_categories cat ON i.category_id = cat.id
                          LEFT JOIN vendors v ON i.vendor_id = v.id
                          LEFT JOIN warehouses w ON i.warehouse_id = w.id
                          ORDER BY i.name ASC");
        return $this->db->resultSet();
    }

    public function getItemById($id) {
        $this->db->query("SELECT * FROM items WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getItemsPaginated($search = '', $limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT i.*, cat.name as category_name, v.name as vendor_name, w.name as warehouse_name,
                       (SELECT image_path FROM item_images WHERE item_id = i.id AND variation_value_id IS NULL ORDER BY id ASC LIMIT 1) as primary_image
                FROM items i 
                LEFT JOIN item_categories cat ON i.category_id = cat.id
                LEFT JOIN vendors v ON i.vendor_id = v.id
                LEFT JOIN warehouses w ON i.warehouse_id = w.id
                WHERE (i.name LIKE :search OR i.item_code LIKE :search OR cat.name LIKE :search OR v.name LIKE :search)";
        
        if (!empty($filters['category_id'])) { $sql .= " AND i.category_id = :cat_id"; }
        if (!empty($filters['vendor_id'])) { $sql .= " AND i.vendor_id = :ven_id"; }
        if (!empty($filters['warehouse_id'])) { $sql .= " AND i.warehouse_id = :wh_id"; }
        if ($filters['min_price'] !== '') { $sql .= " AND i.price >= :min_price"; }
        if ($filters['max_price'] !== '') { $sql .= " AND i.price <= :max_price"; }
        
        $sql .= " ORDER BY i.name ASC LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);
        $this->db->bind(':search', "%$search%");
        if (!empty($filters['category_id'])) { $this->db->bind(':cat_id', $filters['category_id']); }
        if (!empty($filters['vendor_id'])) { $this->db->bind(':ven_id', $filters['vendor_id']); }
        if (!empty($filters['warehouse_id'])) { $this->db->bind(':wh_id', $filters['warehouse_id']); }
        if ($filters['min_price'] !== '') { $this->db->bind(':min_price', $filters['min_price']); }
        if ($filters['max_price'] !== '') { $this->db->bind(':max_price', $filters['max_price']); }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalItems($search = '', $filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM items i 
                LEFT JOIN item_categories cat ON i.category_id = cat.id
                LEFT JOIN vendors v ON i.vendor_id = v.id
                LEFT JOIN warehouses w ON i.warehouse_id = w.id
                WHERE (i.name LIKE :search OR i.item_code LIKE :search OR cat.name LIKE :search OR v.name LIKE :search)";
        
        if (!empty($filters['category_id'])) { $sql .= " AND i.category_id = :cat_id"; }
        if (!empty($filters['vendor_id'])) { $sql .= " AND i.vendor_id = :ven_id"; }
        if (!empty($filters['warehouse_id'])) { $sql .= " AND i.warehouse_id = :wh_id"; }
        if ($filters['min_price'] !== '') { $sql .= " AND i.price >= :min_price"; }
        if ($filters['max_price'] !== '') { $sql .= " AND i.price <= :max_price"; }

        $this->db->query($sql);
        $this->db->bind(':search', "%$search%");
        if (!empty($filters['category_id'])) { $this->db->bind(':cat_id', $filters['category_id']); }
        if (!empty($filters['vendor_id'])) { $this->db->bind(':ven_id', $filters['vendor_id']); }
        if (!empty($filters['warehouse_id'])) { $this->db->bind(':wh_id', $filters['warehouse_id']); }
        if ($filters['min_price'] !== '') { $this->db->bind(':min_price', $filters['min_price']); }
        if ($filters['max_price'] !== '') { $this->db->bind(':max_price', $filters['max_price']); }
        
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getInventoryKPIs() {
        $this->db->query("SELECT 
                            COUNT(id) as total_products,
                            SUM(CASE WHEN type = 'Inventory' THEN quantity_on_hand * cost ELSE 0 END) as total_value,
                            SUM(CASE WHEN type = 'Inventory' AND quantity_on_hand <= minimum_stock_level THEN 1 ELSE 0 END) as low_stock_count
                          FROM items");
        return $this->db->single();
    }

    // --- NEW: Image Management Engine ---
    
    public function getItemImages($itemId) {
        $this->db->query("SELECT * FROM item_images WHERE item_id = :id ORDER BY id ASC");
        $this->db->bind(':id', $itemId);
        return $this->db->resultSet();
    }

    public function saveImage($itemId, $variationValueId, $imagePath) {
        $this->db->query("INSERT INTO item_images (item_id, variation_value_id, image_path) VALUES (:iid, :vid, :path)");
        $this->db->bind(':iid', $itemId);
        $this->db->bind(':vid', $variationValueId);
        $this->db->bind(':path', $imagePath);
        return $this->db->execute();
    }

    public function deleteImage($imageId) {
        // Fetch to unlink physical file
        $this->db->query("SELECT image_path FROM item_images WHERE id = :id");
        $this->db->bind(':id', $imageId);
        $img = $this->db->single();
        if ($img && file_exists('../public/uploads/products/' . $img->image_path)) {
            unlink('../public/uploads/products/' . $img->image_path);
        }

        $this->db->query("DELETE FROM item_images WHERE id = :id");
        $this->db->bind(':id', $imageId);
        return $this->db->execute();
    }

    // ------------------------------------

    public function addItem($data) {
        $this->db->query("INSERT INTO items (item_code, name, category_id, vendor_id, warehouse_id, type, is_variable_pricing, price, cost, quantity_on_hand, minimum_stock_level) 
                          VALUES (:code, :name, :cat_id, :vid, :wh_id, :type, :is_var, :price, :cost, 0, :min_stock)");
        
        $this->db->bind(':code', $data['item_code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':cat_id', $data['category_id']);
        $this->db->bind(':vid', $data['vendor_id']);
        $this->db->bind(':wh_id', $data['warehouse_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':is_var', $data['is_variable_pricing']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':cost', $data['cost']);
        $this->db->bind(':min_stock', $data['min_stock']);
        
        try { 
            $this->db->execute(); 
            $itemId = $this->db->lastInsertId();
            
            if (!empty($data['variations'])) {
                foreach ($data['variations'] as $var) {
                    $this->db->query("INSERT INTO item_variation_options (item_id, variation_id, variation_value_id, sku, price, cost) 
                                      VALUES (:iid, :vid, :vvid, :sku, :price, :cost)");
                    $this->db->bind(':iid', $itemId);
                    $this->db->bind(':vid', $var['variation_id']);
                    $this->db->bind(':vvid', $var['variation_value_id']);
                    $this->db->bind(':sku', $var['sku']);
                    $this->db->bind(':price', $var['price']);
                    $this->db->bind(':cost', $var['cost']);
                    $this->db->execute();
                }
            }
            return $itemId; // Now returns ID for image processing
        } catch (PDOException $e) { return false; }
    }

    public function updateItem($data) {
        $this->db->query("UPDATE items SET 
                            item_code = :code, name = :name, category_id = :cat_id, vendor_id = :vid, warehouse_id = :wh_id, type = :type, 
                            is_variable_pricing = :is_var, price = :price, cost = :cost, 
                            minimum_stock_level = :min_stock
                          WHERE id = :id");
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':code', $data['item_code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':cat_id', $data['category_id']);
        $this->db->bind(':vid', $data['vendor_id']);
        $this->db->bind(':wh_id', $data['warehouse_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':is_var', $data['is_variable_pricing']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':cost', $data['cost']);
        $this->db->bind(':min_stock', $data['min_stock']);
        
        try { 
            $this->db->execute(); 
            
            $this->db->query("DELETE FROM item_variation_options WHERE item_id = :id");
            $this->db->bind(':id', $data['id']);
            $this->db->execute();

            if (!empty($data['variations'])) {
                foreach ($data['variations'] as $var) {
                    $this->db->query("INSERT INTO item_variation_options (item_id, variation_id, variation_value_id, sku, price, cost) 
                                      VALUES (:iid, :vid, :vvid, :sku, :price, :cost)");
                    $this->db->bind(':iid', $data['id']);
                    $this->db->bind(':vid', $var['variation_id']);
                    $this->db->bind(':vvid', $var['variation_value_id']);
                    $this->db->bind(':sku', $var['sku']);
                    $this->db->bind(':price', $var['price']);
                    $this->db->bind(':cost', $var['cost']);
                    $this->db->execute();
                }
            }
            return $data['id']; 
        } catch (PDOException $e) { return false; }
    }

    public function getItemVariations($itemId) {
        $this->db->query("SELECT ivo.*, v.name as variation_name, vv.value_name 
                          FROM item_variation_options ivo
                          JOIN variations v ON ivo.variation_id = v.id
                          JOIN variation_values vv ON ivo.variation_value_id = vv.id
                          WHERE ivo.item_id = :id");
        $this->db->bind(':id', $itemId);
        return $this->db->resultSet();
    }

    public function deleteItem($id) {
        // Find and delete images first
        $images = $this->getItemImages($id);
        foreach($images as $img) {
            $this->deleteImage($img->id);
        }

        $this->db->query("DELETE FROM items WHERE id = :id");
        $this->db->bind(':id', $id);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }
}