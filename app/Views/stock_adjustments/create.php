<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    /* =====================================================
       MODERN BILLING PANEL — STOCK ADJUSTMENTS
       ===================================================== */
    html, body { overflow: hidden; height: 100%; margin: 0; }

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
        --f-mono:        ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;
        --c-blue:        var(--primary);
        --t-secondary:   var(--slate-500, #64748b);
    }

    /* ── Wrapper ── */
    .qb-wrapper {
        background: var(--slate-100);
        font-family: var(--font);
        font-size: 13px;
        color: var(--slate-800);
        height: calc(100vh - 30px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 10px 12px;
        box-sizing: border-box;
    }

    /* ── Main card ── */
    .qb-container {
        background: var(--white);
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: 0;
        box-sizing: border-box;
        overflow: hidden;
    }

    #adjForm {
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow: hidden;
        margin: 0;
    }

    /* ── Top nav bar ── */
    .inv-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 18px;
        border-bottom: 1px solid var(--slate-200);
        background: var(--white);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        flex-shrink: 0;
    }
    .inv-topbar-left { display: flex; align-items: center; gap: 10px; }
    .inv-topbar-right { display: flex; align-items: center; gap: 6px; }
    .inv-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--slate-900);
        letter-spacing: -0.3px;
    }
    .inv-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        background: var(--primary-light);
        color: var(--primary);
    }

    /* ── Buttons ── */
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
        transition: background 0.15s, border-color 0.15s, color 0.15s, box-shadow 0.15s;
        line-height: 1.4;
    }
    .btn:hover { background: var(--slate-100); border-color: var(--slate-400); }
    .btn-primary { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
    .btn-danger-outline { background: var(--white); color: var(--danger); border-color: #fca5a5; }
    .btn-danger-outline:hover { background: var(--danger-light); }
    .btn-sm { padding: 5px 11px; font-size: 11px; }

    /* ── Form body scroll area ── */
    .inv-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 14px 18px 0 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: var(--slate-50);
    }

    /* ── Header fields card ── */
    .inv-meta-card {
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        background: var(--white);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }
    .inv-meta-card-header {
        padding: 7px 12px;
        background: var(--slate-800);
        color: var(--white);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .inv-meta-body {
        padding: 12px 14px;
        display: grid;
        grid-template-columns: 2fr 2fr 1.5fr 3.5fr;
        gap: 14px;
        align-items: flex-start;
    }
    .inv-labeled-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .inv-labeled-field label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--slate-600);
    }
    .inv-labeled-field select,
    .inv-labeled-field input,
    .inv-labeled-field textarea {
        padding: 7px 10px;
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
    .inv-labeled-field select:focus,
    .inv-labeled-field input:focus,
    .inv-labeled-field textarea:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    /* ── Item Search bar ── */
    .search-wrapper {
        position: relative;
        flex-shrink: 0;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .search-select-wrapper {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }
    .item-search-bar {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border: 1.5px solid var(--slate-300);
        border-radius: var(--radius-md);
        font-size: 13px;
        font-family: var(--font);
        font-weight: 500;
        color: var(--slate-800);
        background: var(--white);
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .item-search-bar:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    .item-search-bar::placeholder { color: var(--slate-400); }
    .search-icon-prefix {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-400);
        font-size: 15px;
        pointer-events: none;
    }
    .search-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        width: 100%;
        background: var(--white);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        max-height: 240px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: var(--shadow-md);
    }
    .dropdown-item {
        padding: 9px 14px;
        cursor: pointer;
        list-style: none;
        border-bottom: 1px solid var(--slate-100);
        font-size: 13px;
        color: var(--slate-800);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.1s;
    }
    .dropdown-item:last-child { border-bottom: none; }
    .dropdown-item:hover,
    .dropdown-item.highlighted {
        background: var(--primary) !important;
        color: var(--white) !important;
    }
    .dropdown-item:hover *,
    .dropdown-item.highlighted * {
        color: var(--white) !important;
    }

    /* ── Line items table ── */
    .table-scroll-container {
        flex: 1;
        overflow-y: auto;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        background: var(--white);
        box-shadow: var(--shadow-sm);
        margin-bottom: 14px;
    }
    .qb-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .qb-table thead th {
        position: sticky;
        top: 0;
        background: var(--slate-800);
        color: var(--white);
        padding: 8px 10px;
        text-align: left;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-right: 1px solid rgba(255,255,255,0.08);
        z-index: 10;
    }
    .qb-table thead th:last-child { border-right: none; }
    .qb-table tbody tr { transition: background 0.1s; }
    .qb-table tbody tr:nth-child(even) { background: var(--slate-50); }
    .qb-table tbody tr:hover { background: var(--primary-light); }
    .qb-table td {
        padding: 6px 8px;
        border-bottom: 1px solid var(--slate-100);
        border-right: 1px solid var(--slate-100);
        vertical-align: middle;
    }
    .qb-table td:last-child { border-right: none; }
    .qb-table input {
        width: 100%;
        border: 1px solid var(--slate-200);
        background: var(--white);
        padding: 5px 8px;
        font-size: 12px;
        font-family: var(--font);
        color: var(--slate-800);
        box-sizing: border-box;
        border-radius: 4px;
    }
    .qb-table input:focus {
        background: var(--primary-light);
        border-color: var(--primary);
        outline: none;
    }
    .btn-delete {
        background: transparent;
        border: none;
        color: var(--danger);
        cursor: pointer;
        font-size: 14px;
        padding: 5px;
        border-radius: 50%;
    }
    .btn-delete:hover {
        background: var(--danger-light);
    }

    /* ── Footer section ── */
    .inv-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-shrink: 0;
        padding: 10px 18px;
        border-top: 1px solid var(--slate-200);
        background: var(--white);
    }
