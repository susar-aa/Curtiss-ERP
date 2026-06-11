<?php
// ==========================================
// VARIABLE SAFETY & ROBUST FALLBACK ENGINE
// ==========================================

// Ensure items array is always defined
$items = $data['items'] ?? [];

// Stock analytics
$stats = $data['stats'] ?? (object)[
    'total_items' => count($items),
    'low_stock_count' => 0,
    'out_of_stock_count' => 0
];

// Ensure filters are fully initialized
$filters = $data['filters'] ?? [];
$filters['search'] = $filters['search'] ?? '';
$filters['min_price'] = $filters['min_price'] ?? '';
$filters['max_price'] = $filters['max_price'] ?? '';
$filters['stock_status'] = $filters['stock_status'] ?? '';
$filters['category_id'] = $filters['category_id'] ?? '';
$categories = $data['categories'] ?? [];
$isCurrentlyAdmin = (strtolower($_SESSION['role'] ?? '') === 'admin');

// Retrieve pagination config with safe fallbacks
$pagination = $data['pagination'] ?? [
    'current_page' => 1,
    'per_page' => 15,
    'total_items' => count($items),
    'total_pages' => 1
];

$currentPage = (int)$pagination['current_page'];
$perPage = (int)$pagination['per_page'];
$totalItems = (int)$pagination['total_items'];
$totalPages = (int)$pagination['total_pages'];

// Calculate display bounds
$startIndex = $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$endIndex = min($currentPage * $perPage, $totalItems);

