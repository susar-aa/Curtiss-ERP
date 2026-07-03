<style>
    .split-layout { display: flex; height: 78vh; background: #fff; border-radius: 8px; border: 1px solid var(--mac-border); overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);}
    @media (prefers-color-scheme: dark) { .split-layout { background: #1e1e2d; } }
    
    .left-pane { width: 320px; border-right: 1px solid var(--mac-border); display: flex; flex-direction: column; background: rgba(0,0,0,0.01); }
    .pane-header { padding: 15px; border-bottom: 1px solid var(--mac-border); background: var(--mac-bg); font-weight: bold; display: flex; justify-content: space-between; align-items: center;}
    .search-container { padding: 10px 15px; border-bottom: 1px solid var(--mac-border); background: rgba(0,0,0,0.01); }
    .search-input { width: 100%; padding: 6px 10px; border: 1px solid var(--mac-border); border-radius: 6px; font-size: 13px; background: var(--mac-bg); color: var(--text-main); box-sizing: border-box; }
    
    .provider-list { flex: 1; overflow-y: auto; }
    .provider-item { padding: 15px; border-bottom: 1px solid var(--mac-border); cursor: pointer; text-decoration: none; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; transition: 0.2s;}
    .provider-item:hover { background: rgba(0,102,204,0.05); }
    .provider-item.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    .provider-item.active .text-sub { color: rgba(255,255,255,0.7); }
    .provider-item.active .bal-text { color: #fff !important; }
    .text-sub { font-size: 11px; color: #888; display: block; margin-top: 4px; }
    
    .right-pane { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .right-header { padding: 20px; border-bottom: 1px solid var(--mac-border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01); }
    
    .avatar-circle { width: 44px; height: 44px; border-radius: 50%; background: #0066cc; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; }
    
    .tabs { display: flex; border-bottom: 1px solid var(--mac-border); background: rgba(0,0,0,0.02); padding: 0 20px;}
    .tab-btn { padding: 12px 20px; border: none; background: transparent; cursor: pointer; font-size: 13px; font-weight: 600; color: #666; border-bottom: 3px solid transparent; transition: 0.2s;}
    .tab-btn:hover { color: #0066cc; }
    .tab-btn.active { color: #0066cc; border-bottom-color: #0066cc; }
    
    .tab-content { flex: 1; padding: 20px; overflow-y: auto; display: none; }
    .tab-content.active { display: block; }
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { background: rgba(0,0,0,0.02); color: #888; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
    .num-col { text-align: right; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: var(--text-main); }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 6px; background: var(--mac-bg); color: var(--text-main); box-sizing: border-box; font-size: 13px;}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 13px; font-weight: 500; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;}
    .btn:hover { background: #0052a3; }
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-outline:hover { background: rgba(0,102,204,0.05); }
    .btn-small { padding: 4px 10px; font-size: 11px; border-radius: 4px; }
    .btn-success { background: #2e7d32; }
    .btn-success:hover { background: #1b5e20; }
    
    .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-Pending { background: #fff3e0; color: #e65100; }
    .status-Sent { background: #e3f2fd; color: #0d47a1; }
    .status-Received { background: #e8f5e9; color: #1b5e20; }
    .status-Voided { background: #ffebee; color: #c62828; }
    
    .status-Active { background: #e8f5e9; color: #1b5e20; }
    .status-Inactive { background: #ffebee; color: #c62828; }

    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 2500; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .modal-content { background: var(--mac-bg); padding: 25px; border-radius: 12px; width: 440px; border: 1px solid var(--mac-border); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
</style>

<?php if(!empty($data['error'])): ?>
    <div style="padding: 12px; background:#ffebee; color:#c62828; border-radius:6px; margin-bottom:15px; font-size:13px; font-weight:500; border: 1px solid rgba(198,40,40,0.2);"><?= $data['error'] ?></div>
<?php endif; ?>
<?php if(!empty($data['success'])): ?>
    <div style="padding: 12px; background:#e8f5e9; color:#2e7d32; border-radius:6px; margin-bottom:15px; font-size:13px; font-weight:500; border: 1px solid rgba(46,125,50,0.2);"><?= $data['success'] ?></div>
<?php endif; ?>

<div class="split-layout">
    <!-- Left Pane: List -->
    <div class="left-pane">
        <div class="pane-header">
            Service Providers
            <button class="btn btn-small" onclick="openModal('addProviderModal')">+ Add</button>
        </div>
        <div class="search-container">
            <input type="text" id="providerSearch" class="search-input" placeholder="Search providers..." onkeyup="filterProviders()">
        </div>
        <div class="provider-list" id="providerList">
            <?php if(empty($data['service_providers'])): ?>
                <div style="padding: 30px; text-align: center; color: #888; font-size: 13px;">No service providers registered.</div>
            <?php else: foreach($data['service_providers'] as $s): ?>
                <a href="<?= APP_URL ?>/serviceprovider/index/<?= $s->id ?>" class="provider-item <?= ($data['selected_service_provider'] && $data['selected_service_provider']->id == $s->id) ? 'active' : '' ?>">
                    <div>
                        <strong><?= htmlspecialchars($s->name) ?></strong>
                        <span class="text-sub">
                            <?= htmlspecialchars($s->service_category ?: 'General') ?>
                            <?php if($s->status == 'Inactive'): ?>
                                <span class="status-badge status-Inactive" style="font-size: 9px; padding: 1px 4px; margin-left: 5px;">Inactive</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 10px; opacity: 0.7;">Owed</span><br>
                        <span class="bal-text" style="font-size: 12px; font-weight: bold; color: <?= $s->outstanding_balance > 0 ? '#c62828' : '#2e7d32' ?>;">
                            Rs: <?= number_format($s->outstanding_balance, 2) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Right Pane: Details -->
    <div class="right-pane">
        <?php if(!$data['selected_service_provider']): ?>
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#888;">
                <div style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;">⚡</div>
                <p style="font-size: 14px; font-weight: 500;">Select a service provider from the left to view their profile, ledger, and stats.</p>
            </div>
        <?php else: ?>
            <?php $sup = $data['selected_service_provider']; $stats = $data['stats']; ?>
            <div class="right-header">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div class="avatar-circle"><?= strtoupper(substr($sup->name, 0, 2)) ?></div>
                    <div>
                        <h2 style="margin: 0 0 5px 0; display:flex; align-items:center; gap: 10px; font-size: 20px;">
                            <?= htmlspecialchars($sup->name) ?>
                            <span class="status-badge status-<?= $sup->status ?>"><?= $sup->status ?></span>
                        </h2>
                        <div style="font-size: 13px; color: #666; display: flex; gap: 15px;">
                            <span>📞 <?= htmlspecialchars($sup->phone ?: 'N/A') ?></span>
                            <span>✉️ <?= htmlspecialchars($sup->email ?: 'N/A') ?></span>
                            <span>⚡ Category: <?= htmlspecialchars($sup->service_category ?: 'N/A') ?></span>
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: #888; text-transform: uppercase; font-weight: bold;">Total Outstanding Payable</div>
                    <div style="font-size: 24px; font-weight: bold; color: <?= $stats->outstanding > 0 ? '#c62828' : '#2e7d32' ?>;">
                        Rs: <?= number_format($stats->outstanding, 2) ?>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('ledger')" id="btn_ledger">Activity Ledger</button>
                <button class="tab-btn" onclick="switchTab('bills')" id="btn_bills">Service Bills</button>
                <button class="tab-btn" onclick="switchTab('profile')" id="btn_profile">Profile Details</button>
            </div>

            <!-- TAB 1: Activity Ledger -->
            <div class="tab-content active" id="tab_ledger">
                <div class="grid-3" style="margin-bottom: 20px;">
                    <div style="background:rgba(0,0,0,0.02); padding:15px; border-radius:8px; border:1px solid var(--mac-border);">
                        <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:bold;">Total Billed</div>
                        <div style="font-size:18px; font-weight:bold; color:var(--text-main); margin-top:5px;">Rs: <?= number_format($stats->total_billed, 2) ?></div>
                    </div>
                    <div style="background:rgba(46,125,50,0.05); padding:15px; border-radius:8px; border:1px solid rgba(46,125,50,0.2);">
                        <div style="font-size:11px; color:#2e7d32; text-transform:uppercase; font-weight:bold;">Total Paid</div>
                        <div style="font-size:18px; font-weight:bold; color:#2e7d32; margin-top:5px;">Rs: <?= number_format($stats->total_paid, 2) ?></div>
                    </div>
                    <div style="background:rgba(0,0,0,0.02); padding:15px; border-radius:8px; border:1px solid var(--mac-border);">
                        <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:bold;">Total Returned</div>
                        <div style="font-size:18px; font-weight:bold; color:var(--text-main); margin-top:5px;">Rs: <?= number_format($stats->total_returned, 2) ?></div>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction Type</th>
                            <th>Reference / Description</th>
                            <th class="num-col">Paid / Returned (Dr)</th>
                            <th class="num-col">Billed / Services (Cr)</th>
                            <th class="num-col">Payable Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['ledger'])): ?>
                            <tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">No financial activity recorded.</td></tr>
                        <?php else: foreach($data['ledger'] as $l): ?>
                            <tr>
                                <td style="color:#666; font-size:12px;"><?= date('M d, Y', strtotime($l->date)) ?></td>
                                <td>
                                    <strong><?= $l->type ?></strong>
                                </td>
                                <td>
                                    <?php if($l->type == 'GRN'): ?>
                                        <a href="<?= APP_URL ?>/serviceprovider/bill/<?= $l->id ?>" target="_blank" style="color:#0066cc; font-weight:bold; text-decoration:none;">
                                            <?= htmlspecialchars($l->ref) ?> ↗
                                        </a>
                                    <?php elseif($l->type == 'Supplier Return'): ?>
                                        <a href="<?= APP_URL ?>/supplier-return/show/<?= $l->id ?>" target="_blank" style="color:#ff3b30; font-weight:bold; text-decoration:none;">
                                            <?= htmlspecialchars($l->ref) ?> ↗
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#333;"><?= htmlspecialchars($l->ref) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="num-col" style="color:#2e7d32; font-weight:500;"><?= $l->debit > 0 ? 'Rs: ' . number_format($l->debit, 2) : '-' ?></td>
                                <td class="num-col" style="color:#333; font-weight:500;"><?= $l->credit > 0 ? 'Rs: ' . number_format($l->credit, 2) : '-' ?></td>
                                <td class="num-col" style="font-weight:bold; color: <?= $l->balance > 0 ? '#c62828' : '#2e7d32' ?>;">Rs: <?= number_format($l->balance, 2) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 2: Service Bills -->
            <div class="tab-content" id="tab_bills">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0;">Billing History</h3>
                    <button class="btn btn-small btn-success" onclick="openModal('addBillModal')">+ Record Service Bill</button>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bill Date</th>
                            <th>Bill Number</th>
                            <th>Service Period</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="num-col">Total Amount</th>
                            <th class="num-col">Balance Due</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['bills'])): ?>
                            <tr><td colspan="8" style="text-align: center; color: #888; padding: 20px;">No service bills recorded.</td></tr>
                        <?php else: foreach($data['bills'] as $b): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($b->grn_date)) ?></td>
                                <td><strong><?= htmlspecialchars($b->grn_number) ?></strong></td>
                                <td><?= htmlspecialchars($b->service_period ?: 'N/A') ?></td>
                                <td style="color:#ff3b30;"><?= $b->due_date ? date('M d, Y', strtotime($b->due_date)) : 'N/A' ?></td>
                                <td>
                                    <span class="status-badge status-<?= $b->status ?: 'Unpaid' ?>"><?= $b->status ?: 'Unpaid' ?></span>
                                </td>
                                <td class="num-col" style="font-weight:bold;">Rs: <?= number_format($b->total_amount, 2) ?></td>
                                <td class="num-col" style="font-weight:bold; color: <?= $b->balance_due > 0 ? '#c62828' : '#2e7d32' ?>;">Rs: <?= number_format($b->balance_due, 2) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/serviceprovider/bill/<?= $b->id ?>" target="_blank" class="btn btn-outline btn-small">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 3: Profile Details Form -->
            <div class="tab-content" id="tab_profile">
                <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border); padding-bottom: 10px;">Update Service Provider Details</h3>
                <form action="<?= APP_URL ?>/serviceprovider/index/<?= $sup->id ?>" method="POST">
                    <input type="hidden" name="action" value="update_service_provider">
                    <input type="hidden" name="service_provider_id" value="<?= $sup->id ?>">
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Service Provider / Company Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($sup->name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($sup->email) ?>">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($sup->phone) ?>">
                        </div>
                        <div class="form-group">
                            <label>Service Category</label>
                            <?php
                            $categories = [
                                'Telephone', 'Internet', 'Electricity', 'Water', 'Mobile',
                                'Insurance', 'Courier', 'Cloud Services', 'Software Subscription',
                                'Maintenance', 'Security', 'Rent'
                            ];
                            $currentCat = $sup->service_category;
                            $isOther = !empty($currentCat) && !in_array($currentCat, $categories);
                            ?>
                            <select name="service_category_select" class="form-control" onchange="toggleCustomCategory(this, 'editCustomCategoryContainer')">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $currentCat == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                                <option value="Other" <?= $isOther ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group" id="editCustomCategoryContainer" style="display: <?= $isOther ? 'block' : 'none' ?>;">
                            <label>Custom Category Name *</label>
                            <input type="text" name="service_category_custom" class="form-control" value="<?= $isOther ? htmlspecialchars($currentCat) : '' ?>">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?= $sup->status == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $sup->status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Physical Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($sup->address) ?></textarea>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Add Service Bill Modal -->
            <div class="modal" id="addBillModal">
                <div class="modal-content" style="width: 500px;">
                    <h3 style="margin-top:0; margin-bottom: 15px;">Record Service Bill</h3>
                    <form action="<?= APP_URL ?>/serviceprovider/index/<?= $sup->id ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_service_bill">
                        <input type="hidden" name="service_provider_id" value="<?= $sup->id ?>">
                        <input type="hidden" name="ap_account_id" value="<?= $data['ap_account'] ? $data['ap_account']->id : '' ?>">
                        
                        <div class="grid-2">
                            <div class="form-group">
                                <label>Bill Number (System Generated) *</label>
                                <input type="text" name="bill_number" class="form-control" value="<?= htmlspecialchars($data['next_bill_number']) ?>" readonly style="background:#e9ecef;">
                            </div>
                            <div class="form-group">
                                <label>Supplier Invoice/Receipt Number (Optional)</label>
                                <input type="text" name="receipt_number" class="form-control" placeholder="e.g. INV-12345">
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: -10px;">
                            <label>Service Period</label>
                            <input type="text" name="service_period" class="form-control" placeholder="e.g. July 2026">
                        </div>
                        
                        <div class="grid-2">
                            <div class="form-group">
                                <label>Bill Date *</label>
                                <input type="date" name="bill_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="form-group">
                                <label>Due Date *</label>
                                <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
                            </div>
                        </div>
                        
                        <div class="grid-3">
                            <div class="form-group">
                                <label>Subtotal (Rs) *</label>
                                <input type="number" step="0.01" id="bill_amount" name="amount" class="form-control" required oninput="calculateBillTotal()" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Tax (Rs)</label>
                                <input type="number" step="0.01" id="bill_tax" name="tax" class="form-control" oninput="calculateBillTotal()" placeholder="0.00" value="0.00">
                            </div>
                            <div class="form-group">
                                <label>Total Amount</label>
                                <input type="number" step="0.01" id="bill_total" name="total_amount" class="form-control" readonly placeholder="0.00" style="background:#e9ecef; font-weight:bold; color:var(--text-main);">
                            </div>
                        </div>
                        
                        <div class="grid-2">
                            <div class="form-group">
                                <label>Expense Account *</label>
                                <select name="expense_account_id" class="form-control" required>
                                    <option value="">-- Select Expense --</option>
                                    <?php foreach($data['expenses'] as $acc): ?>
                                        <option value="<?= $acc->id ?>" <?= (!empty($sup->expense_account_id) && $sup->expense_account_id == $acc->id) ? 'selected' : '' ?>><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Accounts Payable *</label>
                                <input type="text" class="form-control" readonly value="<?= $data['ap_account'] ? htmlspecialchars($data['ap_account']->account_code . ' - ' . $data['ap_account']->account_name) : 'Accounts Payable Not Configured' ?>" style="background:#e9ecef;">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Upload Bill / Invoice (Optional)</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Memo / Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Enter notes..."></textarea>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                            <button type="button" class="btn btn-outline" onclick="closeModal('addBillModal')">Cancel</button>
                            <button type="submit" class="btn">Record Bill</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Provider Modal -->
<div class="modal" id="addProviderModal">
    <div class="modal-content">
        <h3 style="margin-top:0; margin-bottom: 15px;">Add New Service Provider</h3>
        <form action="<?= APP_URL ?>/serviceprovider" method="POST">
            <input type="hidden" name="action" value="add_service_provider">
            
            <div class="form-group">
                <label>Provider / Company Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Service Category</label>
                <select name="service_category_select" class="form-control" onchange="toggleCustomCategory(this, 'addCustomCategoryContainer')">
                    <option value="">Select Category</option>
                    <option value="Telephone">Telephone</option>
                    <option value="Internet">Internet</option>
                    <option value="Electricity">Electricity</option>
                    <option value="Water">Water</option>
                    <option value="Mobile">Mobile</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Courier">Courier</option>
                    <option value="Cloud Services">Cloud Services</option>
                    <option value="Software Subscription">Software Subscription</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Security">Security</option>
                    <option value="Rent">Rent</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group" id="addCustomCategoryContainer" style="display: none;">
                <label>Custom Category Name *</label>
                <input type="text" name="service_category_custom" class="form-control">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProviderModal')">Cancel</button>
                <button type="submit" class="btn">Register Provider</button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        const tabEl = document.getElementById('tab_' + tabName);
        const btnEl = document.getElementById('btn_' + tabName);
        if (tabEl) tabEl.classList.add('active');
        if (btnEl) btnEl.classList.add('active');
    }

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function filterProviders() {
        const query = document.getElementById('providerSearch').value.toLowerCase();
        const items = document.querySelectorAll('.provider-item');
        
        items.forEach(item => {
            const name = item.querySelector('strong').innerText.toLowerCase();
            const category = item.querySelector('.text-sub').innerText.toLowerCase();
            if (name.includes(query) || category.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function toggleCustomCategory(selectEl, containerId) {
        const container = document.getElementById(containerId);
        if (selectEl.value === 'Other') {
            container.style.display = 'block';
            container.querySelector('input').setAttribute('required', 'required');
        } else {
            container.style.display = 'none';
            container.querySelector('input').removeAttribute('required');
            container.querySelector('input').value = '';
        }
    }

    function calculateBillTotal() {
        const amt = parseFloat(document.getElementById('bill_amount').value) || 0;
        const tax = parseFloat(document.getElementById('bill_tax').value) || 0;
        document.getElementById('bill_total').value = (amt + tax).toFixed(2);
    }
</script>
