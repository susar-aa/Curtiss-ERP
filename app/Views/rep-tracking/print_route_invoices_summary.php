<?php
$company = $data['company'];
$invoices = $data['invoices'];
$route = $data['route'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Invoice Summary - <?= htmlspecialchars($route->route_name) ?> - <?= APP_NAME ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Base Reset & Typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #e5e5e5;
            font-family: "SF Pro Display", "SF Pro Text", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 9pt; 
            color: #000000;
            line-height: 1.3; 
            -webkit-font-smoothing: antialiased;
        }

        /* Screen Wrapper */
        .page-wrapper {
            max-width: 210mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15mm 10mm 15mm 10mm; 
            position: relative;
            min-height: 297mm;
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
            margin-bottom: 20px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 10px; 
        }

        .company-info {
            width: 55%;
        }

        .company-logo {
            max-width: 120px; 
            max-height: 45px;
            margin-bottom: 5px;
            object-fit: contain;
        }

        .company-name {
            font-size: 13pt;
            font-weight: 800; 
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-details {
            font-size: 8pt;
            color: #222;
        }

        .invoice-meta {
            width: 40%;
            text-align: right;
        }

        .document-title {
            font-size: 16pt; 
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table th, .meta-table td {
            padding: 2px 0; 
            font-size: 8.5pt;
            text-align: right;
        }

        .meta-table th {
            font-weight: 700;
            padding-right: 10px;
            color: #000;
            white-space: nowrap;
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            font-size: 7.5pt;
        }

        .meta-table td {
            font-weight: 500;
            font-variant-numeric: tabular-nums; 
        }

        /* Summary Table */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px; 
        }

        .table-items th, .table-items td {
            padding: 8px 6px; 
            font-size: 8.5pt;
            border: 1px solid #ddd;
        }

        .table-items th {
            background-color: #f8fafc;
            border: 1px solid #000;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 7.5pt;
            text-align: left;
            color: #000;
        }

        .table-items td {
            border-bottom: 1px solid #ddd; 
        }

        .table-items th.num, .table-items td.num {
            text-align: right;
            font-variant-numeric: tabular-nums; 
        }

        .table-items th.center, .table-items td.center {
            text-align: center;
        }

        .table-items tr.bold-row td {
            font-weight: 800;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            background-color: #f8fafc;
        }

        /* Signatures Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px; 
            page-break-inside: avoid;
        }

        .signature-box {
            width: 180px;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 4px;
            height: 30px; 
        }

        .signature-label {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000; 
        }

        /* Footer */
        .document-footer {
            margin-top: auto; 
            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-size: 7.5pt;
            color: #666;
            display: flex;
            justify-content: space-between;
        }

        .no-print { 
            margin-bottom: 15px; 
            text-align: right; 
            background: #f8f9fa; 
            padding: 10px; 
            border-radius: 6px; 
            border: 1px solid #ddd;
        }
        
        .no-print button { 
            padding: 6px 14px; 
            font-size: 12px; 
            font-weight: bold; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .btn-print-action { background: #3f51b5; color: #fff; border: none; }
        .btn-close { background: #fff; color: #333; border: 1px solid #ccc; margin-left: 8px; }

        /* Print Specific Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm; 
            }
            
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .page-wrapper {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
                width: 100%;
                max-width: none;
                min-height: 0;
                display: block; 
            }

            .no-print {
                display: none !important;
            }

            .table-items th {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                background-color: #f8fafc !important;
            }

            .table-items thead {
                display: table-header-group;
            }

            .table-items tr {
                page-break-inside: avoid;
            }

            .signature-section {
                page-break-inside: avoid;
            }
            
            .document-footer {
                margin-top: 20px; 
            }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="no-print" style="max-width: 210mm; margin: 20px auto 0 auto; background: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-weight: bold; font-size: 13px; color: #333;">
            <i class="ph ph-printer"></i> Route Invoice Summary: <?= htmlspecialchars($route->route_name) ?> (<?= count($invoices) ?> Invoices)
        </div>
        <div>
            <button class="btn-print-action" onclick="window.print()"><i class="ph ph-printer"></i> Print Summary</button>
            <button class="btn-close" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="main-content">
            <!-- Header -->
            <div class="invoice-header">
                <div class="company-info">
                    <?php if(!empty($company->logo_path)): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($company->logo_path) ?>" class="company-logo" alt="Company Logo">
                    <?php else: ?>
                        <div class="company-name"><?= htmlspecialchars($company->company_name) ?></div>
                    <?php endif; ?>
                    
                    <div class="company-details">
                        <?php if(!empty($company->address)) echo nl2br(htmlspecialchars($company->address)) . '<br>'; ?>
                        <?php if(!empty($company->phone)) echo 'Tel: ' . htmlspecialchars($company->phone) . '<br>'; ?>
                        <?php if(!empty($company->email)) echo 'Email: ' . htmlspecialchars($company->email) . '<br>'; ?>
                    </div>
                </div>

                <div class="invoice-meta">
                    <div class="document-title">Invoice Summary</div>
                    <table class="meta-table">
                        <tr>
                            <th>Route No:</th>
                            <td>#RT-<?= str_pad($route->id, 5, '0', STR_PAD_LEFT) ?></td>
                        </tr>
                        <tr>
                            <th>Route Name:</th>
                            <td><?= htmlspecialchars($route->route_name) ?></td>
                        </tr>
                        <tr>
                            <th>Representative:</th>
                            <td><?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?= date('d-M-Y', strtotime($route->start_time)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Summary Table -->
            <table class="table-items">
                <thead>
                    <tr>
                        <th class="center" style="width: 5%;">#</th>
                        <th style="width: 15%;">Invoice No</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 30%;">Customer Name</th>
                        <th class="num" style="width: 12%;">Subtotal</th>
                        <th class="num" style="width: 10%;">Discount</th>
                        <th class="num" style="width: 8%;">Tax</th>
                        <th class="num" style="width: 13%;">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rowNum = 1; 
                    $totalSubTotal = 0;
                    $totalDiscount = 0;
                    $totalTax = 0;
                    $totalGrand = 0;

                    if (empty($invoices)): 
                    ?>
                        <tr>
                            <td colspan="8" class="center" style="padding: 15px; color: #666; font-weight: bold;">No invoices found on this route.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($invoices as $invData): 
                            $invoice = $invData['invoice'];
                            
                            $subTotal = floatval($invoice->total_amount);
                            $discountAmount = 0;
                            
                            if ($invoice->global_discount_val > 0) {
                                if ($invoice->global_discount_type == '%') {
                                    $discountAmount = $subTotal * ($invoice->global_discount_val / 100);
                                } else {
                                    $discountAmount = floatval($invoice->global_discount_val);
                                }
                            }
                            
                            $taxAmount = floatval($invoice->tax_amount);
                            $grandTotal = $subTotal - $discountAmount + $taxAmount;

                            $totalSubTotal += $subTotal;
                            $totalDiscount += $discountAmount;
                            $totalTax += $taxAmount;
                            $totalGrand += $grandTotal;
                        ?>
                        <tr>
                            <td class="center"><?= $rowNum++ ?></td>
                            <td><strong><?= htmlspecialchars($invoice->invoice_number) ?></strong></td>
                            <td><?= date('d-M-Y', strtotime($invoice->invoice_date)) ?></td>
                            <td><?= htmlspecialchars($invoice->customer_name) ?></td>
                            <td class="num"><?= number_format($subTotal, 2) ?></td>
                            <td class="num"><?= $discountAmount > 0 ? number_format($discountAmount, 2) : '-' ?></td>
                            <td class="num"><?= $taxAmount > 0 ? number_format($taxAmount, 2) : '-' ?></td>
                            <td class="num" style="font-weight: bold;"><?= number_format($grandTotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bold-row">
                            <td colspan="4" style="text-align: right; text-transform: uppercase;"><strong>Total Summary</strong></td>
                            <td class="num"><strong><?= number_format($totalSubTotal, 2) ?></strong></td>
                            <td class="num"><strong><?= $totalDiscount > 0 ? number_format($totalDiscount, 2) : '-' ?></strong></td>
                            <td class="num"><strong><?= $totalTax > 0 ? number_format($totalTax, 2) : '-' ?></strong></td>
                            <td class="num" style="color: #2e7d32;"><strong><?= number_format($totalGrand, 2) ?></strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Prepared By</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Checked By</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Representative Signature</div>
                </div>
            </div>
        </div>

        <!-- System Footer -->
        <div class="document-footer">
            <div>Generated by <?= APP_NAME ?> on <?= date('d-M-Y H:i') ?> | Route: <?= htmlspecialchars($route->route_name) ?></div>
            <div>Invoice Summary Sheet</div>
        </div>
    </div>

</body>
</html>
