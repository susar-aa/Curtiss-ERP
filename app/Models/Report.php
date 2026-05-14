<?php
class Report {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getTrialBalanceData() {
        $this->db->query("SELECT * FROM chart_of_accounts WHERE balance != 0 ORDER BY account_code ASC");
        return $this->db->resultSet();
    }

    public function getAccountsByTypes($types) {
        $placeholders = str_repeat('?,', count($types) - 1) . '?';
        $sql = "SELECT * FROM chart_of_accounts 
                WHERE account_type IN ($placeholders) AND balance != 0 
                ORDER BY FIELD(account_type, 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'), account_code ASC";
        
        $this->db->query($sql);
        $this->db->stmt->execute($types);
        return $this->db->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // NEW: Fetch unpaid invoices and calculate days overdue
    public function getARAging() {
        $this->db->query("SELECT i.invoice_number, i.due_date, i.total_amount, c.name as customer_name, 
                                 DATEDIFF(CURRENT_DATE, i.due_date) as days_overdue
                          FROM invoices i
                          JOIN customers c ON i.customer_id = c.id
                          WHERE i.status = 'Unpaid'
                          ORDER BY c.name ASC, i.due_date ASC");
        return $this->db->resultSet();
    }
}