<?php
declare(strict_types=1);

require_once __DIR__ . '/JournalEntry.php';
require_once __DIR__ . '/AuditLog.php';
require_once __DIR__ . '/PettyCashTransaction.php';

class PettyCashReimbursement {
    private Database $db;
    private AuditLog $audit;
    private JournalEntry $journal;
    private PettyCashTransaction $pcTx;

    public function __construct() {
        $this->db = new Database();
        $this->audit = new AuditLog();
        $this->journal = new JournalEntry();
        $this->pcTx = new PettyCashTransaction();
    }

    /**
     * Get pending expenses (approved but not yet reimbursed)
     */
    public function getPendingExpenses(): array {
        $this->db->query("SELECT t.*, u.username as creator_name, a.account_name as offset_account_name, a.account_code as offset_account_code
                          FROM petty_cash_transactions t
                          LEFT JOIN users u ON t.created_by = u.id
                          LEFT JOIN chart_of_accounts a ON t.account_id = a.id
                          WHERE t.type = 'expense' AND t.status = 'Approved' AND t.reimbursement_id IS NULL
                          ORDER BY t.transaction_date ASC, t.id ASC");
        return $this->db->resultSet() ?: [];
    }

    /**
     * Get all reimbursement requests
     */
    public function getAllReimbursements(): array {
        $this->db->query("SELECT r.*, u.username as creator_name, app.username as approver_name, a.account_name as bank_account_name, a.account_code as bank_account_code
                          FROM petty_cash_reimbursements r
                          LEFT JOIN users u ON r.created_by = u.id
                          LEFT JOIN users app ON r.approved_by = u.id
                          LEFT JOIN chart_of_accounts a ON r.bank_account_id = a.id
                          ORDER BY r.reimbursement_date DESC, r.id DESC");
        return $this->db->resultSet() ?: [];
    }

    public function getReimbursementById(int $id): ?stdClass {
        $this->db->query("SELECT r.*, u.username as creator_name, app.username as approver_name, a.account_name as bank_account_name, a.account_code as bank_account_code
                          FROM petty_cash_reimbursements r
                          LEFT JOIN users u ON r.created_by = u.id
                          LEFT JOIN users app ON r.approved_by = app.id
                          LEFT JOIN chart_of_accounts a ON r.bank_account_id = a.id
                          WHERE r.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single() ?: null;
    }

    /**
     * Get all expenses linked to a specific reimbursement
     */
    public function getLinkedExpenses(int $reimbursementId): array {
        $this->db->query("SELECT t.*, u.username as creator_name, a.account_name as offset_account_name, a.account_code as offset_account_code
                          FROM petty_cash_transactions t
                          LEFT JOIN users u ON t.created_by = u.id
                          LEFT JOIN chart_of_accounts a ON t.account_id = a.id
                          WHERE t.reimbursement_id = :rid AND t.type = 'expense'
                          ORDER BY t.transaction_date ASC");
        $this->db->bind(':rid', $reimbursementId);
        return $this->db->resultSet() ?: [];
    }

    /**
     * Create a new reimbursement request
     */
    public function createRequest(array $data, int $userId): string|bool {
        try {
            // Get all approved unreimbursed expenses
            $pendingExpenses = $this->getPendingExpenses();
            if (empty($pendingExpenses)) {
                return "There are no approved, unreimbursed expenses to group into a reimbursement request.";
            }

            $totalAmount = 0.0;
            $expenseIds = [];
            foreach ($pendingExpenses as $exp) {
                $totalAmount += floatval($exp->amount);
                $expenseIds[] = (int)$exp->id;
            }

            $bankAccountId = intval($data['bank_account_id']);
            $date = $data['reimbursement_date'];
            $description = $data['description'] ?? 'Petty Cash Reimbursement Request';

            $this->db->beginTransaction();

            // Insert reimbursement header
            $this->db->query("INSERT INTO petty_cash_reimbursements (reimbursement_date, amount, bank_account_id, status, description, created_by) 
                              VALUES (:date, :amount, :bank_id, 'Pending', :desc, :uid)");
            $this->db->bind(':date', $date);
            $this->db->bind(':amount', $totalAmount);
            $this->db->bind(':bank_id', $bankAccountId);
            $this->db->bind(':desc', $description);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            $reimbursementId = intval($this->db->lastInsertId());

            // Link pending expenses to this reimbursement
            $this->db->query("UPDATE petty_cash_transactions 
                              SET reimbursement_id = :rid 
                              WHERE id IN (" . implode(',', $expenseIds) . ")");
            $this->db->bind(':rid', $reimbursementId);
            $this->db->execute();

            $this->db->commit();

            // Log action in audit trail
            $auditDesc = "Created Petty Cash reimbursement request ID {$reimbursementId} for amount {$totalAmount}. Linked " . count($expenseIds) . " expenses.";
            $this->audit->logAction($userId, 'CREATE', 'accounting', $auditDesc, $reimbursementId, null, [
                'reimbursement_id' => $reimbursementId,
                'amount' => $totalAmount,
                'bank_account_id' => $bankAccountId,
                'expenses' => $expenseIds
            ]);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Approve and disburse a reimbursement request
     */
    public function approveRequest(int $id, int $approverId): string|bool {
        try {
            $reim = $this->getReimbursementById($id);
            if (!$reim) {
                return "Reimbursement request not found.";
            }
            if ($reim->status !== 'Pending') {
                return "Only pending reimbursement requests can be approved.";
            }

            $date = $reim->reimbursement_date;
            $amount = floatval($reim->amount);
            $bankAccId = (int)$reim->bank_account_id;
            $pettyAccId = $this->pcTx->getPettyCashAccountId();
            $desc = "Reimbursement Replenishment: " . $reim->description;
            $ref = 'PC-REIM-' . $reim->id;

            // Check if period is closed/locked
            $this->db->query("SELECT COUNT(*) as cnt FROM financial_years WHERE :entry_date BETWEEN start_date AND end_date");
            $this->db->bind(':entry_date', $date);
            $res = $this->db->single();
            if ($res && $res->cnt > 0) {
                return "The accounting period containing date {$date} is closed.";
            }

            // Post Journal Entry: Debit Petty Cash, Credit Bank
            $lines = [
                ['account_id' => $pettyAccId, 'debit' => $amount, 'credit' => 0.0, 'description' => $desc],
                ['account_id' => $bankAccId, 'debit' => 0.0, 'credit' => $amount, 'description' => $desc]
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

            // Update reimbursement status
            $this->db->query("UPDATE petty_cash_reimbursements 
                              SET status = 'Approved', approved_by = :approver_id, approved_at = NOW(), journal_entry_id = :jid 
                              WHERE id = :id");
            $this->db->bind(':approver_id', $approverId);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Create a matching transaction record in petty_cash_transactions
            $this->db->query("INSERT INTO petty_cash_transactions (transaction_date, type, amount, reference, description, account_id, status, created_by, approved_by, approved_at, journal_entry_id, reimbursement_id) 
                              VALUES (:date, 'reimbursement', :amount, :ref, :desc, :bank_id, 'Approved', :approver_id, :approver_id, NOW(), :jid, :rid)");
            $this->db->bind(':date', $date);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':ref', $ref);
            $this->db->bind(':desc', $desc);
            $this->db->bind(':bank_id', $bankAccId);
            $this->db->bind(':approver_id', $approverId);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':rid', $id);
            $this->db->execute();

            $this->db->commit();

            // Log action in audit trail
            $auditDesc = "Approved and disbursed Petty Cash reimbursement request ID {$id} of amount {$amount}. Ref: {$ref}";
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
     * Reject a reimbursement request
     */
    public function rejectRequest(int $id, int $approverId): string|bool {
        try {
            $reim = $this->getReimbursementById($id);
            if (!$reim) {
                return "Reimbursement request not found.";
            }
            if ($reim->status !== 'Pending') {
                return "Only pending reimbursement requests can be rejected.";
            }

            $this->db->beginTransaction();

            // Update reimbursement status
            $this->db->query("UPDATE petty_cash_reimbursements 
                              SET status = 'Rejected', approved_by = :approver_id, approved_at = NOW() 
                              WHERE id = :id");
            $this->db->bind(':approver_id', $approverId);
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Reset the reimbursement_id of all linked expenses so they can be grouped again later
            $this->db->query("UPDATE petty_cash_transactions 
                              SET reimbursement_id = NULL 
                              WHERE reimbursement_id = :rid AND type = 'expense'");
            $this->db->bind(':rid', $id);
            $this->db->execute();

            $this->db->commit();

            // Log action in audit trail
            $auditDesc = "Rejected Petty Cash reimbursement request ID {$id} and unlocked all linked expenses.";
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
