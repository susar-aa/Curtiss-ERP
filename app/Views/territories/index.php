<?php
// Dynamic Stats
$totalMainAreas = count($data['main_areas'] ?? []);
$totalRoutes = 0;
foreach ($data['main_areas'] ?? [] as $area) {
    $totalRoutes += count($area->mcas ?? []);
}
$totalReps = count($data['reps'] ?? []);
?>

<!-- Leaflet for Map Routing -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE — TERRITORY MANAGEMENT
   ============================================================ */

:root {
    --c-bg:           #f2f2f7;
    --c-surface:      #ffffff;
    --c-surface2:     #f9f9fb;
    --c-fill:         rgba(120,120,128,0.12);
    --c-fill2:        rgba(120,120,128,0.16);
    --c-separator:    rgba(60,60,67,0.12);
    --c-separator2:   rgba(60,60,67,0.06);

    --c-blue:         #007aff;
    --c-blue-light:   #e5f2ff;
    --c-blue-mid:     #b3d6ff;
    --c-green:        #34c759;
    --c-green-light:  #e6f9ec;
    --c-orange:       #ff9500;
    --c-orange-light: #fff4e5;
    --c-red:          #ff3b30;
    --c-red-light:    #fff0ef;

    --f-system: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Inter', 'Helvetica Neue', sans-serif;
    --f-mono:   ui-monospace, 'SF Mono', 'Menlo', 'Monaco', monospace;

    --t-primary:   #1c1c1e;
    --t-secondary: #636366;
    --t-tertiary:  #aeaeb2;
    --t-label:     #8e8e93;

    --shadow-xs:  0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm:  0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md:  0 8px 24px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-xl:  0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06);

    --r-xs: 6px;
    --r-sm: 10px;
    --r-md: 14px;
    --r-lg: 20px;
    --r-xl: 26px;
    --r-pill: 999px;

    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --ease-ios:    cubic-bezier(0.25, 0.1, 0.25, 1);
    --dur-fast:    0.18s;
    --dur-mid:     0.28s;
    --dur-slow:    0.42s;
}

@media (prefers-color-scheme: dark) {
    :root {
        --c-bg:           #121212;
        --c-surface:      #1e1e2e;
        --c-surface2:     #161622;
        --c-fill:         rgba(255,255,255,0.08);
        --c-fill2:        rgba(255,255,255,0.12);
        --c-separator:    rgba(255,255,255,0.15);
        --c-separator2:   rgba(255,255,255,0.08);
        --t-primary:   #f5f5f7;
        --t-secondary: #a1a1aa;
        --t-tertiary:  #71717a;
        --t-label:     #52525b;
    }
}

.terr-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.terr-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 16px 24px 100px;
}

/* ---- Stat Cards ---- */
.stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    transition: transform var(--dur-fast) var(--ease-ios), box-shadow var(--dur-fast) var(--ease-ios);
    cursor: default;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2.5px;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
}
.stat-card.blue::before  { background: var(--c-blue); }
.stat-card.orange::before { background: var(--c-orange); }
.stat-card.purple::before { background: var(--c-blue-mid); }
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.stat-icon {
    width: 46px; height: 46px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-card.blue  .stat-icon { background: var(--c-blue-light);   color: var(--c-blue); }
.stat-card.orange .stat-icon { background: var(--c-orange-light); color: var(--c-orange); }
.stat-card.purple   .stat-icon { background: #f3e8ff;    color: #a855f7; }
.stat-info { display: flex; flex-direction: column; justify-content: center; }
.stat-num {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.04em;
    color: var(--t-primary);
    line-height: 1.1;
    margin-bottom: 2px;
}
.stat-lbl {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--t-label);
}

/* ---- Filter Shelf ---- */
.filter-shelf {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 20px;
}
.filter-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    color: var(--t-secondary);
    box-shadow: var(--shadow-xs);
    transition: border-color var(--dur-fast), box-shadow var(--dur-fast);
}
.filter-chip:focus-within {
    border-color: var(--c-blue);
    box-shadow: 0 0 0 3px rgba(0,122,255,0.12);
}
.filter-chip-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--t-label);
    text-transform: uppercase;
}
.filter-reset {
    background: transparent;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    cursor: pointer;
    transition: all var(--dur-fast);
}
.filter-reset:hover { background: var(--c-fill); color: var(--t-primary); }
.filter-count {
    margin-left: auto;
    font-size: 13px;
    color: var(--t-secondary);
    font-weight: 500;
}
.filter-count strong { color: var(--t-primary); font-weight: 700; }