// Capture bulk action status feedback
$flashSuccess = $_SESSION['flash_success'] ?? null;
if ($flashSuccess) {
    unset($_SESSION['flash_success']);
}
$flashError = $_SESSION['flash_error'] ?? null;
if ($flashError) {
    unset($_SESSION['flash_error']);
}
$importResults = $_SESSION['import_results'] ?? null;
if ($importResults) {
    unset($_SESSION['import_results']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventory Management - Curtiss ERP</title>
    
    <!-- Tailwind CSS (Kept strictly for layout/main.php compatibility) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* ==========================================================================
           APPLE IOS/MACOS NATIVE APP DESIGN SYSTEM
           ========================================================================== */
        :root {
            --apple-bg: #f2f2f7; /* Native iOS grouped background */
            --apple-surface: #ffffff;
            --apple-text: #000000;
            --apple-text-muted: #8e8e93; /* iOS secondary label */
            --apple-border: #c6c6c8;
            --apple-border-light: #e5e5ea;
            --apple-blue: #007aff;
            --apple-blue-hover: #005bb5;
            --apple-red: #ff3b30;
            --apple-orange: #ff9500;
            --apple-green: #34c759;
            --transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }

        body {
            background-color: var(--apple-bg) !important;
            color: var(--apple-text) !important;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", sans-serif !important;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding-bottom: 140px; /* Space for floating dock */
            overflow-y: auto !important;
        }

        /* Helpers */
        .hidden { display: none !important; }
        .opacity-100 { opacity: 1 !important; }
        .pointer-events-none { pointer-events: none !important; }

        /* Header Layout */
        .ios-header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px 20px;
        }
        .ios-hero-title {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0 0 16px 0;
        }
        .ios-stats-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .ios-stat-pill {
            background: var(--apple-surface);
            padding: 8px 16px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid var(--apple-border-light);
        }
        .ios-stat-dot { width: 8px; height: 8px; border-radius: 50%; }
        .ios-stat-label { font-size: 13px; font-weight: 500; color: var(--apple-text-muted); }
        .ios-stat-val { font-size: 14px; font-weight: 700; font-family: ui-monospace, monospace; }

        /* iOS Grouped Table Card */
        .ios-table-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .ios-table-card {
            background: var(--apple-surface);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            position: relative;
        }
        .ios-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .ios-table th {
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--apple-text-muted);
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--apple-border-light);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .ios-table td {
            padding: 14px 16px;
            font-size: 15px;
            border-bottom: 1px solid var(--apple-border-light);
            vertical-align: middle;
        }
        .ios-table tbody tr { transition: background-color 0.2s; }
        .ios-table tbody tr:hover { background-color: rgba(0,0,0,0.02); }
        .ios-table tbody tr:last-child td { border-bottom: none; }

        /* Table specific bits */
        .ios-avatar {
            width: 44px; height: 44px;
            border-radius: 8px;
            background: var(--apple-bg);
            border: 1px solid var(--apple-border-light);
            object-fit: cover;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Checkboxes */
        .ios-checkbox {
            appearance: none;
            width: 22px; height: 22px;
            border-radius: 50%;
            border: 1.5px solid var(--apple-border);
            background: transparent;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
            margin: 0;
            outline: none;
        }
        .ios-checkbox:checked {
            background: var(--apple-blue);
            border-color: var(--apple-blue);
        }
        .ios-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 7px; top: 4px;
            width: 5px; height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* Action Buttons inside table */
        .tbl-btn {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--apple-text-muted);
            text-decoration: none;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: 0.2s;
        }
        .tbl-btn:hover { background: var(--apple-bg); color: var(--apple-blue); }
        .tbl-btn.destructive:hover { color: var(--apple-red); }

        /* Pagination */
        .ios-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: var(--apple-surface);
            border-top: 1px solid var(--apple-border-light);
        }
        .ios-select {
            background: var(--apple-bg);
            border: none;
            padding: 6px 30px 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 14px;
            outline: none;
        }
        .ios-pager {
            display: flex;
            background: var(--apple-bg);
            border-radius: 8px;
            overflow: hidden;
        }
        .ios-pager button, .ios-pager .pager-num {
            padding: 6px 12px;
            font-size: 13px;
            border: none;
            background: transparent;
            color: var(--apple-text);
            cursor: pointer;
            font-weight: 500;
        }
        .ios-pager button:hover { background: rgba(0,0,0,0.05); }
        .ios-pager .pager-num.active { background: var(--apple-text); color: #fff; }
        .ios-pager button:disabled { opacity: 0.3; cursor: not-allowed; }

        /* FLOATING DOCK (Dynamic Island Style) */
        .main-dock {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: saturate(180%) blur(30px);
            -webkit-backdrop-filter: saturate(180%) blur(30px);
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15), 0 1px 3px rgba(0,0,0,0.05);
            border-radius: 100px;
            display: flex;
            align-items: center;
            padding: 8px;
            gap: 4px;
            z-index: 100;
            transition: var(--transition);
        }
        .main-dock.hidden-dock {
            transform: translateX(-50%) translateY(150px);
            opacity: 0;
            pointer-events: none;
        }
        
        .dock-search {
            position: relative;
            display: flex;
            align-items: center;
            margin-left: 8px;
        }
        .dock-search i {
            position: absolute;
            left: 12px;
            color: var(--apple-text-muted);
            font-size: 14px;
            pointer-events: none;
        }
        .dock-search input {
            background: rgba(0,0,0,0.06);
            border: none;
            border-radius: 20px;
            padding: 10px 16px 10px 36px;
            font-size: 15px;
            width: 200px;
            outline: none;
            color: var(--apple-text);
            transition: var(--transition);
        }
        .dock-search input:focus {
            background: rgba(0,0,0,0.1);
            width: 260px;
        }

        .dock-divider {
            width: 1px; height: 24px;
            background: rgba(0,0,0,0.1);
            margin: 0 4px;
        }

        .dock-btn {
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: none; background: transparent;
            color: var(--apple-text);
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s; text-decoration: none;
        }
        .dock-btn:hover { background: rgba(0,0,0,0.06); }
        .dock-btn.active-btn { background: rgba(0,0,0,0.1); color: var(--apple-blue); }

        .dock-btn-primary {
            background: var(--apple-blue);
            color: #fff;
            border-radius: 100px;
            padding: 0 20px;
            height: 42px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 15px; gap: 8px;
            text-decoration: none; border: none;
            margin-left: 4px; cursor: pointer; transition: 0.2s;
        }
        .dock-btn-primary:hover { background: var(--apple-blue-hover); transform: scale(0.97); }

        /* Advanced Filters Panel */
        .advanced-filters-panel {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) scale(1) translateY(0);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-radius: 24px;
            padding: 24px;
            width: 90%;
            max-width: 600px;
            z-index: 99;
            opacity: 1;
            pointer-events: auto;
            transition: var(--transition);
        }
        .advanced-filters-panel.hidden-panel {
            transform: translateX(-50%) scale(0.95) translateY(20px);
            opacity: 0;
            pointer-events: none;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .input-group { display: flex; flex-direction: column; gap: 6px; }
        .input-label { font-size: 12px; font-weight: 600; color: var(--apple-text-muted); text-transform: uppercase; }
        .input-field {
            background: rgba(0,0,0,0.05);
            border: 1px solid transparent;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            width: 100%; box-sizing: border-box; outline: none; transition: 0.2s;
        }
        .input-field:focus { background: rgba(0,0,0,0.08); }

        /* Bulk Toolbar (Dark Dock) */
        .bulk-toolbar {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            background: rgba(29, 29, 31, 0.85);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 100px;
            color: white;
            display: flex; align-items: center;
            padding: 10px 16px; gap: 16px;
            z-index: 101;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            transition: var(--transition);
            opacity: 1; pointer-events: auto;
        }
        .bulk-toolbar.hidden {
            display: flex !important; /* Override generic hidden */
            transform: translateX(-50%) translateY(150px);
            opacity: 0; pointer-events: none;
        }
        .badge-count {
            background: var(--apple-blue);
            color: #fff; font-size: 12px; font-weight: 700;
            min-width: 24px; height: 24px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; padding: 0 6px;
        }

        /* Modals & Alerts (Native iOS Action Sheet style) */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            opacity: 1; pointer-events: auto;
            transition: opacity 0.3s;
        }
        .modal-overlay.hidden {
            display: flex !important;
            opacity: 0; pointer-events: none;
        }
        .ios-action-sheet {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 14px;
            width: 100%; max-width: 380px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
            transform: scale(1) translateY(0);
            transition: var(--transition);
        }
        .modal-overlay.hidden .ios-action-sheet { transform: scale(0.95) translateY(10px); }
        
        .sheet-header { text-align: center; padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .sheet-title { font-size: 17px; font-weight: 600; color: var(--apple-text); }
        .sheet-sub { font-size: 13px; color: var(--apple-text-muted); margin-top: 4px; }
        .sheet-body { padding: 20px; max-height: 60vh; overflow-y: auto; }
        .sheet-footer { display: flex; border-top: 1px solid rgba(0,0,0,0.1); }
        
        .sheet-btn {
            flex: 1; padding: 16px; font-size: 17px; font-weight: 400;
            color: var(--apple-blue); background: transparent; border: none;
            border-right: 1px solid rgba(0,0,0,0.1); cursor: pointer; transition: 0.2s;
        }
        .sheet-btn:last-child { border-right: none; }
        .sheet-btn:hover { background: rgba(0,0,0,0.05); }
        .sheet-btn.bold { font-weight: 600; }
        .sheet-btn.destructive { color: var(--apple-red); }

        /* Alerts floating at top */
        .alerts-container {
            position: fixed; top: 24px; left: 50%; transform: translateX(-50%);
            z-index: 1000; display: flex; flex-direction: column; gap: 12px;
            width: 90%; max-width: 400px; pointer-events: none;
        }
        .alert-toast {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 16px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex; align-items: flex-start; gap: 12px;
            pointer-events: auto; animation: dropIn 0.4s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }
        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Loader Overlay */
        #table-loader {
            position: absolute; inset: 0;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(2px);
            z-index: 20; display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s;
        }

    </style>
