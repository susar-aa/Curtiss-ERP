<?php
/* ── DATA PREP ──────────────────────────────────────────────── */
$p          = $data['perf_data'] ?? [];
$hasPerfData = !empty($p);

// Build activity feed (merge sales, collections, unproductive visits)
$activities = [];
if ($hasPerfData) {
    foreach ($p['recent_sales']       ?? [] as $s) { $activities[] = ['type'=>'sale',       'date'=>$s->invoice_date??'', 'name'=>$s->customer_name??'', 'ref'=>$s->invoice_number??'', 'amount'=>floatval($s->true_amount??0), 'status'=>$s->status??'']; }
    foreach ($p['recent_collections'] ?? [] as $c) { $activities[] = ['type'=>'collection', 'date'=>$c->payment_date??'',  'name'=>$c->customer_name??'', 'ref'=>trim(($c->payment_method??'').' '.($c->reference??'')), 'amount'=>floatval($c->amount??0), 'status'=>'']; }
    foreach ($p['recent_unprod']      ?? [] as $v) { $activities[] = ['type'=>'visit',       'date'=>date('Y-m-d',strtotime($v->visit_time??'now')), 'name'=>$v->customer_name??'', 'ref'=>$v->reason??'', 'amount'=>0, 'status'=>'']; }
    usort($activities, fn($a,$b)=>strtotime($b['date'])-strtotime($a['date']));
}
?>
<!-- Rep Performance Premium UI -->
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<style>
/* ═══════════════════════════════════════════════════════════════
   REP PERFORMANCE — DESIGN SYSTEM
   ═══════════════════════════════════════════════════════════════ */
.rp {
    /* Accent palette */
    --g1: #1b5e20; --g2: #2e7d32; --g3: #43a047; --gL: rgba(27,94,32,.08);
    --b1: #0369a1; --b2: #0284c7; --b3: #38bdf8; --bL: rgba(3,105,161,.08);
    --v1: #6d28d9; --v2: #7c3aed; --v3: #a78bfa; --vL: rgba(109,40,217,.08);
    --a1: #b45309; --a2: #d97706; --a3: #fbbf24; --aL: rgba(180,83,9,.08);
    --r1: #991b1b; --r2: #dc2626; --r3: #f87171; --rL: rgba(220,38,38,.08);
    --t1: #0f172a; --t2: #334155; --t3: #64748b; --t4: #94a3b8; --t5: #cbd5e1;
    --bg: #f0f2f6;
    --srf: rgba(255,255,255,.82);
    --srf2: rgba(255,255,255,.60);
    --bdr: rgba(255,255,255,.55);
    --bdr2: rgba(0,0,0,.06);
    --rad: 16px; --rad-s: 10px; --rad-xs: 6px;
    --sh: 0 4px 24px rgba(0,0,0,.06),0 1px 4px rgba(0,0,0,.04);
    --sh-lg: 0 12px 40px rgba(0,0,0,.10);
    font-family:'Inter',system-ui,sans-serif;
}

/* -- Wrapper --------------------------------------------------- */
.rp { padding:0; color:var(--t1); }
.rp *, .rp *::before, .rp *::after { box-sizing:border-box; }

/* -- Glass card ------------------------------------------------ */
.rp-card {
    background: rgba(255,255,255,.78);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,.55);
    border-radius: var(--rad);
    box-shadow: var(--sh);
    overflow: hidden;
    transition: box-shadow .25s, transform .25s;
}
.rp-card:hover { box-shadow: var(--sh-lg); transform:translateY(-2px); }

.rp-card-head {
    padding: 16px 20px;
    display: flex; align-items:center; justify-content:space-between; gap:10px;
    border-bottom: 1px solid rgba(0,0,0,.05);
}
.rp-card-title {
    margin:0; font-size:14px; font-weight:700; color:var(--t1);
    display:flex; align-items:center; gap:8px;
}
.rp-card-title i { font-size:16px; flex-shrink:0; }
.rp-body { padding:20px; }