/* ---- Custom Dropdown ---- */
.sf-dropdown { position: relative; outline: none; cursor: pointer; }
.sf-dropdown-val {
    display: flex; align-items: center; gap: 5px;
    font-size: 13.5px; font-weight: 600; color: var(--t-primary);
}
.sf-dropdown-val::after {
    content: '';
    display: inline-block; width: 12px; height: 12px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238e8e93' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") center/contain no-repeat;
}
.sf-dropdown-menu {
    position: absolute; top: calc(100% + 10px); left: 0; z-index: 200;
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-xl);
    min-width: 220px;
    max-height: 280px; overflow-y: auto;
    opacity: 0; visibility: hidden;
    transform: translateY(-6px) scale(0.98);
    transform-origin: top left;
    transition: opacity var(--dur-mid) var(--ease-ios), transform var(--dur-mid) var(--ease-ios), visibility var(--dur-mid);
    padding: 6px;
}
.sf-dropdown:focus-within .sf-dropdown-menu {
    opacity: 1; visibility: visible; transform: translateY(0) scale(1);
}
.sf-dropdown-item {
    padding: 9px 12px;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--t-primary);
    border-radius: var(--r-sm);
    transition: background var(--dur-fast);
    cursor: pointer;
}
.sf-dropdown-item:hover { background: var(--c-fill); }
.sf-dropdown-item.active { background: var(--c-blue-light); color: var(--c-blue); font-weight: 600; }

/* ---- Area Cards & Lists ---- */
.area-panel {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    margin-bottom: 16px;
    overflow: hidden;
}
.area-row-header {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--c-surface);
    cursor: pointer;
    transition: background var(--dur-fast);
}
.area-row-header:hover { background: var(--c-surface2); }
.area-title-group { display: flex; align-items: center; gap: 14px; }
.area-bullet {
    width: 36px; height: 36px;
    background: var(--c-blue-light);
    color: var(--c-blue);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.area-name-text { font-size: 15px; font-weight: 700; color: var(--t-primary); }
.mca-table-container { border-top: 0.5px solid var(--c-separator); background: var(--c-surface2); display: none; }
.mca-table { width: 100%; border-collapse: collapse; }
.mca-table th {
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--t-label);
    background: var(--c-separator2);
    border-bottom: 0.5px solid var(--c-separator);
    text-align: left;
}
.mca-table td {
    padding: 12px 20px;
    font-size: 13.5px;
    color: var(--t-primary);
    border-bottom: 0.5px solid var(--c-separator2);
    vertical-align: middle;
}
.mca-table tr:last-child td { border-bottom: none; }

/* ---- Badges ---- */
.sf-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: var(--r-pill);
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.02em;
}
.sf-badge.badge-active { background: var(--c-blue-light); color: var(--c-blue); }
.sf-badge.badge-unassigned { background: var(--c-orange-light); color: var(--c-orange); }
.sf-badge.badge-success { background: var(--c-green-light); color: var(--c-green); }
.sf-badge.badge-error { background: var(--c-red-light); color: var(--c-red); }
.sf-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* ---- Alerts ---- */
.sf-alert {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--c-surface);
    border-radius: var(--r-md);
    padding: 14px 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-xs);
    border: 0.5px solid var(--c-separator);
    border-left-width: 3.5px;
    font-size: 14px;
}
.sf-alert.success { border-left-color: var(--c-green); }
.sf-alert.error   { border-left-color: var(--c-red); }
.sf-alert-icon { font-size: 18px; flex-shrink: 0; padding-top: 1px; }
.sf-alert.success .sf-alert-icon { color: var(--c-green); }
.sf-alert.error   .sf-alert-icon { color: var(--c-red); }
.sf-alert-title { font-weight: 700; color: var(--t-primary); margin-bottom: 2px; }
.sf-alert-msg   { color: var(--t-secondary); font-size: 13px; }
.sf-alert-close {
    margin-left: auto; flex-shrink: 0; background: none; border: none;
    color: var(--t-tertiary); cursor: pointer; font-size: 15px; padding: 2px;
}
.sf-alert-close:hover { color: var(--t-secondary); }

