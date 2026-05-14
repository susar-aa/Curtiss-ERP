<?php
// Bank Reconciliation View
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn:disabled { background: #ccc; cursor: not-allowed; }
    .form-control { padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); font-size: 16px; width: 200px;}
    
    .reconciliation-dashboard { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
    
    .recon-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    @media (prefers-color-scheme: dark) { .recon-table { background: #1e1e2d; } }
    .recon-table th, .recon-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 14px; }
    .recon-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    .num-col { text-align: right !important; }
    .debit { color: #2e7d32; font-weight: 500; }
    .credit { color: #c62828; font-weight: 500; }
    
    .summary-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 20px;}
    @media (prefers-color-scheme: dark) { .summary-box { background: #1e1e2d; } }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 15px; }
    .summary-row.total { font-size: 18px; font-weight: bold; border-top: 1px solid var(--mac-border); padding-top: 15px; margin-top: 15px;}
    
    .difference-box { padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 18px; transition: 0.3s;}
    .diff-zero { background: #e8f5e9; color: #2e7d32; border: 2px solid #a5d6a7; }
    .diff-alert { background: #ffebee; color: #c62828; border: 2px solid #ef9a9a; }
</style>

<div class="header-actions">
    <div>
        <a href="<?= APP_URL ?>/banking" style="color: #666; text-decoration:none; font-size:13px;">&larr; Back to Banking</a>
        <h2 style="margin: 10px 0 0 0;">Reconcile Account</h2>
        <p style="margin: 0; color: #888; font-size: 14px;"><?= htmlspecialchars($data['account']->account_code) ?> - <?= htmlspecialchars($data['account']->account_name) ?></p>
    </div>
</div>

<?php if(!empty($data['error'])): ?>
    <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
<?php endif; ?>
<?php if(!empty($data['success'])): ?>
    <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div>
<?php endif; ?>

<form action="<?= APP_URL ?>/banking/reconcile/<?= $data['account']->id ?>" method="POST" id="reconForm">
    <input type="hidden" name="action" value="save_reconciliation">

    <div class="reconciliation-dashboard">
        <!-- Left Column: Transactions List -->
        <div>
            <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px; background: rgba(0,0,0,0.02); padding: 15px; border-radius: 8px; border: 1px solid var(--mac-border);">
                <label style="font-weight: bold;">Ending Balance from Bank Statement:</label>
                <div>Rs: <input type="number" name="statement_balance" id="statementBalance" step="0.01" class="form-control" value="<?= $data['statement_balance'] ?>" oninput="calculateDifference()"></div>
            </div>

            <table class="recon-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">✓</th>
                        <th style="width: 100px;">Date</th>
                        <th>Description / Reference</th>
                        <th class="num-col" style="width: 120px;">Deposit (+)</th>
                        <th class="num-col" style="width: 120px;">Payment (-)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['uncleared'])): ?>
                        <tr><td colspan="5" style="text-align: center; color: #888; padding: 30px;">All transactions are currently cleared!</td></tr>
                    <?php else: foreach($data['uncleared'] as $t): ?>
                        <tr>
                            <td style="text-align: center;">
                                <!-- Store the impact value directly in a data attribute for easy JS calculation -->
                                <?php $impact = ($t->debit > 0) ? $t->debit : -$t->credit; ?>
                                <input type="checkbox" name="cleared_tx[]" value="<?= $t->id ?>" class="tx-checkbox" data-impact="<?= $impact ?>" onchange="calculateDifference()" style="cursor:pointer; width: 16px; height: 16px;">
                            </td>
                            <td><?= date('M d, y', strtotime($t->entry_date)) ?></td>
                            <td>
                                <?= htmlspecialchars($t->description) ?><br>
                                <span style="font-size:11px; color:#888;"><?= htmlspecialchars($t->reference) ?></span>
                            </td>
                            <td class="num-col debit"><?= $t->debit > 0 ? number_format($t->debit, 2) : '' ?></td>
                            <td class="num-col credit"><?= $t->credit > 0 ? number_format($t->credit, 2) : '' ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Right Column: Reconciliation Summary -->
        <div>
            <div class="summary-box">
                <h3 style="margin-top:0; border-bottom: 1px solid var(--mac-border); padding-bottom: 10px;">Summary</h3>
                
                <div class="summary-row">
                    <span style="color:#888;">Cleared Balance (System):</span>
                    <!-- The starting point is the system balance MINUS all UNCLEARED transactions -->
                    <?php 
                        $totalUncleared = 0;
                        foreach($data['uncleared'] as $t) {
                            $totalUncleared += ($t->debit > 0) ? $t->debit : -$t->credit;
                        }
                        $startingClearedBalance = $data['account']->balance - $totalUncleared;
                    ?>
                    <span id="clearedSystemBal" data-base="<?= $startingClearedBalance ?>">Rs: <?= number_format($startingClearedBalance, 2) ?></span>
                </div>

                <div class="summary-row">
                    <span style="color:#888;">Selected Deposits:</span>
                    <span id="selectedDeposits" style="color: #2e7d32;">Rs: 0.00</span>
                </div>
                <div class="summary-row">
                    <span style="color:#888;">Selected Payments:</span>
                    <span id="selectedPayments" style="color: #c62828;">Rs: 0.00</span>
                </div>

                <div class="summary-row total">
                    <span>New Cleared Balance:</span>
                    <span id="newClearedBal">Rs: <?= number_format($startingClearedBalance, 2) ?></span>
                </div>

                <div id="diffBox" class="difference-box diff-alert">
                    Difference: Rs: <span id="diffAmount">0.00</span>
                </div>

                <button type="submit" id="saveReconBtn" class="btn" style="width: 100%; font-size: 16px; padding: 12px;">Save Reconciliation</button>
            </div>
        </div>
    </div>
</form>

<script>
    function calculateDifference() {
        const checkboxes = document.querySelectorAll('.tx-checkbox');
        const statementBalInput = document.getElementById('statementBalance');
        const baseClearedStr = document.getElementById('clearedSystemBal').getAttribute('data-base');
        
        let baseCleared = parseFloat(baseClearedStr) || 0;
        let statementBal = parseFloat(statementBalInput.value) || 0;
        
        let sumDeposits = 0;
        let sumPayments = 0;

        checkboxes.forEach(cb => {
            if(cb.checked) {
                let impact = parseFloat(cb.getAttribute('data-impact'));
                if (impact > 0) { sumDeposits += impact; } 
                else { sumPayments += Math.abs(impact); }
            }
        });

        document.getElementById('selectedDeposits').innerText = 'Rs: ' + sumDeposits.toFixed(2);
        document.getElementById('selectedPayments').innerText = 'Rs: ' + sumPayments.toFixed(2);

        // Calculate new cleared balance
        let newCleared = baseCleared + sumDeposits - sumPayments;
        document.getElementById('newClearedBal').innerText = 'Rs: ' + newCleared.toFixed(2);

        // Calculate Difference (Statement vs New Cleared)
        let difference = statementBal - newCleared;
        const diffBox = document.getElementById('diffBox');
        const diffAmount = document.getElementById('diffAmount');
        const saveBtn = document.getElementById('saveReconBtn');

        diffAmount.innerText = Math.abs(difference).toFixed(2);

        // The goal of reconciliation is a difference of exactly 0
        if (Math.abs(difference) < 0.01) { // Floating point safety
            diffBox.className = 'difference-box diff-zero';
            diffBox.innerHTML = 'Perfectly Balanced! (Diff: 0.00)';
            saveBtn.disabled = false;
            saveBtn.innerText = 'Reconcile Account';
        } else {
            diffBox.className = 'difference-box diff-alert';
            diffBox.innerHTML = 'Difference: Rs: ' + difference.toFixed(2);
            saveBtn.disabled = true; 
            saveBtn.innerText = 'Check transactions to balance';
        }
    }

    // Run on page load to set initial state
    document.addEventListener("DOMContentLoaded", calculateDifference);
</script>