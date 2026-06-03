<?php

// Enable error reporting to prevent blank 500 errors in the future
// But suppress for AJAX requests to prevent HTML in JSON responses
if (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $data['title'] ?? 'Dashboard' ?></title>
    
    <!-- PWA Manifest & Mobile App Support -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#0066cc">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/icon-192.png">
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= APP_URL ?>/service-worker.js')
                    .then((reg) => console.log('PWA Service Worker registered successfully:', reg.scope))
                    .catch((err) => console.log('PWA Service Worker registration failed:', err));
            });
        }
    </script>
    
    <!-- Phosphor Icons for a clean, modern look (replacing emojis) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root {
            /* Original Variables */
            --mac-bg: rgba(255, 255, 255, 0.85);
            --mac-border: rgba(0, 0, 0, 0.15);
            --bg-color: #f4f5f7;
            
            /* Mega Menu Variables */
            --text-main: #111;
            --text-muted: #666;
            --mega-bg: #ffffff;
            --mega-card-bg: #f9f9fb;
            --mega-card-hover: #f0f0f4;
            --mega-icon-border: #eaeaea;
            --mega-icon-bg: #fff;
            --mega-divider: rgba(0,0,0,0.06);
            --mega-hover: #f9f9fb;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --mac-bg: rgba(30, 30, 30, 0.85);
                --mac-border: rgba(255, 255, 255, 0.15);
                --bg-color: #121212;
                
                --text-main: #eee;
                --text-muted: #aaa;
                --mega-bg: #1c1c1e;
                --mega-card-bg: #2c2c2e;
                --mega-card-hover: #3a3a3c;
                --mega-icon-border: #3a3a3c;
                --mega-icon-bg: #2c2c2e;
                --mega-divider: rgba(255,255,255,0.08);
                --mega-hover: rgba(255,255,255,0.05);
            }
        }
        
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* --- macOS Top Bar Base --- */
        .mac-menubar {
            height: 30px;
            background: var(--mac-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--mac-border);
            display: flex;
            align-items: center;
            padding: 0 15px;
            font-size: 13px;
            font-weight: 500;
            z-index: 2000;
        }
        
        .mac-menubar-left, .mac-menubar-right {
            display: flex;
            align-items: center;
            height: 100%;
        }
        
        .mac-menubar-right { margin-left: auto; gap: 15px; }

        .mac-menu-container {
            height: 100%;
            display: flex;
            position: relative;
        }

        .mac-menu-item {
            cursor: pointer;
            padding: 0 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            height: 100%;
            color: var(--text-main);
            text-decoration: none;
        }

        .mac-menu-item.brand { font-weight: 700; font-size: 14px;}

        /* Top bar hover effect */
        .mac-menu-container:hover .mac-menu-item {
            background-color: #0066cc;
            color: #fff;
        }

        /* --- REDESIGNED MEGA MENU --- */
        .mega-menu {
            display: none;
            position: absolute;
            top: 30px; /* Attach to menubar */
            left: 0;
            background: var(--mega-bg);
            border: 1px solid var(--mac-border);
            border-radius: 0 0 12px 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            padding: 10px 0;
            z-index: 2000;
            flex-direction: row;
            cursor: default;
        }

        /* Align menus on the right to not bleed off screen */
        .align-right .mega-menu {
            left: auto;
            right: 0;
        }

        .mac-menu-container:hover .mega-menu {
            display: flex;
        }

        /* Prevent top bar text from staying blue when hovering inside the mega menu */
        .mac-menu-container:hover .mega-menu .mac-menu-item {
            background: transparent;
            color: var(--text-main);
        }

        /* Columns */
        .mega-menu-col {
            padding: 15px 25px;
            display: flex;
            flex-direction: column;
            min-width: 240px;
        }
        .mega-menu-col:not(:first-child) {
            border-left: 1px solid var(--mega-divider);
        }

        .mega-menu-header {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        /* Style 1: Large Card (Explore Column) */
        .mega-cards-grid {
            display: flex;
            gap: 15px;
        }
        .mega-card {
            background: var(--mega-card-bg);
            border-radius: 12px;
            padding: 18px;
            width: 140px;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            transition: background 0.2s ease;
            box-sizing: border-box;
        }
        .mega-card:hover {
            background: var(--mega-card-hover);
        }
        .mega-card .icon {
            font-size: 24px;
            color: var(--text-main);
        }
        .mega-card-text {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .mega-card-text .title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }
        .mega-card-text .desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.3;
        }

        /* Style 2: List Item (Company/Updates Columns) */
        .mega-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px;
            margin: 0 -10px; /* Offset padding for full hover effect */
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .mega-list-item:hover {
            background: var(--mega-hover);
        }
        .mega-list-item .icon-wrapper {
            width: 36px;
            height: 36px;
            border: 1px solid var(--mega-icon-border);
            background: var(--mega-icon-bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            color: var(--text-main);
        }
        .mega-list-item-content {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-top: 1px;
        }
        .mega-list-item-content .title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }
        .mega-list-item-content .desc {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Overrides for specific colors requested */
        .text-danger { color: #ff3b30 !important; }
        .text-warning { color: #ff9500 !important; }
        .text-primary { color: #0066cc !important; }

        /* Rest of App UI */
        .app-container { display: flex; flex: 1; overflow: hidden; position: relative;}
        .main-content { flex: 1; padding: 30px; padding-bottom: 60px; overflow-y: auto; }
        
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        @media (prefers-color-scheme: dark) { .card { background: #1e1e2d; } }
        
        .badge-alert {
            background: #ff3b30;
            color: #fff;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            position: absolute;
            top: 4px;
            right: -10px;
            border: 1px solid var(--mac-bg);
        }

        /* --- Recent Bar --- */
        .mac-recent-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--mac-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--mac-border);
            height: 40px;
            z-index: 2000;
            display: flex;
            align-items: center;
            padding: 0 15px;
            box-sizing: border-box;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(0);
        }

        .mac-recent-bar.collapsed {
            transform: translateY(100%);
        }

        .recent-toggle-tab {
            position: absolute;
            top: -26px; 
            right: 30px;
            background: var(--mac-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--mac-border);
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            padding: 4px 15px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            color: var(--text-main);
            box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .recent-links-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none; 
            flex: 1;
            align-items: center;
        }
        .recent-links-container::-webkit-scrollbar { display: none; }

        .recent-link {
            font-size: 12px;
            color: var(--text-main);
            text-decoration: none;
            background: rgba(0,0,0,0.05);
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
        }
        @media (prefers-color-scheme: dark) { .recent-link { background: rgba(255,255,255,0.1); } }
        .recent-link:hover { background: #0066cc; color: #fff; }
    </style>
</head>
<body>

    <?php 
        $db = new Database();
        $notifCount = 0;
        
        if (isset($_SESSION['user_id'])) {
            try {
                $db->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :uid AND is_read = 0");
                $db->bind(':uid', $_SESSION['user_id']);
                $row = $db->single();
                if ($row) {
                    $notifCount = $row->unread;
                }
            } catch (Exception $e) {
                $notifCount = 0;
            }
        }
    ?>

    <div class="mac-menubar">
        <div class="mac-menubar-left">
            <a href="<?= APP_URL ?>/dashboard" class="mac-menu-item brand">
                <i class="ph-fill ph-apple-logo" style="font-size: 16px;"></i> 
                <?= APP_NAME ?>
            </a>
            
            <!-- 1. Sales & CRM -->
            <div class="mac-menu-container">
                <div class="mac-menu-item">Sales & CRM</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <a href="<?= APP_URL ?>/crm" class="mega-card">
                                <div class="icon"><i class="ph ph-briefcase"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Leads & CRM</div>
                                    <div class="desc">Manage pipelines</div>
                                </div>
                            </a>
                            <a href="<?= APP_URL ?>/customer" class="mega-card">
                                <div class="icon"><i class="ph ph-users"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Customer Center</div>
                                    <div class="desc">Client profiles</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Billing & AR</div>
                        <a href="<?= APP_URL ?>/estimate" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-file-text"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Quotes & Estimates</div>
                                <div class="desc">Send tailored pricing</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/sales/create?type=sales_order" class="mega-list-item">
                            <div class="icon-wrapper text-primary"><i class="ph ph-pencil-simple"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Create Sales Order</div>
                                <div class="desc">Reserve stock without debit</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/sales" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-credit-card"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Invoices & AR</div>
                                <div class="desc">Manage receivables</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/creditnote" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-money"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Credit Notes</div>
                                <div class="desc">Issue client refunds</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/dunning" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-clock"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Dunning Reminders</div>
                                <div class="desc">Automate follow-ups</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/discount" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-tag"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Discount Feed</div>
                                <div class="desc">Configure rules & tiers</div>
                            </div>
                        </a>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Operations</div>
                        <a href="<?php echo APP_URL; ?>/RepTracking/index" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-map-pin"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Rep Route Tracking</div>
                                <div class="desc">Monitor field agents</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/delivery" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-truck"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Arranged Deliveries</div>
                                <div class="desc">Manage dispatches</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/territory" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-map-trifold"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Territory & Routing</div>
                                <div class="desc">Map sales zones</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/sales/deleted_list" class="mega-list-item">
                            <div class="icon-wrapper text-danger"><i class="ph ph-trash"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-danger">Deleted Invoices</div>
                                <div class="desc">View removed records</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. Supply Chain -->
            <div class="mac-menu-container">
                <div class="mac-menu-item">Supply Chain</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <a href="<?= APP_URL ?>/inventory" class="mega-card">
                                <div class="icon"><i class="ph ph-package"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Products</div>
                                    <div class="desc">Inventory catalog</div>
                                </div>
                            </a>
                            <a href="<?= APP_URL ?>/vendor" class="mega-card">
                                <div class="icon"><i class="ph ph-factory"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Vendor Center</div>
                                    <div class="desc">Manage suppliers</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Catalog Setup</div>
                        <a href="<?= APP_URL ?>/category" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-tag"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Product Categories</div>
                                <div class="desc">Organize your items</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/variation" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-sparkle"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Variations</div>
                                <div class="desc">Colors, sizes, types</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/warehouse" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-buildings"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Warehouse Mgmt</div>
                                <div class="desc">Locations and bins</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/warehouse/transfer" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-arrows-left-right"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Stock Transfer</div>
                                <div class="desc">Move stock between depots</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/inventory/reserved" class="mega-list-item text-primary">
                            <div class="icon-wrapper text-primary"><i class="ph ph-shield-check"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Reserved Stock</div>
                                <div class="desc">View all active stock holds</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/inventory/history" class="mega-list-item">
                            <div class="icon-wrapper text-primary"><i class="ph ph-chart-bar"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-primary">Pricing History</div>
                                <div class="desc">Track cost changes</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/creditnote/damaged" class="mega-list-item">
                            <div class="icon-wrapper text-warning"><i class="ph ph-warning-circle"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-warning">Damaged Log</div>
                                <div class="desc">Faulty stock reports</div>
                            </div>
                        </a>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Purchasing</div>
                        <a href="<?= APP_URL ?>/purchase" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-shopping-cart"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Purchase Orders</div>
                                <div class="desc">Send stock requests</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/grn" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-tray-arrow-down"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Goods Receipts (GRN)</div>
                                <div class="desc">Receive inventory</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/supplier-return" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-arrow-counter-clockwise"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Supplier Returns</div>
                                <div class="desc">RTV processing</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/expenses" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-receipt"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Expenses & AP</div>
                                <div class="desc">Payable tracking</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Operations -->
            <div class="mac-menu-container">
                <div class="mac-menu-item">Operations</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Human Resources</div>
                        <a href="<?= APP_URL ?>/hrm" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-user-circle-gear"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">HRM & Employees</div>
                                <div class="desc">Staff directories</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/hrm/payroll" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-bank"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Run Payroll</div>
                                <div class="desc">Process salaries</div>
                            </div>
                        </a>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Management</div>
                        <a href="<?= APP_URL ?>/project" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-clipboard-text"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Projects & Tasks</div>
                                <div class="desc">Team assignments</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/vehicle" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-car-profile"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Vehicle Management</div>
                                <div class="desc">Fleet maintenance</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/cheque" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-signature"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Cheque Management</div>
                                <div class="desc">Track issuing</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. Accounting -->
            <div class="mac-menu-container">
                <div class="mac-menu-item">Accounting</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <a href="<?= APP_URL ?>/accounting/coa" class="mega-card">
                                <div class="icon"><i class="ph ph-notebook"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Chart of Accts</div>
                                    <div class="desc">General ledger base</div>
                                </div>
                            </a>
                            <a href="<?= APP_URL ?>/accounting/journal" class="mega-card">
                                <div class="icon"><i class="ph ph-pen-nib"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Journal Entries</div>
                                    <div class="desc">Manual adjustments</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Banking & Assets</div>
                        <a href="<?= APP_URL ?>/banking" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-bank"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Banking & Registers</div>
                                <div class="desc">Accounts and recons</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/asset" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-buildings"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Fixed Assets Register</div>
                                <div class="desc">Depreciation tracking</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 5. Analytics -->
            <div class="mac-menu-container align-right">
                <div class="mac-menu-item">Analytics</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Insights</div>
                        <a href="<?= APP_URL ?>/report" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-chart-line-up"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Financial Reports Hub</div>
                                <div class="desc">Statements & summaries</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/budget" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-target"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Budgets vs Actuals</div>
                                <div class="desc">Performance tracking</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 6. Admin (Conditional) -->
            <?php if(isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Manager' || $_SESSION['role'] === 'Accountant')): ?>
            <div class="mac-menu-container align-right">
                <div class="mac-menu-item">Admin</div>
                <div class="mega-menu">
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Explore</div>
                        <div class="mega-cards-grid">
                            <a href="<?= APP_URL ?>/settings" class="mega-card">
                                <div class="icon"><i class="ph ph-gear"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Settings</div>
                                    <div class="desc">Company config</div>
                                </div>
                            </a>
                            <?php if($_SESSION['role'] === 'Admin'): ?>
                            <a href="<?= APP_URL ?>/user" class="mega-card">
                                <div class="icon"><i class="ph ph-lock-key"></i></div>
                                <div class="mega-card-text">
                                    <div class="title">Users</div>
                                    <div class="desc">Roles & access</div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Accountant'): ?>
                    <div class="mega-menu-col">
                        <div class="mega-menu-header">Compliance</div>
                        <a href="<?= APP_URL ?>/tax" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-scales"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Tax Rates & Rules</div>
                                <div class="desc">Manage VAT/GST</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/paymentterm" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-handshake"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">Payment Terms</div>
                                <div class="desc">Standard & Date-Driven</div>
                            </div>
                        </a>
                        <a href="<?= APP_URL ?>/accounting/close_year" class="mega-list-item">
                            <div class="icon-wrapper text-danger"><i class="ph ph-lock"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title text-danger">Close Financial Year</div>
                                <div class="desc">Lock historical data</div>
                            </div>
                        </a>
                        <?php if($_SESSION['role'] === 'Admin'): ?>
                        <a href="<?= APP_URL ?>/audit" class="mega-list-item">
                            <div class="icon-wrapper"><i class="ph ph-shield-check"></i></div>
                            <div class="mega-list-item-content">
                                <div class="title">System Audit Trail</div>
                                <div class="desc">Monitor user activity</div>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="mac-menubar-right">
            <a href="<?= APP_URL ?>/notification" class="mac-menu-item" style="position: relative;">
                <i class="ph ph-bell" style="font-size: 16px;"></i>
                <?php if($notifCount > 0): ?>
                    <span class="badge-alert"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>
            <span class="mac-menu-item" style="cursor:default;">
                <i class="ph ph-user" style="font-size: 16px;"></i>
                <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </span>
            <span class="mac-menu-item" id="clock" style="cursor:default;"></span>
            
            <a href="<?= APP_URL ?>/auth/logout" class="mac-menu-item" style="color:#ff3b30; font-weight:bold;">
                <i class="ph ph-sign-out" style="font-size: 16px;"></i> Logout
            </a>
        </div>
    </div>

    <div class="app-container">
        <main class="main-content">
            <?php 
            if (isset($data['content_view'])) {
                require_once '../app/Views/' . $data['content_view'] . '.php';
            } else {
                echo "<p>View not found.</p>";
            }
            ?>
        </main>
    </div>

    <div id="recentBar" class="mac-recent-bar">
        <div class="recent-toggle-tab" onclick="toggleRecentBar()">
            <span id="recentToggleIcon"><i class="ph ph-caret-down"></i></span> Recent Pages
        </div>
        <div style="font-size: 11px; color:#888; font-weight:bold; margin-right: 15px; text-transform:uppercase;">History:</div>
        <div class="recent-links-container" id="recentLinksContainer">
            <!-- JavaScript will populate this dynamically -->
        </div>
    </div>

    <script>
        // Clock Logic
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'short', hour: 'numeric', minute: '2-digit' };
            document.getElementById('clock').innerText = now.toLocaleDateString('en-US', options).replace(',', '');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- Recent History Tracker Logic ---
        document.addEventListener("DOMContentLoaded", function() {
            const currentUrl = window.location.href;
            const currentTitle = "<?= addslashes($data['title'] ?? 'Dashboard') ?>";
            
            // Exclude the login/logout pages from history
            if(currentUrl.includes('/auth/')) return;

            // Fetch existing history from Session Storage
            let history = JSON.parse(sessionStorage.getItem('curtiss_history')) || [];
            
            // Remove current URL if it already exists in the array
            history = history.filter(item => item.url !== currentUrl);
            
            // Add the current page to the absolute front of the array
            history.unshift({ url: currentUrl, title: currentTitle });
            
            // Keep a maximum of 8 recent pages
            if(history.length > 8) history.pop();
            
            // Save it back to session storage
            sessionStorage.setItem('curtiss_history', JSON.stringify(history));

            // Render the pills to the HTML bar (skipping index 0)
            const container = document.getElementById('recentLinksContainer');
            if (history.length <= 1) {
                container.innerHTML = '<span style="font-size:12px; color:#888;">No recent pages yet.</span>';
            } else {
                for(let i = 1; i < history.length; i++) {
                    let a = document.createElement('a');
                    a.href = history[i].url;
                    a.className = 'recent-link';
                    // Replaced emoji with Phosphor Icon in JS history generation
                    a.innerHTML = `<i class="ph ph-file-text" style="font-size: 14px;"></i> ${history[i].title}`;
                    container.appendChild(a);
                }
            }

            // Restore the Collapse state from local storage
            const bar = document.getElementById('recentBar');
            const icon = document.getElementById('recentToggleIcon');
            let isCollapsed = localStorage.getItem('curtiss_recent_collapsed') === 'true';
            
            if(isCollapsed) {
                bar.classList.add('collapsed');
                icon.innerHTML = '<i class="ph ph-caret-up"></i>';
            }
        });

        function toggleRecentBar() {
            const bar = document.getElementById('recentBar');
            const icon = document.getElementById('recentToggleIcon');
            
            bar.classList.toggle('collapsed');
            const collapsedNow = bar.classList.contains('collapsed');
            
            // Save preference to local storage
            localStorage.setItem('curtiss_recent_collapsed', collapsedNow);
            icon.innerHTML = collapsedNow ? '<i class="ph ph-caret-up"></i>' : '<i class="ph ph-caret-down"></i>';
        }
    </script>
    
    <?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>