/* ---- Buttons ---- */
.sf-btn {
    padding: 8px 14px;
    border-radius: var(--r-md);
    font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    border: 0.5px solid transparent; cursor: pointer;
    transition: transform var(--dur-fast) var(--ease-spring), filter var(--dur-fast);
    text-decoration: none;
}
.sf-btn:active { transform: scale(0.97); }
.sf-btn.primary { background: var(--t-primary); color: #fff; }
.sf-btn.neutral { background: var(--c-surface); border-color: var(--c-separator); color: var(--t-primary); box-shadow: var(--shadow-xs); }
.sf-btn.neutral:hover { background: var(--c-surface2); }
.sf-btn.blue { background: var(--c-blue); color: #fff; }
.sf-btn.blue-light { background: var(--c-blue-light); color: var(--c-blue); }

/* ---- Modal System ---- */
.modal-veil {
    position: fixed; inset: 0; z-index: 2000;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: saturate(180%) blur(14px);
    -webkit-backdrop-filter: saturate(180%) blur(14px);
    display: flex; align-items: center; justify-content: center;
}
.modal-veil.hidden { display: none !important; }
.sf-modal {
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-xl);
    box-shadow: var(--shadow-xl);
    width: 900px; max-width: 95vw;
    animation: sfModalSlide var(--dur-mid) var(--ease-spring);
    overflow: hidden;
    display: flex;
}
.sf-modal.narrow { width: 440px; }
@keyframes sfModalSlide {
    from { transform: translateY(20px) scale(0.97); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}
.modal-form-side { flex: 1.2; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; }
.modal-map-side { flex: 1.8; border-left: 0.5px solid var(--c-separator); display: flex; flex-direction: column; }
.modal-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px;
}
.modal-title { font-size: 17px; font-weight: 700; margin: 0; }
.modal-close {
    background: var(--c-fill); border: none; width: 26px; height: 26px;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: var(--t-label); cursor: pointer; font-size: 12px;
}
.modal-close:hover { background: var(--c-fill2); color: var(--t-secondary); }

/* ---- Form controls ---- */
.sf-group { margin-bottom: 16px; }
.sf-group label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: var(--t-secondary); text-transform: uppercase; }
.sf-input {
    width: 100%; padding: 10px 14px;
    border-radius: var(--r-sm); border: 0.5px solid var(--c-separator);
    background: var(--c-surface2); color: var(--t-primary);
    font-size: 14px; outline: none; transition: border-color var(--dur-fast);
    box-sizing: border-box;
}
.sf-input:focus { border-color: var(--c-blue); background: var(--c-surface); }
.search-box { display: flex; gap: 5px; margin-bottom: 5px; }

/* ---- Map Container ---- */
.map-container { width: 100%; height: 500px; background: #eee; }

/* ---- Command Bar (Dynamic Island) ---- */
.cmd-bar {
    position: fixed;
    bottom: 28px; left: 50%;
    transform: translateX(-50%);
    background: rgba(28, 28, 30, 0.92);
    backdrop-filter: saturate(180%) blur(28px);
    -webkit-backdrop-filter: saturate(180%) blur(28px);
    border: 0.5px solid rgba(255,255,255,0.12);
    border-radius: var(--r-pill);
    padding: 7px 10px;
    display: flex; align-items: center; gap: 4px;
    box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3);
    z-index: 100;
}
.cmd-search {
    display: flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.1);
    border-radius: var(--r-pill);
    padding: 8px 14px;
    width: 196px;
    transition: width var(--dur-slow) var(--ease-ios), background var(--dur-mid);
}
.cmd-search:focus-within {
    width: 300px;
    background: rgba(255,255,255,0.18);
}
.cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; flex-shrink: 0; }
.cmd-search input {
    background: transparent; border: none; outline: none;
    color: #fff; font-size: 14px; font-weight: 500;
    font-family: var(--f-system); width: 100%;
}
.cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
.cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
.cmd-cta {
    display: flex; align-items: center; gap: 7px;
    background: #fff; color: #1c1c1e;
    border: none; border-radius: var(--r-pill);
    padding: 0 18px; height: 38px;
    font-size: 14px; font-weight: 700;
    font-family: var(--f-system);
    cursor: pointer; text-decoration: none;
    transition: transform var(--dur-fast) var(--ease-spring), background var(--dur-fast);
    margin-left: 2px;
}
.cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }

