<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Stock Report - <?= htmlspecialchars($data['route']->route_name) ?></title>
    <style>
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
            max-width: 186mm;
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
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th { 
            background-color: #eaeaea !important; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 9px; 
            border: 1px solid #000;
            padding: 6px 8px;
        }
        td { 
            border: 1px solid #666; 
            padding: 5px 8px;
            font-size: 11px;
            line-height: 1.2;
        }
        tr:nth-child(even) { background-color: #fcfcfc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .variance-pos { color: #2e7d32; font-weight: bold; }
        .variance-neg { color: #c62828; font-weight: bold; }
        .footer-signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .sig-block {
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
            margin-top: 30px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="report-container">
        <div class="header-section">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1>Return Stock Report</h1>
                    <p style="margin: 0; font-size: 12px; font-weight: bold; color: #555;"><?= htmlspecialchars($data['company']->company_name ?? 'Curtiss') ?></p>
                </div>
                <div class="text-right">
                    <p style="margin: 0; font-size: 10px; color: #666;">Printed Date: <?= date('Y-m-d H:i') ?></p>
                </div>
            </div>
            <div class="meta-grid">
                <div class="meta-group">
                    <p><strong>Route:</strong> <?= htmlspecialchars($data['route']->route_name) ?></p>
                    <p><strong>Sales Representative:</strong> <?= htmlspecialchars($data['route']->rep_name ?? 'N/A') ?></p>
                    <p><strong>Driver:</strong> <?= htmlspecialchars($data['delivery']->driver_name ?? 'N/A') ?></p>
                </div>
                <div class="meta-group text-right">
                    <p><strong>Vehicle:</strong> <?= htmlspecialchars($data['delivery']->vehicle_number ?? 'N/A') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($data['route']->status) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($data['route']->start_time))) ?></p>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="text-align: left;">Product Name</th>
                    <th style="width: 12%; text-align: center;">Loaded</th>
                    <th style="width: 12%; text-align: center;">Delivered</th>
                    <th style="width: 15%; text-align: center;">Expected Returned</th>
                    <th style="width: 15%; text-align: center;">Actual Returned</th>
                    <th style="width: 12%; text-align: center;">Variance</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $idx = 1;
                $savedReturnStock = $data['savedReturnStock'] ?: [];
                $stockItems = $data['balancing']['stock_items'] ?? [];

                if (empty($stockItems)): 
                ?>
                    <tr>
                        <td colspan="7" class="text-center" style="color: #666;">No items found on this delivery route.</td>
                    </tr>
                <?php 
                else:
                    foreach ($stockItems as $st):
                        $expectedReturned = max(0, intval($st->loaded_qty) - intval($st->delivered_qty));
                        $actualCounted = $expectedReturned;
                        
                        if (!empty($savedReturnStock)) {
                            $savedVal = null;
                            foreach ($savedReturnStock as $x) {
                                if (intval($x['item_id']) === intval($st->item_id) && intval($x['variation_option_id'] ?? 0) === intval($st->variation_option_id ?? 0)) {
                                    $savedVal = $x;
                                    break;
                                }
                            }
                            if ($savedVal) {
                                $actualCounted = intval($savedVal['actual_returned_qty']);
                            }
                        }

                        $variance = $actualCounted - $expectedReturned;
                        $varianceClass = '';
                        $varianceStr = '0';
                        if ($variance > 0) {
                            $varianceClass = 'variance-pos';
                            $varianceStr = '+' . $variance;
                        } elseif ($variance < 0) {
                            $varianceClass = 'variance-neg';
                            $varianceStr = (string)$variance;
                        }
                ?>
                    <tr>
                        <td class="text-center"><?= $idx++ ?></td>
                        <td><?= htmlspecialchars($st->item_name) ?></td>
                        <td class="text-center"><?= intval($st->loaded_qty) ?></td>
                        <td class="text-center"><?= intval($st->delivered_qty) ?></td>
                        <td class="text-center"><?= $expectedReturned ?></td>
                        <td class="text-center" style="font-weight: bold;"><?= $actualCounted ?></td>
                        <td class="text-center <?= $varianceClass ?>"><?= $varianceStr ?></td>
                    </tr>
                <?php 
                    endforeach;
                endif; 
                ?>
            </tbody>
        </table>

        <div class="footer-signatures">
            <div class="sig-block">
                Driver Signature
            </div>
            <div class="sig-block">
                Helper / Partner Signature
            </div>
            <div class="sig-block">
                Accounts / Admin Signature
            </div>
        </div>
    </div>
</body>
</html>
