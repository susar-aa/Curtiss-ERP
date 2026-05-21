<?php
$customer = $data['customer'];
$todayTotal = floatval($data['today_total']);
$arrears = floatval($data['arrears']);
$grandTotal = $todayTotal + $arrears;
?>

<div style="margin-bottom: 15px;">
    <a href="<?= APP_URL ?>/driver/billing/shop/<?= $customer->id ?>" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
        ← Back to Checklist
    </a>
</div>

<form action="<?= APP_URL ?>/driver/billing/process_checkout" method="POST" id="checkout-form">
    <input type="hidden" name="customer_id" value="<?= $customer->id ?>">

    <!-- 1. DEBT SUMMARY CARD -->
    <div class="card" style="background: linear-gradient(135deg, var(--surface) 0%, var(--primary-light) 100%); border: 1px solid rgba(0, 102, 204, 0.15);">
        <h3 style="margin: 0 0 15px; font-size: 16px; font-weight: 800; text-transform: uppercase; color: var(--primary);">Shop Arrears Summary</h3>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
            <span style="color: var(--text-muted);">Previous Outstanding:</span>
            <strong>Rs. <?= number_format($arrears, 2) ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
            <span style="color: var(--text-muted);">Today's Delivery Invoices:</span>
            <strong>Rs. <?= number_format($todayTotal, 2) ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px solid var(--border); font-size: 16px;">
            <strong style="color: var(--text-dark);">Total Outstanding:</strong>
            <strong style="color: var(--text-dark);" id="total-outstanding" data-val="<?= $grandTotal ?>">Rs. <?= number_format($grandTotal, 2) ?></strong>
        </div>
    </div>

    <!-- 2. COLLECTION TERMINAL -->
    <div class="card">
        <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 800; text-transform: uppercase;">Receive Payment</h3>

        <!-- CASH -->
        <label class="form-label">💸 Cash Received (Rs.)</label>
        <input type="number" step="0.01" name="cash" id="cash-input" class="form-input payment-input" placeholder="0.00" value="0.00">

        <!-- BANK TRANSFER -->
        <label class="form-label">🏦 Bank Transfer (Rs.)</label>
        <input type="number" step="0.01" name="bank" id="bank-input" class="form-input payment-input" placeholder="0.00" value="0.00">

        <!-- PDC CHEQUES CONTAINER -->
        <div style="margin-top: 20px; border-top: 1px dashed var(--border); padding-top: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label class="form-label" style="margin: 0; font-weight: bold; font-size: 14px;">💳 Cheques (PDC)</label>
                <button type="button" class="btn-primary" onclick="addChequeRow()" style="padding: 6px 12px; font-size: 11px; width: auto; background: var(--primary); margin: 0; border: none; border-radius: 6px; color: white; cursor: pointer; font-weight: bold;">➕ Add Cheque</button>
            </div>
            <div id="cheques-container" style="display: flex; flex-direction: column; gap: 12px;"></div>
        </div>
    </div>

    <!-- 3. PROJECTED BALANCE DETAILS -->
    <div class="card" style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 14px; font-weight: 700; color: var(--text-muted);">Total Collected Amount:</span>
            <strong id="live-total-collected" style="font-size: 16px; color: var(--success);">Rs. 0.00</strong>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 14px; font-weight: 700; color: var(--text-muted);">Projected Balance:</span>
            <strong id="live-projected-balance" style="font-size: 18px; color: var(--primary);">Rs. <?= number_format($grandTotal, 2) ?></strong>
        </div>
    </div>

    <button type="submit" class="btn-primary" style="background: var(--success); box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);" onclick="return validateChequeDetails(event)">
        ✓ Confirm POS Checkout
    </button>
    
    <button type="button" class="btn-secondary" onclick="submitAsCredit()" style="margin-top: 12px; background: #e0e0e0; color: #333; font-weight: bold; border-radius: 10px; width: 100%; border: none; padding: 15px; font-size: 15px; cursor: pointer;">
        💳 Complete as Credit Sale (No Payment)
    </button>
</form>

