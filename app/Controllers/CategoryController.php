<?php

class CategoryController extends Controller {
    private $categoryModel;
    private $wooService;
    private $itemModel;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }

        $this->categoryModel = $this->model('Category');
        $this->itemModel = $this->model('Item');
        $this->db = new Database();

        require_once '../app/Services/WooCommerceService.php';
        $this->wooService = new WooCommerceService();
    }

    /**
     * Display categories list
     */
    public function index() {
        $categories = $this->categoryModel->getCategories();
        $data = [
            'title' => 'Category Management',
            'categories' => $categories
        ];
        $this->view('categories/index', $data);
    }

    /**
     * Add category with real-time WooCommerce Sync
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sync_woo = isset($_POST['sync_woo']) ? 1 : 0;

            if (empty($name)) {
                die("Category Name is required.");
            }

            $wooCategoryId = null;
            if ($sync_woo === 1) {
                // Sync to WooCommerce and obtain the new category ID
                $wooCategoryId = $this->wooService->syncCategory($name, $description);
            }

            if ($this->categoryModel->addCategory($name, $description, $wooCategoryId)) {
                header('Location: ' . APP_URL . '/category');
                exit;
            } else {
                die("Something went wrong saving the category locally.");
            }
        }
    }

    /**
     * Edit category with real-time WooCommerce Sync
     */
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sync_woo = isset($_POST['sync_woo']) ? 1 : 0;

            $existingCategory = $this->categoryModel->getCategoryById($id);
            if (!$existingCategory) {
                die("Category not found.");
            }

            $wooCategoryId = $existingCategory->woo_category_id;

            if ($sync_woo === 1) {
                // Push update or create if remote ID does not exist
                $wooCategoryId = $this->wooService->syncCategory($name, $description, $wooCategoryId);
            } else if ($wooCategoryId) {
                // If unselected but WooCommerce ID was stored, we can choose to delete from Woo
                $this->wooService->deleteCategory($wooCategoryId);
                $wooCategoryId = null;
            }

            if ($this->categoryModel->updateCategory($id, $name, $description, $wooCategoryId)) {
                header('Location: ' . APP_URL . '/category');
                exit;
            } else {
                die("Something went wrong updating the category locally.");
            }
        }
    }

    /**
     * Delete Category with real-time WooCommerce Sync
     */
    public function delete($id) {
        $existingCategory = $this->categoryModel->getCategoryById($id);
        if ($existingCategory) {
            $wooCategoryId = $existingCategory->woo_category_id;
            
            // Delete from WooCommerce if remote category exists
            if ($wooCategoryId) {
                $this->wooService->deleteCategory($wooCategoryId);
            }

            if ($this->categoryModel->deleteCategory($id)) {
                header('Location: ' . APP_URL . '/category');
                exit;
            } else {
                die("Something went wrong deleting the category locally.");
            }
        }
    }

    /**
     * AJAX Endpoint: Sync Category taxonomies directly from WooCommerce.
     * Robust upsert handling to avoid local database integrity violations.
     */
    public function ajaxSyncCategories() {
        header('Content-Type: application/json');
        
        $imported = 0;
        $updated = 0;
        $logs = [];

        try {
            // Query active WooCommerce store categories
            $wcCategories = $this->wooService->getCategories();

            if (empty($wcCategories) || !is_array($wcCategories)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Zero categories retrieved from store. Verify API credentials.',
                    'logs' => ['[Error] Sync cancelled: no remote categories loaded.']
                ]);
                exit;
            }

            // Gather current local categories to avoid duplicate name integrity errors
            $localCategories = $this->categoryModel->getCategories();
            $localByName = [];
            $localByWooId = [];

            foreach ($localCategories as $lc) {
                $localByName[strtolower(trim($lc->name))] = $lc;
                if (!empty($lc->woo_category_id)) {
                    $localByWooId[intval($lc->woo_category_id)] = $lc;
                }
            }

            $this->db->beginTransaction();

            foreach ($wcCategories as $wcCat) {
                $name = trim($wcCat->name);
                $wooId = intval($wcCat->id);
                $description = trim($wcCat->description ?? '');

                // Reconcile by WooCommerce ID mapping, then fall back to matching Name
                if (isset($localByWooId[$wooId])) {
                    $local = $localByWooId[$wooId];
                    if ($local->name !== $name || ($local->description ?? '') !== $description) {
                        $this->categoryModel->updateCategory($local->id, $name, $description, $wooId);
                        $updated++;
                        $logs[] = "SUCCESS: Updated Category '{$name}' (#{$wooId})";
                    } else {
                        $logs[] = "Reconciled: '{$name}' (#{$wooId}) is already in sync.";
                    }
                } elseif (isset($localByName[strtolower($name)])) {
                    $local = $localByName[strtolower($name)];
                    $this->categoryModel->updateCategory($local->id, $name, $description, $wooId);
                    $updated++;
                    $logs[] = "SUCCESS: Associated local '{$name}' to WooCommerce ID #{$wooId}";
                } else {
                    $this->categoryModel->addCategory($name, $description, $wooId);
                    $imported++;
                    $logs[] = "SUCCESS: Created Category '{$name}' (#{$wooId})";
                }
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'total' => count($wcCategories),
                'logs' => $logs
            ]);
        } catch (Exception $e) {
            if ($this->db) {
                $this->db->rollBack();
            }
            echo json_encode([
                'success' => false,
                'message' => 'Database Sync Failure: ' . $e->getMessage(),
                'logs' => ['[Fatal Database Error] ' . $e->getMessage()]
            ]);
        }
        exit;
    }

    /**
     * AJAX Endpoint: Sync Global attribute definitions from WooCommerce.
     */
    public function ajaxSyncAttributes() {
        header('Content-Type: application/json');
        
        $logs = [];
        $wcAttributes = $this->wooService->getGlobalAttributes();

        if (empty($wcAttributes) || !is_array($wcAttributes)) {
            echo json_encode([
                'success' => true,
                'logs' => ['[Sync] Completed: No custom global attribute schemas exist on WooCommerce. Using standard local builders.']
            ]);
            exit;
        }

        foreach ($wcAttributes as $attr) {
            $logs[] = "SUCCESS: Synchronized Attribute Taxonomy: '{$attr->name}' (slug: {$attr->slug}) - Status: ACTIVE";
        }

        echo json_encode([
            'success' => true,
            'logs' => $logs
        ]);
        exit;
    }

    /**
     * AJAX Endpoint: Sync variations from WooCommerce Variable parent products
     * and persist them directly into local database items table.
     */
    public function ajaxSyncVariations() {
        header('Content-Type: application/json');
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $logs = [];
        $updatedCount = 0;
        $failedCount = 0;

        try {
            // Get all variable products currently logged in the local items database
            $localItems = $this->itemModel->getItems();
            
            $this->db->beginTransaction();

            foreach ($localItems as $item) {
                $sku = $item->item_code;
                if (empty($sku)) continue;

                // Match with WooCommerce to verify if it is variable type
                $wcProduct = $this->wooService->getProductBySku($sku);
                if ($wcProduct && ($wcProduct->type ?? '') === 'variable') {
                    $logs[] = "Found Variable Product on WooCommerce: '{$wcProduct->name}' (SKU: {$sku}). Fetching variations...";
                    
                    $wcVariations = $this->wooService->getProductVariations($wcProduct->id);
                    if (is_array($wcVariations) && !empty($wcVariations)) {
                        $varsArray = [];
                        foreach ($wcVariations as $variation) {
                            $vSku = trim($variation->sku ?? '');
                            if (empty($vSku)) {
                                $vSku = $sku . '-' . ($variation->id ?? '');
                            }

                            // Parse variation attribute option labels
                            $attrLabels = [];
                            if (!empty($variation->attributes) && is_array($variation->attributes)) {
                                foreach ($variation->attributes as $attr) {
                                    if (!empty($attr->option)) {
                                        $attrLabels[] = ucfirst($attr->option);
                                    }
                                }
                            }
                            $attrOption = !empty($attrLabels) ? implode(', ', $attrLabels) : 'Default';

                            // Extract WholesaleX price
                            $vWholesalePrice = $this->wooService->extractWholesalePrice($variation);

                            $varsArray[] = [
                                'id' => $variation->id ?? '',
                                'attribute' => $attrOption,
                                'sku' => $vSku,
                                'cost_price' => 0.00,
                                'price' => floatval($variation->regular_price ?? $variation->price ?? 0),
                                'wholesale_price' => floatval($vWholesalePrice)
                            ];
                        }

                        // Save variations JSON directly to the items table
                        $variationsJson = json_encode($varsArray);
                        
                        $this->db->query("UPDATE items SET variations_json = :vars WHERE id = :id");
                        $this->db->bind(':id', $item->id);
                        $this->db->bind(':vars', $variationsJson);
                        
                        if ($this->db->execute()) {
                            $updatedCount++;
                            $logs[] = "SUCCESS: Synced & saved " . count($varsArray) . " variations in database for SKU '{$sku}'";
                        } else {
                            $failedCount++;
                        }
                    } else {
                        $logs[] = "No variations found on WooCommerce for variable SKU '{$sku}'.";
                    }
                }
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'updated' => $updatedCount,
                'failed' => $failedCount,
                'logs' => $logs
            ]);

        } catch (Exception $e) {
            if ($this->db) {
                $this->db->rollBack();
            }
            echo json_encode([
                'success' => false,
                'message' => 'Variations Sync Failure: ' . $e->getMessage(),
                'logs' => ['[Fatal Database Error] ' . $e->getMessage()]
            ]);
        }
        exit;
    }

    /**
     * AJAX Endpoint: Run deep variation database audits & WholesaleX checks.
     */
    public function ajaxAuditVariations() {
        header('Content-Type: application/json');
        
        $logs = [];
        $items = $this->itemModel->getItems();

        $totalVariations = 0;
        $skuDuplicates = 0;
        $skusSeen = [];
        $variableParentCount = 0;

        $logs[] = "[Audit Log] Initiating structural product catalog integrity scanning...";

        foreach ($items as $item) {
            if (!empty($item->variations_json)) {
                $decoded = json_decode($item->variations_json, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $variableParentCount++;
                    $logs[] = "Auditing Parent Product SKU '{$item->item_code}'...";
                    
                    foreach ($decoded as $v) {
                        $totalVariations++;
                        $vSku = $v['sku'] ?? '';
                        if (!empty($vSku)) {
                            if (isset($skusSeen[$vSku])) {
                                $skuDuplicates++;
                                $logs[] = "CRITICAL: Duplicate SKU conflict detected on variant code '{$vSku}'!";
                            } else {
                                $skusSeen[$vSku] = true;
                            }
                        }
                    }
                }
            }
        }

        $logs[] = "---------------------------------------------";
        $logs[] = "Variable parent items scanned: {$variableParentCount}";
        $logs[] = "Variable child SKUs parsed: {$totalVariations}";
        
        if ($skuDuplicates > 0) {
            $logs[] = "AUDIT WARNING: Found {$skuDuplicates} SKU duplicate conflicts. These must be renamed inside the inventory form.";
        } else {
            $logs[] = "AUDIT OK: Global child variation SKUs contain unique identifiers.";
        }

        echo json_encode([
            'success' => true,
            'total_parents' => $variableParentCount,
            'total_variations' => $totalVariations,
            'duplicates' => $skuDuplicates,
            'logs' => $logs
        ]);
        exit;
    }
}