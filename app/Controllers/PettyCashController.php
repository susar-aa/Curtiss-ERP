<?php
declare(strict_types=1);

class PettyCashController extends Controller {
    private object $pettyCashModel;
    private object $coaModel;
    private object $vendorModel;
    private object $userModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        // Enforce accounting permission
        $this->checkPermission('accounting');

        $this->pettyCashModel = $this->model('PettyCash');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->vendorModel = $this->model('Supplier');
        $this->userModel = $this->model('User');
    }

    /**
     * Dashboard, transaction logs and tabs
     */
    public function index(): void {
        $this->generateCsrfToken();

        $accounts = $this->coaModel->getAccounts();
        
        // Filter bank/cash accounts (assets)
        $bankCashAccounts = array_filter($accounts, function($a) {
            return $a->account_type === 'Asset' && $a->account_code !== '1020';
        });

        // Filter expense accounts
        $expenseAccounts = array_filter($accounts, function($a) {
            return $a->account_type === 'Expense';
        });

        $data = [
            'title' => 'Petty Cash Management',
            'content_view' => 'petty_cash/index',
            'summary' => $this->pettyCashModel->getSummary(),
            'recent_transactions' => $this->pettyCashModel->getRecentTransactions(10),
            'expenses' => $this->pettyCashModel->getExpenses(),
            'outstanding_expenses' => $this->pettyCashModel->getOutstandingExpenses(),
            'reimbursements' => $this->pettyCashModel->getReimbursements(),
            'config' => $this->pettyCashModel->getConfig(),
            'config_history' => $this->pettyCashModel->getConfigHistory(),
            'bank_cash_accounts' => $bankCashAccounts,
            'expense_accounts' => $expenseAccounts,
            'vendors' => $this->vendorModel->getAllVendors() ?: [],
            'users' => $this->userModel->getAllUsers() ?: [],
            'error' => $_SESSION['flash_error'] ?? '',
            'success' => $_SESSION['flash_success'] ?? ''
        ];

        unset($_SESSION['flash_error']);
        unset($_SESSION['flash_success']);

        $this->view('layouts/main', $data);
    }

    /**
     * Update Petty Cash configuration
     */
    public function save_config(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $configData = [
                'limit_amount' => floatval($_POST['limit_amount'] ?? 0),
                'custodian_id' => intval($_POST['custodian_id'] ?? 0),
                'require_approval' => isset($_POST['require_approval']) ? 1 : 0,
                'default_funding_account_id' => intval($_POST['default_funding_account_id'] ?? 0),
                'reimbursement_threshold' => !empty($_POST['reimbursement_threshold']) ? floatval($_POST['reimbursement_threshold']) : null
            ];

            if ($configData['limit_amount'] <= 0) {
                $_SESSION['flash_error'] = 'Limit amount must be greater than zero.';
            } elseif ($configData['custodian_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a valid responsible custodian.';
            } elseif ($configData['default_funding_account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a default funding account.';
            } else {
                if ($this->pettyCashModel->saveConfig($configData, intval($_SESSION['user_id']))) {
                    $_SESSION['flash_success'] = 'Petty Cash configuration updated successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to update configuration.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    /**
     * Record a new petty cash expense
     */
    public function record_expense(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $amount = floatval($_POST['amount'] ?? 0);
            $expenseDate = trim($_POST['expense_date'] ?? date('Y-m-d'));
            $category = trim($_POST['category'] ?? '');
            $expenseAccountId = intval($_POST['expense_account_id'] ?? 0);
            $vendorId = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;
            $description = trim($_POST['description'] ?? '');

            // Attachment upload
            $attachmentPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9_\.-]/", "", basename($_FILES['attachment']['name']));
                $targetDir = '../public/uploads/petty_cash/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    $attachmentPath = 'uploads/petty_cash/' . $fileName;
                }
            }

            if ($amount <= 0) {
                $_SESSION['flash_error'] = 'Expense amount must be greater than zero.';
            } elseif (empty($category)) {
                $_SESSION['flash_error'] = 'Expense category is required.';
            } elseif ($expenseAccountId <= 0) {
                $_SESSION['flash_error'] = 'Please select a valid expense account.';
            } elseif (empty($description)) {
                $_SESSION['flash_error'] = 'Expense description is required.';
            } else {
                // Check if category or amount is duplicate of an expense in the last 1 minute
                $db = new Database();
                $db->query("
                    SELECT COUNT(*) as cnt 
                    FROM petty_cash_expenses 
                    WHERE amount = :amt AND description = :desc AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                ");
                $db->bind(':amt', $amount);
                $db->bind(':desc', $description);
                if ($db->single()->cnt > 0) {
                    $_SESSION['flash_error'] = 'Duplicate protection: A similar expense has just been recorded.';
                    header('Location: ' . APP_URL . '/pettycash');
                    exit;
                }

                $expenseData = [
                    'expense_date' => $expenseDate,
                    'category' => $category,
                    'expense_account_id' => $expenseAccountId,
                    'amount' => $amount,
                    'vendor_id' => $vendorId,
                    'description' => $description,
                    'attachment_path' => $attachmentPath
                ];

                if ($this->pettyCashModel->recordExpense($expenseData, intval($_SESSION['user_id']))) {
                    $_SESSION['flash_success'] = 'Petty Cash expense recorded successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to record expense. Verify that it does not exceed the available petty cash balance.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    /**
     * Approve petty cash expense
     */
    public function approve_expense(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();
            $id = intval($_POST['expense_id'] ?? 0);

            if ($this->pettyCashModel->approveExpense($id, intval($_SESSION['user_id']))) {
                $_SESSION['flash_success'] = 'Petty Cash expense approved and journal entries posted successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to approve expense. Make sure the petty cash ledger has sufficient funds.';
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    /**
     * Reject petty cash expense
     */
    public function reject_expense(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();
            $id = intval($_POST['expense_id'] ?? 0);

            if ($this->pettyCashModel->rejectExpense($id, intval($_SESSION['user_id']))) {
                $_SESSION['flash_success'] = 'Petty Cash expense rejected successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to reject expense.';
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    /**
     * Reimburse petty cash
     */
    public function reimburse(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $reimburseData = [
                'reimbursement_date' => trim($_POST['reimbursement_date'] ?? date('Y-m-d')),
                'amount' => floatval($_POST['amount'] ?? 0),
                'funding_account_id' => intval($_POST['funding_account_id'] ?? 0),
                'remarks' => trim($_POST['remarks'] ?? '')
            ];

            if ($reimburseData['amount'] <= 0) {
                $_SESSION['flash_error'] = 'Reimbursement amount must be greater than zero.';
            } elseif ($reimburseData['funding_account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a valid funding account.';
            } else {
                // Prevent duplicate posting
                $db = new Database();
                $db->query("
                    SELECT COUNT(*) as cnt 
                    FROM petty_cash_reimbursements 
                    WHERE amount = :amt AND funding_account_id = :funding AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                ");
                $db->bind(':amt', $reimburseData['amount']);
                $db->bind(':funding', $reimburseData['funding_account_id']);
                if ($db->single()->cnt > 0) {
                    $_SESSION['flash_error'] = 'Duplicate protection: A similar reimbursement has just been posted.';
                    header('Location: ' . APP_URL . '/pettycash');
                    exit;
                }

                if ($this->pettyCashModel->reimburse($reimburseData, intval($_SESSION['user_id']))) {
                    $_SESSION['flash_success'] = 'Petty Cash reimbursed and ledger updated successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to process reimbursement. Verify the reimbursement does not exceed the limit.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    /**
     * Transfer funds to petty cash
     */
    public function transfer(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $transferData = [
                'transfer_date' => trim($_POST['transfer_date'] ?? date('Y-m-d')),
                'amount' => floatval($_POST['amount'] ?? 0),
                'source_account_id' => intval($_POST['source_account_id'] ?? 0),
                'remarks' => trim($_POST['remarks'] ?? '')
            ];

            if ($transferData['amount'] <= 0) {
                $_SESSION['flash_error'] = 'Transfer amount must be greater than zero.';
            } elseif ($transferData['source_account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a valid source account.';
            } else {
                if ($this->pettyCashModel->transferFunds($transferData, intval($_SESSION['user_id']))) {
                    $_SESSION['flash_success'] = 'Additional funds transferred to Petty Cash successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to transfer funds.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    /**
     * Petty Cash Ledger History
     */
    public function ledger(): void {
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'tx_type' => $_GET['tx_type'] ?? '',
            'category' => $_GET['category'] ?? ''
        ];

        // Categories list
        $db = new Database();
        $db->query("SELECT DISTINCT category FROM petty_cash_expenses WHERE category IS NOT NULL AND category != ''");
        $categories = $db->resultSet() ?: [];

        $data = [
            'title' => 'Petty Cash Ledger',
            'content_view' => 'petty_cash/ledger',
            'ledger' => $this->pettyCashModel->getLedger($filters),
            'users' => $this->userModel->getAllUsers() ?: [],
            'categories' => $categories,
            'filters' => $filters
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Print Printable Petty Cash Report
     */
    public function print_report(): void {
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'tx_type' => $_GET['tx_type'] ?? '',
            'category' => $_GET['category'] ?? ''
        ];

        $data = [
            'title' => 'Petty Cash Audit & Ledger Report',
            'ledger' => $this->pettyCashModel->getLedger($filters),
            'summary' => $this->pettyCashModel->getSummary(),
            'filters' => $filters
        ];

        $this->view('petty_cash/print', $data);
    }
}
