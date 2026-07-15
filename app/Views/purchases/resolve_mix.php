<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — RESOLVE MIX VARIATIONS
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

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
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
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

/* ---- Panel Card ---- */
.resolve-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl);
    padding: 32px;
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

/* ---- Header Info ---- */
.resolve-header {
    margin-bottom: 24px;
}
.resolve-title {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 6px;
    color: var(--t-primary);
}
.resolve-subtitle {
    font-size: 14px;
    color: var(--t-secondary);
    margin: 0;
    font-weight: 500;
}
.resolve-subtitle strong {
    font-family: var(--f-mono);
    color: var(--c-blue);
}

/* ---- Info Panel ---- */
.info-panel {
    background: var(--c-orange-light);
    border: 0.5px solid rgba(255,149,0,0.3);
    padding: 16px 20px;
    border-radius: var(--r-md);
    margin-bottom: 28px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.info-panel i {
    font-size: 18px;
    color: var(--c-orange);
    margin-top: 2px;
}
.info-panel-msg {
    font-size: 13px;
    color: #c05d00;
    font-weight: 600;
    line-height: 1.4;
}

/* ---- Mix Box ---- */
.mix-box {
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    margin-bottom: 28px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.mix-header {
    background: var(--c-surface2);
    padding: 16px 20px;
    border-bottom: 0.5px solid var(--c-separator);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mix-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--t-primary);
}
.mix-meta {
    font-size: 12px;
    color: var(--t-secondary);
    margin-top: 3px;
    display: block;
}

/* ---- Table Styles ---- */
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th {
    background: var(--c-surface2);
    padding: 10px 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--t-label);
    border-bottom: 0.5px solid var(--c-separator);
    text-align: left;
}
.data-table td {
    padding: 12px 20px;
    border-bottom: 0.5px solid var(--c-separator2);
    font-size: 14px;
    vertical-align: middle;
}
.data-table tr:hover td {
    background: var(--c-fill);
}

/* ---- Inputs ---- */
.form-control {
    width: 110px;
    padding: 8px 12px;
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    font-size: 14px;
    font-weight: 700;
    font-family: var(--f-mono);
    color: var(--t-primary);
    outline: none;
    text-align: center;
    transition: all var(--dur-fast);
    box-sizing: border-box;
}
.form-control:focus {
    background: var(--c-surface);
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.15);
}

/* ---- Static Item ---- */
.static-item {
    background: var(--c-surface2);
    padding: 16px 20px;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.static-title {
    font-weight: 600;
    color: var(--t-primary);
}
.static-meta {
    font-size: 11px;
    color: var(--t-secondary);
    margin-top: 3px;
    display: block;
}

/* ---- Button ---- */
.btn-submit {
    padding: 14px 28px;
    background: var(--c-green);
    color: #fff;
    border: none;
    border-radius: var(--r-md);
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast);
    text-decoration: none;
    box-shadow: var(--shadow-sm);
}
.btn-submit:hover { filter: brightness(0.92); }
.btn-submit:active { transform: scale(0.98); }
</style>

<div class="inv-wrap">
    <a href="<?= APP_URL ?>/purchase" class="btn-quick">&larr; Back to PO Dashboard</a>

    <div class="resolve-panel">
        <div class="resolve-header">
            <h2 class="resolve-title">Resolve Variation Quantities (GRN)</h2>
            <p class="resolve-subtitle">Purchase Order: <strong><?= htmlspecialchars($data['po']->po_number) ?></strong></p>
        </div>

        <div class="info-panel">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div class="info-panel-msg">
                You ordered an unspecified mix of variations. Please specify exactly how many of each color/size arrived so we can update stock levels accurately.
            </div>
        </div>

        <form action="<?= APP_URL ?>/purchase/process_mix_grn" method="POST" id="resolveForm">
            <input type="hidden" name="po_id" value="<?= $data['po']->id ?>">

            <h3 style="margin: 0 0 16px; font-size: 14px; color: var(--t-label); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700;">Items to Resolve</h3>
            
            <?php foreach($data['items'] as $poItem): ?>
                <?php if($poItem->is_mix): ?>
                    <div class="mix-box">
                        <div class="mix-header">
                            <div>
                                <h3 class="mix-title"><?= htmlspecialchars($poItem->description) ?></h3>
                                <span class="mix-meta">Expected Total Quantity: <strong style="color:var(--t-primary);"><?= $poItem->quantity ?></strong></span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 11px; color: var(--t-label); text-transform: uppercase; font-weight: 700; letter-spacing: 0.02em;">Total Allocated</span><br>
                                <span style="font-size: 20px; font-weight: 800; color: var(--c-green); font-family: var(--f-mono);" id="allocated_<?= $poItem->id ?>">0</span>
                                <span style="font-size: 14px; color: var(--t-secondary); font-weight: 600; font-family: var(--f-mono);">/ <?= $poItem->quantity ?></span>
                            </div>
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 70%;">Specific Variation Received</th>
                                    <th style="width: 30%; text-align: center;">Qty Received</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($poItem->available_variations)): ?>
                                    <?php foreach($poItem->available_variations as $var): ?>
                                        <?php $desc = $poItem->description . ' -> ' . $var->variation_name . ': ' . $var->value_name; ?>
                                        <tr>
                                            <td>
                                                <strong style="color:var(--t-primary); font-size:14px;"><?= htmlspecialchars($var->variation_name) ?>: <?= htmlspecialchars($var->value_name) ?></strong>
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="number" name="resolve[<?= $poItem->id ?>][qty][]" class="form-control qty-input-<?= $poItem->id ?>" min="0" step="1" oninput="calcAllocated(<?= $poItem->id ?>)">
                                            </td>
                                            <input type="hidden" name="resolve[<?= $poItem->id ?>][var_opt_id][]" value="<?= $var->id ?>">
                                            <input type="hidden" name="resolve[<?= $poItem->id ?>][desc][]" value="<?= htmlspecialchars(str_replace(' (MIX)', '', $desc)) ?>">
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- Fallback row for generic stock -->
                                <tr style="background: var(--c-surface2);">
                                    <td>
                                        <strong style="color:var(--t-secondary); font-size:14px;">General Stock (No specific variation)</strong><br>
                                        <span style="font-size: 11px; color: var(--t-label); margin-top:2px; display:inline-block;">Adds to base item quantity only.</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" name="resolve[<?= $poItem->id ?>][qty][]" class="form-control qty-input-<?= $poItem->id ?>" min="0" step="1" oninput="calcAllocated(<?= $poItem->id ?>)">
                                    </td>
                                    <input type="hidden" name="resolve[<?= $poItem->id ?>][var_opt_id][]" value="">
                                    <input type="hidden" name="resolve[<?= $poItem->id ?>][desc][]" value="<?= htmlspecialchars(str_replace(' (MIX)', '', $poItem->description)) ?>">
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Pre-defined or standard item -->
                    <div class="static-item">
                        <div>
                            <span class="static-title"><?= htmlspecialchars($poItem->description) ?></span>
                            <span class="static-meta">Standard / Pre-selected</span>
                        </div>
                        <div style="font-weight: 700; font-family: var(--f-mono); font-size: 15px; color: var(--t-secondary);">
                            Qty: <?= $poItem->quantity ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div style="margin-top: 32px; text-align: right; border-top: 0.5px solid var(--c-separator); padding-top: 24px;">
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-circle-check"></i> Confirm Allocation & Generate GRN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calcAllocated(poItemId) {
        let total = 0;
        document.querySelectorAll('.qty-input-' + poItemId).forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('allocated_' + poItemId).innerText = total;
    }
</script>