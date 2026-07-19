<?php
// Variable fallbacks & session alerts
$successMsg = $data['success'] ?? '';
$errorMsg = $_SESSION['discount_error'] ?? $data['error'] ?? '';
if (isset($_SESSION['discount_error'])) {
    unset($_SESSION['discount_error']);
}

$rules = $data['rules'] ?? [];
$items = $data['items'] ?? [];
$categories = $data['categories'] ?? [];
$filters = $data['filters'] ?? [];
$metrics = $data['metrics'] ?? [
    'total' => 0, 'active' => 0, 'item_wise' => 0, 'bill_wise' => 0, 'category_wise' => 0, 'expired' => 0
];
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — DISCOUNT RULES ENGINE
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
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;
    --c-teal:         #5ac8fa;
    --c-teal-light:   #eaf8ff;

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

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #121212;
        --c-surface:      #1e1e2e;
        --c-surface2:     #161622;
        --c-fill:         rgba(255,255,255,0.08);
        --c-fill2:        rgba(255,255,255,0.12);
        --c-separator:    rgba(255,255,255,0.15);
        --c-separator2:   rgba(255,255,255,0.08);
        --t-primary:   #f5f5f7;
        --t-secondary: #a1a1aa;
        --t-tertiary:  #71717a;
        --t-label:     #52525b;
    }
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.disc-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.disc-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 10px 24px 160px;
}

.disc-split {
    display: flex;
    gap: 18px;
    align-items: flex-start;
}
.disc-main { flex: 1; min-width: 0; }

/* ---- Page Header ---- */
.disc-header {
    margin-bottom: 24px;
}
.disc-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.disc-title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--t-primary);
    margin-bottom: 6px;
}
.disc-sub {
    font-size: 14px;
    color: var(--t-secondary);
}

/* ---- Stat Cards (SF Widgets style) ---- */
.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
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
    gap: 16px;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2.5px;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
}
.stat-card.blue::before   { background: var(--c-blue); }
.stat-card.green::before  { background: var(--c-green); }
.stat-card.purple::before { background: var(--c-purple); }
.stat-card.orange::before { background: var(--c-orange); }
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.stat-icon {
    width: 46px; height: 46px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-card.blue   .stat-icon { background: var(--c-blue-light);   color: var(--c-blue); }
.stat-card.green  .stat-icon { background: var(--c-green-light);  color: var(--c-green); }
.stat-card.purple .stat-icon { background: var(--c-purple-light); color: var(--c-purple); }
.stat-card.orange .stat-icon { background: var(--c-orange-light); color: var(--c-orange); }

.stat-info { display: flex; flex-direction: column; justify-content: center; }
.stat-num {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.04em;
    color: var(--t-primary);
    line-height: 1.1;
    margin-bottom: 2px;
    font-family: var(--f-mono);
}
.stat-lbl {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--t-label);
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
    border-left-width: 3.5px;
    font-size: 14px;
}
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 700; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }
.sf-alert-close {
    margin-left: auto; flex-shrink: 0; background: none; border: none;
    color: var(--t-tertiary); cursor: pointer; font-size: 15px; padding: 2px;
}
.sf-alert-close:hover { color: var(--t-secondary); }

/* ---- Filter Shelf ---- */
.filter-shelf {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 20px;
}
.filter-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    color: var(--t-secondary);
    box-shadow: var(--shadow-xs);
    transition: border-color var(--dur-fast), box-shadow var(--dur-fast);
}
.filter-chip:focus-within {
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.12);
}
.filter-chip-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--t-label);
    text-transform: uppercase;
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

/* ---- Custom Dropdown ---- */
.sf-dropdown { position: relative; outline: none; cursor: pointer; }
.sf-dropdown-val {
    display: flex; align-items: center; gap: 5px;
    font-size: 13.5px; font-weight: 600; color: var(--t-primary);
}
.sf-dropdown-val::after {
    content: '';
    display: inline-block; width: 12px; height: 12px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238e8e93' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") center/contain no-repeat;
}
.sf-dropdown-menu {
    position: absolute; top: calc(100% + 10px); left: 0; z-index: 200;
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl);
    min-width: 200px;
    max-height: 280px; overflow-y: auto;
    opacity: 0; visibility: hidden;
    transform: translateY(-6px) scale(0.98);
    transform-origin: top left;
    transition: opacity var(--dur-mid) var(--ease-ios),
                transform var(--dur-mid) var(--ease-ios),
                visibility var(--dur-mid);
    padding: 6px;
}
.sf-dropdown:focus-within .sf-dropdown-menu {
    opacity: 1; visibility: visible; transform: translateY(0) scale(1);
}
.sf-dropdown-item {
    padding: 9px 12px;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--t-primary);
    border-radius: var(--r-sm);
    transition: background var(--dur-fast);
    cursor: pointer;
}
.sf-dropdown-item:hover { background: var(--c-fill); }
.sf-dropdown-item.active { background: var(--c-blue-light); color: var(--c-blue); font-weight: 600; }

