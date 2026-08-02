<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SUPPLIER RETURNS UI — DESIGN SYSTEM
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
.vendor-search-container { position: relative; width: 100%; }
.vendor-search-input {
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
.vendor-search-input:focus { border-color: var(--qb-primary); box-shadow: 0 0 0 4px rgba(198,40,40,0.1); }
.vendor-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--qb-slate-400); font-size: 13px; pointer-events: none; }
.vendor-search-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; background: var(--qb-surface); border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius); margin-top: 4px; box-shadow: var(--qb-shadow-xl); max-height: 280px; overflow-y: auto; z-index: 100; display: none;
}
.vendor-search-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid var(--qb-slate-100); transition: background 0.1s; }
.vendor-search-item:last-child { border-bottom: none; }
.vendor-search-item.highlighted { background: var(--qb-slate-50); }
.vendor-search-item:hover { background: var(--qb-slate-50); }
.v-title { font-size: 13px; font-weight: 600; color: var(--qb-slate-900); }
.v-sub { font-size: 11px; color: var(--qb-slate-500); margin-top: 2px; }

/* ---- Data Table for Items ---- */
.qb-table-wrap { overflow-x: auto; flex: 1; min-height: 200px; }
.qb-table { width: 100%; border-collapse: collapse; text-align: left; }
.qb-table th { background: var(--qb-slate-50); color: var(--qb-slate-600); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 14px; border-bottom: 1px solid var(--qb-slate-200); }
.qb-table td { padding: 8px 14px; border-bottom: 1px solid var(--qb-slate-100); vertical-align: top; }
.qb-table input, .qb-table select { width: 100%; padding: 7px; border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius); font-family: var(--qb-font); font-size: 12px; }
.qb-table input:focus, .qb-table select:focus { border-color: var(--qb-primary); outline: none; }
.line-num { font-size: 11px; color: var(--qb-slate-400); font-weight: 600; padding-top: 10px; display: block; text-align: center; }

