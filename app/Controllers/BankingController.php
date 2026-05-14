<?php
class BankingController extends Controller {
    private $coaModel;
    private $journalModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->coaModel = $this->model('ChartOfAccount');
        $this->journalModel = $this->model('JournalEntry');
    }

    // Displays the list of all Bank/Cash accounts
    public function index() {
        $accounts = $this->coaModel->getAccounts();
        
        // Filter to only show Asset accounts (usually where Bank/Cash is categorized)
        $bankAccounts = array_filter($accounts, function($a) { 
            return $a->account_type == 'Asset'; 
        });

        $data = [
            'title' => 'Banking & Cash',
            'content_view' => 'banking/index',
            'bank_accounts' => $bankAccounts,
            'error' => '',
            'success' => ''
        ];

        // Handle Quick Transfer / Manual Entry
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'quick_entry') {
            $type = $_POST['type']; // 'deposit' or 'withdrawal'
            $bankAccountId = $_POST['bank_account_id'];
            $offsetAccountId = $_POST['offset_account_id'];
            $amount = floatval($_POST['amount']);
            $date = $_POST['entry_date'];
            $description = trim($_POST['description']);

            if ($amount <= 0) {
                $data['error'] = 'Amount must be greater than zero.';
            } else {
                if ($type == 'deposit') {
                    $lines = [
                        ['account_id' => $bankAccountId, 'debit' => $amount, 'credit' => 0],
                        ['account_id' => $offsetAccountId, 'debit' => 0, 'credit' => $amount]
                    ];
                } else {
                    $lines = [
                        ['account_id' => $offsetAccountId, 'debit' => $amount, 'credit' => 0],
                        ['account_id' => $bankAccountId, 'debit' => 0, 'credit' => $amount]
                    ];
                }

                $reference = strtoupper($type) . '-' . time();

                if ($this->journalModel->postEntry($date, $reference, $description, $lines, $_SESSION['user_id'])) {
                    header('Location: ' . APP_URL . '/banking?success=1');
                    exit;
                } else {
                    $data['error'] = 'Failed to post transaction to ledger.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }

    // View detailed transaction history for a specific account
    public function ledger($accountId = null) {
        if (!$accountId) {
            header('Location: ' . APP_URL . '/banking');
            exit;
        }

        $account = $this->coaModel->getAccountById($accountId);
        if (!$account) { die("Account not found."); }

        $db = new Database();
        $db->query("SELECT t.*, je.entry_date, je.reference, je.description 
                    FROM transactions t 
                    JOIN journal_entries je ON t.journal_entry_id = je.id 
                    WHERE t.account_id = :aid 
                    ORDER BY je.entry_date DESC, je.id DESC");
        $db->bind(':aid', $accountId);
        $transactions = $db->resultSet();

        $data = [
            'title' => 'Account Ledger: ' . $account->account_name,
            'content_view' => 'banking/ledger',
            'account' => $account,
            'transactions' => $transactions
        ];

        $this->view('layouts/main', $data);
    }

    // --- NEW: Bank Reconciliation Screen ---
    public function reconcile($accountId = null) {
        if (!$accountId) {
            header('Location: ' . APP_URL . '/banking');
            exit;
        }

        $account = $this->coaModel->getAccountById($accountId);
        if (!$account || $account->account_type !== 'Asset') {
            die("Invalid account selected for reconciliation.");
        }

        $data = [
            'title' => 'Reconcile: ' . $account->account_name,
            'content_view' => 'banking/reconcile',
            'account' => $account,
            'uncleared' => $this->coaModel->getUnclearedTransactions($accountId),
            'statement_balance' => isset($_POST['statement_balance']) ? floatval($_POST['statement_balance']) : $account->balance,
            'error' => '',
            'success' => ''
        ];

        // Handle the submission of checked transactions
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_reconciliation') {
            
            if (isset($_POST['cleared_tx']) && !empty($_POST['cleared_tx'])) {
                if ($this->coaModel->clearTransactions($_POST['cleared_tx'])) {
                    $data['success'] = count($_POST['cleared_tx']) . ' transaction(s) have been successfully reconciled and cleared!';
                    // Refresh the uncleared list so they disappear from the screen
                    $data['uncleared'] = $this->coaModel->getUnclearedTransactions($accountId);
                } else {
                    $data['error'] = 'Database error: Failed to update transaction status.';
                }
            } else {
                 $data['error'] = 'No transactions were selected to clear.';
            }
        }

        $this->view('layouts/main', $data);
    }
}