</head>
<body>

    <!-- Unified System Top Menu Bar -->
    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Floating Top Alerts -->
    <div class="alerts-container">
        <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
            <div id="flash-success-alert" class="alert-toast">
                <i class="fa-solid fa-circle-check text-success" style="font-size: 20px;"></i>
                <div style="flex-grow: 1;">
                    <div style="font-weight: 600; font-size: 15px;">Success</div>
                    <div style="font-size: 13px; color: var(--apple-text-muted);"><?= htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? '') ?></div>
                </div>
                <button onclick="document.getElementById('flash-success-alert').style.display='none'" class="tbl-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div id="flash-error-alert" class="alert-toast">
                <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 20px;"></i>
                <div style="flex-grow: 1;">
                    <div style="font-weight: 600; font-size: 15px;">Error</div>
                    <div style="font-size: 13px; color: var(--apple-text-muted);"><?= htmlspecialchars($flashError) ?></div>
                </div>
                <button onclick="document.getElementById('flash-error-alert').style.display='none'" class="tbl-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header & Stats -->
    <div class="ios-header-container">
        <h1 class="ios-hero-title">Inventory</h1>
        <div class="ios-stats-row">
            <div class="ios-stat-pill">
                <span class="ios-stat-dot" style="background: var(--apple-text-muted);"></span>
                <span class="ios-stat-label">Total Items</span>
                <span class="ios-stat-val" id="stat-total-items"><?= number_format($stats->total_items) ?></span>
            </div>
            <div class="ios-stat-pill">
                <span class="ios-stat-dot" style="background: var(--apple-orange);"></span>
                <span class="ios-stat-label">Low Stock</span>
                <span class="ios-stat-val" style="color: var(--apple-orange);" id="stat-low-stock"><?= number_format($stats->low_stock_count) ?></span>
            </div>
            <div class="ios-stat-pill">
                <span class="ios-stat-dot" style="background: var(--apple-red);"></span>
                <span class="ios-stat-label">Out of Stock</span>
                <span class="ios-stat-val" style="color: var(--apple-red);" id="stat-out-of-stock"><?= number_format($stats->out_of_stock_count) ?></span>
            </div>
        </div>
    </div>

    <!-- Optional Import Results -->
    <?php if ($importResults): ?>
        <div class="ios-table-container" style="margin-bottom: 24px;" id="import-results-panel">
            <div class="ios-table-card" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <i class="fa-solid fa-file-circle-check text-success" style="font-size: 24px;"></i>
                        <div>
                            <div style="font-weight: 600; font-size: 16px;">Import Complete</div>
                            <div style="font-size: 13px; color: var(--apple-text-muted);">Processed all CSV rows successfully.</div>
                        </div>
                    </div>
                    <button onclick="document.getElementById('import-results-panel').style.display='none'" class="tbl-btn"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px;">
                    <div style="background: var(--apple-green-bg); padding: 12px; border-radius: 12px; text-align: center;">
                        <div style="font-size: 11px; font-weight: 600; color: #166534; text-transform: uppercase;">Added</div>
                        <div style="font-size: 20px; font-weight: 700; color: #166534;"><?= $importResults['added'] ?></div>
                    </div>
                    <div style="background: #eff6ff; padding: 12px; border-radius: 12px; text-align: center;">
                        <div style="font-size: 11px; font-weight: 600; color: #1d4ed8; text-transform: uppercase;">Updated</div>
                        <div style="font-size: 20px; font-weight: 700; color: #1d4ed8;"><?= $importResults['updated'] ?></div>
                    </div>
                    <div style="background: var(--apple-orange-bg); padding: 12px; border-radius: 12px; text-align: center;">
                        <div style="font-size: 11px; font-weight: 600; color: #b45309; text-transform: uppercase;">Relations</div>
                        <div style="font-size: 20px; font-weight: 700; color: #b45309;"><?= count($importResults['success_logs']) ?></div>
                    </div>
                    <div style="background: var(--apple-red-bg); padding: 12px; border-radius: 12px; text-align: center;">
                        <div style="font-size: 11px; font-weight: 600; color: #991b1b; text-transform: uppercase;">Errors</div>
                        <div style="font-size: 20px; font-weight: 700; color: #991b1b;"><?= count($importResults['errors']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Entire form wrapping the Dock and Filters for seamless serialization -->
    <form id="filterForm" action="<?= APP_URL ?>/inventory" method="GET" onsubmit="event.preventDefault(); applyAjaxFilters();">
        <input type="hidden" name="page" id="currentPageInput" value="<?= $currentPage ?>">
        <input type="hidden" name="per_page" id="perPageInput" value="<?= $perPage ?>">

        <!-- Advanced Filters Glass Panel -->
        <div id="advanced-filters" class="advanced-filters-panel hidden-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="font-size: 17px; font-weight: 600;">Advanced Filters</div>
                <button type="button" onclick="toggleAdvancedFilters()" class="tbl-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div class="filter-grid">
                <div class="input-group">
                    <label class="input-label">Category</label>
                    <select name="category_id" id="categorySelect" onchange="applyAjaxFilters()" class="input-field ios-select" style="background: rgba(0,0,0,0.05);">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (string)$filters['category_id'] === (string)$cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-label">Availability</label>
                    <select name="stock_status" id="stockStatusSelect" onchange="applyAjaxFilters()" class="input-field ios-select" style="background: rgba(0,0,0,0.05);">
                        <option value="">All Statuses</option>
                        <option value="instock" <?= $filters['stock_status'] === 'instock' ? 'selected' : '' ?>>In Stock (> 5)</option>
                        <option value="lowstock" <?= $filters['stock_status'] === 'lowstock' ? 'selected' : '' ?>>Low Stock (1-5)</option>
                        <option value="outstock" <?= $filters['stock_status'] === 'outstock' ? 'selected' : '' ?>>Out of Stock (0)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-label">Min Price (LKR)</label>
                    <input type="number" step="0.01" name="min_price" id="minPriceInput" value="<?= htmlspecialchars($filters['min_price']) ?>" oninput="triggerSearchDelay()" class="input-field font-mono" placeholder="0.00">
                </div>
                <div class="input-group">
                    <label class="input-label">Max Price (LKR)</label>
                    <input type="number" step="0.01" name="max_price" id="maxPriceInput" value="<?= htmlspecialchars($filters['max_price']) ?>" oninput="triggerSearchDelay()" class="input-field font-mono" placeholder="0.00">
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid rgba(0,0,0,0.1);">
                <div style="font-size: 13px; color: var(--apple-text-muted);">Found <span id="matching-count" style="font-weight: 600; color: var(--apple-text);"><?= $totalItems ?></span> items</div>
                <button type="button" onclick="clearAllFilters()" style="background: transparent; border: none; color: var(--apple-blue); font-size: 15px; cursor: pointer;">Clear Reset</button>
            </div>
        </div>

        <!-- Main Content Area: The Table Card -->
        <div class="ios-table-container" style="margin-bottom: 40px;">
            <div class="ios-table-card" id="table-wrapper">
                
                <div id="table-loader" class="pointer-events-none">
                    <i class="fa-solid fa-circle-notch fa-spin text-muted" style="font-size: 32px;"></i>
                </div>

                <div id="table-container">
                    <div style="overflow-x: auto;">
                        <table class="ios-table">
                            <thead>
                                <tr>
                                    <th style="width: 44px; text-align: center; padding-left: 20px;">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="ios-checkbox">
                                    </th>
                                    <th style="width: 60px;">Image</th>
                                    <th>Product Details</th>
                                    <th style="text-align: right;">Retail (LKR)</th>
                                    <th style="text-align: right;">B2B (LKR)</th>
                                    <th style="text-align: center;">Stock</th>
                                    <th>Status</th>
                                    <th style="text-align: right; padding-right: 20px;">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 60px 20px;">
                                            <i class="fa-solid fa-box-open text-muted" style="font-size: 40px; margin-bottom: 12px; opacity: 0.5;"></i>
                                            <div style="font-size: 17px; font-weight: 600;">No Items Found</div>
                                            <div style="font-size: 14px; color: var(--apple-text-muted); margin-top: 4px;">Try modifying your search or filter criteria.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <?php 
                                        $qty = intval($item->qty ?? 0);
                                        $price = floatval($item->selling_price ?? $item->price ?? 0);
                                        $b2b_price = floatval($item->wholesale_price ?? 0);
                                        $sku = !empty($item->item_code) ? $item->item_code : ($item->sku ?? '-');
                                        $image = $item->image_path ?? '';
                                        
                                        if (empty($image)) {
                                            $img_src = 'https://placehold.co/100x100?text=No+Img';
                                        } else {
                                            $filename = basename($image);
                                            $img_src = APP_URL . '/uploads/products/' . $filename;
                                        }

                                        if ($qty <= 0) {
                                            $statusBadge = '<span class="status-badge" style="background: var(--apple-red-bg); color: #991b1b;"><span class="status-dot" style="background: var(--apple-red);"></span>Out</span>';
                                        } elseif ($qty <= 5) {
                                            $statusBadge = '<span class="status-badge" style="background: var(--apple-orange-bg); color: #9a3412;"><span class="status-dot" style="background: var(--apple-orange);"></span>Low</span>';
                                        } else {
                                            $statusBadge = '<span class="status-badge" style="background: var(--apple-green-bg); color: #166534;"><span class="status-dot" style="background: var(--apple-green);"></span>Active</span>';
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center; padding-left: 20px;">
                                                <input type="checkbox" name="selected_items[]" value="<?= $item->id ?>" class="item-select-checkbox ios-checkbox" onchange="updateSelection()">
                                            </td>
                                            <td>
                                                <img src="<?= $img_src ?>" class="ios-avatar" onerror="this.src='https://placehold.co/100x100?text=Err'">
                                            </td>
                                            <td>
                                                <div class="table-product-info">
                                                    <span class="table-product-name"><?= htmlspecialchars($item->name ?? 'Unnamed') ?></span>
                                                    <span class="table-product-sku font-mono"><?= htmlspecialchars($sku) ?> <?= $item->sample_code ? ' | '.htmlspecialchars($item->sample_code) : '' ?></span>
                                                </div>
                                            </td>
                                            <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 500;">
                                                <?= number_format($price, 2) ?>
                                            </td>
                                            <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 500; color: var(--apple-text-muted);">
                                                <?= number_format($b2b_price, 2) ?>
                                            </td>
                                            <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 600; color: <?= $qty <= 0 ? 'var(--apple-red)' : ($qty <= 5 ? 'var(--apple-orange)' : 'inherit') ?>;">
                                                <?= $qty ?>
                                            </td>
                                            <td><?= $statusBadge ?></td>
                                            <td style="text-align: right; padding-right: 20px;">
                                                <a href="<?= APP_URL ?>/stockledger/product/<?= $item->id ?>" class="tbl-btn" title="Ledger"><i class="fa-solid fa-chart-line"></i></a>
                                                <a href="<?= APP_URL ?>/inventory/edit/<?= $item->id ?>" class="tbl-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                                <button type="button" onclick="confirmDelete(<?= $item->id ?>, '<?= htmlspecialchars(addslashes($item->name)) ?>')" class="tbl-btn destructive" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="ios-pagination">
                        <div style="font-size: 13px; color: var(--apple-text-muted);">
                            Showing <span style="font-weight: 600; color: var(--apple-text);"><?= $startIndex ?>-<?= $endIndex ?></span> of <?= $totalItems ?>
                        </div>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <select onchange="updatePageSize(this.value)" class="ios-select">
                                <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / pg</option>
                                <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / pg</option>
                                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / pg</option>
                                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / pg</option>
                            </select>
                            
                            <div class="ios-pager">
                                <button type="button" onclick="navigatePage(<?= max(1, $currentPage - 1) ?>)" <?= $currentPage <= 1 ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-left"></i></button>
                                
                                <?php 
                                $range = 1;
                                $startPage = max(1, $currentPage - $range);
                                $endPage = min($totalPages, $currentPage + $range);

                                if ($startPage > 1) {
                                    echo '<button type="button" onclick="navigatePage(1)">1</button>';
                                    if ($startPage > 2) echo '<span class="pager-num" style="color: var(--apple-text-muted);">...</span>';
                                }

                                for ($i = $startPage; $i <= $endPage; $i++) {
                                    if ($i === $currentPage) {
                                        echo '<span class="pager-num active">'.$i.'</span>';
                                    } else {
                                        echo '<button type="button" onclick="navigatePage('.$i.')">'.$i.'</button>';
                                    }
                                }

                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) echo '<span class="pager-num" style="color: var(--apple-text-muted);">...</span>';
                                    echo '<button type="button" onclick="navigatePage('.$totalPages.')">'.$totalPages.'</button>';
                                }
                                ?>

                                <button type="button" onclick="navigatePage(<?= min($totalPages, $currentPage + 1) ?>)" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- The Floating Apple Dock (Dynamic Island Search/Actions) -->
        <div id="main-dock" class="main-dock">
            <div class="dock-search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" id="searchInput" class="dock-search-input" value="<?= htmlspecialchars($filters['search']) ?>" oninput="triggerSearchDelay()" placeholder="Search Inventory...">
            </div>
            <div class="dock-divider"></div>
            <button type="button" class="dock-btn" id="filterToggleBtn" onclick="toggleAdvancedFilters()" title="Advanced Filters"><i class="fa-solid fa-sliders"></i></button>
            <div class="dock-divider"></div>
            <button type="button" class="dock-btn" onclick="openCsvModal()" title="Import CSV"><i class="fa-solid fa-arrow-up-from-bracket"></i></button>
            <a href="<?= APP_URL ?>/inventory/exportCSV" class="dock-btn" title="Export Data"><i class="fa-solid fa-arrow-down-to-bracket"></i></a>
            <a href="<?= APP_URL ?>/inventory/add" class="dock-btn-primary"><i class="fa-solid fa-plus"></i> Add New</a>
        </div>
    </form>

    <!-- Bulk Edit Selection Toolbar (Replaces Main Dock when active) -->
    <div id="bulkEditToolbar" class="bulk-toolbar hidden">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="badge-count" id="selectedCountBadge">0</div>
            <span style="font-size: 15px; font-weight: 500;">Selected</span>
        </div>
        <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.2); margin: 0 4px;"></div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="clearSelection()" style="background: transparent; color: rgba(255,255,255,0.8); border: none; font-size: 15px; cursor: pointer; padding: 8px 16px; border-radius: 20px; transition: 0.2s;">Cancel</button>
            <button type="button" onclick="openBulkEditModal()" style="background: #fff; color: #000; border: none; border-radius: 20px; padding: 8px 20px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;"><i class="fa-solid fa-pen" style="font-size: 13px;"></i> Bulk Edit</button>
        </div>
    </div>


    <!-- Modals Layer (iOS Action Sheets & Modals) -->

    <!-- CSV Import Action Sheet -->
    <div id="csvImportModal" class="modal-overlay hidden">
        <div class="ios-action-sheet" style="max-width: 440px;">
            <div class="sheet-header">
                <div class="sheet-title">Import Catalog</div>
                <div class="sheet-sub">Upload a standard ERP CSV file. Categories and relationships will be mapped automatically.</div>
            </div>
            <form action="<?= APP_URL ?>/inventory/importERPCSV" method="POST" enctype="multipart/form-data">
                <div id="import-tab-erp" class="sheet-body">
                    <div style="border: 2px dashed var(--apple-border); border-radius: 12px; padding: 40px 20px; text-align: center; background: rgba(0,0,0,0.02); cursor: pointer; position: relative;">
                        <input type="file" name="csv_file" accept=".csv" required style="position: absolute; inset: 0; opacity: 0; cursor: pointer;">
                        <i class="fa-solid fa-file-csv" style="font-size: 40px; color: var(--apple-blue); margin-bottom: 12px;"></i>
                        <div style="font-size: 15px; font-weight: 600;">Tap or Drag File Here</div>
                        <div style="font-size: 13px; color: var(--apple-text-muted); margin-top: 4px;">Requires standard .csv format</div>
                    </div>
                </div>
                <div class="sheet-footer">
                    <button type="button" onclick="closeCsvModal()" class="sheet-btn">Cancel</button>
                    <button type="submit" class="sheet-btn bold">Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Action Sheet -->
    <div id="deleteProductModal" class="modal-overlay hidden">
        <div class="ios-action-sheet">
            <div class="sheet-header">
                <div class="sheet-title">Delete Product</div>
                <div class="sheet-sub">Are you sure you want to delete <strong id="deleteItemName" style="color: var(--apple-text);"></strong>? This cannot be undone.</div>
            </div>
            <form id="deleteProductForm" onsubmit="submitDeleteProduct(event)">
                <input type="hidden" id="deleteItemId" name="item_id">
                
                <?php if (!$isCurrentlyAdmin): ?>
                <div class="sheet-body">
                    <div id="deleteErrorContainer" class="hidden" style="color: var(--apple-red); font-size: 13px; margin-bottom: 12px; text-align: center; background: var(--apple-red-bg); padding: 8px; border-radius: 8px;"></div>
                    <div style="font-size: 13px; color: var(--apple-orange); text-align: center; margin-bottom: 16px;">Admin authorization required</div>
                    <input type="text" name="admin_username" id="deleteAdminUsername" required class="input-field" placeholder="Admin Username" style="margin-bottom: 8px; background: rgba(0,0,0,0.05);">
                    <input type="password" name="password" id="deleteAdminPassword" required class="input-field" placeholder="Admin Password" style="background: rgba(0,0,0,0.05);">
                </div>
                <?php else: ?>
                <div id="deleteErrorContainer" class="hidden" style="color: var(--apple-red); font-size: 13px; padding: 16px; text-align: center; border-bottom: 1px solid rgba(0,0,0,0.1);"></div>
                <?php endif; ?>

                <div class="sheet-footer">
                    <button type="button" onclick="closeDeleteModal()" class="sheet-btn">Cancel</button>
                    <button type="submit" id="deleteSubmitBtn" class="sheet-btn bold destructive">
                        <i id="deleteBtnSpinner" class="fa-solid fa-circle-notch fa-spin hidden" style="margin-right: 6px;"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Edit Modal -->
    <div id="bulkEditModal" class="modal-overlay hidden">
        <div class="ios-action-sheet" style="max-width: 440px;">
            <div class="sheet-header">
                <div class="sheet-title">Bulk Edit</div>
                <div class="sheet-sub">Applying changes to <strong id="bulkSelectedCount">0</strong> items.</div>
            </div>
            <form id="bulkEditForm" onsubmit="submitBulkEdit(event)">
                <div class="sheet-body" style="background: rgba(0,0,0,0.02);">
                    <div id="bulkEditErrorContainer" class="hidden" style="padding: 10px; background: var(--apple-red-bg); color: var(--apple-red); border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center;"></div>

                    <!-- Category -->
                    <div style="background: var(--apple-surface); border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,0.05);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_category" value="1" id="bulkUpdateCategory" onchange="toggleBulkField('category')" class="ios-checkbox">
                            <span style="font-size: 15px; font-weight: 600;">Update Category</span>
                        </label>
                        <select name="category_id" id="bulkCategorySelect" disabled class="ios-select" style="width: 100%; border: 1px solid var(--apple-border-light);">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Retail Price -->
                    <div style="background: var(--apple-surface); border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,0.05);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_selling_price" value="1" id="bulkUpdateSellingPrice" onchange="toggleBulkField('selling_price')" class="ios-checkbox">
                            <span style="font-size: 15px; font-weight: 600;">Update Retail Price</span>
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <select name="selling_price_type" id="bulkSellingPriceType" disabled class="ios-select" style="width: 45%; border: 1px solid var(--apple-border-light);">
                                <option value="flat">Flat Val</option>
                                <option value="pct_inc">Add %</option>
                                <option value="pct_dec">Reduce %</option>
                            </select>
                            <input type="number" step="0.01" name="selling_price_val" id="bulkSellingPriceVal" disabled class="input-field font-mono" style="flex-grow: 1; padding: 8px 12px; border: 1px solid var(--apple-border-light); background: transparent;" placeholder="0.00">
                        </div>
                    </div>

                    <!-- B2B Price -->
                    <div style="background: var(--apple-surface); border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,0.05);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_wholesale_price" value="1" id="bulkUpdateWholesalePrice" onchange="toggleBulkField('wholesale_price')" class="ios-checkbox">
                            <span style="font-size: 15px; font-weight: 600;">Update B2B Price</span>
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <select name="wholesale_price_type" id="bulkWholesalePriceType" disabled class="ios-select" style="width: 45%; border: 1px solid var(--apple-border-light);">
                                <option value="flat">Flat Val</option>
                                <option value="pct_inc">Add %</option>
                                <option value="pct_dec">Reduce %</option>
                            </select>
                            <input type="number" step="0.01" name="wholesale_price_val" id="bulkWholesalePriceVal" disabled class="input-field font-mono" style="flex-grow: 1; padding: 8px 12px; border: 1px solid var(--apple-border-light); background: transparent;" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Status -->
                    <div style="background: var(--apple-surface); border-radius: 12px; padding: 16px; border: 1px solid rgba(0,0,0,0.05);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_status" value="1" id="bulkUpdateStatus" onchange="toggleBulkField('status')" class="ios-checkbox">
                            <span style="font-size: 15px; font-weight: 600;">Update Status</span>
                        </label>
                        <select name="status" id="bulkStatusSelect" disabled class="ios-select" style="width: 100%; border: 1px solid var(--apple-border-light);">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="sheet-footer">
                    <button type="button" onclick="closeBulkEditModal()" class="sheet-btn">Cancel</button>
                    <button type="submit" id="bulkSubmitBtn" class="sheet-btn bold">
                        <i id="bulkBtnSpinner" class="fa-solid fa-circle-notch fa-spin hidden" style="margin-right: 6px;"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Application Logic Engine (Unmodified API Contracts) -->
    <script>
        let searchTimeout = null;

        function triggerSearchDelay() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('currentPageInput').value = '1';
                applyAjaxFilters();
            }, 350);
        }

        function toggleAdvancedFilters() {
            const panel = document.getElementById('advanced-filters');
            const btn = document.getElementById('filterToggleBtn');
            if (panel.classList.contains('hidden-panel')) {
                panel.classList.remove('hidden-panel');
                btn.classList.add('active-btn');
            } else {
                panel.classList.add('hidden-panel');
                btn.classList.remove('active-btn');
            }
        }

        function applyAjaxFilters() {
            const form = document.getElementById('filterForm');
            const loader = document.getElementById('table-loader');

            if (loader) {
                loader.classList.remove('pointer-events-none');
                loader.classList.add('opacity-100');
            }

            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();
            const requestUrl = form.getAttribute('action') + '?' + queryParams;

            fetch(requestUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.getElementById('table-container');
                    const oldTable = document.getElementById('table-container');
                    if (newTable && oldTable) {
                        oldTable.innerHTML = newTable.innerHTML;
                    }

                    const updateStat = (id) => {
                        const newVal = doc.getElementById(id);
                        const oldVal = document.getElementById(id);
                        if (newVal && oldVal) oldVal.textContent = newVal.textContent;
                    };
                    updateStat('stat-total-items');
                    updateStat('stat-low-stock');
                    updateStat('stat-out-of-stock');
                    updateStat('matching-count');

                    window.history.pushState({ path: requestUrl }, '', requestUrl);
                    clearSelection();
                })
                .catch(err => console.error('Sync Error:', err))
                .finally(() => {
                    if (loader) {
                        loader.classList.add('pointer-events-none');
                        loader.classList.remove('opacity-100');
                    }
                });
        }

        function navigatePage(pageNum) {
            document.getElementById('currentPageInput').value = pageNum;
            applyAjaxFilters();
        }

        function updatePageSize(size) {
            document.getElementById('perPageInput').value = size;
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        function clearAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('minPriceInput').value = '';
            document.getElementById('maxPriceInput').value = '';
            document.getElementById('stockStatusSelect').value = '';
            document.getElementById('categorySelect').value = '';
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        function openCsvModal() { document.getElementById('csvImportModal').classList.remove('hidden'); }
        function closeCsvModal() { document.getElementById('csvImportModal').classList.add('hidden'); }

        let activeDeleteId = null;

        function confirmDelete(id, name) {
            activeDeleteId = id;
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            
            const errorContainer = document.getElementById('deleteErrorContainer');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                errorContainer.textContent = '';
            }
            
            const passwordInput = document.getElementById('deleteAdminPassword');
            if (passwordInput) passwordInput.value = '';
            
            const usernameInput = document.getElementById('deleteAdminUsername');
            if (usernameInput) usernameInput.value = '';

            document.getElementById('deleteProductModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteProductModal').classList.add('hidden');
            activeDeleteId = null;
        }

        function submitDeleteProduct(e) {
            e.preventDefault();
            if (!activeDeleteId) return;

            const form = document.getElementById('deleteProductForm');
            const submitBtn = document.getElementById('deleteSubmitBtn');
            const spinner = document.getElementById('deleteBtnSpinner');
            const errorContainer = document.getElementById('deleteErrorContainer');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                errorContainer.textContent = '';
            }

            const formData = new FormData(form);

            fetch('<?php echo APP_URL; ?>/inventory/delete/' + activeDeleteId, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Authorization failed');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    applyAjaxFilters();
                } else {
                    if (errorContainer) {
                        errorContainer.textContent = data.error || 'Authorization failed. Please try again.';
                        errorContainer.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                if (errorContainer) {
                    errorContainer.textContent = err.message || 'An error occurred during verification.';
                    errorContainer.classList.remove('hidden');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
            });
        }

        function toggleSelectAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateSelection();
        }

        function updateSelection() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            const selectedIds = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedIds.push(cb.value);
                }
            });

            const toolbar = document.getElementById('bulkEditToolbar');
            const countBadge = document.getElementById('selectedCountBadge');
            const selectAllCb = document.getElementById('selectAllCheckbox');
            const mainDock = document.getElementById('main-dock');

            if (selectedIds.length > 0) {
                if (countBadge) countBadge.textContent = selectedIds.length;
                if (toolbar) toolbar.classList.remove('hidden');
                if (mainDock) mainDock.classList.add('hidden-dock');
                if (selectAllCb) {
                    selectAllCb.checked = (selectedIds.length === checkboxes.length);
                }
            } else {
                if (toolbar) toolbar.classList.add('hidden');
                if (mainDock) mainDock.classList.remove('hidden-dock');
                if (selectAllCb) selectAllCb.checked = false;
            }
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const selectAllCb = document.getElementById('selectAllCheckbox');
            if (selectAllCb) selectAllCb.checked = false;
            updateSelection();
        }

        function openBulkEditModal() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            const form = document.getElementById('bulkEditForm');
            
            const oldInputs = form.querySelectorAll('input[name="item_ids[]"]');
            oldInputs.forEach(input => input.remove());

            let selectedCount = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedCount++;
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'item_ids[]';
                    hiddenInput.value = cb.value;
                    form.appendChild(hiddenInput);
                }
            });

            document.getElementById('bulkSelectedCount').textContent = selectedCount;
            
            document.getElementById('bulkUpdateCategory').checked = false;
            toggleBulkField('category');
            document.getElementById('bulkUpdateSellingPrice').checked = false;
            toggleBulkField('selling_price');
            document.getElementById('bulkUpdateWholesalePrice').checked = false;
            toggleBulkField('wholesale_price');
            document.getElementById('bulkUpdateStatus').checked = false;
            toggleBulkField('status');

            document.getElementById('bulkEditErrorContainer').classList.add('hidden');
            document.getElementById('bulkEditErrorContainer').textContent = '';

            document.getElementById('bulkEditModal').classList.remove('hidden');
        }

        function closeBulkEditModal() {
            document.getElementById('bulkEditModal').classList.add('hidden');
        }

        function toggleBulkField(field) {
            let id = 'bulkUpdateCategory';
            if (field === 'selling_price') id = 'bulkUpdateSellingPrice';
            else if (field === 'wholesale_price') id = 'bulkUpdateWholesalePrice';
            else if (field === 'status') id = 'bulkUpdateStatus';
            
            const checkbox = document.getElementById(id);
            if (!checkbox) return;
            const isChecked = checkbox.checked;
            
            if (field === 'category') {
                const select = document.getElementById('bulkCategorySelect');
                select.disabled = !isChecked;
            } else if (field === 'selling_price') {
                document.getElementById('bulkSellingPriceType').disabled = !isChecked;
                document.getElementById('bulkSellingPriceVal').disabled = !isChecked;
            } else if (field === 'wholesale_price') {
                document.getElementById('bulkWholesalePriceType').disabled = !isChecked;
                document.getElementById('bulkWholesalePriceVal').disabled = !isChecked;
            } else if (field === 'status') {
                const select = document.getElementById('bulkStatusSelect');
                select.disabled = !isChecked;
            }
        }

        function submitBulkEdit(e) {
            e.preventDefault();

            const form = document.getElementById('bulkEditForm');
            const submitBtn = document.getElementById('bulkSubmitBtn');
            const spinner = document.getElementById('bulkBtnSpinner');
            const errorContainer = document.getElementById('bulkEditErrorContainer');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                errorContainer.textContent = '';
            }

            const formData = new FormData(form);

            fetch('<?php echo APP_URL; ?>/inventory/bulkUpdate', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Bulk update failed');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeBulkEditModal();
                    clearSelection();
                    applyAjaxFilters();
                } else {
                    if (errorContainer) {
                        errorContainer.textContent = data.error || 'Bulk update failed. Please try again.';
                        errorContainer.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                if (errorContainer) {
                    errorContainer.textContent = err.message || 'An error occurred during bulk update.';
                    errorContainer.classList.remove('hidden');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
            });
        }
    </script>

    <!-- Essential System Libraries -->
    <?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>