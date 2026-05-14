<?php
/* STREAMING_CHUNK:Balance Sheet Printable View */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Balance Sheet - <?= APP_NAME ?></title>
    <style>
        body { background: #f4f5f7; font-family: sans-serif; padding: 40px; color: #333; }
        .report-box { max-width: 800px; margin: auto; background: #fff; padding: 50px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .text-center { text-align: center; }
        .company-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 10px 0; border-bottom: 1px solid #eee; }
        .num { text-align: right; }
        .header-row { font-weight: bold; font-size: 16px; border-bottom: 2px solid #333; padding-top: 20px; }
        .total-row { font-weight: bold; padding-top: 15px; }
        .total-row td { border-bottom: 2px solid #333; }
        .grand-total td { font-size: 16px; border-bottom: 4px double #333; }
        @media print { body { padding: 0; background: #fff; } .report-box { box-shadow: none; padding: 0; } }
    </style>
</head>
<body>
    <div class="report-box">
        <div class="text-center">
            <div class="company-name"><?= htmlspecialchars($data['company']->company_name) ?></div>
            <h2>Balance Sheet</h2>
            <p>As of <?= date('F j, Y') ?></p>
        </div>

        <table>
            <!-- ASSETS -->
            <tr><td colspan="2" class="header-row">Assets</td></tr>
            <?php foreach($data['assets'] as $acc): ?>
                <tr><td><?= htmlspecialchars($acc->account_name) ?></td><td class="num"><?= number_format($acc->balance, 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="total-row grand-total"><td>Total Assets</td><td class="num" style="color: #0066cc;">Rs: <?= number_format($data['total_assets'], 2) ?></td></tr>

            <!-- LIABILITIES -->
            <tr><td colspan="2" class="header-row" style="padding-top: 30px;">Liabilities</td></tr>
            <?php foreach($data['liabilities'] as $acc): ?>
                <tr><td><?= htmlspecialchars($acc->account_name) ?></td><td class="num"><?= number_format($acc->balance, 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="total-row"><td>Total Liabilities</td><td class="num">Rs: <?= number_format($data['total_liabilities'], 2) ?></td></tr>

            <!-- EQUITY -->
            <tr><td colspan="2" class="header-row" style="padding-top: 30px;">Equity</td></tr>
            <?php foreach($data['equities'] as $acc): ?>
                <tr><td><?= htmlspecialchars($acc->account_name) ?></td><td class="num"><?= number_format($acc->balance, 2) ?></td></tr>
            <?php endforeach; ?>
            <tr><td><em>Net Income (Calculated)</em></td><td class="num"><em><?= number_format($data['net_income'], 2) ?></em></td></tr>
            <tr class="total-row"><td>Total Equity</td><td class="num">Rs: <?= number_format($data['total_equity'], 2) ?></td></tr>

            <!-- TOTAL LIABILITIES & EQUITY -->
            <tr class="total-row grand-total">
                <td style="padding-top: 30px;">Total Liabilities & Equity</td>
                <td class="num" style="padding-top: 30px; color: #0066cc;">Rs: <?= number_format($data['total_liabilities_equity'], 2) ?></td>
            </tr>
        </table>
        
        <?php if(round($data['total_assets'], 2) === round($data['total_liabilities_equity'], 2)): ?>
            <p style="text-align: center; color: #2e7d32; font-weight: bold; margin-top: 20px;">✓ The Balance Sheet is perfectly balanced.</p>
        <?php else: ?>
            <p style="text-align: center; color: #c62828; font-weight: bold; margin-top: 20px;">⚠ WARNING: Out of balance.</p>
        <?php endif; ?>
    </div>
    <div class="text-center" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Report</button>
    </div>
</body>
</html>