<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — AI SUGGESTED ORDER
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
    --shadow-md:  0 8px 24 rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
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
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

/* ---- Panel Card ---- */
.suggest-panel {
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
.suggest-header {
    margin-bottom: 28px;
}
.suggest-title {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 6px;
    color: var(--t-primary);
}
.suggest-subtitle {
    font-size: 14px;
    color: var(--c-blue);
    margin: 0;
    font-weight: 700;
}

/* ---- Info Panel ---- */
.info-panel {
    background: var(--c-surface2);
    border: 0.5px solid var(--c-separator);
    padding: 16px 20px;
    border-radius: var(--r-md);
    margin-bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}
.info-block-label {
    font-size: 11px;
    color: var(--t-label);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
    display: block;
}
.info-block-value {
    font-size: 15px;
    font-weight: 700;
    color: var(--t-primary);
}

/* ---- Table Styles ---- */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background: var(--c-surface);
    border-radius: var(--r-md);
    overflow: hidden;
    border: 0.5px solid var(--c-separator);
}
.data-table th {
    background: var(--c-surface2);
    padding: 12px 16px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--t-label);
    border-bottom: 0.5px solid var(--c-separator);
}
.data-table td {
    padding: 14px 16px;
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

/* ---- Button ---- */
.btn-submit {
    padding: 14px 28px;
    background: var(--t-primary);
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

.num-col { text-align: center; font-family: var(--f-mono); font-weight: 600; }
</style>

<div class="inv-wrap">
    <a href="<?= APP_URL ?>/purchase/wizard" class="btn-quick">&larr; Back to Wizard</a>

    <div class="suggest-panel">
        <div class="suggest-header">
            <h2 class="suggest-title">AI Suggested Order</h2>
            <p class="suggest-subtitle"><i class="fa-solid fa-building"></i> Vendor: <?= htmlspecialchars($data['vendor']->name) ?></p>
        </div>

        <div class="info-panel">
            <div>
                <span class="info-block-label">Analysis Period</span>
                <span class="info-block-value"><?= date('M d, Y', strtotime($data['start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($data['end_date'])) ?></span>
            </div>
            <div>
                <span class="info-block-label">Safety Buffer Applied</span>
                <span class="info-block-value"><?= $data['buffer'] ?>%</span>
            </div>
            <div style="font-size: 12px; color: var(--t-secondary); max-width: 320px; text-align: right; line-height: 1.4;">
                <em>Calculation Formula: (Sold Qty + Buffer) - Current Stock = Suggested. Negative results default to 0.</em>
            </div>
        </div>

        <form action="<?= APP_URL ?>/purchase/create" method="POST">
            <input type="hidden" name="action" value="from_suggest">
            <input type="hidden" name="vendor_id" value="<?= $data['vendor']->id ?>">

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align:center;"><input type="checkbox" id="selectAll" checked onchange="toggleAll(this)" style="transform:scale(1.2);"></th>
                        <th>Product / Item</th>
                        <th class="num-col">Sold in Period</th>
                        <th class="num-col">Current Stock</th>
                        <th class="num-col" style="color:var(--c-blue);">Suggested Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['sales_data'])): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--t-label); padding: 40px; font-style: italic;">
                                No sales history found for this vendor in the selected date range.
                            </td>
                        </tr>
                    <?php else: foreach($data['sales_data'] as $index => $item): ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" name="selected_items[]" value="<?= $index ?>" class="item-cb" <?= $item->suggested_qty > 0 ? 'checked' : '' ?> style="transform:scale(1.2);">
                                
                                <!-- Hidden Data required for PO Creation mapping -->
                                <input type="hidden" name="item_id[<?= $index ?>]" value="<?= $item->id ?>">
                                <input type="hidden" name="item_name[<?= $index ?>]" value="<?= htmlspecialchars($item->item_name) ?>">
                                <input type="hidden" name="item_cost[<?= $index ?>]" value="<?= $item->cost ?>">
                            </td>
                            <td>
                                <strong style="font-size:14px;"><?= htmlspecialchars($item->item_name) ?></strong><br>
                                <span style="font-size:12px; color:var(--t-secondary); margin-top:3px; display:inline-block;">Cost Price: Rs. <?= number_format($item->cost, 2) ?></span>
                            </td>
                            <td class="num-col"><?= $item->sold_qty ?></td>
                            <td class="num-col" style="color: <?= $item->quantity_on_hand <= 0 ? 'var(--c-red)' : 'var(--c-green)' ?>; font-weight:700;">
                                <?= $item->quantity_on_hand ?>
                            </td>
                            <td class="num-col">
                                <input type="number" name="suggested_qty[<?= $index ?>]" value="<?= $item->suggested_qty ?>" class="form-control" min="1" step="1">
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 32px; text-align: right; border-top: 0.5px solid var(--c-separator); padding-top: 24px;">
                <button type="submit" class="btn-submit">
                    <span>Generate Draft PO with Selected Items</span> <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAll(masterCb) {
        document.querySelectorAll('.item-cb').forEach(cb => cb.checked = masterCb.checked);
    }
</script>