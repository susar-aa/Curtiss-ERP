<?php
$isEdit = !empty($data['deposit']);
$dep = $data['deposit'];
$actionUrl = $isEdit ? APP_URL . '/deposit/update/' . $dep->id : APP_URL . '/deposit/store';
?>
<style>
    .create-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 20px;
        transition: color 0.15s;
    }
    .back-link:hover { color: #0066cc; }
    
    .grid-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 80px; /* space for sticky bar */
    }
    @media (max-width: 1024px) {
        .grid-layout { grid-template-columns: 1fr; }
    }

    .form-card {
        background: #fff;
        border: 1px solid var(--mac-border, #e2e8f0);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    @media (prefers-color-scheme: dark) {
        .form-card { background: #1e1e2d; border-color: #2d2d3d; }
    }

    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark, #1e293b);
        border-bottom: 1px solid var(--mac-border, #f1f5f9);
        padding-bottom: 12px;
        margin: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    @media (prefers-color-scheme: dark) {
        .card-title { color: #f1f5f9; border-color: #27272a; }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
    }
    .form-group input, .form-group select {
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        background: transparent;
        color: inherit;
        box-sizing: border-box;
        width: 100%;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #0066cc;
        outline: none;
    }
    @media (prefers-color-scheme: dark) {
        .form-group input, .form-group select { border-color: #3f3f46; }
    }

    /* Denominations table */
    .denom-table { width: 100%; border-collapse: collapse; }
    .denom-table td { padding: 8px 0; border-bottom: 1px dashed var(--mac-border, #f1f5f9); }
    @media (prefers-color-scheme: dark) { .denom-table td { border-color: #27272a; } }
    .denom-label { font-weight: 700; font-family: monospace; font-size: 14px; width: 80px; }
    .denom-input { width: 80px !important; text-align: center; padding: 6px !important; }
    .denom-subtotal { font-family: monospace; font-size: 14px; text-align: right; font-weight: 600; width: 120px; }

    /* Cheque listing */
    .cheque-list {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid var(--mac-border, #cbd5e1);
        border-radius: 8px;
    }
    @media (prefers-color-scheme: dark) { .cheque-list { border-color: #3f3f46; } }
    .cheque-table { width: 100%; border-collapse: collapse; text-align: left; }
    .cheque-table th, .cheque-table td { padding: 10px 14px; border-bottom: 1px solid var(--mac-border, #e2e8f0); font-size: 13px; }
    @media (prefers-color-scheme: dark) { .cheque-table th, .cheque-table td { border-color: #27272a; } }
    .cheque-table th { background: rgba(0,0,0,0.02); font-size: 11px; font-weight: bold; text-transform: uppercase; color: #64748b; }
    @media (prefers-color-scheme: dark) { .cheque-table th { background: rgba(255,255,255,0.02); } }
    .cheque-table tr:hover td { background: rgba(0,0,0,0.01); }

    /* Sticky Bottom Summary Bar */
    .sticky-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 1000;
        box-shadow: 0 -10px 25px -5px rgba(0,0,0,0.08);
    }
    @media (prefers-color-scheme: dark) {
        .sticky-bar {
            background: rgba(20, 20, 35, 0.88);
            border-top-color: rgba(255, 255, 255, 0.08);
        }
    }
    .summary-item {
        display: flex;
        flex-direction: column;
    }
    .summary-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; }
    .summary-val { font-size: 22px; font-weight: 800; font-family: monospace; }
    .summary-val.cash { color: #3b82f6; }
    .summary-val.cheque { color: #f59e0b; }
    .summary-val.total { color: #10b981; }

    .btn-group { display: flex; gap: 12px; }
</style>

<div class="create-container">
    <a href="<?= APP_URL ?>/deposit" class="back-link">⬅️ Back to Deposits</a>

    <h2 style="margin: 0 0 20px 0;"><?= $isEdit ? 'Edit Bank Deposit Draft' : 'Prepare New Bank Deposit' ?></h2>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 12px 16px; background:#ffebee; color:#c62828; border:1px solid #ffcdd2; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:14px;">
            ⚠️ <?= htmlspecialchars($data['error']) ?>
        </div>
    <?php endif; ?>

    <form action="<?= $actionUrl ?>" method="POST" id="depositForm">
        <!-- CRITICAL: CSRF Token injection -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="grid-layout">
            <!-- Left: Settings & Cash -->
            <div style="display: flex; flex-direction: column; gap: 25px;">
                <div class="form-card">
                    <h3 class="card-title">🏦 Deposit Header settings</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Deposit Date *</label>
                            <input type="date" name="deposit_date" value="<?= $isEdit ? htmlspecialchars($dep->deposit_date) : date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Destination Bank Account *</label>
                            <select name="destination_bank_account_id" required>
                                <option value="">-- Select Bank Account --</option>
                                <?php foreach($data['bank_accounts'] as $acc): ?>
                                    <option value="<?= $acc->id ?>" <?= ($isEdit && $dep->destination_bank_account_id == $acc->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($acc->account_name) ?> (<?= htmlspecialchars($acc->account_code) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <h3 class="card-title">💵 Cash Denominations</h3>
                    <table class="denom-table">
                        <?php 
                        $denoms = [5000, 2000, 1000, 500, 100, 50, 20];
                        foreach ($denoms as $d):
                            $field = "cash_" . $d;
                            $val = $isEdit ? intval($dep->$field) : 0;
                        ?>
                            <tr>
                                <td class="denom-label">Rs: <?= $d ?></td>
                                <td style="text-align: center; width: 40px;">✕</td>
                                <td style="width: 100px;">
                                    <input type="number" name="cash_<?= $d ?>" id="c_<?= $d ?>" class="denom-input form-group input" value="<?= $val ?>" min="0" oninput="calculateCash()">
                                </td>
                                <td class="denom-subtotal">Rs: <span id="sub_<?= $d ?>">0.00</span></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="font-weight: bold; font-size: 15px;">
                            <td colspan="3" style="padding-top: 15px; border-bottom: none;">Total Cash Deposit</td>
                            <td style="padding-top: 15px; text-align: right; color:#3b82f6; font-family: monospace; border-bottom: none;">Rs: <span id="cash_total_txt">0.00</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Right: Cheques -->
            <div class="form-card">
                <h3 class="card-title">✍️ Select Cheques to Deposit</h3>
                
                <div class="cheque-list">
                    <table class="cheque-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">Select</th>
                                <th>Cheque Detail</th>
                                <th>Customer</th>
                                <th style="text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Display cheques currently in this edit deposit -->
                            <?php 
                            $chequeIdsInDeposit = [];
                            if ($isEdit && !empty($data['deposit_cheques'])): 
                                foreach ($data['deposit_cheques'] as $item):
                                    $chequeIdsInDeposit[] = $item->cheque_id;
                            ?>
                                <tr style="background: rgba(79, 70, 229, 0.04);">
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="cheques[]" value="<?= $item->cheque_id ?>" data-amount="<?= $item->cheque_amount ?>" checked onchange="calculateCheques()">
                                    </td>
                                    <td>
                                        <strong>No: <?= htmlspecialchars($item->cheque_number) ?></strong><br>
                                        <span style="font-size: 11px; color:#64748b;"><?= htmlspecialchars($item->bank_name) ?> | <?= date('Y-m-d', strtotime($item->banking_date)) ?></span>
                                    </td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($item->customer_name) ?></td>
                                    <td style="text-align: right; font-weight: 700; font-family: monospace;">Rs: <?= number_format($item->cheque_amount, 2) ?></td>
                                </tr>
                            <?php 
                                endforeach;
                            endif; 
                            ?>

                            <!-- Display other pending cheques -->
                            <?php 
                            $hasPending = false;
                            foreach($data['pending_cheques'] as $chk): 
                                // Skip if already rendered above in edit mode
                                if (in_array($chk->id, $chequeIdsInDeposit)) continue;
                                $hasPending = true;
                            ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="cheques[]" value="<?= $chk->id ?>" data-amount="<?= $chk->amount ?>" onchange="calculateCheques()">
                                    </td>
                                    <td>
                                        <strong>No: <?= htmlspecialchars($chk->cheque_number) ?></strong><br>
                                        <span style="font-size: 11px; color:#64748b;"><?= htmlspecialchars($chk->bank_name) ?> | <?= date('Y-m-d', strtotime($chk->banking_date)) ?></span>
                                    </td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($chk->customer_name) ?></td>
                                    <td style="text-align: right; font-weight: 700; font-family: monospace;">Rs: <?= number_format($chk->amount, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if(!$hasPending && empty($data['deposit_cheques'])): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color:#94a3b8; padding:30px;">No pending cheques available in ledger.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-top: auto; padding-top: 15px; border-top: 1px solid var(--mac-border, #f1f5f9);">
                    <span>Total Cheques Selected</span>
                    <span style="color:#f59e0b; font-family: monospace;">Rs: <span id="cheque_total_txt">0.00</span></span>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Summary Bar -->
        <div class="sticky-bar">
            <div style="display: flex; gap: 40px; align-items: center;">
                <div class="summary-item">
                    <span class="summary-label">Cash Total</span>
                    <span class="summary-val cash">Rs: <span id="lbl_cash">0.00</span></span>
                </div>
                <div class="summary-item" style="border-left: 1px solid #cbd5e1; padding-left: 40px;">
                    <span class="summary-label">Cheque Total</span>
                    <span class="summary-val cheque">Rs: <span id="lbl_cheque">0.00</span></span>
                </div>
                <div class="summary-item" style="border-left: 1px solid #cbd5e1; padding-left: 40px;">
                    <span class="summary-label">Grand Total Deposit</span>
                    <span class="summary-val total">Rs: <span id="lbl_total">0.00</span></span>
                </div>
            </div>
            <div class="btn-group">
                <a href="<?= APP_URL ?>/deposit" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success" style="background:#10b981;">💾 Save Deposit Draft</button>
            </div>
        </div>
    </form>
</div>

<script>
    const denoms = [5000, 2000, 1000, 500, 100, 50, 20];

    function calculateCash() {
        let cashTotal = 0;
        denoms.forEach(d => {
            const count = parseInt(document.getElementById('c_' + d).value) || 0;
            const subtotal = count * d;
            document.getElementById('sub_' + d).innerText = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            cashTotal += subtotal;
        });

        document.getElementById('cash_total_txt').innerText = cashTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('lbl_cash').innerText = cashTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateGrandTotal();
    }

    function calculateCheques() {
        let chequeTotal = 0;
        const checkboxes = document.querySelectorAll('input[name="cheques[]"]:checked');
        checkboxes.forEach(cb => {
            chequeTotal += parseFloat(cb.getAttribute('data-amount')) || 0;
        });

        document.getElementById('cheque_total_txt').innerText = chequeTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('lbl_cheque').innerText = chequeTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let cash = 0;
        denoms.forEach(d => {
            const count = parseInt(document.getElementById('c_' + d).value) || 0;
            cash += count * d;
        });

        let cheque = 0;
        const checkboxes = document.querySelectorAll('input[name="cheques[]"]:checked');
        checkboxes.forEach(cb => {
            cheque += parseFloat(cb.getAttribute('data-amount')) || 0;
        });

        const grand = cash + cheque;
        document.getElementById('lbl_total').innerText = grand.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Initialize calculations on load
    window.addEventListener('DOMContentLoaded', () => {
        calculateCash();
        calculateCheques();
    });
</script>
