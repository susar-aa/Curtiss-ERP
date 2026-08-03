<?php
$html = <<<'PHPEOF'
<!-- =========================================================
     Rep Performance Dashboard — Premium Redesign
     ========================================================= -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ── DESIGN TOKENS ─────────────────────────────────────────── */
:root {
    --rp-bg:        #0d1117;
    --rp-surface:   #161b22;
    --rp-surface2:  #1c2433;
    --rp-border:    rgba(255,255,255,0.06);
    --rp-border2:   rgba(255,255,255,0.10);
    --rp-green:     #00d48a;
    --rp-green-dim: rgba(0,212,138,0.12);
    --rp-blue:      #3b82f6;
    --rp-blue-dim:  rgba(59,130,246,0.12);
    --rp-amber:     #f59e0b;
    --rp-amber-dim: rgba(245,158,11,0.12);
    --rp-violet:    #a78bfa;
    --rp-violet-dim:rgba(167,139,250,0.12);
    --rp-red:       #f87171;
    --rp-red-dim:   rgba(248,113,113,0.12);
    --rp-text:      #e2e8f0;
    --rp-text-muted:#8b98b0;
    --rp-text-dim:  #4a5568;
    --rp-radius:    14px;
    --rp-radius-sm: 8px;
    --rp-shadow:    0 4px 24px rgba(0,0,0,0.4);
    --rp-shadow-lg: 0 8px 48px rgba(0,0,0,0.6);
}

/* ── RESET & BASE ───────────────────────────────────────────── */
.rp-wrap * { box-sizing: border-box; }
.rp-wrap {
    background: var(--rp-bg);
    min-height: calc(100vh - 60px);
    padding: 0;
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--rp-text);
}

/* ── HERO BANNER ────────────────────────────────────────────── */
.rp-hero {
    background: linear-gradient(135deg, #0d1117 0%, #1a2744 50%, #0d1117 100%);
    padding: 28px 32px 0;
    border-bottom: 1px solid var(--rp-border2);
    position: relative;
    overflow: hidden;
}
.rp-hero::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(0,212,138,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.rp-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: 40%;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 70%);
    pointer-events: none;
}

.rp-hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.rp-hero-title {
    display: flex;
    align-items: center;
    gap: 14px;
}
.rp-hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--rp-green), #0ea5e9);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    box-shadow: 0 0 24px rgba(0,212,138,0.3);
}
.rp-hero-title h1 {
    font-size: 22px;
    font-weight: 800;
    margin: 0;
    color: #fff;
    letter-spacing: -0.5px;
}
.rp-hero-title p {
    margin: 2px 0 0;
    font-size: 13px;
    color: var(--rp-text-muted);
    font-weight: 500;
}
.rp-score-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 800;
    border: 1px solid;
    letter-spacing: 0.3px;
}
.rp-score-pill.good   { color: var(--rp-green);  background: var(--rp-green-dim);  border-color: rgba(0,212,138,0.25); }
.rp-score-pill.warn   { color: var(--rp-amber);  background: var(--rp-amber-dim);  border-color: rgba(245,158,11,0.25); }
.rp-score-pill.danger { color: var(--rp-red);    background: var(--rp-red-dim);    border-color: rgba(248,113,113,0.25); }

/* ── FILTER BAR ─────────────────────────────────────────────── */
.rp-filter-bar {
    display: flex;
    gap: 10px;
    padding: 14px 32px;
    background: var(--rp-surface);
    border-bottom: 1px solid var(--rp-border);
    flex-wrap: wrap;
    align-items: flex-end;
}
.rp-filter-group { display: flex; flex-direction: column; gap: 4px; }
.rp-filter-group label {
    font-size: 10px;
    font-weight: 700;
    color: var(--rp-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.rp-filter-group select,
.rp-filter-group input {
    background: var(--rp-surface2);
    border: 1px solid var(--rp-border2);
    border-radius: var(--rp-radius-sm);
    color: var(--rp-text);
    font-size: 13px;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    padding: 7px 10px;
    outline: none;
    transition: border-color 0.2s;
    min-width: 150px;
}
.rp-filter-group select:focus, .rp-filter-group input:focus {
    border-color: var(--rp-green);
}
.rp-filter-actions { display: flex; gap: 8px; align-items: flex-end; }
.rp-btn {
    padding: 8px 16px;
    border-radius: var(--rp-radius-sm);
    font-size: 13px;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.rp-btn-primary {
    background: var(--rp-green);
    color: #0d1117;
}
.rp-btn-primary:hover { background: #00f0a0; color: #000; }
.rp-btn-ghost {
    background: var(--rp-surface2);
    color: var(--rp-text-muted);
    border: 1px solid var(--rp-border2);
}
.rp-btn-ghost:hover { color: var(--rp-text); border-color: var(--rp-border2); }
.rp-btn-sm { padding: 6px 12px; font-size: 12px; }

/* ── TABS ───────────────────────────────────────────────────── */
.rp-tabs {
    display: flex;
    gap: 0;
    padding: 0 32px;
    background: var(--rp-surface);
    border-bottom: 1px solid var(--rp-border);
    overflow-x: auto;
}
.rp-tab {
    background: none;
    border: none;
    padding: 14px 20px;
    color: var(--rp-text-muted);
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 7px;
}
.rp-tab:hover { color: var(--rp-text); }
.rp-tab.active {
    color: var(--rp-green);
    border-bottom-color: var(--rp-green);
}

/* ── TAB PANES ──────────────────────────────────────────────── */
.rp-pane { display: none; }
.rp-pane.active {
    display: block;
    animation: rpFadeUp 0.35s ease;
}
@keyframes rpFadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── CONTENT AREA ───────────────────────────────────────────── */
.rp-body { padding: 24px 32px; }

/* ── STAT CARDS ROW ─────────────────────────────────────────── */
.rp-stat-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
@media (max-width: 1400px) { .rp-stat-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .rp-stat-grid { grid-template-columns: repeat(2, 1fr); } }

.rp-stat {
    background: var(--rp-surface);
    border: 1px solid var(--rp-border);
    border-radius: var(--rp-radius);
    padding: 18px;
    position: relative;
    overflow: hidden;
    transition: border-color 0.2s, transform 0.2s;
    cursor: default;
}
.rp-stat:hover { border-color: var(--rp-border2); transform: translateY(-2px); }
.rp-stat-accent {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 3px;
    border-radius: var(--rp-radius) var(--rp-radius) 0 0;
}
.rp-stat-icon {
    width: 36px; height: 36px;
    border-radius: var(--rp-radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    margin-bottom: 14px;
}
.rp-stat-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--rp-text-muted);
    margin-bottom: 6px;
}
.rp-stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 5px;
    line-height: 1.1;
}
.rp-stat-sub {
    font-size: 11px;
    color: var(--rp-text-dim);
    font-weight: 500;
    line-height: 1.4;
}
.rp-stat-progress {
    height: 4px;
    background: rgba(255,255,255,0.06);
    border-radius: 2px;
    margin-top: 12px;
    overflow: hidden;
}
.rp-stat-bar {
    height: 100%;
    border-radius: 2px;
    transition: width 1s cubic-bezier(0.34,1.56,0.64,1);
}
.rp-stat-pct {
    font-size: 11px;
    font-weight: 700;
    margin-top: 5px;
}

/* ── CARD GRID ──────────────────────────────────────────────── */
.rp-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 24px;
}
.rp-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 24px;
}
.rp-grid-65 {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 14px;
    margin-bottom: 24px;
}
.rp-grid-35 {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 14px;
    margin-bottom: 24px;
}
@media (max-width: 1024px) {
    .rp-grid-2, .rp-grid-3, .rp-grid-65, .rp-grid-35 {
        grid-template-columns: 1fr;
    }
}

