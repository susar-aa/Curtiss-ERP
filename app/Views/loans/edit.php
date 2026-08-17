<?php
$loan = $data['loan'] ?? null;
$liabilities = $data['liabilities'] ?? [];
$flashError = $_GET['error'] ?? null;

$isPending = ($loan && $loan->status === 'Pending');
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE
   ============================================================ */
:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-separator:    rgba(60,60,67,0.12);
    --c-blue:         #007aff;
    --c-red:          #ff3b30;
    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-label:     #8e8e93;
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --r-md: 14px;
    --r-pill: 999px;
    --dur-fast:    0.18s;
}

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #121212;
        --c-surface:      #1e1e2e;
        --c-separator:    rgba(255,255,255,0.15);
        --t-primary:      #f5f5f7;
        --t-secondary:    #a1a1aa;
    }
}

.loan-root { font-family: var(--f-system); font-size: 15px; color: var(--t-primary); background: var(--c-bg); -webkit-font-smoothing: antialiased; }
.loan-wrap { max-width: 800px; margin: 0 auto; padding: 40px 24px 100px; }
.loan-header { margin-bottom: 28px; }
.loan-title { font-size: 32px; font-weight: 700; letter-spacing: -0.03em; color: var(--t-primary); margin-bottom: 4px; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; color: var(--c-blue); text-decoration: none; font-weight: 600; margin-bottom: 16px; }

.card-apple {
    background: var(--c-surface); border-radius: var(--r-md); box-shadow: var(--shadow-sm); border: 0.5px solid var(--c-separator); padding: 32px;
}

.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: var(--t-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em; }
.form-control, .form-select {
    width: 100%; padding: 12px 16px; font-size: 15px; font-family: var(--f-system);
    border: 1px solid var(--c-separator); border-radius: 8px; background: var(--c-bg);
    color: var(--t-primary); transition: border-color var(--dur-fast);
}
.form-control:focus, .form-select:focus { outline: none; border-color: var(--c-blue); background: var(--c-surface); }
.form-control:read-only { background: var(--c-separator); cursor: not-allowed; opacity: 0.7; }

.btn-apple {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    background: var(--c-blue); color: #fff; border: none; border-radius: var(--r-pill);
    padding: 12px 24px; font-size: 16px; font-weight: 600; transition: all var(--dur-fast); cursor: pointer; width: 100%;
}
.btn-apple:hover { background: #0062cc; transform: translateY(-1px); }

.sf-alert { padding:15px; border-radius:var(--r-md); margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
.sf-alert.error { background: #fff0ef; color: var(--c-red); }
</style>

<div class="loan-root">
    <div class="loan-wrap">
        <a href="<?= APP_URL ?>/loan" class="btn-back"><i class="fa-solid fa-chevron-left"></i> Back to Loans</a>
        
        <div class="loan-header">
            <h1 class="loan-title">Edit Loan</h1>
        </div>

        <?php if ($flashError): ?>
        <div class="sf-alert error"><i class="fa-solid fa-circle-exclamation"></i> Failed to update loan. Please check your inputs.</div>
        <?php endif; ?>

        <?php if (!$isPending): ?>
        <div class="sf-alert" style="background: rgba(255, 149, 0, 0.1); color: var(--c-orange);"><i class="fa-solid fa-triangle-exclamation"></i> <strong>Notice:</strong> Financial details cannot be edited because this loan is already active or closed.</div>
        <?php endif; ?>

        <div class="card-apple">
            <form action="<?= APP_URL ?>/loan/edit/<?= $loan->id ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Lender / Bank Name <span style="color:var(--c-red)">*</span></label>
                        <input type="text" name="lender_name" class="form-control" required value="<?= htmlspecialchars($loan->lender_name) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Loan Number (Optional)</label>
                        <input type="text" name="loan_number" class="form-control" value="<?= htmlspecialchars($loan->loan_number) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Liability Account (Chart of Accounts) <span style="color:var(--c-red)">*</span></label>
                    <select name="liability_account_id" class="form-select" required <?= !$isPending ? 'style="pointer-events:none; background:var(--c-separator);"' : '' ?>>
                        <option value="">-- Select Liability Account --</option>
                        <?php foreach ($liabilities as $acc): ?>
                            <option value="<?= $acc->id ?>" <?= $loan->liability_account_id == $acc->id ? 'selected' : '' ?>><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$isPending): ?>
                        <input type="hidden" name="liability_account_id" value="<?= $loan->liability_account_id ?>">
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Principal Amount (Rs.) <span style="color:var(--c-red)">*</span></label>
                        <input type="number" step="0.01" name="principal_amount" class="form-control" required value="<?= htmlspecialchars($loan->principal_amount) ?>" <?= !$isPending ? 'readonly' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Annual Interest Rate (%) <span style="color:var(--c-red)">*</span></label>
                        <input type="number" step="0.01" name="interest_rate" class="form-control" required value="<?= htmlspecialchars($loan->interest_rate) ?>" <?= !$isPending ? 'readonly' : '' ?>>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Loan Start Date <span style="color:var(--c-red)">*</span></label>
                        <input type="date" name="loan_start_date" class="form-control" required value="<?= htmlspecialchars($loan->loan_start_date) ?>" <?= !$isPending ? 'readonly' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Term (Months) <span style="color:var(--c-red)">*</span></label>
                        <input type="number" name="loan_term_months" class="form-control" required value="<?= htmlspecialchars($loan->loan_term_months) ?>" <?= !$isPending ? 'readonly' : '' ?>>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Repayment Frequency <span style="color:var(--c-red)">*</span></label>
                        <select name="repayment_frequency" class="form-select" required <?= !$isPending ? 'style="pointer-events:none; background:var(--c-separator);"' : '' ?>>
                            <option value="Monthly" <?= $loan->repayment_frequency == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="Quarterly" <?= $loan->repayment_frequency == 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                            <option value="Annually" <?= $loan->repayment_frequency == 'Annually' ? 'selected' : '' ?>>Annually</option>
                            <option value="One-Time" <?= $loan->repayment_frequency == 'One-Time' ? 'selected' : '' ?>>One-Time (Bullet)</option>
                        </select>
                        <?php if (!$isPending): ?>
                            <input type="hidden" name="repayment_frequency" value="<?= htmlspecialchars($loan->repayment_frequency) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">First Payment Date</label>
                        <input type="date" name="first_payment_date" class="form-control" value="<?= htmlspecialchars($loan->first_payment_date) ?>" <?= !$isPending ? 'readonly' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Maturity Date</label>
                        <input type="date" name="maturity_date" class="form-control" value="<?= htmlspecialchars($loan->maturity_date) ?>" <?= !$isPending ? 'readonly' : '' ?>>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" style="resize:vertical;"><?= htmlspecialchars($loan->notes) ?></textarea>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-apple">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
