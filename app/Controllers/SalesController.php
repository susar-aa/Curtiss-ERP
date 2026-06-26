<?php

class SalesController extends Controller {
    private $salesModel;
    private $itemModel;
    private $db;

    public function __construct() {
        $isPublicInvoice = false;
        $url = explode('/', filter_var(rtrim($_GET['url'] ?? '', '/'), FILTER_SANITIZE_URL));
        if (isset($url[0]) && strtolower($url[0]) === 'sales' && isset($url[1]) && strtolower($url[1]) === 'show' && isset($url[2])) {
            $isPublicInvoice = true;
        }

        if (!isset($_SESSION['user_id']) && !$isPublicInvoice) {
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

            $this->db->query("CREATE TABLE IF NOT EXISTS deleted_invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(50) NOT NULL,
                customer_name VARCHAR(150) NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                deleted_user_name VARCHAR(100) NOT NULL,
                deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                delete_reason TEXT NOT NULL,
                record_type VARCHAR(20) NOT NULL DEFAULT 'Invoice'
            )");
            $this->db->execute();
        } catch (Exception $e) {
            // Fallback silently
        }
    }

    /**
     * Display all sales invoices with search, filtering, and pagination
     */
    public function index() {
        $limit = 10;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
        $customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        // Build base query
        $queryStr = "SELECT i.*, c.name as customer_name 
                     FROM invoices i 
                     JOIN customers c ON i.customer_id = c.id 
                     WHERE (i.stock_status IS NULL OR i.stock_status = 'deducted')";
        
        $params = [];

        if (!empty($search)) {
            $queryStr .= " AND (i.invoice_number LIKE :search OR c.name LIKE :search OR i.notes LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($startDate)) {
            $queryStr .= " AND i.invoice_date >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $queryStr .= " AND i.invoice_date <= :end_date";
            $params[':end_date'] = $endDate;
        }

        if ($customerId > 0) {
            $queryStr .= " AND i.customer_id = :customer_id";
            $params[':customer_id'] = $customerId;
        }

        if (!empty($status)) {
            $queryStr .= " AND i.status = :status";
            $params[':status'] = $status;
        }

        // Count total records for pagination
        $countQuery = str_replace("SELECT i.*, c.name as customer_name", "SELECT COUNT(*) as total", $queryStr);
        $this->db->query($countQuery);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $countRow = $this->db->single();
        $totalRecords = $countRow ? intval($countRow->total) : 0;
        $totalPages = ceil($totalRecords / $limit);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        // Fetch paginated results
        $queryStr .= " ORDER BY i.invoice_date DESC, i.id DESC LIMIT :limit OFFSET :offset";
        $this->db->query($queryStr);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $invoices = $this->db->resultSet() ?: [];

        // Fetch customers for filter dropdown
        $this->db->query("SELECT id, name FROM customers ORDER BY name ASC");
        $customers = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Invoices & Accounts Receivable',
            'invoices' => $invoices,
            'customers' => $customers,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'customer_id' => $customerId,
            'status' => $status,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'content_view' => 'sales/list'
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Render the invoice or sales order creator view.
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

        $type = $_GET['type'] ?? 'invoice';
        $invoiceNumber = '';

        if ($type === 'sales_order') {
            // Generate next sales order number
            $this->db->query("SELECT id FROM sales_orders ORDER BY id DESC LIMIT 1");
            $lastRow = $this->db->single();
            $nextId = $lastRow ? ($lastRow->id + 1) : 1;
            $invoiceNumber = 'SO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        } else {
            // Generate next invoice number
            $this->db->query("SELECT id FROM invoices ORDER BY id DESC LIMIT 1");
            $lastRow = $this->db->single();
            if (!$lastRow) {
                $this->db->query("SELECT id FROM sales_invoices ORDER BY id DESC LIMIT 1");
                $lastRow = $this->db->single();
            }
            $nextId = $lastRow ? ($lastRow->id + 1) : 1;
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        // Fetch all payment terms
        $termModel = $this->model('PaymentTerm');
        $paymentTerms = $termModel->getAllTerms();

        // Fetch active discount rules
        $discountModel = $this->model('DiscountRule');
        $activeDiscountRules = $discountModel->getActiveRules();

        // Convert From Sales Order Check
        $editingInvoice = null;
        $editingItems = [];
        $fromSalesOrderId = null;
        $fromSoRouteId = null;

        if (isset($_GET['from_so'])) {
            $fromSalesOrderId = intval($_GET['from_so']);
            $this->db->query("SELECT * FROM sales_orders WHERE id = :id");
            $this->db->bind(':id', $fromSalesOrderId);
            $order = $this->db->single();
            if ($order) {
                $editingInvoice = new stdClass();
                $editingInvoice->customer_id = $order->customer_id;
                $editingInvoice->customer_name = $order->customer_name;
                $editingInvoice->customer_phone = $order->customer_phone;
                $editingInvoice->invoice_date = date('Y-m-d');
                $editingInvoice->due_date = date('Y-m-d');
                $editingInvoice->payment_term_id = $order->payment_term_id;
                $editingInvoice->total_amount = $order->subtotal;
                $editingInvoice->global_discount_val = $order->discount;
                $editingInvoice->global_discount_type = 'Rs';
                $editingInvoice->notes = $order->notes;
                $editingInvoice->rep_name = $order->rep_name;
                $editingInvoice->mca = $order->mca;
                $editingInvoice->rep_tp = $order->rep_tp;
                $editingInvoice->po_number = $order->po_number;
                $editingInvoice->grand_total = $order->grand_total;

                $this->db->query("SELECT * FROM sales_order_items WHERE sales_order_id = :id");
                $this->db->bind(':id', $fromSalesOrderId);
                $soItems = $this->db->resultSet() ?: [];
                foreach ($soItems as $oi) {
                    $itemObj = new stdClass();
                    $itemObj->item_id = $oi->item_id;
                    $itemObj->variation_option_id = $oi->variation_option_id;
                    $itemObj->description = $oi->name;
                    $itemObj->quantity = $oi->qty;
                    $itemObj->unit_price = $oi->billing_price;
                    $itemObj->discount_value = $oi->discount_value;
                    $itemObj->discount_type = $oi->discount_type;
                    $itemObj->total = $oi->total;
                    $editingItems[] = $itemObj;
                }
            }
        } elseif (isset($_GET['from_so_route'])) {
            $fromSoRouteId = intval($_GET['from_so_route']);
            $invoiceModel = $this->model('Invoice');
            $order = $invoiceModel->getInvoiceById($fromSoRouteId);
            if ($order && isset($order->stock_status) && $order->stock_status === 'reserved') {
                $editingInvoice = $order;
                $editingInvoice->invoice_date = date('Y-m-d');
                $editingInvoice->due_date = date('Y-m-d');
                $editingItems = $invoiceModel->getInvoiceItems($fromSoRouteId);
            }
        }

        $data = [
            'title' => $type === 'sales_order' ? 'Create Sales Order' : 'Create Bill & Invoice',
            'items' => $items,
            'catalog_items' => $items,
            'payment_terms' => $paymentTerms,
            'invoice_number' => $invoiceNumber,
            'type' => $type,
            'active_discount_rules' => $activeDiscountRules,
            'editing_invoice' => $editingInvoice,
            'editing_items' => $editingItems,
            'from_sales_order_id' => $fromSalesOrderId,
            'from_so_route_id' => $fromSoRouteId
        ];
        $this->view('sales/index', $data);
    }

    /**
     * Process and store invoices or sales orders
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $invoiceNumber = trim($_POST['invoice_number'] ?? '');
            $customerId = intval($_POST['customer_id'] ?? 0);
            $type = $_POST['type'] ?? 'invoice';
            $editingId = intval($_POST['editing_invoice_id'] ?? 0);
            $repRouteId = !empty($_POST['rep_route_id']) ? intval($_POST['rep_route_id']) : null;

            // Resolve whether this record is in invoices table or sales_orders table
            $isInvoiceTable = false;
            if ($editingId > 0) {
                $this->db->query("SELECT id FROM invoices WHERE id = :id");
                $this->db->bind(':id', $editingId);
                if ($this->db->single()) {
                    $isInvoiceTable = true;
                }
            }

            // Route-assigned sales orders MUST be stored in invoices table with stock_status = 'reserved'
            $isRouteSalesOrder = ($type === 'sales_order' && ($repRouteId || $isInvoiceTable));
            
            $itemSelections = $_POST['item_selection'] ?? [];
            $qtys = $_POST['qty'] ?? [];
            $descs = $_POST['desc'] ?? [];
            $prices = $_POST['price'] ?? [];
            $discVals = $_POST['item_discount_val'] ?? [];
            $discTypes = $_POST['item_discount_type'] ?? [];

            if (empty($itemSelections)) {
                $_SESSION['flash_error'] = "Operation failed: Please select at least one item.";
                header('Location: ' . APP_URL . '/sales/create?type=' . $type . ($editingId ? '&id='.$editingId : ''));
                exit;
            }

            if ($type === 'sales_order' && !$isRouteSalesOrder) {
                // --- SALES ORDER PROCESS (No stock depletion, No ledger entries) ---
                $this->db->query("SELECT name, phone FROM customers WHERE id = :id");
                $this->db->bind(':id', $customerId);
                $custRow = $this->db->single();
                $customerName = $custRow ? $custRow->name : 'Walk-In Customer';
                $customerPhone = $custRow ? $custRow->phone : '';

                $oldValues = null;
                if ($editingId > 0) {
                    try {
                        $this->db->query("SELECT * FROM sales_orders WHERE id = :id");
                        $this->db->bind(':id', $editingId);
                        $oldOrder = $this->db->single();
                        $this->db->query("SELECT * FROM sales_order_items WHERE sales_order_id = :id");
                        $this->db->bind(':id', $editingId);
                        $oldItems = $this->db->resultSet() ?: [];
                        $oldValues = [
                            'order' => $oldOrder,
                            'items' => $oldItems
                        ];
                    } catch (Exception $e) {
                        // ignore failures in fetching old values
                    }
                }

                try {
                    $this->db->beginTransaction();

                    $subtotal = 0.00;
                    $itemsPayload = [];

                    foreach ($itemSelections as $index => $compositeId) {
                        $qty = floatval($qtys[$index] ?? 1);
                        $price = floatval($prices[$index] ?? 0.00);
                        $discVal = floatval($discVals[$index] ?? 0.00);
                        $discType = $discTypes[$index] ?? 'Rs';

                        $parts = explode('|', $compositeId);
                        $itemId = intval($parts[0] ?? 0);
                        $varId = isset($parts[1]) && $parts[1] !== 'MIX' && $parts[1] !== '0' ? intval($parts[1]) : null;

                        $lineGross = $qty * $price;
                        $lineDisc = ($discType === '%') ? ($lineGross * $discVal / 100) : $discVal;
                        $lineTotal = max(0.00, $lineGross - $lineDisc);

                        $subtotal += $lineTotal;

                        // Get SKU
                        $sku = 'ITEM';
                        $this->db->query("SELECT item_code FROM items WHERE id = :id");
                        $this->db->bind(':id', $itemId);
                        $itemRow = $this->db->single();
                        if ($itemRow) {
                            $sku = $itemRow->item_code;
                        }

                        $itemsPayload[] = [
                            'item_id' => $itemId,
                            'variation_option_id' => $varId,
                            'sku' => $sku,
                            'name' => $descs[$index] ?? 'Product',
                            'billing_price' => $price,
                            'qty' => $qty,
                            'discount_value' => $discVal,
                            'discount_type' => $discType,
                            'total' => $lineTotal
                        ];
                    }

                    $globalDiscountVal = floatval($_POST['global_discount_val'] ?? 0.00);
                    $globalDiscountType = $_POST['global_discount_type'] ?? 'Rs';
                    $globalDiscount = ($globalDiscountType === '%') ? ($subtotal * $globalDiscountVal / 100) : $globalDiscountVal;
                    $grandTotal = max(0.00, $subtotal - $globalDiscount);

                    if ($editingId > 0) {
                        // Update Sales Order
                        $this->db->query("UPDATE sales_orders SET 
                                            customer_id = :cust_id, 
                                            customer_name = :cust_name, 
                                            customer_phone = :cust_phone, 
                                            subtotal = :sub, 
                                            discount = :disc, 
                                            grand_total = :grand, 
                                            notes = :notes, 
                                            rep_name = :rep, 
                                            mca = :mca, 
                                            rep_tp = :rep_tp, 
                                            po_number = :po, 
                                            order_date = :o_date, 
                                            due_date = :d_date, 
                                            payment_term_id = :term_id 
                                          WHERE id = :id");
                        $this->db->bind(':cust_id', $customerId);
                        $this->db->bind(':cust_name', $customerName);
                        $this->db->bind(':cust_phone', $customerPhone);
                        $this->db->bind(':sub', $subtotal);
                        $this->db->bind(':disc', $globalDiscount);
                        $this->db->bind(':grand', $grandTotal);
                        $this->db->bind(':notes', $_POST['notes'] ?? '');
                        $this->db->bind(':rep', $_POST['rep_name'] ?? '');
                        $this->db->bind(':mca', $_POST['mca'] ?? '');
                        $this->db->bind(':rep_tp', $_POST['rep_tp'] ?? '');
                        $this->db->bind(':po', $_POST['po_number'] ?? '');
                        $this->db->bind(':o_date', $_POST['invoice_date'] ?? date('Y-m-d'));
                        $this->db->bind(':d_date', $_POST['due_date'] ?? date('Y-m-d'));
                        $this->db->bind(':term_id', !empty($_POST['payment_term_id']) ? intval($_POST['payment_term_id']) : null);
                        $this->db->bind(':id', $editingId);
                        $this->db->execute();

                        // Delete old items
                        $this->db->query("DELETE FROM sales_order_items WHERE sales_order_id = :so_id");
                        $this->db->bind(':so_id', $editingId);
                        $this->db->execute();

                        $orderId = $editingId;
                    } else {
                        // Insert Sales Order
                        $this->db->query("INSERT INTO sales_orders (order_number, customer_id, customer_name, customer_phone, billing_type, subtotal, discount, grand_total, notes, rep_name, mca, rep_tp, po_number, order_date, due_date, payment_term_id, status) 
                                          VALUES (:order_num, :cust_id, :cust_name, :cust_phone, 'wholesale', :sub, :disc, :grand, :notes, :rep, :mca, :rep_tp, :po, :o_date, :d_date, :term_id, 'Pending')");
                        $this->db->bind(':order_num', $invoiceNumber);
                        $this->db->bind(':cust_id', $customerId);
                        $this->db->bind(':cust_name', $customerName);
                        $this->db->bind(':cust_phone', $customerPhone);
                        $this->db->bind(':sub', $subtotal);
                        $this->db->bind(':disc', $globalDiscount);
                        $this->db->bind(':grand', $grandTotal);
                        $this->db->bind(':notes', $_POST['notes'] ?? '');
                        $this->db->bind(':rep', $_POST['rep_name'] ?? '');
                        $this->db->bind(':mca', $_POST['mca'] ?? '');
                        $this->db->bind(':rep_tp', $_POST['rep_tp'] ?? '');
                        $this->db->bind(':po', $_POST['po_number'] ?? '');
                        $this->db->bind(':o_date', $_POST['invoice_date'] ?? date('Y-m-d'));
                        $this->db->bind(':d_date', $_POST['due_date'] ?? date('Y-m-d'));
                        $this->db->bind(':term_id', !empty($_POST['payment_term_id']) ? intval($_POST['payment_term_id']) : null);
                        $this->db->execute();
                        $orderId = $this->db->lastInsertId();
                    }

                    // Insert sales order items
                    foreach ($itemsPayload as $line) {
                        $this->db->query("INSERT INTO sales_order_items (sales_order_id, item_id, variation_option_id, sku, name, billing_price, qty, discount_value, discount_type, total) 
                                          VALUES (:so_id, :item_id, :var_id, :sku, :name, :price, :qty, :disc_val, :disc_type, :total)");
                        $this->db->bind(':so_id', $orderId);
                        $this->db->bind(':item_id', $line['item_id']);
                        $this->db->bind(':var_id', $line['variation_option_id']);
                        $this->db->bind(':sku', $line['sku']);
                        $this->db->bind(':name', $line['name']);
                        $this->db->bind(':price', $line['billing_price']);
                        $this->db->bind(':qty', $line['qty']);
                        $this->db->bind(':disc_val', $line['discount_value']);
                        $this->db->bind(':disc_type', $line['discount_type']);
                        $this->db->bind(':total', $line['total']);
                        $this->db->execute();
                    }

                    $this->db->commit();
                    $actionText = $editingId > 0 ? 'Updated' : 'Created';
                    $newValues = [
                        'order' => [
                            'order_number' => $invoiceNumber,
                            'customer_id' => $customerId,
                            'customer_name' => $customerName,
                            'customer_phone' => $customerPhone,
                            'subtotal' => $subtotal,
                            'discount' => $globalDiscount,
                            'grand_total' => $grandTotal,
                            'notes' => $_POST['notes'] ?? '',
                            'rep_name' => $_POST['rep_name'] ?? '',
                            'mca' => $_POST['mca'] ?? '',
                            'rep_tp' => $_POST['rep_tp'] ?? '',
                            'po_number' => $_POST['po_number'] ?? '',
                            'order_date' => $_POST['invoice_date'] ?? date('Y-m-d'),
                            'due_date' => $_POST['due_date'] ?? date('Y-m-d'),
                            'payment_term_id' => !empty($_POST['payment_term_id']) ? intval($_POST['payment_term_id']) : null
                        ],
                        'items' => $itemsPayload
                    ];
                    $this->logActivity("{$actionText} Sales Order", 'Sales Order', "Saved Sales Order {$invoiceNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2), $orderId, $oldValues, $newValues);
                    $_SESSION['flash_success'] = "Sales Order {$invoiceNumber} successfully {$actionText} (Inventory unchanged)!";
                    header('Location: ' . APP_URL . '/salesorder/show/' . $orderId);
                    exit;

                } catch (Exception $e) {
                    $this->db->rollBack();
                    $_SESSION['flash_error'] = "Sales Order Error: " . $e->getMessage();
                    header('Location: ' . APP_URL . '/sales/create?type=sales_order' . ($editingId ? '&id='.$editingId : ''));
                    exit;
                }

            } else {
                // --- INVOICE PROCESS (Stock depleted immediately, Ledger entries posted) ---
                $arAccountId = null;
                if (!empty($_POST['ar_account'])) {
                    $arAccountId = intval($_POST['ar_account']);
                } else {
                    $this->db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Asset' AND (account_name LIKE '%Receivable%' OR account_code LIKE '1100%') LIMIT 1");
                    $arRow = $this->db->single();
                    $arAccountId = $arRow ? $arRow->id : null;
                }

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
                        'payment_term_id' => !empty($_POST['payment_term_id']) ? intval($_POST['payment_term_id']) : null,
                        'subtotal' => $subtotal,
                        'global_discount_val' => $globalDiscountVal,
                        'global_discount_type' => $globalDiscountType,
                        'notes' => trim($_POST['notes'] ?? ''),
                        'rep_route_id' => $repRouteId,
                        'grand_total' => $grandTotal,
                        'stock_status' => $isRouteSalesOrder ? 'reserved' : 'deducted'
                    ];

                    $invoiceModel = $this->model('Invoice');

                    if ($editingId > 0) {
                        $oldValues = null;
                        try {
                            $oldValues = [
                                'invoice' => $invoiceModel->getInvoiceById($editingId),
                                'items' => $invoiceModel->getInvoiceItems($editingId)
                            ];
                        } catch (Exception $e) {}

                        $success = $invoiceModel->updateInvoiceWithAccounting(
                            $editingId,
                            $invoiceData,
                            $itemsPayload,
                            $arAccountId,
                            $revenueAccountId,
                            $_SESSION['user_id']
                        );
                        if ($success) {
                            $newValues = [
                                'invoice' => $invoiceData,
                                'items' => $itemsPayload
                            ];
                            $this->logActivity('Edit Invoice', 'Billing', "Updated and re-posted Invoice {$invoiceNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2), $editingId, $oldValues, $newValues);
                            $_SESSION['flash_success'] = "Sales Order {$invoiceNumber} successfully updated!";
                            
                            $routeId = !empty($_POST['rep_route_id']) ? intval($_POST['rep_route_id']) : null;
                            if (!$routeId && $editingId > 0) {
                                $this->db->query("SELECT rep_route_id FROM invoices WHERE id = :id");
                                $this->db->bind(':id', $editingId);
                                $row = $this->db->single();
                                if ($row) {
                                    $routeId = $row->rep_route_id;
                                }
                            }
                            
                            if ($routeId) {
                                header('Location: ' . APP_URL . '/RepTracking?route_id=' . $routeId . '&filter=adjustments');
                            } else {
                                header('Location: ' . APP_URL . '/sales/show/' . $editingId);
                            }
                            exit;
                        } else {
                            throw new Exception($_SESSION['invoice_error'] ?? "Failed to update invoice.");
                        }
                    } else {
                        $invoiceId = $invoiceModel->createInvoiceWithAccounting(
                            $invoiceData,
                            $itemsPayload,
                            $arAccountId,
                            $revenueAccountId,
                            $_SESSION['user_id']
                        );

                        if ($invoiceId) {
                            $newValues = [
                                'invoice' => $invoiceData,
                                'items' => $itemsPayload
                            ];
                            $this->logActivity('Create Invoice', 'Billing', "Created and posted Invoice {$invoiceNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2), $invoiceId, null, $newValues);
                            
                            $fromSalesOrderId = intval($_POST['from_sales_order_id'] ?? 0);
                            if ($fromSalesOrderId > 0) {
                                $this->db->query("UPDATE sales_orders SET status = 'Transferred' WHERE id = :id");
                                $this->db->bind(':id', $fromSalesOrderId);
                                $this->db->execute();
                            }

                            $saveAction = $_POST['save_action'] ?? 'close';
                            
                            $this->db->query("SELECT phone, name FROM customers WHERE id = :id");
                            $this->db->bind(':id', $customerId);
                            $custRow = $this->db->single();
                            $custPhone = $custRow ? $custRow->phone : '';
                            $custName = $custRow ? $custRow->name : '';

                            $routeId = !empty($_POST['rep_route_id']) ? intval($_POST['rep_route_id']) : null;

                            if ($routeId) {
                                $_SESSION['flash_success'] = "Sales Order {$invoiceNumber} successfully created!";
                                if ($saveAction === 'new') {
                                    header('Location: ' . APP_URL . '/sales/create?type=sales_order&route_id=' . $routeId);
                                } else {
                                    header('Location: ' . APP_URL . '/RepTracking?route_id=' . $routeId . '&filter=adjustments');
                                }
                            } else if ($saveAction === 'print') {
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
                    }

                } catch (Exception $e) {
                    $_SESSION['flash_error'] = "Accounting Engine Error: " . $e->getMessage();
                    header('Location: ' . APP_URL . '/sales/create' . ($editingId ? '?id='.$editingId : ''));
                    exit;
                }
            }
        }
    }

    /**
     * Edit an Invoice or Sales Order
     */
    public function edit($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/sales/create');
            exit;
        }

        // Try to detect type: check both sales_orders and invoices tables
        $type = $_GET['type'] ?? null;
        if (isset($_GET['convert']) && $_GET['convert'] == 1) {
            $type = 'invoice';
        }
        if (!$type) {
            $this->db->query("SELECT id FROM sales_orders WHERE id = :id");
            $this->db->bind(':id', $id);
            if ($this->db->single()) {
                $type = 'sales_order';
            } else {
                $type = 'invoice';
            }
        }

        // Fetch all items for catalog
        $items = $this->itemModel->getAllItems();

        // Standardize wholesale pricing for catalog items
        foreach ($items as $key => $item) {
            $billingPrice = 0.00;
            if (is_object($item)) {
                if (isset($item->wholesale_price) && floatval($item->wholesale_price) > 0) {
                    $billingPrice = floatval($item->wholesale_price);
                } elseif (isset($item->selling_price) && floatval($item->selling_price) > 0) {
                    $billingPrice = floatval($item->selling_price);
                } elseif (isset($item->price) && floatval($item->price) > 0) {
                    $billingPrice = floatval($item->price);
                }
                $item->selling_price = $billingPrice;
                $item->price = $billingPrice;
            }
        }

        // If type is sales_order, first try the sales_orders table, then fall back to invoices
        // Route sales orders are stored in invoices table with stock_status='reserved'
        $inv = null;
        $editingItems = [];
        $actualType = $type;

        if ($type === 'sales_order') {
            // Try sales_orders table first
            $this->db->query("SELECT * FROM sales_orders WHERE id = :id");
            $this->db->bind(':id', $id);
            $order = $this->db->single();

            if ($order) {
                // Found in sales_orders table - map fields
                $inv = new stdClass();
                $inv->id = $order->id;
                $inv->invoice_number = $order->order_number;
                $inv->customer_id = $order->customer_id;
                $inv->invoice_date = $order->order_date;
                $inv->due_date = $order->due_date;
                $inv->payment_term_id = $order->payment_term_id;
                $inv->total_amount = $order->subtotal;
                $inv->global_discount_val = $order->discount;
                $inv->global_discount_type = 'Rs';
                $inv->notes = $order->notes;
                $inv->rep_name = $order->rep_name;
                $inv->mca = $order->mca;
                $inv->rep_tp = $order->rep_tp;
                $inv->po_number = $order->po_number;
                $inv->grand_total = $order->grand_total;

                // Fetch Sales Order Items
                $this->db->query("SELECT * FROM sales_order_items WHERE sales_order_id = :id");
                $this->db->bind(':id', $id);
                $orderItems = $this->db->resultSet() ?: [];
                foreach ($orderItems as $oi) {
                    $itemObj = new stdClass();
                    $itemObj->item_id = $oi->item_id;
                    $itemObj->variation_option_id = $oi->variation_option_id;
                    $itemObj->description = $oi->name;
                    $itemObj->quantity = $oi->qty;
                    $itemObj->unit_price = $oi->billing_price;
                    $itemObj->discount_value = $oi->discount_value;
                    $itemObj->discount_type = $oi->discount_type;
                    $itemObj->total = $oi->total;
                    $editingItems[] = $itemObj;
                }
            } else {
                // Not found in sales_orders - fall back to invoices table (route sales orders)
                $invoiceModel = $this->model('Invoice');
                $inv = $invoiceModel->getInvoiceById($id);
                if ($inv) {
                    $actualType = 'invoice';
                    $editingItems = $invoiceModel->getInvoiceItems($id);
                }
            }
        } else {
            // Fetch Invoice
            $invoiceModel = $this->model('Invoice');
            $inv = $invoiceModel->getInvoiceById($id);
            if ($inv) {
                $editingItems = $invoiceModel->getInvoiceItems($id);
            }
        }

        if (!$inv) {
            error_log("SalesController::edit - Record not found for ID={$id}, type={$type}, actualType={$actualType}, GET=" . json_encode($_GET));
            die("Record not found. ID: {$id}, Type: {$type}");
        }

        $type = $actualType;

        // Fetch all payment terms
        $termModel = $this->model('PaymentTerm');
        $paymentTerms = $termModel->getAllTerms();

        // Fetch active discount rules
        $discountModel = $this->model('DiscountRule');
        $activeDiscountRules = $discountModel->getActiveRules();

        $data = [
            'title' => ($type === 'sales_order' || (isset($inv->stock_status) && $inv->stock_status === 'reserved')) ? 'Edit Sales Order' : 'Edit Bill & Invoice',
            'catalog_items' => $items,
            'items' => $items,
            'payment_terms' => $paymentTerms,
            'editing_invoice' => $inv,
            'editing_items' => $editingItems,
            'invoice_number' => $inv->invoice_number,
            'type' => $type,
            'active_discount_rules' => $activeDiscountRules
        ];

        $this->view('sales/index', $data);
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

        // Calculate amount paid for this invoice
        $invoicePaid = 0;
        try {
            $db = new Database();
            $db->query("SELECT COALESCE(SUM(amount), 0) as paid FROM customer_payments WHERE invoice_id = :id");
            $db->bind(':id', $id);
            $row = $db->single();
            if ($row) {
                $invoicePaid = floatval($row->paid);
            }
        } catch (Exception $e) {
            // Table may not exist, default to 0
            $invoicePaid = 0;
        }

        $data = [
            'invoice' => $invoice,
            'items' => $invoiceModel->getInvoiceItems($id),
            'company' => $companyModel->getSettings(),
            'invoice_paid' => $invoicePaid,
        ];
        $this->view('sales/invoice_view', $data);
    }

