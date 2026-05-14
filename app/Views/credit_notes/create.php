<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #c62828; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .btn-outline { background: transparent; border: 1px solid #c62828; color: #c62828; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .cn-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .cn-table th, .cn-table td { padding: 10px; border-bottom: 1px solid var(--mac-border); }
    .cn-table th { background-color: rgba(198, 40, 40, 0.05); text-align: left; color:#c62828;}
    .cn-table input, .cn-table select { width: 100%; border: 1px solid transparent; background: transparent; color: var(--text-main); padding: 5px; box-sizing: border-box; }
    .cn-table input:focus, .cn-table select:focus { border: 1px solid #c62828; outline: none; border-radius: 4px;}
    .cn-table input[type="number"] { text-align: right; }
    .total-box { float: right; width: 300px; padding: 20px; background: rgba(198, 40, 40, 0.05); border-radius: 8px; margin-top: 20px; text-align: right; font-size: 18px; font-weight: bold; color:#c62828;}
</style>

<div class="card">
    <div class="header-actions">
        <h2 style="color:#c62828;">Issue Credit Note</h2>
        <a href="<?= APP_URL ?>/creditnote" style="color: #666; text-decoration:none;">&larr; Back</a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/creditnote/create" method="POST" id="cnForm">
        
        <div style="background: rgba(198,40,40,0.05); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(198,40,40,0.2);">
            <h4 style="margin-top:0; color:#c62828;">Reverse Accounting Routing</h4>
            <p style="font-size:12px; color:#555; margin-top:-5px;">This will Debit Revenue (reducing income) and Credit AR (reducing customer balance).</p>
            <div class="grid-2">
                <div class="form-group">
                    <label>Debit Account (Sales/Income to Reverse)</label>
                    <select name="revenue_account" class="form-control" required>
                        <?php foreach($data['revenues'] as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Credit Account (Accounts Receivable to Drop)</label>
                    <select name="ar_account" class="form-control" required>
                        <?php foreach($data['assets'] as $acc): ?>
                            <option value="<?= $acc->id ?>" <?= strpos(strtolower($acc->account_name), 'receivable') !== false ? 'selected' : '' ?>><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" class="form-control" required>
                    <option value="">Select Customer...</option>
                    <?php foreach($data['customers'] as $cust): ?>
                        <option value="<?= $cust->id ?>"><?= htmlspecialchars($cust->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Credit Note Number</label>
                <input type="text" name="credit_note_number" class="form-control" value="<?= $data['credit_note_number'] ?>" required>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="note_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <table class="cn-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width: 50%;">Item being Refunded/Credited</th>
                    <th style="width: 15%; text-align:right;">Qty</th>
                    <th style="width: 15%; text-align:right;">Price (Rs:)</th>
                    <th style="width: 15%; text-align:right;">Total (Rs:)</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="cnBody">
                <tr>
                    <td>
                        <select name="desc[]" onchange="autoFillPrice(this)" required>
                            <option value="">Select Product...</option>
                            <?php foreach($data['catalog_items'] as $item): ?>
                                <option value="<?= htmlspecialchars($item->name) ?>" data-price="<?= $item->price ?>">
                                    <?= htmlspecialchars($item->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="qty[]" step="1" min="1" value="1" onchange="calcTotals()" required></td>
                    <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" onchange="calcTotals()" required></td>
                    <td><input type="text" class="line-total" value="0.00" readonly style="font-weight:bold; color:#c62828;"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <button type="button" class="btn btn-outline" style="margin-top: 10px; font-size:12px; padding:6px 10px;" onclick="addRow()">+ Add Line</button>

        <div class="total-box">
            Total Credit: Rs: <span id="grandTotal">0.00</span>
        </div>

        <div style="clear:both; margin-top: 20px; text-align: right;">
            <button type="submit" class="btn" style="padding: 12px 24px; font-size: 16px;">Issue Credit Note & Post Ledger</button>
        </div>
    </form>
</div>

<script>
    const catalogOptions = `
        <option value="">Select Product...</option>
        <?php foreach($data['catalog_items'] as $item): ?>
            <option value="<?= htmlspecialchars($item->name) ?>" data-price="<?= $item->price ?>">
                <?= htmlspecialchars($item->name) ?>
            </option>
        <?php endforeach; ?>
    `;

    function autoFillPrice(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const tr = selectElement.closest('tr');
        const priceInput = tr.querySelector('input[name="price[]"]');
        priceInput.value = parseFloat(price).toFixed(2);
        calcTotals();
    }

    function addRow() {
        const tbody = document.getElementById('cnBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="desc[]" onchange="autoFillPrice(this)" required>${catalogOptions}</select></td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" onchange="calcTotals()" required></td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" onchange="calcTotals()" required></td>
            <td><input type="text" class="line-total" value="0.00" readonly style="font-weight:bold; color:#c62828;"></td>
            <td><button type="button" class="btn" style="padding: 4px 8px; font-size:10px; background:#666;" onclick="removeRow(this)">X</button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) { btn.closest('tr').remove(); calcTotals(); }

    function calcTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#cnBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const total = qty * price;
            row.querySelector('.line-total').value = total.toFixed(2);
            grandTotal += total;
        });
        document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
    }
</script>