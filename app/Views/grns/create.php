<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #2e7d32; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    .grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; }
    .po-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .po-table th, .po-table td { padding: 10px; border-bottom: 1px solid var(--mac-border); vertical-align: top;}
    .po-table th { background-color: rgba(0,0,0,0.02); text-align: left; }
    .po-table input, .po-table select { width: 100%; border: 1px solid transparent; background: transparent; color: var(--text-main); padding: 5px; box-sizing: border-box; }
    .po-table input:focus, .po-table select:focus { border: 1px solid #0066cc; outline: none; border-radius: 4px;}
    
    optgroup { font-weight: bold; color: #2e7d32; background: rgba(46,125,50,0.05); }
    option { font-weight: normal; color: #333; background: #fff;}
</style>

<div class="card">
    <div class="header-actions">
        <h2>Create Goods Receipt Note (GRN)</h2>
        <a href="<?= APP_URL ?>/grn" style="color: #666; text-decoration:none;">&larr; Back to GRNs</a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/grn/create" method="POST" id="grnForm">
        <input type="hidden" name="action" value="save_grn">
        
        <?php if($data['linked_po']): ?>
            <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; margin-bottom:20px; border: 1px solid rgba(46,125,50,0.2);">
                <strong style="font-size: 16px;">🔗 Linked to Purchase Order: <?= htmlspecialchars($data['linked_po']->po_number) ?></strong>
                <p style="margin:5px 0 0 0; font-size:13px;">Saving this GRN will automatically mark the PO as Received and update your physical inventory stock.</p>
                <input type="hidden" name="po_id" value="<?= $data['linked_po']->id ?>">
            </div>
        <?php endif; ?>

        <div class="grid-4">
            <div class="form-group">
                <label>Supplier / Vendor *</label>
                <select name="vendor_id" id="vendorSelect" class="form-control" onchange="filterProducts()" required <?= $data['linked_po'] ? 'style="pointer-events:none; background:rgba(0,0,0,0.02);"' : '' ?>>
                    <option value="">Select Vendor...</option>
                    <?php foreach($data['vendors'] as $ven): ?>
                        <option value="<?= $ven->id ?>" <?= $data['prefilled_vendor'] == $ven->id ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>GRN Number</label>
                <input type="text" name="grn_number" class="form-control" value="<?= htmlspecialchars($data['grn_number']) ?>" required>
            </div>
            <div class="form-group">
                <label>Receipt Date</label>
                <input type="date" name="grn_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <table class="po-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width: 40%;">Specific Product / Variation Received</th>
                    <th style="width: 15%; text-align:right;">Qty Received</th>
                    <th style="width: 15%; text-align:right;">Unit Cost (Rs:)</th>
                    <th style="width: 25%; text-align:right;">New Selling Price (Rs:)</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="poBody">
                <?php if(!empty($data['prefilled_items'])): ?>
                    <?php foreach($data['prefilled_items'] as $item): ?>
                        <?php 
                            // If the PO had a MIX, force the user to pick explicitly by leaving the value blank!
                            $selectedValue = $item->is_mix ? "" : "{$item->item_id}|{$item->item_variation_option_id}";
                            
                            // Find the current Selling Price from the Catalog Data
                            $sellPrice = 0.00;
                            if ($item->item_id) {
                                foreach($data['catalog_items'] as $catItem) {
                                    if ($catItem->id == $item->item_id) {
                                        $sellPrice = $catItem->price; // Base Price
                                        if ($item->item_variation_option_id && !empty($catItem->variations)) {
                                            foreach($catItem->variations as $v) {
                                                if ($v->id == $item->item_variation_option_id) {
                                                    $sellPrice = $v->price ?? $catItem->price; // Specific Variation Price
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <select name="item_selection[]" class="item-select" onchange="autoFillSelection(this)" required>
                                    <option value="">Select Specific Variation...</option>
                                    <?php foreach($data['catalog_items'] as $catItem): ?>
                                        <?php if(!empty($catItem->variations)): ?>
                                            <optgroup label="<?= htmlspecialchars($catItem->name) ?>" data-vendor="<?= $catItem->vendor_id ?>">
                                                <?php foreach($catItem->variations as $var): ?>
                                                    <option value="<?= $catItem->id ?>|<?= $var->id ?>" data-price="<?= $var->cost ?? $catItem->cost ?>" data-selling-price="<?= $var->price ?? $catItem->price ?>" data-name="<?= htmlspecialchars($catItem->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>" <?= $selectedValue === "{$catItem->id}|{$var->id}" ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($catItem->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php else: ?>
                                            <option class="base-opt" value="<?= $catItem->id ?>|0" data-price="<?= $catItem->cost ?>" data-selling-price="<?= $catItem->price ?>" data-vendor="<?= $catItem->vendor_id ?>" data-name="<?= htmlspecialchars($catItem->name) ?>" <?= $selectedValue === "{$catItem->id}|" ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($catItem->name) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="desc[]" class="desc-hidden" value="<?= htmlspecialchars($item->description) ?>">
                                <?php if($item->is_mix): ?>
                                    <div style="font-size:11px; color:#e65100; margin-top:4px;">⚠️ Please resolve MIX: Select the exact variant received. Add more rows if multiple variants arrived.</div>
                                <?php endif; ?>
                            </td>
                            <td><input type="number" name="qty[]" step="1" min="1" value="<?= $item->quantity ?>" required></td>
                            <td><input type="number" name="price[]" step="0.01" min="0" value="<?= number_format($item->unit_price, 2, '.', '') ?>" required></td>
                            <td>
                                <input type="number" name="selling_price[]" step="0.01" min="0" value="<?= number_format($sellPrice, 2, '.', '') ?>" required>
                                <div style="font-size:10px; color:#888; text-align:right;">Updates product catalog</div>
                            </td>
                            <td><button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size:10px; background:#c62828;" onclick="removeRow(this)">X</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Blank Row -->
                    <tr>
                        <td>
                            <select name="item_selection[]" class="item-select" onchange="autoFillSelection(this)" required>
                                <option value="">Select Specific Variation...</option>
                                <?php foreach($data['catalog_items'] as $item): ?>
                                    <?php if(!empty($item->variations)): ?>
                                        <optgroup label="<?= htmlspecialchars($item->name) ?>" data-vendor="<?= $item->vendor_id ?>">
                                            <?php foreach($item->variations as $var): ?>
                                                <option value="<?= $item->id ?>|<?= $var->id ?>" data-price="<?= $var->cost ?? $item->cost ?>" data-selling-price="<?= $var->price ?? $item->price ?>" data-name="<?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>">
                                                    <?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php else: ?>
                                        <option class="base-opt" value="<?= $item->id ?>|0" data-price="<?= $item->cost ?>" data-selling-price="<?= $item->price ?>" data-vendor="<?= $item->vendor_id ?>" data-name="<?= htmlspecialchars($item->name) ?>">
                                            <?= htmlspecialchars($item->name) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="desc[]" class="desc-hidden">
                        </td>
                        <td><input type="number" name="qty[]" step="1" min="1" value="1" required></td>
                        <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" required></td>
                        <td>
                            <input type="number" name="selling_price[]" step="0.01" min="0" value="0.00" required>
                            <div style="font-size:10px; color:#888; text-align:right;">Updates product catalog</div>
                        </td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <button type="button" class="btn btn-outline" style="margin-top: 10px; font-size:12px; background:transparent; border:1px solid #2e7d32; color:#2e7d32;" onclick="addRow()">+ Add Received Item</button>

        <div class="form-group" style="margin-top: 20px;">
            <label>Inspection Notes / Damages</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Log any damages or notes regarding this delivery..."></textarea>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" class="btn" style="padding: 12px 24px; font-size: 16px;">Save GRN & Update Inventory</button>
        </div>
    </form>
</div>

<script>
    const catalogOptions = `
        <option value="">Select Specific Variation...</option>
        <?php foreach($data['catalog_items'] as $item): ?>
            <?php if(!empty($item->variations)): ?>
                <optgroup label="<?= htmlspecialchars($item->name) ?>" data-vendor="<?= $item->vendor_id ?>">
                    <?php foreach($item->variations as $var): ?>
                        <option value="<?= $item->id ?>|<?= $var->id ?>" data-price="<?= $var->cost ?? $item->cost ?>" data-selling-price="<?= $var->price ?? $item->price ?>" data-name="<?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>">
                            <?= htmlspecialchars($item->name) ?> - <?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php else: ?>
                <option class="base-opt" value="<?= $item->id ?>|0" data-price="<?= $item->cost ?>" data-selling-price="<?= $item->price ?>" data-vendor="<?= $item->vendor_id ?>" data-name="<?= htmlspecialchars($item->name) ?>">
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
        });
    }

    function autoFillSelection(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const sellPrice = selectedOption.getAttribute('data-selling-price') || 0;
        const name = selectedOption.getAttribute('data-name') || '';
        
        const tr = selectElement.closest('tr');
        tr.querySelector('input[name="price[]"]').value = parseFloat(price).toFixed(2);
        
        const sellInput = tr.querySelector('input[name="selling_price[]"]');
        if(sellInput) {
            sellInput.value = parseFloat(sellPrice).toFixed(2);
        }
        
        tr.querySelector('.desc-hidden').value = name;
    }

    function addRow() {
        const tbody = document.getElementById('poBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="item_selection[]" class="item-select" onchange="autoFillSelection(this)" required>${catalogOptions}</select>
                <input type="hidden" name="desc[]" class="desc-hidden">
            </td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" required></td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" required></td>
            <td>
                <input type="number" name="selling_price[]" step="0.01" min="0" value="0.00" required>
                <div style="font-size:10px; color:#888; text-align:right;">Updates product catalog</div>
            </td>
            <td><button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size:10px; background:#c62828; color:#fff;" onclick="removeRow(this)">X</button></td>
        `;
        tbody.appendChild(tr);
        filterProducts();
    }

    function removeRow(btn) { btn.closest('tr').remove(); }
    document.addEventListener("DOMContentLoaded", filterProducts);
</script>