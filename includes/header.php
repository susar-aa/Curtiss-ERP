<?php
// Ensure database columns are ready for e-commerce
if(isset($pdo)) {
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_status ENUM('pending', 'processing', 'dispatched', 'delivered', 'cancelled') DEFAULT 'pending' AFTER payment_status");
    } catch(PDOException $e) {}
}

// Ensure auth functions exist
if (!function_exists('hasAccess')) {
    require_once __DIR__ . '/auth_check.php';
}

$cur = basename($_SERVER['PHP_SELF']);

// --- Module Group Mappings ---
$sales_pages     = ['sales_overview.php','create_order.php','orders_list.php','rep_targets.php','online_orders.php'];
$routes_pages    = ['dispatch.php', 'routes.php', 'route_sales.php', 'meter_readings.php'];
$pur_pages       = ['purchasing_overview.php','create_po.php','purchase_orders.php','create_grn.php','grn_list.php','stock_ledger.php'];
$setup_pages     = ['setup_overview.php','products.php','categories.php','suppliers.php','product_gallery.php']; 
$fin_pages       = ['finance_overview.php','cheques.php','bank_cash.php','pnl_report.php','expenses.php','sales_returns.php', 'aging_reports.php']; 
$hr_pages        = ['hr_overview.php','employees.php','payroll.php']; 
$mkt_pages       = ['campaigns.php', 'promotions.php']; 
$analytics_pages = ['reports.php', 'promo_reports.php', 'agent_claims_report.php', 'category_sales.php', 'product_sales.php', 'area_sales.php']; 
$tracking_pages  = ['live_tracking.php', 'route_tracking_history.php'];

// --- Recent Pages Tracking ---
if (!isset($_SESSION['recent_pages'])) {
    $_SESSION['recent_pages'] = [];
}
$page_titles = [
    'dashboard.php' => 'Dashboard', 'sales_overview.php' => 'Sales Overview', 'create_order.php' => 'Create POS Order',
    'online_orders.php' => 'E-Commerce Orders', 'orders_list.php' => 'Sales History', 'rep_targets.php' => 'Rep Targets',
    'dispatch.php' => 'Vehicle Dispatch', 'routes.php' => 'Manage Routes', 'route_sales.php' => 'Route Sales',
    'meter_readings.php' => 'Meter Readings', 'customers.php' => 'Customers', 'purchasing_overview.php' => 'Purchasing',
    'purchase_orders.php' => 'Purchase Orders', 'create_grn.php' => 'Receive Goods (GRN)', 'grn_list.php' => 'GRN History',
    'stock_ledger.php' => 'Stock Ledger', 'setup_overview.php' => 'Catalogue Setup', 'products.php' => 'Products',
    'categories.php' => 'Categories', 'suppliers.php' => 'Suppliers', 'product_gallery.php' => 'Digital Catalog',
    'finance_overview.php' => 'Finance Core', 'bank_cash.php' => 'Bank & Cash Ledgers', 'cheques.php' => 'Manage Cheques',
    'expenses.php' => 'Company Expenses', 'aging_reports.php' => 'AR / AP Aging', 'sales_returns.php' => 'Returns & Credits',
    'pnl_report.php' => 'Profit & Loss', 'live_tracking.php' => 'Live Location', 'route_tracking_history.php' => 'Route History',
    'hr_overview.php' => 'HR & Team', 'employees.php' => 'Employees', 'payroll.php' => 'Payroll & Salaries',
    'campaigns.php' => 'Email Campaigns', 'promotions.php' => 'Promotions', 'reports.php' => 'Sales Analytics',
    'promo_reports.php' => 'Promos Report', 'agent_claims_report.php' => 'Agent Claims', 'category_sales.php' => 'Category Sales',
    'product_sales.php' => 'Product Sales', 'area_sales.php' => 'Area Sales', 'users.php' => 'System Users'
];
$title = $page_titles[$cur] ?? 'Dashboard';

// Avoid consecutive duplicates
if (empty($_SESSION['recent_pages']) || $_SESSION['recent_pages'][0]['url'] !== $cur) {
    array_unshift($_SESSION['recent_pages'], ['url' => $cur, 'title' => $title]);
    if (count($_SESSION['recent_pages']) > 10) {
        array_pop($_SESSION['recent_pages']);
    }
}

