<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — PO WIZARD
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.08);
    --c-fill2:        rgba(120,120,128,0.12);
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-blue-mid:     #b3d6ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-xl: 26px;
    --r-pill: 999px;

    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
    --dur-mid:     0.28s;
}

.inv-wrap {
    max-width: 720px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

/* ---- Card ---- */
.wizard-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl);
    padding: 40px;
    position: relative;
    overflow: hidden;
}
.wizard-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 5px;
    background: linear-gradient(90deg, var(--c-blue), var(--c-purple));
}

/* ---- Back Button ---- */
.btn-quick {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    text-decoration: none;
    transition: all var(--dur-fast);
    margin-bottom: 24px;
}
.btn-quick:hover {
    background: var(--c-fill2);
    color: var(--t-primary);
}

/* ---- Typography ---- */
.wizard-title {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.02em;
    text-align: center;
    margin: 0 0 8px;
    color: var(--t-primary);
}
.wizard-subtitle {
    font-size: 14px;
    color: var(--t-secondary);
    text-align: center;
    margin: 0 0 32px;
    font-weight: 500;
}

/* ---- Form controls ---- */
.form-group {
    margin-bottom: 24px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--t-label);
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.form-control {
    width: 100%;
    padding: 12px 16px;
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    font-size: 15px;
    font-weight: 500;
    font-family: var(--f-system);
    color: var(--t-primary);
    outline: none;
    transition: border-color var(--dur-fast), box-shadow var(--dur-fast), background var(--dur-fast);
    box-sizing: border-box;
}
.form-control:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3.5px rgba(0,122,255,0.14);
}

/* ---- Radio Cards ---- */
.radio-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 28px;
}
.radio-card {
    border: 1px solid var(--c-separator);
    border-radius: var(--r-md);
    padding: 20px;
    cursor: pointer;
    transition: all var(--dur-fast) var(--ease-spring);
    position: relative;
    background: var(--c-surface);
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.radio-card:hover {
    transform: translateY(-2px);
    border-color: var(--c-blue);
    box-shadow: var(--shadow-sm);
}
.radio-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}
.radio-card h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--t-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}
.radio-card p {
    margin: 0;
    font-size: 12px;
    color: var(--t-secondary);
    line-height: 1.4;
}
.radio-card.selected {
    border-color: var(--c-blue);
    border-width: 1.5px;
    background: var(--c-blue-light);
    box-shadow: 0 4px 12px rgba(0,122,255,0.12);
}
.radio-card.selected h4 {
    color: var(--c-blue);
}

/* ---- AI Settings Box ---- */
.ai-settings {
    display: none;
    background: var(--c-surface2);
    padding: 24px;
    border-radius: var(--r-md);
    border: 1px dashed var(--c-separator);
    margin-bottom: 28px;
    animation: fadeIn var(--dur-mid) var(--ease-ios);
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* ---- Submit Button ---- */
.btn-submit {
    padding: 14px 28px;
    background: var(--t-primary);
    color: #fff;
    border: none;
    border-radius: var(--r-md);
    cursor: pointer;
    font-size: 15px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast);
    text-decoration: none;
    width: 100%;
    box-shadow: var(--shadow-sm);
}
.btn-submit:hover { filter: brightness(0.92); }
.btn-submit:active { transform: scale(0.98); }
</style>

<div class="inv-wrap">
    <a href="<?= APP_URL ?>/purchase" class="btn-quick">&larr; Back to Purchase Orders</a>

    <div class="wizard-card">
        <h2 class="wizard-title">Purchase Order Wizard</h2>
        <p class="wizard-subtitle">How would you like to generate this order?</p>

        <form action="<?= APP_URL ?>/purchase/wizard_process" method="POST">
            
            <div class="form-group">
                <label>Select Supplier / Vendor *</label>
                <select name="vendor_id" class="form-control" required>
                    <option value="">Select Vendor...</option>
                    <?php foreach($data['vendors'] as $ven): ?>
                        <option value="<?= $ven->id ?>"><?= htmlspecialchars($ven->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label style="font-size: 12px; font-weight: 600; color: var(--t-label); display:block; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.02em;">Generation Mode *</label>
            <div class="radio-group">
                <label class="radio-card selected" id="card_manual">
                    <input type="radio" name="po_mode" value="manual" checked onchange="toggleMode()">
                    <h4><i class="fa-solid fa-pen-to-square"></i> Manual Entry</h4>
                    <p>Start with a blank Purchase Order and add items yourself.</p>
                </label>
                <label class="radio-card" id="card_ai">
                    <input type="radio" name="po_mode" value="ai" onchange="toggleMode()">
                    <h4><i class="fa-solid fa-wand-magic-sparkles"></i> Smart Suggestion</h4>
                    <p>Analyze past sales and stock levels to calculate exactly what to order.</p>
                </label>
            </div>

            <div id="aiSettings" class="ai-settings">
                <div class="form-group">
                    <label>Analyze Sales From</label>
                    <select name="date_range" id="dateRange" class="form-control" onchange="toggleCustomDates()">
                        <option value="week">Past 7 Days</option>
                        <option value="month">Past 30 Days</option>
                        <option value="custom">Custom Date Range...</option>
                    </select>
                </div>
                
                <div class="grid-2" id="customDates" style="display:none; margin-bottom:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label>Safety Buffer Percentage (%)</label>
                    <input type="number" name="buffer_percent" class="form-control" value="10" min="0" step="1">
                    <span style="font-size:12px; color:var(--t-secondary); display:block; margin-top:6px; line-height:1.4;">
                        If you sold 100 units, a 10% buffer will suggest ordering 110 units (minus current stock).
                    </span>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span>Start Manual PO</span> <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<script>
    function toggleMode() {
        const mode = document.querySelector('input[name="po_mode"]:checked').value;
        const cardManual = document.getElementById('card_manual');
        const cardAi = document.getElementById('card_ai');
        const aiSettings = document.getElementById('aiSettings');
        const submitBtn = document.getElementById('submitBtn');

        if (mode === 'manual') {
            cardManual.classList.add('selected');
            cardAi.classList.remove('selected');
            aiSettings.style.display = 'none';
            submitBtn.querySelector('span').innerText = 'Start Manual PO';
        } else {
            cardAi.classList.add('selected');
            cardManual.classList.remove('selected');
            aiSettings.style.display = 'block';
            submitBtn.querySelector('span').innerText = 'Analyze & Suggest';
        }
    }

    function toggleCustomDates() {
        const range = document.getElementById('dateRange').value;
        document.getElementById('customDates').style.display = (range === 'custom') ? 'grid' : 'none';
    }
</script>