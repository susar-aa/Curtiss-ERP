<?php
// Capture flash/session messages safely
$successMsg = $data['success'] ?? '';
$errorMsg = $_SESSION['discount_error'] ?? $data['error'] ?? '';
if (isset($_SESSION['discount_error'])) {
    unset($_SESSION['discount_error']);
}

$rules = $data['rules'] ?? [];
$items = $data['items'] ?? [];
$categories = $data['categories'] ?? [];
$filters = $data['filters'] ?? [];
$metrics = $data['metrics'] ?? [
    'total' => 0, 'active' => 0, 'item_wise' => 0, 'bill_wise' => 0, 'category_wise' => 0, 'expired' => 0
];
?>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .gradient-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    }
    .badge-pulse {
        position: relative;
    }
    .badge-pulse::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        top: -1px;
        right: -1px;
        background-color: #10b981;
        border-radius: 50%;
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>

<!-- Alert Container -->
<div id="alert-container" class="mb-6">
    <?php if ($successMsg): ?>
        <div id="success-alert" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-4 shadow-sm animate-fade-in">
            <div class="bg-emerald-100 text-emerald-600 p-2 rounded-full shrink-0">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-emerald-900 font-bold text-sm">Action Successful</h4>
                <p class="text-emerald-700 text-xs mt-0.5"><?php echo htmlspecialchars($successMsg); ?></p>
            </div>
            <button onclick="document.getElementById('success-alert').remove()" class="text-emerald-400 hover:text-emerald-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div id="error-alert" class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-start gap-4 shadow-sm animate-fade-in">
            <div class="bg-rose-100 text-rose-600 p-2 rounded-full shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-rose-900 font-bold text-sm">Action Failed</h4>
                <p class="text-rose-700 text-xs mt-0.5"><?php echo htmlspecialchars($errorMsg); ?></p>
            </div>
            <button onclick="document.getElementById('error-alert').remove()" class="text-rose-400 hover:text-rose-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Header Banner -->
<div class="gradient-header rounded-2xl p-6 sm:p-8 text-white shadow-xl mb-8 relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 opacity-10 text-white pointer-events-none">
        <i class="fa-solid fa-tags text-[200px]"></i>
    </div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center gap-2 text-indigo-200 text-xs font-semibold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-bolt text-amber-400"></i> Promotional Rules Engine
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight">Customizable Discount Feed</h1>
            <p class="text-indigo-100 text-sm mt-1 max-w-xl">Configure automated item-wise free issue tiers, category discounts, and bill-wise percentage thresholds for real-time promotion triggers during sales & billing.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button onclick="openSimulatorModal()" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 backdrop-blur-md border border-white/20 shadow-sm cursor-pointer">
                <i class="fa-solid fa-vial text-amber-300"></i> Test Rule Engine
            </button>
            <button onclick="document.getElementById('ruleFormContainer').scrollIntoView({behavior: 'smooth'})" class="px-4 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-indigo-900/30 cursor-pointer">
                <i class="fa-solid fa-plus-circle"></i> Create Rule
            </button>
        </div>
    </div>
</div>

