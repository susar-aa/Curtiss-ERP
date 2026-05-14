<?php
class Company {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get the global company settings
    public function getSettings() {
        $this->db->query("SELECT * FROM company_settings WHERE id = 1");
        return $this->db->single();
    }

    // Update company details
    public function updateSettings($data) {
        $this->db->query("UPDATE company_settings 
                          SET company_name = :name, 
                              email = :email, 
                              phone = :phone, 
                              address = :address, 
                              tax_number = :tax_number 
                          WHERE id = 1");
                          
        $this->db->bind(':name', $data['company_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':tax_number', $data['tax_number']);
        
        return $this->db->execute();
    }

    // Update logo path specifically
    public function updateLogo($path) {
        $this->db->query("UPDATE company_settings SET logo_path = :path WHERE id = 1");
        $this->db->bind(':path', $path);
        return $this->db->execute();
    }
}