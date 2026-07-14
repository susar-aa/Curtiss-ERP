<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Petty Cash Audit & Ledger Report</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1a1a2e;
            padding: 40px;
            margin: 0;
            background: #fff;
            font-size: 13px;
            line-height: 1.5;
        }

        .report-header {
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .report-title h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .report-title p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .report-meta {
            text-align: right;
            font-size: 12px;
            color: #4b5563;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .summary-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
        }

        .summary-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
        }

        .pc-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .pc-table th, .pc-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .pc-table th {
            background: #f3f4f6;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            color: #374151;
            letter-spacing: 0.5px;
        }

        .tx-debit { color: #059669; font-weight: 700; }
        .tx-credit { color: #dc2626; font-weight: 700; }
        .running-balance { font-weight: 700; }

        .print-btn-bar {
            background: #f3f4f6;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            background: #1a1a2e;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .sig-box {
            border-top: 1px dashed #9ca3af;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
            color: #4b5563;
        }

        @media print {
            .print-btn-bar {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <span>Ready for export / physical print.</span>
        <div>
            <button class="btn" onclick="window.print()">Print Document</button>
            <button class="btn" style="background:#4b5563; margin-left:8px;" onclick="window.close()">Close Window</button>
        </div>
    </div>

    <div class="report-header">
        <div class="report-title">
            <h1>Curtiss-ERP</h1>
            <p>Petty Cash Audit & Ledger History Report</p>
        </div>
        <div class="report-meta">
            <div><strong>Date Generated:</strong> <?= date('Y-m-d H:i:s') ?></div>
            <div><strong>Primary Ledger Account:</strong> 1020 - Petty Cash</div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="summary-grid">
        <div class="summary-box">
            <div class="summary-label">Current Balance</div>
            <div class="summary-value">Rs. <?= number_format($data['summary']['current_balance'], 2) ?></div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Allocated Limit</div>
            <div class="summary-value">Rs. <?= number_format($data['summary']['limit_amount'], 2) ?></div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Available Balance</div>
            <div class="summary-value">Rs. <?= number_format($data['summary']['available_balance'], 2) ?></div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Pending Claim Amount</div>
            <div class="summary-value">Rs. <?= number_format($data['summary']['pending_approvals_amount'] ?? 0, 2) ?></div>
        </div>
    </div>

    <!-- Filter details if any -->
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 18px; margin-bottom: 20px;">
        <strong>Report Parameters:</strong>
        <span style="margin-left: 15px;">
            <strong>Start Date:</strong> <?= htmlspecialchars($data['filters']['start_date'] ?: 'Beginning of time') ?>
        </span>
        <span style="margin-left: 15px;">
            <strong>End Date:</strong> <?= htmlspecialchars($data['filters']['end_date'] ?: 'Present') ?>
        </span>
        <span style="margin-left: 15px;">
            <strong>Transaction Type:</strong> <?= htmlspecialchars($data['filters']['tx_type'] ?: 'All') ?>
        </span>
        <span style="margin-left: 15px;">
            <strong>Category:</strong> <?= htmlspecialchars($data['filters']['category'] ?: 'All') ?>
        </span>
    </div>

    <!-- Ledger Table -->
    <table class="pc-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 15%;">Reference / Voucher</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 28%;">Details / Category</th>
                <th style="width: 10%;">Debit (Rs.)</th>
                <th style="width: 10%;">Credit (Rs.)</th>
                <th style="width: 10%;">Balance (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['ledger'])): ?>
                <tr><td colspan="7" style="text-align: center; color: #6b7280; padding: 30px 0;">No ledger records found.</td></tr>
            <?php else: ?>
                <?php foreach($data['ledger'] as $row): ?>
                    <tr>
                        <td><?= date('Y-m-d H:i', strtotime($row['tx_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($row['reference_number']) ?></strong></td>
                        <td><?= htmlspecialchars($row['tx_type']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($row['remarks']) ?></div>
                            <?php if (!empty($row['category'])): ?>
                                <small style="color: #6b7280; font-weight: bold;"><?= htmlspecialchars($row['category']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($row['debit'] > 0): ?>
                                <span class="tx-debit">+<?= number_format($row['debit'], 2) ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($row['credit'] > 0): ?>
                                <span class="tx-credit">-<?= number_format($row['credit'], 2) ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <span class="running-balance"><?= number_format($row['running_balance'], 2) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature fields -->
    <div class="signature-section">
        <div class="sig-box" style="margin-top: 40px;">
            Prepared By (Custodian)
        </div>
        <div class="sig-box" style="margin-top: 40px;">
            Authorized By (Accountant / Admin)
        </div>
    </div>

</body>
</html>
