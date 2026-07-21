<?php

class Item {
    private $db;
    private $priceColumn = 'price'; // Default fallback
    private $itemCodeColumn = 'item_code';
    private $nameColumn = 'name';
    private $qtyColumn = 'quantity_on_hand';
    private $descColumn = 'description';
    private $wholesalePriceColumn = 'wholesale_price'; // For WholesaleX B2B compatibility
    private $orderByColumn = 'id DESC';
    private $hasQtyColumn = false;
    private $hasQuantityOnHandColumn = true;

    // Static cache for detected columns to optimize performance
    private static $cachedColumns = null;

    public function __construct() {
        $this->db = new Database();
        $this->detectColumns();
    }

    /**
     * Dynamically inspect the 'items' table schema to adapt to different column names.
     * Caches results in a static property to run exactly once per request.
     */
    private function detectColumns() {
        if (self::$cachedColumns !== null) {
            $this->priceColumn = self::$cachedColumns['priceColumn'];
            $this->itemCodeColumn = self::$cachedColumns['itemCodeColumn'];
            $this->nameColumn = self::$cachedColumns['nameColumn'];
            $this->qtyColumn = self::$cachedColumns['qtyColumn'];
            $this->descColumn = self::$cachedColumns['descColumn'];
            $this->wholesalePriceColumn = self::$cachedColumns['wholesalePriceColumn'];
            $this->orderByColumn = self::$cachedColumns['orderByColumn'];
            $this->hasQtyColumn = self::$cachedColumns['hasQtyColumn'];
            $this->hasQuantityOnHandColumn = self::$cachedColumns['hasQuantityOnHandColumn'];
            return;
        }

        $cached = Cache::get('items_schema_columns_v2');
        if ($cached) {
            self::$cachedColumns = $cached;
            $this->priceColumn = $cached['priceColumn'];
            $this->itemCodeColumn = $cached['itemCodeColumn'];
            $this->nameColumn = $cached['nameColumn'];
            $this->qtyColumn = $cached['qtyColumn'];
            $this->descColumn = $cached['descColumn'];
            $this->wholesalePriceColumn = $cached['wholesalePriceColumn'];
            $this->orderByColumn = $cached['orderByColumn'];
            $this->hasQtyColumn = $cached['hasQtyColumn'];
            $this->hasQuantityOnHandColumn = $cached['hasQuantityOnHandColumn'];
            return;
        }

        try {
            $this->db->query("DESCRIBE items");
            $columns = $this->db->resultSet();
            if ($columns) {
                $fields = array_map(function($col) {
                    return strtolower($col->Field ?? $col->field ?? '');
                }, $columns);

                $this->hasQtyColumn = in_array('qty', $fields);
                $this->hasQuantityOnHandColumn = in_array('quantity_on_hand', $fields);

                // 1. Detect Price
                $priceCol = 'price';
                foreach ($fields as $f) {
                    if ($f === 'selling_price' || $f === 'price' || $f === 'unit_price' || $f === 'rate') {
                        $priceCol = $f;
                        break;
                    }
                }
                $this->priceColumn = $priceCol;

                // 2. Detect Wholesale Price
                $wholesaleCol = 'wholesale_price';
                foreach ($fields as $f) {
                    if ($f === 'wholesale_price' || $f === 'b2b_price' || $f === 'wholesale' || $f === 'trade_price') {
                        $wholesaleCol = $f;
                        break;
                    }
                }
                $this->wholesalePriceColumn = $wholesaleCol;

                // 3. Detect Item Code / SKU
                $codeCol = 'item_code';
                foreach ($fields as $f) {
                    if ($f === 'item_code' || $f === 'sku' || $f === 'code' || $f === 'barcode') {
                        $codeCol = $f;
                        break;
                    }
                }
                $this->itemCodeColumn = $codeCol;

                // 4. Detect Name / Title
                $this->nameColumn = in_array('name', $fields) ? 'name' : (in_array('title', $fields) ? 'title' : 'name');

                // 5. Detect Qty / Stock
                $qtyCol = 'quantity_on_hand';
                if (in_array('quantity_on_hand', $fields)) {
                    $qtyCol = 'quantity_on_hand';
                } else {
                    foreach ($fields as $f) {
                        if ($f === 'quantity' || $f === 'stock' || $f === 'stock_quantity' || $f === 'stock_qty') {
                            $qtyCol = $f;
                            break;
                        }
                    }
                }
                $this->qtyColumn = $qtyCol;

                // 6. Detect Description
                $this->descColumn = in_array('description', $fields) ? 'description' : (in_array('desc', $fields) ? 'desc' : 'description');

                // Set order parameter
                if (in_array('created_at', $fields)) {
                    $this->orderByColumn = 'created_at DESC';
                } else {
                    $this->orderByColumn = 'id DESC';
                }

                // Cache the values
                self::$cachedColumns = [
                    'priceColumn' => $this->priceColumn,
                    'itemCodeColumn' => $this->itemCodeColumn,
                    'nameColumn' => $this->nameColumn,
                    'qtyColumn' => $this->qtyColumn,
                    'descColumn' => $this->descColumn,
                    'wholesalePriceColumn' => $this->wholesalePriceColumn,
                    'orderByColumn' => $this->orderByColumn,
                    'hasQtyColumn' => $this->hasQtyColumn,
                    'hasQuantityOnHandColumn' => $this->hasQuantityOnHandColumn,
                    'fields' => $fields
                ];
                Cache::set('items_schema_columns_v2', self::$cachedColumns, 86400);
            }
        } catch (Exception $e) {
            // Fall back silently
        }
    }

