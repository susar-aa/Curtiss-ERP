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
            'ecommerce_store_url' => 'http://localhost/Curtiss%20E%20Commerce',
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
                    ecommerce_store_url VARCHAR(255) NULL DEFAULT '',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                $this->db->execute();
            }

            $this->db->query("SHOW COLUMNS FROM company_settings LIKE 'ecommerce_store_url'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE company_settings ADD COLUMN ecommerce_store_url VARCHAR(255) NULL DEFAULT ''");
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

            // Ensure customers table has username and password
            $this->db->query("SHOW COLUMNS FROM customers LIKE 'username'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE customers ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER email");
                $this->db->execute();
            }
            $this->db->query("SHOW COLUMNS FROM customers LIKE 'password'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE customers ADD COLUMN password VARCHAR(255) NULL AFTER username");
                $this->db->execute();
            }

            // Ensure wholesaler_requests table exists
            $this->db->query("SHOW TABLES LIKE 'wholesaler_requests'");
            if (!$this->db->single()) {
                $this->db->query("CREATE TABLE wholesaler_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    business_name VARCHAR(150) NOT NULL,
                    address TEXT NOT NULL,
                    contact_number VARCHAR(50) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    email_address VARCHAR(150) NOT NULL UNIQUE,
                    username VARCHAR(100) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    notes TEXT NULL,
                    status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
                    linked_customer_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (linked_customer_id) REFERENCES customers(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->db->execute();
            }

            // Ensure ecommerce_retail_customers table exists
            $this->db->query("SHOW TABLES LIKE 'ecommerce_retail_customers'");
            if (!$this->db->single()) {
                $this->db->query("CREATE TABLE ecommerce_retail_customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) NOT NULL UNIQUE,
                    username VARCHAR(100) UNIQUE NULL,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(50) NULL,
                    address TEXT NULL,
                    city VARCHAR(100) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->db->execute();
            }

        } catch (Exception $e) {
            // Table/column alterations might already be active
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
        if (!isset($row->ecommerce_store_url)) {
            $row->ecommerce_store_url = '';
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
                tax_number = :tax_number,
                ecommerce_store_url = :ecommerce_store_url
                WHERE id = :id");
            $this->db->bind(':id', $current->id);
        } else {
            $this->db->query("INSERT INTO company_settings (company_name, email, phone, address, tax_number, ecommerce_store_url)
                VALUES (:company_name, :email, :phone, :address, :tax_number, :ecommerce_store_url)");
        }

        $this->db->bind(':company_name', $data['company_name'] ?? '');
        $this->db->bind(':email', $data['email'] ?? '');
        $this->db->bind(':phone', $data['phone'] ?? '');
        $this->db->bind(':address', $data['address'] ?? '');
        $this->db->bind(':tax_number', $data['tax_number'] ?? '');
        $this->db->bind(':ecommerce_store_url', $data['ecommerce_store_url'] ?? '');

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
