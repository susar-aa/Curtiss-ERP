<?php
$f = 'app/Models/User.php';
$c = file_get_contents($f);
$old_method = "
    public function getLoginEligibleUsers() {
        \$this->db->query(\"
            SELECT id, username, role, employee_id 
            FROM users 
            WHERE role IN ('Admin', 'Office Staff', 'Accountant') 
            AND status = 'Active' 
            ORDER BY role ASC, username ASC
        \");
        return \$this->db->resultSet();
    }";

$new_method = "
    public function getLoginEligibleUsers() {
        \$this->db->query(\"
            SELECT u.id, u.username, u.role, u.employee_id, 
                   COALESCE(CONCAT(e.first_name, ' ', e.last_name), u.username) AS full_name
            FROM users u
            LEFT JOIN employees e ON u.employee_id = e.id
            WHERE u.role IN ('Admin', 'Office Staff', 'Accountant') 
            AND u.status = 'Active' 
            ORDER BY u.role ASC, full_name ASC
        \");
        return \$this->db->resultSet();
    }";

$c = str_replace($old_method, $new_method, $c);
file_put_contents($f, $c);
echo "User model updated.";
