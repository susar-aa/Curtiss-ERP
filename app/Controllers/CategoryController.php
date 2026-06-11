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
        
        $this->db->query("SELECT * 
                          FROM items 
                          WHERE category_id = :category_id 
                          ORDER BY name ASC");
        $this->db->bind(':category_id', $id);
        $products = $this->db->resultSet();
        
        echo json_encode($products);
        exit;
    }

    public function generateAiDescription() {
        $this->checkPermission('category', 'create_edit');
        header('Content-Type: application/json');
        
        $name = trim($_GET['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Category name is required.']);
            exit;
        }

        $cleanName = htmlspecialchars($name);
        $apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';
        
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'error' => 'API Connection Error: Groq AI API Key is missing in database config file.']);
            exit;
        }

        $url = "https://api.groq.com/openai/v1/chat/completions";
        $prompt = "Generate a short, unique category description for an e-commerce website.\n\nCategory: \"{$cleanName}\"\n\nRequirements:\n* 1-2 sentences only (20-50 words).\n* Understand what products belong to this category.\n* Mention the category's purpose, common uses, or key features.\n* Avoid generic phrases and marketing fluff.\n* Do not simply repeat the category name.\n* Return only the description text.";
        
        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 100
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode === 200 && !empty($response)) {
            $resData = json_decode($response, true);
            $generatedText = $resData['choices'][0]['message']['content'] ?? '';
            $generatedText = trim($generatedText);
            $generatedText = trim($generatedText, '"\'');
            if (!empty($generatedText)) {
                echo json_encode(['success' => true, 'description' => $generatedText]);
                exit;
            }
        }

        // Handle error responses explicitly
        $errDetails = "HTTP Status {$httpCode}";
        if (!empty($curlErr)) {
            $errDetails .= " - Curl Error: " . $curlErr;
        }
        if (!empty($response)) {
            if (stripos($response, '<html') !== false) {
                if (stripos($response, 'cloudflare') !== false || stripos($response, 'access denied') !== false) {
                    $errDetails = "Access Denied by Cloudflare/Groq. The hosting server's IP range is blocked by Groq's security firewall. Consider using Google Gemini or a VPN/Proxy on the server.";
                } else {
                    $errDetails = "HTML Response (HTTP {$httpCode})";
                }
            } else {
                $resData = json_decode($response, true);
                if (isset($resData['error']['message'])) {
                    $errDetails = $resData['error']['message'];
                }
            }
        }

        echo json_encode(['success' => false, 'error' => "Groq API Connection Error: " . $errDetails]);
        exit;
    }
}