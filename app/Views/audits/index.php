<?php
// Filters values
$filterUser = $data['filters']['user_id'] ?? '';
$filterModule = $data['filters']['module'] ?? '';
$filterAction = $data['filters']['action'] ?? '';
$filterDateFrom = $data['filters']['date_from'] ?? '';
$filterDateTo = $data['filters']['date_to'] ?? '';
$filterSearch = $data['filters']['search'] ?? '';

$stats = $data['stats'] ?? [
    'total_actions' => 0,
    'db_modifications' => 0,
    'system_alerts' => 0,
    'unique_operators' => 0
];
?>

<!-- Inter Font, Phosphor Icons, and FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — AUDIT TRAIL THEME
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.08);
    --c-fill2:        rgba(120,120,128,0.12);
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
    --c-purple-light: #f5e6fc;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-sm: 8px;
    --r-md: 12px;
    --r-lg: 18px;
    --r-xl: 24px;
    --r-pill: 999px;

    --ease-ios: cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast: 0.18s;
}

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #0f0f17;
        --c-surface:      #1c1c28;
        --c-surface2:     #14141f;
        --c-fill:         rgba(255,255,255,0.06);
        --c-fill2:        rgba(255,255,255,0.1);
        --c-separator:    rgba(255,255,255,0.1);
        --c-separator2:   rgba(255,255,255,0.05);
        --t-primary:   #f5f5f7;
        --t-secondary: #a1a1aa;
        --t-tertiary:  #71717a;
        --t-label:     #52525b;
    }
}

.audit-root {
    font-family: var(--f-system);
    font-size: 14px;
    color: var(--t-primary);
    background: var(--c-bg);
    min-height: 100vh;
    padding: 24px 24px 80px;
    box-sizing: border-box;
}

.audit-wrap {
    max-width: 1400px;
    margin: 0 auto;
}

/* Header UI */
.audit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1.5px solid var(--c-separator);
}

.audit-title h1 {
    font-size: 26px;
    font-weight: 700;
    margin: 0 0 4px 0;
    letter-spacing: -0.02em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.audit-title p {
    margin: 0;
    font-size: 14px;
    color: var(--t-secondary);
}

/* Dashboard Summary Cards */
.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    transition: transform var(--dur-fast) var(--ease-ios), box-shadow var(--dur-fast) var(--ease-ios);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}

.stat-card.blue::before   { background: var(--c-blue); }
.stat-card.green::before  { background: var(--c-green); }
.stat-card.red::before    { background: var(--c-red); }
.stat-card.purple::before { background: var(--c-purple); }

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 44px; height: 44px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-card.blue  .stat-icon { background: var(--c-blue-light);   color: var(--c-blue); }
.stat-card.green  .stat-icon { background: var(--c-green-light);  color: var(--c-green); }
.stat-card.red    .stat-icon { background: var(--c-red-light);    color: var(--c-red); }
.stat-card.purple .stat-icon { background: var(--c-purple-light); color: var(--c-purple); }

.stat-info { display: flex; flex-direction: column; }
.stat-num {
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.04em;
    color: var(--t-primary);
    line-height: 1.1;
    margin-bottom: 2px;
}
.stat-lbl {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--t-label);
}

/* Filter Shelf Layout */
.filter-shelf {
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--t-label);
}

.filter-input {
    background: var(--c-fill);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 8px 12px;
    font-size: 13px;
    color: var(--t-primary);
    outline: none;
    transition: border-color 0.15s, background-color 0.15s;
    width: 100%;
    box-sizing: border-box;
}

