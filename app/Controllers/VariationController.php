<?php

class VariationController extends Controller {
    private $itemModel;
    private $wooService;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }

        $this->itemModel = $this->model('Item');
        $this->db = new Database();

        $this->ensureTaxonomyDatabaseTables();

        require_once '../app/Services/WooCommerceService.php';
        $this->wooService = new WooCommerceService();
    }

    /**
     * Dynamic self-healing database migration.
     * Sets up local tables for variable product attributes and terms.
     */
    private function ensureTaxonomyDatabaseTables() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS product_attributes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                woo_attr_id INT UNIQUE NULL
            )");
            $this->db->execute();

            $this->db->query("CREATE TABLE IF NOT EXISTS product_attribute_terms (
                id INT AUTO_INCREMENT PRIMARY KEY,
                attribute_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL,
                woo_term_id INT UNIQUE NULL,
                FOREIGN KEY (attribute_id) REFERENCES product_attributes(id) ON DELETE CASCADE
            )");
            $this->db->execute();
        } catch (Exception $e) {
            // Safe fallback
        }
    }

    /**
     * Render the dedicated Variations Management Page.
     * Extracts individual variations from parent products and flattens them for easy viewing and search.
     */
    public function index() {
        // Query variable products from database
        $this->db->query("SELECT * FROM items WHERE variations_json IS NOT NULL AND variations_json != '[]' AND variations_json != ''");
        $variableItems = $this->db->resultSet() ?: [];

        // Flatten variations for display and easy filtering
        $flatVariations = [];
        foreach ($variableItems as $parent) {
            $decoded = json_decode($parent->variations_json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $v) {
                    $flatVariations[] = (object)[
                        'parent_id' => $parent->id,
                        'parent_name' => $parent->name,
                        'parent_sku' => $parent->item_code,
                        'parent_image' => $parent->image_path,
                        'parent_sync_woo' => $parent->sync_woo,
                        'id' => $v['id'] ?? '',
                        'sku' => $v['sku'] ?? '',
                        'attribute' => $v['attribute'] ?? '',
                        'cost_price' => floatval($v['cost_price'] ?? 0),
                        'price' => floatval($v['price'] ?? 0),
                        'wholesale_price' => floatval($v['wholesale_price'] ?? 0),
                    ];
                }
            }
        }

        // Apply interactive real-time search filtering
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        if (!empty($search)) {
            $flatVariations = array_filter($flatVariations, function($v) use ($search) {
                return (
                    stripos($v->sku, $search) !== false ||
                    stripos($v->parent_sku, $search) !== false ||
                    stripos($v->parent_name, $search) !== false ||
                    stripos($v->attribute, $search) !== false
                );
            });
        }

        // Fetch local synced WooCommerce Attributes and their Terms
        $this->db->query("SELECT * FROM product_attributes ORDER BY name ASC");
        $attributesList = $this->db->resultSet() ?: [];

        foreach ($attributesList as $attr) {
            $this->db->query("SELECT * FROM product_attribute_terms WHERE attribute_id = :id ORDER BY name ASC");
            $this->db->bind(':id', $attr->id);
            $attr->terms = $this->db->resultSet() ?: [];
        }

        $data = [
            'title' => 'Variation Sync Hub',
            'variations' => $flatVariations,
            'total_parents' => count($variableItems),
            'search' => $search,
            'synced_attributes' => $attributesList
        ];
        
        $this->view('variations/index', $data);
    }

    /**
     * AJAX Endpoint: Sync variations from WooCommerce Variable parent products
     * and persist them directly into local database items table variations_json column.
     */
    public function ajaxSyncVariations() {
        header('Content-Type: application/json');
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M'); // High limit for heavy recursive variations loops

        $logs = [];
        $updatedCount = 0;
        $failedCount = 0;

        try {
            // Get all products currently logged in the local items database
            $localItems = $this->itemModel->getItems();
            
            $this->db->beginTransaction();

            foreach ($localItems as $item) {
                $sku = $item->item_code;
                if (empty($sku)) continue;

                // Match with WooCommerce to verify if it is a variable product type
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
                'message' => 'Variations Database Sync Failure: ' . $e->getMessage(),
                'logs' => ['[Fatal Database Error] ' . $e->getMessage()]
            ]);
        }
        exit;
    }

    /**
     * AJAX Endpoint: Pull and Sync Global attribute definitions and Terms
     * dynamically from WooCommerce. Saves them directly to the ERP tables.
     */
    public function ajaxSyncAttributes() {
        header('Content-Type: application/json');
        @set_time_limit(0);
        
        $logs = [];
        $importedAttributes = 0;
        $importedTerms = 0;

        try {
            $this->db->beginTransaction();

            // 1. Fetch WooCommerce Global Attributes list
            $wcAttributes = $this->wooService->getGlobalAttributes();

            if (empty($wcAttributes) || !is_array($wcAttributes)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No attributes found on WooCommerce. Sync canceled.',
                    'logs' => ['[Sync] Attribute taxonomies pull completed. WooCommerce returned empty list.']
                ]);
                exit;
            }

            foreach ($wcAttributes as $attr) {
                $attrName = trim($attr->name);
                $attrSlug = trim($attr->slug);
                $wooAttrId = intval($attr->id);

                // Check if attribute already exists locally
                $this->db->query("SELECT id FROM product_attributes WHERE woo_attr_id = :woo_id OR slug = :slug");
                $this->db->bind(':woo_id', $wooAttrId);
                $this->db->bind(':slug', $attrSlug);
                $localAttr = $this->db->single();

                if ($localAttr) {
                    $attrId = $localAttr->id;
                    $this->db->query("UPDATE product_attributes SET name = :name, slug = :slug, woo_attr_id = :woo_id WHERE id = :id");
                    $this->db->bind(':name', $attrName);
                    $this->db->bind(':slug', $attrSlug);
                    $this->db->bind(':woo_id', $wooAttrId);
                    $this->db->bind(':id', $attrId);
                    $this->db->execute();
                    $logs[] = "Updated attribute schema: '{$attrName}' (#{$wooAttrId})";
                } else {
                    $this->db->query("INSERT INTO product_attributes (name, slug, woo_attr_id) VALUES (:name, :slug, :woo_id)");
                    $this->db->bind(':name', $attrName);
                    $this->db->bind(':slug', $attrSlug);
                    $this->db->bind(':woo_id', $wooAttrId);
                    $this->db->execute();
                    $attrId = $this->db->lastInsertId();
                    $importedAttributes++;
                    $logs[] = "SUCCESS: Created global attribute: '{$attrName}' (#{$wooAttrId})";
                }

                // 2. Query terms for each attribute (A3, A4, Red, Blue, Pack of 12)
                $wcTerms = $this->wooService->getAttributeTerms($wooAttrId);
                if (is_array($wcTerms)) {
                    foreach ($wcTerms as $term) {
                        $termName = trim($term->name);
                        $termSlug = trim($term->slug);
                        $wooTermId = intval($term->id);

                        // Check if term already exists locally for this attribute
                        $this->db->query("SELECT id FROM product_attribute_terms WHERE woo_term_id = :term_id OR (attribute_id = :attr_id AND slug = :slug)");
                        $this->db->bind(':term_id', $wooTermId);
                        $this->db->bind(':attr_id', $attrId);
                        $this->db->bind(':slug', $termSlug);
                        $localTerm = $this->db->single();

                        if (!$localTerm) {
                            $this->db->query("INSERT INTO product_attribute_terms (attribute_id, name, slug, woo_term_id) VALUES (:attr_id, :name, :slug, :term_id)");
                            $this->db->bind(':attr_id', $attrId);
                            $this->db->bind(':name', $termName);
                            $this->db->bind(':slug', $termSlug);
                            $this->db->bind(':term_id', $wooTermId);
                            $this->db->execute();
                            $importedTerms++;
                        }
                    }
                    $logs[] = "Synchronized " . count($wcTerms) . " terms for attribute '{$attrName}'";
                }
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'imported_attributes' => $importedAttributes,
                'imported_terms' => $importedTerms,
                'logs' => $logs
            ]);
        } catch (Exception $e) {
            if ($this->db) {
                $this->db->rollBack();
            }
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'logs' => ['[Error] Attribute pull failed: ' . $e->getMessage()]
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

    /**
     * AJAX Endpoint: Individual Variable Parent Sync Push/Pull Broker
     */
    public function ajaxItemSync() {
        header('Content-Type: application/json');

        $sku = isset($_GET['sku']) ? trim($_GET['sku']) : '';
        $direction = isset($_GET['direction']) ? trim($_GET['direction']) : 'pull';
        
        if (empty($sku)) {
            echo json_encode([
                'success' => false,
                'message' => 'SKU is a required parameter for syncing variable products.'
            ]);
            exit;
        }

        $item = $this->itemModel->getItemByCode($sku);
        if (!$item) {
            echo json_encode([
                'success' => false,
                'message' => "Product SKU '{$sku}' not found in the local ERP database."
            ]);
            exit;
        }

        $logs = [];

        try {
            if ($direction === 'pull') {
                // Pull variations from WooCommerce to Local DB
                $wcProduct = $this->wooService->getProductBySku($sku);
                if ($wcProduct && ($wcProduct->type ?? '') === 'variable') {
                    $wcVariations = $this->wooService->getProductVariations($wcProduct->id);
                    $varsArray = [];
                    
                    if (is_array($wcVariations) && !empty($wcVariations)) {
                        foreach ($wcVariations as $variation) {
                            $vSku = trim($variation->sku ?? '');
                            if (empty($vSku)) {
                                $vSku = $sku . '-' . ($variation->id ?? '');
                            }

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

                        $variationsJson = json_encode($varsArray);
                        
                        $this->db->query("UPDATE items SET variations_json = :vars WHERE id = :id");
                        $this->db->bind(':id', $item->id);
                        $this->db->bind(':vars', $variationsJson);
                        $this->db->execute();

                        $logs[] = "SUCCESS: Pulled " . count($varsArray) . " child variations from WooCommerce into ERP for Parent SKU '{$sku}'";
                    } else {
                        $logs[] = "No variations exist on WooCommerce for Parent ID #{$wcProduct->id}";
                    }
                } else {
                    $logs[] = "Product on WooCommerce is not a Variable product type.";
                }
            } else {
                // Push local variations from ERP to WooCommerce
                $productId = $this->wooService->syncItem($item);
                if ($productId) {
                    $logs[] = "SUCCESS: Synchronized variable Parent product and variable attributes to WooCommerce Product ID #{$productId}";
                } else {
                    throw new Exception("WooCommerce REST API Parent synchronization rejected the request.");
                }
            }

            echo json_encode([
                'success' => true,
                'logs' => $logs
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}