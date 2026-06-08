<?php
class PaymentController extends Controller {
    private $customerModel;
    private $supplierModel;
    private $coaModel;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->customerModel = $this->model('Customer');
        $this->supplierModel = $this->model('Supplier');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->db = new Database();
    }

    public function index() {
        $customers = $this->customerModel->getAllCustomers() ?: [];
        $suppliers = $this->supplierModel->getAllSuppliers() ?: [];
        $accounts = $this->coaModel->getAccounts() ?: [];

        // Filter Asset accounts (e.g., Cash/Bank)
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

        // Fetch Customer Payments
        $this->db->query("
            SELECT cp.*, c.name as customer_name, u.username as responsible_person
            FROM customer_payments cp
            JOIN customers c ON cp.customer_id = c.id
            LEFT JOIN users u ON cp.created_by = u.id
            ORDER BY cp.payment_date DESC, cp.id DESC
        ");
        $customerPayments = $this->db->resultSet() ?: [];

        // Fetch Supplier Payments (recorded in expenses with vendor_id)
        $this->db->query("
            SELECT e.*, v.name as supplier_name, u.username as responsible_person
            FROM expenses e
            JOIN vendors v ON e.vendor_id = v.id
            LEFT JOIN users u ON e.created_by = u.id
            ORDER BY e.expense_date DESC, e.id DESC
        ");
        $supplierPayments = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Payment Center',
            'content_view' => 'payments/index',
            'customers' => $customers,
            'suppliers' => $suppliers,
            'assets' => $assets,
            'ar_account' => $arAccount,
            'ap_account' => $apAccount,
            'customer_payments' => $customerPayments,
            'supplier_payments' => $supplierPayments,
            'error' => '',
            'success' => ''
        ];

        // Handle success messages from redirect
        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'customer_payment') {
                $data['success'] = 'Customer payment recorded and double-entry posted successfully!';
            } elseif ($_GET['success'] === 'supplier_payment') {
                $data['success'] = 'Supplier payment recorded and double-entry posted successfully!';
            }
        }
        if (isset($_GET['error'])) {
            $data['error'] = htmlspecialchars($_GET['error']);
        }

        $this->view('layouts/main', $data);
    }

    /**
     * Record a payment received from a customer
     */
    public function recordCustomerPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/payment');
            exit;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $paymentData = [
            'customer_id' => intval($_POST['customer_id']),
            'amount' => floatval($_POST['amount']),
            'date' => $_POST['payment_date'],
            'method' => $_POST['payment_method'],
            'reference' => trim($_POST['reference']),
            'asset_account_id' => intval($_POST['asset_account_id']),
            'ar_account_id' => intval($_POST['ar_account_id']),
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
            header('Location: ' . APP_URL . '/payment?error=Please select a valid customer.');
            exit;
        }

        if (empty($paymentData['asset_account_id']) || empty($paymentData['ar_account_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Ledger accounts (Cash/Bank and Accounts Receivable) must be selected.');
            exit;
        }

        if ($paymentData['method'] === 'Cheque' && (empty($paymentData['cheque_bank']) || empty($paymentData['cheque_number']) || empty($paymentData['cheque_date']))) {
            header('Location: ' . APP_URL . '/payment?error=All cheque details (Bank, Number, and Date) are required for Cheque payments.');
            exit;
        }

        // Process payment in Model
        if ($this->customerModel->recordPayment($paymentData, $_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/payment?success=customer_payment');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to process payment double-entry logic.');
        }
        exit;
    }

    /**
     * Record a payment made to a supplier
     */
    public function recordSupplierPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/payment');
            exit;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $paymentData = [
            'supplier_id' => intval($_POST['supplier_id']),
            'amount' => floatval($_POST['amount']),
            'date' => $_POST['payment_date'],
            'method' => $_POST['payment_method'],
            'reference' => trim($_POST['reference']),
            'asset_account_id' => intval($_POST['asset_account_id']),
            'ap_account_id' => intval($_POST['ap_account_id']),
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
            header('Location: ' . APP_URL . '/payment?error=Please select a valid supplier.');
            exit;
        }

        if (empty($paymentData['asset_account_id']) || empty($paymentData['ap_account_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Ledger accounts (Cash/Bank and Accounts Payable) must be selected.');
            exit;
        }

        if ($paymentData['method'] === 'Cheque' && (empty($paymentData['cheque_bank']) || empty($paymentData['cheque_number']) || empty($paymentData['cheque_date']))) {
            header('Location: ' . APP_URL . '/payment?error=All cheque details (Bank, Number, and Date) are required for Cheque payments.');
            exit;
        }

        // Process payment in Model
        if ($this->supplierModel->recordPayment($paymentData, $_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/payment?success=supplier_payment');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to process payment double-entry logic.');
        }
        exit;
    }
}
