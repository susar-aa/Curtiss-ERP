<?php
class PaymentController extends Controller {
    private $paymentModel;
    private $customerModel;
    private $supplierModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->paymentModel = $this->model('Payment');
        $this->customerModel = $this->model('Customer');
        $this->supplierModel = $this->model('Supplier');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $customers = $this->paymentModel->getCustomerOutstandingList();
        $suppliers = $this->paymentModel->getSupplierOutstandingList();
        $accounts = $this->coaModel->getAccounts() ?: [];

        // Filter Asset accounts (e.g. Cash/Bank)
        $assets = array_filter($accounts, function($a) {
            return $a->account_type == 'Asset';
        });

        // Find default Accounts Receivable account (Code 1200 or similar name)
        $arAccount = null;
        foreach ($accounts as $acc) {
            if ($acc->account_code === '1200' || strpos(strtolower($acc->account_name), 'receivable') !== false) {
                $arAccount = $acc;
                break;
            }
        }

        // Find default Accounts Payable account (Code 2000 or similar name)
        $apAccount = null;
        foreach ($accounts as $acc) {
            if ($acc->account_code === '2000' || strpos(strtolower($acc->account_name), 'payable') !== false) {
                $apAccount = $acc;
                break;
            }
        }

        // Fetch unified payment history
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'method' => $_GET['method'] ?? '',
            'limit' => 100,
            'offset' => 0
        ];
        $paymentsHistory = $this->paymentModel->getUnifiedPaymentHistory($filters);

        $data = [
            'title' => 'Payment Center',
            'content_view' => 'payments/index',
            'customers' => $customers,
            'suppliers' => $suppliers,
            'assets' => $assets,
            'ar_account' => $arAccount,
            'ap_account' => $apAccount,
            'payments_history' => $paymentsHistory,
            'filters' => $filters,
            'error' => '',
            'success' => ''
        ];

        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'customer_payment') {
                $data['success'] = 'Customer payment recorded successfully!';
            } elseif ($_GET['success'] === 'supplier_payment') {
                $data['success'] = 'Supplier payment recorded successfully!';
            } elseif ($_GET['success'] === 'reversed') {
                $data['success'] = 'Payment reversed successfully and ledger updated!';
            } elseif ($_GET['success'] === 'credit_applied') {
                $data['success'] = 'Available credit balance successfully applied to unpaid invoices!';
            }
        }

        if (isset($_GET['error'])) {
            $data['error'] = htmlspecialchars($_GET['error']);
        }

        $this->view('layouts/main', $data);
    }

    /**
     * API to fetch customer unpaid invoices in JSON
     */
    public function getCustomerInvoicesJson($customerId) {
        header('Content-Type: application/json');
        $invoices = $this->paymentModel->getCustomerUnpaidInvoices(intval($customerId));
        echo json_encode(array_values($invoices));
        exit;
    }

    /**
     * API to fetch supplier unpaid GRNs in JSON
     */
    public function getSupplierGRNsJson($supplierId) {
        header('Content-Type: application/json');
        $grns = $this->paymentModel->getSupplierUnpaidGRNs(intval($supplierId));
        echo json_encode(array_values($grns));
        exit;
    }

    /**
     * Record customer payment
     */
    public function recordCustomerPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/payment');
            exit;
        }

        $paymentData = [
            'customer_id' => intval($_POST['customer_id'] ?? 0),
            'amount' => floatval($_POST['amount'] ?? 0),
            'date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'method' => $_POST['payment_method'] ?? 'Cash',
            'reference' => trim($_POST['reference'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'asset_account_id' => intval($_POST['asset_account_id'] ?? 0),
            'ar_account_id' => intval($_POST['ar_account_id'] ?? 0),
            'allocation_type' => $_POST['allocation_type'] ?? 'auto',
            'allocations' => $_POST['allocations'] ?? [],
            // Cheque details
            'cheque_bank' => trim($_POST['cheque_bank'] ?? ''),
            'cheque_number' => trim($_POST['cheque_number'] ?? ''),
            'cheque_date' => $_POST['cheque_date'] ?? ''
        ];

        if ($paymentData['amount'] <= 0) {
            header('Location: ' . APP_URL . '/payment?error=Payment amount must be greater than zero.');
            exit;
        }

        if (empty($paymentData['customer_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Please select a customer.');
            exit;
        }

        if (empty($paymentData['asset_account_id']) || empty($paymentData['ar_account_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Ledger accounts must be specified.');
            exit;
        }

        if ($paymentData['method'] === 'Cheque' && (empty($paymentData['cheque_bank']) || empty($paymentData['cheque_number']) || empty($paymentData['cheque_date']))) {
            header('Location: ' . APP_URL . '/payment?error=Cheque details are required.');
            exit;
        }

        $paymentId = $this->paymentModel->recordCustomerPayment($paymentData, $_SESSION['user_id']);
        if ($paymentId) {
            $this->logActivity('Record Customer Payment', 'Payments', "Recorded customer payment of Rs: " . number_format($paymentData['amount'], 2) . " for Customer ID {$paymentData['customer_id']} via {$paymentData['method']}");
            header('Location: ' . APP_URL . '/payment?success=customer_payment');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to record payment and update ledger.');
        }
        exit;
    }

    /**
     * Record supplier payment
     */
    public function recordSupplierPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/payment');
            exit;
        }

        $paymentData = [
            'supplier_id' => intval($_POST['supplier_id'] ?? 0),
            'amount' => floatval($_POST['amount'] ?? 0),
            'date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'method' => $_POST['payment_method'] ?? 'Cash',
            'reference' => trim($_POST['reference'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'asset_account_id' => intval($_POST['asset_account_id'] ?? 0),
            'ap_account_id' => intval($_POST['ap_account_id'] ?? 0),
            'allocation_type' => $_POST['allocation_type'] ?? 'auto',
            'allocations' => $_POST['allocations'] ?? [],
            // Cheque details
            'cheque_bank' => trim($_POST['cheque_bank'] ?? ''),
            'cheque_number' => trim($_POST['cheque_number'] ?? ''),
            'cheque_date' => $_POST['cheque_date'] ?? ''
        ];

        if ($paymentData['amount'] <= 0) {
            header('Location: ' . APP_URL . '/payment?error=Payment amount must be greater than zero.');
            exit;
        }

        if (empty($paymentData['supplier_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Please select a supplier.');
            exit;
        }

        if (empty($paymentData['asset_account_id']) || empty($paymentData['ap_account_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Ledger accounts must be specified.');
            exit;
        }

        if ($paymentData['method'] === 'Cheque' && (empty($paymentData['cheque_bank']) || empty($paymentData['cheque_number']) || empty($paymentData['cheque_date']))) {
            header('Location: ' . APP_URL . '/payment?error=Cheque details are required.');
            exit;
        }

        $paymentId = $this->paymentModel->recordSupplierPayment($paymentData, $_SESSION['user_id']);
        if ($paymentId) {
            $this->logActivity('Record Supplier Payment', 'Payments', "Recorded supplier payment of Rs: " . number_format($paymentData['amount'], 2) . " for Supplier ID {$paymentData['supplier_id']} via {$paymentData['method']}");
            header('Location: ' . APP_URL . '/payment?success=supplier_payment');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to record payment and update ledger.');
        }
        exit;
    }

    /**
     * Reverse Customer Payment
     */
    public function reverseCustomerPayment($id) {
        if ($this->paymentModel->reverseCustomerPayment(intval($id), $_SESSION['user_id'])) {
            $this->logActivity('Reverse Customer Payment', 'Payments', "Reversed customer payment ID: {$id}");
            header('Location: ' . APP_URL . '/payment?success=reversed');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to reverse customer payment.');
        }
        exit;
    }

    /**
     * Reverse Supplier Payment
     */
    public function reverseSupplierPayment($id) {
        if ($this->paymentModel->reverseSupplierPayment(intval($id), $_SESSION['user_id'])) {
            $this->logActivity('Reverse Supplier Payment', 'Payments', "Reversed supplier payment ID: {$id}");
            header('Location: ' . APP_URL . '/payment?success=reversed');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to reverse supplier payment.');
        }
        exit;
    }

    /**
     * Apply Customer Credit to unpaid invoices
     */
    public function applyCustomerCredit($customerId) {
        if ($this->paymentModel->settleCustomerInvoicesWithCredit(intval($customerId), $_SESSION['user_id'])) {
            $this->logActivity('Apply Customer Credit', 'Payments', "Applied available credits to invoices for Customer ID: {$customerId}");
            header('Location: ' . APP_URL . '/payment?success=credit_applied');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to apply customer credit balance or no unpaid invoices found.');
        }
        exit;
    }

    /**
     * Apply Supplier Credit to unpaid GRNs
     */
    public function applySupplierCredit($supplierId) {
        if ($this->paymentModel->settleSupplierGRNsWithCredit(intval($supplierId), $_SESSION['user_id'])) {
            $this->logActivity('Apply Supplier Credit', 'Payments', "Applied available credits to GRNs for Supplier ID: {$supplierId}");
            header('Location: ' . APP_URL . '/payment?success=credit_applied');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to apply supplier credit balance or no unpaid GRNs found.');
        }
        exit;
    }

    /**
     * Display printable receipt
     */
    public function receipt($type, $id) {
        $id = intval($id);
        $data = [];
        if ($type === 'customer') {
            $data['payment'] = $this->paymentModel->getCustomerPaymentById($id);
            $data['allocations'] = $this->paymentModel->getCustomerPaymentAllocations($id);
            $this->view('payments/customer_receipt', $data);
        } elseif ($type === 'supplier') {
            $data['payment'] = $this->paymentModel->getSupplierPaymentById($id);
            $data['allocations'] = $this->paymentModel->getSupplierPaymentAllocations($id);
            $this->view('payments/supplier_receipt', $data);
        } else {
            die("Invalid Receipt Type");
        }
    }

    /**
     * Display customer/supplier statement report
     */
    public function statement($type, $id) {
        $id = intval($id);
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $data = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type
        ];

        if ($type === 'customer') {
            $data['customer'] = $this->customerModel->getCustomerById($id);
            $data['stats'] = $this->customerModel->getCustomerStats($id);
            $data['ledger'] = $this->paymentModel->getCustomerStatement($id, $startDate, $endDate);
            $this->view('payments/customer_statement', $data);
        } elseif ($type === 'supplier') {
            $data['supplier'] = $this->supplierModel->getSupplierById($id);
            $data['stats'] = $this->supplierModel->getSupplierStats($id);
            $data['ledger'] = $this->paymentModel->getSupplierStatement($id, $startDate, $endDate);
            $this->view('payments/supplier_statement', $data);
        } else {
            die("Invalid Statement Type");
        }
    }
}
