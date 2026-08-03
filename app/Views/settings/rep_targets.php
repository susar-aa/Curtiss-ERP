<style>
    .grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #475569; }
    .form-control { width: 100%; padding: 10px; border: 1px solid var(--mac-border, #cbd5e1); border-radius: 6px; background: #fff; color: var(--text-main, #334155); box-sizing: border-box; }
    .btn { padding: 10px 20px; background: #1b5e20; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
    .btn:hover { background: #144416; }
    .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    
    .perf-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .perf-table th {
        background: #f8fafc;
        text-align: left;
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #cbd5e1;
    }
    .perf-table td {
        padding: 10px 12px;
        font-size: 13px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }
    .settings-card {
        background: #fff; 
        border: 1px solid var(--mac-border, #cbd5e1); 
        border-radius: 8px; 
        padding: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        margin-bottom: 25px;
    }
</style>

<div class="header-actions" style="margin-bottom: 20px;">
    <h2>Settings Panel</h2>
    <p style="color:#666; margin-top:0;">Manage your business identity, system preferences, and representative benchmarks.</p>
</div>

<?php if(!empty($data['success'])): ?>
    <div class="alert alert-success"><?= $data['success'] ?></div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div class="alert alert-error"><?= $data['error'] ?></div>
<?php endif; ?>

<div style="display: flex; gap: 25px; align-items: flex-start; min-height: 80vh; flex-wrap: wrap;">
    <!-- Left Navigation Side Panel -->
    <div style="width: 260px; background: #fff; border: 1px solid var(--mac-border, #cbd5e1); border-radius: 8px; padding: 15px; box-sizing: border-box; flex-shrink: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
        <h3 style="margin-top:0; font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 15px; letter-spacing: 0.5px;">Settings Directory</h3>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
            <li>
                <a href="<?= APP_URL ?>/settings" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?= $data['active_tab'] === 'company' ? '#1b5e20' : '#475569' ?>; background: <?= $data['active_tab'] === 'company' ? '#e8f5e9' : 'transparent' ?>; border-left: 3px solid <?= $data['active_tab'] === 'company' ? '#1b5e20' : 'transparent' ?>; transition: all 0.2s ease;">
                    <i class="ph ph-buildings" style="font-size: 16px;"></i> Company Profile Settings
                </a>
            </li>
            <li>
                <a href="<?= APP_URL ?>/settings/rep_targets" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?= $data['active_tab'] === 'rep_targets' ? '#1b5e20' : '#475569' ?>; background: <?= $data['active_tab'] === 'rep_targets' ? '#e8f5e9' : 'transparent' ?>; border-left: 3px solid <?= $data['active_tab'] === 'rep_targets' ? '#1b5e20' : 'transparent' ?>; transition: all 0.2s ease;">
                    <i class="ph ph-target" style="font-size: 16px;"></i> Rep Targets &amp; KPI Weights
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Right Content Panel -->
    <div style="flex: 1 1 500px;">
        
        <!-- Filter Selector Removed for Global Default Targets -->

        <!-- Form 1: Rep Targets Form -->
        <div class="settings-card">
            <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border, #cbd5e1); padding-bottom: 10px; font-size: 16px; color:#1e293b;">
                <i class="ph ph-target"></i> Global Default Performance Targets
            </h3>
            
            <form method="POST" action="<?= APP_URL ?>/settings/rep_targets?rep_user_id=<?= $data['selected_rep_id'] ?>&month=<?= $data['month'] ?>&year=<?= $data['year'] ?>" style="margin-top: 15px;">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <input type="hidden" name="save_targets" value="1">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label for="sales_target">Sales Target (LKR) *</label>
                        <input type="number" name="sales_target" id="sales_target" step="0.01" min="0" class="form-control" value="<?= floatval($data['rep_targets']->sales_target) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="productive_visits_target">Productive Visits Target (Count) *</label>
                        <input type="number" name="productive_visits_target" id="productive_visits_target" min="0" class="form-control" value="<?= intval($data['rep_targets']->productive_visits_target) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="total_visits_target">Total Visits Target (Productive + Unproductive) *</label>
                        <input type="number" name="total_visits_target" id="total_visits_target" min="0" class="form-control" value="<?= intval($data['rep_targets']->total_visits_target) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="working_days_target">Total Working Days Target (Days) *</label>
                        <input type="number" name="working_days_target" id="working_days_target" min="0" class="form-control" value="<?= intval($data['rep_targets']->working_days_target) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="credit_limit">Total Credit Limit (LKR) *</label>
                        <input type="number" name="credit_limit" id="credit_limit" step="0.01" min="0" class="form-control" value="<?= floatval($data['rep_targets']->credit_limit) ?>" required>
                        <small style="font-size: 11px; color:#64748b; display:block; margin-top:4px;">Billing will not be restricted; this is for dashboard comparisons.</small>
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: flex-end; align-items: center;">
                    <button type="submit" class="btn"><i class="ph ph-floppy-disk"></i> Save Global Targets</button>
                </div>
            </form>
        </div>

        <!-- Form 2: Global KPI Weights -->
        <div class="settings-card">
            <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border, #cbd5e1); padding-bottom: 10px; font-size: 16px; color:#1e293b;">
                <i class="ph ph-gear-six"></i> Global KPI Scoring Weights &amp; Constraints
            </h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 10px;">
                Define how much weight each metric contributes to the overall representative performance score. All weights must sum up to 100%.
            </p>
            
            <form method="POST" action="<?= APP_URL ?>/settings/rep_targets?rep_user_id=<?= $data['selected_rep_id'] ?>&month=<?= $data['month'] ?>&year=<?= $data['year'] ?>" style="margin-top: 15px;">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <input type="hidden" name="save_weights" value="1">
                
                <table class="perf-table">
                    <thead>
                        <tr>
                            <th>Evaluation Dimension</th>
                            <th>Weight Assigned (%)</th>
                            <th>Default Base Target</th>
                            <th>Min Clamped Score</th>
                            <th>Max Clamped Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['kpi_configs'] as $cfg): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cfg->kpi_name) ?></strong>
                                    <input type="hidden" name="configs[<?= $cfg->kpi_key ?>][kpi_key]" value="<?= $cfg->kpi_key ?>">
                                </td>
                                <td>
                                    <input type="number" name="configs[<?= $cfg->kpi_key ?>][weight]" value="<?= floatval($cfg->weight) ?>" step="0.5" min="0" max="100" class="form-control" style="padding: 6px; width: 80px; display:inline-block;"> %
                                </td>
                                <td>
                                    <input type="number" name="configs[<?= $cfg->kpi_key ?>][target_value]" value="<?= floatval($cfg->target_value) ?>" step="0.01" min="0" class="form-control" style="padding: 6px; width: 140px; display:inline-block;">
                                </td>
                                <td>
                                    <input type="number" name="configs[<?= $cfg->kpi_key ?>][min_score]" value="<?= intval($cfg->min_score) ?>" min="0" class="form-control" style="padding: 6px; width: 80px; display:inline-block;">
                                </td>
                                <td>
                                    <input type="number" name="configs[<?= $cfg->kpi_key ?>][max_score]" value="<?= intval($cfg->max_score) ?>" min="0" class="form-control" style="padding: 6px; width: 80px; display:inline-block;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn" style="background: #334155;"><i class="ph ph-floppy-disk"></i> Save Global KPI Weights</button>
                </div>
            </form>
        </div>

    </div>
</div>
