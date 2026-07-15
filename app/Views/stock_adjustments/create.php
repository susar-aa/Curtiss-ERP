<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — CREATE STOCK ADJUSTMENT
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-green:        #34c759;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08);

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-pill: 999px;

    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
}

.create-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px 100px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.page-header {
    margin-bottom: 24px;
}
.eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 4px;
}
.title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.flash-msg {
    padding: 14px 20px;
    border-radius: var(--r-md);
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}
.flash-msg-error { background: var(--c-red-light); color: #bd2130; border: 0.5px solid rgba(255,59,48,0.3); }

.card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 24px;
    margin-bottom: 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-field-full {
    grid-column: span 3;
}
.label {
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
}
.label-required::after {
    content: " *";
    color: var(--c-red);
}
.select, .input, .textarea {
    background: rgba(120,120,128,0.08);
    border: 0.5px solid transparent;
    border-radius: var(--r-sm);
    padding: 10px 14px;
    font-size: 14px;
    font-family: var(--f-system);
    color: var(--t-primary);
    outline: none;
    transition: all var(--dur-fast);
}
.select:focus, .input:focus, .textarea:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
}
.textarea {
    resize: vertical;
    min-height: 80px;
}

/* ---- Product Picker ---- */
.picker-container {
    background: var(--c-surface2);
    border: 0.5px dashed var(--c-separator);
    border-radius: var(--r-md);
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.search-select-wrapper {
    position: relative;
    flex-grow: 1;
}
.search-dropdown {
    position: absolute;
    top: 100%; left: 0; right: 0;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    box-shadow: var(--shadow-md);
    z-index: 100;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.dropdown-item {
    padding: 10px 14px;
    cursor: pointer;
    font-size: 13px;
    border-bottom: 0.5px solid var(--c-separator2);
}
.dropdown-item:last-child { border-bottom: none; }
.dropdown-item:hover { background: var(--c-blue-light); }

/* ---- Items Grid Table ---- */
.items-table {
    width: 100%;
    border-collapse: collapse;
}
.items-table th {
    background: var(--c-surface2);
    padding: 12px 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--t-secondary);
    border-bottom: 0.5px solid var(--c-separator);
    letter-spacing: 0.05em;
    text-align: left;
}
.items-table td {
    padding: 12px 14px;
    font-size: 14px;
    border-bottom: 0.5px solid var(--c-separator2);
}

.qty-input {
    background: rgba(120,120,128,0.08);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 6px 10px;
    font-size: 13px;
    font-weight: 700;
    font-family: var(--f-mono);
    width: 90px;
    text-align: center;
    color: var(--t-primary);
    outline: none;
}
.cost-input {
    background: rgba(120,120,128,0.08);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 6px 10px;
    font-size: 13px;
    font-weight: 600;
    font-family: var(--f-mono);
    width: 110px;
    text-align: right;
    color: var(--t-primary);
    outline: none;
}
.total-cell {
    font-family: var(--f-mono);
    font-weight: 700;
    text-align: right;
}
.btn-delete {
    background: transparent;
    border: none;
    color: var(--c-red);
    cursor: pointer;
    font-size: 15px;
    padding: 6px;
    border-radius: 50%;
}
.btn-delete:hover {
    background: var(--c-red-light);
}

.actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
}
.btn {
    padding: 12px 24px;
    border-radius: var(--r-pill);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-secondary { background: var(--c-fill); color: var(--t-primary); }
.btn-secondary:hover { background: rgba(120,120,128,0.2); }
.btn-primary { background: var(--c-blue); color: #fff; }
.btn-primary:hover { background: #0066cc; }
</style>

<div class="create-wrap">
    <!-- Header -->
    <div class="page-header">
        <div class="eyebrow">Operations</div>
        <div class="title">Create Stock Adjustment</div>
    </div>

    <!-- Errors -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-msg flash-msg-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/stockadjustment/store" id="adjForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <!-- Header Card -->
        <div class="card">
            <div class="form-grid">
                <!-- Warehouse -->
                <div class="form-field">
                    <label class="label label-required">Warehouse</label>
                    <select name="warehouse_id" class="select" required>
                        <option value="">-- Choose Warehouse --</option>
                        <?php foreach ($data['warehouses'] as $wh): ?>
                            <option value="<?= $wh->id; ?>"><?= htmlspecialchars($wh->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Reason -->
                <div class="form-field">
                    <label class="label label-required">Reason / Correction Type</label>
                    <select name="reason" class="select" required>
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
                <div class="form-field">
                    <label class="label label-required">Adjustment Date</label>
                    <input type="date" name="adjustment_date" class="input" value="<?= date('Y-m-d'); ?>" required>
                </div>

                <!-- Remarks -->
                <div class="form-field form-field-full">
                    <label class="label">General Remarks</label>
                    <textarea name="remarks" class="textarea" placeholder="Describe the reason for this manual adjustment..."></textarea>
                </div>
            </div>
        </div>

        <!-- Product Picker Section -->
        <h3 style="font-weight: 700; margin-bottom: 12px; margin-top: 24px;">Adjusted Products List</h3>
        <div class="picker-container">
            <div class="search-select-wrapper">
                <input type="text" id="productSearchInput" class="input" style="width: 100%;" placeholder="Search product by name, item code, barcode..." autocomplete="off">
                <div class="search-dropdown" id="searchDropdown"></div>
            </div>
            <button type="button" class="btn btn-secondary" id="btnAddSearched" style="flex-shrink: 0;"><i class="fa-solid fa-plus"></i> Add Item</button>
        </div>

        <!-- Items Grid Card -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">SKU / Code</th>
                        <th style="width: 35%;">Product Name</th>
                        <th style="width: 12%; text-align: center;">Adjustment Qty</th>
                        <th style="width: 15%; text-align: right;">Unit Cost</th>
                        <th style="width: 15%; text-align: right;">Total Value</th>
                        <th style="width: 20%;">Item Remarks</th>
                        <th style="width: 5%; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="adjustmentGridBody">
                    <tr id="emptyGridRow">
                        <td colspan="7" style="text-align: center; color: var(--t-secondary); padding: 30px;">
                            No items added yet. Search and select products above to adjust.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Form Submission -->
        <div class="actions">
            <a href="<?= APP_URL ?>/stockadjustment" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Adjustment Request</button>
        </div>
    </form>
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

    // 1. Search filter autocomplete dropdown
    searchInput.addEventListener('input', function() {
        const term = searchInput.value.toLowerCase().trim();
        dropdown.innerHTML = '';
        console.log(`[Search autocomplete] User input: "${term}"`);

        if (!term) {
            dropdown.style.display = 'none';
            selectedItem = null;
            return;
        }

        const matches = itemsList.filter(item => 
            (item.name && item.name.toLowerCase().includes(term)) || 
            (item.item_code && item.item_code.toLowerCase().includes(term)) || 
            (item.barcode && item.barcode.toLowerCase().includes(term))
        ).slice(0, 10);

        console.log(`[Search autocomplete] Found ${matches.length} matches.`);

        if (matches.length > 0) {
            matches.forEach(item => {
                const div = document.createElement('div');
                div.className = 'dropdown-item';
                div.innerHTML = `<strong>${item.item_code}</strong> - ${item.name} (${item.qty} in stock)`;
                div.addEventListener('click', function() {
                    console.log(`[Search autocomplete] Item selected via click: ID: ${item.id} | Code: ${item.item_code} | Name: ${item.name}`);
                    searchInput.value = `${item.item_code} - ${item.name}`;
                    selectedItem = item;
                    dropdown.style.display = 'none';
                });
                dropdown.appendChild(div);
            });
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
            selectedItem = null;
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

        const rowKey = `${item.id}_${item.variation_option_id || 0}`;
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

        const tr = document.createElement('tr');
        tr.id = `grid_row_${rowKey}`;
        tr.innerHTML = `
            <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);">
                <input type="hidden" name="item_ids[]" value="${item.id}">
                <input type="hidden" name="variation_option_ids[]" value="${item.variation_option_id || ''}">
                ${item.item_code}
            </td>
            <td>
                <div style="font-weight: 600;">${item.name}</div>
                <div style="font-size: 11px; color: var(--t-secondary);">${item.category_name || 'General'}</div>
            </td>
            <td style="text-align: center;">
                <!-- Qty can be positive or negative -->
                <input type="number" step="0.01" name="quantities[]" class="qty-input grid-qty" value="1.00" required>
            </td>
            <td style="text-align: right;">
                <input type="number" step="0.01" name="unit_costs[]" class="cost-input grid-cost" value="${cost.toFixed(2)}" required>
            </td>
            <td class="total-cell" id="total_val_${rowKey}">
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
        const qtyEl = tr.querySelector('.grid-qty');
        const costEl = tr.querySelector('.grid-cost');
        
        function updateRowTotal() {
            const q = parseFloat(qtyEl.value) || 0;
            const c = parseFloat(costEl.value) || 0;
            const tot = Math.abs(q * c);
            console.log(`[Grid Calculation] Row Total Updated: Row: ${rowKey} | Qty: ${q} | Cost: ${c} | Total: ${tot.toFixed(2)}`);
            document.getElementById(`total_val_${rowKey}`).textContent = tot.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        qtyEl.addEventListener('input', updateRowTotal);
        costEl.addEventListener('input', updateRowTotal);

        // Bind delete button
        tr.querySelector('.remove-item-btn').addEventListener('click', function() {
            console.log(`[Grid Action] Deleting row for product Row: ${rowKey}`);
            tr.remove();
            if (gridBody.querySelectorAll('tr:not(#emptyGridRow)').length === 0) {
                console.log("[Grid Action] Grid is empty. Displaying placeholder empty row.");
                emptyRow.style.display = '';
            }
        });

        // Reset search input
        searchInput.value = '';
        selectedItem = null;
    }

    btnAddSearched.addEventListener('click', function() {
        if (!selectedItem) {
            console.warn("[Grid Action] Add clicked but no product is selected.");
            alert('Please select a product from the autocomplete dropdown list first.');
            return;
        }
        addProductToGrid(selectedItem);
    });

    // Allow Enter key to trigger adding item
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedItem) {
                console.log(`[Grid Action] Enter key pressed. Selected item: ID: ${selectedItem.id}`);
                addProductToGrid(selectedItem);
            }
        }
    });

    // Log form submissions
    document.getElementById('adjForm').addEventListener('submit', function() {
        console.log("[Form Submission] Submitting stock adjustment request.");
    });
});
</script>
