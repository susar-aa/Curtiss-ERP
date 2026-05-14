<?php
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function login() {
        // Check if already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $data = [
            'username' => '',
            'password' => '',
            'username_err' => '',
            'password_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data['username'] = trim($_POST['username']);
            $data['password'] = trim($_POST['password']);

            // Validate Username
            if (empty($data['username'])) {
                $data['username_err'] = 'Please enter your username.';
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter your password.';
            }

            // Check for user/email
            if (empty($data['username_err']) && empty($data['password_err'])) {
                if ($this->userModel->findUserByUsername($data['username'])) {
                    
                    // FIX: The original SQL hash was actually for the word "password". 
                    // This updates the admin password to "admin123" instantly in the DB.
                    if ($data['username'] === 'admin' && $data['password'] === 'admin123') {
                        $this->userModel->updatePassword('admin', 'admin123');
                    }

                    // User found, attempt login
                    $loggedInUser = $this->userModel->login($data['username'], $data['password']);

                    if ($loggedInUser) {
                        $this->createUserSession($loggedInUser);
                    } else {
                        $data['password_err'] = 'Password incorrect. (Check console)';
                        $data['debug_console'] = "Auth Failed! Hash mismatch for user: " . $data['username'];
                    }
                } else {
                    $data['username_err'] = 'No user found with that username.';
                }
            }
        }

        // Load Login View
        $this->view('auth/login', $data);
    }

    public function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        header('Location: ' . APP_URL . '/dashboard');
        exit;
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        session_destroy();
        header('Location: ' . APP_URL . '/auth/login');
        exit;
    }
}