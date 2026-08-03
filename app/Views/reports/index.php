<?php
$categories = $data['categories'] ?? [];
$groupedReports = $data['grouped_reports'] ?? [];

$categoryMeta = [
    'inventory' => [
        'icon' => 'fa-boxes-stacked',
        'color' => 'blue',
        'desc' => 'Stock movements, batch tracking, valuation & reorders'
    ],
    'sales' => [
        'icon' => 'fa-chart-line',
        'color' => 'green',
        'desc' => 'Invoicing, revenue summaries, sales reps & margins'
    ],
    'procurement' => [
        'icon' => 'fa-truck-ramp-box',
        'color' => 'orange',
        'desc' => 'Purchase orders, supplier receipts & return tracking'
    ],
    'customer' => [
        'icon' => 'fa-users',
        'color' => 'purple',
        'desc' => 'Aging, balances, credit limits & customer analytics'
    ],
    'supplier' => [
        'icon' => 'fa-store',
        'color' => 'orange',
        'desc' => 'Payables aging, statements & procurement histories'
    ],
    'finance' => [
        'icon' => 'fa-building-columns',
        'color' => 'blue',
        'desc' => 'P&L, Balance Sheet, Cash Flows, Trial Balance & GL'
    ],
    'collection' => [
        'icon' => 'fa-hand-holding-dollar',
        'color' => 'green',
        'desc' => 'Receipts, credit collections & payment allocations'
    ],
    'route' => [
        'icon' => 'fa-route',
        'color' => 'purple',
        'desc' => 'Driver routes, trip performance & field sales'
    ],
    'management' => [
        'icon' => 'fa-briefcase',
        'color' => 'blue',
        'desc' => 'Executive KPIs, periodic performance & health metrics'
    ]
];

$totalReportsCount = 0;
foreach ($groupedReports as $list) {
    $totalReportsCount += count($list);
}
$activeCategoriesCount = count($categories);
?>

<!-- Inter Font & FontAwesome Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* ============================================================
   SF PRO + APPLE DESIGN LANGUAGE - REPORTS HUB
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
    --c-purple:       #af52de;
    --c-purple-light: #f5eeff;

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

.rep-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    background: var(--c-bg);
    -webkit-font-smoothing: antialiased;
}

.rep-wrap {
    max-width: 1420px;
    margin: 0 auto;
    padding: 16px 24px 120px;
}

/* Header */
.rep-header {
    margin-bottom: 20px;
}
.rep-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-blue);
    margin-bottom: 4px;
}
.rep-title {
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--t-primary);
    margin: 0 0 6px 0;
}
.rep-desc {
    font-size: 14.5px;
    color: var(--t-secondary);
    margin: 0;
    max-width: 760px;
}

/* Stat Cards Row */
.stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
    margin-top: 14px;
}
.stat-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    padding: 18px 22px;
    box-shadow: var(--shadow-sm);
    border: 0.5px solid var(--c-separator);
    transition: transform var(--dur-fast), box-shadow var(--dur-fast);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
}
.stat-card.blue::before   { background: var(--c-blue); }
.stat-card.purple::before { background: var(--c-purple); }
.stat-card.green::before  { background: var(--c-green); }
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--r-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-card.blue   .stat-icon { background: var(--c-blue-light); color: var(--c-blue); }
.stat-card.purple .stat-icon { background: var(--c-purple-light); color: var(--c-purple); }
.stat-card.green  .stat-icon { background: var(--c-green-light); color: var(--c-green); }
.stat-num {
    font-size: 24px;
    font-weight: 800;
    color: var(--t-primary);
    line-height: 1.1;
    margin-bottom: 2px;
}
.stat-lbl {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--t-label);
}

/* Category Filter Shelf */
.filter-shelf {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 24px;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    box-shadow: var(--shadow-xs);
    cursor: pointer;
    transition: all var(--dur-fast) var(--ease-spring);
    user-select: none;
}
.filter-chip:hover {
    background: var(--c-surface2);
    color: var(--t-primary);
    transform: translateY(-1px);
}
.filter-chip.active {
    background: var(--c-blue);
    border-color: var(--c-blue);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0,122,255,0.28);
}
.filter-chip .chip-count {
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: var(--r-pill);
    background: var(--c-fill);
    color: inherit;
}
.filter-chip.active .chip-count {
    background: rgba(255,255,255,0.25);
    color: #ffffff;
}
.filter-reset {
    background: transparent;
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-secondary);
    cursor: pointer;
    transition: background var(--dur-fast);
}
.filter-reset:hover {
    background: var(--c-fill);
    color: var(--t-primary);
}
.filter-count {
    margin-left: auto;
    font-size: 13px;
    color: var(--t-secondary);
    font-weight: 500;
}
.filter-count strong {
    color: var(--t-primary);
    font-weight: 700;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 20px;
}

