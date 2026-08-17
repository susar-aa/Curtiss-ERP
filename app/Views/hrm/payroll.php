

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payroll Runs</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPayrollModal">
        <i class="bi bi-play-circle"></i> Run New Payroll
    </button>
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
                    <th>Ref ID</th>
                    <th>Period</th>
                    <th>Run Date</th>
                    <th>Total Gross</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['payroll_runs'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">No payroll runs found.</td></tr>
                <?php else: foreach($data['payroll_runs'] as $run): ?>
                    <tr>
                        <td>PR-<?= $run->id ?></td>
                        <td><?= date('M d', strtotime($run->period_start)) ?> - <?= date('M d, Y', strtotime($run->period_end)) ?></td>
                        <td><?= date('M d, Y', strtotime($run->run_date)) ?></td>
                        <td><?= number_format($run->total_gross, 2) ?></td>
                        <td><?= htmlspecialchars($run->username) ?></td>
                        <td>
                            <?php 
                                $badge = 'secondary';
                                if($run->status == 'Approved') $badge = 'info';
                                if($run->status == 'Paid') $badge = 'success';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= $run->status ?></span>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/payroll/show/<?= $run->id ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Payroll Modal -->
<div class="modal fade" id="newPayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="<?= APP_URL ?>/payroll/preview" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Run New Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Period Start Date</label>
                    <input type="date" name="period_start" class="form-control" required value="<?= date('Y-m-01') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Period End Date</label>
                    <input type="date" name="period_end" class="form-control" required value="<?= date('Y-m-t') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Run Date</label>
                    <input type="date" name="run_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Preview Payroll</button>
            </div>
        </form>
    </div>
</div>
