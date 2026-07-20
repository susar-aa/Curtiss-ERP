<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — CREATE/EDIT GRN
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
    --shadow-md:  0 8px 24 rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
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
}

/* ---- Badges & Pricing ---- */
.price-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: var(--r-xs);
    display: inline-block;
    font-family: var(--f-mono);
}
.price-retail {
    background: var(--c-blue-light);
    color: var(--c-blue);
    border: 0.5px solid var(--c-blue-mid);
}
.price-wholesale {
    background: var(--c-purple-light);
    color: var(--c-purple);
    border: 0.5px solid rgba(175,82,222,0.3);
}
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
    color: var(--c-green);
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

/* ---- Autocomplete wrapper ---- */
.autocomplete-suggestions-wrapper {
    position: absolute;
    top: calc(100% + 4px);
    left: 14px;
    right: 14px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    max-height: 220px;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
    z-index: 9999;
    display: none;
    padding: 4px;
}
.autocomplete-suggestion-item {
    padding: 8px 12px;
    font-size: 13px;
    cursor: pointer;
    border-radius: var(--r-sm);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background var(--dur-fast);
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
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 600; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }
</style>

<?php
$isEdit = isset($data['grn']);
$actionUrl = APP_URL . '/grn/' . ($isEdit ? "edit/{$data['grn']->id}" : "create");
?>

