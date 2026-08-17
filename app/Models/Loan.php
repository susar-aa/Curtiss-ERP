<?php
class Loan {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllBankAccounts() {
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600'");
        $parent = $this->db->single();
        $parentId = $parent ? $parent->id : 0;
        
        $this->db->query("SELECT id, account_code as account_number, account_name as bank_name FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $this->db->bind(':pid', $parentId);
        return $this->db->resultSet() ?: [];
    }

    public function getLoans() {
        $this->db->query("
            SELECT l.*, 
                   COALESCE((SELECT SUM(principal_amount) FROM loan_repayments WHERE loan_id = l.id), 0) as total_principal_paid,
                   COALESCE((SELECT SUM(interest_amount) FROM loan_repayments WHERE loan_id = l.id), 0) as total_interest_paid
            FROM loans l
            ORDER BY l.created_at DESC
        ");
        $loans = $this->db->resultSet();
        foreach ($loans as $l) {
            $l->principal_balance = floatval($l->principal_amount) - floatval($l->total_principal_paid);
        }
        return $loans;
    }

    public function getLoanById($id) {
        $this->db->query("
            SELECT l.*,
                   COALESCE((SELECT SUM(principal_amount) FROM loan_repayments WHERE loan_id = l.id), 0) as total_principal_paid,
                   COALESCE((SELECT SUM(interest_amount) FROM loan_repayments WHERE loan_id = l.id), 0) as total_interest_paid
            FROM loans l 
            WHERE l.id = :id
        ");
        $this->db->bind(':id', $id);
        $loan = $this->db->single();
        if ($loan) {
            $loan->principal_balance = floatval($loan->principal_amount) - floatval($loan->total_principal_paid);
        }
        return $loan;
    }

    public function addLoan($data) {
        $this->db->query("INSERT INTO loans 
            (liability_account_id, lender_name, loan_number, principal_amount, interest_rate, loan_start_date, loan_term_months, repayment_frequency, first_payment_date, maturity_date, status, notes) 
            VALUES 
            (:liability_acc, :lender, :loan_no, :principal, :interest, :start_date, :term, :freq, :first_pay, :maturity, :status, :notes)");
        
        $this->db->bind(':liability_acc', $data['liability_account_id']);
        $this->db->bind(':lender', $data['lender_name']);
        $this->db->bind(':loan_no', $data['loan_number']);
        $this->db->bind(':principal', $data['principal_amount']);
        $this->db->bind(':interest', $data['interest_rate']);
        $this->db->bind(':start_date', $data['loan_start_date']);
        $this->db->bind(':term', $data['loan_term_months']);
        $this->db->bind(':freq', $data['repayment_frequency']);
        $this->db->bind(':first_pay', !empty($data['first_payment_date']) ? $data['first_payment_date'] : null);
        $this->db->bind(':maturity', !empty($data['maturity_date']) ? $data['maturity_date'] : null);
        $this->db->bind(':status', 'Pending');
        $this->db->bind(':notes', $data['notes']);
        
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    public function updateLoan($data) {
        $this->db->query("UPDATE loans 
            SET liability_account_id = :liability_acc, lender_name = :lender, loan_number = :loan_no, 
                principal_amount = :principal, interest_rate = :interest, loan_start_date = :start_date, 
                loan_term_months = :term, repayment_frequency = :freq, first_payment_date = :first_pay, 
                maturity_date = :maturity, notes = :notes, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':liability_acc', $data['liability_account_id']);
        $this->db->bind(':lender', $data['lender_name']);
        $this->db->bind(':loan_no', $data['loan_number']);
        $this->db->bind(':principal', $data['principal_amount']);
        $this->db->bind(':interest', $data['interest_rate']);
        $this->db->bind(':start_date', !empty($data['loan_start_date']) ? $data['loan_start_date'] : null);
        $this->db->bind(':term', !empty($data['loan_term_months']) ? $data['loan_term_months'] : null);
        $this->db->bind(':freq', $data['repayment_frequency']);
        $this->db->bind(':first_pay', !empty($data['first_payment_date']) ? $data['first_payment_date'] : null);
        $this->db->bind(':maturity', !empty($data['maturity_date']) ? $data['maturity_date'] : null);
        $this->db->bind(':notes', $data['notes']);

        return $this->db->execute();
    }

    public function deleteLoan($id) {
        // Prevent deletion if the loan has already been disbursed or has repayments
        $loan = $this->getLoanById($id);
        if (!$loan) return false;
        
        // Cannot delete if active or closed
        if ($loan->status != 'Pending') {
            return false;
        }

        $this->db->query("DELETE FROM loans WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function disburseLoan($loanId, $bankAccountId, $processingFees, $userId) {
        $loan = $this->getLoanById($loanId);
        if (!$loan || $loan->status != 'Pending') return false;

        $this->db->query("UPDATE loans SET status = 'Active', bank_account_id = :bank_id, processing_fees = :fees WHERE id = :id");
        $this->db->bind(':bank_id', $bankAccountId);
        $this->db->bind(':fees', $processingFees);
        $this->db->bind(':id', $loanId);
        
        if ($this->db->execute()) {
            $this->syncDisbursementAccounting($loanId, $bankAccountId, $loan->principal_amount, $processingFees, $userId);
            return true;
        }
        return false;
    }

    public function addRepayment($data) {
        $this->db->query("INSERT INTO loan_repayments 
            (loan_id, payment_date, principal_amount, interest_amount, bank_charges, total_payment, bank_account_id, reference, notes, created_by) 
            VALUES 
            (:loan_id, :payment_date, :principal, :interest, :charges, :total, :bank_acc, :ref, :notes, :user_id)");
        
        $this->db->bind(':loan_id', $data['loan_id']);
        $this->db->bind(':payment_date', $data['payment_date']);
        $this->db->bind(':principal', $data['principal_amount']);
        $this->db->bind(':interest', $data['interest_amount']);
        $this->db->bind(':charges', $data['bank_charges']);
        $this->db->bind(':total', $data['total_payment']);
        $this->db->bind(':bank_acc', $data['bank_account_id']);
        $this->db->bind(':ref', $data['reference']);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':user_id', $data['created_by']);
        
        if ($this->db->execute()) {
            $repaymentId = $this->db->lastInsertId();
            $this->syncRepaymentAccounting($repaymentId, $data['loan_id'], $data['bank_account_id'], $data['principal_amount'], $data['interest_amount'], $data['bank_charges'], $data['total_payment'], $data['created_by']);
            
            // Check if fully paid
            $loan = $this->getLoanById($data['loan_id']);
            if ($loan && $loan->principal_balance <= 0) {
                $this->db->query("UPDATE loans SET status = 'Closed' WHERE id = :id");
                $this->db->bind(':id', $loan->id);
                $this->db->execute();
            }
            return true;
        }
        return false;
    }

    public function getRepayments($loanId) {
        $this->db->query("SELECT * FROM loan_repayments WHERE loan_id = :loan_id ORDER BY payment_date DESC, created_at DESC");
        $this->db->bind(':loan_id', $loanId);
        return $this->db->resultSet();
    }

    private function syncDisbursementAccounting($loanId, $bankAccountId, $principalAmount, $fees, $userId) {
        $loan = $this->getLoanById($loanId);
        if (!$loan) return;

        $bankCoaId = $bankAccountId; // The UI passes the chart_of_account_id directly
        $liabilityCoaId = $loan->liability_account_id;

        // Find Bank Charges Account for Fees (assuming 6100)
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '6100' LIMIT 1");
        $feesAccount = $this->db->single();
        $feesCoaId = $feesAccount ? $feesAccount->id : null;

        $ref = "LOAN-DISB-" . $loanId;
        $this->db->query("DELETE FROM journal_entries WHERE reference = :ref");
        $this->db->bind(':ref', $ref);
        $this->db->execute();

        $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) VALUES (:date, :ref, :desc, :uid, 'Posted')");
        $this->db->bind(':date', date('Y-m-d'));
        $this->db->bind(':ref', $ref);
        $this->db->bind(':desc', "Loan Disbursement - " . $loan->lender_name . ($loan->loan_number ? " ({$loan->loan_number})" : ""));
        $this->db->bind(':uid', $userId);
        $this->db->execute();
        
        $jeId = $this->db->lastInsertId();
        
        $netBankAmount = floatval($principalAmount) - floatval($fees);
        
        // Debit Bank
        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, :debit, 0, :desc)");
        $this->db->bind(':je_id', $jeId);
        $this->db->bind(':acc', $bankCoaId);
        $this->db->bind(':debit', $netBankAmount);
        $this->db->bind(':desc', "Loan Funds Received");
        $this->db->execute();

        // Debit Fees if applicable
        if (floatval($fees) > 0 && $feesCoaId) {
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, :debit, 0, :desc)");
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':acc', $feesCoaId);
            $this->db->bind(':debit', floatval($fees));
            $this->db->bind(':desc', "Loan Processing Fees");
            $this->db->execute();
        }

        // Credit Liability
        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, 0, :credit, :desc)");
        $this->db->bind(':je_id', $jeId);
        $this->db->bind(':acc', $liabilityCoaId);
        $this->db->bind(':credit', $principalAmount);
        $this->db->bind(':desc', "Loan Liability");
        $this->db->execute();
    }

