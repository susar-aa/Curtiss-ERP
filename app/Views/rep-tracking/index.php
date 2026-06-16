<?php
// Enable error reporting to prevent blank 500 errors in the future
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
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

    /* Middle Pane: Bills Table */
    .pane-middle { flex: 1; display: flex; flex-direction: column; background: #fff; position: relative;}
    @media (prefers-color-scheme: dark) { .pane-middle { background: #1a1a2e; } }
    .mid-header { padding: 20px 25px; border-bottom: 1px solid var(--mac-border); background: var(--surface); display: flex; justify-content: space-between; align-items: flex-end;}
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 13px;}
    .data-table th { color: #888; font-weight: 600; font-size: 11px; text-transform: uppercase; background: rgba(0,0,0,0.02); position: sticky; top: 0;}
    
    .bill-row { cursor: pointer; transition: 0.1s; user-select: none;}
    .bill-row:hover { background: rgba(0,102,204,0.05); }

    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); }
    .empty-state span { font-size: 50px; margin-bottom: 15px; opacity: 0.5;}

    /* Right Pane: Slide-out Invoice Mini-View (Granular Side-by-Side Split View) */
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

    /* Premium Setup Modal CSS */
    .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-panel { background: #fff; width: 100%; max-width: 450px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); border: 1px solid #ccc; overflow: hidden; display: flex; flex-direction: column; }
    .modal-header { padding: 15px 20px; background: #0066cc; color: #fff; font-weight: bold; font-size: 15px; display: flex; justify-content: space-between; align-items: center;}
    .modal-body { padding: 20px; display: flex; flex-direction: column; gap: 15px; }
    .modal-body label { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;}
    .modal-body input { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;}
    .modal-body input:focus { border-color: #0066cc; outline: none; }
    .modal-footer { padding: 15px 20px; background: #f5f5f5; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;}

    /* Route path map */
    .pane-map-section { border-top: 1px solid var(--mac-border); display: flex; flex-direction: column; min-height: 280px; max-height: 42%; background: #eef1f4; }
    @media (prefers-color-scheme: dark) { .pane-map-section { background: #151520; } }
    .map-section-header { padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; background: var(--surface); border-bottom: 1px solid var(--mac-border); flex-shrink: 0; }
    .map-section-header h4 { margin: 0; font-size: 13px; color: var(--text-dark, #333); }
    .map-legend { display: flex; gap: 12px; font-size: 11px; color: #666; flex-wrap: wrap; }
    .map-legend span { display: inline-flex; align-items: center; gap: 4px; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .legend-start { background: #2e7d32; }
    .legend-invoice { background: #0066cc; }
    .legend-end { background: #c62828; }
    #routePathMap { flex: 1; min-height: 220px; width: 100%; }
    .map-empty-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.85); color: #666; font-size: 13px; font-weight: 600; text-align: center; padding: 20px; z-index: 500; pointer-events: none; }
    @media (prefers-color-scheme: dark) { .map-empty-overlay { background: rgba(18,18,18,0.9); color: #aaa; } }
    .map-wrap { position: relative; flex: 1; min-height: 220px; }
    .path-step-list { max-height: 72px; overflow-y: auto; padding: 6px 20px 10px; font-size: 11px; color: #555; background: var(--surface); border-top: 1px solid var(--mac-border); flex-shrink: 0; }
    .path-step-list ol { margin: 0; padding-left: 18px; }
    .path-step-list li { margin-bottom: 2px; }
    .path-step-start { color: #2e7d32; font-weight: bold; }
    .path-step-invoice { color: #0066cc; }
    .path-step-end { color: #c62828; font-weight: bold; }

    /* Target Bank account selection styling in Route Tracking footer */
    #collectionsFooter {
        background: #ffffff !important;
        border-top: 1px solid var(--mac-border) !important;
    }
    #targetBankAccount {
        border: 1px solid #cbd5e1;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    #targetBankAccount:focus {
        border-color: #0066cc;
        outline: none;
    }
    @media (prefers-color-scheme: dark) {
        #collectionsFooter {
            background: #1e1e2d !important;
            border-top-color: #2d2d3d !important;
        }
        #targetBankAccount {
            background: #12121a !important;
            color: #f1f5f9 !important;
            border-color: #3f3f46 !important;
        }
        #bankAccountSelectorContainer {
            background: rgba(255,255,255,0.03) !important;
            border-color: #2d2d3d !important;
        }
        #bankAccountSelectorContainer span {
            color: #e2e8f0 !important;
        }
    }

    /* Route Multi-Binding CSS System */
    .rb-slot-column {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .rb-slot-box {
        border: 2px dashed #cbd5e1;
        border-radius: 6px;
        padding: 20px;
        text-align: center;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80px;
    }
    .rb-slot-box:hover {
        border-color: #3f51b5;
        background: #f5f7ff;
    }
    .rb-slot-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 13px;
        background: #fff;
        color: #333;
        margin-top: 5px;
    }
    .rb-bill-list {
        max-height: 180px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #fff;
        padding: 8px;
        font-size: 12px;
        display: none;
    }
    .rb-bill-item {
        display: flex;
        justify-content: space-between;
        padding: 6px 8px;
        border-bottom: 1px solid #f1f5f9;
        align-items: center;
    }
    .rb-bill-item:last-child {
        border-bottom: none;
    }
    .rb-bound-tag {
        margin-top: 5px;
        font-size: 11px;
        background: #e8eaf6;
        color: #3f51b5;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
        font-weight: bold;
    }
    @media (prefers-color-scheme: dark) {
        .rb-slot-column {
            background: #1e1e2d;
            border-color: #2d2d3d;
        }
        .rb-slot-box {
            background: #12121a;
            border-color: #3f3f46;
        }
        .rb-slot-box:hover {
            background: #181824;
        }
        .rb-slot-select {
            background: #1e1e2d;
            color: #f1f5f9;
            border-color: #3f3f46;
        }
        .rb-bill-list {
            background: #12121a;
            border-color: #2d2d3d;
        }
        .rb-bill-item {
            border-bottom-color: #1e1e2d;
        }
        .rb-bound-tag {
            background: #1c2438 !important;
            color: #7986cb !important;
        }
    }
</style>

<div class="header-actions" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0 0 5px 0;">Rep Route Tracking & Audits</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Click a route to view sales orders and the GPS path from day start through each sales order to day end.</p>
    </div>
    <div>
        <button id="btnOpenRouteBinding" onclick="openRouteBindingModal()" style="padding: 10px 18px; border: none; background: #3f51b5; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(63, 81, 181, 0.2); transition: all 0.2s ease;">
            🔗 Route Binding Panel
        </button>
    </div>
</div>

<div class="app-workspace">
    <!-- Left Pane: Routes Master List -->
    <div class="pane-left">
        <!-- Filter Tabs for Route Status -->
        <div style="display: flex; border-bottom: 1px solid var(--mac-border); background: var(--surface); padding: 8px; gap: 4px; flex-shrink: 0; position: sticky; top: 0; z-index: 20;">
            <button type="button" class="left-tab-btn active" id="btnLeftActive" onclick="filterLeftPane('active', this)" style="flex: 1; padding: 8px 2px; font-size: 11px; font-weight: bold; border-radius: 6px; border: none; background: #0066cc; color: white; cursor: pointer; white-space: nowrap; transition: 0.2s;">
                ⚡ Active
            </button>
            <button type="button" class="left-tab-btn" id="btnLeftPendingGL" onclick="filterLeftPane('pending_finalization', this)" style="flex: 1; padding: 8px 2px; font-size: 11px; font-weight: bold; border-radius: 6px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; white-space: nowrap; transition: 0.2s;">
                📄 Pending GL
            </button>
            <button type="button" class="left-tab-btn" id="btnLeftPendingDelivery" onclick="filterLeftPane('pending_delivery', this)" style="flex: 1; padding: 8px 2px; font-size: 11px; font-weight: bold; border-radius: 6px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; white-space: nowrap; transition: 0.2s;">
                🚚 Pending Del
            </button>
        </div>

        <div style="flex: 1; overflow-y: auto;" id="routeListItemsContainer">
            <?php foreach($data['routes'] as $route): ?>
                <?php 
                    if ($route->status === 'Active') {
                        $dataType = 'active';
                    } elseif ($route->status === 'Completed' && $route->unfinalized_count > 0) {
                        $dataType = 'pending_finalization';
                    } else {
                        $dataType = 'pending_delivery';
                    }
                ?>
                <div class="route-item" id="route_<?= $route->id ?>" data-route-type="<?= $dataType ?>" onclick="loadRouteDetails(<?= $route->id ?>, this)">
                    <div class="r-title"><span class="status-dot status-<?= $route->status ?>"></span> <?= htmlspecialchars($route->route_name) ?></div>
                    <div class="r-sub">Rep: <?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?></div>
                    <div class="r-meta">
                        <span><?= date('M d, Y', strtotime($route->start_time)) ?></span>
                        <strong style="color: <?= $route->status == 'Completed' ? 'inherit' : '#ef6c00' ?>;">Rs: <?= number_format($route->total_sales, 2) ?></strong>
                    </div>
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
                
                <!-- Hidden data payload to populate the middle header quickly -->
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
                     data-binding-id="<?= $route->route_binding_id ?: '' ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Middle Pane: Bills Detail List -->
    <div class="pane-middle">
        
        <!-- Header populates via JS -->
        <div class="mid-header" id="midHeader" style="visibility: hidden;">
            <div>
                <h3 style="margin:0 0 5px 0; color:var(--primary);" id="mhRouteName">Route Name</h3>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: bold;">Rep: <span id="mhRepName"></span></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                    ODO Start: <strong id="mhStart"></strong> &nbsp;|&nbsp; ODO End: <strong id="mhEnd"></strong>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <div class="stat-box"><span>Total Sales</span><strong style="color:#2e7d32;">Rs <span id="mhSales"></span></strong></div>
                <div class="stat-box"><span>Bills</span><strong id="mhBills"></strong></div>
                
                <!-- Print Loading Report Button -->
                <button id="btnPrintLoading" onclick="printLoading()" style="padding: 10px 15px; border: none; background: #ef6c00; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px;">📄 Print Loading</button>
                
                <!-- NEW: Delivery Arrange Button -->
                <button id="btnArrangeDelivery" onclick="openDeliveryModal()" style="padding: 10px 15px; border: none; background: #2e7d32; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 5px;">🚚 Arrange Delivery</button>
                
                <!-- NEW: Add Invoice Button -->
                <button id="btnAddInvoice" onclick="redirectToAddInvoice()" style="padding: 10px 15px; border: none; background: #0066cc; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 5px;">➕ Add Sales Order</button>

                <!-- NEW: Attach Sales Order Button -->
                <button id="btnAttachInvoice" onclick="openAttachInvoiceModal()" style="padding: 10px 15px; border: none; background: #5c6bc0; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 5px;">🔗 Attach Sales Order</button>

                <!-- NEW: View Map Button -->
                <button id="btnViewMap" onclick="openMapModal()" style="padding: 10px 15px; border: none; background: #ef6c00; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 5px; display: none; align-items: center; gap: 4px;">📍 View Map</button>
                
                <!-- NEW: Undo Route Binding Button -->
                <button id="btnUnbindRoute" onclick="unbindActiveRoute()" style="padding: 10px 15px; border: none; background: #c62828; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 5px; display: none; align-items: center; gap: 4px;">🔗 Undo Bind</button>
            </div>
        </div>

        <!-- Tabs for Switching Views -->
        <div id="routeTabs" style="display: none; padding: 10px 25px; background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--mac-border); gap: 15px;">
            <button id="tabInvoices" class="btn" style="background: #0066cc; color: white; padding: 6px 12px; font-size: 13px;" onclick="switchRouteTab('invoices')">📄 Route Sales Orders</button>
            <button id="tabCollections" class="btn" style="background: transparent; color: #0066cc; border: 1px solid #0066cc; padding: 6px 12px; font-size: 13px;" onclick="switchRouteTab('collections')">💰 Route Collections & GL Finalization</button>
            <button id="tabVariances" class="btn" style="background: transparent; color: #ef6c00; border: 1px solid #ef6c00; padding: 6px 12px; font-size: 13px; display: none;" onclick="switchRouteTab('variances')">🚚 Dispatch Loading Variances</button>
        </div>

        <div style="flex:1; overflow-y:auto; position:relative;">
            <div class="empty-state" id="midEmptyState">
                <span>📍</span>
                Click a route on the left to load its bills.
            </div>

            <!-- COLLECTIONS VIEW -->
            <div id="collectionsWrapper" style="display: none; padding: 20px 25px;">
                <div id="collectionsOverview" style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    <!-- Dynamically populated stats -->
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: var(--text-dark);">Itemized Payments Collected</h4>
                    <button class="btn" onclick="toggleSelectAllCollections(this)" id="btnSelectAllCollections" style="background: transparent; border: 1px solid #ccc; font-size: 11px; padding: 4px 8px; cursor: pointer;">Select All Unfinalized</button>
                </div>

                <div id="collectionsList" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 70px;">
                    <!-- AJAX populated cards -->
                </div>

                <!-- Sticky Post Action Footer -->
                <div id="collectionsFooter" style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--surface); padding: 15px 25px; border-top: 1px solid var(--mac-border); display: none; justify-content: space-between; align-items: center; z-index: 100; gap: 15px; flex-wrap: wrap;">
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 20px; flex: 1;">
                        <div>
                            Selected: <strong id="selectedCollectionsCount" style="color: #0066cc;">0</strong> payment(s) to post to General Ledger
                        </div>
                        
                        <!-- Target Commercial Bank Validation Status Warning -->
                        <div id="bankAccountSelectorContainer" style="display: none; align-items: center; gap: 6px; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold;">
                            <span>⚠️ Target bank required for checked Bank Transfer payments.</span>
                        </div>
                    </div>
                    <button class="btn" id="btnPostGL" style="background: #2e7d32; color: #fff; padding: 8px 16px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px;" onclick="postSelectedCollectionsGL()">
                        ⚡ Post GL Updates & Finalize
                    </button>
                </div>
            </div>

            <!-- DISPATCH LOADING VARIANCES VIEW -->
            <div id="variancesWrapper" style="display: none; padding: 20px 25px;">
                <div id="variancesOverview" style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    <!-- Dynamically populated stats -->
                </div>

                <h4 style="margin: 0 0 15px 0; color: var(--text-dark);">Final Dispatch Verification & Variances</h4>

                <div id="variancesList" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                    <!-- AJAX populated cards -->
                </div>
            </div>

            <table class="data-table" id="billsTable" style="display: none;">
                <thead>
                    <tr>
                        <th>Sales Order #</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th style="text-align:right;">Grand Total (Rs)</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody id="billsTableBody">
                    <!-- AJAX populates here -->
                </tbody>
            </table>
            
            <!-- Loading Indicator -->
            <div id="midLoader" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; font-weight:bold; color:var(--primary);">
                Loading Bills... ⏳
            </div>
        </div>

        </div>
    </div>

    <!-- Dynamic GPS Route Map Modal -->
    <div class="modal-backdrop" id="mapModalBackdrop" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
        <div class="modal-panel" style="max-width: 950px; width: 90%; height: 80vh; display: flex; flex-direction: column; background: var(--surface); border: 1px solid var(--mac-border); box-shadow: 0 15px 40px rgba(0,0,0,0.3); border-radius: 12px; overflow: hidden;">
            <div style="background: #3f51b5; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 15px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>📍 GPS Route Path Tracking Map</span>
                    <span id="modalRouteName" style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 11px;"></span>
                    <span id="pathPointCount" style="font-weight: normal; font-size: 11px; opacity: 0.85;"></span>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 11px; font-weight: normal;">
                        <span style="display: inline-flex; align-items: center; gap: 3px;"><i class="legend-dot legend-start" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #2e7d32;"></i> Day Start</span>
                        <span style="display: inline-flex; align-items: center; gap: 3px;"><i class="legend-dot legend-invoice" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #0066cc;"></i> Sales Order</span>
                        <span style="display: inline-flex; align-items: center; gap: 3px;"><i class="legend-dot legend-end" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #c62828;"></i> Day End</span>
                    </div>
                    <button class="close-slider" onclick="closeMapModal()" style="border: none; background: transparent; color: #fff; font-size: 20px; cursor: pointer; padding: 0;">✕</button>
                </div>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; position: relative; background: #eef1f4;">
                <div style="flex: 1; position: relative; min-height: 350px;">
                    <div id="mapEmptyOverlay" class="map-empty-overlay" style="display: flex; position: absolute; inset: 0; align-items: center; justify-content: center; background: rgba(255,255,255,0.85); color: #666; font-size: 13px; font-weight: 600; text-align: center; padding: 20px; z-index: 500; pointer-events: none;">Select a route to load its path.</div>
                    <div id="routePathMap" style="height: 100%; width: 100%;"></div>
                </div>
                <div class="path-step-list" id="pathStepList" style="max-height: 120px; overflow-y: auto; padding: 12px 20px; background: var(--surface); border-top: 1px solid var(--mac-border); display: none;">
                    <ol id="pathStepOl" style="margin: 0; padding-left: 20px; font-family: monospace; font-size: 11px;"></ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Pane: Invoice Slide-Out Viewer -->
    <div class="pane-right-slider" id="invoiceSlider">
        <div class="slider-header">
            <span>Sales Order Mini-View</span>
            <div>
                <!-- Edit Invoice Button -->
                <a id="btnEditInvoice" href="#" style="background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 15px; border: 1px solid rgba(255,255,255,0.4);">✏️ Edit Sales Order</a>
                <button id="btnDeleteInvoice" onclick="deleteSalesOrder()" style="background: #dc2626; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 15px; cursor: pointer; font-weight: bold;">🗑️ Delete</button>
                <button class="close-slider" onclick="closeInvoiceSlider()">✕</button>
            </div>
        </div>
        <!-- Iframe loads the actual generated PDF view -->
        <iframe id="invoiceIframe" src="about:blank"></iframe>
    </div>
</div>

<!-- NEW: Delivery Arrangement Metadata Setup Modal -->
<div class="modal-backdrop" id="deliveryModal">
    <div class="modal-panel" style="max-width: 650px;">
        <div class="modal-header">
            <span>🚚 Setup Delivery Arrangement</span>
            <button onclick="closeDeliveryModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body">
            <div>
                <label>Delivery Date</label>
                <input type="date" id="daDate" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label>Vehicle Number *</label>
                <select id="daVehicle" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: transparent; color: inherit;">
                    <option value="">-- Select Vehicle --</option>
                    <?php foreach($data['vehicles'] as $v): ?>
                        <?php if($v->status === 'Active'): ?>
                            <option value="<?= htmlspecialchars($v->vehicle_number) ?>"><?= htmlspecialchars($v->vehicle_number) ?> (<?= htmlspecialchars($v->model) ?>)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($data['vehicles'])): ?>
                    <p style="font-size:11px; color:#c62828; margin:5px 0 0 0;">⚠️ No vehicles registered. Please add vehicles in Fleet Management.</p>
                <?php endif; ?>
            </div>
            <div>
                <label>Driver Name *</label>
                <select id="daDriver" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: transparent; color: inherit;">
                    <option value="">-- Select Driver --</option>
                    <?php foreach($data['drivers'] as $d): ?>
                        <option value="<?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?>"><?= htmlspecialchars($d->first_name . ' ' . $d->last_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($data['drivers'])): ?>
                    <p style="font-size:11px; color:#c62828; margin:5px 0 0 0;">⚠️ No active drivers found. Please register employees with the 'Driver' role.</p>
                <?php endif; ?>
            </div>
            <div>
                <label>Partner / Helper</label>
                <select id="daPartner" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: transparent; color: inherit;">
                    <option value="">-- No Partner (Driver Only) --</option>
                    <?php foreach($data['employees'] as $e): ?>
                        <?php if($e->status === 'Active'): ?>
                            <option value="<?= htmlspecialchars($e->first_name . ' ' . $e->last_name) ?>"><?= htmlspecialchars($e->first_name . ' ' . $e->last_name) ?> (<?= htmlspecialchars($e->job_title) ?>)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Removed Bind Secondary Route selection block from delivery arrangement modal -->
            <div style="margin-top: 15px;">
                <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Select Territory Outstanding Credit Bills to Assign</label>
                <div id="outstandingBillsContainer" style="border: 1px solid #ccc; border-radius: 6px; padding: 10px; max-height: 220px; overflow-y: auto; background: #fafafa; font-size: 12px;">
                    <p style="text-align: center; color: #888; margin: 10px 0;">Select a daily rep route first.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="qb-btn" onclick="closeDeliveryModal()" style="border:1px solid #ccc; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="confirmArrangeDelivery()" style="background:#2e7d32; color:#fff; border-color:#2e7d32; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Go to Spreadsheet Summary &rarr;</button>
        </div>
    </div>
</div>

<!-- NEW: Attach Existing Sales Orders Modal -->
<div class="modal-backdrop" id="attachInvoiceModal" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
    <div class="modal-panel" style="max-width: 580px; width: 90%;">
        <div class="modal-header" style="background: #5c6bc0;">
            <span>🔗 Attach Sales Orders to Route</span>
            <button onclick="closeAttachInvoiceModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 12px;">
            <div>
                <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Search Sales Order Number or Customer Name</label>
                <input type="text" id="invoiceSearchInput" onkeyup="searchUnattachedInvoices()" placeholder="Type SO-XXXX or customer name..." style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Start Date</label>
                    <input type="date" id="soFilterStartDate" onchange="searchUnattachedInvoices()" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
                </div>
                <div>
                    <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">End Date</label>
                    <input type="date" id="soFilterEndDate" onchange="searchUnattachedInvoices()" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                <div>
                    <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Status Filter</label>
                    <select id="soFilterStatus" onchange="searchUnattachedInvoices()" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px; background: white;">
                        <option value="">All Statuses</option>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="button" onclick="resetSalesOrderFilters()" style="width: 100%; padding: 8px 12px; background: #e2e8f0; color: #333; border: 1px solid #cbd5e1; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; text-align: center; height: 38px; box-sizing: border-box;">Reset Filters</button>
                </div>
            </div>
            <div id="unattachedInvoicesContainer" style="border: 1px solid #ccc; border-radius: 6px; padding: 10px; max-height: 200px; overflow-y: auto; background: #fafafa; font-size: 12px; margin-top: 5px;">
                <p style="text-align: center; color: #888; margin: 10px 0;">Start typing or change filters to search unattached sales orders...</p>
            </div>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; background: #f5f5f5; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="qb-btn" onclick="closeAttachInvoiceModal()" style="border:1px solid #ccc; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="confirmAttachInvoices()" style="background:#5c6bc0; color:#fff; border-color:#5c6bc0; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;">Attach Selected</button>
        </div>
    </div>
</div>

<!-- NEW: Route Multi-Binding Modal -->
<div class="modal-backdrop" id="routeBindingModal" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
    <div class="modal-panel" style="max-width: 1000px; width: 95%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="background: #3f51b5;">
            <span>🔗 Rep Route Multi-Binding Panel</span>
            <button onclick="closeRouteBindingModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="font-weight: bold; font-size: 12px; text-transform: uppercase; color: #555; margin-bottom: 6px; display: block;">Custom Route Name (Merged Route Assignment ID)</label>
                <input type="text" id="rbBoundName" placeholder="e.g. Combined Western Route - June 2" style="width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 14px;">
            </div>
            
            <label style="font-weight: bold; font-size: 12px; text-transform: uppercase; color: #555; margin-bottom: 10px; display: block;">Define Bound Territory Route Slots</label>
            <div class="rb-slots-grid" id="rbSlotsContainer" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <!-- Dynamically populated slots -->
            </div>
            
            <button type="button" onclick="addBindingSlot()" style="background: #eef2ff; color: #3f51b5; border: 1px dashed #3f51b5; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: block; width: 100%; text-align: center; transition: all 0.2s ease;">
                ➕ Add Another Binding Slot
            </button>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; background: #f5f5f5; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="qb-btn" onclick="closeRouteBindingModal()" style="border:1px solid #ccc; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="submitRouteBinding()" style="background:#2e7d32; color:#fff; border-color:#2e7d32; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;">⚡ Confirm & Create Route Binding</button>
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

    function initRoutePathMap() {
        if (routeMap !== null) return;
        routeMap = L.map('routePathMap', { zoomControl: true }).setView([7.8731, 80.7718], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(routeMap);
    }

    function clearRoutePathMap() {
        routeMapLayers.forEach(layer => routeMap.removeLayer(layer));
        routeMapLayers = [];
    }

    function formatPathTime(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        return d.toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
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
        document.getElementById('pathPointCount').innerText = wps.length
            ? `(${wps.length} point${wps.length === 1 ? '' : 's'})`
            : '(no GPS data)';

        const stepOl = document.getElementById('pathStepOl');
        stepOl.innerHTML = '';

        if (wps.length === 0) {
            document.getElementById('mapEmptyOverlay').style.display = 'flex';
            document.getElementById('mapEmptyOverlay').innerHTML =
                'No GPS points recorded for this route.<br><span style="font-weight:normal;font-size:12px;">Start day, invoices, and end day must capture location on the rep app.</span>';
            document.getElementById('pathStepList').style.display = 'none';
            setTimeout(() => routeMap.invalidateSize(), 100);
            return;
        }

        document.getElementById('mapEmptyOverlay').style.display = 'none';
        document.getElementById('pathStepList').style.display = 'block';

        const latlngs = [];
        wps.forEach((wp, idx) => {
            const latlng = [wp.lat, wp.lng];
            latlngs.push(latlng);

            let icon = pathBlueIcon;
            let stepClass = 'path-step-invoice';
            let stepLabel = '';

            if (wp.type === 'start') {
                icon = pathGreenIcon;
                stepClass = 'path-step-start';
                stepLabel = `Day Start — ${formatPathTime(wp.time)}`;
            } else if (wp.type === 'end') {
                icon = pathRedIcon;
                stepClass = 'path-step-end';
                stepLabel = `Day End — ${formatPathTime(wp.time)}`;
            } else {
                stepLabel = `${wp.sequence}. ${wp.label} — ${wp.detail} (${formatPathTime(wp.time)})`;
            }

            const popupHtml = wp.type === 'invoice'
                ? `<strong>${wp.label}</strong><br>${wp.detail}<br>${formatPathTime(wp.time)}<br>Rs ${parseFloat(wp.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}`
                : `<strong>${wp.label}</strong><br>${wp.detail || ''}<br>${formatPathTime(wp.time)}`;

            const marker = L.marker(latlng, { icon }).bindPopup(popupHtml);
            marker.addTo(routeMap);
            routeMapLayers.push(marker);

            const li = document.createElement('li');
            li.className = stepClass;
            li.textContent = stepLabel || wp.label;
            if (wp.type === 'invoice' && wp.id) {
                li.style.cursor = 'pointer';
                li.title = 'Click to preview sales order';
                li.onclick = () => openInvoiceSlider(wp.id);
            }
            stepOl.appendChild(li);
        });

        if (latlngs.length >= 2) {
            const line = L.polyline(latlngs, {
                color: '#0066cc',
                weight: 4,
                opacity: 0.75,
                dashArray: latlngs.length > 2 ? null : '8, 8'
            });
            line.addTo(routeMap);
            routeMapLayers.push(line);
        }

        const bounds = L.latLngBounds(latlngs);
        routeMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
        setTimeout(() => routeMap.invalidateSize(), 150);
    }

    // --- 1. Master/Left Pane Logic ---
    function loadRouteDetails(routeId, el) {
        currentRouteId = routeId; // Save for print & arrange buttons
        
        // UI Selection Highlight
        document.querySelectorAll('.route-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        // Populate Middle Header from hidden dataset
        const d = document.getElementById('route_data_' + routeId);
        document.getElementById('mhRouteName').innerText = d.getAttribute('data-rname');
        document.getElementById('mhRepName').innerText = d.getAttribute('data-rep');
        document.getElementById('mhStart').innerText = d.getAttribute('data-start');
        document.getElementById('mhEnd').innerText = d.getAttribute('data-end');
        document.getElementById('mhSales').innerText = d.getAttribute('data-sales');
        document.getElementById('mhBills').innerText = d.getAttribute('data-bills');
        
        const status = d.getAttribute('data-status');
        const unfinalized = parseInt(d.getAttribute('data-unfinalized')) || 0;
        
        // Show Undo Bind button if this is a bound group
        const isBound = d.getAttribute('data-bound') === '1';
        const bindingId = d.getAttribute('data-binding-id');
        const btnUnbind = document.getElementById('btnUnbindRoute');
        if (btnUnbind) {
            if (isBound && bindingId) {
                btnUnbind.style.display = 'inline-flex';
                btnUnbind.setAttribute('data-binding-id', bindingId);
            } else {
                btnUnbind.style.display = 'none';
            }
        }

        // Do not show route route collection finalization system until rep end the route
        if (status !== 'Completed') {
            document.getElementById('tabCollections').style.display = 'none';
            document.getElementById('tabVariances').style.display = 'none';
            switchRouteTab('invoices');
        } else {
            document.getElementById('tabCollections').style.display = 'inline-block';
            document.getElementById('tabVariances').style.display = 'inline-block';
        }

        // Show Arrange Delivery only for completed & finalized routes
        if (status === 'Completed' && unfinalized === 0) {
            document.getElementById('btnArrangeDelivery').style.display = 'inline-block';
        } else {
            document.getElementById('btnArrangeDelivery').style.display = 'none';
        }

        document.getElementById('routeTabs').style.display = 'flex';
        document.getElementById('midHeader').style.visibility = 'visible';
        document.getElementById('midEmptyState').style.display = 'none';
        document.getElementById('midLoader').style.display = 'block';

        if (currentRouteTab === 'collections') {
            document.getElementById('billsTable').style.display = 'none';
            document.getElementById('collectionsWrapper').style.display = 'block';
            loadRouteCollections(routeId);
        } else {
            document.getElementById('billsTable').style.display = 'none';
            document.getElementById('collectionsWrapper').style.display = 'none';
        }

        // Close slider if open
        closeInvoiceSlider();

        // Show View Map Button
        document.getElementById('btnViewMap').style.display = 'inline-flex';

        // AJAX Fetch Bills
        fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('midLoader').style.display = 'none';
                if (currentRouteTab === 'invoices') {
                    document.getElementById('billsTable').style.display = 'table';
                }
                
                const tbody = document.getElementById('billsTableBody');
                tbody.innerHTML = '';
                
                if (data.bills.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:30px; color:#888;">No bills generated on this route.</td></tr>';
                    return;
                }

                data.bills.forEach(bill => {
                    let time = new Date(bill.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    let statColor = bill.status === 'Paid' ? '#2e7d32' : (bill.status === 'Unpaid' ? '#ef6c00' : '#666');

                    tbody.innerHTML += `
                        <tr class="bill-row" onclick="openInvoiceSlider(${bill.id})">
                            <td style="font-weight:bold; color:var(--primary);">${bill.invoice_number}</td>
                            <td style="color:var(--text-muted);">${time}</td>
                            <td style="font-weight:bold;">${bill.customer_name}</td>
                            <td style="text-align:right; font-weight:bold; font-family:monospace;">${parseFloat(bill.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                            <td style="text-align:center;"><span style="color:${statColor}; font-weight:bold; font-size:11px; text-transform:uppercase;">${bill.status}</span></td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                document.getElementById('midLoader').style.display = 'none';
                alert("Failed to load bills. Network error.");
                console.error(err);
            });
    }

    // --- 2. Detail/Right Pane Logic ---
    function openInvoiceSlider(invoiceId) {
        const slider = document.getElementById('invoiceSlider');
        const iframe = document.getElementById('invoiceIframe');
        
        // Update Edit button URL path
        document.getElementById('btnEditInvoice').href = '<?= APP_URL ?>/sales/edit/' + invoiceId + '?type=sales_order';

        // Point the iframe to the existing invoice view
        iframe.src = '<?= APP_URL ?>/sales/show/' + invoiceId;
        
        // Slide it open
        slider.classList.add('open');
    }

    function closeInvoiceSlider() {
        const slider = document.getElementById('invoiceSlider');
        slider.classList.remove('open');
        setTimeout(() => { document.getElementById('invoiceIframe').src = 'about:blank'; }, 300);
    }

    // --- 3. Print Loading Report ---
    function printLoading() {
        if(currentRouteId) {
            window.open('<?= APP_URL ?>/RepTracking/print_loading/' + currentRouteId, '_blank');
        }
    }

    // --- 4. NEW: Delivery Setup Modal Handlers ---
    function openDeliveryModal() {
        if(!currentRouteId) {
            alert("Please select a daily rep route first.");
            return;
        }
        document.getElementById('deliveryModal').style.display = 'flex';

        // No secondary route selection needed since routes are bound separately in Rep Route Tracking
        loadOutstandingBillsForBoundRoutes();
    }

    function loadOutstandingBillsForBoundRoutes() {
        const container = document.getElementById('outstandingBillsContainer');
        container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">Loading outstanding credit bills... ⏳</p>';
        
        let url = '<?= APP_URL ?>/RepTracking/api_get_outstanding_bills/' + currentRouteId;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success' || !data.bills || data.bills.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">No outstanding credit bills found in these territories.</p>';
                    return;
                }
                
                let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';
                data.bills.forEach(bill => {
                    let amtFormatted = parseFloat(bill.total_outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    html += `
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 0;">
                            <input type="checkbox" class="da-bill-checkbox" value="${bill.customer_id}" style="width: 16px; height: 16px; margin-top: 2px;">
                            <div style="flex: 1;">
                                <div style="font-weight: bold; color: #333;">${bill.customer_name}</div>
                                <div style="font-size: 11px; color: #666;">
                                    Area: <strong>${bill.mca_name}</strong>
                                </div>
                            </div>
                            <div style="font-weight: bold; font-family: monospace; color: #c62828;">Rs ${amtFormatted}</div>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<p style="text-align: center; color: #c62828; margin: 10px 0;">Failed to load outstanding bills.</p>';
                console.error(err);
            });
    }

    function closeDeliveryModal() {
        document.getElementById('deliveryModal').style.display = 'none';
    }

    function confirmArrangeDelivery() {
        const date = document.getElementById('daDate').value;
        const vehicle = document.getElementById('daVehicle').value;
        const driver = document.getElementById('daDriver').value;
        const partner = document.getElementById('daPartner').value;
        const secondaryRouteId = null;

        if (!vehicle) {
            alert("Please select a vehicle.");
            return;
        }
        if (!driver) {
            alert("Please select a driver.");
            return;
        }

        const checkedBills = [];
        document.querySelectorAll('.da-bill-checkbox:checked').forEach(cb => {
            checkedBills.push(parseInt(cb.value));
        });

        closeDeliveryModal();

        const payload = {
            rep_route_id: currentRouteId,
            secondary_rep_route_id: null,
            delivery_date: date,
            vehicle_number: vehicle,
            driver_name: driver,
            partner_name: partner,
            selected_credit_invoices: checkedBills
        };

        // Post the arrangement to the deliveries endpoint via AJAX
        fetch('<?= APP_URL ?>/delivery/arrange', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🚚 Success: " + data.message);
                window.location.href = '<?= APP_URL ?>/delivery';
            } else {
                alert("⚠️ Error: " + data.message);
            }
        })
        .catch(err => {
            alert("Failed to arrange delivery. Connection or database transaction error.");
            console.error(err);
        });
    }

    // --- Tabs & Collections Finalization Engine ---
    let currentRouteTab = 'invoices';

    function switchRouteTab(tabName) {
        currentRouteTab = tabName;
        const btnInv = document.getElementById('tabInvoices');
        const btnCol = document.getElementById('tabCollections');
        const btnVar = document.getElementById('tabVariances');
        const tblInv = document.getElementById('billsTable');
        const wrpCol = document.getElementById('collectionsWrapper');
        const wrpVar = document.getElementById('variancesWrapper');

        // Reset styles
        btnInv.style.background = 'transparent';
        btnInv.style.color = '#0066cc';
        btnCol.style.background = 'transparent';
        btnCol.style.color = '#0066cc';
        btnVar.style.background = 'transparent';
        btnVar.style.color = '#ef6c00';

        tblInv.style.display = 'none';
        wrpCol.style.display = 'none';
        wrpVar.style.display = 'none';

        if (tabName === 'invoices') {
            btnInv.style.background = '#0066cc';
            btnInv.style.color = 'white';
            tblInv.style.display = 'table';
        } else if (tabName === 'collections') {
            btnCol.style.background = '#0066cc';
            btnCol.style.color = 'white';
            wrpCol.style.display = 'block';
            if (currentRouteId) {
                loadRouteCollections(currentRouteId);
            }
        } else if (tabName === 'variances') {
            btnVar.style.background = '#ef6c00';
            btnVar.style.color = 'white';
            wrpVar.style.display = 'block';
            if (currentRouteId) {
                loadRouteVariances(currentRouteId);
            }
        }
    }

    function loadRouteVariances(routeId) {
        const listDiv = document.getElementById('variancesList');
        const overviewDiv = document.getElementById('variancesOverview');
        listDiv.innerHTML = '<div style="text-align:center; padding:30px; font-weight:bold; color:#ef6c00;">Loading Variances... ⏳</div>';
        overviewDiv.innerHTML = '';

        fetch('<?= APP_URL ?>/RepTracking/api_get_route_variances/' + routeId)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success' || !data.deliveries || data.deliveries.length === 0) {
                    listDiv.innerHTML = '<div style="text-align:center; padding:30px; color:#888;">No dispatch loading records or variances found for this route.</div>';
                    return;
                }

                listDiv.innerHTML = '';
                let totalShort = 0;
                let totalOver = 0;

                data.deliveries.forEach(del => {
                    totalShort += del.shortages;
                    totalOver += del.overages;

                    let itemsHtml = '';
                    if (del.items.length === 0) {
                        itemsHtml = '<p style="color:#888; padding: 10px;">No items in this loading sheet.</p>';
                    } else {
                        itemsHtml = `
                            <table class="data-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                <thead>
                                    <tr style="background: rgba(0,0,0,0.02);">
                                        <th style="padding: 8px; text-align: left; font-size:11px;">Product Name</th>
                                        <th style="padding: 8px; text-align: center; font-size:11px;">Required</th>
                                        <th style="padding: 8px; text-align: center; font-size:11px;">Pre-Loaded</th>
                                        <th style="padding: 8px; text-align: center; font-size:11px;">Final Dispatch</th>
                                        <th style="padding: 8px; text-align: center; font-size:11px;">Variance</th>
                                        <th style="padding: 8px; text-align: center; font-size:11px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        del.items.forEach(item => {
                            let varColor = '#000';
                            let varText = '0';
                            
                            if (item.variance < 0) {
                                varColor = '#c62828';
                                varText = `${item.variance} (Short)`;
                            } else if (item.variance > 0) {
                                varColor = '#ef6c00';
                                varText = `+${item.variance} (Over)`;
                            } else {
                                varColor = '#2e7d32';
                                varText = 'Match';
                            }

                            let finalVal = item.final_loaded_qty !== null ? item.final_loaded_qty : '-';
                            let statusText = item.is_verified ? '<span style="color:#2e7d32; font-weight:bold;">Verified</span>' : '<span style="color:#ef6c00; font-weight:bold;">Pending</span>';

                            itemsHtml += `
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px; font-weight:bold; font-size:12px;">${item.item_name}</td>
                                    <td style="padding: 8px; text-align: center; font-weight:bold;">${item.required_qty}</td>
                                    <td style="padding: 8px; text-align: center;">${item.pre_loaded_qty}</td>
                                    <td style="padding: 8px; text-align: center; font-weight:bold;">${finalVal}</td>
                                    <td style="padding: 8px; text-align: center; font-weight:bold; color:${varColor};">${varText}</td>
                                    <td style="padding: 8px; text-align: center;">${statusText}</td>
                                </tr>
                            `;
                        });

                        itemsHtml += '</tbody></table>';
                    }

                    listDiv.innerHTML += `
                        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:15px; box-shadow:0 1px 3px rgba(0,0,0,0.05); margin-bottom:15px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:10px; margin-bottom:10px;">
                                <h3 style="margin:0; font-size:14px; color:#1e293b;">🚚 Loading Sheet #${del.delivery_id} (${del.vehicle_number})</h3>
                                <span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">Driver: ${del.driver_name}</span>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; font-size:12px; margin-bottom:10px;">
                                <div>Items: <strong>${del.verified_items}/${del.total_items} verified</strong></div>
                                <div style="color:#c62828;">Shortages: <strong>${del.shortages} pcs</strong></div>
                                <div style="color:#ef6c00;">Overages: <strong>${del.overages} pcs</strong></div>
                            </div>
                            ${itemsHtml}
                        </div>
                    `;
                });

                // Render Overview panel stats
                overviewDiv.innerHTML = `
                    <div class="stat-box" style="flex:1; min-width:150px;">
                        <span>Total Shortages</span>
                        <strong style="color:#c62828;">${totalShort} pcs</strong>
                    </div>
                    <div class="stat-box" style="flex:1; min-width:150px;">
                        <span>Total Overages</span>
                        <strong style="color:#ef6c00;">${totalOver} pcs</strong>
                    </div>
                    <div class="stat-box" style="flex:1; min-width:150px;">
                        <span>Status</span>
                        <strong style="color:#2e7d32;">Audited</strong>
                    </div>
                `;
            })
            .catch(err => {
                listDiv.innerHTML = '<div style="text-align:center; padding:30px; color:#c62828;">Failed to load loading variances.</div>';
                console.error(err);
            });
    }

    function renderAccountSelect(paymentId, type, selectedCode) {
        let optionsHtml = '';
        globalAllAccounts.forEach(acc => {
            let isSel = acc.account_code === selectedCode ? 'selected' : '';
            optionsHtml += `<option value="${acc.id}" ${isSel}>${acc.account_code} - ${acc.account_name}</option>`;
        });
        return `
            <select class="payment-${type}-select" data-payment-id="${paymentId}" style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--mac-border); font-size: 11px; max-width: 250px; background: #fff; color: #333;" onchange="updateGLPreview(${paymentId})">
                ${optionsHtml}
            </select>
        `;
    }

    function updateGLPreview(paymentId) {
        // Purely visual effect update if desired, currently selecting handles it
    }

    function loadRouteCollections(routeId) {
        const listDiv = document.getElementById('collectionsList');
        const overviewDiv = document.getElementById('collectionsOverview');
        listDiv.innerHTML = '<div style="text-align:center; padding:30px; font-weight:bold; color:var(--primary);">Loading Collections... ⏳</div>';
        
        fetch('<?= APP_URL ?>/RepTracking/api_get_route_collections/' + routeId)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    listDiv.innerHTML = '<div style="text-align:center; padding:30px; color:#c62828;">Failed to load collections.</div>';
                    return;
                }
                
                const cols = data.collections || [];
                if (cols.length === 0) {
                    listDiv.innerHTML = '<div style="text-align:center; padding:30px; color:#888;">No customer payments collected on this route.</div>';
                    overviewDiv.innerHTML = '';
                    document.getElementById('collectionsFooter').style.display = 'none';
                    return;
                }
                
                document.getElementById('collectionsFooter').style.display = 'flex';
                
                // Build overview stats
                let cashTotal = 0, bankTotal = 0, chequeTotal = 0;
                cols.forEach(c => {
                    let amt = parseFloat(c.amount || 0);
                    if (c.payment_method === 'Cash') cashTotal += amt;
                    else if (c.payment_method === 'Bank Transfer') bankTotal += amt;
                    else if (c.payment_method === 'Cheque') chequeTotal += amt;
                });
                
                overviewDiv.innerHTML = `
                    <div class="stat-box" style="flex: 1; min-width: 120px;"><span>💵 Total Cash</span><strong>Rs ${cashTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></div>
                    <div class="stat-box" style="flex: 1; min-width: 120px;"><span>🏦 Total Bank</span><strong>Rs ${bankTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></div>
                    <div class="stat-box" style="flex: 1; min-width: 120px;"><span>🎫 Total Cheques</span><strong>Rs ${chequeTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></div>
                    <div class="stat-box" style="flex: 1; min-width: 140px; background: rgba(46, 125, 50, 0.05); border-color: rgba(46, 125, 50, 0.2);"><span>💰 Total Collected</span><strong style="color: #2e7d32;">Rs ${(cashTotal + bankTotal + chequeTotal).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></div>
                `;
                
                listDiv.innerHTML = '';
                cols.forEach(c => {
                    let isFinalized = c.journal_entry_id !== null;
                    let assetAccName = c.payment_method === 'Cash' ? '1000 - Cash Account' : (c.payment_method === 'Cheque' ? '1010 - Cheque Clearing Account' : '1600 - Bank Current Account');
                    let amtFormatted = parseFloat(c.amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    
                    let defaultDebitCode = '1000';
                    if (c.payment_method === 'Cheque') {
                        defaultDebitCode = '1010';
                    } else if (c.payment_method === 'Bank Transfer') {
                        defaultDebitCode = '1600';
                    }

                    let bankSelectHtml = '';
                    if (c.payment_method === 'Bank Transfer' && !isFinalized) {
                        let bankOptionsHtml = '<option value="">-- Choose Commercial Bank --</option>';
                        globalBankAccounts.forEach(b => {
                            if (b.account_code !== '1605') {
                                bankOptionsHtml += `<option value="${b.id}">${b.account_name} (${b.account_code})</option>`;
                            }
                        });
                        bankSelectHtml = `
                            <div style="margin-top: 10px; padding: 10px; background: rgba(0,0,0,0.02); border: 1px dashed var(--mac-border); border-radius: 6px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 12px; font-weight: bold; color: var(--text-dark);">🏦 Target Commercial Bank:</span>
                                </div>
                                <select class="payment-bank-select" data-payment-id="${c.id}" style="padding: 6px; border-radius: 4px; border: 1px solid var(--mac-border); font-size: 11px; font-weight: bold; cursor: pointer; color: var(--text-dark); background: #fff;" onchange="updateSelectedCollectionsStats()">
                                    ${bankOptionsHtml}
                                </select>
                            </div>
                        `;
                    }

                    let cardHtml = `
                        <div style="background: var(--surface); border: 1px solid ${isFinalized ? '#c8e6c9' : '#b3e5fc'}; border-radius: 8px; padding: 15px; position: relative; transition: box-shadow 0.2s;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 15px;">
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    ${isFinalized 
                                        ? `<span style="color:#2e7d32; font-size: 20px;" title="Finalized in General Ledger">✅</span>`
                                        : `<input type="checkbox" class="collection-checkbox" value="${c.id}" data-method="${c.payment_method}" onchange="updateSelectedCollectionsStats()" style="width: 18px; height: 18px; cursor: pointer;">`
                                    }
                                    <div>
                                        <span style="font-weight: bold; font-size: 14px; color: var(--text-dark);">${c.customer_name}</span>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 3px;">
                                            Date: <strong>${c.payment_date}</strong> &nbsp;|&nbsp; 
                                            Method: <strong style="color: #0066cc;">${c.payment_method}</strong>
                                            ${c.reference ? `&nbsp;|&nbsp; Ref: <strong>${c.reference}</strong>` : ''}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold; font-size: 16px; font-family: monospace;">Rs ${amtFormatted}</div>
                                    ${isFinalized 
                                        ? `<span class="badge" style="background:#e8f5e9; color:#2e7d32; display:inline-block; margin-top:5px; padding: 3px 8px; border-radius: 12px; font-size: 10px;">GL Finalized (JE: ${c.journal_entry_id})</span>`
                                        : `<span class="badge" style="background:#e1f5fe; color:#0288d1; display:inline-block; margin-top:5px; padding: 3px 8px; border-radius: 12px; font-size: 10px;">GL Pending</span>`
                                    }
                                </div>
                            </div>

                            ${bankSelectHtml}

                            <!-- Double Entry and Account Balance Preview -->
                            <div style="margin-top: 12px; padding: 12px; background: rgba(0,0,0,0.02); border: 1px dashed ${isFinalized ? '#a5d6a7' : '#90caf9'}; border-radius: 6px; font-family: monospace; font-size: 12px; color: #475569;">
                                <div style="font-weight: bold; color: ${isFinalized ? '#2e7d32' : '#0369a1'}; margin-bottom: 5px; text-transform: uppercase;">
                                    ${isFinalized ? 'Posted Double Entry (General Ledger)' : 'GL Double-Entry Preview (Post-Finalization)'}
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: 10px; flex-wrap: wrap;">
                                    <span>DEBIT ACCOUNT:</span>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        ${isFinalized 
                                            ? `<strong>${assetAccName}</strong>`
                                            : renderAccountSelect(c.id, 'debit', defaultDebitCode)
                                        }
                                        <span style="font-weight: bold; color: #16a34a;">+ Rs ${amtFormatted}</span>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: 10px; flex-wrap: wrap;">
                                    <span>CREDIT ACCOUNT:</span>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        ${isFinalized 
                                            ? `<strong>1200 - Accounts Receivable (Customer Sub-ledger)</strong>`
                                            : renderAccountSelect(c.id, 'credit', '1200')
                                        }
                                        <span style="font-weight: bold; color: #dc2626;">- Rs ${amtFormatted}</span>
                                    </div>
                                </div>
                                <div style="border-top: 1px dotted #ccc; margin-top: 5px; padding-top: 5px; font-size: 11px; color: #64748b; font-style: italic;">
                                    ${isFinalized 
                                        ? `Double entry successfully posted on route closing. General Ledger balances updated.` 
                                        : `Finalizing this will credit customer accounts receivable and debit rep collection accounts in real-time.`
                                    }
                                </div>
                            </div>
                        </div>
                    `;
                    listDiv.innerHTML += cardHtml;
                });
                
                updateSelectedCollectionsStats();
            });
    }

    function toggleSelectAllCollections(btn) {
        const checkboxes = document.querySelectorAll('.collection-checkbox');
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = anyUnchecked;
        });
        
        btn.innerText = anyUnchecked ? "Deselect All" : "Select All Unfinalized";
        updateSelectedCollectionsStats();
    }

    function updateSelectedCollectionsStats() {
        const checkboxes = document.querySelectorAll('.collection-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCollectionsCount').innerText = count;
        
        let missingBankSelection = false;
        let hasBankTransfer = false;
        
        checkboxes.forEach(cb => {
            if (cb.getAttribute('data-method') === 'Bank Transfer') {
                hasBankTransfer = true;
                const paymentId = cb.value;
                const selectEl = document.querySelector(`.payment-bank-select[data-payment-id="${paymentId}"]`);
                if (selectEl) {
                    if (!selectEl.value) {
                        missingBankSelection = true;
                        selectEl.style.borderColor = '#c62828';
                        selectEl.style.background = '#ffebee';
                    } else {
                        selectEl.style.borderColor = 'var(--mac-border)';
                        selectEl.style.background = '#fff';
                    }
                }
            }
        });
        
        const container = document.getElementById('bankAccountSelectorContainer');
        if (hasBankTransfer && missingBankSelection) {
            container.style.display = 'flex';
        } else {
            container.style.display = 'none';
        }
        
        const btnPost = document.getElementById('btnPostGL');
        
        if (count > 0) {
            if (missingBankSelection) {
                btnPost.disabled = true;
                btnPost.style.opacity = '0.5';
                btnPost.style.cursor = 'not-allowed';
                btnPost.innerText = "🛑 Select Target Bank(s)";
            } else {
                btnPost.disabled = false;
                btnPost.style.opacity = '1';
                btnPost.style.cursor = 'pointer';
                btnPost.innerText = "⚡ Post GL Updates & Finalize";
            }
        } else {
            btnPost.disabled = true;
            btnPost.style.opacity = '0.5';
            btnPost.style.cursor = 'not-allowed';
            btnPost.innerText = "⚡ Post GL Updates & Finalize";
        }
    }

    function updateGLPostingButtonState() {
        updateSelectedCollectionsStats();
    }

    function postSelectedCollectionsGL() {
        const checkboxes = document.querySelectorAll('.collection-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (ids.length === 0) return;
        
        // Construct bank allocations, custom debit/credits mapping
        const bankAllocations = {};
        const debitAccounts = {};
        const creditAccounts = {};
        let missingSelection = false;
        let firstMissingSelect = null;
        
        checkboxes.forEach(cb => {
            const paymentId = cb.value;
            
            // Extract custom debit select value
            const debitSel = document.querySelector(`.payment-debit-select[data-payment-id="${paymentId}"]`);
            if (debitSel) {
                debitAccounts[paymentId] = parseInt(debitSel.value);
            }
            
            // Extract custom credit select value
            const creditSel = document.querySelector(`.payment-credit-select[data-payment-id="${paymentId}"]`);
            if (creditSel) {
                creditAccounts[paymentId] = parseInt(creditSel.value);
            }

            if (cb.getAttribute('data-method') === 'Bank Transfer') {
                const selectEl = document.querySelector(`.payment-bank-select[data-payment-id="${paymentId}"]`);
                if (selectEl) {
                    if (!selectEl.value) {
                        missingSelection = true;
                        if (!firstMissingSelect) {
                            firstMissingSelect = selectEl;
                        }
                        selectEl.style.borderColor = '#c62828';
                        selectEl.style.background = '#ffebee';
                    } else {
                        bankAllocations[paymentId] = parseInt(selectEl.value);
                    }
                }
            }
        });
        
        if (missingSelection) {
            alert("⚠️ Validation Error: You have selected Bank Transfer collections that do not have a Target Commercial Bank selected. Please specify a bank account for each checked Bank Transfer payment card.");
            if (firstMissingSelect) {
                firstMissingSelect.focus();
                firstMissingSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        
        if (!confirm(`Are you sure you want to finalize and post GL double entries for the ${ids.length} selected collections? This will immediately update your Chart of Accounts balances.`)) {
            return;
        }

        const btnPost = document.getElementById('btnPostGL');
        btnPost.disabled = true;
        btnPost.innerText = "Posting... ⚡";

        fetch('<?= APP_URL ?>/RepTracking/api_finalize_collections', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                payment_ids: ids,
                bank_allocations: bankAllocations,
                debit_accounts: debitAccounts,
                credit_accounts: creditAccounts
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Success: " + data.message);
                window.location.href = '<?= APP_URL ?>/RepTracking?route_id=' + currentRouteId;
            } else {
                alert("⚠️ Error posting collections: " + data.message);
                btnPost.disabled = false;
                btnPost.innerText = "⚡ Post GL Updates & Finalize";
                updateSelectedCollectionsStats();
            }
        })
        .catch(err => {
            alert("Failed to post collections due to network or connection error.");
            btnPost.disabled = false;
            btnPost.innerText = "⚡ Post GL Updates & Finalize";
            updateSelectedCollectionsStats();
        });
    }

    // --- 5. NEW: Redirect to Sales Order Creation ---
    function redirectToAddInvoice() {
        if (currentRouteId) {
            window.location.href = '<?= APP_URL ?>/salesorder/create?rep_route_id=' + currentRouteId;
        }
    }

    // Auto-refresh interval for immediate data visibility post-sync/post-creation
    setInterval(() => {
        if (currentRouteId && document.visibilityState === 'visible') {
            // Fetch silently without showing the loader overlay to avoid UI blinking
            fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + currentRouteId)
                .then(response => response.json())
                .then(data => {
                    if (currentRouteTab === 'invoices') {
                        const tbody = document.getElementById('billsTableBody');
                        let newHtml = '';
                        if (data.bills.length === 0) {
                            newHtml = '<tr><td colspan="5" style="text-align:center; padding:30px; color:#888;">No bills generated on this route.</td></tr>';
                        } else {
                            data.bills.forEach(bill => {
                                let time = new Date(bill.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                let statColor = bill.status === 'Paid' ? '#2e7d32' : (bill.status === 'Unpaid' ? '#ef6c00' : '#666');
                                newHtml += `
                                    <tr class="bill-row" onclick="openInvoiceSlider(${bill.id})">
                                        <td style="font-weight:bold; color:var(--primary);">${bill.invoice_number}</td>
                                        <td style="color:var(--text-muted);">${time}</td>
                                        <td style="font-weight:bold;">${bill.customer_name}</td>
                                        <td style="text-align:right; font-weight:bold; font-family:monospace;">${parseFloat(bill.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                        <td style="text-align:center;"><span style="color:${statColor}; font-weight:bold; font-size:11px; text-transform:uppercase;">${bill.status}</span></td>
                                    </tr>
                                `;
                            });
                        }
                        tbody.innerHTML = newHtml;
                    }
                })
                .catch(err => console.error("Silent refresh failed:", err));
        }
    }, 10000);

    window.addEventListener('pageshow', (event) => {
        if (currentRouteId) {
            const routeEl = document.getElementById('route_' + currentRouteId);
            if (routeEl) {
                loadRouteDetails(currentRouteId, routeEl);
            }
        }
    });

    function openMapModal() {
        if (!currentRouteId) return;
        document.getElementById('modalRouteName').innerText = document.getElementById('mhRouteName').innerText;
        document.getElementById('mapModalBackdrop').style.display = 'flex';
        
        // Trigger GPS route path load
        loadRoutePath(currentRouteId);
        
        // Leaflet needs size invalidation when container is shown dynamically
        setTimeout(() => {
            if (routeMap) {
                routeMap.invalidateSize();
            }
        }, 300);
    }

    function closeMapModal() {
        document.getElementById('mapModalBackdrop').style.display = 'none';
    }

    // Left pane routing tabs system
    let currentLeftFilter = 'active';
    function filterLeftPane(type, btn) {
        currentLeftFilter = type;
        
        document.querySelectorAll('.left-tab-btn').forEach(b => {
            b.style.background = 'transparent';
            b.style.color = 'var(--text-muted)';
            b.classList.remove('active');
        });
        
        if (btn) {
            btn.style.background = '#0066cc';
            btn.style.color = 'white';
            btn.classList.add('active');
        }

        const items = document.querySelectorAll('.route-item');
        let firstVisible = null;
        
        items.forEach(item => {
            if (item.getAttribute('data-route-type') === type) {
                item.style.display = 'block';
                if (!firstVisible) firstVisible = item;
            } else {
                item.style.display = 'none';
            }
        });

        const activeSelected = document.querySelector('.route-item.active');
        if (activeSelected && activeSelected.style.display === 'none') {
            activeSelected.classList.remove('active');
            const midHeader = document.getElementById('midHeader');
            if (midHeader) midHeader.style.visibility = 'hidden';
            
            const routeTabs = document.getElementById('routeTabs');
            if (routeTabs) routeTabs.style.display = 'none';
            
            const midEmptyState = document.getElementById('midEmptyState');
            if (midEmptyState) midEmptyState.style.display = 'flex';
            
            const billsTable = document.getElementById('billsTable');
            if (billsTable) billsTable.style.display = 'none';
            
            const collectionsWrapper = document.getElementById('collectionsWrapper');
            if (collectionsWrapper) collectionsWrapper.style.display = 'none';
            
            const btnViewMap = document.getElementById('btnViewMap');
            if (btnViewMap) btnViewMap.style.display = 'none';
            
            currentRouteId = null;
        }

        if (firstVisible) {
            firstVisible.click();
        }
    }

    // --- 6. NEW: Auto-select route on page load if route_id is passed in URL ---
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const routeId = urlParams.get('route_id');
        if (routeId) {
            const routeEl = document.getElementById('route_' + routeId);
            if (routeEl) {
                const type = routeEl.getAttribute('data-route-type');
                let btnId = 'btnLeftActive';
                if (type === 'pending_finalization') btnId = 'btnLeftPendingGL';
                else if (type === 'pending_delivery') btnId = 'btnLeftPendingDelivery';
                
                const btn = document.getElementById(btnId);
                filterLeftPane(type, btn);
                routeEl.click();
                return;
            }
        }
        
        filterLeftPane('active', document.getElementById('btnLeftActive'));
    });

    // --- NEW: Sales Order Deletion & Invoice Attachment Methods ---
    function deleteSalesOrder() {
        const editLink = document.getElementById('btnEditInvoice').href;
        const matches = editLink.match(/\/sales\/edit\/(\d+)/);
        if (!matches || !matches[1]) return;
        
        const invoiceId = parseInt(matches[1]);
        
        if (!confirm("⚠️ WARNING: Are you sure you want to delete this Sales Order permanently? This will release all locked stock inventory reservations!")) {
            return;
        }
        
        fetch('<?= APP_URL ?>/RepTracking/api_delete_sales_order/' + invoiceId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Deleted: " + data.message);
                closeInvoiceSlider();
                
                // Reload route list to show updated totals and stats
                window.location.reload();
            } else {
                alert("⚠️ Error: " + data.message);
            }
        })
        .catch(err => {
            alert("Failed to delete sales order due to connection error.");
            console.error(err);
        });
    }

    function openAttachInvoiceModal() {
        if (!currentRouteId) {
            alert("Please select a daily rep route first.");
            return;
        }
        document.getElementById('attachInvoiceModal').style.display = 'flex';
        document.getElementById('invoiceSearchInput').value = '';
        if (document.getElementById('soFilterStartDate')) document.getElementById('soFilterStartDate').value = '';
        if (document.getElementById('soFilterEndDate')) document.getElementById('soFilterEndDate').value = '';
        if (document.getElementById('soFilterStatus')) document.getElementById('soFilterStatus').value = '';
        searchUnattachedInvoices();
    }

    function closeAttachInvoiceModal() {
        document.getElementById('attachInvoiceModal').style.display = 'none';
    }

    function resetSalesOrderFilters() {
        document.getElementById('invoiceSearchInput').value = '';
        if (document.getElementById('soFilterStartDate')) document.getElementById('soFilterStartDate').value = '';
        if (document.getElementById('soFilterEndDate')) document.getElementById('soFilterEndDate').value = '';
        if (document.getElementById('soFilterStatus')) document.getElementById('soFilterStatus').value = '';
        searchUnattachedInvoices();
    }

    function searchUnattachedInvoices() {
        const query = document.getElementById('invoiceSearchInput').value;
        const startDate = document.getElementById('soFilterStartDate') ? document.getElementById('soFilterStartDate').value : '';
        const endDate = document.getElementById('soFilterEndDate') ? document.getElementById('soFilterEndDate').value : '';
        const status = document.getElementById('soFilterStatus') ? document.getElementById('soFilterStatus').value : '';
        
        const container = document.getElementById('unattachedInvoicesContainer');
        container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">Searching... ⏳</p>';
        
        let url = '<?= APP_URL ?>/RepTracking/api_get_unattached_invoices?search=' + encodeURIComponent(query);
        if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
        if (endDate) url += '&end_date=' + encodeURIComponent(endDate);
        if (status) url += '&status=' + encodeURIComponent(status);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success' || !data.invoices || data.invoices.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">No unattached sales orders found matching search criteria.</p>';
                    return;
                }
                
                let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';
                data.invoices.forEach(inv => {
                    let amtFormatted = parseFloat(inv.true_grand_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    let statusBadge = `<span style="font-size: 10px; font-weight: bold; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">${inv.status}</span>`;
                    html += `
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; padding: 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 0;">
                            <input type="checkbox" class="attach-invoice-checkbox" value="${inv.id}" style="width: 16px; height: 16px; margin-top: 2px;">
                            <div style="flex: 1;">
                                <div style="font-weight: bold; color: #333;">${inv.invoice_number} ${statusBadge}</div>
                                <div style="font-size: 11px; color: #666;">
                                    Customer: <strong>${inv.customer_name}</strong> | Date: ${inv.invoice_date}
                                </div>
                            </div>
                            <div style="font-weight: bold; font-family: monospace; color: #2e7d32;">Rs ${amtFormatted}</div>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<p style="text-align: center; color: #c62828; margin: 10px 0;">Failed to load sales orders.</p>';
                console.error(err);
            });
    }

    function confirmAttachInvoices() {
        const checkedInvoices = [];
        document.querySelectorAll('.attach-invoice-checkbox:checked').forEach(cb => {
            checkedInvoices.push(parseInt(cb.value));
        });
        
        if (checkedInvoices.length === 0) {
            alert("Please select at least one sales order to attach.");
            return;
        }
        
        closeAttachInvoiceModal();
        
        fetch('<?= APP_URL ?>/RepTracking/api_attach_invoices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                route_id: currentRouteId,
                invoice_ids: checkedInvoices
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Attached: " + data.message);
                
                // Reload route to update details immediately
                window.location.href = '<?= APP_URL ?>/RepTracking?route_id=' + currentRouteId;
            } else {
                alert("⚠️ Error: " + data.message);
            }
        })
        .catch(err => {
            alert("Failed to attach sales orders due to connection error.");
            console.error(err);
        });
    }

    // --- 9. Route Binding Panel Handlers ---
    function getEligibleBindingRoutes() {
        const routes = [];
        document.querySelectorAll('.route-item').forEach(item => {
            if (item.getAttribute('data-route-type') === 'pending_delivery') {
                const id = item.id.replace('route_', '');
                const dataDiv = document.getElementById('route_data_' + id);
                if (dataDiv) {
                    const name = dataDiv.getAttribute('data-rname');
                    const rep = dataDiv.getAttribute('data-rep');
                    routes.push({ id: parseInt(id), name: name, rep: rep });
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
                ${index > 2 ? `<button type="button" onclick="removeBindingSlot(${index})" style="position: absolute; top: 10px; right: 10px; border: none; background: #dc2626; color: #fff; width: 22px; height: 22px; border-radius: 50%; cursor: pointer; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: bold; padding:0; line-height: 22px; z-index: 10;">✕</button>` : ''}
                <h5 style="margin: 0 0 5px 0; color: #3f51b5; font-size: 12px; font-weight: bold; text-transform: uppercase;">Slot ${index}</h5>
                <div class="rb-slot-box">
                    <div style="font-size: 20px; color: #cbd5e1; margin-bottom: 6px;" id="rb_slot_icon_${index}">➕</div>
                    <select class="rb-slot-select" id="rb_select_${index}" onchange="onBindingSlotRouteSelect(${index}, this)">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="rb-bill-list" id="rb_bills_${index}">
                    <!-- Populated dynamically -->
                </div>
            </div>
        `;
        
        document.getElementById('rbSlotsContainer').insertAdjacentHTML('beforeend', slotHtml);
    }

    function removeBindingSlot(index) {
        const el = document.getElementById(`rb_slot_col_${index}`);
        if (el) {
            el.remove();
        }
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
        billsContainer.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">Loading bills... ⏳</p>';
        
        fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success' || !data.bills || data.bills.length === 0) {
                    billsContainer.innerHTML = '<p style="text-align: center; color: #888; margin: 10px 0;">No sales orders in this route.</p>';
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
            })
            .catch(err => {
                billsContainer.innerHTML = '<p style="text-align: center; color: #dc2626; margin: 10px 0;">Failed to load bills.</p>';
                console.error(err);
            });
    }

    function submitRouteBinding() {
        const boundName = document.getElementById('rbBoundName').value.trim();
        if (!boundName) {
            alert("Please enter a custom name for the bound route.");
            return;
        }
        
        const routeIds = [];
        document.querySelectorAll('.rb-slot-select').forEach(select => {
            if (select.value) {
                routeIds.push(parseInt(select.value));
            }
        });
        
        const uniqueRouteIds = [...new Set(routeIds)];
        if (uniqueRouteIds.length < 2) {
            alert("Please select at least 2 distinct routes to bind.");
            return;
        }
        if (uniqueRouteIds.length !== routeIds.length) {
            alert("Please make sure you do not select the same route in multiple slots.");
            return;
        }
        
        if (!confirm(`Are you sure you want to bind these ${uniqueRouteIds.length} routes together under the name "${boundName}"?`)) {
            return;
        }
        
        fetch('<?= APP_URL ?>/RepTracking/api_create_binding', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                binding_name: boundName,
                route_ids: uniqueRouteIds
            })
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
        })
        .catch(err => {
            alert("Failed to save route binding.");
            console.error(err);
        });
    }

    function unbindActiveRoute() {
        const btnUnbind = document.getElementById('btnUnbindRoute');
        const bindingId = btnUnbind ? btnUnbind.getAttribute('data-binding-id') : null;
        if (!bindingId) {
            alert("No active route binding identified.");
            return;
        }

        if (!confirm("Are you sure you want to undo this route binding? The routes will be separated back to their original states and listed individually.")) {
            return;
        }

        fetch('<?= APP_URL ?>/RepTracking/api_unbind_route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                binding_id: parseInt(bindingId)
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("🎉 Success: " + data.message);
                window.location.reload();
            } else {
                alert("⚠️ Error: " + data.message);
            }
        })
        .catch(err => {
            alert("Failed to undo route binding.");
            console.error(err);
        });
    }
</script>