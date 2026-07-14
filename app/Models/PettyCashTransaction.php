<?php
declare(strict_types=1);

require_once __DIR__ . '/JournalEntry.php';
require_once __DIR__ . '/AuditLog.php';
require_once __DIR__ . '/ChartOfAccount.php';

class PettyCashTransaction {
    private Database $db;
    private AuditLog $audit;
    private JournalEntry $journal;

    public function __construct() {
        $this->db = new Database();
        $this->audit = new AuditLog();
        $this->journal = new JournalEntry();
    }

    /**
     * Get petty cash account ID
     */
    public function getPettyCashAccountId(): int {
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1020' LIMIT 1");
        $row = $this->db->single();
        return $row ? (int)$row->id : 10; // Fallback to 10 if not found
    }

    /**
     * Get the current balance of the Petty Cash account in the Chart of Accounts
     */
    public function getLedgerBalance(): float {
        $this->db->query("SELECT balance FROM chart_of_accounts WHERE account_code = '1020' LIMIT 1");
        $row = $this->db->single();
        return $row ? floatval($row->balance) : 0.0;
    }

    /**
     * Calculate Petty Cash balance from transaction ledger
     */
    public function calculateTransactionBalance(): float {
        $this->db->query("SELECT SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as balance 
                          FROM petty_cash_transactions 
                          WHERE status = 'Approved'");
        $row = $this->db->single();
        return $row && $row->balance !== null ? floatval($row->balance) : 0.0;
    }

    /**
     * Get available balance (ledger balance minus any pending expenses)
     */
    public function getAvailableBalance(): float {
        $ledger = $this->getLedgerBalance();
        $this->db->query("SELECT SUM(amount) as pending 
                          FROM petty_cash_transactions 
                          WHERE type = 'expense' AND status = 'Pending'");
        $row = $this->db->single();
        $pending = $row && $row->pending !== null ? floatval($row->pending) : 0.0;
        return max(0.0, $ledger - $pending);
    }

    /**
     * Get total pending reimbursements (approved expenses not yet reimbursed)
     */
    public function getPendingReimbursementsTotal(): float {
        $this->db->query("SELECT SUM(amount) as total 
                          FROM petty_cash_transactions 
                          WHERE type = 'expense' AND status = 'Approved' AND reimbursement_id IS NULL");
        $row = $this->db->single();
        return $row && $row->total !== null ? floatval($row->total) : 0.0;
    }

    /**
     * Get all petty cash transactions with filtering and pagination
     */
    public function getTransactions(array $filters = [], int $limit = 50, int $offset = 0): array {
        $sql = "SELECT t.*, u.username as creator_name, app.username as approver_name, a.account_name as offset_account_name, a.account_code as offset_account_code
                FROM petty_cash_transactions t
                LEFT JOIN users u ON t.created_by = u.id
                LEFT JOIN users app ON t.approved_by = app.id
                LEFT JOIN chart_of_accounts a ON t.account_id = a.id
                WHERE 1=1";
        
        $binds = [];

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $binds['type'] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $binds['status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.transaction_date >= :date_from";
            $binds['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.transaction_date <= :date_to";
            $binds['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.description LIKE :search OR t.paid_to LIKE :search OR t.reference LIKE :search)";
            $binds['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind(':' . $key, $val);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function getTransactionsCount(array $filters = []): int {
        $sql = "SELECT COUNT(*) as total FROM petty_cash_transactions t WHERE 1=1";
        $binds = [];

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $binds['type'] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $binds['status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.transaction_date >= :date_from";
            $binds['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.transaction_date <= :date_to";
            $binds['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.description LIKE :search OR t.paid_to LIKE :search OR t.reference LIKE :search)";
            $binds['search'] = '%' . $filters['search'] . '%';
        }

        $this->db->query($sql);
        foreach ($binds as $key => $val) {
            $this->db->bind(':' . $key, $val);
        }
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getTransactionById(int $id): ?stdClass {
        $this->db->query("SELECT t.*, u.username as creator_name, app.username as approver_name, a.account_name as offset_account_name, a.account_code as offset_account_code
                          FROM petty_cash_transactions t
                          LEFT JOIN users u ON t.created_by = u.id
                          LEFT JOIN users app ON t.approved_by = app.id
                          LEFT JOIN chart_of_accounts a ON t.account_id = a.id
                          WHERE t.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single() ?: null;
    }

    /**
     * Record a Fund Allocation (Transfer from Bank to Petty Cash)
     */
    public function recordAllocation(array $data, int $userId): string|bool {
        try {
            $amount = floatval($data['amount']);
            if ($amount <= 0) {
                return "Allocation amount must be greater than zero.";
            }

            $sourceAccId = intval($data['bank_account_id']);
            $pettyAccId = $this->getPettyCashAccountId();
            $date = $data['transaction_date'];
            $desc = !empty($data['description']) ? $data['description'] : "Petty Cash Fund Allocation";
            $ref = !empty($data['reference']) ? $data['reference'] : 'PC-AL-' . time();

            // Check if period is closed/locked
            $this->db->query("SELECT COUNT(*) as cnt FROM financial_years WHERE :entry_date BETWEEN start_date AND end_date");
            $this->db->bind(':entry_date', $date);
            $res = $this->db->single();
            if ($res && $res->cnt > 0) {
                return "The accounting period containing date {$date} is closed.";
            }

            // Create Journal entry: Debit Petty Cash, Credit Source Account
            $lines = [
                ['account_id' => $pettyAccId, 'debit' => $amount, 'credit' => 0.0, 'description' => $desc],
                ['account_id' => $sourceAccId, 'debit' => 0.0, 'credit' => $amount, 'description' => $desc]
            ];

            $postResult = $this->journal->postEntry($date, $ref, $desc, $lines, $userId);
            if ($postResult !== true) {
                return $postResult ?: "Failed to post journal entry.";
            }

            // Fetch the generated journal entry ID by reference
            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref ORDER BY id DESC LIMIT 1");
            $this->db->bind(':ref', $ref);
            $jeRow = $this->db->single();
            $journalId = $jeRow ? intval($jeRow->id) : null;

            if (!$journalId) {
                return "Failed to find generated journal entry.";
            }

            $this->db->beginTransaction();

            $this->db->query("INSERT INTO petty_cash_transactions (transaction_date, type, amount, reference, description, account_id, status, created_by, approved_by, approved_at, journal_entry_id) 
                              VALUES (:date, 'allocation', :amount, :ref, :desc, :acc_id, 'Approved', :uid, :uid, NOW(), :jid)");
            $this->db->bind(':date', $date);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':ref', $ref);
            $this->db->bind(':desc', $desc);
            $this->db->bind(':acc_id', $sourceAccId);
            $this->db->bind(':uid', $userId);
            $this->db->bind(':jid', $journalId);
            $this->db->execute();

            $txId = intval($this->db->lastInsertId());

            $this->db->commit();

            // Log action in audit trail
            $auditDesc = "Allocated {$amount} to Petty Cash from Bank Account ID {$sourceAccId}. Ref: {$ref}";
            $this->audit->logAction($userId, 'CREATE', 'accounting', $auditDesc, $txId, null, $data);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Record a Petty Cash Expense
     */
    public function recordExpense(array $data, int $userId): string|bool {
        try {
            $amount = floatval($data['amount']);
            if ($amount <= 0) {
                return "Expense amount must be greater than zero.";
            }

            $expenseAccId = intval($data['account_id']);
            $date = $data['transaction_date'];
            $desc = $data['description'];
            $paidTo = $data['paid_to'] ?? '';
            $ref = !empty($data['reference']) ? $data['reference'] : 'PC-EXP-' . time();
            $attachment = $data['attachment_path'] ?? null;

            // Fetch settings to check if approval is required
            $this->db->query("SELECT require_approval FROM petty_cash_config WHERE id = 1");
            $config = $this->db->single();
            $requireApproval = $config ? (bool)$config->require_approval : false;

            if ($requireApproval) {
                // Save as Pending transaction, no journal entry yet
                $this->db->beginTransaction();
                $this->db->query("INSERT INTO petty_cash_transactions (transaction_date, type, amount, reference, description, paid_to, account_id, status, attachment_path, created_by) 
                                  VALUES (:date, 'expense', :amount, :ref, :desc, :paid_to, :acc_id, 'Pending', :attach, :uid)");
                $this->db->bind(':date', $date);
                $this->db->bind(':amount', $amount);
                $this->db->bind(':ref', $ref);
                $this->db->bind(':desc', $desc);
                $this->db->bind(':paid_to', $paidTo);
                $this->db->bind(':acc_id', $expenseAccId);
                $this->db->bind(':attach', $attachment);
                $this->db->bind(':uid', $userId);
                $this->db->execute();

                $txId = intval($this->db->lastInsertId());
                $this->db->commit();

                // Log audit log
                $auditDesc = "Recorded pending Petty Cash expense of {$amount} paid to '{$paidTo}'. Ref: {$ref}";
                $this->audit->logAction($userId, 'CREATE', 'accounting', $auditDesc, $txId, null, $data);

                return true;
            } else {
                // Auto-approve and post journal entry immediately
                $pettyAccId = $this->getPettyCashAccountId();

                // Check period closed
                $this->db->query("SELECT COUNT(*) as cnt FROM financial_years WHERE :entry_date BETWEEN start_date AND end_date");
                $this->db->bind(':entry_date', $date);
                $res = $this->db->single();
                if ($res && $res->cnt > 0) {
                    return "The accounting period containing date {$date} is closed.";
                }

                // Debit Expense Account, Credit Petty Cash
                $lines = [
                    ['account_id' => $expenseAccId, 'debit' => $amount, 'credit' => 0.0, 'description' => $desc],
                    ['account_id' => $pettyAccId, 'debit' => 0.0, 'credit' => $amount, 'description' => $desc]
                ];

                $postResult = $this->journal->postEntry($date, $ref, $desc, $lines, $userId);
                if ($postResult !== true) {
                    return $postResult ?: "Failed to post journal entry.";
                }

                $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref ORDER BY id DESC LIMIT 1");
                $this->db->bind(':ref', $ref);
                $jeRow = $this->db->single();
                $journalId = $jeRow ? intval($jeRow->id) : null;

                $this->db->beginTransaction();
                $this->db->query("INSERT INTO petty_cash_transactions (transaction_date, type, amount, reference, description, paid_to, account_id, status, attachment_path, created_by, approved_by, approved_at, journal_entry_id) 
                                  VALUES (:date, 'expense', :amount, :ref, :desc, :paid_to, :acc_id, 'Approved', :attach, :uid, :uid, NOW(), :jid)");
                $this->db->bind(':date', $date);
                $this->db->bind(':amount', $amount);
                $this->db->bind(':ref', $ref);
                $this->db->bind(':desc', $desc);
                $this->db->bind(':paid_to', $paidTo);
                $this->db->bind(':acc_id', $expenseAccId);
                $this->db->bind(':attach', $attachment);
                $this->db->bind(':uid', $userId);
                $this->db->bind(':jid', $journalId);
                $this->db->execute();

                $txId = intval($this->db->lastInsertId());
                $this->db->commit();

                // Log audit
                $auditDesc = "Recorded & auto-approved Petty Cash expense of {$amount} paid to '{$paidTo}'. Ref: {$ref}";
                $this->audit->logAction($userId, 'CREATE', 'accounting', $auditDesc, $txId, null, $data);

                return true;
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Approve a Pending Expense
     */
    public function approveExpense(int $id, int $approverId): string|bool {
        try {
            $tx = $this->getTransactionById($id);
            if (!$tx) {
                return "Transaction not found.";
            }
            if ($tx->status !== 'Pending') {
                return "Only pending expenses can be approved.";
            }

            $date = $tx->transaction_date;
            $ref = $tx->reference ?: 'PC-EXP-' . $tx->id;
            $desc = "Approved Petty Cash Expense: " . $tx->description;
            $pettyAccId = $this->getPettyCashAccountId();
            $expenseAccId = (int)$tx->account_id;

            // Check period closed
            $this->db->query("SELECT COUNT(*) as cnt FROM financial_years WHERE :entry_date BETWEEN start_date AND end_date");
            $this->db->bind(':entry_date', $date);
            $res = $this->db->single();
            if ($res && $res->cnt > 0) {
                return "The accounting period containing date {$date} is closed.";
            }

            // Debit Expense Account, Credit Petty Cash
            $lines = [
                ['account_id' => $expenseAccId, 'debit' => (float)$tx->amount, 'credit' => 0.0, 'description' => $tx->description],
                ['account_id' => $pettyAccId, 'debit' => 0.0, 'credit' => (float)$tx->amount, 'description' => $tx->description]
            ];

            $postResult = $this->journal->postEntry($date, $ref, $desc, $lines, $approverId);
            if ($postResult !== true) {
                return $postResult ?: "Failed to post journal entry.";
            }

            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref ORDER BY id DESC LIMIT 1");
            $this->db->bind(':ref', $ref);
            $jeRow = $this->db->single();
            $journalId = $jeRow ? intval($jeRow->id) : null;

            $this->db->beginTransaction();

            $this->db->query("UPDATE petty_cash_transactions 
                              SET status = 'Approved', approved_by = :approver_id, approved_at = NOW(), journal_entry_id = :jid 
                              WHERE id = :id");
            $this->db->bind(':approver_id', $approverId);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();

            // Log action in audit trail
            $auditDesc = "Approved Petty Cash expense ID {$id} of amount {$tx->amount}. Ref: {$ref}";
            $this->audit->logAction($approverId, 'UPDATE', 'accounting', $auditDesc, $id, ['status' => 'Pending'], ['status' => 'Approved']);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Reject a Pending Expense
     */
    public function rejectExpense(int $id, int $approverId): string|bool {
        try {
            $tx = $this->getTransactionById($id);
            if (!$tx) {
                return "Transaction not found.";
            }
            if ($tx->status !== 'Pending') {
                return "Only pending expenses can be rejected.";
            }

            $this->db->beginTransaction();

            $this->db->query("UPDATE petty_cash_transactions 
                              SET status = 'Rejected', approved_by = :approver_id, approved_at = NOW() 
                              WHERE id = :id");
            $this->db->bind(':approver_id', $approverId);
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();

            // Log action in audit trail
            $auditDesc = "Rejected Petty Cash expense ID {$id} of amount {$tx->amount}.";
            $this->audit->logAction($approverId, 'UPDATE', 'accounting', $auditDesc, $id, ['status' => 'Pending'], ['status' => 'Rejected']);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Error: " . $e->getMessage();
        }
    }
}
