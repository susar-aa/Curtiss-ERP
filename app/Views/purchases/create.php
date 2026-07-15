<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — CREATE/EDIT PO
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.08);
    --c-fill2:        rgba(120,120,128,0.12);
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-blue-mid:     #b3d6ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-xs: 6px;
    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-xl: 26px;
    --r-pill: 999px;

    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
    --dur-mid:     0.28s;
    --dur-slow:    0.42s;
}

.inv-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 24px 24px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

/* ---- Page Header ---- */
.inv-header {
    margin-bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.inv-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 6px;
}
.inv-title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--t-primary);
}
.btn-quick {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    text-decoration: none;
    transition: all var(--dur-fast);
}
.btn-quick:hover {
    background: var(--c-fill2);
    color: var(--t-primary);
}

/* ---- Panel Card ---- */
.create-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 24px;
    margin-bottom: 28px;
}

/* ---- Form controls ---- */
.form-group {
    margin-bottom: 18px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--t-label);
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.form-control {
    width: 100%;
    padding: 10px 14px;
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    font-size: 14px;
    font-weight: 500;
    font-family: var(--f-system);
    color: var(--t-primary);
    outline: none;
    transition: border-color var(--dur-fast), box-shadow var(--dur-fast), background var(--dur-fast);
    box-sizing: border-box;
}
.form-control:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3.5px rgba(0,122,255,0.14);
}
.grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
}

/* ---- Table Styles ---- */
.po-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 24px;
}
.po-table th {
    padding: 12px 14px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--t-label);
    background: var(--c-surface2);
    border-bottom: 0.5px solid var(--c-separator);
    text-align: left;
}
.po-table td {
    padding: 12px 14px;
    border-bottom: 0.5px solid var(--c-separator2);
    vertical-align: middle;
}
.po-table tr:hover td {
    background: var(--c-fill);
}
.line-row-num {
    text-align: center;
    color: var(--t-tertiary);
    font-weight: 700;
    font-size: 12px;
    width: 40px;
}

/* ---- Select override ---- */
.item-select {
    width: 100%;
    padding: 10px 14px;
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    font-size: 14px;
    font-weight: 500;
    color: var(--t-primary);
    outline: none;
    transition: border-color var(--dur-fast), background var(--dur-fast);
    box-sizing: border-box;
}
.item-select:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
}
optgroup { font-weight: bold; color: var(--c-blue); background: var(--c-surface); }
option { font-weight: normal; color: var(--t-primary); background: var(--c-surface);}

.total-box {
    float: right;
    width: 320px;
    padding: 18px 24px;
    background: var(--c-surface2);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    margin-top: 20px;
    text-align: right;
    font-size: 18px;
    font-weight: 700;
    box-shadow: var(--shadow-xs);
    color: var(--t-primary);
}
.total-box span {
    font-family: var(--f-mono);
    color: var(--c-blue);
}

/* ---- Buttons ---- */
.btn {
    padding: 10px 18px;
    background: var(--t-primary);
    color: #fff;
    border: none;
    border-radius: var(--r-md);
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast);
    text-decoration: none;
}
.btn:hover { filter: brightness(0.92); }
.btn:active { transform: scale(0.97); }
.btn-outline {
    background: transparent;
    border: 0.5px solid var(--c-separator);
    color: var(--t-secondary);
}
.btn-outline:hover {
    background: var(--c-fill);
    color: var(--t-primary);
}
.btn-danger {
    background: var(--c-red-light);
    color: var(--c-red);
    border: 0.5px solid rgba(255,59,48,0.25);
}
.btn-danger:hover {
    background: var(--c-red);
    color: #fff;
}

