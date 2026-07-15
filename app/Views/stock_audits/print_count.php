<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Count Sheet - <?= htmlspecialchars($data['audit']->audit_number); ?></title>
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
            margin-bottom: 20px;
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
            border: 1px solid #999;
            padding: 8px 10px;
            text-align: left;
        }
        .item-table th {
            background-color: #f2f2f7;
            font-weight: bold;
        }
        .write-cell {
            width: 120px;
        }
        .remarks-cell {
            width: 200px;
        }
        .footer {
            margin-top: 50px;
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
        <button onclick="window.print();" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">Print Page</button>
        <button onclick="window.close();" style="padding: 10px 20px; cursor: pointer;">Close Window</button>
    </div>

    <div class="header">
        <h1>CURTISS ERP</h1>
        <h2>Physical Stock Count Sheet</h2>
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
            <td class="meta-label">Created By:</td>
            <td class="meta-value"><?= htmlspecialchars($data['audit']->creator_name ?? 'System'); ?></td>
        </tr>
        <tr>
            <td class="meta-label">Status:</td>
            <td class="meta-value" style="text-transform: uppercase; font-weight: bold;"><?= htmlspecialchars($data['audit']->status); ?></td>
            <td class="meta-label">Count Filters:</td>
            <td class="meta-value">
                Cat: <?= $data['audit']->category_id ? 'Filtered' : 'All'; ?> | 
                Brand: <?= $data['audit']->brand ? htmlspecialchars($data['audit']->brand) : 'All'; ?> | 
                Supplier: <?= $data['audit']->supplier_id ? 'Filtered' : 'All'; ?>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">SKU / Item Code</th>
                <th style="width: 35%;">Product Name</th>
                <th style="width: 15%;">Barcode</th>
                <th class="write-cell" style="text-align: center;">Physical Count</th>
                <th class="remarks-cell">Remarks / Discrepancy Note</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($data['items'] as $item): ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($item->item_code); ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item->item_name); ?></strong><br>
                        <span style="font-size: 11px; color: #555;"><?= htmlspecialchars($item->category_name ?? 'General'); ?></span>
                    </td>
                    <td style="font-family: monospace;"><?= htmlspecialchars($item->barcode ?: '-'); ?></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-block">
            Counted By (Signature)
        </div>
        <div class="sig-block">
            Verified By (Signature)
        </div>
        <div class="sig-block">
            Authorized By (Signature)
        </div>
    </div>

</body>
</html>
