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
$filters['category_id'] = $filters['category_id'] ?? '';
$categories = $data['categories'] ?? [];
$isCurrentlyAdmin = (strtolower($_SESSION['role'] ?? '') === 'admin');

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
$importResults = $_SESSION['import_results'] ?? null;
if ($importResults) {
    unset($_SESSION['import_results']);
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"Segoe UI"', 'Roboto', 'Helvetica', 'Arial', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', '"Liberation Mono"', '"Courier New"', 'monospace'],
                    },
                    colors: {
                        zinc: {
                            50: '#fafafa',
                            100: '#f4f4f5',
                            200: '#e4e4e7',
                            300: '#d4d4d8',
                            400: '#a1a1aa',
                            500: '#71717a',
                            600: '#52525b',
                            700: '#3f3f46',
                            800: '#27272a',
                            900: '#18181b',
                            950: '#09090b',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.4s ease-out',
                        'slide-up': 'slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1)',
                        'scale-in': 'scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        scaleIn: {
                            '0%': { opacity: '0', transform: 'scale(0.95)' },
                            '100%': { opacity: '1', transform: 'scale(1)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Scrollbar for the table container */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d4d4d8;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a1a1aa;
        }

        /* FORCE SCROLLING ENGINE OVERRIDES */
        html, body {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            height: auto !important;
            min-height: 100vh !important;
            background-color: #fbfbfd; /* Apple light gray */
        }

        /* Glassmorphism Utilities */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        /* TOP NAV HOVER GAP REMOVAL BRIDGE OVERRIDES */
        .group:hover > div,
        .group:hover > ul {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

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

        .btn-press:active {
            transform: scale(0.96);
        }
        
        .table-row-hover:hover td {
            background-color: #f4f4f5; /* zinc-100 */
        }
        
        /* Focus styles */
        input:focus, select:focus {
            box-shadow: 0 0 0 2px rgba(24, 24, 27, 0.1);
        }
    </style>
</head>
<body class="bg-[#fbfbfd] text-zinc-900 font-sans antialiased min-h-screen flex flex-col selection:bg-zinc-900 selection:text-white">

    <!-- Included Unified System Top Menu Bar from Layouts Folder -->
    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Main Workspace Container -->
    <div class="px-6 md:px-12 py-8 max-w-[1600px] mx-auto space-y-8 flex-grow w-full animate-slide-up">
        
        <!-- Page Header & Actions -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 border-b border-zinc-200 pb-6">
            <div class="space-y-1.5">
                <h1 class="text-[32px] font-semibold tracking-tight text-zinc-900 leading-tight">Inventory</h1>
                <p class="text-zinc-500 text-sm font-medium">Manage catalog, track levels, and configure pricing.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="<?php echo APP_URL; ?>/inventory/exportCSV" class="px-4 py-2 bg-white border border-zinc-200 hover:border-zinc-300 text-zinc-700 text-sm font-medium rounded-xl shadow-sm transition-all flex items-center gap-2 btn-press">
                    <i class="fa-solid fa-arrow-up-from-bracket text-zinc-400"></i> Export
                </a>
                <button onclick="openCsvModal()" class="px-4 py-2 bg-white border border-zinc-200 hover:border-zinc-300 text-zinc-700 text-sm font-medium rounded-xl shadow-sm transition-all flex items-center gap-2 btn-press">
                    <i class="fa-solid fa-arrow-down-to-bracket text-zinc-400"></i> Import
                </button>
                <a href="<?php echo APP_URL; ?>/inventory/add" class="px-5 py-2 bg-zinc-900 hover:bg-black text-white text-sm font-medium rounded-xl shadow-md transition-all flex items-center gap-2 btn-press ml-2">
                    <i class="fa-solid fa-plus text-zinc-300"></i> New Product
                </a>
            </div>
        </div>

        <!-- Notification Alerts -->
        <?php if ($flashSuccess || isset($_GET['flash_success'])): ?>
            <div id="flash-success-alert" class="bg-white border border-zinc-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-fade-in relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-black"></div>
                <div class="bg-zinc-100 text-zinc-900 p-2 rounded-full shrink-0 flex items-center justify-center w-8 h-8 ml-2">
                    <i class="fa-solid fa-check text-sm"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-zinc-800 text-sm font-medium"><?php echo htmlspecialchars($flashSuccess ?? $_GET['flash_success'] ?? ''); ?></p>
                </div>
                <button onclick="document.getElementById('flash-success-alert').style.display='none'" class="text-zinc-400 hover:text-zinc-600 transition-colors cursor-pointer p-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div id="flash-error-alert" class="bg-white border border-zinc-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-fade-in relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                <div class="bg-red-50 text-red-600 p-2 rounded-full shrink-0 flex items-center justify-center w-8 h-8 ml-2">
                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-zinc-800 text-sm font-medium"><?php echo htmlspecialchars($flashError); ?></p>
                </div>
                <button onclick="document.getElementById('flash-error-alert').style.display='none'" class="text-zinc-400 hover:text-zinc-600 transition-colors cursor-pointer p-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($importResults): ?>
            <div id="import-results-panel" class="bg-white border border-zinc-200 rounded-2xl p-6 shadow-sm space-y-6 animate-scale-in">
                <div class="flex justify-between items-center pb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-zinc-100 text-zinc-900 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-zinc-900">Import Completed</h3>
                            <p class="text-sm text-zinc-500">Processed records from your inventory CSV</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('import-results-panel').style.display='none'" class="text-zinc-400 hover:text-zinc-600 bg-zinc-50 hover:bg-zinc-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-zinc-50 border border-zinc-100 rounded-2xl p-4">
                        <div class="text-xs font-medium text-zinc-500 mb-2 uppercase tracking-wider">New Added</div>
                        <div class="text-2xl font-semibold text-zinc-900"><?= $importResults['added']; ?></div>
                    </div>
                    <div class="bg-zinc-50 border border-zinc-100 rounded-2xl p-4">
                        <div class="text-xs font-medium text-zinc-500 mb-2 uppercase tracking-wider">Updated</div>
                        <div class="text-2xl font-semibold text-zinc-900"><?= $importResults['updated']; ?></div>
                    </div>
                    <div class="bg-zinc-50 border border-zinc-100 rounded-2xl p-4">
                        <div class="text-xs font-medium text-zinc-500 mb-2 uppercase tracking-wider">Relations</div>
                        <div class="text-2xl font-semibold text-zinc-900"><?= count($importResults['success_logs']); ?></div>
                    </div>
                    <div class="bg-zinc-50 border border-zinc-100 rounded-2xl p-4">
                        <div class="text-xs font-medium text-zinc-500 mb-2 uppercase tracking-wider">Errors</div>
                        <div class="text-2xl font-semibold <?php echo count($importResults['errors']) > 0 ? 'text-red-500' : 'text-zinc-900'; ?>"><?= count($importResults['errors']); ?></div>
                    </div>
                </div>

                <?php if (!empty($importResults['success_logs'])): ?>
                    <div class="space-y-2">
                        <h4 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Activity Log</h4>
                        <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100 max-h-40 overflow-y-auto custom-scrollbar">
                            <ul class="text-xs text-zinc-600 font-mono space-y-1">
                                <?php foreach ($importResults['success_logs'] as $log): ?>
                                    <li class="flex items-start gap-2">
                                        <span class="text-zinc-400 mt-0.5">-</span>
                                        <span><?= htmlspecialchars($log); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($importResults['errors'])): ?>
                    <div class="space-y-3">
                        <h4 class="text-xs font-semibold text-red-500 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i> Issues Encountered
                        </h4>
                        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                            <div class="max-h-[300px] overflow-y-auto custom-scrollbar">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 sticky top-0 z-10 border-b border-zinc-200">
                                        <tr>
                                            <th class="px-4 py-3 font-medium text-zinc-500">Row</th>
                                            <th class="px-4 py-3 font-medium text-zinc-500">SKU</th>
                                            <th class="px-4 py-3 font-medium text-zinc-500">Product</th>
                                            <th class="px-4 py-3 font-medium text-zinc-500">Error Details</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100">
                                        <?php foreach ($importResults['errors'] as $err): ?>
                                            <?php if (is_array($err)): ?>
                                                <tr>
                                                    <td class="px-4 py-3 font-mono text-zinc-500"><?= htmlspecialchars($err['row'] ?? '-'); ?></td>
                                                    <td class="px-4 py-3 font-mono text-zinc-700"><?= htmlspecialchars($err['sku'] ?? '-'); ?></td>
                                                    <td class="px-4 py-3 font-medium text-zinc-900 truncate max-w-[150px]"><?= htmlspecialchars($err['name'] ?? '-'); ?></td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex flex-col gap-1">
                                                            <?php foreach ($err['messages'] as $msg): ?>
                                                                <span class="text-red-600">• <?= htmlspecialchars($msg); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <tr>
                                                    <td class="px-4 py-3 font-mono text-zinc-500">-</td>
                                                    <td class="px-4 py-3 font-mono text-zinc-700">-</td>
                                                    <td class="px-4 py-3 font-medium text-zinc-900">-</td>
                                                    <td class="px-4 py-3 text-red-600"><?= htmlspecialchars($err); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Summary Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 transition-colors group-hover:text-zinc-700">Total Items</p>
                        <h3 class="text-3xl font-semibold text-zinc-900 mt-2 tracking-tight" id="stat-total-items"><?php echo number_format($stats->total_items); ?></h3>
                    </div>
                    <div class="w-10 h-10 bg-zinc-50 text-zinc-600 rounded-full flex items-center justify-center border border-zinc-100 transition-colors group-hover:bg-zinc-100">
                        <i class="fa-solid fa-box"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-zinc-500">
                    <span class="w-2 h-2 rounded-full bg-zinc-300"></span> Active catalog SKU records
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 transition-colors group-hover:text-zinc-700">Low Stock</p>
                        <h3 class="text-3xl font-semibold text-zinc-900 mt-2 tracking-tight" id="stat-low-stock"><?php echo number_format($stats->low_stock_count); ?></h3>
                    </div>
                    <div class="w-10 h-10 bg-zinc-50 text-zinc-600 rounded-full flex items-center justify-center border border-zinc-100 transition-colors group-hover:bg-zinc-100">
                        <i class="fa-solid fa-arrow-trend-down"></i>
                    </div>
                </div>
                 <div class="mt-4 flex items-center gap-1.5 text-xs text-zinc-500">
                    <span class="w-2 h-2 rounded-full bg-black"></span> Between 1-5 units available
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 transition-colors group-hover:text-zinc-700">Out of Stock</p>
                        <h3 class="text-3xl font-semibold text-zinc-900 mt-2 tracking-tight" id="stat-out-of-stock"><?php echo number_format($stats->out_of_stock_count); ?></h3>
                    </div>
                    <div class="w-10 h-10 bg-zinc-50 text-zinc-600 rounded-full flex items-center justify-center border border-zinc-100 transition-colors group-hover:bg-zinc-100">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
                 <div class="mt-4 flex items-center gap-1.5 text-xs text-zinc-500">
                    <span class="w-2 h-2 rounded-full bg-zinc-300"></span> Requires immediate action
                </div>
            </div>
        </div>

        <!-- Filter Form Controls -->
        <div class="bg-white rounded-[20px] border border-zinc-200 p-2 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            
            <form id="filterForm" action="<?php echo APP_URL; ?>/inventory" method="GET" onsubmit="event.preventDefault(); applyAjaxFilters();" class="flex-grow flex flex-col md:flex-row items-center w-full">
                <input type="hidden" name="page" id="currentPageInput" value="<?php echo $currentPage; ?>">
                <input type="hidden" name="per_page" id="perPageInput" value="<?php echo $perPage; ?>">

                <!-- Search field -->
                <div class="relative flex-grow w-full md:w-auto md:min-w-[250px] border-b md:border-b-0 md:border-r border-zinc-100 px-2 py-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400 text-sm"></i>
                    <input type="text" name="search" id="searchInput" value="<?php echo htmlspecialchars($filters['search']); ?>" oninput="triggerSearchDelay()" placeholder="Search by SKU or Name..." 
                           class="w-full pl-9 pr-4 py-2 bg-transparent text-sm text-zinc-900 focus:outline-none placeholder-zinc-400 font-medium">
                </div>

                <div class="flex flex-wrap items-center w-full md:w-auto flex-grow px-2 py-1 gap-2 md:gap-0">
                    <!-- Pricing -->
                    <div class="flex items-center border-b md:border-b-0 md:border-r border-zinc-100 w-full md:w-auto">
                        <input type="number" step="0.01" name="min_price" id="minPriceInput" value="<?php echo htmlspecialchars($filters['min_price']); ?>" oninput="triggerSearchDelay()" placeholder="Min Price" 
                               class="w-24 px-3 py-2 bg-transparent text-sm text-zinc-900 focus:outline-none placeholder-zinc-400 font-mono">
                        <span class="text-zinc-300">-</span>
                        <input type="number" step="0.01" name="max_price" id="maxPriceInput" value="<?php echo htmlspecialchars($filters['max_price']); ?>" oninput="triggerSearchDelay()" placeholder="Max Price" 
                               class="w-24 px-3 py-2 bg-transparent text-sm text-zinc-900 focus:outline-none placeholder-zinc-400 font-mono">
                    </div>

                    <!-- Category -->
                    <div class="border-b md:border-b-0 md:border-r border-zinc-100 w-full md:w-auto px-1">
                        <select name="category_id" id="categorySelect" onchange="applyAjaxFilters()" class="w-full px-3 py-2 bg-transparent text-sm text-zinc-700 focus:outline-none cursor-pointer font-medium">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->id; ?>" <?php echo (string)$filters['category_id'] === (string)$cat->id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="w-full md:w-auto px-1">
                        <select name="stock_status" id="stockStatusSelect" onchange="applyAjaxFilters()" class="w-full px-3 py-2 bg-transparent text-sm text-zinc-700 focus:outline-none cursor-pointer font-medium">
                            <option value="">Any Status</option>
                            <option value="instock" <?php echo $filters['stock_status'] === 'instock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="lowstock" <?php echo $filters['stock_status'] === 'lowstock' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="outstock" <?php echo $filters['stock_status'] === 'outstock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="flex items-center gap-3 pr-2 whitespace-nowrap">
                <span class="text-xs text-zinc-500 font-medium hidden lg:inline-block"><span id="matching-count" class="text-zinc-900 font-semibold"><?php echo $totalItems; ?></span> items</span>
                <button type="button" onclick="clearAllFilters()" class="w-8 h-8 flex items-center justify-center bg-zinc-100 hover:bg-zinc-200 text-zinc-600 rounded-full transition-all" title="Clear Filters">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Live Ajax Swap Table Container -->
        <div class="bg-white rounded-[24px] border border-zinc-200 shadow-sm relative overflow-hidden" id="table-wrapper">
            
            <!-- Table Loader Overlay -->
            <div id="table-loader" class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-20 opacity-0 pointer-events-none transition-opacity duration-300">
                <div class="bg-white border border-zinc-200 shadow-lg rounded-[16px] p-4 flex items-center gap-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-zinc-900"></i>
                    <span class="text-sm font-medium text-zinc-700">Updating...</span>
                </div>
            </div>

            <div id="table-container">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr class="bg-zinc-50/80 border-b border-zinc-200">
                                <th class="py-4 px-5 text-center w-[4%]">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="rounded border-zinc-300 text-black focus:ring-black cursor-pointer w-[18px] h-[18px] accent-black transition-all">
                                </th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider w-[8%]">Image</th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider w-[14%]">SKU</th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider w-[28%]">Product</th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider text-right w-[14%]">Retail (LKR)</th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider text-right w-[14%]">B2B Base (LKR)</th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider text-center w-[8%]">Stock</th>
                                <th class="py-4 px-5 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider text-center w-[8%]">Status</th>
                                <th class="py-4 px-5 text-right w-[5%]"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="9" class="py-24 text-center">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-[20px] bg-zinc-50 border border-zinc-100 mb-4">
                                            <i class="fa-solid fa-cube text-2xl text-zinc-300"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-zinc-900 mb-1">No products found</h3>
                                        <p class="text-sm text-zinc-500">Adjust your filters or add a new product.</p>
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
                                    
                                    if (empty($image)) {
                                        $img_src = 'https://placehold.co/100x100/f4f4f5/a1a1aa?text=No+Img';
                                    } else {
                                        $filename = basename($image);
                                        $img_src = APP_URL . '/uploads/products/' . $filename;
                                    }

                                    if ($qty <= 0) {
                                        $statusDot = '<span class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span>';
                                        $statusText = 'Out';
                                        $stockColor = 'text-zinc-400';
                                    } elseif ($qty <= 5) {
                                        $statusDot = '<span class="w-1.5 h-1.5 rounded-full bg-black"></span>';
                                        $statusText = 'Low';
                                        $stockColor = 'text-zinc-900';
                                    } else {
                                        $statusDot = '<span class="w-1.5 h-1.5 rounded-full bg-zinc-900"></span>';
                                        $statusText = 'In Stock';
                                        $stockColor = 'text-zinc-900';
                                    }
                                    ?>
                                    <tr class="table-row-hover transition-colors group">
                                        <td class="py-4 px-5 text-center align-middle">
                                            <input type="checkbox" name="selected_items[]" value="<?php echo $item->id; ?>" onchange="updateSelection()" class="item-select-checkbox rounded border-zinc-300 text-black focus:ring-black cursor-pointer w-[18px] h-[18px] accent-black transition-all">
                                        </td>
                                        <td class="py-4 px-5 align-middle">
                                            <div class="h-10 w-10 rounded-[12px] bg-zinc-50 border border-zinc-200 overflow-hidden shrink-0">
                                                <img src="<?php echo $img_src; ?>" class="object-cover w-full h-full" onerror="this.src='https://placehold.co/100x100/f4f4f5/a1a1aa?text=Err'">
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 align-middle">
                                            <div class="text-sm font-mono text-zinc-900 select-all font-medium"><?php echo htmlspecialchars($sku); ?></div>
                                            <?php if(!empty($item->sample_code)): ?>
                                            <div class="text-[11px] font-mono text-zinc-500 mt-0.5 tracking-tight">Ref: <?php echo htmlspecialchars($item->sample_code); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-5 align-middle whitespace-normal">
                                            <div class="text-sm font-medium text-zinc-900 leading-snug truncate max-w-[280px]" title="<?php echo htmlspecialchars($item->name ?? 'Unnamed Item'); ?>">
                                                <?php echo htmlspecialchars($item->name ?? 'Unnamed Item'); ?>
                                            </div>
                                            <?php if (!empty($item->description)): ?>
                                                <div class="text-[13px] text-zinc-500 truncate max-w-[280px] mt-0.5" title="<?php echo htmlspecialchars($item->description); ?>">
                                                    <?php echo htmlspecialchars($item->description); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-5 text-right align-middle">
                                            <div class="text-sm font-medium text-zinc-900 font-mono"><?php echo number_format($price, 2); ?></div>
                                        </td>
                                        <td class="py-4 px-5 text-right align-middle">
                                            <div class="text-sm font-medium text-zinc-500 font-mono"><?php echo number_format($b2b_price, 2); ?></div>
                                        </td>
                                        <td class="py-4 px-5 text-center align-middle font-mono text-sm">
                                            <span class="<?php echo $stockColor; ?>">
                                                <?php echo $qty; ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-5 text-center align-middle">
                                            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-white border border-zinc-200 text-[11px] font-semibold text-zinc-700 uppercase tracking-wider shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                                <?php echo $statusDot; ?>
                                                <?php echo $statusText; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 text-right align-middle">
                                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <a href="<?php echo APP_URL; ?>/stockledger/product/<?php echo $item->id; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-colors" title="Ledger">
                                                    <i class="fa-solid fa-chart-line text-[13px]"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/inventory/edit/<?php echo $item->id; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-colors" title="Edit">
                                                    <i class="fa-solid fa-pen text-[13px]"></i>
                                                </a>
                                                <button type="button" onclick="confirmDelete(<?php echo $item->id; ?>, '<?php echo htmlspecialchars(addslashes($item->name)); ?>')" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                                    <i class="fa-solid fa-trash text-[13px]"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="px-6 py-4 border-t border-zinc-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm font-medium text-zinc-500">
                        <span class="font-semibold text-zinc-900"><?php echo $startIndex; ?></span> - <span class="font-semibold text-zinc-900"><?php echo $endIndex; ?></span> of <span class="font-semibold text-zinc-900"><?php echo $totalItems; ?></span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-zinc-500 font-medium">Rows per page:</label>
                            <select onchange="updatePageSize(this.value)" class="text-sm bg-transparent font-medium text-zinc-900 focus:outline-none cursor-pointer">
                                <option value="10" <?php echo $perPage === 10 ? 'selected' : ''; ?>>10</option>
                                <option value="15" <?php echo $perPage === 15 ? 'selected' : ''; ?>>15</option>
                                <option value="25" <?php echo $perPage === 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $perPage === 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $perPage === 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>

                        <nav class="flex items-center gap-1" aria-label="Pagination">
                            <button type="button" onclick="navigatePage(<?php echo max(1, $currentPage - 1); ?>)" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 transition-colors <?php echo $currentPage <= 1 ? 'opacity-30 pointer-events-none' : ''; ?>">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            
                            <?php 
                            $range = 1;
                            $startPage = max(1, $currentPage - $range);
                            $endPage = min($totalPages, $currentPage + $range);

                            if ($startPage > 1) {
                                echo '<button type="button" onclick="navigatePage(1)" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium text-zinc-500 hover:bg-zinc-100 transition-colors">1</button>';
                                if ($startPage > 2) echo '<span class="w-8 h-8 flex items-center justify-center text-zinc-400">...</span>';
                            }

                            for ($i = $startPage; $i <= $endPage; $i++) {
                                if ($i === $currentPage) {
                                    echo '<button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold bg-zinc-900 text-white cursor-default shadow-sm">' . $i . '</button>';
                                } else {
                                    echo '<button type="button" onclick="navigatePage(' . $i . ')" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 transition-colors">' . $i . '</button>';
                                }
                            }

                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) echo '<span class="w-8 h-8 flex items-center justify-center text-zinc-400">...</span>';
                                echo '<button type="button" onclick="navigatePage(' . $totalPages . ')" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium text-zinc-500 hover:bg-zinc-100 transition-colors">' . $totalPages . '</button>';
                            }
                            ?>

                            <button type="button" onclick="navigatePage(<?php echo min($totalPages, $currentPage + 1); ?>)" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 transition-colors <?php echo $currentPage >= $totalPages ? 'opacity-30 pointer-events-none' : ''; ?>">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Combined Import Overlay Modal (Apple Style) -->
    <div id="csvImportModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeCsvModal()"></div>
        <div class="bg-white rounded-[24px] shadow-2xl max-w-lg w-full overflow-hidden transform transition-all relative z-10 animate-scale-in">
            <!-- Modal Header -->
            <div class="px-6 py-5 flex justify-between items-center border-b border-zinc-100 bg-white">
                <h3 class="text-lg font-semibold text-zinc-900">Import Catalog</h3>
                <button onclick="closeCsvModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-900 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6">
                <form action="<?php echo APP_URL; ?>/inventory/importERPCSV" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-900">Select CSV File</label>
                        <label class="flex flex-col items-center justify-center w-full h-36 border border-dashed border-zinc-300 rounded-2xl cursor-pointer bg-zinc-50 hover:bg-zinc-100 hover:border-zinc-400 transition-colors group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fa-solid fa-file-csv text-2xl text-zinc-400 group-hover:text-zinc-600 mb-3 transition-colors"></i>
                                <p class="text-sm font-medium text-zinc-700">Click to upload or drag and drop</p>
                                <p class="text-xs text-zinc-500 mt-1">Standard ERP CSV format</p>
                            </div>
                            <input type="file" name="csv_file" accept=".csv" required class="hidden" id="csvFileInput" onchange="updateFileName(this)">
                        </label>
                        <div id="selectedFileName" class="text-sm font-medium text-zinc-900 text-center hidden mt-2 py-2 px-3 bg-zinc-100 rounded-xl"></div>
                    </div>

                    <div class="bg-zinc-50 rounded-2xl p-4 text-xs text-zinc-600 leading-relaxed border border-zinc-100">
                        <span class="font-semibold text-zinc-900 block mb-1">Smart Mapping</span>
                        Matches existing products using the <span class="font-mono bg-zinc-200 px-1 py-0.5 rounded text-[10px]">SKU</span> column. Category, warehouse, or vendor names are created automatically if they do not exist.
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeCsvModal()" class="flex-1 py-2.5 bg-white border border-zinc-200 text-zinc-700 text-sm font-medium rounded-xl hover:bg-zinc-50 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 py-2.5 bg-zinc-900 text-white text-sm font-medium rounded-xl hover:bg-black shadow-md transition-colors btn-press">Start Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Delete Confirmation Modal -->
    <div id="deleteProductModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div class="bg-white rounded-[24px] shadow-2xl max-w-sm w-full overflow-hidden transform transition-all relative z-10 animate-scale-in text-center">
            
            <form id="deleteProductForm" onsubmit="submitDeleteProduct(event)" class="p-6 space-y-6">
                <input type="hidden" id="deleteItemId" name="item_id">
                
                <div class="w-14 h-14 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-2 border border-red-100">
                    <i class="fa-solid fa-trash-can text-xl"></i>
                </div>
                
                <div class="space-y-1">
                    <h3 class="text-[20px] font-semibold text-zinc-900 tracking-tight">Delete Product?</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">
                        Are you sure you want to delete <strong id="deleteItemName" class="text-zinc-900"></strong>? This cannot be undone.
                    </p>
                </div>

                <div id="deleteErrorContainer" class="hidden p-3 bg-red-50 text-red-600 text-xs rounded-xl font-medium text-left border border-red-100"></div>

                <?php if (!$isCurrentlyAdmin): ?>
                    <div class="space-y-3 text-left bg-zinc-50 p-4 rounded-2xl border border-zinc-100">
                        <p class="text-[13px] font-medium text-zinc-700 flex items-center gap-2">
                            <i class="fa-solid fa-lock text-zinc-400"></i> Admin authorization required
                        </p>
                        <input type="text" name="admin_username" id="deleteAdminUsername" required placeholder="Admin username" 
                               class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black/5 transition-all">
                        <input type="password" name="password" id="deleteAdminPassword" required placeholder="Admin password" 
                               class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black/5 transition-all">
                    </div>
                <?php endif; ?>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 py-2.5 bg-zinc-100 text-zinc-700 text-sm font-medium rounded-xl hover:bg-zinc-200 transition-colors">Cancel</button>
                    <button type="submit" id="deleteSubmitBtn" class="flex-1 py-2.5 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 shadow-md transition-colors flex items-center justify-center gap-2 btn-press">
                        <span id="deleteBtnSpinner" class="hidden"><i class="fa-solid fa-spinner animate-spin"></i></span>
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Floating Bulk Action Toolbar -->
    <div id="bulkEditToolbar" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-[rgba(30,30,30,0.85)] backdrop-blur-xl text-white px-5 py-3 rounded-full shadow-2xl z-40 hidden flex items-center justify-between gap-6 animate-slide-up w-max max-w-[90vw] border border-white/10">
        <div class="flex items-center gap-3 pl-1">
            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-white text-black text-[11px] font-bold font-mono shadow-sm" id="selectedCountBadge">0</span>
            <span class="text-[13px] font-medium text-zinc-300">selected</span>
        </div>
        <div class="flex items-center gap-2 pr-1">
            <button type="button" onclick="clearSelection()" class="px-3 py-1.5 hover:bg-white/10 text-zinc-300 rounded-full text-[13px] font-medium transition-colors">
                Cancel
            </button>
            <button type="button" onclick="openBulkEditModal()" class="px-4 py-1.5 bg-white text-black rounded-full text-[13px] font-semibold shadow-sm hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i> Edit
            </button>
        </div>
    </div>

    <!-- Product Bulk Edit Modal -->
    <div id="bulkEditModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeBulkEditModal()"></div>
        <div class="bg-white rounded-[24px] shadow-2xl max-w-md w-full overflow-hidden transform transition-all relative z-10 animate-scale-in">
            
            <div class="px-6 py-5 flex justify-between items-center border-b border-zinc-100 bg-white">
                <h3 class="text-lg font-semibold text-zinc-900">Bulk Edit</h3>
                <button onclick="closeBulkEditModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-900 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="bulkEditForm" onsubmit="submitBulkEdit(event)" class="p-6 space-y-5">
                <p class="text-sm text-zinc-600">
                    Editing <strong id="bulkSelectedCount" class="text-zinc-900 font-mono bg-zinc-100 px-1.5 py-0.5 rounded-md text-[13px]">0</strong> products. Check a field to apply changes.
                </p>

                <div id="bulkEditErrorContainer" class="hidden p-3 bg-red-50 text-red-600 text-xs rounded-xl font-medium border border-red-100"></div>

                <!-- 1. Category -->
                <div class="border border-zinc-200 rounded-2xl p-4 bg-white space-y-3 transition-colors hover:border-zinc-300 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <label class="flex items-center gap-2 text-[13px] font-semibold text-zinc-900 cursor-pointer uppercase tracking-wider">
                        <input type="checkbox" name="update_category" value="1" id="bulkUpdateCategory" onchange="toggleBulkField('category')" class="rounded border-zinc-300 text-black focus:ring-black cursor-pointer w-[18px] h-[18px] accent-black">
                        Update Category
                    </label>
                    <select name="category_id" id="bulkCategorySelect" disabled class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black/5 transition-all disabled:opacity-50 appearance-none font-medium text-zinc-700">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. Selling Price -->
                <div class="border border-zinc-200 rounded-2xl p-4 bg-white space-y-3 transition-colors hover:border-zinc-300 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <label class="flex items-center gap-2 text-[13px] font-semibold text-zinc-900 cursor-pointer uppercase tracking-wider">
                        <input type="checkbox" name="update_selling_price" value="1" id="bulkUpdateSellingPrice" onchange="toggleBulkField('selling_price')" class="rounded border-zinc-300 text-black focus:ring-black cursor-pointer w-[18px] h-[18px] accent-black">
                        Update Retail Price
                    </label>
                    <div class="flex gap-2">
                        <select name="selling_price_type" id="bulkSellingPriceType" disabled class="w-[40%] px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black/5 transition-all disabled:opacity-50 appearance-none font-medium text-zinc-700">
                            <option value="flat">Set Value</option>
                            <option value="pct_inc">+ Percent</option>
                            <option value="pct_dec">- Percent</option>
                        </select>
                        <input type="number" step="0.01" name="selling_price_val" id="bulkSellingPriceVal" disabled placeholder="Value" class="w-[60%] px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black/5 transition-all disabled:opacity-50 font-mono font-medium text-zinc-900">
                    </div>
                </div>

                <!-- 3. Status -->
                <div class="border border-zinc-200 rounded-2xl p-4 bg-white space-y-3 transition-colors hover:border-zinc-300 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                    <label class="flex items-center gap-2 text-[13px] font-semibold text-zinc-900 cursor-pointer uppercase tracking-wider">
                        <input type="checkbox" name="update_status" value="1" id="bulkUpdateStatus" onchange="toggleBulkField('status')" class="rounded border-zinc-300 text-black focus:ring-black cursor-pointer w-[18px] h-[18px] accent-black">
                        Update Status
                    </label>
                    <select name="status" id="bulkStatusSelect" disabled class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-black/5 transition-all disabled:opacity-50 appearance-none font-medium text-zinc-700">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-3">
                    <button type="button" onclick="closeBulkEditModal()" class="flex-1 py-2.5 bg-zinc-100 text-zinc-700 text-sm font-medium rounded-xl hover:bg-zinc-200 transition-colors">Cancel</button>
                    <button type="submit" id="bulkSubmitBtn" class="flex-1 py-2.5 bg-zinc-900 text-white text-sm font-medium rounded-xl hover:bg-black shadow-md transition-colors flex items-center justify-center gap-2 btn-press">
                        <span id="bulkBtnSpinner" class="hidden"><i class="fa-solid fa-spinner animate-spin"></i></span>
                        Apply Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AJAX & Logic Script (Unchanged internally, UI tweaks only) -->
    <script>
        function updateFileName(input) {
            const display = document.getElementById('selectedFileName');
            if(input.files && input.files.length > 0) {
                display.textContent = input.files[0].name;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        }

        let searchTimeout = null;

        function triggerSearchDelay() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('currentPageInput').value = '1';
                applyAjaxFilters();
            }, 350);
        }

        function applyAjaxFilters() {
            const form = document.getElementById('filterForm');
            const loader = document.getElementById('table-loader');

            if (loader) {
                loader.classList.remove('pointer-events-none', 'opacity-0');
                loader.classList.add('opacity-100');
            }

            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();
            const requestUrl = form.getAttribute('action') + '?' + queryParams;

            fetch(requestUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.getElementById('table-container');
                    const oldTable = document.getElementById('table-container');
                    if (newTable && oldTable) {
                        oldTable.innerHTML = newTable.innerHTML;
                    }

                    const updateStat = (id) => {
                        const newVal = doc.getElementById(id);
                        const oldVal = document.getElementById(id);
                        if (newVal && oldVal) oldVal.textContent = newVal.textContent;
                    };
                    updateStat('stat-total-items');
                    updateStat('stat-low-stock');
                    updateStat('stat-out-of-stock');
                    updateStat('matching-count');

                    window.history.pushState({ path: requestUrl }, '', requestUrl);
                    clearSelection();
                })
                .catch(err => console.error(err))
                .finally(() => {
                    if (loader) {
                        loader.classList.add('pointer-events-none', 'opacity-0');
                        loader.classList.remove('opacity-100');
                    }
                });
        }

        function navigatePage(pageNum) {
            document.getElementById('currentPageInput').value = pageNum;
            applyAjaxFilters();
        }

        function updatePageSize(size) {
            document.getElementById('perPageInput').value = size;
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        function clearAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('minPriceInput').value = '';
            document.getElementById('maxPriceInput').value = '';
            document.getElementById('stockStatusSelect').value = '';
            document.getElementById('categorySelect').value = '';
            document.getElementById('currentPageInput').value = '1';
            applyAjaxFilters();
        }

        function openCsvModal() { document.getElementById('csvImportModal').classList.remove('hidden'); }
        function closeCsvModal() { document.getElementById('csvImportModal').classList.add('hidden'); }

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
            if (spinner) spinner.classList.remove('hidden');
            if (errorContainer) errorContainer.classList.add('hidden');

            const formData = new FormData(form);

            fetch('<?php echo APP_URL; ?>/inventory/delete/' + activeDeleteId, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    applyAjaxFilters();
                } else {
                    if (errorContainer) {
                        errorContainer.textContent = data.error || 'Authorization failed.';
                        errorContainer.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                if (errorContainer) {
                    errorContainer.textContent = err.message || 'An error occurred.';
                    errorContainer.classList.remove('hidden');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
            });
        }

        // BULK EDIT ENGINE
        function toggleSelectAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateSelection();
        }

        function updateSelection() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            const selectedIds = [];
            checkboxes.forEach(cb => {
                if (cb.checked) selectedIds.push(cb.value);
            });

            const toolbar = document.getElementById('bulkEditToolbar');
            const countBadge = document.getElementById('selectedCountBadge');
            const selectAllCb = document.getElementById('selectAllCheckbox');

            if (selectedIds.length > 0) {
                if (countBadge) countBadge.textContent = selectedIds.length;
                if (toolbar) toolbar.classList.remove('hidden');
                if (selectAllCb) selectAllCb.checked = (selectedIds.length === checkboxes.length);
            } else {
                if (toolbar) toolbar.classList.add('hidden');
                if (selectAllCb) selectAllCb.checked = false;
            }
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.item-select-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
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
            
            ['Category', 'SellingPrice', 'Status'].forEach(field => {
                const cb = document.getElementById(`bulkUpdate${field}`);
                if(cb) { cb.checked = false; toggleBulkField(field.toLowerCase().replace('sellingprice', 'selling_price')); }
            });

            document.getElementById('bulkEditErrorContainer').classList.add('hidden');
            document.getElementById('bulkEditModal').classList.remove('hidden');
        }

        function closeBulkEditModal() { document.getElementById('bulkEditModal').classList.add('hidden'); }

        function toggleBulkField(field) {
            let id = 'bulkUpdateCategory';
            if (field === 'selling_price') id = 'bulkUpdateSellingPrice';
            else if (field === 'status') id = 'bulkUpdateStatus';
            
            const checkbox = document.getElementById(id);
            if (!checkbox) return;
            const isChecked = checkbox.checked;
            
            if (field === 'category') {
                document.getElementById('bulkCategorySelect').disabled = !isChecked;
            } else if (field === 'selling_price') {
                document.getElementById('bulkSellingPriceType').disabled = !isChecked;
                document.getElementById('bulkSellingPriceVal').disabled = !isChecked;
            } else if (field === 'status') {
                document.getElementById('bulkStatusSelect').disabled = !isChecked;
            }
        }

        function submitBulkEdit(e) {
            e.preventDefault();

            const form = document.getElementById('bulkEditForm');
            const submitBtn = document.getElementById('bulkSubmitBtn');
            const spinner = document.getElementById('bulkBtnSpinner');
            const errorContainer = document.getElementById('bulkEditErrorContainer');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            if (errorContainer) errorContainer.classList.add('hidden');

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
                        errorContainer.textContent = data.error || 'Failed. Please try again.';
                        errorContainer.classList.remove('hidden');
                    }
                }
            })
            .catch(err => {
                if (errorContainer) {
                    errorContainer.textContent = err.message || 'Error occurred.';
                    errorContainer.classList.remove('hidden');
                }
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
            });
        }
    </script>

<?php include '../app/Views/layouts/resilient_loader.php'; ?>
</body>
</html>