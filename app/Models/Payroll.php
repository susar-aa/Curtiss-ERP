<?php
declare(strict_types=1);

class Payroll {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllPayrollRuns(): array {
        $this->db->query("SELECT p.*, u.username 
                          FROM payroll_runs p 
                          JOIN users u ON p.created_by = u.id 
                          ORDER BY p.run_date DESC");
        return $this->db->resultSet() ?: [];
    }

    public function processPayroll(string $periodStart, string $periodEnd, string $runDate, float $totalGross, int $wageExpenseAccId, int $bankAccId, int $userId): bool {
        try {
            $desc = "Payroll Run: " . date('M d', strtotime($periodStart)) . " to " . date('M d, Y', strtotime($periodEnd));
            $this->db->query("SELECT COUNT(id) as total FROM payroll_runs");
            $countRow = $this->db->single();
            $nextId = $countRow ? ($countRow->total + 1) : 1;
            $reference = "PR-" . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            
            $lines = [
                ['account_id' => $wageExpenseAccId, 'debit' => $totalGross, 'credit' => 0, 'description' => 'Wage Expense'],
                ['account_id' => $bankAccId, 'debit' => 0, 'credit' => $totalGross, 'description' => 'Bank payment']
            ];

            require_once APP_ROOT . '/app/Models/JournalEntry.php';
            $journalModel = new JournalEntry();

            $postResult = $journalModel->postEntry($runDate, $reference, $desc, $lines, $userId);
            if ($postResult !== true) {
                return false;
            }

            // Fetch the generated journal entry ID by reference
            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
            $this->db->bind(':ref', $reference);
            $jeRow = $this->db->single();
            $journalId = $jeRow ? intval($jeRow->id) : null;

            if (!$journalId) {
                return false;
            }

            $this->db->beginTransaction();

            // Create Payroll Header record
            $this->db->query("INSERT INTO payroll_runs (run_date, period_start, period_end, total_gross, journal_entry_id, created_by) 
                              VALUES (:rdate, :pstart, :pend, :gross, :jid, :uid)");
            $this->db->bind(':rdate', $runDate);
            $this->db->bind(':pstart', $periodStart);
            $this->db->bind(':pend', $periodEnd);
            $this->db->bind(':gross', $totalGross);
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