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
   SF PRO + APPLE DESIGN LANGUAGE — SUPPLIER CENTER
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

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-xs: 6px;
    --r-sm: 10px;
    --r-md: 14px;
    --r-xl: 26px;
    --r-pill: 999px;
    
    --dur-fast:    0.18s;
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
}

.sup-root {
    font-family: var(--f-system); font-size: 14px;
    color: var(--t-primary); background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
}
.sup-wrap { max-width: 1420px; margin: 0 auto; padding: 16px 24px 100px; }

/* Alerts */
.sf-alert { display: flex; align-items: flex-start; gap: 12px; background: var(--c-surface); border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 20px; box-shadow: var(--shadow-sm); border: 0.5px solid var(--c-separator); border-left-width: 3.5px; }
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 700; margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }

/* Stat Cards */
.stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; margin-top: 10px; }
.stat-card { background: var(--c-surface); border-radius: var(--r-xl); padding: 16px 20px; box-shadow: var(--shadow-sm); border: 0.5px solid var(--c-separator); display: flex; align-items: center; gap: 16px; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2.5px; border-radius: var(--r-xl) var(--r-xl) 0 0; }
.stat-card.blue::before { background: var(--c-blue); }
.stat-card.orange::before { background: var(--c-orange); }
.stat-card.red::before { background: var(--c-red); }
.stat-icon { width: 46px; height: 46px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.stat-card.blue .stat-icon { background: var(--c-blue-light); color: var(--c-blue); }
.stat-card.orange .stat-icon { background: var(--c-orange-light); color: var(--c-orange); }
.stat-card.red .stat-icon { background: var(--c-red-light); color: var(--c-red); }
.stat-num { font-size: 22px; font-weight: 700; color: var(--t-primary); line-height: 1.1; margin-bottom: 2px; }
.stat-lbl { font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--t-label); }

/* Split Layout */
.split-layout { display: flex; height: 74vh; background: var(--c-surface); border-radius: var(--r-xl); border: 0.5px solid var(--c-separator); overflow: hidden; box-shadow: var(--shadow-sm); }
.left-pane { width: 340px; border-right: 0.5px solid var(--c-separator); display: flex; flex-direction: column; background: var(--c-surface2); flex-shrink: 0; }
.pane-header { padding: 16px 20px; border-bottom: 0.5px solid var(--c-separator); font-weight: 700; display: flex; justify-content: space-between; align-items: center; background: var(--c-surface); }
.search-container { padding: 12px 16px; border-bottom: 0.5px solid var(--c-separator); background: var(--c-surface2); position: relative; }
.search-input { width: 100%; padding: 8px 12px 8px 32px; border: 0.5px solid var(--c-separator); border-radius: var(--r-pill); font-size: 13px; background: var(--c-surface); color: var(--t-primary); outline: none; transition: border-color var(--dur-fast); box-sizing: border-box;}
.search-input:focus { border-color: var(--c-blue); }
.search-icon { position: absolute; left: 26px; top: 50%; transform: translateY(-50%); color: var(--t-label); font-size: 13px; }

.supplier-list { flex: 1; overflow-y: auto; }
.supplier-item { padding: 14px 16px; border-bottom: 0.5px solid var(--c-separator2); cursor: pointer; text-decoration: none; color: var(--t-primary); display: flex; justify-content: space-between; align-items: center; transition: background var(--dur-fast); }
.supplier-item:hover { background: var(--c-fill); }
.supplier-item.active { background: var(--c-blue-light); color: var(--t-primary); }
.supplier-item.active .text-sub { color: var(--c-blue); }
.text-sub { font-size: 11.5px; color: var(--t-secondary); display: block; margin-top: 4px; }
.bal-text { font-size: 12.5px; font-weight: 700; margin-top: 2px;}

.right-pane { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--c-surface); }
.right-header { padding: 24px; border-bottom: 0.5px solid var(--c-separator); display: flex; justify-content: space-between; align-items: center; background: var(--c-surface); }
.avatar-circle { width: 50px; height: 50px; border-radius: 50%; background: var(--c-blue-light); color: var(--c-blue); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0; }

