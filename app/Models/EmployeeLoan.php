<?php
class EmployeeLoan {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllLoans() {
        $this->db->query("
            SELECT el.*, e.first_name, e.last_name, e.department,
                   COALESCE((SELECT SUM(principal_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as total_principal_paid,
                   COALESCE((SELECT SUM(interest_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as total_interest_paid
            FROM employee_loans el
            JOIN employees e ON el.employee_id = e.id
            ORDER BY el.created_at DESC
        ");
        $loans = $this->db->resultSet();
        foreach ($loans as $l) {
            $l->principal_balance = floatval($l->principal_amount) - floatval($l->total_principal_paid);
            $l->employee_name = $l->first_name . ' ' . $l->last_name;
        }
        return $loans;
    }

    public function getLoanById($id) {
        $this->db->query("
            SELECT el.*, e.first_name, e.last_name, e.department,
                   COALESCE((SELECT SUM(principal_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as total_principal_paid,
                   COALESCE((SELECT SUM(interest_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as total_interest_paid
            FROM employee_loans el 
            JOIN employees e ON el.employee_id = e.id
            WHERE el.id = :id
        ");
        $this->db->bind(':id', $id);
        $loan = $this->db->single();
        if ($loan) {
            $loan->principal_balance = floatval($loan->principal_amount) - floatval($loan->total_principal_paid);
            $loan->employee_name = $loan->first_name . ' ' . $loan->last_name;
        }
        return $loan;
    }

    public function getLoansByEmployeeId($employeeId) {
        $this->db->query("
            SELECT el.*,
                   COALESCE((SELECT SUM(principal_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as total_principal_paid
            FROM employee_loans el
            WHERE el.employee_id = :employee_id
            ORDER BY el.created_at DESC
        ");
        $this->db->bind(':employee_id', $employeeId);
        $loans = $this->db->resultSet();
        foreach ($loans as $l) {
            $l->principal_balance = floatval($l->principal_amount) - floatval($l->total_principal_paid);
        }
        return $loans;
    }

    public function getActiveLoansByEmployeeId($employeeId) {
        $this->db->query("
            SELECT el.*,
                   COALESCE((SELECT SUM(principal_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as total_principal_paid
            FROM employee_loans el
            WHERE el.employee_id = :employee_id AND el.status = 'Active'
        ");
        $this->db->bind(':employee_id', $employeeId);
        $loans = $this->db->resultSet();
        foreach ($loans as $l) {
            $l->principal_balance = floatval($l->principal_amount) - floatval($l->total_principal_paid);
        }
        return $loans;
    }

    public function addLoan($data) {
        $this->db->query("INSERT INTO employee_loans 
            (employee_id, loan_number, principal_amount, interest_rate, loan_start_date, loan_term_months, repayment_frequency, repayment_amount, status, notes) 
            VALUES 
            (:employee_id, :loan_no, :principal, :interest, :start_date, :term, :freq, :repayment_amount, :status, :notes)");
        
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':loan_no', $data['loan_number']);
        $this->db->bind(':principal', $data['principal_amount']);
        $this->db->bind(':interest', $data['interest_rate'] ?? 0);
        $this->db->bind(':start_date', $data['loan_start_date']);
        $this->db->bind(':term', $data['loan_term_months']);
        $this->db->bind(':freq', $data['repayment_frequency']);
        $this->db->bind(':repayment_amount', $data['repayment_amount']);
        $this->db->bind(':status', 'Pending');
        $this->db->bind(':notes', $data['notes']);
        
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    public function updateLoan($data) {
        $this->db->query("UPDATE employee_loans 
            SET principal_amount = :principal, interest_rate = :interest, loan_start_date = :start_date, 
                loan_term_months = :term, repayment_frequency = :freq, repayment_amount = :repayment_amount, 
                notes = :notes, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':principal', $data['principal_amount']);
        $this->db->bind(':interest', $data['interest_rate'] ?? 0);
        $this->db->bind(':start_date', $data['loan_start_date']);
        $this->db->bind(':term', $data['loan_term_months']);
        $this->db->bind(':freq', $data['repayment_frequency']);
        $this->db->bind(':repayment_amount', $data['repayment_amount']);
        $this->db->bind(':notes', $data['notes']);

        return $this->db->execute();
    }

    public function deleteLoan($id) {
        $loan = $this->getLoanById($id);
        if (!$loan || $loan->status != 'Pending') return false;

        $this->db->query("DELETE FROM employee_loans WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function updateStatus($id, $status) {
        $this->db->query("UPDATE employee_loans SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function addRepayment($data) {
        $this->db->query("INSERT INTO employee_loan_repayments 
            (employee_loan_id, payroll_slip_id, payment_date, principal_amount, interest_amount, notes, created_by) 
            VALUES 
            (:loan_id, :slip_id, :payment_date, :principal, :interest, :notes, :user_id)");
        
        $this->db->bind(':loan_id', $data['employee_loan_id']);
        $this->db->bind(':slip_id', $data['payroll_slip_id'] ?? null);
        $this->db->bind(':payment_date', $data['payment_date']);
        $this->db->bind(':principal', $data['principal_amount']);
        $this->db->bind(':interest', $data['interest_amount'] ?? 0);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':user_id', $data['created_by'] ?? null);
        
        if ($this->db->execute()) {
            // Check if fully paid
            $loan = $this->getLoanById($data['employee_loan_id']);
            if ($loan && $loan->principal_balance <= 0) {
                $this->updateStatus($loan->id, 'Closed');
            }
            return true;
        }
        return false;
    }

    public function getRepayments($loanId) {
        $this->db->query("SELECT * FROM employee_loan_repayments WHERE employee_loan_id = :loan_id ORDER BY payment_date DESC, created_at DESC");
        $this->db->bind(':loan_id', $loanId);
        return $this->db->resultSet();
    }
}
