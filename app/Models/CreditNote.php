<?php
class CreditNote {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllCreditNotes() {
        $this->db->query("SELECT cn.*, c.name as customer_name 
                          FROM credit_notes cn 
                          JOIN customers c ON cn.customer_id = c.id 
                          ORDER BY cn.created_at DESC");
        return $this->db->resultSet();
    }

    public function getCreditNoteById($id) {
        $this->db->query("SELECT cn.*, c.name as customer_name, c.email, c.phone, c.address 
                          FROM credit_notes cn 
                          JOIN customers c ON cn.customer_id = c.id 
                          WHERE cn.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getCreditNoteItems($id) {
        $this->db->query("SELECT * FROM credit_note_items WHERE credit_note_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    public function createCreditNoteWithAccounting($noteData, $items, $arAccountId, $revenueAccountId, $userId) {
        try {
            $this->db->beginTransaction();

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += ($item['qty'] * $item['price']);
            }

            // 1. Post Reverse Journal Entry (Debit Revenue, Credit AR)
            $desc = "Credit Note Issued: " . $noteData['credit_note_number'];
            $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                              VALUES (:date, :ref, :desc, :user, 'Posted')");
            $this->db->bind(':date', $noteData['date']);
            $this->db->bind(':ref', $noteData['credit_note_number']);
            $this->db->bind(':desc', $desc);
            $this->db->bind(':user', $userId);
            $this->db->execute();
            
            $journalId = $this->db->lastInsertId();

            // Note: Normal Sale is Debit AR, Credit Revenue.
            // Credit Note is Debit Revenue (Decrease Income), Credit AR (Decrease Receivable).
            $lines = [
                ['account_id' => $revenueAccountId, 'debit' => $totalAmount, 'credit' => 0],
                ['account_id' => $arAccountId, 'debit' => 0, 'credit' => $totalAmount]
            ];

            foreach ($lines as $line) {
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) 
                                  VALUES (:jid, :aid, :deb, :cred)");
                $this->db->bind(':jid', $journalId);
                $this->db->bind(':aid', $line['account_id']);
                $this->db->bind(':deb', $line['debit']);
                $this->db->bind(':cred', $line['credit']);
                $this->db->execute();

                // Update Chart of Accounts
                $this->db->query("SELECT account_type FROM chart_of_accounts WHERE id = :id");
                $this->db->bind(':id', $line['account_id']);
                $acc = $this->db->single();

                $sql = "UPDATE chart_of_accounts SET balance = balance ";
                if (in_array($acc->account_type, ['Asset', 'Expense'])) {
                    $sql .= "+ :debit - :credit ";
                } else {
                    $sql .= "- :debit + :credit ";
                }
                $sql .= "WHERE id = :id";
                
                $this->db->query($sql);
                $this->db->bind(':debit', $line['debit']);
                $this->db->bind(':credit', $line['credit']);
                $this->db->bind(':id', $line['account_id']);
                $this->db->execute();
            }

            // 2. Create Credit Note Header
            $this->db->query("INSERT INTO credit_notes (credit_note_number, customer_id, note_date, total_amount, journal_entry_id, created_by) 
                              VALUES (:cn_num, :cust_id, :ndate, :total, :jid, :uid)");
            $this->db->bind(':cn_num', $noteData['credit_note_number']);
            $this->db->bind(':cust_id', $noteData['customer_id']);
            $this->db->bind(':ndate', $noteData['date']);
            $this->db->bind(':total', $totalAmount);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            
            $cnId = $this->db->lastInsertId();

            // 3. Create Credit Note Items
            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                $this->db->query("INSERT INTO credit_note_items (credit_note_id, description, quantity, unit_price, total) 
                                  VALUES (:cnid, :desc, :qty, :price, :total)");
                $this->db->bind(':cnid', $cnId);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':price', $item['price']);
                $this->db->bind(':total', $itemTotal);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}