<!-- Key Performance Indicators (KPI Cards) -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="glass-card rounded-xl p-4 shadow-sm flex items-center gap-4">
        <div class="bg-indigo-50 text-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold">
            <i class="fa-solid fa-tags"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Rules</div>
            <div class="text-2xl font-black text-slate-800 mt-0.5"><?php echo $metrics['total']; ?></div>
        </div>
    </div>

    <div class="glass-card rounded-xl p-4 shadow-sm flex items-center gap-4">
        <div class="bg-emerald-50 text-emerald-600 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold badge-pulse">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Feeds</div>
            <div class="text-2xl font-black text-emerald-600 mt-0.5"><?php echo $metrics['active']; ?></div>
        </div>
    </div>

    <div class="glass-card rounded-xl p-4 shadow-sm flex items-center gap-4">
        <div class="bg-blue-50 text-blue-600 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold">
            <i class="fa-solid fa-gift"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Item Free Issues</div>
            <div class="text-2xl font-black text-blue-600 mt-0.5"><?php echo $metrics['item_wise']; ?></div>
        </div>
    </div>

    <div class="glass-card rounded-xl p-4 shadow-sm flex items-center gap-4">
        <div class="bg-purple-50 text-purple-600 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bill Discs</div>
            <div class="text-2xl font-black text-purple-600 mt-0.5"><?php echo $metrics['bill_wise']; ?></div>
        </div>
    </div>

    <div class="glass-card rounded-xl p-4 shadow-sm flex items-center gap-4 col-span-2 lg:col-span-1">
        <div class="bg-amber-50 text-amber-600 w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-xl font-bold">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cat. Rules</div>
            <div class="text-2xl font-black text-amber-600 mt-0.5"><?php echo $metrics['category_wise']; ?></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Rule Configuration Panel (Left - 4 Columns) -->
    <div id="ruleFormContainer" class="lg:col-span-4 glass-card rounded-2xl p-6 shadow-md border border-slate-200">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <h3 class="font-black text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-sliders text-indigo-600"></i> Configure Discount Rule
            </h3>
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full uppercase">Rule Builder</span>
        </div>

        <form action="<?php echo APP_URL; ?>/discount/add" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Rule Name / Campaign Title *</label>
                <input type="text" name="name" required placeholder="e.g., Buy 10 Get 2 Free on Filter Cartridges" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Rule Type *</label>
                    <select name="rule_type" id="ruleTypeSelect" onchange="toggleRuleFields()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                        <option value="item_wise">Item-Wise Promotion</option>
                        <option value="category_wise">Category-Wise Promotion</option>
                        <option value="bill_wise">Bill Total-Wise Discount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Reward Type *</label>
                    <select name="reward_type" id="rewardTypeSelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                        <option value="free_issue">Free Issue Quantity</option>
                        <option value="percentage">Percentage Off (%)</option>
                    </select>
                </div>
            </div>

            <!-- Target Product Selector -->
            <div id="targetProductWrapper">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Target Product SKU *</label>
                <select name="target_item_id" id="targetItemSelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                    <option value="">Select Target Product...</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item->id; ?>">
                            <?php echo htmlspecialchars($item->name . ' [' . ($item->item_code ?? 'N/A') . ']'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Target Category Selector -->
            <div id="targetCategoryWrapper" style="display: none;">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Target Item Category *</label>
                <select name="target_category_id" id="targetCategorySelect" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-all">
                    <option value="">Select Item Category...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>">
                            <?php echo htmlspecialchars($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Campaign Validity Dates -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="date" name="start_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="date" name="end_date" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Max Discount Cap (Rs.) (Optional)</label>
                <input type="number" step="0.01" min="0" name="discount_cap" placeholder="e.g. 5000 (Leave blank for no cap)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Tiers Builder -->
            <div class="border-t border-slate-100 pt-4">
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Discount Tiers / Thresholds</label>
                    <button type="button" onclick="addTierRow()" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-plus"></i> Add Tier
                    </button>
                </div>
                
                <div class="space-y-2.5" id="tiersContainer">
                    <!-- Dynamic tier rows loaded here -->
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Internal Remarks / Description</label>
                <textarea name="description" rows="2" placeholder="Campaign notes for rep guidance..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-indigo-600/20 transition cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Save & Publish Discount Rule
            </button>
        </form>
    </div>

    <!-- Active Rules Registry (Right - 8 Columns) -->
    <div class="lg:col-span-8 space-y-4">
        <!-- Search & Filter Bar -->
        <div class="glass-card rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" id="ruleSearchInput" onkeyup="filterRuleRows()" placeholder="Search rules, SKUs, categories..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto">
                <select id="ruleTypeFilter" onchange="filterRuleRows()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none">
                    <option value="">All Rule Types</option>
                    <option value="item_wise">Item-Wise</option>
                    <option value="category_wise">Category-Wise</option>
                    <option value="bill_wise">Bill-Wise</option>
                </select>

                <select id="statusFilter" onchange="filterRuleRows()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>
        </div>

        <!-- Rules Table -->
        <div class="glass-card rounded-2xl overflow-hidden shadow-md border border-slate-200">
            <div class="bg-slate-50/80 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-indigo-600"></i> Configured Rules Registry
                </h3>
                <span class="text-xs text-slate-500 font-semibold">Showing <span id="visibleRulesCount"><?php echo count($rules); ?></span> rules</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse" id="rulesTable">
                    <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 text-[11px] font-extrabold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-6 text-left">Rule & Campaign Info</th>
                            <th class="py-3 px-6 text-left">Type & Scope</th>
                            <th class="py-3 px-6 text-left">Configured Tiers</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($rules)): ?>
                            <tr>
                                <td colspan="5" class="py-16 text-center text-slate-400 italic">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 text-2xl">
                                            <i class="fa-solid fa-tags"></i>
                                        </div>
                                        <span class="font-semibold text-sm text-slate-500">No customizable discount rules found.</span>
                                        <p class="text-xs text-slate-400 max-w-sm">Use the Rule Builder on the left to configure your first promotional campaign.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rules as $r): ?>
                                <tr class="rule-row hover:bg-slate-50/80 transition-colors" 
                                    data-name="<?php echo strtolower(htmlspecialchars($r->name)); ?>"
                                    data-type="<?php echo $r->rule_type; ?>"
                                    data-status="<?php echo $r->is_expired ? 'Expired' : $r->status; ?>"
                                    data-sku="<?php echo strtolower(htmlspecialchars($r->item_sku ?? '')); ?>"
                                    data-category="<?php echo strtolower(htmlspecialchars($r->category_name ?? '')); ?>">
                                    
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                            <?php echo htmlspecialchars($r->name); ?>
                                        </div>
                                        <?php if (!empty($r->description)): ?>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1"><?php echo htmlspecialchars($r->description); ?></p>
                                        <?php endif; ?>
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5 text-[10px]">
                                            <?php if ($r->start_date || $r->end_date): ?>
                                                <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">
                                                    <i class="fa-regular fa-calendar"></i>
                                                    <?php echo $r->start_date ?: 'Start'; ?> &rarr; <?php echo $r->end_date ?: 'Ongoing'; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($r->discount_cap): ?>
                                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-bold">
                                                    Cap: Rs. <?php echo number_format($r->discount_cap); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        <?php if ($r->rule_type === 'item_wise'): ?>
                                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg font-bold text-[10px]">
                                                <i class="fa-solid fa-gift"></i> Item-Wise
                                            </span>
                                            <?php if (!empty($r->item_name)): ?>
                                                <div class="text-[10px] text-slate-600 mt-1 font-semibold truncate max-w-[150px]">
                                                    <?php echo htmlspecialchars($r->item_name); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($r->rule_type === 'category_wise'): ?>
                                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 px-2.5 py-1 rounded-lg font-bold text-[10px]">
                                                <i class="fa-solid fa-layer-group"></i> Category-Wise
                                            </span>
                                            <?php if (!empty($r->category_name)): ?>
                                                <div class="text-[10px] text-slate-600 mt-1 font-semibold">
                                                    Cat: <?php echo htmlspecialchars($r->category_name); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 px-2.5 py-1 rounded-lg font-bold text-[10px]">
                                                <i class="fa-solid fa-receipt"></i> Bill-Wise
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-4 px-6">
                                        <ul class="space-y-1">
                                            <?php foreach ($r->tiers as $t): ?>
                                                <li class="flex items-center gap-1.5 text-[11px]">
                                                    <span class="font-semibold text-slate-700">
                                                        <?php if (in_array($r->rule_type, ['item_wise', 'category_wise'])): ?>
                                                            Qty &ge; <?php echo intval($t->min_threshold); ?>
                                                        <?php else: ?>
                                                            Rs <?php echo number_format($t->min_threshold); ?>
                                                            <?php echo $t->max_threshold ? ' - Rs ' . number_format($t->max_threshold) : '+'; ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="text-slate-400">&rarr;</span>
                                                    <span class="text-indigo-700 font-bold bg-indigo-50 px-1.5 py-0.5 rounded">
                                                        <?php if ($r->reward_type === 'free_issue' || $r->rule_type === 'item_wise'): ?>
                                                            <?php echo intval($t->reward_val); ?> Free Units
                                                        <?php else: ?>
                                                            <?php echo floatval($t->reward_val); ?>% Off
                                                        <?php endif; ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <?php if ($r->is_expired): ?>
                                            <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2.5 py-1 rounded-full">Expired</span>
                                        <?php elseif ($r->is_upcoming): ?>
                                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-full">Starts Soon</span>
                                        <?php else: ?>
                                            <a href="<?php echo APP_URL; ?>/discount/toggle/<?php echo $r->id; ?>" 
                                               class="px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer <?php echo $r->status === 'Active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                                                <?php echo $r->status; ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Edit Button -->
                                            <button onclick="openEditModal(<?php echo $r->id; ?>)" class="p-1.5 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition cursor-pointer" title="Edit Rule">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </button>

                                            <!-- Duplicate Button -->
                                            <a href="<?php echo APP_URL; ?>/discount/duplicate/<?php echo $r->id; ?>" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Duplicate Rule">
                                                <i class="fa-solid fa-copy text-xs"></i>
                                            </a>

                                            <!-- Delete Button -->
                                            <a href="<?php echo APP_URL; ?>/discount/delete/<?php echo $r->id; ?>" 
                                               onclick="return confirm('Are you sure you want to delete this promotional rule?');"
                                               class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Delete Rule">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- EDIT RULE MODAL -->
