<?php require APP_ROOT . '/app/Views/layouts/header.php'; ?>
<?php $preview = $data['preview']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payroll Preview</h2>
    <a href="<?= APP_URL ?>/payroll" class="btn btn-outline-secondary">Cancel</a>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Period</h6>
                <h5><?= date('M d', strtotime($preview['period_start'])) ?> - <?= date('M d, Y', strtotime($preview['period_end'])) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h6>Total Gross</h6>
                <h5><?= number_format($preview['total_gross'], 2) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h6>Total Deductions</h6>
                <h5><?= number_format($preview['total_deductions'], 2) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h6>Total Net (Payable)</h6>
                <h5><?= number_format($preview['total_net'], 2) ?></h5>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Payslips Breakdown</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th class="text-end">Base Salary</th>
                    <th class="text-end text-success">+ Allowances</th>
                    <th class="text-end text-success">+ Comms/OT</th>
                    <th class="text-end fw-bold">Gross</th>
                    <th class="text-end text-danger">- Loan Ded.</th>
                    <th class="text-end text-danger">- Other Ded.</th>
                    <th class="text-end fw-bold text-success">Net Pay</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($preview['slips'] as $slip): ?>
                    <tr>
                        <td>
                            <a href="<?= APP_URL ?>/user/show/<?= $slip['employee_id'] ?>"><?= htmlspecialchars($slip['employee_name']) ?></a>
                            <?php if(!empty($slip['loan_details'])): ?>
                                <br><small class="text-muted"><i class="bi bi-info-circle"></i> Has active loan deductions</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= number_format($slip['base_salary'], 2) ?></td>
                        <td class="text-end text-success"><?= number_format($slip['allowances'], 2) ?></td>
                        <td class="text-end text-success"><?= number_format($slip['commissions'] + $slip['overtime'], 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($slip['gross_salary'], 2) ?></td>
                        <td class="text-end text-danger"><?= number_format($slip['loan_deduction'], 2) ?></td>
                        <td class="text-end text-danger"><?= number_format($slip['other_deductions'], 2) ?></td>
                        <td class="text-end fw-bold text-success"><?= number_format($slip['net_salary'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-body text-end">
        <form action="<?= APP_URL ?>/payroll/run" method="POST">
            <input type="hidden" name="action" value="run_payroll">
            <input type="hidden" name="period_start" value="<?= $preview['period_start'] ?>">
            <input type="hidden" name="period_end" value="<?= $preview['period_end'] ?>">
            <input type="hidden" name="run_date" value="<?= $data['run_date'] ?>">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle"></i> Confirm & Save as Draft
            </button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
