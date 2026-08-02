<!-- Inter Font, Phosphor Icons & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — ESTIMATES & QUOTATIONS
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.10);
    --c-fill2:        rgba(120,120,128,0.15);
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
    --c-purple-light: #f5eeff;

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
    --r-xl: 26px;
    --r-pill: 999px;

    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
    --dur-mid:     0.28s;
    --dur-slow:    0.42s;
}

.inv-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0px 24px 140px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

/* ---- Page Header ---- */
.inv-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.inv-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 6px;
}
.inv-title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--t-primary);
}

/* ---- Quick Filter Pills ---- */
.quick-links {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    align-items: center;
    background: var(--c-surface);
    padding: 8px 12px;
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}
.quick-links-label {
    font-size: 11px;
    color: var(--t-label);
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-right: 8px;
}
.btn-quick {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    text-decoration: none;
    cursor: pointer;
    transition: all var(--dur-fast);
}
.btn-quick:hover {
    background: var(--c-fill);
    color: var(--t-primary);
}
.btn-quick.active {
    background: var(--c-blue-light);
    color: var(--c-blue);
    border-color: var(--c-blue-mid);
}

/* ---- KPI Metrics Grid ---- */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.kpi-card {
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-lg);
    padding: 16px 20px;
    box-shadow: var(--shadow-xs);
    display: flex;
    align-items: center;
    gap: 16px;
}
.kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--r-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.kpi-content {
    display: flex;
    flex-direction: column;
}
.kpi-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--t-label);
    margin-bottom: 2px;
}
.kpi-value {
    font-size: 20px;
    font-weight: 800;
    font-family: var(--f-mono);
    color: var(--t-primary);
    line-height: 1.2;
}

/* ---- Alerts ---- */
.sf-alert {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--c-surface);
    border-radius: var(--r-md);
    padding: 14px 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-xs);
    border: 0.5px solid var(--c-separator);
    border-left-width: 3px;
    font-size: 14px;
}
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 600; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }

/* ---- Filter Shelf ---- */
.filter-shelf {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 18px;
}
.filter-chip {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    color: var(--t-secondary);
    box-shadow: var(--shadow-xs);
}
.filter-chip-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--t-label);
    text-transform: uppercase;
}
.pg-size-sel {
    font-family: var(--f-system); font-size: 13px; font-weight: 600;
    color: var(--t-primary);
    background: transparent;
    border: none;
    outline: none; cursor: pointer;
}
.filter-reset {
    background: transparent;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    cursor: pointer;
    transition: all var(--dur-fast);
}
.filter-reset:hover { background: var(--c-fill); color: var(--t-primary); }
.filter-count {
    margin-left: auto;
    font-size: 13px;
    color: var(--t-secondary);
    font-weight: 500;
}
.filter-count strong { color: var(--t-primary); font-weight: 700; }

/* ---- Table Panel ---- */
.table-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.inv-table { width: 100%; border-collapse: collapse; }
.inv-table thead th {
    padding: 13px 18px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--t-label);
    background: var(--c-surface2);
    border-bottom: 0.5px solid var(--c-separator);
    white-space: nowrap;
    text-align: left;
}
.inv-table tbody tr {
    transition: background var(--dur-fast);
    border-bottom: 0.5px solid var(--c-separator2);
}
.inv-table tbody tr:last-child { border-bottom: none; }
.inv-table tbody tr:hover { background: var(--c-fill2); }
.inv-table td {
    padding: 14px 18px;
    font-size: 14px;
    color: var(--t-primary);
    vertical-align: middle;
}