// Execute Notification Queries ONLY for Admins & Supervisors
$alertCount = 0;
$alerts = [];
if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'supervisor']) && isset($pdo)) {
    
    // 1. E-Commerce Online Orders
    $onlineOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE shipping_name IS NOT NULL AND order_status = 'pending'")->fetchColumn();
    if($onlineOrders > 0) {
        $alertCount += $onlineOrders;
        $alerts[] = ['icon' => 'bi-cart-check-fill', 'color' => 'success', 'hex' => '#34C759', 'text' => "$onlineOrders new online order(s) pending processing.", 'link' => 'online_orders.php'];
    }

    // 2. Low Stock Alerts
    $lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'available' AND stock <= 5")->fetchColumn();
    if($lowStock > 0) {
        $alertCount += $lowStock;
        $alerts[] = ['icon' => 'bi-exclamation-triangle-fill', 'color' => 'warning', 'hex' => '#FF9500', 'text' => "$lowStock product(s) dropped below minimum stock levels.", 'link' => 'products.php'];
    }

    // 3. Cheques to Bank Today
    $chequesToday = $pdo->query("SELECT COUNT(*) FROM cheques WHERE banking_date = CURDATE() AND status = 'pending'")->fetchColumn();
    if($chequesToday > 0) {
        $alertCount += $chequesToday;
        $alerts[] = ['icon' => 'bi-bank2', 'color' => 'danger', 'hex' => '#FF3B30', 'text' => "$chequesToday cheque(s) scheduled for banking today.", 'link' => 'cheques.php?status=pending'];
    }

    // 4. Bounced / Returned Cheques (Critical Finance)
    $bouncedCheques = $pdo->query("SELECT COUNT(*) FROM cheques WHERE status = 'returned'")->fetchColumn();
    if($bouncedCheques > 0) {
        $alertCount += $bouncedCheques;
        $alerts[] = ['icon' => 'bi-x-octagon-fill', 'color' => 'danger', 'hex' => '#FF3B30', 'text' => "$bouncedCheques cheque(s) have bounced or returned! Immediate action required.", 'link' => 'cheques.php?status=returned'];
    }

    // 5. Supplier Payments Due (Cashflow Warning)
    $apData = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount - paid_amount), 0) as total_due FROM grns WHERE payment_status != 'paid'")->fetch();
    if($apData['count'] > 0) {
        $alertCount++;
        $alerts[] = ['icon' => 'bi-wallet2', 'color' => 'danger', 'hex' => '#FF3B30', 'text' => "You have Rs " . number_format($apData['total_due'], 2) . " in Supplier Payments due this week, helping you manage your bank balances before issuing cheques.", 'link' => 'aging_reports.php'];
    }

    // 6. Critical Customer Debt (AR Aging > 60 Days Warning)
    $criticalDebt = $pdo->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM orders WHERE payment_status != 'paid' AND DATEDIFF(CURDATE(), created_at) > 60")->fetchColumn();
    if($criticalDebt > 0) {
        $alertCount++;
        $alerts[] = ['icon' => 'bi-exclamation-triangle-fill', 'color' => 'warning', 'hex' => '#FF9500', 'text' => "Warning: Rs " . number_format($criticalDebt, 2) . " in customer debt is over 60 days overdue.", 'link' => 'aging_reports.php'];
    }

    // 7. Routes Pending Unload (Operational Bottleneck)
    $pendingUnload = $pdo->query("SELECT COUNT(*) FROM rep_sessions WHERE status = 'ended'")->fetchColumn();
    if($pendingUnload > 0) {
        $alertCount += $pendingUnload;
        $alerts[] = ['icon' => 'bi-truck', 'color' => 'info', 'hex' => '#30B0C7', 'text' => "$pendingUnload rep session(s) ended and waiting for dispatch binding/settlement.", 'link' => 'pending_routes.php'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candent - Command Center</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        /* Candent Topbar iOS Styling */
        .candent-topbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(60, 60, 67, 0.12);
            padding: 8px 16px;
            z-index: 1040; /* Above sidebar */
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .candent-brand img {
            height: 32px;
            object-fit: contain;
        }

        /* Nav Icon Buttons */
        .nav-icon-btn {
            color: #1c1c1e;
            background: transparent;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .nav-icon-btn:hover { background: rgba(60,60,67,0.08); }
        .nav-icon-btn:active { background: rgba(60,60,67,0.12); }

        /* Notification Badge */
        .bell-icon-wrapper {
            position: relative;
            display: inline-flex;
        }
        .candent-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #FF3B30; /* iOS Red */
            color: white;
            font-size: 0.6rem;
            font-weight: 800;
            padding: 2px 5px;
            border-radius: 50px;
            border: 2px solid #ffffff;
            transform: translate(30%, -30%);
            line-height: 1;
        }

        /* Dropdown Styling */
        .candent-dropdown {
            border-radius: 18px;
            border: 1px solid rgba(60, 60, 67, 0.08);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12), 0 2px 10px rgba(0,0,0,0.04);
            padding: 8px;
            min-width: 340px;
            margin-top: 10px !important;
        }
        .candent-dropdown-header {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1c1c1e;
            padding: 8px 12px 12px;
            border-bottom: 1px solid rgba(60,60,67,0.1);
            margin-bottom: 8px;
        }
        .candent-dropdown .dropdown-item {
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 4px;
            transition: background 0.15s;
            white-space: normal; /* Allow text wrapping */
        }
        .candent-dropdown .dropdown-item:hover { background: rgba(60,60,67,0.04); }
        .candent-dropdown .dropdown-item:active { background: rgba(60,60,67,0.08); }

        .alert-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 12px;
        }

        /* Topbar User Profile */
        .topbar-user {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1c1c1e;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-right: 16px;
            border-right: 1px solid rgba(60,60,67,0.15);
            margin-right: 16px;
        }
        .topbar-user span { color: rgba(60,60,67,0.6); font-weight: 500; font-size: 0.75rem; }

        /* Logout Button */
        .logout-btn {
            font-size: 0.85rem;
            font-weight: 600;
            color: #FF3B30;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 50px;
            background: rgba(255,59,48,0.08);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .logout-btn:hover { background: rgba(255,59,48,0.15); color: #CC2200; }
        .logout-btn:active { transform: scale(0.96); }
        
        /* Top Navigation Dropdown Styling */
        .candent-topbar .navbar-nav .nav-link {
            color: #1c1c1e;
            padding: 6px 12px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .candent-topbar .navbar-nav .nav-link:hover { background: rgba(60,60,67,0.08); }
        .candent-topbar .navbar-nav .dropdown-menu {
            border-radius: 12px;
            border: 1px solid rgba(60, 60, 67, 0.08);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
            padding: 8px;
            margin-top: 8px;
        }
        .candent-topbar .navbar-nav .dropdown-item {
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            color: rgba(60,60,67,0.85);
        }
        .candent-topbar .navbar-nav .dropdown-item:hover { background: rgba(60,60,67,0.08); color: #1c1c1e; }
        .candent-topbar .navbar-nav .dropdown-header {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(60,60,67,0.5);
            padding: 8px 12px 4px;
        }
    </style>
</head>
<body class="bg-light">
<script>
    // Execute immediately before render to prevent flickering when Sidebar is minimized
    if (localStorage.getItem('sidebar_minimized') === 'true') {
        document.body.classList.add('sidebar-minimized');
    }
</script>

<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg candent-topbar sticky-top">
    <div class="container-fluid px-2">
        
        <div class="d-flex align-items-center flex-grow-1">
            <!-- Mobile Sidebar Toggle -->
            <button class="nav-icon-btn d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                <i class="bi bi-list fs-4"></i>
            </button>
            
            <!-- Desktop Sidebar Toggle -->
            <button class="nav-icon-btn d-none d-md-inline-flex me-3" type="button" id="sidebarMinimizeBtn" title="Toggle Quick Actions">
                <i class="bi bi-layout-sidebar-reverse fs-5"></i>
            </button>
            
            <!-- Candent Brand Logo -->
            <a class="navbar-brand candent-brand ms-1 me-4" href="dashboard.php">
                <img src="/images/logo/logo.png" alt="Candent" onerror="this.src='https://via.placeholder.com/140x32/ffffff/30C88A?text=CANDENT+SYS'">
            </a>
            
            <!-- Desktop Top Menu -->
            <ul class="navbar-nav flex-row gap-2 d-none d-md-flex align-items-center" style="font-size: 0.9rem; font-weight: 600;">
                <?php if(hasAccess('dashboard.php')): ?>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                
                <?php if(canViewGroup(array_merge($sales_pages, $routes_pages, ['customers.php']))): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Operations</a>
                    <ul class="dropdown-menu">
                        <?php if(canViewGroup($sales_pages)): ?>
                        <li><h6 class="dropdown-header">Sales & Orders</h6></li>
                        <?php if(hasAccess('create_order.php')): ?><li><a class="dropdown-item" href="create_order.php">Create Order (POS)</a></li><?php endif; ?>
                        <?php if(hasAccess('online_orders.php')): ?><li><a class="dropdown-item" href="online_orders.php">E-Commerce Orders</a></li><?php endif; ?>
                        <?php if(hasAccess('orders_list.php')): ?><li><a class="dropdown-item" href="orders_list.php">Sales History</a></li><?php endif; ?>
                        <?php if(hasAccess('rep_targets.php')): ?><li><a class="dropdown-item" href="rep_targets.php">Rep Targets</a></li><?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if(canViewGroup($routes_pages)): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Dispatch & Routes</h6></li>
                        <?php if(hasAccess('pending_routes.php') || hasAccess('dispatch.php')): ?><li><a class="dropdown-item" href="pending_routes.php">Vehicle Dispatch</a></li><?php endif; ?>
                        <?php if(hasAccess('routes.php')): ?><li><a class="dropdown-item" href="routes.php">Manage Routes</a></li><?php endif; ?>
                        <?php if(hasAccess('route_sales.php')): ?><li><a class="dropdown-item" href="route_sales.php">Route Sales</a></li><?php endif; ?>
                        <?php if(hasAccess('meter_readings.php')): ?><li><a class="dropdown-item" href="meter_readings.php">Meter Readings</a></li><?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if(hasAccess('customers.php')): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-bold" href="customers.php">Customers</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(canViewGroup(array_merge($pur_pages, $setup_pages))): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Inventory</a>
                    <ul class="dropdown-menu">
                        <?php if(canViewGroup($pur_pages)): ?>
                        <li><h6 class="dropdown-header">Purchasing</h6></li>
                        <?php if(hasAccess('purchase_orders.php')): ?><li><a class="dropdown-item" href="purchase_orders.php">Purchase Orders</a></li><?php endif; ?>
                        <?php if(hasAccess('create_grn.php')): ?><li><a class="dropdown-item" href="create_grn.php">Receive Goods (GRN)</a></li><?php endif; ?>
                        <?php if(hasAccess('grn_list.php')): ?><li><a class="dropdown-item" href="grn_list.php">GRN History</a></li><?php endif; ?>
                        <?php if(hasAccess('stock_ledger.php')): ?><li><a class="dropdown-item" href="stock_ledger.php">Stock Ledger</a></li><?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if(canViewGroup($setup_pages)): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Catalogue Setup</h6></li>
                        <?php if(hasAccess('products.php')): ?><li><a class="dropdown-item" href="products.php">Products</a></li><?php endif; ?>
                        <?php if(hasAccess('categories.php')): ?><li><a class="dropdown-item" href="categories.php">Categories</a></li><?php endif; ?>
                        <?php if(hasAccess('suppliers.php')): ?><li><a class="dropdown-item" href="suppliers.php">Suppliers</a></li><?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(canViewGroup($fin_pages)): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Finance</a>
                    <ul class="dropdown-menu">
                        <?php if(hasAccess('bank_cash.php')): ?><li><a class="dropdown-item" href="bank_cash.php">Bank & Cash Ledgers</a></li><?php endif; ?>
                        <?php if(hasAccess('cheques.php')): ?><li><a class="dropdown-item" href="cheques.php">Manage Cheques</a></li><?php endif; ?>
                        <?php if(hasAccess('expenses.php')): ?><li><a class="dropdown-item" href="expenses.php">Company Expenses</a></li><?php endif; ?>
                        <?php if(hasAccess('aging_reports.php')): ?><li><a class="dropdown-item" href="aging_reports.php">AR / AP Aging</a></li><?php endif; ?>
                        <?php if(hasAccess('sales_returns.php')): ?><li><a class="dropdown-item" href="sales_returns.php">Returns & Credits</a></li><?php endif; ?>
                        <?php if(hasAccess('pnl_report.php')): ?><li><a class="dropdown-item" href="pnl_report.php">Profit & Loss</a></li><?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(canViewGroup(array_merge($tracking_pages, $hr_pages, $mkt_pages, $analytics_pages))): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">More</a>
                    <ul class="dropdown-menu">
                        <?php if(canViewGroup($tracking_pages)): ?>
                        <li><h6 class="dropdown-header">Location Tracking</h6></li>
                        <?php if(hasAccess('live_tracking.php')): ?><li><a class="dropdown-item" href="live_tracking.php">Live Location</a></li><?php endif; ?>
                        <?php if(hasAccess('route_tracking_history.php')): ?><li><a class="dropdown-item" href="route_tracking_history.php">Route History</a></li><?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php if(canViewGroup($hr_pages)): ?>
                        <li><h6 class="dropdown-header">HR & Team</h6></li>
                        <?php if(hasAccess('employees.php')): ?><li><a class="dropdown-item" href="employees.php">Employees</a></li><?php endif; ?>
                        <?php if(hasAccess('payroll.php')): ?><li><a class="dropdown-item" href="payroll.php">Payroll & Salaries</a></li><?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php if(canViewGroup($analytics_pages)): ?>
                        <li><h6 class="dropdown-header">Analytics</h6></li>
                        <?php if(hasAccess('reports.php')): ?><li><a class="dropdown-item" href="reports.php">Sales Analytics</a></li><?php endif; ?>
                        <?php if(hasAccess('promo_reports.php')): ?><li><a class="dropdown-item" href="promo_reports.php">Promos Report</a></li><?php endif; ?>
                        <?php if(hasAccess('agent_claims_report.php')): ?><li><a class="dropdown-item" href="agent_claims_report.php">Agent Claims</a></li><?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if(hasRole(['admin'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="users.php">System Users</a></li>
                        <li><a class="dropdown-item text-danger" href="backup.php" target="_blank">Database Backup</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="d-flex ms-auto align-items-center">
            
            <!-- Smart Notification Bell (Admins Only) -->
            <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'supervisor'])): ?>
            <div class="dropdown me-3">
                <button class="nav-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative;">
                    <div class="bell-icon-wrapper">
                        <i class="bi bi-bell-fill fs-5" style="color: #1c1c1e;"></i>
                        <?php if($alertCount > 0): ?>
                        <span class="candent-badge">
                            <?php echo $alertCount > 99 ? '99+' : $alertCount; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </button>
                
                <ul class="dropdown-menu dropdown-menu-end candent-dropdown">
                    <li class="candent-dropdown-header">
                        <i class="bi bi-bell me-1"></i> Notifications
                    </li>
                    
                    <?php if(empty($alerts)): ?>
                        <li>
                            <div class="dropdown-item text-center py-4">
                                <i class="bi bi-check2-circle d-block mb-2" style="font-size: 2rem; color: #34C759;"></i>
                                <span style="font-size: 0.85rem; color: rgba(60,60,67,0.6); font-weight: 500;">You're all caught up!</span>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach($alerts as $alert): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-start" href="<?php echo $alert['link']; ?>">
                                    <div class="alert-icon-box" style="background: <?php echo $alert['hex']; ?>20; color: <?php echo $alert['hex']; ?>;">
                                        <i class="bi <?php echo $alert['icon']; ?> fs-6"></i>
                                    </div>
                                    <div style="flex: 1; padding-top: 2px;">
                                        <span style="font-size: 0.82rem; font-weight: 500; color: #1c1c1e; line-height: 1.3; display: block;">
                                            <?php echo $alert['text']; ?>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider my-2" style="border-color: rgba(60,60,67,0.08);"></li>
                        <li>
                            <a href="dashboard.php" class="dropdown-item text-center" style="color: #30C88A; font-weight: 700; font-size: 0.8rem;">
                                Go to Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- User Info & Logout -->
            <div class="d-none d-sm-flex align-items-center">
                <div class="topbar-user">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    <span>(<?php echo ucfirst($_SESSION['user_role']); ?>)</span>
                </div>
            </div>
            
            <a href="../logout.php" class="logout-btn">
                <span>Logout</span>
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<div class="d-flex w-100 position-relative">