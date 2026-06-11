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

        /* ---- Command Bar (Dynamic Island style) ---- */
        .cmd-bar {
            position: fixed;
            bottom: 28px; left: 50%;
            transform: translateX(-50%);
            background: rgba(28, 28, 30, 0.92);
            backdrop-filter: saturate(180%) blur(28px);
            -webkit-backdrop-filter: saturate(180%) blur(28px);
            border: 0.5px solid rgba(255,255,255,0.12);
            border-radius: 999px;
            padding: 7px 10px;
            display: flex; align-items: center; gap: 4px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.14), 0 4px 12px rgba(0,0,0,0.06), 0 0 0 0.5px rgba(0,0,0,0.3);
            z-index: 100;
        }
        .cmd-search {
            display: flex; align-items: center; gap: 9px;
            background: rgba(255,255,255,0.1);
            border-radius: 999px;
            padding: 8px 14px;
            width: 196px;
            transition: width 0.42s cubic-bezier(0.25, 0.1, 0.25, 1),
                        background 0.28s;
        }
        .cmd-search:focus-within {
            width: 300px;
            background: rgba(255,255,255,0.18);
        }
        .cmd-search i { color: rgba(255,255,255,0.55); font-size: 14px; flex-shrink: 0; }
        .cmd-search input {
            background: transparent; border: none; outline: none;
            color: #fff; font-size: 14px; font-weight: 500;
            font-family: inherit; width: 100%;
        }
        .cmd-search input::placeholder { color: rgba(255,255,255,0.45); }
        .cmd-divider { width: 0.5px; height: 22px; background: rgba(255,255,255,0.15); margin: 0 3px; }
        .cmd-cta {
            display: flex; align-items: center; gap: 7px;
            background: #fff; color: #1c1c1e;
            border: none; border-radius: 999px;
            padding: 0 18px; height: 38px;
            font-size: 14px; font-weight: 700;
            font-family: inherit;
            cursor: pointer; text-decoration: none;
            transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1),
                        background 0.18s;
            margin-left: 2px;
        }
        .cmd-cta:hover { background: #e5e5ea; transform: scale(0.97); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen pb-32">

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
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Category Management</h1>
                <p class="text-sm text-slate-500 mt-1">Organize products and manage catalog classifications locally.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openAddModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Add New Category
                </button>
            </div>
        </div>

        <!-- Cards Layout Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="categoriesGrid">
            <?php if (empty($categories)): ?>
                <div class="col-span-full py-16 text-center text-slate-400 italic bg-white border border-slate-200 rounded-2xl">
                    <div class="flex flex-col items-center gap-2">
                        <i class="fa-solid fa-folder-open text-3xl text-slate-300"></i>
                        <span class="text-sm font-medium text-slate-500">No categories found in ERP. Click "Add New Category" above to register one.</span>
                    </div>
                </div>
            <?php else: ?>
                <?php 
                $gradients = [
                    'from-blue-500 to-cyan-500 shadow-blue-100',
                    'from-emerald-500 to-teal-500 shadow-emerald-100',
                    'from-indigo-500 to-purple-500 shadow-indigo-100',
                    'from-pink-500 to-rose-500 shadow-pink-100',
                    'from-orange-500 to-amber-500 shadow-orange-100',
                    'from-violet-500 to-fuchsia-500 shadow-violet-100'
                ];
                ?>
                <?php foreach ($categories as $cat): ?>
                    <?php 
                    $wooId = $cat->woo_category_id ?? null;
                    $has_synced = !empty($wooId);
                    $prodCount = intval($cat->product_count ?? 0);
                    $grad = $gradients[$cat->id % count($gradients)];
                    ?>
                    <div class="category-card bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between" 
                         data-name="<?php echo strtolower(htmlspecialchars($cat->name)); ?>" 
                         data-desc="<?php echo strtolower(htmlspecialchars($cat->description ?? '')); ?>">
                        <div>
                            <!-- Top header info / Gradient badge -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr <?php echo $grad; ?> flex items-center justify-center text-white text-lg font-bold shadow-md">
                                    <i class="fa-solid fa-folder"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-400 font-mono">#<?php echo $cat->id; ?></span>
                            </div>
                            <!-- Name -->
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors duration-200 mb-1"><?php echo htmlspecialchars($cat->name); ?></h3>
                            <!-- Description -->
                            <p class="text-xs text-slate-500 line-clamp-2 min-h-[2.5rem] leading-relaxed mb-4"><?php echo !empty($cat->description) ? htmlspecialchars($cat->description) : '<span class="italic text-slate-300">No description provided</span>'; ?></p>
                        </div>
                        <!-- Footer/Counter and Actions -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-box-open text-slate-400 text-xs"></i>
                                <span class="text-xs font-bold text-slate-600"><?php echo $prodCount; ?> <?php echo $prodCount == 1 ? 'Product' : 'Products'; ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="openEditModal(<?php echo $cat->id; ?>, '<?php echo htmlspecialchars(addslashes($cat->name)); ?>', '<?php echo htmlspecialchars(addslashes($cat->description ?? '')); ?>')" 
                                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit Category">
                                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                                </button>
                                <?php if ($prodCount > 0): ?>
                                    <button type="button" onclick="alert('This category cannot be deleted because it is linked to <?php echo $prodCount; ?> products. Please reassign the products first.');"
                                            class="p-2 text-slate-300 cursor-not-allowed" title="Cannot delete: Linked to products">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo APP_URL; ?>/category/delete/<?php echo $cat->id; ?>" 
                                       onclick="return confirm('Are you sure you want to delete the category \'<?php echo htmlspecialchars(addslashes($cat->name)); ?>\'?');"
                                       class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete Category">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- No categories matching search -->
            <div id="noCategoriesFound" class="hidden col-span-full py-16 text-center text-slate-400 italic">
                <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300"></i>
                    <span class="text-sm font-medium text-slate-500">No categories match your search criteria.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Command Bar (Dynamic Island style) -->
    <div class="cmd-bar">
        <div class="cmd-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="categorySearchInput"
                   oninput="filterCategories()"
                   placeholder="Search categories…">
        </div>
        <div class="cmd-divider"></div>
        <button type="button" onclick="openAddModal()" class="cmd-cta"><i class="fa-solid fa-plus" style="font-size:13px;"></i> New</button>
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

        function openEditModal(id, name, description) {
            document.getElementById('editForm').action = `<?php echo APP_URL; ?>/category/edit/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function filterCategories() {
            const query = document.getElementById('categorySearchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.category-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const desc = card.getAttribute('data-desc') || '';
                if (name.includes(query) || desc.includes(query)) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const emptyState = document.getElementById('noCategoriesFound');
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }
    </script>

</body>
</html>