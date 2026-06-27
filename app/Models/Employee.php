<?php
class Employee {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllEmployees() {
        // Fetch employees combined with their system user credentials and accessible apps
        $this->db->query("SELECT e.*, u.username, u.role as user_role, u.accessible_apps 
                          FROM employees e 
                          LEFT JOIN users u ON u.employee_id = e.id 
                          ORDER BY e.status ASC, e.last_name ASC");
        return $this->db->resultSet();
    }

    public function getActiveEmployees() {
        $this->db->query("SELECT * FROM employees WHERE status = 'Active'");
        return $this->db->resultSet();
    }

    public function addEmployee($empData, $userData = null) {
        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO employees (first_name, last_name, email, phone, department, job_title, base_salary, hire_date, status) 
                              VALUES (:fname, :lname, :email, :phone, :dept, :title, :salary, :hdate, 'Active')");
            
            $email = !empty($empData['email']) ? trim($empData['email']) : null;
            $phone = !empty($empData['phone']) ? trim($empData['phone']) : null;
            $dept = !empty($empData['department']) ? trim($empData['department']) : null;

            $this->db->bind(':fname', $empData['first_name']);
            $this->db->bind(':lname', $empData['last_name']);
            $this->db->bind(':email', $email);
            $this->db->bind(':phone', $phone);
            $this->db->bind(':dept', $dept);
            $this->db->bind(':title', $empData['job_title']);
            $this->db->bind(':salary', $empData['base_salary']);
            $this->db->bind(':hdate', $empData['hire_date']);
            
            $this->db->execute();
            $employeeId = $this->db->lastInsertId();

            if ($userData) {
                $this->db->query("INSERT INTO users (username, email, password_hash, role, signature_path, employee_id, status, accessible_apps) 
                                  VALUES (:username, :email, :password, :role, :sig, :employee_id, :status, :accessible_apps)");
                $this->db->bind(':username', $userData['username']);
                $this->db->bind(':email', $userData['email'] ?: null);
                $this->db->bind(':password', password_hash($userData['password'], PASSWORD_DEFAULT));
                $this->db->bind(':role', $userData['role']);
                $this->db->bind(':sig', $userData['signature_path'] ?: null);
                $this->db->bind(':employee_id', $employeeId);
                $this->db->bind(':status', $userData['status']);
                $this->db->bind(':accessible_apps', $userData['accessible_apps']);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}