/* -- Hero header ------------------------------------------------ */
.rp-hero {
    background: linear-gradient(135deg,#1b5e20 0%,#2e7d32 40%,#0369a1 100%);
    padding:28px 32px 24px;
    position:relative; overflow:hidden;
}
.rp-hero::before {
    content:'';position:absolute;inset:0;
    background: radial-gradient(ellipse 60% 80% at 80% 20%, rgba(255,255,255,.08) 0%,transparent 60%);
}
.rp-hero::after {
    content:'';position:absolute;bottom:-40px;left:40%;
    width:250px;height:250px;border-radius:50%;
    background:rgba(255,255,255,.03);
}
.rp-hero-inner { position:relative; z-index:1; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; }
.rp-hero-left { display:flex; align-items:center; gap:16px; }
.rp-hero-icon {
    width:52px;height:52px;border-radius:14px;
    background:rgba(255,255,255,.18);backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,.30);
    display:flex;align-items:center;justify-content:center;
    font-size:26px;color:#fff;flex-shrink:0;
}
.rp-hero-text h1 { font-size:22px;font-weight:800;color:#fff;margin:0;letter-spacing:-.3px; }
.rp-hero-text p  { font-size:13px;color:rgba(255,255,255,.72);margin:3px 0 0; }

/* Score pill */
.rp-score {
    display:inline-flex;align-items:center;gap:10px;
    background:rgba(255,255,255,.15);backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,.30);
    border-radius:50px;padding:10px 20px;
}
.rp-score-num { font-size:28px;font-weight:900;color:#fff;line-height:1; }
.rp-score-lbl { font-size:11px;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.8px; }
.rp-score-ring {
    width:52px;height:52px;flex-shrink:0;
}

/* -- Filter bar ----------------------------------------------- */
.rp-filters {
    padding:14px 32px;
    background:rgba(255,255,255,.70);
    backdrop-filter:blur(16px);
    border-bottom:1px solid rgba(0,0,0,.06);
    display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;
}
.rp-fg { display:flex;flex-direction:column;gap:4px;min-width:140px; }
.rp-fg label { font-size:10px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.7px; }
.rp-fg select, .rp-fg input {
    border:1px solid rgba(0,0,0,.1);border-radius:var(--rad-xs);
    background:#fff;color:var(--t1);padding:7px 10px;
    font-size:13px;font-weight:500;font-family:inherit;
    outline:none;transition:border-color .2s,box-shadow .2s;
}
.rp-fg select:focus, .rp-fg input:focus {
    border-color:var(--g2);box-shadow:0 0 0 3px rgba(27,94,32,.12);
}

/* Buttons */
.rp-btn {
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 16px;border-radius:var(--rad-xs);
    font-size:13px;font-weight:700;font-family:inherit;
    cursor:pointer;border:none;text-decoration:none;transition:all .2s;
}
.rp-btn-primary { background:linear-gradient(135deg,var(--g1),var(--g2));color:#fff; box-shadow:0 3px 10px rgba(27,94,32,.3); }
.rp-btn-primary:hover { box-shadow:0 5px 16px rgba(27,94,32,.4);transform:translateY(-1px); }
.rp-btn-ghost { background:rgba(255,255,255,.7);border:1px solid rgba(0,0,0,.1);color:var(--t2); }
.rp-btn-ghost:hover { background:#fff;border-color:rgba(0,0,0,.18); }
.rp-btn-xs { padding:5px 10px;font-size:11px; }

/* -- Tabs ----------------------------------------------------- */
.rp-tabs {
    background:rgba(255,255,255,.70);backdrop-filter:blur(16px);
    border-bottom:1px solid rgba(0,0,0,.06);
    display:flex;padding:0 32px;gap:0;overflow-x:auto;
}
.rp-tab {
    background:none;border:none;font-family:inherit;
    padding:13px 20px;font-size:13px;font-weight:600;color:var(--t3);
    cursor:pointer;transition:all .2s;
    border-bottom:2px solid transparent;white-space:nowrap;
    display:flex;align-items:center;gap:7px;
}
.rp-tab:hover { color:var(--t1); }
.rp-tab.active { color:var(--g1);border-bottom-color:var(--g1);font-weight:700; }

.rp-pane { display:none; }
.rp-pane.active { display:block; animation:rpIn .3s ease; }
@keyframes rpIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

/* -- Content layout ------------------------------------------- */
.rp-content { padding:24px 32px; }

/* Grids */
.rp-row { display:flex; flex-wrap:wrap; gap:18px; margin-bottom:18px; }
.rp-row > * { flex:1 1 220px; min-width: 220px; }
.rp-col-3 { flex:0 0 calc(33.333% - 12px); }
.rp-col-2 { flex:0 0 calc(50% - 9px); }
.rp-col-4 { flex:0 0 calc(25% - 13.5px); }
.rp-col-6 { flex:0 0 calc(16.666% - 15px); }
@media(max-width:1280px){ .rp-row{flex-wrap:wrap;} .rp-col-3,.rp-col-4,.rp-col-6{flex:0 0 calc(50% - 9px);} }
@media(max-width:768px){ .rp-row{flex-direction:column;} .rp-col-3,.rp-col-4,.rp-col-6,.rp-col-2{flex:1;} }

/* -- Stat cards ----------------------------------------------- */
.rp-stat {
    border-radius:var(--rad);padding:20px;
    background:rgba(255,255,255,.82);backdrop-filter:blur(16px);
    border:1px solid rgba(255,255,255,.55);box-shadow:var(--sh);
    position:relative;overflow:hidden;transition:all .25s;cursor:default;
    display:flex;flex-direction:column;gap:0;
}
.rp-stat:hover { transform:translateY(-3px);box-shadow:var(--sh-lg); }
.rp-stat-stripe {
    position:absolute;top:0;left:0;right:0;height:4px;border-radius:var(--rad) var(--rad) 0 0;
}
.rp-stat-icon {
    width:40px;height:40px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;font-size:20px;
    margin-bottom:14px;flex-shrink:0;
}
.rp-stat-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--t3);margin-bottom:6px; }
.rp-stat-value { font-size:22px;font-weight:800;color:var(--t1);line-height:1.15;margin-bottom:4px; }
.rp-stat-sub { font-size:12px;color:var(--t4);line-height:1.5;margin-bottom:10px; }
.rp-stat-bar-wrap { height:5px;background:rgba(0,0,0,.07);border-radius:3px;overflow:hidden;margin-top:auto; }
.rp-stat-bar { height:100%;border-radius:3px;transition:width 1.2s cubic-bezier(.34,1.56,.64,1); }
.rp-stat-pct { font-size:11px;font-weight:700;margin-top:5px; }

/* -- Table ---------------------------------------------------- */
.rp-tbl { width:100%;border-collapse:collapse;font-size:13px; }
.rp-tbl th {
    text-align:left;padding:11px 14px;font-size:11px;font-weight:700;
    text-transform:uppercase;letter-spacing:.7px;color:var(--t3);
    background:rgba(248,250,252,.8);border-bottom:1px solid rgba(0,0,0,.06);
}
.rp-tbl td { padding:12px 14px;color:var(--t2);border-bottom:1px solid rgba(0,0,0,.05);font-weight:500; }
.rp-tbl tr:last-child td { border-bottom:none; }
.rp-tbl tr:hover td { background:rgba(248,250,252,.8); }
.rp-tbl .strong { color:var(--t1);font-weight:700; }

/* Badges */
.rp-badge {
    display:inline-flex;align-items:center;gap:4px;
    padding:3px 9px;border-radius:30px;font-size:11px;font-weight:700;
}
.badge-g { background:rgba(27,94,32,.1);color:var(--g1); }
.badge-b { background:rgba(3,105,161,.1);color:var(--b1); }
.badge-v { background:rgba(109,40,217,.1);color:var(--v1); }
.badge-a { background:rgba(180,83,9,.1);color:var(--a1); }
.badge-r { background:rgba(220,38,38,.1);color:var(--r1); }

/* -- Activity feed -------------------------------------------- */
.rp-feed { max-height:440px;overflow-y:auto;padding-right:2px; }
.rp-feed::-webkit-scrollbar { width:3px; }
.rp-feed::-webkit-scrollbar-track { background:transparent; }
.rp-feed::-webkit-scrollbar-thumb { background:rgba(0,0,0,.1);border-radius:2px; }
.rp-fi {
    display:flex;gap:12px;align-items:flex-start;
    padding:12px 0;border-bottom:1px solid rgba(0,0,0,.05);
}
.rp-fi:last-child { border-bottom:none; }
.rp-fi-dot {
    width:36px;height:36px;border-radius:10px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:17px;margin-top:1px;
}
.dot-sale { background:rgba(27,94,32,.1);color:var(--g1); }
.dot-collection { background:rgba(109,40,217,.1);color:var(--v1); }
.dot-visit { background:rgba(180,83,9,.1);color:var(--a1); }
.rp-fi-body { flex:1;min-width:0; }
.rp-fi-top { display:flex;justify-content:space-between;gap:8px;margin-bottom:2px; }
.rp-fi-name { font-size:13px;font-weight:700;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.rp-fi-amt  { font-size:13px;font-weight:800;flex-shrink:0; }
.rp-fi-meta { font-size:11px;color:var(--t4);line-height:1.4; }

/* -- Rank rows ------------------------------------------------ */
.rp-rank {
    display:flex;align-items:center;gap:14px;
    padding:14px 20px;border-bottom:1px solid rgba(0,0,0,.05);transition:background .2s;
}
.rp-rank:last-child { border-bottom:none; }
.rp-rank:hover { background:rgba(248,250,252,.9); }
.rp-rank.me { background:rgba(27,94,32,.05); }
.rp-rnum {
    width:34px;height:34px;border-radius:9px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;
}
.rn1 { background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#000; }
.rn2 { background:rgba(148,163,184,.2);color:#64748b; }
.rn3 { background:rgba(180,83,9,.15);color:var(--a1); }
.rnN { background:rgba(0,0,0,.05);color:var(--t3); }
.rp-rank-info { flex:1;min-width:0; }
.rp-rank-info strong { font-size:14px;font-weight:700;color:var(--t1);display:block; }
.rp-rank-info span  { font-size:11px;color:var(--t4); }
.rp-rank-stats { display:flex;gap:22px;align-items:center;flex-shrink:0; }
.rp-rank-stat .v { font-size:14px;font-weight:800;color:var(--t1); }
.rp-rank-stat .l { font-size:10px;color:var(--t4);text-transform:uppercase;letter-spacing:.6px; }

/* -- Compare grid --------------------------------------------- */
.rp-cmp-grid { display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr; }
.rp-cmp-hd { padding:11px 16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--t4);background:rgba(248,250,252,.8);border-bottom:1px solid rgba(0,0,0,.06); }
.rp-cmp-cell { padding:13px 16px;font-size:13px;font-weight:600;color:var(--t2);border-bottom:1px solid rgba(0,0,0,.05); }
.rp-cmp-cell.hl { color:var(--g1);font-weight:800; }
.rp-cmp-cell.hl2 { color:var(--b1);font-weight:700; }

/* -- Export strip --------------------------------------------- */
.rp-export { display:flex;align-items:center;gap:8px;justify-content:flex-end;padding:0 0 16px;flex-wrap:wrap; }
.rp-export span { font-size:11px;font-weight:700;color:var(--t4);text-transform:uppercase;letter-spacing:.7px; }

/* -- Section heading ------------------------------------------ */
.rp-section-head { font-size:13px;font-weight:800;color:var(--t1);margin:0 0 14px;display:flex;align-items:center;gap:8px; }

/* -- Empty state ---------------------------------------------- */
.rp-empty { text-align:center;padding:56px 24px;color:var(--t3); }
.rp-empty i { font-size:48px;opacity:.3;display:block;margin:0 auto 14px; }
.rp-empty h3 { font-size:18px;color:var(--t2);margin:0 0 8px; }

/* Alert */
.rp-alert { margin:14px 32px;padding:12px 18px;border-radius:var(--rad-xs);font-size:14px;font-weight:600;display:flex;align-items:center;gap:10px; }
.rp-alert-ok  { background:rgba(27,94,32,.08);color:var(--g1);border:1px solid rgba(27,94,32,.15); }
.rp-alert-err { background:rgba(220,38,38,.08);color:var(--r1);border:1px solid rgba(220,38,38,.15); }
</style>

<div class="rp">

<?php if(!empty($data['success'])): ?>
    <div class="rp-alert rp-alert-ok"><i class="ph ph-check-circle"></i> <?=htmlspecialchars($data['success'])?></div>
<?php endif; ?>
<?php if(!empty($data['error'])): ?>
    <div class="rp-alert rp-alert-err"><i class="ph ph-warning"></i> <?=htmlspecialchars($data['error'])?></div>
<?php endif; ?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<div class="rp-hero">
    <div class="rp-hero-inner">
        <div class="rp-hero-left">
            <div class="rp-hero-icon"><i class="ph ph-presentation-chart"></i></div>
            <div class="rp-hero-text">
                <h1>Rep Performance Dashboard</h1>
                <p>
                    <?php
                        $mName = date('F', mktime(0,0,0,intval($data['month']),1));
                        echo "$mName {$data['year']}";
                        if (!empty($data['selected_route_id'])) {
                            foreach($data['routes'] as $rt) if($rt->id==$data['selected_route_id']) echo " &bull; Route: ".htmlspecialchars($rt->route_name);
                        }
                        if (!empty($data['selected_area_id'])) {
                            foreach($data['areas'] as $ar) if($ar->id==$data['selected_area_id']) echo " &bull; Area: ".htmlspecialchars($ar->name);
                        }
                    ?>
                </p>
            </div>
        </div>
        <?php if($hasPerfData):
            $score = $p['overall_score'];
            $grade = $score >= 90 ? 'A+' : ($score >= 80 ? 'A' : ($score >= 70 ? 'B' : ($score >= 60 ? 'C' : 'D')));
            $scoreColor = $score >= 80 ? '#22c55e' : ($score >= 60 ? '#f59e0b' : '#ef4444');
        ?>
        <div class="rp-score">
            <svg class="rp-score-ring" viewBox="0 0 52 52">
                <circle cx="26" cy="26" r="22" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="4"/>
                <circle cx="26" cy="26" r="22" fill="none" stroke="<?=$scoreColor?>" stroke-width="4"
                    stroke-dasharray="<?=round($score/100*138.2,1)?> 138.2"
                    stroke-linecap="round" transform="rotate(-90 26 26)"/>
                <text x="26" y="31" text-anchor="middle" fill="#fff" font-size="12" font-weight="900" font-family="Inter,sans-serif"><?=$grade?></text>
            </svg>
            <div>
                <div class="rp-score-num"><?=number_format($score,1)?>%</div>
                <div class="rp-score-lbl">Overall KPI Score</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ FILTER BAR ════════════════════════════════════════════ -->
<form method="GET" action="<?=APP_URL?>/repperformance">
<div class="rp-filters">
    <div class="rp-fg">
        <label>Representative</label>
        <select name="rep_user_id" onchange="this.form.submit()">
            <?php foreach($data['reps'] as $r): ?>
                <option value="<?=$r->id?>" <?=$data['selected_rep_id']==$r->id?'selected':''?>>
                    <?=htmlspecialchars($r->username)?> — <?=htmlspecialchars($r->first_name.' '.$r->last_name)?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="rp-fg">
        <label>Month</label>
        <select name="month">
            <?php for($m=1;$m<=12;$m++): $mv=str_pad($m,2,'0',STR_PAD_LEFT); ?>
                <option value="<?=$mv?>" <?=$data['month']===$mv?'selected':''?>><?=date('F',mktime(0,0,0,$m,1))?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="rp-fg">
        <label>Year</label>
        <select name="year">
            <?php for($y=date('Y');$y>=2024;$y--): ?>
                <option value="<?=$y?>" <?=$data['year']==$y?'selected':''?>><?=$y?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="rp-fg">
        <label>Route</label>
        <select name="route_id" onchange="this.form.submit()">
            <option value="">All Routes</option>
            <?php foreach($data['routes'] as $rt): ?>
                <option value="<?=$rt->id?>" <?=$data['selected_route_id']==$rt->id?'selected':''?>><?=htmlspecialchars($rt->route_name)?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="rp-fg">
        <label>Territory</label>
        <select name="area_id" onchange="this.form.submit()">
            <option value="">All Areas</option>
            <?php foreach($data['areas'] as $ar): ?>
                <option value="<?=$ar->id?>" <?=$data['selected_area_id']==$ar->id?'selected':''?>><?=htmlspecialchars($ar->name)?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;">
        <button type="submit" class="rp-btn rp-btn-primary"><i class="ph ph-funnel-simple"></i> Apply</button>
        <a href="<?=APP_URL?>/repperformance" class="rp-btn rp-btn-ghost"><i class="ph ph-arrow-counter-clockwise"></i></a>
    </div>
</div>
</form>

<!-- ══ TABS ══════════════════════════════════════════════════ -->
<div class="rp-tabs">
    <button class="rp-tab active" onclick="rpTab('rp1',this)"><i class="ph ph-gauge"></i> Overview</button>
    <button class="rp-tab" onclick="rpTab('rp2',this)"><i class="ph ph-chart-bar"></i> Analytics</button>
    <button class="rp-tab" onclick="rpTab('rp3',this)"><i class="ph ph-git-diff"></i> Compare</button>
    <button class="rp-tab" onclick="rpTab('rp4',this)"><i class="ph ph-trophy"></i> Leaderboard</button>
</div>

<!-- ══════════════════════════════════════════════════════════
     PANE 1 — OVERVIEW
══════════════════════════════════════════════════════════════ -->
<div id="rp1" class="rp-pane active">
<?php if(!$hasPerfData): ?>
<div class="rp-content">
    <div class="rp-card">
        <div class="rp-empty"><i class="ph ph-chart-polar"></i><h3>No performance data found</h3><p>Select a representative and date range, then click Apply.</p></div>
    </div>
</div>
<?php else: ?>
<div class="rp-content">

    <!-- ─ KPI STAT CARDS ─ -->
    <div class="rp-row" style="margin-bottom:18px;">
        <?php
        $kpiCards = [
            ['label'=>'Net Sales','val'=>'Rs '.number_format($p['net_sales'],0),'sub'=>$p['invoice_count'].' invoices · Returns Rs '.number_format($p['total_returns'],0),'color1'=>'#1b5e20','color2'=>'#43a047','dim'=>'rgba(27,94,32,.08)','icon'=>'ph-chart-line-up','pct'=>($p['kpi_scores']['sales_amount']['target']??0)>0?min(100,($p['net_sales']/($p['kpi_scores']['sales_amount']['target']))*100):null,'target'=>($p['kpi_scores']['sales_amount']['target']??0)>0?'Target Rs '.number_format($p['kpi_scores']['sales_amount']['target'],0):null],
            ['label'=>'Total Collections','val'=>'Rs '.number_format($p['total_collections'],0),'sub'=>'Cash, Cheque & Bank Transfer','color1'=>'#6d28d9','color2'=>'#7c3aed','dim'=>'rgba(109,40,217,.08)','icon'=>'ph-hand-coins','pct'=>min(100,$p['collection_efficiency']),'target'=>'Efficiency: '.number_format($p['collection_efficiency'],1).'%'],
            ['label'=>'Total Credit','val'=>'Rs '.number_format($p['total_outstanding'],0),'sub'=>'Total Outstanding Amount','color1'=>'#0369a1','color2'=>'#0284c7','dim'=>'rgba(3,105,161,.08)','icon'=>'ph-credit-card','pct'=>null,'target'=>'Credit Limit: Rs '.number_format($p['credit_limit'],0)],
            ['label'=>'Productive Visits','val'=>$p['productive_visits'],'sub'=>'Total bills billed','color1'=>'#1b5e20','color2'=>'#43a047','dim'=>'rgba(27,94,32,.08)','icon'=>'ph-check-circle','pct'=>($p['kpi_scores']['productive_visit_rate']['target']??0)>0?min(100,($p['productive_visits']/($p['kpi_scores']['productive_visit_rate']['target']))*100):null,'target'=>($p['kpi_scores']['productive_visit_rate']['target']??0)>0?'Target: '.$p['kpi_scores']['productive_visit_rate']['target'].' bills':null],
            ['label'=>'Total Unproductive Visits','val'=>$p['unproductive_visits'],'sub'=>'Visits with no sales','color1'=>'#991b1b','color2'=>'#dc2626','dim'=>'rgba(220,38,38,.08)','icon'=>'ph-x-circle','pct'=>null,'target'=>null],
            ['label'=>'Total Visits','val'=>$p['total_visited'],'sub'=>'Productive + Unproductive','color1'=>'#b45309','color2'=>'#d97706','dim'=>'rgba(180,83,9,.08)','icon'=>'ph-users','pct'=>($p['kpi_scores']['total_visits']['target']??0)>0?min(100,($p['total_visited']/($p['kpi_scores']['total_visits']['target']))*100):null,'target'=>($p['kpi_scores']['total_visits']['target']??0)>0?'Target: '.$p['kpi_scores']['total_visits']['target'].' visits':null],
            ['label'=>'Total Working Days','val'=>$p['working_days'],'sub'=>'Active route days','color1'=>'#065f46','color2'=>'#059669','dim'=>'rgba(6,95,70,.08)','icon'=>'ph-calendar-check','pct'=>($p['kpi_scores']['route_completion']['target']??0)>0?min(100,($p['working_days']/($p['kpi_scores']['route_completion']['target']))*100):null,'target'=>($p['kpi_scores']['route_completion']['target']??0)>0?'Target: '.$p['kpi_scores']['route_completion']['target'].' days':null],
        ];
        foreach($kpiCards as $kc):
        ?>
        <div class="rp-stat">
            <div class="rp-stat-stripe" style="background:linear-gradient(90deg,<?=$kc['color1']?>,<?=$kc['color2']?>);"></div>
            <div class="rp-stat-icon" style="background:<?=$kc['dim']?>;color:<?=$kc['color1']?>;"><i class="ph <?=$kc['icon']?>"></i></div>
            <div class="rp-stat-label"><?=$kc['label']?></div>
            <div class="rp-stat-value"><?=$kc['val']?></div>
            <div class="rp-stat-sub"><?=$kc['sub']?></div>
            <?php if($kc['pct']!==null): ?>
                <div class="rp-stat-bar-wrap"><div class="rp-stat-bar" style="width:<?=$kc['pct']?>%;background:linear-gradient(90deg,<?=$kc['color1']?>,<?=$kc['color2']?>);"></div></div>
                <div class="rp-stat-pct" style="color:<?=$kc['color1']?>;"><?=$kc['target']??number_format($kc['pct'],1).'% of target'?></div>
            <?php elseif($kc['target']): ?>
                <div class="rp-stat-pct" style="color:var(--t4);"><?=$kc['target']?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ─ EXPORT ─ -->
    <div class="rp-export">
        <span>Export:</span>
        <?php
        $ep = "rep_user_id={$data['selected_rep_id']}&start_date={$data['start_date']}&end_date={$data['end_date']}&route_id={$data['selected_route_id']}&area_id={$data['selected_area_id']}";
        $base = APP_URL.'/repperformance/export/';
        ?>
        <a href="<?=$base?>kpi?<?=$ep?>" class="rp-btn rp-btn-ghost rp-btn-xs"><i class="ph ph-file-csv"></i> KPI</a>
        <a href="<?=$base?>sales?<?=$ep?>" class="rp-btn rp-btn-ghost rp-btn-xs"><i class="ph ph-file-csv"></i> Sales</a>
        <a href="<?=$base?>route?<?=$ep?>" class="rp-btn rp-btn-ghost rp-btn-xs"><i class="ph ph-file-csv"></i> Routes</a>
        <a href="<?=$base?>collection?<?=$ep?>" class="rp-btn rp-btn-ghost rp-btn-xs"><i class="ph ph-file-csv"></i> Collections</a>
    </div>

    <!-- ─ ROW: TREND (65%) + RADAR (35%) ─ -->
    <div class="rp-row">
        <div style="flex:1.65;min-width:0;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-trend-up" style="color:var(--g1)"></i> Daily Sales & Collections</h4>
                <span class="rp-badge badge-g">This Period</span>
            </div>
            <div class="rp-body"><div style="height:300px;position:relative;"><canvas id="c_trend"></canvas></div></div>
        </div>
        <div style="flex:1;min-width:0;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-radar" style="color:var(--v1)"></i> KPI Performance Radar</h4>
            </div>
            <div class="rp-body"><div style="height:260px;position:relative;"><canvas id="c_radar"></canvas></div></div>
        </div>
    </div>

    <!-- ─ ROW: VISIT PIE + PAYMENT PIE + TOP CUSTOMERS + ACTIVITY FEED ─ -->
    <div class="rp-row">
        <!-- Visits breakdown -->
        <div style="flex:1;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-map-pin" style="color:var(--b1)"></i> Visits Breakdown</h4>
            </div>
            <div class="rp-body">
                <div style="height:190px;position:relative;margin-bottom:14px;"><canvas id="c_visits"></canvas></div>
                <table class="rp-tbl">
                    <tbody>
                        <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--g2);margin-right:8px;"></span>Productive</td><td class="strong" style="text-align:right;color:var(--g1);"><?=$p['productive_visits']?></td></tr>
                        <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--r2);margin-right:8px;"></span>Unproductive</td><td class="strong" style="text-align:right;color:var(--r1);"><?=$p['unproductive_visits']?></td></tr>
                        <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--b2);margin-right:8px;"></span>New Customers</td><td class="strong" style="text-align:right;color:var(--b1);"><?=$p['new_customers_added']?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment channels -->
        <div style="flex:1;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-coins" style="color:var(--v1)"></i> Payment Channels</h4>
            </div>
            <div class="rp-body">
                <div style="height:190px;position:relative;margin-bottom:14px;"><canvas id="c_pay"></canvas></div>
                <table class="rp-tbl">
                    <tbody>
                        <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--g2);margin-right:8px;"></span>Cash</td><td class="strong" style="text-align:right;color:var(--g1);">Rs <?=number_format($p['cash_collections'],0)?></td></tr>
                        <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--a2);margin-right:8px;"></span>Cheque</td><td class="strong" style="text-align:right;color:var(--a1);">Rs <?=number_format($p['cheque_collections'],0)?></td></tr>
                        <tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:var(--v2);margin-right:8px;"></span>Bank Transfer</td><td class="strong" style="text-align:right;color:var(--v1);">Rs <?=number_format($p['bank_collections'],0)?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Customers -->
        <div style="flex:1.2;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-crown" style="color:var(--a1)"></i> Top Customers</h4>
                <span class="rp-badge badge-a"><?=count($p['top_customers']??[])?> clients</span>
            </div>
            <div style="overflow:auto;">
                <table class="rp-tbl">
                    <thead><tr><th>#</th><th>Customer</th><th style="text-align:right">Revenue</th></tr></thead>
                    <tbody>
                        <?php if(empty($p['top_customers'])): ?>
                            <tr><td colspan="3" style="text-align:center;color:var(--t4);padding:20px;">No data</td></tr>
                        <?php else: $n=1; foreach($p['top_customers'] as $tc): ?>
                            <tr>
                                <td style="color:var(--t4);font-weight:700;"><?=$n++?></td>
                                <td class="strong"><?=htmlspecialchars($tc->customer_name)?></td>
                                <td style="text-align:right;font-weight:800;color:var(--g1);">Rs <?=number_format($tc->total_sales,0)?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Feed -->
        <div style="flex:1.5;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-activity" style="color:var(--g1)"></i> Activity Feed</h4>
                <span class="rp-badge badge-g"><?=count($activities)?> events</span>
            </div>
            <div class="rp-body" style="padding:14px 16px;">
                <div class="rp-feed">
                    <?php if(empty($activities)): ?>
                        <div style="text-align:center;padding:28px;color:var(--t4);">No recent activity</div>
                    <?php else: $cnt=0; foreach($activities as $act): if($cnt++>=18) break;
                        $typeClass = 'dot-'.$act['type'];
                        $icon = $act['type']==='sale' ? 'ph-receipt' : ($act['type']==='collection' ? 'ph-hand-coins' : 'ph-map-pin');
                        $amtColor = $act['type']==='sale' ? 'color:var(--g1)' : ($act['type']==='collection' ? 'color:var(--v1)' : 'color:var(--t4)');
                    ?>
                        <div class="rp-fi">
                            <div class="rp-fi-dot <?=$typeClass?>"><i class="ph <?=$icon?>"></i></div>
                            <div class="rp-fi-body">
                                <div class="rp-fi-top">
                                    <span class="rp-fi-name"><?=htmlspecialchars($act['name'])?></span>
                                    <?php if($act['amount']>0): ?>
                                        <span class="rp-fi-amt" style="<?=$amtColor?>">Rs <?=number_format($act['amount'],0)?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="rp-fi-meta"><?=htmlspecialchars($act['ref'])?> &bull; <?=htmlspecialchars($act['date'])?></div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ─ ROUTE HISTORY TABLE ─ -->
    <div class="rp-card">
        <div class="rp-card-head">
            <h4 class="rp-card-title"><i class="ph ph-map-trifold" style="color:var(--b1)"></i> Route Execution History</h4>
            <span class="rp-badge badge-b"><?=count($p['routes_detail']??[])?> routes</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rp-tbl">
                <thead>
                    <tr><th>Route</th><th>Start</th><th>End</th><th>Km Start</th><th>Km End</th><th>Distance</th><th>Vehicle</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($p['routes_detail'])): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--t4);padding:28px;">No route executions in this period</td></tr>
                    <?php else: foreach($p['routes_detail'] as $rt):
                        $dist = ($rt->end_meter && $rt->start_meter) ? number_format(floatval($rt->end_meter)-floatval($rt->start_meter)).' km' : '—';
                        $s = $rt->status??'Unknown'; $sc = in_array($s,['Finalized','Completed'])?'g':'a';
                    ?>
                        <tr>
                            <td class="strong"><?=htmlspecialchars($rt->route_name)?></td>
                            <td><?=htmlspecialchars($rt->start_time)?></td>
                            <td><?=htmlspecialchars($rt->end_time??'—')?></td>
                            <td><?=number_format(floatval($rt->start_meter))?> km</td>
                            <td><?=$rt->end_meter?number_format(floatval($rt->end_meter)).' km':'—'?></td>
                            <td style="font-weight:700;"><?=$dist?></td>
                            <td><span class="rp-badge badge-b"><?=htmlspecialchars($rt->vehicle_number??'N/A')?></span></td>
                            <td><span class="rp-badge badge-<?=$sc?>"><?=htmlspecialchars($s)?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     PANE 2 — ANALYTICS
