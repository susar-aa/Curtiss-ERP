<?php require APP_ROOT . '/app/Views/layouts/header.php'; ?>
<?php 
$run = $data['run']; 
$slips = $data['slips'];

$totalGross = array_sum(array_column($slips, 'gross_salary'));
$totalNet = array_sum(array_column($slips, 'net_salary'));
$totalDed = array_sum(array_column($slips, 'loan_deduction')) + array_sum(array_column($slips, 'other_deductions'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payroll Run: PR-<?= $run->id ?></h2>
    <a href="<?= APP_URL ?>/payroll" class="btn btn-outline-secondary">Back to Payroll</a>
</div>

<?php if(!empty($data['success'])): ?>
    <div class="alert alert-success"><?= $data['success'] ?></div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div class="alert alert-danger"><?= $data['error'] ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Period</h6>
                <h5><?= date('M d', strtotime($run->period_start)) ?> - <?= date('M d, Y', strtotime($run->period_end)) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6>Total Gross</h6>
                <h5><?= number_format($totalGross, 2) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h6>Total Deductions</h6>
                <h5><?= number_format($totalDed, 2) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h6>Total Net (Payable)</h6>
                <h5><?= number_format($totalNet, 2) ?></h5>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Employee Payslips</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end text-danger">- Ded.</th>
                            <th class="text-end fw-bold text-success">Net Pay</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($slips as $slip): ?>
                            <tr>
                                <td>
                                    <a href="<?= APP_URL ?>/user/show/<?= $slip->employee_id ?>"><?= htmlspecialchars($slip->first_name . ' ' . $slip->last_name) ?></a>
                                </td>
                                <td class="text-end"><?= number_format($slip->gross_salary, 2) ?></td>
                                <td class="text-end text-danger"><?= number_format($slip->loan_deduction + $slip->other_deductions, 2) ?></td>
                                <td class="text-end fw-bold text-success"><?= number_format($slip->net_salary, 2) ?></td>
                                <td>
                                    <?php 
                                        $badge = 'secondary';
                                        if($slip->status == 'Approved') $badge = 'info';
                                        if($slip->status == 'Paid') $badge = 'success';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= $slip->status ?></span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/payroll/payslip/<?= $slip->id ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Print</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Run Status: <span class="badge bg-primary"><?= $run->status ?></span></div>
            <div class="card-body">
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
                        <button type="submit" class="btn btn-info w-100 text-white" onclick="return confirm('Approve payroll and generate Journal Entries?')">
                            <i class="bi bi-check2-circle"></i> Approve & Post
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
                            <small class="text-muted">Must be the same account used during approval.</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark as paid, generate Bank Journal Entry, and process loan deductions?')">
                            <i class="bi bi-cash-stack"></i> Mark as Paid
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if($run->status == 'Paid'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill"></i> This payroll run is fully processed and paid.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if($run->journal_entry_id): ?>
            <div class="card mb-4">
                <div class="card-header">Accounting</div>
                <div class="card-body">
                    <p>Journal Entry ID: <strong><?= $run->journal_entry_id ?></strong></p>
                    <a href="<?= APP_URL ?>/accounting/journal/<?= $run->journal_entry_id ?>" class="btn btn-outline-primary btn-sm">View Journal Entry</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
