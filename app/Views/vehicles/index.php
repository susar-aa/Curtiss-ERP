<?php
?>
<link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
<style>
    /* Styling variables */
    :root {
        --c-primary: #0066cc;
        --c-primary-hover: #005bb5;
        --c-success: #2e7d32;
        --c-warning: #e65100;
        --c-danger: #c62828;
        --c-bg-card: #ffffff;
        --c-bg-hover: rgba(0, 102, 204, 0.05);
        --c-border: var(--mac-border, #e2e8f0);
        --c-text-main: var(--text-main, #1e293b);
        --c-text-sub: #64748b;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --r-lg: 12px;
        --r-md: 8px;
        --r-sm: 4px;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --c-bg-card: #1e1e2d;
            --c-bg-hover: rgba(255, 255, 255, 0.03);
            --c-text-sub: #94a3b8;
        }
    }

    /* Layout & Navigation */
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .nav-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--c-border); margin-bottom: 20px; padding-bottom: 2px; }
    .tab-btn { padding: 10px 18px; border: none; background: transparent; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--c-text-sub); border-bottom: 2px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
    .tab-btn:hover { color: var(--c-primary); }
    .tab-btn.active { color: var(--c-primary); border-bottom-color: var(--c-primary); }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--c-primary); color: #fff; border: none; border-radius: var(--r-md); cursor: pointer; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.2s; }
    .btn:hover { background: var(--c-primary-hover); transform: translateY(-1px); }
    .btn-outline { background: transparent; border: 1.5px solid var(--c-primary); color: var(--c-primary); }
    .btn-outline:hover { background: var(--c-bg-hover); }
    .btn-danger { background: #fee2e2; color: var(--c-danger); border: none; }
    .btn-danger:hover { background: #fecaca; }
    .btn-secondary { background: #f1f5f9; color: #334155; border: none; }
    .btn-secondary:hover { background: #e2e8f0; }
    @media (prefers-color-scheme: dark) {
        .btn-secondary { background: #334155; color: #f1f5f9; }
        .btn-secondary:hover { background: #475569; }
    }

    /* KPIs */
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
    .kpi-card { background: var(--c-bg-card); padding: 15px 20px; border-radius: var(--r-lg); border: 1px solid var(--c-border); display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow-sm); transition: transform 0.2s; }
    .kpi-card:hover { transform: translateY(-2px); }
    .kpi-icon-wrapper { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .kpi-info { display: flex; flex-direction: column; }
    .kpi-title { font-size: 11px; color: var(--c-text-sub); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
    .kpi-val { font-size: 24px; font-weight: 800; color: var(--c-text-main); margin-top: 2px; }

    /* Master-Detail Split Screen */
    .split-layout { display: grid; grid-template-columns: 360px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 1024px) {
        .split-layout { grid-template-columns: 1fr; }
    }
    .master-pane { display: flex; flex-direction: column; gap: 15px; }
    .detail-pane { background: var(--c-bg-card); border-radius: var(--r-lg); border: 1px solid var(--c-border); box-shadow: var(--shadow-sm); min-height: 500px; padding: 25px; display: flex; flex-direction: column; gap: 20px; }

    /* Vehicle Cards */
    .vehicle-list { display: flex; flex-direction: column; gap: 10px; max-height: 600px; overflow-y: auto; padding-right: 5px; }
    .vehicle-card { background: var(--c-bg-card); border: 1px solid var(--c-border); border-radius: var(--r-md); padding: 15px; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-sm); position: relative; }
    .vehicle-card:hover { border-color: var(--c-primary); transform: translateX(2px); }
    .vehicle-card.selected-row { border-color: var(--c-primary); background: var(--c-bg-hover); box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.15); }
    .vehicle-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .vehicle-number { font-size: 15px; font-weight: 700; color: var(--c-text-main); }
    .vehicle-model { font-size: 12px; color: var(--c-text-sub); margin-bottom: 10px; }
    .vehicle-meta { display: flex; gap: 12px; font-size: 11px; color: var(--c-text-sub); }
    .vehicle-meta span { display: flex; align-items: center; gap: 4px; }

    /* Status Badges */
    .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .status-Active { background: #e8f5e9; color: var(--c-success); }
    .status-Maintenance, .status-Under-Maintenance { background: #fff3e0; color: var(--c-warning); }
    .status-Inactive { background: #ffebee; color: var(--c-danger); }

    /* Detail Tabs */
    .detail-tabs-nav { display: flex; gap: 4px; border-bottom: 1px solid var(--c-border); margin-bottom: 15px; overflow-x: auto; }
    .detail-tab-btn { padding: 8px 12px; border: none; background: transparent; cursor: pointer; font-size: 12px; font-weight: 600; color: var(--c-text-sub); border-bottom: 2px solid transparent; white-space: nowrap; }
    .detail-tab-btn.active { color: var(--c-primary); border-bottom-color: var(--c-primary); }

    /* Forms & Modals */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2500; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .modal-content { background: var(--c-bg-card); padding: 25px; border-radius: var(--r-lg); width: 550px; max-width: 90%; border: 1px solid var(--c-border); box-shadow: var(--shadow-md); max-height: 90vh; overflow-y: auto; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 500px) {
        .form-grid { grid-template-columns: 1fr; }
    }
    .form-group { margin-bottom: 12px; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 12px; font-weight: 600; color: var(--c-text-main); }
    .form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--c-border); border-radius: var(--r-md); background: transparent; color: var(--c-text-main); box-sizing: border-box; font-size: 13px; outline: none; transition: border-color 0.2s; }
    .form-control:focus { border-color: var(--c-primary); }
    .form-control[readonly] { background: var(--c-bg-hover); color: var(--c-text-sub); cursor: not-allowed; }

    /* Timelines */
    .timeline { position: relative; padding-left: 20px; border-left: 2px solid var(--c-border); margin-left: 10px; display: flex; flex-direction: column; gap: 15px; }
    .timeline-item { position: relative; }
    .timeline-marker { position: absolute; left: -26px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: var(--c-primary); border: 2px solid var(--c-bg-card); }
    .timeline-content { font-size: 12px; }
    .timeline-title { font-weight: 700; color: var(--c-text-main); margin-bottom: 3px; }
    .timeline-time { font-size: 11px; color: var(--c-text-sub); }

    /* Tables */
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--c-border); font-size: 12px; }
    .data-table th { background: var(--c-bg-hover); font-weight: 700; color: var(--c-text-main); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .data-table tbody tr:hover { background: var(--c-bg-hover); }

    /* Spinner */
    .spinner { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<div class="dashboard-header">
    <div>
        <h2 style="margin: 0 0 5px 0; display: flex; align-items: center; gap: 10px;">🚚 Fleet & Fuel Operations Control Center</h2>
        <p style="margin: 0; color: var(--c-text-sub); font-size: 14px;">Manage vehicle master, track live fuel logs, configure fuel types, and monitor performance.</p>
    </div>
    <button class="btn" onclick="openVehicleModal('add')"><i class="ph ph-plus"></i> Add Vehicle</button>
</div>

<!-- Main Modules Navigation -->
<div class="nav-tabs">
    <button class="tab-btn active" id="tabVehicles" onclick="switchMainTab('vehicles')"><i class="ph ph-truck"></i> Vehicle Fleet</button>
    <button class="tab-btn" id="tabFuelTypes" onclick="switchMainTab('fuel-types')"><i class="ph ph-gas-pump"></i> Fuel Types & Prices</button>
    <button class="tab-btn" id="tabReports" onclick="switchMainTab('reports')"><i class="ph ph-chart-line-up"></i> Fleet Analytics & Reports</button>
</div>

<!-- Section: Vehicles Fleet -->
<div id="sectionVehicles" class="tab-section">
    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-card" style="border-left: 4px solid var(--c-success);">
            <div class="kpi-icon-wrapper" style="background: #e8f5e9; color: var(--c-success);"><i class="ph ph-check-circle"></i></div>
            <div class="kpi-info">
                <span class="kpi-title">Active Vehicles</span>
                <span class="kpi-val"><?= $data['active_count'] ?></span>
            </div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid var(--c-warning);">
            <div class="kpi-icon-wrapper" style="background: #fff3e0; color: var(--c-warning);"><i class="ph ph-wrench"></i></div>
            <div class="kpi-info">
                <span class="kpi-title">In Maintenance</span>
                <span class="kpi-val"><?= $data['maintenance_count'] ?></span>
            </div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid var(--c-danger);">
            <div class="kpi-icon-wrapper" style="background: #ffebee; color: var(--c-danger);"><i class="ph ph-x-circle"></i></div>
            <div class="kpi-info">
                <span class="kpi-title">Inactive Vehicles</span>
                <span class="kpi-val"><?= $data['inactive_count'] ?></span>
            </div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid var(--c-primary);">
            <div class="kpi-icon-wrapper" style="background: #e0f2fe; color: var(--c-primary);"><i class="ph ph-list-numbers"></i></div>
            <div class="kpi-info">
                <span class="kpi-title">Total Fleet</span>
                <span class="kpi-val"><?= $data['total_vehicles'] ?></span>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if(!empty($data['error'])): ?><div style="padding: 12px; background:#ffebee; color:#c62828; border-radius:var(--r-md); margin-bottom:15px; font-weight: 600; display:flex; align-items:center; gap:8px;"><i class="ph ph-warning-circle"></i> <?= $data['error'] ?></div><?php endif; ?>
    <?php if(!empty($data['success'])): ?><div style="padding: 12px; background:#e8f5e9; color:#2e7d32; border-radius:var(--r-md); margin-bottom:15px; font-weight: 600; display:flex; align-items:center; gap:8px;"><i class="ph ph-check-circle"></i> <?= $data['success'] ?></div><?php endif; ?>

    <!-- Master-Detail Grid -->
    <div class="split-layout">
        <!-- Master: Vehicle List -->
        <div class="master-pane">
            <div style="position:relative;">
                <input type="text" id="searchInput" class="form-control" style="padding-left:35px; border-radius:var(--r-md);" placeholder="🔍 Search fleet (number, model, type)..." value="<?= htmlspecialchars($data['search']) ?>">
            </div>
            
            <div class="vehicle-list" id="vehicleListBody">
                <?php if(empty($data['vehicles'])): ?>
                    <div style="text-align: center; color: var(--c-text-sub); padding: 30px;">No vehicles found in fleet.</div>
                <?php else: foreach($data['vehicles'] as $v): ?>
                    <div class="vehicle-card" id="vehicle_row_<?= $v->id ?>" onclick="loadVehicleDetails(<?= $v->id ?>)">
                        <div class="vehicle-card-header">
                            <span class="vehicle-number"><?= htmlspecialchars($v->vehicle_number) ?></span>
                            <span class="status-badge status-<?= str_replace(' ', '-', $v->status) ?>"><?= $v->status ?></span>
                        </div>
                        <div class="vehicle-model"><?= htmlspecialchars($v->model) ?> | <?= htmlspecialchars($v->type) ?></div>
                        <div class="vehicle-meta">
                            <span><i class="ph ph-gauge"></i> <?= number_format($v->current_odometer) ?> Km</span>
                            <?php if(!empty($v->driver_name)): ?>
                                <span><i class="ph ph-user"></i> <?= htmlspecialchars($v->driver_name) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="paginationContainer" style="margin-top: 10px;">
                <?php if($data['total_pages'] > 1): ?>
                    <?php for($i = 1; $i <= $data['total_pages']; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($data['search']) ?>" class="page-btn <?= ($data['page'] == $i) ? 'active' : '' ?>" style="margin-left: 4px;"><?= $i ?></a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detail: Metrics, Logs & Actions Panel -->
        <div class="detail-pane" id="detailPanelContainer">
            <div style="text-align: center; padding: 100px 20px; color: var(--c-text-sub);">
                <i class="ph ph-truck" style="font-size: 48px; color: var(--c-primary); opacity: 0.5;"></i>
                <h3 style="margin: 15px 0 5px 0; color: var(--c-text-main);">Vehicle Detail Workspace</h3>
                <p style="margin: 0; font-size:13px;">Select a vehicle from the fleet to record fuel refills, inspect log history, review route assignments, and check general ledger transactions.</p>
            </div>
        </div>
    </div>
</div>

<!-- Section: Fuel Types Configurations -->
<div id="sectionFuelTypes" class="tab-section" style="display:none;">
    <div class="split-layout" style="grid-template-columns: 1.2fr 0.8fr;">
        <!-- Left: Current Fuel Prices Table -->
        <div class="detail-pane">
            <h3 style="margin: 0 0 10px 0; display:flex; align-items:center; gap:8px;"><i class="ph ph-gas-pump" style="color:var(--c-primary);"></i> Active Fuel Pricing Registry</h3>
            <p style="margin:0 0 15px 0; font-size:13px; color:var(--c-text-sub);">Manage fuel types and update market rates to automate consumption calculation across the ERP.</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fuel Type</th>
                        <th>Price Per Liter (Rs.)</th>
                        <th>Last Updated</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="fuelTypesTableBody">
                    <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--c-text-sub);">Loading fuel registry...</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Right: Manage Fuel Type Form -->
        <div class="detail-pane" style="min-height: auto;">
            <h3 id="fuelFormTitle" style="margin:0 0 15px 0;"><i class="ph ph-plus-circle"></i> Create/Edit Fuel Type</h3>
            <form id="fuelTypeForm" onsubmit="saveFuelType(event)">
                <input type="hidden" id="ft_id" value="">
                <div class="form-group">
                    <label>Fuel Type Name *</label>
                    <input type="text" id="ft_name" class="form-control" required placeholder="e.g. Diesel, Petrol Octane 95">
                </div>
                <div class="form-group">
                    <label>Price Per Liter (Rs.) *</label>
                    <input type="number" step="0.01" min="0.01" id="ft_price" class="form-control" required placeholder="e.g. 370.00">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="resetFuelTypeForm()">Cancel</button>
                    <button type="submit" class="btn"><i class="ph ph-floppy-disk"></i> Save Fuel Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Section: Performance & Analytics Reports -->
<div id="sectionReports" class="tab-section" style="display:none;">
    <div class="detail-pane" style="margin-bottom:20px;">
        <h3 style="margin:0 0 5px 0;"><i class="ph ph-chart-bar" style="color:var(--c-primary);"></i> Vehicle Fuel Efficiency & Consumption Report</h3>
        <p style="margin:0 0 15px 0; color:var(--c-text-sub); font-size:13px;">Overview of fuel efficiency based on actual odometer updates and refill logging.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Model</th>
                    <th style="text-align:right;">Refill Count</th>
                    <th style="text-align:right;">Total Liters</th>
                    <th style="text-align:right;">Total Cost (Rs.)</th>
                    <th style="text-align:right;">Distance (Km)</th>
                    <th style="text-align:right; font-weight:bold; color:var(--c-primary);">Efficiency (Km/L)</th>
                </tr>
            </thead>
            <tbody id="reportConsumptionBody">
                <tr><td colspan="7" style="text-align:center; padding:20px; color:var(--c-text-sub);">Loading report details...</td></tr>
            </tbody>
        </table>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <!-- Driver Cost Analysis -->
        <div class="detail-pane">
            <h3 style="margin:0 0 5px 0;"><i class="ph ph-users" style="color:var(--c-primary);"></i> Cost Attribution by Driver</h3>
            <p style="margin:0 0 15px 0; color:var(--c-text-sub); font-size:13px;">Refills and costs grouped by drivers for operational accountability.</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th style="text-align:right;">Refills</th>
                        <th style="text-align:right;">Liters</th>
                        <th style="text-align:right;">Total Cost (Rs.)</th>
                    </tr>
                </thead>
                <tbody id="reportDriverBody">
                    <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--c-text-sub);">Loading driver statistics...</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Profitability analysis -->
        <div class="detail-pane">
            <h3 style="margin:0 0 5px 0;"><i class="ph ph-currency-dollar" style="color:var(--c-primary);"></i> Route Profitability & Revenue Analysis</h3>
            <p style="margin:0 0 15px 0; color:var(--c-text-sub); font-size:13px;">Deducts fuel and expenses from total sales generated during trips.</p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th style="text-align:right;">Trip Sales (Rs.)</th>
                        <th style="text-align:right;">Fuel Expense (Rs.)</th>
                        <th style="text-align:right;">Other Expenses (Rs.)</th>
                        <th style="text-align:right; font-weight:bold; color:var(--c-success);">Net Contribution (Rs.)</th>
                    </tr>
                </thead>
                <tbody id="reportProfitBody">
                    <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--c-text-sub);">Loading profitability analysis...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Vehicle Specs -->
<div class="modal" id="vehicleModal">
    <div class="modal-content" style="width: 650px;">
        <h3 id="modalTitle" style="margin-top:0; display:flex; align-items:center; gap:8px;"><i class="ph ph-truck" style="color:var(--c-primary);"></i> Add Vehicle to Fleet</h3>
        <form id="vehicleForm" action="<?= APP_URL ?>/vehicle?page=<?= $data['page'] ?>&search=<?= urlencode($data['search']) ?>" method="POST">
            <input type="hidden" name="action" id="formAction" value="add_vehicle">
            <input type="hidden" name="vehicle_id" id="formVehicleId" value="">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Vehicle Number (Plate) *</label>
                    <input type="text" name="vehicle_number" id="f_number" class="form-control" placeholder="e.g. WP NB-4589" required>
                </div>
                <div class="form-group">
                    <label>Registration Number</label>
                    <input type="text" name="registration_number" id="f_reg_no" class="form-control" placeholder="e.g. REG-7890">
                </div>
                <div class="form-group">
                    <label>Chassis Number</label>
                    <input type="text" name="chassis_number" id="f_chassis" class="form-control" placeholder="e.g. MXT12093849">
                </div>
                <div class="form-group">
                    <label>Engine Number</label>
                    <input type="text" name="engine_number" id="f_engine" class="form-control" placeholder="e.g. ENG982348">
                </div>
                <div class="form-group">
                    <label>Model Specification *</label>
                    <input type="text" name="model" id="f_model" class="form-control" placeholder="e.g. Tata LPT 709" required>
                </div>
                <div class="form-group">
                    <label>Vehicle Type *</label>
                    <select name="type" id="f_type" class="form-control" required>
                        <option value="Lorry">Lorry</option>
                        <option value="Van">Van</option>
                        <option value="Three-Wheel">Three-Wheel</option>
                        <option value="Car">Car</option>
                        <option value="Motorcycle">Motorcycle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Assigned Driver</label>
                    <select name="assigned_driver_id" id="f_driver" class="form-control">
                        <option value="">-- None --</option>
                        <?php foreach ($data['drivers'] as $drv): ?>
                            <option value="<?= $drv->id ?>"><?= htmlspecialchars($drv->first_name . ' ' . $drv->last_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fuel Type</label>
                    <select name="fuel_type_id" id="f_fuel_type" class="form-control">
                        <option value="">-- None --</option>
                        <?php foreach ($data['fuel_types'] as $ft): ?>
                            <option value="<?= $ft->id ?>"><?= htmlspecialchars($ft->fuel_type) ?> (Rs. <?= number_format($ft->price_per_liter, 2) ?>/L)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fuel Tank Capacity (Liters)</label>
                    <input type="number" step="0.1" name="fuel_tank_capacity" id="f_capacity" class="form-control" placeholder="e.g. 70">
                </div>
                <div class="form-group">
                    <label>Average Fuel Consumption (Km/L)</label>
                    <input type="number" step="0.01" name="avg_fuel_consumption" id="f_avg_cons" class="form-control" placeholder="e.g. 8.5">
                </div>
                <div class="form-group">
                    <label>Current Odometer (Km)</label>
                    <input type="number" name="current_odometer" id="f_odometer" class="form-control" placeholder="e.g. 12500" value="0">
                </div>
                <div class="form-group">
                    <label>Next Service Mileage (Km)</label>
                    <input type="number" name="next_service_mileage" id="f_next_service" class="form-control" placeholder="e.g. 15000">
                </div>
                <div class="form-group">
                    <label>Insurance Expiry Date</label>
                    <input type="date" name="insurance_expiry" id="f_insurance" class="form-control">
                </div>
                <div class="form-group">
                    <label>Revenue License Expiry</label>
                    <input type="date" name="license_expiry" id="f_license" class="form-control">
                </div>
                <div class="form-group full-width">
                    <label>Status</label>
                    <select name="status" id="f_status" class="form-control">
                        <option value="Active">Active (Ready for delivery)</option>
                        <option value="Maintenance">Under Maintenance</option>
                        <option value="Inactive">Inactive / Out of fleet</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('vehicleModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" id="modalSubmitBtn"><i class="ph ph-floppy-disk"></i> Save Vehicle Specs</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Record Fuel Refill -->
<div class="modal" id="fuelModal">
    <div class="modal-content" style="width: 500px;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:8px;"><i class="ph ph-gas-pump" style="color:var(--c-primary);"></i> Register Fuel Refill</h3>
        <form id="fuelEntryForm" onsubmit="submitFuelEntry(event)">
            <input type="hidden" id="entry_vehicle_id" value="">
            
            <div class="form-group">
                <label>Vehicle Number</label>
                <input type="text" id="entry_vehicle_num" class="form-control" readonly>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Assigned Driver *</label>
                    <select id="entry_driver_id" class="form-control" required>
                        <option value="" disabled selected>Select Driver</option>
                        <?php foreach ($data['drivers'] as $drv): ?>
                            <option value="<?= $drv->id ?>"><?= htmlspecialchars($drv->first_name . ' ' . $drv->last_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Odometer Reading (Km) *</label>
                    <input type="number" id="entry_odometer" class="form-control" required placeholder="Current mileage">
                </div>
                <div class="form-group">
                    <label>Fuel Type *</label>
                    <select id="entry_fuel_type" class="form-control" required onchange="onFuelTypeChanged()">
                        <option value="" disabled selected>Select Type</option>
                        <?php foreach ($data['fuel_types'] as $ft): ?>
                            <option value="<?= $ft->id ?>" data-price="<?= $ft->price_per_liter ?>"><?= htmlspecialchars($ft->fuel_type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price Per Liter (Rs.) *</label>
                    <input type="number" step="0.01" id="entry_price" class="form-control" required oninput="calcFuelCost(true)">
                </div>
                <div class="form-group">
                    <label>Quantity (Liters) *</label>
                    <input type="number" step="0.001" id="entry_qty" class="form-control" required oninput="calcFuelCost(true)">
                </div>
                <div class="form-group">
                    <label>Total Amount (Rs.) *</label>
                    <input type="number" step="0.01" id="entry_total" class="form-control" required oninput="calcFuelCost(false)">
                </div>
            </div>

            <div class="form-group">
                <label>Fuel Station</label>
                <input type="text" id="entry_station" class="form-control" placeholder="e.g. Ceypetco Colombo 03" value="Local Fuel Station">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Payment Source *</label>
                    <select id="entry_payment" class="form-control" required onchange="toggleBankPicker()">
                        <option value="Petty Cash">Petty Cash</option>
                        <option value="Cash in Hand">Cash In Hand</option>
                        <option value="Bank Transfer">Bank Account / Card</option>
                    </select>
                </div>
                <div class="form-group" id="bankPickerGroup" style="display:none;">
                    <label>Select Bank Account *</label>
                    <select id="entry_bank" class="form-control">
                        <option value="" disabled selected>Select Account</option>
                        <?php foreach ($data['bank_accounts'] as $acc): ?>
                            <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->bank_name . ' - ' . $acc->account_number) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea id="entry_remarks" class="form-control" style="height:60px; font-family:inherit;" placeholder="Add details..."></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('fuelModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn"><i class="ph ph-check"></i> Submit Refill</button>
            </div>
        </form>
    </div>
</div>

<script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    // fetch wrapper
    function fetchSecure(url, options = {}) {
        options.headers = options.headers || {};
        options.headers['X-CSRF-TOKEN'] = CSRF_TOKEN;
        
        if (options.body && typeof options.body === 'string') {
            try {
                const parsed = JSON.parse(options.body);
                if (typeof parsed === 'object' && parsed !== null) {
                    parsed.csrf_token = CSRF_TOKEN;
                    options.body = JSON.stringify(parsed);
                }
            } catch (e) {}
        }
        return fetch(url, options);
    }

    // Tab Switching
    function switchMainTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-section').forEach(sec => sec.style.display = 'none');
        
        if (tab === 'vehicles') {
            document.getElementById('tabVehicles').classList.add('active');
            document.getElementById('sectionVehicles').style.display = 'block';
        } else if (tab === 'fuel-types') {
            document.getElementById('tabFuelTypes').classList.add('active');
            document.getElementById('sectionFuelTypes').style.display = 'block';
            loadFuelTypes();
        } else if (tab === 'reports') {
            document.getElementById('tabReports').classList.add('active');
            document.getElementById('sectionReports').style.display = 'block';
            loadPerformanceReports();
        }
    }

    // Live search vehicle fleet list
    let searchTimeout = null;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = encodeURIComponent(e.target.value);
            const url = `?search=${query}&page=1`;
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    document.getElementById('vehicleListBody').innerHTML = doc.getElementById('vehicleListBody').innerHTML;
                    document.getElementById('paginationContainer').innerHTML = doc.getElementById('paginationContainer').innerHTML;
                });
        }, 300);
    });

    // Modal: Add/Edit Vehicle specifications
    function openVehicleModal(mode, vehicleData = null) {
        document.getElementById('vehicleModal').style.display = 'flex';
        const form = document.getElementById('vehicleForm');
        
        if (mode === 'add') {
            document.getElementById('modalTitle').innerHTML = '<i class="ph ph-plus-circle"></i> Add Vehicle to Fleet';
            document.getElementById('formAction').value = 'add_vehicle';
            form.reset();
            document.getElementById('formVehicleId').value = '';
            document.getElementById('f_odometer').readOnly = false;
        } else {
            document.getElementById('modalTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Vehicle Specification';
            document.getElementById('formAction').value = 'edit_vehicle';
            document.getElementById('formVehicleId').value = vehicleData.id;
            
            document.getElementById('f_number').value = vehicleData.vehicle_number || '';
            document.getElementById('f_reg_no').value = vehicleData.registration_number || '';
            document.getElementById('f_chassis').value = vehicleData.chassis_number || '';
            document.getElementById('f_engine').value = vehicleData.engine_number || '';
            document.getElementById('f_model').value = vehicleData.model || '';
            document.getElementById('f_type').value = vehicleData.type || 'Lorry';
            document.getElementById('f_driver').value = vehicleData.assigned_driver_id || '';
            document.getElementById('f_fuel_type').value = vehicleData.fuel_type_id || '';
            document.getElementById('f_capacity').value = vehicleData.fuel_tank_capacity || '';
            document.getElementById('f_avg_cons').value = vehicleData.avg_fuel_consumption || '';
            document.getElementById('f_odometer').value = vehicleData.current_odometer || '0';
            document.getElementById('f_odometer').readOnly = true; // locks odometer edit, can only be updated via fuel log
            document.getElementById('f_next_service').value = vehicleData.next_service_mileage || '';
            document.getElementById('f_insurance').value = vehicleData.insurance_expiry || '';
            document.getElementById('f_license').value = vehicleData.license_expiry || '';
            document.getElementById('f_status').value = vehicleData.status || 'Active';
        }
    }

    // Detail Panel: Tab Switcher
    let detailData = null;
    function switchDetailTab(tabId) {
        document.querySelectorAll('.detail-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.detail-tab-content').forEach(c => c.style.display = 'none');
        
        document.getElementById('detTabBtn_' + tabId).classList.add('active');
        document.getElementById('detTab_' + tabId).style.display = 'block';
    }

    // Load details of single vehicle via AJAX
    function loadVehicleDetails(id) {
        // highlight selected card
        document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('selected-row'));
        const activeCard = document.getElementById('vehicle_row_' + id);
        if (activeCard) activeCard.classList.add('selected-row');

        const container = document.getElementById('detailPanelContainer');
        container.innerHTML = `
            <div style="text-align:center; padding:100px 0; color:var(--c-text-sub);">
                <i class="ph ph-circle-notch spinner" style="font-size:32px; color:var(--c-primary);"></i>
                <p style="margin-top:10px; font-size:13px;">Fetching telemetry and log history...</p>
            </div>
        `;

        fetchSecure('<?= APP_URL ?>/vehicle/api_get_vehicle_details/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    detailData = data;
                    renderDetailPanel(data);
                } else {
                    container.innerHTML = `
                        <div style="text-align:center; padding:100px 0; color:var(--c-danger);">
                            <i class="ph ph-warning-circle" style="font-size:32px;"></i>
                            <p style="margin-top:10px; font-size:13px;">${data.message || 'Error fetching vehicle details'}</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                container.innerHTML = `
                    <div style="text-align:center; padding:100px 0; color:var(--c-danger);">
                        <i class="ph ph-warning-circle" style="font-size:32px;"></i>
                        <p style="margin-top:10px; font-size:13px;">Connection error.</p>
                    </div>
                `;
            });
    }

    // Render detailed pane content
    function renderDetailPanel(data) {
        const v = data.vehicle;
        const container = document.getElementById('detailPanelContainer');
        
        // Build fuel log rows
        let fuelRows = '';
        if (!data.fuel_history || data.fuel_history.length === 0) {
            fuelRows = `<tr><td colspan="8" style="text-align:center; color:var(--c-text-sub);">No fuel refills logged.</td></tr>`;
        } else {
            data.fuel_history.forEach(row => {
                const date = new Date(row.refill_date || row.created_at).toLocaleDateString();
                fuelRows += `
                    <tr>
                        <td><strong>${date}</strong></td>
                        <td>${escapeHtml(row.driver_name || 'System')}</td>
                        <td>${numberFormat(row.odometer_reading)} Km</td>
                        <td>${parseFloat(row.quantity).toFixed(2)} L</td>
                        <td>Rs. ${parseFloat(row.price_per_liter).toFixed(2)}</td>
                        <td style="font-weight:bold;">Rs. ${parseFloat(row.total_amount).toFixed(2)}</td>
                        <td>${escapeHtml(row.payment_source)}</td>
                        <td style="text-align:center;">
                            <button type="button" class="btn btn-danger btn-small" onclick="deleteFuelEntry(${row.id})" title="Delete Refill Log"><i class="ph ph-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        }

        // Build route history items
        let routeTimeline = '<div style="color:var(--c-text-sub); font-size:12px;">No route assignments recorded.</div>';
        if (data.routes && data.routes.length > 0) {
            routeTimeline = '<div class="timeline">';
            data.routes.forEach(r => {
                const start = new Date(r.start_time).toLocaleString();
                const end = r.end_time ? new Date(r.end_time).toLocaleString() : 'Active/Ongoing';
                routeTimeline += `
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Route #RT-${String(r.id).padStart(5, '0')} (${escapeHtml(r.route_name || 'Delivery')})</div>
                            <div>Driver/Rep: <strong>${escapeHtml(r.driver_name || 'Not assigned')}</strong></div>
                            <div class="timeline-time"><i class="ph ph-calendar"></i> ${start} &rarr; ${end}</div>
                            <div style="margin-top:2px;">Odometer: ${numberFormat(r.start_meter)} Km &rarr; ${r.end_meter ? numberFormat(r.end_meter) + ' Km' : 'Live'}</div>
                        </div>
                    </div>
                `;
            });
            routeTimeline += '</div>';
        }

        // Build Odometer updates timeline
        let odoTimeline = '<div style="color:var(--c-text-sub); font-size:12px;">No odometer log updates.</div>';
        if (data.odometer_history && data.odometer_history.length > 0) {
            odoTimeline = '<div class="timeline">';
            data.odometer_history.forEach(o => {
                const date = new Date(o.logged_at).toLocaleString();
                odoTimeline += `
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">${numberFormat(o.odometer)} Km</div>
                            <div>Source: <em>${escapeHtml(o.source)}</em></div>
                            <div class="timeline-time"><i class="ph ph-clock"></i> ${date}</div>
                        </div>
                    </div>
                `;
            });
            odoTimeline += '</div>';
        }

        // Build Accounting Journal entries
        let jRows = '';
        if (!data.transactions || data.transactions.length === 0) {
            jRows = `<tr><td colspan="5" style="text-align:center; color:var(--c-text-sub);">No ledger transactions recorded.</td></tr>`;
        } else {
            data.transactions.forEach(row => {
                const date = new Date(row.entry_date).toLocaleDateString();
                jRows += `
                    <tr>
                        <td><strong>${date}</strong></td>
                        <td>${escapeHtml(row.reference)}</td>
                        <td>${escapeHtml(row.account_name)}</td>
                        <td style="text-align:right; color:var(--c-success); font-weight:bold;">${parseFloat(row.debit) > 0 ? 'Rs. ' + parseFloat(row.debit).toFixed(2) : '-'}</td>
                        <td style="text-align:right; color:var(--c-danger); font-weight:bold;">${parseFloat(row.credit) > 0 ? 'Rs. ' + parseFloat(row.credit).toFixed(2) : '-'}</td>
                    </tr>
                `;
            });
        }

        // Build general history logs
        let histTimeline = '<div style="color:var(--c-text-sub); font-size:12px;">No event log history.</div>';
        if (data.history && data.history.length > 0) {
            histTimeline = '<div class="timeline">';
            data.history.forEach(h => {
                const date = new Date(h.created_at).toLocaleString();
                histTimeline += `
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background:#475569;"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">${escapeHtml(h.event_type)}</div>
                            <div>${escapeHtml(h.description)}</div>
                            <div class="timeline-time"><i class="ph ph-clock"></i> ${date} by ${escapeHtml(h.username || 'System')}</div>
                        </div>
                    </div>
                `;
            });
            histTimeline += '</div>';
        }

        container.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:start; border-bottom:1px solid var(--c-border); padding-bottom:15px;">
                <div>
                    <h3 style="margin:0 0 5px 0; font-size:20px; font-weight:800; color:var(--c-primary);">${escapeHtml(v.vehicle_number)}</h3>
                    <div style="font-size:13px; color:var(--c-text-sub);">${escapeHtml(v.model)} &bull; ${escapeHtml(v.type)}</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button class="btn btn-outline btn-small" onclick='openVehicleModal("edit", ${JSON.stringify(v)})'><i class="ph ph-pencil"></i> Specs</button>
                    <button class="btn btn-small" onclick="openFuelModal(${v.id}, '${escapeHtml(v.vehicle_number)}', '${v.assigned_driver_id || ''}', '${v.fuel_type_id || ''}', ${v.current_odometer})"><i class="ph ph-gas-pump"></i> Refill Fuel</button>
                </div>
            </div>

            <div class="detail-tabs-nav">
                <button class="detail-tab-btn active" id="detTabBtn_specs" onclick="switchDetailTab('specs')">Overview & specs</button>
                <button class="detail-tab-btn" id="detTabBtn_fuel" onclick="switchDetailTab('fuel')">Fuel Logs (${data.fuel_history ? data.fuel_history.length : 0})</button>
                <button class="detail-tab-btn" id="detTabBtn_routes" onclick="switchDetailTab('routes')">Routes</button>
                <button class="detail-tab-btn" id="detTabBtn_odo" onclick="switchDetailTab('odo')">Odometer Log</button>
                <button class="detail-tab-btn" id="detTabBtn_finance" onclick="switchDetailTab('finance')">General Ledger</button>
                <button class="detail-tab-btn" id="detTabBtn_history" onclick="switchDetailTab('history')">System History</button>
            </div>

            <!-- Tab: Specs -->
            <div id="detTab_specs" class="detail-tab-content">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:13px;">
                    <div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Registration Number:</strong><br>${escapeHtml(v.registration_number || '--')}</div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Chassis Number:</strong><br>${escapeHtml(v.chassis_number || '--')}</div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Engine Number:</strong><br>${escapeHtml(v.engine_number || '--')}</div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Assigned Driver:</strong><br>${escapeHtml(v.driver_name || '--')}</div>
                    </div>
                    <div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Fuel Type:</strong><br>${escapeHtml(v.fuel_type_name || '--')}</div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Tank Capacity:</strong><br>${v.fuel_tank_capacity ? v.fuel_tank_capacity + ' Liters' : '--'}</div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Odometer:</strong><br>${numberFormat(v.current_odometer)} Km</div>
                        <div style="margin-bottom:10px;"><strong style="color:var(--c-text-sub);">Next Service Mileage:</strong><br>${v.next_service_mileage ? numberFormat(v.next_service_mileage) + ' Km' : '--'}</div>
                    </div>
                </div>
                <div style="border-top:1px solid var(--c-border); margin-top:15px; padding-top:15px; display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:13px;">
                    <div>
                        <strong style="color:var(--c-text-sub);">Insurance Expiry:</strong><br>
                        <span style="font-weight:bold;" class="${isExpired(v.insurance_expiry) ? 'text-danger' : ''}">${v.insurance_expiry ? new Date(v.insurance_expiry).toLocaleDateString() : '--'}</span>
                    </div>
                    <div>
                        <strong style="color:var(--c-text-sub);">Revenue License Expiry:</strong><br>
                        <span style="font-weight:bold;" class="${isExpired(v.license_expiry) ? 'text-danger' : ''}">${v.license_expiry ? new Date(v.license_expiry).toLocaleDateString() : '--'}</span>
                    </div>
                </div>
            </div>

            <!-- Tab: Fuel Logs -->
            <div id="detTab_fuel" class="detail-tab-content" style="display:none;">
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Driver</th>
                                <th>Odometer</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Total</th>
                                <th>Source</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${fuelRows}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Routes -->
            <div id="detTab_routes" class="detail-tab-content" style="display:none; max-height:400px; overflow-y:auto;">
                ${routeTimeline}
            </div>

            <!-- Tab: Odometer -->
            <div id="detTab_odo" class="detail-tab-content" style="display:none; max-height:400px; overflow-y:auto;">
                ${odoTimeline}
            </div>

            <!-- Tab: Finance -->
            <div id="detTab_finance" class="detail-tab-content" style="display:none;">
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Account</th>
                                <th style="text-align:right;">Debit (Rs.)</th>
                                <th style="text-align:right;">Credit (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${jRows}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: History -->
            <div id="detTab_history" class="detail-tab-content" style="display:none; max-height:400px; overflow-y:auto;">
                ${histTimeline}
            </div>
        `;
    }

    // Modal: Record Fuel entry
    function openFuelModal(vehicleId, vehicleNum, assignedDriverId, fuelTypeId, odometer) {
        document.getElementById('fuelModal').style.display = 'flex';
        document.getElementById('fuelEntryForm').reset();
        
        document.getElementById('entry_vehicle_id').value = vehicleId;
        document.getElementById('entry_vehicle_num').value = vehicleNum;
        document.getElementById('entry_driver_id').value = assignedDriverId;
        document.getElementById('entry_fuel_type').value = fuelTypeId;
        document.getElementById('entry_odometer').value = odometer;
        document.getElementById('entry_odometer').setAttribute('min', odometer);
        
        onFuelTypeChanged();
        toggleBankPicker();
    }

    function onFuelTypeChanged() {
        const ftSelect = document.getElementById('entry_fuel_type');
        const selectedOpt = ftSelect.options[ftSelect.selectedIndex];
        if (selectedOpt && selectedOpt.getAttribute('data-price')) {
            document.getElementById('entry_price').value = parseFloat(selectedOpt.getAttribute('data-price')).toFixed(2);
        } else {
            document.getElementById('entry_price').value = '';
        }
        calcFuelCost(true);
    }

    function calcFuelCost(calcTotal) {
        const price = parseFloat(document.getElementById('entry_price').value) || 0;
        const qty = parseFloat(document.getElementById('entry_qty').value) || 0;
        const total = parseFloat(document.getElementById('entry_total').value) || 0;
        
        if (calcTotal) {
            document.getElementById('entry_total').value = (price * qty).toFixed(2);
        } else {
            if (price > 0) {
                document.getElementById('entry_qty').value = (total / price).toFixed(3);
            }
        }
    }

    function toggleBankPicker() {
        const source = document.getElementById('entry_payment').value;
        const picker = document.getElementById('bankPickerGroup');
        const bankSelect = document.getElementById('entry_bank');
        
        if (source === 'Bank Transfer') {
            picker.style.display = 'block';
            bankSelect.setAttribute('required', 'required');
        } else {
            picker.style.display = 'none';
            bankSelect.removeAttribute('required');
        }
    }

    function submitFuelEntry(event) {
        event.preventDefault();
        
        const payload = {
            vehicle_id: parseInt(document.getElementById('entry_vehicle_id').value),
            driver_id: parseInt(document.getElementById('entry_driver_id').value),
            odometer_reading: parseInt(document.getElementById('entry_odometer').value),
            fuel_type_id: parseInt(document.getElementById('entry_fuel_type').value),
            quantity: parseFloat(document.getElementById('entry_qty').value),
            price_per_liter: parseFloat(document.getElementById('entry_price').value),
            total_amount: parseFloat(document.getElementById('entry_total').value),
            fuel_station: document.getElementById('entry_station').value,
            payment_source: document.getElementById('entry_payment').value,
            bank_account_id: document.getElementById('entry_bank').value ? parseInt(document.getElementById('entry_bank').value) : null,
            remarks: document.getElementById('entry_remarks').value
        };

        fetchSecure('<?= APP_URL ?>/vehicle/api_add_fuel_entry', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                document.getElementById('fuelModal').style.display = 'none';
                loadVehicleDetails(payload.vehicle_id);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            alert('Failed to connect to the server.');
        });
    }

    function deleteFuelEntry(id) {
        if (!confirm('Are you sure you want to delete this fuel record? The corresponding petty cash and general ledger postings will be reversed.')) return;
        
        fetchSecure('<?= APP_URL ?>/vehicle/api_delete_fuel_entry/' + id, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                if (detailData && detailData.vehicle) {
                    loadVehicleDetails(detailData.vehicle.id);
                }
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    // Module: Fuel Types
    function loadFuelTypes() {
        const body = document.getElementById('fuelTypesTableBody');
        body.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--c-text-sub);"><i class="ph ph-circle-notch spinner"></i> Loading pricing database...</td></tr>`;
        
        fetchSecure('<?= APP_URL ?>/vehicle/api_get_fuel_types')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.fuel_types.length > 0) {
                    let html = '';
                    data.fuel_types.forEach(ft => {
                        const date = ft.updated_at ? new Date(ft.updated_at).toLocaleString() : '--';
                        html += `
                            <tr>
                                <td><strong>${escapeHtml(ft.fuel_type)}</strong></td>
                                <td>Rs. ${parseFloat(ft.price_per_liter).toFixed(2)}</td>
                                <td>${date}</td>
                                <td style="text-align:center;">
                                    <button class="btn btn-outline btn-small" onclick="editFuelType(${ft.id}, '${escapeHtml(ft.fuel_type)}', ${ft.price_per_liter})"><i class="ph ph-pencil"></i></button>
                                    <button class="btn btn-danger btn-small" onclick="deleteFuelType(${ft.id})"><i class="ph ph-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    body.innerHTML = html;
                } else {
                    body.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--c-text-sub);">No fuel types registered.</td></tr>`;
                }
            });
    }

    function saveFuelType(event) {
        event.preventDefault();
        const payload = {
            id: document.getElementById('ft_id').value ? parseInt(document.getElementById('ft_id').value) : null,
            fuel_type: document.getElementById('ft_name').value,
            price_per_liter: parseFloat(document.getElementById('ft_price').value)
        };

        fetchSecure('<?= APP_URL ?>/vehicle/api_save_fuel_type', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                resetFuelTypeForm();
                loadFuelTypes();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    function editFuelType(id, name, price) {
        document.getElementById('fuelFormTitle').innerHTML = '<i class="ph ph-pencil-simple"></i> Edit Fuel Pricing';
        document.getElementById('ft_id').value = id;
        document.getElementById('ft_name').value = name;
        document.getElementById('ft_price').value = price;
    }

    function resetFuelTypeForm() {
        document.getElementById('fuelFormTitle').innerHTML = '<i class="ph ph-plus-circle"></i> Create/Edit Fuel Type';
        document.getElementById('fuelTypeForm').reset();
        document.getElementById('ft_id').value = '';
    }

    function deleteFuelType(id) {
        if (!confirm('Are you sure you want to delete this fuel type?')) return;
        fetchSecure('<?= APP_URL ?>/vehicle/api_delete_fuel_type/' + id, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadFuelTypes();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    // Module: Reports & Performance
    function loadPerformanceReports() {
        const consBody = document.getElementById('reportConsumptionBody');
        const drvBody = document.getElementById('reportDriverBody');
        const profBody = document.getElementById('reportProfitBody');
        
        consBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:20px;"><i class="ph ph-circle-notch spinner"></i> Loading fuel efficiency analytics...</td></tr>`;
        drvBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px;"><i class="ph ph-circle-notch spinner"></i> Loading cost groupings...</td></tr>`;
        profBody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px;"><i class="ph ph-circle-notch spinner"></i> Calculating margins...</td></tr>`;

        fetchSecure('<?= APP_URL ?>/vehicle/api_get_reports')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // 1. Consumption Report
                    if (data.consumption.length === 0) {
                        consBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--c-text-sub);">No consumption logs generated.</td></tr>`;
                    } else {
                        let html = '';
                        data.consumption.forEach(c => {
                            html += `
                                <tr>
                                    <td><strong>${escapeHtml(c.vehicle_number)}</strong></td>
                                    <td>${escapeHtml(c.model)}</td>
                                    <td style="text-align:right;">${c.refill_count}</td>
                                    <td style="text-align:right;">${parseFloat(c.total_liters).toFixed(2)} L</td>
                                    <td style="text-align:right;">Rs. ${parseFloat(c.total_cost).toFixed(2)}</td>
                                    <td style="text-align:right;">${numberFormat(c.total_distance)} Km</td>
                                    <td style="text-align:right; font-weight:800; color:var(--c-primary);">${c.avg_km_l > 0 ? c.avg_km_l + ' Km/L' : '--'}</td>
                                </tr>
                            `;
                        });
                        consBody.innerHTML = html;
                    }

                    // 2. Driver Cost Analysis
                    if (data.driver_costs.length === 0) {
                        drvBody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--c-text-sub);">No driver expense logs.</td></tr>`;
                    } else {
                        let html = '';
                        data.driver_costs.forEach(d => {
                            html += `
                                <tr>
                                    <td><strong>${escapeHtml(d.first_name + ' ' + d.last_name)}</strong></td>
                                    <td style="text-align:right;">${d.refill_count}</td>
                                    <td style="text-align:right;">${parseFloat(d.total_liters).toFixed(2)} L</td>
                                    <td style="text-align:right; font-weight:bold;">Rs. ${parseFloat(d.total_cost).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        drvBody.innerHTML = html;
                    }

                    // 3. Profitability report
                    if (data.profitability.length === 0) {
                        profBody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--c-text-sub);">No routes driven.</td></tr>`;
                    } else {
                        let html = '';
                        data.profitability.forEach(p => {
                            html += `
                                <tr>
                                    <td><strong>${escapeHtml(p.vehicle_number)}</strong> (${escapeHtml(p.model)})</td>
                                    <td style="text-align:right;">Rs. ${parseFloat(p.total_sales).toFixed(2)}</td>
                                    <td style="text-align:right; color:var(--c-warning);">Rs. ${parseFloat(p.total_fuel_cost).toFixed(2)}</td>
                                    <td style="text-align:right; color:var(--c-warning);">Rs. ${parseFloat(p.total_route_expenses).toFixed(2)}</td>
                                    <td style="text-align:right; font-weight:800; color:var(--c-success);">Rs. ${parseFloat(p.net_profit).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        profBody.innerHTML = html;
                    }
                }
            });
    }

    // Utilities
    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function numberFormat(val) {
        if (!val) return '0';
        return parseInt(val).toLocaleString();
    }

    function isExpired(dateStr) {
        if (!dateStr) return false;
        const expiry = new Date(dateStr);
        const today = new Date();
        today.setHours(0,0,0,0);
        return expiry < today;
    }
</script>
