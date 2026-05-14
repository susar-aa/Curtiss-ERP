<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn:hover { background: #005bb5; }
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); }
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    
    .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .role-Admin { background: #ffebee; color: #c62828; }
    .role-Accountant { background: #e3f2fd; color: #1565c0; }
    .role-Manager { background: #f3e5f5; color: #6a1b9a; }
    .role-Employee { background: #f5f5f5; color: #666; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 400px; border: 1px solid var(--mac-border); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="card">
    <div class="header-actions">
        <h2>System Users & Access Control</h2>
        <button class="btn" onclick="document.getElementById('userModal').style.display='flex'">+ Create User Account</button>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email Address</th>
                <th>System Role</th>
                <th>Account Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['users'] as $u): ?>
            <tr>
                <td><strong><?= htmlspecialchars($u->username) ?></strong></td>
                <td><?= htmlspecialchars($u->email) ?></td>
                <td><span class="role-badge role-<?= $u->role ?>"><?= $u->role ?></span></td>
                <td style="font-size: 13px; color: #666;"><?= date('M d, Y', strtotime($u->created_at)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create User Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <h3 style="margin-top:0;">Create ERP Login</h3>
        <form action="<?= APP_URL ?>/user" method="POST">
            <input type="hidden" name="action" value="add_user">
            
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>System Role / Permissions *</label>
                <select name="role" class="form-control" required>
                    <option value="Employee">Employee (Basic Access)</option>
                    <option value="Manager">Manager (Approvals & Sales)</option>
                    <option value="Accountant">Accountant (Full Ledger Access)</option>
                    <option value="Admin">Admin (Full System Control)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Temporary Password *</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('userModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Create Account</button>
            </div>
        </form>
    </div>
</div>