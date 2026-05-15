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
    
    .kpi-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--mac-border); border-left: 4px solid #f0ad4e; margin-bottom: 20px; width: 300px;}
    @media (prefers-color-scheme: dark) { .kpi-card { background: #1e1e2d; } }
    .kpi-title { font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
    .kpi-val { font-size: 24px; font-weight: bold; color: var(--text-main); }
    
    .search-bar { width: 100%; padding: 10px 15px; border: 1px solid var(--mac-border); border-radius: 8px; font-size: 14px; margin-bottom: 15px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);}
    @media (prefers-color-scheme: dark) { .data-table { background: #1e1e2d; } }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 14px;}
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; color:#555;}
    
    .pagination { display: flex; justify-content: flex-end; gap: 5px; margin-top: 15px; }
    .page-btn { padding: 6px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: #fff; color: #333; text-decoration: none; font-size: 12px;}
    .page-btn.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); padding: 25px; border-radius: 12px; width: 500px; border: 1px solid var(--mac-border); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
    
    .val-badge { display: inline-block; background: rgba(0,102,204,0.1); color: #0066cc; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-right: 4px; margin-bottom: 4px; border: 1px solid rgba(0,102,204,0.2);}
</style>

<div class="header-actions">
    <div>
        <h2 style="margin: 0 0 5px 0;">Variation Management</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Manage global attributes (Colors, Sizes, Materials).</p>
    </div>
    <button class="btn" onclick="openModal('add')">+ Add Variation</button>
</div>

<!-- Quick Navigation Links -->
<div class="quick-links">
    <span style="font-size: 11px; color: #888; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Related:</span>
    <a href="<?= APP_URL ?>/inventory" class="btn-quick">📦 Products</a>
    <a href="<?= APP_URL ?>/category" class="btn-quick">📁 Categories</a>
    <a href="<?= APP_URL ?>/variation" class="btn-quick active">✨ Variations</a>
</div>

<div class="kpi-card">
    <div class="kpi-title">Total Active Variations</div>
    <div class="kpi-val"><?= $data['total_vars'] ?></div>
</div>

<?php if(!empty($data['error'])): ?><div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div><?php endif; ?>
<?php if(!empty($data['success'])): ?><div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div><?php endif; ?>

<input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search Variations... (Type to search live)" value="<?= htmlspecialchars($data['search']) ?>">

<div id="tableContainer">
    <table class="data-table">
        <thead>
            <tr>
                <th>Variation Name</th>
                <th style="width: 50%;">Available Values</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($data['variations'])): ?>
            <tr><td colspan="3" style="text-align: center; color: #888; padding: 20px;">No variations found.</td></tr>
            <?php else: foreach($data['variations'] as $var): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($var->name) ?></strong><br>
                    <span style="font-size: 11px; color: #888;"><?= htmlspecialchars($var->description) ?></span>
                </td>
                <td>
                    <?php if(empty($var->values)): ?>
                        <span style="color:#aaa; font-style:italic;">No values assigned</span>
                    <?php else: ?>
                        <?php foreach($var->values as $val): ?>
                            <span class="val-badge"><?= htmlspecialchars($val->value_name) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php 
                        $valArray = array_map(function($v) { return $v->value_name; }, $var->values);
                        $valString = implode(', ', $valArray);
                    ?>
                    <button class="btn btn-small btn-outline" onclick="openModal('edit', '<?= $var->id ?>', '<?= htmlspecialchars(addslashes($var->name)) ?>', '<?= htmlspecialchars(addslashes($var->description)) ?>', '<?= htmlspecialchars(addslashes($valString)) ?>')">Edit</button>
                    <form action="<?= APP_URL ?>/variation" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_variation">
                        <input type="hidden" name="variation_id" value="<?= $var->id ?>">
                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this variation permanently? It will be removed from all products.');">Del</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="pagination" id="paginationContainer">
        <?php if($data['total_pages'] > 1): ?>
            <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($data['search']) ?>" class="page-btn <?= ($data['page'] == $i) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Form -->
<div class="modal" id="varModal">
    <div class="modal-content">
        <h3 id="modalTitle" style="margin-top:0;">Add Variation</h3>
        <form action="<?= APP_URL ?>/variation?page=<?= $data['page'] ?>&search=<?= urlencode($data['search']) ?>" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_variation">
            <input type="hidden" name="variation_id" id="formVarId" value="">
            
            <div class="form-group"><label>Variation Name (e.g., Color, Size) *</label><input type="text" name="name" id="f_name" class="form-control" required></div>
            <div class="form-group"><label>Description</label><input type="text" name="description" id="f_desc" class="form-control"></div>
            
            <div class="form-group" style="background: rgba(0,0,0,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--mac-border);">
                <label>Variation Values (Comma Separated) *</label>
                <p style="font-size: 11px; color:#666; margin-top:0;">Type values separated by commas. Example: <em>Red, Blue, Green, Yellow</em></p>
                <textarea name="values" id="f_values" class="form-control" rows="3" placeholder="Small, Medium, Large, XL" required></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('varModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" id="modalSubmitBtn">Save Variation</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Live Search DOM Injection
    let searchTimeout = null;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = encodeURIComponent(e.target.value);
            const url = `?search=${query}&page=1`;
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    document.getElementById('tableBody').innerHTML = doc.getElementById('tableBody').innerHTML;
                    document.getElementById('paginationContainer').innerHTML = doc.getElementById('paginationContainer').innerHTML;
                    window.history.pushState({}, '', url);
                });
        }, 300);
    });

    function openModal(mode, id = '', name = '', desc = '', values = '') {
        document.getElementById('varModal').style.display = 'flex';
        if (mode === 'add') {
            document.getElementById('modalTitle').innerText = 'Add Variation';
            document.getElementById('formAction').value = 'add_variation';
            document.getElementById('modalSubmitBtn').innerText = 'Save Variation';
            document.getElementById('f_name').value = '';
            document.getElementById('f_desc').value = '';
            document.getElementById('f_values').value = '';
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Variation';
            document.getElementById('formAction').value = 'edit_variation';
            document.getElementById('formVarId').value = id;
            document.getElementById('modalSubmitBtn').innerText = 'Update Changes';
            document.getElementById('f_name').value = name;
            document.getElementById('f_desc').value = desc;
            document.getElementById('f_values').value = values;
        }
    }
</script>