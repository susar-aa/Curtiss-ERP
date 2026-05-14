<?php
class Employee {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllEmployees() {
        $this->db->query("SELECT * FROM employees ORDER BY status ASC, last_name ASC");
        return $this->db->resultSet();
    }

    public function getActiveEmployees() {
        $this->db->query("SELECT * FROM employees WHERE status = 'Active'");
        return $this->db->resultSet();
    }

    public function addEmployee($data) {
        $this->db->query("INSERT INTO employees (first_name, last_name, email, phone, department, job_title, base_salary, hire_date) 
                          VALUES (:fname, :lname, :email, :phone, :dept, :title, :salary, :hdate)");
        
        $this->db->bind(':fname', $data['first_name']);
        $this->db->bind(':lname', $data['last_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':dept', $data['department']);
        $this->db->bind(':title', $data['job_title']);
        $this->db->bind(':salary', $data['base_salary']);
        $this->db->bind(':hdate', $data['hire_date']);
        
        return $this->db->execute();
    }
}