.filter-input:focus {
    border-color: var(--c-blue);
    background: var(--c-fill2);
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.btn {
    padding: 8px 16px;
    border-radius: var(--r-sm);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
}

.btn-primary {
    background: var(--c-blue);
    color: #fff;
}

.btn-primary:hover {
    background: #0062cc;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--c-fill);
    color: var(--t-primary);
    text-decoration: none;
}

.btn-secondary:hover {
    background: var(--c-fill2);
}

.btn-success {
    background: var(--c-green);
    color: #fff;
    text-decoration: none;
}

.btn-success:hover {
    background: #2cb04e;
    transform: translateY(-1px);
}

/* Ledger Log List styling */
.logs-table-card {
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.table-wrapper {
    overflow-x: auto;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.logs-table th, .logs-table td {
    padding: 14px 18px;
    border-bottom: 0.5px solid var(--c-separator);
    font-size: 13px;
    vertical-align: middle;
}

.logs-table th {
    background-color: var(--c-surface2);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 11px;
    color: var(--t-label);
}

.log-tr {
    transition: background-color var(--dur-fast) var(--ease-ios);
    cursor: pointer;
}

.log-tr:hover {
    background-color: var(--c-fill);
}

/* Clean UI Status badges */
.badge {
    padding: 4px 10px;
    border-radius: var(--r-pill);
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-create { background: var(--c-green-light); color: var(--c-green); }
.badge-edit { background: var(--c-orange-light); color: var(--c-orange); }
.badge-delete { background: var(--c-red-light); color: var(--c-red); }
.badge-login { background: var(--c-blue-light); color: var(--c-blue); }
.badge-warning { background: var(--c-purple-light); color: var(--c-purple); }
.badge-default { background: var(--c-fill); color: var(--t-secondary); }

.module-tag {
    font-weight: 600;
    font-size: 11px;
    background: var(--c-fill);
    color: var(--t-secondary);
    padding: 3px 8px;
    border-radius: var(--r-sm);
    letter-spacing: 0.02em;
}

.ip-text {
    font-family: var(--f-mono);
    font-size: 11px;
    color: var(--t-secondary);
    background: var(--c-surface2);
    padding: 2px 6px;
    border-radius: 4px;
    border: 0.5px solid var(--c-separator);
}

.btn-inspect {
    background: var(--c-fill);
    color: var(--t-primary);
    padding: 6px 12px;
    border-radius: var(--r-sm);
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: none;
    cursor: pointer;
    transition: all var(--dur-fast);
}

.btn-inspect:hover {
    background: var(--c-blue);
    color: #fff;
}

/* Forensic Details Modal Box */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 3000;
    opacity: 0;
    transition: opacity 0.3s var(--ease-ios);
}

.modal-overlay.show {
    display: flex;
    opacity: 1;
}

.modal-box {
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-md);
    width: 92%;
    max-width: 1050px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-xl);
    animation: modalPop 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}

@keyframes modalPop {
    from { transform: scale(0.96) translateY(8px); }
    to { transform: scale(1) translateY(0); }
}

.modal-header {
    padding: 16px 20px;
    border-bottom: 0.5px solid var(--c-separator);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--c-surface2);
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-close {
    cursor: pointer;
    font-size: 20px;
    color: var(--t-secondary);
    background: none;
    border: none;
    outline: none;
    transition: color 0.15s;
}

.modal-close:hover {
    color: var(--c-red);
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.meta-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    background: var(--c-surface2);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    padding: 14px 16px;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 10px;
    text-transform: uppercase;
    color: var(--t-label);
    font-weight: 700;
    letter-spacing: 0.05em;
}

.meta-value {
    font-size: 13px;
    font-weight: 600;
}

/* Diff Ledger styling */
.diff-header-row {
    display: grid;
    grid-template-columns: 200px 1fr 1fr;
    gap: 16px;
    padding: 8px 12px;
    border-bottom: 1.5px solid var(--c-separator);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    color: var(--t-label);
}

.diff-grid {
    display: flex;
    flex-direction: column;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-sm);
    overflow: hidden;
}

.diff-row {
    display: grid;
    grid-template-columns: 200px 1fr 1fr;
    gap: 16px;
    padding: 10px 12px;
    border-bottom: 0.5px solid var(--c-separator);
    font-size: 12px;
}

.diff-row:last-child {
    border-bottom: none;
}

.diff-key {
    font-family: var(--f-mono);
    font-weight: 700;
    color: var(--t-primary);
    word-break: break-all;
}

.diff-val {
    overflow-x: auto;
    word-break: break-all;
}

.diff-val pre {
    margin: 0;
    font-family: var(--f-mono);
    font-size: 11px;
    white-space: pre-wrap;
}

/* Diff states */
.row-added { background-color: rgba(52, 199, 89, 0.05); }
.row-removed { background-color: rgba(255, 59, 48, 0.05); }
.row-modified { background-color: rgba(255, 149, 0, 0.05); }

.value-added { color: var(--c-green); font-weight: 600; }
.value-removed { color: var(--c-red); text-decoration: line-through; }
.value-none { color: var(--t-tertiary); font-style: italic; }
</style>

