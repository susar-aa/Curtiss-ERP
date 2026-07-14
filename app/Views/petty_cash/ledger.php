<?php
// Petty Cash Ledger History View
?>
<style>
    .ledger-wrap {
        display: flex;
        flex-direction: column;
        gap: 20px;
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 50px;
    }

    .filter-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
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

    .form-control {
        padding: 8px 12px;
        border: 1px solid var(--card-border);
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.02);
        color: var(--text-main);
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
    }

    @media (prefers-color-scheme: dark) {
        .form-control {
            background: rgba(255, 255, 255, 0.03);
        }
    }

    .form-control:focus {
        border-color: var(--text-accent);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        height: 38px;
        box-sizing: border-box;
    }

    .btn-primary {
        background: var(--text-accent);
        color: #fff;
    }

    .btn-primary:hover {
        background: #4338ca;
    }

    .btn-secondary {
        background: rgba(0, 0, 0, 0.04);
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

    /* Ledger Table */
    .table-container {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
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

    .tx-debit {
        color: #10b981;
        font-weight: 700;
    }

    .tx-credit {
        color: #ef4444;
        font-weight: 700;
    }

    .running-balance {
        font-weight: 700;
        color: var(--text-main);
    }

    .badge-tx {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-Expense { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .badge-Reimbursement { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge-Transfer { background: rgba(79, 70, 229, 0.1); color: var(--text-accent); }
</style>

<div class="ledger-wrap">
    <!-- Back Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="<?= APP_URL ?>/pettycash" class="btn btn-secondary" style="padding: 8px 12px;">
                <i class="ph ph-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h1 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">Petty Cash Transaction Ledger</h1>
                <p style="font-size: 13px; color: var(--text-muted);">View complete double-entry transaction history of Account 1020.</p>
            </div>
        </div>
        <div>
            <button class="btn btn-primary" onclick="triggerPrint()">
                <i class="ph ph-printer"></i> Print Audit Report
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="<?= APP_URL ?>/pettycash/ledger">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($data['filters']['start_date'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($data['filters']['end_date'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label>Transaction Type</label>
                    <select name="tx_type" class="form-control">
                        <option value="">-- All Types --</option>
                        <option value="Expense" <?= ($data['filters']['tx_type'] === 'Expense') ? 'selected' : '' ?>>Expense Claim</option>
                        <option value="Reimbursement" <?= ($data['filters']['tx_type'] === 'Reimbursement') ? 'selected' : '' ?>>Reimbursement</option>
                        <option value="Transfer" <?= ($data['filters']['tx_type'] === 'Transfer') ? 'selected' : '' ?>>Fund Injection</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Claim Category</label>
                    <select name="category" class="form-control">
                        <option value="">-- All Categories --</option>
                        <?php foreach($data['categories'] as $c): ?>
                            <option value="<?= htmlspecialchars($c->category) ?>" <?= ($data['filters']['category'] === $c->category) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c->category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Recorded By</label>
                    <select name="user_id" class="form-control">
                        <option value="">-- All Users --</option>
                        <?php foreach($data['users'] as $u): ?>
                            <option value="<?= $u->id ?>" <?= ($data['filters']['user_id'] == $u->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u->username) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="ph ph-funnel"></i> Apply
                    </button>
                    <a href="<?= APP_URL ?>/pettycash/ledger" class="btn btn-secondary">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Ledger table -->
    <div class="table-container">
        <table class="pc-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ref # / Voucher</th>
                    <th>Type</th>
                    <th>Details / Category</th>
                    <th>Recorded By</th>
                    <th style="text-align: right;">Debit (Rs.)</th>
                    <th style="text-align: right;">Credit (Rs.)</th>
                    <th style="text-align: right;">Running Balance (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['ledger'])): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 35px 0;">No ledger transactions match selected filters.</td></tr>
                <?php else: ?>
                    <?php foreach($data['ledger'] as $row): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i', strtotime($row['tx_date'])) ?></td>
                            <td><strong><?= htmlspecialchars($row['reference_number']) ?></strong></td>
                            <td>
                                <span class="badge-tx badge-<?= htmlspecialchars($row['tx_type']) ?>">
                                    <?= htmlspecialchars($row['tx_type'] === 'Expense' ? 'Expense Claim' : $row['tx_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($row['remarks']) ?></div>
                                <?php if (!empty($row['category'])): ?>
                                    <small style="font-weight: 600; color: var(--text-accent);"><?= htmlspecialchars($row['category']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['creator_name']) ?></td>
                            
                            <td style="text-align: right;">
                                <?php if ($row['debit'] > 0): ?>
                                    <span class="tx-debit">+ Rs. <?= number_format($row['debit'], 2) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="text-align: right;">
                                <?php if ($row['credit'] > 0): ?>
                                    <span class="tx-credit">- Rs. <?= number_format($row['credit'], 2) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: right;">
                                <span class="running-balance">Rs. <?= number_format($row['running_balance'], 2) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function triggerPrint() {
        const queryParams = new URLSearchParams(window.location.search).toString();
        window.open('<?= APP_URL ?>/pettycash/print_report?' + queryParams, '_blank');
    }
</script>
