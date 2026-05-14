<?php
?>
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 16px; background: #0066cc; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px;}
    .btn:hover { background: #005bb5; }
    .btn-outline { background: transparent; border: 1px solid #0066cc; color: #0066cc; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); }
    .data-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
    .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .status-Active { background: #e8f5e9; color: #2e7d32; }
    .status-Terminated { background: #ffebee; color: #c62828; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: var(--mac-bg); backdrop-filter: blur(20px); padding: 30px; border-radius: 12px; width: 600px; border: 1px solid var(--mac-border); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border); border-radius: 4px; background: transparent; color: var(--text-main); box-sizing: border-box;}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
</style>

<div class="card">
    <div class="header-actions">
        <h2>Employee Directory</h2>
        <div>
            <a href="<?= APP_URL ?>/hrm/payroll" class="btn btn-outline" style="margin-right: 10px;">Run Payroll</a>
            <button class="btn" onclick="document.getElementById('empModal').style.display='flex'">+ Add Employee</button>
        </div>
    </div>

    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:4px; margin-bottom:15px;"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(!empty($data['success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border-radius:4px; margin-bottom:15px;"><?= $data['success'] ?></div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Job Title & Dept</th>
                <th>Contact</th>
                <th>Status</th>
                <th style="text-align: right;">Base Salary (Rs:)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['employees'])): ?>
            <tr><td colspan="5" style="text-align: center; color: #888;">No employees found.</td></tr>
            <?php else: foreach($data['employees'] as $emp): ?>
            <tr>
                <td><strong><?= htmlspecialchars($emp->first_name . ' ' . $emp->last_name) ?></strong><br><span style="font-size:11px; color:#888;">Hired: <?= date('M Y', strtotime($emp->hire_date)) ?></span></td>
                <td><?= htmlspecialchars($emp->job_title) ?><br><span style="font-size:12px; color:#666;"><?= htmlspecialchars($emp->department) ?></span></td>
                <td style="font-size:13px;"><?= htmlspecialchars($emp->email) ?><br><?= htmlspecialchars($emp->phone) ?></td>
                <td><span class="status-badge status-<?= $emp->status ?>"><?= $emp->status ?></span></td>
                <td style="text-align: right; font-weight:bold;"><?= number_format($emp->base_salary, 2) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="empModal">
    <div class="modal-content">
        <h3 style="margin-top:0;">Add New Employee</h3>
        <form action="<?= APP_URL ?>/hrm" method="POST">
            <input type="hidden" name="action" value="add_employee">
            
            <div class="grid-2">
                <div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" required></div>
                <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
            </div>
            
            <div class="grid-2">
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
            </div>

            <div class="grid-2">
                <div class="form-group"><label>Department</label><input type="text" name="department" class="form-control"></div>
                <div class="form-group"><label>Job Title</label><input type="text" name="job_title" class="form-control"></div>
            </div>

            <div class="grid-2">
                <div class="form-group"><label>Hire Date</label><input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                <div class="form-group"><label>Base Salary (Rs:) *</label><input type="number" name="base_salary" step="0.01" min="0" class="form-control" required></div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('empModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Save Employee</button>
            </div>
        </form>
    </div>
</div>