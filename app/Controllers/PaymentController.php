<?php
class PaymentController extends Controller {
    private $paymentModel;
    private $supplierModel;
    private $coaModel;
    private $serviceProviderModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->paymentModel = $this->model('Payment');
        $this->supplierModel = $this->model('Supplier');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->serviceProviderModel = $this->model('ServiceProvider');
    }

    public function index() {
        $this->checkPermission('supplierpayment', 'view');

        $suppliers = $this->paymentModel->getSupplierOutstandingList();
        $serviceProviders = $this->paymentModel->getServiceProviderOutstandingList();
        $accounts = $this->coaModel->getAccounts() ?: [];

        // Filter Asset accounts (e.g. Cash/Bank)
        $assets = array_filter($accounts, function($a) {
            return $a->account_type == 'Asset';
        });

        // Find default Accounts Payable account (Code 2000 or similar name)
        $apAccount = null;
        foreach ($accounts as $acc) {
            if ($acc->account_code === '2000' || strpos(strtolower($acc->account_name), 'payable') !== false) {
                $apAccount = $acc;
                break;
            }
        }

        // Fetch supplier payment history
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'method' => $_GET['method'] ?? '',
            'limit' => 100,
            'offset' => 0
        ];
        $paymentsHistory = $this->paymentModel->getSupplierPaymentHistory($filters);

        $data = [
            'title' => 'Payments (AP)',
            'content_view' => 'payments/index',
            'suppliers' => $suppliers,
            'service_providers' => $serviceProviders,
            'assets' => $assets,
            'ap_account' => $apAccount,
            'payments_history' => $paymentsHistory,
            'filters' => $filters,
            'error' => '',
            'success' => '',
            'payment_id' => 0,
            'payment_details' => null
        ];

        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'supplier_payment') {
                $data['success'] = 'Payment recorded successfully!';
            } elseif ($_GET['success'] === 'reversed') {
                $data['success'] = 'Payment reversed successfully and ledger updated!';
            } elseif ($_GET['success'] === 'credit_applied') {
                $data['success'] = 'Available credit balance successfully applied to unpaid GRNs!';
            }
        }

        if (isset($_GET['payment_id'])) {
            $data['payment_id'] = intval($_GET['payment_id']);
            $data['payment_details'] = $this->paymentModel->getSupplierPaymentById($data['payment_id']);
        }

        if (isset($_GET['error'])) {
            $data['error'] = htmlspecialchars($_GET['error']);
        }

        $this->view('layouts/main', $data);
    }

    /**
     * API to fetch supplier or service provider unpaid GRNs in JSON
     */
    public function getSupplierGRNsJson($id) {
        header('Content-Type: application/json');
        $type = $_GET['type'] ?? 'supplier';
        if ($type === 'service_provider') {
            $grns = $this->paymentModel->getServiceProviderUnpaidGRNs(intval($id));
        } else {
            $grns = $this->paymentModel->getSupplierUnpaidGRNs(intval($id));
        }
        echo json_encode(array_values($grns));
        exit;
    }

    /**
     * API to fetch supplier or service provider transaction/ledger history in JSON
     */
    public function getSupplierHistoryJson($id) {
        header('Content-Type: application/json');
        $type = $_GET['type'] ?? 'supplier';
        if ($type === 'service_provider') {
            $history = $this->serviceProviderModel->getActivityLedger(intval($id));
        } else {
            $history = $this->supplierModel->getActivityLedger(intval($id));
        }
        echo json_encode($history);
        exit;
    }

    /**
     * API to fetch payment details and allocations by ID
     */
    public function getPaymentDetailsJson($id) {
        header('Content-Type: application/json');
        $payment = $this->paymentModel->getSupplierPaymentById(intval($id));
        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'Payment record not found.']);
            exit;
        }
        $allocations = $this->paymentModel->getSupplierPaymentAllocations(intval($id));
        echo json_encode([
            'success' => true,
            'payment' => $payment,
            'allocations' => $allocations
        ]);
        exit;
    }

    /**
     * Record payment
     */
    public function recordSupplierPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/payment');
            exit;
        }

        $reference = trim($_POST['reference'] ?? '');
        if (empty($reference)) {
            $db = new Database();
            $db->query("SELECT id FROM supplier_payments ORDER BY id DESC LIMIT 1");
            $lastRow = $db->single();
            $nextId = $lastRow ? ($lastRow->id + 1) : 1;
            $reference = 'PV-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        }

        $entityType = $_POST['entity_type'] ?? 'supplier';
        $entityId = intval($_POST['entity_id'] ?? 0);

        $paymentData = [
            'supplier_id' => $entityType === 'supplier' ? $entityId : 0,
            'service_provider_id' => $entityType === 'service_provider' ? $entityId : 0,
            'amount' => floatval($_POST['amount'] ?? 0),
            'date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'method' => $_POST['payment_method'] ?? 'Cash',
            'reference' => $reference,
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

        if (empty($paymentData['supplier_id']) && empty($paymentData['service_provider_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Please select a supplier or service provider.');
            exit;
        }

        if (empty($paymentData['asset_account_id']) || empty($paymentData['ap_account_id'])) {
            header('Location: ' . APP_URL . '/payment?error=Ledger accounts must be specified.');
            exit;
        }

        if ($paymentData['method'] === 'Cheque') {
            if (empty($paymentData['cheque_bank']) || empty($paymentData['cheque_number']) || empty($paymentData['cheque_date'])) {
                header('Location: ' . APP_URL . '/payment?error=Cheque details are required.');
                exit;
            }
            if (!preg_match('/^\d{6}$/', $paymentData['cheque_number'])) {
                header('Location: ' . APP_URL . '/payment?error=Cheque number must be exactly 6 numeric digits.');
                exit;
            }
        }

        $paymentId = $this->paymentModel->recordSupplierPayment($paymentData, $_SESSION['user_id']);
        if ($paymentId) {
            $logEntity = $entityType === 'supplier' ? "Supplier ID {$paymentData['supplier_id']}" : "Service Provider ID {$paymentData['service_provider_id']}";
            $this->logActivity('Record Payment', 'Payments', "Recorded payment of Rs: " . number_format($paymentData['amount'], 2) . " for {$logEntity} via {$paymentData['method']}");
            header('Location: ' . APP_URL . '/payment?success=supplier_payment&payment_id=' . $paymentId);
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to record payment and update ledger.');
        }
        exit;
    }

    /**
     * Reverse Payment
     */
    public function reverseSupplierPayment($id) {
        if ($this->paymentModel->reverseSupplierPayment(intval($id), $_SESSION['user_id'])) {
            $this->logActivity('Reverse Payment', 'Payments', "Reversed payment ID: {$id}");
            header('Location: ' . APP_URL . '/payment?success=reversed');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to reverse payment.');
        }
        exit;
    }

    /**
     * Apply Credit to unpaid GRNs
     */
    public function applyCredit($id) {
        $type = $_GET['type'] ?? 'supplier';
        if ($type === 'service_provider') {
            $success = $this->paymentModel->settleServiceProviderGRNsWithCredit(intval($id), $_SESSION['user_id']);
            $logEntity = "Service Provider ID: {$id}";
        } else {
            $success = $this->paymentModel->settleSupplierGRNsWithCredit(intval($id), $_SESSION['user_id']);
            $logEntity = "Supplier ID: {$id}";
        }

        if ($success) {
            $this->logActivity('Apply Credit', 'Payments', "Applied advance credit balance for {$logEntity}");
            header('Location: ' . APP_URL . '/payment?success=credit_applied');
        } else {
            header('Location: ' . APP_URL . '/payment?error=Failed to apply credit or no outstanding balance exists.');
        }
        exit;
    }

    /**
     * Generate Receipt/Voucher print view
     */
    public function receipt($id) {
        $payment = $this->paymentModel->getSupplierPaymentById(intval($id));
        if (!$payment) {
            die('Payment record not found.');
        }
        $allocations = $this->paymentModel->getSupplierPaymentAllocations(intval($id));

        $data = [
            'title' => 'Payment Voucher - ' . $payment->reference,
            'payment' => $payment,
            'allocations' => $allocations
        ];
        $this->view('payments/supplier_receipt', $data);
    }

    /**
     * Generate Statement print view
     */
    public function statement($id) {
        $type = $_GET['type'] ?? 'supplier';
        if ($type === 'service_provider') {
            $counterparty = $this->serviceProviderModel->getServiceProviderById(intval($id));
            if (!$counterparty) {
                die('Service Provider not found.');
            }
            $title = 'Service Provider Statement - ' . $counterparty->name;
            $statement = $this->paymentModel->getServiceProviderStatement(intval($id), $_GET['start_date'] ?? '', $_GET['end_date'] ?? '');
            $statsObj = $this->serviceProviderModel->getServiceProviderStats(intval($id));
        } else {
            $counterparty = $this->supplierModel->getSupplierById(intval($id));
            if (!$counterparty) {
                die('Supplier not found.');
            }
            $title = 'Supplier Statement - ' . $counterparty->name;
            $statement = $this->paymentModel->getSupplierStatement(intval($id), $_GET['start_date'] ?? '', $_GET['end_date'] ?? '');
            $statsObj = $this->supplierModel->getSupplierStats(intval($id));
        }

        $stats = (object) [
            'total_invoiced' => $statsObj->total_billed,
            'total_paid' => $statsObj->total_paid + $statsObj->total_returned,
            'outstanding' => $statsObj->outstanding
        ];

        $data = [
            'title' => $title,
            'supplier' => $counterparty,
            'ledger' => $statement,
            'stats' => $stats,
            'entity_type' => $type,
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        $this->view('payments/supplier_statement', $data);
    }
}
