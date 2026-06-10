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
                            900: '#134e4a',
                        },
                        surface: {
                            50: '#fafafa',
                            100: '#f4f4f5',
                            200: '#e4e4e7',
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
            background: #fafafa; color: #71717a; font-size: 12px; font-weight: 500; text-transform: uppercase; 
            letter-spacing: 0.05em; padding: 12px 16px; text-align: left; border-bottom: 1px solid #e4e4e7;
            position: sticky; top: 0; z-index: 10;
        }
        .modern-table td { padding: 14px 16px; border-bottom: 1px solid #f4f4f5; font-size: 13px; vertical-align: middle; }
        .modern-table tr:last-child td { border-bottom: none; }
        .modern-table tr:hover td { background: #fcfcfc; }

        /* Status Pills */
        .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 500; }
        .status-instock { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
        .status-lowstock { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
        .status-outstock { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }

        /* Custom Checkbox */
        .custom-checkbox { width: 16px; height: 16px; border-radius: 4px; border: 1px solid #d4d4d8; accent-color: #18181b; cursor: pointer; }

        /* Glass Panel */
        .glass-panel { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border: 1px solid #e4e4e7; border-radius: 12px; }

        /* Checkbox wrapper animation */
        .checkbox-wrapper { display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Main Container Layout: Split View -->
    <div class="flex-grow flex flex-col xl:flex-row h-full overflow-hidden w-full">
        
        <!-- Left Sidebar: Contextual Filters & Insights -->
        <aside class="w-full xl:w-80 flex-shrink-0 bg-white border-r border-surface-200 flex flex-col h-full xl:h-[calc(100vh-64px)] xl:sticky xl:top-0 overflow-y-auto">
            <div class="p-6 border-b border-surface-200">
                <h1 class="text-xl font-semibold text-surface-900 tracking-tight">Inventory</h1>
                <p class="text-sm text-surface-500 mt-1">Manage catalog and stock levels.</p>
            </div>

            <!-- Insights (KPI Cards) -->
            <div class="p-6 border-b border-surface-200 space-y-4">
                <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Insights</h2>
                
                <div class="bg-surface-50 border border-surface-200 rounded-xl p-4 ui-transition hover:border-surface-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-surface-500 font-medium">Total Products</p>
                            <p class="text-2xl font-semibold text-surface-900 mt-1"><?php echo number_format($stats->total_items); ?></p>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-white border border-surface-200 flex items-center justify-center text-surface-500">
                            <i class="fa-solid fa-layer-group text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#fffbeb] border border-[#fef3c7] rounded-xl p-3">
                        <p class="text-[11px] text-[#b45309] font-semibold uppercase tracking-wider">Low Stock</p>
                        <p class="text-xl font-semibold text-[#d97706] mt-1"><?php echo number_format($stats->low_stock_count); ?></p>
                    </div>
                    <div class="bg-[#fef2f2] border border-[#fee2e2] rounded-xl p-3">
                        <p class="text-[11px] text-[#b91c1c] font-semibold uppercase tracking-wider">Out of Stock</p>
                        <p class="text-xl font-semibold text-[#dc2626] mt-1"><?php echo number_format($stats->out_of_stock_count); ?></p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 flex-grow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Filters</h2>
                    <button type="button" onclick="clearAllFilters()" class="text-xs text-brand-600 hover:text-brand-700 font-medium ui-transition">Clear all</button>
                </div>

                <form id="filterForm" class="space-y-5">
                    <div>
                        <label class="block text-xs font-medium text-surface-700 mb-1.5">Search Catalog</label>
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-surface-400 text-xs"></i>
                            <input type="text" id="searchInput" name="search" class="premium-input pl-9" placeholder="SKU, Name, Barcode..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-surface-700 mb-1.5">Category</label>
                        <select name="category_id" class="premium-select w-full">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->id; ?>" <?php echo $filters['category_id'] == $cat->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-surface-700 mb-1.5">Stock Status</label>
                        <select name="stock_status" class="premium-select w-full">
                            <option value="">Any Status</option>
                            <option value="instock" <?php echo $filters['stock_status'] === 'instock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="lowstock" <?php echo $filters['stock_status'] === 'lowstock' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="outstock" <?php echo $filters['stock_status'] === 'outstock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-surface-700 mb-1.5">Min Price</label>
                            <input type="number" step="0.01" name="min_price" class="premium-input" placeholder="0.00" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-700 mb-1.5">Max Price</label>
                            <input type="number" step="0.01" name="max_price" class="premium-input" placeholder="Any" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                        </div>
                    </div>

                    <button type="button" onclick="applyAjaxFilters()" class="btn-primary w-full mt-2">
                        Apply Filters
                    </button>
                    
                    <div class="text-center mt-3">
                        <span class="text-xs text-surface-500">Showing <strong id="matching-count" class="text-surface-900 font-medium"><?php echo $totalItems; ?></strong> results</span>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Right Content Area: Main Table View -->
        <main class="flex-grow flex flex-col bg-surface-50 min-w-0 xl:h-[calc(100vh-64px)] overflow-hidden relative">
            
            <!-- Command Bar / Header -->
            <header class="bg-white border-b border-surface-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 z-20 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center text-brand-600 border border-brand-100">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-surface-900">Products List</h2>
                        <p class="text-xs text-surface-500">Overview of all active inventory</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="<?php echo APP_URL; ?>/inventory/exportCSV" class="btn-secondary" title="Export CSV">
                        <i class="fa-solid fa-download text-surface-500 text-xs"></i>
                        <span class="hidden sm:inline">Export</span>
                    </a>
                    <button onclick="openCsvModal()" class="btn-secondary" title="Import CSV">
                        <i class="fa-solid fa-upload text-surface-500 text-xs"></i>
                        <span class="hidden sm:inline">Import</span>
                    </button>
                    <a href="<?php echo APP_URL; ?>/inventory/add" class="btn-primary">
                        <i class="fa-solid fa-plus text-xs"></i> New Product
                    </a>
                </div>
            </header>

            <!-- Alerts Overlay (Absolute positioned over table area) -->
            <div class="absolute top-4 left-0 right-0 z-30 px-6 pointer-events-none flex flex-col items-center gap-2">
                <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
                    <div id="flash-success-alert" class="pointer-events-auto bg-white border border-green-200 shadow-float rounded-xl p-4 flex items-center gap-3 max-w-md w-full ui-transition">
                        <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-check text-sm"></i></div>
                        <div class="flex-grow">
                            <p class="text-sm font-semibold text-surface-900">Success</p>
                            <p class="text-xs text-surface-500"><?php echo htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? ''); ?></p>
                        </div>
                        <button onclick="document.getElementById('flash-success-alert').style.display='none'" class="text-surface-400 hover:text-surface-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <?php if ($flashError): ?>
                    <div id="flash-error-alert" class="pointer-events-auto bg-white border border-red-200 shadow-float rounded-xl p-4 flex items-center gap-3 max-w-md w-full ui-transition">
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
                <div id="import-results-panel" class="bg-white border-b border-surface-200 p-6 flex-shrink-0 z-10">
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
            <div class="flex-grow overflow-auto relative bg-white" id="table-wrapper">
                
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
                                                <p class="text-xs text-surface-500 truncate max-w-[250px] mt-0.5"><?php echo htmlspecialchars($item->description); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="flex flex-col gap-1">
                                                <span class="font-mono text-xs font-medium text-surface-700 bg-surface-100 border border-surface-200 rounded px-1.5 py-0.5 w-fit"><?php echo htmlspecialchars($sku); ?></span>
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
                    Showing <span class="font-medium text-surface-900"><?php echo $startIndex; ?></span> to <span class="font-medium text-surface-900"><?php echo $endIndex; ?></span> of <span class="font-medium text-surface-900"><?php echo $totalItems; ?></span>
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
    <div id="bulkEditToolbar" class="fixed bottom-8 left-1/2 -translate-x-1/2 glass-panel shadow-float px-4 py-3 flex items-center gap-6 z-40 hidden ui-transition transform translate-y-4 opacity-0 transition-all duration-300">
        <div class="flex items-center gap-3 border-r border-surface-200/50 pr-6">
            <div class="bg-surface-900 text-white rounded text-xs font-bold px-2 py-0.5 font-mono" id="selectedCountBadge">0</div>
            <span class="text-sm font-medium text-surface-700">selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="clearSelection()" class="px-3 py-1.5 text-xs font-medium text-surface-500 hover:text-surface-900 ui-transition">Cancel</button>
            <button type="button" onclick="openBulkEditModal()" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-md shadow-sm flex items-center gap-2 ui-transition">
                <i class="fa-solid fa-bolt text-[10px]"></i> Bulk Actions
            </button>
        </div>
    </div>
    
    <script>
        // Intercept bulkEditToolbar showing to add animation classes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const el = mutation.target;
                    if (el.style.display === 'flex' || el.style.display === 'block') {
                        setTimeout(() => {
                            el.classList.remove('translate-y-4', 'opacity-0');
                        }, 10);
                    } else {
                        el.classList.add('translate-y-4', 'opacity-0');
                    }
                }
            });
        });
        const toolbar = document.getElementById('bulkEditToolbar');
        if(toolbar) observer.observe(toolbar, { attributes: true });
    </script>

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

                    <?php
                    $bulkFields = [
                        ['id'=>'category', 'label'=>'Category Assignment', 'field'=>'category_id', 'icon'=>'fa-folder'],
                        ['id'=>'selling_price', 'label'=>'Retail Pricing', 'field'=>'selling_price', 'icon'=>'fa-tag'],
                        ['id'=>'wholesale_price', 'label'=>'B2B Pricing', 'field'=>'wholesale_price', 'icon'=>'fa-tags'],
                        ['id'=>'status', 'label'=>'Availability Status', 'field'=>'status', 'icon'=>'fa-power-off'],
                    ];
                    foreach ($bulkFields as $f):
                    ?>
                    <div class="border border-surface-200 rounded-xl overflow-hidden focus-within:border-brand-300 focus-within:ring-1 focus-within:ring-brand-300 ui-transition bg-white">
                        <div class="flex items-center justify-between p-3 bg-surface-50 border-b border-surface-200">
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-surface-800 select-none">
                                <input type="checkbox" name="update_<?= $f['id'] ?>" value="1" id="bulkUpdate<?= ucfirst($f['id']) ?>" onchange="toggleBulkField('<?= $f['id'] ?>')" class="custom-checkbox">
                                <i class="fa-solid <?= $f['icon'] ?> text-surface-400 w-4 text-center text-xs"></i>
                                <?= $f['label'] ?>
                            </label>
                        </div>
                        <div class="p-3 bg-white">
                        <?php if ($f['id'] === 'category'): ?>
                            <select name="category_id" id="bulkCategorySelect" disabled class="premium-select w-full disabled:opacity-50 disabled:bg-surface-50">
                                <option value="">No Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($f['id'] === 'status'): ?>
                            <select name="status" id="bulkStatusSelect" disabled class="premium-select w-full disabled:opacity-50 disabled:bg-surface-50">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        <?php else: ?>
                            <div class="flex gap-2">
                                <select name="<?= $f['id'] ?>_type" id="bulk<?= ucfirst(str_replace('_','',ucwords($f['id'],'_'))) ?>Type" disabled class="premium-select w-1/2 disabled:opacity-50 disabled:bg-surface-50">
                                    <option value="flat">Set exact value</option>
                                    <option value="pct_inc">Increase %</option>
                                    <option value="pct_dec">Decrease %</option>
                                </select>
                                <input type="number" step="0.01" name="<?= $f['id'] ?>_val" id="bulk<?= ucfirst(str_replace('_','',ucwords($f['id'],'_'))) ?>Val" disabled class="premium-input w-1/2 disabled:opacity-50 disabled:bg-surface-50" placeholder="Amount">
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
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

    <?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>