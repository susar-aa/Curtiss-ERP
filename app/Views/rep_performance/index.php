<!-- KPI & Representative Performance Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --c-glass: rgba(255, 255, 255, 0.7);
        --c-glass-border: rgba(226, 232, 240, 0.8);
        --glow-green: 0 0 12px rgba(16, 185, 129, 0.4);
        --glow-blue: 0 0 12px rgba(2, 132, 199, 0.4);
        --c-primary: #1b5e20;
        --c-secondary: #0284c7;
    }
    
    .perf-container {
        padding: 20px;
        background: #f8fafc;
        min-height: 100vh;
        font-family: "Outfit", "Inter", sans-serif;
    }

    .glass-card {
        background: var(--c-glass);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--c-glass-border);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        padding: 20px;
        margin-bottom: 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .dashboard-title {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .kpi-score-badge {
        font-size: 16px;
        font-weight: 700;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        box-shadow: var(--glow-green);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Tabs styling */
    .tab-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 2px;
        overflow-x: auto;
    }

    .tab-btn {
        background: none;
        border: none;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: var(--c-primary);
    }

    .tab-btn.active {
        color: var(--c-primary);
        border-bottom-color: var(--c-primary);
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
        animation: fadeIn 0.3s ease-in-out;
    }

    /* KPI Grid Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .kpi-metric-card {
        padding: 18px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .kpi-metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .kpi-metric-card.sales::before { background-color: #10b981; }
    .kpi-metric-card.visits::before { background-color: #0284c7; }
    .kpi-metric-card.routes::before { background-color: #f59e0b; }
    .kpi-metric-card.collections::before { background-color: #8b5cf6; }
    .kpi-metric-card.productivity::before { background-color: #ec4899; }
    .kpi-metric-card.expenses::before { background-color: #ef4444; }

    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .kpi-value {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 5px;
        cursor: pointer;
    }
    
    .kpi-value:hover {
        color: var(--c-primary);
        text-decoration: underline;
    }

    .kpi-sub {
        font-size: 11px;
        color: #94a3b8;
    }

    /* Target Achieved Indicator */
    .kpi-progress-bar {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        margin-top: 10px;
        overflow: hidden;
    }

    .kpi-progress-fill {
        height: 100%;
        background: var(--c-primary);
    }

    /* Layout divisions */
    .dashboard-panels {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .dashboard-panels {
            grid-template-columns: 1fr;
        }
    }

    /* Drilldown Modal */
    .drilldown-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: #fff;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #64748b;
    }

    .modal-close:hover {
        color: #0f172a;
    }

    /* Standardized tables styles */
    .perf-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .perf-table th {
        background: #f1f5f9;
        text-align: left;
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #cbd5e1;
    }

    .perf-table td {
        padding: 10px 12px;
        font-size: 13px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="perf-container">

    <!-- Flash Messages -->
    <?php if (!empty($data['success'])): ?>
        <div style="background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
            <?= htmlspecialchars($data['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['error'])): ?>
        <div style="background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
            <?= htmlspecialchars($data['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Title and Overall Score -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="ph ph-presentation-chart" style="color: var(--c-primary);"></i>
            Rep Performance &amp; Analytics
        </h1>
        <?php if (!empty($data['perf_data'])): ?>
            <div class="kpi-score-badge">
                <i class="ph ph-star-fill"></i>
                Performance Score: <?= number_format($data['perf_data']['overall_score'], 1) ?>%
            </div>
        <?php endif; ?>
    </div>

    <!-- Filters Section -->
    <div class="glass-card" style="background-color: #ffffff;">
        <form method="GET" action="<?= APP_URL ?>/repperformance" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px;" for="rep_user_id">Representative</label>
                <select name="rep_user_id" id="rep_user_id" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;" onchange="this.form.submit()">
                    <?php foreach ($data['reps'] as $r): ?>
                        <option value="<?= $r->id ?>" <?= $data['selected_rep_id'] == $r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->username) ?> (<?= htmlspecialchars($r->first_name . ' ' . $r->last_name) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px;" for="route_id">Daily Route</label>
                <select name="route_id" id="route_id" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;" onchange="this.form.submit()">
                    <option value="">All Routes</option>
                    <?php foreach ($data['routes'] as $rt): ?>
                        <option value="<?= $rt->id ?>" <?= $data['selected_route_id'] == $rt->id ? 'selected' : '' ?>><?= htmlspecialchars($rt->route_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px;" for="area_id">Territory / Area</label>
                <select name="area_id" id="area_id" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;" onchange="this.form.submit()">
                    <option value="">All Areas</option>
                    <?php foreach ($data['areas'] as $ar): ?>
                        <option value="<?= $ar->id ?>" <?= $data['selected_area_id'] == $ar->id ? 'selected' : '' ?>><?= htmlspecialchars($ar->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px;" for="month">Specific Month</label>
                <select name="month" id="month" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                    <?php for ($m = 1; $m <= 12; $m++): $mVal = str_pad((string)$m, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $mVal ?>" <?= $data['month'] === $mVal ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px;" for="year">Specific Year</label>
                <select name="year" id="year" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= $data['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 38px; flex: 1;"><i class="ph ph-funnel"></i> Apply Filter</button>
                <a href="<?= APP_URL ?>/repperformance" class="btn btn-outline" style="height: 38px; display: inline-flex; align-items: center; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #333;"><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Tab Selection Bar -->
    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('profileTab', this)"><i class="ph ph-user-circle"></i> Representative Profile</button>
        <button class="tab-btn" onclick="switchTab('compareTab', this)"><i class="ph ph-git-compare"></i> Side-by-Side Comparison</button>
        <button class="tab-btn" onclick="switchTab('rankingsTab', this)"><i class="ph ph-trophy"></i> Rankings &amp; Leaderboard</button>
    </div>

    <!-- PROFILE TAB -->
    <div id="profileTab" class="tab-pane active">

        <?php if (empty($data['perf_data'])): ?>
            <div class="glass-card" style="text-align: center; padding: 50px;">
                <i class="ph ph-warning" style="font-size: 40px; color: #f59e0b;"></i>
                <h3>No performance data calculated</h3>
                <p>Please select a representative and check active dates to compile the statistics.</p>
            </div>
        <?php else: $p = $data['perf_data']; ?>

            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <!-- Sales -->
                <div class="kpi-metric-card sales">
                    <div class="kpi-label">Total / Net Sales</div>
                    <div class="kpi-value" onclick="openDrilldown('sales')">Rs. <?= number_format($p['net_sales'], 2) ?></div>
                    <div class="kpi-sub">Total Bills: <?= $p['invoice_count'] ?> | Avg: Rs. <?= number_format($p['avg_invoice_value'], 2) ?></div>
                    <?php if ($p['kpi_scores']['sales_amount']['target'] > 0): ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= min(100, $p['kpi_scores']['sales_amount']['achievement_pct']) ?>%; background: #10b981;"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 5px;">Target Achieved: <?= number_format($p['kpi_scores']['sales_amount']['achievement_pct'], 1) ?>%</div>
                    <?php endif; ?>
                </div>

                <!-- Visited -->
                <div class="kpi-metric-card visits">
                    <div class="kpi-label">Customer Visits</div>
                    <div class="kpi-value" onclick="openDrilldown('visits')"><?= $p['productive_visits'] ?> / <?= $p['total_visited'] ?></div>
                    <div class="kpi-sub">New Acquired: <?= $p['new_customers_added'] ?> | Repeat: <?= $p['repeat_customers'] ?></div>
                    <?php if ($p['kpi_scores']['productive_visit_rate']['target'] > 0): ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= min(100, $p['productive_visit_rate']) ?>%; background: #0284c7;"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 5px;">Productive Rate: <?= number_format($p['productive_visit_rate'], 1) ?>%</div>
                    <?php endif; ?>
                </div>

                <!-- Routes -->
                <div class="kpi-metric-card routes">
                    <div class="kpi-label">Routes Completed</div>
                    <div class="kpi-value"><?= $p['completed_routes'] ?> / <?= $p['total_routes'] ?></div>
                    <div class="kpi-sub">Completion: <?= number_format($p['route_completion_rate'], 1) ?>%</div>
                    <?php if ($p['kpi_scores']['route_completion']['target'] > 0): ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= min(100, $p['route_completion_rate']) ?>%; background: #f59e0b;"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 5px;">Rate vs Target: <?= number_format($p['kpi_scores']['route_completion']['achievement_pct'], 1) ?>%</div>
                    <?php endif; ?>
                </div>

                <!-- Collections -->
                <div class="kpi-metric-card collections">
                    <div class="kpi-label">Collections</div>
                    <div class="kpi-value" onclick="openDrilldown('collections')">Rs. <?= number_format($p['total_collections'], 2) ?></div>
                    <div class="kpi-sub">Efficiency: <?= number_format($p['collection_efficiency'], 1) ?>%</div>
                    <?php if ($p['kpi_scores']['collection_efficiency']['target'] > 0): ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= min(100, $p['collection_efficiency']) ?>%; background: #8b5cf6;"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 5px;">Collection Rate: <?= number_format($p['collection_efficiency'], 1) ?>%</div>
                    <?php endif; ?>
                </div>

                <!-- Productivity -->
                <div class="kpi-metric-card productivity">
                    <div class="kpi-label">Working Days</div>
                    <div class="kpi-value"><?= $p['active_route_days'] ?> Days</div>
                    <div class="kpi-sub">Daily Sales Avg: Rs. <?= number_format($p['avg_daily_sales'], 2) ?></div>
                    <div class="kpi-sub">Daily Visits Avg: <?= number_format($p['avg_daily_visits'], 1) ?></div>
                </div>

                <!-- Credit limit and Outstanding -->
                <div class="kpi-metric-card productivity" style="border-left-color: #3b82f6;">
                    <div class="kpi-label">Credit Limit / Outstanding</div>
                    <div class="kpi-value">Rs. <?= number_format($p['total_outstanding'] ?? 0.00, 2) ?></div>
                    <div class="kpi-sub">Assigned Limit: Rs. <?= number_format($p['credit_limit'] ?? 0.00, 2) ?></div>
                    <?php if (($p['credit_limit'] ?? 0.00) > 0.00): ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= min(100, (($p['total_outstanding'] ?? 0.00) / $p['credit_limit']) * 100) ?>%; background: #3b82f6;"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 5px;">Limit Usage: <?= number_format((($p['total_outstanding'] ?? 0.00) / $p['credit_limit']) * 100, 1) ?>%</div>
                    <?php endif; ?>
                </div>

                <!-- Profitability / Expenses -->
                <div class="kpi-metric-card expenses">
                    <div class="kpi-label">Expenses</div>
                    <div class="kpi-value">Rs. <?= number_format($p['total_expenses'], 2) ?></div>
                    <div class="kpi-sub">Fuel: Rs. <?= number_format($p['fuel_expenses'], 2) ?> | Other: Rs. <?= number_format($p['other_expenses'], 2) ?></div>
                    <div class="kpi-sub" style="margin-top: 5px;">Expense Ratio: <?= number_format($p['sales_to_expense_ratio'], 1) ?>%</div>
                </div>
            </div>

            <!-- Export KPI Report buttons -->
            <div style="margin-bottom: 20px; text-align: right;">
                <span style="font-size: 13px; font-weight: bold; color: #475569; margin-right: 10px;">Download Reports:</span>
                <a href="<?= APP_URL ?>/repperformance/export/kpi?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> KPI Report</a>
                <a href="<?= APP_URL ?>/repperformance/export/sales?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Sales Report</a>
                <a href="<?= APP_URL ?>/repperformance/export/route?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Route Report</a>
                <a href="<?= APP_URL ?>/repperformance/export/collection?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Collection Report</a>
                <a href="<?= APP_URL ?>/repperformance/export/customer?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Customer Visit Report</a>
            </div>

            <!-- Two Panels: Charts + Breakdowns -->
            <div class="dashboard-panels">
                <div>
                    <!-- Charts Panel -->
                    <div class="glass-card" style="background-color: #ffffff;">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a;"><i class="ph ph-chart-line-up"></i> Sales &amp; Collections Trend</h4>
                        <div style="height: 320px; position: relative;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    <!-- KPI Target Contribution breakdown -->
                    <div class="glass-card" style="background-color: #ffffff;">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a;"><i class="ph ph-star"></i> KPI Evaluation Detail</h4>
                        <table class="perf-table">
                            <thead>
                                <tr>
                                    <th>Performance Dimension</th>
                                    <th>Target Value</th>
                                    <th>Actual Result</th>
                                    <th>Achievement %</th>
                                    <th>Assigned Weight</th>
                                    <th>Score Contribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($p['kpi_scores'] as $key => $sc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($sc['name']) ?></strong></td>
                                        <td><?= number_format($sc['target'], 2) ?></td>
                                        <td><?= number_format($sc['actual'], 2) ?></td>
                                        <td>
                                            <span style="font-weight: bold; color: <?= $sc['achievement_pct'] >= 100 ? '#10b981' : '#f59e0b' ?>;">
                                                <?= number_format($sc['achievement_pct'], 1) ?>%
                                            </span>
                                        </td>
                                        <td><?= number_format($sc['weight'], 1) ?>%</td>
                                        <td><strong><?= number_format($sc['contribution'], 1) ?>%</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <!-- Top lists panel -->
                    <div class="glass-card" style="background-color: #ffffff;">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a;"><i class="ph ph-crown"></i> Top Customers</h4>
                        <table class="perf-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th style="text-align: right;">Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($p['top_customers'] as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c->customer_name) ?></td>
                                        <td style="text-align: right; font-weight: bold;">Rs. <?= number_format($c->total_sales, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="glass-card" style="background-color: #ffffff;">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a;"><i class="ph ph-shopping-bag"></i> Top Products</h4>
                        <table class="perf-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th style="text-align: right;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($p['top_products'] as $pr): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pr->product_name) ?></td>
                                        <td style="text-align: right; font-weight: bold;"><?= number_format($pr->qty) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- COMPARISON TAB -->
    <div id="compareTab" class="tab-pane">
        <div class="glass-card" style="background-color: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h4 style="margin: 0; color: #0f172a;"><i class="ph ph-git-compare"></i> Side-by-Side Performance Comparison</h4>
                <form method="GET" action="<?= APP_URL ?>/repperformance" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="rep_user_id" value="<?= $data['selected_rep_id'] ?>">
                    <input type="hidden" name="start_date" value="<?= $data['start_date'] ?>">
                    <input type="hidden" name="end_date" value="<?= $data['end_date'] ?>">
                    <label for="compare_user_id" style="font-size: 13px; font-weight: bold;">Compare against: </label>
                    <select name="compare_user_id" id="compare_user_id" style="padding: 6px; border: 1px solid #cbd5e1; border-radius: 6px;" onchange="this.form.submit()">
                        <option value="">Select Competitor...</option>
                        <?php foreach ($data['reps'] as $r): if ($r->id != $data['selected_rep_id']): ?>
                            <option value="<?= $r->id ?>" <?= $data['compare_rep_id'] == $r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->username) ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </form>
            </div>

            <table class="perf-table" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>Performance Dimension</th>
                        <th>Selected Representative</th>
                        <th>Competitor Representative</th>
                        <th>Team Average</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['perf_data'])): $p = $data['perf_data']; $c = $data['compare_data']; $t = $data['team_avg']; ?>
                        <tr>
                            <td><strong>Overall KPI Performance Score</strong></td>
                            <td style="font-weight: bold; color: var(--c-primary);"><?= number_format($p['overall_score'], 1) ?>%</td>
                            <td style="font-weight: bold; color: var(--c-secondary);"><?= $c ? number_format($c['overall_score'], 1) . '%' : 'N/A' ?></td>
                            <td><?= number_format($t['overall_score'], 1) ?>%</td>
                        </tr>
                        <tr>
                            <td>Net Sales (LKR)</td>
                            <td style="font-weight: bold;">Rs. <?= number_format($p['net_sales'], 2) ?></td>
                            <td><?= $c ? 'Rs. ' . number_format($c['net_sales'], 2) : 'N/A' ?></td>
                            <td>Rs. <?= number_format($t['net_sales'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Total Collections (LKR)</td>
                            <td style="font-weight: bold;">Rs. <?= number_format($p['total_collections'], 2) ?></td>
                            <td><?= $c ? 'Rs. ' . number_format($c['total_collections'], 2) : 'N/A' ?></td>
                            <td>Rs. <?= number_format($t['total_collections'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Productive Customer Visits</td>
                            <td style="font-weight: bold;"><?= $p['productive_visits'] ?></td>
                            <td><?= $c ? $c['productive_visits'] : 'N/A' ?></td>
                            <td><?= number_format($t['productive_visits'], 1) ?></td>
                        </tr>
                        <tr>
                            <td>New Customer Acquisition</td>
                            <td style="font-weight: bold;"><?= $p['new_customers_added'] ?></td>
                            <td><?= $c ? $c['new_customers_added'] : 'N/A' ?></td>
                            <td><?= number_format($t['new_customers_added'], 1) ?></td>
                        </tr>
                        <tr>
                            <td>Route Completeness</td>
                            <td style="font-weight: bold;"><?= number_format($p['route_completion_rate'], 1) ?>%</td>
                            <td><?= $c ? number_format($c['route_completion_rate'], 1) . '%' : 'N/A' ?></td>
                            <td><?= number_format($t['total_routes'] > 0 ? 100.00 : 0.00, 1) ?>%</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RANKINGS TAB -->
    <div id="rankingsTab" class="tab-pane">
        <div class="glass-card" style="background-color: #ffffff;">
            <h4 style="margin: 0 0 15px 0; color: #0f172a;"><i class="ph ph-trophy"></i> Representative Rankings leaderboard</h4>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Rank</th>
                        <th>Representative</th>
                        <th style="text-align: right;">Sales Achievement (LKR)</th>
                        <th style="text-align: right;">Collections (LKR)</th>
                        <th style="text-align: right;">Performance Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($data['rankings'] as $rnk): ?>
                        <tr style="<?= $rnk['id'] == $data['selected_rep_id'] ? 'background-color:#e8f5e9;' : '' ?>">
                            <td style="text-align: center; font-weight: bold;">
                                <?php if ($rank === 1): ?>
                                    <i class="ph ph-crown-fill" style="color: #f59e0b; font-size: 18px;"></i>
                                <?php else: ?>
                                    <?= $rank ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($rnk['username']) ?></strong>
                                <br><span style="font-size: 11px; color:#666;"><?= htmlspecialchars($rnk['first_name'] . ' ' . $rnk['last_name']) ?></span>
                            </td>
                            <td style="text-align: right; font-weight: bold;">Rs. <?= number_format($rnk['net_sales'], 2) ?></td>
                            <td style="text-align: right;">Rs. <?= number_format($rnk['total_collections'], 2) ?></td>
                            <td style="text-align: right; font-weight: bold; color: var(--c-primary);"><?= number_format($rnk['score'], 1) ?>%</td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>



</div>

<!-- Drilldown modal overlay -->
<div class="drilldown-modal" id="drilldownModal" onclick="closeDrilldown(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="document.getElementById('drilldownModal').style.display='none'"><i class="ph ph-x"></i></button>
        <h3 id="modalTitle" style="margin-top: 0; color: var(--c-primary); display: flex; align-items: center; gap: 8px;"></h3>
        <div id="modalBody" style="margin-top: 15px;"></div>
    </div>
</div>

<script>
    // Tab switching controller
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
    }

    // Chart.js initialization
    <?php if (!empty($data['perf_data'])): $p = $data['perf_data']; ?>
        const dates = <?= json_encode(array_column($p['sales_trend'], 'label')) ?>;
        const salesData = <?= json_encode(array_map('floatval', array_column($p['sales_trend'], 'sales_amount'))) ?>;
        
        // Match collections trend to the same labels
        const collectionsMap = <?= json_encode(array_reduce($p['collections_trend'], function($carry, $item) {
            $carry[$item->label] = floatval($item->col_amount);
            return $carry;
        }, [])) ?>;
        
        const colData = dates.map(d => collectionsMap[d] || 0.00);

        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Net Sales Amount (LKR)',
                        data: salesData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Collections Amount (LKR)',
                        data: colData,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return 'Rs ' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    <?php endif; ?>

    // Drilldown overlay controllers
    const rawSales = <?= json_encode($data['perf_data']['recent_sales'] ?? []) ?>;
    const rawVisits = <?= json_encode($data['perf_data']['recent_unprod'] ?? []) ?>;
    const rawCollections = <?= json_encode($data['perf_data']['recent_collections'] ?? []) ?>;

    function openDrilldown(type) {
        const modal = document.getElementById('drilldownModal');
        const title = document.getElementById('modalTitle');
        const body = document.getElementById('modalBody');
        
        let html = '';
        if (type === 'sales') {
            title.innerHTML = '<i class="ph ph-receipt"></i> Recent Invoice Sales (Drilldown)';
            html = `<table class="perf-table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Invoice Date</th>
                        <th>Customer</th>
                        <th style="text-align: right;">Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>`;
            if (rawSales.length === 0) {
                html += `<tr><td colspan="6" style="text-align:center;">No recent invoice sales recorded in period.</td></tr>`;
            } else {
                rawSales.forEach(s => {
                    html += `<tr>
                        <td><strong>${s.invoice_number}</strong></td>
                        <td>${s.invoice_date}</td>
                        <td>${s.customer_name}</td>
                        <td style="text-align: right; font-weight: bold;">Rs. ${parseFloat(s.true_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td><span class="badge ${s.status === 'Paid' ? 'badge-success' : 'badge-warning'}">${s.status}</span></td>
                        <td><a href="<?= APP_URL ?>/sales/show/${s.id}" target="_blank" class="btn btn-outline btn-sm" style="padding: 2px 6px;">View Invoice</a></td>
                    </tr>`;
                });
            }
            html += '</tbody></table>';
        } else if (type === 'visits') {
            title.innerHTML = '<i class="ph ph-map-pin"></i> Recent Unproductive visits (Drilldown)';
            html = `<table class="perf-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Reason Category</th>
                        <th>Details/Remarks</th>
                    </tr>
                </thead>
                <tbody>`;
            if (rawVisits.length === 0) {
                html += `<tr><td colspan="4" style="text-align:center;">No recent unproductive visits logged.</td></tr>`;
            } else {
                rawVisits.forEach(v => {
                    html += `<tr>
                        <td>${v.visit_time}</td>
                        <td>${v.customer_name}</td>
                        <td><strong>${v.reason}</strong></td>
                        <td>${v.custom_reason || 'N/A'}</td>
                    </tr>`;
                });
            }
            html += '</tbody></table>';
        } else if (type === 'collections') {
            title.innerHTML = '<i class="ph ph-hand-coins"></i> Recent Collections & Payments (Drilldown)';
            html = `<table class="perf-table">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th style="text-align: right;">Amount Collected</th>
                    </tr>
                </thead>
                <tbody>`;
            if (rawCollections.length === 0) {
                html += `<tr><td colspan="5" style="text-align:center;">No recent payment collections.</td></tr>`;
            } else {
                rawCollections.forEach(c => {
                    html += `<tr>
                        <td>${c.payment_date}</td>
                        <td>${c.customer_name}</td>
                        <td><span class="badge badge-info">${c.payment_method}</span></td>
                        <td>${c.reference || 'N/A'}</td>
                        <td style="text-align: right; font-weight: bold; color:#10b981;">Rs. ${parseFloat(c.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    </tr>`;
                });
            }
            html += '</tbody></table>';
        }
        
        body.innerHTML = html;
        modal.style.display = 'flex';
    }

    function closeDrilldown(e) {
        if (e.target.id === 'drilldownModal') {
            document.getElementById('drilldownModal').style.display = 'none';
        }
    }
</script>
