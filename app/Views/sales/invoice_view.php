<?php
$db = new Database();

$totalOutstanding = 0;
if (!empty($data['invoice']->customer_id)) {
    // Failsafe: Fetch the customer's true total outstanding balance for the "Previous Balance" calculation
    $db->query("
        SELECT 
            COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) as total_billed
        FROM invoices WHERE customer_id = :id AND status != 'Voided'
    ");
    $db->bind(':id', $data['invoice']->customer_id);
    $billed = $db->single()->total_billed ?? 0;

    $db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE customer_id = :id");
    $db->bind(':id', $data['invoice']->customer_id);
    $paid = $db->single()->total_paid ?? 0;

    $db->query("SELECT COALESCE(SUM(total_amount), 0) as total_credited FROM credit_notes WHERE customer_id = :id");
    $db->bind(':id', $data['invoice']->customer_id);
    $credited = $db->single()->total_credited ?? 0;

    $totalOutstanding = $billed - $paid - $credited;
}

// Fetch sales representative information
$repName = '';
$repPhone = '';
if (!empty($data['invoice']->rep_route_id)) {
    $db->query("
        SELECT CONCAT(e.first_name, ' ', e.last_name) as rep_name, e.phone as rep_phone 
        FROM employees e 
        JOIN users u ON u.employee_id = e.id 
        JOIN rep_daily_routes r ON r.user_id = u.id 
        WHERE r.id = :route_id 
        LIMIT 1
    ");
    $db->bind(':route_id', $data['invoice']->rep_route_id);
    $repRow = $db->single();
    if ($repRow) {
        $repName = $repRow->rep_name;
        $repPhone = $repRow->rep_phone;
    }
}

// Fetch payment term information
$paymentTermName = '';
if (!empty($data['invoice']->payment_term_id)) {
    $db->query("SELECT name FROM payment_terms WHERE id = :id LIMIT 1");
    $db->bind(':id', $data['invoice']->payment_term_id);
    $termRow = $db->single();
    if ($termRow) {
        $paymentTermName = $termRow->name;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($data['invoice']->invoice_number) ?> - <?= APP_NAME ?></title>
    <!-- Modern Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
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
        }

        .main-content {
            flex: 1;
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

        .section-heading {
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

    <!-- Screen Action Controls -->
    <?php if (!isset($_GET['hide_buttons']) || $_GET['hide_buttons'] !== '1'): ?>
    <div class="controls-container">
        <div class="controls-title">
            <span>Official Invoice View</span>
            <?php if(!empty($data['is_offline_verified'])): ?>
                <span class="badge-live"><i class="ph ph-check-circle"></i> Offline Verified</span>
            <?php endif; ?>
        </div>
        <div class="print-controls">
            <button onclick="exportToExcel()" class="btn-action btn-excel"><i class="ph ph-file-xls"></i> Excel</button>
            <?php if(!empty($data['invoice']->id) && is_numeric($data['invoice']->id)): ?>
                <a href="<?= APP_URL ?>/sales/download_pdf/<?= $data['invoice']->id ?>" class="btn-action btn-pdf"><i class="ph ph-file-pdf"></i> PDF</a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn-action"><i class="ph ph-printer"></i> Print</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-wrapper">
        <div class="main-content">
            <!-- Header Section -->
            <div class="invoice-header">
                <div class="company-info">
                    <?php if(!empty($data['company']->logo_path)): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($data['company']->logo_path) ?>" class="company-logo" alt="Company Logo">
                    <?php else: ?>
                        <div class="company-name"><?= htmlspecialchars($data['company']->company_name ?? 'CURTISS ERP') ?></div>
                    <?php endif; ?>
                    
                    <div class="company-details">
                        <?php if(!empty($data['company']->address)) echo nl2br(htmlspecialchars($data['company']->address)) . '<br>'; ?>
                        <strong>Phone:</strong> 037 222 8025 &nbsp;|&nbsp; <strong>WhatsApp / Mobile:</strong> 077 362 3623<br>
                        <strong>Website:</strong> <a href="http://www.falconstationery.com" target="_blank" style="color: #333; text-decoration: none;">www.falconstationery.com</a> &nbsp;|&nbsp; <strong>Email:</strong> <a href="mailto:falconstationary@gmail.com" style="color: #333; text-decoration: none;">falconstationary@gmail.com</a><br>
                        <?php if(!empty($data['company']->tax_number)) echo 'VAT/Tax Reg: ' . htmlspecialchars($data['company']->tax_number); ?>
                    </div>
                </div>

                <div class="invoice-meta">
                    <div class="document-title">Invoice</div>
                    <table class="meta-table">
                        <tr>
                            <th>Invoice No:</th>
                            <td><?= htmlspecialchars($data['invoice']->invoice_number) ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?= date('d-M-Y', strtotime($data['invoice']->invoice_date ?? date('Y-m-d'))) ?></td>
                        </tr>
                        <?php if(!empty($paymentTermName)): ?>
                        <tr>
                            <th>Payment Term:</th>
                            <td><?= htmlspecialchars($paymentTermName) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty($data['invoice']->payment_method)): ?>
                        <tr>
                            <th>Payment:</th>
                            <td><?= htmlspecialchars($data['invoice']->payment_method) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty($data['invoice']->cheque_date)): ?>
                        <tr>
                            <th>Cheque Date:</th>
                            <td><?= date('d-M-Y', strtotime($data['invoice']->cheque_date)) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty($repName)): ?>
                        <tr>
                            <th>Sales Rep:</th>
                            <td><?= htmlspecialchars($repName) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(!empty($repPhone)): ?>
                        <tr>
                            <th>Rep Contact:</th>
                            <td><?= htmlspecialchars($repPhone) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Customer & Info Section -->
            <div class="customer-section">
                <div class="info-card">
                    <div class="section-heading"><i class="ph ph-user"></i> Bill To</div>
                    <div class="customer-name"><?= htmlspecialchars($data['invoice']->customer_name ?? 'Customer') ?></div>
                    <div class="customer-details">
                        <?php if(!empty($data['invoice']->address)) echo nl2br(htmlspecialchars($data['invoice']->address)) . '<br>'; ?>
                        <?php if(!empty($data['invoice']->phone)) echo '<strong>Tel:</strong> ' . htmlspecialchars($data['invoice']->phone); ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="section-heading"><i class="ph ph-info"></i> Invoice Details</div>
                    <div class="customer-details">
                        <strong>Status:</strong> <?= htmlspecialchars($data['invoice']->status ?? 'Issued') ?><br>
                        <strong>Issue Type:</strong> <?= !empty($data['is_offline_verified']) ? 'Digital Mobile Terminal' : 'Standard Web Terminal' ?><br>
                        <strong>Created:</strong> <?= date('d-M-Y H:i', strtotime($data['invoice']->created_at ?? date('Y-m-d H:i'))) ?>
                    </div>
                </div>
            </div>

            <!-- Items Table (Responsive) -->
            <div class="table-responsive">
                <table class="table-items">
                    <thead>
                        <tr>
                            <th class="center" style="width: 6%;">#</th>
                            <th style="width: 44%;">Description</th>
                            <th class="num" style="width: 12%;">Qty</th>
                            <th class="num" style="width: 14%;">Price</th>
                            <th class="num" style="width: 10%;">Disc.</th>
                            <th class="num" style="width: 14%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNum = 1; foreach($data['items'] as $item): ?>
                        <tr>
                            <td class="center"><?= $rowNum++ ?></td>
                            <td class="item-desc"><?= htmlspecialchars($item->description) ?></td>
                            <td class="num"><?= number_format($item->quantity, 0) ?></td>
                            <td class="num"><?= number_format($item->unit_price, 2) ?></td>
                            <td class="num">
                                <?php if(!empty($item->discount_value) && $item->discount_value > 0): ?>
                                    <?= (!empty($item->discount_type) && $item->discount_type == '%') ? $item->discount_value . '%' : number_format($item->discount_value, 2) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="num" style="font-weight: 600;"><?= number_format($item->total, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
                // Calculations
                $subTotal = floatval($data['invoice']->total_amount ?? 0);
                $globalDiscountAmount = 0;
                $globalDiscVal = floatval($data['invoice']->global_discount_val ?? 0);
                
                if($globalDiscVal > 0) {
                    if (!empty($data['invoice']->global_discount_type) && $data['invoice']->global_discount_type == '%') {
                        $globalDiscountAmount = $subTotal * ($globalDiscVal / 100);
                    } else {
                        $globalDiscountAmount = $globalDiscVal;
                    }
                }
                
                $netSubTotal = $subTotal - $globalDiscountAmount;
                if ($netSubTotal < 0) $netSubTotal = 0;

                $taxAmount = floatval($data['invoice']->tax_amount ?? 0);
                $thisInvoiceGrandTotal = $netSubTotal + $taxAmount;

                $previousBalance = $totalOutstanding;
                if (in_array($data['invoice']->status, ['Unpaid', 'Draft'])) {
                    $previousBalance -= $thisInvoiceGrandTotal;
                }
                $amountDueNow = $previousBalance + $thisInvoiceGrandTotal;
                $showUnpaid = in_array($data['invoice']->status, ['Unpaid', 'Draft']) && ($previousBalance > 0.01 || $previousBalance < -0.01);
            ?>

            <!-- Bottom Section: Payment Info & Totals -->
            <div class="bottom-section">
                <!-- Dedicated Bank & Payment Details Block -->
                <div class="payment-info">
                    <div class="section-heading"><i class="ph ph-bank"></i> Payment & Terms</div>
                    <div class="terms-text">
                        <strong>Cheques:</strong> To be drawn in favour of "Falcon Stationary PVT (LTD)".<br><br>
                        <strong>Bank Deposits:</strong><br>
                        • <span class="bank-badge">1122015325</span> - Commercial Bank<br>
                        • <span class="bank-badge">101100120033403</span> - Peoples Bank<br><br>
                        <strong>Returns:</strong> Market returns are allowed within a 3 months period only.
                    </div>
                </div>

                <!-- Summary / Totals -->
                <div class="summary-card">
                    <table class="table-totals">
                        <?php if($globalDiscVal > 0): ?>
                            <tr>
                                <th>Subtotal:</th>
                                <td><?= number_format($subTotal, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Discount (<?= (!empty($data['invoice']->global_discount_type) && $data['invoice']->global_discount_type == '%') ? number_format($globalDiscVal, 2) . '%' : 'Flat' ?>):</th>
                                <td style="color: #DC2626;">(<?= number_format($globalDiscountAmount, 2) ?>)</td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th>Net Subtotal:</th>
                            <td><?= number_format($netSubTotal, 2) ?></td>
                        </tr>
                        
                        <?php if($taxAmount > 0): ?>
                        <tr>
                            <th>Tax (<?= htmlspecialchars($data['invoice']->tax_name ?? 'Tax') ?> <?= $data['invoice']->rate_percentage ?? '' ?>%):</th>
                            <td><?= number_format($taxAmount, 2) ?></td>
                        </tr>
                        <?php endif; ?>

                        <tr class="grand-total-row">
                            <th>Current Total:</th>
                            <td><?= number_format($thisInvoiceGrandTotal, 2) ?></td>
                        </tr>

                        <?php if($showUnpaid): ?>
                            <tr>
                                <th>Previous Balance:</th>
                                <td><?= number_format($previousBalance, 2) ?></td>
                            </tr>
                            <tr class="due-row">
                                <th>Total Due Now:</th>
                                <td><?= number_format($amountDueNow, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Customer Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Authorized Signatory</div>
                </div>
            </div>
        </div>

        <!-- System Footer -->
        <div class="document-footer">
            <div>Generated by <?= APP_NAME ?> on <?= date('d-M-Y H:i') ?></div>
            <div>Official Digital Invoice</div>
        </div>
    </div>

    <!-- Excel Export Script -->
    <script>
        function exportToExcel() {
            let html = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="utf-8">
                    <style>
                        table { border-collapse: collapse; width: 100%; font-family: 'Segoe UI', Arial, sans-serif; font-size: 10pt; }
                        td, th { vertical-align: top; }
                        .mso-num { mso-number-format:"\\#\\,\\#\\#0\\.00"; text-align: right; }
                        .mso-int { mso-number-format:"0"; text-align: right; }
                    </style>
                </head>
                <body>
                    <table>
                        <!-- Header -->
                        <tr>
                            <td colspan="4" style="font-size: 16pt; font-weight: bold; text-transform: uppercase;">
                                <?= htmlspecialchars($data['company']->company_name ?? 'CURTISS ERP') ?>
                            </td>
                            <td colspan="2" style="font-size: 24pt; font-weight: bold; text-align: right; text-transform: uppercase;">
                                INVOICE
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="color: #444; border-bottom: 2px solid #000; padding-bottom: 10px;">
                                <?php if(!empty($data['company']->address)) echo nl2br(htmlspecialchars($data['company']->address)) . '<br>'; ?>
                                <strong>Phone:</strong> 037 222 8025 &nbsp;|&nbsp; <strong>WhatsApp / Mobile:</strong> 077 362 3623<br>
                                <strong>Website:</strong> www.falconstationery.com &nbsp;|&nbsp; <strong>Email:</strong> falconstationary@gmail.com<br>
                                <?php if(!empty($data['company']->tax_number)) echo 'VAT/Tax Reg: ' . htmlspecialchars($data['company']->tax_number); ?>
                            </td>
                            <td colspan="2" style="text-align: right; border-bottom: 2px solid #000; padding-bottom: 10px;">
                                <strong>Invoice No:</strong> <?= htmlspecialchars($data['invoice']->invoice_number) ?><br>
                                <strong>Date:</strong> <?= date('d-M-Y', strtotime($data['invoice']->invoice_date ?? date('Y-m-d'))) ?><br>
                                <?php if(!empty($data['invoice']->cheque_date)): ?>
                                <strong>Cheque Date:</strong> <?= date('d-M-Y', strtotime($data['invoice']->cheque_date)) ?><br>
                                <?php endif; ?>
                                <?php if(!empty($repName)): ?>
                                <strong>Sales Rep:</strong> <?= htmlspecialchars($repName) ?><br>
                                <?php endif; ?>
                                <?php if(!empty($repPhone)): ?>
                                <strong>Rep Contact:</strong> <?= htmlspecialchars($repPhone) ?><br>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><td colspan="6"></td></tr>

                        <!-- Customer -->
                        <tr>
                            <td colspan="6" style="font-size: 10pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000;">Bill To</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="padding-top: 5px;">
                                <strong style="font-size: 11pt;"><?= htmlspecialchars($data['invoice']->customer_name ?? 'Customer') ?></strong><br>
                                <?php if(!empty($data['invoice']->address)) echo nl2br(htmlspecialchars($data['invoice']->address)) . '<br>'; ?>
                                <?php if(!empty($data['invoice']->phone)) echo 'Tel: ' . htmlspecialchars($data['invoice']->phone); ?>
                            </td>
                        </tr>
                        <tr><td colspan="6"></td></tr>

                        <!-- Items Header -->
                        <tr>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; text-transform: uppercase; text-align: center;">#</th>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; text-transform: uppercase; text-align: left;">Description</th>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; text-transform: uppercase; text-align: right;">Qty</th>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; text-transform: uppercase; text-align: right;">Price</th>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; text-transform: uppercase; text-align: right;">Disc.</th>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; text-transform: uppercase; text-align: right;">Total</th>
                        </tr>
                        
                        <!-- Items Data -->
                        <?php 
                        $rowNum = 1; 
                        $totalItems = count($data['items']); 
                        foreach($data['items'] as $index => $item): 
                            $isLast = ($index === $totalItems - 1);
                            $bottomBorder = $isLast ? 'border-bottom: 2px solid #000;' : 'border-bottom: 1px solid #eaeaea;';
                        ?>
                        <tr>
                            <td style="text-align: center; padding: 6px; <?= $bottomBorder ?>"><?= $rowNum++ ?></td>
                            <td style="padding: 6px; <?= $bottomBorder ?>"><?= htmlspecialchars($item->description) ?></td>
                            <td class="mso-int" style="padding: 6px; <?= $bottomBorder ?>"><?= $item->quantity ?></td>
                            <td class="mso-num" style="padding: 6px; <?= $bottomBorder ?>"><?= $item->unit_price ?></td>
                            <td class="mso-num" style="padding: 6px; <?= $bottomBorder ?>">
                                <?php if(!empty($item->discount_value) && $item->discount_value > 0): ?>
                                    <?= (!empty($item->discount_type) && $item->discount_type == '%') ? $item->discount_value . '%' : $item->discount_value ?>
                                <?php else: ?>
                                    0
                                <?php endif; ?>
                            </td>
                            <td class="mso-num" style="padding: 6px; <?= $bottomBorder ?>"><?= $item->total ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr><td colspan="6"></td></tr>

                        <?php
                            $totalsRows = 3; 
                            if($globalDiscVal > 0) $totalsRows++;
                            if($taxAmount > 0) $totalsRows++;
                            if($showUnpaid) $totalsRows++;
                        ?>

                        <!-- Summary & Payment Box -->
                        <tr>
                            <td colspan="4" rowspan="<?= $totalsRows ?>" style="border: 1px solid #000; padding: 10px; background-color: #fafafa;">
                                <strong style="font-size: 11pt; border-bottom: 1px solid #ccc;">Payment & Terms</strong><br><br>
                                <strong>Cheques:</strong> To be drawn in favour of "Falcon Stationary PVT (LTD)".<br><br>
                                <strong>Bank Deposits:</strong><br>
                                • 1122015325 - Commercial Bank<br>
                                • 101100120033403 - Peoples Bank<br><br>
                                <strong>Returns:</strong> Market returns are allowed within a three months period only.
                            </td>
                            <th style="text-align: right; padding: 4px;">Subtotal:</th>
                            <td class="mso-num" style="padding: 4px;"><?= $subTotal ?></td>
                        </tr>

                        <?php if($globalDiscVal > 0): ?>
                        <tr>
                            <th style="text-align: right; padding: 4px;">Discount (<?= (!empty($data['invoice']->global_discount_type) && $data['invoice']->global_discount_type == '%') ? number_format($globalDiscVal, 2) . '%' : 'Flat' ?>):</th>
                            <td class="mso-num" style="padding: 4px; color: #cc0000;">-<?= $globalDiscountAmount ?></td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <th style="text-align: right; padding: 4px;">Net Subtotal:</th>
                            <td class="mso-num" style="padding: 4px;"><?= $netSubTotal ?></td>
                        </tr>

                        <?php if($taxAmount > 0): ?>
                        <tr>
                            <th style="text-align: right; padding: 4px;">Tax (<?= htmlspecialchars($data['invoice']->tax_name ?? 'Tax') ?> <?= $data['invoice']->rate_percentage ?? '' ?>%):</th>
                            <td class="mso-num" style="padding: 4px;"><?= $taxAmount ?></td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <th style="border-top: 2px solid #000; border-bottom: 2px solid #000; text-align: right; padding: 6px;">Current Invoice Total:</th>
                            <td class="mso-num" style="border-top: 2px solid #000; border-bottom: 2px solid #000; font-weight: bold; padding: 6px;"><?= $thisInvoiceGrandTotal ?></td>
                        </tr>

                        <?php if($showUnpaid): ?>
                            <tr>
                                <th style="text-align: right; padding: 4px;">Previous Balance:</th>
                                <td class="mso-num" style="padding: 4px;"><?= $previousBalance ?></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <th style="border-bottom: 2px solid #000; text-align: right; padding: 6px;">Total Amount Due:</th>
                                <td class="mso-num" style="border-bottom: 2px solid #000; font-weight: bold; padding: 6px;"><?= $amountDueNow ?></td>
                            </tr>
                        <?php endif; ?>

                    </table>
                </body>
                </html>
            `;

            let blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Invoice_<?= htmlspecialchars($data['invoice']->invoice_number) ?>.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>