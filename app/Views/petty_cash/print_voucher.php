<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Petty Cash Voucher - <?= htmlspecialchars($data['tx']->voucher_number ?: (string)$data['tx']->id) ?></title>
    <style>
        body {
            font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #111;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        .voucher-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            box-sizing: border-box;
            position: relative;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .company-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1b5e20;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: right;
            color: #222;
        }
        .voucher-no {
            font-size: 13pt;
            font-weight: bold;
            text-align: right;
            color: #d32f2f;
            margin-top: 5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
        }
        .meta-table th {
            text-align: left;
            padding: 8px 12px;
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            width: 25%;
        }
        .meta-table td {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            font-size: 10pt;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table th {
            background-color: #e8f5e9;
            color: #1b5e20;
            text-align: left;
            padding: 10px 12px;
            border: 1px solid #c8e6c9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
        }
        .details-table td {
            padding: 12px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }
        .amount-box {
            font-size: 14pt;
            font-weight: bold;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            padding: 12px;
            margin-bottom: 30px;
            color: #1b5e20;
        }
        .signature-section {
            width: 100%;
            margin-top: 50px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 10px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 8px auto;
            height: 40px;
        }
        .signature-label {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
        }
        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 15px auto;
            text-align: right;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 9.5pt;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 8px;
        }
        .btn-print {
            background-color: #1b5e20;
            color: #fff;
            border: 1px solid #1b5e20;
        }
        .btn-pdf {
            background-color: #d32f2f;
            color: #fff;
            border: 1px solid #d32f2f;
        }
        .btn-close {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ccc;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .voucher-container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <?php if (empty($data['is_pdf'])): ?>
        <div class="no-print-bar">
            <button onclick="window.print();" class="btn btn-print">Print Voucher</button>
            <a href="<?= APP_URL ?>/pettycash/download_pdf/<?= $data['tx']->id ?>" class="btn btn-pdf">Save as PDF</a>
            <button onclick="window.close();" class="btn btn-close">Close Window</button>
        </div>
    <?php endif; ?>

    <div class="voucher-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <div class="company-title"><?= htmlspecialchars($data['company']->company_name) ?></div>
                    <div style="font-size: 9pt; color: #555; line-height: 1.3;">
                        <?= !empty($data['company']->address) ? nl2br(htmlspecialchars($data['company']->address)) . '<br>' : '' ?>
                        <?= !empty($data['company']->phone) ? 'Tel: ' . htmlspecialchars($data['company']->phone) . '<br>' : '' ?>
                        <?= !empty($data['company']->email) ? 'Email: ' . htmlspecialchars($data['company']->email) : '' ?>
                    </div>
                </td>
                <td>
                    <div class="document-title">Petty Cash Voucher</div>
                    <div class="voucher-no"><?= htmlspecialchars($data['tx']->voucher_number ?: 'PCV-PENDING') ?></div>
                </td>
            </tr>
        </table>

        <!-- Metadata Info -->
        <table class="meta-table">
            <tr>
                <th>Voucher Date</th>
                <td><?= date('d-M-Y H:i', strtotime($data['tx']->created_at ?: $data['tx']->transaction_date)) ?></td>
                <th>Transaction Type</th>
                <td>
                    <span style="font-weight: bold; text-transform: uppercase;">
                        <?= htmlspecialchars($data['tx']->type) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Payment Source</th>
                <td><?= htmlspecialchars($data['tx']->reference ?: 'N/A') ?></td>
                <th>Journal Entry Ref</th>
                <td><?= $data['tx']->journal_entry_id ? 'J/E ID: ' . $data['tx']->journal_entry_id : 'N/A' ?></td>
            </tr>
            <?php if (!empty($data['tx']->route_name)): ?>
                <tr>
                    <th>Linked Route</th>
                    <td><?= htmlspecialchars($data['tx']->route_name) ?></td>
                    <th>Vehicle & Rep</th>
                    <td>
                        <?= htmlspecialchars($data['tx']->vehicle_number ?: 'N/A') ?> 
                        (<?= htmlspecialchars($data['tx']->rep_first_name . ' ' . $data['tx']->rep_last_name) ?>)
                    </td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Details Table -->
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 70%;">Description & Category</th>
                    <th style="width: 30%; text-align: right;">Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: bold; font-size: 11pt; margin-bottom: 5px;">
                            <?= htmlspecialchars($data['tx']->description) ?>
                        </div>
                        <div style="font-size: 9pt; color: #555;">
                            <?php if ($data['tx']->offset_account_name): ?>
                                Account: <?= htmlspecialchars($data['tx']->offset_account_code) ?> - <?= htmlspecialchars($data['tx']->offset_account_name) ?>
                            <?php else: ?>
                                Account: Petty Cash Control
                            <?php endif; ?>
                        </div>
                        <?php if ($data['tx']->paid_to): ?>
                            <div style="margin-top: 10px; font-size: 9.5pt;">
                                <strong>Paid To / Payee:</strong> <?= htmlspecialchars($data['tx']->paid_to) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 12pt;">
                        Rs. <?= number_format((float)$data['tx']->amount, 2) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Amount Box -->
        <div class="amount-box">
            Amount in Words: <span style="font-weight: normal; font-style: italic; color: #111; margin-left: 5px;"><?= htmlspecialchars($data['amount_in_words']) ?></span>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line">
                            <?php if ($data['tx']->creator_name): ?>
                                <div style="font-size: 9pt; color: #555; text-align: center; padding-top: 20px;">
                                    <?= htmlspecialchars($data['tx']->creator_name) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="signature-label">Prepared By</div>
                    </td>
                    <td>
                        <div class="signature-line">
                            <?php if ($data['tx']->status === 'Approved' && $data['tx']->approver_name): ?>
                                <div style="font-size: 9pt; color: #555; text-align: center; padding-top: 20px;">
                                    <?= htmlspecialchars($data['tx']->approver_name) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="signature-label">Approved By</div>
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">Received By</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
