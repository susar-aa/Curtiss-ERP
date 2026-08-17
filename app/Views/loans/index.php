<?php
$stats = $data['stats'] ?? (object)[
    'active_loans' => 0,
    'total_principal' => 0,
    'outstanding_principal' => 0,
    'paid_interest' => 0
];
$loans = $data['loans'] ?? [];

$flashSuccess = $_GET['success'] ?? null;
$flashError = $_GET['error'] ?? null;
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — LOANS MANAGEMENT
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.12);
    --c-separator:    rgba(60,60,67,0.12);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);

    --r-sm: 10px;
    --r-md: 14px;
    --r-xl: 26px;
    --r-pill: 999px;

    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
}

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #121212;
        --c-surface:      #1e1e2e;
        --c-separator:    rgba(255,255,255,0.15);
        --t-primary:      #f5f5f7;
        --t-secondary:    #a1a1aa;
        --t-label:        #52525b;
    }
}

.loan-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
}

.loan-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 16px 24px 100px;
}

.loan-header { margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end; }
.loan-title { font-size: 32px; font-weight: 700; letter-spacing: -0.03em; color: var(--t-primary); margin-bottom: 4px; }
.loan-subtitle { font-size: 15px; color: var(--t-secondary); margin: 0; }

.btn-apple {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--c-blue); color: #fff;
    border: none; border-radius: var(--r-pill);
    padding: 10px 20px; font-size: 15px; font-weight: 600;
    transition: all var(--dur-fast); cursor: pointer; text-decoration: none;
}
.btn-apple:hover { background: #0062cc; color: #fff; transform: translateY(-1px); }

/* Stat Cards */
.stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px; }
.stat-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    position: relative; overflow: hidden;
    display: flex; align-items: center; gap: 16px;
    transition: transform var(--dur-fast) var(--ease-ios), box-shadow var(--dur-fast) var(--ease-ios);
}
.stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

.stat-card.blue::before { background: var(--c-blue); }
.stat-card.purple::before { background: var(--c-purple); }
.stat-card.red::before { background: var(--c-red); }
.stat-card.orange::before { background: var(--c-orange); }

.stat-icon {
    width: 48px; height: 48px; border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.stat-card.blue .stat-icon { background: var(--c-blue-light); color: var(--c-blue); }
.stat-card.purple .stat-icon { background: var(--c-purple-light); color: var(--c-purple); }
.stat-card.red .stat-icon { background: var(--c-red-light); color: var(--c-red); }
.stat-card.orange .stat-icon { background: var(--c-orange-light); color: var(--c-orange); }

.stat-info { display: flex; flex-direction: column; }
.stat-num { font-size: 24px; font-weight: 700; letter-spacing: -0.04em; font-family: var(--f-mono); line-height: 1; margin-bottom: 4px; color: var(--t-primary); }
.stat-lbl { font-size: 12px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--t-label); }

/* Table Section */
.card-apple {
    background: var(--c-surface);
    border-radius: var(--r-md);
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    overflow: hidden;
}
.table-apple { width: 100%; border-collapse: collapse; }
.table-apple th {
    background: var(--c-surface2); padding: 12px 16px;
    font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--t-label);
    border-bottom: 1px solid var(--c-separator); text-align: left;
}
.table-apple td {
    padding: 16px; border-bottom: 0.5px solid var(--c-separator);
    font-size: 14px; color: var(--t-primary); vertical-align: middle;
}
.table-apple tr:last-child td { border-bottom: none; }
.table-apple tr:hover td { background: var(--c-surface2); }

.badge-apple {
    padding: 4px 10px; border-radius: var(--r-pill); font-size: 12px; font-weight: 600;
}
.badge-success { background: var(--c-green-light); color: var(--c-green); }
.badge-warning { background: var(--c-orange-light); color: var(--c-orange); }
.badge-secondary { background: var(--c-fill); color: var(--t-secondary); }

.action-menu { position: relative; display: inline-block; }
.action-btn { background: none; border: none; color: var(--t-secondary); font-size: 18px; cursor: pointer; padding: 4px 8px; border-radius: var(--r-xs); }
.action-btn:hover { background: var(--c-fill); color: var(--t-primary); }
.dropdown-content {
    display: none; position: absolute; right: 0; background-color: var(--c-surface);
    min-width: 160px; box-shadow: var(--shadow-md); z-index: 10;
    border-radius: var(--r-sm); border: 0.5px solid var(--c-separator); overflow: hidden;
}
.action-menu:hover .dropdown-content { display: block; }
.dropdown-content a, .dropdown-content button {
    color: var(--t-primary); padding: 10px 16px; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 14px;
    background: none; border: none; width: 100%; text-align: left; cursor: pointer;
}
.dropdown-content a:hover, .dropdown-content button:hover { background-color: var(--c-fill); }
.dropdown-content .text-danger { color: var(--c-red); }

