<?php
class ChartOfAccount {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAccounts() {
        $this->db->query("SELECT * FROM chart_of_accounts ORDER BY FIELD(account_type, 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'), account_code ASC");
        return $this->db->resultSet();
    }

    public function addAccount($data) {
        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type) VALUES (:account_code, :account_name, :account_type)");
        
        $this->db->bind(':account_code', $data['account_code']);
        $this->db->bind(':account_name', $data['account_name']);
        $this->db->bind(':account_type', $data['account_type']);

        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            // Usually triggers if account_code is not unique
            return false; 
        }
    }

    // --- NEW: Methods for Bank Reconciliation ---

    // Get a specific account by ID
    public function getAccountById($id) {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get all UNCLEARED transactions for a specific account
    public function getUnclearedTransactions($accountId) {
        $this->db->query("SELECT t.*, je.entry_date, je.reference, je.description 
                          FROM transactions t 
                          JOIN journal_entries je ON t.journal_entry_id = je.id 
                          WHERE t.account_id = :aid AND t.is_cleared = 0 
                          ORDER BY je.entry_date ASC");
        $this->db->bind(':aid', $accountId);
        return $this->db->resultSet();
    }

    // Mark specific transactions as cleared
    public function clearTransactions($transactionIds) {
        if (empty($transactionIds)) return true;

        // Create secure dynamic placeholders for the IN clause
        $placeholders = [];
        foreach($transactionIds as $key => $val) {
            $placeholders[] = ":id" . $key;
        }
        $placeholderString = implode(',', $placeholders);
        
        $this->db->query("UPDATE transactions SET is_cleared = 1 WHERE id IN ($placeholderString)");
        
        // Bind the values dynamically
        foreach($transactionIds as $key => $val) {
            $this->db->bind(":id" . $key, $val);
        }
        
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}