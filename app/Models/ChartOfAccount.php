<?php
class ChartOfAccount {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAccounts() {
        $this->db->query("SELECT c.*, p.account_name as parent_name 
                          FROM chart_of_accounts c 
                          LEFT JOIN chart_of_accounts p ON c.parent_id = p.id 
                          ORDER BY FIELD(c.account_type, 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'), c.account_code ASC");
        return $this->db->resultSet();
    }

    public function getAccountById($id) {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addAccount($data) {
        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) 
                          VALUES (:account_code, :account_name, :account_type, :parent_id)");
        
        $this->db->bind(':account_code', $data['account_code']);
        $this->db->bind(':account_name', $data['account_name']);
        $this->db->bind(':account_type', $data['account_type']);
        $this->db->bind(':parent_id', !empty($data['parent_id']) ? $data['parent_id'] : null);

        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    public function updateAccount($data) {
        $this->db->query("UPDATE chart_of_accounts 
                          SET account_code = :code, account_name = :name, account_type = :type, parent_id = :pid, is_active = :status 
                          WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':code', $data['account_code']);
        $this->db->bind(':name', $data['account_name']);
        $this->db->bind(':type', $data['account_type']);
        $this->db->bind(':pid', !empty($data['parent_id']) ? $data['parent_id'] : null);
        $this->db->bind(':status', $data['is_active']);
        
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    public function deleteAccount($id) {
        $this->db->query("DELETE FROM chart_of_accounts WHERE id = :id");
        $this->db->bind(':id', $id);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    // --- Bank Reconciliation Methods ---
    public function getUnclearedTransactions($accountId) {
        $this->db->query("SELECT t.*, je.entry_date, je.reference, je.description 
                          FROM transactions t 
                          JOIN journal_entries je ON t.journal_entry_id = je.id 
                          WHERE t.account_id = :aid AND t.is_cleared = 0 
                          ORDER BY je.entry_date ASC");
        $this->db->bind(':aid', $accountId);
        return $this->db->resultSet();
    }

    public function clearTransactions($transactionIds) {
        if (empty($transactionIds)) return true;
        $placeholders = [];
        foreach($transactionIds as $key => $val) { $placeholders[] = ":id" . $key; }
        $placeholderString = implode(',', $placeholders);
        
        $this->db->query("UPDATE transactions SET is_cleared = 1 WHERE id IN ($placeholderString)");
        foreach($transactionIds as $key => $val) { $this->db->bind(":id" . $key, $val); }
        
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }
}