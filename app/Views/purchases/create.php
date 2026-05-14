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
    .po-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .po-table th, .po-table td { padding: 10px; border-bottom: 1px solid var(--mac-border); }
    .po-table th { background-color: rgba(0,0,0,0.02); text-align: left; }
    .po-table input, .po-table select { width: 100%; border: 1px solid transparent; background: transparent; color: var(--text-main); padding: 5px; box-sizing: border-box; }
    .po-table input:focus, .po-table select:focus { border: 1px solid #0066cc; outline: none; border-radius: 4px;}
    .po-table input[type="number"] { text-align: right; }
    .total-box { float: right; width: 300px; padding: 20px; background: rgba(0,0,0,0.02); border-radius: 8px; margin-top: 20px; text-align: right; font-size: 18px; font-weight: bold;}
</style>

<div class="card">
    <div class="header-actions">
        <h2>Create Purchase Order</h2>
        <a href="<?= APP_URL ?>/purchase" style="color: #666; text-decoration:none;">&larr; Back</a>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/purchase/create" method="POST" id="poForm">
        
        <div class="grid-4">
            <div class="form-group">
                <label>Vendor *</label>
                <select name="vendor_id" class="form-control" required>
                    <option value="">Select Vendor...</option>
                    <?php foreach($data['vendors'] as $ven): ?>
                        <option value="<?= $ven->id ?>"><?= htmlspecialchars($ven->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>PO Number</label>
                <input type="text" name="po_number" class="form-control" value="<?= $data['po_number'] ?>" required>
            </div>
            <div class="form-group">
                <label>Order Date</label>
                <input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Expected Delivery</label>
                <input type="date" name="expected_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
            </div>
        </div>

        <table class="po-table" id="linesTable">
            <thead>
                <tr>
                    <th style="width: 50%;">Product / Description</th>
                    <th style="width: 15%; text-align:right;">Qty</th>
                    <th style="width: 15%; text-align:right;">Unit Cost (Rs:)</th>
                    <th style="width: 15%; text-align:right;">Total (Rs:)</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="poBody">
                <tr>
                    <td>
                        <select name="desc[]" onchange="autoFillCost(this)" required>
                            <option value="">Select Product...</option>
                            <!-- Note: Pulling data-cost instead of price for Purchasing -->
                            <?php foreach($data['catalog_items'] as $item): ?>
                                <option value="<?= htmlspecialchars($item->name) ?>" data-cost="<?= $item->cost ?>">
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
        
        <button type="button" class="btn btn-outline" style="margin-top: 10px; font-size:12px;" onclick="addRow()">+ Add Item</button>

        <div class="total-box">
            Grand Total: Rs: <span id="grandTotal">0.00</span>
        </div>

        <div style="clear:both; margin-top: 20px; text-align: right;">
            <button type="submit" class="btn" style="padding: 12px 24px; font-size: 16px;">Generate Purchase Order</button>
        </div>
    </form>
</div>

<script>
    const catalogOptions = `
        <option value="">Select Product...</option>
        <?php foreach($data['catalog_items'] as $item): ?>
            <option value="<?= htmlspecialchars($item->name) ?>" data-cost="<?= $item->cost ?>">
                <?= htmlspecialchars($item->name) ?>
            </option>
        <?php endforeach; ?>
    `;

    function autoFillCost(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const cost = selectedOption.getAttribute('data-cost') || 0;
        const tr = selectElement.closest('tr');
        const priceInput = tr.querySelector('input[name="price[]"]');
        priceInput.value = parseFloat(cost).toFixed(2);
        calcTotals();
    }

    function addRow() {
        const tbody = document.getElementById('poBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="desc[]" onchange="autoFillCost(this)" required>${catalogOptions}</select></td>
            <td><input type="number" name="qty[]" step="1" min="1" value="1" onchange="calcTotals()" required></td>
            <td><input type="number" name="price[]" step="0.01" min="0" value="0.00" onchange="calcTotals()" required></td>
            <td><input type="text" class="line-total" value="0.00" readonly style="font-weight:bold; color:var(--text-main);"></td>
            <td><button type="button" class="btn btn-danger" style="padding: 4px 8px; font-size:10px;" onclick="removeRow(this)">X</button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) { btn.closest('tr').remove(); calcTotals(); }

    function calcTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#poBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const total = qty * price;
            row.querySelector('.line-total').value = total.toFixed(2);
            grandTotal += total;
        });
        document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
    }
</script>