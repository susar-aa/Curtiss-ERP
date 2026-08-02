<?php
$totalSuppliers = count($data['suppliers'] ?? []);
$totalOutstanding = 0;
$owedSuppliersCount = 0;
foreach ($data['suppliers'] ?? [] as $s) {
    $bal = floatval($s->outstanding_balance ?? 0);
    $totalOutstanding += $bal;
    if ($bal > 0) {
        $owedSuppliersCount++;
    }
}
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE - SUPPLIER CENTER
   ============================================================ */
:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.12);
    --c-fill2:        rgba(120,120,128,0.16);
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

.sup-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
}

.sup-wrap { max-width: 1420px; margin: 0 auto; padding: 16px 24px 100px; }

.stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; margin-top: 10px;}
.stat-card { background: var(--c-surface); border-radius: var(--r-xl); padding: 16px 20px; box-shadow: var(--shadow-sm); border: 0.5px solid var(--c-separator); transition: transform var(--dur-fast), box-shadow var(--dur-fast); position: relative; overflow: hidden; display: flex; align-items: center; gap: 16px; }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2.5px; border-radius: var(--r-xl) var(--r-xl) 0 0; }
.stat-card.blue::before  { background: var(--c-blue); }
.stat-card.orange::before { background: var(--c-orange); }
.stat-card.red::before   { background: var(--c-red); }
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-icon { width: 46px; height: 46px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.stat-card.blue  .stat-icon { background: var(--c-blue-light); color: var(--c-blue); }
.stat-card.orange .stat-icon { background: var(--c-orange-light); color: var(--c-orange); }
.stat-card.red   .stat-icon { background: var(--c-red-light); color: var(--c-red); }
.stat-num { font-size: 22px; font-weight: 700; color: var(--t-primary); line-height: 1.1; margin-bottom: 2px; }
.stat-lbl { font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--t-label); }

.filter-shelf { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 20px; }
.filter-chip { display: inline-flex; align-items: center; gap: 6px; background: var(--c-surface); border: 0.5px solid var(--c-separator); border-radius: var(--r-pill); padding: 7px 14px; font-size: 13px; font-weight: 500; color: var(--t-secondary); box-shadow: var(--shadow-xs); transition: border-color var(--dur-fast); }
.filter-chip:focus-within { border-color: var(--c-blue); box-shadow: 0 0 0 3px rgba(0,122,255,0.12); }
.filter-chip-label { font-size: 11px; font-weight: 700; color: var(--t-label); text-transform: uppercase; }
.filter-reset { background: transparent; border: 0.5px solid var(--c-separator); border-radius: var(--r-pill); padding: 7px 14px; font-size: 13px; font-weight: 600; color: var(--t-secondary); cursor: pointer; transition: background var(--dur-fast); }
.filter-reset:hover { background: var(--c-fill); color: var(--t-primary); }
.filter-count { margin-left: auto; font-size: 13px; color: var(--t-secondary); font-weight: 500; }
.filter-count strong { color: var(--t-primary); font-weight: 700; }

.sf-dropdown { position: relative; outline: none; cursor: pointer; }
.sf-dropdown-val { display: flex; align-items: center; gap: 5px; font-size: 13.5px; font-weight: 600; color: var(--t-primary); }
.sf-dropdown-val::after { content: ''; display: inline-block; width: 12px; height: 12px; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238e8e93' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") center/contain no-repeat; }
.sf-dropdown-menu { position: absolute; top: calc(100% + 10px); left: 0; z-index: 200; background: var(--c-surface); border-radius: var(--r-md); border: 0.5px solid var(--c-separator); box-shadow: var(--shadow-xl); min-width: 200px; max-height: 280px; overflow-y: auto; opacity: 0; visibility: hidden; transform: translateY(-6px) scale(0.98); transition: opacity var(--dur-mid), transform var(--dur-mid), visibility var(--dur-mid); padding: 6px; }
.sf-dropdown:focus-within .sf-dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
.sf-dropdown-item { padding: 9px 12px; font-size: 13.5px; font-weight: 500; color: var(--t-primary); border-radius: var(--r-sm); transition: background var(--dur-fast); cursor: pointer; }
.sf-dropdown-item:hover { background: var(--c-fill); }
.sf-dropdown-item.active { background: var(--c-blue-light); color: var(--c-blue); font-weight: 600; }

.table-panel { background: var(--c-surface); border-radius: var(--r-xl); border: 0.5px solid var(--c-separator); box-shadow: var(--shadow-sm); overflow: hidden; }
.sup-table { width: 100%; border-collapse: collapse; }
.sup-table thead th { padding: 13px 18px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--t-label); background: var(--c-surface2); border-bottom: 0.5px solid var(--c-separator); text-align: left; }
.sup-table tbody tr { transition: background var(--dur-fast); border-bottom: 0.5px solid var(--c-separator2); cursor: pointer; }
.sup-table tbody tr:hover { background: var(--c-fill2); }
.sup-table td { padding: 14px 18px; font-size: 14px; color: var(--t-primary); vertical-align: middle; }
.txt-right { text-align: right !important; }
.txt-center { text-align: center !important; }

.sf-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: var(--r-xs); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; }
.badge-active { background: var(--c-green-light); color: var(--c-green); }
.badge-owed   { background: var(--c-red-light); color: var(--c-red); }
.badge-new    { background: var(--c-orange-light); color: var(--c-orange); }
.badge-Pending { background: var(--c-orange-light); color: var(--c-orange); }
.badge-Sent { background: var(--c-blue-light); color: var(--c-blue); }
.badge-Received { background: var(--c-green-light); color: var(--c-green); }
.badge-Voided { background: var(--c-red-light); color: var(--c-red); }
.sf-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.avatar-circle { width: 36px; height: 36px; background: var(--c-fill); color: var(--t-secondary); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }

.sf-alert { display: flex; align-items: flex-start; gap: 12px; background: var(--c-surface); border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 20px; box-shadow: var(--shadow-xs); border: 0.5px solid var(--c-separator); border-left-width: 3.5px; font-size: 14px; }
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 700; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }
.sf-alert-close { margin-left: auto; flex-shrink: 0; background: none; border: none; color: var(--t-tertiary); cursor: pointer; font-size: 15px; padding: 2px; }

