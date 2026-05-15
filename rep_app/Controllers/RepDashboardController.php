<?php
class RepDashboardController extends RepController {
    private $routeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->routeModel = $this->model('RepRoute');
    }

    public function index() {
        error_log("--- Rep App: index() Loaded ---");
        
        // ROUTER FIX: Catch POST requests that land on index due to the URL structure
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            error_log("--- Rep App: POST request intercepted in index() ---");
            error_log("POST DATA: " . print_r($_POST, true));
            
            if (isset($_POST['start_meter'])) {
                return $this->start_trip();
            }
        }

        $activeRoute = $this->routeModel->getActiveRoute($_SESSION['user_id']);
        
        $data = [
            'title' => 'Territory Hub',
            'content_view' => 'dashboard',
            'active_route' => $activeRoute,
            'success' => $_GET['success'] ?? ''
        ];
        $this->view('layout', $data);
    }

    public function start_route() {
        error_log("--- Rep App: start_route() UI Loaded ---");
        
        $activeRoute = $this->routeModel->getActiveRoute($_SESSION['user_id']);
        if ($activeRoute) { header('Location: ' . APP_URL . '/rep'); exit; } 

        $data = [
            'title' => 'Start Your Day',
            'content_view' => 'start_route',
            'routes' => $this->routeModel->getMcaRoutes(),
            'error' => $_GET['error'] ?? ''
        ];
        $this->view('layout', $data);
    }

    public function start_trip() {
        error_log("--- Rep App: Executing start_trip() ---");
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $routeName = $_POST['route_name'] ?? 'Unknown Route';
            $startMeter = floatval($_POST['start_meter']);
            
            // CRITICAL FIX: Empty strings from JS will crash MySQL DECIMAL columns. 
            // We MUST explicitly convert "" to null to prevent fatal PDO Exceptions.
            $lat = !empty($_POST['start_lat']) ? $_POST['start_lat'] : null;
            $lng = !empty($_POST['start_lng']) ? $_POST['start_lng'] : null;

            error_log("Processed Data -> Route: $routeName, Meter: $startMeter, Lat: $lat, Lng: $lng");

            if ($startMeter <= 0) {
                error_log("Rep App Error: Invalid Odometer Reading.");
                header('Location: ' . APP_URL . '/rep/start_route?error=Invalid Odometer Reading');
                exit;
            }

            if ($this->routeModel->startRoute($_SESSION['user_id'], $routeName, $startMeter, $lat, $lng)) {
                error_log("Rep App Success: Route started successfully in database!");
                header('Location: ' . APP_URL . '/rep?success=Route Started Successfully!'); 
                exit;
            } else {
                error_log("Rep App Error: Database failed to insert the route.");
                header('Location: ' . APP_URL . '/rep/start_route?error=Database Error: Could not start route.');
                exit;
            }
        }
    }
}