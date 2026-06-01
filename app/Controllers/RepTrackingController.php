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
        
        // 2. Fetch outstanding customers (aggregated outstanding totals) in that main_area_id
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