<?php
$db = new Database();

// Fetch Catalog
$catalog_items = $data['catalog_items'] ?? [];
if (empty($catalog_items)) {
    $db->query("SELECT * FROM items ORDER BY name ASC");
    $catalog_items = $db->resultSet();
}

foreach($catalog_items as $item) {
    if (!isset($item->variations)) {
        $db->query("SELECT ivo.*, v.name as variation_name, vv.value_name 
                    FROM item_variation_options ivo
                    JOIN variations v ON ivo.variation_id = v.id
                    JOIN variation_values vv ON ivo.variation_value_id = vv.id
                    WHERE ivo.item_id = :id");
        $db->bind(':id', $item->id);
        $item->variations = $db->resultSet();
    }
}

// Fetch Customers WITH Territory Name properly joined
$db->query("SELECT c.*, m.name as mca_name FROM customers c LEFT JOIN mca_areas m ON c.mca_id = m.id ORDER BY c.name ASC");
$customers = $db->resultSet();

$db->query("SELECT * FROM employees WHERE status = 'Active' AND job_title = 'Rep' ORDER BY first_name ASC");
$reps = $db->resultSet();

$assets = $data['assets'] ?? [];
if (empty($assets)) {
    $db->query("SELECT * FROM chart_of_accounts WHERE account_type = 'Asset' AND account_name LIKE '%Receivable%' LIMIT 1");
    $assets = $db->resultSet();
    if(empty($assets)) {
        $db->query("SELECT * FROM chart_of_accounts WHERE account_type = 'Asset' LIMIT 1");
        $assets = $db->resultSet();
    }
}

$revenues = $data['revenues'] ?? [];
if (empty($revenues)) {
    $db->query("SELECT * FROM chart_of_accounts WHERE account_type = 'Revenue' LIMIT 1");
    $revenues = $db->resultSet();
}
?>
<style>
    /* Full-Screen App Layout CSS */
    html, body { overflow: hidden; height: 100%; margin: 0; }
    
    .qb-wrapper {
        background-color: #f2f5f8;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #000;
        height: calc(100vh - 30px); /* Account for Mac top bar */
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 10px;
        box-sizing: border-box;
    }
    
    .qb-container {
        background: #fff;
        width: 100%;
        max-width: 1300px;
        margin: 0 auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 1px solid #b0c4de;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 10px 15px;
        box-sizing: border-box;
        overflow: hidden;
    }

    #invoiceForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .qb-title { font-size: 26px; color: #7994b5; font-weight: bold; margin: 0 0 10px 0; letter-spacing: -1px; }

    .qb-input { border: 1px solid #999; padding: 4px; font-size: 12px; font-family: Arial, sans-serif; box-sizing: border-box; }
    .qb-input:focus { border-color: #0066cc; outline: none; background: #f0f8ff;}

    .qb-box { border: 1px solid #999; background: #fff; }
    .qb-box-header { background: #7a7a7a; color: #fff; font-weight: bold; padding: 3px 6px; font-size: 11px; }

    .qb-grid-top { display: flex; justify-content: space-between; margin-bottom: 10px; flex-shrink: 0;}
    .qb-grid-mid { display: flex; border: 1px solid #999; margin-bottom: 10px; background:#fff; flex-shrink: 0;}
    .qb-mid-col { flex: 1; border-right: 1px solid #999; display: flex; flex-direction: column; }
    .qb-mid-col:last-child { border-right: none; }
    .qb-mid-header { background: #7a7a7a; color: #fff; padding: 3px; text-align: center; font-size: 11px; font-weight: bold;}
    .qb-mid-body { padding: 4px; }
    .qb-mid-body input, .qb-mid-body select { width: 100%; border: none; text-align: center; background: transparent; font-size: 12px;}

    .search-wrapper { position: relative; margin-bottom: 10px; flex-shrink: 0;}
    .item-search-bar { width: 100%; padding: 8px; border: 2px solid #7994b5; font-size: 13px; font-weight: bold; border-radius: 4px; box-sizing: border-box; background: #f0f8ff;}
    
    .search-results { position: absolute; top: 100%; left: 0; width: 100%; background: #fff; border: 1px solid #999; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .search-results li { padding: 8px 10px; cursor: pointer; list-style: none; border-bottom: 1px solid #eee; font-size: 13px; display: flex; justify-content: space-between;}
    .search-results li:hover { background: #0066cc; color: #fff; }

    /* The Scrollable Table Container */
    .table-scroll-container {
        flex: 1;
        overflow-y: auto;
        border: 1px solid #999;
        margin-bottom: 10px;
        background: #fff;
    }

    .qb-table { width: 100%; border-collapse: collapse; }
    .qb-table thead th { position: sticky; top: 0; background: #7a7a7a; color: #fff; padding: 4px; text-align: left; font-size: 11px; font-weight: bold; border-right: 1px solid #999; border-bottom: 1px solid #555; z-index: 10;}
    .qb-table td { padding: 2px; border-bottom: 1px solid #ccc; border-right: 1px solid #ccc; vertical-align: top;}
    .qb-table input { width: 100%; border: none; background: transparent; padding: 4px; font-size: 12px; font-family: Arial; box-sizing: border-box;}
    .qb-table input:focus { background: #f0f8ff; outline: 1px solid #0066cc; }
    .qb-table .num { text-align: right; }
    
    .discount-cell { display: flex; border: 1px solid transparent; border-radius: 2px; background: #fff; overflow: hidden; }
    .discount-cell:focus-within { border-color: #0066cc; outline: 1px solid #0066cc;}
    .discount-cell input { border: none; width: 60%; }
    .discount-cell select { border: none; border-left: 1px solid #ccc; width: 40%; padding: 2px; background: #f9f9f9; font-size: 12px;}

    .qb-footer { display: flex; justify-content: space-between; align-items: flex-end; flex-shrink: 0;}
    .qb-totals { text-align: right; font-size: 13px;}
    .qb-totals-row { display: flex; justify-content: flex-end; margin-bottom: 5px; gap: 15px;}
    .qb-totals-row strong { width: 120px; text-align: left; }
    .qb-totals-row span { width: 100px; text-align: right; font-family: Tahoma, sans-serif;}

    .qb-action-bar { background: #e8ecf0; padding: 10px; border-top: 1px solid #b0c4de; display: flex; justify-content: space-between; align-items: center; margin-top: 10px; flex-shrink: 0;}
    .qb-btn { background: #f0f0f0; border: 1px solid #999; padding: 5px 15px; cursor: pointer; font-size: 12px; border-radius: 2px; font-weight: bold;}
    .qb-btn:hover { background: #e0e0e0; }
    .qb-btn-primary { background: #e0eaf5; border-color: #7994b5; }
    
    .wa-btn { background: #25D366; color: #fff; border-color: #1da851; }
    .wa-btn:hover { background: #20ba56; }
    .acc-settings { display: none; } 
</style>

<div class="qb-wrapper">
    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border:1px solid #ef9a9a; margin-bottom:5px; font-weight:bold; flex-shrink:0;"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; margin-bottom:5px; font-weight:bold; flex-shrink:0;">Invoice successfully saved and posted to ledger!</div>
    <?php endif; ?>

    <div class="qb-container">
        <form action="<?= APP_URL ?>/sales/create" method="POST" id="invoiceForm">
            
            <div class="qb-grid-top">
                <div style="width: 300px;">
                    <div class="qb-title">Invoice</div>
                    <div class="qb-box">
                        <div class="qb-box-header">Bill To</div>
                        <select name="customer_id" id="customerSelect" class="qb-input" style="width: 100%; border:none; border-bottom:1px solid #ccc; font-weight:bold;" required onchange="updateBillTo()">
                            <option value="">Select Customer...</option>
                            <?php foreach($customers as $cust): ?>
                                <option value="<?= $cust->id ?>" 
                                        data-address="<?= htmlspecialchars($cust->address) ?>"
                                        data-phone="<?= htmlspecialchars($cust->phone) ?>"
                                        data-mca="<?= htmlspecialchars($cust->mca_name ?? $cust->territory ?? '') ?>">
                                    <?= htmlspecialchars($cust->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <textarea id="billToAddress" class="qb-input" style="width: 100%; height: 60px; border:none; resize:none;" readonly placeholder="Customer address will appear here..."></textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <div class="qb-box" style="width: 120px;">
                        <div class="qb-box-header">Date</div>
                        <input type="date" name="invoice_date" class="qb-input" style="width: 100%; border:none; text-align:center;" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="qb-box" style="width: 120px;">
                        <div class="qb-box-header">Invoice #</div>
                        <input type="text" name="invoice_number" class="qb-input" style="width: 100%; border:none; text-align:center;" value="<?= $data['invoice_number'] ?? 'INV-'.time() ?>" required>
                    </div>
                </div>
            </div>

            <div class="qb-grid-mid">
                <div class="qb-mid-col">
                    <div class="qb-mid-header">P.O. No.</div>
                    <div class="qb-mid-body"><input type="text" name="po_number" placeholder="Optional"></div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Due Date</div>
                    <div class="qb-mid-body"><input type="date" name="due_date" id="dueDate" value="<?= date('Y-m-d') ?>" required></div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Rep</div>
                    <div class="qb-mid-body">
                        <select name="rep_name">
                            <option value="">Select Rep...</option>
                            <?php foreach($reps as $rep): ?>
                                <option value="<?= htmlspecialchars($rep->first_name . ' ' . $rep->last_name) ?>">
                                    <?= htmlspecialchars($rep->first_name . ' ' . $rep->last_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">MCA</div>
                    <div class="qb-mid-body"><input type="text" name="mca" id="displayMca" placeholder="Editable Route Info"></div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Rep TP#</div>
                    <div class="qb-mid-body"><input type="text" name="rep_tp" id="displayPhone" placeholder="Phone #"></div>
                </div>
            </div>

            <div class="search-wrapper">
                <input type="text" id="itemSearch" class="item-search-bar" placeholder="🔍 Search Catalog by Item Code or Name to add to invoice..." onkeyup="filterSearch(event)" autocomplete="off">
                <ul id="searchResults" class="search-results"></ul>
            </div>

            <div class="table-scroll-container">
                <table class="qb-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Item Code</th>
                            <th style="width: 8%;">Qty</th>
                            <th style="width: 35%;">Description</th>
                            <th style="width: 12%; text-align:right;">Rate (Rs:)</th>
                            <th style="width: 15%; text-align:right;">Discount</th>
                            <th style="width: 12%; text-align:right;">Amount (Rs:)</th>
                            <th style="width: 30px; background:#c62828;"></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceBody">
                        <!-- Javascript will prepend rows here -->
                    </tbody>
                </table>
            </div>

            <div class="qb-footer">
                <div style="width: 350px;">
                    <div style="margin-bottom: 5px;">
                        <label style="font-weight:bold; display:block; margin-bottom:2px; font-size:11px;">Customer Message</label>
                        <select class="qb-input" style="width:100%; margin-bottom:5px;">
                            <option value="">Thank you for your business.</option>
                            <option value="">Please remit payment at your earliest convenience.</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:bold; display:block; margin-bottom:2px; font-size:11px;">Memo</label>
                        <input type="text" name="notes" class="qb-input" style="width:100%;">
                    </div>
                </div>

                <div class="qb-totals">
                    <div class="qb-totals-row">
                        <strong>Subtotal LKR</strong>
                        <span id="subTotal">0.00</span>
                    </div>
                    <div class="qb-totals-row" style="align-items: center;">
                        <strong>Bill Discount</strong>
                        <div class="discount-cell" style="width: 100px; border-color:#ccc;">
                            <input type="number" name="global_discount_val" id="globalDiscVal" value="0" class="qb-input num" style="width:60px; border:none;" oninput="calcTotals()">
                            <select name="global_discount_type" id="globalDiscType" class="qb-input" style="width:40px; padding:0; border:none; border-left:1px solid #ccc; background:#f9f9f9;" onchange="calcTotals()">
                                <option value="Rs">Rs</option>
                                <option value="%">%</option>
                            </select>
                        </div>
                    </div>
                    <div class="qb-totals-row" style="font-size: 14px; margin-top: 5px;">
                        <strong>Total LKR</strong>
                        <span id="grandTotal" style="font-weight: bold;">0.00</span>
                    </div>
                    <div class="qb-totals-row" style="font-size: 14px; margin-top: 5px;">
                        <strong style="color:#0066cc;">Balance Due LKR</strong>
                        <span id="balanceDue" style="font-weight: bold; color:#0066cc;">0.00</span>
                    </div>
                </div>
            </div>

            <div class="acc-settings">
                <select name="ar_account" required>
                    <?php foreach($assets ?? [] as $acc): ?>
                        <option value="<?= $acc->id ?>" <?= strpos(strtolower($acc->account_name), 'receivable') !== false ? 'selected' : '' ?>><?= htmlspecialchars($acc->account_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="revenue_account" required>
                    <?php foreach($revenues ?? [] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->account_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="qb-action-bar">
                <div style="display:flex; gap: 10px; align-items:center;">
                    <span style="color:#2e7d32; font-weight:bold;">● System Ready</span>
                </div>
                <div style="display:flex; gap: 10px;">
                    <button type="submit" name="save_action" value="close" class="qb-btn qb-btn-primary">Save & Close</button>
                    <button type="submit" name="save_action" value="new" class="qb-btn">Save & New</button>
                    <button type="submit" name="save_action" value="print" class="qb-btn">Save & Print 🖨️</button>
                    <button type="submit" name="save_action" value="whatsapp" class="qb-btn wa-btn">Save & WhatsApp 💬</button>
                    <button type="button" class="qb-btn" onclick="window.location.reload()">Revert</button>
                </div>
            </div>

        </form>
    </div>
</div>

<?php if(isset($_GET['wa_id'])): ?>
<script>
    // WhatsApp Auto-Sender Logic updated to point to the correct public invoice link!
    document.addEventListener("DOMContentLoaded", function() {
        const waPhone = "<?= htmlspecialchars($_GET['wa_phone']) ?>";
        const waName = "<?= htmlspecialchars($_GET['wa_name']) ?>";
        const waId = "<?= htmlspecialchars($_GET['wa_id']) ?>";
        const publicInvoiceLink = "<?= APP_URL ?>/sales/show/" + waId;
        
        if (waPhone && waPhone.trim() !== '') {
            let cleanPhone = waPhone.replace(/[^\d+]/g, '');
            if(cleanPhone.startsWith('0')) {
                cleanPhone = '94' + cleanPhone.substring(1); 
            }
            
            const waMessage = `Dear ${waName},\n\nPlease find your invoice at the link below:\n${publicInvoiceLink}\n\nKindly review it and let us know if you have any questions.\n\nThank you.`;
            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(waMessage)}`;
            
            window.open(waUrl, '_blank');
        } else {
            alert("This customer does not have a valid WhatsApp or Phone number saved in their profile.");
        }
    });
</script>
<?php endif; ?>

<?php if(isset($_GET['print_id'])): ?>
<script>
    // Print Auto-Opener Logic
    document.addEventListener("DOMContentLoaded", function() {
        const printId = "<?= htmlspecialchars($_GET['print_id']) ?>";
        const printUrl = "<?= APP_URL ?>/sales/show/" + printId;
        window.open(printUrl, '_blank');
    });
</script>
<?php endif; ?>

<script>
    const catalog = [
        <?php foreach($catalog_items as $item): ?>
            <?php if(!empty($item->variations)): ?>
                { id: "<?= $item->id ?>|MIX|1", code: "<?= htmlspecialchars($item->item_code ?? '') ?>", name: "<?= htmlspecialchars(addslashes($item->name)) ?> (MIX)", price: <?= $item->price ?? 0 ?> },
                <?php foreach($item->variations as $var): ?>
                { id: "<?= $item->id ?>|<?= $var->id ?>|0", code: "<?= htmlspecialchars(addslashes($var->sku ?: ($item->item_code ?? ''))) ?>", name: "<?= htmlspecialchars(addslashes($item->name)) ?> - <?= htmlspecialchars(addslashes($var->variation_name)) ?>: <?= htmlspecialchars(addslashes($var->value_name)) ?>", price: <?= isset($var->price) && $var->price > 0 ? $var->price : ($item->price ?? 0) ?> },
                <?php endforeach; ?>
            <?php else: ?>
                { id: "<?= $item->id ?>|0|0", code: "<?= htmlspecialchars($item->item_code ?? '') ?>", name: "<?= htmlspecialchars(addslashes($item->name)) ?>", price: <?= $item->price ?? 0 ?> },
            <?php endif; ?>
        <?php endforeach; ?>
    ];

    function updateBillTo() {
        const select = document.getElementById('customerSelect');
        const selected = select.options[select.selectedIndex];
        
        document.getElementById('billToAddress').value = selected.getAttribute('data-address') || '';
        
        // Fix: Successfully pulls MCA from the database into the editable field
        const mcaInput = document.getElementById('displayMca');
        if(mcaInput) mcaInput.value = selected.getAttribute('data-mca') || '';
        
        const phoneInput = document.getElementById('displayPhone');
        if(phoneInput) phoneInput.value = selected.getAttribute('data-phone') || '';
    }

    function filterSearch(e) {
        const val = e.target.value.toLowerCase().trim();
        const resList = document.getElementById('searchResults');
        resList.innerHTML = '';
        if(!val) { resList.style.display = 'none'; return; }

        const filtered = catalog.filter(i => i.name.toLowerCase().includes(val) || i.code.toLowerCase().includes(val)).slice(0, 15);
        if(filtered.length === 0) { resList.style.display = 'none'; return; }

        filtered.forEach(item => {
            const li = document.createElement('li');
            li.innerHTML = `
                <div><strong>${item.name}</strong><br><span style="font-size: 11px; color: #888;">SKU: ${item.code || 'N/A'}</span></div>
                <div style="color: #0066cc; font-family: monospace; font-weight: bold; font-size: 14px;">Rs: ${item.price.toFixed(2)}</div>
            `;
            li.onclick = () => { addItemRow(item); e.target.value = ''; resList.style.display = 'none'; document.getElementById('itemSearch').focus(); };
            resList.appendChild(li);
        });
        resList.style.display = 'block';
    }

    document.addEventListener('click', function(e) {
        if(e.target.id !== 'itemSearch') { document.getElementById('searchResults').style.display = 'none'; }
    });

    function addItemRow(item) {
        const tbody = document.getElementById('invoiceBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="text" value="${item.code || 'ITEM'}" readonly style="color:#666; font-size:12px;">
                <input type="hidden" name="item_selection[]" value="${item.id}">
            </td>
            <td><input type="number" class="num" name="qty[]" value="1" min="0.01" step="0.01" oninput="calcTotals()" required></td>
            <td><input type="text" name="desc[]" value="${item.name}" required></td>
            <td><input type="number" class="num" name="price[]" value="${parseFloat(item.price).toFixed(2)}" step="0.01" min="0" oninput="calcTotals()" required></td>
            <td>
                <div class="discount-cell">
                    <input type="number" class="num" name="item_discount_val[]" value="0.00" step="0.01" oninput="calcTotals()">
                    <select name="item_discount_type[]" onchange="calcTotals()">
                        <option value="Rs">Rs</option>
                        <option value="%">%</option>
                    </select>
                </div>
            </td>
            <td><input type="text" class="num line-total" value="${parseFloat(item.price).toFixed(2)}" readonly style="font-weight:bold; background: transparent; border: none;"></td>
            <td style="text-align:center;"><button type="button" tabindex="-1" style="background:transparent; color:#c62828; border:none; cursor:pointer; font-weight:bold; font-size: 16px; padding: 4px;" onclick="this.closest('tr').remove(); calcTotals();">&times;</button></td>
        `;
        tbody.insertAdjacentElement('afterbegin', tr);
        calcTotals();
        
        // Scroll container to top to see the new item
        document.querySelector('.table-scroll-container').scrollTop = 0;
    }

    function calcTotals() {
        let subTotal = 0;
        document.querySelectorAll('#invoiceBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const discVal = parseFloat(row.querySelector('input[name="item_discount_val[]"]').value) || 0;
            const discType = row.querySelector('select[name="item_discount_type[]"]').value;

            let rowGross = qty * price;
            let rowDisc = (discType === '%') ? (rowGross * discVal / 100) : discVal;
            let rowNet = rowGross - rowDisc;
            if (rowNet < 0) rowNet = 0; 
            
            row.querySelector('.line-total').value = rowNet.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            subTotal += rowNet;
        });

        const globalDiscVal = parseFloat(document.getElementById('globalDiscVal').value) || 0;
        const globalDiscType = document.getElementById('globalDiscType').value;
        let globalDisc = (globalDiscType === '%') ? (subTotal * globalDiscVal / 100) : globalDiscVal;
        
        let grandTotal = subTotal - globalDisc;
        if (grandTotal < 0) grandTotal = 0;

        document.getElementById('subTotal').innerText = subTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('grandTotal').innerText = grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('balanceDue').innerText = grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
</script>