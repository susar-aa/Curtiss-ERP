<?php
// Build the Hierarchical Tree Data Structure
$tree = [];
foreach($data['accounts'] as $acc) {
    if (empty($acc->parent_id)) {
        $tree[$acc->id] = ['parent' => $acc, 'children' => []];
    }
}
foreach($data['accounts'] as $acc) {
    if (!empty($acc->parent_id)) {
        if (isset($tree[$acc->parent_id])) {
            $tree[$acc->parent_id]['children'][] = $acc;
        } else {
            // Fallback if parent is somehow missing
            $tree[$acc->id] = ['parent' => $acc, 'children' => []];
        }
    }
}
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; text-decoration: none;}
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-small { padding: 4px 8px; font-size: 11px; cursor: pointer; border-radius: 4px;}
    .btn-danger { background: #ffebee; color: #c62828; border: none; }
    
    .search-bar { width: 100%; padding: 10px 15px; border: 1px solid var(--mac-border); border-radius: 8px; font-size: 14px; margin-bottom: 15px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);}
    @media (prefers-color-scheme: dark) { .data-table { background: #1e1e2d; } }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; color:#555;}
    
    /* Hierarchy Styles */
    .row-parent td { font-weight: bold; background: rgba(0,0,0,0.01); border-bottom: 1px solid #ddd;}
    @media (prefers-color-scheme: dark) { .row-parent td { background: rgba(255,255,255,0.02); border-bottom: 1px solid #444; } }
    .row-child td:first-child { padding-left: 35px; color: #666;}
    .sub-indicator { color: #aaa; margin-right: 5px; }

    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .type-Asset { background: #e3f2fd; color: #1565c0; }
    .type-Liability { background: #ffebee; color: #c62828; }
    .type-Equity { background: #f3e5f5; color: #6a1b9a; }
    .type-Revenue { background: #e8f5e9; color: #2e7d32; }
    .type-Expense { background: #fff3e0; color: #ef6c00; }

    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 500px; border: 1px solid var(--mac-border); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="header-actions">
    <div>
        <h2 style="margin: 0 0 5px 0;">Chart of Accounts</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Manage your Main Ledgers and Sub-Accounts.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-outline" onclick="openModal('add_sub')">+ Add Sub-Account</button>
        <button class="btn" onclick="openModal('add_main')">+ Add Main Account</button>
    </div>
</div>

<?php if(!empty($data['error'])): ?><div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div><?php endif; ?>
<?php if(!empty($data['success'])): ?><div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div><?php endif; ?>

<input type="text" id="searchInput" class="search-bar" placeholder="🔍 Search Ledger by Code or Name..." onkeyup="filterTable()">

<div id="tableContainer">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Account Code & Name</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 20%; text-align: right;">Current Balance</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 20%; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($tree)): ?>
                <tr><td colspan="5" style="text-align: center; color: #888; padding: 20px;">No accounts found. Add one above.</td></tr>
            <?php else: foreach($tree as $node): ?>
                
                <!-- PARENT ROW -->
                <?php $parent = $node['parent']; ?>
                <tr class="coa-row row-parent">
                    <td><?= htmlspecialchars($parent->account_code) ?> - <?= htmlspecialchars($parent->account_name) ?></td>
                    <td><span class="badge type-<?= $parent->account_type ?>"><?= $parent->account_type ?></span></td>
                    <td style="text-align: right; font-family:monospace; font-size: 14px;">Rs: <?= number_format($parent->balance, 2) ?></td>
                    <td style="text-align: center;">
                        <?php if($parent->is_active): ?><span style="color: #2e7d32; font-size: 11px;">● Active</span>
                        <?php else: ?><span style="color: #c62828; font-size: 11px;">● Inactive</span><?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline btn-small" onclick="openModal('edit', '<?= $parent->id ?>', '<?= addslashes($parent->account_code) ?>', '<?= addslashes($parent->account_name) ?>', '<?= $parent->account_type ?>', '', <?= $parent->is_active ?>)">Edit</button>
                        <form action="<?= APP_URL ?>/accounting/coa" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_account">
                            <input type="hidden" name="account_id" value="<?= $parent->id ?>">
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Delete this account? All sub-accounts will be deleted too.');">Del</button>
                        </form>
                    </td>
                </tr>

                <!-- SUB-ACCOUNT ROWS -->
                <?php foreach($node['children'] as $child): ?>
                <tr class="coa-row row-child">
                    <td><span class="sub-indicator">↳</span> <?= htmlspecialchars($child->account_code) ?> - <?= htmlspecialchars($child->account_name) ?></td>
                    <td><span class="badge type-<?= $child->account_type ?>"><?= $child->account_type ?></span></td>
                    <td style="text-align: right; font-family:monospace; font-size: 14px;">Rs: <?= number_format($child->balance, 2) ?></td>
                    <td style="text-align: center;">
                        <?php if($child->is_active): ?><span style="color: #2e7d32; font-size: 11px;">● Active</span>
                        <?php else: ?><span style="color: #c62828; font-size: 11px;">● Inactive</span><?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline btn-small" onclick="openModal('edit', '<?= $child->id ?>', '<?= addslashes($child->account_code) ?>', '<?= addslashes($child->account_name) ?>', '<?= $child->account_type ?>', '<?= $child->parent_id ?>', <?= $child->is_active ?>)">Edit</button>
                        <form action="<?= APP_URL ?>/accounting/coa" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_account">
                            <input type="hidden" name="account_id" value="<?= $child->id ?>">
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Delete this sub-account?');">Del</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Account -->
<div class="modal" id="coaModal">
    <div class="modal-content">
        <h3 id="modalTitle" style="margin-top:0;">Manage Account</h3>
        <form action="<?= APP_URL ?>/accounting/coa" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_main">
            <input type="hidden" name="account_id" id="formId" value="">
            
            <div class="form-group" id="parentGroup" style="display:none; background:rgba(0,0,0,0.02); padding:15px; border-radius:8px; border:1px solid var(--mac-border);">
                <label>Parent Account</label>
                <select name="parent_id" id="formParent" class="form-control">
                    <option value="">-- No Parent (Make Main Account) --</option>
                    <?php foreach($data['main_accounts'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <span style="font-size:11px; color:#666;">Sub-accounts automatically inherit the financial Type of their Parent.</span>
            </div>

            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>Account Code *</label>
                    <input type="text" name="account_code" id="formCode" class="form-control" placeholder="e.g. 1010" required>
                </div>
                <div class="form-group" style="flex:2;">
                    <label>Account Name *</label>
                    <input type="text" name="account_name" id="formName" class="form-control" placeholder="e.g. Cash in Bank" required>
                </div>
            </div>

            <div class="form-group" id="typeGroup">
                <label>Financial Type *</label>
                <select name="account_type" id="formType" class="form-control" required>
                    <option value="Asset">Asset (Cash, Receivables, Property)</option>
                    <option value="Liability">Liability (Payables, Loans, Tax)</option>
                    <option value="Equity">Equity (Capital, Retained Earnings)</option>
                    <option value="Revenue">Revenue (Income, Sales)</option>
                    <option value="Expense">Expense (COGS, Rent, Salaries)</option>
                </select>
            </div>

            <div class="form-group" id="statusGroup" style="display:none;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="is_active" id="formStatus" value="1" style="width:16px; height:16px;"> Active Ledger
                </label>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('coaModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" id="submitBtn">Save Account</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(mode, id = '', code = '', name = '', type = '', parentId = '', status = 1) {
        document.getElementById('coaModal').style.display = 'flex';
        
        const actionInput = document.getElementById('formAction');
        const titleInput = document.getElementById('modalTitle');
        const parentGroup = document.getElementById('parentGroup');
        const typeGroup = document.getElementById('typeGroup');
        const statusGroup = document.getElementById('statusGroup');
        const btn = document.getElementById('submitBtn');

        // Reset fields
        document.getElementById('formId').value = id;
        document.getElementById('formCode').value = code;
        document.getElementById('formName').value = name;
        document.getElementById('formType').value = type || 'Asset';
        document.getElementById('formParent').value = parentId;
        document.getElementById('formStatus').checked = status == 1;

        if (mode === 'add_main') {
            titleInput.innerText = 'Create Main Account';
            actionInput.value = 'add_main';
            parentGroup.style.display = 'none';
            typeGroup.style.display = 'block';
            statusGroup.style.display = 'none';
            btn.innerText = 'Save Main Account';
            document.getElementById('formType').required = true;
            document.getElementById('formParent').required = false;
        } 
        else if (mode === 'add_sub') {
            titleInput.innerText = 'Create Sub-Account';
            actionInput.value = 'add_sub';
            parentGroup.style.display = 'block';
            typeGroup.style.display = 'none';
            statusGroup.style.display = 'none';
            btn.innerText = 'Save Sub-Account';
            document.getElementById('formParent').required = true;
            document.getElementById('formType').required = false; // Inherited
        } 
        else if (mode === 'edit') {
            titleInput.innerText = 'Edit Ledger Account';
            actionInput.value = 'edit_account';
            parentGroup.style.display = 'block';
            typeGroup.style.display = 'block';
            statusGroup.style.display = 'block';
            btn.innerText = 'Update Account';
            document.getElementById('formType').required = true;
            document.getElementById('formParent').required = false;
        }
    }

    function filterTable() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.coa-row');
        
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>