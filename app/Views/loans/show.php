<?php require APP_ROOT . '/views/layouts/header.php'; ?>
<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= APP_URL ?>/loan" class="text-muted text-decoration-none mb-2 d-inline-block"><i class="ph ph-arrow-left"></i> Back to Loans</a>
            <h1 class="page-title">Loan: <?= htmlspecialchars($loan->lender_name) ?></h1>
            <p class="text-muted mb-0">Loan Number: <?= htmlspecialchars($loan->loan_number ?: 'N/A') ?></p>
        </div>
        <div>
            <?php if ($loan->status == 'Pending'): ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#disburseModal"><i class="ph ph-bank"></i> Disburse Loan (Receive Funds)</button>
            <?php elseif ($loan->status == 'Active'): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#repayModal"><i class="ph ph-money"></i> Add Repayment</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success mt-3">Action completed successfully.</div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white pt-4 pb-0 border-0">
                    <h5 class="mb-0">Repayment History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th class="text-end">Principal</th>
                                    <th class="text-end">Interest</th>
                                    <th class="text-end">Fees</th>
                                    <th class="text-end text-primary">Total Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($repayments)): ?>
                                    <tr><td colspan="6" class="text-center py-4">No repayments recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach($repayments as $rep): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($rep->payment_date)) ?></td>
                                        <td><?= htmlspecialchars($rep->reference ?: '-') ?></td>
                                        <td class="text-end text-danger">Rs. <?= number_format($rep->principal_amount, 2) ?></td>
                                        <td class="text-end text-warning">Rs. <?= number_format($rep->interest_amount, 2) ?></td>
                                        <td class="text-end">Rs. <?= number_format($rep->bank_charges, 2) ?></td>
                                        <td class="text-end text-primary fw-bold">Rs. <?= number_format($rep->total_payment, 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 bg-light rounded-3">
                    <h5 class="mb-4">Loan Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="fw-bold">
                            <?php if ($loan->status == 'Active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif ($loan->status == 'Pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($loan->status == 'Closed'): ?>
                                <span class="badge bg-secondary">Closed</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($loan->status) ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Principal</span>
                        <span class="fw-bold text-dark">Rs. <?= number_format($loan->principal_amount, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Interest Rate</span>
                        <span class="fw-bold text-dark"><?= $loan->interest_rate ?>%</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Principal Paid</span>
                        <span class="fw-bold text-success">Rs. <?= number_format($loan->total_principal_paid, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Interest Paid</span>
                        <span class="fw-bold text-warning">Rs. <?= number_format($loan->total_interest_paid, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-muted fs-5">Balance</span>
                        <span class="fw-bold text-danger fs-5">Rs. <?= number_format($loan->principal_balance, 2) ?></span>
                    </div>
                    
                    <?php if ($loan->principal_amount > 0): ?>
                    <div class="progress mt-4" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($loan->total_principal_paid / $loan->principal_amount) * 100 ?>%;"></div>
                    </div>
                    <small class="text-muted text-center d-block mt-2"><?= round(($loan->total_principal_paid / $loan->principal_amount) * 100) ?>% Repaid</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Loan Details</h6>
                    <ul class="list-unstyled mb-0 text-muted" style="font-size: 14px;">
                        <li class="mb-2"><strong>Start Date:</strong> <?= date('d M Y', strtotime($loan->loan_start_date)) ?></li>
                        <li class="mb-2"><strong>Maturity Date:</strong> <?= $loan->maturity_date ? date('d M Y', strtotime($loan->maturity_date)) : 'N/A' ?></li>
                        <li class="mb-2"><strong>Term:</strong> <?= $loan->loan_term_months ? $loan->loan_term_months . ' Months' : 'N/A' ?></li>
                        <li class="mb-2"><strong>Frequency:</strong> <?= $loan->repayment_frequency ?></li>
                        <li><strong>Notes:</strong> <?= nl2br(htmlspecialchars($loan->notes)) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disburse Modal -->
<div class="modal fade" id="disburseModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="<?= APP_URL ?>/loan/disburse/<?= $loan->id ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-header">
                <h5 class="modal-title">Disburse Loan Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will activate the loan and create the opening journal entry.</p>
                <div class="mb-3">
                    <label class="form-label">Deposit Into Bank Account <span class="text-danger">*</span></label>
                    <select name="bank_account_id" class="form-select" required>
                        <option value="">-- Select Bank Account --</option>
                        <?php foreach($bank_accounts as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->bank_name . ' (' . $acc->account_number . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Processing Fees Deducted (Rs.)</label>
                    <input type="number" step="0.01" name="processing_fees" class="form-control" value="0.00">
                    <small class="text-muted">If the bank deducted fees from the principal before depositing, enter it here.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Disbursement</button>
            </div>
        </form>
    </div>
</div>

<!-- Repay Modal -->
<div class="modal fade" id="repayModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="<?= APP_URL ?>/loan/repay/<?= $loan->id ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-header">
                <h5 class="modal-title">Record Repayment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pay From Bank Account <span class="text-danger">*</span></label>
                    <select name="bank_account_id" class="form-select" required>
                        <option value="">-- Select Bank Account --</option>
                        <?php foreach($bank_accounts as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->bank_name . ' (' . $acc->account_number . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Principal Paid</label>
                        <input type="number" step="0.01" name="principal_amount" id="rep_principal" class="form-control" value="0.00" oninput="calcRepayTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Interest Paid</label>
                        <input type="number" step="0.01" name="interest_amount" id="rep_interest" class="form-control" value="0.00" oninput="calcRepayTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bank Charges</label>
                        <input type="number" step="0.01" name="bank_charges" id="rep_charges" class="form-control" value="0.00" oninput="calcRepayTotal()">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Payment <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="rep_total" class="form-control form-control-lg text-primary fw-bold" readonly value="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference" class="form-control" placeholder="Cheque No, Transfer Ref, etc">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Repayment</button>
            </div>
        </form>
    </div>
</div>

<script>
function calcRepayTotal() {
    let p = parseFloat(document.getElementById('rep_principal').value) || 0;
    let i = parseFloat(document.getElementById('rep_interest').value) || 0;
    let c = parseFloat(document.getElementById('rep_charges').value) || 0;
    document.getElementById('rep_total').value = (p + i + c).toFixed(2);
}
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
