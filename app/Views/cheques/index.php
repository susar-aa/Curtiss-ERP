<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — CHEQUE MANAGEMENT
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
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-label:     #8e8e93;

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-md: 14px;
    --r-xl: 26px;
    --r-pill: 999px;

    --dur-fast:    0.18s;
    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
}

.inv-wrap {
    max-width: 1420px; margin: 0 auto;
    padding: 0px 24px 140px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.inv-header { margin-bottom: 28px; }
.inv-eyebrow {
    font-size: 11px; font-weight: 600; letter-spacing: 0.08em;
    text-transform: uppercase; color: var(--c-blue); margin-bottom: 6px;
}
.inv-title { font-size: 32px; font-weight: 700; letter-spacing: -0.03em; color: var(--t-primary); }

.sf-alert {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--c-surface); border-radius: var(--r-md);
    padding: 14px 16px; margin-bottom: 20px;
    box-shadow: var(--shadow-xs); border: 0.5px solid var(--c-separator);
    border-left-width: 3px; font-size: 14px;
}
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 600; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }

/* KPI Grid */
.kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card {
    background: var(--c-surface); border-radius: var(--r-md);
    padding: 20px; border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xs);
    display: flex; flex-direction: column; gap: 8px;
}
.kpi-label { font-size: 12px; font-weight: 600; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
.kpi-val { font-size: 28px; font-weight: 700; color: var(--t-primary); }
.kpi-sub { font-size: 13px; color: var(--t-label); font-weight: 500; }

/* Table Section */
.date-group {
    background: var(--c-surface2); padding: 10px 16px;
    font-size: 13px; font-weight: 600; color: var(--t-secondary);
    border-top: 0.5px solid var(--c-separator);
    border-bottom: 0.5px solid var(--c-separator);
    display: flex; justify-content: space-between;
}
.date-group.overdue { background: var(--c-red-light); color: var(--c-red); border-color: rgba(255,59,48,0.2); }

.table-panel {
    background: var(--c-surface); border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator); box-shadow: var(--shadow-sm);
    overflow: hidden; margin-bottom: 20px;
}
.inv-table { width: 100%; border-collapse: collapse; }
.inv-table thead th {
    padding: 13px 18px; font-size: 11px; font-weight: 700;
    letter-spacing: 0.06em; text-transform: uppercase;
    color: var(--t-label); background: var(--c-surface2);
    border-bottom: 0.5px solid var(--c-separator); text-align: left;
}
.inv-table tbody tr { transition: background var(--dur-fast); border-bottom: 0.5px solid var(--c-separator2); }
.inv-table tbody tr:hover { background: var(--c-fill2); }
.inv-table td { padding: 14px 18px; font-size: 14px; color: var(--t-primary); vertical-align: middle; }

/* Badges */
.sf-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: var(--r-pill);
    font-size: 12px; font-weight: 700; letter-spacing: 0.01em; white-space: nowrap;
}
.sf-badge .dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.badge-Pending { background: var(--c-orange-light); color: #c05d00; }
.badge-Pending .dot { background: var(--c-orange); }
.badge-Cleared { background: var(--c-green-light); color: #1a7f3c; }
.badge-Cleared .dot { background: var(--c-green); }
.badge-Bounced { background: var(--c-red-light); color: var(--c-red); }
.badge-Bounced .dot { background: var(--c-red); }

/* Row Actions */
.row-acts { display: flex; gap: 6px; justify-content: flex-end; }
.act-btn {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--c-fill); color: var(--t-label);
    border: none; cursor: pointer; text-decoration: none; font-size: 13px;
    transition: all var(--dur-fast) var(--ease-spring);
}
.act-btn:hover { transform: scale(1.12); }
.act-btn.view:hover   { background: var(--c-purple-light); color: var(--c-purple); }
.act-btn.edit:hover   { background: var(--c-blue-light); color: var(--c-blue); }
.act-btn.delete:hover { background: var(--c-red-light); color: var(--c-red); }

/* Command Bar (Dynamic Island) */
.cmd-bar {
    position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
    background: rgba(28, 28, 30, 0.92);
    backdrop-filter: saturate(180%) blur(28px); -webkit-backdrop-filter: saturate(180%) blur(28px);
    border: 0.5px solid rgba(255,255,255,0.12); border-radius: var(--r-pill);
    padding: 7px 10px; display: flex; align-items: center; gap: 4px;
    box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3); z-index: 1000;
}
.cmd-search {
    display: flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.1); border-radius: var(--r-pill);
    padding: 8px 14px; width: 250px;
    transition: width 0.42s cubic-bezier(0.25, 0.1, 0.25, 1), background var(--dur-fast);
}
.cmd-search:focus-within { width: 380px; background: rgba(255,255,255,0.18); }
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; }
.cmd-search input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 14px; font-weight: 500; font-family: var(--f-system); width: 100%;
}
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-cta {
    display: flex; align-items: center; gap: 7px;
    background: #fff; color: #1c1c1e;
    border: none; border-radius: var(--r-pill); padding: 0 18px; height: 38px;
    font-size: 14px; font-weight: 700; font-family: var(--f-system);
    cursor: pointer; transition: transform var(--dur-fast) var(--ease-spring), background var(--dur-fast);
    margin-left: 2px;
}
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