.rp-card {
    background: var(--rp-surface);
    border: 1px solid var(--rp-border);
    border-radius: var(--rp-radius);
    overflow: hidden;
    transition: border-color 0.2s;
}
.rp-card:hover { border-color: var(--rp-border2); }
.rp-card-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--rp-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.rp-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--rp-text);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.rp-card-title i { font-size: 16px; }
.rp-card-body { padding: 20px; }
.rp-card-body.pad-sm { padding: 14px; }
.rp-card-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 30px;
    background: var(--rp-green-dim);
    color: var(--rp-green);
}

/* ── TABLE ──────────────────────────────────────────────────── */
.rp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.rp-table th {
    text-align: left;
    padding: 10px 14px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--rp-text-muted);
    border-bottom: 1px solid var(--rp-border);
    background: var(--rp-surface2);
}
.rp-table td {
    padding: 12px 14px;
    color: var(--rp-text);
    border-bottom: 1px solid var(--rp-border);
    font-weight: 500;
}
.rp-table tr:last-child td { border-bottom: none; }
.rp-table tr:hover td { background: var(--rp-surface2); }

/* ── BADGES ─────────────────────────────────────────────────── */
.rp-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 700;
}
.rp-badge-green  { background: var(--rp-green-dim);  color: var(--rp-green); }
.rp-badge-amber  { background: var(--rp-amber-dim);  color: var(--rp-amber); }
.rp-badge-blue   { background: var(--rp-blue-dim);   color: var(--rp-blue); }
.rp-badge-violet { background: var(--rp-violet-dim); color: var(--rp-violet); }
.rp-badge-red    { background: var(--rp-red-dim);    color: var(--rp-red); }

/* ── ACTIVITY FEED ──────────────────────────────────────────── */
.rp-feed { max-height: 480px; overflow-y: auto; padding: 0 4px 0 0; }
.rp-feed::-webkit-scrollbar { width: 4px; }
.rp-feed::-webkit-scrollbar-track { background: transparent; }
.rp-feed::-webkit-scrollbar-thumb { background: var(--rp-border2); border-radius: 2px; }

.rp-feed-item {
    display: flex;
    gap: 13px;
    padding: 14px 0;
    border-bottom: 1px solid var(--rp-border);
    align-items: flex-start;
}
.rp-feed-item:last-child { border-bottom: none; }
.rp-feed-dot {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    margin-top: 2px;
}
.rp-feed-dot.sale       { background: var(--rp-green-dim);  color: var(--rp-green); }
.rp-feed-dot.collection { background: var(--rp-violet-dim); color: var(--rp-violet); }
.rp-feed-dot.visit      { background: var(--rp-amber-dim);  color: var(--rp-amber); }

