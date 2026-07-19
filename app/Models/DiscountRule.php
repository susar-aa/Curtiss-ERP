<?php

class DiscountRule {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->migrateTables();
    }

    private function migrateTables() {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS discount_rules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    rule_type VARCHAR(50) NOT NULL DEFAULT 'item_wise',
                    reward_type VARCHAR(50) NOT NULL DEFAULT 'free_issue',
                    target_item_id INT NULL,
                    target_category_id INT NULL,
                    status VARCHAR(50) DEFAULT 'Active',
                    start_date DATE NULL DEFAULT NULL,
                    end_date DATE NULL DEFAULT NULL,
                    discount_cap DECIMAL(10,2) NULL DEFAULT NULL,
                    description TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (target_item_id),
                    INDEX (target_category_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $this->db->execute();

            $this->db->query("
                CREATE TABLE IF NOT EXISTS discount_rule_tiers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rule_id INT NOT NULL,
                    min_threshold DECIMAL(10,2) NOT NULL,
                    max_threshold DECIMAL(10,2) NULL,
                    reward_val DECIMAL(10,2) NOT NULL,
                    INDEX (rule_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $this->db->execute();

            $this->addColIfNotExists('discount_rules', 'reward_type', "VARCHAR(50) NOT NULL DEFAULT 'free_issue'");
            $this->addColIfNotExists('discount_rules', 'target_category_id', "INT NULL DEFAULT NULL");
            $this->addColIfNotExists('discount_rules', 'start_date', "DATE NULL DEFAULT NULL");
            $this->addColIfNotExists('discount_rules', 'end_date', "DATE NULL DEFAULT NULL");
            $this->addColIfNotExists('discount_rules', 'discount_cap', "DECIMAL(10,2) NULL DEFAULT NULL");
            $this->addColIfNotExists('discount_rules', 'description', "TEXT NULL DEFAULT NULL");
        } catch (Exception $e) {
            // Silence migration errors
        }
    }

    private function addColIfNotExists($table, $column, $definition) {
        try {
            $this->db->query("SHOW COLUMNS FROM {$table} LIKE :col");
            $this->db->bind(':col', $column);
            $res = $this->db->single();
            if (!$res) {
                $this->db->query("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
                $this->db->execute();
            }
        } catch (Exception $e) {}
    }

    public function getAllRules($filters = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(r.name LIKE :search OR i.name LIKE :search OR i.item_code LIKE :search OR c.name LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['rule_type'])) {
            $where[] = "r.rule_type = :rule_type";
            $params['rule_type'] = trim($filters['rule_type']);
        }

        if (!empty($filters['status'])) {
            $where[] = "r.status = :status";
            $params['status'] = trim($filters['status']);
        }

        $whereClause = implode(' AND ', $where);

        $this->db->query("
            SELECT r.*, 
                   i.name as item_name, i.item_code as item_sku,
                   c.name as category_name
            FROM discount_rules r 
            LEFT JOIN items i ON r.target_item_id = i.id 
            LEFT JOIN item_categories c ON r.target_category_id = c.id
            WHERE {$whereClause}
            ORDER BY r.id DESC
        ");
        foreach ($params as $k => $v) {
            $this->db->bind(':' . $k, $v);
        }
        $rules = $this->db->resultSet() ?: [];
        foreach ($rules as &$rule) {
            $rule->tiers = $this->getTiersByRuleId($rule->id);
            $rule->is_expired = ($rule->end_date && strtotime($rule->end_date) < strtotime(date('Y-m-d')));
            $rule->is_upcoming = ($rule->start_date && strtotime($rule->start_date) > strtotime(date('Y-m-d')));
        }
        return $rules;
    }

    public function getActiveRules() {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT r.*, 
                   i.name as item_name, i.item_code as item_sku,
                   c.name as category_name
            FROM discount_rules r 
            LEFT JOIN items i ON r.target_item_id = i.id 
            LEFT JOIN item_categories c ON r.target_category_id = c.id
            WHERE r.status = 'Active'
              AND (r.start_date IS NULL OR r.start_date <= :today1)
              AND (r.end_date IS NULL OR r.end_date >= :today2)
            ORDER BY r.id DESC
        ");
        $this->db->bind(':today1', $today);
        $this->db->bind(':today2', $today);
        $rules = $this->db->resultSet() ?: [];
        foreach ($rules as &$rule) {
            $rule->tiers = $this->getTiersByRuleId($rule->id);
        }
        return $rules;
    }

    public function getTiersByRuleId($ruleId) {
        $this->db->query("SELECT * FROM discount_rule_tiers WHERE rule_id = :rule_id ORDER BY min_threshold ASC");
        $this->db->bind(':rule_id', $ruleId);
        return $this->db->resultSet() ?: [];
    }

    public function getRuleById($id) {
        $this->db->query("
            SELECT r.*, 
                   i.name as item_name, i.item_code as item_sku,
                   c.name as category_name
            FROM discount_rules r 
            LEFT JOIN items i ON r.target_item_id = i.id 
            LEFT JOIN item_categories c ON r.target_category_id = c.id
            WHERE r.id = :id
        ");
        $this->db->bind(':id', $id);
        $rule = $this->db->single();
        if ($rule) {
            $rule->tiers = $this->getTiersByRuleId($rule->id);
        }
        return $rule;
    }

    public function addRule($data, $tiers) {
        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO discount_rules 
                (name, rule_type, reward_type, target_item_id, target_category_id, status, start_date, end_date, discount_cap, description) 
                VALUES 
                (:name, :rule_type, :reward_type, :target_item_id, :target_category_id, :status, :start_date, :end_date, :discount_cap, :description)");
            
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':rule_type', $data['rule_type']);
            $this->db->bind(':reward_type', $data['reward_type'] ?? 'free_issue');
            $this->db->bind(':target_item_id', !empty($data['target_item_id']) ? intval($data['target_item_id']) : null);
            $this->db->bind(':target_category_id', !empty($data['target_category_id']) ? intval($data['target_category_id']) : null);
            $this->db->bind(':status', $data['status'] ?? 'Active');
            $this->db->bind(':start_date', !empty($data['start_date']) ? $data['start_date'] : null);
            $this->db->bind(':end_date', !empty($data['end_date']) ? $data['end_date'] : null);
            $this->db->bind(':discount_cap', !empty($data['discount_cap']) ? floatval($data['discount_cap']) : null);
            $this->db->bind(':description', !empty($data['description']) ? $data['description'] : null);
            $this->db->execute();
            $ruleId = $this->db->lastInsertId();

            if (!empty($tiers) && is_array($tiers)) {
                foreach ($tiers as $tier) {
                    $this->db->query("INSERT INTO discount_rule_tiers (rule_id, min_threshold, max_threshold, reward_val) VALUES (:rule_id, :min_threshold, :max_threshold, :reward_val)");
                    $this->db->bind(':rule_id', $ruleId);
                    $this->db->bind(':min_threshold', floatval($tier['min_threshold']));
                    $this->db->bind(':max_threshold', isset($tier['max_threshold']) && $tier['max_threshold'] !== '' ? floatval($tier['max_threshold']) : null);
                    $this->db->bind(':reward_val', floatval($tier['reward_val']));
                    $this->db->execute();
                }
            }

            $this->db->commit();
            return $ruleId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function updateRule($id, $data, $tiers) {
        try {
            $this->db->beginTransaction();

            $this->db->query("UPDATE discount_rules SET
                name = :name,
                rule_type = :rule_type,
                reward_type = :reward_type,
                target_item_id = :target_item_id,
                target_category_id = :target_category_id,
                status = :status,
                start_date = :start_date,
                end_date = :end_date,
                discount_cap = :discount_cap,
                description = :description
                WHERE id = :id");

            $this->db->bind(':id', $id);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':rule_type', $data['rule_type']);
            $this->db->bind(':reward_type', $data['reward_type'] ?? 'free_issue');
            $this->db->bind(':target_item_id', !empty($data['target_item_id']) ? intval($data['target_item_id']) : null);
            $this->db->bind(':target_category_id', !empty($data['target_category_id']) ? intval($data['target_category_id']) : null);
            $this->db->bind(':status', $data['status'] ?? 'Active');
            $this->db->bind(':start_date', !empty($data['start_date']) ? $data['start_date'] : null);
            $this->db->bind(':end_date', !empty($data['end_date']) ? $data['end_date'] : null);
            $this->db->bind(':discount_cap', !empty($data['discount_cap']) ? floatval($data['discount_cap']) : null);
            $this->db->bind(':description', !empty($data['description']) ? $data['description'] : null);
            $this->db->execute();

            // Clear old tiers and re-insert
            $this->db->query("DELETE FROM discount_rule_tiers WHERE rule_id = :rule_id");
            $this->db->bind(':rule_id', $id);
            $this->db->execute();

            if (!empty($tiers) && is_array($tiers)) {
                foreach ($tiers as $tier) {
                    $this->db->query("INSERT INTO discount_rule_tiers (rule_id, min_threshold, max_threshold, reward_val) VALUES (:rule_id, :min_threshold, :max_threshold, :reward_val)");
                    $this->db->bind(':rule_id', $id);
                    $this->db->bind(':min_threshold', floatval($tier['min_threshold']));
                    $this->db->bind(':max_threshold', isset($tier['max_threshold']) && $tier['max_threshold'] !== '' ? floatval($tier['max_threshold']) : null);
                    $this->db->bind(':reward_val', floatval($tier['reward_val']));
                    $this->db->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function deleteRule($id) {
        try {
            $this->db->beginTransaction();

            $this->db->query("DELETE FROM discount_rule_tiers WHERE rule_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->query("DELETE FROM discount_rules WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function toggleRuleStatus($id, $status) {
        $this->db->query("UPDATE discount_rules SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function duplicateRule($id) {
        $existing = $this->getRuleById($id);
        if (!$existing) return false;

        $newRuleData = [
            'name' => $existing->name . ' (Copy)',
            'rule_type' => $existing->rule_type,
            'reward_type' => $existing->reward_type,
            'target_item_id' => $existing->target_item_id,
            'target_category_id' => $existing->target_category_id,
            'status' => 'Inactive',
            'start_date' => $existing->start_date,
            'end_date' => $existing->end_date,
            'discount_cap' => $existing->discount_cap,
            'description' => $existing->description
        ];

        $tiers = [];
        if (!empty($existing->tiers)) {
            foreach ($existing->tiers as $t) {
                $tiers[] = [
                    'min_threshold' => $t->min_threshold,
                    'max_threshold' => $t->max_threshold,
                    'reward_val' => $t->reward_val
                ];
            }
        }

        return $this->addRule($newRuleData, $tiers);
    }
}
