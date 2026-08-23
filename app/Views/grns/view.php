<?php
// Calculate Totals
$grandTotal = 0;
foreach ($data['items'] as $item) {
    $grandTotal += floatval($item->quantity) * floatval($item->unit_cost);
}

// Fetch Pricing Data
$db = new Database();
foreach ($data['items'] as $grnItem) {
    $grnItem->retail_price = floatval($grnItem->selling_price ?? 0);
    $grnItem->wholesale_price = floatval($grnItem->wholesale_price ?? 0);
    
    // Fallback to live catalog prices for backward compatibility with older GRNs
    if ($grnItem->retail_price <= 0.001 || $grnItem->wholesale_price <= 0.001) {
        if (!empty($grnItem->item_id)) {
            $db->query("SELECT price, wholesale_price FROM items WHERE id = :id");
            $db->bind(':id', $grnItem->item_id);
            $itemPrices = $db->single();
            if ($itemPrices) {
                if ($grnItem->retail_price <= 0.001) {
                    $grnItem->retail_price = floatval($itemPrices->price ?? 0);
                }
                if ($grnItem->wholesale_price <= 0.001) {
                    $grnItem->wholesale_price = floatval($itemPrices->wholesale_price ?? 0);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt Note <?= htmlspecialchars($data['grn']->grn_number) ?> - <?= APP_NAME ?></title>
    <!-- Modern Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
        /* Base Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #F1F5F9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            color: #0F172A;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
            padding: 16px 12px;
        }

        /* Top Action Controls Bar */
        .controls-container {
            max-width: 860px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .controls-title {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .controls-title .badge-live {
            background: #DCFCE7;
            color: #15803D;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .print-controls {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #0F172A;
            color: #FFFFFF;
            border: 1px solid #0F172A;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            font-family: inherit;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-action:hover {
            background-color: #334155;
            transform: translateY(-1px);
        }

        .btn-excel {
            background-color: #107C41;
            border-color: #107C41;
        }
        .btn-excel:hover {
            background-color: #0C5E31;
        }

        .btn-pdf {
            background-color: #DC2626;
            border-color: #DC2626;
        }
        .btn-pdf:hover {
            background-color: #B91C1C;
        }

        /* Screen Wrapper (Desktop & Tablet) */
        .page-wrapper {
            max-width: 860px;
            margin: 0 auto;
            background: #FFFFFF;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 36px 32px;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
        }

        /* Authentic PAID Rubber Stamp Seal */
        .stamp-paid {
            position: absolute;
            top: 28px;
            right: 32px;
            border: 3.5px solid #15803D;
            color: #15803D;
            padding: 2px;
            border-radius: 8px;
            transform: rotate(-14deg);
            opacity: 0.88;
            pointer-events: none;
            z-index: 10;
            box-shadow: 0 0 0 1px #15803D, inset 0 0 0 1px #15803D;
            background-color: rgba(240, 253, 244, 0.7);
            backdrop-filter: blur(2px);
            display: inline-block;
        }

        .stamp-inner {
            border: 1.5px dashed #15803D;
            padding: 4px 18px;
            border-radius: 4px;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 26px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 4px;
            line-height: 1;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }

        .status-partial {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }

        .status-pending {
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            border-bottom: 2px solid #0F172A;
            padding-bottom: 20px;
            gap: 20px;
        }

        .company-info {
            flex: 1;
            min-width: 0;
        }

        .company-logo {
            max-width: 140px;
            max-height: 55px;
            margin-bottom: 8px;
            object-fit: contain;
            display: block;
        }

        .company-name {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0F172A;
        }

        .company-details {
            font-size: 11.5px;
            color: #475569;
            line-height: 1.5;
        }

        .invoice-meta {
            text-align: right;
            min-width: 240px;
        }

        .document-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            color: #0F172A;
        }

        .meta-table {
            width: 100%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .meta-table th, .meta-table td {
            padding: 3px 0;
            font-size: 12px;
        }

        .meta-table th {
            font-weight: 600;
            padding-right: 16px;
            color: #64748B;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
            text-align: left;
        }

        .meta-table td {
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: right;
            color: #0F172A;
        }

        /* Customer Section */
        .customer-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .info-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 14px 16px;
        }

        .customer-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #0F172A;
        }

        .customer-details {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
        }

        /* Items Table Container */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 24px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            background: #FFFFFF;
            min-width: 580px;
        }

        .table-items th, .table-items td {
            padding: 10px 12px;
            font-size: 12.5px;
        }

        .table-items th {
            background: #F8FAFC;
            border-bottom: 1px solid #E2E8F0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
            text-align: left;
            color: #475569;
            white-space: nowrap;
        }

        .table-items td {
            border-bottom: 1px solid #F1F5F9;
            color: #1E293B;
        }

        .table-items tr:last-child td {
            border-bottom: none;
        }

        .table-items tr:nth-child(even) {
            background-color: #FAFAFA;
        }

        .table-items th.num, .table-items td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .table-items th.center, .table-items td.center {
            text-align: center;
        }

        .item-desc {
            font-weight: 600;
            color: #0F172A;
        }

        /* Bottom Section: Payment Info & Totals side-by-side */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
            align-items: start;
        }

        /* Payment & Bank Details Block */
        .payment-info {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 16px;
            font-size: 12px;
        }

        .payment-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 6px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .terms-text {
            line-height: 1.6;
            color: #334155;
        }

        .terms-text strong {
            color: #0F172A;
            font-weight: 700;
        }

        .bank-badge {
            display: inline-block;
            background: #E2E8F0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 11.5px;
            font-weight: 600;
            color: #0F172A;
        }

        /* Totals Section */
        .summary-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .table-totals {
            width: 100%;
            border-collapse: collapse;
        }

        .table-totals th, .table-totals td {
            padding: 6px 4px;
            font-size: 12.5px;
            border-bottom: 1px solid #F1F5F9;
        }

        .table-totals th {
            text-align: right;
            font-weight: 600;
            color: #64748B;
            width: 55%;
        }

        .table-totals td {
            text-align: right;
            font-weight: 600;
            width: 45%;
            font-variant-numeric: tabular-nums;
            color: #0F172A;
        }

        .table-totals tr.grand-total-row th,
        .table-totals tr.grand-total-row td {
            border-top: 2px solid #0F172A;
            border-bottom: 2px solid #0F172A;
            font-weight: 800;
            font-size: 15px;
            color: #0F172A;
            padding: 10px 4px;
        }

        .table-totals tr.due-row th,
        .table-totals tr.due-row td {
            border-bottom: 2px solid #0F172A;
            font-weight: 800;
            font-size: 15px;
            color: #DC2626;
            padding: 10px 4px;
        }

        /* Signatures Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 220px;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #94A3B8;
            margin-bottom: 6px;
            height: 36px;
        }

        .signature-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
        }

        /* Footer */
        .document-footer {
            margin-top: 24px;
            border-top: 1px solid #E2E8F0;
            padding-top: 12px;
            font-size: 11px;
            color: #94A3B8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* ============================================================
           RESPONSIVE MOBILE VIEW (Smartphones & Small Screens <= 680px)
           ============================================================ */
        @media screen and (max-width: 680px) {
            body {
                padding: 10px 6px;
                background-color: #F8FAFC;
            }

            .controls-container {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 12px;
            }

            .print-controls {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 6px;
            }

            .btn-action {
                justify-content: center;
                padding: 8px 6px;
                font-size: 11px;
            }

            .page-wrapper {
                padding: 18px 14px;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            }

            .stamp-paid {
                top: 14px;
                right: 14px;
                border-width: 2.5px;
                transform: rotate(-10deg);
            }

            .stamp-inner {
                font-size: 18px;
                padding: 3px 10px;
                letter-spacing: 2px;
            }

            .invoice-header {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
                padding-bottom: 16px;
            }

            .company-info {
                width: 100%;
                text-align: left;
            }

            .invoice-meta {
                width: 100%;
                min-width: 0;
                text-align: left;
                background: #F8FAFC;
                border: 1px solid #E2E8F0;
                border-radius: 10px;
                padding: 12px;
            }

            .document-title {
                font-size: 20px;
                margin-bottom: 6px;
            }

            .meta-table {
                width: 100%;
            }

            .meta-table th {
                font-size: 11px;
                padding-right: 8px;
            }

            .meta-table td {
                font-size: 11.5px;
            }

            .customer-section {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 16px;
            }

            .table-responsive {
                margin-bottom: 16px;
            }

            .bottom-section {
                grid-template-columns: 1fr;
                gap: 16px;
                margin-bottom: 20px;
            }

            .signature-section {
                flex-direction: column;
                gap: 24px;
                margin-top: 20px;
            }

            .signature-box {
                width: 100%;
            }

            .document-footer {
                flex-direction: column;
                text-align: center;
                gap: 4px;
            }
        }

        /* ============================================================
           PRINT SPECIFIC STYLES (Clean A4 Paper Formatting)
           ============================================================ */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                background: #FFFFFF;
                margin: 0;
                padding: 0;
                font-size: 9pt;
            }

            .controls-container {
                display: none !important;
            }

            .page-wrapper {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
                border-radius: 0;
                width: 100%;
                max-width: none;
                display: block;
            }

            .stamp-paid {
                top: 20px;
                right: 30px;
                border-color: #000 !important;
                color: #000 !important;
                box-shadow: none !important;
                background: none !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .stamp-inner {
                border-color: #000 !important;
            }

            .invoice-header {
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 15px;
                display: flex !important;
                flex-direction: row !important;
            }

            .company-info {
                width: 55% !important;
            }

            .invoice-meta {
                width: 40% !important;
                background: none !important;
                border: none !important;
                padding: 0 !important;
                text-align: right !important;
            }

            .customer-section {
                display: flex !important;
                justify-content: space-between !important;
                grid-template-columns: none !important;
                margin-bottom: 15px;
            }

            .info-card {
                background: none !important;
                border: none !important;
                padding: 0 !important;
                width: 48% !important;
            }

            .table-responsive {
                border: none !important;
                overflow: visible !important;
                margin-bottom: 15px;
            }

            .table-items {
                min-width: 0 !important;
            }

            .table-items th {
                background: none !important;
                border-top: 2px solid #000;
                border-bottom: 2px solid #000;
                color: #000;
                padding: 4px 6px;
            }

            .table-items td {
                padding: 4px 6px;
                border-bottom: 1px solid #eaeaea;
            }

            .table-items tr:last-child td {
                border-bottom: 1px solid #000;
            }

            .bottom-section {
                display: flex !important;
                grid-template-columns: none !important;
                justify-content: space-between !important;
                gap: 20px;
                margin-bottom: 15px;
            }

            .payment-info {
                flex: 1 !important;
                border: 1px solid #000 !important;
                background: #FAFAFA !important;
                padding: 8px !important;
            }

            .summary-card {
                width: 280px !important;
                border: none !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .table-totals tr.grand-total-row th,
            .table-totals tr.grand-total-row td {
                border-top: 2px solid #000 !important;
                border-bottom: 2px solid #000 !important;
            }

            .table-totals tr.due-row th,
            .table-totals tr.due-row td {
                border-bottom: 2px solid #000 !important;
                color: #000 !important;
            }

            .signature-section {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                margin-top: 25px;
            }

            .signature-box {
                width: 200px !important;
            }

            .signature-line {
                border-bottom: 1px solid #000 !important;
            }

            .document-footer {
                margin-top: 20px;
                border-top: 1px solid #CCC;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Controls -->
    <div class="controls-container">
        <div class="controls-title">
            <i class="ph ph-file-text" style="font-size: 20px;"></i>
            GOODS RECEIPT NOTE
            <?php if($data['grn']->is_approved): ?>
            <span class="badge-live"><i class="ph-fill ph-check-circle"></i> APPROVED</span>
            <?php else: ?>
            <span class="status-badge status-pending">PENDING</span>
            <?php endif; ?>
        </div>
        <div class="print-controls">
            <a href="<?= APP_URL ?>/grn" class="btn-action" style="background:#F1F5F9; color:#0F172A; border-color:#CBD5E1;">
                <i class="ph ph-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn-action">
                <i class="ph ph-printer"></i> Print Document
            </button>
        </div>
    </div>

    <!-- Main Document Wrapper -->
    <div class="page-wrapper">
        <?php if($data['grn']->is_approved): ?>
        <div class="stamp-paid">
            <div class="stamp-inner">APPROVED</div>
        </div>
        <?php endif; ?>

        <div class="main-content">
            <!-- Header Section -->
            <div class="invoice-header">
                <div class="company-info">
                    <?php if(!empty($data['company']->logo_path)): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($data['company']->logo_path) ?>" alt="Company Logo" class="company-logo">
                    <?php else: ?>
                        <div class="company-name"><?= htmlspecialchars($data['company']->company_name) ?></div>
                    <?php endif; ?>
                    <div class="company-details">
                        <?php if(!empty($data['company']->address)): ?>
                            <?= nl2br(htmlspecialchars($data['company']->address)) ?><br>
                        <?php endif; ?>
                        <?php if(!empty($data['company']->phone)): ?>
                            <?= htmlspecialchars($data['company']->phone) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="invoice-meta">
                    <div class="document-title">GOODS RECEIPT</div>
                    <table class="meta-table">
                        <tr>
                            <th>GRN #</th>
                            <td><?= htmlspecialchars($data['grn']->grn_number) ?></td>
                        </tr>
                        <tr>
                            <th>Received Date</th>
                            <td><?= date('F j, Y', strtotime($data['grn']->grn_date)) ?></td>
                        </tr>
                        <?php if(!empty($data['grn']->receipt_number)): ?>
                        <tr>
                            <th>Supplier Invoice</th>
                            <td><?= htmlspecialchars($data['grn']->receipt_number) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty($data['grn']->po_number)): ?>
                        <tr>
                            <th>PO Reference</th>
                            <td><?= htmlspecialchars($data['grn']->po_number) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Parties Section -->
            <div class="customer-section">
                <div class="info-card">
                    <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Supplier (Received From)</div>
                    <div class="customer-name"><?= htmlspecialchars($data['grn']->vendor_name) ?></div>
                    <div class="customer-details">
                        <?php if(!empty($data['grn']->address)): ?>
                            <?= nl2br(htmlspecialchars($data['grn']->address)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-card">
                    <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Received At</div>
                    <div class="customer-name"><?= htmlspecialchars($data['company']->company_name) ?></div>
                    <div class="customer-details">
                        <?php if(!empty($data['company']->address)): ?>
                            <?= nl2br(htmlspecialchars($data['company']->address)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="table-items">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="center">#</th>
                            <th>Item Description</th>
                            <th class="num">Qty</th>
                            <th class="num">Unit Cost</th>
                            <th class="num" style="color: #1E40AF;">Retail</th>
                            <th class="num" style="color: #6B21A8;">Wholesale</th>
                            <th class="num">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNum = 1; foreach($data['items'] as $item): ?>
                        <?php $lineTotal = floatval($item->quantity) * floatval($item->unit_cost); ?>
                        <tr>
                            <td class="center" style="color: #94A3B8;"><?= $rowNum++ ?></td>
                            <td class="item-desc"><?= htmlspecialchars($item->description) ?></td>
                            <td class="num" style="font-weight: 700;"><?= number_format($item->quantity, 0) ?></td>
                            <td class="num"><?= number_format($item->unit_cost, 2) ?></td>
                            <td class="num" style="color: #1E40AF;"><?= number_format($item->retail_price, 2) ?></td>
                            <td class="num" style="color: #6B21A8; font-weight: 600;"><?= number_format($item->wholesale_price, 2) ?></td>
                            <td class="num" style="font-weight: 700; color: #0F172A;"><?= number_format($lineTotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bottom Section -->
            <div class="bottom-section">
                <!-- Notes -->
                <div class="payment-info" style="background-color: #FFFFFF;">
                    <?php if(!empty($data['grn']->notes)): ?>
                    <div class="payment-title"><i class="ph-fill ph-info"></i> Inspection Notes</div>
                    <div class="terms-text" style="white-space: pre-wrap; font-size: 11.5px;"><?= htmlspecialchars($data['grn']->notes) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Totals -->
                <div class="summary-card">
                    <table class="table-totals">
                        <tr class="grand-total-row">
                            <th>Grand Total (Cost)</th>
                            <td><?= number_format($grandTotal, 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line" style="display: flex; align-items: flex-end; justify-content: center;">
                        <?php if(!empty($data['grn']->creator_signature)): ?>
                            <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($data['grn']->creator_signature) ?>" style="max-height: 40px; max-width: 100%; object-fit: contain;">
                        <?php endif; ?>
                    </div>
                    <div class="signature-label">Received &amp; Verified By</div>
                    <div style="font-size: 10px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($data['grn']->creator_name) ?></div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line" style="display: flex; align-items: flex-end; justify-content: center;">
                        <?php if($data['grn']->is_approved): ?>
                            <span style="color: #15803D; border: 1.5px dashed #15803D; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 10px; text-transform: uppercase;">APPROVED</span>
                        <?php endif; ?>
                    </div>
                    <div class="signature-label">Approved By</div>
                    <?php if($data['grn']->is_approved): ?>
                    <div style="font-size: 10px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($data['grn']->approver_name) ?> (<?= date('M d, Y', strtotime($data['grn']->approved_at)) ?>)</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="document-footer">
            <div>Printed on <?= date('Y-m-d H:i') ?></div>
            <div>Powered by <?= APP_NAME ?> ERP</div>
        </div>
    </div>

</body>
</html>