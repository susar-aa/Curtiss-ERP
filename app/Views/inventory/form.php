<?php
$isEdit = isset($data['item']);
$actionUrl = $isEdit ? APP_URL . '/inventory/edit/' . $data['item']->id : APP_URL . '/inventory/create';
$item = $isEdit ? $data['item'] : null;
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--mac-border); padding-bottom: 15px;}
    .btn { padding: 10px 20px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-danger { background: #ffebee; color: #c62828; border: none; }
    .btn-small { padding: 4px 8px; font-size: 11px; cursor: pointer; border-radius: 4px;}
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    .grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; }
    
    /* Variation specific styles */
    .var-box { background: rgba(0,0,0,0.02); padding: 15px; border-radius: 8px; margin-top: 10px; border: 1px solid var(--mac-border); position: relative;}
    .var-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--mac-border); padding-bottom: 10px; margin-bottom: 10px;}
    .var-header strong { color: #0066cc; font-size: 14px;}
    .var-item-row { display: flex; align-items: center; gap: 15px; margin-bottom: 8px; padding: 10px; border-radius: 4px; background: #fff; border: 1px solid var(--mac-border);}
    @media (prefers-color-scheme: dark) { .var-item-row { background: #1e1e2d; } }

    /* Image Gallery Styles */
    .img-upload-box { background: rgba(0,102,204,0.02); border: 2px dashed #0066cc; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 20px;}
    .gallery { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; }
    .gallery-img-wrapper { position: relative; width: 100px; height: 100px; border-radius: 8px; border: 1px solid var(--mac-border); overflow: hidden; background: #fff;}
    .gallery-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-img-wrapper .del-btn { position: absolute; top: 2px; right: 2px; background: #c62828; color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;}
    
    .var-img-preview { width: 36px; height: 36px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; background:#eee;}
</style>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="header-actions">
        <h2 style="margin:0;"><?= $data['title'] ?></h2>
        <a href="<?= APP_URL ?>/inventory" class="btn btn-outline">&larr; Back to Inventory</a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>

    <!-- NEW: enctype allows robust multiple file uploading -->
    <form action="<?= $actionUrl ?>" method="POST" id="itemForm" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Product / Service Name *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item->name ?? '') ?>" required>
        </div>
        
        <!-- NEW: Image Upload Engine -->
        <div class="img-upload-box">
            <label style="font-weight: bold; color: #0066cc; margin-bottom: 10px; display:block;">Product Image Gallery (PNG, JPG)</label>
            <input type="file" name="product_images[]" class="form-control" accept=".png, .jpg, .jpeg" multiple style="max-width: 400px; margin: auto; background: #fff;">
            <p style="font-size: 11px; color: #666;">Upload multiple images. They will be automatically resized and optimized for fast loading.</p>
            
            <?php if($isEdit && !empty($item->general_images)): ?>
                <div class="gallery" id="existingGallery">
                    <?php foreach($item->general_images as $img): ?>
                        <div class="gallery-img-wrapper" id="img_wrap_<?= $img->id ?>">
                            <img src="<?= APP_URL ?>/uploads/products/<?= htmlspecialchars($img->image_path) ?>" alt="Product">
                            <button type="button" class="del-btn" onclick="markImageForDeletion(<?= $img->id ?>)">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- Hidden inputs for deleted images will be injected here by JS -->
            <div id="deletedImagesContainer"></div>
        </div>

        <div class="grid-4">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">-- Uncategorized --</option>
                    <?php foreach($data['categories'] as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= ($item && $item->category_id == $cat->id) ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Preferred Supplier (Vendor)</label>
                <select name="vendor_id" class="form-control">
                    <option value="">-- No Supplier Linked --</option>
                    <?php foreach($data['vendors'] as $ven): ?>
                        <option value="<?= $ven->id ?>" <?= ($item && $item->vendor_id == $ven->id) ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Warehouse (Location)</label>
                <select name="warehouse_id" class="form-control">
                    <option value="">-- No Location --</option>
                    <?php foreach($data['warehouses'] as $wh): ?>
                        <option value="<?= $wh->id ?>" <?= ($item && $item->warehouse_id == $wh->id) ? 'selected' : ($wh->is_default && !$isEdit ? 'selected' : '') ?>><?= htmlspecialchars($wh->name) ?><?= $wh->is_default ? ' (Default)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Primary SKU / Item Code</label>
                <input type="text" name="item_code" class="form-control" value="<?= htmlspecialchars($item->item_code ?? '') ?>">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Item Type</label>
                <select name="type" id="f_type" class="form-control" onchange="toggleInventory(this.value)">
                    <option value="Inventory" <?= ($item && $item->type == 'Inventory') ? 'selected' : '' ?>>Inventory (Track Stock via GRN)</option>
                    <option value="Service" <?= ($item && $item->type == 'Service') ? 'selected' : '' ?>>Service (No tracking)</option>
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:10px;">
                <label style="cursor:pointer; color:#6a1b9a; display:flex; align-items:center; gap:8px; font-weight:bold;">
                    <input type="checkbox" name="is_variable_pricing" id="f_variable" value="1" onchange="toggleVariablePricingUI()" style="width:16px; height:16px;" <?= ($item && $item->is_variable_pricing) ? 'checked' : '' ?>> 
                    Enable Variable Pricing
                </label>
            </div>
        </div>

        <!-- Standard Pricing -->
        <div class="grid-2" id="mainPricingDiv">
            <div class="form-group">
                <label>Standard Sales Price (Rs:)</label>
                <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($item->price ?? '0.00') ?>">
            </div>
            <div class="form-group">
                <label>Standard Cost (Rs:)</label>
                <input type="number" name="cost" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($item->cost ?? '0.00') ?>">
            </div>
        </div>

        <div id="qtyDiv" style="background: rgba(198, 40, 40, 0.05); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(198, 40, 40, 0.2);">
            <div class="form-group" style="margin-bottom:0;">
                <label style="color:#c62828;">Minimum Stock Alert Level</label>
                <input type="number" name="min_stock" step="1" min="0" class="form-control" value="<?= htmlspecialchars($item->minimum_stock_level ?? 10) ?>">
                <p style="font-size: 11px; color:#666; margin: 5px 0 0 0;">You will be alerted when stock falls to this amount. Default is 10. Actual Stock levels will update automatically when you process Purchase Orders / GRNs.</p>
            </div>
        </div>

        <!-- Dynamic Checkbox Variations Section -->
        <div style="margin-top: 25px; border-top: 1px solid var(--mac-border); padding-top: 25px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <h3 style="color:#0066cc; margin:0;">Product Variations & Specific Images</h3>
                    <p style="font-size:12px; color:#666; margin: 0;">Assign different SKUs and Images to each variation color, size, etc.</p>
                </div>
                <button type="button" class="btn btn-small btn-outline" onclick="addVariationSelector()">+ Add Attribute Group</button>
            </div>
            <div id="variationsContainer"></div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 30px; border-top: 1px solid var(--mac-border); padding-top: 20px;">
            <a href="<?= APP_URL ?>/inventory" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn" style="font-size: 16px; padding: 12px 24px;"><?= $isEdit ? 'Update Product Details' : 'Save New Product' ?></button>
        </div>
    </form>
</div>

<script>
    const variationsTree = <?= json_encode($data['variations_tree'] ?? []) ?>;
    const existingVariations = <?= json_encode($item->variations ?? []) ?>;
    const existingVarImages = <?= json_encode($item->var_images ?? (object)[]) ?>;
    const appUrl = '<?= APP_URL ?>';

    document.addEventListener('DOMContentLoaded', () => {
        toggleVariablePricingUI();
        toggleInventory(document.getElementById('f_type').value);
        
        if (existingVariations && existingVariations.length > 0) {
            const groupedVars = {};
            existingVariations.forEach(v => {
                if(!groupedVars[v.variation_id]) groupedVars[v.variation_id] = [];
                groupedVars[v.variation_id].push(v);
            });
            
            for (const [varId, valuesArray] of Object.entries(groupedVars)) {
                const rowId = 'var_group_' + varId + '_' + Date.now();
                document.getElementById('variationsContainer').insertAdjacentHTML('beforeend', `<div id="${rowId}"></div>`);
                renderVariationCheckboxes(varId, rowId, valuesArray);
            }
        }
    });

    function markImageForDeletion(imageId) {
        document.getElementById('img_wrap_' + imageId).style.display = 'none';
        const container = document.getElementById('deletedImagesContainer');
        container.insertAdjacentHTML('beforeend', `<input type="hidden" name="deleted_images[]" value="${imageId}">`);
    }

    function addVariationSelector() {
        const container = document.getElementById('variationsContainer');
        const rowId = 'var_group_' + Date.now();
        
        let html = `<div id="${rowId}" style="margin-bottom: 15px; display:flex; gap:10px;">
                        <select class="form-control" onchange="renderVariationCheckboxes(this.value, '${rowId}')">
                            <option value="">Select Variation Group...</option>`;
        variationsTree.forEach(v => { html += `<option value="${v.id}">${v.name}</option>`; });
        html += `       </select>
                        <button type="button" class="btn btn-danger" style="padding: 4px 12px;" onclick="document.getElementById('${rowId}').remove()">X</button>
                    </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function renderVariationCheckboxes(varId, containerId, existingValues = []) {
        if(!varId) return;
        const variation = variationsTree.find(v => v.id == varId);
        const container = document.getElementById(containerId);
        let isVarPricing = document.getElementById('f_variable').checked;

        let html = `
            <div class="var-box">
                <button type="button" class="btn btn-danger btn-small" style="position:absolute; top:10px; right:10px;" onclick="document.getElementById('${containerId}').remove()">Remove Group</button>
                <div class="var-header">
                    <strong>${variation.name}</strong>
                    <label style="cursor:pointer; font-size:12px; font-weight:normal;"><input type="checkbox" onchange="toggleSelectAll(this, '${varId}')"> Select All Values</label>
                </div>
                <div style="display:flex; flex-direction:column;">
        `;

        variation.values.forEach(val => {
            let exist = existingValues.find(ev => ev.variation_value_id == val.id);
            let checked = exist ? 'checked' : '';
            let pPrice = exist && exist.price ? parseFloat(exist.price).toFixed(2) : '0.00';
            let pCost = exist && exist.cost ? parseFloat(exist.cost).toFixed(2) : '0.00';
            let pSku = exist && exist.sku ? exist.sku : '';
            
            // Render specific variation image if it exists
            let imgHtml = '';
            if (existingVarImages[val.id]) {
                imgHtml = `
                    <div style="position:relative; margin-right: 10px;" id="var_img_wrap_${existingVarImages[val.id].id}">
                        <img src="${appUrl}/uploads/products/${existingVarImages[val.id].image_path}" class="var-img-preview" alt="Var Img">
                        <button type="button" style="position:absolute; top:-5px; right:-5px; background:#c62828; color:#fff; border:none; border-radius:50%; width:16px; height:16px; font-size:9px; cursor:pointer; padding:0;" onclick="markImageForDeletion(${existingVarImages[val.id].id})">X</button>
                    </div>
                `;
            }

            html += `
                <div class="var-item-row">
                    <input type="hidden" name="var_ids[${val.id}]" value="${varId}">
                    
                    <label style="width: 150px; cursor:pointer; font-weight:bold; font-size:14px; margin:0; color:#333; display:flex; align-items:center;">
                        <input type="checkbox" name="var_val_ids[]" value="${val.id}" class="cb-group-${varId}" ${checked} onchange="toggleRowPricing(this, '${val.id}')" style="margin-right:8px;"> 
                        ${val.value_name}
                    </label>
                    
                    <div style="display: flex; gap: 10px; flex: 1; align-items:center;">
                        <!-- NEW: Specific Image Upload -->
                        <div id="img_upload_${val.id}" style="display: ${checked ? 'flex' : 'none'}; align-items:center; flex: 1;">
                            ${imgHtml}
                            <input type="file" name="var_image[${val.id}]" class="form-control" accept=".png, .jpg, .jpeg" style="padding:4px; font-size:11px; background:#fff;">
                        </div>

                        <!-- SKU is ALWAYS visible if the variation is checked -->
                        <input type="text" name="var_sku[${val.id}]" class="form-control" style="padding:6px; font-size:13px; width: 140px; display: ${checked ? 'block' : 'none'};" id="sku_input_${val.id}" placeholder="Barcode / SKU" value="${pSku}">
                        
                        <!-- Pricing only shows if Variable Pricing is enabled globally -->
                        <div id="pricing_row_${val.id}" style="display: ${isVarPricing && exist ? 'flex' : 'none'}; gap: 10px; flex: 1.5;">
                            <input type="number" name="var_price[${val.id}]" step="0.01" class="form-control" style="padding:6px; font-size:13px;" placeholder="Override Price" value="${pPrice}">
                            <input type="number" name="var_cost[${val.id}]" step="0.01" class="form-control" style="padding:6px; font-size:13px;" placeholder="Override Cost" value="${pCost}">
                        </div>
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
        container.innerHTML = html;
    }

    function toggleSelectAll(masterCb, varId) {
        let checkboxes = document.querySelectorAll(`.cb-group-${varId}`);
        checkboxes.forEach(cb => {
            cb.checked = masterCb.checked;
            toggleRowPricing(cb, cb.value);
        });
    }

    function toggleVariablePricingUI() {
        let isVar = document.getElementById('f_variable').checked;
        document.getElementById('mainPricingDiv').style.display = isVar ? 'none' : 'grid';
        
        document.querySelectorAll('input[name="var_val_ids[]"]').forEach(cb => {
            toggleRowPricing(cb, cb.value);
        });
    }

    function toggleRowPricing(cb, valId) {
        let isVar = document.getElementById('f_variable').checked;
        let pricingDiv = document.getElementById(`pricing_row_${valId}`);
        let skuInput = document.getElementById(`sku_input_${valId}`);
        let imgUpload = document.getElementById(`img_upload_${valId}`);
        
        if(skuInput) skuInput.style.display = cb.checked ? 'block' : 'none';
        if(imgUpload) imgUpload.style.display = cb.checked ? 'flex' : 'none';
        if(pricingDiv) pricingDiv.style.display = (isVar && cb.checked) ? 'flex' : 'none';
    }

    function toggleInventory(type) {
        document.getElementById('qtyDiv').style.display = (type === 'Service') ? 'none' : 'block';
    }
</script>