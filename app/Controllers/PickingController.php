<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

class PickingController extends Controller {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Helper to resolve route bindings (reused from Delivery model)
    private function resolveAllBoundRouteIds($rids) {
        if (empty($rids)) return [];
        $rids = array_map('intval', $rids);
        $ridsStr = implode(',', $rids);
        
        $this->db->query("SELECT DISTINCT route_binding_id FROM rep_daily_routes WHERE id IN ($ridsStr) AND route_binding_id IS NOT NULL");
        $bindings = $this->db->resultSet();
        
        if (!empty($bindings)) {
            $bindingIds = [];
            foreach ($bindings as $b) {
                $bindingIds[] = intval($b->route_binding_id);
            }
            $bindingIdsStr = implode(',', $bindingIds);
            
            $this->db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id IN ($bindingIdsStr)");
            $allRoutes = $this->db->resultSet();
            foreach ($allRoutes as $r) {
                $rids[] = intval($r->id);
            }
        }
        return array_unique($rids);
    }

    // Update delivery status based on picking items completion and route status
    private function updateDeliveryStatus($deliveryId) {
        // No-op to comply with the strict rule: "Route status must NOT change during Pre-loading, Final loading, Product picking."
        // All stage transitions are controlled explicitly by the administrator in the Master Route Control Panel.
    }

