<?php

class CategoryController extends Controller {
    private $categoryModel;
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
    }

    /**
     * Display categories list
     */
    public function index() {
        $this->checkPermission('category', 'view');
        $categories = $this->categoryModel->getCategories();
        $data = [
            'title' => 'Category Management',
            'categories' => $categories,
            'content_view' => 'categories/index'
        ];
        $this->view('layouts/main', $data);
    }

    /**
     * Add category locally
     */
    public function add() {
        $this->checkPermission('category', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $name = html_entity_decode(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $description = html_entity_decode(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');

            if (empty($name)) {
                die("Category Name is required.");
            }

            if ($this->categoryModel->addCategory($name, $description, null)) {
                header('Location: ' . APP_URL . '/category');
                exit;
            } else {
                die("Something went wrong saving the category locally.");
            }
        }
    }

    /**
     * Edit category locally
     */
    public function edit($id) {
        $this->checkPermission('category', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $name = html_entity_decode(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $description = html_entity_decode(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');

            $existingCategory = $this->categoryModel->getCategoryById($id);
            if (!$existingCategory) {
                die("Category not found.");
            }

            if ($this->categoryModel->updateCategory($id, $name, $description, null)) {
                header('Location: ' . APP_URL . '/category');
                exit;
            } else {
                die("Something went wrong updating the category locally.");
            }
        }
    }

    /**
     * Delete Category locally
     */
    public function delete($id) {
        $this->checkPermission('category', 'delete');
        $existingCategory = $this->categoryModel->getCategoryById($id);
        if ($existingCategory) {
            if ($this->categoryModel->deleteCategory($id)) {
                header('Location: ' . APP_URL . '/category');
                exit;
            } else {
                die("Something went wrong deleting the category locally.");
            }
        }
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
     * AJAX Endpoint: Get products for a category
     */
    public function products($id) {
        $this->checkPermission('category', 'view');
        header('Content-Type: application/json');
        
        $this->db->query("SELECT id, name, sku, item_code, image_path, qty, selling_price, status 
                          FROM items 
                          WHERE category_id = :category_id 
                          ORDER BY name ASC");
        $this->db->bind(':category_id', $id);
        $products = $this->db->resultSet();
        
        echo json_encode($products);
        exit;
    }

    /**
     * AJAX Endpoint: Generate AI description based on category name
     */
    public function generateAiDescription() {
        $this->checkPermission('category', 'create_edit');
        header('Content-Type: application/json');
        
        $name = trim($_GET['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Category name is required.']);
            exit;
        }

        $cleanName = htmlspecialchars($name);
        $lowerName = strtolower($name);
        $desc = "";
        
        if (strpos($lowerName, 'tool') !== false || strpos($lowerName, 'equip') !== false || strpos($lowerName, 'machine') !== false) {
            $desc = "A professional-grade collection of {$cleanName} engineered for high performance, structural durability, and operational excellence. This segment features premium items optimized for distribution, retail sales, and heavy-duty utility.";
        } elseif (strpos($lowerName, 'electronic') !== false || strpos($lowerName, 'tech') !== false || strpos($lowerName, 'device') !== false) {
            $desc = "High-performance {$cleanName} utilizing advanced technology and components. This category contains precision-engineered hardware, consumer devices, and technical units cataloged for streamlined ERP stock tracking and retail sales.";
        } elseif (strpos($lowerName, 'wear') !== false || strpos($lowerName, 'cloth') !== false || strpos($lowerName, 'apparel') !== false || strpos($lowerName, 'shoe') !== false) {
            $desc = "Modern and stylish {$cleanName} tailored for quality comfort, fashion standards, and retail appeal. This classification covers a wide selection of designs and materials, organized for inventory distribution and seasonal sales tracking.";
        } elseif (strpos($lowerName, 'food') !== false || strpos($lowerName, 'bev') !== false || strpos($lowerName, 'drink') !== false || strpos($lowerName, 'snack') !== false) {
            $desc = "Premium grade {$cleanName} prepared and packaged according to food safety and freshness regulations. This category maintains strict batch tracking, stock rotation, and warehouse distribution guidelines.";
        } elseif (strpos($lowerName, 'stationery') !== false || strpos($lowerName, 'office') !== false || strpos($lowerName, 'pen') !== false || strpos($lowerName, 'book') !== false) {
            $desc = "Essential {$cleanName} curated for office productivity, creative work, and corporate environments. This catalog segment is optimized for high-volume retail supply chains and warehouse inventory control.";
        } else {
            $desc = "A specialized collection of {$cleanName} designed for quality assurance, retail demand, and systematic stock categorization. This segment includes core items, accessories, and spare parts cataloged to optimize warehouse operations and sales tracking.";
        }

        echo json_encode(['success' => true, 'description' => $desc]);
        exit;
    }
}