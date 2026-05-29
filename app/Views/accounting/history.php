<style>
    .ledger-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .account-summary-card {
        background: var(--mac-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--mac-border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    
    .summary-info h3 { margin: 0 0 5px 0; font-size: 20px; color: var(--text-main); }
    .summary-info p { margin: 0; color: #666; font-size: 13px; }
    
    .summary-balance { text-align: right; }
    .balance-label { font-size: 11px; text-transform: uppercase; color: #666; font-weight: 600; margin-bottom: 5px; }
    .balance-amount { font-size: 24px; font-weight: 700; color: #0066cc; font-family: monospace; }

    .filter-card {
        background: var(--mac-bg);
        border: 1px solid var(--mac-border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;}
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .btn-small { padding: 6px 12px; font-size: 12px; border-radius: 4px; }
    
    .quick-ranges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 10px;
    }
    .quick-range-btn {
        padding: 5px 12px;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid var(--mac-border);
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        color: var(--text-main);
        text-decoration: none;
        transition: 0.2s;
    }
    @media (prefers-color-scheme: dark) { .quick-range-btn { background: rgba(255, 255, 255, 0.05); } }
    .quick-range-btn:hover, .quick-range-btn.active {
        background: #0066cc;
        color: #fff;
        border-color: #0066cc;
    }

    .form-group label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #555; }
    @media (prefers-color-scheme: dark) { .form-group label { color: #aaa; } }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 6px; background: transparent; color: var(--text-main); font-size: 13px; box-sizing: border-box; }
    
    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid var(--mac-border);
    }
    @media (prefers-color-scheme: dark) { .ledger-table { background: #1e1e2d; } }
    .ledger-table th, .ledger-table td { padding: 12px 18px; text-align: left; font-size: 13px; border-bottom: 1px solid var(--mac-border); }
    .ledger-table th { background: rgba(0,0,0,0.02); font-weight: 600; color: #555; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
    @media (prefers-color-scheme: dark) { .ledger-table th { background: rgba(255,255,255,0.02); color: #aaa; } }
    
    .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .type-Asset { background: #e3f2fd; color: #1565c0; }
    .type-Liability { background: #ffebee; color: #c62828; }
    .type-Equity { background: #f3e5f5; color: #6a1b9a; }
    .type-Revenue { background: #e8f5e9; color: #2e7d32; }
    .type-Expense { background: #fff3e0; color: #ef6c00; }

    .amount-debit { color: #2e7d32; font-family: monospace; font-weight: 600; }
    .amount-credit { color: #c62828; font-family: monospace; font-weight: 600; }
    .amount-neutral { color: #aaa; font-family: monospace; }
</style>

<div class="ledger-header">
    <div>
        <h2 style="margin: 0 0 5px 0;">Account Transaction History</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Detailed general ledger transaction audit trail.</p>
    </div>
    <div>
        <a href="<?= APP_URL ?>/accounting/coa" class="btn btn-outline">← Back to Chart of Accounts</a>
    </div>
</div>

<!-- Account Selector -->
<div class="filter-card" style="margin-bottom: 25px; border-left: 5px solid #0066cc;">
    <div class="form-group" style="margin:0; display:flex; flex-wrap:wrap; gap:15px; align-items:center;">
        <label style="font-weight: 700; font-size: 14px; margin:0; min-width:180px;">Select Active Account Ledger:</label>
        <select onchange="window.location.href='<?= APP_URL ?>/accounting/history/' + this.value" class="form-control" style="max-width: 400px; font-weight: 600; border-color: #0066cc;">
            <?php foreach($data['all_accounts'] as $acc): ?>
                <option value="<?= $acc->id ?>" <?= $acc->id == $data['selected_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($acc->account_code) ?> - <?= htmlspecialchars($acc->account_name) ?> (<?= $acc->account_type ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if ($data['selected_account']): ?>
    <?php $acc = $data['selected_account']; ?>
    
    <div class="split-view-container" style="display: flex; gap: 20px; transition: all 0.3s ease; width: 100%;">
        <!-- Main Ledger Table Panel -->
        <div id="ledgerMainPanel" style="width: 100%; flex: 1; transition: all 0.3s ease; overflow-x: auto;">
            
            <!-- Account Information Summary -->
            <div class="account-summary-card">
                <div class="summary-info">
                    <span class="badge type-<?= $acc->account_type ?>" style="margin-bottom:8px; display:inline-block;"><?= $acc->account_type ?></span>
                    <h3><?= htmlspecialchars($acc->account_code) ?> - <?= htmlspecialchars($acc->account_name) ?></h3>
                    <p>Parent Account: <?= $acc->parent_id ? 'Sub-ledger Account' : 'Main Account Ledger' ?></p>
                </div>
                <div class="summary-balance">
                    <div class="balance-label">Current Ledger Balance</div>
                    <div class="balance-amount">Rs. <?= number_format($acc->balance, 2) ?></div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="filter-card">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; text-transform:uppercase; letter-spacing:0.5px; color:#555;">Query & Filter Ledger</h4>
                <form action="<?= APP_URL ?>/accounting/history/<?= $data['selected_id'] ?>" method="GET" id="filterForm">
                    <input type="hidden" name="quick_range" id="quickRangeInput" value="<?= htmlspecialchars($data['quick_range']) ?>">
                    
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="start_date" id="startDate" class="form-control" value="<?= htmlspecialchars($data['start_date']) ?>">
                        </div>
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="end_date" id="endDate" class="form-control" value="<?= htmlspecialchars($data['end_date']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Reference/Description Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search memo or ref..." value="<?= htmlspecialchars($data['search']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select name="tx_type" class="form-control">
                                <option value="all" <?= $data['tx_type'] === 'all' ? 'selected' : '' ?>>All Transactions</option>
                                <option value="debit" <?= $data['tx_type'] === 'debit' ? 'selected' : '' ?>>Debits Only (+)</option>
                                <option value="credit" <?= $data['tx_type'] === 'credit' ? 'selected' : '' ?>>Credits Only (-)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-top:15px; border-top:1px solid var(--mac-border); padding-top:15px;">
                        <div class="quick-ranges">
                            <span style="font-size:12px; font-weight:600; color:#666; margin-right:5px;">Quick Ranges:</span>
                            <button type="button" class="quick-range-btn <?= $data['quick_range'] === 'today' ? 'active' : '' ?>" onclick="setQuickRange('today')">Today</button>
                            <button type="button" class="quick-range-btn <?= $data['quick_range'] === 'last_week' ? 'active' : '' ?>" onclick="setQuickRange('last_week')">Last 7 Days</button>
                            <button type="button" class="quick-range-btn <?= $data['quick_range'] === 'last_month' ? 'active' : '' ?>" onclick="setQuickRange('last_month')">Last 30 Days</button>
                            <a href="<?= APP_URL ?>/accounting/history/<?= $data['selected_id'] ?>" class="quick-range-btn" style="background:#ffebee; color:#c62828;">Clear Filters</a>
                        </div>
                        <div>
                            <button type="submit" class="btn">Apply Filters</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Ledger Table -->
            <div style="overflow-x:auto;">
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 18%;">Reference</th>
                            <th style="width: 30%;">Description / Memo</th>
                            <th style="width: 13%; text-align: right;">Debit (Dr)</th>
                            <th style="width: 13%; text-align: right;">Credit (Cr)</th>
                            <th style="width: 14%; text-align: right;">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Prior/Starting Balance Row -->
                        <tr style="background:rgba(0,0,0,0.01); font-weight: 600;">
                            <td colspan="3" style="color: #666;">Opening / Prior Cumulative Balance</td>
                            <td colspan="2" style="text-align: right; color: #888;">-</td>
                            <td style="text-align: right; font-family:monospace; font-size:14px; color: #0066cc;">
                                Rs. <?= number_format($data['prior_balance'], 2) ?>
                            </td>
                        </tr>

                        <?php 
                        $runningBalance = $data['prior_balance'];
                        if(empty($data['transactions'])): 
                        ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #888; padding: 30px;">
                                    No transaction ledger entries found matching the filter criteria.
                                </td>
                            </tr>
                        <?php 
                        else: 
                            foreach($data['transactions'] as $tx): 
                                // Cumulative running balance computation
                                if (in_array($acc->account_type, ['Asset', 'Expense'])) {
                                    $runningBalance += floatval($tx->debit) - floatval($tx->credit);
                                } else {
                                    $runningBalance += floatval($tx->credit) - floatval($tx->debit);
                                }
                        ?>
                            <tr>
                                <td style="font-weight: 500;"><?= date('Y-m-d', strtotime($tx->entry_date)) ?></td>
                                <td>
                                    <span style="background:rgba(0,102,204,0.05); color:#0066cc; padding:2px 6px; border-radius:4px; font-weight:600; font-size:11px; font-family:monospace;"><?= htmlspecialchars($tx->reference) ?></span>
                                    <?php if (!empty($tx->invoice_id)): ?>
                                        <a href="javascript:void(0)" onclick="openInvoiceMiniView(<?= $tx->invoice_id ?>, '<?= htmlspecialchars($tx->invoice_number) ?>')" style="margin-left: 8px; font-size: 11px; color: #4f46e5; text-decoration: underline; font-weight: 600; white-space: nowrap;" class="mini-view-link">
                                            <i class="fa-solid fa-window-restore mr-0.5"></i> Mini View
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($tx->description) ?></td>
                                <td style="text-align: right;" class="<?= $tx->debit > 0 ? 'amount-debit' : 'amount-neutral' ?>">
                                    <?= $tx->debit > 0 ? 'Rs. ' . number_format($tx->debit, 2) : '-' ?>
                                </td>
                                <td style="text-align: right;" class="<?= $tx->credit > 0 ? 'amount-credit' : 'amount-neutral' ?>">
                                    <?= $tx->credit > 0 ? 'Rs. ' . number_format($tx->credit, 2) : '-' ?>
                                </td>
                                <td style="text-align: right; font-family:monospace; font-weight:600; font-size:14px; color: #333;">
                                    Rs. <?= number_format($runningBalance, 2) ?>
                                </td>
                            </tr>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Dynamic Slide-out Split-screen Panel for Invoice mini-view -->
        <div id="invoiceSidePanel" style="width: 0; min-width: 0; display: none; background: #fff; border: 1px solid var(--mac-border); border-radius: 12px; box-shadow: -5px 0 25px rgba(0,0,0,0.08); overflow: hidden; transition: all 0.3s ease; flex-direction: column;">
            <div style="padding: 15px; border-bottom: 1px solid var(--mac-border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #333;" id="sidePanelTitle">Invoice Mini View</h3>
                <button onclick="closeInvoiceMiniView()" style="background: transparent; border: none; font-size: 18px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; hover:background: #e2e8f0; transition: background 0.2s;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div style="flex: 1; position: relative; background: #525659; overflow: hidden; min-height: 600px;">
                <iframe id="invoiceIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>

<?php else: ?>
    <div style="padding:40px; background:#fff; border-radius:12px; text-align:center; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <p style="color:#666; margin:0;">Please select a ledger account above to display history.</p>
    </div>
<?php endif; ?>

<script>
    function setQuickRange(range) {
        document.getElementById('quickRangeInput').value = range;
        
        // Let form submit automatically
        document.getElementById('filterForm').submit();
    }

    function openInvoiceMiniView(id, number) {
        const mainPanel = document.getElementById('ledgerMainPanel');
        const sidePanel = document.getElementById('invoiceSidePanel');
        const iframe = document.getElementById('invoiceIframe');
        const title = document.getElementById('sidePanelTitle');

        // Configure Title
        title.innerHTML = `<i class="fa-solid fa-file-invoice text-indigo-600 mr-1.5"></i> Invoice ${number} <span style="font-size:11px; font-weight:normal; color:#64748b;">(Split-Screen)</span>`;
        
        // Set secure dynamic src URL to invoice print view
        iframe.src = `<?= APP_URL ?>/sales/show/${id}`;

        // Trigger dynamic layout sizing transformation
        sidePanel.style.display = 'flex';
        setTimeout(() => {
            sidePanel.style.width = '48%';
            mainPanel.style.width = '52%';
            mainPanel.style.flex = 'none';
        }, 10);
    }

    function closeInvoiceMiniView() {
        const mainPanel = document.getElementById('ledgerMainPanel');
        const sidePanel = document.getElementById('invoiceSidePanel');
        const iframe = document.getElementById('invoiceIframe');

        // Trigger collapse transition
        sidePanel.style.width = '0';
        mainPanel.style.width = '100%';
        mainPanel.style.flex = '1';

        setTimeout(() => {
            iframe.src = '';
            sidePanel.style.display = 'none';
        }, 300);
    }
</script>
