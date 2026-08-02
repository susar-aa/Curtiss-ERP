<!-- Inter Font, Phosphor Icons & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<style>
    /* =====================================================
       CURTISS-ERP: MODERN GOODS RECEIPT NOTE (GRN) PANEL
       ===================================================== */
    html, body { overflow: hidden !important; height: 100%; margin: 0; padding: 0; }

    :root {
        --primary:       #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #eff6ff;
        --success:       #16a34a;
        --success-hover: #15803d;
        --success-light: #f0fdf4;
        --danger:        #dc2626;
        --danger-light:  #fef2f2;
        --warning:       #d97706;
        --warning-light: #fffbeb;
        --purple:        #7c3aed;
        --purple-light:  #f5f3ff;
        --slate-900:     #0f172a;
        --slate-800:     #1e293b;
        --slate-700:     #334155;
        --slate-600:     #475569;
        --slate-500:     #64748b;
        --slate-400:     #94a3b8;
        --slate-300:     #cbd5e1;
        --slate-200:     #e2e8f0;
        --slate-100:     #f1f5f9;
        --slate-50:      #f8fafc;
        --white:         #ffffff;
        --radius-xs:     4px;
        --radius-sm:     6px;
        --radius-md:     10px;
        --radius-lg:     14px;
        --shadow-xs:     0 1px 2px rgba(0,0,0,0.04);
        --shadow-sm:     0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md:     0 4px 14px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
        --shadow-xl:     0 20px 35px -8px rgba(0,0,0,0.18), 0 8px 16px -4px rgba(0,0,0,0.08);
        --font:          'Inter', system-ui, -apple-system, sans-serif;
        --f-mono:        ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;
    }

    /* ── Wrapper Container ── */
    .qb-wrapper {
        background: var(--slate-100);
        font-family: var(--font);
        font-size: 13px;
        color: var(--slate-800);
        height: calc(100vh - 85px);
        max-height: calc(100vh - 85px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 0 12px 12px 12px;
        box-sizing: border-box;
    }

    /* ── Main Panel Card ── */
    .qb-container {
        background: var(--white);
        width: 100%;
        max-width: 1560px;
        margin: 0 auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: 0;
        box-sizing: border-box;
        overflow: hidden;
    }

    #grnForm {
        display: flex;
        flex-direction: column;
        flex: 1;
        height: 100%;
        overflow: hidden;
        margin: 0;
    }

    /* ── Top Bar ── */
    .inv-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 18px;
        border-bottom: 1px solid var(--slate-200);
        background: var(--white);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        flex-shrink: 0;
        gap: 12px;
    }
    .inv-topbar-left { display: flex; align-items: center; gap: 12px; }
    .inv-topbar-right { display: flex; align-items: center; gap: 8px; }
    
    .inv-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .inv-icon-box {
        width: 34px;
        height: 34px;
        border-radius: var(--radius-md);
        background: #e0f2fe;
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .inv-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--slate-900);
        letter-spacing: -0.3px;
        margin: 0;
    }
    .inv-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        background: var(--primary-light);
        color: var(--primary);
        border: 1px solid rgba(37,99,235,0.2);
    }
    .inv-type-badge.edit-mode {
        background: var(--warning-light);
        color: var(--warning);
        border-color: rgba(217,119,6,0.25);
    }
    .po-linked-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        background: var(--success-light);
        color: var(--success);
        border: 1px solid rgba(22,163,74,0.25);
    }

    /* ── Action Buttons ── */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 14px;
        font-family: var(--font);
        font-size: 12px;
        font-weight: 600;
        border-radius: var(--radius-sm);
        border: 1px solid var(--slate-300);
        background: var(--white);
        color: var(--slate-700);
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
        line-height: 1.4;
        height: 32px;
        box-sizing: border-box;
    }
    .btn:hover { background: var(--slate-100); border-color: var(--slate-400); color: var(--slate-900); }
    .btn-primary { background: var(--primary); color: var(--white); border-color: var(--primary); }
    .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); color: var(--white); }
    .btn-success { background: var(--success); color: var(--white); border-color: var(--success); }
    .btn-success:hover { background: var(--success-hover); color: var(--white); }
    .btn-sm { padding: 4px 8px; font-size: 11px; height: 26px; }
    .btn-danger-outline { background: var(--white); color: var(--danger); border-color: #fca5a5; }
    .btn-danger-outline:hover { background: var(--danger-light); border-color: var(--danger); }
    .btn-kbd {
        font-family: var(--f-mono);
        font-size: 10px;
        background: rgba(0,0,0,0.15);
        color: inherit;
        padding: 1px 5px;
        border-radius: 3px;
        margin-left: 4px;
    }

    /* ── Body Scroll Container ── */
    .inv-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 12px 18px 0 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* ── Header Cards Grid ── */
    .inv-header-row {
        display: flex;
        gap: 14px;
        align-items: stretch;
        flex-shrink: 0;
    }

    /* Card 1: Supplier Card */
    .supplier-card {
        width: 360px;
        flex-shrink: 0;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        background: var(--white);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
    }
    .card-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 12px;
        background: var(--slate-800);
        color: var(--white);
        border-radius: var(--radius-md) var(--radius-md) 0 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .card-body-content {
        padding: 10px 12px;
        position: relative;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .custom-select-box {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--slate-300);
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-family: var(--font);
        font-weight: 600;
        color: var(--slate-800);
        background: var(--slate-50);
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .custom-select-box:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    .supplier-preview-info {
        background: var(--slate-50);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-sm);
        padding: 8px 10px;
        font-size: 11px;
        color: var(--slate-600);
        line-height: 1.4;
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-height: 48px;
    }

    /* Card 2: Document Meta Details Card */
    .doc-meta-card {
        flex: 1;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        background: var(--white);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
    }
    .doc-fields-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-sm);
        overflow: hidden;
        background: var(--white);
        margin-top: 2px;
    }
    .doc-field-col {
        border-right: 1px solid var(--slate-200);
        display: flex;
        flex-direction: column;
    }
    .doc-field-col:last-child { border-right: none; }
    .doc-field-label {
        background: var(--slate-100);
        color: var(--slate-600);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 8px;
        border-bottom: 1px solid var(--slate-200);
    }
    .doc-field-input {
        padding: 6px 10px;
        font-family: var(--font);
        font-size: 12px;
        color: var(--slate-800);
        border: none;
        background: transparent;
        width: 100%;
        box-sizing: border-box;
        outline: none;
        font-weight: 500;
    }
    .doc-field-input:focus {
        background: var(--primary-light);
    }
    .doc-field-input.mono {
        font-family: var(--f-mono);
        font-weight: 700;
        color: var(--primary);
    }

    /* ── Item Catalog Search Strip ── */
    .search-wrapper {
        position: relative;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }
    .search-input-container {
        position: relative;
        flex: 1;
    }
    .search-icon-prefix {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-400);
        font-size: 16px;
        pointer-events: none;
    }
    .item-search-bar {
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1.5px solid var(--slate-300);
        border-radius: var(--radius-md);
        font-size: 13px;
        font-family: var(--font);
        font-weight: 500;
        color: var(--slate-800);
        background: var(--slate-50);
        box-sizing: border-box;
        outline: none;
        transition: all 0.15s ease;
    }
    .item-search-bar:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
    }
    .item-search-bar::placeholder { color: var(--slate-400); font-size: 12px; }

    /* ── Search Dropdown Results ── */
    .search-results {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--white);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-xl);
        max-height: 280px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        padding: 4px;
        list-style: none;
        margin: 0;
    }
    .search-item-row {
        padding: 8px 12px;
        cursor: pointer;
        border-radius: var(--radius-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--slate-100);
        transition: background 0.1s ease;
    }
    .search-item-row:last-child { border-bottom: none; }
    .search-item-row:hover,
    .search-item-row.active {
        background: var(--primary-light);
        border-color: var(--primary-light);
    }
    .search-item-name {
        font-weight: 600;
        color: var(--slate-900);
        font-size: 12px;
    }
    .search-item-sku {
        font-family: var(--f-mono);
        font-size: 11px;
        color: var(--slate-500);
    }
    .search-item-cost {
        font-family: var(--f-mono);
        font-size: 12px;
        font-weight: 700;
        color: var(--slate-800);
    }
    .supplier-badge {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .badge-preferred {
        background: var(--success-light);
        color: var(--success);
        border: 1px solid rgba(22,163,74,0.25);
    }
    .badge-linked {
        background: var(--primary-light);
        color: var(--primary);
        border: 1px solid rgba(37,99,235,0.25);
    }
    .badge-new {
        background: var(--warning-light);
        color: var(--warning);
        border: 1px solid rgba(217,119,6,0.25);
    }

    /* ── Table Container & Grid ── */
    .table-scroll-container {
        flex: 1;
        min-height: 180px;
        overflow-y: auto;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        background: var(--white);
        box-shadow: var(--shadow-xs);
    }
    .qb-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .qb-table thead th {
        position: sticky;
        top: 0;
        background: var(--slate-800);
        color: var(--white);
        padding: 7px 10px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border-right: 1px solid rgba(255,255,255,0.1);
        border-bottom: 1px solid var(--slate-900);
        z-index: 10;
    }
    .qb-table thead th:last-child { border-right: none; }
    .qb-table tbody td {
        padding: 5px 8px;
        border-bottom: 1px solid var(--slate-200);
        border-right: 1px solid var(--slate-100);
        vertical-align: middle;
        font-size: 12px;
    }
    .qb-table tbody tr:hover td {
        background: var(--slate-50);
    }
    .qb-table tbody tr:last-child td {
        border-bottom: none;
    }
    .line-row-num {
        text-align: center;
        color: var(--slate-400);
        font-weight: 700;
        font-size: 11px;
    }
    .form-control-cell {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        padding: 4px 6px;
        font-size: 12px;
        font-family: var(--font);
        border-radius: var(--radius-xs);
        box-sizing: border-box;
        outline: none;
        transition: all 0.15s;
    }
    .form-control-cell:hover {
        border-color: var(--slate-300);
        background: var(--white);
    }
    .form-control-cell:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
    }
    .form-control-cell.num {
        text-align: right;
        font-family: var(--f-mono);
        font-weight: 600;
    }

    /* ── Pricing Badges ── */
    .price-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 6px;
        border-radius: var(--radius-xs);
        display: inline-block;
        font-family: var(--f-mono);
        text-align: right;
    }
    .price-retail {
        background: var(--primary-light);
        color: var(--primary);
        border: 1px solid rgba(37,99,235,0.25);
    }
    .price-wholesale {
        background: var(--purple-light);
        color: var(--purple);
        border: 1px solid rgba(124,58,237,0.25);
    }
    .btn-delete-row {
        background: transparent;
        border: none;
        color: var(--slate-400);
        cursor: pointer;
        padding: 4px 6px;
        border-radius: var(--radius-xs);
        transition: all 0.15s;
    }
    .btn-delete-row:hover {
        background: var(--danger-light);
        color: var(--danger);
    }

    /* ── Empty State ── */
    .empty-grid-notice {
        text-align: center;
        padding: 45px 20px;
        color: var(--slate-400);
    }

    /* ── Footer Panel ── */
    .inv-footer {
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        padding: 10px 18px 12px 18px;
        background: var(--slate-50);
        border-top: 1px solid var(--slate-200);
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
        flex-shrink: 0;
        gap: 20px;
    }
    .inv-footer-left {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .footer-field-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--slate-600);
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }
    .footer-textarea {
        width: 100%;
        height: 52px;
        border: 1px solid var(--slate-300);
        border-radius: var(--radius-sm);
        padding: 6px 10px;
        font-size: 11px;
        font-family: var(--font);
        color: var(--slate-800);
        background: var(--white);
        resize: none;
        box-sizing: border-box;
        outline: none;
    }
    .footer-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(37,99,235,0.12);
    }

    /* ── Summary Totals ── */
    .totals-card {
        width: 320px;
        background: var(--white);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        padding: 8px 14px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 4px;
        box-shadow: var(--shadow-xs);
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 12px;
        color: var(--slate-600);
    }
    .totals-row.grand-total {
        border-top: 1px dashed var(--slate-200);
        padding-top: 6px;
        margin-top: 4px;
        font-size: 14px;
        font-weight: 700;
        color: var(--slate-900);
    }
    .grand-total-amount {
        font-family: var(--f-mono);
        font-size: 17px;
        color: var(--success);
        font-weight: 800;
    }

    /* ── Alerts ── */
    .panel-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 8px;
    }
    .panel-alert.error {
        background: var(--danger-light);
        color: var(--danger);
        border: 1px solid rgba(220,38,38,0.25);
    }
