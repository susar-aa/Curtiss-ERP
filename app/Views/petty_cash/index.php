<?php
declare(strict_types=1);
?>

<style>
    /* Styling for Petty Cash Module */
    .pc-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding: 4px;
        height: 100%;
        overflow-y: auto;
    }

    .pc-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .pc-title-area h1 {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.5px;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pc-title-area p {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .pc-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Modern KPI Cards Grid */
    .pc-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .kpi-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .kpi-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 80px;
        color: rgba(79, 70, 229, 0.06);
        pointer-events: none;
    }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 700;
        color: var(--text-main);
        margin: 12px 0 6px 0;
        letter-spacing: -0.5px;
    }

    .kpi-footer {
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .progress-bar-container {
        width: 100%;
        height: 6px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
        margin-top: 10px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: var(--text-accent);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    /* Tabs & Content */
    .pc-tabs-wrapper {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow: hidden;
    }

    .pc-tabs {
        display: flex;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        background: rgba(0, 0, 0, 0.01);
        padding: 0 16px;
    }

    .tab-btn {
        padding: 16px 20px;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--text-muted);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
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
        padding: 20px;
        flex: 1;
        overflow-y: auto;
    }

    .tab-content.active {
        display: block;
    }

    /* Filters Bar */
    .filters-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-input {
        padding: 8px 12px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        background: rgba(255,255,255,0.7);
        color: var(--text-main);
        font-size: 13px;
        min-width: 140px;
        outline: none;
        transition: border-color 0.2s;
    }

    @media (prefers-color-scheme: dark) {
        .filter-input {
            background: rgba(30, 30, 50, 0.7);
            border-color: rgba(255,255,255,0.1);
        }
    }

    .filter-input:focus {
        border-color: var(--text-accent);
    }

    /* Buttons */
    .btn {
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--text-accent);
        color: #ffffff;
    }

    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text-main);
        border: 1px solid rgba(0,0,0,0.1);
    }

    @media (prefers-color-scheme: dark) {
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255,255,255,0.15);
        }
    }

    .btn-secondary:hover {
        background: rgba(0, 0, 0, 0.08);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--text-accent);
        color: var(--text-accent);
    }

    .btn-outline:hover {
        background: rgba(79, 70, 229, 0.05);
    }

    .btn-danger {
        background: #dc2626;
        color: #ffffff;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
    }

    /* Table Styles */
    .pc-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .pc-table th, .pc-table td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-size: 13.5px;
    }

    @media (prefers-color-scheme: dark) {
        .pc-table th, .pc-table td {
            border-bottom-color: rgba(255,255,255,0.05);
        }
    }

    .pc-table th {
        background: rgba(0,0,0,0.01);
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .pc-table tr:hover td {
        background: rgba(0, 0, 0, 0.01);
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .badge-danger { background: rgba(239, 68, 68, 0.1); color: #ef6868; }
    .badge-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

    /* Modals */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        z-index: 3000;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .modal.show {
        display: flex;
        opacity: 1;
    }

    .modal-dialog {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 500px;
        overflow: hidden;
        transform: translateY(-20px);
        transition: transform 0.25s ease;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-main);
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 20px;
        color: var(--text-muted);
        cursor: pointer;
    }

    .modal-body {
        padding: 20px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-footer {
        padding: 16px 20px;
        border-top: 1px solid rgba(0,0,0,0.06);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: rgba(0,0,0,0.01);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }

    .form-group label {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-main);
    }

    .form-group input, .form-group select, .form-group textarea {
        padding: 10px 12px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        background: rgba(255,255,255,0.8);
        color: var(--text-main);
        font-size: 13.5px;
        outline: none;
        width: 100%;
        box-sizing: border-box;
    }

    @media (prefers-color-scheme: dark) {
        .form-group input, .form-group select, .form-group textarea {
            background: rgba(30, 30, 50, 0.8);
            border-color: rgba(255,255,255,0.15);
        }
    }

    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: var(--text-accent);
    }

    .form-checkbox {
        flex-direction: row;
        align-items: center;
        gap: 10px;
    }

    .form-checkbox input {
        width: auto;
    }

    /* Alerts */
    .alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 12px;
        color: rgba(79, 70, 229, 0.2);
    }

    /* Pagination container */
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
</style>

