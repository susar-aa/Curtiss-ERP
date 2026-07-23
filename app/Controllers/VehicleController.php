<?php
class VehicleController extends Controller {
    private $vehicleModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->vehicleModel = $this->model('Vehicle');
    }

    public function index() {
        $this->checkPermission('vehicles', 'view');

        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalVehicles = $this->vehicleModel->getTotalVehicles($search);
        $totalPages = ceil($totalVehicles / $limit);

        // Fetch counts for KPIs
        $allVehicles = $this->vehicleModel->getAllVehicles();
        $activeCount = 0;
        $maintenanceCount = 0;
        $inactiveCount = 0;
        foreach($allVehicles as $v) {
            if($v->status === 'Active') $activeCount++;
            elseif($v->status === 'Maintenance' || $v->status === 'Under Maintenance') $maintenanceCount++;
            elseif($v->status === 'Inactive') $inactiveCount++;
        }

        // Fetch related dropdown options
        $drivers = $this->vehicleModel->getDrivers();
        $fuelTypes = $this->vehicleModel->getFuelTypes();
        
        $db = new Database();
        $db->query("SELECT id, bank_name, account_number FROM bank_accounts");
        $bankAccounts = $db->resultSet() ?: [];

        $data = [
            'title' => 'Vehicle & Fleet Management',
            'content_view' => 'vehicles/index',
            'vehicles' => $this->vehicleModel->getVehiclesPaginated($search, $limit, $offset),
            'search' => $search,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_vehicles' => $totalVehicles,
            'active_count' => $activeCount,
            'maintenance_count' => $maintenanceCount,
            'inactive_count' => $inactiveCount,
            'drivers' => $drivers,
            'fuel_types' => $fuelTypes,
            'bank_accounts' => $bankAccounts,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $this->checkPermission('vehicles', 'create_edit');
            
            $vehicleData = [
                'vehicle_number' => trim($_POST['vehicle_number']),
                'registration_number' => trim($_POST['registration_number'] ?? ''),
                'chassis_number' => trim($_POST['chassis_number'] ?? ''),
                'engine_number' => trim($_POST['engine_number'] ?? ''),
                'model' => trim($_POST['model']),
                'type' => trim($_POST['type']),
                'status' => $_POST['status'] ?? 'Active',
                'assigned_driver_id' => $_POST['assigned_driver_id'] ?? null,
                'fuel_type_id' => $_POST['fuel_type_id'] ?? null,
                'fuel_tank_capacity' => $_POST['fuel_tank_capacity'] ?? null,
                'avg_fuel_consumption' => $_POST['avg_fuel_consumption'] ?? null,
                'current_odometer' => $_POST['current_odometer'] ?? 0,
                'next_service_mileage' => $_POST['next_service_mileage'] ?? null,
                'insurance_expiry' => $_POST['insurance_expiry'] ?? null,
                'license_expiry' => $_POST['license_expiry'] ?? null
            ];

            if ($_POST['action'] == 'add_vehicle') {
                if (empty($vehicleData['vehicle_number']) || empty($vehicleData['model'])) {
                    $data['error'] = 'Vehicle Number and Model are required.';
                } else {
                    if ($this->vehicleModel->addVehicle($vehicleData, $_SESSION['user_id'])) {
                        header("Location: " . APP_URL . "/vehicle?success=" . urlencode('Vehicle added successfully')); exit;
                    } else { 
                        $data['error'] = 'Failed to add vehicle. Number might already be registered.'; 
                    }
                }
            } 
            elseif ($_POST['action'] == 'edit_vehicle') {
                $id = $_POST['vehicle_id'];
                if (empty($vehicleData['vehicle_number']) || empty($vehicleData['model'])) {
                    $data['error'] = 'Vehicle Number and Model are required.';
                } else {
                    if ($this->vehicleModel->updateVehicle($id, $vehicleData, $_SESSION['user_id'])) {
                        header("Location: " . APP_URL . "/vehicle?page=$page&search=" . urlencode($search) . "&success=" . urlencode('Vehicle updated successfully')); exit;
                    } else { 
                        $data['error'] = 'Failed to update vehicle.'; 
                    }
                }
            }
            elseif ($_POST['action'] == 'delete_vehicle') {
                $this->checkPermission('vehicles', 'delete');
                $id = $_POST['vehicle_id'];
                if ($this->vehicleModel->deleteVehicle($id)) {
                    header("Location: " . APP_URL . "/vehicle?success=" . urlencode('Vehicle deleted successfully')); exit;
                } else { 
                    $data['error'] = 'Failed to delete vehicle.'; 
                }
            }
        }

        if (isset($_GET['success'])) { $data['success'] = $_GET['success']; }
        $this->view('layouts/main', $data);
    }

    /* AJAX API - Get Details */
    public function api_get_vehicle_details($id) {
        $this->checkPermission('vehicles', 'view');
        header('Content-Type: application/json');

        $vehicle = $this->vehicleModel->getVehicleById(intval($id));
        if (!$vehicle) {
            echo json_encode(['status' => 'error', 'message' => 'Vehicle not found']);
            exit;
        }

        $odometerHistory = $this->vehicleModel->getOdometerHistory(intval($id));
        $fuelHistory = $this->vehicleModel->getFuelConsumptionHistory(intval($id));
        $history = $this->vehicleModel->getVehicleHistory(intval($id));
        $routes = $this->vehicleModel->getVehicleRouteAssignments(intval($id));
        $transactions = $this->vehicleModel->getRelatedTransactions(intval($id));

        echo json_encode([
            'status' => 'success',
            'vehicle' => $vehicle,
            'odometer_history' => $odometerHistory,
            'fuel_history' => $fuelHistory,
            'history' => $history,
            'routes' => $routes,
            'transactions' => $transactions
        ]);
        exit;
    }

    /* AJAX API - Add Fuel Record */
    public function api_add_fuel_entry() {
        $this->checkPermission('vehicles', 'create_edit');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            exit;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true) ?: $_POST;

        try {
            require_once dirname(__DIR__) . '/Services/FuelService.php';
            $fuelService = new FuelService();
            $res = $fuelService->recordFuelEntry($payload, intval($_SESSION['user_id']));

            if ($res === true) {
                echo json_encode(['status' => 'success', 'message' => 'Fuel transaction recorded successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $res ?: 'Failed to record fuel transaction.']);
            }
        } catch (Throwable $e) {
            error_log('[api_add_fuel_entry] ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine()
            ]);
        }
        exit;
    }

    /* AJAX API - Delete Fuel Record */
    public function api_delete_fuel_entry($id) {
        $this->checkPermission('vehicles', 'delete');
        header('Content-Type: application/json');

        require_once dirname(__DIR__) . '/Services/FuelService.php';
        $fuelService = new FuelService();
        $res = $fuelService->deleteFuelEntry(intval($id), intval($_SESSION['user_id']));

        if ($res === true) {
            echo json_encode(['status' => 'success', 'message' => 'Fuel transaction deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $res ?: 'Failed to delete fuel transaction.']);
        }
        exit;
    }

    /* AJAX API - Get Fuel Types */
    public function api_get_fuel_types() {
        $this->checkPermission('vehicles', 'view');
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'fuel_types' => $this->vehicleModel->getFuelTypes()
        ]);
        exit;
    }

    /* AJAX API - Save Fuel Type */
    public function api_save_fuel_type() {
        $this->checkPermission('vehicles', 'create_edit');
        header('Content-Type: application/json');

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true) ?: $_POST;

        $id = isset($payload['id']) ? intval($payload['id']) : 0;
        if ($id > 0) {
            $res = $this->vehicleModel->updateFuelType($id, $payload);
        } else {
            $res = $this->vehicleModel->addFuelType($payload);
        }

        if ($res) {
            echo json_encode(['status' => 'success', 'message' => 'Fuel type saved successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save fuel type.']);
        }
        exit;
    }

    /* AJAX API - Delete Fuel Type */
    public function api_delete_fuel_type($id) {
        $this->checkPermission('vehicles', 'delete');
        header('Content-Type: application/json');
        if ($this->vehicleModel->deleteFuelType(intval($id))) {
            echo json_encode(['status' => 'success', 'message' => 'Fuel type deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete fuel type.']);
        }
        exit;
    }

    /* AJAX API - Get Operations & Profitability Reports */
    public function api_get_reports() {
        $this->checkPermission('vehicles', 'view');
        header('Content-Type: application/json');

        $db = new Database();

        // 1. Fuel Consumption (Km/L) by Vehicle
        $db->query("SELECT v.id, v.vehicle_number, v.model,
                           (SELECT COUNT(*) FROM fuel_records WHERE vehicle_id = v.id) as refill_count,
                           (SELECT SUM(quantity) FROM fuel_records WHERE vehicle_id = v.id) as total_liters,
                           (SELECT SUM(total_amount) FROM fuel_records WHERE vehicle_id = v.id) as total_cost,
                           (SELECT MAX(odometer_reading) - MIN(odometer_reading) FROM fuel_records WHERE vehicle_id = v.id) as total_distance
                    FROM vehicles v
                    ORDER BY total_cost DESC");
        $rawConsumption = $db->resultSet() ?: [];
        $consumptionReport = [];
        foreach ($rawConsumption as $c) {
            $c->total_liters = floatval($c->total_liters);
            $c->total_cost = floatval($c->total_cost);
            $c->total_distance = intval($c->total_distance);
            $c->avg_km_l = ($c->total_liters > 0 && $c->total_distance > 0) ? round($c->total_distance / $c->total_liters, 2) : 0;
            $consumptionReport[] = $c;
        }

        // 2. Cost Breakdown by Driver
        $db->query("SELECT e.id as driver_id, e.first_name, e.last_name,
                           COUNT(fr.id) as refill_count,
                           SUM(fr.quantity) as total_liters,
                           SUM(fr.total_amount) as total_cost
                    FROM fuel_records fr
                    JOIN employees e ON fr.driver_id = e.id
                    GROUP BY e.id
                    ORDER BY total_cost DESC");
        $driverCostReport = $db->resultSet() ?: [];

        // 3. Profitability & Mileage Report (Sales revenue from route vs fuel expense)
        // We can link rep_daily_routes -> customer_payments and invoices (sales) vs fuel expenses
        $db->query("SELECT v.id, v.vehicle_number, v.model,
                           COALESCE((SELECT SUM(fr.total_amount) FROM fuel_records fr WHERE fr.vehicle_id = v.id), 0.0) as total_fuel_cost,
                           COALESCE((SELECT SUM(re.amount) FROM route_expenses re WHERE re.vehicle_number = v.vehicle_number), 0.0) as total_route_expenses,
                           COALESCE((SELECT SUM(inv.grand_total) FROM invoices inv 
                                     JOIN rep_daily_routes r ON inv.rep_route_id = r.id
                                     JOIN deliveries d ON d.rep_route_id = r.id
                                     WHERE d.vehicle_number = v.vehicle_number), 0.0) as total_sales
                    FROM vehicles v
                    ORDER BY total_sales DESC");
        $profitabilityReport = $db->resultSet() ?: [];
        foreach ($profitabilityReport as $p) {
            $p->total_fuel_cost = floatval($p->total_fuel_cost);
            $p->total_route_expenses = floatval($p->total_route_expenses);
            $p->total_sales = floatval($p->total_sales);
            // Deduct route expenses (which already includes fuel) and any extra fuel to find net contribution
            $p->net_profit = $p->total_sales - $p->total_route_expenses;
        }

        echo json_encode([
            'status' => 'success',
            'consumption' => $consumptionReport,
            'driver_costs' => $driverCostReport,
            'profitability' => $profitabilityReport
        ]);
        exit;
    }
}