    private function syncRepaymentAccounting($repaymentId, $loanId, $bankAccountId, $principal, $interest, $charges, $total, $userId) {
        $loan = $this->getLoanById($loanId);
        
        $bankCoaId = $bankAccountId; // The UI passes the chart_of_account_id directly
        $liabilityCoaId = $loan->liability_account_id;

        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '6100' LIMIT 1");
        $bankChargesAcc = $this->db->single();
        $bankChargesCoaId = $bankChargesAcc ? $bankChargesAcc->id : null;

        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '6150' LIMIT 1");
        $intExpAcc = $this->db->single();
        if (!$intExpAcc) {
            $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, is_active) VALUES ('6150', 'Interest Expense', 'Expense', 'Operating Expenses', 1)");
            $this->db->execute();
            $intExpCoaId = $this->db->lastInsertId();
        } else {
            $intExpCoaId = $intExpAcc->id;
        }

        $ref = "LOAN-REP-" . $repaymentId;
        
        $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) VALUES (:date, :ref, :desc, :uid, 'Posted')");
        $this->db->bind(':date', date('Y-m-d'));
        $this->db->bind(':ref', $ref);
        $this->db->bind(':desc', "Loan Repayment - " . $loan->lender_name . ($loan->loan_number ? " ({$loan->loan_number})" : ""));
        $this->db->bind(':uid', $userId);
        $this->db->execute();
        
