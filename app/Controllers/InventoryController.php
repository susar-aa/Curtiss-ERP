<?php
class InventoryController extends Controller {
    private $itemModel;
    private $categoryModel;
    private $variationModel;
    private $vendorModel;
    private $warehouseModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->itemModel = $this->model('Item');
        $this->categoryModel = $this->model('Category');
        $this->variationModel = $this->model('Variation');
        $this->vendorModel = $this->model('Vendor');
        $this->warehouseModel = $this->model('Warehouse');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $filters = [
            'category_id' => $_GET['category_id'] ?? '',
            'vendor_id' => $_GET['vendor_id'] ?? '',
            'warehouse_id' => $_GET['warehouse_id'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_item') {
            if ($this->itemModel->deleteItem($_POST['item_id'])) {
                header("Location: " . APP_URL . "/inventory?success=Product deleted successfully"); exit;
            } else { 
                $data['error'] = 'Cannot delete item. It is likely used in existing Invoices or POs.'; 
            }
        }

        $totalItems = $this->itemModel->getTotalItems($search, $filters);
        $totalPages = ceil($totalItems / $limit);

        $items = $this->itemModel->getItemsPaginated($search, $limit, $offset, $filters);
        foreach($items as $item) {
            $item->variations = $this->itemModel->getItemVariations($item->id);
            // General images (for quick view modal)
            $item->images = $this->itemModel->getItemImages($item->id); 
        }

        $data = [
            'title' => 'Product Management',
            'content_view' => 'inventory/index',
            'items' => $items,
            'categories' => $this->categoryModel->getAllCategories(),
            'vendors' => $this->vendorModel->getAllVendors(),
            'warehouses' => $this->warehouseModel->getAllWarehouses(),
            'kpis' => $this->itemModel->getInventoryKPIs(),
            'search' => $search,
            'filters' => $filters,
            'page' => $page,
            'total_pages' => $totalPages,
            'error' => '',
            'success' => $_GET['success'] ?? ''
        ];

        $this->view('layouts/main', $data);
    }

    // --- SMART IMAGE COMPRESSION ENGINE ---
    private function compressAndSaveImage($tmpPath, $mimeType) {
        $uploadDir = '../public/uploads/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = ($mimeType == 'image/png') ? 'png' : 'jpg';
        $fileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        list($width, $height) = getimagesize($tmpPath);
        $maxWidth = 1200; // Optimal sizing for web without losing visible quality
        
        if ($width > $maxWidth) {
            $ratio = $maxWidth / $width;
            $newWidth = $maxWidth;
            $newHeight = $height * $ratio;
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $image_p = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($mimeType == 'image/png') {
            imagealphablending($image_p, false);
            imagesavealpha($image_p, true);
            $transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
            imagefilledrectangle($image_p, 0, 0, $newWidth, $newHeight, $transparent);
            $image = imagecreatefrompng($tmpPath);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagepng($image_p, $destPath, 8); // PNG Compression level 0-9
        } else {
            $image = imagecreatefromjpeg($tmpPath);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($image_p, $destPath, 85); // JPEG Quality 0-100
        }
        
        imagedestroy($image_p);
        imagedestroy($image);
        
        return $fileName;
    }

    private function handleImageUploads($itemId) {
        // 1. Delete Requested Images
        if (!empty($_POST['deleted_images'])) {
            foreach($_POST['deleted_images'] as $imgId) {
                $this->itemModel->deleteImage($imgId);
            }
        }

        // 2. Process General Product Images Array
        if (isset($_FILES['product_images'])) {
            $files = $_FILES['product_images'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $mime = mime_content_type($files['tmp_name'][$i]);
                    if (in_array($mime, ['image/jpeg', 'image/png'])) {
                        $fileName = $this->compressAndSaveImage($files['tmp_name'][$i], $mime);
                        $this->itemModel->saveImage($itemId, null, $fileName);
                    }
                }
            }
        }

        // 3. Process Specific Variation Images
        if (isset($_FILES['var_image'])) {
            foreach ($_FILES['var_image']['name'] as $valId => $name) {
                if ($_FILES['var_image']['error'][$valId] === UPLOAD_ERR_OK) {
                    $tmpPath = $_FILES['var_image']['tmp_name'][$valId];
                    $mime = mime_content_type($tmpPath);
                    if (in_array($mime, ['image/jpeg', 'image/png'])) {
                        $fileName = $this->compressAndSaveImage($tmpPath, $mime);
                        $this->itemModel->saveImage($itemId, $valId, $fileName);
                    }
                }
            }
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $itemData = $this->extractFormData();
            $itemId = $this->itemModel->addItem($itemData);
            if ($itemId) {
                $this->handleImageUploads($itemId);
                header("Location: " . APP_URL . "/inventory?success=Product added successfully"); exit;
            } else { 
                $data['error'] = 'Failed to add item. Check if Item Code is unique.'; 
            }
        }

        $data = [
            'title' => 'Add New Product',
            'content_view' => 'inventory/form',
            'categories' => $this->categoryModel->getAllCategories(),
            'vendors' => $this->vendorModel->getAllVendors(),
            'warehouses' => $this->warehouseModel->getAllWarehouses(),
            'variations_tree' => $this->variationModel->getAllVariationsWithValues(),
            'error' => ''
        ];
        $this->view('layouts/main', $data);
    }

