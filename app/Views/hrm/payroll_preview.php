
<?php $preview = $data['preview']; ?>

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
    
    .btn-success-solid { background: #10b981; color:#fff !important; }
    .btn-success-solid:hover { background: #059669; }
    
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
    .summary-card {
        background: var(--card-bg); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--card-border); border-radius: 20px;
        padding: 24px; text-align: center; box-shadow: var(--card-shadow);
        transition: transform 0.2s;
    }
    .summary-card:hover { transform: translateY(-3px); }
    .summary-card h6 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin: 0 0 8px 0; font-weight: 600; }
    .summary-card h5 { font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0; }
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-receipt"></i> Payroll Preview</h2>
        </div>
        <a href="<?= APP_URL ?>/payroll" class="btn btn-outline">
            <i class="ph ph-x-circle"></i> Cancel
        </a>
    </div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Period</h6>
            <h5><?= date('M d', strtotime($preview['period_start'])) ?> - <?= date('M d, Y', strtotime($preview['period_end'])) ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Total Gross</h6>
            <h5 style="color: #3b82f6;">$<?= number_format($preview['total_gross'], 2) ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Total Deductions</h6>
            <h5 style="color: #ef4444;">$<?= number_format($preview['total_deductions'], 2) ?></h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card">
            <h6>Total Net (Payable)</h6>
            <h5 style="color: #10b981;">$<?= number_format($preview['total_net'], 2) ?></h5>
        </div>
    </div>
</div>

<div class="glass-card mb-4" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border);">
    <h5 style="font-weight: 700; margin-bottom: 16px;">Payslips Breakdown</h5>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th class="text-end">Base Salary</th>
                    <th class="text-end" style="color:#10b981;">+ Allowances</th>
                    <th class="text-end" style="color:#10b981;">+ Comms/OT</th>
                    <th class="text-end fw-bold">Gross</th>
                    <th class="text-end" style="color:#ef4444;">- Loan Ded.</th>
                    <th class="text-end" style="color:#ef4444;">- Other Ded.</th>
                    <th class="text-end fw-bold" style="color:#10b981;">Net Pay</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($preview['slips'] as $slip): ?>
                    <tr>
                        <td style="font-weight:600;">
                            <a href="<?= APP_URL ?>/user/show/<?= $slip['employee_id'] ?>" style="text-decoration: none; color: var(--text-accent);"><?= htmlspecialchars($slip['employee_name']) ?></a>
                            <?php if(!empty($slip['loan_details'])): ?>
                                <br><small style="color:var(--text-muted); font-weight:normal; font-size:11px;"><i class="ph ph-info"></i> Has active loan deductions</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">$<?= number_format($slip['base_salary'], 2) ?></td>
                        <td class="text-end" style="color:#10b981;">$<?= number_format($slip['allowances'], 2) ?></td>
                        <td class="text-end" style="color:#10b981;">$<?= number_format($slip['commissions'] + $slip['overtime'], 2) ?></td>
                        <td class="text-end fw-bold">$<?= number_format($slip['gross_salary'], 2) ?></td>
                        <td class="text-end" style="color:#ef4444;">$<?= number_format($slip['loan_deduction'], 2) ?></td>
                        <td class="text-end" style="color:#ef4444;">$<?= number_format($slip['other_deductions'], 2) ?></td>
                        <td class="text-end fw-bold" style="color:#10b981;">$<?= number_format($slip['net_salary'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="glass-card" style="background: var(--card-bg); border-radius: 20px; padding: 24px; border: 1px solid var(--card-border); display: flex; justify-content: flex-end;">
    <form action="<?= APP_URL ?>/payroll/run" method="POST">
        <input type="hidden" name="action" value="run_payroll">
        <input type="hidden" name="period_start" value="<?= $preview['period_start'] ?>">
        <input type="hidden" name="period_end" value="<?= $preview['period_end'] ?>">
        <input type="hidden" name="run_date" value="<?= $data['run_date'] ?>">
        <button type="submit" class="btn btn-success-solid" style="padding:12px 24px; font-size:15px;">
            <i class="ph ph-check-circle"></i> Confirm & Save as Draft
        </button>
    </form>
</div>
</div>


