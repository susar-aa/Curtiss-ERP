<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SUPPLIER RETURNS PANEL SYSTEM — DESIGN SYSTEM
   ============================================================ */
:root {
    --qb-bg:           #f8fafc;
    --qb-surface:      #ffffff;
    --qb-surface-2:    #f1f5f9;
    --qb-primary:      #c62828;
    --qb-primary-dark: #b71c1c;
    --qb-primary-light:#ffebee;
    
    --qb-slate-50:     #f8fafc;
    --qb-slate-100:    #f1f5f9;
    --qb-slate-200:    #e2e8f0;
    --qb-slate-300:    #cbd5e1;
    --qb-slate-400:    #94a3b8;
    --qb-slate-500:    #64748b;
    --qb-slate-600:    #475569;
    --qb-slate-700:    #334155;
    --qb-slate-800:    #1e293b;
    --qb-slate-900:    #0f172a;

    --qb-emerald-50:   #ecfdf5;
    --qb-emerald-500:  #10b981;
    --qb-emerald-600:  #059669;
    --qb-emerald-700:  #047857;

    --qb-amber-50:     #fffbeb;
    --qb-amber-500:    #f59e0b;
    --qb-amber-600:    #d97706;

    --qb-rose-50:      #fff1f2;
    --qb-rose-500:     #f43f5e;
    --qb-rose-600:     #e11d48;

    --qb-font:         'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --qb-mono:         ui-monospace, "SF Mono", "Cascadia Code", "Roboto Mono", Menlo, monospace;
    --qb-radius:       8px;
    --qb-radius-lg:    12px;
    --qb-shadow-sm:    0 1px 2px 0 rgb(0 0 0 / 0.05);
    --qb-shadow:       0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    --qb-shadow-md:    0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --qb-shadow-xl:    0 20px 25px -5px rgb(0 0 0 / 0.15), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

* { box-sizing: border-box; }

.qb-wrapper {
    font-family: var(--qb-font);
    background-color: var(--qb-bg);
    color: var(--qb-slate-800);
    padding: 12px;
    min-height: calc(100vh - 70px);
    display: flex;
    flex-direction: column;
}

.qb-container {
    max-width: 1540px;
    margin: 0 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 12px;
}

/* ---- Top Header Bar ---- */
.qb-header {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--qb-shadow-sm);
}

.qb-title-group {
    display: flex;
    align-items: center;
    gap: 14px;
}

.qb-title-icon {
    width: 40px;
    height: 40px;
    background: var(--qb-primary-light);
    color: var(--qb-primary);
    border-radius: var(--qb-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.qb-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--qb-slate-900);
    margin: 0 0 2px 0;
    letter-spacing: -0.01em;
}

.qb-subtitle {
    font-size: 12px;
    color: var(--qb-slate-500);
    margin: 0;
}

.qb-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ---- Buttons ---- */
.qb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 14px;
    font-size: 12.5px;
    font-weight: 600;
    border-radius: var(--qb-radius);
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    font-family: var(--qb-font);
}

.qb-btn-ghost {
    background: transparent;
    color: var(--qb-slate-600);
    border-color: var(--qb-slate-200);
}
.qb-btn-ghost:hover {
    background: var(--qb-slate-100);
    color: var(--qb-slate-900);
}

.qb-btn-primary {
    background: var(--qb-primary);
    color: #ffffff;
    box-shadow: 0 1px 2px 0 rgba(198, 40, 40, 0.3);
}
.qb-btn-primary:hover {
    background: var(--qb-primary-dark);
}

/* ---- Filter Bar ---- */
.qb-filter-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 14px 16px;
    box-shadow: var(--qb-shadow-sm);
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.qb-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.qb-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--qb-slate-600);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.qb-input {
    width: 100%;
    padding: 8px 12px;
    font-size: 12.5px;
    font-family: var(--qb-font);
    color: var(--qb-slate-900);
    background: #ffffff;
    border: 1px solid var(--qb-slate-300);
    border-radius: var(--qb-radius);
    outline: none;
    transition: all 0.15s ease;
}

.qb-input:focus {
    border-color: var(--qb-primary);
    box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.15);
}

/* ---- Data Table ---- */
.qb-table-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    box-shadow: var(--qb-shadow-sm);
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.qb-table-scroll {
    overflow-x: auto;
    overflow-y: auto;
    flex: 1;
}

.qb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
    text-align: left;
}

.qb-table th {
    background: var(--qb-slate-50);
    color: var(--qb-slate-600);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 10px 16px;
    border-bottom: 1px solid var(--qb-slate-200);
    position: sticky;
    top: 0;
    z-index: 10;
}

.qb-table td {
    padding: 10px 16px;
    border-bottom: 1px solid var(--qb-slate-100);
    vertical-align: middle;
}

.qb-table tbody tr:hover {
    background: var(--qb-slate-50);
}

.qb-badge-doc {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: var(--qb-mono);
    font-weight: 700;
    color: var(--qb-primary);
    text-decoration: none;
}
.qb-badge-doc:hover {
    text-decoration: underline;
}

