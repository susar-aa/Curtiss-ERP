<?php

class StockAuditController extends Controller {
    private $auditModel;
    private $warehouseModel;
    private $categoryModel;
    private $supplierModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->auditModel = $this->model('StockAudit');
        $this->warehouseModel = $this->model('Warehouse');
        $this->categoryModel = $this->model('Category');
        $this->supplierModel = $this->model('Supplier');
    }

    /**
     * Lists stock audits
     */
    public function index() {
        $warehouseId = $_GET['warehouse_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $filters = [
            'warehouse_id' => $warehouseId,
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $audits = $this->auditModel->getAllAudits($filters);
        $warehouses = $this->warehouseModel->getAllWarehouses();

        $data = [
            'title' => 'Stock Audits',
            'content_view' => 'stock_audits/index',
            'audits' => $audits,
            'warehouses' => $warehouses,
            'filters' => $filters
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Show creation screen
     */
    public function create() {
        $warehouses = $this->warehouseModel->getAllWarehouses();
        $categories = $this->categoryModel->getCategories();
        $suppliers = $this->supplierModel->getAllSuppliers();

        // Get unique brands
        $db = new Database();
        $db->query("SELECT DISTINCT brand FROM items WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        $brands = $db->resultSet() ?: [];

        $data = [
            'title' => 'New Stock Audit',
            'content_view' => 'stock_audits/create',
            'warehouses' => $warehouses,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'brands' => $brands
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Store new stock audit header and generate items
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockaudit');
            exit;
        }

        $payload = [
            'warehouse_id' => intval($_POST['warehouse_id']),
            'category_id' => $_POST['category_id'] ?? null,
            'brand' => $_POST['brand'] ?? null,
            'supplier_id' => $_POST['supplier_id'] ?? null,
            'remarks' => trim($_POST['remarks']),
            'created_by' => $_SESSION['user_id']
        ];

        if (!$payload['warehouse_id']) {
            $_SESSION['flash_error'] = 'Warehouse selection is required.';
            header('Location: ' . APP_URL . '/stockaudit/create');
            exit;
        }

        $auditId = $this->auditModel->createAudit($payload);
        if ($auditId) {
            $this->logActivity('Create Stock Audit', 'Operations', "Created Stock Audit Header ID {$auditId}", $auditId, null, $payload);
            header('Location: ' . APP_URL . '/stockaudit/wizard/' . $auditId);
        } else {
            $_SESSION['flash_error'] = 'Failed to create stock audit. Verify filters and try again.';
            header('Location: ' . APP_URL . '/stockaudit/create');
        }
        exit;
    }

    /**
     * counting wizard view
     */
    public function wizard($id) {
        $audit = $this->auditModel->getAuditById($id);
        if (!$audit) {
            header('Location: ' . APP_URL . '/stockaudit?error=Audit not found');
            exit;
        }

        if (in_array($audit->status, ['Approved', 'Completed', 'Cancelled'])) {
            header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
            exit;
        }

        $items = $this->auditModel->getAuditItems($id);

        $data = [
            'title' => 'Stock Count - ' . $audit->audit_number,
            'content_view' => 'stock_audits/wizard',
            'audit' => $audit,
            'items' => $items
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Save count post request (Draft or Finalize)
     */
    public function saveCount($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockaudit/wizard/' . $id);
            exit;
        }

        $audit = $this->auditModel->getAuditById($id);
        if (!$audit) {
            header('Location: ' . APP_URL . '/stockaudit?error=Audit not found');
            exit;
        }

        $counts = $_POST['counts'] ?? [];
        $remarks = $_POST['remarks'] ?? [];
        $overallRemarks = trim($_POST['overall_remarks'] ?? '');
        $action = $_POST['action'] ?? 'save_draft';

        $userId = $_SESSION['user_id'];

        if ($action === 'complete') {
            $res = $this->auditModel->completeCount($id, $counts, $remarks, $overallRemarks, $userId);
            if ($res) {
                $this->logActivity('Complete Stock Count', 'Operations', "Completed stock count for Audit ID {$id}", $id);
                $_SESSION['flash_success'] = 'Stock count completed successfully and pending approval.';
                header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
            } else {
                $_SESSION['flash_error'] = 'Failed to complete count.';
                header('Location: ' . APP_URL . '/stockaudit/wizard/' . $id);
            }
        } else {
            // Default Save Draft
            $res = $this->auditModel->saveDraftCount($id, $counts, $remarks, $overallRemarks);
            if ($res) {
                $this->logActivity('Save Stock Count Draft', 'Operations', "Saved draft count for Audit ID {$id}", $id);
                $_SESSION['flash_success'] = 'Stock count draft saved successfully.';
                header('Location: ' . APP_URL . '/stockaudit/wizard/' . $id);
            } else {
                $_SESSION['flash_error'] = 'Failed to save draft.';
                header('Location: ' . APP_URL . '/stockaudit/wizard/' . $id);
            }
        }
        exit;
    }

    /**
     * View audit results and action approval
     */
    public function view($id) {
        $audit = $this->auditModel->getAuditById($id);
        if (!$audit) {
            header('Location: ' . APP_URL . '/stockaudit?error=Audit not found');
            exit;
        }

        $items = $this->auditModel->getAuditItems($id);

        $data = [
            'title' => 'Stock Audit Details - ' . $audit->audit_number,
            'content_view' => 'stock_audits/view',
            'audit' => $audit,
            'items' => $items
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Approve audit: automatically triggers StockAdjustment creation & approval
     */
    public function approve($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
            exit;
        }

        $audit = $this->auditModel->getAuditById($id);
        if (!$audit || $audit->status !== 'Completed') {
            $_SESSION['flash_error'] = 'Audit must be in Completed status to approve.';
            header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
            exit;
        }

        $items = $this->auditModel->getAuditItems($id);
        $adjustmentItems = [];

        foreach ($items as $item) {
            $diff = floatval($item->difference);
            if ($diff != 0.00) {
                $adjustmentItems[] = [
                    'item_id' => $item->item_id,
                    'quantity' => $diff,
                    'unit_cost' => floatval($item->unit_cost),
                    'remarks' => "Variance from Stock Audit: " . $audit->audit_number
                ];
            }
        }

        if (empty($adjustmentItems)) {
            // No variances found! Approve audit directly
            $db = new Database();
            $db->query("UPDATE stock_audits SET status = 'Approved', approved_by = :approved_by, approved_at = CURRENT_TIMESTAMP WHERE id = :id");
            $db->bind(':approved_by', $_SESSION['user_id']);
            $db->bind(':id', $id);
            $db->execute();

            $this->logActivity('Approve Stock Audit', 'Operations', "Approved Stock Audit ID {$id} with zero variance", $id);
            $_SESSION['flash_success'] = 'Stock Audit approved. Zero stock variances found.';
            header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
            exit;
        }

        // Create adjustment
        $adjModel = $this->model('StockAdjustment');
        $adjPayload = [
            'warehouse_id' => $audit->warehouse_id,
            'reason' => 'Stock Audit Variance',
            'adjustment_date' => date('Y-m-d'),
            'created_by' => $_SESSION['user_id'],
            'stock_audit_id' => $audit->id,
            'remarks' => "Auto-generated adjustment for Stock Audit " . $audit->audit_number,
            'items' => $adjustmentItems
        ];

        $adjId = $adjModel->createAdjustment($adjPayload);
        if ($adjId) {
            // Approve the auto-created stock adjustment immediately!
            $approved = $adjModel->approveAdjustment($adjId, $_SESSION['user_id']);
            if ($approved) {
                $this->logActivity('Approve Stock Audit', 'Operations', "Approved Stock Audit ID {$id} and auto-posted Adjustment ID {$adjId}", $id);
                $_SESSION['flash_success'] = 'Stock Audit approved and stock levels adjusted successfully.';
            } else {
                $_SESSION['flash_error'] = 'Stock Audit adjustment created but failed to automatically post.';
            }
        } else {
            $_SESSION['flash_error'] = 'Failed to generate stock adjustment for variances.';
        }

        header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
        exit;
    }

    /**
     * Cancel audit
     */
    public function cancel($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
            exit;
        }

        if ($this->auditModel->cancelAudit($id)) {
            $this->logActivity('Cancel Stock Audit', 'Operations', "Cancelled Stock Audit ID {$id}", $id);
            $_SESSION['flash_success'] = 'Stock Audit cancelled successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to cancel audit.';
        }

        header('Location: ' . APP_URL . '/stockaudit/view/' . $id);
        exit;
    }

    /**
     * Print Count Sheet (physical count sheet without quantities for manual audits)
     */
    public function printCountSheet($id) {
        $audit = $this->auditModel->getAuditById($id);
        if (!$audit) {
            die("Audit not found.");
        }
        $items = $this->auditModel->getAuditItems($id);

        $data = [
            'audit' => $audit,
            'items' => $items
        ];

        $this->view('stock_audits/print_count', $data);
    }

    /**
     * Print Stock Audit Report with full variance details
     */
    public function printReport($id) {
        $audit = $this->auditModel->getAuditById($id);
        if (!$audit) {
            die("Audit not found.");
        }
        $items = $this->auditModel->getAuditItems($id);

        $data = [
            'audit' => $audit,
            'items' => $items
        ];

        $this->view('stock_audits/print_report', $data);
    }
}
