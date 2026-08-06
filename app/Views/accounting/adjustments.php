<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — BALANCE ADJUSTMENTS
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
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);

    --r-xs: 6px;
    --r-sm: 10px;
    --r-md: 14px;
    --r-xl: 26px;

    --dur-fast:    0.18s;
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

.adjust-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.table-panel, .form-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    position: relative;
    margin-bottom: 25px;
    padding: 24px;
}
.table-panel { padding: 0; }

.cust-table { width: 100%; border-collapse: collapse; }
.cust-table thead th {
    padding: 13px 18px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--t-label);
    background: var(--c-surface2);
    border-bottom: 0.5px solid var(--c-separator);
    text-align: left;
}
.cust-table tbody tr {
    transition: background var(--dur-fast);
    border-bottom: 0.5px solid var(--c-separator2);
}
.cust-table tbody tr:hover { background: var(--c-fill2); }
.cust-table td {
    padding: 14px 18px;
    font-size: 14px;
    color: var(--t-primary);
    vertical-align: middle;
}

.sf-group { margin-bottom: 16px; }
.sf-group label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: var(--t-secondary); text-transform: uppercase; }
.sf-input {
    width: 100%; padding: 10px 14px;
    border-radius: var(--r-sm); border: 0.5px solid var(--c-separator);
    background: var(--c-surface2); color: var(--t-primary);
    font-size: 14px; outline: none; transition: border-color var(--dur-fast);
    box-sizing: border-box;
}
.sf-input:focus { border-color: var(--c-blue); background: var(--c-surface); }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

