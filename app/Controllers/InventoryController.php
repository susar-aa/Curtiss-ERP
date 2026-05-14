<?php
class InventoryController extends Controller {
    private $itemModel;
    private $coaModel;
    private $categoryModel;
    private $notificationModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->itemModel = $this->model('Item');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->categoryModel = $this->model('Category');
        $this->notificationModel = $this->model('Notification');
    }

    public function index() {
        $accounts = $this->coaModel->getAccounts();
        $revenues = array_filter($accounts, function($a) { return $a->account_type == 'Revenue'; });
        $expenses = array_filter($accounts, function($a) { return $a->account_type == 'Expense' || $a->account_type == 'Asset'; });

        $data = [
            'title' => 'Products & Inventory',
            'content_view' => 'inventory/index',
            'items' => $this->itemModel->getAllItems(),
            'categories' => $this->categoryModel->getAllCategories(),
            'revenues' => $revenues,
            'expenses' => $expenses,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            
            // Handle Add Category
            if ($_POST['action'] == 'add_category') {
                $catName = trim($_POST['category_name']);
                if (!empty($catName)) {
                    if ($this->categoryModel->addCategory($catName, trim($_POST['description'] ?? ''))) {
                        $data['success'] = "Category '$catName' created successfully.";
                        $data['categories'] = $this->categoryModel->getAllCategories();
                    } else {
                        $data['error'] = "Category name already exists.";
                    }
                }
            }

            // Handle Add Item
            if ($_POST['action'] == 'add_item') {
                $itemData = [
                    'item_code' => trim($_POST['item_code']),
                    'name' => trim($_POST['name']),
                    'category_id' => $_POST['category_id'] ?: null,
                    'type' => $_POST['type'],
                    'price' => floatval($_POST['price']),
                    'cost' => floatval($_POST['cost']),
                    'qty' => intval($_POST['qty']),
                    'min_stock' => intval($_POST['min_stock'] ?? 0),
                    'income_account_id' => $_POST['income_account_id'],
                    'expense_account_id' => $_POST['expense_account_id']
                ];

                if (empty($itemData['name']) || empty($itemData['income_account_id'])) {
                    $data['error'] = 'Item Name and Income Account are required.';
                } else {
                    if ($this->itemModel->addItem($itemData)) {
                        $data['success'] = 'Product added successfully!';
                        $data['items'] = $this->itemModel->getAllItems();
                        
                        // Check if the initial quantity triggers a low stock alert
                        if ($itemData['type'] === 'Inventory' && $itemData['qty'] <= $itemData['min_stock']) {
                            $msg = "Warning: {$itemData['name']} is currently at or below the minimum stock level ({$itemData['min_stock']}). Please issue a Purchase Order.";
                            $this->notificationModel->createNotification($_SESSION['user_id'], 'Low Stock Alert', $msg, '/purchase/create');
                        }
                        
                    } else {
                        $data['error'] = 'Failed to add item. Check if Item Code is unique.';
                    }
                }
            }
        }

        // Dashboard check: Alert user immediately on the UI if items are low
        $data['low_stock_items'] = $this->itemModel->getLowStockItems();

        $this->view('layouts/main', $data);
    }
}