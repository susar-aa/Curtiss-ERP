<?php
class DriverAuthController extends DriverController {

    // Handles both displaying the login form (GET) and processing the login (POST)
    public function login() {
        // If already logged in, redirect to the dashboard
        if (isset($_SESSION['user_id'])) {
            $role = strtolower($_SESSION['role'] ?? '');
            if ($role === 'driver') {
                header('Location: ' . APP_URL . '/driver');
            } elseif ($role === 'rep') {
                header('Location: ' . APP_URL . '/rep');
            } else {
                header('Location: ' . APP_URL . '/dashboard');
            }
            exit;
        }

        $data = [
            'title' => 'Driver Login',
            'content_view' => 'login',
            'error' => $_GET['error'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Basic validation
            if ($username === '' || $password === '') {
                $data['error'] = 'Missing credentials';
                $this->view('layout', $data);
                return;
            }

            // Re-use central User model
            $userModel = $this->model('User');
            $user = $userModel->login($username, $password);

            if ($user) {
                $_SESSION['user_id']   = $user->id;
                $_SESSION['username']  = $user->username;
                $_SESSION['role']      = $user->role;
                
                $role = strtolower($user->role);
                if ($role === 'driver') {
                    header('Location: ' . APP_URL . '/driver');
                } elseif ($role === 'rep') {
                    header('Location: ' . APP_URL . '/rep');
                } else {
                    header('Location: ' . APP_URL . '/dashboard');
                }
                exit;
            } else {
                $data['error'] = 'Invalid username or password';
            }
        }

        $this->view('layout', $data);
    }

    // Log out the driver
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/driver/auth/login');
        exit;
    }

    // JSON API for Native Mobile App Authentication
    public function api_login() {
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true) ?: $_POST;
        
        $username = trim($postData['username'] ?? '');
        $password = $postData['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Missing credentials']);
            exit;
        }
        
        $userModel = $this->model('User');
        $user = $userModel->login($username, $password);
        
        if ($user) {
            $role = strtolower($user->role);
            if ($role === 'driver') {
                echo json_encode([
                    'success' => true,
                    'user_id' => intval($user->id),
                    'username' => $user->username,
                    'role' => $user->role
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Unauthorized role']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
        exit;
    }
}