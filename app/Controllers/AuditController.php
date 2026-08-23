<?php
class AuditController extends Controller {
    private $auditModel;
    private $userModel;

    public function __construct() {
        // Must be logged in
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        
        // STRICT RBAC: Only Admins can view the Audit Trail
        if ($_SESSION['role'] !== 'Admin') {
            die("Access Denied: Only Administrators can view the System Audit Logs.");
        }
        
        $this->auditModel = $this->model('AuditLog');
        $this->userModel = $this->model('User');
    }

    public function index() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'module' => $_GET['module'] ?? '',
            'action' => $_GET['action'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $logs = $this->auditModel->getFilteredLogs($filters, 250);

        // Calculate statistics based on the active filtered logs dataset
        $totalLogsCount = count($logs);
        $dbModCount = 0;
        $systemAlertsCount = 0;
        $uniqueUsers = [];

        foreach ($logs as $log) {
            $act = strtolower($log->action ?? '');
            $desc = strtolower($log->description ?? '');
            
            // Database modifications count
            if (strpos($act, 'create') !== false || strpos($act, 'edit') !== false || 
                strpos($act, 'delete') !== false || strpos($act, 'update') !== false || 
                strpos($act, 'post') !== false || strpos($act, 'void') !== false ||
                strpos($act, 'import') !== false) {
                $dbModCount++;
            }
            
            // Warnings/Alerts
            if (strpos($act, 'warning') !== false || strpos($act, 'fail') !== false || 
                strpos($desc, 'limit') !== false || strpos($desc, 'block') !== false || 
                strpos($desc, 'exceed') !== false) {
                $systemAlertsCount++;
            }
            
            if (!empty($log->username)) {
                $uniqueUsers[$log->username] = true;
            }
        }
        
        $stats = [
            'total_actions' => $totalLogsCount,
            'db_modifications' => $dbModCount,
            'system_alerts' => $systemAlertsCount,
            'unique_operators' => count($uniqueUsers)
        ];

        $data = [
            'title' => 'System Audit Logs',
            'content_view' => 'audits/index',
            'logs' => $logs,
            'users' => $this->userModel->getAllUsers(),
            'modules' => $this->auditModel->getUniqueModules(),
            'actions' => $this->auditModel->getUniqueActions(),
            'filters' => $filters,
            'stats' => $stats
        ];

        $this->view('layouts/main', $data);
    }

    public function export() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'module' => $_GET['module'] ?? '',
            'action' => $_GET['action'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Fetch logs (higher limit for CSV export)
        $logs = $this->auditModel->getFilteredLogs($filters, 2000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=forensic_audit_logs_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, ['Timestamp', 'Operator', 'Role', 'Action', 'Module', 'Description', 'Record Reference ID', 'IP Address', 'Browser/Device']);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->created_at,
                $log->username ?? 'System',
                $log->role ?? 'N/A',
                $log->action,
                $log->module,
                $log->description,
                $log->record_id ?? 'N/A',
                $log->ip_address,
                $log->browser_device ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }
}