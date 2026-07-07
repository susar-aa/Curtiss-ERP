<?php
$categories = $data['categories'];
$groupedReports = $data['grouped_reports'];

// Icons helper mapped to categories for rich visual look
$categoryIcons = [
    'inventory' => 'ph-package',
    'sales' => 'ph-shopping-cart',
    'procurement' => 'ph-truck',
    'customer' => 'ph-users',
    'supplier' => 'ph-storefront',
    'finance' => 'ph-bank',
    'collection' => 'ph-hand-coins',
    'route' => 'ph-map-trifold',
    'management' => 'ph-presentation-chart'
];

// Presentational-only accent colors per category (does not affect data/logic)
$categoryColors = [
    'inventory' => ['bg' => '#e8f1ff', 'fg' => '#0066cc'],
    'sales' => ['bg' => '#e9f9f0', 'fg' => '#0d9f5f'],
    'procurement' => ['bg' => '#fff4e5', 'fg' => '#c8720a'],
    'customer' => ['bg' => '#f2ecff', 'fg' => '#6c3fd6'],
    'supplier' => ['bg' => '#ffe9ee', 'fg' => '#d6336c'],
    'finance' => ['bg' => '#e6f7f8', 'fg' => '#0891a3'],
    'collection' => ['bg' => '#fef6e0', 'fg' => '#b5891a'],
    'route' => ['bg' => '#eafaf5', 'fg' => '#0aa885'],
    'management' => ['bg' => '#eef0ff', 'fg' => '#4650c9'],
];
$defaultColor = ['bg' => '#f1f5f9', 'fg' => '#475569'];

// Total report count across all categories (display only)
$totalReportCount = 0;
foreach ($categories as $catKey => $catTitle) {
    if (!empty($groupedReports[$catKey])) {
        $totalReportCount += count($groupedReports[$catKey]);
    }
}
?>

