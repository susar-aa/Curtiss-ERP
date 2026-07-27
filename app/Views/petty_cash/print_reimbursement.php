<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Petty Cash Reimbursement Voucher - REIM-<?= $data['reim']->id ?></title>
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
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: right;
            color: #222;
        }
        .voucher-no {
            font-size: 13pt;
            font-weight: bold;
            text-align: right;
            color: #1b5e20;
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
            margin-bottom: 25px;
        }
        .details-table th {
            background-color: #e8f5e9;
            color: #1b5e20;
            text-align: left;
            padding: 10px 8px;
            border: 1px solid #c8e6c9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .details-table td {
            padding: 8px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
            font-size: 9pt;
        }
        .amount-box {
            font-size: 13pt;
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
            <a href="<?= APP_URL ?>/pettycash/download_reimbursement_pdf/<?= $data['reim']->id ?>" class="btn btn-pdf">Save as PDF</a>
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
                    <div class="document-title">Petty Cash Reimbursement</div>
                    <div class="voucher-no">REIM-<?= $data['reim']->id ?></div>
                </td>
            </tr>
        </table>

        <!-- Metadata Info -->
        <table class="meta-table">
            <tr>
                <th>Reimbursement Date</th>
                <td><?= date('d-M-Y', strtotime($data['reim']->reimbursement_date)) ?></td>
                <th>Status</th>
                <td>
                    <span style="font-weight: bold; text-transform: uppercase; color: <?= $data['reim']->status === 'Approved' ? '#2e7d32' : '#f57c00' ?>;">
                        <?= htmlspecialchars($data['reim']->status) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Bank / Funding Source</th>
                <td><?= htmlspecialchars($data['reim']->bank_account_code) ?> - <?= htmlspecialchars($data['reim']->bank_account_name) ?></td>
                <th>Journal Entry Ref</th>
                <td><?= $data['reim']->journal_entry_id ? 'J/E ID: ' . $data['reim']->journal_entry_id : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Remarks / Description</th>
                <td colspan="3"><?= htmlspecialchars($data['reim']->description ?: 'N/A') ?></td>
            </tr>
        </table>

        <!-- Details Table -->
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Voucher No</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 20%;">Payee / Category</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 15%; text-align: right;">Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['expenses'] as $exp): ?>
                    <tr>
                        <td><strong style="font-family: monospace; font-size: 11px;"><?= htmlspecialchars($exp->voucher_number ?: 'N/A') ?></strong></td>
                        <td><?= date('d-M-Y', strtotime($exp->transaction_date)) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($exp->paid_to ?: 'N/A') ?></strong>
                            <br><span style="font-size: 10px; color: #555;"><?= htmlspecialchars($exp->offset_account_name) ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($exp->description) ?>
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            Rs. <?= number_format((float)$exp->amount, 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Amount Box -->
        <div class="amount-box">
            Total Payout Amount: <span style="color: #d32f2f; margin-right: 15px;">Rs. <?= number_format((float)$data['reim']->amount, 2) ?></span>
            <br><span style="font-size: 10pt; font-weight: normal; font-style: italic; color: #111;">Amount in Words: <?= htmlspecialchars($data['amount_in_words']) ?></span>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line">
                            <?php if ($data['reim']->creator_name): ?>
                                <div style="font-size: 9pt; color: #555; text-align: center; padding-top: 20px;">
                                    <?= htmlspecialchars($data['reim']->creator_name) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="signature-label">Prepared By / Custodian</div>
                    </td>
                    <td>
                        <div class="signature-line">
                            <?php if ($data['reim']->status === 'Approved' && $data['reim']->approver_name): ?>
                                <div style="font-size: 9pt; color: #555; text-align: center; padding-top: 20px;">
                                    <?= htmlspecialchars($data['reim']->approver_name) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="signature-label">Authorized / Disbursed By</div>
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
