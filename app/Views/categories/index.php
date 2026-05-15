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
    
    .kpi-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--mac-border); border-left: 4px solid #6a1b9a; margin-bottom: 20px; width: 300px;}
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
    .modal-content { background: var(--mac-bg); padding: 25px; border-radius: 12px; width: 450px; border: 1px solid var(--mac-border); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="header-actions">
    <div>
        <h2 style="margin: 0 0 5px 0;">Product Categories</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Organize your inventory catalog.</p>
    </div>
    <button class="btn" onclick="openModal('add')">+ Add Category</button>
</div>

<!-- Quick Navigation Links -->
<div class="quick-links">
    <span style="font-size: 11px; color: #888; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Related:</span>
    <a href="<?= APP_URL ?>/inventory" class="btn-quick">📦 Products</a>
    <a href="<?= APP_URL ?>/category" class="btn-quick active">📁 Categories</a>
    <a href="<?= APP_URL ?>/variation" class="btn-quick">✨ Variations</a>
</div>

<div class="kpi-card">
    <div class="kpi-title">Total Active Categories</div>
    <div class="kpi-val"><?= $data['total_cats'] ?></div>
</div>

<?php if(!empty($data['error'])): ?><div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div><?php endif; ?>
<?php if(!empty($data['success'])): ?><div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div><?php endif; ?>

<input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search Categories... (Type to search live)" value="<?= htmlspecialchars($data['search']) ?>">

<div id="tableContainer">
    <table class="data-table">
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Description</th>
                <th style="text-align: center;">Items Linked</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($data['categories'])): ?>
            <tr><td colspan="4" style="text-align: center; color: #888; padding: 20px;">No categories found.</td></tr>
            <?php else: foreach($data['categories'] as $cat): ?>
            <tr>
                <td><strong><?= htmlspecialchars($cat->name) ?></strong></td>
                <td style="color:#666; font-size:13px;"><?= htmlspecialchars($cat->description) ?: '<em style="color:#aaa;">No description</em>' ?></td>
                <td style="text-align: center;">
                    <span style="background: rgba(0,0,0,0.05); padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size:11px;"><?= $cat->item_count ?> Items</span>
                </td>
                <td style="text-align: center;">
                    <button class="btn btn-small btn-outline" onclick="openModal('edit', '<?= $cat->id ?>', '<?= htmlspecialchars(addslashes($cat->name)) ?>', '<?= htmlspecialchars(addslashes($cat->description)) ?>')">Edit</button>
                    <form action="<?= APP_URL ?>/category" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= $cat->id ?>">
                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Delete this category? Items inside it will become Uncategorized.');">Del</button>
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
<div class="modal" id="catModal">
    <div class="modal-content">
        <h3 id="modalTitle" style="margin-top:0;">Add Category</h3>
        <form action="<?= APP_URL ?>/category?page=<?= $data['page'] ?>&search=<?= urlencode($data['search']) ?>" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_category">
            <input type="hidden" name="category_id" id="formCatId" value="">
            
            <div class="form-group"><label>Category Name *</label><input type="text" name="name" id="f_name" class="form-control" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" id="f_desc" class="form-control" rows="3"></textarea></div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('catModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" id="modalSubmitBtn">Save Category</button>
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

    function openModal(mode, id = '', name = '', desc = '') {
        document.getElementById('catModal').style.display = 'flex';
        if (mode === 'add') {
            document.getElementById('modalTitle').innerText = 'Add Category';
            document.getElementById('formAction').value = 'add_category';
            document.getElementById('modalSubmitBtn').innerText = 'Save Category';
            document.getElementById('f_name').value = '';
            document.getElementById('f_desc').value = '';
        } else {
            document.getElementById('modalTitle').innerText = 'Edit Category';
            document.getElementById('formAction').value = 'edit_category';
            document.getElementById('formCatId').value = id;
            document.getElementById('modalSubmitBtn').innerText = 'Update Changes';
            document.getElementById('f_name').value = name;
            document.getElementById('f_desc').value = desc;
        }
    }
</script>