

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Employee Loans</h2>
    <a href="<?= APP_URL ?>/employeeloan/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Employee Loan
    </a>
</div>

<?php if(!empty($data['success'])): ?>
    <div class="alert alert-success"><?= $data['success'] ?></div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div class="alert alert-danger"><?= $data['error'] ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Loan No</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Principal</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['loans'] as $loan): ?>
                    <tr>
                        <td><?= htmlspecialchars($loan->loan_number) ?></td>
                        <td>
                            <a href="<?= APP_URL ?>/user/show/<?= $loan->employee_id ?>">
                                <?= htmlspecialchars($loan->employee_name) ?>
                            </a>
                        </td>
                        <td><?= date('M d, Y', strtotime($loan->loan_start_date)) ?></td>
                        <td><?= number_format($loan->principal_amount, 2) ?></td>
                        <td><?= number_format($loan->principal_balance, 2) ?></td>
                        <td>
                            <?php 
                                $badge = 'secondary';
                                if($loan->status == 'Active') $badge = 'primary';
                                if($loan->status == 'Approved') $badge = 'info';
                                if($loan->status == 'Closed') $badge = 'success';
                                if($loan->status == 'Rejected') $badge = 'danger';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= $loan->status ?></span>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/employeeloan/show/<?= $loan->id ?>" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


