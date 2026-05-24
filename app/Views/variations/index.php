<?php
$synced_attributes = $data['synced_attributes'] ?? [];
$title = $data['title'] ?? 'Attribute & Terms Management';

// Calculate analytics safely
$total_attributes = count($synced_attributes);
$total_terms = 0;
foreach ($synced_attributes as $attr) {
    if (isset($attr->terms) && is_array($attr->terms)) {
        $total_terms += count($attr->terms);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Curtiss ERP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
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

    <!-- Main Content Area -->
    <div class="p-4 md:p-8 lg:p-10 max-w-[1600px] w-full mx-auto space-y-8 flex-grow">
        
        <!-- Page Header & Actions -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-diagram-project text-xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Attribute Management</h1>
                </div>
                <p class="text-slate-500 text-sm ml-13">Configure and synchronize global product attribute taxonomies and terms directly with WooCommerce store attributes.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button onclick="syncAllAttributes()" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-purple-500/30 transition-all duration-200 flex items-center gap-2 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-rotate text-sm" id="global-sync-icon"></i> <span>Fetch WooCommerce Attributes</span>
                </button>
                <button onclick="openAddAttributeModal()" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-primary-500/30 transition-all duration-200 flex items-center gap-2 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add New Attribute
                </button>
            </div>
        </div>

        <!-- Overview Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Total Global Attributes -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-20 h-20 bg-purple-50 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Global Attributes</p>
                        <h3 class="text-2xl font-black text-slate-800" id="metric-total-attributes"><?php echo $total_attributes; ?> Attributes</h3>
                    </div>
                    <div class="p-2.5 bg-purple-50 text-purple-600 rounded-lg">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Dynamic Terms -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden">
                 <div class="absolute -right-6 -top-6 w-20 h-20 bg-indigo-50 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Synced Terms</p>
                        <h3 class="text-2xl font-black text-indigo-600" id="metric-total-terms"><?php echo $total_terms; ?> Terms</h3>
                    </div>
                    <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg">
                        <i class="fa-solid fa-diagram-project"></i>
                    </div>
                </div>
            </div>

            <!-- Card 3: Connection Health -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-20 h-20 bg-emerald-50 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Woo REST connection</p>
                        <h3 class="text-sm font-black text-slate-800 mt-2 flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span> Live Integration
                        </h3>
                    </div>
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attribute & Terms Board Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Panel: Attributes List (1/3 Width) -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4 shadow-sm h-fit">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Attributes Registry</h4>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold font-mono"><?php echo $total_attributes; ?></span>
                </div>

                <div class="space-y-2">
                    <?php if (empty($synced_attributes)): ?>
                        <p class="text-xs text-slate-400 italic text-center py-6">No attributes found. Click 'Fetch WooCommerce Attributes' to sync.</p>
                    <?php else: ?>
                        <?php foreach ($synced_attributes as $index => $attr): ?>
                            <div onclick="selectAttribute(<?php echo $attr->id; ?>)" id="attr-card-<?php echo $attr->id; ?>" 
                                 class="attr-selection-card p-4 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 rounded-xl cursor-pointer transition-all flex justify-between items-center <?php echo $index === 0 ? 'bg-indigo-50/70 border-indigo-200 ring-2 ring-indigo-500/10' : ''; ?>">
                                <div>
                                    <h5 class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($attr->name); ?></h5>
                                    <span class="text-[10px] text-slate-500 font-mono mt-0.5 block">Slug: pa_<?php echo htmlspecialchars($attr->slug); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if (!empty($attr->woo_attr_id)): ?>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold">Synced</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-slate-200 text-slate-600 rounded text-[9px]">Local</span>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center opacity-0 group-hover:opacity-100 attr-actions">
                                        <button onclick="event.stopPropagation(); openEditAttributeModal(<?php echo $attr->id; ?>, '<?php echo addslashes($attr->name); ?>', '<?php echo addslashes($attr->slug); ?>', <?php echo !empty($attr->woo_attr_id) ? 'true' : 'false'; ?>)" class="p-1 text-slate-400 hover:text-indigo-600"><i class="fa-solid fa-pen text-xs"></i></button>
                                        <a href="<?php echo APP_URL; ?>/variation/deleteAttribute/<?php echo $attr->id; ?>" onclick="event.stopPropagation(); return confirm('Delete attribute? Terms & WooCommerce taxonomy will be removed.');" class="p-1 text-slate-400 hover:text-rose-600"><i class="fa-solid fa-trash text-xs"></i></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Panel: Synced Terms List (2/3 Width) -->
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm min-h-[350px] flex flex-col justify-between">
                
                <?php foreach ($synced_attributes as $index => $attr): ?>
                    <div id="term-container-<?php echo $attr->id; ?>" class="term-group-view space-y-6 <?php echo $index === 0 ? '' : 'hidden'; ?>">
                        
                        <!-- Header with add buttons -->
                        <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900"><?php echo htmlspecialchars($attr->name); ?> terms</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Assigned terms for the 'pa_<?php echo htmlspecialchars($attr->slug); ?>' attribute taxonomy template.</p>
                            </div>
                            <button onclick="openAddTermModal(<?php echo $attr->id; ?>, '<?php echo addslashes($attr->name); ?>')" class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/30 flex items-center gap-1.5 transition-all cursor-pointer">
                                <i class="fa-solid fa-plus"></i> Add Term Option
                            </button>
                        </div>

                        <!-- Terms layout card list -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php if (empty($attr->terms)): ?>
                                <div class="col-span-3 py-10 text-center text-slate-400 italic">No term options defined for this attribute. Click "Add Term Option" to append.</div>
                            <?php else: ?>
                                <?php foreach ($attr->terms as $term): ?>
                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex justify-between items-center hover:bg-slate-100/50 hover:border-slate-300 transition-all group">
                                        <div>
                                            <span class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($term->name); ?></span>
                                            <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">Slug: <?php echo htmlspecialchars($term->slug); ?></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <?php if (!empty($term->woo_term_id)): ?>
                                                <span class="h-2 w-2 rounded-full bg-emerald-500" title="WooCommerce Synced"></span>
                                            <?php endif; ?>
                                            <div class="flex items-center opacity-0 group-hover:opacity-100 transition-all">
                                                <button onclick="openEditTermModal(<?php echo $term->id; ?>, '<?php echo addslashes($term->name); ?>', '<?php echo addslashes($term->slug); ?>', <?php echo !empty($term->woo_term_id) ? 'true' : 'false'; ?>)" class="p-1.5 text-slate-400 hover:text-indigo-600 cursor-pointer"><i class="fa-solid fa-pen text-xs"></i></button>
                                                <a href="<?php echo APP_URL; ?>/variation/deleteTerm/<?php echo $term->id; ?>" onclick="return confirm('Delete attribute term? WooCommerce mapping will also be un-synced.');" class="p-1.5 text-slate-400 hover:text-rose-600"><i class="fa-solid fa-trash text-xs"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- Live Synchronization Terminal Logging Console -->
        <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-xl" id="sync-console-container">
            <div class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-sm tracking-wide text-slate-200 uppercase flex items-center gap-2">
                        <i class="fa-solid fa-terminal text-primary-500"></i> WooCommerce Sync Operations Console
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Real-time terminal diagnostic logging for global taxonomies and variable attributes</p>
                </div>
                <button onclick="clearTerminalLogs()" class="text-[10px] text-slate-400 hover:text-rose-400 font-semibold px-3 py-1.5 bg-slate-800 hover:bg-rose-950/20 border border-slate-700 rounded-md transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-[9px]"></i> Clear Console Logs
                </button>
            </div>
            <div class="p-6 font-mono text-[11px] space-y-2 max-h-60 overflow-y-auto bg-slate-950 text-slate-400 custom-scrollbar" id="terminalConsole">
                <div class="text-slate-500">[System] Attributes Hub active. Awaiting synchronization requests...</div>
            </div>
        </div>

    </div>

    <!-- Modals Configuration -->
    <!-- 1. Add Attribute Modal -->
    <div id="addAttributeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="bg-slate-50 border-b border-slate-100 p-6 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-900">Add New Attribute</h3>
                <button onclick="closeAddAttributeModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="<?php echo APP_URL; ?>/variation/addAttribute" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Attribute Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Ruling Size" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slug (Unique identifier; pa_{slug})</label>
                    <input type="text" name="slug" placeholder="e.g. ruling-size" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono">
                </div>
                <div class="bg-purple-50/50 border border-purple-100 p-4 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-purple-950 font-bold text-xs block">WooCommerce Active Sync</span>
                        <span class="text-purple-700/80 text-[10px] block mt-0.5">Sync Attribute taxonomy to WooCommerce</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sync_woo" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 relative"></div>
                    </label>
                </div>
                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddAttributeModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-500/30 transition cursor-pointer">Save Attribute</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Attribute Modal -->
    <div id="editAttributeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="bg-slate-50 border-b border-slate-100 p-6 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-900">Edit Attribute</h3>
                <button onclick="closeEditAttributeModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form id="editAttributeForm" action="" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Attribute Name *</label>
                    <input type="text" name="name" id="editAttrName" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slug</label>
                    <input type="text" name="slug" id="editAttrSlug" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono">
                </div>
                <div class="bg-purple-50/50 border border-purple-100 p-4 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-purple-950 font-bold text-xs block">WooCommerce Active Sync</span>
                        <span class="text-purple-700/80 text-[10px] block mt-0.5">Sync changes dynamically with WooCommerce</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sync_woo" id="editAttrSyncWoo" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 relative"></div>
                    </label>
                </div>
                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditAttributeModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-500/30 transition cursor-pointer">Update Attribute</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Add Term Modal -->
    <div id="addTermModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="bg-slate-50 border-b border-slate-100 p-6 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-900">Add Term to <span id="addTermParentName" class="text-indigo-600">Attribute</span></h3>
                <button onclick="closeAddTermModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form action="<?php echo APP_URL; ?>/variation/addTerm" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="attribute_id" id="addTermParentId" value="">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Term Option Value *</label>
                    <input type="text" name="name" required placeholder="e.g. Large, 200 Pages" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-semibold font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slug</label>
                    <input type="text" name="slug" placeholder="e.g. large" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono">
                </div>
                <div class="bg-purple-50/50 border border-purple-100 p-4 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-purple-950 font-bold text-xs block">WooCommerce Active Sync</span>
                        <span class="text-purple-700/80 text-[10px] block mt-0.5">Sync Term directly to WooCommerce Attribute</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sync_woo" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 relative"></div>
                    </label>
                </div>
                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddTermModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-500/30 transition cursor-pointer">Save Term Value</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. Edit Term Modal -->
    <div id="editTermModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="bg-slate-50 border-b border-slate-100 p-6 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-900">Edit Term Value</h3>
                <button onclick="closeEditTermModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form id="editTermForm" action="" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Term Name *</label>
                    <input type="text" name="name" id="editTermName" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-semibold font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slug</label>
                    <input type="text" name="slug" id="editTermSlug" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono">
                </div>
                <div class="bg-purple-50/50 border border-purple-100 p-4 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-purple-950 font-bold text-xs block">WooCommerce Active Sync</span>
                        <span class="text-purple-700/80 text-[10px] block mt-0.5">Sync changes dynamically with WooCommerce</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sync_woo" id="editTermSyncWoo" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 relative"></div>
                    </label>
                </div>
                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditTermModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-500/30 transition cursor-pointer">Update Term</button>
                </div>
            </form>
        </div>
    </div>

    <!-- AJAX & UI Client Operations -->
    <script>
        // Tab Switchers
        function switchTab(tabId) {
            const contents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < contents.length; i++) {
                contents[i].classList.add('hidden');
            }
            document.getElementById(tabId).classList.remove('hidden');

            const tabs = ['variationsTab', 'attributesTab'];
            tabs.forEach(t => {
                const btn = document.getElementById(`btn-${t}`);
                if (t === tabId) {
                    btn.className = "px-6 py-4 border-b-2 border-primary-600 text-primary-600 font-bold text-sm flex items-center gap-2 focus:outline-none transition-all duration-150 cursor-pointer";
                } else {
                    btn.className = "px-6 py-4 border-b-2 border-transparent text-slate-500 hover:text-slate-800 font-semibold text-sm flex items-center gap-2 focus:outline-none transition-all duration-150 cursor-pointer";
                }
            });
        }

        /**
         * Interactively updates selected attribute panel view
         */
        function selectAttribute(id) {
            // Remove active classes from all attribute selectors
            const cards = document.getElementsByClassName('attr-selection-card');
            for(let i=0; i<cards.length; i++) {
                cards[i].classList.remove('bg-indigo-50/70', 'border-indigo-200', 'ring-2', 'ring-indigo-500/10');
            }

            // Assign active state classes to selected target
            const activeCard = document.getElementById(`attr-card-${id}`);
            if (activeCard) {
                activeCard.classList.add('bg-indigo-50/70', 'border-indigo-200', 'ring-2', 'ring-indigo-500/10');
            }

            // Toggle corresponding terms view list
            const termContainers = document.getElementsByClassName('term-group-view');
            for(let i=0; i<termContainers.length; i++) {
                termContainers[i].classList.add('hidden');
            }
            const activeContainer = document.getElementById(`term-container-${id}`);
            if (activeContainer) {
                activeContainer.classList.remove('hidden');
            }
        }

        // Add Attribute Modals
        function openAddAttributeModal() {
            document.getElementById('addAttributeModal').classList.remove('hidden');
        }
        function closeAddAttributeModal() {
            document.getElementById('addAttributeModal').classList.add('hidden');
        }

        // Edit Attribute Modals
        function openEditAttributeModal(id, name, slug, isSynced) {
            document.getElementById('editAttributeForm').action = `<?php echo APP_URL; ?>/variation/editAttribute/${id}`;
            document.getElementById('editAttrName').value = name;
            document.getElementById('editAttrSlug').value = slug;
            document.getElementById('editAttrSyncWoo').checked = isSynced;
            document.getElementById('editAttributeModal').classList.remove('hidden');
        }
        function closeEditAttributeModal() {
            document.getElementById('editAttributeModal').classList.add('hidden');
        }

        // Add Term Modals
        function openAddTermModal(attrId, attrName) {
            document.getElementById('addTermParentId').value = attrId;
            document.getElementById('addTermParentName').innerText = attrName;
            document.getElementById('addTermModal').classList.remove('hidden');
        }
        function closeAddTermModal() {
            document.getElementById('addTermModal').classList.add('hidden');
        }

        // Edit Term Modals
        function openEditTermModal(id, name, slug, isSynced) {
            document.getElementById('editTermForm').action = `<?php echo APP_URL; ?>/variation/editTerm/${id}`;
            document.getElementById('editTermName').value = name;
            document.getElementById('editTermSlug').value = slug;
            document.getElementById('editTermSyncWoo').checked = isSynced;
            document.getElementById('editTermModal').classList.remove('hidden');
        }
        function closeEditTermModal() {
            document.getElementById('editTermModal').classList.add('hidden');
        }

        /**
         * Pull global attributes and their term definitions dynamically from WooCommerce
         */
        function syncAllAttributes() {
            const syncIcon = document.getElementById('global-sync-icon');
            syncIcon.classList.add('animate-spin');
            appendLog("[Attributes Sync] Initiating global WooCommerce attributes and terms list import...", 'text-purple-400 font-bold');

            fetch('<?php echo APP_URL; ?>/variation/ajaxSyncAttributes')
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP Error Status: ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        appendLog(`[Attributes Sync] Success! Retrieved & synced ${data.imported_attributes} attributes and ${data.imported_terms} unique terms into ERP tables.`, 'text-emerald-400 font-bold');
                        if (data.logs && Array.isArray(data.logs)) {
                            data.logs.forEach(log => appendLog(log, 'text-slate-300'));
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 2200);
                    } else {
                        appendLog(`[Error] Attribute pull failed: ${data.message}`, 'text-rose-400 font-bold');
                    }
                })
                .catch(err => {
                    appendLog(`[Fatal Error] Attributes connection breakdown: ${err.message}`, 'text-rose-400 font-bold');
                })
                .finally(() => {
                    syncIcon.classList.remove('animate-spin');
                });
        }

        function appendLog(text, colorClass = 'text-slate-300') {
            const logger = document.getElementById('terminalConsole');
            const div = document.createElement('div');
            div.className = `${colorClass} font-mono text-[10px] leading-relaxed`;
            
            const timestamp = new Date().toTimeString().split(' ')[0];
            div.innerHTML = `<span class="text-slate-600">[${timestamp}]</span> ${text}`;
            
            logger.appendChild(div);
            logger.scrollTop = logger.scrollHeight; // Auto Scroll
        }

        function clearTerminalLogs() {
            document.getElementById('terminalConsole').innerHTML = `<div class="text-slate-600 italic">[Console Cleared] Awaiting sync triggers...</div>`;
        }
    </script>

</body>
</html>