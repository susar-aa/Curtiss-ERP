<?php
// Journal Entry View Redesigned
// Dynamic Stats
$totalEntries = $data['total_entries'] ?? 0;
$postedCount = 0;
$voidedCount = 0;
$draftCount = 0;
foreach ($data['entries'] ?? [] as $entry) {
    if ($entry->status === 'Posted') $postedCount++;
    if ($entry->status === 'Voided') $voidedCount++;
    if ($entry->status === 'Draft') $draftCount++;
}
$search = $data['filters']['search'] ?? '';
$status = $data['filters']['status'] ?? 'All';
$startDate = $data['filters']['start_date'] ?? '';
$endDate = $data['filters']['end_date'] ?? '';
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE - GENERAL JOURNAL
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

.cust-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.cust-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 16px 24px 100px;
}

/* ---- Stat Cards ---- */
.stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform var(--dur-fast), box-shadow var(--dur-fast);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2.5px; border-radius: var(--r-xl) var(--r-xl) 0 0; }
.stat-card.blue::before  { background: var(--c-blue); }
.stat-card.orange::before { background: var(--c-orange); }
.stat-card.red::before   { background: var(--c-red); }
.stat-card.green::before { background: var(--c-green); }

.stat-icon { width: 46px; height: 46px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.stat-card.blue .stat-icon { background: var(--c-blue-light); color: var(--c-blue); }
.stat-card.orange .stat-icon { background: var(--c-orange-light); color: var(--c-orange); }
.stat-card.red .stat-icon { background: var(--c-red-light); color: var(--c-red); }
.stat-card.green .stat-icon { background: var(--c-green-light); color: var(--c-green); }

.stat-info { display: flex; flex-direction: column; justify-content: center; }
.stat-num { font-size: 22px; font-weight: 700; letter-spacing: -0.04em; color: var(--t-primary); line-height: 1.1; margin-bottom: 2px; }
.stat-lbl { font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--t-label); }

/* ---- Filter Shelf ---- */
.filter-shelf { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 20px; }
.filter-shelf form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; width: 100%; }
.filter-input, .filter-select {
    background: var(--c-surface); border: 0.5px solid var(--c-separator); border-radius: var(--r-pill);
    padding: 7px 14px; font-size: 13px; font-weight: 500; color: var(--t-primary); box-shadow: var(--shadow-xs);
    outline: none; transition: border-color var(--dur-fast), box-shadow var(--dur-fast);
}
.filter-input:focus, .filter-select:focus { border-color: var(--c-blue); box-shadow: 0 0 0 3px rgba(0,122,255,0.12); }
.filter-btn {
    background: var(--c-blue); color: #fff; border: none; border-radius: var(--r-pill);
    padding: 7px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform var(--dur-fast), filter var(--dur-fast);
}
.filter-btn:hover { filter: brightness(1.1); }
.filter-btn:active { transform: scale(0.96); }
.filter-reset {
    background: transparent; border: 0.5px solid var(--c-separator); border-radius: var(--r-pill);
    padding: 7px 14px; font-size: 13px; font-weight: 600; color: var(--t-secondary); cursor: pointer; text-decoration: none;
}
.filter-reset:hover { background: var(--c-fill); color: var(--t-primary); }

/* ---- Table Panel ---- */
.table-panel { background: var(--c-surface); border-radius: var(--r-xl); border: 0.5px solid var(--c-separator); box-shadow: var(--shadow-sm); overflow: hidden; position: relative; margin-bottom: 30px;}
.cust-table { width: 100%; border-collapse: collapse; }
.cust-table thead th { padding: 13px 18px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--t-label); background: var(--c-surface2); border-bottom: 0.5px solid var(--c-separator); white-space: nowrap; text-align: left; }
.cust-table tbody tr { transition: background var(--dur-fast); border-bottom: 0.5px solid var(--c-separator2); }
.cust-table tbody tr:last-child { border-bottom: none; }
.cust-table tbody tr:hover { background: var(--c-fill2); }
.cust-table td { padding: 14px 18px; font-size: 14px; color: var(--t-primary); vertical-align: middle; }

