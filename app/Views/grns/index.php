<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-danger { background: #ffebee; color: #c62828; border: none; }
    .btn-small { padding: 4px 8px; font-size: 11px; margin-right: 5px; cursor: pointer; border-radius: 4px;}
    
    .quick-links { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; background: rgba(0,0,0,0.02); padding: 10px 15px; border-radius: 8px; border: 1px solid var(--mac-border); }
    .btn-quick { padding: 6px 12px; background: #fff; color: #555; border: 1px solid var(--mac-border); border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .btn-quick:hover { background: rgba(0,102,204,0.05); color: #0066cc; border-color: #0066cc; }
    .btn-quick.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    
    .filter-panel { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid var(--mac-border); margin-bottom: 15px; display: flex; gap: 15px; align-items: flex-end;}
    @media (prefers-color-scheme: dark) { .filter-panel { background: #1e1e2d; } }
    .search-bar { width: 100%; padding: 10px 15px; border: 1px solid var(--mac-border); border-radius: 8px; font-size: 14px; margin-bottom: 15px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);}
    @media (prefers-color-scheme: dark) { .data-table { background: #1e1e2d; } }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; color:#555;}
    
    .pagination { display: flex; justify-content: flex-end; gap: 5px; margin-top: 15px; }
    .page-btn { padding: 6px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: #fff; color: #333; text-decoration: none; font-size: 12px;}
    .page-btn.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    
    .form-group { margin:0; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 11px; font-weight: 600; color: #888; text-transform: uppercase; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="header-actions">
    <h2>Goods Receipt Notes (GRN)</h2>
    <a href="<?= APP_URL ?>/grn/create" class="btn">+ Create Manual GRN</a>
</div>

<div class="quick-links">
    <span style="font-size: 11px; color: #888; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Supply Chain:</span>
    <a href="<?= APP_URL ?>/vendor" class="btn-quick">🏢 Vendors</a>
    <a href="<?= APP_URL ?>/purchase" class="btn-quick">🛒 Purchase Orders</a>
    <a href="<?= APP_URL ?>/grn" class="btn-quick active">📦 Goods Receipts (GRN)</a>
    <a href="<?= APP_URL ?>/inventory" class="btn-quick">🗄️ Inventory</a>
</div>

<?php if(!empty($data['error'])): ?><div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div><?php endif; ?>
<?php if(!empty($data['success'])): ?><div style="padding: 15px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px; font-weight:bold; font-size: 14px;">✓ <?= $data['success'] ?></div><?php endif; ?>

<input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search GRN Number, PO Number or Supplier Name..." value="<?= htmlspecialchars($data['search']) ?>">

<div class="filter-panel">
    <div class="form-group" style="flex: 1;">
        <label>Filter by Supplier</label>
        <select id="filterVendor" class="form-control" onchange="triggerSearch()">
            <option value="">All Suppliers</option>
            <?php foreach($data['vendors'] as $ven): ?>
                <option value="<?= $ven->id ?>" <?= ($data['filters']['vendor_id'] == $ven->id) ? 'selected' : '' ?>><?= htmlspecialchars($ven->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-outline" style="padding: 8px 12px; border-color:#ccc; color:#666;" onclick="clearFilters()">Clear Filters</button>
</div>

<div id="tableContainer">
    <table class="data-table">
        <thead>
            <tr>
                <th>GRN Number & Date</th>
                <th>Supplier / Vendor</th>
                <th>Linked PO</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($data['grns'])): ?>
            <tr><td colspan="4" style="text-align: center; color: #888; padding: 20px;">No Goods Receipt Notes found.</td></tr>
            <?php else: foreach($data['grns'] as $grn): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($grn->grn_number) ?></strong><br>
                    <span style="font-size: 11px; color: #888;">Rcvd: <?= date('M d, Y', strtotime($grn->grn_date)) ?></span>
                </td>
                <td>
                    <span style="color:#0066cc; font-weight:bold;"><?= htmlspecialchars($grn->vendor_name) ?></span><br>
                    <span style="font-size: 10px; color:#888;">Created by: <?= htmlspecialchars($grn->creator_name) ?></span>
                </td>
                <td>
                    <?php if($grn->po_number): ?>
                        <span style="background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight:bold;">🔗 <?= htmlspecialchars($grn->po_number) ?></span>
                    <?php else: ?>
                        <span style="color: #aaa; font-style: italic; font-size: 11px;">Manual GRN</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <a href="<?= APP_URL ?>/grn/show/<?= $grn->id ?>" class="btn btn-small btn-outline" target="_blank">Print Document</a>
                    <form action="<?= APP_URL ?>/grn" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_grn">
                        <input type="hidden" name="grn_id" value="<?= $grn->id ?>">
                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this GRN? This will REVERSE the physical stock quantities back out of inventory!');">Delete & Reverse Stock</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
    let searchTimeout = null;
    document.getElementById('searchInput').addEventListener('input', triggerSearchDelay);
    function triggerSearchDelay() { clearTimeout(searchTimeout); searchTimeout = setTimeout(triggerSearch, 400); }

    function triggerSearch() {
        const query = encodeURIComponent(document.getElementById('searchInput').value);
        const venId = encodeURIComponent(document.getElementById('filterVendor').value);
        const url = `?search=${query}&vendor_id=${venId}&page=1`;
        
        fetch(url).then(response => response.text()).then(html => {
            const parser = new DOMParser(); const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('tableBody').innerHTML = doc.getElementById('tableBody').innerHTML;
            window.history.pushState({}, '', url);
        });
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterVendor').value = '';
        triggerSearch();
    }
</script>