/* ---- Purchase History UI ---- */
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
.searchable-select-input { width: 100%; padding: 7px; border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius); font-family: var(--qb-font); font-size: 12px; background: #fff; cursor: text; }
.searchable-select-input:focus { border-color: var(--qb-primary); outline: none; }
.searchable-select-dropdown { position: absolute; z-index: 9999; left: 0; right: 0; background: #fff; border: 1px solid var(--qb-slate-300); border-radius: var(--qb-radius); max-height: 200px; overflow-y: auto; box-shadow: var(--qb-shadow-md); display: none; }
.searchable-select-item { padding: 6px 10px; font-size: 12px; cursor: pointer; border-bottom: 1px solid var(--qb-slate-100); display: flex; flex-direction: column; gap: 2px; }
.searchable-select-item:hover, .searchable-select-item.highlighted { background: var(--qb-slate-50); }
.searchable-select-item-sub { font-size: 10.5px; color: var(--qb-slate-500); }
</style>

<div class="qb-wrapper">
    <form action="<?= APP_URL ?>/supplier-return/create" method="POST" id="returnForm" class="qb-container">
        <input type="hidden" name="action" value="save_return">
        <input type="hidden" name="total_amount_hidden" id="totalAmountHidden" value="0.00">
        <input type="hidden" name="vendor_id" id="hiddenVendorId" value="" required>
        
        <!-- HEADER -->
        <div class="qb-header">
            <div class="qb-title-group">
                <div class="qb-title-icon">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <h1 class="qb-title">Create Supplier Goods Return (SRN)</h1>
                    <p class="qb-subtitle">Select a vendor, add items to return, and deduct from stock</p>
                </div>
            </div>
            <div class="qb-header-actions">
                <a href="<?= APP_URL ?>/supplier-return" class="qb-btn qb-btn-ghost">
                    Cancel
                </a>
            </div>
        </div>

        <?php if(!empty($data['error'])): ?>
            <div style="background: var(--qb-rose-50); border: 1px solid #fda4af; color: var(--qb-rose-600); padding: 10px 14px; border-radius: var(--qb-radius); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($data['error']) ?>
            </div>
        <?php endif; ?>

        <!-- META GRID -->
        <div class="qb-meta-grid">
            <div class="qb-card">
                <div class="qb-card-header">
                    <div class="qb-card-title"><i class="fa-solid fa-building"></i> Select Supplier</div>
                </div>
                
                <div class="vendor-search-container">
                    <i class="fa-solid fa-magnifying-glass vendor-search-icon"></i>
                    <input type="text" id="vendorSearchInput" class="vendor-search-input" placeholder="Search Supplier Name..." autocomplete="off">
                    <div id="vendorSearchDropdown" class="vendor-search-dropdown">
                        <?php foreach($data['vendors'] as $ven): ?>
                            <div class="vendor-search-item" data-id="<?= $ven->id ?>" data-name="<?= htmlspecialchars($ven->name) ?>" data-phone="<?= htmlspecialchars($ven->phone ?? '') ?>" data-email="<?= htmlspecialchars($ven->email ?? '') ?>" data-address="<?= htmlspecialchars($ven->address ?? '') ?>">
                                <div class="v-title"><?= htmlspecialchars($ven->name) ?></div>
                                <div class="v-sub"><?= htmlspecialchars($ven->phone ?? '') ?> &bull; <?= htmlspecialchars($ven->email ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="vendorDetailsBox" style="margin-top: 14px; display: none; padding: 12px; background: var(--qb-slate-50); border: 1px solid var(--qb-slate-200); border-radius: var(--qb-radius);">
                    <div style="font-size: 11px; text-transform: uppercase; color: var(--qb-slate-500); font-weight: 600; margin-bottom: 4px;">Selected Supplier</div>
                    <div id="vdName" style="font-weight: 700; font-size: 14px; color: var(--qb-slate-900);"></div>
                    <div id="vdContact" style="font-size: 12px; color: var(--qb-slate-600); margin-top: 2px;"></div>
                    <div id="vdAddress" style="font-size: 12px; color: var(--qb-slate-600); margin-top: 2px;"></div>
                </div>
            </div>

            <div class="qb-card">
                <div class="qb-card-header">
                    <div class="qb-card-title"><i class="fa-solid fa-sliders"></i> Document Settings</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="qb-field-group">
                        <label class="qb-label">Return No.</label>
                        <input type="text" name="return_number" class="qb-input" value="<?= htmlspecialchars($data['return_number']) ?>" required>
                    </div>
                    <div class="qb-field-group">
                        <label class="qb-label">Return Date</label>
                        <input type="date" name="return_date" class="qb-input" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="qb-field-group" style="margin-top: 12px;">
                    <label class="qb-label">Return Reason / Notes</label>
                    <textarea name="notes" class="qb-input" rows="2" placeholder="e.g. Expired items, damaged packaging..."></textarea>
                </div>
            </div>
        </div>

        <!-- ITEMS GRID -->
        <div class="qb-card" style="flex: 1; display: flex; flex-direction: column;">
            <div class="qb-card-header" style="border-bottom: none; margin-bottom: 0;">
                <div class="qb-card-title"><i class="fa-solid fa-list-check"></i> Return Items</div>
                <div>
                    <button type="button" class="qb-btn qb-btn-ghost" style="font-size: 11px; padding: 4px 10px;" onclick="addRow()">
                        <i class="fa-solid fa-plus"></i> Add Line
                    </button>
                </div>
            </div>

            <!-- Product Purchase History Section (Loads dynamically) -->
            <div id="historyContainer" class="history-pane">
                <div class="history-pane-title">
                    <i class="fa-solid fa-clock-rotate-left"></i> Purchase History for Selected Item
                </div>
                <div style="font-size: 10.5px; color: #b45309; margin-bottom: 8px;">Select a batch to automatically apply the accurate unit cost and GRN reference.</div>
                <div id="historyTableContainer"></div>
            </div>

            <div class="qb-table-wrap" style="margin-top: 10px;">
                <table class="qb-table" id="linesTable">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align: center;">#</th>
                            <th style="width: 35%;">Product (Purchased from Supplier)</th>
                            <th style="width: 15%;">Original Batch (GRN)</th>
                            <th style="width: 12%; text-align: right;">Purchase Cost</th>
                            <th style="width: 12%; text-align: right;">Return Qty</th>
                            <th style="width: 15%; text-align: right;">Total Return (Rs)</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        <!-- Line items inserted via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER SUMMARY -->
        <div class="qb-footer">
            <div class="summary-stack">
                <div class="summary-item">
                    <div class="summary-lbl">Total Lines</div>
                    <div class="summary-val" id="sumLines">0</div>
                </div>
                <div class="summary-item">
                    <div class="summary-lbl">Total Return Qty</div>
                    <div class="summary-val" id="sumQty">0.00</div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 24px;">
                <div class="summary-item" style="align-items: flex-end;">
                    <div class="summary-lbl">Grand Total Return Value</div>
                    <div class="summary-val summary-grand">Rs. <span id="totalDisplay">0.00</span></div>
                </div>
                <button type="submit" class="qb-btn qb-btn-primary" style="padding: 10px 20px; font-size: 14px;">
                    <i class="fa-solid fa-check"></i> Save Return & Deduct Stock
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    // --- VENDOR SEARCH LOGIC ---
    const vendorInput = document.getElementById('vendorSearchInput');
    const vendorDropdown = document.getElementById('vendorSearchDropdown');
    const hiddenVendorId = document.getElementById('hiddenVendorId');
    const vendorDetailsBox = document.getElementById('vendorDetailsBox');
    let vendorProducts = [];
    let currentActiveRow = null;

    vendorInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const items = vendorDropdown.querySelectorAll('.vendor-search-item');
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
        vendorDropdown.style.display = hasVisible ? 'block' : 'none';
    });

    vendorInput.addEventListener('focus', function() {
        this.dispatchEvent(new Event('input'));
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.vendor-search-container')) {
            vendorDropdown.style.display = 'none';
        }
    });

    vendorDropdown.addEventListener('click', function(e) {
        const item = e.target.closest('.vendor-search-item');
        if (item) {
            const vId = item.getAttribute('data-id');
            const vName = item.getAttribute('data-name');
            const vPhone = item.getAttribute('data-phone');
            const vEmail = item.getAttribute('data-email');
            const vAddress = item.getAttribute('data-address');
            
            vendorInput.value = vName;
            hiddenVendorId.value = vId;
            vendorDropdown.style.display = 'none';

            document.getElementById('vdName').textContent = vName;
            document.getElementById('vdContact').textContent = (vPhone ? vPhone : '') + (vPhone && vEmail ? ' | ' : '') + (vEmail ? vEmail : '');
            document.getElementById('vdAddress').textContent = vAddress;
            vendorDetailsBox.style.display = 'block';

            onVendorSelected(vId);
        }
    });

    // --- RETURN LOGIC ---
    function onVendorSelected(vendorId) {
        const tbody = document.getElementById('linesBody');
        tbody.innerHTML = ''; 
        document.getElementById('historyContainer').style.display = 'none';
        updateTotals();

        fetch(`<?= APP_URL ?>/supplier-return/get_vendor_products?vendor_id=${vendorId}`)
            .then(res => res.json())
            .then(data => {
                vendorProducts = data;
                addRow(); 
            });
    }

    function addRow() {
        const vendorId = hiddenVendorId.value;
        if (!vendorId) {
            alert('Please select a Supplier first.');
            return;
        }

        const tbody = document.getElementById('linesBody');
        const tr = document.createElement('tr');
        
        let selectOptions = '<option value=""></option>';
        vendorProducts.forEach(p => {
            const skuVal = p.var_sku || p.sku || '';
            const sampleCodeVal = p.sample_code || '';
            selectOptions += `<option value="${p.item_id}|${p.var_opt_id || '0'}" data-sku="${escapeHtml(skuVal)}" data-sample-code="${escapeHtml(sampleCodeVal)}">${escapeHtml(p.product_name)}</option>`;
        });

        tr.innerHTML = `
            <td><span class="line-num"></span></td>
            <td>
                <select name="item_selection[]" class="original-select" style="display:none;" required>
                    ${selectOptions}
                </select>
                <input type="hidden" name="desc[]" class="desc-hidden">
                <input type="hidden" name="grn_id[]" class="grn-id-hidden">
            </td>
            <td>
                <input type="text" name="grn_display[]" class="grn-display-input" placeholder="No batch..." readonly style="background: var(--qb-slate-50); color: var(--qb-slate-500); border-color: transparent;">
            </td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" style="text-align:right;" oninput="calculateLineTotal(this)" required></td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" style="text-align:right;" oninput="calculateLineTotal(this)" required></td>
            <td style="text-align: right; font-family: var(--qb-mono); font-weight: 700; color: var(--qb-slate-800); vertical-align: middle;">
                <span class="line-total-display">0.00</span>
            </td>
            <td style="vertical-align: middle;">
                <button type="button" style="background:transparent; border:none; color:var(--qb-rose-500); cursor:pointer; font-size:14px; padding:4px;" onclick="removeRow(this)">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        renumberRows();
        
        // Initialize searchable select for this new row
        const newSelect = tr.querySelector('.original-select');
        convertSelectToSearchable(newSelect, onItemChange);
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

    function onItemChange(select) {
        const tr = select.closest('tr');
        currentActiveRow = tr;
        
        const selectedOption = select.options[select.selectedIndex];
        tr.querySelector('.desc-hidden').value = selectedOption.textContent;

        const val = select.value;
        if (!val) {
            document.getElementById('historyContainer').style.display = 'none';
            return;
        }

        const vendorId = hiddenVendorId.value;

        fetch(`<?= APP_URL ?>/supplier-return/get_product_history?vendor_id=${vendorId}&product_val=${encodeURIComponent(val)}`)
            .then(res => res.json())
            .then(history => {
                renderHistoryTable(history);
            });
    }

    function renderHistoryTable(history) {
        const container = document.getElementById('historyTableContainer');
        const card = document.getElementById('historyContainer');
        card.style.display = 'block';

        if (history.length === 0) {
            container.innerHTML = '<p style="color: var(--qb-slate-500); font-style: italic; margin: 0; font-size: 11px;">No purchase history found for this product from this supplier.</p>';
            return;
        }

        let html = `
            <table class="history-table">
                <thead>
                    <tr>
                        <th>GRN Batch #</th>
                        <th>Purchase Date</th>
                        <th style="text-align: right;">Unit Cost</th>
                        <th style="text-align: right;">Qty Purchased</th>
                        <th style="text-align: center; width:80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        history.forEach(h => {
            html += `
                <tr>
                    <td style="font-family:var(--qb-mono); font-weight:600;">${escapeHtml(h.grn_number)}</td>
                    <td>${escapeHtml(h.grn_date)}</td>
                    <td style="text-align: right; font-weight: 600;">Rs. ${parseFloat(h.unit_cost).toFixed(2)}</td>
                    <td style="text-align: right;">${parseFloat(h.quantity).toFixed(0)}</td>
                    <td style="text-align: center;">
                        <button type="button" class="qb-btn" style="background:#fef3c7; color:#92400e; padding: 4px 8px; font-size:10px; border-radius:4px;" onclick="selectHistoryRecord('${h.grn_id}', '${escapeHtml(h.grn_number)}', ${h.unit_cost})">
                            <i class="fa-solid fa-check"></i> Apply
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function selectHistoryRecord(grnId, grnNumber, cost) {
        if (!currentActiveRow) return;
        
        currentActiveRow.querySelector('input[name="price[]"]').value = cost.toFixed(2);
        currentActiveRow.querySelector('.grn-id-hidden').value = grnId;
        currentActiveRow.querySelector('.grn-display-input').value = grnNumber;
        
        calculateLineTotal(currentActiveRow.querySelector('input[name="price[]"]'));
        
        // Flash display
        const card = document.getElementById('historyContainer');
        card.style.backgroundColor = '#fef3c7';
        setTimeout(() => { card.style.backgroundColor = '#fdfbf7'; }, 300);
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
        return str
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

    // --- Searchable Select Component for Grid ---
    function convertSelectToSearchable(select, onChangeCallback) {
        const wrapper = document.createElement('div');
        wrapper.className = 'searchable-select-wrapper';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'searchable-select-input';
        input.placeholder = 'Search item...';
        input.autocomplete = 'off';

        const dropdown = document.createElement('div');
        dropdown.className = 'searchable-select-dropdown';

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        wrapper.appendChild(input);
        wrapper.appendChild(dropdown);

        let visibleItems = [];
        let highlightedIndex = -1;

        function buildOptionsList(showAll = false) {
            dropdown.innerHTML = '';
            visibleItems = [];
            const filter = input.value.toLowerCase();
            let hasVisible = false;

            for (let i = 0; i < select.options.length; i++) {
                const option = select.options[i];
                if (!option.value && !option.textContent.trim()) continue; // Skip empty placeholder

                const text = option.textContent.toLowerCase();
                const sku = option.getAttribute('data-sku') || '';
                const sampleCode = option.getAttribute('data-sample-code') || '';
                
                const searchString = `${text} ${sku.toLowerCase()} ${sampleCode.toLowerCase()}`;

                if (showAll || searchString.includes(filter)) {
                    hasVisible = true;
                    const item = document.createElement('div');
                    item.className = 'searchable-select-item';
                    
                    if (option.selected) item.classList.add('selected');

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = option.textContent;
                    item.appendChild(labelSpan);

                    let subtitleText = '';
                    if (sku) subtitleText += `SKU: ${sku}`;
                    if (sampleCode) subtitleText += (subtitleText ? ' | ' : '') + `Code: ${sampleCode}`;

                    if (subtitleText) {
                        const subSpan = document.createElement('span');
                        subSpan.className = 'searchable-select-item-sub';
                        subSpan.textContent = subtitleText;
                        item.appendChild(subSpan);
                    }

                    item.addEventListener('mousedown', function(e) {
                        e.preventDefault(); 
                        selectOption(option);
                    });

                    dropdown.appendChild(item);
                    visibleItems.push({ element: item, option: option });
                }
            }
            dropdown.style.display = hasVisible ? 'block' : 'none';
        }

        function selectOption(option) {
            select.value = option.value;
            input.value = option.textContent;
            dropdown.style.display = 'none';
            highlightedIndex = -1;
            
            if(onChangeCallback) onChangeCallback(select);
        }

        input.addEventListener('focus', function() {
            buildOptionsList(true);
            dropdown.style.display = 'block';
        });

        input.addEventListener('input', function() {
            buildOptionsList(false);
        });

        input.addEventListener('blur', function() {
            // Delay to allow mousedown to fire on items
            setTimeout(() => {
                dropdown.style.display = 'none';
                const selectedOpt = select.options[select.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    input.value = selectedOpt.textContent;
                } else {
                    input.value = '';
                }
            }, 150);
        });
    }

</script>