.sf-btn { padding: 8px 14px; border-radius: var(--r-md); font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: 0.5px solid transparent; cursor: pointer; transition: transform var(--dur-fast); text-decoration: none; font-family: var(--f-system); }
.sf-btn:active { transform: scale(0.97); }
.sf-btn.primary { background: var(--c-blue); color: #fff; }
.sf-btn.danger { background: var(--c-red); color: #fff; }
.sf-btn.success { background: var(--c-green); color: #fff; }
.sf-btn.neutral { background: var(--c-surface); border-color: var(--c-separator); color: var(--t-primary); box-shadow: var(--shadow-xs); }
.sf-btn.neutral:hover { background: var(--c-surface2); }
.act-btn { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; background: transparent; cursor: pointer; font-size: 13.5px; transition: background var(--dur-fast); }
.act-btn.view  { color: var(--c-blue); }
.act-btn.view:hover { background: var(--c-blue-light); }
.act-btn.edit  { color: var(--t-secondary); }
.act-btn.edit:hover { background: var(--c-fill); }

.cmd-bar { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); background: rgba(28, 28, 30, 0.92); backdrop-filter: saturate(180%) blur(28px); border: 0.5px solid rgba(255,255,255,0.12); border-radius: var(--r-pill); padding: 7px 10px; display: flex; align-items: center; gap: 4px; box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3); z-index: 100; }
.cmd-search { display: flex; align-items: center; gap: 9px; background: rgba(255,255,255,0.1); border-radius: var(--r-pill); padding: 8px 14px; width: 196px; transition: width var(--dur-slow) var(--ease-ios); }
.cmd-search:focus-within { width: 300px; background: rgba(255,255,255,0.18); }
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; flex-shrink: 0; }
.cmd-search input { background: transparent; border: none; outline: none; color: #fff; font-size: 14px; font-weight: 500; font-family: var(--f-system); width: 100%; }
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-cta { display: flex; align-items: center; gap: 7px; background: #fff; color: #1c1c1e; border: none; border-radius: var(--r-pill); padding: 0 18px; height: 38px; font-size: 14px; font-weight: 700; font-family: var(--f-system); cursor: pointer; transition: transform var(--dur-fast); margin-left: 2px; }
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

.sf-pagination { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: var(--c-surface2); border-top: 0.5px solid var(--c-separator); }
.pg-info { font-size: 13px; color: var(--t-secondary); }
.pg-right { display: flex; align-items: center; gap: 20px; }
.pg-size-wrap { display: flex; align-items: center; gap: 8px; }
.pg-size-lbl { font-size: 12px; font-weight: 600; color: var(--t-label); text-transform: uppercase; }
.pg-size-sel { font-family: var(--f-system); font-size: 13px; font-weight: 600; color: var(--t-primary); background: var(--c-fill); border: 0.5px solid var(--c-separator); border-radius: var(--r-sm); padding: 5px 9px; outline: none; cursor: pointer; }
.pg-nav { display: flex; border: 0.5px solid var(--c-separator); border-radius: var(--r-sm); overflow: hidden; }
.pg-btn { width: 34px; height: 32px; display: flex; align-items: center; justify-content: center; background: var(--c-surface); border: none; cursor: pointer; color: var(--t-primary); font-size: 12px; transition: background var(--dur-fast); }
.pg-btn:hover:not(:disabled) { background: var(--c-fill); }
.pg-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.pg-btn + .pg-btn { border-left: 0.5px solid var(--c-separator); }
.pg-current { padding: 0 14px; display: flex; align-items: center; font-size: 13px; font-weight: 600; color: var(--t-primary); background: var(--c-surface); border-left: 0.5px solid var(--c-separator); border-right: 0.5px solid var(--c-separator); }

.modal-veil { position: fixed; inset: 0; z-index: 2000; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; transition: opacity var(--dur-mid); }
.modal-veil.hidden { display: none; }
.sf-modal { background: var(--c-surface); border: 0.5px solid var(--c-separator); border-radius: var(--r-xl); box-shadow: var(--shadow-xl); width: 520px; max-width: 95vw; animation: sfModalSlide var(--dur-mid) var(--ease-spring); overflow: hidden; }
@keyframes sfModalSlide { from { transform: translateY(20px) scale(0.97); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 0.5px solid var(--c-separator); }
.modal-title { font-size: 16px; font-weight: 700; margin: 0; }
.modal-close { background: var(--c-fill); border: none; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--t-label); cursor: pointer; }
.modal-close:hover { background: var(--c-fill2); color: var(--t-secondary); }
.modal-body { padding: 24px; }
.modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; background: var(--c-surface2); border-top: 0.5px solid var(--c-separator); }

.sf-group { margin-bottom: 16px; }
.sf-group label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: var(--t-secondary); text-transform: uppercase; }
.sf-input { width: 100%; padding: 10px 14px; border-radius: var(--r-sm); border: 0.5px solid var(--c-separator); background: var(--c-surface2); color: var(--t-primary); font-size: 14px; outline: none; box-sizing: border-box; }
.sf-input:focus { border-color: var(--c-blue); background: var(--c-surface); }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.tabs { display: flex; border-bottom: 0.5px solid var(--c-separator); background: var(--c-surface2); padding: 0 24px; }
.tab-btn { padding: 12px 18px; border: none; background: transparent; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--t-secondary); border-bottom: 2.5px solid transparent; transition: 0.18s; }
.tab-btn:hover { color: var(--c-blue); }
.tab-btn.active { color: var(--c-blue); border-bottom-color: var(--c-blue); }
.tab-content { display: none; }
.tab-content.active { display: block; }
.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
.data-table th, .data-table td { padding: 10px 12px; text-align: left; border-bottom: 0.5px solid var(--c-separator2); font-size: 13px; }
.data-table th { color: var(--t-label); font-weight: 600; font-size: 10.5px; text-transform: uppercase; background: var(--c-surface2); }
.num-col { text-align: right !important; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.7s linear infinite; display: inline-block; }
.hidden { display: none !important; }
</style>

<div class="sup-root">
    <div class="sup-wrap">

        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-truck-field"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($totalSuppliers) ?></div>
                    <div class="stat-lbl">Total Suppliers</div>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-num">Rs. <?= number_format($totalOutstanding, 2) ?></div>
                    <div class="stat-lbl">Total Payable Outstanding</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($owedSuppliersCount) ?></div>
                    <div class="stat-lbl">Accounts to Settle</div>
                </div>
            </div>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="sf-alert error" id="error-alert">
                <i class="fa-solid fa-circle-exclamation sf-alert-icon"></i>
                <div>
                    <div class="sf-alert-title">Error</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
                </div>
                <button type="button" class="sf-alert-close" onclick="document.getElementById('error-alert').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <div class="sf-alert success" id="success-alert">
                <i class="fa-solid fa-circle-check sf-alert-icon"></i>
                <div>
                    <div class="sf-alert-title">Success</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div>
                </div>
                <button type="button" class="sf-alert-close" onclick="document.getElementById('success-alert').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- Filters Block -->
        <div class="filter-shelf">
            <!-- Payment Status -->
            <div class="filter-chip">
                <span class="filter-chip-label">Ledger</span>
                <div class="sf-dropdown" tabindex="0">
                    <div class="sf-dropdown-val" id="status-dropdown-val">All Accounts</div>
                    <div class="sf-dropdown-menu">
                        <div class="sf-dropdown-item active" data-val="" onclick="selectStatus('', 'All Accounts')">All Accounts</div>
                        <div class="sf-dropdown-item" data-val="owed" onclick="selectStatus('owed', 'Has Unpaid Balance')">Has Unpaid Balance</div>
                        <div class="sf-dropdown-item" data-val="cleared" onclick="selectStatus('cleared', 'Zero Balance')">Zero Balance</div>
                    </div>
                    <input type="hidden" id="filterStatus" value="">
                </div>
            </div>
            <!-- Reset Button -->
            <button type="button" onclick="clearAllFilters()" class="filter-reset">Reset</button>
            <!-- Counter -->
            <div class="filter-count">
                <strong id="matching-count"><?= count($data['suppliers']) ?></strong> suppliers
            </div>
        </div>

        <!-- Table View -->
        <div class="table-panel">
            <table class="sup-table">
                <thead>
                    <tr>
                        <th>Supplier / Company</th>
                        <th>Contact Details</th>
                        <th class="txt-right">Outstanding Balance</th>
                        <th>Status</th>
                        <th style="width:120px;" class="txt-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="supList">
                    <?php if (empty($data['suppliers'])): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--t-secondary);">
                                <i class="fa-solid fa-building" style="font-size:28px; margin-bottom:8px; color:var(--t-tertiary);"></i><br>
                                No suppliers registered yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data['suppliers'] as $s): ?>
                            <?php 
                            $bal = floatval($s->outstanding_balance); 
                            $badgeCls = $bal > 0 ? 'badge-owed' : 'badge-active';
                            $badgeTxt = $bal > 0 ? 'Owed' : 'Cleared';
                            ?>
                            <tr class="supplier-row" 
                                onclick="showSupplierProfile(<?= $s->id ?>)"
                                data-id="<?= $s->id ?>"
                                data-name="<?= htmlspecialchars(strtolower($s->name ?? '')) ?>"
                                data-phone="<?= htmlspecialchars(strtolower($s->phone ?? '')) ?>"
                                data-email="<?= htmlspecialchars(strtolower($s->email ?? '')) ?>"
                                data-outstanding="<?= $bal ?>">
                                
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div class="avatar-circle"><?= strtoupper(substr($s->name ?? '', 0, 2)) ?></div>
                                        <div>
                                            <strong style="font-size:14.5px; font-weight:600; color:var(--t-primary);"><?= htmlspecialchars($s->name ?? '') ?></strong>
                                            <span style="font-size:11px; color:var(--t-secondary); display:block; margin-top:2px;">
                                                üè† <?= !empty($s->address) ? htmlspecialchars($s->address) : '<span style="color:var(--c-red); font-weight:600;">Missing Address</span>' ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size:13px; font-weight:500; display:block;">
                                        üìû <?= !empty($s->phone) ? htmlspecialchars($s->phone) : '<span style="color:var(--c-red); font-weight:600;">Missing Phone</span>' ?>
                                    </span>
                                    <?php if (!empty($s->email)): ?>
                                        <span style="font-size:11px; color:var(--t-secondary); display:block; margin-top:2px;">
                                            <?= htmlspecialchars($s->email) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="txt-right" style="font-weight:700; font-family:var(--f-mono); font-size:14px; color: <?= $bal > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>;">
                                    Rs: <?= number_format($bal, 2) ?>
                                </td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-start;">
                                        <span class="sf-badge <?= $badgeCls ?>">
                                            <span class="dot"></span><?= $badgeTxt ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex; justify-content:center; gap:6px;" onclick="event.stopPropagation()">
                                        <button type="button" class="act-btn view" onclick="showSupplierProfile(<?= $s->id ?>)" title="View ledger & profile">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination Block -->
            <div class="sf-pagination">
                <div class="pg-info" id="pg-info-text">
                    Showing <strong>1</strong> ‚Äì <strong>15</strong> of <strong><?= count($data['suppliers']) ?></strong>
                </div>
                <div class="pg-right">
                    <div class="pg-size-wrap">
                        <span class="pg-size-lbl">Per page</span>
                        <select class="pg-size-sel native-select" onchange="updatePageSize(this.value)">
                            <option value="15" selected>15</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="1000">All</option>
                        </select>
                    </div>
                    <div class="pg-nav" id="pg-nav-container">
                        <button type="button" class="pg-btn" id="pg-prev-btn" onclick="navigatePage(currentPage - 1)">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="pg-current" id="pg-current-text">1 / 1</div>
                        <button type="button" class="pg-btn" id="pg-next-btn" onclick="navigatePage(currentPage + 1)">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
