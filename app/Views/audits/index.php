<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.02);}
    @media (prefers-color-scheme: dark) { .data-table { background: #1e1e2d; } }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px; }
    .data-table th { background-color: rgba(0,0,0,0.03); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; color: #666; }
    
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; letter-spacing: 0.5px; }
    .action-CREATE { background: #e8f5e9; color: #2e7d32; }
    .action-UPDATE { background: #fff3e0; color: #ef6c00; }
    .action-DELETE { background: #ffebee; color: #c62828; }
    .action-LOGIN { background: #e3f2fd; color: #1565c0; }
    
    .module-badge { font-weight: 600; color: #555; }
</style>

<div class="card" style="background: transparent; box-shadow: none; padding: 0;">
    <div class="header-actions">
        <div>
            <h2 style="margin: 0 0 5px 0; color: #c62828;">System Audit Trail</h2>
            <p style="margin: 0; color: #666; font-size: 14px;">Immutable, read-only ledger of all user activities.</p>
        </div>
        <div style="font-size: 12px; color: #888;">Showing last 250 events</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 150px;">Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Module</th>
                <th style="width: 40%;">Description</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['logs'])): ?>
                <tr><td colspan="6" style="text-align: center; color: #888; padding: 30px;">No audit logs found.</td></tr>
            <?php else: foreach($data['logs'] as $log): ?>
                <tr>
                    <td style="color: #888;"><?= date('M d, Y H:i:s', strtotime($log->created_at)) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($log->username ?? 'System') ?></strong><br>
                        <span style="font-size: 10px; color: #888;"><?= htmlspecialchars($log->role ?? 'N/A') ?></span>
                    </td>
                    <td><span class="badge action-<?= strtoupper(htmlspecialchars($log->action)) ?>"><?= htmlspecialchars($log->action) ?></span></td>
                    <td class="module-badge"><?= htmlspecialchars($log->module) ?></td>
                    <td><?= htmlspecialchars($log->description) ?></td>
                    <td style="font-size: 11px; color: #888; font-family: monospace;"><?= htmlspecialchars($log->ip_address) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>