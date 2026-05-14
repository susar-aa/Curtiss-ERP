<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn:hover { background: #005bb5; }
    .btn-deposit { background: #2e7d32; }
    .btn-withdrawal { background: #c62828; }
    
    .bank-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .bank-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--mac-border); display: flex; flex-direction: column;}
    @media (prefers-color-scheme: dark) { .bank-card { background: rgba(255,255,255,0.02); } }
    .bank-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
    .bank-code { font-size: 12px; color: #888; margin-bottom: 15px; }
    .bank-balance { font-size: 24px; font-weight: bold; color: #0066cc; margin-bottom: 20px; }
    .bank-actions { margin-top: auto; display: flex; gap: 10px; border-top: 1px solid var(--mac-border); padding-top: 15px;}
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 500px; border: 1px solid var(--mac-border); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
</style>

<div class="header-actions">
    <h2>Banking & Cash Accounts</h2>
    <div>
        <button class="btn btn-deposit" onclick="openModal('deposit')">+ Record Deposit</button>
        <button class="btn btn-withdrawal" onclick="openModal('withdrawal')">- Record Withdrawal</button>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;">Transaction posted successfully!</div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
<?php endif; ?>

<div class="bank-grid">
    <?php if(empty($data['bank_accounts'])): ?>
        <p>No Asset accounts found in your Chart of Accounts.</p>
    <?php else: foreach($data['bank_accounts'] as $acc): ?>
        <div class="bank-card">
            <div class="bank-name"><?= htmlspecialchars($acc->account_name) ?></div>
            <div class="bank-code">Account Code: <?= htmlspecialchars($acc->account_code) ?></div>
            <div class="bank-balance">Rs: <?= number_format($acc->balance, 2) ?></div>
            <div class="bank-actions">
                <a href="<?= APP_URL ?>/banking/ledger/<?= $acc->id ?>" class="btn" style="flex: 1; text-align:center; background:#f4f5f7; color:#333; border: 1px solid #ddd;">Register</a>
                <a href="<?= APP_URL ?>/banking/reconcile/<?= $acc->id ?>" class="btn" style="flex: 1; text-align:center;">Reconcile</a>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- Quick Transaction Modal -->
<div class="modal" id="transactionModal">
    <div class="modal-content">
        <h3 id="modalTitle" style="margin-top:0;">Record Transaction</h3>
        <form action="<?= APP_URL ?>/banking" method="POST">
            <input type="hidden" name="action" value="quick_entry">
            <input type="hidden" name="type" id="transType" value="deposit">
            
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label id="bankLabel">Bank Account (Receiving Funds)</label>
                <select name="bank_account_id" class="form-control" required>
                    <?php foreach($data['bank_accounts'] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_name) ?> (Rs: <?= number_format($acc->balance, 2) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label id="offsetLabel">From Account (Source of Funds)</label>
                <select name="offset_account_id" class="form-control" required>
                    <option value="">Select Ledger Account...</option>
                    <?php 
                    $db = new Database();
                    $db->query("SELECT * FROM chart_of_accounts ORDER BY account_type, account_name");
                    $allAccs = $db->resultSet();
                    foreach($allAccs as $acc): 
                    ?>
                        <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Amount (Rs:)</label>
                <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required style="font-size: 18px; font-weight:bold;">
            </div>

            <div class="form-group">
                <label>Memo / Description</label>
                <input type="text" name="description" class="form-control" placeholder="e.g. Owner Capital Injection" required>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn" style="background:transparent; border:1px solid #ccc; color:#333;" onclick="document.getElementById('transactionModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" id="submitBtn">Save Transaction</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(type) {
        document.getElementById('transactionModal').style.display = 'flex';
        document.getElementById('transType').value = type;
        
        const title = document.getElementById('modalTitle');
        const bankLabel = document.getElementById('bankLabel');
        const offsetLabel = document.getElementById('offsetLabel');
        const btn = document.getElementById('submitBtn');

        if (type === 'deposit') {
            title.innerText = 'Record Deposit';
            title.style.color = '#2e7d32';
            bankLabel.innerText = 'Bank Account (Receiving Funds)';
            offsetLabel.innerText = 'From Account (Source of Funds / Revenue)';
            btn.style.background = '#2e7d32';
            btn.innerText = 'Save Deposit';
        } else {
            title.innerText = 'Record Withdrawal';
            title.style.color = '#c62828';
            bankLabel.innerText = 'Bank Account (Withdrawing From)';
            offsetLabel.innerText = 'To Account (Expense / Liability Paid)';
            btn.style.background = '#c62828';
            btn.innerText = 'Save Withdrawal';
        }
    }
</script>