/* ---- Create Entry Panel ---- */
.create-panel { background: var(--c-surface); border-radius: var(--r-xl); border: 0.5px solid var(--c-separator); box-shadow: var(--shadow-md); padding: 20px 24px; margin-bottom: 30px; display: none; }
.create-panel.active { display: block; animation: slideDown 0.3s var(--ease-spring); }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.create-panel h3 { margin-top: 0; margin-bottom: 16px; font-size: 18px; font-weight: 700; color: var(--t-primary); }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--t-secondary); }
.form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--c-separator); border-radius: var(--r-sm); background: var(--c-surface2); color: var(--t-primary); font-size: 14px; outline: none; transition: border-color 0.2s; box-sizing: border-box;}
.form-control:focus { border-color: var(--c-blue); background: var(--c-surface); }
.journal-input-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.journal-input-table th { padding: 10px; font-size: 12px; font-weight: 600; color: var(--t-label); text-transform: uppercase; border-bottom: 1px solid var(--c-separator); text-align: left; }
.journal-input-table td { padding: 8px; border-bottom: 1px solid var(--c-separator2); }
.journal-input-table input[type="number"] { text-align: right; }
.totals-row { font-weight: 700; background: var(--c-surface2); }
.totals-row td { padding: 12px 8px !important; }
.diff-warning { color: var(--c-red); font-size: 13px; font-weight: 600; display: none; }

/* ---- Badges & Alerts ---- */
.sf-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: var(--r-xs); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; }
.sf-badge.badge-posted { background: var(--c-green-light); color: var(--c-green); }
.sf-badge.badge-voided { background: var(--c-red-light); color: var(--c-red); text-decoration: line-through; }
.sf-badge.badge-draft { background: var(--c-orange-light); color: var(--c-orange); }

.sf-alert { display: flex; align-items: flex-start; gap: 12px; background: var(--c-surface); border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 20px; box-shadow: var(--shadow-xs); border: 0.5px solid var(--c-separator); border-left-width: 3.5px; font-size: 14px; }
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error .sf-alert-icon { color: var(--c-red); }