<div class="pc-container">
    <!-- Header -->
    <div class="pc-header">
        <div class="pc-title-area">
            <h1><i class="ph ph-coins"></i> Petty Cash Dashboard</h1>
            <p>Administer allocations, record office expenditures, and request replenishments</p>
        </div>
        <div class="pc-actions">
            <button class="btn btn-secondary" onclick="openModal('settingsModal')"><i class="ph ph-gear"></i> Settings</button>
            <button class="btn btn-outline" onclick="openModal('allocateModal')"><i class="ph ph-bank"></i> Fund Float</button>
            <button class="btn btn-primary" onclick="openModal('expenseModal')"><i class="ph ph-plus"></i> Record Expense</button>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($data['success'])): ?>
        <div class="alert alert-success">
            <i class="ph ph-check-circle"></i>
            <span><?= htmlspecialchars($data['success']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger">
            <i class="ph ph-warning-circle"></i>
            <span><?= htmlspecialchars($data['error']) ?></span>
        </div>
    <?php endif; ?>

    <!-- KPI Grid -->
    <div class="pc-kpi-grid">
        <!-- KPI 1: Ledger Balance -->
        <div class="kpi-card">
            <i class="ph ph-wallet kpi-icon"></i>
            <div>
                <div class="kpi-header">
                    <span>Petty Cash Balance</span>
                    <span class="badge badge-info">Ledger 1020</span>
                </div>
                <div class="kpi-value">Rs: <?= number_format($data['ledger_balance'], 2) ?></div>
            </div>
            <div>
                <?php 
                $pct = $data['config_limit'] > 0 ? ($data['ledger_balance'] / $data['config_limit']) * 100 : 0;
                ?>
                <div class="kpi-footer">
                    <span><?= round($pct) ?>% of limit (Rs: <?= number_format($data['config_limit'], 2) ?>)</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?= min(100, $pct) ?>%; background: <?= $pct < 20 ? '#ef6868' : 'var(--text-accent)' ?>;"></div>
                </div>
            </div>
        </div>

        <!-- KPI 2: Configured Limit -->
        <div class="kpi-card">
            <i class="ph ph-sliders kpi-icon"></i>
            <div>
                <div class="kpi-header">
                    <span>Petty Cash Limit</span>
                </div>
                <div class="kpi-value">Rs: <?= number_format($data['config_limit'], 2) ?></div>
            </div>
            <div class="kpi-footer">
                <i class="ph ph-user-focus"></i>
                <span>Custodian: <strong><?= $data['config'] && $data['config']->custodian_name ? htmlspecialchars($data['config']->custodian_name) : 'Not Assigned' ?></strong></span>
            </div>
        </div>

        <!-- KPI 3: Available Balance -->
        <div class="kpi-card">
            <i class="ph ph-hand-coins"></i>
            <div>
                <div class="kpi-header">
                    <span>Available to Spend</span>
                </div>
                <div class="kpi-value" style="color: <?= $data['available_balance'] <= 0 ? '#ef6868' : 'inherit' ?>;">Rs: <?= number_format($data['available_balance'], 2) ?></div>
            </div>
            <div class="kpi-footer">
                <span>Excluding pending approvals</span>
            </div>
        </div>

        <!-- KPI 4: Pending Reimbursements -->
        <div class="kpi-card">
            <i class="ph ph-receipt kpi-icon"></i>
            <div>
                <div class="kpi-header">
                    <span>Pending Reimbursement</span>
                    <?php 
                    $threshold = $data['config'] ? floatval($data['config']->reimbursement_threshold) : 10000.00;
                    $exceeded = $data['pending_reimbursements'] >= $threshold;
                    if ($exceeded):
                    ?>
                        <span class="badge badge-danger">Threshold Exceeded</span>
                    <?php endif; ?>
                </div>
                <div class="kpi-value">Rs: <?= number_format($data['pending_reimbursements'], 2) ?></div>
            </div>
            <div class="kpi-footer" style="justify-content: space-between;">
                <span>Threshold: Rs: <?= number_format($threshold, 2) ?></span>
                <?php if ($data['pending_reimbursements'] > 0): ?>
                    <button class="btn btn-outline btn-sm" onclick="openModal('reimburseModal')">Reimburse</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabs Wrapper -->
    <div class="pc-tabs-wrapper">
        <div class="pc-tabs">
            <button class="tab-btn active" onclick="switchTab(event, 'transactionsTab')"><i class="ph ph-list-bullets"></i> Transactions</button>
            <button class="tab-btn" onclick="switchTab(event, 'reimbursementsTab')"><i class="ph ph-arrows-clockwise"></i> Reimbursements</button>
            <button class="tab-btn" onclick="switchTab(event, 'pendingExpensesTab')"><i class="ph ph-clock"></i> Unreimbursed Expenses (<?= count($data['pending_expenses']) ?>)</button>
        </div>

        <!-- Tab 1: Transactions -->
        <div id="transactionsTab" class="tab-content active">
            <!-- Filter Form -->
            <form method="GET" action="<?= APP_URL ?>/pettycash" class="filters-bar">
                <div class="filter-group">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="filter-input">
                        <option value="">All Types</option>
                        <option value="allocation" <?= $data['filters']['type'] === 'allocation' ? 'selected' : '' ?>>Allocation</option>
                        <option value="expense" <?= $data['filters']['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                        <option value="reimbursement" <?= $data['filters']['type'] === 'reimbursement' ? 'selected' : '' ?>>Reimbursement</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="filter-input">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?= $data['filters']['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= $data['filters']['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= $data['filters']['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($data['filters']['date_from']) ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($data['filters']['date_to']) ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <label for="search">Keyword</label>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($data['filters']['search']) ?>" placeholder="Paid to, desc, ref..." class="filter-input" style="min-width: 180px;">
                </div>
                <button type="submit" class="btn btn-secondary"><i class="ph ph-funnel"></i> Filter</button>
                <a href="<?= APP_URL ?>/pettycash" class="btn btn-outline" style="padding: 9px 12px;"><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
            </form>

            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ref & J/E</th>
                        <th>Type</th>
                        <th>Payee</th>
                        <th>Description & Category</th>
                        <th style="text-align: right;">Amount</th>
                        <th>Status</th>
                        <th style="text-align: center;">Receipt</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['transactions'])): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="ph ph-receipt"></i>
                                    <p>No transactions found matching the filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: foreach ($data['transactions'] as $tx): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($tx->transaction_date)) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($tx->reference) ?></strong>
                                <?php if ($tx->journal_entry_id): ?>
                                    <br><span style="font-size: 11px; color: var(--text-muted);">J/E ID: <?= $tx->journal_entry_id ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($tx->type === 'allocation'): ?>
                                    <span class="badge badge-info">Allocation</span>
                                <?php elseif ($tx->type === 'reimbursement'): ?>
                                    <span class="badge badge-success">Reimburse</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Expense</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($tx->paid_to ?: 'N/A') ?></td>
                            <td>
                                <?= htmlspecialchars($tx->description) ?>
                                <?php if ($tx->offset_account_name): ?>
                                    <br><span style="font-size: 11px; color: var(--text-accent);">Acct: <?= $tx->offset_account_code ?> - <?= $tx->offset_account_name ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-weight: bold; color: <?= $tx->type === 'expense' ? '#ef6868' : '#10b981' ?>;">
                                <?= $tx->type === 'expense' ? '-' : '+' ?>Rs: <?= number_format($tx->amount, 2) ?>
                            </td>
                            <td>
                                <?php if ($tx->status === 'Approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php elseif ($tx->status === 'Pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($tx->attachment_path): ?>
                                    <a href="<?= APP_URL ?>/<?= $tx->attachment_path ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px;"><i class="ph ph-file-pdf"></i> View</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 11px;">None</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($tx->status === 'Pending' && $tx->type === 'expense'): ?>
                                    <a href="<?= APP_URL ?>/pettycash/approve_expense/<?= $tx->id ?>" class="btn btn-primary btn-sm"><i class="ph ph-check"></i> Approve</a>
                                    <a href="<?= APP_URL ?>/pettycash/reject_expense/<?= $tx->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this expense?')"><i class="ph ph-x"></i> Reject</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($data['total_pages'] > 1): ?>
                <div class="pagination">
                    <span style="font-size: 13px; color: var(--text-muted);">Page <?= $data['page'] ?> of <?= $data['total_pages'] ?></span>
                    <div style="display: flex; gap: 6px;">
                        <?php if ($data['page'] > 1): ?>
                            <a href="?page=<?= $data['page'] - 1 ?>&type=<?= $data['filters']['type'] ?>&status=<?= $data['filters']['status'] ?>&date_from=<?= $data['filters']['date_from'] ?>&date_to=<?= $data['filters']['date_to'] ?>&search=<?= urlencode($data['filters']['search']) ?>" class="btn btn-secondary btn-sm">Prev</a>
                        <?php endif; ?>
                        <?php if ($data['page'] < $data['total_pages']): ?>
                            <a href="?page=<?= $data['page'] + 1 ?>&type=<?= $data['filters']['type'] ?>&status=<?= $data['filters']['status'] ?>&date_from=<?= $data['filters']['date_from'] ?>&date_to=<?= $data['filters']['date_to'] ?>&search=<?= urlencode($data['filters']['search']) ?>" class="btn btn-secondary btn-sm">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Reimbursements -->
        <div id="reimbursementsTab" class="tab-content">
            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reimbursement ID</th>
                        <th>Remarks / Description</th>
                        <th>Bank / Cash Source</th>
                        <th style="text-align: right;">Replenished Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['reimbursements'])): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="ph ph-arrows-clockwise"></i>
                                    <p>No reimbursement requests have been created yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: foreach ($data['reimbursements'] as $reim): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($reim->reimbursement_date)) ?></td>
                            <td><strong>REIM-<?= $reim->id ?></strong></td>
                            <td><?= htmlspecialchars($reim->description) ?></td>
                            <td><?= $reim->bank_account_code ?> - <?= htmlspecialchars($reim->bank_account_name) ?></td>
                            <td style="text-align: right; font-weight: bold; color: #10b981;">Rs: <?= number_format($reim->amount, 2) ?></td>
                            <td>
                                <?php if ($reim->status === 'Approved'): ?>
                                    <span class="badge badge-success">Disbursed</span>
                                <?php elseif ($reim->status === 'Pending'): ?>
                                    <span class="badge badge-warning">Pending Approval</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($reim->status === 'Pending'): ?>
                                    <a href="<?= APP_URL ?>/pettycash/approve_reimbursement/<?= $reim->id ?>" class="btn btn-primary btn-sm"><i class="ph ph-hand-coins"></i> Disburse (Replenish)</a>
                                    <a href="<?= APP_URL ?>/pettycash/reject_reimbursement/<?= $reim->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject reimbursement? Linked expenses will be unlocked.')"><i class="ph ph-x"></i> Reject</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tab 3: Pending Expenses (ready to be reimbursed) -->
        <div id="pendingExpensesTab" class="tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <p style="font-size: 13.5px; color: var(--text-muted);">
                    These approved expenses are paid out of Petty Cash and await replenishment from a bank account.
                </p>
                <?php if (!empty($data['pending_expenses'])): ?>
                    <button class="btn btn-primary" onclick="openModal('reimburseModal')"><i class="ph ph-arrows-clockwise"></i> Generate Reimbursement Payout</button>
                <?php endif; ?>
            </div>

            <table class="pc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Payee</th>
                        <th>Description & Category</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['pending_expenses'])): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph ph-check-circle" style="color: #10b981;"></i>
                                    <p>All approved expenses have been reimbursed. Petty Cash balance matches the physical float!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: foreach ($data['pending_expenses'] as $pe): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($pe->transaction_date)) ?></td>
                            <td><strong><?= htmlspecialchars($pe->reference) ?></strong></td>
                            <td><?= htmlspecialchars($pe->paid_to ?: 'N/A') ?></td>
                            <td>
                                <?= htmlspecialchars($pe->description) ?>
                                <br><span style="font-size: 11px; color: var(--text-accent);">Acct: <?= $pe->offset_account_code ?> - <?= $pe->offset_account_name ?></span>
                            </td>
                            <td style="text-align: right; font-weight: bold; color: #ef6868;">Rs: <?= number_format($pe->amount, 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL 1: SETTINGS -->
<div id="settingsModal" class="modal">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/pettycash/settings">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div class="modal-header">
                <h3><i class="ph ph-gear"></i> Petty Cash Configuration</h3>
                <button type="button" class="close-btn" onclick="closeModal('settingsModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="limit_amount">Petty Cash Limit (Float Amount) *</label>
                    <input type="number" step="0.01" name="limit_amount" id="limit_amount" value="<?= $data['config'] ? $data['config']->limit_amount : '50000.00' ?>" required>
                </div>
                <div class="form-group">
                    <label for="custodian_id">Custodian User *</label>
                    <select name="custodian_id" id="custodian_id" required>
                        <option value="">-- Select Custodian --</option>
                        <?php foreach ($data['users'] as $u): ?>
                            <option value="<?= $u->id ?>" <?= $data['config'] && (int)$data['config']->custodian_id === (int)$u->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u->username) ?> (<?= htmlspecialchars($u->email) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="default_funding_account_id">Default Funding Bank/Cash Account *</label>
                    <select name="default_funding_account_id" id="default_funding_account_id" required>
                        <option value="">-- Select Source Account --</option>
                        <?php foreach ($data['funding_accounts'] as $fa): ?>
                            <option value="<?= $fa->id ?>" <?= $data['config'] && (int)$data['config']->default_funding_account_id === (int)$fa->id ? 'selected' : '' ?>>
                                <?= $fa->account_code ?> - <?= htmlspecialchars($fa->account_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reimbursement_threshold">Reimbursement Alert Threshold (Spent Amount) *</label>
                    <input type="number" step="0.01" name="reimbursement_threshold" id="reimbursement_threshold" value="<?= $data['config'] ? $data['config']->reimbursement_threshold : '10000.00' ?>" required>
                </div>
                <div class="form-group form-checkbox">
                    <input type="checkbox" name="require_approval" id="require_approval" value="1" <?= !$data['config'] || $data['config']->require_approval ? 'checked' : '' ?>>
                    <label for="require_approval">Require Manager Approval for all Petty Cash Expenses</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('settingsModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: FUND FLOAT (ALLOCATION) -->
<div id="allocateModal" class="modal">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/pettycash/allocate">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div class="modal-header">
                <h3><i class="ph ph-bank"></i> Fund Petty Cash Float</h3>
                <button type="button" class="close-btn" onclick="closeModal('allocateModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="alloc_amount">Allocation Amount (Rs:) *</label>
                    <input type="number" step="0.01" name="amount" id="alloc_amount" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label for="bank_account_id">Source Bank/Cash Account *</label>
                    <select name="bank_account_id" id="bank_account_id" required>
                        <option value="">-- Select Source Account --</option>
                        <?php foreach ($data['funding_accounts'] as $fa): ?>
                            <option value="<?= $fa->id ?>" <?= $data['config'] && (int)$data['config']->default_funding_account_id === (int)$fa->id ? 'selected' : '' ?>>
                                <?= $fa->account_code ?> - <?= htmlspecialchars($fa->account_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alloc_date">Transfer Date *</label>
                    <input type="date" name="transaction_date" id="alloc_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="alloc_ref">Reference / Slip Number</label>
                    <input type="text" name="reference" id="alloc_ref" placeholder="PC-AL-<?= time() ?>">
                </div>
                <div class="form-group">
                    <label for="alloc_desc">Remarks / Description</label>
                    <textarea name="description" id="alloc_desc" rows="3" placeholder="Establish/top-up petty cash float"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('allocateModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Allocate Funds</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: RECORD EXPENSE -->
<div id="expenseModal" class="modal">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/pettycash/expense" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div class="modal-header">
                <h3><i class="ph ph-plus"></i> Record Petty Cash Expense</h3>
                <button type="button" class="close-btn" onclick="closeModal('expenseModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="exp_amount">Expense Amount (Rs:) *</label>
                    <input type="number" step="0.01" name="amount" id="exp_amount" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label for="account_id">Expense Category Account *</label>
                    <select name="account_id" id="account_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($data['expense_accounts'] as $ea): ?>
                            <option value="<?= $ea->id ?>"><?= $ea->account_code ?> - <?= htmlspecialchars($ea->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="paid_to">Paid To (Payee) *</label>
                    <input type="text" name="paid_to" id="paid_to" placeholder="Merchant or Employee Name" required>
                </div>
                <div class="form-group">
                    <label for="exp_date">Expense Date *</label>
                    <input type="date" name="transaction_date" id="exp_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="exp_ref">Receipt / Reference Number</label>
                    <input type="text" name="reference" id="exp_ref" placeholder="e.g. Bill #1234">
                </div>
                <div class="form-group">
                    <label for="exp_desc">Description *</label>
                    <textarea name="description" id="exp_desc" rows="2" placeholder="Describe the item or service purchased" required></textarea>
                </div>
                <div class="form-group">
                    <label for="attachment">Attach Receipt (PDF/Image)</label>
                    <input type="file" name="attachment" id="attachment" accept="image/*,application/pdf">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('expenseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Record Expense</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: REQUEST REIMBURSEMENT -->
<div id="reimburseModal" class="modal">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/pettycash/reimburse">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
            <div class="modal-header">
                <h3><i class="ph ph-arrows-clockwise"></i> Generate Reimbursement Request</h3>
                <button type="button" class="close-btn" onclick="closeModal('reimburseModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?php 
                $reimAmount = 0.0;
                foreach ($data['pending_expenses'] as $pe) {
                    $reimAmount += floatval($pe->amount);
                }
                ?>
                <div style="background: rgba(79, 70, 229, 0.06); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(79, 70, 229, 0.1);">
                    <p style="font-size: 13px; color: var(--text-muted);">Total Reimbursement Value:</p>
                    <p style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-top: 4px;">Rs: <?= number_format($reimAmount, 2) ?></p>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">Includes <strong><?= count($data['pending_expenses']) ?></strong> approved expense transactions.</p>
                </div>

                <div class="form-group">
                    <label for="reim_bank_account_id">Draw Funds From Bank Account *</label>
                    <select name="bank_account_id" id="reim_bank_account_id" required>
                        <option value="">-- Select Source Bank --</option>
                        <?php foreach ($data['funding_accounts'] as $fa): ?>
                            <option value="<?= $fa->id ?>" <?= $data['config'] && (int)$data['config']->default_funding_account_id === (int)$fa->id ? 'selected' : '' ?>>
                                <?= $fa->account_code ?> - <?= htmlspecialchars($fa->account_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reim_date">Reimbursement Date *</label>
                    <input type="date" name="reimbursement_date" id="reim_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="reim_desc">Remarks / Description</label>
                    <textarea name="description" id="reim_desc" rows="3" placeholder="Reimbursement of office expenses"><?= 'Reimbursement request for ' . count($data['pending_expenses']) . ' office expenses' ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('reimburseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Request</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab switching logic
    function switchTab(evt, tabId) {
        // Hide all tab contents
        const tabcontents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabcontents.length; i++) {
            tabcontents[i].classList.remove("active");
        }

        // Deactivate all tab buttons
        const tablinks = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }

        // Show the current tab and activate the button
        document.getElementById(tabId).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    // Modal control logic
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }
    }

    // Close modal when clicking outside the dialog content
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    }
</script>