<div class="audit-root">
    <div class="audit-wrap">
        
        <!-- Header -->
        <div class="audit-header">
            <div class="audit-title">
                <h1><i class="ph ph-shield-check" style="color: var(--c-red);"></i> System Audit Trail</h1>
                <p>Forensic immutable operations logs and ledger snapshots of administrative changes.</p>
            </div>
            <div class="action-buttons">
                <a href="<?= APP_URL ?>/audit/export?<?= http_build_query($data['filters']) ?>" class="btn btn-success">
                    <i class="ph ph-download-simple"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Dashboard Summary Stats Row -->
        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="ph ph-list-bullets"></i></div>
                <div class="stat-info">
                    <span class="stat-num"><?= number_format($stats['total_actions']) ?></span>
                    <span class="stat-lbl">Total Operations</span>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="ph ph-database"></i></div>
                <div class="stat-info">
                    <span class="stat-num"><?= number_format($stats['db_modifications']) ?></span>
                    <span class="stat-lbl">Database Edits</span>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="ph ph-warning-octagon"></i></div>
                <div class="stat-info">
                    <span class="stat-num"><?= number_format($stats['system_alerts']) ?></span>
                    <span class="stat-lbl">System Alerts</span>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon"><i class="ph ph-users-three"></i></div>
                <div class="stat-info">
                    <span class="stat-num"><?= number_format($stats['unique_operators']) ?></span>
                    <span class="stat-lbl">Unique Users</span>
                </div>
            </div>
        </div>

        <!-- Filters Shelves -->
        <div class="filter-shelf">
            <form class="filter-grid" method="GET" action="<?= APP_URL ?>/audit">
                <div class="filter-group">
                    <label for="search">Global Search</label>
                    <input type="text" id="search" name="search" class="filter-input" placeholder="Ref ID, Desc..." value="<?= htmlspecialchars($filterSearch) ?>">
                </div>

                <div class="filter-group">
                    <label for="user_id">User Operator</label>
                    <select id="user_id" name="user_id" class="filter-input">
                        <option value="">All Users</option>
                        <?php foreach($data['users'] as $user): ?>
                            <option value="<?= $user->id ?>" <?= $filterUser == $user->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user->username) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="module">Module</label>
                    <select id="module" name="module" class="filter-input">
                        <option value="">All Modules</option>
                        <?php foreach($data['modules'] as $mod): ?>
                            <option value="<?= htmlspecialchars($mod->module) ?>" <?= $filterModule == $mod->module ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mod->module) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="action">Action Type</label>
                    <select id="action" name="action" class="filter-input">
                        <option value="">All Actions</option>
                        <?php foreach($data['actions'] as $act): ?>
                            <option value="<?= htmlspecialchars($act->action) ?>" <?= $filterAction == $act->action ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act->action) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from" class="filter-input" value="<?= htmlspecialchars($filterDateFrom) ?>">
                </div>

                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" class="filter-input" value="<?= htmlspecialchars($filterDateTo) ?>">
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary"><i class="ph ph-funnel"></i> Search</button>
                    <a href="<?= APP_URL ?>/audit" class="btn btn-secondary"><i class="ph ph-arrows-counter-clockwise"></i> Reset</a>
                </div>
            </form>
        </div>

        <!-- Ledger Table List -->
        <div class="logs-table-card">
            <div class="table-wrapper">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th style="width: 170px;">Timestamp</th>
                            <th>Operator</th>
                            <th>Action Type</th>
                            <th>Module</th>
                            <th style="width: 35%;">Log Description</th>
                            <th>IP Address</th>
                            <th style="text-align: center; width: 100px;">Forensics</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['logs'])): ?>
                            <tr><td colspan="7" style="text-align: center; color: var(--t-tertiary); padding: 40px;">No audit trail events match the current filter parameters.</td></tr>
                        <?php else: foreach ($data['logs'] as $log): ?>
                            <?php
                            $cleanAct = strtolower($log->action ?? '');
                            $badgeClass = 'badge-default';
                            if (strpos($cleanAct, 'create') !== false || strpos($cleanAct, 'post') !== false) $badgeClass = 'badge-create';
                            elseif (strpos($cleanAct, 'edit') !== false || strpos($cleanAct, 'update') !== false) $badgeClass = 'badge-edit';
                            elseif (strpos($cleanAct, 'delete') !== false || strpos($cleanAct, 'void') !== false) $badgeClass = 'badge-delete';
                            elseif (strpos($cleanAct, 'login') !== false || strpos($cleanAct, 'logout') !== false) $badgeClass = 'badge-login';
                            elseif (strpos($cleanAct, 'warning') !== false || strpos($cleanAct, 'fail') !== false) $badgeClass = 'badge-warning';

                            $hasSnapshot = (!empty($log->old_values) || !empty($log->new_values));
                            ?>
                            <tr class="log-tr"
                                data-old="<?= htmlspecialchars($log->old_values ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                data-new="<?= htmlspecialchars($log->new_values ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-action="<?= htmlspecialchars($log->action) ?>"
                                data-module="<?= htmlspecialchars($log->module) ?>"
                                data-desc="<?= htmlspecialchars($log->description) ?>"
                                data-user="<?= htmlspecialchars($log->username ?? 'System') ?>"
                                data-time="<?= date('M d, Y H:i:s', strtotime($log->created_at)) ?>"
                                data-ip="<?= htmlspecialchars($log->ip_address) ?>"
                                data-device="<?= htmlspecialchars($log->browser_device ?? 'N/A') ?>"
                                data-record="<?= htmlspecialchars($log->record_id ?? 'N/A') ?>">
                                
                                <td style="color: var(--t-secondary);"><?= date('M d, Y H:i:s', strtotime($log->created_at)) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($log->username ?? 'System') ?></strong><br>
                                    <span style="font-size: 10px; color: var(--t-label);"><?= htmlspecialchars($log->role ?? 'N/A') ?></span>
                                </td>
                                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($log->action) ?></span></td>
                                <td><span class="module-tag"><?= htmlspecialchars($log->module) ?></span></td>
                                <td>
                                    <?= htmlspecialchars($log->description) ?>
                                    <?php if ($log->record_id): ?>
                                        <span style="font-size: 10px; background: var(--c-fill); padding: 2px 6px; border-radius: 4px; font-family: var(--f-mono);">ID: <?= $log->record_id ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="ip-text"><?= htmlspecialchars($log->ip_address) ?></span></td>
                                <td style="text-align: center;">
                                    <?php if ($hasSnapshot): ?>
                                        <button type="button" class="btn-inspect" onclick="event.stopPropagation(); openInspectModal(this.closest('.log-tr'))">
                                            <i class="ph ph-eye"></i> Details
                                        </button>
                                    <?php else: ?>
                                        <span style="color: var(--t-tertiary); font-size: 11px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Forensic Diff View Inspect Modal -->
<div class="modal-overlay" id="inspectModal" onclick="closeInspectModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="ph ph-shield-check" style="color: var(--c-red); font-size: 18px;"></i> Forensic Audit Log Details
            </h3>
            <button class="modal-close" onclick="closeInspectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Metadata Grid Info -->
            <div class="meta-summary">
                <div class="meta-item">
                    <span class="meta-label">User / Operator</span>
                    <span class="meta-value" id="meta-user"></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Action & Module</span>
                    <span class="meta-value" id="meta-action"></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Reference ID</span>
                    <span class="meta-value" id="meta-record"></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Timestamp</span>
                    <span class="meta-value" id="meta-time"></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">IP Address</span>
                    <span class="meta-value" id="meta-ip"></span>
                </div>
                <div class="meta-item" style="grid-column: span 2;">
                    <span class="meta-label">Browser & Device User-Agent</span>
                    <span class="meta-value" id="meta-device" style="font-family: var(--f-mono); font-size: 11px;"></span>
                </div>
            </div>

            <!-- Event Description text -->
            <div>
                <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--t-secondary);">Event Description</h4>
                <p id="meta-desc" style="margin: 0; padding: 12px; background: var(--c-surface2); border-left: 4px solid var(--c-blue); border-radius: 4px; font-size: 13px;"></p>
            </div>

            <!-- Ledger State Diff section -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--t-secondary);">Ledger State Diff (Old vs New Snapshots)</h4>
                    <button type="button" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11px; height: 26px;" onclick="exportForensicJson()">
                        <i class="ph ph-file-code"></i> Export Snapshot JSON
                    </button>
                </div>
                
                <div class="diff-header-row">
                    <div>Data Field / Key</div>
                    <div>Before / Old Value</div>
                    <div>After / New Value</div>
                </div>
                <div id="diff-view-target"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Bind click events on rows
    document.querySelectorAll('.log-tr').forEach(row => {
        row.addEventListener('click', () => {
            if (row.getAttribute('data-old') || row.getAttribute('data-new')) {
                openInspectModal(row);
            }
        });
    });

    let currentLogData = { oldVal: null, newVal: null, desc: '' };

    function auditEscapeHtml(text) {
        if (text === undefined || text === null) return '';
        const str = typeof text === 'string' ? text : String(text);
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatDiffValue(val, isNew, status) {
        if (val === undefined) {
            return isNew ? '<span class="value-none">N/A</span>' : '<span class="value-none">Deleted</span>';
        }
        if (val === null) {
            return '<span class="value-none">null</span>';
        }
        
        let parsed = null;
        let isJson = false;
        if (typeof val === 'string' && (val.trim().startsWith('{') || val.trim().startsWith('['))) {
            try {
                parsed = JSON.parse(val);
                isJson = true;
            } catch(e) {}
        } else if (typeof val === 'object') {
            parsed = val;
            isJson = true;
        }

        let spanClass = '';
        if (status === 'added') spanClass = 'value-added';
        else if (status === 'removed') spanClass = 'value-removed';

        if (isJson && parsed !== null) {
            // Check if it is an array of objects
            if (Array.isArray(parsed)) {
                if (parsed.length === 0) {
                    return `<span class="value-none ${spanClass}">Empty List []</span>`;
                }

                let uniqueKeys = new Set();
                parsed.forEach(item => {
                    if (item && typeof item === 'object') {
                        Object.keys(item).forEach(k => uniqueKeys.add(k));
                    }
                });

                let tableHtml = `<div class="diff-json-table-wrapper ${spanClass}" style="margin-top: 4px; overflow-x: auto; max-width: 100%; border: 0.5px solid var(--c-separator); border-radius: 6px; background: var(--c-surface2);">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px; text-align: left;">
                        <thead>
                            <tr style="background: rgba(0,0,0,0.03); border-bottom: 0.5px solid var(--c-separator);">`;
                
                const preferredOrder = ['sku', 'attribute', 'name', 'value_name', 'price', 'wholesale_price', 'cost', 'cost_price', 'qty', 'quantity_on_hand'];
                let orderedKeys = preferredOrder.filter(k => uniqueKeys.has(k));
                uniqueKeys.forEach(k => {
                    if (!orderedKeys.includes(k) && k !== 'id') {
                        orderedKeys.push(k);
                    }
                });

                orderedKeys.forEach(k => {
                    let label = k.replace(/_/g, ' ');
                    label = label.charAt(0).toUpperCase() + label.slice(1);
                    tableHtml += `<th style="padding: 6px 8px; font-weight: 700; border-right: 0.5px solid var(--c-separator);">${label}</th>`;
                });
                tableHtml += `</tr></thead><tbody>`;

                parsed.forEach((item, idx) => {
                    tableHtml += `<tr style="border-bottom: 0.5px solid var(--c-separator); ${idx % 2 === 1 ? 'background: rgba(0,0,0,0.01);' : ''}">`;
                    orderedKeys.forEach(k => {
                        let cellVal = item[k];
                        if (cellVal === undefined || cellVal === null) {
                            cellVal = '-';
                        } else if (typeof cellVal === 'object') {
                            cellVal = JSON.stringify(cellVal);
                        } else if (k === 'price' || k === 'wholesale_price' || k === 'cost' || k === 'cost_price') {
                            cellVal = 'Rs ' + parseFloat(cellVal).toFixed(2);
                        }
                        tableHtml += `<td style="padding: 6px 8px; border-right: 0.5px solid var(--c-separator);">${auditEscapeHtml(cellVal)}</td>`;
                    });
                    tableHtml += `</tr>`;
                });
                tableHtml += `</tbody></table></div>`;
                return tableHtml;
            } else {
                // It is a simple object
                let objHtml = `<div class="diff-json-obj ${spanClass}" style="margin-top: 4px; display: flex; flex-direction: column; gap: 4px; background: var(--c-surface2); border: 0.5px solid var(--c-separator); border-radius: 6px; padding: 8px;">`;
                for (let k in parsed) {
                    let label = k.replace(/_/g, ' ');
                    label = label.charAt(0).toUpperCase() + label.slice(1);
                    let cellVal = parsed[k];
                    if (cellVal === undefined || cellVal === null) {
                        cellVal = 'null';
                    } else if (typeof cellVal === 'object') {
                        cellVal = JSON.stringify(cellVal);
                    }
                    objHtml += `<div style="font-size: 11px;"><strong>${auditEscapeHtml(label)}:</strong> ${auditEscapeHtml(cellVal)}</div>`;
                }
                objHtml += `</div>`;
                return objHtml;
            }
        }

        const isObject = typeof val === 'object';
        const str = isObject ? JSON.stringify(val, null, 2) : String(val);
        const escaped = auditEscapeHtml(str);
        
        if (isObject) {
            return `<pre class="${spanClass}">${escaped}</pre>`;
        } else {
            return `<span class="${spanClass}">${escaped}</span>`;
        }
    }

    function generateDiffHtml(oldVal, newVal) {
        if (!oldVal && !newVal) {
            return '<p style="padding: 20px; text-align: center; color: var(--t-tertiary);">No forensic snapshots recorded for this action.</p>';
        }

        let oldObj = null;
        let newObj = null;

        try {
            oldObj = oldVal ? (typeof oldVal === 'string' ? JSON.parse(oldVal) : oldVal) : null;
        } catch(e) { oldObj = oldVal; }

        try {
            newObj = newVal ? (typeof newVal === 'string' ? JSON.parse(newVal) : newVal) : null;
        } catch(e) { newObj = newVal; }

        if ((oldObj && typeof oldObj !== 'object') || (newObj && typeof newObj !== 'object')) {
            return `
                <div class="diff-grid">
                    <div class="diff-row row-modified">
                        <div class="diff-key">Value</div>
                        <div class="diff-val">${formatDiffValue(oldObj, false, 'removed')}</div>
                        <div class="diff-val">${formatDiffValue(newObj, true, 'added')}</div>
                    </div>
                </div>
            `;
        }

        oldObj = oldObj || {};
        newObj = newObj || {};

        const allKeys = new Set([...Object.keys(oldObj), ...Object.keys(newObj)]);
        let html = '<div class="diff-grid">';

        allKeys.forEach(key => {
            const valOld = oldObj[key];
            const valNew = newObj[key];

            const isOldDefined = valOld !== undefined;
            const isNewDefined = valNew !== undefined;

            let rowClass = '';
            let oldHtml = '';
            let newHtml = '';

            if (!isOldDefined) {
                rowClass = 'row-added';
                oldHtml = formatDiffValue(undefined, false, 'added');
                newHtml = formatDiffValue(valNew, true, 'added');
            } else if (!isNewDefined) {
                rowClass = 'row-removed';
                oldHtml = formatDiffValue(valOld, false, 'removed');
                newHtml = formatDiffValue(undefined, true, 'removed');
            } else if (JSON.stringify(valOld) !== JSON.stringify(valNew)) {
                rowClass = 'row-modified';
                oldHtml = formatDiffValue(valOld, false, 'removed');
                newHtml = formatDiffValue(valNew, true, 'added');
            } else {
                rowClass = 'row-unchanged';
                oldHtml = formatDiffValue(valOld, false, 'unchanged');
                newHtml = formatDiffValue(valNew, true, 'unchanged');
            }

            html += `
                <div class="diff-row ${rowClass}">
                    <div class="diff-key">${auditEscapeHtml(key)}</div>
                    <div class="diff-val">${oldHtml}</div>
                    <div class="diff-val">${newHtml}</div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    function openInspectModal(row) {
        const oldVal = row.getAttribute('data-old');
        const newVal = row.getAttribute('data-new');
        
        currentLogData = {
            oldVal: oldVal ? JSON.parse(oldVal) : null,
            newVal: newVal ? JSON.parse(newVal) : null,
            desc: row.getAttribute('data-desc')
        };

        document.getElementById('meta-user').innerText = row.getAttribute('data-user');
        document.getElementById('meta-action').innerText = row.getAttribute('data-action') + ' (' + row.getAttribute('data-module') + ')';
        document.getElementById('meta-record').innerText = row.getAttribute('data-record') || 'N/A';
        document.getElementById('meta-time').innerText = row.getAttribute('data-time');
        document.getElementById('meta-ip').innerText = row.getAttribute('data-ip');
        document.getElementById('meta-device').innerText = row.getAttribute('data-device');
        document.getElementById('meta-desc').innerText = row.getAttribute('data-desc');

        const diffContainer = document.getElementById('diff-view-target');
        diffContainer.innerHTML = generateDiffHtml(oldVal, newVal);

        document.getElementById('inspectModal').classList.add('show');
    }

    function closeInspectModal() {
        document.getElementById('inspectModal').classList.remove('show');
    }

    function exportForensicJson() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(currentLogData, null, 2));
        const downloadAnchor = document.createElement('a');
        downloadAnchor.setAttribute("href", dataStr);
        downloadAnchor.setAttribute("download", "forensic_snapshot_" + new Date().toISOString().slice(0,10) + ".json");
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        downloadAnchor.remove();
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeInspectModal();
        }
    });
</script>