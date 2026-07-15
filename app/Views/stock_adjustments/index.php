<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — STOCK ADJUSTMENTS
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
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14);

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-pill: 999px;

    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
}

.adj-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0px 24px 140px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.adj-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.adj-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 4px;
}
.adj-title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

/* ---- Flash Messages ---- */
.flash-msg {
    padding: 14px 20px;
    border-radius: var(--r-md);
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}
.flash-msg-success { background: var(--c-green-light); color: #1e7e34; border: 0.5px solid rgba(52,199,89,0.3); }
.flash-msg-error { background: var(--c-red-light); color: #bd2130; border: 0.5px solid rgba(255,59,48,0.3); }

/* ---- Filter Bar ---- */
.filter-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    margin-bottom: 24px;
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}
.filter-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.filter-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--t-secondary);
}
.filter-select, .filter-input {
    background: var(--c-fill);
    border: 0.5px solid transparent;
    border-radius: var(--r-sm);
    padding: 10px 14px;
    font-size: 14px;
    font-family: var(--f-system);
    color: var(--t-primary);
    outline: none;
    transition: all var(--dur-fast);
}
.filter-select:focus, .filter-input:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
}
.filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 16px;
}
.btn-reset {
    background: var(--c-fill);
    border: none;
    color: var(--t-primary);
    padding: 10px 18px;
    border-radius: var(--r-pill);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}
.btn-submit {
    background: var(--c-blue);
    border: none;
    color: #fff;
    padding: 10px 22px;
    border-radius: var(--r-pill);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}

/* ---- Table Styling ---- */
.table-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}
.adj-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.adj-table th {
    background: var(--c-surface2);
    padding: 14px 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--t-secondary);
    border-bottom: 0.5px solid var(--c-separator);
}
.adj-table td {
    padding: 16px 20px;
    font-size: 14px;
    color: var(--t-primary);
    border-bottom: 0.5px solid var(--c-separator2);
}
.adj-table tr:last-child td {
    border-bottom: none;
}
.adj-table tr:hover td {
    background: var(--c-surface2);
}

/* ---- Badges ---- */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: var(--r-pill);
    font-size: 12px;
    font-weight: 600;
}
.badge-pending { background: var(--c-orange-light); color: var(--c-orange); }
.badge-approved { background: var(--c-green-light); color: var(--c-green); }
.badge-rejected { background: var(--c-red-light); color: var(--c-red); }

