<style>
    .btn { padding: 10px 20px; background: #0066cc; color: #fff; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;}
    .btn:hover { background: #005bb5; }
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-outline:hover { background: rgba(0,102,204,0.05); }
    
    .form-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--text-main); }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--mac-border); border-radius: 6px; background: var(--mega-bg); color: var(--text-main); box-sizing: border-box; font-size: 14px; transition: border 0.2s; }
    .form-control:focus { border-color: #0066cc; outline: none; }
    
    .perm-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .perm-table th, .perm-table td { padding: 10px 12px; border-bottom: 1px solid var(--mac-border); text-align: left; }
    .perm-table th { background: rgba(0,0,0,0.02); font-weight: 700; font-size: 12px; text-transform: uppercase; color: var(--text-muted); }
    .perm-table tr:hover { background: rgba(0,0,0,0.01); }
    
    .checkbox-cell { text-align: center; width: 120px; }
    .checkbox-cell input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #0066cc; }

    .module-group-title { font-weight: 700; font-size: 14px; color: #0066cc; padding: 12px 12px 6px; }
</style>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--mac-border); padding-bottom: 15px; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; font-weight: 700;"><i class="ph ph-user-plus" style="color:#0066cc;"></i> Create User Account</h2>
            <p style="margin: 5px 0 0 0; font-size: 13px; color: var(--text-muted);">Configure login details and granular access privileges.</p>
        </div>
        <a href="<?= APP_URL ?>/user" class="btn btn-outline" style="padding: 8px 14px; font-size: 13px;"><i class="ph ph-arrow-left"></i> Back to List</a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 12px 16px; background:#ffebee; color:#c62828; border-radius:6px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
            <?= $data['error'] ?>
        </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/user/create" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" required placeholder="e.g. jason.m" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" required placeholder="e.g. jason@curtiss.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Link to Employee Profile</label>
                    <select name="employee_id" class="form-control">
                        <option value="">-- No Linked Employee --</option>
                        <?php foreach($data['employees'] as $emp): ?>
                            <option value="<?= $emp->id ?>" <?= (($_POST['employee_id'] ?? '') == $emp->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name) ?> (<?= htmlspecialchars($emp->job_title) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label>System Role *</label>
                    <select name="role" class="form-control" required onchange="togglePermissionsGrid(this.value)">
                        <option value="office" <?= (($_POST['role'] ?? 'office') === 'office') ? 'selected' : '' ?>>Office Staff</option>
                        <option value="driver" <?= (($_POST['role'] ?? '') === 'driver') ? 'selected' : '' ?>>Driver</option>
                        <option value="rep" <?= (($_POST['role'] ?? '') === 'rep') ? 'selected' : '' ?>>Rep (Sales Representative)</option>
                        <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin (Full System Control)</option>
                        <option value="accountant" <?= (($_POST['role'] ?? '') === 'accountant') ? 'selected' : '' ?>>Accountant (Full Finance Access)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Temporary Password *</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 4 characters">
                </div>

                <div class="form-group">
                    <label>Account Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="Active" selected>Active</option>
                        <option value="Blocked">Blocked / Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Digital Signature (Optional PNG/JPG)</label>
                    <input type="file" name="signature" class="form-control" accept=".png, .jpg, .jpeg" style="padding: 6px;">
                </div>
            </div>
        </div>

        <div id="permissionsCard" class="card" style="margin: 20px 0; background: rgba(0,0,0,0.01); border: 1px solid var(--mac-border); padding: 20px; border-radius: 8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="ph ph-key" style="color:#0066cc;"></i> Granular Modular Permissions</h3>
                <div style="display:flex; gap:10px; font-size:12px;">
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="toggleAllCheckboxes('view', true)">All View</button>
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="toggleAllCheckboxes('create_edit', true)">All Create/Edit</button>
                    <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="toggleAllCheckboxes('delete', true)">All Delete</button>
                    <button type="button" class="btn btn-outline text-danger" style="border-color:#ff3b30; padding: 4px 10px; font-size: 11px;" onclick="clearAllCheckboxes()">Clear All</button>
                </div>
            </div>
            
            <p id="adminNotice" style="display:none; color:#c62828; font-weight:bold; font-size:13px; margin:0 0 10px 0;">
                💡 Admin accounts automatically bypass permissions. The checkboxes below will be ignored.
            </p>

            <table class="perm-table">
                <thead>
                    <tr>
                        <th>Module / Feature Section</th>
                        <th style="text-align: center; width:120px;">View Access</th>
                        <th style="text-align: center; width:120px;">Create / Edit</th>
                        <th style="text-align: center; width:120px;">Delete Access</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['modules'] as $moduleKey => $moduleName): ?>
                    <tr>
                        <td>
                            <strong style="font-size: 13px; color: var(--text-main);"><?= htmlspecialchars($moduleName) ?></strong>
                            <span style="display:block; font-size:10px; color:var(--text-muted); font-family:mono;"><?= $moduleKey ?></span>
                        </td>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="permissions[<?= $moduleKey ?>][view]" value="1" class="chk-view" onclick="autoCheckDependencies('<?= $moduleKey ?>', 'view')">
                        </td>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="permissions[<?= $moduleKey ?>][create_edit]" value="1" class="chk-create" onclick="autoCheckDependencies('<?= $moduleKey ?>', 'create_edit')">
                        </td>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="permissions[<?= $moduleKey ?>][delete]" value="1" class="chk-delete" onclick="autoCheckDependencies('<?= $moduleKey ?>', 'delete')">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--mac-border); padding-top: 20px;">
            <a href="<?= APP_URL ?>/user" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn"><i class="ph ph-check-square"></i> Save User Account</button>
        </div>
    </form>
