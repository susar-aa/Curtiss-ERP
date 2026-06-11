<?php
// ==========================================
// VARIABLE SAFETY & ROBUST FALLBACK ENGINE
// ==========================================

$items = $data['items'] ?? [];

$stats = $data['stats'] ?? (object)[
    'total_items' => count($items),
    'low_stock_count' => 0,
    'out_of_stock_count' => 0
];

$filters = $data['filters'] ?? [];
$filters['search'] = $filters['search'] ?? '';
$filters['min_price'] = $filters['min_price'] ?? '';
$filters['max_price'] = $filters['max_price'] ?? '';
$filters['stock_status'] = $filters['stock_status'] ?? '';
$filters['category_id'] = $filters['category_id'] ?? '';
$categories = $data['categories'] ?? [];
$isCurrentlyAdmin = (strtolower($_SESSION['role'] ?? '') === 'admin');

$pagination = $data['pagination'] ?? [
    'current_page' => 1,
    'per_page' => 15,
    'total_items' => count($items),
    'total_pages' => 1
];

$currentPage = (int)$pagination['current_page'];
$perPage = (int)$pagination['per_page'];
$totalItems = (int)$pagination['total_items'];
$totalPages = (int)$pagination['total_pages'];

$startIndex = $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$endIndex = min($currentPage * $perPage, $totalItems);

