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
    <title>Inventory — Curtiss ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6', // Teal
                            600: '#0d9488',
                            700: '#0f766e',
                            900: '#134e4a',
                        },
                        surface: {
                            50: '#fafafa',
                            100: '#f4f4f5',
                            200: '#e4e4e7',
                            300: '#d4d4d8',
                            500: '#71717a',
                            700: '#3f3f46',
                            800: '#27272a',
                            900: '#18181b',
                        }
                    },
                    boxShadow: {
                        'subtle': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                        'float': '0 10px 30px -5px rgba(0, 0, 0, 0.08), 0 4px 10px -5px rgba(0, 0, 0, 0.03)',
                        'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    <style>
        body { background: #fafafa; color: #18181b; }
        
        /* Premium Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d4d4d8; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1aa; }

        /* Smooth UI Transitions */
        .ui-transition { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Inputs & Selects */
        .premium-input {
            width: 100%; background: #fff; border: 1px solid #e4e4e7; border-radius: 8px; 
            padding: 8px 12px; font-size: 13px; color: #27272a; outline: none; 
            transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .premium-input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15); }
        
        .premium-select {
            appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23a1a1aa%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px top 50%;
            background-size: 10px auto; border: 1px solid #e4e4e7; border-radius: 8px;
            padding: 8px 30px 8px 12px; font-size: 13px; color: #27272a; outline: none; cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02); transition: all 0.2s;
        }
        .premium-select:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15); }

        /* Buttons */
        .btn-primary {
            background: #18181b; color: #fff; border: 1px solid #18181b; border-radius: 8px;
            padding: 8px 16px; font-size: 13px; font-weight: 500; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;
        }
        .btn-primary:hover { background: #27272a; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-primary:active { transform: translateY(0); }

        .btn-secondary {
            background: #fff; color: #3f3f46; border: 1px solid #e4e4e7; border-radius: 8px;
            padding: 8px 16px; font-size: 13px; font-weight: 500; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02); transition: all 0.2s;
        }
        .btn-secondary:hover { background: #f4f4f5; border-color: #d4d4d8; color: #18181b; }

        /* Table Design */
        .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .modern-table th { 
            background: #fafafa; color: #71717a; font-size: 12px; font-weight: 650; text-transform: uppercase; 
            letter-spacing: 0.05em; padding: 14px 16px; text-align: left; border-bottom: 1px solid #e4e4e7;
            position: sticky; top: 0; z-index: 10;
        }
        .modern-table td { padding: 14px 16px; border-bottom: 1px solid #f4f4f5; font-size: 13px; vertical-align: middle; }
        .modern-table tr:last-child td { border-bottom: none; }
        .modern-table tbody tr { transition: background-color 0.15s ease; }
        .modern-table tbody tr:hover td { background: rgba(20, 184, 166, 0.015); }

        /* Status Pills */
        .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
        .status-instock { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
        .status-lowstock { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .status-outstock { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        /* Custom Checkbox */
        .custom-checkbox { width: 16px; height: 16px; border-radius: 4px; border: 1px solid #d4d4d8; accent-color: #0d9488; cursor: pointer; }

        /* Glass Panel */
        .glass-panel { background: rgba(255,255,255,0.92); backdrop-filter: blur(16px); border: 1px solid rgba(228, 228, 231, 0.8); border-radius: 16px; }

        /* Checkbox wrapper animation */
        .checkbox-wrapper { display: flex; align-items: center; justify-content: center; }

        /* Grid Background Pattern */
        .bg-grid-pattern {
            background-size: 24px 24px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.015) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(0, 0, 0, 0.015) 1px, transparent 1px);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Main Container Layout: Split View -->
    <div class="flex-grow flex flex-col xl:flex-row h-full overflow-hidden w-full">
        
        <!-- Left Sidebar: Contextual Filters & Insights -->
        <aside class="w-full xl:w-80 flex-shrink-0 bg-white border-r border-surface-200 flex flex-col h-full xl:h-[calc(100vh-64px)] xl:sticky xl:top-0 overflow-y-auto">
            <div class="p-6 border-b border-surface-200 bg-surface-50/50">
                <h1 class="text-xl font-bold text-surface-900 tracking-tight">Inventory System</h1>
                <p class="text-xs text-surface-500 mt-1">Track and manage physical stock catalog.</p>
            </div>

            <!-- Insights (KPI Cards) -->
            <div class="p-6 border-b border-surface-200 space-y-4 bg-surface-50/20">
                <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-2">Inventory Stats</h2>
                
                <!-- Total Products Card -->
                <div class="bg-white border border-surface-200 rounded-xl p-4 ui-transition hover:border-surface-300 hover:shadow-subtle">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-surface-500 font-medium">Total Products</p>
                            <p class="text-2xl font-bold text-surface-900 mt-1" id="stat-total-items"><?php echo number_format($stats->total_items); ?></p>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600">
                            <i class="fa-solid fa-layer-group text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Low Stock and Out of Stock Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white border border-surface-200 rounded-xl p-3.5 ui-transition hover:border-amber-300">
                        <p class="text-[10px] text-amber-600 font-semibold uppercase tracking-wider">Low Stock</p>
                        <p class="text-xl font-bold text-amber-600 mt-1" id="stat-low-stock"><?php echo number_format($stats->low_stock_count); ?></p>
                    </div>
                    <div class="bg-white border border-surface-200 rounded-xl p-3.5 ui-transition hover:border-rose-300">
                        <p class="text-[10px] text-rose-600 font-semibold uppercase tracking-wider">Out of Stock</p>
                        <p class="text-xl font-bold text-rose-600 mt-1" id="stat-out-of-stock"><?php echo number_format($stats->out_of_stock_count); ?></p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 flex-grow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Advanced Filters</h2>
                    <button type="button" onclick="clearAllFilters()" class="text-xs text-brand-600 hover:text-brand-700 font-semibold ui-transition">Clear all</button>
                </div>

                <form id="filterForm" onsubmit="event.preventDefault(); applyAjaxFilters();" class="space-y-4">
                    <!-- Pagination and configuration states -->
                    <input type="hidden" name="page" id="currentPageInput" value="<?php echo $currentPage; ?>">
                    <input type="hidden" name="per_page" id="perPageInput" value="<?php echo $perPage; ?>">
                    
                    <!-- Search query state synced from the floating bar -->
                    <input type="hidden" id="hiddenSearchInput" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>">

                    <div>
                        <label class="block text-xs font-semibold text-surface-700 mb-1.5">Category</label>
                        <select name="category_id" onchange="applyAjaxFilters()" class="premium-select w-full">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->id; ?>" <?php echo $filters['category_id'] == $cat->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-surface-700 mb-1.5">Stock Status</label>
                        <select name="stock_status" onchange="applyAjaxFilters()" class="premium-select w-full">
                            <option value="">Any Status</option>
                            <option value="instock" <?php echo $filters['stock_status'] === 'instock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="lowstock" <?php echo $filters['stock_status'] === 'lowstock' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="outstock" <?php echo $filters['stock_status'] === 'outstock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-surface-700 mb-1.5">Min Price</label>
                            <input type="number" step="0.01" name="min_price" oninput="triggerSearchDelay()" class="premium-input" placeholder="0.00" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-surface-700 mb-1.5">Max Price</label>
                            <input type="number" step="0.01" name="max_price" oninput="triggerSearchDelay()" class="premium-input" placeholder="Any" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full bg-brand-600 hover:bg-brand-700 border-brand-600 text-white mt-4">
                        <i class="fa-solid fa-filter text-xs mr-1"></i> Apply Filters
                    </button>
                    
                    <div class="text-center mt-4 pt-2 border-t border-surface-100">
                        <span class="text-xs text-surface-400">Query Matches: <strong id="matching-count" class="text-surface-700 font-bold font-mono"><?php echo $totalItems; ?></strong> products</span>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Right Content Area: Main Table View -->
        <main class="flex-grow flex flex-col bg-surface-50 min-w-0 xl:h-[calc(100vh-64px)] overflow-hidden relative bg-grid-pattern">
            
            <!-- Floating Capsule Command & Search Bar -->
            <div class="px-6 pt-6 pb-4 flex-shrink-0 z-20">
                <div class="w-full max-w-6xl mx-auto bg-white/95 backdrop-blur-md border border-surface-200/80 shadow-float rounded-full pl-6 pr-2 py-2 flex items-center justify-between gap-4 transition-all focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/10">
                    <!-- Search Input Wrapper -->
                    <div class="flex items-center gap-3 flex-grow min-w-0">
                        <i class="fa-solid fa-magnifying-glass text-surface-400 text-sm flex-shrink-0"></i>
                        <input type="text" id="searchInput" placeholder="Search catalog by SKU, name, barcode..." class="w-full bg-transparent border-none outline-none text-sm text-surface-900 placeholder-surface-400 focus:ring-0 p-0" oninput="syncSearchAndTrigger(this.value)" value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>

                    <!-- Divider -->
                    <div class="w-px h-6 bg-surface-200 flex-shrink-0"></div>

                    <!-- Current Page Actions (Import, Export, Add Product) -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Import button -->
                        <button type="button" onclick="openCsvModal()" class="h-9 px-4 rounded-full border border-surface-200 hover:bg-surface-50 text-surface-700 hover:text-surface-900 text-xs font-semibold flex items-center gap-2 ui-transition" title="Import CSV Catalog">
                            <i class="fa-solid fa-file-import text-brand-600 text-xs"></i>
                            <span class="hidden sm:inline">Import</span>
                        </button>
                        
                        <!-- Export button -->
                        <a href="<?php echo APP_URL; ?>/inventory/exportCSV" class="h-9 px-4 rounded-full border border-surface-200 hover:bg-surface-50 text-surface-700 hover:text-surface-900 text-xs font-semibold flex items-center gap-2 ui-transition" title="Export CSV Catalog">
                            <i class="fa-solid fa-file-export text-brand-600 text-xs"></i>
                            <span class="hidden sm:inline">Export</span>
                        </a>

                        <!-- Add Product -->
                        <a href="<?= APP_URL ?>/inventory/add" class="h-9 rounded-full bg-brand-600 hover:bg-brand-700 text-white px-4.5 flex items-center gap-2 text-xs font-bold shadow-sm hover:shadow-md ui-transition">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>New Product</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts Overlay (Absolute positioned over table area) -->
            <div class="absolute top-24 left-0 right-0 z-30 px-6 pointer-events-none flex flex-col items-center gap-2">
                <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
                    <div id="flash-success-alert" class="pointer-events-auto bg-white border border-green-200 shadow-float rounded-xl p-4 flex items-center gap-3 max-w-md w-full ui-transition animate-fade-in">
                        <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check text-sm"></i></div>
                        <div class="flex-grow">
                            <p class="text-sm font-semibold text-surface-900">Success</p>
                            <p class="text-xs text-surface-500"><?php echo htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? ''); ?></p>
                        </div>
                        <button onclick="document.getElementById('flash-success-alert').style.display='none'" class="text-surface-400 hover:text-surface-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <?php if ($flashError): ?>
                    <div id="flash-error-alert" class="pointer-events-auto bg-white border border-red-200 shadow-float rounded-xl p-4 flex items-center gap-3 max-w-md w-full ui-transition animate-fade-in">
                        <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-exclamation text-sm"></i></div>
                        <div class="flex-grow">
                            <p class="text-sm font-semibold text-surface-900">Error</p>
                            <p class="text-xs text-surface-500"><?php echo htmlspecialchars($flashError); ?></p>
                        </div>
                        <button onclick="document.getElementById('flash-error-alert').style.display='none'" class="text-surface-400 hover:text-surface-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Import Results -->
            <?php if ($importResults): ?>
                <div id="import-results-panel" class="bg-white border-b border-surface-200 p-6 flex-shrink-0 z-10 mx-6 mb-4 rounded-2xl shadow-subtle border">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-surface-900">Import Results</h3>
                        <button onclick="document.getElementById('import-results-panel').style.display='none'" class="text-surface-400 hover:text-surface-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="flex gap-4 mb-4">
                        <span class="px-3 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-md border border-green-200">Added: <?= $importResults['added']; ?></span>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-md border border-blue-200">Updated: <?= $importResults['updated']; ?></span>
                        <span class="px-3 py-1 bg-red-50 text-red-700 text-xs font-medium rounded-md border border-red-200">Errors: <?= count($importResults['errors']); ?></span>
                    </div>
                    <?php if (!empty($importResults['errors'])): ?>
                    <div class="bg-red-50/50 border border-red-100 rounded-lg p-4 max-h-48 overflow-y-auto">
                        <p class="text-xs font-semibold text-red-800 mb-2">Errors details:</p>
                        <ul class="text-xs text-red-600 space-y-1 list-disc pl-4">
                            <?php foreach ($importResults['errors'] as $err): ?>
                                <li><?= is_array($err) ? htmlspecialchars($err['sku'] ?? 'Row ' . ($err['row'] ?? '-')) . ': ' . implode(', ', array_map('htmlspecialchars', $err['messages'] ?? [])) : htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Table Area (Scrollable) -->
            <div class="flex-grow overflow-auto relative bg-white border-t border-surface-200" id="table-wrapper">
                
                <!-- Loader -->
                <div id="table-loader" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-20 flex items-center justify-center opacity-0 pointer-events-none ui-transition">
                    <div class="flex flex-col items-center bg-white p-4 rounded-xl shadow-glass border border-surface-100">
                        <i class="fa-solid fa-circle-notch fa-spin text-brand-600 text-2xl mb-2"></i>
                        <span class="text-xs font-medium text-surface-600">Syncing data...</span>
                    </div>
                </div>

                <div id="table-container" class="min-w-[900px]">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="custom-checkbox">
                                    </div>
                                </th>
                                <th class="w-16">Item</th>
                                <th>Product Details</th>
                                <th>Identifiers</th>
                                <th class="text-right">Retail Price</th>
                                <th class="text-right text-brand-700 bg-brand-50/30">B2B Base</th>
                                <th>Stock Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-20">
                                        <div class="w-16 h-16 bg-surface-50 border border-surface-200 rounded-full flex items-center justify-center mx-auto mb-4 text-surface-400">
                                            <i class="fa-solid fa-box-open text-2xl"></i>
                                        </div>
                                        <h3 class="text-sm font-semibold text-surface-900 mb-1">No products found</h3>
                                        <p class="text-xs text-surface-500 max-w-xs mx-auto">Try adjusting your filters or search query to find what you're looking for.</p>
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
                                        $statusClass = 'status-outstock';
                                        $statusText = 'Out of Stock';
                                        $statusDot = 'bg-red-500';
                                    } elseif ($qty <= 5) {
                                        $statusClass = 'status-lowstock';
                                        $statusText = 'Low Stock';
                                        $statusDot = 'bg-amber-500';
                                    } else {
                                        $statusClass = 'status-instock';
                                        $statusText = 'In Stock';
                                        $statusDot = 'bg-emerald-500';
                                    }
                                    ?>
                                    <tr class="group">
                                        <td class="text-center">
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" name="selected_items[]" value="<?php echo $item->id; ?>" onchange="updateSelection()" class="item-select-checkbox custom-checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="w-10 h-10 rounded-lg border border-surface-200 bg-surface-50 overflow-hidden flex-shrink-0">
                                                <img src="<?php echo $img_src; ?>" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/120?text=N/A'">
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-medium text-surface-900"><?php echo htmlspecialchars($item->name ?? 'Unnamed Item'); ?></p>
                                            <?php if (!empty($item->description)): ?>
                                                <p class="text-xs text-surface-500 truncate max-w-[250px] mt-0.5" title="<?php echo htmlspecialchars($item->description); ?>"><?php echo htmlspecialchars($item->description); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="flex flex-col gap-1">
                                                <span class="font-mono text-xs font-medium text-surface-700 bg-surface-100 border border-surface-200 rounded px-1.5 py-0.5 w-fit select-all"><?php echo htmlspecialchars($sku); ?></span>
                                                <span class="text-[11px] text-surface-400 font-mono">Sample: <?php echo htmlspecialchars($item->sample_code ?? '—'); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <p class="text-sm font-semibold text-surface-900 font-mono"><?php echo number_format($price, 2); ?></p>
                                            <p class="text-[10px] text-surface-400 uppercase mt-0.5">LKR</p>
                                        </td>
                                        <td class="text-right bg-brand-50/10 group-hover:bg-brand-50/30 ui-transition">
                                            <p class="text-sm font-semibold text-brand-700 font-mono"><?php echo number_format($b2b_price, 2); ?></p>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <span class="font-mono text-sm font-medium <?php echo $qty <= 0 ? 'text-red-600' : ($qty <= 5 ? 'text-amber-600' : 'text-surface-900'); ?>">
                                                    <?php echo $qty; ?>
                                                </span>
                                                <span class="status-pill <?php echo $statusClass; ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full <?php echo $statusDot; ?>"></span>
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 ui-transition">
                                                <a href="<?php echo APP_URL; ?>/stockledger/product/<?php echo $item->id; ?>" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-brand-50 hover:text-brand-600 text-surface-500 ui-transition" title="Ledger">
                                                    <i class="fa-solid fa-chart-line text-xs"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/inventory/edit/<?php echo $item->id; ?>" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-surface-100 hover:text-surface-900 text-surface-500 ui-transition" title="Edit">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </a>
                                                <button type="button" onclick="confirmDelete(<?php echo $item->id; ?>, '<?php echo htmlspecialchars(addslashes($item->name)); ?>')" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-red-50 hover:text-red-600 text-surface-500 ui-transition" title="Delete">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Pagination -->
            <div class="bg-white border-t border-surface-200 px-6 py-3 flex items-center justify-between flex-shrink-0 z-10">
                <p class="text-xs text-surface-500">
                    Showing <span class="font-medium text-surface-900"><?php echo $startIndex; ?></span> to <span class="font-medium text-surface-900"><?php echo $endIndex; ?></span> of <span class="font-medium text-surface-900"><?php echo $totalItems; ?></span> entries
                </p>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-surface-500">Rows:</span>
                        <select onchange="updatePageSize(this.value)" class="premium-select py-1 pl-2 pr-6 text-xs h-7 min-h-0 bg-surface-50">
                            <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="15" <?php echo $perPage === 15 ? 'selected' : ''; ?>>15</option>
                            <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    <div class="flex border border-surface-200 rounded-lg overflow-hidden shadow-sm">
                        <button type="button" onclick="navigatePage(<?php echo max(1, $currentPage - 1); ?>)" class="px-3 py-1.5 bg-white hover:bg-surface-50 text-surface-600 text-xs disabled:opacity-50" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        
                        <?php
                        $range = 1;
                        $startPage = max(1, $currentPage - $range);
                        $endPage = min($totalPages, $currentPage + $range);

                        if ($startPage > 1) {
                            echo '<button type="button" onclick="navigatePage(1)" class="px-3 py-1.5 border-l border-surface-200 bg-white hover:bg-surface-50 text-surface-700 text-xs font-medium">1</button>';
                            if ($startPage > 2) echo '<span class="px-2 py-1.5 border-l border-surface-200 bg-white text-surface-400 text-xs">...</span>';
                        }
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $active = $i === $currentPage;
                            $activeClass = $active ? 'bg-surface-900 text-white hover:bg-surface-800' : 'bg-white hover:bg-surface-50 text-surface-700';
                            echo '<button type="button" onclick="navigatePage('.$i.')" class="px-3 py-1.5 border-l border-surface-200 text-xs font-medium ui-transition '.$activeClass.'">'.$i.'</button>';
                        }
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) echo '<span class="px-2 py-1.5 border-l border-surface-200 bg-white text-surface-400 text-xs">...</span>';
                            echo '<button type="button" onclick="navigatePage('.$totalPages.')" class="px-3 py-1.5 border-l border-surface-200 bg-white hover:bg-surface-50 text-surface-700 text-xs font-medium">'.$totalPages.'</button>';
                        }
                        ?>

                        <button type="button" onclick="navigatePage(<?php echo min($totalPages, $currentPage + 1); ?>)" class="px-3 py-1.5 border-l border-surface-200 bg-white hover:bg-surface-50 text-surface-600 text-xs disabled:opacity-50" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Contextual Action Bar (Linear/Stripe style floating toolbar) -->
    <div id="bulkEditToolbar" class="fixed bottom-8 left-1/2 -translate-x-1/2 glass-panel shadow-float px-5 py-3.5 flex items-center gap-6 z-40 hidden ui-transition transform translate-y-4 opacity-0 transition-all duration-300">
        <div class="flex items-center gap-3 border-r border-surface-200/50 pr-6">
            <div class="bg-surface-900 text-white rounded text-xs font-bold px-2 py-0.5 font-mono" id="selectedCountBadge">0</div>
            <span class="text-xs font-semibold text-surface-600">items selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="clearSelection()" class="px-3 py-1.5 text-xs font-semibold text-surface-500 hover:text-surface-900 ui-transition">Cancel</button>
            <button type="button" onclick="openBulkEditModal()" class="px-4.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-2 ui-transition">
                <i class="fa-solid fa-bolt text-[10px]"></i> Bulk Edit
            </button>
        </div>
    </div>

    <!-- Bulk Edit Modal (Apple Settings Style) -->
    <div id="bulkEditModal" class="fixed inset-0 bg-surface-900/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-float max-w-lg w-full overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="bg-surface-50 px-5 py-4 border-b border-surface-200 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white border border-surface-200 flex items-center justify-center text-brand-600 shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-surface-900">Bulk Modification</h3>
                        <p class="text-xs text-surface-500">Editing <strong id="bulkSelectedCount" class="text-brand-600">0</strong> items</p>
                    </div>
                </div>
                <button onclick="closeBulkEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-surface-400 hover:bg-surface-200 hover:text-surface-600 ui-transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="overflow-y-auto p-5">
                <form id="bulkEditForm" onsubmit="submitBulkEdit(event)" class="space-y-4">
                    <div id="bulkEditErrorContainer" class="hidden p-3 bg-red-50 border border-red-100 text-red-600 text-xs rounded-lg mb-4"></div>

                    <!-- 1. Category -->
                    <div class="border border-surface-200 rounded-xl overflow-hidden focus-within:border-brand-300 focus-within:ring-1 focus-within:ring-brand-300 ui-transition bg-white">
                        <div class="flex items-center justify-between p-3.5 bg-surface-50 border-b border-surface-200">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-surface-700 uppercase tracking-wider select-none">
                                <input type="checkbox" name="update_category" value="1" id="bulkUpdateCategory" onchange="toggleBulkField('category')" class="custom-checkbox">
                                <i class="fa-solid fa-folder text-surface-400 w-4 text-center"></i>
                                Update Category
                            </label>
                        </div>
                        <div class="p-4 bg-white">
                            <select name="category_id" id="bulkCategorySelect" disabled class="premium-select w-full disabled:opacity-50 disabled:bg-surface-50">
                                <option value="">No Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 2. Selling Price -->
                    <div class="border border-surface-200 rounded-xl overflow-hidden focus-within:border-brand-300 focus-within:ring-1 focus-within:ring-brand-300 ui-transition bg-white">
                        <div class="flex items-center justify-between p-3.5 bg-surface-50 border-b border-surface-200">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-surface-700 uppercase tracking-wider select-none">
                                <input type="checkbox" name="update_selling_price" value="1" id="bulkUpdateSellingPrice" onchange="toggleBulkField('selling_price')" class="custom-checkbox">
                                <i class="fa-solid fa-tag text-surface-400 w-4 text-center"></i>
                                Update Retail Price
                            </label>
                        </div>
                        <div class="p-4 bg-white">
                            <div class="flex gap-2">
                                <select name="selling_price_type" id="bulkSellingPriceType" disabled class="premium-select w-1/3 disabled:opacity-50 disabled:bg-surface-50">
                                    <option value="flat">Set Flat Value</option>
                                    <option value="pct_inc">Increase by %</option>
                                    <option value="pct_dec">Decrease by %</option>
                                </select>
                                <input type="number" step="0.01" name="selling_price_val" id="bulkSellingPriceVal" disabled placeholder="e.g. 10.00" class="premium-input w-2/3 disabled:opacity-50 disabled:bg-surface-50">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Wholesale Price -->
                    <div class="border border-surface-200 rounded-xl overflow-hidden focus-within:border-brand-300 focus-within:ring-1 focus-within:ring-brand-300 ui-transition bg-white">
                        <div class="flex items-center justify-between p-3.5 bg-surface-50 border-b border-surface-200">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-surface-700 uppercase tracking-wider select-none">
                                <input type="checkbox" name="update_wholesale_price" value="1" id="bulkUpdateWholesalePrice" onchange="toggleBulkField('wholesale_price')" class="custom-checkbox">
                                <i class="fa-solid fa-tags text-surface-400 w-4 text-center"></i>
                                Update B2B Base Price
                            </label>
                        </div>
                        <div class="p-4 bg-white">
                            <div class="flex gap-2">
                                <select name="wholesale_price_type" id="bulkWholesalePriceType" disabled class="premium-select w-1/3 disabled:opacity-50 disabled:bg-surface-50">
                                    <option value="flat">Set Flat Value</option>
                                    <option value="pct_inc">Increase by %</option>
                                    <option value="pct_dec">Decrease by %</option>
                                </select>
                                <input type="number" step="0.01" name="wholesale_price_val" id="bulkWholesalePriceVal" disabled placeholder="e.g. 10.00" class="premium-input w-2/3 disabled:opacity-50 disabled:bg-surface-50">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Status -->
                    <div class="border border-surface-200 rounded-xl overflow-hidden focus-within:border-brand-300 focus-within:ring-1 focus-within:ring-brand-300 ui-transition bg-white">
                        <div class="flex items-center justify-between p-3.5 bg-surface-50 border-b border-surface-200">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-surface-700 uppercase tracking-wider select-none">
                                <input type="checkbox" name="update_status" value="1" id="bulkUpdateStatus" onchange="toggleBulkField('status')" class="custom-checkbox">
                                <i class="fa-solid fa-power-off text-surface-400 w-4 text-center"></i>
                                Update Status
                            </label>
                        </div>
                        <div class="p-4 bg-white">
                            <select name="status" id="bulkStatusSelect" disabled class="premium-select w-full disabled:opacity-50 disabled:bg-surface-50">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-4 border-t border-surface-200 bg-surface-50 flex justify-end gap-3">
                <button type="button" onclick="closeBulkEditModal()" class="btn-secondary">Cancel</button>
                <button type="submit" form="bulkEditForm" id="bulkSubmitBtn" class="btn-primary bg-brand-600 border-brand-600 hover:bg-brand-700 hover:border-brand-700 text-white">
                    <span id="bulkBtnSpinner" style="display:none;" class="mr-2"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    Confirm Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Product Delete Confirmation Modal -->
    <div id="deleteProductModal" class="fixed inset-0 bg-surface-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-float max-w-md w-full overflow-hidden border border-surface-200 flex flex-col">
            <div class="bg-rose-50 px-5 py-4 border-b border-rose-100 flex justify-between items-center">
                <div class="flex items-center gap-3 text-rose-800">
                    <div class="w-8 h-8 rounded-lg bg-white border border-rose-200 flex items-center justify-center text-rose-600 shadow-sm">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-rose-900">Delete Product</h3>
                        <p class="text-xs text-rose-500">This action is destructive and permanent</p>
                    </div>
                </div>
                <button onclick="closeDeleteModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-surface-400 hover:bg-rose-100 hover:text-surface-600 ui-transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="deleteProductForm" onsubmit="submitDeleteProduct(event)" class="p-6 space-y-4">
                <input type="hidden" id="deleteItemId" name="item_id">
                
                <p class="text-sm text-surface-600 leading-relaxed">
                    Are you sure you want to permanently delete <strong id="deleteItemName" class="text-surface-900 font-semibold"></strong>? All ledger history and stock associations for this product will be lost.
                </p>

                <div id="deleteErrorContainer" class="hidden p-3 bg-red-50 border border-red-100 text-red-600 text-xs rounded-lg"></div>

                <?php if ($isCurrentlyAdmin): ?>
                    <!-- Administrator: password bypass allowed -->
                <?php else: ?>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex gap-2.5 items-start">
                        <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5 text-xs"></i>
                        <div class="text-[11px] text-amber-800 leading-relaxed font-medium">
                            Administrative privileges are required to perform this action. An administrator must verify their credentials below to sign the deletion.
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-surface-500 uppercase tracking-wider mb-1.5">Admin Username</label>
                            <input type="text" name="admin_username" id="deleteAdminUsername" required placeholder="Enter admin username" class="premium-input">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-surface-500 uppercase tracking-wider mb-1.5">Admin Password</label>
                            <input type="password" name="password" id="deleteAdminPassword" required placeholder="Enter admin password" class="premium-input">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-200">
                    <button type="button" onclick="closeDeleteModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" id="deleteSubmitBtn" class="btn-primary bg-rose-600 border-rose-600 hover:bg-rose-700 hover:border-rose-700 text-white">
                        <span id="deleteBtnSpinner" style="display:none;" class="mr-2"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        Authorize & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CSV Import Modal (Glassmorphic) -->
    <div id="csvImportModal" class="fixed inset-0 bg-surface-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-float max-w-lg w-full overflow-hidden border border-surface-200 flex flex-col">
            <div class="bg-surface-50 px-5 py-4 border-b border-surface-200 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white border border-surface-200 flex items-center justify-center text-brand-600 shadow-sm">
                        <i class="fa-solid fa-file-import text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-surface-900">Import Products Catalog</h3>
                        <p class="text-xs text-surface-500">Upload CSV to bulk add or update products</p>
                    </div>
                </div>
                <button onclick="closeCsvModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-surface-400 hover:bg-surface-200 hover:text-surface-600 ui-transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <form action="<?php echo APP_URL; ?>/inventory/importERPCSV" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider">Select Standard ERP CSV File</label>
                        <div class="border-2 border-dashed border-surface-200 hover:border-brand-500 bg-surface-50 rounded-xl p-8 text-center cursor-pointer relative transition-colors duration-150">
                            <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 opacity-0 cursor-pointer">
                            <div class="space-y-2">
                                <div class="h-10 w-10 bg-white border border-surface-200 text-brand-600 rounded-xl flex items-center justify-center mx-auto shadow-sm">
                                    <i class="fa-solid fa-file-arrow-up text-lg"></i>
                                </div>
                                <p class="text-xs font-semibold text-surface-700">Drag & drop standard CSV here or <span class="text-brand-600 hover:underline">browse files</span></p>
                                <p class="text-[10px] text-surface-400">Standard ERP format (resolves category, warehouse, vendor by name)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Column Mapping Info -->
                    <div class="bg-brand-50/50 border border-brand-100 rounded-xl p-4 space-y-2 text-xs text-brand-950">
                        <h4 class="font-bold text-brand-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i> Smart Field Matcher
                        </h4>
                        <p class="text-[11px] leading-relaxed text-brand-800">
                            Products are matched using the <strong class="font-semibold text-brand-900">SKU</strong>. If category, warehouse, or vendor names are not found in the database, they will be created dynamically.
                        </p>
                        <div class="text-[10px] text-brand-700/80 font-mono overflow-x-auto py-1 border-t border-brand-100 mt-2">
                            Expected: SKU, Name, Selling Price, Wholesale Price, Cost Price, Quantity, Description, Barcode, Category, Brand, Warehouse, Vendor, Alert Qty, Unit, Status
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-surface-200">
                        <button type="button" onclick="closeCsvModal()" class="btn-secondary flex-1">Cancel</button>
                        <button type="submit" class="btn-primary bg-brand-600 border-brand-600 hover:bg-brand-700 text-white flex-1">Start Catalog Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Client-Side Reactive Controller Scripts -->
    <script>
        let searchTimeout = null;

        /**
         * Synchronizes floating search input value to the hidden sidebar filter input
         * and triggers the search debounce timer.
         */
        function syncSearchAndTrigger(val) {
            document.getElementById('hiddenSearchInput').value = val;
            triggerSearchDelay();
        }

        /**
         * Trigger debounce search update to avoid database flooding while typing
         */
        function triggerSearchDelay() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Reset current page back to 1 on typing to get fresh filter results
                document.getElementById('currentPageInput').value = '1';
                applyAjaxFilters();
            }, 300); // 300ms buffer
        }

        /**
         * Submit form values asynchronously using HTML Fetch
         * Parses the response and updates the table without losing search field focus
         */
        function applyAjaxFilters() {
            const form = document.getElementById('filterForm');
            const loader = document.getElementById('table-loader');

            // Show local loading overlay inside table container
            if (loader) {
                loader.classList.remove('pointer-events-none');
                loader.classList.add('opacity-100');
            }

            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();
            const requestUrl = '<?php echo APP_URL; ?>/inventory?' + queryParams;

            // Fetch the template payload safely
            fetch(requestUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network error during inventory retrieval');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Swap table structure
                    const newTable = doc.getElementById('table-container');
                    const oldTable = document.getElementById('table-container');
                    if (newTable && oldTable) {
                        oldTable.innerHTML = newTable.innerHTML;
                    }

                    // Swap dynamic statistics counters without full page reloads
                    const updateStat = (id) => {
                        const newVal = doc.getElementById(id);
                        const oldVal = document.getElementById(id);
                        if (newVal && oldVal) oldVal.textContent = newVal.textContent;
                    };
                    updateStat('stat-total-items');
                    updateStat('stat-low-stock');
                    updateStat('stat-out-of-stock');
                    updateStat('matching-count');

                    // Update Address Bar/History URL so back actions are preserved
                    window.history.pushState({ path: requestUrl }, '', requestUrl);
                    clearSelection();
                })
                .catch(err => {
                    console.error('Asynchronous Sync Error:', err);
                })
                .finally(() => {
                    // Hide table loader
                    if (loader) {
                        loader.classList.add('pointer-events-none');
                        loader.classList.remove('opacity-100');
                    }
                });
        }

        /**
         * Handle page click navigations
         */
        function navigatePage(pageNum) {
            document.getElementById('currentPageInput').value = pageNum;
            applyAjaxFilters();
        }

        /**
         * Handle page size limit changes
         */
        function updatePageSize(size) {
            document.getElementById('perPageInput').value = size;
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        /**
         * Reset form inputs completely
         */
        function clearAllFilters() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) searchInput.value = '';
            
            document.getElementById('hiddenSearchInput').value = '';
            document.querySelector('select[name="category_id"]').value = '';
            document.querySelector('select[name="stock_status"]').value = '';
            document.querySelector('input[name="min_price"]').value = '';
            document.querySelector('input[name="max_price"]').value = '';
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        // Modal Control Functions
        function openCsvModal() {
            document.getElementById('csvImportModal').classList.remove('hidden');
        }

        function closeCsvModal() {
            document.getElementById('csvImportModal').classList.add('hidden');
        }

        let activeDeleteId = null;

        function confirmDelete(id, name) {
            activeDeleteId = id;
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            
            const errorContainer = document.getElementById('deleteErrorContainer');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                errorContainer.textContent = '';
            }
            
            const passwordInput = document.getElementById('deleteAdminPassword');
            if (passwordInput) passwordInput.value = '';
            
            const usernameInput = document.getElementById('deleteAdminUsername');
            if (usernameInput) usernameInput.value = '';

            document.getElementById('deleteProductModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteProductModal').classList.add('hidden');
            activeDeleteId = null;
        }

        function submitDeleteProduct(e) {
            e.preventDefault();

            if (!activeDeleteId) return;

            const form = document.getElementById('deleteProductForm');
            const submitBtn = document.getElementById('deleteSubmitBtn');
            const spinner = document.getElementById('deleteBtnSpinner');
            const errorContainer = document.getElementById('deleteErrorContainer');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                errorContainer.textContent = '';
            }

            const formData = new FormData(form);

            fetch('<?php echo APP_URL; ?>/inventory/delete/' + activeDeleteId, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to authorize deletion');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    applyAjaxFilters();
                } else {
                    if (errorContainer) {
                        errorContainer.textContent = data.error || 'Authorization failed. Please try again.';
                        errorContainer.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                if (errorContainer) {
                    errorContainer.textContent = err.message || 'An error occurred during verification.';
                    errorContainer.classList.remove('hidden');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.style.display = 'none';
            });
        }

        // ==========================================
        // BULK EDIT SELECTION & MODAL ENGINE
        // ==========================================
        function toggleSelectAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateSelection();
        }

        function updateSelection() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            const selectedIds = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedIds.push(cb.value);
                }
            });

            const toolbar = document.getElementById('bulkEditToolbar');
            const countBadge = document.getElementById('selectedCountBadge');
            const selectAllCb = document.getElementById('selectAllCheckbox');

            if (selectedIds.length > 0) {
                if (countBadge) countBadge.textContent = selectedIds.length;
                if (toolbar) {
                    if (toolbar.classList.contains('hidden')) {
                        toolbar.classList.remove('hidden');
                        toolbar.offsetHeight; // force reflow
                    }
                    toolbar.classList.remove('translate-y-4', 'opacity-0');
                }
                if (selectAllCb) {
                    selectAllCb.checked = (selectedIds.length === checkboxes.length);
                }
            } else {
                if (toolbar) {
                    toolbar.classList.add('translate-y-4', 'opacity-0');
                    setTimeout(() => {
                        // verify array is still empty before hiding container
                        const activeCheckboxes = document.querySelectorAll('.item-select-checkbox:checked');
                        if (activeCheckboxes.length === 0) {
                            toolbar.classList.add('hidden');
                        }
                    }, 300);
                }
                if (selectAllCb) selectAllCb.checked = false;
            }
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            const selectAllCb = document.getElementById('selectAllCheckbox');
            if (selectAllCb) selectAllCb.checked = false;
            updateSelection();
        }

        function openBulkEditModal() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            const form = document.getElementById('bulkEditForm');
            
            const oldInputs = form.querySelectorAll('input[name="item_ids[]"]');
            oldInputs.forEach(input => input.remove());

            let selectedCount = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedCount++;
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'item_ids[]';
                    hiddenInput.value = cb.value;
                    form.appendChild(hiddenInput);
                }
            });

            document.getElementById('bulkSelectedCount').textContent = selectedCount;
            
            document.getElementById('bulkUpdateCategory').checked = false;
            toggleBulkField('category');
            document.getElementById('bulkUpdateSellingPrice').checked = false;
            toggleBulkField('selling_price');
            document.getElementById('bulkUpdateWholesalePrice').checked = false;
            toggleBulkField('wholesale_price');
            document.getElementById('bulkUpdateStatus').checked = false;
            toggleBulkField('status');

            document.getElementById('bulkEditErrorContainer').classList.add('hidden');
            document.getElementById('bulkEditErrorContainer').textContent = '';

            document.getElementById('bulkEditModal').classList.remove('hidden');
            document.getElementById('bulkEditModal').classList.add('flex');
        }

        function closeBulkEditModal() {
            document.getElementById('bulkEditModal').classList.add('hidden');
            document.getElementById('bulkEditModal').classList.remove('flex');
        }

        function toggleBulkField(field) {
            let id = 'bulkUpdateCategory';
            if (field === 'selling_price') id = 'bulkUpdateSellingPrice';
            else if (field === 'wholesale_price') id = 'bulkUpdateWholesalePrice';
            else if (field === 'status') id = 'bulkUpdateStatus';
            
            const checkbox = document.getElementById(id);
            if (!checkbox) return;
            const isChecked = checkbox.checked;
            
            if (field === 'category') {
                const select = document.getElementById('bulkCategorySelect');
                select.disabled = !isChecked;
            } else if (field === 'selling_price') {
                document.getElementById('bulkSellingPriceType').disabled = !isChecked;
                document.getElementById('bulkSellingPriceVal').disabled = !isChecked;
            } else if (field === 'wholesale_price') {
                document.getElementById('bulkWholesalePriceType').disabled = !isChecked;
                document.getElementById('bulkWholesalePriceVal').disabled = !isChecked;
            } else if (field === 'status') {
                const select = document.getElementById('bulkStatusSelect');
                select.disabled = !isChecked;
            }
        }

        function submitBulkEdit(e) {
            e.preventDefault();

            const form = document.getElementById('bulkEditForm');
            const submitBtn = document.getElementById('bulkSubmitBtn');
            const spinner = document.getElementById('bulkBtnSpinner');
            const errorContainer = document.getElementById('bulkEditErrorContainer');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (errorContainer) {
                errorContainer.classList.add('hidden');
                errorContainer.textContent = '';
            }

            const formData = new FormData(form);

            fetch('<?php echo APP_URL; ?>/inventory/bulkUpdate', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Bulk update failed');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeBulkEditModal();
                    clearSelection();
                    applyAjaxFilters();
                } else {
                    if (errorContainer) {
                        errorContainer.textContent = data.error || 'Bulk update failed. Please try again.';
                        errorContainer.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                if (errorContainer) {
                    errorContainer.textContent = err.message || 'An error occurred during bulk update.';
                    errorContainer.classList.remove('hidden');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.style.display = 'none';
            });
        }
    </script>

    <?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>