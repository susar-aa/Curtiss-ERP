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
   SF PRO + APPLE DESIGN LANGUAGE - REPORTS HUB (SIDE PANEL)
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
    --dur-slow:    0.42s;
}

body {
    margin: 0;
    padding: 0;
    background: var(--c-bg);
}

.rep-root {
    font-family: var(--f-system);
    font-size: 15px;
    color: var(--t-primary);
    -webkit-font-smoothing: antialiased;
    height: calc(100vh - 60px); /* Adjust based on global header if any */
    display: flex;
    overflow: hidden;
}

/* Sidebar */
.rep-sidebar {
    width: 280px;
    background: var(--c-surface);
    border-right: 0.5px solid var(--c-separator);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    z-index: 10;
}

.rep-sidebar-header {
    padding: 24px 20px 16px;
    border-bottom: 0.5px solid var(--c-separator2);
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
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--t-primary);
    margin: 0 0 16px 0;
}

.cmd-search {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--c-fill);
    border-radius: var(--r-md);
    padding: 10px 12px;
    transition: background var(--dur-fast), box-shadow var(--dur-fast);
}
.cmd-search:focus-within {
    background: var(--c-surface);
    box-shadow: 0 0 0 2px var(--c-blue-mid);
}
.cmd-search i {
    color: var(--t-tertiary);
    font-size: 14px;
}
.cmd-search input {
    background: transparent;
    border: none;
    outline: none;
    color: var(--t-primary);
    font-size: 14px;
    font-weight: 500;
    font-family: var(--f-system);
    width: 100%;
}
.cmd-search input::placeholder {
    color: var(--t-label);
}
.cmd-kbd {
    background: var(--c-surface);
    color: var(--t-secondary);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 700;
    font-family: var(--f-mono);
    border: 0.5px solid var(--c-separator);
}

.rep-nav {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
}

.nav-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: var(--r-sm);
    color: var(--t-secondary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--dur-fast);
    margin-bottom: 2px;
}
.nav-item-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.nav-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    background: var(--c-surface2);
    color: var(--t-tertiary);
    transition: all var(--dur-fast);
}
.nav-item:hover {
    background: var(--c-fill);
    color: var(--t-primary);
}
.nav-item:hover .nav-icon {
    background: #fff;
    color: var(--t-primary);
    box-shadow: var(--shadow-xs);
}

.nav-item.active {
    background: var(--c-blue);
    color: #fff;
}
.nav-item.active .nav-icon {
    background: rgba(255,255,255,0.2);
    color: #fff;
}

.nav-count {
    font-size: 11px;
    font-weight: 700;
    background: var(--c-fill2);
    padding: 2px 8px;
    border-radius: var(--r-pill);
    color: inherit;
}

.nav-item.active .nav-count {
    background: rgba(255,255,255,0.25);
}

/* Main Content */
.rep-main {
    flex: 1;
    overflow-y: auto;
    padding: 32px 40px;
    background: var(--c-bg);
}

.rep-main-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.rep-main-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--t-primary);
    margin: 0;
}
.rep-main-meta {
    font-size: 13px;
    color: var(--t-secondary);
    font-weight: 500;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.category-card {
    background: var(--c-surface);
    border-radius: var(--r-lg);
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
    font-size: 15px;
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
    transition: background var(--dur-fast);
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

.rep-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding-right: 12px;
}
.rep-name {
    font-size: 13.5px;
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
    font-size: 10px;
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
    flex-shrink: 0;
}

/* Empty Search State */
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
.filter-reset {
    background: var(--c-surface);
    border: 0.5px solid var(--c-separator);
    border-radius: var(--r-pill);
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--t-primary);
    cursor: pointer;
}
.filter-reset:hover {
    background: var(--c-fill);
}

@media (max-width: 900px) {
    .rep-root {
        flex-direction: column;
    }
    .rep-sidebar {
        width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 0.5px solid var(--c-separator);
    }
    .rep-nav {
        display: flex;
        overflow-x: auto;
        padding: 10px;
    }
    .nav-item {
        flex-direction: column;
        min-width: 80px;
        text-align: center;
        gap: 6px;
        padding: 8px;
    }
    .nav-count {
        display: none;
    }
}
</style>

<div class="rep-root">
    
    <!-- Sidebar Panel -->
    <div class="rep-sidebar">
        <div class="rep-sidebar-header">
            <div class="rep-eyebrow">Enterprise Intelligence</div>
            <h1 class="rep-title">Reports Hub</h1>
            <div class="cmd-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="reportSearch" placeholder="Find reports..." autocomplete="off">
                <span class="cmd-kbd">/</span>
            </div>
        </div>
        <div class="rep-nav">
            <div class="nav-item active" data-cat="all" onclick="selectCategory('all', 'All Reports')">
                <div class="nav-item-left">
                    <div class="nav-icon"><i class="fa-solid fa-grid-2"></i></div>
                    <span>All Reports</span>
                </div>
                <span class="nav-count"><?= $totalReportsCount ?></span>
            </div>
            
            <?php foreach ($categories as $catKey => $catTitle): 
                $count = count($groupedReports[$catKey] ?? []);
                $meta = $categoryMeta[$catKey] ?? ['icon' => 'fa-folder', 'color' => 'blue'];
            ?>
                <div class="nav-item" data-cat="<?= $catKey ?>" onclick="selectCategory('<?= $catKey ?>', '<?= htmlspecialchars($catTitle) ?>')">
                    <div class="nav-item-left">
                        <div class="nav-icon"><i class="fa-solid <?= $meta['icon'] ?>"></i></div>
                        <span><?= htmlspecialchars($catTitle) ?></span>
                    </div>
                    <span class="nav-count"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content Panel -->
    <div class="rep-main" id="mainContent">
        <div class="rep-main-header">
            <h2 class="rep-main-title" id="mainTitle">All Reports</h2>
            <div class="rep-main-meta">
                Showing <strong id="visibleCount"><?= $totalReportsCount ?></strong> reports
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
                                            <span class="rep-tag drilldown">Custom</span>
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
                <div class="rep-empty-title">No reports found</div>
                <div class="rep-empty-desc">We couldn't find any report matching your search query in this category.</div>
                <button class="filter-reset" onclick="resetFilters()">Clear Filters & Search</button>
            </div>
        </div>
    </div>
</div>

<script>
    let activeCategory = 'all';

    const searchInput = document.getElementById('reportSearch');
    const categoriesGrid = document.getElementById('categoriesGrid');
    const emptyState = document.getElementById('emptySearchState');
    const visibleCountEl = document.getElementById('visibleCount');
    const mainTitle = document.getElementById('mainTitle');
    const mainContent = document.getElementById('mainContent');

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            filterReports();
        });
    }

    document.addEventListener('keydown', (e) => {
        if ((e.key === '/' && document.activeElement !== searchInput && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') ||
            ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k')) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.blur();
            searchInput.value = '';
            filterReports();
        }
    });

    function selectCategory(catKey, catTitle) {
        activeCategory = catKey;
        mainTitle.innerText = catTitle;
        
        document.querySelectorAll('.nav-item').forEach(item => {
            if (item.getAttribute('data-cat') === catKey) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Scroll to top of main content
        mainContent.scrollTop = 0;
        
        filterReports();
    }

    function filterReports() {
        const query = (searchInput.value || '').trim().toLowerCase();
        let totalVisibleReports = 0;

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
                const countBadge = card.querySelector('.cat-badge');
                if (countBadge) countBadge.innerText = visibleInCard;
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
        if (searchInput) searchInput.value = '';
        selectCategory('all', 'All Reports');
    }
</script>
