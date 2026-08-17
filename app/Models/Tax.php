<?php
class Tax {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllTaxRates() {
        $this->db->query("SELECT t.*, c.account_name, c.account_code 
                          FROM tax_rates t 
                          JOIN chart_of_accounts c ON t.liability_account_id = c.id 
                          ORDER BY t.is_active DESC, t.tax_name ASC");
        return $this->db->resultSet();
    }

    public function addTaxRate($data) {
        $this->db->query("INSERT INTO tax_rates (tax_name, rate_percentage, liability_account_id) 
                          VALUES (:name, :rate, :acc_id)");
        $this->db->bind(':name', $data['tax_name']);
        $this->db->bind(':rate', $data['rate_percentage']);
        $this->db->bind(':acc_id', $data['liability_account_id']);
        return $this->db->execute();
    }

    public function toggleStatus($id, $status) {
        $this->db->query("UPDATE tax_rates SET is_active = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function updateTaxRate($data) {
        $this->db->query("UPDATE tax_rates SET tax_name = :name, rate_percentage = :rate, liability_account_id = :acc_id WHERE id = :id");
        $this->db->bind(':name', $data['tax_name']);
        $this->db->bind(':rate', $data['rate_percentage']);
        $this->db->bind(':acc_id', $data['liability_account_id']);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function deleteTaxRate($id) {
        // Prevent deletion if in use by invoices or sales_orders
        $this->db->query("SELECT COUNT(*) as cnt FROM invoices WHERE tax_rate_id = :id");
        $this->db->bind(':id', $id);
        $res = $this->db->single();
        if ($res && $res->cnt > 0) return false;

        $this->db->query("SELECT COUNT(*) as cnt FROM sales_orders WHERE tax_rate_id = :id");
        $this->db->bind(':id', $id);
        $res = $this->db->single();
        if ($res && $res->cnt > 0) return false;

        $this->db->query("DELETE FROM tax_rates WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}