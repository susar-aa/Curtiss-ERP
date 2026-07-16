<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — STOCK AUDIT DETAILS
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-label:     #8e8e93;

    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08);

    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-pill: 999px;

    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
}

.details-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 24px 100px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.page-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 4px;
}
.title {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.flash-msg {
    padding: 14px 20px;
    border-radius: var(--r-md);
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}
.flash-msg-success { background: var(--c-green-light); color: #1e7e34; border: 0.5px solid rgba(52,199,89,0.3); }
.flash-msg-error { background: var(--c-red-light); color: #bd2130; border: 0.5px solid rgba(255,59,48,0.3); }

/* ---- Summary Cards ---- */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.summary-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.summary-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--t-label);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.summary-val {
    font-size: 20px;
    font-weight: 700;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: var(--r-pill);
    font-size: 12px;
    font-weight: 600;
}
.badge-draft { background: var(--c-fill); color: var(--t-secondary); }
.badge-progress { background: var(--c-blue-light); color: var(--c-blue); }
.badge-completed { background: var(--c-purple-light); color: var(--c-purple); }
.badge-approved { background: var(--c-green-light); color: var(--c-green); }
.badge-cancelled { background: var(--c-red-light); color: var(--c-red); }

/* ---- Details Meta Section ---- */
.meta-panel {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 14px;
}
.meta-item label {
    font-size: 11px;
    font-weight: 600;
    color: var(--t-label);
    text-transform: uppercase;
}

/* ---- Items Table ---- */
.table-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 24px;
}
.audit-table {
    width: 100%;
    border-collapse: collapse;
}
.audit-table th {
    background: var(--c-surface2);
    padding: 14px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--t-secondary);
    border-bottom: 0.5px solid var(--c-separator);
    letter-spacing: 0.05em;
    text-align: left;
}
.audit-table td {
    padding: 14px 16px;
    font-size: 14px;
    border-bottom: 0.5px solid var(--c-separator2);
}
.audit-table tr:last-child td {
    border-bottom: none;
}

.val-text { font-family: var(--f-mono); font-weight: 600; }
.val-positive { color: var(--c-green); }
.val-negative { color: var(--c-red); }

/* ---- Remarks Box ---- */
.remarks-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    padding: 20px;
    margin-bottom: 24px;
}