</style>

<div class="qb-wrapper">
    <!-- Errors -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div style="background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; font-weight: 500;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="qb-container">
        <form method="POST" action="<?= APP_URL ?>/stockadjustment/store" id="adjForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <!-- ═══ TOP NAV BAR ═══ -->
            <div class="inv-topbar">
                <div class="inv-topbar-left">
                    <a href="<?= APP_URL ?>/stockadjustment" class="btn">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                    <div class="inv-title">Create Stock Adjustment</div>
                    <span class="inv-type-badge">Stock Adjustment</span>
                </div>
                <div class="inv-topbar-right">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Submit Adjustment Request
                    </button>
                    <a href="<?= APP_URL ?>/stockadjustment" class="btn btn-sm btn-danger-outline">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- ═══ SCROLLABLE BODY ═══ -->
            <div class="inv-body">

                <!-- ── Header row: Adjustment Parameters Card ── -->
                <div class="inv-meta-card">
                    <div class="inv-meta-card-header">
                        <i class="fa-solid fa-sliders" style="margin-right: 6px;"></i>Adjustment Parameters
                    </div>
                    <div class="inv-meta-body">
                        <!-- Warehouse -->
                        <div class="inv-labeled-field">
                            <label>Warehouse <span style="color:#dc2626;">*</span></label>
                            <select name="warehouse_id" required>
                                <option value="">-- Choose Warehouse --</option>
                                <?php foreach ($data['warehouses'] as $wh): ?>
                                    <option value="<?= $wh->id; ?>"><?= htmlspecialchars($wh->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Reason -->
                        <div class="inv-labeled-field">
                            <label>Reason / Correction Type <span style="color:#dc2626;">*</span></label>
                            <select name="reason" required>
                                <option value="">-- Select Reason --</option>
                                <option value="Damage">Damage</option>
                                <option value="Theft">Theft</option>
                                <option value="Inventory Write-off">Inventory Write-off</option>
                                <option value="Found Item">Found Item</option>
                                <option value="Promotion">Promotion / Marketing Sample</option>
                                <option value="General Adjustment">General Adjustment</option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="inv-labeled-field">
                            <label>Adjustment Date <span style="color:#dc2626;">*</span></label>
                            <input type="date" name="adjustment_date" value="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Remarks -->
                        <div class="inv-labeled-field">
                            <label>General Remarks</label>
                            <input type="text" name="remarks" placeholder="Describe the reason for this manual adjustment...">
                        </div>
                    </div>
                </div>

                <!-- ── Item Catalog Search ── -->
                <div class="search-wrapper">
                    <div class="search-select-wrapper">
                        <i class="fa-solid fa-magnifying-glass search-icon-prefix"></i>
                        <input type="text" id="productSearchInput" class="item-search-bar"
                               placeholder="Search product catalog by name, item code, barcode, or SKU..."
                               autocomplete="off">
                        <div class="search-dropdown" id="searchDropdown"></div>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnAddSearched">
                        <i class="fa-solid fa-plus"></i> Add Item
                    </button>
                </div>

                <!-- ── Line Items Table ── -->
                <div class="table-scroll-container">
                    <table class="qb-table">
                        <thead>
                            <tr>
                                <th style="width:13%;">SKU / Code</th>
                                <th style="width:25%;">Product Name</th>
                                <th style="width:10%; text-align:center;">Current Qty</th>
                                <th style="width:10%; text-align:center;">New Qty</th>
                                <th style="width:11%; text-align:center;">Adjustment Qty</th>
                                <th style="width:11%; text-align:right;">Unit Cost</th>
                                <th style="width:11%; text-align:right;">Total Value</th>
                                <th style="width:15%;">Item Remarks</th>
                                <th style="width:30px; background:#7f1d1d;"></th>
                            </tr>
                        </thead>
                        <tbody id="adjustmentGridBody">
                            <tr id="emptyGridRow">
                                <td colspan="9" style="text-align: center; color: var(--slate-400); padding: 40px; font-weight: 500;">
                                    No items added yet. Search and select products above to adjust.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div><!-- /.inv-body -->

            <!-- ═══ FOOTER ═══ -->
            <div class="inv-footer">
                <div style="font-size: 12px; color: var(--slate-600); font-weight: 500;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 6px;"></i>
                    All stock adjustments affect ledger balances immediately upon submission.
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="<?= APP_URL ?>/stockadjustment" class="btn btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Submit Adjustment Request
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
// Parse items list from PHP backend
const itemsList = <?php echo json_encode($data['items'] ?: []); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearchInput');
    const dropdown = document.getElementById('searchDropdown');
    const btnAddSearched = document.getElementById('btnAddSearched');
    const gridBody = document.getElementById('adjustmentGridBody');
    const emptyRow = document.getElementById('emptyGridRow');

    console.log("Create Stock Adjustment view loaded. Auto-complete registry items: " + itemsList.length);

    let selectedItem = null;
    let activeSearchIndex = -1;

    function highlightSearchItem(items) {
        items.forEach((itemEl, index) => {
            if (index === activeSearchIndex) {
                itemEl.classList.add('highlighted');
                itemEl.scrollIntoView({ block: 'nearest' });
            } else {
                itemEl.classList.remove('highlighted');
            }
        });
    }

    // 1. Search filter autocomplete dropdown
    searchInput.addEventListener('input', function() {
        const term = searchInput.value.toLowerCase().trim();
        dropdown.innerHTML = '';
        activeSearchIndex = -1;
        console.log(`[Search autocomplete] User input: "${term}"`);

        if (!term) {
            dropdown.style.display = 'none';
            selectedItem = null;
            return;
        }

        const tokens = term.split(/\s+/).filter(Boolean);
        const matches = itemsList.filter(item => {
            const searchStr = `${item.name || ''} ${item.item_code || ''} ${item.barcode || ''} ${item.sample_code || ''} ${item.category_name || ''}`.toLowerCase();
            return tokens.every(token => searchStr.includes(token));
        });

        console.log(`[Search autocomplete] Found ${matches.length} matches.`);

        if (matches.length > 0) {
            matches.forEach((item, idx) => {
                const div = document.createElement('div');
                div.className = 'dropdown-item';
                div.innerHTML = `<strong>${item.item_code}</strong> - ${item.name} (${item.qty} in stock)`;
                div.addEventListener('click', function() {
                    console.log(`[Search autocomplete] Item selected via click: ID: ${item.id} | Code: ${item.item_code} | Name: ${item.name}`);
                    dropdown.style.display = 'none';
                    searchInput.value = '';
                    selectedItem = null;
                    activeSearchIndex = -1;
                    addProductToGrid(item);
                });
                dropdown.appendChild(div);
            });
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
            selectedItem = null;
            activeSearchIndex = -1;
        }
    });

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-select-wrapper')) {
            dropdown.style.display = 'none';
        }
    });

    // 2. Add product to the adjustment grid
    function addProductToGrid(item) {
        if (!item) return;

        const rowKey = `${item.id}_${item.variation_option_id || 0}_${(item.item_code || '').replace(/[^a-zA-Z0-9]/g, '_')}`;
        console.log(`[Grid Action] Attempting to add product ID: ${item.id} | Variation Option ID: ${item.variation_option_id || 'None'} | Code: ${item.item_code}`);

        // Check if item already exists in the grid
        if (document.getElementById(`grid_row_${rowKey}`)) {
            console.warn(`[Grid Action] Prevented duplicate: Item row ${rowKey} is already in the list.`);
            alert('Item is already added to the list.');
            return;
        }

        if (emptyRow) {
            emptyRow.style.display = 'none';
        }

        const cost = parseFloat(item.cost_price) || 0.00;
        const currentQty = parseFloat(item.qty) || 0.00;

        const tr = document.createElement('tr');
        tr.id = `grid_row_${rowKey}`;
        tr.innerHTML = `
            <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);">
                <input type="hidden" name="item_ids[]" value="${item.id}">
                <input type="hidden" name="variation_option_ids[]" value="${item.variation_option_id || ''}">
                <input type="hidden" name="item_codes[]" value="${item.item_code || ''}">
                <input type="hidden" name="variation_names[]" value="${item.name || ''}">
                ${item.item_code}
            </td>
            <td>
                <div style="font-weight: 600;">${item.name}</div>
                <div style="font-size: 11px; color: var(--t-secondary);">${item.category_name || 'General'}</div>
            </td>
            <td style="text-align: center; font-weight: 600; color: var(--t-secondary);">
                ${currentQty.toFixed(2)}
            </td>
            <td style="text-align: center;">
                <input type="number" step="0.01" class="qty-input grid-new-qty" value="${(currentQty + 1).toFixed(2)}" style="width: 80px;" required>
            </td>
            <td style="text-align: center;">
                <!-- Qty can be positive or negative -->
                <input type="number" step="0.01" name="quantities[]" class="qty-input grid-qty" value="1.00" style="width: 80px;" required>
            </td>
            <td style="text-align: right;">
                <input type="number" step="0.01" name="unit_costs[]" class="cost-input grid-cost" value="${cost.toFixed(2)}" style="text-align: right;" required>
            </td>
            <td class="total-cell" id="total_val_${rowKey}" style="text-align: right;">
                ${cost.toFixed(2)}
            </td>
            <td>
                <input type="text" name="item_remarks[]" class="input" style="width: 100%; padding: 6px 10px; font-size: 13px;" placeholder="Item note...">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-delete remove-item-btn" data-id="${item.id}"><i class="fa-solid fa-trash-can"></i></button>
            </td>
        `;

        gridBody.appendChild(tr);
        console.log(`[Grid Action] Product row ${rowKey} successfully added to grid.`);

        // Bind update triggers
        const newQtyEl = tr.querySelector('.grid-new-qty');
        const qtyEl = tr.querySelector('.grid-qty');
        const costEl = tr.querySelector('.grid-cost');
        const remarksEl = tr.querySelector('input[name="item_remarks[]"]');
        
        function updateRowTotal() {
            const q = parseFloat(qtyEl.value) || 0;
            const c = parseFloat(costEl.value) || 0;
            const tot = Math.abs(q * c);
            console.log(`[Grid Calculation] Row Total Updated: Row: ${rowKey} | Qty: ${q} | Cost: ${c} | Total: ${tot.toFixed(2)}`);
            document.getElementById(`total_val_${rowKey}`).textContent = tot.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        newQtyEl.addEventListener('input', function() {
            const newQty = parseFloat(newQtyEl.value) || 0;
            const diff = newQty - currentQty;
            qtyEl.value = diff.toFixed(2);
            updateRowTotal();
        });

        qtyEl.addEventListener('input', function() {
            const diff = parseFloat(qtyEl.value) || 0;
            const newQty = currentQty + diff;
            newQtyEl.value = newQty.toFixed(2);
            updateRowTotal();
        });

        costEl.addEventListener('input', updateRowTotal);

        // Allow Enter key in row inputs to return focus back to the search bar
        function handleEnterToSearch(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchInput.focus();
            }
        }
        newQtyEl.addEventListener('keydown', handleEnterToSearch);
        qtyEl.addEventListener('keydown', handleEnterToSearch);
        costEl.addEventListener('keydown', handleEnterToSearch);
        if (remarksEl) remarksEl.addEventListener('keydown', handleEnterToSearch);

        // Bind delete button
        tr.querySelector('.remove-item-btn').addEventListener('click', function() {
            console.log(`[Grid Action] Deleting row for product Row: ${rowKey}`);
            tr.remove();
            if (gridBody.querySelectorAll('tr:not(#emptyGridRow)').length === 0) {
                console.log("[Grid Action] Grid is empty. Displaying placeholder empty row.");
                emptyRow.style.display = '';
            }
        });

        // Reset search input and autofocus on the New QTY column
        searchInput.value = '';
        selectedItem = null;
        activeSearchIndex = -1;
        dropdown.style.display = 'none';

        setTimeout(() => {
            if (newQtyEl) {
                newQtyEl.focus();
                newQtyEl.select();
            }
        }, 50);
    }

    btnAddSearched.addEventListener('click', function() {
        const items = dropdown.querySelectorAll('.dropdown-item');
        if (activeSearchIndex >= 0 && activeSearchIndex < items.length) {
            items[activeSearchIndex].click();
        } else if (selectedItem) {
            addProductToGrid(selectedItem);
        } else if (items.length > 0) {
            items[0].click();
        } else {
            alert('Please select a product from the autocomplete dropdown list first.');
        }
    });

    // Keyboard Navigation & Enter key to trigger adding item
    searchInput.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.dropdown-item');
        if (e.key === 'ArrowDown' || e.key === 'Down' || e.keyCode === 40) {
            if (items.length === 0) return;
            e.preventDefault();
            activeSearchIndex++;
            if (activeSearchIndex >= items.length) activeSearchIndex = 0;
            highlightSearchItem(items);
        } else if (e.key === 'ArrowUp' || e.key === 'Up' || e.keyCode === 38) {
            if (items.length === 0) return;
            e.preventDefault();
            activeSearchIndex--;
            if (activeSearchIndex < 0) activeSearchIndex = items.length - 1;
            highlightSearchItem(items);
        } else if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            if (activeSearchIndex >= 0 && activeSearchIndex < items.length) {
                items[activeSearchIndex].click();
            } else if (selectedItem) {
                addProductToGrid(selectedItem);
            } else if (items.length > 0) {
                items[0].click();
            }
        }
    });

    // Log form submissions
    document.getElementById('adjForm').addEventListener('submit', function() {
        console.log("[Form Submission] Submitting stock adjustment request.");
    });
});
</script>
