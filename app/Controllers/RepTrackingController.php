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

        $data = [
            'title' => 'Rep Route Tracking',
            'content_view' => 'rep-tracking/index',
            'routes' => $this->trackingModel->getAllRoutes(),
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'employees' => $allEmployees
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
}