<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Cash Flows - <?= APP_NAME ?></title>
    <style>
        body { background: #f4f5f7; font-family: sans-serif; padding: 40px; color: #333; }
        .report-box { max-width: 800px; margin: auto; background: #fff; padding: 50px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .text-center { text-align: center; }
        .company-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 10px 0; border-bottom: 1px solid #eee; }
        .num { text-align: right; }
        .header-row { font-weight: bold; font-size: 16px; border-bottom: 2px solid #333; padding-top: 20px; color: #0066cc; }
        .sub-row { padding-left: 20px; }
        .total-row { font-weight: bold; padding-top: 15px; }
        .grand-total td { font-size: 18px; border-bottom: 4px double #333; padding-top: 20px;}
        @media print { body { padding: 0; background: #fff; } .report-box { box-shadow: none; padding: 0; } }
    </style>
</head>
<body>
    <div class="report-box">
        <div class="text-center">
            <div class="company-name"><?= htmlspecialchars($data['company']->company_name) ?></div>
            <h2>Statement of Cash Flows</h2>
            <p>For the period ending <?= date('F j, Y') ?></p>
        </div>

        <?php 
            $opTotal = $data['net_income'];
            foreach($data['operating'] as $op) { $opTotal += $op['amount']; }
            
            $invTotal = 0;
            foreach($data['investing'] as $inv) { $invTotal += $inv['amount']; }
            
            $finTotal = 0;
            foreach($data['financing'] as $fin) { $finTotal += $fin['amount']; }
            
            $netCashIncrease = $opTotal + $invTotal + $finTotal;
        ?>

        <table>
            <!-- OPERATING ACTIVITIES -->
            <tr><td colspan="2" class="header-row">Cash Flows from Operating Activities</td></tr>
            <tr><td>Net Income</td><td class="num"><?= number_format($data['net_income'], 2) ?></td></tr>
            <?php foreach($data['operating'] as $op): ?>
                <tr><td class="sub-row"><?= htmlspecialchars($op['name']) ?></td><td class="num"><?= number_format($op['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="total-row"><td>Net Cash from Operating Activities</td><td class="num">Rs: <?= number_format($opTotal, 2) ?></td></tr>

            <!-- INVESTING ACTIVITIES -->
            <tr><td colspan="2" class="header-row" style="padding-top: 30px;">Cash Flows from Investing Activities</td></tr>
            <?php if(empty($data['investing'])): ?><tr><td class="sub-row" style="color:#888;">No investing activities</td><td class="num">0.00</td></tr><?php endif; ?>
            <?php foreach($data['investing'] as $inv): ?>
                <tr><td class="sub-row"><?= htmlspecialchars($inv['name']) ?></td><td class="num"><?= number_format($inv['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="total-row"><td>Net Cash from Investing Activities</td><td class="num">Rs: <?= number_format($invTotal, 2) ?></td></tr>

            <!-- FINANCING ACTIVITIES -->
            <tr><td colspan="2" class="header-row" style="padding-top: 30px;">Cash Flows from Financing Activities</td></tr>
            <?php if(empty($data['financing'])): ?><tr><td class="sub-row" style="color:#888;">No financing activities</td><td class="num">0.00</td></tr><?php endif; ?>
            <?php foreach($data['financing'] as $fin): ?>
                <tr><td class="sub-row"><?= htmlspecialchars($fin['name']) ?></td><td class="num"><?= number_format($fin['amount'], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr class="total-row"><td>Net Cash from Financing Activities</td><td class="num">Rs: <?= number_format($finTotal, 2) ?></td></tr>

            <!-- GRAND TOTALS -->
            <tr class="grand-total">
                <td>Net Increase (Decrease) in Cash</td>
                <td class="num">Rs: <?= number_format($netCashIncrease, 2) ?></td>
            </tr>
            <tr class="grand-total" style="color: #2e7d32;">
                <td>Ending Cash Balance</td>
                <td class="num">Rs: <?= number_format($data['ending_cash'], 2) ?></td>
            </tr>
        </table>
    </div>
    <div class="text-center" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Report</button>
    </div>
</body>
</html>