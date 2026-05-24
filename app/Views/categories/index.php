<?php
// ==========================================
// VARIABLE SAFETY & ROBUST FALLBACK ENGINE
// ==========================================
$categories = $data['categories'] ?? [];
$title = $data['title'] ?? 'Category Management';

// Capture flash messages safely
$flashSuccess = $_SESSION['flash_success'] ?? $_GET['flash_success'] ?? null;
if (isset($_SESSION['flash_success'])) {
    unset($_SESSION['flash_success']);
}
$flashError = $_SESSION['flash_error'] ?? $_GET['flash_error'] ?? null;
if (isset($_SESSION['flash_error'])) {
    unset($_SESSION['flash_error']);
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
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
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen">

    <!-- Included Unified System Top Menu Bar from Layouts Folder -->
    <?php include '../app/Views/layouts/main.php'; ?>

    <!-- Main Workspace Container -->
    <div class="p-8 max-w-[1400px] mx-auto space-y-6">

        <!-- Inline Status Alerts -->
        <div id="alert-container">
            <?php if ($flashSuccess): ?>
                <div id="success-alert" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-4 mb-4 shadow-sm animate-fade-in">
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-full mt-0.5 shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h4 class="text-emerald-800 font-semibold text-sm">Action Successful</h4>
                        <p class="text-emerald-600 text-xs mt-0.5"><?php echo htmlspecialchars($flashSuccess); ?></p>
                    </div>
                    <button onclick="document.getElementById('success-alert').style.display='none'" class="ml-auto text-emerald-400 hover:text-emerald-600 cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div id="error-alert" class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-4 mb-4 shadow-sm animate-fade-in">
                    <div class="bg-rose-100 text-rose-600 p-2 rounded-full mt-0.5 shrink-0">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="text-rose-800 font-semibold text-sm">Sync Notification</h4>
                        <p class="text-rose-600 text-xs mt-0.5"><?php echo htmlspecialchars($flashError); ?></p>
                    </div>
                    <button onclick="document.getElementById('error-alert').style.display='none'" class="ml-auto text-rose-400 hover:text-rose-600 cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section Title and Sync Actions Toolbar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">Category Management</h1>
                <p class="text-xs text-slate-500 mt-1">Organize products, manage catalog classifications, and sync active categories with WooCommerce.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Sync categories action trigger -->
                <button onclick="triggerCategoriesSync()" id="sync-btn" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-300 hover:border-slate-400 text-slate-700 rounded-lg text-xs font-semibold flex items-center gap-2 transition cursor-pointer">
                    <i id="sync-icon" class="fa-solid fa-rotate"></i>
                    <span>Sync with WooCommerce</span>
                </button>
                <button onclick="openAddModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Add New Category
                </button>
            </div>
        </div>

        <!-- Mapped Categories Table Registry -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6 text-left w-[12%]">Local ID</th>
                        <th class="py-3.5 px-6 text-left w-[30%]">Category Name</th>
                        <th class="py-3.5 px-6 text-left w-[38%]">Description</th>
                        <th class="py-3.5 px-6 text-center w-[12%]">WooCommerce ID</th>
                        <th class="py-3.5 px-6 text-right w-[8%]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 italic">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
                                    <span>No categories found in ERP. Click 'Add New Category' above to register one.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <?php 
                            $wooId = $cat->woo_category_id ?? null;
                            $has_synced = !empty($wooId);
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-3.5 px-6 font-mono font-bold text-slate-500">#<?php echo $cat->id; ?></td>
                                <td class="py-3.5 px-6 font-semibold text-slate-900"><?php echo htmlspecialchars($cat->name); ?></td>
                                <td class="py-3.5 px-6 text-slate-500"><?php echo htmlspecialchars($cat->description ?? '-'); ?></td>
                                <td class="py-3.5 px-6 text-center">
                                    <?php if ($has_synced): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-150">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Sync (#<?php echo $wooId; ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            <i class="fa-solid fa-circle-notch text-[9px]"></i> Local
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="openEditModal(<?php echo $cat->id; ?>, '<?php echo addslashes($cat->name); ?>', '<?php echo addslashes($cat->description ?? ''); ?>', <?php echo $has_synced ? 'true' : 'false'; ?>)" 
                                                class="text-indigo-600 hover:text-indigo-850 hover:underline font-semibold cursor-pointer">Edit</button>
                                        <span class="text-slate-300">|</span>
                                        <a href="<?php echo APP_URL; ?>/category/delete/<?php echo $cat->id; ?>" 
                                           onclick="return confirm('Delete category? WooCommerce mapping will also be un-synced.');"
                                           class="text-rose-600 hover:text-rose-800 hover:underline font-semibold">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals Configuration -->
    <!-- 1. Add Category Modal -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 animate-fade-in">
            <div class="bg-slate-50 border-b border-slate-150 p-5 flex justify-between items-center">
                <h3 class="font-bold text-slate-900 text-sm">Add New Category</h3>
                <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            <form action="<?php echo APP_URL; ?>/category/add" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Executive writing tools" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" rows="3" placeholder="Category details and specifications..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"></textarea>
                </div>
                <div class="bg-purple-50 border border-purple-100 p-3.5 rounded-lg flex items-center justify-between">
                    <div>
                        <span class="text-purple-950 font-bold text-xs block">WooCommerce Active Sync</span>
                        <span class="text-purple-700/80 text-[10px] block mt-0.5">Sync Category to WooCommerce store</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sync_woo" value="1" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600 relative"></div>
                    </label>
                </div>
                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeAddModal()" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow transition cursor-pointer">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Category Modal -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 animate-fade-in">
            <div class="bg-slate-50 border-b border-slate-150 p-5 flex justify-between items-center">
                <h3 class="font-bold text-slate-900 text-sm">Edit Category</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            <form id="editForm" action="" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category Name *</label>
                    <input type="text" name="name" id="editName" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" id="editDescription" rows="3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none"></textarea>
                </div>
                <div class="bg-purple-50 border border-purple-100 p-3.5 rounded-lg flex items-center justify-between">
                    <div>
                        <span class="text-purple-950 font-bold text-xs block">WooCommerce Active Sync</span>
                        <span class="text-purple-700/80 text-[10px] block mt-0.5">Sync changes dynamically with WooCommerce</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="sync_woo" id="editSyncWoo" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600 relative"></div>
                    </label>
                </div>
                <div class="flex gap-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow transition cursor-pointer">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- UI Core Logic -->
    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function openEditModal(id, name, description, isSynced) {
            document.getElementById('editForm').action = `<?php echo APP_URL; ?>/category/edit/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editSyncWoo').checked = isSynced;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        /**
         * Trigger Background Categories Sync & Display Status Messages
         */
        function triggerCategoriesSync() {
            const btn = document.getElementById('sync-btn');
            const icon = document.getElementById('sync-icon');
            btn.disabled = true;
            icon.classList.add('animate-spin');

            fetch('<?php echo APP_URL; ?>/category/ajaxSyncCategories')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Dynamic Redirect with Success Parameter to update local state
                        window.location.href = '<?php echo APP_URL; ?>/category?flash_success=' + encodeURIComponent(`WooCommerce categories synchronized successfully! Added: ${data.imported}, Updated: ${data.updated}.`);
                    } else {
                        showInlineError(data.message || 'Synchronization encountered an issue.');
                    }
                })
                .catch(err => {
                    showInlineError('Connection breakdown: ' + err.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    icon.classList.remove('animate-spin');
                });
        }

        function showInlineError(message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div id="error-alert" class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-4 mb-4 shadow-sm animate-fade-in">
                    <div class="bg-rose-100 text-rose-600 p-2 rounded-full mt-0.5 shrink-0">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="text-rose-800 font-semibold text-sm">Sync Notification</h4>
                        <p class="text-rose-600 text-xs mt-0.5">${message}</p>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="ml-auto text-rose-400 hover:text-rose-600 cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            `;
        }
    </script>

</body>
</html>