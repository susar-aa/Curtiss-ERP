<style>
    /* Tabs System */
    .tabs-container {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--mac-border);
        padding-bottom: 12px;
        margin-bottom: 25px;
    }
    .tab-btn {
        background: transparent;
        border: 1px solid transparent;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        padding: 8px 18px;
        border-radius: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tab-btn:hover {
        background: rgba(0, 0, 0, 0.04);
        color: var(--text-main);
    }
    .tab-btn.active {
        background: #0066cc;
        color: #fff;
        border-color: #0066cc;
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
    }
    @media (prefers-color-scheme: dark) {
        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.05);
        }
    }

    /* KPI Summary Cards */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    .kpi-card {
        padding: 20px;
        border-radius: 12px;
        border: 1px solid var(--mac-border);
        background: var(--mac-bg);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .kpi-val {
        font-size: 22px;
        font-weight: 700;
        margin-top: 4px;
        font-family: monospace;
    }
    .kpi-label {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Color variations for KPIs */
    .kpi-customer .kpi-icon { background: #e8f5e9; color: #2e7d32; }
    .kpi-supplier .kpi-icon { background: #fff3e0; color: #ef6c00; }
    .kpi-cheques .kpi-icon { background: #e3f2fd; color: #1565c0; }
    @media (prefers-color-scheme: dark) {
        .kpi-customer .kpi-icon { background: rgba(46, 125, 50, 0.15); }
        .kpi-supplier .kpi-icon { background: rgba(239, 108, 0, 0.15); }
        .kpi-cheques .kpi-icon { background: rgba(21, 101, 192, 0.15); }
    }

    /* Split Dashboard Layout */
    .split-layout {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 25px;
        align-items: start;
    }
    @media (max-width: 1100px) {
        .split-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Forms & Panel Styles */
    .form-pane {
        border-radius: 12px;
        border: 1px solid var(--mac-border);
        padding: 24px;
        background: var(--mac-bg);
    }
    .form-pane h3 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
    }
    .form-control {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--mac-border);
        border-radius: 6px;
        background: transparent;
        color: var(--text-main);
        font-size: 13px;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #0066cc;
        outline: none;
    }
    .btn-submit {
        width: 100%;
        padding: 10px 16px;
        background: #0066cc;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
        box-shadow: 0 4px 10px rgba(0, 102, 204, 0.15);
        transition: opacity 0.2s;
    }
    .btn-submit:hover {
        opacity: 0.9;
    }

    /* Cheque Fieldset slider */
    .cheque-details-box {
        display: none;
        background: rgba(0, 0, 0, 0.02);
        padding: 15px;
        border-radius: 8px;
        border: 1px dashed var(--mac-border);
        margin-bottom: 16px;
        animation: slideDown 0.25s ease-out;
    }
    @media (prefers-color-scheme: dark) {
        .cheque-details-box { background: rgba(255, 255, 255, 0.02); }
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* List Pane styles */
    .list-pane {
        border-radius: 12px;
        border: 1px solid var(--mac-border);
        padding: 24px;
        background: var(--mac-bg);
    }
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }
    .list-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
    }
    .search-filter {
        width: 200px;
        padding: 6px 12px;
        font-size: 12px;
        border: 1px solid var(--mac-border);
        border-radius: 6px;
        background: transparent;
        color: var(--text-main);
    }

    /* Table enhancements */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table th, .data-table td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid var(--mac-border);
    }
    .data-table th {
        background-color: rgba(0, 0, 0, 0.01);
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    .data-table tr:hover td {
        background-color: rgba(0, 0, 0, 0.01);
    }
    @media (prefers-color-scheme: dark) {
        .data-table tr:hover td { background-color: rgba(255, 255, 255, 0.01); }
    }
    .badge-method {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .method-Cash { background: #e8f5e9; color: #2e7d32; }
    .method-BankTransfer { background: #e3f2fd; color: #1565c0; }
    .method-Cheque { background: #fff3e0; color: #ef6c00; }
    @media (prefers-color-scheme: dark) {
        .method-Cash { background: rgba(46,125,50,0.15); }
        .method-BankTransfer { background: rgba(21,101,192,0.15); }
        .method-Cheque { background: rgba(239,108,0,0.15); }
    }

    .hidden {
        display: none !important;
    }
</style>

<!-- Header Section -->
<div style="margin-bottom: 25px;">
    <h2 style="margin: 0 0 5px 0; font-weight: 700;">Payment & Collections Center</h2>
    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Manage and record dual-entry customer payments (collections) and supplier expenses.</p>
</div>

<!-- Alert notifications -->
<?php if (!empty($data['error'])): ?>
    <div style="padding: 12px 15px; background: #ffebee; color: #c62828; border-radius: 8px; border: 1px solid rgba(198,40,40,0.15); margin-bottom: 20px; font-size: 13px; font-weight: 500;">
        <i class="ph ph-warning-circle" style="vertical-align: middle; font-size: 16px; margin-right: 6px;"></i> <?= $data['error'] ?>
    </div>
<?php endif; ?>
<?php if (!empty($data['success'])): ?>
    <div style="padding: 12px 15px; background: #e8f5e9; color: #2e7d32; border-radius: 8px; border: 1px solid rgba(46,125,50,0.15); margin-bottom: 20px; font-size: 13px; font-weight: 500;">
        <i class="ph ph-check-circle" style="vertical-align: middle; font-size: 16px; margin-right: 6px;"></i> <?= $data['success'] ?>
    </div>
<?php endif; ?>

<!-- KPI Status Summary Widgets -->
<?php
    // Calculate dashboard aggregate sums
    $totalCustAmount = array_sum(array_column($data['customer_payments'], 'amount'));
    $totalSuppAmount = array_sum(array_column($data['supplier_payments'], 'amount'));
?>
<div class="kpi-row">
    <div class="kpi-card kpi-customer">
        <div class="kpi-icon"><i class="ph ph-hand-coins"></i></div>
        <div>
            <div class="kpi-label">Customer Receipts</div>
            <div class="kpi-val">Rs <?= number_format($totalCustAmount, 2) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-supplier">
        <div class="kpi-icon"><i class="ph ph-truck"></i></div>
        <div>
            <div class="kpi-label">Supplier Payments</div>
            <div class="kpi-val">Rs <?= number_format($totalSuppAmount, 2) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-cheques">
        <div class="kpi-icon"><i class="ph ph-file-text"></i></div>
        <div>
            <div class="kpi-label">Receivable / Payable Accounts</div>
            <div class="kpi-val" style="font-size: 13px; font-weight: 600; margin-top: 8px; font-family: inherit;">
                AR: Code <?= $data['ar_account'] ? $data['ar_account']->account_code : '1200' ?><br>
                AP: Code <?= $data['ap_account'] ? $data['ap_account']->account_code : '2000' ?>
            </div>
        </div>
    </div>
</div>

<!-- Tabs selector -->
<div class="tabs-container">
    <button class="tab-btn active" id="btn-customer" onclick="switchTab('customer')">
        <i class="ph ph-user-shared"></i> Customer collections
    </button>
    <button class="tab-btn" id="btn-supplier" onclick="switchTab('supplier')">
        <i class="ph ph-factory"></i> Supplier Payments
    </button>
</div>

<!-- ============================================== -->
<!-- TAB CONTENT 1: CUSTOMER COLLECTIONS -->
<!-- ============================================== -->
<div id="tab-content-customer" class="tab-panel">
    <div class="split-layout">
        <!-- Form Pane -->
        <div class="form-pane">
            <h3><i class="ph ph-plus-circle text-primary"></i> Record Collection</h3>
            <form action="<?= APP_URL ?>/payment/recordCustomerPayment" method="POST" id="customerPaymentForm">
                <div class="form-group">
                    <label for="cust-select">Select Customer *</label>
                    <select name="customer_id" id="cust-select" class="form-control" required>
                        <option value="">-- Choose Customer --</option>
                        <?php foreach($data['customers'] as $cust): ?>
                            <option value="<?= $cust->id ?>" data-balance="<?= $cust->outstanding_balance ?>">
                                <?= htmlspecialchars($cust->name) ?> (Outstanding: Rs <?= number_format($cust->outstanding_balance, 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cust-amount">Amount (Rs) *</label>
                    <input type="number" step="0.01" name="amount" id="cust-amount" class="form-control" placeholder="0.00" required min="0.01">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="cust-date">Payment Date *</label>
                        <input type="date" name="payment_date" id="cust-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="cust-method">Method *</label>
                        <select name="payment_method" id="cust-method" class="form-control" onchange="toggleChequeFields('customer')" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <!-- Conditional Cheque Fields -->
                <div id="customer-cheque-box" class="cheque-details-box">
                    <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Cheque Information</h4>
                    <div class="form-group">
                        <label for="cust-chk-bank">Bank Name *</label>
                        <input type="text" name="cheque_bank" id="cust-chk-bank" class="form-control" placeholder="e.g. Commercial Bank">
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="cust-chk-num">Cheque Number *</label>
                            <input type="text" name="cheque_number" id="cust-chk-num" class="form-control" placeholder="e.g. 123456">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="cust-chk-date">Cheque Date *</label>
                            <input type="date" name="cheque_date" id="cust-chk-date" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cust-ref">Reference / Memo</label>
                    <input type="text" name="reference" id="cust-ref" class="form-control" placeholder="Receipt no., memo, etc.">
                </div>

                <!-- Ledger Accounts selection (COA) -->
                <div style="background: rgba(0, 102, 204, 0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(0, 102, 204, 0.1); margin-top: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: #0066cc; font-weight: 700;">Double Entry GL Accounts</h4>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Debit Account (Cash/Bank) *</label>
                        <select name="asset_account_id" class="form-control" required>
                            <?php foreach ($data['assets'] as $asset): ?>
                                <option value="<?= $asset->id ?>" <?= $asset->account_code === '1000' ? 'selected' : '' ?>>
                                    <?= $asset->account_code ?> - <?= htmlspecialchars($asset->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Credit Account (Accounts Receivable) *</label>
                        <select name="ar_account_id" class="form-control" required>
                            <option value="<?= $data['ar_account'] ? $data['ar_account']->id : 11 ?>">
                                <?= $data['ar_account'] ? $data['ar_account']->account_code . ' - ' . htmlspecialchars($data['ar_account']->account_name) : '1200 - Accounts Receivable' ?>
                            </option>
                            <?php foreach ($data['assets'] as $asset): ?>
                                <?php if ($data['ar_account'] && $asset->id === $data['ar_account']->id) continue; ?>
                                <option value="<?= $asset->id ?>">
                                    <?= $asset->account_code ?> - <?= htmlspecialchars($asset->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="ph ph-check-square"></i> Record Collection
                </button>
            </form>
        </div>

        <!-- List Pane -->
        <div class="list-pane">
            <div class="list-header">
                <h3>Customer Payment Records</h3>
                <input type="text" id="cust-search" class="search-filter" placeholder="Search collections..." onkeyup="filterCollectionTable('customer')">
            </div>

            <div style="overflow-x: auto;">
                <table class="data-table" id="table-customer-payments">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th style="text-align: right;">Amount</th>
                            <th>GL J-Entry</th>
                            <th>Responsible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['customer_payments'])): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 25px;">No collections recorded yet.</td>
                            </tr>
                        <?php else: foreach ($data['customer_payments'] as $cp): ?>
                            <tr class="row-collection">
                                <td style="white-space: nowrap; font-weight: 500;"><?= date('Y-m-d', strtotime($cp->payment_date)) ?></td>
                                <td class="col-entity" style="font-weight: 600;"><?= htmlspecialchars($cp->customer_name) ?></td>
                                <td>
                                    <span class="badge-method method-<?= str_replace(' ', '', $cp->payment_method) ?>"><?= $cp->payment_method ?></span>
                                </td>
                                <td class="col-ref"><?= htmlspecialchars($cp->reference ?: '-') ?></td>
                                <td style="text-align: right; font-weight: 600; font-family: monospace;">Rs <?= number_format($cp->amount, 2) ?></td>
                                <td>
                                    <?php if ($cp->journal_entry_id): ?>
                                        <a href="<?= APP_URL ?>/accounting/history/<?= $cp->journal_entry_id ?>" style="color: #0066cc; font-weight: 600; text-decoration: none;">
                                            <i class="ph ph-link"></i> JE #<?= $cp->journal_entry_id ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic;">No Entry</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-muted); font-size: 12px;"><?= htmlspecialchars($cp->responsible_person ?: 'System') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- TAB CONTENT 2: SUPPLIER PAYMENTS -->
<!-- ============================================== -->
<div id="tab-content-supplier" class="tab-panel hidden">
    <div class="split-layout">
        <!-- Form Pane -->
        <div class="form-pane">
            <h3><i class="ph ph-minus-circle text-warning"></i> Record Payment</h3>
            <form action="<?= APP_URL ?>/payment/recordSupplierPayment" method="POST" id="supplierPaymentForm">
                <div class="form-group">
                    <label for="supp-select">Select Supplier *</label>
                    <select name="supplier_id" id="supp-select" class="form-control" required>
                        <option value="">-- Choose Supplier --</option>
                        <?php foreach($data['suppliers'] as $supp): ?>
                            <option value="<?= $supp->id ?>" data-balance="<?= $supp->outstanding_balance ?>">
                                <?= htmlspecialchars($supp->name) ?> (Outstanding: Rs <?= number_format($supp->outstanding_balance, 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="supp-amount">Amount (Rs) *</label>
                    <input type="number" step="0.01" name="amount" id="supp-amount" class="form-control" placeholder="0.00" required min="0.01">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="supp-date">Payment Date *</label>
                        <input type="date" name="payment_date" id="supp-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="supp-method">Method *</label>
                        <select name="payment_method" id="supp-method" class="form-control" onchange="toggleChequeFields('supplier')" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <!-- Conditional Cheque Fields -->
                <div id="supplier-cheque-box" class="cheque-details-box">
                    <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Cheque Information</h4>
                    <div class="form-group">
                        <label for="supp-chk-bank">Bank Name *</label>
                        <input type="text" name="cheque_bank" id="supp-chk-bank" class="form-control" placeholder="e.g. Commercial Bank">
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="supp-chk-num">Cheque Number *</label>
                            <input type="text" name="cheque_number" id="supp-chk-num" class="form-control" placeholder="e.g. 987654">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="supp-chk-date">Cheque Date *</label>
                            <input type="date" name="cheque_date" id="supp-chk-date" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="supp-ref">Reference / Memo</label>
                    <input type="text" name="reference" id="supp-ref" class="form-control" placeholder="Voucher no., invoice link, etc.">
                </div>

                <!-- Ledger Accounts selection (COA) -->
                <div style="background: rgba(239, 108, 0, 0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(239, 108, 0, 0.1); margin-top: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: #ef6c00; font-weight: 700;">Double Entry GL Accounts</h4>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Debit Account (Accounts Payable) *</label>
                        <select name="ap_account_id" class="form-control" required>
                            <option value="<?= $data['ap_account'] ? $data['ap_account']->id : 18 ?>">
                                <?= $data['ap_account'] ? $data['ap_account']->account_code . ' - ' . htmlspecialchars($data['ap_account']->account_name) : '2000 - Accounts Payable' ?>
                            </option>
                            <?php foreach ($data['assets'] as $asset): ?>
                                <option value="<?= $asset->id ?>">
                                    <?= $asset->account_code ?> - <?= htmlspecialchars($asset->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Credit Account (Cash/Bank) *</label>
                        <select name="asset_account_id" class="form-control" required>
                            <?php foreach ($data['assets'] as $asset): ?>
                                <option value="<?= $asset->id ?>" <?= $asset->account_code === '1000' ? 'selected' : '' ?>>
                                    <?= $asset->account_code ?> - <?= htmlspecialchars($asset->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="background: #ef6c00; box-shadow: 0 4px 10px rgba(239, 108, 0, 0.15);">
                    <i class="ph ph-check-square"></i> Record Payment
                </button>
            </form>
        </div>

        <!-- List Pane -->
        <div class="list-pane">
            <div class="list-header">
                <h3>Supplier Payment Records</h3>
                <input type="text" id="supp-search" class="search-filter" placeholder="Search payments..." onkeyup="filterCollectionTable('supplier')">
            </div>

            <div style="overflow-x: auto;">
                <table class="data-table" id="table-supplier-payments">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th style="text-align: right;">Amount</th>
                            <th>GL J-Entry</th>
                            <th>Responsible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['supplier_payments'])): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 25px;">No payments recorded yet.</td>
                            </tr>
                        <?php else: foreach ($data['supplier_payments'] as $sp): ?>
                            <tr class="row-collection">
                                <td style="white-space: nowrap; font-weight: 500;"><?= date('Y-m-d', strtotime($sp->expense_date)) ?></td>
                                <td class="col-entity" style="font-weight: 600;"><?= htmlspecialchars($sp->supplier_name) ?></td>
                                <td class="col-ref"><?= htmlspecialchars($sp->reference ?: '-') ?></td>
                                <td><?= htmlspecialchars($sp->description ?: '-') ?></td>
                                <td style="text-align: right; font-weight: 600; font-family: monospace;">Rs <?= number_format($sp->amount, 2) ?></td>
                                <td>
                                    <?php if ($sp->journal_entry_id): ?>
                                        <a href="<?= APP_URL ?>/accounting/history/<?= $sp->journal_entry_id ?>" style="color: #0066cc; font-weight: 600; text-decoration: none;">
                                            <i class="ph ph-link"></i> JE #<?= $sp->journal_entry_id ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic;">No Entry</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-muted); font-size: 12px;"><?= htmlspecialchars($sp->responsible_person ?: 'System') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching engine
    function switchTab(tab) {
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

        document.getElementById('tab-content-' + tab).classList.remove('hidden');
        document.getElementById('btn-' + tab).classList.add('active');

        // Store tab preference in sessionStorage
        sessionStorage.setItem('payments_active_tab', tab);
    }

    // Toggle cheque field boxes conditionally
    function toggleChequeFields(type) {
        const method = document.getElementById(type + '-method').value;
        const box = document.getElementById(type + '-cheque-box');
        const bankInput = document.getElementById(type + '-chk-bank');
        const numInput = document.getElementById(type + '-chk-num');
        const dateInput = document.getElementById(type + '-chk-date');

        if (method === 'Cheque') {
            box.style.display = 'block';
            bankInput.required = true;
            numInput.required = true;
            dateInput.required = true;
        } else {
            box.style.display = 'none';
            bankInput.required = false;
            numInput.required = false;
            dateInput.required = false;
        }
    }

    // Table quick searches
    function filterCollectionTable(type) {
        const query = document.getElementById(type + '-search').value.toLowerCase();
        const rows = document.querySelectorAll('#table-' + type + '-payments tbody tr.row-collection');

        rows.forEach(row => {
            const entity = row.querySelector('.col-entity').textContent.toLowerCase();
            const ref = row.querySelector('.col-ref').textContent.toLowerCase();
            if (entity.includes(query) || ref.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initial state loading
    document.addEventListener('DOMContentLoaded', () => {
        // Toggle initial cheque forms state
        toggleChequeFields('customer');
        toggleChequeFields('supplier');

        // Restore active tab if saved
        const activeTab = sessionStorage.getItem('payments_active_tab');
        if (activeTab === 'supplier') {
            switchTab('supplier');
        } else {
            switchTab('customer');
        }
    });
</script>
