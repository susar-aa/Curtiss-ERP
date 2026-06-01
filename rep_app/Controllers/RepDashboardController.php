<?php
class RepDashboardController extends RepController {
    private $routeModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) && !isset($_REQUEST['api_sync']) && !strpos($_SERVER['REQUEST_URI'] ?? '', 'api_login')) { 
            header('Location: ' . APP_URL . '/auth/login'); 
            exit; 
        }
        $this->routeModel = $this->model('RepRoute');
    }

    public function index() {
        error_log("--- Rep App: index() Loaded ---");
        
        // ROUTER FIX: Catch POST requests that land on index due to the URL structure
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            error_log("--- Rep App: POST request intercepted in index() ---");
            error_log("POST DATA: " . print_r($_POST, true));
            
            if (isset($_POST['start_meter'])) {
                return $this->start_trip();
            }
        }

        $activeRoute = $this->routeModel->getActiveRoute($_SESSION['user_id']);
        
        // NEW: Fetch live route stats
        $routeStats = null;
        if ($activeRoute) {
            $routeStats = $this->routeModel->getRouteStats($activeRoute->id);
        }

        $data = [
            'title' => 'Territory Hub',
            'content_view' => 'dashboard',
            'active_route' => $activeRoute,
            'route_stats' => $routeStats,
            'success' => $_GET['success'] ?? ''
        ];
        $this->view('layout', $data);
    }

    public function start_route() {
        error_log("--- Rep App: start_route() UI Loaded ---");
        
        $activeRoute = $this->routeModel->getActiveRoute($_SESSION['user_id']);
        if ($activeRoute) { header('Location: ' . APP_URL . '/rep'); exit; } 

        $data = [
            'title' => 'Start Your Day',
            'content_view' => 'start_route',
            'routes' => $this->routeModel->getMcaRoutes(),
            'error' => $_GET['error'] ?? ''
        ];
        $this->view('layout', $data);
    }

    public function start_trip() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $routeName = trim($_POST['route_name']);
            $startMeter = floatval($_POST['start_meter']);
            $lat = $_POST['start_lat'] ?? null;
            $lng = $_POST['start_lng'] ?? null;

            if (empty($routeName) || $startMeter <= 0) {
                header('Location: ' . APP_URL . '/rep/start_route?error=Invalid Route or Odometer Reading.');
                exit;
            }

            if ($this->routeModel->startRoute($_SESSION['user_id'], $routeName, $startMeter, $lat, $lng)) {
                header('Location: ' . APP_URL . '/rep/dashboard?success=Route Started Successfully!');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/rep');
    }

    // NEW: Display the End Route input form
    public function end_route() {
        $activeRoute = $this->routeModel->getActiveRoute($_SESSION['user_id']);
        if (!$activeRoute) { header('Location: ' . APP_URL . '/rep'); exit; }
        
        $data = [
            'title' => 'End Daily Route',
            'content_view' => 'end_route',
            'active_route' => $activeRoute,
            'error' => $_GET['error'] ?? ''
        ];
        $this->view('layout', $data);
    }

    // NEW: Process the End Route submission
    public function process_end_route() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $endMeter = floatval($_POST['end_meter']);
            $lat = $_POST['end_lat'] ?? null;
            $lng = $_POST['end_lng'] ?? null;
            
            $activeRoute = $this->routeModel->getActiveRoute($_SESSION['user_id']);
            if ($activeRoute) {
                if ($endMeter < $activeRoute->start_meter) {
                    header('Location: ' . APP_URL . '/rep/end_route?error=Ending Odometer cannot be less than Starting Odometer.');
                    exit;
                }
                $this->routeModel->endRoute($activeRoute->id, $endMeter, $lat, $lng);
                header('Location: ' . APP_URL . '/rep/route_summary/' . $activeRoute->id);
                exit;
            }
        }
        header('Location: ' . APP_URL . '/rep');
    }

    // NEW: Display the final Route Summary Report
    public function route_summary($routeId = null) {
        if (!$routeId) { header('Location: ' . APP_URL . '/rep/history'); exit; }
        
        $summary = $this->routeModel->getRouteSummaryData($routeId);
        
        // Ensure user can only view their own routes
        if (!$summary || $summary['route']->user_id != $_SESSION['user_id']) { 
            die("Unauthorized or Invalid Route"); 
        }
        
        // NEW: Fetch pending GL collections for this route
        $pendingGL = $this->routeModel->getPendingGLForRoute($routeId);
        $summary['pending_gl'] = $pendingGL ?: [];
        
        $data = [
            'title' => 'Route Summary',
            'content_view' => 'route_summary',
            'summary' => $summary
        ];
        
        $this->view('layout', $data);
    }

    // Realtime Mobile representative authentication
    public function api_login() {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);
        
        $username = $payload['username'] ?? '';
        $password = $payload['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please provide username and password.']);
            exit;
        }
        
        $db = new Database();
        $db->query("SELECT u.id, u.username, u.password_hash, u.employee_id, u.role, e.first_name, e.last_name 
                    FROM users u 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE LOWER(u.username) = :username LIMIT 1");
        $db->bind(':username', strtolower($username));
        $user = $db->single();
        
        if ($user) {
            if (strtolower($user->role) !== 'rep') {
                echo json_encode(['success' => false, 'message' => 'Access Denied: Only representatives can log in.']);
                exit;
            }
            
            if (password_verify($password, $user->password_hash)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Authentication successful!',
                    'user' => [
                        'id' => intval($user->id),
                        'username' => $user->username,
                        'employee_id' => intval($user->employee_id),
                        'first_name' => $user->first_name ?: 'Rep',
                        'last_name' => $user->last_name ?: 'User'
                    ]
                ]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    // Pull Categories, Products, Customers, and Territories
    public function sync_pull() {
        header('Content-Type: application/json');
        
        $catalogModel = $this->model('RepCatalog');
        $customerModel = $this->model('RepCustomer');
        $routeModel = $this->model('RepRoute');

        $products = $catalogModel->getVisualCatalog();
        $customers = $customerModel->getAllCustomers();
        $routes = $routeModel->getMcaRoutes();

        // Fetch representatives linked to employees
        $db = new Database();
        $db->query("SELECT u.id, u.username, u.password_hash, u.employee_id, e.first_name, e.last_name 
                    FROM users u 
                    JOIN employees e ON u.employee_id = e.id 
                    WHERE LOWER(u.role) = 'rep'");
        $reps = $db->resultSet();

        // Fetch active payment terms
        $db->query("SELECT id, name, days_due FROM payment_terms WHERE is_inactive = 0 ORDER BY days_due ASC");
        $terms = $db->resultSet() ?: [];

        // Fetch categories directly from item_categories table
        $db->query("SELECT id, name FROM item_categories ORDER BY name ASC");
        $categories = $db->resultSet() ?: [];

        // Fetch outstanding credit balances per customer with accurate math (Total Billed - Total Paid - Total Credited)
        $db->query("
            SELECT c.id, 'Arrears' as invoice_number, c.id as customer_id, '' as invoice_date, 'Unpaid' as status,
                   (COALESCE(
                       (SELECT SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) FROM invoices i WHERE i.customer_id = c.id AND i.status != 'Voided'), 0
                   ) - COALESCE(
                       (SELECT SUM(p.amount) FROM customer_payments p WHERE p.customer_id = c.id), 0
                   ) - COALESCE(
                       (SELECT SUM(cn.total_amount) FROM credit_notes cn WHERE cn.customer_id = c.id), 0
                   )) as true_grand_total,
                   c.name as customer_name, c.address as customer_address
            FROM customers c
            HAVING true_grand_total > 0.01
            ORDER BY c.name ASC
        ");
        $creditInvoices = $db->resultSet() ?: [];

        echo json_encode([
            'success' => true,
            'products' => $products,
            'customers' => $customers,
            'routes' => $routes,
            'reps' => $reps,
            'payment_terms' => $terms,
            'categories' => $categories,
            'credit_invoices' => $creditInvoices
        ]);
        exit;
    }

    // Push Offline-Billed Invoices, Newly Geo-Tagged Shops, and Completed Routes
    public function sync_push() {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);
        
        if (!$payload) {
            echo json_encode(['success' => false, 'message' => 'Empty or invalid JSON payload']);
            exit;
        }

        $db = new Database();
        $userId = $payload['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Missing representative user_id']);
            exit;
        }

        // Ensure pending_collections table exists (failsafe for fresh deployments - DDL placed before beginTransaction to avoid implicit commit in MySQL)
        try {
            $createSql = "CREATE TABLE IF NOT EXISTS pending_collections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                route_id INT DEFAULT NULL,
                payment_method VARCHAR(50) DEFAULT 'Cash',
                amount DECIMAL(12,2) DEFAULT 0.00,
                bank_name VARCHAR(255) DEFAULT '',
                cheque_number VARCHAR(255) DEFAULT '',
                cheque_date DATE DEFAULT NULL,
                status VARCHAR(20) DEFAULT 'Pending',
                created_by INT DEFAULT NULL,
                finalized_by INT DEFAULT NULL,
                finalized_at DATETIME DEFAULT NULL,
                rejected_by INT DEFAULT NULL,
                rejected_at DATETIME DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                latitude DECIMAL(10,8) DEFAULT NULL,
                longitude DECIMAL(11,8) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->query($createSql);
            $db->execute();

            // Self-healing columns check for latitude and longitude
            try {
                $db->query("ALTER TABLE pending_collections ADD COLUMN latitude DECIMAL(10,8) DEFAULT NULL");
                $db->execute();
            } catch (\Exception $e) {}
            try {
                $db->query("ALTER TABLE pending_collections ADD COLUMN longitude DECIMAL(11,8) DEFAULT NULL");
                $db->execute();
            } catch (\Exception $e) {}
        } catch (Exception $e) {
            error_log("Failed to ensure pending_collections table exists: " . $e->getMessage());
        }

        $responseMappings = [
            'customers' => [],
            'routes' => [],
            'invoices' => []
        ];

        try {
            $db->beginTransaction();

            // 1. Process Offline Added Customers
            if (!empty($payload['customers']) && is_array($payload['customers'])) {
                foreach ($payload['customers'] as $cust) {
                    $localId = $cust['local_id'] ?? 0;
                    
                    $db->query("INSERT INTO customers (name, phone, whatsapp, address, territory, latitude, longitude) 
                                VALUES (:name, :phone, :whatsapp, :address, :territory, :lat, :lng)");
                    $db->bind(':name', $cust['name']);
                    $db->bind(':phone', $cust['phone'] ?: null);
                    $db->bind(':whatsapp', $cust['whatsapp'] ?: null);
                    $db->bind(':address', $cust['address'] ?: null);
                    $db->bind(':territory', $cust['territory'] ?: null);
                    $db->bind(':lat', $cust['latitude'] ?: null);
                    $db->bind(':lng', $cust['longitude'] ?: null);
                    $db->execute();
                    
                    $serverId = $db->lastInsertId();
                    $responseMappings['customers'][] = [
                        'local_id' => $localId,
                        'server_id' => $serverId
                    ];
                }
            }

            // 2. Process Offline Route Actions
            if (!empty($payload['routes']) && is_array($payload['routes'])) {
                foreach ($payload['routes'] as $route) {
                    $localId = $route['local_id'] ?? 0;
                    $status = $route['status'] ?? 'Completed';
                    
                    if ($status === 'Active') {
                        $db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, start_lat, start_lng, status) 
                                    VALUES (:uid, :rname, :meter, :stime, :lat, :lng, 'Active')");
                        $db->bind(':uid', $userId);
                        $db->bind(':rname', $route['route_name']);
                        $db->bind(':meter', $route['start_meter']);
                        $db->bind(':stime', $route['start_time']);
                        $db->bind(':lat', $route['start_lat']);
                        $db->bind(':lng', $route['start_lng']);
                        $db->execute();
                        $serverId = $db->lastInsertId();
                    } else {
                        // Ending an existing active route or direct sync
                        $db->query("SELECT id FROM rep_daily_routes WHERE user_id = :uid AND status = 'Active' ORDER BY id DESC LIMIT 1");
                        $db->bind(':uid', $userId);
                        $activeRoute = $db->single();
                        $routeId = $activeRoute ? $activeRoute->id : null;

                        if ($routeId) {
                            $db->query("UPDATE rep_daily_routes SET end_meter = :meter, end_time = :etime, end_lat = :lat, end_lng = :lng, status = 'Completed' WHERE id = :id");
                            $db->bind(':id', $routeId);
                            $db->bind(':meter', $route['end_meter']);
                            $db->bind(':etime', $route['end_time']);
                            $db->bind(':lat', $route['end_lat']);
                            $db->bind(':lng', $route['end_lng']);
                            $db->execute();
                            $serverId = $routeId;
                        } else {
                            // Insert a fully completed historic route directly
                            $db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, start_lat, start_lng, end_meter, end_time, end_lat, end_lng, status) 
                                        VALUES (:uid, :rname, :smeter, :stime, :slat, :slng, :emeter, :etime, :elat, :elng, 'Completed')");
                            $db->bind(':uid', $userId);
                            $db->bind(':rname', $route['route_name']);
                            $db->bind(':smeter', $route['start_meter']);
                            $db->bind(':stime', $route['start_time']);
                            $db->bind(':slat', $route['start_lat']);
                            $db->bind(':slng', $route['start_lng']);
                            $db->bind(':emeter', $route['end_meter']);
                            $db->bind(':etime', $route['end_time']);
                            $db->bind(':elat', $route['end_lat']);
                            $db->bind(':elng', $route['end_lng']);
                            $db->execute();
                            $serverId = $db->lastInsertId();
                        }
                    }
                    $responseMappings['routes'][] = [
                        'local_id' => $localId,
                        'server_id' => $serverId
                    ];
                }
            }

            // 3. Process Offline Billed Invoices (Completing Transactions + Inventory Double-Entry)
            if (!empty($payload['invoices']) && is_array($payload['invoices'])) {
                foreach ($payload['invoices'] as $inv) {
                    $localId = $inv['local_id'] ?? 0;
                    $customerId = $inv['customer_id'];
                    $paymentMethod = $inv['payment_method'];
                    
                    // Look up mapping if this customer was created during this same offline window
                    foreach ($responseMappings['customers'] as $map) {
                        if ($map['local_id'] == $customerId) {
                            $customerId = $map['server_id'];
                            break;
                        }
                    }

                    // Resolve active route
                    $db->query("SELECT id FROM rep_daily_routes WHERE user_id = :uid AND status = 'Active' ORDER BY id DESC LIMIT 1");
                    $db->bind(':uid', $userId);
                    $activeRoute = $db->single();
                    $routeId = $activeRoute ? $activeRoute->id : null;

                    // Compute Account IDs from Chart of Accounts
                    $db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1000', '1010', '1600', '4000', '1200')");
                    $accounts = $db->resultSet();
                    $accMap = [];
                    foreach ($accounts as $a) { $accMap[$a->account_code] = $a->id; }

                    $cashAcc = $accMap['1000'] ?? null;
                    $chequeAcc = $accMap['1010'] ?? null;
                    $bankAcc = $accMap['1600'] ?? null;
                    $salesAcc = $accMap['4000'] ?? null;
                    $arAcc = $accMap['1200'] ?? null;

                    // Create Journal Entry
                    $invoiceNumber = $inv['invoice_number'];
                    $db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                VALUES (:edate, :ref, :desc, :uid, 'Posted')");
                    $db->bind(':edate', $inv['invoice_date']);
                    $db->bind(':ref', $invoiceNumber);
                    $db->bind(':desc', "Offline Native POS Sale: " . $invoiceNumber);
                    $db->bind(':uid', $userId);
                    $db->execute();
                    $saleJournalId = $db->lastInsertId();

                    // Ledger Double Entry: Debit Accounts Receivable (1200), Credit Sales (4000)
                    $grandTotal = floatval($inv['grand_total']);
                    $db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                    $db->bind(':jid', $saleJournalId);
                    $db->bind(':aid', $arAcc);
                    $db->bind(':deb', $grandTotal);
                    $db->execute();
                    $db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                    $db->bind(':amt', $grandTotal);
                    $db->bind(':aid', $arAcc);
                    $db->execute();

                    $db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, 0, :cred)");
                    $db->bind(':jid', $saleJournalId);
                    $db->bind(':aid', $salesAcc);
                    $db->bind(':cred', $grandTotal);
                    $db->execute();
                    $db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                    $db->bind(':amt', $grandTotal);
                    $db->bind(':aid', $salesAcc);
                    $db->execute();

                    // Insert Invoice Record
                    $db->query("INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, payment_term_id, total_amount, global_discount_val, global_discount_type, journal_entry_id, created_by, rep_route_id, latitude, longitude, stock_status) 
                                VALUES (:inv, :cid, :idate, :ddate, :payment_term_id, :total, :gdisc_val, 'Rs', :jid, :uid, :route, :lat, :lng, 'reserved')");
                    $db->bind(':inv', $invoiceNumber);
                    $db->bind(':cid', $customerId);
                    $db->bind(':idate', $inv['invoice_date']);
                    $db->bind(':ddate', $inv['due_date']);
                    $db->bind(':payment_term_id', $inv['payment_term_id'] ?? null);
                    $db->bind(':total', $inv['subtotal']);
                    $db->bind(':gdisc_val', $inv['discount'] ?? 0.0);
                    $db->bind(':jid', $saleJournalId);
                    $db->bind(':uid', $userId);
                    $db->bind(':route', $routeId);
                    $db->bind(':lat', $inv['latitude'] ?: null);
                    $db->bind(':lng', $inv['longitude'] ?: null);
                    $db->execute();
                    $invoiceServerId = $db->lastInsertId();

                    // Insert Invoice Items and Reserve Inventory
                    if (!empty($inv['items']) && is_array($inv['items'])) {
                        foreach ($inv['items'] as $item) {
                            $db->query("INSERT INTO invoice_items (invoice_id, item_id, description, quantity, loaded_quantity, unit_price, discount_value, discount_type, total) 
                                        VALUES (:iid, :item_id, :desc, :qty, :qty, :price, :dval, 'Rs', :tot)");
                            $db->bind(':iid', $invoiceServerId);
                            $db->bind(':item_id', $item['product_id']);
                            $db->bind(':desc', $item['product_name']);
                            $db->bind(':qty', $item['quantity']);
                            $db->bind(':price', $item['unit_price']);
                            $db->bind(':dval', $item['discount_val'] ?? 0.0);
                            $db->bind(':tot', $item['total']);
                            $db->execute();

                            // Reserve physical stock on DB items
                            $db->query("UPDATE items SET quantity_reserved = quantity_reserved + :qty WHERE id = :id");
                            $db->bind(':qty', $item['quantity']);
                            $db->bind(':id', $item['product_id']);
                            $db->execute();
                        }
                    }

                    // Note: Payments and collections are deferred until final delivery confirmation, so we do not insert a local payment transaction here.

                    $responseMappings['invoices'][] = [
                        'local_id' => $localId,
                        'server_id' => $invoiceServerId
                    ];
                }
            }

            // 4. Process Temporary Collections (Pending GL) from mobile offline cache
            if (!empty($payload['payments']) && is_array($payload['payments'])) {
                foreach ($payload['payments'] as $payment) {
                    $customerId = $payment['customer_id'] ?? null;
                    $routeId = $payment['route_id'] ?? $payment['server_route_id'] ?? null;
                    $method = $payment['payment_method'] ?? 'Unknown';
                    $amount = floatval($payment['amount'] ?? 0);
                    $bankName = $payment['bank_name'] ?? null;
                    $chequeNum = $payment['cheque_number'] ?? null;
                    $chequeDate = $payment['cheque_date'] ?? null;

                    if ($bankName === '') $bankName = null;
                    if ($chequeNum === '') $chequeNum = null;
                    if ($chequeDate === '' || $chequeDate === 'null') $chequeDate = null;

                    if ($customerId > 0 && $amount > 0) {
                        // Store pending collection for later GL finalization
                        $db->query("INSERT INTO pending_collections (customer_id, route_id, payment_method, amount, bank_name, cheque_number, cheque_date, created_by, status, latitude, longitude) 
                                    VALUES (:cid, :rid, :method, :amt, :bank, :chqnum, :chqdate, :uid, 'Pending', :lat, :lng)");
                        $db->bind(':cid', $customerId);
                        $db->bind(':rid', $routeId ?: null);
                        $db->bind(':method', $method);
                        $db->bind(':amt', $amount);
                        $db->bind(':bank', $bankName);
                        $db->bind(':chqnum', $chequeNum);
                        $db->bind(':chqdate', $chequeDate);
                        $db->bind(':uid', $userId);
                        $db->bind(':lat', $payment['latitude'] ?? null);
                        $db->bind(':lng', $payment['longitude'] ?? null);
                        $db->execute();
                    }
                }
            }

            $db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Sync push processed successfully',
                'mappings' => $responseMappings
            ]);
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Sync push database transaction failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}