<?php
?>
<style>
    .grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 10px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    .btn { padding: 10px 20px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
    .btn:hover { background: #005bb5; }
    .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    
    .logo-preview {
        width: 150px; height: 150px;
        border: 2px dashed var(--mac-border);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 15px;
        overflow: hidden;
        background: rgba(0,0,0,0.02);
    }
    .logo-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
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
        <div class="grid-2">
            <!-- Profile Form -->
            <div class="card" style="background:#fff; border: 1px solid var(--mac-border, #cbd5e1); border-radius:8px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border, #cbd5e1); padding-bottom: 10px; font-size: 16px; color:#1e293b;">Business Profile</h3>
                <form action="<?= APP_URL ?>/settings" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label>Registered Company Name *</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($data['settings']->company_name) ?>" required>
                    </div>
                    
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1 1 200px;">
                            <label>Business Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['settings']->email ?? '') ?>">
                        </div>
                        <div class="form-group" style="flex: 1 1 200px;">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['settings']->phone ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tax/VAT Number</label>
                        <input type="text" name="tax_number" class="form-control" value="<?= htmlspecialchars($data['settings']->tax_number ?? '') ?>" placeholder="e.g. VAT-12345678">
                    </div>

                    <div class="form-group">
                        <label>Business Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($data['settings']->address ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>E-Commerce Storefront URL</label>
                        <input type="url" name="ecommerce_store_url" class="form-control" value="<?= htmlspecialchars($data['settings']->ecommerce_store_url ?? '') ?>" placeholder="e.g. http://localhost/Curtiss%20E%20Commerce">
                    </div>

                    <!-- Facebook Integration Section -->
                    <div style="margin-top: 25px; margin-bottom: 20px; border-top: 1px dashed var(--mac-border, #cbd5e1); padding-top: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: #0066cc; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-brands fa-facebook"></i> Facebook Page Autopost Integration
                        </h4>
                        <p style="font-size: 12px; color: #666; margin: 0 0 15px 0;">Automatically publish newly created products directly to your Facebook Business Page feed.</p>
                        
                        <div class="form-group">
                            <label>Facebook Page ID</label>
                            <input type="text" name="facebook_page_id" class="form-control" value="<?= htmlspecialchars($data['settings']->facebook_page_id ?? '') ?>" placeholder="e.g. 104859672859">
                        </div>
                        
                        <div class="form-group">
                            <label>Facebook Page Access Token</label>
                            <input type="text" name="facebook_access_token" class="form-control" value="<?= htmlspecialchars($data['settings']->facebook_access_token ?? '') ?>" placeholder="e.g. EAAGzD... (Permanent Page Token)">
                            <small style="display: block; font-size: 11px; color: #888; margin-top: 4px;">Use a Facebook Page Access Token with <code>pages_manage_posts</code> and <code>pages_read_engagement</code> permissions.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="background:#1b5e20;">Save Profile Settings</button>
                </form>
            </div>

            <!-- Payroll & Commissions Settings Form -->
            <div class="card" style="background:#fff; border: 1px solid var(--mac-border, #cbd5e1); border-radius:8px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-top: 20px;">
                <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border, #cbd5e1); padding-bottom: 10px; font-size: 16px; color:#1e293b;"><i class="ph ph-wallet"></i> Payroll &amp; Commissions Configurations</h3>
                <form action="<?= APP_URL ?>/settings" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                    <input type="hidden" name="update_payroll_settings" value="1">
                    
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1 1 200px;">
                            <label>Sales Commission Percentage (%)</label>
                            <input type="number" step="0.01" name="sales_commission_pct" class="form-control" value="<?= htmlspecialchars($data['settings']->sales_commission_pct ?? '0.00') ?>" required>
                        </div>
                    </div>

                    <div style="margin-top: 15px; margin-bottom: 15px; border-top: 1px dashed var(--mac-border, #cbd5e1); padding-top: 15px;">
                        <h4 style="margin: 0 0 10px 0; color: #0066cc; font-size: 14px;">Sales Incentive Thresholds</h4>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1 1 180px;">
                                <label>Minimum Sales Value (LKR)</label>
                                <input type="number" step="0.01" name="sales_incentive_min_value" class="form-control" value="<?= htmlspecialchars($data['settings']->sales_incentive_min_value ?? '0.00') ?>" required>
                            </div>
                            <div class="form-group" style="flex: 1 1 180px;">
                                <label>Sales Incentive (%)</label>
                                <input type="number" step="0.01" name="sales_incentive_pct" class="form-control" value="<?= htmlspecialchars($data['settings']->sales_incentive_pct ?? '0.00') ?>" required>
                            </div>
                            <div class="form-group" style="flex: 1 1 180px;">
                                <label>Max Incentive Limit (LKR)</label>
                                <input type="number" step="0.01" name="sales_incentive_max_limit" class="form-control" value="<?= htmlspecialchars($data['settings']->sales_incentive_max_limit ?? '0.00') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 15px; margin-bottom: 20px; border-top: 1px dashed var(--mac-border, #cbd5e1); padding-top: 15px;">
                        <h4 style="margin: 0 0 10px 0; color: #0066cc; font-size: 14px;">Fixed Bonus Payouts (LKR)</h4>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1 1 180px;">
                                <label>Productive Visits Achieved</label>
                                <input type="number" step="0.01" name="productive_visits_payout" class="form-control" value="<?= htmlspecialchars($data['settings']->productive_visits_payout ?? '0.00') ?>" required>
                            </div>
                            <div class="form-group" style="flex: 1 1 180px;">
                                <label>Working Days Achieved</label>
                                <input type="number" step="0.01" name="working_days_payout" class="form-control" value="<?= htmlspecialchars($data['settings']->working_days_payout ?? '0.00') ?>" required>
                            </div>
                            <div class="form-group" style="flex: 1 1 180px;">
                                <label>Collection Target Achieved</label>
                                <input type="number" step="0.01" name="collection_efficiency_payout" class="form-control" value="<?= htmlspecialchars($data['settings']->collection_efficiency_payout ?? '0.00') ?>" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="background:#1b5e20;">Save Payroll Settings</button>
                </form>
            </div>

            <!-- Logo Upload -->
            <div class="card" style="background:#fff; border: 1px solid var(--mac-border, #cbd5e1); border-radius:8px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border, #cbd5e1); padding-bottom: 10px; font-size: 16px; color:#1e293b;">Company Logo</h3>
                
                <div class="logo-preview">
                    <?php if(!empty($data['settings']->logo_path)): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= $data['settings']->logo_path ?>" alt="Company Logo">
                    <?php else: ?>
                        <span style="color:#aaa; font-size: 12px;">No Logo Uploaded</span>
                    <?php endif; ?>
                </div>

                <form action="<?= APP_URL ?>/settings" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                    <input type="hidden" name="upload_logo" value="1">
                    <div class="form-group">
                        <label>Select Image (JPG, PNG)</label>
                        <input type="file" name="logo" class="form-control" accept=".jpg, .jpeg, .png, .gif" required style="padding: 6px;">
                    </div>
                    <button type="submit" class="btn" style="width: 100%; background: #333;">Upload Logo</button>
                </form>
                <p style="font-size: 11px; color:#888; margin-top: 15px;">This logo will appear on all client-facing Invoices, Quotes, and Financial Reports.</p>
            </div>
        </div>
    </div>
</div>