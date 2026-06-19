@ -27,15 +27,21 @@ $totalOutstanding = $billed - $paid - $credited;
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($data['invoice']->invoice_number) ?> - <?= APP_NAME ?></title>
    <style>
        /* A4 Page Formatting - Professional Black & White Design */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            background-color: #f0f0f0; 
            font-family: 'Times New Roman', Times, serif;
            display: flex; 
            justify-content: center; 
            padding: 20px; 
            margin: 0; 
            color: #000; 
        }
        
        .a4-container { 
@ -43,198 +49,487 @@ $totalOutstanding = $billed - $paid - $credited;
            min-height: 297mm; 
            background: #fff; 
            padding: 15mm 20mm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.3); 
            position: relative;
            border: 1px solid #000;
        }

        .controls { 
            text-align: center; 
            margin-bottom: 20px; 
            width: 100%; 
            position: absolute; 
            top: -60px; 
            left: 0;
        }
        
        .btn { 
            padding: 10px 25px; 
            background: #000; 
            color: #fff; 
            text-decoration: none; 
            border: 1px solid #000; 
            font-weight: bold; 
            cursor: pointer; 
            margin: 0 5px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #333;
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
        }

        .company-info {
            flex: 1;
        }

        .logo { 
            max-height: 60px; 
            max-width: 200px; 
            object-fit: contain; 
            margin-bottom: 10px; 
        }

        .company-name { 
            font-size: 22px; 
            font-weight: bold; 
            margin: 0 0 8px 0; 
            color: #000; 
            font-family: Arial, sans-serif;
        }

        .company-details { 
            font-size: 11px; 
            color: #000; 
            line-height: 1.5;
        }

        .invoice-info {
            text-align: right;
            min-width: 200px;
        }

        .invoice-title { 
            font-size: 32px; 
            color: #000; 
            font-weight: bold; 
            margin: 0 0 15px 0; 
            letter-spacing: 3px;
            font-family: Arial, sans-serif;
            text-transform: uppercase;
        }

        .invoice-meta-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        .invoice-meta-table td {
            padding: 4px 0;
            text-align: right;
        }

        .invoice-meta-table td:first-child {
            font-weight: bold;
            padding-right: 10px;
            white-space: nowrap;
        }

        /* Customer Information Section */
        .customer-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #000;
            background: #fff;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #000;
            font-family: Arial, sans-serif;
            letter-spacing: 1px;
        }

        .customer-details {
            font-size: 12px;
            line-height: 1.6;
        }

        .customer-details strong {
            font-size: 14px;
        }

        /* Product Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11px;
        }

        .items-table thead {
            border-bottom: 2px solid #000;
        }

        .items-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            font-family: Arial, sans-serif;
            border-bottom: 1px solid #000;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #ccc;
            vertical-align: top;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #000;
        }

        /* Totals Section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 25px;
        }

        .totals-table {
            width: 280px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
        }

        .totals-table td.text-right {
            text-align: right;
            font-weight: bold;
        }

        .totals-table tr.grand-total td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 14px;
            font-weight: bold;
            padding: 10px;
        }

        .totals-table tr.highlight td {
            color: #000;
            border-top: 1px dashed #000;
        }

        /* Payment Information Section */
        .payment-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #000;
            font-size: 12px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #eee;
        }

        .payment-table td:first-child {
            font-weight: bold;
            white-space: nowrap;
        }

        /* Notes Section */
        .notes-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #000;
            font-size: 12px;
            display: none;
        }

        .notes-section.has-content {
            display: block;
        }

        .notes-content {
            line-height: 1.6;
        }

        /* Signature Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-top: 20px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 30%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 8px;
            font-size: 11px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            right: 20mm;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 10px;
            color: #000;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Print Media Query */
        @media print { 
            @page { 
                size: A4; 
                margin: 0; 
            }
            body { 
                background: #fff; 
                padding: 0; 
            } 
            .a4-container { 
                width: 100%; 
                min-height: auto; 
                box-shadow: none; 
                padding: 15mm 20mm; 
                border: none;
            } 
            .controls { 
                display: none !important; 
            } 
            .footer {
                position: fixed;
                bottom: 0;
                left: 20mm;
                right: 20mm;
            }
            
            /* Prevent page breaks inside important sections */
            .header-section,
            .customer-section,
            .totals-section,
            .signature-section {
                page-break-inside: avoid;
            }
            
            /* Repeat table header on new page */
            .items-table thead {
                display: table-header-group;
            }
            
            .items-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }

        /* Screen Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .a4-container {
                width: 100%;
                min-height: auto;
                padding: 15px;
                border: none;
            }
            .controls {
                position: static;
                margin-bottom: 15px;
            }
            .header-section {
                flex-direction: column;
                gap: 15px;
            }
            .invoice-info {
                text-align: left;
                width: 100%;
            }
            .invoice-meta-table td {
                text-align: left;
            }
            .signature-section {
                flex-direction: column;
                gap: 20px;
            }
            .signature-box {
                width: 100%;
            }
            .footer {
                position: static;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="a4-container">
        <div class="controls">
            <button onclick="window.print()" class="btn">Print Invoice</button>
            <button onclick="window.print()" class="btn">Download PDF</button>
        </div>

        <!-- Header Section -->
        <div class="header-section">
            <div class="company-info">
                <?php if(!empty($data['company']->logo_path)): ?>
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($data['company']->logo_path) ?>" class="logo" alt="Logo">
                <?php else: ?>
                    <h1 class="company-name"><?= htmlspecialchars($data['company']->company_name) ?></h1>
                <?php endif; ?>
                
                <div class="company-details">
                    <?php if(!empty($data['company']->address)) echo nl2br(htmlspecialchars($data['company']->address)) . '<br>'; ?>
                    <?php if(!empty($data['company']->phone)) echo 'Phone: ' . htmlspecialchars($data['company']->phone) . '<br>'; ?>
                    <?php if(!empty($data['company']->email)) echo 'Email: ' . htmlspecialchars($data['company']->email) . '<br>'; ?>
                    <?php if(!empty($data['company']->website)) echo 'Website: ' . htmlspecialchars($data['company']->website) . '<br>'; ?>
                    <?php if(!empty($data['company']->tax_number)) echo 'VAT/Tax No: ' . htmlspecialchars($data['company']->tax_number); ?>
                </div>
            </div>
            
            <div class="invoice-info">
                <h1 class="invoice-title">INVOICE</h1>
                <table class="invoice-meta-table">
                    <tr>
                        <td>Invoice No:</td>
                        <td><?= htmlspecialchars($data['invoice']->invoice_number) ?></td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td><?= date('d/m/Y', strtotime($data['invoice']->invoice_date)) ?></td>
                    </tr>
                    <tr>
                        <td>Due Date:</td>
                        <td><?= date('d/m/Y', strtotime($data['invoice']->due_date)) ?></td>
                    </tr>
                    <?php if(!empty($data['invoice']->sales_order_ref)): ?>
                    <tr>
                        <td>Sales Order:</td>
                        <td><?= htmlspecialchars($data['invoice']->sales_order_ref) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(!empty($data['invoice']->route_ref)): ?>
                    <tr>
                        <td>Route Ref:</td>
                        <td><?= htmlspecialchars($data['invoice']->route_ref) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Customer Information Section -->
        <div class="customer-section">
            <div class="section-title">Bill To</div>
            <div class="customer-details">
                <strong><?= htmlspecialchars($data['invoice']->customer_name) ?></strong><br>
                <?php if(!empty($data['invoice']->customer_code)) echo 'Customer Code: ' . htmlspecialchars($data['invoice']->customer_code) . '<br>'; ?>
                <?php if(!empty($data['invoice']->address)) echo nl2br(htmlspecialchars($data['invoice']->address)) . '<br>'; ?>
                <?php if(!empty($data['invoice']->phone)) echo 'Phone: ' . htmlspecialchars($data['invoice']->phone) . '<br>'; ?>
                <?php if(!empty($data['invoice']->tax_number)) echo 'Tax No: ' . htmlspecialchars($data['invoice']->tax_number); ?>
            </div>
        </div>

        <!-- Product Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Product</th>
                    <th style="width: 80px;" class="text-center">SKU</th>
                    <th style="width: 60px;" class="text-center">Qty</th>
                    <th style="width: 100px;" class="text-right">Unit Price</th>
                    <th style="width: 80px;" class="text-right">Discount</th>
                    <th style="width: 120px;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $rowNum = 1; foreach($data['items'] as $item): ?>
                <tr>
                    <td class="text-center"><?= $rowNum++ ?></td>
                    <td><?= htmlspecialchars($item->description) ?></td>
                    <td class="text-center"><?= !empty($item->sku) ? htmlspecialchars($item->sku) : '-' ?></td>
                    <td class="text-center"><?= number_format($item->quantity, 0) ?></td>
                    <td class="text-right"><?= number_format($item->unit_price, 2) ?></td>
                    <td class="text-right">
                        <?php if($item->discount_value > 0): ?>
                            <?= $item->discount_type == '%' ? $item->discount_value . '%' : number_format($item->discount_value, 2) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= number_format($item->total, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <table class="totals-table">
                <?php 
                    // Calculate exact correct totals working downwards from the DB subtotal
                    $subTotal = $data['invoice']->total_amount;
@ -263,51 +558,133 @@ $totalOutstanding = $billed - $paid - $credited;
                    $amountDueNow = $previousBalance + $thisInvoiceGrandTotal;
                ?>
                
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right"><?= number_format($subTotal, 2) ?></td>
                </tr>
                
                <?php if($data['invoice']->global_discount_val > 0): ?>
                <tr>
                    <td>Bill Discount (<?= $data['invoice']->global_discount_type == '%' ? number_format($data['invoice']->global_discount_val, 2) . '%' : 'Flat' ?>):</td>
                    <td class="text-right">-<?= number_format($globalDiscountAmount, 2) ?></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <td>Net Subtotal:</td>
                    <td class="text-right"><?= number_format($netSubTotal, 2) ?></td>
                </tr>
                
                <?php if($data['invoice']->tax_amount > 0): ?>
                <tr>
                    <td>Tax (<?= htmlspecialchars($data['invoice']->tax_name ?? 'Tax') ?> <?= $data['invoice']->rate_percentage ?? '' ?>%):</td>
                    <td class="text-right"><?= number_format($data['invoice']->tax_amount, 2) ?></td>
                </tr>
                <?php endif; ?>

                <tr class="grand-total">
                    <td>Grand Total:</td>
                    <td class="text-right"><?= number_format($thisInvoiceGrandTotal, 2) ?></td>
                </tr>

                <!-- Outstanding Balance for Unpaid Invoices -->
                <?php if(in_array($data['invoice']->status, ['Unpaid', 'Draft']) && ($previousBalance > 0.01 || $previousBalance < -0.01)): ?>
                <tr class="highlight">
                    <td>Previous Balance:</td>
                    <td class="text-right"><?= number_format($previousBalance, 2) ?></td>
                </tr>
                <tr class="highlight">
                    <td>Total Amount Due:</td>
                    <td class="text-right"><?= number_format($amountDueNow, 2) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Payment Information Section -->
        <?php if(!empty($data['invoice']->payment_method) || !empty($data['invoice']->status)): ?>
        <div class="payment-section">
            <div class="section-title">Payment Information</div>
            <table class="payment-table">
                <?php if(!empty($data['invoice']->payment_method)): ?>
                <tr>
                    <td>Payment Method:</td>
                    <td><?= htmlspecialchars($data['invoice']->payment_method) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Payment Status:</td>
                    <td><?= htmlspecialchars($data['invoice']->status) ?></td>
                </tr>
                <?php 
                // Calculate amount paid for this invoice
                $db->query("SELECT COALESCE(SUM(amount), 0) as paid FROM customer_payments WHERE invoice_id = :id");
                $db->bind(':id', $data['invoice']->id);
                $invoicePaid = $db->single()->paid ?? 0;
                $balanceDue = $thisInvoiceGrandTotal - $invoicePaid;
                ?>
                <?php if($invoicePaid > 0): ?>
                <tr>
                    <td>Amount Paid:</td>
                    <td><?= number_format($invoicePaid, 2) ?></td>
                </tr>
                <?php endif; ?>
                <?php if($balanceDue > 0.01): ?>
                <tr>
                    <td>Balance Due:</td>
                    <td><?= number_format($balanceDue, 2) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- Notes Section -->
        <?php 
        $hasNotes = false;
        $notesContent = '';
        if(!empty($data['invoice']->remarks)) {
            $hasNotes = true;
            $notesContent .= '<strong>Remarks:</strong> ' . nl2br(htmlspecialchars($data['invoice']->remarks)) . '<br><br>';
        }
        if(!empty($data['invoice']->delivery_instructions)) {
            $hasNotes = true;
            $notesContent .= '<strong>Delivery Instructions:</strong> ' . nl2br(htmlspecialchars($data['invoice']->delivery_instructions)) . '<br><br>';
        }
        if(!empty($data['invoice']->internal_notes)) {
            $hasNotes = true;
            $notesContent .= '<strong>Internal Notes:</strong> ' . nl2br(htmlspecialchars($data['invoice']->internal_notes));
        }
        ?>
        <?php if($hasNotes): ?>
        <div class="notes-section has-content">
            <div class="section-title">Notes</div>
            <div class="notes-content">
                <?= $notesContent ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Prepared By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Customer Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <span>Printed: <?= date('d/m/Y H:i:s') ?></span>
                <span><?= APP_NAME ?></span>
                <span>Page 1 of 1</span>
            </div>
        </div>
    </div>
</body>