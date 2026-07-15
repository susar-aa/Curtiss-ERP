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
        $items = $this->itemModel->getAllItems();

        $data = [
            'title' => 'Create Stock Adjustment',
            'content_view' => 'stock_adjustments/create',
            'warehouses' => $warehouses,
            'items' => $items
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

        $adjustmentItems = [];
        for ($i = 0; $i < count($itemIds); $i++) {
            $itemId = intval($itemIds[$i]);
            $qty = floatval($quantities[$i]);
            $unitCost = floatval($unitCosts[$i]);

            if ($itemId && $qty != 0.00) {
                $adjustmentItems[] = [
                    'item_id' => $itemId,
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

        // Handle attachment file upload if present
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../public/uploads/adjustments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['attachment']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                $attachmentPath = 'uploads/adjustments/' . $fileName;
            }
        }

        $payload = [
            'warehouse_id' => $warehouseId,
            'reason' => $reason,
            'adjustment_date' => $adjustmentDate,
            'created_by' => $_SESSION['user_id'],
            'remarks' => $remarks,
            'attachment_path' => $attachmentPath,
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
}