.sf-btn {
    padding: 10px 18px;
    border-radius: var(--r-md);
    font-size: 14px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 8px;
    border: 0.5px solid transparent; cursor: pointer;
    transition: transform var(--dur-fast), filter var(--dur-fast);
    text-decoration: none;
}
.sf-btn:active { transform: scale(0.97); }
.sf-btn.primary { background: var(--c-blue); color: #fff; }
.sf-btn.neutral { background: var(--c-surface); border-color: var(--c-separator); color: var(--t-primary); box-shadow: var(--shadow-sm); }
.sf-btn.neutral:hover { background: var(--c-surface2); }

.sf-alert {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--c-surface);
    border-radius: var(--r-md);
    padding: 14px 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
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

.badge-inc { background: var(--c-green-light); color: var(--c-green); padding: 4px 8px; border-radius: var(--r-xs); font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge-dec { background: var(--c-red-light); color: var(--c-red); padding: 4px 8px; border-radius: var(--r-xs); font-size: 11px; font-weight: 700; text-transform: uppercase; }
</style>

<div class="adjust-root">
    
    <?php if (!empty($data['error'])): ?>
        <div class="sf-alert error">
            <i class="fa-solid fa-circle-exclamation sf-alert-icon"></i>
            <div><div class="sf-alert-title">Error</div><div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div></div>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['success'])): ?>
        <div class="sf-alert success">
            <i class="fa-solid fa-circle-check sf-alert-icon"></i>
            <div><div class="sf-alert-title">Success</div><div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div></div>
        </div>
    <?php endif; ?>

    <div class="form-panel">
        <h3 style="margin-top:0; font-weight: 700; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-sliders" style="color: var(--c-blue);"></i> New Balance Adjustment</h3>
        <p style="color: var(--t-secondary); font-size: 13px; margin-bottom: 20px;">Use this form to manually adjust account balances. A balancing double-entry journal will automatically be posted to the "Balance Adjustment" account.</p>
        
        <form action="<?= APP_URL ?>/accounting/adjustments" method="POST">
            <input type="hidden" name="action" value="adjust">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="grid-3">
                <div class="sf-group">
                    <label>Ledger Account</label>
                    <select name="account_id" class="sf-input" required>
                        <option value="">-- Select Account --</option>
                        <?php foreach($data['accounts'] as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?> (<?= htmlspecialchars($acc->account_type) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sf-group">
                    <label>Adjustment Type</label>
                    <select name="adjustment_type" class="sf-input" required>
                        <option value="Increase">Increase Balance</option>
                        <option value="Decrease">Decrease Balance</option>
                    </select>
                </div>
                <div class="sf-group">
                    <label>Amount (Rs)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="sf-input" required>
                </div>
            </div>
            <div class="grid-2">
                <div class="sf-group">
                    <label>Date</label>
                    <input type="date" name="adjustment_date" class="sf-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="sf-group">
                    <label>Reason / Notes</label>
                    <input type="text" name="reason" class="sf-input" placeholder="Explain why this adjustment is needed" required>
                </div>
            </div>
            <div style="text-align: right; margin-top: 10px;">
                <button type="submit" class="sf-btn primary" onclick="return confirm('Are you sure you want to post this adjustment? This action cannot be easily undone without a reversing journal entry.')"><i class="fa-solid fa-check"></i> Post Adjustment</button>
            </div>
        </form>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
        <h3 style="margin:0; font-weight: 700; color: var(--t-primary);">Adjustment History</h3>
        <div>
            <a href="<?= APP_URL ?>/accounting/adjustments?export=true&start_date=<?= urlencode($data['start_date']) ?>&end_date=<?= urlencode($data['end_date']) ?>" class="sf-btn neutral btn-small"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
        </div>
    </div>

    <div class="form-panel" style="padding: 15px 24px; margin-bottom: 15px;">
        <form action="<?= APP_URL ?>/accounting/adjustments" method="GET" style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap;">
            <div class="sf-group" style="margin-bottom:0; width: 180px;">
                <label>From Date</label>
                <input type="date" name="start_date" class="sf-input" value="<?= htmlspecialchars($data['start_date']) ?>">
            </div>
            <div class="sf-group" style="margin-bottom:0; width: 180px;">
                <label>To Date</label>
                <input type="date" name="end_date" class="sf-input" value="<?= htmlspecialchars($data['end_date']) ?>">
            </div>
            <div class="sf-group" style="margin-bottom:0; width: 250px;">
                <label>Search Memo</label>
                <input type="text" name="search" class="sf-input" placeholder="..." value="<?= htmlspecialchars($data['search']) ?>">
            </div>
            <div>
                <button type="submit" class="sf-btn neutral">Filter</button>
            </div>
        </form>
    </div>

    <div class="table-panel">
        <table class="cust-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th style="text-align:right;">Prev Balance</th>
                    <th style="text-align:right;">Adjustment</th>
                    <th style="text-align:right;">New Balance</th>
                    <th>Reason</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['history'])): ?>
                    <tr><td colspan="8" style="text-align:center; padding: 30px; color: var(--t-secondary);">No adjustments found.</td></tr>
                <?php else: foreach($data['history'] as $h): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= date('Y-m-d', strtotime($h->adjustment_date)) ?></td>
                        <td><span style="font-weight:600;"><?= htmlspecialchars($h->account_code) ?></span> - <?= htmlspecialchars($h->account_name) ?></td>
                        <td>
                            <?php if($h->adjustment_type == 'Increase'): ?>
                                <span class="badge-inc"><i class="fa-solid fa-arrow-trend-up"></i> Inc</span>
                            <?php else: ?>
                                <span class="badge-dec"><i class="fa-solid fa-arrow-trend-down"></i> Dec</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; font-family:var(--f-mono);">Rs. <?= number_format($h->previous_balance, 2) ?></td>
                        <td style="text-align:right; font-family:var(--f-mono); font-weight:700; color: <?= $h->adjustment_type == 'Increase' ? 'var(--c-green)' : 'var(--c-red)' ?>;">
                            <?= $h->adjustment_type == 'Increase' ? '+' : '-' ?>Rs. <?= number_format($h->amount, 2) ?>
                        </td>
                        <td style="text-align:right; font-family:var(--f-mono); font-weight:600;">Rs. <?= number_format($h->new_balance, 2) ?></td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($h->reason) ?>"><?= htmlspecialchars($h->reason) ?></td>
                        <td><?= htmlspecialchars($h->username) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
