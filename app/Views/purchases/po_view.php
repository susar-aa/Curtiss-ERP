<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order <?= htmlspecialchars($data['po']->po_number) ?> - <?= APP_NAME ?></title>
    <style>
        body { background-color: #e5e7eb; font-family: sans-serif; margin: 0; padding: 40px 20px; color: #333; }
        .po-box { max-width: 800px; margin: auto; padding: 50px; background: #fff; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #0066cc; padding-bottom: 20px;}
        .logo { max-height: 80px; max-width: 200px; object-fit: contain; margin-bottom: 15px; }
        .title { font-size: 32px; font-weight: 300; color: #0066cc; margin: 0 0 10px 0; letter-spacing: 1px; }
        
        .parties { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .vendor-box h4 { margin: 0 0 5px 0; color: #888; text-transform: uppercase; font-size: 12px; }
        
        table.items { width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #ddd; }
        table.items td { padding: 12px; border-bottom: 1px solid #eee; }
        .num { text-align: right; }
        
        .totals-box { width: 300px; float: right; }
        .grand-total { border-top: 2px solid #333; font-weight: bold; font-size: 18px; padding-top: 12px; color: #0066cc; display: flex; justify-content: space-between;}

        .controls { text-align: center; margin-bottom: 30px; }
        .btn { padding: 10px 20px; background: #0066cc; color: #fff; text-decoration: none; border-radius: 4px; cursor: pointer; border: none; margin: 0 5px; }
        @media print { body { padding: 0; background: #fff; } .po-box { box-shadow: none; padding: 0; } .controls { display: none; } }
    </style>
</head>
<body>
    <div class="controls">
        <a href="<?= APP_URL ?>/purchase" class="btn" style="background:transparent; border: 1px solid #0066cc; color: #0066cc;">&larr; Back to POs</a>
        <button onclick="window.print()" class="btn">Print / Save as PDF</button>
    </div>

    <div class="po-box">
        <div class="header">
            <div>
                <?php if(!empty($data['company']->logo_path)): ?>
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($data['company']->logo_path) ?>" class="logo">
                <?php else: ?>
                    <h2 style="margin:0;"><?= htmlspecialchars($data['company']->company_name) ?></h2>
                <?php endif; ?>
                <div style="font-size: 13px; color: #666; margin-top: 10px;">
                    <?php if(!empty($data['company']->address)) echo nl2br(htmlspecialchars($data['company']->address)) . '<br>'; ?>
                    <?= htmlspecialchars($data['company']->phone) ?>
                </div>
            </div>
            <div style="text-align: right;">
                <h1 class="title">PURCHASE ORDER</h1>
                <div style="font-size: 14px;">
                    <strong>PO Number:</strong> <?= htmlspecialchars($data['po']->po_number) ?><br>
                    <strong>Date:</strong> <?= date('F j, Y', strtotime($data['po']->po_date)) ?><br>
                    <strong>Expected By:</strong> <?= date('F j, Y', strtotime($data['po']->expected_date)) ?>
                </div>
            </div>
        </div>

        <div class="parties">
            <div class="vendor-box">
                <h4>Vendor / Supplier</h4>
                <strong><?= htmlspecialchars($data['po']->vendor_name) ?></strong><br>
                <?php if(!empty($data['po']->address)) echo nl2br(htmlspecialchars($data['po']->address)) . '<br>'; ?>
                <?= htmlspecialchars($data['po']->email) ?><br>
                <?= htmlspecialchars($data['po']->phone) ?>
            </div>
            <div class="vendor-box" style="text-align: right;">
                <h4>Ship To</h4>
                <strong><?= htmlspecialchars($data['company']->company_name) ?></strong><br>
                <?php if(!empty($data['company']->address)) echo nl2br(htmlspecialchars($data['company']->address)); ?>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="num">Qty</th>
                    <th class="num">Unit Cost</th>
                    <th class="num">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item->description) ?></td>
                    <td class="num"><?= number_format($item->quantity, 0) ?></td>
                    <td class="num">Rs: <?= number_format($item->unit_price, 2) ?></td>
                    <td class="num">Rs: <?= number_format($item->total, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="overflow: hidden;">
            <div class="totals-box">
                <div class="grand-total">
                    <span>Authorized Total:</span>
                    <span>Rs: <?= number_format($data['po']->total_amount, 2) ?></span>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 80px; font-size: 13px; color: #333;">
            <div style="border-top: 1px solid #333; width: 250px; padding-top: 5px;">
                Authorized Signature
            </div>
        </div>
    </div>
</body>
</html>