<!-- Centered Invoice Popup Modal -->
    <!-- Global Context Menu Backdrop Blur -->
    <div id="menuBackdrop" onclick="closeAllDotsMenus()" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0, 0, 0, 0.08); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); transition: all 0.2s ease;"></div>

    <div class="modal-backdrop" id="invoiceSliderBackdrop" style="display: none; z-index: 2000;">
        <div class="modal-panel" style="max-width: 950px; width: 90%; height: 85vh; display: flex; flex-direction: column; position: relative;">
            <button onclick="closeInvoiceSlider()" style="position: absolute; top: 12px; right: 16px; background: var(--c-fill); border: none; font-size: 16px; font-weight: bold; color: var(--t-secondary); cursor: pointer; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10;">✕</button>
            <iframe id="invoiceIframe" src="about:blank" style="width: 100%; flex: 1; border: none; border-radius: var(--r-lg); background: #fff;"></iframe>
        </div>
    </div>

    <!-- Secure Delete Confirmation Modal -->
    <div class="modal-backdrop" id="deleteConfirmModal" style="display: none; z-index: 2005;">
        <div class="modal-panel">
            <div class="modal-header" style="background: var(--c-red-light); color: var(--c-red); border-bottom: 0.5px solid var(--c-separator); padding: 16px 20px;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="ph ph-warning-octagon"></i> Secure Deletion</h3>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                <p style="margin:0; font-size:13px; color:var(--t-secondary);">
                    You are about to delete Sales Order <strong id="deleteTargetInvNum" style="color:var(--t-primary);"></strong>. This will permanently reverse general ledger entries and restore stock to inventory.
                </p>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:4px;">Administrator Password *</label>
                    <input type="password" id="deleteConfirmPassword" placeholder="Enter password" style="width:100%; padding:10px; border:1px solid var(--c-separator); border-radius:var(--r-md); background:var(--c-bg); color:var(--t-primary); font-size:13px;" required>
                </div>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:4px;">Reason for Deletion *</label>
                    <textarea id="deleteConfirmReason" placeholder="Reason (e.g. Cancelled by customer)" style="width:100%; height:70px; padding:10px; border:1px solid var(--c-separator); border-radius:var(--r-md); background:var(--c-bg); color:var(--t-primary); font-size:13px; resize:none;" required></textarea>
                </div>
            </div>
            <div style="padding: 12px 20px; background: var(--c-surface2); border-top: 0.5px solid var(--c-separator); display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="closeDeleteConfirmModal()" style="padding:8px 16px; background:var(--c-fill); border:1px solid var(--c-separator); color:var(--t-secondary); border-radius:var(--r-sm); font-size:12px; font-weight:bold; cursor:pointer;">Cancel</button>
                <button onclick="submitDeleteSalesOrder()" style="padding:8px 16px; background:var(--c-red); border:none; color:#fff; border-radius:var(--r-sm); font-size:12px; font-weight:bold; cursor:pointer;">Permanently Delete</button>
            </div>
        </div>
    </div>

    <!-- Secure Delete Route Confirmation Modal -->
    <div class="modal-backdrop" id="deleteRouteModal" style="display: none; z-index: 2005;">
        <div class="modal-panel" style="max-width: 500px; width: 90%;">
            <div class="modal-header" style="background: var(--c-red-light); color: var(--c-red); border-bottom: 0.5px solid var(--c-separator); padding: 16px 20px;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="ph ph-warning-octagon"></i> Secure Route Deletion</h3>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                <p style="margin:0; font-size:13px; color:var(--t-secondary);">
                    You are about to delete Daily Route <strong id="deleteRouteTargetNum" style="color:var(--t-primary);"></strong>. Please choose how you want to handle the associated daily transactions:
                </p>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:8px;">Deletion Mode *</label>
                    <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                            <input type="radio" name="deleteRouteMode" value="detach" style="margin-top:3px;" checked>
                            <div>
                                <strong>1. Delete Only Route (Preserve Sales Orders)</strong>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;">
                                    The route will be deleted, but all invoices/sales orders, payments, cheques, and deliveries will be preserved by detaching them from the route.
                                </div>
                            </div>
                        </label>
                        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                            <input type="radio" name="deleteRouteMode" value="delete_with_so" style="margin-top:3px;">
                            <div>
                                <strong>2. Delete Route and Delete/Void Sales Orders</strong>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;">
                                    The route will be deleted, and all associated Sales Orders/Invoices will be permanently deleted (reversing stock and ledger postings). Payments/cheques/deliveries will be detached.
                                </div>
                            </div>
                        </label>
                        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                            <input type="radio" name="deleteRouteMode" value="force_delete_all" style="margin-top:3px;">
                            <div>
                                <strong>3. Force Delete Route & All Associated Records</strong>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;">
                                    The route, associated invoices, payments/cheques, deliveries, and collections will be permanently deleted from the database.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:4px;">Administrator Password *</label>
                    <input type="password" id="deleteRoutePassword" placeholder="Enter password" style="width:100%; padding:10px; border:1px solid var(--c-separator); border-radius:var(--r-md); background:var(--c-bg); color:var(--t-primary); font-size:13px;" required>
                </div>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:4px;">Security CAPTCHA *</label>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                        <span id="deleteRouteCaptchaQuestion" style="font-weight:bold; font-size:13px; color:var(--t-primary); background:var(--c-surface2); padding:8px 12px; border:1px dashed var(--c-separator); border-radius:var(--r-sm); flex-grow:1; text-align:center;">Loading CAPTCHA...</span>
                        <button type="button" onclick="refreshDeleteRouteCaptcha()" style="background:none; border:none; color:var(--c-blue); font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Refresh CAPTCHA"><i class="ph ph-arrows-clockwise"></i></button>
                    </div>
                    <input type="text" id="deleteRouteCaptchaAnswer" placeholder="Enter CAPTCHA answer" style="width:100%; padding:10px; border:1px solid var(--c-separator); border-radius:var(--r-md); background:var(--c-bg); color:var(--t-primary); font-size:13px;" required>
                </div>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:4px;">Reason for Deletion *</label>
                    <textarea id="deleteRouteReason" placeholder="Reason (e.g. Route created by mistake)" style="width:100%; height:70px; padding:10px; border:1px solid var(--c-separator); border-radius:var(--r-md); background:var(--c-bg); color:var(--t-primary); font-size:13px; resize:none;" required></textarea>
                </div>
            </div>
            <div style="padding: 12px 20px; background: var(--c-surface2); border-top: 0.5px solid var(--c-separator); display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="closeDeleteRouteModal()" style="padding:8px 16px; background:var(--c-fill); border:1px solid var(--c-separator); color:var(--t-secondary); border-radius:var(--r-sm); font-size:12px; font-weight:bold; cursor:pointer;">Cancel</button>
                <button onclick="submitDeleteRoute()" style="padding:8px 16px; background:var(--c-red); border:none; color:#fff; border-radius:var(--r-sm); font-size:12px; font-weight:bold; cursor:pointer;">Permanently Delete Route</button>
            </div>
        </div>
    </div>

    <!-- Move Sales Order Modal -->
    <div class="modal-backdrop" id="moveInvoiceModal" style="display: none; z-index: 2005;">
        <div class="modal-panel">
            <div class="modal-header" style="background: var(--c-blue-light); color: var(--c-blue); border-bottom: 0.5px solid var(--c-separator); padding: 16px 20px;">
                <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="ph ph-arrow-square-out"></i> Move Sales Order</h3>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                <p style="margin:0; font-size:13px; color:var(--t-secondary);">
                    Select the destination route to move Sales Order <strong id="moveTargetInvNum" style="color:var(--t-primary);"></strong> to.
                </p>
                <div>
                    <label style="font-weight:bold; font-size:11px; text-transform:uppercase; color:var(--t-secondary); display:block; margin-bottom:4px;">Destination Route *</label>
                    <select id="moveDestinationRouteSelect" style="width:100%; padding:10px; border:1px solid var(--c-separator); border-radius:var(--r-md); background:var(--c-bg); color:var(--t-primary); font-size:13px;" required>
                        <option value="">-- Select Destination Route --</option>
                    </select>
                </div>
            </div>
            <div style="padding: 12px 20px; background: var(--c-surface2); border-top: 0.5px solid var(--c-separator); display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="closeMoveInvoiceModal()" style="padding:8px 16px; background:var(--c-fill); border:1px solid var(--c-separator); color:var(--t-secondary); border-radius:var(--r-sm); font-size:12px; font-weight:bold; cursor:pointer;">Cancel</button>
                <button onclick="submitMoveSalesOrder()" style="padding:8px 16px; background:var(--c-blue); border:none; color:#fff; border-radius:var(--r-sm); font-size:12px; font-weight:bold; cursor:pointer;">Move Sales Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic GPS Map Modal -->
