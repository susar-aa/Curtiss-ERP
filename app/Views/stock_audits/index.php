<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — STOCK AUDITS
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

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', sans-serif;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-pill: 999px;

    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
}

.audit-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0px 24px 140px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.audit-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.audit-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 4px;
}
.audit-title {
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
.audit-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.audit-table th {
    background: var(--c-surface2);
    padding: 14px 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--t-secondary);
    border-bottom: 0.5px solid var(--c-separator);
}
.audit-table td {
    padding: 16px 20px;
    font-size: 14px;
    color: var(--t-primary);
    border-bottom: 0.5px solid var(--c-separator2);
}
.audit-table tr:last-child td {
    border-bottom: none;
}
.audit-table tr:hover td {
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
.badge-draft { background: var(--c-fill2); color: var(--t-secondary); }
.badge-progress { background: var(--c-blue-light); color: var(--c-blue); }
.badge-completed { background: var(--c-purple-light); color: var(--c-purple); }
.badge-approved { background: var(--c-green-light); color: var(--c-green); }
.badge-cancelled { background: var(--c-red-light); color: var(--c-red); }

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

<div class="audit-wrap">
    <!-- Header -->
    <div class="audit-header">
        <div>
            <div class="audit-eyebrow">Operations</div>
            <div class="audit-title">Stock Audits</div>
        </div>
        <a href="<?= APP_URL ?>/stockaudit/create" class="btn-submit" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> New Audit
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

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="<?= APP_URL ?>/stockaudit">
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
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="Draft" <?= ($data['filters']['status'] === 'Draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="In Progress" <?= ($data['filters']['status'] === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?= ($data['filters']['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="Approved" <?= ($data['filters']['status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="Cancelled" <?= ($data['filters']['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
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
                <a href="<?= APP_URL ?>/stockaudit" class="btn-reset">Reset</a>
                <button type="submit" class="btn-submit">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Audit Number</th>
                    <th>Warehouse</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Counted By</th>
                    <th>Approver</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="auditTableBody">
                <?php if (empty($data['audits'])): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--t-secondary); padding: 40px;">
                            No stock audits found. Click "New Audit" to start one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['audits'] as $audit): ?>
                        <tr class="audit-row" data-number="<?= htmlspecialchars($audit->audit_number); ?>" data-warehouse="<?= htmlspecialchars($audit->warehouse_name); ?>">
                            <td style="font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($audit->audit_number); ?></td>
                            <td><?= htmlspecialchars($audit->warehouse_name); ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($audit->created_at)); ?></td>
                            <td>
                                <?php if ($audit->status === 'Draft'): ?>
                                    <span class="badge badge-draft">Draft</span>
                                <?php elseif ($audit->status === 'In Progress'): ?>
                                    <span class="badge badge-progress">In Progress</span>
                                <?php elseif ($audit->status === 'Completed'): ?>
                                    <span class="badge badge-completed">Completed</span>
                                <?php elseif ($audit->status === 'Approved'): ?>
                                    <span class="badge badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-cancelled">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($audit->creator_name ?? 'System'); ?></td>
                            <td><?= htmlspecialchars($audit->counter_name ?? '-'); ?></td>
                            <td><?= htmlspecialchars($audit->approver_name ?? '-'); ?></td>
                            <td style="text-align: right;">
                                <?php if (in_array($audit->status, ['Draft', 'In Progress'])): ?>
                                    <a href="<?= APP_URL ?>/stockaudit/wizard/<?= $audit->id; ?>" class="btn-submit" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Resume Count</a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/stockaudit/show/<?= $audit->id; ?>" class="btn-reset" style="padding: 6px 12px; font-size: 12px; text-decoration: none; background: var(--c-fill2);">View Details</a>
                                <?php endif; ?>
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
    <a href="<?= APP_URL ?>/stockaudit" class="cmd-icon" title="Clear Filters"><i class="fa-solid fa-filter-circle-xmark"></i></a>
    <a href="<?= APP_URL ?>/stockadjustment" class="cmd-icon" title="Manual Adjustments"><i class="fa-solid fa-sliders"></i></a>
    <a href="<?= APP_URL ?>/stockaudit/create" class="cmd-cta"><i class="fa-solid fa-plus"></i> New Audit</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('cmdSearchInput');
    const tableRows = document.querySelectorAll('.audit-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = searchInput.value.toLowerCase().trim();
            
            tableRows.forEach(row => {
                const num = row.getAttribute('data-number').toLowerCase();
                const wh = row.getAttribute('data-warehouse').toLowerCase();
                
                if (num.includes(term) || wh.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
