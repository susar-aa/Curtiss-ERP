<?php
class DriverBillingController extends DriverController {
    private $billingModel;
    private $routeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/driver/auth/login');
            exit;
        }
        $this->billingModel = $this->model('DriverInvoice');
        $this->routeModel = $this->model('DriverRoute');
    }

    public function shop($customerId = null) {
        if (!$customerId) {
            header('Location: ' . APP_URL . '/driver');
            exit;
        }

        $activeDelivery = $this->routeModel->getAssignedDelivery($_SESSION['user_id']);
        if (!$activeDelivery || $activeDelivery->status !== 'In Transit') {
            header('Location: ' . APP_URL . '/driver?error=No active in-transit delivery route.');
            exit;
        }

        $customer = $this->billingModel->getCustomerDetails($customerId);
        if (!$customer) {
            die("Customer not found.");
        }

        $invoices = $this->billingModel->getCustomerInvoices($customerId, $activeDelivery->rep_route_id);
        $creditBills = $this->billingModel->getCustomerCreditBills($customerId);

        // Fetch items for today's invoices
        $invoiceItems = [];
        $pendingCount = 0;
        foreach ($invoices as $invoice) {
            if ($invoice->delivery_status === 'Pending') {
                $pendingCount++;
            }
            $items = $this->billingModel->getInvoiceItems($invoice->id);
            foreach ($items as $item) {
                if (floatval($item->quantity) <= 0) continue;
                $invoiceItems[] = [
                    'item' => $item,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_id' => $invoice->id
                ];
            }
        }

        // Fetch payments made by this customer on this daily route
        $db = new Database();
        $db->query("SELECT * FROM customer_payments WHERE customer_id = :cid AND rep_route_id = :rid");
        $db->bind(':cid', $customerId);
        $db->bind(':rid', $activeDelivery->rep_route_id);
        $payments = $db->resultSet();

        // Check if delivery is fully completed for this shop
        $hasTodayInvoices = (count($invoices) > 0);
        if ($hasTodayInvoices) {
            $isDelivered = ($pendingCount === 0);
        } else {
            $isDelivered = (count($payments) > 0);
        }

        $data = [
            'title' => $isDelivered ? 'Delivery Report: ' . $customer->name : 'Shop Checklist',
            'content_view' => $isDelivered ? 'shop_delivery_report' : 'shop_delivery',
            'customer' => $customer,
            'invoices' => $invoices,
            'payments' => $payments,
            'credit_bills' => $creditBills,
            'invoice_items' => $invoiceItems,
            'active_delivery' => $activeDelivery,
            'success' => $_GET['success'] ?? '',
            'error' => $_GET['error'] ?? ''
        ];

        $this->view('layout', $data);
    }

    public function api_update_item() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true) ?: $_POST;

        $action = $postData['action'] ?? '';
        $itemId = intval($postData['item_id'] ?? 0);

        if ($itemId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Item ID.']);
            exit;
        }

        if ($action === 'update') {
            $newQty = floatval($postData['quantity'] ?? 0);
            if ($newQty <= 0) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Quantity must be positive. Use delete instead.']);
                exit;
            }

            $success = $this->billingModel->updateInvoiceItemQty($itemId, $newQty);
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Quantity updated.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity. Check stock.']);
            }
            exit;
        } elseif ($action === 'delete') {
            $success = $this->billingModel->deleteInvoiceItem($itemId);
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Product removed from invoice.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove item.']);
            }
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
    }

    public function checkout($customerId = null) {
        if (!$customerId) {
            header('Location: ' . APP_URL . '/driver');
            exit;
        }

        $activeDelivery = $this->routeModel->getAssignedDelivery($_SESSION['user_id']);
        if (!$activeDelivery || $activeDelivery->status !== 'In Transit') {
            header('Location: ' . APP_URL . '/driver?error=No active trip.');
            exit;
        }

        $customer = $this->billingModel->getCustomerDetails($customerId);
        $invoices = $this->billingModel->getCustomerInvoices($customerId, $activeDelivery->rep_route_id);
        
        $todayTotal = 0.0;
        foreach ($invoices as $inv) {
            $todayTotal += floatval($inv->true_grand_total);
        }

        $arrears = $this->billingModel->getCustomerTotalArrears($customerId, $activeDelivery->rep_route_id);

        $data = [
            'title' => 'POS Checkout Terminal',
            'content_view' => 'checkout',
            'customer' => $customer,
            'today_total' => $todayTotal,
            'arrears' => $arrears,
            'active_delivery' => $activeDelivery,
            'error' => $_GET['error'] ?? ''
        ];

        $this->view('layout', $data);
    }

    public function process_checkout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/driver');
            exit;
        }

        $customerId = intval($_POST['customer_id'] ?? 0);
        $cash = floatval($_POST['cash'] ?? 0);
        $bank = floatval($_POST['bank'] ?? 0);
        
        $cheques = [];
        $chequeTotal = 0.0;
        
        if (isset($_POST['cheque_amounts']) && is_array($_POST['cheque_amounts'])) {
            $amounts = $_POST['cheque_amounts'];
            $banks = $_POST['cheque_banks'] ?? [];
            $numbers = $_POST['cheque_numbers'] ?? [];
            $dates = $_POST['cheque_dates'] ?? [];
            
            for ($i = 0; $i < count($amounts); $i++) {
                $amt = floatval($amounts[$i]);
                if ($amt > 0) {
                    $cheques[] = [
                        'amount' => $amt,
                        'bank' => trim($banks[$i] ?? 'Unknown'),
                        'number' => trim($numbers[$i] ?? 'Unknown'),
                        'date' => trim($dates[$i] ?? date('Y-m-d'))
                    ];
                    $chequeTotal += $amt;
                }
            }
        }

        // Fallback for legacy single cheque input
        if (empty($cheques)) {
            $singleChequeAmt = floatval($_POST['cheque'] ?? 0);
            if ($singleChequeAmt > 0) {
                $cheques[] = [
                    'amount' => $singleChequeAmt,
                    'bank' => trim($_POST['cheque_bank'] ?? 'Unknown'),
                    'number' => trim($_POST['cheque_number'] ?? 'Unknown'),
                    'date' => trim($_POST['cheque_date'] ?? date('Y-m-d'))
                ];
                $chequeTotal = $singleChequeAmt;
            }
        }

        if ($customerId <= 0) {
            header('Location: ' . APP_URL . '/driver');
            exit;
        }

        $activeDelivery = $this->routeModel->getAssignedDelivery($_SESSION['user_id']);
        if (!$activeDelivery || $activeDelivery->status !== 'In Transit') {
            header('Location: ' . APP_URL . '/driver?error=No active trip.');
            exit;
        }

        $collections = [
            'cash' => $cash,
            'bank' => $bank,
            'cheques' => $cheques
        ];

        $success = $this->billingModel->checkoutShop($customerId, $activeDelivery->rep_route_id, $_SESSION['user_id'], $collections);
        if ($success) {
            header('Location: ' . APP_URL . '/driver/billing/success/' . $customerId . '/' . $activeDelivery->rep_route_id . '?cash=' . $cash . '&bank=' . $bank . '&cheque=' . $chequeTotal);
            exit;
        } else {
            header('Location: ' . APP_URL . '/driver/billing/checkout/' . $customerId . '?error=Failed to process POS checkout due to database error.');
            exit;
        }
    }

    public function success($customerId = null, $routeId = null) {
        if (!$customerId || !$routeId) {
            header('Location: ' . APP_URL . '/driver');
            exit;
        }

        $customer = $this->billingModel->getCustomerDetails($customerId);
        if (!$customer) {
            die("Customer not found.");
        }

        $cash = floatval($_GET['cash'] ?? 0);
        $bank = floatval($_GET['bank'] ?? 0);
        $cheque = floatval($_GET['cheque'] ?? 0);
        $totalCollected = $cash + $bank + $cheque;

        $data = [
            'title' => 'Checkout Successful',
            'content_view' => 'success',
            'customer' => $customer,
            'route_id' => $routeId,
            'cash' => $cash,
            'bank' => $bank,
            'cheque' => $cheque,
            'total_collected' => $totalCollected
        ];

        $this->view('layout', $data);
    }
}