/* ---- Table Panel ---- */
.table-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    position: relative;
}
.disc-table { width: 100%; border-collapse: collapse; }
.disc-table thead th {
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
.disc-table tbody tr {
    transition: background var(--dur-fast);
    border-bottom: 0.5px solid var(--c-separator2);
    cursor: pointer;
}
.disc-table tbody tr:last-child { border-bottom: none; }
.disc-table tbody tr:hover { background: var(--c-fill2); }
.disc-table td {
    padding: 14px 18px;
    font-size: 14px;
    color: var(--t-primary);
    vertical-align: middle;
}

/* ---- Badges & Labels ---- */
.sf-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: var(--r-pill);
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.02em;
    white-space: nowrap;
}
.sf-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-active  { background: var(--c-green-light);  color: var(--c-green); }
.badge-inactive{ background: var(--c-fill);         color: var(--t-secondary); }
.badge-expired { background: var(--c-red-light);    color: var(--c-red); }
.badge-item    { background: var(--c-blue-light);   color: var(--c-blue); }
.badge-category{ background: var(--c-orange-light); color: var(--c-orange); }
.badge-bill    { background: var(--c-purple-light); color: var(--c-purple); }

/* ---- Row Actions ---- */
.act-btn {
    width: 30px; height: 30px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; background: var(--c-fill); cursor: pointer;
    font-size: 12.5px; transition: transform var(--dur-fast) var(--ease-spring), background var(--dur-fast);
    text-decoration: none; color: var(--t-secondary);
}
.act-btn:hover { transform: scale(1.12); }
.act-btn.view:hover   { background: var(--c-blue-light); color: var(--c-blue); }
.act-btn.edit:hover   { background: var(--c-purple-light); color: var(--c-purple); }
.act-btn.copy:hover   { background: var(--c-teal-light); color: var(--c-teal); }
.act-btn.trash:hover  { background: var(--c-red-light); color: var(--c-red); }

/* ---- Command Bar (Dynamic Island style) ---- */
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
    display: flex; align-items: center; gap: 4px;
    box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3);
    z-index: 100;
}
.cmd-search {
    display: flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.1);
    border-radius: var(--r-pill);
    padding: 8px 14px;
    width: 210px;
    transition: width var(--dur-slow) var(--ease-ios), background var(--dur-mid);
}
.cmd-search:focus-within {
    width: 320px;
    background: rgba(255,255,255,0.18);
}
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; flex-shrink: 0; }
.cmd-search input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 14px; font-weight: 500;
    font-family: var(--f-system); width: 100%;
}
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-icon {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.8); font-size: 15px;
    background: transparent; border: none; cursor: pointer; text-decoration: none;
    transition: background var(--dur-fast);
}
.cmd-icon:hover { background: rgba(255,255,255,0.12); color: #fff; }
.cmd-cta {
    display: flex; align-items: center; gap: 7px;
    background: #fff; color: #1c1c1e;
    border: none; border-radius: var(--r-pill);
    padding: 0 18px; height: 38px;
    font-size: 14px; font-weight: 700;
    font-family: var(--f-system);
    cursor: pointer; text-decoration: none;
    transition: transform var(--dur-fast) var(--ease-spring), background var(--dur-fast);
    margin-left: 2px;
}
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

/* ---- Quick View Inspector Panel ---- */
.qv-panel {
    width: 0; opacity: 0; overflow: hidden;
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-md);
    flex-shrink: 0;
    position: sticky; top: 20px;
    height: fit-content;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    transition: width var(--dur-slow) var(--ease-ios), opacity var(--dur-slow) var(--ease-ios);
}
.qv-panel.open { width: 340px; opacity: 1; }
.qv-inner { padding: 22px; min-width: 340px; position: relative; }
.qv-close {
    position: absolute; top: 16px; right: 16px;
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--c-fill); border: none; cursor: pointer;
    color: var(--t-label); font-size: 13px;
    display: flex; align-items: center; justify-content: center;
    transition: background var(--dur-fast);
}
.qv-close:hover { background: var(--c-fill2); color: var(--t-secondary); }

