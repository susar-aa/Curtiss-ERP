<?php
$loan = $data['loan'] ?? null;
$repayments = $data['repayments'] ?? [];
$bank_accounts = $data['bank_accounts'] ?? [];
$flashSuccess = $_GET['success'] ?? null;
$flashError = $_GET['error'] ?? null;

if (!$loan) {
    echo "<div style='padding:40px; text-align:center;'>Loan not found.</div>";
    return;
}
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
    --c-surface2:     #f9f9fb;
    --c-separator:    rgba(60,60,67,0.12);
    --c-blue:         #007aff;
    --c-green:        #34c759;
    --c-orange:       #ff9500;
    --c-red:          #ff3b30;
    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;
    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-label:     #8e8e93;
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --r-md: 14px;
    --r-lg: 20px;
    --r-pill: 999px;
    --dur-fast:    0.18s;
}

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #121212;
        --c-surface:      #1e1e2e;
        --c-surface2:     #161622;
        --c-separator:    rgba(255,255,255,0.15);
        --t-primary:      #f5f5f7;
        --t-secondary:    #a1a1aa;
    }
}

.loan-root { font-family: var(--f-system); font-size: 15px; color: var(--t-primary); background: var(--c-bg); -webkit-font-smoothing: antialiased; }
.loan-wrap { max-width: 1420px; margin: 0 auto; padding: 20px 24px 100px; }

.loan-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; }
.loan-title { font-size: 32px; font-weight: 700; letter-spacing: -0.03em; color: var(--t-primary); margin-bottom: 4px; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; color: var(--c-blue); text-decoration: none; font-weight: 600; margin-bottom: 16px; }