.rp-feed-info { flex: 1; min-width: 0; }
.rp-feed-top {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 3px;
}
.rp-feed-name {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rp-feed-amount {
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
}
.rp-feed-meta { font-size: 11px; color: var(--rp-text-muted); }

/* ── LEADERBOARD ─────────────────────────────────────────────── */
.rp-rank-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--rp-border);
    transition: background 0.2s;
}
.rp-rank-row:last-child { border-bottom: none; }
.rp-rank-row:hover { background: var(--rp-surface2); }
.rp-rank-row.current { background: rgba(0,212,138,0.06); }
.rp-rank-num {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    font-weight: 900;
    flex-shrink: 0;
}
.rank-1 { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #000; }
.rank-2 { background: rgba(148,163,184,0.2); color: #94a3b8; }
.rank-3 { background: rgba(180,83,9,0.2); color: #d97706; }
.rank-n { background: var(--rp-surface2); color: var(--rp-text-muted); }

.rp-rank-name { flex: 1; }
.rp-rank-name strong { font-size: 14px; color: #fff; display: block; }
.rp-rank-name span { font-size: 11px; color: var(--rp-text-muted); }
.rp-rank-stats { display: flex; gap: 24px; }
.rp-rank-stat { text-align: right; }
.rp-rank-stat .val { font-size: 14px; font-weight: 800; color: #fff; }
.rp-rank-stat .lbl { font-size: 10px; color: var(--rp-text-muted); text-transform: uppercase; letter-spacing: 0.6px; }

/* ── COMPARE TABLE ───────────────────────────────────────────── */
.rp-cmp-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 1px;
    border-bottom: 1px solid var(--rp-border);
}
.rp-cmp-row:last-child { border-bottom: none; }
.rp-cmp-cell {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--rp-text);
}
.rp-cmp-head {
    background: var(--rp-surface2);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--rp-text-muted);
    padding: 12px 16px;
}
.rp-cmp-cell.primary { color: var(--rp-green); font-weight: 800; }
.rp-cmp-cell.competitor { color: var(--rp-blue); font-weight: 700; }

/* ── EXPORT BAR ─────────────────────────────────────────────── */
.rp-export-bar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding: 10px 0;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.rp-export-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--rp-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-right: 4px;
}

/* ── EMPTY STATE ─────────────────────────────────────────────── */
.rp-empty {
    text-align: center;
    padding: 64px 24px;
    color: var(--rp-text-muted);
}
.rp-empty i { font-size: 48px; margin-bottom: 16px; opacity: 0.4; display: block; }
.rp-empty h3 { font-size: 18px; color: var(--rp-text); margin: 0 0 8px; }
.rp-empty p { font-size: 14px; margin: 0; }

/* ── ALERTS ──────────────────────────────────────────────────── */
.rp-alert {
    margin: 16px 32px;
    padding: 12px 18px;
    border-radius: var(--rp-radius-sm);
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}
.rp-alert-ok  { background: var(--rp-green-dim); color: var(--rp-green); border: 1px solid rgba(0,212,138,0.2); }
.rp-alert-err { background: var(--rp-red-dim);   color: var(--rp-red);   border: 1px solid rgba(248,113,113,0.2); }
</style>

<?php
    // ── PHP HELPERS ──────────────────────────────────────────────────────
    $p = $data['perf_data'] ?? [];
    $hasPerfData = !empty($p);

    // Build merged activity feed
    $activities = [];
    if ($hasPerfData) {
        foreach ($p['recent_sales'] ?? [] as $s) {
            $activities[] = [
                'type'     => 'sale',
                'date'     => $s->invoice_date ?? '',
                'name'     => $s->customer_name ?? '',
                'ref'      => $s->invoice_number ?? '',
                'amount'   => floatval($s->true_amount ?? 0),
                'icon'     => 'ph-receipt',
                'meta'     => $s->status ?? '',
            ];
        }
        foreach ($p['recent_collections'] ?? [] as $c) {
            $activities[] = [
                'type'     => 'collection',
                'date'     => $c->payment_date ?? '',
                'name'     => $c->customer_name ?? '',
                'ref'      => trim(($c->payment_method ?? '') . ' ' . ($c->reference ?? '')),
                'amount'   => floatval($c->amount ?? 0),
                'icon'     => 'ph-hand-coins',
                'meta'     => 'Payment',
            ];
        }
        foreach ($p['recent_unprod'] ?? [] as $v) {
            $activities[] = [
                'type'     => 'visit',
                'date'     => date('Y-m-d', strtotime($v->visit_time ?? 'now')),
                'name'     => $v->customer_name ?? '',
                'ref'      => $v->reason ?? '',
                'amount'   => 0,
                'icon'     => 'ph-x-circle',
                'meta'     => 'Unproductive',
            ];
        }
        usort($activities, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
    }

    // KPI stats config
    $stats = [];
    if ($hasPerfData) {
        $salesTarget = $p['kpi_scores']['sales_amount']['target'] ?? 0;
        $visitTarget = $p['kpi_scores']['productive_visit_rate']['target'] ?? 0;
        $routeTarget = $p['kpi_scores']['route_completion']['target'] ?? 0;

        $stats = [
            [
                'label'   => 'Net Sales',
                'value'   => 'Rs ' . number_format($p['net_sales'], 0),
                'sub'     => $p['invoice_count'] . ' invoices · Rs ' . number_format($p['total_returns'], 0) . ' returned',
                'pct'     => $salesTarget > 0 ? min(100, ($p['net_sales'] / $salesTarget) * 100) : null,
                'target'  => $salesTarget > 0 ? 'Target Rs ' . number_format($salesTarget, 0) : null,
                'color'   => '#00d48a',
                'icon'    => 'ph-chart-line-up',
                'css'     => 'green',
            ],
            [
                'label'   => 'Collections',
                'value'   => 'Rs ' . number_format($p['total_collections'], 0),
                'sub'     => 'Efficiency ' . number_format($p['collection_efficiency'], 1) . '%',
                'pct'     => min(100, $p['collection_efficiency']),
                'target'  => null,
                'color'   => '#a78bfa',
                'icon'    => 'ph-hand-coins',
                'css'     => 'violet',
            ],
            [
                'label'   => 'Productive Visits',
                'value'   => $p['productive_visits'] . ' / ' . $p['total_visited'],
                'sub'     => 'New: ' . $p['new_customers_added'] . ' · Repeat: ' . $p['repeat_customers'],
                'pct'     => $visitTarget > 0 ? min(100, ($p['productive_visits'] / $visitTarget) * 100) : null,
                'target'  => $visitTarget > 0 ? 'Target ' . $visitTarget . ' productive' : null,
                'color'   => '#3b82f6',
                'icon'    => 'ph-users',
                'css'     => 'blue',
            ],
            [
                'label'   => 'Routes',
                'value'   => $p['completed_routes'] . ' / ' . $p['total_routes'],
                'sub'     => 'Completion ' . number_format($p['route_completion_rate'], 1) . '%',
                'pct'     => $routeTarget > 0 ? min(100, ($p['active_route_days'] / $routeTarget) * 100) : min(100, $p['route_completion_rate']),
                'target'  => null,
                'color'   => '#f59e0b',
                'icon'    => 'ph-map-trifold',
                'css'     => 'amber',
            ],
            [
                'label'   => 'Expenses',
                'value'   => 'Rs ' . number_format($p['total_expenses'], 0),
                'sub'     => 'Fuel Rs ' . number_format($p['fuel_expenses'], 0) . ' · Other Rs ' . number_format($p['other_expenses'], 0),
                'pct'     => null,
                'target'  => 'Expense Ratio ' . number_format($p['sales_to_expense_ratio'], 1) . '%',
                'color'   => '#f87171',
                'icon'    => 'ph-money',
                'css'     => 'red',
            ],
            [
                'label'   => 'Outstanding',
                'value'   => 'Rs ' . number_format($p['total_outstanding'] ?? 0, 0),
                'sub'     => 'Credit Limit Rs ' . number_format($p['credit_limit'] ?? 0, 0),
                'pct'     => ($p['credit_limit'] ?? 0) > 0 ? min(100, (($p['total_outstanding'] ?? 0) / $p['credit_limit']) * 100) : null,
                'target'  => null,
                'color'   => '#38bdf8',
                'icon'    => 'ph-warning-circle',
                'css'     => 'blue',
            ],
        ];
    }
?>

<div class="rp-wrap">

    <!-- ALERTS -->
    <?php if (!empty($data['success'])): ?>
        <div class="rp-alert rp-alert-ok"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($data['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($data['error'])): ?>
        <div class="rp-alert rp-alert-err"><i class="ph ph-warning"></i> <?= htmlspecialchars($data['error']) ?></div>
    <?php endif; ?>

    <!-- HERO BANNER -->
    <div class="rp-hero">
        <div class="rp-hero-top">
            <div class="rp-hero-title">
                <div class="rp-hero-icon"><i class="ph ph-presentation-chart"></i></div>
                <div>
                    <h1>Rep Performance</h1>
                    <p><?= $data['month_name'] ?? date('F', mktime(0,0,0,intval($data['month']),1)) ?> <?= $data['year'] ?> · <?= date('l, d M Y') ?></p>
                </div>
            </div>
            <?php if ($hasPerfData):
                $score = $p['overall_score'];
                $pillCls = $score >= 80 ? 'good' : ($score >= 50 ? 'warn' : 'danger');
                $pillIcon = $score >= 80 ? 'ph-star-fill' : ($score >= 50 ? 'ph-star-half' : 'ph-star');
            ?>
                <div class="rp-score-pill <?= $pillCls ?>">
                    <i class="ph <?= $pillIcon ?>"></i>
                    Overall Score: <?= number_format($score, 1) ?>%
                </div>
            <?php endif; ?>
        </div>

        <!-- FILTER BAR -->
        <form method="GET" action="<?= APP_URL ?>/repperformance">
        <div class="rp-filter-bar" style="padding: 0 0 20px;">
            <div class="rp-filter-group">
                <label for="rep_user_id">Representative</label>
                <select name="rep_user_id" id="rep_user_id" onchange="this.form.submit()">
                    <?php foreach ($data['reps'] as $r): ?>
                        <option value="<?= $r->id ?>" <?= $data['selected_rep_id'] == $r->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r->username) ?> — <?= htmlspecialchars($r->first_name . ' ' . $r->last_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rp-filter-group">
                <label for="month">Month</label>
                <select name="month" id="month">
                    <?php for ($m = 1; $m <= 12; $m++):
                        $mVal = str_pad((string)$m, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $mVal ?>" <?= $data['month'] === $mVal ? 'selected' : '' ?>>
                            <?= date('F', mktime(0,0,0,$m,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="rp-filter-group">
                <label for="year">Year</label>
                <select name="year" id="year">
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= $data['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="rp-filter-group">
                <label for="route_id">Route</label>
                <select name="route_id" id="route_id" onchange="this.form.submit()">
                    <option value="">All Routes</option>
                    <?php foreach ($data['routes'] as $rt): ?>
                        <option value="<?= $rt->id ?>" <?= $data['selected_route_id'] == $rt->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rt->route_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rp-filter-group">
                <label for="area_id">Territory</label>
                <select name="area_id" id="area_id" onchange="this.form.submit()">
                    <option value="">All Areas</option>
                    <?php foreach ($data['areas'] as $ar): ?>
                        <option value="<?= $ar->id ?>" <?= $data['selected_area_id'] == $ar->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ar->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rp-filter-actions">
                <button type="submit" class="rp-btn rp-btn-primary"><i class="ph ph-funnel-simple"></i> Apply</button>
                <a href="<?= APP_URL ?>/repperformance" class="rp-btn rp-btn-ghost"><i class="ph ph-arrow-counter-clockwise"></i></a>
            </div>
        </div>
        </form>
    </div>

    <!-- TABS NAV -->
    <div class="rp-tabs">
        <button class="rp-tab active" onclick="rpTab('rpPane1',this)">
            <i class="ph ph-gauge"></i> Overview
        </button>
        <button class="rp-tab" onclick="rpTab('rpPane2',this)">
            <i class="ph ph-chart-bar"></i> Analytics
        </button>
        <button class="rp-tab" onclick="rpTab('rpPane3',this)">
            <i class="ph ph-git-diff"></i> Comparison
        </button>
        <button class="rp-tab" onclick="rpTab('rpPane4',this)">
            <i class="ph ph-trophy"></i> Leaderboard
        </button>
    </div>

    <!-- ╔════════════════════════════════╗
         ║   PANE 1 — OVERVIEW            ║
         ╚════════════════════════════════╝ -->
    <div id="rpPane1" class="rp-pane active">
    <?php if (!$hasPerfData): ?>
        <div class="rp-body">
            <div class="rp-card">
                <div class="rp-empty">
                    <i class="ph ph-chart-polar"></i>
                    <h3>No Performance Data</h3>
                    <p>Select a representative and date range above to load their analytics.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="rp-body">

            <!-- STAT ROW -->
            <div class="rp-stat-grid">
                <?php foreach ($stats as $st): ?>
                    <div class="rp-stat">
                        <div class="rp-stat-accent" style="background: linear-gradient(90deg, <?= $st['color'] ?>, <?= $st['color'] ?>66);"></div>
                        <div class="rp-stat-icon" style="background: <?= $st['color'] ?>18; color: <?= $st['color'] ?>;">
                            <i class="ph <?= $st['icon'] ?>"></i>
                        </div>
                        <div class="rp-stat-label"><?= $st['label'] ?></div>
                        <div class="rp-stat-value"><?= $st['value'] ?></div>
                        <div class="rp-stat-sub"><?= $st['sub'] ?></div>
                        <?php if ($st['pct'] !== null): ?>
                            <div class="rp-stat-progress">
                                <div class="rp-stat-bar" style="width:<?= $st['pct'] ?>%; background: <?= $st['color'] ?>;"></div>
                            </div>
                            <div class="rp-stat-pct" style="color:<?= $st['color'] ?>;">
                                <?= $st['target'] ?? number_format($st['pct'], 1) . '% of target' ?>
                            </div>
                        <?php elseif ($st['target']): ?>
                            <div class="rp-stat-pct" style="color: var(--rp-text-muted);"><?= $st['target'] ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- EXPORT BAR -->
            <div class="rp-export-bar">
                <span class="rp-export-label">Export:</span>
                <a href="<?= APP_URL ?>/repperformance/export/kpi?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="rp-btn rp-btn-ghost rp-btn-sm"><i class="ph ph-file-csv"></i> KPI</a>
                <a href="<?= APP_URL ?>/repperformance/export/sales?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="rp-btn rp-btn-ghost rp-btn-sm"><i class="ph ph-file-csv"></i> Sales</a>
                <a href="<?= APP_URL ?>/repperformance/export/route?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="rp-btn rp-btn-ghost rp-btn-sm"><i class="ph ph-file-csv"></i> Routes</a>
                <a href="<?= APP_URL ?>/repperformance/export/collection?rep_user_id=<?= $data['selected_rep_id'] ?>&start_date=<?= $data['start_date'] ?>&end_date=<?= $data['end_date'] ?>&route_id=<?= $data['selected_route_id'] ?>&area_id=<?= $data['selected_area_id'] ?>" class="rp-btn rp-btn-ghost rp-btn-sm"><i class="ph ph-file-csv"></i> Collections</a>
            </div>

            <!-- ROW: TREND + RADAR -->
            <div class="rp-grid-65">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h4 class="rp-card-title"><i class="ph ph-trend-up" style="color:var(--rp-green)"></i> Daily Sales vs Collections</h4>
                        <span class="rp-card-badge">This Period</span>
                    </div>
                    <div class="rp-card-body">
                        <div style="height:300px; position:relative;">
                            <canvas id="rpTrendChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h4 class="rp-card-title"><i class="ph ph-radar" style="color:var(--rp-violet)"></i> KPI Radar</h4>
                        <span class="rp-card-badge" style="background:var(--rp-violet-dim);color:var(--rp-violet);">vs Target</span>
                    </div>
                    <div class="rp-card-body">
                        <div style="height:280px; position:relative;">
                            <canvas id="rpRadarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW: ACTIVITY FEED + PAYMENT PIE + TOP CUSTOMERS -->
            <div class="rp-grid-3">
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h4 class="rp-card-title"><i class="ph ph-lightning" style="color:var(--rp-amber)"></i> Activity Feed</h4>
                        <span class="rp-card-badge" style="background:var(--rp-amber-dim);color:var(--rp-amber);"><?= count($activities) ?> events</span>
                    </div>
                    <div class="rp-card-body pad-sm">
                        <div class="rp-feed">
                            <?php if (empty($activities)): ?>
                                <div style="text-align:center;padding:30px;color:var(--rp-text-muted);">No recent activity</div>
                            <?php else: $cnt = 0; foreach ($activities as $act): if ($cnt++ >= 15) break;
                                $amtStr = $act['amount'] > 0 ? 'Rs ' . number_format($act['amount'], 0) : '';
                                $amtColor = $act['type'] === 'sale' ? 'var(--rp-green)' : ($act['type'] === 'collection' ? 'var(--rp-violet)' : 'var(--rp-text-muted)');
                            ?>
                                <div class="rp-feed-item">
                                    <div class="rp-feed-dot <?= $act['type'] ?>">
                                        <i class="ph <?= $act['icon'] ?>"></i>
                                    </div>
                                    <div class="rp-feed-info">
                                        <div class="rp-feed-top">
                                            <span class="rp-feed-name"><?= htmlspecialchars($act['name']) ?></span>
                                            <?php if ($amtStr): ?>
                                                <span class="rp-feed-amount" style="color:<?= $amtColor ?>"><?= $amtStr ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="rp-feed-meta">
                                            <?= htmlspecialchars($act['ref']) ?> · <?= htmlspecialchars($act['date']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h4 class="rp-card-title"><i class="ph ph-coins" style="color:var(--rp-violet)"></i> Payment Channels</h4>
                    </div>
                    <div class="rp-card-body">
                        <div style="height:200px;position:relative;margin-bottom:16px;">
                            <canvas id="rpPayPieChart"></canvas>
                        </div>
                        <table class="rp-table">
                            <tbody>
                                <tr>
                                    <td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#00d48a;margin-right:8px;"></span>Cash</td>
                                    <td style="text-align:right;font-weight:700;color:var(--rp-green);">Rs <?= number_format($p['cash_collections'], 0) ?></td>
                                </tr>
                                <tr>
                                    <td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f59e0b;margin-right:8px;"></span>Cheque</td>
                                    <td style="text-align:right;font-weight:700;color:var(--rp-amber);">Rs <?= number_format($p['cheque_collections'], 0) ?></td>
                                </tr>
                                <tr>
                                    <td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#a78bfa;margin-right:8px;"></span>Bank Transfer</td>
                                    <td style="text-align:right;font-weight:700;color:var(--rp-violet);">Rs <?= number_format($p['bank_collections'], 0) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rp-card">
                    <div class="rp-card-head">
                        <h4 class="rp-card-title"><i class="ph ph-crown" style="color:var(--rp-amber)"></i> Top Customers</h4>
                    </div>
                    <div class="rp-card-body pad-sm">
                        <table class="rp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th style="text-align:right">Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($p['top_customers'])): ?>
                                    <tr><td colspan="3" style="text-align:center;color:var(--rp-text-muted);">No data</td></tr>
                                <?php else: $n = 1; foreach ($p['top_customers'] as $tc): ?>
                                    <tr>
                                        <td style="font-weight:800;color:var(--rp-text-muted);"><?= $n++ ?></td>
                                        <td><strong style="color:#fff;"><?= htmlspecialchars($tc->customer_name) ?></strong></td>
                                        <td style="text-align:right;font-weight:700;color:var(--rp-green);">Rs <?= number_format($tc->total_sales, 0) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ROUTE TABLE -->
            <div class="rp-card">
                <div class="rp-card-head">
                    <h4 class="rp-card-title"><i class="ph ph-map-trifold" style="color:var(--rp-blue)"></i> Route Execution History</h4>
                    <span class="rp-card-badge" style="background:var(--rp-blue-dim);color:var(--rp-blue);"><?= count($p['routes_detail'] ?? []) ?> routes</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="rp-table">
                        <thead>
                            <tr>
                                <th>Route Name</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Start Meter</th>
                                <th>End Meter</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($p['routes_detail'])): ?>
                                <tr><td colspan="7" style="text-align:center;color:var(--rp-text-muted);padding:30px;">No route executions in this period.</td></tr>
                            <?php else: foreach ($p['routes_detail'] as $rt): ?>
                                <tr>
                                    <td><strong style="color:#fff;"><?= htmlspecialchars($rt->route_name) ?></strong></td>
                                    <td><?= htmlspecialchars($rt->start_time) ?></td>
                                    <td><?= htmlspecialchars($rt->end_time ?? '—') ?></td>
                                    <td><?= number_format(floatval($rt->start_meter)) ?> km</td>
                                    <td><?= $rt->end_meter ? number_format(floatval($rt->end_meter)) . ' km' : '—' ?></td>
                                    <td><span class="rp-badge rp-badge-blue"><?= htmlspecialchars($rt->vehicle_number ?? 'N/A') ?></span></td>
                                    <td>
                                        <?php
                                            $status = $rt->status ?? 'Unknown';
                                            $sc = in_array($status, ['Finalized','Completed']) ? 'green' : 'amber';
                                        ?>
                                        <span class="rp-badge rp-badge-<?= $sc ?>"><?= htmlspecialchars($status) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>

    <!-- ╔════════════════════════════════╗
         ║   PANE 2 — ANALYTICS           ║
         ╚════════════════════════════════╝ -->
    <div id="rpPane2" class="rp-pane">
    <?php if ($hasPerfData): ?>
        <div class="rp-body">
            <!-- 6-month trend -->
            <?php if (!empty($data['monthly_trend'])): ?>
            <div class="rp-card" style="margin-bottom:24px;">
                <div class="rp-card-head">
                    <h4 class="rp-card-title"><i class="ph ph-calendar-blank" style="color:var(--rp-blue)"></i> 6-Month Historical Trend</h4>
                    <span class="rp-card-badge" style="background:var(--rp-blue-dim);color:var(--rp-blue);">Sales · Collections · Score</span>
                </div>
                <div class="rp-card-body">
                    <div style="height:320px;position:relative;">
                        <canvas id="rpMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- KPI breakdown + product mix -->
            <div class="rp-grid-2">
                <!-- KPI table -->
                <div class="rp-card">
                    <div class="rp-card-head">
                        <h4 class="rp-card-title"><i class="ph ph-star" style="color:var(--rp-amber)"></i> KPI Breakdown</h4>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="rp-table">
                            <thead>
                                <tr>
                                    <th>Dimension</th>
                                    <th style="text-align:right">Target</th>
                                    <th style="text-align:right">Actual</th>
                                    <th style="text-align:right">Achievement</th>
                                    <th style="text-align:right">Weight</th>
                                    <th style="text-align:right">Contribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($p['kpi_scores'] as $sc): ?>
                                    <tr>
                                        <td><strong style="color:#fff;"><?= htmlspecialchars($sc['name']) ?></strong></td>
                                        <td style="text-align:right;color:var(--rp-text-muted);"><?= number_format($sc['target'], 1) ?></td>
                                        <td style="text-align:right;font-weight:700;color:#fff;"><?= number_format($sc['actual'], 1) ?></td>
                                        <td style="text-align:right;">
                                            <span class="rp-badge rp-badge-<?= $sc['achievement_pct'] >= 100 ? 'green' : ($sc['achievement_pct'] >= 70 ? 'amber' : 'red') ?>">
                                                <?= number_format($sc['achievement_pct'], 1) ?>%
                                            </span>
                                        </td>
                                        <td style="text-align:right;color:var(--rp-text-muted);"><?= number_format($sc['weight'], 1) ?>%</td>
                                        <td style="text-align:right;font-weight:700;color:var(--rp-green);"><?= number_format($sc['contribution'], 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Product mix doughnut + Top Products -->
                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div class="rp-card">
                        <div class="rp-card-head">
                            <h4 class="rp-card-title"><i class="ph ph-pie-chart" style="color:var(--rp-blue)"></i> Product Category Mix</h4>
                        </div>
                        <div class="rp-card-body">
                            <div style="height:220px;position:relative;">
                                <canvas id="rpCatChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="rp-card">
                        <div class="rp-card-head">
                            <h4 class="rp-card-title"><i class="ph ph-shopping-bag" style="color:var(--rp-green)"></i> Top Products</h4>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="rp-table">
                                <thead><tr><th>#</th><th>Product</th><th style="text-align:right">Qty</th><th style="text-align:right">Revenue</th></tr></thead>
                                <tbody>
                                    <?php if (empty($p['top_products'])): ?>
                                        <tr><td colspan="4" style="text-align:center;color:var(--rp-text-muted);">No data</td></tr>
                                    <?php else: $n=1; foreach ($p['top_products'] as $pr): ?>
                                        <tr>
                                            <td style="font-weight:800;color:var(--rp-text-muted);"><?= $n++ ?></td>
                                            <td><strong style="color:#fff;"><?= htmlspecialchars($pr->product_name) ?></strong></td>
                                            <td style="text-align:right;font-weight:700;color:var(--rp-blue);"><?= number_format($pr->qty) ?></td>
                                            <td style="text-align:right;font-weight:700;color:var(--rp-green);">Rs <?= number_format($pr->total_sales, 0) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="rp-body">
            <div class="rp-card"><div class="rp-empty"><i class="ph ph-chart-bar"></i><h3>No Data</h3><p>Select a representative above.</p></div></div>
        </div>
    <?php endif; ?>
    </div>

    <!-- ╔════════════════════════════════╗
         ║   PANE 3 — COMPARISON          ║
         ╚════════════════════════════════╝ -->
    <div id="rpPane3" class="rp-pane">
        <div class="rp-body">
            <div class="rp-card" style="margin-bottom:24px;">
                <div class="rp-card-head">
                    <h4 class="rp-card-title"><i class="ph ph-git-diff" style="color:var(--rp-blue)"></i> Side-by-Side Comparison</h4>
                    <form method="GET" action="<?= APP_URL ?>/repperformance" style="display:flex;gap:10px;align-items:center;">
                        <input type="hidden" name="rep_user_id" value="<?= $data['selected_rep_id'] ?>">
                        <input type="hidden" name="month" value="<?= $data['month'] ?>">
                        <input type="hidden" name="year" value="<?= $data['year'] ?>">
                        <div class="rp-filter-group">
                            <label for="compare_user_id">Compare vs</label>
                            <select name="compare_user_id" id="compare_user_id" onchange="this.form.submit()">
                                <option value="">Select competitor…</option>
                                <?php foreach ($data['reps'] as $r): if ($r->id != $data['selected_rep_id']): ?>
                                    <option value="<?= $r->id ?>" <?= ($data['compare_rep_id'] ?? 0) == $r->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r->username) ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                        <div class="rp-filter-actions" style="margin-top:16px;">
                            <button type="submit" class="rp-btn rp-btn-primary"><i class="ph ph-arrows-left-right"></i> Compare</button>
                        </div>
                    </form>
                </div>

                <?php if (!empty($data['compare_data'])): ?>
                <div style="padding:20px;">
                    <div style="height:320px;position:relative;margin-bottom:24px;">
                        <canvas id="rpCompareChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($hasPerfData):
                    $pC = $data['perf_data'];
                    $cC = $data['compare_data'] ?? null;
                    $tC = $data['team_avg'];
                    $rows = [
                        ['label' => 'Overall KPI Score', 'p' => number_format($pC['overall_score'],1).'%', 'c' => $cC ? number_format($cC['overall_score'],1).'%' : '—', 't' => number_format($tC['overall_score'],1).'%'],
                        ['label' => 'Net Sales (Rs)', 'p' => 'Rs '.number_format($pC['net_sales'],0), 'c' => $cC ? 'Rs '.number_format($cC['net_sales'],0) : '—', 't' => 'Rs '.number_format($tC['net_sales'],0)],
                        ['label' => 'Total Collections', 'p' => 'Rs '.number_format($pC['total_collections'],0), 'c' => $cC ? 'Rs '.number_format($cC['total_collections'],0) : '—', 't' => 'Rs '.number_format($tC['total_collections'],0)],
                        ['label' => 'Productive Visits', 'p' => $pC['productive_visits'], 'c' => $cC ? $cC['productive_visits'] : '—', 't' => number_format($tC['productive_visits'],1)],
                        ['label' => 'New Customers', 'p' => $pC['new_customers_added'], 'c' => $cC ? $cC['new_customers_added'] : '—', 't' => number_format($tC['new_customers_added'],1)],
                        ['label' => 'Route Completion', 'p' => number_format($pC['route_completion_rate'],1).'%', 'c' => $cC ? number_format($cC['route_completion_rate'],1).'%' : '—', 't' => '—'],
                        ['label' => 'Total Expenses', 'p' => 'Rs '.number_format($pC['total_expenses'],0), 'c' => $cC ? 'Rs '.number_format($cC['total_expenses'],0) : '—', 't' => '—'],
                    ];
                ?>
                <div style="border-top:1px solid var(--rp-border);">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1px;background:var(--rp-border);">
                        <div class="rp-cmp-head">Dimension</div>
                        <div class="rp-cmp-head" style="color:var(--rp-green);">Selected Rep</div>
                        <div class="rp-cmp-head" style="color:var(--rp-blue);">Competitor</div>
                        <div class="rp-cmp-head">Team Avg</div>
                        <?php foreach ($rows as $row): ?>
                            <div class="rp-cmp-cell" style="background:var(--rp-surface);border-bottom:1px solid var(--rp-border);"><strong style="color:#fff;"><?= $row['label'] ?></strong></div>
                            <div class="rp-cmp-cell primary" style="background:var(--rp-surface);border-bottom:1px solid var(--rp-border);"><?= $row['p'] ?></div>
                            <div class="rp-cmp-cell competitor" style="background:var(--rp-surface);border-bottom:1px solid var(--rp-border);"><?= $row['c'] ?></div>
                            <div class="rp-cmp-cell" style="background:var(--rp-surface);border-bottom:1px solid var(--rp-border);color:var(--rp-text-muted);"><?= $row['t'] ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ╔════════════════════════════════╗
         ║   PANE 4 — LEADERBOARD         ║
         ╚════════════════════════════════╝ -->
    <div id="rpPane4" class="rp-pane">
        <div class="rp-body">
            <div class="rp-card">
                <div class="rp-card-head">
                    <h4 class="rp-card-title"><i class="ph ph-trophy" style="color:var(--rp-amber)"></i> Team Leaderboard</h4>
                    <span class="rp-card-badge" style="background:var(--rp-amber-dim);color:var(--rp-amber);"><?= count($data['rankings']) ?> reps</span>
                </div>
                <?php $rank = 1; foreach ($data['rankings'] as $rnk):
                    $isMe = $rnk['id'] == $data['selected_rep_id'];
                    $cls  = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-n'));
                    $icon = $rank === 1 ? '👑' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank));
                ?>
                    <div class="rp-rank-row <?= $isMe ? 'current' : '' ?>">
                        <div class="rp-rank-num <?= $cls ?>"><?= $icon ?></div>
                        <div class="rp-rank-name">
                            <strong><?= htmlspecialchars($rnk['username']) ?></strong>
                            <span><?= htmlspecialchars($rnk['first_name'] . ' ' . $rnk['last_name']) ?></span>
                        </div>
                        <div class="rp-rank-stats">
                            <div class="rp-rank-stat">
                                <div class="val" style="color:var(--rp-green);">Rs <?= number_format($rnk['net_sales'], 0) ?></div>
                                <div class="lbl">Net Sales</div>
                            </div>
                            <div class="rp-rank-stat">
                                <div class="val" style="color:var(--rp-violet);">Rs <?= number_format($rnk['total_collections'], 0) ?></div>
                                <div class="lbl">Collections</div>
                            </div>
                            <div class="rp-rank-stat">
                                <?php $sc = $rnk['score']; $c = $sc >= 80 ? 'var(--rp-green)' : ($sc >= 50 ? 'var(--rp-amber)' : 'var(--rp-red)'); ?>
                                <div class="val" style="color:<?= $c ?>;"><?= number_format($sc, 1) ?>%</div>
                                <div class="lbl">Score</div>
                            </div>
                        </div>
                        <?php if ($isMe): ?>
                            <span class="rp-badge rp-badge-green">You</span>
                        <?php endif; ?>
                    </div>
                <?php $rank++; endforeach; ?>
            </div>
        </div>
    </div>

</div><!-- .rp-wrap -->

<!-- ── JAVASCRIPT ─────────────────────────────────────────────── -->
<script>
// ── TAB SWITCHER ──────────────────────────────────────────────
function rpTab(id, btn) {
    document.querySelectorAll('.rp-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.rp-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}

// ── CHART DEFAULTS ────────────────────────────────────────────
Chart.defaults.color = '#8b98b0';
Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(22,27,34,0.95)';
Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.08)';
Chart.defaults.plugins.tooltip.borderWidth = 1;
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.titleFont = { size: 13, weight: '700' };
Chart.defaults.plugins.tooltip.bodyFont = { size: 12, weight: '600' };

<?php if ($hasPerfData): $p = $data['perf_data']; ?>

// 1. TREND CHART
(function() {
    const dates       = <?= json_encode(array_column($p['sales_trend'], 'label')) ?>;
    const salesData   = <?= json_encode(array_map('floatval', array_column($p['sales_trend'], 'sales_amount'))) ?>;
    const colMap      = <?= json_encode(array_reduce($p['collections_trend'], fn($c, $i) => array_merge($c, [$i->label => floatval($i->col_amount)]), [])) ?>;
    const colData     = dates.map(d => colMap[d] || 0);

    new Chart(document.getElementById('rpTrendChart'), {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Sales',
                    data: salesData,
                    backgroundColor: 'rgba(0,212,138,0.55)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Collections',
                    data: colData,
                    type: 'line',
                    borderColor: '#a78bfa',
                    backgroundColor: 'rgba(167,139,250,0.1)',
                    borderWidth: 2.5,
                    tension: 0.45,
                    pointRadius: 4,
                    pointBackgroundColor: '#161b22',
                    pointBorderColor: '#a78bfa',
                    pointBorderWidth: 2,
                    fill: true,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { font: { size: 11 } } },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { callback: v => 'Rs ' + (v >= 1000 ? Math.round(v/1000)+'k' : v), font: { size: 11 } }
                }
            }
        }
    });
})();

// 2. RADAR CHART
(function() {
    const labels = <?= json_encode(array_values(array_map(fn($sc) => $sc['name'], $p['kpi_scores']))) ?>;
    const pcts   = <?= json_encode(array_values(array_map(fn($sc) => min(100, round($sc['achievement_pct'], 1)), $p['kpi_scores']))) ?>;

    new Chart(document.getElementById('rpRadarChart'), {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Achievement %',
                data: pcts,
                backgroundColor: 'rgba(0,212,138,0.12)',
                borderColor: '#00d48a',
                pointBackgroundColor: '#00d48a',
                pointBorderColor: '#161b22',
                pointBorderWidth: 2,
                pointRadius: 4,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    min: 0, max: 100, stepSize: 25,
                    grid: { color: 'rgba(255,255,255,0.06)' },
                    angleLines: { color: 'rgba(255,255,255,0.06)' },
                    pointLabels: { font: { size: 11, weight: '600' }, color: '#8b98b0' },
                    ticks: { display: false }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
})();

// 3. PAYMENT PIE CHART
(function() {
    new Chart(document.getElementById('rpPayPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Cheque', 'Bank Transfer'],
            datasets: [{
                data: [<?= floatval($p['cash_collections']) ?>, <?= floatval($p['cheque_collections']) ?>, <?= floatval($p['bank_collections']) ?>],
                backgroundColor: ['#00d48a', '#f59e0b', '#a78bfa'],
                borderColor: '#161b22',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
})();

<?php endif; ?>

// 4. MONTHLY TREND
<?php if (!empty($data['monthly_trend'])): ?>
(function() {
    const labels  = <?= json_encode(array_column($data['monthly_trend'], 'label')) ?>;
    const sales   = <?= json_encode(array_map('floatval', array_column($data['monthly_trend'], 'net_sales'))) ?>;
    const cols    = <?= json_encode(array_map('floatval', array_column($data['monthly_trend'], 'total_collections'))) ?>;
    const scores  = <?= json_encode(array_map('floatval', array_column($data['monthly_trend'], 'overall_score'))) ?>;

    const el = document.getElementById('rpMonthlyChart');
    if (el) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Net Sales', data: sales, backgroundColor: 'rgba(59,130,246,0.65)', borderRadius: 4, yAxisID: 'y' },
                    { label: 'Collections', data: cols, backgroundColor: 'rgba(0,212,138,0.65)', borderRadius: 4, yAxisID: 'y' },
                    { label: 'KPI Score %', data: scores, type: 'line', borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.08)',
                      borderWidth: 2.5, tension: 0.4, pointRadius: 5, pointBackgroundColor: '#161b22',
                      pointBorderColor: '#f59e0b', pointBorderWidth: 2, fill: true, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.04)' } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { callback: v => 'Rs '+(v>=1000?Math.round(v/1000)+'k':v) } },
                    y1: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false },
                          ticks: { callback: v => v+'%', color: '#f59e0b', font: { weight: '700' } } }
                }
            }
        });
    }
})();
<?php endif; ?>