/* ---- Modal System ---- */
.modal-veil {
    position: fixed; inset: 0; z-index: 500;
    background: rgba(0, 0, 0, 0.4);
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
    width: 100%; max-width: 540px;
    overflow: hidden;
    transform: translateY(16px) scale(0.97);
    transition: transform var(--dur-slow) var(--ease-spring);
}
.modal-veil:not(.hidden) .sf-modal { transform: translateY(0) scale(1); }
.modal-head {
    padding: 18px 24px;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 0.5px solid var(--c-separator);
}
.modal-title { font-size: 16px; font-weight: 700; color: var(--t-primary); }
.modal-close {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--c-fill); border: none; cursor: pointer;
    color: var(--t-label); font-size: 13px;
    display: flex; align-items: center; justify-content: center;
    transition: background var(--dur-fast);
}
.modal-close:hover { background: var(--c-fill2); }
.modal-body { padding: 22px; max-height: 70vh; overflow-y: auto; }
.modal-foot {
    padding: 16px 24px;
    background: var(--c-surface2);
    border-top: 0.5px solid var(--c-separator);
    display: flex; justify-content: flex-end; gap: 10px;
}

/* ---- Input Controls ---- */
.sf-group { margin-bottom: 14px; }
.sf-group label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 700; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.04em; }
.sf-input {
    width: 100%; padding: 10px 14px;
    background: var(--c-surface2);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    font-size: 14px; font-weight: 500; font-family: var(--f-system);
    color: var(--t-primary); outline: none;
    transition: border-color var(--dur-fast), background var(--dur-fast);
    box-sizing: border-box;
}
.sf-input:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.12);
}
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* ---- Buttons ---- */
.sf-btn {
    padding: 9px 16px;
    border-radius: var(--r-md);
    font-size: 13px; font-weight: 700;
    font-family: var(--f-system); text-align: center;
    cursor: pointer; border: 0.5px solid transparent;
    transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast);
    text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}
