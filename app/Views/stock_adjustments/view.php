<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   APPLE DESIGN LANGUAGE — STOCK ADJUSTMENT DETAILS
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

.view-wrap {
    max-width: 1200px;
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

/* ---- Summary Widgets ---- */
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
.badge-pending { background: var(--c-orange-light); color: var(--c-orange); }
.badge-approved { background: var(--c-green-light); color: var(--c-green); }
.badge-rejected { background: var(--c-red-light); color: var(--c-red); }

/* ---- Metadata Panel ---- */
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
.items-table {
    width: 100%;
    border-collapse: collapse;
}
.items-table th {
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
.items-table td {
    padding: 14px 16px;
    font-size: 14px;
    border-bottom: 0.5px solid var(--c-separator2);
}
.items-table tr:last-child td {
    border-bottom: none;
}

.val-text { font-family: var(--f-mono); font-weight: 600; }
.val-positive { color: var(--c-green); }
.val-negative { color: var(--c-red); }

/* ---- Remarks ---- */
.remarks-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    padding: 20px;
    margin-bottom: 24px;
}

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
.btn-danger { background: var(--c-red-light); color: var(--c-red); }
.btn-danger:hover { background: rgba(255,59,48,0.15); }
</style>

<div class="view-wrap">
    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="eyebrow">Adjustment Details</div>
            <div class="title"><?= htmlspecialchars($data['adjustment']->adjustment_number); ?></div>
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
        $netAdjustmentVal = 0;
        $totalItemsCount = 0;
        foreach ($data['items'] as $item) {
            $netAdjustmentVal += floatval($item->total_value);
            $totalItemsCount++;
        }
    ?>
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Status</span>
            <span class="summary-val">
                <?php if ($data['adjustment']->status === 'Pending'): ?>
                    <span class="badge badge-pending">Pending Approval</span>
                <?php elseif ($data['adjustment']->status === 'Approved'): ?>
                    <span class="badge badge-approved">Approved &amp; Posted</span>
                <?php else: ?>
                    <span class="badge badge-rejected">Rejected</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Reason / Type</span>
            <span class="summary-val" style="font-size: 16px;"><?= htmlspecialchars($data['adjustment']->reason); ?></span>
        </div>
        <div class="summary-card">
            <span class="summary-label">SKUs Adjusted</span>
            <span class="summary-val"><?= $totalItemsCount; ?> items</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Total Adjusted Value</span>
            <span class="summary-val val-text">
                <?= number_format($netAdjustmentVal, 2); ?> LKR
            </span>
        </div>
    </div>

    <!-- Meta Details -->
    <div class="meta-panel">
        <div class="meta-item">
            <label>Warehouse</label>
            <span style="font-weight: 600;"><?= htmlspecialchars($data['adjustment']->warehouse_name); ?></span>
        </div>
        <div class="meta-item">
            <label>Date Requested</label>
            <span><?= date('Y-m-d', strtotime($data['adjustment']->adjustment_date)); ?></span>
        </div>
        <div class="meta-item">
            <label>Created By</label>
            <span><?= htmlspecialchars($data['adjustment']->creator_name ?? 'System'); ?></span>
        </div>
        <div class="meta-item">
            <label>Approver / Actioner</label>
            <span><?= $data['adjustment']->approver_name ? htmlspecialchars($data['adjustment']->approver_name) : '-'; ?> 
                <?= $data['adjustment']->approved_at ? 'on ' . date('Y-m-d H:i', strtotime($data['adjustment']->approved_at)) : ''; ?>
            </span>
        </div>
    </div>

    <!-- Additional Reference Information -->
    <div class="meta-panel" style="margin-top: -10px;">
        <div class="meta-item">
            <label>Journal Entry Reference</label>
            <span style="font-family: var(--f-mono); font-weight: 600;">
                <?= $data['adjustment']->journal_reference ? htmlspecialchars($data['adjustment']->journal_reference) : '-'; ?>
            </span>
        </div>
        <div class="meta-item">
            <label>Link Source (Stock Audit)</label>
            <span>
                <?php if ($data['adjustment']->stock_audit_id): ?>
                    <a href="<?= APP_URL ?>/stockaudit/show/<?= $data['adjustment']->stock_audit_id; ?>" style="color: var(--c-blue); text-decoration: none; font-weight: 600;">
                        <?= htmlspecialchars($data['adjustment']->audit_number); ?>
                    </a>
                <?php else: ?>
                    Manual Input
                <?php endif; ?>
            </span>
        </div>
        <div class="meta-item">
            <label>Attachment Document</label>
            <span>
                <?php if ($data['adjustment']->attachment_path): ?>
                    <a href="<?= APP_URL ?>/<?= htmlspecialchars($data['adjustment']->attachment_path); ?>" target="_blank" style="color: var(--c-blue); text-decoration: none; font-weight: 600;">
                        <i class="fa-solid fa-paperclip"></i> View Uploaded File
                    </a>
                <?php else: ?>
                    None Uploaded
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- Items Grid Table -->
    <div class="table-card">
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Code / SKU</th>
                    <th>Product Details</th>
                    <th style="text-align: right;">Adjustment Qty</th>
                    <th style="text-align: right;">Unit Cost</th>
                    <th style="text-align: right;">Total Adjusted Value</th>
                    <th>Item Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['items'] as $item): ?>
                    <tr>
                        <td style="font-family: var(--f-mono); font-weight: 600; color: var(--c-blue);"><?= htmlspecialchars($item->item_code); ?></td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($item->item_name); ?></div>
                            <div style="font-size: 11px; color: var(--t-secondary);"><?= htmlspecialchars($item->category_name ?? 'General'); ?></div>
                        </td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: bold;" class="<?= floatval($item->quantity) >= 0 ? 'val-positive' : 'val-negative'; ?>">
                            <?= (floatval($item->quantity) >= 0 ? '+' : '') . number_format($item->quantity, 2); ?>
                        </td>
                        <td style="text-align: right; font-family: var(--f-mono); color: var(--t-secondary);"><?= number_format($item->unit_cost, 2); ?></td>
                        <td style="text-align: right; font-family: var(--f-mono); font-weight: 700;"><?= number_format($item->total_value, 2); ?></td>
                        <td style="color: var(--t-secondary); font-size: 13px;"><?= htmlspecialchars($item->remarks ?: '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Remarks Card -->
    <?php if (!empty($data['adjustment']->remarks)): ?>
        <div class="remarks-card">
            <h4 style="margin-top: 0; margin-bottom: 8px; font-weight: 700;">General Explanation Notes</h4>
            <p style="margin: 0; font-size: 14px; color: var(--t-secondary); line-height: 1.5; white-space: pre-wrap;"><?= htmlspecialchars($data['adjustment']->remarks); ?></p>
        </div>
    <?php endif; ?>

    <!-- Actions Panel -->
    <div class="btn-row">
        <a href="<?= APP_URL ?>/stockadjustment" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Adjustments</a>

        <div style="display: flex; gap: 12px;">
            <?php 
                $userRole = strtolower($_SESSION['role'] ?? '');
                $isAdminUser = in_array($userRole, ['admin', 'administrator', 'super admin', 'superadmin', 'manager']) || (function_exists('hasPermission') && hasPermission('inventory', 'create_edit'));
            ?>
            <?php if ($data['adjustment']->status === 'Pending' && $isAdminUser): ?>
                <form method="POST" action="<?= APP_URL ?>/stockadjustment/reject/<?= $data['adjustment']->id; ?>" style="display: inline;" onsubmit="return confirm('Reject this stock adjustment request?');">
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Reject Request</button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/stockadjustment/approve/<?= $data['adjustment']->id; ?>" style="display: inline;" onsubmit="return confirm('Approving this stock adjustment will instantly update warehouse product quantities and post double-entry journal logs to the general ledger. Proceed?');">
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-file-invoice-dollar"></i> Approve &amp; Post Ledger</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
