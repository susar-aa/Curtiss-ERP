<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   CUSTOMER RETURNS UI — DESIGN SYSTEM (QB Wrapper)
   ============================================================ */
:root {
    --qb-bg:           #f8fafc;
    --qb-surface:      #ffffff;
    --qb-surface-2:    #f1f5f9;
    --qb-primary:      #c62828;
    --qb-primary-dark: #b71c1c;
    --qb-primary-light:#ffebee;
    
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

    --qb-font:         'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --qb-mono:         ui-monospace, "SF Mono", "Cascadia Code", "Roboto Mono", Menlo, monospace;
    --qb-radius:       8px;
    --qb-radius-lg:    12px;
    --qb-shadow-sm:    0 1px 2px 0 rgb(0 0 0 / 0.05);
    --qb-shadow:       0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
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
    box-shadow: 0 1px 2px 0 rgba(198, 40, 40, 0.3);
}
.qb-btn-primary:hover {
    background: var(--qb-primary-dark);
}

/* ---- Card Layout ---- */
.qb-meta-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 12px;
}

.qb-card {
    background: var(--qb-surface);
    border: 1px solid var(--qb-slate-200);
    border-radius: var(--qb-radius-lg);
    padding: 14px 16px;
    box-shadow: var(--qb-shadow-sm);
    position: relative;
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
.qb-field-group { display: flex; flex-direction: column; gap: 4px; }
.qb-label { font-size: 11px; font-weight: 600; color: var(--qb-slate-600); text-transform: uppercase; letter-spacing: 0.03em; }

.qb-input {
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
.qb-input:focus {
    border-color: var(--qb-primary);
    box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.15);
}

/* Customer Search Component */
.customer-search-container { position: relative; width: 100%; }
.customer-search-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    font-size: 13px;
    font-weight: 500;
    font-family: var(--qb-font);
    border: 1.5px solid var(--qb-slate-300);
    border-radius: var(--qb-radius);
    outline: none;
    transition: all 0.15s ease;
}
.customer-search-input:focus { border-color: var(--qb-primary); box-shadow: 0 0 0 4px rgba(198,40,40,0.1); }
.customer-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--qb-slate-400); font-size: 13px; pointer-events: none; }
.customer-search-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; background: var(--qb-surface); border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius); margin-top: 4px; box-shadow: var(--qb-shadow-xl); max-height: 280px; overflow-y: auto; z-index: 100; display: none;
}
.customer-search-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid var(--qb-slate-100); transition: background 0.1s; }
.customer-search-item:last-child { border-bottom: none; }
.customer-search-item.highlighted { background: var(--qb-slate-50); }
.customer-search-item:hover { background: var(--qb-slate-50); }
.c-title { font-size: 13px; font-weight: 600; color: var(--qb-slate-900); }
.c-sub { font-size: 11px; color: var(--qb-slate-500); margin-top: 2px; }

/* ---- Data Table for Items ---- */
.qb-table-wrap { overflow-x: auto; flex: 1; min-height: 200px; }
.qb-table { width: 100%; border-collapse: collapse; text-align: left; }
.qb-table th { background: var(--qb-slate-50); color: var(--qb-slate-600); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 14px; border-bottom: 1px solid var(--qb-slate-200); }
.qb-table td { padding: 8px 14px; border-bottom: 1px solid var(--qb-slate-100); vertical-align: top; }
.qb-table input, .qb-table select { width: 100%; padding: 7px; border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius); font-family: var(--qb-font); font-size: 12px; }
.qb-table input:focus, .qb-table select:focus { border-color: var(--qb-primary); outline: none; }
.line-num { font-size: 11px; color: var(--qb-slate-400); font-weight: 600; padding-top: 10px; display: block; text-align: center; }

