<?php
class Company {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function defaultSettingsObject() {
        return (object) [
            'id' => 1,
            'company_name' => 'CANDENT Enterprise',
            'email' => 'info@candent.com',
            'phone' => '+94 77 123 4567',
            'address' => 'No. 123, Business Hub, Kurunegala, Sri Lanka',
            'tax_number' => 'TIN-948372615',
            'tax_id' => 'TIN-948372615',
            'logo_path' => null,
            'website' => 'www.candent.com',
            'currency' => 'LKR',
        ];
    }

    private function ensureCompanySettingsTable() {
        try {
            $this->db->query("SHOW TABLES LIKE 'company_settings'");
            if (!$this->db->single()) {
                $this->db->query("CREATE TABLE company_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    company_name VARCHAR(255) NOT NULL DEFAULT '',
                    email VARCHAR(255) NULL,
                    phone VARCHAR(50) NULL,
                    address TEXT NULL,
                    tax_number VARCHAR(100) NULL,
                    logo_path VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                $this->db->execute();
            }

            $this->db->query("SHOW COLUMNS FROM company_settings LIKE 'tax_number'");
            if (!$this->db->single()) {
                $this->db->query("SHOW COLUMNS FROM company_settings LIKE 'tax_id'");
                if ($this->db->single()) {
                    $this->db->query("ALTER TABLE company_settings CHANGE tax_id tax_number VARCHAR(100) NULL");
                } else {
                    $this->db->query("ALTER TABLE company_settings ADD COLUMN tax_number VARCHAR(100) NULL");
                }
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Table may already exist with incompatible schema; reads will fall back
        }
    }

    private function normalizeSettingsRow($row) {
        if (!$row) {
            return $this->defaultSettingsObject();
        }
        if (empty($row->tax_number) && !empty($row->tax_id)) {
            $row->tax_number = $row->tax_id;
        }
        if (empty($row->tax_id) && !empty($row->tax_number)) {
            $row->tax_id = $row->tax_number;
        }
        return $row;
    }

    public function getSettings() {
        $this->ensureCompanySettingsTable();

        try {
            $this->db->query("SELECT * FROM company_settings ORDER BY id ASC LIMIT 1");
            $row = $this->db->single();
            if ($row) {
                return $this->normalizeSettingsRow($row);
            }
        } catch (Exception $e) {
            // Fall through to default object
        }

        return $this->defaultSettingsObject();
    }

    /** @deprecated Use getSettings() */
    public function getCompanyDetails() {
        return $this->getSettings();
    }

    public function updateSettings($data) {
        $this->ensureCompanySettingsTable();
        $current = $this->getSettings();

        if (!empty($current->id)) {
            $this->db->query("UPDATE company_settings SET
                company_name = :company_name,
                email = :email,
                phone = :phone,
                address = :address,
                tax_number = :tax_number
                WHERE id = :id");
            $this->db->bind(':id', $current->id);
        } else {
            $this->db->query("INSERT INTO company_settings (company_name, email, phone, address, tax_number)
                VALUES (:company_name, :email, :phone, :address, :tax_number)");
        }

        $this->db->bind(':company_name', $data['company_name'] ?? '');
        $this->db->bind(':email', $data['email'] ?? '');
        $this->db->bind(':phone', $data['phone'] ?? '');
        $this->db->bind(':address', $data['address'] ?? '');
        $this->db->bind(':tax_number', $data['tax_number'] ?? '');

        return $this->db->execute();
    }

    public function updateLogo($logoFileName) {
        $this->ensureCompanySettingsTable();
        $current = $this->getSettings();

        if (!empty($current->id)) {
            $this->db->query("UPDATE company_settings SET logo_path = :logo_path WHERE id = :id");
            $this->db->bind(':id', $current->id);
        } else {
            $this->db->query("INSERT INTO company_settings (company_name, logo_path) VALUES ('Company', :logo_path)");
        }

        $this->db->bind(':logo_path', $logoFileName);
        return $this->db->execute();
    }
}
