<?php
?>
<style>
    .hrm-container {
        padding: 24px;
    }
    .glass-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 24px;
    }
    .header-actions { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 24px; 
        gap: 16px;
        flex-wrap: wrap;
    }
    .header-title-wrap h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .header-title-wrap p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 4px 0 0 0;
    }
    
    .btn { 
        padding: 10px 20px; 
        background: var(--text-accent); 
        color: #fff !important; 
        border: none; 
        border-radius: 12px; 
        cursor: pointer; 
        text-decoration: none; 
        font-size: 13.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s, transform 0.15s;
    }
    .btn:hover { 
        background: var(--text-accent-light); 
        transform: translateY(-1px);
    }
    .btn-outline { 
        background: transparent; 
        border: 1px solid var(--glass-border); 
        color: var(--text-main) !important; 
    }
    .btn-outline:hover {
        background: rgba(255, 255, 255, 0.08);
    }
    @media (prefers-color-scheme: dark) {
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.04);
        }
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 10px; 
    }
    .data-table th, .data-table td { 
        padding: 14px 16px; 
        text-align: left; 
        border-bottom: 1px solid var(--glass-border); 
    }
    .data-table th { 
        background-color: rgba(0, 0, 0, 0.03); 
        font-weight: 600; 
        font-size: 12px; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
    }
    @media (prefers-color-scheme: dark) {
        .data-table th {
            background-color: rgba(255, 255, 255, 0.02);
        }
    }
    .data-table td {
        color: var(--text-main);
        font-size: 13.5px;
    }
    .data-table tr:hover td {
        background: rgba(255, 255, 255, 0.03);
    }
    @media (prefers-color-scheme: dark) {
        .data-table tr:hover td {
            background: rgba(255, 255, 255, 0.01);
        }
    }

    .status-badge { 
        padding: 4px 10px; 
        border-radius: 20px; 
        font-size: 11px; 
        font-weight: 700; 
        display: inline-block;
    }
    .status-Active { 
        background: rgba(16, 185, 129, 0.15); 
        color: #10b981; 
    }
    .status-Terminated { 
        background: rgba(239, 68, 68, 0.15); 
        color: #ef4444; 
    }
    
    /* modal styling */
    .modal { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(8, 8, 16, 0.65); 
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 9999; 
        align-items: center; 
        justify-content: center; 
    }
    .modal-content { 
        background: var(--glass-bg); 
        border: 1px solid var(--glass-border);
        box-shadow: var(--glass-shadow);
        backdrop-filter: blur(32px); 
        -webkit-backdrop-filter: blur(32px);
        padding: 30px; 
        border-radius: 20px; 
        width: 100%;
        max-width: 600px; 
        color: var(--text-main);
        box-sizing: border-box;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label { 
        display: block; 
        margin-bottom: 6px; 
        font-size: 12.5px; 
        font-weight: 600; 
        color: var(--text-main);
        opacity: 0.85;
    }
    .form-control { 
        width: 100%; 
        padding: 10px 14px; 
        border: 1px solid var(--glass-border); 
        border-radius: 10px; 
        background: rgba(255, 255, 255, 0.08); 
        color: var(--text-main); 
        box-sizing: border-box;
        font-family: inherit;
        font-size: 13.5px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    @media (prefers-color-scheme: dark) {
        .form-control {
            background: rgba(0, 0, 0, 0.2);
        }
    }
    .form-control:focus {
        border-color: var(--text-accent);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    select.form-control option {
        background: var(--bg-color);
        color: var(--text-main);
    }
    .grid-2 { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 16px; 
    }
    @media (max-width: 580px) {
        .grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }
</style>

<div class="hrm-container">
    <div class="glass-card">
        <div class="header-actions">
            <div class="header-title-wrap">
                <h2><i class="ph ph-users-three" style="color: var(--text-accent);"></i> Employee Directory</h2>
                <p>Manage staff details, departments, roles, and base salary structures.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= APP_URL ?>/hrm/payroll" class="btn btn-outline"><i class="ph ph-bank"></i> Run Payroll</a>
                <button class="btn" onclick="document.getElementById('empModal').style.display='flex'"><i class="ph ph-plus-circle"></i> Add Employee</button>
            </div>
        </div>

        <?php if(!empty($data['error'])): ?>
            <div style="padding: 12px 16px; background: rgba(239, 68, 68, 0.12); color: #ef4444; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(239, 68, 68, 0.25);">
                <i class="ph ph-warning-circle" style="font-size: 16px;"></i>
                <?= $data['error'] ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($data['success'])): ?>
            <div style="padding: 12px 16px; background: rgba(16, 185, 129, 0.12); color: #10b981; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <i class="ph ph-check-circle" style="font-size: 16px;"></i>
                <?= $data['success'] ?>
            </div>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Job Title &amp; Dept</th>
                        <th>Contact</th>
                        <th>System Access</th>
                        <th>Status</th>
                        <th style="text-align: right;">Base Salary (Rs:)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['employees'])): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">No employees found.</td></tr>
                    <?php else: foreach($data['employees'] as $emp): ?>
                    <tr>
                        <td>
                            <strong style="color: var(--text-main);"><?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name) ?></strong>
                            <div style="font-size:11px; color: var(--text-muted); margin-top: 2px;">Hired: <?= date('d M Y', strtotime($emp->hire_date)) ?></div>
                        </td>
                        <td>
                            <span style="font-weight: 500;"><?= htmlspecialchars($emp->job_title) ?></span>
                            <div style="font-size:11px; color: var(--text-muted); margin-top: 2px;"><?= htmlspecialchars($emp->department ?: 'N/A') ?></div>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($emp->email ?: '—') ?></div>
                            <div style="font-size:11px; color: var(--text-muted); margin-top: 2px;"><?= htmlspecialchars($emp->phone ?: '—') ?></div>
                        </td>
                        <td>
                            <?php if(!empty($emp->username)): ?>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-weight:600; color: var(--text-accent); font-size:12.5px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="ph ph-shield-check" style="font-size: 15px;"></i> <?= htmlspecialchars($emp->username) ?>
                                    </span>
                                    <span style="font-size: 11px; color: var(--text-muted);">
                                        Role: <strong><?= htmlspecialchars(ucfirst($emp->user_role)) ?></strong>
                                    </span>
                                    <?php if(!empty($emp->accessible_apps)): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 3px; margin-top: 2px;">
                                            <?php foreach(explode(',', $emp->accessible_apps) as $app): ?>
                                                <span style="font-size: 9px; padding: 1px 5px; background: rgba(79, 70, 229, 0.08); color: var(--text-accent); border-radius: 4px; font-weight: 600; border: 0.5px solid rgba(79, 70, 229, 0.15); display: inline-block;">
                                                    <?= htmlspecialchars(trim($app)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="font-size:12px; color: var(--text-muted); font-style: italic;">No login created</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge status-<?= $emp->status ?>"><?= $emp->status ?></span></td>
                        <td style="text-align: right; font-weight: 700; color: var(--text-main);"><?= number_format($emp->base_salary, 2) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="empModal">
    <div class="modal-content">
        <h3 style="margin-top:0; margin-bottom: 20px; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="ph ph-user-plus" style="color: var(--text-accent);"></i> Add New Employee
        </h3>
        <form action="<?= APP_URL ?>/hrm" method="POST">
            <input type="hidden" name="action" value="add_employee">
            
            <div class="grid-2">
                <div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" required></div>
                <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
            </div>
            
            <div class="grid-2">
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
            </div>
 
            <div class="grid-2">
                <div class="form-group"><label>Department</label><input type="text" name="department" class="form-control" placeholder="e.g. Operations"></div>
                <div class="form-group">
                    <label>Job Title / Role *</label>
                    <select name="job_title" class="form-control" required>
                        <option value="Driver">Driver</option>
                        <option value="Rep">Rep</option>
                        <option value="Admin">Admin</option>
                        <option value="Accountant">Accountant</option>
                        <option value="Office">Office</option>
                    </select>
                </div>
            </div>
 
            <div class="grid-2">
                <div class="form-group"><label>Hire Date</label><input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                <div class="form-group"><label>Base Salary (Rs:) *</label><input type="number" name="base_salary" step="0.01" min="0" class="form-control" required></div>
            </div>

            <!-- Optional user login credentials -->
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--glass-border);">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13.5px; cursor: pointer; color: var(--text-main);">
                    <input type="checkbox" name="create_login" id="createLoginCheckbox" value="1" onchange="toggleLoginFields()">
                    <span>Create User Login Credentials</span>
                </label>
            </div>

            <div id="loginFieldsContainer" style="display: none; margin-top: 15px;">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" id="loginUsername" class="form-control" placeholder="e.g. johndoe">
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter secure password">
                    </div>
                </div>
                <div class="grid-2" style="grid-template-columns: 1fr 1fr; align-items: start;">
                    <div class="form-group">
                        <label>System Role *</label>
                        <select name="role" id="loginRole" class="form-control">
                            <option value="office">Office User</option>
                            <option value="rep">Representative (Rep)</option>
                            <option value="driver">Driver</option>
                            <option value="admin">Administrator (Admin)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="margin-bottom: 8px;">App Access Permissions *</label>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; cursor: pointer; color: var(--text-main);">
                                <input type="checkbox" name="accessible_apps[]" value="ERP System" checked> ERP System
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; cursor: pointer; color: var(--text-main);">
                                <input type="checkbox" name="accessible_apps[]" value="Driver App"> Driver App
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; cursor: pointer; color: var(--text-main);">
                                <input type="checkbox" name="accessible_apps[]" value="Rep App"> Rep App
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 500; cursor: pointer; color: var(--text-main);">
                                <input type="checkbox" name="accessible_apps[]" value="Curtiss Portal"> Curtiss Portal
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--glass-border);">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('empModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Save Employee</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleLoginFields() {
    const checked = document.getElementById('createLoginCheckbox').checked;
    const container = document.getElementById('loginFieldsContainer');
    container.style.display = checked ? 'block' : 'none';
    
    document.getElementById('loginUsername').required = checked;
    document.getElementById('loginPassword').required = checked;
    document.getElementById('loginRole').required = checked;
}
</script>