<?php

// Enable error reporting to prevent blank 500 errors in the future
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class RepTracking {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllRoutes() {
        $this->db->query("
            SELECT r.*, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            WHERE r.id NOT IN (SELECT rep_route_id FROM deliveries)
            ORDER BY r.start_time DESC
        ");
        return $this->db->resultSet();
    }

    public function getRouteBills($routeId) {
        $this->db->query("
            SELECT i.*, c.name as customer_name,
            (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.rep_route_id = :rid
            ORDER BY i.created_at ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }

    // UPDATED: Now fetches live high-level statistics for the printable report header block
    public function getRouteById($routeId) {
        $this->db->query("
            SELECT r.*, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            WHERE r.id = :rid
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->single();
    }

    public function getRouteLoadingItems($routeId) {
        $this->db->query("
            SELECT ii.description as item_name, SUM(ii.quantity) as total_qty 
            FROM invoice_items ii 
            JOIN invoices i ON ii.invoice_id = i.id 
            WHERE i.rep_route_id = :rid AND i.status != 'Voided' 
            GROUP BY ii.description 
            ORDER BY ii.description ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }
}