<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Audit Report - <?= htmlspecialchars($data['audit']->audit_number); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 5px 10px;
            border: none;
        }
        .meta-label {
            font-weight: bold;
            width: 15%;
        }
        .meta-value {
            width: 35%;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .item-table th, .item-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        .item-table th {
            background-color: #f2f2f7;
            font-weight: bold;
            border-bottom: 2px solid #999;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .val-positive {
            color: #2e7d32;
            font-weight: bold;
        }
        .val-negative {
            color: #c62828;
            font-weight: bold;
        }
        .summary-box {
            background-color: #f9f9fb;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 25px;
            border-radius: 6px;
            width: 300px;
            margin-left: auto;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            font-weight: bold;
        }
        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .sig-block {
            width: 30%;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 5px;
            margin-top: 40px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print();" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">Print Report</button>
        <button onclick="window.close();" style="padding: 10px 20px; cursor: pointer;">Close Window</button>
    </div>

    <div class="header">
        <h1>CURTISS ERP</h1>
        <h2>Stock Audit Variance Report</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Audit Number:</td>
            <td class="meta-value" style="font-weight: bold;"><?= htmlspecialchars($data['audit']->audit_number); ?></td>
            <td class="meta-label">Warehouse:</td>
            <td class="meta-value"><?= htmlspecialchars($data['audit']->warehouse_name); ?></td>
        </tr>
        <tr>
            <td class="meta-label">Date Created:</td>
            <td class="meta-value"><?= date('Y-m-d H:i', strtotime($data['audit']->created_at)); ?></td>
            <td class="meta-label">Counted By:</td>
            <td class="meta-value">
                <?= $data['audit']->counter_name ? htmlspecialchars($data['audit']->counter_name) : '-'; ?> 
                <?= $data['audit']->completed_at ? 'on ' . date('Y-m-d H:i', strtotime($data['audit']->completed_at)) : ''; ?>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Approved By:</td>
            <td class="meta-value">
                <?= $data['audit']->approver_name ? htmlspecialchars($data['audit']->approver_name) : '-'; ?> 
                <?= $data['audit']->approved_at ? 'on ' . date('Y-m-d H:i', strtotime($data['audit']->approved_at)) : ''; ?>
            </td>
            <td class="meta-label">Status:</td>
            <td class="meta-value" style="text-transform: uppercase; font-weight: bold; color: #007aff;"><?= htmlspecialchars($data['audit']->status); ?></td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">SKU / Item Code</th>
                <th style="width: 30%;">Product Details</th>
                <th style="width: 10%; text-align: right;">System Qty</th>
                <th style="width: 10%; text-align: right;">Physical Qty</th>
                <th style="width: 10%; text-align: right;">Variance Qty</th>
                <th style="width: 10%; text-align: right;">Unit Cost</th>
                <th style="width: 10%; text-align: right;">Variance Value</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $i = 1; 
                $netVal = 0; 
                $discrepantCount = 0;
                foreach ($data['items'] as $item): 
                    $diff = floatval($item->difference);
                    $varVal = floatval($item->variance_value);
                    $netVal += $varVal;
                    if ($diff != 0.00) $discrepantCount++;
            ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($item->item_code); ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item->item_name); ?></strong><br>
                        <span style="font-size: 11px; color: #666;"><?= htmlspecialchars($item->category_name ?? 'General'); ?></span>
                    </td>
                    <td class="text-right" style="font-family: monospace;"><?= number_format($item->system_qty, 2); ?></td>
                    <td class="text-right" style="font-family: monospace; font-weight: bold;"><?= number_format($item->physical_qty, 2); ?></td>
                    <td class="text-right <?= $diff >= 0 ? ($diff > 0 ? 'val-positive' : '') : 'val-negative'; ?>" style="font-family: monospace;">
                        <?= ($diff >= 0 ? '+' : '') . number_format($diff, 2); ?>
                    </td>
                    <td class="text-right" style="font-family: monospace; color: #555;"><?= number_format($item->unit_cost, 2); ?></td>
                    <td class="text-right <?= $varVal >= 0 ? ($varVal > 0 ? 'val-positive' : '') : 'val-negative'; ?>" style="font-family: monospace;">
                        <?= ($varVal >= 0 ? '+' : '') . number_format($varVal, 2); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row">
            <span>Total SKUs Audited:</span>
            <strong><?= count($data['items']); ?></strong>
        </div>
        <div class="summary-row">
            <span>Discrepant SKUs:</span>
            <strong style="color: <?= $discrepantCount > 0 ? '#ff9500' : '#2e7d32'; ?>;"><?= $discrepantCount; ?></strong>
        </div>
        <div class="summary-row">
            <span>Net Variance Value:</span>
            <strong class="<?= $netVal >= 0 ? ($netVal > 0 ? 'val-positive' : '') : 'val-negative'; ?>">
                <?= ($netVal >= 0 ? '+' : '') . number_format($netVal, 2); ?> LKR
            </strong>
        </div>
    </div>

    <?php if (!empty($data['audit']->overall_remarks)): ?>
        <div style="margin-top: 30px; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
            <strong style="display: block; margin-bottom: 6px;">Overall Audit Remarks:</strong>
            <div style="font-size: 13px; color: #555; white-space: pre-wrap;"><?= htmlspecialchars($data['audit']->overall_remarks); ?></div>
        </div>
    <?php endif; ?>

    <div class="footer">
        <div class="sig-block">
            Counted By (Signature)
        </div>
        <div class="sig-block">
            Verified By (Signature)
        </div>
        <div class="sig-block">
            Approved By (Signature)
        </div>
    </div>

</body>
</html>
