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