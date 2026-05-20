<?php
class SalesController extends Controller {
    private $invoiceModel;
    private $customerModel;
    private $itemModel;
    private $coaModel;
    private $taxModel;
    private $companyModel;

    public function __construct() {
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
            'editing_invoice' => null,
            'editing_items' => [],
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? ''
        ];
        
        $this->view('layouts/main', $data);
    }

    public function edit($id = null) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        if (!$id) { header('Location: ' . APP_URL . '/sales'); exit; }

        $invoice = $this->invoiceModel->getInvoiceById($id);
        if (!$invoice) { die("Target invoice record not found."); }

        $data = [
            'title' => 'Edit Invoice: ' . $invoice->invoice_number,
            'content_view' => 'sales/index',
            'catalog_items' => $this->itemModel->getAllItems(),
            'customers' => $this->customerModel->getAllCustomers(),
            'invoice_number' => $invoice->invoice_number,
            'editing_invoice' => $invoice,
            'editing_items' => $this->invoiceModel->getInvoiceItems($id),
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? ''
        ];
        
        $this->view('layouts/main', $data);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoiceId = $_POST['editing_invoice_id'] ?? null; 
            $customerId = $_POST['customer_id'] ?? null;
            $invoiceNumber = $_POST['invoice_number'] ?? null;
            $invoiceDate = $_POST['invoice_date'] ?? null;
            $dueDate = $_POST['due_date'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            $arAccountId = $_POST['ar_account'] ?? null;
            $revenueAccountId = $_POST['revenue_account'] ?? null;

            if (empty($_POST['item_selection'])) {
                header('Location: ' . APP_URL . '/sales?error=Cannot compile an invoice with zero lines.');
                exit;
            }

            $subtotal = 0;
            $items = [];
            foreach ($_POST['item_selection'] as $index => $itemSelection) {
                $qty = floatval($_POST['qty'][$index] ?? 0);
                $price = floatval($_POST['price'][$index] ?? 0);
                $discVal = floatval($_POST['item_discount_val'][$index] ?? 0);
                $discType = $_POST['item_discount_type'][$index] ?? 'Rs';

                $rowGross = $qty * $price;
                $rowDisc = ($discType === '%') ? ($rowGross * $discVal / 100) : $discVal;
                $rowNet = $rowGross - $rowDisc;
                if($rowNet < 0) $rowNet = 0;
                
                $subtotal += $rowNet;

                $items[] = [
                    'item_selection' => $itemSelection,
                    'description' => $_POST['desc'][$index] ?? '',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_value' => $discVal,
                    'discount_type' => $discType,
                    'total' => $rowNet
                ];
            }

            $globalDiscVal = floatval($_POST['global_discount_val'] ?? 0);
            $globalDiscType = $_POST['global_discount_type'] ?? 'Rs';
            $globalDisc = ($globalDiscType === '%') ? ($subtotal * $globalDiscVal / 100) : $globalDiscVal;
            
            $grandTotal = $subtotal - $globalDisc;
            if ($grandTotal < 0) $grandTotal = 0;

            $invoiceData = [
                'customer_id' => $customerId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'notes' => $notes,
                'subtotal' => $subtotal,
                'global_discount_val' => $globalDiscVal,
                'global_discount_type' => $globalDiscType,
                'grand_total' => $grandTotal
            ];

            $userId = $_SESSION['user_id'];

            if ($invoiceId) {
                try {
                    $updated = $this->invoiceModel->updateInvoiceWithAccounting($invoiceId, $invoiceData, $items, $arAccountId, $revenueAccountId, $userId);
                    if ($updated) {
                        header('Location: ' . APP_URL . '/sales/edit/' . $invoiceId . '?success=1');
                    } else {
                        $errorMsg = isset($_SESSION['invoice_error']) ? $_SESSION['invoice_error'] : 'Failed to commit changes safely.';
                        unset($_SESSION['invoice_error']); // clear single-use session diagnostic
                        header('Location: ' . APP_URL . '/sales/edit/' . $invoiceId . '?error=' . urlencode($errorMsg));
                    }
                } catch (Throwable $t) {
                    header('Location: ' . APP_URL . '/sales/edit/' . $invoiceId . '?error=' . urlencode("Controller Exception: " . $t->getMessage() . " in " . $t->getFile() . " on line " . $t->getLine()));
                }
                exit;
            } else {
                try {
                    $invoiceId = $this->invoiceModel->createInvoiceWithAccounting($invoiceData, $items, $arAccountId, $revenueAccountId, $userId);
                    if ($invoiceId) {
                        $cust = $this->customerModel->getCustomerById($customerId);
                        $phone = !empty($cust->whatsapp) ? $cust->whatsapp : $cust->phone;
                        
                        if (isset($_POST['save_action']) && $_POST['save_action'] == 'whatsapp') {
                            header('Location: ' . APP_URL . '/sales?success=1&wa_id=' . $invoiceId . '&wa_phone=' . urlencode($phone) . '&wa_name=' . urlencode($cust->name));
                        } elseif (isset($_POST['save_action']) && $_POST['save_action'] == 'print') {
                            header('Location: ' . APP_URL . '/sales?success=1&print_id=' . $invoiceId);
                        } else {
                            header('Location: ' . APP_URL . '/sales?success=1');
                        }
                        exit;
                    } else {
                        $errorMsg = isset($_SESSION['invoice_error']) ? $_SESSION['invoice_error'] : 'Failed to create new record entries.';
                        unset($_SESSION['invoice_error']); // clear single-use session diagnostic
                        header('Location: ' . APP_URL . '/sales?error=' . urlencode($errorMsg));
                        exit;
                    }
                } catch (Throwable $t) {
                    header('Location: ' . APP_URL . '/sales?error=' . urlencode("Controller Exception: " . $t->getMessage() . " in " . $t->getFile() . " on line " . $t->getLine()));
                    exit;
                }
            }
        }
    }

    public function show($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        
        $invoice = $this->invoiceModel->getInvoiceById($id);
        if (!$invoice) { die("Invoice not found."); }

        $companyDetails = $this->companyModel->getCompanyDetails();

        $data = [
            'invoice' => $invoice,
            'items' => $this->invoiceModel->getInvoiceItems($id),
            'company' => $companyDetails
        ];
        $this->view('sales/invoice_view', $data);
    }
}