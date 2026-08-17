
<?php 
$run = $data['run']; 
$slips = $data['slips'];

$totalGross = array_sum(array_column($slips, 'gross_salary'));
$totalNet = array_sum(array_column($slips, 'net_salary'));
$totalDed = array_sum(array_column($slips, 'loan_deduction')) + array_sum(array_column($slips, 'other_deductions'));
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
    .status-approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }

    .summary-card {
        background: var(--card-bg); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--card-border); border-radius: 20px;
        padding: 24px; text-align: center; box-shadow: var(--card-shadow);
        transition: transform 0.2s;
    }
    .summary-card:hover { transform: translateY(-3px); }
    .summary-card h6 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin: 0 0 8px 0; font-weight: 600; }
    .summary-card h5 { font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0; }
    
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
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-receipt"></i> Payroll Run: PR-<?= $run->id ?></h2>
        </div>
        <a href="<?= APP_URL ?>/payroll" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Payroll
        </a>
    </div>

<?php if(!empty($data['success'])): ?>
    <div class="alert alert-success"><?= $data['success'] ?></div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div class="alert alert-danger"><?= $data['error'] ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Period</h6>
            <h5><?= date('M d', strtotime($run->period_start)) ?> - <?= date('M d, Y', strtotime($run->period_end)) ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Total Gross</h6>
            <h5 style="color: #3b82f6;">$<?= number_format($totalGross, 2) ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Total Deductions</h6>
            <h5 style="color: #ef4444;">$<?= number_format($totalDed, 2) ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Total Net (Payable)</h6>
            <h5 style="color: #10b981;">$<?= number_format($totalNet, 2) ?></h5>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="glass-card mb-4" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <h5 style="font-weight: 700; margin-bottom: 16px;">Employee Payslips</h5>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end" style="color:#ef4444;">- Ded.</th>
                            <th class="text-end" style="color:#10b981;">Net Pay</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($slips as $slip): ?>
                            <tr>
                                <td style="font-weight:600;">
                                    <a href="<?= APP_URL ?>/user/show/<?= $slip->employee_id ?>" style="text-decoration: none; color: var(--text-accent);"><?= htmlspecialchars($slip->first_name . ' ' . $slip->last_name) ?></a>
                                </td>
                                <td class="text-end">$<?= number_format($slip->gross_salary, 2) ?></td>
                                <td class="text-end" style="color:#ef4444;">$<?= number_format($slip->loan_deduction + $slip->other_deductions, 2) ?></td>
                                <td class="text-end fw-bold" style="color:#10b981;">$<?= number_format($slip->net_salary, 2) ?></td>
                                <td>
                                    <?php 
                                        $statusClass = 'status-draft';
                                        if($slip->status == 'Approved') $statusClass = 'status-approved';
                                        if($slip->status == 'Paid') $statusClass = 'status-paid';
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>"><?= $slip->status ?></span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/payroll/payslip/<?= $slip->id ?>" target="_blank" class="btn btn-outline" style="padding:6px 12px; font-size:12px;"><i class="ph ph-printer"></i> Print</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="glass-card mb-4" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <h5 style="font-weight: 700; margin-bottom: 16px;">Run Status: 
                <?php 
                    $rsClass = 'status-draft';
                    if($run->status == 'Approved') $rsClass = 'status-approved';
                    if($run->status == 'Paid') $rsClass = 'status-paid';
                ?>
                <span class="status-badge <?= $rsClass ?>"><?= $run->status ?></span>
            </h5>
                <?php if($run->status == 'Draft'): ?>
                    <form action="<?= APP_URL ?>/payroll/approve/<?= $run->id ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Wage Expense Account</label>
                            <select name="wage_expense_account_id" class="form-select" required>
                                <?php foreach($data['expenses'] as $exp): ?>
                                    <option value="<?= $exp->id ?>" <?= stripos($exp->account_name, 'wage') !== false || stripos($exp->account_name, 'salary') !== false ? 'selected' : '' ?>><?= $exp->account_name ?> (<?= $exp->account_code ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salary Payable Account (Liability)</label>
                            <select name="salary_payable_account_id" class="form-select" required>
                                <?php foreach($data['liabilities'] as $lia): ?>
                                    <option value="<?= $lia->id ?>" <?= stripos($lia->account_name, 'payable') !== false || stripos($lia->account_name, 'salary') !== false ? 'selected' : '' ?>><?= $lia->account_name ?> (<?= $lia->account_code ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">A liability account to hold the net pay until paid.</small>
                        </div>
                        <button type="submit" class="btn btn-info-solid w-100" onclick="return confirm('Approve payroll and generate Journal Entries?')">
                            <i class="ph ph-check-circle"></i> Approve & Post
                        </button>
                    </form>
                <?php endif; ?>

                <?php if($run->status == 'Approved'): ?>
                    <form action="<?= APP_URL ?>/payroll/pay/<?= $run->id ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pay From Bank Account</label>
                            <select name="bank_account_id" class="form-select" required>
                                <?php foreach($data['banks'] as $b): ?>
                                    <option value="<?= $b->id ?>"><?= $b->account_name ?> (<?= $b->account_code ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salary Payable Account</label>
                            <select name="salary_payable_account_id" class="form-select" required>
                                <?php foreach($data['liabilities'] as $lia): ?>
                                    <option value="<?= $lia->id ?>" <?= stripos($lia->account_name, 'payable') !== false || stripos($lia->account_name, 'salary') !== false ? 'selected' : '' ?>><?= $lia->account_name ?> (<?= $lia->account_code ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:var(--text-muted); font-size:11px;">Must be the same account used during approval.</small>
                        </div>
                        <button type="submit" class="btn btn-success-solid w-100" onclick="return confirm('Mark as paid, generate Bank Journal Entry, and process loan deductions?')">
                            <i class="ph ph-money"></i> Mark as Paid
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if($run->status == 'Paid'): ?>
                    <div class="alert alert-success mb-0" style="background:rgba(16,185,129,0.15); border:none; color:#10b981; font-weight:600; border-radius:12px;">
                        <i class="ph ph-check-circle-fill"></i> This payroll run is fully processed and paid.
                    </div>
                <?php endif; ?>
            </div>
        
        <?php if($run->journal_entry_id): ?>
            <div class="glass-card mb-4" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
                <h5 style="font-weight: 700; margin-bottom: 16px;">Accounting</h5>
                <p>Journal Entry ID: <strong><?= $run->journal_entry_id ?></strong></p>
                <a href="<?= APP_URL ?>/accounting/journal/<?= $run->journal_entry_id ?>" class="btn btn-outline" style="width:100%;">View Journal Entry</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>