<div class="modal-backdrop" id="mapModalBackdrop">
    <div class="modal-panel" style="max-width: 950px; width: 90%; height: 80vh; display: flex; flex-direction: column;">
        <div style="background: #3f51b5; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 15px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span><i class="ph ph-map-pin"></i> GPS Route Path Tracking</span>
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

<!-- Create Manual Route Modal -->
<div class="modal-backdrop" id="createManualRouteModal">
    <div class="modal-panel" style="max-width: 480px; width: 95%;">
        <div class="modal-header" style="background: #2e7d32;">
            <span><i class="ph ph-plus-circle"></i> Create Route Manually</span>
            <button onclick="closeCreateRouteModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <form action="<?= APP_URL ?>/RepTracking/create_route_manual" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div>
                    <label for="mrRep">Select Representative *</label>
                    <select name="user_id" id="mrRep" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: white;" required>
                        <option value="">-- Select Rep --</option>
                        <?php foreach($data['reps'] as $rep): ?>
                            <option value="<?= $rep->id ?>"><?= htmlspecialchars(($rep->first_name ? $rep->first_name . ' ' . $rep->last_name : $rep->username)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="mrRouteName">Route Name (Territory/Area) *</label>
                    <input list="mca_areas_list" name="route_name" id="mrRouteName" placeholder="Select or type route name..." style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: white;" required autocomplete="off">
                    <datalist id="mca_areas_list">
                        <?php foreach($data['mca_areas'] as $area): ?>
                            <option value="<?= htmlspecialchars($area->name) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label for="mrStartMeter">Starting Odometer / Meter *</label>
                    <input type="number" step="0.1" name="start_meter" id="mrStartMeter" value="0.0" min="0" max="999999" oninput="if(this.value.length > 6) this.value = this.value.slice(0,6);" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: white;" required>
                </div>
                <div>
                    <label for="mrStartTime">Start Date & Time *</label>
                    <input type="datetime-local" name="start_time" id="mrStartTime" value="<?= date('Y-m-d\TH:i') ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; background: white;" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="qb-btn" onclick="closeCreateRouteModal()" style="border:1px solid #ccc; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
                <button type="submit" class="qb-btn" style="background:#2e7d32; color:#fff; border:none; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;"><i class="ph ph-lightning"></i> Create Route</button>
            </div>
        </form>
    </div>
</div>

<!-- Route Multi-Binding Modal -->
<div class="modal-backdrop" id="routeBindingModal">
    <div class="modal-panel" style="max-width: 900px; width: 95%; max-height: 90vh;">
        <div class="modal-header" style="background: #3f51b5;">
            <span><i class="ph ph-link"></i> Rep Route Multi-Binding Panel</span>
            <button onclick="closeRouteBindingModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body" style="overflow-y:auto; flex:1;">
            <div style="margin-bottom: 20px;">
                <label>Custom Name for Bound Group</label>
                <input type="text" id="rbBoundName" placeholder="e.g. Western Route Combined - June 15">
            </div>
            <label>Route Slots</label>
            <div id="rbSlotsContainer" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px,1fr)); gap:15px; margin-bottom:15px;"></div>
            <button type="button" onclick="addBindingSlot()" style="background: #eef2ff; color: #3f51b5; border: 1px dashed #3f51b5; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; display: block; width: 100%;"><i class="ph ph-plus-circle"></i> Add Route Slot</button>
        </div>
        <div class="modal-footer">
            <button class="qb-btn" onclick="closeRouteBindingModal()" style="border:1px solid #ccc; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="submitRouteBinding()" style="background:#2e7d32; color:#fff; border:none; padding:8px 18px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;"><i class="ph ph-lightning"></i> Confirm & Create Route Binding</button>
        </div>
    </div>
