<?php
class Company {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * NEW: Fetches the primary company administrative profile details.
     * Maps database table configurations or falls back to standard settings 
     * to prevent public invoice view compilation errors.
     */
    public function getCompanyDetails() {
        // Step 1: Check if a structural company_settings table exists
        $this->db->query("SHOW TABLES LIKE 'company_settings'");
        $tableExists = $this->db->single();

        if ($tableExists) {
            $this->db->query("SELECT * FROM company_settings LIMIT 1");
            $details = $this->db->single();
            if ($details) {
                return $details;
            }
        }

        // Step 2: Fallback configuration matching your ERP business profile
        // Returns a structured object to prevent standard property lookup crashes
        return (object) [
            'company_name' => 'CANDENT Enterprise',
            'email'        => 'info@candent.com',
            'phone'        => '+94 77 123 4567',
            'address'      => 'No. 123, Business Hub, Kurunegala, Sri Lanka',
            'tax_number'   => 'TIN-948372615',
            'website'      => 'www.candent.com',
            'currency'     => 'LKR'
        ];
    }
}