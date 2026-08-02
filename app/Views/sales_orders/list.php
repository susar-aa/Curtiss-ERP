<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — SALES ORDERS
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
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;
    --c-gray:         #8e8e93;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-label:     #8e8e93;

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-md: 14px;
    --r-xl: 26px;
    --r-pill: 999px;

    --dur-fast:    0.18s;
    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
}

.inv-wrap {
    max-width: 1420px; margin: 0 auto;
    padding: 20px 24px 140px;
    font-family: var(--f-system);
    color: var(--t-primary);
}

.inv-header { margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; }
.inv-title-group {}
.inv-eyebrow { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--c-blue); margin-bottom: 6px; }
.inv-title { font-size: 32px; font-weight: 700; letter-spacing: -0.03em; color: var(--t-primary); margin:0; }
.inv-desc { margin: 5px 0 0 0; font-size: 13px; color: var(--t-secondary); }

.sf-alert { display: flex; align-items: flex-start; gap: 12px; background: var(--c-surface); border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 20px; box-shadow: var(--shadow-xs); border: 0.5px solid var(--c-separator); border-left-width: 3px; font-size: 14px; }
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 600; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }

/* Filter Card */
.filter-card { background: var(--c-surface); border-radius: var(--r-xl); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm); border: 0.5px solid var(--c-separator); }
.filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: end; }
.sf-form-group { margin-bottom: 0; }
.sf-form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--t-secondary); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;}
.sf-input { width: 100%; padding: 10px 14px; background: var(--c-surface2); border: 1px solid var(--c-separator); border-radius: var(--r-md); font-family: var(--f-system); font-size: 14px; color: var(--t-primary); box-sizing: border-box; transition: border-color var(--dur-fast), background var(--dur-fast); }
.sf-input:focus { outline: none; border-color: var(--c-blue); background: var(--c-surface); }
.sf-input-wrap { position: relative; }
.sf-input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--t-label); }
.sf-input-wrap .sf-input { padding-left: 36px; }

