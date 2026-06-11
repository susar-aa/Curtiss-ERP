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
           APPLE-INSPIRED MINIMAL BLACK & WHITE DESIGN SYSTEM
           ========================================================================== */
        :root {
            --apple-bg: #fbfbfd;
            --apple-surface: #ffffff;
            --apple-text: #1d1d1f;
            --apple-text-muted: #86868b;
            --apple-border: #d2d2d7;
            --apple-border-light: #e5e5ea;
            --apple-black: #000000;
            --apple-black-hover: #333333;
            --apple-blue: #0071e3;
            --apple-red: #ff3b30;
            --apple-red-bg: #fff0f0;
            --apple-orange: #f59e0b;
            --apple-orange-bg: #fffbeb;
            --apple-green: #34c759;
            --apple-green-bg: #f0fdf4;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.06);
            --shadow-modal: 0 24px 48px rgba(0,0,0,0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-xl: 24px;
            --transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
        }

        body {
            background-color: var(--apple-bg) !important;
            color: var(--apple-text) !important;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Override scrolling engine */
        html, body {
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        /* Structure */
        .workspace-container {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
            flex-grow: 1;
        }

        /* Typography */
        .t-hero { font-size: 36px; font-weight: 700; letter-spacing: -0.04em; margin: 0; color: var(--apple-black); }
        .t-sub { font-size: 15px; font-weight: 400; color: var(--apple-text-muted); margin-top: 6px; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important; }

        /* Helpers */
        .flex-h { display: flex; align-items: center; }
        .flex-v { display: flex; flex-direction: column; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .hidden { display: none !important; }
        .opacity-100 { opacity: 1 !important; }
        .pointer-events-none { pointer-events: none !important; }
        .text-danger { color: var(--apple-red); }
        .text-warning { color: var(--apple-orange); }
        .text-success { color: var(--apple-green); }
        .text-muted { color: var(--apple-text-muted); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-xl);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }
        .btn-dark { background: var(--apple-black); color: #fff; }
        .btn-dark:hover { background: var(--apple-black-hover); transform: scale(0.98); }
        .btn-light { background: var(--apple-surface); color: var(--apple-text); border: 1px solid var(--apple-border); }
        .btn-light:hover { background: var(--apple-bg); border-color: var(--apple-text-muted); }
        .btn-danger { background: var(--apple-red); color: #fff; }
        .btn-danger:hover { opacity: 0.9; transform: scale(0.98); }
        .btn-icon-only {
            padding: 8px;
            border-radius: 50%;
            color: var(--apple-text-muted);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-icon-only:hover { background: var(--apple-bg); color: var(--apple-black); }
        .btn-icon-only.hover-danger:hover { background: var(--apple-red-bg); color: var(--apple-red); }

        /* Panels & Cards */
        .panel {
            background: var(--apple-surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--apple-border-light);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            box-sizing: border-box;
            transition: var(--transition);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 20px;
            margin-top: 32px;
        }
        @media(min-width: 768px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
        .stat-card {
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--apple-text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-value { font-size: 40px; font-weight: 700; margin-top: 8px; letter-spacing: -0.03em; color: var(--apple-black); }

        /* Forms & Inputs */
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: end;
        }
        @media(min-width: 768px) { .filter-grid { grid-template-columns: 2.5fr 1fr 1fr 1.5fr 1.5fr; } }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-label { font-size: 12px; font-weight: 600; color: var(--apple-text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .input-field {
            background: var(--apple-bg);
            border: 1px solid transparent;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--apple-text);
            transition: var(--transition);
            width: 100%;
            box-sizing: border-box;
            font-family: inherit;
        }
        .input-field:focus {
            outline: none;
            background: var(--apple-surface);
            border-color: var(--apple-black);
            box-shadow: 0 0 0 4px rgba(0,0,0,0.04);
        }
        select.input-field {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px;
            cursor: pointer;
        }

        /* Checkbox Custom */
        .apple-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 1px solid var(--apple-border);
            border-radius: 6px;
            background: var(--apple-surface);
            cursor: pointer;
            position: relative;
            transition: var(--transition);
            margin: 0;
        }
        .apple-checkbox:checked {
            background: var(--apple-blue);
            border-color: var(--apple-blue);
        }
        .apple-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* Table */
        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
        }
        .data-table th {
            padding: 16px 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--apple-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--apple-border-light);
            background: var(--apple-surface);
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .data-table td {
            padding: 16px 20px;
            font-size: 14px;
            border-bottom: 1px solid var(--apple-border-light);
            vertical-align: middle;
        }
        .data-table tbody tr {
            transition: background-color 0.15s ease;
        }
        .data-table tbody tr:hover {
            background-color: var(--apple-bg);
        }
        .data-table tbody tr:last-child td { border-bottom: none; }

        .img-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: var(--apple-bg);
            border: 1px solid var(--apple-border-light);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; }

        /* Loaders & Wrappers */
        #table-wrapper { position: relative; padding: 0; overflow: hidden; }
        #table-loader {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            animation: slideDown 0.3s ease-out forwards;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-success { background: var(--apple-green-bg); color: #166534; border: 1px solid #dcfce7; }
        .alert-error { background: var(--apple-red-bg); color: #991b1b; border: 1px solid #fee2e2; }

        /* Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 999;
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
        .modal-card {
            background: var(--apple-surface);
            width: 100%;
            max-width: 480px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-modal);
            transform: scale(0.95) translateY(10px);
            transition: var(--transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal-overlay:not(.hidden) .modal-card {
            transform: scale(1) translateY(0);
        }
        .modal-header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--apple-border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title { font-size: 18px; font-weight: 700; color: var(--apple-black); display: flex; align-items: center; gap: 8px; }
        .modal-body { padding: 32px; }
        .modal-footer {
            padding: 20px 32px;
            background: var(--apple-bg);
            border-top: 1px solid var(--apple-border-light);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* Floating Bulk Toolbar */
        .bulk-toolbar {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(29, 29, 31, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 16px 24px;
            border-radius: 100px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 24px;
            z-index: 900;
            box-shadow: var(--shadow-modal);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            opacity: 0;
            pointer-events: none;
        }
        .bulk-toolbar:not(.hidden) {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .badge-count {
            background: var(--apple-blue);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            min-width: 24px;
            height: 24px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
        }
    </style>
</head>
<body>

    <!-- Unified System Top Menu Bar -->
    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Main Workspace Container -->
    <div class="workspace-container">
        
        <!-- Header -->
        <div class="flex-h justify-between" style="margin-bottom: 32px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 class="t-hero">Inventory Catalog</h1>
                <p class="t-sub">View physical stock, track alert levels, and manage parameters.</p>
            </div>
            <div class="flex-h gap-3" style="flex-wrap: wrap;">
                <a href="<?= APP_URL ?>/inventory/exportCSV" class="btn btn-light"><i class="fa-solid fa-arrow-down-to-bracket"></i> Export</a>
                <button type="button" onclick="openCsvModal()" class="btn btn-light"><i class="fa-solid fa-arrow-up-from-bracket"></i> Import</button>
                <a href="<?= APP_URL ?>/inventory/add" class="btn btn-dark"><i class="fa-solid fa-plus"></i> Add Product</a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
            <div id="flash-success-alert" class="alert alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 20px; margin-top: 2px;"></i>
                <div style="flex-grow: 1;">
                    <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">Action Successful</div>
                    <div style="font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? '') ?></div>
                </div>
                <button onclick="document.getElementById('flash-success-alert').style.display='none'" class="btn-icon-only" style="margin: -8px; width: 32px; height: 32px;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div id="flash-error-alert" class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 20px; margin-top: 2px;"></i>
                <div style="flex-grow: 1;">
                    <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">Action Failed</div>
                    <div style="font-size: 13px; opacity: 0.9;"><?= htmlspecialchars($flashError) ?></div>
                </div>
                <button onclick="document.getElementById('flash-error-alert').style.display='none'" class="btn-icon-only" style="margin: -8px; width: 32px; height: 32px;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <?php if ($importResults): ?>
            <div id="import-results-panel" class="panel" style="margin-bottom: 32px;">
                <div class="flex-h justify-between" style="border-bottom: 1px solid var(--apple-border-light); padding-bottom: 16px; margin-bottom: 20px;">
                    <div class="flex-h gap-3">
                        <i class="fa-solid fa-file-circle-check text-success" style="font-size: 24px;"></i>
                        <div>
                            <div style="font-weight: 700; font-size: 15px;">CSV Import Completed</div>
                            <div style="font-size: 13px; color: var(--apple-text-muted);">Processed all records from your inventory data.</div>
                        </div>
                    </div>
                    <button onclick="document.getElementById('import-results-panel').style.display='none'" class="btn-icon-only"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                    <div style="padding: 16px; background: var(--apple-green-bg); border-radius: 12px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: #166534; text-transform: uppercase;">Added</div>
                        <div style="font-size: 24px; font-weight: 700; color: #166534; margin-top: 4px; font-family: monospace;"><?= $importResults['added'] ?></div>
                    </div>
                    <div style="padding: 16px; background: #eff6ff; border-radius: 12px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: #1d4ed8; text-transform: uppercase;">Updated</div>
                        <div style="font-size: 24px; font-weight: 700; color: #1d4ed8; margin-top: 4px; font-family: monospace;"><?= $importResults['updated'] ?></div>
                    </div>
                    <div style="padding: 16px; background: var(--apple-orange-bg); border-radius: 12px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: #b45309; text-transform: uppercase;">Relations</div>
                        <div style="font-size: 24px; font-weight: 700; color: #b45309; margin-top: 4px; font-family: monospace;"><?= count($importResults['success_logs']) ?></div>
                    </div>
                    <div style="padding: 16px; background: var(--apple-red-bg); border-radius: 12px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 600; color: #991b1b; text-transform: uppercase;">Errors</div>
                        <div style="font-size: 24px; font-weight: 700; color: #991b1b; margin-top: 4px; font-family: monospace;"><?= count($importResults['errors']) ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="panel stat-card">
                <span class="stat-label">Total Catalog Items</span>
                <span class="stat-value" id="stat-total-items"><?= number_format($stats->total_items) ?></span>
                <i class="fa-solid fa-layer-group stat-icon" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; opacity: 0.03;"></i>
            </div>
            <div class="panel stat-card">
                <span class="stat-label">Low Stock Alerts</span>
                <span class="stat-value text-warning" id="stat-low-stock"><?= number_format($stats->low_stock_count) ?></span>
                <i class="fa-solid fa-triangle-exclamation stat-icon" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; opacity: 0.03; color: var(--apple-orange);"></i>
            </div>
            <div class="panel stat-card">
                <span class="stat-label">Out of Stock</span>
                <span class="stat-value text-danger" id="stat-out-of-stock"><?= number_format($stats->out_of_stock_count) ?></span>
                <i class="fa-solid fa-ban stat-icon" style="position: absolute; right: -10px; bottom: -10px; font-size: 80px; opacity: 0.03; color: var(--apple-red);"></i>
            </div>
        </div>

        <!-- Smart Filter Form -->
        <div class="panel" style="margin-top: 24px;">
            <form id="filterForm" action="<?= APP_URL ?>/inventory" method="GET" onsubmit="event.preventDefault(); applyAjaxFilters();">
                <input type="hidden" name="page" id="currentPageInput" value="<?= $currentPage ?>">
                <input type="hidden" name="per_page" id="perPageInput" value="<?= $perPage ?>">

                <div class="filter-grid">
                    <div class="input-group">
                        <label class="input-label">Search Catalog</label>
                        <div style="position: relative;">
                            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 14px; color: var(--apple-text-muted); font-size: 14px;"></i>
                            <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($filters['search']) ?>" oninput="triggerSearchDelay()" class="input-field" placeholder="Search by SKU, Name..." style="padding-left: 40px;">
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Min Price (Rs.)</label>
                        <input type="number" step="0.01" name="min_price" id="minPriceInput" value="<?= htmlspecialchars($filters['min_price']) ?>" oninput="triggerSearchDelay()" class="input-field font-mono" placeholder="0.00">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Max Price (Rs.)</label>
                        <input type="number" step="0.01" name="max_price" id="maxPriceInput" value="<?= htmlspecialchars($filters['max_price']) ?>" oninput="triggerSearchDelay()" class="input-field font-mono" placeholder="0.00">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Category</label>
                        <select name="category_id" id="categorySelect" onchange="applyAjaxFilters()" class="input-field">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>" <?= (string)$filters['category_id'] === (string)$cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Availability</label>
                        <select name="stock_status" id="stockStatusSelect" onchange="applyAjaxFilters()" class="input-field">
                            <option value="">All Statuses</option>
                            <option value="instock" <?= $filters['stock_status'] === 'instock' ? 'selected' : '' ?>>In Stock (> 5)</option>
                            <option value="lowstock" <?= $filters['stock_status'] === 'lowstock' ? 'selected' : '' ?>>Low Stock (1-5)</option>
                            <option value="outstock" <?= $filters['stock_status'] === 'outstock' ? 'selected' : '' ?>>Out of Stock (0)</option>
                        </select>
                    </div>
                </div>

                <div class="flex-h justify-between" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--apple-border-light);">
                    <span class="text-muted" style="font-size: 13px;">Displaying <strong id="matching-count" style="color: var(--apple-black); font-family: monospace; font-size: 14px;"><?= $totalItems ?></strong> matching results</span>
                    <div class="flex-h gap-2">
                        <button type="button" onclick="clearAllFilters()" class="btn btn-light" style="padding: 8px 16px; border-radius: 8px;">Clear Reset</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Data Table Section -->
        <div class="panel" id="table-wrapper" style="margin-top: 24px; padding: 0;">
            
            <div id="table-loader" class="pointer-events-none">
                <div class="flex-v items-center gap-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-muted" style="font-size: 28px;"></i>
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--apple-text-muted);">Syncing...</span>
                </div>
            </div>

            <div id="table-container">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="apple-checkbox">
                                </th>
                                <th style="width: 80px;">Media</th>
                                <th style="width: 140px;">SKU / Sample</th>
                                <th>Product Name & Info</th>
                                <th style="text-align: right; width: 120px;">Retail (LKR)</th>
                                <th style="text-align: right; width: 120px;">B2B (LKR)</th>
                                <th style="text-align: center; width: 100px;">Units</th>
                                <th style="width: 120px;">State</th>
                                <th style="text-align: right; width: 140px;">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 80px 20px;">
                                        <div style="display: inline-flex; width: 64px; height: 64px; background: var(--apple-bg); border-radius: 50%; align-items: center; justify-content: center; margin-bottom: 16px;">
                                            <i class="fa-solid fa-cube text-muted" style="font-size: 24px;"></i>
                                        </div>
                                        <div style="font-size: 16px; font-weight: 600; color: var(--apple-black);">No Products Found</div>
                                        <div style="font-size: 14px; color: var(--apple-text-muted); margin-top: 4px;">Try adjusting your search or filters to find what you're looking for.</div>
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
                                        $statusBadge = '<span class="status-badge" style="background: var(--apple-red-bg); color: #991b1b; border: 1px solid #fee2e2;"><span class="status-dot bg-danger"></span>Out</span>';
                                    } elseif ($qty <= 5) {
                                        $statusBadge = '<span class="status-badge" style="background: var(--apple-orange-bg); color: #9a3412; border: 1px solid #ffedd5;"><span class="status-dot bg-warning"></span>Low</span>';
                                    } else {
                                        $statusBadge = '<span class="status-badge" style="background: var(--apple-green-bg); color: #166534; border: 1px solid #dcfce7;"><span class="status-dot bg-success"></span>Active</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="checkbox" name="selected_items[]" value="<?= $item->id ?>" class="item-select-checkbox apple-checkbox" onchange="updateSelection()">
                                        </td>
                                        <td>
                                            <div class="img-box">
                                                <img src="<?= $img_src ?>" onerror="this.src='https://placehold.co/100x100?text=Err'">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="font-mono" style="font-size: 13px; font-weight: 600; color: var(--apple-black);"><?= htmlspecialchars($sku) ?></div>
                                            <div class="font-mono" style="font-size: 11px; color: var(--apple-text-muted); margin-top: 4px;"><?= htmlspecialchars($item->sample_code ?? '-') ?></div>
                                        </td>
                                        <td>
                                            <div style="font-size: 14px; font-weight: 600; color: var(--apple-black); margin-bottom: 4px;"><?= htmlspecialchars($item->name ?? 'Unnamed Item') ?></div>
                                            <?php if (!empty($item->description)): ?>
                                                <div style="font-size: 12px; color: var(--apple-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;">
                                                    <?= htmlspecialchars($item->description) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="font-mono" style="font-size: 14px; font-weight: 600;"><?= number_format($price, 2) ?></div>
                                        </td>
                                        <td style="text-align: right; background: #f8fafc;">
                                            <div class="font-mono" style="font-size: 14px; font-weight: 600; color: #334155;"><?= number_format($b2b_price, 2) ?></div>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="font-mono" style="font-size: 15px; font-weight: 700; color: <?= $qty <= 0 ? 'var(--apple-red)' : ($qty <= 5 ? 'var(--apple-orange)' : 'var(--apple-black)') ?>;"><?= $qty ?></div>
                                        </td>
                                        <td><?= $statusBadge ?></td>
                                        <td style="text-align: right;">
                                            <div class="flex-h justify-end gap-2">
                                                <a href="<?= APP_URL ?>/stockledger/product/<?= $item->id ?>" class="btn-icon-only" title="Ledger"><i class="fa-solid fa-chart-line"></i></a>
                                                <a href="<?= APP_URL ?>/inventory/edit/<?= $item->id ?>" class="btn-icon-only" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                                <button onclick="confirmDelete(<?= $item->id ?>, '<?= htmlspecialchars(addslashes($item->name)) ?>')" class="btn-icon-only hover-danger" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="flex-h justify-between" style="padding: 16px 24px; border-top: 1px solid var(--apple-border-light); background: var(--apple-bg); flex-wrap: wrap; gap: 16px;">
                    <div style="font-size: 13px; color: var(--apple-text-muted);">
                        Viewing <strong style="color: var(--apple-black);"><?= $startIndex ?></strong> - <strong style="color: var(--apple-black);"><?= $endIndex ?></strong> of <strong style="color: var(--apple-black);"><?= $totalItems ?></strong> records
                    </div>
                    
                    <div class="flex-h gap-4">
                        <select onchange="updatePageSize(this.value)" class="input-field font-mono" style="padding: 6px 36px 6px 12px; height: 34px; font-size: 13px; font-weight: 600; border: 1px solid var(--apple-border); background-color: var(--apple-surface);">
                            <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                            <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / page</option>
                            <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                            <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
                        </select>
                        
                        <div style="display: flex; border: 1px solid var(--apple-border); border-radius: 8px; overflow: hidden; background: var(--apple-surface);">
                            <button onclick="navigatePage(<?= max(1, $currentPage - 1) ?>)" class="btn-icon-only" style="border-radius: 0; height: 32px; width: 36px;" <?= $currentPage <= 1 ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i></button>
                            
                            <?php 
                            $range = 1;
                            $startPage = max(1, $currentPage - $range);
                            $endPage = min($totalPages, $currentPage + $range);

                            if ($startPage > 1) {
                                echo '<button onclick="navigatePage(1)" style="border: none; border-left: 1px solid var(--apple-border); background: transparent; padding: 0 12px; font-size: 13px; cursor: pointer; font-family: inherit;">1</button>';
                                if ($startPage > 2) {
                                    echo '<div style="border-left: 1px solid var(--apple-border); padding: 0 12px; display: flex; align-items: center; color: var(--apple-text-muted); font-size: 13px;">...</div>';
                                }
                            }

                            for ($i = $startPage; $i <= $endPage; $i++) {
                                if ($i === $currentPage) {
                                    echo '<div style="border-left: 1px solid var(--apple-border); background: var(--apple-black); color: white; padding: 0 14px; display: flex; align-items: center; font-size: 13px; font-weight: 600;">'.$i.'</div>';
                                } else {
                                    echo '<button onclick="navigatePage('.$i.')" style="border: none; border-left: 1px solid var(--apple-border); background: transparent; padding: 0 14px; font-size: 13px; cursor: pointer; font-family: inherit;">'.$i.'</button>';
                                }
                            }

                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<div style="border-left: 1px solid var(--apple-border); padding: 0 12px; display: flex; align-items: center; color: var(--apple-text-muted); font-size: 13px;">...</div>';
                                }
                                echo '<button onclick="navigatePage('.$totalPages.')" style="border: none; border-left: 1px solid var(--apple-border); background: transparent; padding: 0 12px; font-size: 13px; cursor: pointer; font-family: inherit;">'.$totalPages.'</button>';
                            }
                            ?>

                            <button onclick="navigatePage(<?= min($totalPages, $currentPage + 1) ?>)" class="btn-icon-only" style="border-radius: 0; height: 32px; width: 36px; border-left: 1px solid var(--apple-border);" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals Layer -->

    <!-- CSV Import Modal -->
    <div id="csvImportModal" class="modal-overlay hidden">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><i class="fa-solid fa-arrow-up-from-bracket text-muted"></i> Import Product Data</div>
                <button onclick="closeCsvModal()" class="btn-icon-only"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="import-tab-erp" class="modal-body">
                <form action="<?= APP_URL ?>/inventory/importERPCSV" method="POST" enctype="multipart/form-data">
                    <div class="input-group" style="margin-bottom: 24px;">
                        <label class="input-label" style="margin-bottom: 8px;">Upload Standard ERP CSV</label>
                        <div style="border: 2px dashed var(--apple-border); border-radius: var(--radius-lg); padding: 40px 20px; text-align: center; background: var(--apple-bg); cursor: pointer; position: relative;">
                            <input type="file" name="csv_file" accept=".csv" required style="position: absolute; inset: 0; opacity: 0; cursor: pointer;">
                            <i class="fa-solid fa-file-csv" style="font-size: 32px; color: var(--apple-blue); margin-bottom: 12px;"></i>
                            <div style="font-size: 14px; font-weight: 600;">Drag and drop file here</div>
                            <div style="font-size: 12px; color: var(--apple-text-muted); margin-top: 4px;">.csv files supported</div>
                        </div>
                    </div>
                    <div style="background: var(--apple-surface); border: 1px solid var(--apple-border-light); border-radius: var(--radius-md); padding: 16px;">
                        <div style="font-size: 13px; font-weight: 600; margin-bottom: 6px;"><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--apple-blue);"></i> Automatic Mapping</div>
                        <div style="font-size: 12px; color: var(--apple-text-muted); line-height: 1.5;">The system resolves categories, warehouses, and relationships dynamically based on SKU.</div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                        <button type="button" onclick="closeCsvModal()" class="btn btn-light">Cancel</button>
                        <button type="submit" class="btn btn-dark">Begin Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteProductModal" class="modal-overlay hidden">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Delete Product</div>
                <button onclick="closeDeleteModal()" class="btn-icon-only"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="deleteProductForm" onsubmit="submitDeleteProduct(event)">
                <div class="modal-body">
                    <input type="hidden" id="deleteItemId" name="item_id">
                    
                    <p style="font-size: 15px; line-height: 1.5; color: var(--apple-text); margin-bottom: 24px; margin-top: 0;">
                        Are you sure you want to permanently delete <strong id="deleteItemName" style="color: var(--apple-black);"></strong>? This action is irreversible.
                    </p>

                    <div id="deleteErrorContainer" class="hidden" style="padding: 14px; background: var(--apple-red-bg); color: #991b1b; border: 1px solid #fee2e2; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 24px; font-weight: 500;"></div>

                    <?php if (!$isCurrentlyAdmin): ?>
                        <div style="padding: 14px; background: var(--apple-orange-bg); border: 1px solid #ffedd5; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 13px; color: #9a3412; display: flex; gap: 12px;">
                            <i class="fa-solid fa-shield-halved" style="margin-top: 2px;"></i>
                            <div>Admin authorization is required to perform deletions.</div>
                        </div>
                        <div class="input-group" style="margin-bottom: 16px;">
                            <label class="input-label">Admin Username</label>
                            <input type="text" name="admin_username" id="deleteAdminUsername" required class="input-field" placeholder="Required">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Admin Password</label>
                            <input type="password" name="password" id="deleteAdminPassword" required class="input-field" placeholder="Required">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-light">Cancel</button>
                    <button type="submit" id="deleteSubmitBtn" class="btn btn-danger">
                        <i id="deleteBtnSpinner" class="fa-solid fa-circle-notch fa-spin hidden"></i> Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Edit Selection Toolbar -->
    <div id="bulkEditToolbar" class="bulk-toolbar hidden">
        <div class="flex-h gap-3">
            <div class="badge-count" id="selectedCountBadge">0</div>
            <span style="font-size: 14px; font-weight: 500;">Items Selected</span>
        </div>
        <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.2);"></div>
        <div class="flex-h gap-2">
            <button type="button" onclick="clearSelection()" style="background: transparent; color: rgba(255,255,255,0.8); border: none; font-size: 14px; cursor: pointer; padding: 8px 12px; border-radius: 20px; transition: var(--transition);">Cancel</button>
            <button type="button" onclick="openBulkEditModal()" style="background: #fff; color: #000; border: none; border-radius: 20px; padding: 8px 18px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: var(--transition);"><i class="fa-solid fa-pen text-muted"></i> Bulk Edit</button>
        </div>
    </div>

    <!-- Bulk Edit Modal -->
    <div id="bulkEditModal" class="modal-overlay hidden">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><i class="fa-solid fa-pen-to-square text-muted"></i> Modify Multiple Items</div>
                <button onclick="closeBulkEditModal()" class="btn-icon-only"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="bulkEditForm" onsubmit="submitBulkEdit(event)">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div style="font-size: 14px; color: var(--apple-text-muted); margin-bottom: 24px;">
                        Applying changes to <strong id="bulkSelectedCount" style="color: var(--apple-black);">0</strong> selected products. Select the fields you wish to update.
                    </div>

                    <div id="bulkEditErrorContainer" class="hidden" style="padding: 14px; background: var(--apple-red-bg); color: #991b1b; border: 1px solid #fee2e2; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 24px; font-weight: 500;"></div>

                    <!-- Category Field -->
                    <div style="background: var(--apple-bg); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px;">
                        <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_category" value="1" id="bulkUpdateCategory" onchange="toggleBulkField('category')" class="apple-checkbox">
                            <span style="font-size: 14px; font-weight: 600; color: var(--apple-black);">Update Category</span>
                        </label>
                        <select name="category_id" id="bulkCategorySelect" disabled class="input-field" style="background: var(--apple-surface);">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Retail Price Field -->
                    <div style="background: var(--apple-bg); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px;">
                        <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_selling_price" value="1" id="bulkUpdateSellingPrice" onchange="toggleBulkField('selling_price')" class="apple-checkbox">
                            <span style="font-size: 14px; font-weight: 600; color: var(--apple-black);">Update Retail Price</span>
                        </label>
                        <div style="display: flex; gap: 12px;">
                            <select name="selling_price_type" id="bulkSellingPriceType" disabled class="input-field" style="width: 40%; background: var(--apple-surface);">
                                <option value="flat">Set Flat Val</option>
                                <option value="pct_inc">Add %</option>
                                <option value="pct_dec">Reduce %</option>
                            </select>
                            <input type="number" step="0.01" name="selling_price_val" id="bulkSellingPriceVal" disabled class="input-field font-mono" style="flex-grow: 1; background: var(--apple-surface);" placeholder="0.00">
                        </div>
                    </div>

                    <!-- B2B Price Field -->
                    <div style="background: var(--apple-bg); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px;">
                        <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_wholesale_price" value="1" id="bulkUpdateWholesalePrice" onchange="toggleBulkField('wholesale_price')" class="apple-checkbox">
                            <span style="font-size: 14px; font-weight: 600; color: var(--apple-black);">Update B2B Base Price</span>
                        </label>
                        <div style="display: flex; gap: 12px;">
                            <select name="wholesale_price_type" id="bulkWholesalePriceType" disabled class="input-field" style="width: 40%; background: var(--apple-surface);">
                                <option value="flat">Set Flat Val</option>
                                <option value="pct_inc">Add %</option>
                                <option value="pct_dec">Reduce %</option>
                            </select>
                            <input type="number" step="0.01" name="wholesale_price_val" id="bulkWholesalePriceVal" disabled class="input-field font-mono" style="flex-grow: 1; background: var(--apple-surface);" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Status Field -->
                    <div style="background: var(--apple-bg); border-radius: var(--radius-md); padding: 20px;">
                        <label class="flex-h gap-3" style="cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="update_status" value="1" id="bulkUpdateStatus" onchange="toggleBulkField('status')" class="apple-checkbox">
                            <span style="font-size: 14px; font-weight: 600; color: var(--apple-black);">Update Status</span>
                        </label>
                        <select name="status" id="bulkStatusSelect" disabled class="input-field" style="background: var(--apple-surface);">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeBulkEditModal()" class="btn btn-light">Cancel</button>
                    <button type="submit" id="bulkSubmitBtn" class="btn btn-dark">
                        <i id="bulkBtnSpinner" class="fa-solid fa-circle-notch fa-spin hidden"></i> Apply Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Application Logic Engine (Unmodified API Contracts) -->
    <script>
        let searchTimeout = null;

        /**
         * Trigger debounce search update to avoid database flooding while typing
         */
        function triggerSearchDelay() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('currentPageInput').value = '1';
                applyAjaxFilters();
            }, 350);
        }

        /**
         * Submit form values asynchronously using HTML Fetch
         */
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
                        if (newVal && oldVal) oldVal.textContent = newVal.textContent;
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