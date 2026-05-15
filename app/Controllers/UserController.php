<?php
class UserController extends Controller {
    private $userModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        if ($_SESSION['role'] !== 'Admin') { die("Access Denied: Only Admins can manage users."); }
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
            
            // Handle Signature Upload
            $signaturePath = null;
            if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $signaturePath = 'sig_' . time() . '_' . rand(100,999) . '.' . $ext;
                    move_uploaded_file($_FILES['signature']['tmp_name'], '../public/uploads/' . $signaturePath);
                }
            }

            $userData = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'role' => $_POST['role'],
                'signature_path' => $signaturePath
            ];

            if (empty($userData['username']) || empty($userData['password'])) {
                $data['error'] = 'Username and Password are required.';
            } elseif ($this->userModel->findUserByUsername($userData['username'])) {
                $data['error'] = 'Username is already taken.';
            } else {
                if ($this->userModel->createUser($userData)) {
                    $data['success'] = 'User account created with signature successfully!';
                    $data['users'] = $this->userModel->getAllUsers();
                } else {
                    $data['error'] = 'Failed to create user.';
                }
            }
        }
        $this->view('layouts/main', $data);
    }
}