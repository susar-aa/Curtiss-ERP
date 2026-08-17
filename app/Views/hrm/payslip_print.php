<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #fff; color: #000; padding: 20px; }
        .payslip-container { max-width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; }
        .slip-title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        .emp-details { margin-bottom: 30px; }
        .table-totals { font-weight: bold; background-color: #f8f9fa; }
        .net-pay { font-size: 20px; font-weight: bold; color: #198754; text-align: right; }
        @media print {
            body { padding: 0; }
            .payslip-container { border: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

<div class="mb-3 text-center btn-print">
    <button onclick="window.print()" class="btn btn-primary">Print Payslip</button>
</div>

<?php $slip = $data['slip']; ?>

<div class="payslip-container">
    <div class="header">
        <div class="company-name">Curtiss ERP</div>
        <div class="slip-title">Payslip</div>
        <div>Period: <?= date('M d', strtotime($slip->period_start)) ?> - <?= date('M d, Y', strtotime($slip->period_end)) ?></div>
    </div>

    <div class="row emp-details">
        <div class="col-6">
            <strong>Employee Name:</strong> <?= htmlspecialchars($slip->first_name . ' ' . $slip->last_name) ?><br>
            <strong>Job Title:</strong> <?= htmlspecialchars($slip->job_title) ?><br>
            <strong>Department:</strong> <?= htmlspecialchars($slip->department) ?>
        </div>
        <div class="col-6 text-end">
            <strong>Payslip ID:</strong> PS-<?= $slip->id ?><br>
            <strong>Status:</strong> <?= $slip->status ?><br>
            <?php if($slip->payment_date): ?>
                <strong>Payment Date:</strong> <?= date('M d, Y', strtotime($slip->payment_date)) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <table class="table table-bordered">
                <thead>
                    <tr class="table-light"><th colspan="2">Earnings</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td class="text-end"><?= number_format($slip->basic_salary, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Allowances</td>
                        <td class="text-end"><?= number_format($slip->allowances, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Commissions</td>
                        <td class="text-end"><?= number_format($slip->commissions, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Overtime</td>
                        <td class="text-end"><?= number_format($slip->overtime, 2) ?></td>
                    </tr>
                    <tr class="table-totals">
                        <td>Total Earnings (Gross)</td>
                        <td class="text-end"><?= number_format($slip->gross_salary, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-bordered">
                <thead>
                    <tr class="table-light"><th colspan="2">Deductions</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Loan Repayment</td>
                        <td class="text-end"><?= number_format($slip->loan_deduction, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Other Deductions</td>
                        <td class="text-end"><?= number_format($slip->other_deductions, 2) ?></td>
                    </tr>
                    <tr class="table-totals">
                        <td>Total Deductions</td>
                        <td class="text-end"><?= number_format($slip->loan_deduction + $slip->other_deductions, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 p-3 border">
        <div class="row align-items-center">
            <div class="col-6 text-end fs-5 fw-bold">Net Pay:</div>
            <div class="col-6 net-pay">Rs. <?= number_format($slip->net_salary, 2) ?></div>
        </div>
    </div>
    
    <div class="mt-5 pt-5 row text-center">
        <div class="col-6">
            <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">Employer Signature</div>
        </div>
        <div class="col-6">
            <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">Employee Signature</div>
        </div>
    </div>
</div>

</body>
</html>