<div class="inv-wrap">
    <!-- Header -->
    <div class="inv-header">
        <div>
            <div class="inv-eyebrow">Supply Chain</div>
            <h1 class="inv-title"><?= $isEdit ? 'Edit Goods Receipt Note' : 'Create Goods Receipt Note' ?></h1>
        </div>
        <a href="<?= APP_URL ?>/grn" class="btn-quick">&larr; Back to GRNs</a>
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
        <form action="<?= $actionUrl ?>" method="POST" id="grnForm">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update_grn' : 'save_grn' ?>">
            
            <?php if($data['linked_po']): ?>
                <div class="sf-alert success" style="margin-bottom: 24px; border-left-color: var(--c-green);">
                    <i class="fa-solid fa-link sf-alert-icon" style="color: var(--c-green);"></i>
                    <div style="flex: 1;">
                        <div class="sf-alert-title" style="font-size: 15px;">Linked to Purchase Order: <?= htmlspecialchars($data['linked_po']->po_number) ?></div>
                        <div class="sf-alert-msg">Saving this GRN will automatically mark the PO as Received and update your physical inventory stock levels.</div>
                    </div>
                    <input type="hidden" name="po_id" value="<?= $data['linked_po']->id ?>">
                </div>
            <?php endif; ?>

            <div class="grid-4">
                <div class="form-group">
                    <label>Supplier / Vendor *</label>
                    <select name="vendor_id" id="vendorSelect" class="form-control" onchange="onVendorChange()" required <?= ($data['linked_po'] || $isEdit) ? 'style="pointer-events:none; background:rgba(0,0,0,0.04);"' : '' ?>>
                        <option value="">Select Vendor...</option>
                        <?php foreach($data['vendors'] as $ven): ?>
                            <option value="<?= $ven->id ?>" <?= $data['prefilled_vendor'] == $ven->id ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>GRN Number</label>
                    <input type="text" name="grn_number" class="form-control" value="<?= htmlspecialchars($isEdit ? $data['grn']->grn_number : $data['grn_number']) ?>" required <?= $isEdit ? 'readonly style="pointer-events:none; background:rgba(0,0,0,0.04);"' : '' ?>>
                </div>
                <div class="form-group">
                    <label>Receipt Date</label>
                    <input type="date" name="grn_date" class="form-control" value="<?= htmlspecialchars($isEdit ? $data['grn']->grn_date : date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Supplier Invoice / Receipt No.</label>
                    <input type="text" name="receipt_number" class="form-control" placeholder="Invoice number from supplier" value="<?= htmlspecialchars($isEdit ? ($data['grn']->receipt_number ?? '') : '') ?>">
                </div>
            </div>

            <!-- Items Table -->
            <table class="po-table" id="linesTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align:center;">#</th>
                        <th style="width: 25%;">Specific Product / Variation Received</th>
                        <th style="width: 9%; text-align:right;">Qty</th>
                        <th style="width: 12%; text-align:right;">Unit Cost (Rs)</th>
                        <th style="width: 12%; text-align:right;">Line Total</th>
                        <th style="width: 9%; text-align:right;">Retail %</th>
                        <th style="width: 9%; text-align:right;">Wholesale %</th>
                        <th style="width: 12%; text-align:right; color: var(--c-blue);">Retail Price</th>
                        <th style="width: 12%; text-align:right; color: var(--c-purple);">Wholesale B2B</th>
                        <th style="width: 40px; text-align:center;"></th>
                    </tr>
                </thead>
                <tbody id="poBody">
                    <?php if(!empty($data['prefilled_items'])): ?>
                        <?php $lineNum = 1; foreach($data['prefilled_items'] as $item): ?>
                            <?php 
                                $retailMargin = isset($item->retail_margin) ? floatval($item->retail_margin) : 0.0;
                                $wholesaleMargin = isset($item->wholesale_margin) ? floatval($item->wholesale_margin) : 0.0;
                                $displayName = $item->description;
                                if ($item->item_id) {
                                    foreach($data['catalog_items'] as $catItem) {
                                        if ($catItem->id == $item->item_id) {
                                            if (!isset($item->retail_margin)) {
                                                $retailMargin = floatval($catItem->retail_margin ?? 0);
                                            }
                                            if (!isset($item->wholesale_margin)) {
                                                $wholesaleMargin = floatval($catItem->wholesale_margin ?? 0);
                                            }
                                            $displayName = $catItem->name;
                                            if ($item->item_variation_option_id && !empty($catItem->variations)) {
                                                foreach($catItem->variations as $v) {
                                                    if ($v->id == $item->item_variation_option_id) {
                                                        $displayName = "{$catItem->name} - {$v->variation_name}: {$v->value_name}";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $unitCost = isset($item->unit_cost) ? $item->unit_cost : ($item->unit_price ?? 0);
                            ?>
                            <tr>
                                <td class="line-row-num"><?= $lineNum++ ?></td>
                                <td style="position: relative; overflow: visible;">
                                    <input type="text" class="form-control autocomplete-search-input" value="<?= htmlspecialchars($displayName) ?>" placeholder="Type product name/SKU..." autocomplete="off" required>
                                    <div class="autocomplete-suggestions-wrapper"></div>
                                    <input type="hidden" name="item_selection[]" class="item-selection-hidden" value="<?= $item->item_id ?>|<?= $item->item_variation_option_id ?: '0' ?>" required>
                                    <input type="hidden" name="desc[]" class="desc-hidden" value="<?= htmlspecialchars($displayName) ?>">
                                </td>
                                <td><input type="number" name="qty[]" step="1" min="1" value="<?= $item->quantity ?>" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
                                <td><input type="number" name="price[]" step="0.01" min="0" value="<?= number_format($unitCost, 2, '.', '') ?>" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="font-weight:600; text-align:right; font-family:var(--f-mono);"></td>
                                <td style="text-align: right; vertical-align: middle; font-family: var(--f-mono); font-weight:700;">
                                    <span class="line-total-display">0.00</span>
                                </td>
                                <td><input type="number" name="retail_margin[]" step="0.1" value="<?= number_format($retailMargin, 1, '.', '') ?>" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
                                <td><input type="number" name="wholesale_margin[]" step="0.1" value="<?= number_format($wholesaleMargin, 1, '.', '') ?>" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
                                <td style="text-align: right; vertical-align: middle;">
                                    <span class="price-badge price-retail display-retail">0.00</span>
                                    <input type="hidden" name="selling_price[]" value="0.00">
                                </td>
                                <td style="text-align: right; vertical-align: middle;">
                                    <span class="price-badge price-wholesale display-wholesale">0.00</span>
                                    <input type="hidden" name="wholesale_price[]" value="0.00">
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-danger" style="padding: 6px; width:28px; height:28px; border-radius:50%;" onclick="removeRow(this)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Blank Row -->
                        <tr>
                            <td class="line-row-num">1</td>
                            <td style="position: relative; overflow: visible;">
                                <input type="text" class="form-control autocomplete-search-input" placeholder="Type product name/SKU..." autocomplete="off" required>
                                <div class="autocomplete-suggestions-wrapper"></div>
                                <input type="hidden" name="item_selection[]" class="item-selection-hidden" required>
                                <input type="hidden" name="desc[]" class="desc-hidden">
                            </td>
                            <td><input type="number" name="qty[]" step="1" min="1" value="1" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
                            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="font-weight:600; text-align:right; font-family:var(--f-mono);"></td>
                            <td style="text-align: right; vertical-align: middle; font-family: var(--f-mono); font-weight:700;">
                                <span class="line-total-display">0.00</span>
                            </td>
                            <td><input type="number" name="retail_margin[]" step="0.1" value="0.0" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
                            <td><input type="number" name="wholesale_margin[]" step="0.1" value="0.0" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
                            <td style="text-align: right; vertical-align: middle;">
                                <span class="price-badge price-retail display-retail">0.00</span>
                                <input type="hidden" name="selling_price[]" value="0.00">
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <span class="price-badge price-wholesale display-wholesale">0.00</span>
                                <input type="hidden" name="wholesale_price[]" value="0.00">
                            </td>
                            <td style="text-align: center;"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <button type="button" class="btn btn-outline" style="margin-top: 14px;" onclick="addRow()">
                <i class="fa-solid fa-plus"></i> Add Received Item
            </button>

            <!-- Notes & Summary Footer -->
            <div style="display: flex; justify-content: space-between; margin-top: 28px; align-items: flex-start; gap: 40px;">
                <div class="form-group" style="flex: 1;">
                    <label>Inspection Notes / Damages</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Log any damages, discrepancies or receipt details..." style="resize: none;"><?= htmlspecialchars($isEdit ? ($data['grn']->notes ?? '') : '') ?></textarea>
                </div>
                
                <div>
                    <div class="total-box">
                        Grand Total: Rs: <span id="grandTotal">0.00</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 32px; text-align: right; border-top: 0.5px solid var(--c-separator); padding-top: 24px;">
                <button type="submit" class="btn" style="padding: 14px 28px; font-size: 15px;">
                    <i class="fa-solid fa-circle-check"></i> Save GRN & Update Inventory
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Injected catalog items with preloaded variations & supplier mappings
    var catalogItems = <?= json_encode($data['catalog_items']) ?>;
    var itemSupplierMappings = <?= json_encode($data['item_supplier_mappings'] ?? []) ?>;

    // Fast lookup dictionary for supplier-item relationships: "itemId_supplierId" -> { last_cost_price, is_primary }
    var itemSupplierMap = {};
    if (Array.isArray(itemSupplierMappings)) {
        itemSupplierMappings.forEach(mapping => {
            const key = `${mapping.item_id}_${mapping.supplier_id}`;
            itemSupplierMap[key] = {
                last_cost_price: parseFloat(mapping.last_cost_price ?? 0),
                is_primary: parseInt(mapping.is_primary ?? 0) === 1
            };
        });
    }
    
    // Generate flattened list of searchable elements (main items + variation options)
    var searchableItems = [];
    catalogItems.forEach(item => {
        if (item.variations && item.variations.length > 0) {
            item.variations.forEach(v => {
                searchableItems.push({
                    item_id: item.id,
                    var_opt_id: v.id,
                    display_name: `${item.name} - ${v.variation_name}: ${v.value_name}`,
                    sku: v.sku || item.item_code || '',
                    vendor_id: item.vendor_id,
                    cost: parseFloat(v.cost && parseFloat(v.cost) > 0 ? v.cost : (item.cost_price && parseFloat(item.cost_price) > 0 ? item.cost_price : (item.cost ?? 0))),
                    price: parseFloat(v.price ?? item.price ?? 0),
                    retail_margin: parseFloat(item.retail_margin ?? 0),
                    wholesale_margin: parseFloat(item.wholesale_margin ?? 0),
                    wholesale_price: parseFloat(item.wholesale_price ?? 0)
                });
            });
        } else {
            searchableItems.push({
                item_id: item.id,
                var_opt_id: 0,
                display_name: item.name,
                sku: item.item_code || '',
                vendor_id: item.vendor_id,
                cost: parseFloat(item.cost_price && parseFloat(item.cost_price) > 0 ? item.cost_price : (item.cost ?? 0)),
                price: parseFloat(item.price ?? 0),
                retail_margin: parseFloat(item.retail_margin ?? 0),
                wholesale_margin: parseFloat(item.wholesale_margin ?? 0),
                wholesale_price: parseFloat(item.wholesale_price ?? 0)
            });
        }
    });

    function onVendorChange() {
        const tbody = document.getElementById('poBody');
        tbody.innerHTML = ''; // Clear all current lines to prevent vendor mixing
        addRow(); // Insert a single clean row
    }

    function initAutocomplete(row) {
        const input = row.querySelector('.autocomplete-search-input');
        const wrapper = row.querySelector('.autocomplete-suggestions-wrapper');
        const hiddenSelection = row.querySelector('.item-selection-hidden');
        const hiddenDesc = row.querySelector('.desc-hidden');

        input.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const selectedVendorId = parseInt(document.getElementById('vendorSelect').value || 0);
            
            wrapper.innerHTML = '';
            
            if (!selectedVendorId) {
                wrapper.innerHTML = '<div style="padding: 10px 12px; font-size:12px; color:var(--c-red); font-weight:600; text-align:center;">⚠️ Select Supplier / Vendor first</div>';
                wrapper.style.display = 'block';
                return;
            }

            if (query.length < 1) {
                wrapper.style.display = 'none';
                return;
            }

            // Search ALL active catalog items
            let matches = searchableItems.filter(item => 
                item.display_name.toLowerCase().includes(query) || item.sku.toLowerCase().includes(query)
            );

            if (matches.length === 0) {
                wrapper.innerHTML = '<div style="padding: 10px 12px; font-size:12px; color:var(--t-tertiary); text-align:center; font-style:italic;">No products found</div>';
                wrapper.style.display = 'block';
                return;
            }

            // Annotate each match with supplier link status
            matches = matches.map(m => {
                const mapKey = `${m.item_id}_${selectedVendorId}`;
                const supplierInfo = itemSupplierMap[mapKey];
                const isDirectVendor = parseInt(m.vendor_id) === selectedVendorId;
                const isLinked = isDirectVendor || Boolean(supplierInfo);
                const isPreferred = isDirectVendor || (supplierInfo && supplierInfo.is_primary);

                let applicableCost = m.cost;
                if (supplierInfo && supplierInfo.last_cost_price > 0) {
                    applicableCost = supplierInfo.last_cost_price;
                }

                return {
                    ...m,
                    is_linked: isLinked,
                    is_preferred: isPreferred,
                    supplier_cost: applicableCost
                };
            });

            // Sort: Preferred / Linked products first, then non-linked products
            matches.sort((a, b) => {
                if (a.is_preferred && !b.is_preferred) return -1;
                if (!a.is_preferred && b.is_preferred) return 1;
                if (a.is_linked && !b.is_linked) return -1;
                if (!a.is_linked && b.is_linked) return 1;
                return a.display_name.localeCompare(b.display_name);
            });

            // Limit to top 15 results
            matches = matches.slice(0, 15);

            matches.forEach(m => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'autocomplete-suggestion-item';
                itemDiv.style.padding = '8px 12px';
                itemDiv.style.borderBottom = '0.5px solid var(--c-separator2)';

                let badgeHtml = '';
                if (m.is_preferred) {
                    badgeHtml = '<span style="font-size:10px; font-weight:700; background:var(--c-green-light); color:var(--c-green); padding:2px 6px; border-radius:4px; border:0.5px solid rgba(52,199,89,0.3);"><i class="fa-solid fa-star" style="font-size:9px;"></i> Preferred Supplier</span>';
                } else if (m.is_linked) {
                    badgeHtml = '<span style="font-size:10px; font-weight:700; background:var(--c-blue-light); color:var(--c-blue); padding:2px 6px; border-radius:4px; border:0.5px solid rgba(0,122,255,0.3);"><i class="fa-solid fa-check" style="font-size:9px;"></i> Linked Supplier</span>';
                } else {
                    badgeHtml = '<span style="font-size:10px; font-weight:700; background:var(--c-orange-light); color:var(--c-orange); padding:2px 6px; border-radius:4px; border:0.5px solid rgba(255,149,0,0.3);"><i class="fa-solid fa-plus-circle" style="font-size:9px;"></i> New Supplier Product</span>';
                }
                
                itemDiv.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:2px; flex:1;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                            <span style="font-weight: 600; color: var(--t-primary); font-size:13px;">${escapeHtml(m.display_name)}</span>
                            ${badgeHtml}
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; font-size: 11px; color:var(--t-label); font-family:var(--f-mono);">
                            <span>SKU: ${escapeHtml(m.sku || 'N/A')}</span>
                            <span>Cost: Rs. ${m.supplier_cost.toFixed(2)}</span>
                        </div>
                    </div>
                `;
                
                itemDiv.addEventListener('mouseover', () => {
                    itemDiv.style.background = 'var(--c-fill2)';
                });
                itemDiv.addEventListener('mouseout', () => {
                    itemDiv.style.background = '';
                });

                itemDiv.addEventListener('click', () => {
                    input.value = m.display_name;
                    hiddenSelection.value = `${m.item_id}|${m.var_opt_id}`;
                    hiddenDesc.value = m.display_name;
                    wrapper.style.display = 'none';

                    // Populate prices, margins and costs
                    row.querySelector('input[name="price[]"]').value = m.supplier_cost.toFixed(2);
                    row.querySelector('input[name="retail_margin[]"]').value = m.retail_margin.toFixed(1);
                    row.querySelector('input[name="wholesale_margin[]"]').value = m.wholesale_margin.toFixed(1);
                    
                    calculateRowPrices(row);
                });

                wrapper.appendChild(itemDiv);
            });

            wrapper.style.display = 'block';
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !wrapper.contains(e.target)) {
                wrapper.style.display = 'none';
            }
        });
    }

    function calculateRowPrices(row) {
        const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
        const cost = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
        const retailMargin = parseFloat(row.querySelector('input[name="retail_margin[]"]').value) || 0;
        const wholesaleMargin = parseFloat(row.querySelector('input[name="wholesale_margin[]"]').value) || 0;

        const lineTotal = qty * cost;
        const lineTotalSpan = row.querySelector('.line-total-display');
        if (lineTotalSpan) {
            lineTotalSpan.textContent = lineTotal.toFixed(2);
        }

        const calculatedRetail = cost + (cost * retailMargin / 100);
        const calculatedWholesale = cost + (cost * wholesaleMargin / 100);

        row.querySelector('.display-retail').textContent = calculatedRetail.toFixed(2);
        row.querySelector('.display-wholesale').textContent = calculatedWholesale.toFixed(2);
        
        // Populate inputs to submit
        row.querySelector('input[name="selling_price[]"]').value = calculatedRetail.toFixed(2);
        row.querySelector('input[name="wholesale_price[]"]').value = calculatedWholesale.toFixed(2);

        calcGrandTotal();
    }

    function calcGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('#poBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const cost = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            grandTotal += qty * cost;
        });
        const grandTotalSpan = document.getElementById('grandTotal');
        if (grandTotalSpan) {
            grandTotalSpan.textContent = grandTotal.toFixed(2);
        }
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
            <td style="position: relative; overflow: visible;">
                <input type="text" class="form-control autocomplete-search-input" placeholder="Type product name/SKU..." autocomplete="off" required>
                <div class="autocomplete-suggestions-wrapper"></div>
                <input type="hidden" name="item_selection[]" class="item-selection-hidden" required>
                <input type="hidden" name="desc[]" class="desc-hidden">
            </td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="font-weight:600; text-align:right; font-family:var(--f-mono);"></td>
            <td style="text-align: right; vertical-align: middle; font-family: var(--f-mono); font-weight:700;">
                <span class="line-total-display">0.00</span>
            </td>
            <td><input type="number" name="retail_margin[]" step="0.1" value="0.0" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
            <td><input type="number" name="wholesale_margin[]" step="0.1" value="0.0" class="form-control" oninput="calculateRowPrices(this.closest('tr'))" required style="text-align:right;"></td>
            <td style="text-align: right; vertical-align: middle;">
                <span class="price-badge price-retail display-retail">0.00</span>
                <input type="hidden" name="selling_price[]" value="0.00">
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <span class="price-badge price-wholesale display-wholesale">0.00</span>
                <input type="hidden" name="wholesale_price[]" value="0.00">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-danger" style="padding: 6px; width:28px; height:28px; border-radius:50%;" onclick="removeRow(this)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        initAutocomplete(tr);
        renumberLineRows('poBody');
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        renumberLineRows('poBody');
        calcGrandTotal();
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.addEventListener("DOMContentLoaded", () => {
        // Initialize autocomplete on any prefilled rows
        document.querySelectorAll('#poBody tr').forEach(row => {
            initAutocomplete(row);
            calculateRowPrices(row);
        });
        renumberLineRows('poBody');

        const form = document.getElementById('grnForm');
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
                                    const newInput = newLastTr.querySelector('.autocomplete-search-input');
                                    if (newInput) newInput.focus();
                                }
                            }, 50);
                        }
                    }
                }
            });
        }
    });
</script>