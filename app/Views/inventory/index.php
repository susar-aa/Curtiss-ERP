<?php
// ==========================================
// VARIABLE SAFETY & ROBUST FALLBACK ENGINE
// ==========================================

// Ensure items array is always defined
$items = $data['items'] ?? [];

// Stock analytics
$stats = $data['stats'] ?? (object)[
    'total_items' => count($items),
    'low_stock_count' => 0,
    'out_of_stock_count' => 0
];

// Ensure filters are fully initialized
$filters = $data['filters'] ?? [];
$filters['search'] = $filters['search'] ?? '';
$filters['min_price'] = $filters['min_price'] ?? '';
$filters['max_price'] = $filters['max_price'] ?? '';
$filters['stock_status'] = $filters['stock_status'] ?? '';

// Retrieve pagination config with safe fallbacks
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

// Calculate display bounds
$startIndex = $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$endIndex = min($currentPage * $perPage, $totalItems);

// Capture bulk action status feedback
$flashSuccess = $_SESSION['flash_success'] ?? null;
if ($flashSuccess) {
    unset($_SESSION['flash_success']);
}
$flashError = $_SESSION['flash_error'] ?? null;
if ($flashError) {
    unset($_SESSION['flash_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Curtiss ERP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Scrollbar for the table container */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* FORCE SCROLLING ENGINE OVERRIDES */
        html, body {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            height: auto !important;
            min-height: 100vh !important;
        }

        /* TOP NAV HOVER GAP REMOVAL BRIDGE OVERRIDES */
        /* Targets hover dropdown structures globally to prevent loss of focus */
        .group:hover > div,
        .group:hover > ul {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Virtual hit-area bridge to cover physical spacing gaps between button trigger and content container */
        .group > div::before,
        .group > ul::before {
            content: '';
            position: absolute;
            top: -24px;
            left: 0;
            right: 0;
            height: 24px;
            background: transparent !important;
            z-index: 10;
        }

        nav, header {
            position: relative;
            z-index: 40 !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Included Unified System Top Menu Bar from Layouts Folder -->
    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Main Workspace Container -->
    <div class="p-8 max-w-[1400px] mx-auto space-y-6 flex-grow">
        
        <!-- Page Header & Actions -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-boxes-stacked text-xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Inventory Catalog</h1>
                </div>
                <p class="text-slate-500 text-sm ml-13">View physical stock, track alert levels, and manage catalog parameters locally.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button onclick="openCsvModal()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-500/30 transition-all duration-200 flex items-center gap-2 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-file-csv"></i> Import WooCommerce CSV
                </button>
                <a href="<?php echo APP_URL; ?>/inventory/migrateImages" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-amber-500/30 transition-all duration-200 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-images animate-pulse"></i> Migrate Remote Images
                </a>
                <a href="<?php echo APP_URL; ?>/inventory/add" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-primary-500/30 transition-all duration-200 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus"></i> Add New Product
                </a>
            </div>
        </div>

        <!-- Notification Alerts -->
        <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
            <div id="flash-success-alert" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-4 shadow-sm animate-fade-in">
                <div class="bg-emerald-100 text-emerald-600 p-2 rounded-full mt-0.5 shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <h4 class="text-emerald-800 font-semibold text-sm">Success Action</h4>
                    <p class="text-emerald-600 text-xs mt-0.5"><?php echo htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? ''); ?></p>
                </div>
                <button onclick="document.getElementById('flash-success-alert').style.display='none'" class="ml-auto text-emerald-400 hover:text-emerald-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div id="flash-error-alert" class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-4 shadow-sm animate-fade-in">
                <div class="bg-rose-100 text-rose-600 p-2 rounded-full mt-0.5 shrink-0">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div>
                    <h4 class="text-rose-800 font-semibold text-sm">Error</h4>
                    <p class="text-rose-600 text-xs mt-0.5"><?php echo htmlspecialchars($flashError); ?></p>
                </div>
                <button onclick="document.getElementById('flash-error-alert').style.display='none'" class="ml-auto text-rose-400 hover:text-rose-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Summary Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary-50 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Catalog Items</p>
                        <h3 class="text-3xl font-extrabold text-slate-800" id="stat-total-items"><?php echo number_format($stats->total_items); ?></h3>
                    </div>
                    <div class="p-3 bg-primary-50 text-primary-600 rounded-xl">
                        <i class="fa-solid fa-cubes text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs font-semibold text-primary-600 flex items-center gap-1">
                    <i class="fa-solid fa-check-double"></i> Complete catalog SKU records
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                 <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Low Stock Alerts</p>
                        <h3 class="text-3xl font-extrabold text-amber-600" id="stat-low-stock"><?php echo number_format($stats->low_stock_count); ?></h3>
                    </div>
                    <div class="p-3 bg-amber-50 text-amber-500 rounded-xl">
                        <i class="fa-solid fa-circle-exclamation text-xl animate-pulse"></i>
                    </div>
                </div>
                 <div class="mt-4 text-xs font-semibold text-amber-500 flex items-center gap-1">
                    <i class="fa-solid fa-box-open"></i> Items below reorder threshold (1-5 units)
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-50 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Out of Stock</p>
                        <h3 class="text-3xl font-extrabold text-rose-600" id="stat-out-of-stock"><?php echo number_format($stats->out_of_stock_count); ?></h3>
                    </div>
                    <div class="p-3 bg-rose-50 text-red-500 rounded-xl">
                        <i class="fa-solid fa-ban text-xl"></i>
                    </div>
                </div>
                 <div class="mt-4 text-xs font-semibold text-rose-500 flex items-center gap-1">
                    <i class="fa-solid fa-truck-ramp-box"></i> Requires immediate reordering
                </div>
            </div>
        </div>

        <!-- Filter Form Controls -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-4 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-primary-500"></i> Interactive Search & Catalog Filters
            </h3>
            
            <form id="filterForm" action="<?php echo APP_URL; ?>/inventory" method="GET" onsubmit="event.preventDefault(); applyAjaxFilters();" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="page" id="currentPageInput" value="<?php echo $currentPage; ?>">
                <input type="hidden" name="per_page" id="perPageInput" value="<?php echo $perPage; ?>">

                <!-- Search field -->
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Code / Title</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" id="searchInput" value="<?php echo htmlspecialchars($filters['search']); ?>" oninput="triggerSearchDelay()" placeholder="Type SKU or Name..." 
                               class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all placeholder-slate-400">
                    </div>
                </div>

                <!-- Pricing Thresholds -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Min Price (Rs.)</label>
                    <input type="number" step="0.01" name="min_price" id="minPriceInput" value="<?php echo htmlspecialchars($filters['min_price']); ?>" oninput="triggerSearchDelay()" placeholder="0.00" 
                           class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Max Price (Rs.)</label>
                    <input type="number" step="0.01" name="max_price" id="maxPriceInput" value="<?php echo htmlspecialchars($filters['max_price']); ?>" oninput="triggerSearchDelay()" placeholder="0.00" 
                           class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all font-mono">
                </div>

                <!-- Status Indicator -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Stock Level status</label>
                    <select name="stock_status" id="stockStatusSelect" onchange="applyAjaxFilters()" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all cursor-pointer">
                        <option value="">All Stock Levels</option>
                        <option value="instock" <?php echo $filters['stock_status'] === 'instock' ? 'selected' : ''; ?>>In Stock (>5)</option>
                        <option value="lowstock" <?php echo $filters['stock_status'] === 'lowstock' ? 'selected' : ''; ?>>Low Stock (1-5)</option>
                        <option value="outstock" <?php echo $filters['stock_status'] === 'outstock' ? 'selected' : ''; ?>>Out of Stock (0)</option>
                    </select>
                </div>
            </form>

            <div class="flex justify-between items-center mt-5 pt-4 border-t border-slate-100">
                <span class="text-xs text-slate-400 font-medium">Matching Database Query Results: <span id="matching-count" class="text-primary-600 font-bold font-mono"><?php echo $totalItems; ?></span> items</span>
                <div class="flex gap-2">
                    <button type="button" onclick="clearAllFilters()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition-all border border-slate-200">
                        <i class="fa-solid fa-trash-can mr-1.5"></i> Clear Filters
                    </button>
                    <button type="button" onclick="applyAjaxFilters()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition-all">
                        <i class="fa-solid fa-filter mr-1.5"></i> Run Query
                    </button>
                </div>
            </div>
        </div>

        <!-- Live Ajax Swap Table Container -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm transition-all duration-300 relative" id="table-wrapper">
            
            <!-- Table Loader Overlay -->
            <div id="table-loader" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-10 opacity-0 pointer-events-none transition-opacity duration-150">
                <div class="flex flex-col items-center gap-2">
                    <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-[11px] font-bold text-slate-400 tracking-wider uppercase font-sans">Updating Query...</span>
                </div>
            </div>

            <div id="table-container">
                <!-- Data Grid -->
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-[10%]">Image</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-[12%]">Product SKU</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-[12%]">Sample Code</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-[26%]">Product Details</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-[15%]">Retail Price</th>
                                <th class="py-4 px-6 text-xs font-bold text-purple-700 uppercase tracking-wider text-right w-[15%]">B2B Base Price</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-[8%]">Stock</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-[10%]">Status</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-[5%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="py-20 text-center">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                            <i class="fa-solid fa-box-open text-2xl text-slate-400"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-900 mb-1">Catalog empty or no records match</h3>
                                        <p class="text-xs text-slate-500">Change your filter values, search criteria or upload your WooCommerce CSV sheet.</p>
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
                                    
                                    // Render exclusively from local uploads/products/ folder by extracting the filename
                                    if (empty($image)) {
                                        $img_src = 'https://placehold.co/300?text=No+Image';
                                    } else {
                                        $filename = basename($image);
                                        $img_src = APP_URL . '/uploads/products/' . $filename;
                                    }

                                    if ($qty <= 0) {
                                        $statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Out of Stock</span>';
                                    } elseif ($qty <= 5) {
                                        $statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Low Stock</span>';
                                    } else {
                                        $statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>In Stock</span>';
                                    }
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="py-4 px-6 align-top">
                                            <div class="h-12 w-12 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shadow-inner">
                                                <img src="<?php echo $img_src; ?>" class="object-cover w-full h-full" onerror="this.src='https://placehold.co/300?text=Error'">
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 align-top">
                                            <div class="inline-flex items-center px-2 py-1 rounded text-xs font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200 select-all">
                                                <?php echo htmlspecialchars($sku); ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 align-top">
                                            <div class="text-xs font-mono font-bold text-slate-600 select-all">
                                                <?php echo htmlspecialchars($item->sample_code ?? '-'); ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 align-top whitespace-normal">
                                            <div class="text-sm font-bold text-slate-900 mb-1 leading-tight"><?php echo htmlspecialchars($item->name ?? 'Unnamed Item'); ?></div>
                                            <?php if (!empty($item->description)): ?>
                                                <div class="text-xs text-slate-500 line-clamp-2 leading-relaxed" title="<?php echo htmlspecialchars($item->description); ?>">
                                                    <?php echo htmlspecialchars($item->description); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6 text-right align-top">
                                            <div class="text-sm font-bold text-slate-950 font-mono"><?php echo number_format($price, 2); ?></div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">LKR</div>
                                        </td>
                                        <td class="py-4 px-6 text-right align-top bg-purple-50/10">
                                            <div class="text-sm font-bold text-purple-950 font-mono"><?php echo number_format($b2b_price, 2); ?></div>
                                            <div class="text-[10px] text-purple-400 font-bold uppercase tracking-wider mt-0.5">B2B Base (WholesaleX)</div>
                                        </td>
                                        <td class="py-4 px-6 text-center align-top font-mono text-sm">
                                            <span class="font-bold <?php echo $qty <= 0 ? 'text-red-600' : ($qty <= 5 ? 'text-amber-600' : 'text-slate-700'); ?>">
                                                <?php echo $qty; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center align-top">
                                            <?php echo $statusBadge; ?>
                                        </td>
                                        <td class="py-4 px-6 text-right align-top">
                                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-100">
                                                <a href="<?php echo APP_URL; ?>/inventory/edit/<?php echo $item->id; ?>" 
                                                   class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="Edit Catalog Entry">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs font-medium text-slate-500">
                        Displaying <span class="font-bold text-slate-900 font-mono"><?php echo $startIndex; ?></span> to <span class="font-bold text-slate-900 font-mono"><?php echo $endIndex; ?></span> of <span class="font-bold text-slate-900 font-mono"><?php echo $totalItems; ?></span> entries
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Page Size -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-500 font-semibold uppercase">Show Rows:</label>
                            <select onchange="updatePageSize(this.value)" class="text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-primary-500 focus:border-primary-500 focus:outline-none text-slate-700 bg-white shadow-sm cursor-pointer font-mono font-bold">
                                <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10</option>
                                <option value="15" <?php echo $perPage === 15 ? 'selected' : ''; ?>>15</option>
                                <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>

                        <!-- Navigation Drawer -->
                        <nav class="inline-flex -space-x-px rounded-lg shadow-sm border border-slate-200 text-xs font-mono font-bold" aria-label="Pagination">
                            <button type="button" onclick="navigatePage(<?php echo max(1, $currentPage - 1); ?>)" 
                                    class="relative inline-flex items-center rounded-l-lg px-3 py-2 text-slate-500 hover:text-slate-900 bg-white hover:bg-slate-50 focus:outline-none cursor-pointer <?php echo $currentPage <= 1 ? 'opacity-40 pointer-events-none' : ''; ?>">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </button>
                            
                            <?php 
                            $range = 1;
                            $startPage = max(1, $currentPage - $range);
                            $endPage = min($totalPages, $currentPage + $range);

                            if ($startPage > 1) {
                                echo '<button type="button" onclick="navigatePage(1)" class="relative inline-flex items-center px-3 py-2 bg-white text-slate-500 hover:bg-slate-50 cursor-pointer">1</button>';
                                if ($startPage > 2) {
                                    echo '<span class="relative inline-flex items-center px-2 py-2 bg-white text-slate-400">...</span>';
                                }
                            }

                            for ($i = $startPage; $i <= $endPage; $i++) {
                                if ($i === $currentPage) {
                                    echo '<button type="button" class="relative inline-flex items-center bg-primary-600 px-3.5 py-2 text-white border-y border-primary-600 cursor-default">' . $i . '</button>';
                                } else {
                                    echo '<button type="button" onclick="navigatePage(' . $i . ')" class="relative inline-flex items-center px-3.5 py-2 bg-white text-slate-500 hover:bg-slate-50 cursor-pointer">' . $i . '</button>';
                                }
                            }

                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<span class="relative inline-flex items-center px-2 py-2 bg-white text-slate-400">...</span>';
                                }
                                echo '<button type="button" onclick="navigatePage(' . $totalPages . ')" class="relative inline-flex items-center px-3 py-2 bg-white text-slate-500 hover:bg-slate-50 cursor-pointer">' . $totalPages . '</button>';
                            }
                            ?>

                            <button type="button" onclick="navigatePage(<?php echo min($totalPages, $currentPage + 1); ?>)" 
                                    class="relative inline-flex items-center rounded-r-lg px-3 py-2 text-slate-500 hover:text-slate-900 bg-white hover:bg-slate-50 focus:outline-none cursor-pointer <?php echo $currentPage >= $totalPages ? 'opacity-40 pointer-events-none' : ''; ?>">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- WooCommerce CSV Import Overlay Modal (Fully Custom Styled) -->
    <div id="csvImportModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all border border-slate-200">
            <div class="bg-slate-50 border-b border-slate-100 p-6 flex justify-between items-center">
                <div class="flex items-center gap-2.5 text-slate-900">
                    <i class="fa-solid fa-file-csv text-emerald-600 text-xl"></i>
                    <h3 class="text-base font-bold">Import WooCommerce Products</h3>
                </div>
                <button onclick="closeCsvModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            
            <form action="<?php echo APP_URL; ?>/inventory/importCSV" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Select WooCommerce Export CSV</label>
                    <div class="border-2 border-dashed border-slate-200 hover:border-emerald-500 bg-slate-50 rounded-xl p-6 text-center cursor-pointer relative transition-colors duration-150">
                        <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 opacity-0 cursor-pointer">
                        <div class="space-y-2">
                            <div class="h-10 w-10 bg-slate-100 text-emerald-600 rounded-xl flex items-center justify-center mx-auto shadow-inner">
                                <i class="fa-solid fa-file-arrow-up text-lg"></i>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">Drag & drop your CSV file here or <span class="text-emerald-600 hover:underline">browse files</span></p>
                            <p class="text-[10px] text-slate-400 font-medium">Accepts standard .csv exports from your WooCommerce catalog</p>
                        </div>
                    </div>
                </div>

                <!-- Price Mapping Indicators -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 space-y-2.5 text-xs text-indigo-950">
                    <h4 class="font-bold text-indigo-900 flex items-center gap-1"><i class="fa-solid fa-circle-info"></i> Auto Price Mappings</h4>
                    <ul class="space-y-1 text-[11px] list-disc list-inside">
                        <li><span class="font-bold text-indigo-900">Regular price</span> &rarr; Retail Price (LKR)</li>
                        <li><span class="font-bold text-indigo-900">B2B Users Base Price</span> &rarr; Wholesale Price (WholesaleX)</li>
                        <li>Variations are nested automatically under parent SKUs</li>
                    </ul>
                </div>

                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeCsvModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-750 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition">Start Instant Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- AJAX-based real-time search, filter logic, and step-by-step progress importer -->
    <script>
        let searchTimeout = null;

        /**
         * Trigger debounce search update to avoid database flooding while typing
         */
        function triggerSearchDelay() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Reset current page back to 1 on typing to get fresh filter results
                document.getElementById('currentPageInput').value = '1';
                applyAjaxFilters();
            }, 350); // 350ms buffer
        }

        /**
         * Submit form values asynchronously using HTML Fetch
         * Parses the response and updates the table without losing search field focus
         */
        function applyAjaxFilters() {
            const form = document.getElementById('filterForm');
            const loader = document.getElementById('table-loader');
            const searchInput = document.getElementById('searchInput');

            // Capture current focus & cursor selection parameters of searchInput before update
            const hasFocus = (document.activeElement === searchInput);
            const selectionStart = searchInput ? searchInput.selectionStart : 0;
            const selectionEnd = searchInput ? searchInput.selectionEnd : 0;
            
            // Show local loading overlay inside table container
            if (loader) {
                loader.classList.remove('pointer-events-none');
                loader.classList.add('opacity-100');
            }

            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();
            const requestUrl = form.getAttribute('action') + '?' + queryParams;

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

                    // Restore active selection index parameters to make typing fluid without jumps
                    if (hasFocus && searchInput) {
                        searchInput.focus();
                        try {
                            searchInput.setSelectionRange(selectionStart, selectionEnd);
                        } catch (e) {
                            // Safe fallback for browsers with restricted inputs
                        }
                    }
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
            document.getElementById('searchInput').value = '';
            document.getElementById('minPriceInput').value = '';
            document.getElementById('maxPriceInput').value = '';
            document.getElementById('stockStatusSelect').value = '';
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        // Modal Control Functions
        function openCsvModal() {
            document.getElementById('csvImportModal').classList.remove('hidden');
        }

        // Close csv modal helper
        function closeCsvModal() {
            document.getElementById('csvImportModal').classList.add('hidden');
        }
    </script>

</body>
</html>