/* ---- Status Badges & Select ---- */
.status-select-wrap {
    position: relative;
    display: inline-block;
}
.status-badge-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: var(--r-pill);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.01em;
    border: none;
    cursor: pointer;
    outline: none;
    font-family: var(--f-system);
}
.status-badge-btn .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.st-Draft    { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.st-Draft .dot { background: #64748b; }

.st-Sent     { background: var(--c-blue-light); color: var(--c-blue); border: 1px solid var(--c-blue-mid); }
.st-Sent .dot { background: var(--c-blue); }

.st-Accepted { background: var(--c-green-light); color: #15803d; border: 1px solid #bbf7d0; }
.st-Accepted .dot { background: var(--c-green); }

.st-Declined { background: var(--c-red-light); color: var(--c-red); border: 1px solid rgba(255,59,48,0.3); }
.st-Declined .dot { background: var(--c-red); }

.st-Invoiced { background: var(--c-purple-light); color: var(--c-purple); border: 1px solid rgba(175,82,222,0.3); }
.st-Invoiced .dot { background: var(--c-purple); }

/* ---- Row Actions ---- */
.row-acts { display: flex; gap: 8px; justify-content: flex-end; align-items: center; }
.act-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--c-fill);
    color: var(--t-secondary);
    border: none; cursor: pointer; text-decoration: none;
    font-size: 14px;
    transition: all var(--dur-fast) var(--ease-spring);
}
.act-btn:hover { transform: scale(1.12); }
.act-btn.view:hover   { background: var(--c-blue-light);   color: var(--c-blue); }
.act-btn.convert {
    width: auto;
    padding: 0 12px;
    height: 30px;
    border-radius: var(--r-pill);
    background: var(--c-green-light);
    color: #15803d;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid #bbf7d0;
}
.act-btn.convert:hover {
    background: var(--c-green);
    color: #fff;
    transform: scale(1.03);
}

/* ---- Dynamic Island Command Bar ---- */
.cmd-bar {
    position: fixed;
    bottom: 28px; left: 50%;
    transform: translateX(-50%);
    background: rgba(28, 28, 30, 0.92);
    backdrop-filter: saturate(180%) blur(28px);
    -webkit-backdrop-filter: saturate(180%) blur(28px);
    border: 0.5px solid rgba(255,255,255,0.12);
    border-radius: var(--r-pill);
    padding: 7px 10px;
    display: flex; align-items: center; gap: 6px;
    box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3);
    z-index: 1000;
}
.cmd-search {
    display: flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.1);
    border-radius: var(--r-pill);
    padding: 8px 14px;
    width: 260px;
    transition: width var(--dur-slow) var(--ease-ios),
                background var(--dur-mid);
}
.cmd-search:focus-within {
    width: 400px;
    background: rgba(255,255,255,0.18);
}
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 15px; flex-shrink: 0; }
.cmd-search input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 13px; font-weight: 500;
    font-family: var(--f-system); width: 100%;
}
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-kbd {
    font-family: var(--f-mono);
    font-size: 10px;
    color: rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.1);
    padding: 2px 5px;
    border-radius: 4px;
}
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-cta {
    display: flex; align-items: center; gap: 7px;
    background: #fff; color: #1c1c1e;
    border: none; border-radius: var(--r-pill);
    padding: 0 18px; height: 38px;
    font-size: 13px; font-weight: 700;
    font-family: var(--f-system);
    cursor: pointer; text-decoration: none;
    transition: transform var(--dur-fast) var(--ease-spring),
                background var(--dur-fast);
    margin-left: 2px;
}
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