══════════════════════════════════════════════════════════════ -->
<div id="rp2" class="rp-pane">
<?php if($hasPerfData): ?>
<div class="rp-content">

    <!-- 6-month historical trend -->
    <?php if(!empty($data['monthly_trend'])): ?>
    <div class="rp-card" style="margin-bottom:18px;">
        <div class="rp-card-head">
            <h4 class="rp-card-title"><i class="ph ph-calendar-blank" style="color:var(--b1)"></i> 6-Month Historical Trend</h4>
            <span class="rp-badge badge-b">Sales · Collections · KPI Score</span>
        </div>
        <div class="rp-body"><div style="height:320px;position:relative;"><canvas id="c_monthly"></canvas></div></div>
    </div>
    <?php endif; ?>

    <div class="rp-row">
        <!-- KPI Scoring Table -->
        <div style="flex:1.5;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-star" style="color:var(--a1)"></i> KPI Evaluation Detail</h4>
            </div>
            <div style="overflow-x:auto;">
                <table class="rp-tbl">
                    <thead>
                        <tr><th>Dimension</th><th style="text-align:right">Target</th><th style="text-align:right">Actual</th><th style="text-align:right">Achievement</th><th style="text-align:right">Weight</th><th style="text-align:right">Score</th><th>Progress</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($p['kpi_scores'] as $sc): $pct=min(100,$sc['achievement_pct']); $bc=$pct>=100?'#1b5e20':($pct>=70?'#d97706':'#dc2626'); ?>
                        <tr>
                            <td class="strong"><?=htmlspecialchars($sc['name'])?></td>
                            <td style="text-align:right;color:var(--t3);"><?=number_format($sc['target'],1)?></td>
                            <td style="text-align:right;font-weight:700;"><?=number_format($sc['actual'],1)?></td>
                            <td style="text-align:right;">
                                <span class="rp-badge <?=$pct>=100?'badge-g':($pct>=70?'badge-a':'badge-r')?>"><?=number_format($pct,1)?>%</span>
                            </td>
                            <td style="text-align:right;color:var(--t3);"><?=number_format($sc['weight'],1)?>%</td>
                            <td style="text-align:right;font-weight:800;color:var(--g1);"><?=number_format($sc['contribution'],1)?></td>
                            <td style="width:100px;">
                                <div style="height:5px;background:rgba(0,0,0,.08);border-radius:3px;overflow:hidden;">
                                    <div style="height:100%;width:<?=$pct?>%;background:<?=$bc?>;border-radius:3px;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Product Category Mix -->
        <div style="flex:1;" class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-pie-chart" style="color:var(--b1)"></i> Product Category Mix</h4>
            </div>
            <div class="rp-body"><div style="height:260px;position:relative;"><canvas id="c_cat"></canvas></div></div>
        </div>
    </div>

    <!-- Top Products + Category Revenue -->
    <div class="rp-row">
        <div class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-shopping-bag" style="color:var(--g1)"></i> Top Products Sold</h4>
            </div>
            <div style="overflow-x:auto;">
                <table class="rp-tbl">
                    <thead><tr><th>#</th><th>Product</th><th style="text-align:right">Qty</th><th style="text-align:right">Revenue</th></tr></thead>
                    <tbody>
                        <?php if(empty($p['top_products'])): ?>
                            <tr><td colspan="4" style="text-align:center;color:var(--t4);padding:20px;">No data</td></tr>
                        <?php else: $n=1; foreach($p['top_products'] as $pr): ?>
                            <tr>
                                <td style="color:var(--t4);font-weight:700;"><?=$n++?></td>
                                <td class="strong"><?=htmlspecialchars($pr->product_name)?></td>
                                <td style="text-align:right;font-weight:700;color:var(--b1);"><?=number_format($pr->qty)?></td>
                                <td style="text-align:right;font-weight:800;color:var(--g1);">Rs <?=number_format($pr->total_sales,0)?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rp-card">
            <div class="rp-card-head">
                <h4 class="rp-card-title"><i class="ph ph-tag" style="color:var(--v1)"></i> Revenue by Category</h4>
            </div>
            <div style="overflow-x:auto;">
                <table class="rp-tbl">
                    <thead><tr><th>#</th><th>Category</th><th style="text-align:right">Revenue (Rs)</th></tr></thead>
                    <tbody>
                        <?php if(empty($p['top_categories'])): ?>
                            <tr><td colspan="3" style="text-align:center;color:var(--t4);padding:20px;">No data</td></tr>
                        <?php else: $n=1; foreach($p['top_categories'] as $cat): ?>
                            <tr>
                                <td style="color:var(--t4);font-weight:700;"><?=$n++?></td>
                                <td class="strong"><?=htmlspecialchars($cat->category_name)?></td>
                                <td style="text-align:right;font-weight:800;color:var(--g1);">Rs <?=number_format($cat->total_sales,0)?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php else: ?>
