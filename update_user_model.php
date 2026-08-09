<?php
$f = 'app/Models/User.php';
$c = file_get_contents($f);
$new_method = "
    public function getLoginEligibleUsers() {
        \$this->db->query(\"
            SELECT id, username, role, employee_id 
            FROM users 
            WHERE role IN ('Admin', 'Office Staff', 'Accountant') 
            AND status = 'Active' 
            ORDER BY role ASC, username ASC
        \");
        return \$this->db->resultSet();
    }

    public function findUserByUsername";
$c = str_replace("    public function findUserByUsername", $new_method, $c);
file_put_contents($f, $c);
echo "User model updated.";
