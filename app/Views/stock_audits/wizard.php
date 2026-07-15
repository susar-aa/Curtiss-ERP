<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — STOCK COUNT WIZARD
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
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
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

.wizard-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 24px 100px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.page-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
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
.flash-msg-success { background: var(--c-green-light); color: #1e7e34; border: 0.5px solid rgba(52,199,89,0.3); }
.flash-msg-error { background: var(--c-red-light); color: #bd2130; border: 0.5px solid rgba(255,59,48,0.3); }

/* ---- Meta Info Card ---- */
.meta-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.meta-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--t-label);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.meta-val {
    font-size: 15px;
    font-weight: 700;
}

/* ---- Controls Bar ---- */
.controls-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}
.search-wrapper {
    position: relative;
    flex-grow: 1;
    max-width: 400px;
}
.search-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--t-secondary);
    font-size: 14px;
}
.search-input {
    background: rgba(120,120,128,0.12);
    border: 0.5px solid transparent;
    border-radius: var(--r-pill);
    padding: 10px 14px 10px 38px;
    font-size: 14px;
    font-family: var(--f-system);
    color: var(--t-primary);
    outline: none;
    width: 100%;
    transition: all var(--dur-fast);
}
.search-input:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
}

.barcode-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--c-blue-light);
    border: 0.5px solid rgba(0,122,255,0.25);
    padding: 8px 16px;
    border-radius: var(--r-pill);
}
.barcode-input {
    background: transparent;
    border: none;
    outline: none;
    font-size: 13px;
    font-weight: 600;
    color: var(--c-blue);
    width: 150px;
    font-family: var(--f-mono);
}
.barcode-input::placeholder {
    color: rgba(0,122,255,0.6);
}

/* ---- Items Sheet Grid ---- */
.sheet-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 24px;
}
.sheet-table {
    width: 100%;
    border-collapse: collapse;
}
.sheet-table th {
    background: var(--c-surface2);
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--t-secondary);
    border-bottom: 0.5px solid var(--c-separator);
    letter-spacing: 0.05em;
}
.sheet-table td {
    padding: 12px 16px;
    font-size: 14px;
    border-bottom: 0.5px solid var(--c-separator2);
}
.sheet-table tr:last-child td {
    border-bottom: none;
}
.sheet-table tr.highlighted td {
    background: #fffae6 !important;
}

.qty-input {
    background: rgba(120,120,128,0.08);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 700;
    font-family: var(--f-mono);
    width: 90px;
    text-align: center;
    color: var(--t-primary);
    outline: none;
    transition: all var(--dur-fast);
}
.qty-input:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
}

.val-text {
    font-family: var(--f-mono);
    font-weight: 600;
}
.val-positive { color: var(--c-green); }
.val-negative { color: var(--c-red); }

.remarks-input {
    background: rgba(120,120,128,0.08);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 8px 12px;
    font-size: 13px;
    width: 100%;
    color: var(--t-primary);
    outline: none;
}