    public function edit($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/inventory'); exit; }
        
        $item = $this->itemModel->getItemById($id);
        if (!$item) { die("Product not found."); }
        
        $item->variations = $this->itemModel->getItemVariations($id);
        
        // Structure existing images for the view
        $allImages = $this->itemModel->getItemImages($id);
        $item->general_images = [];
        $item->var_images = [];
        foreach($allImages as $img) {
            if ($img->variation_value_id) {
                $item->var_images[$img->variation_value_id] = $img;
            } else {
                $item->general_images[] = $img;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $itemData = $this->extractFormData();
            $itemData['id'] = $id;
            
            if ($this->itemModel->updateItem($itemData)) {
                $this->handleImageUploads($id);
                header("Location: " . APP_URL . "/inventory?success=Product updated successfully"); exit;
            } else { 
                $data['error'] = 'Failed to update item.'; 
            }
        }

        $data = [
            'title' => 'Edit Product',
            'content_view' => 'inventory/form',
            'item' => $item,
            'categories' => $this->categoryModel->getAllCategories(),
            'vendors' => $this->vendorModel->getAllVendors(),
            'warehouses' => $this->warehouseModel->getAllWarehouses(),
            'variations_tree' => $this->variationModel->getAllVariationsWithValues(),
            'error' => ''
        ];
        $this->view('layouts/main', $data);
    }

    private function extractFormData() {
        $is_variable = isset($_POST['is_variable_pricing']) ? 1 : 0;
        $variations = [];
        
        if (isset($_POST['var_val_ids']) && is_array($_POST['var_val_ids'])) {
            foreach ($_POST['var_val_ids'] as $valId) {
                if (isset($_POST['var_ids'][$valId])) {
                    $varId = $_POST['var_ids'][$valId];
                    $price = ($is_variable && isset($_POST['var_price'][$valId])) ? floatval($_POST['var_price'][$valId]) : null;
                    $cost = ($is_variable && isset($_POST['var_cost'][$valId])) ? floatval($_POST['var_cost'][$valId]) : null;
                    $sku = isset($_POST['var_sku'][$valId]) ? trim($_POST['var_sku'][$valId]) : null;
                    
                    $variations[] = [
                        'variation_id' => $varId,
                        'variation_value_id' => $valId,
                        'sku' => $sku,
                        'price' => $price,
                        'cost' => $cost
                    ];
                }
            }
        }

        return [
            'item_code' => trim($_POST['item_code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'vendor_id' => !empty($_POST['vendor_id']) ? $_POST['vendor_id'] : null,
            'warehouse_id' => !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : null,
            'type' => $_POST['type'] ?? 'Inventory',
            'is_variable_pricing' => $is_variable,
            'price' => floatval($_POST['price'] ?? 0),
            'cost' => floatval($_POST['cost'] ?? 0),
            'min_stock' => intval($_POST['min_stock'] ?? 10),
            'variations' => $variations
        ];
    }
}