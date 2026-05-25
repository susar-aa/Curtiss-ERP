<?php
// ==========================================
// FORM SAFETY & FALLBACK ENGINE
// ==========================================
$db = new Database();

// Safely solve potential undefined variable warnings
$title = $data['title'] ?? 'Product Catalog Form';
$item = $data['item'] ?? null;

// Populate current variables (support edit mapping / create defaults)
$item_id = $item ? ($item->id ?? '') : '';
$item_code = $item ? ($item->item_code ?? '') : '';
$name = $item ? ($item->name ?? '') : '';
$selling_price = $item ? ($item->selling_price ?? '') : '';
$description = $item ? ($item->description ?? '') : '';

// Retrieve potential fallback features to prevent omissions
$barcode = $item ? ($item->barcode ?? '') : '';
$cost_price = $item ? ($item->cost_price ?? '') : '';
$wholesale_price = $item ? ($item->wholesale_price ?? '') : ''; // WholesaleX B2B Price field
$alert_qty = $item ? ($item->alert_qty ?? '5') : '5';
$category_id = $item ? ($item->category_id ?? '') : '';
$brand = $item ? ($item->brand ?? '') : '';
$unit = $item ? ($item->unit ?? 'pcs') : 'pcs';
$status = $item ? ($item->status ?? 'active') : 'active';
$sync_woo = $item ? ($item->sync_woo ?? '1') : '1';
$image_path = $item ? ($item->image_path ?? '') : '';
$weight = $item ? ($item->weight ?? '') : '';

// Retrieve Relational Warehouse and Vendor variables
$warehouse_id = $item ? ($item->warehouse_id ?? '') : '';
$vendor_id = $item ? ($item->vendor_id ?? '') : '';
$retail_margin = $item ? ($item->retail_margin ?? '') : '';
$wholesale_margin = $item ? ($item->wholesale_margin ?? '') : '';

$is_edit = !empty($item_id);
$form_action = $is_edit ? APP_URL . '/inventory/edit/' . $item_id : APP_URL . '/inventory/add';

// Dynamically use WooCommerce categories loaded from controller with fallback
$categories = $data['categories'] ?? [];
$vendors = $data['vendors'] ?? [];
$warehouses = $data['warehouses'] ?? [];