/* ---- Bottom Actions ---- */
.bottom-panel {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    padding: 24px;
    box-shadow: var(--shadow-md);
}
.textarea-remarks {
    background: rgba(120,120,128,0.08);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 12px 16px;
    font-size: 14px;
    color: var(--t-primary);
    outline: none;
    width: 100%;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 20px;
}
.button-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
.btn-secondary:hover { background: var(--c-fill2); }
.btn-primary { background: var(--c-blue); color: #fff; }
.btn-primary:hover { background: #0066cc; }
.btn-success { background: var(--c-green); color: #fff; }
.btn-success:hover { background: #2fb34f; }
.btn-danger { background: var(--c-red-light); color: var(--c-red); }
.btn-danger:hover { background: rgba(255,59,48,0.15); }
</style>

<div class="wizard-wrap">
    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="eyebrow">Stock Count</div>
            <div class="title"><?= htmlspecialchars($data['audit']->audit_number); ?></div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= APP_URL ?>/stockaudit/printCountSheet/<?= $data['audit']->id; ?>" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-print"></i> Blank count sheet
            </a>
            <form method="POST" action="<?= APP_URL ?>/stockaudit/cancel/<?= $data['audit']->id; ?>" onsubmit="return confirm('Are you sure you want to cancel this stock audit?');">
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Cancel Audit</button>
            </form>
        </div>
    </div>

    <!-- Meta Info -->
    <div class="meta-card">
        <div class="meta-item">
            <span class="meta-label">Warehouse</span>
            <span class="meta-val"><?= htmlspecialchars($data['audit']->warehouse_name); ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Filters Applied</span>
            <span class="meta-val" style="font-size: 12px; font-weight: 500; color: var(--t-secondary);">
                Cat: <?= $data['audit']->category_id ? 'Yes' : 'All'; ?> | 
                Brand: <?= $data['audit']->brand ? htmlspecialchars($data['audit']->brand) : 'All'; ?> | 
                Supplier: <?= $data['audit']->supplier_id ? 'Yes' : 'All'; ?>
            </span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Current Status</span>
            <span class="meta-val">
                <span class="badge badge-progress"><?= htmlspecialchars($data['audit']->status); ?></span>
            </span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Total Items</span>
            <span class="meta-val" id="summaryTotalItems"><?= count($data['items']); ?></span>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="controls-card">
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="wizardSearch" class="search-input" placeholder="Search product name, code, barcode...">
        </div>

        <div class="barcode-wrapper" title="Focus here and scan barcodes using a scanner">
            <i class="fa-solid fa-barcode" style="color: var(--c-blue);"></i>
            <input type="text" id="barcodeScannerInput" class="barcode-input" placeholder="Scan Barcode..." autocomplete="off">
        </div>
    </div>

    <!-- Counting Form Sheet -->
    <form id="countingForm" method="POST" action="<?= APP_URL ?>/stockaudit/saveCount/<?= $data['audit']->id; ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="formActionField" name="action" value="save_draft">

        <div class="sheet-card">
            <table class="sheet-table">
                <thead>
                    <tr>
                        <th>Item Code / SKU</th>
                        <th>Product Details</th>
                        <th>Barcode</th>
                        <th style="text-align: right;">System Qty</th>
                        <th style="text-align: center;">Physical Qty</th>
                        <th style="text-align: right;">Difference</th>
                        <th style="text-align: right;">Unit Cost</th>
                        <th style="text-align: right;">Variance Value</th>
                        <th style="width: 250px;">Item Remarks</th>
                    </tr>
                </thead>
                <tbody id="wizardTableBody">
                    <?php foreach ($data['items'] as $item): ?>
                        <tr class="wizard-row" 
                            id="row_<?= $item->item_id; ?>" 
                            data-code="<?= htmlspecialchars($item->item_code); ?>" 
                            data-name="<?= htmlspecialchars($item->item_name); ?>" 
                            data-barcode="<?= htmlspecialchars($item->barcode); ?>">
                            
                            <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($item->item_code); ?></td>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($item->item_name); ?></div>
                                <div style="font-size: 11px; color: var(--t-secondary);"><?= htmlspecialchars($item->category_name ?? 'General'); ?></div>
                            </td>
                            <td style="font-family: var(--f-mono); font-size: 13px;"><?= htmlspecialchars($item->barcode ?: '-'); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 500;" id="sys_<?= $item->item_id; ?>"><?= number_format($item->system_qty, 2); ?></td>
                            <td style="text-align: center;">
                                <input type="number" 
                                       step="0.01" 
                                       name="counts[<?= $item->item_id; ?>]" 
                                       class="qty-input count-qty" 
                                       data-id="<?= $item->item_id; ?>" 
                                       value="<?= htmlspecialchars($item->physical_qty); ?>" 
                                       autocomplete="off">
                            </td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="diff_<?= $item->item_id; ?>">0.00</td>
                            <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);" id="cost_<?= $item->item_id; ?>"><?= number_format($item->unit_cost, 2); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="var_<?= $item->item_id; ?>">0.00</td>
                            <td>
                                <input type="text" name="remarks[<?= $item->item_id; ?>]" class="remarks-input" value="<?= htmlspecialchars($item->remarks ?? ''); ?>" placeholder="Add remarks...">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Overall Remarks & Actions -->
        <div class="bottom-panel">
            <h3 style="margin-top: 0; margin-bottom: 12px; font-weight: 700;">Overall Audit Remarks</h3>
            <textarea name="overall_remarks" class="textarea-remarks" placeholder="Enter overall remarks, discrepancies noted, or reason for count..."><?= htmlspecialchars($data['audit']->overall_remarks ?? ''); ?></textarea>

            <div class="button-row">
                <a href="<?= APP_URL ?>/stockaudit" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" id="btnSaveDraft" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Progress Draft</button>
                    <button type="submit" id="btnFinalize" class="btn btn-success"><i class="fa-solid fa-circle-check"></i> Finalize Count Sheet</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInputs = document.querySelectorAll('.count-qty');
    const searchInput = document.getElementById('wizardSearch');
    const barcodeInput = document.getElementById('barcodeScannerInput');
    const form = document.getElementById('countingForm');
    const formActionField = document.getElementById('formActionField');
    const btnSaveDraft = document.getElementById('btnSaveDraft');
    const btnFinalize = document.getElementById('btnFinalize');

    // 1. Recalculate row differences & variances in real-time
    function updateRowValues(input) {
        const itemId = input.getAttribute('data-id');
        const physical = parseFloat(input.value) || 0.00;

        const systemText = document.getElementById('sys_' + itemId).textContent.replace(/,/g, '');
        const system = parseFloat(systemText) || 0.00;

        const costText = document.getElementById('cost_' + itemId).textContent.replace(/,/g, '');
        const cost = parseFloat(costText) || 0.00;

        const diff = physical - system;
        const varianceVal = diff * cost;

        const diffEl = document.getElementById('diff_' + itemId);
        diffEl.textContent = (diff >= 0 ? '+' : '') + diff.toFixed(2);
        
        if (diff > 0) {
            diffEl.className = 'val-text val-positive';
        } else if (diff < 0) {
            diffEl.className = 'val-text val-negative';
        } else {
            diffEl.className = 'val-text';
        }

        const varEl = document.getElementById('var_' + itemId);
        varEl.textContent = (varianceVal >= 0 ? '+' : '') + varianceVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        if (varianceVal > 0) {
            varEl.className = 'val-text val-positive';
        } else if (varianceVal < 0) {
            varEl.className = 'val-text val-negative';
        } else {
            varEl.className = 'val-text';
        }
    }

    qtyInputs.forEach(input => {
        updateRowValues(input);
        input.addEventListener('input', () => updateRowValues(input));
    });

    // 2. Client-side instant filter search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.wizard-row');

            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const code = row.getAttribute('data-code').toLowerCase();
                const barcode = row.getAttribute('data-barcode').toLowerCase();

                if (name.includes(term) || code.includes(term) || barcode.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 3. Barcode Scanner Support
    if (barcodeInput) {
        barcodeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const scannedBarcode = barcodeInput.value.trim();
                barcodeInput.value = '';

                if (!scannedBarcode) return;

                const row = [...document.querySelectorAll('.wizard-row')].find(r => r.getAttribute('data-barcode') === scannedBarcode);
                if (row) {
                    // Highlight the row
                    row.classList.add('highlighted');
                    setTimeout(() => row.classList.remove('highlighted'), 2000);

                    // Scroll row into view
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Focus physical count input, increment value by 1
                    const qtyInput = row.querySelector('.count-qty');
                    if (qtyInput) {
                        qtyInput.focus();
                        let currentVal = parseFloat(qtyInput.value) || 0.00;
                        qtyInput.value = (currentVal + 1).toFixed(2);
                        updateRowValues(qtyInput);
                    }
                } else {
                    // Try by item_code
                    const rowCode = [...document.querySelectorAll('.wizard-row')].find(r => r.getAttribute('data-code') === scannedBarcode);
                    if (rowCode) {
                        rowCode.classList.add('highlighted');
                        setTimeout(() => rowCode.classList.remove('highlighted'), 2000);
                        rowCode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const qtyInput = rowCode.querySelector('.count-qty');
                        if (qtyInput) {
                            qtyInput.focus();
                            let currentVal = parseFloat(qtyInput.value) || 0.00;
                            qtyInput.value = (currentVal + 1).toFixed(2);
                            updateRowValues(qtyInput);
                        }
                    } else {
                        alert('Scanned code not found in this audit list: ' + scannedBarcode);
                    }
                }
            }
        });
    }

    // 4. Form Action Buttons
    if (btnSaveDraft) {
        btnSaveDraft.addEventListener('click', () => {
            formActionField.value = 'save_draft';
        });
    }

    if (btnFinalize) {
        btnFinalize.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to finalize this stock count? This will lock edits and make it ready for approval.')) {
                e.preventDefault();
            } else {
                formActionField.value = 'complete';
            }
        });
    }
});
</script>
