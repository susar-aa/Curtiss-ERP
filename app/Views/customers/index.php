<?php
?>
<style>
    .split-layout { display: flex; height: calc(100vh - 80px); background: #f4f5f7; border-radius: 8px; overflow: hidden; gap: 0; border: 1px solid var(--mac-border); box-shadow: 0 2px 10px rgba(0,0,0,0.05);}
    @media (prefers-color-scheme: dark) { .split-layout { background: #121212; } }
    
    /* Left Pane */
    .left-pane { width: 320px; background: rgba(0,0,0,0.02); border-right: 1px solid var(--mac-border); display: flex; flex-direction: column; overflow: hidden; }
    @media (prefers-color-scheme: dark) { .left-pane { background: #1e1e2d; } }
    .search-bar { padding: 15px 15px 10px 15px; background: var(--mac-bg); }
    .search-input { width: 100%; padding: 10px; border: 1px solid var(--mac-border); border-radius: 8px; background: rgba(0,0,0,0.04); color: var(--text-main); font-size: 13px; box-sizing: border-box; outline:none;}
    .search-input:focus { border-color: #0066cc; background: #fff; }
    
    .customer-list { flex: 1; overflow-y: auto; }
    .customer-item { padding: 15px; border-bottom: 1px solid var(--mac-border); cursor: pointer; text-decoration: none; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; transition: 0.2s; background: transparent;}
    .customer-item:hover { background: rgba(0,102,204,0.05); }
    .customer-item.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    .customer-item.active .text-sub, .customer-item.active .bal-text { color: rgba(255,255,255,0.8) !important; }
    .text-sub { font-size: 11px; color: #888; display: block; margin-top: 4px; }
    
    /* Right Pane */
    .right-pane { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #fff;}
    @media (prefers-color-scheme: dark) { .right-pane { background: #1a1a2e; } }
    
    .right-header { padding: 20px 25px; border-bottom: 1px solid var(--mac-border); display: flex; justify-content: space-between; align-items: flex-start; background: #fff;}
    @media (prefers-color-scheme: dark) { .right-header { background: #1e1e2d; } }
    
    .avatar-circle { width: 50px; height: 50px; background: #e8f5e9; color: #2e7d32; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; flex-shrink: 0;}
    
    /* Tabs System */
    .tabs { display: flex; border-bottom: 1px solid var(--mac-border); background: rgba(0,0,0,0.02); padding: 0 25px;}
    .tab-btn { padding: 12px 20px; border: none; background: transparent; cursor: pointer; font-size: 13px; font-weight: 600; color: #666; border-bottom: 3px solid transparent; transition: 0.2s;}
    .tab-btn:hover { color: #0066cc; }
    .tab-btn.active { color: #0066cc; border-bottom-color: #0066cc; }
    
    .tab-content { flex: 1; padding: 25px; overflow-y: auto; display: none; }
    .tab-content.active { display: block; }
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { color: #888; font-weight: 600; font-size: 11px; text-transform: uppercase; background: rgba(0,0,0,0.02);}
    .num-col { text-align: right !important; }
    
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.2s;}
    .btn:hover { opacity: 0.9; }
    .btn-outline { background: transparent; border: 1px solid var(--mac-border); color: #555; }
    .btn-outline:hover { background: rgba(0,0,0,0.05); }
    .btn-success { background: #2e7d32; }
    
    .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    .status-Paid { background: #e8f5e9; color: #2e7d32; }
    .status-Unpaid { background: #fff3e0; color: #ef6c00; }
    .status-Pending { background: #f5f5f5; color: #666; }
    .status-Cleared { background: #e8f5e9; color: #2e7d32; }
    .status-Bounced { background: #ffebee; color: #c62828; }

    .map-box { width: 100%; height: 250px; border-radius: 8px; border: 1px solid var(--mac-border); background: #eee; overflow: hidden; margin-top: 15px;}

    /* Modals */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 500px; border: 1px solid var(--mac-border); max-height: 90vh; overflow-y:auto;}
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--mac-border); border-radius: 6px; background: transparent; color: var(--text-main); box-sizing: border-box; outline:none; font-size: 14px;}
    .form-control:focus { border-color: #0066cc; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
</style>

<?php if(!empty($data['error'])): ?>
    <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:8px; margin-bottom:15px;"><?= $data['error'] ?></div>
<?php endif; ?>
<?php if(!empty($data['success'])): ?>
    <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:8px; margin-bottom:15px;"><?= $data['success'] ?></div>
<?php endif; ?>

<div class="split-layout">
    
    <!-- Left Pane: Customer List with Advanced Filters -->
    <div class="left-pane">
        <div class="search-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search customers..." onkeyup="filterList()">
        </div>
        
        <!-- NEW: Filter Panel -->
        <div style="padding: 0 15px 15px 15px; border-bottom: 1px solid var(--mac-border); background: var(--mac-bg); display:flex; flex-direction:column; gap:8px;">
            <select id="filterRoute" class="search-input" onchange="filterList()" style="padding: 6px 10px; font-size: 12px;">
                <option value="">All Territories / Routes</option>
                <?php 
                $routes = [];
                foreach($data['customers'] as $c) {
                    if(!empty($c->mca_name) && !in_array($c->mca_name, $routes)) { $routes[] = $c->mca_name; }
                }
                sort($routes);
                foreach($routes as $r): ?>
                    <option value="<?= htmlspecialchars(strtolower($r)) ?>"><?= htmlspecialchars($r) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select id="filterStatus" class="search-input" onchange="filterList()" style="padding: 6px 10px; font-size: 12px;">
                <option value="">All Payment Statuses</option>
                <option value="owed">Has Unpaid Balance (Owed)</option>
                <option value="cleared">All Cleared (Zero Balance)</option>
            </select>
        </div>

        <div class="customer-list" id="custList">
            <?php foreach($data['customers'] as $c): ?>
                <?php $isActive = ($data['selected_customer'] && $data['selected_customer']->id == $c->id); ?>
                <a href="<?= APP_URL ?>/customer/index/<?= $c->id ?>" 
                   class="customer-item <?= $isActive ? 'active' : '' ?>"
                   data-route="<?= htmlspecialchars(strtolower($c->mca_name ?? '')) ?>"
                   data-outstanding="<?= $c->outstanding_balance ?>">
                    <div>
                        <strong class="c-name"><?= htmlspecialchars($c->name) ?></strong>
                        <span class="text-sub c-contact"><?= htmlspecialchars($c->email ?: $c->phone ?: 'No contact info') ?></span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 10px; color: <?= $isActive ? 'rgba(255,255,255,0.7)' : '#888' ?>;">Unpaid</span><br>
                        <span class="bal-text" style="font-size: 12px; font-weight: bold; color: <?= $isActive ? '#fff' : ($c->outstanding_balance > 0 ? '#c62828' : '#2e7d32') ?>;">
                            Rs: <?= number_format($c->outstanding_balance, 2) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right Pane: Tabbed Dashboard -->
    <div class="right-pane">
        <?php if(!$data['selected_customer']): ?>
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#888;">
                <div style="font-size: 40px; margin-bottom: 10px;">🏢</div>
                <p>Select a customer from the left to view their ledger and profile.</p>
            </div>
        <?php else: ?>
            <?php $c = $data['selected_customer']; $s = $data['stats']; ?>
            
            <!-- Fixed Top Header -->
            <div class="right-header">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div class="avatar-circle"><?= strtoupper(substr($c->name, 0, 2)) ?></div>
                    <div>
                        <h2 style="margin: 0 0 5px 0; display:flex; align-items:center; gap: 10px;">
                            <?= htmlspecialchars($c->name) ?>
                            <button class="btn btn-outline" style="font-size: 10px; padding: 2px 8px;" onclick="openModal('editModal')">✏️ Edit</button>
                        </h2>
                        <div style="font-size: 13px; color: #666; display: flex; gap: 15px;">
                            <span>📞 <?= htmlspecialchars($c->phone ?: 'N/A') ?></span>
                            <span>✉️ <?= htmlspecialchars($c->email ?: 'N/A') ?></span>
                            <span>🗺️ <?= htmlspecialchars($c->mca_name ?: 'Route Unassigned') ?></span>
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: #888; text-transform: uppercase; font-weight: bold;">Total Unpaid Balance</div>
                    <div style="font-size: 24px; font-weight: bold; color: <?= $s->outstanding > 0 ? '#c62828' : '#2e7d32' ?>;">
                        Rs: <?= number_format($s->outstanding, 2) ?>
                    </div>
                    <div style="margin-top: 5px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button class="btn btn-outline" style="padding: 8px 16px; border-color: #f57c00; color: #f57c00;" onclick="sharePortal(<?= $c->id ?>, '<?= htmlspecialchars(addslashes($c->phone ?? '')) ?>', '<?= htmlspecialchars(addslashes($c->name)) ?>')">🔗 Share B2B Portal</button>
                        <button class="btn btn-success" style="padding: 8px 16px;" onclick="openModal('paymentModal')">+ Record Payment</button>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('ledger')" id="btn_ledger">Activity Ledger</button>
                <button class="tab-btn" onclick="switchTab('invoices')" id="btn_invoices">Invoices</button>
                <button class="tab-btn" onclick="switchTab('cheques')" id="btn_cheques">Cheques (PDC)</button>
                <button class="tab-btn" onclick="switchTab('profile')" id="btn_profile">Profile & Map</button>
            </div>

            <!-- TAB 1: Activity Ledger -->
            <div class="tab-content active" id="tab_ledger">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="num-col">Debit (Dr)</th>
                            <th class="num-col">Credit (Cr)</th>
                            <th class="num-col">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['ledger'])): ?>
                            <tr><td colspan="5" style="text-align: center; color: #888; padding: 20px;">No financial activity yet.</td></tr>
                        <?php else: foreach($data['ledger'] as $l): ?>
                            <tr>
                                <td style="color:#666; font-size:12px;"><?= date('M d, Y', strtotime($l->date)) ?></td>
                                <td>
                                    <strong><?= $l->type ?></strong>
                                    
                                    <!-- NEW: Clickable Invoice Link Logic -->
                                    <?php if($l->type == 'Invoice'): ?>
                                        <a href="<?= APP_URL ?>/sales/show/<?= $l->id ?>" target="_blank" style="color:#0066cc; font-size: 11px; margin-left: 5px; font-weight:bold; text-decoration:none;">
                                            <?= htmlspecialchars($l->ref) ?> ↗
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#888; font-size: 11px; margin-left: 5px;"><?= htmlspecialchars($l->ref) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="num-col" style="color:#333; font-weight:500;"><?= $l->debit > 0 ? 'Rs: ' . number_format($l->debit, 2) : '-' ?></td>
                                <td class="num-col" style="color:#2e7d32; font-weight:500;"><?= $l->credit > 0 ? 'Rs: ' . number_format($l->credit, 2) : '-' ?></td>
                                <td class="num-col" style="font-weight:bold; color: <?= $l->balance > 0 ? '#c62828' : '#2e7d32' ?>;">Rs: <?= number_format($l->balance, 2) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 2: Latest Invoices -->
            <div class="tab-content" id="tab_invoices">
                <table class="data-table">
                    <thead><tr><th>Order #</th><th>Date</th><th class="num-col">Grand Total</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if(empty($data['invoices'])): ?>
                            <tr><td colspan="4" style="text-align:center; color:#888; padding: 20px;">No invoices found.</td></tr>
                        <?php else: foreach($data['invoices'] as $inv): ?>
                            <?php 
                                // Recalculate true grand total for UI list
                                $trueInvTotal = $inv->total_amount;
                                if($inv->global_discount_val > 0) {
                                    $trueInvTotal -= ($inv->global_discount_type == '%' ? ($inv->total_amount * $inv->global_discount_val / 100) : $inv->global_discount_val);
                                }
                                $trueInvTotal += $inv->tax_amount;
                            ?>
                            <tr>
                                <td><a href="<?= APP_URL ?>/sales/show/<?= $inv->id ?>" target="_blank" style="color:#0066cc; font-weight:bold; text-decoration:none;"><?= $inv->invoice_number ?></a></td>
                                <td style="color:#666; font-size:12px;"><?= date('M d, Y', strtotime($inv->invoice_date)) ?></td>
                                <td class="num-col" style="font-weight:bold;">Rs: <?= number_format($trueInvTotal, 2) ?></td>
                                <td><span class="status-badge status-<?= $inv->status ?>"><?= $inv->status ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 3: Latest Cheques -->
            <div class="tab-content" id="tab_cheques">
                <table class="data-table">
                    <thead><tr><th>Bank & Date</th><th class="num-col">Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if(empty($data['cheques'])): ?>
                            <tr><td colspan="3" style="text-align:center; color:#888; padding: 20px;">No cheques recorded.</td></tr>
                        <?php else: foreach($data['cheques'] as $chk): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($chk->bank_name) ?></strong><br>
                                    <span style="font-size:11px; color:#666;"><?= date('M d, Y', strtotime($chk->banking_date)) ?></span>
                                </td>
                                <td class="num-col" style="font-weight:bold;">Rs: <?= number_format($chk->amount, 2) ?></td>
                                <td><span class="status-badge status-<?= $chk->status ?>"><?= $chk->status ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 4: Profile & Map -->
            <div class="tab-content" id="tab_profile">
                <div class="grid-2">
                    <!-- Profile Stats -->
                    <div>
                        <div style="display:flex; gap:20px; margin-bottom: 20px;">
                            <div style="flex:1; background:rgba(0,0,0,0.02); padding:15px; border-radius:8px; border:1px solid var(--mac-border);">
                                <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:bold;">Total Orders</div>
                                <div style="font-size:20px; font-weight:bold; color:var(--text-main);"><?= $s->total_orders ?></div>
                            </div>
                            <div style="flex:1; background:rgba(0,0,0,0.02); padding:15px; border-radius:8px; border:1px solid var(--mac-border);">
                                <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:bold;">Total Billed</div>
                                <div style="font-size:20px; font-weight:bold; color:var(--text-main);">Rs: <?= number_format($s->total_billed, 2) ?></div>
                            </div>
                            <div style="flex:1; background:rgba(46,125,50,0.05); padding:15px; border-radius:8px; border:1px solid rgba(46,125,50,0.2);">
                                <div style="font-size:11px; color:#2e7d32; text-transform:uppercase; font-weight:bold;">Total Paid</div>
                                <div style="font-size:20px; font-weight:bold; color:#2e7d32;">Rs: <?= number_format($s->total_paid, 2) ?></div>
                            </div>
                        </div>

                        <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border); padding-bottom: 10px;">Update Details</h3>
                        <form action="<?= APP_URL ?>/customer/index/<?= $c->id ?>" method="POST">
                            <input type="hidden" name="action" value="update_customer">
                            <input type="hidden" name="customer_id" value="<?= $c->id ?>">
                            
                            <div class="form-group"><label>Customer/Company Name *</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($c->name) ?>" required></div>
                            <div class="grid-2">
                                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($c->email) ?>"></div>
                                <div class="form-group"><label>Phone Number</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($c->phone) ?>"></div>
                            </div>
                            <div class="form-group"><label>Billing Address</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($c->address) ?></textarea></div>
                            
                            <div class="grid-2">
                                <div class="form-group"><label>Latitude (GPS)</label><input type="text" name="latitude" class="form-control" value="<?= $c->latitude ?>"></div>
                                <div class="form-group"><label>Longitude (GPS)</label><input type="text" name="longitude" class="form-control" value="<?= $c->longitude ?>"></div>
                            </div>
                            
                            <div style="text-align: right; margin-top: 10px;">
                                <button type="submit" class="btn">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Map View -->
                    <div>
                        <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border); padding-bottom: 10px;">Location</h3>
                        <div class="map-box">
                            <?php if($c->latitude && $c->longitude): ?>
                                <iframe width="100%" height="100%" frameborder="0" style="border:0;" src="https://maps.google.com/maps?q=<?= $c->latitude ?>,<?= $c->longitude ?>&hl=en&z=14&output=embed"></iframe>
                            <?php else: ?>
                                <div style="display:flex; height:100%; align-items:center; justify-content:center; color:#aaa; font-size:13px; flex-direction:column;">
                                    <span style="font-size: 30px; margin-bottom: 5px;">🗺️</span>
                                    No GPS coordinates saved.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php if($data['selected_customer']): ?>
<!-- Record Payment Double-Entry Modal -->
<div class="modal" id="paymentModal">
    <div class="modal-content" style="width: 550px;">
        <h3 style="margin-top:0; color:#2e7d32;">Record Payment Received</h3>
        <p style="font-size: 12px; color: #666; margin-top:-5px; margin-bottom: 20px;">This will drop the customer's outstanding balance and automatically post to the ledger.</p>
        
        <form action="<?= APP_URL ?>/customer/index/<?= $c->id ?>" method="POST">
            <input type="hidden" name="action" value="record_payment">
            <input type="hidden" name="customer_id" value="<?= $c->id ?>">
            
            <!-- Hidden AR Account (Credit) -->
            <?php if($data['ar_account']): ?>
                <input type="hidden" name="ar_account_id" value="<?= $data['ar_account']->id ?>">
            <?php else: ?>
                <div style="color:#c62828; font-size:12px; margin-bottom:10px;">⚠ No "Accounts Receivable" found in Chart of Accounts!</div>
            <?php endif; ?>

            <div class="grid-2">
                <div class="form-group">
                    <label>Payment Amount (Rs:) *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" class="form-control" style="font-size: 18px; font-weight:bold; color:#2e7d32;" required>
                </div>
                <div class="form-group">
                    <label>Payment Date *</label>
                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" id="payMethod" class="form-control" onchange="togglePaymentFields()" required>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque (PDC)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Receipt / Ref #</label>
                    <input type="text" name="reference" class="form-control" placeholder="Optional">
                </div>
            </div>

            <div class="form-group" style="background: rgba(0,102,204,0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(0,102,204,0.2);">
                <label style="color:#0066cc;">Deposit To (Asset Ledger Account) *</label>
                <select name="asset_account_id" class="form-control" required style="background:#fff;">
                    <?php foreach($data['assets'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 11px; color:#666; margin: 5px 0 0 0;">Select your Cash in Hand, Bank, or Undeposited Funds account.</p>
            </div>

            <!-- Dynamic Cheque Fields -->
            <div id="chequeFields" style="display:none; background: #fff8e1; padding: 15px; border-radius: 8px; border: 1px dashed #ffb300; margin-bottom: 15px;">
                <h4 style="margin:0 0 10px 0; color:#f57c00; font-size:13px;">Cheque Details (Will save to PDC Register)</h4>
                <div class="form-group">
                    <label>Bank Name *</label>
                    <input type="text" name="cheque_bank" id="cq_bank" class="form-control" placeholder="e.g. Commercial Bank">
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Cheque Number *</label>
                        <input type="text" name="cheque_number" id="cq_num" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Banking Date *</label>
                        <input type="date" name="cheque_date" id="cq_date" class="form-control">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('paymentModal')">Cancel</button>
                <button type="submit" class="btn btn-success">Save Payment & Post Ledger</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Profile Modal (Still functional if triggered via top button) -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3 style="margin-top:0; color:#0066cc;">Edit Customer Details</h3>
        <p style="font-size: 12px; color: #666; margin-top:-5px; margin-bottom: 20px;">Or use the Profile tab for quick edits.</p>
        <form action="<?= APP_URL ?>/customer/index/<?= $c->id ?>" method="POST">
            <input type="hidden" name="action" value="update_customer">
            <input type="hidden" name="customer_id" value="<?= $c->id ?>">
            <div class="form-group"><label>Customer/Company Name *</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($c->name) ?>" required></div>
            <div class="grid-2">
                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($c->email) ?>"></div>
                <div class="form-group"><label>Phone Number</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($c->phone) ?>"></div>
            </div>
            <div class="form-group"><label>Billing Address</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($c->address) ?></textarea></div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab_' + tabName).classList.add('active');
        document.getElementById('btn_' + tabName).classList.add('active');
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // NEW: Advanced Filter Logic
    function filterList() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const routeFilter = document.getElementById('filterRoute').value.toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;
        
        const items = document.querySelectorAll('.customer-item');
        
        items.forEach(item => {
            const name = item.querySelector('.c-name').innerText.toLowerCase();
            const contact = item.querySelector('.c-contact').innerText.toLowerCase();
            const route = item.getAttribute('data-route');
            const outstanding = parseFloat(item.getAttribute('data-outstanding'));

            let matchesSearch = name.includes(query) || contact.includes(query);
            let matchesRoute = routeFilter === "" || route === routeFilter;
            let matchesStatus = true;

            if (statusFilter === 'owed') {
                matchesStatus = outstanding > 0;
            } else if (statusFilter === 'cleared') {
                matchesStatus = outstanding <= 0;
            }

            if (matchesSearch && matchesRoute && matchesStatus) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function togglePaymentFields() {
        const method = document.getElementById('payMethod').value;
        const cqDiv = document.getElementById('chequeFields');
        const bInput = document.getElementById('cq_bank');
        const nInput = document.getElementById('cq_num');
        const dInput = document.getElementById('cq_date');

        if (method === 'Cheque') {
            cqDiv.style.display = 'block';
            bInput.required = true;
            nInput.required = true;
            dInput.required = true;
        } else {
            cqDiv.style.display = 'none';
            bInput.required = false;
            nInput.required = false;
            dInput.required = false;
        }
    }

    function sharePortal(id, phone, name) {
        // Obscure the ID slightly to prevent basic URL guessing
        const encodedId = btoa(id);
        const portalLink = "<?= APP_URL ?>/portal/show/" + encodedId;
        const msg = `Hello ${name},\n\nYou can view your live account statement, outstanding balances, and download past invoices on our B2B Customer Portal here:\n\n${portalLink}\n\nThank you!`;
        
        if (phone && phone.trim() !== '') {
            let cleanPhone = phone.replace(/[^\d+]/g, '');
            if(cleanPhone.startsWith('0')) cleanPhone = '94' + cleanPhone.substring(1); 
            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        } else {
            // Fallback for desktop/no-phone
            prompt("Customer has no phone number saved. Copy the link below to share manually:", portalLink);
        }
    }
</script>