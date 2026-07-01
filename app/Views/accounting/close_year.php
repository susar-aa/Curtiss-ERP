<style>
    .header-actions { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
    .btn { padding: 10px 20px; background: #c62828; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px; font-weight:bold;}
    .btn:hover { background: #b71c1c; }
    .btn-secondary { padding: 8px 16px; background: #efefef; color: #333; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight:500; }
    .btn-secondary:hover { background: #e0e0e0; }
    .btn-outline-danger { padding: 6px 12px; background: transparent; color: #c62828; border: 1px solid #c62828; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
    .btn-outline-danger:hover { background: #c62828; color: #fff; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 500; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); font-size: 16px; box-sizing: border-box;}
    
    .warning-box { background: #ffebee; border-left: 4px solid #c62828; padding: 20px; border-radius: 4px; margin-bottom: 30px; color: #c62828; }
    .warning-box h3 { margin-top: 0; color: #c62828; }

    /* Modal styling */
    .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
    .modal-card { background: var(--bg-card, #fff); border-radius: 8px; width: 450px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); border: 1px solid var(--mac-border); }
    .modal-card h3 { margin-top: 0; color: #c62828; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .hidden { display: none !important; }
</style>

<div class="header-actions">
    <div>
        <h2 style="margin: 0 0 5px 0;">Close Financial Year</h2>
        <p style="margin: 0; color: #666;">Permanently lock the ledger and roll over Net Income.</p>
    </div>
</div>

<?php if(!empty($data['error'])): ?>
    <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px; border: 1px solid #ef9a9a;"><?= $data['error'] ?></div>
<?php endif; ?>
<?php if(!empty($data['success'])): ?>
    <div style="padding: 15px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px; border: 1px solid #a5d6a7; font-weight: bold;">✓ <?= $data['success'] ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start;">
    <!-- LEFT SIDE: CLOSING FORM -->
    <div>
        <div class="warning-box">
            <h3>⚠ Critical Accounting Operation</h3>
            <p>Running the Year-End Close will perform the following irreversible actions:</p>
            <ul style="line-height: 1.6; margin-bottom: 0;">
                <li>Calculate total Net Income/Loss for all unclosed transactions up to your chosen End Date.</li>
                <li>Generate a Journal Entry to force all Revenue and Expense accounts to a balance of Rs: 0.00.</li>
                <li>Transfer the Net Income directly into your selected Retained Earnings account.</li>
                <li>Permanently lock all journal entries prior to the End Date so they can no longer be edited or deleted.</li>
            </ul>
        </div>

        <form action="<?= APP_URL ?>/accounting/close_year" method="POST" style="background: rgba(0,0,0,0.02); padding: 25px; border-radius: 8px; border: 1px solid var(--mac-border);">
            <input type="hidden" name="action" value="close_books">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Financial Year Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Financial Year End Date (Cutoff Date)</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Transfer Net Income To (Equity Account)</label>
                <select name="retained_earnings_id" class="form-control" required>
                    <?php foreach($data['equity_accounts'] as $acc): ?>
                        <option value="<?= $acc->id ?>" <?= stripos($acc->account_name, 'retained') !== false ? 'selected' : '' ?>>
                            <?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Confirm Account Password (Security Verification)</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Enter your password to verify closing" required>
            </div>

            <div style="margin-top: 30px; text-align: right;">
                <button type="submit" class="btn" onclick="return confirm('Are you absolutely sure you want to close the books?');">Close Financial Year</button>
            </div>
        </form>
    </div>

    <!-- RIGHT SIDE: CLOSED YEARS LIST -->
    <div>
        <h3 style="margin-top: 0; margin-bottom: 15px; font-weight: 600;">Closed Financial Years</h3>
        <div style="background: rgba(0,0,0,0.01); border: 1px solid var(--mac-border); border-radius: 8px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.03); border-bottom: 1px solid var(--mac-border);">
                        <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; color: #666;">Year Info</th>
                        <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; color: #666;">Date Range</th>
                        <th style="padding: 12px 15px; font-size: 13px; font-weight: 600; color: #666; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['closed_years'])): ?>
                        <tr>
                            <td colspan="3" style="padding: 30px 15px; text-align: center; color: #999; font-style: italic;">
                                No closed financial years found.
                            </td>
                        </tr>
                    <?php else: foreach($data['closed_years'] as $fy): ?>
                        <tr style="border-bottom: 1px solid var(--mac-border); font-size: 14.5px;">
                            <td style="padding: 12px 15px;">
                                <strong style="color: var(--text-main);"><?= htmlspecialchars($fy->year_name) ?></strong><br>
                                <span style="font-size: 11.5px; color: #888;">Closed by <?= htmlspecialchars($fy->closed_by_name) ?> on <?= date('Y-m-d', strtotime($fy->closed_at)) ?></span>
                            </td>
                            <td style="padding: 12px 15px; color: #555;">
                                <?= date('M d, Y', strtotime($fy->start_date)) ?> –<br>
                                <?= date('M d, Y', strtotime($fy->end_date)) ?>
                            </td>
                            <td style="padding: 12px 15px; text-align: center; vertical-align: middle;">
                                <button type="button" class="btn-outline-danger" onclick="openRevertModal(<?= $fy->id ?>, '<?= htmlspecialchars($fy->year_name) ?>')">
                                    Revert Close
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Revert Close Password Confirmation -->
<div id="revertModal" class="modal-backdrop hidden">
    <div class="modal-card">
        <h3>Revert Financial Year Close</h3>
        <p>You are about to unlock historical transactions and reverse the Year-End Closing Journal Entry for <strong id="revertYearName"></strong>.</p>
        <p style="color: #c62828; font-weight: 500; font-size: 13.5px;">⚠ This will unlock all entries for editing, which may affect subsequent closed periods if done out of sequence.</p>
        
        <form action="<?= APP_URL ?>/accounting/revert_close_year" method="POST" style="margin-top: 20px;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="fy_id" id="revertFyId" value="">
            
            <div class="form-group">
                <label>Confirm Account Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Enter password to confirm year reversal" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeRevertModal()">Cancel</button>
                <button type="submit" class="btn">Confirm Reversal</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRevertModal(fyId, yearName) {
        document.getElementById('revertFyId').value = fyId;
        document.getElementById('revertYearName').textContent = yearName;
        document.getElementById('revertModal').classList.remove('hidden');
    }

    function closeRevertModal() {
        document.getElementById('revertModal').classList.add('hidden');
    }
</script>