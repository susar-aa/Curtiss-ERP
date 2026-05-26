<?php

class SalesOrderController extends Controller {
    private $customerModel;
    private $itemModel;
    private $companyModel;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }

        $this->customerModel = $this->model('Customer');
        $this->itemModel = $this->model('Item');
        $this->companyModel = $this->model('Company');
        $this->db = new Database();
        
        $this->ensureSalesOrderTablesExist();
    }

    /**
     * Self-healing migration to support Sales Order tables
     */
    private function ensureSalesOrderTablesExist() {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS sales_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                customer_id INT NOT NULL,
                customer_name VARCHAR(150) NOT NULL,
                customer_phone VARCHAR(50) NULL,
                billing_type ENUM('retail', 'wholesale') NOT NULL DEFAULT 'retail',
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                notes TEXT NULL,
                rep_name VARCHAR(100) NULL,
                mca VARCHAR(100) NULL,
                rep_tp VARCHAR(50) NULL,
                po_number VARCHAR(50) NULL,
                order_date DATE NOT NULL,
                due_date DATE NOT NULL,
                payment_term_id INT NULL DEFAULT NULL,
                status VARCHAR(50) DEFAULT 'Pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $this->db->execute();

            $this->db->query("CREATE TABLE IF NOT EXISTS sales_order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sales_order_id INT NOT NULL,
                item_id INT NOT NULL,
                variation_option_id INT NULL DEFAULT NULL,
                sku VARCHAR(100) NOT NULL,
                name VARCHAR(255) NOT NULL,
                billing_price DECIMAL(10,2) NOT NULL,
                qty INT NOT NULL,
                discount_value DECIMAL(10,2) DEFAULT 0.00,
                discount_type VARCHAR(10) DEFAULT 'Rs',
                total DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE
            )");
            $this->db->execute();

            // Self-healing migration for sales_orders payment_term_id
            $this->db->query("SHOW COLUMNS FROM sales_orders LIKE 'payment_term_id'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE sales_orders ADD COLUMN payment_term_id INT NULL DEFAULT NULL AFTER due_date");
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Fallback silently
        }
    }

    public function index() {
        header('Location: ' . APP_URL . '/salesorder/create');
        exit;
    }

    /**
     * Render the Sales Order creation view
     */
    public function create() {
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

        // Generate next sales order number
        $this->db->query("SELECT id FROM sales_orders ORDER BY id DESC LIMIT 1");
        $lastRow = $this->db->single();
        $nextId = $lastRow ? ($lastRow->id + 1) : 1;
        $orderNumber = 'SO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Fetch all payment terms
        $termModel = $this->model('PaymentTerm');
        $paymentTerms = $termModel->getAllTerms();

        $data = [
            'title' => 'Create Sales Order',
            'content_view' => 'sales_orders/create',
            'catalog_items' => $items,
            'payment_terms' => $paymentTerms,
            'order_number' => $orderNumber,
            'error' => ''
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Store Sales Order (without inventory depletion or ledger posts)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $orderNumber = trim($_POST['order_number'] ?? '');
            $customerId = intval($_POST['customer_id'] ?? 0);
            
            $this->db->query("SELECT name, phone FROM customers WHERE id = :id");
            $this->db->bind(':id', $customerId);
            $custRow = $this->db->single();
            $customerName = $custRow ? $custRow->name : 'Walk-In Customer';
            $customerPhone = $custRow ? $custRow->phone : '';

            $itemSelections = $_POST['item_selection'] ?? [];
            $qtys = $_POST['qty'] ?? [];
            $descs = $_POST['desc'] ?? [];
            $prices = $_POST['price'] ?? [];
            $discVals = $_POST['item_discount_val'] ?? [];
            $discTypes = $_POST['item_discount_type'] ?? [];

            if (empty($itemSelections)) {
                $_SESSION['flash_error'] = "Sales Order creation failed: Please select at least one item.";
                header('Location: ' . APP_URL . '/salesorder/create');
                exit;
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

                // Insert Sales Order (No Stock depletion, No ledger entries)
                $this->db->query("INSERT INTO sales_orders (order_number, customer_id, customer_name, customer_phone, billing_type, subtotal, discount, grand_total, notes, rep_name, mca, rep_tp, po_number, order_date, due_date, payment_term_id, status) 
                                  VALUES (:order_num, :cust_id, :cust_name, :cust_phone, 'wholesale', :sub, :disc, :grand, :notes, :rep, :mca, :rep_tp, :po, :o_date, :d_date, :term_id, 'Pending')");
                $this->db->bind(':order_num', $orderNumber);
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
                $this->logActivity('Create Sales Order', 'Sales Order', "Saved Sales Order {$orderNumber} for Customer ID {$customerId} totaling Rs: " . number_format($grandTotal, 2));
                $_SESSION['flash_success'] = "Sales Order {$orderNumber} successfully saved (Inventory unchanged)!";
                header('Location: ' . APP_URL . '/salesorder/show/' . $orderId);
                exit;

            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_error'] = "Sales Order Saving Error: " . $e->getMessage();
                header('Location: ' . APP_URL . '/salesorder/create');
                exit;
            }
        }
    }

    /**
     * Show / Print a Sales Order
     */
    public function show($id = null) {
        if (!$id) {
            header('Location: ' . APP_URL . '/salesorder/create');
            exit;
        }

        $this->db->query("SELECT * FROM sales_orders WHERE id = :id");
        $this->db->bind(':id', $id);
        $order = $this->db->single();
        if (!$order) {
            die("Sales Order not found.");
        }

        $this->db->query("SELECT * FROM sales_order_items WHERE sales_order_id = :id");
        $this->db->bind(':id', $id);
        $items = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Sales Order ' . $order->order_number,
            'order' => $order,
            'items' => $items,
            'company' => $this->companyModel->getSettings()
        ];

        $this->view('sales_orders/show', $data);
    }
}
