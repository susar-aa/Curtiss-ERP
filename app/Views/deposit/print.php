<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Slip <?= htmlspecialchars($data['deposit']->deposit_number) ?> - <?= APP_NAME ?></title>
    <style>
        /* A4 Page Formatting */
        body { 
            background-color: #525659; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; 
            display: flex; 
            justify-content: center; 
            padding: 40px 20px; 
            margin: 0; 
            color: #333; 
        }
        
        .a4-container { 
            width: 210mm; 
            min-height: 297mm; 
            background: #fff; 
            padding: 15mm 20mm; 
            box-shadow: 0 0 20px rgba(0,0,0,0.5); 
            box-sizing: border-box; 
            position: relative;
        }

        .controls { text-align: center; margin-bottom: 20px; width: 100%; position: absolute; top: -50px; left: 0;}
        .btn { padding: 10px 20px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; margin: 0 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);}
        
        /* Internal Typography & Grid */
        .header-area { display: flex; justify-content: space-between; border-bottom: 3px solid #0066cc; padding-bottom: 15px; margin-bottom: 20px;}
        .logo { max-height: 70px; max-width: 250px; object-fit: contain; margin-bottom: 10px; }
        .company-name { font-size: 26px; font-weight: bold; margin: 0 0 5px 0; color: #111; }
        .company-details { font-size: 12px; color: #555; line-height: 1.4;}
        
        .invoice-title { font-size: 30px; color: #0066cc; font-weight: bold; margin: 0 0 10px 0; letter-spacing: 1px; }
        .invoice-meta { font-size: 13px; color: #333; }
        .invoice-meta strong { display: inline-block; width: 110px; }

        .billing-grid { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 13px;}
        .bill-to h4 { margin: 0 0 8px 0; color: #fff; background: #0066cc; padding: 4px 10px; display: inline-block; text-transform: uppercase; font-size: 11px;}
        .bill-to p { margin: 0; line-height: 1.5; }
        
        /* Table */
        table.items { width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 30px; font-size: 13px;}
        table.items th { background: #f0f4f8; padding: 10px; border-bottom: 2px solid #ccc; font-weight: bold; color: #333;}
        table.items td { padding: 10px; border-bottom: 1px solid #ddd; }
        table.items th.num, table.items td.num { text-align: right; }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0066cc;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin: 25px 0 10px 0;
        }

        .totals-section { width: 100%; display: flex; justify-content: flex-end; }
        .totals-box { width: 320px; font-size: 13px;}
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .totals-row.grand-total { border-top: 2px solid #000; font-weight: bold; font-size: 15px; padding-top: 10px; margin-top: 5px;}
        
        .footer-notes { position: absolute; bottom: 15mm; left: 20mm; right: 20mm; border-top: 1px solid #ccc; padding-top: 10mm; font-size: 11px; color: #666; text-align: center; }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 60px;
            font-size: 13px;
        }
        .signature-line {
            border-top: 1px dashed #999;
            text-align: center;
            padding-top: 8px;
            margin-top: 40px;
            font-weight: bold;
        }

        /* Print Media Query */
        @media print { 
            @page { size: A4; margin: 0; }
            body { background: #fff; padding: 0; display: block; } 
            .a4-container { width: 100%; min-height: auto; box-shadow: none; padding: 15mm 20mm; } 
            .controls { display: none !important; } 
            .footer-notes { position: relative; bottom: 0; left: 0; right: 0; margin-top: 40px;}
        }
    </style>
</head>
<body>
    <?php
    $dep = $data['deposit'];
    $items = $data['items'];
    $company = $data['company'];
    ?>
    <div class="a4-container">
        <div class="controls">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/deposit/process/<?= $dep->id ?>" class="btn" style="background:#fff; color:#0066cc; border: 1px solid #0066cc;">&larr; Back to Details</a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn">Print Slip / PDF (A4)</button>
        </div>

        <div class="header-area">
            <div>
                <?php if(!empty($company->logo_path)): ?>
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($company->logo_path) ?>" class="logo" alt="Logo">
                <?php else: ?>
                    <h2 class="company-name"><?= htmlspecialchars($company->company_name ?? APP_NAME) ?></h2>
                <?php endif; ?>
                
                <div class="company-details">
                    <?php if(!empty($company->address)) echo nl2br(htmlspecialchars($company->address)) . '<br>'; ?>
                    <?php if(!empty($company->phone)) echo 'Phone: ' . htmlspecialchars($company->phone) . '<br>'; ?>
                    <?php if(!empty($company->email)) echo 'Email: ' . htmlspecialchars($company->email) . '<br>'; ?>
                </div>
            </div>
            
            <div style="text-align: right;">
                <h1 class="invoice-title">BANK DEPOSIT SLIP</h1>
                <div class="invoice-meta">
                    <div><strong>Deposit No:</strong> <?= htmlspecialchars($dep->deposit_number) ?></div>
                    <div><strong>Date Prepared:</strong> <?= date('d/m/Y', strtotime($dep->deposit_date)) ?></div>
                    <?php if ($dep->actual_banking_date): ?>
                        <div><strong>Banking Date:</strong> <?= date('d/m/Y', strtotime($dep->actual_banking_date)) ?></div>
                    <?php endif; ?>
                    <div><strong>Status:</strong> <?= htmlspecialchars($dep->status) ?></div>
                </div>
            </div>
        </div>

        <div class="billing-grid">
            <div class="bill-to">
                <h4>Banking Destination</h4>
                <p>
                    <strong style="font-size:14px;"><?= htmlspecialchars($dep->bank_name) ?></strong><br>
                    Account ID: <?= htmlspecialchars($dep->destination_bank_account_id) ?><br>
                    Account Code: <?= htmlspecialchars($dep->bank_code) ?><br>
                </p>
            </div>
            <div class="bill-to" style="text-align: right;">
                <h4>Audit Trail References</h4>
                <p>
                    Prepared By: <?= htmlspecialchars($dep->prepared_by_name) ?><br>
                    Transit J.E: <?= $dep->journal_entry_id ? '#' . $dep->journal_entry_id : '-' ?><br>
                    Realization J.E: <?= $dep->realization_journal_entry_id ? '#' . $dep->realization_journal_entry_id : '-' ?><br>
                </p>
            </div>
        </div>

        <!-- Cash Details -->
        <?php if ($dep->cash_total > 0): ?>
            <div class="section-title">Cash Denomination Breakdown</div>
            <table class="items" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Denomination</th>
                        <th class="num">Multiplier / Qty</th>
                        <th class="num">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $denoms = [5000, 2000, 1000, 500, 100, 50, 20];
                    foreach ($denoms as $d):
                        $field = "cash_" . $d;
                        if (intval($dep->$field) > 0):
                    ?>
                        <tr>
                            <td>Rs. <?= number_format($d) ?> Note</td>
                            <td class="num">✕ <?= number_format($dep->$field) ?></td>
                            <td class="num">Rs. <?= number_format($d * $dep->$field, 2) ?></td>
                        </tr>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Cheques Details -->
        <?php 
        $chequeItems = array_filter($items, function($item) { return $item->cheque_id !== null; });
        if (!empty($chequeItems)):
        ?>
            <div class="section-title">Cheques List</div>
            <table class="items" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Cheque No</th>
                        <th>Bank / Date</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chequeItems as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item->cheque_number) ?></strong></td>
                            <td><?= htmlspecialchars($item->bank_name) ?> (<?= date('d/m/Y', strtotime($item->banking_date)) ?>)</td>
                            <td><?= htmlspecialchars($item->customer_name) ?></td>
                            <td style="font-weight: bold;"><?= htmlspecialchars($item->status) ?></td>
                            <td class="num" style="font-family: monospace;">Rs. <?= number_format($item->cheque_amount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="totals-section">
            <div class="totals-box">
                <div class="totals-row">
                    <span>Cash Total Sent:</span>
                    <span>Rs. <?= number_format($dep->cash_total, 2) ?></span>
                </div>
                <?php if ($dep->accepted_cash_amount !== null && $dep->accepted_cash_amount != $dep->cash_total): ?>
                    <div class="totals-row" style="color: #c62828;">
                        <span>Accepted Cash:</span>
                        <span>Rs. <?= number_format($dep->accepted_cash_amount, 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="totals-row">
                    <span>Cheques Total:</span>
                    <span>Rs. <?= number_format($dep->cheque_total, 2) ?></span>
                </div>
                <div class="totals-row grand-total">
                    <span>Grand Total Deposited:</span>
                    <?php 
                    $finalCash = $dep->accepted_cash_amount !== null ? floatval($dep->accepted_cash_amount) : floatval($dep->cash_total);
                    $finalTotal = $finalCash + floatval($dep->cheque_total);
                    ?>
                    <span>Rs. <?= number_format($finalTotal, 2) ?></span>
                </div>
            </div>
        </div>

        <?php if(!empty($dep->approval_remarks)): ?>
        <div style="margin-top: 30px; font-size: 12px; border: 1px solid #ddd; padding: 10px; background: #fafafa;">
            <strong>Remarks / Verification Notes:</strong><br>
            <?= nl2br(htmlspecialchars($dep->approval_remarks)) ?>
        </div>
        <?php endif; ?>

        <div class="signature-grid">
            <div>
                <div class="signature-line">Prepared By / Representative</div>
            </div>
            <div>
                <div class="signature-line">Verified By / Accountant</div>
            </div>
        </div>

        <div class="footer-notes">
            Note: This is an internal deposit summary slip. Please attach bank deposit confirmations, slips, and receipts to this document for auditing and cash book verification.
        </div>
    </div>
</body>
</html>
