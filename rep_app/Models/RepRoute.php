<?php
class RepRoute {
    private $db;

    public function __construct() {
        $this->db = new Database(); // Uses your core Database class
    }

    public function getActiveRoute($userId) {
        // NEW: JOIN mca_areas to pull the budget and actual distance metrics for the dashboard
        $this->db->query("SELECT r.*, m.budget_km, m.actual_route_km 
                          FROM rep_daily_routes r 
                          LEFT JOIN mca_areas m ON r.route_name = m.name 
                          WHERE r.user_id = :uid AND r.status = 'Active' 
                          LIMIT 1");
        $this->db->bind(':uid', $userId);
        return $this->db->single();
    }

    public function getMcaRoutes() {
        $this->db->query("SELECT * FROM mca_areas ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function startRoute($userId, $routeName, $startMeter, $lat, $lng) {
        $this->db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, start_lat, start_lng, status) 
                          VALUES (:uid, :rname, :meter, NOW(), :lat, :lng, 'Active')");
        
        $this->db->bind(':uid', $userId);
        $this->db->bind(':rname', $routeName);
        $this->db->bind(':meter', $startMeter);
        $this->db->bind(':lat', $lat);
        $this->db->bind(':lng', $lng);
        
        return $this->db->execute();
    }
}