<?php
class VendorController extends Controller {
    private $vendorModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->vendorModel = $this->model('Vendor');
    }

    public function index($id = null) {
        $vendors = $this->vendorModel->getAllVendors();
        
        $selectedVendor = null;
        $expenses = [];
        $pos = [];

        if ($id) {
            $selectedVendor = $this->vendorModel->getVendorById($id);
            if ($selectedVendor) {
                $expenses = $this->vendorModel->getVendorExpenses($id);
                $pos = $this->vendorModel->getVendorPOs($id);
            }
        }

        $data = [
            'title' => 'Vendor & Supplier Center',
            'content_view' => 'vendors/index',
            'vendors' => $vendors,
            'selected_vendor' => $selectedVendor,
            'expenses' => $expenses,
            'pos' => $pos,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_vendor') {
                $vendorData = [
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? '')
                ];
                if (!empty($vendorData['name'])) {
                    if ($this->vendorModel->addVendor($vendorData)) {
                        header('Location: ' . APP_URL . '/vendor?success=added');
                        exit;
                    }
                }
            } elseif ($_POST['action'] == 'update_vendor') {
                $updateData = [
                    'id' => $_POST['vendor_id'],
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? '')
                ];

                if (!empty($updateData['name'])) {
                    if ($this->vendorModel->updateVendor($updateData)) {
                        header('Location: ' . APP_URL . '/vendor/index/' . $updateData['id'] . '?success=updated');
                        exit;
                    }
                }
            }
        }

        if (isset($_GET['success'])) {
            $data['success'] = "Vendor profile updated successfully!";
        }

        $this->view('layouts/main', $data);
    }
}