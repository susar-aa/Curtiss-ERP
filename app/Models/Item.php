<?php
class Item {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllItems() {
        $this->db->query("SELECT i.*, 
                                 cat.name as category_name,
                                 inc.account_name as income_account, 
                                 exp.account_name as expense_account 
                          FROM items i 
                          LEFT JOIN item_categories cat ON i.category_id = cat.id
                          LEFT JOIN chart_of_accounts inc ON i.income_account_id = inc.id 
                          LEFT JOIN chart_of_accounts exp ON i.expense_account_id = exp.id 
                          ORDER BY i.name ASC");
        return $this->db->resultSet();
    }

    public function getLowStockItems() {
        $this->db->query("SELECT * FROM items WHERE type = 'Inventory' AND quantity_on_hand <= minimum_stock_level");
        return $this->db->resultSet();
    }

    public function addItem($data) {
        $this->db->query("INSERT INTO items (item_code, name, category_id, type, price, cost, quantity_on_hand, minimum_stock_level, income_account_id, expense_account_id) 
                          VALUES (:code, :name, :cat_id, :type, :price, :cost, :qty, :min_stock, :inc_id, :exp_id)");
        
        $this->db->bind(':code', $data['item_code']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':cat_id', $data['category_id'] ?: null);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':cost', $data['cost']);
        $this->db->bind(':qty', $data['qty']);
        $this->db->bind(':min_stock', $data['min_stock']);
        $this->db->bind(':inc_id', $data['income_account_id'] ?: null);
        $this->db->bind(':exp_id', $data['expense_account_id'] ?: null);
        
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}