</style>

<?php
$isEdit = isset($data['grn']);
$actionUrl = APP_URL . '/grn/' . ($isEdit ? "edit/{$data['grn']->id}" : "create");
?>

<div class="qb-wrapper">
    <div class="qb-container">
        <form action="<?= $actionUrl ?>" method="POST" id="grnForm">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update_grn' : 'save_grn' ?>">
            <?php if($data['linked_po']): ?>
                <input type="hidden" name="po_id" value="<?= $data['linked_po']->id ?>">
            <?php endif; ?>

            <!-- ═══ TOPBAR ═══ -->
            <div class="inv-topbar">
                <div class="inv-topbar-left">
                    <div class="inv-title-group">
                        <div class="inv-icon-box">
                            <i class="ph-bold ph-package"></i>
                        </div>
                        <div>
                            <div class="inv-title"><?= $isEdit ? 'Edit Goods Receipt Note' : 'Goods Receipt Note (GRN)' ?></div>
                        </div>
                    </div>
                    <span class="inv-type-badge <?= $isEdit ? 'edit-mode' : '' ?>">
                        <i class="ph-bold <?= $isEdit ? 'ph-pencil-simple' : 'ph-plus-circle' ?>"></i>
                        <?= $isEdit ? 'Edit Mode # ' . htmlspecialchars($data['grn']->grn_number) : 'New Receiving' ?>
                    </span>
                    <?php if($data['linked_po']): ?>
                        <span class="po-linked-badge">
                            <i class="ph-bold ph-link"></i> Linked PO: <?= htmlspecialchars($data['linked_po']->po_number) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="inv-topbar-right">
                    <a href="<?= APP_URL ?>/grn" class="btn" title="Back to GRN List">
                        <i class="ph-bold ph-arrow-left"></i> Back to GRNs
                    </a>
                    <button type="button" class="btn" onclick="openShortcutsModal()" title="Keyboard Shortcuts">
                        <i class="ph-bold ph-keyboard"></i> Shortcuts
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveGrnBtn">
                        <i class="ph-bold ph-check-circle"></i> Save GRN & Update Stock <span class="btn-kbd">Ctrl+S</span>
                    </button>
                </div>
            </div>

            <!-- ═══ ERROR ALERT (IF ANY) ═══ -->
            <?php if(!empty($data['error'])): ?>
                <div style="padding: 10px 18px 0 18px;">
                    <div class="panel-alert error">
                        <i class="ph-bold ph-warning-circle" style="font-size: 16px;"></i>
                        <span><?= htmlspecialchars($data['error']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ═══ BODY CONTENT ═══ -->
            <div class="inv-body">
                
                <!-- ── Header Info Cards Row ── -->
                <div class="inv-header-row">
                    <!-- Left: Supplier Card -->
                    <div class="supplier-card">
                        <div class="card-header-bar">
                            <span><i class="ph-bold ph-storefront"></i> Supplier / Vendor *</span>
                            <span id="supplierProductCount" style="font-size: 10px; opacity: 0.8;"></span>
                        </div>
                        <div class="card-body-content">
                            <select name="vendor_id" id="vendorSelect" class="custom-select-box" onchange="onVendorChange()" required <?= ($data['linked_po'] || $isEdit) ? 'style="pointer-events:none; background:#f1f5f9;"' : '' ?>>
                                <option value="">-- Select Vendor / Supplier --</option>
                                <?php foreach($data['vendors'] as $ven): ?>
                                    <option value="<?= $ven->id ?>" 
                                            data-phone="<?= htmlspecialchars($ven->phone ?? '') ?>"
                                            data-email="<?= htmlspecialchars($ven->email ?? '') ?>"
                                            data-address="<?= htmlspecialchars($ven->address ?? '') ?>"
                                            <?= $data['prefilled_vendor'] == $ven->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ven->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="supplier-preview-info" id="supplierInfoPreview">
                                <span style="color: var(--slate-400); font-style: italic;">Select a supplier above to load contact details and preferred product mappings.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Document Meta Details Card -->
                    <div class="doc-meta-card">
                        <div class="card-header-bar">
                            <span><i class="ph-bold ph-file-text"></i> Document & Receipt Information</span>
                            <span style="font-size: 10px; font-weight: 500; opacity: 0.85;">Physical Stock Reception</span>
                        </div>
                        <div class="card-body-content" style="justify-content: center;">
                            <div class="doc-fields-strip">
                                <div class="doc-field-col">
                                    <label class="doc-field-label">GRN Number *</label>
                                    <input type="text" name="grn_number" id="grn_number" class="doc-field-input mono" value="<?= htmlspecialchars($isEdit ? $data['grn']->grn_number : $data['grn_number']) ?>" required <?= $isEdit ? 'readonly style="pointer-events:none;"' : '' ?>>
                                </div>
                                <div class="doc-field-col">
                                    <label class="doc-field-label">Receipt Date *</label>
                                    <input type="date" name="grn_date" id="grn_date" class="doc-field-input" value="<?= htmlspecialchars($isEdit ? $data['grn']->grn_date : date('Y-m-d')) ?>" required>
                                </div>
                                <div class="doc-field-col">
                                    <label class="doc-field-label">Supplier Invoice / Bill No.</label>
                                    <input type="text" name="receipt_number" id="receipt_number" class="doc-field-input" placeholder="Supplier Bill / DC #" value="<?= htmlspecialchars($isEdit ? ($data['grn']->receipt_number ?? '') : '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Product Search & Quick Add Strip ── -->
                <div class="search-wrapper">
                    <div class="search-input-container">
                        <i class="ph-bold ph-magnifying-glass search-icon-prefix"></i>
                        <input type="text" id="itemSearch" class="item-search-bar"
                               placeholder="Search catalog by product name, SKU, item code, or variation... (Press ' / ' to focus, ↑ / ↓ to navigate, Enter to add)"
                               autocomplete="off">
                        <ul id="searchResults" class="search-results"></ul>
                    </div>
                    <button type="button" class="btn" onclick="addRow()" title="Add manual line item">
                        <i class="ph-bold ph-plus"></i> Add Blank Line <span class="btn-kbd">Alt+N</span>
                    </button>
                </div>

                <!-- ── Line Items Table ── -->
                <div class="table-scroll-container">
                    <table class="qb-table" id="linesTable">
                        <thead>
                            <tr>
                                <th style="width: 32px; text-align:center;">#</th>
                                <th style="width: 28%;">Product / Variation Received</th>
                                <th style="width: 8%; text-align:right;">Qty</th>
                                <th style="width: 11%; text-align:right;">Unit Cost (Rs)</th>
                                <th style="width: 12%; text-align:right;">Line Total (Rs)</th>
                                <th style="width: 8%; text-align:right;">Retail %</th>
                                <th style="width: 8%; text-align:right;">Wholesale %</th>
                                <th style="width: 11%; text-align:right; color: #93c5fd;">Retail Price</th>
                                <th style="width: 11%; text-align:right; color: #d8b4fe;">Wholesale B2B</th>
                                <th style="width: 32px; text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="poBody">
                            <?php if(!empty($data['prefilled_items'])): ?>
                                <?php $lineNum = 1; foreach($data['prefilled_items'] as $item): ?>
                                    <?php 
                                        $retailMargin = isset($item->retail_margin) ? floatval($item->retail_margin) : 0.0;
                                        $wholesaleMargin = isset($item->wholesale_margin) ? floatval($item->wholesale_margin) : 0.0;
                                        $displayName = $item->description;
                                        $sku = '';
                                        if ($item->item_id) {
                                            foreach($data['catalog_items'] as $catItem) {
                                                if ($catItem->id == $item->item_id) {
                                                    $sku = $catItem->item_code ?? '';
                                                    if (!isset($item->retail_margin)) {
                                                        $retailMargin = floatval($catItem->retail_margin ?? 0);
                                                    }
                                                    if (!isset($item->wholesale_margin)) {
                                                        $wholesaleMargin = floatval($catItem->wholesale_margin ?? 0);
                                                    }
                                                    $displayName = $catItem->name;
                                                    if ($item->item_variation_option_id && !empty($catItem->variations)) {
                                                        foreach($catItem->variations as $v) {
                                                            if ($v->id == $item->item_variation_option_id) {
                                                                $displayName = "{$catItem->name} - {$v->variation_name}: {$v->value_name}";
                                                                if ($v->sku) $sku = $v->sku;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $unitCost = isset($item->unit_cost) ? $item->unit_cost : ($item->unit_price ?? 0);
                                    ?>
                                    <tr>
                                        <td class="line-row-num"><?= $lineNum++ ?></td>
                                        <td style="position: relative;">
                                            <input type="text" class="form-control-cell row-search-input" value="<?= htmlspecialchars($displayName) ?>" placeholder="Search product..." autocomplete="off" required>
                                            <div class="row-suggestions-wrapper" style="display:none;"></div>
                                            <input type="hidden" name="item_selection[]" class="item-selection-hidden" value="<?= $item->item_id ?>|<?= $item->item_variation_option_id ?: '0' ?>" required>
                                            <input type="hidden" name="desc[]" class="desc-hidden" value="<?= htmlspecialchars($displayName) ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="qty[]" step="1" min="1" value="<?= $item->quantity ?>" class="form-control-cell num qty-input" oninput="calculateRowPrices(this.closest('tr'))" required>
                                        </td>
                                        <td>
                                            <input type="number" name="price[]" step="0.01" min="0" value="<?= number_format($unitCost, 2, '.', '') ?>" class="form-control-cell num cost-input" oninput="calculateRowPrices(this.closest('tr'))" required>
                                        </td>
                                        <td style="text-align: right; font-family: var(--f-mono); font-weight:700; color: var(--slate-900);">
                                            <span class="line-total-display">0.00</span>
                                        </td>
                                        <td>
                                            <input type="number" name="retail_margin[]" step="0.1" value="<?= number_format($retailMargin, 1, '.', '') ?>" class="form-control-cell num retail-margin-input" oninput="calculateRowPrices(this.closest('tr'))" required>
                                        </td>
                                        <td>
                                            <input type="number" name="wholesale_margin[]" step="0.1" value="<?= number_format($wholesaleMargin, 1, '.', '') ?>" class="form-control-cell num wholesale-margin-input" oninput="calculateRowPrices(this.closest('tr'))" required>
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="price-badge price-retail display-retail">0.00</span>
                                            <input type="hidden" name="selling_price[]" value="0.00">
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="price-badge price-wholesale display-wholesale">0.00</span>
                                            <input type="hidden" name="wholesale_price[]" value="0.00">
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-delete-row" onclick="removeRow(this)" title="Remove item">
                                                <i class="ph-bold ph-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="emptyTableNotice" class="empty-grid-notice" style="<?= !empty($data['prefilled_items']) ? 'display:none;' : '' ?>">
                        <i class="ph-bold ph-package" style="font-size: 38px; opacity: 0.4; margin-bottom: 6px;"></i>
                        <div style="font-size: 13px; font-weight: 600; color: var(--slate-600);">No items added to this GRN yet.</div>
                        <div style="font-size: 11px; color: var(--slate-400); margin-top: 2px;">Search products above or click "Add Blank Line" to begin.</div>
                    </div>
                </div>

            </div><!-- /.inv-body -->

            <!-- ═══ FOOTER ═══ -->
            <div class="inv-footer">
                <!-- Left: Inspection Memo -->
                <div class="inv-footer-left">
                    <label class="footer-field-label">Inspection Notes & Received Discrepancies</label>
                    <textarea name="notes" class="footer-textarea" placeholder="Log any physical damage, quantity variances, package seal numbers, or receiving remarks..."><?= htmlspecialchars($isEdit ? ($data['grn']->notes ?? '') : '') ?></textarea>
                </div>

                <!-- Right: Summary Totals -->
                <div class="totals-card">
                    <div class="totals-row">
                        <span>Total Items Count:</span>
                        <strong id="totalLinesLabel" style="font-family: var(--f-mono); color: var(--slate-800);">0 lines (0 units)</strong>
                    </div>
                    <div class="totals-row">
                        <span>Items Subtotal:</span>
                        <span id="subTotalLabel" style="font-family: var(--f-mono); font-weight: 600;">Rs 0.00</span>
                    </div>
                    <div class="totals-row grand-total">
                        <span>Grand Total:</span>
                        <span class="grand-total-amount" id="grandTotal">Rs 0.00</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ═══ KEYBOARD SHORTCUTS MODAL ═══ -->
<div id="shortcutsModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--white); border-radius:var(--radius-lg); width:420px; box-shadow:var(--shadow-xl); border:1px solid var(--slate-200); padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid var(--slate-200); padding-bottom:10px;">
            <div style="font-size:15px; font-weight:700; color:var(--slate-900); display:flex; align-items:center; gap:6px;">
                <i class="ph-bold ph-keyboard" style="color:var(--primary);"></i> Keyboard Navigation
            </div>
            <button type="button" onclick="closeShortcutsModal()" style="background:none; border:none; color:var(--slate-400); cursor:pointer; font-size:18px;">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px; font-size:12px;">
            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed var(--slate-100);">
                <span style="color:var(--slate-600);">Focus Item Search:</span>
                <span class="btn-kbd" style="font-size:11px;">/ or F2</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed var(--slate-100);">
                <span style="color:var(--slate-600);">Navigate Search Suggestions:</span>
                <span class="btn-kbd" style="font-size:11px;">↑ / ↓ Arrow Keys</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed var(--slate-100);">
                <span style="color:var(--slate-600);">Add Selected Item to GRN:</span>
                <span class="btn-kbd" style="font-size:11px;">Enter</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed var(--slate-100);">
                <span style="color:var(--slate-600);">Close Dropdown Suggestions:</span>
                <span class="btn-kbd" style="font-size:11px;">Escape</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed var(--slate-100);">
                <span style="color:var(--slate-600);">Save GRN Form:</span>
                <span class="btn-kbd" style="font-size:11px;">Ctrl + S / Cmd + S</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0;">
                <span style="color:var(--slate-600);">Add Blank Line:</span>
                <span class="btn-kbd" style="font-size:11px;">Alt + N</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary" onclick="closeShortcutsModal()" style="width:100%; margin-top:16px;">
            Got it
        </button>
    </div>
</div>

<script>
    // ═══ BACKEND DATA INJECTION ═══
    var catalogItems = <?= json_encode($data['catalog_items']) ?>;
    var itemSupplierMappings = <?= json_encode($data['item_supplier_mappings'] ?? []) ?>;

    // Fast lookup dictionary for supplier-item relationships: "itemId_supplierId" -> { last_cost_price, is_primary }
    var itemSupplierMap = {};
    if (Array.isArray(itemSupplierMappings)) {
        itemSupplierMappings.forEach(mapping => {
            const key = `${mapping.item_id}_${mapping.supplier_id}`;
            itemSupplierMap[key] = {
                last_cost_price: parseFloat(mapping.last_cost_price ?? 0),
                is_primary: parseInt(mapping.is_primary ?? 0) === 1
            };
        });
    }
    
    // Generate flattened list of searchable elements (main items + variation options)
    var searchableItems = [];
    catalogItems.forEach(item => {
        if (item.variations && item.variations.length > 0) {
            item.variations.forEach(v => {
                searchableItems.push({
                    item_id: item.id,
                    var_opt_id: v.id,
                    display_name: `${item.name} - ${v.variation_name}: ${v.value_name}`,
                    name: item.name,
                    variation_label: `${v.variation_name}: ${v.value_name}`,
                    sku: v.sku || item.item_code || '',
                    vendor_id: item.vendor_id,
                    cost: parseFloat(v.cost && parseFloat(v.cost) > 0 ? v.cost : (item.cost_price && parseFloat(item.cost_price) > 0 ? item.cost_price : (item.cost ?? 0))),
                    price: parseFloat(v.price ?? item.price ?? 0),
                    retail_margin: parseFloat(item.retail_margin ?? 0),
                    wholesale_margin: parseFloat(item.wholesale_margin ?? 0),
                    wholesale_price: parseFloat(item.wholesale_price ?? 0)
                });
            });
        } else {
            searchableItems.push({
                item_id: item.id,
                var_opt_id: 0,
                display_name: item.name,
                name: item.name,
                variation_label: '',
                sku: item.item_code || '',
                vendor_id: item.vendor_id,
                cost: parseFloat(item.cost_price && parseFloat(item.cost_price) > 0 ? item.cost_price : (item.cost ?? 0)),
                price: parseFloat(item.price ?? 0),
                retail_margin: parseFloat(item.retail_margin ?? 0),
                wholesale_margin: parseFloat(item.wholesale_margin ?? 0),
                wholesale_price: parseFloat(item.wholesale_price ?? 0)
            });
        }
    });

    // ═══ STATE & DOM REFERENCES ═══
    const itemSearchInput = document.getElementById('itemSearch');
    const searchResultsList = document.getElementById('searchResults');
    const poBody = document.getElementById('poBody');
    const emptyNotice = document.getElementById('emptyTableNotice');
    let activeSearchIndex = -1;
    let currentSearchResults = [];

    // ═══ SUPPLIER CHANGE HANDLER ═══
    function onVendorChange() {
        const vendorSelect = document.getElementById('vendorSelect');
        const preview = document.getElementById('supplierInfoPreview');
        const countSpan = document.getElementById('supplierProductCount');
        
        if (!vendorSelect.value) {
            preview.innerHTML = '<span style="color: var(--slate-400); font-style: italic;">Select a supplier above to load contact details and preferred product mappings.</span>';
            if (countSpan) countSpan.textContent = '';
            return;
        }

        const opt = vendorSelect.options[vendorSelect.selectedIndex];
        const phone = opt.getAttribute('data-phone') || 'No phone recorded';
        const email = opt.getAttribute('data-email') || 'No email recorded';
        const address = opt.getAttribute('data-address') || 'No address recorded';
        const vendorId = parseInt(vendorSelect.value);

        // Count linked products
        let linkedCount = 0;
        searchableItems.forEach(item => {
            const mapKey = `${item.item_id}_${vendorId}`;
            if (parseInt(item.vendor_id) === vendorId || itemSupplierMap[mapKey]) {
                linkedCount++;
            }
        });

        if (countSpan) {
            countSpan.textContent = `${linkedCount} linked products`;
        }

        preview.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:var(--slate-800); font-size:12px;">${escapeHtml(opt.text)}</strong>
                <span style="font-size:10px; font-weight:700; color:var(--primary); background:var(--primary-light); padding:1px 6px; border-radius:3px;">ID #${vendorId}</span>
            </div>
            <div style="font-size:11px; color:var(--slate-500); display:flex; gap:12px; margin-top:2px;">
                <span><i class="ph ph-phone"></i> ${escapeHtml(phone)}</span>
                <span><i class="ph ph-envelope"></i> ${escapeHtml(email)}</span>
            </div>
            <div style="font-size:10px; color:var(--slate-400); margin-top:2px;">
                <i class="ph ph-map-pin"></i> ${escapeHtml(address)}
            </div>
        `;
    }

    // ═══ SEARCH ENGINE & KEYBOARD NAVIGATION ═══
    function renderSearchDropdown(query) {
        const vendorSelect = document.getElementById('vendorSelect');
        const selectedVendorId = parseInt(vendorSelect.value || 0);

        if (!selectedVendorId) {
            searchResultsList.innerHTML = `
                <li style="padding:10px 14px; color:var(--danger); font-size:12px; font-weight:600; text-align:center;">
                    <i class="ph-bold ph-warning-circle"></i> Please select a Supplier / Vendor above first
                </li>
            `;
            searchResultsList.style.display = 'block';
            currentSearchResults = [];
            activeSearchIndex = -1;
            return;
        }

        if (!query || query.trim().length === 0) {
            searchResultsList.style.display = 'none';
            currentSearchResults = [];
            activeSearchIndex = -1;
            return;
        }

        const tokens = query.toLowerCase().split(/\s+/).filter(Boolean);
        let matches = searchableItems.filter(item => {
            const searchStr = `${item.display_name || ''} ${item.sku || ''}`.toLowerCase();
            return tokens.every(token => searchStr.includes(token));
        });

        if (matches.length === 0) {
            searchResultsList.innerHTML = `
                <li style="padding:12px; color:var(--slate-400); font-size:12px; text-align:center; font-style:italic;">
                    <i class="ph ph-magnifying-glass"></i> No catalog products matching "${escapeHtml(query)}"
                </li>
            `;
            searchResultsList.style.display = 'block';
            currentSearchResults = [];
            activeSearchIndex = -1;
            return;
        }

        // Annotate each match with supplier link status
        matches = matches.map(m => {
            const mapKey = `${m.item_id}_${selectedVendorId}`;
            const supplierInfo = itemSupplierMap[mapKey];
            const isDirectVendor = parseInt(m.vendor_id) === selectedVendorId;
            const isLinked = isDirectVendor || Boolean(supplierInfo);
            const isPreferred = isDirectVendor || (supplierInfo && supplierInfo.is_primary);

            let applicableCost = m.cost;
            if (supplierInfo && supplierInfo.last_cost_price > 0) {
                applicableCost = supplierInfo.last_cost_price;
            }

            return {
                ...m,
                is_linked: isLinked,
                is_preferred: isPreferred,
                supplier_cost: applicableCost
            };
        });

        // Sort: Preferred -> Linked -> Others -> Alphabetical
        matches.sort((a, b) => {
            if (a.is_preferred && !b.is_preferred) return -1;
            if (!a.is_preferred && b.is_preferred) return 1;
            if (a.is_linked && !b.is_linked) return -1;
            if (!a.is_linked && b.is_linked) return 1;
            return a.display_name.localeCompare(b.display_name);
        });

        currentSearchResults = matches;
        activeSearchIndex = 0; // Default highlight first result

        searchResultsList.innerHTML = '';
        matches.forEach((m, idx) => {
            const li = document.createElement('li');
            li.className = 'search-item-row' + (idx === 0 ? ' active' : '');
            li.dataset.index = idx;

            let badgeHtml = '';
            if (m.is_preferred) {
                badgeHtml = '<span class="supplier-badge badge-preferred"><i class="ph-fill ph-star"></i> Preferred</span>';
            } else if (m.is_linked) {
                badgeHtml = '<span class="supplier-badge badge-linked"><i class="ph-bold ph-check"></i> Linked</span>';
            } else {
                badgeHtml = '<span class="supplier-badge badge-new"><i class="ph-bold ph-plus"></i> New Product</span>';
            }

            li.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:2px; flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <span class="search-item-name" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(m.display_name)}</span>
                        ${badgeHtml}
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:2px;">
                        <span class="search-item-sku">SKU: ${escapeHtml(m.sku || 'N/A')}</span>
                        <span class="search-item-cost">Cost: Rs ${m.supplier_cost.toFixed(2)}</span>
                    </div>
                </div>
            `;

            li.addEventListener('mouseenter', () => {
                activeSearchIndex = idx;
                highlightActiveSearchItem();
            });

            li.addEventListener('click', () => {
                selectSearchProduct(m);
            });

            searchResultsList.appendChild(li);
        });

        searchResultsList.style.display = 'block';
    }

    function highlightActiveSearchItem() {
        const rows = searchResultsList.querySelectorAll('.search-item-row');
        rows.forEach((row, idx) => {
            if (idx === activeSearchIndex) {
                row.classList.add('active');
                row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                row.classList.remove('active');
            }
        });
    }

    function selectSearchProduct(product) {
        if (!product) return;

        // Add row with selected product data
        const newRow = addRow({
            itemId: product.item_id,
            varOptId: product.var_opt_id,
            desc: product.display_name,
            qty: 1,
            cost: product.supplier_cost,
            retailMargin: product.retail_margin,
            wholesaleMargin: product.wholesale_margin
        });

        // Clear search and reset
        itemSearchInput.value = '';
        searchResultsList.style.display = 'none';
        currentSearchResults = [];
        activeSearchIndex = -1;

        // Autofocus quantity input of newly added row
        if (newRow) {
            setTimeout(() => {
                const qtyInput = newRow.querySelector('.qty-input');
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            }, 30);
        }
    }

    // ═══ SEARCH INPUT EVENT LISTENERS ═══
    itemSearchInput.addEventListener('input', function() {
        renderSearchDropdown(this.value);
    });

    itemSearchInput.addEventListener('keydown', function(e) {
        if (searchResultsList.style.display === 'block' && currentSearchResults.length > 0) {
            if (e.key === 'ArrowDown' || e.key === 'Down') {
                e.preventDefault();
                activeSearchIndex++;
                if (activeSearchIndex >= currentSearchResults.length) activeSearchIndex = 0;
                highlightActiveSearchItem();
                return;
            } else if (e.key === 'ArrowUp' || e.key === 'Up') {
                e.preventDefault();
                activeSearchIndex--;
                if (activeSearchIndex < 0) activeSearchIndex = currentSearchResults.length - 1;
                highlightActiveSearchItem();
                return;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSearchIndex >= 0 && activeSearchIndex < currentSearchResults.length) {
                    selectSearchProduct(currentSearchResults[activeSearchIndex]);
                } else if (currentSearchResults.length > 0) {
                    selectSearchProduct(currentSearchResults[0]);
                }
                return;
            } else if (e.key === 'Escape') {
                searchResultsList.style.display = 'none';
                return;
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            // If search is empty and user hits enter, add a blank line
            if (!this.value || this.value.trim().length === 0) {
                addRow();
            }
        }
    });

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        if (!itemSearchInput.contains(e.target) && !searchResultsList.contains(e.target)) {
            searchResultsList.style.display = 'none';
        }
    });

    // ═══ TABLE CALCULATIONS & ROW MANAGEMENT ═══
    function calculateRowPrices(row) {
        if (!row) return;
        const qtyInput = row.querySelector('.qty-input');
        const costInput = row.querySelector('.cost-input');
        const retailMarginInput = row.querySelector('.retail-margin-input');
        const wholesaleMarginInput = row.querySelector('.wholesale-margin-input');

        const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
        const cost = parseFloat(costInput ? costInput.value : 0) || 0;
        const retailMargin = parseFloat(retailMarginInput ? retailMarginInput.value : 0) || 0;
        const wholesaleMargin = parseFloat(wholesaleMarginInput ? wholesaleMarginInput.value : 0) || 0;

        const lineTotal = qty * cost;
        const lineTotalDisplay = row.querySelector('.line-total-display');
        if (lineTotalDisplay) {
            lineTotalDisplay.textContent = lineTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        const calculatedRetail = cost + (cost * retailMargin / 100);
        const calculatedWholesale = cost + (cost * wholesaleMargin / 100);

        const retailDisplay = row.querySelector('.display-retail');
        const wholesaleDisplay = row.querySelector('.display-wholesale');
        if (retailDisplay) retailDisplay.textContent = calculatedRetail.toFixed(2);
        if (wholesaleDisplay) wholesaleDisplay.textContent = calculatedWholesale.toFixed(2);

        const sellingPriceInput = row.querySelector('input[name="selling_price[]"]');
        const wholesalePriceInput = row.querySelector('input[name="wholesale_price[]"]');
        if (sellingPriceInput) sellingPriceInput.value = calculatedRetail.toFixed(2);
        if (wholesalePriceInput) wholesalePriceInput.value = calculatedWholesale.toFixed(2);

        recalcGrandTotals();
    }

    function recalcGrandTotals() {
        let grandTotal = 0;
        let totalUnits = 0;
        const rows = poBody.querySelectorAll('tr');
        const lineCount = rows.length;

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0) || 0;
            const cost = parseFloat(row.querySelector('.cost-input')?.value || 0) || 0;
            grandTotal += (qty * cost);
            totalUnits += qty;
        });

        const grandTotalSpan = document.getElementById('grandTotal');
        const subTotalSpan = document.getElementById('subTotalLabel');
        const totalLinesSpan = document.getElementById('totalLinesLabel');

        const formatted = 'Rs ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (grandTotalSpan) grandTotalSpan.textContent = formatted;
        if (subTotalSpan) subTotalSpan.textContent = formatted;
        if (totalLinesSpan) totalLinesSpan.textContent = `${lineCount} lines (${totalUnits} units)`;

        if (emptyNotice) {
            emptyNotice.style.display = lineCount === 0 ? 'block' : 'none';
        }
    }

    function renumberRows() {
        poBody.querySelectorAll('tr').forEach((tr, i) => {
            const cell = tr.querySelector('.line-row-num');
            if (cell) cell.textContent = i + 1;
        });
    }

    function addRow(data = {}) {
        const tr = document.createElement('tr');
        const itemId = data.itemId || '';
        const varOptId = data.varOptId || '0';
        const desc = data.desc || '';
        const qty = data.qty || 1;
        const cost = data.cost !== undefined ? parseFloat(data.cost).toFixed(2) : '0.00';
        const retailMargin = data.retailMargin !== undefined ? parseFloat(data.retailMargin).toFixed(1) : '20.0';
        const wholesaleMargin = data.wholesaleMargin !== undefined ? parseFloat(data.wholesaleMargin).toFixed(1) : '10.0';

        tr.innerHTML = `
            <td class="line-row-num"></td>
            <td style="position: relative;">
                <input type="text" class="form-control-cell row-search-input" value="${escapeHtml(desc)}" placeholder="Search product / SKU..." autocomplete="off" required>
                <div class="row-suggestions-wrapper" style="display:none;"></div>
                <input type="hidden" name="item_selection[]" class="item-selection-hidden" value="${itemId ? `${itemId}|${varOptId}` : ''}" required>
                <input type="hidden" name="desc[]" class="desc-hidden" value="${escapeHtml(desc)}">
            </td>
            <td>
                <input type="number" name="qty[]" step="1" min="1" value="${qty}" class="form-control-cell num qty-input" oninput="calculateRowPrices(this.closest('tr'))" required>
            </td>
            <td>
                <input type="number" name="price[]" step="0.01" min="0" value="${cost}" class="form-control-cell num cost-input" oninput="calculateRowPrices(this.closest('tr'))" required>
            </td>
            <td style="text-align: right; font-family: var(--f-mono); font-weight:700; color: var(--slate-900);">
                <span class="line-total-display">0.00</span>
            </td>
            <td>
                <input type="number" name="retail_margin[]" step="0.1" value="${retailMargin}" class="form-control-cell num retail-margin-input" oninput="calculateRowPrices(this.closest('tr'))" required>
            </td>
            <td>
                <input type="number" name="wholesale_margin[]" step="0.1" value="${wholesaleMargin}" class="form-control-cell num wholesale-margin-input" oninput="calculateRowPrices(this.closest('tr'))" required>
            </td>
            <td style="text-align: right;">
                <span class="price-badge price-retail display-retail">0.00</span>
                <input type="hidden" name="selling_price[]" value="0.00">
            </td>
            <td style="text-align: right;">
                <span class="price-badge price-wholesale display-wholesale">0.00</span>
                <input type="hidden" name="wholesale_price[]" value="0.00">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-delete-row" onclick="removeRow(this)" title="Remove item">
                    <i class="ph-bold ph-trash"></i>
                </button>
            </td>
        `;

        poBody.appendChild(tr);
        bindInlineRowSearch(tr);
        bindRowEnterTraversal(tr);
        renumberRows();
        calculateRowPrices(tr);

        return tr;
    }

    function removeRow(btn) {
        if (!btn) return;
        const tr = btn.closest('tr');
        if (tr) {
            tr.remove();
            renumberRows();
            recalcGrandTotals();
        }
    }

    // Inline row search binding for manual row edits
    function bindInlineRowSearch(row) {
        const input = row.querySelector('.row-search-input');
        const hiddenSel = row.querySelector('.item-selection-hidden');
        const hiddenDesc = row.querySelector('.desc-hidden');
        const wrapper = row.querySelector('.row-suggestions-wrapper');

        if (!input || !wrapper) return;

        input.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const selectedVendorId = parseInt(document.getElementById('vendorSelect').value || 0);

            if (!query || query.length === 0) {
                wrapper.style.display = 'none';
                return;
            }

            const tokens = query.split(/\s+/).filter(Boolean);
            let matches = searchableItems.filter(item => {
                const searchStr = `${item.display_name || ''} ${item.sku || ''}`.toLowerCase();
                return tokens.every(t => searchStr.includes(t));
            });

            if (matches.length === 0) {
                wrapper.innerHTML = '<div style="padding:8px 10px; font-size:11px; color:var(--slate-400); text-align:center;">No matching products</div>';
                wrapper.style.display = 'block';
                return;
            }

            wrapper.innerHTML = '';
            matches.slice(0, 10).forEach(m => {
                const mapKey = `${m.item_id}_${selectedVendorId}`;
                const supplierInfo = itemSupplierMap[mapKey];
                let cost = m.cost;
                if (supplierInfo && supplierInfo.last_cost_price > 0) {
                    cost = supplierInfo.last_cost_price;
                }

                const itemDiv = document.createElement('div');
                itemDiv.className = 'search-item-row';
                itemDiv.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                        <span style="font-weight:600; font-size:12px; color:var(--slate-800);">${escapeHtml(m.display_name)}</span>
                        <span style="font-family:var(--f-mono); font-size:11px; font-weight:700;">Rs ${cost.toFixed(2)}</span>
                    </div>
                `;

                itemDiv.addEventListener('click', () => {
                    input.value = m.display_name;
                    hiddenSel.value = `${m.item_id}|${m.var_opt_id}`;
                    hiddenDesc.value = m.display_name;
                    wrapper.style.display = 'none';

                    row.querySelector('.cost-input').value = cost.toFixed(2);
                    row.querySelector('.retail-margin-input').value = m.retail_margin.toFixed(1);
                    row.querySelector('.wholesale-margin-input').value = m.wholesale_margin.toFixed(1);
                    calculateRowPrices(row);

                    const qtyInput = row.querySelector('.qty-input');
                    if (qtyInput) {
                        qtyInput.focus();
                        qtyInput.select();
                    }
                });

                wrapper.appendChild(itemDiv);
            });

            wrapper.style.display = 'block';
            wrapper.style.position = 'absolute';
            wrapper.style.top = '100%';
            wrapper.style.left = '0';
            wrapper.style.right = '0';
            wrapper.style.background = 'var(--white)';
            wrapper.style.border = '1px solid var(--slate-200)';
            wrapper.style.borderRadius = 'var(--radius-sm)';
            wrapper.style.boxShadow = 'var(--shadow-xl)';
            wrapper.style.zIndex = '999';
            wrapper.style.maxHeight = '200px';
            wrapper.style.overflowY = 'auto';
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !wrapper.contains(e.target)) {
                wrapper.style.display = 'none';
            }
        });
    }

    // Row keyboard traversal (Enter advances to next field or next row)
    function bindRowEnterTraversal(row) {
        const inputs = row.querySelectorAll('input:not([type="hidden"])');
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                        if (inputs[index + 1].select) inputs[index + 1].select();
                    } else {
                        // At the last field in the row: move focus back to search bar or add new line
                        itemSearchInput.focus();
                    }
                }
            });
        });
    }

    // ═══ GLOBAL SHORTCUTS & INITIALIZATION ═══
    function openShortcutsModal() {
        document.getElementById('shortcutsModal').style.display = 'flex';
    }

    function closeShortcutsModal() {
        document.getElementById('shortcutsModal').style.display = 'none';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.addEventListener("DOMContentLoaded", () => {
        // Init vendor preview if vendor is selected
        onVendorChange();

        // Calculate all prefilled rows and bind listeners
        poBody.querySelectorAll('tr').forEach(row => {
            bindInlineRowSearch(row);
            bindRowEnterTraversal(row);
            calculateRowPrices(row);
        });
        renumberRows();
        recalcGrandTotals();

        // Global hotkeys
        document.addEventListener('keydown', function(e) {
            // Focus item search: '/' or 'F2'
            if ((e.key === '/' || e.key === 'F2') && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                itemSearchInput.focus();
                itemSearchInput.select();
                return;
            }

            // Save GRN: Ctrl+S / Cmd+S
            if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
                e.preventDefault();
                const form = document.getElementById('grnForm');
                if (form) {
                    if (form.checkValidity()) {
                        form.submit();
                    } else {
                        form.reportValidity();
                    }
                }
                return;
            }

            // Add Blank Line: Alt+N
            if (e.altKey && (e.key === 'n' || e.key === 'N')) {
                e.preventDefault();
                addRow();
                return;
            }

            // Close modal on Escape
            if (e.key === 'Escape') {
                closeShortcutsModal();
            }
        });
    });
</script>