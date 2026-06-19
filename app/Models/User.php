<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllUsers() {
        $this->db->query("SELECT u.id, u.username, u.email, u.role, u.signature_path, u.created_at, u.employee_id, u.status, e.first_name, e.last_name 
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
            if (isset($row->status) && strtolower($row->status) !== 'active') {
                return false;
            }
            $hashed_password = $row->password_hash;
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    public function createUser($data) {
        $this->db->query("INSERT INTO users (username, email, password_hash, role, signature_path, employee_id, status) 
                          VALUES (:username, :email, :password, :role, :sig, :employee_id, :status)");
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':sig', $data['signature_path']);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':status', $data['status'] ?? 'Active');
        return $this->db->execute();
    }

    public function updatePassword($username, $new_password) {
        $this->db->query("UPDATE users SET password_hash = :hash WHERE username = :username");
        $this->db->bind(':hash', password_hash($new_password, PASSWORD_DEFAULT));
        $this->db->bind(':username', $username);
        return $this->db->execute();
    }

    public function getUserPermissions($userId) {
        $this->db->query("SELECT * FROM user_permissions WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $rows = $this->db->resultSet();
        $perms = [];
        foreach ($rows as $row) {
            $perms[$row->module] = [
                'can_view' => (bool)$row->can_view,
                'can_create_edit' => (bool)$row->can_create_edit,
                'can_delete' => (bool)$row->can_delete
            ];
        }
        return $perms;
    }

    public function saveUserPermissions($userId, $permissions) {
        // Delete existing
        $this->db->query("DELETE FROM user_permissions WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $this->db->execute();

        // Insert new ones
        if (!empty($permissions)) {
            foreach ($permissions as $module => $actions) {
                $canView = !empty($actions['view']) ? 1 : 0;
                $canCreateEdit = !empty($actions['create_edit']) ? 1 : 0;
                $canDelete = !empty($actions['delete']) ? 1 : 0;

                // Only insert if at least one permission is true
                if ($canView || $canCreateEdit || $canDelete) {
                    $this->db->query("INSERT INTO user_permissions (user_id, module, can_view, can_create_edit, can_delete) 
                                      VALUES (:user_id, :module, :can_view, :can_create_edit, :can_delete)");
                    $this->db->bind(':user_id', $userId);
                    $this->db->bind(':module', $module);
                    $this->db->bind(':can_view', $canView);
                    $this->db->bind(':can_create_edit', $canCreateEdit);
                    $this->db->bind(':can_delete', $canDelete);
                    $this->db->execute();
                }
            }
        }
        return true;
    }

    public function updateUser($data) {
        $this->db->query("UPDATE users SET username = :username, email = :email, role = :role, employee_id = :employee_id, signature_path = :sig, status = :status WHERE id = :id");
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':sig', $data['signature_path']);
        $this->db->bind(':status', $data['status'] ?? 'Active');
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function deleteUser($id) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}