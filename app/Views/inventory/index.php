<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-danger { background: #ffebee; color: #c62828; border: none; }
    .btn-small { padding: 4px 8px; font-size: 11px; margin-right: 5px; cursor: pointer; border-radius: 4px; text-decoration:none;}
    
    .quick-links { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; background: rgba(0,0,0,0.02); padding: 10px 15px; border-radius: 8px; border: 1px solid var(--mac-border); }
    .btn-quick { padding: 6px 12px; background: #fff; color: #555; border: 1px solid var(--mac-border); border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .btn-quick:hover { background: rgba(0,102,204,0.05); color: #0066cc; border-color: #0066cc; }
    .btn-quick.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
    .kpi-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--mac-border); border-left: 4px solid #0066cc; }
    @media (prefers-color-scheme: dark) { .kpi-card { background: #1e1e2d; } }
    .kpi-title { font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
    .kpi-val { font-size: 24px; font-weight: bold; color: var(--text-main); }
    
    /* New Filter Panel Styles */
    .filter-panel { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid var(--mac-border); margin-bottom: 15px; display: flex; gap: 15px; align-items: flex-end; box-shadow: 0 1px 3px rgba(0,0,0,0.02); flex-wrap: wrap;}
    @media (prefers-color-scheme: dark) { .filter-panel { background: #1e1e2d; } }
    .search-bar { width: 100%; padding: 10px 15px; border: 1px solid var(--mac-border); border-radius: 8px; font-size: 14px; margin-bottom: 15px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);}
    @media (prefers-color-scheme: dark) { .data-table { background: #1e1e2d; } }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; color:#555;}
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .badge-inventory { background: #e3f2fd; color: #1565c0; }
    .badge-service { background: #f3e5f5; color: #6a1b9a; }
    .badge-category { background: #f5f5f5; color: #555; border: 1px solid #ddd; }
    
    .pagination { display: flex; justify-content: flex-end; gap: 5px; margin-top: 15px; }
    .page-btn { padding: 6px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: #fff; color: #333; text-decoration: none; font-size: 12px;}
    .page-btn.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); padding: 25px; border-radius: 12px; width: 450px; border: 1px solid var(--mac-border);}
    .form-group { margin:0; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 11px; font-weight: 600; color: #888; text-transform: uppercase; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="header-actions">
    <h2>Product & Inventory Management</h2>
    <a href="<?= APP_URL ?>/inventory/create" class="btn">+ Add New Product</a>
</div>

<!-- Quick Navigation Links -->
<div class="quick-links">
    <span style="font-size: 11px; color: #888; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Related:</span>
    <a href="<?= APP_URL ?>/inventory" class="btn-quick active">📦 Products</a>
    <a href="<?= APP_URL ?>/category" class="btn-quick">📁 Categories</a>
    <a href="<?= APP_URL ?>/variation" class="btn-quick">✨ Variations</a>
    <a href="<?= APP_URL ?>/warehouse" class="btn-quick">🏭 Warehouses</a>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-title">Total Products in Catalog</div>
        <div class="kpi-val"><?= $data['kpis']->total_products ?? 0 ?></div>
    </div>
    <div class="kpi-card" style="border-left-color: #2e7d32;">
        <div class="kpi-title">Total Inventory Valuation</div>
        <div class="kpi-val">Rs: <?= number_format($data['kpis']->total_value ?? 0, 2) ?></div>
    </div>
    <div class="kpi-card" style="border-left-color: #c62828;">
        <div class="kpi-title">Items Low on Stock</div>
        <div class="kpi-val" style="color: <?= ($data['kpis']->low_stock_count > 0) ? '#c62828' : 'inherit' ?>;"><?= $data['kpis']->low_stock_count ?? 0 ?> Items</div>
    </div>
</div>

<?php if(!empty($data['error'])): ?><div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div><?php endif; ?>
<?php if(!empty($data['success'])): ?><div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div><?php endif; ?>

<input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search by Product Name, SKU, or Category..." value="<?= htmlspecialchars($data['search']) ?>">

<!-- Advanced Filtering Panel -->
<div class="filter-panel">
    <div class="form-group" style="flex: 1; min-width: 150px;">
        <label>Filter by Category</label>
        <select id="filterCategory" class="form-control" onchange="triggerSearch()">
            <option value="">All Categories</option>
            <?php foreach($data['categories'] as $cat): ?>
                <option value="<?= $cat->id ?>" <?= ($data['filters']['category_id'] == $cat->id) ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="flex: 1; min-width: 150px;">
        <label>Filter by Supplier</label>
        <select id="filterVendor" class="form-control" onchange="triggerSearch()">
            <option value="">All Suppliers</option>
            <?php foreach($data['vendors'] as $ven): ?>
                <option value="<?= $ven->id ?>" <?= ($data['filters']['vendor_id'] == $ven->id) ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="flex: 1; min-width: 150px;">
        <label>Filter by Warehouse</label>
        <select id="filterWarehouse" class="form-control" onchange="triggerSearch()">
            <option value="">All Locations</option>
            <?php foreach($data['warehouses'] as $wh): ?>
                <option value="<?= $wh->id ?>" <?= ($data['filters']['warehouse_id'] == $wh->id) ? 'selected' : '' ?>><?= htmlspecialchars($wh->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="width: 120px;">
        <label>Min Price (Rs:)</label>
        <input type="number" id="filterMinPrice" class="form-control" placeholder="0.00" value="<?= htmlspecialchars($data['filters']['min_price']) ?>" onkeyup="triggerSearchDelay()">
    </div>
    <div class="form-group" style="width: 120px;">
        <label>Max Price (Rs:)</label>
        <input type="number" id="filterMaxPrice" class="form-control" placeholder="No Limit" value="<?= htmlspecialchars($data['filters']['max_price']) ?>" onkeyup="triggerSearchDelay()">
    </div>
    <button class="btn btn-outline" style="padding: 8px 12px; border-color:#ccc; color:#666;" onclick="clearFilters()">Clear</button>
</div>

<div id="tableContainer">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50px;">Img</th>
                <th>Item Name & SKU</th>
                <th>Category, Supplier & Warehouse</th>
                <th>Type</th>
                <th style="text-align: right;">Price / Cost</th>
                <th style="text-align: center;">Stock</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($data['items'])): ?>
            <tr><td colspan="7" style="text-align: center; color: #888; padding: 20px;">No products found matching your criteria.</td></tr>
            <?php else: foreach($data['items'] as $item): ?>
            <tr>
                <td>
                    <?php if($item->primary_image): ?>
                        <img src="<?= APP_URL ?>/uploads/products/<?= htmlspecialchars($item->primary_image) ?>" style="width:40px; height:40px; border-radius:4px; object-fit:cover; border:1px solid #ccc; background:#fff;">
                    <?php else: ?>
                        <div style="width:40px; height:40px; border-radius:4px; background:rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:center; color:#aaa; font-size:10px; border:1px dashed #ccc;">No Img</div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($item->name) ?></strong><br>
                    <span style="font-size: 11px; color: #888;"><?= htmlspecialchars($item->item_code) ?></span>
                </td>
                <td>
                    <?php if($item->category_name): ?>
                        <span class="badge badge-category"><?= htmlspecialchars($item->category_name) ?></span>
                    <?php else: ?>
                        <span style="color:#aaa; font-size:11px;">Uncategorized</span>
                    <?php endif; ?>
                    <br>
                    <span style="font-size: 11px; color:#0066cc; font-weight:bold;"><?= htmlspecialchars($item->vendor_name ?? 'No Supplier') ?></span>
                    <br>
                    <span style="font-size: 11px; color:#e65100; font-weight:bold;"><?= htmlspecialchars($item->warehouse_name ?? 'No Warehouse') ?></span>
                </td>
                <td><span class="badge badge-<?= strtolower($item->type) ?>"><?= $item->type ?></span></td>
                <td style="text-align: right;">
                    <?php if($item->is_variable_pricing): ?>
                        <span style="color:#6a1b9a; font-weight:bold; font-size: 11px;">(Variable Pricing)</span>
                    <?php else: ?>
                        <span style="color:#0066cc; font-weight:bold;">S: <?= number_format($item->price, 2) ?></span><br>
                        <span style="font-size: 11px; color:#c62828;">C: <?= number_format($item->cost, 2) ?></span>
                    <?php endif; ?>
                </td>
                
                <?php $isLowStock = ($item->type == 'Inventory' && $item->quantity_on_hand <= $item->minimum_stock_level); ?>
                <td style="text-align: center;">
                    <?php if($item->type == 'Service'): ?>
                        <span style="color:#aaa;">--</span>
                    <?php else: ?>
                        <span style="font-weight:bold; color: <?= $isLowStock ? '#c62828' : '#2e7d32' ?>; <?= $isLowStock ? 'background:#ffebee; padding:4px 8px; border-radius:4px;' : '' ?>">
                            <?= $item->quantity_on_hand ?>
                        </span>
                        <div style="font-size: 10px; color:#888;">Min: <?= $item->minimum_stock_level ?></div>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <button class="btn btn-small btn-outline" data-item='<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>' onclick="openViewModal(this)">View</button>
                    <a href="<?= APP_URL ?>/inventory/edit/<?= $item->id ?>" class="btn btn-small" style="background:#f0ad4e; border:none; color:#fff; display:inline-block;">Edit</a>
                    <form action="<?= APP_URL ?>/inventory" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="item_id" value="<?= $item->id ?>">
                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this product permanently? This cannot be undone.');">Del</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="pagination" id="paginationContainer">
        <?php if($data['total_pages'] > 1): ?>
            <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                <?php 
                    $params = http_build_query([
                        'search' => $data['search'],
                        'category_id' => $data['filters']['category_id'],
                        'vendor_id' => $data['filters']['vendor_id'],
                        'warehouse_id' => $data['filters']['warehouse_id'],
                        'min_price' => $data['filters']['min_price'],
                        'max_price' => $data['filters']['max_price'],
                        'page' => $i
                    ]); 
                ?>
                <a href="?<?= $params ?>" class="page-btn <?= ($data['page'] == $i) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>

<!-- View Details Modal (Read-Only) -->
<div class="modal" id="viewModal">
    <div class="modal-content">
        <h3 style="margin-top:0; color:#0066cc;" id="v_name">Product Name</h3>
        <table class="data-table" style="background:transparent; box-shadow:none;">
            <tr><th style="width: 40%;">SKU/Code</th><td id="v_code"></td></tr>
            <tr><th>Category</th><td id="v_cat"></td></tr>
            <tr><th>Supplier</th><td id="v_ven" style="color:#0066cc; font-weight:bold;"></td></tr>
            <tr><th>Warehouse</th><td id="v_wh" style="color:#e65100; font-weight:bold;"></td></tr>
            <tr><th>Type</th><td id="v_type"></td></tr>
            <tr><th>Sales Price</th><td id="v_price" style="color:#0066cc; font-weight:bold;"></td></tr>
            <tr><th>Unit Cost</th><td id="v_cost" style="color:#c62828;"></td></tr>
            <tr><th>Attributes</th><td id="v_vars" style="font-size:12px;"></td></tr>
            <tr><th>In Stock</th><td id="v_qty" style="font-weight:bold;"></td></tr>
            <tr><th>Min Level</th><td id="v_min"></td></tr>
        </table>
        <button class="btn btn-outline" style="width:100%; margin-top:15px;" onclick="document.getElementById('viewModal').style.display='none'">Close</button>
    </div>
</div>

<script>
    function openViewModal(btn) {
        const data = JSON.parse(btn.getAttribute('data-item'));
        document.getElementById('viewModal').style.display = 'flex';
        document.getElementById('v_name').innerText = data.name;
        document.getElementById('v_code').innerText = data.item_code || 'N/A';
        document.getElementById('v_cat').innerText = data.category_name || 'Uncategorized';
        document.getElementById('v_ven').innerText = data.vendor_name || 'None';
        document.getElementById('v_wh').innerText = data.warehouse_name || 'None';
        document.getElementById('v_type').innerText = data.type;
        
        if(data.is_variable_pricing == 1) {
            document.getElementById('v_price').innerHTML = '<span style="color:#6a1b9a;">Variable Pricing</span>';
            document.getElementById('v_cost').innerHTML = '<span style="color:#6a1b9a;">Variable Pricing</span>';
        } else {
            document.getElementById('v_price').innerText = 'Rs: ' + parseFloat(data.price).toFixed(2);
            document.getElementById('v_cost').innerText = 'Rs: ' + parseFloat(data.cost).toFixed(2);
        }
        
        let varsHtml = '';
        if(data.variations && data.variations.length > 0) {
            varsHtml = data.variations.map(v => {
                let skuTag = v.sku ? ` (SKU: ${v.sku})` : '';
                let priceTag = (data.is_variable_pricing == 1 && v.price) ? ` | Rs: ${v.price}` : '';
                return `<span style="background:rgba(0,102,204,0.1); color:#0066cc; padding:4px 8px; border-radius:4px; margin-right:4px; display:inline-block; margin-bottom:4px; font-size:11px;">${v.variation_name}: <strong>${v.value_name}</strong>${skuTag}${priceTag}</span>`;
            }).join('');
        } else {
            varsHtml = '<span style="color:#aaa;">No attributes</span>';
        }
        document.getElementById('v_vars').innerHTML = varsHtml;
        
        document.getElementById('v_qty').innerText = data.type === 'Service' ? 'N/A' : data.quantity_on_hand;
        document.getElementById('v_min').innerText = data.type === 'Service' ? 'N/A' : data.minimum_stock_level;
    }

    // Advanced Live Search & Filtering DOM Injection
    let searchTimeout = null;
    
    document.getElementById('searchInput').addEventListener('input', triggerSearchDelay);

    function triggerSearchDelay() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(triggerSearch, 400); // 400ms debounce for typing
    }

    function triggerSearch() {
        const query = encodeURIComponent(document.getElementById('searchInput').value);
        const catId = encodeURIComponent(document.getElementById('filterCategory').value);
        const venId = encodeURIComponent(document.getElementById('filterVendor').value);
        const whId = encodeURIComponent(document.getElementById('filterWarehouse').value);
        const minP = encodeURIComponent(document.getElementById('filterMinPrice').value);
        const maxP = encodeURIComponent(document.getElementById('filterMaxPrice').value);
        
        const url = `?search=${query}&category_id=${catId}&vendor_id=${venId}&warehouse_id=${whId}&min_price=${minP}&max_price=${maxP}&page=1`;
        
        fetch(url).then(response => response.text()).then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('tableBody').innerHTML = doc.getElementById('tableBody').innerHTML;
            document.getElementById('paginationContainer').innerHTML = doc.getElementById('paginationContainer').innerHTML;
            window.history.pushState({}, '', url);
        });
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterVendor').value = '';
        document.getElementById('filterWarehouse').value = '';
        document.getElementById('filterMinPrice').value = '';
        document.getElementById('filterMaxPrice').value = '';
        triggerSearch();
    }
</script>