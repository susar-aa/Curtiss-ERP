<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — SUPPLIER RETURNS
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
    margin-bottom: 28px;
}
.inv-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-purple);
    margin-bottom: 6px;
}
.inv-title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--t-primary);
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
    gap: 8px;
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
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: var(--t-label);
    text-transform: uppercase;
}
.pg-size-sel {
    font-family: var(--f-system); font-size: 13px; font-weight: 600;
    color: var(--t-primary);
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 5px 9px;
    outline: none; cursor: pointer;
    transition: border-color var(--dur-fast);
}
.pg-size-sel:hover { border-color: var(--c-purple); }
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

/* ---- Row Actions ---- */
.row-acts { display: flex; gap: 6px; justify-content: flex-end; }
.act-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--c-fill);
    color: var(--t-label);
    border: none; cursor: pointer; text-decoration: none;
    font-size: 13px;
    transition: all var(--dur-fast) var(--ease-spring);
}
.act-btn:hover { transform: scale(1.12); }
.act-btn.view:hover   { background: var(--c-purple-light); color: var(--c-purple); }

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
    z-index: 1000;
}
.cmd-search {
    display: flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.1);
    border-radius: var(--r-pill);
    padding: 8px 14px;
    width: 250px;
    transition: width var(--dur-slow) var(--ease-ios),
                background var(--dur-mid);
}
.cmd-search:focus-within {
    width: 380px;
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
.cmd-cta {
    display: flex; align-items: center; gap: 7px;
    background: #fff; color: #1c1c1e;
    border: none; border-radius: var(--r-pill);
    padding: 0 18px; height: 38px;
    font-size: 14px; font-weight: 700;
    font-family: var(--f-system);
    cursor: pointer; text-decoration: none;
    transition: transform var(--dur-fast) var(--ease-spring),
                background var(--dur-fast);
    margin-left: 2px;
}
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

</style>

<div class="inv-wrap">

    <div class="inv-header">
        <div class="inv-eyebrow">Supplier Returns</div>
        <h1 class="inv-title">Return Note History</h1>
    </div>

    <!-- Alert Messaging -->
    <?php if(!empty($data['error'])): ?>
    <div class="sf-alert error">
        <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Operation Error</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($data['success'])): ?>
    <div class="sf-alert success">
        <i class="fa-solid fa-circle-check sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Success</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Shelf -->
    <div class="filter-shelf">
        <div class="filter-chip">
            <span class="filter-chip-label">Supplier:</span>
            <select id="filterVendor" class="pg-size-sel" onchange="triggerSearch()" style="border:none; background:transparent; font-weight:600; padding:0; outline:none; font-size:13px; color:var(--t-primary); cursor:pointer;">
                <option value="">All Suppliers</option>
                <?php foreach($data['vendors'] as $ven): ?>
                    <option value="<?= $ven->id ?>" <?= ($data['filters']['vendor_id'] == $ven->id) ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button class="filter-reset" onclick="clearFilters()">Clear Filters</button>
        
        <div class="filter-count">
            Showing <strong><?= count($data['returns']) ?></strong> Return Notes
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-panel">
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Return Note Number</th>
                    <th style="width: 35%;">Supplier / Vendor</th>
                    <th style="width: 15%;">Created By</th>
                    <th style="text-align: right; width: 15%;">Total Value</th>
                    <th style="text-align: center; width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if(empty($data['returns'])): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--t-label); padding: 32px; font-style: italic;">
                        No Supplier Returns found.
                    </td>
                </tr>
                <?php else: foreach($data['returns'] as $ret): ?>
                <tr>
                    <td>
                        <strong style="font-family: var(--f-mono); font-size:14px;"><?= htmlspecialchars($ret->return_number) ?></strong><br>
                        <span style="font-size: 11px; color: var(--t-secondary); margin-top: 2px; display:inline-block;">
                            Date: <?= date('M d, Y', strtotime($ret->return_date)) ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: var(--c-purple); font-weight: 700;"><?= htmlspecialchars($ret->vendor_name) ?></span>
                    </td>
                    <td>
                        <span style="font-size: 12px; font-weight: 500; color: var(--t-secondary);">
                            <i class="fa-solid fa-user-astronaut"></i> <?= htmlspecialchars($ret->creator_name) ?>
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 700; font-family: var(--f-mono); font-size: 14px;">
                        Rs: <?= number_format($ret->total_amount, 2) ?>
                    </td>
                    <td style="text-align: center;">
                        <div class="row-acts" style="justify-content: center;">
                            <a href="<?= APP_URL ?>/supplier-return/show/<?= $ret->id ?>" class="act-btn view" title="View/Print Note">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Command Bar (Dynamic Island style) -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search Return Notes, Supplier..." value="<?= htmlspecialchars($data['search']) ?>">
    </div>
    <div class="cmd-divider"></div>
    <a href="<?= APP_URL ?>/supplier-return/create" class="cmd-cta">
        <i class="fa-solid fa-plus"></i> Create Return Note
    </a>
</div>

<script>
    let searchTimeout = null;
    document.getElementById('searchInput').addEventListener('input', triggerSearchDelay);
    function triggerSearchDelay() { clearTimeout(searchTimeout); searchTimeout = setTimeout(triggerSearch, 400); }

    function triggerSearch() {
        const query = encodeURIComponent(document.getElementById('searchInput').value);
        const venId = encodeURIComponent(document.getElementById('filterVendor').value);
        const url = `?search=${query}&vendor_id=${venId}&page=1`;
        
        fetch(url).then(response => response.text()).then(html => {
            const parser = new DOMParser(); const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('tableBody').innerHTML = doc.getElementById('tableBody').innerHTML;
            window.history.pushState({}, '', url);
        });
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterVendor').value = '';
        triggerSearch();
    }
</script>
