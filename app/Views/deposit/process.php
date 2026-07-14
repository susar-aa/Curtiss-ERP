<?php
$dep = $data['deposit'];
$isSent = $dep->status === 'Sent to Bank';
$isCompleted = $dep->status === 'Completed';
?>
<style>
    .process-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 20px;
        transition: color 0.15s;
    }
    .back-link:hover { color: #0066cc; }

    .header-card {
        background: #fff;
        border: 1px solid var(--mac-border, #e2e8f0);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    @media (prefers-color-scheme: dark) {
        .header-card { background: #1e1e2d; border-color: #2d2d3d; }
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .detail-item { display: flex; flex-direction: column; gap: 4px; }
    .detail-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; }
    .detail-val { font-size: 14px; font-weight: 600; }

    .status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        text-transform: uppercase;
    }
    .status-Sent { background: #e0f2fe; color: #0369a1; }
    .status-Completed { background: #dcfce7; color: #15803d; }
    @media (prefers-color-scheme: dark) {
        .status-Sent { background: rgba(3, 105, 161, 0.15); color: #38bdf8; }
        .status-Completed { background: rgba(21, 128, 61, 0.15); color: #4ade80; }
    }

    .section-card {
        background: #fff;
        border: 1px solid var(--mac-border, #e2e8f0);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    @media (prefers-color-scheme: dark) {
        .section-card { background: #1e1e2d; border-color: #2d2d3d; }
    }

    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark, #1e293b);
        border-bottom: 1px solid var(--mac-border, #f1f5f9);
        padding-bottom: 12px;
        margin: 0 0 20px 0;
    }
    @media (prefers-color-scheme: dark) {
        .card-title { color: #f1f5f9; border-color: #27272a; }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
    }
    .form-group input, .form-group textarea, .form-group select {
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        background: transparent;
        color: inherit;
        box-sizing: border-box;
    }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
        border-color: #0066cc;
        outline: none;
    }
    @media (prefers-color-scheme: dark) {
        .form-group input, .form-group textarea, .form-group select { border-color: #3f3f46; }
    }

    .item-table { width: 100%; border-collapse: collapse; text-align: left; }
    .item-table th, .item-table td { padding: 12px 16px; border-bottom: 1px solid var(--mac-border, #e2e8f0); font-size: 13px; }
    @media (prefers-color-scheme: dark) { .item-table th, .item-table td { border-color: #27272a; } }
    .item-table th { background: rgba(0,0,0,0.02); font-size: 11px; font-weight: bold; text-transform: uppercase; color: #64748b; }
    @media (prefers-color-scheme: dark) { .item-table th { background: rgba(255,255,255,0.02); } }

    .action-select { padding: 5px 8px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 12px; font-weight: 600; }
    @media (prefers-color-scheme: dark) { .action-select { border-color: #3f3f46; background: #181825; } }

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
    .btn-success { background: #2e7d32; color: #fff; }
    .btn-success:hover { background: #235f26; }
    .btn-secondary { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
    .btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    @media (prefers-color-scheme: dark) {
        .btn-secondary { background: #1e1e2d; color: #e2e8f0; border-color: #3f3f46; }
        .btn-secondary:hover { background: #27273a; border-color: #52525b; }
    }
</style>

<div class="process-container">
    <a href="<?= APP_URL ?>/deposit" class="back-link">⬅️ Back to Deposits</a>

    <div class="header-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 20px;">Deposit: #<?= htmlspecialchars($dep->deposit_number) ?></h2>
            <span class="status-badge status-<?= str_replace(' ', '', $dep->status) ?>"><?= htmlspecialchars($dep->status) ?></span>
        </div>
        
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Deposit Date</span>
                <span class="detail-val"><?= date('Y-m-d', strtotime($dep->deposit_date)) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Destination Bank Account</span>
                <span class="detail-val"><?= htmlspecialchars($dep->bank_name) ?> (<?= htmlspecialchars($dep->bank_code) ?>)</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Prepared By</span>
                <span class="detail-val"><?= htmlspecialchars($dep->prepared_by_name) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Grand Total Sent</span>
                <span class="detail-val" style="color: #2e7d32; font-family: monospace;">Rs: <?= number_format($dep->total_deposit, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Audit Journal Entries Link if Completed -->
    <?php if ($isCompleted): ?>
        <div class="section-card" style="border-left: 5px solid #2e7d32; background: rgba(46, 125, 50, 0.02);">
            <h3 class="card-title" style="color: #2e7d32;">🔗 Audit Trail & GL Ledger Links</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <div>
                    <strong>1. Transit Entry Reference:</strong> 
                    <span style="font-family: monospace; font-weight: 700; color: #0066cc;"># <?= htmlspecialchars($dep->deposit_number) ?></span>
                    <span style="color:#64748b;">(Posted when Sent to Bank. Moved funds to Deposit in Transit accounts)</span>
                </div>
                <?php if (!empty($dep->realization_journal_entry_id)): 
                    // Let's resolve the reference
                    $db = new Database();
                    $db->query("SELECT reference FROM journal_entries WHERE id = :jid");
                    $db->bind(':jid', $dep->realization_journal_entry_id);
                    $rjeRow = $db->single();
                    $rjeRef = $rjeRow ? $rjeRow->reference : '';
                ?>
                    <div>
                        <strong>2. Realization Entry Reference:</strong>
                        <span style="font-family: monospace; font-weight: 700; color: #2e7d32;"># <?= htmlspecialchars($rjeRef) ?></span>
                        <span style="color:#64748b;">(Posted when Approved. Moved cleared funds from transit to Destination Bank Account)</span>
                    </div>
                <?php endif; ?>
                <div style="font-size: 11px; color: #ef6c00; margin-top: 5px;">
                    ℹ️ <em>If any cheques were returned or rejected, their individual reversal journal entries are prefixed with <strong>REV-RC-</strong> and can be verified in the general ledger audit logs.</em>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/deposit/process/<?= $dep->id ?>" method="POST">
        <!-- CRITICAL: CSRF Token injection -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <!-- Cash Details -->
        <?php if ($dep->cash_total > 0): ?>
            <div class="section-card">
                <h3 class="card-title">💵 Cash Processing</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div style="font-size: 13px; line-height: 1.6;">
                        <strong>Cash Denominations Breakdowns:</strong>
                        <table style="width: 100%; margin-top: 10px; font-family: monospace;">
                            <?php 
                            $denoms = [5000, 2000, 1000, 500, 100, 50, 20];
                            foreach ($denoms as $d):
                                $field = "cash_" . $d;
                                if (intval($dep->$field) > 0):
                            ?>
                                <tr>
                                    <td>Rs: <?= $d ?> ✕ <?= $dep->$field ?></td>
                                    <td style="text-align: right;">Rs: <?= number_format($d * $dep->$field, 2) ?></td>
                                </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                            <tr style="font-weight: bold; border-top: 1px solid #cbd5e1;">
                                <td style="padding-top: 5px;">Total Cash Sent:</td>
                                <td style="text-align: right; padding-top: 5px;">Rs: <?= number_format($dep->cash_total, 2) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <div class="form-group">
                            <label>Accepted Cash Amount (Rs:) *</label>
                            <?php if ($isSent): ?>
                                <input type="number" name="accepted_cash_amount" step="0.01" min="0.00" max="<?= $dep->cash_total ?>" value="<?= htmlspecialchars($dep->cash_total) ?>" required style="font-size: 15px; font-weight:700; width: 220px; color:#2e7d32;">
                                <span style="font-size: 11px; color:#64748b; margin-top: 4px;">Prefilled with total cash sent. Change if there is a discrepancy with the bank statement deposit confirmation.</span>
                            <?php else: ?>
                                <div style="font-size: 16px; font-weight:700; font-family: monospace; color:#2e7d32; padding: 10px 0;">
                                    Rs: <?= number_format($dep->accepted_cash_amount, 2) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cheques Details -->
        <?php 
        $hasCheques = false;
        foreach ($data['items'] as $item) {
            if ($item->cheque_id !== null) { $hasCheques = true; break; }
        }
        if ($hasCheques):
        ?>
            <div class="section-card">
                <h3 class="card-title">✍️ Cheque Clearing Details</h3>
                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Cheque Detail</th>
                            <th>Customer</th>
                            <th style="text-align: right;">Amount</th>
                            <th style="text-align: center; width: 180px;">Action Resolution</th>
                            <th>Reason (For Return/Reject)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['items'] as $item): 
                            if ($item->cheque_id === null) continue;
                        ?>
                            <tr>
                                <td>
                                    <strong>No: <?= htmlspecialchars($item->cheque_number) ?></strong><br>
                                    <span style="font-size: 11px; color:#64748b;"><?= htmlspecialchars($item->bank_name) ?> | <?= date('Y-m-d', strtotime($item->banking_date)) ?></span>
                                </td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($item->customer_name) ?></td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace;">Rs: <?= number_format($item->cheque_amount, 2) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($isSent): ?>
                                        <select name="cheque_action[<?= $item->cheque_id ?>]" class="action-select" onchange="toggleReason(<?= $item->cheque_id ?>, this.value)">
                                            <option value="Clear" style="color: #2e7d32; font-weight: bold;">✔️ Clear / Pass</option>
                                            <option value="Return" style="color: #ef6c00; font-weight: bold;">❌ Return</option>
                                            <option value="Reject" style="color: #c62828; font-weight: bold;">⚠️ Reject</option>
                                        </select>
                                    <?php else: ?>
                                        <?php 
                                        $badgeColor = '#64748b';
                                        if ($item->status === 'Passed') $badgeColor = '#2e7d32';
                                        elseif ($item->status === 'Returned') $badgeColor = '#ef6c00';
                                        elseif ($item->status === 'Rejected') $badgeColor = '#c62828';
                                        ?>
                                        <span style="font-weight: 800; color: <?= $badgeColor ?>; text-transform: uppercase; font-size: 11px;">
                                            <?= htmlspecialchars($item->status === 'Passed' ? 'Cleared' : $item->status) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isSent): ?>
                                        <input type="text" name="rejection_reason[<?= $item->cheque_id ?>]" id="reason_<?= $item->cheque_id ?>" placeholder="Enter return reason..." style="display: none; width: 100%; font-size:12px; padding: 4px 8px;">
                                    <?php else: ?>
                                        <span style="color: #64748b; font-size: 12px; font-style: italic;"><?= htmlspecialchars($item->rejection_reason ?: '-') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Remarks Section -->
        <div class="section-card">
            <h3 class="card-title">📝 Remarks / Audit Notes</h3>
            <div class="form-group">
                <?php if ($isSent): ?>
                    <textarea name="approval_remarks" rows="3" placeholder="Enter any notes or remarks from the bank deposit confirmation/statement..."></textarea>
                <?php else: ?>
                    <div style="font-size: 13px; line-height: 1.5; font-style: italic; color:#475569;">
                        <?= nl2br(htmlspecialchars($dep->approval_remarks ?: 'No remarks provided.')) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Buttons Section -->
        <div style="display: flex; gap: 12px; margin-bottom: 50px;">
            <a href="<?= APP_URL ?>/deposit" class="btn btn-secondary">Back</a>
            <a href="<?= APP_URL ?>/deposit/printSlip/<?= $dep->id ?>" target="_blank" class="btn btn-primary" style="background:#0066cc; color:#fff;">🖨️ Print Slip</a>
            <?php if ($isSent): ?>
                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to finalize this deposit processing? Cleared items will post to the bank account ledger; returned cheques will be reversed.')">✔️ Complete Processing</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    function toggleReason(chequeId, val) {
        const input = document.getElementById('reason_' + chequeId);
        if (input) {
            if (val === 'Return' || val === 'Reject') {
                input.style.display = 'block';
                input.required = true;
            } else {
                input.style.display = 'none';
                input.required = false;
            }
        }
    }
</script>
