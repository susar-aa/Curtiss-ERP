<?php
class UserController extends Controller {
    private $userModel;

    public function __construct() {
        // 1. Ensure logged in
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        
        // 2. Strict RBAC Protection: Only Admins can manage users
        if ($_SESSION['role'] !== 'Admin') {
            die("<div style='padding:40px; font-family:sans-serif; text-align:center; color:#c62828;'>
                    <h2>Access Denied (403)</h2>
                    <p>Your current role (<strong>" . htmlspecialchars($_SESSION['role']) . "</strong>) does not have permission to access User Management.</p>
                    <a href='" . APP_URL . "/dashboard' style='color:#0066cc;'>Return to Dashboard</a>
                 </div>");
        }
        
        $this->userModel = $this->model('User');
    }

    public function index() {
        $data = [
            'title' => 'User Management',
            'content_view' => 'users/index',
            'users' => $this->userModel->getAllUsers(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_user') {
            $userData = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'role' => $_POST['role']
            ];

            if (empty($userData['username']) || empty($userData['password'])) {
                $data['error'] = 'Username and Password are required.';
            } elseif ($this->userModel->findUserByUsername($userData['username'])) {
                $data['error'] = 'That username is already taken by another employee.';
            } else {
                if ($this->userModel->createUser($userData)) {
                    $data['success'] = 'New system user account created successfully!';
                    $data['users'] = $this->userModel->getAllUsers(); // Refresh table
                } else {
                    $data['error'] = 'Database Error: Failed to create user.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }
}