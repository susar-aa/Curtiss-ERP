<?php require APP_ROOT . '/views/layouts/header.php'; ?>
<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="content">
    <div class="page-header">
        <a href="<?= APP_URL ?>/loan" class="text-muted text-decoration-none mb-2 d-inline-block"><i class="ph ph-arrow-left"></i> Back to Loans</a>
        <h1 class="page-title">Register New Bank Loan</h1>
    </div>

    <div class="card border-0 shadow-sm mt-4" style="max-width: 800px;">
        <div class="card-body p-4">
            <form action="<?= APP_URL ?>/loan/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <h5 class="mb-3 text-primary">Lender Details</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank / Lender Name <span class="text-danger">*</span></label>
                        <input type="text" name="lender_name" class="form-control" required placeholder="e.g. Commercial Bank">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loan Reference / Account No</label>
                        <input type="text" name="loan_number" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Liability Account (Chart of Accounts) <span class="text-danger">*</span></label>
                        <select name="liability_account_id" class="form-select" required>
                            <option value="">-- Select Liability Account --</option>
                            <?php foreach ($liabilities as $acc): ?>
                                <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">This is where the loan balance will be tracked in accounting.</small>
                    </div>
                </div>

                <h5 class="mb-3 text-primary">Loan Financials</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Principal Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="principal_amount" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="interest_rate" class="form-control" required placeholder="Annual rate">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Term (Months)</label>
                        <input type="number" name="loan_term_months" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Repayment Frequency</label>
                        <select name="repayment_frequency" class="form-select">
                            <option value="Monthly">Monthly</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <h5 class="mb-3 text-primary">Schedule</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Loan Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="loan_start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">First Payment Date</label>
                        <input type="date" name="first_payment_date" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Maturity Date</label>
                        <input type="date" name="maturity_date" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Any special terms or conditions"></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">Save Loan Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
