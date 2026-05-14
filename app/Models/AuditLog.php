<?php
class AuditLog {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllLogs($limit = 100) {
        $this->db->query("SELECT a.*, u.username, u.role 
                          FROM audit_logs a 
                          LEFT JOIN users u ON a.user_id = u.id 
                          ORDER BY a.created_at DESC 
                          LIMIT :limit");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function logAction($userId, $action, $module, $description) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $this->db->query("INSERT INTO audit_logs (user_id, action, module, description, ip_address) 
                          VALUES (:uid, :act, :mod, :desc, :ip)");
        
        $this->db->bind(':uid', $userId);
        $this->db->bind(':act', $action);
        $this->db->bind(':mod', $module);
        $this->db->bind(':desc', $description);
        $this->db->bind(':ip', $ip);
        
        return $this->db->execute();
    }
}