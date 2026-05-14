<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn:hover { background: #005bb5; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); }
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .status-Draft { background: #f5f5f5; color: #666; }
    .status-Sent { background: #e3f2fd; color: #1565c0; }
    .status-Received { background: #e8f5e9; color: #2e7d32; }
</style>

<div class="card">
    <div class="header-actions">
        <h2>Purchase Orders (Procurement)</h2>
        <a href="<?= APP_URL ?>/purchase/create" class="btn">+ Create Purchase Order</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;">Purchase Order generated successfully!</div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Date</th>
                <th>Vendor</th>
                <th>Status</th>
                <th style="text-align: right;">Amount (Rs:)</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['pos'])): ?>
            <tr><td colspan="6" style="text-align: center; color: #888;">No Purchase Orders found.</td></tr>
            <?php else: foreach($data['pos'] as $po): ?>
            <tr>
                <td><strong><?= htmlspecialchars($po->po_number) ?></strong></td>
                <td><?= date('M d, Y', strtotime($po->po_date)) ?></td>
                <td><?= htmlspecialchars($po->vendor_name) ?></td>
                <td><span class="status-badge status-<?= $po->status ?>"><?= $po->status ?></span></td>
                <td style="text-align: right; font-weight:bold;"><?= number_format($po->total_amount, 2) ?></td>
                <td style="text-align: center;">
                    <a href="<?= APP_URL ?>/purchase/show/<?= $po->id ?>" class="btn" style="padding: 4px 8px; font-size: 11px;">View / Print</a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>