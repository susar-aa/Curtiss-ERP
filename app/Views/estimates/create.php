<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   ESTIMATES & QUOTATIONS PANEL SYSTEM — DESIGN SYSTEM
   ============================================================ */

:root {
    --qb-bg:           #f8fafc;
    --qb-surface:      #ffffff;
    --qb-surface-2:    #f1f5f9;
    --qb-primary:      #0284c7;
    --qb-primary-dark: #0369a1;
    --qb-primary-light:#e0f2fe;
    
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
    padding: 10px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--qb-shadow-sm);
}

.qb-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.qb-title-icon {
    width: 36px;
    height: 36px;
    background: var(--qb-primary-light);
    color: var(--qb-primary);
    border-radius: var(--qb-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.qb-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--qb-slate-900);
    margin: 0;
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
    box-shadow: 0 1px 2px 0 rgba(2, 132, 199, 0.3);
}
.qb-btn-primary:hover {
    background: var(--qb-primary-dark);
}

/* ---- Dual Card Top Info Section ---- */
.qb-meta-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 12px;
}

.qb-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 14px 16px;
    box-shadow: var(--qb-shadow-sm);
}

.qb-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--qb-slate-100);
}

.qb-card-title {
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--qb-slate-500);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ---- Form Controls ---- */
.qb-field-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.qb-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--qb-slate-600);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.qb-input, .qb-select {
    width: 100%;
    padding: 7px 10px;
    font-size: 12.5px;
    font-family: var(--qb-font);
    color: var(--qb-slate-900);
    background: #ffffff;
    border: 1px solid var(--qb-slate-300);
    border-radius: var(--qb-radius);
    outline: none;
    transition: all 0.15s ease;
}

.qb-input:focus, .qb-select:focus {
    border-color: var(--qb-primary);
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.qb-input[readonly] {
    background-color: var(--qb-slate-100);
    color: var(--qb-slate-600);
    cursor: default;
}

.qb-customer-strip {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.qb-customer-details {
    background: var(--qb-slate-50);
    border: 1px dashed var(--qb-slate-200);
    border-radius: var(--qb-radius);
    padding: 8px 12px;
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 38px;
}

.qb-customer-info {
    color: var(--qb-slate-600);
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.qb-customer-info span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Customer Balance Badges */
.qb-balance-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.02em;
    white-space: nowrap;
}
.qb-balance-badge.payable {
    background: var(--qb-rose-50);
    color: var(--qb-rose-600);
    border: 1px solid #fecdd3;
}
.qb-balance-badge.advance {
    background: var(--qb-emerald-50);
    color: var(--qb-emerald-700);
    border: 1px solid #a7f3d0;
}
.qb-balance-badge.clear {
    background: var(--qb-slate-100);
    color: var(--qb-slate-600);
    border: 1px solid var(--qb-slate-200);
}

.qb-doc-strip {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
}

/* ---- Product Search Bar & Dropdown ---- */
.qb-search-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 10px 14px;
    box-shadow: var(--qb-shadow-sm);
    position: relative;
}

.qb-search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.qb-search-icon {
    position: absolute;
    left: 12px;
    color: var(--qb-slate-400);
    font-size: 13px;
    pointer-events: none;
}

.qb-search-input {
    width: 100%;
    padding: 9px 90px 9px 34px;
    font-size: 13px;
    font-family: var(--qb-font);
    background: var(--qb-slate-50);
    border: 1px solid var(--qb-slate-300);
    border-radius: var(--qb-radius);
    outline: none;
    transition: all 0.15s ease;
}

.qb-search-input:focus {
    background: #ffffff;
    border-color: var(--qb-primary);
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.qb-search-badge {
    position: absolute;
    right: 10px;
    background: var(--qb-slate-200);
    color: var(--qb-slate-600);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    font-family: var(--qb-mono);
    pointer-events: none;
}

/* Dropdown list for suggestions */
.qb-suggestions-list {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--qb-slate-300);
    border-radius: var(--qb-radius);
    box-shadow: var(--qb-shadow-md);
    max-height: 280px;
    overflow-y: auto;
    z-index: 100;
    display: none;
}

.qb-suggestion-item {
    padding: 9px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--qb-slate-100);
    cursor: pointer;
    transition: background 0.1s;
}