/* ---- Alerts ---- */
.sf-alert {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--c-surface);
    border-radius: var(--r-md);
    padding: 14px 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-xs);
    border: 0.5px solid var(--c-separator);
    border-left-width: 3px;
    font-size: 14px;
}
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert.info    { border-left-color: var(--c-blue); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert.info .sf-alert-icon    { color: var(--c-blue); }
.sf-alert-title { font-weight: 600; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }
</style>

<?php
$isEdit = isset($data['po']);
$actionUrl = APP_URL . '/purchase/' . ($isEdit ? "edit/{$data['po']->id}" : "create"); 
?>

<div class="inv-wrap">
    <!-- Header -->
    <div class="inv-header">
        <div>
            <div class="inv-eyebrow">Procurement</div>
            <h1 class="inv-title"><?= $isEdit ? 'Edit Purchase Order' : 'Create Purchase Order' ?></h1>
        </div>
        <a href="<?= APP_URL ?>/purchase" class="btn-quick">&larr; Back to POs</a>
    </div>

    <!-- Alert Messaging -->
    <?php if(!empty($data['error'])): ?>
    <div class="sf-alert error">
        <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Operation Error</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="create-panel">
        <form action="<?= $actionUrl ?>" method="POST" id="poForm">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update_po' : 'save_po' ?>">
            
            <div class="grid-4">
                <div class="form-group">
                    <label>Supplier / Vendor *</label>
                    <select name="vendor_id" id="vendorSelect" class="form-control" onchange="filterProducts()" required>
                        <option value="">Select Vendor...</option>
                        <?php foreach($data['vendors'] as $ven): ?>
                            <option value="<?= $ven->id ?>" <?= $data['prefilled_vendor'] == $ven->id ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>PO Number</label>
                    <input type="text" name="po_number" class="form-control" value="<?= htmlspecialchars($data['po']->po_number ?? $data['po_number']) ?>" <?= $isEdit ? 'readonly style="pointer-events:none; background:rgba(0,0,0,0.04);"' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label>Order Date</label>
                    <input type="date" name="po_date" class="form-control" value="<?= htmlspecialchars($data['po']->po_date ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Expected Delivery</label>
                    <input type="date" name="expected_date" class="form-control" value="<?= htmlspecialchars($data['po']->expected_date ?? date('Y-m-d', strtotime('+7 days'))) ?>" required>
                </div>
            </div>

            <div class="sf-alert info" style="margin-top: 10px;">
                <i class="fa-solid fa-circle-info sf-alert-icon"></i>
                <div style="flex:1;">
                    <div class="sf-alert-msg">Products are filtered by Vendor. Select specific variants directly, or choose "(MIX)" if the exact colors/sizes will be decided upon delivery.</div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="po-table" id="linesTable">
                <thead>
                    <tr>
                        <th class="line-row-num">#</th>
                        <th style="width: 50%;">Product / Variation</th>
                        <th style="width: 15%; text-align:right;">Qty</th>
                        <th style="width: 15%; text-align:right;">Unit Cost (Rs)</th>
                        <th style="width: 15%; text-align:right;">Total (Rs)</th>
                        <th style="width: 40px; text-align:center;"></th>
                    </tr>
                </thead>
                <tbody id="poBody">
                    <?php if($isEdit || !empty($data['prefilled_items'])): ?>
                        <?php $loopItems = $isEdit ? $data['items'] : $data['prefilled_items']; ?>
                        <?php $lineNum = 1; foreach($loopItems as $item): ?>
                            
                            <?php 
                                // Determine the exact value string to pre-select
                                if ($isEdit) {
                                    $vId = $item->item_id;
                                    $vOpt = $item->item_variation_option_id ?: ($item->is_mix ? 'MIX' : '0');
                                    $vMix = $item->is_mix;
                                    $selectedValue = "{$vId}|{$vOpt}|{$vMix}";
                                } else {
                                    // Coming from AI Suggester (prefilled) -> defaults to MIX if variations exist
                                    $itemRef = array_filter($data['catalog_items'], function($c) use ($item) { return $c->id == $item['item_id']; });
                                    $itemRef = reset($itemRef);
                                    $hasVars = !empty($itemRef->variations);
                                    $selectedValue = $item['item_id'] . '|' . ($hasVars ? 'MIX' : '0') . '|' . ($hasVars ? '1' : '0');
                                }
                            ?>

                            <tr>
                                <td class="line-row-num"><?= $lineNum++ ?></td>
                                <td>
                                    <select name="item_selection[]" class="item-select" onchange="autoFillSelection(this)" required>
                                        <option value="">Select Product...</option>
                                        <?php foreach($data['catalog_items'] as $catItem): ?>
                                            <?php if(!empty($catItem->variations)): ?>
                                                <optgroup label="<?= htmlspecialchars($catItem->name) ?>" data-vendor="<?= $catItem->vendor_id ?>">
                                                    <option value="<?= $catItem->id ?>|MIX|1" data-price="<?= floatval($catItem->cost_price ?? 0.00) ?>" data-name="<?= htmlspecialchars($catItem->name) ?> (MIX)" data-sku="<?= htmlspecialchars($catItem->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($catItem->sample_code ?? '') ?>" <?= $selectedValue === "{$catItem->id}|MIX|1" ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($catItem->name) ?> (MIX)
                                                    </option>
                                                    <?php foreach($catItem->variations as $var): ?>
                                                        <option value="<?= $catItem->id ?>|<?= $var->id ?>|0" data-price="<?= floatval($var->cost ?? 0) > 0 ? $var->cost : floatval($catItem->cost_price ?? 0.00) ?>" data-name="<?= htmlspecialchars($catItem->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>" data-sku="<?= htmlspecialchars($var->sku ?? $catItem->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($catItem->sample_code ?? '') ?>" <?= $selectedValue === "{$catItem->id}|{$var->id}|0" ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($catItem->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php else: ?>
                                                <option class="base-opt" value="<?= $catItem->id ?>|0|0" data-price="<?= floatval($catItem->cost_price ?? 0.00) ?>" data-vendor="<?= $catItem->vendor_id ?>" data-name="<?= htmlspecialchars($catItem->name) ?>" data-sku="<?= htmlspecialchars($catItem->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($catItem->sample_code ?? '') ?>" <?= $selectedValue === "{$catItem->id}|0|0" ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($catItem->name) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="desc[]" class="desc-hidden" value="<?= htmlspecialchars($isEdit ? $item->description : $item['name']) ?>">
                                </td>
                                <td><input type="number" name="qty[]" step="1" min="1" value="<?= $isEdit ? $item->quantity : $item['qty'] ?>" class="form-control" onchange="calcTotals()" required style="text-align:right;"></td>
                                <td><input type="number" name="price[]" step="0.01" min="0" value="<?= number_format($isEdit ? $item->unit_price : $item['cost'], 2, '.', '') ?>" class="form-control" onchange="calcTotals()" required style="font-weight:600; text-align:right; font-family:var(--f-mono);"></td>
                                <td><input type="text" class="form-control line-total" value="<?= number_format($isEdit ? $item->total : ($item['qty'] * $item['cost']), 2, '.', '') ?>" readonly style="font-weight:700; text-align:right; font-family:var(--f-mono); background:transparent; border:none; pointer-events:none;"></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-danger" style="padding: 6px; width:28px; height:28px; border-radius:50%;" onclick="removeRow(this)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Standard Blank Row -->
                        <tr>
                            <td class="line-row-num">1</td>
                            <td>
                                <select name="item_selection[]" class="item-select" onchange="autoFillSelection(this)" required>
                                    <option value="">Select Product...</option>
                                    <?php foreach($data['catalog_items'] as $item): ?>
                                        <?php if(!empty($item->variations)): ?>
                                            <optgroup label="<?= htmlspecialchars($item->name) ?>" data-vendor="<?= $item->vendor_id ?>">
                                                <option value="<?= $item->id ?>|MIX|1" data-price="<?= floatval($item->cost_price ?? 0.00) ?>" data-name="<?= htmlspecialchars($item->name) ?> (MIX)" data-sku="<?= htmlspecialchars($item->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($item->sample_code ?? '') ?>">
                                                    <?= htmlspecialchars($item->name) ?> (MIX)
                                                </option>
                                                <?php foreach($item->variations as $var): ?>
                                                    <option value="<?= $item->id ?>|<?= $var->id ?>|0" data-price="<?= floatval($var->cost ?? 0) > 0 ? $var->cost : floatval($item->cost_price ?? 0.00) ?>" data-name="<?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>" data-sku="<?= htmlspecialchars($var->sku ?? $item->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($item->sample_code ?? '') ?>">
                                                        <?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php else: ?>
                                            <option class="base-opt" value="<?= $item->id ?>|0|0" data-price="<?= floatval($item->cost_price ?? 0.00) ?>" data-vendor="<?= $item->vendor_id ?>" data-name="<?= htmlspecialchars($item->name) ?>" data-sku="<?= htmlspecialchars($item->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($item->sample_code ?? '') ?>">
                                                <?= htmlspecialchars($item->name) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="desc[]" class="desc-hidden">
                            </td>
                            <td><input type="number" name="qty[]" step="1" min="1" value="1" class="form-control" onchange="calcTotals()" required style="text-align:right;"></td>
                            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" class="form-control" onchange="calcTotals()" required style="font-weight:600; text-align:right; font-family:var(--f-mono);"></td>
                            <td><input type="text" class="form-control line-total" value="0.00" readonly style="font-weight:700; text-align:right; font-family:var(--f-mono); background:transparent; border:none; pointer-events:none;"></td>
                            <td style="text-align: center;"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <button type="button" class="btn btn-outline" style="margin-top: 14px;" onclick="addRow()">
                <i class="fa-solid fa-plus"></i> Add Item
            </button>

            <!-- Notes & Summary Footer -->
            <div style="display: flex; justify-content: space-between; margin-top: 28px; align-items: flex-start; gap: 40px;">
                <div class="form-group" style="flex: 1;">
                    <label>Terms / Notes</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Enter terms of delivery or specific requests..." style="resize: none;"><?= htmlspecialchars($data['po']->notes ?? '') ?></textarea>
                </div>
                
                <div>
                    <div class="total-box">
                        Grand Total: Rs: <span id="grandTotal">0.00</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 32px; text-align: right; border-top: 0.5px solid var(--c-separator); padding-top: 24px;">
                <button type="submit" class="btn" style="padding: 14px 28px; font-size: 15px;">
                    <i class="fa-solid fa-circle-check"></i> <?= $isEdit ? 'Update Purchase Order' : 'Generate Purchase Order' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var catalogOptions = `
        <option value="">Select Product...</option>
        <?php foreach($data['catalog_items'] as $item): ?>
            <?php if(!empty($item->variations)): ?>
                <optgroup label="<?= htmlspecialchars($item->name) ?>" data-vendor="<?= $item->vendor_id ?>">
                    <option value="<?= $item->id ?>|MIX|1" data-price="<?= floatval($item->cost_price ?? 0.00) ?>" data-name="<?= htmlspecialchars($item->name) ?> (MIX)" data-sku="<?= htmlspecialchars($item->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($item->sample_code ?? '') ?>">
                        <?= htmlspecialchars($item->name) ?> (MIX)
                    </option>
                    <?php foreach($item->variations as $var): ?>
                        <option value="<?= $item->id ?>|<?= $var->id ?>|0" data-price="<?= floatval($var->cost ?? 0) > 0 ? $var->cost : floatval($item->cost_price ?? 0.00) ?>" data-name="<?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>" data-sku="<?= htmlspecialchars($var->sku ?? $item->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($item->sample_code ?? '') ?>">
                            <?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php else: ?>
                <option class="base-opt" value="<?= $item->id ?>|0|0" data-price="<?= floatval($item->cost_price ?? 0.00) ?>" data-vendor="<?= $item->vendor_id ?>" data-name="<?= htmlspecialchars($item->name) ?>" data-sku="<?= htmlspecialchars($item->item_code ?? '') ?>" data-sample-code="<?= htmlspecialchars($item->sample_code ?? '') ?>">
                    <?= htmlspecialchars($item->name) ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    `;

    function filterProducts() {
        const vendorId = document.getElementById('vendorSelect').value;
        document.querySelectorAll('.item-select').forEach(select => {
            select.querySelectorAll('optgroup, option.base-opt').forEach(group => {
                if (group.getAttribute('data-vendor') === vendorId || vendorId === "" || !group.getAttribute('data-vendor')) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });
            // Reset if previously selected item is now hidden
            const selectedOpt = select.options[select.selectedIndex];
            if(selectedOpt && (selectedOpt.style.display === 'none' || (selectedOpt.parentElement && selectedOpt.parentElement.style.display === 'none'))) {
                select.value = "";
                const tr = select.closest('tr');
                tr.querySelector('input[name="price[]"]').value = "0.00";
                tr.querySelector('.desc-hidden').value = "";
                calcTotals();
            }
        });
    }

    function autoFillSelection(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const name = selectedOption.getAttribute('data-name') || '';
        
        const tr = selectElement.closest('tr');
        tr.querySelector('input[name="price[]"]').value = parseFloat(price).toFixed(2);
        tr.querySelector('.desc-hidden').value = name;
        calcTotals();
    }

    function renumberLineRows(tbodyId) {
        document.querySelectorAll(`#${tbodyId} tr`).forEach((tr, i) => {
            const cell = tr.querySelector('.line-row-num');
            if (cell) cell.textContent = i + 1;
        });
    }

    function addRow() {
        const tbody = document.getElementById('poBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="line-row-num"></td>
            <td>
                <select name="item_selection[]" class="item-select" onchange="autoFillSelection(this)" required>${catalogOptions}</select>
                <input type="hidden" name="desc[]" class="desc-hidden">
            </td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" class="form-control" onchange="calcTotals()" required style="text-align:right;"></td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" class="form-control" onchange="calcTotals()" required style="font-weight:600; text-align:right; font-family:var(--f-mono);"></td>
            <td><input type="text" class="form-control line-total" value="0.00" readonly style="font-weight:700; text-align:right; font-family:var(--f-mono); background:transparent; border:none; pointer-events:none;"></td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-danger" style="padding: 6px; width:28px; height:28px; border-radius:50%;" onclick="removeRow(this)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        filterProducts();
        renumberLineRows('poBody');
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        renumberLineRows('poBody');
        calcTotals();
    }

    function calcTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#poBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const total = qty * price;
            
            const totalInput = row.querySelector('.line-total');
            if(totalInput) { totalInput.value = total.toFixed(2); }
            grandTotal += total;
        });
        document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
    }

    document.addEventListener("DOMContentLoaded", () => {
        calcTotals();
        filterProducts();
        renumberLineRows('poBody');

        const form = document.getElementById('poForm');
        if (form) {
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const target = e.target;
                    if (target.tagName === 'INPUT' || target.tagName === 'SELECT') {
                        const row = target.closest('tr');
                        if (row && row.closest('#poBody')) {
                            e.preventDefault();
                            
                            const tbody = document.getElementById('poBody');
                            const lastTr = tbody.querySelector('tr:last-child');
                            if (row === lastTr) {
                                addRow();
                            }
                            
                            setTimeout(() => {
                                const newLastTr = tbody.querySelector('tr:last-child');
                                if (newLastTr) {
                                    const select = newLastTr.querySelector('.item-select');
                                    if (select) {
                                        if (select.tomselect) {
                                            select.tomselect.focus();
                                        } else {
                                            select.focus();
                                        }
                                    }
                                }
                            }, 50);
                        }
                    }
                }
            });
        }
    });
</script>