<style>
    * { box-sizing: border-box; }

    .reports-hub-wrapper {
        max-width: 1500px;
        margin: 0 auto;
        padding: 20px;
    }

    /* ---------- Top bar: title + search ---------- */
    .hub-topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 18px;
    }

    .hub-title h1 {
        font-size: 22px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 4px 0;
        letter-spacing: -0.2px;
    }

    .hub-title p {
        color: #666;
        margin: 0;
        font-size: 13px;
    }

    .search-box-wrapper {
        position: relative;
        width: 300px;
        flex-shrink: 0;
    }

    .search-box-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 16px;
    }

    .search-box-wrapper input {
        width: 100%;
        padding: 9px 10px 9px 36px;
        border: 1px solid #ddd;
        border-radius: 20px;
        font-size: 13px;
        background: #fff;
        transition: all 0.2s;
    }

    .search-box-wrapper input:focus {
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
        outline: none;
    }

    /* ---------- Shell: sidebar + main ---------- */
    .hub-shell {
        display: flex;
        align-items: stretch;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .hub-sidebar {
        width: 250px;
        flex-shrink: 0;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        padding: 14px 10px;
    }

    .sidebar-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        padding: 6px 10px 8px 10px;
    }

    .sidebar-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 10px;
        border: none;
        background: transparent;
        border-radius: 8px;
        cursor: pointer;
        text-align: left;
        font-size: 13.5px;
        font-weight: 550;
        color: #334155;
        margin-bottom: 2px;
        transition: background 0.15s, color 0.15s;
    }

    .sidebar-nav-item:hover {
        background: #eef2f7;
    }

    .sidebar-nav-item.active {
        background: #e8f1ff;
        color: #0066cc;
    }

    .sidebar-icon-badge {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-icon-badge i {
        font-size: 14px;
    }

    .sidebar-nav-item span.nav-label {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-nav-item .nav-count {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        background: rgba(0,0,0,0.05);
        border-radius: 10px;
        padding: 2px 7px;
        flex-shrink: 0;
    }

    .sidebar-nav-item.active .nav-count {
        color: #0066cc;
        background: rgba(0,102,204,0.12);
    }

    /* ---------- Main panel ---------- */
    .hub-main {
        flex: 1;
        min-width: 0;
        padding: 18px 22px 24px 22px;
    }

    .main-header {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .main-header h2 {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .main-header .main-count {
        font-size: 13px;
        color: #94a3b8;
        font-weight: 600;
    }

    /* ---------- Tile grid (uniform height -> no wasted whitespace) ---------- */
    .tiles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 10px;
    }

    .report-tile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        border: 1px solid #eef2f7;
        border-radius: 9px;
        text-decoration: none;
        color: #334155;
        background: #fff;
        transition: border-color 0.15s, background 0.15s, transform 0.1s;
    }

    .report-tile:hover {
        border-color: #b7d3f2;
        background: #f7fafd;
        transform: translateY(-1px);
    }

    .tile-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .tile-icon i {
        font-size: 16px;
    }

    .tile-text {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }

    .tile-title {
        font-size: 13.5px;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tile-category-tag {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 1px;
    }

    .hub-main.single-category .tile-category-tag {
        display: none;
    }

    .tile-arrow {
        font-size: 14px;
        opacity: 0;
        color: #0066cc;
        transition: opacity 0.15s, transform 0.15s;
        flex-shrink: 0;
    }

    .report-tile:hover .tile-arrow {
        opacity: 1;
        transform: translateX(2px);
    }

    .no-results {
        display: none;
        text-align: center;
        padding: 50px 20px;
        color: #94a3b8;
        font-size: 14px;
    }

    .no-results.visible {
        display: block;
    }

    .no-results i {
        font-size: 28px;
        display: block;
        margin-bottom: 8px;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 900px) {
        .hub-shell {
            flex-direction: column;
        }

        .hub-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            overflow-x: auto;
            gap: 4px;
            padding: 10px;
        }

        .sidebar-label {
            display: none;
        }

        .sidebar-nav-item {
            flex-shrink: 0;
            width: auto;
            white-space: nowrap;
            margin-bottom: 0;
        }
    }

    @media (max-width: 640px) {
        .hub-topbar {
            flex-direction: column;
        }
        .search-box-wrapper {
            width: 100%;
        }
        .tiles-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-color-scheme: dark) {
        .hub-shell {
            background: #1a1a1a;
            border-color: #2e2e2e;
        }
        .hub-sidebar {
            background: #141414;
            border-color: #2a2a2a;
        }
        .sidebar-nav-item {
            color: #cbd5e1;
        }
        .sidebar-nav-item:hover {
            background: #232323;
        }
        .sidebar-nav-item.active {
            background: rgba(0,102,204,0.18);
            color: #6fb0f5;
        }
        .main-header {
            border-color: #2a2a2a;
        }
        .main-header h2 {
            color: #f1f5f9;
        }
        .report-tile {
            background: #1e1e1e;
            border-color: #2a2a2a;
            color: #cbd5e1;
        }
        .report-tile:hover {
            background: rgba(0,102,204,0.1);
            border-color: #3a597f;
        }
        .hub-title h1 {
            color: #ffffff;
        }
    }
</style>

<div class="reports-hub-wrapper">
    <div class="hub-topbar">
        <div class="hub-title">
            <h1>Centralized Reporting Hub</h1>
            <p>Unified enterprise reporting engine for real-time operations, analytics, and accounting views.</p>
        </div>
        <div class="search-box-wrapper">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="reportSearch" placeholder="Search reports..." onkeyup="applyFilters()">
        </div>
    </div>

    <div class="hub-shell">
        <!-- Sidebar navigation -->
        <div class="hub-sidebar">
            <div class="sidebar-label">Categories</div>

            <button type="button" class="sidebar-nav-item active" data-category="all" onclick="selectCategory('all', this)">
                <span class="sidebar-icon-badge" style="background:#eef2f7;">
                    <i class="ph ph-squares-four" style="color:#475569;"></i>
                </span>
                <span class="nav-label">All Reports</span>
                <span class="nav-count"><?= $totalReportCount ?></span>
            </button>

            <?php foreach ($categories as $catKey => $catTitle): ?>
                <?php if (!empty($groupedReports[$catKey])): ?>
                    <?php $color = $categoryColors[$catKey] ?? $defaultColor; ?>
                    <button type="button" class="sidebar-nav-item" data-category="<?= $catKey ?>" onclick="selectCategory('<?= $catKey ?>', this)">
                        <span class="sidebar-icon-badge" style="background:<?= $color['bg'] ?>;">
                            <i class="ph <?= $categoryIcons[$catKey] ?? 'ph-file-text' ?>" style="color:<?= $color['fg'] ?>;"></i>
                        </span>
                        <span class="nav-label"><?= htmlspecialchars($catTitle) ?></span>
                        <span class="nav-count"><?= count($groupedReports[$catKey]) ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Main content -->
        <div class="hub-main" id="hubMain">
            <div class="main-header">
                <h2 id="mainTitle">All Reports</h2>
                <span class="main-count" id="mainCount">(<?= $totalReportCount ?>)</span>
            </div>

            <div class="tiles-grid" id="tilesGrid">
                <?php foreach ($categories as $catKey => $catTitle): ?>
                    <?php if (!empty($groupedReports[$catKey])): ?>
                        <?php $color = $categoryColors[$catKey] ?? $defaultColor; ?>
                        <?php foreach ($groupedReports[$catKey] as $key => $rep): ?>
                            <a href="<?= APP_URL ?>/report/viewer/<?= $key ?>"
                               class="report-tile"
                               data-category="<?= $catKey ?>"
                               data-name="<?= strtolower($rep['title']) ?>">
                                <span class="tile-icon" style="background:<?= $color['bg'] ?>;">
                                    <i class="ph <?= $categoryIcons[$catKey] ?? 'ph-file-text' ?>" style="color:<?= $color['fg'] ?>;"></i>
                                </span>
                                <span class="tile-text">
                                    <span class="tile-title"><?= htmlspecialchars($rep['title']) ?></span>
                                    <span class="tile-category-tag"><?= htmlspecialchars($catTitle) ?></span>
                                </span>
                                <i class="ph ph-arrow-square-out tile-arrow"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="no-results" id="noResults">
                <i class="ph ph-magnifying-glass"></i>
                No reports match your search.
            </div>
        </div>
    </div>
</div>

<script>
    let currentCategory = 'all';

    const categoryTitles = {
        all: 'All Reports'
    };
    document.querySelectorAll('.sidebar-nav-item').forEach(btn => {
        const key = btn.getAttribute('data-category');
        const label = btn.querySelector('.nav-label').textContent;
        categoryTitles[key] = label;
    });

    function selectCategory(key, btn) {
        currentCategory = key;

        document.querySelectorAll('.sidebar-nav-item').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        const hubMain = document.getElementById('hubMain');
        hubMain.classList.toggle('single-category', key !== 'all');

        document.getElementById('mainTitle').textContent = categoryTitles[key] || 'Reports';

        applyFilters();
    }

    function applyFilters() {
        const query = document.getElementById('reportSearch').value.toLowerCase();
        const tiles = document.querySelectorAll('.report-tile');
        let visibleCount = 0;

        tiles.forEach(tile => {
            const matchesCategory = currentCategory === 'all' || tile.getAttribute('data-category') === currentCategory;
            const matchesSearch = query.length === 0 || tile.getAttribute('data-name').includes(query);
            const visible = matchesCategory && matchesSearch;

            tile.style.display = visible ? 'flex' : 'none';
            if (visible) visibleCount++;
        });

        document.getElementById('mainCount').textContent = `(${visibleCount})`;
        document.getElementById('noResults').classList.toggle('visible', visibleCount === 0);
    }
</script>