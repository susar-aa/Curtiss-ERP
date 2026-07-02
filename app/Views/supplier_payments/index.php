<style>
    /* =====================================================
       MODERN AP BILLING PANEL — REDESIGNED UI
       ===================================================== */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --primary:       #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #eff6ff;
        --success:       #16a34a;
        --success-light: #f0fdf4;
        --danger:        #dc2626;
        --danger-light:  #fef2f2;
        --warning:       #d97706;
        --warning-light: #fffbeb;
        --slate-900:     #0f172a;
        --slate-800:     #1e293b;
        --slate-700:     #334155;
        --slate-600:     #475569;
        --slate-400:     #94a3b8;
        --slate-300:     #cbd5e1;
        --slate-200:     #e2e8f0;
        --slate-100:     #f1f5f9;
        --slate-50:      #f8fafc;
        --white:         #ffffff;
        --radius-sm:     6px;
        --radius-md:     10px;
        --radius-lg:     14px;
        --shadow-sm:     0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md:     0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);
        --font:          'Inter', system-ui, -apple-system, sans-serif;
    }

    .ap-wrapper {
        font-family: var(--font);
        color: var(--slate-800);
        box-sizing: border-box;
    }

    /* Tabs Layout */
    .payment-center-tabs {
        display: flex;
        border-bottom: 2px solid var(--slate-200);
        margin-bottom: 25px;
        gap: 5px;
    }
    .payment-tab-btn {
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        color: var(--slate-600);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .payment-tab-btn:hover {
        color: var(--slate-900);
        background: rgba(0,0,0,0.02);
    }
    .payment-tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    /* KPI Row */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    .stat-card {
        background: var(--white);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }
    .stat-card.payable::before { background: var(--warning); }
    .stat-card.general::before { background: var(--success); }

    .stat-title {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--slate-600);
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    .stat-val {
        font-size: 24px;
        font-weight: 800;
        color: var(--slate-900);
        font-family: monospace;
    }

    /* ── Header row: Supplier + Metadata Cards ── */
    .inv-header-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .customer-card, .inv-meta-card {
        flex: 1;
        min-width: 320px;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        overflow: visible;
        background: var(--white);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
    }

    .customer-card-header, .inv-meta-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 14px;
        background: var(--slate-800);
        color: var(--white);
        border-radius: var(--radius-md) var(--radius-md) 0 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .customer-card-body, .inv-meta-body {
        padding: 12px;
        position: relative;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .customer-search-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--slate-300);
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-family: var(--font);
        font-weight: 600;
        color: var(--slate-800);
        box-sizing: border-box;
        background: var(--slate-50);
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none;
    }
    .customer-search-input:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    .customer-address-area {
        width: 100%;
        flex: 1;
        min-height: 60px;
        padding: 8px 12px;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-sm);
        font-size: 11px;
        font-family: var(--font);
        color: var(--slate-600);
        resize: none;
        box-sizing: border-box;
        background: var(--slate-50);
    }

    .customer-outstanding {
        font-size: 12px;
        padding: 10px;
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }

    .customer-actions {
        display: flex;
        gap: 8px;
    }

    /* Autocomplete Dropdown list styling */
    .search-results {
        position: absolute;
        top: calc(100% - 2px);
        left: 12px;
        width: calc(100% - 24px);
        background: var(--white);
        border: 1px solid var(--slate-300);
        border-radius: var(--radius-sm);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: var(--shadow-md);
        padding: 0;
        margin: 0;
    }
    .search-results li {
        padding: 10px 14px;
        cursor: pointer;
        list-style: none;
        border-bottom: 1px solid var(--slate-100);
        font-size: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.1s;
    }
    .search-results li:last-child { border-bottom: none; }
    .search-results li:hover, .search-results li.highlighted {
        background: var(--primary) !important;
        color: var(--white) !important;
    }
    .search-results li:hover span, .search-results li.highlighted span {
        color: var(--white) !important;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 13px;
        font-family: var(--font);
        font-size: 12px;
        font-weight: 600;
        border-radius: var(--radius-sm);
        border: 1px solid var(--slate-300);
        background: var(--white);
        color: var(--slate-700);
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }
    .btn:hover { background: var(--slate-100); border-color: var(--slate-400); }
    .btn-primary { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .btn-primary:hover { background: var(--primary-hover); }
    .btn-success { background: var(--success); color: var(--white); border-color: var(--success); }
    .btn-success:hover { background: #15803d; }
    .btn-sm { padding: 4px 10px; font-size: 11px; }

    /* Forms */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .form-group label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--slate-600);
    }
    .form-control {
        padding: 8px 12px;
        border: 1px solid var(--slate-300);
        border-radius: var(--radius-sm);
        font-family: var(--font);
        font-size: 12px;
        color: var(--slate-800);
        background: var(--slate-50);
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    /* Allocation Workflow Card */
    .allocation-box {
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        padding: 16px;
        background: var(--slate-50);
        margin-top: 15px;
    }
    .allocations-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 12px;
    }
    .allocations-table th, .allocations-table td {
        padding: 8px 10px;
        border-bottom: 1px solid var(--slate-200);
        text-align: left;
    }
    .allocations-table th {
        font-weight: 700;
        color: var(--slate-600);
        text-transform: uppercase;
        font-size: 10px;
    }

    .cheque-details-box {
        border: 1.5px solid #f59e0b;
        border-radius: var(--radius-md);
        padding: 15px;
        background: #fffbeb;
        margin-top: 15px;
    }

    /* History Audit Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 12px;
    }
    .data-table th, .data-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--slate-200);
        text-align: left;
    }
    .data-table th {
        background: var(--slate-800);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 10.5px;
    }
    .data-table tbody tr:nth-child(even) {
        background: var(--slate-50);
    }
    .data-table tbody tr:hover {
        background: var(--primary-light);
    }
    .badge-method {
        padding: 3px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-method.method-Cash { background: #d1fae5; color: #065f46; }
    .badge-method.method-Cheque { background: #fef3c7; color: #d97706; }
    .badge-method.method-BankTransfer { background: #dbeafe; color: #2563eb; }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-Active { background: #d1fae5; color: #065f46; }
    .status-Reversed { background: #fee2e2; color: #991b1b; }

    .btn-action-small {
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--slate-200);
        background: var(--white);
        color: var(--slate-700);
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action-small:hover {
        background: var(--slate-100);
    }
    .btn-action-small.btn-danger {
        border-color: #f87171;
        color: #ef4444;
    }
    .btn-action-small.btn-danger:hover {
        background: #fef2f2;
    }

    .hidden {
        display: none !important;
    }
</style>

<div class="ap-wrapper">
    <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0 0 5px 0; font-weight: 800; color: var(--slate-900);">Supplier Payments Center</h2>
            <p style="margin: 0; color: var(--slate-600); font-size: 14px;">Audit-ready accounts payable (AP) billing settlements, credit applications, and ledger adjustments.</p>
        </div>
    </div>

    <!-- Notifications -->
    <?php if (!empty($data['error'])): ?>
        <div style="padding: 12px 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fca5a5; margin-bottom: 20px; font-size: 13px; font-weight: 500;">
            ⚠ <?= $data['error'] ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['success'])): ?>
        <div style="padding: 12px 15px; background: #d1fae5; color: #065f46; border-radius: 8px; border: 1px solid #6ee7b7; margin-bottom: 20px; font-size: 13px; font-weight: 500;">
            ✓ <?= $data['success'] ?>
        </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="stats-grid">
        <div class="stat-card payable">
            <div class="stat-title">Total Accounts Payable (Outstanding)</div>
            <div class="stat-val">
                Rs <?= number_format(array_sum(array_column($data['suppliers'], 'outstanding_balance')), 2) ?>
            </div>
        </div>
        <div class="stat-card general">
            <div class="stat-title">GL Cash & Bank Accounts</div>
            <div class="stat-val" style="font-size: 13px; font-family: inherit; font-weight: 600; line-height: 1.5; margin-top: 10px;">
                <?php foreach (array_slice($data['assets'], 0, 3) as $asset): ?>
                    • <?= htmlspecialchars($asset->account_name) ?>: <strong>Rs <?= number_format($asset->balance, 2) ?></strong><br>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="payment-center-tabs">
        <button class="payment-tab-btn active" id="tab-ap-btn" onclick="switchMainTab('ap')">
            🏭 Record Supplier Payment
        </button>
        <button class="payment-tab-btn" id="tab-history-btn" onclick="switchMainTab('history')">
            📜 Payouts History & Reversals
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB PANEL: RECORD SUPPLIER PAYMENT         -->
    <!-- ========================================== -->
    <div id="tab-ap" class="payment-tab-panel">
        <form action="<?= APP_URL ?>/supplierpayment/recordSupplierPayment" method="POST" id="supplierPayForm">
            <!-- ── Header Panel Row ── -->
            <div class="inv-header-row">
                <!-- Supplier Search Card -->
                <div class="customer-card">
                    <div class="customer-card-header">
                        <span><i class="ph ph-factory" style="margin-right:5px;"></i>Paid To (Supplier)</span>
                    </div>
                    <div class="customer-card-body">
                        <input type="hidden" name="supplier_id" id="form-supp-id" required>
                        <input type="text" id="supplierSearch" class="customer-search-input"
                               placeholder="Search by name, phone, address..."
                               autocomplete="off" required>
                        <ul id="supplierSearchResults" class="search-results"></ul>
                        <textarea id="supplierDetailsArea" class="customer-address-area"
                                  readonly placeholder="Supplier address and phone will appear here..."></textarea>
                    </div>
                </div>

                <!-- Supplier Account status card -->
                <div class="customer-card" id="supplierStatusCard" style="display: none; flex-direction: column;">
                    <div class="customer-card-header">
                        <span><i class="ph ph-cardholder" style="margin-right:5px;"></i>Supplier Account</span>
                    </div>
                    <div class="customer-card-body" style="display: flex; flex-direction: column; gap: 8px; justify-content: space-between; flex: 1;">
                        <div id="supplierOutstanding" class="customer-outstanding" style="margin-top:0;"></div>
                        <div id="supplierOptionsContainer" class="customer-actions" style="margin-top:auto; display:none;">
                            <a href="#" id="selected-supp-statement-btn" target="_blank" class="btn btn-sm" style="flex:1; justify-content:center;">
                                <i class="ph ph-file-text"></i> Statement
                            </a>
                            <a href="#" id="selected-supp-apply-credit" class="btn btn-sm btn-success" style="flex:1; justify-content:center; display:none;">
                                <i class="ph ph-plus-circle"></i> Apply Credit
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Voucher Details Card -->
                <div class="inv-meta-card">
                    <div class="inv-meta-card-header">
                        <i class="ph ph-receipt" style="margin-right:5px;"></i>Voucher Details
                    </div>
                    <div class="inv-meta-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Reference # / Voucher #</label>
                                <input type="text" name="reference" class="form-control" placeholder="Optional reference">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group">
                                <label>Debit Ledger Account (Accounts Payable) *</label>
                                <select name="ap_account_id" class="form-control" required style="background: var(--white);">
                                    <option value="<?= $data['ap_account'] ? $data['ap_account']->id : 18 ?>">
                                        <?= $data['ap_account'] ? $data['ap_account']->account_code . ' - ' . htmlspecialchars($data['ap_account']->account_name) : '2000 - Accounts Payable' ?>
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Credit Ledger Account (Cash/Bank) *</label>
                                <select name="asset_account_id" id="supp-asset-account" class="form-control" required style="background: var(--white);">
                                    <?php foreach ($data['assets'] as $asset): ?>
                                        <option value="<?= $asset->id ?>" data-code="<?= $asset->account_code ?>" data-parent="<?= $asset->parent_id ?>" <?= $asset->account_code === '1000' ? 'selected' : '' ?>>
                                            <?= $asset->account_code ?> - <?= htmlspecialchars($asset->account_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Payment Method *</label>
                            <select name="payment_method" id="supp-method-select" class="form-control" onchange="toggleChequeFields('supp')" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome workspace helper -->
            <div id="ap-welcome" style="text-align: center; padding: 60px 20px; color: var(--slate-400); background: var(--white); border: 1px dashed var(--slate-300); border-radius: var(--radius-lg);">
                <i class="ph ph-factory" style="font-size: 64px; color: var(--warning); opacity: 0.6; margin-bottom: 15px;"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--slate-700);">Select a supplier above to load AP invoice balances and record a payout.</h3>
                <p style="font-size: 13px; max-width: 450px; margin: 10px auto 0 auto;">Dedicated, audit-ready AP module allows auto-clearing GRNs (FIFO), manual line-by-line allocation, and advance payments.</p>
            </div>

            <!-- Workspace Fields -->
            <div id="ap-workspace-fields" class="hidden" style="background: var(--white); border: 1px solid var(--slate-200); padding: 20px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                
                <!-- Cheque Details Container -->
                <div id="supp-cheque-details" class="cheque-details-box hidden" style="margin-bottom: 15px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: #b45309; font-weight: 700;">Cheque Details</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                        <div class="form-group">
                            <label>Bank Name *</label>
                            <input type="text" name="cheque_bank" id="supp-chk-bank-input" class="form-control" placeholder="e.g. Commercial Bank">
                        </div>
                        <div class="form-group">
                            <label>Cheque Number *</label>
                            <input type="text" name="cheque_number" id="supp-chk-num-input" class="form-control" placeholder="e.g. 987654" pattern="\d{6}" maxlength="6" minlength="6" title="Cheque number must be exactly 6 digits." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group">
                            <label>Banking Date *</label>
                            <input type="date" name="cheque_date" id="supp-chk-date-input" class="form-control" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label>Payment Amount (Rs:) *</label>
                        <input type="number" step="0.01" name="amount" id="supp-amount-input" class="form-control" style="font-size: 16px; font-weight: 700; color: var(--warning);" required min="0.01" oninput="updateAllocationTotals()">
                    </div>
                    <div class="form-group">
                        <label>GRN Allocation Strategy *</label>
                        <select name="allocation_type" id="supp-allocation-type" class="form-control" onchange="toggleAllocationGrid('supp')" required>
                            <option value="auto">Automatic (FIFO Allocation)</option>
                            <option value="manual">Manual Line-by-Line Allocation</option>
                        </select>
                    </div>
                </div>

                <!-- Allocation Selection -->
                <div class="allocation-box">
                    <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: var(--slate-800);">GRN Allocation Workflow</h4>
                    
                    <div id="supp-manual-grid" class="hidden">
                        <p style="margin: 0; font-size: 11px; color: var(--slate-500);">Unpaid Goods Received Notes (GRN) under accounts payable. Enter the amount to settle for each record manually.</p>
                        
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--slate-200); border-radius: 8px; margin-top: 10px;">
                            <table class="allocations-table">
                                <thead>
                                    <tr>
                                        <th>GRN Number</th>
                                        <th>GRN Date</th>
                                        <th style="text-align: right;">Total Amount</th>
                                        <th style="text-align: right;">Balance Due</th>
                                        <th style="width: 120px; text-align: right;">Allocation</th>
                                    </tr>
                                </thead>
                                <tbody id="supp-grns-tbody">
                                    <!-- Injected via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; font-size: 13px; font-weight: 600;">
                        <span>Unallocated Amount: <span id="supp-unallocated-lbl" style="color: var(--danger); font-family: monospace;">Rs 0.00</span></span>
                        <span>Total Allocated: <span id="supp-allocated-lbl" style="color: var(--success); font-family: monospace;">Rs 0.00</span></span>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Notes / Memo</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Internal audit logs memo..."></textarea>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="font-size: 14px; font-weight: 700; height: 44px; justify-content: center; width: 100%;">
                        <i class="ph ph-shield-check" style="font-size: 18px;"></i> Post & Allocate Payout
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- TAB PANEL: PAYOUTS HISTORY & REVERSALS     -->
    <!-- ========================================== -->
    <div id="tab-history" class="payment-tab-panel hidden">
        <div style="background: var(--white); border: 1px solid var(--slate-200); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700;">GL Audit-Trail Payouts History</h3>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="history-search" class="form-control" style="width: 250px;" placeholder="Search history..." onkeyup="filterHistory()">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="data-table" id="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th style="text-align: right;">Amount</th>
                            <th>Status</th>
                            <th>Logged By</th>
                            <th style="text-align: center; width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['payments_history'])): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--slate-400); padding: 30px;">No payout logs found in current audit timeline.</td>
                            </tr>
                        <?php else: foreach ($data['payments_history'] as $ph): ?>
                            <tr class="history-row-el">
                                <td style="white-space: nowrap; font-weight: 500;"><?= date('Y-m-d', strtotime($ph->payment_date)) ?></td>
                                <td class="history-name-col" style="font-weight: 600;"><?= htmlspecialchars($ph->counterparty_name) ?></td>
                                <td><span class="badge-method method-<?= str_replace(' ', '', $ph->payment_method) ?>"><?= $ph->payment_method ?></span></td>
                                <td class="history-ref-col"><?= htmlspecialchars($ph->reference ?: '-') ?></td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: <?= $ph->status === 'Reversed' ? 'var(--danger)' : 'inherit' ?>">
                                    Rs <?= number_format($ph->amount, 2) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $ph->status ?>"><?= $ph->status ?></span>
                                </td>
                                <td><?= htmlspecialchars($ph->creator_name ?: 'System') ?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="<?= APP_URL ?>/supplierpayment/receipt/<?= $ph->id ?>" target="_blank" class="btn-action-small">
                                        🖨 Voucher
                                    </a>
                                    <?php if ($ph->status === 'Active'): ?>
                                        <button onclick="triggerReversal(<?= $ph->id ?>, <?= $ph->amount ?>)" class="btn-action-small btn-danger" style="cursor:pointer;">
                                            ↩ Reverse
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Global states
    let activeSupplierGRNs = [];
    let activeSupplierSearchIndex = -1;

    // Suppliers list injected from PHP
    const suppliers = [
        <?php foreach($data['suppliers'] as $s): ?>
        {
            id: "<?= $s->id ?>",
            name: <?= json_encode((string)($s->name ?? '')) ?>,
            phone: <?= json_encode((string)($s->phone ?? '')) ?>,
            address: <?= json_encode((string)($s->address ?? '')) ?>,
            outstanding: <?= floatval($s->outstanding_balance ?? 0) ?>
        },
        <?php endforeach; ?>
    ];

    // Switch main tabs
    function switchMainTab(tab) {
        document.querySelectorAll('.payment-tab-panel').forEach(panel => panel.classList.add('hidden'));
        document.querySelectorAll('.payment-tab-btn').forEach(btn => btn.classList.remove('active'));

        document.getElementById('tab-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab + '-btn').classList.add('active');
    }

    // Helper for HTML escaping
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Autocomplete Supplier Search
    function renderSupplierSearch(query) {
        const val = query.toLowerCase().trim();
        const resList = document.getElementById('supplierSearchResults');
        resList.innerHTML = '';
        if(!val) { resList.style.display = 'none'; return; }

        const filtered = suppliers.filter(s =>
            (s.name && s.name.toLowerCase().includes(val)) ||
            (s.phone && s.phone.toLowerCase().includes(val)) ||
            (s.address && s.address.toLowerCase().includes(val))
        ).slice(0, 10);

        if(filtered.length === 0) {
            const li = document.createElement('li');
            li.className = 'no-results';
            li.style.padding = '10px 14px';
            li.style.color = '#94a3b8';
            li.innerText = 'No suppliers found';
            resList.appendChild(li);
            resList.style.display = 'block';
            return;
        }

        filtered.forEach((supp, index) => {
            const li = document.createElement('li');
            li.setAttribute('data-index', index);
            
            const balanceVal = parseFloat(supp.outstanding) || 0;
            let balanceText = 'Rs ' + balanceVal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
            
            li.innerHTML = `
                <div style="font-weight:600; color:var(--slate-800);">${escapeHtml(supp.name)}</div>
                <div style="font-size:11px; color:var(--slate-500);">${supp.phone ? `<i class="ph ph-phone"></i> ` + escapeHtml(supp.phone) : ''}</div>
                <div style="font-size:11px; color:var(--slate-500);">${supp.address ? `<i class="ph ph-map-pin"></i> ` + escapeHtml(supp.address) : ''}</div>
                <div style="font-weight:700; color:${balanceVal > 0 ? 'var(--danger)' : 'var(--success)'};">Bal: ${balanceText}</div>
            `;
            li.addEventListener('click', () => { selectSupplier(supp); });
            resList.appendChild(li);
        });
        resList.style.display = 'block';
    }

    function highlightSupplierSearchItem(items) {
        items.forEach((item, index) => {
            if (index === activeSupplierSearchIndex) {
                item.classList.add('highlighted');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('highlighted');
            }
        });
    }

    function clearSupplierSelection() {
        document.getElementById('supplierSearch').value = '';
        document.getElementById('form-supp-id').value = '';
        document.getElementById('supplierDetailsArea').value = '';
        document.getElementById('supplierStatusCard').style.display = 'none';
        document.getElementById('supplierOutstanding').style.display = 'none';
        document.getElementById('supplierOptionsContainer').style.display = 'none';
        document.getElementById('ap-welcome').classList.remove('hidden');
        document.getElementById('ap-workspace-fields').classList.add('hidden');
        activeSupplierGRNs = [];
        document.getElementById('supp-grns-tbody').innerHTML = '';
        document.getElementById('supp-amount-input').value = '';
    }

    function selectSupplier(supp) {
        document.getElementById('supplierSearchResults').style.display = 'none';
        document.getElementById('supplierSearch').value = supp.name;
        document.getElementById('form-supp-id').value = supp.id;
        
        let details = '';
        if (supp.address) details += "Address: " + supp.address + "\n";
        if (supp.phone) details += "Phone: " + supp.phone;
        if (!details) details = "No address/phone details available for this supplier.";
        document.getElementById('supplierDetailsArea').value = details;

        const outDiv = document.getElementById('supplierOutstanding');
        const balVal = parseFloat(supp.outstanding) || 0;
        let balText = 'Rs ' + balVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        outDiv.style.display = 'block';
        if (balVal > 0) {
            outDiv.style.background = 'var(--danger-light)';
            outDiv.style.color = 'var(--danger)';
            outDiv.style.border = '1px solid #fecaca';
            outDiv.innerHTML = `<div><i class="ph ph-warning"></i> Outstanding Payable:</div><div style="font-size: 18px; font-weight: 800; font-family: monospace; margin-top:4px;">${balText}</div>`;
        } else {
            outDiv.style.background = 'var(--success-light)';
            outDiv.style.color = 'var(--success)';
            outDiv.style.border = '1px solid #bbf7d0';
            outDiv.innerHTML = `<div><i class="ph ph-check"></i> Account Clear:</div><div style="font-size: 18px; font-weight: 800; font-family: monospace; margin-top:4px;">${balText}</div>`;
        }

        document.getElementById('supplierOptionsContainer').style.display = 'flex';
        document.getElementById('supplierStatusCard').style.display = 'flex';
        
        document.getElementById('selected-supp-statement-btn').href = '<?= APP_URL ?>/supplierpayment/statement/' + supp.id;
        
        const creditBtn = document.getElementById('selected-supp-apply-credit');
        creditBtn.href = '<?= APP_URL ?>/supplierpayment/applyCredit/' + supp.id;
        creditBtn.style.display = balVal > 0 ? 'inline-flex' : 'none';

        const tbody = document.getElementById('supp-grns-tbody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading unpaid GRNs...</td></tr>';
        
        fetch('<?= APP_URL ?>/supplierpayment/getSupplierGRNsJson/' + supp.id)
            .then(res => res.json())
            .then(data => {
                activeSupplierGRNs = data;
                renderSupplierGRNsTable();
                
                document.getElementById('ap-welcome').classList.add('hidden');
                document.getElementById('ap-workspace-fields').classList.remove('hidden');
                
                document.getElementById('supp-amount-input').value = '';
                document.getElementById('supp-allocation-type').value = 'auto';
                toggleAllocationGrid('supp');
                updateAllocationTotals();
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Failed to load GRNs.</td></tr>';
            });
    }

    // Filter history
    function filterHistory() {
        const query = document.getElementById('history-search').value.toLowerCase();
        const rows = document.querySelectorAll('.history-row-el');
        rows.forEach(row => {
            const name = row.querySelector('.history-name-col').textContent.toLowerCase();
            const ref = row.querySelector('.history-ref-col').textContent.toLowerCase();
            if (name.includes(query) || ref.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Render supplier GRNs grid
    function renderSupplierGRNsTable() {
        const tbody = document.getElementById('supp-grns-tbody');
        tbody.innerHTML = '';
        if (activeSupplierGRNs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:var(--slate-400);">No unpaid GRNs for this supplier.</td></tr>';
            return;
        }

        activeSupplierGRNs.forEach(g => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${g.grn_number}</strong> ${g.receipt_number ? `(${g.receipt_number})` : ''}</td>
                <td>${g.grn_date}</td>
                <td style="text-align:right;">Rs ${parseFloat(g.total_amount).toFixed(2)}</td>
                <td style="text-align:right; font-weight:600; color:var(--danger);">Rs ${parseFloat(g.balance_due).toFixed(2)}</td>
                <td style="text-align:right;">
                    <input type="number" step="0.01" class="form-control supp-alloc-input" 
                           style="width: 100px; text-align: right; padding: 4px 8px; background: transparent;" 
                           name="allocations[${g.id}]" 
                           data-max="${g.balance_due}" 
                           placeholder="0.00" 
                           oninput="validateAndAllocate('supp', this)">
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Toggle allocation strategy grid visibility
    function toggleAllocationGrid(prefix) {
        const type = document.getElementById(prefix + '-allocation-type').value;
        const grid = document.getElementById(prefix + '-manual-grid');
        
        if (type === 'manual') {
            grid.classList.remove('hidden');
        } else {
            grid.classList.add('hidden');
        }
        updateAllocationTotals();
    }

    // Validate allocation input and recalculate
    function validateAndAllocate(prefix, input) {
        const max = parseFloat(input.getAttribute('data-max'));
        let val = parseFloat(input.value) || 0;
        
        if (val < 0) {
            input.value = '0.00';
            val = 0;
        }
        if (val > max) {
            input.value = max.toFixed(2);
            val = max;
        }

        updateAllocationTotals();
    }

    // Update running allocated / unallocated totals
    function updateAllocationTotals() {
        const suppTotalPay = parseFloat(document.getElementById('supp-amount-input').value) || 0;
        const suppAllocType = document.getElementById('supp-allocation-type').value;
        let suppAllocated = 0;

        if (suppAllocType === 'manual') {
            document.querySelectorAll('.supp-alloc-input').forEach(input => {
                suppAllocated += parseFloat(input.value) || 0;
            });
        }

        const suppUnallocated = Math.max(0, suppTotalPay - suppAllocated);
        document.getElementById('supp-allocated-lbl').innerText = 'Rs ' + suppAllocated.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('supp-unallocated-lbl').innerText = 'Rs ' + suppUnallocated.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    // Toggle cheque details
    function toggleChequeFields(prefix) {
        const method = document.getElementById(prefix + '-method-select').value;
        const cqBox = document.getElementById(prefix + '-cheque-details');
        const bankInput = document.getElementById(prefix + '-chk-bank-input');
        const numInput = document.getElementById(prefix + '-chk-num-input');
        const dateInput = document.getElementById(prefix + '-chk-date-input');

        if (method === 'Cheque') {
            cqBox.classList.remove('hidden');
            bankInput.required = true;
            numInput.required = true;
            dateInput.required = true;
        } else {
            cqBox.classList.add('hidden');
            bankInput.required = false;
            numInput.required = false;
            dateInput.required = false;
        }

        // Automatic Account Selection based on Method
        const assetSelect = document.getElementById(prefix + '-asset-account');
        if (assetSelect && typeof allAssetAccounts !== 'undefined' && allAssetAccounts.length > 0) {
            // Find the ID of the 1600 Bank parent account
            let bankParentId = '';
            allAssetAccounts.forEach(acc => {
                if (acc.code === '1600') {
                    bankParentId = acc.value;
                }
            });

            // Filter accounts based on payment method
            let filtered = [];
            if (method === 'Cash') {
                filtered = allAssetAccounts.filter(acc => acc.code === '1000');
            } else if (method === 'Cheque') {
                filtered = allAssetAccounts.filter(acc => acc.code === '1010');
            } else if (method === 'Bank Transfer') {
                filtered = allAssetAccounts.filter(acc => acc.parent === bankParentId);
            } else {
                filtered = allAssetAccounts;
            }

            // Rebuild options
            assetSelect.innerHTML = '';
            if (method === 'Bank Transfer') {
                const emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.text = '-- Select Bank Account --';
                assetSelect.appendChild(emptyOpt);
            }
            filtered.forEach(acc => {
                const opt = document.createElement('option');
                opt.value = acc.value;
                opt.text = acc.text;
                opt.setAttribute('data-code', acc.code);
                opt.setAttribute('data-parent', acc.parent);
                assetSelect.appendChild(opt);
            });

            // Auto-select first available option or leave empty for bank transfer
            if (method === 'Bank Transfer') {
                assetSelect.value = '';
            } else if (filtered.length > 0) {
                assetSelect.selectedIndex = 0;
            }
        }
    }

    // Trigger reversal action
    function triggerReversal(id, amount) {
        const msg = `Are you absolutely sure you want to REVERSE this supplier payment of Rs ${parseFloat(amount).toLocaleString()}?\n\nThis will restore the unpaid balance of the allocated GRNs, bounce associated cheques, and create reversing journal entries in the General Ledger.`;
        if (confirm(msg)) {
            window.location.href = `<?= APP_URL ?>/supplierpayment/reverseSupplierPayment/${id}`;
        }
    }

    // Initial page load bindings
    let allAssetAccounts = [];
    document.addEventListener('DOMContentLoaded', () => {
        const assetSelect = document.getElementById('supp-asset-account');
        if (assetSelect) {
            for (let i = 0; i < assetSelect.options.length; i++) {
                const opt = assetSelect.options[i];
                allAssetAccounts.push({
                    value: opt.value,
                    text: opt.text,
                    code: opt.getAttribute('data-code'),
                    parent: opt.getAttribute('data-parent')
                });
            }
        }
        toggleChequeFields('supp');

        // Form Submit Validation Checks
        const form = document.getElementById('supplierPayForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const method = document.getElementById('supp-method-select').value;
                const assetSelect = document.getElementById('supp-asset-account');
                
                if (method === 'Bank Transfer' && (!assetSelect || assetSelect.value === '')) {
                    e.preventDefault();
                    alert('Please select a bank account for the bank transfer.');
                    return false;
                }

                if (method === 'Cheque') {
                    const chkNum = document.getElementById('supp-chk-num-input').value;
                    const chkDate = document.getElementById('supp-chk-date-input').value;
                    
                    if (!/^\d{6}$/.test(chkNum)) {
                        e.preventDefault();
                        alert('Cheque number must be exactly 6 numeric digits.');
                        return false;
                    }
                    
                    const today = new Date();
                    today.setHours(0,0,0,0);
                    const selectDate = new Date(chkDate);
                    selectDate.setHours(0,0,0,0);
                    
                    if (selectDate < today) {
                        e.preventDefault();
                        alert('Cheque date cannot be in the past.');
                        return false;
                    }
                }
            });
        }

        // Supplier Search input listener
        const supplierSearch = document.getElementById('supplierSearch');
        if (supplierSearch) {
            supplierSearch.addEventListener('input', function() {
                activeSupplierSearchIndex = -1;
                if (!this.value.trim()) {
                    clearSupplierSelection();
                } else {
                    renderSupplierSearch(this.value);
                }
            });

            supplierSearch.addEventListener('keydown', function(e) {
                const resList = document.getElementById('supplierSearchResults');
                if (resList.style.display !== 'block') return;
                
                const items = resList.querySelectorAll('li:not(.no-results)');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeSupplierSearchIndex++;
                    if (activeSupplierSearchIndex >= items.length) activeSupplierSearchIndex = 0;
                    highlightSupplierSearchItem(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeSupplierSearchIndex--;
                    if (activeSupplierSearchIndex < 0) activeSupplierSearchIndex = items.length - 1;
                    highlightSupplierSearchItem(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeSupplierSearchIndex >= 0 && activeSupplierSearchIndex < items.length) {
                        items[activeSupplierSearchIndex].click();
                    }
                }
            });
        }

        // Dismiss dropdown list on outside clicks
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'supplierSearch') {
                const resList = document.getElementById('supplierSearchResults');
                if (resList) resList.style.display = 'none';
            }
        });
    });
</script>
