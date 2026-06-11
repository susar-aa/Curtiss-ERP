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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Curtiss ERP</title>
    
    <!-- Tailwind CSS (Kept strictly for layout/main.php & resilient_loader.php compatibility) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* ==========================================================================
           RADICAL iOS / MAC OS INSPIRED MINIMALIST ARCHITECTURE
           ========================================================================== */
        :root {
            --ios-bg: #f2f2f7;
            --ios-surface: #ffffff;
            --ios-text: #000000;
            --ios-text-secondary: #8a8a8e;
            --ios-border: #e5e5ea;
            --ios-border-dark: #c6c6c8;
            --ios-blue: #007aff;
            --ios-blue-hover: #0060cc;
            --ios-red: #ff3b30;
            --ios-red-bg: #ffeceb;
            --ios-orange: #ff9500;
            --ios-orange-bg: #fff5e5;
            --ios-green: #34c759;
            --ios-green-bg: #ebf9ee;
            --shadow-float: 0 20px 40px rgba(0,0,0,0.15), 0 1px 5px rgba(0,0,0,0.1);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --radius-pill: 999px;
            --radius-card: 16px;
            --transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
        }

        body {
            background-color: var(--ios-bg) !important;
            color: var(--ios-text) !important;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif !important;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Helpers */
        .flex-h { display: flex; align-items: center; }
        .flex-v { display: flex; flex-direction: column; }
        .hidden { display: none !important; }
        .opacity-100 { opacity: 1 !important; }
        .pointer-events-none { pointer-events: none !important; }

        /* Workspace Core */
        .workspace-core {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px 140px 20px; /* Extra padding at bottom for the floating bar */
            width: 100%;
            box-sizing: border-box;
            flex-grow: 1;
        }

        /* iOS Hero Header */
        .ios-hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 24px;
        }
        .ios-title {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: -0.04em;
            margin: 0;
            color: var(--ios-text);
        }
        .ios-subtitle {
            font-size: 15px;
            color: var(--ios-text-secondary);
            margin: 4px 0 0 0;
        }

        /* Stats Micro-Widgets */
        .ios-stats-row {
            display: flex;
            gap: 16px;
        }
        .ios-stat-widget {
            background: var(--ios-surface);
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            min-width: 100px;
        }
        .ios-stat-val {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .ios-stat-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--ios-text-secondary);
            letter-spacing: 0.05em;
            margin-top: 2px;
        }
        .ios-stat-widget.warning .ios-stat-val { color: var(--ios-orange); }
        .ios-stat-widget.danger .ios-stat-val { color: var(--ios-red); }

        /* Pill Filters */
        .pill-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            align-items: center;
        }
        .pill-item {
            display: flex;
            align-items: center;
            background: var(--ios-surface);
            border: 1px solid var(--ios-border);
            border-radius: var(--radius-pill);
            padding: 8px 16px;
            box-shadow: var(--shadow-sm);
            font-size: 13px;
            font-weight: 500;
            color: var(--ios-text-secondary);
            transition: var(--transition);
        }
        .pill-item:hover {
            border-color: var(--ios-border-dark);
        }
        .pill-item select, .pill-item input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 14px;
            font-weight: 600;
            color: var(--ios-text);
            font-family: inherit;
            margin-left: 6px;
            padding: 0;
        }
        .pill-item select {
            appearance: none;
            cursor: pointer;
            padding-right: 18px;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238a8a8e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right center;
            background-size: 14px;
        }
        .pill-item input {
            width: 70px;
        }
        .pill-btn {
            background: transparent;
            border: 1px solid var(--ios-border-dark);
            color: var(--ios-text);
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .pill-btn:hover { background: var(--ios-border); }

        /* The Main Floating Command Bar (Dynamic Island style) */
        .command-bar {
            position: fixed;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(30, 30, 30, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: var(--radius-pill);
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--shadow-float);
            z-index: 100;
            transition: var(--transition);
        }
        .cmd-search {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.12);
            border-radius: var(--radius-pill);
            padding: 8px 16px;
            color: #fff;
            gap: 10px;
            width: 200px;
            transition: width 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        .cmd-search:focus-within {
            width: 320px;
            background: rgba(255,255,255,0.2);
        }
        .cmd-search input {
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            font-size: 15px;
            font-weight: 500;
            width: 100%;
            font-family: inherit;
        }
        .cmd-search input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        .cmd-divider {
            width: 1px;
            height: 24px;
            background: rgba(255,255,255,0.2);
            margin: 0 4px;
        }
        .cmd-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-size: 15px;
        }
        .cmd-btn:hover { background: rgba(255,255,255,0.15); }
        .cmd-btn-primary {
            background: #fff;
            color: #000;
            padding: 0 20px;
            height: 40px;
            border-radius: var(--radius-pill);
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: var(--transition);
            margin-left: 4px;
        }
        .cmd-btn-primary:hover { background: #e5e5ea; transform: scale(0.97); }

        /* Bulk Edit Overlay Bar */
        .bulk-toolbar {
            position: fixed;
            bottom: 100px; /* Floats above command bar */
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--ios-blue);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            padding: 12px 24px;
            border-radius: var(--radius-pill);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 20px;
            z-index: 90;
            box-shadow: var(--shadow-float);
            transition: var(--transition);
            opacity: 0;
            pointer-events: none;
        }
        .bulk-toolbar:not(.hidden) {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .badge-count {
            background: #fff;
            color: var(--ios-blue);
            font-size: 13px;
            font-weight: 700;
            height: 24px;
            min-width: 24px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
        }
        .bulk-cancel {
            background: transparent;
            color: rgba(255,255,255,0.8);
            border: none;
            font-size: 14px;
            cursor: pointer;
            padding: 8px;
            transition: var(--transition);
        }
        .bulk-cancel:hover { color: #fff; }
        .bulk-action {
            background: #fff;
            color: var(--ios-blue);
            border: none;
            border-radius: var(--radius-pill);
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .bulk-action:hover { transform: scale(0.97); }

        /* iOS Data Table */
        .ios-table-panel {
            background: var(--ios-surface);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--ios-border);
            overflow: hidden;
            position: relative;
        }
        .ios-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .ios-table th {
            padding: 16px 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ios-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--ios-border);
            background: var(--ios-surface);
        }
        .ios-table td {
            padding: 16px 20px;
            font-size: 15px;
            border-bottom: 1px solid var(--ios-border);
            vertical-align: middle;
            color: var(--ios-text);
        }
        .ios-table tr:last-child td { border-bottom: none; }
        .ios-table tbody tr { transition: background-color 0.2s; }
        .ios-table tbody tr:hover { background-color: var(--ios-bg); }

        /* Custom Circle Checkbox */
        .ios-checkbox {
            appearance: none;
            width: 22px;
            height: 22px;
            border: 1px solid var(--ios-border-dark);
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
            margin: 0;
            display: block;
        }
        .ios-checkbox:checked {
            background: var(--ios-blue);
            border-color: var(--ios-blue);
        }
        .ios-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 5px;
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* Product Thumb */
        .ios-thumb {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--ios-bg);
            border: 1px solid var(--ios-border);
            object-fit: cover;
        }

        /* Table Text Formatting */
        .tbl-title { font-weight: 600; font-size: 15px; color: var(--ios-text); margin-bottom: 2px; }
        .tbl-sub { font-size: 12px; color: var(--ios-text-secondary); }
        .tbl-num { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 14px; }
        
        /* Status Badges */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 600;
        }
        .dot { width: 6px; height: 6px; border-radius: 50%; }

        /* Row Actions */
        .row-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        .row-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--ios-bg);
            color: var(--ios-text-secondary);
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        .row-action-btn:hover { background: var(--ios-border-dark); color: var(--ios-text); }
        .row-action-btn.delete:hover { background: var(--ios-red-bg); color: var(--ios-red); }

        /* Table Loader */
        #table-wrapper { position: relative; }
        #table-loader {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(4px);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        /* Pagination Layout */
        .ios-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: var(--ios-surface);
            border-top: 1px solid var(--ios-border);
            border-radius: 0 0 var(--radius-card) var(--radius-card);
        }
        .page-info { font-size: 13px; color: var(--ios-text-secondary); }
        .page-controls { display: flex; align-items: center; gap: 16px; }
        .page-select {
            appearance: none;
            background: var(--ios-bg);
            border: 1px solid var(--ios-border);
            border-radius: 8px;
            padding: 6px 30px 6px 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238a8a8e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
        }
        .page-nav-group {
            display: flex;
            border: 1px solid var(--ios-border);
            border-radius: 8px;
            overflow: hidden;
        }
        .page-nav-btn {
            background: var(--ios-surface);
            border: none;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ios-text);
            cursor: pointer;
            border-left: 1px solid var(--ios-border);
            transition: background 0.2s;
        }
        .page-nav-btn:first-child { border-left: none; }
        .page-nav-btn:hover { background: var(--ios-bg); }
        .page-nav-btn.active { background: var(--ios-text); color: #fff; }

        /* Glassmorphism Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: var(--transition);
        }
        .modal-overlay:not(.hidden) {
            opacity: 1;
            pointer-events: auto;
        }
        .ios-modal {
            background: var(--ios-surface);
            width: 100%;
            max-width: 440px;
            border-radius: 24px;
            box-shadow: var(--shadow-float);
            transform: scale(0.95);
            transition: var(--transition);
            overflow: hidden;
        }
        .modal-overlay:not(.hidden) .ios-modal {
            transform: scale(1);
        }
        .ios-modal-header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid var(--ios-border);
            position: relative;
        }
        .ios-modal-title { font-size: 17px; font-weight: 600; margin: 0; }
        .ios-modal-close {
            position: absolute;
            right: 16px;
            top: 20px;
            background: var(--ios-bg);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ios-text-secondary);
            cursor: pointer;
        }
        .ios-modal-body { padding: 24px; }
        .ios-modal-footer {
            padding: 16px 24px;
            background: var(--ios-bg);
            display: flex;
            gap: 12px;
        }
        .modal-btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
            transition: transform 0.2s;
        }
        .modal-btn:hover { transform: scale(0.98); }
        .modal-btn.secondary { background: var(--ios-border); color: var(--ios-text); }
        .modal-btn.primary { background: var(--ios-text); color: #fff; }
        .modal-btn.danger { background: var(--ios-red); color: #fff; }

        /* Input fields in modals */
        .ios-input-stack { display: flex; flex-direction: column; gap: 16px; }
        .ios-input-field {
            background: var(--ios-bg);
            border: 1px solid var(--ios-border);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 15px;
            width: 100%;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        .ios-input-field:focus { border-color: var(--ios-blue); }

        /* Banner Alerts */
        .ios-alert {
            background: var(--ios-surface);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid transparent;
        }
        .ios-alert.success { border-left-color: var(--ios-green); }
        .ios-alert.error { border-left-color: var(--ios-red); }
    </style>
</head>
<body>

    <!-- Unified System Top Menu Bar -->
    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Form wrapper that encloses the filters AND the floating command bar -->
    <form id="filterForm" action="<?= APP_URL ?>/inventory" method="GET" onsubmit="event.preventDefault(); applyAjaxFilters();">
        <input type="hidden" name="page" id="currentPageInput" value="<?= $currentPage ?>">
        <input type="hidden" name="per_page" id="perPageInput" value="<?= $perPage ?>">

        <div class="workspace-core">
            
            <!-- Hero Header & Stats -->
            <div class="ios-hero">
                <div>
                    <h1 class="ios-title">Inventory</h1>
                    <p class="ios-subtitle">Manage your catalog, pricing, and stock levels.</p>
                </div>
                <div class="ios-stats-row">
                    <div class="ios-stat-widget">
                        <span class="ios-stat-val" id="stat-total-items"><?= number_format($stats->total_items) ?></span>
                        <span class="ios-stat-label">Total</span>
                    </div>
                    <div class="ios-stat-widget warning">
                        <span class="ios-stat-val" id="stat-low-stock"><?= number_format($stats->low_stock_count) ?></span>
                        <span class="ios-stat-label">Low Stock</span>
                    </div>
                    <div class="ios-stat-widget danger">
                        <span class="ios-stat-val" id="stat-out-of-stock"><?= number_format($stats->out_of_stock_count) ?></span>
                        <span class="ios-stat-label">Out of Stock</span>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
                <div id="flash-success-alert" class="ios-alert success">
                    <i class="fa-solid fa-circle-check" style="color: var(--ios-green); font-size: 20px;"></i>
                    <div style="flex-grow: 1;">
                        <div style="font-weight: 600; font-size: 14px;">Success</div>
                        <div style="font-size: 13px; color: var(--ios-text-secondary);"><?= htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? '') ?></div>
                    </div>
                    <button type="button" onclick="document.getElementById('flash-success-alert').style.display='none'" style="background:none; border:none; color: var(--ios-text-secondary); cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div id="flash-error-alert" class="ios-alert error">
                    <i class="fa-solid fa-circle-exclamation" style="color: var(--ios-red); font-size: 20px;"></i>
                    <div style="flex-grow: 1;">
                        <div style="font-weight: 600; font-size: 14px;">Error</div>
                        <div style="font-size: 13px; color: var(--ios-text-secondary);"><?= htmlspecialchars($flashError) ?></div>
                    </div>
                    <button type="button" onclick="document.getElementById('flash-error-alert').style.display='none'" style="background:none; border:none; color: var(--ios-text-secondary); cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- Table Panel & Inline Filters -->
            <div class="ios-table-panel">
                
                <div style="padding: 20px 20px 0 20px;">
                    <!-- Sleek Pill Filters -->
                    <div class="pill-filters">
                        <div class="pill-item">
                            <span style="color: var(--ios-text-secondary);">Category:</span>
                            <select name="category_id" id="categorySelect" onchange="applyAjaxFilters()">
                                <option value="">All</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>" <?= (string)$filters['category_id'] === (string)$cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="pill-item">
                            <span style="color: var(--ios-text-secondary);">Status:</span>
                            <select name="stock_status" id="stockStatusSelect" onchange="applyAjaxFilters()">
                                <option value="">All Statuses</option>
                                <option value="instock" <?= $filters['stock_status'] === 'instock' ? 'selected' : '' ?>>In Stock</option>
                                <option value="lowstock" <?= $filters['stock_status'] === 'lowstock' ? 'selected' : '' ?>>Low Stock</option>
                                <option value="outstock" <?= $filters['stock_status'] === 'outstock' ? 'selected' : '' ?>>Out of Stock</option>
                            </select>
                        </div>
                        <div class="pill-item">
                            <span style="color: var(--ios-text-secondary);">Min Rs:</span>
                            <input type="number" step="0.01" name="min_price" id="minPriceInput" value="<?= htmlspecialchars($filters['min_price']) ?>" oninput="triggerSearchDelay()" placeholder="0.00">
                        </div>
                        <div class="pill-item">
                            <span style="color: var(--ios-text-secondary);">Max Rs:</span>
                            <input type="number" step="0.01" name="max_price" id="maxPriceInput" value="<?= htmlspecialchars($filters['max_price']) ?>" oninput="triggerSearchDelay()" placeholder="0.00">
                        </div>
                        <button type="button" onclick="clearAllFilters()" class="pill-btn">Reset</button>

                        <div style="margin-left: auto; font-size: 13px; color: var(--ios-text-secondary); font-weight: 500;">
                            <span id="matching-count" style="color: var(--ios-text); font-weight: 600;"><?= $totalItems ?></span> items
                        </div>
                    </div>
                </div>

                <!-- Table Content Wrapper -->
                <div id="table-wrapper">
                    <div id="table-loader" class="pointer-events-none">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--ios-text-secondary);"></i>
                    </div>

                    <div id="table-container">
                        <div class="table-container">
                            <table class="ios-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px; text-align: center;">
                                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="ios-checkbox">
                                        </th>
                                        <th style="width: 300px;">Product</th>
                                        <th style="text-align: right;">Retail LKR</th>
                                        <th style="text-align: right;">B2B LKR</th>
                                        <th style="text-align: center;">Qty</th>
                                        <th>Status</th>
                                        <th style="text-align: right;">Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($items)): ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 60px 20px;">
                                                <i class="fa-solid fa-cube" style="font-size: 32px; color: var(--ios-border-dark); margin-bottom: 12px;"></i>
                                                <div style="font-weight: 600; font-size: 15px;">No Products Found</div>
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
                                            
                                            $img_src = empty($image) ? 'https://placehold.co/100x100?text=No+Img' : APP_URL . '/uploads/products/' . basename($image);

                                            if ($qty <= 0) {
                                                $statusBadge = '<span class="badge-status" style="background: var(--ios-red-bg); color: var(--ios-red);"><span class="dot" style="background: var(--ios-red);"></span>Out</span>';
                                            } elseif ($qty <= 5) {
                                                $statusBadge = '<span class="badge-status" style="background: var(--ios-orange-bg); color: #d97706;"><span class="dot" style="background: var(--ios-orange);"></span>Low</span>';
                                            } else {
                                                $statusBadge = '<span class="badge-status" style="background: var(--ios-green-bg); color: #15803d;"><span class="dot" style="background: var(--ios-green);"></span>Active</span>';
                                            }
                                            ?>
                                            <tr>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" name="selected_items[]" value="<?= $item->id ?>" class="item-select-checkbox ios-checkbox" onchange="updateSelection()">
                                                </td>
                                                <td>
                                                    <div class="flex-h gap-3">
                                                        <img src="<?= $img_src ?>" class="ios-thumb" onerror="this.src='https://placehold.co/100x100?text=Err'">
                                                        <div>
                                                            <div class="tbl-title"><?= htmlspecialchars($item->name ?? 'Unnamed Item') ?></div>
                                                            <div class="tbl-sub">SKU: <?= htmlspecialchars($sku) ?> <?= !empty($item->sample_code) ? '• Code: ' . htmlspecialchars($item->sample_code) : '' ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="text-align: right;" class="tbl-num"><?= number_format($price, 2) ?></td>
                                                <td style="text-align: right; color: var(--ios-text-secondary);" class="tbl-num"><?= number_format($b2b_price, 2) ?></td>
                                                <td style="text-align: center;" class="tbl-num">
                                                    <span style="font-weight: 600; color: <?= $qty <= 0 ? 'var(--ios-red)' : ($qty <= 5 ? 'var(--ios-orange)' : 'var(--ios-text)') ?>;"><?= $qty ?></span>
                                                </td>
                                                <td><?= $statusBadge ?></td>
                                                <td>
                                                    <div class="row-actions">
                                                        <a href="<?= APP_URL ?>/stockledger/product/<?= $item->id ?>" class="row-action-btn" title="Ledger"><i class="fa-solid fa-chart-line"></i></a>
                                                        <a href="<?= APP_URL ?>/inventory/edit/<?= $item->id ?>" class="row-action-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                                        <button type="button" onclick="confirmDelete(<?= $item->id ?>, '<?= htmlspecialchars(addslashes($item->name)) ?>')" class="row-action-btn delete" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Footer -->
                        <div class="ios-pagination">
                            <div class="page-info">
                                Viewing <strong><?= $startIndex ?></strong> to <strong><?= $endIndex ?></strong> of <strong><?= $totalItems ?></strong>
                            </div>
                            <div class="page-controls">
                                <select onchange="updatePageSize(this.value)" class="page-select">
                                    <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                                    <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / page</option>
                                    <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                                    <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
                                </select>
                                
                                <div class="page-nav-group">
                                    <button type="button" onclick="navigatePage(<?= max(1, $currentPage - 1) ?>)" class="page-nav-btn" <?= $currentPage <= 1 ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i></button>
                                    
                                    <?php 
                                    $range = 1;
                                    $startPage = max(1, $currentPage - $range);
                                    $endPage = min($totalPages, $currentPage + $range);

                                    if ($startPage > 1) {
                                        echo '<button type="button" onclick="navigatePage(1)" class="page-nav-btn">1</button>';
                                        if ($startPage > 2) {
                                            echo '<div class="page-nav-btn pointer-events-none" style="color: var(--ios-text-secondary);">...</div>';
                                        }
                                    }

                                    for ($i = $startPage; $i <= $endPage; $i++) {
                                        if ($i === $currentPage) {
                                            echo '<div class="page-nav-btn active">'.$i.'</div>';
                                        } else {
                                            echo '<button type="button" onclick="navigatePage('.$i.')" class="page-nav-btn">'.$i.'</button>';
                                        }
                                    }

                                    if ($endPage < $totalPages) {
                                        if ($endPage < $totalPages - 1) {
                                            echo '<div class="page-nav-btn pointer-events-none" style="color: var(--ios-text-secondary);">...</div>';
                                        }
                                        echo '<button type="button" onclick="navigatePage('.$totalPages.')" class="page-nav-btn">'.$totalPages.'</button>';
                                    }
                                    ?>
                                    <button type="button" onclick="navigatePage(<?= min($totalPages, $currentPage + 1) ?>)" class="page-nav-btn" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- THE DYNAMIC ISLAND: Floating Command Bar -->
        <div class="command-bar">
            <div class="cmd-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($filters['search']) ?>" oninput="triggerSearchDelay()" placeholder="Search catalog...">
            </div>
            <div class="cmd-divider"></div>
            <a href="<?= APP_URL ?>/inventory/exportCSV" class="cmd-btn" title="Export CSV"><i class="fa-solid fa-arrow-down-to-bracket"></i></a>
            <button type="button" onclick="openCsvModal()" class="cmd-btn" title="Import CSV"><i class="fa-solid fa-arrow-up-from-bracket"></i></button>
            <a href="<?= APP_URL ?>/inventory/add" class="cmd-btn-primary">
                <i class="fa-solid fa-plus"></i> New
            </a>
        </div>
    </form>

    <!-- Modals Layer -->

    <!-- CSV Import Modal -->
    <div id="csvImportModal" class="modal-overlay hidden">
        <div class="ios-modal">
            <div class="ios-modal-header">
                <h3 class="ios-modal-title">Import Data</h3>
                <button type="button" onclick="closeCsvModal()" class="ios-modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="ios-modal-body" id="import-tab-erp">
                <form action="<?= APP_URL ?>/inventory/importERPCSV" method="POST" enctype="multipart/form-data">
                    <div style="border: 2px dashed var(--ios-border-dark); border-radius: 16px; padding: 32px 20px; text-align: center; background: var(--ios-bg); cursor: pointer; position: relative; margin-bottom: 24px;">
                        <input type="file" name="csv_file" accept=".csv" required style="position: absolute; inset: 0; opacity: 0; cursor: pointer;">
                        <i class="fa-solid fa-file-csv" style="font-size: 32px; color: var(--ios-blue); margin-bottom: 12px;"></i>
                        <div style="font-size: 15px; font-weight: 600;">Drag and drop file</div>
                        <div style="font-size: 13px; color: var(--ios-text-secondary); margin-top: 4px;">.csv format supported</div>
                    </div>
                    <div style="background: var(--ios-surface); border: 1px solid var(--ios-border); border-radius: 12px; padding: 16px;">
                        <div style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: flex; gap: 8px; align-items: center;">
                            <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--ios-blue);"></i> Auto Mapping
                        </div>
                        <div style="font-size: 13px; color: var(--ios-text-secondary); line-height: 1.4;">The system dynamically resolves categories, warehouses, and relations based on SKUs.</div>
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                        <button type="button" onclick="closeCsvModal()" class="modal-btn secondary">Cancel</button>
                        <button type="submit" class="modal-btn primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteProductModal" class="modal-overlay hidden">
        <div class="ios-modal">
            <div class="ios-modal-header">
                <h3 class="ios-modal-title" style="color: var(--ios-red);">Delete Product</h3>
                <button type="button" onclick="closeDeleteModal()" class="ios-modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="deleteProductForm" onsubmit="submitDeleteProduct(event)">
                <div class="ios-modal-body">
                    <input type="hidden" id="deleteItemId" name="item_id">
                    
                    <p style="font-size: 15px; line-height: 1.5; text-align: center; margin: 0 0 24px 0;">
                        Delete <strong id="deleteItemName"></strong> permanently?<br>
                        <span style="color: var(--ios-text-secondary); font-size: 13px;">This action cannot be undone.</span>
                    </p>

                    <div id="deleteErrorContainer" class="hidden" style="padding: 12px; background: var(--ios-red-bg); color: var(--ios-red); border-radius: 12px; font-size: 13px; font-weight: 600; text-align: center; margin-bottom: 24px;"></div>

                    <?php if (!$isCurrentlyAdmin): ?>
                        <div class="ios-input-stack">
                            <input type="text" name="admin_username" id="deleteAdminUsername" required class="ios-input-field" placeholder="Admin Username">
                            <input type="password" name="password" id="deleteAdminPassword" required class="ios-input-field" placeholder="Admin Password">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ios-modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="modal-btn secondary">Cancel</button>
                    <button type="submit" id="deleteSubmitBtn" class="modal-btn danger">
                        <i id="deleteBtnSpinner" class="fa-solid fa-spinner fa-spin hidden"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Edit Toolbar (Floats above command bar) -->
    <div id="bulkEditToolbar" class="bulk-toolbar hidden">
        <div class="flex-h gap-3">
            <div class="badge-count" id="selectedCountBadge">0</div>
            <span style="font-size: 14px; font-weight: 600;">Selected</span>
        </div>
        <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.3);"></div>
        <div class="flex-h gap-2">
            <button type="button" onclick="clearSelection()" class="bulk-cancel">Cancel</button>
            <button type="button" onclick="openBulkEditModal()" class="bulk-action"><i class="fa-solid fa-pen"></i> Edit</button>
        </div>
    </div>

    <!-- Bulk Edit Modal -->
    <div id="bulkEditModal" class="modal-overlay hidden">
        <div class="ios-modal">
            <div class="ios-modal-header">
                <h3 class="ios-modal-title">Bulk Edit</h3>
                <button type="button" onclick="closeBulkEditModal()" class="ios-modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="bulkEditForm" onsubmit="submitBulkEdit(event)">
                <div class="ios-modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <div style="text-align: center; font-size: 14px; color: var(--ios-text-secondary); margin-bottom: 24px;">
                        Editing <strong id="bulkSelectedCount" style="color: var(--ios-text);">0</strong> products
                    </div>

                    <div id="bulkEditErrorContainer" class="hidden" style="padding: 12px; background: var(--ios-red-bg); color: var(--ios-red); border-radius: 12px; font-size: 13px; font-weight: 600; text-align: center; margin-bottom: 24px;"></div>

                    <div class="ios-input-stack">
                        <!-- Category -->
                        <div style="background: var(--ios-bg); border-radius: 12px; padding: 16px;">
                            <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                                <input type="checkbox" name="update_category" value="1" id="bulkUpdateCategory" onchange="toggleBulkField('category')" class="ios-checkbox">
                                <span style="font-size: 15px; font-weight: 600;">Category</span>
                            </label>
                            <select name="category_id" id="bulkCategorySelect" disabled class="ios-input-field" style="background: var(--ios-surface);">
                                <option value="">No Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Retail Price -->
                        <div style="background: var(--ios-bg); border-radius: 12px; padding: 16px;">
                            <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                                <input type="checkbox" name="update_selling_price" value="1" id="bulkUpdateSellingPrice" onchange="toggleBulkField('selling_price')" class="ios-checkbox">
                                <span style="font-size: 15px; font-weight: 600;">Retail Price</span>
                            </label>
                            <div class="flex-h gap-2">
                                <select name="selling_price_type" id="bulkSellingPriceType" disabled class="ios-input-field" style="width: 40%; background: var(--ios-surface); padding: 12px 8px;">
                                    <option value="flat">Flat Val</option>
                                    <option value="pct_inc">+ %</option>
                                    <option value="pct_dec">- %</option>
                                </select>
                                <input type="number" step="0.01" name="selling_price_val" id="bulkSellingPriceVal" disabled class="ios-input-field font-mono" style="background: var(--ios-surface);" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Wholesale Price -->
                        <div style="background: var(--ios-bg); border-radius: 12px; padding: 16px;">
                            <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                                <input type="checkbox" name="update_wholesale_price" value="1" id="bulkUpdateWholesalePrice" onchange="toggleBulkField('wholesale_price')" class="ios-checkbox">
                                <span style="font-size: 15px; font-weight: 600;">B2B Price</span>
                            </label>
                            <div class="flex-h gap-2">
                                <select name="wholesale_price_type" id="bulkWholesalePriceType" disabled class="ios-input-field" style="width: 40%; background: var(--ios-surface); padding: 12px 8px;">
                                    <option value="flat">Flat Val</option>
                                    <option value="pct_inc">+ %</option>
                                    <option value="pct_dec">- %</option>
                                </select>
                                <input type="number" step="0.01" name="wholesale_price_val" id="bulkWholesalePriceVal" disabled class="ios-input-field font-mono" style="background: var(--ios-surface);" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Status -->
                        <div style="background: var(--ios-bg); border-radius: 12px; padding: 16px;">
                            <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                                <input type="checkbox" name="update_status" value="1" id="bulkUpdateStatus" onchange="toggleBulkField('status')" class="ios-checkbox">
                                <span style="font-size: 15px; font-weight: 600;">Status</span>
                            </label>
                            <select name="status" id="bulkStatusSelect" disabled class="ios-input-field" style="background: var(--ios-surface);">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ios-modal-footer">
                    <button type="button" onclick="closeBulkEditModal()" class="modal-btn secondary">Cancel</button>
                    <button type="submit" id="bulkSubmitBtn" class="modal-btn primary">
                        <i id="bulkBtnSpinner" class="fa-solid fa-spinner fa-spin hidden"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Engine (Unmodified API Contracts) -->
    <script>
        let searchTimeout = null;

        function triggerSearchDelay() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('currentPageInput').value = '1';
                applyAjaxFilters();
            }, 350);
        }

        function applyAjaxFilters() {
            const form = document.getElementById('filterForm');
            const loader = document.getElementById('table-loader');

            if (loader) {
                loader.classList.remove('pointer-events-none');
                loader.classList.add('opacity-100');
            }

            const formData = new FormData(form);
            
            // To prevent appending massive selected_items array to URL during standard filter/search
            const paramsToKeep = new FormData();
            for (let [key, value] of formData.entries()) {
                if (key !== 'selected_items[]') {
                    paramsToKeep.append(key, value);
                }
            }
            
            const queryParams = new URLSearchParams(paramsToKeep).toString();
            const requestUrl = form.getAttribute('action') + '?' + queryParams;

            fetch(requestUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network error during inventory retrieval');
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
                        if (newVal && oldVal) oldVal.innerHTML = newVal.innerHTML;
                    };
                    updateStat('stat-total-items');
                    updateStat('stat-low-stock');
                    updateStat('stat-out-of-stock');
                    updateStat('matching-count');

                    window.history.pushState({ path: requestUrl }, '', requestUrl);
                    clearSelection();
                })
                .catch(err => {
                    console.error('Asynchronous Sync Error:', err);
                })
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
                if (!response.ok) throw new Error('Failed to authorize deletion');
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

            if (selectedIds.length > 0) {
                if (countBadge) countBadge.textContent = selectedIds.length;
                if (toolbar) toolbar.classList.remove('hidden');
                if (selectAllCb) {
                    selectAllCb.checked = (selectedIds.length === checkboxes.length);
                }
            } else {
                if (toolbar) toolbar.classList.add('hidden');
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