<div class="rp-content"><div class="rp-card"><div class="rp-empty"><i class="ph ph-chart-bar"></i><h3>No Data</h3><p>Select a rep above.</p></div></div></div>
<?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     PANE 3 — COMPARE
══════════════════════════════════════════════════════════════ -->
<div id="rp3" class="rp-pane">
<div class="rp-content">
    <div class="rp-card" style="margin-bottom:18px;">
        <div class="rp-card-head">
            <h4 class="rp-card-title"><i class="ph ph-git-diff" style="color:var(--b1)"></i> Side-by-Side Comparison</h4>
            <form method="GET" action="<?=APP_URL?>/repperformance" style="display:flex;gap:10px;align-items:flex-end;">
                <input type="hidden" name="rep_user_id" value="<?=$data['selected_rep_id']?>">
                <input type="hidden" name="month" value="<?=$data['month']?>">
                <input type="hidden" name="year" value="<?=$data['year']?>">
                <div class="rp-fg">
                    <label>Compare vs</label>
                    <select name="compare_user_id" onchange="this.form.submit()">
                        <option value="">Select competitor…</option>
                        <?php foreach($data['reps'] as $r): if($r->id!=$data['selected_rep_id']): ?>
                            <option value="<?=$r->id?>" <?=($data['compare_rep_id']??0)==$r->id?'selected':''?>><?=htmlspecialchars($r->username)?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="rp-btn rp-btn-primary" style="margin-bottom:1px;"><i class="ph ph-arrows-left-right"></i> Compare</button>
            </form>
        </div>

        <?php if(!empty($data['compare_data'])): ?>
        <div class="rp-body"><div style="height:340px;position:relative;"><canvas id="c_compare"></canvas></div></div>
        <?php endif; ?>

        <?php if($hasPerfData):
            $pR=$data['perf_data']; $cR=$data['compare_data']??null; $tR=$data['team_avg'];
            $selName=''; $cmpName='';
            foreach($data['reps'] as $r){ if($r->id==$data['selected_rep_id']) $selName=$r->username; if($r->id==($data['compare_rep_id']??0)) $cmpName=$r->username; }
            $rows=[
                ['Metric','Selected Rep','Competitor','Team Avg'],
                ['Overall KPI Score', number_format($pR['overall_score'],1).'%', $cR?number_format($cR['overall_score'],1).'%':'—', number_format($tR['overall_score'],1).'%'],
                ['Net Sales (Rs)', 'Rs '.number_format($pR['net_sales'],0), $cR?'Rs '.number_format($cR['net_sales'],0):'—', 'Rs '.number_format($tR['net_sales'],0)],
                ['Collections (Rs)', 'Rs '.number_format($pR['total_collections'],0), $cR?'Rs '.number_format($cR['total_collections'],0):'—', 'Rs '.number_format($tR['total_collections'],0)],
                ['Productive Visits', $pR['productive_visits'], $cR?$cR['productive_visits']:'—', number_format($tR['productive_visits'],1)],
                ['New Customers', $pR['new_customers_added'], $cR?$cR['new_customers_added']:'—', number_format($tR['new_customers_added'],1)],
                ['Route Completion', number_format($pR['route_completion_rate'],1).'%', $cR?number_format($cR['route_completion_rate'],1).'%':'—', '—'],
                ['Collection Efficiency', number_format($pR['collection_efficiency'],1).'%', $cR?number_format($cR['collection_efficiency'],1).'%':'—', '—'],
                ['Invoices Issued', $pR['invoice_count'], $cR?($cR['invoice_count']??'—'):'—', '—'],
            ];
        ?>
        <div style="overflow-x:auto;border-top:1px solid rgba(0,0,0,.06);">
            <div class="rp-cmp-grid">
                <?php foreach($rows as $i=>$row):
                    $isHead = ($i === 0);
                    $c0 = $isHead ? 'rp-cmp-hd' : 'rp-cmp-cell';
                    $c1 = $isHead ? 'rp-cmp-hd hl' : 'rp-cmp-cell hl';
                    $c2 = $isHead ? 'rp-cmp-hd hl2' : 'rp-cmp-cell hl2';
                    $v0 = $isHead ? $row[0] : '<strong>'.$row[0].'</strong>';
                    $v1 = $isHead ? ($selName ?: $row[1]) : $row[1];
                    $v2 = $isHead ? ($cmpName ?: $row[2]) : $row[2];
                ?>
                    <div class="<?=$c0?>"><?=$v0?></div>
                    <div class="<?=$c1?>"><?=$v1?></div>
                    <div class="<?=$c2?>"><?=$v2?></div>
                    <div class="<?=$c0?>"><?=$row[3]?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PANE 4 — LEADERBOARD
