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

/* ---- Variations Expandable Styles ---- */
.variation-item-row.hidden {
    display: none !important;
}
.toggle-var-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--t-secondary);
    width: 24px;
    height: 24px;
    border-radius: var(--r-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background var(--dur-fast), color var(--dur-fast);
    margin-right: 6px;
}
.toggle-var-btn:hover {
    background: rgba(0, 0, 0, 0.05);
    color: var(--t-primary);
}
.has-variations-parent td {
    background-color: rgba(0, 122, 255, 0.04) !important;
}
.has-variations-parent td:first-child {
    border-left: 4px solid var(--c-blue) !important;
}
.variation-item-row td {
    background-color: rgba(0, 122, 255, 0.01) !important;
}
.variation-item-row td:first-child {
    border-left: 4px solid var(--c-blue) !important;
}
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
        <input type="hidden" id="auditDataField" name="audit_data" value="">


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
                    <?php 
                    // Group items by item_id
                    $groupedItems = [];
                    foreach ($data['items'] as $item) {
                        $groupedItems[$item->item_id][] = $item;
                    }

                    foreach ($groupedItems as $itemId => $group): 
                        $firstItem = $group[0];
                        $hasVariations = (count($group) > 1 || !empty($firstItem->variation_option_id));
                        
                        if ($hasVariations):
                    ?>
                        <!-- Parent Product Header Row -->
                        <tr class="wizard-row has-variations-parent" 
                            id="parent_row_<?= $itemId; ?>" 
                            data-code="<?= htmlspecialchars($firstItem->base_item_code); ?>" 
                            data-name="<?= htmlspecialchars($firstItem->base_item_name); ?>" 
                            data-barcode="">
                            
                            <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);">
                                <button type="button" class="toggle-var-btn" onclick="toggleVariationsRow(<?= $itemId; ?>, this)" title="Show Variations">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <?= htmlspecialchars($firstItem->base_item_code); ?>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?= htmlspecialchars($firstItem->base_item_name); ?></div>
                                <div style="font-size: 11px; color: var(--t-secondary);"><?= htmlspecialchars($firstItem->category_name ?? 'General'); ?></div>
                            </td>
                            <td style="font-family: var(--f-mono); font-size: 13px; text-align: center; color: var(--t-label);">-</td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 500;" id="parent_sys_<?= $itemId; ?>">0.00</td>
                            <td style="text-align: center; font-family: var(--f-mono); font-weight: 600;" id="parent_phys_<?= $itemId; ?>">0.00</td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="parent_diff_<?= $itemId; ?>">0.00</td>
                            <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);">-</td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="parent_var_<?= $itemId; ?>">0.00</td>
                            <td><span style="font-size: 10px; font-weight: bold; color: var(--c-blue); background: var(--c-blue-light); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">Variable Product</span></td>
                        </tr>

                        <!-- Variation Rows -->
                        <?php foreach ($group as $item): ?>
                            <tr class="wizard-row variation-item-row hidden variations_row_<?= $itemId; ?>" 
                                id="row_<?= $item->id; ?>" 
                                data-code="<?= htmlspecialchars($item->item_code); ?>" 
                                data-name="<?= htmlspecialchars($item->item_name); ?>" 
                                data-barcode="<?= htmlspecialchars($item->barcode); ?>">
                                
                                <td style="padding-left: 36px; font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($item->item_code); ?></td>
                                <td>
                                    <div style="font-weight: 600; padding-left: 8px;"><?= htmlspecialchars($item->item_name); ?></div>
                                    <div style="font-size: 11px; color: var(--t-secondary); padding-left: 8px;"><?= htmlspecialchars($item->category_name ?? 'General'); ?></div>
                                </td>
                                <td style="font-family: var(--f-mono); font-size: 13px;"><?= htmlspecialchars($item->barcode ?: '-'); ?></td>
                                <td style="text-align: right; font-family: var(--f-mono); font-weight: 500;" id="sys_<?= $item->id; ?>"><?= number_format($item->system_qty, 2); ?></td>
                                <td style="text-align: center;">
                                    <input type="number" 
                                           step="0.01" 
                                           name="counts[<?= $item->id; ?>]" 
                                           class="qty-input count-qty" 
                                           data-id="<?= $item->id; ?>" 
                                           data-parent-id="<?= $itemId; ?>"
                                           value="<?= htmlspecialchars($item->physical_qty); ?>" 
                                           autocomplete="off">
                                </td>
                                <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="diff_<?= $item->id; ?>">0.00</td>
                                <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);" id="cost_<?= $item->id; ?>"><?= number_format($item->unit_cost, 2); ?></td>
                                <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="var_<?= $item->id; ?>">0.00</td>
                                <td>
                                    <input type="text" name="remarks[<?= $item->id; ?>]" class="remarks-input" value="<?= htmlspecialchars($item->remarks ?? ''); ?>" placeholder="Remarks...">
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: 
                        $item = $firstItem;
                    ?>
                        <!-- Simple Product Row -->
                        <tr class="wizard-row" 
                            id="row_<?= $item->id; ?>" 
                            data-code="<?= htmlspecialchars($item->item_code); ?>" 
                            data-name="<?= htmlspecialchars($item->item_name); ?>" 
                            data-barcode="<?= htmlspecialchars($item->barcode); ?>">
                            
                            <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($item->item_code); ?></td>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($item->item_name); ?></div>
                                <div style="font-size: 11px; color: var(--t-secondary);"><?= htmlspecialchars($item->category_name ?? 'General'); ?></div>
                            </td>
                            <td style="font-family: var(--f-mono); font-size: 13px;"><?= htmlspecialchars($item->barcode ?: '-'); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 500;" id="sys_<?= $item->id; ?>"><?= number_format($item->system_qty, 2); ?></td>
                            <td style="text-align: center;">
                                <input type="number" 
                                       step="0.01" 
                                       name="counts[<?= $item->id; ?>]" 
                                       class="qty-input count-qty" 
                                       data-id="<?= $item->id; ?>" 
                                       value="<?= htmlspecialchars($item->physical_qty); ?>" 
                                       autocomplete="off">
                            </td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="diff_<?= $item->id; ?>">0.00</td>
                            <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);" id="cost_<?= $item->id; ?>"><?= number_format($item->unit_cost, 2); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" id="var_<?= $item->id; ?>">0.00</td>
                            <td>
                                <input type="text" name="remarks[<?= $item->id; ?>]" class="remarks-input" value="<?= htmlspecialchars($item->remarks ?? ''); ?>" placeholder="Add remarks...">
                            </td>
                        </tr>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
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
// Expandable Toggle Function
function toggleVariationsRow(itemId, btn) {
    const rows = document.querySelectorAll('.variations_row_' + itemId);
    if (!rows.length) return;
    
    const isHidden = rows[0].classList.contains('hidden') || rows[0].style.display === 'none';
    rows.forEach(row => {
        if (isHidden) {
            row.classList.remove('hidden');
            row.style.display = '';
        } else {
            row.classList.add('hidden');
            row.style.display = 'none';
        }
    });
    
    if (isHidden) {
        btn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
        btn.setAttribute('title', 'Hide Variations');
    } else {
        btn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
        btn.setAttribute('title', 'Show Variations');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const qtyInputs = document.querySelectorAll('.count-qty');
    const searchInput = document.getElementById('wizardSearch');
    const barcodeInput = document.getElementById('barcodeScannerInput');
    const form = document.getElementById('countingForm');
    const formActionField = document.getElementById('formActionField');
    const btnSaveDraft = document.getElementById('btnSaveDraft');
    const btnFinalize = document.getElementById('btnFinalize');

    console.log("Stock Count Wizard loaded. Total count items in DOM: " + qtyInputs.length);

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

        console.log(`[Audit Calculation] Item ID: ${itemId} | Physical: ${physical} | System: ${system} | Diff: ${diff.toFixed(2)} | Unit Cost: ${cost} | Variance Val: ${varianceVal.toFixed(2)}`);

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

        const parentId = input.getAttribute('data-parent-id');
        if (parentId) {
            updateParentTotals(parentId);
        }
    }

    // Recalculate parent row totals dynamically
    function updateParentTotals(parentId) {
        const varInputs = document.querySelectorAll(`input.count-qty[data-parent-id="${parentId}"]`);
        let totalSys = 0;
        let totalPhys = 0;
        let totalDiff = 0;
        let totalVarVal = 0;

        varInputs.forEach(input => {
            const itemId = input.getAttribute('data-id');
            const physical = parseFloat(input.value) || 0.00;

            const systemText = document.getElementById('sys_' + itemId).textContent.replace(/,/g, '');
            const system = parseFloat(systemText) || 0.00;

            const costText = document.getElementById('cost_' + itemId).textContent.replace(/,/g, '');
            const cost = parseFloat(costText) || 0.00;

            const diff = physical - system;
            const varianceVal = diff * cost;

            totalSys += system;
            totalPhys += physical;
            totalDiff += diff;
            totalVarVal += varianceVal;
        });

        const parentSysEl = document.getElementById('parent_sys_' + parentId);
        const parentPhysEl = document.getElementById('parent_phys_' + parentId);
        const parentDiffEl = document.getElementById('parent_diff_' + parentId);
        const parentVarEl = document.getElementById('parent_var_' + parentId);

        if (parentSysEl) parentSysEl.textContent = totalSys.toFixed(2);
        if (parentPhysEl) parentPhysEl.textContent = totalPhys.toFixed(2);
        
        if (parentDiffEl) {
            parentDiffEl.textContent = (totalDiff >= 0 ? '+' : '') + totalDiff.toFixed(2);
            if (totalDiff > 0) {
                parentDiffEl.className = 'val-text val-positive';
            } else if (totalDiff < 0) {
                parentDiffEl.className = 'val-text val-negative';
            } else {
                parentDiffEl.className = 'val-text';
            }
        }

        if (parentVarEl) {
            parentVarEl.textContent = (totalVarVal >= 0 ? '+' : '') + totalVarVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (totalVarVal > 0) {
                parentVarEl.className = 'val-text val-positive';
            } else if (totalVarVal < 0) {
                parentVarEl.className = 'val-text val-negative';
            } else {
                parentVarEl.className = 'val-text';
            }
        }
    }

    qtyInputs.forEach(input => {
        updateRowValues(input);
        input.addEventListener('input', () => updateRowValues(input));
    });

    // Initialize parent totals on page load
    const parentIds = new Set();
    qtyInputs.forEach(input => {
        const parentId = input.getAttribute('data-parent-id');
        if (parentId) {
            parentIds.add(parentId);
        }
    });
    parentIds.forEach(parentId => updateParentTotals(parentId));

    // 2. Client-side instant filter search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.wizard-row');
            console.log(`[Search Filter] Searching for: "${term}"`);

            if (!term) {
                // If search is cleared, restore standard collapsed/expanded state
                rows.forEach(row => {
                    if (row.classList.contains('variation-item-row')) {
                        row.style.display = 'none'; // hide variations by default
                        row.classList.add('hidden');
                    } else {
                        row.style.display = '';
                    }
                    // reset toggle buttons to down chevron
                    const toggleBtn = row.querySelector('.toggle-var-btn');
                    if (toggleBtn) {
                        toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
                        toggleBtn.setAttribute('title', 'Show Variations');
                    }
                });
                return;
            }

            // Group by parent relationship
            // First hide everything
            rows.forEach(row => {
                row.style.display = 'none';
            });

            // Keep track of which parent rows need to be shown
            const visibleParents = new Set();

            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const code = row.getAttribute('data-code').toLowerCase();
                const barcode = (row.getAttribute('data-barcode') || '').toLowerCase();

                if (name.includes(term) || code.includes(term) || barcode.includes(term)) {
                    row.style.display = '';
                    if (row.classList.contains('variation-item-row')) {
                        row.classList.remove('hidden');
                        const classes = row.className.split(' ');
                        classes.forEach(cls => {
                            if (cls.startsWith('variations_row_')) {
                                const parentId = cls.replace('variations_row_', '');
                                visibleParents.add(parentId);
                            }
                        });
                    }
                }
            });

            // Show all parent rows that have matching variations
            visibleParents.forEach(parentId => {
                const parentRow = document.getElementById('parent_row_' + parentId);
                if (parentRow) {
                    parentRow.style.display = '';
                    // Set parent toggle button to up chevron since variations are visible
                    const toggleBtn = parentRow.querySelector('.toggle-var-btn');
                    if (toggleBtn) {
                        toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
                        toggleBtn.setAttribute('title', 'Hide Variations');
                    }
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

                console.log(`[Barcode Scanner] Scanned string received: "${scannedBarcode}"`);
                if (!scannedBarcode) return;

                const row = [...document.querySelectorAll('.wizard-row')].find(r => r.getAttribute('data-barcode') === scannedBarcode);
                if (row) {
                    console.log(`[Barcode Scanner] Matched row via barcode: ${scannedBarcode}`);
                    
                    // If it is a variation row, make sure it and its parent are visible and expanded
                    if (row.classList.contains('variation-item-row')) {
                        row.classList.remove('hidden');
                        row.style.display = '';
                        const classes = row.className.split(' ');
                        classes.forEach(cls => {
                            if (cls.startsWith('variations_row_')) {
                                const parentId = cls.replace('variations_row_', '');
                                const parentRow = document.getElementById('parent_row_' + parentId);
                                if (parentRow) {
                                    parentRow.style.display = '';
                                    const toggleBtn = parentRow.querySelector('.toggle-var-btn');
                                    if (toggleBtn) {
                                        toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
                                        toggleBtn.setAttribute('title', 'Hide Variations');
                                    }
                                }
                            }
                        });
                    }

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
                        console.log(`[Barcode Scanner] Incremented quantity to ${(currentVal + 1).toFixed(2)}`);
                        updateRowValues(qtyInput);
                    }
                } else {
                    // Try by item_code
                    const rowCode = [...document.querySelectorAll('.wizard-row')].find(r => r.getAttribute('data-code') === scannedBarcode);
                    if (rowCode) {
                        console.log(`[Barcode Scanner] Matched row via item code: ${scannedBarcode}`);
                        
                        if (rowCode.classList.contains('variation-item-row')) {
                            rowCode.classList.remove('hidden');
                            rowCode.style.display = '';
                            const classes = rowCode.className.split(' ');
                            classes.forEach(cls => {
                                if (cls.startsWith('variations_row_')) {
                                    const parentId = cls.replace('variations_row_', '');
                                    const parentRow = document.getElementById('parent_row_' + parentId);
                                    if (parentRow) {
                                        parentRow.style.display = '';
                                        const toggleBtn = parentRow.querySelector('.toggle-var-btn');
                                        if (toggleBtn) {
                                            toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
                                            toggleBtn.setAttribute('title', 'Hide Variations');
                                        }
                                    }
                                }
                            });
                        }

                        rowCode.classList.add('highlighted');
                        setTimeout(() => rowCode.classList.remove('highlighted'), 2000);
                        rowCode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const qtyInput = rowCode.querySelector('.count-qty');
                        if (qtyInput) {
                            qtyInput.focus();
                            let currentVal = parseFloat(qtyInput.value) || 0.00;
                            qtyInput.value = (currentVal + 1).toFixed(2);
                            console.log(`[Barcode Scanner] Incremented quantity to ${(currentVal + 1).toFixed(2)}`);
                            updateRowValues(qtyInput);
                        }
                    } else {
                        console.warn(`[Barcode Scanner] Code/Barcode not found in list: ${scannedBarcode}`);
                        alert('Scanned code not found in this audit list: ' + scannedBarcode);
                    }
                }
            }
        });
    }

    // 4. Form Action Buttons
    if (btnSaveDraft) {
        btnSaveDraft.addEventListener('click', () => {
            console.log("[Form Submission] Set action to save_draft");
            formActionField.value = 'save_draft';
        });
    }

    if (btnFinalize) {
        btnFinalize.addEventListener('click', (e) => {
            console.log("[Form Submission] Finalize clicked. Prompting confirmation.");
            if (!confirm('Are you sure you want to finalize this stock count? This will lock edits and make it ready for approval.')) {
                console.log("[Form Submission] Finalization cancelled by user.");
                e.preventDefault();
            } else {
                console.log("[Form Submission] Confirmed. Set action to complete.");
                formActionField.value = 'complete';
            }
        });
    }

    // Intercept form submission to log all inputs and save to localStorage
    form.addEventListener('submit', function(e) {
        console.log("=== FORM SUBMISSION START ===");
        console.log("Form Action Field value:", formActionField.value);
        
        const inputs = form.querySelectorAll('.count-qty');
        console.log("Total inputs found in form:", inputs.length);
        
        const auditData = {};
        const submittedCounts = [];
        inputs.forEach(input => {
            const id = input.getAttribute('data-id');
            const parentId = input.getAttribute('data-parent-id');
            const val = input.value;
            const isVariation = !!parentId;
            
            const remarkInput = form.querySelector(`input[name="remarks[${id}]"]`);
            const remark = remarkInput ? remarkInput.value : '';
            
            auditData[id] = {
                qty: val,
                remark: remark
            };
            
            submittedCounts.push({
                inputName: input.name,
                auditItemId: id,
                parentId: parentId || 'none (simple)',
                isVariation: isVariation,
                value: val
            });
        });
        
        // Put in hidden field
        const auditDataField = document.getElementById('auditDataField');
        if (auditDataField) {
            auditDataField.value = JSON.stringify(auditData);
        }
        
        // Remove names from individual inputs so they don't count towards max_input_vars
        inputs.forEach(input => {
            input.removeAttribute('name');
        });
        const remarkInputs = form.querySelectorAll('.remarks-input');
        remarkInputs.forEach(input => {
            input.removeAttribute('name');
        });
        
        console.table(submittedCounts);
        console.log("=== FORM SUBMISSION END ===");
        
        localStorage.setItem('stock_audit_debug_logs', JSON.stringify({
            timestamp: new Date().toLocaleTimeString(),
            action: formActionField.value,
            items: submittedCounts
        }));
    });

    // Read and print debug log from localStorage if present
    const debugLogs = localStorage.getItem('stock_audit_debug_logs');
    if (debugLogs) {
        localStorage.removeItem('stock_audit_debug_logs');
        try {
            const data = JSON.parse(debugLogs);
            console.group(`[DEBUG] Last Submitted Audit Data - ${data.timestamp} (${data.action})`);
            console.log("Action:", data.action);
            console.table(data.items);
            console.groupEnd();
        } catch (err) {
            console.error("Error reading debug logs:", err);
        }
    }
});
</script>
