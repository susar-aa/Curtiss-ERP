
<?php $isHistory = $data['is_history'] ?? false; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<?php include __DIR__ . '/index.css.php'; ?>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="ios-toast success" id="successToast">
        <div class="ios-toast-content">
            <i class="fa-solid fa-circle-check ios-toast-icon"></i>
            <span class="ios-toast-message"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <button class="ios-toast-close" onclick="closeToast('successToast')">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="ios-toast error" id="errorToast">
        <div class="ios-toast-content">
            <i class="fa-solid fa-circle-xmark ios-toast-icon"></i>
            <span class="ios-toast-message"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <button class="ios-toast-close" onclick="closeToast('errorToast')">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<script>
    function closeToast(id) {
        const toast = document.getElementById(id);
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -20px) scale(0.95)';
            setTimeout(() => {
                toast.remove();
            }, 400);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const successToast = document.getElementById('successToast');
        if (successToast) {
            setTimeout(() => {
                closeToast('successToast');
            }, 5000);
        }
        const errorToast = document.getElementById('errorToast');
        if (errorToast) {
            setTimeout(() => {
                closeToast('errorToast');
            }, 5000);
        }
    });
</script>

<div class="header-actions" style="margin-bottom: 15px; display: flex; justify-content: <?= $isHistory ? 'flex-start' : 'flex-end' ?>; align-items: center; flex-wrap: wrap; gap: 10px;">
    <?php if ($isHistory): ?>
        <div>
            <a href="<?= APP_URL ?>/RepTracking/index" style="padding: 10px 18px; border: none; background: #64748b; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(100, 116, 139, 0.2); transition: all 0.2s ease; text-decoration: none;">
                <i class="ph-bold ph-arrow-left"></i> Back to Control Panel
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- TOP GLOBAL MULTI-FACETED FILTERS BAR -->
<div class="global-status-filter-bar" style="display: flex; gap: 12px; background: var(--c-surface); border: 0.5px solid var(--c-separator); border-radius: var(--r-md); padding: 12px 20px; margin-bottom: 20px; box-shadow: var(--shadow-sm); align-items: center; justify-content: space-between; flex-wrap: wrap;">
    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap; flex: 1;">
        <!-- Rep-wise filter -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 10px; font-weight: 700; color: var(--t-label); text-transform: uppercase; letter-spacing: 0.05em;">Representative</label>
            <select id="filterRepSelect"  style="padding: 6px 12px; border: 0.5px solid var(--c-separator); border-radius: var(--r-xs); font-size: 13px; background: var(--c-surface2); color: var(--t-primary); min-width: 160px; outline: none;">
                <option value="">All Representatives</option>
                <?php
                $db = new Database();
                $db->query("
                    SELECT DISTINCT COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name
                    FROM rep_daily_routes r
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN employees e ON u.email = e.email
                    UNION
                    SELECT DISTINCT COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name
                    FROM users u
                    LEFT JOIN employees e ON u.employee_id = e.id
                    WHERE u.role = 'rep' AND (u.status IS NULL OR u.status = 'Active')
                ");
                $dbReps = $db->resultSet() ?: [];
                $repNames = [];
                foreach ($dbReps as $r) {
                    $fullName = trim($r->first_name . ' ' . $r->last_name);
                    if ($fullName !== '' && !in_array($fullName, $repNames)) {
                        $repNames[] = $fullName;
                    }
                }
                sort($repNames);
                foreach ($repNames as $name): ?>
                    <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Route-wise filter -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 10px; font-weight: 700; color: var(--t-label); text-transform: uppercase; letter-spacing: 0.05em;">Route</label>
            <select id="filterRouteSelect"  style="padding: 6px 12px; border: 0.5px solid var(--c-separator); border-radius: var(--r-xs); font-size: 13px; background: var(--c-surface2); color: var(--t-primary); min-width: 160px; outline: none;">
                <option value="">All Routes</option>
                <?php 
                $uniqueRouteNames = [];
                foreach ($data['routes'] as $r) {
                    if (!in_array($r->route_name, $uniqueRouteNames)) {
                        $uniqueRouteNames[] = $r->route_name;
                    }
                }
                sort($uniqueRouteNames);
                foreach ($uniqueRouteNames as $name): ?>
                    <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Date-wise filter -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 10px; font-weight: 700; color: var(--t-label); text-transform: uppercase; letter-spacing: 0.05em;">Date</label>
            <input type="date" id="filterDateInput"  style="padding: 5px 12px; border: 0.5px solid var(--c-separator); border-radius: var(--r-xs); font-size: 13px; background: var(--c-surface2); color: var(--t-primary); min-width: 140px; outline: none;">
        </div>
        <!-- Territory-wise filter -->
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 10px; font-weight: 700; color: var(--t-label); text-transform: uppercase; letter-spacing: 0.05em;">Territory</label>
            <select id="filterTerritorySelect"  style="padding: 6px 12px; border: 0.5px solid var(--c-separator); border-radius: var(--r-xs); font-size: 13px; background: var(--c-surface2); color: var(--t-primary); min-width: 160px; outline: none;">
                <option value="">All Territories</option>
                <?php foreach ($data['mca_areas'] as $area): ?>
                    <option value="<?= htmlspecialchars($area->name) ?>"><?= htmlspecialchars($area->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div style="display: flex; align-items: flex-end; align-self: flex-end;">
        <button id="auto-evt-button-1" type="button"  style="padding: 7px 14px; border: 0.5px solid var(--c-separator); background: var(--c-surface2); color: var(--t-secondary); border-radius: var(--r-xs); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all var(--dur-fast);"  >
            <i class="ph ph-arrow-counter-clockwise"></i> Clear Filters
        </button>
    </div>
</div>

<div class="app-workspace">
    <!-- Left Pane: Routes Master List -->
    <div class="pane-left">
        <div style="flex: 1; overflow-y: auto;" id="routeListItemsContainer">
            <?php 
            $routes = $data['routes'];
            include __DIR__ . '/_route_list_items.php'; 
            ?>
        </div>
        <div id="routePaginationContainer" style="width:100%; flex-shrink:0;">
            <?php 
            $pagination = $data['pagination'] ?? null;
            include __DIR__ . '/_pagination.php'; 
            ?>
        </div>
    </div>

    <!-- Middle Pane: Workspace -->
    <div class="pane-middle">
        <!-- Header -->
        <div class="mid-header" id="midHeader" style="display: none !important; visibility: hidden; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; background: var(--c-surface); border-bottom: 1px solid var(--c-separator);">
            
            <!-- Left Side: Back button + Route Name + Status Badge -->
            <div style="display: flex; align-items: center; gap: 16px; min-width: 0;">
                <button id="auto-evt-button-2" type="button"  style="background: var(--c-fill); border: none; color: var(--c-blue); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: var(--r-sm);">
                    <i class="ph-bold ph-arrow-left"></i> Back
                </button>
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                        <span id="mhRouteNumber" style="font-family: var(--f-mono); font-weight: 700; background: var(--c-blue-light); color: var(--c-blue); padding: 2px 6px; border-radius: var(--r-xs); font-size: 11px;">Route #RT-00000</span>
                        <span id="mhRouteStatusBadge" style="font-size: 10px; font-weight: 700; padding: 1.5px 6px; border-radius: var(--r-pill); background: var(--c-orange-light); color: var(--c-orange); border: 0.5px solid var(--c-orange);">Active</span>
                    </div>
                    <h2 style="margin:0; color:var(--t-primary); font-size: 16px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px;" id="mhRouteName">Route Name</h2>
                </div>
            </div>

            <!-- Middle: Metadata details (Representative & Odometer) -->
            <div style="display: flex; align-items: center; gap: 15px; font-size: 12px; color: var(--t-secondary); background: var(--c-surface2); padding: 8px 14px; border-radius: var(--r-md); border: 0.5px solid var(--c-separator);">
                <div>Rep: <strong id="mhRepName" style="color: var(--t-primary);"></strong></div>
                <div style="width: 1px; height: 12px; background: var(--c-separator);"></div>
                <div>ODO: <strong id="mhStart"></strong> - <strong id="mhEnd"></strong></div>
            </div>

            <!-- Right Side: Stats + Global Action Buttons -->
            <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                <div class="stat-box" style="padding: 6px 12px; border: 0.5px solid var(--c-separator); border-radius: var(--r-sm); text-align: left; display: flex; flex-direction: column;">
                    <span style="font-size: 9px; color: var(--t-label); text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">Sales Value</span>
                    <strong style="color: var(--c-green); font-size: 14px; font-weight: 700; font-family: var(--f-mono);">Rs <span id="mhSales"></span></strong>
                </div>
                <div class="stat-box" style="padding: 6px 12px; border: 0.5px solid var(--c-separator); border-radius: var(--r-sm); text-align: left; display: flex; flex-direction: column;">
                    <span style="font-size: 9px; color: var(--t-label); text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">Bills</span>
                    <strong id="mhBills" style="font-size: 14px; font-weight: 700; font-family: var(--f-mono);"></strong>
                </div>

                <div style="display: flex; gap: 6px; align-items: center; border-left: 1px solid var(--c-separator); padding-left: 12px;">
                    <button id="auto-evt-button-3" type="button"  style="padding: 8px 12px; border: 0.5px solid var(--c-blue-mid); background: var(--c-surface); color: var(--c-blue); border-radius: var(--r-sm); font-weight: 600; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 6px; box-shadow: var(--shadow-xs);">
                        <i class="ph ph-swap"></i> Switch
                    </button>
                    <button type="button" id="btnViewMap"  style="padding: 8px 12px; border: none; background: var(--c-orange); color: #fff; border-radius: var(--r-sm); font-weight: 600; cursor: pointer; font-size: 12px; display: none; align-items: center; gap: 4px; box-shadow: var(--shadow-xs);"><i class="ph ph-map-pin"></i> Map</button>
                    <button type="button" id="btnUnbindRoute" style="padding: 8px 12px; border: none; background: #dc2626; color: #fff; border-radius: var(--r-sm); font-weight: 600; cursor: pointer; font-size: 12px; display: none; align-items: center; gap: 4px; box-shadow: var(--shadow-xs);"><i class="ph ph-link-break"></i> Undo Bind</button>
                </div>
            </div>
        </div>

        <!-- Route Workspace Tabs -->
        <div class="scroll-tabs" id="routeWorkspaceTabs" style="display: none; border-bottom: 2px solid #cbd5e1; margin-bottom: 0;">
            <button id="auto-evt-button-4" class="scroll-tab-btn active" ><i class="ph ph-clipboard-text"></i> 1. Route Details</button>
            <button id="auto-evt-button-6" class="scroll-tab-btn" ><i class="ph ph-gear"></i> 2. Bill Adjustments</button>
            <button id="auto-evt-button-7" class="scroll-tab-btn" ><i class="ph ph-truck"></i> 3. Loading</button>
            <button id="auto-evt-button-8" class="scroll-tab-btn" ><i class="ph ph-scales"></i> 4. Variance Audit</button>
            <button id="auto-evt-button-9" class="scroll-tab-btn" ><i class="ph ph-map-trifold"></i> 5. Delivery Arrangement</button>
            <button id="auto-evt-button-11" class="scroll-tab-btn" ><i class="ph ph-steering-wheel"></i> 6. Delivery</button>
            <button id="btnTabExpenses" class="scroll-tab-btn" ><i class="ph ph-receipt"></i> 7. Expenses</button>
            <button id="auto-evt-button-10" class="scroll-tab-btn" ><i class="ph ph-currency-dollar"></i> 8. Reconciliation</button>
            <button id="auto-evt-button-12" class="scroll-tab-btn" ><i class="ph ph-package"></i> 9. Return Stock Verification</button>
            <button id="auto-evt-button-13" class="scroll-tab-btn" ><i class="ph ph-briefcase"></i> 10. Payments</button>
            <button id="btnTabFinalize" class="scroll-tab-btn" ><i class="ph ph-flag-checkered"></i> 11. Finalize</button>
        </div>

        <!-- Workspace Layout Container (Sidebar + Content Body) -->
        <div id="workspaceLayoutWrapper" style="display: none; flex: 1; flex-direction: row; min-height: 0; width: 100%;">
            <!-- Left Side: Workflow Sidebar -->
            <div class="workflow-sidebar" id="workflowSidebar">
                <div style="padding: 0 8px 16px 8px; border-bottom: 1.5px solid var(--c-separator); margin-bottom: 16px;">
                    <button id="auto-evt-button-14" type="button"  style="width: 100%; background: var(--c-fill); border: 0.5px solid var(--c-separator); color: var(--c-blue); font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; border-radius: var(--r-md); transition: 0.2s;">
                        <i class="ph ph-arrow-left" style="font-weight: bold;"></i> Back to Route List
                    </button>
                </div>
                <div class="workflow-sidebar-steps">
                    <div class="sidebar-step-item active" id="sb-step-1" >
                        <div class="step-dot">1</div>
                        <div class="step-info">
                            <span class="step-title">Route Details</span>
                            <span class="step-desc">Representative & Odo</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-3" >
                        <div class="step-dot">2</div>
                        <div class="step-info">
                            <span class="step-title">Bill Adjustments</span>
                            <span class="step-desc">Attach/detach SOs</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-4" >
                        <div class="step-dot">3</div>
                        <div class="step-info">
                            <span class="step-title">Loading Checklist</span>
                            <span class="step-desc">Verify loaded stock</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-5" >
                        <div class="step-dot">4</div>
                        <div class="step-info">
                            <span class="step-title">Variance Audit</span>
                            <span class="step-desc">Confirm product variances</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-6" >
                        <div class="step-dot">5</div>
                        <div class="step-info">
                            <span class="step-title">Delivery Arrange</span>
                            <span class="step-desc">Assign driver & vehicle</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-8" >
                        <div class="step-dot">6</div>
                        <div class="step-info">
                            <span class="step-title">Delivery Execution</span>
                            <span class="step-desc">Track live status</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-12" >
                        <div class="step-dot">7</div>
                        <div class="step-info">
                            <span class="step-title">Expenses</span>
                            <span class="step-desc">Record route expenses</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-7" >
                        <div class="step-dot">8</div>
                        <div class="step-info">
                            <span class="step-title">Reconciliation</span>
                            <span class="step-desc">Discrepancies & cash</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-9" >
                        <div class="step-dot">9</div>
                        <div class="step-info">
                            <span class="step-title">Return Stock</span>
                            <span class="step-desc">Verify returned items</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-10" >
                        <div class="step-dot">10</div>
                        <div class="step-info">
                            <span class="step-title">Payments</span>
                            <span class="step-desc">Verify & approve payments</span>
                        </div>
                    </div>
                    <div class="sidebar-step-item" id="sb-step-11" >
                        <div class="step-dot">11</div>
                        <div class="step-info">
                            <span class="step-title">Finalize</span>
                            <span class="step-desc">Settle & Close Route</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Content Area (previously #workspaceBody) -->
            <div style="flex:1; overflow-y:auto; position:relative; background:var(--c-bg);" id="workspaceBody">
                <!-- Loading Indicator -->
                <div id="midLoader" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; font-weight:bold; color:var(--c-blue); z-index: 10;">
                    Loading Workspace Information... <i class="fa-solid fa-spinner fa-spin"></i>
                </div>

                <!-- Dynamic Stage Containers -->
                <div id="stageContentWrapper" style="display:none; padding: 16px 20px;">
                

                <!-- COMPLETED ARCHIVE OPTIONS (READ ONLY AT THE TOP IF FINALIZED) -->
                <div id="completedArchiveBanner" style="display:none; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px; padding:15px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h4 style="margin:0; font-size:14px; font-weight:bold; color:#2e7d32;"><i class="ph ph-flag-checkered"></i> Route Settle Balancing Finalized</h4>
                        <p style="margin:5px 0 0 0; font-size:12px; color:#666;">This route is read-only. All transactions, inventories, and GL postings are successfully finalized.</p>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button id="auto-evt-button-15"  style="padding:8px 12px; background:#0066cc; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-file-text"></i> View Route Summary</button>
                        <button id="auto-evt-button-16"  style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-chart-bar"></i> Print Spreadsheet</button>
                        <button id="auto-evt-button-17"  style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-truck"></i> Print Loading Summary</button>
                        <button id="auto-evt-button-18"  style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-download"></i> Export CSV</button>
                    </div>
                </div>

                <!-- TAB 1: DETAILS -->
                <div class="workspace-tab-panel" id="tabpanel-1" style="display:none;">
                    <!-- Bound Route Summary Card -->
                    <div id="boundRouteSummaryContainer" style="display:none; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; background: #eff6ff; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <div>
                                <h4 style="margin: 0; font-size: 15px; font-weight: bold; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                                    <i class="ph ph-link"></i> Bound Route Summary Information
                                </h4>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #1e3a8a;">This is a merged route containing multiple bound routes. System actions apply to all constituent routes.</p>
                            </div>
                            <button type="button" onclick="unbindCombinedRoute()" style="padding: 8px 14px; background: #dc2626; color: #fff; border: none; border-radius: var(--r-sm); font-weight: 700; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; box-shadow: var(--shadow-xs);">
                                <i class="ph ph-link-break"></i> Undo Route Binding
                            </button>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; background: #fff; padding: 15px; border-radius: 6px; border: 0.5px solid #dbeafe;">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Constituent Routes</span>
                                <strong id="brsConstituentsList" style="font-size: 13px; color: #1e293b; margin-top: 2px;">-</strong>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Customers</span>
                                <strong id="brsTotalCustomers" style="font-size: 13px; color: #1e293b; margin-top: 2px;">-</strong>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Invoices</span>
                                <strong id="brsTotalInvoices" style="font-size: 13px; color: #1e293b; margin-top: 2px;">-</strong>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Merged Value</span>
                                <strong id="brsTotalValue" style="font-size: 13px; color: #10b981; margin-top: 2px;">-</strong>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Items Breakdown</span>
                                <strong id="brsTotalProducts" style="font-size: 13px; color: #1e293b; margin-top: 2px;">-</strong>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div style="border:1px solid #cbd5e1; border-radius:8px; padding:20px; background:#fff;">
                            <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-clipboard-text"></i> Route & Representative Info</h4>
                            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Route Code</td><td style="padding:10px 0; font-weight:bold; font-family:var(--f-mono);" id="tab1RouteNumber">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Route Name</td><td style="padding:10px 0; font-weight:bold;" id="tab1RouteName">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Representative</td><td style="padding:10px 0; font-weight:bold;" id="tab1RepName">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Current Status</td><td style="padding:10px 0;"><span id="tab1Status" style="font-weight:bold; background:#e2e8f0; padding:2px 8px; border-radius:4px; font-size:11px;">-</span></td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Start Time</td><td style="padding:10px 0;" id="tab1StartTime">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">End Time</td><td style="padding:10px 0;" id="tab1EndTime">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Total Sales Value</td><td style="padding:10px 0; font-weight:bold; color:var(--c-green);" id="tab1SalesValue">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Total Bills Count</td><td style="padding:10px 0; font-weight:bold;" id="tab1BillsCount">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Unproductive Visits</td><td style="padding:10px 0; font-weight:bold; color:#ea580c;" id="tab1UnproductiveCount">0</td></tr>
                            </table>
                            <div style="display: flex; gap: 8px; margin-top: 15px;">
                                <button id="auto-evt-button-19" type="button"  style="flex:1; justify-content:center; display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border:0.5px solid var(--c-blue-mid); background:var(--c-surface); color:var(--c-blue); border-radius:var(--r-sm); font-weight:600; cursor:pointer; font-size:12px; box-shadow:var(--shadow-xs);">
                                    <i class="ph ph-swap"></i> Switch Route
                                </button>
                                <button type="button" id="btnViewMapDetails"  style="flex:1; justify-content:center; display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border:none; background:var(--c-orange); color:#fff; border-radius:var(--r-sm); font-weight:600; cursor:pointer; font-size:12px; box-shadow:var(--shadow-xs);">
                                    <i class="ph ph-map-pin"></i> View Map
                                </button>
                                <button type="button" id="btnUnbindRouteTab1" onclick="unbindCombinedRoute()" style="flex:1; justify-content:center; display:none; align-items:center; gap:6px; padding:8px 12px; border:none; background:#dc2626; color:#fff; border-radius:var(--r-sm); font-weight:600; cursor:pointer; font-size:12px; box-shadow:var(--shadow-xs);">
                                    <i class="ph ph-link-break"></i> Undo Bind
                                </button>
                                <button id="auto-evt-button-20" type="button"  style="flex:1; justify-content:center; display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border:none; background:#dc2626; color:#fff; border-radius:var(--r-sm); font-weight:600; cursor:pointer; font-size:12px; box-shadow:var(--shadow-xs);">
                                    <i class="ph ph-trash"></i> Delete Route
                                </button>
                            </div>
                        </div>
                        <div style="border:1px solid #cbd5e1; border-radius:8px; padding:20px; background:#fff;">
                            <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-gauge"></i> Odometer Readings</h4>
                            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:15px;">
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Start ODO</td><td style="padding:10px 0; font-weight:bold;" id="tab1StartMeter">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">End ODO</td><td style="padding:10px 0; font-weight:bold;" id="tab1EndMeter">-</td></tr>
                                <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Total Distance</td><td style="padding:10px 0; font-weight:bold; color:#0f172a;" id="tab1Distance">-</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Unproductive Sales Log -->
                    <div style="border:1px solid #cbd5e1; border-radius:8px; padding:20px; background:#fff; margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; color:var(--primary); font-size:15px; font-weight:bold; display:flex; align-items:center; gap:8px;">
                                <i class="ph ph-x-circle" style="color: #ea580c;"></i> Unproductive Sales & Visits Log
                            </h4>
                            <span id="tab1UnproductiveBadge" style="background:#fff7ed; color:#c2410c; border:1px solid #ffedd5; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:bold;">0 Visits</span>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="data-table" style="margin-top:0; width:100%; font-size:12px;">
                                <thead>
                                    <tr>
                                        <th>Visit Time</th>
                                        <th>Customer</th>
                                        <th>Reason</th>
                                        <th>GPS Location</th>
                                        <th style="text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tab1UnproductiveTbody">
                                    <tr>
                                        <td colspan="5" style="text-align:center; color:#888; padding:15px;">No unproductive visits recorded for this route.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="border:1px solid #cbd5e1; border-radius:8px; padding:20px; background:#fff; margin-bottom:20px;">
                        <h4 style="margin:0 0 10px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-note-pencil"></i> Route General Notes</h4>
                        <textarea id="tab1RouteNotes" style="width:100%; height:100px; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; resize:vertical;" placeholder="Write any remarks or observations regarding this route..."></textarea>
                        <div style="text-align:right; margin-top:10px;">
                            <button id="btnSaveRouteNotes"  style="padding:8px 16px; background:#3f51b5; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-floppy-disk"></i> Save Route Notes</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: CREDIT COLLECTIONS -->

                <!-- TAB 3: ADJUSTMENTS -->
                <div class="workspace-tab-panel" id="tabpanel-3" style="display:none; margin: -16px -20px; height: calc(100% + 32px); background: var(--c-surface);">
                    <div style="border-bottom: 1.5px solid var(--c-separator); padding: 16px 20px; background: var(--c-surface); display:flex; justify-content:space-between; align-items:center;">
                        <h4 style="margin:0; font-size:14px; font-weight:bold; display:flex; align-items:center; gap:6px; color: var(--t-primary);"><i class="ph ph-wrench"></i> Sales Order Operations</h4>
                        <div style="display:flex; gap:10px;">
                            <button id="btnTab3CreateSO"  style="padding:8px 16px; background:#0066cc; border:none; color:#fff; border-radius:var(--r-sm); font-weight:bold; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-plus-circle"></i> Create Sales Order</button>
                            <button id="btnTab3AttachSO"  style="padding:8px 16px; background:#5c6bc0; border:none; color:#fff; border-radius:var(--r-sm); font-weight:bold; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-link"></i> Attach Sales Order</button>
                            <button id="btnTab3PrintInvoices"  style="padding:8px 16px; background:#1e293b; border:none; color:#fff; border-radius:var(--r-sm); font-weight:bold; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-printer"></i> Print All Invoices</button>
                            <button id="btnTab3PrintSummary"  style="padding:8px 16px; background:#0f766e; border:none; color:#fff; border-radius:var(--r-sm); font-weight:bold; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"><i class="ph ph-printer"></i> Print Invoice Summary</button>
                        </div>
                    </div>
                    <div style="padding: 16px 20px; background: var(--c-surface);">
                        <table class="data-table" style="margin-top:0;">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Time</th>
                                    <th>Customer Name</th>
                                    <th style="text-align:right;">Grand Total (Rs)</th>
                                    <th style="text-align:center; width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="adjustmentsInvoicesTbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: LOADING -->
                <div class="workspace-tab-panel" id="tabpanel-4" style="display:none;">
                    <div id="loadingBox" style="margin-bottom:20px;"></div>
                </div>

                <!-- TAB 5: VARIANCE -->
                <div class="workspace-tab-panel" id="tabpanel-5" style="display:none;">
                    <div id="varianceAuditBox" style="margin-bottom:20px;"></div>
                </div>

                <!-- TAB 6: DISPATCH -->
                <div class="workspace-tab-panel" id="tabpanel-6" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Logistics Binding & Dispatch</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Assign driver, vehicle, helper and select outstanding credit bills to dispatch with this delivery manifest.</p>
                        </div>
                        <div>
                            <button id="auto-evt-button-21" type="button"  class="macos-btn-secondary">
                                <i class="ph ph-printer"></i> Print Loading Summary
                            </button>
                        </div>
                    </div>
                    
                    <!-- Global Status Banner -->
                    <div id="adjDeliveryStatusBanner" class="macos-banner" style="display:none;">
                        <i class="ph-bold ph-paperclip" style="font-size: 16px;"></i>
                        <span>Delivery Manifest <strong id="adjDeliveryStatusId">#--</strong> successfully generated. Ready for warehouse.</span>
                    </div>

                    <!-- Form View (Always Visible macOS Split Layout) -->
                    <div id="adjDeliveryFormView">
                        <form id="adjDeliveryArrangeForm" style="display: flex; flex-wrap: wrap; gap: 20px; max-width: 1150px; margin: 0 auto; align-items: stretch;">
                            
                            <!-- Left Card: Delivery Details -->
                            <div class="macos-window" style="flex: 1 1 380px; margin: 0; display: flex; flex-direction: column;">
                                <div class="macos-titlebar">
                                    <div class="macos-dots">
                                        <span class="macos-dot close"></span>
                                        <span class="macos-dot minimize"></span>
                                        <span class="macos-dot zoom"></span>
                                    </div>
                                    <span class="macos-title">Delivery Details</span>
                                </div>
                                <div class="macos-content" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div style="display: flex; flex-direction: column; gap: 15px;">
                                        <div>
                                            <label class="macos-label">Delivery Date</label>
                                            <input type="date" id="adjDaDate" class="macos-input" value="<?= date('Y-m-d') ?>">
                                        </div>

                                        <div>
                                            <label class="macos-label">Vehicle Number *</label>
                                            <select id="adjDaVehicle" class="macos-select" required>
                                                <option value="">-- Select Vehicle --</option>
                                                <?php foreach($data['vehicles'] as $v): ?>
                                                    <?php if($v->status === 'Active'): ?>
                                                        <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="macos-label">Driver Name *</label>
                                            <select id="adjDaDriver" class="macos-select" required>
                                                <option value="">-- Select Driver --</option>
                                                <?php foreach($data['drivers'] as $d): ?>
                                                    <option value="<?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?>"><?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="macos-label">Partner / Helper</label>
                                            <select id="adjDaPartner" class="macos-select">
                                                <option value="">-- None --</option>
                                                <?php foreach($data['drivers'] as $d): ?>
                                                    <option value="<?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?>"><?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div style="text-align: right; margin-top: 20px;">
                                        <button id="auto-evt-button-22" type="button"  class="macos-btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
                                            <i class="ph ph-truck"></i> Save Delivery Arrangement
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Card: Credit Bills Dispatch -->
                            <div class="macos-window" style="flex: 1.2 1 480px; margin: 0; display: flex; flex-direction: column;">
                                <div class="macos-titlebar">
                                    <div class="macos-dots">
                                        <span class="macos-dot close"></span>
                                        <span class="macos-dot minimize"></span>
                                        <span class="macos-dot zoom"></span>
                                    </div>
                                    <span class="macos-title">Credit Bills Selection</span>
                                </div>
                                <div class="macos-content" style="flex: 1; display: flex; flex-direction: column; gap: 10px;">
                                    <label class="macos-label" style="margin-bottom: 0;">Select Territory Credit Invoices to dispatch with this vehicle</label>
                                    
                                    <!-- Search & Route Filter controls -->
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 5px;">
                                        <input type="text" id="creditBillsSearch" placeholder="Search by customer or invoice..." style="flex: 1.5; padding: 6px 10px; border: 1px solid var(--c-separator); border-radius: var(--r-sm); background: var(--c-bg); color: var(--t-primary); font-size: 12px; outline: none;" >
                                        <select id="creditBillsRouteFilter" style="flex: 1; padding: 6px 10px; border: 1px solid var(--c-separator); border-radius: var(--r-sm); background: var(--c-bg); color: var(--t-primary); font-size: 12px; outline: none;" >
                                            <option value="all">All Routes</option>
                                            <option value="none">No Route / Unassigned</option>
                                        </select>
                                    </div>

                                    <div id="adjDaBillsContainer" class="macos-checkbox-list" style="flex: 1; min-height: 280px; max-height: none;">
                                        <!-- Outstanding credit bills list -->
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- TAB 8: DELIVERY (LIVE MONITORING) -->
                <div class="workspace-tab-panel" id="tabpanel-8" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Delivery Live Execution Status</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Track live progress of customer dispatches and collections on the route.</p>
                        </div>
                    </div>
                    <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); margin-bottom:20px;">
                        <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-chart-bar"></i> Delivery Performance Summary</h4>
                        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:15px;" id="deliveryTabSummaryCards">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                    <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm);">
                        <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-map-pin"></i> Customer Visit & Dispatch Status</h4>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Invoice Number</th>
                                    <th style="text-align:right;">Grand Total (Rs)</th>
                                    <th style="text-align:center;">Delivery Status</th>
                                    <th style="text-align:center;">Payment Status</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="deliveryTabInvoicesTbody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 12: EXPENSES (NEW STAGE) -->
                <div class="workspace-tab-panel" id="tabpanel-12" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Route Expenses Management</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Record and manage operational expenses for Representatives and Delivery operations.</p>
                        </div>
                    </div>

                    <!-- Inner Sub-Tabs Header -->
                    <div style="display:flex; gap:10px; border-bottom:1px solid #cbd5e1; margin-bottom:20px;">
                        <button type="button" class="left-tab-btn active" id="btnExpenseSubTabRep" onclick="switchExpenseSubTab('Rep')">
                            <i class="ph ph-user-gear"></i> Rep Expenses
                        </button>
                        <button type="button" class="left-tab-btn" id="btnExpenseSubTabDelivery" onclick="switchExpenseSubTab('Delivery')">
                            <i class="ph ph-truck"></i> Delivery Expenses
                        </button>
                    </div>

                    <div style="display:grid; grid-template-columns:1.1fr 0.9fr; gap:20px;">
                        <!-- Left: Form Container -->
                        <div>
                            <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); margin-bottom:20px;">
                                <form id="formRecordRouteExpense" onsubmit="submitRouteExpense(event)">
                                    <input type="hidden" id="expCategory" value="Rep">

                                    <!-- Rep Expenses Header -->
                                    <div id="subtabRepHeader">
                                        <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;">
                                            <i class="ph ph-user-gear"></i> Record Representative Expense
                                        </h4>
                                    </div>

                                    <!-- Delivery Expenses Header -->
                                    <div id="subtabDeliveryHeader" style="display:none;">
                                        <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;">
                                            <i class="ph ph-truck"></i> Record Delivery Manifest Expense
                                        </h4>
                                    </div>

                                    <!-- Rep Expenses Fields -->
                                    <div id="fieldsRepExpenses">
                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Related Route / Trip</label>
                                                <input type="text" id="expRouteNumber" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-family:monospace; font-weight:bold;">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Assigned Vehicle <span style="color:red;">*</span></label>
                                                <select id="expVehicleNumber" style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); font-weight:bold; outline:none;">
                                                    <option value="">-- Select Vehicle --</option>
                                                    <?php if (!empty($data['vehicles'])): ?>
                                                        <?php foreach($data['vehicles'] as $v): ?>
                                                            <?php if($v->status === 'Active'): ?>
                                                                <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Representative</label>
                                                <input type="text" id="expRepName" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-weight:500;">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Date & Time</label>
                                                <input type="text" id="expDateTime" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-weight:500;">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delivery Expenses Fields -->
                                    <div id="fieldsDeliveryExpenses" style="display:none;">
                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Delivery Manifest</label>
                                                <input type="text" id="expDelManifest" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-family:monospace; font-weight:bold;">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Arranged Delivery Vehicle</label>
                                                <input type="text" id="expDelVehicle" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-weight:bold;">
                                            </div>
                                        </div>

                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Driver & Partner Info</label>
                                                <input type="text" id="expDelDriverInfo" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-weight:500;">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Date & Time</label>
                                                <input type="text" id="expDelDateTime" readonly style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); background:#f8fafc; font-weight:500;">
                                            </div>
                                        </div>
                                    </div>

                                    <div style="border-top: 1px solid #e2e8f0; margin: 15px 0; padding-top: 15px;"></div>

                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                        <div>
                                            <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Expense Type <span style="color:red;">*</span></label>
                                            <select id="expType" required style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); outline:none;">
                                                <option value="" disabled selected>Select Type</option>
                                                <option value="Fuel">Fuel</option>
                                                <option value="Meals">Meals</option>
                                                <option value="Accommodation">Accommodation</option>
                                                <option value="Parking">Parking</option>
                                                <option value="Vehicle Maintenance">Vehicle Maintenance</option>
                                                <option value="Toll Charges">Toll Charges</option>
                                                <option value="Other Expenses">Other Expenses</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Payment Source <span style="color:red;">*</span></label>
                                            <select id="expSource" required onchange="onExpenseSourceChanged()" style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); outline:none;">
                                                <option value="" disabled selected>Select Source</option>
                                                <option value="Petty Cash">Petty Cash</option>
                                                <option value="Collected Cash">Collected Cash on Route</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                        <div>
                                            <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Amount (Rs.) <span style="color:red;">*</span></label>
                                            <input type="number" step="0.01" min="0.01" id="expAmount" required style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); font-weight:bold; font-family:monospace;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Receipt / Voucher No</label>
                                            <input type="text" id="expReceiptNumber" style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs);">
                                        </div>
                                    </div>

                                    <div style="margin-bottom: 20px;">
                                        <label style="display:block; font-size:11px; font-weight:700; color:var(--t-label); text-transform:uppercase; margin-bottom:5px;">Description <span style="color:red;">*</span></label>
                                        <textarea id="expDescription" required rows="2" style="width:100%; padding:8px 12px; border:1px solid var(--c-separator); border-radius:var(--r-xs); outline:none; resize:none;"></textarea>
                                    </div>

                                    <button type="submit" id="btnSubmitRouteExpense" style="width:100%; background:var(--primary); color:#fff; border:none; padding:10px; border-radius:var(--r-md); font-weight:bold; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                                        <i class="ph ph-floppy-disk"></i> Record Expense
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Right: Balances and Live List -->
                        <div>
                            <!-- Available Balances Card -->
                            <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); margin-bottom:20px;">
                                <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-bank"></i> Funding Source Balances</h4>
                                <div style="display:flex; flex-direction:column; gap:12px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:8px;">
                                        <div>
                                            <span style="font-weight:600; font-size:13px; color:#334155;">Available Petty Cash Balance</span>
                                            <p style="margin:2px 0 0 0; font-size:11px; color:#64748b;">Current system petty cash ledger</p>
                                        </div>
                                        <span id="lblAvailPettyCash" style="font-family:monospace; font-weight:bold; font-size:14px; color:#2e7d32;">Rs 0.00</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:4px;">
                                        <div>
                                            <span style="font-weight:600; font-size:13px; color:#334155;">Available Route Cash Collections</span>
                                            <p style="margin:2px 0 0 0; font-size:11px; color:#64748b;">Collected route payments minus expenses</p>
                                        </div>
                                        <span id="lblAvailRouteCash" style="font-family:monospace; font-weight:bold; font-size:14px; color:#2e7d32;">Rs 0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Recorded Expenses list -->
                            <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); height: 320px; display: flex; flex-direction: column;">
                                <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-list-bullets"></i> Recorded Expenses</h4>
                                <div style="flex:1; overflow-y:auto; font-size:12px;" id="listRecordedExpenses">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: RECONCILIATION -->
                <div class="workspace-tab-panel" id="tabpanel-7" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Route Collections & Variance Reconciliation</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Count cash, verify cheques and document financial variances. Save draft or submit for final settlement.</p>
                        </div>
                    </div>
                    
                    <div id="tab7ContentContainer">
                        <div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:20px;">
                            <div>
                                <!-- Cash Reconciliation Card -->
                                <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); margin-bottom:20px;">
                                    <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-coins"></i> Cash Collections Counter</h4>
                                    <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:15px;">
                                        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Expected Cash Sales</td><td style="padding:10px 0; font-weight:bold; font-family:monospace; text-align:right;" id="reconExpectedCash">Rs 0.00</td></tr>
                                        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Expected Cash Collections</td><td style="padding:10px 0; font-weight:bold; font-family:monospace; text-align:right;" id="reconExpectedCollections">Rs 0.00</td></tr>
                                        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Total Collected Cash</td><td style="padding:10px 0; font-weight:bold; font-family:monospace; text-align:right;" id="reconTotalExpectedCash">Rs 0.00</td></tr>
                                        <tr style="border-bottom:1px solid #f1f5f9; color:#ef4444;"><td style="padding:10px 0; font-weight:bold;">Less: Route Expenses Paid from Cash</td><td style="padding:10px 0; font-weight:bold; font-family:monospace; text-align:right;" id="reconRouteExpenses">Rs 0.00</td></tr>
                                        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#2e7d32; font-weight:bold;">Net Expected Cash Handover</td><td style="padding:10px 0; font-weight:bold; font-family:monospace; text-align:right; color:#2e7d32;" id="reconNetExpectedCash">Rs 0.00</td></tr>
                                        <tr style="border-bottom:1px solid #f1f5f9;"><td style="padding:10px 0; color:#64748b; font-weight:bold;">Actual Counted Cash</td><td style="padding:10px 0; text-align:right;"><input type="number" step="0.01" min="0" id="reconActualCash" readonly style="padding:6px; border:1px solid #ccc; border-radius:4px; width:150px; text-align:right; font-weight:bold; font-family:monospace; background:#f8fafc;" value="0.00"></td></tr>
                                        <tr><td style="padding:10px 0; color:#64748b; font-weight:bold;">Cash Variance</td><td style="padding:10px 0; font-weight:bold; font-family:monospace; text-align:right;" id="reconCashVariance">Rs 0.00</td></tr>
                                    </table>
                                    
                                    <div style="margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                                        <h5 style="margin: 0 0 10px 0; color:#334155; font-size:13px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">Denomination Breakdown</h5>
                                        <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                            <thead>
                                                <tr style="border-bottom: 2px solid #cbd5e1; text-align:left; color:#475569;">
                                                    <th style="padding:6px 4px;">Denom</th>
                                                    <th style="padding:6px 4px; text-align:center;">Collector Qty</th>
                                                    <th style="padding:6px 4px; text-align:right;">Collector Value</th>
                                                    <th style="padding:6px 4px; text-align:center; width: 90px;">Actual Qty</th>
                                                    <th style="padding:6px 4px; text-align:right;">Actual Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Denominations Row 5000 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 5,000</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty5000">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal5000">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty5000"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal5000">0.00</td>
                                                </tr>
                                                <!-- Denominations Row 2000 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 2,000</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty2000">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal2000">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty2000"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal2000">0.00</td>
                                                </tr>
                                                <!-- Denominations Row 1000 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 1,000</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty1000">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal1000">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty1000"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal1000">0.00</td>
                                                </tr>
                                                <!-- Denominations Row 500 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 500</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty500">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal500">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty500"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal500">0.00</td>
                                                </tr>
                                                <!-- Denominations Row 100 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 100</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty100">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal100">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty100"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal100">0.00</td>
                                                </tr>
                                                <!-- Denominations Row 50 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 50</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty50">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal50">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty50"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal50">0.00</td>
                                                </tr>
                                                <!-- Denominations Row 20 -->
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding:6px 4px; font-weight:500;">Rs 20</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#64748b;" id="colQty20">0</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colVal20">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" min="0" id="actQty20"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actVal20">0.00</td>
                                                </tr>
                                                <!-- Coins Row -->
                                                <tr>
                                                    <td style="padding:6px 4px; font-weight:500;">Coins Total</td>
                                                    <td style="padding:6px 4px; text-align:center; color:#888;">-</td>
                                                    <td style="padding:6px 4px; text-align:right; color:#64748b; font-family:monospace;" id="colValCoins">0.00</td>
                                                    <td style="padding:6px 4px; text-align:center;"><input type="number" step="0.01" min="0" id="actValCoins"  style="width:70px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;"></td>
                                                    <td style="padding:6px 4px; text-align:right; font-weight:bold; font-family:monospace;" id="actValCoinsTotal">0.00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Cheque Reconciliation Card -->
                                <div style="border:1px solid #cbd5e1; border-radius:8px; padding:20px; background:#fff;">
                                    <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-bank"></i> Cheques Verification</h4>
                                    <table class="data-table" style="font-size:11px;">
                                        <thead>
                                            <tr>
                                                <th>Customer Name</th>
                                                <th>Cheque Number</th>
                                                <th style="text-align:right;">Amount (Rs)</th>
                                                <th style="text-align:center;">Approve</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reconChequesTbody">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <!-- Notes & Save Draft Card -->
                                <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); height:100%; display:flex; flex-direction:column; justify-content:space-between;">
                                    <div>
                                        <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-note"></i> Audit Remarks</h4>
                                        <textarea id="reconAuditNotes" style="width:100%; height:180px; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; resize:none;" placeholder="Write any audit notes regarding cash discrepancy, bank transfer receipts verified, etc..."></textarea>
                                    </div>
                                    <div style="text-align:right; margin-top:20px;">
                                        <button id="btnSaveReconciliationDraft"  style="padding:10px 20px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer; width:100%;"><i class="ph ph-floppy-disk"></i> Save Reconciliation Draft</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab7GuardContainer" style="display:none;"></div>
                </div>

                <!-- TAB 9: RETURN STOCK VERIFICATION -->
                <div class="workspace-tab-panel" id="tabpanel-9" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Return Stock Verification</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Verify returned physical stocks and confirm route inventory updates.</p>
                        </div>
                        <div>
                            <button id="btnPrintReturnStock" style="padding:10px 16px; background:#475569; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px;">
                                🖨️ Print Return Stock Report
                            </button>
                        </div>
                    </div>
                    <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; color:var(--primary); font-size:15px; font-weight:bold;">Returned Stock Settle Verification</h4>
                            <label style="font-weight:bold; font-size:12px; display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="checkbox" id="settleVerifyStock"  style="width:16px; height:16px;">
                                I have physically verified all returned inventory and confirm quantities are correct.
                            </label>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th style="text-align:center;">Loaded</th>
                                    <th style="text-align:center;">Delivered</th>
                                    <th style="text-align:center;">Expected Returned</th>
                                    <th style="text-align:right; width:150px;">Actual Counted Returns</th>
                                </tr>
                            </thead>
                            <tbody id="settleStockTableBody">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                        <div style="text-align:right; margin-top:20px;">
                            <button id="btnSaveReturnStockDraft"  style="padding:10px 20px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">💾 Save Return Stock Draft</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 10: PAYMENTS -->
                <div class="workspace-tab-panel" id="tabpanel-10" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Payments & Settlements</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Verify collected payments and map route transactions to general ledger postings.</p>
                        </div>
                    </div>

                    <!-- Dispatch Assignment Section inside Accounting final tab (Hidden to streamline interface) -->
                    <div style="display:none;">
                        <select id="settleDaVehicle">
                            <option value="">-- Select Vehicle --</option>
                            <?php foreach($data['vehicles'] as $v): ?>
                                <?php if($v->status === 'Active'): ?>
                                    <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <select id="settleDaDriver">
                            <option value="">-- Select Driver --</option>
                            <?php foreach($data['drivers'] as $d): ?>
                                <option value="<?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?>"><?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="settleDaPartner">
                            <option value="">-- None --</option>
                            <?php foreach($data['drivers'] as $d): ?>
                                <option value="<?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?>"><?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="tab10ContentContainer">
                        <!-- General Ledger account double entry mappings card -->
                        <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); margin-bottom:20px;">
                            <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-briefcase"></i> Account Mappings</h4>
                            <div style="display: flex; gap: 10px; border-bottom: 1px solid #eee; margin-bottom: 15px;">
                                <button type="button" class="left-tab-btn active" id="settleDeTabCollectionsBtn" ><i class="ph ph-coins"></i> Cash/Cheques Posting</button>
                                <button type="button" class="left-tab-btn" id="settleDeTabSalesBtn" ><i class="ph ph-file-text"></i> Invoices Sales Posting</button>
                            </div>
                            <div id="settleDeCollectionsContainer"></div>
                            <div id="settleDeSalesContainer" style="display:none;"></div>
                            <div style="text-align:right; margin-top:20px;">
                                <button id="btnSaveAccountingDraft"  style="padding:10px 20px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;"><i class="ph ph-floppy-disk"></i> Save Account Mappings Draft</button>
                            </div>
                        </div>

                    </div>
                    <div id="tab10GuardContainer" style="display:none;"></div>
                </div>

                <!-- TAB 11: FINALIZE ROUTE -->
                <div class="workspace-tab-panel" id="tabpanel-11" style="display:none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--t-primary);">Finalize Route</h3>
                            <p style="margin:4px 0 0 0; font-size:13px; color:var(--t-secondary);">Review the route summary and submit final settlement.</p>
                        </div>
                    </div>
                    
                    <div id="tab11ContentContainer">
                        <!-- Summary Cards Grid -->
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                            <!-- Card 1: Sales & Collections Summary -->
                            <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm);">
                                <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-chart-bar"></i> Sales & Collections Summary</h4>
                                
                                <div style="margin-bottom: 15px; background: #f8fafc; padding: 12px; border-radius: 6px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px;">
                                        <span>Cash Sales:</span>
                                        <strong id="sumCashSales" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px;">
                                        <span>Cheque Sales:</span>
                                        <strong id="sumChequeSales" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px;">
                                        <span>Bank Transfer Sales:</span>
                                        <strong id="sumBankSales" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px;">
                                        <span>Credit Sales:</span>
                                        <strong id="sumCreditSales" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px; color:#ef4444;">
                                        <span>Route Expenses Incurred:</span>
                                        <strong id="sumRouteExpenses" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-weight:bold; border-top: 1px dashed #cbd5e1; padding-top: 6px; margin-top: 6px; font-size: 13px;">
                                        <span>Total Route Sales:</span>
                                        <strong id="sumTotalSales" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                </div>

                                <div style="background: #f0fdf4; padding: 12px; border-radius: 6px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px; color: #166534;">
                                        <span>Expected Cash Collections (Driver):</span>
                                        <strong id="sumExpectedCash" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px; font-size: 13px; color: #166534;">
                                        <span>Actual Cash Entered:</span>
                                        <strong id="sumActualCash" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-weight:bold; border-top: 1px dashed #bbf7d0; padding-top: 6px; margin-top: 6px; font-size: 13px; color: #166534;">
                                        <span>Cash Count Variance:</span>
                                        <strong id="sumCashVariance" style="font-family:monospace;">Rs 0.00</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Vehicle & Route Info -->
                            <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm);">
                                <h4 style="margin:0 0 15px 0; color:var(--primary); font-size:15px; font-weight:bold;"><i class="ph ph-info"></i> Route & Vehicle Info</h4>
                                
                                <div style="margin-bottom: 15px; font-size: 13px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                        <span style="color:var(--t-secondary);">Route:</span>
                                        <strong id="sumRouteName">--</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                        <span style="color:var(--t-secondary);">Representative:</span>
                                        <strong id="sumRepName">--</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                        <span style="color:var(--t-secondary);">Vehicle Number:</span>
                                        <strong id="sumVehicleNumber">--</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                        <span style="color:var(--t-secondary);">Driver Name:</span>
                                        <strong id="sumDriverName">--</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between;">
                                        <span style="color:var(--t-secondary);">Partner/Helper:</span>
                                        <strong id="sumPartnerName">--</strong>
                                    </div>
                                </div>

                                <div style="background: #f8fafc; padding: 12px; border-radius: 6px; font-size: 13px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px;">
                                        <span>Start Meter:</span>
                                        <strong id="sumStartMeter">0 KM</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom: 6px;">
                                        <span>End Meter:</span>
                                        <strong id="sumEndMeter">0 KM</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-weight:bold; border-top: 1px dashed #cbd5e1; padding-top: 6px; margin-top: 6px;">
                                        <span>Distance Traveled:</span>
                                        <strong id="sumDistanceTraveled">0 KM</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Settle Actions -->
                        <div style="border:0.5px solid var(--c-separator); border-radius:var(--r-lg); padding:20px; background:var(--c-surface); box-shadow:var(--shadow-sm); display:flex; justify-content:space-between; align-items:center;">
                            <div id="settleStatusText" style="font-size:12px; color:#c62828; font-weight:bold;">
                                Please verify Cash, Cheques, and Return stock counts under Reconciliation & Return Stock tabs to unlock Finalization.
                            </div>
                            <button id="settleSubmitBtn"  style="padding:12px 24px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:14px; opacity:0.5; cursor:not-allowed;" disabled>
                                <i class="ph ph-scales"></i> Settle Balancing & Finalize Route
                            </button>
                        </div>
                    </div>
                    
                    <div id="tab11GuardContainer" style="display:none;"></div>
                </div>

                <!-- COMPLETED / READ ONLY VIEW -->
                <div class="stage-section-panel" id="ssec-Completed" style="display:none;">
                    <div style="background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px; padding:15px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h4 style="margin:0; font-size:14px; font-weight:bold; color:#2e7d32;"><i class="ph ph-flag-checkered"></i> Route Settle Balancing Finalized</h4>
                            <p style="margin:5px 0 0 0; font-size:12px; color:#666;">This route is read-only. All transactions, inventories, and GL postings are successfully finalized.</p>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button id="auto-evt-button-23"  style="padding:8px 12px; background:#0066cc; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-file-text"></i> View Route Summary</button>
                            <button id="auto-evt-button-24"  style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-chart-bar"></i> Print Spreadsheet</button>
                            <button id="auto-evt-button-25"  style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-truck"></i> Print Loading Summary</button>
                            <button id="auto-evt-button-26"  style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;"><i class="ph ph-download"></i> Export CSV</button>
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:10px; border-bottom:1px solid #eee; margin-bottom:15px;">
                        <button class="left-tab-btn active" id="compTabInvoicesBtn" ><i class="ph ph-file-text"></i> Invoices</button>
                        <button class="left-tab-btn" id="compTabCollectionsBtn" ><i class="ph ph-coins"></i> Settled Collections</button>
                        <button class="left-tab-btn" id="compTabVariancesBtn" ><i class="ph ph-scales"></i> Variances</button>
                    </div>

                    <div id="completedInvoicesTab">
                        <table class="data-table">
                            <thead>
                                <tr><th>Invoice Number</th><th>Time</th><th>Customer Name</th><th style="text-align:right;">Grand Total (Rs)</th><th style="text-align:center;">Status</th></tr>
                            </thead>
                            <tbody class="render-invoices-tbody"></tbody>
                        </table>
                    </div>
                    <div id="completedCollectionsTab" style="display:none;"></div>
                    <div id="completedVariancesTab" style="display:none;"></div>
                </div>

                </div> <!-- closes #stageContentWrapper -->
            </div> <!-- closes #workspaceBody -->
        </div> <!-- closes #workspaceLayoutWrapper -->

        <!-- Empty State (when no route selected) -->
        <div class="empty-state" id="midEmptyState" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--t-tertiary);">
            <span style="font-size: 50px; margin-bottom: 15px; opacity: 0.5;"><i class="ph ph-map-pin"></i></span>
            Please select a route from the left to view details.
        </div>
    </div> <!-- closes .pane-middle -->

    <?php include __DIR__ . '/_modals.php'; ?>

