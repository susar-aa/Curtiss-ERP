<?php
declare(strict_types=1);

class AccountingController extends Controller {
    private object $coaModel;
    private object $journalModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->coaModel = $this->model('ChartOfAccount');
        $this->journalModel = $this->model('JournalEntry');
    }

    public function coa(): void {
        $data = [
            'title' => 'Chart of Accounts',
            'content_view' => 'accounting/coa',
            'accounts' => [],
            'main_accounts' => [],
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if ($_POST['action'] == 'add_main') {
                $code = trim($_POST['account_code']);
                $name = trim($_POST['account_name']);
                $type = trim($_POST['account_type']);
                $category = trim($_POST['account_category'] ?? '');

                $isValid = true;
                if (!preg_match('/^[0-9]{4}$/', $code)) {
                    $data['error'] = 'Accounting Error: Account Code must be exactly a 4-digit number.';
                    $isValid = false;
                } else {
                    $firstDigit = $code[0];
                    $expectedRange = '';
                    if ($type === COA_TYPE_ASSET && $firstDigit !== '1') { $expectedRange = '1000-1999'; }
                    elseif ($type === COA_TYPE_LIABILITY && $firstDigit !== '2') { $expectedRange = '2000-2999'; }
                    elseif ($type === COA_TYPE_EQUITY && $firstDigit !== '3') { $expectedRange = '3000-3999'; }
                    elseif ($type === COA_TYPE_REVENUE && $firstDigit !== '4') { $expectedRange = '4000-4999'; }
                    elseif ($type === COA_TYPE_EXPENSE && $firstDigit !== '5' && $firstDigit !== '6') { $expectedRange = '5000-6999'; }

                    if (!empty($expectedRange)) {
                        $data['error'] = "Accounting Error: Account Code for Type '{$type}' must be in the range {$expectedRange}.";
                        $isValid = false;
                    }
                }

                if ($isValid) {
                    $validCategories = [];
                    if ($type === COA_TYPE_ASSET) $validCategories = ['Current Asset', 'Fixed Asset', 'Non-current Asset'];
                    elseif ($type === COA_TYPE_LIABILITY) $validCategories = ['Current Liability', 'Long-term Liability'];
                    elseif ($type === COA_TYPE_EQUITY) $validCategories = ['Equity'];
                    elseif ($type === COA_TYPE_REVENUE) $validCategories = ['Revenue'];
                    elseif ($type === COA_TYPE_EXPENSE) $validCategories = ['Cost of Goods Sold', 'Operating Expense', 'Non-operating Expense'];

                    if (!in_array($category, $validCategories)) {
                        $data['error'] = "Accounting Error: Invalid category '{$category}' selected for account type '{$type}'.";
                        $isValid = false;
                    }
                }

                if ($isValid) {
                    $postData = [
                        'account_code' => $code,
                        'account_name' => $name,
                        'account_type' => $type,
                        'account_category' => $category,
                        'parent_id' => null
                    ];
                    if (!empty($postData['account_code']) && !empty($postData['account_name'])) {
                        if ($this->coaModel->addAccount($postData)) { $data['success'] = 'Main Account added successfully.'; } 
                        else { $data['error'] = 'Failed to add account. Account Code may already exist.'; }
                    } else { $data['error'] = 'Code and Name are required.'; }
                }
            }
            elseif ($_POST['action'] == 'add_sub') {
                $parent = $this->coaModel->getAccountById($_POST['parent_id']);
                if ($parent) {
                    $code = trim($_POST['account_code']);
                    $name = trim($_POST['account_name']);
                    $type = $parent->account_type;
                    $category = $parent->account_category;

                    $isValid = true;
                    if (!preg_match('/^[0-9]{4}$/', $code)) {
                        $data['error'] = 'Accounting Error: Account Code must be exactly a 4-digit number.';
                        $isValid = false;
                    } else {
                        $firstDigit = $code[0];
                        $expectedRange = '';
                        if ($type === COA_TYPE_ASSET && $firstDigit !== '1') { $expectedRange = '1000-1999'; }
                        elseif ($type === COA_TYPE_LIABILITY && $firstDigit !== '2') { $expectedRange = '2000-2999'; }
                        elseif ($type === COA_TYPE_EQUITY && $firstDigit !== '3') { $expectedRange = '3000-3999'; }
                        elseif ($type === COA_TYPE_REVENUE && $firstDigit !== '4') { $expectedRange = '4000-4999'; }
                        elseif ($type === COA_TYPE_EXPENSE && $firstDigit !== '5' && $firstDigit !== '6') { $expectedRange = '5000-6999'; }

                        if (!empty($expectedRange)) {
                            $data['error'] = "Accounting Error: Account Code for Type '{$type}' must be in the range {$expectedRange}.";
                            $isValid = false;
                        }
                    }

                    if ($isValid) {
                        $postData = [
                            'account_code' => $code,
                            'account_name' => $name,
                            'account_type' => $type, // Sub-accounts MUST inherit the parent's type
                            'account_category' => $category, // Sub-accounts MUST inherit the parent's category
                            'parent_id' => $parent->id
                        ];
                        if ($this->coaModel->addAccount($postData)) { $data['success'] = 'Sub-Account added successfully.'; } 
                        else { $data['error'] = 'Failed to add account. Account Code may already exist.'; }
                    }
                } else {
                    $data['error'] = 'Invalid parent account selected.';
                }
            }
            elseif ($_POST['action'] == 'edit_account') {
                $code = trim($_POST['account_code']);
                $name = trim($_POST['account_name']);
                $type = trim($_POST['account_type']);
                $category = trim($_POST['account_category'] ?? '');
                $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

                if ($parentId) {
                    $parent = $this->coaModel->getAccountById($parentId);
                    if ($parent) {
                        $type = $parent->account_type;
                        $category = $parent->account_category;
                    }
                }

                $isValid = true;
                if (!preg_match('/^[0-9]{4}$/', $code)) {
                    $data['error'] = 'Accounting Error: Account Code must be exactly a 4-digit number.';
                    $isValid = false;
                } else {
                    $firstDigit = $code[0];
                    $expectedRange = '';
                    if ($type === COA_TYPE_ASSET && $firstDigit !== '1') { $expectedRange = '1000-1999'; }
                    elseif ($type === COA_TYPE_LIABILITY && $firstDigit !== '2') { $expectedRange = '2000-2999'; }
                    elseif ($type === COA_TYPE_EQUITY && $firstDigit !== '3') { $expectedRange = '3000-3999'; }
                    elseif ($type === COA_TYPE_REVENUE && $firstDigit !== '4') { $expectedRange = '4000-4999'; }
                    elseif ($type === COA_TYPE_EXPENSE && $firstDigit !== '5' && $firstDigit !== '6') { $expectedRange = '5000-6999'; }

                    if (!empty($expectedRange)) {
                        $data['error'] = "Accounting Error: Account Code for Type '{$type}' must be in the range {$expectedRange}.";
                        $isValid = false;
                    }
                }

                if ($isValid && !$parentId) {
                    $validCategories = [];
                    if ($type === COA_TYPE_ASSET) $validCategories = ['Current Asset', 'Fixed Asset', 'Non-current Asset'];
                    elseif ($type === COA_TYPE_LIABILITY) $validCategories = ['Current Liability', 'Long-term Liability'];
                    elseif ($type === COA_TYPE_EQUITY) $validCategories = ['Equity'];
                    elseif ($type === COA_TYPE_REVENUE) $validCategories = ['Revenue'];
                    elseif ($type === COA_TYPE_EXPENSE) $validCategories = ['Cost of Goods Sold', 'Operating Expense', 'Non-operating Expense'];

                    if (!in_array($category, $validCategories)) {
                        $data['error'] = "Accounting Error: Invalid category '{$category}' selected for account type '{$type}'.";
                        $isValid = false;
                    }
                }

                if ($isValid) {
                    $postData = [
                        'id' => $_POST['account_id'],
                        'account_code' => $code,
                        'account_name' => $name,
                        'account_type' => $type,
                        'account_category' => $category,
                        'parent_id' => $parentId,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0
                    ];
                    if ($this->coaModel->updateAccount($postData)) { $data['success'] = 'Account updated successfully.'; } 
                    else { $data['error'] = 'Failed to update account.'; }
                }
            }
            elseif ($_POST['action'] == 'delete_account') {
                $accountId = $_POST['account_id'] ?? null;
                $this->logActivity('Delete Attempt', 'Accounting', "Unauthorized attempt to delete General Ledger Account ID: {$accountId}", $accountId);
                $data['error'] = 'Audit Trail Protection: General Ledger accounts cannot be deleted to preserve financial audit history.';
            }
        }

        $allAccounts = $this->coaModel->getAccounts();
        $data['accounts'] = $allAccounts;
        $data['main_accounts'] = array_filter($allAccounts, function($a) { return empty($a->parent_id); });
        
        $customerModel = $this->model('Customer');
        $data['customers'] = $customerModel->getAllCustomers() ?: [];
        
        $this->view('layouts/main', $data);
    }

    /**
     * Reusable, filterable Account Ledger History view
     */
    public function history(?int $id = null): void {
        $allAccounts = $this->coaModel->getAccounts();
        
        if (!$id && !empty($allAccounts)) {
            $id = $allAccounts[0]->id;
        }

        $selectedAccount = null;
        if ($id) {
            $selectedAccount = $this->coaModel->getAccountById($id);
        }

        $transactions = [];
        $priorBalance = 0.00;
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $quickRange = $_GET['quick_range'] ?? '';

        if ($selectedAccount) {
            // Handle Quick Date Ranges
            if (!empty($quickRange)) {
                $today = date('Y-m-d');
                if ($quickRange === 'today') {
                    $startDate = $today;
                    $endDate = $today;
                } elseif ($quickRange === 'last_week') {
                    $startDate = date('Y-m-d', strtotime('-7 days'));
                    $endDate = $today;
                } elseif ($quickRange === 'last_month') {
                    $startDate = date('Y-m-d', strtotime('-30 days'));
                    $endDate = $today;
                }
            }

            // Calculate Prior Balance (for starting running total in ledger)
            if (!empty($startDate)) {
                $priorSum = $this->coaModel->getPriorBalance($id, $startDate);
                $pDeb = floatval($priorSum->total_debit ?? 0);
                $pCred = floatval($priorSum->total_credit ?? 0);

                if (in_array($selectedAccount->account_type, ['Asset', 'Expense'])) {
                    $priorBalance = $pDeb - $pCred;
                } else {
                    $priorBalance = $pCred - $pDeb;
                }
            }

            // Fetch filtered ledger lines
            $filters = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => trim($_GET['search'] ?? ''),
                'tx_type' => $_GET['tx_type'] ?? 'all'
            ];
            $transactions = $this->coaModel->getAccountHistory($id, $filters);
        }

        $data = [
            'title' => 'Account Ledger History',
            'content_view' => 'accounting/history',
            'all_accounts' => $allAccounts,
            'selected_account' => $selectedAccount,
            'transactions' => $transactions,
            'prior_balance' => $priorBalance,
            'selected_id' => $id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quick_range' => $quickRange,
            'search' => $_GET['search'] ?? '',
            'tx_type' => $_GET['tx_type'] ?? 'all'
        ];

        $this->view('layouts/main', $data);
    }

    public function journal(): void {
        $this->generateCsrfToken();
        
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $totalEntries = $this->journalModel->getEntriesCount();
        $totalPages = ceil($totalEntries / $limit);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $entries = $this->journalModel->getAllEntries($limit, $offset);

        $data = [
            'title' => 'General Journal',
            'content_view' => 'accounting/journal',
            'accounts' => $this->coaModel->getAccounts(),
            'entries' => $entries,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_entries' => $totalEntries,
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $date = trim($_POST['entry_date']);
            $reference = trim($_POST['reference']);
            $description = trim($_POST['description']);
            $accounts = $_POST['account_id'];
            $debits = $_POST['debit'];
            $credits = $_POST['credit'];

            $line_descriptions = $_POST['line_description'] ?? [];

            $lines = [];
            $totalDebit = 0; $totalCredit = 0;
            $hasNegative = false;
            $hasBoth = false;

            for ($i = 0; $i < count($accounts); $i++) {
                $deb = empty($debits[$i]) ? 0 : floatval($debits[$i]);
                $cred = empty($credits[$i]) ? 0 : floatval($credits[$i]);
                $line_desc = empty($line_descriptions[$i]) ? '' : trim($line_descriptions[$i]);

                if ($deb < 0 || $cred < 0) {
                    $hasNegative = true;
                }
                if ($deb > 0 && $cred > 0) {
                    $hasBoth = true;
                }

                if (($deb > 0 || $cred > 0) && $deb >= 0 && $cred >= 0) {
                    $lines[] = ['account_id' => $accounts[$i], 'debit' => $deb, 'credit' => $cred, 'description' => $line_desc];
                    $totalDebit += $deb; $totalCredit += $cred;
                }
            }

            $totalDebitCents = bcmul(sprintf("%.2f", $totalDebit), '100', 0);
            $totalCreditCents = bcmul(sprintf("%.2f", $totalCredit), '100', 0);

            if ($hasNegative) { $data['error'] = 'Accounting Error: Debit and Credit amounts cannot be negative.'; }
            elseif ($hasBoth) { $data['error'] = 'Accounting Error: A single transaction line cannot contain both a Debit and a Credit amount.'; }
            elseif (empty($lines)) { $data['error'] = 'You must enter at least one transaction line.'; } 
            elseif ($totalDebitCents !== $totalCreditCents) { $data['error'] = 'Accounting Error: Total Debits must equal Total Credits.'; } 
            else {
                $postResult = $this->journalModel->postEntry($date, $reference, $description, $lines, $_SESSION['user_id']);
                if ($postResult === true) {
                    $data['success'] = 'Journal Entry successfully posted.';
                    // Recalculate
                    $page = 1;
                    $offset = 0;
                    $totalEntries = $this->journalModel->getEntriesCount();
                    $totalPages = ceil($totalEntries / $limit);
                    if ($totalPages < 1) $totalPages = 1;
                    
                    $data['entries'] = $this->journalModel->getAllEntries($limit, $offset);
                    $data['page'] = $page;
                    $data['total_pages'] = $totalPages;
                    $data['total_entries'] = $totalEntries;
                } else {
                    $data['error'] = $postResult;
                }
            }
        }
        $this->view('layouts/main', $data);
    }

    public function close_year(): void {
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Accountant') {
            die("Access Denied: Only Administrators or Accountants can perform Year-End Closing.");
        }

        $this->generateCsrfToken();

        $allAccounts = $this->coaModel->getAccounts();
        $equityAccounts = array_filter($allAccounts, function($a) { return $a->account_type == 'Equity'; });

        // Fetch closed financial years list
        $closedYears = $this->journalModel->getClosedFinancialYears();

        $data = [
            'title' => 'Financial Year Close',
            'content_view' => 'accounting/close_year',
            'equity_accounts' => $equityAccounts,
            'closed_years' => $closedYears,
            'error' => $_SESSION['flash_error'] ?? '',
            'success' => $_SESSION['flash_success'] ?? ''
        ];

        unset($_SESSION['flash_error']);
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'close_books') {
            $this->validateCsrfOrDie();

            // Enforce HTTPS transmission to protect password verification from packet sniffing
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                        || $_SERVER['SERVER_PORT'] == 443 
                        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            $isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';

            if (!$isSecure && !$isLocal) {
                $data['error'] = "Accounting Security Error: For safety, password confirmation must be sent over a secure connection (HTTPS).";
            } else {
                $startDate = trim($_POST['start_date']);
                $endDate = trim($_POST['end_date']);
                $retainedEarningsId = $_POST['retained_earnings_id'];
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($startDate) || empty($endDate)) {
                    $data['error'] = "Accounting Error: Both Start Date and End Date are required.";
                } elseif (strtotime($startDate) > strtotime($endDate)) {
                    $data['error'] = "Accounting Error: Start date cannot be after end date.";
                } else {
                    $userModel = $this->model('User');
                    $user = $userModel->login($_SESSION['username'], $confirmPassword);

                    if (!$user) {
                        $data['error'] = "Accounting Security Error: Invalid password confirmation. Year-End closing aborted.";
                    } else {
                        $result = $this->journalModel->closeFinancialYear($startDate, $endDate, $_SESSION['user_id'], $retainedEarningsId);

                        if ($result === true) {
                            $data['success'] = "Financial Year closed successfully! Net Income transferred to Retained Earnings and past ledgers are now locked.";
                            // Re-fetch closed years
                            $data['closed_years'] = $this->journalModel->getClosedFinancialYears();
                        } else {
                            $data['error'] = $result; 
                        }
                    }
                }
            }
        }

        $this->view('layouts/main', $data);
    }

    public function revert_close_year(): void {
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Accountant') {
            die("Access Denied: Only Administrators or Accountants can revert a Year-End Close.");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfOrDie();

            // Enforce HTTPS transmission to protect password verification from packet sniffing
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                        || $_SERVER['SERVER_PORT'] == 443 
                        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            $isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';

            if (!$isSecure && !$isLocal) {
                $_SESSION['flash_error'] = "Accounting Security Error: For safety, password confirmation must be sent over a secure connection (HTTPS).";
            } else {
                $fyId = intval($_POST['fy_id']);
                $confirmPassword = $_POST['confirm_password'] ?? '';

                $userModel = $this->model('User');
                $user = $userModel->login($_SESSION['username'], $confirmPassword);

                if (!$user) {
                    $_SESSION['flash_error'] = "Accounting Security Error: Invalid password confirmation. Reversal aborted.";
                } else {
                    $result = $this->journalModel->revertFinancialYearClose($fyId);

                    if ($result === true) {
                        $_SESSION['flash_success'] = "Financial Year Close reversed successfully! Closing journal entry has been voided, ledger balances restored, and entries unlocked.";
                    } else {
                        $_SESSION['flash_error'] = $result; 
                    }
                }
            }
        }

        header('Location: ' . APP_URL . '/accounting/close_year');
        exit;
    }

    public function recurring(): void {
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Accountant') {
            die("Access Denied: Only Administrators or Accountants can manage recurring journal templates.");
        }

        $this->generateCsrfToken();
        
        if (!class_exists('RecurringJournal')) {
            require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/RecurringJournal.php';
        }
        $recurringModel = new RecurringJournal();

        $data = [
            'title' => 'Recurring Journal Templates',
            'content_view' => 'accounting/recurring',
            'templates' => [],
            'pending_templates' => [],
            'accounts' => $this->coaModel->getAccounts() ?: [],
            'error' => $_SESSION['flash_error'] ?? '',
            'success' => $_SESSION['flash_success'] ?? ''
        ];

        unset($_SESSION['flash_error']);
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $this->validateCsrfOrDie();

            if ($_POST['action'] === 'create_template') {
                $name = trim($_POST['template_name']);
                $frequency = trim($_POST['frequency']);
                $dom = intval($_POST['day_of_month'] ?? 1);
                $desc = trim($_POST['description']);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                // Process lines
                $lines = [];
                $totalDebit = 0;
                $totalCredit = 0;
                if (isset($_POST['lines']) && is_array($_POST['lines'])) {
                    foreach ($_POST['lines'] as $line) {
                        $aid = intval($line['account_id']);
                        $deb = floatval($line['debit'] ?? 0);
                        $cred = floatval($line['credit'] ?? 0);
                        $ldesc = trim($line['description'] ?? '');

                        if ($aid > 0 && ($deb > 0 || $cred > 0)) {
                            $lines[] = [
                                'account_id' => $aid,
                                'debit' => $deb,
                                'credit' => $cred,
                                'description' => $ldesc
                            ];
                            $totalDebit += $deb;
                            $totalCredit += $cred;
                        }
                    }
                }

                $totalDebitCents = bcmul(sprintf("%.2f", $totalDebit), '100', 0);
                $totalCreditCents = bcmul(sprintf("%.2f", $totalCredit), '100', 0);

                if (empty($name)) {
                    $data['error'] = 'Template name is required.';
                } elseif (empty($lines)) {
                    $data['error'] = 'At least one transaction line is required.';
                } elseif ($totalDebitCents !== $totalCreditCents) {
                    $data['error'] = 'Accounting Error: Total debits (' . $totalDebit . ') must equal total credits (' . $totalCredit . ').';
                } else {
                    $templateData = [
                        'template_name' => $name,
                        'frequency' => $frequency,
                        'day_of_month' => $dom,
                        'description' => $desc,
                        'is_active' => $isActive,
                        'lines' => $lines
                    ];

                    if ($recurringModel->createTemplate($templateData)) {
                        $data['success'] = 'Recurring journal template created successfully.';
                    } else {
                        $data['error'] = 'Failed to create recurring journal template.';
                    }
                }
            } 
            elseif ($_POST['action'] === 'delete_template') {
                $id = intval($_POST['template_id']);
                if ($recurringModel->deleteTemplate($id)) {
                    $data['success'] = 'Recurring template deleted successfully.';
                } else {
                    $data['error'] = 'Failed to delete template.';
                }
            } 
            elseif ($_POST['action'] === 'post_entry') {
                $id = intval($_POST['template_id']);
                $date = trim($_POST['post_date'] ?? date('Y-m-d'));
                
                $result = $recurringModel->postRecurringEntry($id, $date, $_SESSION['user_id']);
                if ($result === true) {
                    $data['success'] = 'Journal Entry successfully generated and posted from template.';
                } else {
                    $data['error'] = $result;
                }
            } 
            elseif ($_POST['action'] === 'post_all_due') {
                $dueTemplates = $recurringModel->getPendingTemplates();
                if (empty($dueTemplates)) {
                    $data['error'] = 'No due templates to post.';
                } else {
                    $postedCount = 0;
                    $errors = [];
                    foreach ($dueTemplates as $dt) {
                        $result = $recurringModel->postRecurringEntry($dt->id, date('Y-m-d'), $_SESSION['user_id']);
                        if ($result === true) {
                            $postedCount++;
                        } else {
                            $errors[] = $dt->template_name . ": " . $result;
                        }
                    }

                    if ($postedCount > 0) {
                        $data['success'] = "Successfully posted {$postedCount} recurring journal entry(ies).";
                    }
                    if (!empty($errors)) {
                        $data['error'] = 'Some errors occurred: ' . implode('; ', $errors);
                    }
                }
            }
        }

        // Re-fetch list
        $data['templates'] = $recurringModel->getAllTemplates();
        $data['pending_templates'] = $recurringModel->getPendingTemplates();

        $this->view('layouts/main', $data);
    }

    public function void_journal(): void {
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Accountant') {
            die("Access Denied: Only Administrators or Accountants can void journal entries.");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfOrDie();

            $id = intval($_POST['entry_id']);
            if ($this->journalModel->voidEntry($id)) {
                $_SESSION['flash_success'] = 'Journal Entry has been successfully voided and account balances reversed.';
            } else {
                $_SESSION['flash_error'] = 'Failed to void journal entry. (Maybe it belongs to a closed/locked period).';
            }
        }
        header('Location: ' . APP_URL . '/accounting/journal');
        exit;
    }
}