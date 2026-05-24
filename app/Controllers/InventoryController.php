<?php

class InventoryController extends Controller {
    private $itemModel;
    private $wooService;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        $this->itemModel = $this->model('Item');
        $this->db = new Database(); // Added to check for database relations
        
        require_once '../app/Services/WooCommerceService.php';
        $this->wooService = new WooCommerceService();
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
                    
                    // Parse image links
                    $imagePath = '';
                    if (!empty($imagesIdx) && !empty($row[$imagesIdx])) {
                        $imgUrls = explode(',', $row[$imagesIdx]);
                        $imagePath = trim($imgUrls[0]); // store remote CDN URL directly (Bypasses network delay)
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
                        if ($this->itemModel->updateItem($data)) {
                            $updatedCount++;
                        }
                    } else {
                        if ($this->itemModel->addItem($data)) {
                            $importedCount++;
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
     * Add new inventory item with live database category, supplier, and warehouse listings
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Compress & save locally uploaded photo
            $imagePath = '';
            if (!empty($_POST['compressed_image_base64'])) {
                $base64 = $_POST['compressed_image_base64'];
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
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (file_put_contents($uploadDir . '/' . $fileName, $binary)) {
                        $imagePath = 'uploads/products/' . $fileName;
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
                'variations_json' => trim($_POST['variations_json'] ?? '[]'),
                'image_path' => $imagePath
            ];

            if ($this->itemModel->addItem($data)) {
                $newItem = $this->itemModel->getItemByCode($data['item_code']);
                if ($newItem && $data['sync_woo'] === 1) {
                    $this->wooService->syncItem($newItem, $_POST['compressed_image_base64'] ?? null);
                }
                
                header('Location: ' . APP_URL . '/inventory');
                exit;
            } else {
                die('Something went wrong saving the item to the ERP DB.');
            }
        } else {
            // Dynamic Selections
            $categories = $this->wooService->getCategories();
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
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $existingItem = $this->itemModel->getItemById($id);
            $imagePath = $existingItem->image_path ?? '';

            // Update compressed photo if modified
            if (!empty($_POST['compressed_image_base64'])) {
                $base64 = $_POST['compressed_image_base64'];
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
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    if (file_put_contents($uploadDir . '/' . $fileName, $binary)) {
                        $imagePath = 'uploads/products/' . $fileName;
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
                'variations_json' => trim($_POST['variations_json'] ?? '[]'),
                'image_path' => $imagePath
            ];

            if ($this->itemModel->updateItem($data)) {
                $updatedItem = $this->itemModel->getItemById($id);
                if ($updatedItem && $data['sync_woo'] === 1) {
                    $this->wooService->syncItem($updatedItem, $_POST['compressed_image_base64'] ?? null);
                }

                header('Location: ' . APP_URL . '/inventory');
                exit;
            } else {
                die('Something went wrong updating the item.');
            }
        } else {
            $item = $this->itemModel->getItemById($id);
            
            // Dynamic Selections
            $categories = $this->wooService->getCategories();
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
     * Sync Stock specifically when changes are made
     */
    public function adjustStock($id, $newQty) {
        if ($this->itemModel->updateStockOnly($id, $newQty)) {
            $item = $this->itemModel->getItemById($id);
            if ($item) {
                $sku = !empty($item->item_code) ? $item->item_code : ($item->sku ?? '');
                $this->wooService->updateStock($sku, $newQty);
            }
            return true;
        }
        return false;
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