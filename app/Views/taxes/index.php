<?php
?>
<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — TAX CONFIGURATION
   ============================================================ */
:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.12);
    --c-fill2:        rgba(120,120,128,0.16);
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-blue-mid:     #b3d6ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
}

body {
    background-color: var(--c-bg);
    font-family: var(--f-system);
    -webkit-font-smoothing: antialiased;
    color: #1c1c1e;
}

/* Page Header */
.mac-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 32px;
}
.mac-page-title {
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -0.015em;
    margin: 0;
}
.mac-page-subtitle {
    font-size: 15px;
    color: #8e8e93;
    margin-top: 4px;
}
.mac-btn-primary {
    background: var(--c-blue);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,122,255,0.3);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.mac-btn-primary:hover {
    background: #0062cc;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,122,255,0.4);
}
.mac-btn-danger {
    background: var(--c-red-light);
    color: var(--c-red);
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mac-btn-danger:hover {
    background: var(--c-red);
    color: #fff;
}
.mac-btn-edit {
    background: var(--c-fill);
    color: #1c1c1e;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mac-btn-edit:hover {
    background: var(--c-fill2);
}

/* Main Card */
.mac-card {
    background: var(--c-surface);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    margin: 0 32px 32px 32px;
    overflow: hidden;
}

/* Alerts */
.mac-alert {
    padding: 14px 20px;
    border-radius: 8px;
    margin: 0 32px 20px 32px;
    font-weight: 500;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.mac-alert.error {
    background: var(--c-red-light);
    color: var(--c-red);
}
.mac-alert.success {
    background: var(--c-green-light);
    color: var(--c-green);
}

/* Table */
.mac-table {
    width: 100%;
    border-collapse: collapse;
}
.mac-table th {
    background: var(--c-surface2);
    padding: 12px 20px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #8e8e93;
    border-bottom: 1px solid var(--c-separator);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.mac-table td {
    padding: 16px 20px;
    border-bottom: 1px solid var(--c-separator2);
    font-size: 15px;
    vertical-align: middle;
}
.mac-table tbody tr:hover {
    background: var(--c-surface2);
}
.mac-table tbody tr:last-child td {
    border-bottom: none;
}

/* Status Badges */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    cursor: pointer;
    border: none;
    outline: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 25px;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 10px top 50%;
    background-size: 10px auto;
}
.status-badge.active {
    background-color: var(--c-green-light);
    color: var(--c-green);
}
.status-badge.inactive {
    background-color: var(--c-red-light);
    color: var(--c-red);
}

/* Actions */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* Modal */
.mac-backdrop-custom {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.mac-panel-custom {
    background: var(--c-surface);
    width: 460px;
    border-radius: 14px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    overflow: hidden;
    animation: mac-slide-down 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes mac-slide-down {
    0% { transform: translateY(-20px) scale(0.98); opacity: 0; }
    100% { transform: translateY(0) scale(1); opacity: 1; }
}
.mac-header-custom {
    background: var(--c-surface2);
    padding: 16px 20px;
    border-bottom: 1px solid var(--c-separator);
    display: flex;
    align-items: center;
}
.mac-traffic-lights {
    display: flex;
    gap: 8px;
    margin-right: 16px;
}
.mac-light {
    width: 12px; height: 12px; border-radius: 50%; cursor: pointer;
}
.mac-light.red { background: #ff5f56; }
.mac-light.yellow { background: #ffbd2e; }
.mac-light.green { background: #27c93f; }
.mac-title-custom {
    font-weight: 600;
    font-size: 15px;
}
.mac-body-custom {
    padding: 24px;
}
.mac-field-group {
    margin-bottom: 16px;
}
.mac-field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #3a3a3c;
    margin-bottom: 6px;
}
.mac-input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #d1d1d6;
    border-radius: 8px;
    font-size: 15px;
    font-family: var(--f-system);
    transition: all 0.2s ease;
}
.mac-input:focus {
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px var(--c-blue-light);
    outline: none;
}
.mac-footer-custom {
    padding: 16px 24px;
    background: var(--c-surface2);
    border-top: 1px solid var(--c-separator);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
.mac-btn-cancel {
    background: var(--c-surface);
    border: 1px solid #d1d1d6;
    color: #1c1c1e;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}
.mac-btn-submit {
    background: var(--c-blue);
    border: none;
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}
.mac-info-box {
    background: var(--c-blue-light);
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.mac-info-box i {
    color: var(--c-blue);
    font-size: 18px;
    margin-top: 2px;
}
.mac-info-box p {
    margin: 0;
    font-size: 13px;
    color: #1c1c1e;
    line-height: 1.4;
}
</style>

<div class="mac-page-header">
    <div>
        <h1 class="mac-page-title">Tax Configuration</h1>
        <p class="mac-page-subtitle">Manage VAT, GST, and Sales Tax profiles applied to your invoices and sales orders.</p>
    </div>
    <button class="mac-btn-primary" onclick="openTaxModal()">
        <i class="fas fa-plus"></i> Add Tax Rate
    </button>
</div>

<?php if(!empty($data['error'])): ?>
    <div class="mac-alert error">
        <i class="fas fa-exclamation-circle"></i> <?= $data['error'] ?>
    </div>
<?php endif; ?>
<?php if(!empty($data['success'])): ?>
    <div class="mac-alert success">
        <i class="fas fa-check-circle"></i> <?= $data['success'] ?>
    </div>
<?php endif; ?>

<div class="mac-card">
    <table class="mac-table">
        <thead>
            <tr>
                <th>Tax Name</th>
                <th style="text-align: right;">Rate (%)</th>
                <th>Accounting Ledger (Liability)</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['tax_rates'])): ?>
            <tr><td colspan="5" style="text-align: center; color: #8e8e93; padding: 40px 0;">No tax rates configured. Click "Add Tax Rate" to get started.</td></tr>
            <?php else: foreach($data['tax_rates'] as $tax): ?>
            <tr>
                <td>
                    <div style="font-weight: 600; font-size: 16px;"><?= htmlspecialchars($tax->tax_name) ?></div>
                </td>
                <td style="text-align: right; font-weight: 700; color: var(--c-blue); font-size: 16px;">
                    <?= number_format($tax->rate_percentage, 2) ?>%
                </td>
                <td>
                    <div style="font-size: 12px; color: #8e8e93; font-weight: 600; margin-bottom: 2px;">
                        <?= htmlspecialchars($tax->account_code) ?>
                    </div>
                    <div style="font-size: 14px;">
                        <?= htmlspecialchars($tax->account_name) ?>
                    </div>
                </td>
                <td style="text-align: center;">
                    <form action="<?= APP_URL ?>/tax" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="tax_id" value="<?= $tax->id ?>">
                        <select name="status" onchange="this.form.submit()" class="status-badge <?= $tax->is_active ? 'active' : 'inactive' ?>">
                            <option value="1" <?= $tax->is_active ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= !$tax->is_active ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </form>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="mac-btn-edit" title="Edit Tax" onclick="editTax(<?= htmlspecialchars(json_encode([
                            'id' => $tax->id,
                            'tax_name' => $tax->tax_name,
                            'rate_percentage' => $tax->rate_percentage,
                            'liability_account_id' => $tax->liability_account_id
                        ])) ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="<?= APP_URL ?>/tax" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this tax rate?');">
                            <input type="hidden" name="action" value="delete_tax">
                            <input type="hidden" name="tax_id" value="<?= $tax->id ?>">
                            <button type="submit" class="mac-btn-danger" title="Delete Tax">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit -->
<div class="mac-backdrop-custom" id="taxModal">
    <div class="mac-panel-custom">
        <div class="mac-header-custom">
            <div class="mac-traffic-lights">
                <div class="mac-light red" onclick="closeTaxModal()"></div>
                <div class="mac-light yellow"></div>
                <div class="mac-light green"></div>
            </div>
            <span class="mac-title-custom" id="taxModalTitle">Create Tax Rate</span>
        </div>
        <form action="<?= APP_URL ?>/tax" method="POST" style="margin:0;">
            <input type="hidden" name="action" id="taxFormAction" value="add_tax">
            <input type="hidden" name="tax_id" id="taxIdInput" value="">
            
            <div class="mac-body-custom">
                <div class="mac-info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Tax collected is owed to the government. Ensure you select an appropriate Liability account (e.g. "VAT Payable").</p>
                </div>
                
                <div class="mac-field-group">
                    <label class="mac-field-label">Tax Name (e.g., "VAT 18%") <span style="color:var(--c-red);">*</span></label>
                    <input type="text" name="tax_name" id="taxNameInput" class="mac-input" placeholder="Value Added Tax" required>
                </div>
                
                <div class="mac-field-group">
                    <label class="mac-field-label">Tax Rate Percentage (%) <span style="color:var(--c-red);">*</span></label>
                    <input type="number" name="rate_percentage" id="taxRateInput" step="0.01" min="0" class="mac-input" placeholder="18.00" required>
                </div>

                <div class="mac-field-group">
                    <label class="mac-field-label">Liability Account <span style="color:var(--c-red);">*</span></label>
                    <select name="liability_account_id" id="taxLiabilityInput" class="mac-input" required>
                        <option value="">Select Account...</option>
                        <?php foreach($data['liabilities'] as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= $acc->account_code ?> - <?= htmlspecialchars($acc->account_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mac-footer-custom">
                <button type="button" class="mac-btn-cancel" onclick="closeTaxModal()">Cancel</button>
                <button type="submit" class="mac-btn-submit" id="taxSubmitBtn">Save Tax Rate</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTaxModal() {
    document.getElementById('taxModalTitle').innerText = 'Create Tax Rate';
    document.getElementById('taxFormAction').value = 'add_tax';
    document.getElementById('taxIdInput').value = '';
    document.getElementById('taxNameInput').value = '';
    document.getElementById('taxRateInput').value = '';
    document.getElementById('taxLiabilityInput').value = '';
    document.getElementById('taxSubmitBtn').innerText = 'Save Tax Rate';
    
    document.getElementById('taxModal').style.display = 'flex';
}

function editTax(taxData) {
    document.getElementById('taxModalTitle').innerText = 'Edit Tax Rate';
    document.getElementById('taxFormAction').value = 'edit_tax';
    document.getElementById('taxIdInput').value = taxData.id;
    document.getElementById('taxNameInput').value = taxData.tax_name;
    document.getElementById('taxRateInput').value = taxData.rate_percentage;
    document.getElementById('taxLiabilityInput').value = taxData.liability_account_id;
    document.getElementById('taxSubmitBtn').innerText = 'Update Tax Rate';
    
    document.getElementById('taxModal').style.display = 'flex';
}

function closeTaxModal() {
    document.getElementById('taxModal').style.display = 'none';
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeTaxModal();
    }
});
</script>