<?php require APP_ROOT . '/app/Views/layouts/header.php'; ?>
<?php $emp = $data['employee']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Employee Profile: <?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name) ?></h2>
    <a href="<?= APP_URL ?>/user" class="btn btn-outline-secondary">Back to Directory</a>
</div>

<ul class="nav nav-tabs mb-4" id="employeeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile Details</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab">Salary Info</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab">Loans & Repayments</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="payslips-tab" data-bs-toggle="tab" data-bs-target="#payslips" type="button" role="tab">Payroll History</button>
    </li>
</ul>

<div class="tab-content" id="employeeTabsContent">
    <!-- Profile Tab -->
    <div class="tab-pane fade show active" id="profile" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>First Name:</strong> <?= htmlspecialchars($emp->first_name) ?></p>
                        <p><strong>Last Name:</strong> <?= htmlspecialchars($emp->last_name) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($emp->email) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($emp->phone) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Department:</strong> <?= htmlspecialchars($emp->department) ?></p>
                        <p><strong>Job Title:</strong> <?= htmlspecialchars($emp->job_title) ?></p>
                        <p><strong>Hire Date:</strong> <?= date('M d, Y', strtotime($emp->hire_date)) ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-<?= $emp->status == 'Active' ? 'success' : 'secondary' ?>"><?= $emp->status ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Tab -->
    <div class="tab-pane fade" id="salary" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h4>Base Salary: <?= number_format($emp->base_salary, 2) ?></h4>
                <p class="text-muted">Salary details and allowances will be fully configured here in future updates.</p>
            </div>
        </div>
    </div>

    <!-- Loans Tab -->
    <div class="tab-pane fade" id="loans" role="tabpanel">
        <div class="d-flex justify-content-end mb-3">
            <a href="<?= APP_URL ?>/employeeloan/create" class="btn btn-primary btn-sm">Issue New Loan</a>
        </div>
        <div class="accordion" id="loansAccordion">
            <?php if(empty($data['loans'])): ?>
                <div class="alert alert-info">No loans found for this employee.</div>
            <?php else: foreach($data['loans'] as $index => $loan): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#loan-<?= $loan->id ?>">
                            Loan <?= htmlspecialchars($loan->loan_number) ?> - <?= number_format($loan->principal_amount, 2) ?> 
                            <span class="badge bg-<?= $loan->status == 'Active' ? 'primary' : ($loan->status == 'Closed' ? 'success' : 'secondary') ?> ms-2"><?= $loan->status ?></span>
                        </button>
                    </h2>
                    <div id="loan-<?= $loan->id ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#loansAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Principal:</strong> <?= number_format($loan->principal_amount, 2) ?></p>
                                    <p><strong>Repayment / Period:</strong> <?= number_format($loan->repayment_amount, 2) ?> (<?= $loan->repayment_frequency ?>)</p>
                                    <p><strong>Total Paid:</strong> <?= number_format($loan->total_principal_paid, 2) ?></p>
                                    <p class="text-danger"><strong>Balance:</strong> <?= number_format($loan->principal_balance, 2) ?></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="<?= APP_URL ?>/employeeloan/show/<?= $loan->id ?>" class="btn btn-outline-primary btn-sm">Manage Loan</a>
                                </div>
                            </div>
                            
                            <h6 class="mt-3">Repayment History</h6>
                            <?php $repayments = $data['loanModel']->getRepayments($loan->id); ?>
                            <?php if(empty($repayments)): ?>
                                <p class="text-muted small">No repayments yet.</p>
                            <?php else: ?>
                                <table class="table table-sm table-bordered mt-2">
                                    <thead><tr><th>Date</th><th>Principal Paid</th><th>Notes</th></tr></thead>
                                    <tbody>
                                        <?php foreach($repayments as $r): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($r->payment_date)) ?></td>
                                                <td class="text-success">+<?= number_format($r->principal_amount, 2) ?></td>
                                                <td><?= htmlspecialchars($r->notes) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Payslips Tab -->
    <div class="tab-pane fade" id="payslips" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['payslips'])): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No payroll history found.</td></tr>
                        <?php else: foreach($data['payslips'] as $slip): ?>
                            <tr>
                                <td><?= date('M d', strtotime($slip->period_start)) ?> - <?= date('M d, Y', strtotime($slip->period_end)) ?></td>
                                <td><?= number_format($slip->gross_salary, 2) ?></td>
                                <td class="text-danger"><?= number_format($slip->loan_deduction + $slip->other_deductions, 2) ?></td>
                                <td class="text-success fw-bold"><?= number_format($slip->net_salary, 2) ?></td>
                                <td>
                                    <?php 
                                        $badge = 'secondary';
                                        if($slip->status == 'Approved') $badge = 'info';
                                        if($slip->status == 'Paid') $badge = 'success';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= $slip->status ?></span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/payroll/payslip/<?= $slip->id ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View Payslip</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/app/Views/layouts/footer.php'; ?>