    /**
     * Render the generated dynamic Invoice details
     */
    public function invoice($id) {
        $this->show($id);
    }

    /**
     * Delete an Invoice or Route Sales Order with administrative authentication
     */
    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        if (!$id) {
            $_SESSION['flash_error'] = "Record ID is missing!";
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        $password = $_POST['password'] ?? '';
        $reason = trim($_POST['delete_reason'] ?? '');

        if (empty($reason)) {
            $_SESSION['flash_error'] = "Deletion reason is required!";
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        // Authenticate password
        $userModel = $this->model('User');
        $username = $_SESSION['username'] ?? '';
        $user = $userModel->login($username, $password);

        if (!$user) {
            $_SESSION['flash_error'] = "Authentication failed: Incorrect password!";
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        // Verify Delete Permission
        if (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'admin') {
            $perms = $_SESSION['permissions'] ?? [];
            if (!($perms['sales']['can_delete'] ?? false)) {
                $_SESSION['flash_error'] = "Access denied: You do not have permission to delete invoices.";
                header('Location: ' . APP_URL . '/sales');
                exit;
            }
        }

        $invoiceModel = $this->model('Invoice');
        $inv = $invoiceModel->getInvoiceById($id);

        if (!$inv) {
            $_SESSION['flash_error'] = "Invoice not found!";
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        $isRouteSalesOrder = (isset($inv->stock_status) && $inv->stock_status === 'reserved');
        $recordType = $isRouteSalesOrder ? 'Sales Order' : 'Invoice';

        // Calculate true grand total for invoices (since grand_total is not a stored column in invoices table)
        $invoiceTotal = floatval($inv->total_amount);
        $discount = 0.00;
        if (floatval($inv->global_discount_val) > 0) {
            if ($inv->global_discount_type === '%') {
                $discount = ($invoiceTotal * floatval($inv->global_discount_val) / 100);
            } else {
                $discount = floatval($inv->global_discount_val);
            }
        }
        $grandTotal = $invoiceTotal - $discount + floatval($inv->tax_amount ?? 0);

        // Keep track of old values for general activity log
        $oldValues = [
            'invoice' => $inv,
            'items' => $invoiceModel->getInvoiceItems($id)
        ];

        // Perform deletion
        $success = $invoiceModel->deleteInvoiceWithAccounting($id, $_SESSION['user_id']);

        if ($success) {
            // Write to deleted_invoices audit table
            $this->db->query("INSERT INTO deleted_invoices (invoice_number, customer_name, total_amount, deleted_user_name, delete_reason, record_type) 
                              VALUES (:inv_num, :cust_name, :total, :deleted_user, :reason, :rec_type)");
            $this->db->bind(':inv_num', $inv->invoice_number);
            $this->db->bind(':cust_name', $inv->customer_name);
            $this->db->bind(':total', $grandTotal);
            $this->db->bind(':deleted_user', $_SESSION['username'] ?? 'System');
            $this->db->bind(':reason', $reason);
            $this->db->bind(':rec_type', $recordType);
            $this->db->execute();

            // Log general system activity
            $this->logActivity('Delete ' . $recordType, 'Billing', "Deleted {$recordType} {$inv->invoice_number} for customer {$inv->customer_name} totaling Rs: " . number_format($grandTotal, 2) . ". Reason: {$reason}", $id, $oldValues, null);

            $_SESSION['flash_success'] = "{$recordType} {$inv->invoice_number} deleted and stock/ledger balances reversed successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to delete. " . ($_SESSION['invoice_error'] ?? '');
        }

        $getParams = $_GET;
        unset($getParams['url']);
        $queryString = http_build_query($getParams);
        $targetList = $isRouteSalesOrder ? '/salesorder' : '/sales';
        $redirectUrl = APP_URL . $targetList;
        if (!empty($queryString)) {
            $redirectUrl .= '?' . $queryString;
        }
        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Convert an Invoice back to a Sales Order and restock the deducted inventory.
     */
    public function convert_to_so($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = "Invoice ID is missing!";
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        $invoiceModel = $this->model('Invoice');
        $inv = $invoiceModel->getInvoiceById($id);

        if (!$inv) {
            $_SESSION['flash_error'] = "Invoice not found!";
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        // Cache the invoice items before deletion
        $items = $invoiceModel->getInvoiceItems($id);

        // Fetch rep name if rep_route_id exists
        $repName = '';
        if (!empty($inv->rep_route_id)) {
            $this->db->query("SELECT CONCAT(e.first_name, ' ', e.last_name) as rep_name 
                              FROM employees e 
                              JOIN users u ON u.employee_id = e.id 
                              JOIN rep_daily_routes r ON r.user_id = u.id 
                              WHERE r.id = :route_id LIMIT 1");
            $this->db->bind(':route_id', $inv->rep_route_id);
            $repRow = $this->db->single();
            $repName = $repRow ? $repRow->rep_name : '';
        }

        // Fetch MCA
        $mca = '';
        $this->db->query("SELECT name FROM mca_areas WHERE id = (SELECT mca_id FROM customers WHERE id = :cust_id LIMIT 1) LIMIT 1");
        $this->db->bind(':cust_id', $inv->customer_id);
        $mcaRow = $this->db->single();
        $mca = $mcaRow ? $mcaRow->name : '';

        // Generate next sales order number
        $this->db->query("SELECT id FROM sales_orders ORDER BY id DESC LIMIT 1");
        $lastRow = $this->db->single();
        $nextId = $lastRow ? ($lastRow->id + 1) : 1;
        $orderNumber = 'SO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Delete invoice and reverse stock/ledgers/transactions
        $success = $invoiceModel->deleteInvoiceWithAccounting($id, $_SESSION['user_id']);

        if (!$success) {
            $_SESSION['flash_error'] = "Failed to revert invoice stock/ledgers: " . ($_SESSION['invoice_error'] ?? '');
            header('Location: ' . APP_URL . '/sales');
            exit;
        }

        try {
            $this->db->beginTransaction();

            $subtotal = floatval($inv->total_amount);
            $discount = 0.00;
            if (floatval($inv->global_discount_val) > 0) {
                if ($inv->global_discount_type === '%') {
                    $discount = ($subtotal * floatval($inv->global_discount_val) / 100);
                } else {
                    $discount = floatval($inv->global_discount_val);
                }
            }
            $grandTotal = $subtotal - $discount + floatval($inv->tax_amount ?? 0);

            // Insert into sales_orders
            $this->db->query("INSERT INTO sales_orders (order_number, customer_id, customer_name, customer_phone, billing_type, subtotal, discount, grand_total, notes, rep_name, mca, rep_tp, po_number, order_date, due_date, payment_term_id, status) 
                              VALUES (:order_num, :cust_id, :cust_name, :cust_phone, 'wholesale', :sub, :disc, :grand, :notes, :rep, :mca, :rep_tp, :po, :o_date, :d_date, :term_id, 'Pending')");
            $this->db->bind(':order_num', $orderNumber);
            $this->db->bind(':cust_id', $inv->customer_id);
            $this->db->bind(':cust_name', $inv->customer_name);
            $this->db->bind(':cust_phone', $inv->phone);
            $this->db->bind(':sub', $subtotal);
            $this->db->bind(':disc', $discount);
            $this->db->bind(':grand', $grandTotal);
            $this->db->bind(':notes', ($inv->notes ?? '') . " (Converted from Invoice {$inv->invoice_number})");
            $this->db->bind(':rep', $repName);
            $this->db->bind(':mca', $mca);
            $this->db->bind(':rep_tp', '');
            $this->db->bind(':po', '');
            $this->db->bind(':o_date', date('Y-m-d'));
            $this->db->bind(':d_date', date('Y-m-d'));
            $this->db->bind(':term_id', $inv->payment_term_id);
            $this->db->execute();

            $orderId = $this->db->lastInsertId();

            // Insert cached items into sales_order_items
            foreach ($items as $item) {
                // Get SKU
                $sku = 'ITEM';
                $this->db->query("SELECT item_code FROM items WHERE id = :id");
                $this->db->bind(':id', $item->item_id);
                $itemRow = $this->db->single();
                if ($itemRow) {
                    $sku = $itemRow->item_code;
                }

                $this->db->query("INSERT INTO sales_order_items (sales_order_id, item_id, variation_option_id, sku, name, billing_price, qty, discount_value, discount_type, total) 
                                  VALUES (:so_id, :item_id, :var_id, :sku, :name, :price, :qty, :disc_val, :disc_type, :total)");
                $this->db->bind(':so_id', $orderId);
                $this->db->bind(':item_id', $item->item_id);
                $this->db->bind(':var_id', $item->variation_option_id);
                $this->db->bind(':sku', $sku);
                $this->db->bind(':name', $item->description);
                $this->db->bind(':price', $item->unit_price);
                $this->db->bind(':qty', $item->quantity);
                $this->db->bind(':disc_val', $item->discount_value);
                $this->db->bind(':disc_type', $item->discount_type);
                $this->db->bind(':total', $item->total);
                $this->db->execute();
            }

            // Write to deleted_invoices audit table for record keeping
            $this->db->query("INSERT INTO deleted_invoices (invoice_number, customer_name, total_amount, deleted_user_name, delete_reason, record_type) 
                              VALUES (:inv_num, :cust_name, :total, :deleted_user, :reason, 'Invoice')");
            $this->db->bind(':inv_num', $inv->invoice_number);
            $this->db->bind(':cust_name', $inv->customer_name);
            $this->db->bind(':total', $grandTotal);
            $this->db->bind(':deleted_user', $_SESSION['username'] ?? 'System');
            $this->db->bind(':reason', "Converted to Sales Order {$orderNumber}");
            $this->db->execute();

            $this->db->commit();

            // Log activity
            $this->logActivity('Convert Invoice to SO', 'Billing', "Converted Invoice {$inv->invoice_number} to Sales Order {$orderNumber} (Stock added back to Inventory).", $orderId, null, null);

            $_SESSION['flash_success'] = "Invoice {$inv->invoice_number} successfully converted to Sales Order {$orderNumber}! Inventory stock has been restocked.";
            header('Location: ' . APP_URL . '/salesorder/show/' . $orderId);
            exit;

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash_error'] = "Failed to create Sales Order: " . $e->getMessage();
            header('Location: ' . APP_URL . '/sales');
            exit;
        }
    }

    /**
     * Render the deleted invoices and sales orders audit log
     */
    public function deleted_list() {
        $this->db->query("SELECT * FROM deleted_invoices ORDER BY deleted_at DESC");
        $deletedList = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Deleted Invoices Audit Log',
            'invoices' => $deletedList,
            'content_view' => 'sales/deleted_list'
        ];
        $this->view('layouts/main', $data);
    }
}