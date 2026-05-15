<?php
class WarehouseController extends Controller {
    private $warehouseModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->warehouseModel = $this->model('Warehouse');
    }

    public function index() {
        $data = [
            'title' => 'Warehouse Management',
            'content_view' => 'warehouses/index',
            'warehouses' => $this->warehouseModel->getAllWarehouses(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $whData = [
                'name' => trim($_POST['name']),
                'location' => trim($_POST['location']),
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];

            if ($_POST['action'] == 'add_warehouse') {
                if ($this->warehouseModel->addWarehouse($whData)) {
                    $data['success'] = 'Warehouse created successfully.';
                } else { $data['error'] = 'Failed to create warehouse.'; }
            } 
            elseif ($_POST['action'] == 'edit_warehouse') {
                $whData['id'] = $_POST['warehouse_id'];
                if ($this->warehouseModel->updateWarehouse($whData)) {
                    $data['success'] = 'Warehouse updated successfully.';
                } else { $data['error'] = 'Failed to update warehouse.'; }
            }
            elseif ($_POST['action'] == 'delete_warehouse') {
                if ($this->warehouseModel->deleteWarehouse($_POST['warehouse_id'])) {
                    $data['success'] = 'Warehouse deleted successfully.';
                } else { $data['error'] = 'Failed to delete. It is likely linked to existing products.'; }
            }
            $data['warehouses'] = $this->warehouseModel->getAllWarehouses();
        }

        $this->view('layouts/main', $data);
    }
}