/* ---- Pagination ---- */
.qb-pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 12px;
    border-top: 1px solid var(--qb-slate-100);
    background: var(--qb-surface);
}

.qb-page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    border-radius: var(--qb-radius);
    border: 1px solid var(--qb-slate-200);
    background: #ffffff;
    color: var(--qb-slate-600);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
}

.qb-page-link:hover {
    background: var(--qb-slate-100);
    color: var(--qb-slate-900);
}

.qb-page-link.active {
    background: var(--qb-primary);
    color: #ffffff;
    border-color: var(--qb-primary);
}

.qb-empty-state {
    padding: 60px 20px;
    text-align: center;
    color: var(--qb-slate-400);
}
.qb-empty-state i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.6;
}
</style>

<div class="qb-wrapper">
    <div class="qb-container">
        
        <!-- ═══ HEADER BAR ═══ -->
        <div class="qb-header">
            <div class="qb-title-group">
                <div class="qb-title-icon">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <div>
                    <h1 class="qb-title">Supplier Goods Returns</h1>
                    <p class="qb-subtitle">Manage and track inventory returned to vendors (Debit Notes)</p>
                </div>
            </div>

            <div class="qb-header-actions">
                <a href="<?= APP_URL ?>/supplier-return/create" class="qb-btn qb-btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Return Note
                </a>
            </div>
        </div>

        <?php if(!empty($data['success'])): ?>
        <div style="background: var(--qb-emerald-50); border: 1px solid #a7f3d0; color: var(--qb-emerald-700); padding: 10px 14px; border-radius: var(--qb-radius); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($data['success']) ?>
        </div>
        <?php endif; ?>

        <!-- ═══ FILTER BAR ═══ -->
        <form method="GET" action="<?= APP_URL ?>/supplier-return" class="qb-filter-card">
            <div class="qb-field-group" style="flex: 2;">
                <label class="qb-label">Search Return</label>
                <input type="text" name="search" class="qb-input" placeholder="Search by Return Number or Supplier Name..." value="<?= htmlspecialchars($data['search']) ?>">
            </div>
            
            <div class="qb-field-group">
                <label class="qb-label">Filter by Supplier</label>
                <select name="vendor_id" class="qb-input">
                    <option value="">All Suppliers</option>
                    <?php foreach($data['vendors'] as $v): ?>
                        <option value="<?= $v->id ?>" <?= ($data['filters']['vendor_id'] == $v->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="qb-header-actions" style="margin-bottom: 2px;">
                <button type="submit" class="qb-btn qb-btn-primary" style="padding: 9px 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>
                <a href="<?= APP_URL ?>/supplier-return" class="qb-btn qb-btn-ghost" style="padding: 9px 16px;">
                    Reset
                </a>
            </div>
        </form>

        <!-- ═══ DATA TABLE ═══ -->
        <div class="qb-table-card">
            <div class="qb-table-scroll">
                <table class="qb-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Return Note #</th>
                            <th>Supplier / Vendor</th>
                            <th style="width: 140px;">Return Date</th>
                            <th>Created By</th>
                            <th style="text-align: right; width: 160px;">Total Return Value</th>
                            <th style="text-align: center; width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['returns'])): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="qb-empty-state">
                                        <i class="fa-solid fa-box-open"></i>
                                        <h3 style="font-size:15px; color:var(--qb-slate-700); margin:0 0 4px 0;">No supplier returns found</h3>
                                        <p style="font-size:13px; margin:0;">Create a new goods return note to deduct stock and claim credit.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($data['returns'] as $ret): ?>
                                <tr>
                                    <td>
                                        <a href="<?= APP_URL ?>/supplier-return/show/<?= $ret->id ?>" class="qb-badge-doc">
                                            <i class="fa-solid fa-file-invoice"></i>
                                            <?= htmlspecialchars($ret->return_number) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--qb-slate-900);">
                                            <?= htmlspecialchars($ret->vendor_name) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; color: var(--qb-slate-600);">
                                            <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>
                                            <?= date('M d, Y', strtotime($ret->return_date)) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:inline-flex; align-items:center; gap:6px; font-size:12px; background:var(--qb-slate-100); padding:3px 8px; border-radius:12px; font-weight:500;">
                                            <i class="fa-solid fa-user-astronaut" style="color:var(--qb-slate-500);"></i>
                                            <?= htmlspecialchars($ret->creator_name) ?>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="font-family: var(--qb-mono); font-weight: 700; color: var(--qb-slate-800);">
                                            Rs. <?= number_format($ret->total_amount, 2) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?= APP_URL ?>/supplier-return/show/<?= $ret->id ?>" class="qb-btn qb-btn-ghost" style="padding: 4px 8px; font-size:11.5px;">
                                            <i class="fa-solid fa-arrow-right"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($data['total_pages'] > 1): ?>
            <div class="qb-pagination">
                <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                    <a href="<?= APP_URL ?>/supplier-return?page=<?= $i ?>&search=<?= urlencode($data['search']) ?>&vendor_id=<?= $data['filters']['vendor_id'] ?>" 
                       class="qb-page-link <?= $data['page'] == $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