<div id="editRuleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-indigo-900 to-indigo-800 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i> Edit Promotional Discount Rule
            </h3>
            <button onclick="closeEditModal()" class="text-indigo-200 hover:text-white text-lg cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?php echo APP_URL; ?>/discount/update" method="POST" class="p-6 space-y-4" id="editRuleForm">
            <input type="hidden" name="rule_id" id="editRuleId">

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Rule Name *</label>
                <input type="text" name="name" id="editName" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Rule Type *</label>
                    <select name="rule_type" id="editRuleType" onchange="toggleEditRuleFields()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="item_wise">Item-Wise Promotion</option>
                        <option value="category_wise">Category-Wise Promotion</option>
                        <option value="bill_wise">Bill Total-Wise Discount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Reward Type *</label>
                    <select name="reward_type" id="editRewardType" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="free_issue">Free Issue Quantity</option>
                        <option value="percentage">Percentage Off (%)</option>
                    </select>
                </div>
            </div>

            <div id="editTargetProductWrapper">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Target Product SKU</label>
                <select name="target_item_id" id="editTargetItem" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    <option value="">Select Target Product...</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="editTargetCategoryWrapper" style="display: none;">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Target Item Category</label>
                <select name="target_category_id" id="editTargetCategory" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                    <option value="">Select Category...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="date" name="start_date" id="editStartDate" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="date" name="end_date" id="editEndDate" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Max Cap (Rs.)</label>
                    <input type="number" step="0.01" name="discount_cap" id="editDiscountCap" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" id="editStatus" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Tiers Container in Modal -->
            <div class="border-t border-slate-100 pt-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Discount Tiers</label>
                    <button type="button" onclick="addEditTierRow()" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-[11px] font-bold">
                        <i class="fa-solid fa-plus"></i> Add Tier
                    </button>
                </div>
                <div class="space-y-2" id="editTiersContainer"></div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- RULE SIMULATOR MODAL -->
