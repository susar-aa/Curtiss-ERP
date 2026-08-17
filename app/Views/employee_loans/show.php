

<?php
$loan = $data['loan'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Employee Loan: <?= htmlspecialchars($loan->loan_number) ?></h2>
    <a href="<?= APP_URL ?>/employeeloan" class="btn btn-outline-secondary">Back</a>
</div>

<?php if(!empty($data['success'])): ?>
    <div class="alert alert-success"><?= $data['success'] ?></div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div class="alert alert-danger"><?= $data['error'] ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Loan Details</div>
            <div class="card-body">
                <p><strong>Employee:</strong> <a href="<?= APP_URL ?>/user/show/<?= $loan->employee_id ?>"><?= htmlspecialchars($loan->employee_name) ?></a></p>
                <p><strong>Principal Amount:</strong> <?= number_format($loan->principal_amount, 2) ?></p>
                <p><strong>Total Repaid:</strong> <?= number_format($loan->total_principal_paid, 2) ?></p>
                <p class="text-danger"><strong>Remaining Balance:</strong> <?= number_format($loan->principal_balance, 2) ?></p>
                <hr>
                <p><strong>Start Date:</strong> <?= date('M d, Y', strtotime($loan->loan_start_date)) ?></p>
                <p><strong>Term:</strong> <?= $loan->loan_term_months ?> Months</p>
                <p><strong>Repayment / Period:</strong> <?= number_format($loan->repayment_amount, 2) ?> (<?= $loan->repayment_frequency ?>)</p>
                <p><strong>Status:</strong> <span class="badge bg-primary"><?= $loan->status ?></span></p>
            </div>
        </div>

        <?php if($loan->status == 'Pending'): ?>
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">Approve Loan</div>
                <div class="card-body">
                    <form action="<?= APP_URL ?>/employeeloan/approve/<?= $loan->id ?>" method="POST">
                        <button type="submit" class="btn btn-info w-100 text-white" onclick="return confirm('Approve this loan application?')">Approve Application</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if($loan->status == 'Approved'): ?>
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">Disburse Loan</div>
                <div class="card-body">
                    <form action="<?= APP_URL ?>/employeeloan/disburse/<?= $loan->id ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Pay From Bank Account</label>
                            <select name="bank_account_id" class="form-select" required>
                                <?php foreach($data['banks'] as $b): ?>
                                    <option value="<?= $b->id ?>"><?= $b->account_name ?> (<?= $b->account_code ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Disburse loan and create journal entries?')">Mark as Disbursed</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Repayment History</span>
                <?php if($loan->status == 'Active'): ?>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manualRepayModal">Manual Repayment</button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
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
                            <tr><td colspan="4" class="text-center text-muted py-3">No repayments recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['repayments'] as $rep): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($rep->payment_date)) ?></td>
                                    <td class="text-success">+ <?= number_format($rep->principal_amount, 2) ?></td>
                                    <td><?= number_format($rep->interest_amount, 2) ?></td>
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

<!-- Manual Repayment Modal -->
<div class="modal fade" id="manualRepayModal" tabindex="-1">
    <div class="modal-dialog">
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
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Process Repayment</button>
            </div>
        </form>
    </div>
</div>


