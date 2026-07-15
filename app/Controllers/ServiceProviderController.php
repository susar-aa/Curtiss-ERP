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
        $expenseAccounts = array_filter($accounts, function($a) { return $a->account_type == 'Expense'; });
        
        $apAccount = null;
        foreach($liabilities as $acc) {
            if (strpos(strtolower($acc->account_name), 'payable') !== false) {
                $apAccount = $acc; break;
            }
        }

        $bills = [];
        $nextBillNumber = '';

        if ($id) {
            $selectedServiceProvider = $this->serviceProviderModel->getServiceProviderById($id);
            if ($selectedServiceProvider) {
                $stats = $this->serviceProviderModel->getServiceProviderStats($id);
                $ledger = $this->serviceProviderModel->getActivityLedger($id);
                $pos = $this->serviceProviderModel->getServiceProviderPOs($id);
                $products = $this->serviceProviderModel->getServiceProviderProducts($id);
                $bills = $this->serviceProviderModel->getServiceBills($id);

                // Generate next bill number
                $db = new Database();
                $db->query("SELECT id FROM goods_receipt_notes ORDER BY id DESC LIMIT 1");
                $lastRow = $db->single();
                $nextId = $lastRow ? ($lastRow->id + 1) : 1;
                $nextBillNumber = str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);
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
            'bills' => $bills,
            'expenses' => $expenseAccounts,
            'assets' => $assets,
            'ap_account' => $apAccount,
            'next_bill_number' => $nextBillNumber,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_service_provider') {
                $category = trim($_POST['service_category_select'] ?? '');
                if ($category === 'Other') {
                    $category = trim($_POST['service_category_custom'] ?? '');
                }
                $spData = [
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'service_category' => $category,
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
                $category = trim($_POST['service_category_select'] ?? '');
                if ($category === 'Other') {
                    $category = trim($_POST['service_category_custom'] ?? '');
                }
                $updateData = [
                    'id' => intval($_POST['service_provider_id']),
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'service_category' => $category,
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
            } elseif ($_POST['action'] == 'add_service_bill') {
                $spId = intval($_POST['service_provider_id']);
                $provider = $this->serviceProviderModel->getServiceProviderById($spId);

                // Re-calculate / generate next unique bill number on insert
                $db = new Database();
                $db->query("SELECT id FROM goods_receipt_notes ORDER BY id DESC LIMIT 1");
                $lastRow = $db->single();
                $nextId = $lastRow ? ($lastRow->id + 1) : 1;
                $billNumber = str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);
                
                $attachmentPath = null;
                if (!empty($_FILES['attachment']['name'])) {
                    $fileName = time() . '_' . basename($_FILES['attachment']['name']);
                    $targetDir = '../public/uploads/service_bills/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $targetFile = $targetDir . $fileName;
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                        $attachmentPath = 'uploads/service_bills/' . $fileName;
                    }
                }

                $billData = [
                    'service_provider_id' => $spId,
                    'provider_name' => $provider ? $provider->name : 'Service Provider',
                    'bill_number' => $billNumber,
                    'receipt_number' => trim($_POST['receipt_number'] ?? ''),
                    'bill_date' => trim($_POST['bill_date'] ?? ''),
                    'due_date' => trim($_POST['due_date'] ?? ''),
                    'service_period' => trim($_POST['service_period'] ?? ''),
                    'amount' => floatval($_POST['amount'] ?? 0),
                    'tax' => floatval($_POST['tax'] ?? 0),
                    'total_amount' => floatval($_POST['total_amount'] ?? 0),
                    'notes' => trim($_POST['notes'] ?? ''),
                    'expense_account_id' => intval($_POST['expense_account_id']),
                    'ap_account_id' => intval($_POST['ap_account_id']),
                    'attachment' => $attachmentPath
                ];

                if (!empty($billData['bill_number']) && $billData['total_amount'] > 0) {
                    if ($this->serviceProviderModel->addServiceBill($billData, $_SESSION['user_id'])) {
                        $this->logActivity('Record Service Bill', 'Service Providers', "Recorded Service Bill: {$billData['bill_number']} for amount Rs: " . number_format($billData['total_amount'], 2));
                        header('Location: ' . APP_URL . '/serviceprovider/index/' . $spId . '?success=bill_added');
                        exit;
                    } else {
                        $data['error'] = 'Failed to add service bill.';
                    }
                } else {
                    $data['error'] = 'Bill number and amount are required.';
                }
            }
        }

        if (isset($_GET['success'])) {
            if ($_GET['success'] == 'added') {
                $data['success'] = "Service provider registered successfully!";
            } elseif ($_GET['success'] == 'updated') {
                $data['success'] = "Service provider profile updated successfully!";
            } elseif ($_GET['success'] == 'bill_added') {
                $data['success'] = "Service bill recorded and posted successfully!";
            }
        }

        $this->view('layouts/main', $data);
    }

    public function bill($id) {
        $bill = $this->serviceProviderModel->getServiceBillById(intval($id));
        if (!$bill) {
            die('Service Bill not found.');
        }
        $provider = $this->serviceProviderModel->getServiceProviderById($bill->service_provider_id);
        
        $data = [
            'title' => 'Service Bill Details - ' . $bill->grn_number,
            'bill' => $bill,
            'provider' => $provider
        ];
        
        $this->view('service_providers/bill_detail', $data);
    }
}