══════════════════════════════════════════════════════════════ -->
<div id="rp4" class="rp-pane">
<div class="rp-content">
    <div class="rp-card">
        <div class="rp-card-head">
            <h4 class="rp-card-title"><i class="ph ph-trophy" style="color:var(--a1)"></i> Team Leaderboard</h4>
            <span class="rp-badge badge-a"><?=count($data['rankings'])?> representatives</span>
        </div>
        <?php $rank=1; foreach($data['rankings'] as $rnk):
            $isMe = $rnk['id']==$data['selected_rep_id'];
            $nc   = $rank===1?'rn1':($rank===2?'rn2':($rank===3?'rn3':'rnN'));
            $ico  = $rank===1?'👑':($rank===2?'🥈':($rank===3?'🥉':$rank));
            $sc   = $rnk['score']; $sc_c=$sc>=80?'var(--g1)':($sc>=60?'var(--a1)':'var(--r1)');
        ?>
            <div class="rp-rank <?=$isMe?'me':''?>">
                <div class="rp-rnum <?=$nc?>"><?=$ico?></div>
                <div class="rp-rank-info">
                    <strong><?=htmlspecialchars($rnk['username'])?></strong>
                    <span><?=htmlspecialchars($rnk['first_name'].' '.$rnk['last_name'])?></span>
                </div>
                <div class="rp-rank-stats">
                    <div class="rp-rank-stat">
                        <div class="v" style="color:var(--g1);">Rs <?=number_format($rnk['net_sales'],0)?></div>
                        <div class="l">Net Sales</div>
                    </div>
                    <div class="rp-rank-stat">
                        <div class="v" style="color:var(--v1);">Rs <?=number_format($rnk['total_collections'],0)?></div>
                        <div class="l">Collections</div>
                    </div>
                    <div class="rp-rank-stat">
                        <div style="height:36px;width:36px;">
                            <svg viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="rgba(0,0,0,.07)" stroke-width="3"/>
                                <circle cx="18" cy="18" r="14" fill="none" stroke="<?=$sc_c?>" stroke-width="3"
                                    stroke-dasharray="<?=round($sc/100*87.96,1)?> 87.96"
                                    stroke-linecap="round" transform="rotate(-90 18 18)"/>
                                <text x="18" y="22" text-anchor="middle" fill="<?=$sc_c?>" font-size="8" font-weight="800" font-family="Inter,sans-serif"><?=round($sc)?>%</text>
                            </svg>
                        </div>
                    </div>
                </div>
                <?php if($isMe): ?><span class="rp-badge badge-g" style="flex-shrink:0;">You</span><?php endif; ?>
            </div>
        <?php $rank++; endforeach; ?>
    </div>
