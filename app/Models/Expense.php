<?php
declare(strict_types=1);

class Expense {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllExpenses(): array {
        $this->db->query("SELECT e.*, v.name as vendor_name 
                          FROM expenses e 
                          LEFT JOIN vendors v ON e.vendor_id = v.id 
                          ORDER BY e.expense_date DESC, e.id DESC");
        return $this->db->resultSet() ?: [];
    }

    public function createExpenseWithAccounting(array $data, int $expenseAccountId, int $paymentAccountId, int $userId): bool {
        try {
            $desc = "Expense recorded: " . $data['description'];
            $ref = !empty($data['reference']) ? $data['reference'] : 'EXP-' . time();

            $lines = [
                ['account_id' => $expenseAccountId, 'debit' => $data['amount'], 'credit' => 0, 'description' => $data['description']],
                ['account_id' => $paymentAccountId, 'debit' => 0, 'credit' => $data['amount'], 'description' => $data['description']]
            ];

            require_once APP_ROOT . '/app/Models/JournalEntry.php';
            $journalModel = new JournalEntry();

            $postResult = $journalModel->postEntry($data['expense_date'], $ref, $desc, $lines, $userId);
            if ($postResult !== true) {
                return false;
            }

            // Fetch the generated journal entry ID by reference
            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
            $this->db->bind(':ref', $ref);
            $jeRow = $this->db->single();
            $journalId = $jeRow ? intval($jeRow->id) : null;

            if (!$journalId) {
                return false;
            }

            $this->db->beginTransaction();

            // Create Expense Header
            $this->db->query("INSERT INTO expenses (reference, vendor_id, expense_date, amount, description, journal_entry_id, created_by) 
                              VALUES (:ref, :vid, :edate, :amt, :desc, :jid, :uid)");
            $this->db->bind(':ref', $ref);
            $this->db->bind(':vid', $data['vendor_id'] ?: null);
            $this->db->bind(':edate', $data['expense_date']);
            $this->db->bind(':amt', $data['amount']);
            $this->db->bind(':desc', $data['description']);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}