/* ---- Sales History UI ---- */
.history-pane { background: #fdfbf7; border: 1px solid #fde68a; border-radius: var(--qb-radius); padding: 12px; margin-top: 10px; display: none; }
.history-pane-title { font-size: 12px; font-weight: 600; color: #b45309; display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
.history-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
.history-table th, .history-table td { padding: 6px; border-bottom: 1px dotted #fde68a; text-align: left; }
.history-table th { color: #92400e; font-weight: 600; }

/* ---- Summary Footer ---- */
.qb-footer { background: var(--qb-surface); border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius-lg); padding: 14px 20px; box-shadow: var(--qb-shadow-sm); display: flex; justify-content: space-between; align-items: flex-end; }
.summary-stack { display: flex; gap: 24px; }
.summary-item { display: flex; flex-direction: column; }
.summary-lbl { font-size: 11px; color: var(--qb-slate-500); text-transform: uppercase; font-weight: 600; margin-bottom: 2px; }
.summary-val { font-size: 16px; font-family: var(--qb-mono); font-weight: 700; color: var(--qb-slate-800); }
.summary-grand { font-size: 24px; color: var(--qb-primary); }

/* Custom Searchable Select for Line Items */
.searchable-select-wrapper { position: relative; }
</style>

<div class="qb-wrapper">
    <form action="<?= APP_URL ?>/creditnote/create" method="POST" id="cnForm" class="qb-container">

        <!-- Error Alert -->
        <?php if(!empty($data['error'])): ?>
            <div style="background: var(--qb-primary-light); color: var(--qb-primary-dark); padding: 12px 16px; border-radius: var(--qb-radius-lg); font-size: 13px; font-weight: 500; border: 1px solid rgba(198,40,40,0.2);">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <?= $data['error'] ?>
            </div>
        <?php endif; ?>
        
        <!-- Feature Banner -->
        <div style="background: rgba(46, 125, 50, 0.06); padding: 12px 18px; border-radius: var(--qb-radius-lg); border: 1px solid rgba(46, 125, 50, 0.2); display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 20px; color: #2e7d32;">⚡</div>
            <div>
                <h4 style="margin: 0 0 2px 0; color: #2e7d32; font-size: 13px; font-weight: 700;">Automated Double-Entry Accounting Enabled</h4>
                <p style="font-size: 11.5px; color: #2e7d32; margin: 0; opacity: 0.9;">Manual ledger configuration has been eliminated. The system will automatically reverse sales revenue, deduct the customer's outstanding balance, and manage inventory.</p>
            </div>
        </div>

        <!-- 1. Header -->
        <header class="qb-header">
            <div class="qb-title-group">
                <div class="qb-title-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></div>
                <div>
                    <h1 class="qb-title">Customer Return Note</h1>
                    <p class="qb-subtitle">Issue Credit Note to Customer</p>
                </div>
            </div>
            <div class="qb-header-actions">
                <a href="<?= APP_URL ?>/creditnote" class="qb-btn qb-btn-ghost">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </a>
            </div>
        </header>

        <!-- 2. Meta Grid -->
        <div class="qb-meta-grid">
            
            <!-- Customer Card -->
            <div class="qb-card">
                <div class="qb-card-header">
                    <div class="qb-card-title"><i class="fa-solid fa-user"></i> Customer Details</div>
                </div>
                
                <div class="customer-search-container">
                    <i class="fa-solid fa-magnifying-glass customer-search-icon"></i>
                    <input type="text" id="customerSearchInput" class="customer-search-input" placeholder="Search and select a customer..." autocomplete="off">
                    <input type="hidden" name="customer_id" id="hiddenCustomerId" required>
                    
                    <div class="customer-search-dropdown" id="customerSearchDropdown">
                        <?php foreach($data['customers'] as $c): ?>
                            <div class="customer-search-item" 
                                 data-id="<?= $c->id ?>" 
                                 data-name="<?= htmlspecialchars($c->name) ?>"
                                 data-phone="<?= htmlspecialchars($c->phone ?? '') ?>"
                                 data-email="<?= htmlspecialchars($c->email ?? '') ?>"
                                 data-address="<?= htmlspecialchars($c->address ?? '') ?>">
                                <div class="c-title"><?= htmlspecialchars($c->name) ?></div>
                                <div class="c-sub"><?= htmlspecialchars($c->phone ?? 'No Phone') ?> | <?= htmlspecialchars($c->email ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="customerDetailsBox" style="display:none; margin-top: 14px; padding: 12px; background: var(--qb-slate-50); border-radius: var(--qb-radius); border: 1px solid var(--qb-slate-200);">
                    <div style="font-size: 13px; font-weight: 700; color: var(--qb-slate-900); margin-bottom: 4px;" id="cdName">-</div>
                    <div style="font-size: 11.5px; color: var(--qb-slate-500); margin-bottom: 2px;" id="cdContact">-</div>
                    <div style="font-size: 11.5px; color: var(--qb-slate-500);" id="cdAddress">-</div>
                </div>
            </div>

            <!-- Note Details Card -->
            <div class="qb-card">
                <div class="qb-card-header">
                    <div class="qb-card-title"><i class="fa-solid fa-file-invoice"></i> Document Details</div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div class="qb-field-group">
                        <label class="qb-label">Return Note #</label>
                        <input type="text" name="credit_note_number" class="qb-input" value="<?= htmlspecialchars($data['credit_note_number']) ?>" readonly style="background: var(--qb-slate-50); color: var(--qb-slate-500); font-family: var(--qb-mono); font-weight:600; cursor:not-allowed;">
                    </div>
                    <div class="qb-field-group">
                        <label class="qb-label">Note Date</label>
                        <input type="date" name="note_date" class="qb-input" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Return Items Table Card -->
        <div class="qb-card" style="display: flex; flex-direction: column; flex: 1;">
            <div class="qb-card-header" style="border-bottom:none; margin-bottom:0;">
                <div class="qb-card-title"><i class="fa-solid fa-box-open"></i> Return Items</div>
            </div>
            
            <div style="padding: 10px 0 16px 0;">
                <div class="qb-search-wrapper" style="position: relative; width: 100%; max-width: 500px;">
                    <i class="fa-solid fa-barcode" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--qb-slate-400); font-size: 13px; z-index: 2;"></i>
                    <input type="text" id="productSearchInput" class="customer-search-input" placeholder="Select a Customer first..." autocomplete="off" disabled style="background: var(--qb-surface); border-color: var(--qb-slate-300);">
                    
                    <div class="customer-search-dropdown" id="productSearchDropdown" style="max-height: 300px; z-index: 101;">
                        <!-- Dynamic product list goes here -->
                    </div>
                </div>
            </div>

            <div class="qb-table-wrap">
                <table class="qb-table" id="linesTable">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align:center;">#</th>
                            <th style="width: 28%;">Product Returned</th>
                            <th style="width: 15%;">Original Invoice Link</th>
                            <th style="width: 12%;">Condition</th>
                            <th style="width: 12%; text-align: right;">Return Qty</th>
                            <th style="width: 14%; text-align: right;">Unit Price (Rs)</th>
                            <th style="width: 15%; text-align: right;">Line Total (Rs)</th>
                            <th style="width: 40px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        <!-- JS injected rows -->
                    </tbody>
                </table>

                <!-- Product Sales History Section -->
                <div id="historyContainer" class="history-pane">
                    <div class="history-pane-title">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Product Invoice Sales History</span>
                    </div>
                    <p style="font-size: 11px; color:#666; margin: 0 0 10px 0;">Select a sale record below to automatically apply the historical sale price and link the original invoice.</p>
                    <div id="historyTableContainer"></div>
                </div>

            </div>
        </div>

        <!-- 4. Footer -->
        <footer class="qb-footer">
            <div class="summary-stack">
                <div class="summary-item">
                    <span class="summary-lbl">Total Qty</span>
                    <span class="summary-val" id="sumQty">0</span>
                </div>
                <div class="summary-item">
                    <span class="summary-lbl">Total Lines</span>
                    <span class="summary-val" id="sumLines">0</span>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 30px;">
                <div style="text-align: right;">
                    <div class="summary-lbl">Total Credit Amount (Rs)</div>
                    <div class="summary-val summary-grand" id="totalDisplay">0.00</div>
                    <input type="hidden" name="total_amount" id="totalAmountHidden" value="0.00">
                </div>
                <button type="submit" class="qb-btn qb-btn-primary" style="padding: 12px 24px; font-size: 14px;">
                    <i class="fa-solid fa-check"></i> Issue Credit Note
                </button>
            </div>
        </footer>

    </form>
</div>

<script>
    // --- CUSTOMER SEARCH LOGIC ---
    const customerInput = document.getElementById('customerSearchInput');
    const customerDropdown = document.getElementById('customerSearchDropdown');
    const hiddenCustomerId = document.getElementById('hiddenCustomerId');
    const customerDetailsBox = document.getElementById('customerDetailsBox');
    let customerProducts = [];
    let currentActiveRow = null;

    customerInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const items = customerDropdown.querySelectorAll('.customer-search-item');
        let hasVisible = false;
        items.forEach(item => {
            const txt = item.textContent.toLowerCase();
            if (txt.includes(filter)) {
                item.style.display = 'block';
                hasVisible = true;
            } else {
                item.style.display = 'none';
            }
        });
        customerDropdown.style.display = hasVisible ? 'block' : 'none';
    });

    customerInput.addEventListener('focus', function() {
        this.dispatchEvent(new Event('input'));
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.customer-search-container')) {
            customerDropdown.style.display = 'none';
        }
    });

    customerDropdown.addEventListener('click', function(e) {
        const item = e.target.closest('.customer-search-item');
        if (item) {
            const cId = item.getAttribute('data-id');
            const cName = item.getAttribute('data-name');
            const cPhone = item.getAttribute('data-phone');
            const cEmail = item.getAttribute('data-email');
            const cAddress = item.getAttribute('data-address');
            
            customerInput.value = cName;
            hiddenCustomerId.value = cId;
            customerDropdown.style.display = 'none';

            document.getElementById('cdName').textContent = cName;
            document.getElementById('cdContact').textContent = (cPhone ? cPhone : '') + (cPhone && cEmail ? ' | ' : '') + (cEmail ? cEmail : '');
            document.getElementById('cdAddress').textContent = cAddress;
            customerDetailsBox.style.display = 'block';

            onCustomerSelected(cId);
        }
    });

    // --- RETURN LOGIC ---
    function onCustomerSelected(customerId) {
        const tbody = document.getElementById('linesBody');
        tbody.innerHTML = ''; 
        document.getElementById('historyContainer').style.display = 'none';
        updateTotals();

        const prodSearch = document.getElementById('productSearchInput');
        prodSearch.disabled = true;
        prodSearch.placeholder = 'Loading products...';

        fetch(`<?= APP_URL ?>/creditnote/get_customer_products?customer_id=${customerId}`)
            .then(res => res.json())
            .then(data => {
                customerProducts = data;
                prodSearch.disabled = false;
                prodSearch.placeholder = 'Search Customer Products by name or SKU...';
                prodSearch.focus();
            });
    }

    // --- PRODUCT SEARCH LOGIC ---
    const prodSearchInput = document.getElementById('productSearchInput');
    const prodSearchDropdown = document.getElementById('productSearchDropdown');

    prodSearchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase().trim();
        if(!filter) {
            prodSearchDropdown.style.display = 'none';
            return;
        }

        prodSearchDropdown.innerHTML = '';
        let hasVisible = false;

        const matched = customerProducts.filter(p => {
            const name = (p.product_name || '').toLowerCase();
            const sku = (p.sku || '').toLowerCase();
            const sampleCode = (p.sample_code || '').toLowerCase();
            return name.includes(filter) || sku.includes(filter) || sampleCode.includes(filter);
        });

        matched.forEach(p => {
            hasVisible = true;
            const item = document.createElement('div');
            item.className = 'customer-search-item';
            
            let sub = '';
            if(p.sku) sub += `SKU: ${p.sku}`;
            if(p.sample_code) sub += (sub ? ' | ' : '') + `Code: ${p.sample_code}`;

            item.innerHTML = `
                <div class="c-title">${escapeHtml(p.product_name)}</div>
                <div class="c-sub">${escapeHtml(sub)} <span style="color:var(--qb-primary);">(Sold: ${parseFloat(p.total_sold).toFixed(0)} Pcs)</span></div>
            `;

            item.onclick = () => {
                prodSearchDropdown.style.display = 'none';
                prodSearchInput.value = '';
                const val = `${p.item_id}|${p.variation_option_id || '0'}`;
                addItemRow(val, p.product_name, p.max_returnable);
            };
            prodSearchDropdown.appendChild(item);
        });

        prodSearchDropdown.style.display = hasVisible ? 'block' : 'none';
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.qb-search-wrapper')) {
            prodSearchDropdown.style.display = 'none';
        }
    });

    function addItemRow(productVal, productName, maxReturnable) {
        const tbody = document.getElementById('linesBody');
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td><span class="line-num"></span></td>
            <td>
                <input type="hidden" name="item_selection[]" value="${escapeHtml(productVal)}">
                <input type="text" name="desc[]" class="qb-table-input" value="${escapeHtml(productName)}" readonly style="background:transparent; border:none; font-weight:600; font-size:12px; padding-left:0;">
                <input type="hidden" name="invoice_id[]" class="invoice-id-hidden">
                <input type="hidden" name="invoice_item_id[]" class="invoice-item-id-hidden">
            </td>
            <td>
                <input type="text" name="invoice_display[]" class="invoice-display-input" placeholder="No invoice linked..." readonly style="background: var(--qb-slate-50); color: var(--qb-slate-500); border-color: transparent; font-family:var(--qb-mono); font-size:11px;">
            </td>
            <td>
                <select name="condition[]" style="font-weight:600; color:var(--qb-slate-700);">
                    <option value="Good">Good (Restock)</option>
                    <option value="Damaged">Damaged (Loss)</option>
                </select>
            </td>
            <td style="position:relative;">
                <input type="number" name="qty[]" step="1" min="1" max="${maxReturnable}" value="1" class="qb-table-input num" style="text-align:right;" oninput="validateQty(this); calculateLineTotal(this)" required>
                <span class="max-qty-badge" style="position: absolute; right: 10px; top: -5px; font-size: 9px; background: var(--qb-slate-600); color: #fff; padding: 2px 4px; border-radius: 3px;">Max: ${parseFloat(maxReturnable).toFixed(0)}</span>
            </td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" class="qb-table-input num" style="text-align:right;" oninput="calculateLineTotal(this)" required></td>
            <td style="text-align: right; vertical-align: middle;" class="qb-table-total">
                <span class="line-total-display" style="font-family:var(--qb-mono); font-weight:600; color:var(--qb-primary);">0.00</span>
            </td>
            <td style="vertical-align: middle; text-align:center;">
                <button type="button" style="background:transparent; border:none; color:var(--qb-slate-400); cursor:pointer; font-size:14px; padding:4px;" onclick="removeRow(this)" onmouseover="this.style.color='var(--qb-primary)'" onmouseout="this.style.color='var(--qb-slate-400)'">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        renumberRows();
        
        currentActiveRow = tr;

        // Auto trigger history fetch
        const customerId = hiddenCustomerId.value;
        fetch(`<?= APP_URL ?>/creditnote/get_product_sale_history?customer_id=${customerId}&product_val=${encodeURIComponent(productVal)}`)
            .then(res => res.json())
            .then(history => {
                renderHistoryTable(history, maxReturnable);
            });
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        renumberRows();
        updateTotals();
        document.getElementById('historyContainer').style.display = 'none';
    }

    function renumberRows() {
        document.querySelectorAll('#linesBody tr').forEach((tr, i) => {
            tr.querySelector('.line-num').textContent = i + 1;
        });
        updateTotals();
    }

    function validateQty(input) {
        const max = parseFloat(input.max) || 0;
        if (max > 0 && parseFloat(input.value) > max) {
            alert(`Quantity exceeds the total returnable balance (${max} Pcs) purchased by this customer.`);
            input.value = max;
        }
    }

    function calculateLineTotal(input) {
        const tr = input.closest('tr');
        const qty = parseFloat(tr.querySelector('input[name="qty[]"]').value) || 0;
        const cost = parseFloat(tr.querySelector('input[name="price[]"]').value) || 0;
        
        const lineTotal = qty * cost;
        tr.querySelector('.line-total-display').textContent = lineTotal.toFixed(2);
        
        updateTotals();
    }

    function updateTotals() {
        let grandTotal = 0;
        let totalQty = 0;
        let lineCount = 0;
        
        document.querySelectorAll('#linesBody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('input[name="qty[]"]').value) || 0;
            const cost = parseFloat(tr.querySelector('input[name="price[]"]').value) || 0;
            grandTotal += qty * cost;
            totalQty += qty;
            lineCount++;
        });

        document.getElementById('totalDisplay').textContent = grandTotal.toFixed(2);
        document.getElementById('totalAmountHidden').value = grandTotal.toFixed(2);
        document.getElementById('sumQty').textContent = totalQty.toFixed(0);
        document.getElementById('sumLines').textContent = lineCount;
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.getElementById('linesBody').addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (tr) currentActiveRow = tr;
    });

    function renderHistoryTable(history, baseMaxReturnable) {
        const container = document.getElementById('historyTableContainer');
        const card = document.getElementById('historyContainer');
        card.style.display = 'block';

        if (history.length === 0) {
            container.innerHTML = '<p style="color: var(--qb-slate-500); font-style: italic; margin: 0; font-size: 11px;">No invoice sale history found for this product.</p>';
            return;
        }

        let html = `
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Sale Date</th>
                        <th style="text-align: right;">Selling Price</th>
                        <th style="text-align: right;">Qty Sold</th>
                        <th style="text-align: right;">Returned Qty</th>
                        <th style="text-align: center; width:90px;">Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        history.forEach(h => {
            html += `
                <tr>
                    <td style="font-family:var(--qb-mono); font-weight:600;">${escapeHtml(h.invoice_number)}</td>
                    <td>${escapeHtml(h.invoice_date)}</td>
                    <td style="text-align: right; font-weight: 600;">Rs. ${parseFloat(h.unit_price).toFixed(2)}</td>
                    <td style="text-align: right;">${parseFloat(h.quantity).toFixed(0)}</td>
                    <td style="text-align: right; color:var(--qb-slate-500);">${parseFloat(h.returned_qty).toFixed(0)}</td>
                    <td style="text-align: center;">
                        <button type="button" class="qb-btn" style="background:#fef3c7; color:#92400e; padding: 4px 8px; font-size:10px; border-radius:4px;" onclick="selectHistoryRecord('${h.invoice_id}', '${h.invoice_item_id}', '${escapeHtml(h.invoice_number)}', ${h.unit_price}, ${h.max_returnable})">
                            <i class="fa-solid fa-check"></i> Apply
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function selectHistoryRecord(invoiceId, invoiceItemId, invoiceNumber, price, maxReturnable) {
        if (!currentActiveRow) return;
        
        currentActiveRow.querySelector('input[name="price[]"]').value = price.toFixed(2);
        currentActiveRow.querySelector('.invoice-id-hidden').value = invoiceId;
        currentActiveRow.querySelector('.invoice-item-id-hidden').value = invoiceItemId;
        currentActiveRow.querySelector('.invoice-display-input').value = invoiceNumber;

        const qtyInput = currentActiveRow.querySelector('input[name="qty[]"]');
        qtyInput.max = maxReturnable;
        
        const badge = currentActiveRow.querySelector('.max-qty-badge');
        if(badge) {
            badge.textContent = `Max: ${maxReturnable}`;
        }
        
        calculateLineTotal(qtyInput);
        
        // Flash display
        const card = document.getElementById('historyContainer');
        card.style.backgroundColor = '#fef3c7';
        setTimeout(() => { card.style.backgroundColor = '#fdfbf7'; }, 300);
    }
</script>