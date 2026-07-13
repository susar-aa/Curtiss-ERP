<?php
declare(strict_types=1);

require_once __DIR__ . '/AuditLog.php';

class PettyCashConfig {
    private Database $db;
    private AuditLog $audit;

    public function __construct() {
        $this->db = new Database();
        $this->audit = new AuditLog();
    }

    public function getConfig(): ?stdClass {
        $this->db->query("SELECT c.*, u.username as custodian_name, a.account_name as default_funding_account_name 
                          FROM petty_cash_config c
                          LEFT JOIN users u ON c.custodian_id = u.id
                          LEFT JOIN chart_of_accounts a ON c.default_funding_account_id = a.id
                          ORDER BY c.id ASC LIMIT 1");
        return $this->db->single() ?: null;
    }

    public function updateConfig(array $data, int $userId): bool {
        try {
            $current = $this->getConfig();
            $oldValues = $current ? (array)$current : null;

            $this->db->beginTransaction();

            $this->db->query("UPDATE petty_cash_config 
                              SET limit_amount = :limit_amount, 
                                  custodian_id = :custodian_id, 
                                  require_approval = :require_approval, 
                                  default_funding_account_id = :default_funding_account_id, 
                                  reimbursement_threshold = :reimbursement_threshold
                              WHERE id = 1");
            
            $this->db->bind(':limit_amount', $data['limit_amount']);
            $this->db->bind(':custodian_id', $data['custodian_id']);
            $this->db->bind(':require_approval', $data['require_approval']);
            $this->db->bind(':default_funding_account_id', $data['default_funding_account_id']);
            $this->db->bind(':reimbursement_threshold', $data['reimbursement_threshold']);
            $this->db->execute();

            // Insert config history
            $this->db->query("INSERT INTO petty_cash_config_history 
                              (limit_amount, custodian_id, require_approval, default_funding_account_id, reimbursement_threshold, changed_by, action) 
                              VALUES (:limit_amount, :custodian_id, :require_approval, :default_funding_account_id, :reimbursement_threshold, :changed_by, :action)");
            
            $this->db->bind(':limit_amount', $data['limit_amount']);
            $this->db->bind(':custodian_id', $data['custodian_id']);
            $this->db->bind(':require_approval', $data['require_approval']);
            $this->db->bind(':default_funding_account_id', $data['default_funding_account_id']);
            $this->db->bind(':reimbursement_threshold', $data['reimbursement_threshold']);
            $this->db->bind(':changed_by', $userId);
            $this->db->bind(':action', 'UPDATE');
            $this->db->execute();

            $this->db->commit();

            // Log action in audit trail
            $desc = "Updated petty cash settings: Limit = " . $data['limit_amount'] . ", Threshold = " . $data['reimbursement_threshold'];
            $this->audit->logAction($userId, 'UPDATE', 'accounting', $desc, 1, $oldValues, $data);

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