/* ---- Buttons ---- */
.sf-btn { padding: 9px 16px; border-radius: var(--r-md); font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: 0.5px solid transparent; cursor: pointer; transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast); text-decoration: none; }
.sf-btn:active { transform: scale(0.97); }
.sf-btn.primary { background: var(--c-blue); color: #fff; }
.sf-btn.neutral { background: var(--c-surface); border-color: var(--c-separator); color: var(--t-primary); box-shadow: var(--shadow-xs); }
.sf-btn.neutral:hover { background: var(--c-surface2); }
.sf-btn.danger { background: var(--c-red-light); color: var(--c-red); border-color: transparent;}
.sf-btn.danger:hover { background: var(--c-red); color: #fff;}

/* ---- Command Bar ---- */
.cmd-bar { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); background: rgba(28, 28, 30, 0.92); backdrop-filter: saturate(180%) blur(28px); -webkit-backdrop-filter: saturate(180%) blur(28px); border: 0.5px solid rgba(255,255,255,0.12); border-radius: var(--r-pill); padding: 7px 10px; display: flex; align-items: center; gap: 4px; box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3); z-index: 100; }
.cmd-cta { display: flex; align-items: center; gap: 7px; background: #fff; color: #1c1c1e; border: none; border-radius: var(--r-pill); padding: 8px 16px; font-size: 14px; font-weight: 700; font-family: var(--f-system); cursor: pointer; transition: transform var(--dur-fast) var(--ease-spring), opacity var(--dur-fast); }
.cmd-cta:hover { opacity: 0.9; }
.cmd-cta:active { transform: scale(0.95); }

/* Pagination */
.pagination { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 0.5px solid var(--c-separator); font-size: 13px; color: var(--t-secondary); }
.page-links { display: flex; gap: 5px; }
.page-link { padding: 6px 12px; background: var(--c-surface); border: 0.5px solid var(--c-separator); border-radius: var(--r-md); color: var(--t-primary); text-decoration: none; font-weight: 500; transition: background var(--dur-fast); }
.page-link:hover { background: var(--c-fill); }
.page-link.active { background: var(--c-blue); color: #fff; border-color: var(--c-blue); }

/* Journal Details Modal */
.journal-modal-veil {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.4); backdrop-filter: blur(8px);
    display: none; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.2s ease;
}
.journal-modal-veil.open { display: flex; opacity: 1; }
.journal-modal-box {
    background: var(--c-surface); width: 680px; max-width: 90vw; max-height: 85vh;
    border-radius: var(--r-xl); border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl); display: flex; flex-direction: column; overflow: hidden;
    transform: scale(0.95); transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.journal-modal-veil.open .journal-modal-box { transform: scale(1); }
</style>

<div class="cust-root">
    <div class="cust-wrap">
        
        <?php if(!empty($data['error'])): ?>
            <div class="sf-alert error">
                <i class="fa-solid fa-circle-exclamation sf-alert-icon"></i>
                <div>
                    <div class="sf-alert-title">Error</div>
                    <div class="sf-alert-msg"><?= $data['error'] ?></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(!empty($data['success'])): ?>
            <div class="sf-alert success">
                <i class="fa-solid fa-circle-check sf-alert-icon"></i>
                <div>
                    <div class="sf-alert-title">Success</div>
                    <div class="sf-alert-msg"><?= $data['success'] ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-book-journal-whills"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($totalEntries) ?></div>
                    <div class="stat-lbl">Total Entries</div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fa-solid fa-check-double"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($postedCount) ?></div>
                    <div class="stat-lbl">Posted Entries</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($voidedCount) ?></div>
                    <div class="stat-lbl">Voided Entries</div>
                </div>
            </div>
        </div>

        <!-- Filter Shelf -->
        <div class="filter-shelf">
            <form action="<?= APP_URL ?>/accounting/journal" method="GET">
                <input type="text" name="search" class="filter-input" placeholder="Search ref or description..." value="<?= htmlspecialchars($search) ?>" style="width: 200px;">
                <select name="status" class="filter-select">
                    <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Posted" <?= $status === 'Posted' ? 'selected' : '' ?>>Posted</option>
                    <option value="Voided" <?= $status === 'Voided' ? 'selected' : '' ?>>Voided</option>
                    <option value="Draft" <?= $status === 'Draft' ? 'selected' : '' ?>>Draft</option>
                </select>
                <input type="date" name="start_date" class="filter-input" value="<?= htmlspecialchars($startDate) ?>" title="Start Date">
                <span style="color: var(--t-tertiary); font-weight: 500;">to</span>
                <input type="date" name="end_date" class="filter-input" value="<?= htmlspecialchars($endDate) ?>" title="End Date">
                
                <button type="submit" class="filter-btn"><i class="fa-solid fa-filter"></i> Apply</button>
                <a href="<?= APP_URL ?>/accounting/journal" class="filter-reset">Reset</a>
            </form>
        </div>

        <!-- Create Journal Entry Panel (Hidden by default) -->
        <div class="create-panel" id="createPanel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3>New Journal Entry</h3>
                <button type="button" class="sf-btn neutral" onclick="toggleCreatePanel()"><i class="fa-solid fa-xmark"></i> Close</button>
            </div>
            <form action="<?= APP_URL ?>/accounting/journal" method="POST" id="journalForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="form-group" style="max-width: 550px; background: var(--c-surface2); padding: 12px; border-radius: var(--r-md); border: 0.5px solid var(--c-separator); margin-bottom: 20px;">
                    <label>Load Journal Template</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select id="templateSelector" class="form-control" onchange="loadTemplate(this.value)" style="flex: 1; background: var(--c-surface);">
                            <option value="">-- Select Template --</option>
                            <option value="rent">Record Rent Expense (Debit Rent, Credit Bank)</option>
                            <option value="utility">Record Utility Bill (Debit Utilities, Credit Bank)</option>
                            <option value="revenue">Record Customer Payment (Debit Bank, Credit AR)</option>
                            <option value="payroll">Record Payroll (Debit Salaries, Credit Bank)</option>
                        </select>
                        <button type="button" id="undoTemplateBtn" class="sf-btn neutral" style="display:none;" onclick="undoTemplateLoad()">Undo</button>
                    </div>
                </div>
                
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Date</label>
                        <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Reference #</label>
                        <input type="text" name="reference" class="form-control" placeholder="e.g. INV-1001">
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Memo for this journal entry..." required>
                    </div>
                </div>

                <table class="journal-input-table" id="linesTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Account</th>
                            <th style="width: 35%;">Description / Memo</th>
                            <th style="width: 13%;">Debit (Rs)</th>
                            <th style="width: 13%;">Credit (Rs)</th>
                            <th style="width: 4%;"></th>
                        </tr>
                    </thead>
                    <tbody id="journalBody">
                        <!-- Initial 2 lines -->
                        <tr>
                            <td>
                                <select name="account_id[]" class="form-control" required>
                                    <option value="">Select Account...</option>
                                    <?php foreach($data['accounts'] as $acc): ?>
                                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note"></td>
                            <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                            <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <select name="account_id[]" class="form-control" required>
                                    <option value="">Select Account...</option>
                                    <?php foreach($data['accounts'] as $acc): ?>
                                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note"></td>
                            <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                            <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <button type="button" class="sf-btn neutral" onclick="addRow()"><i class="fa-solid fa-plus"></i> Add Line</button>
                            </td>
                            <td class="totals-row" id="totalDebit">0.00</td>
                            <td class="totals-row" id="totalCredit">0.00</td>
                            <td class="totals-row"></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="diff-warning" id="diffWarning"><i class="fa-solid fa-circle-exclamation"></i> Debits and Credits must balance!</span>
                    <button type="submit" class="sf-btn primary" id="btnSubmit" style="margin-left: auto;">Post Journal Entry</button>
                </div>
            </form>
        </div>

        <!-- Main Data Table -->
        <div class="table-panel">
            <table class="cust-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Posted By</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['entries'])): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--t-tertiary);">No entries found matching criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach($data['entries'] as $entry): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= date('M d, Y', strtotime($entry->entry_date)) ?></td>
                            <td><strong><?= htmlspecialchars($entry->reference) ?></strong></td>
                            <td style="color: var(--t-secondary);"><?= htmlspecialchars($entry->description) ?></td>
                            <td>
                                <?php if($entry->status === 'Posted'): ?>
                                    <span class="sf-badge badge-posted"><span class="dot"></span><?= $entry->status ?></span>
                                <?php elseif($entry->status === 'Voided'): ?>
                                    <span class="sf-badge badge-voided"><span class="dot"></span><?= $entry->status ?></span>
                                <?php else: ?>
                                    <span class="sf-badge badge-draft"><span class="dot"></span><?= $entry->status ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($entry->username) ?></td>
                            <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px; align-items: center;">
                                <button type="button" class="sf-btn neutral" onclick="viewJournal(<?= $entry->id ?>)" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-eye"></i> View</button>
                                <?php if($entry->status === 'Posted' && !$entry->is_closed): ?>
                                    <form action="<?= APP_URL ?>/accounting/void_journal" method="POST" onsubmit="return confirm('Are you sure you want to void this journal entry? This will reverse all ledger balances for these accounts.')" style="display:inline; margin: 0;">
                                        <input type="hidden" name="entry_id" value="<?= $entry->id ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="sf-btn danger" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-ban"></i> Void</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--t-tertiary); font-size: 13px; padding-right: 6px;">Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($data['total_pages'] > 1): 
                // Build query string for pagination links to preserve filters
                $qArgs = [];
                if (!empty($search)) $qArgs['search'] = $search;
                if ($status !== 'All') $qArgs['status'] = $status;
                if (!empty($startDate)) $qArgs['start_date'] = $startDate;
                if (!empty($endDate)) $qArgs['end_date'] = $endDate;
                $qString = http_build_query($qArgs);
                if (!empty($qString)) $qString = '&' . $qString;
            ?>
            <div class="pagination">
                <div>Showing page <?= $data['page'] ?> of <?= $data['total_pages'] ?> (<?= $data['total_entries'] ?> total entries)</div>
                <div class="page-links">
                    <?php if ($data['page'] > 1): ?>
                        <a href="?page=<?= $data['page'] - 1 ?><?= $qString ?>" class="page-link">&laquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php 
                    $start = max(1, $data['page'] - 2);
                    $end = min($data['total_pages'], $data['page'] + 2);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <a href="?page=<?= $i ?><?= $qString ?>" class="page-link <?= $i === $data['page'] ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($data['page'] < $data['total_pages']): ?>
                        <a href="?page=<?= $data['page'] + 1 ?><?= $qString ?>" class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Journal Details Modal -->
