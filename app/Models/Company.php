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
            'facebook_page_id' => '',
            'facebook_access_token' => '',
            'sales_commission_pct' => 0.00,
            'sales_incentive_min_value' => 0.00,
            'sales_incentive_pct' => 0.00,
            'sales_incentive_max_limit' => 0.00,
            'productive_visits_payout' => 0.00,
            'working_days_payout' => 0.00,
            'collection_efficiency_payout' => 0.00,
            'vat_percentage' => 0.00,
            'vat_account_id' => null,
            'is_vat_enabled' => 0,
        ];
    }

    private function ensureCompanySettingsTable() {
        // Handled centrally by MigrationManager
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
        if (!isset($row->facebook_page_id)) {
            $row->facebook_page_id = '';
        }
        if (!isset($row->facebook_access_token)) {
            $row->facebook_access_token = '';
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
                ecommerce_store_url = :ecommerce_store_url,
                facebook_page_id = :facebook_page_id,
                facebook_access_token = :facebook_access_token,
                vat_percentage = :vat_percentage,
                vat_account_id = :vat_account_id,
                is_vat_enabled = :is_vat_enabled
                WHERE id = :id");
            $this->db->bind(':id', $current->id);
        } else {
            $this->db->query("INSERT INTO company_settings (company_name, email, phone, address, tax_number, ecommerce_store_url, facebook_page_id, facebook_access_token, vat_percentage, vat_account_id, is_vat_enabled)
                VALUES (:company_name, :email, :phone, :address, :tax_number, :ecommerce_store_url, :facebook_page_id, :facebook_access_token, :vat_percentage, :vat_account_id, :is_vat_enabled)");
        }

        $this->db->bind(':company_name', $data['company_name'] ?? '');
        $this->db->bind(':email', $data['email'] ?? '');
        $this->db->bind(':phone', $data['phone'] ?? '');
        $this->db->bind(':address', $data['address'] ?? '');
        $this->db->bind(':tax_number', $data['tax_number'] ?? '');
        $this->db->bind(':ecommerce_store_url', $data['ecommerce_store_url'] ?? '');
        $this->db->bind(':facebook_page_id', $data['facebook_page_id'] ?? '');
        $this->db->bind(':facebook_access_token', $data['facebook_access_token'] ?? '');
        
        $this->db->bind(':vat_percentage', floatval($data['vat_percentage'] ?? 0.00));
        $this->db->bind(':vat_account_id', !empty($data['vat_account_id']) ? intval($data['vat_account_id']) : null);
        $this->db->bind(':is_vat_enabled', !empty($data['is_vat_enabled']) ? 1 : 0);

        return $this->db->execute();
    }

    public function updatePayrollSettings($data) {
        $this->ensureCompanySettingsTable();
        $current = $this->getSettings();

        if (!empty($current->id)) {
            $this->db->query("UPDATE company_settings SET
                sales_commission_pct = :scp,
                sales_incentive_min_value = :simv,
                sales_incentive_pct = :sip,
                sales_incentive_max_limit = :siml,
                productive_visits_payout = :pvp,
                working_days_payout = :wdp,
                collection_efficiency_payout = :cep
                WHERE id = :id");
            $this->db->bind(':id', $current->id);
        } else {
            $this->db->query("INSERT INTO company_settings (company_name, sales_commission_pct, sales_incentive_min_value, sales_incentive_pct, sales_incentive_max_limit, productive_visits_payout, working_days_payout, collection_efficiency_payout)
                VALUES ('Company', :scp, :simv, :sip, :siml, :pvp, :wdp, :cep)");
        }

        $this->db->bind(':scp', $data['sales_commission_pct'] ?? 0.00);
        $this->db->bind(':simv', $data['sales_incentive_min_value'] ?? 0.00);
        $this->db->bind(':sip', $data['sales_incentive_pct'] ?? 0.00);
        $this->db->bind(':siml', $data['sales_incentive_max_limit'] ?? 0.00);
        $this->db->bind(':pvp', $data['productive_visits_payout'] ?? 0.00);
        $this->db->bind(':wdp', $data['working_days_payout'] ?? 0.00);
        $this->db->bind(':cep', $data['collection_efficiency_payout'] ?? 0.00);

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
