<?php
class RepTrackingController extends Controller {
    private $trackingModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->trackingModel = $this->model('RepTracking');
    }

    public function index() {
        $vehicleModel = $this->model('Vehicle');
        $employeeModel = $this->model('Employee');

        // Fetch all vehicles
        $vehicles = $vehicleModel->getAllVehicles();

        // Fetch all employees to separate drivers and all employees (for partners)
        $allEmployees = $employeeModel->getAllEmployees();

        // Filter drivers where job_title is "Driver" (case-insensitive) and status is "Active"
        $drivers = array_filter($allEmployees, function($emp) {
            return strtolower($emp->job_title) === 'driver' && $emp->status === 'Active';
        });

        // Fetch Bank Accounts for Finalization dropdown
        $db = new Database();
        $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600'");
        $parent = $db->single();
        $parentId = $parent ? $parent->id : 0;
        
        $db->query("SELECT * FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $db->bind(':pid', $parentId);
        $bankAccounts = $db->resultSet() ?: [];

        $db->query("SELECT id, account_code, account_name FROM chart_of_accounts ORDER BY account_code ASC");
        $allAccounts = $db->resultSet() ?: [];

        $data = [
            'title' => 'Rep Route Tracking',
            'content_view' => 'rep-tracking/index',
            'routes' => $this->trackingModel->getAllRoutes(),
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'employees' => $allEmployees,
            'bank_accounts' => $bankAccounts,
            'all_accounts' => $allAccounts
        ];
        
        $this->view('layouts/main', $data);
    }

    // API Endpoint for AJAX fetching
    public function api_get_route_details($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        
        $bills = $this->trackingModel->getRouteBills($routeId);
        
        // Return JSON response to the Javascript frontend
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $bills]);
        exit;
    }

    // API: chronological GPS path (start → invoices → end)
    public function api_get_route_path($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            die("Invalid Request");
        }

        $path = $this->trackingModel->getRoutePath($routeId);
        header('Content-Type: application/json');
        if (!$path) {
            echo json_encode(['status' => 'error', 'message' => 'Route not found']);
            exit;
        }
        echo json_encode(['status' => 'success', 'path' => $path]);
        exit;
    }

    // Helper to auto-apply customer payments to non-voided invoices in chronological (FIFO) order
    private function autoApplyPaymentsToInvoices($customerId) {
        $db = new Database();
        
        // 1. Get total paid amount for this customer
        $db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE customer_id = :cid");
        $db->bind(':cid', $customerId);
        $rowPaid = $db->single();
        $totalPaid = $rowPaid ? floatval($rowPaid->total_paid) : 0.0;
        
        // 2. Get all non-voided invoices in chronological order
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
            
            if ($remainingPaid >= $grandTotal - 0.01) { // Allow minor rounding differences
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

    // API: outstanding credit bills in the same main area/territory of the route
    public function api_get_outstanding_bills($routeId) {
        $db = new Database();
        
        $routeIds = [$routeId];
        // Resolve all bound routes sharing the same route_binding_id
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
                // Fallback
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

        // Fetch and heal statuses for all customers in this territory who have invoices
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

        // Fetch outstanding customers (aggregated outstanding totals) in that territory,
        // excluding invoices already assigned to the route(s) to avoid duplicate assignments
        $db->query("
            SELECT c.id as customer_id, c.name as customer_name, m.name as mca_name,
                   SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_outstanding
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            JOIN mca_areas m ON c.mca_id = m.id
            WHERE m.main_area_id IN ($areaIdsStr)
              AND i.status = 'Unpaid'
              AND (i.rep_route_id IS NULL OR i.rep_route_id NOT IN ($routeIdsStr))
            GROUP BY c.id, c.name, m.name
            ORDER BY c.name ASC
        ");
        $bills = $db->resultSet();
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $bills]);
        exit;
    }

    // NEW: Endpoint to generate and show the Loading Report
    public function print_loading($routeId) {
        $data = [
            'route' => $this->trackingModel->getRouteById($routeId),
            'items' => $this->trackingModel->getRouteLoadingItems($routeId)
        ];
        
        // Load the print view directly (without the main navbar/sidebar layout)
        $this->view('rep-tracking/print_loading', $data);
    }

    // NEW: Get collections and unfinalized GL details for a route
    public function api_get_route_collections($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        $collections = $this->trackingModel->getRouteCollections($routeId);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'collections' => $collections]);
        exit;
    }

    // NEW: Finalize GL double entries for selected collections with custom debits and credits
    public function api_finalize_collections() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        
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

    // NEW: Delete Sales Order API
    public function api_delete_sales_order($invoiceId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        
        try {
            $db = new Database();
            $db->beginTransaction();
            
            // 1. Restore reserved quantities
            $db->query("SELECT item_id, quantity FROM invoice_items WHERE invoice_id = :iid");
            $db->bind(':iid', $invoiceId);
            $items = $db->resultSet() ?: [];
            
            foreach ($items as $item) {
                if ($item->item_id) {
                    $db->query("UPDATE items SET quantity_reserved = GREATEST(0, quantity_reserved - :qty) WHERE id = :id");
                    $db->bind(':qty', $item->quantity);
                    $db->bind(':id', $item->item_id);
                    $db->execute();
                }
            }
            
            // 2. Delete invoice items
            $db->query("DELETE FROM invoice_items WHERE invoice_id = :iid");
            $db->bind(':iid', $invoiceId);
            $db->execute();
            
            // 3. Delete invoice
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

    // NEW: Get unattached invoices (Filtered to Sales Orders only)
    public function api_get_unattached_invoices() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        
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
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'invoices' => $invoices]);
        exit;
    }


    // NEW: Attach unattached invoices to route
    public function api_attach_invoices() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $routeId = intval($payload['route_id'] ?? 0);
        $invoiceIds = $payload['invoice_ids'] ?? [];
        
        if ($routeId <= 0 || empty($invoiceIds)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid route or invoice selection.']);
            exit;
        }
        
        try {
            $db = new Database();
            $db->beginTransaction();
            
            foreach ($invoiceIds as $invId) {
                $db->query("UPDATE invoices SET rep_route_id = :rid WHERE id = :id");
                $db->bind(':rid', $routeId);
                $db->bind(':id', $invId);
                $db->execute();
            }
            
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Invoices attached successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // NEW: API endpoint to create route binding
    public function api_create_binding() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $bindingName = trim($payload['binding_name'] ?? '');
        $routeIds = $payload['route_ids'] ?? [];
        
        if (empty($bindingName) || empty($routeIds) || count($routeIds) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid route name and select at least 2 routes to bind.']);
            exit;
        }
        
        try {
            $db = new Database();
            $db->beginTransaction();
            
            // Insert binding
            $db->query("INSERT INTO route_bindings (name) VALUES (:name)");
            $db->bind(':name', $bindingName);
            $db->execute();
            $bindingId = $db->lastInsertId();
            
            // Link routes to binding
            $routeIdsList = implode(',', array_map('intval', $routeIds));
            $db->query("UPDATE rep_daily_routes SET route_binding_id = :bid WHERE id IN ($routeIdsList)");
            $db->bind(':bid', $bindingId);
            $db->execute();
            
            $db->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Routes successfully bound under "' . $bindingName . '"!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // NEW: API endpoint to undo route binding (unbind routes)
    public function api_unbind_route() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        $bindingId = intval($payload['binding_id'] ?? 0);
        
        if (empty($bindingId)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid Binding ID.']);
            exit;
        }
        
        try {
            $db = new Database();
            $db->beginTransaction();
            
            // 1. Remove association from daily routes
            $db->query("UPDATE rep_daily_routes SET route_binding_id = NULL WHERE route_binding_id = :bid");
            $db->bind(':bid', $bindingId);
            $db->execute();
            
            // 2. Delete the binding record
            $db->query("DELETE FROM route_bindings WHERE id = :bid");
            $db->bind(':bid', $bindingId);
            $db->execute();
            
            $db->commit();
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Route binding successfully undone! Routes are now separated.']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // NEW: Get loading variances for a route
    public function api_get_route_variances($routeId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { die("Invalid Request"); }
        
        $db = new Database();
        
        // Find deliveries linked to this route
        $db->query("
            SELECT d.id as id, d.vehicle_number, d.driver_name, d.status
            FROM deliveries d
            WHERE d.rep_route_id = :rid OR d.secondary_rep_route_id = :rid
        ");
        $db->bind(':rid', $routeId);
        $deliveries = $db->resultSet() ?: [];
        
        $results = [];
        
        foreach ($deliveries as $del) {
            $db->query("
                SELECT dpi.item_id, dpi.item_name, dpi.required_qty, dpi.loaded_qty as pre_loaded_qty, 
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
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'deliveries' => $results]);
        exit;
    }
}