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
        $parentId = $this->coaModel->selfHealBankAccounts();

        $data = [
            'title' => 'Banking & Cash',
            'content_view' => 'banking/index',
            'bank_accounts' => [],
            'cash_accounts' => [],
            'error' => '',
            'success' => ''
        ];

        // Handle Bank Account Actions & Transfers
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action == 'add_bank') {
                $name = trim($_POST['account_name']);
                if (empty($name)) {
                    $data['error'] = 'Bank Account Name is required.';
                } else {
                    $nextCode = $this->coaModel->getNextBankCode();
                    
                    if ($this->coaModel->addBankAccount($nextCode, $name, $parentId)) {
                        header('Location: ' . APP_URL . '/banking?success=bank_added');
                        exit;
                    } else {
                        $data['error'] = 'Failed to add bank account.';
                    }
                }
            }
            elseif ($action == 'edit_bank') {
                $id = intval($_POST['bank_id']);
                $name = trim($_POST['account_name']);
                if (empty($name)) {
                    $data['error'] = 'Bank Account Name is required.';
                } else {
                    if ($this->coaModel->editBankAccountName($id, $name)) {
                        header('Location: ' . APP_URL . '/banking?success=bank_edited');
                        exit;
                    } else {
                        $data['error'] = 'Failed to update bank account.';
                    }
                }
            }
            elseif ($action == 'delete_bank') {
                $id = intval($_POST['bank_id']);
                $txCount = $this->coaModel->getAccountTransactionCount($id);
                
                if ($txCount > 0) {
                    $data['error'] = 'Audit Protection: Bank accounts with transaction history cannot be deleted to preserve financial audit history.';
                } else {
                    if ($this->coaModel->deleteAccount($id)) {
                        header('Location: ' . APP_URL . '/banking?success=bank_deleted');
                        exit;
                    } else {
                        $data['error'] = 'Failed to delete bank account.';
                    }
                }
            }
            elseif ($action == 'transfer_funds') {
                $fromAccId = intval($_POST['from_account_id']);
                $toAccId = intval($_POST['to_account_id']);
                $amount = floatval($_POST['amount']);
                $date = $_POST['entry_date'];
                $description = trim($_POST['description']);
                
                if ($fromAccId == $toAccId) {
                    $data['error'] = 'Source and Destination bank accounts must be different.';
                } elseif ($amount <= 0) {
                    $data['error'] = 'Transfer amount must be greater than zero.';
                } else {
                    $db = new Database();
                    $db->query("SELECT COUNT(id) as total FROM journal_entries");
                    $countRow = $db->single();
                    $nextId = $countRow ? ($countRow->total + 1) : 1;
                    $reference = 'BK-XFER-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                    $lines = [
                        ['account_id' => $toAccId, 'debit' => $amount, 'credit' => 0],
                        ['account_id' => $fromAccId, 'debit' => 0, 'credit' => $amount]
                    ];
                    
                    if ($this->journalModel->postEntry($date, $reference, $description, $lines, $_SESSION['user_id'])) {
                        header('Location: ' . APP_URL . '/banking?success=transfer_completed');
                        exit;
                    } else {
                        $data['error'] = 'Failed to record inter-bank transfer in ledger.';
                    }
                }
            }
            elseif ($action == 'quick_entry') {
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

                    $db = new Database();
                    $db->query("SELECT COUNT(id) as total FROM journal_entries");
                    $countRow = $db->single();
                    $nextId = $countRow ? ($countRow->total + 1) : 1;
                    $reference = strtoupper($type) . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                    if ($this->journalModel->postEntry($date, $reference, $description, $lines, $_SESSION['user_id'])) {
                        header('Location: ' . APP_URL . '/banking?success=quick_entry_completed');
                        exit;
                    } else {
                        $data['error'] = 'Failed to post transaction to ledger.';
                    }
                }
            }
        }

        // Fetch bank accounts (sub-accounts of parent 1600)
        $data['bank_accounts'] = $this->coaModel->getBankAccounts($parentId);

        // Fetch cash accounts (other assets)
        $data['cash_accounts'] = $this->coaModel->getCashAccounts($parentId);

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

        $transactions = $this->coaModel->getBankLedger($accountId);

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
            $statementBalance = floatval($_POST['statement_balance'] ?? 0.0);
            $clearedTx = $_POST['cleared_tx'] ?? [];

            if (!empty($clearedTx)) {
                // Fetch current uncleared to calculate and validate
                $unclearedList = $this->coaModel->getUnclearedTransactions($accountId);
                $unclearedMap = [];
                $totalUnclearedImpact = 0.0;
                foreach ($unclearedList as $t) {
                    $unclearedMap[$t->id] = $t;
                    $totalUnclearedImpact += ($t->debit > 0) ? floatval($t->debit) : -floatval($t->credit);
                }

                $validCleared = [];
                $selectedDeposits = 0.0;
                $selectedPayments = 0.0;

                foreach ($clearedTx as $txId) {
                    $txId = intval($txId);
                    if (isset($unclearedMap[$txId])) {
                        $validCleared[] = $txId;
                        $t = $unclearedMap[$txId];
                        $impact = ($t->debit > 0) ? floatval($t->debit) : -floatval($t->credit);
                        if ($impact > 0) {
                            $selectedDeposits += $impact;
                        } else {
                            $selectedPayments += abs($impact);
                        }
                    }
                }

                if (count($validCleared) !== count($clearedTx)) {
                    $data['error'] = 'Data Integrity Error: One or more selected transactions are invalid or already cleared.';
                } else {
                    $startingClearedBalance = floatval($account->balance) - $totalUnclearedImpact;
                    $newClearedBalance = $startingClearedBalance + $selectedDeposits - $selectedPayments;
                    $difference = $statementBalance - $newClearedBalance;

                    if (abs($difference) >= 0.01) {
                        $data['error'] = 'Reconciliation Error: Selected transactions do not match the statement ending balance (Difference: Rs ' . number_format($difference, 2) . ').';
                    } else {
                        if ($this->coaModel->clearTransactions($validCleared)) {
                            $data['success'] = count($validCleared) . ' transaction(s) have been successfully reconciled and cleared!';
                            $data['statement_balance'] = $statementBalance;
                            $data['uncleared'] = $this->coaModel->getUnclearedTransactions($accountId);
                        } else {
                            $data['error'] = 'Database error: Failed to update transaction status.';
                        }
                    }
                }
            } else {
                 $data['error'] = 'No transactions were selected to clear.';
            }
        }

        $this->view('layouts/main', $data);
    }
}