.qb-suggestion-item:last-child {
    border-bottom: none;
}

.qb-suggestion-item:hover, .qb-suggestion-item.active {
    background: var(--qb-primary-light);
}

.qb-suggestion-left {
    display: flex;
    flex-direction: column;
}

.qb-suggestion-name {
    font-weight: 600;
    font-size: 12.5px;
    color: var(--qb-slate-900);
}

.qb-suggestion-sku {
    font-size: 11px;
    color: var(--qb-slate-500);
    font-family: var(--qb-mono);
}

.qb-suggestion-price {
    font-weight: 700;
    font-family: var(--qb-mono);
    color: var(--qb-emerald-700);
    font-size: 12.5px;
}

/* ---- Items Table ---- */
.qb-table-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    box-shadow: var(--qb-shadow-sm);
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 240px;
}

.qb-table-scroll {
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
    background: var(--qb-slate-100);
    color: var(--qb-slate-600);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 9px 12px;
    border-bottom: 1px solid var(--qb-slate-200);
    position: sticky;
    top: 0;
    z-index: 10;
}

.qb-table td {
    padding: 7px 12px;
    border-bottom: 1px solid var(--qb-slate-100);
    vertical-align: middle;
}

.qb-table tbody tr:hover {
    background: #f8fafc;
}

.qb-table-input {
    width: 100%;
    padding: 6px 8px;
    font-size: 12px;
    font-family: var(--qb-font);
    border: 1px solid var(--qb-slate-200);
    border-radius: 4px;
    outline: none;
    transition: border-color 0.15s ease;
    background: transparent;
}

.qb-table-input:focus {
    border-color: var(--qb-primary);
    background: #ffffff;
}

.qb-table-input.num {
    text-align: right;
    font-family: var(--qb-mono);
    font-weight: 600;
}

.qb-table-total {
    text-align: right;
    font-family: var(--qb-mono);
    font-weight: 700;
    color: var(--qb-slate-800);
}

.qb-row-del {
    background: transparent;
    border: none;
    color: var(--qb-slate-400);
    cursor: pointer;
    font-size: 13px;
    width: 26px;
    height: 26px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.1s;
}

.qb-row-del:hover {
    background: var(--qb-rose-50);
    color: var(--qb-rose-600);
}

/* Empty Placeholder */
.qb-empty-state {
    padding: 40px 20px;
    text-align: center;
    color: var(--qb-slate-400);
}
.qb-empty-state i {
    font-size: 32px;
    margin-bottom: 8px;
    opacity: 0.6;
}

/* ---- Bottom Section ---- */
.qb-bottom-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 12px;
}

.qb-notes-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 12px 16px;
    box-shadow: var(--qb-shadow-sm);
    display: flex;
    flex-direction: column;
}

.qb-textarea {
    width: 100%;
    height: 76px;
    padding: 8px 10px;
    font-size: 12px;
    font-family: var(--qb-font);
    border: 1px solid var(--qb-slate-300);
    border-radius: var(--qb-radius);
    resize: none;
    outline: none;
}
.qb-textarea:focus {
    border-color: var(--qb-primary);
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.qb-summary-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 12px 16px;
    box-shadow: var(--qb-shadow-sm);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.qb-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12.5px;
    color: var(--qb-slate-600);
    padding: 3px 0;
}

.qb-summary-row.grand {
    border-top: 2px solid var(--qb-slate-200);
    padding-top: 8px;
    margin-top: 4px;
}

.qb-grand-total {
    font-size: 20px;
    font-weight: 800;
    font-family: var(--qb-mono);
    color: var(--qb-emerald-600);
}

/* ---- Modal ---- */
.qb-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}
.qb-modal-overlay.active {
    opacity: 1;
    pointer-events: auto;
}
.qb-modal {
    background: #ffffff;
    border-radius: var(--qb-radius-lg);
    border: 1px solid var(--qb-slate-200);
    box-shadow: var(--qb-shadow-md);
    width: 100%;
    max-width: 440px;
    padding: 20px;
}
</style>

