<?php
$reportKey = $data['reportKey'];
$metadata = $data['metadata'];
$customers = $data['customers'] ?? [];
$suppliers = $data['suppliers'] ?? [];
$products = $data['products'] ?? [];
$warehouses = $data['warehouses'] ?? [];
$routes = $data['routes'] ?? [];
$categories = $data['categories'] ?? [];
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

<!-- SF Pro / Inter Font & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — REPORT ENGINE VIEWER
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-surface3:     #f4f4f8;
    --c-fill:         rgba(120,120,128,0.08);
    --c-fill2:        rgba(120,120,128,0.14);
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-blue-mid:     #b3d6ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;
    --c-purple:       #af52de;
    --c-purple-light: #f6e8ff;
    --c-teal:         #30b0c7;
    --c-teal-light:   #e8f8fa;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-xs: 6px;
    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-xl: 24px;
    --r-pill: 999px;

    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
    --dur-mid:     0.28s;
}

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #121212;
        --c-surface:      #1e1e2e;
        --c-surface2:     #161622;
        --c-surface3:     #252538;
        --c-fill:         rgba(255,255,255,0.08);
        --c-fill2:        rgba(255,255,255,0.12);
        --c-separator:    rgba(255,255,255,0.15);
        --c-separator2:   rgba(255,255,255,0.08);
        --t-primary:      #f5f5f7;
        --t-secondary:    #a1a1aa;
        --t-tertiary:     #71717a;
        --t-label:        #52525b;
        --c-blue-light:   rgba(0,122,255,0.15);
        --c-green-light:  rgba(52,199,89,0.15);
        --c-orange-light: rgba(255,149,0,0.15);
        --c-red-light:    rgba(255,59,48,0.15);
        --c-purple-light: rgba(175,82,222,0.15);
    }
}

.report-root {
    font-family: var(--f-system);
    font-size: 14px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    min-height: 100vh;
}

.viewer-container {
    max-width: 1680px;
    margin: 0 auto;
    padding: 20px 24px 80px;
}

/* Header & Breadcrumb */
.report-top-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
}

.breadcrumb-shelf {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--t-secondary);
    margin-bottom: 6px;
}

.breadcrumb-shelf a {
    color: var(--c-blue);
    text-decoration: none;
    transition: opacity var(--dur-fast);
}

.breadcrumb-shelf a:hover {
    text-decoration: underline;
}

.category-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: var(--r-pill);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: var(--c-blue-light);
    color: var(--c-blue);
}

.report-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-title {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--t-primary);
    margin: 0;
    line-height: 1.2;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Apple Buttons */
.sf-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: var(--r-pill);
    font-size: 13.5px;
    font-weight: 600;
    font-family: var(--f-system);
    cursor: pointer;
    border: 0.5px solid var(--c-separator);
    background: var(--c-surface);
    color: var(--t-primary);
    box-shadow: var(--shadow-xs);
    transition: all var(--dur-fast) var(--ease-ios);
    text-decoration: none;
    user-select: none;
    position: relative;
}

.sf-btn:hover {
    background: var(--c-fill);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.sf-btn:active {
    transform: scale(0.98);
}

.sf-btn-primary {
    background: var(--c-blue);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(0,122,255,0.25);
}

.sf-btn-primary:hover {
    background: #0066e6;
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(0,122,255,0.35);
}

.sf-btn-secondary {
    background: var(--c-surface2);
    color: var(--t-secondary);
}

.sf-btn-icon {
    padding: 9px 12px;
}

/* Stat Cards (KPI Summary Bar) */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.kpi-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    transition: transform var(--dur-fast) var(--ease-ios), box-shadow var(--dur-fast) var(--ease-ios);
    cursor: default;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 14px;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
}

.kpi-card.blue::before   { background: var(--c-blue); }
.kpi-card.green::before  { background: var(--c-green); }
.kpi-card.orange::before { background: var(--c-orange); }
.kpi-card.purple::before { background: var(--c-purple); }

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--r-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}

.kpi-card.blue   .kpi-icon { background: var(--c-blue-light);   color: var(--c-blue); }
.kpi-card.green  .kpi-icon { background: var(--c-green-light);  color: var(--c-green); }
.kpi-card.orange .kpi-icon { background: var(--c-orange-light); color: var(--c-orange); }
.kpi-card.purple .kpi-icon { background: var(--c-purple-light); color: var(--c-purple); }

.kpi-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}

.kpi-num {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--t-primary);
    line-height: 1.15;
    margin-bottom: 2px;
    font-family: var(--f-mono);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.kpi-lbl {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--t-label);
    white-space: nowrap;
}

/* Two-column Layout */
.viewer-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

/* Filter Sidebar */
.filter-sidebar {
    width: 320px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 20px;
}

.sf-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 20px;
}

.sf-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 0.5px solid var(--c-separator2);
}

.sf-card-title {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: var(--t-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.sf-card-title i {
    color: var(--c-blue);
    font-size: 18px;
}

/* Preset Date Chips */
.date-preset-shelf {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 14px;
}

.preset-chip {
    padding: 4px 10px;
    border-radius: var(--r-pill);
    font-size: 11.5px;
    font-weight: 600;
    background: var(--c-surface2);
    border: 0.5px solid var(--c-separator);
    color: var(--t-secondary);
    cursor: pointer;
    transition: all var(--dur-fast);
}

.preset-chip:hover {
    background: var(--c-fill);
    color: var(--t-primary);
}

.preset-chip.active {
    background: var(--c-blue);
    color: #ffffff;
    border-color: var(--c-blue);
}

/* Form Fields */
.sf-form-group {
    margin-bottom: 14px;
}

.sf-form-group:last-child {
    margin-bottom: 0;
}

.sf-form-label {
    display: block;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--t-label);
    margin-bottom: 6px;
}

.sf-input, .sf-select {
    width: 100%;
    padding: 9px 12px;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    font-size: 13.5px;
    font-family: var(--f-system);
    background-color: var(--c-surface2);
    color: var(--t-primary);
    transition: all var(--dur-fast);
    outline: none;
    box-sizing: border-box;
}

.sf-input:focus, .sf-select:focus {
    background-color: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.14);
}

/* Main Preview Panel */
.preview-panel {
    flex-grow: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Simulation Alert */
.sim-alert {
    background: var(--c-red-light);
    border: 0.5px solid rgba(255,59,48,0.25);
    border-left: 4px solid var(--c-red);
    border-radius: var(--r-md);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--c-red);
    font-size: 13px;
    font-weight: 500;
}

/* Table Card */
.table-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    position: relative;
}

.table-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 0.5px solid var(--c-separator);
    background: var(--c-surface);
    flex-wrap: wrap;
    gap: 12px;
}

.table-search-box {
    position: relative;
    width: 280px;
}

.table-search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--t-label);
    font-size: 15px;
    pointer-events: none;
}

.table-search-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    font-size: 13px;
    background: var(--c-surface2);
    color: var(--t-primary);
    outline: none;
    transition: all var(--dur-fast);
    box-sizing: border-box;
}

.table-search-input:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.12);
}

