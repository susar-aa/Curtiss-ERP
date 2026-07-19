<?php
class Cheque {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllCheques() {
        $this->db->query("SELECT ch.*, c.name as customer_name, v.name as vendor_name, ba.account_name as drawn_bank_name 
                          FROM cheques ch 
                          LEFT JOIN customers c ON ch.customer_id = c.id 
                          LEFT JOIN vendors v ON ch.vendor_id = v.id 
                          LEFT JOIN chart_of_accounts ba ON ch.bank_account_id = ba.id
                          ORDER BY ch.status ASC, ch.banking_date ASC");
        return $this->db->resultSet();
    }

    public function addCheque($data) {
        try {
            $this->db->beginTransaction();
            
            $this->db->query("INSERT INTO cheques (customer_id, vendor_id, bank_name, cheque_number, amount, banking_date, created_by, bank_account_id, status) 
                              VALUES (:cid, :vid, :bank, :cnum, :amt, :bdate, :uid, :bank_account_id, 'Pending')");
            $this->db->bind(':cid', !empty($data['customer_id']) ? $data['customer_id'] : null);
            $this->db->bind(':vid', !empty($data['vendor_id']) ? $data['vendor_id'] : null);
            $this->db->bind(':bank', $data['bank_name']);
            $this->db->bind(':cnum', $data['cheque_number']);
            $this->db->bind(':amt', $data['amount']);
            $this->db->bind(':bdate', $data['banking_date']);
            $this->db->bind(':uid', $data['created_by']);
            $this->db->bind(':bank_account_id', !empty($data['bank_account_id']) ? $data['bank_account_id'] : null);
            $this->db->execute();
            $chequeId = $this->db->lastInsertId();
            
            // Post Journal Entry
            $this->postChequeJournalEntry($chequeId, 'Pending', null);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("addCheque error: " . $e->getMessage());
            return false;
        }
    }

    public function updateCheque($data) {
        try {
            $this->db->beginTransaction();
            
            // Get old status and bank account id
            $this->db->query("SELECT status, bank_account_id FROM cheques WHERE id = :id FOR UPDATE");
            $this->db->bind(':id', $data['id']);
            $old = $this->db->single();
            
            $this->db->query("UPDATE cheques 
                              SET customer_id = :cid, vendor_id = :vid, bank_name = :bank, cheque_number = :cnum, 
                                  amount = :amt, banking_date = :bdate, status = :status, bank_account_id = :bank_account_id 
                              WHERE id = :id");
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':cid', !empty($data['customer_id']) ? $data['customer_id'] : null);
            $this->db->bind(':vid', !empty($data['vendor_id']) ? $data['vendor_id'] : null);
            $this->db->bind(':bank', $data['bank_name']);
            $this->db->bind(':cnum', $data['cheque_number']);
            $this->db->bind(':amt', $data['amount']);
            $this->db->bind(':bdate', $data['banking_date']);
            $this->db->bind(':status', $data['status']);
            $this->db->bind(':bank_account_id', !empty($data['bank_account_id']) ? $data['bank_account_id'] : null);
            $this->db->execute();
            
            // If status has changed or details updated, post adjustment / realization entries
            if ($old && ($old->status !== $data['status'])) {
                $this->postChequeJournalEntry($data['id'], $data['status'], $old->status);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("updateCheque error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCheque($id) {
        $this->db->query("DELETE FROM cheques WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    private function postChequeJournalEntry($chequeId, $newStatus, $oldStatus) {
        $this->db->query("SELECT * FROM cheques WHERE id = :id");
        $this->db->bind(':id', $chequeId);
        $chk = $this->db->single();
        if (!$chk) return;
        
        $amount = floatval($chk->amount);
        $chequeNum = $chk->cheque_number;
        $bankingDate = $chk->banking_date;
        $uid = $chk->created_by ?: ($_SESSION['user_id'] ?? 1);
        
        // Account IDs
        $chequeInHandId = $this->getAccountIdByCode('1010');
        $arAccountId = $this->getAccountIdByCode('1200');
        $apAccountId = $this->getAccountIdByCode('2100');
        
        if ($oldStatus === null && $newStatus === 'Pending') {
            // Cheque Created
            if ($chk->customer_id) {
                // Received Cheque
                $ref = 'CQ-RCV-' . $chequeNum;
                $desc = "Received Customer Cheque #" . $chequeNum;
                $lines = [
                    ['account_id' => $chequeInHandId, 'debit' => $amount, 'credit' => 0.00, 'description' => $desc],
                    ['account_id' => $arAccountId, 'debit' => 0.00, 'credit' => $amount, 'description' => $desc]
                ];
                $this->insertJournalEntry($bankingDate, $ref, $desc, $lines, $uid);
            } elseif ($chk->vendor_id && $chk->bank_account_id) {
                // Issued Cheque
                $ref = 'CQ-ISS-' . $chequeNum;
                $desc = "Issued Supplier Cheque #" . $chequeNum;
                $lines = [
                    ['account_id' => $apAccountId, 'debit' => $amount, 'credit' => 0.00, 'description' => $desc],
                    ['account_id' => $chk->bank_account_id, 'debit' => 0.00, 'credit' => $amount, 'description' => $desc]
                ];
                $this->insertJournalEntry($bankingDate, $ref, $desc, $lines, $uid);
            }
        }
        
        // Status Transition: -> Cleared (direct manual clear)
        if ($newStatus === 'Cleared' && $oldStatus !== 'Cleared') {
            if ($chk->customer_id && $chk->bank_account_id) {
                // Manually clear customer cheque directly to a bank account
                $ref = 'CQ-CLR-' . $chequeNum;
                $desc = "Cleared Customer Cheque #" . $chequeNum;
                $creditAccount = ($oldStatus === 'Deposited' || $oldStatus === 'Sent to Bank') 
                    ? ($this->getAccountIdByCode('1040') ?: $chequeInHandId) 
                    : $chequeInHandId;
                $lines = [
                    ['account_id' => $chk->bank_account_id, 'debit' => $amount, 'credit' => 0.00, 'description' => $desc],
                    ['account_id' => $creditAccount, 'debit' => 0.00, 'credit' => $amount, 'description' => $desc]
                ];
                $this->insertJournalEntry($bankingDate, $ref, $desc, $lines, $uid);
            }
        }
        
        // Status Transition: -> Bounced / Returned (direct manual bounce)
        if (($newStatus === 'Bounced' || $newStatus === 'Returned') && $oldStatus !== $newStatus) {
            if ($chk->customer_id) {
                // Bounced customer cheque: Re-open Accounts Receivable
                $ref = 'CQ-BNC-' . $chequeNum;
                $desc = "Bounced Customer Cheque #" . $chequeNum;
                $creditAccount = ($oldStatus === 'Deposited' || $oldStatus === 'Sent to Bank') 
                    ? ($this->getAccountIdByCode('1040') ?: $chequeInHandId) 
                    : $chequeInHandId;
                $lines = [
                    ['account_id' => $arAccountId, 'debit' => $amount, 'credit' => 0.00, 'description' => $desc],
                    ['account_id' => $creditAccount, 'debit' => 0.00, 'credit' => $amount, 'description' => $desc]
                ];
                $this->insertJournalEntry($bankingDate, $ref, $desc, $lines, $uid);

                // Reverse corresponding active customer payment and re-open invoices to Unpaid
                $this->db->query("SELECT p.* FROM customer_payments p
                                  WHERE p.customer_id = :cid AND p.amount = :amt AND p.payment_method = 'Cheque' AND p.status = 'Active' 
                                  ORDER BY p.id DESC LIMIT 1");
                $this->db->bind(':cid', $chk->customer_id);
                $this->db->bind(':amt', $amount);
                $payment = $this->db->single();

                if ($payment) {
                    $this->db->query("UPDATE customer_payments SET status = 'Bounced', reversed_by = :uid, reversed_at = CURRENT_TIMESTAMP() WHERE id = :pid");
                    $this->db->bind(':uid', intval($uid));
                    $this->db->bind(':pid', intval($payment->id));
                    $this->db->execute();

                    $this->db->query("SELECT invoice_id FROM customer_payment_allocations WHERE customer_payment_id = :pid AND is_reversed = 0");
                    $this->db->bind(':pid', intval($payment->id));
                    $allocs = $this->db->resultSet() ?: [];

                    $this->db->query("UPDATE customer_payment_allocations SET is_reversed = 1, reversed_by = :uid, reversed_at = CURRENT_TIMESTAMP() WHERE customer_payment_id = :pid");
                    $this->db->bind(':uid', intval($uid));
                    $this->db->bind(':pid', intval($payment->id));
                    $this->db->execute();

                    foreach ($allocs as $a) {
                        $this->db->query("UPDATE invoices SET status = 'Unpaid' WHERE id = :id");
                        $this->db->bind(':id', intval($a->invoice_id));
                        $this->db->execute();
                    }
                }
            } elseif ($chk->vendor_id && $chk->bank_account_id) {
                // Bounced/Cancelled supplier cheque (reverses the payment)
                $ref = 'CQ-CAN-' . $chequeNum;
                $desc = "Cancelled Supplier Cheque #" . $chequeNum;
                $lines = [
                    ['account_id' => $chk->bank_account_id, 'debit' => $amount, 'credit' => 0.00, 'description' => $desc],
                    ['account_id' => $apAccountId, 'debit' => 0.00, 'credit' => $amount, 'description' => $desc]
                ];
                $this->insertJournalEntry($bankingDate, $ref, $desc, $lines, $uid);
            }
        }
    }
    
    private function getAccountIdByCode($code) {
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = :code LIMIT 1");
        $this->db->bind(':code', $code);
        $row = $this->db->single();
        return $row ? intval($row->id) : null;
    }
    
    private function insertJournalEntry($date, $ref, $desc, $lines, $uid) {
        $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                          VALUES (:date, :ref, :desc, :uid, 'Posted')");
        $this->db->bind(':date', $date);
        $this->db->bind(':ref', $ref);
        $this->db->bind(':desc', $desc);
        $this->db->bind(':uid', $uid);
        $this->db->execute();
        $jeId = $this->db->lastInsertId();
        
        foreach ($lines as $line) {
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) 
                              VALUES (:jid, :aid, :deb, :cred, :desc)");
            $this->db->bind(':jid', $jeId);
            $this->db->bind(':aid', $line['account_id']);
            $this->db->bind(':deb', $line['debit']);
            $this->db->bind(':cred', $line['credit']);
            $this->db->bind(':desc', $line['description']);
            $this->db->execute();
            
            // Update ledger account balance
            $this->db->query("SELECT account_type FROM chart_of_accounts WHERE id = :id FOR UPDATE");
            $this->db->bind(':id', $line['account_id']);
            $account = $this->db->single();
            
            $balanceUpdateSql = "UPDATE chart_of_accounts SET balance = balance ";
            if ($account && in_array($account->account_type, ['Asset', 'Expense'])) {
                $balanceUpdateSql .= "+ :debit - :credit ";
            } else {
                $balanceUpdateSql .= "- :debit + :credit ";
            }
            $balanceUpdateSql .= "WHERE id = :id";
            
            $this->db->query($balanceUpdateSql);
            $this->db->bind(':debit', $line['debit']);
            $this->db->bind(':credit', $line['credit']);
            $this->db->bind(':id', $line['account_id']);
            $this->db->execute();
        }
    }
}