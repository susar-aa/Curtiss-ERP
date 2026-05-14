<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .btn-danger { background: #ff3b30; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box; }
    .grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .invoice-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .invoice-table th, .invoice-table td { padding: 10px; border-bottom: 1px solid var(--mac-border); }
    .invoice-table th { background-color: rgba(0,0,0,0.02); text-align: left; }
    .invoice-table input, .invoice-table select { width: 100%; border: 1px solid transparent; background: transparent; color: var(--text-main); padding: 5px; box-sizing: border-box; }
    .invoice-table input:focus, .invoice-table select:focus { border: 1px solid #0066cc; outline: none; border-radius: 4px;}
    .invoice-table input[type="number"] { text-align: right; }
    .totals-wrapper { display: flex; justify-content: flex-end; margin-top: 20px; }
    .total-box { width: 350px; padding: 20px; background: rgba(0,0,0,0.02); border-radius: 8px; }
    .total-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; }
    .grand-total { font-size: 18px; font-weight: bold; border-top: 2px solid var(--mac-border); padding-top: 10px; margin-top: 10px; color: #0066cc;}
</style>

<div class="card">
    <div class="header-actions">
        <h2>Create Invoice</h2>
        <a href="<?= APP_URL ?>/sales" style="color: #666; text-decoration:none;">&larr; Back</a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/sales/create" method="POST" id="invoiceForm">
        
        <div style="background: rgba(0,102,204,0.05); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(0,102,204,0.2);">
            <h4 style="margin-top:0; color:#0066cc;">ERP Accounting Routing</h4>
            <div class="grid-2">
                <div class="form-group">
                    <label>Debit Account (Accounts Receivable)</label>
                    <select name="ar_account" class="form-control" required>
                        <?php foreach($data['assets'] as $acc): ?>
                            <option value="<?= $acc->id ?>" <?= strpos(strtolower($acc->account_name), 'receivable') !== false ? 'selected' : '' ?>><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Credit Account (Sales/Income)</label>
                    <select name="revenue_account" class="form-control" required>
                        <?php foreach($data['revenues'] as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid-4">
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
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" value="<?= $data['invoice_number'] ?>" required>
            </div>
            <div class="form-group">
                <label>Invoice Date</label>
                <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
            </div>
        </div>

        <table class="invoice-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width: 50%;">Product / Service</th>
                    <th style="width: 15%; text-align:right;">Qty</th>
                    <th style="width: 15%; text-align:right;">Price (Rs:)</th>
                    <th style="width: 15%; text-align:right;">Total (Rs:)</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="invoiceBody">
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
                    <td><input type="text" class="line-total" value="0.00" readonly style="font-weight:bold; color:var(--text-main);"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <button type="button" class="btn btn-outline" style="margin-top: 10px; font-size:12px;" onclick="addRow()">+ Add Line</button>

        <div class="totals-wrapper">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subTotal">Rs: 0.00</span>
                </div>
                
                <div class="form-group" style="margin-bottom: 5px;">
                    <label>Apply Tax Rate</label>
                    <select name="tax_rate_id" id="taxRateSelect" class="form-control" style="background:#fff;" onchange="calcTotals()">
                        <option value="" data-rate="0">No Tax</option>
                        <?php foreach($data['taxes'] as $tax): ?>
                            <option value="<?= $tax->id ?>" data-rate="<?= $tax->rate_percentage ?>"><?= htmlspecialchars($tax->tax_name) ?> (<?= $tax->rate_percentage ?>%)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="total-row">
                    <span>Calculated Tax:</span>
                    <span id="taxAmountDisplay">Rs: 0.00</span>
                </div>

                <div class="total-row grand-total">
                    <span>Grand Total:</span>
                    <span id="grandTotal">Rs: 0.00</span>
                </div>
            </div>
        </div>

        <div style="clear:both; margin-top: 20px; text-align: right;">
            <button type="submit" class="btn" style="padding: 12px 24px; font-size: 16px;">Save & Post to Ledger</button>
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
        const tbody = document.getElementById('invoiceBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="desc[]" onchange="autoFillPrice(this)" required>${catalogOptions}</select></td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" onchange="calcTotals()" required></td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" onchange="calcTotals()" required></td>
            <td><input type="text" class="line-total" value="0.00" readonly style="font-weight:bold; color:var(--text-main);"></td>
            <td><button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size:10px;" onclick="removeRow(this)">X</button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calcTotals();
    }

    function calcTotals() {
        let subTotal = 0;
        const rows = document.querySelectorAll('#invoiceBody tr');
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const total = qty * price;
            row.querySelector('.line-total').value = total.toFixed(2);
            subTotal += total;
        });

        document.getElementById('subTotal').innerText = 'Rs: ' + subTotal.toFixed(2);

        // Calculate Tax
        const taxSelect = document.getElementById('taxRateSelect');
        const selectedTaxOption = taxSelect.options[taxSelect.selectedIndex];
        const taxRate = parseFloat(selectedTaxOption.getAttribute('data-rate')) || 0;
        
        const taxAmount = (subTotal * taxRate) / 100;
        document.getElementById('taxAmountDisplay').innerText = 'Rs: ' + taxAmount.toFixed(2);

        // Grand Total
        const grandTotal = subTotal + taxAmount;
        document.getElementById('grandTotal').innerText = 'Rs: ' + grandTotal.toFixed(2);
    }
</script>