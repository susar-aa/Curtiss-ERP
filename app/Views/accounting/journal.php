<?php
// Journal Entry View
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
    .btn:hover { background: #005bb5; }
    .btn-secondary { background: #e0e0e0; color: #333; }
    .btn-secondary:hover { background: #ccc; }
    .btn-danger { background: #ff3b30; padding: 6px 10px; font-size: 12px;}
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    
    .journal-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .journal-table th, .journal-table td { padding: 10px; text-align: left; border-bottom: 1px solid var(--mac-border); }
    .journal-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    .journal-table input[type="number"] { width: 120px; text-align: right; }
    
    .totals-row { font-weight: bold; background-color: rgba(0,102,204,0.05); }
    .diff-warning { color: #ff3b30; font-size: 12px; font-weight: bold; display: none; }
    
    .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
</style>

<div class="card">
    <div class="header-actions">
        <h2>General Journal</h2>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div class="alert alert-error"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['success'])): ?>
        <div class="alert alert-success"><?= $data['success'] ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/accounting/journal" method="POST" id="journalForm" style="background: rgba(0,0,0,0.02); padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid var(--mac-border);">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="form-group" style="margin-bottom: 20px; max-width: 550px;">
            <label style="font-weight: 600; font-size: 13px;">Load Journal Template</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <select id="templateSelector" class="form-control" onchange="loadTemplate(this.value)" style="flex: 1;">
                    <option value="">-- Select Template --</option>
                    <option value="rent">Record Rent Expense (Debit Rent Expense, Credit Cash/Bank)</option>
                    <option value="utility">Record Utility Bill (Debit Utilities Expense, Credit Cash/Bank)</option>
                    <option value="revenue">Record Customer Payment (Debit Cash/Bank, Credit Account Receivable)</option>
                    <option value="payroll">Record Payroll (Debit Salaries Expense, Credit Cash/Bank)</option>
                </select>
                <button type="button" id="undoTemplateBtn" class="btn btn-secondary hidden" onclick="undoTemplateLoad()" style="font-size: 12px; height: 38px; white-space: nowrap;">Undo Load</button>
            </div>
        </div>
        
        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Date</label>
                <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Reference #</label>
                <input type="text" name="reference" class="form-control" placeholder="e.g. INV-1001">
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Description</label>
                <input type="text" name="description" class="form-control" placeholder="Memo for this journal entry..." required>
            </div>
        </div>

        <table class="journal-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width: 35%;">Account</th>
                    <th style="width: 35%;">Description / Memo</th>
                    <th style="width: 13%;">Debit (Rs:)</th>
                    <th style="width: 13%;">Credit (Rs:)</th>
                    <th style="width: 4%;"></th>
                </tr>
            </thead>
            <tbody id="journalBody">
                <!-- Initial 2 lines required for double entry -->
                <tr>
                    <td>
                        <select name="account_id[]" class="form-control" required>
                            <option value="">Select Account...</option>
                            <?php foreach($data['accounts'] as $acc): ?>
                                <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note (optional)"></td>
                    <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                    <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <select name="account_id[]" class="form-control" required>
                            <option value="">Select Account...</option>
                            <?php foreach($data['accounts'] as $acc): ?>
                                <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note (optional)"></td>
                    <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                    <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" onchange="calcTotals()"></td>
                    <td></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <button type="button" class="btn btn-secondary" onclick="addRow()" style="font-size: 12px;">+ Add Line</button>
                    </td>
                    <td class="totals-row" id="totalDebit">0.00</td>
                    <td class="totals-row" id="totalCredit">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
            <span class="diff-warning" id="diffWarning">Error: Debits and Credits must balance!</span>
            <button type="submit" class="btn" id="btnSubmit" style="margin-left: auto;">Post Journal Entry</button>
        </div>
    </form>

    <h3>Recent Journal Entries</h3>
    <table class="journal-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Status</th>
                <th>Posted By</th>
                <th style="text-align: right; width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['entries'])): ?>
            <tr><td colspan="6" style="text-align: center; color: #888;">No entries found.</td></tr>
            <?php else: ?>
                <?php foreach($data['entries'] as $entry): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($entry->entry_date)) ?></td>
                    <td><strong><?= htmlspecialchars($entry->reference) ?></strong></td>
                    <td><?= htmlspecialchars($entry->description) ?></td>
                    <td>
                        <?php if($entry->status === 'Posted'): ?>
                            <span style="color: #2e7d32; font-weight: bold;">✓ <?= $entry->status ?></span>
                        <?php elseif($entry->status === 'Voided'): ?>
                            <span style="color: #c62828; text-decoration: line-through; font-weight: bold;"><?= $entry->status ?></span>
                        <?php else: ?>
                            <span style="color: #f59e0b; font-weight: bold;"><?= $entry->status ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($entry->username) ?></td>
                    <td style="text-align: right;">
                        <?php if($entry->status === 'Posted' && !$entry->is_closed): ?>
                            <form action="<?= APP_URL ?>/accounting/void_journal" method="POST" onsubmit="return confirm('Are you sure you want to void this journal entry? This will reverse all ledger balances for these accounts.')" style="display:inline;">
                                <input type="hidden" name="entry_id" value="<?= $entry->id ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Void</button>
                            </form>
                        <?php else: ?>
                            <span style="color: #aaa; font-size: 11px;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <?php if ($data['total_pages'] > 1): ?>
    <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
        <div style="color: var(--t-secondary);">
            Showing page <?= $data['page'] ?> of <?= $data['total_pages'] ?> (<?= $data['total_entries'] ?> total entries)
        </div>
        <div style="display: flex; gap: 5px;">
            <?php if ($data['page'] > 1): ?>
                <a href="?page=<?= $data['page'] - 1 ?>" class="btn btn-secondary" style="padding: 5px 10px; text-decoration: none; font-size: 12px;">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $data['page'] - 2);
            $end = min($data['total_pages'], $data['page'] + 2);
            for ($i = $start; $i <= $end; $i++): 
            ?>
                <a href="?page=<?= $i ?>" class="btn <?= $i === $data['page'] ? '' : 'btn-secondary' ?>" style="padding: 5px 10px; text-decoration: none; font-size: 12px;"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($data['page'] < $data['total_pages']): ?>
                <a href="?page=<?= $data['page'] + 1 ?>" class="btn btn-secondary" style="padding: 5px 10px; text-decoration: none; font-size: 12px;">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    const accountOptions = `
        <option value="">Select Account...</option>
        <?php foreach($data['accounts'] as $acc): ?>
            <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_code . ' - ' . $acc->account_name) ?></option>
        <?php endforeach; ?>
    `;

    let previousState = null;

    function addRow() {
        const tbody = document.getElementById('journalBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="account_id[]" class="form-control" required>${accountOptions}</select></td>
            <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note (optional)"></td>
            <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" onchange="calcTotals()"></td>
            <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" onchange="calcTotals()"></td>
            <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calcTotals();
    }

    function calcTotals() {
        let debits = 0;
        let credits = 0;
        
        document.querySelectorAll('.debit-input').forEach(input => {
            debits += parseFloat(input.value) || 0;
        });
        
        document.querySelectorAll('.credit-input').forEach(input => {
            credits += parseFloat(input.value) || 0;
        });

        document.getElementById('totalDebit').innerText = debits.toFixed(2);
        document.getElementById('totalCredit').innerText = credits.toFixed(2);

        const btnSubmit = document.getElementById('btnSubmit');
        const warning = document.getElementById('diffWarning');
        const linesCount = document.querySelectorAll('#journalBody tr').length;

        if (linesCount === 0) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerText = 'Error: The journal entry is empty. Please add at least 2 lines.';
            warning.style.display = 'inline-block';
        } else if (linesCount < 2) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerText = 'Error: A journal entry must have at least 2 lines to balance.';
            warning.style.display = 'inline-block';
        } else if (debits !== credits) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerText = 'Error: Debits and Credits must balance!';
            warning.style.display = 'inline-block';
        } else if (debits === 0) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            warning.innerText = 'Error: Total Debit and Credit amounts must be greater than zero.';
            warning.style.display = 'inline-block';
        } else {
            btnSubmit.disabled = false;
            btnSubmit.style.opacity = '1';
            warning.style.display = 'none';
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function captureState() {
        const tbody = document.getElementById('journalBody');
        const rows = tbody.querySelectorAll('tr');
        const lines = [];
        rows.forEach(row => {
            const select = row.querySelector('select');
            const descInput = row.querySelector('input[placeholder*="note"]');
            const debitInput = row.querySelector('.debit-input');
            const creditInput = row.querySelector('.credit-input');
            lines.push({
                account_id: select ? select.value : '',
                line_description: descInput ? descInput.value : '',
                debit: debitInput ? debitInput.value : '',
                credit: creditInput ? creditInput.value : ''
            });
        });

        previousState = {
            entry_date: document.querySelector('input[name="entry_date"]').value,
            reference: document.querySelector('input[name="reference"]').value,
            description: document.querySelector('input[name="description"]').value,
            lines: lines
        };
    }

    function undoTemplateLoad() {
        if (!previousState) return;

        document.querySelector('input[name="entry_date"]').value = previousState.entry_date;
        document.querySelector('input[name="reference"]').value = previousState.reference;
        document.querySelector('input[name="description"]').value = previousState.description;

        const tbody = document.getElementById('journalBody');
        tbody.innerHTML = '';
        
        previousState.lines.forEach((line, index) => {
            const tr = document.createElement('tr');
            const isInitial = index < 2; // Initial rows don't have delete button
            tr.innerHTML = `
                <td><select name="account_id[]" class="form-control" required>${accountOptions}</select></td>
                <td><input type="text" name="line_description[]" class="form-control" placeholder="Line-specific note (optional)" value="${escapeHtml(line.line_description)}"></td>
                <td><input type="number" name="debit[]" class="form-control debit-input" step="0.01" min="0" value="${line.debit}" onchange="calcTotals()"></td>
                <td><input type="number" name="credit[]" class="form-control credit-input" step="0.01" min="0" value="${line.credit}" onchange="calcTotals()"></td>
                <td>${isInitial ? '' : '<button type="button" class="btn btn-danger" onclick="removeRow(this)">X</button>'}</td>
            `;
            tbody.appendChild(tr);
            tr.querySelector('select').value = line.account_id;
        });

        previousState = null;
        document.getElementById('undoTemplateBtn').classList.add('hidden');
        
        showFeedback("Prior state restored successfully.");
        calcTotals();
    }

    function showFeedback(message) {
        let toast = document.getElementById('journalFeedbackToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'journalFeedbackToast';
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.background = '#34c759';
            toast.style.color = '#fff';
            toast.style.padding = '12px 24px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            toast.style.zIndex = '9999';
            toast.style.fontFamily = 'sans-serif';
            toast.style.fontSize = '14px';
            toast.style.fontWeight = '600';
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 300);
        }, 3000);
    }
    
    function loadTemplate(type) {
        if (!type) return;

        // Check if there is existing data that would be lost
        let hasData = false;
        const tbody = document.getElementById('journalBody');
        const selects = tbody.querySelectorAll('select');
        const debits = tbody.querySelectorAll('.debit-input');
        const credits = tbody.querySelectorAll('.credit-input');
        const descInput = document.querySelector('input[name="description"]');
        const refInput = document.querySelector('input[name="reference"]');
        
        if (descInput.value || refInput.value) {
            hasData = true;
        }
        selects.forEach(sel => { if (sel.value) hasData = true; });
        debits.forEach(deb => { if (deb.value && parseFloat(deb.value) > 0) hasData = true; });
        credits.forEach(cred => { if (cred.value && parseFloat(cred.value) > 0) hasData = true; });

        if (hasData) {
            if (!confirm("Loading this template will clear your current journal lines. Do you want to proceed?")) {
                document.getElementById('templateSelector').value = '';
                return;
            }
        }

        // Capture current state before loading new template
        captureState();
        document.getElementById('undoTemplateBtn').classList.remove('hidden');

        // Clear existing lines to start fresh
        tbody.innerHTML = '';

        // Add 2 lines
        addRow();
        addRow();

        const newSelects = tbody.querySelectorAll('select');
        const newDebits = tbody.querySelectorAll('.debit-input');

        const options1 = Array.from(newSelects[0].options);
        const options2 = Array.from(newSelects[1].options);

        let memo = "";
        let match1 = null;
        let match2 = null;

        if (type === 'rent') {
            memo = "Rent Expense for current month";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('rent'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
        } else if (type === 'utility') {
            memo = "Utility Bill payment";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('utilit') || opt.text.toLowerCase().includes('electricity') || opt.text.toLowerCase().includes('water'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
        } else if (type === 'revenue') {
            memo = "Record customer payment receipt";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('receivable') || opt.text.toLowerCase().includes('debtor'));
        } else if (type === 'payroll') {
            memo = "Monthly salary disbursement";
            match1 = options1.find(opt => opt.text.toLowerCase().includes('salari') || opt.text.toLowerCase().includes('salary') || opt.text.toLowerCase().includes('wage') || opt.text.toLowerCase().includes('payroll'));
            match2 = options2.find(opt => opt.text.toLowerCase().includes('cash') || opt.text.toLowerCase().includes('bank'));
        }

        if (match1) newSelects[0].value = match1.value;
        if (match2) newSelects[1].value = match2.value;

        // Set memo
        document.querySelector('input[name="description"]').value = memo;

        // Pre-fill placeholder/focus
        newDebits[0].focus();
        
        // Reset selector
        document.getElementById('templateSelector').value = '';

        showFeedback("Template loaded! Rent/Utilities/Revenue/Payroll accounts pre-filled.");
        calcTotals();
    }
    
    // Run once on load to disable button initially
    calcTotals();
</script>