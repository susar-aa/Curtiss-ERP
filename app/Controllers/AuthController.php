<?php
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function login() {
        // Generate CSRF token if not exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Check if already logged in
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
            'username' => '',
            'password' => '',
            'username_err' => '',
            'password_err' => '',
            'csrf_err' => '',
            'lockout_err' => ''
        ];

        // Rate Limiting lockout check
        $max_attempts = 5;
        $lockout_time = 180; // 3 minutes
        if (isset($_SESSION['login_locked_until']) && $_SESSION['login_locked_until'] > time()) {
            $remaining = $_SESSION['login_locked_until'] - time();
            $data['lockout_err'] = "Too many failed attempts. Secure login is locked. Try again in {$remaining} seconds.";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // If already locked, do not allow processing
            if (!empty($data['lockout_err'])) {
                $this->view('auth/login', $data);
                return;
            }

            // CSRF protection validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $data['csrf_err'] = 'Security validation failed (CSRF mismatch). Please refresh and try again.';
                $this->logActivity('Login Suspicious', 'Auth', "CSRF token mismatch detected for login attempt.");
            }

            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data['username'] = trim($_POST['username'] ?? '');
            $data['password'] = trim($_POST['password'] ?? '');

            // Validate Username input
            if (empty($data['username'])) {
                $data['username_err'] = 'Please enter your username.';
            } elseif (strlen($data['username']) < 3) {
                $data['username_err'] = 'Username must be at least 3 characters.';
            } elseif (strlen($data['username']) > 50) {
                $data['username_err'] = 'Username cannot exceed 50 characters.';
            }

            // Validate Password input
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter your password.';
            } elseif (strlen($data['password']) < 4) {
                $data['password_err'] = 'Password must be at least 4 characters.';
            }

            // Attempt login if there are no validation or security errors
            if (empty($data['username_err']) && empty($data['password_err']) && empty($data['csrf_err'])) {
                if ($this->userModel->findUserByUsername($data['username'])) {
                    
                    // Authenticate user
                    $loggedInUser = $this->userModel->login($data['username'], $data['password']);

                    if ($loggedInUser) {
                        // Reset rate limits on success
                        unset($_SESSION['login_attempts']);
                        unset($_SESSION['login_locked_until']);

                        // Session Hijacking prevention - regenerate ID
                        session_regenerate_id(true);

                        $this->logActivity('Login', 'Auth', "User '{$loggedInUser->username}' successfully logged in.", $loggedInUser->id);
                        
                        if (isset($_POST['redirect_url']) && !empty($_POST['redirect_url'])) {
                            $_SESSION['redirect_url'] = $_POST['redirect_url'];
                        }
                        
                        $this->createUserSession($loggedInUser);
                    } else {
                        $this->handleFailedAttempt();
                        $this->logActivity('Login Failed', 'Auth', "Failed login attempt for username: '{$data['username']}' (Incorrect Password)");
                        
                        $attempts = $_SESSION['login_attempts'] ?? 0;
                        $remaining = $max_attempts - $attempts;
                        
                        if ($remaining > 0) {
                            $data['password_err'] = "Password incorrect. You have {$remaining} attempts remaining.";
                        } else {
                            $_SESSION['login_locked_until'] = time() + $lockout_time;
                            $this->logActivity('Account Locked', 'Auth', "Secure login locked for 3 minutes for username: '{$data['username']}' due to too many failed attempts.");
                            $data['lockout_err'] = "Too many failed attempts. Secure login is locked for 3 minutes.";
                        }
                    }
                } else {
                    $this->handleFailedAttempt();
                    $this->logActivity('Login Failed', 'Auth', "Failed login attempt for unknown username: '{$data['username']}'");
                    
                    $attempts = $_SESSION['login_attempts'] ?? 0;
                    $remaining = $max_attempts - $attempts;
                    
                    if ($remaining > 0) {
                        $data['username_err'] = "No user found with that username. You have {$remaining} attempts remaining.";
                    } else {
                        $_SESSION['login_locked_until'] = time() + $lockout_time;
                        $this->logActivity('Account Locked', 'Auth', "Secure login locked for 3 minutes for username: '{$data['username']}' due to too many failed attempts.");
                        $data['lockout_err'] = "Too many failed attempts. Secure login is locked for 3 minutes.";
                    }
                }
            }
        }

        // Load Login View
        $this->view('auth/login', $data);
    }

    private function handleFailedAttempt() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 1;
        } else {
            $_SESSION['login_attempts']++;
        }
    }

    public function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        
        // Fetch and load user permissions from the database
        $db = new Database();
        $db->query("SELECT module, can_view, can_create_edit, can_delete FROM user_permissions WHERE user_id = :user_id");
        $db->bind(':user_id', $user->id);
        $perms = $db->resultSet();
        $sessionPerms = [];
        foreach ($perms as $p) {
            $sessionPerms[$p->module] = [
                'can_view' => (bool)$p->can_view,
                'can_create_edit' => (bool)$p->can_create_edit,
                'can_delete' => (bool)$p->can_delete
            ];
        }
        $_SESSION['permissions'] = $sessionPerms;
        
        if (isset($_SESSION['redirect_url']) && !empty($_SESSION['redirect_url'])) {
            $redirectUrl = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        $role = strtolower($user->role);
        if ($role === 'driver') {
            header('Location: ' . APP_URL . '/driver');
        } elseif ($role === 'rep') {
            header('Location: ' . APP_URL . '/rep');
        } else {
            header('Location: ' . APP_URL . '/dashboard');
        }
        exit;
    }

    public function logout() {
        if (isset($_SESSION['username'])) {
            $this->logActivity('Logout', 'Auth', "User '{$_SESSION['username']}' logged out.", $_SESSION['user_id'] ?? null);
        }
        
        // Capture CSRF to keep session token clean
        $csrf = $_SESSION['csrf_token'] ?? null;

        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        session_destroy();
        
        // Re-initialize a secure, clean session and restore CSRF
        session_start();
        if ($csrf) {
            $_SESSION['csrf_token'] = $csrf;
        }

        header('Location: ' . APP_URL . '/auth/login');
        exit;
    }

    public function check_session() {
        header('Content-Type: application/json');
        echo json_encode([
            'logged_in' => isset($_SESSION['user_id'])
        ]);
        exit;
    }

    public function timeout_logout() {
        if (isset($_SESSION['username'])) {
            $this->logActivity('Logout (Inactivity)', 'Auth', "User '{$_SESSION['username']}' logged out due to inactivity.", $_SESSION['user_id'] ?? null);
        }
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        session_destroy();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}