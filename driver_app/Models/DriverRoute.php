<?php
class DriverRoute {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUserDetails($userId) {
        $this->db->query("SELECT u.id, u.username, u.role, u.employee_id, 
                                 CONCAT(e.first_name, ' ', e.last_name) as full_name
                          FROM users u
                          LEFT JOIN employees e ON u.employee_id = e.id
                          WHERE u.id = :uid");
        $this->db->bind(':uid', $userId);
        return $this->db->single();
    }

    public function getAssignedDelivery($userId) {
        $user = $this->getUserDetails($userId);
        if (!$user) return false;

        $fullName = $user->full_name ?: $user->username;
        $username = $user->username;

        $this->db->query("
            SELECT d.*, r.route_name, r.start_time,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            WHERE d.status != 'Completed' AND 
                  (d.driver_name = :fullName OR d.partner_name = :fullName OR 
                   d.driver_name = :username OR d.partner_name = :username)
            ORDER BY d.delivery_date DESC, d.created_at DESC
            LIMIT 1
        ");
        $this->db->bind(':fullName', $fullName);
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function getDeliveryById($id) {
        $this->db->query("
            SELECT d.*, r.route_name, r.start_time,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            WHERE d.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function acceptRoute($deliveryId) {
        $this->db->query("UPDATE deliveries SET status = 'Accepted', accepted_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $deliveryId);
        return $this->db->execute();
    }

    public function startTrip($deliveryId, $startMeter, $driverName, $partnerName) {
        $this->db->query("UPDATE deliveries 
                          SET status = 'In Transit', 
                              start_meter = :startMeter, 
                              started_at = NOW(), 
                              driver_name = :driverName, 
                              partner_name = :partnerName 
                          WHERE id = :id");
        $this->db->bind(':id', $deliveryId);
        $this->db->bind(':startMeter', $startMeter);
        $this->db->bind(':driverName', $driverName);
        $this->db->bind(':partnerName', $partnerName);
        return $this->db->execute();
    }

    public function getActiveEmployees() {
        $this->db->query("SELECT *, CONCAT(first_name, ' ', last_name) as full_name 
                          FROM employees 
                          WHERE status = 'Active' 
                          ORDER BY first_name ASC");
        return $this->db->resultSet();
    }

    public function getDeliveryShops($routeId) {
        $this->db->query("
            SELECT c.id, c.name, c.phone, c.address,
                COUNT(i.id) as invoice_count,
                SUM(CASE WHEN i.delivery_status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_amount
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.rep_route_id = :rid AND i.status != 'Voided'
            GROUP BY c.id, c.name, c.phone, c.address
            ORDER BY c.name ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }

    public function endTrip($deliveryId, $endMeter, $cashDenomJson = null) {
        $this->db->beginTransaction();
        try {
            $delivery = $this->getDeliveryById($deliveryId);
            if (!$delivery) {
                throw new Exception("Delivery not found");
            }

            // 1. Update delivery status
            $this->db->query("UPDATE deliveries 
                              SET status = 'Completed', end_meter = :endMeter, cash_denominations = :cashDenoms, completed_at = NOW() 
                              WHERE id = :id");
            $this->db->bind(':id', $deliveryId);
            $this->db->bind(':endMeter', $endMeter);
            $this->db->bind(':cashDenoms', $cashDenomJson);
            $this->db->execute();

            // 2. Update rep_daily_routes status
            $this->db->query("UPDATE rep_daily_routes 
                              SET end_meter = :endMeter, end_time = NOW(), status = 'Completed' 
                              WHERE id = :route_id");
            $this->db->bind(':route_id', $delivery->rep_route_id);
            $this->db->bind(':endMeter', $endMeter);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("endTrip error: " . $e->getMessage());
            return false;
        }
    }
}
