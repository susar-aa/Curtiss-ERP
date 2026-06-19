<?php
class UserController extends Controller {
    private $userModel;
    private $employeeModel;

    private $modules = [
        'crm' => 'CRM & Leads',
        'customer' => 'Customers Center',
        'estimate' => 'Quotes & Estimates',
        'sales' => 'Invoices & AR',
        'creditnote' => 'Credit Notes & Damaged Log',
        'dunning' => 'Dunning Reminders',
        'discount' => 'Discount Feed',
        'reptracking' => 'Rep Route Tracking',
        'delivery' => 'Arranged Deliveries',
        'territory' => 'Territory & Routing',
        'inventory' => 'Products & Inventory',
        'category' => 'Product Categories',
        'variation' => 'Variations & Attributes',
        'warehouse' => 'Warehouse Management',
        'supplier' => 'Suppliers Center',
        'purchase' => 'Purchase Orders',
        'grn' => 'Goods Receipts (GRN)',
        'supplier_return' => 'Supplier Returns',
        'expenses' => 'Expenses & AP',
        'hrm' => 'HRM, Employees & Payroll',
        'project' => 'Projects & Tasks',
        'vehicle' => 'Vehicle Management',
        'cheque' => 'Cheque Management',
        'accounting' => 'General Accounting',
        'customerpayment' => 'Customer Payments',
        'supplierpayment' => 'Supplier Payments',
        'asset' => 'Fixed Assets Register',
        'report' => 'Financial Reports & Budgets',
        'ecommerce' => 'E-Commerce Operations',
        'settings' => 'Company Settings',
        'user' => 'Users & Permissions Control',
        'tax' => 'Tax Rates & Rules',
        'paymentterm' => 'Payment Terms & Rules',
        'audit' => 'System Audit Trail'
    ];

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { 
            header('Location: ' . APP_URL . '/auth/login'); 
            exit; 
        }

        // Auto-initialize the user_permissions table if not exists
        $db = new Database();
        $db->query("CREATE TABLE IF NOT EXISTS user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            module VARCHAR(50) NOT NULL,
            can_view TINYINT(1) DEFAULT 0,
            can_create_edit TINYINT(1) DEFAULT 0,
            can_delete TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY user_module (user_id, module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $db->execute();

        $this->userModel = $this->model('User');
        $this->employeeModel = $this->model('Employee');

        // Restrict access using the permission system
        $this->checkPermission('user', 'view');
    }

    /**
     * Display users list
     */
    public function index() {
        $users = $this->userModel->getAllUsers();
        
        // Load permissions for each user
        foreach ($users as $u) {
            $u->permissions = $this->userModel->getUserPermissions($u->id);
        }

        $data = [
            'title' => 'User Management',
            'content_view' => 'users/index',
            'users' => $users,
            'employees' => $this->employeeModel->getAllEmployees()
        ];

        $this->view('layouts/main', $data);
    }

    /**
     * Create user account and define permissions
     */
    public function create() {
        $this->checkPermission('user', 'create_edit');

        $data = [
            'title' => 'Create User Account',
            'content_view' => 'users/create',
            'employees' => $this->employeeModel->getAllEmployees(),
            'modules' => $this->modules,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Retrieve inputs
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'office';
            $employeeId = !empty($_POST['employee_id']) ? intval($_POST['employee_id']) : null;
            $permissions = $_POST['permissions'] ?? [];

            // Handle Signature Upload
            $signaturePath = null;
            if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $signaturePath = 'sig_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    if (!file_exists('../public/uploads')) {
                        @mkdir('../public/uploads', 0777, true);
                    }
                    move_uploaded_file($_FILES['signature']['tmp_name'], '../public/uploads/' . $signaturePath);
                }
            }

            if (empty($username) || empty($password)) {
                $data['error'] = 'Username and Password are required.';
            } elseif ($this->userModel->findUserByUsername($username)) {
                $data['error'] = 'Username is already taken.';
            } else {
                $userData = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => $role,
                    'signature_path' => $signaturePath,
                    'employee_id' => $employeeId,
                    'status' => $_POST['status'] ?? 'Active'
                ];

