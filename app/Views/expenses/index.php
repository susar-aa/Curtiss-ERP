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
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 400px; border: 1px solid var(--mac-border); }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; margin-bottom: 15px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="card">
    <div class="header-actions">
        <h2>Expenses & Accounts Payable</h2>
        <div>
            <button class="btn btn-outline" onclick="document.getElementById('vendorModal').style.display='flex'">+ New Vendor</button>
            <a href="<?= APP_URL ?>/expense/create" class="btn">+ Record Expense</a>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;">Expense recorded and posted to ledger successfully!</div>
    <?php endif; ?>
    <?php if(!empty($data['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Vendor</th>
                <th>Description</th>
                <th style="text-align: right;">Amount (Rs:)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['expenses'])): ?>
            <tr><td colspan="5" style="text-align: center; color: #888;">No expenses found.</td></tr>
            <?php else: foreach($data['expenses'] as $exp): ?>
            <tr>
                <td><?= date('M d, Y', strtotime($exp->expense_date)) ?></td>
                <td><strong><?= htmlspecialchars($exp->reference) ?></strong></td>
                <td><?= htmlspecialchars($exp->vendor_name ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($exp->description) ?></td>
                <td style="text-align: right; font-weight:bold;">Rs: <?= number_format($exp->amount, 2) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Vendor Modal -->
<div class="modal" id="vendorModal">
    <div class="modal-content">
        <h3 style="margin-top:0;">Add New Vendor</h3>
        <form action="<?= APP_URL ?>/expense" method="POST">
            <input type="hidden" name="action" value="add_vendor">
            <label style="font-size: 13px; font-weight:600;">Vendor / Company Name *</label>
            <input type="text" name="name" class="form-control" required>
            
            <label style="font-size: 13px; font-weight:600;">Email</label>
            <input type="email" name="email" class="form-control">
            
            <label style="font-size: 13px; font-weight:600;">Phone</label>
            <input type="text" name="phone" class="form-control">
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('vendorModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Save Vendor</button>
            </div>
        </form>
    </div>
</div>