/* ---- Convert Modal ---- */
.modal-veil {
    position: fixed; inset: 0; z-index: 2000;
    background: rgba(0,0,0,0.4);
    backdrop-filter: saturate(180%) blur(14px);
    -webkit-backdrop-filter: saturate(180%) blur(14px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    opacity: 0; pointer-events: none;
    transition: opacity var(--dur-mid) var(--ease-ios);
}
.modal-veil:not(.hidden) { opacity: 1; pointer-events: auto; }
.sf-modal {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl);
    width: 100%; max-width: 460px;
    overflow: hidden;
    transform: translateY(16px) scale(0.97);
    transition: transform var(--dur-slow) var(--ease-spring);
}
.modal-veil:not(.hidden) .sf-modal { transform: translateY(0) scale(1); }
.modal-head {
    padding: 20px 22px 18px;
    text-align: center;
    border-bottom: 0.5px solid var(--c-separator);
    position: relative;
}
.modal-title { font-size: 17px; font-weight: 700; color: var(--t-primary); }
.modal-close {
    position: absolute; right: 14px; top: 14px;
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--c-fill); border: none; cursor: pointer;
    color: var(--t-label); font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    transition: background var(--dur-fast);
}
.modal-close:hover { background: var(--c-fill2); }
.modal-body { padding: 22px; }
.modal-foot {
    padding: 14px 22px;
    background: var(--c-surface2);
    border-top: 0.5px solid var(--c-separator);
    display: flex; gap: 10px;
}
.sf-btn {
    flex: 1; padding: 12px;
    border-radius: var(--r-md);
    font-size: 14px; font-weight: 700;
    font-family: var(--f-system); text-align: center;
    cursor: pointer; border: none;
    transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast);
    text-decoration: none; display: flex; align-items: center; justify-content: center;
}
.sf-btn:hover { filter: brightness(0.94); }
.sf-btn:active { transform: scale(0.97); }
.sf-btn.neutral { background: var(--c-fill); color: var(--t-primary); }
.sf-btn.primary { background: #16a34a; color: #fff; }

.sf-input {
    width: 100%; padding: 10px 14px;
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    font-size: 13px; font-weight: 500; font-family: var(--f-system);
    color: var(--t-primary); outline: none;
    transition: border-color var(--dur-fast), box-shadow var(--dur-fast), background var(--dur-fast);
    box-sizing: border-box;
}
.sf-input:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.14);
}
</style>

<?php
// Calculate KPI values
$totalQuotes = count($data['estimates']);
$openValue = 0;
$acceptedValue = 0;
$invoicedCount = 0;

foreach($data['estimates'] as $est) {
    if (in_array($est->status, ['Draft', 'Sent'])) {
        $openValue += floatval($est->total_amount);
    } elseif ($est->status === 'Accepted') {
        $acceptedValue += floatval($est->total_amount);
    } elseif ($est->status === 'Invoiced') {
        $invoicedCount++;
    }
}
?>

