<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Loading Report - <?= htmlspecialchars($data['route']->route_name) ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @page {
            size: <?= $data['type'] === 'summary' ? 'A4 landscape' : 'A4 portrait' ?>;
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
            max-width: <?= $data['type'] === 'summary' ? '273mm' : '186mm' ?>;
            margin: 0 auto;
        }
        .header-section { 
            margin-bottom: 15px; 
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
            padding: 5px 6px;
        }
        td { 
            border: 1px solid #666; 
            padding: 4px 6px;
            font-size: 11px;
            line-height: 1.2;
        }
        tr:nth-child(even) { background-color: #fcfcfc; }
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
        .btn-print { background: #3f51b5; color: #fff; border: none; }
        .btn-close { background: #fff; color: #333; border: 1px solid #ccc; margin-left: 8px; }
        .category-header-row {
            background-color: #f1f3f9 !important;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
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
            <button class="btn-print" onclick="window.print()"><i class="ph ph-printer"></i> Print Document</button>
            <button class="btn-close" onclick="window.close()">Close Window</button>
        </div>

        <?php if ($data['type'] === 'loading'): ?>
            <div class="header-section">
                <h1>Route Loading Sheet</h1>
                <div class="meta-grid">
                    <div class="meta-group">
                        <p><strong>Route Name:</strong> <?= htmlspecialchars($data['route']->route_name) ?></p>
                        <p><strong>Assigned Rep:</strong> <?= htmlspecialchars($data['route']->first_name . ' ' . $data['route']->last_name) ?></p>
                        <p><strong>Printed On:</strong> <?= date('Y-m-d g:i A') ?></p>
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

            <!-- LOADING SHEET TABLE -->
            <table>
                <thead>
                    <tr>
                        <th style="width:5%; text-align:center;">No</th>
                        <th style="text-align:left; width:45%;">Product Name</th>
                        <th style="text-align:center; width:10%;">Qty</th>
                        <th style="text-align:right; width:13%;">Unit Price</th>
                        <th style="text-align:right; width:15%;">Total</th>
                        <th style="width:12%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (empty($data['items'])): 
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 15px; color: #555;">No items found for loading on this route.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $currentCategory = null;
                        $counter = 1;
                        foreach ($data['items'] as $item): 
                            if ($item->category_name !== $currentCategory):
                                $currentCategory = $item->category_name;
                        ?>
                            <tr class="category-header-row">
                                <td colspan="6" style="background-color: #f1f3f9; font-weight: bold; padding: 6px 8px; font-size: 10px; border: 1px solid #000; text-transform: uppercase;">
                                    <?= htmlspecialchars($currentCategory) ?>
                                </td>
                            </tr>
                        <?php 
                            endif; 
                            $itemTotal = floatval($item->final_loaded_qty) * floatval($item->unit_price);
                        ?>
                            <tr>
                                <td style="text-align:center;"><?= $counter++ ?></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($item->item_name) ?></td>
                                <td style="text-align:center; font-family: monospace; font-size:11px; font-weight:bold;"><?= floatval($item->final_loaded_qty) ?></td>
                                <td style="text-align:right; font-family: monospace; font-size:11px;"><?= number_format($item->unit_price, 2) ?></td>
                                <td style="text-align:right; font-family: monospace; font-size:11px; font-weight:bold;"><?= number_format($itemTotal, 2) ?></td>
                                <td style="text-align:right; padding-right: 15px;"><span class="tick-box-placeholder"></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Route Variance Summary Box -->
            <div style="margin-top: 20px; border: 1px solid #000; border-radius: 4px; padding: 10px; background: #fafafa;">
                <h3 style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase;">Total Route Summary & Verification</h3>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; font-size: 11px;">
                    <div>
                        <strong>Total Items Count:</strong><br>
                        <?= count($data['items']) ?> items
                    </div>
                    <div>
                        <strong>Total Shortages:</strong><br>
                        <?php 
                        $shortages = 0;
                        foreach($data['items'] as $item) {
                            if (floatval($item->variance) < 0) $shortages += abs(floatval($item->variance));
                        }
                        echo $shortages . ' pcs';
                        ?>
                    </div>
                    <div>
                        <strong>Total Overages:</strong><br>
                        <?php 
                        $overages = 0;
                        foreach($data['items'] as $item) {
                            if (floatval($item->variance) > 0) $overages += floatval($item->variance);
                        }
                        echo $overages . ' pcs';
                        ?>
                    </div>
                    <div>
                        <strong>Physical Verification:</strong><br>
                        <span style="display:inline-block; border-bottom:1px solid #444; width:100px; height:12px;"></span>
                    </div>
                </div>
            </div>

            <!-- Signature Block -->
            <div style="margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; font-size: 10px;">
                <div>
                    <p style="margin-bottom: 25px;">Prepared By:</p>
                    <p>___________________________</p>
                    <p style="color: #666;">Logistics / Admin</p>
                </div>
                <div>
                    <p style="margin-bottom: 25px;">Verified By (OIC):</p>
                    <p>___________________________</p>
                    <p style="color: #666;">Warehouse Keeper</p>
                </div>
                <div>
                    <p style="margin-bottom: 25px;">Acknowledged By:</p>
                    <p>___________________________</p>
                    <p style="color: #666;">Driver / Representative</p>
                </div>
            </div>

        <?php elseif ($data['type'] === 'summary'): ?>
            <!-- REVAMPED COLLECTION SUMMARY REPORT (LANDSCAPE DUAL COLUMN) -->
            <div style="text-align: center; margin-bottom: 15px;">
                <h2 style="margin: 0; font-size: 16px; text-transform: uppercase; font-weight: bold;">
                    <?= htmlspecialchars($data['company']->company_name ?? 'Falcon Stationary (Pvt) Ltd') ?>
                </h2>
                <h3 style="margin: 3px 0 0 0; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Collection Report</h3>
            </div>

            <!-- Meta Header (Customer/Location removed) -->
            <table style="width: 100%; border: none; margin-bottom: 12px; font-size: 10px;">
                <tr style="background: none;">
                    <td style="border: none; padding: 2px 0; width: 50%; line-height: 1.4;">
                        <strong>Sales rep:</strong> <?= htmlspecialchars($data['route']->first_name . ' ' . $data['route']->last_name) ?><br>
                        <strong>Route:</strong> <?= htmlspecialchars($data['route']->route_name) ?>
                    </td>
                    <td style="border: none; padding: 2px 0; width: 50%; text-align: right; vertical-align: top; line-height: 1.4;">
                        <strong>From Date:</strong> <?= date('d/m/Y', strtotime($data['route']->start_time ?? 'now')) ?><br>
                        <strong>To Date:</strong> <?= date('d/m/Y') ?>
                    </td>
                </tr>
            </table>

            <!-- Main Dual Column Layout -->
            <div style="display: flex; gap: 20px; align-items: flex-start; font-size: 10px;">
                
                <!-- Left Side: Table 1 and Table 2 stacked -->
                <div style="flex: 1.4; display: flex; flex-direction: column; gap: 15px;">
                    
                    <!-- Table 1: Sales Orders Grouping -->
                    <div>
                        <h4 style="margin: 0 0 5px 0; font-size: 11px; text-transform: uppercase; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 3px;">1. Sales Orders Grouping</h4>
                        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                            <thead>
                                <tr style="background-color: #f1f3f9;">
                                    <th style="border: 1px solid #000; padding: 4px; text-align: left; font-weight: bold; width: 35%;">Customer Name</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold; width: 15%;">Sales Amount</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 14%;">Payment Term</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 12%;">Cash</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 12%;">Credit</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 12%;">CHQ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotal = 0;
                                if (empty($data['bills'])):
                                ?>
                                    <tr style="background: none;">
                                        <td colspan="6" style="text-align: center; padding: 8px; border: 1px solid #000;">No bills found for this route.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['bills'] as $bill): 
                                        $grandTotal += floatval($bill->true_grand_total);
                                        $term = 'Credit';
                                        if ($bill->payment_term_id == 1 || strtolower($bill->status) === 'paid') {
                                            $term = 'Cash';
                                        }
                                    ?>
                                        <tr style="background: none;">
                                            <td style="border: 1px solid #000; padding: 4px; font-weight: bold;"><?= htmlspecialchars($bill->customer_name) ?></td>
                                            <td style="border: 1px solid #000; padding: 4px; text-align: right; font-family: monospace; font-weight: bold;"><?= number_format($bill->true_grand_total, 2) ?></td>
                                            <td style="border: 1px solid #000; padding: 4px; text-align: center;"><?= $term ?></td>
                                            <td style="border: 1px solid #000; padding: 4px;"></td>
                                            <td style="border: 1px solid #000; padding: 4px;"></td>
                                            <td style="border: 1px solid #000; padding: 4px;"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="background-color: #eaeaea; font-weight: bold;">
                                        <td style="border: 1px solid #000; padding: 4px; text-align: right; text-transform: uppercase;">Total</td>
                                        <td style="border: 1px solid #000; padding: 4px; text-align: right; font-family: monospace; font-size: 11px;"><?= number_format($grandTotal, 2) ?></td>
                                        <td colspan="4" style="border: 1px solid #000; padding: 4px;"></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table 2: Credit Collection -->
                    <div>
                        <h4 style="margin: 0 0 5px 0; font-size: 11px; text-transform: uppercase; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 3px;">2. Credit Collection</h4>
                        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                            <thead>
                                <tr style="background-color: #f1f3f9;">
                                    <th style="border: 1px solid #000; padding: 4px; text-align: left; font-weight: bold; width: 40%;">Customer Name</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold; width: 20%;">Credit Bill Value</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 20%;">Cash</th>
                                    <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 20%;">CHQ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i=1; $i<=8; $i++): ?>
                                    <tr style="background: none; height: 18px;">
                                        <td style="border: 1px solid #000; padding: 4px;"></td>
                                        <td style="border: 1px solid #000; padding: 4px;"></td>
                                        <td style="border: 1px solid #000; padding: 4px;"></td>
                                        <td style="border: 1px solid #000; padding: 4px;"></td>
                                    </tr>
                                <?php endfor; ?>
                                <tr style="background-color: #eaeaea; font-weight: bold;">
                                    <td style="border: 1px solid #000; padding: 4px; text-align: right; text-transform: uppercase;">Total</td>
                                    <td style="border: 1px solid #000; padding: 4px;"></td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 4px;"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Side: Cash Collection & Odometer Details Sidebar -->
                <div style="flex: 0.6; display: flex; flex-direction: column; gap: 15px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                        <tbody>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold; width: 40%;">Cash Collector</td>
                                <td style="border: 1px solid #000; padding: 4px; width: 60%;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Driver</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Start KM</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">End KM</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Vehicle No</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">Date</td>
                                <td style="border: 1px solid #000; padding: 4px;"><?= date('d/m/Y') ?></td>
                            </tr>
                            <tr style="background-color: #f1f3f9;">
                                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-weight: bold; text-align: center; text-transform: uppercase;">Cash Collection</td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">5000 X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">1000 X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">500 X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">100 X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">50 X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">20 X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background: none;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;">Coins X</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                            <tr style="background-color: #eaeaea; font-weight: bold;">
                                <td style="border: 1px solid #000; padding: 4px; text-align: right; text-transform: uppercase;">Total</td>
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 20px; font-size: 10px;">
                        <div style="text-align: center;">
                            <p style="margin: 0 0 4px 0;">....................................................................</p>
                            <p style="margin: 0; font-weight: bold;">Driver's Signature</p>
                        </div>
                        <div style="text-align: center;">
                            <p style="margin: 0 0 4px 0;">....................................................................</p>
                            <p style="margin: 0; font-weight: bold;">Partner's Signature</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>