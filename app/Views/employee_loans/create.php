

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
    
    .form-control, .form-select {
        background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); color: var(--text-main);
        border-radius: 10px; padding: 10px 14px; transition: all 0.2s; width: 100%;
        box-sizing: border-box; font-family: inherit; font-size: 13.5px;
    }
    .form-control:focus, .form-select:focus {
        background: rgba(0,0,0,0.05); border-color: var(--text-accent); box-shadow: 0 0 0 3px rgba(79,70,229,0.15); outline: none;
    }
    @media (prefers-color-scheme: dark) {
        .form-control, .form-select { background: rgba(255,255,255,0.03); }
        .form-control:focus, .form-select:focus { background: rgba(255,255,255,0.05); }
        .form-select option { background: var(--bg-color); color: var(--text-main); }
    }
    .form-label { font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block; }
</style>

<div class="hrm-container">
    <div class="header-actions">
        <div class="header-title-wrap">
            <h2><i class="ph ph-hand-coins"></i> New Employee Loan</h2>
        </div>
        <a href="<?= APP_URL ?>/employeeloan" class="btn btn-outline">
            <i class="ph ph-arrow-left"></i> Back to Loans
        </a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-danger" style="border-radius:12px; border:none; background: rgba(239,68,68,0.15); color: #ef4444; font-weight:600;"><i class="ph ph-warning-circle"></i> <?= $data['error'] ?></div>
    <?php endif; ?>

    <div class="glass-card" style="background: var(--card-bg); border-radius: 20px; padding: 32px; border: 1px solid var(--card-border); max-width: 800px; margin: 0 auto;">
        <form action="<?= APP_URL ?>/employeeloan/create" method="POST">
            <div class="mb-4">
                <label class="form-label">Employee <span style="color:#ef4444;">*</span></label>
                <select name="employee_id" class="form-select select2" required>
                    <option value="">-- Select Employee --</option>
                    <?php foreach($data['employees'] as $emp): ?>
                        <option value="<?= $emp->id ?>"><?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Loan/Ref Number</label>
                    <input type="text" name="loan_number" class="form-control" value="EL-<?= time() ?>">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Principal Amount <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" name="principal_amount" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Start Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="loan_start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Term (Months) <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="loan_term_months" class="form-control" required value="12">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Repayment Frequency</label>
                    <select name="repayment_frequency" class="form-select">
                        <option value="Monthly">Monthly (Payroll Deduction)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Repayment Amount per Period <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" name="repayment_amount" class="form-control" required>
                    <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">This amount will be automatically deducted during payroll.</small>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Interest Rate (%) (Optional)</label>
                <input type="number" step="0.01" name="interest_rate" class="form-control" value="0.00">
            </div>

            <div class="mb-5">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn" style="padding: 12px 30px; font-size: 15px;">
                    <i class="ph ph-check-circle"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>


