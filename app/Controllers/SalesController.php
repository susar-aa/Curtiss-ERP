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
            'invoice_number' => $invoiceNumber
        ];
        $this->view('sales/index', $data);
    }

    /**
     * Process and store invoices
     * Enforces the Wholesale Price inside calculations and persists the data.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $invoiceNumber = trim($_POST['invoice_number'] ?? '');
            $customerName = trim($_POST['customer_name'] ?? 'Walk-In Customer');
            $customerPhone = trim($_POST['customer_phone'] ?? '');
            $discount = floatval($_POST['discount'] ?? 0.00);

            // Fetch product and quantity arrays from post request
            $itemIds = $_POST['item_id'] ?? [];
            $qtys = $_POST['qty'] ?? [];

            if (empty($itemIds)) {
                $_SESSION['flash_error'] = "Invoice creation failed: Please select at least one item to bill.";
                header('Location: ' . APP_URL . '/sales/create');
                exit;
            }

            try {
                $this->db->beginTransaction();

                $subtotal = 0.00;
                $invoiceItemsPayload = [];

                foreach ($itemIds as $index => $itemId) {
                    $qty = intval($qtys[$index] ?? 1);
                    
                    // Fetch details from model
                    $item = $this->itemModel->getItemById($itemId);
                    if ($item) {
                        // Enforce wholesale pricing check with recursive retail fallbacks
                        $price = 0.00;
                        if (is_object($item)) {
                            if (isset($item->wholesale_price) && floatval($item->wholesale_price) > 0) {
                                $price = floatval($item->wholesale_price);
                            } elseif (isset($item->selling_price) && floatval($item->selling_price) > 0) {
                                $price = floatval($item->selling_price);
                            } elseif (isset($item->price) && floatval($item->price) > 0) {
                                $price = floatval($item->price);
                            } elseif (isset($item->regular_price) && floatval($item->regular_price) > 0) {
                                $price = floatval($item->regular_price);
                            }
                        } elseif (is_array($item)) {
                            if (isset($item['wholesale_price']) && floatval($item['wholesale_price']) > 0) {
                                $price = floatval($item['wholesale_price']);
                            } elseif (isset($item['selling_price']) && floatval($item['selling_price']) > 0) {
                                $price = floatval($item['selling_price']);
                            } elseif (isset($item['price']) && floatval($item['price']) > 0) {
                                $price = floatval($item['price']);
                            } elseif (isset($item['regular_price']) && floatval($item['regular_price']) > 0) {
                                $price = floatval($item['regular_price']);
                            }
                        }

                        $lineTotal = $price * $qty;
                        $subtotal += $lineTotal;

                        $invoiceItemsPayload[] = [
                            'item_id' => is_object($item) ? $item->id : $item['id'],
                            'sku' => is_object($item) ? $item->item_code : $item['item_code'],
                            'name' => is_object($item) ? $item->name : $item['name'],
                            'billing_price' => $price,
                            'qty' => $qty,
                            'total' => $lineTotal
                        ];
                    }
                }

                $grandTotal = max(0.00, $subtotal - $discount);

                // 1. Insert Sales Invoice
                $this->db->query("INSERT INTO sales_invoices (invoice_number, customer_name, customer_phone, billing_type, subtotal, discount, grand_total) 
                                  VALUES (:invoice_num, :cust_name, :cust_phone, 'wholesale', :sub, :disc, :grand)");
                $this->db->bind(':invoice_num', $invoiceNumber);
                $this->db->bind(':cust_name', $customerName);
                $this->db->bind(':cust_phone', $customerPhone);
                $this->db->bind(':sub', $subtotal);
                $this->db->bind(':disc', $discount);
                $this->db->bind(':grand', $grandTotal);
                $this->db->execute();
                
                $invoiceId = $this->db->lastInsertId();

                // 2. Insert invoice lines & update stocks
                foreach ($invoiceItemsPayload as $line) {
                    $this->db->query("INSERT INTO sales_invoice_items (invoice_id, item_id, sku, name, billing_price, qty, total) 
                                      VALUES (:inv_id, :item_id, :sku, :name, :price, :qty, :total)");
                    $this->db->bind(':inv_id', $invoiceId);
                    $this->db->bind(':item_id', $line['item_id']);
                    $this->db->bind(':sku', $line['sku']);
                    $this->db->bind(':name', $line['name']);
                    $this->db->bind(':price', $line['billing_price']);
                    $this->db->bind(':qty', $line['qty']);
                    $this->db->bind(':total', $line['total']);
                    $this->db->execute();

                    // Safely update ERP item inventory level
                    $this->db->query("UPDATE items SET qty = GREATEST(0, qty - :deduction) WHERE id = :id");
                    $this->db->bind(':deduction', $line['qty']);
                    $this->db->bind(':id', $line['item_id']);
                    $this->db->execute();
                }

                $this->db->commit();
                $_SESSION['flash_success'] = "Invoice {$invoiceNumber} created and recorded successfully!";
                header('Location: ' . APP_URL . '/sales/invoice/' . $invoiceId);
                exit;

            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_error'] = "Billing Error: " . $e->getMessage();
                header('Location: ' . APP_URL . '/sales/create');
                exit;
            }
        }
    }

    /**
     * Render the generated dynamic Invoice details
     */
    public function invoice($id) {
        $this->db->query("SELECT * FROM sales_invoices WHERE id = :id");
        $this->db->bind(':id', $id);
        $invoice = $this->db->single();

        if (!$invoice) {
            die("Invoice not found.");
        }

        $this->db->query("SELECT * FROM sales_invoice_items WHERE invoice_id = :id");
        $this->db->bind(':id', $id);
        $items = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Invoice Details - ' . $invoice->invoice_number,
            'invoice' => $invoice,
            'items' => $items
        ];
        $this->view('sales/invoice', $data);
    }
}