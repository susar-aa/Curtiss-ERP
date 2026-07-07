<?php
require_once dirname(__DIR__) . '/Services/RepBindingService.php';
require_once dirname(__DIR__) . '/Services/RepVarianceService.php';
require_once dirname(__DIR__) . '/Services/RepRouteService.php';

class RepTrackingController extends Controller {
    private $trackingModel;
    private $deliveryModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { 
            header('Location: ' . APP_URL . '/auth/login'); 
            exit; 
        }
        $this->generateCsrfToken();
        $this->trackingModel = $this->model('RepTracking');
        $this->deliveryModel = $this->model('Delivery');
    }

    protected function validateCsrf() {
        if (!parent::validateCsrf()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            if ($isAjax) {
                header('HTTP/1.1 403 Forbidden');
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'success' => false, 'message' => 'CSRF token validation failed.']);
                exit;
            } else {
                http_response_code(403);
                die("CSRF validation failed.");
            }
        }
        return true;
    }

    private function resolveDrivers($allEmployees) {
        $drivers = array_filter($allEmployees, function($emp) {
            return strtolower($emp->job_title) === 'driver' && $emp->status === 'Active';
        });

        $db = new Database();
        $db->query("SELECT id, username, email, employee_id FROM users WHERE LOWER(role) = 'driver' AND (status = 'Active' OR status IS NULL)");
        $driverUsers = $db->resultSet() ?: [];

        foreach ($driverUsers as $du) {
            $alreadyExists = false;
            foreach ($drivers as $d) {
                if ((!empty($du->employee_id) && $d->id == $du->employee_id) || 
                    (!empty($du->email) && strtolower($d->email) === strtolower($du->email))) {
                    $alreadyExists = true;
                    break;
                }
            }
            if (!$alreadyExists) {
                $virtualDriver = new stdClass();
                $virtualDriver->id = null;
                $virtualDriver->employee_id = $du->employee_id;
                $virtualDriver->username = $du->username;
                
                $parts = explode('.', str_replace('@', '.', $du->username));
                $virtualDriver->first_name = ucfirst($parts[0]);
                $virtualDriver->last_name = isset($parts[1]) ? ucfirst($parts[1]) : '';
                $virtualDriver->email = $du->email;
                $virtualDriver->job_title = 'Driver';
                $virtualDriver->status = 'Active';
                
                $drivers[] = $virtualDriver;
            }
        }
        return $drivers;
    }

    public function index() {
        $vehicleModel = $this->model('Vehicle');
        $employeeModel = $this->model('Employee');

        $vehicles = $vehicleModel->getAllVehicles();
        $allEmployees = $employeeModel->getAllEmployees();

        $drivers = $this->resolveDrivers($allEmployees);

        $db = new Database();
        $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600'");
        $parent = $db->single();
        $parentId = $parent ? $parent->id : 0;
        
        $db->query("SELECT * FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $db->bind(':pid', $parentId);
        $bankAccounts = $db->resultSet() ?: [];

        $db->query("SELECT id, account_code, account_name FROM chart_of_accounts ORDER BY account_code ASC");
        $allAccounts = $db->resultSet() ?: [];

        // Fetch active reps (users with role = 'rep')
        $db->query("SELECT u.id, u.username, e.first_name, e.last_name 
                    FROM users u 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE u.role = 'rep' AND (u.status IS NULL OR u.status = 'Active') 
                    ORDER BY e.first_name ASC, u.username ASC");
        $repsList = $db->resultSet() ?: [];

        // Fetch active MCA areas (territories)
        $db->query("SELECT id, name FROM mca_areas WHERE status = 'active' OR status IS NULL ORDER BY name ASC");
        $mcaAreas = $db->resultSet() ?: [];

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $routesData = $this->getUnifiedRoutes($page, $limit);

        // AJAX response for pagination and search
        if (isset($_GET['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json');
            ob_start();
            $routes = $routesData['routes'];
            $isHistory = false;
            include dirname(__DIR__) . '/Views/rep-tracking/_route_list_items.php';
            $routesHtml = ob_get_clean();

            ob_start();
            $pagination = $routesData['pagination'];
            include dirname(__DIR__) . '/Views/rep-tracking/_pagination.php';
            $paginationHtml = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'routes_html' => $routesHtml,
                'pagination_html' => $paginationHtml
            ]);
            exit;
        }

        $data = [
            'title' => 'Master Route Control Panel',
            'content_view' => 'rep-tracking/index',
            'routes' => $routesData['routes'],
            'pagination' => $routesData['pagination'],
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'employees' => $allEmployees,
            'bank_accounts' => $bankAccounts,
            'all_accounts' => $allAccounts,
            'reps' => $repsList,
            'mca_areas' => $mcaAreas
        ];
        
        $this->view('layouts/main', $data);
    }

    public function history() {
        $vehicleModel = $this->model('Vehicle');
        $employeeModel = $this->model('Employee');

        $vehicles = $vehicleModel->getAllVehicles();
        $allEmployees = $employeeModel->getAllEmployees();

        $drivers = $this->resolveDrivers($allEmployees);

        $db = new Database();
        $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600'");
        $parent = $db->single();
        $parentId = $parent ? $parent->id : 0;
        
        $db->query("SELECT * FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $db->bind(':pid', $parentId);
        $bankAccounts = $db->resultSet() ?: [];

        $db->query("SELECT id, account_code, account_name FROM chart_of_accounts ORDER BY account_code ASC");
        $allAccounts = $db->resultSet() ?: [];

        // Fetch active reps (users with role = 'rep')
        $db->query("SELECT u.id, u.username, e.first_name, e.last_name 
                    FROM users u 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE u.role = 'rep' AND (u.status IS NULL OR u.status = 'Active') 
                    ORDER BY e.first_name ASC, u.username ASC");
        $repsList = $db->resultSet() ?: [];

        // Fetch active MCA areas (territories)
        $db->query("SELECT id, name FROM mca_areas WHERE status = 'active' OR status IS NULL ORDER BY name ASC");
        $mcaAreas = $db->resultSet() ?: [];

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $routesData = $this->getCompletedRoutes($page, $limit);

        // AJAX response for pagination and search
        if (isset($_GET['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json');
            ob_start();
            $routes = $routesData['routes'];
            $isHistory = true;
            include dirname(__DIR__) . '/Views/rep-tracking/_route_list_items.php';
            $routesHtml = ob_get_clean();

            ob_start();
            $pagination = $routesData['pagination'];
            include dirname(__DIR__) . '/Views/rep-tracking/_pagination.php';
            $paginationHtml = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'routes_html' => $routesHtml,
                'pagination_html' => $paginationHtml
            ]);
            exit;
        }

        $data = [
            'title' => 'Route History',
            'content_view' => 'rep-tracking/index',
            'routes' => $routesData['routes'],
            'pagination' => $routesData['pagination'],
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'employees' => $allEmployees,
            'bank_accounts' => $bankAccounts,
            'all_accounts' => $allAccounts,
            'reps' => $repsList,
            'mca_areas' => $mcaAreas,
            'is_history' => true
        ];
        
        $this->view('layouts/main', $data);
    }

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
            $filterSql .= " AND TRIM(CONCAT(COALESCE(e.first_name, u.username), ' ', COALESCE(e.last_name, ''))) = :rep";
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
            WHERE r.status != 'Bound' AND r.status != 'Bound Into Route'
              AND r.status != 'Completed' AND r.status != 'Finalized'
              $filterSql
        ";
        $db->query($countQuery);
        foreach ($binds as $key => $val) {
            $db->bind($key, $val);
        }
        $countRow = $db->single();
        $total = $countRow ? intval($countRow->cnt) : 0;
        $totalPages = max(1, ceil($total / $limit));
        
        // Ensure page doesn't exceed totalPages
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
            WHERE r.status != 'Bound' AND r.status != 'Bound Into Route'
              AND r.status != 'Completed' AND r.status != 'Finalized'
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
        
        return [
            'routes' => $this->processUnifiedRoutes($rawRoutes),
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'limit' => $limit
            ]
        ];
    }

    private function getCompletedRoutes($page = 1, $limit = 20) {
        $db = new Database();
        
        $rep = isset($_GET['rep']) ? trim($_GET['rep']) : '';
        $routeName = isset($_GET['route']) ? trim($_GET['route']) : '';
        $date = isset($_GET['date']) ? trim($_GET['date']) : '';
        $territory = isset($_GET['territory']) ? trim($_GET['territory']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $filterSql = "";
        $binds = [];
        
        if ($rep !== '') {
            $filterSql .= " AND TRIM(CONCAT(COALESCE(e.first_name, u.username), ' ', COALESCE(e.last_name, ''))) = :rep";
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
            WHERE r.status != 'Bound' AND r.status != 'Bound Into Route'
              $filterSql
        ";
        $db->query($countQuery);
        foreach ($binds as $key => $val) {
            $db->bind($key, $val);
        }
        $countRow = $db->single();
        $total = $countRow ? intval($countRow->cnt) : 0;
        $totalPages = max(1, ceil($total / $limit));
        
        // Ensure page doesn't exceed totalPages
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
            WHERE r.status != 'Bound' AND r.status != 'Bound Into Route'
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
        
        return [
            'routes' => $this->processUnifiedRoutes($rawRoutes),
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'limit' => $limit
            ]
        ];
    }

    private function processUnifiedRoutes($rawRoutes) {
        $grouped = [];
        $unbound = [];
        
        foreach ($rawRoutes as $route) {
            if ($route->route_binding_id && $route->is_merged_route == 0) {
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
            $merged->customer_count = 0;
            $merged->is_bound_group = true;
            
            $names = [];
            foreach ($routesList as $r) {
                $merged->bill_count += intval($r->bill_count);
                $merged->total_sales += floatval($r->total_sales);
                $merged->unfinalized_count += intval($r->unfinalized_count);
                $merged->customer_count += intval($r->customer_count);
                $names[] = $r->route_name;
            }
            
            $merged->constituent_routes_info = implode(' & ', $names);
            $finalRoutes[] = $merged;
        }
        
        usort($finalRoutes, function($a, $b) {
            return strtotime($b->start_time) - strtotime($a->start_time);
        });
        
        return $finalRoutes;
    }



    public function api_get_route_path($routeId) {
        $path = $this->trackingModel->getRoutePath($routeId);
        header('Content-Type: application/json');
        if (!$path) {
            echo json_encode(['status' => 'error', 'message' => 'Route not found']);
            exit;
        }
        echo json_encode(['status' => 'success', 'path' => $path]);
        exit;
    }

    public function api_get_outstanding_bills($routeId) {
        $db = new Database();
        $routeIds = [$routeId];
        $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
        $db->bind(':rid', $routeId);
        $routeRow = $db->single();
        if ($routeRow && $routeRow->route_binding_id) {
            $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
            $db->bind(':bid', $routeRow->route_binding_id);
            $boundRoutes = $db->resultSet();
            foreach ($boundRoutes as $br) {
                $routeIds[] = intval($br->id);
            }
        }
        $routeIds = array_unique($routeIds);
        
        $mainAreaIds = [];
        foreach ($routeIds as $rid) {
            $db->query("
                SELECT m.main_area_id
                FROM mca_areas m
                JOIN rep_daily_routes r ON r.route_name = m.name
                WHERE r.id = :rid
                LIMIT 1
            ");
            $db->bind(':rid', $rid);
            $row = $db->single();
            if ($row && $row->main_area_id) {
                $mainAreaIds[] = intval($row->main_area_id);
            } else {
                $db->query("
                    SELECT DISTINCT m.main_area_id
                    FROM invoices i
                    JOIN customers c ON i.customer_id = c.id
                    JOIN mca_areas m ON c.mca_id = m.id
                    WHERE i.rep_route_id = :rid AND c.mca_id IS NOT NULL
                    LIMIT 1
                ");
                $db->bind(':rid', $rid);
                $rowFallback = $db->single();
                if ($rowFallback && $rowFallback->main_area_id) {
                    $mainAreaIds[] = intval($rowFallback->main_area_id);
                }
            }
        }
        
        if (empty($mainAreaIds)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'bills' => []]);
            exit;
        }
        
        $areaIdsStr = implode(',', array_unique($mainAreaIds));
        
        $db->query("
            SELECT DISTINCT c.id
            FROM customers c
            JOIN mca_areas m ON c.mca_id = m.id
            JOIN invoices i ON i.customer_id = c.id
            WHERE m.main_area_id IN ($areaIdsStr) AND i.status != 'Voided'
        ");
        $custs = $db->resultSet();
        foreach ($custs as $c) {
            $this->autoApplyPaymentsToInvoices($c->id);
        }
        
        $routeIdsStr = implode(',', array_map('intval', $routeIds));
        
        $db->query("
            SELECT c.id as customer_id, c.name as customer_name, m.name as mca_name,
                   (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                    FROM invoices WHERE customer_id = c.id AND status = 'Unpaid') as outstanding_amount
            FROM customers c
            JOIN mca_areas m ON c.mca_id = m.id
            WHERE m.main_area_id IN ($areaIdsStr)
            HAVING outstanding_amount > 0
            ORDER BY c.name ASC
        ");
        $outstandingCustomers = $db->resultSet();
        
        $customersWithBills = [];
        foreach ($outstandingCustomers as $cust) {
            $db->query("
                SELECT i.id, i.invoice_number, i.invoice_date, i.rep_route_id,
                       (SELECT route_name FROM rep_daily_routes WHERE id = i.rep_route_id) as route_name,
                       (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
                FROM invoices i
                WHERE i.customer_id = :cid AND i.status = 'Unpaid'
                ORDER BY i.invoice_date ASC
            ");
            $db->bind(':cid', $cust->customer_id);
            $bills = $db->resultSet() ?: [];
            
            if (!empty($bills)) {
                $customersWithBills[] = [
                    'customer_id' => $cust->customer_id,
                    'customer_name' => $cust->customer_name,
                    'mca_name' => $cust->mca_name,
                    'outstanding_amount' => $cust->outstanding_amount,
                    'bills' => $bills
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $customersWithBills]);
        exit;
    }

    private function autoApplyPaymentsToInvoices($customerId) {
        $db = new Database();
        
        // Sum active/finalized customer payments
        $db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE customer_id = :cid AND (status IS NULL OR status = 'Active')");
        $db->bind(':cid', $customerId);
        $rowPaid = $db->single();
        $totalPaid = $rowPaid ? floatval($rowPaid->total_paid) : 0.0;
        
        // Sum pending route collections (in transit/not yet finalized)
        $db->query("SELECT COALESCE(SUM(amount), 0) as total_pending FROM pending_collections WHERE customer_id = :cid AND status = 'Pending'");
        $db->bind(':cid', $customerId);
        $rowPending = $db->single();
        $totalPending = $rowPending ? floatval($rowPending->total_pending) : 0.0;
        
        $totalPaid += $totalPending;
        
        $db->query("
            SELECT id, 
                   (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total,
                   status
            FROM invoices
            WHERE customer_id = :cid AND status != 'Voided'
            ORDER BY invoice_date ASC, id ASC
        ");
        $db->bind(':cid', $customerId);
        $invoices = $db->resultSet();
        
        $remainingPaid = $totalPaid;
        foreach ($invoices as $inv) {
            $grandTotal = floatval($inv->true_grand_total);
            if ($remainingPaid >= $grandTotal - 0.01) {
                $newStatus = 'Paid';
                $remainingPaid -= $grandTotal;
            } else {
                $newStatus = 'Unpaid';
                $remainingPaid = 0;
            }
            if ($inv->status !== $newStatus) {
                $db->query("UPDATE invoices SET status = :status WHERE id = :id");
                $db->bind(':status', $newStatus);
                $db->bind(':id', $inv->id);
                $db->execute();
            }
        }
    }

    public function api_get_route_variances($routeId) {
        try {
            $db = new Database();
            RepVarianceService::autoApplyRouteSubstitutions($db, $routeId, $_SESSION['user_id'] ?? 1);

            // Fetch route details first
            $db->query("SELECT status, route_name, route_binding_id FROM rep_daily_routes WHERE id = :id");
            $db->bind(':id', $routeId);
            $route = $db->single();

            $db->query("
                SELECT d.id as id, d.vehicle_number, d.driver_name, d.status
                FROM deliveries d
                WHERE d.rep_route_id = :rid OR d.secondary_rep_route_id = :rid
            ");
            $db->bind(':rid', $routeId);
            $deliveries = $db->resultSet() ?: [];

            // If no delivery exists but route status is 'Loading', auto-create it
            if (empty($deliveries) && $route && $route->status === 'Loading') {
                require_once dirname(__DIR__) . '/Services/RepRouteService.php';
                RepRouteService::ensureDeliveryAndPickingPopulated($db, $routeId);
                
                // Re-fetch deliveries
                $db->query("
                    SELECT d.id as id, d.vehicle_number, d.driver_name, d.status
                    FROM deliveries d
                    WHERE d.rep_route_id = :rid OR d.secondary_rep_route_id = :rid
                ");
                    $db->bind(':rid', $routeId);
                $deliveries = $db->resultSet() ?: [];
            }

            $results = [];
            foreach ($deliveries as $del) {
                // Ensure picking items are populated
                require_once dirname(__DIR__) . '/Services/RepRouteService.php';
                RepRouteService::ensurePickingItemsPopulated($db, $del->id);

                $db->query("
                    SELECT dpi.item_id, dpi.variation_option_id, dpi.item_name, dpi.required_qty, dpi.loaded_qty as pre_loaded_qty, 
                           dpi.final_loaded_qty, dpi.variance, dpi.is_verified, dpi.verified_at, u.username as verifier_name
                    FROM delivery_picking_items dpi
                    LEFT JOIN users u ON dpi.verified_by = u.id
                    WHERE dpi.delivery_id = :did
                ");
                $db->bind(':did', $del->id);
                $items = $db->resultSet() ?: [];
                
                $shortages = 0;
                $overages = 0;
                $totalItems = count($items);
                $verifiedItems = 0;
                
                // Fetch substitutions for this delivery
                $db->query("
                    SELECT ps.*, 
                           COALESCE(oi.name, 'Deleted Product') as original_item_name, 
                           COALESCE(ri.name, 'Deleted Product') as replacement_item_name
                    FROM product_substitutions ps
                    LEFT JOIN items oi ON ps.original_item_id = oi.id
                    LEFT JOIN items ri ON ps.replacement_item_id = ri.id
                    WHERE ps.delivery_id = :delivery_id
                ");
                $db->bind(':delivery_id', $del->id);
                $deliverySubs = $db->resultSet() ?: [];

                foreach ($items as $item) {
                    $item->required_qty = floatval($item->required_qty);
                    $item->pre_loaded_qty = floatval($item->pre_loaded_qty);
                    $item->final_loaded_qty = $item->final_loaded_qty !== null ? floatval($item->final_loaded_qty) : null;
                    $item->variance = floatval($item->variance);
                    $item->is_verified = intval($item->is_verified);
                    
                    if ($item->is_verified) {
                        $verifiedItems++;
                    }
                    if ($item->variance < 0) {
                        $shortages += abs($item->variance);
                    } elseif ($item->variance > 0) {
                        $overages += $item->variance;
                    }

                    // Attach substitution details
                    $item->replaced_by_name = null;
                    $item->replacement_qty = null;
                    $item->replaces_name = null;

                    foreach ($deliverySubs as $ds) {
                        if (intval($ds->original_item_id) === intval($item->item_id)) {
                            $item->replaced_by_name = $ds->replacement_item_name;
                            $item->replacement_qty = floatval($ds->loaded_qty);
                        }
                        if (intval($ds->replacement_item_id) === intval($item->item_id) && floatval($item->required_qty) === 0.0) {
                            $item->replaces_name = $ds->original_item_name;
                        }
                    }
                }
                
                $results[] = [
                    'delivery_id' => $del->id,
                    'vehicle_number' => $del->vehicle_number,
                    'driver_name' => $del->driver_name,
                    'status' => $del->status,
                    'total_items' => $totalItems,
                    'verified_items' => $verifiedItems,
                    'shortages' => $shortages,
                    'overages' => $overages,
                    'items' => $items
                ];
            }
            
            // Fetch substitutions
            $db->query("
                SELECT ps.*, 
                       COALESCE(oi.name, 'Deleted Product') as original_item_name, 
                       COALESCE(ri.name, 'Deleted Product') as replacement_item_name,
                       u.username as creator_name
                FROM product_substitutions ps
                LEFT JOIN items oi ON ps.original_item_id = oi.id
                LEFT JOIN items ri ON ps.replacement_item_id = ri.id
                LEFT JOIN users u ON ps.user_id = u.id
                WHERE ps.route_id = :rid
                ORDER BY ps.created_at DESC
            ");
            $db->bind(':rid', $routeId);
            $substitutions = $db->resultSet() ?: [];

            $loadingItems = $this->trackingModel->getRouteLoadingItems($routeId) ?: [];
            foreach ($loadingItems as $li) {
                $li->total_qty = floatval($li->total_qty);
                $li->unit_price = floatval($li->unit_price);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'deliveries' => $results,
                'substitutions' => $substitutions,
                'loading_items' => $loadingItems
            ]);
            exit;
        } catch (Throwable $e) {
            error_log("api_get_route_variances failed for Route ID {$routeId}: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function api_get_delivery_details($id) {
        $delivery = null;
        $routeId = isset($_GET['route_id']) ? intval($_GET['route_id']) : 0;
        
        if ($id > 0) {
            $delivery = $this->deliveryModel->getDeliveryById($id);
        }
        
        if (!$delivery && $routeId > 0) {
            $db = new Database();
            $db->query("SELECT id FROM deliveries WHERE rep_route_id = :rid ORDER BY id DESC LIMIT 1");
            $db->bind(':rid', $routeId);
            $delRow = $db->single();
            if ($delRow) {
                $delivery = $this->deliveryModel->getDeliveryById($delRow->id);
            }
        }
        
        if (!$delivery && $routeId > 0) {
            $delivery = $this->deliveryModel->getVirtualDeliveryByRouteId($routeId);
        }
        
        if (!$delivery) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Delivery not found.']);
            exit;
        }

        $invoices = $this->deliveryModel->getDeliveryInvoices($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null);
        $creditInvoices = $this->deliveryModel->getDeliveryCreditInvoices($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null);
        $balancing = $this->deliveryModel->getDeliveryBalancingData($delivery->id, $delivery->rep_route_id);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'delivery' => $delivery,
            'invoices' => $invoices,
            'credit_invoices' => $creditInvoices,
            'balancing' => $balancing
        ]);
        exit;
    }

    public function arrange() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
            exit;
        }
        $this->validateCsrf();

        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true);
        if (!$postData) {
            $postData = $_POST;
        }

        $deliveryData = [
            'rep_route_id' => intval($postData['rep_route_id'] ?? 0),
            'secondary_rep_route_id' => !empty($postData['secondary_rep_route_id']) ? intval($postData['secondary_rep_route_id']) : null,
            'delivery_date' => trim($postData['delivery_date'] ?? ''),
            'vehicle_number' => trim($postData['vehicle_number'] ?? ''),
            'driver_name' => trim($postData['driver_name'] ?? ''),
            'partner_name' => trim($postData['partner_name'] ?? ''),
            'selected_credit_invoices' => !empty($postData['selected_credit_invoices']) ? json_encode($postData['selected_credit_invoices']) : null
        ];

        if (empty($deliveryData['rep_route_id']) || empty($deliveryData['delivery_date'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'All mandatory fields (Route, Date) are required.']);
            exit;
        }

        $deliveryId = $this->deliveryModel->createDelivery($deliveryData);

        header('Content-Type: application/json');
        if ($deliveryId) {
            // Route status remains in Adjustments stage until explicitly advanced by operator
            $this->logRouteActivity('Arrange Delivery', 'RepTracking', "Created delivery arrangement ID: {$deliveryId}", $deliveryData['rep_route_id']);

            echo json_encode(['status' => 'success', 'message' => 'Delivery arranged successfully!', 'delivery_id' => $deliveryId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to arrange delivery. Database transaction error.']);
        }
        exit;
    }

    public function finalize() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
            exit;
        }
        $this->validateCsrf();

        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true);
        if (!$postData) {
            $postData = $_POST;
        }

        $deliveryId = intval($postData['delivery_id'] ?? 0);
        if ($deliveryId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Delivery ID is required.']);
            exit;
        }

        try {
            $db = new Database();
            // Get route ID associated with this delivery
            $db->query("SELECT rep_route_id FROM deliveries WHERE id = :did");
            $db->bind(':did', $deliveryId);
            $deliveryRow = $db->single();
            $repRouteId = $deliveryRow ? intval($deliveryRow->rep_route_id) : 0;
            
            if ($repRouteId > 0) {
                // Get the route's binding ID to check bound routes as well
                $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid");
                $db->bind(':rid', $repRouteId);
                $routeRow = $db->single();
                $bindingId = $routeRow ? $routeRow->route_binding_id : 0;

                // Check for unverified collections
                $db->query("SELECT COUNT(*) as pending_count FROM pending_collections WHERE (route_id = :rid OR route_id IN (SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid AND route_binding_id IS NOT NULL)) AND is_verified = 0");
                $db->bind(':rid', $repRouteId);
                $db->bind(':bid', $bindingId);
                $pendingRow = $db->single();
                
                if ($pendingRow && intval($pendingRow->pending_count) > 0) {
                    throw new Exception("Cannot finalize dispatch: There are outstanding payment collections that have not been verified and approved by the accounts department.");
                }
            }

            $adminUserId = $_SESSION['user_id'];
            $selectedPaymentIds = isset($postData['selected_payment_ids']) ? array_map('intval', $postData['selected_payment_ids']) : [];
            $selectedInvoiceIds = isset($postData['selected_invoice_ids']) ? array_map('intval', $postData['selected_invoice_ids']) : [];
            $debitAccounts = $postData['debit_accounts'] ?? [];
            $creditAccounts = $postData['credit_accounts'] ?? [];
            $returnedItems = $postData['returned_items'] ?? [];
            
            $vehicleNumber = !empty($postData['vehicle_number']) ? trim($postData['vehicle_number']) : null;
            $driverName = !empty($postData['driver_name']) ? trim($postData['driver_name']) : null;
            $partnerName = !empty($postData['partner_name']) ? trim($postData['partner_name']) : null;

            if (empty($vehicleNumber) || empty($driverName)) {
                throw new Exception("Vehicle number and Driver name are required to finalize dispatch.");
            }

            $this->deliveryModel->finalizeDelivery(
                $deliveryId, 
                $adminUserId, 
                $selectedPaymentIds, 
                $selectedInvoiceIds, 
                $debitAccounts, 
                $creditAccounts,
                $returnedItems,
                $vehicleNumber,
                $driverName,
                $partnerName
            );

            // Update route status to Completed
            $delivery = $this->deliveryModel->getDeliveryById($deliveryId);
            if ($delivery) {
                $db = new Database();
                $db->query("UPDATE rep_daily_routes SET status = 'Completed' WHERE id = :id OR route_binding_id = (SELECT route_binding_id FROM rep_daily_routes WHERE id = :id2)");
                $db->bind(':id', $delivery->rep_route_id);
                $db->bind(':id2', $delivery->rep_route_id);
                $db->execute();
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Delivery route finalized successfully! Route marked as Completed.']);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function api_update_route_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
            exit;
        }
        $this->validateCsrf();

        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true);
        if (!$postData) {
            $postData = $_POST;
        }

        $routeId = intval($postData['route_id'] ?? 0);
        $targetStatus = trim($postData['status'] ?? '');

        $allowedStatuses = [
            'Active', 'Pending GL', 'Adjustments', 'Loading', 
            'Variance Adjustment', 'Finalizing', 'Delivery Arranged', 'Completed'
        ];

        header('Content-Type: application/json');
        if (!$routeId || !in_array($targetStatus, $allowedStatuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters: ID=' . $routeId . ', Status=' . $targetStatus]);
            exit;
        }

        $db = new Database();
        $db->query("SELECT status, route_name, route_binding_id FROM rep_daily_routes WHERE id = :id");
        $db->bind(':id', $routeId);
        $oldRoute = $db->single();
        $oldStatus = $oldRoute ? $oldRoute->status : 'Unknown';
        $routeName = $oldRoute ? $oldRoute->route_name : '';

        // Guard status transitions if there are unverified collections
        $statusPriority = [
            'Active' => 0,
            'Pending GL' => 1,
            'Adjustments' => 2,
            'Loading' => 3,
            'Variance Adjustment' => 4,
            'Finalizing' => 5,
            'Delivery Arranged' => 5,
            'Completed' => 6
        ];
        $targetPriority = $statusPriority[$targetStatus] ?? 0;
        if ($targetStatus === 'Completed') { // Only guard transition to Completed
            $db->query("SELECT id, UNIX_TIMESTAMP(created_at) as created_ts FROM pending_collections 
                        WHERE (route_id = :rid OR route_id IN (SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid AND route_binding_id IS NOT NULL)) 
                        AND (status = 'Pending' OR is_verified = 0)");
            $db->bind(':rid', $routeId);
            $db->bind(':bid', $oldRoute ? $oldRoute->route_binding_id : 0);
            $pendingCollections = $db->resultSet() ?: [];

            if (!empty($pendingCollections)) {
                $hasTimeout = false;
                $timeoutLimit = 86400; // 24 hours in seconds
                $now = time();
                foreach ($pendingCollections as $pc) {
                    $createdTs = intval($pc->created_ts ?? 0);
                    if ($createdTs > 0 && ($now - $createdTs) > $timeoutLimit) {
                        $hasTimeout = true;
                    }
                }

                $userRole = strtolower($_SESSION['role'] ?? '');
                $isAuthorizedToOverride = in_array($userRole, ['admin', 'accountant', 'manager']);
                $adminOverride = !empty($postData['admin_override']);

                if ($isAuthorizedToOverride && $adminOverride) {
                    // Log the bypass action in audit_logs
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                    $bypassReason = $hasTimeout ? "Escalation/Timeout Bypass (>24h)" : "Manual Administrative Override";
                    $db->query("INSERT INTO audit_logs (user_id, action, module, description, reference_id, ip_address) 
                                VALUES (:uid, 'ADMIN_OVERRIDE_COLLECTIONS', 'Logistics', :desc, :ref, :ip)");
                    $db->bind(':uid', $_SESSION['user_id'] ?? null);
                    $db->bind(':desc', "$bypassReason bypassed unverified collections check for route ID: " . $routeId . " moving to status: " . $targetStatus);
                    $db->bind(':ref', $routeId);
                    $db->bind(':ip', $ip);
                    $db->execute();
                } else {
                    $msg = "Cannot advance status: There are outstanding payment collections that have not been verified and finalized by the accounts department.";
                    if ($hasTimeout) {
                        $msg .= " Note: Some unverified collections have been pending for over 24 hours (Escalated status).";
                    }
                    if ($isAuthorizedToOverride) {
                        $msg .= " As an authorized user (" . ucfirst($userRole) . "), you can override this guard to proceed.";
                    } else {
                        $msg .= " Please contact an Administrator, Accountant, or Manager to override this guard.";
                    }
                    echo json_encode([
                        'status' => 'error',
                        'message' => $msg,
                        'show_override' => $isAuthorizedToOverride,
                        'is_escalated' => $hasTimeout
                    ]);
                    exit;
                }
            }
        }

        // Automatically create and expose loading task if moving to Loading status
        if ($targetStatus === 'Loading') {
            require_once dirname(__DIR__) . '/Services/RepRouteService.php';
            RepRouteService::ensureDeliveryAndPickingPopulated($db, $routeId);
        }

        $db->query("UPDATE rep_daily_routes SET status = :status WHERE id = :id");
        $db->bind(':status', $targetStatus);
        $db->bind(':id', $routeId);
        $db->execute();

        if ($oldRoute && $oldRoute->route_binding_id) {
            $db->query("UPDATE rep_daily_routes SET status = :status WHERE route_binding_id = :bid");
            $db->bind(':status', $targetStatus);
            $db->bind(':bid', $oldRoute->route_binding_id);
            $db->execute();
        }

        // Keep deliveries in sync
        $delStatus = 'Arranged';
        if ($targetStatus === 'Completed') {
            // Check if there are any pending collections for this route
            $db->query("SELECT COUNT(*) as pending_count FROM pending_collections WHERE (route_id = :rid OR route_id IN (SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid AND route_binding_id IS NOT NULL)) AND (status = 'Pending' OR is_verified = 0)");
            $db->bind(':rid', $routeId);
            $db->bind(':bid', $oldRoute ? $oldRoute->route_binding_id : 0);
            $pendingRow = $db->single();
            if ($pendingRow && intval($pendingRow->pending_count) > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot complete route: There are outstanding payment collections that have not been verified and finalized by the accounts department.']);
                exit;
            }
            // Decouple delivery status from route completion: do not force delivery to 'Completed' here.
            $delStatus = null;
        }
        if ($delStatus !== null) {
            $db->query("UPDATE deliveries SET status = :status WHERE (rep_route_id = :rid OR secondary_rep_route_id = :rid) AND status NOT IN ('Completed', 'Finalized')");
            $db->bind(':status', $delStatus);
            $db->bind(':rid', $routeId);
            $db->execute();
        }

        $this->logRouteActivity('Route Status Update', 'RepTracking', "Moved route '{$routeName}' status from '{$oldStatus}' to '{$targetStatus}'", $routeId);

        echo json_encode(['status' => 'success', 'message' => 'Route status updated successfully to ' . $targetStatus]);
        exit;
    }

    private function logRouteActivity($action, $module, $desc, $refId = null) {
        try {
            $db = new Database();
            $db->query("INSERT INTO audit_logs (user_id, action, module, description, reference_id, ip_address) 
                        VALUES (:uid, :action, :module, :desc, :ref, :ip)");
            $db->bind(':uid', $_SESSION['user_id'] ?? null);
            $db->bind(':action', $action);
            $db->bind(':module', $module);
            $db->bind(':desc', $desc);
            $db->bind(':ref', $refId);
            $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $db->execute();
        } catch (Exception $e) {
            error_log("Audit logging failed in logRouteActivity: " . $e->getMessage());
        }
    }

    public function balancing_report($id) {
        $delivery = $this->deliveryModel->getDeliveryById($id);
        if (!$delivery) {
            die("<div style='padding:20px; font-family:sans-serif; color:red;'><h3>Delivery Not Found</h3></div>");
        }
        $balancing = $this->deliveryModel->getDeliveryBalancingData($id);
        $data = [
            'title' => 'Delivery Balancing & Settlement Report',
            'delivery' => $delivery,
            'balancing' => $balancing
        ];
        $this->view('deliveries/balancing_report', $data);
    }

    public function spreadsheet($id) {
        $delivery = $this->deliveryModel->getDeliveryById($id);
        if (!$delivery) {
            die("<div style='padding:20px; font-family:sans-serif; color:red;'><h3>Delivery Not Found</h3></div>");
        }
        $data = [
            'title' => 'Delivery Loading Spreadsheet',
            'delivery' => $delivery,
            'items' => $this->deliveryModel->getDeliverySpreadsheetData($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null),
            'bills' => $this->deliveryModel->getDeliveryInvoices($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null)
        ];
        $this->view('deliveries/spreadsheet', $data);
    }

    public function export_csv($id) {
        $delivery = $this->deliveryModel->getDeliveryById($id);
        if (!$delivery) {
            die("Delivery not found.");
        }
        $items = $this->deliveryModel->getDeliverySpreadsheetData($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null);
        $filename = "Loading_Sheet_" . str_replace(" ", "_", $delivery->route_name) . "_" . $delivery->delivery_date . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');
        fputcsv($output, ['DELIVERY LOADING SHEET SUMMARY']);
        fputcsv($output, ['Route Name:', $delivery->route_name, 'Delivery Date:', $delivery->delivery_date]);
        fputcsv($output, ['Vehicle Number:', $delivery->vehicle_number, 'Representative:', $delivery->first_name . ' ' . $delivery->last_name]);
        fputcsv($output, ['Driver Name:', $delivery->driver_name]);
        fputcsv($output, ['']);
        fputcsv($output, ['Product / Item Description', 'Total Quantity to Load']);
        foreach ($items as $item) {
            fputcsv($output, [$item->item_name, $item->total_qty]);
        }
        fclose($output);
        exit;
    }

    public function print_loading($routeId) {
        $type = $_GET['type'] ?? 'final';
        
        // Always try to fetch final loading items first (from delivery verification)
        $items = $this->trackingModel->getRouteFinalLoadingItems($routeId);
        
        // If empty (e.g. delivery is not yet arranged), fall back to invoice-based loading items
        if (empty($items)) {
            $rawItems = $this->trackingModel->getRouteLoadingItems($routeId);
            $items = [];
            foreach ($rawItems as $ri) {
                $item = new stdClass();
                $item->item_id = null;
                $item->item_name = $ri->item_name;
                $item->required_qty = floatval($ri->total_qty);
                $item->pre_loaded_qty = floatval($ri->total_qty);
                $item->final_loaded_qty = floatval($ri->total_qty);
                $item->variance = 0.0;
                $item->category_name = $ri->category_name;
                $item->unit_price = floatval($ri->unit_price);
                $items[] = $item;
            }
        } else {
            foreach ($items as $item) {
                $item->required_qty = floatval($item->required_qty);
                $item->pre_loaded_qty = floatval($item->pre_loaded_qty);
                $item->final_loaded_qty = $item->final_loaded_qty !== null ? floatval($item->final_loaded_qty) : floatval($item->required_qty);
                $item->variance = floatval($item->variance);
                $item->unit_price = floatval($item->unit_price);
            }
        }

        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $db = new Database();
        $db->query("SELECT * FROM deliveries WHERE rep_route_id = :rid ORDER BY id DESC LIMIT 1");
        $db->bind(':rid', $routeId);
        $delivery = $db->single();

        $data = [
            'type' => $type === 'summary' ? 'summary' : 'loading',
            'route' => $this->trackingModel->getRouteById($routeId),
            'items' => $items,
            'bills' => $this->trackingModel->getRouteBills($routeId),
            'credit_bills' => $this->deliveryModel->getDeliveryCreditInvoices($routeId),
            'delivery' => $delivery,
            'company' => $company
        ];
        $this->view('rep-tracking/print_loading', $data);
    }

    public function print_route_invoices($routeId) {
        $route = $this->trackingModel->getRouteById($routeId);
        if (!$route) {
            die("Route not found.");
        }
        
        $bills = $this->trackingModel->getRouteBills($routeId);
        
        $invoiceModel = $this->model('Invoice');
        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();
        
        $invoicesData = [];
        foreach ($bills as $bill) {
            if ($bill->status === 'Voided') continue;
            
            // Get full invoice details (includes customer address, phone, tax details)
            $fullInvoice = $invoiceModel->getInvoiceById($bill->id);
            if (!$fullInvoice) continue;
            
            $invoicePaid = 0;
            try {
                $db = new Database();
                $db->query("SELECT COALESCE(SUM(amount), 0) as paid FROM customer_payments WHERE invoice_id = :id");
                $db->bind(':id', $bill->id);
                $row = $db->single();
                if ($row) {
                    $invoicePaid = floatval($row->paid);
                }
            } catch (Exception $e) {
                $invoicePaid = 0;
            }
            
            // Calculate customer outstanding balance
            $db = new Database();
            $db->query("
                SELECT 
                    COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) as total_billed
                FROM invoices WHERE customer_id = :id AND status != 'Voided'
            ");
            $db->bind(':id', $fullInvoice->customer_id);
            $billed = $db->single()->total_billed ?? 0;
 
            $db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE customer_id = :id");
            $db->bind(':id', $fullInvoice->customer_id);
            $paid = $db->single()->total_paid ?? 0;
 
            $db->query("SELECT COALESCE(SUM(total_amount), 0) as total_credited FROM credit_notes WHERE customer_id = :id");
            $db->bind(':id', $fullInvoice->customer_id);
            $credited = $db->single()->total_credited ?? 0;
 
            $totalOutstanding = $billed - $paid - $credited;
            
            $invoicesData[] = [
                'invoice' => $fullInvoice,
                'items' => $invoiceModel->getInvoiceItems($bill->id),
                'invoice_paid' => $invoicePaid,
                'total_outstanding' => $totalOutstanding
            ];
        }
        
        $data = [
            'route' => $route,
            'company' => $company,
            'invoices' => $invoicesData
        ];
        
        $this->view('rep-tracking/print_route_invoices', $data);
    }

    public function print_route_invoices_summary($routeId) {
        $route = $this->trackingModel->getRouteById($routeId);
        if (!$route) {
            die("Route not found.");
        }
        
        $bills = $this->trackingModel->getRouteBills($routeId);
        
        $invoiceModel = $this->model('Invoice');
        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();
        
        $invoicesData = [];
        foreach ($bills as $bill) {
            if ($bill->status === 'Voided') continue;
            
            $fullInvoice = $invoiceModel->getInvoiceById($bill->id);
            if (!$fullInvoice) continue;
            
            $invoicesData[] = [
                'invoice' => $fullInvoice,
                'items' => $invoiceModel->getInvoiceItems($bill->id)
            ];
        }
        
        $data = [
            'route' => $route,
            'company' => $company,
            'invoices' => $invoicesData
        ];
        
        $this->view('rep-tracking/print_route_invoices_summary', $data);
    }

    public function print_return_stock($routeId) {
        $route = $this->trackingModel->getRouteById($routeId);
        if (!$route) {
            die("Route not found.");
        }
        
        $db = new Database();
        $db->query("SELECT * FROM deliveries WHERE rep_route_id = :rid ORDER BY id DESC LIMIT 1");
        $db->bind(':rid', $routeId);
        $delivery = $db->single();

        if (!$delivery) {
            die("Delivery not arranged for this route.");
        }

        $balancing = $this->deliveryModel->getDeliveryBalancingData($delivery->id, $routeId);
        $savedReturnStock = null;
        if (!empty($delivery->return_stock_json)) {
            $savedReturnStock = json_decode($delivery->return_stock_json, true);
        }

        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $data = [
            'route' => $route,
            'delivery' => $delivery,
            'balancing' => $balancing,
            'savedReturnStock' => $savedReturnStock,
            'company' => $company
        ];

        $this->view('rep-tracking/print_return_stock', $data);
    }

    public function api_get_route_collections($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        $collections = $this->trackingModel->getRouteCollections($routeId);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'collections' => $collections]);
        exit;
    }

    public function api_verify_collections() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $updates = $payload['updates'] ?? [];
        $userId = $_SESSION['user_id'] ?? 1;

        try {
            $db = new Database();
            $routeId = 0;

            foreach ($updates as $up) {
                $paymentId = intval($up['id']);
                $isVerified = isset($up['is_verified']) ? intval($up['is_verified']) : 0;
                $isFlagged = isset($up['is_flagged']) ? intval($up['is_flagged']) : 0;
                $adjAmount = isset($up['adjusted_amount']) && $up['adjusted_amount'] !== '' ? floatval($up['adjusted_amount']) : null;
                $notes = isset($up['verification_notes']) ? trim($up['verification_notes']) : null;
                $debitAccId = !empty($up['debit_account_id']) ? intval($up['debit_account_id']) : null;
                $creditAccId = !empty($up['credit_account_id']) ? intval($up['credit_account_id']) : null;

                if ($routeId === 0) {
                    $db->query("SELECT route_id FROM pending_collections WHERE id = :id");
                    $db->bind(':id', $paymentId);
                    $routeRow = $db->single();
                    $routeId = $routeRow ? intval($routeRow->route_id) : 0;
                }

                // Check current status
                $db->query("SELECT status FROM pending_collections WHERE id = :id");
                $db->bind(':id', $paymentId);
                $currentStatusRow = $db->single();
                $currentStatus = $currentStatusRow ? $currentStatusRow->status : 'Pending';

                $db->query("UPDATE pending_collections 
                            SET is_verified = :is_v, is_flagged = :is_f, adjusted_amount = :adj, verification_notes = :notes, 
                                verified_by = :vby, verified_at = NOW(), debit_account_id = :da, credit_account_id = :ca
                            WHERE id = :id");
                $db->bind(':is_v', $isVerified);
                $db->bind(':is_f', $isFlagged);
                $db->bind(':adj', $adjAmount);
                $db->bind(':notes', $notes);
                $db->bind(':vby', $userId);
                $db->bind(':da', $debitAccId);
                $db->bind(':ca', $creditAccId);
                $db->bind(':id', $paymentId);
                $db->execute();

            }

            // Transition route status based on pending collections count
            if ($routeId > 0) {
                // Get route binding ID and current status
                $db->query("SELECT route_binding_id, status FROM rep_daily_routes WHERE id = :id");
                $db->bind(':id', $routeId);
                $routeInfo = $db->single();
                $bindingId = $routeInfo ? $routeInfo->route_binding_id : null;
                $routeStatus = $routeInfo ? $routeInfo->status : '';

                if ($routeStatus === 'Pending GL' || $routeStatus === 'Adjustments') {
                    if ($bindingId) {
                        $db->query("SELECT COUNT(*) as pending_count FROM pending_collections 
                                    WHERE (route_id = :rid OR route_id IN (SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid)) 
                                    AND is_verified = 0");
                        $db->bind(':rid', $routeId);
                        $db->bind(':bid', $bindingId);
                    } else {
                        $db->query("SELECT COUNT(*) as pending_count FROM pending_collections WHERE route_id = :rid AND is_verified = 0");
                        $db->bind(':rid', $routeId);
                    }
                    $pendingCountRow = $db->single();
                    $pendingCount = $pendingCountRow ? intval($pendingCountRow->pending_count) : 0;

                    $newStatus = ($pendingCount > 0) ? 'Pending GL' : 'Adjustments';
                    if ($newStatus !== $routeStatus) {
                        $db->query("UPDATE rep_daily_routes SET status = :status WHERE id = :id");
                        $db->bind(':status', $newStatus);
                        $db->bind(':id', $routeId);
                        $db->execute();

                        if ($bindingId) {
                            $db->query("UPDATE rep_daily_routes SET status = :status WHERE route_binding_id = :bid");
                            $db->bind(':status', $newStatus);
                            $db->bind(':bid', $bindingId);
                            $db->execute();
                        }
                    }
                }
            }

            // Query final status
            $finalStatus = 'Pending GL';
            if ($routeId > 0) {
                $db->query("SELECT status FROM rep_daily_routes WHERE id = :id");
                $db->bind(':id', $routeId);
                $statusRow = $db->single();
                if ($statusRow) {
                    $finalStatus = $statusRow->status;
                }
            }

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'message' => 'Collections verified successfully!',
                'route_status' => $finalStatus
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_get_product_invoices() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        $routeId = intval($_GET['route_id'] ?? 0);
        $itemId = intval($_GET['item_id'] ?? 0);
        $varId = (isset($_GET['variation_option_id']) && $_GET['variation_option_id'] !== '' && $_GET['variation_option_id'] !== 'null') ? intval($_GET['variation_option_id']) : null;

        try {
            $db = new Database();
            $routeIds = [intval($routeId)];
            $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
            $db->bind(':rid', $routeId);
            $routeRow = $db->single();
            if ($routeRow && $routeRow->route_binding_id) {
                $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
                $db->bind(':bid', $routeRow->route_binding_id);
                $boundRoutes = $db->resultSet();
                foreach ($boundRoutes as $br) {
                    $routeIds[] = intval($br->id);
                }
            }
            $routeIds = array_unique($routeIds);
            $routeIdsStr = implode(',', array_map('intval', $routeIds));

            // Check if this item is a replacement in a product substitution on this route
            $db->query("SELECT original_item_id, status FROM product_substitutions WHERE route_id IN ($routeIdsStr) AND replacement_item_id = :item_id LIMIT 1");
            $db->bind(':item_id', $itemId);
            $subRow = $db->single();

            if ($subRow) {
                // Fetch replacement price
                $db->query("SELECT price FROM items WHERE id = :id");
                $db->bind(':id', $itemId);
                $replProductRow = $db->single();
                $replPrice = $replProductRow ? floatval($replProductRow->price) : 0.00;

                if ($subRow->status === 'Applied') {
                    // Substitution is already applied. Find invoices containing the replacement item itself.
                    $db->query("
                        SELECT i.id as invoice_id, i.invoice_number, c.name as customer_name, 
                               ii.quantity, ii.unit_price, ii.total as line_total,
                               ii.quantity as original_qty
                        FROM invoices i
                        JOIN customers c ON i.customer_id = c.id
                        JOIN invoice_items ii ON ii.invoice_id = i.id
                        WHERE i.rep_route_id IN ($routeIdsStr) AND ii.item_id = :item_id AND i.status != 'Voided'
                    ");
                    $db->bind(':item_id', $itemId);
                    $invoices = $db->resultSet() ?: [];
                } else {
                    // Substitution is not applied yet. Find invoices containing the original item.
                    $db->query("
                        SELECT i.id as invoice_id, i.invoice_number, c.name as customer_name, 
                               0.00 as quantity, :price as unit_price, 0.00 as line_total,
                               ii.quantity as original_qty
                        FROM invoices i
                        JOIN customers c ON i.customer_id = c.id
                        JOIN invoice_items ii ON ii.invoice_id = i.id
                        WHERE i.rep_route_id IN ($routeIdsStr) AND ii.item_id = :orig_item_id AND i.status != 'Voided'
                    ");
                    $db->bind(':price', $replPrice);
                    $db->bind(':orig_item_id', $subRow->original_item_id);
                    $invoices = $db->resultSet() ?: [];
                }
            } else {
                $sql = "
                    SELECT i.id as invoice_id, i.invoice_number, c.name as customer_name, ii.quantity, ii.unit_price, ii.total as line_total, ii.variation_option_id, ii.quantity as original_qty
                    FROM invoices i
                    JOIN customers c ON i.customer_id = c.id
                    JOIN invoice_items ii ON ii.invoice_id = i.id
                    WHERE i.rep_route_id IN ($routeIdsStr) AND ii.item_id = :item_id AND i.status != 'Voided'
                ";
                if ($varId !== null && $varId > 0) {
                    $sql .= " AND ii.variation_option_id = :var_id";
                } else {
                    $sql .= " AND (ii.variation_option_id IS NULL OR ii.variation_option_id = 0)";
                }
                $db->query($sql);
                $db->bind(':item_id', $itemId);
                if ($varId !== null && $varId > 0) {
                    $db->bind(':var_id', $varId);
                }
                $invoices = $db->resultSet() ?: [];
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'invoices' => $invoices]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_adjust_variance_billing() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $routeId = intval($payload['route_id'] ?? 0);
        $adjustments = $payload['adjustments'] ?? [];
        $force = !empty($payload['force']) ? true : false;
        $userId = $_SESSION['user_id'] ?? 1;

        try {
            $result = RepVarianceService::adjustVarianceBilling($routeId, $adjustments, $userId, $force);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_get_route_substitutions($routeId) {
        $db = new Database();
        $db->query("
            SELECT ps.*, 
                   COALESCE(oi.name, 'Deleted Product') as original_item_name, 
                   COALESCE(ri.name, 'Deleted Product') as replacement_item_name,
                   u.username as creator_name
            FROM product_substitutions ps
            LEFT JOIN items oi ON ps.original_item_id = oi.id
            LEFT JOIN items ri ON ps.replacement_item_id = ri.id
            LEFT JOIN users u ON ps.user_id = u.id
            WHERE ps.route_id = :rid
            ORDER BY ps.created_at DESC
        ");
        $db->bind(':rid', $routeId);
        $subs = $db->resultSet() ?: [];

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'substitutions' => $subs]);
        exit;
    }

    public function api_apply_substitution() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $subId = intval($payload['substitution_id'] ?? 0);
        $pricingChoice = $payload['pricing_choice'] ?? 'replacement'; // 'original' or 'replacement'
        $userId = $_SESSION['user_id'] ?? 1;

        if (!$subId) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Substitution ID is required']);
            exit;
        }

        $db = new Database();
        $db->beginTransaction();
        try {
            RepVarianceService::executeApplySubstitution($db, $subId, $pricingChoice, $userId);
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Product substitution applied to bills successfully!']);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_finalize_collections() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $paymentIds = $payload['payment_ids'] ?? [];
        $bankAllocations = $payload['bank_allocations'] ?? [];
        $customDebitAccounts = $payload['debit_accounts'] ?? [];
        $customCreditAccounts = $payload['credit_accounts'] ?? [];
        $userId = $_SESSION['user_id'];
        
        try {
            $db = new Database();
            $db->beginTransaction();
            $this->trackingModel->finalizePayments($paymentIds, $userId, $bankAllocations, $customDebitAccounts, $customCreditAccounts);
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Selected collections posted to GL successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_delete_sales_order($invoiceId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        try {
            $db = new Database();
            $db->beginTransaction();

            // Retrieve invoice number for reference
            $db->query("SELECT invoice_number FROM invoices WHERE id = :id");
            $db->bind(':id', $invoiceId);
            $invRow = $db->single();
            $invNumber = $invRow ? $invRow->invoice_number : 'INV-DELETE';

            $db->query("SELECT item_id, variation_option_id, quantity FROM invoice_items WHERE invoice_id = :iid");
            $db->bind(':iid', $invoiceId);
            $items = $db->resultSet() ?: [];
            foreach ($items as $item) {
                $itemId = $item->item_id;
                $varId = (!empty($item->variation_option_id) && is_numeric($item->variation_option_id) && intval($item->variation_option_id) > 0) ? intval($item->variation_option_id) : null;
                $qty = floatval($item->quantity);

                if ($itemId) {
                    $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                    $db->bind(':qty', $qty);
                    $db->bind(':id', $itemId);
                    $db->execute();
                }
                if ($varId) {
                    $db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                    $db->bind(':qty', $qty);
                    $db->bind(':id', $varId);
                    $db->execute();
                }

                // Log movement in stock ledger (HIGH-6)
                require_once dirname(__DIR__) . '/Models/StockLedger.php';
                $ledger = new StockLedger();
                $db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                $db->bind(':id', $itemId);
                $itemRow = $db->single();
                $whId = $itemRow ? $itemRow->warehouse_id : null;
                $itemCost = $itemRow ? floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00) : 0.00;
                
                $ledger->logMovement($itemId, $varId, 0, 0, 'Reserved Stock Release', $invNumber, $whId, $_SESSION['user_id'] ?? 1, 'Invoice Deleted - Reserved Stock Released', $itemCost);
            }
            $db->query("DELETE FROM invoice_items WHERE invoice_id = :iid");
            $db->bind(':iid', $invoiceId);
            $db->execute();
            $db->query("DELETE FROM invoices WHERE id = :iid");
            $db->bind(':iid', $invoiceId);
            $db->execute();
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Sales Order deleted successfully and inventory released!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_get_unattached_invoices() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        
        $invoicesList = [];
        
        // 1. Fetch unattached route bookings from invoices
        if (empty($status) || $status !== 'Pending') {
            $queryStr = "
                SELECT i.id, i.invoice_number, i.invoice_date, c.name as customer_name, i.status,
                       (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as true_grand_total
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                WHERE (i.rep_route_id IS NULL OR i.rep_route_id = 0)
                  AND i.stock_status = 'reserved'
                  AND i.status != 'Voided'
            ";
            $params = [];
            if (!empty($search)) {
                $queryStr .= " AND (i.invoice_number LIKE :search OR c.name LIKE :search2)";
                $params['search'] = '%' . $search . '%';
                $params['search2'] = '%' . $search . '%';
            }
            if (!empty($startDate)) {
                $queryStr .= " AND i.invoice_date >= :start_date";
                $params['start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $queryStr .= " AND i.invoice_date <= :end_date";
                $params['end_date'] = $endDate;
            }
            if (!empty($status)) {
                $queryStr .= " AND i.status = :status";
                $params['status'] = $status;
            }
            $queryStr .= " ORDER BY i.invoice_number DESC LIMIT 50";
            $db = new Database();
            $db->query($queryStr);
            foreach ($params as $key => $val) {
                $db->bind(':' . $key, $val);
            }
            $invoices = $db->resultSet() ?: [];
            foreach ($invoices as $inv) {
                $invoicesList[] = [
                    'id' => 'route:' . $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'invoice_date' => $inv->invoice_date,
                    'customer_name' => $inv->customer_name,
                    'status' => $inv->status,
                    'true_grand_total' => floatval($inv->true_grand_total)
                ];
            }
        }

        // 2. Fetch pending standard sales orders
        if (empty($status) || $status === 'Pending') {
            $soQueryStr = "
                SELECT so.id, so.order_number as invoice_number, so.order_date as invoice_date, c.name as customer_name, so.status,
                       so.grand_total as true_grand_total
                FROM sales_orders so
                JOIN customers c ON so.customer_id = c.id
                WHERE so.status = 'Pending'
            ";
            $soParams = [];
            if (!empty($search)) {
                $soQueryStr .= " AND (so.order_number LIKE :search OR c.name LIKE :search2)";
                $soParams['search'] = '%' . $search . '%';
                $soParams['search2'] = '%' . $search . '%';
            }
            if (!empty($startDate)) {
                $soQueryStr .= " AND so.order_date >= :start_date";
                $soParams['start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $soQueryStr .= " AND so.order_date <= :end_date";
                $soParams['end_date'] = $endDate;
            }
            $soQueryStr .= " ORDER BY so.order_number DESC LIMIT 50";
            $db = new Database();
            $db->query($soQueryStr);
            foreach ($soParams as $key => $val) {
                $db->bind(':' . $key, $val);
            }
            $soList = $db->resultSet() ?: [];
            foreach ($soList as $so) {
                $invoicesList[] = [
                    'id' => 'standard:' . $so->id,
                    'invoice_number' => $so->invoice_number,
                    'invoice_date' => $so->invoice_date,
                    'customer_name' => $so->customer_name,
                    'status' => $so->status,
                    'true_grand_total' => floatval($so->true_grand_total)
                ];
            }
        }

        // Sort combined list by date desc
        usort($invoicesList, function($a, $b) {
            return strcmp($b['invoice_date'], $a['invoice_date']);
        });

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'invoices' => $invoicesList]);
        exit;
    }

    public function api_attach_invoices() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $routeId = intval($payload['route_id'] ?? 0);
        $invoiceIds = $payload['invoice_ids'] ?? [];
        $userId = $_SESSION['user_id'] ?? 1;

        if ($routeId <= 0 || empty($invoiceIds)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid route or invoice selection.']);
            exit;
        }
        try {
            RepRouteService::attachInvoices($routeId, $invoiceIds, $userId);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Invoices attached successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_create_binding() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $bindingName = trim($payload['binding_name'] ?? '');
        $routeIds = $payload['route_ids'] ?? [];
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if (empty($bindingName) || empty($routeIds) || count($routeIds) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid route name and select at least 2 routes to bind.']);
            exit;
        }

        try {
            $result = RepBindingService::createBinding($bindingName, $routeIds, $userId, $ipAddress);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_unbind_route() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $bindingId = intval($payload['binding_id'] ?? 0);
        $routeId = intval($payload['route_id'] ?? 0);
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        try {
            $result = RepBindingService::unbindRoute($bindingId, $routeId, $userId, $ipAddress);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_get_bound_routes_summary($routeId) {
        $db = new Database();
        
        // Find all constituent routes bound into this route
        $db->query("SELECT id, route_name, status, start_time FROM rep_daily_routes WHERE bound_to_route_id = :rid");
        $db->bind(':rid', $routeId);
        $constituents = $db->resultSet() ?: [];
        
        // Fetch invoices for this route to calculate customer count, invoice count, and values
        $db->query("
            SELECT i.id, i.customer_id, 
                   (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            WHERE i.rep_route_id = :rid AND i.status != 'Voided'
        ");
        $db->bind(':rid', $routeId);
        $invoices = $db->resultSet() ?: [];
        
        $customerIds = [];
        $totalValue = 0.0;
        foreach ($invoices as $inv) {
            $customerIds[] = $inv->customer_id;
            $totalValue += floatval($inv->true_grand_total);
        }
        $totalCustomers = count(array_unique($customerIds));
        $totalInvoices = count($invoices);
        
        // Fetch unique products count and total quantities
        $db->query("
            SELECT ii.description as item_name, SUM(ii.quantity) as qty
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.rep_route_id = :rid AND i.status != 'Voided'
            GROUP BY ii.description
        ");
        $db->bind(':rid', $routeId);
        $items = $db->resultSet() ?: [];
        
        $uniqueProductsCount = count($items);
        $totalProductsQty = 0.0;
        foreach ($items as $item) {
            $totalProductsQty += floatval($item->qty);
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'constituents' => $constituents,
            'total_customers' => $totalCustomers,
            'total_invoices' => $totalInvoices,
            'total_value' => $totalValue,
            'unique_products' => $uniqueProductsCount,
            'total_products_qty' => $totalProductsQty
        ]);
        exit;
    }

    public function api_save_reconciliation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $deliveryId = intval($payload['delivery_id'] ?? 0);
        $reconciliationData = $payload['reconciliation_data'] ?? null;
        
        if ($deliveryId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid delivery ID.']);
            exit;
        }
        
        try {
            $db = new Database();
            $db->query("UPDATE deliveries SET reconciliation_json = :recon WHERE id = :id");
            $db->bind(':recon', json_encode($reconciliationData));
            $db->bind(':id', $deliveryId);
            $db->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Reconciliation data saved successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_get_invoice_for_delivery($invoiceId) {
        $invoiceId = intval($invoiceId);
        
        $driverInvoiceModel = $this->model('DriverInvoice');
        $invoice = $driverInvoiceModel->getInvoiceDetails($invoiceId);
        if (!$invoice) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
            exit;
        }
        
        $items = $driverInvoiceModel->getInvoiceItems($invoiceId);
        $arrears = $driverInvoiceModel->getCustomerTotalArrears($invoice->customer_id, $invoice->rep_route_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'invoice' => $invoice,
            'items' => $items,
            'arrears' => $arrears
        ]);
        exit;
    }

    public function api_process_delivery_visit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $routeId = intval($payload['route_id'] ?? 0);
        $customerId = intval($payload['customer_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($routeId <= 0 || $customerId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid route or customer ID.']);
            exit;
        }
        
        $driverInvoiceModel = $this->model('DriverInvoice');
        
        // 1. Process deliveries
        $deliveries = $payload['deliveries'] ?? [];
        foreach ($deliveries as $del) {
            $invoiceId = intval($del['invoice_id'] ?? 0);
            $deliveryStatus = $del['delivery_status'] ?? 'Delivered';
            if ($invoiceId > 0) {
                $driverInvoiceModel->updateInvoiceDeliveryStatus($invoiceId, $deliveryStatus);
                
                $items = $del['items'] ?? [];
                
                // If status is Cancelled or Postponed, set all items delivered qty to 0
                if ($deliveryStatus === 'Cancelled' || $deliveryStatus === 'Postponed') {
                    $allInvoiceItems = $driverInvoiceModel->getInvoiceItems($invoiceId);
                    foreach ($allInvoiceItems as $item) {
                        $driverInvoiceModel->deleteInvoiceItem($item->id); // sets quantity to 0 and releases reservation
                    }
                    $db = new Database();
                    $db->query("UPDATE invoices SET stock_status = 'returned' WHERE id = :id");
                    $db->bind(':id', $invoiceId);
                    $db->execute();
                } else {
                    // If we are setting to Delivered or Pending, and NO custom items were sent in payload
                    // (meaning it was a simple status dropdown change), we should restore the items to their loaded_quantity!
                    if (empty($items)) {
                        $allInvoiceItems = $driverInvoiceModel->getInvoiceItems($invoiceId);
                        foreach ($allInvoiceItems as $item) {
                            if (floatval($item->quantity) == 0 && floatval($item->loaded_quantity) > 0) {
                                $driverInvoiceModel->updateInvoiceItemQty($item->id, intval($item->loaded_quantity));
                            }
                        }
                    } else {
                        // Otherwise update based on custom user input
                        foreach ($items as $item) {
                            $itemId = intval($item['invoice_item_id'] ?? 0);
                            $deliveredQty = intval($item['delivered_qty'] ?? 0);
                            if ($itemId > 0) {
                                if ($deliveredQty <= 0) {
                                    $driverInvoiceModel->deleteInvoiceItem($itemId);
                                } else {
                                    $driverInvoiceModel->updateInvoiceItemQty($itemId, $deliveredQty);
                                }
                            }
                        }
                    }
                    $db = new Database();
                    $db->query("UPDATE invoices SET stock_status = 'reserved' WHERE id = :id");
                    $db->bind(':id', $invoiceId);
                    $db->execute();
                }
            }
        }
        
        // 2. Process payments (collections)
        $collections = $payload['collections'] ?? null;
        if ($collections) {
            $cashAmt = floatval($collections['cash'] ?? 0);
            $bankAmt = floatval($collections['bank'] ?? 0);
            $cheques = $collections['cheques'] ?? [];
            
            $collectionsPayload = [
                'cash' => $cashAmt,
                'bank' => $bankAmt,
                'cheques' => $cheques
            ];
            
            if ($cashAmt > 0 || $bankAmt > 0 || count($cheques) > 0) {
                $driverInvoiceModel->checkoutShop($customerId, $routeId, $userId, $collectionsPayload);
            }
        }
        
        // 3. Auto-apply payments to invoices
        $this->autoApplyPaymentsToInvoices($customerId);
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Delivery visit processed successfully!']);
        exit;
    }

    public function api_save_return_stock() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $deliveryId = intval($payload['delivery_id'] ?? 0);
        $returnStockData = $payload['return_stock_data'] ?? null;
        
        if ($deliveryId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid delivery ID.']);
            exit;
        }
        
        try {
            $db = new Database();
            
            // Check if delivery is finalized
            $db->query("SELECT status FROM deliveries WHERE id = :id LIMIT 1");
            $db->bind(':id', $deliveryId);
            $existing = $db->single();
            if ($existing && ($existing->status === 'Finalized' || $existing->status === 'Completed')) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Cannot verify return stock because this delivery has already been finalized.']);
                exit;
            }

            $db->beginTransaction();

            // Update return stock JSON and verified fields
            $db->query("UPDATE deliveries SET 
                        return_stock_json = :ret,
                        return_stock_verified_by = :uid,
                        return_stock_verified_at = NOW()
                        WHERE id = :id");
            $db->bind(':ret', json_encode($returnStockData));
            $db->bind(':uid', $_SESSION['user_id'] ?? null);
            $db->bind(':id', $deliveryId);
            $db->execute();

            // Write Audit Log
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $db->query("INSERT INTO audit_logs (user_id, action, module, description, record_id, ip_address) 
                        VALUES (:uid, 'RETURN_STOCK_DRAFT_SAVE', 'Logistics', :desc, :ref, :ip)");
            $db->bind(':uid', $_SESSION['user_id'] ?? null);
            $db->bind(':desc', "Saved return stock draft for delivery ID: " . $deliveryId);
            $db->bind(':ref', $deliveryId);
            $db->bind(':ip', $ip);
            $db->execute();

            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Return stock draft saved successfully!']);
            exit;
        } catch (Exception $e) {
            if (isset($db)) { $db->rollBack(); }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving return stock draft. Please try again.']);
            exit;
        }
    }

    public function api_save_accounting_entries() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        if (!$this->validateCsrf()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'CSRF validation failed.']);
            exit;
        }
        $payload = json_decode(file_get_contents('php://input'), true);
        $deliveryId = intval($payload['delivery_id'] ?? 0);
        $routeId = intval($payload['route_id'] ?? 0);
        $accountingEntries = $payload['accounting_entries'] ?? $payload['accounting_entries_json'] ?? null;
        
        $db = new Database();
        
        if ($deliveryId <= 0 && $routeId > 0) {
            $db->query("SELECT id FROM deliveries WHERE rep_route_id = :rid ORDER BY id DESC LIMIT 1");
            $db->bind(':rid', $routeId);
            $delRow = $db->single();
            if ($delRow) {
                $deliveryId = intval($delRow->id);
            } else {
                $db->query("SELECT start_time FROM rep_daily_routes WHERE id = :rid");
                $db->bind(':rid', $routeId);
                $rRow = $db->single();
                $deliveryDate = $rRow ? date('Y-m-d', strtotime($rRow->start_time)) : date('Y-m-d');
                
                $db->query("INSERT INTO deliveries (rep_route_id, delivery_date, vehicle_number, driver_name, status) 
                            VALUES (:rid, :ddate, '', '', 'Arranged')");
                $db->bind(':rid', $routeId);
                $db->bind(':ddate', $deliveryDate);
                $db->execute();
                $deliveryId = intval($db->lastInsertId());
            }
        }
        
        if ($deliveryId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid delivery ID.']);
            exit;
        }
        
        try {
            $db->beginTransaction();

            // Save the raw JSON mapping to the delivery as cache/fallback
            $db->query("UPDATE deliveries SET accounting_entries_json = :acc WHERE id = :id");
            $db->bind(':acc', json_encode($accountingEntries));
            $db->bind(':id', $deliveryId);
            $db->execute();

            // If there are accounting entries draft, parse and insert them as Draft Journal Entries and Transactions
            if (is_array($accountingEntries)) {
                $debits = $accountingEntries['debit'] ?? [];
                $credits = $accountingEntries['credit'] ?? [];
                $collections = $payload['collections'] ?? $payload['accounting_entries_json']['collections'] ?? $accountingEntries['collections'] ?? [];

                // 1. Save collections verification draft to pending_collections
                foreach ($collections as $c) {
                    $payId = intval($c['id']);
                    $isVerified = intval($c['is_verified'] ?? 0);
                    $adjAmount = (isset($c['adjusted_amount']) && $c['adjusted_amount'] !== '') ? floatval($c['adjusted_amount']) : null;
                    $notes = isset($c['verification_notes']) ? trim($c['verification_notes']) : null;
                    $debitAccId = !empty($c['debit_account_id']) ? intval($c['debit_account_id']) : null;
                    $creditAccId = !empty($c['credit_account_id']) ? intval($c['credit_account_id']) : null;

                    $db->query("UPDATE pending_collections 
                                SET is_verified = :is_v, adjusted_amount = :adj, verification_notes = :notes, 
                                    debit_account_id = :da, credit_account_id = :ca
                                WHERE id = :id");
                    $db->bind(':is_v', $isVerified);
                    $db->bind(':adj', $adjAmount);
                    $db->bind(':notes', $notes);
                    $db->bind(':da', $debitAccId);
                    $db->bind(':ca', $creditAccId);
                    $db->bind(':id', $payId);
                    $db->execute();
                }

                // 2. Gather invoice keys and insert draft Journal Entries and Transactions for Invoices Sales Posting
                $allKeys = array_unique(array_merge(array_keys($debits), array_keys($credits)));

                // Default accounts for fallback
                $db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1200', '4000', '1000', '1010', '1605', '1600', '1090')");
                $defaultAccs = $db->resultSet();
                $accMap = [];
                foreach ($defaultAccs as $a) {
                    $accMap[$a->account_code] = $a->id;
                }
                $defaultArAcc = $accMap['1200'] ?? null;
                $defaultSalesAcc = $accMap['4000'] ?? null;

                foreach ($allKeys as $key) {
                    $debAcc = isset($debits[$key]) ? intval($debits[$key]) : null;
                    $credAcc = isset($credits[$key]) ? intval($credits[$key]) : null;

                    if (strpos($key, 'inv_') === 0) {
                        $invId = intval(substr($key, 4));
                        if ($invId <= 0) continue;

                        // Fetch invoice
                        $db->query("SELECT * FROM invoices WHERE id = :id AND status != 'Voided'");
                        $db->bind(':id', $invId);
                        $invoice = $db->single();
                        if (!$invoice) continue;

                        // Get true grand total
                        $subTotal = floatval($invoice->total_amount ?? 0);
                        $globalDiscVal = floatval($invoice->global_discount_val ?? 0);
                        $globalDiscType = $invoice->global_discount_type ?? 'Rs';
                        $globalDisc = ($globalDiscType === '%') ? ($subTotal * $globalDiscVal / 100) : $globalDiscVal;
                        $gTotal = max(0, $subTotal - $globalDisc) + floatval($invoice->tax_amount ?? 0);

                        if ($gTotal <= 0) continue;

                        // Use defaults if not set
                        if (!$debAcc) $debAcc = $defaultArAcc;
                        if (!$credAcc) $credAcc = $defaultSalesAcc;

                        if ($debAcc && $credAcc) {
                            // Find and clean up any existing draft JEs for this invoice
                            $db->query("SELECT id FROM journal_entries WHERE reference = :ref AND status = 'Draft'");
                            $db->bind(':ref', "INV-SALES-DRAFT-" . $invId);
                            $oldJEs = $db->resultSet();
                            foreach ($oldJEs as $oldJE) {
                                $db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                                $db->bind(':jid', $oldJE->id);
                                $db->execute();

                                $db->query("DELETE FROM journal_entries WHERE id = :id");
                                $db->bind(':id', $oldJE->id);
                                $db->execute();
                            }

                            // Insert new Draft Journal Entry
                            $db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                              VALUES (CURDATE(), :ref, :desc, :uid, 'Draft')");
                            $db->bind(':ref', "INV-SALES-DRAFT-" . $invId);
                            $db->bind(':desc', "Sales Invoice Delivery Revenue Posting (" . $invoice->invoice_number . ") [Draft]");
                            $db->bind(':uid', $_SESSION['user_id']);
                            $db->execute();
                            $jid = $db->lastInsertId();

                            // Debit transaction
                            $db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $debAcc);
                            $db->bind(':deb', $gTotal);
                            $db->execute();

                            // Credit transaction
                            $db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, 0, :cred)");
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $credAcc);
                            $db->bind(':cred', $gTotal);
                            $db->execute();
                        }
                    }
                }
            }

            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Suggested double-entries accounting mappings and draft ledger entries saved successfully!', 'delivery_id' => $deliveryId]);
            exit;
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_save_route_notes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $routeId = intval($payload['route_id'] ?? 0);
        $notes = $payload['notes'] ?? '';
        
        if ($routeId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid route ID.']);
            exit;
        }
        
        try {
            $db = new Database();
            $db->query("UPDATE rep_daily_routes SET notes = :notes WHERE id = :id");
            $db->bind(':notes', $notes);
            $db->bind(':id', $routeId);
            $db->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Route notes saved successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_get_route_details($routeId) {
        $bills = $this->trackingModel->getRouteBills($routeId);
        // Fetch notes from rep_daily_routes
        $db = new Database();
        $db->query("SELECT notes FROM rep_daily_routes WHERE id = :id LIMIT 1");
        $db->bind(':id', $routeId);
        $routeRow = $db->single();
        $notes = $routeRow ? $routeRow->notes : '';

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $bills, 'notes' => $notes]);
        exit;
    }

    public function api_detach_invoice() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $this->validateCsrf();
        $payload = json_decode(file_get_contents('php://input'), true);
        $invoiceId = intval($payload['invoice_id'] ?? 0);
        if ($invoiceId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid invoice ID.']);
            exit;
        }
        try {
            $db = new Database();
            $db->query("UPDATE invoices SET rep_route_id = NULL WHERE id = :id");
            $db->bind(':id', $invoiceId);
            $db->execute();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Invoice detached successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function create_route_manual() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/RepTracking');
            exit;
        }

        $this->validateCsrf();

        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $routeName = isset($_POST['route_name']) ? trim($_POST['route_name']) : '';
        $startMeter = isset($_POST['start_meter']) ? floatval($_POST['start_meter']) : 0.0;
        $startTime = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';

        if ($userId <= 0 || empty($routeName) || empty($startTime)) {
            $_SESSION['flash_error'] = 'Invalid route input. Please fill in all fields.';
            header('Location: ' . APP_URL . '/RepTracking');
            exit;
        }

        // Generate unique UUID
        $uuid = bin2hex(random_bytes(16));
        $uuid = substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20, 12);

        $db = new Database();
        try {
            $db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, start_lat, start_lng, status, uuid) 
                        VALUES (:user_id, :route_name, :start_meter, :start_time, 0.0, 0.0, 'Active', :uuid)");
            $db->bind(':user_id', $userId);
            $db->bind(':route_name', $routeName);
            $db->bind(':start_meter', $startMeter);
            $db->bind(':start_time', $startTime);
            $db->bind(':uuid', $uuid);
            
            if ($db->execute()) {
                $_SESSION['flash_success'] = "Route '{$routeName}' created successfully.";
            } else {
                $_SESSION['flash_error'] = 'Failed to create route.';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database Error: ' . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/RepTracking');
        exit;
    }

    public function get_captcha() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['delete_route_captcha'] = $num1 + $num2;
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'question' => "What is $num1 + $num2?"]);
        exit;
    }

    private function descramblePassword($str, $key) {
        if (empty($key)) {
            return $str;
        }
        $data = base64_decode($str);
        if ($data === false) {
            return $str;
        }
        $result = "";
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $result .= $data[$i] ^ $key[$i % $keyLen];
        }
        return $result;
    }

    public function delete_route() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/RepTracking');
            exit;
        }

        $this->validateCsrf();

        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true);
        if (!$postData) {
            $postData = $_POST;
        }

        $scrambledPassword = $postData['password'] ?? '';
        $reason = trim($postData['delete_reason'] ?? '');
        $routeId = intval($postData['route_id'] ?? 0);
        $mode = $postData['mode'] ?? 'detach'; // 'detach', 'delete_with_so', 'force_delete_all'
        $captchaAnswer = trim($postData['captcha_answer'] ?? '');

        $errorResponse = function($msg) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        };

        if (!$routeId) {
            $errorResponse("Route ID is missing!");
        }

        if (empty($reason)) {
            $errorResponse("Deletion reason is required!");
        }

        // 1. Rate Limiting Check
        $sessionKey = 'route_delete_attempts';
        $lockoutKey = 'route_delete_lockout_time';
        $now = time();

        if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey] > $now) {
            $timeLeft = $_SESSION[$lockoutKey] - $now;
            $errorResponse("Too many failed attempts. Please try again in " . $timeLeft . " seconds.");
        }

        // 2. CAPTCHA Check
        $expectedCaptcha = $_SESSION['delete_route_captcha'] ?? null;
        if ($expectedCaptcha === null || intval($captchaAnswer) !== intval($expectedCaptcha)) {
            $errorResponse("Security CAPTCHA verification failed! Please try again.");
        }
        // Invalidate CAPTCHA after verification attempt
        unset($_SESSION['delete_route_captcha']);

        // Descramble the password using session CSRF token
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $password = $this->descramblePassword($scrambledPassword, $csrfToken);

        // Authenticate password
        $userModel = $this->model('User');
        $username = $_SESSION['username'] ?? '';
        $user = $userModel->login($username, $password);

        if (!$user) {
            $_SESSION[$sessionKey] = ($_SESSION[$sessionKey] ?? 0) + 1;
            if ($_SESSION[$sessionKey] >= 3) {
                $_SESSION[$lockoutKey] = time() + 300; // 5 minutes lockout
                unset($_SESSION[$sessionKey]);
                $errorResponse("Authentication failed: Incorrect password! Too many failed attempts. You have been locked out for 5 minutes.");
            } else {
                $remaining = 3 - $_SESSION[$sessionKey];
                $errorResponse("Authentication failed: Incorrect password! " . $remaining . " attempts remaining.");
            }
        }

        // Reset rate limiter on successful authentication
        unset($_SESSION[$sessionKey]);
        unset($_SESSION[$lockoutKey]);

        // Verify Delete Permission
        if (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'admin') {
            $perms = $_SESSION['permissions'] ?? [];
            if (!($perms['sales']['can_delete'] ?? false)) {
                $errorResponse("Access denied: You do not have permission to delete daily routes.");
            }
        }

        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $msg = RepRouteService::deleteRoute($routeId, $mode, $reason, $userId, $username);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => $msg]);
            exit;
        } catch (Exception $e) {
            $errorResponse('Failed to delete route: ' . $e->getMessage());
        }
    }
}
