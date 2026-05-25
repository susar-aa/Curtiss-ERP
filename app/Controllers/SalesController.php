<?php

class SalesController extends Controller {
    private $salesModel;
    private $itemModel;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }

        // Load models safely
        $this->itemModel = $this->model('Item');
        $this->db = new Database();
        
        $this->ensureSalesTablesExist();
    }

    /**
     * Self-healing migration to support local invoice tables
     */
    private function ensureSalesTablesExist() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS sales_invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(50) NOT NULL UNIQUE,
                customer_name VARCHAR(150) NOT NULL,
                customer_phone VARCHAR(50) NULL,
                billing_type ENUM('retail', 'wholesale') NOT NULL DEFAULT 'retail',
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $this->db->execute();

            $this->db->query("CREATE TABLE IF NOT EXISTS sales_invoice_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT NOT NULL,
                item_id INT NOT NULL,
                sku VARCHAR(100) NOT NULL,
                name VARCHAR(255) NOT NULL,
                billing_price DECIMAL(10,2) NOT NULL,
                qty INT NOT NULL,
                total DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (invoice_id) REFERENCES sales_invoices(id) ON DELETE CASCADE
            )");
            $this->db->execute();
        } catch (Exception $e) {
            // Fallback silently
        }
    }

    /**
     * Display all sales invoices
     */
    public function index() {
        header('Location: ' . APP_URL . '/sales/create');
        exit;
    }

    /**
     * Render the invoice creator view.
     * Intercepts and maps the correct billing price across all standard pricing fields 
     * to keep existing UI scripts fully functional without any changes.
     */
    public function create() {
        // Fetch all items from local ERP Database
        $items = $this->itemModel->getAllItems();

        // Backend intercept: Ensure all possible price properties are completely populated
        // This solves empty price display issues if the UI refers to alternative property keys.
        foreach ($items as $key => $item) {
            $billingPrice = 0.00;

            if (is_object($item)) {
                // Enforce Wholesale (B2B) Price preference with recursive fallback chain
                if (isset($item->wholesale_price) && floatval($item->wholesale_price) > 0) {
                    $billingPrice = floatval($item->wholesale_price);
                } elseif (isset($item->selling_price) && floatval($item->selling_price) > 0) {
                    $billingPrice = floatval($item->selling_price);
                } elseif (isset($item->price) && floatval($item->price) > 0) {
                    $billingPrice = floatval($item->price);
                } elseif (isset($item->regular_price) && floatval($item->regular_price) > 0) {
                    $billingPrice = floatval($item->regular_price);
                }

                // Sync all alias properties to make sure the price is completely shown in all UI templates
                $item->selling_price   = $billingPrice;
                $item->price           = $billingPrice;
                $item->regular_price   = $billingPrice;
                $item->sale_price      = $billingPrice;
                $item->wholesale_price = $billingPrice;
                $item->b2b_price       = $billingPrice;
                $item->unit_price      = $billingPrice;
                $item->rate            = $billingPrice;
            } elseif (is_array($item)) {
                // Enforce Wholesale (B2B) Price preference with recursive fallback chain
                if (isset($item['wholesale_price']) && floatval($item['wholesale_price']) > 0) {
                    $billingPrice = floatval($item['wholesale_price']);
                } elseif (isset($item['selling_price']) && floatval($item['selling_price']) > 0) {
                    $billingPrice = floatval($item['selling_price']);
                } elseif (isset($item['price']) && floatval($item['price']) > 0) {
                    $billingPrice = floatval($item['price']);
                } elseif (isset($item['regular_price']) && floatval($item['regular_price']) > 0) {
                    $billingPrice = floatval($item['regular_price']);
                }

                // Sync all alias properties to make sure the price is completely shown in all UI templates
                $items[$key]['selling_price']   = $billingPrice;
                $items[$key]['price']           = $billingPrice;
                $items[$key]['regular_price']   = $billingPrice;
                $items[$key]['sale_price']      = $billingPrice;
                $items[$key]['wholesale_price'] = $billingPrice;
                $items[$key]['b2b_price']       = $billingPrice;
                $items[$key]['unit_price']      = $billingPrice;
                $items[$key]['rate']            = $billingPrice;
            }
        }

        // Generate next invoice number
        $this->db->query("SELECT id FROM sales_invoices ORDER BY id DESC LIMIT 1");
        $lastRow = $this->db->single();
        $nextId = $lastRow ? ($lastRow->id + 1) : 1;
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $data = [
            'title' => 'Create Bill & Invoice',
            'items' => $items,
            'catalog_items' => $items,
            'invoice_number' => $invoiceNumber
        ];
        $this->view('sales/index', $data);
    }

    /**
     * Process and store invoices
     * Enforces the Wholesale Price inside calculations and persists the data.
     */
    /**
     * Process and store invoices
     * Enforces the Wholesale Price inside calculations and persists the data using the robust ledger engine.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $invoiceNumber = trim($_POST['invoice_number'] ?? '');
            $customerId = intval($_POST['customer_id'] ?? 0);
            
            $itemSelections = $_POST['item_selection'] ?? [];
            $qtys = $_POST['qty'] ?? [];
            $descs = $_POST['desc'] ?? [];
            $prices = $_POST['price'] ?? [];
            $discVals = $_POST['item_discount_val'] ?? [];
            $discTypes = $_POST['item_discount_type'] ?? [];

            if (empty($itemSelections)) {
                $_SESSION['flash_error'] = "Invoice creation failed: Please select at least one item to bill.";
                header('Location: ' . APP_URL . '/sales/create');
                exit;
            }

            // 1. Resolve Accounts Receivable (AR) Account
            $arAccountId = null;
            if (!empty($_POST['ar_account'])) {
                $arAccountId = intval($_POST['ar_account']);
            } else {
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Asset' AND (account_name LIKE '%Receivable%' OR account_code LIKE '1100%') LIMIT 1");
                $arRow = $this->db->single();
                $arAccountId = $arRow ? $arRow->id : null;
            }

            // 2. Resolve Revenue Account
            $revenueAccountId = null;
            if (!empty($_POST['revenue_account'])) {
                $revenueAccountId = intval($_POST['revenue_account']);
            } else {
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' AND (account_name LIKE '%Sales%' OR account_name LIKE '%Revenue%' OR account_code LIKE '4000%') LIMIT 1");
                $revRow = $this->db->single();
                $revenueAccountId = $revRow ? $revRow->id : null;
            }

            if (!$arAccountId || !$revenueAccountId) {
                $_SESSION['flash_error'] = "Accounting Configuration Error: AR or Sales Revenue account could not be resolved.";
                header('Location: ' . APP_URL . '/sales/create');
                exit;
            }

            try {
                $subtotal = 0.00;
                $itemsPayload = [];

                foreach ($itemSelections as $index => $compositeId) {
                    $qty = floatval($qtys[$index] ?? 1);
                    $price = floatval($prices[$index] ?? 0.00);
                    $discVal = floatval($discVals[$index] ?? 0.00);
                    $discType = $discTypes[$index] ?? 'Rs';

                    $lineGross = $qty * $price;
                    $lineDisc = ($discType === '%') ? ($lineGross * $discVal / 100) : $discVal;
                    $lineTotal = max(0.00, $lineGross - $lineDisc);

                    $subtotal += $lineTotal;

                    $itemsPayload[] = [
                        'item_selection' => $compositeId,
                        'description' => $descs[$index] ?? 'Product',
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'discount_value' => $discVal,
                        'discount_type' => $discType,
                        'total' => $lineTotal
                    ];
                }

                $globalDiscountVal = floatval($_POST['global_discount_val'] ?? 0.00);
                $globalDiscountType = $_POST['global_discount_type'] ?? 'Rs';
                $globalDiscount = ($globalDiscountType === '%') ? ($subtotal * $globalDiscountVal / 100) : $globalDiscountVal;
                $grandTotal = max(0.00, $subtotal - $globalDiscount);

                $invoiceData = [
                    'customer_id' => $customerId,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $_POST['invoice_date'] ?? date('Y-m-d'),
                    'due_date' => $_POST['due_date'] ?? date('Y-m-d'),
                    'subtotal' => $subtotal,
                    'global_discount_val' => $globalDiscountVal,
                    'global_discount_type' => $globalDiscountType,
                    'notes' => trim($_POST['notes'] ?? ''),
                    'rep_route_id' => !empty($_POST['rep_route_id']) ? intval($_POST['rep_route_id']) : null,
                    'grand_total' => $grandTotal
                ];

                // Load Invoice Model
                $invoiceModel = $this->model('Invoice');

                // Create the true Double-Entry and FIFO Stock depleted invoice!
                $invoiceId = $invoiceModel->createInvoiceWithAccounting(
                    $invoiceData,
                    $itemsPayload,
                    $arAccountId,
                    $revenueAccountId,
                    $_SESSION['user_id']
                );

                if ($invoiceId) {
                    // Check if Save & Print, Save & WhatsApp or Save & Close
                    $saveAction = $_POST['save_action'] ?? 'close';
                    
                    $this->db->query("SELECT phone, name FROM customers WHERE id = :id");
                    $this->db->bind(':id', $customerId);
                    $custRow = $this->db->single();
                    $custPhone = $custRow ? $custRow->phone : '';
                    $custName = $custRow ? $custRow->name : '';

                    if ($saveAction === 'print') {
                        header('Location: ' . APP_URL . '/sales/create?print_id=' . $invoiceId);
                    } elseif ($saveAction === 'whatsapp') {
                        header('Location: ' . APP_URL . '/sales/create?wa_id=' . $invoiceId . '&wa_phone=' . urlencode($custPhone) . '&wa_name=' . urlencode($custName));
                    } else {
                        $_SESSION['flash_success'] = "Invoice {$invoiceNumber} successfully created and posted to general ledger!";
                        header('Location: ' . APP_URL . '/sales/show/' . $invoiceId);
                    }
                    exit;
                } else {
                    throw new Exception($_SESSION['invoice_error'] ?? "Failed to save invoice.");
                }

            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Accounting Engine Error: " . $e->getMessage();
                header('Location: ' . APP_URL . '/sales/create');
                exit;
            }
        }
    }

    /**
     * Print / public view for invoices (main invoices table).
     */
    public function show($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/sales/create');
            exit;
        }

        $invoiceModel = $this->model('Invoice');
        $companyModel = $this->model('Company');
        $invoice = $invoiceModel->getInvoiceById($id);

        if (!$invoice) {
            die('Invoice not found.');
        }

        $data = [
            'invoice' => $invoice,
            'items' => $invoiceModel->getInvoiceItems($id),
            'company' => $companyModel->getSettings(),
        ];
        $this->view('sales/invoice_view', $data);
    }

    /**
     * Render the generated dynamic Invoice details
     */
    public function invoice($id) {
        $this->show($id);
    }
}