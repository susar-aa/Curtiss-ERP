<style>
    /* =====================================================
       UNIFIED AP PAYMENTS PANEL — MODERN UI
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

    /* ── Unified Payment Panel Card ── */
    .payment-panel {
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        overflow: visible;
        background: var(--white);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
    }

    .payment-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 18px;
        background: var(--slate-800);
        color: var(--white);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .payment-panel-body {
        padding: 20px;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
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

    /* Autocomplete Dropdown list styling */
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
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
        padding: 8px 16px;
        font-family: var(--font);
        font-size: 13px;
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

    /* Allocation Box & Tables */
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

    .hidden {
        display: none !important;
    }

    /* Modal Popups Overlay & Cards */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.2s ease-out;
    }
    .modal-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        width: 100%;
        max-width: 400px;
        padding: 24px;
        text-align: center;
        animation: scaleIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: 1px solid var(--slate-200);
    }
    .modal-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        margin: 0 auto 16px auto;
    }
    .modal-icon.success {
        background: var(--success-light);
        color: var(--success);
        border: 2px solid var(--success);
    }
    .modal-icon.error {
        background: var(--danger-light);
        color: var(--danger);
        border: 2px solid var(--danger);
    }
    .modal-icon.warning {
        background: var(--warning-light);
        color: var(--warning);
        border: 2px solid var(--warning);
    }
    .modal-icon.primary {
        background: var(--primary-light);
        color: var(--primary);
        border: 2px solid var(--primary);
    }
    .modal-icon.danger {
        background: var(--danger-light);
        color: var(--danger);
        border: 2px solid var(--danger);
    }
    .modal-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--slate-900);
        margin: 0 0 10px 0;
    }
    .modal-text {
        font-size: 13px;
        color: var(--slate-600);
        margin: 0 0 20px 0;
        line-height: 1.5;
    }
    .receipt-summary-box {
        background: var(--slate-50);
        border: 1px dashed var(--slate-300);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 20px;
        text-align: left;
    }
    .receipt-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 12px;
    }
    .receipt-row:last-child {
        margin-bottom: 0;
    }
    .receipt-row.total-row {
        border-top: 1.5px solid var(--slate-300);
        padding-top: 8px;
        margin-top: 8px;
        font-weight: 700;
        font-size: 14px;
        color: var(--slate-900);
    }
    .receipt-label {
        color: var(--slate-500);
    }
    .receipt-value {
        color: var(--slate-800);
        font-weight: 600;
    }
    .modal-actions {
        display: flex;
        gap: 12px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes scaleIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="ap-wrapper">
    <!-- Notifications & Receipt Modal Popups -->
    <?php if (!empty($data['error'])): ?>
        <div id="error-modal" class="modal-overlay">
            <div class="modal-card">
                <div class="modal-icon error">⚠</div>
                <h3 class="modal-title" style="color: var(--danger);">Error</h3>
                <p class="modal-text"><?= htmlspecialchars($data['error']) ?></p>
                <div class="modal-actions" style="justify-content: center;">
                    <button type="button" onclick="closeModal('error-modal')" class="btn btn-danger" style="min-width: 120px; justify-content: center; background: var(--danger); color: var(--white); border-color: var(--danger);">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($data['success'])): ?>
        <?php if ($data['success'] === 'Supplier payment recorded successfully!' && !empty($data['payment_details'])): 
            $payment = $data['payment_details'];
        ?>
            <div id="receipt-modal" class="modal-overlay">
                <div class="modal-card">
                    <div class="modal-icon success">✓</div>
                    <h3 class="modal-title">Payment Recorded Successfully!</h3>
                    
                    <div class="receipt-summary-box">
                        <div class="receipt-row">
                            <span class="receipt-label">Voucher / Ref #</span>
                            <span class="receipt-value" style="font-weight: 700;"><?= htmlspecialchars($payment->reference) ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Date</span>
                            <span class="receipt-value"><?= htmlspecialchars($payment->payment_date) ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Paid To</span>
                            <span class="receipt-value" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($payment->supplier_name) ?></span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Method</span>
                            <span class="receipt-value"><?= htmlspecialchars($payment->payment_method) ?></span>
                        </div>
                        <div class="receipt-row total-row">
                            <span class="receipt-label">Amount Paid</span>
                            <span class="receipt-value">Rs <?= number_format($payment->amount, 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="printFullReceipt(<?= $payment->id ?>)" class="btn btn-primary" style="flex: 1; justify-content: center;">
                            <i class="ph ph-printer"></i> Print Receipt
                        </button>
                        <button type="button" onclick="closeModal('receipt-modal')" class="btn" style="flex: 1; justify-content: center;">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div id="general-success-modal" class="modal-overlay">
                <div class="modal-card">
                    <div class="modal-icon success">✓</div>
                    <h3 class="modal-title">Success</h3>
                    <p class="modal-text"><?= htmlspecialchars($data['success']) ?></p>
                    <div class="modal-actions" style="justify-content: center;">
                        <button type="button" onclick="closeModal('general-success-modal')" class="btn btn-primary" style="min-width: 120px; justify-content: center;">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Reusable Dynamic Transaction Details Modal (View/Print Receipt) -->
    <div id="dynamic-receipt-modal" class="modal-overlay" style="display: none;">
        <div class="modal-card" style="max-width: 500px;">
            <div class="modal-icon success" id="dynamic-receipt-icon">✓</div>
            <h3 class="modal-title" id="dynamic-receipt-title">Transaction Details</h3>
            
            <div class="receipt-summary-box" id="dynamic-receipt-content" style="max-height: 380px; overflow-y: auto;">
                <!-- Content injected dynamically -->
            </div>
            
            <div class="modal-actions">
                <button type="button" id="dynamic-receipt-print-btn" class="btn btn-primary" style="flex: 1; justify-content: center;">
                    <i class="ph ph-printer"></i> Print Receipt
                </button>
                <button type="button" onclick="closeModal('dynamic-receipt-modal')" class="btn" style="flex: 1; justify-content: center;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.45fr 0.55fr; gap: 20px; align-items: stretch; margin-top: 15px; height: 650px;">
        <form action="<?= APP_URL ?>/supplierpayment/recordSupplierPayment" method="POST" id="supplierPayForm" style="margin: 0; display: flex; flex-direction: column; height: 100%;">
            <div class="payment-panel" style="flex: 1; display: flex; flex-direction: column; height: 100%; overflow: hidden;">
                <div class="payment-panel-header">
                    <span>Record Supplier Payment</span>
                </div>
            
            <div class="payment-panel-body" style="flex: 1; overflow-y: auto;">
                <!-- Row 1: Supplier Search & Status details -->
                <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Search Supplier *</label>
                        <input type="hidden" name="supplier_id" id="form-supp-id" required>
                        <input type="text" id="supplierSearch" class="form-control"
                               placeholder="Type to search supplier by name, phone, address..."
                               autocomplete="off" required>
                        <ul id="supplierSearchResults" class="search-results"></ul>
                    </div>
                    <div class="form-group">
                        <label>Supplier Contact Info</label>
                        <input type="text" id="supplierDetailsArea" class="form-control" readonly placeholder="Supplier details will appear here...">
                    </div>
                    <div class="form-group">
                        <label>Outstanding Payable</label>
                        <div id="supplierOutstanding" class="form-control" style="background: var(--slate-100); font-weight: 700; color: var(--slate-600); display: flex; align-items: center; justify-content: center; height: 35px; border-radius: var(--radius-sm); border: 1px solid var(--slate-300);">
                            Rs 0.00
                        </div>
                    </div>
                </div>

                <!-- Row 2: Payment Parameters -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Reference # / Voucher #</label>
                        <input type="text" name="reference" class="form-control" placeholder="Optional reference">
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

                <!-- Row 3: Ledger Accounts -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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

                <!-- Workspace Fields (Shown only when supplier is selected) -->
                <div id="ap-workspace-fields" class="hidden">
                    
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
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
                    <div class="allocation-box hidden" id="supp-allocation-box">
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

                <!-- Welcome helper -->
                <div id="ap-welcome" style="text-align: center; padding: 60px 20px; color: var(--slate-400); border: 1px dashed var(--slate-300); border-radius: var(--radius-lg); margin-top: 15px;">
                    <i class="ph ph-factory" style="font-size: 64px; color: var(--warning); opacity: 0.6; margin-bottom: 15px;"></i>
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--slate-700);">Select a supplier above to load GRN balances.</h3>
                </div>
            </div>
        </div>
    </form>

    <!-- Right Panel: Supplier History Card -->
    <div class="payment-panel" id="supplier-history-panel" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
        <div class="payment-panel-header" style="background: var(--slate-700); display: flex; justify-content: space-between; align-items: center;">
            <span>Supplier History</span>
            <div style="display: flex; gap: 8px;">
                <a href="#" id="btn-view-profile" target="_blank" class="btn" style="padding: 4px 10px; font-size: 11px; background: rgba(255,255,255,0.15); color: var(--white); border-color: rgba(255,255,255,0.25); display: inline-flex; align-items: center; gap: 4px; pointer-events: none; opacity: 0.5; text-decoration: none;">
                    <i class="ph ph-user"></i> View Profile
                </a>
                <a href="#" id="btn-view-statement" target="_blank" class="btn" style="padding: 4px 10px; font-size: 11px; background: rgba(255,255,255,0.15); color: var(--white); border-color: rgba(255,255,255,0.25); display: inline-flex; align-items: center; gap: 4px; pointer-events: none; opacity: 0.5; text-decoration: none;">
                    <i class="ph ph-file-text"></i> View Statement
                </a>
            </div>
        </div>
        <div class="payment-panel-body" id="supplier-history-body" style="flex-grow: 1; overflow-y: auto; display: flex; flex-direction: column; justify-content: center; align-items: center; color: var(--slate-400); padding: 15px; box-sizing: border-box; width: 100%;">
            <i class="ph ph-clock-counter-clockwise" style="font-size: 48px; opacity: 0.5; margin-bottom: 10px;"></i>
            <span style="font-size: 12px;">Select a supplier to view history.</span>
        </div>
    </div>
