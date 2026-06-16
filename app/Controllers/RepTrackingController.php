<?php
class RepTrackingController extends Controller {
    private $trackingModel;
    private $deliveryModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { 
            header('Location: ' . APP_URL . '/auth/login'); 
            exit; 
        }
        $this->trackingModel = $this->model('RepTracking');
        $this->deliveryModel = $this->model('Delivery');
    }

    public function index() {
        $vehicleModel = $this->model('Vehicle');
        $employeeModel = $this->model('Employee');

        $vehicles = $vehicleModel->getAllVehicles();
        $allEmployees = $employeeModel->getAllEmployees();

        $drivers = array_filter($allEmployees, function($emp) {
            return strtolower($emp->job_title) === 'driver' && $emp->status === 'Active';
        });

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
            'title' => 'Master Route Control Panel',
            'content_view' => 'rep-tracking/index',
            'routes' => $this->getUnifiedRoutes(),
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'employees' => $allEmployees,
            'bank_accounts' => $bankAccounts,
            'all_accounts' => $allAccounts
        ];
        
        $this->view('layouts/main', $data);
    }

    private function getUnifiedRoutes() {
        $db = new Database();
        $db->query("
            SELECT r.*, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales,
                (SELECT COUNT(*) FROM pending_collections WHERE route_id = r.id AND status = 'Pending') as unfinalized_count,
                rb.name as binding_name,
                d.id as delivery_id, d.vehicle_number, d.driver_name, d.partner_name, d.status as delivery_status,
                (SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id) as total_items,
                (SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id AND is_picked = 1) as picked_items,
                (SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id AND is_verified = 1) as verified_items
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            LEFT JOIN route_bindings rb ON r.route_binding_id = rb.id
            LEFT JOIN deliveries d ON d.rep_route_id = r.id OR d.secondary_rep_route_id = r.id
            WHERE r.status != 'Bound' AND r.status != 'Bound Into Route'
            ORDER BY r.start_time DESC
        ");
        $rawRoutes = $db->resultSet() ?: [];
        
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
        
        return $finalRoutes;
    }

    public function api_get_route_details($routeId) {
        $bills = $this->trackingModel->getRouteBills($routeId);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'bills' => $bills]);
        exit;
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
                SELECT i.id, i.invoice_number, i.invoice_date,
                       (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
                FROM invoices i
                WHERE i.customer_id = :cid AND i.status = 'Unpaid'
                  AND i.rep_route_id NOT IN ($routeIdsStr)
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
        $db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE customer_id = :cid");
        $db->bind(':cid', $customerId);
        $rowPaid = $db->single();
        $totalPaid = $rowPaid ? floatval($rowPaid->total_paid) : 0.0;
        
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
        $db = new Database();
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

    public function api_get_delivery_details($id) {
        $delivery = $this->deliveryModel->getDeliveryById($id);
        if (!$delivery) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Delivery not found.']);
            exit;
        }

        $invoices = $this->deliveryModel->getDeliveryInvoices($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null);
        $creditInvoices = $this->deliveryModel->getDeliveryCreditInvoices($delivery->rep_route_id, $delivery->secondary_rep_route_id ?? null);
        $balancing = $this->deliveryModel->getDeliveryBalancingData($id);

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

        $rawInput = file_get_contents('php://input');
        $postData = json_decode($rawInput, true);
        if (!$postData) {
            $postData = $_POST;
        }

        $routeId = intval($postData['route_id'] ?? 0);
        $targetStatus = trim($postData['status'] ?? '');

        $allowedStatuses = [
            'Active', 'Pending GL', 'Adjustments', 'Pending Loading', 'Final Loading', 
            'Variance Adjustment', 'Finalizing', 'Completed'
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
            $delStatus = 'Completed';
        }
        $db->query("UPDATE deliveries SET status = :status WHERE rep_route_id = :rid OR secondary_rep_route_id = :rid");
        $db->bind(':status', $delStatus);
        $db->bind(':rid', $routeId);
        $db->execute();

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
        } catch (Exception $e) {}
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
        $type = $_GET['type'] ?? 'pre';
        if ($type === 'final') {
            $items = $this->trackingModel->getRouteFinalLoadingItems($routeId);
        } else {
            $items = $this->trackingModel->getRouteLoadingItems($routeId);
        }
        $data = [
            'type' => $type,
            'route' => $this->trackingModel->getRouteById($routeId),
            'items' => $items,
            'bills' => $this->trackingModel->getRouteBills($routeId)
        ];
        $this->view('rep-tracking/print_loading', $data);
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
        $payload = json_decode(file_get_contents('php://input'), true);
        $updates = $payload['updates'] ?? [];
        $userId = $_SESSION['user_id'] ?? 1;

        try {
            $db = new Database();
            foreach ($updates as $up) {
                $paymentId = intval($up['id']);
                $isVerified = isset($up['is_verified']) ? intval($up['is_verified']) : 0;
                $isFlagged = isset($up['is_flagged']) ? intval($up['is_flagged']) : 0;
                $adjAmount = isset($up['adjusted_amount']) && $up['adjusted_amount'] !== '' ? floatval($up['adjusted_amount']) : null;
                $notes = isset($up['verification_notes']) ? trim($up['verification_notes']) : null;
                $debitAccId = !empty($up['debit_account_id']) ? intval($up['debit_account_id']) : null;
                $creditAccId = !empty($up['credit_account_id']) ? intval($up['credit_account_id']) : null;

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

                if ($isVerified === 1 && $currentStatus === 'Pending') {
                    $this->trackingModel->finalizePayments([$paymentId], $userId, [], [$paymentId => $debitAccId], [$paymentId => $creditAccId]);
                }
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Collections verified successfully!']);
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

        try {
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
            $routeIdsStr = implode(',', $routeIds);

            $db->query("
                SELECT i.id as invoice_id, i.invoice_number, c.name as customer_name, ii.quantity, ii.unit_price, ii.total as line_total
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                JOIN invoice_items ii ON ii.invoice_id = i.id
                WHERE i.rep_route_id IN ($routeIdsStr) AND ii.item_id = :item_id AND i.status != 'Voided'
            ");
            $db->bind(':item_id', $itemId);
            $invoices = $db->resultSet() ?: [];

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
        $payload = json_decode(file_get_contents('php://input'), true);
        $routeId = intval($payload['route_id'] ?? 0);
        $adjustments = $payload['adjustments'] ?? [];
        $userId = $_SESSION['user_id'] ?? 1;

        try {
            $db = new Database();
            $db->beginTransaction();

            $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1200' OR account_name LIKE '%Receivable%' LIMIT 1");
            $arAccRow = $db->single();
            $arAccId = $arAccRow ? intval($arAccRow->id) : null;

            $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' LIMIT 1");
            $revAccRow = $db->single();
            $revAccId = $revAccRow ? intval($revAccRow->id) : null;

            $modifiedInvoices = [];

            foreach ($adjustments as $adj) {
                $itemId = intval($adj['item_id']);
                $invoiceAdjs = $adj['invoice_adjustments'] ?? [];

                foreach ($invoiceAdjs as $ia) {
                    $invoiceId = intval($ia['invoice_id']);
                    $newQty = floatval($ia['new_qty']);

                    $db->query("SELECT ii.id, ii.quantity as old_qty, ii.unit_price, ii.discount_value, ii.discount_type, i.stock_status, i.invoice_number
                                FROM invoice_items ii
                                JOIN invoices i ON ii.invoice_id = i.id
                                WHERE ii.invoice_id = :iid AND ii.item_id = :item_id");
                    $db->bind(':iid', $invoiceId);
                    $db->bind(':item_id', $itemId);
                    $line = $db->single();

                    if ($line) {
                        $oldQty = floatval($line->old_qty);
                        if ($oldQty === $newQty) {
                            continue;
                        }

                        $unitPrice = floatval($line->unit_price);
                        $discVal = floatval($line->discount_value);
                        $discType = $line->discount_type;

                        $lineTotal = $newQty * $unitPrice;
                        if ($discType === '%') {
                            $lineTotal -= ($lineTotal * $discVal / 100);
                        } else {
                            $lineTotal -= ($discVal * $newQty);
                        }

                        $db->query("UPDATE invoice_items SET quantity = :qty, total = :total WHERE id = :id");
                        $db->bind(':qty', $newQty);
                        $db->bind(':total', $lineTotal);
                        $db->bind(':id', $line->id);
                        $db->execute();

                        $diff = $newQty - $oldQty;
                        if ($line->stock_status === 'reserved') {
                            $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :diff) WHERE id = :item_id");
                            $db->bind(':diff', $diff);
                            $db->bind(':item_id', $itemId);
                            $db->execute();
                        } else {
                            $db->query("UPDATE items SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :diff) WHERE id = :item_id");
                            $db->bind(':diff', $diff);
                            $db->bind(':item_id', $itemId);
                            $db->execute();

                            require_once '../app/Models/StockLedger.php';
                            $ledger = new StockLedger();
                            $db->query("SELECT warehouse_id, cost, cost_price FROM items WHERE id = :id");
                            $db->bind(':id', $itemId);
                            $itemRow = $db->single();
                            $whId = $itemRow ? $itemRow->warehouse_id : null;
                            $itemCost = $itemRow ? floatval($itemRow->cost > 0 ? $itemRow->cost : ($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00)) : 0.00;
                            if ($diff > 0) {
                                $ledger->logMovement($itemId, null, 0, $diff, 'Sales Invoice Variance Increase', $line->invoice_number, $whId, $userId, 'Variance Audit Adjust', $itemCost);
                            } else {
                                $ledger->logMovement($itemId, null, abs($diff), 0, 'Sales Invoice Variance Decrease', $line->invoice_number, $whId, $userId, 'Variance Audit Adjust', $itemCost);
                            }
                        }

                        if (!in_array($invoiceId, $modifiedInvoices)) {
                            $modifiedInvoices[] = $invoiceId;
                        }
                    }
                }
            }

            foreach ($modifiedInvoices as $invId) {
                $db->query("SELECT SUM(total) as subtotal FROM invoice_items WHERE invoice_id = :id");
                $db->bind(':id', $invId);
                $subrow = $db->single();
                $subtotal = $subrow ? floatval($subrow->subtotal) : 0.0;

                $db->query("SELECT total_amount, global_discount_val, global_discount_type, tax_rate_id, tax_amount, journal_entry_id FROM invoices WHERE id = :id");
                $db->bind(':id', $invId);
                $invRow = $db->single();

                if ($invRow) {
                    $oldSub = floatval($invRow->total_amount);
                    $oldDiscVal = floatval($invRow->global_discount_val);
                    $oldDiscType = $invRow->global_discount_type;
                    $oldDisc = ($oldDiscType === '%') ? ($oldSub * $oldDiscVal / 100) : $oldDiscVal;
                    $oldGrand = ($oldSub - $oldDisc) + floatval($invRow->tax_amount);

                    $disc = ($oldDiscType === '%') ? ($subtotal * $oldDiscVal / 100) : $oldDiscVal;
                    $taxVal = 0.0;

                    if ($invRow->tax_rate_id) {
                        $db->query("SELECT rate_percentage FROM tax_rates WHERE id = :tid");
                        $db->bind(':tid', $invRow->tax_rate_id);
                        $taxRateRow = $db->single();
                        if ($taxRateRow) {
                            $taxVal = ($subtotal - $disc) * floatval($taxRateRow->rate_percentage) / 100;
                        }
                    }

                    $grandTotal = ($subtotal - $disc) + $taxVal;

                    $db->query("UPDATE invoices SET total_amount = :sub, tax_amount = :tax WHERE id = :id");
                    $db->bind(':sub', $subtotal);
                    $db->bind(':tax', $taxVal);
                    $db->bind(':id', $invId);
                    $db->execute();

                    $jid = $invRow->journal_entry_id;
                    if ($jid) {
                        if ($arAccId) {
                            $db->query("UPDATE transactions SET debit = :grand WHERE journal_entry_id = :jid AND account_id = :aid AND debit > 0");
                            $db->bind(':grand', $grandTotal);
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $arAccId);
                            $db->execute();
                        }
                        if ($revAccId) {
                            $db->query("UPDATE transactions SET credit = :grand WHERE journal_entry_id = :jid AND account_id = :aid AND credit > 0");
                            $db->bind(':grand', $grandTotal);
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $revAccId);
                            $db->execute();
                        }

                        $diffGrand = $grandTotal - $oldGrand;
                        if ($diffGrand !== 0.0) {
                            if ($arAccId) {
                                $db->query("UPDATE chart_of_accounts SET balance = balance + :diff WHERE id = :id");
                                $db->bind(':diff', $diffGrand);
                                $db->bind(':id', $arAccId);
                                $db->execute();
                            }
                            if ($revAccId) {
                                $db->query("UPDATE chart_of_accounts SET balance = balance + :diff WHERE id = :id");
                                $db->bind(':diff', $diffGrand);
                                $db->bind(':id', $revAccId);
                                $db->execute();
                            }
                        }
                    }
                }
            }

            $db->query("UPDATE rep_daily_routes SET status = 'Finalizing' WHERE id = :rid");
            $db->bind(':rid', $routeId);
            $db->execute();

            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Variances reconciled and bills adjusted successfully!']);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

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

    public function api_delete_sales_order($invoiceId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        try {
            $db = new Database();
            $db->beginTransaction();
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

            // Validate Route Name uniqueness
            $db->query("SELECT id FROM rep_daily_routes WHERE route_name = :name LIMIT 1");
            $db->bind(':name', $bindingName);
            if ($db->single()) {
                throw new Exception("The route name '{$bindingName}' is already taken. Please enter a unique route name.");
            }

            // Fetch original routes details
            $routeIdsList = implode(',', array_map('intval', $routeIds));
            $db->query("SELECT id, user_id, route_name, start_meter, start_time, status, route_binding_id, bound_to_route_id FROM rep_daily_routes WHERE id IN ($routeIdsList)");
            $originalRoutes = $db->resultSet() ?: [];
            if (count($originalRoutes) < 2) {
                throw new Exception("Selected routes not found in database.");
            }

            // Fetch associated records to create snapshot
            // Invoices
            $db->query("SELECT id, rep_route_id FROM invoices WHERE rep_route_id IN ($routeIdsList)");
            $originalInvoices = $db->resultSet() ?: [];

            // Deliveries
            $db->query("SELECT id, rep_route_id, secondary_rep_route_id FROM deliveries WHERE rep_route_id IN ($routeIdsList) OR secondary_rep_route_id IN ($routeIdsList)");
            $originalDeliveries = $db->resultSet() ?: [];

            // Cheques
            $db->query("SELECT id, rep_route_id FROM cheques WHERE rep_route_id IN ($routeIdsList)");
            $originalCheques = $db->resultSet() ?: [];

            // Customer Payments
            $db->query("SELECT id, rep_route_id FROM customer_payments WHERE rep_route_id IN ($routeIdsList)");
            $originalPayments = $db->resultSet() ?: [];

            // Pending Collections
            $db->query("SELECT id, route_id FROM pending_collections WHERE route_id IN ($routeIdsList)");
            $originalCollections = $db->resultSet() ?: [];

            // Create JSON snapshot
            $snapshot = [
                'original_routes' => array_map(function($r) { return (array)$r; }, $originalRoutes),
                'invoices' => array_map(function($i) { return (array)$i; }, $originalInvoices),
                'deliveries' => array_map(function($d) { return (array)$d; }, $originalDeliveries),
                'cheques' => array_map(function($c) { return (array)$c; }, $originalCheques),
                'customer_payments' => array_map(function($p) { return (array)$p; }, $originalPayments),
                'pending_collections' => array_map(function($col) { return (array)$col; }, $originalCollections)
            ];
            $snapshotJson = json_encode($snapshot);

            // Insert into route_bindings
            $db->query("INSERT INTO route_bindings (name, created_by, snapshot) VALUES (:name, :created_by, :snapshot)");
            $db->bind(':name', $bindingName);
            $db->bind(':created_by', $_SESSION['user_id'] ?? null);
            $db->bind(':snapshot', $snapshotJson);
            $db->execute();
            $bindingId = $db->lastInsertId();

            // Create new combined route in rep_daily_routes
            // Copy rep, start odo, and start time from the first selected route
            $firstRoute = $originalRoutes[0];
            $db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, status, route_binding_id, is_merged_route) 
                        VALUES (:user_id, :route_name, :start_meter, :start_time, 'Adjustments', :route_binding_id, 1)");
            $db->bind(':user_id', $firstRoute->user_id);
            $db->bind(':route_name', $bindingName);
            $db->bind(':start_meter', $firstRoute->start_meter);
            $db->bind(':start_time', $firstRoute->start_time);
            $db->bind(':route_binding_id', $bindingId);
            $db->execute();
            $newRouteId = $db->lastInsertId();

            // Mark source routes as Bound and link to the new route
            $db->query("UPDATE rep_daily_routes SET status = 'Bound', bound_to_route_id = :new_route_id, route_binding_id = :binding_id WHERE id IN ($routeIdsList)");
            $db->bind(':new_route_id', $newRouteId);
            $db->bind(':binding_id', $bindingId);
            $db->execute();

            // Move invoices
            if (!empty($originalInvoices)) {
                $db->query("UPDATE invoices SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Move deliveries
            if (!empty($originalDeliveries)) {
                $db->query("UPDATE deliveries SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();

                $db->query("UPDATE deliveries SET secondary_rep_route_id = NULL WHERE secondary_rep_route_id IN ($routeIdsList)");
                $db->execute();
            }

            // Move cheques
            if (!empty($originalCheques)) {
                $db->query("UPDATE cheques SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Move customer payments
            if (!empty($originalPayments)) {
                $db->query("UPDATE customer_payments SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Move pending collections
            if (!empty($originalCollections)) {
                $db->query("UPDATE pending_collections SET route_id = :new_route_id WHERE route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Write Audit Log
            $routesDescription = implode(', ', array_map(function($r) { return "{$r->route_name} (#{$r->id})"; }, $originalRoutes));
            $auditDesc = "Bound routes [{$routesDescription}] into combined route '{$bindingName}' (#{$newRouteId})";
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $db->query("INSERT INTO audit_logs (user_id, action, module, description, reference_id, ip_address) 
                        VALUES (:uid, 'ROUTE_BIND', 'Logistics', :desc, :ref, :ip)");
            $db->bind(':uid', $_SESSION['user_id'] ?? null);
            $db->bind(':desc', $auditDesc);
            $db->bind(':ref', $newRouteId);
            $db->bind(':ip', $ip);
            $db->execute();

            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Routes successfully bound under "' . $bindingName . '"!']);
            exit;
        } catch (Exception $e) {
            if (isset($db)) { $db->rollBack(); }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function api_unbind_route() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
        $payload = json_decode(file_get_contents('php://input'), true);
        $bindingId = intval($payload['binding_id'] ?? 0);
        $routeId = intval($payload['route_id'] ?? 0);
        
        try {
            $db = new Database();
            $db->beginTransaction();

            if ($routeId > 0 && $bindingId === 0) {
                $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
                $db->bind(':rid', $routeId);
                $row = $db->single();
                if ($row) {
                    $bindingId = intval($row->route_binding_id);
                }
            }

            if (empty($bindingId)) {
                throw new Exception("Invalid Binding ID or Route ID provided.");
            }

            // Fetch the route binding record
            $db->query("SELECT * FROM route_bindings WHERE id = :bid LIMIT 1");
            $db->bind(':bid', $bindingId);
            $binding = $db->single();
            if (!$binding) {
                throw new Exception("Route binding record not found.");
            }

            $snapshot = json_decode($binding->snapshot, true);
            if (empty($snapshot)) {
                throw new Exception("Snapshot data is missing or corrupted.");
            }

            // Restore original routes
            foreach ($snapshot['original_routes'] as $orig) {
                $db->query("UPDATE rep_daily_routes SET 
                            route_name = :route_name,
                            status = :status,
                            route_binding_id = :route_binding_id,
                            bound_to_route_id = :bound_to_route_id
                            WHERE id = :id");
                $db->bind(':route_name', $orig['route_name']);
                $db->bind(':status', $orig['status']);
                $db->bind(':route_binding_id', $orig['route_binding_id']);
                $db->bind(':bound_to_route_id', $orig['bound_to_route_id']);
                $db->bind(':id', $orig['id']);
                $db->execute();
            }

            // Restore Invoices
            foreach ($snapshot['invoices'] as $inv) {
                $db->query("UPDATE invoices SET rep_route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $inv['rep_route_id']);
                $db->bind(':id', $inv['id']);
                $db->execute();
            }

            // Restore Deliveries
            foreach ($snapshot['deliveries'] as $del) {
                $db->query("UPDATE deliveries SET rep_route_id = :orig_rid, secondary_rep_route_id = :orig_sec_rid WHERE id = :id");
                $db->bind(':orig_rid', $del['rep_route_id']);
                $db->bind(':orig_sec_rid', $del['secondary_rep_route_id']);
                $db->bind(':id', $del['id']);
                $db->execute();
            }

            // Restore Cheques
            foreach ($snapshot['cheques'] as $chq) {
                $db->query("UPDATE cheques SET rep_route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $chq['rep_route_id']);
                $db->bind(':id', $chq['id']);
                $db->execute();
            }

            // Restore Customer Payments
            foreach ($snapshot['customer_payments'] as $pmt) {
                $db->query("UPDATE customer_payments SET rep_route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $pmt['rep_route_id']);
                $db->bind(':id', $pmt['id']);
                $db->execute();
            }

            // Restore Pending Collections
            foreach ($snapshot['pending_collections'] as $col) {
                $db->query("UPDATE pending_collections SET route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $col['route_id']);
                $db->bind(':id', $col['id']);
                $db->execute();
            }

            // Delete the combined route
            $db->query("DELETE FROM rep_daily_routes WHERE route_binding_id = :bid AND is_merged_route = 1");
            $db->bind(':bid', $bindingId);
            $db->execute();

            // Mark route_binding as undone (audit trail preservation)
            $db->query("UPDATE route_bindings SET undo_by = :undo_by, undo_at = NOW() WHERE id = :bid");
            $db->bind(':undo_by', $_SESSION['user_id'] ?? null);
            $db->bind(':bid', $bindingId);
            $db->execute();

            // Write Audit Log
            $routesDescription = implode(', ', array_map(function($r) { return "{$r['route_name']} (#{$r['id']})"; }, $snapshot['original_routes']));
            $auditDesc = "Undid route binding '{$binding->name}' (#{$bindingId}), restoring constituent routes [{$routesDescription}]";
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $db->query("INSERT INTO audit_logs (user_id, action, module, description, reference_id, ip_address) 
                        VALUES (:uid, 'ROUTE_UNBIND', 'Logistics', :desc, :ref, :ip)");
            $db->bind(':uid', $_SESSION['user_id'] ?? null);
            $db->bind(':desc', $auditDesc);
            $db->bind(':ref', $bindingId);
            $db->bind(':ip', $ip);
            $db->execute();

            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Route binding successfully undone! Routes are now separated.']);
            exit;
        } catch (Exception $e) {
            if (isset($db)) { $db->rollBack(); }
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

    public function api_detach_invoice() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die("Invalid Request"); }
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
}