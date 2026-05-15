<?php
class SalesController extends Controller {
    private $invoiceModel;
    private $customerModel;
    private $itemModel;
    private $coaModel;
    private $taxModel;
    private $companyModel;

    public function __construct() {
        // Global Auth removed here so customers can view public invoice links. 
        // Strict auth is applied inside index() and create() below.
        $this->invoiceModel = $this->model('Invoice');
        $this->customerModel = $this->model('Customer');
        $this->itemModel = $this->model('Item');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->taxModel = $this->model('Tax');
        $this->companyModel = $this->model('Company');
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }

        $data = [
            'title' => 'Sales & Invoicing',
            'content_view' => 'sales/index',
            'catalog_items' => $this->itemModel->getAllItems(),
            'customers' => $this->customerModel->getAllCustomers(),
            'invoice_number' => 'INV-' . time(),
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? ''
        ];
        
        $this->view('layouts/main', $data);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoiceData = [
                'customer_id' => $_POST['customer_id'] ?? '',
                'invoice_number' => $_POST['invoice_number'] ?? '',
                'date' => $_POST['invoice_date'] ?? '',
                'due_date' => $_POST['due_date'] ?? '',
                'global_discount_val' => floatval($_POST['global_discount_val'] ?? 0),
                'global_discount_type' => $_POST['global_discount_type'] ?? 'Rs',
                'po_number' => trim($_POST['po_number'] ?? ''),
                'terms' => trim($_POST['terms'] ?? ''),
                'rep_name' => trim($_POST['rep_name'] ?? ''),
                'mca' => trim($_POST['mca'] ?? ''),
                'rep_tp' => trim($_POST['rep_tp'] ?? ''),
                'customer_message' => trim($_POST['customer_message'] ?? ''),
                'notes' => trim($_POST['notes'] ?? '')
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
                            'price' => $_POST['price'][$i],
                            'disc_val' => floatval($_POST['item_discount_val'][$i] ?? 0),
                            'disc_type' => $_POST['item_discount_type'][$i] ?? 'Rs',
                            'item_selection' => $_POST['item_selection'][$i] ?? null
                        ];
                    }
                }
            }

            if (empty($items)) {
                header('Location: ' . APP_URL . '/sales?error=You must add at least one item to the invoice.');
                exit;
            } else {
                $invoiceId = $this->invoiceModel->createInvoiceWithAccounting($invoiceData, $items, $arAccount, $revAccount, $_SESSION['user_id'], $taxData);
                
                if ($invoiceId) {
                    if (isset($_POST['save_action']) && $_POST['save_action'] == 'whatsapp') {
                        $cust = $this->customerModel->getCustomerById($invoiceData['customer_id']);
                        $phone = !empty($cust->whatsapp) ? $cust->whatsapp : $cust->phone;
                        
                        header('Location: ' . APP_URL . '/sales?success=1&wa_id=' . $invoiceId . '&wa_phone=' . urlencode($phone) . '&wa_name=' . urlencode($cust->name));
                    } elseif (isset($_POST['save_action']) && $_POST['save_action'] == 'new') {
                        header('Location: ' . APP_URL . '/sales?success=1');
                    } elseif (isset($_POST['save_action']) && $_POST['save_action'] == 'print') {
                        header('Location: ' . APP_URL . '/sales?success=1&print_id=' . $invoiceId);
                    } else {
                        header('Location: ' . APP_URL . '/sales?success=1');
                    }
                    exit;
                } else {
                    header('Location: ' . APP_URL . '/sales?error=Database Error: Failed to create invoice and post ledger entries.');
                    exit;
                }
            }
        }
        
        header('Location: ' . APP_URL . '/sales');
        exit;
    }

    public function show($id = null) {
        // NOTE: This method is now intentionally public so WhatsApp links function perfectly.
        if (!$id) { header('Location: ' . APP_URL . '/auth/login'); exit; }

        $invoice = $this->invoiceModel->getInvoiceById($id);
        if (!$invoice) { die("Invoice not found."); }

        $data = [
            'invoice' => $invoice,
            'items' => $this->invoiceModel->getInvoiceItems($id),
            'company' => $this->companyModel->getSettings()
        ];

        $this->view('sales/invoice_view', $data);
    }
}