<div id="journalDetailsModal" class="journal-modal-veil" onclick="if(event.target === this) closeJournalModal()">
    <div class="journal-modal-box">
        <div class="modal-head" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 0.5px solid var(--c-separator); background: var(--c-surface2);">
            <h3 class="modal-title" style="margin: 0; font-size: 16px; font-weight: 700;">Journal Entry Details</h3>
            <button type="button" class="modal-close" onclick="closeJournalModal()" style="background: var(--c-fill); border: none; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="padding: 24px; overflow-y: auto; flex: 1;">
            <!-- Loader -->
            <div id="modalLoader" style="display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 10px; padding: 40px 0;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 32px; color: var(--c-blue);"></i>
                <span style="font-size: 14px; color: var(--t-secondary);">Loading journal details...</span>
            </div>
            <!-- Error message -->
            <div id="modalError" style="display: none; text-align: center; color: var(--c-red); padding: 20px 0;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 36px; margin-bottom: 10px;"></i>
                <p id="modalErrorMsg" style="margin: 0; font-weight: 500;"></p>
            </div>
            <!-- Details Content -->
            <div id="modalContent" style="display: none;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px; background: var(--c-surface2); padding: 16px; border-radius: var(--r-md); border: 0.5px solid var(--c-separator);">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--t-label); font-weight: 600; margin-bottom: 4px;">Entry Date</div>
                        <div id="detDate" style="font-weight: 600; color: var(--t-primary);"></div>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--t-label); font-weight: 600; margin-bottom: 4px;">Reference #</div>
                        <div id="detRef" style="font-weight: 700; color: var(--c-blue);"></div>
                    </div>
                    <div style="grid-column: span 2;">
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--t-label); font-weight: 600; margin-bottom: 4px;">Description / Memo</div>
                        <div id="detDesc" style="color: var(--t-primary); font-size: 14px; line-height: 1.4;"></div>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--t-label); font-weight: 600; margin-bottom: 4px;">Posted By</div>
                        <div id="detUser" style="color: var(--t-secondary);"></div>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--t-label); font-weight: 600; margin-bottom: 4px;">Status</div>
                        <div id="detStatus"></div>
                    </div>
                </div>

                <h4 style="margin: 0 0 12px 0; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--t-label);">Transactions / Double-Entry Lines</h4>
                <div style="border: 0.5px solid var(--c-separator); border-radius: var(--r-md); overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                        <thead>
                            <tr style="background: var(--c-surface2); border-bottom: 0.5px solid var(--c-separator);">
                                <th style="padding: 10px 14px; font-weight: 600; color: var(--t-secondary);">Account</th>
                                <th style="padding: 10px 14px; font-weight: 600; color: var(--t-secondary);">Memo</th>
                                <th style="padding: 10px 14px; font-weight: 600; color: var(--t-secondary); text-align: right; width: 110px;">Debit (Rs)</th>
                                <th style="padding: 10px 14px; font-weight: 600; color: var(--t-secondary); text-align: right; width: 110px;">Credit (Rs)</th>
                            </tr>
                        </thead>
                        <tbody id="detLinesBody">
                            <!-- Lines will be dynamically added here -->
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: 700; background: var(--c-surface2); border-top: 0.5px solid var(--c-separator);">
                                <td colspan="2" style="padding: 12px 14px; text-align: right; color: var(--t-secondary);">Total</td>
                                <td id="detTotalDebit" style="padding: 12px 14px; text-align: right; color: var(--t-primary);">0.00</td>
                                <td id="detTotalCredit" style="padding: 12px 14px; text-align: right; color: var(--t-primary);">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-foot" style="padding: 16px 24px; border-top: 0.5px solid var(--c-separator); background: var(--c-surface2); display: flex; justify-content: flex-end;">
            <button type="button" class="sf-btn neutral" onclick="closeJournalModal()">Close</button>
        </div>
    </div>
