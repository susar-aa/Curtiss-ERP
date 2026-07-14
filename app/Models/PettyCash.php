<?php
declare(strict_types=1);

class PettyCash {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get petty cash configuration
     */
    public function getConfig() {
        $this->db->query("
            SELECT c.*, u.username as custodian_name, coa.account_name as funding_account_name 
            FROM petty_cash_config c
            LEFT JOIN users u ON c.custodian_id = u.id
            LEFT JOIN chart_of_accounts coa ON c.default_funding_account_id = coa.id
            LIMIT 1
        ");
        return $this->db->single();
    }

    /**
     * Save petty cash configuration and record in history
     */
    public function saveConfig(array $data, int $userId): bool {
        try {
            $this->db->beginTransaction();

            $oldConfig = $this->getConfig();

            $this->db->query("SELECT COUNT(*) as cnt FROM petty_cash_config");
            $hasConfig = $this->db->single()->cnt > 0;

            if ($hasConfig) {
                $this->db->query("
                    UPDATE petty_cash_config 
                    SET limit_amount = :limit_amount, 
                        custodian_id = :custodian_id, 
                        require_approval = :require_approval, 
                        default_funding_account_id = :default_funding_account_id, 
                        reimbursement_threshold = :reimbursement_threshold
                    WHERE id = :id
                ");
                $this->db->bind(':id', $oldConfig->id);
            } else {
                $this->db->query("
                    INSERT INTO petty_cash_config (limit_amount, custodian_id, require_approval, default_funding_account_id, reimbursement_threshold)
                    VALUES (:limit_amount, :custodian_id, :require_approval, :default_funding_account_id, :reimbursement_threshold)
                ");
            }

            $this->db->bind(':limit_amount', $data['limit_amount']);
            $this->db->bind(':custodian_id', $data['custodian_id']);
            $this->db->bind(':require_approval', $data['require_approval']);
            $this->db->bind(':default_funding_account_id', $data['default_funding_account_id']);
            $this->db->bind(':reimbursement_threshold', $data['reimbursement_threshold'] ?: null);
            $this->db->execute();

            // Insert into history
            $this->db->query("
                INSERT INTO petty_cash_config_history (limit_amount, custodian_id, require_approval, default_funding_account_id, reimbursement_threshold, changed_by, action)
                VALUES (:limit_amount, :custodian_id, :require_approval, :default_funding_account_id, :reimbursement_threshold, :changed_by, :action)
            ");
            $this->db->bind(':limit_amount', $data['limit_amount']);
            $this->db->bind(':custodian_id', $data['custodian_id']);
            $this->db->bind(':require_approval', $data['require_approval']);
            $this->db->bind(':default_funding_account_id', $data['default_funding_account_id']);
            $this->db->bind(':reimbursement_threshold', $data['reimbursement_threshold'] ?: null);
            $this->db->bind(':changed_by', $userId);
            $this->db->bind(':action', $hasConfig ? 'UPDATED' : 'CREATED');
            $this->db->execute();

            $this->db->commit();

            // Log Audit Log
            require_once APP_ROOT . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction(
                $userId,
                $hasConfig ? 'Update Settings' : 'Create Settings',
                'Petty Cash',
                "Updated Petty Cash limit to " . $data['limit_amount'],
                $oldConfig ? intval($oldConfig->id) : 1,
                $oldConfig ? json_encode($oldConfig) : null,
                json_encode($data)
            );

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Get petty cash configuration change history
     */
    public function getConfigHistory(): array {
        $this->db->query("
            SELECT h.*, u.username as changer_name, coa.account_name as funding_account_name, cust.username as custodian_name
            FROM petty_cash_config_history h
            LEFT JOIN users u ON h.changed_by = u.id
            LEFT JOIN chart_of_accounts coa ON h.default_funding_account_id = coa.id
            LEFT JOIN users cust ON h.custodian_id = cust.id
            ORDER BY h.changed_at DESC
        ");
        return $this->db->resultSet() ?: [];
    }

    /**
     * Get Petty Cash Account current balance
     */
    public function getPettyCashAccountBalance(): float {
        $this->db->query("SELECT balance FROM chart_of_accounts WHERE account_code = '1020' LIMIT 1");
        $row = $this->db->single();
        return $row ? floatval($row->balance) : 0.00;
    }

    /**
     * Get Petty Cash Pending Expenses total
     */
    public function getPendingExpensesTotal(): float {
        $this->db->query("SELECT SUM(amount) as total FROM petty_cash_expenses WHERE status = 'Pending'");
        $row = $this->db->single();
        return $row ? floatval($row->total ?? 0.00) : 0.00;
    }

    /**
     * Get Petty Cash Available Balance
     */
    public function getAvailableBalance(): float {
        $balance = $this->getPettyCashAccountBalance();
        $pending = $this->getPendingExpensesTotal();
        return max(0.00, $balance - $pending);
    }

    /**
     * Get Petty Cash Summary stats
     */
    public function getSummary(?string $month = null): array {
        if (!$month) {
            $month = date('Y-m');
        }

        $config = $this->getConfig();
        $limit = $config ? floatval($config->limit_amount) : 0.00;
        $currentBalance = $this->getPettyCashAccountBalance();
        $pendingExpenses = $this->getPendingExpensesTotal();
        $availableBalance = max(0.00, $currentBalance - $pendingExpenses);

        // Pending Reimbursements (Approved expenses not yet reimbursed)
        $this->db->query("SELECT SUM(amount) as total FROM petty_cash_expenses WHERE status = 'Approved' AND reimbursement_id IS NULL");
        $pendingReimbursements = floatval($this->db->single()->total ?? 0.00);

        // Pending approvals count
        $this->db->query("SELECT COUNT(*) as count FROM petty_cash_expenses WHERE status = 'Pending'");
        $pendingApprovalsCount = intval($this->db->single()->count ?? 0);

        // Total Expenses This Month
        $this->db->query("SELECT SUM(amount) as total FROM petty_cash_expenses WHERE status = 'Approved' AND DATE_FORMAT(expense_date, '%Y-%m') = :month");
        $this->db->bind(':month', $month);
        $totalExpensesThisMonth = floatval($this->db->single()->total ?? 0.00);

        // Total Reimbursements This Month
        $this->db->query("SELECT SUM(amount) as total FROM petty_cash_reimbursements WHERE DATE_FORMAT(reimbursement_date, '%Y-%m') = :month");
        $this->db->bind(':month', $month);
        $totalReimbursementsThisMonth = floatval($this->db->single()->total ?? 0.00);

        return [
            'current_balance' => $currentBalance,
            'limit_amount' => $limit,
            'available_balance' => $availableBalance,
            'pending_reimbursements' => $pendingReimbursements,
            'pending_expense_approvals' => $pendingApprovalsCount,
            'total_expenses_this_month' => $totalExpensesThisMonth,
            'total_reimbursements_this_month' => $totalReimbursementsThisMonth
        ];
    }

    /**
     * Get recent transactions (mix of expenses, transfers, reimbursements)
     */
    public function getRecentTransactions(int $limit = 10): array {
        $sql = "
            (SELECT 'Expense' as type, id, expense_date as date, amount, description, status, created_at
             FROM petty_cash_expenses)
            UNION ALL
            (SELECT 'Reimbursement' as type, id, reimbursement_date as date, amount, CONCAT('Reimbursement - ', remarks) as description, 'Approved' as status, created_at
             FROM petty_cash_reimbursements)
            UNION ALL
            (SELECT 'Transfer' as type, id, transfer_date as date, amount, CONCAT('Transfer - ', remarks) as description, 'Approved' as status, created_at
             FROM petty_cash_transfers)
            ORDER BY date DESC, created_at DESC
            LIMIT :limit
        ";
        $this->db->query($sql);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Record a new petty cash expense
     */
    public function recordExpense(array $data, int $userId): bool {
        try {
            // Check available balance before submitting
            $available = $this->getAvailableBalance();
            if ($data['amount'] > $available) {
                return false; // Amount exceeds available petty cash balance
            }

            // Create unique transaction ref / pad format
            $this->db->query("SELECT COUNT(id) as total FROM petty_cash_expenses");
            $countRow = $this->db->single();
            $nextId = $countRow ? ($countRow->total + 1) : 1;
            $reference = 'EXP-PC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            $this->db->query("
                INSERT INTO petty_cash_expenses (expense_date, category, expense_account_id, amount, vendor_id, description, payment_reference, attachment_path, status, created_by)
                VALUES (:expense_date, :category, :expense_account_id, :amount, :vendor_id, :description, :payment_reference, :attachment_path, :status, :created_by)
            ");

            $config = $this->getConfig();
            $requireApproval = $config ? intval($config->require_approval) : 1;
            $status = ($requireApproval === 0) ? 'Approved' : 'Pending';

            $this->db->bind(':expense_date', $data['expense_date']);
            $this->db->bind(':category', $data['category']);
            $this->db->bind(':expense_account_id', $data['expense_account_id']);
            $this->db->bind(':amount', $data['amount']);
            $this->db->bind(':vendor_id', $data['vendor_id'] ?: null);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':payment_reference', $reference);
            $this->db->bind(':attachment_path', $data['attachment_path'] ?? null);
            $this->db->bind(':status', $status);
            $this->db->bind(':created_by', $userId);
            $this->db->execute();

            $expenseId = $this->db->lastInsertId();

            // If auto-approved, post the journal entry immediately
            if ($status === 'Approved') {
                $this->db->beginTransaction();
                $postResult = $this->postExpenseJournalEntry($expenseId, $userId);
                if ($postResult !== true) {
                    $this->db->rollBack();
                    return false;
                }
                $this->db->commit();
            }

            // Audit log
            require_once APP_ROOT . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, 'Record Expense', 'Petty Cash', "Recorded expense of " . $data['amount'] . " (Status: $status)", intval($expenseId));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Approve petty cash expense and post journal entry
     */
    public function approveExpense(int $expenseId, int $userId): bool {
        try {
            $this->db->query("SELECT * FROM petty_cash_expenses WHERE id = :id FOR UPDATE");
            $this->db->bind(':id', $expenseId);
            $expense = $this->db->single();

            if (!$expense || $expense->status !== 'Pending') {
                return false; // Can only approve pending expenses
            }

            // Verify available balance again
            $pettyCashBalance = $this->getPettyCashAccountBalance();
            if ($expense->amount > $pettyCashBalance) {
                return false; // Insufficient funds in ledger account to approve
            }

            $this->db->beginTransaction();

            // Post Journal Entry
            $postResult = $this->postExpenseJournalEntry(intval($expenseId), $userId);
            if ($postResult !== true) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();

            // Audit log
            require_once APP_ROOT . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, 'Approve Expense', 'Petty Cash', "Approved expense ID $expenseId amount " . $expense->amount, $expenseId);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Reject petty cash expense
     */
    public function rejectExpense(int $expenseId, int $userId): bool {
        try {
            $this->db->query("SELECT * FROM petty_cash_expenses WHERE id = :id");
            $this->db->bind(':id', $expenseId);
            $expense = $this->db->single();

            if (!$expense || $expense->status !== 'Pending') {
                return false;
            }

            $this->db->query("
                UPDATE petty_cash_expenses 
                SET status = 'Rejected', approved_by = :uid, approved_at = NOW() 
                WHERE id = :id
            ");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':id', $expenseId);
            $this->db->execute();

            // Audit log
            require_once APP_ROOT . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, 'Reject Expense', 'Petty Cash', "Rejected expense ID $expenseId amount " . $expense->amount, $expenseId);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Post Expense Journal Entry (Double-Entry Bookkeeping)
     * Debit Selected Expense Account, Credit Petty Cash (1020)
     */
    private function postExpenseJournalEntry(int $expenseId, int $userId): bool {
        $this->db->query("
            SELECT e.*, coa.id as petty_cash_acc_id
            FROM petty_cash_expenses e
            JOIN chart_of_accounts coa ON coa.account_code = '1020'
            WHERE e.id = :id
        ");
        $this->db->bind(':id', $expenseId);
        $expense = $this->db->single();

        if (!$expense) return false;

        $desc = "Petty Cash Expense: " . $expense->description;
        $lines = [
            ['account_id' => $expense->expense_account_id, 'debit' => $expense->amount, 'credit' => 0, 'description' => $expense->description],
            ['account_id' => $expense->petty_cash_acc_id, 'debit' => 0, 'credit' => $expense->amount, 'description' => $expense->description]
        ];

        require_once APP_ROOT . '/app/Models/JournalEntry.php';
        $journalModel = new JournalEntry();
        $res = $journalModel->postEntry($expense->expense_date, $expense->payment_reference, $desc, $lines, $userId);

        if ($res === true) {
            // Find journal_entry_id
            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
            $this->db->bind(':ref', $expense->payment_reference);
            $je = $this->db->single();
            $jeId = $je ? intval($je->id) : null;

            $this->db->query("
                UPDATE petty_cash_expenses 
                SET status = 'Approved', approved_by = :uid, approved_at = NOW(), journal_entry_id = :je_id 
                WHERE id = :id
            ");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':id', $expenseId);
            $this->db->execute();

            return true;
        }

        return false;
    }

    /**
     * Reimburse petty cash back to limit
     */
    public function reimburse(array $data, int $userId): bool {
        try {
            $config = $this->getConfig();
            $limit = $config ? floatval($config->limit_amount) : 0.00;
            $current = $this->getPettyCashAccountBalance();

            // Reimbursement amount check
            $reimburseAmount = floatval($data['amount']);
            if ($reimburseAmount <= 0) return false;

            // Restrict from exceeding the limit unless explicitly allowed
            // We'll enforce this limit check
            if ($current + $reimburseAmount > $limit) {
                return false; // Amount exceeds the limit required to restore petty cash
            }

            // Create unique transaction voucher ref
            $this->db->query("SELECT COUNT(id) as total FROM petty_cash_reimbursements");
            $countRow = $this->db->single();
            $nextId = $countRow ? ($countRow->total + 1) : 1;
            $reference = 'REIM-PC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Fetch petty cash account id (1020)
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1020' LIMIT 1");
            $pettyCashAccId = intval($this->db->single()->id);

            // Double Entry: Debit Petty Cash (1020), Credit Selected Funding Account
            $desc = "Petty Cash Reimbursement - Remarks: " . $data['remarks'];
            $lines = [
                ['account_id' => $pettyCashAccId, 'debit' => $reimburseAmount, 'credit' => 0, 'description' => $data['remarks']],
                ['account_id' => $data['funding_account_id'], 'debit' => 0, 'credit' => $reimburseAmount, 'description' => $data['remarks']]
            ];

            require_once APP_ROOT . '/app/Models/JournalEntry.php';
            $journalModel = new JournalEntry();

            $this->db->beginTransaction();

            $res = $journalModel->postEntry($data['reimbursement_date'], $reference, $desc, $lines, $userId);
            if ($res !== true) {
                $this->db->rollBack();
                return false;
            }

            // Find journal_entry_id
            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
            $this->db->bind(':ref', $reference);
            $je = $this->db->single();
            $jeId = $je ? intval($je->id) : null;

            // Save reimbursement record
            $this->db->query("
                INSERT INTO petty_cash_reimbursements (reimbursement_date, amount, funding_account_id, remarks, journal_entry_id, created_by)
                VALUES (:rdate, :amt, :funding, :remarks, :je_id, :uid)
            ");
            $this->db->bind(':rdate', $data['reimbursement_date']);
            $this->db->bind(':amt', $reimburseAmount);
            $this->db->bind(':funding', $data['funding_account_id']);
            $this->db->bind(':remarks', $data['remarks']);
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            $reimbursementId = $this->db->lastInsertId();

            // Link outstanding approved expenses to this reimbursement
            $this->db->query("
                UPDATE petty_cash_expenses 
                SET reimbursement_id = :reimburse_id 
                WHERE status = 'Approved' AND reimbursement_id IS NULL
            ");
            $this->db->bind(':reimburse_id', $reimbursementId);
            $this->db->execute();

            $this->db->commit();

            // Audit Log
            require_once APP_ROOT . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, 'Reimburse Cash', 'Petty Cash', "Reimbursed petty cash amount " . $reimburseAmount, $reimbursementId);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Transfer additional funds to petty cash
     */
    public function transferFunds(array $data, int $userId): bool {
        try {
            $transferAmount = floatval($data['amount']);
            if ($transferAmount <= 0) return false;

            // Create unique transaction voucher ref
            $this->db->query("SELECT COUNT(id) as total FROM petty_cash_transfers");
            $countRow = $this->db->single();
            $nextId = $countRow ? ($countRow->total + 1) : 1;
            $reference = 'XFER-PC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Fetch petty cash account id (1020)
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1020' LIMIT 1");
            $pettyCashAccId = intval($this->db->single()->id);

            // Double Entry: Debit Petty Cash (1020), Credit Source Account
            $desc = "Petty Cash Fund Transfer - Remarks: " . $data['remarks'];
            $lines = [
                ['account_id' => $pettyCashAccId, 'debit' => $transferAmount, 'credit' => 0, 'description' => $data['remarks']],
                ['account_id' => $data['source_account_id'], 'debit' => 0, 'credit' => $transferAmount, 'description' => $data['remarks']]
            ];

            require_once APP_ROOT . '/app/Models/JournalEntry.php';
            $journalModel = new JournalEntry();

            $this->db->beginTransaction();

            $res = $journalModel->postEntry($data['transfer_date'], $reference, $desc, $lines, $userId);
            if ($res !== true) {
                $this->db->rollBack();
                return false;
            }

            // Find journal_entry_id
            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
            $this->db->bind(':ref', $reference);
            $je = $this->db->single();
            $jeId = $je ? intval($je->id) : null;

            // Save transfer record
            $this->db->query("
                INSERT INTO petty_cash_transfers (transfer_date, amount, source_account_id, remarks, journal_entry_id, created_by)
                VALUES (:tdate, :amt, :source, :remarks, :je_id, :uid)
            ");
            $this->db->bind(':tdate', $data['transfer_date']);
            $this->db->bind(':amt', $transferAmount);
            $this->db->bind(':source', $data['source_account_id']);
            $this->db->bind(':remarks', $data['remarks']);
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            $transferId = $this->db->lastInsertId();

            $this->db->commit();

            // Audit Log
            require_once APP_ROOT . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, 'Fund Transfer', 'Petty Cash', "Transferred funds to petty cash amount " . $transferAmount, $transferId);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Get Petty Cash Ledger (Chronological transactions combining expenses, transfers, reimbursements)
     */
    public function getLedger(array $filters = []): array {
        // Query to reconstruct running balance from transactions table for account 1020
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1020' LIMIT 1");
        $coaRow = $this->db->single();
        if (!$coaRow) return [];
        $pettyCashAccId = intval($coaRow->id);

        $sql = "
            SELECT t.id as transaction_id, je.entry_date as date, je.reference as ref, 
                   je.description as description, t.debit, t.credit, je.id as journal_entry_id, 
                   u.username as creator_name,
                   (CASE 
                        WHEN je.reference LIKE 'EXP-PC-%' THEN 'Expense'
                        WHEN je.reference LIKE 'REIM-PC-%' THEN 'Reimbursement'
                        WHEN je.reference LIKE 'XFER-PC-%' THEN 'Transfer'
                        ELSE 'Other'
                    END) as type,
                   (SELECT category FROM petty_cash_expenses WHERE payment_reference = je.reference LIMIT 1) as category
            FROM transactions t
            JOIN journal_entries je ON t.journal_entry_id = je.id
            LEFT JOIN users u ON je.created_by = u.id
            WHERE t.account_id = :account_id AND je.status = 'Posted'
        ";

        $params = [':account_id' => $pettyCashAccId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND je.entry_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND je.entry_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND je.created_by = :user_id";
            $params[':user_id'] = intval($filters['user_id']);
        }
        if (!empty($filters['tx_type'])) {
            if ($filters['tx_type'] === 'Expense') {
                $sql .= " AND je.reference LIKE 'EXP-PC-%'";
            } elseif ($filters['tx_type'] === 'Reimbursement') {
                $sql .= " AND je.reference LIKE 'REIM-PC-%'";
            } elseif ($filters['tx_type'] === 'Transfer') {
                $sql .= " AND je.reference LIKE 'XFER-PC-%'";
            }
        }
        if (!empty($filters['category'])) {
            $sql .= " AND je.reference IN (SELECT payment_reference FROM petty_cash_expenses WHERE category = :category)";
            $params[':category'] = $filters['category'];
        }

        $sql .= " ORDER BY je.entry_date ASC, t.id ASC";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $ledger = $this->db->resultSet() ?: [];

        // Compute running balance chronologically
        $balance = 0.00;
        foreach ($ledger as $row) {
            $balance += floatval($row->debit);
            $balance -= floatval($row->credit);
            $row->running_balance = $balance;
        }

        // Return reversed array for table view (newest first)
        return array_reverse($ledger);
    }

    /**
     * Get Petty Cash Expenses List
     */
    public function getExpenses(array $filters = []): array {
        $sql = "
            SELECT e.*, coa.account_name as expense_account_name, v.name as vendor_name, 
                   u.username as creator_name, app.username as approver_name
            FROM petty_cash_expenses e
            JOIN chart_of_accounts coa ON e.expense_account_id = coa.id
            LEFT JOIN vendors v ON e.vendor_id = v.id
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN users app ON e.approved_by = app.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND e.expense_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND e.expense_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet() ?: [];
    }

    /**
     * Get outstanding approved expenses (not yet reimbursed)
     */
    public function getOutstandingExpenses(): array {
        $this->db->query("
            SELECT e.*, coa.account_name as expense_account_name, v.name as vendor_name
            FROM petty_cash_expenses e
            JOIN chart_of_accounts coa ON e.expense_account_id = coa.id
            LEFT JOIN vendors v ON e.vendor_id = v.id
            WHERE e.status = 'Approved' AND e.reimbursement_id IS NULL
            ORDER BY e.expense_date ASC
        ");
        return $this->db->resultSet() ?: [];
    }

    /**
     * Get previous reimbursements
     */
    public function getReimbursements(): array {
        $this->db->query("
            SELECT r.*, coa.account_name as funding_account_name, u.username as creator_name
            FROM petty_cash_reimbursements r
            JOIN chart_of_accounts coa ON r.funding_account_id = coa.id
            LEFT JOIN users u ON r.created_by = u.id
            ORDER BY r.reimbursement_date DESC, r.id DESC
        ");
        return $this->db->resultSet() ?: [];
    }
}
