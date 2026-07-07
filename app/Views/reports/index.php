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

// Number of items visible before "show more" collapses the rest (display-only, no data change)
$visibleLimit = 6;
?>

<style>
    .reports-hub-wrapper {
        padding: 20px;
        max-width: 1500px;
        margin: 0 auto;
    }

    /* ---------- Sticky header ---------- */
    .hub-header {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #fff;
        padding: 18px 0 14px 0;
        margin-bottom: 6px;
        border-bottom: 1px solid #e2e8f0;
    }

    .hub-header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 16px;
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

    /* ---------- Quick-jump chip row ---------- */
    .category-chip-row {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: thin;
    }

    .category-chip-row::-webkit-scrollbar {
        height: 5px;
    }
    .category-chip-row::-webkit-scrollbar-thumb {
        background: #dbe3ec;
        border-radius: 10px;
    }

    .category-chip {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, transform 0.1s;
        cursor: pointer;
    }

    .category-chip:hover {
        background: #eef4fc;
        border-color: #b7d3f2;
        transform: translateY(-1px);
    }

    .category-chip i {
        font-size: 14px;
    }

    .category-chip .chip-count {
        background: rgba(0,0,0,0.06);
        border-radius: 10px;
        padding: 1px 6px;
        font-size: 11px;
        font-weight: 700;
    }

    /* ---------- Masonry grid ---------- */
    .categories-grid {
        column-count: 4;
        column-gap: 18px;
        margin-top: 18px;
    }

    @media (max-width: 1400px) {
        .categories-grid { column-count: 3; }
    }
    @media (max-width: 980px) {
        .categories-grid { column-count: 2; }
    }
    @media (max-width: 640px) {
        .categories-grid { column-count: 1; }
        .hub-header-top { flex-direction: column; }
        .search-box-wrapper { width: 100%; }
    }

    .category-section {
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        overflow: hidden;
        margin-bottom: 18px;
        transition: box-shadow 0.2s, border-color 0.2s;
        display: inline-block;
        width: 100%;
    }

    .category-section:hover {
        box-shadow: 0 6px 14px -4px rgba(0,0,0,0.08);
        border-color: #d6e2f0;
    }

    .category-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-icon-badge {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .category-icon-badge i {
        font-size: 16px;
    }

    .category-header h3 {
        font-size: 14.5px;
        font-weight: 650;
        margin: 0;
        color: #1e293b;
        flex: 1;
    }

    .category-header .category-count {
        font-size: 11.5px;
        font-weight: 600;
        color: #94a3b8;
        background: #f8fafc;
        border-radius: 10px;
        padding: 2px 8px;
    }

    .reports-list {
        padding: 4px 0;
    }

    .report-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 16px;
        color: #475569;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
        border-bottom: 1px solid #f8fafc;
        font-size: 13.5px;
    }

    .report-item:last-child {
        border-bottom: none;
    }

    .report-item:hover {
        background: #f0f7ff;
        color: #0066cc;
    }

    .report-item.report-item-extra {
        display: none;
    }

    .category-section.expanded .report-item.report-item-extra {
        display: flex;
    }

    .report-info {
        display: flex;
        flex-direction: column;
    }

    .report-name {
        font-weight: 550;
    }

    .report-item i.launch-icon {
        font-size: 14px;
        opacity: 0;
        transition: opacity 0.15s, transform 0.15s;
    }

    .report-item:hover i.launch-icon {
        opacity: 1;
        transform: translateX(3px);
    }

    .show-more-btn {
        width: 100%;
        text-align: left;
        padding: 9px 16px;
        border: none;
        background: #fafbfc;
        color: #0066cc;
        font-size: 12.5px;
        font-weight: 650;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .show-more-btn:hover {
        background: #f0f7ff;
    }

    .show-more-btn i {
        font-size: 13px;
        transition: transform 0.15s;
    }

    .category-section.expanded .show-more-btn i {
        transform: rotate(180deg);
    }

    .no-results {
        display: none;
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
        font-size: 14px;
    }

    .no-results.visible {
        display: block;
    }

    @media (prefers-color-scheme: dark) {
        .hub-header {
            background: #121212;
            border-color: #2e2e2e;
        }
        .category-section {
            background: #1e1e1e;
            border-color: #2e2e2e;
        }
        .category-header {
            border-color: #2a2a2a;
        }
        .category-header h3 {
            color: #e2e8f0;
        }
        .category-header .category-count {
            background: #262626;
            color: #94a3b8;
        }
        .report-item {
            color: #cbd5e1;
            border-color: #2a2a2a;
        }
        .report-item:hover {
            background: rgba(0, 102, 204, 0.15);
        }
        .show-more-btn {
            background: #202020;
            color: #5b9fe8;
        }
        .show-more-btn:hover {
            background: rgba(0, 102, 204, 0.15);
        }
        .hub-title h1 {
            color: #ffffff;
        }
        .category-chip {
            background: #1e1e1e;
            border-color: #2e2e2e;
            color: #cbd5e1;
        }
        .category-chip:hover {
            background: #23324a;
            border-color: #3a597f;
        }
    }
</style>

<div class="reports-hub-wrapper">
    <div class="hub-header">
        <div class="hub-header-top">
            <div class="hub-title">
                <h1>Centralized Reporting Hub</h1>
                <p>Unified enterprise reporting engine for real-time operations, analytics, and accounting views.</p>
            </div>
            <div class="search-box-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="reportSearch" placeholder="Search reports..." onkeyup="filterReports()">
            </div>
        </div>

        <div class="category-chip-row" id="categoryChipRow">
            <?php foreach ($categories as $catKey => $catTitle): ?>
                <?php if (!empty($groupedReports[$catKey])): ?>
                    <?php $color = $categoryColors[$catKey] ?? $defaultColor; ?>
                    <a href="#cat-<?= $catKey ?>" class="category-chip" onclick="jumpToCategory(event, 'cat-<?= $catKey ?>')">
                        <i class="ph <?= $categoryIcons[$catKey] ?? 'ph-file-text' ?>" style="color: <?= $color['fg'] ?>;"></i>
                        <span><?= htmlspecialchars($catTitle) ?></span>
                        <span class="chip-count"><?= count($groupedReports[$catKey]) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="categories-grid" id="categoriesGrid">
        <?php foreach ($categories as $catKey => $catTitle): ?>
            <?php if (!empty($groupedReports[$catKey])): ?>
                <?php
                    $color = $categoryColors[$catKey] ?? $defaultColor;
                    $reportCount = count($groupedReports[$catKey]);
                ?>
                <div class="category-section" id="cat-<?= $catKey ?>" data-category="<?= $catKey ?>">
                    <div class="category-header">
                        <div class="category-icon-badge" style="background: <?= $color['bg'] ?>;">
                            <i class="ph <?= $categoryIcons[$catKey] ?? 'ph-file-text' ?>" style="color: <?= $color['fg'] ?>;"></i>
                        </div>
                        <h3><?= htmlspecialchars($catTitle) ?></h3>
                        <span class="category-count"><?= $reportCount ?></span>
                    </div>
                    <div class="reports-list">
                        <?php $i = 0; foreach ($groupedReports[$catKey] as $key => $rep): $i++; ?>
                            <a href="<?= APP_URL ?>/report/viewer/<?= $key ?>"
                               class="report-item<?= $i > $visibleLimit ? ' report-item-extra' : '' ?>"
                               data-name="<?= strtolower($rep['title']) ?>">
                                <div class="report-info">
                                    <span class="report-name"><?= htmlspecialchars($rep['title']) ?></span>
                                </div>
                                <i class="ph ph-arrow-square-out launch-icon"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($reportCount > $visibleLimit): ?>
                        <button type="button" class="show-more-btn" onclick="toggleExpand(this)">
                            <span class="show-more-label">Show <?= $reportCount - $visibleLimit ?> more</span>
                            <i class="ph ph-caret-down"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="no-results" id="noResults">
        <i class="ph ph-magnifying-glass" style="font-size: 28px; display:block; margin-bottom: 8px;"></i>
        No reports match your search.
    </div>
</div>

<script>
    function jumpToCategory(event, id) {
        event.preventDefault();
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function toggleExpand(btn) {
        const section = btn.closest('.category-section');
        const isExpanded = section.classList.toggle('expanded');
        const label = btn.querySelector('.show-more-label');
        const extraCount = section.querySelectorAll('.report-item-extra').length;
        label.textContent = isExpanded ? 'Show less' : `Show ${extraCount} more`;
    }

    function filterReports() {
        const query = document.getElementById('reportSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.category-section');
        let anyVisible = false;

        cards.forEach(card => {
            const items = card.querySelectorAll('.report-item');
            let hasVisibleItem = false;

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                const matches = name.includes(query);
                item.style.display = matches ? 'flex' : 'none';
                if (matches) hasVisibleItem = true;
            });

            // While searching, ignore the collapsed state so matches beyond the
            // visible limit are still reachable; restore it once search is cleared.
            if (query.length > 0) {
                card.classList.add('search-active');
            } else {
                card.classList.remove('search-active');
                const expanded = card.classList.contains('expanded');
                card.querySelectorAll('.report-item-extra').forEach(item => {
                    if (!expanded) item.style.display = 'none';
                });
            }

            const showMoreBtn = card.querySelector('.show-more-btn');
            if (showMoreBtn) {
                showMoreBtn.style.display = query.length > 0 ? 'none' : 'flex';
            }

            card.style.display = hasVisibleItem ? 'inline-block' : 'none';
            if (hasVisibleItem) anyVisible = true;
        });

        document.getElementById('noResults').classList.toggle('visible', !anyVisible && query.length > 0);
    }
</script>