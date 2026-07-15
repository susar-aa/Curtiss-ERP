<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — PROCUREMENT & PURCHASE ORDERS
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
    padding: 24px 24px 140px;
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

/* ---- Quick Links ---- */
.quick-links {
    display: flex;
    gap: 8px;
    margin-bottom: 28px;
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
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    text-decoration: none;
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
.pg-size-sel:hover { border-color: var(--c-blue); }
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

/* ---- Badges ---- */
.sf-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: var(--r-pill);
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.01em;
    white-space: nowrap;
}
.sf-badge .dot {
    width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0;
}
.badge-active  { background: var(--c-green-light);  color: #1a7f3c; }
.badge-active  .dot { background: var(--c-green); }
.badge-low     { background: var(--c-orange-light); color: #c05d00; }
.badge-low     .dot { background: var(--c-orange); }
.badge-grey    { background: var(--c-fill);          color: var(--t-secondary); }
.badge-grey    .dot { background: var(--t-label); }

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
.act-btn.view:hover   { background: var(--c-blue-light);   color: var(--c-blue); }
.act-btn.edit:hover   { background: var(--c-purple-light); color: var(--c-purple); }
.act-btn.trash:hover  { background: var(--c-red-light);    color: var(--c-red); }

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

/* ---- Pagination ---- */
.pagination {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    padding: 16px 20px;
    background: var(--c-surface2);
    border-top: 0.5px solid var(--c-separator);
}
.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border-radius: var(--r-sm);
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    text-decoration: none;
    background: var(--c-fill);
    transition: all var(--dur-fast);
}
.page-btn:hover {
    background: var(--c-fill2);
    color: var(--t-primary);
}
.page-btn.active {
    background: var(--c-blue);
    color: #fff;
}
</style>

<div class="inv-wrap">
    <!-- Header -->
    <div class="inv-header">
        <div class="inv-eyebrow">Procurement</div>
        <h1 class="inv-title">Procurement & Purchase Orders</h1>
    </div>

    <!-- Quick Navigation Links -->
    <div class="quick-links">
        <span class="quick-links-label">Supply Chain:</span>
        <a href="<?= APP_URL ?>/supplier" class="btn-quick">🏢 Suppliers</a>
        <a href="<?= APP_URL ?>/purchase" class="btn-quick active">🛒 Purchase Orders</a>
        <a href="<?= APP_URL ?>/grn" class="btn-quick">📦 Goods Receipts (GRN)</a>
        <a href="<?= APP_URL ?>/inventory" class="btn-quick">🗄️ Inventory</a>
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

        <div class="filter-chip">
            <span class="filter-chip-label">Status:</span>
            <select id="filterStatus" class="pg-size-sel" onchange="triggerSearch()" style="border:none; background:transparent; font-weight:600; padding:0; outline:none; font-size:13px; color:var(--t-primary); cursor:pointer;">
                <option value="">All Statuses</option>
                <option value="Draft" <?= ($data['filters']['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                <option value="Sent" <?= ($data['filters']['status'] == 'Sent') ? 'selected' : '' ?>>Sent</option>
                <option value="Received" <?= ($data['filters']['status'] == 'Received') ? 'selected' : '' ?>>Received / Closed</option>
            </select>
        </div>
        
        <button class="filter-reset" onclick="clearFilters()">Clear Filters</button>
        
        <div class="filter-count">
            Showing <strong><?= count($data['pos']) ?></strong> PO records
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-panel">
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width: 25%;">PO Number & Date</th>
                    <th style="width: 30%;">Supplier / Vendor</th>
                    <th style="width: 15%;">Status</th>
                    <th style="text-align: right; width: 15%;">Total Amount</th>
                    <th style="text-align: center; width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if(empty($data['pos'])): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--t-label); padding: 32px; font-style: italic;">
                        No Purchase Orders found.
                    </td>
                </tr>
                <?php else: foreach($data['pos'] as $po): ?>
                <tr>
                    <td>
                        <strong style="font-family: var(--f-mono); font-size:14px;"><?= htmlspecialchars($po->po_number) ?></strong><br>
                        <span style="font-size: 11px; color: var(--t-secondary); margin-top: 2px; display:inline-block;">
                            <?= date('M d, Y', strtotime($po->po_date)) ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: var(--c-blue); font-weight: 700;"><?= htmlspecialchars($po->vendor_name) ?></span><br>
                        <span style="font-size: 11px; color: var(--t-secondary); margin-top: 3px; display:inline-block;">Created by: <?= htmlspecialchars($po->creator_name) ?></span>
                    </td>
                    <td>
                        <?php if($po->status === 'Received'): ?>
                            <span class="sf-badge badge-active"><span class="dot"></span>Received</span>
                        <?php elseif($po->status === 'Sent'): ?>
                            <span class="sf-badge badge-low"><span class="dot"></span>Sent</span>
                        <?php else: ?>
                            <span class="sf-badge badge-grey"><span class="dot"></span>Draft</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; font-weight: 700; font-family: var(--f-mono); font-size: 14px;">
                        Rs: <?= number_format($po->total_amount, 2) ?>
                    </td>
                    <td style="text-align: center;">
                        <div class="row-acts" style="justify-content: center;">
                            <a href="<?= APP_URL ?>/purchase/show/<?= $po->id ?>" class="act-btn view" title="Print/PDF" target="_blank">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            
                            <?php if($po->status !== 'Received'): ?>
                                <a href="<?= APP_URL ?>/purchase/edit/<?= $po->id ?>" class="act-btn edit" title="Edit PO">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                
                                <?php if($po->has_mix): ?>
                                    <a href="<?= APP_URL ?>/purchase/resolve_mix_grn/<?= $po->id ?>" class="act-btn view" style="background:var(--c-green-light); color:var(--c-green);" title="Receive GRN (Resolve Variations)">
                                        <i class="fa-solid fa-boxes-packing"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/grn/create?po_id=<?= $po->id ?>" class="act-btn view" style="background:var(--c-green-light); color:var(--c-green);" title="Receive GRN">
                                        <i class="fa-solid fa-boxes-packing"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Email Dispatch -->
                            <?php 
                                $btnColor = 'var(--c-blue)'; 
                                if ($po->status === 'Sent') {
                                    $btnColor = 'var(--c-green)'; 
                                } elseif ($po->status === 'Received') {
                                    $btnColor = 'var(--t-label)';
                                }
                            ?>
                            <?php if(!empty($po->email)): ?>
                            <form action="<?= APP_URL ?>/purchase/email_po" method="POST" style="display:inline;" id="email-form-<?= $po->id ?>">
                                <input type="hidden" name="po_id" value="<?= $po->id ?>">
                                <button type="button" class="act-btn edit" style="background:var(--c-blue-light); color:<?= $btnColor ?>;" onclick="if(confirm('Dispatch this Purchase Order via email to the supplier?')) document.getElementById('email-form-<?= $po->id ?>').submit();" title="Send email to <?= htmlspecialchars($po->email) ?>">
                                    <i class="fa-solid fa-envelope"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <form action="<?= APP_URL ?>/purchase" method="POST" style="display:inline;" id="del-form-<?= $po->id ?>">
                                <input type="hidden" name="action" value="delete_po">
                                <input type="hidden" name="po_id" value="<?= $po->id ?>">
                                <button type="button" class="act-btn trash" onclick="if(confirm('Delete this PO permanently?')) document.getElementById('del-form-<?= $po->id ?>').submit();" title="Delete Purchase Order">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <div class="pagination" id="paginationContainer">
            <?php if($data['total_pages'] > 1): ?>
                <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                    <?php $params = http_build_query(['search' => $data['search'], 'vendor_id' => $data['filters']['vendor_id'], 'status' => $data['filters']['status'], 'page' => $i]); ?>
                    <a href="?<?= $params ?>" class="page-btn <?= ($data['page'] == $i) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Command Bar (Dynamic Island style) -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search PO, Supplier..." value="<?= htmlspecialchars($data['search']) ?>">
    </div>
    <div class="cmd-divider"></div>
    <a href="<?= APP_URL ?>/purchase/wizard" class="cmd-cta">
        <i class="fa-solid fa-plus"></i> Create Purchase Order
    </a>
</div>

<script>
    let searchTimeout = null;
    document.getElementById('searchInput').addEventListener('input', triggerSearchDelay);
    function triggerSearchDelay() { clearTimeout(searchTimeout); searchTimeout = setTimeout(triggerSearch, 400); }

    function triggerSearch() {
        const query = encodeURIComponent(document.getElementById('searchInput').value);
        const venId = encodeURIComponent(document.getElementById('filterVendor').value);
        const status = encodeURIComponent(document.getElementById('filterStatus').value);
        const url = `?search=${query}&vendor_id=${venId}&status=${status}&page=1`;
        
        fetch(url).then(response => response.text()).then(html => {
            const parser = new DOMParser(); const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('tableBody').innerHTML = doc.getElementById('tableBody').innerHTML;
            document.getElementById('paginationContainer').innerHTML = doc.getElementById('paginationContainer').innerHTML;
            window.history.pushState({}, '', url);
        });
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterVendor').value = '';
        document.getElementById('filterStatus').value = '';
        triggerSearch();
    }
</script>