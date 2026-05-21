<?php
class DriverDashboardController extends DriverController {
    private $routeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/driver/auth/login');
            exit;
        }
        $this->routeModel = $this->model('DriverRoute');
    }

    public function index() {
        $activeDelivery = $this->routeModel->getAssignedDelivery($_SESSION['user_id']);
        
        $shops = [];
        $employees = [];
        $todayCashCollected = 0.0;
        $routeCreditBills = [];

        if ($activeDelivery) {
            if ($activeDelivery->status === 'In Transit') {
                $shops = $this->routeModel->getDeliveryShops($activeDelivery->rep_route_id);
                
                // Fetch today's cash collections
                $db = new Database();
                $db->query("SELECT COALESCE(SUM(amount), 0) as cash_total 
                            FROM customer_payments 
                            WHERE rep_route_id = :rid AND payment_method = 'Cash'");
                $db->bind(':rid', $activeDelivery->rep_route_id);
                $row = $db->single();
                $todayCashCollected = $row ? floatval($row->cash_total) : 0.0;

                // Fetch all previous credit bills for the MCA(s) of this route
                $db->query("
                    SELECT i.*, c.name as customer_name,
                        (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as true_grand_total
                    FROM invoices i
                    JOIN customers c ON i.customer_id = c.id
                    WHERE (
                        c.mca_id IN (
                            SELECT DISTINCT cust.mca_id 
                            FROM invoices inv 
                            JOIN customers cust ON inv.customer_id = cust.id 
                            WHERE inv.rep_route_id = :rid AND cust.mca_id IS NOT NULL
                        )
                        OR
                        c.mca_id = (
                            SELECT m.id 
                            FROM mca_areas m 
                            JOIN rep_daily_routes r ON r.route_name = m.name 
                            WHERE r.id = :rid3
                        )
                        OR
                        i.customer_id IN (
                            SELECT DISTINCT customer_id FROM invoices WHERE rep_route_id = :rid2 AND status != 'Voided'
                        )
                    ) AND i.status = 'Unpaid'
                    ORDER BY c.name ASC, i.invoice_date ASC
                ");
                $db->bind(':rid', $activeDelivery->rep_route_id);
                $db->bind(':rid2', $activeDelivery->rep_route_id);
                $db->bind(':rid3', $activeDelivery->rep_route_id);
                $routeCreditBills = $db->resultSet();
            }
            $employees = $this->routeModel->getActiveEmployees();
        }

        $data = [
            'title' => 'Driver Hub',
            'content_view' => 'dashboard',
            'active_delivery' => $activeDelivery,
            'shops' => $shops,
            'employees' => $employees,
            'today_cash_collected' => $todayCashCollected,
            'route_credit_bills' => $routeCreditBills,
            'success' => $_GET['success'] ?? '',
            'error' => $_GET['error'] ?? ''
        ];
        
        $this->view('layout', $data);
    }

    public function accept() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deliveryId = intval($_POST['delivery_id'] ?? 0);
            if ($deliveryId > 0) {
                $this->routeModel->acceptRoute($deliveryId);
                header('Location: ' . APP_URL . '/driver?success=Delivery route accepted.');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/driver');
    }

    public function start_trip() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deliveryId = intval($_POST['delivery_id'] ?? 0);
            $startMeter = floatval($_POST['start_meter'] ?? 0);
            $driverName = trim($_POST['driver_name'] ?? '');
            $partnerName = trim($_POST['partner_name'] ?? '');

            if ($deliveryId <= 0 || $startMeter <= 0 || empty($driverName)) {
                header('Location: ' . APP_URL . '/driver?error=Invalid odometer reading or driver selection.');
                exit;
            }

            if ($this->routeModel->startTrip($deliveryId, $startMeter, $driverName, $partnerName)) {
                header('Location: ' . APP_URL . '/driver?success=Trip started successfully! Drive safely.');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/driver');
    }

    public function end_trip() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deliveryId = intval($_POST['delivery_id'] ?? 0);
            $endMeter = floatval($_POST['end_meter'] ?? 0);

            if ($deliveryId <= 0 || $endMeter <= 0) {
                header('Location: ' . APP_URL . '/driver?error=Invalid ending odometer reading.');
                exit;
            }

            $delivery = $this->routeModel->getDeliveryById($deliveryId);
            if (!$delivery) {
                header('Location: ' . APP_URL . '/driver?error=Delivery route not found.');
                exit;
            }

            if ($endMeter < floatval($delivery->start_meter)) {
                header('Location: ' . APP_URL . '/driver?error=Ending odometer cannot be less than starting odometer (' . $delivery->start_meter . ').');
                exit;
            }

            $denoms = $_POST['denom'] ?? [];
            $cashDenomJson = json_encode($denoms);

            if ($this->routeModel->endTrip($deliveryId, $endMeter, $cashDenomJson)) {
                header('Location: ' . APP_URL . '/driver/trip_summary/' . $deliveryId);
                exit;
            } else {
                header('Location: ' . APP_URL . '/driver?error=Failed to end trip due to database error.');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/driver');
    }

    public function trip_summary($deliveryId = null) {
        if (!$deliveryId) {
            header('Location: ' . APP_URL . '/driver');
            exit;
        }

        $delivery = $this->routeModel->getDeliveryById($deliveryId);
        if (!$delivery) {
            die("Invalid trip summary ID.");
        }

        // Fetch collections for this specific route session
        $db = new Database();
        $db->query("SELECT payment_method, COUNT(*) as tx_count, COALESCE(SUM(amount), 0) as total_collected 
                    FROM customer_payments 
                    WHERE rep_route_id = :rid
                    GROUP BY payment_method");
        $db->bind(':rid', $delivery->rep_route_id);
        $collections = $db->resultSet();

        $data = [
            'title' => 'Trip Completion Summary',
            'content_view' => 'trip_summary',
            'delivery' => $delivery,
            'collections' => $collections
        ];

        $this->view('layout', $data);
    }

    public function vehicle_stock() {
        $activeDelivery = $this->routeModel->getAssignedDelivery($_SESSION['user_id']);
        $stockItems = [];

        if ($activeDelivery) {
            $db = new Database();
            $db->query("
                SELECT 
                    MAX(ii.item_id) as item_id, 
                    MAX(ii.variation_option_id) as variation_option_id,
                    TRIM(ii.description) as item_name,
                    SUM(ii.loaded_quantity) as loaded_qty,
                    SUM(CASE WHEN i.delivery_status = 'Delivered' THEN ii.quantity ELSE 0 END) as delivered_qty,
                    (SUM(ii.loaded_quantity) - SUM(CASE WHEN i.delivery_status = 'Delivered' THEN ii.quantity ELSE 0 END)) as remaining_qty
                FROM invoice_items ii
                JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.rep_route_id = :rid AND i.status != 'Voided'
                GROUP BY TRIM(ii.description)
                ORDER BY TRIM(ii.description) ASC
            ");
            $db->bind(':rid', $activeDelivery->rep_route_id);
            $stockItems = $db->resultSet();
        }

        $data = [
            'title' => 'Vehicle Stock Balance',
            'content_view' => 'vehicle_stock',
            'active_delivery' => $activeDelivery,
            'stock_items' => $stockItems
        ];
        
        $this->view('layout', $data);
    }
}