</div>
</div>

</div><!-- .rp -->

<!-- ══ CHARTS ════════════════════════════════════════════════ -->
<script>
function rpTab(id,btn){
    document.querySelectorAll('.rp-pane').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.rp-tab').forEach(b=>b.classList.remove('active'));
    document.getElementById(id).classList.add('active');btn.classList.add('active');
}

// Global Chart defaults
Chart.defaults.font.family="'Inter',system-ui,sans-serif";
Chart.defaults.color='#64748b';
Chart.defaults.plugins.tooltip.backgroundColor='rgba(15,23,42,.9)';
Chart.defaults.plugins.tooltip.borderColor='rgba(255,255,255,.1)';
Chart.defaults.plugins.tooltip.borderWidth=1;
Chart.defaults.plugins.tooltip.padding=12;
Chart.defaults.plugins.tooltip.cornerRadius=8;
Chart.defaults.plugins.tooltip.titleFont={size:13,weight:'700'};
Chart.defaults.plugins.tooltip.bodyFont={size:12,weight:'500'};

<?php if($hasPerfData): $p=$data['perf_data']; ?>

// 1. Trend Chart
(()=>{
    const dates=<?=json_encode(array_column($p['sales_trend'],'label'))?>;
    const sales=<?=json_encode(array_map('floatval',array_column($p['sales_trend'],'sales_amount')))?>;
    const colMap=<?=json_encode(array_reduce($p['collections_trend'],fn($c,$i)=>array_merge($c,[$i->label=>floatval($i->col_amount)]),[])) ?>;
    const cols=dates.map(d=>colMap[d]||0);
    new Chart(document.getElementById('c_trend'),{
        type:'bar',
        data:{labels:dates,datasets:[
            {label:'Sales',data:sales,backgroundColor:'rgba(27,94,32,.55)',borderRadius:5,order:2},
            {label:'Collections',data:cols,type:'line',borderColor:'#7c3aed',backgroundColor:'rgba(124,58,237,.1)',
             borderWidth:2.5,tension:.4,pointRadius:3,pointBackgroundColor:'#fff',pointBorderColor:'#7c3aed',
             pointBorderWidth:2,fill:true,order:1}
        ]},
        options:{responsive:true,maintainAspectRatio:false,
            interaction:{mode:'index',intersect:false},
            plugins:{legend:{position:'top',labels:{boxWidth:12,usePointStyle:true}}},
            scales:{
                x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11}}},
                y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{callback:v=>'Rs '+(v>=1000?Math.round(v/1000)+'k':v),font:{size:11}}}
            }
        }
    });
})();

