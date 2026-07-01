<?php
class RecurringJournal {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllTemplates() {
        $this->db->query("SELECT * FROM recurring_journal_templates ORDER BY template_name ASC");
        return $this->db->resultSet() ?: [];
    }

    public function getTemplateById($id) {
        $this->db->query("SELECT * FROM recurring_journal_templates WHERE id = :id");
        $this->db->bind(':id', $id);
        $template = $this->db->single();
        
        if ($template) {
            $this->db->query("SELECT rl.*, c.account_code, c.account_name 
                              FROM recurring_journal_lines rl
                              JOIN chart_of_accounts c ON rl.account_id = c.id
                              WHERE rl.template_id = :id");
            $this->db->bind(':id', $id);
            $template->lines = $this->db->resultSet() ?: [];
        }
        
        return $template;
    }

    public function createTemplate($data) {
        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO recurring_journal_templates (template_name, frequency, day_of_month, description, is_active) 
                              VALUES (:name, :freq, :dom, :desc, :active)");
            $this->db->bind(':name', $data['template_name']);
            $this->db->bind(':freq', $data['frequency']);
            $this->db->bind(':dom', $data['day_of_month']);
            $this->db->bind(':desc', $data['description']);
            $this->db->bind(':active', $data['is_active'] ? 1 : 0);
            $this->db->execute();
            
            $templateId = $this->db->lastInsertId();

            foreach ($data['lines'] as $line) {
                $this->db->query("INSERT INTO recurring_journal_lines (template_id, account_id, debit, credit, description) 
                                  VALUES (:tid, :aid, :deb, :cred, :desc)");
                $this->db->bind(':tid', $templateId);
                $this->db->bind(':aid', $line['account_id']);
                $this->db->bind(':deb', $line['debit']);
                $this->db->bind(':cred', $line['credit']);
                $this->db->bind(':desc', !empty($line['description']) ? $line['description'] : null);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteTemplate($id) {
        $this->db->query("DELETE FROM recurring_journal_templates WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function postRecurringEntry($templateId, $date, $userId) {
        $template = $this->getTemplateById($templateId);
        if (!$template) {
            return 'Template not found.';
        }

        // Validate lines
        if (empty($template->lines)) {
            return 'No lines found in template.';
        }

        $totalDebit = 0;
        $totalCredit = 0;
        $lines = [];
        foreach ($template->lines as $line) {
            $totalDebit += floatval($line->debit);
            $totalCredit += floatval($line->credit);
            $lines[] = [
                'account_id' => $line->account_id,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'description' => $line->description
            ];
        }

        // Enforce balanced entry
        $totalDebitCents = bcmul(sprintf("%.2f", $totalDebit), '100', 0);
        $totalCreditCents = bcmul(sprintf("%.2f", $totalCredit), '100', 0);

        if ($totalDebitCents !== $totalCreditCents) {
            return 'Accounting Error: Template total debits must equal total credits.';
        }

        // Use JournalEntry model to post
        if (!class_exists('JournalEntry')) {
            require_once __DIR__ . '/JournalEntry.php';
        }
        $journalModel = new JournalEntry();
        
        $reference = "REC-" . $templateId . "-" . date('Ymd', strtotime($date));
        $description = "Recurring Entry: " . $template->template_name . " - " . $template->description;
        
        $postResult = $journalModel->postEntry($date, $reference, $description, $lines, $userId);
        
        if ($postResult === true) {
            // Update last posted date
            $this->db->query("UPDATE recurring_journal_templates SET last_posted_date = :date WHERE id = :id");
            $this->db->bind(':date', $date);
            $this->db->bind(':id', $templateId);
            $this->db->execute();
            return true;
        }

        return $postResult;
    }

    public function getPendingTemplates() {
        $templates = $this->getAllTemplates();
        $pending = [];
        $today = new DateTime();
        $currentMonth = intval($today->format('m'));
        $currentYear = intval($today->format('Y'));

        foreach ($templates as $t) {
            if (!$t->is_active) continue;

            $isDue = false;
            if (empty($t->last_posted_date)) {
                $isDue = true;
            } else {
                $last = new DateTime($t->last_posted_date);
                $lastMonth = intval($last->format('m'));
                $lastYear = intval($last->format('Y'));

                if ($t->frequency === 'Monthly') {
                    if ($currentYear > $lastYear || ($currentYear === $lastYear && $currentMonth > $lastMonth)) {
                        $isDue = true;
                    }
                } elseif ($t->frequency === 'Quarterly') {
                    $diffMonths = (($currentYear - $lastYear) * 12) + ($currentMonth - $lastMonth);
                    if ($diffMonths >= 3) {
                        $isDue = true;
                    }
                } elseif ($t->frequency === 'Annually') {
                    if ($currentYear > $lastYear) {
                        $isDue = true;
                    }
                }
            }

            if ($isDue) {
                $pending[] = $t;
            }
        }

        return $pending;
    }
}
