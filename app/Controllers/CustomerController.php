<?php
class CustomerController extends Controller {
    private $customerModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->customerModel = $this->model('Customer');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index($id = null) {
        $customers = $this->customerModel->getAllCustomers();
        
        $selectedCustomer = null;
        $stats = null;
        $ledger = [];
        $invoices = [];
        $cheques = [];

        // Pre-fetch Accounts for the Payment Modal
        $accounts = $this->coaModel->getAccounts();
        $assets = array_filter($accounts, function($a) { return $a->account_type == 'Asset'; });
        $arAccount = null;
        foreach($assets as $acc) {
            if (strpos(strtolower($acc->account_name), 'receivable') !== false) {
                $arAccount = $acc; break;
            }
        }

        if ($id) {
            $selectedCustomer = $this->customerModel->getCustomerById($id);
            if ($selectedCustomer) {
                $stats = $this->customerModel->getCustomerStats($id);
                $ledger = $this->customerModel->getActivityLedger($id);
                $invoices = $this->customerModel->getCustomerInvoices($id, 5); // Limit to latest 5
                $cheques = $this->customerModel->getCustomerCheques($id, 5); // Limit to latest 5
            }
        }

        $mcaAreas = $this->customerModel->getMcaAreas();

        $data = [
            'title' => 'Customer Profile',
            'content_view' => 'customers/index',
            'customers' => $customers,
            'selected_customer' => $selectedCustomer,
            'stats' => $stats,
            'ledger' => $ledger,
            'invoices' => $invoices,
            'cheques' => $cheques,
            'assets' => $assets,
            'ar_account' => $arAccount,
            'mca_areas' => $mcaAreas,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_customer') {
                $mcaId = !empty($_POST['mca_id']) ? intval($_POST['mca_id']) : null;
                $territoryName = null;
                if ($mcaId) {
                    foreach ($mcaAreas as $area) {
                        if ($area->id == $mcaId) {
                            $territoryName = $area->name;
                            break;
                        }
                    }
                }

                $addData = [
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'lat' => !empty($_POST['latitude']) ? $_POST['latitude'] : null,
                    'lng' => !empty($_POST['longitude']) ? $_POST['longitude'] : null,
                    'mca_id' => $mcaId,
                    'territory' => $territoryName
                ];

                if (!empty($addData['name'])) {
                    if ($this->customerModel->addCustomer($addData)) {
                        $this->logActivity('Add Customer', 'Customer', "Registered new customer profile: {$addData['name']}");
                        header('Location: ' . APP_URL . '/customer/index?success=add'); exit;
                    } else { $data['error'] = 'Failed to create new customer profile.'; }
                } else { $data['error'] = 'Customer/Company name is required.'; }

            } elseif ($_POST['action'] == 'update_customer') {
                $mcaId = !empty($_POST['mca_id']) ? intval($_POST['mca_id']) : null;
                $territoryName = null;
                if ($mcaId) {
                    foreach ($mcaAreas as $area) {
                        if ($area->id == $mcaId) {
                            $territoryName = $area->name;
                            break;
                        }
                    }
                }

                $updateData = [
                    'id' => $_POST['customer_id'],
                    'name' => trim($_POST['name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'lat' => !empty($_POST['latitude']) ? $_POST['latitude'] : null,
                    'lng' => !empty($_POST['longitude']) ? $_POST['longitude'] : null,
                    'mca_id' => $mcaId,
                    'territory' => $territoryName
                ];

                if (!empty($updateData['name'])) {
                    if ($this->customerModel->updateCustomer($updateData)) {
                        $this->logActivity('Update Customer', 'Customer', "Updated profile details for Customer ID {$updateData['id']} ({$updateData['name']})");
                        header('Location: ' . APP_URL . '/customer/index/' . $updateData['id'] . '?success=1'); exit;
                    } else { $data['error'] = 'Failed to update customer details.'; }
                }

            } elseif ($_POST['action'] == 'record_payment') {
                $paymentData = [
                    'customer_id' => $_POST['customer_id'],
                    'amount' => floatval($_POST['amount']),
                    'date' => $_POST['payment_date'],
                    'method' => $_POST['payment_method'],
                    'reference' => trim($_POST['reference']),
                    'asset_account_id' => $_POST['asset_account_id'],
                    'ar_account_id' => $_POST['ar_account_id'],
                    // Cheque Specific
                    'cheque_bank' => trim($_POST['cheque_bank'] ?? ''),
                    'cheque_number' => trim($_POST['cheque_number'] ?? ''),
                    'cheque_date' => $_POST['cheque_date'] ?? ''
                ];

                if ($paymentData['amount'] > 0 && !empty($paymentData['asset_account_id'])) {
                    if ($this->customerModel->recordPayment($paymentData, $_SESSION['user_id'])) {
                        $this->logActivity('Record Payment', 'Billing', "Recorded payment of Rs: " . number_format($paymentData['amount'], 2) . " for Customer ID {$paymentData['customer_id']} via {$paymentData['method']}");
                        header('Location: ' . APP_URL . '/customer/index/' . $paymentData['customer_id'] . '?success=payment'); exit;
                    } else { $data['error'] = 'Failed to process payment double-entry logic.'; }
                } else { $data['error'] = 'Invalid payment amount or missing ledger accounts.'; }
            }
        }

        if (isset($_GET['success'])) {
            if ($_GET['success'] == 'payment') {
                $data['success'] = "Payment recorded and ledger updated!";
            } elseif ($_GET['success'] == 'add') {
                $data['success'] = "New customer profile registered successfully!";
            } else {
                $data['success'] = "Customer profile updated successfully!";
            }
        }

        $this->view('layouts/main', $data);
    }
}