<?php
// Build the Hierarchical Tree Data Structure supporting 3 levels (Main -> Sub-Account -> Sub-Sub-Account)
$accountsMap = [];
foreach($data['accounts'] as $acc) {
    $acc->children = [];
    $accountsMap[$acc->id] = $acc;
}

$tree = [];
foreach($data['accounts'] as $acc) {
    if (empty($acc->parent_id)) {
        $tree[] = $accountsMap[$acc->id];
    } else {
        $parent = $accountsMap[$acc->parent_id] ?? null;
        if ($parent) {
            $parent->children[] = $accountsMap[$acc->id];
        } else {
            $tree[] = $accountsMap[$acc->id];
        }
    }
}
?>

<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn { 
        padding: 8px 16px; 
        background: #0066cc; 
        color: #fff; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-size: 13px; 
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s, transform 0.1s;
    }
    .btn:active { transform: scale(0.97); }
    .btn-outline { background: transparent; border: 1.5px solid #0066cc; color: #0066cc; }
    .btn-outline:hover { background: rgba(0, 102, 204, 0.05); }
    .btn-small { padding: 4px 10px; font-size: 11px; cursor: pointer; border-radius: 4px;}
    .btn-danger { background: #ffebee; color: #c62828; border: none; }
    
    .data-table { 
        width: 100%; 
        border-collapse: separate; 
        border-spacing: 0; 
        background: #fff; 
        border-radius: 12px; 
        overflow: hidden; 
        border: 1.5px solid var(--mac-border);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 100px;
    }
    @media (prefers-color-scheme: dark) { 
        .data-table { 
            background: #1c1c1e; 
            border-color: #2c2c2e; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
        } 
    }
    .data-table th, .data-table td { padding: 14px 18px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13.5px;}
    .data-table th { background-color: rgba(0,0,0,0.01); font-weight: 700; color: #475569; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;}
    @media (prefers-color-scheme: dark) {
        .data-table th {
            color: #94a3b8;
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid #2c2c2e;
        }
        .data-table td {
            border-bottom: 1px solid #2c2c2e;
            color: #e2e8f0;
        }
    }
    
    /* Hierarchy Styles */
    .row-parent td { font-weight: 700; background: rgba(0,0,0,0.01); }
    @media (prefers-color-scheme: dark) { .row-parent td { background: rgba(255,255,255,0.01); } }
    .row-child td:first-child { padding-left: 35px; color: inherit;}
    .sub-indicator { color: #8a8a8e; margin-right: 6px; font-weight: bold; }
    
    .coa-row { transition: background-color 0.15s; }
    .coa-row:hover { background-color: rgba(0, 102, 204, 0.04) !important; }

    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-block; }
    .type-Asset { background: #e3f2fd; color: #1565c0; }
    .type-Liability { background: #ffebee; color: #c62828; }
    .type-Equity { background: #f3e5f5; color: #6a1b9a; }
    .type-Revenue { background: #e8f5e9; color: #2e7d32; }
    .type-Expense { background: #fff3e0; color: #ef6c00; }
    
    @media (prefers-color-scheme: dark) {
        .type-Asset { background: rgba(21, 101, 192, 0.15); color: #64b5f6; }
        .type-Liability { background: rgba(198, 40, 40, 0.15); color: #ef5350; }
        .type-Equity { background: rgba(106, 27, 154, 0.15); color: #ba68c8; }
        .type-Revenue { background: rgba(46, 125, 50, 0.15); color: #81c784; }
        .type-Expense { background: rgba(239, 108, 0, 0.15); color: #ffb74d; }
    }

    /* macOS Modal Styles */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .modal-content { 
        background: #ffffff; 
        border: 1px solid #d1d1d6; 
        border-radius: 14px; 
        width: 480px; 
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15); 
        overflow: hidden; 
        display: flex;
        flex-direction: column;
    }
    @media (prefers-color-scheme: dark) {
        .modal-content {
            background: #2c2c2e;
            border-color: #3a3a3c;
        }
    }
    .modal-header {
        background: #f5f5f7;
        border-bottom: 1px solid #d1d1d6;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    @media (prefers-color-scheme: dark) {
        .modal-header {
            background: #1c1c1e;
            border-color: #3a3a3c;
        }
    }
    .modal-title {
        font-size: 15px;
        font-weight: 700;
        color: #1d1d1f;
        margin: 0;
    }
    @media (prefers-color-scheme: dark) {
        .modal-title {
            color: #ffffff;
        }
    }
    .modal-close {
        background: transparent;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #8e8e93;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .modal-close:hover {
        color: #48484a;
    }
    .modal-body {
        padding: 20px 24px;
    }
    .modal-footer {
        background: #f5f5f7;
        border-top: 1px solid #d1d1d6;
        padding: 12px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    @media (prefers-color-scheme: dark) {
        .modal-footer {
            background: #1c1c1e;
            border-color: #3a3a3c;
        }
    }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #48484a; }
    @media (prefers-color-scheme: dark) {
        .form-group label {
            color: #d1d1d6;
        }
    }
    .form-control { 
        width: 100%; 
        padding: 8px 12px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        background: #fff; 
        color: #1d1d1f; 
        font-size: 13px;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus {
        border-color: #007aff;
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15);
    }
    @media (prefers-color-scheme: dark) {
        .form-control {
            background: #1c1c1e;
            border-color: #48484a;
            color: #ffffff;
        }
    }

    /* ---- Command Bar (Dynamic Island style) ---- */
    .cmd-bar {
        position: fixed;
        bottom: 28px; left: 50%;
        transform: translateX(-50%);
        background: rgba(28, 28, 30, 0.92);
        backdrop-filter: saturate(180%) blur(28px);
        -webkit-backdrop-filter: saturate(180%) blur(28px);
        border: 0.5px solid rgba(255,255,255,0.12);
        border-radius: 9999px;
        padding: 7px 10px;
        display: flex; align-items: center; gap: 4px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 0 0.5px rgba(0,0,0,0.3);
        z-index: 1000;
    }
    .cmd-search {
        display: flex;
        align-items: center;
        gap: 9px;
        background: rgba(255,255,255,0.1);
        border-radius: 9999px;
        padding: 8px 14px;
        width: 196px;
        transition: width 0.3s cubic-bezier(0.25, 0.8, 0.25, 1),
                    background 0.2s;
    }
    .cmd-search:focus-within {
        width: 300px;
        background: rgba(255,255,255,0.18);
    }
    .cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; flex-shrink: 0; }
    .cmd-search input {
        background: transparent; border: none; outline: none;
        color: #fff; font-size: 14px; font-weight: 500;
        width: 100%;
    }
    .cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
    .cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
    .cmd-icon {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,0.8); font-size: 16px;
        background: transparent; border: none; cursor: pointer; text-decoration: none;
        transition: background 0.2s;
    }
    .cmd-icon:hover { background: rgba(255,255,255,0.12); color: #fff; }
</style>

<div class="header-actions">
    <div>
        <h2 style="margin: 0 0 5px 0;">Chart of Accounts</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Manage your Main Ledgers and Sub-Accounts.</p>
    </div>
</div>

<?php if(!empty($data['error'])): ?><div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div><?php endif; ?>
<?php if(!empty($data['success'])): ?><div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div><?php endif; ?>

<div id="tableContainer">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Account Code & Name</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 20%; text-align: right;">Current Balance</th>
                <th style="width: 15%; text-align: center;">Status</th>
                <th style="width: 15%; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($tree)): ?>
                <tr><td colspan="5" style="text-align: center; color: #888; padding: 20px;">No accounts found. Add one above.</td></tr>
            <?php else: foreach($tree as $parent): ?>
                
                <!-- PARENT ROW (Level 1) -->
                <tr class="coa-row row-parent">
                    <td>
                        <a href="<?= APP_URL ?>/accounting/history/<?= $parent->id ?>" style="text-decoration:none; color:#0066cc; font-weight:bold;">
                            📁 <?= htmlspecialchars($parent->account_code) ?> - <?= htmlspecialchars($parent->account_name) ?>
                        </a>
                    </td>
                    <td><span class="badge type-<?= $parent->account_type ?>"><?= $parent->account_type ?></span></td>
                    <td style="text-align: right; font-family:monospace; font-size: 14px;">Rs: <?= number_format($parent->balance, 2) ?></td>
                    <td style="text-align: center;">
                        <?php if($parent->is_active): ?><span style="color: #2e7d32; font-size: 11.5px; font-weight: 600;">● Active</span>
                        <?php else: ?><span style="color: #c62828; font-size: 11.5px; font-weight: 600;">● Inactive</span><?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline btn-small" onclick="openModal('edit', '<?= $parent->id ?>', '<?= addslashes($parent->account_code) ?>', '<?= addslashes($parent->account_name) ?>', '<?= $parent->account_type ?>', '', <?= $parent->is_active ?>)">Edit</button>
                    </td>
                </tr>

                <!-- SUB-ACCOUNT ROWS (Level 2) -->
                <?php foreach($parent->children as $child): ?>
                <tr class="coa-row row-child">
                    <td>
                        <span class="sub-indicator">↳</span> 
                        <a href="<?= APP_URL ?>/accounting/history/<?= $child->id ?>" style="text-decoration:none; color:inherit; font-weight:500;">
                            📄 <?= htmlspecialchars($child->account_code) ?> - <?= htmlspecialchars($child->account_name) ?>
                        </a>
                    </td>
                    <td><span class="badge type-<?= $child->account_type ?>"><?= $child->account_type ?></span></td>
                    <td style="text-align: right; font-family:monospace; font-size: 14px;">Rs: <?= number_format($child->balance, 2) ?></td>
                    <td style="text-align: center;">
                        <?php if($child->is_active): ?><span style="color: #2e7d32; font-size: 11.5px; font-weight: 600;">● Active</span>
                        <?php else: ?><span style="color: #c62828; font-size: 11.5px; font-weight: 600;">● Inactive</span><?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline btn-small" onclick="openModal('edit', '<?= $child->id ?>', '<?= addslashes($child->account_code) ?>', '<?= addslashes($child->account_name) ?>', '<?= $child->account_type ?>', '<?= $child->parent_id ?>', <?= $child->is_active ?>)">Edit</button>
                    </td>
                </tr>

                <!-- SUB-SUB-ACCOUNT ROWS (Level 3) -->
                <?php foreach($child->children as $subsub): ?>
                <tr class="coa-row row-child level-3-row" style="background: rgba(0,0,0,0.01);">
                    <td style="padding-left: 55px;">
                        <span class="sub-indicator" style="color: #888; font-weight: bold; margin-right: 5px;">↳ ↳ 📁</span> 
                        <a href="<?= APP_URL ?>/accounting/history/<?= $subsub->id ?>" style="text-decoration:none; color:inherit; font-weight:500; font-style: italic; color: #475569;">
                            <?= htmlspecialchars($subsub->account_code) ?> - <?= htmlspecialchars($subsub->account_name) ?>
                        </a>
                    </td>
                    <td><span class="badge type-<?= $subsub->account_type ?>" style="opacity: 0.85;"><?= $subsub->account_type ?></span></td>
                    <td style="text-align: right; font-family:monospace; font-size: 13px; color: #475569;">Rs: <?= number_format($subsub->balance, 2) ?></td>
                    <td style="text-align: center;">
                        <?php if($subsub->is_active): ?><span style="color: #2e7d32; font-size: 11.5px; font-weight: 600;">● Active</span>
                        <?php else: ?><span style="color: #c62828; font-size: 11.5px; font-weight: 600;">● Inactive</span><?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-outline btn-small" onclick="openModal('edit', '<?= $subsub->id ?>', '<?= addslashes($subsub->account_code) ?>', '<?= addslashes($subsub->account_name) ?>', '<?= $subsub->account_type ?>', '<?= $subsub->parent_id ?>', <?= $subsub->is_active ?>)">Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>

                <!-- CUSTOMER ACCOUNTS UNDER AR (Virtual sub-sub accounts) -->
                <?php if (($child->account_code == '1200' || stripos($child->account_name, 'Receivable') !== false) && !empty($data['customers'])): ?>
                    <?php foreach($data['customers'] as $cust): ?>
                        <?php if (floatval($cust->outstanding_balance) != 0): ?>
                        <tr class="coa-row row-child customer-sub-row" style="background: rgba(99, 102, 241, 0.02);">
                            <td style="padding-left: 60px;">
                                <span class="sub-indicator" style="color: #6366f1; font-weight: bold; margin-right: 5px;">↳ 👤</span>
                                <span style="font-weight: 600; color: #4f46e5;"><?= htmlspecialchars($cust->name) ?></span>
                                <span style="font-size: 10px; color: #64748b; font-style: italic; margin-left: 6px;">(Customer Ledger)</span>
                            </td>
                            <td><span class="badge" style="background: #e0e7ff; color: #4338ca;">Customer AR</span></td>
                            <td style="text-align: right; font-family:monospace; font-size: 13px; color: #4338ca; font-weight: 600;">Rs: <?= number_format($cust->outstanding_balance, 2) ?></td>
                            <td style="text-align: center;"><span style="color: #4f46e5; font-size: 11px;">● Outstanding</span></td>
                            <td style="text-align: center;">
                                <a href="<?= APP_URL ?>/customer/edit/<?= $cust->id ?>" class="btn btn-outline btn-small" style="border-color: #4f46e5; color: #4f46e5; display: inline-block; padding: 3px 8px; font-size: 11px;">View Profile</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php endforeach; ?>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Add / Edit Account -->
<div class="modal" id="coaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Manage Account</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('coaModal').style.display='none'">&times;</button>
        </div>
        <form action="<?= APP_URL ?>/accounting/coa" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add_main">
                <input type="hidden" name="account_id" id="formId" value="">
                
                <div class="form-group" id="parentGroup" style="display:none; background:rgba(0,0,0,0.02); padding:15px; border-radius:8px; border:1px solid var(--mac-border);">
                    <label>Parent Account</label>
                    <select name="parent_id" id="formParent" class="form-control">
                        <option value="">-- No Parent (Make Main Account) --</option>
                        <?php foreach($data['accounts'] as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span style="font-size:11px; color:#666; display: block; margin-top: 6px;">Sub-accounts automatically inherit the financial Type of their Parent.</span>
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
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight: 600; font-size: 13px;">
                        <input type="checkbox" name="is_active" id="formStatus" value="1" style="width:16px; height:16px;"> Active Ledger
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('coaModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" id="submitBtn">Save Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Command Bar -->
<div class="cmd-bar">
    <div class="cmd-search" onclick="document.getElementById('searchInput').focus()">
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search accounts..." onkeyup="filterTable()">
    </div>
    <div class="cmd-divider"></div>
    <button type="button" class="cmd-icon" onclick="openModal('add_main')" title="Add Main Account"><i class="ph ph-folder-open"></i></button>
    <button type="button" class="cmd-icon" onclick="openModal('add_sub')" title="Add Sub-Account"><i class="ph ph-file-plus"></i></button>
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