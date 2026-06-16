
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    /* 3-Pane Layout System */
    .app-workspace { display: flex; height: calc(100vh - 80px); background: #f4f5f7; border-radius: 8px; overflow: hidden; border: 1px solid var(--mac-border); position: relative;}
    @media (prefers-color-scheme: dark) { .app-workspace { background: #121212; } }

    /* Left Pane: Route List */
    .pane-left { width: 350px; background: rgba(0,0,0,0.02); border-right: 1px solid var(--mac-border); display: flex; flex-direction: column; z-index: 10;}
    @media (prefers-color-scheme: dark) { .pane-left { background: #1e1e2d; } }
    .route-item { padding: 15px; border-bottom: 1px solid var(--mac-border); cursor: pointer; user-select: none; transition: 0.2s;}
    .route-item:hover { background: rgba(0,102,204,0.05); }
    .route-item.active { background: #0066cc; color: #fff; border-color: #0066cc; }
    .route-item.active .r-sub, .route-item.active .r-meta { color: rgba(255,255,255,0.8); }
    
    .r-title { font-weight: bold; font-size: 14px; margin-bottom: 5px; }
    .r-sub { font-size: 11px; color: #666; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;}
    .r-meta { font-size: 12px; color: #888; display: flex; justify-content: space-between; }
    
    .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px;}
    .status-Active { background: #ef6c00; box-shadow: 0 0 5px #ef6c00;}
    .status-Completed { background: #2e7d32; }

    /* Middle Pane: Unified Wizard Workspace */
    .pane-middle { flex: 1; display: flex; flex-direction: column; background: #fff; position: relative;}
    @media (prefers-color-scheme: dark) { .pane-middle { background: #1a1a2e; } }
    .mid-header { padding: 20px 25px; border-bottom: 1px solid var(--mac-border); background: var(--surface); display: flex; justify-content: space-between; align-items: flex-end;}
    
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { color: #888; font-weight: 600; font-size: 11px; text-transform: uppercase; background: rgba(0,0,0,0.02); position: sticky; top: 0;}
    
    .bill-row { cursor: pointer; transition: 0.1s; user-select: none;}
    .bill-row:hover { background: rgba(0,102,204,0.05); }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); }
    .empty-state span { font-size: 50px; margin-bottom: 15px; opacity: 0.5;}

    /* Right Pane: Slide-out Invoice Mini-View */
    .pane-right-slider { 
        position: absolute; top: 0; right: 0; bottom: 0; width: 50%; 
        background: var(--surface); border-left: 1px solid var(--mac-border); 
        box-shadow: -10px 0 35px rgba(0,0,0,0.15); transform: translateX(100%); 
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 150;
        display: flex; flex-direction: column;
    }
    .pane-right-slider.open { transform: translateX(0); }
    
    .slider-header { padding: 15px 20px; background: #333; color: #fff; display: flex; justify-content: space-between; align-items: center; font-weight: bold;}
    .close-slider { background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer; padding: 0;}
    
    #invoiceIframe { width: 100%; flex: 1; border: none; background: #525659; }

    /* Highlights */
    .stat-box { background: rgba(0,0,0,0.02); border: 1px solid var(--border); padding: 10px 15px; border-radius: 6px; text-align: center;}
    .stat-box span { display: block; font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 3px;}
    .stat-box strong { font-size: 16px; color: var(--text-dark); }

    /* Modal styles */
    .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-panel { background: #fff; width: 100%; max-width: 450px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); border: 1px solid #ccc; overflow: hidden; display: flex; flex-direction: column; }
    .modal-header { padding: 15px 20px; background: #0066cc; color: #fff; font-weight: bold; font-size: 15px; display: flex; justify-content: space-between; align-items: center;}
    .modal-body { padding: 20px; display: flex; flex-direction: column; gap: 15px; }
    .modal-body label { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;}
    .modal-body input { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;}
    .modal-body input:focus { border-color: #0066cc; outline: none; }
    .modal-footer { padding: 15px 20px; background: #f5f5f5; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;}

    /* 8-Stage Wizard Style */
    .workflow-wizard {
        display: flex;
        justify-content: space-between;
        background: var(--surface);
        border-bottom: 1px solid var(--mac-border);
        padding: 10px 15px;
        overflow-x: auto;
        gap: 5px;
        flex-shrink: 0;
    }
    .wizard-step {
        flex: 1;
        text-align: center;
        padding: 8px 6px;
        font-size: 10px;
        font-weight: 700;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.03);
        color: var(--text-muted);
        white-space: nowrap;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    @media (prefers-color-scheme: dark) {
        .wizard-step { background: rgba(255, 255, 255, 0.03); }
    }
    .wizard-step.active {
        background: #0066cc;
        color: #fff;
        border-color: #0066cc;
        box-shadow: 0 2px 5px rgba(0, 102, 204, 0.3);
    }
    .wizard-step.completed {
        background: rgba(46, 125, 50, 0.1);
        color: #2e7d32;
        border-color: rgba(46, 125, 50, 0.2);
    }

    /* GPS path map overlay */
    .map-empty-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.85); color: #666; font-size: 13px; font-weight: 600; text-align: center; padding: 20px; z-index: 500; pointer-events: none; }
    @media (prefers-color-scheme: dark) { .map-empty-overlay { background: rgba(18,18,18,0.9); color: #aaa; } }
    
    .path-step-list { max-height: 120px; overflow-y: auto; padding: 12px 20px; font-size: 11px; color: #555; background: var(--surface); border-top: 1px solid var(--mac-border); flex-shrink: 0; }
    .path-step-list ol { margin: 0; padding-left: 18px; }
    .path-step-list li { margin-bottom: 2px; }
    .path-step-start { color: #2e7d32; font-weight: bold; }
    .path-step-invoice { color: #0066cc; }
    .path-step-end { color: #c62828; font-weight: bold; }

    /* Left side Tabs styling */
    .left-tab-btn {
        flex: 1; padding: 8px 2px; font-size: 11px; font-weight: bold; border-radius: 6px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; white-space: nowrap; transition: 0.2s;
    }
    .left-tab-btn.active {
        background: #0066cc; color: white;
    }

    /* Binding panel */
    .rb-slot-column { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    .rb-slot-box { border: 2px dashed #cbd5e1; border-radius: 6px; padding: 20px; text-align: center; background: #ffffff; cursor: pointer; transition: all 0.2s ease; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80px; }
    .rb-slot-box:hover { border-color: #3f51b5; background: #f5f7ff; }
    .rb-slot-select { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; background: #fff; color: #333; margin-top: 5px; }
    .rb-bill-list { max-height: 180px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; padding: 8px; font-size: 12px; display: none; }
    .rb-bill-item { display: flex; justify-content: space-between; padding: 6px 8px; border-bottom: 1px solid #f1f5f9; align-items: center; }
    .rb-bill-item:last-child { border-bottom: none; }
    .rb-bound-tag { margin-top: 5px; font-size: 11px; background: #e8eaf6; color: #3f51b5; padding: 2px 6px; border-radius: 4px; display: inline-block; font-weight: bold; }
    @media (prefers-color-scheme: dark) {
        .rb-slot-column { background: #1e1e2d; border-color: #2d2d3d; }
        .rb-slot-box { background: #12121a; border-color: #3f3f46; }
        .rb-slot-box:hover { background: #181824; }
        .rb-slot-select { background: #1e1e2d; color: #f1f5f9; border-color: #3f3f46; }
        .rb-bill-list { background: #12121a; border-color: #2d2d3d; }
        .rb-bill-item { border-bottom-color: #1e1e2d; }
        .rb-bound-tag { background: #1c2438 !important; color: #7986cb !important; }
    }

    /* Scrollable Stage Tab styling */
    .scroll-tabs {
        display: flex;
        overflow-x: auto;
        gap: 6px;
        padding: 8px 12px;
        background: #f8fafc;
        border-bottom: 1px solid var(--mac-border);
        flex-shrink: 0;
        scrollbar-width: none; /* Firefox */
    }
    .scroll-tabs::-webkit-scrollbar {
        display: none; /* Safari and Chrome */
    }
    .scroll-tab-btn {
        flex: 0 0 auto;
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }
    .scroll-tab-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
    .scroll-tab-btn.active {
        background: #3f51b5;
        color: #fff;
        border-color: #3f51b5;
        box-shadow: 0 2px 4px rgba(63, 81, 181, 0.2);
    }
    .global-filter-btn {
        padding: 6px 14px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .global-filter-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .global-filter-btn.active {
        background: #3f51b5;
        color: #fff;
        border-color: #3f51b5;
        box-shadow: 0 2px 4px rgba(63, 81, 181, 0.2);
    }
    @media (prefers-color-scheme: dark) {
        .global-status-filter-bar { background: #1a1a2e !important; border-color: #3f3f46 !important; }
        .global-filter-btn { background: #1e1e2d; color: #94a3b8; border-color: #3f3f46; }
        .global-filter-btn:hover { background: #2d2d3d; color: #f1f5f9; }
        .global-filter-btn.active { background: #3f51b5; color: #fff; border-color: #3f51b5; }
    }
</style>

<div class="header-actions" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0; font-weight: 700;">🛡️ Master Route Control Panel</h2>
        <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">Manage route status updates, arrange dispatches, verify picking, and settle General Ledger postings.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button id="btnOpenRouteBinding" onclick="openRouteBindingModal()" style="padding: 10px 18px; border: none; background: #3f51b5; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(63, 81, 181, 0.2); transition: all 0.2s ease;">
            🔗 Route Binding Panel
        </button>
    </div>
</div>

<!-- TOP GLOBAL STATUS FILTER BAR -->
<div class="global-status-filter-bar" style="display: flex; gap: 8px; background: #fff; border: 1px solid var(--mac-border); border-radius: 8px; padding: 10px 15px; margin-bottom: 15px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.05); align-items: center;">
    <span style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #64748b; margin-right: 10px; white-space: nowrap;">⚡ Status Filter:</span>
    <button type="button" class="global-filter-btn active" onclick="filterLeftPane('all', this)">All</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('active', this)">🟢 Active Rep Routes</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('pending_gl', this)">💰 Credit Collections</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('adjustments', this)">⚙️ Adjustments</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('pending_loading', this)">📦 Pending Loading</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('final_loading', this)">🚛 Final Loading</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('variance', this)">⚖️ Variance Audit</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('finalizing', this)">🔒 Finalizing</button>
    <button type="button" class="global-filter-btn" onclick="filterLeftPane('completed', this)">🏁 Completed</button>
</div>

<div class="app-workspace">
    <!-- Left Pane: Routes Master List -->
    <div class="pane-left">

        <div style="flex: 1; overflow-y: auto;" id="routeListItemsContainer">
            <?php foreach($data['routes'] as $route): ?>
                <?php 
                    $status = $route->status;
                    if ($status === 'Active') {
                        $dataType = 'active';
                    } elseif ($status === 'Pending GL') {
                        $dataType = 'pending_gl';
                    } elseif ($status === 'Adjustments') {
                        $dataType = 'adjustments';
                    } elseif ($status === 'Pending Loading') {
                        $dataType = 'pending_loading';
                    } elseif ($status === 'Final Loading') {
                        $dataType = 'final_loading';
                    } elseif ($status === 'Variance Adjustment') {
                        $dataType = 'variance';
                    } elseif ($status === 'Finalizing') {
                        $dataType = 'finalizing';
                    } else {
                        $dataType = 'completed';
                    }
                ?>
                <div class="route-item" id="route_<?= $route->id ?>" data-route-type="<?= $dataType ?>" onclick="loadRouteDetails(<?= $route->id ?>, this)">
                    <div class="r-title"><span class="status-dot status-<?= $route->status === 'Completed' || $route->status === 'Finalized' ? 'Completed' : 'Active' ?>"></span> <?= htmlspecialchars($route->route_name) ?></div>
                    <div class="r-sub">Rep: <?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?></div>
                    <div class="r-meta">
                        <span><?= date('M d, Y', strtotime($route->start_time)) ?></span>
                        <strong style="color: <?= $dataType == 'completed' ? 'inherit' : '#ef6c00' ?>;">Rs: <?= number_format($route->total_sales, 2) ?></strong>
                    </div>
                    <div style="font-size:11px; margin-top:4px; font-weight:bold; color:#0066cc;">Status: <?= htmlspecialchars($route->status) ?></div>
                    <?php if (!empty($route->is_bound_group)): ?>
                        <div class="rb-bound-tag" style="background: #e0f2fe; color: #0369a1; display: block; margin-top: 5px;">
                            🔗 Group: <?= htmlspecialchars($route->constituent_routes_info) ?>
                        </div>
                    <?php elseif (!empty($route->binding_name)): ?>
                        <div class="rb-bound-tag">
                            🔗 Bound: <?= htmlspecialchars($route->binding_name) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Hidden data payload -->
                <div id="route_data_<?= $route->id ?>" style="display:none;" 
                     data-rep="<?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?>"
                     data-rname="<?= htmlspecialchars($route->route_name) ?>"
                     data-start="<?= $route->start_meter ?>"
                     data-end="<?= $route->end_meter ?: 'Active' ?>"
                     data-sales="<?= number_format($route->total_sales, 2) ?>"
                     data-bills="<?= $route->bill_count ?>"
                     data-status="<?= $route->status ?>"
                     data-unfinalized="<?= $route->unfinalized_count ?>"
                     data-bound="<?= !empty($route->is_bound_group) ? '1' : '0' ?>"
                     data-binding-id="<?= $route->route_binding_id ?: '' ?>"
                     data-delivery-id="<?= $route->delivery_id ?: '' ?>"
                     data-delivery-status="<?= $route->delivery_status ?: '' ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Middle Pane: Workspace -->
    <div class="pane-middle">
        <!-- Header -->
        <div class="mid-header" id="midHeader" style="visibility: hidden; padding: 15px 25px;">
            <div>
                <h3 style="margin:0 0 5px 0; color:var(--primary);" id="mhRouteName">Route Name</h3>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: bold;">Representative: <span id="mhRepName"></span></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                    ODO Start: <strong id="mhStart"></strong> &nbsp;|&nbsp; ODO End: <strong id="mhEnd"></strong>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div class="stat-box"><span>Total Sales</span><strong style="color:#2e7d32;">Rs <span id="mhSales"></span></strong></div>
                <div class="stat-box"><span>Bills</span><strong id="mhBills"></strong></div>
                
                <button id="btnViewMap" onclick="openMapModal()" style="padding: 10px 15px; border: none; background: #ef6c00; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: none; align-items: center; gap: 4px;">📍 View Map</button>
                <button id="btnUnbindRoute" onclick="unbindActiveRoute()" style="padding: 10px 15px; border: none; background: #c62828; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: none; align-items: center; gap: 4px;">🔗 Undo Bind</button>
            </div>
        </div>

        <!-- Visual 8-Stage Progress Wizard -->
        <div class="workflow-wizard" id="workflowWizard" style="display: none;">
            <div class="wizard-step" id="wstep-Active">1. Active Route</div>
            <div class="wizard-step" id="wstep-PendingGL">2. Credit Collections</div>
            <div class="wizard-step" id="wstep-Adjustments">3. Adjustments</div>
            <div class="wizard-step" id="wstep-PendingLoading">4. Pending Loading</div>
            <div class="wizard-step" id="wstep-FinalLoading">5. Final Loading</div>
            <div class="wizard-step" id="wstep-VarianceAdjustment">6. Variance Audit</div>
            <div class="wizard-step" id="wstep-Finalizing">7. Finalizing</div>
        </div>

        <!-- Content Area -->
        <div style="flex:1; overflow-y:auto; position:relative; background:#fff;" id="workspaceBody">
            <div class="empty-state" id="midEmptyState">
                <span>📍</span>
                Please select a route from the left to view details.
            </div>

            <!-- Loading Indicator -->
            <div id="midLoader" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; font-weight:bold; color:var(--primary);">
                Loading Stage Information... ⏳
            </div>

            <!-- Dynamic Stage Containers -->
            <div id="stageContentWrapper" style="display:none; padding:20px 25px;">
                
                <!-- STAGE 1: ACTIVE -->
                <div class="stage-section-panel" id="ssec-Active" style="display:none;">
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:15px; margin-bottom:20px; color:#1e3a8a;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">⚡ Live Route Operations Active</h4>
                        <p style="margin:0; font-size:12px;">The field agent is currently performing active route sales. When they submit their end-day meter, this route is ready for GL Audit.</p>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <h4 style="margin:0;">Created Sales Orders</h4>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>Time</th>
                                <th>Customer Name</th>
                                <th style="text-align:right;">Grand Total (Rs)</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody class="render-invoices-tbody"></tbody>
                    </table>
                    <div style="text-align:right; margin-top:25px;">
                        <button onclick="advanceRouteStatus('Pending GL')" style="padding:10px 20px; background:#ef6c00; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">🔒 Close Route & Move to GL Audit</button>
                    </div>
                </div>

                <!-- STAGE 2: PENDING GL -->
                <div class="stage-section-panel" id="ssec-PendingGL" style="display:none;">
                    <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:15px; margin-bottom:20px; color:#78350f;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">📄 General Ledger Audit & Verification</h4>
                        <p style="margin:0; font-size:12px;">Please audit all credit collections. Verify debit/credit accounts and approve collections before moving the route to Adjustments stage.</p>
                    </div>
                    <div>
                        <!-- Collections Verification Table Card -->
                        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.02); display:flex; flex-direction:column; justify-content:space-between; margin-bottom:20px;">
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                    <h5 style="margin:0; font-size:13px; font-weight:bold; color:#1e293b;">💵 Credit Collections & Verification</h5>
                                    <button onclick="saveCollectionsVerificationStage2()" style="padding:5px 12px; background:#2e7d32; color:#fff; border:none; border-radius:4px; font-size:11px; font-weight:bold; cursor:pointer; transition:0.2s;">💾 Save Verification</button>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                                    <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:8px; border-radius:6px; font-size:11px; text-align:center;">
                                        Cash: <strong id="glTotalCash" style="font-family:monospace; color:#2e7d32;">Rs 0.00</strong>
                                    </div>
                                    <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:8px; border-radius:6px; font-size:11px; text-align:center;">
                                        Cheque: <strong id="glTotalCheque" style="font-family:monospace; color:#0066cc;">Rs 0.00</strong>
                                    </div>
                                </div>
                                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    <table class="data-table" style="font-size:11px; margin-top:0;">
                                        <thead style="position: sticky; top: 0; z-index: 10;">
                                            <tr style="background:#f8fafc;">
                                                <th style="text-align:left; padding:8px 4px; font-size:10px; width:20%;">Customer / Pay</th>
                                                <th style="text-align:right; padding:8px 4px; font-size:10px; width:12%;">Collected</th>
                                                <th style="text-align:center; padding:8px 4px; font-size:10px; width:8%;">Approve</th>
                                                <th style="text-align:left; padding:8px 4px; font-size:10px; width:20%;">Debit Account</th>
                                                <th style="text-align:left; padding:8px 4px; font-size:10px; width:20%;">Credit Account</th>
                                                <th style="text-align:right; padding:8px 4px; font-size:10px; width:10%;">Adjusted</th>
                                                <th style="text-align:left; padding:8px 4px; font-size:10px; width:10%;">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody id="glCollectionsTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:25px; border-top:1px solid #eee; padding-top:15px;">
                        <button onclick="advanceRouteStatus('Active')" style="padding:10px 20px; background:#e2e8f0; color:#333; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">↩ Send back to Active</button>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <span id="glVerificationStatusText" style="font-size:12px; font-weight:bold;"></span>
                            <button id="glApproveSalesBtn" onclick="advanceRouteStatus('Adjustments')" style="padding:10px 20px; background:#0066cc; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; opacity:0.5; cursor:not-allowed;" disabled>✅ Approve Sales & Move to Adjustments</button>
                        </div>
                    </div>
                </div>

                <!-- STAGE 3: PENDING LOADING -->
                <!-- STAGE 3: ADJUSTMENTS -->
                <div class="stage-section-panel" id="ssec-Adjustments" style="display:none;">
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:15px; margin-bottom:20px; color:#1e3a8a;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">⚙️ Route Adjustments & Delivery Binding</h4>
                        <p style="margin:0; font-size:12px;">Add, attach or remove Sales Orders, and bind this route to a vehicle/delivery arrangement before physical warehouse loading preparation.</p>
                    </div>

                    <!-- Invoice & Sales Order Management Section -->
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.02); margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; font-size:14px; font-weight:bold;">Sales Order Operations</h4>
                            <div style="display:flex; gap:10px;">
                                <button onclick="redirectToAddInvoice()" style="padding:6px 12px; background:#0066cc; border:none; color:#fff; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer;">➕ Create Sales Order</button>
                                <button onclick="openAttachInvoiceModal()" style="padding:6px 12px; background:#5c6bc0; border:none; color:#fff; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer;">🔗 Attach Sales Order</button>
                            </div>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Time</th>
                                    <th>Customer Name</th>
                                    <th style="text-align:right;">Grand Total (Rs)</th>
                                    <th style="text-align:center;">Status</th>
                                    <th style="text-align:center; width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="adjustmentsInvoicesTbody"></tbody>
                        </table>
                    </div>

                    <!-- Delivery Arrangement Section -->
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.02); margin-bottom:20px;">
                        <h4 style="margin:0 0 15px 0; font-size:14px; font-weight:bold;">🚚 Delivery / Logistics Binding</h4>
                        
                        <!-- 1. Form View -->
                        <div id="adjDeliveryFormView" style="display:none;">
                            <form id="adjDeliveryArrangeForm" style="display:flex; flex-direction:column; gap:15px; max-width:650px; background:#fafafa; border:1px solid #e2e8f0; padding:20px; border-radius:8px; margin:0 auto;">
                                <div>
                                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:#555; display:block; margin-bottom:4px;">Delivery Date</label>
                                    <input type="date" id="adjDaDate" value="<?= date('Y-m-d') ?>" style="width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px;">
                                </div>

                                <div>
                                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:#555; display:block; margin-bottom:4px;">Bind Secondary Route (Optional)</label>
                                    <select id="adjDaSecondaryRoute" style="width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px;">
                                        <option value="">-- Select Route --</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:#555; display:block; margin-bottom:6px;">Select Territory Credit Invoices to dispatch with this vehicle</label>
                                    <div id="adjDaBillsContainer" style="border:1px solid #ccc; border-radius:6px; max-height:160px; overflow-y:auto; background:#fff; padding:8px;">
                                        <!-- Outstanding credit bills list -->
                                    </div>
                                </div>

                                <div style="text-align:right;">
                                    <button type="button" onclick="submitAdjustmentsLogisticsArrange()" style="padding:10px 20px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">🚚 Bind Delivery Manifest</button>
                                </div>
                            </form>
                        </div>

                        <!-- 2. Arranged View -->
                        <div id="adjDeliveryArrangedView" style="display:none;">
                            <div id="adjArrangedDetailsContainer" style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px; font-size:13px; line-height:1.6; margin-bottom:15px;"></div>
                            <div>
                                <button onclick="document.getElementById('adjDeliveryFormView').style.display='block'; document.getElementById('adjDeliveryArrangedView').style.display='none';" style="padding:8px 16px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer;">✏️ Edit/Re-arrange Delivery</button>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:25px; border-top:1px solid #eee; padding-top:15px;">
                        <button onclick="advanceRouteStatus('Pending GL')" style="padding:10px 20px; background:#e2e8f0; color:#333; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">↩ Back to GL Audit</button>
                        <button onclick="advanceRouteStatus('Pending Loading')" style="padding:10px 20px; background:#ef6c00; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">⚡ Confirm Adjustments & Move to Pending Loading</button>
                    </div>
                </div>

                <!-- STAGE 4: PENDING LOADING -->
                <div class="stage-section-panel" id="ssec-PendingLoading" style="display:none;">
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:15px; margin-bottom:20px; color:#1e3a8a;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">📦 Warehouse Loading Preparation</h4>
                        <p style="margin:0; font-size:12px;">Physical picking is underway. You can print the loading sheet or summary here. When ready for physical vehicle verification, move the route to Final Loading.</p>
                    </div>

                    <!-- Manifest Detail Summary -->
                    <div id="arrangeSummaryPanel" style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px; font-size:13px; line-height:1.6; margin-bottom:20px;"></div>

                    <!-- Aggregated Products to Load List -->
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.02); margin-bottom:20px;">
                        <h4 style="margin:0 0 15px 0; font-size:14px; font-weight:bold;">Aggregated Loading Products Checklist</h4>
                        <div id="pendingLoadingItemsBox"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:25px; border-top:1px solid #eee; padding-top:15px;">
                        <div>
                            <button onclick="advanceRouteStatus('Adjustments')" style="padding:10px 20px; background:#e2e8f0; color:#333; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">↩ Back to Adjustments</button>
                            <button onclick="printLoadingSheet('pre')" style="padding:10px 20px; background:#3f51b5; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer; margin-left:10px;">🖨️ Print Loading Sheet</button>
                            <button onclick="printLoadingSheet('summary')" style="padding:10px 20px; background:#673ab7; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer; margin-left:10px;">🖨️ Print Loading Summary</button>
                        </div>
                        <button onclick="advanceRouteStatus('Final Loading')" style="padding:10px 20px; background:#ef6c00; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">⚡ Send to Final Loading</button>
                    </div>
                </div>

                <!-- STAGE 4: FINAL LOADING -->
                <div class="stage-section-panel" id="ssec-FinalLoading" style="display:none;">
                    <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:15px; margin-bottom:20px; color:#78350f;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">🔍 Final Loading Verification Checklist</h4>
                        <p style="margin:0; font-size:12px;">Perform physical count checks before the vehicle dispatches. Discrepancies will be reported under Variance Adjustment.</p>
                    </div>
                    <div id="finalLoadingBox" style="margin-bottom:20px;"></div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <button onclick="advanceRouteStatus('Pending Loading')" style="padding:8px 16px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer;">↩ Re-open Picking</button>
                            <button onclick="printLoadingSheet('final')" style="padding:8px 16px; background:#3f51b5; color:#fff; border:none; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer; margin-left:10px;">🖨️ Print Final Loading Sheet</button>
                        </div>
                        <button onclick="advanceRouteStatus('Variance Adjustment')" style="padding:10px 20px; background:#0066cc; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">🔍 Submit for Variance Audit</button>
                    </div>
                </div>

                <!-- STAGE 7: VARIANCE ADJUSTMENT -->
                <div class="stage-section-panel" id="ssec-VarianceAdjustment" style="display:none;">
                    <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:15px; margin-bottom:20px; color:#991b1b;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">🚨 Variance Adjustment Approval</h4>
                        <p style="margin:0; font-size:12px;">Review shortages and overages identified from final picking. Confirm variances before proceeding to financial finalize settlement.</p>
                    </div>
                    <div id="varianceAuditBox" style="margin-bottom:20px;"></div>
                    <div style="display:flex; justify-content:space-between;">
                        <button onclick="advanceRouteStatus('Final Loading')" style="padding:8px 16px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; font-size:12px; cursor:pointer;">↩ Back to Verification</button>
                        <button onclick="advanceRouteStatus('Finalizing')" style="padding:10px 20px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:13px; cursor:pointer;">⚖️ Approve Variances & Proceed to Settlements</button>
                    </div>
                </div>

                <!-- STAGE 8: FINALIZING (SETTLEMENTS) -->
                <div class="stage-section-panel" id="ssec-Finalizing" style="display:none;">
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:15px; margin-bottom:20px; color:#166534;">
                        <h4 style="margin:0 0 5px 0; font-size:14px; font-weight:bold;">⚖️ Final Balancing & Settlement Posting</h4>
                        <p style="margin:0; font-size:12px;">Verify collections, return items, and assign appropriate Ledger double entries to balance and close the route.</p>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                        <!-- Collections Verification Table Card -->
                        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                    <h5 style="margin:0; font-size:13px;">💵 Collected Payment Settle Verification</h5>
                                    <button onclick="saveCollectionsVerification()" style="padding:4px 10px; background:#2e7d32; color:#fff; border:none; border-radius:4px; font-size:11px; font-weight:bold; cursor:pointer;">💾 Save Verification</button>
                                </div>
                                <table class="data-table" style="font-size:11px; margin-top:5px; border: 1px solid #eee;">
                                    <thead>
                                        <tr style="background:#f8fafc;">
                                            <th style="text-align:left; padding:6px 4px;">Customer / Payment</th>
                                            <th style="text-align:right; padding:6px 4px;">Collected</th>
                                            <th style="text-align:center; padding:6px 4px;">Status</th>
                                            <th style="text-align:right; padding:6px 4px;">Adjusted Amt</th>
                                            <th style="text-align:left; padding:6px 4px;">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="settleCollectionsTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Stock and returns card -->
                        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff;">
                            <h5 style="margin:0 0 10px 0; font-size:13px;">📦 Returned Stock Settle Verification</h5>
                            <div style="margin-bottom:10px;"><label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" id="settleVerifyStock" onchange="checkSettleVerification()"> Verified Return Items Count</label></div>
                            <table class="data-table" style="font-size:11px;">
                                <thead>
                                    <tr><th>Product Name</th><th>Loaded</th><th>Deliv</th><th style="text-align:right;">Returned</th></tr>
                                </thead>
                                <tbody id="settleStockTableBody"></tbody>
                            </table>
                        </div>
                    <!-- Driver, Vehicle, Partner Assignment (Finalize) -->
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; margin-bottom:20px;">
                        <h5 style="margin:0 0 10px 0; font-size:13px; font-weight:bold; color:#1e293b;">🚚 Dispatch & Logistics Assignment</h5>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px;">
                            <div>
                                <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:#555; display:block; margin-bottom:4px;">Vehicle Number *</label>
                                <select id="settleDaVehicle" style="width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px;" required>
                                    <option value="">-- Select Vehicle --</option>
                                    <?php foreach($data['vehicles'] as $v): ?>
                                        <?php if($v->status === 'Active'): ?>
                                            <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:#555; display:block; margin-bottom:4px;">Driver Name *</label>
                                <select id="settleDaDriver" style="width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px;" required>
                                    <option value="">-- Select Driver --</option>
                                    <?php foreach($data['drivers'] as $d): ?>
                                        <option value="<?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?>"><?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:#555; display:block; margin-bottom:4px;">Partner / Helper</label>
                                <select id="settleDaPartner" style="width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px;">
                                    <option value="">-- None --</option>
                                    <?php foreach($data['employees'] as $e): ?>
                                        <?php if($e->status === 'Active'): ?>
                                            <option value="<?= htmlspecialchars($e->first_name . ' ' . $e->last_name) ?>"><?= htmlspecialchars($e->first_name . ' ' . $e->last_name) ?> (<?= htmlspecialchars($e->job_title) ?>)</option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- General Ledger double entries mapping panel -->
                    <div style="border:1px solid #e2e8f0; border-radius:8px; padding:15px; background:#fff; margin-bottom:20px;">
                        <h5 style="margin:0 0 10px 0; font-size:13px;">💼 General Ledger Account Double Entry Mappings</h5>
                        <div style="display: flex; gap: 10px; border-bottom: 1px solid #eee; margin-bottom: 15px;">
                            <button type="button" class="left-tab-btn active" id="settleDeTabCollectionsBtn" onclick="switchSettleDeTab('collections')">💵 Cash/Cheques Posting</button>
                            <button type="button" class="left-tab-btn" id="settleDeTabSalesBtn" onclick="switchSettleDeTab('sales')">📦 Invoices Sales Posting</button>
                        </div>
                        <div id="settleDeCollectionsContainer"></div>
                        <div id="settleDeSalesContainer" style="display:none;"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #eee; padding-top:20px;">
                        <div id="settleStatusText" style="font-size:12px; color:#c62828; font-weight:bold;">
                            Please verify Cash, Cheques, and Return stock counts above to unlock Finalization.
                        </div>
                        <button id="settleSubmitBtn" onclick="submitFinalSettle()" style="padding:12px 24px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-weight:bold; font-size:14px; opacity:0.5; cursor:not-allowed;" disabled>
                            ⚖️ Settle Balancing & Finalize Route
                        </button>
                    </div>
                </div>

                <!-- COMPLETED / READ ONLY VIEW -->
                <div class="stage-section-panel" id="ssec-Completed" style="display:none;">
                    <div style="background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px; padding:15px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h4 style="margin:0; font-size:14px; font-weight:bold; color:#2e7d32;">🏁 Route Settle Balancing Finalized</h4>
                            <p style="margin:5px 0 0 0; font-size:12px; color:#666;">This route is read-only. All transactions, inventories, and GL postings are successfully finalized.</p>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button onclick="printBalancingReport()" style="padding:8px 12px; background:#0066cc; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;">Print Balancing Report 🖨</button>
                            <button onclick="printLoadingSheetSpreadsheet()" style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;">Print Spreadsheet 📊</button>
                            <button onclick="printLoadingSheet('summary')" style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;">Print Loading Summary 🚚</button>
                            <button onclick="exportCSV()" style="padding:8px 12px; background:#e2e8f0; color:#333; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:12px;">Export CSV 📥</button>
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:10px; border-bottom:1px solid #eee; margin-bottom:15px;">
                        <button class="left-tab-btn active" id="compTabInvoicesBtn" onclick="switchCompletedTab('invoices')">📄 Invoices</button>
                        <button class="left-tab-btn" id="compTabCollectionsBtn" onclick="switchCompletedTab('collections')">💰 Settled Collections</button>
                        <button class="left-tab-btn" id="compTabVariancesBtn" onclick="switchCompletedTab('variances')">🚚 Variances</button>
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

            </div>
        </div>
    </div>

    <!-- Right Pane: Invoice Slide-Out Viewer -->
    <div class="pane-right-slider" id="invoiceSlider">
        <div class="slider-header">
            <span>Sales Order Mini-Viewer</span>
            <div>
                <a id="btnEditInvoice" href="#" style="background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 15px; border: 1px solid rgba(255,255,255,0.4);">✏️ Edit</a>
                <button id="btnDeleteInvoice" onclick="deleteSalesOrder()" style="background: #dc2626; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 15px; cursor: pointer; font-weight: bold;">🗑️ Delete</button>
                <button class="close-slider" onclick="closeInvoiceSlider()">✕</button>
            </div>
        </div>
        <iframe id="invoiceIframe" src="about:blank"></iframe>
    </div>
</div>

<!-- Dynamic GPS Map Modal -->
<div class="modal-backdrop" id="mapModalBackdrop">
    <div class="modal-panel" style="max-width: 950px; width: 90%; height: 80vh; display: flex; flex-direction: column;">
        <div style="background: #3f51b5; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 15px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span>📍 GPS Route Path Tracking</span>
                <span id="modalRouteName" style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 11px;"></span>
                <span id="pathPointCount" style="font-weight: normal; font-size: 11px;"></span>
            </div>
            <button class="close-slider" onclick="closeMapModal()">✕</button>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; position: relative; background: #eef1f4;">
            <div style="flex: 1; position: relative; min-height: 350px;">
                <div id="mapEmptyOverlay" class="map-empty-overlay">Loading...</div>
                <div id="routePathMap" style="height: 100%; width: 100%;"></div>
            </div>
            <div class="path-step-list" id="pathStepList" style="display: none;">
                <ol id="pathStepOl"></ol>
            </div>
        </div>
    </div>
</div>

<!-- Route Multi-Binding Modal -->
<div class="modal-backdrop" id="routeBindingModal">
    <div class="modal-panel" style="max-width: 900px; width: 95%; max-height: 90vh;">
        <div class="modal-header" style="background: #3f51b5;">
            <span>🔗 Rep Route Multi-Binding Panel</span>
            <button onclick="closeRouteBindingModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body" style="overflow-y:auto; flex:1;">
            <div style="margin-bottom: 20px;">
                <label>Custom Name for Bound Group</label>
                <input type="text" id="rbBoundName" placeholder="e.g. Western Route Combined - June 15">
            </div>
            <label>Route Slots</label>
            <div id="rbSlotsContainer" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px,1fr)); gap:15px; margin-bottom:15px;"></div>
            <button type="button" onclick="addBindingSlot()" style="background: #eef2ff; color: #3f51b5; border: 1px dashed #3f51b5; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: block; width: 100%;">➕ Add Route Slot</button>
        </div>
        <div class="modal-footer">
            <button class="qb-btn" onclick="closeRouteBindingModal()" style="border:1px solid #ccc; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="submitRouteBinding()" style="background:#2e7d32; color:#fff; border:none; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;">⚡ Confirm & Create Route Binding</button>
        </div>
    </div>
</div>

<!-- Attach Sales Order Modal -->
<div class="modal-backdrop" id="attachInvoiceModal">
    <div class="modal-panel" style="max-width: 580px; width: 90%;">
        <div class="modal-header" style="background: #5c6bc0;">
            <span>🔗 Attach Sales Orders to Route</span>
            <button onclick="closeAttachInvoiceModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body">
            <div>
                <label>Search Sales Order or Customer</label>
                <input type="text" id="invoiceSearchInput" onkeyup="searchUnattachedInvoices()" placeholder="Type SO-XXXX or customer name...">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label>Start Date</label>
                    <input type="date" id="soFilterStartDate" onchange="searchUnattachedInvoices()">
                </div>
                <div>
                    <label>End Date</label>
                    <input type="date" id="soFilterEndDate" onchange="searchUnattachedInvoices()">
                </div>
            </div>
            <div>
                <label>Status Filter</label>
                <select id="soFilterStatus" onchange="searchUnattachedInvoices()" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: white;">
                    <option value="">All Statuses</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <div id="unattachedInvoicesContainer" style="border: 1px solid #ccc; border-radius: 6px; padding: 10px; max-height: 200px; overflow-y: auto; background: #fafafa; font-size: 12px; margin-top: 5px;">
                <p style="text-align: center; color: #888; margin: 10px 0;">Start typing to search...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="qb-btn" onclick="closeAttachInvoiceModal()" style="border:1px solid #ccc; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="confirmAttachInvoices()" style="background:#5c6bc0; color:#fff; border:none; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;">Attach Selected</button>
        </div>
    </div>
</div>

<script>
    const globalBankAccounts = <?php echo json_encode($data['bank_accounts'] ?? []); ?>;
    const globalAllAccounts = <?php echo json_encode($data['all_accounts'] ?? []); ?>;
    let currentRouteId = null;
    let routeMap = null;
    let routeMapLayers = [];
    let rbSlotsCount = 2;
    let activeRouteBills = [];
    let currentDeliveryDetails = null;

    const pathGreenIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
    });
    const pathRedIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
    });
    const pathBlueIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
    });

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const routeId = urlParams.get('route_id');
        const filterType = urlParams.get('filter');

        if (filterType) {
            const filterBtns = document.querySelectorAll('.global-filter-btn');
            let matchedBtn = null;
            filterBtns.forEach(btn => {
                const onclickAttr = btn.getAttribute('onclick') || '';
                if (onclickAttr.includes(`'${filterType}'`)) {
                    matchedBtn = btn;
                }
            });
            filterLeftPane(filterType, matchedBtn || filterBtns[0]);
        } else {
            filterLeftPane('all', document.querySelector('.global-filter-btn'));
        }

        if (routeId) {
            const routeEl = document.getElementById('route_' + routeId);
            if (routeEl) {
                loadRouteDetails(routeId, routeEl);
                routeEl.scrollIntoView({ block: 'nearest' });
            }
        }
    });

    function filterLeftPane(type, btn) {
        document.querySelectorAll('.global-filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        if (btn) btn.classList.add('active');
        
        document.querySelectorAll('.route-item').forEach(item => {
            const rType = item.getAttribute('data-route-type');
            if (type === 'all') {
                item.style.display = 'block';
            } else if (type === 'pending_loading') {
                if (rType === 'pending_delivery' || rType === 'arrangement') {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            } else if (rType === type) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function updateWizardProgress(status) {
        document.getElementById('workflowWizard').style.display = 'flex';
        const steps = ['Active', 'PendingGL', 'Adjustments', 'PendingLoading', 'FinalLoading', 'VarianceAdjustment', 'Finalizing'];
        
        let statusIndex = steps.indexOf(status.replace(' ', ''));
        if (status === 'Completed' || status === 'Finalized') {
            statusIndex = 99; // All completed
        }

        steps.forEach((step, idx) => {
            const el = document.getElementById('wstep-' + step);
            if (el) {
                el.classList.remove('active', 'completed');
                if (idx === statusIndex) {
                    el.classList.add('active');
                } else if (idx < statusIndex) {
                    el.classList.add('completed');
                }
            }
        });
    }

    function loadRouteDetails(routeId, el) {
        currentRouteId = routeId;
        
        document.querySelectorAll('.route-item').forEach(i => i.classList.remove('active'));
        if (el) el.classList.add('active');

        const d = document.getElementById('route_data_' + routeId);
        const routeName = d.getAttribute('data-rname');
        const repName = d.getAttribute('data-rep');
        const status = d.getAttribute('data-status');
        const bindingId = d.getAttribute('data-binding-id');
        const isBound = d.getAttribute('data-bound') === '1';

        document.getElementById('mhRouteName').innerText = routeName;
        document.getElementById('mhRepName').innerText = repName;
        document.getElementById('mhStart').innerText = d.getAttribute('data-start');
        document.getElementById('mhEnd').innerText = d.getAttribute('data-end');
        document.getElementById('mhSales').innerText = d.getAttribute('data-sales');
        document.getElementById('mhBills').innerText = d.getAttribute('data-bills');

        document.getElementById('midHeader').style.visibility = 'visible';
        document.getElementById('midEmptyState').style.display = 'none';
        document.getElementById('btnViewMap').style.display = 'inline-flex';

        const btnUnbind = document.getElementById('btnUnbindRoute');
        if (btnUnbind) {
            if (isBound && bindingId) {
                btnUnbind.style.display = 'inline-flex';
                btnUnbind.setAttribute('data-binding-id', bindingId);
            } else {
                btnUnbind.style.display = 'none';
            }
        }

        updateWizardProgress(status);

        document.getElementById('stageContentWrapper').style.display = 'block';
        document.querySelectorAll('.stage-section-panel').forEach(p => p.style.display = 'none');

        // Close slider
        closeInvoiceSlider();

        // Load specific stage data
        if (status === 'Active') {
            document.getElementById('ssec-Active').style.display = 'block';
            loadActiveStageBills(routeId);
        } else if (status === 'Pending GL') {
            document.getElementById('ssec-PendingGL').style.display = 'block';
            loadActiveStageBills(routeId);
            loadCollectionsVerificationStage2(routeId);
        } else if (status === 'Adjustments') {
            document.getElementById('ssec-Adjustments').style.display = 'block';
            loadAdjustmentsStage(routeId);
        } else if (status === 'Pending Loading') {
            document.getElementById('ssec-PendingLoading').style.display = 'block';
            loadPendingLoadingStage(routeId);
        } else if (status === 'Final Loading') {
            document.getElementById('ssec-FinalLoading').style.display = 'block';
            loadFinalLoadingStage(routeId);
        } else if (status === 'Variance Adjustment') {
            document.getElementById('ssec-VarianceAdjustment').style.display = 'block';
            loadVarianceAdjustmentStage(routeId);
        } else if (status === 'Finalizing') {
            document.getElementById('ssec-Finalizing').style.display = 'block';
            loadFinalizingStage(routeId);
        } else if (status === 'Completed' || status === 'Finalized') {
            document.getElementById('ssec-Completed').style.display = 'block';
            loadCompletedStage(routeId);
        }
    }

    function loadActiveStageBills(routeId) {
        const tbodies = document.querySelectorAll('.render-invoices-tbody');
        tbodies.forEach(tbody => {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading bills... ⏳</td></tr>';
        });

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(res => res.json())
            .then(data => {
                activeRouteBills = data.bills || [];
                tbodies.forEach(tbody => {
                    tbody.innerHTML = '';
                    if (activeRouteBills.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#888;">No bills generated on this route.</td></tr>';
                        return;
                    }
                    activeRouteBills.forEach(bill => {
                        let time = new Date(bill.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        let statColor = bill.status === 'Paid' ? '#2e7d32' : (bill.status === 'Unpaid' ? '#ef6c00' : '#666');
                        tbody.innerHTML += `
                            <tr class="bill-row" onclick="openInvoiceSlider(${bill.id})">
                                <td style="font-weight:bold; color:var(--primary);">${bill.invoice_number}</td>
                                <td>${time}</td>
                                <td><strong>${bill.customer_name}</strong></td>
                                <td style="text-align:right; font-family:monospace; font-weight:bold;">${parseFloat(bill.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                <td style="text-align:center;"><span style="color:${statColor}; font-weight:bold; text-transform:uppercase;">${bill.status}</span></td>
                            </tr>
                        `;
                    });
                });
            });
    }

    function loadAdjustmentsStage(routeId) {
        const tbody = document.getElementById('adjustmentsInvoicesTbody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Loading Sales Orders... ⏳</td></tr>';

        // Load secondary routes for logistics binding dropdown
        const secSelect = document.getElementById('adjDaSecondaryRoute');
        secSelect.innerHTML = '<option value="">-- Choose Route --</option>';
        document.querySelectorAll('.route-item').forEach(item => {
            const id = item.id.replace('route_', '');
            if (parseInt(id) !== parseInt(routeId)) {
                const rdata = document.getElementById('route_data_' + id);
                if (rdata && rdata.getAttribute('data-status') === 'Adjustments') {
                    secSelect.innerHTML += `<option value="${id}">${rdata.getAttribute('data-rname')} (Rep: ${rdata.getAttribute('data-rep')})</option>`;
                }
            }
        });

        // Load outstanding bills
        const container = document.getElementById('adjDaBillsContainer');
        container.innerHTML = '<p style="text-align:center; color:#888;">Loading credit bills... ⏳</p>';

        fetch('<?= APP_URL ?>/RepTracking/api_get_outstanding_bills/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.bills || data.bills.length === 0) {
                    container.innerHTML = '<p style="text-align:center; color:#888; margin:10px 0;">No outstanding credit bills found in these territories.</p>';
                } else {
                    let html = '<div style="display:flex; flex-direction:column; gap:8px;">';
                    data.bills.forEach(cust => {
                        cust.bills.forEach(b => {
                            let amtFormatted = parseFloat(b.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2});
                            html += `
                                <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:6px; border-bottom:1px solid #f0f0f0;">
                                    <input type="checkbox" class="adj-da-bill-checkbox" value="${b.id}" style="width:16px; height:16px;">
                                    <div style="flex:1;">
                                        <div style="font-weight:bold;">${b.invoice_number}</div>
                                        <div style="font-size:11px; color:#666;">Customer: <strong>${cust.customer_name}</strong> | Date: ${b.invoice_date}</div>
                                    </div>
                                    <div style="font-weight:bold; font-family:monospace; color:#c62828;">Rs ${amtFormatted}</div>
                                </label>
                            `;
                        });
                    });
                    html += '</div>';
                    container.innerHTML = html;
                }

                // Now fetch current route details & delivery status
                return fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId);
            })
            .then(res => res.json())
            .then(data => {
                const bills = data.bills || [];
                tbody.innerHTML = '';
                if (bills.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">No sales orders attached to this route.</td></tr>';
                } else {
                    bills.forEach(bill => {
                        let time = new Date(bill.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        let statColor = bill.status === 'Paid' ? '#2e7d32' : (bill.status === 'Unpaid' ? '#ef6c00' : '#666');
                        tbody.innerHTML += `
                            <tr>
                                <td style="font-weight:bold; color:var(--primary); cursor:pointer;" onclick="openInvoiceSlider(${bill.id})">${bill.invoice_number}</td>
                                <td>${time}</td>
                                <td><strong>${bill.customer_name}</strong></td>
                                <td style="text-align:right; font-family:monospace; font-weight:bold;">${parseFloat(bill.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                <td style="text-align:center;"><span style="color:${statColor}; font-weight:bold; text-transform:uppercase;">${bill.status}</span></td>
                                <td style="text-align:center;">
                                    <button onclick="detachInvoice(${bill.id})" style="padding:4px 8px; background:#ef4444; color:#fff; border:none; border-radius:4px; font-size:11px; cursor:pointer; font-weight:bold;">❌ Remove</button>
                                </td>
                            </tr>
                        `;
                    });
                }

                // Check delivery details
                const rdata = document.getElementById('route_data_' + routeId);
                const delId = rdata.getAttribute('data-delivery-id');
                if (!delId || delId === '0' || delId === '') {
                    // Not arranged yet
                    document.getElementById('adjDeliveryArrangedView').style.display = 'none';
                    document.getElementById('adjDeliveryFormView').style.display = 'block';
                } else {
                    // Already arranged
                    document.getElementById('adjDeliveryFormView').style.display = 'none';
                    document.getElementById('adjDeliveryArrangedView').style.display = 'block';
                    
                    // Fetch delivery info
                    fetch('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
                        .then(res => res.json())
                        .then(dData => {
                            const container = document.getElementById('adjArrangedDetailsContainer');
                            if (dData.delivery) {
                                container.innerHTML = `
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                        <div><strong>Delivery Date:</strong> ${dData.delivery.delivery_date}</div>
                                        <div><strong>Secondary Bound Route:</strong> ${dData.delivery.secondary_route_name || 'None'}</div>
                                    </div>
                                    <div style="margin-top:10px; font-weight:bold; font-size:12px; color:#1e3a8a;">
                                        🔗 Bound Manifest successfully generated. Ready for warehouse.
                                    </div>
                                `;
                            } else {
                                container.innerHTML = 'Error loading delivery details.';
                            }
                        });
                }
            });
    }

    function detachInvoice(invoiceId) {
        if (!confirm("Are you sure you want to remove/detach this Sales Order from this route?")) return;
        fetch('<?= APP_URL ?>/RepTracking/api_detach_invoice', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ invoice_id: invoiceId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Sales Order successfully detached from route!");
                loadAdjustmentsStage(currentRouteId);
            } else {
                alert("Error: " + data.message);
            }
        });
    }

    function submitAdjustmentsLogisticsArrange() {
        const date = document.getElementById('adjDaDate').value;
        const secondary = document.getElementById('adjDaSecondaryRoute').value;

        const checkedBills = [];
        document.querySelectorAll('.adj-da-bill-checkbox:checked').forEach(cb => {
            checkedBills.push(parseInt(cb.value));
        });

        const payload = {
            rep_route_id: currentRouteId,
            secondary_rep_route_id: secondary ? parseInt(secondary) : null,
            delivery_date: date,
            vehicle_number: '',
            driver_name: '',
            partner_name: '',
            selected_credit_invoices: checkedBills
        };

        fetch('<?= APP_URL ?>/RepTracking/arrange', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Delivery bound successfully!");
                const rdata = document.getElementById('route_data_' + currentRouteId);
                if (rdata) {
                    rdata.setAttribute('data-delivery-id', data.delivery_id);
                }
                loadAdjustmentsStage(currentRouteId);
            } else {
                alert("⚠️ Error: " + data.message);
            }
        });
    }

    function loadPendingLoadingStage(routeId) {
        loadArrangeSummaryStage(routeId);
        loadPendingLoadingItemsChecklist(routeId);
    }

    function loadPendingLoadingItemsChecklist(routeId) {
        const box = document.getElementById('pendingLoadingItemsBox');
        box.innerHTML = 'Loading loading items checklist... ⏳';

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.deliveries || data.deliveries.length === 0) {
                    box.innerHTML = '<p style="color:red; padding:10px;">No verification records found. Please ensure delivery is bound in Adjustments stage.</p>';
                    return;
                }
                const del = data.deliveries[0];
                let listHtml = '';
                del.items.forEach(item => {
                    listHtml += `
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:8px 0; font-weight:bold;">${item.item_name}</td>
                            <td style="text-align:center; font-family:monospace; font-size:12px; font-weight:bold;">${item.required_qty}</td>
                        </tr>
                    `;
                });

                box.innerHTML = `
                    <div style="background:#f1f5f9; padding:12px; border-radius:6px; margin-bottom:15px; font-size:12px; font-weight:bold; color:#1e293b;">
                        📦 Picking Manifest ID: #${del.delivery_id} | Total Unique Items: ${del.total_items}
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr><th>Product Name / Item Description</th><th style="text-align:center; width:120px;">Required Quantity</th></tr>
                        </thead>
                        <tbody>${listHtml}</tbody>
                    </table>
                `;
            });
    }

    function loadArrangeSummaryStage(routeId) {
        const d = document.getElementById('route_data_' + routeId);
        const delId = d.getAttribute('data-delivery-id');
        const container = document.getElementById('arrangeSummaryPanel');
        container.innerHTML = 'Loading manifest details... ⏳';

        if (!delId) {
            container.innerHTML = '<p style="color:red; font-weight:bold;">No arranged delivery ID found for this route.</p>';
            return;
        }

        fetch('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    container.innerHTML = '<p style="color:red;">Failed to retrieve delivery data.</p>';
                    return;
                }
                const del = data.delivery;
                container.innerHTML = `
                    <h4 style="margin:0 0 10px 0; border-bottom:1px solid #ddd; padding-bottom:8px;">🚚 Delivery Arrangement Manifest Details</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
                        <div>Vehicle: <strong>${del.vehicle_number || 'Not Assigned Yet'}</strong></div>
                        <div>Delivery Date: <strong>${del.delivery_date}</strong></div>
                        <div>Driver Name: <strong>${del.driver_name || 'Not Assigned Yet'}</strong></div>
                        <div>Partner Name: <strong>${del.partner_name || 'None'}</strong></div>
                    </div>
                    <div>Invoices Assigned: <strong>${data.invoices.length}</strong></div>
                    <div>Credit Invoices Assigned: <strong>${data.credit_invoices.length}</strong></div>
                `;
            });
    }

    function loadFinalLoadingStage(routeId) {
        const box = document.getElementById('finalLoadingBox');
        box.innerHTML = 'Loading loading items checklist... ⏳';

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.deliveries || data.deliveries.length === 0) {
                    box.innerHTML = '<p style="color:red;">No verification records.</p>';
                    return;
                }
                const del = data.deliveries[0];
                let listHtml = '';
                del.items.forEach(item => {
                    let finalVal = item.final_loaded_qty !== null ? item.final_loaded_qty : '-';
                    let statusText = item.is_verified ? '<span style="color:#2e7d32; font-weight:bold;">Verified</span>' : '<span style="color:#ef6c00; font-weight:bold;">Pending</span>';
                    listHtml += `
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:8px 0; font-weight:bold;">${item.item_name}</td>
                            <td style="text-align:center; font-weight:bold;">${item.required_qty}</td>
                            <td style="text-align:center;">${item.pre_loaded_qty}</td>
                            <td style="text-align:center; font-weight:bold; color:#0066cc;">${finalVal}</td>
                            <td style="text-align:center;">${statusText}</td>
                        </tr>
                    `;
                });

                box.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#f1f5f9; padding:12px; border-radius:6px; margin-bottom:15px;">
                        <div>Loading Sheet Verification ID: <strong>#${del.delivery_id}</strong></div>
                        <div>Verification: <strong>${del.verified_items} / ${del.total_items} verified</strong></div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr><th>Product Name</th><th style="text-align:center;">Required</th><th style="text-align:center;">Pre-Loaded</th><th style="text-align:center;">Final Loaded</th><th style="text-align:center;">Status</th></tr>
                        </thead>
                        <tbody>${listHtml}</tbody>
                    </table>
                `;
            });
    }

    let currentVarianceState = {};

    function loadVarianceAdjustmentStage(routeId) {
        const box = document.getElementById('varianceAuditBox');
        box.innerHTML = '<div style="padding:20px; text-align:center;">Loading shortages & overages... ⏳</div>';
        currentVarianceState = {};

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.deliveries || data.deliveries.length === 0) {
                    box.innerHTML = '<p style="color:red; padding:10px;">No variance records found.</p>';
                    return;
                }
                const del = data.deliveries[0];
                const items = del.items || [];
                
                if (items.length === 0) {
                    box.innerHTML = '<p style="color:green; padding:10px; font-weight:bold;">No products picked on this route.</p>';
                    return;
                }

                let fetchPromises = [];
                items.forEach(item => {
                    const itemId = item.item_id;
                    currentVarianceState[itemId] = {
                        item_id: itemId,
                        item_name: item.item_name,
                        required_qty: parseFloat(item.required_qty),
                        pre_loaded_qty: parseFloat(item.pre_loaded_qty),
                        final_loaded_qty: item.final_loaded_qty !== null ? parseFloat(item.final_loaded_qty) : parseFloat(item.required_qty),
                        variance: parseFloat(item.variance),
                        invoices: []
                    };

                    if (parseFloat(item.variance) !== 0) {
                        const p = fetch('<?= APP_URL ?>/RepTracking/api_get_product_invoices?route_id=' + routeId + '&item_id=' + itemId)
                            .then(res => res.json())
                            .then(invData => {
                                if (invData.status === 'success') {
                                    currentVarianceState[itemId].invoices = invData.invoices.map(inv => ({
                                        invoice_id: parseInt(inv.invoice_id),
                                        invoice_number: inv.invoice_number,
                                        customer_name: inv.customer_name,
                                        original_qty: parseFloat(inv.quantity),
                                        quantity: parseFloat(inv.quantity),
                                        unit_price: parseFloat(inv.unit_price)
                                    }));
                                }
                            });
                        fetchPromises.push(p);
                    }
                });

                Promise.all(fetchPromises).then(() => {
                    renderVarianceReconciliation();
                });
            });
    }

    function renderVarianceReconciliation() {
        const box = document.getElementById('varianceAuditBox');
        let html = '';

        let totalShortages = 0;
        let totalOverages = 0;
        let hasUnbalanced = false;

        let tableRows = '';
        
        Object.values(currentVarianceState).forEach(item => {
            const variance = item.variance;
            if (variance < 0) totalShortages += Math.abs(variance);
            if (variance > 0) totalOverages += variance;

            let varColor = '#2e7d32';
            let varText = 'Match (0)';
            if (variance < 0) {
                varColor = '#c62828';
                varText = `${variance} (Shortage)`;
            } else if (variance > 0) {
                varColor = '#ef6c00';
                varText = `+${variance} (Overage)`;
            }

            let allocatedSum = 0;
            if (variance === 0) {
                allocatedSum = item.final_loaded_qty;
            } else {
                item.invoices.forEach(inv => {
                    allocatedSum += inv.quantity;
                });
            }

            const isItemBalanced = (Math.abs(allocatedSum - item.final_loaded_qty) < 0.01);
            if (!isItemBalanced) {
                hasUnbalanced = true;
            }

            const statusBadge = isItemBalanced 
                ? `<span style="background:#d1fae5; color:#065f46; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">✔ Balanced</span>`
                : `<span style="background:#fee2e2; color:#991b1b; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;">⚠️ Unbalanced</span>`;

            tableRows += `
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px; font-weight:bold; color:#1e293b;">${item.item_name}</td>
                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace;">${item.required_qty}</td>
                    <td style="padding:10px; text-align:center; font-family:monospace;">${item.pre_loaded_qty}</td>
                    <td style="padding:10px; text-align:center; font-weight:bold; font-family:monospace; background:#f8fafc;">${item.final_loaded_qty}</td>
                    <td style="padding:10px; text-align:center; font-weight:bold; color:${varColor}; font-family:monospace;">${varText}</td>
                    <td style="padding:10px; text-align:center;">${statusBadge}</td>
                </tr>
            `;
        });

        html += `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:6px; padding:12px; text-align:center; color:#991b1b;">
                    <span>Shortages to Reconcile</span><br><strong style="font-size:16px;">${totalShortages} pcs</strong>
                </div>
                <div style="background:#fff3e0; border:1px solid #ffe0b2; border-radius:6px; padding:12px; text-align:center; color:#e65100;">
                    <span>Overages to Reconcile</span><br><strong style="font-size:16px;">${totalOverages} pcs</strong>
                </div>
            </div>
            
            <h5 style="margin:0 0 10px 0; font-size:13px; color:#475569; text-transform:uppercase; font-weight:bold;">📦 Product Loading Variances</h5>
            <table class="data-table" style="margin-bottom:25px; border:1px solid #e2e8f0; border-radius:6px; overflow:hidden;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="padding:10px; text-align:left;">Product Name</th>
                        <th style="padding:10px; text-align:center;">Required</th>
                        <th style="padding:10px; text-align:center;">Pre-Loaded</th>
                        <th style="padding:10px; text-align:center; background:#e2e8f0;">Final Loaded</th>
                        <th style="padding:10px; text-align:center;">Variance</th>
                        <th style="padding:10px; text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        `;

        let reconciliationPanels = '';
        let hasAnyVariance = false;

        Object.values(currentVarianceState).forEach(item => {
            if (item.variance === 0) return;
            hasAnyVariance = true;

            let invoiceRows = '';
            let currentTotal = 0;

            item.invoices.forEach((inv, index) => {
                currentTotal += inv.quantity;
                invoiceRows += `
                    <div style="display:grid; grid-template-columns:1.5fr 1fr 1fr 1.2fr; gap:10px; align-items:center; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <div style="font-size:12px; font-weight:500;">
                            📄 <strong>${inv.invoice_number}</strong><br>
                            <span style="font-size:10px; color:#64748b;">${inv.customer_name}</span>
                        </div>
                        <div style="text-align:center; font-family:monospace;">
                            Original: <strong>${inv.original_qty}</strong>
                        </div>
                        <div style="text-align:center;">
                            <input type="number" step="1" min="0" value="${inv.quantity}" 
                                   oninput="updateInvoiceAllocation(${item.item_id}, ${inv.invoice_id}, this.value)" 
                                   style="width:70px; padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; text-align:center; font-weight:bold; font-family:monospace;" />
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:11px; color:#64748b;">Rs. ${(inv.quantity * inv.unit_price).toFixed(2)}</span>
                        </div>
                    </div>
                `;
            });

            if (item.invoices.length === 0) {
                invoiceRows = `<p style="color:#64748b; font-size:12px; margin:10px 0; font-style:italic;">No invoices contain this product on this route.</p>`;
            }

            const unbalancedVal = item.final_loaded_qty - currentTotal;
            const panelStatus = (Math.abs(unbalancedVal) < 0.01)
                ? `<span style="color:#16a34a; font-weight:bold;">✔ Balanced</span>`
                : `<span style="color:#dc2626; font-weight:bold;">⚠️ Unbalanced (${unbalancedVal > 0 ? '+' : ''}${unbalancedVal.toFixed(1)} pcs)</span>`;

            reconciliationPanels += `
                <div style="border:1px solid #cbd5e1; border-radius:8px; padding:15px; margin-bottom:20px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
                        <div>
                            <strong style="font-size:13px; color:#0f172a;">🛠️ Reconcile: ${item.item_name}</strong><br>
                            <span style="font-size:11px; color:#475569;">Variance: <strong>${item.variance > 0 ? '+' : ''}${item.variance}</strong> | Required: <strong>${item.required_qty}</strong> | Final Loaded: <strong>${item.final_loaded_qty}</strong></span>
                        </div>
                        <div style="text-align:right;">
                            <button onclick="autoDistributeVariance(${item.item_id})" style="padding:4px 10px; background:#3b82f6; color:#fff; border:none; border-radius:4px; font-size:11px; font-weight:bold; cursor:pointer; margin-right:10px;">⚡ Auto-Distribute</button>
                            ${panelStatus}
                        </div>
                    </div>
                    <div>
                        ${invoiceRows}
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:10px; font-size:11px; color:#475569; background:#f8fafc; padding:8px; border-radius:4px;">
                        <span>Allocated: <strong style="font-family:monospace; font-size:12px;">${currentTotal.toFixed(1)}</strong></span>
                        <span>Remaining to Allocate: <strong style="font-family:monospace; font-size:12px; color:${unbalancedVal !== 0 ? '#dc2626' : '#16a34a'};">${unbalancedVal.toFixed(1)}</strong></span>
                    </div>
                </div>
            `;
        });

        if (!hasAnyVariance) {
            reconciliationPanels = `
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px; text-align:center; color:#166534; font-weight:bold; margin-bottom:20px;">
                    🎉 All items are fully balanced! No billing adjustments required.
                </div>
            `;
        }

        html += `
            <h5 style="margin:0 0 10px 0; font-size:13px; color:#475569; text-transform:uppercase; font-weight:bold;">⚖️ Bill Reconciliation Engine</h5>
            ${reconciliationPanels}
        `;

        box.innerHTML = html;

        const submitBtn = document.querySelector("#ssec-VarianceAdjustment button[onclick*='Finalizing']");
        if (submitBtn) {
            if (hasUnbalanced) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.title = "Please balance all product variances across invoices before proceeding.";
                submitBtn.setAttribute('onclick', "alert('Please balance all product variances across invoices before proceeding.');");
            } else {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.title = "";
                submitBtn.setAttribute('onclick', 'submitVarianceAdjustments()');
            }
        }
    }

    function updateInvoiceAllocation(itemId, invoiceId, value) {
        let qty = parseFloat(value);
        if (isNaN(qty) || qty < 0) {
            qty = 0;
        }
        
        const item = currentVarianceState[itemId];
        if (item) {
            const inv = item.invoices.find(i => i.invoice_id === invoiceId);
            if (inv) {
                inv.quantity = qty;
            }
        }
        renderVarianceReconciliation();
    }

    function autoDistributeVariance(itemId) {
        const item = currentVarianceState[itemId];
        if (!item || item.invoices.length === 0) return;

        let targetTotal = item.final_loaded_qty;
        let originalTotal = item.required_qty;
        let diff = targetTotal - originalTotal;

        if (diff === 0) {
            item.invoices.forEach(inv => {
                inv.quantity = inv.original_qty;
            });
        } else if (diff < 0) {
            let shortageToDeduct = Math.abs(diff);
            item.invoices.forEach(inv => {
                if (shortageToDeduct <= 0) return;
                if (inv.quantity >= shortageToDeduct) {
                    inv.quantity -= shortageToDeduct;
                    shortageToDeduct = 0;
                } else {
                    shortageToDeduct -= inv.quantity;
                    inv.quantity = 0;
                }
            });
        } else {
            item.invoices[0].quantity += diff;
        }

        renderVarianceReconciliation();
    }

    function submitVarianceAdjustments() {
        if (!currentRouteId) return;

        const adjustments = [];
        Object.values(currentVarianceState).forEach(item => {
            if (item.variance === 0) return;
            
            const invoiceAdjustments = item.invoices.map(inv => ({
                invoice_id: inv.invoice_id,
                new_qty: inv.quantity
            }));

            adjustments.push({
                item_id: item.item_id,
                invoice_adjustments: invoiceAdjustments
            });
        });

        if (adjustments.length > 0 && !confirm('Are you sure you want to approve these variance adjustments and update invoice billing? This action will modify invoice line quantities.')) {
            return;
        }

        const payload = {
            route_id: currentRouteId,
            adjustments: adjustments
        };

        fetch('<?= APP_URL ?>/RepTracking/api_adjust_variance_billing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadRouteDetails(currentRouteId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An unexpected error occurred during submission.');
        });
    }

    function printLoadingSheet(type) {
        if (!currentRouteId) return;
        window.open('<?= APP_URL ?>/RepTracking/print_loading/' + currentRouteId + '?type=' + type, '_blank');
    }

    let currentCollectionsState = [];

    function loadFinalizingStage(routeId) {
        const d = document.getElementById('route_data_' + routeId);
        const delId = d.getAttribute('data-delivery-id');

        document.getElementById('settleVerifyStock').checked = false;
        document.getElementById('settleStockTableBody').innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading...</td></tr>';
        
        document.getElementById('settleSubmitBtn').disabled = true;
        document.getElementById('settleSubmitBtn').style.opacity = '0.5';
        document.getElementById('settleSubmitBtn').style.cursor = 'not-allowed';
        document.getElementById('settleStatusText').innerHTML = 'Please approve all collections and verify Return stock counts to unlock Finalization.';

        // Fetch collections verification
        loadCollectionsVerification(routeId);

        fetch('<?= APP_URL ?>/RepTracking/api_get_delivery_details/' + delId)
            .then(res => res.json())
            .then(data => {
                currentDeliveryDetails = data;

                // Pre-populate logistics fields if set
                if (data.delivery) {
                    document.getElementById('settleDaVehicle').value = data.delivery.vehicle_number || '';
                    document.getElementById('settleDaDriver').value = data.delivery.driver_name || '';
                    document.getElementById('settleDaPartner').value = data.delivery.partner_name || '';
                }
                
                // Render Stock/returns
                const stockTbody = document.getElementById('settleStockTableBody');
                stockTbody.innerHTML = '';
                if (!data.balancing.stock_items || data.balancing.stock_items.length === 0) {
                    stockTbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#888;">No stock items loaded.</td></tr>';
                } else {
                    data.balancing.stock_items.forEach(st => {
                        stockTbody.innerHTML += `
                            <tr>
                                <td><strong>${st.item_name}</strong></td>
                                <td style="text-align:center;">${parseInt(st.loaded_qty)}</td>
                                <td style="text-align:center; color:#2e7d32;">${parseInt(st.delivered_qty)}</td>
                                <td style="text-align:right;">
                                    <input type="number" class="actual-returned-input" 
                                           data-name="${st.item_name}" data-item-id="${st.item_id}" data-var-id="${st.variation_option_id || 0}"
                                           data-loaded="${st.loaded_qty}" data-delivered="${st.delivered_qty}" 
                                           value="${parseInt(st.remaining_qty)}" min="0" style="width:60px; text-align:right; padding:3px;">
                                </td>
                            </tr>
                        `;
                    });
                }

                // Render double-entries mappings
                renderSettleDoubleEntries();
            });
    }

    function loadCollectionsVerification(routeId) {
        const tbody = document.getElementById('settleCollectionsTableBody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading collections... ⏳</td></tr>';
        currentCollectionsState = [];

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_collections/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    currentCollectionsState = data.collections || [];
                    renderCollectionsVerification();
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Failed to load collections.</td></tr>';
                }
            });
    }

    function renderCollectionsVerification() {
        const tbody = document.getElementById('settleCollectionsTableBody');
        tbody.innerHTML = '';

        if (currentCollectionsState.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#888; padding: 10px;">No payments collected on this route.</td></tr>';
            checkSettleVerification();
            return;
        }

        currentCollectionsState.forEach((col, index) => {
            const isVerified = parseInt(col.is_verified) === 1;
            const isFlagged = parseInt(col.is_flagged) === 1;
            
            let statusSelect = `
                <select onchange="updateCollectionStatus(${index}, this.value)" style="padding:4px; border:1px solid #ccc; border-radius:4px; font-size:11px; font-weight:bold; color:${isVerified ? '#16a34a' : (isFlagged ? '#dc2626' : '#475569')};">
                    <option value="pending" ${(!isVerified && !isFlagged) ? 'selected' : ''} style="color:#475569;">Pending</option>
                    <option value="approved" ${isVerified ? 'selected' : ''} style="color:#16a34a;">Approved</option>
                    <option value="flagged" ${isFlagged ? 'selected' : ''} style="color:#dc2626;">Flagged</option>
                </select>
            `;

            const adjustedVal = col.adjusted_amount !== null ? col.adjusted_amount : col.amount;

            tbody.innerHTML += `
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:6px 4px;">
                        <strong>${col.customer_name}</strong><br>
                        <span style="font-size:10px; color:#64748b;">${col.payment_method} ${col.reference ? '(' + col.reference + ')' : ''}</span>
                    </td>
                    <td style="padding:6px 4px; text-align:right; font-family:monospace; font-weight:bold;">
                        Rs ${parseFloat(col.amount).toFixed(2)}
                    </td>
                    <td style="padding:6px 4px; text-align:center;">
                        ${statusSelect}
                    </td>
                    <td style="padding:6px 4px; text-align:center;">
                        <input type="number" step="0.01" min="0" value="${parseFloat(adjustedVal).toFixed(2)}"
                               oninput="updateCollectionAdjustedAmount(${index}, this.value)"
                               style="width:80px; padding:3px; border:1px solid #cbd5e1; border-radius:4px; text-align:right; font-family:monospace; font-size:11px;" />
                    </td>
                    <td style="padding:6px 4px; text-align:center;">
                        <input type="text" value="${col.verification_notes || ''}" placeholder="Notes"
                               oninput="updateCollectionNotes(${index}, this.value)"
                               style="width:100px; padding:3px; border:1px solid #cbd5e1; border-radius:4px; font-size:11px;" />
                    </td>
                </tr>
            `;
        });

        checkSettleVerification();
    }

    function updateCollectionStatus(index, val) {
        const col = currentCollectionsState[index];
        if (col) {
            if (val === 'approved') {
                col.is_verified = 1;
                col.is_flagged = 0;
            } else if (val === 'flagged') {
                col.is_verified = 0;
                col.is_flagged = 1;
            } else {
                col.is_verified = 0;
                col.is_flagged = 0;
            }
        }
        renderCollectionsVerification();
    }

    function updateCollectionAdjustedAmount(index, val) {
        const col = currentCollectionsState[index];
        if (col) {
            col.adjusted_amount = val !== '' ? parseFloat(val) : null;
        }
        checkSettleVerification();
    }

    function updateCollectionNotes(index, val) {
        const col = currentCollectionsState[index];
        if (col) {
            col.verification_notes = val;
        }
    }

    function saveCollectionsVerification() {
        if (!currentRouteId) return;

        const updates = currentCollectionsState.map(col => ({
            id: col.id,
            is_verified: col.is_verified,
            is_flagged: col.is_flagged,
            adjusted_amount: col.adjusted_amount !== null ? parseFloat(col.adjusted_amount) : parseFloat(col.amount),
            verification_notes: col.verification_notes
        }));

        fetch('<?= APP_URL ?>/RepTracking/api_verify_collections', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ updates: updates })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadCollectionsVerification(currentRouteId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while saving verification.');
        });
    }

    function checkSettleVerification() {
        const verifyStock = document.getElementById('settleVerifyStock').checked;

        const btn = document.getElementById('settleSubmitBtn');
        const text = document.getElementById('settleStatusText');

        let allCollectionsApproved = true;
        let pendingOrFlaggedCount = 0;
        currentCollectionsState.forEach(col => {
            if (parseInt(col.is_verified) !== 1) {
                allCollectionsApproved = false;
                pendingOrFlaggedCount++;
            }
        });

        if (allCollectionsApproved && verifyStock) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            text.innerHTML = '<span style="color:#2e7d32; font-weight:bold;">Verification Complete!</span> Ready to settle balancing.';
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            
            let msg = '';
            if (!allCollectionsApproved) {
                msg += `Please approve/verify all collections (${pendingOrFlaggedCount} remaining). `;
            }
            if (!verifyStock) {
                msg += 'Please verify Returned Stock count.';
            }
            text.innerHTML = `<span style="color:#dc2626; font-weight:bold;">Locked:</span> ${msg}`;
        }
    }

    let settleActiveDeTab = 'collections';
    function switchSettleDeTab(tab) {
        settleActiveDeTab = tab;
        document.getElementById('settleDeTabCollectionsBtn').classList.toggle('active', tab === 'collections');
        document.getElementById('settleDeTabSalesBtn').classList.toggle('active', tab === 'sales');
        document.getElementById('settleDeCollectionsContainer').style.display = tab === 'collections' ? 'block' : 'none';
        document.getElementById('settleDeSalesContainer').style.display = tab === 'sales' ? 'block' : 'none';
    }

    function renderSettleDeAccountSelect(id, type, selectedCode) {
        let optionsHtml = '';
        globalAllAccounts.forEach(acc => {
            let isSel = acc.account_code === selectedCode ? 'selected' : '';
            optionsHtml += `<option value="${acc.id}" ${isSel}>${acc.account_code} - ${acc.account_name}</option>`;
        });
        return `<select class="settle-de-select" data-id="${id}" data-type="${type}" style="padding:4px 8px; font-size:12px; border-radius:4px; border:1px solid #ccc; width:100%;">${optionsHtml}</select>`;
    }

    function renderSettleDoubleEntries() {
        const colContainer = document.getElementById('settleDeCollectionsContainer');
        const salesContainer = document.getElementById('settleDeSalesContainer');
        
        colContainer.innerHTML = '';
        salesContainer.innerHTML = '';

        const payments = currentDeliveryDetails.balancing.payments || [];
        const invoices = currentDeliveryDetails.invoices || [];

        // 1. Collections
        if (payments.length === 0) {
            colContainer.innerHTML = '<p style="color:#888; text-align:center;">No payments logged on this trip.</p>';
        } else {
            payments.forEach(p => {
                let defaultDebitCode = '1000'; // Cash
                if (p.payment_method === 'Cheque') { defaultDebitCode = '1010'; }
                else if (p.payment_method === 'Bank Transfer') { defaultDebitCode = '1605'; }

                colContainer.innerHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#fafafa; border:1px solid #eee; padding:10px; margin-bottom:8px; border-radius:6px; flex-wrap:wrap; gap:10px;">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-weight:bold;">
                                <input type="checkbox" class="settle-payment-chk" value="${p.id}" checked>
                                ${p.customer_name} (${p.payment_method})
                            </label>
                        </div>
                        <div style="font-weight:bold; color:#2e7d32;">Rs ${parseFloat(p.amount).toFixed(2)}</div>
                        <div style="display:flex; gap:10px; flex:2;">
                            <div style="flex:1;">
                                <span style="font-size:9px; color:#666;">Debit Account</span>
                                ${renderSettleDeAccountSelect(p.id, 'debit', defaultDebitCode)}
                            </div>
                            <div style="flex:1;">
                                <span style="font-size:9px; color:#666;">Credit Account</span>
                                ${renderSettleDeAccountSelect(p.id, 'credit', '1090')}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // 2. Sales
        const deliveredInvoices = invoices.filter(inv => inv.delivery_status === 'Delivered');
        if (deliveredInvoices.length === 0) {
            salesContainer.innerHTML = '<p style="color:#888; text-align:center;">No delivered sales invoices on this trip.</p>';
        } else {
            deliveredInvoices.forEach(inv => {
                salesContainer.innerHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#fafafa; border:1px solid #eee; padding:10px; margin-bottom:8px; border-radius:6px; flex-wrap:wrap; gap:10px;">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:flex; align-items:center; gap:8px; font-weight:bold;">
                                <input type="checkbox" class="settle-invoice-chk" value="${inv.id}" checked>
                                ${inv.invoice_number} (${inv.customer_name})
                            </label>
                        </div>
                        <div style="font-weight:bold; color:#0066cc;">Rs ${parseFloat(inv.true_grand_total).toFixed(2)}</div>
                        <div style="display:flex; gap:10px; flex:2;">
                            <div style="flex:1;">
                                <span style="font-size:9px; color:#666;">Debit Account (AR)</span>
                                ${renderSettleDeAccountSelect(inv.id, 'debit', '1090')}
                            </div>
                            <div style="flex:1;">
                                <span style="font-size:9px; color:#666;">Credit Account (Sales)</span>
                                ${renderSettleDeAccountSelect(inv.id, 'credit', '3000')}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    }

    function submitFinalSettle() {
        const vehicle = document.getElementById('settleDaVehicle').value;
        const driver = document.getElementById('settleDaDriver').value;
        const partner = document.getElementById('settleDaPartner').value;

        if (!vehicle) { alert("Please select a Vehicle Number."); return; }
        if (!driver) { alert("Please select a Driver Name."); return; }

        if (!confirm("Are you sure you want to FINALIZE and SETTLE this delivery route?\n\nThis will post all selected collections to GL and update inventory.")) {
            return;
        }

        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d.getAttribute('data-delivery-id');

        const selectedPaymentIds = [];
        document.querySelectorAll('.settle-payment-chk:checked').forEach(cb => {
            selectedPaymentIds.push(parseInt(cb.value));
        });

        const selectedInvoiceIds = [];
        document.querySelectorAll('.settle-invoice-chk:checked').forEach(cb => {
            selectedInvoiceIds.push(parseInt(cb.value));
        });

        const debitAccounts = {};
        const creditAccounts = {};
        document.querySelectorAll('.settle-de-select').forEach(sel => {
            const id = sel.getAttribute('data-id');
            const type = sel.getAttribute('data-type');
            const val = parseInt(sel.value);
            if (type === 'debit') { debitAccounts[id] = val; } else { creditAccounts[id] = val; }
        });

        const returnedItems = [];
        document.querySelectorAll('.actual-returned-input').forEach(input => {
            returnedItems.push({
                item_name: input.getAttribute('data-name'),
                item_id: parseInt(input.getAttribute('data-item-id') || 0),
                variation_option_id: parseInt(input.getAttribute('data-var-id') || 0),
                loaded_qty: parseFloat(input.getAttribute('data-loaded') || 0),
                delivered_qty: parseFloat(input.getAttribute('data-delivered') || 0),
                actual_returned_qty: parseFloat(input.value || 0)
            });
        });

        const btn = document.getElementById('settleSubmitBtn');
        btn.disabled = true;
        btn.innerText = 'Settling Route... ⏳';

        fetch('<?= APP_URL ?>/RepTracking/finalize', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                delivery_id: parseInt(delId),
                selected_payment_ids: selectedPaymentIds,
                selected_invoice_ids: selectedInvoiceIds,
                debit_accounts: debitAccounts,
                credit_accounts: creditAccounts,
                returned_items: returnedItems,
                vehicle_number: vehicle,
                driver_name: driver,
                partner_name: partner
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Settle balancing successfully completed! Route marked as Completed.");
                window.location.reload();
            } else {
                alert("⚠️ Error: " + data.message);
                btn.disabled = false;
                btn.innerText = '⚖️ Settle Balancing & Finalize Route';
                checkSettleVerification();
            }
        });
    }

    function loadCompletedStage(routeId) {
        loadActiveStageBills(routeId);
        switchCompletedTab('invoices');
    }

    let completedActiveTab = 'invoices';
    function switchCompletedTab(tab) {
        completedActiveTab = tab;
        document.getElementById('compTabInvoicesBtn').classList.toggle('active', tab === 'invoices');
        document.getElementById('compTabCollectionsBtn').classList.toggle('active', tab === 'collections');
        document.getElementById('compTabVariancesBtn').classList.toggle('active', tab === 'variances');

        document.getElementById('completedInvoicesTab').style.display = tab === 'invoices' ? 'block' : 'none';
        document.getElementById('completedCollectionsTab').style.display = tab === 'collections' ? 'block' : 'none';
        document.getElementById('completedVariancesTab').style.display = tab === 'variances' ? 'block' : 'none';

        if (tab === 'collections') {
            const container = document.getElementById('completedCollectionsTab');
            container.innerHTML = 'Loading settled collections... ⏳';
            fetch('<?= APP_URL ?>/RepTracking/api_get_route_collections/' + currentRouteId)
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = '';
                    const colls = data.collections || [];
                    if (colls.length === 0) {
                        container.innerHTML = '<p style="color:#888; text-align:center; padding:20px;">No collections found for this route.</p>';
                        return;
                    }
                    colls.forEach(c => {
                        let isPosted = c.is_posted === '1' || c.status === 'Posted';
                        let statText = isPosted ? '<span style="color:#2e7d32; font-weight:bold;">Posted</span>' : '<span style="color:#ef6c00; font-weight:bold;">Pending GL</span>';
                        container.innerHTML += `
                            <div style="background:#fafafa; border:1px solid #eee; padding:10px; margin-bottom:8px; border-radius:6px; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong>${c.customer_name}</strong> (${c.payment_method})<br>
                                    <span style="font-size:11px; color:#888;">Ref: ${c.reference || '-'} | Date: ${c.payment_date}</span>
                                </div>
                                <div style="text-align:right;">
                                    <strong style="color:#2e7d32;">Rs ${parseFloat(c.amount).toFixed(2)}</strong><br>
                                    <span style="font-size:11px;">Status: ${statText}</span>
                                </div>
                            </div>
                        `;
                    });
                });
        } else if (tab === 'variances') {
            const container = document.getElementById('completedVariancesTab');
            container.innerHTML = 'Loading variances... ⏳';
            fetch('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + currentRouteId)
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.status !== 'success' || !data.deliveries || data.deliveries.length === 0) {
                        container.innerHTML = '<p style="color:#888; text-align:center; padding:20px;">No variance logs found.</p>';
                        return;
                    }
                    const del = data.deliveries[0];
                    let listHtml = '';
                    del.items.forEach(item => {
                        let varColor = '#000';
                        let varText = '0';
                        if (item.variance < 0) { varColor = '#c62828'; varText = `${item.variance} (Short)`; }
                        else if (item.variance > 0) { varColor = '#ef6c00'; varText = `+${item.variance} (Over)`; }
                        else { varColor = '#2e7d32'; varText = 'Match'; }
                        listHtml += `
                            <tr>
                                <td>${item.item_name}</td>
                                <td style="text-align:center;">${item.required_qty}</td>
                                <td style="text-align:center;">${item.pre_loaded_qty}</td>
                                <td style="text-align:center;">${item.final_loaded_qty || '-'}</td>
                                <td style="text-align:center; color:${varColor}; font-weight:bold;">${varText}</td>
                            </tr>
                        `;
                    });
                    container.innerHTML = `
                        <table class="data-table">
                            <thead>
                                <tr><th>Product Name</th><th style="text-align:center;">Required</th><th style="text-align:center;">Pre-Loaded</th><th style="text-align:center;">Final Dispatch</th><th style="text-align:center;">Variance</th></tr>
                            </thead>
                            <tbody>${listHtml}</tbody>
                        </table>
                    `;
                });
        }
    }

    function printBalancingReport() {
        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d.getAttribute('data-delivery-id');
        if (delId) { window.open('<?= APP_URL ?>/RepTracking/balancing_report/' + delId, '_blank'); }
    }

    function printLoadingSheetSpreadsheet() {
        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d.getAttribute('data-delivery-id');
        if (delId) { window.open('<?= APP_URL ?>/RepTracking/spreadsheet/' + delId, '_blank'); }
    }

    function exportCSV() {
        const d = document.getElementById('route_data_' + currentRouteId);
        const delId = d.getAttribute('data-delivery-id');
        if (delId) { window.location.href = '<?= APP_URL ?>/RepTracking/export_csv/' + delId; }
    }

    function getDataTypeFromStatus(status) {
        if (status === 'Active') return 'active';
        if (status === 'Pending GL') return 'pending_gl';
        if (status === 'Adjustments') return 'adjustments';
        if (status === 'Pending Loading') return 'pending_loading';
        if (status === 'Final Loading') return 'final_loading';
        if (status === 'Variance Adjustment') return 'variance';
        if (status === 'Finalizing') return 'finalizing';
        return 'completed';
    }

    function advanceRouteStatus(targetStatus) {
        if (!confirm(`Are you sure you want to advance this route to "${targetStatus}" stage?`)) {
            return;
        }
        fetch('<?= APP_URL ?>/RepTracking/api_update_route_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: currentRouteId, status: targetStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(`🎉 Route advanced to ${targetStatus}`);
                const filterType = getDataTypeFromStatus(targetStatus);
                window.location.href = window.location.pathname + `?route_id=${currentRouteId}&filter=${filterType}`;
            } else {
                alert("⚠️ Error: " + data.message);
            }
        });
    }

    function redirectToAddInvoice() {
        if (currentRouteId) {
            window.location.href = '<?= APP_URL ?>/sales/create?type=sales_order&route_id=' + currentRouteId;
        }
    }

    function openInvoiceSlider(invoiceId) {
        const slider = document.getElementById('invoiceSlider');
        const iframe = document.getElementById('invoiceIframe');
        document.getElementById('btnEditInvoice').href = '<?= APP_URL ?>/sales/edit/' + invoiceId + '?type=sales_order';
        document.getElementById('btnDeleteInvoice').setAttribute('data-invoice-id', invoiceId);
        iframe.src = '<?= APP_URL ?>/sales/show/' + invoiceId;
        slider.classList.add('open');
    }

    function closeInvoiceSlider() {
        document.getElementById('invoiceSlider').classList.remove('open');
        setTimeout(() => { document.getElementById('invoiceIframe').src = 'about:blank'; }, 300);
    }

    function deleteSalesOrder() {
        const invoiceId = document.getElementById('btnDeleteInvoice').getAttribute('data-invoice-id');
        if (!invoiceId) return;
        if (!confirm("Are you sure you want to delete this Sales Order? This will release reserved stock back to inventory and cannot be undone.")) {
            return;
        }
        fetch('<?= APP_URL ?>/RepTracking/api_delete_sales_order/' + invoiceId, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Success: " + data.message);
                closeInvoiceSlider();
                loadRouteDetails(currentRouteId);
            } else {
                alert("⚠️ Error: " + data.message);
            }
        });
    }

    // --- GPS Path Map Handlers ---
    function openMapModal() {
        document.getElementById('mapModalBackdrop').style.display = 'flex';
        loadRoutePath(currentRouteId);
    }

    function closeMapModal() {
        document.getElementById('mapModalBackdrop').style.display = 'none';
    }

    function initRoutePathMap() {
        if (routeMap !== null) return;
        routeMap = L.map('routePathMap').setView([7.8731, 80.7718], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(routeMap);
    }

    function clearRoutePathMap() {
        routeMapLayers.forEach(layer => routeMap.removeLayer(layer));
        routeMapLayers = [];
    }

    function loadRoutePath(routeId) {
        document.getElementById('mapEmptyOverlay').style.display = 'flex';
        document.getElementById('mapEmptyOverlay').innerText = 'Loading route path...';
        document.getElementById('pathStepList').style.display = 'none';

        initRoutePathMap();
        clearRoutePathMap();

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_path/' + routeId)
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success' || !data.path) {
                    document.getElementById('mapEmptyOverlay').innerText = 'Could not load route path.';
                    return;
                }
                renderRoutePath(data.path);
            })
            .catch(() => {
                document.getElementById('mapEmptyOverlay').innerText = 'Failed to load route path.';
            });
    }

    function renderRoutePath(path) {
        const wps = path.waypoints || [];
        document.getElementById('pathPointCount').innerText = wps.length ? `(${wps.length} points)` : '(no GPS)';
        document.getElementById('modalRouteName').innerText = path.route_name || '';

        const stepOl = document.getElementById('pathStepOl');
        stepOl.innerHTML = '';

        if (wps.length === 0) {
            document.getElementById('mapEmptyOverlay').style.display = 'flex';
            document.getElementById('mapEmptyOverlay').innerHTML = 'No GPS points recorded for this route.';
            document.getElementById('pathStepList').style.display = 'none';
            setTimeout(() => routeMap.invalidateSize(), 100);
            return;
        }

        document.getElementById('mapEmptyOverlay').style.display = 'none';
        document.getElementById('pathStepList').style.display = 'block';

        const latlngs = [];
        wps.forEach((wp) => {
            const latlng = [wp.lat, wp.lng];
            latlngs.push(latlng);

            let icon = pathBlueIcon;
            let stepClass = 'path-step-invoice';
            if (wp.type === 'start') { icon = pathGreenIcon; stepClass = 'path-step-start'; }
            else if (wp.type === 'end') { icon = pathRedIcon; stepClass = 'path-step-end'; }

            const marker = L.marker(latlng, { icon: icon }).addTo(routeMap);
            marker.bindPopup(`<strong>${wp.name}</strong><br>${wp.description}<br><span style="font-size:10px; color:#666;">${wp.time}</span>`);
            routeMapLayers.push(marker);

            stepOl.innerHTML += `<li class="${stepClass}"><strong>${wp.time}</strong> - ${wp.name} (${wp.description})</li>`;
        });

        if (latlngs.length > 1) {
            const polyline = L.polyline(latlngs, { color: '#0066cc', weight: 4, opacity: 0.7 }).addTo(routeMap);
            routeMapLayers.push(polyline);
            routeMap.fitBounds(polyline.getBounds(), { padding: [30, 30] });
        } else {
            routeMap.setView(latlngs[0], 14);
        }

        setTimeout(() => routeMap.invalidateSize(), 100);
    }

    // --- Route Binding Handlers ---
    function getEligibleBindingRoutes() {
        const routes = [];
        document.querySelectorAll('.route-item').forEach(item => {
            if (item.getAttribute('data-route-type') === 'pending_gl') {
                const id = item.id.replace('route_', '');
                const dataDiv = document.getElementById('route_data_' + id);
                if (dataDiv) {
                    routes.push({ id: parseInt(id), name: dataDiv.getAttribute('data-rname'), rep: dataDiv.getAttribute('data-rep') });
                }
            }
        });
        return routes;
    }

    function openRouteBindingModal() {
        document.getElementById('rbBoundName').value = '';
        document.getElementById('rbSlotsContainer').innerHTML = '';
        rbSlotsCount = 0;
        addBindingSlot();
        addBindingSlot();
        document.getElementById('routeBindingModal').style.display = 'flex';
    }

    function closeRouteBindingModal() {
        document.getElementById('routeBindingModal').style.display = 'none';
    }

    function addBindingSlot() {
        rbSlotsCount++;
        const index = rbSlotsCount;
        const eligibleRoutes = getEligibleBindingRoutes();
        
        let optionsHtml = '<option value="">-- Choose Route --</option>';
        eligibleRoutes.forEach(r => {
            optionsHtml += `<option value="${r.id}">${r.name} (Rep: ${r.rep})</option>`;
        });
        
        const slotHtml = `
            <div class="rb-slot-column" id="rb_slot_col_${index}" style="position: relative;">
                ${index > 2 ? `<button type="button" onclick="removeBindingSlot(${index})" style="position: absolute; top: 10px; right: 10px; border: none; background: #dc2626; color: #fff; width: 22px; height: 22px; border-radius: 50%; cursor: pointer; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: bold; padding:0;">✕</button>` : ''}
                <h5 style="margin: 0 0 5px 0; color: #3f51b5; font-size: 12px; font-weight: bold; text-transform: uppercase;">Slot ${index}</h5>
                <div class="rb-slot-box">
                    <div style="font-size: 20px; color: #cbd5e1; margin-bottom: 6px;" id="rb_slot_icon_${index}">➕</div>
                    <select class="rb-slot-select" id="rb_select_${index}" onchange="onBindingSlotRouteSelect(${index}, this)">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="rb-bill-list" id="rb_bills_${index}"></div>
            </div>
        `;
        document.getElementById('rbSlotsContainer').insertAdjacentHTML('beforeend', slotHtml);
    }

    function removeBindingSlot(index) {
        const el = document.getElementById(`rb_slot_col_${index}`);
        if (el) el.remove();
    }

    function onBindingSlotRouteSelect(index, select) {
        const routeId = select.value;
        const billsContainer = document.getElementById(`rb_bills_${index}`);
        const icon = document.getElementById(`rb_slot_icon_${index}`);
        
        if (!routeId) {
            billsContainer.style.display = 'none';
            billsContainer.innerHTML = '';
            icon.innerText = '➕';
            return;
        }
        
        icon.innerText = '🔗';
        billsContainer.style.display = 'block';
        billsContainer.innerHTML = '<p style="text-align: center; color: #888;">Loading bills... ⏳</p>';
        
        fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.bills || data.bills.length === 0) {
                    billsContainer.innerHTML = '<p style="text-align: center; color: #888;">No sales orders in this route.</p>';
                    return;
                }
                
                let html = '<div style="font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 6px; font-size: 11px; text-transform: uppercase; color: #666;">Sales Orders</div>';
                data.bills.forEach(b => {
                    let trueTotal = parseFloat(b.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    html += `
                        <div class="rb-bill-item">
                            <span><strong>${b.invoice_number}</strong> (${b.customer_name})</span>
                            <strong style="font-family: monospace; color: #2e7d32;">Rs ${trueTotal}</strong>
                        </div>
                    `;
                });
                billsContainer.innerHTML = html;
            });
    }

    function submitRouteBinding() {
        const boundName = document.getElementById('rbBoundName').value.trim();
        if (!boundName) { alert("Please enter a custom name for the bound route."); return; }
        
        const routeIds = [];
        document.querySelectorAll('.rb-slot-select').forEach(select => {
            if (select.value) { routeIds.push(parseInt(select.value)); }
        });
        
        const uniqueRouteIds = [...new Set(routeIds)];
        if (uniqueRouteIds.length < 2) { alert("Please select at least 2 distinct routes to bind."); return; }
        if (uniqueRouteIds.length !== routeIds.length) { alert("Please make sure you do not select the same route in multiple slots."); return; }
        
        if (!confirm(`Are you sure you want to bind these ${uniqueRouteIds.length} routes together under "${boundName}"?`)) { return; }
        
        fetch('<?= APP_URL ?>/RepTracking/api_create_binding', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ binding_name: boundName, route_ids: uniqueRouteIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Success: " + data.message);
                closeRouteBindingModal();
                window.location.reload();
            } else {
                alert("⚠️ Error: " + data.message);
            }
        });
    }

    function unbindActiveRoute() {
        const btnUnbind = document.getElementById('btnUnbindRoute');
        const bindingId = btnUnbind ? btnUnbind.getAttribute('data-binding-id') : null;
        if (!bindingId) { alert("No active route binding identified."); return; }

        if (!confirm("Are you sure you want to undo this route binding? The routes will be separated back to their original states and listed individually.")) { return; }

        fetch('<?= APP_URL ?>/RepTracking/api_unbind_route', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ binding_id: parseInt(bindingId) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Success: " + data.message);
                window.location.reload();
            } else {
                alert("⚠️ Error: " + data.message);
            }
        });
    }

    // --- Attach Invoice Modal Handlers ---
    function openAttachInvoiceModal() {
        document.getElementById('invoiceSearchInput').value = '';
        document.getElementById('soFilterStartDate').value = '';
        document.getElementById('soFilterEndDate').value = '';
        document.getElementById('soFilterStatus').value = '';
        document.getElementById('unattachedInvoicesContainer').innerHTML = '<p style="text-align: center; color: #888;">Type search text or modify filters to query unattached sales orders...</p>';
        document.getElementById('attachInvoiceModal').style.display = 'flex';
    }

    function closeAttachInvoiceModal() {
        document.getElementById('attachInvoiceModal').style.display = 'none';
    }

    function searchUnattachedInvoices() {
        const query = document.getElementById('invoiceSearchInput').value;
        const startDate = document.getElementById('soFilterStartDate').value;
        const endDate = document.getElementById('soFilterEndDate').value;
        const status = document.getElementById('soFilterStatus').value;
        const container = document.getElementById('unattachedInvoicesContainer');
        
        container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">Searching... ⏳</p>';
        
        let url = '<?= APP_URL ?>/RepTracking/api_get_unattached_invoices?search=' + encodeURIComponent(query);
        if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
        if (endDate) url += '&end_date=' + encodeURIComponent(endDate);
        if (status) url += '&status=' + encodeURIComponent(status);
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.invoices || data.invoices.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">No unattached sales orders found.</p>';
                    return;
                }
                
                let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';
                data.invoices.forEach(inv => {
                    let amtFormatted = parseFloat(inv.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    html += `
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 0;">
                            <input type="checkbox" class="attach-invoice-checkbox" value="${inv.id}" style="width: 16px; height: 16px;">
                            <div style="flex: 1;">
                                <div style="font-weight: bold; color: #333;">${inv.invoice_number} <span style="font-size: 10px; font-weight: bold; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">${inv.status}</span></div>
                                <div style="font-size: 11px; color: #666;">Customer: <strong>${inv.customer_name}</strong> | Date: ${inv.invoice_date}</div>
                            </div>
                            <div style="font-weight: bold; font-family: monospace; color: #2e7d32;">Rs ${amtFormatted}</div>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            });
    }

    function confirmAttachInvoices() {
        const checkedInvoices = [];
        document.querySelectorAll('.attach-invoice-checkbox:checked').forEach(cb => {
            checkedInvoices.push(parseInt(cb.value));
        });
        
        if (checkedInvoices.length === 0) { alert("Please select at least one sales order to attach."); return; }
        
        closeAttachInvoiceModal();
        
        fetch('<?= APP_URL ?>/RepTracking/api_attach_invoices', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: currentRouteId, invoice_ids: checkedInvoices })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Attached successfully!");
                loadRouteDetails(currentRouteId);
            } else {
                alert("⚠️ Error: " + data.message);
            }
        });
    }

    function resetSalesOrderFilters() {
        document.getElementById('invoiceSearchInput').value = '';
        document.getElementById('soFilterStartDate').value = '';
        document.getElementById('soFilterEndDate').value = '';
        document.getElementById('soFilterStatus').value = '';
        searchUnattachedInvoices();
    }

    // --- Credit Collections Stage 2 Verification JS ---
    let currentGLCollectionsState = [];

    function loadCollectionsVerificationStage2(routeId) {
        const tbody = document.getElementById('glCollectionsTableBody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading collections... ⏳</td></tr>';
        currentGLCollectionsState = [];
        
        document.getElementById('glTotalCash').innerText = 'Rs 0.00';
        document.getElementById('glTotalCheque').innerText = 'Rs 0.00';

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_collections/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    currentGLCollectionsState = data.collections || [];
                    renderGLCollectionsVerification();
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Failed to load collections.</td></tr>';
                }
            });
    }

    function getAccountIdByCode(code) {
        let acc = globalAllAccounts.find(a => a.account_code === code);
        if (!acc && code === '1605') {
            acc = globalAllAccounts.find(a => a.account_code === '1600');
        }
        return acc ? parseInt(acc.id) : null;
    }

    function buildAccountOptions(selectedId, defaultCode) {
        let optionsHtml = '<option value="">-- Choose Account --</option>';
        let selectedAccId = selectedId;
        if (!selectedAccId && defaultCode) {
            const defaultAcc = globalAllAccounts.find(a => a.account_code === defaultCode);
            if (defaultAcc) {
                selectedAccId = parseInt(defaultAcc.id);
            }
        }
        globalAllAccounts.forEach(acc => {
            const isSel = parseInt(acc.id) === parseInt(selectedAccId) ? 'selected' : '';
            optionsHtml += `<option value="${acc.id}" ${isSel}>${acc.account_code} - ${acc.account_name}</option>`;
        });
        return optionsHtml;
    }

    function renderGLCollectionsVerification() {
        const tbody = document.getElementById('glCollectionsTableBody');
        tbody.innerHTML = '';

        let cashSum = 0;
        let chequeSum = 0;

        if (currentGLCollectionsState.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#888; padding: 10px;">No payments collected on this route.</td></tr>';
            checkGLVerification();
            return;
        }

        currentGLCollectionsState.forEach((col, index) => {
            const isVerified = parseInt(col.is_verified) === 1;
            const isFinalized = col.status === 'Finalized';
            const amt = parseFloat(col.amount);
            
            if (col.payment_method === 'Cash') {
                cashSum += amt;
            } else {
                chequeSum += amt;
            }

            let statusSelect = `
                <input type="checkbox" onchange="updateGLCollectionApproval(${index}, this.checked)" 
                       ${(isVerified || isFinalized) ? 'checked' : ''} ${isFinalized ? 'disabled' : ''} 
                       style="width:16px; height:16px; cursor:pointer;" />
            `;

            const adjustedVal = col.adjusted_amount !== null ? col.adjusted_amount : col.amount;

            tbody.innerHTML += `
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:6px 4px;">
                        <strong>${col.customer_name}</strong><br>
                        <span style="font-size:10px; color:#64748b;">${col.payment_method} ${col.reference ? '(' + col.reference + ')' : ''}</span>
                        ${isFinalized ? '<br><span style="font-size:10px; color:#2e7d32; font-weight:bold;">Posted to GL</span>' : ''}
                    </td>
                    <td style="padding:6px 4px; text-align:right; font-family:monospace; font-weight:bold;">
                        Rs ${amt.toFixed(2)}
                    </td>
                    <td style="padding:6px 4px; text-align:center;">
                        ${statusSelect}
                    </td>
                    <td style="padding:6px 4px;">
                        <select onchange="updateGLCollectionDebitAccount(${index}, this.value)" 
                                style="width:100%; max-width:180px; padding:4px; font-size:11px; border:1px solid #cbd5e1; border-radius:4px;" 
                                ${isFinalized ? 'disabled' : ''}>
                            ${buildAccountOptions(col.debit_account_id, col.payment_method === 'Cash' ? '1000' : (col.payment_method === 'Cheque' ? '1010' : '1605'))}
                        </select>
                    </td>
                    <td style="padding:6px 4px;">
                        <select onchange="updateGLCollectionCreditAccount(${index}, this.value)" 
                                style="width:100%; max-width:180px; padding:4px; font-size:11px; border:1px solid #cbd5e1; border-radius:4px;" 
                                ${isFinalized ? 'disabled' : ''}>
                            ${buildAccountOptions(col.credit_account_id, '1200')}
                        </select>
                    </td>
                    <td style="padding:6px 4px; text-align:center;">
                        <input type="number" step="0.01" min="0" value="${parseFloat(adjustedVal).toFixed(2)}"
                               oninput="updateGLCollectionAdjustedAmount(${index}, this.value)"
                               ${isFinalized ? 'disabled' : ''}
                               style="width:80px; padding:3px; border:1px solid #cbd5e1; border-radius:4px; text-align:right; font-family:monospace; font-size:11px;" />
                    </td>
                    <td style="padding:6px 4px; text-align:center;">
                        <input type="text" value="${col.verification_notes || ''}" placeholder="Notes"
                               oninput="updateGLCollectionNotes(${index}, this.value)"
                               ${isFinalized ? 'disabled' : ''}
                               style="width:100px; padding:3px; border:1px solid #cbd5e1; border-radius:4px; font-size:11px;" />
                    </td>
                </tr>
            `;
        });

        document.getElementById('glTotalCash').innerText = 'Rs ' + cashSum.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('glTotalCheque').innerText = 'Rs ' + chequeSum.toLocaleString('en-US', {minimumFractionDigits: 2});

        checkGLVerification();
    }

    function updateGLCollectionApproval(index, checked) {
        const col = currentGLCollectionsState[index];
        if (col) {
            col.is_verified = checked ? 1 : 0;
            col.is_flagged = 0;
        }
        checkGLVerification();
    }

    function updateGLCollectionDebitAccount(index, val) {
        const col = currentGLCollectionsState[index];
        if (col) {
            col.debit_account_id = val !== '' ? parseInt(val) : null;
        }
    }

    function updateGLCollectionCreditAccount(index, val) {
        const col = currentGLCollectionsState[index];
        if (col) {
            col.credit_account_id = val !== '' ? parseInt(val) : null;
        }
    }

    function updateGLCollectionAdjustedAmount(index, val) {
        const col = currentGLCollectionsState[index];
        if (col) {
            col.adjusted_amount = val !== '' ? parseFloat(val) : null;
        }
        checkGLVerification();
    }

    function updateGLCollectionNotes(index, val) {
        const col = currentGLCollectionsState[index];
        if (col) {
            col.verification_notes = val;
        }
    }

    function saveCollectionsVerificationStage2() {
        if (!currentRouteId) return;

        const updates = currentGLCollectionsState.map(col => ({
            id: col.id,
            is_verified: col.is_verified,
            is_flagged: col.is_flagged,
            adjusted_amount: col.adjusted_amount !== null ? parseFloat(col.adjusted_amount) : parseFloat(col.amount),
            verification_notes: col.verification_notes,
            debit_account_id: col.debit_account_id || getAccountIdByCode(col.payment_method === 'Cash' ? '1000' : (col.payment_method === 'Cheque' ? '1010' : '1605')),
            credit_account_id: col.credit_account_id || getAccountIdByCode('1200')
        }));

        fetch('<?= APP_URL ?>/RepTracking/api_verify_collections', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ updates: updates })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadCollectionsVerificationStage2(currentRouteId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while saving verification.');
        });
    }

    function checkGLVerification() {
        const btn = document.getElementById('glApproveSalesBtn');
        const text = document.getElementById('glVerificationStatusText');

        let allCollectionsApproved = true;
        let pendingOrFlaggedCount = 0;
        currentGLCollectionsState.forEach(col => {
            if (parseInt(col.is_verified) !== 1) {
                allCollectionsApproved = false;
                pendingOrFlaggedCount++;
            }
        });

        if (allCollectionsApproved) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            text.innerHTML = '<span style="color:#2e7d32; font-weight:bold;">Verification Complete!</span> collections approved.';
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            text.innerHTML = `<span style="color:#dc2626; font-weight:bold;">Verification Pending:</span> ${pendingOrFlaggedCount} collections remaining.`;
        }
    }
</script>