.category-card {
    background: var(--c-surface);
    border-radius: var(--r-xl);
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform var(--dur-fast), box-shadow var(--dur-fast), border-color var(--dur-fast);
}
.category-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: rgba(0,122,255,0.3);
}

.cat-header {
    padding: 16px 20px;
    background: var(--c-surface2);
    border-bottom: 0.5px solid var(--c-separator);
    display: flex;
    align-items: center;
    gap: 12px;
}
.cat-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: var(--r-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.cat-icon-badge.blue   { background: var(--c-blue-light); color: var(--c-blue); }
.cat-icon-badge.green  { background: var(--c-green-light); color: var(--c-green); }
.cat-icon-badge.orange { background: var(--c-orange-light); color: var(--c-orange); }
.cat-icon-badge.purple { background: var(--c-purple-light); color: var(--c-purple); }

.cat-header-text {
    flex: 1;
    min-width: 0;
}
.cat-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--t-primary);
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.cat-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: var(--r-pill);
    background: var(--c-fill);
    color: var(--t-secondary);
}
.cat-desc {
    font-size: 12px;
    color: var(--t-label);
    margin: 2px 0 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Reports List inside Card */
.reports-list {
    padding: 6px 0;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.report-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    text-decoration: none;
    color: var(--t-primary);
    border-bottom: 0.5px solid var(--c-separator2);
    transition: background var(--dur-fast), transform var(--dur-fast);
}
.report-item:last-child {
    border-bottom: none;
}
.report-item:hover {
    background: var(--c-fill);
}
.report-item:hover .rep-name {
    color: var(--c-blue);
}
.report-item:hover .rep-arrow {
    transform: translateX(3px);
    color: var(--c-blue);
}

.rep-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding-right: 12px;
}
.rep-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--t-primary);
    transition: color var(--dur-fast);
}
.rep-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}
.rep-tag {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 2px 6px;
    border-radius: var(--r-xs);
    background: var(--c-surface2);
    border: 0.5px solid var(--c-separator);
    color: var(--t-label);
}
.rep-tag.realtime {
    background: var(--c-green-light);
    color: #1a7f3c;
    border-color: rgba(52,199,89,0.2);
}
.rep-tag.drilldown {
    background: var(--c-blue-light);
    color: var(--c-blue);
    border-color: rgba(0,122,255,0.2);
}
.rep-arrow {
    font-size: 12px;
    color: var(--t-tertiary);
    transition: transform var(--dur-fast) var(--ease-spring), color var(--dur-fast);
    flex-shrink: 0;
}

/* Empty State */
.rep-empty {
    grid-column: 1 / -1;
    background: var(--c-surface);
    border-radius: var(--r-xl);
    padding: 48px 24px;
    text-align: center;
    border: 0.5px solid var(--c-separator);
    box-shadow: var(--shadow-sm);
    display: none;
}
.rep-empty-icon {
    font-size: 42px;
    color: var(--t-label);
    margin-bottom: 12px;
}
.rep-empty-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--t-primary);
    margin-bottom: 6px;
}
.rep-empty-desc {
    font-size: 14px;
    color: var(--t-secondary);
    max-width: 420px;
    margin: 0 auto 18px;
}

