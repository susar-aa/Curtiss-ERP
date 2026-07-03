<?php
$bill = $data['bill'];
$provider = $data['provider'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Bill <?= htmlspecialchars($bill->grn_number) ?></title>
    <style>
        @media print { 
            @page { margin: 0mm; size: auto; } 
            body { padding: 1.5cm !important; background: #fff !important; margin: 0; -webkit-print-color-adjust: exact; } 
            .po-box { box-shadow: none !important; padding: 0 !important; max-width: 100% !important;} 
            .controls { display: none !important; } 
        }

        body { background-color: #e5e7eb; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 40px 20px; color: #333; }
        .po-box { max-width: 800px; margin: auto; padding: 50px; background: #fff; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 8px solid #0066cc;}
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #0066cc; padding-bottom: 20px;}
        .title { font-size: 32px; font-weight: 300; color: #0066cc; margin: 0 0 10px 0; letter-spacing: 1px; }
        
        .parties { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .vendor-box h4 { margin: 0 0 5px 0; color: #888; text-transform: uppercase; font-size: 12px; }
        
        table.items { width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #ddd; }
        table.items td { padding: 12px; border-bottom: 1px solid #eee; }
        .num { text-align: right; }

        .controls { text-align: center; margin-bottom: 30px; }
        .btn { padding: 10px 20px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 6px; cursor: pointer; border: none; margin: 0 5px; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
        .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
        
        .status-badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-Unpaid { background: #ffebee; color: #c62828; }
        .status-PartiallyPaid { background: #fff3e0; color: #e65100; }
        .status-Paid { background: #e8f5e9; color: #2e7d32; }

        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; }
        .detail-item { font-size: 14px; }
        .detail-item strong { color: #555; }
    </style>
</head>
<body>
    <div class="controls">
        <a href="<?= APP_URL ?>/serviceprovider/index/<?= $provider->id ?>" class="btn btn-outline">&larr; Back to Profile</a>
        <button onclick="window.print()" class="btn">Print Bill</button>
    </div>

    <div class="po-box">
        <div class="header">
            <div>
                <h2 style="margin:0; color:#333;">Service Bill Invoice</h2>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">
                    Category: <strong><?= htmlspecialchars($provider->service_category ?: 'General') ?></strong>
                </div>
            </div>
            <div style="text-align: right;">
                <h1 class="title"><?= htmlspecialchars($bill->grn_number) ?></h1>
                <div style="font-size: 14px;">
                    <strong>Bill Date:</strong> <?= date('F j, Y', strtotime($bill->grn_date)) ?><br>
                    <strong>Due Date:</strong> <?= date('F j, Y', strtotime($bill->due_date)) ?><br>
                    <strong>Status:</strong> <span class="status-badge status-<?= str_replace(' ', '', $bill->status ?: 'Unpaid') ?>"><?= $bill->status ?: 'Unpaid' ?></span>
                </div>
            </div>
        </div>

        <div class="parties">
            <div class="vendor-box">
                <h4>Service Provider</h4>
                <strong><?= htmlspecialchars($provider->name) ?></strong><br>
                <?php if(!empty($provider->address)) echo nl2br(htmlspecialchars($provider->address)) . '<br>'; ?>
                <?php if(!empty($provider->phone)) echo 'Phone: ' . htmlspecialchars($provider->phone) . '<br>'; ?>
                <?php if(!empty($provider->email)) echo 'Email: ' . htmlspecialchars($provider->email); ?>
            </div>
            <div class="vendor-box" style="text-align: right;">
                <h4>Billing Period</h4>
                <strong style="font-size:16px; color:#0066cc;"><?= htmlspecialchars($bill->service_period ?: 'N/A') ?></strong>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <strong>Subtotal:</strong> Rs: <?= number_format($bill->amount, 2) ?>
            </div>
            <div class="detail-item">
                <strong>Tax Amount:</strong> Rs: <?= number_format($bill->tax, 2) ?>
            </div>
            <div class="detail-item">
                <strong>Total Bill Amount:</strong> Rs: <?= number_format($bill->total_amount, 2) ?>
            </div>
            <div class="detail-item">
                <strong>Amount Paid:</strong> Rs: <?= number_format($bill->amount_paid, 2) ?>
            </div>
            <?php if(!empty($bill->receipt_number) && $bill->receipt_number !== $bill->grn_number): ?>
            <div class="detail-item" style="grid-column: span 2; border-top: 1px solid #eee; padding-top: 10px; font-size:14px;">
                <strong>Supplier Invoice Ref:</strong> <?= htmlspecialchars($bill->receipt_number) ?>
            </div>
            <?php endif; ?>
            <div class="detail-item" style="grid-column: span 2; border-top: 1px dashed #ddd; padding-top: 10px; margin-top: 5px; font-size:16px;">
                <strong>Balance Due:</strong> <span style="font-weight:bold; color: <?= $bill->balance_due > 0 ? '#c62828' : '#2e7d32' ?>;">Rs: <?= number_format($bill->balance_due, 2) ?></span>
            </div>
        </div>

        <?php if(!empty($bill->notes)): ?>
            <h4 style="margin: 0 0 5px 0; color: #888; font-size: 12px; text-transform: uppercase;">Memo / Notes</h4>
            <p style="margin: 0 0 30px 0; font-size: 13px; color: #444; line-height: 1.5; padding: 15px; background: #f8f9fa; border-radius: 6px; border:1px solid #eee;">
                <?= nl2br(htmlspecialchars($bill->notes)) ?>
            </p>
        <?php endif; ?>

        <?php if(!empty($bill->attachment)): ?>
            <h4 style="margin: 0 0 5px 0; color: #888; font-size: 12px; text-transform: uppercase;">Attachment</h4>
            <p style="margin: 0; padding: 10px 15px; background: #e3f2fd; border-radius: 6px; border: 1px solid #bbdefb; display: inline-flex; align-items: center; gap: 8px; font-size: 13px;">
                <span>📄 Original Bill Attachment</span>
                <a href="<?= APP_URL ?>/<?= htmlspecialchars($bill->attachment) ?>" download class="btn btn-small" style="padding: 2px 8px; font-size: 11px;">Download File</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
