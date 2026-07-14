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
                          ORDER BY FIELD(c.account_type, :asset, :liability, :equity, :revenue, :expense), c.account_code ASC");
        $this->db->bind(':asset', COA_TYPE_ASSET);
        $this->db->bind(':liability', COA_TYPE_LIABILITY);
        $this->db->bind(':equity', COA_TYPE_EQUITY);
        $this->db->bind(':revenue', COA_TYPE_REVENUE);
        $this->db->bind(':expense', COA_TYPE_EXPENSE);
        return $this->db->resultSet();
    }

    public function getAccountById($id) {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addAccount($data) {
        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, parent_id) 
                          VALUES (:account_code, :account_name, :account_type, :account_category, :parent_id)");
        
        $this->db->bind(':account_code', $data['account_code']);
        $this->db->bind(':account_name', $data['account_name']);
        $this->db->bind(':account_type', $data['account_type']);
        $this->db->bind(':account_category', $data['account_category'] ?? null);
        $this->db->bind(':parent_id', !empty($data['parent_id']) ? $data['parent_id'] : null);

        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    public function updateAccount($data) {
        $this->db->query("UPDATE chart_of_accounts 
                          SET account_code = :code, account_name = :name, account_type = :type, account_category = :category, parent_id = :pid, is_active = :status 
                          WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':code', $data['account_code']);
        $this->db->bind(':name', $data['account_name']);
        $this->db->bind(':type', $data['account_type']);
        $this->db->bind(':category', $data['account_category'] ?? null);
        $this->db->bind(':pid', !empty($data['parent_id']) ? $data['parent_id'] : null);
        $this->db->bind(':status', $data['is_active']);
        
        try {
            $res = $this->db->execute();
            if ($res) {
                $this->cascadeAccountType($data['id'], $data['account_type']);
                $this->cascadeAccountCategory($data['id'], $data['account_category'] ?? null);
            }
            return $res;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cascadeAccountType($parentId, $type) {
        $tempDb = new Database();
        $tempDb->query("SELECT id FROM chart_of_accounts WHERE parent_id = :parent_id");
        $tempDb->bind(':parent_id', $parentId);
        $children = $tempDb->resultSet();

        if (!empty($children)) {
            $tempDb->query("UPDATE chart_of_accounts SET account_type = :type WHERE parent_id = :parent_id");
            $tempDb->bind(':type', $type);
            $tempDb->bind(':parent_id', $parentId);
            $tempDb->execute();

            foreach ($children as $child) {
                $this->cascadeAccountType($child->id, $type);
            }
        }
    }

    public function cascadeAccountCategory($parentId, $category) {
        $tempDb = new Database();
        $tempDb->query("SELECT id FROM chart_of_accounts WHERE parent_id = :parent_id");
        $tempDb->bind(':parent_id', $parentId);
        $children = $tempDb->resultSet();

        if (!empty($children)) {
            $tempDb->query("UPDATE chart_of_accounts SET account_category = :category WHERE parent_id = :parent_id");
            $tempDb->bind(':category', $category);
            $tempDb->bind(':parent_id', $parentId);
            $tempDb->execute();

            foreach ($children as $child) {
                $this->cascadeAccountCategory($child->id, $category);
            }
        }
    }

    public function deleteAccount($id) {
        $this->db->query("DELETE FROM chart_of_accounts WHERE id = :id");
        $this->db->bind(':id', $id);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }

    /**
     * Compute cumulative prior transaction sums before a starting date
     */
    public function getPriorBalance($accountId, $startDate) {
        $this->db->query("SELECT SUM(t.debit) as total_debit, SUM(t.credit) as total_credit 
                          FROM transactions t 
                          JOIN journal_entries je ON t.journal_entry_id = je.id 
                          WHERE t.account_id = :account_id AND je.entry_date < :start_date AND je.status = 'Posted'");
        $this->db->bind(':account_id', $accountId);
        $this->db->bind(':start_date', $startDate);
        $row = $this->db->single();
        return $row ? $row : (object)['total_debit' => 0, 'total_credit' => 0];
    }

    /**
     * Fetch filtered chronological transaction entries for account ledger
     */
    public function getAccountHistory($accountId, $filters = []) {
        $sql = "SELECT t.*, je.entry_date, je.reference, je.description AS entry_description, inv.id as invoice_id, inv.invoice_number 
                FROM transactions t 
                JOIN journal_entries je ON t.journal_entry_id = je.id 
                LEFT JOIN invoices inv ON je.id = inv.journal_entry_id 
                WHERE t.account_id = :account_id AND je.status = 'Posted'";
        
        $params = [':account_id' => $accountId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND je.entry_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND je.entry_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (je.reference LIKE :search OR je.description LIKE :search OR t.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['tx_type'])) {
            if ($filters['tx_type'] === 'debit') {
                $sql .= " AND t.debit > 0";
            } elseif ($filters['tx_type'] === 'credit') {
                $sql .= " AND t.credit > 0";
            }
        }

        $sql .= " ORDER BY je.entry_date ASC, t.id ASC";

        $this->db->query($sql);
        foreach ($params as $param => $val) {
            $this->db->bind($param, $val);
        }
        return $this->db->resultSet() ?: [];
    }

    // --- Bank Reconciliation Methods ---
    public function getUnclearedTransactions($accountId) {
        $this->db->query("SELECT t.*, je.entry_date, je.reference, je.description AS entry_description 
                          FROM transactions t 
                          JOIN journal_entries je ON t.journal_entry_id = je.id 
                          WHERE t.account_id = :aid AND t.is_cleared = 0 
                          ORDER BY je.entry_date ASC");
        $this->db->bind(':aid', $accountId);
        return $this->db->resultSet();
    }

    public function clearTransactions($transactionIds) {
        if (empty($transactionIds)) return true;
        
        // Fetch the journal entry IDs associated with these transactions before clearing
        $placeholders = [];
        foreach($transactionIds as $key => $val) { $placeholders[] = ":id" . $key; }
        $placeholderString = implode(',', $placeholders);

        try {
            $this->db->query("SELECT DISTINCT journal_entry_id FROM transactions WHERE id IN ($placeholderString)");
            foreach($transactionIds as $key => $val) { $this->db->bind(":id" . $key, $val); }
            $jeRows = $this->db->resultSet();
            
            $this->db->query("UPDATE transactions SET is_cleared = 1 WHERE id IN ($placeholderString)");
            foreach($transactionIds as $key => $val) { $this->db->bind(":id" . $key, $val); }
            $this->db->execute();

            if (!empty($jeRows)) {
                $jeIds = [];
                foreach ($jeRows as $row) {
                    if ($row->journal_entry_id) {
                        $jeIds[] = intval($row->journal_entry_id);
                    }
                }
                if (!empty($jeIds)) {
                    $jePlaceholders = [];
                    foreach ($jeIds as $k => $vid) { $jePlaceholders[] = ":jeid" . $k; }
                    $jePlaceholderStr = implode(',', $jePlaceholders);
                    
                    $this->db->query("UPDATE deposits SET reconciliation_status = 'Reconciled' WHERE realization_journal_entry_id IN ($jePlaceholderStr)");
                    foreach ($jeIds as $k => $vid) { $this->db->bind(":jeid" . $k, $vid); }
                    $this->db->execute();
                }
            }
            return true;
        } catch (PDOException $e) { 
            return false; 
        }
    }


    public function selfHealBankAccounts() {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE account_code = :parent_code");
        $this->db->bind(':parent_code', COA_CODE_CASH_PARENT);
        $parent = $this->db->single();
        if (!$parent) {
            $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id, balance) 
                              VALUES (:parent_code, 'Bank Current Account', :type_asset, NULL, 0)");
            $this->db->bind(':parent_code', COA_CODE_CASH_PARENT);
            $this->db->bind(':type_asset', COA_TYPE_ASSET);
            $this->db->execute();
            $parentId = $this->db->lastInsertId();
        } else {
            $parentId = $parent->id;
        }

        $this->db->query("SELECT * FROM chart_of_accounts WHERE account_code = :temp_code");
        $this->db->bind(':temp_code', COA_CODE_CASH_TEMP);
        $tempAcc = $this->db->single();
        if (!$tempAcc) {
            $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id, balance) 
                              VALUES (:temp_code, 'Temporary Bank Account', :type_asset, :pid, 0)");
            $this->db->bind(':temp_code', COA_CODE_CASH_TEMP);
            $this->db->bind(':type_asset', COA_TYPE_ASSET);
            $this->db->bind(':pid', $parentId);
            $this->db->execute();
        }
        return $parentId;
    }

    public function getBankAccounts($parentId) {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $this->db->bind(':pid', $parentId);
        return $this->db->resultSet() ?: [];
    }

    public function getCashAccounts($parentId) {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE account_type = :type_asset AND (parent_id IS NULL OR parent_id != :pid) AND id != :pid ORDER BY account_code ASC");
        $this->db->bind(':type_asset', COA_TYPE_ASSET);
        $this->db->bind(':pid', $parentId);
        return $this->db->resultSet() ?: [];
    }

    public function getNextBankCode() {
        $prefix = substr(COA_CODE_CASH_PARENT, 0, 2);
        $this->db->query("SELECT MAX(CAST(account_code AS UNSIGNED)) as max_code FROM chart_of_accounts WHERE account_code LIKE :prefix AND account_code != :parent_code");
        $this->db->bind(':prefix', $prefix . '%');
        $this->db->bind(':parent_code', COA_CODE_CASH_PARENT);
        $row = $this->db->single();
        $defaultStartCode = intval($prefix . '01');
        $nextCode = $row && $row->max_code ? intval($row->max_code) + 1 : $defaultStartCode;
        if ($nextCode == intval(COA_CODE_CASH_TEMP)) {
            $nextCode = intval(COA_CODE_CASH_TEMP) + 1;
        }
        return $nextCode;
    }

    public function addBankAccount($code, $name, $parentId) {
        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id, balance, is_active) 
                          VALUES (:code, :name, :type_asset, :pid, 0, 1)");
        $this->db->bind(':code', (string)$code);
        $this->db->bind(':name', $name);
        $this->db->bind(':type_asset', COA_TYPE_ASSET);
        $this->db->bind(':pid', $parentId);
        return $this->db->execute();
    }

    public function editBankAccountName($id, $name) {
        $this->db->query("UPDATE chart_of_accounts SET account_name = :name WHERE id = :id");
        $this->db->bind(':name', $name);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getAccountTransactionCount($id) {
        $this->db->query("SELECT COUNT(*) as tx_count FROM transactions WHERE account_id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ? intval($row->tx_count) : 0;
    }

    public function getBankLedger($accountId) {
        $this->db->query("SELECT t.*, je.entry_date, je.reference, je.description 
                          FROM transactions t 
                          JOIN journal_entries je ON t.journal_entry_id = je.id 
                          WHERE t.account_id = :aid 
                          ORDER BY je.entry_date DESC, je.id DESC");
        $this->db->bind(':aid', $accountId);
        return $this->db->resultSet() ?: [];
    }
}