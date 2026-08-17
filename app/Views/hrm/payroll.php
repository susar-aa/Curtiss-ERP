
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
        font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;
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
    .status-approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .status-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    
    /* Modal Styles matching the theme */
    .modal-content {
        background: var(--card-bg); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--card-border); border-radius: 20px;
        color: var(--text-main);
    }
    .modal-header { border-bottom: 1px solid var(--glass-border); padding: 20px 24px; }
    .modal-title { font-weight: 700; font-size: 18px; }
    .modal-body { padding: 24px; }
    .modal-footer { border-top: 1px solid var(--glass-border); padding: 20px 24px; }
    .form-control {
        background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); color: var(--text-main);
        border-radius: 10px; padding: 10px 14px; transition: all 0.2s;
    }
    .form-control:focus {
        background: rgba(0,0,0,0.05); border-color: var(--text-accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
    }
    @media (prefers-color-scheme: dark) {
        .form-control { background: rgba(255,255,255,0.03); }
        .form-control:focus { background: rgba(255,255,255,0.05); }
    }
    .form-label { font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; }
    .btn-close { filter: invert(var(--close-invert, 0)); }
    @media (prefers-color-scheme: dark) { :root { --close-invert: 1; } }
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-bank"></i> Payroll Runs</h2>
        </div>
        <button class="btn" data-bs-toggle="modal" data-bs-target="#newPayrollModal">
            <i class="ph ph-plus-circle"></i> Run New Payroll
        </button>
    </div>

    <?php if(!empty($data['success'])): ?>
        <div class="alert alert-success" style="border-radius:12px; border:none; background: rgba(16,185,129,0.15); color: #10b981; font-weight:600;"><i class="ph ph-check-circle"></i> <?= $data['success'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-danger" style="border-radius:12px; border:none; background: rgba(239,68,68,0.15); color: #ef4444; font-weight:600;"><i class="ph ph-warning-circle"></i> <?= $data['error'] ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <div style="overflow-x: auto;">
            <table class="data-table datatable">
                <thead>
                    <tr>
                        <th>Ref ID</th>
                        <th>Period</th>
                        <th>Run Date</th>
                        <th>Total Gross</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['payroll_runs'])): ?>
                        <tr><td colspan="7" class="text-center" style="color:var(--text-muted); padding:30px;">No payroll runs found.</td></tr>
                    <?php else: foreach($data['payroll_runs'] as $run): ?>
                        <tr>
                            <td style="font-weight:600;">PR-<?= $run->id ?></td>
                            <td><?= date('M d', strtotime($run->period_start)) ?> - <?= date('M d, Y', strtotime($run->period_end)) ?></td>
                            <td><?= date('M d, Y', strtotime($run->run_date)) ?></td>
                            <td style="font-weight:600;">$<?= number_format($run->total_gross, 2) ?></td>
                            <td><?= htmlspecialchars($run->username) ?></td>
                            <td>
                                <?php 
                                    $statusClass = 'status-draft';
                                    if($run->status == 'Approved') $statusClass = 'status-approved';
                                    if($run->status == 'Paid') $statusClass = 'status-paid';
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $run->status ?></span>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/payroll/show/<?= $run->id ?>" class="btn btn-outline" style="padding:6px 12px; font-size:12px;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Payroll Modal -->
<div class="modal fade" id="newPayrollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= APP_URL ?>/payroll/preview" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Run New Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Period Start Date</label>
                    <input type="date" name="period_start" class="form-control" required value="<?= date('Y-m-01') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Period End Date</label>
                    <input type="date" name="period_end" class="form-control" required value="<?= date('Y-m-t') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Run Date</label>
                    <input type="date" name="run_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn">Preview Payroll</button>
            </div>
        </form>
    </div>
</div>