    /**
     * Sanitizes dynamic column names to prevent SQL Injection and satisfy static analysis.
     */
    private function safeCol($column) {
        $column = trim($column);
        if (preg_match('/^[a-zA-Z0-9_]+\s+(ASC|DESC)$/i', $column)) {
            return $column;
        }
        return preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    }

    /**
     * Builds standard SQL where clauses dynamically to keep database queries highly performant.
     */
    private function buildFilterConditions($filters, &$params) {
        $whereClauses = [];

        $priceCol = $this->safeCol($this->priceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $nameCol = $this->safeCol($this->nameColumn);
        $qtyCol = $this->safeCol($this->qtyColumn);

        if (!empty($filters['search'])) {
            $searchStr = trim($filters['search']);
            
            // Split search string into individual words/tokens
            $tokens = preg_split('/\s+/', $searchStr);
            
            // Limit tokens to prevent extreme database load on complex queries
            if (count($tokens) > 8) {
                $tokens = array_slice($tokens, 0, 8);
            }
            
            $tokenConditions = [];
            foreach ($tokens as $index => $token) {
                if (strlen($token) === 0) continue;
                
                $tokenParam = ':search_token_' . $index;
                // Match each word against SKU, Name, and Sample Code (order-independent)
                $tokenConditions[] = "(i.{$itemCodeCol} LIKE {$tokenParam} OR i.{$nameCol} LIKE {$tokenParam} OR i.sample_code LIKE {$tokenParam})";
                $params[$tokenParam] = '%' . $token . '%';
            }
            
            if (!empty($tokenConditions)) {
                $whereClauses[] = "(" . implode(" AND ", $tokenConditions) . ")";
            }
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $whereClauses[] = "CAST(i.{$priceCol} AS DECIMAL(10,2)) >= :min_price";
            $params[':min_price'] = floatval($filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $whereClauses[] = "CAST(i.{$priceCol} AS DECIMAL(10,2)) <= :max_price";
            $params[':max_price'] = floatval($filters['max_price']);
        }

        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'instock') {
                $whereClauses[] = "CAST(i.{$qtyCol} AS SIGNED) > 5";
            } elseif ($filters['stock_status'] === 'lowstock') {
                $whereClauses[] = "CAST(i.{$qtyCol} AS SIGNED) > 0 AND CAST(i.{$qtyCol} AS SIGNED) <= 5";
            } elseif ($filters['stock_status'] === 'outstock') {
                $whereClauses[] = "(CAST(i.{$qtyCol} AS SIGNED) <= 0 OR i.{$qtyCol} IS NULL)";
            }
        }

        if (!empty($filters['category_id'])) {
            $whereClauses[] = "i.category_id = :category_id";
            $params[':category_id'] = intval($filters['category_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $whereClauses[] = "i.status = :status";
            $params[':status'] = $filters['status'];
        }

        return !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
    }

    /**
     * Fetch items in paginated batches at database level (resolves PHP out of memory error).
     */
    public function getItemsPaged($limit, $offset, $filters = []) {
        $params = [];
        $whereSql = $this->buildFilterConditions($filters, $params);

        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $qtyCol = $this->safeCol($this->qtyColumn);
        $descCol = $this->safeCol($this->descColumn);
        $orderByCol = $this->safeCol($this->orderByColumn);

        $this->db->query("SELECT i.*, 
                          cat.name AS category_name,
                          i.{$priceCol} AS selling_price, 
                          i.{$wholesalePriceCol} AS wholesale_price, 
                          i.{$itemCodeCol} AS item_code, 
                          i.{$qtyCol} AS qty, 
                          i.{$descCol} AS description 
                          FROM items i 
                          LEFT JOIN item_categories cat ON i.category_id = cat.id
                          $whereSql 
                          ORDER BY i.{$orderByCol} 
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

        $this->db->query("SELECT COUNT(*) AS total FROM items i $whereSql");
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
            $qtyCol = $this->safeCol($this->qtyColumn);
            $this->db->query("SELECT 
                COUNT(*) AS total_items,
                SUM(CASE WHEN CAST({$qtyCol} AS SIGNED) <= 0 OR {$qtyCol} IS NULL THEN 1 ELSE 0 END) AS out_of_stock_count,
                SUM(CASE WHEN CAST({$qtyCol} AS SIGNED) > 0 AND CAST({$qtyCol} AS SIGNED) <= 5 THEN 1 ELSE 0 END) AS low_stock_count
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
        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $qtyCol = $this->safeCol($this->qtyColumn);
        $descCol = $this->safeCol($this->descColumn);
        $orderByCol = $this->safeCol($this->orderByColumn);

        $this->db->query("SELECT i.*, cat.name AS category_name, i.{$priceCol} AS selling_price, i.{$wholesalePriceCol} AS wholesale_price, i.{$itemCodeCol} AS item_code, i.{$qtyCol} AS qty, i.{$descCol} AS description FROM items i LEFT JOIN item_categories cat ON i.category_id = cat.id ORDER BY i.{$orderByCol}");
        return $this->db->resultSet();
    }

    public function getItemsDelta($lastSync = '') {
        $whereSql = '';
        if (!empty($lastSync)) {
            $fields = self::$cachedColumns['fields'] ?? [];
            if (in_array('updated_at', $fields)) {
                $whereSql = " WHERE i.updated_at > :last_sync";
            } elseif (in_array('created_at', $fields)) {
                $whereSql = " WHERE i.created_at > :last_sync";
            } else {
                $this->db->query("SHOW COLUMNS FROM items LIKE 'updated_at'");
                if ($this->db->single()) {
                    $whereSql = " WHERE i.updated_at > :last_sync";
                } else {
                    $this->db->query("SHOW COLUMNS FROM items LIKE 'created_at'");
                    if ($this->db->single()) {
                        $whereSql = " WHERE i.created_at > :last_sync";
                    }
                }
            }
        } else {
            $whereSql = " WHERE (i.status IS NULL OR i.status != 'inactive')";
        }

        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $qtyCol = $this->safeCol($this->qtyColumn);
        $descCol = $this->safeCol($this->descColumn);
        $orderByCol = $this->safeCol($this->orderByColumn);
        
        $this->db->query("SELECT i.id, i.name, i.category_id, cat.name AS category_name, i.quantity_reserved, i.image_path, i.brand, i.status, i.cost_price, i.sample_code, i.variations_json,
                                 i.{$priceCol} AS selling_price, i.{$wholesalePriceCol} AS wholesale_price, i.{$itemCodeCol} AS item_code, i.{$qtyCol} AS qty, i.{$descCol} AS description 
                          FROM items i 
                          LEFT JOIN item_categories cat ON i.category_id = cat.id 
                          $whereSql
                          ORDER BY i.{$orderByCol}");
                          
        if (!empty($lastSync)) {
            $this->db->bind(':last_sync', $lastSync);
        }
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
        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $qtyCol = $this->safeCol($this->qtyColumn);
        $descCol = $this->safeCol($this->descColumn);

        $this->db->query("SELECT *, {$priceCol} AS selling_price, {$wholesalePriceCol} AS wholesale_price, {$itemCodeCol} AS item_code, {$qtyCol} AS qty, {$descCol} AS description FROM items WHERE id = :id");
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
            $variations = $this->db->resultSet() ?: [];

            if (empty($variations)) {
                // Self-healing: check if variations_json exists and populate relation tables
                $this->db->query("SELECT variations_json FROM items WHERE id = :id LIMIT 1");
                $this->db->bind(':id', $itemId);
                $itemRow = $this->db->single();
                if ($itemRow && !empty($itemRow->variations_json)) {
                    $decoded = json_decode($itemRow->variations_json);
                    if (is_array($decoded) && !empty($decoded)) {
                        $this->syncVariationOptions($itemId, $itemRow->variations_json);
                        
                        // Re-query
                        $this->db->query("
                            SELECT ivo.*, v.name AS variation_name, vv.value_name
                            FROM item_variation_options ivo
                            JOIN variations v ON ivo.variation_id = v.id
                            JOIN variation_values vv ON ivo.variation_value_id = vv.id
                            WHERE ivo.item_id = :id
                            ORDER BY v.name ASC, vv.value_name ASC
                        ");
                        $this->db->bind(':id', $itemId);
                        $variations = $this->db->resultSet() ?: [];
                    }
                }
            }
            return $variations;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getItemByCode($item_code) {
        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $qtyCol = $this->safeCol($this->qtyColumn);
        $descCol = $this->safeCol($this->descColumn);

        $this->db->query("SELECT *, {$priceCol} AS selling_price, {$wholesalePriceCol} AS wholesale_price, {$itemCodeCol} AS item_code, {$qtyCol} AS qty, {$descCol} AS description FROM items WHERE {$itemCodeCol} = :item_code");
        $this->db->bind(':item_code', $item_code);
        return $this->db->single();
    }

    public function addItem($data) {
        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $descCol = $this->safeCol($this->descColumn);

        $qtyColumnNames = [];
        $qtyParamBindings = [];
        if ($this->hasQtyColumn) {
            $qtyColumnNames[] = 'qty';
            $qtyParamBindings[] = ':qty';
        }
        if ($this->hasQuantityOnHandColumn) {
            $qtyColumnNames[] = 'quantity_on_hand';
            $qtyParamBindings[] = ':qty';
        }
        if (empty($qtyColumnNames)) {
            $qtyColumnNames[] = 'quantity_on_hand';
            $qtyParamBindings[] = ':qty';
        }

        $qtyColsStr = implode(', ', $qtyColumnNames);
        $qtyValsStr = implode(', ', $qtyParamBindings);

        $this->db->query("INSERT INTO items (
            {$itemCodeCol}, name, {$priceCol}, {$wholesalePriceCol}, {$qtyColsStr}, {$descCol},
            barcode, category_id, brand, warehouse, alert_qty, unit, status, weight, sync_woo, variations_json, image_path,
            additional_images, cost_price, warehouse_id, vendor_id, sample_code, retail_margin, wholesale_margin
        ) VALUES (
            :item_code, :name, :price, :wholesale_price, {$qtyValsStr}, :description,
            :barcode, :category_id, :brand, :warehouse, :alert_qty, :unit, :status, :weight, :sync_woo, :variations_json, :image_path,
            :additional_images, :cost_price, :warehouse_id, :vendor_id, :sample_code, :retail_margin, :wholesale_margin
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
        $this->db->bind(':additional_images', $data['additional_images'] ?? '[]');
        $this->db->bind(':cost_price', $data['cost_price'] ?? '0.00');
        $this->db->bind(':warehouse_id', $data['warehouse_id'] ?? null);
        $this->db->bind(':vendor_id', $data['vendor_id'] ?? null);
        $this->db->bind(':sample_code', $data['sample_code'] ?? null);
        $this->db->bind(':retail_margin', $data['retail_margin'] ?? '0.00');
        $this->db->bind(':wholesale_margin', $data['wholesale_margin'] ?? '0.00');

        if ($this->db->execute()) {
            $newItemId = $this->db->lastInsertId();
            if ($newItemId && !empty($data['variations_json'])) {
                $this->syncVariationOptions($newItemId, $data['variations_json']);
            }
            return true;
        }
        return false;
    }

    public function updateItem($data) {
        $priceCol = $this->safeCol($this->priceColumn);
        $wholesalePriceCol = $this->safeCol($this->wholesalePriceColumn);
        $itemCodeCol = $this->safeCol($this->itemCodeColumn);
        $descCol = $this->safeCol($this->descColumn);

        $qtyUpdates = [];
        if ($this->hasQtyColumn) {
            $qtyUpdates[] = "qty = :qty";
        }
        if ($this->hasQuantityOnHandColumn) {
            $qtyUpdates[] = "quantity_on_hand = :qty";
        }
        if (empty($qtyUpdates)) {
            $qtyUpdates[] = "quantity_on_hand = :qty";
        }
        $qtyUpdatesStr = implode(', ', $qtyUpdates);

        $this->db->query("UPDATE items SET 
            {$itemCodeCol} = :item_code,
            name = :name,
            {$priceCol} = :price,
            {$wholesalePriceCol} = :wholesale_price,
            {$qtyUpdatesStr},
            {$descCol} = :description,
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
            additional_images = :additional_images,
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
        $this->db->bind(':additional_images', $data['additional_images'] ?? '[]');
        $this->db->bind(':cost_price', $data['cost_price'] ?? '0.00');
        $this->db->bind(':warehouse_id', $data['warehouse_id'] ?? null);
        $this->db->bind(':vendor_id', $data['vendor_id'] ?? null);
        $this->db->bind(':sample_code', $data['sample_code'] ?? null);
        $this->db->bind(':retail_margin', $data['retail_margin'] ?? '0.00');
        $this->db->bind(':wholesale_margin', $data['wholesale_margin'] ?? '0.00');

        if ($this->db->execute()) {
            if (!empty($data['id'])) {
                $this->syncVariationOptions($data['id'], $data['variations_json'] ?? '[]');
            }
            return true;
        }
        return false;
    }

    public function updateStockOnly($id, $newQty) {
        if (is_numeric($this->qtyColumn)) {
            return true;
        }
        $qtyUpdates = [];
        if ($this->hasQtyColumn) {
            $qtyUpdates[] = "qty = :qty";
        }
        if ($this->hasQuantityOnHandColumn) {
            $qtyUpdates[] = "quantity_on_hand = :qty";
        }
        if (empty($qtyUpdates)) {
            $qtyUpdates[] = "quantity_on_hand = :qty";
        }
        $qtyUpdatesStr = implode(', ', $qtyUpdates);

        $this->db->query("UPDATE items SET {$qtyUpdatesStr} WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':qty', $newQty);
        return $this->db->execute();
    }

    /**
     * Increment or decrement stock by a delta in a schema-aware manner.
     * Prevents negative stock underflow using GREATEST(0, ...).
     */
    public function updateStockDelta($id, $delta, $variationOptionId = null) {
        if ($variationOptionId) {
            $this->db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) + :delta) WHERE id = :id");
            $this->db->bind(':id', $variationOptionId);
            $this->db->bind(':delta', $delta);
            $this->db->execute();

            // Calculate parent total stock as sum of variations to prevent drift
            $this->db->query("SELECT SUM(quantity_on_hand) AS total_qty FROM item_variation_options WHERE item_id = :item_id");
            $this->db->bind(':item_id', $id);
            $totalRow = $this->db->single();
            $newParentQty = floatval($totalRow->total_qty ?? 0);

            $qtyUpdates = [];
            if ($this->hasQtyColumn) {
                $qtyUpdates[] = "qty = :qty";
            }
            if ($this->hasQuantityOnHandColumn) {
                $qtyUpdates[] = "quantity_on_hand = :qty";
            }
            if (empty($qtyUpdates)) {
                $qtyUpdates[] = "quantity_on_hand = :qty";
            }
            $qtyUpdatesStr = implode(', ', $qtyUpdates);

            $this->db->query("UPDATE items SET {$qtyUpdatesStr} WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->bind(':qty', $newParentQty);
            $res = $this->db->execute();
            $this->syncVariationsJsonColumn($id);
            return $res;
        } else {
            $qtyUpdates = [];
            if ($this->hasQtyColumn) {
                $qtyUpdates[] = "qty = GREATEST(0, CAST(qty AS SIGNED) + :delta)";
            }
            if ($this->hasQuantityOnHandColumn) {
                $qtyUpdates[] = "quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) + :delta)";
            }
            if (empty($qtyUpdates)) {
                $qtyUpdates[] = "quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) + :delta)";
            }
            $qtyUpdatesStr = implode(', ', $qtyUpdates);

            $this->db->query("UPDATE items SET {$qtyUpdatesStr} WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->bind(':delta', $delta);
            return $this->db->execute();
        }
    }

    /**
     * Synchronizes variations_json from items table to relational item_variation_options,
     * variations, and variation_values tables.
     */
    public function syncVariationOptions($itemId, $variationsJson) {
        $decoded = json_decode($variationsJson);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $activeOptionIds = [];

        foreach ($decoded as $v) {
            $valueName = $v->attribute ?? $v->value ?? $v->value_name ?? '';
            if (empty($valueName)) {
                continue;
            }

            $sku = $v->sku ?? '';
            $price = floatval($v->price ?? 0);
            $wholesalePrice = floatval($v->wholesale_price ?? 0);
            $cost = floatval($v->cost ?? $v->cost_price ?? 0);
            $qty = floatval($v->qty ?? $v->quantity_on_hand ?? 0);

            // 1. Resolve variation attribute group name
            $attrName = 'Option';
            $this->db->query("
                SELECT pa.name 
                FROM product_attribute_terms pat 
                JOIN product_attributes pa ON pat.attribute_id = pa.id 
                WHERE pat.name = :term_name 
                LIMIT 1
            ");
            $this->db->bind(':term_name', $valueName);
            $patRow = $this->db->single();
            if ($patRow) {
                $attrName = $patRow->name;
            }

            // 2. Get or create variation record
            $this->db->query("SELECT id FROM variations WHERE name = :name LIMIT 1");
            $this->db->bind(':name', $attrName);
            $varRow = $this->db->single();
            if ($varRow) {
                $variationId = $varRow->id;
            } else {
                $this->db->query("INSERT INTO variations (name) VALUES (:name)");
                $this->db->bind(':name', $attrName);
                $this->db->execute();
                $variationId = $this->db->lastInsertId();
            }

            // 3. Get or create variation value record
            $this->db->query("SELECT id FROM variation_values WHERE variation_id = :var_id AND value_name = :val_name LIMIT 1");
            $this->db->bind(':var_id', $variationId);
            $this->db->bind(':val_name', $valueName);
            $vvRow = $this->db->single();
            if ($vvRow) {
                $variationValueId = $vvRow->id;
            } else {
                $this->db->query("INSERT INTO variation_values (variation_id, value_name) VALUES (:var_id, :val_name)");
                $this->db->bind(':var_id', $variationId);
                $this->db->bind(':val_name', $valueName);
                $this->db->execute();
                $variationValueId = $this->db->lastInsertId();
            }

            // 4. Check if variation option exists in item_variation_options
            $this->db->query("
                SELECT id 
                FROM item_variation_options 
                WHERE item_id = :item_id AND variation_value_id = :val_id 
                LIMIT 1
            ");
            $this->db->bind(':item_id', $itemId);
            $this->db->bind(':val_id', $variationValueId);
            $ivoRow = $this->db->single();

            if ($ivoRow) {
                $optionId = $ivoRow->id;
                $this->db->query("
                    UPDATE item_variation_options 
                    SET sku = :sku, price = :price, wholesale_price = :wholesale_price, cost = :cost, quantity_on_hand = :qty 
                    WHERE id = :id
                ");
                $this->db->bind(':sku', $sku);
                $this->db->bind(':price', $price);
                $this->db->bind(':wholesale_price', $wholesalePrice);
                $this->db->bind(':cost', $cost);
                $this->db->bind(':qty', $qty);
                $this->db->bind(':id', $optionId);
                $this->db->execute();
            } else {
                $this->db->query("
                    INSERT INTO item_variation_options 
                    (item_id, variation_id, variation_value_id, sku, price, wholesale_price, cost, quantity_on_hand, quantity_reserved) 
                    VALUES (:item_id, :var_id, :val_id, :sku, :price, :wholesale_price, :cost, :qty, 0)
                ");
                $this->db->bind(':item_id', $itemId);
                $this->db->bind(':var_id', $variationId);
                $this->db->bind(':val_id', $variationValueId);
                $this->db->bind(':sku', $sku);
                $this->db->bind(':price', $price);
                $this->db->bind(':wholesale_price', $wholesalePrice);
                $this->db->bind(':cost', $cost);
                $this->db->bind(':qty', $qty);
                $this->db->execute();
                $optionId = $this->db->lastInsertId();
            }

            $activeOptionIds[] = $optionId;
        }

        // 5. Delete removed variation options
        if (!empty($activeOptionIds)) {
            $idsPlaceholders = implode(',', array_map('intval', $activeOptionIds));
            $this->db->query("DELETE FROM item_variation_options WHERE item_id = :item_id AND id NOT IN ($idsPlaceholders)");
            $this->db->bind(':item_id', $itemId);
            $this->db->execute();
        } else {
            $this->db->query("DELETE FROM item_variation_options WHERE item_id = :item_id");
            $this->db->bind(':item_id', $itemId);
            $this->db->execute();
        }

        // 6. Resync variations_json to reflect final relational option states
        $this->syncVariationsJsonColumn($itemId);
    }

    /**
     * Synchronize the `variations_json` column of the `items` table
     * with the current values in `item_variation_options` and `variation_values`.
     */
    public function syncVariationsJsonColumn($itemId) {
        $itemId = intval($itemId);
        try {
            $this->db->query("
                SELECT ivo.id, ivo.sku, ivo.price, ivo.wholesale_price, ivo.cost, ivo.quantity_on_hand, vv.value_name AS attribute
                FROM item_variation_options ivo
                LEFT JOIN variation_values vv ON ivo.variation_value_id = vv.id
                WHERE ivo.item_id = :id
            ");
            $this->db->bind(':id', $itemId);
            $options = $this->db->resultSet() ?: [];
            
            $jsonArray = [];
            foreach ($options as $opt) {
                $jsonArray[] = [
                    'id' => intval($opt->id),
                    'sku' => $opt->sku,
                    'price' => floatval($opt->price),
                    'wholesale_price' => floatval($opt->wholesale_price ?? 0),
                    'cost' => floatval($opt->cost),
                    'qty' => intval($opt->quantity_on_hand),
                    'quantity_on_hand' => intval($opt->quantity_on_hand),
                    'attribute' => $opt->attribute
                ];
            }
            
            $jsonStr = json_encode($jsonArray);
            
            $this->db->query("UPDATE items SET variations_json = :variations_json WHERE id = :id");
            $this->db->bind(':variations_json', $jsonStr);
            $this->db->bind(':id', $itemId);
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error in syncVariationsJsonColumn: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Automatically generate sample codes ONLY for products that don't have one yet.
     * Existing sample codes are preserved and never overwritten.
     * Orders categories alphabetically (name ASC) and then orders products within each
     * category by creation (id ASC). The first category starts at 100, second at 200, etc.
     */
    public function regenerateSampleCodes() {
        try {
            // Fetch all categories sorted by name ASC
            $this->db->query("SELECT id FROM item_categories ORDER BY name ASC");
            $categories = $this->db->resultSet() ?: [];

            $categoryIndex = 0;
            foreach ($categories as $cat) {
                // Determine base code (100, 200, 300, etc.)
                $baseCode = ($categoryIndex + 1) * 100;
                
                // Fetch all products in this category that DON'T have a sample_code yet, ordered by id ASC
                $this->db->query("SELECT id FROM items WHERE category_id = :category_id AND (sample_code IS NULL OR sample_code = '') ORDER BY id ASC");
                $this->db->bind(':category_id', $cat->id);
                $items = $this->db->resultSet() ?: [];
                
                $itemIndex = 0;
                foreach ($items as $item) {
                    $sampleCode = (string)($baseCode + $itemIndex);
                    
                    // Update this item's sample code
                    $this->db->query("UPDATE items SET sample_code = :sample_code WHERE id = :id");
                    $this->db->bind(':sample_code', $sampleCode);
                    $this->db->bind(':id', $item->id);
                    $this->db->execute();
                    
                    $itemIndex++;
                }
                
                $categoryIndex++;
            }

            // Products without any valid category still get NULL sample_code
            $this->db->query("UPDATE items SET sample_code = NULL WHERE (category_id IS NULL OR category_id = 0) AND (sample_code IS NOT NULL AND sample_code != '')");
            $this->db->execute();
            
        } catch (Exception $e) {
            // Log error or ignore gracefully
        }
    }

    public function deleteItem($id) {
        $this->db->beginTransaction();
        try {
            // Delete related discount rules
            $this->db->query("DELETE FROM discount_rules WHERE target_item_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Delete related item images
            $this->db->query("DELETE FROM item_images WHERE item_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Delete related variation options
            $this->db->query("DELETE FROM item_variation_options WHERE item_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Delete item itself
            $this->db->query("DELETE FROM items WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getPriceColumn() {
        return $this->priceColumn;
    }

    public function getWholesalePriceColumn() {
        return $this->wholesalePriceColumn;
    }

    /**
     * Fetch all item-supplier relationships for multi-supplier GRN product filtering
     */
    public function getAllItemSupplierMappings() {
        try {
            $this->db->query("SELECT item_id, supplier_id, last_cost_price, is_primary FROM item_suppliers");
            return $this->db->resultSet() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Link an item to a supplier or update the supplier-specific last cost price
     */
    public function linkItemToSupplier($itemId, $supplierId, $lastCostPrice = 0.00) {
        if (!$itemId || !$supplierId) {
            return false;
        }
        try {
            $this->db->query("
                INSERT INTO item_suppliers (item_id, supplier_id, last_cost_price, is_primary)
                VALUES (:item_id, :supplier_id, :last_cost_price, 0)
                ON DUPLICATE KEY UPDATE 
                    last_cost_price = CASE WHEN :last_cost_price_dup > 0 THEN :last_cost_price_dup ELSE last_cost_price END,
                    updated_at = CURRENT_TIMESTAMP
            ");
            $this->db->bind(':item_id', intval($itemId));
            $this->db->bind(':supplier_id', intval($supplierId));
            $this->db->bind(':last_cost_price', floatval($lastCostPrice));
            $this->db->bind(':last_cost_price_dup', floatval($lastCostPrice));
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Fetch all associated suppliers for a specific item
     */
    public function getItemSuppliers($itemId) {
        if (!$itemId) return [];
        try {
            $this->db->query("
                SELECT s.*, v.name as supplier_name 
                FROM item_suppliers s
                LEFT JOIN vendors v ON s.supplier_id = v.id
                WHERE s.item_id = :item_id
                ORDER BY s.is_primary DESC, v.name ASC
            ");
            $this->db->bind(':item_id', intval($itemId));
            return $this->db->resultSet() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Synchronize multiple supplier associations for a product
     */
    public function syncItemSuppliers($itemId, array $supplierIds, $primarySupplierId = null) {
        if (!$itemId) {
            return false;
        }
        try {
            $supplierIds = array_values(array_unique(array_map('intval', array_filter($supplierIds))));
            
            // Delete suppliers not in the array
            if (!empty($supplierIds)) {
                $placeholders = implode(',', $supplierIds);
                $this->db->query("DELETE FROM item_suppliers WHERE item_id = :item_id AND supplier_id NOT IN ($placeholders)");
                $this->db->bind(':item_id', intval($itemId));
                $this->db->execute();
            } else {
                $this->db->query("DELETE FROM item_suppliers WHERE item_id = :item_id");
                $this->db->bind(':item_id', intval($itemId));
                $this->db->execute();
            }

            // Insert or update each selected supplier
            foreach ($supplierIds as $supId) {
                $isPrimary = ($primarySupplierId && intval($supId) === intval($primarySupplierId)) ? 1 : 0;
                $this->db->query("
                    INSERT INTO item_suppliers (item_id, supplier_id, is_primary)
                    VALUES (:item_id, :supplier_id, :is_primary)
                    ON DUPLICATE KEY UPDATE 
                        is_primary = VALUES(is_primary),
                        updated_at = CURRENT_TIMESTAMP
                ");
                $this->db->bind(':item_id', intval($itemId));
                $this->db->bind(':supplier_id', intval($supId));
                $this->db->bind(':is_primary', $isPrimary);
                $this->db->execute();
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}