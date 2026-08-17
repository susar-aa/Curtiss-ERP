

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
    .status-draft { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
    .status-active { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-hand-coins"></i> Employee Loans</h2>
        </div>
        <a href="<?= APP_URL ?>/employeeloan/create" class="btn">
            <i class="ph ph-plus-circle"></i> New Employee Loan
        </a>
    </div>

    <?php if(!empty($data['success'])): ?>
        <div class="alert alert-success" style="border-radius:12px; border:none; background: rgba(16,185,129,0.15); color: #10b981; font-weight:600;"><i class="ph ph-check-circle"></i> <?= $data['success'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-danger" style="border-radius:12px; border:none; background: rgba(239,68,68,0.15); color: #ef4444; font-weight:600;"><i class="ph ph-warning-circle"></i> <?= $data['error'] ?></div>
    <?php endif; ?>

    <div class="glass-card" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
        <div style="overflow-x: auto;">
            <table class="data-table datatable">
                <thead>
                    <tr>
                        <th>Loan No</th>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Principal</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['loans'])): ?>
                        <tr><td colspan="7" class="text-center" style="color:var(--text-muted); padding:30px;">No loans found.</td></tr>
                    <?php else: foreach($data['loans'] as $loan): ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($loan->loan_number) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/user/show/<?= $loan->employee_id ?>" style="color: var(--text-accent); font-weight:500; text-decoration:none;">
                                    <?= htmlspecialchars($loan->employee_name) ?>
                                </a>
                            </td>
                            <td><?= date('M d, Y', strtotime($loan->loan_start_date)) ?></td>
                            <td>$<?= number_format($loan->principal_amount, 2) ?></td>
                            <td style="color:#ef4444; font-weight:600;">$<?= number_format($loan->principal_balance, 2) ?></td>
                            <td>
                                <?php 
                                    $statusClass = 'status-draft';
                                    if($loan->status == 'Active') $statusClass = 'status-active';
                                    if($loan->status == 'Approved') $statusClass = 'status-active';
                                    if($loan->status == 'Closed') $statusClass = 'status-paid';
                                    if($loan->status == 'Rejected') $statusClass = 'status-rejected';
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $loan->status ?></span>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/employeeloan/show/<?= $loan->id ?>" class="btn btn-outline" style="padding:6px 12px; font-size:12px;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