/* Buttons */
.sf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 20px; border-radius: var(--r-pill); font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all var(--dur-fast) var(--ease-spring); font-family: var(--f-system); text-decoration: none; }
.sf-btn:active { transform: scale(0.96); }
.sf-btn-primary { background: var(--c-blue); color: #fff; }
.sf-btn-primary:hover { background: #006ce6; }
.sf-btn-ghost { background: var(--c-fill); color: var(--t-primary); }
.sf-btn-ghost:hover { background: var(--c-fill2); }
.sf-btn-danger { background: var(--c-red); color: #fff; }
.sf-btn-danger:hover { background: #e03228; }
.sf-btn-danger-outline { background: transparent; border: 1px solid var(--c-red); color: var(--c-red); }
.sf-btn-danger-outline:hover { background: var(--c-red-light); }

/* Table Section */
.table-panel { background: var(--c-surface); border-radius: var(--r-xl); border: 0.5px solid var(--c-separator); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 20px; }
.inv-table { width: 100%; border-collapse: collapse; }
.inv-table thead th { padding: 13px 18px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--t-label); background: var(--c-surface2); border-bottom: 0.5px solid var(--c-separator); text-align: left; }
.inv-table tbody tr { transition: background var(--dur-fast); border-bottom: 0.5px solid var(--c-separator2); }
.inv-table tbody tr:hover { background: var(--c-fill2); }
.inv-table td { padding: 14px 18px; font-size: 14px; color: var(--t-primary); vertical-align: middle; }

/* Badges */
.sf-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: var(--r-pill); font-size: 12px; font-weight: 700; letter-spacing: 0.01em; white-space: nowrap; }
.sf-badge .dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.badge-Pending { background: var(--c-orange-light); color: #c05d00; }
.badge-Pending .dot { background: var(--c-orange); }
.badge-Invoiced { background: var(--c-green-light); color: #1a7f3c; }
.badge-Invoiced .dot { background: var(--c-green); }
.badge-Voided { background: var(--c-separator); color: var(--t-secondary); }
.badge-Voided .dot { background: var(--t-secondary); }
.badge-Std { background: var(--c-blue-light); color: var(--c-blue); font-size: 11px; padding: 3px 8px;}
.badge-Route { background: var(--c-purple-light); color: var(--c-purple); font-size: 11px; padding: 3px 8px;}

/* Row Actions */
.row-acts { display: flex; gap: 6px; justify-content: flex-end; }
.act-btn { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--c-fill); color: var(--t-label); border: none; cursor: pointer; text-decoration: none; font-size: 13px; transition: all var(--dur-fast) var(--ease-spring); }
.act-btn:hover { transform: scale(1.12); }
.act-btn.print:hover   { background: var(--c-gray); color: #fff; }
.act-btn.convert:hover { background: var(--c-green-light); color: var(--c-green); }
.act-btn.edit:hover   { background: var(--c-blue-light); color: var(--c-blue); }
.act-btn.delete:hover { background: var(--c-red-light); color: var(--c-red); }

/* Bulk Action Bar */
.bulk-bar { display: none; background: rgba(255, 255, 255, 0.85); backdrop-filter: saturate(180%) blur(20px); border: 0.5px solid var(--c-blue); border-radius: var(--r-xl); padding: 12px 20px; margin-bottom: 24px; box-shadow: var(--shadow-md); align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap; }
.bulk-selected { background: var(--c-blue); color: #fff; padding: 4px 10px; border-radius: var(--r-pill); font-size: 12px; font-weight: 600; }

/* Checkboxes */
.sf-checkbox { accent-color: var(--c-blue); width: 16px; height: 16px; cursor: pointer; }

/* Command Bar (Dynamic Island) */
.cmd-bar { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); background: rgba(28, 28, 30, 0.92); backdrop-filter: saturate(180%) blur(28px); -webkit-backdrop-filter: saturate(180%) blur(28px); border: 0.5px solid rgba(255,255,255,0.12); border-radius: var(--r-pill); padding: 7px 10px; display: flex; align-items: center; gap: 4px; box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3); z-index: 1000; }
.cmd-search { display: flex; align-items: center; gap: 9px; background: rgba(255,255,255,0.1); border-radius: var(--r-pill); padding: 8px 14px; width: 250px; transition: width 0.42s cubic-bezier(0.25, 0.1, 0.25, 1), background var(--dur-fast); }
.cmd-search:focus-within { width: 380px; background: rgba(255,255,255,0.18); }
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; }
.cmd-search input { background: transparent; border: none; outline: none; color: #fff; font-size: 14px; font-weight: 500; font-family: var(--f-system); width: 100%; }
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-cta { display: flex; align-items: center; gap: 7px; background: #fff; color: #1c1c1e; border: none; border-radius: var(--r-pill); padding: 0 18px; height: 38px; font-size: 14px; font-weight: 700; font-family: var(--f-system); cursor: pointer; transition: transform var(--dur-fast) var(--ease-spring), background var(--dur-fast); margin-left: 2px; text-decoration: none; }
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

/* Modals */
.sf-modal { display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; opacity: 0; transition: opacity var(--dur-fast); }
.sf-modal.open { display: flex; opacity: 1; }
.sf-modal-box { background: var(--c-surface); width: 440px; border-radius: var(--r-xl); padding: 32px; box-shadow: var(--shadow-xl); border: 0.5px solid var(--c-separator); transform: scale(0.95); transition: transform var(--dur-fast) var(--ease-spring); }
.sf-modal.open .sf-modal-box { transform: scale(1); }
</style>

<!-- Sales Orders Management -->
<div class="inv-wrap">

    <!-- Alert Notifications -->
    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="sf-alert success">
        <i class="fa-solid fa-circle-check sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Success</div>
            <div class="sf-alert-msg"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="sf-alert error">
        <i class="fa-solid fa-triangle-exclamation sf-alert-icon"></i>
        <div style="flex:1;">
            <div class="sf-alert-title">Error</div>
            <div class="sf-alert-msg"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search & Filters Card -->
    <div class="filter-card">
        <form id="filterForm" method="GET" action="<?= APP_URL ?>/salesorder" class="filter-grid">
            
            <!-- Hidden Search Text -->
            <input type="hidden" name="search" id="mainSearchInput" value="<?= htmlspecialchars($data['search'] ?? '') ?>">

            <!-- Customer Filter -->
            <div class="sf-form-group">
                <label>Customer</label>
                <select name="customer_id" class="sf-input">
                    <option value="0">-- All Customers --</option>
                    <?php foreach ($data['customers'] as $c): ?>
                        <option value="<?= $c->id ?>" <?= (isset($data['customer_id']) && $data['customer_id'] == $c->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Range: Start -->
            <div class="sf-form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($data['start_date'] ?? '') ?>" class="sf-input">
            </div>

            <!-- Date Range: End -->
            <div class="sf-form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($data['end_date'] ?? '') ?>" class="sf-input">
            </div>

            <!-- Sales Rep Filter -->
            <div class="sf-form-group">
                <label>Sales Rep</label>
                <select name="rep_name" class="sf-input">
                    <option value="">-- All Reps --</option>
                    <?php foreach ($data['sales_reps'] as $rep): ?>
                        <option value="<?= htmlspecialchars($rep->name) ?>" <?= (isset($data['rep_name']) && $data['rep_name'] === $rep->name) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rep->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sf-form-group" style="display: flex; gap: 8px;">
                <button type="submit" class="sf-btn sf-btn-primary" style="flex: 1;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="<?= APP_URL ?>/salesorder" class="sf-btn sf-btn-ghost" style="flex: 1; text-align:center;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <form id="bulkForm" method="POST" action="<?= APP_URL ?>/salesorder/bulk_action" onsubmit="return handleBulkSubmit(event);">
        <!-- Bulk Action Bar -->
        <div id="bulkActionBar" class="bulk-bar">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="bulk-selected" id="selectedCountBadge">0 selected</span>
                <span style="font-size: 13px; font-weight: 600; color: var(--t-primary);">Bulk Actions:</span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end;">
                <!-- Action Dropdown -->
                <select name="bulk_action" id="bulkActionSelect" onchange="toggleBulkActionInputs()" class="sf-input" style="min-width: 180px; width:auto;" required>
                    <option value="">-- Select Action --</option>
                    <option value="change_date">Change Transaction Date</option>
                    <option value="change_rep">Change Assigned Representative</option>
                    <option value="delete">Delete Selected</option>
                </select>

                <!-- Date Input -->
                <div id="bulkDateInputContainer" style="display: none;">
                    <input type="date" name="bulk_date" class="sf-input" style="width:auto;">
                </div>

                <!-- Rep Dropdown -->
                <div id="bulkRepInputContainer" style="display: none;">
                    <select name="bulk_rep" class="sf-input" style="width:auto;">
                        <option value="">-- Select Representative --</option>
                        <?php if (!empty($data['sales_reps'])): ?>
                            <?php foreach ($data['sales_reps'] as $rep): ?>
                                <option value="<?= htmlspecialchars($rep->name) ?>"><?= htmlspecialchars($rep->name) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Password Input (for Delete validation) -->
                <div id="bulkPasswordInputContainer" style="display: none; align-items: center; gap: 8px;">
                    <input type="password" name="admin_password" placeholder="Admin Password" class="sf-input" style="border-color:var(--c-red); width:auto;" autocomplete="new-password">
                </div>

                <!-- Apply Button -->
                <button type="submit" class="sf-btn sf-btn-primary">
                    <i class="fa-solid fa-check"></i> Apply
                </button>
            </div>
        </div>

        <!-- Data Table Container -->
        <div class="table-panel">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width: 4%; text-align: center;">
                            <input type="checkbox" id="selectAllCheckbox" class="sf-checkbox" onchange="toggleSelectAll(this)">
                        </th>
                        <th style="width: 14%;">Order No</th>
                        <th style="width: 11%;">Date</th>
                        <th>Customer Name</th>
                        <th style="width: 12%;">Source / Channel</th>
                        <th style="width: 13%; text-align: right;">Total Amount</th>
                        <th style="width: 11%; text-align: center;">Status</th>
                        <th style="width: 20%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['orders'])): ?>
                        <tr>
                            <td colspan="8" style="padding: 60px; text-align: center; color: var(--t-secondary);">
                                <i class="fa-regular fa-folder-open" style="font-size: 32px; display: block; margin: 0 auto 10px; opacity:0.5;"></i>
                                No sales orders matching the current criteria found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['orders'] as $so): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="ids[]" value="<?= htmlspecialchars($so->source_type) ?>:<?= $so->id ?>" class="row-checkbox sf-checkbox" onchange="updateSelectedCount()">
                                </td>
                                <td>
                                    <strong style="color:var(--c-blue); font-family:var(--f-mono);">
                                    <?php if ($so->source_type === 'standard'): ?>
                                        <a href="<?= APP_URL ?>/salesorder/show/<?= $so->id ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                            <?= htmlspecialchars($so->document_number) ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= APP_URL ?>/sales/show/<?= $so->id ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                            <?= htmlspecialchars($so->document_number) ?>
                                        </a>
                                    <?php endif; ?>
                                    </strong>
                                </td>
                                <td>
                                    <?= date('Y-m-d', strtotime($so->document_date)) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($so->customer_name) ?></div>
                                    <?php if (!empty($so->rep_name)): ?>
                                        <div style="font-size: 11px; color: var(--t-secondary); margin-top: 3px;">
                                            <i class="fa-regular fa-user"></i> Rep: <?= htmlspecialchars($so->rep_name) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($so->source_type === 'standard'): ?>
                                        <span class="sf-badge badge-Std">📋 Standard SO</span>
                                    <?php else: ?>
                                        <span class="sf-badge badge-Route">🚚 Route Rep <?= !empty($so->route_name) ? '(' . htmlspecialchars($so->route_name) . ')' : '' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 600; text-align: right; font-family: var(--f-mono);">
                                    Rs: <?= number_format($so->grand_total, 2) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php 
                                        $status = $so->status ?? 'Pending';
                                        $badgeClass = 'badge-Pending';
                                        if ($status === 'Transferred' || $status === 'Completed') { $badgeClass = 'badge-Invoiced'; }
                                        elseif ($status === 'Voided') { $badgeClass = 'badge-Voided'; }
                                    ?>
                                    <div class="sf-badge <?= $badgeClass ?>">
                                        <div class="dot"></div> <?= $status === 'Transferred' ? 'Invoiced' : $status ?>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div class="row-acts">
                                        
                                        <!-- Print -->
                                        <?php if ($so->source_type === 'standard'): ?>
                                            <a href="<?= APP_URL ?>/salesorder/show/<?= $so->id ?>" target="_blank" class="act-btn print" title="Print">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= APP_URL ?>/sales/show/<?= $so->id ?>" target="_blank" class="act-btn print" title="Print">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        <?php endif; ?>
 
                                        <!-- Edit -->
                                        <a href="<?= APP_URL ?>/sales/edit/<?= $so->id ?>?type=sales_order" target="_blank" class="act-btn edit" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
 
                                        <!-- Convert / Transfer to Invoice -->
                                        <?php if ($status !== 'Transferred' && $status !== 'Completed'): ?>
                                            <?php if ($so->source_type === 'standard'): ?>
                                                <a href="<?= APP_URL ?>/sales/create?from_so=<?= $so->id ?>" class="act-btn convert" title="Convert to Invoice">
                                                    <i class="fa-solid fa-file-export"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= APP_URL ?>/sales/create?from_so_route=<?= $so->id ?>" class="act-btn convert" title="Convert to Invoice">
                                                    <i class="fa-solid fa-file-export"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
 
                                        <!-- Delete -->
                                        <button type="button" class="act-btn delete" title="Delete" onclick="openDeleteModal(<?= $so->id ?>, '<?= htmlspecialchars(addslashes($so->document_number)) ?>', '<?= htmlspecialchars(addslashes($so->source_type)) ?>')">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <!-- Pagination Footer -->
    <?php if ($data['total_pages'] > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #666; padding: 10px 5px;">
            <div>
                Showing page <strong><?= $data['page'] ?></strong> of <strong><?= $data['total_pages'] ?></strong> (Total <strong><?= $data['total_records'] ?></strong> records)
            </div>
            <div style="display: flex; gap: 8px;">
                <?php if ($data['page'] > 1): ?>
                    <a href="?page=<?= $data['page'] - 1 ?>&search=<?= urlencode($data['search']) ?>&customer_id=<?= $data['customer_id'] ?>&start_date=<?= urlencode($data['start_date']) ?>&end_date=<?= urlencode($data['end_date']) ?>&status=<?= urlencode($data['status']) ?>&source_type=<?= urlencode($data['source_type'] ?? '') ?>&rep_name=<?= urlencode($data['rep_name'] ?? '') ?>" class="sf-btn sf-btn-ghost">&laquo; Previous</a>
                <?php else: ?>
                    <span class="sf-btn sf-btn-ghost" style="opacity: 0.5; cursor: not-allowed;">&laquo; Previous</span>
                <?php endif; ?>

                <?php if ($data['page'] < $data['total_pages']): ?>
                    <a href="?page=<?= $data['page'] + 1 ?>&search=<?= urlencode($data['search']) ?>&customer_id=<?= $data['customer_id'] ?>&start_date=<?= urlencode($data['start_date']) ?>&end_date=<?= urlencode($data['end_date']) ?>&status=<?= urlencode($data['status']) ?>&source_type=<?= urlencode($data['source_type'] ?? '') ?>&rep_name=<?= urlencode($data['rep_name'] ?? '') ?>" class="sf-btn sf-btn-ghost">Next &raquo;</a>
                <?php else: ?>
                    <span class="sf-btn sf-btn-ghost" style="opacity: 0.5; cursor: not-allowed;">Next &raquo;</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Command Bar -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search orders..." value="<?= htmlspecialchars($data['search'] ?? '') ?>" onkeypress="if(event.key === 'Enter') { document.getElementById('mainSearchInput').value = this.value; document.getElementById('filterForm').submit(); }">
    </div>
    <div class="cmd-divider"></div>
    <a href="<?= APP_URL ?>/sales/create?type=sales_order" class="cmd-cta">
        <i class="fa-solid fa-plus"></i> Create Sales Order
    </a>
</div>

<!-- Delete Authentication Modal -->
<div id="deleteAuthModal" class="sf-modal">
    <div class="sf-modal-box">
        <h3 style="color:var(--c-red); display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-triangle-exclamation"></i> Confirm Deletion
        </h3>
        <p style="font-size: 13px; color: var(--t-secondary); line-height: 1.5; margin-bottom: 20px;">
            You are about to delete <strong id="deleteRecordNum" style="color:var(--c-red);"></strong>. This action requires administrative authentication. The deletion will be permanently logged in the audit trail.
        </p>
        
        <form id="deleteForm" method="POST" action="">
            <div class="sf-form-group">
                <label>Reason for Deletion *</label>
                <textarea name="delete_reason" class="sf-input" required style="height:70px; resize:none;" placeholder="Why are you deleting this record?"></textarea>
            </div>
            
            <div class="sf-form-group">
                <label>Your Admin Password *</label>
                <input type="password" name="password" class="sf-input" required placeholder="Enter password to verify">
            </div>
            
            <div class="sf-form-group" style="display:flex; justify-content:flex-end; gap:10px; margin-top: 24px;">
                <button type="button" class="sf-btn sf-btn-ghost" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="sf-btn sf-btn-danger">Verify & Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, number, sourceType) {
    const modal = document.getElementById('deleteAuthModal');
    const form = document.getElementById('deleteForm');
    const recordNumSpan = document.getElementById('deleteRecordNum');
    
    recordNumSpan.textContent = `Sales Order ${number}`;
    
    // Capture current search/filter parameters
    const queryParams = window.location.search || '';
    
    // Set form action based on the source table
    if (sourceType === 'standard') {
        form.action = `<?= APP_URL ?>/salesorder/delete/${id}${queryParams}`;
    } else {
        form.action = `<?= APP_URL ?>/sales/delete/${id}${queryParams}`;
    }
    
    form.reset();
    modal.classList.add('open');
}

function closeDeleteModal() {
    document.getElementById('deleteAuthModal').classList.remove('open');
}

window.onclick = function(event) {
    const modal = document.getElementById('deleteAuthModal');
    if (event.target == modal) {
        closeDeleteModal();
    }
}

// Bulk Actions Logic
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const panel = document.getElementById('bulkActionBar');
    const badge = document.getElementById('selectedCountBadge');
    
    if (checkboxes.length > 0) {
        panel.style.display = 'flex';
        badge.textContent = checkboxes.length + ' selected';
    } else {
        panel.style.display = 'none';
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
    }
}

function toggleBulkActionInputs() {
    const action = document.getElementById('bulkActionSelect').value;
    
    document.getElementById('bulkDateInputContainer').style.display = action === 'change_date' ? 'block' : 'none';
    document.getElementById('bulkRepInputContainer').style.display = action === 'change_rep' ? 'block' : 'none';
    document.getElementById('bulkPasswordInputContainer').style.display = action === 'delete' ? 'block' : 'none';
    
    // Add required attributes dynamically
    document.querySelector('input[name="bulk_date"]').required = action === 'change_date';
    document.querySelector('select[name="bulk_rep"]').required = action === 'change_rep';
    document.querySelector('input[name="admin_password"]').required = action === 'delete';
}

function handleBulkSubmit(e) {
    const action = document.getElementById('bulkActionSelect').value;
    if (!action) {
        alert('Please select a bulk action.');
        e.preventDefault();
        return false;
    }
    
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('No records selected.');
        e.preventDefault();
        return false;
    }
    
    if (action === 'delete') {
        const password = document.querySelector('input[name="admin_password"]').value;
        if (!password) {
            alert('Admin password is required for bulk deletion.');
            e.preventDefault();
            return false;
        }
        if (!confirm('Are you sure you want to permanently delete the ' + checkboxes.length + ' selected records? This action requires administrator validation and cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    }
    return true;
}
</script>
