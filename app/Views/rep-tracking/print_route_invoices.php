<?php
$company = $data['company'];
$route = $data['route'];
$delivery = $data['delivery'];
$bills_on_route = $data['bills_on_route'];
$credit_collections = $data['credit_collections'];

// Calculate Totals for Bills on Route
$total_sales = 0;
$total_cash_sales = 0;
$total_chq_sales = 0;
$total_credit_sales = 0;
foreach ($bills_on_route as $b) {
    $total_sales += $b['sales_amount'];
    $total_cash_sales += $b['cash'];
    $total_chq_sales += $b['chq'];
    $total_credit_sales += $b['credit'];
}

// Calculate Totals for Credit Collections
$total_credit_val = 0;
$total_cash_coll = 0;
$total_chq_coll = 0;
foreach ($credit_collections as $cc) {
    $total_credit_val += $cc['credit_bill_value'];
    $total_cash_coll += $cc['cash'];
    $total_chq_coll += $cc['chq'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Collection Summary - <?= htmlspecialchars($route->route_name) ?> - <?= APP_NAME ?></title>
    <!-- Google Fonts Inter for premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Modern Reset and Aesthetics */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.4;
            padding: 20px;
        }

        /* Screen Wrapper */
        .report-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 20mm 15mm;
            position: relative;
            min-height: 297mm;
            display: flex;
            flex-direction: column;
        }

        /* Control Panel (Screen Only) */
        .print-controls {
            max-width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-action {
            background-color: #0f172a;
            color: #ffffff;
            border: 1px solid #1e293b;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background-color: #1e293b;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #0f172a;
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
        }

        /* Report Header Styling */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }

        .company-info {
            width: 60%;
        }

        .company-name {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .company-details {
            font-size: 8.5pt;
            color: #64748b;
        }

        .report-title-section {
            width: 40%;
            text-align: right;
        }

        .report-title {
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .report-timestamp {
            font-size: 8.5pt;
            color: #64748b;
            font-weight: 500;
        }

        /* Metadata Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 25px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-size: 9.5pt;
            font-weight: 600;
            color: #0f172a;
        }

        /* Section Layouts */
        .section-container {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 10.5pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Premium Table Design */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table th, .data-table td {
            padding: 6px 8px;
            font-size: 8.5pt;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .data-table th {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 7.5pt;
            background-color: #f8fafc;
            color: #475569;
            border-bottom: 2px solid #cbd5e1;
        }

        .data-table td {
            color: #334155;
            font-weight: 500;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .bold-total-row {
            background-color: #f8fafc;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }

        .bold-total-row td {
            font-weight: 800 !important;
            color: #0f172a !important;
            font-size: 9pt;
        }

        /* Side by Side Bottom Section */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .bottom-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            background-color: #ffffff;
        }

        .bottom-card-title {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Denomination Layout */
        .denom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .denom-table td {
            padding: 4px 6px;
            font-size: 8.5pt;
            border-bottom: 1px dashed #e2e8f0;
        }

        .denom-table tr:last-child td {
            border-bottom: none;
            font-weight: 800;
            color: #0f172a;
            border-top: 1.5px solid #0f172a;
            padding-top: 8px;
        }

        .denom-input-line {
            width: 65px;
            border: none;
            border-bottom: 1px dotted #000;
            text-align: center;
            font-weight: bold;
            font-family: inherit;
            font-size: 9pt;
            background: transparent;
        }

        .denom-total-line {
            width: 90px;
            border: none;
            border-bottom: 1.5px solid #000;
            text-align: right;
            font-weight: bold;
            font-family: inherit;
            font-size: 9pt;
            background: transparent;
        }

        /* Logistics Layout */
        .logistics-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .logistics-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed #f1f5f9;
            padding-bottom: 6px;
        }

        .logistics-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .logistics-label {
            font-weight: 600;
            color: #475569;
        }

        .logistics-val {
            font-weight: 700;
            color: #0f172a;
        }

        .logistics-input-line {
            width: 120px;
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-weight: bold;
            text-align: center;
        }

        /* Signatures Grid */
        .signatures-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-block {
            width: 40%;
            text-align: center;
        }

        .signature-line {
            border-top: 1.5px dotted #94a3b8;
            margin-bottom: 6px;
        }

        .signature-label {
            font-size: 8.5pt;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Print Media Target overrides */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                color: #000000;
            }

            .report-container {
                box-shadow: none;
                padding: 0;
                border-radius: 0;
                margin: 0;
                width: 100%;
                min-height: auto;
            }

            .print-controls {
                display: none;
            }

            .data-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .meta-grid {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .bold-total-row {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action bar -->
    <div class="print-controls">
        <a href="javascript:window.close();" class="btn-action btn-secondary"><i class="ph ph-x"></i> Close Window</a>
        <button onclick="window.print();" class="btn-action"><i class="ph ph-printer"></i> Print Summary</button>
    </div>

    <!-- Main Report Body -->
    <div class="report-container">
        
        <!-- Header -->
        <header class="report-header">
            <div class="company-info">
                <?php if(!empty($company->logo_path)): ?>
                    <img src="<?= APP_URL . htmlspecialchars($company->logo_path) ?>" style="max-height: 45px; margin-bottom: 5px; object-fit: contain;">
                <?php endif; ?>
                <h1 class="company-name"><?= htmlspecialchars($company->name ?: 'Falcon Stationary (Pvt) Ltd') ?></h1>
                <p class="company-details">
                    <?= htmlspecialchars($company->address ?: '') ?> | Tel: <?= htmlspecialchars($company->phone ?: '') ?>
                </p>
            </div>
            <div class="report-title-section">
                <h2 class="report-title">Collection Report</h2>
                <div class="report-timestamp">
                    Printed: <?= date('d/m/Y h:i A') ?>
                </div>
            </div>
        </header>

        <!-- Route & Rep Metadata -->
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Route</span>
                <span class="meta-value"><?= htmlspecialchars($route->route_name) ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Sales Representative</span>
                <span class="meta-value"><?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Delivery Date</span>
                <span class="meta-value">
                    <?= htmlspecialchars($delivery && !empty($delivery->delivery_date) ? date('d/m/Y', strtotime($delivery->delivery_date)) : date('d/m/Y', strtotime($route->start_time))) ?>
                </span>
            </div>
        </div>

        <!-- SECTION 1: Bills on Route -->
        <section class="section-container">
            <h3 class="section-title"><i class="ph ph-file-text"></i> Bills On Route</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">SO No</th>
                        <th style="width: 25%;">Customer Name</th>
                        <th style="width: 10%;" class="text-right">Sales Amt</th>
                        <th style="width: 10%;">Payment Term</th>
                        <th style="width: 10%;" class="text-right">Bill Value</th>
                        <th style="width: 10%;" class="text-right">ACY Value</th>
                        <th style="width: 8%;" class="text-right">Cash</th>
                        <th style="width: 8%;" class="text-right">Credit</th>
                        <th style="width: 8%;" class="text-right">CHQ</th>
                        <th style="width: 10%;">CHQ Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bills_on_route)): ?>
                        <tr>
                            <td colspan="10" class="text-center" style="color: #64748b; padding: 15px;">No bills recorded on this route.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($bills_on_route as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['invoice_number']) ?></td>
                                <td><?= htmlspecialchars($b['customer_name']) ?></td>
                                <td class="text-right"><?= number_format($b['sales_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($b['term_name']) ?></td>
                                <td class="text-right"><?= number_format($b['sales_amount'], 2) ?></td>
                                <td class="text-right"><?= number_format($b['sales_amount'], 2) ?></td>
                                <td class="text-right"><?= $b['cash'] > 0 ? number_format($b['cash'], 2) : '-' ?></td>
                                <td class="text-right"><?= $b['credit'] > 0 ? number_format($b['credit'], 2) : '-' ?></td>
                                <td class="text-right"><?= $b['chq'] > 0 ? number_format($b['chq'], 2) : '-' ?></td>
                                <td><?= htmlspecialchars($b['chq_number']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bold-total-row">
                            <td colspan="2">Total</td>
                            <td class="text-right"><?= number_format($total_sales, 2) ?></td>
                            <td>-</td>
                            <td class="text-right"><?= number_format($total_sales, 2) ?></td>
                            <td class="text-right"><?= number_format($total_sales, 2) ?></td>
                            <td class="text-right"><?= $total_cash_sales > 0 ? number_format($total_cash_sales, 2) : '0.00' ?></td>
                            <td class="text-right"><?= $total_credit_sales > 0 ? number_format($total_credit_sales, 2) : '0.00' ?></td>
                            <td class="text-right"><?= $total_chq_sales > 0 ? number_format($total_chq_sales, 2) : '0.00' ?></td>
                            <td>-</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- SECTION 2: Credit Collections -->
        <section class="section-container">
            <h3 class="section-title"><i class="ph ph-receipt"></i> Credit Collection</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Invoice No</th>
                        <th style="width: 25%;">Customer Name</th>
                        <th style="width: 15%;" class="text-right">Credit Bill Value</th>
                        <th style="width: 12%;">Date of Invoice</th>
                        <th style="width: 10%;" class="text-right">Cash</th>
                        <th style="width: 10%;" class="text-right">CHQ</th>
                        <th style="width: 13%;">CHQ No</th>
                        <th style="width: 15%;">Cash Collector</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($credit_collections)): ?>
                        <tr>
                            <td colspan="8" class="text-center" style="color: #64748b; padding: 15px;">No credit collections recorded.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($credit_collections as $cc): ?>
                            <tr>
                                <td><?= htmlspecialchars($cc['invoice_number']) ?></td>
                                <td><?= htmlspecialchars($cc['customer_name']) ?></td>
                                <td class="text-right"><?= number_format($cc['credit_bill_value'], 2) ?></td>
                                <td><?= !empty($cc['invoice_date']) ? date('d/m/Y', strtotime($cc['invoice_date'])) : '-' ?></td>
                                <td class="text-right"><?= $cc['cash'] > 0 ? number_format($cc['cash'], 2) : '-' ?></td>
                                <td class="text-right"><?= $cc['chq'] > 0 ? number_format($cc['chq'], 2) : '-' ?></td>
                                <td><?= htmlspecialchars($cc['chq_number']) ?></td>
                                <td><?= htmlspecialchars($cc['collector']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bold-total-row">
                            <td colspan="2">Total</td>
                            <td class="text-right"><?= number_format($total_credit_val, 2) ?></td>
                            <td>-</td>
                            <td class="text-right"><?= $total_cash_coll > 0 ? number_format($total_cash_coll, 2) : '0.00' ?></td>
                            <td class="text-right"><?= $total_chq_coll > 0 ? number_format($total_chq_coll, 2) : '0.00' ?></td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Side-by-Side bottom sections: Logistics and Cash Denomination -->
        <div class="bottom-grid">
            
            <!-- Logistics Log -->
            <div class="bottom-card">
                <h4 class="bottom-card-title"><i class="ph ph-truck"></i> Logistics & Odometer Log</h4>
                <div class="logistics-list">
                    <div class="logistics-row">
                        <span class="logistics-label">Driver</span>
                        <span class="logistics-val"><?= htmlspecialchars($delivery && !empty($delivery->driver_name) ? $delivery->driver_name : '__________________') ?></span>
                    </div>
                    <div class="logistics-row">
                        <span class="logistics-label">Vehicle No</span>
                        <span class="logistics-val"><?= htmlspecialchars($delivery && !empty($delivery->vehicle_number) ? $delivery->vehicle_number : '__________________') ?></span>
                    </div>
                    <div class="logistics-row">
                        <span class="logistics-label">Date</span>
                        <span class="logistics-val"><?= date('d/m/Y') ?></span>
                    </div>
                    <div class="logistics-row">
                        <span class="logistics-label">Helper</span>
                        <span class="logistics-val"><?= htmlspecialchars($delivery && !empty($delivery->partner_name) ? $delivery->partner_name : '__________________') ?></span>
                    </div>
                    <div class="logistics-row" style="margin-top: 8px;">
                        <span class="logistics-label">Start KM</span>
                        <span><input type="text" class="logistics-input-line"> KM</span>
                    </div>
                    <div class="logistics-row">
                        <span class="logistics-label">End KM</span>
                        <span><input type="text" class="logistics-input-line"> KM</span>
                    </div>
                    <div class="logistics-row">
                        <span class="logistics-label">Distance Travelled</span>
                        <span><input type="text" class="logistics-input-line"> KM</span>
                    </div>
                </div>
            </div>

            <!-- Cash Denomination Counter -->
            <div class="bottom-card">
                <h4 class="bottom-card-title"><i class="ph ph-coins"></i> Cash Denomination</h4>
                <table class="denom-table">
                    <tr>
                        <td>5000 x</td>
                        <td><input type="text" class="denom-input-line"></td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td>1000 x</td>
                        <td><input type="text" class="denom-input-line"></td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td>500 x</td>
                        <td><input type="text" class="denom-input-line"></td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td>100 x</td>
                        <td><input type="text" class="denom-input-line"></td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td>50 x</td>
                        <td><input type="text" class="denom-input-line"></td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td>20 x</td>
                        <td><input type="text" class="denom-input-line"></td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td colspan="2">Coins</td>
                        <td class="text-right">= <input type="text" class="denom-total-line"></td>
                    </tr>
                    <tr>
                        <td colspan="2">Total Cash</td>
                        <td class="text-right">= <input type="text" class="denom-total-line" style="border-bottom: 2px double #000;"></td>
                    </tr>
                </table>
            </div>

        </div>

        <!-- Signatures Block -->
        <footer class="signatures-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <span class="signature-label">Representative Signature</span>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <span class="signature-label">Account Department Signature</span>
            </div>
        </footer>

    </div>

</body>
</html>
