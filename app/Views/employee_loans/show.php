

<?php
$loan = $data['loan'];
?>
<style>
    .hrm-container { padding: 24px; }
    .header-actions { 
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
    }
    .header-title-wrap h2 {
        font-size: 22px; font-weight: 700; color: var(--text-main); margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .btn { 
        padding: 10px 20px; background: var(--text-accent); color: #fff !important; 
        border: none; border-radius: 12px; cursor: pointer; text-decoration: none; 
        font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; justify-content: center;
        transition: background 0.2s, transform 0.15s;
    }
    .btn:hover { background: var(--text-accent-light); transform: translateY(-1px); }
    .btn-outline { 
        background: transparent; border: 1px solid var(--glass-border); 
        color: var(--text-main) !important; 
    }
    .btn-outline:hover { background: rgba(255, 255, 255, 0.08); }
    
    .btn-success-solid { background: #10b981; color:#fff !important; }
    .btn-success-solid:hover { background: #059669; }
    .btn-info-solid { background: #3b82f6; color:#fff !important; }
    .btn-info-solid:hover { background: #2563eb; }
    
    @media (prefers-color-scheme: dark) {
        .btn-outline:hover { background: rgba(255, 255, 255, 0.04); }
    }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { 
        padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--glass-border); 
    }
    .data-table th { 
        background-color: rgba(0, 0, 0, 0.03); font-weight: 600; font-size: 12px; 
        text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);
    }
    @media (prefers-color-scheme: dark) {
        .data-table th { background-color: rgba(255, 255, 255, 0.02); }
    }
    .data-table td { font-size: 13.5px; color: var(--text-main); }
    .data-table tr { transition: background 0.15s; }
    .data-table tr:hover { background-color: rgba(0, 0, 0, 0.015); }
    @media (prefers-color-scheme: dark) {
        .data-table tr:hover { background-color: rgba(255, 255, 255, 0.02); }
    }
    .status-badge {
        padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
    }
    .status-draft { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
    .status-active { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

    .modal-content {
        background: var(--card-bg); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--card-border); border-radius: 20px;
        color: var(--text-main);
    }
    .modal-header { border-bottom: 1px solid var(--glass-border); padding: 20px 24px; }
    .modal-title { font-weight: 700; font-size: 18px; }
    .modal-body { padding: 24px; }
    .modal-footer { border-top: 1px solid var(--glass-border); padding: 20px 24px; }
    
    .form-control, .form-select {
        background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); color: var(--text-main);
        border-radius: 10px; padding: 10px 14px; transition: all 0.2s; width: 100%;
        box-sizing: border-box; font-family: inherit; font-size: 13.5px;
    }
    .form-control:focus, .form-select:focus {
        background: rgba(0,0,0,0.05); border-color: var(--text-accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.15); outline: none;
    }
    @media (prefers-color-scheme: dark) {
        .form-control, .form-select { background: rgba(255,255,255,0.03); }
        .form-control:focus, .form-select:focus { background: rgba(255,255,255,0.05); }
        .form-select option { background: var(--bg-color); color: var(--text-main); }
    }
    .form-label { font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block; }
    .btn-close { filter: invert(var(--close-invert, 0)); }
    @media (prefers-color-scheme: dark) { :root { --close-invert: 1; } }
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-hand-coins"></i> Employee Loan: <?= htmlspecialchars($loan->loan_number) ?></h2>
        </div>
        <a href="<?= APP_URL ?>/employeeloan" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back
        </a>
    </div>

    <?php if(!empty($data['success'])): ?>
        <div class="alert alert-success" style="border-radius:12px; border:none; background: rgba(16,185,129,0.15); color: #10b981; font-weight:600;"><i class="ph ph-check-circle"></i> <?= $data['success'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-danger" style="border-radius:12px; border:none; background: rgba(239,68,68,0.15); color: #ef4444; font-weight:600;"><i class="ph ph-warning-circle"></i> <?= $data['error'] ?></div>
    <?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="glass-card mb-4" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <h5 style="font-weight: 700; margin-bottom: 16px;">Loan Details</h5>
            <div style="line-height: 1.8; font-size: 14px;">
                <p><strong>Employee:</strong> <a href="<?= APP_URL ?>/user/show/<?= $loan->employee_id ?>" style="color:var(--text-accent); font-weight:600; text-decoration:none;"><?= htmlspecialchars($loan->employee_name) ?></a></p>
                <p><strong>Principal Amount:</strong> $<?= number_format($loan->principal_amount, 2) ?></p>
                <p><strong>Total Repaid:</strong> <span style="color:#10b981; font-weight:600;">$<?= number_format($loan->total_principal_paid, 2) ?></span></p>
                <p style="color:#ef4444;"><strong>Remaining Balance:</strong> $<?= number_format($loan->principal_balance, 2) ?></p>
                <hr style="border-color: var(--glass-border); margin: 16px 0;">
                <p><strong>Start Date:</strong> <?= date('M d, Y', strtotime($loan->loan_start_date)) ?></p>
                <p><strong>Term:</strong> <?= $loan->loan_term_months ?> Months</p>
                <p><strong>Repayment:</strong> $<?= number_format($loan->repayment_amount, 2) ?> / <?= $loan->repayment_frequency ?></p>
                <p><strong>Status:</strong> 
                    <?php 
                        $lstatusClass = 'status-draft';
                        if($loan->status == 'Active') $lstatusClass = 'status-active';
                        if($loan->status == 'Approved') $lstatusClass = 'status-active';
                        if($loan->status == 'Closed') $lstatusClass = 'status-paid';
                        if($loan->status == 'Rejected') $lstatusClass = 'status-rejected';
                    ?>
                    <span class="status-badge <?= $lstatusClass ?>"><?= $loan->status ?></span>
                </p>
            </div>
        </div>

        <?php if($loan->status == 'Pending'): ?>
            <div class="glass-card mb-4" style="background: rgba(59,130,246,0.05); border-radius: 20px; padding: 24px; border: 1px solid rgba(59,130,246,0.2);">
                <h5 style="font-weight: 700; margin-bottom: 16px; color:#3b82f6;">Approve Loan</h5>
                <form action="<?= APP_URL ?>/employeeloan/approve/<?= $loan->id ?>" method="POST">
                    <button type="submit" class="btn btn-info-solid w-100" onclick="return confirm('Approve this loan application?')">Approve Application</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if($loan->status == 'Approved'): ?>
            <div class="glass-card mb-4" style="background: rgba(16,185,129,0.05); border-radius: 20px; padding: 24px; border: 1px solid rgba(16,185,129,0.2);">
                <h5 style="font-weight: 700; margin-bottom: 16px; color:#10b981;">Disburse Loan</h5>
                <form action="<?= APP_URL ?>/employeeloan/disburse/<?= $loan->id ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Pay From Bank Account</label>
                        <select name="bank_account_id" class="form-select" required>
                            <?php foreach($data['banks'] as $b): ?>
                                <option value="<?= $b->id ?>"><?= $b->account_name ?> (<?= $b->account_code ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success-solid w-100" onclick="return confirm('Disburse loan and create journal entries?')">Mark as Disbursed</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="glass-card mb-4" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="font-weight: 700; margin: 0;">Repayment History</h5>
                <?php if($loan->status == 'Active'): ?>
                    <button class="btn btn-outline" style="font-size:12px; padding:6px 12px;" data-bs-toggle="modal" data-bs-target="#manualRepayModal"><i class="ph ph-plus"></i> Manual Repayment</button>
                <?php endif; ?>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Principal Paid</th>
                            <th>Interest Paid</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['repayments'])): ?>
                            <tr><td colspan="4" class="text-center" style="color:var(--text-muted); padding:30px;">No repayments recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['repayments'] as $rep): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($rep->payment_date)) ?></td>
                                    <td style="color:#10b981; font-weight:600;">+$<?= number_format($rep->principal_amount, 2) ?></td>
                                    <td>$<?= number_format($rep->interest_amount, 2) ?></td>
                                    <td><?= htmlspecialchars($rep->notes) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Manual Repayment Modal -->
<div class="modal fade" id="manualRepayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= APP_URL ?>/employeeloan/repay/<?= $loan->id ?>" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Manual Loan Repayment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deposit to Bank Account</label>
                    <select name="bank_account_id" class="form-select" required>
                        <?php foreach($data['banks'] as $b): ?>
                            <option value="<?= $b->id ?>"><?= $b->account_name ?> (<?= $b->account_code ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Principal Amount</label>
                    <input type="number" step="0.01" name="principal_amount" class="form-control" required max="<?= $loan->principal_balance ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Interest Amount</label>
                    <input type="number" step="0.01" name="interest_amount" class="form-control" value="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" style="background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); color: var(--text-main); border-radius: 10px; padding: 10px 14px;" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success-solid">Process Repayment</button>
            </div>
        </form>
    </div>
</div>


