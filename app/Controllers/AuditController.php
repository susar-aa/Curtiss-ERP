<?php
class AuditController extends Controller {
    private $auditModel;

    public function __construct() {
        // Must be logged in
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        
        // STRICT RBAC: Only Admins can view the Audit Trail
        if ($_SESSION['role'] !== 'Admin') {
            die("Access Denied: Only Administrators can view the System Audit Logs.");
        }
        
        $this->auditModel = $this->model('AuditLog');
    }

    public function index() {
        $data = [
            'title' => 'System Audit Logs',
            'content_view' => 'audits/index',
            'logs' => $this->auditModel->getAllLogs(250) // Fetch the last 250 actions
        ];

        $this->view('layouts/main', $data);
    }
}