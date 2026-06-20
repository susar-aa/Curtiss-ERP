<?php
$reportKey = $data['reportKey'];
$metadata = $data['metadata'];
$customers = $data['customers'];
$suppliers = $data['suppliers'];
$products = $data['products'];
$warehouses = $data['warehouses'];
$routes = $data['routes'];
$categories = $data['categories'];
$savedViews = $data['savedViews'];
$scheduled = $data['scheduled'];
?>

<style>
    .viewer-layout {
        display: flex;
        gap: 20px;
        padding: 20px;
        max-width: 1600px;
        margin: 0 auto;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Filter Sidebar */
    .filter-sidebar {
        width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 20px;
    }

    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 10px;
    }

    .card-title i {
        color: #0066cc;
        font-size: 18px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13.5px;
        background-color: #fff;
        color: #1e293b;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: #0066cc;
        outline: none;
    }

    .btn {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        border: none;
    }

    .btn-primary {
        background: #0066cc;
        color: #fff;
    }

    .btn-primary:hover {
        background: #0052a3;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        margin-top: 10px;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }

    /* Main Preview Panel */
    .preview-panel {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
        min-width: 0; /* Prevents table overflow from breaking flex box */
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 15px;
    }

    .report-meta h1 {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .report-meta .breadcrumb {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 5px;
    }

    .report-actions {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        position: relative;
    }

    .action-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        z-index: 100;
        min-width: 140px;
        margin-top: 5px;
    }

    .dropdown-menu a {
        display: block;
        padding: 8px 12px;
        color: #475569;
        text-decoration: none;
        font-size: 13px;
    }

    .dropdown-menu a:hover {
        background: #f1f5f9;
        color: #0066cc;
    }

    /* Simulation mode warning alert */
    .sim-alert {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #b45309;
        font-size: 13px;
    }

    .sim-alert i {
        font-size: 20px;
    }

    /* Search and filter controls inside table card */
    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 15px;
    }

    .search-input-wrapper {
        position: relative;
        width: 280px;
    }

    .search-input-wrapper i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input-wrapper input {
        padding-left: 32px;
    }

    /* Elegant Responsive Table Styles */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        text-align: left;
    }

    .report-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 650;
        padding: 12px 16px;
        border-bottom: 2px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        position: relative;
    }

    .report-table th:hover {
        background: #f1f5f9;
    }

    .report-table th.sorted-asc::after {
        content: ' ▲';
        font-size: 9px;
        color: #0066cc;
    }

    .report-table th.sorted-desc::after {
        content: ' ▼';
        font-size: 9px;
        color: #0066cc;
    }

    .report-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        color: #1e293b;
    }

    .report-table tr:hover {
        background: #f8fafc;
    }

    /* Subtotal & Grand Total Rows */
    .total-row {
        font-weight: 750;
        background: #f8fafc;
    }

    .total-row td {
        border-top: 2px double #cbd5e1;
        border-bottom: 2px double #cbd5e1;
        color: #0f172a;
    }

    /* Drilldown badge links */
    .drilldown-link {
        color: #0066cc;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1px dashed #0066cc;
    }

    .drilldown-link:hover {
        color: #0047b3;
        border-bottom-style: solid;
    }

    .badge-completed {
        background: #dcfce7;
        color: #15803d;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-pending {
        background: #fef3c7;
        color: #b45309;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Pagination Footer */
    .pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }

    .pagination-info {
        font-size: 13px;
        color: #64748b;
    }

    .pagination-controls {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .page-btn {
        background: #fff;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        font-size: 13px;
        border-radius: 4px;
        cursor: pointer;
    }

    .page-btn:hover:not(:disabled) {
        background: #f1f5f9;
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-btn.active {
        background: #0066cc;
        color: #fff;
        border-color: #0066cc;
    }

    /* Spinner loading overlay */
    .loading-overlay {
        position: relative;
    }

    .spinner {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 4px solid #f3f3f3;
        border-top: 4px solid #0066cc;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        z-index: 10;
    }

    @keyframes spin {
        0% { transform: translate(-50%, -50__) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    /* Collapsible cards view list */
    .collapsible-list {
        margin-top: 10px;
        max-height: 150px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .collapsible-item {
        font-size: 12.5px;
        padding: 6px 10px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s;
    }

    .collapsible-item:hover {
        background: #f1f5f9;
        color: #0066cc;
    }

    .collapsible-item span.date {
        font-size: 11px;
        color: #94a3b8;
    }

    /* Print Stylesheet integration */
    @media print {
        header, footer, .filter-sidebar, .preview-header, .table-toolbar, .pagination-footer, .no-print {
            display: none !important;
        }
        body, .viewer-layout, .preview-panel {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .table-container {
            border: none !important;
        }
        .report-table th {
            background: #e2e8f0 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .report-table td {
            border-bottom: 1px solid #cbd5e1 !important;
        }
    }

    @media (prefers-color-scheme: dark) {
        .card {
            background: #1e1e1e;
            border-color: #2e2e2e;
        }
        .card-title {
            color: #e2e8f0;
            border-color: #2e2e2e;
        }
        .form-control {
            background-color: #252525;
            border-color: #3e3e3e;
            color: #e2e8f0;
        }
        .action-btn {
            background-color: #252525;
            border-color: #3e3e3e;
            color: #cbd5e1;
        }
        .action-btn:hover {
            background-color: #2d2d2d;
        }
        .dropdown-menu {
            background-color: #1e1e1e;
            border-color: #3e3e3e;
        }
        .dropdown-menu a {
            color: #cbd5e1;
        }
        .dropdown-menu a:hover {
            background-color: #2d2d2d;
        }
        .report-meta h1 {
            color: #ffffff;
        }
        .report-table th {
            background-color: #252525;
            color: #cbd5e1;
            border-color: #3e3e3e;
        }
        .report-table td {
            color: #cbd5e1;
            border-color: #2e2e2e;
        }
        .report-table tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
        .total-row {
            background-color: #252525;
        }
        .total-row td {
            color: #ffffff;
            border-top-color: #3e3e3e;
            border-bottom-color: #3e3e3e;
        }
        .collapsible-item {
            background-color: #252525;
            border-color: #3e3e3e;
            color: #cbd5e1;
        }
        .collapsible-item:hover {
            background-color: #2d2d2d;
        }
    }
</style>

<div class="viewer-layout">
    <!-- Filter Panel Sidebar -->
    <div class="filter-sidebar no-print">
        <!-- Main Filter Selection -->
        <div class="card">
            <div class="card-title">
                <i class="ph ph-sliders"></i>
                <span>Report Filters</span>
            </div>
            <form id="filterForm">
                <?php if (in_array('date_range', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="filter_start_date" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date" id="filter_end_date" value="<?= date('Y-m-d') ?>">
                    </div>
                <?php endif; ?>

                <?php if (in_array('customer', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Customer</label>
                        <select class="form-control" name="customer" id="filter_customer">
                            <option value="">-- All Customers --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('supplier', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Supplier / Vendor</label>
                        <select class="form-control" name="supplier" id="filter_supplier">
                            <option value="">-- All Suppliers --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s->id ?>"><?= htmlspecialchars($s->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('product', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Product</label>
                        <select class="form-control" name="product" id="filter_product">
                            <option value="">-- All Products --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('warehouse', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Warehouse</label>
                        <select class="form-control" name="warehouse" id="filter_warehouse">
                            <option value="">-- All Warehouses --</option>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= $w->id ?>"><?= htmlspecialchars($w->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('category', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Item Category</label>
                        <select class="form-control" name="category" id="filter_category">
                            <option value="">-- All Categories --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('route', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Route</label>
                        <select class="form-control" name="route" id="filter_route">
                            <option value="">-- All Routes --</option>
                            <?php foreach ($routes as $r): ?>
                                <option value="<?= $r->id ?>"><?= htmlspecialchars($r->route_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="button" class="btn btn-primary" onclick="loadReportData(1)" style="margin-top: 15px;">
                    <i class="ph ph-arrow-clockwise"></i> Generate Report
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetAllFilters()">
                    Reset Filters
                </button>
            </form>
        </div>

        <!-- Saved Views / Saved Filter Sets -->
        <div class="card">
            <div class="card-title">
                <i class="ph ph-bookmark-simple"></i>
                <span>Saved Layout Views</span>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" id="new_view_name" placeholder="View name (e.g. Q2 Sales)">
                <button class="btn btn-secondary" onclick="saveActiveView()" style="margin-top: 8px;">
                    <i class="ph ph-floppy-disk"></i> Save Current Filters
                </button>
            </div>
            <div class="collapsible-list" id="savedViewsList">
                <?php if (empty($savedViews)): ?>
                    <p style="font-size:12px; color:#888; margin:5px 0;">No saved views for this report yet.</p>
                <?php else: ?>
                    <?php foreach ($savedViews as $sv): ?>
                        <div class="collapsible-item" onclick='loadSavedView(<?= htmlspecialchars($sv->filters) ?>)'>
                            <span><?= htmlspecialchars($sv->view_name) ?></span>
                            <span class="date"><?= date('M d', strtotime($sv->created_at)) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Scheduled Emails Setup -->
        <div class="card">
            <div class="card-title">
                <i class="ph ph-clock"></i>
                <span>Automated Email Delivery</span>
            </div>
            <div class="form-group">
                <label>Frequency</label>
                <select class="form-control" id="sched_frequency">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div class="form-group">
                <label>Email Recipient</label>
                <input type="email" class="form-control" id="sched_email" placeholder="manager@company.com">
            </div>
            <button class="btn btn-secondary" onclick="saveReportSchedule()">
                <i class="ph ph-bell"></i> Schedule Report
            </button>
            <div class="collapsible-list" id="schedulesList" style="margin-top: 10px;">
                <?php if (empty($scheduled)): ?>
                    <p style="font-size:12px; color:#888; margin:5px 0;">No automated schedules configured.</p>
                <?php else: ?>
                    <?php foreach ($scheduled as $s): ?>
                        <div class="collapsible-item" style="cursor:default;">
                            <span><?= htmlspecialchars($s->email_recipient) ?></span>
                            <span class="badge-completed"><?= ucfirst($s->frequency) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Report Preview Table -->
    <div class="preview-panel">
        <div class="preview-header">
            <div class="report-meta">
                <div class="breadcrumb">Reports / <?= htmlspecialchars(ucfirst($metadata['category'])) ?></div>
                <h1><?= htmlspecialchars($metadata['title']) ?></h1>
            </div>
            <div class="report-actions no-print">
                <!-- Share Button -->
                <div style="position:relative;">
                    <button class="action-btn" onclick="toggleDropdown('shareMenu')">
                        <i class="ph ph-share-network"></i> Share <i class="ph ph-caret-down"></i>
                    </button>
                    <div class="dropdown-menu" id="shareMenu">
                        <a href="javascript:void(0)" onclick="copyShareLink()"><i class="ph ph-link"></i> Copy Link</a>
                        <a href="javascript:void(0)" onclick="emailShare()"><i class="ph ph-envelope"></i> Email link</a>
                    </div>
                </div>

                <!-- Print Button -->
                <button class="action-btn" onclick="openPrintLayout()">
                    <i class="ph ph-printer"></i> Print
                </button>

                <!-- Export Dropdown -->
                <div style="position:relative;">
                    <button class="action-btn" onclick="toggleDropdown('exportMenu')">
                        <i class="ph ph-download"></i> Export <i class="ph ph-caret-down"></i>
                    </button>
                    <div class="dropdown-menu" id="exportMenu">
                        <a href="javascript:void(0)" onclick="triggerExport('excel')"><i class="ph ph-file-xls"></i> Excel (.xls)</a>
                        <a href="javascript:void(0)" onclick="triggerExport('csv')"><i class="ph ph-file-csv"></i> CSV (.csv)</a>
                        <a href="javascript:void(0)" onclick="triggerExport('word')"><i class="ph ph-file-doc"></i> Word (.doc)</a>
                        <a href="javascript:void(0)" onclick="triggerExport('xml')"><i class="ph ph-file-xml"></i> XML</a>
                        <a href="javascript:void(0)" onclick="triggerExport('json')"><i class="ph ph-file-code"></i> JSON</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simulation mode warning alert -->
        <div class="sim-alert" id="simAlert" style="display:none;">
            <i class="ph ph-warning-circle"></i>
            <div>
                <strong>Simulation Mode Active:</strong> The table database relation for this report is currently undergoing system migration. Below values are high-fidelity mockups provided to preview formatting layout.
            </div>
        </div>

        <!-- Interactive Table Card -->
        <div class="card" style="padding: 15px; position:relative;">
            <div class="spinner" id="tableSpinner"></div>
            <div class="loading-overlay" id="tableOverlay">
                <div class="table-toolbar no-print">
                    <div class="search-input-wrapper">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" class="form-control" id="tableSearch" placeholder="Filter rows in view..." onkeyup="clientFilterRows()">
                    </div>
                    <div style="font-size: 13px; color: #64748b;">
                        Showing Page size: 
                        <select id="limitSelect" onchange="loadReportData(1)" style="padding:4px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                            <option value="25">25 rows</option>
                            <option value="50" selected>50 rows</option>
                            <option value="100">100 rows</option>
                        </select>
                    </div>
                </div>

                <!-- Scrollable Table -->
                <div class="table-container" id="printableArea">
                    <table class="report-table" id="reportDataTable">
                        <thead>
                            <tr id="tableHeaders">
                                <!-- Headers injected dynamically by JS -->
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Body rows injected dynamically -->
                        </tbody>
                        <tfoot id="tableFoot">
                            <!-- Totals injected dynamically -->
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination footer controls -->
                <div class="pagination-footer no-print">
                    <div class="pagination-info" id="paginationInfo">
                        Showing 0 to 0 of 0 entries
                    </div>
                    <div class="pagination-controls" id="paginationControls">
                        <!-- Controls injected by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const reportKey = '<?= $reportKey ?>';
    const columnsMeta = <?= json_encode($metadata['columns']) ?>;
    let currentSortCol = null;
    let currentSortDir = 'ASC';
    let currentPage = 1;
    let totalEntries = 0;
    let currentRows = [];

    document.addEventListener('DOMContentLoaded', function() {
        // Render headers first
        const headerRow = document.getElementById('tableHeaders');
        headerRow.innerHTML = '';
        for (const [colKey, def] of Object.entries(columnsMeta)) {
            const th = document.createElement('th');
            th.textContent = def.label;
            if (def.align === 'right') {
                th.style.textAlign = 'right';
            }
            if (def.sortable) {
                th.style.cursor = 'pointer';
                th.onclick = function() { toggleTableSort(colKey); };
            }
            th.setAttribute('data-col-key', colKey);
            headerRow.appendChild(th);
        }

        // Load initial data
        loadReportData(1);

        // Click outside drops handler
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.action-btn')) {
                document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
            }
        });
    });

    function toggleDropdown(id) {
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m.id !== id) m.style.display = 'none';
        });
        const m = document.getElementById(id);
        m.style.display = m.style.display === 'block' ? 'none' : 'block';
    }

    function resetAllFilters() {
        document.getElementById('filterForm').reset();
        loadReportData(1);
    }

    function loadReportData(page) {
        currentPage = page;
        const spinner = document.getElementById('tableSpinner');
        const overlay = document.getElementById('tableOverlay');
        spinner.style.display = 'block';
        overlay.style.opacity = '0.4';

        // Collect filters
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        params.append('report', reportKey);
        params.append('page', page);
        params.append('limit', document.getElementById('limitSelect').value);
        if (currentSortCol) {
            params.append('sort_col', currentSortCol);
            params.append('sort_dir', currentSortDir);
        }

        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        fetch('<?= APP_URL ?>/report/fetch_data?' + params.toString())
            .then(res => res.json())
            .then(data => {
                spinner.style.display = 'none';
                overlay.style.opacity = '1';

                if (!data.success) {
                    alert('Error loading report: ' + data.message);
                    return;
                }

                // Show simulation notice if applicable
                document.getElementById('simAlert').style.display = data.simulation ? 'flex' : 'none';

                currentRows = data.rows;
                totalEntries = data.total_rows;
                
                renderTableBody(data.rows);
                renderTableFoot(data.grand_totals);
                renderPagination(page, data.total_rows);
            })
            .catch(err => {
                spinner.style.display = 'none';
                overlay.style.opacity = '1';
                console.error(err);
            });
    }

    function renderTableBody(rows) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        if (rows.length === 0) {
            const colCount = Object.keys(columnsMeta).length;
            tbody.innerHTML = `<tr><td colspan="${colCount}" style="text-align:center;color:#888;padding:30px;">No matching records found.</td></tr>`;
            return;
        }

        rows.forEach(r => {
            const tr = document.createElement('tr');
            for (const [colKey, def] of Object.entries(columnsMeta)) {
                const td = document.createElement('td');
                let val = r[colKey] !== undefined ? r[colKey] : '';

                if (def.align === 'right') {
                    td.style.textAlign = 'right';
                }

                // Formatting cell types
                if (def.type === 'currency') {
                    td.textContent = 'Rs. ' + parseFloat(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (def.type === 'number') {
                    td.textContent = parseInt(val || 0).toLocaleString();
                } else if (def.type === 'badge') {
                    const cleanVal = (val || '').toLowerCase();
                    if (cleanVal === 'completed' || cleanVal === 'paid' || cleanVal === 'active') {
                        td.innerHTML = `<span class="badge-completed">${val}</span>`;
                    } else {
                        td.innerHTML = `<span class="badge-pending">${val}</span>`;
                    }
                } else if (def.drilldown && val) {
                    let drillUrl = '#';
                    if (def.drilldown === 'invoice') {
                        drillUrl = `<?= APP_URL ?>/sales/show/${r.id || ''}`;
                    } else if (def.drilldown === 'product') {
                        drillUrl = `<?= APP_URL ?>/inventory`;
                    }
                    td.innerHTML = `<a href="${drillUrl}" class="drilldown-link">${val}</a>`;
                } else {
                    td.textContent = val;
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        });
    }

    function renderTableFoot(grandTotals) {
        const tfoot = document.getElementById('tableFoot');
        tfoot.innerHTML = '';

        // 1. Calculate Page Subtotals
        const subRow = document.createElement('tr');
        subRow.className = 'total-row';
        let firstCol = true;

        for (const [colKey, def] of Object.entries(columnsMeta)) {
            const td = document.createElement('td');
            if (def.align === 'right') {
                td.style.textAlign = 'right';
            }

            if (firstCol) {
                td.textContent = 'Page Subtotal';
                firstCol = false;
            } else if (def.total === 'sum') {
                let sum = 0;
                currentRows.forEach(r => {
                    sum += parseFloat(r[colKey] || 0);
                });
                td.textContent = (def.type === 'currency' ? 'Rs. ' : '') + sum.toLocaleString(undefined, {minimumFractionDigits: def.type === 'currency' ? 2 : 0, maximumFractionDigits: def.type === 'currency' ? 2 : 0});
            } else {
                td.textContent = '-';
            }
            subRow.appendChild(td);
        }
        tfoot.appendChild(subRow);

        // 2. Query Grand Totals (from server response)
        const grandRow = document.createElement('tr');
        grandRow.className = 'total-row';
        firstCol = true;

        for (const [colKey, def] of Object.entries(columnsMeta)) {
            const td = document.createElement('td');
            if (def.align === 'right') {
                td.style.textAlign = 'right';
            }

            if (firstCol) {
                td.textContent = 'Grand Total';
                firstCol = false;
            } else if (def.total === 'sum' && grandTotals && grandTotals[colKey] !== undefined) {
                const totalVal = parseFloat(grandTotals[colKey]);
                td.textContent = (def.type === 'currency' ? 'Rs. ' : '') + totalVal.toLocaleString(undefined, {minimumFractionDigits: def.type === 'currency' ? 2 : 0, maximumFractionDigits: def.type === 'currency' ? 2 : 0});
            } else {
                td.textContent = '-';
            }
            grandRow.appendChild(td);
        }
        tfoot.appendChild(grandRow);
    }

    function toggleTableSort(colKey) {
        if (currentSortCol === colKey) {
            currentSortDir = currentSortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSortCol = colKey;
            currentSortDir = 'ASC';
        }

        // Highlight header
        document.querySelectorAll('.report-table th').forEach(th => {
            th.className = '';
        });
        const activeTh = document.querySelector(`.report-table th[data-col-key="${colKey}"]`);
        if (activeTh) {
            activeTh.className = currentSortDir === 'ASC' ? 'sorted-asc' : 'sorted-desc';
        }

        loadReportData(1);
    }

    function clientFilterRows() {
        const query = document.getElementById('tableSearch').value.toLowerCase();
        const filtered = currentRows.filter(r => {
            return Object.values(r).some(val => String(val).toLowerCase().includes(query));
        });
        renderTableBody(filtered);
    }

    function renderPagination(page, total) {
        const limit = parseInt(document.getElementById('limitSelect').value);
        const totalPages = Math.ceil(total / limit) || 1;
        
        // Info label
        const start = total === 0 ? 0 : (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);
        document.getElementById('paginationInfo').textContent = `Showing ${start} to ${end} of ${total} entries`;

        // Page buttons
        const controls = document.getElementById('paginationControls');
        controls.innerHTML = '';

        // Previous
        const prev = document.createElement('button');
        prev.className = 'page-btn';
        prev.textContent = 'Previous';
        prev.disabled = page === 1;
        prev.onclick = function() { loadReportData(page - 1); };
        controls.appendChild(prev);

        // Individual numbers
        let startPage = Math.max(1, page - 2);
        let endPage = Math.min(totalPages, page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.className = 'page-btn' + (i === page ? ' active' : '');
            btn.textContent = i;
            btn.onclick = function() { loadReportData(i); };
            controls.appendChild(btn);
        }

        // Next
        const next = document.createElement('button');
        next.className = 'page-btn';
        next.textContent = 'Next';
        next.disabled = page === totalPages;
        next.onclick = function() { loadReportData(page + 1); };
        controls.appendChild(next);
    }

    // --- Saved Views handlers ---
    function saveActiveView() {
        const nameInput = document.getElementById('new_view_name');
        const viewName = nameInput.value.trim();
        if (!viewName) {
            alert('Please enter a view name.');
            return;
        }

        // Get active form filters
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const filters = {};
        for (const [key, value] of formData.entries()) {
            if (value) filters[key] = value;
        }

        const data = new URLSearchParams();
        data.append('report_key', reportKey);
        data.append('view_name', viewName);
        for (const [k, v] of Object.entries(filters)) {
            data.append(`filters[${k}]`, v);
        }

        fetch('<?= APP_URL ?>/report/save_view', {
            method: 'POST',
            body: data,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                nameInput.value = '';
                // Reload list
                location.reload();
            } else {
                alert('Failed to save layout view.');
            }
        });
    }

    function loadSavedView(filters) {
        // Reset form
        const form = document.getElementById('filterForm');
        form.reset();
        
        // Fill form fields
        for (const [key, val] of Object.entries(filters)) {
            const field = document.getElementById(`filter_${key}`);
            if (field) field.value = val;
        }
        loadReportData(1);
    }

    // --- Automated Schedules handlers ---
    function saveReportSchedule() {
        const emailInput = document.getElementById('sched_email');
        const email = emailInput.value.trim();
        const freq = document.getElementById('sched_frequency').value;

        if (!email) {
            alert('Please enter a valid recipient email address.');
            return;
        }

        // Active filters
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const filters = {};
        for (const [key, value] of formData.entries()) {
            if (value) filters[key] = value;
        }

        const data = new URLSearchParams();
        data.append('report_key', reportKey);
        data.append('frequency', freq);
        data.append('email_recipient', email);
        for (const [k, v] of Object.entries(filters)) {
            data.append(`filters[${k}]`, v);
        }

        fetch('<?= APP_URL ?>/report/save_schedule', {
            method: 'POST',
            body: data,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                emailInput.value = '';
                location.reload();
            } else {
                alert('Failed to create report email schedule.');
            }
        });
    }

    // --- Share features ---
    function copyShareLink() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Share URL link copied to clipboard successfully!');
        });
    }

    function emailShare() {
        const url = encodeURIComponent(window.location.href);
        const subject = encodeURIComponent('Curtiss ERP Live Report View');
        window.location.href = `mailto:?subject=${subject}&body=Check out this live Curtiss ERP Report:%0D%0A${url}`;
    }

    // --- Export trigger ---
    function triggerExport(format) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        params.append('format', format);

        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        window.location.href = `<?= APP_URL ?>/report/export/${reportKey}?` + params.toString();
    }

    // --- Dedicated high-fidelity print window layout ---
    function openPrintLayout() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        window.open('<?= APP_URL ?>/report/print_report/' + reportKey + '?' + params.toString(), '_blank');
    }
</script>
