

<div class="content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Bank Loans Management</h1>
            <p class="text-muted mb-0">Track and manage company loans and repayments.</p>
        </div>
        <div>
            <a href="<?= APP_URL ?>/loan/create" class="btn btn-primary"><i class="ph ph-plus"></i> Add New Loan</a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success mt-3">Action completed successfully.</div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Active Loans</h6>
                    <h3 class="mb-0"><?= $data['stats']->active_loans ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Principal</h6>
                    <h3 class="mb-0 text-primary">Rs. <?= number_format($data['stats']->total_principal, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding Principal</h6>
                    <h3 class="mb-0 text-danger">Rs. <?= number_format($data['stats']->outstanding_principal, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Interest Paid</h6>
                    <h3 class="mb-0 text-warning">Rs. <?= number_format($data['stats']->paid_interest, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="mb-0">All Loans</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Loan #</th>
                            <th>Lender / Bank</th>
                            <th>Start Date</th>
                            <th class="text-end">Principal</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['loans'])): ?>
                        <tr><td colspan="7" class="text-center py-4">No loans found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['loans'] as $loan): ?>
                            <tr>
                                <td><?= htmlspecialchars($loan->loan_number ?: 'N/A') ?></td>
                                <td><strong><?= htmlspecialchars($loan->lender_name) ?></strong></td>
                                <td><?= date('d M Y', strtotime($loan->loan_start_date)) ?></td>
                                <td class="text-end">Rs. <?= number_format($loan->principal_amount, 2) ?></td>
                                <td class="text-end"><strong>Rs. <?= number_format($loan->principal_balance, 2) ?></strong></td>
                                <td>
                                    <?php if ($loan->status == 'Active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif ($loan->status == 'Pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($loan->status == 'Closed'): ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?= htmlspecialchars($loan->status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= APP_URL ?>/loan/show/<?= $loan->id ?>" class="btn btn-sm btn-light">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    border-radius: 12px;
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
</style>


