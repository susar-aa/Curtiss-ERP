<?php
class DriverController {
    
    // Load model from the isolated driver_app/Models folder with fallback to app/Models
    public function model($model) {
        if (file_exists('../driver_app/Models/' . $model . '.php')) {
            require_once '../driver_app/Models/' . $model . '.php';
        } elseif (file_exists('../app/Models/' . $model . '.php')) {
            require_once '../app/Models/' . $model . '.php';
        } else {
            die("Model does not exist: " . $model);
        }
        return new $model();
    }

    // Load view from the isolated driver_app/Views folder
    public function view($view, $data = []) {
        if (file_exists('../driver_app/Views/' . $view . '.php')) {
            require_once '../driver_app/Views/' . $view . '.php';
        } else {
            die("Driver View does not exist: " . $view);
        }
    }

    /**
     * Helper to log system activity
     */
    protected function logActivity($action, $module, $description, $recordId = null, $oldValues = null, $newValues = null) {
        try {
            $userId = $_SESSION['user_id'] ?? 0;
            require_once dirname(__DIR__) . '/app/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, $action, $module, $description, $recordId, $oldValues, $newValues);
        } catch (Exception $e) {
            // Failsafe
        }
    }
}