.table-toolbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--t-secondary);
}

.page-size-select {
    padding: 5px 10px;
    border-radius: var(--r-sm);
    border: 0.5px solid var(--c-separator);
    background: var(--c-surface2);
    color: var(--t-primary);
    font-size: 12.5px;
    font-weight: 500;
    outline: none;
}

/* Scrollable Table */
.table-scroll {
    overflow-x: auto;
    position: relative;
    max-height: 720px;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    text-align: left;
}

.report-table thead th {
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--t-label);
    background: var(--c-surface2);
    border-bottom: 0.5px solid var(--c-separator);
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
    user-select: none;
    transition: background var(--dur-fast);
}

.report-table thead th:hover {
    background: var(--c-fill2);
}

.report-table thead th.sortable {
    cursor: pointer;
}

.report-table thead th.sorted-asc::after {
    content: ' ▲';
    font-size: 9px;
    color: var(--c-blue);
    margin-left: 4px;
}

.report-table thead th.sorted-desc::after {
    content: ' ▼';
    font-size: 9px;
    color: var(--c-blue);
    margin-left: 4px;
}

.report-table tbody tr {
    transition: background var(--dur-fast);
    border-bottom: 0.5px solid var(--c-separator2);
}

.report-table tbody tr:hover {
    background: var(--c-fill);
}

.report-table td {
    padding: 12px 16px;
    color: var(--t-primary);
    vertical-align: middle;
}

.report-table td.numeric-cell {
    font-family: var(--f-mono);
    font-feature-settings: "tnum";
    font-variant-numeric: tabular-nums;
}

/* Total Rows */
.report-table tfoot tr.total-row {
    background: var(--c-surface2);
    font-weight: 750;
    border-top: 1.5px solid var(--c-separator);
    border-bottom: 1.5px solid var(--c-separator);
}

.report-table tfoot tr.total-row td {
    color: var(--t-primary);
    font-weight: 700;
    font-family: var(--f-mono);
}

.report-table tfoot tr.grand-total-row {
    background: var(--c-surface3);
    font-weight: 800;
    border-top: 2px solid var(--c-separator);
    border-bottom: 2px double var(--c-separator);
}

.report-table tfoot tr.grand-total-row td {
    color: var(--t-primary);
    font-weight: 800;
    font-size: 14px;
    font-family: var(--f-mono);
}

/* Badges & Status Pills */
.sf-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: var(--r-pill);
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.02em;
}

.sf-badge-success { background: var(--c-green-light); color: var(--c-green); }
.sf-badge-warning { background: var(--c-orange-light); color: var(--c-orange); }
.sf-badge-danger  { background: var(--c-red-light); color: var(--c-red); }
.sf-badge-info    { background: var(--c-blue-light); color: var(--c-blue); }
.sf-badge-neutral { background: var(--c-fill2); color: var(--t-secondary); }

/* Interactive Cells & Drilldowns */
.drill-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    width: 100%;
}

.drill-link {
    color: var(--c-blue);
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border-bottom: 1px dashed rgba(0,122,255,0.4);
    transition: all var(--dur-fast);
}

.drill-link:hover {
    color: #0056cc;
    border-bottom-style: solid;
}

.drill-actions {
    opacity: 0;
    display: inline-flex;
    gap: 3px;
    margin-left: auto;
    transition: opacity var(--dur-fast);
}

.drill-cell:hover .drill-actions {
    opacity: 1;
}

.drill-action-btn {
    background: transparent;
    border: none;
    padding: 3px 5px;
    color: var(--t-tertiary);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    border-radius: var(--r-xs);
    font-size: 13px;
    text-decoration: none;
    transition: all var(--dur-fast);
}

.drill-action-btn:hover {
    background: var(--c-fill2);
    color: var(--c-blue);
}

/* Pagination Footer */
.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-top: 0.5px solid var(--c-separator);
    background: var(--c-surface);
    flex-wrap: wrap;
    gap: 12px;
}

.pagination-info {
    font-size: 13px;
    color: var(--t-secondary);
    font-weight: 500;
}

.pagination-controls {
    display: flex;
    gap: 4px;
    align-items: center;
}

.pagination-btn {
    background: var(--c-surface2);
    border: 0.5px solid var(--c-separator);
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    border-radius: var(--r-sm);
    color: var(--t-primary);
    cursor: pointer;
    transition: all var(--dur-fast);
}

.pagination-btn:hover:not(:disabled) {
    background: var(--c-fill2);
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-btn.active {
    background: var(--c-blue);
    color: #ffffff;
    border-color: var(--c-blue);
}

/* Dropdown Menus */
.sf-dropdown-wrap {
    position: relative;
}

.sf-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl);
    min-width: 170px;
    padding: 6px;
    z-index: 200;
    display: none;
    animation: menuFadeIn 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
}

@keyframes menuFadeIn {
    from { opacity: 0; transform: translateY(-6px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.sf-dropdown-menu.show {
    display: block;
}

.sf-dropdown-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 500;
    color: var(--t-primary);
    text-decoration: none;
    border-radius: var(--r-xs);
    cursor: pointer;
    transition: background var(--dur-fast);
}

.sf-dropdown-item:hover {
    background: var(--c-fill);
    color: var(--c-blue);
}

.sf-dropdown-item i {
    font-size: 16px;
}

/* Loading Overlay & Skeleton */
.loading-curtain {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(2px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    z-index: 50;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

@media (prefers-color-scheme: dark) {
    .loading-curtain {
        background: rgba(30,30,46,0.75);
    }
}

.loading-curtain.active {
    opacity: 1;
    pointer-events: auto;
}

.sf-spinner {
    width: 36px;
    height: 36px;
    border: 3.5px solid var(--c-fill2);
    border-top-color: var(--c-blue);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Side Quick View Drawer (Apple Sheet style) */
.quickview-drawer {
    position: fixed;
    top: 0;
    right: -520px;
    width: 500px;
    height: 100%;
    background: var(--c-surface);
    box-shadow: var(--shadow-xl);
    z-index: 1050;
    transition: right 0.32s var(--ease-ios);
    display: flex;
    flex-direction: column;
    border-left: 0.5px solid var(--c-separator);
}

.quickview-drawer.active {
    right: 0;
}

.quickview-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    backdrop-filter: blur(4px);
    z-index: 1040;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.quickview-backdrop.active {
    opacity: 1;
    pointer-events: auto;
}

.quickview-head {
    padding: 18px 22px;
    border-bottom: 0.5px solid var(--c-separator);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--c-surface2);
}

.quickview-head h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--t-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.quickview-close-btn {
    background: var(--c-fill);
    border: none;
    color: var(--t-secondary);
    cursor: pointer;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all var(--dur-fast);
}

.quickview-close-btn:hover {
    background: var(--c-red-light);
    color: var(--c-red);
}

.quickview-content-body {
    padding: 20px 22px;
    overflow-y: auto;
    flex-grow: 1;
}

.qv-subcard {
    background: var(--c-surface2);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    padding: 16px;
    margin-bottom: 16px;
}

.qv-subcard-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--t-label);
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 0.5px solid var(--c-separator2);
}

.qv-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.qv-field label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--t-label);
    margin-bottom: 2px;
}

