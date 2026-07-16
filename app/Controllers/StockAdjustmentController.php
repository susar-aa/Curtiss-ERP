<?php

class StockAdjustmentController extends Controller {
    private $adjustmentModel;
    private $warehouseModel;
    private $itemModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->adjustmentModel = $this->model('StockAdjustment');
        $this->warehouseModel = $this->model('Warehouse');
        $this->itemModel = $this->model('Item');
    }

    /**
     * Lists stock adjustments
     */
    public function index() {
        $warehouseId = $_GET['warehouse_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $reason = $_GET['reason'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $filters = [
            'warehouse_id' => $warehouseId,
            'status' => $status,
            'reason' => $reason,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $adjustments = $this->adjustmentModel->getAllAdjustments($filters);
        $warehouses = $this->warehouseModel->getAllWarehouses();

        // Unique reasons list
        $db = new Database();
        $db->query("SELECT DISTINCT reason FROM stock_adjustments WHERE reason IS NOT NULL AND reason != '' ORDER BY reason ASC");
        $reasons = $db->resultSet() ?: [];

        $data = [
            'title' => 'Stock Adjustments',
            'content_view' => 'stock_adjustments/index',
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
            'reasons' => $reasons,
            'filters' => $filters
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Show manual adjustment creation form
     */
    public function create() {
        $warehouses = $this->warehouseModel->getAllWarehouses();
        $allItems = $this->itemModel->getAllItems();

        $adjustedItems = [];
        foreach ($allItems as $item) {
            $variations = $this->itemModel->getItemVariations($item->id);

            // Fallback to variations_json if empty
            if (empty($variations) && !empty($item->variations_json)) {
                $decoded = json_decode($item->variations_json);
                if (is_array($decoded)) {
                    $variations = [];
                    foreach ($decoded as $v) {
                        $vObj = new stdClass();
                        $vObj->id = $v->id ?? 0;
                        $vObj->variation_name = 'Option';
                        $vObj->value_name = $v->attribute ?? $v->value ?? $v->value_name ?? '';
                        $vObj->sku = $v->sku ?? '';
                        $vObj->quantity_on_hand = $v->qty ?? $v->quantity_on_hand ?? $item->qty ?? 0;
                        $vObj->cost = $v->cost ?? $v->cost_price ?? $item->cost_price ?? 0.00;
                        $variations[] = $vObj;
                    }
                }
            }

            if (!empty($variations)) {
                foreach ($variations as $var) {
                    $adjustedItems[] = (object)[
                        'id' => $item->id,
                        'variation_option_id' => $var->id,
                        'item_code' => $var->sku ?: $item->item_code,
                        'barcode' => $var->sku ?: $item->barcode,
                        'name' => $item->name . ' - ' . $var->value_name,
                        'category_name' => $item->category_name,
                        'qty' => $var->quantity_on_hand,
                        'cost_price' => $var->cost ?: $item->cost_price
                    ];
                }
            } else {
                $adjustedItems[] = (object)[
                    'id' => $item->id,
                    'variation_option_id' => null,
                    'item_code' => $item->item_code,
                    'barcode' => $item->barcode,
                    'name' => $item->name,
                    'category_name' => $item->category_name,
                    'qty' => $item->qty,
                    'cost_price' => $item->cost_price
                ];
            }
        }

        $data = [
            'title' => 'Create Stock Adjustment',
            'content_view' => 'stock_adjustments/create',
            'warehouses' => $warehouses,
            'items' => $adjustedItems
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Handle manual adjustment creation
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockadjustment');
            exit;
        }

        $warehouseId = intval($_POST['warehouse_id']);
        $reason = trim($_POST['reason']);
        $adjustmentDate = trim($_POST['adjustment_date'] ?? date('Y-m-d'));
        $remarks = trim($_POST['remarks'] ?? '');

        $itemIds = $_POST['item_ids'] ?? [];
        $variationOptionIds = $_POST['variation_option_ids'] ?? [];
        $itemCodes = $_POST['item_codes'] ?? [];
        $variationNames = $_POST['variation_names'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $unitCosts = $_POST['unit_costs'] ?? [];
        $itemRemarks = $_POST['item_remarks'] ?? [];

        if (!$warehouseId || !$reason) {
            $_SESSION['flash_error'] = 'Warehouse and Reason are required.';
            header('Location: ' . APP_URL . '/stockadjustment/create');
            exit;
        }

        if (empty($itemIds)) {
            $_SESSION['flash_error'] = 'Please add at least one item to adjust.';
            header('Location: ' . APP_URL . '/stockadjustment/create');
            exit;
        }

        $db = new Database();
        $adjustmentItems = [];
        for ($i = 0; $i < count($itemIds); $i++) {
            $itemId = intval($itemIds[$i]);
            $varOptId = !empty($variationOptionIds[$i]) ? intval($variationOptionIds[$i]) : null;
            $qty = floatval($quantities[$i]);
            $unitCost = floatval($unitCosts[$i]);
            $sku = trim($itemCodes[$i] ?? '');
            $fullName = trim($variationNames[$i] ?? '');

            if ($itemId && empty($varOptId)) {
                // 1. Try to find variation_option_id by SKU in item_variation_options
                if ($sku) {
                    $db->query("SELECT id FROM item_variation_options WHERE item_id = :item_id AND sku = :sku LIMIT 1");
                    $db->bind(':item_id', $itemId);
                    $db->bind(':sku', $sku);
                    $row = $db->single();
                    if ($row) {
                        $varOptId = $row->id;
                    }
                }

                // 2. If still empty, try to resolve by attribute value name from name (e.g. "Test Variation - Green")
                if (empty($varOptId) && $fullName) {
                    if (strpos($fullName, ' - ') !== false) {
                        $parts = explode(' - ', $fullName);
                        $valName = trim(end($parts));

                        $db->query("
                            SELECT ivo.id 
                            FROM item_variation_options ivo
                            JOIN variation_values vv ON ivo.variation_value_id = vv.id
                            WHERE ivo.item_id = :item_id AND vv.value_name = :val_name 
                            LIMIT 1
                        ");
                        $db->bind(':item_id', $itemId);
                        $db->bind(':val_name', $valName);
                        $row = $db->single();
                        if ($row) {
                            $varOptId = $row->id;
                        }
                    }
                }
            }

            if ($itemId && $qty != 0.00) {
                $adjustmentItems[] = [
                    'item_id' => $itemId,
                    'variation_option_id' => $varOptId,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'remarks' => trim($itemRemarks[$i] ?? '')
                ];
            }
        }

        if (empty($adjustmentItems)) {
            $_SESSION['flash_error'] = 'At least one item with non-zero adjustment quantity must be added.';
            header('Location: ' . APP_URL . '/stockadjustment/create');
            exit;
        }

        $payload = [
            'warehouse_id' => $warehouseId,
            'reason' => $reason,
            'adjustment_date' => $adjustmentDate,
            'created_by' => $_SESSION['user_id'],
            'remarks' => $remarks,
            'attachment_path' => null,
            'items' => $adjustmentItems
        ];

        $adjId = $this->adjustmentModel->createAdjustment($payload);
        if ($adjId) {
            $this->logActivity('Create Stock Adjustment', 'Operations', "Created manual Stock Adjustment ID {$adjId}", $adjId, null, $payload);
            $_SESSION['flash_success'] = 'Stock Adjustment request created and is pending approval.';
            header('Location: ' . APP_URL . '/stockadjustment/show/' . $adjId);
        } else {
            $_SESSION['flash_error'] = 'Failed to create stock adjustment.';
            header('Location: ' . APP_URL . '/stockadjustment/create');
        }
        exit;
    }

    /**
     * View adjustment details
     */
    public function show($id) {
        $adjustment = $this->adjustmentModel->getAdjustmentById($id);
        if (!$adjustment) {
            header('Location: ' . APP_URL . '/stockadjustment?error=Adjustment not found');
            exit;
        }

        $items = $this->adjustmentModel->getAdjustmentItems($id);

        $data = [
            'title' => 'Stock Adjustment Details - ' . $adjustment->adjustment_number,
            'content_view' => 'stock_adjustments/view',
            'adjustment' => $adjustment,
            'items' => $items
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Approve adjustment
     */
    public function approve($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockadjustment/show/' . $id);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $res = $this->adjustmentModel->approveAdjustment($id, $userId);

        if ($res) {
            $this->logActivity('Approve Stock Adjustment', 'Operations', "Approved Stock Adjustment ID {$id}", $id);
            $_SESSION['flash_success'] = 'Stock Adjustment approved. Quantities and accounting journals posted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to approve stock adjustment.';
        }

        header('Location: ' . APP_URL . '/stockadjustment/show/' . $id);
        exit;
    }

    /**
     * Reject adjustment
     */
    public function reject($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockadjustment/show/' . $id);
            exit;
        }

        $userId = $_SESSION['user_id'];
        if ($this->adjustmentModel->rejectAdjustment($id, $userId)) {
            $this->logActivity('Reject Stock Adjustment', 'Operations', "Rejected Stock Adjustment ID {$id}", $id);
            $_SESSION['flash_success'] = 'Stock Adjustment request rejected.';
        } else {
            $_SESSION['flash_error'] = 'Failed to reject stock adjustment.';
        }

        header('Location: ' . APP_URL . '/stockadjustment/show/' . $id);
        exit;
    }

    /**
     * Delete/cancel stock adjustment
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockadjustment');
            exit;
        }

        // 1. Verify CSRF Token
        $this->validateCsrfOrDie();

        // 2. Verify Admin Role
        if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
            $_SESSION['flash_error'] = 'Access Denied: Only Administrators can delete stock adjustments.';
            header('Location: ' . APP_URL . '/stockadjustment');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $res = $this->adjustmentModel->deleteAdjustment($id, $userId);

        if ($res) {
            $this->logActivity('Delete Stock Adjustment', 'Operations', "Deleted Stock Adjustment ID {$id}", $id);
            $_SESSION['flash_success'] = 'Stock Adjustment deleted and reversed successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete stock adjustment.';
        }

        header('Location: ' . APP_URL . '/stockadjustment');
        exit;
    }
}