</div>
</div>

<script>
    // Global states
    let activeSupplierGRNs = [];
    let activeSupplierSearchIndex = -1;
    let activeSupplierHistory = [];

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
        
        const outDiv = document.getElementById('supplierOutstanding');
        outDiv.innerText = 'Rs 0.00';
        outDiv.style.background = 'var(--slate-100)';
        outDiv.style.color = 'var(--slate-600)';
        outDiv.style.borderColor = 'var(--slate-300)';

        document.getElementById('ap-welcome').classList.remove('hidden');
        document.getElementById('ap-workspace-fields').classList.add('hidden');
        activeSupplierGRNs = [];
        document.getElementById('supp-grns-tbody').innerHTML = '';
        document.getElementById('supp-amount-input').value = '';

        // Disable buttons
        const btnProfile = document.getElementById('btn-view-profile');
        btnProfile.removeAttribute('href');
        btnProfile.style.pointerEvents = 'none';
        btnProfile.style.opacity = '0.5';

        const btnStatement = document.getElementById('btn-view-statement');
        btnStatement.removeAttribute('href');
        btnStatement.style.pointerEvents = 'none';
        btnStatement.style.opacity = '0.5';

        // Reset history body
        const histBody = document.getElementById('supplier-history-body');
        histBody.style.justifyContent = 'center';
        histBody.style.alignItems = 'center';
        histBody.innerHTML = `
            <i class="ph ph-clock-counter-clockwise" style="font-size: 48px; opacity: 0.5; margin-bottom: 10px;"></i>
            <span style="font-size: 12px;">Select a supplier to view history.</span>
        `;
    }

    function selectSupplier(supp) {
        document.getElementById('supplierSearchResults').style.display = 'none';
        document.getElementById('supplierSearch').value = supp.name;
        document.getElementById('form-supp-id').value = supp.id;
        
        let details = '';
        if (supp.phone) details += supp.phone;
        if (supp.address) details += (details ? ', ' : '') + supp.address;
        if (!details) details = "No contact details available.";
        document.getElementById('supplierDetailsArea').value = details;

        const outDiv = document.getElementById('supplierOutstanding');
        const balVal = parseFloat(supp.outstanding) || 0;
        let balText = 'Rs ' + balVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        if (balVal > 0) {
            outDiv.style.background = 'var(--danger-light)';
            outDiv.style.color = 'var(--danger)';
            outDiv.style.borderColor = '#fca5a5';
        } else {
            outDiv.style.background = 'var(--success-light)';
            outDiv.style.color = 'var(--success)';
            outDiv.style.borderColor = '#bbf7d0';
        }
        outDiv.innerText = balText;

        // Enable buttons
        const btnProfile = document.getElementById('btn-view-profile');
        btnProfile.href = '<?= APP_URL ?>/supplier/index/' + supp.id;
        btnProfile.style.pointerEvents = 'auto';
        btnProfile.style.opacity = '1';

        const btnStatement = document.getElementById('btn-view-statement');
        btnStatement.href = '<?= APP_URL ?>/report/viewer/supplier_statement?supplier=' + supp.id + '&start_date=&end_date=';
        btnStatement.style.pointerEvents = 'auto';
        btnStatement.style.opacity = '1';

        // Fetch history
        fetchSupplierHistory(supp.id);

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

    function fetchSupplierHistory(supplierId) {
        const historyBody = document.getElementById('supplier-history-body');
        historyBody.innerHTML = `
            <div style="text-align: center; padding: 40px 0; color: var(--slate-400);">
                <i class="ph ph-circle-notch animate-spin" style="font-size: 32px; margin-bottom: 10px; display: inline-block;"></i>
                <div>Loading transaction history...</div>
            </div>
        `;
        
        fetch('<?= APP_URL ?>/supplierpayment/getSupplierHistoryJson/' + supplierId)
            .then(res => res.json())
            .then(data => {
                activeSupplierHistory = data;
                renderSupplierHistory(data);
            })
            .catch(err => {
                console.error(err);
                historyBody.innerHTML = `
                    <div style="text-align: center; padding: 40px 0; color: var(--danger);">
                        <i class="ph ph-x-circle" style="font-size: 32px; margin-bottom: 10px; display: inline-block;"></i>
                        <div>Failed to load history.</div>
                    </div>
                `;
            });
    }

    function renderSupplierHistory(history) {
        const historyBody = document.getElementById('supplier-history-body');
        if (!history || history.length === 0) {
            historyBody.style.justifyContent = 'center';
            historyBody.style.alignItems = 'center';
            historyBody.innerHTML = `
                <div style="text-align: center; padding: 80px 20px; color: var(--slate-400);">
                    <i class="ph ph-clock-counter-clockwise" style="font-size: 48px; opacity: 0.5; margin-bottom: 10px; display: inline-block;"></i>
                    <div style="font-size: 12px;">No transaction history found for this supplier.</div>
                </div>
            `;
            return;
        }

        historyBody.style.justifyContent = 'flex-start';
        historyBody.style.alignItems = 'stretch';

        let html = '<div style="display: flex; flex-direction: column; gap: 10px; width: 100%;">';
        history.forEach(item => {
            let typeColor = 'var(--slate-600)';
            let amtColor = 'var(--slate-800)';
            let amtText = '';
            
            if (item.type === 'Payment' || item.type === 'Expense') {
                typeColor = 'var(--success)';
                amtColor = 'var(--success)';
                amtText = '-Rs ' + parseFloat(item.debit).toFixed(2);
            } else if (item.type === 'Supplier Return') {
                typeColor = 'var(--primary)';
                amtColor = 'var(--primary)';
                amtText = '-Rs ' + parseFloat(item.debit).toFixed(2);
            } else if (item.type === 'GRN') {
                typeColor = 'var(--warning)';
                amtColor = 'var(--slate-800)';
                amtText = '+Rs ' + parseFloat(item.credit).toFixed(2);
            }

            const balFormatted = parseFloat(item.balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            html += `
                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-sm); padding: 10px 14px; display: flex; flex-direction: column; gap: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700; font-size: 10px; text-transform: uppercase; color: ${typeColor}; background: ${typeColor}15; padding: 2px 6px; border-radius: 4px;">${escapeHtml(item.type)}</span>
                        <span style="font-size: 11px; color: var(--slate-400); font-family: monospace;">${item.date}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-size: 12px; font-weight: 500; color: var(--slate-800); max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(item.ref)}">${escapeHtml(item.ref)}</span>
                        <span style="font-size: 12px; font-weight: 700; color: ${amtColor}; font-family: monospace;">${amtText}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--slate-500); border-top: 1px dashed var(--slate-200); padding-top: 4px; margin-top: 2px; align-items: center;">
                        <span>Running Balance: <strong style="font-family: monospace; color: var(--slate-700);">Rs ${balFormatted}</strong></span>
                        <div style="display: flex; gap: 8px;">
                            <a href="javascript:void(0)" onclick="viewLedgerItem('${item.type}', ${item.id})" style="color: var(--primary); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                <i class="ph ph-eye"></i> View
                            </a>
                            <a href="javascript:void(0)" onclick="printLedgerItem('${item.type}', ${item.id})" style="color: var(--slate-600); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                <i class="ph ph-printer"></i> Print
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        historyBody.innerHTML = html;
    }

    function viewLedgerItem(type, id) {
        const item = activeSupplierHistory.find(x => x.type === type && x.id == id);
        if (!item) return;
        
        const modal = document.getElementById('dynamic-receipt-modal');
        const title = document.getElementById('dynamic-receipt-title');
        const icon = document.getElementById('dynamic-receipt-icon');
        const content = document.getElementById('dynamic-receipt-content');
        const printBtn = document.getElementById('dynamic-receipt-print-btn');
        
        // Show loading state
        content.innerHTML = `
            <div style="text-align: center; padding: 20px 0; color: var(--slate-400);">
                <i class="ph ph-circle-notch animate-spin" style="font-size: 24px; margin-bottom: 8px; display: inline-block;"></i>
                <div>Loading details...</div>
            </div>
        `;
        printBtn.style.display = 'none';
        modal.style.display = 'flex';
        
        if (type === 'Payment') {
            icon.innerText = '✓';
            icon.className = 'modal-icon success';
            title.innerText = 'Payment Receipt Details';
            
            fetch('<?= APP_URL ?>/supplierpayment/getPaymentDetailsJson/' + id)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        content.innerHTML = `<div style="color: var(--danger); font-weight: 600;">${data.message}</div>`;
                        return;
                    }
                    const payment = data.payment;
                    const allocations = data.allocations;
                    
                    let allocHtml = '';
                    if (allocations && allocations.length > 0) {
                        allocHtml = `
                            <div style="margin-top: 15px; border-top: 1px solid var(--slate-200); padding-top: 10px;">
                                <h4 style="margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">GRN Allocations</h4>
                                <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid var(--slate-200); color: var(--slate-500); text-align: left;">
                                            <th style="padding: 4px 0;">GRN #</th>
                                            <th style="padding: 4px 0; text-align: right;">Allocated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        allocations.forEach(a => {
                            allocHtml += `
                                <tr style="border-bottom: 1px dotted var(--slate-100);">
                                    <td style="padding: 6px 0; color: var(--slate-700); font-weight: 500;">${escapeHtml(a.grn_number)}</td>
                                    <td style="padding: 6px 0; text-align: right; font-family: monospace; font-weight: 600; color: var(--success);">Rs ${parseFloat(a.amount).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        allocHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        allocHtml = `
                            <div style="margin-top: 15px; border-top: 1px solid var(--slate-200); padding-top: 10px; color: var(--slate-400); font-size: 11px; text-align: center;">
                                No allocations recorded (Advance Payment)
                            </div>
                        `;
                    }
                    
                    content.innerHTML = `
                        <div class="receipt-row">
                            <span class="receipt-label">Voucher / Ref #</span>
                            <span class="receipt-value" style="font-weight: 700;">${escapeHtml(payment.reference)}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Date</span>
                            <span class="receipt-value">${payment.payment_date}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Paid To</span>
                            <span class="receipt-value" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escapeHtml(payment.supplier_name)}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Method</span>
                            <span class="receipt-value">${escapeHtml(payment.payment_method)}</span>
                        </div>
                        ${payment.payment_method === 'Cheque' ? `
                            <div class="receipt-row" style="background: var(--warning-light); padding: 4px 8px; border-radius: 4px; margin-top: 4px;">
                                <span class="receipt-label" style="color: var(--warning);">Cheque Details</span>
                                <span class="receipt-value" style="color: var(--warning); font-size: 11px;">${escapeHtml(payment.cheque_bank)} - ${escapeHtml(payment.cheque_number)} (${payment.cheque_date})</span>
                            </div>
                        ` : ''}
                        <div class="receipt-row">
                            <span class="receipt-label">Status</span>
                            <span class="receipt-value" style="color: ${payment.status === 'Active' ? 'var(--success)' : 'var(--danger)'};">${escapeHtml(payment.status)}</span>
                        </div>
                        ${payment.notes ? `
                            <div class="receipt-row" style="margin-top: 4px;">
                                <span class="receipt-label">Notes</span>
                                <span class="receipt-value" style="font-style: italic; font-weight: normal; color: var(--slate-500);">${escapeHtml(payment.notes)}</span>
                            </div>
                        ` : ''}
                        <div class="receipt-row total-row">
                            <span class="receipt-label">Amount Paid</span>
                            <span class="receipt-value" style="font-family: monospace;">Rs ${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                        ${allocHtml}
                    `;
                    
                    printBtn.style.display = 'flex';
                    printBtn.onclick = function() { printFullReceipt(payment.id); };
                })
                .catch(err => {
                    console.error(err);
                    content.innerHTML = `<div style="color: var(--danger);">Failed to load payment details.</div>`;
                });
        } else if (type === 'GRN') {
            icon.innerText = '⛟';
            icon.className = 'modal-icon warning';
            title.innerText = 'Goods Receipt Note';
            content.innerHTML = `
                <div class="receipt-row">
                    <span class="receipt-label">GRN Reference</span>
                    <span class="receipt-value" style="font-weight: 700;">${escapeHtml(item.ref)}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Date</span>
                    <span class="receipt-value">${item.date}</span>
                </div>
                <div class="receipt-row total-row">
                    <span class="receipt-label">GRN Total</span>
                    <span class="receipt-value" style="color: var(--slate-900); font-family: monospace;">Rs ${parseFloat(item.credit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <p style="font-size: 11px; color: var(--slate-500); margin-bottom: 10px;">For full line-by-line item details, view the full record.</p>
                    <a href="<?= APP_URL ?>/grn/show/${id}" target="_blank" class="btn btn-primary" style="display: inline-flex; justify-content: center; width: 100%;">
                        <i class="ph ph-arrow-square-out"></i> View Full GRN Details
                    </a>
                </div>
            `;
        } else if (type === 'Supplier Return') {
            icon.innerText = '↩';
            icon.className = 'modal-icon primary';
            title.innerText = 'Supplier Return Note';
            content.innerHTML = `
                <div class="receipt-row">
                    <span class="receipt-label">Return Reference</span>
                    <span class="receipt-value" style="font-weight: 700;">${escapeHtml(item.ref)}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Date</span>
                    <span class="receipt-value">${item.date}</span>
                </div>
                <div class="receipt-row total-row">
                    <span class="receipt-label">Return Total</span>
                    <span class="receipt-value" style="color: var(--primary); font-family: monospace;">Rs ${parseFloat(item.debit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <p style="font-size: 11px; color: var(--slate-500); margin-bottom: 10px;">For line-by-line item returns details, view the full record.</p>
                    <a href="<?= APP_URL ?>/supplier-return/show/${id}" target="_blank" class="btn btn-primary" style="display: inline-flex; justify-content: center; width: 100%;">
                        <i class="ph ph-arrow-square-out"></i> View Full Return Details
                    </a>
                </div>
            `;
        } else if (type === 'Expense') {
            icon.innerText = '💸';
            icon.className = 'modal-icon danger';
            title.innerText = 'Expense Details';
            content.innerHTML = `
                <div class="receipt-row">
                    <span class="receipt-label">Expense Ref / Memo</span>
                    <span class="receipt-value" style="font-weight: 700;">${escapeHtml(item.ref)}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Date</span>
                    <span class="receipt-value">${item.date}</span>
                </div>
                <div class="receipt-row total-row">
                    <span class="receipt-label">Expense Amount</span>
                    <span class="receipt-value" style="color: var(--danger); font-family: monospace;">Rs ${parseFloat(item.debit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
            `;
        }
    }

    function printLedgerItem(type, id) {
        if (type === 'Payment') {
            printFullReceipt(id);
        } else if (type === 'GRN') {
            window.open('<?= APP_URL ?>/grn/show/' + id, '_blank');
        } else if (type === 'Supplier Return') {
            window.open('<?= APP_URL ?>/supplier-return/show/' + id, '_blank');
        } else {
            alert('Print option not available for this ledger type.');
        }
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
        const box = document.getElementById(prefix + '-allocation-box');
        const manualGrid = document.getElementById(prefix + '-manual-grid');
        
        if (type === 'manual') {
            box.classList.remove('hidden');
            if (manualGrid) manualGrid.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
            if (manualGrid) manualGrid.classList.add('hidden');
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

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function printFullReceipt(id) {
        const printWindow = window.open('<?= APP_URL ?>/supplierpayment/receipt/' + id, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>
