<?php
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .welcome-banner { margin-bottom: 25px; }
    .welcome-banner h2 { margin: 0 0 5px 0; color: var(--text-main); }
    .welcome-banner p { margin: 0; color: #666; font-size: 14px; }
    
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border-left: 4px solid #ccc; }
    @media (prefers-color-scheme: dark) { .kpi-card { background: #1e1e2d; } }
    
    .kpi-card.revenue { border-color: #2e7d32; }
    .kpi-card.expense { border-color: #c62828; }
    .kpi-card.profit { border-color: #0066cc; }
    .kpi-card.ar { border-color: #f57c00; }
    
    .kpi-title { font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; font-weight: 600; }
    .kpi-value { font-size: 24px; font-weight: bold; color: var(--text-main); }
    
    .dashboard-lower { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    
    .activity-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .activity-table th, .activity-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--mac-border); font-size: 14px; }
    .activity-table th { background-color: rgba(0,0,0,0.02); font-weight: 600; font-size: 13px; }
</style>

<div class="welcome-banner">
    <h2>Financial Overview</h2>
    <p>Welcome back, <?= htmlspecialchars($data['username']) ?>. Here is your business snapshot.</p>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card revenue">
        <div class="kpi-title">Total Revenue</div>
        <div class="kpi-value" style="color: #2e7d32;">Rs: <?= number_format($data['revenue'], 2) ?></div>
    </div>
    <div class="kpi-card expense">
        <div class="kpi-title">Total Expenses</div>
        <div class="kpi-value" style="color: #c62828;">Rs: <?= number_format($data['expenses'], 2) ?></div>
    </div>
    <div class="kpi-card profit">
        <div class="kpi-title">Net Profit</div>
        <div class="kpi-value" style="color: #0066cc;">Rs: <?= number_format($data['profit'], 2) ?></div>
    </div>
    <div class="kpi-card ar">
        <div class="kpi-title">Accounts Receivable</div>
        <div class="kpi-value">Rs: <?= number_format($data['ar'], 2) ?></div>
    </div>
</div>

<div class="dashboard-lower">
    <!-- Recent Activity Table -->
    <div class="card">
        <h3 style="margin-top:0; font-size: 16px;">Recent Journal Activity</h3>
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Posted By</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['recent_activity'])): ?>
                <tr><td colspan="4" style="text-align:center; color:#888;">No recent activity.</td></tr>
                <?php else: foreach($data['recent_activity'] as $activity): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($activity->entry_date)) ?></td>
                    <td><strong><?= htmlspecialchars($activity->reference) ?></strong></td>
                    <td><?= htmlspecialchars($activity->description) ?></td>
                    <td><?= htmlspecialchars($activity->username) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        <div style="margin-top: 15px; text-align: right;">
            <a href="<?= APP_URL ?>/accounting/journal" style="color: #0066cc; text-decoration: none; font-size: 13px; font-weight: bold;">View All Journals &rarr;</a>
        </div>
    </div>

    <!-- Financial Chart -->
    <div class="card" style="display: flex; flex-direction: column; align-items: center;">
        <h3 style="margin-top:0; width: 100%; font-size: 16px;">Income vs Expenses</h3>
        <div style="position: relative; width: 100%; max-width: 250px; margin: auto;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>
</div>

<script>
    // Initialize Chart.js Doughnut Chart
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('financeChart').getContext('2d');
        const rev = <?= $data['revenue'] > 0 ? $data['revenue'] : 0 ?>;
        const exp = <?= $data['expenses'] > 0 ? $data['expenses'] : 0 ?>;
        
        // If no data exists yet, show a grey placeholder ring
        const hasData = rev > 0 || exp > 0;
        const chartData = hasData ? [rev, exp] : [1];
        const bgColors = hasData ? ['#2e7d32', '#c62828'] : ['#e0e0e0'];
        const labels = hasData ? ['Revenue', 'Expenses'] : ['No Data'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: chartData,
                    backgroundColor: bgColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
                }
            }
        });
    });
</script>