/* Floating Command Bar (Dynamic Island) */
.cmd-bar {
    position: fixed;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(28, 28, 30, 0.92);
    backdrop-filter: saturate(180%) blur(28px);
    -webkit-backdrop-filter: saturate(180%) blur(28px);
    border: 0.5px solid rgba(255,255,255,0.12);
    border-radius: var(--r-pill);
    padding: 7px 10px;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: var(--shadow-xl), 0 0 0 0.5px rgba(0,0,0,0.3);
    z-index: 1000;
}
.cmd-search {
    display: flex;
    align-items: center;
    gap: 9px;
    background: rgba(255,255,255,0.1);
    border-radius: var(--r-pill);
    padding: 8px 14px;
    width: 220px;
    transition: width var(--dur-slow) var(--ease-ios), background var(--dur-fast);
}
.cmd-search:focus-within {
    width: 340px;
    background: rgba(255,255,255,0.18);
}
.cmd-search i {
    color: rgba(255,255,255,0.55);
    font-size: 14px;
    flex-shrink: 0;
}
.cmd-search input {
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    font-family: var(--f-system);
    width: 100%;
}
.cmd-search input::placeholder {
    color: rgba(255,255,255,0.45);
}
.cmd-kbd {
    background: rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.7);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 700;
    font-family: var(--f-mono);
}
.cmd-divider {
    width: 0.5px;
    height: 22px;
    background: rgba(255,255,255,0.15);
    margin: 0 3px;
}
.cmd-cta {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #fff;
    color: #1c1c1e;
    border: none;
    border-radius: var(--r-pill);
    padding: 0 16px;
    height: 38px;
    font-size: 13.5px;
    font-weight: 700;
    font-family: var(--f-system);
    cursor: pointer;
    transition: transform var(--dur-fast), background var(--dur-fast);
    margin-left: 2px;
}
.cmd-cta:hover {
    background: #e5e5ea;
    transform: scale(0.98);
}
.cmd-btn-secondary {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: none;
    border-radius: var(--r-pill);
    padding: 0 14px;
    height: 38px;
    font-size: 13px;
    font-weight: 600;
    font-family: var(--f-system);
    cursor: pointer;
    transition: background var(--dur-fast);
}
.cmd-btn-secondary:hover {
    background: rgba(255,255,255,0.2);
}

@media (max-width: 900px) {
    .stat-row { grid-template-columns: 1fr; }
    .cmd-search { width: 160px; }
    .cmd-search:focus-within { width: 220px; }
    .cmd-kbd { display: none; }
}
</style>

<div class="rep-root">
    <div class="rep-wrap">
        
        <!-- Header -->
        <div class="rep-header">
            <div class="rep-eyebrow">Enterprise Intelligence & Analytics</div>
            <h1 class="rep-title">Reports Hub</h1>
            <p class="rep-desc">Unified enterprise reporting engine for real-time operations, drilldown analytics, financial statements, and executive performance metrics.</p>
        </div>

        <!-- Metric Stat Cards -->
        <div class="stat-row">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <div class="stat-num" id="stat_total_reports"><?= $totalReportsCount ?> Available</div>
                    <div class="stat-lbl">Enterprise Reports</div>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div>
                    <div class="stat-num"><?= $activeCategoriesCount ?> Modules</div>
                    <div class="stat-lbl">Operational Categories</div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <div class="stat-num">Live Subsecond</div>
                    <div class="stat-lbl">Aggregated SQL Engine</div>
                </div>
            </div>
        </div>

        <!-- Filter Pill Shelf -->
        <div class="filter-shelf">
            <div class="filter-chip active" data-cat="all" onclick="selectCategoryFilter('all')">
                <i class="fa-solid fa-grid-2"></i> All Categories
                <span class="chip-count"><?= $totalReportsCount ?></span>
            </div>
            <?php foreach ($categories as $catKey => $catTitle): 
                $count = count($groupedReports[$catKey] ?? []);
                $meta = $categoryMeta[$catKey] ?? ['icon' => 'fa-folder', 'color' => 'blue'];
            ?>
                <div class="filter-chip" data-cat="<?= $catKey ?>" onclick="selectCategoryFilter('<?= $catKey ?>')">
                    <i class="fa-solid <?= $meta['icon'] ?>"></i> <?= htmlspecialchars($catTitle) ?>
                    <span class="chip-count"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
            <button class="filter-reset" onclick="resetFilters()">Reset</button>
            <div class="filter-count">
                Showing <strong id="visible_count"><?= $totalReportsCount ?></strong> reports
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="categories-grid" id="categoriesGrid">
            <?php foreach ($categories as $catKey => $catTitle): 
                $reports = $groupedReports[$catKey] ?? [];
                if (empty($reports)) continue;
                $meta = $categoryMeta[$catKey] ?? ['icon' => 'fa-folder', 'color' => 'blue', 'desc' => ''];
            ?>
                <div class="category-card" data-category="<?= $catKey ?>">
                    <div class="cat-header">
                        <div class="cat-icon-badge <?= $meta['color'] ?>">
                            <i class="fa-solid <?= $meta['icon'] ?>"></i>
                        </div>
                        <div class="cat-header-text">
                            <div class="cat-title">
                                <span><?= htmlspecialchars($catTitle) ?></span>
                                <span class="cat-badge"><?= count($reports) ?></span>
                            </div>
                            <div class="cat-desc"><?= htmlspecialchars($meta['desc']) ?></div>
                        </div>
                    </div>
                    <div class="reports-list">
                        <?php foreach ($reports as $key => $rep): 
                            $isCustom = !empty($rep['custom_render']);
                        ?>
                            <a href="<?= APP_URL ?>/report/viewer/<?= $key ?>" class="report-item" data-title="<?= strtolower(htmlspecialchars($rep['title'])) ?>" data-key="<?= $key ?>">
                                <div class="rep-info">
                                    <div class="rep-name"><?= htmlspecialchars($rep['title']) ?></div>
                                    <div class="rep-meta">
                                        <span class="rep-tag realtime"><i class="fa-solid fa-circle" style="font-size:6px; margin-right:4px;"></i>Live</span>
                                        <?php if ($isCustom): ?>
                                            <span class="rep-tag drilldown">Custom View</span>
                                        <?php else: ?>
                                            <span class="rep-tag">Server-side</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right rep-arrow"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Empty Search State -->
            <div class="rep-empty" id="emptySearchState">
                <div class="rep-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <div class="rep-empty-title">No matching reports found</div>
                <div class="rep-empty-desc">We couldn't find any report matching your search query. Try adjusting your keyword or clearing filters.</div>
                <button class="filter-reset" onclick="resetFilters()">Clear Filters & Search</button>
            </div>
        </div>

    </div>
