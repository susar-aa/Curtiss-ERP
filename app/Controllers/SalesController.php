<?php
class SalesController extends Controller {
    private $customerModel;
    private $invoiceModel;
    private $coaModel;
    private $companyModel;
    private $itemModel;
    private $taxModel; // Added Tax Model

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->customerModel = $this->model('Customer');
        $this->invoiceModel = $this->model('Invoice');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->companyModel = $this->model('Company');
        $this->itemModel = $this->model('Item');
        $this->taxModel = $this->model('Tax');
    }

    public function index() {
        $data = [
            'title' => 'Sales & AR',
            'content_view' => 'sales/index',
            'invoices' => $this->invoiceModel->getAllInvoices(),
            'customers' => $this->customerModel->getAllCustomers(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_customer') {
            $custData = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];
            
            if (!empty($custData['name'])) {
                $this->customerModel->addCustomer($custData);
                $data['success'] = 'Customer added successfully!';
                $data['customers'] = $this->customerModel->getAllCustomers();
            }
        }

        $this->view('layouts/main', $data);
    }

    public function show($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        $invoice = $this->invoiceModel->getInvoiceById($id);
        if (!$invoice) {
            die("Invoice not found.");
        }

        $items = $this->invoiceModel->getInvoiceItems($id);
        $company = $this->companyModel->getSettings();

        $data = [
            'invoice' => $invoice,
            'items' => $items,
            'company' => $company
        ];

        $this->view('sales/invoice_view', $data);
    }

    public function create() {
        $accounts = $this->coaModel->getAccounts();
        $assets = array_filter($accounts, function($a) { return $a->account_type == 'Asset'; });
        $revenues = array_filter($accounts, function($a) { return $a->account_type == 'Revenue'; });
        
        // Fetch active tax rates only
        $allTaxes = $this->taxModel->getAllTaxRates();
        $activeTaxes = array_filter($allTaxes, function($t) { return $t->is_active == 1; });

        $data = [
            'title' => 'Create Invoice',
            'content_view' => 'sales/create',
            'customers' => $this->customerModel->getAllCustomers(),
            'assets' => $assets,
            'revenues' => $revenues,
            'catalog_items' => $this->itemModel->getAllItems(), 
            'taxes' => $activeTaxes,
            'invoice_number' => 'INV-' . time(),
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoiceData = [
                'customer_id' => $_POST['customer_id'] ?? '',
                'invoice_number' => $_POST['invoice_number'] ?? '',
                'date' => $_POST['invoice_date'] ?? '',
                'due_date' => $_POST['due_date'] ?? ''
            ];
            $arAccount = $_POST['ar_account'] ?? '';
            $revAccount = $_POST['revenue_account'] ?? '';
            $taxData = ['tax_rate_id' => $_POST['tax_rate_id'] ?? null];
            
            $items = [];
            if (isset($_POST['desc'])) {
                for ($i = 0; $i < count($_POST['desc']); $i++) {
                    if (!empty($_POST['desc'][$i]) && isset($_POST['qty'][$i]) && $_POST['qty'][$i] > 0 && isset($_POST['price'][$i]) && $_POST['price'][$i] >= 0) {
                        $items[] = [
                            'desc' => $_POST['desc'][$i],
                            'qty' => $_POST['qty'][$i],
                            'price' => $_POST['price'][$i]
                        ];
                    }
                }
            }

            if (empty($items)) {
                $data['error'] = 'You must add at least one item.';
            } else {
                // Pass taxData into the accounting engine
                if ($this->invoiceModel->createInvoiceWithAccounting($invoiceData, $items, $arAccount, $revAccount, $_SESSION['user_id'], $taxData)) {
                    header('Location: ' . APP_URL . '/sales?success=1');
                    exit;
                } else {
                    $data['error'] = 'Database Error: Failed to create invoice and post ledger entries.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }
}