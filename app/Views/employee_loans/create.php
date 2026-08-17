<?php require APP_ROOT . '/app/Views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>New Employee Loan</h2>
    <a href="<?= APP_URL ?>/employeeloan" class="btn btn-outline-secondary">Back to Loans</a>
</div>

<?php if(!empty($data['error'])): ?>
    <div class="alert alert-danger"><?= $data['error'] ?></div>
<?php endif; ?>

<div class="card max-w-800 mx-auto">
    <div class="card-body">
        <form action="<?= APP_URL ?>/employeeloan/create" method="POST">
            <div class="mb-3">
                <label class="form-label">Employee *</label>
                <select name="employee_id" class="form-select select2" required>
                    <option value="">-- Select Employee --</option>
                    <?php foreach($data['employees'] as $emp): ?>
                        <option value="<?= $emp->id ?>"><?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name . ' (' . $emp->employee_code . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loan/Ref Number</label>
                    <input type="text" name="loan_number" class="form-control" value="EL-<?= time() ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Principal Amount *</label>
                    <input type="number" step="0.01" name="principal_amount" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="loan_start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Term (Months) *</label>
                    <input type="number" name="loan_term_months" class="form-control" required value="12">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Repayment Frequency</label>
                    <select name="repayment_frequency" class="form-select">
                        <option value="Monthly">Monthly (Payroll Deduction)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Repayment Amount per Period *</label>
                    <input type="number" step="0.01" name="repayment_amount" class="form-control" required>
                    <small class="text-muted">This amount will be automatically deducted during payroll.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Interest Rate (%) (Optional)</label>
                <input type="number" step="0.01" name="interest_rate" class="form-control" value="0.00">
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