.sf-btn:active { transform: scale(0.97); }
.sf-btn.primary { background: var(--c-blue); color: #fff; }
.sf-btn.neutral { background: var(--c-surface); border-color: var(--c-separator); color: var(--t-primary); box-shadow: var(--shadow-xs); }
.sf-btn.neutral:hover { background: var(--c-surface2); }
.sf-btn.danger  { background: var(--c-red); color: #fff; }

.hidden { display: none !important; }
</style>

<div class="disc-root">
    <div class="disc-wrap">

        <!-- Page Header -->
        <div class="disc-header">
            <div class="disc-eyebrow">
                <i class="fa-solid fa-bolt text-amber-500"></i> Promotional Rules Engine
            </div>
            <h1 class="disc-title">Discount Feed</h1>
            <p class="disc-sub">Configure automated item-wise free issue promotions, category discounts, and bill-wise percentage thresholds.</p>
        </div>

        <!-- Stat Widgets Row -->
        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-tags"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $metrics['total'] ?></div>
                    <div class="stat-lbl">Total Rules</div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $metrics['active'] ?></div>
                    <div class="stat-lbl">Active Feeds</div>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon"><i class="fa-solid fa-gift"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $metrics['item_wise'] ?></div>
                    <div class="stat-lbl">Item Free Issues</div>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $metrics['bill_wise'] ?></div>
                    <div class="stat-lbl">Bill Discounts</div>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <?php if ($successMsg): ?>
            <div class="sf-alert success" id="success-alert">
                <i class="fa-solid fa-circle-check sf-alert-icon"></i>
                <div style="flex: 1;">
                    <div class="sf-alert-title">Success</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($successMsg) ?></div>
                </div>
                <button type="button" class="sf-alert-close" onclick="document.getElementById('success-alert').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="sf-alert error" id="error-alert">
                <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
                <div style="flex: 1;">
                    <div class="sf-alert-title">Action Failed</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($errorMsg) ?></div>
                </div>
                <button type="button" class="sf-alert-close" onclick="document.getElementById('error-alert').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- Filter Shelf -->
        <div class="filter-shelf">
            <!-- Filter by Type -->
            <div class="filter-chip">
                <span class="filter-chip-label">Type</span>
                <div class="sf-dropdown" tabindex="0">
                    <div class="sf-dropdown-val" id="type-dropdown-val">All Rule Types</div>
                    <div class="sf-dropdown-menu">
                        <div class="sf-dropdown-item active" onclick="selectRuleType('', 'All Rule Types')">All Rule Types</div>
                        <div class="sf-dropdown-item" onclick="selectRuleType('item_wise', 'Item-Wise Promotions')">Item-Wise</div>
                        <div class="sf-dropdown-item" onclick="selectRuleType('category_wise', 'Category-Wise Promotions')">Category-Wise</div>
                        <div class="sf-dropdown-item" onclick="selectRuleType('bill_wise', 'Bill-Wise Discounts')">Bill-Wise</div>
                    </div>
                    <input type="hidden" id="filterRuleType" value="">
                </div>
            </div>

            <!-- Filter by Status -->
            <div class="filter-chip">
                <span class="filter-chip-label">Status</span>
                <div class="sf-dropdown" tabindex="0">
                    <div class="sf-dropdown-val" id="status-dropdown-val">All Statuses</div>
                    <div class="sf-dropdown-menu">
                        <div class="sf-dropdown-item active" onclick="selectRuleStatus('', 'All Statuses')">All Statuses</div>
                        <div class="sf-dropdown-item" onclick="selectRuleStatus('Active', 'Active Feeds Only')">Active</div>
                        <div class="sf-dropdown-item" onclick="selectRuleStatus('Inactive', 'Inactive Feeds')">Inactive</div>
                        <div class="sf-dropdown-item" onclick="selectRuleStatus('Expired', 'Expired Campaigns')">Expired</div>
                    </div>
                    <input type="hidden" id="filterStatus" value="">
                </div>
            </div>

            <button type="button" onclick="clearAllFilters()" class="filter-reset">Reset</button>

            <div class="filter-count">
                Showing <strong id="matching-count"><?= count($rules) ?></strong> promotional rules
            </div>
        </div>

        <!-- Main Content Area with Split Quick View Inspector -->
        <div class="disc-split">
            <!-- Table Panel -->
            <div class="disc-main">
                <div class="table-panel">
                    <table class="disc-table" id="rulesTable">
                        <thead>
                            <tr>
                                <th>Rule & Campaign Info</th>
                                <th>Type & Scope</th>
                                <th>Configured Tiers</th>
                                <th style="text-align: center;">Status</th>
                                <th style="width: 130px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rules)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 60px 20px; color: var(--t-secondary);">
                                        <i class="fa-solid fa-tags" style="font-size: 36px; margin-bottom: 12px; color: var(--t-tertiary);"></i><br>
                                        <strong style="font-size: 15px; color: var(--t-primary);">No promotional rules configured yet.</strong><br>
                                        <span style="font-size: 13px;">Click "New Rule" in the action bar to create your first discount feed.</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rules as $r): ?>
                                    <tr class="rule-row" onclick="inspectRule(<?= $r->id ?>)"
                                        data-id="<?= $r->id ?>"
                                        data-name="<?= htmlspecialchars(strtolower($r->name)) ?>"
                                        data-type="<?= $r->rule_type ?>"
                                        data-status="<?= $r->is_expired ? 'Expired' : $r->status ?>"
                                        data-sku="<?= htmlspecialchars(strtolower($r->item_sku ?? '')) ?>"
                                        data-category="<?= htmlspecialchars(strtolower($r->category_name ?? '')) ?>">
                                        
                                        <td>
                                            <div style="font-weight: 700; color: var(--t-primary); font-size: 14px;">
                                                <?= htmlspecialchars($r->name) ?>
                                            </div>
                                            <?php if (!empty($r->description)): ?>
                                                <div style="font-size: 12px; color: var(--t-secondary); margin-top: 2px;">
                                                    <?= htmlspecialchars($r->description) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($r->start_date || $r->end_date || $r->discount_cap): ?>
                                                <div style="display: flex; gap: 6px; margin-top: 4px; font-size: 11px;">
                                                    <?php if ($r->start_date || $r->end_date): ?>
                                                        <span style="background: var(--c-fill); padding: 2px 6px; border-radius: var(--r-xs); font-family: var(--f-mono);">
                                                            📅 <?= $r->start_date ?: 'Start' ?> &rarr; <?= $r->end_date ?: 'Ongoing' ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($r->discount_cap): ?>
                                                        <span style="background: var(--c-orange-light); color: var(--c-orange); padding: 2px 6px; border-radius: var(--r-xs); font-weight: 700;">
                                                            Cap: Rs. <?= number_format($r->discount_cap) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($r->rule_type === 'item_wise'): ?>
                                                <span class="sf-badge badge-item"><i class="fa-solid fa-gift"></i> Item-Wise</span>
                                                <?php if (!empty($r->item_name)): ?>
                                                    <div style="font-size: 11px; color: var(--t-secondary); margin-top: 4px; font-weight: 600;">
                                                        <?= htmlspecialchars($r->item_name) ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($r->rule_type === 'category_wise'): ?>
                                                <span class="sf-badge badge-category"><i class="fa-solid fa-layer-group"></i> Category-Wise</span>
                                                <?php if (!empty($r->category_name)): ?>
                                                    <div style="font-size: 11px; color: var(--t-secondary); margin-top: 4px; font-weight: 600;">
                                                        Cat: <?= htmlspecialchars($r->category_name) ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="sf-badge badge-bill"><i class="fa-solid fa-receipt"></i> Bill-Wise</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                                <?php foreach ($r->tiers as $t): ?>
                                                    <div style="font-size: 12px; color: var(--t-primary);">
                                                        <span style="font-weight: 600;">
                                                            <?php if (in_array($r->rule_type, ['item_wise', 'category_wise'])): ?>
                                                                Qty &ge; <?= intval($t->min_threshold) ?>
                                                            <?php else: ?>
                                                                Rs <?= number_format($t->min_threshold) ?> <?= $t->max_threshold ? '- Rs ' . number_format($t->max_threshold) : '+' ?>
                                                            <?php endif; ?>
                                                        </span>
                                                        &rarr;
                                                        <span style="color: var(--c-blue); font-weight: 700;">
                                                            <?php if ($r->reward_type === 'free_issue' || $r->rule_type === 'item_wise'): ?>
                                                                <?= intval($t->reward_val) ?> Free Units
                                                            <?php else: ?>
                                                                <?= floatval($t->reward_val) ?>% Off
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>

                                        <td style="text-align: center;">
                                            <?php if ($r->is_expired): ?>
                                                <span class="sf-badge badge-expired"><span class="dot"></span> Expired</span>
                                            <?php elseif ($r->is_upcoming): ?>
                                                <span class="sf-badge badge-inactive"><span class="dot"></span> Starts Soon</span>
                                            <?php else: ?>
                                                <a href="<?= APP_URL ?>/discount/toggle/<?= $r->id ?>" onclick="event.stopPropagation()" class="sf-badge <?= $r->status === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                                                    <span class="dot"></span> <?= $r->status ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>

                                        <td style="text-align: center;" onclick="event.stopPropagation()">
                                            <div style="display: flex; gap: 4px; justify-content: center;">
                                                <button type="button" onclick="openEditModal(<?= $r->id ?>)" class="act-btn edit" title="Edit Rule"><i class="fa-solid fa-pen"></i></button>
                                                <a href="<?= APP_URL ?>/discount/duplicate/<?= $r->id ?>" class="act-btn copy" title="Duplicate Rule"><i class="fa-solid fa-copy"></i></a>
                                                <a href="<?= APP_URL ?>/discount/delete/<?= $r->id ?>" onclick="return confirm('Delete this discount rule?');" class="act-btn trash" title="Delete Rule"><i class="fa-solid fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick View Inspector Panel -->
            <div class="qv-panel" id="qvPanel">
                <div class="qv-inner">
                    <button type="button" class="qv-close" onclick="closeInspector()"><i class="fa-solid fa-xmark"></i></button>
                    
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--c-blue); letter-spacing: 0.06em; margin-bottom: 4px;" id="qvRuleType">ITEM PROMOTION</div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--t-primary); margin-bottom: 12px; line-height: 1.2;" id="qvRuleName">Rule Details</h3>

                    <div style="background: var(--c-surface2); border-radius: var(--r-md); padding: 14px; border: 0.5px solid var(--c-separator); margin-bottom: 16px;">
                        <div style="font-size: 12px; color: var(--t-secondary); margin-bottom: 8px;" id="qvDescription">No remarks added.</div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; padding-top: 8px; border-top: 0.5px solid var(--c-separator2);">
                            <span style="color: var(--t-label);">Status:</span>
                            <strong id="qvStatus">Active</strong>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--t-label); margin-bottom: 8px;">Configured Tiers</div>
                        <div id="qvTiersList" style="display: flex; flex-direction: column; gap: 6px;"></div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 20px;">
                        <button type="button" id="qvEditBtn" class="sf-btn primary" style="width: 100%;"><i class="fa-solid fa-pen"></i> Edit Rule</button>
                        <a id="qvToggleBtn" href="#" class="sf-btn neutral" style="width: 100%;"><i class="fa-solid fa-power-off"></i> Toggle Status</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Command Bar (Dynamic Island Floating Bar) -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="cmdSearchInput" onkeyup="filterRules()" placeholder="Search rules, SKUs...">
    </div>

    <div class="cmd-divider"></div>

    <button type="button" class="cmd-icon" onclick="openSimulatorModal()" title="Run Discount Simulation">
        <i class="fa-solid fa-flask text-amber-400"></i>
    </button>
    <button type="button" class="cmd-icon" onclick="window.location.reload()" title="Refresh Feeds">
        <i class="fa-solid fa-rotate-right"></i>
    </button>

    <div class="cmd-divider"></div>

    <button type="button" class="cmd-cta" onclick="openCreateModal()">
        <i class="fa-solid fa-plus"></i> New Rule
    </button>