<script>
    var grandTotal = parseFloat(document.getElementById('total-outstanding').getAttribute('data-val'));
    
    function calculateLiveBalances() {
        var totalCollected = 0.0;
        
        // Cash
        var cashInput = document.getElementById('cash-input');
        var cashVal = parseFloat(cashInput.value);
        if (!isNaN(cashVal) && cashVal > 0) {
            totalCollected += cashVal;
        }

        // Bank
        var bankInput = document.getElementById('bank-input');
        var bankVal = parseFloat(bankInput.value);
        if (!isNaN(bankVal) && bankVal > 0) {
            totalCollected += bankVal;
        }

        // Cheques
        var chequeInputs = document.querySelectorAll('.cheque-amount-input');
        for (var i = 0; i < chequeInputs.length; i++) {
            var val = parseFloat(chequeInputs[i].value);
            if (!isNaN(val) && val > 0) {
                totalCollected += val;
            }
        }

        var projectedBalance = Math.max(0, grandTotal - totalCollected);

        document.getElementById('live-total-collected').innerText = 'Rs. ' + totalCollected.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('live-projected-balance').innerText = 'Rs. ' + projectedBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function addChequeRow() {
        var container = document.getElementById('cheques-container');
        var div = document.createElement('div');
        div.className = 'cheque-card';
        div.style.padding = '12px 15px';
        div.style.borderLeft = '4px solid #f57c00';
        div.style.background = 'var(--app-bg)';
        div.style.borderRadius = '8px';
        div.style.position = 'relative';
        div.style.border = '1px solid var(--border)';
        div.style.borderLeftWidth = '4px';
        
        var today = new Date().toISOString().split('T')[0];
        
        div.innerHTML = 
            '<button type="button" onclick="removeChequeRow(this)" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 16px; color: var(--danger); cursor: pointer; padding: 4px;">✕</button>' +
            '<h4 style="margin: 0 0 10px; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #f57c00;">Cheque Entry</h4>' +
            '<label class="form-label" style="font-size: 11px; margin-bottom: 4px; display: block;">Cheque Amount (Rs.)</label>' +
            '<input type="number" step="0.01" name="cheque_amounts[]" class="form-input cheque-amount-input" placeholder="0.00" value="0.00" required style="margin-bottom: 10px;">' +
            '<label class="form-label" style="font-size: 11px; margin-bottom: 4px; display: block;">Cheque Number</label>' +
            '<input type="text" name="cheque_numbers[]" class="form-input cheque-number-input" placeholder="e.g. CHQ74839" required style="margin-bottom: 10px;">' +
            '<label class="form-label" style="font-size: 11px; margin-bottom: 4px; display: block;">Banking Date (PDC)</label>' +
            '<input type="date" name="cheque_dates[]" class="form-input cheque-date-input" value="' + today + '" required style="margin-bottom: 10px;">' +
            '<label class="form-label" style="font-size: 11px; margin-bottom: 4px; display: block;">Bank Name</label>' +
            '<input type="text" name="cheque_banks[]" class="form-input cheque-bank-input" placeholder="e.g. Commercial Bank" required style="margin-bottom: 0;">';
            
        container.appendChild(div);
        
        var amountInput = div.querySelector('.cheque-amount-input');
        amountInput.addEventListener('input', calculateLiveBalances);
        amountInput.addEventListener('focus', function() {
            if (parseFloat(this.value) === 0) {
                this.value = '';
            }
        });
        amountInput.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.value = '0.00';
            }
            calculateLiveBalances();
        });
        
        calculateLiveBalances();
    }

    function removeChequeRow(btn) {
        var card = btn.parentNode;
        card.parentNode.removeChild(card);
        calculateLiveBalances();
    }

    function validateChequeDetails(e) {
        var chequeCards = document.querySelectorAll('.cheque-card');
        for (var i = 0; i < chequeCards.length; i++) {
            var card = chequeCards[i];
            var amt = parseFloat(card.querySelector('.cheque-amount-input').value);
            var num = card.querySelector('.cheque-number-input').value.trim();
            var date = card.querySelector('.cheque-date-input').value.trim();
            var bank = card.querySelector('.cheque-bank-input').value.trim();
            
            if (amt > 0) {
                if (!num || !date || !bank) {
                    e.preventDefault();
                    alert("Please provide all Cheque Details (Cheque Number, Banking Date, and Bank Name) for each entered cheque.");
                    return false;
                }
            }
        }
        return true;
    }

    // Bind event listeners to cash & bank inputs
    var mainInputs = [document.getElementById('cash-input'), document.getElementById('bank-input')];
    for (var i = 0; i < mainInputs.length; i++) {
        (function(input) {
            input.addEventListener('input', calculateLiveBalances);
            input.addEventListener('focus', function() {
                if (parseFloat(this.value) === 0) {
                    this.value = '';
                }
            });
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '0.00';
                }
                calculateLiveBalances();
            });
        })(mainInputs[i]);
    }

    function submitAsCredit() {
        if (confirm("Are you sure you want to finalize this as a Credit Sale with Rs. 0.00 collection?")) {
            document.getElementById('cash-input').value = "0.00";
            document.getElementById('bank-input').value = "0.00";
            var container = document.getElementById('cheques-container');
            if (container) container.innerHTML = '';
            document.getElementById('checkout-form').submit();
        }
    }

    // Run initial calculation
    calculateLiveBalances();
</script>
