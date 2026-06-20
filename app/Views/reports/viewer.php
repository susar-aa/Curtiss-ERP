<?php
$reportKey = $data['reportKey'];
$metadata = $data['metadata'];
$customers = $data['customers'];
$suppliers = $data['suppliers'];
$products = $data['products'];
$warehouses = $data['warehouses'];
$routes = $data['routes'];
$categories = $data['categories'];
$brands = $data['brands'] ?? [];
$groups = $data['groups'] ?? [];
$vehicles = $data['vehicles'] ?? [];
$drivers = $data['drivers'] ?? [];
$partners = $data['partners'] ?? [];
$territories = $data['territories'] ?? [];
$reps = $data['reps'] ?? [];
$payment_methods = $data['payment_methods'] ?? [];
$statuses = $data['statuses'] ?? [];
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
        background: #fef2f2;
        border: 1px solid #fee2e2;
        border-left: 4px solid #ef4444;
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #991b1b;
        font-size: 13.5px;
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
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
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

                <?php if (in_array('rep', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Sales Rep</label>
                        <select class="form-control" name="rep" id="filter_rep">
                            <option value="">-- All Reps --</option>
                            <?php foreach ($reps as $r): ?>
                                <option value="<?= $r->id ?>"><?= htmlspecialchars($r->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('payment_method', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" name="payment_method" id="filter_payment_method">
                            <option value="">-- All Methods --</option>
                            <?php foreach ($payment_methods as $pm): ?>
                                <option value="<?= $pm->id ?>"><?= htmlspecialchars($pm->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('status', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="filter_status">
                            <option value="">-- All Statuses --</option>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st->id ?>"><?= htmlspecialchars($st->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('brand', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Brand</label>
                        <select class="form-control" name="brand" id="filter_brand">
                            <option value="">-- All Brands --</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= htmlspecialchars($b->brand) ?>"><?= htmlspecialchars($b->brand) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('group', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Customer Group</label>
                        <select class="form-control" name="group" id="filter_group">
                            <option value="">-- All Groups --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= htmlspecialchars($g->name) ?>"><?= htmlspecialchars($g->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('vehicle', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Vehicle</label>
                        <select class="form-control" name="vehicle" id="filter_vehicle">
                            <option value="">-- All Vehicles --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('driver', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Driver</label>
                        <select class="form-control" name="driver" id="filter_driver">
                            <option value="">-- All Drivers --</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= htmlspecialchars($d->name) ?>"><?= htmlspecialchars($d->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('partner', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Partner / Helper</label>
                        <select class="form-control" name="partner" id="filter_partner">
                            <option value="">-- All Partners --</option>
                            <?php foreach ($partners as $p): ?>
                                <option value="<?= htmlspecialchars($p->name) ?>"><?= htmlspecialchars($p->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array('territory', $metadata['filters'] ?? [])): ?>
                    <div class="form-group">
                        <label>Territory</label>
                        <select class="form-control" name="territory" id="filter_territory">
                            <option value="">-- All Territories --</option>
                            <?php foreach ($territories as $t): ?>
                                <option value="<?= htmlspecialchars($t->territory) ?>"><?= htmlspecialchars($t->territory) ?></option>
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
                        <a href="javascript:void(0)" onclick="whatsappShare()"><i class="ph ph-whatsapp-logo"></i> WhatsApp Share</a>
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
                const simAlert = document.getElementById('simAlert');
                if (data.simulation) {
                    simAlert.style.display = 'flex';
                    let errorText = "Simulation Mode: Real Database Table is Missing. Displaying Simulated Data.";
                    if (data.db_error) {
                        errorText += " (Error: " + data.db_error + ")";
                    }
                    simAlert.querySelector('div').innerHTML = `<strong>Warning:</strong> ${errorText}`;
                } else {
                    simAlert.style.display = 'none';
                }

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
                    } else if (def.drilldown === 'customer') {
                        drillUrl = `<?= APP_URL ?>/customer/edit/${r.customer_id || r.id || ''}`;
                    } else if (def.drilldown === 'supplier') {
                        drillUrl = `<?= APP_URL ?>/supplier/edit/${r.vendor_id || r.id || ''}`;
                    } else if (def.drilldown === 'po') {
                        drillUrl = `<?= APP_URL ?>/purchase/show/${r.id || ''}`;
                    } else if (def.drilldown === 'grn') {
                        drillUrl = `<?= APP_URL ?>/grn/show/${r.id || ''}`;
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

    function whatsappShare() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Check out this live Curtiss ERP Report: ') + url;
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
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