// Dynamic self-healing fetch of synced WooCommerce attributes and terms
$synced_attributes = [];
try {
    $db->query("SELECT * FROM product_attributes ORDER BY name ASC");
    $synced_attributes = $db->resultSet() ?: [];
    foreach ($synced_attributes as $attr) {
        $db->query("SELECT * FROM product_attribute_terms WHERE attribute_id = :id ORDER BY name ASC");
        $db->bind(':id', $attr->id);
        $attr->terms = $db->resultSet() ?: [];
    }
} catch (Exception $e) {
    // Fail-safe empty array fallback
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
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen pb-12">

    <div class="max-w-5xl w-full mx-auto px-4 py-8">
        
        <!-- Navigation Breadcrumbs -->
        <a href="<?php echo APP_URL; ?>/inventory" class="inline-flex items-center gap-2 text-slate-500 hover:text-primary-600 font-semibold text-sm transition-colors mb-6 group">
            <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Back to Product Catalog
        </a>

        <!-- Form Card Container -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
            
            <!-- Header section -->
            <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-slate-500 text-xs mt-1">Configure retail, cost, B2B wholesale prices, upload images, select suppliers, and map custom product attributes.</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center border border-primary-100">
                    <i class="fa-solid <?php echo $is_edit ? 'fa-pen-to-square' : 'fa-plus'; ?> text-xl"></i>
                </div>
            </div>

            <!-- Form Body Fields -->
            <form action="<?php echo $form_action; ?>" method="POST" id="productForm" class="p-6 md:p-8 space-y-8" enctype="multipart/form-data">
                
                <!-- Hidden inputs for variations and image compression base64 -->
                <input type="hidden" name="variations_json" id="variationsJson" value="[]">
                <input type="hidden" name="compressed_image_base64" id="compressedImageBase64" value="">

                <!-- Section 1: Product Media Dropzone with Compressor -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-image text-primary-500"></i> Product Image (Auto-Compressing Dropzone)
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <div class="md:col-span-2">
                            <!-- Drag and drop zone -->
                            <div id="dropzone" class="border-2 border-dashed border-slate-300 hover:border-primary-500 bg-slate-50/50 hover:bg-primary-50/10 rounded-2xl p-6 transition-all duration-200 cursor-pointer text-center relative">
                                <input type="file" id="imageFileInput" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                                <div class="space-y-2">
                                    <div class="h-10 w-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center mx-auto shadow-inner group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">Drag & drop product photo or <span class="text-primary-600 hover:underline">browse files</span></p>
                                    <p class="text-[10px] text-slate-400">Supports PNG, JPG, JPEG up to 10MB (automatically compressed to fast-loading size)</p>
                                </div>
                            </div>

                            <!-- Upload & Compression Progress Bar -->
                            <div id="progressWrapper" class="mt-4 bg-slate-100 rounded-full h-2.5 overflow-hidden hidden border border-slate-200">
                                <div id="progressBar" class="bg-gradient-to-r from-primary-500 to-purple-500 h-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p id="progressText" class="text-[11px] text-slate-500 font-semibold mt-1.5 hidden"></p>
                        </div>

                        <!-- Image Preview Slot -->
                        <div class="flex justify-center">
                            <div class="relative w-36 h-36 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shadow-inner">
                                <img id="previewImage" src="<?php echo !empty($image_path) ? (filter_var($image_path, FILTER_VALIDATE_URL) ? $image_path : APP_URL . '/' . $image_path) : 'https://placehold.co/300?text=No+Product+Image'; ?>" class="object-cover w-full h-full" onerror="this.src='https://placehold.co/300?text=No+Product+Image'">
                                <button type="button" id="removeImageBtn" class="absolute bottom-2 right-2 p-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl shadow-md transition-colors text-xs hidden">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Core Product Identification -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                        <i class="fa-solid fa-barcode mr-1 text-primary-500"></i> Core Product Identification
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- SKU Code -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Product SKU / Code *</label>
                            <div class="relative">
                                <i class="fa-solid fa-hashtag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="item_code" id="mainItemCode" value="<?php echo htmlspecialchars($item_code); ?>" placeholder="e.g. FCON-PEN-01" required
                                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono font-bold">
                            </div>
                        </div>

                        <!-- Barcode EAN/UPC -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Barcode UPC/EAN</label>
                            <div class="relative">
                                <i class="fa-solid fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="barcode" value="<?php echo htmlspecialchars($barcode); ?>" placeholder="e.g. 4791234567890" 
                                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono">
                            </div>
                        </div>

                        <!-- Product Status Option -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catalog Status</label>
                            <select name="status" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all cursor-pointer font-semibold">
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active / Published</option>
                                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft Listing</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Suspended / Inactive</option>
                            </select>
                        </div>

                        <!-- Item Name/Title -->
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Item Name / Product Title *</label>
                            <div class="relative">
                                <i class="fa-solid fa-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="name" id="mainProductName" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. Falcon Luxury Blue Ink Pen" required
                                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-semibold">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Catalog Categorization & Database Relations -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                        <i class="fa-solid fa-warehouse mr-1 text-primary-500"></i> Organization, Category & Database storage relations
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        
                        <!-- WooCommerce Category list -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category (WooCommerce Live)</label>
                            <select name="category_id" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all cursor-pointer font-semibold">
                                <option value="">General Stationery</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat->id; ?>" <?php echo $category_id == $cat->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Supplier Selection from Database Vendors data -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Supplier / Vendor (ERP Database)</label>
                            <select name="vendor_id" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all cursor-pointer font-semibold">
                                <option value="">Select Supplier</option>
                                <?php foreach ($vendors as $v): ?>
                                    <option value="<?php echo $v->id; ?>" <?php echo $vendor_id == $v->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($v->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Warehouse Selection from Database warehouses data -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Warehouse Storage Bin</label>
                            <select name="warehouse_id" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all cursor-pointer font-semibold">
                                <option value="">Select Warehouse</option>
                                <?php foreach ($warehouses as $wh): ?>
                                    <option value="<?php echo $wh->id; ?>" <?php echo $warehouse_id == $wh->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($wh->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Brand/Manufacturer -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Brand / Manufacturer</label>
                            <div class="relative">
                                <i class="fa-solid fa-building-flag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="brand" value="<?php echo htmlspecialchars($brand); ?>" placeholder="e.g. Falcon Stationery" 
                                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Dynamic Synced WooCommerce Variations Builder -->
                <div>
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-diagram-project text-primary-500 animate-pulse"></i> WooCommerce Synced Attributes & Variations
                        </h3>
                        <div class="text-xs text-slate-400 italic">No stock inputs required (Managed via GRN/PO transactions)</div>
                    </div>

                    <!-- Step-by-Step interactive builder panel linked with dynamic databases -->
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 mb-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <!-- 1. Database Synced Attribute Select Dropdown -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">1. Select WooCommerce Attribute</label>
                                <select id="attrGroupSelect" onchange="handleAttributeSelectionChange()" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none cursor-pointer font-semibold">
                                    <option value="">-- Choose Synced Attribute --</option>
                                    <?php foreach ($synced_attributes as $attr): ?>
                                        <option value="<?php echo $attr->id; ?>">
                                            <?php echo htmlspecialchars($attr->name); ?> (pa_<?php echo htmlspecialchars($attr->slug); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="Custom">Custom Attribute Group...</option>
                                </select>
                            </div>

                            <!-- Custom Group Text Box (Fallback option) -->
                            <div id="customGroupWrapper" class="hidden">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Custom Attribute Name</label>
                                <input type="text" id="customGroupName" placeholder="e.g. Style, Size" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-semibold">
                            </div>

                            <!-- Custom Values Entry Field -->
                            <div id="customValuesWrapper" class="md:col-span-2 hidden">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Type custom options (Press Enter to separate)</label>
                                <div class="relative">
                                    <i class="fa-solid fa-keyboard absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="customValuesInput" placeholder="Type value and hit Enter (e.g. Black, White)" class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none">
                                </div>
                            </div>

                            <!-- Informational Term Selection Field -->
                            <div id="syncedTermsWrapper" class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">2. Click a synced term to add as variation</label>
                                <div id="syncedTermsContainer" class="flex flex-wrap gap-2 bg-white/70 border border-slate-200 rounded-xl p-3 min-h-[42px] items-center text-slate-400 italic text-xs">
                                    Select a synced attribute on the left to show available terms list.
                                </div>
                            </div>
                        </div>

                        <!-- Rendered Interactive Custom Tags (Only shown if custom selected) -->
                        <div id="attributeTagsContainer" class="flex flex-wrap gap-2 pt-2 hidden"></div>
                    </div>

                    <!-- Variations table list (No stock column) -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 shadow-inner">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase font-mono tracking-wider">
                                    <th class="py-3 px-4 w-[20%]">Attribute option value</th>
                                    <th class="py-3 px-4 w-[15%]">Variation SKU *</th>
                                    <th class="py-3 px-4 w-[12%] text-right">Cost Price (LKR)</th>
                                    <th class="py-3 px-4 w-[12%] text-right">Retail Margin %</th>
                                    <th class="py-3 px-4 w-[12%] text-right">Retail Price (LKR)</th>
                                    <th class="py-3 px-4 w-[12%] text-right">Wholesale Margin %</th>
                                    <th class="py-3 px-4 w-[12%] text-right">B2B base Price (LKR)</th>
                                    <th class="py-3 px-4 w-[5%] text-right">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="variationsTableBody" class="divide-y divide-slate-200">
                                <!-- Dynamic variation rows populated via JS -->
                                <tr id="noVariationsRow">
                                    <td colspan="8" class="py-8 text-center text-slate-400 italic">No variations created. Product will be synced as a simple standard product.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 5: Pricing, Costing & WholesaleX B2B (Locked/Disabled if variations exist) -->
                <div class="relative transition-all duration-300" id="basePricingContainer">
                    
                    <!-- Pricing Lock Alert Overlay -->
                    <div id="basePricingAlert" class="bg-purple-50 border border-purple-200 text-purple-950 p-4 rounded-xl text-xs flex items-center gap-3.5 mb-6 hidden">
                        <i class="fa-solid fa-lock text-purple-600 text-base animate-bounce"></i>
                        <div>
                            <strong class="font-bold">Base Pricing Deactivated:</strong> Product pricing is currently managed at the **variation level** above. Remove variations to reactive base catalog prices.
                        </div>
                    </div>

                    <div id="basePricingSection" class="space-y-6">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                            <span><i class="fa-solid fa-wallet mr-1 text-primary-500"></i> Base Pricing, Costing & WholesaleX B2B Price</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                            
                            <!-- Cost Price -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cost Price (Purchase)</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="cost_price" id="costPriceInput" value="<?php echo htmlspecialchars($cost_price); ?>" oninput="calculateMarkupProfit()" placeholder="0.00" 
                                           class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono font-bold">
                                </div>
                            </div>

                            <!-- Retail Margin % -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Retail Margin %</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">%</span>
                                    <input type="number" step="0.1" name="retail_margin" id="retailMarginInput" value="<?php echo htmlspecialchars($retail_margin); ?>" oninput="calculatePriceFromMargin('retail')" placeholder="0.0" 
                                           class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono font-bold">
                                </div>
                            </div>

                            <!-- Selling Price (LKR Retail) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Retail Price (LKR) *</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="selling_price" id="sellingPriceInput" value="<?php echo htmlspecialchars($selling_price); ?>" oninput="calculateMarginFromPrice('retail')" placeholder="0.00" required
                                           class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono font-bold">
                                </div>
                            </div>

                            <!-- Wholesale Margin % -->
                            <div>
                                <label class="block text-xs font-bold text-purple-700 uppercase tracking-wider mb-2">Wholesale Margin %</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-400 text-xs font-bold">%</span>
                                    <input type="number" step="0.1" name="wholesale_margin" id="wholesaleMarginInput" value="<?php echo htmlspecialchars($wholesale_margin); ?>" oninput="calculatePriceFromMargin('wholesale')" placeholder="0.0" 
                                           class="w-full pl-9 pr-3 py-2.5 bg-purple-50/20 border border-purple-200 focus:border-purple-500 rounded-xl text-sm focus:ring-2 focus:ring-purple-500/20 focus:outline-none font-mono font-bold text-purple-850">
                                </div>
                            </div>

                            <!-- B2B Wholesale Price (WholesaleX) -->
                            <div>
                                <label class="block text-xs font-bold text-purple-700 uppercase tracking-wider mb-2">B2B User Base Price *</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-400 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="wholesale_price" id="wholesalePriceInput" value="<?php echo htmlspecialchars($wholesale_price); ?>" oninput="calculateMarginFromPrice('wholesale')" placeholder="0.00" 
                                           class="w-full pl-9 pr-3 py-2.5 bg-purple-50/20 border border-purple-200 focus:border-purple-500 rounded-xl text-sm focus:ring-2 focus:ring-purple-500/20 focus:outline-none font-mono font-bold text-purple-850">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Non-Stock Physical Properties -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                        <i class="fa-solid fa-scale-balanced mr-1 text-primary-500"></i> Physical Specifications & Reorder Alerts
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Minimum Reorder Alert Threshold -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alert Quantity Limit</label>
                            <div class="relative">
                                <i class="fa-solid fa-triangle-exclamation absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="number" name="alert_qty" value="<?php echo htmlspecialchars($alert_qty); ?>" placeholder="5" 
                                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none font-mono">
                            </div>
                        </div>

                        <!-- Unit of Measure -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Unit of Measure (UOM)</label>
                            <select name="unit" class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none transition-all cursor-pointer font-bold">
                                <option value="pcs" <?php echo $unit === 'pcs' ? 'selected' : ''; ?>>Pieces (pcs)</option>
                                <option value="pack" <?php echo $unit === 'pack' ? 'selected' : ''; ?>>Packs (pack)</option>
                                <option value="box" <?php echo $unit === 'box' ? 'selected' : ''; ?>>Boxes (box)</option>
                                <option value="kg" <?php echo $unit === 'kg' ? 'selected' : ''; ?>>Kilograms (kg)</option>
                            </select>
                        </div>

                        <!-- Physical Item Weight -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Weight (grams / kg)</label>
                            <div class="relative">
                                <i class="fa-solid fa-scale-balanced absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="weight" value="<?php echo htmlspecialchars($weight); ?>" placeholder="e.g. 150g" 
                                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 7: Integration Settings & Detailed Description Logs -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                        <i class="fa-brands fa-wordpress-simple mr-1 text-purple-600 animate-pulse"></i> WooCommerce Integration Master Toggle
                    </h3>
                    
                    <div class="bg-purple-50/50 border border-purple-100 p-5 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <span class="text-purple-950 font-bold text-sm block">WooCommerce Active Synchronization</span>
                            <span class="text-purple-700/80 text-xs block mt-0.5">When checked, any save action directly updates the product details and stock status on WooCommerce in real time.</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="sync_woo" value="1" <?php echo $sync_woo == '1' ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 relative"></div>
                        </label>
                    </div>

                    <!-- Description Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Item Specifications Description & Metadata</label>
                        <textarea name="description" rows="4" placeholder="Enter product characteristics, properties, notes, or compatibility features for WooCommerce..."
                                  class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:outline-none placeholder-slate-400 leading-relaxed"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                </div>

                <!-- Form Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="<?php echo APP_URL; ?>/inventory" class="px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 text-sm font-semibold rounded-xl transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-md shadow-primary-500/30 transition-all duration-200 transform hover:-translate-y-0.5 flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-save"></i> Save Product Entry
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Client side dynamic compressor, calculators, variations serializer scripts -->
    <script>
        // WooCommerce Synced Attributes & Terms data set
        const syncedAttributes = <?php echo json_encode($synced_attributes); ?>;

        // Variations Tracker State
        let variations = [];
        let activeCustomTags = [];

        function calculatePriceFromMargin(type) {
            const costVal = parseFloat(document.getElementById('costPriceInput').value) || 0;
            if (type === 'retail') {
                const marginVal = parseFloat(document.getElementById('retailMarginInput').value) || 0;
                if (costVal > 0) {
                    const price = costVal + (costVal * marginVal / 100);
                    document.getElementById('sellingPriceInput').value = price.toFixed(2);
                }
            } else if (type === 'wholesale') {
                const marginVal = parseFloat(document.getElementById('wholesaleMarginInput').value) || 0;
                if (costVal > 0) {
                    const price = costVal + (costVal * marginVal / 100);
                    document.getElementById('wholesalePriceInput').value = price.toFixed(2);
                }
            }
        }

        function calculateMarginFromPrice(type) {
            const costVal = parseFloat(document.getElementById('costPriceInput').value) || 0;
            if (type === 'retail') {
                const priceVal = parseFloat(document.getElementById('sellingPriceInput').value) || 0;
                if (costVal > 0) {
                    const margin = ((priceVal - costVal) / costVal) * 100;
                    document.getElementById('retailMarginInput').value = margin.toFixed(1);
                }
            } else if (type === 'wholesale') {
                const priceVal = parseFloat(document.getElementById('wholesalePriceInput').value) || 0;
                if (costVal > 0) {
                    const margin = ((priceVal - costVal) / costVal) * 100;
                    document.getElementById('wholesaleMarginInput').value = margin.toFixed(1);
                }
            }
        }

        function calculateMarkupProfit() {
            const costVal = parseFloat(document.getElementById('costPriceInput').value) || 0;
            const retailMarginVal = parseFloat(document.getElementById('retailMarginInput').value) || 0;
            const wholesaleMarginVal = parseFloat(document.getElementById('wholesaleMarginInput').value) || 0;

            if (costVal > 0) {
                if (retailMarginVal > 0) {
                    calculatePriceFromMargin('retail');
                } else {
                    calculateMarginFromPrice('retail');
                }

                if (wholesaleMarginVal > 0) {
                    calculatePriceFromMargin('wholesale');
                } else {
                    calculateMarginFromPrice('wholesale');
                }
            }
        }

        /**
         * Disable/Hide base prices when variations exist (Variations have different prices)
         */
        function toggleBasePricingSection() {
            const pricingSection = document.getElementById('basePricingSection');
            const pricingAlert = document.getElementById('basePricingAlert');
            const costInput = document.getElementById('costPriceInput');
            const sellInput = document.getElementById('sellingPriceInput');
            const wholesaleInput = document.getElementById('wholesalePriceInput');

            // Count valid variation rows
            const hasVariations = variations.filter(v => v !== null).length > 0;

            if (hasVariations) {
                pricingSection.classList.add('opacity-40', 'pointer-events-none');
                pricingAlert.classList.remove('hidden');
                
                // Remove HTML5 constraint required flags during active variation syncing
                costInput.removeAttribute('required');
                sellInput.removeAttribute('required');
                wholesaleInput.removeAttribute('required');
            } else {
                pricingSection.classList.remove('opacity-40', 'pointer-events-none');
                pricingAlert.classList.add('hidden');
                
                costInput.setAttribute('required', 'required');
                sellInput.setAttribute('required', 'required');
                wholesaleInput.setAttribute('required', 'required');
            }
        }

        // ==========================================
        // CLIENT-SIDE IMAGE COMPRESSOR & DROPZONE
        // ==========================================
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('imageFileInput');
        const previewImage = document.getElementById('previewImage');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const progressWrapper = document.getElementById('progressWrapper');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const base64Input = document.getElementById('compressedImageBase64');

        // Drag/Drop Events
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add('border-primary-500', 'bg-primary-50/10');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-primary-500', 'bg-primary-50/10');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length) handleImageUpload(files[0]);
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleImageUpload(e.target.files[0]);
        });

        function handleImageUpload(file) {
            if (!file.type.match('image.*')) {
                alert('Oops! Only image files (PNG, JPG, JPEG) are accepted.');
                return;
            }

            progressWrapper.classList.remove('hidden');
            progressText.classList.remove('hidden');
            progressBar.style.width = '20%';
            progressText.innerText = "Analyzing file parameters...";

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                progressBar.style.width = '50%';
                progressText.innerText = "Compressing size to fast-loading specifications...";

                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    const max_size = 1000;
                    if (width > height) {
                        if (width > max_size) {
                            height *= max_size / width;
                            width = max_size;
                        }
                    } else {
                        if (height > max_size) {
                            width *= max_size / height;
                            height = max_size;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.7);
                    
                    setTimeout(() => {
                        progressBar.style.width = '100%';
                        progressText.innerText = "Compression complete! Ready to save.";
                        
                        previewImage.src = compressedBase64;
                        base64Input.value = compressedBase64;
                        removeImageBtn.classList.remove('hidden');
                        
                        setTimeout(() => {
                            progressWrapper.classList.add('hidden');
                            progressText.classList.add('hidden');
                        }, 1200);
                    }, 600);
                };
            };
        }

        removeImageBtn.addEventListener('click', () => {
            previewImage.src = 'https://placehold.co/300?text=No+Product+Image';
            base64Input.value = '';
            fileInput.value = '';
            removeImageBtn.classList.add('hidden');
        });

        // ==========================================
        // DYNAMIC SYNCED ATTRIBUTES & VARIATIONS
        // ==========================================
        const selectGroup = document.getElementById('attrGroupSelect');
        const customGroupWrapper = document.getElementById('customGroupWrapper');
        const customValuesWrapper = document.getElementById('customValuesWrapper');
        const customValuesInput = document.getElementById('customValuesInput');
        const syncedTermsWrapper = document.getElementById('syncedTermsWrapper');
        const syncedTermsContainer = document.getElementById('syncedTermsContainer');
        const tagsContainer = document.getElementById('attributeTagsContainer');
        const tableBody = document.getElementById('variationsTableBody');
        const noVariationsRow = document.getElementById('noVariationsRow');

        /**
         * Handle Synced Attribute Selection Changes
         */
        function handleAttributeSelectionChange() {
            const val = selectGroup.value;

            // Reset layouts
            customGroupWrapper.classList.add('hidden');
            customValuesWrapper.classList.add('hidden');
            tagsContainer.classList.add('hidden');
            syncedTermsWrapper.classList.add('hidden');
            syncedTermsContainer.innerHTML = '';
            activeCustomTags = [];

            if (val === '') {
                syncedTermsWrapper.classList.remove('hidden');
                syncedTermsContainer.innerHTML = 'Select a synced attribute on the left to show available terms list.';
            } else if (val === 'Custom') {
                customGroupWrapper.classList.remove('hidden');
                customValuesWrapper.classList.remove('hidden');
                tagsContainer.classList.remove('hidden');
                renderCustomTags();
            } else {
                syncedTermsWrapper.classList.remove('hidden');
                
                // Fetch attribute terms from matched local synced array
                const attrObj = syncedAttributes.find(a => a.id == val);
                if (attrObj && attrObj.terms && attrObj.terms.length > 0) {
                    attrObj.terms.forEach(term => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = "px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 transition-all flex items-center gap-1.5 cursor-pointer";
                        btn.innerHTML = `<i class="fa-solid fa-plus-circle text-[10px]"></i> Add ${escapeHtml(term.name)}`;
                        btn.onclick = () => {
                            addVariationRow({ attribute: term.name });
                        };
                        syncedTermsContainer.appendChild(btn);
                    });
                } else {
                    syncedTermsContainer.innerHTML = '<span class="text-amber-600 font-semibold"><i class="fa-solid fa-circle-exclamation mr-1"></i> No terms exist for this attribute. Please sync attributes or add a Custom Attribute group.</span>';
                }
            }
        }

        // Listener for custom tags entry
        customValuesInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const rawVal = customValuesInput.value.replace(',', '').trim();
                if (rawVal !== '' && !activeCustomTags.includes(rawVal)) {
                    activeCustomTags.push(rawVal);
                    renderCustomTags();
                    customValuesInput.value = '';
                }
            }
        });

        /**
         * Renders custom clickable attributes values as tags
         */
        function renderCustomTags() {
            tagsContainer.innerHTML = '';
            activeCustomTags.forEach(tag => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = "px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 transition-all flex items-center gap-1.5 cursor-pointer";
                btn.innerHTML = `<i class="fa-solid fa-plus-circle text-[10px]"></i> Add <strong>${escapeHtml(tag)}</strong>`;
                btn.onclick = () => {
                    addVariationRow({ attribute: tag });
                };
                tagsContainer.appendChild(btn);
            });
        }

        function updateNoVariationsVisibility() {
            const activeCount = variations.filter(v => v !== null).length;
            if (activeCount === 0) {
                noVariationsRow.classList.remove('hidden');
            } else {
                noVariationsRow.classList.add('hidden');
            }
        }

        function calculateVarPriceFromMargin(index, type) {
            const costInput = document.getElementById(`var-cost-${index}`);
            const marginInput = document.getElementById(`var-${type}-margin-${index}`);
            const priceInput = document.getElementById(`var-${type}-price-${index}`);

            const costVal = parseFloat(costInput.value) || 0;
            const marginVal = parseFloat(marginInput.value) || 0;

            if (costVal > 0) {
                const price = costVal + (costVal * marginVal / 100);
                priceInput.value = price.toFixed(2);
                updateVariationValue(index, type === 'retail' ? 'price' : 'wholesale_price', priceInput.value);
            }
        }

        function calculateVarMarginFromPrice(index, type) {
            const costInput = document.getElementById(`var-cost-${index}`);
            const marginInput = document.getElementById(`var-${type}-margin-${index}`);
            const priceInput = document.getElementById(`var-${type}-price-${index}`);

            const costVal = parseFloat(costInput.value) || 0;
            const priceVal = parseFloat(priceInput.value) || 0;

            if (costVal > 0) {
                const margin = ((priceVal - costVal) / costVal) * 100;
                marginInput.value = margin.toFixed(1);
                updateVariationValue(index, type === 'retail' ? 'retail_margin' : 'wholesale_margin', marginInput.value);
            }
        }

        function calculateVarRowMaster(index) {
            const retailMarginInput = document.getElementById(`var-retail-margin-${index}`);
            const wholesaleMarginInput = document.getElementById(`var-wholesale-margin-${index}`);

            const retailMarginVal = parseFloat(retailMarginInput.value) || 0;
            const wholesaleMarginVal = parseFloat(wholesaleMarginInput.value) || 0;

            if (retailMarginVal > 0) {
                calculateVarPriceFromMargin(index, 'retail');
            } else {
                calculateVarMarginFromPrice(index, 'retail');
            }

            if (wholesaleMarginVal > 0) {
                calculateVarPriceFromMargin(index, 'wholesale');
            } else {
                calculateVarMarginFromPrice(index, 'wholesale');
            }
        }

        /**
         * Appends an interactive variation item row
         */
        function addVariationRow(existing = null) {
            const index = variations.length;
            const mainSku = document.getElementById('mainItemCode').value || 'SKU';
            const defaultCostPrice = document.getElementById('costPriceInput').value || '0.00';
            const defaultSellPrice = document.getElementById('sellingPriceInput').value || '0.00';
            const defaultWholePrice = document.getElementById('wholesalePriceInput').value || '0.00';

            const rowData = {
                id: existing ? (existing.id || '') : '',
                attribute: existing ? (existing.attribute || '') : '',
                sku: existing ? (existing.sku || `${mainSku}-${index + 1}`) : `${mainSku}-${index + 1}`,
                cost_price: existing ? (existing.cost_price || defaultCostPrice) : defaultCostPrice,
                price: existing ? (existing.price || defaultSellPrice) : defaultSellPrice,
                wholesale_price: existing ? (existing.wholesale_price || defaultWholePrice) : defaultWholePrice,
                retail_margin: existing ? (existing.retail_margin || '') : '',
                wholesale_margin: existing ? (existing.wholesale_margin || '') : ''
            };

            // Calculate default margins if empty
            const costVal = parseFloat(rowData.cost_price) || 0;
            if (costVal > 0) {
                if (!rowData.retail_margin && parseFloat(rowData.price) > 0) {
                    rowData.retail_margin = (((parseFloat(rowData.price) - costVal) / costVal) * 100).toFixed(1);
                }
                if (!rowData.wholesale_margin && parseFloat(rowData.wholesale_price) > 0) {
                    rowData.wholesale_margin = (((parseFloat(rowData.wholesale_price) - costVal) / costVal) * 100).toFixed(1);
                }
            }

            variations.push(rowData);

            const row = document.createElement('tr');
            row.id = `variation-row-${index}`;
            row.className = "hover:bg-slate-100/50 transition-colors";
            row.innerHTML = `
                <td class="py-3 px-4 font-bold text-slate-800">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-primary-50 text-primary-700 border border-primary-150">
                        ${escapeHtml(rowData.attribute)}
                    </span>
                    <input type="hidden" value="${escapeHtml(rowData.attribute)}" oninput="updateVariationValue(${index}, 'attribute', this.value)">
                </td>
                <td class="py-3 px-4">
                    <input type="text" value="${escapeHtml(rowData.sku)}" oninput="updateVariationValue(${index}, 'sku', this.value)" placeholder="e.g. SKU-RED" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </td>
                <td class="py-3 px-4 text-right">
                    <input type="number" step="0.01" id="var-cost-${index}" value="${rowData.cost_price}" oninput="updateVariationValue(${index}, 'cost_price', this.value); calculateVarRowMaster(${index});" placeholder="0.00" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono text-right focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </td>
                <td class="py-3 px-4 text-right">
                    <input type="number" step="0.1" id="var-retail-margin-${index}" value="${rowData.retail_margin}" oninput="updateVariationValue(${index}, 'retail_margin', this.value); calculateVarPriceFromMargin(${index}, 'retail');" placeholder="0.0" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono text-right focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </td>
                <td class="py-3 px-4 text-right">
                    <input type="number" step="0.01" id="var-retail-price-${index}" value="${rowData.price}" oninput="updateVariationValue(${index}, 'price', this.value); calculateVarMarginFromPrice(${index}, 'retail');" placeholder="0.00" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono text-right focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </td>
                <td class="py-3 px-4 text-right">
                    <input type="number" step="0.1" id="var-wholesale-margin-${index}" value="${rowData.wholesale_margin}" oninput="updateVariationValue(${index}, 'wholesale_margin', this.value); calculateVarPriceFromMargin(${index}, 'wholesale');" placeholder="0.0" class="w-full bg-purple-50/20 border border-purple-200 rounded-lg px-2.5 py-1.5 text-xs font-mono text-right text-purple-750 focus:ring-1 focus:ring-purple-500 focus:outline-none font-bold">
                </td>
                <td class="py-3 px-4 text-right">
                    <input type="number" step="0.01" id="var-wholesale-price-${index}" value="${rowData.wholesale_price}" oninput="updateVariationValue(${index}, 'wholesale_price', this.value); calculateVarMarginFromPrice(${index}, 'wholesale');" placeholder="0.00" class="w-full bg-purple-50/30 border border-purple-200 rounded-lg px-2.5 py-1.5 text-xs font-mono text-right text-purple-750 focus:ring-1 focus:ring-purple-500 focus:outline-none font-bold">
                </td>
                <td class="py-3 px-4 text-right">
                    <button type="button" onclick="removeVariationRow(${index})" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-trash-can"></i></button>
                </td>
            `;

            tableBody.appendChild(row);
            updateNoVariationsVisibility();
            toggleBasePricingSection();
            serializeVariations();
        }

        function updateVariationValue(index, field, value) {
            if (variations[index]) {
                variations[index][field] = value;
                serializeVariations();
            }
        }

        function removeVariationRow(index) {
            const row = document.getElementById(`variation-row-${index}`);
            if (row) row.remove();
            
            variations[index] = null; // Mark as deleted to keep indexing consistent
            serializeVariations();
            
            const activeVariations = variations.filter(v => v !== null);
            if (activeVariations.length === 0) {
                variations = [];
                updateNoVariationsVisibility();
            }
            toggleBasePricingSection();
        }

        function serializeVariations() {
            const filtered = variations.filter(v => v !== null);
            document.getElementById('variationsJson').value = JSON.stringify(filtered);
        }

        function escapeHtml(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // ==========================================
        // INITIALIZATIONS
        // ==========================================
        window.addEventListener('DOMContentLoaded', () => {
            calculateMarkupProfit();

            // Populate existing variations in Edit Mode
            <?php if ($is_edit && isset($item->variations_json) && !empty($item->variations_json)): ?>
                const savedVariations = <?php echo $item->variations_json; ?>;
                if (Array.isArray(savedVariations)) {
                    savedVariations.forEach(item => addVariationRow(item));
                }
            <?php endif; ?>
        });

        // Form submission parameters mapping
        document.getElementById('productForm').addEventListener('submit', (e) => {
            serializeVariations();
        });
    </script>

</body>
</html>