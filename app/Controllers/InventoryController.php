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
            'stock_status' => isset($_GET['stock_status']) ? trim($_GET['stock_status']) : '',
            'category_id' => isset($_GET['category_id']) && $_GET['category_id'] !== '' ? intval($_GET['category_id']) : ''
        ];

        $totalItems = $this->itemModel->countItems($filters);
        $totalPages = max(1, ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $items = $this->itemModel->getItemsPaged($perPage, $offset, $filters);
        $stats = $this->itemModel->getStockStats();
        $categories = $this->categoryModel->getCategories();

        $data = [
            'items' => $items,
            'stats' => $stats,
            'filters' => $filters,
            'categories' => $categories,
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
        // Automatically regenerate sample codes before exporting to ensure they are up to date
        $this->itemModel->regenerateSampleCodes();

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

            // Helper to find column index matching multiple possible candidate aliases
            $findHeaderIdx = function($candidates) use ($headers) {
                foreach ($candidates as $candidate) {
                    $idx = array_search(strtolower(trim($candidate)), $headers);
                    if ($idx !== FALSE) {
                        return $idx;
                    }
                }
                return FALSE;
            };

            // Locate columns using candidate aliases
            $skuIdx = $findHeaderIdx(['sku', 'item_code', 'item code', 'code', 'product_code', 'product code']);
            $nameIdx = $findHeaderIdx(['name', 'title', 'product_name', 'product name', 'item_name', 'item name']);
            $sellingPriceIdx = $findHeaderIdx(['selling price', 'selling_price', 'price', 'unit_price', 'unit price', 'rate']);
            $wholesalePriceIdx = $findHeaderIdx(['wholesale price', 'wholesale_price', 'wholesale', 'b2b_price', 'b2b price', 'trade_price', 'trade price']);
            $costPriceIdx = $findHeaderIdx(['cost price', 'cost_price', 'cost', 'purchase_price', 'purchase price', 'buy_price', 'buy price']);
            $qtyIdx = $findHeaderIdx(['quantity', 'qty', 'stock', 'stock_qty', 'stock qty', 'stock_quantity', 'stock quantity']);
            $descIdx = $findHeaderIdx(['description', 'desc', 'product_description', 'product description', 'details']);
            $barcodeIdx = $findHeaderIdx(['barcode', 'bar_code', 'upc', 'ean']);
            $categoryIdx = $findHeaderIdx(['category', 'category_name', 'category name', 'cat']);
            $brandIdx = $findHeaderIdx(['brand', 'brand_name', 'brand name', 'manufacturer']);
            $warehouseIdx = $findHeaderIdx(['warehouse', 'warehouse_name', 'warehouse name', 'location']);
            $vendorIdx = $findHeaderIdx(['vendor', 'vendor_name', 'vendor name', 'supplier', 'supplier_name', 'supplier name']);
            $alertQtyIdx = $findHeaderIdx(['alert qty', 'alert_qty', 'alert_quantity', 'alert quantity', 'min_stock', 'min stock']);
            $unitIdx = $findHeaderIdx(['unit', 'uom', 'measurement']);
            $statusIdx = $findHeaderIdx(['status', 'item_status', 'state']);
            $weightIdx = $findHeaderIdx(['weight', 'item_weight', 'mass']);
            $retailMarginIdx = $findHeaderIdx(['retail margin', 'retail_margin', 'retail_markup', 'retail markup']);
            $wholesaleMarginIdx = $findHeaderIdx(['wholesale margin', 'wholesale_margin', 'wholesale_markup', 'wholesale markup']);
            $sampleCodeIdx = $findHeaderIdx(['sample code', 'sample_code', 'sample_no', 'sample no']);
            $variationsIdx = $findHeaderIdx(['variations', 'variations_json', 'variation', 'options']);

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

                    $sku = ($skuIdx !== FALSE && isset($row[$skuIdx])) ? trim($row[$skuIdx]) : '';
                    $name = ($nameIdx !== FALSE && isset($row[$nameIdx])) ? trim($row[$nameIdx]) : '';

                    if (empty($sku)) {
                        $errors[] = "Row {$rowCount}: SKU is missing or empty. Skipped.";
                        continue;
                    }
                    if (empty($name)) {
                        $errors[] = "Row {$rowCount}: Name is missing or empty for SKU '{$sku}'. Skipped.";
                        continue;
                    }

                    // Parse numeric inputs safely, removing thousand-separator commas
                    $sellingPrice = ($sellingPriceIdx !== FALSE && isset($row[$sellingPriceIdx])) ? floatval(str_replace(',', '', trim($row[$sellingPriceIdx]))) : 0.00;
                    $wholesalePrice = ($wholesalePriceIdx !== FALSE && isset($row[$wholesalePriceIdx])) ? floatval(str_replace(',', '', trim($row[$wholesalePriceIdx]))) : 0.00;
                    $costPrice = ($costPriceIdx !== FALSE && isset($row[$costPriceIdx])) ? floatval(str_replace(',', '', trim($row[$costPriceIdx]))) : 0.00;
                    $qty = ($qtyIdx !== FALSE && isset($row[$qtyIdx])) ? intval(str_replace(',', '', trim($row[$qtyIdx]))) : 0;
                    
                    $description = ($descIdx !== FALSE && isset($row[$descIdx])) ? trim($row[$descIdx]) : '';
                    $barcode = ($barcodeIdx !== FALSE && isset($row[$barcodeIdx])) ? trim($row[$barcodeIdx]) : '';
                    $categoryName = ($categoryIdx !== FALSE && isset($row[$categoryIdx])) ? trim($row[$categoryIdx]) : '';
                    $brand = ($brandIdx !== FALSE && isset($row[$brandIdx])) ? trim($row[$brandIdx]) : '';
                    $warehouseName = ($warehouseIdx !== FALSE && isset($row[$warehouseIdx])) ? trim($row[$warehouseIdx]) : '';
                    $vendorName = ($vendorIdx !== FALSE && isset($row[$vendorIdx])) ? trim($row[$vendorIdx]) : '';
                    
                    $alertQty = ($alertQtyIdx !== FALSE && isset($row[$alertQtyIdx])) ? intval(str_replace(',', '', trim($row[$alertQtyIdx]))) : 5;
                    $unit = ($unitIdx !== FALSE && isset($row[$unitIdx])) ? trim($row[$unitIdx]) : 'pcs';
                    
                    $status = ($statusIdx !== FALSE && isset($row[$statusIdx])) ? strtolower(trim($row[$statusIdx])) : 'active';
                    if ($status !== 'active' && $status !== 'inactive') {
                        $status = 'active';
                    }
                    
                    $weight = ($weightIdx !== FALSE && isset($row[$weightIdx])) ? trim($row[$weightIdx]) : '';
                    
                    $retailMargin = ($retailMarginIdx !== FALSE && isset($row[$retailMarginIdx])) ? floatval(str_replace(',', '', trim($row[$retailMarginIdx]))) : 0.00;
                    $wholesaleMargin = ($wholesaleMarginIdx !== FALSE && isset($row[$wholesaleMarginIdx])) ? floatval(str_replace(',', '', trim($row[$wholesaleMarginIdx]))) : 0.00;
                    $sampleCode = ($sampleCodeIdx !== FALSE && isset($row[$sampleCodeIdx])) ? trim($row[$sampleCodeIdx]) : '';
                    $variationsJson = ($variationsIdx !== FALSE && isset($row[$variationsIdx])) ? trim($row[$variationsIdx]) : '[]';

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

            // Auto-regenerate product sample codes sequentially by category name ASC and product id ASC
            $this->itemModel->regenerateSampleCodes();
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
                
                // Auto-regenerate product sample codes sequentially by category name ASC and product id ASC
                $this->itemModel->regenerateSampleCodes();

                // Check if facebook sharing was requested
                if (isset($_POST['share_facebook']) && $_POST['share_facebook'] == '1') {
                    $fbResult = $this->postProductToFacebook($newItemId, $data, $imagePath);
                    if ($fbResult) {
                        $_SESSION['flash_success'] = "Product created and successfully posted to Facebook Page!";
                    } else {
                        $_SESSION['flash_success'] = "Product created successfully, but Facebook Page auto-posting failed. Please check your credentials in Settings.";
                    }
                } else {
                    $_SESSION['flash_success'] = "Product created successfully!";
                }
                
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
            $companyModel = $this->model('Company');
            $settings = $companyModel->getSettings();

            $data = [
                'title' => 'Create New Inventory Product',
                'item' => null,
                'categories' => $categories,
                'vendors' => $vendors,
                'warehouses' => $warehouses,
                'settings' => $settings
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
                
                // Auto-regenerate product sample codes sequentially by category name ASC and product id ASC
                $this->itemModel->regenerateSampleCodes();
                
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
            $companyModel = $this->model('Company');
            $settings = $companyModel->getSettings();

            $data = [
                'title' => 'Edit Inventory Product Profile',
                'item' => $item,
                'categories' => $categories,
                'vendors' => $vendors,
                'warehouses' => $warehouses,
                'settings' => $settings
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

    /**
     * Publish promotional post to Facebook Graph API for a newly created product
     */
    private function postProductToFacebook($productId, $data, $imagePath) {
        try {
            $companyModel = $this->model('Company');
            $settings = $companyModel->getSettings();

            $pageId = $settings->facebook_page_id ?? '';
            $accessToken = $settings->facebook_access_token ?? '';

            if (empty($pageId) || empty($accessToken)) {
                return false;
            }

            // Construct Storefront Product Detail URL
            $storeUrl = rtrim($settings->ecommerce_store_url ?? '', '/');
            if (empty($storeUrl)) {
                $storeUrl = 'http://localhost/Curtiss%20E%20Commerce';
            }
            $productUrl = $storeUrl . '/index.php?p=product&id=' . $productId;

            // Message template
            $message = $data['name'] . "\n";
            $message .= "Price: Rs. " . number_format(floatval($data['selling_price']), 2);
            if (floatval($data['wholesale_price']) > 0) {
                $message .= " (Retail) / Rs. " . number_format(floatval($data['wholesale_price']), 2) . " (Wholesale)";
            }
            $message .= "\n\n";
            if (!empty($data['description'])) {
                $message .= $data['description'] . "\n\n";
            }
            $message .= "View & Order Online:\n" . $productUrl;

            $absoluteImgPath = '';
            if (!empty($imagePath)) {
                $absoluteImgPath = dirname(__DIR__, 2) . '/' . $imagePath;
            }

            // If there's a valid local image, try to post it as a photo
            if (!empty($absoluteImgPath) && file_exists($absoluteImgPath)) {
                $url = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "/photos";
                $postFields = [
                    'caption' => $message,
                    'access_token' => $accessToken,
                    'source' => new CURLFile($absoluteImgPath)
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300) {
                    return true;
                }
            }

            // Fallback to feed/link post if photo upload fails or is not available
            $url = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "/feed";
            $postFields = [
                'message' => $message,
                'link' => $productUrl,
                'access_token' => $accessToken
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ($httpCode >= 200 && $httpCode < 300);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
            exit;
        }

        header('Content-Type: application/json');
        
        $password = $_POST['password'] ?? '';
        $isAdmin = (strtolower($_SESSION['role'] ?? '') === 'admin');

        $userModel = $this->model('User');
        $authorized = false;
        $authUsername = '';

        if ($isAdmin) {
            // Verify current logged in admin password
            $currentUsername = $_SESSION['username'] ?? '';
            $user = $userModel->login($currentUsername, $password);
            if ($user && strtolower($user->role) === 'admin') {
                $authorized = true;
                $authUsername = $currentUsername;
            } else {
                echo json_encode(['success' => false, 'error' => 'Incorrect password for your admin account.']);
                exit;
            }
        } else {
            // Verify provided admin credentials
            $adminUsername = trim($_POST['admin_username'] ?? '');
            if (empty($adminUsername)) {
                echo json_encode(['success' => false, 'error' => 'Admin username is required.']);
                exit;
            }
            $user = $userModel->login($adminUsername, $password);
            if ($user && strtolower($user->role) === 'admin') {
                $authorized = true;
                $authUsername = $adminUsername;
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid admin credentials or user is not an administrator.']);
                exit;
            }
        }

        if ($authorized) {
            $item = $this->itemModel->getItemById($id);
            if (!$item) {
                echo json_encode(['success' => false, 'error' => 'Product not found.']);
                exit;
            }

            if ($this->itemModel->deleteItem($id)) {
                // Log action
                $this->logActivity(
                    'Product Deleted',
                    'Inventory',
                    "Product '{$item->name}' (Code: {$item->item_code}) deleted by administrator '{$authUsername}' (Requesting user: '{$_SESSION['username']}').",
                    $id
                );

                echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete product from database.']);
                exit;
            }
        }

        echo json_encode(['success' => false, 'error' => 'Unauthorized action.']);
        exit;
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