< ! - -   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  
           F L O A T I N G   C O M M A N D   /   S E A R C H   B A R   ( D Y N A M I C   I S L A N D )  
           = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =   - - >  
 < d i v   c l a s s = " c m d - b a r " >  
         < d i v   c l a s s = " c m d - s e a r c h " >  
                 < i   c l a s s = " f a - s o l i d   f a - m a g n i f y i n g - g l a s s " > < / i >  
                 < i n p u t   t y p e = " t e x t "   i d = " s e a r c h I n p u t "   p l a c e h o l d e r = " S e a r c h   s u p p l i e r s . . . "   o n k e y u p = " f i l t e r L i s t ( ) " >  
         < / d i v >  
         < d i v   c l a s s = " c m d - d i v i d e r " > < / d i v >  
         < b u t t o n   t y p e = " b u t t o n "   c l a s s = " c m d - c t a "   o n c l i c k = " o p e n M o d a l ( ' a d d S u p p l i e r M o d a l ' ) " > < i   c l a s s = " f a - s o l i d   f a - p l u s "   s t y l e = " f o n t - s i z e : 1 3 p x ; " > < / i >   N e w < / b u t t o n >  
 < / d i v >  
  
 < ! - -   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  
           P O P U P   M O D A L :   S U P P L I E R   P R O F I L E   L E D G E R   &   D E T A I L S  
           = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =   - - >  
 < d i v   i d = " s u p p l i e r P r o f i l e M o d a l "   c l a s s = " m o d a l - v e i l   h i d d e n "   o n c l i c k = " i f ( e v e n t . t a r g e t   = = =   t h i s )   c l o s e S u p p l i e r P r o f i l e ( ) " >  
         < d i v   c l a s s = " s f - m o d a l "   s t y l e = " w i d t h :   8 5 % ;   m a x - w i d t h :   1 0 0 0 p x ;   h e i g h t :   8 5 v h ;   d i s p l a y :   f l e x ;   f l e x - d i r e c t i o n :   c o l u m n ;   p a d d i n g :   0 ;   o v e r f l o w :   h i d d e n ;   b o r d e r - r a d i u s :   v a r ( - - r - l g ) ; " >  
                  
                 < d i v   c l a s s = " m o d a l - h e a d "   i d = " m o d a l - h e a d e r - c o n t a i n e r "   s t y l e = " p a d d i n g :   1 8 p x   2 4 p x ;   b o r d e r - b o t t o m :   0 . 5 p x   s o l i d   v a r ( - - c - s e p a r a t o r ) ;   b a c k g r o u n d :   v a r ( - - c - s u r f a c e 2 ) ; " >  
                         < d i v   s t y l e = " d i s p l a y :   f l e x ;   g a p :   1 5 p x ;   a l i g n - i t e m s :   c e n t e r ; " >  
                                 < d i v   c l a s s = " a v a t a r - c i r c l e "   s t y l e = " w i d t h :   4 0 p x ;   h e i g h t :   4 0 p x ;   f o n t - s i z e :   1 6 p x ; " > S < / d i v >  
                                 < d i v >  
                                         < h 3   c l a s s = " m o d a l - t i t l e "   s t y l e = " f o n t - s i z e :   1 8 p x ;   f o n t - w e i g h t :   7 0 0 ; " > S u p p l i e r   D e t a i l s < / h 3 >  
                                 < / d i v >  
                         < / d i v >  
                         < b u t t o n   t y p e = " b u t t o n "   o n c l i c k = " c l o s e S u p p l i e r P r o f i l e ( ) "   c l a s s = " m o d a l - c l o s e "   s t y l e = " w i d t h : 3 0 p x ;   h e i g h t : 3 0 p x ; " > < i   c l a s s = " f a - s o l i d   f a - x m a r k " > < / i > < / b u t t o n >  
                 < / d i v >  
                  
                 < d i v   i d = " m o d a l - l o a d e r "   s t y l e = " d i s p l a y : n o n e ;   f l e x : 1 ;   a l i g n - i t e m s : c e n t e r ;   j u s t i f y - c o n t e n t : c e n t e r ;   f l e x - d i r e c t i o n : c o l u m n ;   g a p : 1 2 p x ;   b a c k g r o u n d : v a r ( - - c - s u r f a c e ) ; " >  
                         < i   c l a s s = " f a - s o l i d   f a - s p i n n e r   s p i n "   s t y l e = " f o n t - s i z e : 3 2 p x ;   c o l o r : v a r ( - - c - b l u e ) ; " > < / i >  
                         < s p a n   s t y l e = " f o n t - s i z e : 1 4 p x ;   c o l o r : v a r ( - - t - s e c o n d a r y ) ;   f o n t - w e i g h t : 5 0 0 ; " > L o a d i n g   s u p p l i e r   p r o f i l e . . . < / s p a n >  
                 < / d i v >  
                  
                 < d i v   i d = " m o d a l - p r o f i l e - c o n t e n t "   s t y l e = " f l e x : 1 ;   d i s p l a y : f l e x ;   f l e x - d i r e c t i o n : c o l u m n ;   o v e r f l o w : h i d d e n ; " >  
                         < ! - -   C o n t e n t   w i l l   l o a d   d y n a m i c a l l y   h e r e   - - >  
                 < / d i v >  
         < / d i v >  
 < / d i v >  
  
 < ! - -   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  
           H I D D E N   T E M P L A T E   S O U R C E S   ( E X T R A C T E D   V I A   D O M   P A R S E R )  
           = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =   - - >  
 < ? p h p   i f   ( $ d a t a [ ' s e l e c t e d _ s u p p l i e r ' ] ) :   ? >  
         < ? p h p   $ s u p   =   $ d a t a [ ' s e l e c t e d _ s u p p l i e r ' ] ;   $ s t a t s   =   $ d a t a [ ' s t a t s ' ] ;   ? >  
          
         < d i v   i d = " m o d a l - h e a d e r - s o u r c e "   c l a s s = " h i d d e n " >  
                 < d i v   s t y l e = " d i s p l a y :   f l e x ;   g a p :   1 5 p x ;   a l i g n - i t e m s :   c e n t e r ;   m i n - w i d t h :   0 ;   f l e x :   1 ; " >  
                         < d i v   c l a s s = " a v a t a r - c i r c l e "   s t y l e = " w i d t h :   4 0 p x ;   h e i g h t :   4 0 p x ;   f o n t - s i z e :   1 6 p x ;   b a c k g r o u n d :   v a r ( - - c - b l u e - l i g h t ) ;   c o l o r :   v a r ( - - c - b l u e ) ;   f l e x - s h r i n k :   0 ; " >  
                                 < ? =   s t r t o u p p e r ( s u b s t r ( $ s u p - > n a m e   ? ?   ' ' ,   0 ,   2 ) )   ? >  
                         < / d i v >  
                         < d i v   s t y l e = " m i n - w i d t h :   0 ;   m a x - w i d t h :   2 5 0 p x ; " >  
                                 < h 3   c l a s s = " m o d a l - t i t l e "   s t y l e = " f o n t - s i z e :   1 6 . 5 p x ;   f o n t - w e i g h t :   7 0 0 ;   w h i t e - s p a c e :   n o w r a p ;   o v e r f l o w :   h i d d e n ;   t e x t - o v e r f l o w :   e l l i p s i s ;   m a r g i n :   0 ; "   t i t l e = " < ? =   h t m l s p e c i a l c h a r s ( $ s u p - > n a m e   ? ?   ' ' )   ? > " >  
                                         < ? =   h t m l s p e c i a l c h a r s ( $ s u p - > n a m e   ? ?   ' ' )   ? >  
                                 < / h 3 >  
                                 < d i v   s t y l e = " f o n t - s i z e :   1 1 p x ;   c o l o r :   v a r ( - - t - s e c o n d a r y ) ;   d i s p l a y :   f l e x ;   g a p :   1 0 p x ;   m a r g i n - t o p :   2 p x ;   w h i t e - s p a c e :   n o w r a p ;   o v e r f l o w :   h i d d e n ;   a l i g n - i t e m s :   c e n t e r ; " >  
                                         < s p a n >  x ~  < ? =   ! e m p t y ( $ s u p - > p h o n e )   ?   h t m l s p e c i a l c h a r s ( $ s u p - > p h o n e )   :   ' < s p a n   s t y l e = " c o l o r : v a r ( - - c - r e d ) ;   f o n t - w e i g h t : 6 0 0 ; " > M i s s i n g < / s p a n > '   ? > < / s p a n >  
                                         < s p a n > ‚ S0 Ô ∏ è   < ? =   ! e m p t y ( $ s u p - > e m a i l )   ?   h t m l s p e c i a l c h a r s ( $ s u p - > e m a i l )   :   ' < s p a n   s t y l e = " c o l o r : v a r ( - - c - r e d ) ;   f o n t - w e i g h t : 6 0 0 ; " > M i s s i n g < / s p a n > '   ? > < / s p a n >  
                                 < / d i v >  
                         < / d i v >  
                 < / d i v >  
                  
                 < ! - -   H e a d e r   S t a t i s t i c s   C a r d s   - - >  
                 < d i v   s t y l e = " d i s p l a y :   f l e x ;   g a p :   8 p x ;   m a r g i n - r i g h t :   1 8 p x ;   f l e x - s h r i n k :   0 ;   a l i g n - i t e m s :   c e n t e r ; " >  
                         < d i v   s t y l e = " b a c k g r o u n d :   v a r ( - - c - f i l l ) ;   p a d d i n g :   5 p x   1 0 p x ;   b o r d e r - r a d i u s :   v a r ( - - r - s m ) ;   t e x t - a l i g n :   c e n t e r ;   m i n - w i d t h :   1 0 0 p x ; " >  
                                 < d i v   s t y l e = " f o n t - s i z e :   8 . 5 p x ;   c o l o r :   v a r ( - - t - l a b e l ) ;   t e x t - t r a n s f o r m :   u p p e r c a s e ;   f o n t - w e i g h t :   7 0 0 ;   l e t t e r - s p a c i n g :   0 . 0 4 e m ; " > T o t a l   B i l l e d < / d i v >  
                                 < d i v   s t y l e = " f o n t - s i z e :   1 3 p x ;   f o n t - w e i g h t :   b o l d ;   c o l o r :   v a r ( - - t - p r i m a r y ) ;   m a r g i n - t o p :   1 p x ;   f o n t - f a m i l y :   v a r ( - - f - m o n o ) ; " > R s :   < ? =   n u m b e r _ f o r m a t ( $ s t a t s - > t o t a l _ b i l l e d ,   2 )   ? > < / d i v >  
                         < / d i v >  
                         < d i v   s t y l e = " b a c k g r o u n d :   v a r ( - - c - g r e e n - l i g h t ) ;   p a d d i n g :   5 p x   1 0 p x ;   b o r d e r - r a d i u s :   v a r ( - - r - s m ) ;   b o r d e r :   0 . 5 p x   s o l i d   r g b a ( 5 2 , 1 9 9 , 8 9 , 0 . 2 ) ;   t e x t - a l i g n :   c e n t e r ;   m i n - w i d t h :   1 0 0 p x ; " >  
                                 < d i v   s t y l e = " f o n t - s i z e :   8 . 5 p x ;   c o l o r :   v a r ( - - c - g r e e n ) ;   t e x t - t r a n s f o r m :   u p p e r c a s e ;   f o n t - w e i g h t :   7 0 0 ;   l e t t e r - s p a c i n g :   0 . 0 4 e m ; " > T o t a l   P a i d < / d i v >  
                                 < d i v   s t y l e = " f o n t - s i z e :   1 3 p x ;   f o n t - w e i g h t :   b o l d ;   c o l o r :   v a r ( - - c - g r e e n ) ;   m a r g i n - t o p :   1 p x ;   f o n t - f a m i l y :   v a r ( - - f - m o n o ) ; " > R s :   < ? =   n u m b e r _ f o r m a t ( $ s t a t s - > t o t a l _ p a i d ,   2 )   ? > < / d i v >  
                         < / d i v >  
                         < d i v   s t y l e = " b a c k g r o u n d :   < ? =   $ s t a t s - > o u t s t a n d i n g   >   0   ?   ' v a r ( - - c - r e d - l i g h t ) '   :   ' v a r ( - - c - g r e e n - l i g h t ) '   ? > ;   p a d d i n g :   5 p x   1 0 p x ;   b o r d e r - r a d i u s :   v a r ( - - r - s m ) ;   b o r d e r :   0 . 5 p x   s o l i d   < ? =   $ s t a t s - > o u t s t a n d i n g   >   0   ?   ' r g b a ( 2 5 5 , 5 9 , 4 8 , 0 . 2 ) '   :   ' r g b a ( 5 2 , 1 9 9 , 8 9 , 0 . 2 ) '   ? > ;   t e x t - a l i g n :   c e n t e r ;   m i n - w i d t h :   1 0 0 p x ; " >  
                                 < d i v   s t y l e = " f o n t - s i z e :   8 . 5 p x ;   c o l o r :   < ? =   $ s t a t s - > o u t s t a n d i n g   >   0   ?   ' v a r ( - - c - r e d ) '   :   ' v a r ( - - c - g r e e n ) '   ? > ;   t e x t - t r a n s f o r m :   u p p e r c a s e ;   f o n t - w e i g h t :   7 0 0 ;   l e t t e r - s p a c i n g :   0 . 0 4 e m ; " > O u t s t a n d i n g < / d i v >  
                                 < d i v   s t y l e = " f o n t - s i z e :   1 3 p x ;   f o n t - w e i g h t :   b o l d ;   c o l o r :   < ? =   $ s t a t s - > o u t s t a n d i n g   >   0   ?   ' v a r ( - - c - r e d ) '   :   ' v a r ( - - c - g r e e n ) '   ? > ;   m a r g i n - t o p :   1 p x ;   f o n t - f a m i l y :   v a r ( - - f - m o n o ) ; " > R s :   < ? =   n u m b e r _ f o r m a t ( $ s t a t s - > o u t s t a n d i n g ,   2 )   ? > < / d i v >  
                         < / d i v >  
                 < / d i v >  
  
                 < b u t t o n   t y p e = " b u t t o n "   o n c l i c k = " c l o s e S u p p l i e r P r o f i l e ( ) "   c l a s s = " m o d a l - c l o s e "   s t y l e = " w i d t h : 3 0 p x ;   h e i g h t : 3 0 p x ;   f l e x - s h r i n k :   0 ; " > < i   c l a s s = " f a - s o l i d   f a - x m a r k " > < / i > < / b u t t o n >  
         < / d i v >  
  
         < d i v   i d = " m o d a l - p r o f i l e - c o n t e n t - s o u r c e "   c l a s s = " h i d d e n " >  
                 < ! - -   T a b   N a v i g a t i o n   - - >  
                 < d i v   c l a s s = " t a b s " >  
                         < b u t t o n   c l a s s = " t a b - b t n   a c t i v e "   o n c l i c k = " s w i t c h M o d a l T a b ( ' l e d g e r ' ) "   i d = " m b t n _ l e d g e r " > A c t i v i t y   L e d g e r < / b u t t o n >  
                         < b u t t o n   c l a s s = " t a b - b t n "   o n c l i c k = " s w i t c h M o d a l T a b ( ' p o s ' ) "   i d = " m b t n _ p o s " > P u r c h a s e   O r d e r s < / b u t t o n >  
                         < b u t t o n   c l a s s = " t a b - b t n "   o n c l i c k = " s w i t c h M o d a l T a b ( ' p r o d u c t s ' ) "   i d = " m b t n _ p r o d u c t s " > P r o d u c t s   L i s t < / b u t t o n >  
                         < b u t t o n   c l a s s = " t a b - b t n "   o n c l i c k = " s w i t c h M o d a l T a b ( ' p r o f i l e ' ) "   i d = " m b t n _ p r o f i l e " > P r o f i l e < / b u t t o n >  
                 < / d i v >  
  
                 < ! - -   T A B   1 :   L e d g e r   - - >  
                 < d i v   i d = " m t a b _ l e d g e r "   c l a s s = " t a b - c o n t e n t   a c t i v e "   s t y l e = " p a d d i n g :   2 2 p x ;   o v e r f l o w - y :   a u t o ;   f l e x :   1 ; " >  
                         < t a b l e   c l a s s = " d a t a - t a b l e " >  
                                 < t h e a d >  
                                         < t r >  
                                                 < t h > D a t e < / t h >  
                                                 < t h > T y p e < / t h >  
                                                 < t h > R e f   /   D e s c < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > D r   ( P a i d / R e t ) < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > C r   ( B i l l e d ) < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > B a l a n c e < / t h >  
                                         < / t r >  
                                 < / t h e a d >  
                                 < t b o d y >  
                                         < ? p h p   i f ( e m p t y ( $ d a t a [ ' l e d g e r ' ] ) ) :   ? >  
                                                 < t r > < t d   c o l s p a n = " 6 "   s t y l e = " t e x t - a l i g n :   c e n t e r ;   c o l o r :   v a r ( - - t - s e c o n d a r y ) ;   p a d d i n g :   2 0 p x ; " > N o   f i n a n c i a l   a c t i v i t y   r e c o r d e d . < / t d > < / t r >  
                                         < ? p h p   e l s e :   f o r e a c h ( $ d a t a [ ' l e d g e r ' ]   a s   $ l ) :   ? >  
                                                 < t r >  
                                                         < t d   s t y l e = " c o l o r : v a r ( - - t - s e c o n d a r y ) ;   f o n t - s i z e : 1 2 p x ; " > < ? =   d a t e ( ' M   d ,   Y ' ,   s t r t o t i m e ( $ l - > d a t e ) )   ? > < / t d >  
                                                         < t d > < s t r o n g > < ? =   $ l - > t y p e   ? > < / s t r o n g > < / t d >  
                                                         < t d >  
                                                                 < ? p h p   i f ( $ l - > t y p e   = =   ' G R N ' ) :   ? >  
                                                                         < a   h r e f = " < ? =   A P P _ U R L   ? > / g r n / s h o w / < ? =   $ l - > i d   ? > "   t a r g e t = " _ b l a n k "   s t y l e = " c o l o r : v a r ( - - c - b l u e ) ;   f o n t - w e i g h t : b o l d ;   t e x t - d e c o r a t i o n : n o n e ; " >  
                                                                                 < ? =   h t m l s p e c i a l c h a r s ( $ l - > r e f )   ? >   ‚     
                                                                         < / a >  
                                                                 < ? p h p   e l s e i f ( $ l - > t y p e   = =   ' S u p p l i e r   R e t u r n ' ) :   ? >  
                                                                         < a   h r e f = " < ? =   A P P _ U R L   ? > / s u p p l i e r - r e t u r n / s h o w / < ? =   $ l - > i d   ? > "   t a r g e t = " _ b l a n k "   s t y l e = " c o l o r : v a r ( - - c - r e d ) ;   f o n t - w e i g h t : b o l d ;   t e x t - d e c o r a t i o n : n o n e ; " >  
                                                                                 < ? =   h t m l s p e c i a l c h a r s ( $ l - > r e f )   ? >   ‚     
                                                                         < / a >  
                                                                 < ? p h p   e l s e :   ? >  
                                                                         < s p a n   s t y l e = " c o l o r : v a r ( - - t - p r i m a r y ) ; " > < ? =   h t m l s p e c i a l c h a r s ( $ l - > r e f )   ? > < / s p a n >  
                                                                 < ? p h p   e n d i f ;   ? >  
                                                         < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " c o l o r : v a r ( - - c - g r e e n ) ;   f o n t - w e i g h t : 5 0 0 ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ; " > < ? =   $ l - > d e b i t   >   0   ?   ' R s :   '   .   n u m b e r _ f o r m a t ( $ l - > d e b i t ,   2 )   :   ' - '   ? > < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " f o n t - w e i g h t : 5 0 0 ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ; " > < ? =   $ l - > c r e d i t   >   0   ?   ' R s :   '   .   n u m b e r _ f o r m a t ( $ l - > c r e d i t ,   2 )   :   ' - '   ? > < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " f o n t - w e i g h t : b o l d ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ;   c o l o r :   < ? =   $ l - > b a l a n c e   >   0   ?   ' v a r ( - - c - r e d ) '   :   ' v a r ( - - c - g r e e n ) '   ? > ; " > R s :   < ? =   n u m b e r _ f o r m a t ( $ l - > b a l a n c e ,   2 )   ? > < / t d >  
                                                 < / t r >  
                                         < ? p h p   e n d f o r e a c h ;   e n d i f ;   ? >  
                                 < / t b o d y >  
                         < / t a b l e >  
                 < / d i v >  
  
                 < ! - -   T A B   2 :   P u r c h a s e   O r d e r s   - - >  
                 < d i v   i d = " m t a b _ p o s "   c l a s s = " t a b - c o n t e n t "   s t y l e = " p a d d i n g :   2 2 p x ;   o v e r f l o w - y :   a u t o ;   f l e x :   1 ; " >  
                         < t a b l e   c l a s s = " d a t a - t a b l e " >  
                                 < t h e a d >  
                                         < t r >  
                                                 < t h > D a t e < / t h >  
                                                 < t h > E x p e c t e d < / t h >  
                                                 < t h > P O   N u m b e r < / t h >  
                                                 < t h > S t a t u s < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > T o t a l < / t h >  
                                                 < t h > < / t h >  
                                         < / t r >  
                                 < / t h e a d >  
                                 < t b o d y >  
                                         < ? p h p   i f ( e m p t y ( $ d a t a [ ' p o s ' ] ) ) :   ? >  
                                                 < t r > < t d   c o l s p a n = " 6 "   s t y l e = " t e x t - a l i g n :   c e n t e r ;   c o l o r :   v a r ( - - t - s e c o n d a r y ) ;   p a d d i n g :   2 0 p x ; " > N o   p u r c h a s e   o r d e r s . < / t d > < / t r >  
                                         < ? p h p   e l s e :   f o r e a c h ( $ d a t a [ ' p o s ' ]   a s   $ p o ) :   ? >  
                                                 < t r >  
                                                         < t d > < ? =   d a t e ( ' M   d ,   Y ' ,   s t r t o t i m e ( $ p o - > p o _ d a t e ) )   ? > < / t d >  
                                                         < t d   s t y l e = " c o l o r : v a r ( - - t - s e c o n d a r y ) ; " > < ? =   $ p o - > e x p e c t e d _ d a t e   ?   d a t e ( ' M   d ,   Y ' ,   s t r t o t i m e ( $ p o - > e x p e c t e d _ d a t e ) )   :   ' N / A '   ? > < / t d >  
                                                         < t d > < s t r o n g > < ? =   h t m l s p e c i a l c h a r s ( $ p o - > p o _ n u m b e r )   ? > < / s t r o n g > < / t d >  
                                                         < t d > < s p a n   c l a s s = " s f - b a d g e   b a d g e - < ? =   $ p o - > s t a t u s   ? > " > < ? =   $ p o - > s t a t u s   ? > < / s p a n > < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " f o n t - w e i g h t : 7 0 0 ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ; " > R s .   < ? =   n u m b e r _ f o r m a t ( $ p o - > t o t a l _ a m o u n t ,   2 )   ? > < / t d >  
                                                         < t d   s t y l e = " t e x t - a l i g n : r i g h t ; " >  
                                                                 < a   h r e f = " < ? =   A P P _ U R L   ? > / p u r c h a s e / s h o w / < ? =   $ p o - > i d   ? > "   t a r g e t = " _ b l a n k "   c l a s s = " s f - b t n   n e u t r a l   s f - b t n - s m a l l " > V i e w < / a >  
                                                         < / t d >  
                                                 < / t r >  
                                         < ? p h p   e n d f o r e a c h ;   e n d i f ;   ? >  
                                 < / t b o d y >  
                         < / t a b l e >  
                 < / d i v >  
  
                 < ! - -   T A B   3 :   P r o d u c t s   - - >  
                 < d i v   i d = " m t a b _ p r o d u c t s "   c l a s s = " t a b - c o n t e n t "   s t y l e = " p a d d i n g :   2 2 p x ;   o v e r f l o w - y :   a u t o ;   f l e x :   1 ; " >  
                         < t a b l e   c l a s s = " d a t a - t a b l e " >  
                                 < t h e a d >  
                                         < t r >  
                                                 < t h > S K U < / t h >  
                                                 < t h > P r o d u c t   N a m e < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > L a s t   C o s t < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > S e l l   P r i c e < / t h >  
                                                 < t h   c l a s s = " n u m - c o l " > S t o c k < / t h >  
                                         < / t r >  
                                 < / t h e a d >  
                                 < t b o d y >  
                                         < ? p h p   i f ( e m p t y ( $ d a t a [ ' p r o d u c t s ' ] ) ) :   ? >  
                                                 < t r > < t d   c o l s p a n = " 5 "   s t y l e = " t e x t - a l i g n :   c e n t e r ;   c o l o r :   v a r ( - - t - s e c o n d a r y ) ;   p a d d i n g :   2 0 p x ; " > N o   p r o d u c t s   a s s i g n e d . < / t d > < / t r >  
                                         < ? p h p   e l s e :   f o r e a c h ( $ d a t a [ ' p r o d u c t s ' ]   a s   $ p ) :   ? >  
                                                 < t r >  
                                                         < t d   s t y l e = " c o l o r : v a r ( - - t - s e c o n d a r y ) ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ;   f o n t - s i z e : 1 3 p x ; " > < ? =   h t m l s p e c i a l c h a r s ( $ p - > s k u   ? :   ' N / A ' )   ? > < / t d >  
                                                         < t d > < s t r o n g > < ? =   h t m l s p e c i a l c h a r s ( $ p - > p r o d u c t _ n a m e )   ? > < / s t r o n g > < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " f o n t - w e i g h t : 6 0 0 ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ; " > R s .   < ? =   n u m b e r _ f o r m a t ( $ p - > c o s t _ p r i c e ,   2 )   ? > < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " c o l o r : v a r ( - - c - b l u e ) ;   f o n t - w e i g h t : 6 0 0 ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ; " > R s .   < ? =   n u m b e r _ f o r m a t ( $ p - > p r i c e ,   2 )   ? > < / t d >  
                                                         < t d   c l a s s = " n u m - c o l "   s t y l e = " f o n t - w e i g h t : 7 0 0 ;   f o n t - f a m i l y : v a r ( - - f - m o n o ) ;   c o l o r :   < ? =   $ p - > q u a n t i t y _ o n _ h a n d   >   5   ?   ' v a r ( - - t - p r i m a r y ) '   :   ' v a r ( - - c - o r a n g e ) '   ? > ; " >  
                                                                 < ? =   n u m b e r _ f o r m a t ( $ p - > q u a n t i t y _ o n _ h a n d )   ? >  
                                                         < / t d >  
                                                 < / t r >  
                                         < ? p h p   e n d f o r e a c h ;   e n d i f ;   ? >  
                                 < / t b o d y >  
                         < / t a b l e >  
                 < / d i v >  
  
                 < ! - -   T A B   4 :   P r o f i l e   - - >  
                 < d i v   i d = " m t a b _ p r o f i l e "   c l a s s = " t a b - c o n t e n t "   s t y l e = " p a d d i n g :   2 2 p x ;   o v e r f l o w - y :   a u t o ;   f l e x :   1 ; " >  
                         < h 4   s t y l e = " m a r g i n : 0   0   1 4 p x   0 ;   b o r d e r - b o t t o m :   0 . 5 p x   s o l i d   v a r ( - - c - s e p a r a t o r ) ;   p a d d i n g - b o t t o m :   6 p x ;   f o n t - s i z e : 1 4 p x ;   f o n t - w e i g h t : 7 0 0 ;   t e x t - t r a n s f o r m : u p p e r c a s e ;   c o l o r : v a r ( - - t - s e c o n d a r y ) ; " >  
                                 E d i t   S u p p l i e r   P r o f i l e  
                         < / h 4 >  
                          
                         < f o r m   a c t i o n = " < ? =   A P P _ U R L   ? > / s u p p l i e r / i n d e x / < ? =   $ s u p - > i d   ? > "   m e t h o d = " P O S T "   s t y l e = " m a x - w i d t h :   6 0 0 p x ; " >  
                                 < i n p u t   t y p e = " h i d d e n "   n a m e = " a c t i o n "   v a l u e = " u p d a t e _ s u p p l i e r " >  
                                 < i n p u t   t y p e = " h i d d e n "   n a m e = " s u p p l i e r _ i d "   v a l u e = " < ? =   $ s u p - > i d   ? > " >  
                                  
                                 < d i v   c l a s s = " g r i d - 2 " >  
                                         < d i v   c l a s s = " s f - g r o u p " >  
                                                 < l a b e l > C o m p a n y   N a m e   * < / l a b e l >  
                                                 < i n p u t   t y p e = " t e x t "   n a m e = " n a m e "   c l a s s = " s f - i n p u t "   v a l u e = " < ? =   h t m l s p e c i a l c h a r s ( $ s u p - > n a m e )   ? > "   r e q u i r e d >  
                                         < / d i v >  
                                         < d i v   c l a s s = " s f - g r o u p " >  
                                                 < l a b e l > E m a i l   A d d r e s s < / l a b e l >  
                                                 < i n p u t   t y p e = " e m a i l "   n a m e = " e m a i l "   c l a s s = " s f - i n p u t "   v a l u e = " < ? =   h t m l s p e c i a l c h a r s ( $ s u p - > e m a i l )   ? > " >  
                                         < / d i v >  
                                 < / d i v >  
                                 < d i v   c l a s s = " g r i d - 2 " >  
                                         < d i v   c l a s s = " s f - g r o u p " >  
                                                 < l a b e l > P h o n e   N u m b e r < / l a b e l >  
                                                 < i n p u t   t y p e = " t e x t "   n a m e = " p h o n e "   c l a s s = " s f - i n p u t "   v a l u e = " < ? =   h t m l s p e c i a l c h a r s ( $ s u p - > p h o n e )   ? > " >  
                                         < / d i v >  
                                         < d i v   c l a s s = " s f - g r o u p " >  
                                                 < l a b e l > P h y s i c a l   A d d r e s s < / l a b e l >  
                                                 < t e x t a r e a   n a m e = " a d d r e s s "   c l a s s = " s f - i n p u t "   r o w s = " 2 "   s t y l e = " r e s i z e : v e r t i c a l ;   m i n - h e i g h t : 5 0 p x ; " > < ? =   h t m l s p e c i a l c h a r s ( $ s u p - > a d d r e s s )   ? > < / t e x t a r e a >  
                                         < / d i v >  
                                 < / d i v >  
                                  
                                 < d i v   s t y l e = " t e x t - a l i g n :   r i g h t ;   m a r g i n - t o p :   1 0 p x ; " >  
                                         < b u t t o n   t y p e = " s u b m i t "   c l a s s = " s f - b t n   p r i m a r y " > S a v e   C h a n g e s < / b u t t o n >  
                                 < / d i v >  
                         < / f o r m >  
                 < / d i v >  
         < / d i v >  
 < ? p h p   e n d i f ;   ? >  
  
 < ! - -   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  
           A D D   S U P P L I E R   M O D A L  
           = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =   - - >  
 < d i v   c l a s s = " m o d a l - v e i l   h i d d e n "   i d = " a d d S u p p l i e r M o d a l "   o n c l i c k = " i f ( e v e n t . t a r g e t   = = =   t h i s )   c l o s e M o d a l ( ' a d d S u p p l i e r M o d a l ' ) " >  
         < d i v   c l a s s = " s f - m o d a l "   s t y l e = " w i d t h :   4 8 0 p x ; " >  
                 < d i v   c l a s s = " m o d a l - h e a d " >  
                         < h 3   c l a s s = " m o d a l - t i t l e " > A d d   N e w   S u p p l i e r < / h 3 >  
                         < b u t t o n   t y p e = " b u t t o n "   c l a s s = " m o d a l - c l o s e "   o n c l i c k = " c l o s e M o d a l ( ' a d d S u p p l i e r M o d a l ' ) " > < i   c l a s s = " f a - s o l i d   f a - x m a r k " > < / i > < / b u t t o n >  
                 < / d i v >  
                 < f o r m   a c t i o n = " < ? =   A P P _ U R L   ? > / s u p p l i e r "   m e t h o d = " P O S T " >  
                         < d i v   c l a s s = " m o d a l - b o d y " >  
                                 < i n p u t   t y p e = " h i d d e n "   n a m e = " a c t i o n "   v a l u e = " a d d _ s u p p l i e r " >  
                                 < d i v   c l a s s = " s f - g r o u p " >  
                                         < l a b e l > C o m p a n y   N a m e   * < / l a b e l >  
                                         < i n p u t   t y p e = " t e x t "   n a m e = " n a m e "   c l a s s = " s f - i n p u t "   r e q u i r e d >  
                                 < / d i v >  
                                 < d i v   c l a s s = " s f - g r o u p " >  
                                         < l a b e l > E m a i l < / l a b e l >  
                                         < i n p u t   t y p e = " e m a i l "   n a m e = " e m a i l "   c l a s s = " s f - i n p u t " >  
                                 < / d i v >  
                                 < d i v   c l a s s = " s f - g r o u p " >  
                                         < l a b e l > P h o n e < / l a b e l >  
                                         < i n p u t   t y p e = " t e x t "   n a m e = " p h o n e "   c l a s s = " s f - i n p u t " >  
                                 < / d i v >  
                                 < d i v   c l a s s = " s f - g r o u p " >  
                                         < l a b e l > A d d r e s s < / l a b e l >  
                                         < t e x t a r e a   n a m e = " a d d r e s s "   c l a s s = " s f - i n p u t "   r o w s = " 2 "   s t y l e = " r e s i z e : v e r t i c a l ; " > < / t e x t a r e a >  
                                 < / d i v >  
                         < / d i v >  
                         < d i v   c l a s s = " m o d a l - f o o t " >  
                                 < b u t t o n   t y p e = " b u t t o n "   c l a s s = " s f - b t n   n e u t r a l "   o n c l i c k = " c l o s e M o d a l ( ' a d d S u p p l i e r M o d a l ' ) " > C a n c e l < / b u t t o n >  
                                 < b u t t o n   t y p e = " s u b m i t "   c l a s s = " s f - b t n   p r i m a r y " > R e g i s t e r   S u p p l i e r < / b u t t o n >  
                         < / d i v >  
                 < / f o r m >  
         < / d i v >  
 < / d i v >  
  
 < s c r i p t >  
         / /   - - -   P a g i n a t i o n   a n d   L i s t   G l o b a l s   - - -  
         l e t   a l l S u p p l i e r s   =   [ ] ;  
         l e t   f i l t e r e d S u p p l i e r s   =   [ ] ;  
         l e t   c u r r e n t P a g e   =   1 ;  
         l e t   p a g e S i z e   =   1 5 ;  
  
         d o c u m e n t . a d d E v e n t L i s t e n e r ( " D O M C o n t e n t L o a d e d " ,   f u n c t i o n ( )   {  
                 c o n s t   r o w s   =   d o c u m e n t . q u e r y S e l e c t o r A l l ( ' # s u p L i s t   . s u p p l i e r - r o w ' ) ;  
                 r o w s . f o r E a c h ( r o w   = >   {  
                         a l l S u p p l i e r s . p u s h ( {  
                                 e l e m e n t :   r o w ,  
                                 n a m e :   r o w . g e t A t t r i b u t e ( ' d a t a - n a m e ' ) ,  
                                 p h o n e :   r o w . g e t A t t r i b u t e ( ' d a t a - p h o n e ' ) ,  
                                 e m a i l :   r o w . g e t A t t r i b u t e ( ' d a t a - e m a i l ' ) ,  
                                 o u t s t a n d i n g :   p a r s e F l o a t ( r o w . g e t A t t r i b u t e ( ' d a t a - o u t s t a n d i n g ' ) )  
                         } ) ;  
                 } ) ;  
                 f i l t e r e d S u p p l i e r s   =   [ . . . a l l S u p p l i e r s ] ;  
                 r e n d e r P a g i n a t i o n ( ) ;  
                  
                 / /   A u t o - o p e n   p r o f i l e   m o d a l   i f   U R L   h a s   ? s u p p l i e r _ i d = . . .   o r   / s u p p l i e r / i n d e x / 1 2 3  
                 c o n s t   p a t h P a r t s   =   w i n d o w . l o c a t i o n . p a t h n a m e . s p l i t ( ' / ' ) ;  
                 c o n s t   i d I n d e x   =   p a t h P a r t s . i n d e x O f ( ' i n d e x ' ) ;  
                 l e t   a u t o L o a d I d   =   n u l l ;  
                 i f   ( i d I n d e x   ! = =   - 1   & &   p a t h P a r t s . l e n g t h   >   i d I n d e x   +   1 )   {  
                         a u t o L o a d I d   =   p a t h P a r t s [ i d I n d e x   +   1 ] ;  
                 }   e l s e   {  
                         c o n s t   u r l P a r a m s   =   n e w   U R L S e a r c h P a r a m s ( w i n d o w . l o c a t i o n . s e a r c h ) ;  
                         a u t o L o a d I d   =   u r l P a r a m s . g e t ( ' s u p p l i e r _ i d ' ) ;  
                 }  
                  
                 i f   ( a u t o L o a d I d   & &   ! i s N a N ( a u t o L o a d I d ) )   {  
                         c o n s t   t a b   =   n e w   U R L S e a r c h P a r a m s ( w i n d o w . l o c a t i o n . s e a r c h ) . g e t ( ' t a b ' ) ;  
                         s h o w S u p p l i e r P r o f i l e ( a u t o L o a d I d ,   t a b ) ;  
                 }  
         } ) ;  
  
         / /   - - -   S e a r c h   &   F i l t e r   H a n d l e r s   - - -  
         l e t   f i l t e r S t a t u s V a l u e   =   ' ' ;  
          
         f u n c t i o n   f i l t e r L i s t ( )   {  
                 c o n s t   q u e r y   =   d o c u m e n t . g e t E l e m e n t B y I d ( ' s e a r c h I n p u t ' ) . v a l u e . t o L o w e r C a s e ( ) ;  
                  
                 f i l t e r e d S u p p l i e r s   =   a l l S u p p l i e r s . f i l t e r ( c   = >   {  
                         c o n s t   m a t c h Q u e r y   =   c . n a m e . i n c l u d e s ( q u e r y )   | |   c . p h o n e . i n c l u d e s ( q u e r y )   | |   c . e m a i l . i n c l u d e s ( q u e r y ) ;  
                         l e t   m a t c h S t a t u s   =   t r u e ;  
                          
                         i f   ( f i l t e r S t a t u s V a l u e   = = =   ' o w e d ' )   m a t c h S t a t u s   =   ( c . o u t s t a n d i n g   >   0 ) ;  
                         e l s e   i f   ( f i l t e r S t a t u s V a l u e   = = =   ' c l e a r e d ' )   m a t c h S t a t u s   =   ( c . o u t s t a n d i n g   < =   0 ) ;  
                          
                         r e t u r n   m a t c h Q u e r y   & &   m a t c h S t a t u s ;  
                 } ) ;  
                  
                 c u r r e n t P a g e   =   1 ;  
                 r e n d e r P a g i n a t i o n ( ) ;  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' m a t c h i n g - c o u n t ' ) . i n n e r T e x t   =   f i l t e r e d S u p p l i e r s . l e n g t h ;  
         }  
  
         f u n c t i o n   s e l e c t S t a t u s ( v a l ,   l a b e l )   {  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' f i l t e r S t a t u s ' ) . v a l u e   =   v a l ;  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' s t a t u s - d r o p d o w n - v a l ' ) . i n n e r T e x t   =   l a b e l ;  
                 f i l t e r S t a t u s V a l u e   =   v a l ;  
                 f i l t e r L i s t ( ) ;  
                 d o c u m e n t . a c t i v e E l e m e n t . b l u r ( ) ;  
         }  
  
         f u n c t i o n   c l e a r A l l F i l t e r s ( )   {  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' s e a r c h I n p u t ' ) . v a l u e   =   ' ' ;  
                 s e l e c t S t a t u s ( ' ' ,   ' A l l   A c c o u n t s ' ) ;  
         }  
  
         / /   - - -   P a g i n a t i o n   R e n d e r   - - -  
         f u n c t i o n   r e n d e r P a g i n a t i o n ( )   {  
                 a l l S u p p l i e r s . f o r E a c h ( c   = >   c . e l e m e n t . s t y l e . d i s p l a y   =   ' n o n e ' ) ;  
                  
                 i f   ( f i l t e r e d S u p p l i e r s . l e n g t h   = = =   0 )   {  
                         d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - i n f o - t e x t ' ) . i n n e r H T M L   =   " N o   m a t c h i n g   s u p p l i e r s " ;  
                         d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - c u r r e n t - t e x t ' ) . i n n e r T e x t   =   " 0   /   0 " ;  
                         d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - p r e v - b t n ' ) . d i s a b l e d   =   t r u e ;  
                         d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - n e x t - b t n ' ) . d i s a b l e d   =   t r u e ;  
                         r e t u r n ;  
                 }  
                  
                 l e t   t o t a l P a g e s   =   M a t h . c e i l ( f i l t e r e d S u p p l i e r s . l e n g t h   /   p a g e S i z e ) ;  
                 i f   ( c u r r e n t P a g e   >   t o t a l P a g e s )   c u r r e n t P a g e   =   t o t a l P a g e s ;  
                 i f   ( c u r r e n t P a g e   <   1 )   c u r r e n t P a g e   =   1 ;  
                  
                 l e t   s t a r t I d x   =   ( c u r r e n t P a g e   -   1 )   *   p a g e S i z e ;  
                 l e t   e n d I d x   =   s t a r t I d x   +   p a g e S i z e ;  
                  
                 f o r   ( l e t   i   =   s t a r t I d x ;   i   <   e n d I d x   & &   i   <   f i l t e r e d S u p p l i e r s . l e n g t h ;   i + + )   {  
                         f i l t e r e d S u p p l i e r s [ i ] . e l e m e n t . s t y l e . d i s p l a y   =   ' t a b l e - r o w ' ;  
                 }  
                  
                 l e t   s h o w i n g E n d   =   M a t h . m i n ( e n d I d x ,   f i l t e r e d S u p p l i e r s . l e n g t h ) ;  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - i n f o - t e x t ' ) . i n n e r H T M L   =   ` S h o w i n g   < s t r o n g > $ { s t a r t I d x   +   1 } < / s t r o n g >   ‚ ¨    < s t r o n g > $ { s h o w i n g E n d } < / s t r o n g >   o f   < s t r o n g > $ { f i l t e r e d S u p p l i e r s . l e n g t h } < / s t r o n g > ` ;  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - c u r r e n t - t e x t ' ) . i n n e r T e x t   =   ` $ { c u r r e n t P a g e }   /   $ { t o t a l P a g e s } ` ;  
                  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - p r e v - b t n ' ) . d i s a b l e d   =   ( c u r r e n t P a g e   = = =   1 ) ;  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' p g - n e x t - b t n ' ) . d i s a b l e d   =   ( c u r r e n t P a g e   = = =   t o t a l P a g e s ) ;  
         }  
          
         f u n c t i o n   n a v i g a t e P a g e ( p a g e )   {  
                 l e t   t o t a l P a g e s   =   M a t h . c e i l ( f i l t e r e d S u p p l i e r s . l e n g t h   /   p a g e S i z e ) ;  
                 i f   ( p a g e   > =   1   & &   p a g e   < =   t o t a l P a g e s )   {  
                         c u r r e n t P a g e   =   p a g e ;  
                         r e n d e r P a g i n a t i o n ( ) ;  
                 }  
         }  
          
         f u n c t i o n   u p d a t e P a g e S i z e ( s i z e )   {  
                 i f   ( s i z e   = = =   ' 1 0 0 0 ' )   {   p a g e S i z e   =   9 9 9 9 9 9 ;   }   e l s e   {   p a g e S i z e   =   p a r s e I n t ( s i z e ) ;   }  
                 c u r r e n t P a g e   =   1 ;  
                 r e n d e r P a g i n a t i o n ( ) ;  
         }  
  
         / /   - - -   M o d a l   C o n t r o l   H e l p e r   f u n c t i o n s   - - -  
         f u n c t i o n   o p e n M o d a l ( i d )   {   d o c u m e n t . g e t E l e m e n t B y I d ( i d ) . c l a s s L i s t . r e m o v e ( ' h i d d e n ' ) ;   }  
         f u n c t i o n   c l o s e M o d a l ( i d )   {   d o c u m e n t . g e t E l e m e n t B y I d ( i d ) . c l a s s L i s t . a d d ( ' h i d d e n ' ) ;   }  
  
         / /   - - -   S u p p l i e r   P r o f i l e   P o p u p   M o d a l   H a n d l e r s   - - -  
         f u n c t i o n   s h o w S u p p l i e r P r o f i l e ( i d ,   t a b   =   n u l l )   {  
                 c o n s t   m o d a l   =   d o c u m e n t . g e t E l e m e n t B y I d ( ' s u p p l i e r P r o f i l e M o d a l ' ) ;  
                 c o n s t   l o a d e r   =   d o c u m e n t . g e t E l e m e n t B y I d ( ' m o d a l - l o a d e r ' ) ;  
                 c o n s t   c o n t e n t   =   d o c u m e n t . g e t E l e m e n t B y I d ( ' m o d a l - p r o f i l e - c o n t e n t ' ) ;  
                  
                 m o d a l . c l a s s L i s t . r e m o v e ( ' h i d d e n ' ) ;  
                 l o a d e r . s t y l e . d i s p l a y   =   ' f l e x ' ;  
                 c o n t e n t . s t y l e . d i s p l a y   =   ' n o n e ' ;  
                  
                 l e t   t a r g e t U r l   =   ' < ? =   A P P _ U R L   ? > / s u p p l i e r / i n d e x / '   +   i d ;  
                 i f   ( t a b )   {   t a r g e t U r l   + =   ' ? t a b = '   +   t a b ;   }  
                 w i n d o w . h i s t o r y . p u s h S t a t e ( {   p a t h :   t a r g e t U r l   } ,   ' ' ,   t a r g e t U r l ) ;  
                  
                 f e t c h ( t a r g e t U r l )  
                         . t h e n ( r e s p o n s e   = >   {  
                                 i f   ( ! r e s p o n s e . o k )   t h r o w   n e w   E r r o r ( ' F a i l e d   t o   l o a d   p r o f i l e ' ) ;  
                                 r e t u r n   r e s p o n s e . t e x t ( ) ;  
                         } )  
                         . t h e n ( h t m l   = >   {  
                                 c o n s t   p a r s e r   =   n e w   D O M P a r s e r ( ) ;  
                                 c o n s t   d o c   =   p a r s e r . p a r s e F r o m S t r i n g ( h t m l ,   ' t e x t / h t m l ' ) ;  
                                  
                                 c o n s t   n e w C o n t e n t   =   d o c . g e t E l e m e n t B y I d ( ' m o d a l - p r o f i l e - c o n t e n t - s o u r c e ' ) ;  
                                 c o n s t   n e w H e a d e r   =   d o c . g e t E l e m e n t B y I d ( ' m o d a l - h e a d e r - s o u r c e ' ) ;  
                                  
                                 i f   ( n e w C o n t e n t   & &   n e w H e a d e r )   {  
                                         c o n t e n t . i n n e r H T M L   =   n e w C o n t e n t . i n n e r H T M L ;  
                                         d o c u m e n t . g e t E l e m e n t B y I d ( ' m o d a l - h e a d e r - c o n t a i n e r ' ) . i n n e r H T M L   =   n e w H e a d e r . i n n e r H T M L ;  
                                         l o a d e r . s t y l e . d i s p l a y   =   ' n o n e ' ;  
                                         c o n t e n t . s t y l e . d i s p l a y   =   ' f l e x ' ;  
                                          
                                         i f   ( t a b )   {   s w i t c h M o d a l T a b ( t a b ) ;   }    
                                         e l s e   {   s w i t c h M o d a l T a b ( ' l e d g e r ' ) ;   }  
                                 }   e l s e   {  
                                         t h r o w   n e w   E r r o r ( ' P r o f i l e   l a y o u t   m i s m a t c h ' ) ;  
                                 }  
                         } )  
                         . c a t c h ( e r r   = >   {  
                                 c o n s o l e . e r r o r ( e r r ) ;  
                                 l o a d e r . s t y l e . d i s p l a y   =   ' n o n e ' ;  
                                 c o n t e n t . i n n e r H T M L   =   ` < d i v   s t y l e = " p a d d i n g : 4 0 p x ;   t e x t - a l i g n : c e n t e r ;   c o l o r : v a r ( - - c - r e d ) ;   f o n t - w e i g h t : 6 0 0 ; " > < i   c l a s s = " f a - s o l i d   f a - t r i a n g l e - e x c l a m a t i o n "   s t y l e = " f o n t - s i z e : 2 4 p x ;   m a r g i n - b o t t o m : 1 0 p x ;   d i s p l a y : b l o c k ; " > < / i > F a i l e d   t o   l o a d   s u p p l i e r   p r o f i l e   d a t a . < / d i v > ` ;  
                                 c o n t e n t . s t y l e . d i s p l a y   =   ' b l o c k ' ;  
                         } ) ;  
         }  
  
         f u n c t i o n   c l o s e S u p p l i e r P r o f i l e ( )   {  
                 d o c u m e n t . g e t E l e m e n t B y I d ( ' s u p p l i e r P r o f i l e M o d a l ' ) . c l a s s L i s t . a d d ( ' h i d d e n ' ) ;  
                 w i n d o w . h i s t o r y . p u s h S t a t e ( {   p a t h :   ' < ? =   A P P _ U R L   ? > / s u p p l i e r '   } ,   ' ' ,   ' < ? =   A P P _ U R L   ? > / s u p p l i e r ' ) ;  
         }  
  
         f u n c t i o n   s w i t c h M o d a l T a b ( t a b N a m e )   {  
                 c o n s t   c o n t e n t   =   d o c u m e n t . g e t E l e m e n t B y I d ( ' m o d a l - p r o f i l e - c o n t e n t ' ) ;  
                 c o n t e n t . q u e r y S e l e c t o r A l l ( ' . t a b - c o n t e n t ' ) . f o r E a c h ( e l   = >   e l . s t y l e . d i s p l a y   =   ' n o n e ' ) ;  
                 c o n t e n t . q u e r y S e l e c t o r A l l ( ' . t a b - b t n ' ) . f o r E a c h ( e l   = >   e l . c l a s s L i s t . r e m o v e ( ' a c t i v e ' ) ) ;  
                  
                 c o n s t   t a b E l   =   c o n t e n t . q u e r y S e l e c t o r ( ' # m t a b _ '   +   t a b N a m e ) ;  
                 c o n s t   b t n E l   =   c o n t e n t . q u e r y S e l e c t o r ( ' # m b t n _ '   +   t a b N a m e ) ;  
                  
                 i f   ( t a b E l )   t a b E l . s t y l e . d i s p l a y   =   ' b l o c k ' ;  
                 i f   ( b t n E l )   b t n E l . c l a s s L i s t . a d d ( ' a c t i v e ' ) ;  
                  
                 c o n s t   u r l P a r a m s   =   n e w   U R L S e a r c h P a r a m s ( w i n d o w . l o c a t i o n . s e a r c h ) ;  
                 u r l P a r a m s . s e t ( ' t a b ' ,   t a b N a m e ) ;  
                 c o n s t   n e w U r l   =   w i n d o w . l o c a t i o n . p a t h n a m e   +   ' ? '   +   u r l P a r a m s . t o S t r i n g ( ) ;  
                 w i n d o w . h i s t o r y . r e p l a c e S t a t e ( {   p a t h :   n e w U r l   } ,   ' ' ,   n e w U r l ) ;  
         }  
 < / s c r i p t >  
 