</div>

<!-- Attach Sales Order Modal -->
<div class="modal-backdrop" id="attachInvoiceModal">
    <div class="modal-panel" style="max-width: 580px; width: 90%;">
        <div class="modal-header" style="background: #5c6bc0;">
            <span><i class="ph ph-link"></i> Attach Sales Orders to Route</span>
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

<!-- Route Switcher Modal -->
<div class="modal-backdrop" id="routeSwitcherModalBackdrop">
    <div class="modal-panel" style="max-width: 550px; width: 90%; max-height: 80vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background: #3f51b5; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; color: #fff; font-weight: bold;">
            <span><i class="ph ph-swap"></i> Switch Route</span>
            <button onclick="closeRouteSwitcherModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div style="padding: 15px; border-bottom: 1px solid #e2e8f0; background: #fff;">
            <input type="text" id="routeSwitcherSearchInput" placeholder="Search routes by name, rep..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;" oninput="searchRouteSwitcherList()" />
        </div>
        <div class="modal-body" style="overflow-y: auto; flex: 1; padding: 15px; display: flex; flex-direction: column; gap: 10px; background: #fafafa;" id="routeSwitcherItemsContainer">
            <?php foreach($data['routes'] as $route): ?>
                <div class="switcher-route-item" onclick="selectRouteFromSwitcher(<?= $route->id ?>)" style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s;" data-rname="<?= htmlspecialchars($route->route_name) ?>" data-rep="<?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <strong style="color: #3f51b5; font-size: 12px; font-family: monospace;">#RT-<?= str_pad($route->id, 5, '0', STR_PAD_LEFT) ?></strong>
                        <span style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: #e2e8f0; font-weight: bold; color: #555;"><?= htmlspecialchars($route->status) ?></span>
                    </div>
                    <div style="font-weight: bold; font-size: 13px; color: #333;"><?= htmlspecialchars($route->route_name) ?></div>
                    <div style="font-size: 11px; color: #666;">Rep: <?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Server-side Delivery Process Modal -->
