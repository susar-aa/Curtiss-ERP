<!-- KPI & Representative Performance Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --c-glass: rgba(255, 255, 255, 0.85);
        --c-glass-border: rgba(226, 232, 240, 0.8);
        --glow-green: 0 0 12px rgba(16, 185, 129, 0.3);
        --glow-blue: 0 0 12px rgba(2, 132, 199, 0.3);
        --c-primary: #1b5e20;
        --c-secondary: #0284c7;
    }
    
    .perf-container {
        padding: 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
        font-family: "Outfit", "Inter", sans-serif;
    }

    .glass-card {
        background: var(--c-glass);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--c-glass-border);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
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
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }

    .kpi-score-badge {
        font-size: 20px;
        font-weight: 800;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        padding: 8px 16px;
        border-radius: 30px;
        box-shadow: var(--glow-green);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .score-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 0 12px rgba(245, 158, 11, 0.3);
    }

    .score-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
    }

    /* Tabs styling */
    .tab-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #cbd5e1;
        padding-bottom: 2px;
        overflow-x: auto;
    }

    .tab-btn {
        background: none;
        border: none;
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
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

    .tab-pane { display: none; }
    .tab-pane.active {
        display: block;
        animation: fadeIn 0.4s ease-in-out;
    }

    /* KPI Grid Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .kpi-metric-card {
        padding: 20px;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .kpi-metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }

    .kpi-metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .kpi-metric-card.sales::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .kpi-metric-card.visits::before { background: linear-gradient(90deg, #0284c7, #38bdf8); }
    .kpi-metric-card.routes::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .kpi-metric-card.collections::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
    .kpi-metric-card.productivity::before { background: linear-gradient(90deg, #ec4899, #f472b6); }
    .kpi-metric-card.expenses::before { background: linear-gradient(90deg, #ef4444, #f87171); }

    .kpi-label {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .kpi-value:hover {
        color: var(--c-primary);
    }

    .kpi-sub {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
    }

    .kpi-progress-bar {
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        margin-top: 12px;
        overflow: hidden;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }

    .kpi-progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 1s ease-in-out;
    }

    /* Layout divisions */
    .dashboard-panels {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .chart-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .chart-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    @media (max-width: 1024px) {
        .dashboard-panels, .chart-grid-2, .chart-grid-3 {
            grid-template-columns: 1fr;
        }
    }

    .perf-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .perf-table th {
        background: #f1f5f9;
        text-align: left;
        padding: 14px 12px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }

    .perf-table td {
        padding: 14px 12px;
        font-size: 14px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 500;
    }
    
    .perf-table tr:hover td {
        background-color: #f8fafc;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
    }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-info { background: #e0f2fe; color: #0369a1; }
    .badge-danger { background: #fee2e2; color: #991b1b; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Activity Feed styling */
    .activity-feed {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
    }
    
    .activity-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }
    
    .icon-sale { background: linear-gradient(135deg, #10b981, #059669); }
    .icon-collection { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .icon-visit { background: linear-gradient(135deg, #f59e0b, #d97706); }
    
    .activity-content {
        flex: 1;
    }
    
    .activity-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between;
    }
    
    .activity-desc {
        font-size: 13px;
        color: #64748b;
    }

</style>

<div class="perf-container">

    <!-- Flash Messages -->
    <?php if (!empty($data['success'])): ?>
        <div style="background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; padding: 12px; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(16,185,129,0.1);">
            <i class="ph ph-check-circle"></i> <?= htmlspecialchars($data['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['error'])): ?>
        <div style="background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(239,68,68,0.1);">
            <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($data['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Title and Overall Score -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="ph ph-presentation-chart" style="color: var(--c-primary);"></i>
            Rep Performance Dashboard
        </h1>
        <?php if (!empty($data['perf_data'])): 
            $score = $data['perf_data']['overall_score'];
            $scoreClass = '';
            if ($score < 50) $scoreClass = 'score-danger';
            elseif ($score < 80) $scoreClass = 'score-warning';
        ?>
            <div class="kpi-score-badge <?= $scoreClass ?>">
                <i class="ph ph-star-fill"></i>
                Score: <?= number_format($score, 1) ?>%
            </div>
        <?php endif; ?>
    </div>

    <!-- Filters Section -->
    <div class="glass-card">
        <form method="GET" action="<?= APP_URL ?>/repperformance" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;" for="rep_user_id">Representative</label>
                <select name="rep_user_id" id="rep_user_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-weight: 600;" onchange="this.form.submit()">
                    <?php foreach ($data['reps'] as $r): ?>
                        <option value="<?= $r->id ?>" <?= $data['selected_rep_id'] == $r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->username) ?> (<?= htmlspecialchars($r->first_name . ' ' . $r->last_name) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;" for="route_id">Daily Route</label>
                <select name="route_id" id="route_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-weight: 600;" onchange="this.form.submit()">
                    <option value="">All Routes</option>
                    <?php foreach ($data['routes'] as $rt): ?>
                        <option value="<?= $rt->id ?>" <?= $data['selected_route_id'] == $rt->id ? 'selected' : '' ?>><?= htmlspecialchars($rt->route_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;" for="area_id">Territory / Area</label>
                <select name="area_id" id="area_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-weight: 600;" onchange="this.form.submit()">
                    <option value="">All Areas</option>
                    <?php foreach ($data['areas'] as $ar): ?>
                        <option value="<?= $ar->id ?>" <?= $data['selected_area_id'] == $ar->id ? 'selected' : '' ?>><?= htmlspecialchars($ar->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;" for="month">Specific Month</label>
                <select name="month" id="month" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-weight: 600;">
                    <?php for ($m = 1; $m <= 12; $m++): $mVal = str_pad((string)$m, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $mVal ?>" <?= $data['month'] === $mVal ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;" for="year">Specific Year</label>
                <select name="year" id="year" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; font-weight: 600;">
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= $data['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="height: 42px; flex: 1; border-radius: 8px; font-weight: 700; box-shadow: 0 4px 12px rgba(27,94,32,0.2);"><i class="ph ph-funnel"></i> Apply</button>
                <a href="<?= APP_URL ?>/repperformance" class="btn btn-outline" style="height: 42px; display: inline-flex; align-items: center; padding: 0 16px; border: 1px solid #cbd5e1; border-radius: 8px; text-decoration: none; color: #333; font-weight: 700;"><i class="ph ph-arrow-counter-clockwise"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Tab Selection Bar -->
    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('profileTab', this)"><i class="ph ph-user-circle"></i> Rep Profile & KPIs</button>
        <button class="tab-btn" onclick="switchTab('analyticsTab', this)"><i class="ph ph-chart-pie-slice"></i> Deep Analytics & Mix</button>
        <button class="tab-btn" onclick="switchTab('compareTab', this)"><i class="ph ph-git-compare"></i> Side-by-Side Compare</button>
        <button class="tab-btn" onclick="switchTab('rankingsTab', this)"><i class="ph ph-trophy"></i> Leaderboard</button>
    </div>

    <!-- PROFILE TAB -->
    <div id="profileTab" class="tab-pane active">

        <?php if (empty($data['perf_data'])): ?>
            <div class="glass-card" style="text-align: center; padding: 60px;">
                <i class="ph ph-warning" style="font-size: 48px; color: #f59e0b; margin-bottom: 15px;"></i>
                <h3 style="font-size: 24px; color: #0f172a; margin-bottom: 10px;">No performance data calculated</h3>
                <p style="color: #64748b;">Please select a representative and check active dates to compile the statistics.</p>
            </div>
        <?php else: $p = $data['perf_data']; ?>

            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <!-- Sales -->
                <div class="kpi-metric-card sales">
                    <div class="kpi-label">Total / Net Sales</div>
                    <div class="kpi-value">Rs. <?= number_format($p['net_sales'], 2) ?></div>
                    <div class="kpi-sub">Bills: <?= $p['invoice_count'] ?> | Returns: Rs. <?= number_format($p['total_returns'], 2) ?></div>
                    <?php if (($p['kpi_scores']['sales_amount']['target'] ?? 0) > 0): 
                        $pct = min(100, (($p['net_sales'] / $p['kpi_scores']['sales_amount']['target']) * 100));
                    ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 8px; display:flex; justify-content: space-between;">
                            <span>Target: Rs. <?= number_format($p['kpi_scores']['sales_amount']['target'], 2) ?></span>
                            <span style="font-weight:700; color:#10b981;"><?= number_format($pct, 1) ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Visited -->
                <div class="kpi-metric-card visits">
                    <div class="kpi-label">Customer Visits</div>
                    <div class="kpi-value"><?= $p['productive_visits'] ?> <span style="font-size: 16px; color:#64748b; font-weight:600;">/ <?= $p['total_visited'] ?> Total</span></div>
                    <div class="kpi-sub">New Acquired: <strong><?= $p['new_customers_added'] ?></strong> | Repeat: <strong><?= $p['repeat_customers'] ?></strong></div>
                    <?php if (($p['kpi_scores']['productive_visit_rate']['target'] ?? 0) > 0): 
                        $pct = min(100, (($p['productive_visits'] / $p['kpi_scores']['productive_visit_rate']['target']) * 100));
                    ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #0284c7, #38bdf8);"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 8px; display:flex; justify-content: space-between;">
                            <span>Target: <?= $p['kpi_scores']['productive_visit_rate']['target'] ?> prod. visits</span>
                            <span style="font-weight:700; color:#0284c7;"><?= number_format($pct, 1) ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Routes -->
                <div class="kpi-metric-card routes">
                    <div class="kpi-label">Routes Completed</div>
                    <div class="kpi-value"><?= $p['completed_routes'] ?> <span style="font-size: 16px; color:#64748b; font-weight:600;">/ <?= $p['total_routes'] ?></span></div>
                    <div class="kpi-sub">Completion Rate: <strong><?= number_format($p['route_completion_rate'], 1) ?>%</strong></div>
                    <?php if (($p['kpi_scores']['route_completion']['target'] ?? 0) > 0): 
                        $pct = min(100, (($p['active_route_days'] / $p['kpi_scores']['route_completion']['target']) * 100));
                    ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 8px; display:flex; justify-content: space-between;">
                            <span>Target Days: <?= $p['kpi_scores']['route_completion']['target'] ?></span>
                            <span style="font-weight:700; color:#f59e0b;"><?= number_format($pct, 1) ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Collections -->
                <div class="kpi-metric-card collections">
                    <div class="kpi-label">Collections</div>
                    <div class="kpi-value">Rs. <?= number_format($p['total_collections'], 2) ?></div>
                    <div class="kpi-sub">Efficiency: <strong><?= number_format($p['collection_efficiency'], 1) ?>%</strong></div>
                    <?php if (($p['kpi_scores']['collection_efficiency']['target'] ?? 0) > 0): 
                        $pct = min(100, $p['collection_efficiency']);
                    ?>
                        <div class="kpi-progress-bar">
                            <div class="kpi-progress-fill" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #8b5cf6, #a78bfa);"></div>
                        </div>
                        <div class="kpi-sub" style="margin-top: 8px; display:flex; justify-content: space-between;">
                            <span>Target Efficiency: <?= $p['kpi_scores']['collection_efficiency']['target'] ?>%</span>
                            <span style="font-weight:700; color:#8b5cf6;"><?= number_format($pct, 1) ?>%</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Export KPI Report buttons -->
            <div class="glass-card" style="padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-weight: 800; color: #0f172a;"><i class="ph ph-download-simple"></i> Export Data</div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?= APP_URL ?>/repperformance/export/kpi?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> KPI</a>
                    <a href="<?= APP_URL ?>/repperformance/export/sales?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Sales</a>
                    <a href="<?= APP_URL ?>/repperformance/export/route?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Routes</a>
                    <a href="<?= APP_URL ?>/repperformance/export/collection?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="btn btn-outline btn-sm"><i class="ph ph-file-csv"></i> Collections</a>
                </div>
            </div>

            <div class="dashboard-panels">
                <!-- Dual Axis Trend Chart -->
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 18px; font-weight:800;"><i class="ph ph-chart-line-up"></i> Sales &amp; Collections Trend</h4>
                    <div style="height: 380px; position: relative;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Radar Chart -->
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 18px; font-weight:800;"><i class="ph ph-radar"></i> Performance Radar</h4>
                    <div style="height: 350px; position: relative;">
                        <canvas id="radarChart"></canvas>
                    </div>
                    <div style="text-align:center; font-size: 12px; color:#64748b; margin-top: 10px;">Compares actual achievement vs targets across all dimensions.</div>
                </div>
            </div>

            <!-- Activity Feed & Tables -->
            <div class="dashboard-panels">
                
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 18px; font-weight:800;"><i class="ph ph-activity"></i> Live Activity Feed</h4>
                    <div class="activity-feed">
                        <?php 
                        // Merge and sort activities by date descending
                        $activities = [];
                        foreach ($p['recent_sales'] as $s) {
                            $activities[] = [
                                'type' => 'sale',
                                'date' => $s->invoice_date,
                                'title' => 'Invoice Created',
                                'subtitle' => $s->customer_name,
                                'amount' => $s->true_amount,
                                'ref' => $s->invoice_number,
                                'icon' => 'ph-receipt'
                            ];
                        }
                        foreach ($p['recent_collections'] as $c) {
                            $activities[] = [
                                'type' => 'collection',
                                'date' => $c->payment_date,
                                'title' => 'Payment Received',
                                'subtitle' => $c->customer_name,
                                'amount' => $c->amount,
                                'ref' => $c->payment_method . ' ' . $c->reference,
                                'icon' => 'ph-hand-coins'
                            ];
                        }
                        foreach ($p['recent_unprod'] as $v) {
                            $activities[] = [
                                'type' => 'visit',
                                'date' => date('Y-m-d', strtotime($v->visit_time)),
                                'title' => 'Unproductive Visit',
                                'subtitle' => $v->customer_name,
                                'amount' => 0,
                                'ref' => $v->reason,
                                'icon' => 'ph-map-pin'
                            ];
                        }
                        
                        usort($activities, function($a, $b) {
                            return strtotime($b['date']) - strtotime($a['date']);
                        });
                        
                        $count = 0;
                        if (empty($activities)): ?>
                            <div style="text-align:center; padding: 30px; color:#94a3b8;">No recent activities found.</div>
                        <?php else:
                        foreach ($activities as $act):
                            if ($count >= 15) break; $count++;
                            $iconCls = 'icon-' . $act['type'];
                        ?>
                            <div class="activity-item">
                                <div class="activity-icon <?= $iconCls ?>"><i class="ph <?= $act['icon'] ?>"></i></div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?= htmlspecialchars($act['title']) ?> 
                                        <?php if ($act['amount'] > 0): ?>
                                            <span style="color: var(--c-primary);">Rs. <?= number_format($act['amount'], 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-desc">
                                        <strong><?= htmlspecialchars($act['subtitle']) ?></strong> &bull; <?= htmlspecialchars($act['date']) ?>
                                        <br>
                                        Ref: <?= htmlspecialchars($act['ref']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div>
                    <!-- Category Sales Breakdown Doughnut -->
                    <div class="glass-card">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 18px; font-weight:800;"><i class="ph ph-pie-chart"></i> Product Mix</h4>
                        <div style="height: 250px; position: relative;">
                            <canvas id="categoryMixChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="glass-card" style="padding: 15px;">
                        <h4 style="margin: 0 0 10px 0; color: #0f172a; font-size: 16px; font-weight:800;"><i class="ph ph-crown"></i> Top 5 Customers</h4>
                        <table class="perf-table" style="font-size: 13px;">
                            <tbody>
                                <?php if (empty($p['top_customers'])): ?>
                                    <tr><td colspan="2" style="text-align:center; color:#94a3b8;">No clients found.</td></tr>
                                <?php else: foreach ($p['top_customers'] as $c): ?>
                                    <tr>
                                        <td style="padding: 8px 12px;"><strong><?= htmlspecialchars($c->customer_name) ?></strong></td>
                                        <td style="text-align: right; font-weight: 800; color: var(--c-primary); padding: 8px 12px;">Rs. <?= number_format($c->total_sales, 0) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
    
    <!-- ANALYTICS TAB -->
    <div id="analyticsTab" class="tab-pane">
        <?php if (!empty($data['perf_data'])): $p = $data['perf_data']; ?>
            <div class="chart-grid-3">
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 16px; font-weight:800;"><i class="ph ph-shopping-bag"></i> Top Products Sold</h4>
                    <table class="perf-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th style="text-align: right;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($p['top_products'])): ?>
                                <tr><td colspan="2" style="text-align:center; color:#94a3b8;">No items sold.</td></tr>
                            <?php else: foreach ($p['top_products'] as $pr): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pr->product_name) ?></strong></td>
                                    <td style="text-align: right; font-weight: 800; color: var(--c-secondary);"><?= number_format($pr->qty) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 16px; font-weight:800;"><i class="ph ph-tag"></i> Category Revenue</h4>
                    <table class="perf-table">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th style="text-align: right;">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($p['top_categories'])): ?>
                                <tr><td colspan="2" style="text-align:center; color:#94a3b8;">No categories found.</td></tr>
                            <?php else: foreach ($p['top_categories'] as $cat): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($cat->category_name) ?></strong></td>
                                    <td style="text-align: right; font-weight: 800; color: var(--c-primary);">Rs. <?= number_format($cat->total_sales, 2) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 16px; font-weight:800;"><i class="ph ph-wallet"></i> Collections Splitting</h4>
                    <div style="height: 250px; position: relative;">
                        <canvas id="paymentPieChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-panels">
                <!-- 6 Month Trend -->
                <?php if (!empty($data['monthly_trend'])): ?>
                    <div class="glass-card">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 18px; font-weight:800;"><i class="ph ph-calendar"></i> 6-Month Historical Sales &amp; KPI Score Profile</h4>
                        <div style="height: 350px; position: relative;">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- KPI Evaluation Table -->
                <div class="glass-card">
                    <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 18px; font-weight:800;"><i class="ph ph-star"></i> KPI Breakdown</h4>
                    <table class="perf-table">
                        <thead>
                            <tr>
                                <th>Dimension</th>
                                <th>Target</th>
                                <th>Result</th>
                                <th>Achievement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($p['kpi_scores'] as $key => $sc): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($sc['name']) ?></strong></td>
                                    <td><?= number_format($sc['target'], 2) ?></td>
                                    <td><?= number_format($sc['actual'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $sc['achievement_pct'] >= 100 ? 'badge-success' : 'badge-warning' ?>">
                                            <?= number_format($sc['achievement_pct'], 1) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- COMPARISON TAB -->
    <div id="compareTab" class="tab-pane">
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <h4 style="margin: 0; color: #0f172a; font-size: 20px; font-weight:800;"><i class="ph ph-git-compare"></i> Side-by-Side Performance Comparison</h4>
                <form method="GET" action="<?= APP_URL ?>/repperformance" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="rep_user_id" value="<?= $data['selected_rep_id'] ?>">
                    <input type="hidden" name="month" value="<?= $data['month'] ?>">
                    <input type="hidden" name="year" value="<?= $data['year'] ?>">
                    <label for="compare_user_id" style="font-size: 14px; font-weight: 700;">Compare against: </label>
                    <select name="compare_user_id" id="compare_user_id" style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight:600;" onchange="this.form.submit()">
                        <option value="">Select Competitor...</option>
                        <?php foreach ($data['reps'] as $r): if ($r->id != $data['selected_rep_id']): ?>
                            <option value="<?= $r->id ?>" <?= $data['compare_rep_id'] == $r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->username) ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- Side by Side Bar comparison Chart -->
            <?php if (!empty($data['compare_data'])): ?>
                <div style="height: 350px; position: relative; margin-bottom: 30px;">
                    <canvas id="compareBarChart"></canvas>
                </div>
            <?php endif; ?>

            <table class="perf-table" style="font-size: 15px;">
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
                            <td style="font-weight: 800; color: var(--c-primary); font-size: 16px;"><?= number_format($p['overall_score'], 1) ?>%</td>
                            <td style="font-weight: 800; color: var(--c-secondary); font-size: 16px;"><?= $c ? number_format($c['overall_score'], 1) . '%' : 'N/A' ?></td>
                            <td style="font-weight: 800; color: #64748b;"><?= number_format($t['overall_score'], 1) ?>%</td>
                        </tr>
                        <tr>
                            <td>Net Sales (LKR)</td>
                            <td style="font-weight: 700;">Rs. <?= number_format($p['net_sales'], 2) ?></td>
                            <td style="font-weight: 700;"><?= $c ? 'Rs. ' . number_format($c['net_sales'], 2) : 'N/A' ?></td>
                            <td>Rs. <?= number_format($t['net_sales'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Total Collections (LKR)</td>
                            <td style="font-weight: 700;">Rs. <?= number_format($p['total_collections'], 2) ?></td>
                            <td style="font-weight: 700;"><?= $c ? 'Rs. ' . number_format($c['total_collections'], 2) : 'N/A' ?></td>
                            <td>Rs. <?= number_format($t['total_collections'], 2) ?></td>
                        </tr>
                        <tr>
                            <td>Productive Customer Visits</td>
                            <td style="font-weight: 700;"><?= $p['productive_visits'] ?></td>
                            <td style="font-weight: 700;"><?= $c ? $c['productive_visits'] : 'N/A' ?></td>
                            <td><?= number_format($t['productive_visits'], 1) ?></td>
                        </tr>
                        <tr>
                            <td>New Customer Acquisition</td>
                            <td style="font-weight: 700;"><?= $p['new_customers_added'] ?></td>
                            <td style="font-weight: 700;"><?= $c ? $c['new_customers_added'] : 'N/A' ?></td>
                            <td><?= number_format($t['new_customers_added'], 1) ?></td>
                        </tr>
                        <tr>
                            <td>Route Completeness</td>
                            <td style="font-weight: 700;"><?= number_format($p['route_completion_rate'], 1) ?>%</td>
                            <td style="font-weight: 700;"><?= $c ? number_format($c['route_completion_rate'], 1) . '%' : 'N/A' ?></td>
                            <td><?= number_format($t['total_routes'] > 0 ? 100.00 : 0.00, 1) ?>%</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RANKINGS TAB -->
    <div id="rankingsTab" class="tab-pane">
        <div class="glass-card">
            <h4 style="margin: 0 0 20px 0; color: #0f172a; font-size: 20px; font-weight:800;"><i class="ph ph-trophy"></i> Representative Rankings Leaderboard</h4>
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
                        <tr style="<?= $rnk['id'] == $data['selected_rep_id'] ? 'background-color:#f0fdf4;' : '' ?>">
                            <td style="text-align: center; font-weight: 800; font-size: 18px;">
                                <?php if ($rank === 1): ?>
                                    <i class="ph ph-crown-fill" style="color: #f59e0b; font-size: 24px; filter: drop-shadow(0 2px 4px rgba(245,158,11,0.4));"></i>
                                <?php elseif ($rank === 2): ?>
                                    <i class="ph ph-medal-fill" style="color: #94a3b8; font-size: 22px;"></i>
                                <?php elseif ($rank === 3): ?>
                                    <i class="ph ph-medal-fill" style="color: #b45309; font-size: 22px;"></i>
                                <?php else: ?>
                                    <?= $rank ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($rnk['username']) ?></strong>
                                <br><span style="font-size: 12px; color:#64748b;"><?= htmlspecialchars($rnk['first_name'] . ' ' . $rnk['last_name']) ?></span>
                            </td>
                            <td style="text-align: right; font-weight: 800;">Rs. <?= number_format($rnk['net_sales'], 2) ?></td>
                            <td style="text-align: right; font-weight: 700; color:#475569;">Rs. <?= number_format($rnk['total_collections'], 2) ?></td>
                            <td style="text-align: right; font-weight: 900; font-size: 16px; color: var(--c-primary);">
                                <?php if($rnk['id'] == $data['selected_rep_id']) echo '<i class="ph ph-caret-right" style="color:var(--c-primary);"></i>'; ?>
                                <?= number_format($rnk['score'], 1) ?>%
                            </td>
                        </tr>
                    <?php $rank++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
    }

    // Charting Configuration
    Chart.defaults.font.family = '"Outfit", "Inter", sans-serif';
    Chart.defaults.color = '#475569';

    <?php if (!empty($data['perf_data'])): $p = $data['perf_data']; ?>
        
        // 1. Dual Axis Trend Chart
        const dates = <?= json_encode(array_column($p['sales_trend'], 'label')) ?>;
        const salesData = <?= json_encode(array_map('floatval', array_column($p['sales_trend'], 'sales_amount'))) ?>;
        const collectionsMap = <?= json_encode(array_reduce($p['collections_trend'], function($carry, $item) {
            $carry[$item->label] = floatval($item->col_amount);
            return $carry;
        }, [])) ?>;
        const colData = dates.map(d => collectionsMap[d] || 0.00);

        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Net Sales (Bar)',
                        data: salesData,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderRadius: 4,
                        order: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Collections (Line)',
                        data: colData,
                        type: 'line',
                        borderColor: '#8b5cf6',
                        backgroundColor: '#8b5cf6',
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#8b5cf6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        order: 1,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { 
                    legend: { position: 'top', labels: { font: {weight: 'bold'} } },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: {size: 14},
                        bodyFont: {size: 13, weight: 'bold'},
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.5)' },
                        ticks: { callback: function(value) { return 'Rs ' + (value/1000) + 'k'; }, font: {weight: '600'} }
                    }
                }
            }
        });

        // 2. Performance Radar Chart
        const radarCtx = document.getElementById('radarChart').getContext('2d');
        const kpiKeys = <?= json_encode(array_values(array_map(fn($sc) => $sc['name'], $p['kpi_scores']))) ?>;
        const kpiPcts = <?= json_encode(array_values(array_map(fn($sc) => min(100, $sc['achievement_pct']), $p['kpi_scores']))) ?>;
        
        new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: kpiKeys,
                datasets: [{
                    label: 'Achievement %',
                    data: kpiPcts,
                    backgroundColor: 'rgba(27, 94, 32, 0.2)',
                    borderColor: '#1b5e20',
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#10b981',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(226, 232, 240, 0.8)' },
                        grid: { color: 'rgba(226, 232, 240, 0.8)' },
                        pointLabels: { font: { size: 11, weight: '700' }, color: '#475569' },
                        ticks: { min: 0, max: 100, stepSize: 20, display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 3. Category Mix Doughnut Chart
        <?php if (!empty($p['top_categories'])): ?>
            const mixLabels = <?= json_encode(array_column($p['top_categories'], 'category_name')) ?>;
            const mixData = <?= json_encode(array_map('floatval', array_column($p['top_categories'], 'total_sales'))) ?>;
            
            const mixCtx = document.getElementById('categoryMixChart').getContext('2d');
            new Chart(mixCtx, {
                type: 'doughnut',
                data: {
                    labels: mixLabels,
                    datasets: [{
                        data: mixData,
                        backgroundColor: ['#10b981', '#0284c7', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { 
                        legend: { position: 'right', labels: { boxWidth: 12, usePointStyle: true, font: {weight: '600'} } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed !== null) {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'LKR' }).format(context.parsed);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
        
        // 4. Payment Splitting Chart
        const payCtx = document.getElementById('paymentPieChart').getContext('2d');
        new Chart(payCtx, {
            type: 'pie',
            data: {
                labels: ['Cash', 'Cheque', 'Bank Transfer'],
                datasets: [{
                    data: [<?= $p['cash_collections'] ?>, <?= $p['cheque_collections'] ?>, <?= $p['bank_collections'] ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, font: {weight: '600'} } } }
            }
        });
    <?php endif; ?>

    // 5. 6-Month Monthly Historical Performance Trend Chart
    <?php if (!empty($data['monthly_trend'])): ?>
        const trendLabels = <?= json_encode(array_column($data['monthly_trend'], 'label')) ?>;
        const trendSales = <?= json_encode(array_map('floatval', array_column($data['monthly_trend'], 'net_sales'))) ?>;
        const trendCollections = <?= json_encode(array_map('floatval', array_column($data['monthly_trend'], 'total_collections'))) ?>;
        const trendScores = <?= json_encode(array_map('floatval', array_column($data['monthly_trend'], 'overall_score'))) ?>;

        const mTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(mTrendCtx, {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Net Sales',
                        data: trendSales,
                        backgroundColor: 'rgba(2, 132, 199, 0.7)',
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Collections',
                        data: trendCollections,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'KPI Score (%)',
                        data: trendScores,
                        borderColor: '#ef4444',
                        backgroundColor: '#ef4444',
                        borderWidth: 3,
                        type: 'line',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { font: {weight: 'bold'} } } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: 'rgba(226, 232, 240, 0.5)' },
                        ticks: { callback: function(value) { return 'Rs ' + (value/1000) + 'k'; }, font: {weight: '600'} }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 100,
                        grid: { drawOnChartArea: false },
                        ticks: { callback: function(value) { return value + '%'; }, font: {weight: '700', color: '#ef4444'} }
                    }
                }
            }
        });
    <?php endif; ?>

    // 6. Compare Bar Chart
    <?php if (!empty($data['compare_data'])): $p = $data['perf_data']; $c = $data['compare_data']; $t = $data['team_avg']; ?>
        const compCtx = document.getElementById('compareBarChart').getContext('2d');
        new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: ['KPI Score (%)', 'Net Sales (10k LKR)', 'Collections (10k LKR)', 'Productive Visits'],
                datasets: [
                    {
                        label: '<?= htmlspecialchars($data['reps'][array_search($data['selected_rep_id'], array_column($data['reps'], 'id'))]->username ?? 'Selected Rep') ?>',
                        data: [
                            <?= floatval($p['overall_score']) ?>, 
                            <?= floatval($p['net_sales'] / 10000) ?>, 
                            <?= floatval($p['total_collections'] / 10000) ?>, 
                            <?= floatval($p['productive_visits']) ?>
                        ],
                        backgroundColor: 'rgba(27, 94, 32, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: '<?= htmlspecialchars($data['reps'][array_search($data['compare_rep_id'], array_column($data['reps'], 'id'))]->username ?? 'Competitor') ?>',
                        data: [
                            <?= floatval($c['overall_score']) ?>, 
                            <?= floatval($c['net_sales'] / 10000) ?>, 
                            <?= floatval($c['total_collections'] / 10000) ?>, 
                            <?= floatval($c['productive_visits']) ?>
                        ],
                        backgroundColor: 'rgba(2, 132, 199, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: 'Team Average',
                        data: [
                            <?= floatval($t['overall_score']) ?>, 
                            <?= floatval($t['net_sales'] / 10000) ?>, 
                            <?= floatval($t['total_collections'] / 10000) ?>, 
                            <?= floatval($t['productive_visits']) ?>
                        ],
                        backgroundColor: 'rgba(100, 116, 139, 0.8)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: {weight: 'bold'} } } },
                scales: {
                    x: { grid: { display: false }, ticks: {font: {weight: '700'}} },
                    y: { grid: { color: 'rgba(226, 232, 240, 0.5)' } }
                }
            }
        });
    <?php endif; ?>

</script>