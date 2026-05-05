<?php
// Enable error reporting for easier debugging of 500 errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';
requireRole(['rep']); // Strictly for Sales Reps

$rep_id = $_SESSION['user_id'];

// --- 1. HANDLE SESSION ACTIONS ---

// --- Handle Post Actions for Sessions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['route_action'])) {
    $action = $_POST['route_action'];

    if ($action == 'start_day') {
        $route_id = (int)$_POST['route_id'];
        $meter = (float)$_POST['start_meter'];
        $lat = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $lng = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;

        $pdo->prepare("INSERT INTO rep_sessions (rep_id, route_id, start_meter, date, status) VALUES (?, ?, ?, CURDATE(), 'active')")->execute([$rep_id, $route_id, $meter]);
        $pdo->prepare("INSERT INTO rep_daily_sessions (user_id, session_date, start_time, status) VALUES (?, CURDATE(), NOW(), 'active') ON DUPLICATE KEY UPDATE start_time = NOW(), status = 'active'")->execute([$rep_id]);
        
        if ($lat && $lng) {
            $pdo->prepare("INSERT INTO rep_location_logs (user_id, latitude, longitude, activity_type, timestamp) VALUES (?, ?, ?, 'session_started', NOW())")->execute([$rep_id, $lat, $lng]);
        }
    } elseif ($action == 'end_day') {
        $session_id = (int)$_POST['session_id'];
        $end_meter = (float)$_POST['end_meter'];
        $cash = (float)($_POST['actual_cash_total_input'] ?? 0);
        $cheque_amt = (float)($_POST['cheque_amount'] ?? 0);
        $cheque_count = (int)($_POST['cheque_count'] ?? 0);
        $lat = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $lng = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        
        $pdo->prepare("UPDATE rep_sessions SET end_meter = ?, cash_collected = ?, cheque_amount = ?, cheque_count = ?, status = 'ended' WHERE id = ? AND rep_id = ?")->execute([$end_meter, $cash, $cheque_amt, $cheque_count, $session_id, $rep_id]);
        $pdo->prepare("UPDATE rep_daily_sessions SET end_time = NOW(), status = 'ended' WHERE user_id = ? AND session_date = CURDATE()")->execute([$rep_id]);
        
        if ($lat && $lng) {
            $pdo->prepare("INSERT INTO rep_location_logs (user_id, latitude, longitude, activity_type, timestamp) VALUES (?, ?, ?, 'session_ended', NOW())")->execute([$rep_id, $lat, $lng]);
        }
    }
    header("Location: dashboard.php");
    exit;
}

// --- Fetch Key Metrics ---
$today_sales = 0;
$today_bills = 0;
$cash_sales = 0;
$active_session = null;
$ended_sessions = [];
$all_routes = [];