/* ---- Command Bar ---- */
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
    width: 196px;
    transition: width var(--dur-fast) var(--ease-ios), background var(--dur-fast);
}
.cmd-search:focus-within {
    width: 300px;
    background: rgba(255,255,255,0.18);
}
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; }
.cmd-search input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 14px; font-weight: 500;
    width: 100%;
}
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-icon {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.8); font-size: 15px;
    background: transparent; border: none; cursor: pointer; text-decoration: none;
}
.cmd-icon:hover { background: rgba(255,255,255,0.12); color: #fff; }
.cmd-cta {
    display: flex; align-items: center; gap: 7px;
    background: #fff; color: #1c1c1e;
    border: none; border-radius: var(--r-pill);
    padding: 0 18px; height: 38px;
    font-size: 14px; font-weight: 700;
    cursor: pointer; text-decoration: none;
    margin-left: 2px;
}
.cmd-cta:hover { background: #e5e5ea; }
</style>

<div class="adj-wrap">
    <!-- Header -->
    <div class="adj-header">
        <div>
            <div class="adj-eyebrow">Operations</div>
            <div class="adj-title">Stock Adjustments</div>
        </div>
        <a href="<?= APP_URL ?>/stockadjustment/create" class="btn-submit" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> Create Adjustment
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash-msg flash-msg-success">
            <i class="fa-solid fa-circle-check"></i>
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-msg flash-msg-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Filters Card -->
    <div class="filter-card">
        <form method="GET" action="<?= APP_URL ?>/stockadjustment">
            <div class="filter-grid">
                <div class="filter-field">
                    <label class="filter-label">Warehouse</label>
                    <select name="warehouse_id" class="filter-select">
                        <option value="">All Warehouses</option>
                        <?php foreach ($data['warehouses'] as $wh): ?>
                            <option value="<?= $wh->id; ?>" <?= ($data['filters']['warehouse_id'] == $wh->id) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($wh->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Reason</label>
                    <select name="reason" class="filter-select">
                        <option value="">All Reasons</option>
                        <option value="Damage" <?= ($data['filters']['reason'] === 'Damage') ? 'selected' : ''; ?>>Damage</option>
                        <option value="Theft" <?= ($data['filters']['reason'] === 'Theft') ? 'selected' : ''; ?>>Theft</option>
                        <option value="Inventory Write-off" <?= ($data['filters']['reason'] === 'Inventory Write-off') ? 'selected' : ''; ?>>Inventory Write-off</option>
                        <option value="Found Item" <?= ($data['filters']['reason'] === 'Found Item') ? 'selected' : ''; ?>>Found Item</option>
                        <option value="Stock Audit Variance" <?= ($data['filters']['reason'] === 'Stock Audit Variance') ? 'selected' : ''; ?>>Stock Audit Variance</option>
                        <option value="General Adjustment" <?= ($data['filters']['reason'] === 'General Adjustment') ? 'selected' : ''; ?>>General Adjustment</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?= ($data['filters']['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?= ($data['filters']['status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?= ($data['filters']['status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Start Date</label>
                    <input type="date" name="start_date" class="filter-input" value="<?= htmlspecialchars($data['filters']['start_date']); ?>">
                </div>
                <div class="filter-field">
                    <label class="filter-label">End Date</label>
                    <input type="date" name="end_date" class="filter-input" value="<?= htmlspecialchars($data['filters']['end_date']); ?>">
                </div>
            </div>
            <div class="filter-actions">
                <a href="<?= APP_URL ?>/stockadjustment" class="btn-reset">Reset</a>
                <button type="submit" class="btn-submit">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <table class="adj-table">
            <thead>
                <tr>
                    <th>Adjustment No.</th>
                    <th>Warehouse</th>
                    <th>Date</th>
                    <th>Reason / Type</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Journal Reference</th>
                    <th>Link Source</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="adjTableBody">
                <?php if (empty($data['adjustments'])): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--t-secondary); padding: 40px;">
                            No stock adjustments found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['adjustments'] as $adj): ?>
                        <tr class="adj-row" data-number="<?= htmlspecialchars($adj->adjustment_number); ?>" data-reason="<?= htmlspecialchars($adj->reason); ?>">
                            <td style="font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($adj->adjustment_number); ?></td>
                            <td><?= htmlspecialchars($adj->warehouse_name); ?></td>
                            <td><?= date('Y-m-d', strtotime($adj->adjustment_date)); ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($adj->reason); ?></td>
                            <td>
                                <?php if ($adj->status === 'Pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php elseif ($adj->status === 'Approved'): ?>
                                    <span class="badge badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($adj->creator_name ?? 'System'); ?></td>
                            <td style="font-family: var(--f-mono); font-size: 13px;">
                                <?= $adj->journal_reference ? htmlspecialchars($adj->journal_reference) : '-'; ?>
                            </td>
                            <td>
                                <?php if ($adj->stock_audit_id): ?>
                                    <a href="<?= APP_URL ?>/stockaudit/show/<?= $adj->stock_audit_id; ?>" style="color: var(--c-blue); text-decoration: none; font-weight: 500;">
                                        <i class="fa-solid fa-file-invoice"></i> <?= htmlspecialchars($adj->audit_number); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--t-secondary);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= APP_URL ?>/stockadjustment/show/<?= $adj->id; ?>" class="btn-reset" style="padding: 6px 12px; font-size: 12px; text-decoration: none; background: var(--c-fill2);">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Floating bottom Command Bar -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="cmdSearchInput" placeholder="Quick Search...">
    </div>
    <div class="cmd-divider"></div>
    <a href="<?= APP_URL ?>/stockadjustment" class="cmd-icon" title="Clear Filters"><i class="fa-solid fa-filter-circle-xmark"></i></a>
    <a href="<?= APP_URL ?>/stockaudit" class="cmd-icon" title="Stock Audits"><i class="fa-solid fa-file-invoice"></i></a>
    <a href="<?= APP_URL ?>/stockadjustment/create" class="cmd-cta"><i class="fa-solid fa-plus"></i> Add Adjustment</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('cmdSearchInput');
    const tableRows = document.querySelectorAll('.adj-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = searchInput.value.toLowerCase().trim();
            
            tableRows.forEach(row => {
                const num = row.getAttribute('data-number').toLowerCase();
                const reason = row.getAttribute('data-reason').toLowerCase();
                
                if (num.includes(term) || reason.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