<div class="inv-wrap">

    <!-- ═══ HEADER ═══ -->
    <div class="inv-header">
        <div>
            <div class="inv-eyebrow">Sales & Commerce</div>
            <h1 class="inv-title">Estimates & Quotations</h1>
        </div>
        <div>
            <a href="<?= APP_URL ?>/estimate/create" class="btn-quick active" style="padding: 9px 18px; font-size: 14px;">
                <i class="ph-bold ph-plus"></i> Create Estimate
            </a>
        </div>
    </div>

    <!-- ═══ ALERT MESSAGING ═══ -->
    <?php if(!empty($data['error'])): ?>
    <div class="sf-alert error">
        <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Operation Error</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($data['success']) || isset($_GET['success'])): ?>
    <div class="sf-alert success">
        <i class="fa-solid fa-circle-check sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Success</div>
            <div class="sf-alert-msg"><?= htmlspecialchars(!empty($data['success']) ? $data['success'] : 'Estimate processed successfully.') ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══ KPI METRICS ROW ═══ -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e0f2fe; color:#0284c7;">
                <i class="ph-bold ph-file-text"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Quotes</span>
                <span class="kpi-value"><?= $totalQuotes ?></span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#fffbeb; color:#d97706;">
                <i class="ph-bold ph-clock"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Open / Pending</span>
                <span class="kpi-value">Rs <?= number_format($openValue, 2) ?></span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f0fdf4; color:#16a34a;">
                <i class="ph-bold ph-check-circle"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Accepted Pipeline</span>
                <span class="kpi-value">Rs <?= number_format($acceptedValue, 2) ?></span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f5f3ff; color:#7c3aed;">
                <i class="ph-bold ph-receipt"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Invoiced & Closed</span>
                <span class="kpi-value"><?= $invoicedCount ?></span>
            </div>
        </div>
    </div>

    <!-- ═══ QUICK STATUS PILLS ═══ -->
    <div class="quick-links">
        <span class="quick-links-label">Quick Views:</span>
        <button type="button" class="btn-quick status-tab active" data-status="">All Quotes</button>
        <button type="button" class="btn-quick status-tab" data-status="Draft">Draft</button>
        <button type="button" class="btn-quick status-tab" data-status="Sent">Sent</button>
        <button type="button" class="btn-quick status-tab" data-status="Accepted">Accepted</button>
        <button type="button" class="btn-quick status-tab" data-status="Invoiced">Invoiced</button>
        <button type="button" class="btn-quick status-tab" data-status="Declined">Declined</button>
    </div>

    <!-- ═══ FILTER SHELF ═══ -->
    <div class="filter-shelf">
        <div class="filter-chip">
            <span class="filter-chip-label">Customer:</span>
            <select id="filterCustomer" class="pg-size-sel" onchange="applyFilters()">
                <option value="">All Customers</option>
                <?php if(!empty($data['customers'])): foreach($data['customers'] as $cust): ?>
                    <option value="<?= htmlspecialchars($cust->name) ?>"><?= htmlspecialchars($cust->name) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="filter-chip">
            <span class="filter-chip-label">Status:</span>
            <select id="filterStatus" class="pg-size-sel" onchange="applyFilters()">
                <option value="">All Statuses</option>
                <option value="Draft">Draft</option>
                <option value="Sent">Sent</option>
                <option value="Accepted">Accepted</option>
                <option value="Invoiced">Invoiced</option>
                <option value="Declined">Declined</option>
            </select>
        </div>

        <button class="filter-reset" onclick="clearFilters()">Clear Filters</button>

        <div class="filter-count">
            Showing <strong id="visibleCount"><?= $totalQuotes ?></strong> of <strong><?= $totalQuotes ?></strong> estimates
        </div>
    </div>

    <!-- ═══ TABLE CONTAINER ═══ -->
    <div class="table-panel">
        <table class="inv-table" id="estimatesTable">
            <thead>
                <tr>
                    <th style="width: 22%;">Estimate # & Date</th>
                    <th style="width: 28%;">Customer / Client</th>
                    <th style="width: 16%;">Valid Until</th>
                    <th style="width: 14%;">Status</th>
                    <th style="width: 12%; text-align: right;">Total Amount</th>
                    <th style="width: 8%; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['estimates'])): ?>
                    <tr id="noRecordsRow">
                        <td colspan="6" style="text-align: center; color: var(--t-tertiary); padding: 50px 20px;">
                            <i class="ph-bold ph-folder-notch-open" style="font-size: 36px; opacity: 0.5; margin-bottom: 8px;"></i>
                            <div style="font-size: 14px; font-weight: 600;">No estimates found.</div>
                        </td>
                    </tr>
                <?php else: foreach($data['estimates'] as $est): ?>
                    <?php
                        $isExpired = strtotime($est->expiry_date) < strtotime(date('Y-m-d')) && !in_array($est->status, ['Invoiced', 'Declined']);
                    ?>
                    <tr class="estimate-row" 
                        data-number="<?= strtolower(htmlspecialchars($est->estimate_number)) ?>"
                        data-customer="<?= strtolower(htmlspecialchars($est->customer_name)) ?>"
                        data-status="<?= htmlspecialchars($est->status) ?>">
                        
                        <!-- Estimate # & Date -->
                        <td>
                            <div style="font-weight: 700; font-family: var(--f-mono); color: var(--c-blue); font-size: 14px;">
                                <?= htmlspecialchars($est->estimate_number) ?>
                            </div>
                            <div style="font-size: 11px; color: var(--t-tertiary); margin-top: 2px;">
                                <i class="ph ph-calendar"></i> <?= date('M d, Y', strtotime($est->estimate_date)) ?>
                            </div>
                        </td>

                        <!-- Customer -->
                        <td>
                            <div style="font-weight: 600; color: var(--t-primary);">
                                <?= htmlspecialchars($est->customer_name) ?>
                            </div>
                            <div style="font-size: 11px; color: var(--t-secondary); margin-top: 2px;">
                                Client ID #<?= $est->customer_id ?>
                            </div>
                        </td>

                        <!-- Validity Date -->
                        <td>
                            <div style="font-size: 13px; font-weight: 500; <?= $isExpired ? 'color:var(--c-red); font-weight:600;' : '' ?>">
                                <?= date('M d, Y', strtotime($est->expiry_date)) ?>
                            </div>
                            <?php if($isExpired): ?>
                                <span style="font-size: 10px; font-weight: 700; color: var(--c-red); text-transform: uppercase;">
                                    <i class="ph-bold ph-warning"></i> Expired
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Status Badge & Inline Select -->
                        <td>
                            <form action="<?= APP_URL ?>/estimate" method="POST" style="display:inline; margin:0;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="estimate_id" value="<?= $est->id ?>">
                                <select name="new_status" onchange="this.form.submit()" 
                                        class="status-badge-btn st-<?= $est->status ?>" 
                                        <?= $est->status == 'Invoiced' ? 'disabled style="cursor:default;"' : '' ?>>
                                    <option value="Draft" <?= $est->status == 'Draft' ? 'selected' : '' ?>>● Draft</option>
                                    <option value="Sent" <?= $est->status == 'Sent' ? 'selected' : '' ?>>● Sent</option>
                                    <option value="Accepted" <?= $est->status == 'Accepted' ? 'selected' : '' ?>>● Accepted</option>
                                    <option value="Declined" <?= $est->status == 'Declined' ? 'selected' : '' ?>>● Declined</option>
                                    <?php if($est->status == 'Invoiced'): ?>
                                        <option value="Invoiced" selected>● Invoiced</option>
                                    <?php endif; ?>
                                </select>
                            </form>
                        </td>

                        <!-- Total Amount -->
                        <td style="text-align: right;">
                            <div style="font-family: var(--f-mono); font-weight: 800; font-size: 14px; color: var(--t-primary);">
                                Rs. <?= number_format($est->total_amount, 2) ?>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td>
                            <div class="row-acts">
                                <a href="<?= APP_URL ?>/estimate/show/<?= $est->id ?>" class="act-btn view" title="View & Print Quotation">
                                    <i class="ph-bold ph-eye"></i>
                                </a>
                                <?php if($est->status !== 'Invoiced'): ?>
                                    <button type="button" onclick="openConvertModal(<?= $est->id ?>, '<?= htmlspecialchars($est->estimate_number) ?>')" class="act-btn convert" title="Convert to Real Invoice">
                                        <i class="ph-bold ph-arrow-right"></i> Invoice
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- /.inv-wrap -->

