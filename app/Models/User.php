<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllUsers() {
        $this->db->query("SELECT u.id, u.username, u.email, u.role, u.signature_path, u.created_at, u.employee_id, e.first_name, e.last_name 
                          FROM users u 
                          LEFT JOIN employees e ON u.employee_id = e.id 
                          ORDER BY u.created_at DESC");
        return $this->db->resultSet();
    }

    public function findUserByUsername($username) {
        $this->db->query("SELECT * FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        $row = $this->db->resultSet();
        return count($row) > 0 ? $row[0] : false;
    }

    public function login($username, $password) {
        $row = $this->findUserByUsername($username);
        if ($row) {
            $hashed_password = $row->password_hash;
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    public function createUser($data) {
        $this->db->query("INSERT INTO users (username, email, password_hash, role, signature_path, employee_id) 
                          VALUES (:username, :email, :password, :role, :sig, :employee_id)");
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':sig', $data['signature_path']);
        $this->db->bind(':employee_id', $data['employee_id']);
        return $this->db->execute();
    }

    public function updatePassword($username, $new_password) {
        $this->db->query("UPDATE users SET password_hash = :hash WHERE username = :username");
        $this->db->bind(':hash', password_hash($new_password, PASSWORD_DEFAULT));
        $this->db->bind(':username', $username);
        return $this->db->execute();
    }
}