</div>

<!-- CREATE / EDIT RULE MODAL -->
<div id="ruleModalVeil" class="modal-veil hidden">
    <div class="sf-modal">
        <div class="modal-head">
            <h3 class="modal-title" id="ruleModalTitle">Configure Discount Rule</h3>
            <button type="button" class="modal-close" onclick="closeRuleModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="<?= APP_URL ?>/discount/add" method="POST" id="ruleModalForm">
            <input type="hidden" name="rule_id" id="modalRuleId">

            <div class="modal-body">
                <div class="sf-group">
                    <label>Rule Name / Campaign Title *</label>
                    <input type="text" name="name" id="modalName" required placeholder="e.g., Buy 10 Get 2 Free on Filter Cartridges" class="sf-input">
                </div>

                <div class="grid-2 sf-group">
                    <div>
                        <label>Rule Type *</label>
                        <select name="rule_type" id="modalRuleType" onchange="toggleModalFields()" class="sf-input">
                            <option value="item_wise">Item-Wise Promotion</option>
                            <option value="category_wise">Category-Wise Promotion</option>
                            <option value="bill_wise">Bill Total-Wise Discount</option>
                        </select>
                    </div>
                    <div>
                        <label>Reward Type *</label>
                        <select name="reward_type" id="modalRewardType" class="sf-input">
                            <option value="free_issue">Free Issue Quantity</option>
                            <option value="percentage">Percentage Off (%)</option>
                        </select>
                    </div>
                </div>

                <div class="sf-group" id="modalProductWrapper">
                    <label>Target Product SKU *</label>
                    <select name="target_item_id" id="modalTargetItem" class="sf-input">
                        <option value="">Select Target Product...</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item->id ?>"><?= htmlspecialchars($item->name . ' [' . ($item->item_code ?? 'N/A') . ']') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sf-group" id="modalCategoryWrapper" style="display: none;">
                    <label>Target Item Category *</label>
                    <select name="target_category_id" id="modalTargetCategory" class="sf-input">
                        <option value="">Select Item Category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid-2 sf-group">
                    <div>
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="modalStartDate" class="sf-input">
                    </div>
                    <div>
                        <label>End Date</label>
                        <input type="date" name="end_date" id="modalEndDate" class="sf-input">
                    </div>
                </div>

                <div class="grid-2 sf-group">
                    <div>
                        <label>Max Discount Cap (Rs.)</label>
                        <input type="number" step="0.01" name="discount_cap" id="modalDiscountCap" placeholder="e.g. 5000" class="sf-input">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" id="modalStatus" class="sf-input">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Tiers Builder -->
                <div style="margin-top: 18px; border-top: 0.5px solid var(--c-separator); padding-top: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--t-secondary);">Discount Tiers & Thresholds</label>
                        <button type="button" onclick="addModalTierRow()" class="sf-btn neutral" style="padding: 4px 10px; font-size: 11px;">
                            <i class="fa-solid fa-plus"></i> Add Tier
                        </button>
                    </div>
                    <div id="modalTiersContainer" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

                <div class="sf-group" style="margin-top: 14px;">
                    <label>Remarks / Description</label>
                    <textarea name="description" id="modalDescription" rows="2" placeholder="Campaign notes..." class="sf-input"></textarea>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" onclick="closeRuleModal()" class="sf-btn neutral">Cancel</button>
                <button type="submit" class="sf-btn primary"><i class="fa-solid fa-floppy-disk"></i> Save Rule</button>
            </div>
        </form>
    </div>