// 5. CATEGORY DOUGHNUT
<?php if ($hasPerfData && !empty($p['top_categories'])): ?>
(function() {
    const el = document.getElementById('rpCatChart');
    if (!el) return;
    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($p['top_categories'], 'category_name')) ?>,
            datasets: [{
                data: <?= json_encode(array_map('floatval', array_column($p['top_categories'], 'total_sales'))) ?>,
                backgroundColor: ['#00d48a','#3b82f6','#f59e0b','#a78bfa','#f87171'],
                borderColor: '#161b22',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11, weight: '600' }, color: '#8b98b0' } }
            }
        }
    });
})();
<?php endif; ?>

// 6. COMPARE CHART
<?php if (!empty($data['compare_data'])): $pR = $data['perf_data']; $cR = $data['compare_data']; $tR = $data['team_avg'];
    $selName = '';
    $cmpName = '';
    foreach ($data['reps'] as $r) {
        if ($r->id == $data['selected_rep_id']) $selName = $r->username;
        if ($r->id == ($data['compare_rep_id'] ?? 0)) $cmpName = $r->username;
    }
?>
(function() {
    const el = document.getElementById('rpCompareChart');
    if (!el) return;
    new Chart(el, {
        type: 'bar',
        data: {
            labels: ['KPI Score (%)', 'Net Sales (10k Rs)', 'Collections (10k Rs)', 'Productive Visits'],
            datasets: [
                {
                    label: <?= json_encode($selName ?: 'Selected') ?>,
                    data: [<?= round($pR['overall_score'],1) ?>, <?= round($pR['net_sales']/10000,1) ?>, <?= round($pR['total_collections']/10000,1) ?>, <?= $pR['productive_visits'] ?>],
                    backgroundColor: 'rgba(0,212,138,0.75)',
                    borderRadius: 4
                },
                {
                    label: <?= json_encode($cmpName ?: 'Competitor') ?>,
                    data: [<?= round($cR['overall_score'],1) ?>, <?= round($cR['net_sales']/10000,1) ?>, <?= round($cR['total_collections']/10000,1) ?>, <?= $cR['productive_visits'] ?>],
                    backgroundColor: 'rgba(59,130,246,0.75)',
                    borderRadius: 4
                },
                {
                    label: 'Team Avg',
                    data: [<?= round($tR['overall_score'],1) ?>, <?= round($tR['net_sales']/10000,1) ?>, <?= round($tR['total_collections']/10000,1) ?>, <?= round($tR['productive_visits'],1) ?>],
                    backgroundColor: 'rgba(139,152,176,0.5)',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.04)' } },
                y: { grid: { color: 'rgba(255,255,255,0.04)' } }
            }
        }
    });
})();
<?php endif; ?>
</script>
PHPEOF;

file_put_contents(__DIR__ . '/../app/Views/rep_performance/index.php', $html);
echo "OK";
