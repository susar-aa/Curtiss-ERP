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

        $data = [
            'title' => $type === 'sales_order' ? 'Create Sales Order' : 'Create Bill & Invoice',
            'items' => $items,
            'catalog_items' => $items,
            'payment_terms' => $paymentTerms,
            'invoice_number' => $invoiceNumber,
            'type' => $type
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
                    $this->logActivity("{$actionText} Sales Order", 'Sales Order', "Saved Sales Order {$invoiceNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2));
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
                        $success = $invoiceModel->updateInvoiceWithAccounting(
                            $editingId,
                            $invoiceData,
                            $itemsPayload,
                            $arAccountId,
                            $revenueAccountId,
                            $_SESSION['user_id']
                        );
                        if ($success) {
                            $this->logActivity('Edit Invoice', 'Billing', "Updated and re-posted Invoice {$invoiceNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2));
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
                                header('Location: ' . APP_URL . '/RepTracking?route_id=' . $routeId);
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
                            $this->logActivity('Create Invoice', 'Billing', "Created and posted Invoice {$invoiceNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2));
                            
                            $saveAction = $_POST['save_action'] ?? 'close';
                            
                            $this->db->query("SELECT phone, name FROM customers WHERE id = :id");
                            $this->db->bind(':id', $customerId);
                            $custRow = $this->db->single();
                            $custPhone = $custRow ? $custRow->phone : '';
                            $custName = $custRow ? $custRow->name : '';

                            $routeId = !empty($_POST['rep_route_id']) ? intval($_POST['rep_route_id']) : null;

                            if ($routeId) {
                                $_SESSION['flash_success'] = "Sales Order {$invoiceNumber} successfully created!";
                                header('Location: ' . APP_URL . '/RepTracking?route_id=' . $routeId);
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

        $type = $_GET['type'] ?? null;
        if (!$type) {
            $this->db->query("SELECT id FROM sales_orders WHERE id = :id");
            $this->db->bind(':id', $id);
            if ($this->db->single()) {
                $type = 'sales_order';
            } else {
                $type = 'invoice';
            }
        }

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

        $inv = null;
        $editingItems = [];

        if ($type === 'sales_order') {
            // Fetch Sales Order
            $this->db->query("SELECT * FROM sales_orders WHERE id = :id");
            $this->db->bind(':id', $id);
            $order = $this->db->single();
            if ($order) {
                // Map Sales Order fields to Invoice fields for the unified view
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
            die("Record not found.");
        }

        // Fetch all payment terms
        $termModel = $this->model('PaymentTerm');
        $paymentTerms = $termModel->getAllTerms();

        $data = [
            'title' => ($type === 'sales_order' || (isset($inv->stock_status) && $inv->stock_status === 'reserved')) ? 'Edit Sales Order' : 'Edit Bill & Invoice',
            'catalog_items' => $items,
            'items' => $items,
            'payment_terms' => $paymentTerms,
            'editing_invoice' => $inv,
            'editing_items' => $editingItems,
            'invoice_number' => $inv->invoice_number,
            'type' => $type
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