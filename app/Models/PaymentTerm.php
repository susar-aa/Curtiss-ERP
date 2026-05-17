<?php
class PaymentTerm {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllTerms() {
        $this->db->query("SELECT * FROM payment_terms ORDER BY days_due ASC");
        return $this->db->resultSet();
    }

    public function addTerm($data) {
        $this->db->query("INSERT INTO payment_terms (name, days_due) VALUES (:name, :days)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':days', $data['days_due']);
        return $this->db->execute();
    }

    public function updateTerm($data) {
        $this->db->query("UPDATE payment_terms SET name = :name, days_due = :days WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':days', $data['days_due']);
        return $this->db->execute();
    }

    public function deleteTerm($id) {
        $this->db->query("DELETE FROM payment_terms WHERE id = :id");
        $this->db->bind(':id', $id);
        try { return $this->db->execute(); } catch (PDOException $e) { return false; }
    }
}