/* SF Modals */
.sf-modal {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.4); backdrop-filter: blur(8px);
    align-items: center; justify-content: center; opacity: 0;
    transition: opacity var(--dur-fast);
}
.sf-modal.open { display: flex; opacity: 1; }
.sf-modal-box {
    background: var(--c-surface); width: 540px;
    border-radius: var(--r-xl); padding: 32px;
    box-shadow: var(--shadow-xl); border: 0.5px solid var(--c-separator);
    transform: scale(0.95); transition: transform var(--dur-fast) var(--ease-spring);
}
.sf-modal.open .sf-modal-box { transform: scale(1); }
.sf-modal-box h3 { margin-top: 0; font-size: 22px; margin-bottom: 20px; color: var(--t-primary); }
.sf-form-group { margin-bottom: 16px; }
.sf-form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--t-secondary); margin-bottom: 6px; }
.sf-input {
    width: 100%; padding: 10px 14px;
    background: var(--c-surface2); border: 1px solid var(--c-separator);
    border-radius: var(--r-md); font-family: var(--f-system);
    font-size: 14px; color: var(--t-primary); box-sizing: border-box;
    transition: border-color var(--dur-fast), background var(--dur-fast);
}
.sf-input:focus { outline: none; border-color: var(--c-blue); background: var(--c-surface); }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.sf-modal-acts { display: flex; justify-content: flex-end; gap: 10px; margin-top: 28px; }
.sf-btn {
    padding: 10px 20px; border-radius: var(--r-pill); font-weight: 600; font-size: 14px;
    border: none; cursor: pointer; transition: background var(--dur-fast); font-family: var(--f-system);
}
.sf-btn-ghost { background: var(--c-fill); color: var(--t-primary); }
.sf-btn-ghost:hover { background: var(--c-fill2); }
.sf-btn-primary { background: var(--c-blue); color: #fff; }
.sf-btn-primary:hover { background: #006ce6; }
.sf-btn-danger { background: var(--c-red); color: #fff; }
.sf-btn-danger:hover { background: #e03228; }

/* Cheque Viewer Modal */
.cheque-paper {
    width: 100%; height: 260px;
    background: repeating-linear-gradient(45deg, #f0f8ff, #f0f8ff 10px, #e6f2ff 10px, #e6f2ff 20px);
    border: 1px solid #b0c4de; border-radius: 8px; padding: 25px;
    position: relative; font-family: 'Times New Roman', serif;
    box-shadow: inset 0 0 40px rgba(0,0,0,0.05); color: #333; box-sizing: border-box;
}
.cq-bank { font-size: 18px; font-weight: bold; color: #444; border-bottom: 2px solid #ccc; display: inline-block; padding-bottom: 5px; margin-bottom: 20px;}
.cq-date { position: absolute; top: 25px; right: 25px; border-bottom: 1px solid #555; padding-bottom: 2px; font-size: 16px; font-family: var(--f-mono); letter-spacing: 2px;}
.cq-payee { margin-top: 20px; font-size: 16px; border-bottom: 1px solid #555; padding-bottom: 5px; }
.cq-amount-words { margin-top: 20px; font-size: 16px; border-bottom: 1px solid #555; padding-bottom: 5px; line-height: 1.5; }
.cq-amount-box { position: absolute; top: 110px; right: 25px; border: 2px solid #555; padding: 10px 20px; font-size: 20px; font-weight: bold; background: #fff; }
.cq-signature { position: absolute; bottom: 50px; right: 25px; border-bottom: 1px solid #555; width: 180px; text-align: center; font-size: 14px; color: var(--c-blue); font-style: italic;}
.cq-micr { position: absolute; bottom: 15px; left: 0; width: 100%; text-align: center; font-family: var(--f-mono); font-size: 20px; font-weight: bold; letter-spacing: 4px; color: #111;}

/* Segmented Control */
.sf-segment-wrap { display: flex; justify-content: center; margin-bottom: 24px; }
.sf-segments { display: inline-flex; background: var(--c-fill); padding: 4px; border-radius: var(--r-md); }
.sf-seg-btn { padding: 8px 24px; font-size: 14px; font-weight: 600; font-family: var(--f-system); border: none; background: transparent; color: var(--t-secondary); border-radius: 10px; cursor: pointer; transition: all var(--dur-fast); }
.sf-seg-btn.active { background: var(--c-surface); color: var(--t-primary); box-shadow: var(--shadow-sm); }
.chq-section { display: none; }
.chq-section.active { display: block; }

</style>

<div class="inv-wrap">

    <!-- Alerts -->
    <?php if(isset($_GET['success'])): ?>
    <div class="sf-alert success">
        <i class="fa-solid fa-circle-check sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Success</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div>
        </div>
    </div>
    <?php endif; ?>
    <?php if(!empty($data['error'])): ?>
    <div class="sf-alert error">
        <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Operation Error</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI Grid -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label" style="color: var(--c-orange);">Pending Portfolio</div>
            <div class="kpi-val">Rs: <?= number_format($data['kpi_pending'], 2) ?></div>
            <div class="kpi-sub">Total Uncleared Amount</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label" style="color: var(--c-blue);">Next Banking Date</div>
            <div class="kpi-val" style="color: var(--c-blue);">
                <?= $data['kpi_next_date'] ? date('M d, Y', strtotime($data['kpi_next_date'])) : 'No Schedule' ?>
            </div>
            <div class="kpi-sub">Rs: <?= number_format($data['kpi_next_amount'], 2) ?> due</div>
        </div>
    </div>

    <!-- Table Body Container (ajax target) -->
    <div id="tableContainer">
        
        <div class="sf-segment-wrap">
            <div class="sf-segments">
                <button class="sf-seg-btn active" id="tab-received" onclick="switchChequeTab('received')">Collections (Received)</button>
                <button class="sf-seg-btn" id="tab-issued" onclick="switchChequeTab('issued')">Payments (Issued)</button>
                <button class="sf-seg-btn" id="tab-returned" onclick="switchChequeTab('returned')">Returns (Cheques in Hand)</button>
            </div>
        </div>

        <?php if(empty($data['grouped_received_cheques']) && empty($data['grouped_issued_cheques'])): ?>
        <div class="table-panel" style="padding: 60px; text-align: center; color: var(--t-secondary);">
            <i class="fa-solid fa-money-check" style="font-size: 32px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No cheques recorded matching this query.</p>
        </div>
        <?php else: ?>
            
            <div id="section-received" class="chq-section active">
                <?php if(empty($data['grouped_received_cheques'])): ?>
                    <div class="table-panel" style="padding: 60px; text-align: center; color: var(--t-secondary);">
                        <p>No received cheques matching this query.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['grouped_received_cheques'] as $date => $cheques): ?>
                        <?php 
                            $dayTotal = 0;
                            foreach($cheques as $c) { if($c->status == 'Pending') $dayTotal += $c->amount; }
                            $isPastDue = strtotime($date) < strtotime('today') && $dayTotal > 0;
                        ?>
                        <div class="date-group <?= $isPastDue ? 'overdue' : '' ?>">
                            <span><i class="fa-regular fa-calendar" style="margin-right:6px;"></i> <?= date('l, F j, Y', strtotime($date)) ?> <?= $isPastDue ? '— OVERDUE' : '' ?></span>
                            <span>Deposit Rs: <?= number_format($dayTotal, 2) ?></span>
                        </div>
                        <div class="table-panel" style="border-radius: 0; border-top: none; margin-bottom: 0;">
                            <table class="inv-table">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Customer (Drawer)</th>
                                        <th style="width: 20%;">Bank details</th>
                                        <th style="width: 15%;">Cheque Number</th>
                                        <th style="width: 12%;">Status</th>
                                        <th style="width: 15%; text-align: right;">Amount (Rs:)</th>
                                        <th style="width: 13%; text-align: right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cheques as $chk): ?>
                                    <tr>
                                        <td>
                                            <strong style="font-weight:600;"><?= htmlspecialchars($chk->payee_name ?: '-') ?></strong>
                                            <div style="font-size:11px; color:var(--c-green); font-weight:600; margin-top:2px;">Customer Receipt</div>
                                        </td>
                                        <td>
                                            <div style="font-weight:500;"><?= htmlspecialchars($chk->bank_name) ?></div>
                                            <?php if (!empty($chk->drawn_bank_name)): ?>
                                                <div style="font-size: 11px; color: var(--t-secondary); margin-top:2px;">
                                                    <i class="fa-solid fa-building-columns"></i> <?= htmlspecialchars($chk->drawn_bank_name) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-family: var(--f-mono); background: var(--c-fill); padding: 4px 8px; border-radius: 6px; font-size: 13px;">
                                                <?= htmlspecialchars($chk->cheque_number) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="sf-badge badge-<?= $chk->status ?>">
                                                <div class="dot"></div> <?= $chk->status ?>
                                            </div>
                                        </td>
                                        <td style="text-align: right; font-weight: 600;">
                                            <?= number_format($chk->amount, 2) ?>
                                        </td>
                                        <td>
                                            <div class="row-acts">
                                                <button class="act-btn view" title="View Cheque" onclick="viewCheque('<?= htmlspecialchars(addslashes($chk->bank_name)) ?>', '<?= htmlspecialchars(addslashes($chk->banking_date)) ?>', '<?= htmlspecialchars(addslashes($chk->amount)) ?>', '<?= htmlspecialchars(addslashes($chk->payee_name ?: '')) ?>', '<?= htmlspecialchars(addslashes($chk->cheque_number)) ?>')">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                                <button class="act-btn edit" title="Edit" onclick="openEditModal(<?= $chk->id ?>, '<?= htmlspecialchars(addslashes($chk->payee_name ?: '')) ?>', '<?= htmlspecialchars(addslashes($chk->bank_name)) ?>', '<?= htmlspecialchars(addslashes($chk->cheque_number)) ?>', <?= $chk->amount ?>, '<?= $chk->banking_date ?>', '<?= $chk->status ?>', <?= $chk->bank_account_id ?: 'null' ?>, 'received')">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button class="act-btn delete" title="Delete" onclick="openDeleteModal(<?= $chk->id ?>, '<?= htmlspecialchars(addslashes($chk->cheque_number)) ?>')">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="section-issued" class="chq-section">
                <?php if(empty($data['grouped_issued_cheques'])): ?>
                    <div class="table-panel" style="padding: 60px; text-align: center; color: var(--t-secondary);">
                        <p>No issued cheques matching this query.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['grouped_issued_cheques'] as $date => $cheques): ?>
                        <?php 
                            $dayTotal = 0;
                            foreach($cheques as $c) { if($c->status == 'Pending') $dayTotal += $c->amount; }
                            $isPastDue = strtotime($date) < strtotime('today') && $dayTotal > 0;
                        ?>
                        <div class="date-group <?= $isPastDue ? 'overdue' : '' ?>">
                            <span><i class="fa-regular fa-calendar" style="margin-right:6px;"></i> <?= date('l, F j, Y', strtotime($date)) ?> <?= $isPastDue ? '— OVERDUE' : '' ?></span>
                            <span>Payment Rs: <?= number_format($dayTotal, 2) ?></span>
                        </div>
                        <div class="table-panel" style="border-radius: 0; border-top: none; margin-bottom: 0;">
                            <table class="inv-table">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Supplier / Payee</th>
                                        <th style="width: 20%;">Bank details</th>
                                        <th style="width: 15%;">Cheque Number</th>
                                        <th style="width: 12%;">Status</th>
                                        <th style="width: 15%; text-align: right;">Amount (Rs:)</th>
                                        <th style="width: 13%; text-align: right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cheques as $chk): ?>
                                    <tr>
                                        <td>
                                            <strong style="font-weight:600;"><?= htmlspecialchars($chk->payee_name ?: '-') ?></strong>
                                            <div style="font-size:11px; color:var(--c-orange); font-weight:600; margin-top:2px;">Supplier Payment</div>
                                        </td>
                                        <td>
                                            <div style="font-weight:500;"><?= htmlspecialchars($chk->bank_name) ?></div>
                                            <?php if (!empty($chk->drawn_bank_name)): ?>
                                                <div style="font-size: 11px; color: var(--t-secondary); margin-top:2px;">
                                                    <i class="fa-solid fa-building-columns"></i> <?= htmlspecialchars($chk->drawn_bank_name) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-family: var(--f-mono); background: var(--c-fill); padding: 4px 8px; border-radius: 6px; font-size: 13px;">
                                                <?= htmlspecialchars($chk->cheque_number) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="sf-badge badge-<?= $chk->status ?>">
                                                <div class="dot"></div> <?= $chk->status ?>
                                            </div>
                                        </td>
                                        <td style="text-align: right; font-weight: 600;">
                                            <?= number_format($chk->amount, 2) ?>
                                        </td>
                                        <td>
                                            <div class="row-acts">
                                                <button class="act-btn view" title="View Cheque" onclick="viewCheque('<?= htmlspecialchars(addslashes($chk->bank_name)) ?>', '<?= htmlspecialchars(addslashes($chk->banking_date)) ?>', '<?= htmlspecialchars(addslashes($chk->amount)) ?>', '<?= htmlspecialchars(addslashes($chk->payee_name ?: '')) ?>', '<?= htmlspecialchars(addslashes($chk->cheque_number)) ?>')">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                                <button class="act-btn edit" title="Edit" onclick="openEditModal(<?= $chk->id ?>, '<?= htmlspecialchars(addslashes($chk->payee_name ?: '')) ?>', '<?= htmlspecialchars(addslashes($chk->bank_name)) ?>', '<?= htmlspecialchars(addslashes($chk->cheque_number)) ?>', <?= $chk->amount ?>, '<?= $chk->banking_date ?>', '<?= $chk->status ?>', <?= $chk->bank_account_id ?: 'null' ?>, 'issued')">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button class="act-btn delete" title="Delete" onclick="openDeleteModal(<?= $chk->id ?>, '<?= htmlspecialchars(addslashes($chk->cheque_number)) ?>')">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Returns Section -->
            <div id="section-returned" class="chq-section">
                <?php 
                $hasPendingReturns = false;
                if (!empty($data['grouped_received_cheques'])) {
                    foreach($data['grouped_received_cheques'] as $date => $cheques) {
                        foreach($cheques as $chk) {
                            if($chk->status == 'Pending' && empty($chk->bank_account_id)) {
                                $hasPendingReturns = true;
                                break 2;
                            }
                        }
                    }
                }
                ?>
                
                <?php if(!$hasPendingReturns): ?>
                    <div class="table-panel" style="padding: 60px; text-align: center; color: var(--t-secondary);">
                        <p>No pending cheques in hand to return.</p>
                    </div>
                <?php else: ?>
                    <div class="table-panel" style="border-radius: 8px;">
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Cheque Number</th>
                                    <th style="width: 20%;">Customer</th>
                                    <th style="width: 20%;">Bank</th>
                                    <th style="width: 15%; text-align: right;">Amount (Rs:)</th>
                                    <th style="width: 15%; text-align: center;">Banking Date</th>
                                    <th style="width: 15%; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['grouped_received_cheques'] as $date => $cheques): ?>
                                    <?php foreach($cheques as $chk): ?>
                                        <?php if($chk->status == 'Pending' && empty($chk->bank_account_id)): ?>
                                        <tr>
                                            <td>
                                                <span style="font-family: var(--f-mono); background: var(--c-fill); padding: 4px 8px; border-radius: 6px; font-size: 13px;">
                                                    <?= htmlspecialchars($chk->cheque_number) ?>
                                                </span>
                                            </td>
                                            <td><strong style="font-weight:600;"><?= htmlspecialchars($chk->customer_name ?? $chk->payee_name) ?></strong></td>
                                            <td><?= htmlspecialchars($chk->bank_name) ?></td>
                                            <td style="text-align: right; font-weight: 600;"><?= number_format($chk->amount, 2) ?></td>
                                            <td style="text-align: center;"><?= date('d-M-Y', strtotime($chk->banking_date)) ?></td>
                                            <td style="text-align: right;">
                                                <button type="button" class="sf-btn" style="background:var(--c-red); color:white; font-size: 12px; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer;" onclick="openReturnModal(<?= $chk->id ?>, '<?= htmlspecialchars(addslashes($chk->cheque_number ?: '')) ?>', '<?= htmlspecialchars(addslashes($chk->customer_name ?? $chk->payee_name ?? '')) ?>', <?= $chk->amount ?: 0 ?>)">
                                                    Mark Returned
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- Command Bar -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by name, bank, or cheque #" value="<?= htmlspecialchars($data['search'] ?? '') ?>">
    </div>
    <div class="cmd-divider"></div>
    <button class="cmd-cta" onclick="openModal('addModal')">
        <i class="fa-solid fa-plus"></i> Record Cheque
    </button>
    <div class="cmd-divider"></div>
    <a href="<?= APP_URL ?>/cheque?export=true<?= !empty($data['search']) ? '&search=' . urlencode($data['search']) : '' ?>" class="cmd-cta" style="background: var(--c-surface2); color: var(--t-primary); padding: 0 14px;" title="Export Excel"><i class="fa-solid fa-file-excel"></i></a>
</div>

<!-- Add Modal -->
<div class="sf-modal" id="addModal">
    <div class="sf-modal-box">
        <h3>Record Received / Paid Cheque</h3>
        <form action="<?= APP_URL ?>/cheque" method="POST">
            <input type="hidden" name="action" value="add_cheque">
            <div class="sf-form-group">
                <label>Cheque Type *</label>
                <select name="cheque_type" id="add_cheque_type" class="sf-input" required onchange="toggleAddBankAcc()">
                    <option value="">-- Select Type --</option>
                    <option value="received">Collection (Received Cheque)</option>
                    <option value="issued">Payment (Issued Cheque)</option>
                </select>
            </div>
            <div class="sf-form-group">
                <label>Drawer / Payee Name *</label>
                <input type="text" name="payee_name" class="sf-input" placeholder="e.g. John Doe" required>
            </div>
            <div class="sf-form-group" id="add_bank_acc_group" style="display:none;">
                <label>Drawn Bank Account (Required for Issued Cheques)</label>
                <select name="bank_account_id" id="add_bank_account_id" class="sf-input">
                    <option value="">-- Select Bank Account --</option>
                    <?php foreach($data['bank_accounts'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_name) ?> (<?= htmlspecialchars($acc->account_code) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid-2">
                <div class="sf-form-group"><label>Bank Name *</label><input type="text" name="bank_name" class="sf-input" placeholder="e.g. Commercial Bank" required></div>
                <div class="sf-form-group"><label>Cheque Number *</label><input type="text" name="cheque_number" class="sf-input" required></div>
            </div>
            <div class="grid-2">
                <div class="sf-form-group"><label>Banking Date *</label><input type="date" name="banking_date" class="sf-input" required></div>
                <div class="sf-form-group"><label>Amount (Rs:) *</label><input type="number" name="amount" step="0.01" class="sf-input" required></div>
            </div>
            <div class="sf-modal-acts">
                <button type="button" class="sf-btn sf-btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="sf-btn sf-btn-primary">Save Cheque</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="sf-modal" id="editModal">
    <div class="sf-modal-box">
        <h3>Update Cheque Details</h3>
        <form action="<?= APP_URL ?>/cheque" method="POST">
            <input type="hidden" name="action" value="edit_cheque">
            <input type="hidden" name="cheque_id" id="edit_id">
            
            <div class="sf-form-group">
                <label>Cheque Type *</label>
                <select name="cheque_type" id="edit_cheque_type" class="sf-input" required onchange="toggleEditBankAcc()">
                    <option value="received">Collection (Received Cheque)</option>
                    <option value="issued">Payment (Issued Cheque)</option>
                </select>
            </div>
            <div class="sf-form-group">
                <label>Drawer / Payee Name *</label>
                <input type="text" name="payee_name" id="edit_payee_name" class="sf-input" required>
            </div>
            <div class="sf-form-group" id="edit_bank_acc_group" style="display:none;">
                <label>Drawn Bank Account (Required for Issued Cheques)</label>
                <select name="bank_account_id" id="edit_bank_account_id" class="sf-input">
                    <option value="">-- None --</option>
                    <?php foreach($data['bank_accounts'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_name) ?> (<?= htmlspecialchars($acc->account_code) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid-2">
                <div class="sf-form-group"><label>Bank Name</label><input type="text" name="bank_name" id="edit_bank" class="sf-input" required></div>
                <div class="sf-form-group"><label>Cheque Number</label><input type="text" name="cheque_number" id="edit_cnum" class="sf-input" required></div>
            </div>
            <div class="grid-2">
                <div class="sf-form-group"><label>Banking Date</label><input type="date" name="banking_date" id="edit_date" class="sf-input" required></div>
                <div class="sf-form-group"><label>Amount</label><input type="number" name="amount" id="edit_amt" step="0.01" class="sf-input" required></div>
            </div>
            <div class="sf-form-group">
                <label style="color:var(--c-blue);">Cheque Status</label>
                <select name="status" id="edit_status" class="sf-input" style="background:var(--c-blue-light); border-color:var(--c-blue); color:var(--c-blue); font-weight:700;">
                    <option value="Pending">Pending (Holding)</option>
                    <option value="Cleared">Cleared (Realized in Bank)</option>
                    <option value="Bounced">Bounced (Returned)</option>
                </select>
            </div>
            <div class="sf-modal-acts">
                <button type="button" class="sf-btn sf-btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="sf-btn sf-btn-primary">Update Cheque</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="sf-modal" id="deleteModal">
    <div class="sf-modal-box" style="width: 420px; text-align: center;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:42px; color:var(--c-red); margin-bottom:16px;"></i>
        <h3 style="margin-bottom:8px;">Delete Cheque?</h3>
        <p style="color:var(--t-secondary); margin-bottom:24px; font-size:14px;">Are you sure you want to permanently delete cheque <strong id="del_cnum"></strong>? This action cannot be undone.</p>
        <form action="<?= APP_URL ?>/cheque" method="POST">
            <input type="hidden" name="action" value="delete_cheque">
            <input type="hidden" name="delete_id" id="delete_id">
            <div class="sf-modal-acts" style="justify-content: center;">
                <button type="button" class="sf-btn sf-btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="sf-btn sf-btn-danger">Delete Cheque</button>
            </div>
        </form>
    </div>
</div>

<!-- View Cheque Modal -->
<div class="sf-modal" id="viewModal">
    <div class="sf-modal-box" style="width: 760px; background:transparent; border:none; box-shadow:none; padding:0;">
        <div class="cheque-paper">
            <div class="cq-bank" id="view_bank">Bank Name</div>
            <div class="cq-date" id="view_date">DD/MM/YYYY</div>
            <div class="cq-payee"><strong>Pay:</strong> <span id="view_payee" style="font-family: var(--f-mono);"></span></div>
            <div class="cq-amount-words"><strong>Rupees:</strong> <span id="view_words" style="font-style:italic;"></span></div>
            <div class="cq-amount-box">Rs. <span id="view_amount"></span></div>
            <div class="cq-signature">Authorized Signatory</div>
            <div class="cq-micr">|| 000 <span id="view_cnum"></span> 0000 00 ||</div>
        </div>
        <div style="text-align:center; margin-top:16px;">
            <button class="sf-btn sf-btn-ghost" style="background:rgba(255,255,255,0.2); color:#fff; backdrop-filter:blur(10px);" onclick="closeModal('viewModal')">Close Viewer</button>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="sf-modal" id="returnModal">
    <div class="sf-modal-box">
        <h3>Mark Cheque as Returned</h3>
        <form method="POST" action="<?= APP_URL ?>/cheque" id="returnForm">
            <input type="hidden" name="action" value="process_return">
            <input type="hidden" name="cheque_id" id="return_cheque_id">
            
            <div style="background: var(--c-fill); padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                You are about to return Cheque #<strong id="disp_cheque_num"></strong> from <strong id="disp_customer"></strong>.
                <br>Amount: <strong>Rs: <span id="disp_amount"></span></strong>
            </div>

            <div class="sf-form-group">
                <label>Return Reason *</label>
                <select name="return_reason" id="return_reason" class="sf-input" required onchange="toggleOtherReason()">
                    <option value="">-- Select Reason --</option>
                    <option value="Insufficient Funds">Insufficient Funds</option>
                    <option value="Signature Mismatch">Signature Mismatch</option>
                    <option value="Account Closed">Account Closed</option>
                    <option value="Payment Stopped">Payment Stopped</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="sf-form-group" id="other_reason_div" style="display:none;">
                <label>Specify Reason *</label>
                <input type="text" name="other_reason" id="other_reason" class="sf-input" placeholder="Enter reason">
            </div>

            <div class="sf-form-group">
                <label>Return Date *</label>
                <input type="date" name="return_date" class="sf-input" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
            </div>
            
            <hr style="border:none; border-top: 1px solid var(--c-separator); margin: 20px 0;">
            <h4 style="margin-bottom: 12px; font-size: 14px;">Bank Return Charges (Optional)</h4>
            
            <div class="sf-form-group">
                <label>Bank Charge Amount</label>
                <input type="number" step="0.01" min="0" name="return_charge" class="sf-input" placeholder="0.00">
            </div>

            <div class="sf-form-group">
                <label>Expense Account for Charge</label>
                <select name="charge_account_id" class="sf-input">
                    <option value="">-- Select Account --</option>
                    <?php if(!empty($data['expense_accounts'])): foreach($data['expense_accounts'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="modal-acts">
                <button type="button" class="sf-btn sf-btn-ghost" onclick="closeModal('returnModal')">Cancel</button>
                <button type="submit" class="sf-btn" style="background: var(--c-red); color: white;" onclick="return confirm('Are you sure? This will reverse the customer\'s payment and reopen their invoices.');">Confirm Return</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReturnModal(id, num, customer, amount) {
        document.getElementById('return_cheque_id').value = id;
        document.getElementById('disp_cheque_num').textContent = num;
        document.getElementById('disp_customer').textContent = customer;
        document.getElementById('disp_amount').textContent = amount.toFixed(2);
        openModal('returnModal');
    }

    function toggleOtherReason() {
        var val = document.getElementById('return_reason').value;
        var otherDiv = document.getElementById('other_reason_div');
        var otherInput = document.getElementById('other_reason');
        if (val === 'Other') {
            otherDiv.style.display = 'block';
            otherInput.required = true;
        } else {
            otherDiv.style.display = 'none';
            otherInput.required = false;
            otherInput.value = '';
        }
    }

    function openModal(id) { 
        const m = document.getElementById(id);
        m.classList.add('open');
    }
    function closeModal(id) { 
        document.getElementById(id).classList.remove('open');
    }

    function toggleAddBankAcc() {
        const type = document.getElementById('add_cheque_type').value;
        const group = document.getElementById('add_bank_acc_group');
        const select = document.getElementById('add_bank_account_id');
        if (type === 'issued') {
            group.style.display = 'block';
            select.setAttribute('required', 'required');
        } else {
            group.style.display = 'none';
            select.removeAttribute('required');
            select.value = '';
        }
    }

    function toggleEditBankAcc() {
        const type = document.getElementById('edit_cheque_type').value;
        const group = document.getElementById('edit_bank_acc_group');
        const select = document.getElementById('edit_bank_account_id');
        if (type === 'issued') {
            group.style.display = 'block';
            select.setAttribute('required', 'required');
        } else {
            group.style.display = 'none';
            select.removeAttribute('required');
            select.value = '';
        }
    }

    function openEditModal(id, payee, bank, cnum, amt, date, status, bankAccId, type) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_cheque_type').value = type;
        document.getElementById('edit_payee_name').value = payee;
        document.getElementById('edit_bank').value = bank;
        document.getElementById('edit_cnum').value = cnum;
        document.getElementById('edit_amt').value = amt;
        document.getElementById('edit_date').value = date;
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_bank_account_id').value = bankAccId || '';
        toggleEditBankAcc();
        openModal('editModal');
    }

    function openDeleteModal(id, cnum) {
        document.getElementById('delete_id').value = id;
        document.getElementById('del_cnum').innerText = cnum;
        openModal('deleteModal');
    }


    function numberToWords(amount) {
        const num = parseFloat(amount);
        if (isNaN(num) || num === 0) return "Zero Rupees Only";

        const units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function convertSection(n) {
            let str = '';
            if (n >= 100) {
                str += units[Math.floor(n / 100)] + ' Hundred ';
                n %= 100;
            }
            if (n >= 20) {
                str += tens[Math.floor(n / 10)] + ' ';
                n %= 10;
            }
            if (n > 0) {
                str += units[n] + ' ';
            }
            return str.trim();
        }

        const integerPart = Math.floor(Math.abs(num));
        const decimalPart = Math.round((Math.abs(num) - integerPart) * 100);

        let crore = Math.floor(integerPart / 10000000);
        let remainder = integerPart % 10000000;
        let lakh = Math.floor(remainder / 100000);
        remainder %= 100000;
        let thousand = Math.floor(remainder / 1000);
        remainder %= 1000;
        let hundredAndRest = remainder;

        let result = '';
        if (crore > 0) result += convertSection(crore) + ' Crore ';
        if (lakh > 0) result += convertSection(lakh) + ' Lakh ';
        if (thousand > 0) result += convertSection(thousand) + ' Thousand ';
        if (hundredAndRest > 0) result += convertSection(hundredAndRest) + ' ';

        result = result.trim();
        if (!result) result = 'Zero';
        result += ' Rupees';

        if (decimalPart > 0) {
            result += ' and ' + convertSection(decimalPart) + ' Cents';
        }
        result += ' Only';

        return result;
    }

    function viewCheque(bank, date, amount, drawer, cnum) {
        document.getElementById('view_bank').innerText = bank;
        
        // Format Date nicely
        const d = new Date(date);
        document.getElementById('view_date').innerText = d.getDate().toString().padStart(2, '0') + '/' + (d.getMonth()+1).toString().padStart(2, '0') + '/' + d.getFullYear();
        
        document.getElementById('view_amount').innerText = parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('view_words').innerText = numberToWords(amount);
        document.getElementById('view_payee').innerText = drawer + " (Auth Signatory)";
        
        // Randomize MICR looking numbers using the actual cheque number as base
        document.getElementById('view_cnum').innerText = cnum.padStart(6, '0');
        
        openModal('viewModal');
    }

    let activeTab = 'received';

    function switchChequeTab(tab) {
        activeTab = tab;
        // Update Buttons
        document.getElementById('tab-received').classList.remove('active');
        document.getElementById('tab-issued').classList.remove('active');
        document.getElementById('tab-returned').classList.remove('active');
        document.getElementById('tab-' + tab).classList.add('active');
        
        // Update Sections
        document.getElementById('section-received').classList.remove('active');
        document.getElementById('section-issued').classList.remove('active');
        document.getElementById('section-returned').classList.remove('active');
        document.getElementById('section-' + tab).classList.add('active');
    }

    // AJAX Search implementation
    let searchTimeout = null;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout); 
            searchTimeout = setTimeout(triggerSearch, 400); 
        });
    }

    function triggerSearch() {
        const query = encodeURIComponent(searchInput.value);
        const url = `?search=${query}`;
        
        fetch(url).then(response => response.text()).then(html => {
            const parser = new DOMParser(); 
            const doc = parser.parseFromString(html, 'text/html');
            const newContainer = doc.getElementById('tableContainer');
            if (newContainer) {
                document.getElementById('tableContainer').innerHTML = newContainer.innerHTML;
                
                // Restore Tab state after replacing the container contents
                switchChequeTab(activeTab);
            }
            window.history.pushState({}, '', url);
        }).catch(err => {
            console.error("Fetch error during search:", err);
        });
    }
</script>