<style>
    .deposit-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .btn {
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .btn-primary { background: #0066cc; color: #fff; }
    .btn-primary:hover { background: #0056b3; }
    .btn-secondary { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
    .btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    .btn-success { background: #2e7d32; color: #fff; }
    .btn-success:hover { background: #235f26; }
    .btn-danger { background: #c62828; color: #fff; }
    .btn-danger:hover { background: #9a1f1f; }
    
    @media (prefers-color-scheme: dark) {
        .btn-secondary { background: #1e1e2d; color: #e2e8f0; border-color: #3f3f46; }
        .btn-secondary:hover { background: #27273a; border-color: #52525b; }
    }

    .table-card {
        background: #fff;
        border: 1px solid var(--mac-border, #e2e8f0);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-top: 15px;
    }
    @media (prefers-color-scheme: dark) {
        .table-card { background: #1e1e2d; border-color: #2d2d3d; }
    }

    .deposit-table { width: 100%; border-collapse: collapse; text-align: left; }
    .deposit-table th, .deposit-table td { padding: 14px 20px; border-bottom: 1px solid var(--mac-border, #e2e8f0); }
    @media (prefers-color-scheme: dark) { .deposit-table th, .deposit-table td { border-color: #27272a; } }
    .deposit-table th { background: rgba(0,0,0,0.01); font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; }
    @media (prefers-color-scheme: dark) { .deposit-table th { background: rgba(255,255,255,0.01); } }
    .deposit-table tr:last-child td { border-bottom: none; }
    .deposit-table tr:hover td { background: rgba(0,0,0,0.01); }
    @media (prefers-color-scheme: dark) { .deposit-table tr:hover td { background: rgba(255,255,255,0.01); } }

    .status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        text-transform: uppercase;
    }
    .status-Draft { background: #f1f5f9; color: #475569; }
    .status-Sent { background: #e0f2fe; color: #0369a1; } /* Sent to Bank */
    .status-Approved { background: #dcfce7; color: #15803d; }
    .status-Completed { background: #dcfce7; color: #15803d; }
    
    @media (prefers-color-scheme: dark) {
        .status-Draft { background: #334155; color: #cbd5e1; }
        .status-Sent { background: rgba(3, 105, 161, 0.15); color: #38bdf8; }
        .status-Approved { background: rgba(21, 128, 61, 0.15); color: #4ade80; }
        .status-Completed { background: rgba(21, 128, 61, 0.15); color: #4ade80; }
    }
</style>

<div class="deposit-container">
    <div class="header-actions">
        <div>
            <h2 style="margin: 0 0 5px 0;">Bank Deposits Workflow</h2>
            <p style="margin: 0; color: #64748b; font-size: 14px;">Prepare, audit, and process cash and cheque deposits from collections into bank accounts.</p>
        </div>
        <div>
            <a href="<?= APP_URL ?>/deposit/create" class="btn btn-primary">➕ New Bank Deposit</a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if(!empty($data['success'])): ?>
        <div style="padding: 12px 16px; background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px;">
            <span>✅</span> <?= htmlspecialchars($data['success']) ?>
        </div>
    <?php endif; ?>
    <?php if(!empty($data['error'])): ?>
        <div style="padding: 12px 16px; background:#ffebee; color:#c62828; border:1px solid #ffcdd2; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px;">
            <span>⚠️</span> <?= htmlspecialchars($data['error']) ?>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <table class="deposit-table">
            <thead>
                <tr>
                    <th>Deposit Number</th>
                    <th>Date</th>
                    <th>Destination Bank</th>
                    <th style="text-align: right;">Cash Total</th>
                    <th style="text-align: right;">Cheques Total</th>
                    <th style="text-align: right;">Grand Total</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center; width: 250px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['deposits'])): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 40px;">
                            <span style="font-size: 32px; display: block; margin-bottom: 10px;">📥</span>
                            No deposits recorded yet. Click "New Bank Deposit" to start.
                        </td>
                    </tr>
                <?php else: foreach($data['deposits'] as $dep): ?>
                    <tr>
                        <td style="font-weight: 700; color: #0066cc;"># <?= htmlspecialchars($dep->deposit_number) ?></td>
                        <td><?= date('Y-m-d', strtotime($dep->deposit_date)) ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($dep->bank_name) ?></td>
                        <td style="text-align: right; font-family: monospace; font-size: 13px;">Rs <?= number_format($dep->cash_total, 2) ?></td>
                        <td style="text-align: right; font-family: monospace; font-size: 13px;">Rs <?= number_format($dep->cheque_total, 2) ?></td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace; font-size: 13px; color: #2e7d32;">Rs <?= number_format($dep->total_deposit, 2) ?></td>
                        <td style="text-align: center;">
                            <span class="status-badge status-<?= str_replace(' ', '', $dep->status) ?>">
                                <?= htmlspecialchars($dep->status) ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <?php if ($dep->status === 'Draft'): ?>
                                    <a href="<?= APP_URL ?>/deposit/edit/<?= $dep->id ?>" class="btn btn-secondary" style="padding: 5px 10px; font-size: 11px;">✏️ Edit</a>
                                    <a href="<?= APP_URL ?>/deposit/send/<?= $dep->id ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 11px;" onclick="return confirm('Are you sure you want to send this deposit to the bank? This will debit Deposit in Transit and credit Cash/Cheque in Hand.')">📤 Send to Bank</a>
                                    <a href="<?= APP_URL ?>/deposit/delete/<?= $dep->id ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 11px; background:#c62828;" onclick="return confirm('Are you sure you want to delete this draft?')">🗑️</a>
                                <?php elseif ($dep->status === 'Sent to Bank'): ?>
                                    <a href="<?= APP_URL ?>/deposit/process/<?= $dep->id ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 11px; background:#ef6c00; border-color:#ef6c00;">⚙️ Process</a>
                                    <a href="<?= APP_URL ?>/deposit/printSlip/<?= $dep->id ?>" target="_blank" class="btn btn-secondary" style="padding: 5px 10px; font-size: 11px;">🖨️</a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/deposit/process/<?= $dep->id ?>" class="btn btn-secondary" style="padding: 5px 10px; font-size: 11px;">🔍 View Details</a>
                                    <a href="<?= APP_URL ?>/deposit/printSlip/<?= $dep->id ?>" target="_blank" class="btn btn-secondary" style="padding: 5px 10px; font-size: 11px;">🖨️</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