.tabs { display: flex; border-bottom: 0.5px solid var(--c-separator); background: var(--c-surface2); padding: 0 24px;}
.tab-btn { padding: 14px 20px; border: none; background: transparent; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--t-secondary); border-bottom: 2.5px solid transparent; transition: var(--dur-fast);}
.tab-btn:hover { color: var(--c-blue); }
.tab-btn.active { color: var(--c-blue); border-bottom-color: var(--c-blue); }

.tab-content { flex: 1; padding: 24px; overflow-y: auto; display: none; }
.tab-content.active { display: block; }

/* Table */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 14px 16px; text-align: left; border-bottom: 0.5px solid var(--c-separator2); font-size: 13.5px; color: var(--t-primary); }
.data-table th { background: var(--c-surface2); color: var(--t-label); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; border-bottom: 0.5px solid var(--c-separator); }
.data-table tbody tr:hover { background: var(--c-fill2); }
.num-col { text-align: right !important; }

/* Form Elements */
.sf-group { margin-bottom: 16px; }
.sf-group label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: var(--t-secondary); text-transform: uppercase; }
.sf-input { width: 100%; padding: 10px 14px; border-radius: var(--r-sm); border: 0.5px solid var(--c-separator); background: var(--c-surface2); color: var(--t-primary); font-size: 14px; outline: none; transition: border-color var(--dur-fast); box-sizing: border-box; }
.sf-input:focus { border-color: var(--c-blue); background: var(--c-surface); }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