        $jeId = $this->db->lastInsertId();

        // Credit Bank
        if ($total > 0) {
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, 0, :credit, :desc)");
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':acc', $bankCoaId);
            $this->db->bind(':credit', $total);
            $this->db->bind(':desc', "Loan Repayment");
            $this->db->execute();
        }

        // Debit Principal
        if ($principal > 0) {
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, :debit, 0, :desc)");
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':acc', $liabilityCoaId);
            $this->db->bind(':debit', $principal);
            $this->db->bind(':desc', "Principal Repayment");
            $this->db->execute();
        }

        // Debit Interest
        if ($interest > 0) {
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, :debit, 0, :desc)");
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':acc', $intExpCoaId);
            $this->db->bind(':debit', $interest);
            $this->db->bind(':desc', "Interest Payment");
            $this->db->execute();
        }

        // Debit Charges
        if ($charges > 0 && $bankChargesCoaId) {
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) VALUES (:je_id, :acc, :debit, 0, :desc)");
            $this->db->bind(':je_id', $jeId);
            $this->db->bind(':acc', $bankChargesCoaId);
            $this->db->bind(':debit', $charges);
            $this->db->bind(':desc', "Bank Charges");
            $this->db->execute();
        }
    }

    public function getDashboardStats() {
        $stats = new stdClass();
        
        $this->db->query("SELECT COUNT(id) as count, COALESCE(SUM(principal_amount), 0) as total_principal FROM loans WHERE status = 'Active'");
        $active = $this->db->single();
        
        $this->db->query("
            SELECT COALESCE(SUM(lr.principal_amount), 0) as paid_principal, COALESCE(SUM(lr.interest_amount), 0) as paid_interest 
            FROM loan_repayments lr 
            JOIN loans l ON lr.loan_id = l.id 
            WHERE l.status = 'Active'
        ");
        $paid = $this->db->single();
        
        $stats->active_loans = $active->count;
        $stats->total_principal = $active->total_principal;
        $stats->paid_principal = $paid->paid_principal;
        $stats->paid_interest = $paid->paid_interest;
        $stats->outstanding_principal = $stats->total_principal - $stats->paid_principal;
        
        return $stats;
    }
}
