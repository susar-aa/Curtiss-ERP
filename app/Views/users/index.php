<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;}
    .btn:hover { background: #005bb5; }
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-outline:hover { background: rgba(0,102,204,0.05); }
    .btn-danger { background: #ff3b30; }
    .btn-danger:hover { background: #e0241b; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); }
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    
    .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .role-Admin, .role-admin { background: #ffebee; color: #c62828; }
    .role-Accountant, .role-accountant { background: #e3f2fd; color: #1565c0; }
    .role-Manager, .role-manager { background: #f3e5f5; color: #6a1b9a; }
    .role-Employee, .role-employee { background: #f5f5f5; color: #666; }
    .role-Driver, .role-driver { background: #e8f5e9; color: #2e7d32; }
    .role-Rep, .role-rep { background: #fff3e0; color: #e65100; }
    .role-Office, .role-office { background: #f3e5f5; color: #6a1b9a; }

    .action-links { display: flex; gap: 8px; }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div style="padding: 12px 16px; background:#e8f5e9; color:#2e7d32; border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
        <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
        <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div style="padding: 12px 16px; background:#ffebee; color:#c62828; border-radius:8px; margin-bottom:20px; font-weight:500; display:flex; align-items:center; gap:8px;">
        <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
        <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="header-actions">
        <div>
            <h2 style="margin:0 0 5px 0; font-weight: 700;">System Users & Access Control</h2>
            <p style="margin:0; font-size:13px; color:var(--text-muted);">Manage login credentials and granular modular permissions for employees.</p>
        </div>
        <a href="<?= APP_URL ?>/user/create" class="btn">
            <i class="ph-bold ph-user-plus"></i> + Create User Account
        </a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email Address</th>
                <th>Linked Employee</th>
                <th>System Role</th>
                <th style="text-align: center;">Signature</th>
                <th>Permissions Status</th>
                <th>Status</th>
                <th style="width: 140px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['users'] as $u): ?>
            <tr>
                <td><strong><?= htmlspecialchars($u->username) ?></strong></td>
                <td><?= htmlspecialchars($u->email) ?></td>
                <td>
                    <?php if(!empty($u->first_name)): ?>
                        <strong style="color: #0066cc;">👤 <?= htmlspecialchars($u->first_name . ' ' . $u->last_name) ?></strong>
                    <?php else: ?>
                        <span style="color: #888; font-style: italic;">None</span>
                    <?php endif; ?>
                </td>
                <td><span class="role-badge role-<?= $u->role ?>"><?= $u->role ?></span></td>
                <td style="text-align: center;">
                    <?php if(!empty($u->signature_path)): ?>
                        <span style="color: #2e7d32; font-size: 12px; font-weight: bold;"><i class="ph-bold ph-check"></i> Uploaded</span>
                    <?php else: ?>
                        <span style="color: #888; font-size: 12px;">None</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (strtolower($u->role) === 'admin'): ?>
                        <span style="color: #c62828; font-size: 12px; font-weight: bold;"><i class="ph-bold ph-shield"></i> Full Access (Admin)</span>
                    <?php else: ?>
                        <span style="color: #0066cc; font-size: 12px; font-weight: bold;">
                            <i class="ph-bold ph-key"></i> <?= count($u->permissions) ?> Module(s) Custom
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (strtolower($u->status ?? 'active') === 'active'): ?>
                        <span style="color: #2e7d32; font-size: 12px; font-weight: bold;"><i class="ph-bold ph-check-circle"></i> Active</span>
                    <?php else: ?>
                        <span style="color: #ff3b30; font-size: 12px; font-weight: bold;"><i class="ph-bold ph-x-circle"></i> Blocked</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: right;">
                    <div class="action-links" style="justify-content: flex-end;">
                        <a href="<?= APP_URL ?>/user/edit/<?= $u->id ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px;">
                            <i class="ph-bold ph-pencil"></i> Edit
                        </a>
                        <?php if ($u->id != $_SESSION['user_id']): ?>
                            <a href="<?= APP_URL ?>/user/delete/<?= $u->id ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Are you sure you want to delete this user?');">
                                <i class="ph-bold ph-trash"></i> Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>