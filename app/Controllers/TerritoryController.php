<?php
class TerritoryController extends Controller {
    private $territoryModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->territoryModel = $this->model('Territory');
    }

    public function index() {
        $mainAreas = $this->territoryModel->getMainAreas();
        
        foreach ($mainAreas as $area) {
            $area->mcas = $this->territoryModel->getMcaAreasWithDistance($area->id, $area->latitude, $area->longitude);
        }

        $db = new Database();
        $db->query("SELECT u.id, u.username, e.first_name, e.last_name 
                    FROM users u 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE u.role = 'rep' AND (u.status IS NULL OR u.status = 'Active') 
                    ORDER BY e.first_name ASC, u.username ASC");
        $repsList = $db->resultSet() ?: [];

        $data = [
            'title' => 'Territory & Routing',
            'content_view' => 'territories/index',
            'main_areas' => $mainAreas,
            'reps' => $repsList,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_main_area') {
                if ($this->territoryModel->addMainArea([
                    'name' => trim($_POST['name']),
                    'lat' => !empty($_POST['latitude']) ? $_POST['latitude'] : '7.8731',
                    'lng' => !empty($_POST['longitude']) ? $_POST['longitude'] : '80.7718',
                    'rep_id' => !empty($_POST['rep_id']) ? intval($_POST['rep_id']) : null
                ])) {
                    $data['success'] = "Main Area created successfully!";
                    header("Refresh:0"); 
                } else {
                    $data['error'] = "Failed to create Main Area.";
                }
            } elseif ($_POST['action'] == 'add_mca') {
                if ($this->territoryModel->addMcaArea([
                    'main_area_id' => $_POST['main_area_id'],
                    'name' => trim($_POST['name']),
                    'start_lat' => $_POST['start_lat'],
                    'start_lng' => $_POST['start_lng'],
                    'end_lat' => $_POST['end_lat'],
                    'end_lng' => $_POST['end_lng'],
                    'budget_km' => floatval($_POST['budget_km'] ?? 0),
                    'actual_route_km' => floatval($_POST['actual_route_km'] ?? 0)
                ])) {
                    $data['success'] = "MCA Route linked successfully!";
                    header("Refresh:0"); 
                } else {
                    $data['error'] = "Failed to create MCA Route.";
                }
            }
        }

        $this->view('layouts/main', $data);
    }
}