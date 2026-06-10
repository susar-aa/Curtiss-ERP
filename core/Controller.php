<?php
class Controller {
    // Load model
    public function model($model) {
        require_once '../app/Models/' . $model . '.php';
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        // Check for view file
        if (file_exists('../app/Views/' . $view . '.php')) {
            require_once '../app/Views/' . $view . '.php';
        } else {
            // View does not exist
            die("View '" . $view . "' does not exist.");
        }
    }

    /**
     * Helper to log system activity
     */
    protected function logActivity($action, $module, $description, $recordId = null, $oldValues = null, $newValues = null) {
        try {
            $userId = $_SESSION['user_id'] ?? 0;
            require_once '../app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, $action, $module, $description, $recordId, $oldValues, $newValues);
        } catch (Exception $e) {
            // Failsafe to avoid crashing the main application flow
        }
    }

    /**
     * Check permissions for module access
     */
    public function checkPermission($module, $action = 'view') {
        // Admin role always has all access
        if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
            return true;
        }
        
        // If not logged in, redirect to login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }

        $hasAccess = false;
        if (isset($_SESSION['permissions']) && isset($_SESSION['permissions'][$module])) {
            $perm = $_SESSION['permissions'][$module];
            if ($action === 'view') {
                $hasAccess = (bool)($perm['can_view'] ?? false);
            } elseif ($action === 'create_edit') {
                $hasAccess = (bool)($perm['can_create_edit'] ?? false);
            } elseif ($action === 'delete') {
                $hasAccess = (bool)($perm['can_delete'] ?? false);
            }
        }

        if (!$hasAccess) {
            // If AJAX request, return a clean JSON or plain error message
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                echo "Access Denied: You do not have permission to perform this action.";
                exit;
            }

            // Load error layout or page
            $this->view('layouts/main', [
                'title' => 'Access Denied',
                'content_view' => 'auth/access_denied',
                'error_message' => "You do not have permission to " . str_replace('_', ' and ', $action) . " the " . strtoupper($module) . " module. Please contact your system administrator."
            ]);
            exit;
        }
        return true;
    }
}