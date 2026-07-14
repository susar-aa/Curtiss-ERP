<?php
// Petty Cash Main View
?>
<style>
    .petty-cash-wrap {
        display: flex;
        flex-direction: column;
        gap: 24px;
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 50px;
    }

    /* Grid layout for stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.blue { background: rgba(79, 70, 229, 0.1); color: var(--text-accent); }
    .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .stat-icon.orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .stat-icon.red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
    }

    .stat-subtext {
        font-size: 11px;
        color: var(--text-muted);
    }

    /* Tabs */
    .tabs-nav {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--mega-divider);
        padding-bottom: 1px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .tabs-nav::-webkit-scrollbar {
        display: none;
    }

    .tab-btn {
        background: transparent;
        border: none;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tab-btn:hover {
        color: var(--text-accent);
    }

    .tab-btn.active {
        color: var(--text-accent);
        border-bottom-color: var(--text-accent);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.25s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Layout divisions */
    .split-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .split-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Elegant forms */
    .form-box {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
    }

    .form-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 18px;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid var(--mega-divider);
        padding-bottom: 10px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    @media (max-width: 600px) {
        .form-group.full-width {
            grid-column: span 1;
        }
    }

    .form-group label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .form-control {
        padding: 10px 14px;
        border: 1px solid var(--card-border);
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.02);
        color: var(--text-main);
        font-size: 13.5px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    @media (prefers-color-scheme: dark) {
        .form-control {
            background: rgba(255, 255, 255, 0.03);
        }
    }

    .form-control:focus {
        border-color: var(--text-accent);
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
    }

    .form-control::placeholder {
        color: var(--text-muted);
        opacity: 0.7;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, transform 0.1s;
    }

    .btn:active {
        transform: scale(0.98);
    }

    .btn-primary {
        background: var(--text-accent);
        color: #fff;
    }

    .btn-primary:hover {
        background: #4338ca;
    }

    .btn-secondary {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text-main);
        border-color: var(--card-border);
    }

    @media (prefers-color-scheme: dark) {
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
        }
    }

    .btn-secondary:hover {
        background: rgba(0, 0, 0, 0.08);
    }

    .btn-success {
        background: #10b981;
        color: #fff;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-danger {
        background: #ef4444;
        color: #fff;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    /* Modern Tables */
    .table-container {
        overflow-x: auto;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
    }

    .pc-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .pc-table th, .pc-table td {
        padding: 12px 18px;
        font-size: 13px;
        border-bottom: 1px solid var(--mega-divider);
    }

    .pc-table th {
        background: rgba(0, 0, 0, 0.01);
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .pc-table tbody tr:last-child td {
        border-bottom: none;
    }

    .pc-table tbody tr:hover {
        background: rgba(79, 70, 229, 0.02);
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .badge-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .badge-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .badge-reimbursed { background: rgba(79, 70, 229, 0.1); color: var(--text-accent); }

    /* Alert classes */
    .alert {
        padding: 14px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13.5px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.08);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.08);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .audit-timeline {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 15px;
    }

    .audit-item {
        position: relative;
        padding-left: 20px;
        border-left: 2px solid var(--mega-divider);
        font-size: 12.5px;
    }

    .audit-item::before {
        content: '';
        position: absolute;
        left: -5px;
        top: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--text-accent);
    }

    .audit-meta {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .audit-desc {
        color: var(--text-main);
    }

    /* Modal / Popups */
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 5000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.open {
        display: flex;
    }

    .modal-content {
        background: var(--mega-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        animation: modalSlide 0.2s ease;
        overflow: hidden;
    }

    @keyframes modalSlide {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--mega-divider);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header h3 {
        font-size: 16px;
        font-weight: 700;
    }

    .modal-close {
        background: transparent;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: var(--text-muted);
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 12px 20px;
        border-top: 1px solid var(--mega-divider);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: rgba(0,0,0,0.01);
    }
</style>

<div class="petty-cash-wrap">
    <!-- Header Title with Ledger Action -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">Petty Cash Control</h1>
            <p style="font-size: 13px; color: var(--text-muted);">Manage cash lifecycle, approve claims, and restore ledger integrity.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= APP_URL ?>/pettycash/ledger" class="btn btn-secondary">
                <i class="ph ph-list"></i> Transaction Ledger
            </a>
            <button class="btn btn-primary" onclick="openModal('expenseModal')">
                <i class="ph ph-plus"></i> Record Expense
            </button>
        </div>
    </div>

    <!-- Alert Messaging -->
    <?php if (!empty($data['error'])): ?>
        <div class="alert alert-error">
            <i class="ph ph-warning-circle" style="font-size: 20px;"></i>
            <span><?= htmlspecialchars($data['error']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['success'])): ?>
        <div class="alert alert-success">
            <i class="ph ph-check-circle" style="font-size: 20px;"></i>
            <span><?= htmlspecialchars($data['success']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Dashboard Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="ph ph-wallet"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Petty Cash Balance</span>
                <span class="stat-value">Rs. <?= number_format($data['summary']['current_balance'], 2) ?></span>
                <span class="stat-subtext">Ledger Balance (1020)</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="ph ph-shield-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Allocated Fund Limit</span>
                <span class="stat-value">Rs. <?= number_format($data['summary']['limit_amount'], 2) ?></span>
                <span class="stat-subtext">Custodian: <?= htmlspecialchars($data['config']->custodian_name ?? 'Not Assigned') ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="ph ph-trend-up"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Available Balance</span>
                <span class="stat-value">Rs. <?= number_format($data['summary']['available_balance'], 2) ?></span>
                <span class="stat-subtext">Limit less unpaid claims</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="ph ph-clock-clockwise"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Pending Claims</span>
                <span class="stat-value">Rs. <?= number_format($data['summary']['pending_approvals_amount'] ?? 0, 2) ?></span>
                <span class="stat-subtext"><?= intval($data['summary']['pending_approvals']) ?> expense claims pending</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-nav">
        <button class="tab-btn active" onclick="switchTab('tab-expenses', this)">
            <i class="ph ph-receipt"></i> Claims & Expenses
        </button>
        <button class="tab-btn" onclick="switchTab('tab-reimbursements', this)">
            <i class="ph ph-hand-coins"></i> Fund Reimbursements
        </button>
        <button class="tab-btn" onclick="switchTab('tab-transfers', this)">
            <i class="ph ph-arrows-left-right"></i> External Transfers
        </button>
        <button class="tab-btn" onclick="switchTab('tab-settings', this)">
            <i class="ph ph-gear"></i> Module Configurations
        </button>
    </div>

    <!-- Tab 1: Expenses & Claims -->
    <div id="tab-expenses" class="tab-content active">
        <div class="table-container">
            <div style="padding: 18px; border-bottom: 1px solid var(--mega-divider); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 15px; font-weight: 700;">Expense Claim Records</h3>
            </div>
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Voucher ID</th>
                        <th>Category</th>
                        <th>Account</th>
                        <th>Description</th>
                        <th style="text-align: right;">Amount (Rs.)</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                        <th style="text-align: center;">Receipt</th>
                        <th style="text-align: right; width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['expenses'])): ?>
                        <tr><td colspan="10" style="text-align: center; color: var(--text-muted); padding: 30px 0;">No expense vouchers found.</td></tr>
                    <?php else: ?>
                        <?php foreach($data['expenses'] as $exp): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($exp->expense_date)) ?></td>
                                <td><strong><?= htmlspecialchars($exp->voucher_number) ?></strong></td>
                                <td><span style="font-weight: 600; color: var(--text-accent);"><?= htmlspecialchars($exp->category) ?></span></td>
                                <td><span style="font-size:11.5px;"><?= htmlspecialchars($exp->expense_account_code . ' - ' . $exp->expense_account_name) ?></span></td>
                                <td><?= htmlspecialchars($exp->description) ?></td>
                                <td style="text-align: right; font-weight: 700;">Rs. <?= number_format($exp->amount, 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($exp->status) ?>">
                                        <?= htmlspecialchars($exp->status) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($exp->creator_name) ?></td>
                                <td style="text-align: center;">
                                    <?php if (!empty($exp->attachment_path)): ?>
                                        <a href="<?= APP_URL . '/' . htmlspecialchars($exp->attachment_path) ?>" target="_blank" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">
                                            <i class="ph ph-paperclip"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">No file</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($exp->status === 'Pending'): ?>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <form action="<?= APP_URL ?>/pettycash/approve_expense" method="POST" onsubmit="return confirm('Confirm and approve this petty cash claim? This will automatically post the corresponding ledger journal entries.')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <input type="hidden" name="expense_id" value="<?= $exp->id ?>">
                                                <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 11px;">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="<?= APP_URL ?>/pettycash/reject_expense" method="POST" onsubmit="return confirm('Are you sure you want to reject this petty cash claim?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <input type="hidden" name="expense_id" value="<?= $exp->id ?>">
                                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Fund Reimbursements -->
    <div id="tab-reimbursements" class="tab-content">
        <div class="split-layout">
            <div class="table-container" style="height: fit-content;">
                <div style="padding: 18px; border-bottom: 1px solid var(--mega-divider);">
                    <h3 style="font-size: 15px; font-weight: 700;">Reimbursement Log</h3>
                </div>
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference ID</th>
                            <th>Source Account</th>
                            <th style="text-align: right;">Amount (Rs.)</th>
                            <th>Remarks</th>
                            <th>Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['reimbursements'])): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px 0;">No reimbursement entries logged.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['reimbursements'] as $reim): ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($reim->reimbursement_date)) ?></td>
                                    <td><strong><?= htmlspecialchars($reim->reimbursement_number) ?></strong></td>
                                    <td><?= htmlspecialchars($reim->funding_account_code . ' - ' . $reim->funding_account_name) ?></td>
                                    <td style="text-align: right; font-weight: 700; color: #10b981;">Rs. <?= number_format($reim->amount, 2) ?></td>
                                    <td><?= htmlspecialchars($reim->remarks) ?></td>
                                    <td><?= htmlspecialchars($reim->username) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Reimbursement creation form -->
            <div class="form-box">
                <div class="form-title">
                    <i class="ph ph-hand-coins" style="color: #10b981; font-size: 20px;"></i>
                    Restore Petty Cash Funds
                </div>
                <form action="<?= APP_URL ?>/pettycash/reimburse" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label>Reimbursement Date</label>
                        <input type="date" name="reimbursement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Recommended Amount to Restore</label>
                        <?php 
                            $restorable = $data['summary']['limit_amount'] - $data['summary']['current_balance'];
                            if ($restorable < 0) $restorable = 0;
                        ?>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="<?= sprintf("%.2f", $restorable) ?>" required>
                        <small style="font-size: 11px; color: var(--text-muted);">Available restorable buffer based on configurations.</small>
                    </div>

                    <div class="form-group">
                        <label>Funding Bank/Cash Account</label>
                        <select name="funding_account_id" class="form-control" required>
                            <option value="">-- Choose Account --</option>
                            <?php foreach($data['bank_cash_accounts'] as $acc): ?>
                                <option value="<?= $acc->id ?>" <?= ($data['config']->default_funding_account_id == $acc->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Remarks / Memo</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Restoration of petty cash balance..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 10px;">
                        Post Reimbursement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 3: External Transfers -->
    <div id="tab-transfers" class="tab-content">
        <div class="split-layout">
            <div class="table-container" style="height: fit-content;">
                <div style="padding: 18px; border-bottom: 1px solid var(--mega-divider);">
                    <h3 style="font-size: 15px; font-weight: 700;">Fund Injection History</h3>
                </div>
                <table class="pc-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference ID</th>
                            <th>Source Account</th>
                            <th style="text-align: right;">Amount (Rs.)</th>
                            <th>Remarks</th>
                            <th>Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Fetch transfers from model or DB directly
                        $db = new Database();
                        $db->query("
                            SELECT t.*, u.username, src.account_code as source_code, src.account_name as source_name
                            FROM petty_cash_transfers t
                            LEFT JOIN users u ON t.created_by = u.id
                            LEFT JOIN chart_of_accounts src ON t.source_account_id = src.id
                            ORDER BY t.transfer_date DESC, t.id DESC
                        ");
                        $transfers = $db->resultSet() ?: [];
                        if (empty($transfers)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px 0;">No manual fund transfers registered.</td></tr>
                        <?php else: ?>
                            <?php foreach($transfers as $tr): ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($tr->transfer_date)) ?></td>
                                    <td><strong><?= htmlspecialchars($tr->transfer_number) ?></strong></td>
                                    <td><?= htmlspecialchars($tr->source_code . ' - ' . $tr->source_name) ?></td>
                                    <td style="text-align: right; font-weight: 700; color: #10b981;">Rs. <?= number_format($tr->amount, 2) ?></td>
                                    <td><?= htmlspecialchars($tr->remarks) ?></td>
                                    <td><?= htmlspecialchars($tr->username) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Transfer creation form -->
            <div class="form-box">
                <div class="form-title">
                    <i class="ph ph-arrows-left-right" style="color: var(--text-accent); font-size: 20px;"></i>
                    Inject Fund / Transfer
                </div>
                <form action="<?= APP_URL ?>/pettycash/transfer" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label>Transfer Date</label>
                        <input type="date" name="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Transfer Amount (Rs.)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Source Account (Cr.)</label>
                        <select name="source_account_id" class="form-control" required>
                            <option value="">-- Choose Account --</option>
                            <?php foreach($data['bank_cash_accounts'] as $acc): ?>
                                <option value="<?= $acc->id ?>">
                                    <?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Remarks / Reference</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Direct transfer to increase petty cash..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        Execute Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 4: Settings & Configuration -->
    <div id="tab-settings" class="tab-content">
        <div class="split-layout">
            <!-- Settings configuration form -->
            <div class="form-box">
                <div class="form-title">
                    <i class="ph ph-gear" style="color: var(--text-accent); font-size: 20px;"></i>
                    Petty Cash Configurations
                </div>
                <form action="<?= APP_URL ?>/pettycash/save_config" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="form-group">
                        <label>Maximum Cash Limit (Rs.)</label>
                        <input type="number" name="limit_amount" class="form-control" step="0.01" min="0.01" value="<?= htmlspecialchars((string)($data['config']->limit_amount ?? 0)) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Default Funding Bank/Cash Account</label>
                        <select name="default_funding_account_id" class="form-control" required>
                            <option value="">-- Choose Account --</option>
                            <?php foreach($data['bank_cash_accounts'] as $acc): ?>
                                <option value="<?= $acc->id ?>" <?= ($data['config']->default_funding_account_id == $acc->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Responsible Custodian</label>
                        <select name="custodian_id" class="form-control" required>
                            <option value="">-- Choose Custodian --</option>
                            <?php foreach($data['users'] as $u): ?>
                                <option value="<?= $u->id ?>" <?= ($data['config']->custodian_id == $u->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($u->first_name ?? '') . ' ' . ($u->last_name ?? '') . ' (' . $u->username . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Reimbursement Threshold Alert Level (Rs.)</label>
                        <input type="number" name="reimbursement_threshold" class="form-control" step="0.01" value="<?= htmlspecialchars((string)($data['config']->reimbursement_threshold ?? '')) ?>" placeholder="Optional threshold alert limit">
                        <small style="font-size: 11px; color: var(--text-muted);">Triggers warning notification when petty cash drops below this level.</small>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                            <input type="checkbox" name="require_approval" id="require_approval" value="1" <?= ($data['config']->require_approval ?? 1) ? 'checked' : '' ?> style="width: 16px; height: 16px; cursor: pointer;">
                            <label for="require_approval" style="cursor: pointer; font-size: 13px; font-weight: 600;">Enforce Administrative Approvals</label>
                        </div>
                        <small style="font-size: 11px; color: var(--text-muted); margin-left: 24px;">If unchecked, claims are approved immediately upon creation.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">
                        Save Configurations
                    </button>
                </form>
            </div>

            <!-- History Logs -->
            <div class="form-box" style="height: fit-content;">
                <div class="form-title">
                    <i class="ph ph-shield-check" style="color: #10b981; font-size: 20px;"></i>
                    Configuration Change Logs
                </div>
                <div class="audit-timeline">
                    <?php if (empty($data['config_history'])): ?>
                        <div style="font-size: 12px; color: var(--text-muted);">No modifications logged.</div>
                    <?php else: ?>
                        <?php foreach($data['config_history'] as $hist): ?>
                            <div class="audit-item">
                                <div class="audit-meta"><?= date('Y-m-d H:i', strtotime($hist->created_at)) ?> | By <?= htmlspecialchars($hist->username) ?></div>
                                <div class="audit-desc">
                                    Limit: <strong>Rs. <?= number_format($hist->limit_amount, 2) ?></strong> | 
                                    Approval Req: <strong><?= $hist->require_approval ? 'Yes' : 'No' ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Expense -->
<div id="expenseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record Petty Cash Expense</h3>
            <button class="modal-close" onclick="closeModal('expenseModal')">&times;</button>
        </div>
        <form action="<?= APP_URL ?>/pettycash/record_expense" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Expense Date</label>
                    <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">-- Choose Category --</option>
                            <option value="Office Supplies">Office Supplies</option>
                            <option value="Refreshments & Meals">Refreshments & Meals</option>
                            <option value="Fuel & Transport">Fuel & Transport</option>
                            <option value="Postage & Courier">Postage & Courier</option>
                            <option value="Repairs & Maintenance">Repairs & Maintenance</option>
                            <option value="Miscellaneous">Miscellaneous</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount Claimed (Rs.)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>GL Expense Account</label>
                    <select name="expense_account_id" class="form-control" required>
                        <option value="">-- Choose Expense Account --</option>
                        <?php foreach($data['expense_accounts'] as $acc): ?>
                            <option value="<?= $acc->id ?>">
                                <?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Supplier / Vendor (Optional)</label>
                    <select name="vendor_id" class="form-control">
                        <option value="">-- None / General Claim --</option>
                        <?php foreach($data['vendors'] as $v): ?>
                            <option value="<?= $v->id ?>"><?= htmlspecialchars($v->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Receipt Document / Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                    <small style="font-size: 11px; color: var(--text-muted);">PDFs or images allowed.</small>
                </div>

                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter purpose of the expense claim..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('expenseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab switching logic
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');

        // Save tab to localstorage for persistence on refresh
        localStorage.setItem('petty_cash_active_tab', tabId);
    }

    // Modal helpers
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('open');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('open');
    }

    // Auto-restore tab from localStorage
    document.addEventListener('DOMContentLoaded', () => {
        const activeTab = localStorage.getItem('petty_cash_active_tab');
        if (activeTab && document.getElementById(activeTab)) {
            const btn = Array.from(document.querySelectorAll('.tab-btn')).find(b => {
                return b.getAttribute('onclick').includes(activeTab);
            });
            if (btn) {
                switchTab(activeTab, btn);
            }
        }
    });
</script>