<div class="qb-wrapper">
    <form action="<?= APP_URL ?>/estimate/create" method="POST" id="estimateForm" class="qb-container">
        
        <!-- ═══ HEADER BAR ═══ -->
        <div class="qb-header">
            <div class="qb-title-group">
                <div class="qb-title-icon">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div>
                    <h1 class="qb-title">Create Estimate / Quotation</h1>
                    <p class="qb-subtitle">Draft official pricing quotations for clients & potential customers</p>
                </div>
            </div>

            <div class="qb-header-actions">
                <a href="<?= APP_URL ?>/estimate" class="qb-btn qb-btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Back to Quotes
                </a>
                <button type="button" class="qb-btn qb-btn-ghost" onclick="toggleShortcutsModal()">
                    <i class="fa-solid fa-keyboard"></i> Shortcuts
                </button>
                <button type="submit" class="qb-btn qb-btn-primary" id="btnSaveEstimate">
                    <i class="fa-solid fa-check"></i> Save Estimate (Ctrl+S)
                </button>
            </div>
        </div>

        <?php if(!empty($data['error'])): ?>
        <div style="background: var(--qb-rose-50); border: 1px solid #fecdd3; color: var(--qb-rose-600); padding: 10px 14px; border-radius: var(--qb-radius); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($data['error']) ?>
        </div>
        <?php endif; ?>

        <!-- ═══ TOP METADATA CARDS ═══ -->
        <div class="qb-meta-grid">
            
            <!-- Customer Card -->
            <div class="qb-card">
                <div class="qb-card-header">
                    <span class="qb-card-title">
                        <i class="fa-solid fa-user-tie"></i> Customer / Client Details
                    </span>
                    <span id="customerBalanceIndicator" class="qb-balance-badge clear">
                        <i class="fa-solid fa-circle-notch"></i> Select a customer
                    </span>
                </div>

                <div class="qb-customer-strip">
                    <div class="qb-field-group">
                        <select name="customer_id" id="customerSelect" class="qb-select" required onchange="handleCustomerChange(this)">
                            <option value="">Select Customer...</option>
                            <?php foreach($data['customers'] as $cust): ?>
                                <option value="<?= $cust->id ?>"
                                        data-phone="<?= htmlspecialchars($cust->phone ?? '') ?>"
                                        data-email="<?= htmlspecialchars($cust->email ?? '') ?>"
                                        data-address="<?= htmlspecialchars($cust->address ?? '') ?>"
                                        data-balance="<?= floatval($cust->outstanding_balance ?? 0) ?>">
                                    <?= htmlspecialchars($cust->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="qb-customer-details" id="customerPreviewBox">
                        <div class="qb-customer-info" id="customerInfoText">
                            <span><i class="fa-solid fa-phone"></i> Phone: -</span>
                            <span><i class="fa-solid fa-envelope"></i> Email: -</span>
                            <span><i class="fa-solid fa-location-dot"></i> Address: -</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotation Parameters Card -->
            <div class="qb-card">
                <div class="qb-card-header">
                    <span class="qb-card-title">
                        <i class="fa-solid fa-receipt"></i> Quotation Parameters
                    </span>
                    <span style="font-size:11px; font-weight:700; color:var(--qb-primary); font-family:var(--qb-mono);">
                        DRAFT
                    </span>
                </div>

                <div class="qb-doc-strip">
                    <div class="qb-field-group">
                        <label class="qb-label">Estimate #</label>
                        <input type="text" name="estimate_number" class="qb-input" value="<?= htmlspecialchars($data['estimate_number']) ?>" required style="font-family:var(--qb-mono); font-weight:700;">
                    </div>

                    <div class="qb-field-group">
                        <label class="qb-label">Estimate Date</label>
                        <input type="date" name="estimate_date" class="qb-input" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="qb-field-group">
                        <label class="qb-label">Valid Until</label>
                        <input type="date" name="expiry_date" class="qb-input" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══ PRODUCT SEARCH BAR ═══ -->
        <div class="qb-search-card">
            <div class="qb-search-wrapper">
                <i class="fa-solid fa-magnifying-glass qb-search-icon"></i>
                <input type="text" id="catalogSearch" class="qb-search-input" placeholder="Search catalog items, SKUs, or type custom description... (Press '/' or F2)" autocomplete="off">
                <span class="qb-search-badge">/ or F2</span>
            </div>

            <div class="qb-suggestions-list" id="suggestionsList"></div>
        </div>

        <!-- ═══ LINE ITEMS TABLE CARD ═══ -->
        <div class="qb-table-card">
            <div class="qb-table-scroll">
                <table class="qb-table" id="estimateTable">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">#</th>
                            <th style="width: 48%;">Product / Service Description</th>
                            <th style="width: 14%; text-align: right;">Quantity</th>
                            <th style="width: 16%; text-align: right;">Unit Price (Rs.)</th>
                            <th style="width: 16%; text-align: right;">Line Total (Rs.)</th>
                            <th style="width: 40px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="estimateBody">
                        <!-- Initial Dynamic Empty State or Default Row -->
                    </tbody>
                </table>

                <div class="qb-empty-state" id="emptyState">
                    <i class="fa-solid fa-cart-flatbed"></i>
                    <p style="font-size: 13px; font-weight: 600; margin: 0;">No items added yet</p>
                    <p style="font-size: 11.5px; margin: 4px 0 0;">Use the search bar above or press <kbd>/</kbd> to quickly add items.</p>
                </div>
            </div>
        </div>

        <!-- ═══ BOTTOM SUMMARY & NOTES ═══ -->
        <div class="qb-bottom-grid">
            
            <div class="qb-notes-card">
                <label class="qb-label" style="margin-bottom: 6px;">Terms & Conditions / Customer Notes</label>
                <textarea name="notes" class="qb-textarea" placeholder="Payment terms, warranty conditions, delivery timeline, or banking details..."></textarea>
            </div>

            <div class="qb-summary-card">
                <div>
                    <div class="qb-summary-row">
                        <span>Total Line Items:</span>
                        <strong id="summaryLineCount" style="font-family:var(--qb-mono);">0 lines</strong>
                    </div>
                    <div class="qb-summary-row">
                        <span>Total Units / Quantity:</span>
                        <strong id="summaryUnitsCount" style="font-family:var(--qb-mono);">0 units</strong>
                    </div>
                </div>

                <div class="qb-summary-row grand">
                    <span style="font-weight:700; font-size:14px;">Quotation Grand Total:</span>
                    <span class="qb-grand-total">Rs. <span id="grandTotalText">0.00</span></span>
                </div>
            </div>

        </div>

    </form>
</div>

<!-- ═══ SHORTCUTS MODAL ═══ -->
<div class="qb-modal-overlay" id="shortcutsModal" onclick="if(event.target === this) toggleShortcutsModal()">
    <div class="qb-modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="margin:0; font-size:15px; font-weight:700;"><i class="fa-solid fa-keyboard"></i> Keyboard Shortcuts</h3>
            <button type="button" class="qb-row-del" onclick="toggleShortcutsModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px; font-size:12.5px;">
            <div style="display:flex; justify-content:space-between;"><span>Focus Product Search</span><kbd class="qb-search-badge" style="position:static;">/</kbd> or <kbd class="qb-search-badge" style="position:static;">F2</kbd></div>
            <div style="display:flex; justify-content:space-between;"><span>Save Estimate</span><kbd class="qb-search-badge" style="position:static;">Ctrl + S</kbd></div>
            <div style="display:flex; justify-content:space-between;"><span>Navigate Suggestions</span><kbd class="qb-search-badge" style="position:static;">↑ / ↓</kbd></div>
            <div style="display:flex; justify-content:space-between;"><span>Select / Next Field</span><kbd class="qb-search-badge" style="position:static;">Enter</kbd></div>
            <div style="display:flex; justify-content:space-between;"><span>Close Modals / Menus</span><kbd class="qb-search-badge" style="position:static;">Esc</kbd></div>
        </div>
    </div>
</div>

<script>
    // ═══ CATALOG DATA ═══
    const catalogData = [];

    <?php if(!empty($data['catalog_items'])): foreach($data['catalog_items'] as $item): ?>
        // Base Item
        catalogData.push({
            id: <?= $item->id ?>,
            name: <?= json_encode($item->name) ?>,
            sku: <?= json_encode($item->item_code ?? '') ?>,
            price: <?= floatval($item->price ?? 0) ?>,
            variation: null
        });

        // Variations
        <?php if(!empty($item->variations)): foreach($item->variations as $var): ?>
            catalogData.push({
                id: <?= $item->id ?>,
                name: <?= json_encode($item->name . ' (' . $var->option_name . ')') ?>,
                sku: <?= json_encode($var->sku ?? ($item->item_code ?? '')) ?>,
                price: <?= floatval($var->price > 0 ? $var->price : $item->price) ?>,
                variation: <?= json_encode($var->option_name) ?>
            });
        <?php endforeach; endif; ?>
    <?php endforeach; endif; ?>

    // ═══ CUSTOMER CHANGE HANDLER ═══
    function handleCustomerChange(select) {
        const option = select.options[select.selectedIndex];
        const previewBox = document.getElementById('customerInfoText');
        const balanceIndicator = document.getElementById('customerBalanceIndicator');

        if (!option || !option.value) {
            previewBox.innerHTML = `
                <span><i class="fa-solid fa-phone"></i> Phone: -</span>
                <span><i class="fa-solid fa-envelope"></i> Email: -</span>
                <span><i class="fa-solid fa-location-dot"></i> Address: -</span>
            `;
            balanceIndicator.className = 'qb-balance-badge clear';
            balanceIndicator.innerHTML = '<i class="fa-solid fa-circle-notch"></i> Select a customer';
            return;
        }

        const phone = option.getAttribute('data-phone') || 'N/A';
        const email = option.getAttribute('data-email') || 'N/A';
        const address = option.getAttribute('data-address') || 'N/A';
        const balance = parseFloat(option.getAttribute('data-balance') || 0);

        previewBox.innerHTML = `
            <span><i class="fa-solid fa-phone"></i> ${phone}</span>
            <span><i class="fa-solid fa-envelope"></i> ${email}</span>
            <span><i class="fa-solid fa-location-dot"></i> ${address}</span>
        `;

        if (balance > 0) {
            balanceIndicator.className = 'qb-balance-badge payable';
            balanceIndicator.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> Outstanding: Rs. ${balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else if (balance < 0) {
            balanceIndicator.className = 'qb-balance-badge advance';
            balanceIndicator.innerHTML = `<i class="fa-solid fa-circle-check"></i> Advance Credit: Rs. ${Math.abs(balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            balanceIndicator.className = 'qb-balance-badge clear';
            balanceIndicator.innerHTML = `<i class="fa-solid fa-check"></i> No Outstanding Balance`;
        }
    }

    // ═══ PRODUCT SEARCH & AUTOCOMPLETE ═══
    const searchInput = document.getElementById('catalogSearch');
    const suggestionsList = document.getElementById('suggestionsList');
    let activeSuggestionIndex = -1;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        if (!query) {
            suggestionsList.style.display = 'none';
            return;
        }

        const matches = catalogData.filter(item => {
            return (item.name && item.name.toLowerCase().includes(query)) ||
                   (item.sku && item.sku.toLowerCase().includes(query));
        }).slice(0, 15);

        renderSuggestions(matches, query);
    });

    function renderSuggestions(matches, query) {
        suggestionsList.innerHTML = '';
        activeSuggestionIndex = -1;

        if (matches.length === 0) {
            // Option to add custom item text directly
            const customDiv = document.createElement('div');
            customDiv.className = 'qb-suggestion-item';
            customDiv.innerHTML = `
                <div class="qb-suggestion-left">
                    <span class="qb-suggestion-name"><i class="fa-solid fa-plus-circle" style="color:var(--qb-primary);"></i> Add Custom Line: "${query}"</span>
                    <span class="qb-suggestion-sku">Custom Non-Inventory Service / Item</span>
                </div>
                <div class="qb-suggestion-price">Custom Price</div>
            `;
            customDiv.onclick = () => {
                addItemRow(query, 1, 0);
                searchInput.value = '';
                suggestionsList.style.display = 'none';
            };
            suggestionsList.appendChild(customDiv);
        } else {
            matches.forEach((item, idx) => {
                const div = document.createElement('div');
                div.className = 'qb-suggestion-item';
                div.innerHTML = `
                    <div class="qb-suggestion-left">
                        <span class="qb-suggestion-name">${escapeHtml(item.name)}</span>
                        <span class="qb-suggestion-sku">SKU: ${escapeHtml(item.sku || 'N/A')}</span>
                    </div>
                    <div class="qb-suggestion-price">Rs. ${item.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                `;
                div.onclick = () => {
                    addItemRow(item.name, 1, item.price);
                    searchInput.value = '';
                    suggestionsList.style.display = 'none';
                };
                suggestionsList.appendChild(div);
            });
        }

        suggestionsList.style.display = 'block';
    }

    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsList.querySelectorAll('.qb-suggestion-item');
        if (suggestionsList.style.display === 'block' && items.length > 0) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSuggestionIndex = (activeSuggestionIndex + 1) % items.length;
                updateActiveSuggestion(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSuggestionIndex = (activeSuggestionIndex - 1 + items.length) % items.length;
                updateActiveSuggestion(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestionIndex >= 0 && items[activeSuggestionIndex]) {
                    items[activeSuggestionIndex].click();
                } else if (items.length > 0) {
                    items[0].click();
                }
            } else if (e.key === 'Escape') {
                suggestionsList.style.display = 'none';
            }
        }
    });

    function updateActiveSuggestion(items) {
        items.forEach((it, i) => {
            if (i === activeSuggestionIndex) {
                it.classList.add('active');
                it.scrollIntoView({ block: 'nearest' });
            } else {
                it.classList.remove('active');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // ═══ TABLE ROW MANAGEMENT ═══
    function addItemRow(description, qty = 1, price = 0) {
        const tbody = document.getElementById('estimateBody');
        const emptyState = document.getElementById('emptyState');
        
        const row = document.createElement('tr');
        row.className = 'qb-item-row';
        row.innerHTML = `
            <td class="qb-row-index" style="text-align: center; color: var(--qb-slate-400); font-family: var(--qb-mono); font-weight: 700;"></td>
            <td>
                <input type="text" name="desc[]" class="qb-table-input item-desc-input" value="${escapeHtml(description)}" required placeholder="Description of item or service...">
            </td>
            <td>
                <input type="number" name="qty[]" class="qb-table-input num item-qty-input" value="${qty}" min="0.01" step="any" required oninput="calcLine(this)">
            </td>
            <td>
                <input type="number" name="price[]" class="qb-table-input num item-price-input" value="${parseFloat(price).toFixed(2)}" min="0" step="0.01" required oninput="calcLine(this)">
            </td>
            <td class="qb-table-total item-line-total">
                Rs. 0.00
            </td>
            <td style="text-align: center;">
                <button type="button" class="qb-row-del" onclick="deleteRow(this)" title="Remove item">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
        emptyState.style.display = 'none';
        
        // Re-index & calculate
        reindexRows();
        calcLine(row.querySelector('.item-qty-input'));

        // Setup Enter key navigation: Qty -> Price -> Search input
        const qtyInput = row.querySelector('.item-qty-input');
        const priceInput = row.querySelector('.item-price-input');

        qtyInput.focus();
        qtyInput.select();

        qtyInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                priceInput.focus();
                priceInput.select();
            }
        });

        priceInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
    }

    function deleteRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        reindexRows();
        calcGrandTotal();
    }

    function reindexRows() {
        const rows = document.querySelectorAll('#estimateBody .qb-item-row');
        const emptyState = document.getElementById('emptyState');
        
        if (rows.length === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }

        rows.forEach((row, idx) => {
            row.querySelector('.qb-row-index').textContent = idx + 1;
        });

        document.getElementById('summaryLineCount').textContent = `${rows.length} line${rows.length === 1 ? '' : 's'}`;
    }

    function calcLine(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.item-qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.item-price-input').value) || 0;
        const total = qty * price;

        row.querySelector('.item-line-total').textContent = `Rs. ${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        calcGrandTotal();
    }

    function calcGrandTotal() {
        const rows = document.querySelectorAll('#estimateBody .qb-item-row');
        let grandTotal = 0;
        let totalUnits = 0;

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.item-price-input').value) || 0;
            grandTotal += (qty * price);
            totalUnits += qty;
        });

        document.getElementById('grandTotalText').textContent = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryUnitsCount').textContent = `${totalUnits.toLocaleString('en-US')} unit${totalUnits === 1 ? '' : 's'}`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ═══ GLOBAL SHORTCUTS ═══
    document.addEventListener('keydown', function(e) {
        // Ctrl+S to save
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            document.getElementById('btnSaveEstimate').click();
        }
        // '/' or F2 to focus search
        if ((e.key === '/' || e.key === 'F2') && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });

    function toggleShortcutsModal() {
        document.getElementById('shortcutsModal').classList.toggle('active');
    }
</script>