
<?php $emp = $data['employee']; ?>

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
    .status-active { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .status-inactive { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
    .status-draft { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
    .status-approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }

    .nav-tabs {
        border-bottom: 1px solid var(--glass-border);
        margin-bottom: 24px;
    }
    .nav-tabs .nav-link {
        color: var(--text-muted); font-weight: 600; border: none; border-bottom: 2px solid transparent;
        background: transparent; padding: 12px 20px; font-size: 14px; transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover { border-color: rgba(0,0,0,0.1); color: var(--text-main); }
    @media (prefers-color-scheme: dark) {
        .nav-tabs .nav-link:hover { border-color: rgba(255,255,255,0.1); }
    }
    .nav-tabs .nav-link.active {
        color: var(--text-accent); background: transparent; border: none; border-bottom: 2px solid var(--text-accent);
    }
    
    .profile-info p { margin-bottom: 12px; font-size: 14px; color: var(--text-main); }
    .profile-info strong { color: var(--text-muted); font-weight: 600; width: 120px; display: inline-block; }
    
    .accordion-item {
        background: transparent; border: 1px solid var(--glass-border); border-radius: 16px !important;
        margin-bottom: 12px; overflow: hidden;
    }
    .accordion-button {
        background: rgba(0,0,0,0.02); color: var(--text-main); font-weight: 600; box-shadow: none !important;
    }
    @media (prefers-color-scheme: dark) {
        .accordion-button { background: rgba(255,255,255,0.02); }
    }
    .accordion-button:not(.collapsed) {
        background: rgba(79, 70, 229, 0.05); color: var(--text-accent);
    }
    .accordion-body { padding: 20px; }
    .accordion-button::after { filter: invert(var(--close-invert, 0)); }
    @media (prefers-color-scheme: dark) { :root { --close-invert: 1; } }
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-user-circle"></i> Employee Profile: <?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name) ?></h2>
        </div>
        <a href="<?= APP_URL ?>/user" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Directory
        </a>
    </div>

<ul class="nav nav-tabs mb-4" id="employeeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab"><i class="ph ph-identification-card"></i> Profile Details</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab"><i class="ph ph-currency-dollar"></i> Salary Info</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab"><i class="ph ph-hand-coins"></i> Loans & Repayments</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="payslips-tab" data-bs-toggle="tab" data-bs-target="#payslips" type="button" role="tab"><i class="ph ph-receipt"></i> Payroll History</button>
    </li>
</ul>

<div class="tab-content" id="employeeTabsContent">
    <!-- Profile Tab -->
    <div class="tab-pane fade show active" id="profile" role="tabpanel">
        <div class="glass-card" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <div class="profile-info">
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
                        <p><strong>Status:</strong> <span class="status-badge <?= $emp->status == 'Active' ? 'status-active' : 'status-inactive' ?>"><?= $emp->status ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Tab -->
    <div class="tab-pane fade" id="salary" role="tabpanel">
        <div class="glass-card" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <h4 style="font-weight:700; margin-bottom:12px;">Base Salary: $<?= number_format($emp->base_salary, 2) ?></h4>
            <p style="color:var(--text-muted); font-size:14px;"><i class="ph ph-info"></i> Salary details and allowances will be fully configured here in future updates.</p>
        </div>
    </div>

    <!-- Loans Tab -->
    <div class="tab-pane fade" id="loans" role="tabpanel">
        <div class="d-flex justify-content-end mb-3">
            <a href="<?= APP_URL ?>/employeeloan/create" class="btn">Issue New Loan</a>
        </div>
        <div class="accordion" id="loansAccordion">
            <?php if(empty($data['loans'])): ?>
                <div class="alert alert-info" style="border-radius:12px; background:rgba(59,130,246,0.15); border:none; color:#3b82f6;"><i class="ph ph-info"></i> No loans found for this employee.</div>
            <?php else: foreach($data['loans'] as $index => $loan): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#loan-<?= $loan->id ?>">
                            Loan <?= htmlspecialchars($loan->loan_number) ?> - $<?= number_format($loan->principal_amount, 2) ?> 
                            <?php 
                                $lstatusClass = 'status-draft';
                                if($loan->status == 'Active') $lstatusClass = 'status-active';
                                if($loan->status == 'Closed') $lstatusClass = 'status-paid';
                            ?>
                            <span class="status-badge <?= $lstatusClass ?> ms-3"><?= $loan->status ?></span>
                        </button>
                    </h2>
                    <div id="loan-<?= $loan->id ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#loansAccordion">
                        <div class="accordion-body profile-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Principal:</strong> $<?= number_format($loan->principal_amount, 2) ?></p>
                                    <p><strong>Repayment:</strong> $<?= number_format($loan->repayment_amount, 2) ?> (<?= $loan->repayment_frequency ?>)</p>
                                    <p><strong>Total Paid:</strong> $<?= number_format($loan->total_principal_paid, 2) ?></p>
                                    <p><strong>Balance:</strong> <span style="color:#ef4444; font-weight:600;">$<?= number_format($loan->principal_balance, 2) ?></span></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="<?= APP_URL ?>/employeeloan/show/<?= $loan->id ?>" class="btn btn-outline" style="font-size:12px; padding:6px 12px;">Manage Loan</a>
                                </div>
                            </div>
                            
                            <h6 class="mt-4" style="font-weight:600;">Repayment History</h6>
                            <?php $repayments = $data['loanModel']->getRepayments($loan->id); ?>
                            <?php if(empty($repayments)): ?>
                                <p style="color:var(--text-muted); font-size:13px;"><i class="ph ph-calendar-blank"></i> No repayments yet.</p>
                            <?php else: ?>
                                <table class="data-table mt-2">
                                    <thead><tr><th>Date</th><th>Principal Paid</th><th>Notes</th></tr></thead>
                                    <tbody>
                                        <?php foreach($repayments as $r): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($r->payment_date)) ?></td>
                                                <td style="color:#10b981; font-weight:600;">+$<?= number_format($r->principal_amount, 2) ?></td>
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
        <div class="glass-card" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
            <div style="overflow-x: auto;">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Gross Pay</th>
                            <th style="color:#ef4444;">Deductions</th>
                            <th style="color:#10b981;">Net Pay</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['payslips'])): ?>
                            <tr><td colspan="6" class="text-center" style="color:var(--text-muted); padding:30px;">No payroll history found.</td></tr>
                        <?php else: foreach($data['payslips'] as $slip): ?>
                            <tr>
                                <td><?= date('M d', strtotime($slip->period_start)) ?> - <?= date('M d, Y', strtotime($slip->period_end)) ?></td>
                                <td>$<?= number_format($slip->gross_salary, 2) ?></td>
                                <td style="color:#ef4444;">$<?= number_format($slip->loan_deduction + $slip->other_deductions, 2) ?></td>
                                <td style="color:#10b981; font-weight:600;">$<?= number_format($slip->net_salary, 2) ?></td>
                                <td>
                                    <?php 
                                        $badge = 'status-draft';
                                        if($slip->status == 'Approved') $badge = 'status-approved';
                                        if($slip->status == 'Paid') $badge = 'status-paid';
                                    ?>
                                    <span class="status-badge <?= $badge ?>"><?= $slip->status ?></span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/payroll/payslip/<?= $slip->id ?>" target="_blank" class="btn btn-outline" style="font-size:12px; padding:6px 12px;"><i class="ph ph-receipt"></i> View</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>