/* ---- Action Buttons ---- */
.btn-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn {
    padding: 12px 24px;
    border-radius: var(--r-pill);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-secondary { background: var(--c-fill); color: var(--t-primary); }
.btn-secondary:hover { background: rgba(120,120,128,0.2); }
.btn-primary { background: var(--c-blue); color: #fff; }
.btn-primary:hover { background: #0066cc; }
.btn-success { background: var(--c-green); color: #fff; }
.btn-success:hover { background: #2fb34f; }
.btn-danger { background: var(--c-red); color: #fff; }
.btn-danger:hover { background: #e02e24; }

/* ---- Variations Expandable Styles ---- */
.variation-item-row.hidden {
    display: none !important;
}
.toggle-var-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--t-secondary);
    width: 24px;
    height: 24px;
    border-radius: var(--r-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background var(--dur-fast), color var(--dur-fast);
    margin-right: 6px;
}
.toggle-var-btn:hover {
    background: rgba(0, 0, 0, 0.05);
    color: var(--t-primary);
}
.has-variations-parent td {
    background-color: rgba(0, 122, 255, 0.04) !important;
}
.has-variations-parent td:first-child {
    border-left: 4px solid var(--c-blue) !important;
}
.variation-item-row td {
    background-color: rgba(0, 122, 255, 0.01) !important;
}
.variation-item-row td:first-child {
    border-left: 4px solid var(--c-blue) !important;
}
</style>

<div class="details-wrap">
    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="eyebrow">Audit Details</div>
            <div class="title"><?= htmlspecialchars($data['audit']->audit_number); ?></div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= APP_URL ?>/stockaudit/printReport/<?= $data['audit']->id; ?>" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-print"></i> Print Audit Report
            </a>
            <a href="<?= APP_URL ?>/stockaudit/printReport/<?= $data['audit']->id; ?>?variance_only=1" target="_blank" class="btn btn-secondary" style="border: 1px solid var(--c-orange); color: var(--c-orange); background: transparent;">
                <i class="fa-solid fa-print"></i> Print Variance Report
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash-msg flash-msg-success">
            <i class="fa-solid fa-circle-check"></i>
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-msg flash-msg-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Summary Widgets -->
    <?php 
        $netVarianceVal = 0;
        $totalItemsCounted = 0;
        $discrepantItems = 0;
        foreach ($data['items'] as $item) {
            $netVarianceVal += floatval($item->variance_value);
            $totalItemsCounted++;
            if (floatval($item->difference) != 0.00) {
                $discrepantItems++;
            }
        }
    ?>
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Status</span>
            <span class="summary-val">
                <?php if ($data['audit']->status === 'Draft'): ?>
                    <span class="badge badge-draft">Draft</span>
                <?php elseif ($data['audit']->status === 'In Progress'): ?>
                    <span class="badge badge-progress">In Progress</span>
                <?php elseif ($data['audit']->status === 'Completed'): ?>
                    <span class="badge badge-completed">Completed (Pending Approval)</span>
                <?php elseif ($data['audit']->status === 'Approved'): ?>
                    <span class="badge badge-approved">Approved &amp; Posted</span>
                <?php else: ?>
                    <span class="badge badge-cancelled">Cancelled</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Items Checked</span>
            <span class="summary-val"><?= $totalItemsCounted; ?> items</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Discrepant SKU Count</span>
            <span class="summary-val" style="color: <?= $discrepantItems > 0 ? 'var(--c-orange)' : 'var(--c-green)'; ?>;">
                <?= $discrepantItems; ?> SKU(s)
            </span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Net Variance Valuation</span>
            <span class="summary-val val-text <?= $netVarianceVal >= 0 ? ($netVarianceVal > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                <?= ($netVarianceVal >= 0 ? '+' : '') . number_format($netVarianceVal, 2); ?> LKR
            </span>
        </div>
    </div>

    <!-- Metadata Details -->
    <div class="meta-panel">
        <div class="meta-item">
            <label>Warehouse</label>
            <span style="font-weight: 600;"><?= htmlspecialchars($data['audit']->warehouse_name); ?></span>
        </div>
        <div class="meta-item">
            <label>Created By</label>
            <span><?= htmlspecialchars($data['audit']->creator_name ?? 'System'); ?> on <?= date('Y-m-d H:i', strtotime($data['audit']->created_at)); ?></span>
        </div>
        <div class="meta-item">
            <label>Counted By</label>
            <span><?= $data['audit']->counter_name ? htmlspecialchars($data['audit']->counter_name) : '-'; ?> 
                <?= $data['audit']->completed_at ? 'on ' . date('Y-m-d H:i', strtotime($data['audit']->completed_at)) : ''; ?>
            </span>
        </div>
        <div class="meta-item">
            <label>Approved By</label>
            <span><?= $data['audit']->approver_name ? htmlspecialchars($data['audit']->approver_name) : '-'; ?> 
                <?= $data['audit']->approved_at ? 'on ' . date('Y-m-d H:i', strtotime($data['audit']->approved_at)) : ''; ?>
            </span>
        </div>
    </div>

    <!-- Items Variance Table -->
    <div class="table-card">
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Item Code / SKU</th>
                    <th>Product Details</th>
                    <th style="text-align: right;">System Qty</th>
                    <th style="text-align: right;">Physical Qty</th>
                    <th style="text-align: right;">Difference</th>
                    <th style="text-align: right;">Unit Cost</th>
                    <th style="text-align: right;">Variance Value</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Group items by item_id
                $groupedItems = [];
                foreach ($data['items'] as $item) {
                    $groupedItems[$item->item_id][] = $item;
                }

                foreach ($groupedItems as $itemId => $group): 
                    $firstItem = $group[0];
                    $hasVariations = (count($group) > 1 || !empty($firstItem->variation_option_id));
                    
                    if ($hasVariations):
                        // Compute parent totals
                        $parentSys = 0;
                        $parentPhys = 0;
                        $parentDiff = 0;
                        $parentVarVal = 0;
                        foreach ($group as $item) {
                            $parentSys += floatval($item->system_qty);
                            $parentPhys += floatval($item->physical_qty);
                            $parentDiff += floatval($item->difference);
                            $parentVarVal += floatval($item->variance_value);
                        }
                ?>
                    <!-- Parent Product Header Row -->
                    <tr class="has-variations-parent">
                        <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);">
                            <button type="button" class="toggle-var-btn" onclick="toggleVariationsRow(<?= $itemId; ?>, this)" title="Show Variations">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <?= htmlspecialchars($firstItem->base_item_code); ?>
                        </td>
                        <td>
                            <div style="font-weight: 700;"><?= htmlspecialchars($firstItem->base_item_name); ?></div>
                            <div style="font-size: 11px; color: var(--t-secondary);"><?= htmlspecialchars($firstItem->category_name ?? 'General'); ?></div>
                        </td>
                        <td style="text-align: right; font-family: var(--f-mono);"><?= number_format($parentSys, 2); ?></td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 600;"><?= number_format($parentPhys, 2); ?></td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" class="<?= $parentDiff >= 0 ? ($parentDiff > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                            <?= ($parentDiff >= 0 ? '+' : '') . number_format($parentDiff, 2); ?>
                        </td>
                        <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);">-</td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" class="<?= $parentVarVal >= 0 ? ($parentVarVal > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                            <?= ($parentVarVal >= 0 ? '+' : '') . number_format($parentVarVal, 2); ?>
                        </td>
                        <td><span style="font-size: 10px; font-weight: bold; color: var(--c-blue); background: var(--c-blue-light); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">Variable Product</span></td>
                    </tr>

                    <!-- Variation Rows -->
                    <?php foreach ($group as $item): ?>
                        <tr class="variation-item-row hidden variations_row_<?= $itemId; ?>">
                            <td style="padding-left: 36px; font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($item->item_code); ?></td>
                            <td>
                                <div style="font-weight: 600; padding-left: 8px;"><?= htmlspecialchars($item->item_name); ?></div>
                                <div style="font-size: 11px; color: var(--t-secondary); padding-left: 8px;"><?= htmlspecialchars($item->category_name ?? 'General'); ?></div>
                            </td>
                            <td style="text-align: right; font-family: var(--f-mono);"><?= number_format($item->system_qty, 2); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 600;"><?= number_format($item->physical_qty, 2); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" class="<?= floatval($item->difference) >= 0 ? (floatval($item->difference) > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                                <?= (floatval($item->difference) >= 0 ? '+' : '') . number_format($item->difference, 2); ?>
                            </td>
                            <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);"><?= number_format($item->unit_cost, 2); ?></td>
                            <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" class="<?= floatval($item->variance_value) >= 0 ? (floatval($item->variance_value) > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                                <?= (floatval($item->variance_value) >= 0 ? '+' : '') . number_format($item->variance_value, 2); ?>
                            </td>
                            <td style="color: var(--t-secondary); font-size: 13px;"><?= htmlspecialchars($item->remarks ?: '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: 
                    $item = $firstItem;
                ?>
                    <!-- Simple Product Row -->
                    <tr>
                        <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($item->item_code); ?></td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($item->item_name); ?></div>
                            <div style="font-size: 11px; color: var(--t-secondary);"><?= htmlspecialchars($item->category_name ?? 'General'); ?></div>
                        </td>
                        <td style="text-align: right; font-family: var(--f-mono);"><?= number_format($item->system_qty, 2); ?></td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 600;"><?= number_format($item->physical_qty, 2); ?></td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" class="<?= floatval($item->difference) >= 0 ? (floatval($item->difference) > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                            <?= (floatval($item->difference) >= 0 ? '+' : '') . number_format($item->difference, 2); ?>
                        </td>
                        <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);"><?= number_format($item->unit_cost, 2); ?></td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;" class="<?= floatval($item->variance_value) >= 0 ? (floatval($item->variance_value) > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                            <?= (floatval($item->variance_value) >= 0 ? '+' : '') . number_format($item->variance_value, 2); ?>
                        </td>
                        <td style="color: var(--t-secondary); font-size: 13px;"><?= htmlspecialchars($item->remarks ?: '-'); ?></td>
                    </tr>
                <?php 
                    endif;
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>

    <!-- Remarks Card -->
    <?php if (!empty($data['audit']->overall_remarks)): ?>
        <div class="remarks-card">
            <h4 style="margin-top: 0; margin-bottom: 8px; font-weight: 700;">Overall Auditor Remarks</h4>
            <p style="margin: 0; font-size: 14px; color: var(--t-secondary); line-height: 1.5; white-space: pre-wrap;"><?= htmlspecialchars($data['audit']->overall_remarks); ?></p>
        </div>
    <?php endif; ?>

    <!-- Actions Panel -->
    <div class="btn-row">
        <a href="<?= APP_URL ?>/stockaudit" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Audits</a>

        <div style="display: flex; gap: 12px; align-items: center;">
            <?php if ($data['audit']->status === 'Completed' && strtolower($_SESSION['role'] ?? '') === 'admin'): ?>
                <form method="POST" action="<?= APP_URL ?>/stockaudit/approve/<?= $data['audit']->id; ?>" onsubmit="return confirm('Approving this audit will automatically adjust the warehouse inventory quantities and post balanced double-entry journal postings in the General Ledger. Proceed?');">
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-file-invoice-dollar"></i> Approve Audit &amp; Post Adjustments</button>
                </form>
            <?php endif; ?>

            <?php if (in_array($data['audit']->status, ['Completed', 'Approved']) && strtolower($_SESSION['role'] ?? '') === 'admin'): ?>
                <form method="POST" action="<?= APP_URL ?>/stockaudit/delete/<?= $data['audit']->id; ?>" onsubmit="return confirm('WARNING: Deleting this completed/approved audit will permanently delete all its records and completely REVERSE all stock adjustments and ledger double-entries. This action cannot be undone. Are you sure you want to proceed?');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Delete &amp; Reverse Audit</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleVariationsRow(itemId, btn) {
    const rows = document.querySelectorAll('.variations_row_' + itemId);
    if (!rows.length) return;
    
    const isHidden = rows[0].classList.contains('hidden') || rows[0].style.display === 'none';
    rows.forEach(row => {
        if (isHidden) {
            row.classList.remove('hidden');
            row.style.display = '';
        } else {
            row.classList.add('hidden');
            row.style.display = 'none';
        }
    });
    
    if (isHidden) {
        btn.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
        btn.setAttribute('title', 'Hide Variations');
    } else {
        btn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
        btn.setAttribute('title', 'Show Variations');
    }
}
</script>
