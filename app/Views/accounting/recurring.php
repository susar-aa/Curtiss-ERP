<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 10px 20px; background: #007aff; color: #fff; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: background 0.15s; }
    .btn:hover { background: #0062cc; }
    .btn-secondary { padding: 8px 16px; background: #efefef; color: #333; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; }
    .btn-secondary:hover { background: #e0e0e0; }
    .btn-success { padding: 10px 20px; background: #34c759; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
    .btn-success:hover { background: #28a745; }
    .btn-danger { padding: 8px 14px; background: #ff3b30; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
    .btn-danger:hover { background: #e02d22; }
    .btn-outline { padding: 6px 12px; background: transparent; color: #007aff; border: 1px solid #007aff; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s; }
    .btn-outline:hover { background: #007aff; color: #fff; }
    .btn-outline-danger { padding: 6px 12px; background: transparent; color: #ff3b30; border: 1px solid #ff3b30; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s; }
    .btn-outline-danger:hover { background: #ff3b30; color: #fff; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: var(--text-muted, #666); }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--mac-border, #ccc); border-radius: 6px; background: transparent; color: var(--text-main); font-size: 14px; box-sizing: border-box; }
    
    .panel { background: var(--bg-card, #fff); border: 1px solid var(--mac-border, #eaeaea); border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .table-responsive { overflow-x: auto; margin-top: 15px; }
    .table-custom { width: 100%; border-collapse: collapse; text-align: left; }
    .table-custom th { padding: 12px 15px; background: rgba(0,0,0,0.02); font-size: 12px; font-weight: 600; color: #666; border-bottom: 1px solid var(--mac-border, #eaeaea); text-transform: uppercase; }
    .table-custom td { padding: 12px 15px; border-bottom: 1px solid var(--mac-border, #eaeaea); font-size: 14px; vertical-align: middle; }

    .badge-due { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-block; }
    .badge-current { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-block; }
    .badge-active { background: #cce5ff; color: #004085; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-block; }
    .badge-inactive { background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-block; }

    /* Modal styling */
    .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
    .modal-card { background: var(--bg-card, #fff); border-radius: 12px; width: 900px; max-width: 95%; max-height: 90vh; overflow-y: auto; padding: 30px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); border: 1px solid var(--mac-border, #eaeaea); }
    .modal-card-small { width: 450px; }
    .modal-card h3 { margin-top: 0; font-weight: 700; color: var(--text-main); }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; }
    .hidden { display: none !important; }
</style>

<div class="header-actions">
    <div>
        <h2 style="margin: 0 0 5px 0;">Recurring Journal Entries</h2>
        <p style="margin: 0; color: #666;">Set up and automate repeating accounting transactions like monthly rent or depreciation.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <?php if (!empty($data['pending_templates'])): ?>
            <form action="<?= APP_URL ?>/accounting/recurring" method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="action" value="post_all_due">
                <button type="submit" class="btn-success">
                    <i class="fa-solid fa-bolt"></i> Post All Due (<?= count($data['pending_templates']) ?>)
                </button>
            </form>
        <?php endif; ?>
        <button type="button" class="btn" onclick="openCreateModal()">
            <i class="fa-solid fa-plus"></i> New Template
        </button>
    </div>
</div>

<?php if(!empty($data['error'])): ?>
    <div style="padding: 10px; background:#ffebee; color:#c62828; border-radius:6px; margin-bottom:15px; border: 1px solid #ef9a9a;"><?= $data['error'] ?></div>
<?php endif; ?>
<?php if(!empty($data['success'])): ?>
    <div style="padding: 15px; background:#e8f5e9; color:#2e7d32; border-radius:6px; margin-bottom:15px; border: 1px solid #a5d6a7; font-weight: bold;">✓ <?= $data['success'] ?></div>
<?php endif; ?>

<div class="panel">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Frequency</th>
                    <th>Schedule Day</th>
                    <th>Description</th>
                    <th>Last Posted</th>
                    <th>Status</th>
                    <th style="text-align: center; width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['templates'])): ?>
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #999;">
                            <i class="fa-solid fa-arrows-clockwise" style="font-size: 24px; margin-bottom: 8px; display:block;"></i>
                            No recurring templates found. Create one to begin.
                        </td>
                    </tr>
                <?php else: foreach($data['templates'] as $t): 
                    $isPending = false;
                    foreach($data['pending_templates'] as $pt) {
                        if ($pt->id === $t->id) {
                            $isPending = true;
                            break;
                        }
                    }
                ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t->template_name) ?></strong></td>
                        <td><span class="badge-active"><?= htmlspecialchars($t->frequency) ?></span></td>
                        <td>Day <?= htmlspecialchars($t->day_of_month) ?></td>
                        <td><?= htmlspecialchars($t->description) ?></td>
                        <td style="color: #666; font-size:13.5px;">
                            <?= !empty($t->last_posted_date) ? date('Y-m-d', strtotime($t->last_posted_date)) : 'Never' ?>
                        </td>
                        <td>
                            <?php if (!$t->is_active): ?>
                                <span class="badge-inactive">Inactive</span>
                            <?php elseif ($isPending): ?>
                                <span class="badge-due">Due to Post</span>
                            <?php else: ?>
                                <span class="badge-current">Up to Date</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <button type="button" class="btn-outline" onclick="openPostModal(<?= $t->id ?>, '<?= htmlspecialchars($t->template_name) ?>')">
                                    <i class="fa-solid fa-paper-plane"></i> Post Entry
                                </button>
                                <form action="<?= APP_URL ?>/accounting/recurring" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="action" value="delete_template">
                                    <input type="hidden" name="template_id" value="<?= $t->id ?>">
                                    <button type="submit" class="btn-outline-danger" title="Delete Template">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Recurring Template -->
<div id="createModal" class="modal-backdrop hidden">
    <div class="modal-card">
        <h3>Create Recurring Template</h3>
        <form action="<?= APP_URL ?>/accounting/recurring" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="create_template">
            
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Template Name *</label>
                    <input type="text" name="template_name" class="form-control" placeholder="e.g. Monthly Shop Rent" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Frequency</label>
                    <select name="frequency" class="form-control">
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Annually">Annually</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Schedule Day (of month)</label>
                    <input type="number" name="day_of_month" class="form-control" min="1" max="31" value="1" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 3fr 1fr; gap: 15px; align-items: end; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Template Description</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Standard monthly office space rental expense">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; height: 38px; font-weight: 600;">
                        <input type="checkbox" name="is_active" value="1" checked style="width:18px; height:18px;"> Template Active
                    </label>
                </div>
            </div>

            <h4 style="margin: 20px 0 10px 0; font-weight: 600; border-bottom: 1.5px solid #eaeaea; padding-bottom: 6px;">Journal Lines</h4>
            
            <table class="table-custom" id="linesTable">
                <thead>
                    <tr>
                        <th style="width: 40%;">Account</th>
                        <th style="width: 20%;">Debit (Rs)</th>
                        <th style="width: 20%;">Credit (Rs)</th>
                        <th style="width: 20%;">Line Description</th>
                        <th style="width: 60px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    <!-- Row 1 -->
                    <tr>
                        <td>
                            <select name="lines[0][account_id]" class="form-control" required>
                                <option value="">-- Select Account --</option>
                                <?php foreach($data['accounts'] as $acc): ?>
                                    <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?> (<?= $acc->account_type ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="lines[0][debit]" class="form-control line-debit" step="0.01" min="0" placeholder="0.00" onchange="calculateTotals()"></td>
                        <td><input type="number" name="lines[0][credit]" class="form-control line-credit" step="0.01" min="0" placeholder="0.00" onchange="calculateTotals()"></td>
                        <td><input type="text" name="lines[0][description]" class="form-control" placeholder="Line description"></td>
                        <td style="text-align: center;"><button type="button" class="btn-outline-danger" onclick="removeRow(this)" style="padding: 4px 8px;"><i class="fa-solid fa-trash-can"></i></button></td>
                    </tr>
                    <!-- Row 2 -->
                    <tr>
                        <td>
                            <select name="lines[1][account_id]" class="form-control" required>
                                <option value="">-- Select Account --</option>
                                <?php foreach($data['accounts'] as $acc): ?>
                                    <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?> (<?= $acc->account_type ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="lines[1][debit]" class="form-control line-debit" step="0.01" min="0" placeholder="0.00" onchange="calculateTotals()"></td>
                        <td><input type="number" name="lines[1][credit]" class="form-control line-credit" step="0.01" min="0" placeholder="0.00" onchange="calculateTotals()"></td>
                        <td><input type="text" name="lines[1][description]" class="form-control" placeholder="Line description"></td>
                        <td style="text-align: center;"><button type="button" class="btn-outline-danger" onclick="removeRow(this)" style="padding: 4px 8px;"><i class="fa-solid fa-trash-can"></i></button></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn-secondary" onclick="addRow()">
                    <i class="fa-solid fa-plus"></i> Add Line
                </button>
                <div style="font-size: 15px; font-weight: 700;">
                    Debits: Rs: <span id="totalDebits">0.00</span> | Credits: Rs: <span id="totalCredits">0.00</span>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn">Create Template</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Post Template Entry Confirmation -->
<div id="postModal" class="modal-backdrop hidden">
    <div class="modal-card modal-card-small">
        <h3>Post Recurring Entry</h3>
        <p>You are about to generate a journal entry from the template: <strong id="postTemplateName"></strong>.</p>
        
        <form action="<?= APP_URL ?>/accounting/recurring" method="POST" style="margin-top: 20px;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="post_entry">
            <input type="hidden" name="template_id" id="postTemplateId" value="">
            
            <div class="form-group">
                <label>Posting Date</label>
                <input type="date" name="post_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePostModal()">Cancel</button>
                <button type="submit" class="btn" style="background:#34c759;">Post Now</button>
            </div>
        </form>
    </div>
</div>

<script>
    let rowIndex = 2;
    const accountsOptionsHtml = `
        <option value="">-- Select Account --</option>
        <?php foreach($data['accounts'] as $acc): ?>
            <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?> (<?= $acc->account_type ?>)</option>
        <?php endforeach; ?>
    `;

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openPostModal(id, name) {
        document.getElementById('postTemplateId').value = id;
        document.getElementById('postTemplateName').textContent = name;
        document.getElementById('postModal').classList.remove('hidden');
    }

    function closePostModal() {
        document.getElementById('postModal').classList.add('hidden');
    }

    function addRow() {
        const body = document.getElementById('linesBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="lines[${rowIndex}][account_id]" class="form-control" required>
                    ${accountsOptionsHtml}
                </select>
            </td>
            <td><input type="number" name="lines[${rowIndex}][debit]" class="form-control line-debit" step="0.01" min="0" placeholder="0.00" onchange="calculateTotals()"></td>
            <td><input type="number" name="lines[${rowIndex}][credit]" class="form-control line-credit" step="0.01" min="0" placeholder="0.00" onchange="calculateTotals()"></td>
            <td><input type="text" name="lines[${rowIndex}][description]" class="form-control" placeholder="Line description"></td>
            <td style="text-align: center;"><button type="button" class="btn-outline-danger" onclick="removeRow(this)" style="padding: 4px 8px;"><i class="fa-solid fa-trash-can"></i></button></td>
        `;
        body.appendChild(tr);
        rowIndex++;
    }

    function removeRow(btn) {
        const tr = btn.closest('tr');
        tr.remove();
        calculateTotals();
    }

    function calculateTotals() {
        let debits = 0;
        let credits = 0;

        document.querySelectorAll('.line-debit').forEach(input => {
            const v = parseFloat(input.value);
            if (!isNaN(v)) debits += v;
        });

        document.querySelectorAll('.line-credit').forEach(input => {
            const v = parseFloat(input.value);
            if (!isNaN(v)) credits += v;
        });

        document.getElementById('totalDebits').textContent = debits.toFixed(2);
        document.getElementById('totalCredits').textContent = credits.toFixed(2);
    }
</script>
