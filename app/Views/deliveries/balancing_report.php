<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Route Summary - <?= htmlspecialchars($data['delivery']->route_name) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .report-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        
        .report-title {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .meta-item {
            font-size: 12.5px;
        }

        .meta-item strong {
            color: #111;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 6px;
            margin: 25px 0 15px 0;
            letter-spacing: 0.3px;
        }

        .kpi-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .kpi-card {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 12px 15px;
            background: #fff;
        }

        .kpi-card-title {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            color: #666;
            margin-bottom: 8px;
            display: block;
        }

        .kpi-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 12.5px;
        }

        .kpi-row:last-child {
            margin-bottom: 0;
        }

        .kpi-row strong {
            font-family: monospace;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        table th, table td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5px;
            color: #444;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .monospace {
            font-family: monospace;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .signature-block {
            border-top: 1px solid #333;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
        }

        .signature-block p {
            margin: 5px 0;
            color: #555;
        }

        .btn-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #007aff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        @media print {
            body {
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>

    <?php 
        $d = $data['delivery'];
        $b = $data['balancing'];

        // Filter delivery-only collections (exclude rep collections where mobile_rep_id is not null/empty)
        $deliveryPayments = array_filter($b['payments'] ?? [], function($p) {
            return empty($p->mobile_rep_id);
        });

        $rawCashCollections = floatval($b['raw_cash_collections'] ?? 0.0);
        $routeExpenses = floatval($b['collected_cash_expenses_total'] ?? 0.0);
        $deliveryCashCollections = max(0.0, $rawCashCollections - $routeExpenses);

        $deliveryChequeCollections = 0.0;
        $deliveryBankCollections = 0.0;
        $deliveryChequeList = [];
        
        foreach ($deliveryPayments as $p) {
            $amt = floatval($p->amount);
            if ($p->payment_method === 'Cheque') {
                $deliveryChequeCollections += $amt;
                $deliveryChequeList[] = (object)[
                    'customer_name' => $p->customer_name,
                    'bank_name' => $p->bank_name,
                    'cheque_number' => $p->cheque_number,
                    'banking_date' => $p->cheque_date ?: ($p->created_at ? date('Y-m-d', strtotime($p->created_at)) : date('Y-m-d')),
                    'amount' => $p->amount
                ];
            } elseif ($p->payment_method === 'Bank Transfer') {
                $deliveryBankCollections += $amt;
            }
        }
    ?>

    <div class="report-header">
        <h1 class="report-title">Route Summary</h1>
        <h2 style="margin: 8px 0 4px 0; font-size: 16px; font-weight: 700; color: #111; text-transform: uppercase;"><?= htmlspecialchars($d->route_name) ?></h2>
        <h3 style="margin: 0 0 5px 0; font-size: 13px; font-weight: 500; color: #555;">Representative: <?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?> &nbsp;|&nbsp; Delivered Date: <?= date('l, F d, Y', strtotime($d->delivery_date)) ?></h3>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            Vehicle Number: <strong><?= htmlspecialchars($d->vehicle_number) ?></strong><br>
            Driver Name: <strong><?= htmlspecialchars($d->driver_name) ?></strong><br>
            Partner/Helper: <strong><?= htmlspecialchars($d->partner_name ?: 'None') ?></strong>
        </div>
        <div class="meta-item">
            Arranged At: <strong><?= date('M d, Y h:i A', strtotime($d->created_at)) ?></strong><br>
            Status: <span class="badge badge-success"><?= $d->status ?></span><br>
            Audit Run Date: <strong><?= date('Y-m-d H:i:s') ?></strong>
        </div>
    </div>

    <div class="section-title">📊 Trip Financial Summary</div>
    <div class="kpi-container">
        <div class="kpi-card">
            <span class="kpi-card-title">Sales Summary</span>
            <div class="kpi-row">
                <span>Cash Sales:</span>
                <strong style="color: #2e7d32;">Rs <?= number_format($b['cash_sales'], 2) ?></strong>
            </div>
            <div class="kpi-row">
                <span>Cheque Sales:</span>
                <strong style="color: #2e7d32;">Rs <?= number_format($b['cheque_sales'], 2) ?></strong>
            </div>
            <div class="kpi-row">
                <span>Bank Transfer Sales:</span>
                <strong style="color: #2e7d32;">Rs <?= number_format($b['bank_sales'], 2) ?></strong>
            </div>
            <div class="kpi-row">
                <span>Credit Sales:</span>
                <strong style="color: #ef6c00;">Rs <?= number_format($b['credit_sales'], 2) ?></strong>
            </div>
            <div class="kpi-row" style="border-top: 1px dashed #ccc; padding-top: 4px; margin-top: 6px; font-weight: bold; font-size: 13.5px;">
                <span>Total Sales:</span>
                <strong>Rs <?= number_format($b['cash_sales'] + $b['cheque_sales'] + $b['bank_sales'] + $b['credit_sales'], 2) ?></strong>
            </div>
        </div>

        <div class="kpi-card">
            <span class="kpi-card-title">Vehicle Info</span>
            <div class="kpi-row">
                <span>Vehicle Number:</span>
                <strong><?= htmlspecialchars($d->vehicle_number) ?></strong>
            </div>
            <div class="kpi-row">
                <span>Start Odometer:</span>
                <strong><?= number_format($d->start_meter, 0) ?> KM</strong>
            </div>
            <div class="kpi-row">
                <span>End Odometer:</span>
                <strong><?= number_format($d->end_meter, 0) ?> KM</strong>
            </div>
            <?php $dist = max(0, $d->end_meter - $d->start_meter); ?>
            <div class="kpi-row" style="border-top: 1px dashed #ccc; padding-top: 4px; margin-top: 6px; font-weight: bold;">
                <span>Distance Traveled:</span>
                <strong><?= number_format($dist, 0) ?> KM</strong>
            </div>
        </div>
    </div>

    <div class="section-title">💵 Cash Count Denomination Audit</div>
    <div style="display: flex; gap: 40px; margin-bottom: 25px;">
        <div style="flex: 1;">
            <?php 
                $cashDenoms = [];
                $totalCashEntered = 0.0;
                $hasRecon = false;
                
                if (!empty($d->reconciliation_json)) {
                    try {
                        $recon = json_decode($d->reconciliation_json, true);
                        if ($recon && isset($recon['actual_cash'])) {
                            $cashDenoms = $recon['actual_denominations'] ?? [];
                            $hasRecon = true;
                        }
                    } catch(Exception $e) {}
                }
                
                if (!$hasRecon && !empty($d->cash_denominations)) {
                    try {
                        $cashDenoms = json_decode($d->cash_denominations, true);
                    } catch(Exception $e) {}
                }
                
                $denomList = [5000, 2000, 1000, 500, 100, 50, 20, 'coins'];
                $hasEntries = false;
            ?>
            <table>
                <thead>
                    <tr>
                        <th>Denomination</th>
                        <th class="text-center">Count</th>
                        <th class="text-right">Total Amount (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($denomList as $den): ?>
                        <?php 
                            $count = isset($cashDenoms[$den]) ? intval($cashDenoms[$den]) : 0;
                            $val = 0.0;
                            $label = '';
                            if ($den === 'coins') {
                                $val = isset($cashDenoms['coins']) ? floatval($cashDenoms['coins']) : 0.0;
                                $label = 'Coins';
                            } else {
                                $val = $den * $count;
                                $label = 'Rs ' . $den;
                            }
                            $totalCashEntered += $val;
                            if ($val <= 0) {
                                continue;
                            }
                            $hasEntries = true;
                        ?>
                        <tr>
                            <td style="font-weight: 600;"><?= $label ?></td>
                            <td class="text-center"><?= $den === 'coins' ? '-' : $count ?></td>
                            <td class="text-right monospace"><?= number_format($val, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$hasEntries): ?>
                        <tr>
                            <td colspan="3" class="text-center" style="color: #666; padding: 10px;">No cash denominations entered.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="width: 320px; background: #fafafa; border: 1px solid #ccc; padding: 20px; border-radius: 6px; display: flex; flex-direction: column; justify-content: center; height: fit-content; margin-top: 10px;">
            <div class="kpi-row" style="margin-bottom: 12px;">
                <span>Expected Cash Collections:</span>
                <strong class="monospace">Rs <?= number_format($deliveryCashCollections, 2) ?></strong>
            </div>
            <div class="kpi-row" style="margin-bottom: 12px;">
                <span>Actual Cash Count Entered:</span>
                <strong class="monospace">Rs <?= number_format($totalCashEntered, 2) ?></strong>
            </div>
            <?php $variance = $totalCashEntered - $deliveryCashCollections; ?>
            <div class="kpi-row" style="border-top: 1px solid #aaa; padding-top: 12px; font-weight: bold; font-size: 14px;">
                <span>Variance (Difference):</span>
                <strong class="monospace" style="color: <?= abs($variance) < 0.01 ? '#2e7d32' : ($variance < 0 ? '#c62828' : '#ef6c00') ?>;">
                    <?= $variance >= 0 ? '+' : '' ?>Rs <?= number_format($variance, 2) ?>
                </strong>
            </div>
        </div>
    </div>

    <div class="section-title">💸 Recorded Route Expenses</div>
    <table>
        <thead>
            <tr>
                <th>Expense Type</th>
                <th>Description</th>
                <th>Payment Source</th>
                <th>Reference/Details</th>
                <th class="text-right">Amount (Rs)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($b['expenses'])): ?>
                <tr>
                    <td colspan="5" class="text-center" style="color: #666; padding: 15px;">No expenses recorded on this trip.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($b['expenses'] as $exp): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($exp->expense_type) ?></td>
                        <td><?= htmlspecialchars($exp->description) ?></td>
                        <td><span class="badge badge-success" style="background:#f1f5f9; color:#334155; border:1px solid #e2e8f0;"><?= htmlspecialchars($exp->payment_source) ?></span></td>
                        <td><?= htmlspecialchars($exp->vehicle_number ? 'Veh: ' . $exp->vehicle_number : '') ?></td>
                        <td class="text-right monospace" style="font-weight: 700;">Rs <?= number_format($exp->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">💳 PDC Cheques Collected Audit</div>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Bank Name</th>
                <th>Cheque Number</th>
                <th>Banking Date</th>
                <th class="text-right">Amount (Rs)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($deliveryChequeList)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="color: #666; padding: 15px;">No cheques collected on this trip.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($deliveryChequeList as $ch): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($ch->customer_name) ?></td>
                        <td><?= htmlspecialchars($ch->bank_name) ?></td>
                        <td class="monospace"><?= htmlspecialchars($ch->cheque_number) ?></td>
                        <td><?= date('M d, Y', strtotime($ch->banking_date)) ?></td>
                        <td class="text-right monospace" style="font-weight: 700;"><?= number_format($ch->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-block">
            <div style="height: 60px;"></div>
            <p><strong>Driver Signature</strong></p>
            <p>Name: <?= htmlspecialchars($d->driver_name) ?></p>
        </div>
        <div class="signature-block">
            <div style="height: 60px;"></div>
            <p><strong>Partner / Helper Signature</strong></p>
            <p>Name: <?= htmlspecialchars($d->partner_name ?: '_____________________') ?></p>
        </div>
        <div class="signature-block">
            <div style="height: 60px;"></div>
            <p><strong>Auditing Administrator Signature</strong></p>
            <p>Name: _______________________________</p>
        </div>
    </div>

</body>
</html>
