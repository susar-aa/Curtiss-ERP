<?php

class InventoryController extends Controller {
    private $itemModel;
    private $categoryModel;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        $this->itemModel = $this->model('Item');
        $this->categoryModel = $this->model('Category');
        $this->db = new Database(); // Added to check for database relations
    }

    /**
     * Validates if a category ID exists in the local database.
     * Prevents Foreign Key Integrity Constraint Violations.
     */
    private function validateCategoryId($categoryId) {
        if (empty($categoryId)) return null;
        
        $this->db->query("SELECT id FROM item_categories WHERE id = :id");
        $this->db->bind(':id', intval($categoryId));
        $row = $this->db->single();
        
        return $row ? intval($categoryId) : null;
    }

    /**
     * Render inventory list view with database-level pagination and filtering
     */
    public function index() {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = isset($_GET['per_page']) ? max(5, min(100, intval($_GET['per_page']))) : 15;

        $filters = [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'min_price' => isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : '',
            'max_price' => isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : '',
            'stock_status' => isset($_GET['stock_status']) ? trim($_GET['stock_status']) : ''
        ];

        $totalItems = $this->itemModel->countItems($filters);
        $totalPages = max(1, ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $items = $this->itemModel->getItemsPaged($perPage, $offset, $filters);
        $stats = $this->itemModel->getStockStats();

        $data = [
            'items' => $items,
            'stats' => $stats,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages
            ]
        ];

        $this->view('inventory/index', $data);
    }

    /**
     * Dedicated Stock Reservation Dashboard Page
     */
    public function reserved() {
        // Query to get simple products with non-zero reserved quantities
        $this->db->query("
            SELECT i.id, i.item_code, i.name as item_name, i.price, i.quantity_on_hand, i.quantity_reserved,
                   COALESCE(c.name, 'Uncategorized') as category_name,
                   (i.quantity_on_hand - i.quantity_reserved) as quantity_available
            FROM items i
            LEFT JOIN item_categories c ON i.category_id = c.id
            WHERE i.quantity_reserved > 0
            ORDER BY i.quantity_reserved DESC
        ");
        $reservedItems = $this->db->resultSet() ?: [];

        // Query to get product variations with non-zero reserved quantities
        $this->db->query("
            SELECT ivo.id, ivo.sku, ivo.price, ivo.quantity_on_hand, ivo.quantity_reserved,
                   i.name as item_name, i.item_code as parent_code,
                   CONCAT(v.name, ': ', vv.value_name) as variation_display,
                   (ivo.quantity_on_hand - ivo.quantity_reserved) as quantity_available
            FROM item_variation_options ivo
            JOIN items i ON ivo.item_id = i.id
            JOIN variations v ON ivo.variation_id = v.id
            JOIN variation_values vv ON ivo.variation_value_id = vv.id
            WHERE ivo.quantity_reserved > 0
            ORDER BY ivo.quantity_reserved DESC
        ");
        $reservedVariations = $this->db->resultSet() ?: [];

        // Query to get active route and customer-level commitments/invoices
        $this->db->query("
            SELECT ii.quantity as reserved_qty, ii.description as item_name, 
                   i.invoice_number, i.created_at as invoice_date, i.stock_status,
                   c.name as customer_name,
                   r.route_name
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            LEFT JOIN customers c ON i.customer_id = c.id
            LEFT JOIN rep_daily_routes r ON i.rep_route_id = r.id
            WHERE i.stock_status = 'reserved'
            ORDER BY i.created_at DESC
        ");
        $reservationDetails = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Stock Reservation Dashboard',
            'content_view' => 'inventory/reserved',
            'reserved_items' => $reservedItems,
            'reserved_variations' => $reservedVariations,
            'details' => $reservationDetails
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Export entire inventory catalog to a standard ERP CSV file.
     * Mapped to resolve category, warehouse, and vendor names instead of their internal IDs.
     */
    public function exportCSV() {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=inventory_export_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');

        // Write UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write CSV Header
        fputcsv($output, [
            'SKU',
            'Name',
            'Selling Price',
            'Wholesale Price',
            'Cost Price',
            'Quantity',
            'Description',
            'Barcode',
            'Category',
            'Brand',
            'Warehouse',
            'Vendor',
            'Alert Qty',
            'Unit',
            'Status',
            'Weight',
            'Retail Margin',
            'Wholesale Margin',
            'Sample Code',
            'Variations'
        ]);

        // Query all items resolving relation names
        $this->db->query("
            SELECT i.*, 
                   c.name AS category_name, 
                   w.name AS warehouse_name, 
                   v.name AS vendor_name
            FROM items i
            LEFT JOIN item_categories c ON i.category_id = c.id
            LEFT JOIN warehouses w ON i.warehouse_id = w.id
            LEFT JOIN vendors v ON i.vendor_id = v.id
            ORDER BY i.id DESC
        ");
        $items = $this->db->resultSet() ?: [];

        foreach ($items as $item) {
            fputcsv($output, [
                $item->item_code,
                $item->name,
                number_format(floatval($item->price ?? 0), 2, '.', ''),
                number_format(floatval($item->wholesale_price ?? 0), 2, '.', ''),
                number_format(floatval($item->cost_price ?? 0), 2, '.', ''),
                intval($item->qty ?? 0),
                $item->description ?? '',
                $item->barcode ?? '',
                $item->category_name ?? '',
                $item->brand ?? '',
                $item->warehouse_name ?? $item->warehouse ?? '',
                $item->vendor_name ?? '',
                intval($item->alert_qty ?? 5),
                $item->unit ?? 'pcs',
                $item->status ?? 'active',
                $item->weight ?? '',
                number_format(floatval($item->retail_margin ?? 0), 2, '.', ''),
                number_format(floatval($item->wholesale_margin ?? 0), 2, '.', ''),
                $item->sample_code ?? '',
                $item->variations_json ?? '[]'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Import custom ERP CSV file.
     * Updates products if SKU matches; inserts new products otherwise.
     * Category, Warehouse, and Vendor names are matched or created automatically on-the-fly.
     */
    public function importERPCSV() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['csv_file']['tmp_name'])) {
            $_SESSION['flash_error'] = "Please select a valid CSV file to upload.";
            header('Location: ' . APP_URL . '/inventory');
            exit;
        }

        $filepath = $_FILES['csv_file']['tmp_name'];

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $errors = [];
        $successLogs = [];
        $addedCount = 0;
        $updatedCount = 0;

        if (($handle = fopen($filepath, "r")) !== FALSE) {
            // Read headers
            $headers = fgetcsv($handle, 10000, ",");
            if (!$headers) {
                $_SESSION['flash_error'] = "The uploaded CSV file is empty or has invalid formatting.";
                header('Location: ' . APP_URL . '/inventory');
                exit;
            }

            // Clean headers (remove BOM and force lower)
            $headers = array_map(function($h) {
                // Strip UTF-8 BOM if present
                $bom = pack('H*', 'EFBBBF');
                $h = preg_replace("/^$bom/", '', $h);
                return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
            }, $headers);

            // Locate columns
            $skuIdx = array_search('sku', $headers);
            $nameIdx = array_search('name', $headers);
            $sellingPriceIdx = array_search('selling price', $headers);
            $wholesalePriceIdx = array_search('wholesale price', $headers);
            $costPriceIdx = array_search('cost price', $headers);
            $qtyIdx = array_search('quantity', $headers);
            $descIdx = array_search('description', $headers);
            $barcodeIdx = array_search('barcode', $headers);
            $categoryIdx = array_search('category', $headers);
            $brandIdx = array_search('brand', $headers);
            $warehouseIdx = array_search('warehouse', $headers);
            $vendorIdx = array_search('vendor', $headers);
            $alertQtyIdx = array_search('alert qty', $headers);
            $unitIdx = array_search('unit', $headers);
            $statusIdx = array_search('status', $headers);
            $weightIdx = array_search('weight', $headers);
            $retailMarginIdx = array_search('retail margin', $headers);
            $wholesaleMarginIdx = array_search('wholesale margin', $headers);
            $sampleCodeIdx = array_search('sample code', $headers);
            $variationsIdx = array_search('variations', $headers);

            if ($skuIdx === FALSE || $nameIdx === FALSE) {
                $_SESSION['flash_error'] = "Invalid CSV structure. Could not find required 'SKU' and 'Name' headers.";
                header('Location: ' . APP_URL . '/inventory');
                exit;
            }

            // Lookup maps
            $categoryMap = [];
            $warehouseMap = [];
            $vendorMap = [];

            // Fetch current lookup maps
            $this->db->query("SELECT id, name FROM item_categories");
            foreach ($this->db->resultSet() as $r) {
                $categoryMap[strtolower(trim($r->name))] = $r->id;
            }

            $this->db->query("SELECT id, name FROM warehouses");
            foreach ($this->db->resultSet() as $r) {
                $warehouseMap[strtolower(trim($r->name))] = $r->id;
            }

            $this->db->query("SELECT id, name FROM vendors");
            foreach ($this->db->resultSet() as $r) {
                $vendorMap[strtolower(trim($r->name))] = $r->id;
            }

            $rowCount = 0;
            $this->db->beginTransaction();

            try {
                while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $rowCount++;

                    $sku = trim($row[$skuIdx] ?? '');
                    $name = trim($row[$nameIdx] ?? '');

                    if (empty($sku)) {
                        $errors[] = "Row {$rowCount}: SKU is missing or empty. Skipped.";
                        continue;
                    }
                    if (empty($name)) {
                        $errors[] = "Row {$rowCount}: Name is missing or empty for SKU '{$sku}'. Skipped.";
                        continue;
                    }

                    $sellingPrice = floatval(trim($row[$sellingPriceIdx] ?? '0'));
                    $wholesalePrice = floatval(trim($row[$wholesalePriceIdx] ?? '0'));
                    $costPrice = floatval(trim($row[$costPriceIdx] ?? '0'));
                    $qty = intval(trim($row[$qtyIdx] ?? '0'));
                    $description = trim($row[$descIdx] ?? '');
                    $barcode = trim($row[$barcodeIdx] ?? '');
                    $categoryName = trim($row[$categoryIdx] ?? '');
                    $brand = trim($row[$brandIdx] ?? '');
                    $warehouseName = trim($row[$warehouseIdx] ?? '');
                    $vendorName = trim($row[$vendorIdx] ?? '');
                    $alertQty = intval(trim($row[$alertQtyIdx] ?? '5'));
                    $unit = trim($row[$unitIdx] ?? 'pcs');
                    $status = strtolower(trim($row[$statusIdx] ?? 'active'));
                    if ($status !== 'active' && $status !== 'inactive') {
                        $status = 'active';
                    }
                    $weight = trim($row[$weightIdx] ?? '');
                    $retailMargin = floatval(trim($row[$retailMarginIdx] ?? '0'));
                    $wholesaleMargin = floatval(trim($row[$wholesaleMarginIdx] ?? '0'));
                    $sampleCode = trim($row[$sampleCodeIdx] ?? '');
                    $variationsJson = trim($row[$variationsIdx] ?? '[]');

                    // Resolve category on-the-fly
                    $categoryId = null;
                    if (!empty($categoryName)) {
                        $catKey = strtolower($categoryName);
                        if (isset($categoryMap[$catKey])) {
                            $categoryId = $categoryMap[$catKey];
                        } else {
                            $this->db->query("INSERT INTO item_categories (name, description) VALUES (:name, :desc)");
                            $this->db->bind(':name', $categoryName);
                            $this->db->bind(':desc', 'Auto-created during CSV import');
                            if ($this->db->execute()) {
                                $newCatId = $this->db->lastInsertId();
                                $categoryMap[$catKey] = $newCatId;
                                $categoryId = $newCatId;
                                $successLogs[] = "Auto-created Category '{$categoryName}'";
                            }
                        }
                    }

                    // Resolve warehouse on-the-fly
                    $warehouseId = null;
                    if (!empty($warehouseName)) {
                        $whKey = strtolower($warehouseName);
                        if (isset($warehouseMap[$whKey])) {
                            $warehouseId = $warehouseMap[$whKey];
                        } else {
                            $this->db->query("INSERT INTO warehouses (name, location, is_default) VALUES (:name, 'Auto-created', 0)");
                            $this->db->bind(':name', $warehouseName);
                            if ($this->db->execute()) {
                                $newWhId = $this->db->lastInsertId();
                                $warehouseMap[$whKey] = $newWhId;
                                $warehouseId = $newWhId;
                                $successLogs[] = "Auto-created Warehouse '{$warehouseName}'";
                            }
                        }
                    }

                    // Resolve vendor on-the-fly
                    $vendorId = null;
                    if (!empty($vendorName)) {
                        $vKey = strtolower($vendorName);
                        if (isset($vendorMap[$vKey])) {
                            $vendorId = $vendorMap[$vKey];
                        } else {
                            $this->db->query("INSERT INTO vendors (name, email, phone, address) VALUES (:name, '', '', '')");
                            $this->db->bind(':name', $vendorName);
                            if ($this->db->execute()) {
                                $newVndId = $this->db->lastInsertId();
                                $vendorMap[$vKey] = $newVndId;
                                $vendorId = $newVndId;
                                $successLogs[] = "Auto-created Vendor '{$vendorName}'";
                            }
                        }
                    }

                    $itemData = [
                        'item_code' => $sku,
                        'name' => $name,
                        'selling_price' => $sellingPrice,
                        'wholesale_price' => $wholesalePrice,
                        'qty' => $qty,
                        'description' => $description,
                        'barcode' => $barcode,
                        'category_id' => $categoryId,
                        'brand' => $brand,
                        'warehouse' => $warehouseName,
                        'alert_qty' => $alertQty,
                        'unit' => $unit,
                        'status' => $status,
                        'weight' => $weight,
                        'sync_woo' => 1,
                        'variations_json' => $variationsJson,
                        'image_path' => '',
                        'cost_price' => $costPrice,
                        'warehouse_id' => $warehouseId,
                        'vendor_id' => $vendorId,
                        'sample_code' => $sampleCode,
                        'retail_margin' => $retailMargin,
                        'wholesale_margin' => $wholesaleMargin
                    ];

                    $existingItem = $this->itemModel->getItemByCode($sku);

                    if ($existingItem) {
                        $itemData['id'] = $existingItem->id;
                        $itemData['image_path'] = $existingItem->image_path ?? '';

                        // Track before/after changes for audit log
                        $oldValues = [];
                        $newValues = [];
                        $changesExist = false;
                        foreach (['selling_price', 'wholesale_price', 'cost_price', 'name', 'status', 'qty'] as $key) {
                            $oldVal = $existingItem->$key ?? null;
                            $newVal = $itemData[$key] ?? null;
                            if (floatval($oldVal) != floatval($newVal) || $oldVal != $newVal) {
                                $oldValues[$key] = $oldVal;
                                $newValues[$key] = $newVal;
                                $changesExist = true;
                            }
                        }

                        if ($this->itemModel->updateItem($itemData)) {
                            $updatedCount++;
                            if ($changesExist) {
                                $this->logActivity('Product Edited', 'Inventory', "Product '{$itemData['name']}' (Code: {$itemData['item_code']}) updated via custom ERP CSV import.", $existingItem->id, $oldValues, $newValues);
                            }
                        } else {
                            $errors[] = "Row {$rowCount}: Failed to update database record for SKU '{$sku}'.";
                        }
                    } else {
                        if ($this->itemModel->addItem($itemData)) {
                            $addedCount++;
                            $newItemId = $this->db->lastInsertId();
                            $this->logActivity('Product Created', 'Inventory', "Product '{$itemData['name']}' (Code: {$itemData['item_code']}) created via custom ERP CSV import.", $newItemId, null, $itemData);
                        } else {
                            $errors[] = "Row {$rowCount}: Failed to insert new product '{$name}' (SKU: '{$sku}') into database.";
                        }
                    }
                }

                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                $errors[] = "Database Transaction Failure: " . $e->getMessage();
            }

            fclose($handle);
        } else {
            $errors[] = "Could not read uploaded CSV file.";
        }

        // Store outcomes in session
        $_SESSION['import_results'] = [
            'added' => $addedCount,
            'updated' => $updatedCount,
            'errors' => $errors,
            'success_logs' => $successLogs
        ];

        header('Location: ' . APP_URL . '/inventory');
        exit;
    }

    /**
     * High-speed, Transactional CSV Catalog Importer.
     * Imports WooCommerce CSV sheets directly without loading overhead.
     * Preserves image URLs as remote CDN targets for fast loading.
     */
    public function importCSV() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['csv_file']['tmp_name'])) {
            $_SESSION['flash_error'] = "Please select a valid WooCommerce product CSV file to upload.";
            header('Location: ' . APP_URL . '/inventory');
            exit;
        }

        $filepath = $_FILES['csv_file']['tmp_name'];

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        if (($handle = fopen($filepath, "r")) !== FALSE) {
            // Fetch file headers
            $headers = fgetcsv($handle, 10000, ",");
            if (!$headers) {
                $_SESSION['flash_error'] = "The uploaded CSV file has an invalid structure or is empty.";
                header('Location: ' . APP_URL . '/inventory');
                exit;
            }

            // Standardize and sanitize CSV header keys
            $headers = array_map(function($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $headers);

            // Match WooCommerce CSV indexes
            $skuIdx = array_search('SKU', $headers);
            $typeIdx = array_search('Type', $headers);
            $nameIdx = array_search('Name', $headers);
            $descIdx = array_search('Description', $headers);
            $regularPriceIdx = array_search('Regular price', $headers);
            $b2bPriceIdx = array_search('B2B Users Base Price', $headers);
            $stockIdx = array_search('Stock', $headers);
            $imagesIdx = array_search('Images', $headers);
            $parentIdx = array_search('Parent', $headers);
            $weightIdx = array_search('Weight (kg)', $headers);
            $barcodeIdx = array_search('GTIN, UPC, EAN, or ISBN', $headers);
            $statusIdx = array_search('Published', $headers);

            // SKU and Name are required fields
            if ($skuIdx === FALSE || $nameIdx === FALSE) {
                $_SESSION['flash_error'] = "Invalid CSV layout. Could not locate required columns: 'SKU' and 'Name'.";
                header('Location: ' . APP_URL . '/inventory');
                exit;
            }

            $importedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            
            // Temporary container to map variation child rows
            $variationsGrouped = [];

            // Execute all queries inside a high-speed database transaction
            $this->db->beginTransaction();

            try {
                while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $sku = trim($row[$skuIdx] ?? '');
                    if (empty($sku)) {
                        $skippedCount++;
                        continue;
                    }

                    $type = !empty($typeIdx) ? strtolower(trim($row[$typeIdx])) : 'simple';
                    $name = !empty($nameIdx) ? trim($row[$nameIdx]) : 'Unnamed Product';
                    $description = !empty($descIdx) ? trim($row[$descIdx]) : '';
                    
                    // Direct price mapping matching B2B Users Base and Retail Prices
                    $retailPrice = !empty($regularPriceIdx) ? floatval($row[$regularPriceIdx]) : 0.00;
                    $wholesalePrice = !empty($b2bPriceIdx) ? floatval($row[$b2bPriceIdx]) : 0.00;
                    
                    $qty = !empty($stockIdx) && $row[$stockIdx] !== '' ? intval($row[$stockIdx]) : 0;
                    $barcode = !empty($barcodeIdx) ? trim($row[$barcodeIdx]) : '';
                    $weight = !empty($weightIdx) ? trim($row[$weightIdx]) : '';
                    
                    // Parse image links and download WooCommerce remote images locally
                    $imagePath = '';
                    if (!empty($imagesIdx) && !empty($row[$imagesIdx])) {
                        $imgUrls = explode(',', $row[$imagesIdx]);
                        $remoteUrl = trim($imgUrls[0]);
                        if (!empty($remoteUrl)) {
                            if (filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
                                $imgName = basename($remoteUrl);
                                if (($pos = strpos($imgName, '?')) !== false) {
                                    $imgName = substr($imgName, 0, $pos);
                                }
                                $imgName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $imgName);
                                if (empty($imgName)) {
                                    $imgName = 'prod_' . time() . '_' . rand(1000, 9999) . '.jpg';
                                }
                                if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $imgName)) {
                                    $imgName .= '.jpg';
                                }
                                
                                $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
                                if (!file_exists($uploadDir)) {
                                    @mkdir($uploadDir, 0777, true);
                                }
                                
                                $localFilePath = $uploadDir . '/' . $imgName;
                                
                                // Download image if it doesn't already exist locally
                                $downloadSuccess = false;
                                if (file_exists($localFilePath)) {
                                    $downloadSuccess = true;
                                } else {
                                    $imgData = @file_get_contents($remoteUrl);
                                    if ($imgData) {
                                        if (@file_put_contents($localFilePath, $imgData)) {
                                            $downloadSuccess = true;
                                        }
                                    }
                                }
                                
                                if ($downloadSuccess) {
                                    $imagePath = $imgName;
                                } else {
                                    // if download fails, fall back to remote URL so it doesn't break
                                    $imagePath = $remoteUrl;
                                }
                            } else {
                                $imagePath = $remoteUrl;
                            }
                        }
                    }

                    $publishedValue = !empty($statusIdx) ? trim($row[$statusIdx]) : '1';
                    $status = ($publishedValue === '1' || strtolower($publishedValue) === 'true') ? 'active' : 'inactive';

                    // Group variations by parent SKU
                    if ($type === 'variation') {
                        $parentSku = !empty($parentIdx) ? trim($row[$parentIdx]) : '';
                        if (!empty($parentSku)) {
                            $attrVal = 'Default';
                            $attrIdx = array_search('Attribute 1 value(s)', $headers);
                            if ($attrIdx !== FALSE && !empty($row[$attrIdx])) {
                                $attrVal = trim($row[$attrIdx]);
                            }

                            $variationsGrouped[$parentSku][] = [
                                'attribute' => $attrVal,
                                'sku' => $sku,
                                'cost_price' => 0.00, // CSV export doesn't map cost, defaults to 0
                                'price' => $retailPrice,
                                'wholesale_price' => $wholesalePrice
                            ];
                        }
                        continue;
                    }

                    // Simple or Variable parent insertion
                    $existingItem = $this->itemModel->getItemByCode($sku);

                    $data = [
                        'item_code' => $sku,
                        'name' => $name,
                        'selling_price' => $retailPrice,
                        'wholesale_price' => $wholesalePrice,
                        'qty' => $qty,
                        'description' => $description,
                        'barcode' => $barcode,
                        'category_id' => null,
                        'brand' => '',
                        'warehouse' => '',
                        'alert_qty' => 5,
                        'unit' => 'pcs',
                        'status' => $status,
                        'weight' => $weight,
                        'sync_woo' => 1,
                        'variations_json' => '[]',
                        'image_path' => $imagePath,
                        'cost_price' => 0.00,
                        'warehouse_id' => null,
                        'vendor_id' => null
                    ];

                    if ($existingItem) {
                        $data['id'] = $existingItem->id;
                        if (empty($imagePath)) {
                            $data['image_path'] = $existingItem->image_path ?? '';
                        }
                        
                        // Compare for logging inside CSV import
                        $oldValues = [];
                        $newValues = [];
                        $changesExist = false;
                        foreach (['selling_price', 'wholesale_price', 'cost_price', 'name', 'status'] as $key) {
                            $oldVal = $existingItem->$key ?? null;
                            $newVal = $data[$key] ?? null;
                            if (floatval($oldVal) != floatval($newVal) || $oldVal != $newVal) {
                                $oldValues[$key] = $oldVal;
                                $newValues[$key] = $newVal;
                                $changesExist = true;
                            }
                        }

                        if ($this->itemModel->updateItem($data)) {
                            $updatedCount++;
                            if (!empty($imagePath)) {
                                $this->syncItemImagesTable($existingItem->id, $imagePath);
                            }
                            if ($changesExist) {
                                $this->logActivity('Product Edited', 'Inventory', "Product '{$data['name']}' (Code: {$data['item_code']}) updated via WooCommerce CSV import.", $existingItem->id, $oldValues, $newValues);
                            }
                        }
                    } else {
                        if ($this->itemModel->addItem($data)) {
                            $importedCount++;
                            $newItemId = $this->db->lastInsertId();
                            if ($newItemId && !empty($imagePath)) {
                                $this->syncItemImagesTable($newItemId, $imagePath);
                            }
                            $this->logActivity('Product Created', 'Inventory', "Product '{$data['name']}' (Code: {$data['item_code']}) created via WooCommerce CSV import.", $newItemId, null, $data);
                        }
                    }
                }

                // Append parsed child variations to their variable parents
                foreach ($variationsGrouped as $parentSku => $vList) {
                    $parentItem = $this->itemModel->getItemByCode($parentSku);
                    if ($parentItem) {
                        $parentData = [
                            'id' => $parentItem->id,
                            'item_code' => $parentItem->item_code,
                            'name' => $parentItem->name,
                            'selling_price' => $parentItem->selling_price,
                            'wholesale_price' => $parentItem->wholesale_price,
                            'qty' => $parentItem->qty,
                            'description' => $parentItem->description,
                            'barcode' => $parentItem->barcode,
                            'category_id' => $parentItem->category_id,
                            'brand' => $parentItem->brand,
                            'warehouse' => $parentItem->warehouse,
                            'alert_qty' => $parentItem->alert_qty,
                            'unit' => $parentItem->unit,
                            'status' => $parentItem->status,
                            'weight' => $parentItem->weight,
                            'sync_woo' => $parentItem->sync_woo,
                            'variations_json' => json_encode($vList),
                            'image_path' => $parentItem->image_path,
                            'cost_price' => $parentItem->cost_price ?? 0.00,
                            'warehouse_id' => $parentItem->warehouse_id ?? null,
                            'vendor_id' => $parentItem->vendor_id ?? null
                        ];
                        $this->itemModel->updateItem($parentData);
                    }
                }

                $this->db->commit();
                $_SESSION['flash_success'] = "WooCommerce CSV import completed successfully! Newly registered <strong>{$importedCount}</strong> products and updated <strong>{$updatedCount}</strong> profiles instantly.";

            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_error'] = "Database Transaction Error: " . $e->getMessage();
            }

            fclose($handle);
        }

        header('Location: ' . APP_URL . '/inventory');
        exit;
    }

    /**
     * One-click self-healing migration route to download all legacy external WooCommerce image URLs 
     * locally and convert database path values to unified filenames.
     */
    public function migrateImages() {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $this->db->query("SELECT id, image_path FROM items WHERE image_path LIKE 'http%'");
        $itemsToMigrate = $this->db->resultSet() ?: [];

        $successCount = 0;
        $failedCount = 0;

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        foreach ($itemsToMigrate as $item) {
            $remoteUrl = trim($item->image_path);
            if (empty($remoteUrl) || !filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
                continue;
            }

            $imgName = basename($remoteUrl);
            if (($pos = strpos($imgName, '?')) !== false) {
                $imgName = substr($imgName, 0, $pos);
            }
            $imgName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $imgName);
            if (empty($imgName)) {
                $imgName = 'prod_migrated_' . $item->id . '_' . time() . '.jpg';
            }
            if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $imgName)) {
                $imgName .= '.jpg';
            }

            $localFilePath = $uploadDir . '/' . $imgName;

            // Download legacy image if not already present locally
            $downloadSuccess = false;
            if (file_exists($localFilePath)) {
                $downloadSuccess = true;
            } else {
                $imgData = @file_get_contents($remoteUrl);
                if ($imgData) {
                    if (@file_put_contents($localFilePath, $imgData)) {
                        $downloadSuccess = true;
                    }
                }
            }

            if ($downloadSuccess) {
                // Update item image_path in database to store just the filename
                $this->db->query("UPDATE items SET image_path = :image_path WHERE id = :id");
                $this->db->bind(':image_path', $imgName);
                $this->db->bind(':id', $item->id);
                if ($this->db->execute()) {
                    $this->syncItemImagesTable($item->id, $imgName);
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        // --- SECONDARY SYNC FOR ITEM_IMAGES TABLE ---
        try {
            $this->db->query("SHOW TABLES LIKE 'item_images'");
            if ($this->db->single()) {
                $this->db->query("SELECT id, item_id, image_path FROM item_images WHERE image_path LIKE 'http%'");
                $imagesToMigrate = $this->db->resultSet() ?: [];
                foreach ($imagesToMigrate as $img) {
                    $remoteUrl = trim($img->image_path);
                    if (empty($remoteUrl) || !filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
                        continue;
                    }

                    $imgName = basename($remoteUrl);
                    if (($pos = strpos($imgName, '?')) !== false) {
                        $imgName = substr($imgName, 0, $pos);
                    }
                    $imgName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $imgName);
                    if (empty($imgName)) {
                        $imgName = 'prod_migrated_img_' . $img->id . '_' . time() . '.jpg';
                    }
                    if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $imgName)) {
                        $imgName .= '.jpg';
                    }

                    $localFilePath = $uploadDir . '/' . $imgName;

                    $downloadSuccess = false;
                    if (file_exists($localFilePath)) {
                        $downloadSuccess = true;
                    } else {
                        $imgData = @file_get_contents($remoteUrl);
                        if ($imgData) {
                            if (@file_put_contents($localFilePath, $imgData)) {
                                $downloadSuccess = true;
                            }
                        }
                    }

                    if ($downloadSuccess) {
                        $this->db->query("UPDATE item_images SET image_path = :image_path WHERE id = :id");
                        $this->db->bind(':image_path', $imgName);
                        $this->db->bind(':id', $img->id);
                        $this->db->execute();
                        
                        // Also sync to items table if empty
                        $this->db->query("UPDATE items SET image_path = :image_path WHERE id = :id AND (image_path IS NULL OR image_path = '' OR image_path LIKE 'http%')");
                        $this->db->bind(':image_path', $imgName);
                        $this->db->bind(':id', $img->item_id);
                        $this->db->execute();
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore error
        }

        $_SESSION['flash_success'] = "Image migration completed. Successfully migrated <strong>{$successCount}</strong> remote WooCommerce images to local storage 'uploads/products/'. Failed/Skipped: <strong>{$failedCount}</strong>.";
        header('Location: ' . APP_URL . '/inventory');
        exit;
    }

    /**
     * Add new inventory item with live database category, supplier, and warehouse listings
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rawBase64 = $_POST['compressed_image_base64'] ?? '';
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Save locally uploaded photo (supports standard multipart file or client-side base64)
            $imagePath = '';
            
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image_file']['tmp_name'];
                $fileName = $_FILES['image_file']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    
                    $newFileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                    $destPath = $uploadDir . '/' . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $imagePath = 'public/uploads/products/' . $newFileName; // Save relative path to DB
                        $this->compressImagePHP($destPath, $fileExtension);
                    }
                }
            } elseif (!empty($rawBase64)) {
                $base64 = html_entity_decode($rawBase64, ENT_QUOTES, 'UTF-8');
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $ext = strtolower($type[1]);
                } else {
                    $ext = 'jpg';
                }
                $binary = base64_decode($base64);
                if ($binary) {
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (file_put_contents($uploadDir . '/' . $fileName, $binary)) {
                        $imagePath = 'public/uploads/products/' . $fileName; // Save relative path to DB
                    }
                }
            }

            // Sanitize database selections
            $catId = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
            $catId = $this->validateCategoryId($catId);

            $warehouseId = isset($_POST['warehouse_id']) && $_POST['warehouse_id'] !== '' ? intval($_POST['warehouse_id']) : null;
            $vendorId = isset($_POST['vendor_id']) && $_POST['vendor_id'] !== '' ? intval($_POST['vendor_id']) : null;

            $data = [
                'item_code' => trim($_POST['item_code']),
                'name' => trim($_POST['name']),
                'selling_price' => trim($_POST['selling_price'] ?? '0.00'),
                'wholesale_price' => trim($_POST['wholesale_price'] ?? '0.00'),
                'cost_price' => trim($_POST['cost_price'] ?? '0.00'),
                'qty' => 0, // Stock fields removed from form, defaulted to zero safely
                'description' => trim($_POST['description'] ?? ''),
                'barcode' => trim($_POST['barcode'] ?? ''),
                'category_id' => $catId,
                'brand' => trim($_POST['brand'] ?? ''),
                'warehouse' => '',
                'warehouse_id' => $warehouseId,
                'vendor_id' => $vendorId,
                'alert_qty' => trim($_POST['alert_qty'] ?? '5'),
                'unit' => trim($_POST['unit'] ?? 'pcs'),
                'status' => trim($_POST['status'] ?? 'active'),
                'weight' => trim($_POST['weight'] ?? ''),
                'sync_woo' => isset($_POST['sync_woo']) ? 1 : 0,
                'variations_json' => html_entity_decode(trim($_POST['variations_json'] ?? '[]'), ENT_QUOTES, 'UTF-8'),
                'image_path' => $imagePath,
                'retail_margin' => trim($_POST['retail_margin'] ?? '0.00'),
                'wholesale_margin' => trim($_POST['wholesale_margin'] ?? '0.00'),
                'sample_code' => trim($_POST['sample_code'] ?? '')
            ];

            if ($this->itemModel->addItem($data)) {
                $newItemId = $this->db->lastInsertId();
                if ($newItemId && !empty($imagePath)) {
                    $this->syncItemImagesTable($newItemId, $imagePath);
                }
                $this->logActivity('Product Created', 'Inventory', "Product '{$data['name']}' (Code: {$data['item_code']}) created successfully.", $newItemId, null, $data);
                header('Location: ' . APP_URL . '/inventory');
                exit;
            } else {
                die('Something went wrong saving the item to the ERP DB.');
            }
        } else {
            // Dynamic Selections
            $categories = $this->categoryModel->getCategories();
            $vendors = $this->getVendorsDropdown();
            $warehouses = $this->getWarehousesDropdown();

            $data = [
                'title' => 'Create New Inventory Product',
                'item' => null,
                'categories' => $categories,
                'vendors' => $vendors,
                'warehouses' => $warehouses
            ];
            $this->view('inventory/form', $data);
        }
    }

    /**
     * Edit existing inventory item with live database categories, suppliers, and warehouses
     */
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rawBase64 = $_POST['compressed_image_base64'] ?? '';
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $existingItem = $this->itemModel->getItemById($id);
            $imagePath = $existingItem->image_path ?? '';

            $imageDeleted = ($_POST['image_deleted'] ?? '0') === '1';
            if ($imageDeleted) {
                $imagePath = '';
            }

            // Save locally uploaded photo (supports standard multipart file or client-side base64)
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image_file']['tmp_name'];
                $fileName = $_FILES['image_file']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    
                    $newFileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                    $destPath = $uploadDir . '/' . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $imagePath = 'public/uploads/products/' . $newFileName; // Save relative path to DB
                        $this->compressImagePHP($destPath, $fileExtension);
                    }
                }
            } elseif (!empty($rawBase64)) {
                $base64 = html_entity_decode($rawBase64, ENT_QUOTES, 'UTF-8');
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $ext = strtolower($type[1]);
                } else {
                    $ext = 'jpg';
                }
                $binary = base64_decode($base64);
                if ($binary) {
                    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (file_put_contents($uploadDir . '/' . $fileName, $binary)) {
                        $imagePath = 'public/uploads/products/' . $fileName; // Save relative path to DB
                    }
                }
            }

            // Sanitize database selections
            $catId = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
            $catId = $this->validateCategoryId($catId);

            $warehouseId = isset($_POST['warehouse_id']) && $_POST['warehouse_id'] !== '' ? intval($_POST['warehouse_id']) : null;
            $vendorId = isset($_POST['vendor_id']) && $_POST['vendor_id'] !== '' ? intval($_POST['vendor_id']) : null;

            $data = [
                'id' => $id,
                'item_code' => trim($_POST['item_code']),
                'name' => trim($_POST['name']),
                'selling_price' => trim($_POST['selling_price'] ?? '0.00'),
                'wholesale_price' => trim($_POST['wholesale_price'] ?? '0.00'),
                'cost_price' => trim($_POST['cost_price'] ?? '0.00'),
                'qty' => $existingItem->qty ?? 0, // preserve stock levels safely, no manual form edits
                'description' => trim($_POST['description'] ?? ''),
                'barcode' => trim($_POST['barcode'] ?? ''),
                'category_id' => $catId,
                'brand' => trim($_POST['brand'] ?? ''),
                'warehouse' => '',
                'warehouse_id' => $warehouseId,
                'vendor_id' => $vendorId,
                'alert_qty' => trim($_POST['alert_qty'] ?? '5'),
                'unit' => trim($_POST['unit'] ?? 'pcs'),
                'status' => trim($_POST['status'] ?? 'active'),
                'weight' => trim($_POST['weight'] ?? ''),
                'sync_woo' => isset($_POST['sync_woo']) ? 1 : 0,
                'variations_json' => html_entity_decode(trim($_POST['variations_json'] ?? '[]'), ENT_QUOTES, 'UTF-8'),
                'image_path' => $imagePath,
                'retail_margin' => trim($_POST['retail_margin'] ?? '0.00'),
                'wholesale_margin' => trim($_POST['wholesale_margin'] ?? '0.00'),
                'sample_code' => trim($_POST['sample_code'] ?? '')
            ];

            if ($this->itemModel->updateItem($data)) {
                if (!empty($imagePath)) {
                    $this->syncItemImagesTable($id, $imagePath);
                }
                
                // Track before/after changes for audit log
                $oldValues = [];
                $newValues = [];
                $changes = [];
                
                $fieldsToCompare = [
                    'item_code' => 'Code',
                    'name' => 'Name',
                    'selling_price' => 'Selling Price',
                    'wholesale_price' => 'Wholesale Price',
                    'cost_price' => 'Cost Price',
                    'description' => 'Description',
                    'barcode' => 'Barcode',
                    'category_id' => 'Category ID',
                    'brand' => 'Brand',
                    'status' => 'Status',
                    'image_path' => 'Image Path'
                ];
                
                if ($existingItem) {
                    foreach ($fieldsToCompare as $key => $label) {
                        $oldVal = $existingItem->$key ?? null;
                        $newVal = $data[$key] ?? null;
                        if ($key === 'selling_price' || $key === 'wholesale_price' || $key === 'cost_price') {
                            if (floatval($oldVal) !== floatval($newVal)) {
                                $oldValues[$key] = floatval($oldVal);
                                $newValues[$key] = floatval($newVal);
                                $changes[] = "$label changed from " . number_format(floatval($oldVal), 2) . " to " . number_format(floatval($newVal), 2);
                            }
                        } else {
                            if ($oldVal != $newVal) {
                                $oldValues[$key] = $oldVal;
                                $newValues[$key] = $newVal;
                                $changes[] = "$label changed from '" . ($oldVal ?: 'None') . "' to '" . ($newVal ?: 'None') . "'";
                            }
                        }
                    }
                }
                
                $desc = "Product '{$data['name']}' (Code: {$data['item_code']}) updated.";
                if (!empty($changes)) {
                    $desc .= " Changes: " . implode(', ', $changes);
                } else {
                    $desc .= " No values changed.";
                }
                
                $this->logActivity('Product Edited', 'Inventory', $desc, $id, $oldValues, $newValues);
                
                header('Location: ' . APP_URL . '/inventory');
                exit;
            } else {
                die('Something went wrong updating the item.');
            }
        } else {
            $item = $this->itemModel->getItemById($id);
            
            // Dynamic Selections
            $categories = $this->categoryModel->getCategories();
            $vendors = $this->getVendorsDropdown();
            $warehouses = $this->getWarehousesDropdown();

            $data = [
                'title' => 'Edit Inventory Product Profile',
                'item' => $item,
                'categories' => $categories,
                'vendors' => $vendors,
                'warehouses' => $warehouses
            ];
            $this->view('inventory/form', $data);
        }
    }

    /**
     * Safe Query Helper for Vendors dropdown
     */
    private function getVendorsDropdown() {
        try {
            $this->db->query("SELECT id, name FROM vendors ORDER BY name ASC");
            return $this->db->resultSet() ?: [];
        } catch (Exception $e) {
            try {
                $this->db->query("SELECT id, name FROM suppliers ORDER BY name ASC");
                return $this->db->resultSet() ?: [];
            } catch (Exception $e2) {
                return [];
            }
        }
    }

    /**
     * Safe Query Helper for Warehouses dropdown
     */
    private function getWarehousesDropdown() {
        try {
            $this->db->query("SELECT id, name FROM warehouses ORDER BY name ASC");
            return $this->db->resultSet() ?: [];
        } catch (Exception $e) {
            try {
                $this->db->query("SELECT id, name FROM item_warehouses ORDER BY name ASC");
                return $this->db->resultSet() ?: [];
            } catch (Exception $e2) {
                return [];
            }
        }
    }

    /**
     * View Product Pricing & Inventory Transaction History Dashboard
     */
    public function history() {
        // Suppress errors for this method to prevent HTML output
        error_reporting(0);
        ini_set('display_errors', 0);

        try {
            $catalogItems = $this->itemModel->getAllItems();
            foreach($catalogItems as $item) {
                $item->variations = $this->itemModel->getItemVariations($item->id);
            }
        } catch (Exception $e) {
            $catalogItems = [];
        }

        $data = [
            'title' => 'Product History & Pricing Audit',
            'content_view' => 'inventory/history',
            'catalog_items' => $catalogItems
        ];
        $this->view('layouts/main', $data);
    }

    /**
     * Ajax Endpoint: Fetch pricing timeline and stock events of any product
     */
    public function get_price_history() {
        // Set up custom error handler to catch all PHP errors and return JSON
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'PHP Error: ' . $errstr]);
            exit;
        });

        // Suppress error output to ensure clean JSON response
        error_reporting(0);
        ini_set('display_errors', 0);

        header('Content-Type: application/json');

        try {
            $itemId = $_GET['item_id'] ?? null;
            $varOptId = $_GET['var_opt_id'] ?? null;

            if (!$itemId) {
                echo json_encode(['success' => false, 'error' => 'Product ID is required']);
                exit;
            }

            $varOptId = ($varOptId === '0' || empty($varOptId)) ? null : $varOptId;

            // Check if items table exists
            $this->db->query("SHOW TABLES LIKE 'items'");
            $tableExists = $this->db->single();
            if (!$tableExists) {
                echo json_encode(['success' => false, 'error' => 'Items table not found']);
                exit;
            }

            // 1. Fetch current pricing & meta - simplified to avoid join errors
            // Select all columns and handle mapping in PHP to avoid column name errors
            $currentSql = "SELECT * FROM items WHERE id = :item_id LIMIT 1";
            $this->db->query($currentSql);
            $this->db->bind(':item_id', $itemId);
            $item = $this->db->single();

            if (!$item) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }

            // Map columns dynamically based on what exists
            $currentMeta = (object)[
                'name' => $item->name ?? '',
                'item_code' => $item->item_code ?? $item->sku ?? $item->code ?? '',
                'product_name' => $item->name ?? '',
                'current_retail' => $item->selling_price ?? $item->price ?? $item->unit_price ?? $item->rate ?? 0,
                'current_wholesale' => $item->wholesale_price ?? $item->b2b_price ?? $item->wholesale ?? $item->trade_price ?? 0,
                'current_cost' => $item->cost_price ?? 0,
                'current_stock' => $item->qty ?? $item->quantity ?? $item->stock ?? $item->stock_quantity ?? $item->stock_qty ?? 0
            ];

            if (!$currentMeta) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }

            // 2. Fetch history timeline - simplified to avoid errors from missing tables
            $timeline = [];

            try {
                // Check if GRN tables exist before querying
                $this->db->query("SHOW TABLES LIKE 'grn_items'");
                if ($this->db->single()) {
                    $grnSql = "
                        SELECT
                            grn.grn_date AS date_occurred,
                            'GRN Received' AS event_type,
                            gri.quantity AS quantity_changed,
                            gri.unit_cost AS cost_price,
                            gri.selling_price AS selling_price,
                            gri.wholesale_price AS wholesale_price,
                            CONCAT('GRN #', grn.grn_number) AS reference
                        FROM grn_items gri
                        JOIN goods_receipt_notes grn ON gri.grn_id = grn.id
                        WHERE gri.item_id = :item_id
                    ";
                    $this->db->query($grnSql);
                    $this->db->bind(':item_id', $itemId);
                    $grnItems = $this->db->resultSet();
                    if ($grnItems) {
                        $timeline = array_merge($timeline, $grnItems);
                    }
                }
            } catch (Exception $e) {
                // Skip GRN history if table doesn't exist
            }

            try {
                // Check if invoice tables exist before querying
                $this->db->query("SHOW TABLES LIKE 'invoice_items'");
                if ($this->db->single()) {
                    $invoiceSql = "
                        SELECT
                            i.created_at AS date_occurred,
                            'Invoice Sold' AS event_type,
                            ii.quantity AS quantity_changed,
                            ii.cost_at_sale AS cost_price,
                            ii.unit_price AS selling_price,
                            NULL AS wholesale_price,
                            CONCAT('Invoice #', i.invoice_number) AS reference
                        FROM invoice_items ii
                        JOIN invoices i ON ii.invoice_id = i.id
                        WHERE ii.item_id = :item_id AND i.is_deleted = 0
                    ";
                    $this->db->query($invoiceSql);
                    $this->db->bind(':item_id', $itemId);
                    $invoiceItems = $this->db->resultSet();
                    if ($invoiceItems) {
                        $timeline = array_merge($timeline, $invoiceItems);
                    }
                }
            } catch (Exception $e) {
                // Skip invoice history if table doesn't exist
            }

            // Sort timeline by date
            usort($timeline, function($a, $b) {
                return strtotime($b->date_occurred ?? '0') - strtotime($a->date_occurred ?? '0');
            });

            echo json_encode([
                'success' => true,
                'current' => $currentMeta,
                'timeline' => $timeline
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }

        restore_error_handler();
        exit;
    }

    public function adjustStock($id, $newQty) {
        $item = $this->itemModel->getItemById($id);
        if ($item) {
            $oldQty = intval($item->qty);
            $newQtyInt = intval($newQty);
            $diff = $newQtyInt - $oldQty;
            $res = $this->itemModel->updateStockOnly($id, $newQtyInt);
            if ($res) {
                $this->logActivity(
                    'Stock Adjustment', 
                    'Inventory', 
                    "Stock adjusted for product '{$item->name}' (Code: {$item->item_code}) from {$oldQty} to {$newQtyInt} (Delta: " . ($diff >= 0 ? '+' : '') . "{$diff}).", 
                    $id, 
                    ['qty' => $oldQty], 
                    ['qty' => $newQtyInt]
                );
            }
            return $res;
        }
        return false;
    }

    /**
     * Helper to synchronize the primary item image to the item_images table.
     * Keeps the ERP web items table in sync with the mobile rep app item_images table!
     */
    private function syncItemImagesTable($itemId, $imagePath) {
        if (empty($itemId) || empty($imagePath)) return;

        try {
            // Check if item_images table exists
            $this->db->query("SHOW TABLES LIKE 'item_images'");
            if (!$this->db->single()) {
                return; // Table doesn't exist, skip silently
            }

            // Check if there is already a primary image for this item
            $this->db->query("SELECT id FROM item_images WHERE item_id = :item_id AND variation_value_id IS NULL LIMIT 1");
            $this->db->bind(':item_id', $itemId);
            $existing = $this->db->single();

            if ($existing) {
                // Update existing record
                $this->db->query("UPDATE item_images SET image_path = :image_path, is_primary = 1 WHERE id = :id");
                $this->db->bind(':image_path', $imagePath);
                $this->db->bind(':id', $existing->id);
                $this->db->execute();
            } else {
                // Insert new record
                $this->db->query("INSERT INTO item_images (item_id, image_path, is_primary, variation_value_id) VALUES (:item_id, :image_path, 1, NULL)");
                $this->db->bind(':item_id', $itemId);
                $this->db->bind(':image_path', $imagePath);
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Silence exceptions to keep the main transaction alive
        }
    }

    /**
     * Optional helper to compress standard file uploads in PHP using GD library without reducing quality
     */
    private function compressImagePHP($filePath, $ext) {
        try {
            if (!extension_loaded('gd')) return;

            if ($ext === 'jpg' || $ext === 'jpeg') {
                $image = @imagecreatefromjpeg($filePath);
                if ($image) {
                    @imagejpeg($image, $filePath, 85); // Compress visually lossless
                    @imagedestroy($image);
                }
            } elseif ($ext === 'png') {
                $image = @imagecreatefrompng($filePath);
                if ($image) {
                    @imagepng($image, $filePath, 6); // Optimize size
                    @imagedestroy($image);
                }
            } elseif ($ext === 'webp') {
                $image = @imagecreatefromwebp($filePath);
                if ($image) {
                    @imagewebp($image, $filePath, 85);
                    @imagedestroy($image);
                }
            }
        } catch (Exception $e) {
            // Silence compression errors
        }
    }
}

/**
 * Truncates long names nicely
 */
if (!function_exists('truncateString')) {
    function truncateString($string, $length) {
        if (strlen($string) > $length) {
            return substr($string, 0, $length - 3) . '...';
        }
        return $string;
    }
}