// 2. Radar Chart
(()=>{
    const labels=<?=json_encode(array_values(array_map(fn($s)=>$s['name'],$p['kpi_scores'])))?>;
    const pcts=<?=json_encode(array_values(array_map(fn($s)=>min(100,round($s['achievement_pct'],1)),$p['kpi_scores'])))?>;
    new Chart(document.getElementById('c_radar'),{
        type:'radar',
        data:{labels,datasets:[{
            label:'Achievement %',data:pcts,
            backgroundColor:'rgba(27,94,32,.12)',borderColor:'#2e7d32',
            pointBackgroundColor:'#2e7d32',pointBorderColor:'#fff',pointBorderWidth:2,pointRadius:4,borderWidth:2
        }]},
        options:{responsive:true,maintainAspectRatio:false,
            scales:{r:{min:0,max:100,
                grid:{color:'rgba(0,0,0,.06)'},angleLines:{color:'rgba(0,0,0,.06)'},
                pointLabels:{font:{size:11,weight:'600'},color:'#475569'},
                ticks:{display:false}
            }},
            plugins:{legend:{display:false}}
        }
    });
})();

// 3. Visit Pie
(()=>{
    new Chart(document.getElementById('c_visits'),{
        type:'doughnut',
        data:{
            labels:['Productive','Unproductive'],
            datasets:[{
                data:[<?=$p['productive_visits']?>,<?=$p['unproductive_visits']?>],
                backgroundColor:['#2e7d32','#dc2626'],borderColor:'#fff',borderWidth:3,hoverOffset:5
            }]
        },
        options:{responsive:true,maintainAspectRatio:false,cutout:'72%',plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}}}
    });
})();