</style>

<div class="loan-root">
    <div class="loan-wrap">
        
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success mt-3 sf-alert success" style="background:var(--c-green-light); color:var(--c-green); padding:15px; border-radius:var(--r-md); margin-bottom: 20px;">
            <i class="fa-solid fa-check-circle"></i> Action completed successfully.
        </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
        <div class="alert alert-danger mt-3 sf-alert error" style="background:var(--c-red-light); color:var(--c-red); padding:15px; border-radius:var(--r-md); margin-bottom: 20px;">
            <i class="fa-solid fa-exclamation-circle"></i> Action failed. <?php if($flashError == 'delete_failed_active') echo "Cannot delete a loan that has been disbursed or repaid."; ?>
        </div>
        <?php endif; ?>

        <div class="loan-header">
            <div>
                <h1 class="loan-title">Bank Loans Management</h1>
                <p class="loan-subtitle">Track and manage company loans and repayments.</p>
            </div>
            <a href="<?= APP_URL ?>/loan/create" class="btn-apple"><i class="fa-solid fa-plus"></i> Add New Loan</a>
        </div>

        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $stats->active_loans ?></div>
                    <div class="stat-lbl">Active Loans</div>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon"><i class="fa-solid fa-money-check-dollar"></i></div>
                <div class="stat-info">
                    <div class="stat-num">Rs. <?= number_format($stats->total_principal, 2) ?></div>
                    <div class="stat-lbl">Total Principal</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <div class="stat-info">
                    <div class="stat-num">Rs. <?= number_format($stats->outstanding_principal, 2) ?></div>
                    <div class="stat-lbl">Outstanding Balance</div>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
                <div class="stat-info">
                    <div class="stat-num">Rs. <?= number_format($stats->paid_interest, 2) ?></div>
                    <div class="stat-lbl">Total Interest Paid</div>
                </div>
            </div>
        </div>

        <div class="card-apple">
            <table class="table-apple">
                <thead>
                    <tr>
                        <th>Loan #</th>
                        <th>Lender / Bank</th>
                        <th>Start Date</th>
                        <th style="text-align: right;">Principal</th>
                        <th style="text-align: right;">Balance</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loans)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--t-tertiary); padding: 40px;">No loans found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                        <tr>
                            <td style="font-family: var(--f-mono); font-weight: 500; color: var(--t-secondary);"><?= htmlspecialchars($loan->loan_number ?: 'N/A') ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($loan->lender_name) ?></td>
                            <td><?= date('d M Y', strtotime($loan->loan_start_date)) ?></td>
                            <td style="text-align: right; font-family: var(--f-mono);">Rs. <?= number_format($loan->principal_amount, 2) ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 600;">Rs. <?= number_format($loan->principal_balance, 2) ?></td>
                            <td>
                                <?php if ($loan->status == 'Active'): ?>
                                    <span class="badge-apple badge-success">Active</span>
                                <?php elseif ($loan->status == 'Pending'): ?>
                                    <span class="badge-apple badge-warning">Pending</span>
                                <?php elseif ($loan->status == 'Closed'): ?>
                                    <span class="badge-apple badge-secondary">Closed</span>
                                <?php else: ?>
                                    <span class="badge-apple badge-secondary"><?= htmlspecialchars($loan->status) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-menu">
                                    <button class="action-btn"><i class="fa-solid fa-ellipsis"></i></button>
                                    <div class="dropdown-content">
                                        <a href="<?= APP_URL ?>/loan/show/<?= $loan->id ?>"><i class="fa-regular fa-eye"></i> View Details</a>
                                        <a href="<?= APP_URL ?>/loan/edit/<?= $loan->id ?>"><i class="fa-regular fa-pen-to-square"></i> Edit Loan</a>
                                        <form action="<?= APP_URL ?>/loan/delete/<?= $loan->id ?>" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this loan? This action cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <button type="submit" class="text-danger"><i class="fa-regular fa-trash-can"></i> Delete Loan</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