/* Buttons */
.sf-btn { padding: 9px 16px; border-radius: var(--r-pill); font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; transition: transform var(--dur-fast), filter var(--dur-fast); text-decoration: none; font-family: var(--f-system); }
.sf-btn:active { transform: scale(0.97); }
.sf-btn-primary { background: var(--c-blue); color: #fff; }
.sf-btn-outline { background: transparent; border: 0.5px solid var(--c-separator); color: var(--t-primary); box-shadow: var(--shadow-xs); }
.sf-btn-outline:hover { background: var(--c-fill); }
.sf-btn-small { padding: 5px 12px; font-size: 12px; }

/* Badges */
.sf-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: var(--r-xs); font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge-Pending { background: var(--c-orange-light); color: var(--c-orange); }
.badge-Sent { background: var(--c-blue-light); color: var(--c-blue); }
.badge-Received { background: var(--c-green-light); color: var(--c-green); }
.badge-Voided { background: var(--c-red-light); color: var(--c-red); }

/* Modal */
.modal-veil { position: fixed; inset: 0; z-index: 2000; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(10px); display: none; align-items: center; justify-content: center; }
.modal-veil.open { display: flex; }
.modal-box { background: var(--c-surface); border: 0.5px solid var(--c-separator); border-radius: var(--r-xl); box-shadow: var(--shadow-xl); width: 480px; max-width: 95vw; overflow: hidden; transform: scale(0.95); opacity: 0; transition: all var(--dur-fast); }
.modal-veil.open .modal-box { transform: scale(1); opacity: 1; }
.modal-head { padding: 18px 24px; border-bottom: 0.5px solid var(--c-separator); display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { margin: 0; font-size: 18px; }
.modal-close { background: var(--c-fill); border: none; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--t-label); cursor: pointer; font-size: 12px; }
.modal-close:hover { background: var(--c-fill2); color: var(--t-primary); }
.modal-body { padding: 24px; }
</style>

<div class="sup-root">
    <div class="sup-wrap">

        <?php if(!empty($data['error'])): ?>
            <div class="sf-alert error" id="alert-error">
                <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
                <div style="flex:1;">
                    <div class="sf-alert-title">Error</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
                </div>
                <button class="modal-close" onclick="document.getElementById('alert-error').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        <?php if(!empty($data['success'])): ?>
            <div class="sf-alert success" id="alert-success">
                <i class="fa-solid fa-circle-check sf-alert-icon"></i>
                <div style="flex:1;">
                    <div class="sf-alert-title">Success</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div>
                </div>
                <button class="modal-close" onclick="document.getElementById('alert-success').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- Stat Cards Row -->
        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-truck-field"></i></div>
                <div>
                    <div class="stat-num"><?= number_format($totalSuppliers) ?></div>
                    <div class="stat-lbl">Total Suppliers</div>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="stat-num">Rs. <?= number_format($totalOutstanding, 2) ?></div>
                    <div class="stat-lbl">Total Payable Outstanding</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="stat-num"><?= number_format($owedSuppliersCount) ?></div>
                    <div class="stat-lbl">Accounts to Settle</div>
                </div>
            </div>
        </div>

        <div class="split-layout">
            <!-- Left Pane: List -->
            <div class="left-pane">
                <div class="pane-header">
                    <span>Suppliers</span>
                    <button class="sf-btn sf-btn-outline sf-btn-small" onclick="openModal('addSupplierModal')"><i class="fa-solid fa-plus"></i> Add</button>
                </div>
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="supplierSearch" class="search-input" placeholder="Search suppliers..." onkeyup="filterSuppliers()">
                </div>
                <div class="supplier-list" id="supplierList">
                    <?php if(empty($data['suppliers'])): ?>
                        <div style="padding: 30px; text-align: center; color: var(--t-label); font-size: 13px;">No suppliers registered.</div>
                    <?php else: foreach($data['suppliers'] as $s): ?>
                        <a href="<?= APP_URL ?>/supplier/index/<?= $s->id ?>" class="supplier-item <?= ($data['selected_supplier'] && $data['selected_supplier']->id == $s->id) ? 'active' : '' ?>">
                            <div>
                                <strong><?= htmlspecialchars($s->name) ?></strong>
                                <span class="text-sub"><?= htmlspecialchars($s->email ?: $s->phone ?: 'No contact details') ?></span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 10px; font-weight:600; text-transform:uppercase; color:var(--t-label);">Owed</span><br>
                                <span class="bal-text" style="color: <?= $s->outstanding_balance > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>;">
                                    Rs. <?= number_format($s->outstanding_balance, 2) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Right Pane: Details -->
            <div class="right-pane">
                <?php if(!$data['selected_supplier']): ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--t-label);">
                        <div style="font-size: 52px; margin-bottom: 16px; opacity: 0.3;"><i class="fa-solid fa-building"></i></div>
                        <p style="font-size: 15px; font-weight: 500;">Select a supplier to view details, ledger, and products.</p>
                    </div>
                <?php else: ?>
                    <?php $sup = $data['selected_supplier']; $stats = $data['stats']; ?>
                    <div class="right-header">
                        <div style="display: flex; gap: 16px; align-items: center;">
                            <div class="avatar-circle"><?= strtoupper(substr($sup->name, 0, 2)) ?></div>
                            <div>
                                <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight:700; color:var(--t-primary);">
                                    <?= htmlspecialchars($sup->name) ?>
                                </h2>
                                <div style="font-size: 13px; color: var(--t-secondary); display: flex; gap: 15px; font-weight:500;">
                                    <span><i class="fa-solid fa-phone" style="margin-right:4px;"></i> <?= htmlspecialchars($sup->phone ?: 'N/A') ?></span>
                                    <span><i class="fa-solid fa-envelope" style="margin-right:4px;"></i> <?= htmlspecialchars($sup->email ?: 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; color: var(--t-label); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Total Payable</div>
                            <div style="font-size: 24px; font-weight: 700; color: <?= $stats->outstanding > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>;">
                                Rs. <?= number_format($stats->outstanding, 2) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="tabs">
                        <button class="tab-btn active" onclick="switchTab('ledger')" id="btn_ledger">Activity Ledger</button>
                        <button class="tab-btn" onclick="switchTab('pos')" id="btn_pos">Purchase Orders</button>
                        <button class="tab-btn" onclick="switchTab('products')" id="btn_products">Products List</button>
                        <button class="tab-btn" onclick="switchTab('profile')" id="btn_profile">Profile Settings</button>
                    </div>

                    <!-- TAB 1: Activity Ledger -->
                    <div class="tab-content active" id="tab_ledger">
                        <div class="grid-3" style="margin-bottom: 20px;">
                            <div style="background:var(--c-surface2); padding:16px; border-radius:var(--r-md); border:0.5px solid var(--c-separator);">
                                <div style="font-size:11px; color:var(--t-label); text-transform:uppercase; font-weight:700;">Total Goods Billed</div>
                                <div style="font-size:18px; font-weight:700; color:var(--t-primary); margin-top:5px;">Rs. <?= number_format($stats->total_billed, 2) ?></div>
                            </div>
                            <div style="background:var(--c-green-light); padding:16px; border-radius:var(--r-md); border:0.5px solid rgba(52,199,89,0.3);">
                                <div style="font-size:11px; color:var(--c-green); text-transform:uppercase; font-weight:700;">Total Paid</div>
                                <div style="font-size:18px; font-weight:700; color:var(--c-green); margin-top:5px;">Rs. <?= number_format($stats->total_paid, 2) ?></div>
                            </div>
                            <div style="background:var(--c-surface2); padding:16px; border-radius:var(--r-md); border:0.5px solid var(--c-separator);">
                                <div style="font-size:11px; color:var(--t-label); text-transform:uppercase; font-weight:700;">Goods Returned</div>
                                <div style="font-size:18px; font-weight:700; color:var(--t-primary); margin-top:5px;">Rs. <?= number_format($stats->total_returned, 2) ?></div>
                            </div>
                        </div>

                        <div style="border: 0.5px solid var(--c-separator); border-radius: var(--r-md); overflow:hidden;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Ref / Desc</th>
                                        <th class="num-col">Dr (Paid/Ret)</th>
                                        <th class="num-col">Cr (Billed)</th>
                                        <th class="num-col">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($data['ledger'])): ?>
                                        <tr><td colspan="6" style="text-align: center; color: var(--t-label); padding: 20px;">No financial activity recorded.</td></tr>
                                    <?php else: foreach($data['ledger'] as $l): ?>
                                        <tr>
                                            <td style="color:var(--t-secondary); font-size:13px;"><?= date('M d, Y', strtotime($l->date)) ?></td>
                                            <td><strong><?= $l->type ?></strong></td>
                                            <td>
                                                <?php if($l->type == 'GRN'): ?>
                                                    <a href="<?= APP_URL ?>/grn/show/<?= $l->id ?>" target="_blank" style="color:var(--c-blue); font-weight:600; text-decoration:none;">
                                                        <?= htmlspecialchars($l->ref) ?> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px; margin-left:2px;"></i>
                                                    </a>
                                                <?php elseif($l->type == 'Supplier Return'): ?>
                                                    <a href="<?= APP_URL ?>/supplier-return/show/<?= $l->id ?>" target="_blank" style="color:var(--c-red); font-weight:600; text-decoration:none;">
                                                        <?= htmlspecialchars($l->ref) ?> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px; margin-left:2px;"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color:var(--t-primary);"><?= htmlspecialchars($l->ref) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="num-col" style="color:var(--c-green); font-weight:600;"><?= $l->debit > 0 ? 'Rs. ' . number_format($l->debit, 2) : '-' ?></td>
                                            <td class="num-col" style="font-weight:600;"><?= $l->credit > 0 ? 'Rs. ' . number_format($l->credit, 2) : '-' ?></td>
                                            <td class="num-col" style="font-weight:700; color: <?= $l->balance > 0 ? 'var(--c-red)' : 'var(--c-green)' ?>;">Rs. <?= number_format($l->balance, 2) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: Purchase Orders -->
                    <div class="tab-content" id="tab_pos">
                        <div style="border: 0.5px solid var(--c-separator); border-radius: var(--r-md); overflow:hidden;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Expected</th>
                                        <th>PO Number</th>
                                        <th>Status</th>
                                        <th class="num-col">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($data['pos'])): ?>
                                        <tr><td colspan="6" style="text-align: center; color: var(--t-label); padding: 20px;">No purchase orders.</td></tr>
                                    <?php else: foreach($data['pos'] as $po): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($po->po_date)) ?></td>
                                            <td style="color:var(--t-secondary);"><?= $po->expected_date ? date('M d, Y', strtotime($po->expected_date)) : 'N/A' ?></td>
                                            <td><strong><?= htmlspecialchars($po->po_number) ?></strong></td>
                                            <td><span class="sf-badge badge-<?= $po->status ?>"><?= $po->status ?></span></td>
                                            <td class="num-col" style="font-weight:700;">Rs. <?= number_format($po->total_amount, 2) ?></td>
                                            <td style="text-align:right;">
                                                <a href="<?= APP_URL ?>/purchase/show/<?= $po->id ?>" target="_blank" class="sf-btn sf-btn-outline sf-btn-small">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: Products -->
                    <div class="tab-content" id="tab_products">
                        <div style="border: 0.5px solid var(--c-separator); border-radius: var(--r-md); overflow:hidden;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Product Name</th>
                                        <th class="num-col">Last Cost</th>
                                        <th class="num-col">Sell Price</th>
                                        <th class="num-col">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($data['products'])): ?>
                                        <tr><td colspan="5" style="text-align: center; color: var(--t-label); padding: 20px;">No products assigned.</td></tr>
                                    <?php else: foreach($data['products'] as $p): ?>
                                        <tr>
                                            <td style="color:var(--t-secondary); font-family:var(--f-mono); font-size:13px;"><?= htmlspecialchars($p->sku ?: 'N/A') ?></td>
                                            <td><strong><?= htmlspecialchars($p->product_name) ?></strong></td>
                                            <td class="num-col" style="font-weight:600;">Rs. <?= number_format($p->cost_price, 2) ?></td>
                                            <td class="num-col" style="color:var(--c-blue); font-weight:600;">Rs. <?= number_format($p->price, 2) ?></td>
                                            <td class="num-col" style="font-weight:700; color: <?= $p->quantity_on_hand > 5 ? 'var(--t-primary)' : 'var(--c-orange)' ?>;">
                                                <?= number_format($p->quantity_on_hand) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: Profile -->
                    <div class="tab-content" id="tab_profile">
                        <form action="<?= APP_URL ?>/supplier/index/<?= $sup->id ?>" method="POST" style="max-width: 600px;">
                            <input type="hidden" name="action" value="update_supplier">
                            <input type="hidden" name="supplier_id" value="<?= $sup->id ?>">
                            
                            <div class="grid-2">
                                <div class="sf-group">
                                    <label>Company Name *</label>
                                    <input type="text" name="name" class="sf-input" value="<?= htmlspecialchars($sup->name) ?>" required>
                                </div>
                                <div class="sf-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="sf-input" value="<?= htmlspecialchars($sup->email) ?>">
                                </div>
                            </div>
                            <div class="grid-2">
                                <div class="sf-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone" class="sf-input" value="<?= htmlspecialchars($sup->phone) ?>">
                                </div>
                                <div class="sf-group">
                                    <label>Physical Address</label>
                                    <textarea name="address" class="sf-input" rows="2"><?= htmlspecialchars($sup->address) ?></textarea>
                                </div>
                            </div>
                            
                            <div style="margin-top: 10px; text-align: right;">
                                <button type="submit" class="sf-btn sf-btn-primary">Save Profile</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-veil" id="addSupplierModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Add New Supplier</h3>
            <button class="modal-close" onclick="closeModal('addSupplierModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form action="<?= APP_URL ?>/supplier" method="POST">
                <input type="hidden" name="action" value="add_supplier">
                <div class="sf-group">
                    <label>Company Name *</label>
                    <input type="text" name="name" class="sf-input" required>
                </div>
                <div class="sf-group">
                    <label>Email</label>
                    <input type="email" name="email" class="sf-input">
                </div>
                <div class="sf-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="sf-input">
                </div>
                <div class="sf-group">
                    <label>Address</label>
                    <textarea name="address" class="sf-input" rows="2"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                    <button type="button" class="sf-btn sf-btn-outline" onclick="closeModal('addSupplierModal')">Cancel</button>
                    <button type="submit" class="sf-btn sf-btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        const tabEl = document.getElementById('tab_' + tabName);
        const btnEl = document.getElementById('btn_' + tabName);
        if (tabEl) tabEl.classList.add('active');
        if (btnEl) btnEl.classList.add('active');
    }

    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    function filterSuppliers() {
        const query = document.getElementById('supplierSearch').value.toLowerCase();
        const items = document.querySelectorAll('.supplier-item');
        
        items.forEach(item => {
            const name = item.querySelector('strong').innerText.toLowerCase();
            const contact = item.querySelector('.text-sub').innerText.toLowerCase();
            if (name.includes(query) || contact.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
