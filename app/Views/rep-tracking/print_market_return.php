<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($data['title']) ?></title>
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.45; }
        .print-container { max-width: 800px; margin: 0 auto; }
        .company-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 25px; }
        .company-title { font-size: 22px; font-weight: 800; text-transform: uppercase; color: #1e3a8a; margin: 0 0 5px 0; }
        .company-details p { margin: 2px 0; color: #666; font-size: 12px; }
        .doc-title { font-size: 18px; font-weight: 700; color: #ff3b30; text-transform: uppercase; margin: 0; text-align: right; }
        .doc-number { font-family: monospace; font-size: 14px; font-weight: bold; color: #333; text-align: right; margin-top: 5px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .info-block h4 { margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .info-block p { margin: 3px 0; font-size: 13px; color: #1e293b; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th, .items-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .items-table th { background: #f8fafc; font-weight: 600; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
        .items-table td { font-size: 12.5px; }
        .items-table .num-col { text-align: right; font-family: monospace; }
        .items-table .cond-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .items-table .cond-good { background: #dcfce7; color: #15803d; }
        .items-table .cond-damaged { background: #fee2e2; color: #b91c1c; }
        .total-section { display: flex; justify-content: flex-end; margin-bottom: 40px; }
        .total-box { width: 280px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 18px; }
        .total-row { display: flex; justify-content: space-between; font-size: 13px; margin: 4px 0; }
        .total-row.grand { font-size: 15px; font-weight: bold; border-top: 1.5px solid #cbd5e1; padding-top: 8px; margin-top: 8px; color: #b91c1c; }
        .signature-section { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 60px; text-align: center; }
        .sig-box { border-top: 1px dashed #94a3b8; padding-top: 8px; font-size: 11.5px; color: #64748b; }
        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background:#f1f5f9; border-bottom:1px solid #cbd5e1; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; margin:-30px -30px 30px -30px;">
        <span style="font-weight:bold; color:#1e293b;">Market Return Note Print Preview</span>
        <div>
            <button onclick="window.print()" style="padding:6px 14px; background:#0066cc; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer;">Print Receipt</button>
            <button onclick="window.close()" style="padding:6px 14px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; margin-left:8px;">Close Window</button>
        </div>
    </div>

    <div class="print-container">
        <div class="company-header">
            <div class="company-details">
                <h1 class="company-title"><?= htmlspecialchars($data['company']->company_name ?? 'CURTISS ERP') ?></h1>
                <p><?= htmlspecialchars($data['company']->address ?? 'No. 123, Galle Road, Colombo, Sri Lanka') ?></p>
                <p>Phone: <?= htmlspecialchars($data['company']->phone ?? '+94 11 234 5678') ?> | Email: <?= htmlspecialchars($data['company']->email ?? 'info@curtiss.com') ?></p>
            </div>
            <div>
                <h2 class="doc-title">Market Return Note</h2>
                <div class="doc-number"><?= htmlspecialchars($data['credit_note']->credit_note_number) ?></div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h4>Return Details</h4>
                <p><strong>Date:</strong> <?= htmlspecialchars($data['credit_note']->note_date) ?></p>
                <p><strong>Representative:</strong> <?= htmlspecialchars($data['route_info']->rep_first_name . ' ' . $data['route_info']->rep_last_name) ?></p>
                <p><strong>Route Code:</strong> <?= htmlspecialchars($data['route_info']->route_number ?? 'N/A') ?></p>
                <p><strong>Route Name:</strong> <?= htmlspecialchars($data['route_info']->route_name ?? 'N/A') ?></p>
            </div>
            <div class="info-block">
                <h4>Customer Details</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($data['credit_note']->customer_name) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($data['credit_note']->phone ?? 'N/A') ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($data['credit_note']->address ?? 'N/A') ?></p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Returned Product</th>
                    <th style="width: 10%; text-align: center;">Condition</th>
                    <th style="width: 10%; text-align: right;">Qty</th>
                    <th style="width: 15%; text-align: right;">Price (Rs)</th>
                    <th style="width: 15%; text-align: right;">Total (Rs)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalQty = 0;
                foreach ($data['items'] as $index => $item): 
                    $totalQty += floatval($item->quantity);
                ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item->description) ?></strong>
                            <?php if (!empty($item->remarks)): ?>
                                <div style="font-size: 10.5px; color: #64748b; margin-top: 3px;">Remarks: <?= htmlspecialchars($item->remarks) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($item->condition_status === 'Good'): ?>
                                <span class="cond-badge cond-good">Good</span>
                            <?php else: ?>
                                <span class="cond-badge cond-damaged">Damaged</span>
                            <?php endif; ?>
                        </td>
                        <td class="num-col"><?= number_format($item->quantity, 0) ?></td>
                        <td class="num-col"><?= number_format($item->unit_price, 2) ?></td>
                        <td class="num-col" style="font-weight: bold;"><?= number_format($item->total, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Total Items Qty:</span>
                    <strong><?= number_format($totalQty, 0) ?></strong>
                </div>
                <div class="total-row grand">
                    <span>Grand Total:</span>
                    <span>Rs <?= number_format($data['credit_note']->total_amount, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <div class="sig-box">
                Prepared By
            </div>
            <div class="sig-box">
                Sales Representative
            </div>
            <div class="sig-box">
                Customer Signature
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