</div>

<!-- Floating Command Bar (Dynamic Island) -->
<div class="cmd-bar">
    <div class="cmd-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="reportSearch" placeholder="Search reports..." autocomplete="off">
        <span class="cmd-kbd">/</span>
    </div>
    <div class="cmd-divider"></div>
    <button class="cmd-btn-secondary" onclick="resetFilters()">
        <i class="fa-solid fa-arrow-rotate-left"></i> Reset
    </button>
    <button class="cmd-cta" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fa-solid fa-arrow-up"></i> Top
    </button>
</div>

<script>
    let activeCategory = 'all';

    const searchInput = document.getElementById('reportSearch');
    const categoriesGrid = document.getElementById('categoriesGrid');
    const emptyState = document.getElementById('emptySearchState');
    const visibleCountEl = document.getElementById('visible_count');

    // Live search listener
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            filterReports();
        });
    }

    // Keyboard shortcut '/' or 'Ctrl+K' / 'Cmd+K' to focus search
    document.addEventListener('keydown', (e) => {
        if ((e.key === '/' && document.activeElement !== searchInput && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') ||
            ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k')) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.blur();
        }
    });

    function selectCategoryFilter(cat) {
        activeCategory = cat;
        document.querySelectorAll('.filter-chip').forEach(chip => {
            if (chip.getAttribute('data-cat') === cat) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });
        filterReports();
    }

    function filterReports() {
        const query = (searchInput.value || '').trim().toLowerCase();
        let totalVisibleReports = 0;
        let visibleCardsCount = 0;

        const cards = document.querySelectorAll('.category-card');
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            const matchesCategory = (activeCategory === 'all' || activeCategory === cardCat);

            const items = card.querySelectorAll('.report-item');
            let visibleInCard = 0;

            items.forEach(item => {
                const title = item.getAttribute('data-title') || '';
                const key = item.getAttribute('data-key') || '';
                const matchesQuery = !query || title.includes(query) || key.includes(query);

                if (matchesCategory && matchesQuery) {
                    item.style.display = 'flex';
                    visibleInCard++;
                    totalVisibleReports++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (visibleInCard > 0) {
                card.style.display = 'flex';
                visibleCardsCount++;
                const countBadge = card.querySelector('.cat-badge');
                if (countBadge) {
                    countBadge.innerText = visibleInCard;
                }
            } else {
                card.style.display = 'none';
            }
        });

        visibleCountEl.innerText = totalVisibleReports;

        if (totalVisibleReports === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }
    }

    function resetFilters() {
        if (searchInput) {
            searchInput.value = '';
        }
        selectCategoryFilter('all');
    }
</script>
