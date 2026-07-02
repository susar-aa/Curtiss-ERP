<?php
class ServiceProviderController extends Controller {
    private $serviceProviderModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { 
            header('Location: ' . APP_URL . '/auth/login'); 
            exit; 
        }
        $this->serviceProviderModel = $this->model('ServiceProvider');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index($id = null) {
        $serviceProviders = $this->serviceProviderModel->getAllServiceProviders();
        
        $selectedServiceProvider = null;
        $stats = null;
        $ledger = [];
        $pos = [];
        $products = [];

        // Pre-fetch Accounts for any potential Modal
        $accounts = $this->coaModel->getAccounts();
        $assets = array_filter($accounts, function($a) { return $a->account_type == 'Asset'; });
        $liabilities = array_filter($accounts, function($a) { return $a->account_type == 'Liability'; });
        
        $apAccount = null;
        foreach($liabilities as $acc) {
            if (strpos(strtolower($acc->account_name), 'payable') !== false) {
                $apAccount = $acc; break;
            }
        }

        if ($id) {
            $selectedServiceProvider = $this->serviceProviderModel->getServiceProviderById($id);
            if ($selectedServiceProvider) {
                $stats = $this->serviceProviderModel->getServiceProviderStats($id);
                $ledger = $this->serviceProviderModel->getActivityLedger($id);
                $pos = $this->serviceProviderModel->getServiceProviderPOs($id);
                $products = $this->serviceProviderModel->getServiceProviderProducts($id);
            }
        }

        $data = [
            'title' => 'Service Provider Center',
            'content_view' => 'service_providers/index',
            'service_providers' => $serviceProviders,
            'selected_service_provider' => $selectedServiceProvider,
            'stats' => $stats,
            'ledger' => $ledger,
            'pos' => $pos,
            'products' => $products,
            'assets' => $assets,
            'ap_account' => $apAccount,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_service_provider') {
                $spData = [
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'service_category' => trim($_POST['service_category'] ?? ''),
                    'status' => trim($_POST['status'] ?? 'Active')
                ];
                if (!empty($spData['name'])) {
                    if ($this->serviceProviderModel->addServiceProvider($spData)) {
                        header('Location: ' . APP_URL . '/serviceprovider?success=added');
                        exit;
                    } else {
                        $data['error'] = 'Failed to add service provider.';
                    }
                } else {
                    $data['error'] = 'Service provider name is required.';
                }
            } elseif ($_POST['action'] == 'update_service_provider') {
                $updateData = [
                    'id' => intval($_POST['service_provider_id']),
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'service_category' => trim($_POST['service_category'] ?? ''),
                    'status' => trim($_POST['status'] ?? 'Active')
                ];

                if (!empty($updateData['name'])) {
                    if ($this->serviceProviderModel->updateServiceProvider($updateData)) {
                        header('Location: ' . APP_URL . '/serviceprovider/index/' . $updateData['id'] . '?success=updated');
                        exit;
                    } else {
                        $data['error'] = 'Failed to update service provider.';
                    }
                }
            }
        }

        if (isset($_GET['success'])) {
            if ($_GET['success'] == 'added') {
                $data['success'] = "Service provider registered successfully!";
            } elseif ($_GET['success'] == 'updated') {
                $data['success'] = "Service provider profile updated successfully!";
            }
        }

        $this->view('layouts/main', $data);
    }
}