$flashSuccess = $_SESSION['flash_success'] ?? null;
if ($flashSuccess) unset($_SESSION['flash_success']);
$flashError = $_SESSION['flash_error'] ?? null;
if ($flashError) unset($_SESSION['flash_error']);
$importResults = $_SESSION['import_results'] ?? null;
if ($importResults) unset($_SESSION['import_results']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Catalog — Curtiss ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', '"Helvetica Neue"', 'Arial', 'sans-serif'],
                        display: ['"SF Pro Display"', '-apple-system', 'BlinkMacSystemFont', '"Helvetica Neue"', 'sans-serif'],
                        mono: ['"SF Mono"', '"Fira Code"', '"Cascadia Code"', 'monospace'],
                    },
                    colors: {
                        blue: {
                            50: '#f0f5ff',
                            100: '#e0eaff',
                            500: '#0066cc',
                            600: '#0055b3',
                            700: '#004499',
                        },
                        purple: {
                            50: '#f5f0ff',
                            100: '#ede5ff',
                            600: '#6e4de0',
                            700: '#5c3ec4',
                            900: '#2d1f62',
                        }
                    },
                    letterSpacing: {
                        'sf': '-0.01em',
                        'sf-tight': '-0.02em',
                        'sf-wide': '0.01em',
                    }
                }
            }
        }
    </script>
    <style>
        /* ─── SF Pro System Font Stack ─── */
        body { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", Arial, sans-serif; }

        /* ─── Typography Scale ─── */
        .sf-largetitle { font-size: 34px; font-weight: 700; letter-spacing: -0.021em; line-height: 1.2; }
        .sf-title1     { font-size: 28px; font-weight: 700; letter-spacing: -0.021em; line-height: 1.22; }
        .sf-title2     { font-size: 22px; font-weight: 700; letter-spacing: -0.018em; line-height: 1.27; }
        .sf-title3     { font-size: 20px; font-weight: 600; letter-spacing: -0.014em; line-height: 1.3; }
        .sf-headline   { font-size: 17px; font-weight: 600; letter-spacing: -0.01em; line-height: 1.41; }
        .sf-body       { font-size: 15px; font-weight: 400; letter-spacing: -0.006em; line-height: 1.6; }
        .sf-callout    { font-size: 16px; font-weight: 400; letter-spacing: -0.01em; line-height: 1.5; }
        .sf-subhead    { font-size: 13px; font-weight: 400; letter-spacing: -0.003em; line-height: 1.54; }
        .sf-footnote   { font-size: 13px; font-weight: 400; letter-spacing: 0; line-height: 1.38; }
        .sf-caption1   { font-size: 12px; font-weight: 400; letter-spacing: 0; line-height: 1.33; }
        .sf-caption2   { font-size: 11px; font-weight: 400; letter-spacing: 0.06em; line-height: 1.18; }
        .sf-label-caps { font-size: 11px; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase; }

        /* ─── Scrollbar ─── */
        .sf-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .sf-scroll::-webkit-scrollbar-track { background: #f5f5f7; }
        .sf-scroll::-webkit-scrollbar-thumb { background: #c7c7cc; border-radius: 4px; }

        html, body { overflow-y: auto !important; overflow-x: hidden !important; height: auto !important; min-height: 100vh !important; }
        .group:hover > div, .group:hover > ul { display: block !important; opacity: 1 !important; visibility: visible !important; }
        .group > div::before, .group > ul::before { content: ''; position: absolute; top: -24px; left: 0; right: 0; height: 24px; background: transparent !important; z-index: 10; }
        nav, header { position: relative; z-index: 40 !important; }

        /* ─── Table row hover ─── */
        .sf-table-row:hover { background: rgba(0,0,0,0.02); }

        /* ─── Stat card ─── */
        .sf-stat-card { background: #ffffff; border: 1px solid #e5e5ea; border-radius: 16px; transition: box-shadow 0.2s; }
        .sf-stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); }

        /* ─── Button styles ─── */
        .sf-btn-primary { background: #0066cc; color: #fff; font-size: 14px; font-weight: 500; border-radius: 10px; padding: 8px 18px; border: none; cursor: pointer; transition: background 0.15s, transform 0.1s; display: inline-flex; align-items: center; gap: 7px; letter-spacing: -0.01em; }
        .sf-btn-primary:hover { background: #0055b3; }
        .sf-btn-primary:active { transform: scale(0.98); }
        .sf-btn-secondary { background: #f5f5f7; color: #1d1d1f; font-size: 14px; font-weight: 500; border-radius: 10px; padding: 8px 18px; border: 1px solid #e5e5ea; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 7px; letter-spacing: -0.01em; }
        .sf-btn-secondary:hover { background: #ebebf0; }
        .sf-btn-green { background: #34c759; color: #fff; font-size: 14px; font-weight: 500; border-radius: 10px; padding: 8px 18px; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 7px; letter-spacing: -0.01em; }
        .sf-btn-green:hover { background: #2db34e; }
        .sf-btn-indigo { background: #5856d6; color: #fff; font-size: 14px; font-weight: 500; border-radius: 10px; padding: 8px 18px; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 7px; letter-spacing: -0.01em; }
        .sf-btn-indigo:hover { background: #4745c0; }

        /* ─── Input ─── */
        .sf-input { background: #f5f5f7; border: 1.5px solid #e5e5ea; border-radius: 10px; padding: 8px 12px; font-size: 14px; color: #1d1d1f; outline: none; transition: all 0.18s; font-family: inherit; width: 100%; box-sizing: border-box; }
        .sf-input:focus { background: #fff; border-color: #0066cc; box-shadow: 0 0 0 3px rgba(0,102,204,0.15); }
        .sf-select { background: #f5f5f7; border: 1.5px solid #e5e5ea; border-radius: 10px; padding: 8px 12px; font-size: 14px; color: #1d1d1f; outline: none; transition: all 0.18s; cursor: pointer; width: 100%; font-family: inherit; }
        .sf-select:focus { background: #fff; border-color: #0066cc; box-shadow: 0 0 0 3px rgba(0,102,204,0.15); }

        /* ─── Badge ─── */
        .sf-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 6px; font-size: 12px; font-weight: 500; padding: 3px 9px; letter-spacing: 0; }
        .sf-badge-green { background: #e6f9ee; color: #1c7b3a; }
        .sf-badge-amber { background: #fff5e0; color: #9a5e00; }
        .sf-badge-red { background: #fff0f0; color: #c0392b; }

        /* ─── Section card ─── */
        .sf-card { background: #fff; border: 1px solid #e5e5ea; border-radius: 16px; overflow: hidden; }
        .sf-filter-card { background: #fff; border: 1px solid #e5e5ea; border-radius: 16px; padding: 20px 24px; }
    </style>
</head>
<body style="background: #f5f5f7; color: #1d1d1f;" class="min-h-screen flex flex-col">

    <?php include '../app/Views/layouts/main.php'; ?>

    <div style="max-width: 1440px;" class="w-full mx-auto px-6 py-8 space-y-6 flex-grow">

        <!-- ─── Page Header ─── -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-5">
            <div>
                <p class="sf-label-caps" style="color: #6e6e73; margin-bottom: 4px;">Inventory Management</p>
                <h1 class="sf-largetitle" style="color: #1d1d1f; margin: 0;">Product Catalog</h1>
                <p class="sf-subhead" style="color: #6e6e73; margin-top: 4px;">Track stock levels, manage pricing, and administer catalog entries.</p>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <a href="<?php echo APP_URL; ?>/inventory/exportCSV" class="sf-btn-indigo" style="text-decoration:none;">
                    <i class="fa-solid fa-file-export" style="font-size:13px;"></i> Export Catalog
                </a>
                <button onclick="openCsvModal()" class="sf-btn-green">
                    <i class="fa-solid fa-file-import" style="font-size:13px;"></i> Import CSV
                </button>
                <a href="<?php echo APP_URL; ?>/inventory/add" class="sf-btn-primary" style="text-decoration:none;">
                    <i class="fa-solid fa-plus" style="font-size:13px;"></i> Add Product
                </a>
            </div>
        </div>

        <!-- ─── Flash Alerts ─── -->
        <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
            <div id="flash-success-alert" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:14px 18px; display:flex; align-items:flex-start; gap:12px;">
                <div style="background:#dcfce7; color:#16a34a; border-radius:8px; padding:6px; flex-shrink:0;"><i class="fa-solid fa-check text-sm"></i></div>
                <div>
                    <p class="sf-headline" style="color:#15803d; margin:0 0 2px;">Success</p>
                    <p class="sf-footnote" style="color:#16a34a; margin:0;"><?php echo htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? ''); ?></p>
                </div>
                <button onclick="document.getElementById('flash-success-alert').style.display='none'" style="margin-left:auto; background:none; border:none; cursor:pointer; color:#86efac;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div id="flash-error-alert" style="background:#fff5f5; border:1px solid #fecaca; border-radius:12px; padding:14px 18px; display:flex; align-items:flex-start; gap:12px;">
                <div style="background:#fee2e2; color:#dc2626; border-radius:8px; padding:6px; flex-shrink:0;"><i class="fa-solid fa-circle-exclamation text-sm"></i></div>
                <div>
                    <p class="sf-headline" style="color:#b91c1c; margin:0 0 2px;">Error</p>
                    <p class="sf-footnote" style="color:#dc2626; margin:0;"><?php echo htmlspecialchars($flashError); ?></p>
                </div>
                <button onclick="document.getElementById('flash-error-alert').style.display='none'" style="margin-left:auto; background:none; border:none; cursor:pointer; color:#fca5a5;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- ─── Import Results Panel ─── -->
        <?php if ($importResults): ?>
        <div id="import-results-panel" class="sf-card" style="padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f2f2f7;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="background:#f0fdf4; color:#16a34a; border-radius:10px; padding:8px 10px;"><i class="fa-solid fa-file-circle-check"></i></div>
                    <div>
                        <p class="sf-headline" style="margin:0; color:#1d1d1f;">CSV Import Complete</p>
                        <p class="sf-caption1" style="margin:2px 0 0; color:#6e6e73;">All records from your inventory CSV have been processed.</p>
                    </div>
                </div>
                <button onclick="document.getElementById('import-results-panel').style.display='none'" style="background:none; border:none; cursor:pointer; color:#8e8e93;"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:16px;">
                <div style="background:#f0fdf4; border:1px solid #d1fae5; border-radius:12px; padding:14px; text-align:center;">
                    <p class="sf-caption2" style="color:#6e6e73; margin:0 0 4px;">New Products</p>
                    <p style="font-size:22px; font-weight:700; color:#16a34a; font-family:SF Mono,monospace; margin:0;"><?= $importResults['added']; ?></p>
                </div>
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:14px; text-align:center;">
                    <p class="sf-caption2" style="color:#6e6e73; margin:0 0 4px;">Updated</p>
                    <p style="font-size:22px; font-weight:700; color:#2563eb; font-family:SF Mono,monospace; margin:0;"><?= $importResults['updated']; ?></p>
                </div>
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:14px; text-align:center;">
                    <p class="sf-caption2" style="color:#6e6e73; margin:0 0 4px;">Relations</p>
                    <p style="font-size:22px; font-weight:700; color:#d97706; font-family:SF Mono,monospace; margin:0;"><?= count($importResults['success_logs']); ?></p>
                </div>
                <div style="background:#fff5f5; border:1px solid #fecaca; border-radius:12px; padding:14px; text-align:center;">
                    <p class="sf-caption2" style="color:#6e6e73; margin:0 0 4px;">Errors</p>
                    <p style="font-size:22px; font-weight:700; color:#dc2626; font-family:SF Mono,monospace; margin:0;"><?= count($importResults['errors']); ?></p>
                </div>
            </div>
            <?php if (!empty($importResults['errors'])): ?>
            <div style="background:#fff5f5; border:1px solid #fecaca; border-radius:12px; padding:16px;">
                <p class="sf-label-caps" style="color:#b91c1c; margin:0 0 10px; display:flex; align-items:center; gap:6px;"><i class="fa-solid fa-triangle-exclamation"></i> Validation Errors</p>
                <div class="sf-scroll" style="max-height:280px; overflow-y:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr style="background:#fef2f2; position:sticky; top:0;">
                                <th style="padding:10px 14px; text-align:left; color:#b91c1c; font-weight:600; width:50px;">Row</th>
                                <th style="padding:10px 14px; text-align:left; color:#b91c1c; font-weight:600; width:130px;">SKU</th>
                                <th style="padding:10px 14px; text-align:left; color:#b91c1c; font-weight:600;">Product Name</th>
                                <th style="padding:10px 14px; text-align:left; color:#b91c1c; font-weight:600;">Issues</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($importResults['errors'] as $err): ?>
                            <?php if (is_array($err)): ?>
                            <tr style="border-top:1px solid #fee2e2;">
                                <td style="padding:8px 14px; color:#dc2626; font-family:monospace;"><?= htmlspecialchars($err['row'] ?? '-'); ?></td>
                                <td style="padding:8px 14px; color:#374151; font-family:monospace;"><?= htmlspecialchars($err['sku'] ?? '-'); ?></td>
                                <td style="padding:8px 14px; color:#374151; font-weight:500;"><?= htmlspecialchars($err['name'] ?? '-'); ?></td>
                                <td style="padding:8px 14px; color:#dc2626;"><?= implode(', ', array_map('htmlspecialchars', $err['messages'] ?? [])); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr style="border-top:1px solid #fee2e2;">
                                <td colspan="4" style="padding:8px 14px; color:#dc2626;"><?= htmlspecialchars($err); ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ─── Stats Row ─── -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
            <div class="sf-stat-card" style="padding:22px 24px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <p class="sf-label-caps" style="color:#6e6e73; margin:0 0 6px;">Total Catalog Items</p>
                        <p style="font-size:34px; font-weight:700; color:#1d1d1f; margin:0; letter-spacing:-0.021em; font-family:-apple-system,BlinkMacSystemFont,sans-serif;"><?php echo number_format($stats->total_items); ?></p>
                    </div>
                    <div style="background:#e8f0fe; color:#0066cc; border-radius:12px; padding:10px 12px; font-size:20px;">
                        <i class="fa-solid fa-cubes"></i>
                    </div>
                </div>
                <p class="sf-caption1" style="color:#0066cc; margin:12px 0 0; display:flex; align-items:center; gap:5px; font-weight:500;">
                    <i class="fa-solid fa-check-double" style="font-size:11px;"></i> Complete SKU records on file
                </p>
            </div>
            <div class="sf-stat-card" style="padding:22px 24px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <p class="sf-label-caps" style="color:#6e6e73; margin:0 0 6px;">Low Stock Alerts</p>
                        <p style="font-size:34px; font-weight:700; color:#d97706; margin:0; letter-spacing:-0.021em; font-family:-apple-system,BlinkMacSystemFont,sans-serif;"><?php echo number_format($stats->low_stock_count); ?></p>
                    </div>
                    <div style="background:#fffbeb; color:#d97706; border-radius:12px; padding:10px 12px; font-size:20px;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                </div>
                <p class="sf-caption1" style="color:#d97706; margin:12px 0 0; display:flex; align-items:center; gap:5px; font-weight:500;">
                    <i class="fa-solid fa-box-open" style="font-size:11px;"></i> Items below reorder threshold
                </p>
            </div>
            <div class="sf-stat-card" style="padding:22px 24px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <p class="sf-label-caps" style="color:#6e6e73; margin:0 0 6px;">Out of Stock</p>
                        <p style="font-size:34px; font-weight:700; color:#dc2626; margin:0; letter-spacing:-0.021em; font-family:-apple-system,BlinkMacSystemFont,sans-serif;"><?php echo number_format($stats->out_of_stock_count); ?></p>
                    </div>
                    <div style="background:#fff5f5; color:#dc2626; border-radius:12px; padding:10px 12px; font-size:20px;">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                </div>
                <p class="sf-caption1" style="color:#dc2626; margin:12px 0 0; display:flex; align-items:center; gap:5px; font-weight:500;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:11px;"></i> Zero stock — needs replenishment
                </p>
            </div>
        </div>

        <!-- ─── Filters ─── -->
        <div class="sf-filter-card">
            <p class="sf-label-caps" style="color:#6e6e73; margin:0 0 16px; display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-sliders" style="color:#0066cc;"></i> Filter &amp; Search
            </p>
            <form id="filterForm" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr; gap:12px; align-items:end;">
                <div>
                    <label class="sf-label-caps" style="color:#6e6e73; display:block; margin-bottom:6px;">Search</label>
                    <div style="position:relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#8e8e93; font-size:12px;"></i>
                        <input type="text" id="searchInput" name="search" class="sf-input" style="padding-left:32px;" placeholder="Name, SKU, barcode…" value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>
                </div>
                <div>
                    <label class="sf-label-caps" style="color:#6e6e73; display:block; margin-bottom:6px;">Category</label>
                    <select name="category_id" class="sf-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>" <?php echo $filters['category_id'] == $cat->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="sf-label-caps" style="color:#6e6e73; display:block; margin-bottom:6px;">Min Price (Rs.)</label>
                    <input type="number" step="0.01" name="min_price" class="sf-input" placeholder="0.00" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                </div>
                <div>
                    <label class="sf-label-caps" style="color:#6e6e73; display:block; margin-bottom:6px;">Max Price (Rs.)</label>
                    <input type="number" step="0.01" name="max_price" class="sf-input" placeholder="Any" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                </div>
                <div>
                    <label class="sf-label-caps" style="color:#6e6e73; display:block; margin-bottom:6px;">Stock Status</label>
                    <select name="stock_status" class="sf-select">
                        <option value="">All Status</option>
                        <option value="instock" <?php echo $filters['stock_status'] === 'instock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="lowstock" <?php echo $filters['stock_status'] === 'lowstock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="outstock" <?php echo $filters['stock_status'] === 'outstock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
            </form>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding-top:14px; border-top:1px solid #f2f2f7;">
                <p class="sf-footnote" style="color:#8e8e93; margin:0;">
                    Query result: <strong id="matching-count" style="color:#0066cc; font-variant-numeric:tabular-nums;"><?php echo $totalItems; ?></strong> items
                </p>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="clearAllFilters()" class="sf-btn-secondary" style="font-size:13px; padding:7px 14px;">
                        <i class="fa-solid fa-xmark" style="font-size:11px;"></i> Clear
                    </button>
                    <button type="button" onclick="applyAjaxFilters()" class="sf-btn-primary" style="font-size:13px; padding:7px 14px;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size:11px;"></i> Search
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── Table Card ─── -->
        <div class="sf-card" id="table-wrapper" style="position:relative;">

            <!-- Loader Overlay -->
            <div id="table-loader" style="position:absolute; inset:0; background:rgba(255,255,255,0.7); backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center; z-index:10; opacity:0; pointer-events:none; transition:opacity 0.15s;">
                <div style="text-align:center;">
                    <svg style="animation:spin 1s linear infinite; width:28px; height:28px; color:#0066cc;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="sf-label-caps" style="color:#8e8e93; margin:8px 0 0;">Loading…</p>
                </div>
            </div>
            <style>@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>

            <div id="table-container">
                <div class="sf-scroll" style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; text-align:left; white-space:nowrap;">
                        <thead>
                            <tr style="background:#f5f5f7; border-bottom:1px solid #e5e5ea;">
                                <th style="padding:13px 16px; text-align:center; width:44px;">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="width:15px; height:15px; cursor:pointer; accent-color:#0066cc;">
                                </th>
                                <th style="padding:13px 16px; width:64px;"><span class="sf-label-caps" style="color:#6e6e73;">Image</span></th>
                                <th style="padding:13px 16px;"><span class="sf-label-caps" style="color:#6e6e73;">SKU / Code</span></th>
                                <th style="padding:13px 16px;"><span class="sf-label-caps" style="color:#6e6e73;">Sample</span></th>
                                <th style="padding:13px 16px;"><span class="sf-label-caps" style="color:#6e6e73;">Product</span></th>
                                <th style="padding:13px 16px; text-align:right;"><span class="sf-label-caps" style="color:#6e6e73;">Retail (Rs.)</span></th>
                                <th style="padding:13px 16px; text-align:right;"><span class="sf-label-caps" style="color:#5856d6;">B2B Base</span></th>
                                <th style="padding:13px 16px; text-align:center;"><span class="sf-label-caps" style="color:#6e6e73;">Stock</span></th>
                                <th style="padding:13px 16px; text-align:center;"><span class="sf-label-caps" style="color:#6e6e73;">Status</span></th>
                                <th style="padding:13px 16px; text-align:right;"><span class="sf-label-caps" style="color:#6e6e73;">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="10" style="padding:60px 16px; text-align:center;">
                                        <div style="display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:50%; background:#f5f5f7; margin-bottom:14px;">
                                            <i class="fa-solid fa-box-open" style="font-size:22px; color:#8e8e93;"></i>
                                        </div>
                                        <p class="sf-headline" style="color:#1d1d1f; margin:0 0 4px;">No products found</p>
                                        <p class="sf-subhead" style="color:#8e8e93; margin:0;">Adjust your filters or add a new product to get started.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $qty = intval($item->qty ?? 0);
                                    $price = floatval($item->selling_price ?? $item->price ?? 0);
                                    $b2b_price = floatval($item->wholesale_price ?? 0);
                                    $sku = !empty($item->item_code) ? $item->item_code : ($item->sku ?? '-');
                                    $image = $item->image_path ?? '';
                                    $img_src = empty($image) ? 'https://placehold.co/120?text=No+Image' : APP_URL . '/uploads/products/' . basename($image);

                                    if ($qty <= 0) {
                                        $statusBadge = '<span class="sf-badge sf-badge-red"><span style="width:6px;height:6px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>Out of Stock</span>';
                                    } elseif ($qty <= 5) {
                                        $statusBadge = '<span class="sf-badge sf-badge-amber"><span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;flex-shrink:0;"></span>Low Stock</span>';
                                    } else {
                                        $statusBadge = '<span class="sf-badge sf-badge-green"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;flex-shrink:0;"></span>In Stock</span>';
                                    }
                                    ?>
                                    <tr class="sf-table-row" style="border-bottom:1px solid #f2f2f7; transition:background 0.12s;">
                                        <td style="padding:14px 16px; text-align:center; vertical-align:middle;">
                                            <input type="checkbox" name="selected_items[]" value="<?php echo $item->id; ?>" onchange="updateSelection()" class="item-select-checkbox" style="width:15px; height:15px; cursor:pointer; accent-color:#0066cc;">
                                        </td>
                                        <td style="padding:14px 16px; vertical-align:middle;">
                                            <div style="width:46px; height:46px; border-radius:10px; border:1px solid #e5e5ea; background:#f5f5f7; overflow:hidden;">
                                                <img src="<?php echo $img_src; ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='https://placehold.co/120?text=N/A'">
                                            </div>
                                        </td>
                                        <td style="padding:14px 16px; vertical-align:middle;">
                                            <span style="background:#f5f5f7; border:1px solid #e5e5ea; border-radius:6px; padding:3px 8px; font-family:'SF Mono',monospace; font-size:12px; font-weight:600; color:#3a3a3c; user-select:all;"><?php echo htmlspecialchars($sku); ?></span>
                                        </td>
                                        <td style="padding:14px 16px; vertical-align:middle;">
                                            <span style="font-family:'SF Mono',monospace; font-size:12px; color:#6e6e73;"><?php echo htmlspecialchars($item->sample_code ?? '—'); ?></span>
                                        </td>
                                        <td style="padding:14px 16px; vertical-align:middle; max-width:280px; white-space:normal;">
                                            <p class="sf-body" style="font-weight:600; color:#1d1d1f; margin:0 0 2px; letter-spacing:-0.01em;"><?php echo htmlspecialchars($item->name ?? 'Unnamed Item'); ?></p>
                                            <?php if (!empty($item->description)): ?>
                                                <p class="sf-caption1" style="color:#8e8e93; margin:0; overflow:hidden; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical;"><?php echo htmlspecialchars($item->description); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:14px 16px; text-align:right; vertical-align:middle;">
                                            <p style="font-size:14px; font-weight:700; color:#1d1d1f; margin:0; font-family:'SF Mono',monospace; letter-spacing:-0.01em;"><?php echo number_format($price, 2); ?></p>
                                            <p class="sf-caption2" style="color:#8e8e93; margin:2px 0 0;">LKR</p>
                                        </td>
                                        <td style="padding:14px 16px; text-align:right; vertical-align:middle; background:rgba(88,86,214,0.025);">
                                            <p style="font-size:14px; font-weight:700; color:#5856d6; margin:0; font-family:'SF Mono',monospace;"><?php echo number_format($b2b_price, 2); ?></p>
                                            <p class="sf-caption2" style="color:#5856d6; margin:2px 0 0; opacity:0.7;">WholesaleX</p>
                                        </td>
                                        <td style="padding:14px 16px; text-align:center; vertical-align:middle;">
                                            <span style="font-size:15px; font-weight:700; font-family:'SF Mono',monospace; color:<?php echo $qty <= 0 ? '#dc2626' : ($qty <= 5 ? '#d97706' : '#1d1d1f'); ?>;"><?php echo $qty; ?></span>
                                        </td>
                                        <td style="padding:14px 16px; text-align:center; vertical-align:middle;"><?php echo $statusBadge; ?></td>
                                        <td style="padding:14px 16px; text-align:right; vertical-align:middle;">
                                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:4px;">
                                                <a href="<?php echo APP_URL; ?>/stockledger/product/<?php echo $item->id; ?>" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; color:#8e8e93; text-decoration:none; transition:all 0.12s;" onmouseover="this.style.background='#eff6ff'; this.style.color='#0066cc';" onmouseout="this.style.background='none'; this.style.color='#8e8e93';" title="Stock Ledger">
                                                    <i class="fa-solid fa-chart-line" style="font-size:13px;"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/inventory/edit/<?php echo $item->id; ?>" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; color:#8e8e93; text-decoration:none; transition:all 0.12s;" onmouseover="this.style.background='#eff6ff'; this.style.color='#0066cc';" onmouseout="this.style.background='none'; this.style.color='#8e8e93';" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square" style="font-size:13px;"></i>
                                                </a>
                                                <button type="button" onclick="confirmDelete(<?php echo $item->id; ?>, '<?php echo htmlspecialchars(addslashes($item->name)); ?>')" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; color:#8e8e93; background:none; border:none; cursor:pointer; transition:all 0.12s;" onmouseover="this.style.background='#fff5f5'; this.style.color='#dc2626';" onmouseout="this.style.background='none'; this.style.color='#8e8e93';" title="Delete">
                                                    <i class="fa-solid fa-trash-can" style="font-size:13px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ─── Pagination ─── -->
                <div style="padding:14px 20px; border-top:1px solid #f2f2f7; background:#fafafa; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <p class="sf-footnote" style="color:#6e6e73; margin:0;">
                        Showing <strong style="color:#1d1d1f;"><?php echo $startIndex; ?></strong>–<strong style="color:#1d1d1f;"><?php echo $endIndex; ?></strong> of <strong style="color:#1d1d1f;"><?php echo $totalItems; ?></strong>
                    </p>
                    <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label class="sf-caption2" style="color:#6e6e73;">Per page</label>
                            <select onchange="updatePageSize(this.value)" style="background:#f5f5f7; border:1px solid #e5e5ea; border-radius:8px; padding:5px 10px; font-size:13px; font-family:inherit; outline:none; cursor:pointer;">
                                <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10</option>
                                <option value="15" <?php echo $perPage === 15 ? 'selected' : ''; ?>>15</option>
                                <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        <nav style="display:flex; border:1px solid #e5e5ea; border-radius:10px; overflow:hidden; background:#fff;">
                            <button type="button" onclick="navigatePage(<?php echo max(1, $currentPage - 1); ?>)" style="padding:7px 13px; background:none; border:none; cursor:pointer; color:#6e6e73; font-size:12px; <?php echo $currentPage <= 1 ? 'opacity:0.35; pointer-events:none;' : ''; ?>" onmouseover="this.style.background='#f5f5f7';" onmouseout="this.style.background='none';">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <?php
                            $range = 1;
                            $startPage = max(1, $currentPage - $range);
                            $endPage = min($totalPages, $currentPage + $range);

                            if ($startPage > 1) {
                                echo '<button type="button" onclick="navigatePage(1)" style="padding:7px 13px; background:none; border:none; cursor:pointer; color:#6e6e73; font-size:13px; font-weight:500; border-left:1px solid #f2f2f7;">1</button>';
                                if ($startPage > 2) echo '<span style="padding:7px 8px; color:#8e8e93; font-size:13px; border-left:1px solid #f2f2f7;">…</span>';
                            }
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $active = $i === $currentPage;
                                echo '<button type="button" onclick="navigatePage('.$i.')" style="padding:7px 13px; background:'.($active ? '#0066cc' : 'none').'; color:'.($active ? '#fff' : '#1d1d1f').'; border:none; cursor:pointer; font-size:13px; font-weight:'.($active ? '700' : '500').'; border-left:1px solid #f2f2f7;">'.$i.'</button>';
                            }
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) echo '<span style="padding:7px 8px; color:#8e8e93; font-size:13px; border-left:1px solid #f2f2f7;">…</span>';
                                echo '<button type="button" onclick="navigatePage('.$totalPages.')" style="padding:7px 13px; background:none; border:none; cursor:pointer; color:#6e6e73; font-size:13px; font-weight:500; border-left:1px solid #f2f2f7;">'.$totalPages.'</button>';
                            }
                            ?>
                            <button type="button" onclick="navigatePage(<?php echo min($totalPages, $currentPage + 1); ?>)" style="padding:7px 13px; background:none; border:none; cursor:pointer; color:#6e6e73; font-size:12px; border-left:1px solid #f2f2f7; <?php echo $currentPage >= $totalPages ? 'opacity:0.35; pointer-events:none;' : ''; ?>" onmouseover="this.style.background='#f5f5f7';" onmouseout="this.style.background='none';">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ─── Bulk Toolbar ─── -->
    <div id="bulkEditToolbar" style="position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:rgba(28,28,30,0.92); backdrop-filter:blur(20px) saturate(180%); color:#fff; padding:14px 24px; border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); z-index:40; display:none; align-items:center; gap:24px; min-width:380px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <span id="selectedCountBadge" style="background:#0066cc; border-radius:6px; font-size:13px; font-weight:700; padding:2px 9px; font-family:SF Mono,monospace;">0</span>
            <span class="sf-footnote" style="color:#aeaeb2;">items selected</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px; margin-left:auto;">
            <button type="button" onclick="clearSelection()" style="background:none; border:none; cursor:pointer; color:#aeaeb2; font-size:13px; font-weight:500; padding:6px 12px; border-radius:8px; font-family:inherit;" onmouseover="this.style.background='rgba(255,255,255,0.1)';" onmouseout="this.style.background='none';">Cancel</button>
            <button type="button" onclick="openBulkEditModal()" style="background:#0066cc; border:none; cursor:pointer; color:#fff; font-size:13px; font-weight:500; padding:7px 16px; border-radius:9px; display:flex; align-items:center; gap:7px; font-family:inherit;" onmouseover="this.style.background='#0055b3';" onmouseout="this.style.background='#0066cc';">
                <i class="fa-solid fa-pen-to-square" style="font-size:12px;"></i> Bulk Edit
            </button>
        </div>
    </div>

    <!-- ─── Bulk Edit Modal ─── -->
    <div id="bulkEditModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.4); backdrop-filter:blur(8px); z-index:50; display:none; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#fff; border-radius:18px; box-shadow:0 20px 60px rgba(0,0,0,0.2); max-width:500px; width:100%; overflow:hidden; border:1px solid #e5e5ea;">
            <div style="background:#f5f5f7; border-bottom:1px solid #e5e5ea; padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-pen-to-square" style="color:#5856d6; font-size:18px;"></i>
                    <p class="sf-title3" style="margin:0; color:#1d1d1f;">Bulk Edit Products</p>
                </div>
                <button onclick="closeBulkEditModal()" style="background:none; border:none; cursor:pointer; color:#8e8e93; width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:16px;" onmouseover="this.style.background='#ebebf0';" onmouseout="this.style.background='none';"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="bulkEditForm" onsubmit="submitBulkEdit(event)" style="padding:24px; display:flex; flex-direction:column; gap:16px;">
                <p class="sf-footnote" style="color:#8e8e93; margin:0;">Editing <strong id="bulkSelectedCount" style="color:#5856d6; font-variant-numeric:tabular-nums;">0</strong> products. Check a field to apply that change to all selected items.</p>
                <div id="bulkEditErrorContainer" style="display:none; padding:12px 14px; background:#fff5f5; border:1px solid #fecaca; color:#dc2626; font-size:13px; border-radius:10px;"></div>

                <?php
                $bulkFields = [
                    ['id'=>'category', 'label'=>'Update Category', 'field'=>'category_id'],
                    ['id'=>'selling_price', 'label'=>'Update Retail Price', 'field'=>'selling_price'],
                    ['id'=>'wholesale_price', 'label'=>'Update B2B Base Price', 'field'=>'wholesale_price'],
                    ['id'=>'status', 'label'=>'Update Status', 'field'=>'status'],
                ];
                foreach ($bulkFields as $f):
                ?>
                <div style="border:1px solid #f2f2f7; border-radius:12px; padding:16px; background:#fafafa;">
                    <label style="display:flex; align-items:center; gap:9px; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" name="update_<?= $f['id'] ?>" value="1" id="bulkUpdate<?= ucfirst($f['id']) ?>" onchange="toggleBulkField('<?= $f['id'] ?>')" style="width:15px; height:15px; accent-color:#5856d6; cursor:pointer;">
                        <span class="sf-label-caps" style="color:#1d1d1f;"><?= $f['label'] ?></span>
                    </label>
                    <?php if ($f['id'] === 'category'): ?>
                        <select name="category_id" id="bulkCategorySelect" disabled class="sf-select" style="opacity:0.45;">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($f['id'] === 'status'): ?>
                        <select name="status" id="bulkStatusSelect" disabled class="sf-select" style="opacity:0.45;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    <?php else: ?>
                        <div style="display:flex; gap:10px;">
                            <select name="<?= $f['id'] ?>_type" id="bulk<?= ucfirst(str_replace('_','',ucwords($f['id'],'_'))) ?>Type" disabled class="sf-select" style="width:40%; opacity:0.45;">
                                <option value="flat">Set flat value</option>
                                <option value="pct_inc">Increase by %</option>
                                <option value="pct_dec">Decrease by %</option>
                            </select>
                            <input type="number" step="0.01" name="<?= $f['id'] ?>_val" id="bulk<?= ucfirst(str_replace('_','',ucwords($f['id'],'_'))) ?>Val" disabled class="sf-input" style="opacity:0.45;" placeholder="e.g. 10">
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div style="display:flex; justify-content:flex-end; gap:10px; padding-top:10px; border-top:1px solid #f2f2f7;">
                    <button type="button" onclick="closeBulkEditModal()" class="sf-btn-secondary" style="font-size:13px;">Cancel</button>
                    <button type="submit" id="bulkSubmitBtn" style="background:#5856d6; color:#fff; font-size:14px; font-weight:500; border-radius:10px; padding:8px 20px; border:none; cursor:pointer; display:flex; align-items:center; gap:7px; letter-spacing:-0.01em; font-family:inherit;">
                        <span id="bulkBtnSpinner" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i></span>
                        Apply Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>