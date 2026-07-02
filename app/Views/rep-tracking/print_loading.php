<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Loading Report - <?= htmlspecialchars($data['route']->route_name) ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
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
            max-width: 190mm;
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

        <div class="header-section">
            <h1>
                <?php 
                if ($data['type'] === 'final') {
                    echo 'Final Loading Verification Sheet';
                } elseif ($data['type'] === 'summary') {
                    echo 'Route Loading Summary & Dispatch Report';
                } else {
                    echo 'Pre-Loading Picking Sheet';
                }
                ?>
            </h1>
            <div class="meta-grid">
                <div class="meta-group">
                    <p><strong>Route Name:</strong> <?= htmlspecialchars($data['route']->route_name) ?></p>
                    <p><strong>Assigned Rep:</strong> <?= htmlspecialchars($data['route']->first_name . ' ' . $data['route']->last_name) ?></p>
                    <p><strong>Printed On:</strong> <?= date('Y-m-d g:i A') ?></p>
                    <?php 
                    $db = new Database();
                    $db->query("SELECT * FROM deliveries WHERE rep_route_id = :rid OR secondary_rep_route_id = :rid LIMIT 1");
                    $db->bind(':rid', $data['route']->id);
                    $delivery = $db->single();
                    if ($data['type'] === 'summary' && $delivery): 
                    ?>
                        <p><strong>Delivery Date:</strong> <?= htmlspecialchars($delivery->delivery_date) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($data['type'] === 'summary'): ?>
                    <div class="meta-group">
                        <p><strong>Vehicle Number:</strong> <?= $delivery ? htmlspecialchars($delivery->vehicle_number) : 'Pending' ?></p>
                        <p><strong>Driver Name:</strong> <?= $delivery ? htmlspecialchars($delivery->driver_name) : 'Pending' ?></p>
                        <?php if ($delivery && !empty($delivery->partner_name)): ?>
                            <p><strong>Helper/Partner:</strong> <?= htmlspecialchars($delivery->partner_name) ?></p>
                        <?php endif; ?>
                        <p><strong>Departure Odometer:</strong> <?= ($delivery && $delivery->start_meter > 0) ? floatval($delivery->start_meter) . ' km' : '_________________ km' ?></p>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>
            </div>
        </div>

        <?php if ($data['type'] === 'final'): ?>
            <!-- FINAL LOADING TABLE -->
            <table>
                <thead>
                    <tr>
                        <th style="width:5%; text-align:center;">No</th>
                        <th style="text-align:left; width:50%;">Product Name</th>
                        <th style="text-align:center; width:15%;">Qty</th>
                        <th style="text-align:right; width:15%;">Unit Price</th>
                        <th style="width:15%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    if (empty($data['items'])): 
                    ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 15px; color: #555;">No picking items verified for final loading.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['items'] as $item): ?>
                            <tr>
                                <td style="text-align:center;"><?= $counter++ ?></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($item->item_name) ?></td>
                                <td style="text-align:center; font-family: monospace; font-size:11px; font-weight:bold;"><?= $item->final_loaded_qty !== null ? floatval($item->final_loaded_qty) : floatval($item->required_qty) ?></td>
                                <td style="text-align:right; font-family: monospace; font-size:11px;"><?= number_format($item->unit_price, 2) ?></td>
                                <td style="text-align:center;"><span class="tick-box-placeholder"></span></td>
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

        <?php elseif ($data['type'] === 'summary'): ?>
            <!-- SECTION 1: LOADED PRODUCTS SUMMARY -->
            <h3 style="margin-top: 15px; margin-bottom: 5px; font-size: 12px; text-transform: uppercase;">1. Loaded Products Summary (Warehouse Loading Sheet)</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 45%; text-align: left;">Product / Item Description</th>
                        <th style="width: 15%; text-align: center;">Qty to Load</th>
                        <th style="width: 15%; text-align: right;">Unit Price (Rs)</th>
                        <th style="width: 20%; text-align: right;">Total Value (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $itemCounter = 1;
                    $totalItemsQty = 0;
                    $totalItemsVal = 0;
                    if (empty($data['items'])): 
                    ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 15px; color: #555;">No products required for loading on this route.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $currentCategory = '';
                        foreach ($data['items'] as $item): 
                            if ($item->category_name !== $currentCategory): 
                                $currentCategory = $item->category_name;
                        ?>
                                <tr class="category-header-row">
                                    <td colspan="5" style="font-weight: bold; background-color: #f1f3f9; padding: 4px 6px; font-size: 10px;"><?= htmlspecialchars($currentCategory) ?></td>
                                </tr>
                        <?php 
                            endif;
                            $qty = floatval($item->total_qty);
                            $price = floatval($item->unit_price);
                            $val = $qty * $price;
                            $totalItemsQty += $qty;
                            $totalItemsVal += $val;
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $itemCounter++ ?></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($item->item_name) ?></td>
                                <td style="text-align: center; font-family: monospace; font-weight: bold;"><?= $qty ?></td>
                                <td style="text-align: right; font-family: monospace;"><?= number_format($price, 2) ?></td>
                                <td style="text-align: right; font-family: monospace; font-weight: bold;"><?= number_format($val, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #eaeaea; font-weight: bold;">
                            <td colspan="2" style="text-align: right; text-transform: uppercase;">Total Products Loaded:</td>
                            <td style="text-align: center; font-family: monospace;"><?= $totalItemsQty ?></td>
                            <td></td>
                            <td style="text-align: right; font-family: monospace; font-size: 11px;">Rs. <?= number_format($totalItemsVal, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- SECTION 2: SALES INVOICES / BILLS ON ROUTE -->
            <h3 style="margin-top: 20px; margin-bottom: 5px; font-size: 12px; text-transform: uppercase;">2. Sales Invoices / Bills on Route</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 25%; text-align: left;">Customer Name</th>
                        <th style="width: 25%; text-align: left;">Invoice / Bill No</th>
                        <th style="width: 20%; text-align: center;">Date & Time</th>
                        <th style="width: 15%; text-align: right;">Grand Total (Rs)</th>
                        <th style="width: 10%; text-align: center;">OIC Verify</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $billCounter = 1;
                    $routeBillsTotal = 0;
                    if (empty($data['bills'])):
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 15px; color: #555;">No active sales bills on this route.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['bills'] as $bill): 
                            $routeBillsTotal += floatval($bill->true_grand_total);
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $billCounter++ ?></td>
                                <td style="font-weight: bold;"><?= htmlspecialchars($bill->customer_name) ?></td>
                                <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($bill->invoice_number) ?></td>
                                <td style="text-align: center;"><?= date('Y-m-d g:i A', strtotime($bill->created_at)) ?></td>
                                <td style="text-align: right; font-family: monospace; font-weight: bold;">Rs. <?= number_format($bill->true_grand_total, 2) ?></td>
                                <td style="text-align: center;"><span class="tick-box-placeholder"></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #eaeaea; font-weight: bold;">
                            <td colspan="4" style="text-align: right; text-transform: uppercase;">Route Bills Subtotal:</td>
                            <td style="text-align: right; font-family: monospace; font-size: 11px;">Rs. <?= number_format($routeBillsTotal, 2) ?></td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- SECTION 3: ATTACHED OUTSTANDING CREDIT BILLS -->
            <h3 style="margin-top: 20px; margin-bottom: 5px; font-size: 12px; text-transform: uppercase;">3. Attached Outstanding Credit Bills</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 30%; text-align: left;">Customer Name</th>
                        <th style="width: 25%; text-align: left;">Credit Bill No</th>
                        <th style="width: 15%; text-align: center;">Invoice Date</th>
                        <th style="width: 15%; text-align: right;">Outstanding (Rs)</th>
                        <th style="width: 10%; text-align: center;">OIC Verify</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $creditCounter = 1;
                    $creditBillsTotal = 0;
                    if (empty($data['credit_bills'])):
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 15px; color: #555;">No attached outstanding credit bills.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['credit_bills'] as $cb): 
                            $creditBillsTotal += floatval($cb->true_grand_total);
                        ?>
                            <tr style="background-color: #fff9f9;">
                                <td style="text-align: center;"><?= $creditCounter++ ?></td>
                                <td style="font-weight: bold; color: #b71c1c;"><?= htmlspecialchars($cb->customer_name) ?></td>
                                <td style="font-family: monospace; font-weight: bold; color: #b71c1c;"><?= htmlspecialchars($cb->invoice_number) ?></td>
                                <td style="text-align: center;"><?= htmlspecialchars($cb->invoice_date) ?></td>
                                <td style="text-align: right; font-family: monospace; font-weight: bold; color: #b71c1c;">Rs. <?= number_format($cb->true_grand_total, 2) ?></td>
                                <td style="text-align: center;"><span class="tick-box-placeholder"></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #eaeaea; font-weight: bold;">
                            <td colspan="4" style="text-align: right; text-transform: uppercase;">Attached Credit Subtotal:</td>
                            <td style="text-align: right; font-family: monospace; font-size: 11px; color: #b71c1c;">Rs. <?= number_format($creditBillsTotal, 2) ?></td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- SUMMARY CONSOLIDATION BLOCK -->
            <div style="margin-top: 20px; border: 2px solid #000; border-radius: 4px; padding: 10px; background: #fafafa; margin-bottom: 20px;">
                <h3 style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 4px;">Logistics & Dispatch Summary</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 11px;">
                    <div>
                        <strong>Route Sales Subtotal:</strong><br>
                        Rs. <?= number_format($routeBillsTotal, 2) ?>
                    </div>
                    <div>
                        <strong>Attached Credit Subtotal:</strong><br>
                        Rs. <?= number_format($creditBillsTotal, 2) ?>
                    </div>
                    <div>
                        <strong style="font-size: 12px; color: #000;">Total Document Value:</strong><br>
                        <span style="font-size: 12px; font-weight: bold; font-family: monospace;">Rs. <?= number_format($routeBillsTotal + $creditBillsTotal, 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: CUSTOMER DISPATCH BREAKDOWN / CHECKLIST -->
            <h3 style="margin-top: 20px; margin-bottom: 5px; font-size: 12px; text-transform: uppercase;">4. Logistics & Gate Pass Checklist</h3>
            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 15px; margin-top: 5px;">
                <div style="border: 1px solid #ccc; border-radius: 4px; padding: 10px; background: #fafafa;">
                    <h4 style="margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase;">Security & Loading Verification Checklist</h4>
                    <ul style="margin: 0; padding-left: 15px; line-height: 1.6;">
                        <li>Verify physical product counts match Section 1 totals.</li>
                        <li>Verify all Section 2 invoice documents are loaded and signed.</li>
                        <li>Verify all Section 3 credit bill documents are present.</li>
                        <li>Confirm driver license, vehicle road worthiness and helpers count.</li>
                    </ul>
                </div>
                <div style="border: 1px solid #ccc; border-radius: 4px; padding: 10px; background: #fafafa; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 5px 0; font-size: 11px; text-transform: uppercase;">Dispatch Log Notes</h4>
                        <p style="margin: 2px 0;">Helper Crew: ____________________________________</p>
                        <p style="margin: 2px 0;">Security Seal No: _________________________________</p>
                        <p style="margin: 2px 0;">Gate Pass Approved Time: ________________________</p>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- PRE LOADING TABLE -->
            <table>
                <thead>
                    <tr>
                        <th style="width:5%; text-align:center;">No</th>
                        <th style="text-align:left; width:50%;">Product Name</th>
                        <th style="text-align:center; width:15%;">Qty</th>
                        <th style="text-align:right; width:15%;">Unit Price</th>
                        <th style="width:15%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    if (empty($data['items'])): 
                    ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 15px; color: #555;">No items found for pre-loading on this route.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['items'] as $item): ?>
                            <tr>
                                <td style="text-align:center;"><?= $counter++ ?></td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($item->item_name) ?></td>
                                <td style="text-align:center; font-family: monospace; font-size:12px; font-weight:bold;"><?= floatval($item->total_qty) ?></td>
                                <td style="text-align:right; font-family: monospace; font-size:11px;"><?= number_format($item->unit_price, 2) ?></td>
                                <td style="text-align:center;"><span class="tick-box-placeholder"></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

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
    </div>
</body>
</html>