</div>

<!-- SIMULATOR TEST MODAL -->
<div id="simulatorModalVeil" class="modal-veil hidden">
    <div class="sf-modal">
        <div class="modal-head">
            <h3 class="modal-title"><i class="fa-solid fa-flask text-amber-500"></i> Discount Engine Simulation</h3>
            <button type="button" class="modal-close" onclick="closeSimulatorModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-body">
            <p style="font-size: 13px; color: var(--t-secondary); margin-bottom: 14px;">Test order attributes against active rules to preview discount qualification.</p>

            <div class="grid-2 sf-group">
                <div>
                    <label>Bill Subtotal (Rs.)</label>
                    <input type="number" id="simSubtotal" placeholder="75000" class="sf-input">
                </div>
                <div>
                    <label>Item Quantity</label>
                    <input type="number" id="simQty" placeholder="12" class="sf-input">
                </div>
            </div>

            <div class="sf-group">
                <label>Target Product SKU</label>
                <select id="simItemId" class="sf-input">
                    <option value="">Select Item to Test...</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item->id ?>"><?= htmlspecialchars($item->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" onclick="runSimulation()" class="sf-btn primary" style="width: 100%; margin-top: 6px;">Run Test Simulation</button>

            <div id="simResults" class="hidden" style="margin-top: 16px; padding: 14px; background: var(--c-surface2); border-radius: var(--r-md); border: 0.5px solid var(--c-separator);">
                <div style="font-size: 12px; font-weight: 700; color: var(--t-primary); margin-bottom: 8px;">Simulation Output:</div>
                <div id="simResultsContent" style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;"></div>
            </div>
        </div>

        <div class="modal-foot">
            <button type="button" onclick="closeSimulatorModal()" class="sf-btn neutral">Close</button>
        </div>
    </div>
</div>

