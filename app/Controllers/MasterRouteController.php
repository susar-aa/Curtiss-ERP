<?php
require_once __DIR__ . '/RepTrackingController.php';

class MasterRouteController extends RepTrackingController {
    
    protected function getUnifiedRoutes($page = 1, $limit = 20) {
        $db = new Database();
        
        $rep = isset($_GET['rep']) ? trim($_GET['rep']) : '';
        $routeName = isset($_GET['route']) ? trim($_GET['route']) : '';
        $date = isset($_GET['date']) ? trim($_GET['date']) : '';
        $territory = isset($_GET['territory']) ? trim($_GET['territory']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $filterSql = "";
        $binds = [];
        
        if ($rep !== '') {
            $filterSql .= " AND CONCAT(COALESCE(e.first_name, u.username), ' ', COALESCE(e.last_name, '')) = :rep";
            $binds[':rep'] = $rep;
        }
        if ($routeName !== '') {
            $filterSql .= " AND r.route_name = :route_name";
            $binds[':route_name'] = $routeName;
        }
        if ($date !== '') {
            $filterSql .= " AND DATE(r.start_time) = :date";
            $binds[':date'] = $date;
        }
        if ($territory !== '') {
            $filterSql .= " AND (r.route_name = :territory OR rb.name = :territory)";
            $binds[':territory'] = $territory;
        }
        if ($search !== '') {
            $filterSql .= " AND (r.route_name LIKE :search OR CONCAT(COALESCE(e.first_name, u.username), ' ', COALESCE(e.last_name, '')) LIKE :search OR r.id = :search_id)";
            $binds[':search'] = '%' . $search . '%';
            $binds[':search_id'] = intval(str_replace('#RT-', '', str_replace('#rt-', '', $search)));
        }

        $countQuery = "
            SELECT COUNT(DISTINCT r.id) as cnt
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            LEFT JOIN route_bindings rb ON r.route_binding_id = rb.id
            WHERE 1=1
              $filterSql
        ";
        $db->query($countQuery);
        foreach ($binds as $key => $val) {
            $db->bind($key, $val);
        }
        $countRow = $db->single();
        $total = $countRow ? intval($countRow->cnt) : 0;
        $totalPages = max(1, ceil($total / $limit));
        
        if ($totalPages > 0) {
            $page = max(1, min($page, $totalPages));
        } else {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        $selectQuery = "
            SELECT r.*, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                COALESCE(inv.bill_count, 0) as bill_count,
                COALESCE(inv.total_sales, 0.00) as total_sales,
                COALESCE(pc.unfinalized_count, 0) as unfinalized_count,
                COALESCE(inv.customer_count, 0) as customer_count,
                rb.name as binding_name,
                d.id as delivery_id, d.vehicle_number, d.driver_name, d.partner_name, d.status as delivery_status,
                COALESCE(dpi.total_items, 0) as total_items,
                COALESCE(dpi.picked_items, 0) as picked_items,
                COALESCE(dpi.verified_items, 0) as verified_items
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            LEFT JOIN route_bindings rb ON r.route_binding_id = rb.id
            LEFT JOIN (
                SELECT rep_route_id, 
                       COUNT(*) as bill_count,
                       SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_sales,
                       COUNT(DISTINCT customer_id) as customer_count
                FROM invoices 
                WHERE status != 'Voided' AND rep_route_id IS NOT NULL
                GROUP BY rep_route_id
            ) inv ON inv.rep_route_id = r.id
            LEFT JOIN (
                SELECT route_id, COUNT(*) as unfinalized_count
                FROM pending_collections 
                WHERE status = 'Pending' AND route_id IS NOT NULL
                GROUP BY route_id
            ) pc ON pc.route_id = r.id
            LEFT JOIN (
                SELECT route_id, MAX(delivery_id) as latest_delivery_id
                FROM (
                    SELECT rep_route_id as route_id, id as delivery_id FROM deliveries WHERE rep_route_id IS NOT NULL
                    UNION ALL
                    SELECT secondary_rep_route_id as route_id, id as delivery_id FROM deliveries WHERE secondary_rep_route_id IS NOT NULL
                ) as union_deliveries
                GROUP BY route_id
            ) latest_del ON latest_del.route_id = r.id
            LEFT JOIN deliveries d ON d.id = latest_del.latest_delivery_id
            LEFT JOIN (
                SELECT delivery_id,
                       COUNT(*) as total_items,
                       SUM(CASE WHEN is_picked = 1 THEN 1 ELSE 0 END) as picked_items,
                       SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_items
                FROM delivery_picking_items
                GROUP BY delivery_id
            ) dpi ON dpi.delivery_id = d.id
            WHERE 1=1
              $filterSql
            ORDER BY r.start_time DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $db->query($selectQuery);
        foreach ($binds as $key => $val) {
            $db->bind($key, $val);
        }
        $db->bind(':limit', $limit);
        $db->bind(':offset', $offset);
        $rawRoutes = $db->resultSet() ?: [];
        
        $grouped = [];
        $unbound = [];
        
        foreach ($rawRoutes as $route) {
            if ($route->route_binding_id) {
                $grouped[$route->route_binding_id][] = $route;
            } else {
                $unbound[] = $route;
            }
        }
        
        $finalRoutes = [];
        foreach ($unbound as $route) {
            $route->is_bound_group = false;
            $finalRoutes[] = $route;
        }
        
        foreach ($grouped as $bindingId => $routesList) {
            usort($routesList, function($a, $b) { return $a->id - $b->id; });
            $rep = $routesList[0];
            
            $merged = clone $rep;
            $merged->route_name = $rep->binding_name;
            $merged->bill_count = 0;
            $merged->total_sales = 0.0;
            $merged->unfinalized_count = 0;
            $merged->is_bound_group = true;
            
            $names = [];
            foreach ($routesList as $r) {
                $merged->bill_count += intval($r->bill_count);
                $merged->total_sales += floatval($r->total_sales);
                $merged->unfinalized_count += intval($r->unfinalized_count);
                $names[] = $r->route_name;
            }
            
            $merged->constituent_routes_info = implode(' & ', $names);
            $finalRoutes[] = $merged;
        }
        
        usort($finalRoutes, function($a, $b) {
            return strtotime($b->start_time) - strtotime($a->start_time);
        });
        
        return [
            'routes' => $finalRoutes,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'limit' => $limit
            ]
        ];
    }
}
