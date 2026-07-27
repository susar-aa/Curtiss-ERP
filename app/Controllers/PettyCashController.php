<?php
declare(strict_types=1);

class PettyCashController extends Controller {
    private PettyCashTransaction $pcTxModel;
    private PettyCashConfig $pcConfigModel;
    private PettyCashReimbursement $pcReimModel;
    private ChartOfAccount $coaModel;
    private User $userModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->pcTxModel = $this->model('PettyCashTransaction');
        $this->pcConfigModel = $this->model('PettyCashConfig');
        $this->pcReimModel = $this->model('PettyCashReimbursement');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->userModel = $this->model('User');
    }

    public function index() {
        $this->checkPermission('petty_cash', 'view');

        // Fetch settings
        $config = $this->pcConfigModel->getConfig();
        $limitAmount = $config ? floatval($config->limit_amount) : 50000.00;

        // Get filters
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => trim($_GET['search'] ?? ''),
            'voucher_number' => trim($_GET['voucher_number'] ?? ''),
            'paid_to' => trim($_GET['paid_to'] ?? ''),
            'rep_route_id' => $_GET['rep_route_id'] ?? '',
            'rep_user_id' => $_GET['rep_user_id'] ?? '',
        ];

        // Pagination for transactions
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $totalTransactions = $this->pcTxModel->getTransactionsCount($filters);
        $totalPages = max(1, (int)ceil($totalTransactions / $limit));

        $transactions = $this->pcTxModel->getTransactions($filters, $limit, $offset);
        $reimbursements = $this->pcReimModel->getAllReimbursements();
        $pendingExpenses = $this->pcReimModel->getPendingExpenses();

        // Get Chart of Accounts for forms
        $accounts = $this->coaModel->getAccounts();
        
        // Filter asset accounts for funding (exclude petty cash itself)
        $pettyCashAccId = $this->pcTxModel->getPettyCashAccountId();
        $fundingAccounts = array_filter($accounts, function($a) use ($pettyCashAccId) {
            return $a->account_type == 'Asset' && (int)$a->id !== $pettyCashAccId;
        });

        // Filter expense accounts
        $expenseAccounts = array_filter($accounts, function($a) {
            return $a->account_type == 'Expense';
        });

        // Get all active daily routes for filter bar
        $db = new Database();
        $db->query("SELECT id, route_name FROM rep_daily_routes ORDER BY route_name ASC");
        $routes = $db->resultSet() ?: [];

        $data = [
            'title' => 'Petty Cash Management',
            'content_view' => 'petty_cash/index',
            'config' => $config,
            'ledger_balance' => $this->pcTxModel->getLedgerBalance(),
            'config_limit' => $limitAmount,
            'available_balance' => $this->pcTxModel->getAvailableBalance(),
            'pending_reimbursements' => $this->pcTxModel->getPendingReimbursementsTotal(),
            'transactions' => $transactions,
            'reimbursements' => $reimbursements,
            'pending_expenses' => $pendingExpenses,
            'funding_accounts' => $fundingAccounts,
            'expense_accounts' => $expenseAccounts,
            'users' => $this->userModel->getAllUsers(),
            'routes' => $routes,
            'filters' => $filters,
            'page' => $page,
            'total_pages' => $totalPages,
            'csrf_token' => $this->generateCsrfToken(),
            'error' => '',
            'success' => ''
        ];

        if (isset($_SESSION['flash_success'])) {
            $data['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            $data['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        $this->view('layouts/main', $data);
    }

    public function settings() {
        $this->checkPermission('petty_cash', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $settingsData = [
                'limit_amount' => floatval($_POST['limit_amount'] ?? 0),
                'custodian_id' => intval($_POST['custodian_id'] ?? 0),
                'require_approval' => 0,
                'default_funding_account_id' => intval($_POST['default_funding_account_id'] ?? 0),
                'reimbursement_threshold' => floatval($_POST['reimbursement_threshold'] ?? 0)
            ];

            if ($settingsData['custodian_id'] <= 0) {
                $config = $this->pcConfigModel->getConfig();
                $settingsData['custodian_id'] = ($config && isset($config->custodian_id) && $config->custodian_id > 0)
                    ? intval($config->custodian_id)
                    : intval($_SESSION['user_id'] ?? 1);
            }

            if ($settingsData['limit_amount'] <= 0) {
                $_SESSION['flash_error'] = 'Limit amount must be greater than zero.';
            } elseif ($settingsData['default_funding_account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a default funding account.';
            } else {
                if ($this->pcConfigModel->updateConfig($settingsData, (int)$_SESSION['user_id'])) {
                    $_SESSION['flash_success'] = 'Petty Cash configuration updated successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to update configuration.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function allocate() {
        $this->checkPermission('petty_cash', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $allocationData = [
                'amount' => floatval($_POST['amount'] ?? 0),
                'bank_account_id' => intval($_POST['bank_account_id'] ?? 0),
                'transaction_date' => $_POST['transaction_date'] ?? date('Y-m-d'),
                'description' => trim($_POST['description'] ?? ''),
                'reference' => trim($_POST['reference'] ?? '')
            ];

            if ($allocationData['amount'] <= 0) {
                $_SESSION['flash_error'] = 'Allocation amount must be greater than zero.';
            } elseif ($allocationData['bank_account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a source bank/cash account.';
            } else {
                $result = $this->pcTxModel->recordAllocation($allocationData, (int)$_SESSION['user_id']);
                if ($result === true) {
                    $_SESSION['flash_success'] = 'Funds allocated to Petty Cash successfully.';
                } else {
                    $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to record allocation.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function expense() {
        $this->checkPermission('petty_cash', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $attachmentPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $fileName = time() . '_' . basename($_FILES['attachment']['name']);
                $targetDir = '../public/uploads/petty_cash/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    $attachmentPath = 'uploads/petty_cash/' . $fileName;
                }
            }

            $expenseData = [
                'amount' => floatval($_POST['amount'] ?? 0),
                'account_id' => intval($_POST['account_id'] ?? 0),
                'transaction_date' => $_POST['transaction_date'] ?? date('Y-m-d'),
                'description' => trim($_POST['description'] ?? ''),
                'paid_to' => trim($_POST['paid_to'] ?? ''),
                'reference' => trim($_POST['reference'] ?? ''),
                'attachment_path' => $attachmentPath
            ];

            if ($expenseData['amount'] <= 0) {
                $_SESSION['flash_error'] = 'Expense amount must be greater than zero.';
            } elseif ($expenseData['account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a valid expense category account.';
            } else {
                $result = $this->pcTxModel->recordExpense($expenseData, (int)$_SESSION['user_id']);
                if ($result === true) {
                    $_SESSION['flash_success'] = 'Expense recorded successfully.';
                } else {
                    $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to record expense.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function approve_expense($id) {
        $this->checkPermission('petty_cash', 'create_edit');
        
        $result = $this->pcTxModel->approveExpense(intval($id), (int)$_SESSION['user_id']);
        if ($result === true) {
            $_SESSION['flash_success'] = 'Petty Cash expense approved and journal entries posted successfully.';
        } else {
            $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to approve expense.';
        }
        
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function reject_expense($id) {
        $this->checkPermission('petty_cash', 'create_edit');
        
        $result = $this->pcTxModel->rejectExpense(intval($id), (int)$_SESSION['user_id']);
        if ($result === true) {
            $_SESSION['flash_success'] = 'Petty Cash expense rejected.';
        } else {
            $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to reject expense.';
        }
        
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function reimburse() {
        $this->checkPermission('petty_cash', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrDie();

            $reimburseData = [
                'bank_account_id' => intval($_POST['bank_account_id'] ?? 0),
                'reimbursement_date' => $_POST['reimbursement_date'] ?? date('Y-m-d'),
                'description' => trim($_POST['description'] ?? '')
            ];

            if ($reimburseData['bank_account_id'] <= 0) {
                $_SESSION['flash_error'] = 'Please select a bank account to fund the reimbursement.';
            } else {
                $result = $this->pcReimModel->createRequest($reimburseData, (int)$_SESSION['user_id']);
                if ($result === true) {
                    $_SESSION['flash_success'] = 'Reimbursement request generated successfully.';
                } else {
                    $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to create reimbursement request.';
                }
            }
        }
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function approve_reimbursement($id) {
        $this->checkPermission('petty_cash', 'create_edit');
        
        $result = $this->pcReimModel->approveRequest(intval($id), (int)$_SESSION['user_id']);
        if ($result === true) {
            $_SESSION['flash_success'] = 'Reimbursement request approved and disbursed successfully. Petty Cash replenished.';
        } else {
            $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to approve reimbursement request.';
        }
        
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function reject_reimbursement($id) {
        $this->checkPermission('petty_cash', 'create_edit');
        
        $result = $this->pcReimModel->rejectRequest(intval($id), (int)$_SESSION['user_id']);
        if ($result === true) {
            $_SESSION['flash_success'] = 'Reimbursement request rejected. Linked expenses unlocked.';
        } else {
            $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to reject reimbursement request.';
        }
        
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }

    public function print_voucher($id) {
        $this->checkPermission('petty_cash', 'view');
        $tx = $this->pcTxModel->getTransactionById(intval($id));
        if (!$tx) {
            die('Voucher not found.');
        }

        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $amountInWords = $this->numberToWords(floatval($tx->amount));

        $data = [
            'tx' => $tx,
            'company' => $company,
            'amount_in_words' => $amountInWords,
            'is_pdf' => false
        ];

        // Audit printed by action
        $this->logActivity('Print Voucher', 'Petty Cash', "Printed Petty Cash Voucher: " . ($tx->voucher_number ?: $tx->id), $tx->id);

        $this->view('petty_cash/print_voucher', $data);
    }

    public function download_pdf($id) {
        $this->checkPermission('petty_cash', 'view');
        $tx = $this->pcTxModel->getTransactionById(intval($id));
        if (!$tx) {
            die('Voucher not found.');
        }

        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $amountInWords = $this->numberToWords(floatval($tx->amount));

        ob_start();
        $data = [
            'tx' => $tx,
            'company' => $company,
            'amount_in_words' => $amountInWords,
            'is_pdf' => true
        ];
        $this->view('petty_cash/print_voucher', $data);
        $html = ob_get_clean();

        // Audit PDF download action
        $this->logActivity('Download Voucher PDF', 'Petty Cash', "Downloaded PDF for Petty Cash Voucher: " . ($tx->voucher_number ?: $tx->id), $tx->id);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'petty_cash_voucher_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $tx->voucher_number ?: (string)$tx->id) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    private function numberToWords(float $number): string {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen((string)$no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter].$plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] ?? $words[floor($decimal / 10) * 10] . " " . $words[$decimal % 10]) . ' Cents' : '';
        return trim(($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' Only');
    }

    public function print_reimbursement($id) {
        $this->checkPermission('petty_cash', 'view');
        $reim = $this->pcReimModel->getReimbursementById(intval($id));
        if (!$reim) {
            die('Reimbursement request not found.');
        }

        $expenses = $this->pcReimModel->getLinkedExpenses(intval($id));

        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $amountInWords = $this->numberToWords(floatval($reim->amount));

        $data = [
            'reim' => $reim,
            'expenses' => $expenses,
            'company' => $company,
            'amount_in_words' => $amountInWords,
            'is_pdf' => false
        ];

        // Audit printed action
        $this->logActivity('Print Reimbursement', 'Petty Cash', "Printed Reimbursement Voucher: REIM-" . $reim->id, $reim->id);

        $this->view('petty_cash/print_reimbursement', $data);
    }

    public function download_reimbursement_pdf($id) {
        $this->checkPermission('petty_cash', 'view');
        $reim = $this->pcReimModel->getReimbursementById(intval($id));
        if (!$reim) {
            die('Reimbursement request not found.');
        }

        $expenses = $this->pcReimModel->getLinkedExpenses(intval($id));

        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $amountInWords = $this->numberToWords(floatval($reim->amount));

        ob_start();
        $data = [
            'reim' => $reim,
            'expenses' => $expenses,
            'company' => $company,
            'amount_in_words' => $amountInWords,
            'is_pdf' => true
        ];
        $this->view('petty_cash/print_reimbursement', $data);
        $html = ob_get_clean();

        // Audit PDF download action
        $this->logActivity('Download Reimbursement PDF', 'Petty Cash', "Downloaded PDF for Reimbursement Voucher: REIM-" . $reim->id, $reim->id);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'petty_cash_reimbursement_' . $reim->id . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    public function delete($id) {
        $this->checkPermission('petty_cash', 'delete');
        
        $result = $this->pcTxModel->deleteTransaction(intval($id), (int)$_SESSION['user_id']);
        if ($result === true) {
            $_SESSION['flash_success'] = 'Transaction deleted and reversed successfully.';
        } else {
            $_SESSION['flash_error'] = is_string($result) ? $result : 'Failed to delete transaction.';
        }
        
        header('Location: ' . APP_URL . '/pettycash');
        exit;
    }
}