<div id="simulatorModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
        <div class="bg-slate-900 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fa-solid fa-vial text-amber-400"></i> Discount Engine Simulation Tester
            </h3>
            <button onclick="closeSimulatorModal()" class="text-slate-400 hover:text-white text-lg cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <p class="text-xs text-slate-500">Test how active rules will trigger for different order amounts and item quantities before publishing to live billing.</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Bill Subtotal (Rs.)</label>
                    <input type="number" id="testBillSubtotal" placeholder="e.g. 75000" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Item Quantity</label>
                    <input type="number" id="testItemQty" placeholder="e.g. 12" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Target Item SKU</label>
                <select id="testItemId" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold">
                    <option value="">Select Item to Test...</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo $item->id; ?>"><?php echo htmlspecialchars($item->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" onclick="runRuleSimulation()" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow cursor-pointer">
                Run Simulation Test
            </button>

            <!-- Test Results Box -->
            <div id="simResultsBox" class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl hidden">
                <h4 class="font-bold text-slate-800 text-xs mb-2">Simulation Test Results:</h4>
                <div id="simResultsContent" class="space-y-2 text-xs"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRuleFields() {
        const type = document.getElementById('ruleTypeSelect').value;
        const productWrapper = document.getElementById('targetProductWrapper');
        const categoryWrapper = document.getElementById('targetCategoryWrapper');
        const container = document.getElementById('tiersContainer');
        
        if (type === 'item_wise') {
            productWrapper.style.display = 'block';
            categoryWrapper.style.display = 'none';
        } else if (type === 'category_wise') {
            productWrapper.style.display = 'none';
            categoryWrapper.style.display = 'block';
        } else {
            productWrapper.style.display = 'none';
            categoryWrapper.style.display = 'none';
        }

        container.innerHTML = '';
        addTierRow();
    }

    function addTierRow(minVal = '', maxVal = '', rewardVal = '') {
        const type = document.getElementById('ruleTypeSelect').value;
        const container = document.getElementById('tiersContainer');
        const rowDiv = document.createElement('div');
        rowDiv.className = 'flex items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200 animate-fade-in';
        
        if (type === 'item_wise' || type === 'category_wise') {
            rowDiv.innerHTML = `
                <div class="flex-1">
                    <input type="number" min="1" step="1" name="min_threshold[]" value="${minVal}" required placeholder="Min Qty (e.g. 10)" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none">
                </div>
                <div class="flex-1">
                    <input type="number" min="1" step="0.01" name="reward_val[]" value="${rewardVal}" required placeholder="Reward (Qty or %)" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none">
                </div>
                <button type="button" onclick="this.closest('div').remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
        } else {
            rowDiv.innerHTML = `
                <div style="flex: 2;">
                    <input type="number" min="0.01" step="0.01" name="min_threshold[]" value="${minVal}" required placeholder="Min Amt (e.g. 50000)" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none">
                </div>
                <div style="flex: 2;">
                    <input type="number" min="0.01" step="0.01" name="max_threshold[]" value="${maxVal}" placeholder="Max Amt (Optional)" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none">
                </div>
                <div style="flex: 1.5;">
                    <input type="number" min="0.01" step="0.01" name="reward_val[]" value="${rewardVal}" required placeholder="% Off" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:outline-none">
                </div>
                <button type="button" onclick="this.closest('div').remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
        }
        
        container.appendChild(rowDiv);
    }

    function filterRuleRows() {
        const query = document.getElementById('ruleSearchInput').value.toLowerCase().trim();
        const typeFilter = document.getElementById('ruleTypeFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        const rows = document.querySelectorAll('#rulesTable tbody tr.rule-row');
        let visible = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const type = row.getAttribute('data-type') || '';
            const status = row.getAttribute('data-status') || '';
            const sku = row.getAttribute('data-sku') || '';
            const cat = row.getAttribute('data-category') || '';

            const matchesQuery = !query || name.includes(query) || sku.includes(query) || cat.includes(query);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesStatus = !statusFilter || status === statusFilter;

            if (matchesQuery && matchesType && matchesStatus) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('visibleRulesCount').innerText = visible;
    }

    // Modal Edit functions
    function openEditModal(ruleId) {
        fetch('<?php echo APP_URL; ?>/discount/api_get_rule/' + ruleId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const rule = res.data;
                    document.getElementById('editRuleId').value = rule.id;
                    document.getElementById('editName').value = rule.name;
                    document.getElementById('editRuleType').value = rule.rule_type;
                    document.getElementById('editRewardType').value = rule.reward_type || 'free_issue';
                    document.getElementById('editTargetItem').value = rule.target_item_id || '';
                    document.getElementById('editTargetCategory').value = rule.target_category_id || '';
                    document.getElementById('editStartDate').value = rule.start_date || '';
                    document.getElementById('editEndDate').value = rule.end_date || '';
                    document.getElementById('editDiscountCap').value = rule.discount_cap || '';
                    document.getElementById('editStatus').value = rule.status || 'Active';

                    toggleEditRuleFields();

                    const container = document.getElementById('editTiersContainer');
                    container.innerHTML = '';
                    if (rule.tiers && rule.tiers.length > 0) {
                        rule.tiers.forEach(t => {
                            addEditTierRow(t.min_threshold, t.max_threshold || '', t.reward_val);
                        });
                    } else {
                        addEditTierRow();
                    }

                    document.getElementById('editRuleModal').classList.remove('hidden');
                } else {
                    alert('Error loading rule details.');
                }
            })
            .catch(err => {
                alert('Failed to connect to server.');
                console.error(err);
            });
    }

    function closeEditModal() {
        document.getElementById('editRuleModal').classList.add('hidden');
    }

    function toggleEditRuleFields() {
        const type = document.getElementById('editRuleType').value;
        document.getElementById('editTargetProductWrapper').style.display = (type === 'item_wise') ? 'block' : 'none';
        document.getElementById('editTargetCategoryWrapper').style.display = (type === 'category_wise') ? 'block' : 'none';
    }

    function addEditTierRow(minVal = '', maxVal = '', rewardVal = '') {
        const type = document.getElementById('editRuleType').value;
        const container = document.getElementById('editTiersContainer');
        const rowDiv = document.createElement('div');
        rowDiv.className = 'flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200';

        if (type === 'item_wise' || type === 'category_wise') {
            rowDiv.innerHTML = `
                <div class="flex-1">
                    <input type="number" min="1" step="1" name="min_threshold[]" value="${minVal}" required placeholder="Min Qty" class="w-full px-2 py-1 bg-white border rounded text-xs font-semibold">
                </div>
                <div class="flex-1">
                    <input type="number" min="1" step="0.01" name="reward_val[]" value="${rewardVal}" required placeholder="Reward" class="w-full px-2 py-1 bg-white border rounded text-xs font-semibold">
                </div>
                <button type="button" onclick="this.closest('div').remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
        } else {
            rowDiv.innerHTML = `
                <div style="flex:2;">
                    <input type="number" min="0.01" step="0.01" name="min_threshold[]" value="${minVal}" required placeholder="Min Amt" class="w-full px-2 py-1 bg-white border rounded text-xs font-semibold">
                </div>
                <div style="flex:2;">
                    <input type="number" min="0.01" step="0.01" name="max_threshold[]" value="${maxVal}" placeholder="Max Amt" class="w-full px-2 py-1 bg-white border rounded text-xs font-semibold">
                </div>
                <div style="flex:1.5;">
                    <input type="number" min="0.01" step="0.01" name="reward_val[]" value="${rewardVal}" required placeholder="% Off" class="w-full px-2 py-1 bg-white border rounded text-xs font-semibold">
                </div>
                <button type="button" onclick="this.closest('div').remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
        }

        container.appendChild(rowDiv);
    }

    // Simulator Functions
    function openSimulatorModal() {
        document.getElementById('simulatorModal').classList.remove('hidden');
    }
    function closeSimulatorModal() {
        document.getElementById('simulatorModal').classList.add('hidden');
    }

    function runRuleSimulation() {
        const subtotal = document.getElementById('testBillSubtotal').value;
        const qty = document.getElementById('testItemQty').value;
        const itemId = document.getElementById('testItemId').value;

        fetch('<?php echo APP_URL; ?>/discount/api_test_rule', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                bill_subtotal: subtotal,
                item_qty: qty,
                item_id: itemId
            })
        })
        .then(res => res.json())
        .then(data => {
            const box = document.getElementById('simResultsBox');
            const content = document.getElementById('simResultsContent');
            box.classList.remove('hidden');

            if (data.status === 'success' && data.matched_count > 0) {
                let html = '';
                data.matched_rules.forEach(r => {
                    html += `
                        <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900 flex justify-between items-center">
                            <div>
                                <span class="font-bold">${r.rule_name}</span>
                                <span class="text-[10px] text-emerald-700 block">${r.matched_tier}</span>
                            </div>
                            <span class="bg-emerald-600 text-white font-extrabold px-2 py-0.5 rounded text-[10px]">${r.reward}</span>
                        </div>
                    `;
                });
                content.innerHTML = html;
            } else {
                content.innerHTML = `<div class="p-3 bg-slate-100 text-slate-600 rounded-lg italic">No rules matched the test criteria.</div>`;
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleRuleFields();
    });
</script>
