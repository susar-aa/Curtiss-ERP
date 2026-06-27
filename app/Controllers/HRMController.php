<?php
class HrmController extends Controller {
    private $employeeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->employeeModel = $this->model('Employee');
    }

    public function index() {
        $this->checkPermission('hrm', 'view');

        $data = [
            'title' => 'HRM & Employees',
            'content_view' => 'hrm/index',
            'employees' => $this->employeeModel->getAllEmployees(),
            'error' => '',
            'success' => ''
        ];

        // Handle success/error flash messages
        if (isset($_SESSION['flash_success'])) {
            $data['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            $data['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_employee') {
            $this->checkPermission('hrm', 'create_edit');

            $empData = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'department' => trim($_POST['department']),
                'job_title' => trim($_POST['job_title']),
                'base_salary' => floatval($_POST['base_salary']),
                'hire_date' => $_POST['hire_date']
            ];

            $createLogin = isset($_POST['create_login']) && $_POST['create_login'] == '1';
            $userData = null;

            if ($createLogin) {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? 'office';
                
                $appsArray = $_POST['accessible_apps'] ?? ['ERP System'];
                $accessibleApps = implode(',', $appsArray);

                $userModel = $this->model('User');
                if ($userModel->findUserByUsername($username)) {
                    $data['error'] = 'Failed to add employee: Username is already taken.';
                    $this->view('layouts/main', $data);
                    return;
                }

                $userData = [
                    'username' => $username,
                    'email' => trim($_POST['email']),
                    'password' => $password,
                    'role' => $role,
                    'signature_path' => null,
                    'status' => 'Active',
                    'accessible_apps' => $accessibleApps
                ];
            }

            try {
                if ($this->employeeModel->addEmployee($empData, $userData)) {
                    $_SESSION['flash_success'] = $createLogin 
                        ? 'Employee and user login created successfully.' 
                        : 'Employee added successfully.';
                    header('Location: ' . APP_URL . '/hrm');
                    exit;
                } else {
                    $data['error'] = 'Failed to add employee.';
                }
            } catch (Exception $e) {
                $data['error'] = 'Database Error: ' . $e->getMessage();
            }
        }

        $this->view('layouts/main', $data);
    }

    public function edit() {
        $this->checkPermission('hrm', 'create_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/hrm');
            exit;
        }

        $id = intval($_POST['id']);
        $empData = [
            'id' => $id,
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'department' => trim($_POST['department']),
            'job_title' => trim($_POST['job_title']),
            'base_salary' => floatval($_POST['base_salary']),
            'hire_date' => $_POST['hire_date'],
            'status' => $_POST['status'] ?? 'Active'
        ];

        $createLogin = isset($_POST['create_login']) && $_POST['create_login'] == '1';
        $userData = null;

        if ($createLogin) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'office';
            
            $appsArray = $_POST['accessible_apps'] ?? ['ERP System'];
            $accessibleApps = implode(',', $appsArray);

            // Check if username conflict exists with another user
            $userModel = $this->model('User');
            $existing = $userModel->findUserByUsername($username);
            if ($existing && $existing->employee_id != $id) {
                $_SESSION['flash_error'] = 'Failed to update employee: Username is already taken by another account.';
                header('Location: ' . APP_URL . '/hrm');
                exit;
            }

            $userData = [
                'username' => $username,
                'email' => trim($_POST['email']),
                'password' => $password, // empty password handled inside updateEmployee
                'role' => $role,
                'status' => 'Active',
                'accessible_apps' => $accessibleApps
            ];
        }

        try {
            if ($this->employeeModel->updateEmployee($empData, $userData)) {
                $_SESSION['flash_success'] = 'Employee details updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to update employee details.';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database Error: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/hrm');
        exit;
    }

    public function delete($id) {
        $this->checkPermission('hrm', 'delete');

        try {
            if ($this->employeeModel->deleteEmployee($id)) {
                $_SESSION['flash_success'] = 'Employee and their linked user account deleted successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to delete employee.';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database Error: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/hrm');
        exit;
    }
}