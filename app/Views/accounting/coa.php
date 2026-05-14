<?php
?>
<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .btn {
        padding: 8px 16px;
        background: #0066cc;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    .btn:hover { background: #005bb5; }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--mac-border);
    }
    th {
        background-color: rgba(0,0,0,0.02);
        font-weight: 600;
        font-size: 13px;
        color: #555;
    }
    @media (prefers-color-scheme: dark) {
        th { background-color: rgba(255,255,255,0.05); color: #ccc; }
    }
    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .type-Asset { background: #e3f2fd; color: #1565c0; }
    .type-Liability { background: #ffebee; color: #c62828; }
    .type-Equity { background: #f3e5f5; color: #6a1b9a; }
    .type-Revenue { background: #e8f5e9; color: #2e7d32; }
    .type-Expense { background: #fff3e0; color: #ef6c00; }

    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    .form-group { flex: 1; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--mac-border);
        border-radius: 4px;
        background: transparent;
        color: var(--text-main);
        box-sizing: border-box;
    }
    .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
</style>

<div class="card">
    <?php ?>
    <div class="header-actions">
        <h2>Chart of Accounts</h2>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-error"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['success'])): ?>
        <div class="alert alert-success"><?= $data['success'] ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/accounting/coa" method="POST" style="background: rgba(0,0,0,0.02); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--mac-border);">
        <h4 style="margin-top: 0; margin-bottom: 15px;">Add New Ledger Account</h4>
        <div class="form-row">
            <div class="form-group">
                <label>Account Code (e.g., 1001)</label>
                <input type="text" name="account_code" class="form-control" required>
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Account Name (e.g., Cash in Bank)</label>
                <input type="text" name="account_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="account_type" class="form-control" required>
                    <option value="Asset">Asset</option>
                    <option value="Liability">Liability</option>
                    <option value="Equity">Equity</option>
                    <option value="Revenue">Revenue</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <div class="form-group" style="flex: 0.5; display: flex; align-items: flex-end;">
                <button type="submit" class="btn" style="width: 100%;">Save Account</button>
            </div>
        </div>
    </form>

    <?php ?>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th style="text-align: right;">Current Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['accounts'])): ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #888;">No accounts found. Start by adding one above.</td>
            </tr>
            <?php else: ?>
                <?php foreach($data['accounts'] as $acc): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($acc->account_code) ?></strong></td>
                    <td><?= htmlspecialchars($acc->account_name) ?></td>
                    <td><span class="badge type-<?= $acc->account_type ?>"><?= $acc->account_type ?></span></td>
                    <td style="text-align: right;">Rs: <?= number_format($acc->balance, 2) ?></td>
                    <td>
                        <?php if($acc->is_active): ?>
                            <span style="color: #2e7d32; font-size: 12px;">● Active</span>
                        <?php else: ?>
                            <span style="color: #c62828; font-size: 12px;">● Inactive</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>