.btn-apple {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--c-blue); color: #fff; border: none; border-radius: var(--r-pill);
    padding: 10px 20px; font-size: 14px; font-weight: 600; transition: all var(--dur-fast); cursor: pointer; text-decoration: none;
}
.btn-apple.success { background: var(--c-green); }
.btn-apple.secondary { background: #e5e5ea; color: var(--t-primary); }
@media (prefers-color-scheme: dark) { .btn-apple.secondary { background: #2c2c2e; color: #fff; } }
.btn-apple:hover { filter: brightness(0.9); transform: translateY(-1px); }

.card-apple {
    background: var(--c-surface); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); border: 0.5px solid var(--c-separator); overflow: hidden; padding: 24px; margin-bottom: 24px;
}
.card-apple-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: var(--t-primary); }

.info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
.info-label { color: var(--t-secondary); }
.info-value { font-weight: 600; color: var(--t-primary); }
.info-value.mono { font-family: var(--f-mono); }

.sf-alert { padding:15px; border-radius:var(--r-md); margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
.sf-alert.success { background: #e6f9ec; color: var(--c-green); }
.sf-alert.error { background: #fff0ef; color: var(--c-red); }

.table-apple { width: 100%; border-collapse: collapse; }
.table-apple th { background: var(--c-surface2); padding: 12px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--t-label); border-bottom: 1px solid var(--c-separator); text-align: left; }
.table-apple td { padding: 16px; border-bottom: 0.5px solid var(--c-separator); font-size: 14px; color: var(--t-primary); }
.table-apple tr:last-child td { border-bottom: none; }

.badge-apple { padding: 4px 10px; border-radius: var(--r-pill); font-size: 12px; font-weight: 600; }
.badge-success { background: #e6f9ec; color: var(--c-green); }
.badge-warning { background: #fff4e5; color: var(--c-orange); }
.badge-secondary { background: var(--c-surface2); color: var(--t-secondary); }

.progress-bar-container { background: var(--c-separator); height: 8px; border-radius: 4px; overflow: hidden; margin-top: 16px; }
.progress-bar-fill { background: var(--c-green); height: 100%; }

/* Modal Styles */
.modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-overlay.active { display: flex; }
.modal-content { background: var(--c-surface); border-radius: var(--r-lg); width: 100%; max-width: 500px; padding: 30px; box-shadow: var(--shadow-sm); }
.modal-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; }
.form-control, .form-select { width: 100%; padding: 12px 16px; font-size: 15px; border: 1px solid var(--c-separator); border-radius: 8px; background: var(--c-bg); color: var(--t-primary); margin-bottom: 20px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: var(--t-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em; }
</style>

<div class="loan-root">
    <div class="loan-wrap">
        <a href="<?= APP_URL ?>/loan" class="btn-back"><i class="fa-solid fa-chevron-left"></i> Back to Loans</a>
        
        <?php if ($flashSuccess): ?>
        <div class="sf-alert success"><i class="fa-solid fa-check-circle"></i> Action completed successfully.</div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="sf-alert error"><i class="fa-solid fa-circle-exclamation"></i> Action failed.</div>
        <?php endif; ?>

        <div class="loan-header">
            <div>
                <h1 class="loan-title">Loan: <?= htmlspecialchars($loan->lender_name) ?></h1>
                <p class="text-muted" style="margin:0;">Loan Number: <?= htmlspecialchars($loan->loan_number ?: 'N/A') ?></p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="<?= APP_URL ?>/loan/edit/<?= $loan->id ?>" class="btn-apple secondary"><i class="fa-solid fa-pen"></i> Edit Loan</a>
                <?php if ($loan->status == 'Pending'): ?>
                    <button class="btn-apple success" onclick="openModal('disburseModal')"><i class="fa-solid fa-building-columns"></i> Disburse Funds</button>
                <?php elseif ($loan->status == 'Active'): ?>
                    <button class="btn-apple" onclick="openModal('repayModal')"><i class="fa-solid fa-money-bill-wave"></i> Add Repayment</button>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <div>
                <div class="card-apple" style="padding:0;">
                    <div style="padding: 24px 24px 0;"><h3 class="card-apple-title">Repayment Schedule</h3></div>
                    <table class="table-apple">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th style="text-align: right;">Principal</th>
                                <th style="text-align: right;">Interest</th>
                                <th style="text-align: right;">Total Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($repayments)): ?>
                                <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--t-tertiary);">No repayments recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach($repayments as $rep): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($rep->payment_date)) ?></td>
                                    <td style="font-family: var(--f-mono);"><?= htmlspecialchars($rep->reference ?: '-') ?></td>
                                    <td style="text-align: right; font-family: var(--f-mono);">Rs. <?= number_format($rep->principal_amount, 2) ?></td>
                                    <td style="text-align: right; font-family: var(--f-mono);">Rs. <?= number_format($rep->interest_amount, 2) ?></td>
                                    <td style="text-align: right; font-family: var(--f-mono); font-weight:600;">Rs. <?= number_format($rep->principal_amount + $rep->interest_amount, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="card-apple">
                    <h3 class="card-apple-title">Summary</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <?php if ($loan->status == 'Active'): ?>
                                <span class="badge-apple badge-success">Active</span>
                            <?php elseif ($loan->status == 'Pending'): ?>
                                <span class="badge-apple badge-warning">Pending</span>
                            <?php elseif ($loan->status == 'Closed'): ?>
                                <span class="badge-apple badge-secondary">Closed</span>
                            <?php else: ?>
                                <span class="badge-apple badge-secondary"><?= htmlspecialchars($loan->status) ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Principal Amount</span>
                        <span class="info-value mono">Rs. <?= number_format($loan->principal_amount, 2) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Interest Rate</span>
                        <span class="info-value mono"><?= $loan->interest_rate ?>%</span>
                    </div>
                    
                    <hr style="border:0; border-top:1px solid var(--c-separator); margin: 16px 0;">
                    
                    <div class="info-row">
                        <span class="info-label">Principal Paid</span>
                        <span class="info-value mono" style="color:var(--c-green)">Rs. <?= number_format($loan->total_principal_paid, 2) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Interest Paid</span>
                        <span class="info-value mono" style="color:var(--c-orange)">Rs. <?= number_format($loan->total_interest_paid, 2) ?></span>
                    </div>
                    
                    <hr style="border:0; border-top:1px solid var(--c-separator); margin: 16px 0;">
                    
                    <div class="info-row" style="font-size:18px;">
                        <span class="info-label">Balance</span>
                        <span class="info-value mono" style="color:var(--c-red)">Rs. <?= number_format($loan->principal_balance, 2) ?></span>
                    </div>
                    
                    <?php if ($loan->principal_amount > 0): ?>
                    <?php $pct = round(($loan->total_principal_paid / $loan->principal_amount) * 100); ?>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                    </div>
                    <div style="text-align: center; font-size: 12px; color: var(--t-tertiary); margin-top: 8px; font-weight: 600;"><?= $pct ?>% Repaid</div>
                    <?php endif; ?>
                </div>

                <div class="card-apple">
                    <h3 class="card-apple-title">Loan Details</h3>
                    <div class="info-row"><span class="info-label">Start Date</span><span class="info-value"><?= date('d M Y', strtotime($loan->loan_start_date)) ?></span></div>
                    <div class="info-row"><span class="info-label">Maturity Date</span><span class="info-value"><?= $loan->maturity_date ? date('d M Y', strtotime($loan->maturity_date)) : 'N/A' ?></span></div>
                    <div class="info-row"><span class="info-label">Term</span><span class="info-value"><?= $loan->loan_term_months ? $loan->loan_term_months . ' Months' : 'N/A' ?></span></div>
                    <div class="info-row"><span class="info-label">Frequency</span><span class="info-value"><?= $loan->repayment_frequency ?></span></div>
                    
                    <?php if ($loan->notes): ?>
                    <hr style="border:0; border-top:1px solid var(--c-separator); margin: 16px 0;">
                    <div style="font-size: 13px; color: var(--t-secondary); line-height: 1.5;">
                        <strong style="display:block; margin-bottom:4px;">Notes:</strong>
                        <?= nl2br(htmlspecialchars($loan->notes)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disburse Modal -->
<div class="modal-overlay" id="disburseModal">
    <form class="modal-content" action="<?= APP_URL ?>/loan/disburse/<?= $loan->id ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <h5 class="modal-title">Disburse Loan Funds</h5>
        <p style="color:var(--t-secondary); font-size:14px; margin-bottom:20px;">This will activate the loan and create the opening journal entry.</p>
        
        <label class="form-label">Deposit Into Bank Account <span style="color:var(--c-red)">*</span></label>
        <select name="bank_account_id" class="form-select" required>
            <option value="">-- Select Bank Account --</option>
            <?php foreach($bank_accounts as $acc): ?>
                <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->bank_name . ' (' . $acc->account_number . ')') ?></option>
            <?php endforeach; ?>
        </select>
        
        <label class="form-label">Processing Fees (Optional)</label>
        <input type="number" step="0.01" name="processing_fees" class="form-control" placeholder="0.00">
        
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
            <button type="button" class="btn-apple secondary" onclick="closeModal('disburseModal')">Cancel</button>
            <button type="submit" class="btn-apple success">Confirm Disbursement</button>
        </div>
    </form>
</div>

<!-- Repay Modal -->
<div class="modal-overlay" id="repayModal">
    <form class="modal-content" action="<?= APP_URL ?>/loan/repay/<?= $loan->id ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <h5 class="modal-title">Record Repayment</h5>
        
        <label class="form-label">Payment Date <span style="color:var(--c-red)">*</span></label>
        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        
        <label class="form-label">Pay From Bank Account <span style="color:var(--c-red)">*</span></label>
        <select name="bank_account_id" class="form-select" required>
            <option value="">-- Select Bank Account --</option>
            <?php foreach($bank_accounts as $acc): ?>
                <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->bank_name . ' (' . $acc->account_number . ')') ?></option>
            <?php endforeach; ?>
        </select>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            <div>
                <label class="form-label">Principal Paid <span style="color:var(--c-red)">*</span></label>
                <input type="number" step="0.01" name="principal_amount" class="form-control" required placeholder="0.00">
            </div>
            <div>
                <label class="form-label">Interest Paid <span style="color:var(--c-red)">*</span></label>
                <input type="number" step="0.01" name="interest_amount" class="form-control" required placeholder="0.00">
            </div>
        </div>
        
        <label class="form-label">Reference / Cheque No</label>
        <input type="text" name="reference" class="form-control" placeholder="e.g. CHQ-882190">
        
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
            <button type="button" class="btn-apple secondary" onclick="closeModal('repayModal')">Cancel</button>
            <button type="submit" class="btn-apple">Save Repayment</button>
        </div>
    </form>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>