    // API: Verify login credentials
    public function api_login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Username and password are required']);
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->login($username, $password);

        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        }
        exit;
    }

    // API: Fetch all loading sheets (deliveries) in Pre-Loading and Final Loading stages
    public function api_get_sheets() {
        $this->db->query("
            SELECT d.id, d.delivery_date, d.vehicle_number, d.driver_name, d.status as db_status, 
                   r.route_name, r.id as route_id, d.secondary_rep_route_id,
                   r.status as route_status,
                   (SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id) as total_items,
                   (SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id AND is_picked = 1) as picked_items
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            WHERE r.status = 'Final Loading'
            ORDER BY d.delivery_date DESC, d.id DESC
        ");
        $sheets = $this->db->resultSet() ?: [];

        foreach ($sheets as $sheet) {
            // Retrieve customer names for this delivery's routes
            $rids = [$sheet->route_id];
            if ($sheet->secondary_rep_route_id) {
                $rids[] = $sheet->secondary_rep_route_id;
            }
            $rids = $this->resolveAllBoundRouteIds($rids);
            
            if (!empty($rids)) {
                $ridsStr = implode(',', $rids);
                $this->db->query("
                    SELECT DISTINCT c.name 
                    FROM invoices i 
                    JOIN customers c ON i.customer_id = c.id 
                    WHERE i.rep_route_id IN ($ridsStr) AND i.status != 'Voided'
                ");
                $custs = $this->db->resultSet();
                $custNames = array_map(function($c) { return $c->name; }, $custs);
                $sheet->customer_info = !empty($custNames) ? implode(', ', $custNames) : 'No Customer Invoices';
            } else {
                $sheet->customer_info = 'No Customer Invoices';
            }

            // Determine picking status
            if ($sheet->route_status === 'Final Loading') {
                $sheet->status = 'Final Loading';
            } else {
                if ($sheet->picked_items > 0 && $sheet->picked_items < $sheet->total_items) {
                    $sheet->status = 'In Progress';
                } else if ($sheet->total_items > 0 && $sheet->picked_items == $sheet->total_items) {
                    $sheet->status = 'Ready for Final';
                } else {
                    $sheet->status = 'Pending';
                }
            }
        }

        echo json_encode(['success' => true, 'sheets' => $sheets]);
        exit;
    }

    // API: Fetch single loading sheet detail with items list
    public function api_get_sheet_details($deliveryId) {
        $deliveryId = intval($deliveryId);
        
        $this->db->query("
            SELECT d.*, r.route_name, r.id as route_id, r.status as route_status 
            FROM deliveries d 
            JOIN rep_daily_routes r ON d.rep_route_id = r.id 
            WHERE d.id = :id
        ");
        $this->db->bind(':id', $deliveryId);
        $delivery = $this->db->single();

        if (!$delivery) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Loading sheet not found']);
            exit;
        }

        // Fetch customer names
        $rids = [$delivery->route_id];
        if ($delivery->secondary_rep_route_id) {
            $rids[] = $delivery->secondary_rep_route_id;
        }
        $rids = $this->resolveAllBoundRouteIds($rids);
        $ridsStr = implode(',', $rids);

        $this->db->query("
            SELECT DISTINCT c.name 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            WHERE i.rep_route_id IN ($ridsStr) AND i.status != 'Voided'
        ");
        $custs = $this->db->resultSet();
        $custNames = array_map(function($c) { return $c->name; }, $custs);
        $delivery->customer_info = !empty($custNames) ? implode(', ', $custNames) : 'No Customer Invoices';

        // Check if items are already populated in delivery_picking_items
        $this->db->query("SELECT COUNT(*) as cnt FROM delivery_picking_items WHERE delivery_id = :id");
        $this->db->bind(':id', $deliveryId);
        $check = $this->db->single();

        if (!$check || intval($check->cnt) === 0) {
            // Initialize from aggregated invoices items on the route
            $this->db->query("
                SELECT ii.item_id, ii.variation_option_id, ii.description as item_name, SUM(ii.quantity) as required_qty
                FROM invoice_items ii
                JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.rep_route_id IN ($ridsStr) AND i.status != 'Voided'
                GROUP BY ii.item_id, ii.variation_option_id, ii.description
            ");
            $invoiceItems = $this->db->resultSet();

            foreach ($invoiceItems as $item) {
                $this->db->query("
                    INSERT INTO delivery_picking_items (delivery_id, item_name, item_id, variation_option_id, required_qty, loaded_qty, is_picked)
                    VALUES (:delivery_id, :item_name, :item_id, :variation_option_id, :required_qty, :loaded_qty, 0)
                ");
                $this->db->bind(':delivery_id', $deliveryId);
                $this->db->bind(':item_name', $item->item_name);
                $this->db->bind(':item_id', $item->item_id);
                $this->db->bind(':variation_option_id', $item->variation_option_id);
                $this->db->bind(':required_qty', $item->required_qty);
                $this->db->bind(':loaded_qty', $item->required_qty);
                $this->db->execute();
            }
        }

        // Fetch picking items details with categories and primary images
        $this->db->query("
            SELECT dpi.*, 
                   COALESCE(c.name, 'Uncategorized') as category_name,
                   COALESCE(it.image_path, (SELECT image_path FROM item_images WHERE item_id = it.id AND is_primary = 1 LIMIT 1)) as image_path
            FROM delivery_picking_items dpi
            LEFT JOIN items it ON dpi.item_id = it.id
            LEFT JOIN item_categories c ON it.category_id = c.id
            WHERE dpi.delivery_id = :delivery_id
        ");
        $this->db->bind(':delivery_id', $deliveryId);
        $items = $this->db->resultSet();

        // Convert types appropriately for JSON
        foreach ($items as $item) {
            $item->id = intval($item->id);
            $item->delivery_id = intval($item->delivery_id);
            $item->item_id = $item->item_id ? intval($item->item_id) : null;
            $item->variation_option_id = $item->variation_option_id ? intval($item->variation_option_id) : null;
            $item->required_qty = floatval($item->required_qty);
            $item->loaded_qty = floatval($item->loaded_qty);
            $item->is_picked = intval($item->is_picked);
            $item->final_loaded_qty = $item->final_loaded_qty !== null ? floatval($item->final_loaded_qty) : null;
            $item->is_verified = intval($item->is_verified);
            $item->variance = floatval($item->variance);
            $item->verified_by = $item->verified_by ? intval($item->verified_by) : null;
        }

        echo json_encode([
            'success' => true,
            'delivery' => $delivery,
            'items' => $items
        ]);
        exit;
    }

    // API: Update single item details (loaded_qty, is_picked)
    public function api_update_item() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        $loadedQty = floatval($input['loaded_qty'] ?? 0);
        $isPicked = intval($input['is_picked'] ?? 0);
        $updatedAt = $input['updated_at'] ?? date('Y-m-d H:i:s');

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Item ID is required']);
            exit;
        }

        // Fetch current item to know the delivery ID
        $this->db->query("SELECT delivery_id FROM delivery_picking_items WHERE id = :id");
        $this->db->bind(':id', $id);
        $item = $this->db->single();

        if (!$item) {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            exit;
        }

        // Simple direct overwrite update to ensure data reliability
        $this->db->query("
            UPDATE delivery_picking_items 
            SET loaded_qty = :loaded_qty, is_picked = :is_picked, updated_at = :updated_at 
            WHERE id = :id
        ");
        $this->db->bind(':loaded_qty', $loadedQty);
        $this->db->bind(':is_picked', $isPicked);
        $this->db->bind(':updated_at', $updatedAt);
        $this->db->bind(':id', $id);
        $this->db->execute();

        // Recalculate and update the overall delivery status
        $this->updateDeliveryStatus($item->delivery_id);

        echo json_encode(['success' => true]);
        exit;
    }

    // API: Update single item details for final loading (final_loaded_qty, is_verified, variance)
    public function api_update_final_item() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        $finalLoadedQty = floatval($input['final_loaded_qty'] ?? 0);
        $isVerified = intval($input['is_verified'] ?? 0);
        $userId = intval($input['user_id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Item ID is required']);
            exit;
        }

        // Fetch item details (required_qty) to compute variance
        $this->db->query("SELECT required_qty, delivery_id FROM delivery_picking_items WHERE id = :id");
        $this->db->bind(':id', $id);
        $item = $this->db->single();

        if (!$item) {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            exit;
        }

        // Variance = Final Loaded Quantity - Required Quantity
        $variance = $finalLoadedQty - floatval($item->required_qty);

        $this->db->query("
            UPDATE delivery_picking_items 
            SET final_loaded_qty = :final_loaded_qty, 
                is_verified = :is_verified, 
                variance = :variance, 
                verified_at = NOW(), 
                verified_by = :verified_by
            WHERE id = :id
        ");
        $this->db->bind(':final_loaded_qty', $finalLoadedQty);
        $this->db->bind(':is_verified', $isVerified);
        $this->db->bind(':variance', $variance);
        $this->db->bind(':verified_by', $userId ? $userId : null);
        $this->db->bind(':id', $id);
        $this->db->execute();

        // Recalculate and update the overall delivery status
        $this->updateDeliveryStatus($item->delivery_id);

        echo json_encode(['success' => true]);
        exit;
    }

    // API: Bulk Sync updates from offline PWA
    public function api_sync() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $updates = $input['updates'] ?? [];

        if (empty($updates) || !is_array($updates)) {
            echo json_encode(['success' => true, 'message' => 'No updates to sync']);
            exit;
        }

        $affectedDeliveries = [];

        foreach ($updates as $upd) {
            $id = intval($upd['id'] ?? 0);
            if (!$id) continue;

            // Find current details
            $this->db->query("SELECT required_qty, delivery_id FROM delivery_picking_items WHERE id = :id");
            $this->db->bind(':id', $id);
            $item = $this->db->single();
            if ($item) {
                $affectedDeliveries[] = intval($item->delivery_id);
            } else {
                continue;
            }

            if (isset($upd['final_loaded_qty'])) {
                // Final Loading Update
                $finalLoadedQty = floatval($upd['final_loaded_qty']);
                $isVerified = intval($upd['is_verified'] ?? 0);
                $userId = intval($upd['user_id'] ?? 0);
                $variance = $finalLoadedQty - floatval($item->required_qty);

                $this->db->query("
                    UPDATE delivery_picking_items 
                    SET final_loaded_qty = :final_loaded_qty, 
                        is_verified = :is_verified, 
                        variance = :variance, 
                        verified_at = NOW(), 
                        verified_by = :verified_by
                    WHERE id = :id
                ");
                $this->db->bind(':final_loaded_qty', $finalLoadedQty);
                $this->db->bind(':is_verified', $isVerified);
                $this->db->bind(':variance', $variance);
                $this->db->bind(':verified_by', $userId ? $userId : null);
                $this->db->bind(':id', $id);
                $this->db->execute();
            } else {
                // Pre-Loading Update (Warehouse Pick)
                $loadedQty = floatval($upd['loaded_qty'] ?? 0);
                $isPicked = intval($upd['is_picked'] ?? 0);
                $updatedAt = $upd['updated_at'] ?? date('Y-m-d H:i:s');

                $this->db->query("
                    UPDATE delivery_picking_items 
                    SET loaded_qty = :loaded_qty, is_picked = :is_picked, updated_at = :updated_at 
                    WHERE id = :id
                ");
                $this->db->bind(':loaded_qty', $loadedQty);
                $this->db->bind(':is_picked', $isPicked);
                $this->db->bind(':updated_at', $updatedAt);
                $this->db->bind(':id', $id);
                $this->db->execute();
            }
        }

        // Update overall statuses for all affected loading sheets
        $affectedDeliveries = array_unique($affectedDeliveries);
        foreach ($affectedDeliveries as $delId) {
            $this->updateDeliveryStatus($delId);
        }

        echo json_encode(['success' => true, 'synced_count' => count($updates)]);
        exit;
    }
}