.hidden { display: none !important; }
</style>

<div class="terr-root">
    <div class="terr-wrap">

        <!-- Stat Cards Row -->
        <div class="stat-row" style="margin-top: 10px;">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($totalMainAreas) ?></div>
                    <div class="stat-lbl">Main Territories</div>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa-solid fa-route"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($totalRoutes) ?></div>
                    <div class="stat-lbl">Mapped Routes</div>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($totalReps) ?></div>
                    <div class="stat-lbl">Active Representatives</div>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <?php if (!empty($data['error'])): ?>
            <div class="sf-alert error" id="error-alert">
                <i class="fa-solid fa-circle-exclamation sf-alert-icon"></i>
                <div>
                    <div class="sf-alert-title">Error</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($data['error']) ?></div>
                </div>
                <button type="button" class="sf-alert-close" onclick="document.getElementById('error-alert').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($data['success'])): ?>
            <div class="sf-alert success" id="success-alert">
                <i class="fa-solid fa-circle-check sf-alert-icon"></i>
                <div>
                    <div class="sf-alert-title">Success</div>
                    <div class="sf-alert-msg"><?= htmlspecialchars($data['success']) ?></div>
                </div>
                <button type="button" class="sf-alert-close" onclick="document.getElementById('success-alert').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- Filters Block -->
        <div class="filter-shelf">
            <!-- Filter by Representative -->
            <div class="filter-chip">
                <span class="filter-chip-label">Representative</span>
                <div class="sf-dropdown" tabindex="0">
                    <div class="sf-dropdown-val" id="rep-dropdown-val">All Representatives</div>
                    <div class="sf-dropdown-menu">
                        <div class="sf-dropdown-item active" data-val="" onclick="selectRep('', 'All Representatives')">All Representatives</div>
                        <?php foreach($data['reps'] as $rep): 
                            $fullName = htmlspecialchars($rep->first_name . ' ' . $rep->last_name);
                            $val = htmlspecialchars(strtolower($rep->username));
                        ?>
                            <div class="sf-dropdown-item" data-val="<?= $val ?>" onclick="selectRep('<?= $val ?>', '<?= $fullName ?>')"><?= $fullName ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="filterRep" value="">
                </div>
            </div>

            <!-- Reset Button -->
            <button type="button" onclick="clearAllFilters()" class="filter-reset">Reset</button>

            <!-- Counter -->
            <div class="filter-count">
                <strong id="matching-count"><?= $totalMainAreas ?></strong> main territories listed
            </div>
        </div>

        <!-- Territories List Panel -->
        <div id="mainAreasList">
            <?php if (empty($data['main_areas'])): ?>
                <div class="area-panel" style="padding: 40px; text-align: center; color: var(--t-secondary);">
                    <i class="fa-solid fa-map-location" style="font-size: 28px; margin-bottom: 8px; color: var(--t-tertiary);"></i><br>
                    No territories registered yet. Click "+ Create Main Area" at the bottom to start.
                </div>
            <?php else: ?>
                <?php foreach($data['main_areas'] as $area): 
                    $repName = !empty($area->first_name) ? ($area->first_name . ' ' . $area->last_name) : '';
                    $repUsername = !empty($area->username) ? strtolower($area->username) : '';
                ?>
                    <div class="area-panel main-area-card" 
                         data-id="<?= $area->id ?>"
                         data-name="<?= htmlspecialchars(strtolower($area->name)) ?>"
                         data-rep="<?= htmlspecialchars($repUsername) ?>">
                        
                        <div class="area-row-header" onclick="toggleMca('mca_container_<?= $area->id ?>')">
                            <div class="area-title-group">
                                <div class="area-bullet"><i class="fa-solid fa-map-pin"></i></div>
                                <div>
                                    <div class="area-name-text"><?= htmlspecialchars($area->name) ?></div>
                                    <div style="font-size: 11px; color: var(--t-secondary); margin-top: 2px;">
                                        Coordinate Center (Lat: <?= round($area->latitude, 4) ?>, Lng: <?= round($area->longitude, 4) ?>)
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 12px;" onclick="event.stopPropagation()">
                                <?php if (!empty($repName)): ?>
                                    <span class="sf-badge badge-active"><span class="dot"></span>Rep: <?= htmlspecialchars($repName) ?></span>
                                <?php else: ?>
                                    <span class="sf-badge badge-unassigned"><span class="dot"></span>Unassigned</span>
                                <?php endif; ?>
                                
                                <button class="sf-btn blue-light" onclick="openMapModal('mca', <?= $area->id ?>, '<?= htmlspecialchars(addslashes($area->name)) ?>')">
                                    <i class="fa-solid fa-route"></i> Add Route
                                </button>
                                
                                <button class="sf-btn neutral" onclick="toggleMca('mca_container_<?= $area->id ?>')" style="padding: 8px 10px;">
                                    <i class="fa-solid fa-chevron-down" id="arrow_mca_container_<?= $area->id ?>"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mca-table-container" id="mca_container_<?= $area->id ?>">
                            <table class="mca-table">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Master Card Route</th>
                                        <th style="width: 25%;">GPS Start / End</th>
                                        <th style="width: 15%;">Budget Distance</th>
                                        <th style="width: 15%;">Actual Distance</th>
                                        <th style="width: 15%;">HQ Distance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($area->mcas)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: var(--t-secondary); padding: 20px;">
                                                No routes mapped to this area yet.
                                            </td>
                                        </tr>
                                    <?php else: foreach($area->mcas as $mca): ?>
                                        <tr class="mca-route-row" data-name="<?= htmlspecialchars(strtolower($mca->name)) ?>">
                                            <td><strong><?= htmlspecialchars($mca->name) ?></strong></td>
                                            <td style="font-family: var(--f-mono); font-size: 11px;">
                                                <span style="color:var(--c-green);"><i class="fa-solid fa-play"></i> <?= round($mca->start_lat, 4) ?>, <?= round($mca->start_lng, 4) ?></span><br>
                                                <span style="color:var(--c-red);"><i class="fa-solid fa-stop"></i> <?= round($mca->end_lat, 4) ?>, <?= round($mca->end_lng, 4) ?></span>
                                            </td>
                                            <td style="font-family: var(--f-mono); font-weight: 600;"><?= number_format($mca->budget_km, 1) ?> KM</td>
                                            
                                            <?php 
                                                $variance = $mca->actual_route_km - $mca->budget_km; 
                                                $isOver = $variance > 0;
                                            ?>
                                            <td>
                                                <span style="font-family: var(--f-mono); font-weight: 700; color: var(--c-blue);"><?= number_format($mca->actual_route_km, 1) ?> KM</span>
                                                <?php if($mca->budget_km > 0): ?>
                                                    <span class="sf-badge <?= $isOver ? 'badge-error' : 'badge-success' ?>" style="margin-left: 6px; font-size: 9.5px; padding: 2px 6px;">
                                                        <?= $isOver ? '+' : '' ?><?= number_format($variance, 1) ?> KM
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-family: var(--f-mono); color: var(--t-secondary);"><?= number_format($mca->distance_to_start_km ?? 0, 1) ?> KM</td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal Dialog Veil -->
<div class="modal-veil hidden" id="mapModal">
    <div class="sf-modal" id="sfModalCard">
        <!-- Form Side -->
        <div class="modal-form-side">
            <div>
                <div class="modal-head">
                    <h3 class="modal-title" id="modalTitle">Create Main Area</h3>
                    <button type="button" class="modal-close" onclick="closeMapModal()"><i class="fa-solid fa-xmark"></i></button>
                </div>
                
                <form action="<?= APP_URL ?>/territory" method="POST" id="territoryForm">
                    <input type="hidden" name="action" id="formAction" value="add_main_area">
                    <input type="hidden" name="main_area_id" id="formParentId" value="">
                    
                    <div class="sf-group">
                        <label id="nameLabel">Route/Area Name *</label>
                        <input type="text" name="name" id="formName" class="sf-input" placeholder="e.g. Colombo Area" required>
                    </div>

                    <!-- Representative Assignment Selection -->
                    <div id="repAssignmentGroup" class="sf-group">
                        <label>Assign Representative</label>
                        <select name="rep_id" id="formRepId" class="sf-input">
                            <option value="">-- Select Representative --</option>
                            <?php foreach($data['reps'] as $rep): ?>
                                <option value="<?= $rep->id ?>"><?= htmlspecialchars($rep->first_name . ' ' . $rep->last_name . ' (' . $rep->username . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Single Point Inputs (Main Area) -->
                    <div id="singlePointInputs">
                        <!-- Location search hidden or removed as per user instruction -->
                        <input type="hidden" name="latitude" id="formLat" value="7.8731">
                        <input type="hidden" name="longitude" id="formLng" value="80.7718">
                    </div>

                    <!-- Route Inputs (MCA) -->
                    <div id="routePointInputs" style="display: none;">
                        <div class="sf-group" style="background: rgba(46, 125, 50, 0.05); padding: 10px; border-radius: 8px; border: 0.5px solid rgba(46, 125, 50, 0.2);">
                            <label style="color:#2e7d32; font-size: 11px;">Start Location</label>
                            <div class="search-box">
                                <input type="text" id="startSearchInput" class="sf-input" placeholder="e.g. Dambokka">
                                <button type="button" class="sf-btn primary" onclick="searchLocation('start')" style="padding: 8px 12px;">Search</button>
                            </div>
                            <input type="hidden" name="start_lat" id="formStartLat">
                            <input type="hidden" name="start_lng" id="formStartLng">
                        </div>

                        <div class="sf-group" style="background: rgba(198, 40, 40, 0.05); padding: 10px; border-radius: 8px; border: 0.5px solid rgba(198, 40, 40, 0.2); margin-top: 10px;">
                            <label style="color:#c62828; font-size: 11px;">End Location</label>
                            <div class="search-box">
                                <input type="text" id="endSearchInput" class="sf-input" placeholder="e.g. Rambukkana">
                                <button type="button" class="sf-btn primary" onclick="searchLocation('end')" style="padding: 8px 12px;">Search</button>
                            </div>
                            <input type="hidden" name="end_lat" id="formEndLat">
                            <input type="hidden" name="end_lng" id="formEndLng">
                        </div>

                        <div style="background: var(--c-surface2); padding: 12px 14px; border-radius: 8px; margin-top: 14px; border: 0.5px solid var(--c-separator);">
                            <div class="sf-group" style="margin-bottom: 10px;">
                                <label>Budget Distance (KM) *</label>
                                <input type="number" name="budget_km" id="budgetKm" step="0.1" min="0" class="sf-input" value="0.0" style="font-family: var(--f-mono);">
                            </div>
                            <div class="sf-group" style="margin-bottom: 0;">
                                <label>Map Driving Distance</label>
                                <div style="font-size: 20px; font-weight: 800; color: var(--c-blue); font-family: var(--f-mono);" id="actualKmDisplay">0.0 KM</div>
                                <input type="hidden" name="actual_route_km" id="formActualKm" value="0">
                            </div>
                        </div>
                        
                        <button type="button" class="sf-btn neutral" onclick="resetRouteMap()" style="margin-top: 10px; width: 100%; display: flex; justify-content: center;">
                            <i class="fa-solid fa-trash-can"></i> Clear Route Map
                        </button>
                    </div>
                </form>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="button" class="sf-btn neutral" style="flex: 1; justify-content: center; padding: 11px;" onclick="closeMapModal()">Cancel</button>
                <button type="button" class="sf-btn blue" style="flex: 1; justify-content: center; padding: 11px;" onclick="document.getElementById('territoryForm').submit();">Save Mapping</button>
            </div>
        </div>
        
        <!-- Map Side -->
        <div class="modal-map-side" id="modalMapColumn">
            <div id="interactiveMap" class="map-container"></div>
        </div>
    </div>
</div>

<!-- Command Bar (Dynamic Island style) -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="cmdSearchInput" placeholder="Search territories..." oninput="handleSearch(this.value)">
    </div>
    <div class="cmd-divider"></div>
    <button type="button" class="cmd-cta" onclick="openMapModal('main')">
        <i class="fa-solid fa-plus"></i> Create Main Area
    </button>
</div>

<script>
    let map = null;
    let mainMarker = null;
    let startMarker = null;
    let endMarker = null;
    let routeLine = null;
    let currentMode = 'main';

    const greenIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41]
    });
    const redIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41]
    });

    function toggleMca(id) {
        const el = document.getElementById(id);
        const arrow = document.getElementById('arrow_' + id);
        if (el.style.display === 'block') {
            el.style.display = 'none';
            if (arrow) {
                arrow.classList.remove('fa-chevron-up');
                arrow.classList.add('fa-chevron-down');
            }
        } else {
            el.style.display = 'block';
            if (arrow) {
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-up');
            }
        }
    }

    function initMap() {
        if(map !== null) return;
        map = L.map('interactiveMap').setView([7.8731, 80.7718], 7); 
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

        map.on('click', function(e) {
            if (currentMode === 'mca') {
                if (!startMarker) {
                    placeStartMarker(e.latlng.lat, e.latlng.lng);
                } else if (!endMarker) {
                    placeEndMarker(e.latlng.lat, e.latlng.lng);
                }
            }
        });
    }

    function placeStartMarker(lat, lng) {
        if(startMarker) map.removeLayer(startMarker);
        startMarker = L.marker([lat, lng], {icon: greenIcon}).addTo(map);
        document.getElementById('formStartLat').value = lat;
        document.getElementById('formStartLng').value = lng;
        drawRealRoute();
    }

    function placeEndMarker(lat, lng) {
        if(endMarker) map.removeLayer(endMarker);
        endMarker = L.marker([lat, lng], {icon: redIcon}).addTo(map);
        document.getElementById('formEndLat').value = lat;
        document.getElementById('formEndLng').value = lng;
        drawRealRoute();
    }

    async function drawRealRoute() {
        if (routeLine) map.removeLayer(routeLine);
        
        if (startMarker && endMarker) {
            const start = startMarker.getLatLng();
            const end = endMarker.getLatLng();
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${start.lng},${start.lat};${end.lng},${end.lat}?overview=full&geometries=geojson`;

            try {
                const response = await fetch(osrmUrl);
                const data = await response.json();

                if(data.routes && data.routes.length > 0) {
                    const route = data.routes[0];
                    const distanceKm = (route.distance / 1000).toFixed(2);

                    document.getElementById('formActualKm').value = distanceKm;
                    document.getElementById('actualKmDisplay').innerText = distanceKm + ' KM';

                    routeLine = L.geoJSON(route.geometry, {
                        style: { color: '#007aff', weight: 5, opacity: 0.8 }
                    }).addTo(map);

                    map.fitBounds(routeLine.getBounds(), {padding: [50, 50]});
                } else {
                    fallbackToStraightLine(start, end);
                }
            } catch(e) {
                console.error("Routing Error:", e);
                fallbackToStraightLine(start, end);
            }
        }
    }

    function fallbackToStraightLine(start, end) {
        routeLine = L.polyline([start, end], {color: '#ff3b30', weight: 4, dashArray: '5, 10'}).addTo(map);
        map.fitBounds(routeLine.getBounds(), {padding: [50, 50]});
        document.getElementById('actualKmDisplay').innerText = "Routing Failed";
    }

    function resetRouteMap() {
        if(startMarker) map.removeLayer(startMarker);
        if(endMarker) map.removeLayer(endMarker);
        if(routeLine) map.removeLayer(routeLine);
        startMarker = null; endMarker = null; routeLine = null;
        document.getElementById('formStartLat').value = ''; document.getElementById('formStartLng').value = '';
        document.getElementById('formEndLat').value = ''; document.getElementById('formEndLng').value = '';
        document.getElementById('formActualKm').value = '0';
        document.getElementById('actualKmDisplay').innerText = '0.0 KM';
    }

    async function searchLocation(type) {
        let inputId = type === 'start' ? 'startSearchInput' : 'endSearchInput';
        const query = document.getElementById(inputId).value;
        if(!query) return;

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Sri Lanka')}&limit=1`;
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            if(data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                
                map.flyTo([lat, lng], 13);
                
                if (type === 'start') {
                    placeStartMarker(lat, lng);
                    updateRouteName();
                } else if (type === 'end') {
                    placeEndMarker(lat, lng);
                    updateRouteName();
                }
            } else {
                alert("Location not found. Try searching a nearby town.");
            }
        } catch (error) { console.error("Geocoding Error: ", error); }
    }

    function updateRouteName() {
        const start = document.getElementById('startSearchInput').value;
        const end = document.getElementById('endSearchInput').value;
        if(start && end) {
            document.getElementById('formName').value = `From ${start} To ${end}`;
        }
    }

    function openMapModal(mode, parentId = '', parentName = '') {
        const modal = document.getElementById('mapModal');
        const card = document.getElementById('sfModalCard');
        const mapCol = document.getElementById('modalMapColumn');

        modal.classList.remove('hidden');
        currentMode = mode;
        document.getElementById('formName').value = '';
        document.getElementById('formRepId').value = '';

        if(mode === 'main') {
            document.getElementById('modalTitle').innerText = 'Create Main Area';
            document.getElementById('nameLabel').innerText = 'Main Area Name *';
            document.getElementById('formAction').value = 'add_main_area';
            document.getElementById('repAssignmentGroup').style.display = 'block';
            document.getElementById('singlePointInputs').style.display = 'block';
            document.getElementById('routePointInputs').style.display = 'none';
            
            // For main area, hide the map and shrink the modal card
            card.classList.add('narrow');
            mapCol.style.display = 'none';
        } else {
            document.getElementById('modalTitle').innerText = 'Add Route to: ' + parentName;
            document.getElementById('nameLabel').innerText = 'Route Name *';
            document.getElementById('formAction').value = 'add_mca';
            document.getElementById('formParentId').value = parentId;
            document.getElementById('repAssignmentGroup').style.display = 'none';
            document.getElementById('singlePointInputs').style.display = 'none';
            document.getElementById('routePointInputs').style.display = 'block';
            
            // For route map, show the map and expand the modal card
            card.classList.remove('narrow');
            mapCol.style.display = 'flex';
            
            initMap();
            setTimeout(() => { map.invalidateSize(); }, 200);
            
            resetRouteMap();
            map.setView([7.8731, 80.7718], 7);
        }
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
    }

    /* ---- Live Search & Filters JS ---- */
    function handleSearch(query) {
        query = query.toLowerCase().trim();
        const mainCards = document.querySelectorAll('.main-area-card');
        let visibleCount = 0;

        mainCards.forEach(card => {
            const areaName = card.getAttribute('data-name');
            const repUsername = card.getAttribute('data-rep');
            const repFilterVal = document.getElementById('filterRep').value;
            
            let matchesSearch = areaName.includes(query);
            let matchesRep = !repFilterVal || repUsername === repFilterVal;

            // Also check inner routes if we matches search inside route name
            let matchesRouteInside = false;
            const routeRows = card.querySelectorAll('.mca-route-row');
            routeRows.forEach(row => {
                const routeName = row.getAttribute('data-name');
                if (routeName.includes(query)) {
                    matchesRouteInside = true;
                }
            });

            if ((matchesSearch || matchesRouteInside) && matchesRep) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        document.getElementById('matching-count').innerText = visibleCount;
    }

    function selectRep(val, displayLabel) {
        document.getElementById('rep-dropdown-val').innerText = displayLabel;
        document.getElementById('filterRep').value = val;
        
        // Remove active class from menu items
        const dropdownItems = document.querySelectorAll('.sf-dropdown-menu .sf-dropdown-item');
        dropdownItems.forEach(item => {
            if (item.getAttribute('data-val') === val) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // Trigger search filter refresh
        handleSearch(document.getElementById('cmdSearchInput').value);
    }

    function clearAllFilters() {
        document.getElementById('cmdSearchInput').value = '';
        selectRep('', 'All Representatives');
    }
</script>