<div class="modal-backdrop" id="serverDeliveryProcessModal" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-panel" style="max-width: 1100px; width: 95%; height: 85vh; max-height: 85vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background: #0066cc; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; color: #fff; font-weight: bold; flex-shrink: 0;">
            <span><i class="ph ph-steering-wheel"></i> Process Visit: <span id="sdpCustomerName"></span></span>
            <button onclick="closeServerDeliveryProcessModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <div class="modal-body" style="flex: 1; padding: 20px; display: flex !important; flex-direction: row !important; gap: 20px; background: #fafafa; overflow: hidden !important; min-height: 0;">
            
            <!-- Left Column: Adjust Invoice Items (Bill Quantity) -->
            <div style="flex: 1.3; display: flex; flex-direction: column; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; height: 100%; min-height: 0;">
                <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #333; display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                    <i class="ph ph-package" style="color:#0066cc;"></i> Adjust Invoice Items (Bill Quantity)
                </h4>
                
                <!-- Search Option -->
                <div style="margin-bottom: 10px; flex-shrink: 0;">
                    <div style="position: relative;">
                        <input type="text" id="sdpItemSearch" placeholder="Search item description..." oninput="filterSdpItems()"
                               style="width: 100%; padding: 8px 12px 8px 35px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; outline: none; transition: border 0.2s;" />
                        <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 14px;"></i>
                    </div>
                </div>
                
                <div style="border: 1px solid #e2e8f0; border-radius: 6px; flex: 1; overflow-y: auto; min-height: 0;">
                    <table class="data-table" style="margin-top: 0; font-size: 12px; width: 100%;">
                        <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 5;">
                            <tr>
                                <th>Item Description</th>
                                <th style="text-align: right; width: 80px;">Loaded</th>
                                <th style="text-align: right; width: 120px;">Delivered Qty</th>
                            </tr>
                        </thead>
                        <tbody id="sdpItemsTbody">
                            <!-- Populated in JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Column: Visit Status, Outstanding, and Collections on a separate panel -->
            <div style="flex: 1; display: flex; flex-direction: column; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; gap: 15px; height: 100%; min-height: 0; overflow-y: auto;">
                <!-- Hidden details -->
                <input type="hidden" id="sdpInvoiceId" />
                <input type="hidden" id="sdpCustomerId" />

                <!-- Visit Status & Info -->
                <div style="background: #f8fafc; padding: 12px 15px; border-radius: 6px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-shrink: 0;">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: #888; font-weight: bold;">Processing Invoice</div>
                        <strong id="sdpInvoiceNumber" style="font-size: 15px; color: #0066cc;"></strong>
                    </div>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: #888; font-weight: bold;">Delivery Status</div>
                        <select id="sdpDeliveryStatus" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 13px;">
                            <option value="Pending">Pending</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Postponed">Postponed</option>
                        </select>
                    </div>
                </div>

                <!-- Arrears & Outstanding Info -->
                <div id="sdpArrearsInfoBox" style="background: #fffbeb; padding: 12px 15px; border-radius: 6px; border: 1px solid #fef3c7; color: #b45309; font-size: 13px; font-weight: 500; flex-shrink: 0;">
                    <i class="ph ph-warning"></i> Customer Outstanding Balance: <strong id="sdpOutstandingArrears">Rs 0.00</strong>
                </div>

                <!-- Collections Section (Record Payments & Credit Collections) -->
                <div style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                    <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: bold; color: #333; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; flex-shrink: 0;">
                        <i class="ph ph-coins" style="color:#2e7d32;"></i> Record Payments & Collections
                    </h4>
                    
                    <!-- Balance Summary Row -->
                    <div style="background: #f8fafc; padding: 10px 12px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 15px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; font-size: 11px; flex-shrink: 0;">
                        <div>
                            <div style="color: #64748b; font-weight: bold;">INVOICE TOTAL</div>
                            <strong id="sdpInvoiceTotal" style="font-size: 12px; color: #334155;">Rs 0.00</strong>
                        </div>
                        <div>
                            <div style="color: #64748b; font-weight: bold;">TOTAL COLLECTED</div>
                            <strong id="sdpTotalCollected" style="font-size: 12px; color: #2e7d32;">Rs 0.00</strong>
                        </div>
                        <div>
                            <div style="color: #64748b; font-weight: bold;">REMAINING BALANCE</div>
                            <strong id="sdpRemainingBalance" style="font-size: 12px; color: #c62828;">Rs 0.00</strong>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; flex-shrink: 0;">
                        <div>
                            <label style="font-size: 11px; font-weight: bold; color: #475569; display: block; margin-bottom: 4px;">Cash Amount (Rs)</label>
                            <input type="number" step="0.01" min="0" id="sdpCashAmount" oninput="updateSdpBalance()" style="width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size: 12px;" value="0.00">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: bold; color: #475569; display: block; margin-bottom: 4px;">Bank Transfer (Rs)</label>
                            <input type="number" step="0.01" min="0" id="sdpBankAmount" oninput="updateSdpBalance()" style="width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size: 12px;" value="0.00">
                        </div>
                    </div>

                    <!-- Cheques list section -->
                    <div style="border-top: 1px dashed #e2e8f0; padding-top: 15px; flex: 1; overflow-y: auto; min-height: 0;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-shrink: 0;">
                            <strong style="font-size:12px; color:#555;">Cheque Collections</strong>
                            <button type="button" onclick="addSdpChequeRow()" class="btn-premium secondary" style="padding:4px 8px; font-size:11px; cursor:pointer;"><i class="ph ph-plus"></i> Add Cheque</button>
                        </div>
                        <div id="sdpChequesContainer" style="display:flex; flex-direction:column; gap:10px;">
                            <!-- Cheque rows go here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
            <button class="qb-btn" onclick="closeServerDeliveryProcessModal()" style="border:1px solid #ccc; padding:8px 18px; border-radius:6px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="submitServerDeliveryProcess()" style="background:#0066cc; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-size:12px; cursor:pointer; font-weight: bold;">Save & Process Visit</button>
        </div>
    </div>
</div>