</div>

<script>
    function togglePermissionsGrid(role) {
        const adminNotice = document.getElementById('adminNotice');
        const checkboxes = document.querySelectorAll('.perm-table input[type="checkbox"]');
        if (role === 'admin') {
            adminNotice.style.display = 'block';
            checkboxes.forEach(chk => {
                chk.checked = true;
                chk.disabled = true;
            });
        } else {
            adminNotice.style.display = 'none';
            checkboxes.forEach(chk => {
                chk.disabled = false;
            });
        }
    }

    function toggleAllCheckboxes(type, state) {
        let selector = '';
        if (type === 'view') selector = '.chk-view';
        else if (type === 'create_edit') selector = '.chk-create';
        else if (type === 'delete') selector = '.chk-delete';
        
        document.querySelectorAll(selector).forEach(chk => {
            if (!chk.disabled) {
                chk.checked = state;
            }
        });
        
        // If checking create_edit or delete, automatically check view as well
        if (state && (type === 'create_edit' || type === 'delete')) {
            document.querySelectorAll('.chk-view').forEach(chk => {
                if (!chk.disabled) chk.checked = true;
            });
        }
    }

    function clearAllCheckboxes() {
        document.querySelectorAll('.perm-table input[type="checkbox"]').forEach(chk => {
            if (!chk.disabled) {
                chk.checked = false;
            }
        });
    }

    function autoCheckDependencies(module, action) {
        const viewChk = document.querySelector(`input[name="permissions[${module}][view]"]`);
        const createChk = document.querySelector(`input[name="permissions[${module}][create_edit]"]`);
        const deleteChk = document.querySelector(`input[name="permissions[${module}][delete]"]`);
        
        if (action === 'create_edit' && createChk.checked) {
            viewChk.checked = true;
        }
        if (action === 'delete' && deleteChk.checked) {
            viewChk.checked = true;
        }
        if (action === 'view' && !viewChk.checked) {
            createChk.checked = false;
            deleteChk.checked = false;
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        togglePermissionsGrid(document.querySelector('select[name="role"]').value);
    });
</script>