<script>
    const allRulesData = <?= json_encode($rules) ?>;

    function selectRuleType(val, label) {
        document.getElementById('filterRuleType').value = val;
        document.getElementById('type-dropdown-val').innerText = label;
        filterRules();
    }

    function selectRuleStatus(val, label) {
        document.getElementById('filterStatus').value = val;
        document.getElementById('status-dropdown-val').innerText = label;
        filterRules();
    }

    function clearAllFilters() {
        document.getElementById('filterRuleType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('cmdSearchInput').value = '';
        document.getElementById('type-dropdown-val').innerText = 'All Rule Types';
        document.getElementById('status-dropdown-val').innerText = 'All Statuses';
        filterRules();
    }

    function filterRules() {
        const q = document.getElementById('cmdSearchInput').value.toLowerCase().trim();
        const typeF = document.getElementById('filterRuleType').value;
        const statusF = document.getElementById('filterStatus').value;

        const rows = document.querySelectorAll('#rulesTable tbody tr.rule-row');
        let count = 0;

        rows.forEach(r => {
            const name = r.getAttribute('data-name') || '';
            const type = r.getAttribute('data-type') || '';
            const status = r.getAttribute('data-status') || '';
            const sku = r.getAttribute('data-sku') || '';
            const cat = r.getAttribute('data-category') || '';

            const matchQ = !q || name.includes(q) || sku.includes(q) || cat.includes(q);
            const matchType = !typeF || type === typeF;
            const matchStatus = !statusF || status === statusF;

            if (matchQ && matchType && matchStatus) {
                r.style.display = '';
                count++;
            } else {
                r.style.display = 'none';
            }
        });

        document.getElementById('matching-count').innerText = count;
    }

    function inspectRule(id) {
        const rule = allRulesData.find(r => parseInt(r.id) === parseInt(id));
        if (!rule) return;

        document.getElementById('qvRuleType').innerText = rule.rule_type.replace('_', ' ').toUpperCase();
        document.getElementById('qvRuleName').innerText = rule.name;
        document.getElementById('qvDescription').innerText = rule.description || 'No internal remarks configured.';
        document.getElementById('qvStatus').innerText = rule.status;

        const tiersList = document.getElementById('qvTiersList');
        tiersList.innerHTML = '';
        if (rule.tiers && rule.tiers.length > 0) {
            rule.tiers.forEach(t => {
                const item = document.createElement('div');
                item.style.cssText = 'padding:8px 10px; background:var(--c-surface); border-radius:var(--r-sm); border:0.5px solid var(--c-separator); font-size:12px; display:flex; justify-between; align-items:center;';
                const thresh = rule.rule_type === 'bill_wise' ? `Rs ${parseFloat(t.min_threshold).toLocaleString()}` : `Qty >= ${parseInt(t.min_threshold)}`;
                const reward = rule.reward_type === 'free_issue' ? `${parseInt(t.reward_val)} Free Units` : `${parseFloat(t.reward_val)}% Off`;
                item.innerHTML = `<span>${thresh}</span> <strong style="color:var(--c-blue);">${reward}</strong>`;
                tiersList.appendChild(item);
            });
        }

        document.getElementById('qvEditBtn').onclick = function() { openEditModal(rule.id); };
        document.getElementById('qvToggleBtn').href = '<?= APP_URL ?>/discount/toggle/' + rule.id;

        document.getElementById('qvPanel').classList.add('open');
    }

    function closeInspector() {
        document.getElementById('qvPanel').classList.remove('open');
    }

    // Modal Control Functions
    function openCreateModal() {
        document.getElementById('ruleModalTitle').innerText = 'Configure New Discount Rule';
        document.getElementById('ruleModalForm').action = '<?= APP_URL ?>/discount/add';
        document.getElementById('modalRuleId').value = '';
        document.getElementById('modalName').value = '';
        document.getElementById('modalRuleType').value = 'item_wise';
        document.getElementById('modalRewardType').value = 'free_issue';
        document.getElementById('modalTargetItem').value = '';
        document.getElementById('modalTargetCategory').value = '';
        document.getElementById('modalStartDate').value = '';
        document.getElementById('modalEndDate').value = '';
        document.getElementById('modalDiscountCap').value = '';
        document.getElementById('modalDescription').value = '';
        document.getElementById('modalStatus').value = 'Active';

        toggleModalFields();
        document.getElementById('modalTiersContainer').innerHTML = '';
        addModalTierRow();

        document.getElementById('ruleModalVeil').classList.remove('hidden');
    }

    function openEditModal(id) {
        fetch('<?= APP_URL ?>/discount/api_get_rule/' + id)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const rule = res.data;
                    document.getElementById('ruleModalTitle').innerText = 'Edit Discount Rule';
                    document.getElementById('ruleModalForm').action = '<?= APP_URL ?>/discount/update';
                    document.getElementById('modalRuleId').value = rule.id;
                    document.getElementById('modalName').value = rule.name;
                    document.getElementById('modalRuleType').value = rule.rule_type;
                    document.getElementById('modalRewardType').value = rule.reward_type || 'free_issue';
                    document.getElementById('modalTargetItem').value = rule.target_item_id || '';
                    document.getElementById('modalTargetCategory').value = rule.target_category_id || '';
                    document.getElementById('modalStartDate').value = rule.start_date || '';
                    document.getElementById('modalEndDate').value = rule.end_date || '';
                    document.getElementById('modalDiscountCap').value = rule.discount_cap || '';
                    document.getElementById('modalDescription').value = rule.description || '';
                    document.getElementById('modalStatus').value = rule.status || 'Active';

                    toggleModalFields();

                    const container = document.getElementById('modalTiersContainer');
                    container.innerHTML = '';
                    if (rule.tiers && rule.tiers.length > 0) {
                        rule.tiers.forEach(t => addModalTierRow(t.min_threshold, t.max_threshold || '', t.reward_val));
                    } else {
                        addModalTierRow();
                    }

                    document.getElementById('ruleModalVeil').classList.remove('hidden');
                }
            });
    }

    function closeRuleModal() {
        document.getElementById('ruleModalVeil').classList.add('hidden');
    }

    function toggleModalFields() {
        const type = document.getElementById('modalRuleType').value;
        document.getElementById('modalProductWrapper').style.display = (type === 'item_wise') ? 'block' : 'none';
        document.getElementById('modalCategoryWrapper').style.display = (type === 'category_wise') ? 'block' : 'none';
    }

    function addModalTierRow(minVal = '', maxVal = '', rewardVal = '') {
        const type = document.getElementById('modalRuleType').value;
        const container = document.getElementById('modalTiersContainer');
        const row = document.createElement('div');
        row.style.cssText = 'display:flex; gap:8px; align-items:center; background:var(--c-surface2); padding:8px; border-radius:var(--r-sm); border:0.5px solid var(--c-separator);';

        if (type === 'item_wise' || type === 'category_wise') {
            row.innerHTML = `
                <input type="number" min="1" name="min_threshold[]" value="${minVal}" required placeholder="Min Qty" class="sf-input" style="flex:1;">
                <input type="number" min="0.01" step="0.01" name="reward_val[]" value="${rewardVal}" required placeholder="Reward" class="sf-input" style="flex:1;">
                <button type="button" onclick="this.closest('div').remove()" style="color:var(--c-red); border:none; background:none; cursor:pointer; padding:4px;"><i class="fa-solid fa-xmark"></i></button>
            `;
        } else {
            row.innerHTML = `
                <input type="number" min="0.01" step="0.01" name="min_threshold[]" value="${minVal}" required placeholder="Min Amt" class="sf-input" style="flex:2;">
                <input type="number" min="0.01" step="0.01" name="max_threshold[]" value="${maxVal}" placeholder="Max Amt" class="sf-input" style="flex:2;">
                <input type="number" min="0.01" step="0.01" name="reward_val[]" value="${rewardVal}" required placeholder="% Off" class="sf-input" style="flex:1.5;">
                <button type="button" onclick="this.closest('div').remove()" style="color:var(--c-red); border:none; background:none; cursor:pointer; padding:4px;"><i class="fa-solid fa-xmark"></i></button>
            `;
        }
        container.appendChild(row);
    }

    // Simulator Modal
    function openSimulatorModal() {
        document.getElementById('simulatorModalVeil').classList.remove('hidden');
    }
    function closeSimulatorModal() {
        document.getElementById('simulatorModalVeil').classList.add('hidden');
    }

    function runSimulation() {
        const subtotal = document.getElementById('simSubtotal').value;
        const qty = document.getElementById('simQty').value;
        const itemId = document.getElementById('simItemId').value;

        fetch('<?= APP_URL ?>/discount/api_test_rule', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ bill_subtotal: subtotal, item_qty: qty, item_id: itemId })
        })
        .then(res => res.json())
        .then(data => {
            const box = document.getElementById('simResults');
            const content = document.getElementById('simResultsContent');
            box.classList.remove('hidden');

            if (data.status === 'success' && data.matched_count > 0) {
                let html = '';
                data.matched_rules.forEach(r => {
                    html += `<div style="padding:8px 10px; background:var(--c-green-light); border-radius:var(--r-xs); color:var(--c-green); font-weight:600;">✅ ${r.rule_name} &mdash; ${r.reward}</div>`;
                });
                content.innerHTML = html;
            } else {
                content.innerHTML = `<div style="color:var(--t-secondary); italic">No active rules matched the criteria.</div>`;
            }
        });
    }
</script>
