<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Loading Report - <?= htmlspecialchars($data['route']->route_name) ?></title>
    <style>
        /* A4 Optimization Framework */
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            font-size: 11px; 
            color: #111; 
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .report-container {
            width: 100%;
            max-width: 190mm; /* Maximum writable space inside A4 portrait boundaries */
            margin: 0 auto;
        }

        /* High Density Header Layout */
        .header-section { 
            margin-bottom: 12px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 8px;
        }
        .header-section h1 { 
            margin: 0 0 4px 0; 
            font-size: 18px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            color: #000;
        }
        
        .meta-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 10px;
            margin-top: 5px;
        }
        .meta-group p { margin: 2px 0; line-height: 1.3; font-size: 11px; }
        
        /* Stats Dashboard Panel */
        .stats-summary {
            background: #f5f5f5 !important;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 6px 10px;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        .stat-item { text-align: center; }
        .stat-item span { display: block; font-size: 9px; text-transform: uppercase; color: #555; font-weight: bold; }
        .stat-item strong { font-size: 13px; color: #000; font-family: monospace, sans-serif; }

        /* Highly Compact Cost-Saving Table Layout */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 5px; 
        }
        th { 
            background-color: #eaeaea !important; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 9px; 
            border: 1px solid #000;
            padding: 4px 6px;
        }
        td { 
            border: 1px solid #666; 
            padding: 3px 6px; /* Ultra-tight padding to save paper vertical space */
            font-size: 11px;
            line-height: 1.2;
        }
        
        /* Alternating row background for easier scanning */
        tr:nth-child(even) { background-color: #fcfcfc; }
        
        /* Exact Column Constraints */
        .col-num { width: 5%; text-align: center; color: #555; font-size: 10px; }
        .col-name { width: 51%; font-weight: 500; }
        .col-qty { width: 10%; text-align: center; font-weight: bold; font-family: monospace; font-size: 12px; }
        
        /* Compact Tracking Column Controls */
        .col-tick { width: 7%; text-align: center; }
        .col-actual { width: 13%; text-align: center; }
        .col-double { width: 7%; text-align: center; }

        .tick-box-placeholder {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #444;
            border-radius: 2px;
            vertical-align: middle;
            background: #fff;
        }
        
        .line-input-placeholder {
            display: inline-block;
            width: 90%;
            border-bottom: 1px dotted #444;
            height: 12px;
            vertical-align: bottom;
        }
        
        /* Interactive Print Bar Controls */
        .no-print { 
            margin-bottom: 15px; 
            text-align: right; 
            background: #f8f9fa; 
            padding: 10px; 
            border-radius: 6px; 
            border: 1px solid #ddd;
        }
        .no-print button { 
            padding: 6px 14px; 
            font-size: 12px; 
            font-weight: bold; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .btn-print { background: #0066cc; color: #fff; border: none; }
        .btn-close { background: #fff; color: #333; border: 1px solid #ccc; margin-left: 8px; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            th { background-color: #eaeaea !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stats-summary { background-color: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="report-container">
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">🖨️ Print Loading Sheet</button>
            <button class="btn-close" onclick="window.close()">Close Window</button>
        </div>

        <div class="header-section">
            <h1>Route Loading Report</h1>
            <div class="meta-grid">
                <div class="meta-group">
                    <p><strong>Route Code/Name:</strong> <?= htmlspecialchars($data['route']->route_name) ?></p>
                    <p><strong>Assigned Representative:</strong> <?= htmlspecialchars($data['route']->first_name . ' ' . $data['route']->last_name) ?></p>
                    <p><strong>Generated Stamp:</strong> <?= date('Y-m-d g:i A') ?></p>
                </div>
                <div class="stats-summary">
                    <div class="stat-item">
                        <span>Active Bills</span>
                        <strong><?= $data['route']->bill_count ?></strong>
                    </div>
                    <div style="width: 1px; height: 20px; background: #ccc;"></div>
                    <div class="stat-item">
                        <span>Total Route Value</span>
                        <strong>Rs. <?= number_format($data['route']->total_sales, 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-name">Item Description</th>
                    <th class="col-qty">Req. Qty</th>
                    <th class="col-tick">Loaded</th>
                    <th class="col-actual">Actual Qty Loaded</th>
                    <th class="col-double">Audited</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                if (empty($data['items'])): 
                ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 15px; color: #555;">No products or orders found matching this tracking reference.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['items'] as $item): ?>
                        <tr>
                            <td class="col-num"><?= $counter++ ?></td>
                            <td class="col-name"><?= htmlspecialchars($item->item_name) ?></td>
                            <td class="col-qty"><?= floatval($item->total_qty) ?></td>
                            
                            <td class="col-tick"><span class="tick-box-placeholder"></span></td>
                            <td class="col-actual"><span class="line-input-placeholder"></span></td>
                            <td class="col-double"><span class="tick-box-placeholder"></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>