.qv-field span, .qv-field strong {
    font-size: 13.5px;
    color: var(--t-primary);
}

.qv-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}

.qv-table th {
    padding: 8px 10px;
    background: var(--c-fill);
    color: var(--t-label);
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    text-align: left;
}

.qv-table td {
    padding: 8px 10px;
    border-bottom: 0.5px solid var(--c-separator2);
    color: var(--t-primary);
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 48px;
    color: var(--t-tertiary);
    margin-bottom: 14px;
}

.empty-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--t-primary);
    margin-bottom: 6px;
}

.empty-sub {
    font-size: 13px;
    color: var(--t-secondary);
    max-width: 320px;
    margin: 0 auto 16px;
}

/* Print Stylesheet */
@media print {
    .no-print, .filter-sidebar, .report-top-header .header-actions, .table-toolbar, .table-footer, .drill-actions, .quickview-drawer, .quickview-backdrop {
        display: none !important;
    }
    body, .report-root, .viewer-container, .viewer-layout, .preview-panel, .table-panel {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        box-shadow: none !important;
        border: none !important;
    }
    .report-table thead th {
        background: #f1f5f9 !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<div class="report-root">
    <div class="viewer-container">
        
        <!-- Header & Breadcrumbs -->
        <div class="report-top-header">
            <div>
                <div class="breadcrumb-shelf">
                    <a href="<?= APP_URL ?>/report"><i class="ph ph-squares-four"></i> Reports Hub</a>
                    <span>/</span>
                    <span class="category-tag"><?= htmlspecialchars(ucfirst($metadata['category'])) ?></span>
                </div>
                <div class="report-title-row">
                    <h1 class="report-title"><?= htmlspecialchars($metadata['title']) ?></h1>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="header-actions no-print">
                <!-- Share Dropdown -->
                <div class="sf-dropdown-wrap">
                    <button class="sf-btn" onclick="toggleDropdown('shareMenu')">
                        <i class="ph ph-share-network"></i> Share <i class="ph ph-caret-down" style="font-size: 11px;"></i>
                    </button>
                    <div class="sf-dropdown-menu" id="shareMenu">
                        <div class="sf-dropdown-item" onclick="copyShareLink()"><i class="ph ph-link"></i> Copy Link</div>
                        <div class="sf-dropdown-item" onclick="emailShare()"><i class="ph ph-envelope"></i> Email Report</div>
                        <div class="sf-dropdown-item" onclick="whatsappShare()"><i class="ph ph-whatsapp-logo"></i> WhatsApp</div>
                    </div>
                </div>

                <!-- Print Button -->
                <button class="sf-btn" onclick="openPrintLayout()">
                    <i class="ph ph-printer"></i> Print
                </button>

                <!-- Export Dropdown -->
                <div class="sf-dropdown-wrap">
                    <button class="sf-btn sf-btn-primary" onclick="toggleDropdown('exportMenu')">
                        <i class="ph ph-download-simple"></i> Export <i class="ph ph-caret-down" style="font-size: 11px;"></i>
                    </button>
                    <div class="sf-dropdown-menu" id="exportMenu">
                        <div class="sf-dropdown-item" onclick="triggerExport('excel')"><i class="ph ph-file-xls" style="color:#107c41;"></i> Excel (.xls)</div>
                        <div class="sf-dropdown-item" onclick="triggerExport('csv')"><i class="ph ph-file-csv" style="color:#0284c7;"></i> CSV (.csv)</div>
                        <div class="sf-dropdown-item" onclick="triggerExport('word')"><i class="ph ph-file-doc" style="color:#185abd;"></i> Word (.doc)</div>
                        <div class="sf-dropdown-item" onclick="triggerExport('xml')"><i class="ph ph-file-code" style="color:#d97706;"></i> XML</div>
                        <div class="sf-dropdown-item" onclick="triggerExport('json')"><i class="ph ph-brackets-curly" style="color:#7c3aed;"></i> JSON</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards (Auto-Calculated) -->
        <div class="kpi-row no-print" id="kpiRow">
            <div class="kpi-card blue">
                <div class="kpi-icon"><i class="ph ph-rows"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num" id="kpiTotalRecords">-</div>
                    <div class="kpi-lbl">Total Records</div>
                </div>
            </div>
            <div class="kpi-card green" id="kpiPrimaryCard" style="display:none;">
                <div class="kpi-icon"><i class="ph ph-currency-dollar"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num" id="kpiPrimaryVal">-</div>
                    <div class="kpi-lbl" id="kpiPrimaryLbl">Total Sum</div>
                </div>
            </div>
            <div class="kpi-card orange" id="kpiSecondaryCard" style="display:none;">
                <div class="kpi-icon"><i class="ph ph-chart-pie-slice"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num" id="kpiSecondaryVal">-</div>
                    <div class="kpi-lbl" id="kpiSecondaryLbl">Secondary Metric</div>
                </div>
            </div>
            <div class="kpi-card purple" id="kpiTertiaryCard" style="display:none;">
                <div class="kpi-icon"><i class="ph ph-trend-up"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num" id="kpiTertiaryVal">-</div>
                    <div class="kpi-lbl" id="kpiTertiaryLbl">Additional Metric</div>
                </div>
            </div>
        </div>

        <!-- Two Column Main Layout -->
        <div class="viewer-layout">
            
            <!-- Left Filter Sidebar -->
            <div class="filter-sidebar no-print">
                <div class="sf-card">
                    <div class="sf-card-header">
                        <h2 class="sf-card-title"><i class="ph ph-funnel-simple"></i> Report Filters</h2>
                        <button type="button" class="preset-chip" onclick="resetAllFilters()" title="Reset to defaults">Reset</button>
                    </div>

                    <form id="filterForm" onsubmit="event.preventDefault(); loadReportData(1);">
                        
                        <?php if (in_array('date_range', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Date Range Presets</label>
                                <div class="date-preset-shelf">
                                    <span class="preset-chip" onclick="setDatePreset('today', this)">Today</span>
                                    <span class="preset-chip" onclick="setDatePreset('this_month', this)">This Month</span>
                                    <span class="preset-chip" onclick="setDatePreset('last_month', this)">Last Month</span>
                                    <span class="preset-chip" onclick="setDatePreset('this_year', this)">This Year</span>
                                    <span class="preset-chip" onclick="setDatePreset('all_time', this)">All Time</span>
                                </div>
                            </div>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Start Date</label>
                                <input type="date" class="sf-input" name="start_date" id="filter_start_date" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="sf-form-group">
                                <label class="sf-form-label">End Date</label>
                                <input type="date" class="sf-input" name="end_date" id="filter_end_date" value="<?= date('Y-m-d') ?>">
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('customer', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Customer</label>
                                <select class="sf-select" name="customer" id="filter_customer">
                                    <option value="">-- All Customers --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c->id ?>"><?= htmlspecialchars($c->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('supplier', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Supplier / Vendor</label>
                                <select class="sf-select" name="supplier" id="filter_supplier">
                                    <option value="">-- All Suppliers --</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?= $s->id ?>"><?= htmlspecialchars($s->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('product', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Product / Item</label>
                                <select class="sf-select" name="product" id="filter_product">
                                    <option value="">-- All Products --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p->id ?>"><?= htmlspecialchars($p->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('warehouse', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Warehouse</label>
                                <select class="sf-select" name="warehouse" id="filter_warehouse">
                                    <option value="">-- All Warehouses --</option>
                                    <?php foreach ($warehouses as $w): ?>
                                        <option value="<?= $w->id ?>"><?= htmlspecialchars($w->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('category', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Category</label>
                                <select class="sf-select" name="category" id="filter_category">
                                    <option value="">-- All Categories --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('route', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Route</label>
                                <select class="sf-select" name="route" id="filter_route">
                                    <option value="">-- All Routes --</option>
                                    <?php foreach ($routes as $r): ?>
                                        <option value="<?= $r->id ?>"><?= htmlspecialchars($r->route_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('rep', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Sales Rep</label>
                                <select class="sf-select" name="rep" id="filter_rep">
                                    <option value="">-- All Reps --</option>
                                    <?php foreach ($reps as $r): ?>
                                        <option value="<?= $r->id ?>"><?= htmlspecialchars($r->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('payment_method', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Payment Method</label>
                                <select class="sf-select" name="payment_method" id="filter_payment_method">
                                    <option value="">-- All Methods --</option>
                                    <?php foreach ($payment_methods as $pm): ?>
                                        <option value="<?= $pm->id ?>"><?= htmlspecialchars($pm->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('status', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Status</label>
                                <select class="sf-select" name="status" id="filter_status">
                                    <option value="">-- All Statuses --</option>
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $st->id ?>"><?= htmlspecialchars($st->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('brand', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Brand</label>
                                <select class="sf-select" name="brand" id="filter_brand">
                                    <option value="">-- All Brands --</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?= htmlspecialchars($b->brand) ?>"><?= htmlspecialchars($b->brand) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('group', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Customer Group</label>
                                <select class="sf-select" name="group" id="filter_group">
                                    <option value="">-- All Groups --</option>
                                    <?php foreach ($groups as $g): ?>
                                        <option value="<?= htmlspecialchars($g->name) ?>"><?= htmlspecialchars($g->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('vehicle', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Vehicle</label>
                                <select class="sf-select" name="vehicle" id="filter_vehicle">
                                    <option value="">-- All Vehicles --</option>
                                    <?php foreach ($vehicles as $v): ?>
                                        <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('driver', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Driver</label>
                                <select class="sf-select" name="driver" id="filter_driver">
                                    <option value="">-- All Drivers --</option>
                                    <?php foreach ($drivers as $d): ?>
                                        <option value="<?= htmlspecialchars($d->name) ?>"><?= htmlspecialchars($d->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('partner', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Partner / Helper</label>
                                <select class="sf-select" name="partner" id="filter_partner">
                                    <option value="">-- All Partners --</option>
                                    <?php foreach ($partners as $p): ?>
                                        <option value="<?= htmlspecialchars($p->name) ?>"><?= htmlspecialchars($p->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('territory', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Territory</label>
                                <select class="sf-select" name="territory" id="filter_territory">
                                    <option value="">-- All Territories --</option>
                                    <?php foreach ($territories as $t): ?>
                                        <option value="<?= htmlspecialchars($t->territory) ?>"><?= htmlspecialchars($t->territory) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array('tb_type', $metadata['filters'] ?? [])): ?>
                            <div class="sf-form-group">
                                <label class="sf-form-label">Trial Balance Type</label>
                                <select class="sf-select" name="tb_type" id="filter_tb_type">
                                    <option value="pre_closing">Pre-Closing</option>
                                    <option value="post_closing">Post-Closing (Include Year-End)</option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 8px;">
                            <button type="submit" class="sf-btn sf-btn-primary" style="width: 100%;">
                                <i class="ph ph-funnel"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Preview & Data Table Panel -->
            <div class="preview-panel">
                
                <!-- Simulation Notice Alert -->
                <div class="sim-alert" id="simAlert" style="display: none;">
                    <i class="ph ph-warning-circle" style="font-size: 20px;"></i>
                    <div id="simAlertText"></div>
                </div>

                <!-- Table Card -->
                <div class="table-panel">
                    <!-- Loading Curtain -->
                    <div class="loading-curtain" id="tableLoadingCurtain">
                        <div class="sf-spinner"></div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--t-secondary);">Loading live report data...</div>
                    </div>

                    <!-- Toolbar -->
                    <div class="table-toolbar no-print">
                        <div class="table-search-box">
                            <i class="ph ph-magnifying-glass"></i>
                            <input type="text" class="table-search-input" id="tableSearch" placeholder="Filter rows in view..." onkeyup="clientFilterRows()">
                        </div>

                        <div class="table-toolbar-right">
                            <span>Rows per page:</span>
                            <select id="limitSelect" class="page-size-select" onchange="loadReportData(1)">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table Scroll Area -->
                    <div class="table-scroll" id="printableArea">
                        <table class="report-table" id="reportDataTable">
                            <thead>
                                <tr id="tableHeaders">
                                    <!-- Injected via JavaScript -->
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Injected via JavaScript -->
                            </tbody>
                            <tfoot id="tableFoot">
                                <!-- Injected via JavaScript -->
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="table-footer no-print">
                        <div class="pagination-info" id="paginationInfo">
                            Showing 0 to 0 of 0 entries
                        </div>
                        <div class="pagination-controls" id="paginationControls">
                            <!-- Injected via JavaScript -->
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Side Quick View Drawer -->
<div class="quickview-backdrop no-print" id="quickviewBackdrop" onclick="closeQuickView()"></div>
<div class="quickview-drawer no-print" id="quickviewDrawer">
    <div class="quickview-head">
        <h3 id="quickviewTitle"><i class="ph ph-eye"></i> Quick View</h3>
        <button class="quickview-close-btn" onclick="closeQuickView()"><i class="ph ph-x"></i></button>
    </div>
    <div class="quickview-content-body" id="quickviewContentBody">
        <!-- Injected via JavaScript -->
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
        // Render Header Columns
        renderTableHeaders();

        // Pre-fill filters from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams.entries()) {
            const field = document.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value;
            }
        }

        // Initialize First Fetch
        loadReportData(1);

        // Global click handler to close dropdowns
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.sf-dropdown-wrap')) {
                document.querySelectorAll('.sf-dropdown-menu').forEach(m => m.classList.remove('show'));
            }
        });
    });

    function renderTableHeaders() {
        const headerRow = document.getElementById('tableHeaders');
        headerRow.innerHTML = '';
        for (const [colKey, def] of Object.entries(columnsMeta)) {
            const th = document.createElement('th');
            th.textContent = def.label;
            if (def.align === 'right') {
                th.style.textAlign = 'right';
            }
            if (def.sortable !== false) {
                th.classList.add('sortable');
                th.onclick = function() { toggleTableSort(colKey); };
            }
            th.setAttribute('data-col-key', colKey);
            headerRow.appendChild(th);
        }
    }

    function toggleDropdown(id) {
        document.querySelectorAll('.sf-dropdown-menu').forEach(m => {
            if (m.id !== id) m.classList.remove('show');
        });
        const m = document.getElementById(id);
        if (m) m.classList.toggle('show');
    }

    function setDatePreset(type, el) {
        document.querySelectorAll('.date-preset-shelf .preset-chip').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');

        const now = new Date();
        const startInput = document.getElementById('filter_start_date');
        const endInput = document.getElementById('filter_end_date');
        if (!startInput || !endInput) return;

        const fmt = d => d.toISOString().split('T')[0];

        if (type === 'today') {
            startInput.value = fmt(now);
            endInput.value = fmt(now);
        } else if (type === 'this_month') {
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            startInput.value = fmt(start);
            endInput.value = fmt(now);
        } else if (type === 'last_month') {
            const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const end = new Date(now.getFullYear(), now.getMonth(), 0);
            startInput.value = fmt(start);
            endInput.value = fmt(end);
        } else if (type === 'this_year') {
            const start = new Date(now.getFullYear(), 0, 1);
            startInput.value = fmt(start);
            endInput.value = fmt(now);
        } else if (type === 'all_time') {
            startInput.value = '2000-01-01';
            endInput.value = fmt(now);
        }
        loadReportData(1);
    }

    function resetAllFilters() {
        document.getElementById('filterForm').reset();
        document.querySelectorAll('.date-preset-shelf .preset-chip').forEach(c => c.classList.remove('active'));
        loadReportData(1);
    }

    function loadReportData(page) {
        currentPage = page;
        const curtain = document.getElementById('tableLoadingCurtain');
        curtain.classList.add('active');

        // Form parameters
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
                curtain.classList.remove('active');

                if (!data.success) {
                    alert('Error loading report: ' + data.message);
                    return;
                }

                // Simulation Warning
                const simAlert = document.getElementById('simAlert');
                if (data.simulation) {
                    simAlert.style.display = 'flex';
                    let errorText = "Simulation Mode Active: Real Database Table is Missing. Displaying Simulated Data.";
                    if (data.db_error) {
                        errorText += " (" + data.db_error + ")";
                    }
                    document.getElementById('simAlertText').innerHTML = `<strong>Notice:</strong> ${errorText}`;
                } else {
                    simAlert.style.display = 'none';
                }

                currentRows = data.rows || [];
                totalEntries = data.total_rows || 0;

                updateKpis(data);
                renderTableBody(data.rows);
                renderTableFoot(data.grand_totals);
                renderPagination(page, data.total_rows);
            })
            .catch(err => {
                curtain.classList.remove('active');
                console.error(err);
            });
    }

    function updateKpis(data) {
        document.getElementById('kpiTotalRecords').textContent = (data.total_rows || 0).toLocaleString();

        const totals = data.grand_totals || {};
        const sumCols = Object.keys(columnsMeta).filter(k => columnsMeta[k].total === 'sum');

        // Primary KPI
        const primCard = document.getElementById('kpiPrimaryCard');
        if (sumCols.length > 0) {
            const firstCol = sumCols[0];
            const val = totals[firstCol] !== undefined ? totals[firstCol] : 0;
            const def = columnsMeta[firstCol];
            document.getElementById('kpiPrimaryLbl').textContent = 'TOTAL ' + def.label.toUpperCase();
            document.getElementById('kpiPrimaryVal').textContent = (def.type === 'currency' ? 'Rs. ' : '') + parseFloat(val).toLocaleString(undefined, {minimumFractionDigits: def.type === 'currency' ? 2 : 0, maximumFractionDigits: def.type === 'currency' ? 2 : 0});
            primCard.style.display = 'flex';
        } else {
            primCard.style.display = 'none';
        }

        // Secondary KPI
        const secCard = document.getElementById('kpiSecondaryCard');
        if (sumCols.length > 1) {
            const secondCol = sumCols[1];
            const val = totals[secondCol] !== undefined ? totals[secondCol] : 0;
            const def = columnsMeta[secondCol];
            document.getElementById('kpiSecondaryLbl').textContent = 'TOTAL ' + def.label.toUpperCase();
            document.getElementById('kpiSecondaryVal').textContent = (def.type === 'currency' ? 'Rs. ' : '') + parseFloat(val).toLocaleString(undefined, {minimumFractionDigits: def.type === 'currency' ? 2 : 0, maximumFractionDigits: def.type === 'currency' ? 2 : 0});
            secCard.style.display = 'flex';
        } else {
            secCard.style.display = 'none';
        }

        // Tertiary KPI
        const terCard = document.getElementById('kpiTertiaryCard');
        if (sumCols.length > 2) {
            const thirdCol = sumCols[2];
            const val = totals[thirdCol] !== undefined ? totals[thirdCol] : 0;
            const def = columnsMeta[thirdCol];
            document.getElementById('kpiTertiaryLbl').textContent = 'TOTAL ' + def.label.toUpperCase();
            document.getElementById('kpiTertiaryVal').textContent = (def.type === 'currency' ? 'Rs. ' : '') + parseFloat(val).toLocaleString(undefined, {minimumFractionDigits: def.type === 'currency' ? 2 : 0, maximumFractionDigits: def.type === 'currency' ? 2 : 0});
            terCard.style.display = 'flex';
        } else {
            terCard.style.display = 'none';
        }
    }

    function renderTableBody(rows) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        if (!rows || rows.length === 0) {
            const colCount = Object.keys(columnsMeta).length;
            tbody.innerHTML = `
                <tr>
                    <td colspan="${colCount}">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="ph ph-tray"></i></div>
                            <div class="empty-title">No Records Found</div>
                            <div class="empty-sub">No data matching your selected filters were found. Try adjusting or resetting your filter criteria.</div>
                            <button type="button" class="sf-btn sf-btn-secondary" onclick="resetAllFilters()"><i class="ph ph-arrow-counter-clockwise"></i> Reset Filters</button>
                        </div>
                    </td>
                </tr>
            `;
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
                if (def.type === 'currency' || def.type === 'number') {
                    td.classList.add('numeric-cell');
                }

                // Check for interactive drilldown
                const drill = detectDrilldown(colKey, r, def);

                if (def.type === 'currency') {
                    td.textContent = 'Rs. ' + parseFloat(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (def.type === 'number') {
                    td.textContent = parseInt(val || 0).toLocaleString();
                } else if (def.type === 'badge') {
                    const cleanVal = String(val || '').toLowerCase();
                    if (cleanVal === 'completed' || cleanVal === 'paid' || cleanVal === 'active' || cleanVal === 'cleared') {
                        td.innerHTML = `<span class="sf-badge sf-badge-success">${val}</span>`;
                    } else if (cleanVal === 'pending' || cleanVal === 'unpaid' || cleanVal === 'partial') {
                        td.innerHTML = `<span class="sf-badge sf-badge-warning">${val}</span>`;
                    } else if (cleanVal === 'voided' || cleanVal === 'bounced' || cleanVal === 'cancelled' || cleanVal === 'overdue') {
                        td.innerHTML = `<span class="sf-badge sf-badge-danger">${val}</span>`;
                    } else {
                        td.innerHTML = `<span class="sf-badge sf-badge-neutral">${val}</span>`;
                    }
                } else if (drill) {
                    const tabUrl = getDrilldownUrl(drill.type, drill.id, drill.val);
                    td.innerHTML = `
                        <div class="drill-cell">
                            <span class="drill-link" onclick="handleDrilldownClick(event, '${drill.type}', '${drill.id}', '${drill.val}')">${drill.val}</span>
                            <span class="drill-actions no-print">
                                <a href="${tabUrl}" target="_blank" title="Open in New Tab" class="drill-action-btn"><i class="ph ph-arrow-square-out"></i></a>
                                <button type="button" onclick="triggerQuickView('${drill.type}', '${drill.id}', '${drill.val}')" title="Quick View" class="drill-action-btn"><i class="ph ph-eye"></i></button>
                            </span>
                        </div>
                    `;
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

        if (!currentRows || currentRows.length === 0) return;

        // 1. Page Subtotal Row
        const subRow = document.createElement('tr');
        subRow.className = 'total-row';
        let firstCol = true;

        for (const [colKey, def] of Object.entries(columnsMeta)) {
            const td = document.createElement('td');
            if (def.align === 'right') {
                td.style.textAlign = 'right';
            }
            if (def.type === 'currency' || def.type === 'number') {
                td.classList.add('numeric-cell');
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

        // 2. Query Grand Totals Row
        const grandRow = document.createElement('tr');
        grandRow.className = 'grand-total-row';
        firstCol = true;

        for (const [colKey, def] of Object.entries(columnsMeta)) {
            const td = document.createElement('td');
            if (def.align === 'right') {
                td.style.textAlign = 'right';
            }
            if (def.type === 'currency' || def.type === 'number') {
                td.classList.add('numeric-cell');
            }

            if (firstCol) {
                td.textContent = 'Grand Total (' + totalEntries.toLocaleString() + ' records)';
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

        // Highlight Active Header
        document.querySelectorAll('.report-table thead th').forEach(th => {
            th.classList.remove('sorted-asc', 'sorted-desc');
        });
        const activeTh = document.querySelector(`.report-table thead th[data-col-key="${colKey}"]`);
        if (activeTh) {
            activeTh.classList.add(currentSortDir === 'ASC' ? 'sorted-asc' : 'sorted-desc');
        }

        loadReportData(1);
    }

    function clientFilterRows() {
        const query = document.getElementById('tableSearch').value.toLowerCase().trim();
        if (!query) {
            renderTableBody(currentRows);
            return;
        }
        const filtered = currentRows.filter(r => {
            return Object.values(r).some(val => String(val).toLowerCase().includes(query));
        });
        renderTableBody(filtered);
    }

    function renderPagination(page, total) {
        const limit = parseInt(document.getElementById('limitSelect').value);
        const totalPages = Math.ceil(total / limit) || 1;
        
        const start = total === 0 ? 0 : (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);
        document.getElementById('paginationInfo').textContent = `Showing ${start.toLocaleString()} to ${end.toLocaleString()} of ${total.toLocaleString()} entries`;

        const controls = document.getElementById('paginationControls');
        controls.innerHTML = '';

        // Prev Button
        const prev = document.createElement('button');
        prev.className = 'pagination-btn';
        prev.innerHTML = `<i class="ph ph-caret-left"></i>`;
        prev.disabled = page === 1;
        prev.onclick = function() { loadReportData(page - 1); };
        controls.appendChild(prev);

        // Page Number Buttons
        let startPage = Math.max(1, page - 2);
        let endPage = Math.min(totalPages, page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.className = 'pagination-btn' + (i === page ? ' active' : '');
            btn.textContent = i;
            btn.onclick = function() { loadReportData(i); };
            controls.appendChild(btn);
        }

        // Next Button
        const next = document.createElement('button');
        next.className = 'pagination-btn';
        next.innerHTML = `<i class="ph ph-caret-right"></i>`;
        next.disabled = page === totalPages;
        next.onclick = function() { loadReportData(page + 1); };
        controls.appendChild(next);
    }

    // --- Share Functions ---
    function copyShareLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Report URL copied to clipboard!');
        });
    }

    function emailShare() {
        const url = encodeURIComponent(window.location.href);
        const subject = encodeURIComponent('Curtiss ERP Live Report: ' + '<?= htmlspecialchars($metadata['title']) ?>');
        window.location.href = `mailto:?subject=${subject}&body=View the live report in Curtiss ERP:%0D%0A${url}`;
    }

    function whatsappShare() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Curtiss ERP Report - <?= htmlspecialchars($metadata['title']) ?>: ') + url;
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
    }

    // --- Export Handler ---
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

    // --- High Fidelity Print Window ---
    function openPrintLayout() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        window.open('<?= APP_URL ?>/report/print_report/' + reportKey + '?' + params.toString(), '_blank');
    }

    // --- Interactive BI Drill-down & Quick View Helpers ---
    function detectDrilldown(colKey, row, def) {
        let type = def.drilldown || null;
        let id = row.id || row[`${type}_id`] || '';
        let val = row[colKey] !== undefined ? row[colKey] : '';

        if (!type) {
            const keyLower = colKey.toLowerCase();
            if (keyLower.includes('customer')) {
                type = 'customer';
                id = row.customer_id || row.id || '';
            } else if (keyLower.includes('supplier') || keyLower.includes('vendor')) {
                type = 'supplier';
                id = row.vendor_id || row.supplier_id || row.id || '';
            } else if (keyLower.includes('item_code') || keyLower.includes('sku') || keyLower.includes('product_code')) {
                type = 'product';
                id = row.item_id || row.id || '';
            } else if (keyLower === 'product_name' || keyLower === 'item_name' || (keyLower === 'name' && reportKey.includes('stock'))) {
                type = 'product';
                id = row.item_id || row.id || '';
            } else if (keyLower.includes('invoice')) {
                type = 'invoice';
                id = row.invoice_id || row.id || '';
            } else if (keyLower.includes('route')) {
                type = 'route';
                id = row.route_id || row.id || '';
            } else if (keyLower === 'rep_name' || keyLower === 'sales_rep' || keyLower === 'rep' || keyLower === 'sales_representative') {
                type = 'rep';
                id = row.rep_id || row.user_id || row.id || '';
            } else if (keyLower.includes('warehouse')) {
                type = 'warehouse';
                id = row.warehouse_id || row.id || '';
            } else if (keyLower.includes('po_number') || keyLower.includes('po_no') || keyLower === 'purchase_order') {
                type = 'po';
                id = row.po_id || row.id || '';
            } else if (keyLower.includes('grn_number') || keyLower.includes('grn_no')) {
                type = 'grn';
                id = row.grn_id || row.id || '';
            } else if (keyLower.includes('cheque')) {
                type = 'cheque';
                id = row.cheque_id || row.id || '';
            } else if (keyLower.includes('driver')) {
                type = 'driver';
                id = row.driver_id || row.employee_id || row.id || '';
            } else if (keyLower.includes('vehicle')) {
                type = 'vehicle';
                id = row.vehicle_id || row.id || '';
            } else if (keyLower === 'payment_ref' || keyLower === 'reference' || keyLower === 'ref_doc' || keyLower === 'ref') {
                const valStr = String(val);
                const rowType = String(row.type || '').toLowerCase();
                if (valStr.startsWith('INV-')) {
                    type = 'invoice';
                } else if (valStr.startsWith('GRN-') || rowType === 'grn') {
                    type = 'grn';
                } else if (valStr.startsWith('PO-') || rowType === 'po') {
                    type = 'po';
                } else {
                    type = reportKey === 'supplier_statement' ? 'supplier_payment' : 'payment';
                }
                id = row.id || '';
            }
        }

        if (!type || !val) return null;
        if (!id && row.id) id = row.id;

        return { type, id, val };
    }

    function getDrilldownUrl(type, id, val) {
        let rKey = null;
        let filterParam = null;

        switch (type) {
            case 'customer':
                rKey = 'customer_statement';
                filterParam = 'customer';
                break;
            case 'supplier':
                rKey = 'supplier_statement';
                filterParam = 'supplier';
                break;
            case 'product':
                rKey = 'stock_ledger';
                filterParam = 'product';
                break;
            case 'warehouse':
                rKey = 'stock_balance';
                filterParam = 'warehouse';
                break;
            case 'route':
                rKey = 'stock_movement';
                filterParam = 'route';
                break;
            case 'rep':
                rKey = 'sales_summary';
                filterParam = 'rep';
                break;
            case 'invoice':
                return `<?= APP_URL ?>/sales/show/${id}`;
            case 'po':
                return `<?= APP_URL ?>/purchase/show/${id}`;
            case 'grn':
                return `<?= APP_URL ?>/grn?search=${val}`;
            case 'payment':
                return `<?= APP_URL ?>/customerpayment?payment_id=${id}`;
            case 'supplier_payment':
                return `<?= APP_URL ?>/payment?payment_id=${id}`;
            case 'cheque':
                return `<?= APP_URL ?>/cheque/show/${id}`;
            default:
                return '#';
        }

        if (rKey) {
            const start = document.getElementById('filter_start_date') ? document.getElementById('filter_start_date').value : '';
            const end = document.getElementById('filter_end_date') ? document.getElementById('filter_end_date').value : '';
            let url = `<?= APP_URL ?>/report/viewer/${rKey}?${filterParam}=${id}`;
            if (start) url += `&start_date=${start}`;
            if (end) url += `&end_date=${end}`;
            return url;
        }
        return '#';
    }

    function handleDrilldownClick(event, type, id, val) {
        event.preventDefault();
        const url = getDrilldownUrl(type, id, val);
        if (url && url !== '#') {
            window.location.href = url;
        } else {
            triggerQuickView(type, id, val);
        }
    }

    function formatCurrency(val) {
        return 'Rs. ' + parseFloat(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // --- Side Quick View Drawer Trigger ---
    function triggerQuickView(type, id, number) {
        const drawer = document.getElementById('quickviewDrawer');
        const backdrop = document.getElementById('quickviewBackdrop');
        const content = document.getElementById('quickviewContentBody');
        const title = document.getElementById('quickviewTitle');

        drawer.classList.add('active');
        backdrop.classList.add('active');
        content.innerHTML = `
            <div style="padding: 40px; text-align: center;">
                <div class="sf-spinner" style="margin: 0 auto 12px;"></div>
                <div style="font-size: 13px; color: var(--t-secondary);">Loading ${type} details...</div>
            </div>
        `;
        title.innerHTML = `<i class="ph ph-eye"></i> Quick Preview`;

        const params = new URLSearchParams();
        params.append('type', type);
        if (id) params.append('id', id);
        if (number) params.append('number', number);

        fetch('<?= APP_URL ?>/report/quick_view?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    title.innerHTML = `<i class="ph ph-warning-circle" style="color: var(--c-red);"></i> Error`;
                    content.innerHTML = `<div style="color: var(--c-red); padding: 20px; font-weight: 600;">${data.message || 'Record details not found.'}</div>`;
                    return;
                }

                title.innerHTML = `<i class="ph ph-eye"></i> ${type.toUpperCase()} PREVIEW`;
                renderQuickViewContent(type, data);
            })
            .catch(err => {
                title.innerHTML = `<i class="ph ph-warning-circle" style="color: var(--c-red);"></i> Error`;
                content.innerHTML = `<div style="color: var(--c-red); padding: 20px;">An error occurred while loading record details.</div>`;
                console.error(err);
            });
    }

    function closeQuickView() {
        document.getElementById('quickviewDrawer').classList.remove('active');
        document.getElementById('quickviewBackdrop').classList.remove('active');
    }

    function renderQuickViewContent(type, data) {
        const content = document.getElementById('quickviewContentBody');
        const ent = data.entity;
        let html = '';

        switch (type) {
            case 'customer':
                html += `
                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Customer Profile</div>
                        <div class="qv-grid">
                            <div class="qv-field" style="grid-column: span 2;"><label>Name</label><strong>${ent.name}</strong></div>
                            <div class="qv-field"><label>Type</label><span>${ent.customer_type}</span></div>
                            <div class="qv-field"><label>Phone</label><span>${ent.phone || 'N/A'}</span></div>
                            <div class="qv-field"><label>Email</label><span>${ent.email || 'N/A'}</span></div>
                            <div class="qv-field"><label>Territory</label><span>${ent.territory || 'N/A'}</span></div>
                            <div class="qv-field" style="grid-column: span 2;"><label>Address</label><span>${ent.address || 'N/A'}</span></div>
                        </div>
                    </div>
                    
                    <div class="qv-subcard" style="text-align: center;">
                        <div class="qv-subcard-title">Outstanding Balance</div>
                        <div style="font-size: 22px; font-weight: 800; font-family: var(--f-mono); color: ${ent.outstanding_balance > 0 ? 'var(--c-red)' : 'var(--c-green)'};">
                            ${formatCurrency(ent.outstanding_balance)}
                        </div>
                    </div>

                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Recent Invoices</div>
                        <table class="qv-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th style="text-align: right;">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.invoices && data.invoices.length > 0 ? data.invoices.map(inv => `
                                    <tr>
                                        <td>${inv.invoice_date}</td>
                                        <td><a href="javascript:void(0)" onclick="triggerQuickView('invoice', '${inv.id}', '${inv.invoice_number}')" style="color: var(--c-blue); font-weight: 600;">${inv.invoice_number}</a></td>
                                        <td style="text-align: right; font-family: var(--f-mono);">${formatCurrency(inv.total_amount)}</td>
                                        <td><span class="sf-badge ${inv.status.toLowerCase() === 'paid' ? 'sf-badge-success' : 'sf-badge-warning'}">${inv.status}</span></td>
                                    </tr>
                                `).join('') : '<tr><td colspan="4" style="text-align: center; color: var(--t-tertiary);">No recent invoices</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="${getDrilldownUrl('customer', ent.id, ent.name)}" class="sf-btn sf-btn-primary" style="flex: 1;"><i class="ph ph-file-text"></i> Customer Statement</a>
                        <button onclick="closeQuickView()" class="sf-btn sf-btn-secondary">Close</button>
                    </div>
                `;
                break;

            case 'product':
                html += `
                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Product Details</div>
                        <div class="qv-grid">
                            <div class="qv-field"><label>SKU / Code</label><strong>${ent.item_code}</strong></div>
                            <div class="qv-field"><label>Brand</label><span>${ent.brand || 'N/A'}</span></div>
                            <div class="qv-field" style="grid-column: span 2;"><label>Name</label><span>${ent.name}</span></div>
                            <div class="qv-field"><label>Retail Price</label><span style="font-family: var(--f-mono); font-weight: 600;">${formatCurrency(ent.price)}</span></div>
                            <div class="qv-field"><label>Cost Price</label><span style="font-family: var(--f-mono);">${formatCurrency(ent.cost)}</span></div>
                        </div>
                    </div>

                    <div class="qv-subcard" style="text-align: center;">
                        <div class="qv-subcard-title">Total Stock Level</div>
                        <div style="font-size: 22px; font-weight: 800; font-family: var(--f-mono); color: var(--c-blue);">
                            ${parseInt(ent.qty_on_hand || 0).toLocaleString()} Units
                        </div>
                    </div>

                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Stock By Warehouse</div>
                        <table class="qv-table">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th style="text-align: right;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.stock && data.stock.length > 0 ? data.stock.map(st => `
                                    <tr>
                                        <td>${st.warehouse_name}</td>
                                        <td style="text-align: right; font-weight: 600; font-family: var(--f-mono);">${parseInt(st.quantity).toLocaleString()}</td>
                                    </tr>
                                `).join('') : '<tr><td colspan="2" style="text-align: center; color: var(--t-tertiary);">No warehouse stock info</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="${getDrilldownUrl('product', ent.id, ent.name)}" class="sf-btn sf-btn-primary" style="flex: 1;"><i class="ph ph-chart-line"></i> Stock Ledger</a>
                        <button onclick="closeQuickView()" class="sf-btn sf-btn-secondary">Close</button>
                    </div>
                `;
                break;

            case 'invoice':
                html += `
                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Invoice Details</div>
                        <div class="qv-grid">
                            <div class="qv-field"><label>Invoice #</label><strong style="color: var(--c-blue);">${ent.invoice_number}</strong></div>
                            <div class="qv-field"><label>Date</label><span>${ent.invoice_date}</span></div>
                            <div class="qv-field" style="grid-column: span 2;"><label>Customer</label><span>${ent.customer_name}</span></div>
                            <div class="qv-field"><label>Due Date</label><span>${ent.due_date || 'N/A'}</span></div>
                            <div class="qv-field"><label>Status</label><span class="sf-badge ${ent.status.toLowerCase() === 'paid' ? 'sf-badge-success' : 'sf-badge-warning'}">${ent.status}</span></div>
                        </div>
                    </div>

                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Invoice Items</div>
                        <table class="qv-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="text-align: right;">Qty</th>
                                    <th style="text-align: right;">Price</th>
                                    <th style="text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.items && data.items.length > 0 ? data.items.map(item => `
                                    <tr>
                                        <td>${item.item_name}</td>
                                        <td style="text-align: right; font-family: var(--f-mono);">${parseInt(item.quantity).toLocaleString()}</td>
                                        <td style="text-align: right; font-family: var(--f-mono);">${formatCurrency(item.unit_price)}</td>
                                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 600;">${formatCurrency(item.total_amount || (item.quantity * item.unit_price))}</td>
                                    </tr>
                                `).join('') : '<tr><td colspan="4" style="text-align: center; color: var(--t-tertiary);">No items in invoice</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Financial Summary</div>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between;"><span>Subtotal</span><span style="font-family: var(--f-mono);">${formatCurrency(ent.total)}</span></div>
                            ${ent.discount > 0 ? `<div style="display: flex; justify-content: space-between; color: var(--c-red);"><span>Discount</span><span style="font-family: var(--f-mono);">-${formatCurrency(ent.discount)}</span></div>` : ''}
                            ${ent.tax > 0 ? `<div style="display: flex; justify-content: space-between;"><span>Tax</span><span style="font-family: var(--f-mono);">${formatCurrency(ent.tax)}</span></div>` : ''}
                            <div style="display: flex; justify-content: space-between; font-weight: 750; border-top: 0.5px solid var(--c-separator); padding-top: 6px; font-size: 14px;">
                                <span>Net Total</span><span style="font-family: var(--f-mono); font-weight: 800; color: var(--c-blue);">${formatCurrency(ent.net_total)}</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="<?= APP_URL ?>/sales/show/${ent.id}" target="_blank" class="sf-btn sf-btn-primary" style="flex: 1;"><i class="ph ph-printer"></i> Open Invoice</a>
                        <button onclick="closeQuickView()" class="sf-btn sf-btn-secondary">Close</button>
                    </div>
                `;
                break;

            case 'supplier':
                html += `
                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Supplier Info</div>
                        <div class="qv-grid">
                            <div class="qv-field" style="grid-column: span 2;"><label>Name</label><strong>${ent.name}</strong></div>
                            <div class="qv-field"><label>Phone</label><span>${ent.phone || 'N/A'}</span></div>
                            <div class="qv-field"><label>Email</label><span>${ent.email || 'N/A'}</span></div>
                            <div class="qv-field" style="grid-column: span 2;"><label>Address</label><span>${ent.address || 'N/A'}</span></div>
                        </div>
                    </div>

                    <div class="qv-subcard" style="text-align: center;">
                        <div class="qv-subcard-title">Outstanding Balance</div>
                        <div style="font-size: 22px; font-weight: 800; font-family: var(--f-mono); color: ${ent.outstanding_balance > 0 ? 'var(--c-red)' : 'var(--c-green)'};">
                            ${formatCurrency(ent.outstanding_balance)}
                        </div>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="${getDrilldownUrl('supplier', ent.id, ent.name)}" class="sf-btn sf-btn-primary" style="flex: 1;"><i class="ph ph-file-text"></i> Supplier Statement</a>
                        <button onclick="closeQuickView()" class="sf-btn sf-btn-secondary">Close</button>
                    </div>
                `;
                break;

            default:
                html += `
                    <div class="qv-subcard">
                        <div class="qv-subcard-title">Record Details</div>
                        <pre style="font-size: 12px; white-space: pre-wrap; word-break: break-all; font-family: var(--f-mono);">${JSON.stringify(ent, null, 2)}</pre>
                    </div>
                    <div style="margin-top: 20px; display: flex;">
                        <button onclick="closeQuickView()" class="sf-btn sf-btn-secondary" style="width:100%;">Close</button>
                    </div>
                `;
                break;
        }

        content.innerHTML = html;
    }
</script>