<!-- ═══ FLOATING COMMAND BAR (DYNAMIC ISLAND) ═══ -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="ph-bold ph-magnifying-glass"></i>
        <input type="text" id="liveSearchInput" placeholder="Search estimates, numbers, clients..." autocomplete="off">
        <span class="cmd-kbd">/</span>
    </div>

    <div class="cmd-divider"></div>

    <a href="<?= APP_URL ?>/estimate/create" class="cmd-cta">
        <i class="ph-bold ph-plus"></i>
        <span>New Quote</span>
    </a>
</div>

<!-- ═══ CONVERT TO INVOICE MODAL ═══ -->
<div class="modal-veil hidden" id="convertModalVeil">
    <div class="sf-modal">
        <div class="modal-head">
            <h3 class="modal-title" style="color: #15803d; display:flex; align-items:center; justify-content:center; gap:6px;">
                <i class="ph-bold ph-receipt"></i> Convert to Invoice & Post Ledger
            </h3>
            <button type="button" class="modal-close" onclick="closeConvertModal()">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <form action="<?= APP_URL ?>/estimate" method="POST">
            <input type="hidden" name="action" value="convert_to_invoice">
            <input type="hidden" name="estimate_id" id="modalEstId">

            <div class="modal-body">
                <p style="font-size: 13px; color: var(--t-secondary); margin-top: 0; line-height: 1.4;">
                    You are about to turn Estimate <strong id="modalEstNum" style="color:var(--t-primary); font-family:var(--f-mono);"></strong> into a live Sales Invoice. Select the accounting ledger routing details below:
                </p>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--t-label); margin-bottom:6px;">
                        Debit Account (Accounts Receivable) *
                    </label>
                    <select name="ar_account" class="sf-input" required>
                        <?php if(!empty($data['assets'])): foreach($data['assets'] as $acc): ?>
                            <option value="<?= $acc->id ?>" <?= strpos(strtolower($acc->account_name), 'receivable') !== false ? 'selected' : '' ?>>
                                <?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--t-label); margin-bottom:6px;">
                        Credit Account (Sales / Revenue) *
                    </label>
                    <select name="revenue_account" class="sf-input" required>
                        <?php if(!empty($data['revenues'])): foreach($data['revenues'] as $acc): ?>
                            <option value="<?= $acc->id ?>">
                                <?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="sf-btn neutral" onclick="closeConvertModal()">Cancel</button>
                <button type="submit" class="sf-btn primary">Generate Invoice</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ═══ CLIENT-SIDE SEARCH & FILTERING ═══
    const searchInput = document.getElementById('liveSearchInput');
    const filterCust = document.getElementById('filterCustomer');
    const filterStat = document.getElementById('filterStatus');
    const statusTabs = document.querySelectorAll('.status-tab');
    const rows = document.querySelectorAll('.estimate-row');
    const countDisplay = document.getElementById('visibleCount');

    function applyFilters() {
        const query = (searchInput.value || '').toLowerCase().trim();
        const selectedCust = (filterCust.value || '').toLowerCase().trim();
        const selectedStat = (filterStat.value || '').trim();

        let visibleCount = 0;

        rows.forEach(row => {
            const num = row.getAttribute('data-number') || '';
            const cust = row.getAttribute('data-customer') || '';
            const stat = row.getAttribute('data-status') || '';

            const matchesSearch = !query || num.includes(query) || cust.includes(query);
            const matchesCust = !selectedCust || cust.includes(selectedCust);
            const matchesStat = !selectedStat || stat === selectedStat;

            if (matchesSearch && matchesCust && matchesStat) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countDisplay) {
            countDisplay.textContent = visibleCount;
        }
    }

    function clearFilters() {
        searchInput.value = '';
        filterCust.value = '';
        filterStat.value = '';
        statusTabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-status') === ''));
        applyFilters();
    }

    // Tab clicks
    statusTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            statusTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const targetStatus = this.getAttribute('data-status');
            filterStat.value = targetStatus;
            applyFilters();
        });
    });

    searchInput.addEventListener('input', applyFilters);

    // Global shortcut for focus search: '/' or 'F2'
    document.addEventListener('keydown', function(e) {
        if ((e.key === '/' || e.key === 'F2') && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        if (e.key === 'Escape') {
            closeConvertModal();
        }
    });

    // ═══ CONVERT MODAL ═══
    function openConvertModal(id, estNum) {
        document.getElementById('modalEstId').value = id;
        document.getElementById('modalEstNum').innerText = estNum;
        document.getElementById('convertModalVeil').classList.remove('hidden');
    }

    function closeConvertModal() {
        document.getElementById('convertModalVeil').classList.add('hidden');
    }
</script>