<?php
// Extracted Failsafe: Calculates the TRUE outstanding balance for each customer 
$successInvoiceId = $_GET['success_invoice_id'] ?? null;
$successCustomerId = $_GET['customer_id'] ?? null;
$successCustomer = null;

$db = new Database();
foreach ($customers as $c) {
    if ($successCustomerId && $c->id == $successCustomerId) {
        $successCustomer = $c;
    }

    if (!isset($c->outstanding_balance)) {
        $db->query("
            SELECT 
               (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) FROM invoices WHERE customer_id = :id1 AND status != 'Voided') 
               - 
               (SELECT COALESCE(SUM(amount), 0) FROM customer_payments WHERE customer_id = :id2) 
               - 
               (SELECT COALESCE(SUM(total_amount), 0) FROM credit_notes WHERE customer_id = :id3) 
               AS outstanding_balance
        ");
        $db->bind(':id1', $c->id);
        $db->bind(':id2', $c->id);
        $db->bind(':id3', $c->id);
        $balRow = $db->single();
        $c->outstanding_balance = $balRow->outstanding_balance ?? 0;
    }
}

if ($successInvoiceId && !$successCustomer) {
    $db->query("SELECT * FROM customers WHERE id = :id");
    $db->bind(':id', $successCustomerId);
    $successCustomer = $db->single();
}
?>
<style>
    /* POS Checkout Terminal Overlay */
    .co-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: var(--app-bg); z-index: 3000; display: none; flex-direction: column; overflow-y: auto; }
    .co-header { background: #cfd8dc; padding: 15px 20px; display: flex; align-items: center; gap: 15px; position: sticky; top:0; z-index: 3010; }
    .co-header h2 { margin: 0; font-size: 18px; color: #333; }
    .co-header span { font-size: 12px; color: #666; }
    
    .co-title-bar { padding: 20px; background: var(--surface); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
    .co-card { background: var(--surface); border-radius: 12px; border: 1px solid var(--border); margin: 15px 20px; padding: 20px; }
    
    .co-item-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 15px 0; border-bottom: 1px dashed var(--border); cursor: pointer; transition: 0.2s; }
    .co-item-row:hover { background: rgba(0,102,204,0.02); }
    .co-item-row:last-child { border-bottom: none; padding-bottom: 0; }
    .co-del-btn { color: #c62828; cursor: pointer; border: none; background: transparent; font-size: 18px; padding: 5px; margin-top: 5px; }

    .pay-input-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;}
    .pay-input-row label { font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px; color: var(--text-dark);}
    .pay-input-box { width: 60%; border-radius: 8px; padding: 12px; text-align: right; font-size: 16px; font-weight: bold; font-family: monospace; outline: none; border: 1px solid var(--border);}

    .pay-cash { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
    .pay-bank { background: #e3f2fd; color: #1565c0; border-color: #bbdefb; }
    .pay-cheque { background: #fff8e1; color: #f57c00; border-color: #ffecb3; }

    .co-totals-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: var(--text-muted);}
    .co-payable-row { display: flex; justify-content: space-between; padding: 15px 0; font-size: 20px; font-weight: bold; color: #0066cc; border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; margin-top: 10px;}
    .co-due-row { display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; color: var(--text-dark); margin-top: 20px; border-top: 2px solid var(--border); padding-top: 15px;}
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
</style>

<!-- POS Checkout Terminal Overlay -->
<div class="co-overlay" id="checkoutOverlay">
    <div class="co-header">
        <button onclick="closeCheckout()" style="background:transparent; border:none; font-size:24px; cursor:pointer; color:#333;">←</button>
        <div>
            <h2>POS Terminal</h2>
            <span>Generate Invoice & Arrears</span>
        </div>
    </div>

    <!-- Customer Info Display & Payment Term -->
    <div style="padding: 0 20px;">
        <div class="co-card" style="margin: 15px 0 0 0; border: 2px solid var(--primary); display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="openCustomerSheet()">
            <div>
                <label style="font-size:11px; font-weight:bold; color:var(--primary); text-transform:uppercase; display:block; margin-bottom:5px;">Billing Customer (Tap to Change)</label>
                <div id="checkoutCustomerName" style="font-size: 16px; font-weight: bold; color: var(--text-dark);">Not Selected</div>
            </div>
            <div style="text-align: right;">
                <label style="font-size:11px; font-weight:bold; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:5px;">Outstanding</label>
                <div id="checkoutCustomerOws" style="font-size: 14px; font-weight: bold; color: var(--text-dark);">Rs 0.00</div>
            </div>
        </div>

        <div class="co-card" style="margin: 15px 0 0 0;">
            <label style="font-size:11px; font-weight:bold; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:5px;">Invoice Payment Term *</label>
            <select id="coPaymentTerm" class="form-control" style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold; background: var(--surface); color: var(--text-dark); border: 1px solid var(--border); border-radius: 8px; outline: none;">
                <option value="">Select Term...</option>
                <?php foreach($paymentTerms as $pt): ?>
                    <option value="<?= $pt->id ?>"><?= htmlspecialchars($pt->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="co-title-bar" style="margin-top: 15px;">
        <h3 style="margin:0; display:flex; align-items:center; gap:10px; color:var(--text-dark);">🛒 Current Bill Summary</h3>
        <button onclick="closeCheckout()" style="background:transparent; border:none; font-size:20px; cursor:pointer; color:var(--text-muted);">✕</button>
    </div>

    <div class="co-card">
        <div style="font-size:11px; font-weight:bold; color:var(--text-muted); margin-bottom:15px; text-transform:uppercase;">Billed Items (Tap to Edit)</div>
        <div id="coItemsList"></div>
    </div>

    <div style="padding:0 20px;">
        <div class="grid-2">
            <div class="co-card" style="margin:0; padding:15px;">
                <div style="font-size:11px; font-weight:bold; color:var(--text-muted); margin-bottom:10px;">BILL DISCOUNT</div>
                <div style="display:flex; gap:5px;">
                    <select id="coGlobalDiscType" onchange="calcCheckout()" style="padding:8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text-dark);"><option value="%">%</option><option value="Rs">Rs</option></select>
                    <input type="number" id="coGlobalDiscVal" value="0" oninput="calcCheckout()" style="width:100%; padding:8px; border:1px solid var(--border); border-radius:6px; text-align:right; font-weight:bold; color:#c62828; background:var(--surface);">
                </div>
            </div>
            <div class="co-card" style="margin:0; padding:15px;">
                <div style="font-size:11px; font-weight:bold; color:var(--text-muted); margin-bottom:10px;">TAX / VAT</div>
                <div style="display:flex; gap:5px;">
                    <select id="coTaxType" onchange="calcCheckout()" style="padding:8px; border:1px solid var(--border); border-radius:6px; background:var(--surface); color:var(--text-dark);"><option value="%">%</option><option value="Rs">Rs</option></select>
                    <input type="number" id="coTaxVal" value="0" oninput="calcCheckout()" style="width:100%; padding:8px; border:1px solid var(--border); border-radius:6px; text-align:right; font-weight:bold; color:#0066cc; background:var(--surface);">
                </div>
            </div>
        </div>
    </div>

    <div class="co-card">
        <div class="co-totals-row">
            <span>Subtotal</span>
            <span id="coSubtotal" style="font-family: monospace; font-weight:bold; color:var(--text-dark);">Rs 0.00</span>
        </div>
        <div class="co-payable-row">
            <span>CURRENT BILL PAYABLE</span>
            <span>Rs <span id="coPayable" style="font-family: monospace;">0.00</span></span>
        </div>
    </div>

    <!-- Credit Collection (Arrears) -->
    <div class="co-card" id="creditCollectionBox" style="display: none; border: 2px solid #0066cc; background: rgba(0,102,204,0.02);">
        <div style="font-size:14px; font-weight:bold; color:#0066cc; margin-bottom:5px; text-transform:uppercase;">Collect Outstanding Arrears</div>
        <p style="font-size:12px; color:var(--text-muted); margin-top:0; margin-bottom:15px;">Collections here will drop the customer's Accounts Receivable balance instantly.</p>
        
        <div class="pay-input-row">
            <label>💵 Cash</label>
            <input type="number" id="payCash" class="pay-input-box pay-cash" value="0.00" onfocus="if(this.value=='0.00')this.value='';" onblur="if(this.value=='')this.value='0.00';" oninput="calcCheckout()">
        </div>
        <div class="pay-input-row">
            <label>🏦 Bank Transfer</label>
            <input type="number" id="payBank" class="pay-input-box pay-bank" value="0.00" onfocus="if(this.value=='0.00')this.value='';" onblur="if(this.value=='')this.value='0.00';" oninput="calcCheckout()">
        </div>
        <div class="pay-input-row" style="margin-bottom:0;">
            <label>💳 Cheque (PDC)</label>
            <input type="number" id="payCheque" class="pay-input-box pay-cheque" value="0.00" onfocus="if(this.value=='0.00')this.value='';" onblur="if(this.value=='')this.value='0.00';" oninput="calcCheckout()">
        </div>
        
        <div id="chequeDetailsBox" style="display:none; margin-top:15px; padding:15px; background:#fff8e1; border:1px dashed #ffb300; border-radius:6px;">
            <label style="font-size:11px; font-weight:bold; color:#f57c00; text-transform:uppercase; margin-bottom:5px; display:block;">PDC Cheque Details</label>
            <input type="text" id="co_cq_bank" class="form-control" placeholder="Bank Name" style="margin-bottom:8px; border-color:#ffecb3;">
            <input type="text" id="co_cq_num" class="form-control" placeholder="Cheque Number" style="margin-bottom:8px; border-color:#ffecb3;">
            <input type="date" id="co_cq_date" class="form-control" style="border-color:#ffecb3;" value="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <!-- Final Calculation Output -->
    <div class="co-card" style="margin-top: 0;">
        <div class="co-due-row" style="margin-top: 0; padding-top: 0; border-top: none;">
            <span>NEW PROJECTED OUTSTANDING</span>
            <span id="coProjectedOutstanding" style="font-family: monospace;">0.00</span>
        </div>
        <p style="font-size: 11px; color: var(--text-muted); margin: 5px 0 0 0; text-align: right;">Includes previous arrears + this bill - collections.</p>
    </div>

    <div style="padding: 0 20px 40px 20px;">
        <form id="checkoutForm" action="<?= APP_URL ?>/rep/billing/process_checkout" method="POST">
            <input type="hidden" name="checkout_payload" id="checkoutPayload">
            <button type="button" id="confirmSubmitBtn" class="btn-primary" onclick="submitCheckout()">Confirm Checkout & Invoice</button>
        </form>
    </div>
</div>

<!-- SUCCESS MODAL (Triggered when the backend redirects with a success_invoice_id) -->
<?php if ($successInvoiceId && $successCustomer): ?>
<div class="sheet-overlay" id="successModalOverlay" style="display:flex; z-index: 5000; align-items:center;">
    <div class="modal-content" style="background:var(--surface); width:90%; max-width:400px; padding:30px; border-radius:16px; text-align:center; position:relative;">
        <div style="font-size: 50px; margin-bottom: 15px;">✅</div>
        <h2 style="margin-top:0; color:#2e7d32;">Order Saved Successfully!</h2>
        <p style="color:var(--text-muted); font-size:14px; margin-bottom:25px;">Invoice #<?= htmlspecialchars($_GET['invoice_num'] ?? $successInvoiceId) ?> has been generated and GPS location recorded.</p>
        
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php if(!empty($successCustomer->email)): ?>
                <a href="<?= APP_URL ?>/sales/email_invoice?invoice_id=<?= $successInvoiceId ?>" class="btn-primary" style="background:#0066cc; text-decoration:none; padding:12px;">📧 Email Receipt</a>
            <?php else: ?>
                <button class="btn-primary" style="background:var(--border); color:var(--text-muted); padding:12px; box-shadow:none; cursor:not-allowed;" disabled>📧 No Email on File</button>
            <?php endif; ?>

            <?php if(!empty($successCustomer->whatsapp) || !empty($successCustomer->phone)): ?>
                <?php
                    $phone = !empty($successCustomer->whatsapp) ? $successCustomer->whatsapp : $successCustomer->phone;
                    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
                    if(strpos($cleanPhone, '0') === 0) $cleanPhone = '94' . substr($cleanPhone, 1);
                    $invUrl = APP_URL . "/sales/show/" . $successInvoiceId;
                    $waMsg = "Dear {$successCustomer->name},\n\nThank you for your order. Your invoice is ready and can be viewed/downloaded here:\n{$invUrl}\n\nThank you!";
                    $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode($waMsg);
                ?>
                <a href="<?= $waLink ?>" target="_blank" class="btn-primary" style="background:#25D366; text-decoration:none; padding:12px;">💬 Send via WhatsApp</a>
            <?php else: ?>
                <button class="btn-primary" style="background:var(--border); color:var(--text-muted); padding:12px; box-shadow:none; cursor:not-allowed;" disabled>💬 No Phone/WA on File</button>
            <?php endif; ?>

            <button class="btn-primary" style="background:#f57c00; padding:12px;" onclick="document.getElementById('qrCodeBox').style.display='block'">📲 Show QR Code</button>
        </div>

        <div id="qrCodeBox" style="display:none; margin-top: 20px; padding: 15px; border: 2px dashed #f57c00; border-radius: 8px;">
            <p style="margin: 0 0 10px 0; font-size: 13px; font-weight:bold; color: #f57c00;">Scan to view Invoice</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(APP_URL . "/sales/show/" . $successInvoiceId) ?>" alt="QR Code" style="width:150px; height:150px; border-radius:8px;">
        </div>

        <a href="<?= APP_URL ?>/rep/billing" class="btn-primary" style="background:transparent; color:var(--text-muted); border:1px solid var(--border); box-shadow:none; margin-top:20px; padding:12px; text-decoration:none;">Close & Start New Bill</a>
    </div>
</div>
<?php endif; ?>

<script>
    function openCheckout() {
        document.getElementById('checkoutOverlay').style.display = 'flex';
        renderCheckoutItems();
        calcCheckout();
    }

    function closeCheckout() {
        document.getElementById('checkoutOverlay').style.display = 'none';
    }

    function renderCheckoutItems() {
        const list = document.getElementById('coItemsList');
        list.innerHTML = '';
        
        if (cart.length === 0) {
            list.innerHTML = '<div style="text-align:center; color:#888; padding:20px;">Cart is empty</div>';
            return;
        }

        cart.forEach(item => {
            let rowGross = item.qty * item.price;
            let rowDisc = (item.disc_type === '%') ? (rowGross * item.disc_val / 100) : parseFloat(item.disc_val);
            let rowNet = rowGross - rowDisc;
            if (rowNet < 0) rowNet = 0;

            list.innerHTML += `
                <div class="co-item-row">
                    <div style="flex:1;" onclick="editCartItem('${item.cartItemId}')">
                        <div style="font-weight:bold; color:var(--text-dark); font-size:14px; margin-bottom:4px;">${item.name}</div>
                        <div style="font-size:12px; color:var(--text-muted);">${item.qty}x @ Rs ${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:5px;">
                        <div style="font-weight:bold; font-size:14px; color:var(--text-dark);">Rs ${rowNet.toFixed(2)}</div>
                        <button type="button" class="co-del-btn" onclick="removeFromCart('${item.cartItemId}')">🗑️</button>
                    </div>
                </div>
            `;
        });
    }

    function removeFromCart(cartItemId) {
        cart = cart.filter(i => i.cartItemId !== cartItemId);
        if(typeof updateCartUI === 'function') updateCartUI(); 
        renderCheckoutItems(); 
        calcCheckout();
        if(cart.length === 0) closeCheckout();
    }

    function calcCheckout() {
        let subtotal = 0;
        cart.forEach(item => {
            let rowGross = item.qty * item.price;
            let rowDisc = (item.disc_type === '%') ? (rowGross * item.disc_val / 100) : parseFloat(item.disc_val);
            let rowNet = rowGross - rowDisc;
            if(rowNet < 0) rowNet = 0;
            subtotal += rowNet;
        });

        const globalDiscVal = parseFloat(document.getElementById('coGlobalDiscVal').value) || 0;
        const globalDiscType = document.getElementById('coGlobalDiscType').value;
        let billDisc = (globalDiscType === '%') ? (subtotal * globalDiscVal / 100) : globalDiscVal;
        
        let currentBill = subtotal - billDisc;
        if(currentBill < 0) currentBill = 0;

        const taxVal = parseFloat(document.getElementById('coTaxVal').value) || 0;
        const taxType = document.getElementById('coTaxType').value;
        let tax = (taxType === '%') ? (currentBill * taxVal / 100) : taxVal;

        let payable = currentBill + tax;

        let outstanding = typeof globalSelectedCustomerOutstanding !== 'undefined' ? globalSelectedCustomerOutstanding : 0;

        let cash = parseFloat(document.getElementById('payCash').value) || 0;
        let bank = parseFloat(document.getElementById('payBank').value) || 0;
        let cheque = parseFloat(document.getElementById('payCheque').value) || 0;
        
        document.getElementById('chequeDetailsBox').style.display = (cheque > 0) ? 'block' : 'none';
        
        const collectionBox = document.getElementById('creditCollectionBox');
        if (outstanding > 0) {
            collectionBox.style.display = 'block';
        } else {
            collectionBox.style.display = 'none';
        }

        let collections = cash + bank + cheque;
        let projectedOutstanding = outstanding + payable - collections;

        document.getElementById('coSubtotal').innerText = `Rs ${subtotal.toFixed(2)}`;
        document.getElementById('coPayable').innerText = payable.toFixed(2);
        
        const projEl = document.getElementById('coProjectedOutstanding');
        projEl.innerText = projectedOutstanding.toFixed(2);
        
        if (projectedOutstanding <= 0) {
            projEl.style.color = '#2e7d32'; 
        } else {
            projEl.style.color = '#c62828'; 
        }
    }

    function submitCheckout() {
        if (cart.length === 0) { alert('Cart is empty!'); return; }
        
        const customerId = typeof globalSelectedCustomerId !== 'undefined' ? globalSelectedCustomerId : null;
        if (!customerId) { alert('ERROR: You must select a Billing Customer from the top bar.'); return; }
        
        const termId = document.getElementById('coPaymentTerm').value;
        if (!termId) { alert('ERROR: You must select an Invoice Payment Term.'); return; }

        const submitBtn = document.getElementById('confirmSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.innerText = "📍 Capturing Location & Saving...";
        submitBtn.style.opacity = '0.8';

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    finalizeSubmit(position.coords.latitude, position.coords.longitude, customerId, termId);
                },
                function(error) {
                    alert("GPS Capture Failed: " + error.message + " - Saving order without location.");
                    finalizeSubmit(null, null, customerId, termId);
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        } else {
            finalizeSubmit(null, null, customerId, termId);
        }
    }
    
    function finalizeSubmit(lat, lng, customerId, termId) {
        const payload = {
            customer_id: customerId,
            payment_term_id: termId,
            cart: cart,
            discounts: {
                val: parseFloat(document.getElementById('coGlobalDiscVal').value) || 0,
                type: document.getElementById('coGlobalDiscType').value
            },
            tax: {
                val: parseFloat(document.getElementById('coTaxVal').value) || 0,
                type: document.getElementById('coTaxType').value
            },
            arrears_collections: {
                cash: parseFloat(document.getElementById('payCash').value) || 0,
                bank: parseFloat(document.getElementById('payBank').value) || 0,
                cheque: parseFloat(document.getElementById('payCheque').value) || 0
            },
            cheque_details: {
                bank: document.getElementById('co_cq_bank').value,
                number: document.getElementById('co_cq_num').value,
                date: document.getElementById('co_cq_date').value
            },
            location: {
                lat: lat,
                lng: lng
            }
        };

        document.getElementById('checkoutPayload').value = JSON.stringify(payload);
        document.getElementById('checkoutForm').submit();
    }
</script>