<?php
$db = new Database();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($data['invoice']->invoice_number) ?> - <?= APP_NAME ?></title>
    <style>
        /* A4 Page Formatting */
        body { 
            background-color: #525659; 
            font-family: Arial, sans-serif; 
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
        
        .invoice-title { font-size: 38px; color: #0066cc; font-weight: bold; margin: 0 0 10px 0; letter-spacing: 2px; }
        .invoice-meta { font-size: 13px; color: #333; }
        .invoice-meta strong { display: inline-block; width: 80px; }

        .billing-grid { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 13px;}
        .bill-to h4 { margin: 0 0 8px 0; color: #fff; background: #0066cc; padding: 4px 10px; display: inline-block; text-transform: uppercase; font-size: 11px;}
        .bill-to p { margin: 0; line-height: 1.5; }
        
        /* Table */
        table.items { width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 30px; font-size: 13px;}
        table.items th { background: #f0f4f8; padding: 10px; border-bottom: 2px solid #ccc; font-weight: bold; color: #333;}
        table.items td { padding: 10px; border-bottom: 1px solid #ddd; }
        table.items th.num, table.items td.num { text-align: right; }
        
        .totals-section { width: 100%; display: flex; justify-content: flex-end; }
        .totals-box { width: 300px; font-size: 13px;}
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .totals-row.grand-total { border-top: 2px solid #000; font-weight: bold; font-size: 16px; padding-top: 10px; margin-top: 5px;}
        
        .footer-notes { position: absolute; bottom: 15mm; left: 20mm; right: 20mm; border-top: 1px solid #ccc; padding-top: 10mm; font-size: 11px; color: #666; text-align: center; }

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
    <div class="a4-container">
        <div class="controls">
            <!-- Checking if viewed inside ERP vs viewed by public guest -->
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/sales" class="btn" style="background:#fff; color:#0066cc;">&larr; Back to ERP</a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn">Print / PDF (A4)</button>
        </div>

        <div class="header-area">
            <div>
                <?php if(!empty($data['company']->logo_path)): ?>
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($data['company']->logo_path) ?>" class="logo" alt="Logo">
                <?php else: ?>
                    <h2 class="company-name"><?= htmlspecialchars($data['company']->company_name) ?></h2>
                <?php endif; ?>
                
                <div class="company-details">
                    <?php if(!empty($data['company']->address)) echo nl2br(htmlspecialchars($data['company']->address)) . '<br>'; ?>
                    <?php if(!empty($data['company']->phone)) echo htmlspecialchars($data['company']->phone) . '<br>'; ?>
                    <?php if(!empty($data['company']->email)) echo htmlspecialchars($data['company']->email) . '<br>'; ?>
                    <?php if(!empty($data['company']->tax_number)) echo 'VAT/Tax No: ' . htmlspecialchars($data['company']->tax_number); ?>
                </div>
            </div>
            
            <div style="text-align: right;">
                <h1 class="invoice-title">INVOICE</h1>
                <div class="invoice-meta">
                    <div><strong>Invoice No:</strong> <?= htmlspecialchars($data['invoice']->invoice_number) ?></div>
                    <div><strong>Date:</strong> <?= date('d/m/Y', strtotime($data['invoice']->invoice_date)) ?></div>
                    <div><strong>Due Date:</strong> <?= date('d/m/Y', strtotime($data['invoice']->due_date)) ?></div>
                </div>
            </div>
        </div>

        <div class="billing-grid">
            <div class="bill-to">
                <h4>Billed To</h4>
                <p>
                    <strong style="font-size:14px;"><?= htmlspecialchars($data['invoice']->customer_name) ?></strong><br>
                    <?php if(!empty($data['invoice']->address)) echo nl2br(htmlspecialchars($data['invoice']->address)) . '<br>'; ?>
                    <?php if(!empty($data['invoice']->phone)) echo htmlspecialchars($data['invoice']->phone); ?>
                </p>
            </div>
            <div class="bill-to" style="text-align: right;">
                <h4>Status</h4>
                <div style="display:inline-block; font-size: 16px; padding: 5px 15px; border: 2px solid <?= $data['invoice']->status == 'Paid' ? '#2e7d32' : '#ef6c00' ?>; color: <?= $data['invoice']->status == 'Paid' ? '#2e7d32' : '#ef6c00' ?>; font-weight: bold; border-radius: 4px; text-transform: uppercase;">
                    <?= $data['invoice']->status ?>
                </div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num" style="width: 10%;">Qty</th>
                    <th class="num" style="width: 15%;">Unit Price</th>
                    <th class="num" style="width: 15%;">Discount</th>
                    <th class="num" style="width: 20%;">Net Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item->description) ?></td>
                    <td class="num"><?= number_format($item->quantity, 0) ?></td>
                    <td class="num"><?= number_format($item->unit_price, 2) ?></td>
                    <td class="num">
                        <?php if($item->discount_value > 0): ?>
                            <?= $item->discount_type == '%' ? $item->discount_value . '%' : 'Rs: ' . number_format($item->discount_value, 2) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="num">Rs: <?= number_format($item->total, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-box">
                
                <?php 
                    // Calculate exact correct totals working downwards from the DB subtotal
                    $subTotal = $data['invoice']->total_amount;
                    $globalDiscountAmount = 0;
                    
                    if($data['invoice']->global_discount_val > 0) {
                        if ($data['invoice']->global_discount_type == '%') {
                            $globalDiscountAmount = $subTotal * ($data['invoice']->global_discount_val / 100);
                        } else {
                            $globalDiscountAmount = $data['invoice']->global_discount_val;
                        }
                    }
                    
                    $netSubTotal = $subTotal - $globalDiscountAmount;
                    if ($netSubTotal < 0) $netSubTotal = 0;

                    // This Invoice's specific final total
                    $thisInvoiceGrandTotal = $netSubTotal + $data['invoice']->tax_amount;

                    // If this invoice is Unpaid/Draft, it's inherently included in the $totalOutstanding
                    // To show a true 'Previous Balance', we must subtract this invoice from the total outstanding
                    $previousBalance = $totalOutstanding;
                    if (in_array($data['invoice']->status, ['Unpaid', 'Draft'])) {
                        $previousBalance -= $thisInvoiceGrandTotal;
                    }
                    $amountDueNow = $previousBalance + $thisInvoiceGrandTotal;
                ?>
                
                <?php if($data['invoice']->global_discount_val > 0): ?>
                    <div class="totals-row">
                        <span>Subtotal:</span>
                        <span>Rs: <?= number_format($subTotal, 2) ?></span>
                    </div>
                    <div class="totals-row" style="color: #c62828;">
                        <span>Discount (<?= $data['invoice']->global_discount_type == '%' ? number_format($data['invoice']->global_discount_val, 2) . '%' : 'Flat' ?>):</span>
                        <span>- Rs: <?= number_format($globalDiscountAmount, 2) ?></span>
                    </div>
                <?php endif; ?>

                <div class="totals-row">
                    <span>Net Subtotal:</span>
                    <span>Rs: <?= number_format($netSubTotal, 2) ?></span>
                </div>
                
                <?php if($data['invoice']->tax_amount > 0): ?>
                <div class="totals-row">
                    <span>Tax (<?= htmlspecialchars($data['invoice']->tax_name ?? 'Tax') ?> <?= $data['invoice']->rate_percentage ?? '' ?>%):</span>
                    <span>Rs: <?= number_format($data['invoice']->tax_amount, 2) ?></span>
                </div>
                <?php endif; ?>

                <div class="totals-row grand-total">
                    <span>Current Invoice Total:</span>
                    <span>Rs: <?= number_format($thisInvoiceGrandTotal, 2) ?></span>
                </div>

                <!-- NEW: Outstanding Balance Integration for Unpaid Invoices -->
                <?php if(in_array($data['invoice']->status, ['Unpaid', 'Draft']) && ($previousBalance > 0.01 || $previousBalance < -0.01)): ?>
                    <div class="totals-row" style="margin-top: 15px; color: #555; font-size: 13px;">
                        <span>Previous Balance:</span>
                        <span>Rs: <?= number_format($previousBalance, 2) ?></span>
                    </div>
                    <div class="totals-row" style="font-size: 16px; font-weight: bold; border-top: 1px dashed #333; padding-top: 8px; margin-top: 4px; color: #c62828;">
                        <span>Total Amount Due:</span>
                        <span>Rs: <?= number_format($amountDueNow, 2) ?></span>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        
        <div class="footer-notes">
            Thank you for your business. Please make checks payable to <strong><?= htmlspecialchars($data['company']->company_name) ?></strong>.
        </div>
    </div>
</body>
</html>