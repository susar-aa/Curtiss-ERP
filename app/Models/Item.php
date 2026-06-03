<?php

class Item {
    private $db;
    private $priceColumn = 'price'; // Default fallback
    private $itemCodeColumn = 'item_code';
    private $nameColumn = 'name';
    private $qtyColumn = 'qty';
    private $descColumn = 'description';
    private $wholesalePriceColumn = 'wholesale_price'; // For WholesaleX B2B compatibility
    private $orderByColumn = 'id DESC';

    public function __construct() {
        $this->db = new Database();
        $this->detectColumns();
    }

    /**
     * Dynamically inspect the 'items' table schema to adapt to different column names.
     * Automatically runs ALTER TABLE migrations to append WholesaleX, Variations,
     * Warehouse Relations, and Supplier parameters so the database is self-healing.
     */
    private function detectColumns() {
        try {
            $this->db->query("DESCRIBE items");
            $columns = $this->db->resultSet();
            if ($columns) {
                $fields = array_map(function($col) {
                    return strtolower($col->Field ?? $col->field ?? '');
                }, $columns);

                $altered = false;

                // 1. Detect & Migrate Price
                $foundPrice = false;
                foreach ($fields as $f) {
                    if ($f === 'selling_price' || $f === 'price' || $f === 'unit_price' || $f === 'rate') {
                        $this->priceColumn = $f;
                        $foundPrice = true;
                        break;
                    }
                }
                if (!$foundPrice) {
                    $this->db->query("ALTER TABLE items ADD price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
                    $this->db->execute();
                    $this->priceColumn = 'price';
                    $fields[] = 'price';
                    $altered = true;
                }

                // 2. Detect & Migrate Wholesale Price (WholesaleX B2B)
                $foundWholesale = false;
                foreach ($fields as $f) {
                    if ($f === 'wholesale_price' || $f === 'b2b_price' || $f === 'wholesale' || $f === 'trade_price') {
                        $this->wholesalePriceColumn = $f;
                        $foundWholesale = true;
                        break;
                    }
                }
                if (!$foundWholesale) {
                    $this->db->query("ALTER TABLE items ADD wholesale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
                    $this->db->execute();
                    $this->wholesalePriceColumn = 'wholesale_price';
                    $fields[] = 'wholesale_price';
                    $altered = true;
                }

                // 3. Detect & Migrate Item Code / SKU
                $foundCode = false;
                foreach ($fields as $f) {
                    if ($f === 'item_code' || $f === 'sku' || $f === 'code' || $f === 'barcode') {
                        $this->itemCodeColumn = $f;
                        $foundCode = true;
                        break;
                    }
                }
                if (!$foundCode) {
                    $this->db->query("ALTER TABLE items ADD item_code VARCHAR(100) NULL");
                    $this->db->execute();
                    $this->itemCodeColumn = 'item_code';
                    $fields[] = 'item_code';
                    $altered = true;
                }

                // 4. Detect Name / Title
                if (!in_array('name', $fields) && !in_array('title', $fields)) {
                    $this->db->query("ALTER TABLE items ADD name VARCHAR(255) NOT NULL");
                    $this->db->execute();
                    $this->nameColumn = 'name';
                    $fields[] = 'name';
                    $altered = true;
                } else {
                    $this->nameColumn = in_array('name', $fields) ? 'name' : 'title';
                }

                // 5. Detect Qty / Stock
                $foundQty = false;
                foreach ($fields as $f) {
                    if ($f === 'qty' || $f === 'quantity' || $f === 'stock' || $f === 'stock_quantity' || $f === 'stock_qty') {
                        $this->qtyColumn = $f;
                        $foundQty = true;
                        break;
                    }
                }
                if (!$foundQty) {
                    $this->db->query("ALTER TABLE items ADD qty INT NOT NULL DEFAULT 0");
                    $this->db->execute();
                    $this->qtyColumn = 'qty';
                    $fields[] = 'qty';
                    $altered = true;
                }

                // 6. Detect Description
                if (!in_array('description', $fields) && !in_array('desc', $fields)) {
                    $this->db->query("ALTER TABLE items ADD description TEXT NULL");
                    $this->db->execute();
                    $this->descColumn = 'description';
                    $fields[] = 'description';
                    $altered = true;
                } else {
                    $this->descColumn = in_array('description', $fields) ? 'description' : 'desc';
                }

                // 7. Migrate relational columns to support complete, crash-free forms
                $migrations = [
                    'variations_json' => "ALTER TABLE items ADD variations_json TEXT NULL",
                    'image_path' => "ALTER TABLE items ADD image_path VARCHAR(255) NULL",
                    'barcode' => "ALTER TABLE items ADD barcode VARCHAR(100) NULL",
                    'category_id' => "ALTER TABLE items ADD category_id INT NULL",
                    'warehouse_id' => "ALTER TABLE items ADD warehouse_id INT NULL",
                    'vendor_id' => "ALTER TABLE items ADD vendor_id INT NULL",
                    'cost_price' => "ALTER TABLE items ADD cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00",
                    'brand' => "ALTER TABLE items ADD brand VARCHAR(100) NULL",
                    'warehouse' => "ALTER TABLE items ADD warehouse VARCHAR(100) NULL",
                    'alert_qty' => "ALTER TABLE items ADD alert_qty INT NOT NULL DEFAULT 5",
                    'unit' => "ALTER TABLE items ADD unit VARCHAR(20) NOT NULL DEFAULT 'pcs'",
                    'status' => "ALTER TABLE items ADD status VARCHAR(20) NOT NULL DEFAULT 'active'",
                    'weight' => "ALTER TABLE items ADD weight VARCHAR(50) NULL",
                    'sync_woo' => "ALTER TABLE items ADD sync_woo TINYINT NOT NULL DEFAULT 1",
                    'sample_code' => "ALTER TABLE items ADD sample_code VARCHAR(100) NULL"
                ];

                foreach ($migrations as $col => $sql) {
                    if (!in_array($col, $fields)) {
                        $this->db->query($sql);
                        $this->db->execute();
                        $fields[] = $col;
                        $altered = true;
                    }
                }

                if ($altered) {
                    $this->detectColumns();
                    return;
                }

                // Set order parameter
                if (in_array('created_at', $fields)) {
                    $this->orderByColumn = 'created_at DESC';
                } else {
                    $this->orderByColumn = 'id DESC';
                }
            }
        } catch (Exception $e) {
            // Fall back silently
        }
    }

    /**
     * Builds standard SQL where clauses dynamically to keep database queries highly performant.
     */
    private function buildFilterConditions($filters, &$params) {
        $whereClauses = [];

        if (!empty($filters['search'])) {
            $whereClauses[] = "({$this->itemCodeColumn} LIKE :search OR {$this->nameColumn} LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $whereClauses[] = "CAST({$this->priceColumn} AS DECIMAL(10,2)) >= :min_price";
            $params[':min_price'] = floatval($filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $whereClauses[] = "CAST({$this->priceColumn} AS DECIMAL(10,2)) <= :max_price";
            $params[':max_price'] = floatval($filters['max_price']);
        }

        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'instock') {
                $whereClauses[] = "CAST({$this->qtyColumn} AS SIGNED) > 5";
            } elseif ($filters['stock_status'] === 'lowstock') {
                $whereClauses[] = "CAST({$this->qtyColumn} AS SIGNED) > 0 AND CAST({$this->qtyColumn} AS SIGNED) <= 5";
            } elseif ($filters['stock_status'] === 'outstock') {
                $whereClauses[] = "(CAST({$this->qtyColumn} AS SIGNED) <= 0 OR {$this->qtyColumn} IS NULL)";
            }
        }

        return !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
    }

    /**
     * Fetch items in paginated batches at database level (resolves PHP out of memory error).
     */
    public function getItemsPaged($limit, $offset, $filters = []) {
        $params = [];
        $whereSql = $this->buildFilterConditions($filters, $params);

        $this->db->query("SELECT *, 
                          {$this->priceColumn} AS selling_price, 
                          {$this->wholesalePriceColumn} AS wholesale_price, 
                          {$this->itemCodeColumn} AS item_code, 
                          {$this->qtyColumn} AS qty, 
                          {$this->descColumn} AS description 
                          FROM items 
                          $whereSql 
                          ORDER BY {$this->orderByColumn} 
                          LIMIT :limit OFFSET :offset");

        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);

        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    /**
     * Return total quantity of items matching filters (essential for pagination calculations).
     */
    public function countItems($filters = []) {
        $params = [];
        $whereSql = $this->buildFilterConditions($filters, $params);

        $this->db->query("SELECT COUNT(*) AS total FROM items $whereSql");
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    /**
     * Calculate global item analytics dynamically at the database level.
     */
    public function getStockStats() {
        try {
            $this->db->query("SELECT 
                COUNT(*) AS total_items,
                SUM(CASE WHEN CAST({$this->qtyColumn} AS SIGNED) <= 0 OR {$this->qtyColumn} IS NULL THEN 1 ELSE 0 END) AS out_of_stock_count,
                SUM(CASE WHEN CAST({$this->qtyColumn} AS SIGNED) > 0 AND CAST({$this->qtyColumn} AS SIGNED) <= 5 THEN 1 ELSE 0 END) AS low_stock_count
                FROM items");
            
            $stats = $this->db->single();
            if ($stats) {
                return (object)[
                    'total_items' => (int)($stats->total_items ?? 0),
                    'low_stock_count' => (int)($stats->low_stock_count ?? 0),
                    'out_of_stock_count' => (int)($stats->out_of_stock_count ?? 0)
                ];
            }
        } catch (Exception $e) {
            // Safe fallbacks on failure
        }

        return (object)[
            'total_items' => 0,
            'low_stock_count' => 0,
            'out_of_stock_count' => 0
        ];
    }

    /**
     * Retrieve all items inside the local database
     */
    public function getItems() {
        $this->db->query("SELECT i.*, cat.name AS category_name, i.{$this->priceColumn} AS selling_price, i.{$this->wholesalePriceColumn} AS wholesale_price, i.{$this->itemCodeColumn} AS item_code, i.{$this->qtyColumn} AS qty, i.{$this->descColumn} AS description FROM items i LEFT JOIN item_categories cat ON i.category_id = cat.id ORDER BY i.{$this->orderByColumn}");
        return $this->db->resultSet();
    }

    /**
     * Alias method for SalesController compatibility.
     * Prevents "Call to undefined method Item::getAllItems()" Fatal Error on create invoice page.
     */
    public function getAllItems() {
        return $this->getItems();
    }

    public function getItemById($id) {
        $this->db->query("SELECT *, {$this->priceColumn} AS selling_price, {$this->wholesalePriceColumn} AS wholesale_price, {$this->itemCodeColumn} AS item_code, {$this->qtyColumn} AS qty, {$this->descColumn} AS description FROM items WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Variation options for an item (PO mix resolver, GRN create, etc.).
     */
    public function getItemVariations($itemId) {
        if (!$itemId) {
            return [];
        }

        try {
            $this->db->query("
                SELECT ivo.*, v.name AS variation_name, vv.value_name
                FROM item_variation_options ivo
                JOIN variations v ON ivo.variation_id = v.id
                JOIN variation_values vv ON ivo.variation_value_id = vv.id
                WHERE ivo.item_id = :id
                ORDER BY v.name ASC, vv.value_name ASC
            ");
            $this->db->bind(':id', $itemId);
            return $this->db->resultSet() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getItemByCode($item_code) {
        $this->db->query("SELECT *, {$this->priceColumn} AS selling_price, {$this->wholesalePriceColumn} AS wholesale_price, {$this->itemCodeColumn} AS item_code, {$this->qtyColumn} AS qty, {$this->descColumn} AS description FROM items WHERE {$this->itemCodeColumn} = :item_code");
        $this->db->bind(':item_code', $item_code);
        return $this->db->single();
    }

    public function addItem($data) {
        $this->db->query("INSERT INTO items (
            {$this->itemCodeColumn}, name, {$this->priceColumn}, {$this->wholesalePriceColumn}, {$this->qtyColumn}, {$this->descColumn},
            barcode, category_id, brand, warehouse, alert_qty, unit, status, weight, sync_woo, variations_json, image_path,
            cost_price, warehouse_id, vendor_id, sample_code, retail_margin, wholesale_margin
        ) VALUES (
            :item_code, :name, :price, :wholesale_price, :qty, :description,
            :barcode, :category_id, :brand, :warehouse, :alert_qty, :unit, :status, :weight, :sync_woo, :variations_json, :image_path,
            :cost_price, :warehouse_id, :vendor_id, :sample_code, :retail_margin, :wholesale_margin
        )");

        $this->db->bind(':item_code', $data['item_code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':price', $data['selling_price']);
        $this->db->bind(':wholesale_price', $data['wholesale_price'] ?? '0.00');
        $this->db->bind(':qty', $data['qty']);
        $this->db->bind(':description', $data['description'] ?? '');
        $this->db->bind(':barcode', $data['barcode'] ?? '');
        $this->db->bind(':category_id', !empty($data['category_id']) ? intval($data['category_id']) : null);
        $this->db->bind(':brand', $data['brand'] ?? '');
        $this->db->bind(':warehouse', $data['warehouse'] ?? '');
        $this->db->bind(':alert_qty', intval($data['alert_qty'] ?? 5));
        $this->db->bind(':unit', $data['unit'] ?? 'pcs');
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':weight', $data['weight'] ?? '');
        $this->db->bind(':sync_woo', intval($data['sync_woo'] ?? 1));
        $this->db->bind(':variations_json', $data['variations_json'] ?? '[]');
        $this->db->bind(':image_path', $data['image_path'] ?? '');
        $this->db->bind(':cost_price', $data['cost_price'] ?? '0.00');
        $this->db->bind(':warehouse_id', $data['warehouse_id'] ?? null);
        $this->db->bind(':vendor_id', $data['vendor_id'] ?? null);
        $this->db->bind(':sample_code', $data['sample_code'] ?? null);
        $this->db->bind(':retail_margin', $data['retail_margin'] ?? '0.00');
        $this->db->bind(':wholesale_margin', $data['wholesale_margin'] ?? '0.00');

        return $this->db->execute();
    }

    public function updateItem($data) {
        $this->db->query("UPDATE items SET 
            {$this->itemCodeColumn} = :item_code,
            name = :name,
            {$this->priceColumn} = :price,
            {$this->wholesalePriceColumn} = :wholesale_price,
            {$this->qtyColumn} = :qty,
            {$this->descColumn} = :description,
            barcode = :barcode,
            category_id = :category_id,
            brand = :brand,
            warehouse = :warehouse,
            alert_qty = :alert_qty,
            unit = :unit,
            status = :status,
            weight = :weight,
            sync_woo = :sync_woo,
            variations_json = :variations_json,
            image_path = :image_path,
            cost_price = :cost_price,
            warehouse_id = :warehouse_id,
            vendor_id = :vendor_id,
            sample_code = :sample_code,
            retail_margin = :retail_margin,
            wholesale_margin = :wholesale_margin
            WHERE id = :id");

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':item_code', $data['item_code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':price', $data['selling_price']);
        $this->db->bind(':wholesale_price', $data['wholesale_price'] ?? '0.00');
        $this->db->bind(':qty', $data['qty']);
        $this->db->bind(':description', $data['description'] ?? '');
        $this->db->bind(':barcode', $data['barcode'] ?? '');
        $this->db->bind(':category_id', !empty($data['category_id']) ? intval($data['category_id']) : null);
        $this->db->bind(':brand', $data['brand'] ?? '');
        $this->db->bind(':warehouse', $data['warehouse'] ?? '');
        $this->db->bind(':alert_qty', intval($data['alert_qty'] ?? 5));
        $this->db->bind(':unit', $data['unit'] ?? 'pcs');
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':weight', $data['weight'] ?? '');
        $this->db->bind(':sync_woo', intval($data['sync_woo'] ?? 1));
        $this->db->bind(':variations_json', $data['variations_json'] ?? '[]');
        $this->db->bind(':image_path', $data['image_path'] ?? '');
        $this->db->bind(':cost_price', $data['cost_price'] ?? '0.00');
        $this->db->bind(':warehouse_id', $data['warehouse_id'] ?? null);
        $this->db->bind(':vendor_id', $data['vendor_id'] ?? null);
        $this->db->bind(':sample_code', $data['sample_code'] ?? null);
        $this->db->bind(':retail_margin', $data['retail_margin'] ?? '0.00');
        $this->db->bind(':wholesale_margin', $data['wholesale_margin'] ?? '0.00');

        return $this->db->execute();
    }

    public function updateStockOnly($id, $newQty) {
        if (is_numeric($this->qtyColumn)) {
            return true;
        }
        $this->db->query("UPDATE items SET {$this->qtyColumn} = :qty WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':qty', $newQty);
        return $this->db->execute();
    }
}