                if ($this->userModel->createUser($userData)) {
                    $newUser = $this->userModel->findUserByUsername($username);
                    if ($newUser) {
                        // Save permissions
                        $this->userModel->saveUserPermissions($newUser->id, $permissions);
                    }

                    $cleanUserData = $userData;
                    unset($cleanUserData['password']);

                    $this->logActivity(
                        'User Created', 
                        'Security', 
                        "User account '{$username}' (Role: {$role}) created with custom permissions successfully.", 
                        $newUser ? $newUser->id : null, 
                        null, 
                        $cleanUserData
                    );

                    $_SESSION['flash_success'] = 'User account and permissions created successfully!';
                    header('Location: ' . APP_URL . '/user');
                    exit;
                } else {
                    $data['error'] = 'Failed to create user account.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }

    /**
     * Edit user account and modify permissions
     */
    public function edit($id) {
        $this->checkPermission('user', 'create_edit');

        $user = $this->userModel->findUserByUsername($this->getUsernameById($id));
        if (!$user) {
            die("User not found.");
        }

        $currentPermissions = $this->userModel->getUserPermissions($id);

        $data = [
            'title' => 'Edit User Account',
            'content_view' => 'users/edit',
            'user' => $user,
            'employees' => $this->employeeModel->getAllEmployees(),
            'modules' => $this->modules,
            'currentPermissions' => $currentPermissions,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'office';
            $employeeId = !empty($_POST['employee_id']) ? intval($_POST['employee_id']) : null;
            $permissions = $_POST['permissions'] ?? [];
            $password = $_POST['password'] ?? '';

            // Handle signature delete/upload
            $signaturePath = $user->signature_path;
            if (isset($_POST['delete_signature']) && $_POST['delete_signature'] == '1') {
                $signaturePath = null;
            }

            if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $signaturePath = 'sig_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    if (!file_exists('../public/uploads')) {
                        @mkdir('../public/uploads', 0777, true);
                    }
                    move_uploaded_file($_FILES['signature']['tmp_name'], '../public/uploads/' . $signaturePath);
                }
            }

            // Check if username changed and is already taken
            $usernameConflict = false;
            if (strtolower($username) !== strtolower($user->username)) {
                if ($this->userModel->findUserByUsername($username)) {
                    $usernameConflict = true;
                }
            }

            if (empty($username)) {
                $data['error'] = 'Username is required.';
            } elseif ($usernameConflict) {
                $data['error'] = 'Username is already taken.';
            } else {
                $updateData = [
                    'id' => $id,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'employee_id' => $employeeId,
                    'signature_path' => $signaturePath,
                    'status' => $_POST['status'] ?? 'Active'
                ];

                if ($this->userModel->updateUser($updateData)) {
                    // Update password if typed
                    if (!empty($password)) {
                        $this->userModel->updatePassword($username, $password);
                    }

                    // Save permissions
                    $this->userModel->saveUserPermissions($id, $permissions);

                    // If currently logged in user edited themselves, refresh their session permissions immediately
                    if ($_SESSION['user_id'] == $id) {
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                        
                        // Reload permissions in session
                        $perms = $this->userModel->getUserPermissions($id);
                        $sessionPerms = [];
                        foreach ($perms as $module => $p) {
                            $sessionPerms[$module] = [
                                'can_view' => $p['can_view'],
                                'can_create_edit' => $p['can_create_edit'],
                                'can_delete' => $p['can_delete']
                            ];
                        }
                        $_SESSION['permissions'] = $sessionPerms;
                    }

                    $this->logActivity(
                        'User Updated', 
                        'Security', 
                        "User account '{$username}' updated successfully.", 
                        $id, 
                        null, 
                        $updateData
                    );

                    $_SESSION['flash_success'] = 'User account and permissions updated successfully!';
                    header('Location: ' . APP_URL . '/user');
                    exit;
                } else {
                    $data['error'] = 'Failed to update user account.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }

    /**
     * Delete user account
     */
    public function delete($id) {
        $this->checkPermission('user', 'delete');

        if ($id == $_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'You cannot delete your own account.';
            header('Location: ' . APP_URL . '/user');
            exit;
        }

        $db = new Database();
        $db->query("SELECT role, username FROM users WHERE id = :id");
        $db->bind(':id', $id);
        $userRow = $db->single();

        if ($userRow) {
            if (strtolower($userRow->role) === 'rep') {
                $_SESSION['flash_error'] = 'Representative accounts cannot be deleted to preserve the route and audit trails. Please block this user by setting their status to Blocked/Inactive instead.';
                header('Location: ' . APP_URL . '/user');
                exit;
            }

            if ($this->userModel->deleteUser($id)) {
                $this->logActivity('User Deleted', 'Security', "User account '{$userRow->username}' deleted successfully.", $id);
                $_SESSION['flash_success'] = "User account '{$userRow->username}' deleted successfully.";
            } else {
                $_SESSION['flash_error'] = 'Failed to delete user account.';
            }
        }

        header('Location: ' . APP_URL . '/user');
        exit;
    }

    private function getUsernameById($id) {
        $db = new Database();
        $db->query("SELECT username FROM users WHERE id = :id");
        $db->bind(':id', $id);
        $row = $db->single();
        return $row ? $row->username : null;
    }
}