<!-- FLOATING COMMAND BAR -->
<div class="cmd-bar">
    <div id="auto-evt-div-27" class="cmd-search" >
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="floatingSearchInput"  placeholder="Search routes...">
    </div>
    <div class="cmd-divider"></div>
    
    <?php if ($isHistory): ?>
        <a href="<?= APP_URL ?>/RepTracking/index" class="cmd-btn" title="Active Routes">
            <i class="ph-bold ph-arrow-left"></i>
            <span>Active Routes</span>
        </a>
    <?php else: ?>
        <a href="<?= APP_URL ?>/RepTracking/history" class="cmd-btn" title="Route History">
            <i class="ph-bold ph-clock-counter-clockwise"></i>
            <span>Route History</span>
        </a>
        <button id="auto-evt-button-28" type="button"  class="cmd-btn" title="Create Route Manually">
            <i class="ph-bold ph-plus-circle"></i>
            <span>Create Route</span>
        </button>
    <?php endif; ?>
    
    <button type="button" id="btnOpenRouteBinding"  class="cmd-btn" title="Route Binding Panel">
        <i class="ph-bold ph-link"></i>
        <span>Binding Panel</span>
    </button>
    <div class="cmd-divider"></div>
    
    <button id="auto-evt-button-29" type="button"  class="cmd-icon" title="Refresh page">
        <i class="ph ph-arrows-clockwise"></i>
    </button>
</div>

<?php include __DIR__ . '/index.js.php'; ?>