</div>

<!-- Command Bar -->
<div class="cmd-bar">
    <button class="cmd-cta" onclick="toggleCreatePanel()">
        <i class="fa-solid fa-plus"></i> New Journal Entry
    </button>
</div>

<script>
    function toggleCreatePanel() {
        const panel = document.getElementById('createPanel');
        if (panel.classList.contains('active')) {
            panel.classList.remove('active');
            setTimeout(() => { panel.style.display = 'none'; }, 300);
        } else {
            panel.style.display = 'block';
            setTimeout(() => { panel.classList.add('active'); }, 10);
            window.scrollTo({ top: panel.offsetTop - 20, behavior: 'smooth' });
        }
    }

    const accountOptions = `
        <option value="">Select Account...</option>
        <?php foreach($data['accounts'] as $acc): ?>
            <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
        <?php endforeach; ?>
    `;

    let previousState = null;

    function addRow() {
        const tbody = document.getElementById('journalBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="account_id[]" class="form-control" required>${accountOptions}</select></td>
            <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note"></td>
            <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" onchange="calcTotals()"></td>
            <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" onchange="calcTotals()"></td>
            <td><button type="button" class="sf-btn danger" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calcTotals();
    }

    function calcTotals() {
        let debits = 0;
        let credits = 0;
        
        document.querySelectorAll('.debit-input').forEach(input => {
            debits += parseFloat(input.value) || 0;
        });
        
        document.querySelectorAll('.credit-input').forEach(input => {
            credits += parseFloat(input.value) || 0;
        });

        document.getElementById('totalDebit').innerText = debits.toFixed(2);
        document.getElementById('totalCredit').innerText = credits.toFixed(2);

        const btnSubmit = document.getElementById('btnSubmit');
        const warning = document.getElementById('diffWarning');
        const linesCount = document.querySelectorAll('#journalBody tr').length;

        if (linesCount === 0) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Error: The journal entry is empty. Please add at least 2 lines.';
            warning.style.display = 'inline-block';
        } else if (linesCount < 2) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Error: A journal entry must have at least 2 lines to balance.';
            warning.style.display = 'inline-block';
        } else if (Math.abs(debits - credits) > 0.001) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Error: Debits and Credits must balance!';
            warning.style.display = 'inline-block';
        } else if (debits === 0) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Error: Total Debit and Credit amounts must be greater than zero.';
            warning.style.display = 'inline-block';
        } else {
            btnSubmit.disabled = false;
            btnSubmit.style.opacity = '1';
            warning.style.display = 'none';
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function captureState() {
        const tbody = document.getElementById('journalBody');
        const rows = tbody.querySelectorAll('tr');
        const lines = [];
        rows.forEach(row => {
            const select = row.querySelector('select');
            const descInput = row.querySelector('input[placeholder*="note"]');
            const debitInput = row.querySelector('.debit-input');
            const creditInput = row.querySelector('.credit-input');
            lines.push({
                account_id: select ? select.value : '',
                line_description: descInput ? descInput.value : '',
                debit: debitInput ? debitInput.value : '',
                credit: creditInput ? creditInput.value : ''
            });
        });

        previousState = {
            entry_date: document.querySelector('input[name="entry_date"]').value,
            reference: document.querySelector('input[name="reference"]').value,
            description: document.querySelector('input[name="description"]').value,
            lines: lines
        };
    }

    function undoTemplateLoad() {
        if (!previousState) return;

        document.querySelector('input[name="entry_date"]').value = previousState.entry_date;
        document.querySelector('input[name="reference"]').value = previousState.reference;
        document.querySelector('input[name="description"]').value = previousState.description;

        const tbody = document.getElementById('journalBody');
        tbody.innerHTML = '';
        
        previousState.lines.forEach((line, index) => {
            const tr = document.createElement('tr');
            const isInitial = index < 2; 
            tr.innerHTML = `
                <td><select name="account_id[]" class="form-control" required>${accountOptions}</select></td>
                <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note" value="${escapeHtml(line.line_description)}"></td>
                <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" value="${line.debit}" onchange="calcTotals()"></td>
                <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" value="${line.credit}" onchange="calcTotals()"></td>
                <td>${isInitial ? '' : '<button type="button" class="sf-btn danger" onclick="removeRow(this)"><i class="fa-solid fa-trash"></i></button>'}</td>
            `;
            tbody.appendChild(tr);
            tr.querySelector('select').value = line.account_id;
        });

        previousState = null;
        document.getElementById('undoTemplateBtn').style.display = 'none';
        calcTotals();
    }

    function loadTemplate(type) {
        if (!type) return;

        let hasData = false;
        const tbody = document.getElementById('journalBody');
        const selects = tbody.querySelectorAll('select');
        const debits = tbody.querySelectorAll('.debit-input');
        const credits = tbody.querySelectorAll('.credit-input');
        const descInput = document.querySelector('input[name="description"]');
        const refInput = document.querySelector('input[name="reference"]');
        
        if (descInput.value || refInput.value) hasData = true;
        selects.forEach(sel => { if (sel.value) hasData = true; });
        debits.forEach(deb => { if (deb.value && parseFloat(deb.value) > 0) hasData = true; });
        credits.forEach(cred => { if (cred.value && parseFloat(cred.value) > 0) hasData = true; });

        if (hasData) {
            if (!confirm("Loading this template will clear your current journal lines. Proceed?")) {
                document.getElementById('templateSelector').value = '';
                return;
            }
        }

        captureState();
        document.getElementById('undoTemplateBtn').style.display = 'inline-block';

        tbody.innerHTML = '';
        addRow();
        addRow();

        const newSelects = tbody.querySelectorAll('select');
        const newDebits = tbody.querySelectorAll('.debit-input');

        const options1 = Array.from(newSelects[0].options);
        const options2 = Array.from(newSelects[1].options);

        let memo = "";
        let match1 = null;
        let match2 = null;

        if (type === 'rent') {
            memo = "Rent Expense for current month";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('rent'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
        } else if (type === 'utility') {
            memo = "Utility Bill payment";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('utilit') || opt.text.toLowerCase().includes('electric') || opt.text.toLowerCase().includes('water'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
        } else if (type === 'revenue') {
            memo = "Record customer payment receipt";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('receivable') || opt.text.toLowerCase().includes('debtor'));
        } else if (type === 'payroll') {
            memo = "Monthly salary disbursement";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('salari') || opt.text.toLowerCase().includes('salary') || opt.text.toLowerCase().includes('wage') || opt.text.toLowerCase().includes('payroll'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
        }

        if (match1) newSelects[0].value = match1.value;
        if (match2) newSelects[1].value = match2.value;

        document.querySelector('input[name="description"]').value = memo;
        newDebits[0].focus();
        document.getElementById('templateSelector').value = '';
        calcTotals();
    }
    
    calcTotals();

    function viewJournal(id) {
        const veil = document.getElementById('journalDetailsModal');
        const loader = document.getElementById('modalLoader');
        const error = document.getElementById('modalError');
        const content = document.getElementById('modalContent');
        
        // Show modal and loader
        veil.classList.add('open');
        loader.style.display = 'flex';
        error.style.display = 'none';
        content.style.display = 'none';
        
        fetch('<?= APP_URL ?>/accounting/journal_details/' + id)
            .then(res => res.json())
            .then(res => {
                loader.style.display = 'none';
                if (res.status === 'success') {
                    const data = res.data;
                    const entry = data.entry;
                    const lines = data.lines;
                    
                    document.getElementById('detDate').innerText = new Date(entry.entry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    document.getElementById('detRef').innerText = entry.reference || 'N/A';
                    document.getElementById('detDesc').innerText = entry.description || 'No description';
                    document.getElementById('detUser').innerText = entry.username || 'System';
                    
                    // Status Badge
                    const statusEl = document.getElementById('detStatus');
                    statusEl.innerHTML = '';
                    if (entry.status === 'Posted') {
                        statusEl.innerHTML = '<span class="sf-badge badge-posted">' + entry.status + '</span>';
                    } else if (entry.status === 'Voided') {
                        statusEl.innerHTML = '<span class="sf-badge badge-voided">' + entry.status + '</span>';
                    } else {
                        statusEl.innerHTML = '<span class="sf-badge badge-draft">' + entry.status + '</span>';
                    }
                    
                    // Lines Table
                    const tbody = document.getElementById('detLinesBody');
                    tbody.innerHTML = '';
                    
                    let totalDebit = 0;
                    let totalCredit = 0;
                    
                    lines.forEach(line => {
                        const tr = document.createElement('tr');
                        tr.style.borderBottom = '0.5px solid var(--c-separator2)';
                        
                        const debitVal = parseFloat(line.debit) || 0;
                        const creditVal = parseFloat(line.credit) || 0;
                        
                        totalDebit += debitVal;
                        totalCredit += creditVal;
                        
                        tr.innerHTML = `
                            <td style="padding: 12px 14px;"><strong>${escapeHtml(line.account_code)}</strong> - ${escapeHtml(line.account_name)}</td>
                            <td style="padding: 12px 14px; color: var(--t-secondary);">${escapeHtml(line.description) || '-'}</td>
                            <td style="padding: 12px 14px; text-align: right;">${debitVal > 0 ? debitVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}</td>
                            <td style="padding: 12px 14px; text-align: right;">${creditVal > 0 ? creditVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                    
                    document.getElementById('detTotalDebit').innerText = totalDebit.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    document.getElementById('detTotalCredit').innerText = totalCredit.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                    content.style.display = 'block';
                } else {
                    document.getElementById('modalErrorMsg').innerText = res.message || 'Failed to load journal details.';
                    error.style.display = 'block';
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                document.getElementById('modalErrorMsg').innerText = 'An error occurred while fetching details.';
                error.style.display = 'block';
                console.error(err);
            });
    }
    
    function closeJournalModal() {
        document.getElementById('journalDetailsModal').classList.remove('open');
    }
</script>