<?php
class AccountingController extends Controller {
    private $coaModel;
    private $journalModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->coaModel = $this->model('ChartOfAccount');
        $this->journalModel = $this->model('JournalEntry');
    }

    public function coa() {
        $data = [
            'title' => 'Chart of Accounts',
            'content_view' => 'accounting/coa',
            'accounts' => [],
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $postData = ['account_code' => trim($_POST['account_code']), 'account_name' => trim($_POST['account_name']), 'account_type' => trim($_POST['account_type'])];

            if (empty($postData['account_code']) || empty($postData['account_name']) || empty($postData['account_type'])) {
                $data['error'] = 'All fields are required.';
            } else {
                if ($this->coaModel->addAccount($postData)) { $data['success'] = 'Account added successfully.'; } 
                else { $data['error'] = 'Failed to add account. Check if Account Code already exists.'; }
            }
        }
        $data['accounts'] = $this->coaModel->getAccounts();
        $this->view('layouts/main', $data);
    }

    public function journal() {
        $data = ['title' => 'General Journal', 'content_view' => 'accounting/journal', 'accounts' => $this->coaModel->getAccounts(), 'entries' => $this->journalModel->getAllEntries(), 'error' => '', 'success' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $date = trim($_POST['entry_date']);
            $reference = trim($_POST['reference']);
            $description = trim($_POST['description']);
            $accounts = $_POST['account_id'];
            $debits = $_POST['debit'];
            $credits = $_POST['credit'];

            $lines = [];
            $totalDebit = 0; $totalCredit = 0;

            for ($i = 0; $i < count($accounts); $i++) {
                $deb = empty($debits[$i]) ? 0 : floatval($debits[$i]);
                $cred = empty($credits[$i]) ? 0 : floatval($credits[$i]);

                if ($deb > 0 || $cred > 0) {
                    $lines[] = ['account_id' => $accounts[$i], 'debit' => $deb, 'credit' => $cred];
                    $totalDebit += $deb; $totalCredit += $cred;
                }
            }

            if (empty($lines)) { $data['error'] = 'You must enter at least one transaction line.'; } 
            elseif (round($totalDebit, 2) !== round($totalCredit, 2)) { $data['error'] = 'Accounting Error: Total Debits must equal Total Credits.'; } 
            else {
                if ($this->journalModel->postEntry($date, $reference, $description, $lines, $_SESSION['user_id'])) {
                    $data['success'] = 'Journal Entry successfully posted.';
                    $data['entries'] = $this->journalModel->getAllEntries();
                } else { $data['error'] = 'Database Error: Failed to post entry.'; }
            }
        }
        $this->view('layouts/main', $data);
    }

    public function close_year() {
        // RBAC: Only Admin or Accountant can close books
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Accountant') {
            die("Access Denied: Only Administrators or Accountants can perform Year-End Closing.");
        }

        $allAccounts = $this->coaModel->getAccounts();
        $equityAccounts = array_filter($allAccounts, function($a) { return $a->account_type == 'Equity'; });

        $data = [
            'title' => 'Financial Year Close',
            'content_view' => 'accounting/close_year',
            'equity_accounts' => $equityAccounts,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'close_books') {
            $endDate = $_POST['end_date'];
            $retainedEarningsId = $_POST['retained_earnings_id'];

            $result = $this->journalModel->closeFinancialYear($endDate, $_SESSION['user_id'], $retainedEarningsId);

            if ($result === true) {
                $data['success'] = "Financial Year closed successfully! Net Income transferred to Retained Earnings and past ledgers are now locked.";
            } else {
                $data['error'] = $result; // Will contain error message string
            }
        }

        $this->view('layouts/main', $data);
    }
}