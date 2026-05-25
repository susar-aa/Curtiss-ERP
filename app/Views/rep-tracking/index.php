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

    /* Right Pane: Slide-out Invoice Mini-View */
    .pane-right-slider { 
        position: absolute; top: 0; right: 0; bottom: 0; width: 500px; 
        background: var(--surface); border-left: 1px solid var(--border); 
        box-shadow: -5px 0 25px rgba(0,0,0,0.1); transform: translateX(100%); 
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 50;
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
</style>

<div class="header-actions" style="margin-bottom: 15px;">
    <div>
        <h2 style="margin: 0 0 5px 0;">Rep Route Tracking & Audits</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Click a route to view bills and the GPS path from day start through each invoice to day end.</p>
    </div>
</div>

<div class="app-workspace">
    <!-- Left Pane: Routes Master List -->
    <div class="pane-left" style="overflow-y: auto;">
        <?php foreach($data['routes'] as $route): ?>
            <div class="route-item" id="route_<?= $route->id ?>" onclick="loadRouteDetails(<?= $route->id ?>, this)">
                <div class="r-title"><span class="status-dot status-<?= $route->status ?>"></span> <?= htmlspecialchars($route->route_name) ?></div>
                <div class="r-sub">Rep: <?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?></div>
                <div class="r-meta">
                    <span><?= date('M d, Y', strtotime($route->start_time)) ?></span>
                    <strong style="color: <?= $route->status == 'Completed' ? 'inherit' : '#ef6c00' ?>;">Rs: <?= number_format($route->total_sales, 2) ?></strong>
                </div>
            </div>
            
            <!-- Hidden data payload to populate the middle header quickly -->
            <div id="route_data_<?= $route->id ?>" style="display:none;" 
                 data-rep="<?= htmlspecialchars($route->first_name . ' ' . $route->last_name) ?>"
                 data-rname="<?= htmlspecialchars($route->route_name) ?>"
                 data-start="<?= $route->start_meter ?>"
                 data-end="<?= $route->end_meter ?: 'Active' ?>"
                 data-sales="<?= number_format($route->total_sales, 2) ?>"
                 data-bills="<?= $route->bill_count ?>">
            </div>
        <?php endforeach; ?>
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
                <button id="btnAddInvoice" onclick="redirectToAddInvoice()" style="padding: 10px 15px; border: none; background: #0066cc; color: #fff; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 5px;">➕ Add Invoice</button>
            </div>
        </div>

        <div style="flex:1; overflow-y:auto; position:relative;">
            <div class="empty-state" id="midEmptyState">
                <span>📍</span>
                Click a route on the left to load its bills.
            </div>

            <table class="data-table" id="billsTable" style="display: none;">
                <thead>
                    <tr>
                        <th>Invoice #</th>
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

        <!-- Route GPS path map -->
        <div class="pane-map-section" id="mapSection" style="display:none;">
            <div class="map-section-header">
                <h4>📍 Route Path <span id="pathPointCount" style="font-weight:normal; color:#888;"></span></h4>
                <div class="map-legend">
                    <span><i class="legend-dot legend-start"></i> Day start</span>
                    <span><i class="legend-dot legend-invoice"></i> Invoice</span>
                    <span><i class="legend-dot legend-end"></i> Day end</span>
                </div>
            </div>
            <div class="map-wrap">
                <div id="mapEmptyOverlay" class="map-empty-overlay">Select a route to load its path.</div>
                <div id="routePathMap"></div>
            </div>
            <div class="path-step-list" id="pathStepList" style="display:none;">
                <ol id="pathStepOl"></ol>
            </div>
        </div>
    </div>

    <!-- Right Pane: Invoice Slide-Out Viewer -->
    <div class="pane-right-slider" id="invoiceSlider">
        <div class="slider-header">
            <span>Invoice Mini-View</span>
            <div>
                <!-- Edit Invoice Button -->
                <a id="btnEditInvoice" href="#" style="background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 15px; border: 1px solid rgba(255,255,255,0.4);">✏️ Edit in Sales</a>
                <button class="close-slider" onclick="closeInvoiceSlider()">✕</button>
            </div>
        </div>
        <!-- Iframe loads the actual generated PDF view -->
        <iframe id="invoiceIframe" src="about:blank"></iframe>
    </div>
</div>

<!-- NEW: Delivery Arrangement Metadata Setup Modal -->
<div class="modal-backdrop" id="deliveryModal">
    <div class="modal-panel">
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
        </div>
        <div class="modal-footer">
            <button class="qb-btn" onclick="closeDeliveryModal()" style="border:1px solid #ccc; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
            <button class="qb-btn" onclick="confirmArrangeDelivery()" style="background:#2e7d32; color:#fff; border-color:#2e7d32; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Go to Spreadsheet Summary &rarr;</button>
        </div>
    </div>
</div>

<script>
    let currentRouteId = null;
    let routeMap = null;
    let routeMapLayers = [];

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
        document.getElementById('mapSection').style.display = 'flex';
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
                li.title = 'Click to preview invoice';
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
        
        document.getElementById('midHeader').style.visibility = 'visible';
        document.getElementById('midEmptyState').style.display = 'none';
        document.getElementById('billsTable').style.display = 'none';
        document.getElementById('midLoader').style.display = 'block';

        // Close slider if open
        closeInvoiceSlider();

        loadRoutePath(routeId);

        // AJAX Fetch Bills
        fetch('<?= APP_URL ?>/RepTracking/api_get_route_details/' + routeId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('midLoader').style.display = 'none';
                document.getElementById('billsTable').style.display = 'table';
                
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
        document.getElementById('btnEditInvoice').href = '<?= APP_URL ?>/sales/edit/' + invoiceId;

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
    }

    function closeDeliveryModal() {
        document.getElementById('deliveryModal').style.display = 'none';
    }

    function confirmArrangeDelivery() {
        const date = document.getElementById('daDate').value;
        const vehicle = document.getElementById('daVehicle').value;
        const driver = document.getElementById('daDriver').value;
        const partner = document.getElementById('daPartner').value;

        if (!vehicle) {
            alert("Please select a vehicle.");
            return;
        }
        if (!driver) {
            alert("Please select a driver.");
            return;
        }

        closeDeliveryModal();

        const payload = {
            rep_route_id: currentRouteId,
            delivery_date: date,
            vehicle_number: vehicle,
            driver_name: driver,
            partner_name: partner
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

    // --- 5. NEW: Redirect to Invoice Creation ---
    function redirectToAddInvoice() {
        if (currentRouteId) {
            window.location.href = '<?= APP_URL ?>/sales?rep_route_id=' + currentRouteId;
        }
    }

    // --- 6. NEW: Auto-select route on page load if route_id is passed in URL ---
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const routeId = urlParams.get('route_id');
        if (routeId) {
            const routeEl = document.getElementById('route_' + routeId);
            if (routeEl) {
                routeEl.click();
            }
        }
    });
</script>