// 4. Payment Pie
(()=>{
    new Chart(document.getElementById('c_pay'),{
        type:'doughnut',
        data:{
            labels:['Cash','Cheque','Bank Transfer'],
            datasets:[{
                data:[<?=floatval($p['cash_collections'])?>,<?=floatval($p['cheque_collections'])?>,<?=floatval($p['bank_collections'])?>],
                backgroundColor:['#2e7d32','#d97706','#7c3aed'],borderColor:'#fff',borderWidth:3,hoverOffset:5
            }]
        },
        options:{responsive:true,maintainAspectRatio:false,cutout:'72%',plugins:{legend:{position:'bottom',labels:{boxWidth:10,usePointStyle:true}}}}
    });
})();

<?php endif; ?>

// 5. Monthly Trend
<?php if(!empty($data['monthly_trend'])): ?>
(()=>{
    const el=document.getElementById('c_monthly'); if(!el)return;
    const L=<?=json_encode(array_column($data['monthly_trend'],'label'))?>;
    const S=<?=json_encode(array_map('floatval',array_column($data['monthly_trend'],'net_sales')))?>;
    const C=<?=json_encode(array_map('floatval',array_column($data['monthly_trend'],'total_collections')))?>;
    const K=<?=json_encode(array_map('floatval',array_column($data['monthly_trend'],'overall_score')))?>;
    new Chart(el,{
        type:'bar',
        data:{labels:L,datasets:[
            {label:'Net Sales',data:S,backgroundColor:'rgba(3,105,161,.6)',borderRadius:5,yAxisID:'y'},
            {label:'Collections',data:C,backgroundColor:'rgba(27,94,32,.6)',borderRadius:5,yAxisID:'y'},
            {label:'KPI Score %',data:K,type:'line',borderColor:'#d97706',backgroundColor:'rgba(217,119,6,.08)',
             borderWidth:2.5,tension:.4,pointRadius:5,pointBackgroundColor:'#fff',pointBorderColor:'#d97706',
             pointBorderWidth:2,fill:true,yAxisID:'y1'}
        ]},
        options:{responsive:true,maintainAspectRatio:false,
            interaction:{mode:'index',intersect:false},
            plugins:{legend:{position:'top',labels:{boxWidth:12,usePointStyle:true}}},
            scales:{
                x:{grid:{color:'rgba(0,0,0,.04)'}},
                y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{callback:v=>'Rs '+(v>=1000?Math.round(v/1000)+'k':v)}},
                y1:{position:'right',min:0,max:100,grid:{drawOnChartArea:false},
                    ticks:{callback:v=>v+'%',color:'#d97706',font:{weight:'700'}}}
            }
        }
    });
})();
<?php endif; ?>

// 6. Category Chart
<?php if($hasPerfData && !empty($p['top_categories'])): ?>
(()=>{
    const el=document.getElementById('c_cat'); if(!el)return;
    new Chart(el,{
        type:'doughnut',
        data:{
            labels:<?=json_encode(array_column($p['top_categories'],'category_name'))?>,
            datasets:[{
                data:<?=json_encode(array_map('floatval',array_column($p['top_categories'],'total_sales')))?>,
                backgroundColor:['#2e7d32','#0284c7','#d97706','#7c3aed','#dc2626','#0891b2'],
                borderColor:'#fff',borderWidth:3,hoverOffset:6
            }]
        },
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',
            plugins:{legend:{position:'right',labels:{boxWidth:10,usePointStyle:true,font:{size:11,weight:'600'},color:'#475569'}}}}
    });
})();
<?php endif; ?>

// 7. Compare Chart
<?php if(!empty($data['compare_data'])):
    $pR=$data['perf_data'];$cR=$data['compare_data'];$tR=$data['team_avg'];
    $sn=''; $cn='';
    foreach($data['reps'] as $r){ if($r->id==$data['selected_rep_id']) $sn=$r->username; if($r->id==($data['compare_rep_id']??0)) $cn=$r->username; }
?>
(()=>{
    const el=document.getElementById('c_compare'); if(!el)return;
    new Chart(el,{
        type:'bar',
        data:{
            labels:['KPI Score (%)','Net Sales (10k Rs)','Collections (10k Rs)','Productive Visits','New Customers'],
            datasets:[
                {label:<?=json_encode($sn?:'Selected')?>,data:[<?=round($pR['overall_score'],1)?>,<?=round($pR['net_sales']/10000,1)?>,<?=round($pR['total_collections']/10000,1)?>,<?=$pR['productive_visits']?>,<?=$pR['new_customers_added']?>],backgroundColor:'rgba(27,94,32,.75)',borderRadius:5},
                {label:<?=json_encode($cn?:'Competitor')?>,data:[<?=round($cR['overall_score'],1)?>,<?=round($cR['net_sales']/10000,1)?>,<?=round($cR['total_collections']/10000,1)?>,<?=$cR['productive_visits']?>,<?=$cR['new_customers_added']?>],backgroundColor:'rgba(3,105,161,.75)',borderRadius:5},
                {label:'Team Avg',data:[<?=round($tR['overall_score'],1)?>,<?=round($tR['net_sales']/10000,1)?>,<?=round($tR['total_collections']/10000,1)?>,<?=round($tR['productive_visits'],1)?>,<?=round($tR['new_customers_added'],1)?>],backgroundColor:'rgba(100,116,139,.5)',borderRadius:5}
            ]
        },
        options:{responsive:true,maintainAspectRatio:false,
            plugins:{legend:{position:'top',labels:{boxWidth:12,usePointStyle:true}}},
            scales:{
                x:{grid:{color:'rgba(0,0,0,.04)'}},
                y:{grid:{color:'rgba(0,0,0,.04)'}}
            }
        }
    });
})();
<?php endif; ?>
</script>