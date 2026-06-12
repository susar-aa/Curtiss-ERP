<?php
class RepDashboardController extends Controller {
    private $userModel;
    private $itemModel;
    private $customerModel;
    private $trackingModel;
    private $discountModel;
    private $invoiceModel;
    private $db;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->itemModel = $this->model('Item');
        $this->customerModel = $this->model('Customer');
        $this->trackingModel = $this->model('RepTracking');
        $this->discountModel = $this->model('DiscountRule');
        $this->invoiceModel = $this->model('Invoice');
        $this->db = new Database();
    }

    public function api_login() {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Missing username or password.']);
            exit;
        }
        
        $username = trim($data['username']);
        $password = trim($data['password']);
        
        $user = $this->userModel->login($username, $password);
        if ($user) {
            if (strtolower($user->role) !== 'rep') {
                echo json_encode(['success' => false, 'message' => 'Access denied: not a representative account.']);
                exit;
            }
            
            // Get employee first and last name if possible
            $this->db->query("SELECT first_name, last_name FROM employees WHERE id = :id");
            $this->db->bind(':id', $user->employee_id);
            $emp = $this->db->single();
            
            $firstName = $emp ? $emp->first_name : $user->username;
            $lastName = $emp ? $emp->last_name : '';
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => intval($user->id),
                    'employee_id' => intval($user->employee_id),
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            exit;
        }
    }

    public function sync_pull() {
        header('Content-Type: application/json');
        try {
            $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
            exit;
        }
        
        // 1. Get products (items)
        $items = $this->itemModel->getItems();
        $productsJson = [];
        foreach ($items as $item) {
            $wholesalePrice = floatval($item->wholesale_price ?? 0);
            if ($wholesalePrice <= 0) {
                $wholesalePrice = floatval($item->selling_price ?? 0);
            }
            $productsJson[] = [
                'id' => intval($item->id),
                'name' => $item->name,
                'category_name' => $item->category_name ?? 'General',
                'selling_price' => $wholesalePrice,
                'wholesale_price' => $wholesalePrice,
                'quantity_on_hand' => intval($item->qty ?? $item->quantity_on_hand ?? 0),
                'quantity_reserved' => intval($item->quantity_reserved),
                'image_path' => $item->image_path ?? ''
            ];
        }
        
        // 2. Get categories
        $this->db->query("SELECT id, name FROM item_categories ORDER BY name ASC");
        $cats = $this->db->resultSet() ?: [];
        $categoriesJson = [];
        foreach ($cats as $cat) {
            $categoriesJson[] = [
                'id' => intval($cat->id),
                'name' => $cat->name
            ];
        }
        
        // 3. Get customers
        $this->db->query("
            SELECT c.id, c.name, c.phone, c.whatsapp, c.address, c.territory, c.latitude, c.longitude, c.mca_id, m.name as mca_name,
                   ((SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) FROM invoices WHERE customer_id = c.id AND status != 'Voided') 
                   - 
                   (SELECT COALESCE(SUM(amount), 0) FROM customer_payments WHERE customer_id = c.id AND status = 'Active') 
                   - 
                   (SELECT COALESCE(SUM(total_amount), 0) FROM credit_notes WHERE customer_id = c.id)) 
                   AS balance
            FROM customers c 
            LEFT JOIN mca_areas m ON c.mca_id = m.id
            ORDER BY c.name ASC
        ");
        $customers = $this->db->resultSet() ?: [];
        $customersJson = [];
        foreach ($customers as $c) {
            $customersJson[] = [
                'id' => intval($c->id),
                'name' => $c->name,
                'phone' => $c->phone ?? '',
                'whatsapp' => $c->whatsapp ?? '',
                'address' => $c->address ?? '',
                'territory' => $c->territory ?? '',
                'latitude' => floatval($c->latitude ?? 0.0),
                'longitude' => floatval($c->longitude ?? 0.0),
                'outstanding' => floatval($c->balance ?? 0.0),
                'mca_id' => intval($c->mca_id ?? 0),
                'mca_name' => $c->mca_name ?? ''
            ];
        }
        
        // 4. Get server routes (territories)
        $this->db->query("SELECT id, name, main_area_id FROM mca_areas ORDER BY name ASC");
        $routes = $this->db->resultSet() ?: [];
        $routesJson = [];
        foreach ($routes as $r) {
            $routesJson[] = [
                'id' => intval($r->id),
                'name' => $r->name,
                'main_area_id' => intval($r->main_area_id ?? 0)
            ];
        }
        
        // 5. Get representatives
        $this->db->query("SELECT u.id, u.username, u.password_hash, u.employee_id, e.first_name, e.last_name 
                    FROM users u 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE u.role = 'rep'");
        $reps = $this->db->resultSet() ?: [];
        $repsJson = [];
        foreach ($reps as $rep) {
            $repsJson[] = [
                'id' => intval($rep->id),
                'username' => $rep->username,
                'password_hash' => $rep->password_hash,
                'employee_id' => intval($rep->employee_id),
                'first_name' => $rep->first_name ?? $rep->username,
                'last_name' => $rep->last_name ?? ''
            ];
        }
        
        // 6. Get payment terms
        $this->db->query("SELECT id, name, days_due FROM payment_terms ORDER BY days_due ASC");
        $terms = $this->db->resultSet() ?: [];
        $termsJson = [];
        foreach ($terms as $t) {
            $termsJson[] = [
                'id' => intval($t->id),
                'name' => $t->name,
                'days_due' => intval($t->days_due)
            ];
        }
        
        // 7. Get outstanding credit invoices for the customers
        $this->db->query("SELECT i.id, i.invoice_number, i.customer_id, i.invoice_date, 
                           (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as true_grand_total,
                           c.name as customer_name, c.address as customer_address
                    FROM invoices i
                    JOIN customers c ON i.customer_id = c.id
                    WHERE i.status = 'Unpaid' OR i.status = 'Partially Paid'
                    ORDER BY i.invoice_date ASC");
        $creditInvs = $dbInvs = $this->db->resultSet() ?: [];
        $creditInvsJson = [];
        foreach ($creditInvs as $ci) {
            $creditInvsJson[] = [
                'id' => intval($ci->id),
                'invoice_number' => $ci->invoice_number,
                'customer_id' => intval($ci->customer_id),
                'invoice_date' => $ci->invoice_date,
                'true_grand_total' => floatval($ci->true_grand_total),
                'customer_name' => $ci->customer_name,
                'customer_address' => $ci->customer_address
            ];
        }
        
        // 8. Get ongoing active route for this rep
        $this->db->query("SELECT * FROM rep_daily_routes WHERE user_id = :uid AND status = 'Active' ORDER BY id DESC LIMIT 1");
        $this->db->bind(':uid', $userId);
        $activeRoute = $this->db->single();
        $activeRouteJson = null;
        $activeRouteInvsJson = [];
        $activeRouteItemsJson = [];
        
        if ($activeRoute) {
            $activeRouteJson = [
                'id' => intval($activeRoute->id),
                'route_name' => $activeRoute->route_name,
                'start_meter' => floatval($activeRoute->start_meter),
                'start_time' => $activeRoute->start_time,
                'start_lat' => floatval($activeRoute->start_lat),
                'start_lng' => floatval($activeRoute->start_lng),
                'status' => $activeRoute->status
            ];
            
            // Invoices on this route
            $this->db->query("SELECT * FROM invoices WHERE rep_route_id = :rid AND status != 'Voided'");
            $this->db->bind(':rid', $activeRoute->id);
            $invs = $this->db->resultSet() ?: [];
            foreach ($invs as $inv) {
                $activeRouteInvsJson[] = [
                    'id' => intval($inv->id),
                    'invoice_number' => $inv->invoice_number,
                    'customer_id' => intval($inv->customer_id),
                    'invoice_date' => $inv->invoice_date,
                    'due_date' => $inv->due_date,
                    'payment_term_id' => $inv->payment_term_id ? intval($inv->payment_term_id) : 0,
                    'total_amount' => floatval($inv->total_amount),
                    'global_discount_val' => floatval($inv->global_discount_val),
                    'tax_amount' => floatval($inv->tax_amount),
                    'global_discount_type' => $inv->global_discount_type
                ];
                
                // Invoice items
                $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :iid");
                $this->db->bind(':iid', $inv->id);
                $items = $this->db->resultSet() ?: [];
                foreach ($items as $item) {
                    $activeRouteItemsJson[] = [
                        'invoice_id' => intval($item->invoice_id),
                        'item_id' => intval($item->item_id),
                        'description' => $item->description,
                        'quantity' => intval($item->quantity),
                        'unit_price' => floatval($item->unit_price),
                        'discount_value' => floatval($item->discount_value),
                        'total' => floatval($item->total)
                    ];
                }
            }
        }
        
        // 9. Fetch active discount rules and tiers
        $activeRules = $this->discountModel->getActiveRules();
        $discountRulesJson = [];
        foreach ($activeRules as $rule) {
            $tiersJson = [];
            foreach ($rule->tiers as $t) {
                $tiersJson[] = [
                    'id' => intval($t->id),
                    'rule_id' => intval($t->rule_id),
                    'min_threshold' => floatval($t->min_threshold),
                    'max_threshold' => $t->max_threshold !== null ? floatval($t->max_threshold) : null,
                    'reward_val' => floatval($t->reward_val)
                ];
            }
            $discountRulesJson[] = [
                'id' => intval($rule->id),
                'name' => $rule->name,
                'rule_type' => $rule->rule_type,
                'target_item_id' => $rule->target_item_id !== null ? intval($rule->target_item_id) : null,
                'status' => $rule->status,
                'tiers' => $tiersJson
            ];
        }

            echo json_encode([
                'success' => true,
                'products' => $productsJson,
                'categories' => $categoriesJson,
                'customers' => $customersJson,
                'routes' => $routesJson,
                'reps' => $repsJson,
                'payment_terms' => $termsJson,
                'credit_invoices' => $creditInvsJson,
                'active_route' => $activeRouteJson,
                'active_route_invoices' => $activeRouteInvsJson,
                'active_route_invoice_items' => $activeRouteItemsJson,
                'discount_rules' => $discountRulesJson
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            
            $schemaDebug = [];
            try {
                $tables = ['invoices', 'customer_payments', 'credit_notes', 'customers'];
                foreach ($tables as $tbl) {
                    $this->db->query("DESCRIBE `$tbl`");
                    $rows = $this->db->resultSet() ?: [];
                    $cols = [];
                    foreach ($rows as $r) {
                        $cols[] = $r->Field . ' (' . $r->Type . ')';
                    }
                    $schemaDebug[$tbl] = $cols;
                }
            } catch (Throwable $dbEx) {
                $schemaDebug['error'] = $dbEx->getMessage();
            }

            echo json_encode([
                'success' => false,
                'message' => 'Internal server error during pull sync: ' . $e->getMessage(),
                'schema' => $schemaDebug,
                'trace' => $e->getTraceAsString()
            ]);
            exit;
        }
    }

    public function sync_push() {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);
        
        if (!$payload) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
            exit;
        }

        $userId = isset($payload['user_id']) ? intval($payload['user_id']) : 0;
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing or invalid user ID.']);
            exit;
        }

        // Set session user ID temporarily for audit log tracking
        $_SESSION['user_id'] = $userId;

        $mappings = [
            'customers' => [],
            'routes' => [],
            'invoices' => []
        ];

        try {
            // 1. Process Customers
            if (isset($payload['customers']) && is_array($payload['customers'])) {
                foreach ($payload['customers'] as $c) {
                    $localId = intval($c['local_id']);
                    
                    // Always register new customer profile
                    $this->customerModel->addCustomer([
                        'name' => $c['name'],
                        'email' => $c['email'] ?? null,
                        'phone' => $c['phone'] ?? null,
                        'whatsapp' => $c['whatsapp'] ?? null,
                        'address' => $c['address'] ?? null,
                        'lat' => $c['latitude'] ?? null,
                        'lng' => $c['longitude'] ?? null,
                        'mca_id' => null,
                        'territory' => $c['territory'] ?? null
                    ]);
                    $serverId = $this->customerModel->getLastInsertId();
                    
                    $this->logActivity('Add Customer', 'Customer', "Registered new customer profile via mobile sync: {$c['name']}", $serverId);
                    $mappings['customers'][] = [
                        'local_id' => $localId,
                        'server_id' => intval($serverId)
                    ];
                }
            }

            // 2. Process Routes
            if (isset($payload['routes']) && is_array($payload['routes'])) {
                foreach ($payload['routes'] as $r) {
                    $localId = intval($r['local_id']);
                    
                    $this->db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, start_lat, start_lng, end_meter, end_time, end_lat, end_lng, status) 
                                      VALUES (:user_id, :route_name, :start_meter, :start_time, :start_lat, :start_lng, :end_meter, :end_time, :end_lat, :end_lng, :status)");
                    $this->db->bind(':user_id', $userId);
                    $this->db->bind(':route_name', $r['route_name']);
                    $this->db->bind(':start_meter', $r['start_meter']);
                    $this->db->bind(':start_time', $r['start_time']);
                    $this->db->bind(':start_lat', $r['start_lat']);
                    $this->db->bind(':start_lng', $r['start_lng']);
                    $this->db->bind(':end_meter', isset($r['end_meter']) && $r['end_meter'] !== '' ? $r['end_meter'] : null);
                    $this->db->bind(':end_time', $r['end_time'] ?? null);
                    $this->db->bind(':end_lat', $r['end_lat'] ?? null);
                    $this->db->bind(':end_lng', $r['end_lng'] ?? null);
                    $this->db->bind(':status', $r['status'] ?? 'Active');
                    $this->db->execute();
                    $serverId = $this->db->lastInsertId();

                    $this->logActivity('Create Route', 'RepTracking', "Created daily representative route via mobile sync: {$r['route_name']}", $serverId);
                    $mappings['routes'][] = [
                        'local_id' => $localId,
                        'server_id' => intval($serverId)
                    ];
                }
            }

            // Helpers to find mapped customer/route server IDs
            $getCustomerServerId = function($localCustId) use ($mappings) {
                foreach ($mappings['customers'] as $map) {
                    if ($map['local_id'] == $localCustId) return $map['server_id'];
                }
                return $localCustId;
            };

            $getRouteServerId = function($localRouteId) use ($mappings) {
                foreach ($mappings['routes'] as $map) {
                    if ($map['local_id'] == $localRouteId) return $map['server_id'];
                }
                return $localRouteId;
            };

            // Resolve AR and Revenue accounting accounts
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Asset' AND (account_name LIKE '%Receivable%' OR account_code LIKE '1100%' OR account_code = '1200') LIMIT 1");
            $arRow = $this->db->single();
            $arAccountId = $arRow ? $arRow->id : null;

            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' AND (account_name LIKE '%Sales%' OR account_name LIKE '%Revenue%' OR account_code LIKE '4000%') LIMIT 1");
            $revRow = $this->db->single();
            $revenueAccountId = $revRow ? $revRow->id : null;

            if (!$arAccountId || !$revenueAccountId) {
                throw new Exception("Accounting accounts could not be resolved.");
            }

            // 3. Process Invoices
            if (isset($payload['invoices']) && is_array($payload['invoices'])) {
                foreach ($payload['invoices'] as $inv) {
                    $localId = intval($inv['local_id']);
                    $custServerId = $getCustomerServerId(intval($inv['customer_id']));
                    
                    $localRouteId = intval($inv['local_route_id'] ?? 0);
                    $serverRouteIdFromApp = intval($inv['server_route_id'] ?? 0);
                    if ($serverRouteIdFromApp > 0) {
                        $routeServerId = $serverRouteIdFromApp;
                    } else {
                        $routeServerId = $getRouteServerId($localRouteId);
                    }
                    
                    // Generate new invoice number if sequence is used or keep what mobile generated
                    $invNo = $inv['invoice_number'];
                    
                    // Format invoice items for createInvoiceWithAccounting method
                    $itemsPayload = [];
                    if (isset($inv['items']) && is_array($inv['items'])) {
                        foreach ($inv['items'] as $item) {
                            $itemsPayload[] = [
                                'item_selection' => $item['product_id'] . '|0', // format: "product_id|var_id"
                                'description' => $item['product_name'],
                                'quantity' => intval($item['quantity']),
                                'unit_price' => floatval($item['unit_price']),
                                'discount_value' => floatval($item['discount_val'] ?? 0.0),
                                'discount_type' => 'Rs',
                                'total' => floatval($item['total'])
                            ];
                        }
                    }

                    $invoiceData = [
                        'customer_id' => $custServerId,
                        'invoice_number' => $invNo,
                        'invoice_date' => $inv['invoice_date'],
                        'due_date' => $inv['due_date'],
                        'payment_term_id' => !empty($inv['payment_term_id']) ? intval($inv['payment_term_id']) : null,
                        'subtotal' => floatval($inv['subtotal']),
                        'global_discount_val' => floatval($inv['discount'] ?? 0.00),
                        'global_discount_type' => 'Rs', // default mobile discount type is Rs
                        'notes' => 'Created via Mobile App Sync',
                        'rep_route_id' => $routeServerId ?: null,
                        'grand_total' => floatval($inv['grand_total']),
                        'stock_status' => 'reserved' // Keep as reserved Sales Order until finalized
                    ];

                    $invoiceId = $this->invoiceModel->createInvoiceWithAccounting(
                        $invoiceData,
                        $itemsPayload,
                        $arAccountId,
                        $revenueAccountId,
                        $userId
                    );

                    if ($invoiceId) {
                        $this->logActivity('Create Invoice', 'Billing', "Created and posted Invoice {$invNo} for Customer ID {$custServerId} via mobile sync", $invoiceId);
                        
                        // If discount was applied, log this to system_audit_trail specifically
                        if ($invoiceData['global_discount_val'] > 0) {
                            $this->logActivity('Apply Discount', 'Billing', "Applied global discount Rs: " . number_format($invoiceData['global_discount_val'], 2) . " on Invoice {$invNo}", $invoiceId);
                        }
                        
                        // Log item-wise discount applications
                        foreach ($itemsPayload as $it) {
                            if ($it['discount_value'] > 0) {
                                $this->logActivity('Apply Discount', 'Billing', "Applied item-wise discount Rs: " . number_format($it['discount_value'], 2) . " on product in Invoice {$invNo}", $invoiceId);
                            }
                        }

                        $mappings['invoices'][] = [
                            'local_id' => $localId,
                            'server_id' => intval($invoiceId),
                            'invoice_number' => $invNo
                        ];
                    } else {
                        $err = isset($_SESSION['invoice_error']) ? $_SESSION['invoice_error'] : 'Unknown error during creation';
                        unset($_SESSION['invoice_error']);
                        throw new Exception("Invoice creation failed for invoice number {$invNo}: " . $err);
                    }
                }
            }

            // 4. Process Payments
            if (isset($payload['payments']) && is_array($payload['payments'])) {
                foreach ($payload['payments'] as $p) {
                    $custServerId = $getCustomerServerId(intval($p['customer_id']));
                    $routeServerId = $getRouteServerId(intval($p['server_route_id'] ?? 0));
                    
                    $this->db->query("INSERT INTO pending_collections (customer_id, route_id, payment_method, amount, bank_name, cheque_number, cheque_date, status, notes) 
                                      VALUES (:customer_id, :route_id, :payment_method, :amount, :bank_name, :cheque_number, :cheque_date, 'Pending', 'Synced from mobile app')");
                    $this->db->bind(':customer_id', $custServerId);
                    $this->db->bind(':route_id', $routeServerId ?: null);
                    $this->db->bind(':payment_method', $p['payment_method']);
                    $this->db->bind(':amount', floatval($p['amount']));
                    $this->db->bind(':bank_name', $p['bank_name'] ?? null);
                    $this->db->bind(':cheque_number', $p['cheque_number'] ?? null);
                    $this->db->bind(':cheque_date', !empty($p['cheque_date']) ? $p['cheque_date'] : null);
                    $this->db->execute();
                    
                    $this->logActivity('Record Collection', 'Billing', "Recorded payment collection Rs: " . number_format(floatval($p['amount']), 2) . " for Customer ID {$custServerId} via mobile sync");
                }
            }

            echo json_encode([
                'success' => true,
                'mappings' => $mappings
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Sync push processing exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