try {
    // Get all available routes
    $routeStmt = $pdo->query("SELECT id, name FROM routes ORDER BY name ASC");
    $all_routes = $routeStmt->fetchAll();

    // Check for an active session today
    $activeStmt = $pdo->prepare("
        SELECT rs.*, r.name as route_name 
        FROM rep_sessions rs 
        JOIN routes r ON rs.route_id = r.id 
        WHERE rs.rep_id = ? AND rs.date = CURDATE() AND rs.status = 'active'
        ORDER BY rs.id DESC LIMIT 1
    ");
    $activeStmt->execute([$rep_id]);
    $active_session = $activeStmt->fetch();

    $session_id = $active_session ? $active_session['id'] : null;

    if ($session_id) {
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE rep_session_id = ?");
        $stmt->execute([$session_id]);
        $today_sales = $stmt->fetchColumn() ?: 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE rep_session_id = ?");
        $stmt->execute([$session_id]);
        $today_bills = $stmt->fetchColumn() ?: 0;
    } else {
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE rep_id = ? AND DATE(created_at) = CURDATE()");
        $stmt->execute([$rep_id]);
        $today_sales = $stmt->fetchColumn() ?: 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE rep_id = ? AND DATE(created_at) = CURDATE()");
        $stmt->execute([$rep_id]);
        $today_bills = $stmt->fetchColumn() ?: 0;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE rep_id = ? AND payment_status != 'paid'");
    $stmt->execute([$rep_id]);
    $pending_orders = $stmt->fetchColumn() ?: 0;

} catch(PDOException $e) {
    die("<div style='padding: 20px; color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard — Fintrix</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Fintrix Rep">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            /* Clean UI Color Palette */
            --bg-color: #F8FAFC;         
            --surface: #FFFFFF;          
            --text-main: #0F172A;        
            --text-muted: #64748B;       
            --border: #E2E8F0;           
            
            --primary: #2563EB;          
            --primary-bg: #EFF6FF;
            --success: #10B981;          
            --success-bg: #ECFDF5;
            --danger: #EF4444;           
            --danger-bg: #FEF2F2;
            --warning: #F59E0B;          
            --warning-bg: #FFFBEB;
            
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 10px;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            
            --nav-h: 70px;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            padding-bottom: calc(var(--nav-h) + 20px);
            -webkit-font-smoothing: antialiased;
            margin: 0;
        }

        /* ── Modern Clean Header ── */
        .app-header {
            background: var(--surface);
            padding: 40px 20px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: var(--shadow-sm);
        }
        .header-text { display: flex; flex-direction: column; }
        .header-date { font-size: 12px; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .header-name { font-size: 22px; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.02em; }
        .header-avatar {
            width: 44px; height: 44px; border-radius: 50%; background: var(--primary-bg); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700;
            border: 1px solid var(--border); text-decoration: none;
        }

        /* ── Page Content ── */
        .page-content { padding: 20px 16px 0; }

        /* ── Stats Grid ── */
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .stat-card {
            background: var(--surface); border-radius: var(--radius-lg); padding: 16px;
            border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: flex; flex-direction: column;
        }
        .stat-icon {
            width: 32px; height: 32px; border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 16px;
        }
        .stat-icon.sales { background: var(--success-bg); color: var(--success); }
        .stat-icon.bills { background: var(--primary-bg); color: var(--primary); }
        
        .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; }
        .stat-value { font-size: 24px; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: var(--text-main); line-height: 1; letter-spacing: -0.02em; }

        /* ── Section Title ── */
        .section-title { font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0 0 16px 4px; letter-spacing: -0.01em; }

        /* ── Route Card ── */
        .route-card {
            background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border);
            box-shadow: var(--shadow-sm); margin-bottom: 24px; overflow: hidden;
        }
        .route-header { padding: 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .route-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
        .route-badge.pending { background: var(--warning-bg); color: var(--warning); }
        .route-badge.active { background: var(--success-bg); color: var(--success); }
        .route-badge.completed { background: var(--primary-bg); color: var(--primary); }
        .route-badge.rejected { background: var(--danger-bg); color: var(--danger); }
        
        .route-body { padding: 16px; }
        .route-name { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .route-desc { font-size: 14px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 16px; }
        
        .btn-action {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 15px; font-weight: 600; padding: 14px;
            border-radius: var(--radius-md); border: none; width: 100%; transition: transform 0.1s; cursor: pointer;
        }
        .btn-action:active { transform: scale(0.98); }
        .btn-primary-action { background: var(--text-main); color: #fff; }
        .btn-success-action { background: var(--success); color: #fff; }
        .btn-danger-action { background: var(--danger-bg); color: var(--danger); border: 1px solid #FECACA; }
        
        .action-grid-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        /* Unified Form Inputs */
        .clean-input {
            width: 100%; background: var(--bg-color); border: 1px solid var(--border);
            border-radius: var(--radius-md); padding: 14px 16px; font-size: 16px;
            font-family: 'Inter', sans-serif; font-weight: 500;
            color: var(--text-main); outline: none; transition: border 0.2s; margin-bottom: 16px;
        }
        .clean-input.mono { font-family: 'JetBrains Mono', monospace; }
        .clean-input:focus { border-color: var(--primary); background: #fff; }

        /* ── APP GRID (New Addition) ── */
        .app-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px 12px;
            margin-bottom: 32px;
            padding: 10px 4px;
        }
        .app-grid-btn {
            display: flex; flex-direction: column; align-items: center; text-align: center;
            text-decoration: none; color: var(--text-main);
            transition: transform 0.1s; cursor: pointer;
        }
        .app-grid-btn:active { transform: scale(0.95); }
        .ag-icon-wrapper {
            width: 58px; height: 58px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .ag-text { font-size: 12px; font-weight: 600; line-height: 1.25; color: var(--text-main); }
        
        /* Grid Colors */
        .ag-green { background: var(--success-bg); color: var(--success); }
        .ag-blue { background: var(--primary-bg); color: var(--primary); }
        .ag-amber { background: var(--warning-bg); color: var(--warning); }
        .ag-purple { background: #F3E8FF; color: #7E22CE; }
        .ag-rose { background: #FCE7F3; color: #BE185D; }
        .ag-gray { background: #F1F5F9; color: var(--text-muted); box-shadow: none; border: 1px solid var(--border); }

        /* ── List Menu Group ── */
        .menu-group {
            background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border);
            box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px;
        }
        .menu-item {
            display: flex; align-items: center; gap: 14px; padding: 16px; border-bottom: 1px solid var(--border);
            text-decoration: none; color: var(--text-main); background: var(--surface); transition: background 0.1s;
        }
        .menu-item:active { background: var(--bg-color); }
        .menu-item:last-child { border-bottom: none; }
        
        .menu-icon {
            width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0; background: var(--bg-color); color: var(--text-muted);
        }
        .menu-icon.red { color: var(--danger); background: var(--danger-bg); }
        .menu-icon.amber { color: var(--warning); background: var(--warning-bg); }
        .menu-icon.gray { color: var(--text-muted); background: var(--bg-color); border: 1px solid var(--border); }
        
        .menu-content { flex: 1; }
        .menu-title { font-size: 15px; font-weight: 600; margin-bottom: 2px; }
        .menu-sub { font-size: 13px; color: var(--text-muted); }
        .menu-chevron { color: #CBD5E1; font-size: 16px; }

        /* ── Info Box ── */
        .info-box {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-md); padding: 12px; margin-bottom: 16px;
        }
        .info-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 0; border-bottom: 1px dashed var(--border); font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--text-muted); }
        .info-value { font-weight: 600; color: var(--text-main); font-family: 'JetBrains Mono', monospace; }
        .info-value.money { color: var(--success); }
        .info-value.expense { color: var(--danger); }

        /* ── Alert ── */
        .clean-alert {
            background: var(--warning-bg); border-radius: var(--radius-md); padding: 16px;
            display: flex; gap: 12px; align-items: center; margin-bottom: 24px; border: 1px solid #FEF3C7;
        }
        .clean-alert i { color: var(--warning); font-size: 20px; }
        .clean-alert h6 { margin: 0 0 4px 0; font-weight: 700; font-size: 14px; color: #92400E; }
        .clean-alert p { margin: 0; font-size: 13px; color: #B45309; }

        /* ── Bottom Nav (Glassmorphism) ── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            display: flex; justify-content: space-around; align-items: center;
            height: var(--nav-h); z-index: 1000; padding-bottom: env(safe-area-inset-bottom, 0);
        }
        .nav-tab {
            flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;
            text-decoration: none; color: var(--text-muted); font-size: 11px; font-weight: 500;
            padding: 8px 0; transition: color 0.2s;
        }
        .nav-tab i { font-size: 22px; }
        .nav-tab.active { color: var(--primary); }
        .nav-fab-wrapper { position: relative; top: -16px; flex: 1; display: flex; flex-direction: column; align-items: center; text-decoration: none;}
        .nav-fab {
            width: 52px; height: 52px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.25);
            transition: transform 0.1s;
        }
        .nav-fab:active { transform: scale(0.95); }
        .nav-fab-label { font-size: 11px; font-weight: 600; color: var(--text-main); margin-top: 6px; }

        /* Modals Overrides */
        .modal-content { border: none; border-radius: 24px 24px 0 0; }
        .modal-header { border-bottom: 1px solid var(--border); padding: 20px; }
        .modal-title { font-weight: 700; font-size: 18px; }
        .modal-body { padding: 20px; }

        /* Cash denominations inside Modal */
        .denom-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .denom-label { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 600; width: 60px; text-align: right; }
        .denom-input { flex: 1; background: var(--bg-color); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px; font-family: 'JetBrains Mono', monospace; font-size: 16px; outline: none; }
        .denom-input:focus { border-color: var(--primary); background: #fff; }
    </style>
</head>
<body>

    <!-- ── Header ── -->
    <header class="app-header">
        <div class="header-text">
            <span class="header-date"><?php echo date('l, d M'); ?></span>
            <h1 class="header-name">Hi, <?php echo htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]); ?></h1>
        </div>
        <div class="header-actions d-flex gap-2 align-items-center">
            <button id="installAppBtn" class="btn btn-sm btn-outline-primary rounded-pill fw-bold d-none" onclick="installApp()"><i class="bi bi-download"></i> Install</button>
            <a href="../logout.php" class="header-avatar shadow-sm">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
            </a>
        </div>
    </header>

    <main class="page-content">

        <!-- ── Stats Grid ── -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon sales"><i class="bi bi-wallet2"></i></div>
                <div class="stat-content">
                    <p class="stat-label">Route Sales</p>
                    <h3 class="stat-value">
                        <?php echo $today_sales >= 1000 ? 'Rs ' . number_format($today_sales / 1000, 1) . 'k' : 'Rs ' . number_format($today_sales); ?>
                    </h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bills"><i class="bi bi-receipt"></i></div>
                <div class="stat-content">
                    <p class="stat-label">Bills Issued</p>
                    <h3 class="stat-value"><?php echo $today_bills; ?></h3>
                </div>
            </div>
        </div>

        <!-- ── Dynamic Route Card ── -->
        <h2 class="section-title">Today's Route Session</h2>
        
        <?php if ($active_session): ?>
            <!-- Active Session -->
            <div class="route-card">
                <div class="route-header">
                    <span class="route-badge active"><i class="bi bi-broadcast"></i> Session Active</span>
                    <span class="text-muted small fw-bold" style="font-family: 'JetBrains Mono', monospace;">Start: <?php echo number_format($active_session['start_meter'], 1); ?></span>
                </div>
                <div class="route-body">
                    <h3 class="route-name mb-4"><?php echo htmlspecialchars($active_session['route_name']); ?></h3>
                    <button type="button" class="btn-action btn-danger-action" onclick="showEndDayModal()" style="background: var(--danger); color: white;">
                        End Route & Day
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- No Active Session - Start One -->
            <div class="route-card">
                <div class="route-header">
                    <span class="route-badge pending"><i class="bi bi-key"></i> Ready to Start</span>
                </div>
                <div class="route-body">
                    <h3 class="route-name mb-2">Start New Session</h3>
                    <p class="text-muted small mb-3">Select your route and enter your starting meter to begin taking orders.</p>
                    
                    <form method="POST" id="startDayForm">
                        <input type="hidden" name="route_action" value="start_day">
                        <input type="hidden" name="latitude" id="start_lat" value="">
                        <input type="hidden" name="longitude" id="start_lng" value="">
                        
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Select Route</label>
                        <select name="route_id" class="clean-input" required>
                            <option value="">-- Choose Route --</option>
                            <?php foreach($all_routes as $r): ?>
                                <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block mt-2">Start Meter (km)</label>
                        <input type="text" inputmode="numeric" name="start_meter" class="clean-input mono" required placeholder="e.g. 45200.5">
                        
                        <button type="button" class="btn-action btn-primary-action" onclick="processStartDay(this);">Start Day</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pending Orders Alert -->
        <?php if($pending_orders > 0): ?>
        <div class="clean-alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div>
                <h6>Action Required</h6>
                <p>You have <?php echo $pending_orders; ?> pending payment collections.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── NEW APP GRID SECTIONS ── -->
        <h2 class="section-title mt-4">Sales Tools</h2>
        <div class="app-grid">
            
            <!-- Row 1 -->
            <a href="create_order.php" class="app-grid-btn">
                <div class="ag-icon-wrapper ag-green"><i class="bi bi-receipt"></i></div>
                <span class="ag-text">Create<br>Invoice</span>
            </a>

            <a href="vehicle_stock.php" class="app-grid-btn">
                <div class="ag-icon-wrapper ag-blue"><i class="bi bi-truck-front"></i></div>
                <span class="ag-text">Vehicle<br>Stock</span>
            </a>

            <?php if($session_id && $active_session['status'] == 'active' && !is_null($active_session['start_meter'])): ?>
                <a href="#" class="app-grid-btn" onclick="showExpensesModal()">
                    <div class="ag-icon-wrapper ag-amber"><i class="bi bi-fuel-pump"></i></div>
                    <span class="ag-text">Route<br>Expenses</span>
                </a>
            <?php else: ?>
                <div class="app-grid-btn" style="opacity: 0.5;" onclick="alert('Start your route first to log expenses.')">
                    <div class="ag-icon-wrapper ag-gray"><i class="bi bi-fuel-pump"></i></div>
                    <span class="ag-text">Route<br>Expenses</span>
                </div>
            <?php endif; ?>

            <!-- Row 2 -->
            <a href="todays_bills.php<?php echo $session_id ? '?session_id='.$session_id : ''; ?>" class="app-grid-btn">
                <div class="ag-icon-wrapper ag-purple"><i class="bi bi-list-check"></i></div>
                <span class="ag-text">Active<br>Bills</span>
            </a>

            <a href="customers.php?filter=route" class="app-grid-btn">
                <div class="ag-icon-wrapper ag-rose"><i class="bi bi-people"></i></div>
                <span class="ag-text">Route<br>Customers</span>
            </a>

            <a href="create_order.php?general=true" class="app-grid-btn">
                <div class="ag-icon-wrapper ag-gray"><i class="bi bi-boxes"></i></div>
                <span class="ag-text">General<br>Invoice</span>
            </a>

        </div>

        <h2 class="section-title">Other Tools</h2>
        <div class="menu-group mb-5">
            <?php if($session_id && $active_session['status'] == 'active' && !is_null($active_session['start_meter'])): ?>
            <a href="log_unproductive.php" class="menu-item">
                <div class="menu-icon red"><i class="bi bi-x-circle-fill"></i></div>
                <div class="menu-content">
                    <div class="menu-title">Log Unproductive</div>
                    <div class="menu-sub">Record shops that didn't buy</div>
                </div>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </a>
            <?php else: ?>
            <div class="menu-item" style="opacity: 0.5; cursor: pointer;" onclick="alert('Please start your route first to log unproductive visits.')">
                <div class="menu-icon red"><i class="bi bi-x-circle-fill"></i></div>
                <div class="menu-content">
                    <div class="menu-title">Log Unproductive</div>
                    <div class="menu-sub">Record shops that didn't buy</div>
                </div>
                <i class="bi bi-lock-fill menu-chevron"></i>
            </div>
            <?php endif; ?>

            <a href="process_return.php" class="menu-item">
                <div class="menu-icon red"><i class="bi bi-arrow-return-left"></i></div>
                <div class="menu-content">
                    <div class="menu-title">Process Returns</div>
                    <div class="menu-sub">Handle damaged goods</div>
                </div>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </a>

            <a href="analytics.php" class="menu-item">
                <div class="menu-icon amber"><i class="bi bi-bar-chart-fill"></i></div>
                <div class="menu-content">
                    <div class="menu-title">Analytics</div>
                    <div class="menu-sub">View KPIs and targets</div>
                </div>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </a>

            <a href="route_history.php" class="menu-item">
                <div class="menu-icon gray"><i class="bi bi-clock-history"></i></div>
                <div class="menu-content">
                    <div class="menu-title">Route History</div>
                    <div class="menu-sub">View past trips & records</div>
                </div>
                <i class="bi bi-chevron-right menu-chevron"></i>
            </a>
        </div>

    </main>

    <!-- ── Updated Bottom Nav (Glassmorphism) ── -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-tab active">
            <i class="bi bi-house-door-fill"></i> Home
        </a>
        <a href="catalog.php" class="nav-tab">
            <i class="bi bi-grid"></i> Catalog
        </a>
        <div class="nav-fab-wrapper">
            <a href="create_order.php" class="nav-fab">
                <i class="bi bi-plus-lg"></i>
            </a>
            <span class="nav-fab-label">POS</span>
        </div>
        <a href="customers.php" class="nav-tab">
            <i class="bi bi-people-fill"></i> Customers
        </a>
        <a href="analytics.php" class="nav-tab">
            <i class="bi bi-bar-chart-line-fill"></i> Stats
        </a>
    </nav>

    <!-- ═══════════════════════════════════════
         EXPENSES MODAL
    ═══════════════════════════════════════ -->
    <?php if ($session_id): ?>
    <div class="modal fade" id="expensesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-bottom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <?php if(count($route_expenses) > 0): ?>
                    <div class="info-box mb-4">
                        <?php foreach($route_expenses as $exp): ?>
                        <div class="info-row align-items-start" style="font-family: 'Inter', sans-serif;">
                            <div>
                                <span class="d-block fw-bold text-dark"><?php echo htmlspecialchars($exp['type']); ?></span>
                                <span class="d-block text-muted small"><?php echo htmlspecialchars($exp['description']); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-danger fw-bold" style="font-family: 'JetBrains Mono', monospace;">-Rs <?php echo number_format($exp['amount'], 2); ?></span>
                                <form method="POST" onsubmit="return confirm('Delete expense?');" style="margin:0;">
                                    <input type="hidden" name="route_action" value="delete_expense">
                                    <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                                    <input type="hidden" name="expense_id" value="<?php echo $exp['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-link text-muted p-0"><i class="bi bi-x-circle-fill" style="font-size: 16px;"></i></button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="route_action" value="add_expense">
                        <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                        
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Type</label>
                        <select name="expense_type" class="clean-input" required>
                            <option value="Fuel">Fuel</option>
                            <option value="Meals">Meals / Refreshments</option>
                            <option value="Vehicle Repair">Vehicle Repair / Tolls</option>
                            <option value="Other">Other</option>
                        </select>
                        
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Amount (Rs)</label>
                        <input type="number" step="0.01" name="expense_amount" class="clean-input mono" required placeholder="0.00">
                        
                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Note</label>
                        <input type="text" name="expense_desc" class="clean-input mb-4" placeholder="Optional context">
                        
                        <button type="submit" class="btn-action btn-primary-action">Save Expense</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════
         END DAY MODAL
    ═══════════════════════════════════════ -->
    <?php if ($active_session): ?>
    <div class="modal fade" id="endDayModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-bottom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">End Route Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Handover Summary -->
                    <div class="info-box mb-4">
                        <h6 class="fw-bold mb-3" style="font-family: 'Inter', sans-serif;">Session Summary</h6>
                        <div class="info-row" style="font-family: 'Inter', sans-serif;">
                            <span class="info-label">Gross Bills Issued</span>
                            <span class="info-value text-dark money">Rs <?php echo number_format($today_sales, 2); ?></span>
                        </div>
                    </div>

                    <form method="POST" id="endDayForm">
                        <input type="hidden" name="route_action" value="end_day">
                        <input type="hidden" name="session_id" value="<?php echo $active_session['id']; ?>">
                        <input type="hidden" name="latitude" id="end_lat" value="">
                        <input type="hidden" name="longitude" id="end_lng" value="">

                        <!-- Cash denomination calculator -->
                        <div class="info-box mb-4 bg-light border-0">
                            <h6 class="fw-bold mb-3" style="font-family: 'Inter', sans-serif;">Cash Collection</h6>
                            <?php
                            $denoms = [5000 => '5,000', 2000 => '2,000', 1000 => '1,000', 500 => '500', 100 => '100', 50 => '50', 20 => '20'];
                            foreach($denoms as $val => $label): ?>
                            <div class="denom-row">
                                <span class="denom-label"><?php echo $label; ?></span>
                                <span class="text-muted small">x</span>
                                <input type="number" id="denom_<?php echo $val; ?>" class="denom-input cash-calc" min="0" inputmode="numeric" placeholder="0">
                            </div>
                            <?php endforeach; ?>
                            <div class="denom-row">
                                <span class="denom-label text-muted">Coins</span>
                                <span class="text-muted small">+</span>
                                <input type="number" step="0.01" id="denom_coins" class="denom-input cash-calc" min="0" inputmode="decimal" placeholder="0.00">
                            </div>
                            
                            <div class="info-row mt-3 border-top pt-2" style="font-family: 'Inter', sans-serif;">
                                <span class="info-label fw-bold">Total Cash</span>
                                <span class="info-value text-success fs-5 money" id="actual_cash_total">0.00</span>
                                <input type="hidden" name="actual_cash_total_input" id="actual_cash_total_input" value="0">
                            </div>
                        </div>

                        <!-- Cheque Collection -->
                        <div class="info-box mb-4">
                            <h6 class="fw-bold mb-3" style="font-family: 'Inter', sans-serif;">Cheque Collection</h6>
                            <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Total Cheque Amount (Rs)</label>
                            <input type="number" step="0.01" name="cheque_amount" class="clean-input mono mb-3" placeholder="0.00">

                            <label class="text-muted fw-bold small text-uppercase mb-2 d-block">Number of Cheques</label>
                            <input type="number" name="cheque_count" class="clean-input mono mb-2" placeholder="0">
                        </div>

                        <label class="text-muted fw-bold small text-uppercase mb-2 d-block">End Meter (km)</label>
                        <input type="text" inputmode="numeric" name="end_meter" class="clean-input mono mb-4" required placeholder="e.g. 45250.5">

                        <button type="button" class="btn-action btn-danger-action" style="background: var(--danger); color: white;" onclick="processEndDay(this);">Complete Day</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Updated Bottom Nav (Glassmorphism) ── -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-tab active">
            <i class="bi bi-house-door-fill"></i> Home
        </a>
        <a href="catalog.php" class="nav-tab">
            <i class="bi bi-grid"></i> Catalog
        </a>
        <div class="nav-fab-wrapper">
            <a href="create_order.php" class="nav-fab">
                <i class="bi bi-plus-lg"></i>
            </a>
            <span class="nav-fab-label">POS</span>
        </div>
        <a href="customers.php" class="nav-tab">
            <i class="bi bi-people-fill"></i> Customers
        </a>
        <a href="analytics.php" class="nav-tab">
            <i class="bi bi-bar-chart-line-fill"></i> Stats
        </a>
    </nav>


    <script>
        // ── PWA Install ──
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const btn = document.getElementById('installAppBtn');
            if(btn) btn.classList.remove('d-none');
        });
        function installApp() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((r) => {
                if (r.outcome === 'accepted') document.getElementById('installAppBtn').classList.add('d-none');
                deferredPrompt = null;
            });
        }
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('sw.js'));
        }

        // ── Meter formatter ──
        document.querySelectorAll('.clean-input[name*="meter"]').forEach(input => {
            input.addEventListener('input', function() {
                let val = this.value.replace(/[^0-9]/g, '');
                if (val.length > 7) val = val.substring(0, 7); // Allow up to 7 digits (6 + decimal)
                
                if (val.length >= 6) {
                    // Automatically insert dot before the last digit if 6 or more digits
                    let main = val.substring(0, val.length - 1);
                    let decimal = val.substring(val.length - 1);
                    this.value = main + '.' + decimal;
                } else {
                    this.value = val;
                }
            });
        });

        // ── Cash Calculator ──
        function calculateCash() {
            let total = 0;
            [5000, 2000, 1000, 500, 100, 50, 20].forEach(d => {
                const el = document.getElementById('denom_' + d);
                total += (el ? (parseInt(el.value) || 0) : 0) * d;
            });
            const coinsEl = document.getElementById('denom_coins');
            total += coinsEl ? (parseFloat(coinsEl.value) || 0) : 0;

            document.getElementById('actual_cash_total').innerText = total.toFixed(2);
            document.getElementById('actual_cash_total_input').value = total.toFixed(2);

            const expectedEl = document.getElementById('expected_cash_val');
            const expected = expectedEl ? (parseFloat(expectedEl.value) || 0) : 0;
            const diff = total - expected;
            const el = document.getElementById('cash_diff');
            if (el) {
                el.innerText = (diff >= 0 ? '+' : '') + diff.toFixed(2);
                el.style.color = diff >= 0 ? 'var(--success)' : 'var(--danger)';
            }
        }
        document.querySelectorAll('.cash-calc').forEach(i => i.addEventListener('input', calculateCash));

        // ── Start Day ──
        function processStartDay(btn) {
            if (!document.querySelector('input[name="start_meter"]').value) {
                alert('Please enter start meter.'); return;
            }
            if (!confirm('Start day and log attendance?')) return;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => { document.getElementById('start_lat').value = pos.coords.latitude; document.getElementById('start_lng').value = pos.coords.longitude; document.getElementById('startDayForm').submit(); },
                    () => document.getElementById('startDayForm').submit(),
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            } else { document.getElementById('startDayForm').submit(); }
        }

        // ── End Day ──
        function processEndDay(btn) {
            if (!document.querySelector('input[name="end_meter"]').value) {
                alert('Please enter end meter.'); return;
            }
            if (!confirm('Finalize route and end day?')) return;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => { document.getElementById('end_lat').value = pos.coords.latitude; document.getElementById('end_lng').value = pos.coords.longitude; document.getElementById('endDayForm').submit(); },
                    () => document.getElementById('endDayForm').submit(),
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            } else { document.getElementById('endDayForm').submit(); }
        }

        // ── Modal Triggers ──
        function showEndDayModal() {
            if (typeof bootstrap === 'undefined') {
                alert('Components are still loading. Please wait 1-2 seconds and try again.');
                return;
            }
            const el = document.getElementById('endDayModal');
            if (el) {
                const modal = new bootstrap.Modal(el);
                modal.show();
            } else {
                alert('Session data is loading. Please refresh.');
            }
        }

        function showExpensesModal() {
            if (typeof bootstrap === 'undefined') return;
            const el = document.getElementById('expensesModal');
            if (el) {
                const modal = new bootstrap.Modal(el);
                modal.show();
            }
        }

        // ── Background Location Ping ──
        <?php if ($active_session && $active_session['status'] == 'active' && !is_null($active_session['start_meter'])): ?>
        function sendLocationPing() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(pos => {
                fetch('../ajax/log_location.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ latitude: pos.coords.latitude, longitude: pos.coords.longitude, activity: 'background_ping' })
                }).catch(() => {});
            }, () => {}, { enableHighAccuracy: true });
        }
        sendLocationPing();
        setInterval(sendLocationPing, 120000);
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>