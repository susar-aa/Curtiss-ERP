<div class="sf-container">
    <div class="sf-page-header">
        <div class="sf-page-title">
            <h1>Returned Cheques</h1>
            <p>Process bounced cheques and manage return history</p>
        </div>
    </div>

    <?php if(!empty($data['success'])): ?>
    <div class="sf-alert success" style="margin-bottom: 24px;">
        <i class="fa-solid fa-circle-check sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Success</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($data['error'])): ?>
    <div class="sf-alert error" style="margin-bottom: 24px;">
        <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Error</div>
            <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Process Return Form -->
    <div class="table-panel" style="margin-bottom: 32px; padding: 24px;">
        <h2 style="font-size: 16px; margin-bottom: 16px;">Process a Cheque Return</h2>
        
        <?php if(empty($data['pending_cheques'])): ?>
            <div style="padding: 24px; text-align: center; color: var(--t-secondary); background: var(--c-fill); border-radius: 8px;">
                <p>No pending received cheques available to return.</p>
            </div>
        <?php else: ?>
            <form action="<?= APP_URL ?>/cheque/returns" method="POST">
                <input type="hidden" name="action" value="process_return">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="sf-form-group">
                        <label>Select Cheque <span style="color:var(--c-red)">*</span></label>
                        <select name="cheque_id" class="sf-input" required>
                            <option value="">-- Select Pending Cheque --</option>
                            <?php foreach($data['pending_cheques'] as $chk): ?>
                                <option value="<?= $chk->id ?>">
                                    <?= htmlspecialchars($chk->cheque_number) ?> - <?= htmlspecialchars($chk->customer_name ?? $chk->payee_name ?? 'Unknown') ?> (<?= number_format($chk->amount, 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sf-form-group">
                        <label>Return Date <span style="color:var(--c-red)">*</span></label>
                        <input type="date" name="return_date" class="sf-input" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="sf-form-group">
                        <label>Return Reason <span style="color:var(--c-red)">*</span></label>
                        <select name="return_reason" class="sf-input" id="return_reason" required onchange="toggleOtherReason()">
                            <option value="">-- Select Reason --</option>
                            <option value="Insufficient Funds">Insufficient Funds</option>
                            <option value="Signature Mismatch">Signature Mismatch</option>
                            <option value="Account Closed">Account Closed</option>
                            <option value="Payment Stopped">Payment Stopped</option>
                            <option value="Post-dated Cheque">Post-dated Cheque</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="sf-form-group" id="other_reason_group" style="display: none;">
                        <label>Specify Reason <span style="color:var(--c-red)">*</span></label>
                        <input type="text" name="other_reason" id="other_reason" class="sf-input" placeholder="Enter reason">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px;">
                    <div class="sf-form-group">
                        <label>Bank Return Charges</label>
                        <input type="number" step="0.01" name="return_charge" class="sf-input" placeholder="0.00">
                    </div>

                    <div class="sf-form-group">
                        <label>Charge Account</label>
                        <select name="charge_account_id" class="sf-input">
                            <option value="">-- Select Account --</option>
                            <?php if(!empty($data['expense_accounts'])): foreach($data['expense_accounts'] as $acc): ?>
                                <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="sf-btn" style="background: var(--c-red); color: white;" onclick="return confirm('Are you sure you want to mark this cheque as returned? This will reverse the customer\'s payment and reopen their invoices.');">Process Return</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- History Section -->
    <div class="table-panel">
        <h2 style="font-size: 16px; margin-bottom: 16px; padding: 20px 20px 0 20px;">Return History</h2>
        
        <?php if(empty($data['returned_cheques'])): ?>
            <div style="padding: 60px; text-align: center; color: var(--t-secondary);">
                <i class="fa-solid fa-clock-rotate-left" style="font-size: 32px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>No returned cheques history.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="sf-table">
                    <thead>
                        <tr>
                            <th>Cheque Details</th>
                            <th>Customer/Payee</th>
                            <th style="text-align: right;">Amount</th>
                            <th>Return Reason</th>
                            <th style="text-align: center;">Returned Date</th>
                            <th style="text-align: right;">Charge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['returned_cheques'] as $chk): ?>
                        <tr>
                            <td>
                                <div><strong style="font-family: var(--f-mono); font-size: 13px;"><?= htmlspecialchars($chk->cheque_number) ?></strong></div>
                                <div style="font-size: 11px; color: var(--t-secondary); margin-top: 2px;"><?= htmlspecialchars($chk->bank_name) ?></div>
                            </td>
                            <td><strong style="font-weight:600;"><?= htmlspecialchars($chk->customer_name ?? $chk->payee_name ?? '') ?></strong></td>
                            <td style="text-align: right; font-weight: 600;"><?= number_format($chk->amount, 2) ?></td>
                            <td><?= htmlspecialchars($chk->return_reason ?? '-') ?></td>
                            <td style="text-align: center;"><?= !empty($chk->returned_date) ? date('d-M-Y', strtotime($chk->returned_date)) : '-' ?></td>
                            <td style="text-align: right; color: var(--c-red);"><?= $chk->return_charge > 0 ? number_format($chk->return_charge, 2) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleOtherReason() {
        const select = document.getElementById('return_reason');
        const otherGroup = document.getElementById('other_reason_group');
        const otherInput = document.getElementById('other_reason');
        
        if (select.value === 'Other') {
            otherGroup.style.display = 'block';
            otherInput.required = true;
        } else {
            otherGroup.style.display = 'none';
            otherInput.required = false;
        }
    }
</script>
