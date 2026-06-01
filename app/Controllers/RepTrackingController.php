<?php
class RepTrackingController extends Controller {
    private $trackingModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->trackingModel = $this->model('RepTracking');
    }

    public function index() {
        $vehicleModel = $this->model('Vehicle');
        $employeeModel = $this->model('Employee');

        // Fetch all vehicles
        $vehicles = $vehicleModel->getAllVehicles();

        // Fetch all employees to separate drivers and all employees (for partners)
        $allEmployees = $employeeModel->getAllEmployees();

        // Filter drivers where job_title is "Driver" (case-insensitive) and status is "Active"
        $drivers = array_filter($allEmployees, function($emp) {
            return strtolower($emp->job_title) === 'driver' && $emp->status === 'Active';
        });

        // Fetch Bank Accounts for Finalization dropdown
        $db = new Database();
        $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600'");
        $parent = $db->single();
        $parentId = $parent ? $parent->id : 0;
        
        $db->query("SELECT * FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $db->bind(':pid', $parentId);
        $bankAccounts = $db->resultSet() ?: [];

        $data = [
            'title' => 'Rep Route Tracking',
            'content_view' => 'rep-tracking/index',
            'routes' => $this->trackingModel->getAllRoutes(),
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'employees' => $allEmployees,
            'bank_accounts' => $bankAccounts
        ];
        
        $this->view('layouts/main', $data);
    }

    // API Endpoint for AJAX fetching
    public function api_get_route_details($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        
        $bills = $this->trackingModel->getRouteBills($routeId);
        
        // Return JSON response to the Javascript frontend
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $bills]);
        exit;
    }

    // API: chronological GPS path (start → invoices → end)
    public function api_get_route_path($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            die("Invalid Request");
        }

        $path = $this->trackingModel->getRoutePath($routeId);
        header('Content-Type: application/json');
        if (!$path) {
            echo json_encode(['status' => 'error', 'message' => 'Route not found']);
            exit;
        }
        echo json_encode(['status' => 'success', 'path' => $path]);
        exit;
    }

    // Helper to auto-apply customer payments to non-voided invoices in chronological (FIFO) order
    private function autoApplyPaymentsToInvoices($customerId) {
        $db = new Database();
        
        // 1. Get total paid amount for this customer
        $db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE customer_id = :cid");
        $db->bind(':cid', $customerId);
        $rowPaid = $db->single();
        $totalPaid = $rowPaid ? floatval($rowPaid->total_paid) : 0.0;
        
        // 2. Get all non-voided invoices in chronological order
        $db->query("
            SELECT id, 
                   (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total,
                   status
            FROM invoices
            WHERE customer_id = :cid AND status != 'Voided'
            ORDER BY invoice_date ASC, id ASC
        ");
        $db->bind(':cid', $customerId);
        $invoices = $db->resultSet();
        
        $remainingPaid = $totalPaid;
        
        foreach ($invoices as $inv) {
            $grandTotal = floatval($inv->true_grand_total);
            
            if ($remainingPaid >= $grandTotal - 0.01) { // Allow minor rounding differences
                $newStatus = 'Paid';
                $remainingPaid -= $grandTotal;
            } else {
                $newStatus = 'Unpaid';
                $remainingPaid = 0;
            }
            
            if ($inv->status !== $newStatus) {
                $db->query("UPDATE invoices SET status = :status WHERE id = :id");
                $db->bind(':status', $newStatus);
                $db->bind(':id', $inv->id);
                $db->execute();
            }
        }
    }

    // API: outstanding credit bills in the same main area/territory of the route
    public function api_get_outstanding_bills($routeId) {
        $db = new Database();
        
        // 1. Get the main_area_id of the current route
        $db->query("
            SELECT m.main_area_id
            FROM mca_areas m
            JOIN rep_daily_routes r ON r.route_name = m.name
            WHERE r.id = :rid
            LIMIT 1
        ");
        $db->bind(':rid', $routeId);
        $row = $db->single();
        
        $mainAreaId = null;
        if ($row) {
            $mainAreaId = $row->main_area_id;
        } else {
            // Fallback: check customers associated with invoices on this route
            $db->query("
                SELECT DISTINCT m.main_area_id
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                JOIN mca_areas m ON c.mca_id = m.id
                WHERE i.rep_route_id = :rid AND c.mca_id IS NOT NULL
                LIMIT 1
            ");
            $db->bind(':rid', $routeId);
            $rowFallback = $db->single();
            if ($rowFallback) {
                $mainAreaId = $rowFallback->main_area_id;
            }
        }
        
        if (!$mainAreaId) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'bills' => []]);
            exit;
        }

        // 2. Fetch and heal statuses for all customers in this territory who have invoices
        $db->query("
            SELECT DISTINCT c.id
            FROM customers c
            JOIN mca_areas m ON c.mca_id = m.id
            JOIN invoices i ON i.customer_id = c.id
            WHERE m.main_area_id = :mid AND i.status != 'Voided'
        ");
        $db->bind(':mid', $mainAreaId);
        $custs = $db->resultSet();
        foreach ($custs as $c) {
            $this->autoApplyPaymentsToInvoices($c->id);
        }
        
        // 3. Fetch outstanding customers (aggregated outstanding totals) in that main_area_id
        $db->query("
            SELECT c.id as customer_id, c.name as customer_name, m.name as mca_name,
                   SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_outstanding
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            JOIN mca_areas m ON c.mca_id = m.id
            WHERE m.main_area_id = :mid
              AND i.status = 'Unpaid'
            GROUP BY c.id, c.name, m.name
            ORDER BY c.name ASC
        ");
        $db->bind(':mid', $mainAreaId);
        $bills = $db->resultSet();
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $bills]);
        exit;
    }

    // NEW: Endpoint to generate and show the Loading Report
    public function print_loading($routeId) {
        $data = [
            'route' => $this->trackingModel->getRouteById($routeId),
            'items' => $this->trackingModel->getRouteLoadingItems($routeId)
        ];
        
        // Load the print view directly (without the main navbar/sidebar layout)
        $this->view('rep-tracking/print_loading', $data);
    }

    // NEW: Get collections and unfinalized GL details for a route
    public function api_get_route_collections($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        $collections = $this->trackingModel->getRouteCollections($routeId);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'collections' => $collections]);
        exit;
    }

    // NEW: Finalize GL double entries for selected collections
    public function api_finalize_collections() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $paymentIds = $payload['payment_ids'] ?? [];
        $bankAllocations = $payload['bank_allocations'] ?? [];
        $userId = $_SESSION['user_id'];
        
        try {
            $db = new Database();
            $db->beginTransaction();
            
            $this->trackingModel->finalizePayments($paymentIds, $userId, $bankAllocations);
            
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Selected collections posted to GL successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}