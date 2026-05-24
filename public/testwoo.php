<?php
session_start();
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../app/Services/WooCommerceService.php';

// Check if user is logged in as Admin/Staff (Optional safety layer)
$user_logged_in = isset($_SESSION['user_id']);

$woo = new WooCommerceService();
$logFile = dirname(__DIR__) . '/public/uploads/woocommerce_sync.log';

// Handle Custom Actions
$actionResult = null;
$actionError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'search_sku') {
        $searchSku = trim($_POST['search_sku_val'] ?? '');
        if (!empty($searchSku)) {
            $product = $woo->getProductBySku($searchSku);
            if ($product) {
                $actionResult = [
                    'type' => 'search',
                    'success' => true,
                    'message' => "Product found successfully on WooCommerce!",
                    'data' => [
                        'ID' => $product->id,
                        'Name' => $product->name,
                        'SKU' => $product->sku,
                        'Price' => $product->price . ' LKR',
                        'Stock Level' => $product->stock_quantity ?? 'Unmanaged/None',
                        'Status' => ucfirst($product->status),
                        'Permalink' => $product->permalink
                    ]
                ];
            } else {
                $actionError = "No matching WooCommerce product found with SKU: '{$searchSku}'";
            }
        } else {
            $actionError = "Please enter a valid SKU to search.";
        }
    }

    if ($action === 'custom_sync') {
        $syncSku = trim($_POST['sync_sku'] ?? '');
        $syncName = trim($_POST['sync_name'] ?? '');
        $syncPrice = floatval($_POST['sync_price'] ?? 0);
        $syncQty = intval($_POST['sync_qty'] ?? 0);
        $syncDesc = trim($_POST['sync_desc'] ?? '');

        if (!empty($syncSku) && !empty($syncName)) {
            $customItem = (object)[
                'item_code' => $syncSku,
                'name' => $syncName,
                'selling_price' => $syncPrice,
                'qty' => $syncQty,
                'description' => $syncDesc
            ];

            $syncId = $woo->syncItem($customItem);
            if ($syncId) {
                $actionResult = [
                    'type' => 'sync',
                    'success' => true,
                    'message' => "Successfully synchronized to WooCommerce! Product ID: #{$syncId}",
                    'data' => [
                        'WooCommerce ID' => $syncId,
                        'SKU' => $syncSku,
                        'Name' => $syncName,
                        'Synced Price' => $syncPrice . ' LKR',
                        'Synced Stock' => $syncQty,
                    ]
                ];
            } else {
                $actionError = "Synchronization failed. Please check the logs below for detailed connection errors.";
            }
        } else {
            $actionError = "SKU and Name are required fields for synchronizing products.";
        }
    }

    if ($action === 'clear_logs') {
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            $actionResult = [
                'type' => 'clear_logs',
                'success' => true,
                'message' => "Synchronization log file cleared successfully."
            ];
        }
    }
}

// Read log file tail
$logs = [];
if (file_exists($logFile)) {
    $logContent = file($logFile);
    if (is_array($logContent)) {
        $logs = array_slice($logContent, -15); // Get last 15 lines
        $logs = array_reverse($logs); // Newest first
    }
}

// Mask Sensitive Credentials
$maskedKey = substr(WC_CONSUMER_KEY, 0, 6) . '************************' . substr(WC_CONSUMER_KEY, -4);
$maskedSecret = substr(WC_CONSUMER_SECRET, 0, 6) . '************************' . substr(WC_CONSUMER_SECRET, -4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WooCommerce Sync Diagnostic Terminal</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans">

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-800 pb-6 mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <h1 class="text-2xl font-bold tracking-tight">WooCommerce Core Diagnostics</h1>
                </div>
                <p class="text-slate-400 text-sm mt-1">Curtiss ERP Live Connection Broker & System Status Hub</p>
            </div>
            <div class="flex gap-3">
                <a href="<?php echo APP_URL; ?>/dashboard" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg transition border border-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-gauge"></i> Back to ERP Dashboard
                </a>
                <a href="test_woo_sync.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg transition flex items-center gap-2 shadow-lg shadow-indigo-900/30">
                    <i class="fa-solid fa-rotate"></i> Refresh State
                </a>
            </div>
        </header>

        <!-- Credentials Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-slate-800/50 border border-slate-700/50 p-6 rounded-xl backdrop-blur">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-key text-indigo-400"></i> Active Store Configuration
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                    <div class="bg-slate-900 p-3 rounded-lg border border-slate-800">
                        <span class="text-slate-500 block mb-1">WooCommerce REST Target Endpoint:</span>
                        <span class="text-indigo-300 break-all select-all font-semibold"><?php echo htmlspecialchars(WC_STORE_URL); ?></span>
                    </div>
                    <div class="bg-slate-900 p-3 rounded-lg border border-slate-800">
                        <span class="text-slate-500 block mb-1">Consumer Key Identifier (CK):</span>
                        <span class="text-slate-300 break-all"><?php echo htmlspecialchars($maskedKey); ?></span>
                    </div>
                    <div class="bg-slate-900 p-3 rounded-lg border border-slate-800 md:col-span-2">
                        <span class="text-slate-500 block mb-1">Consumer Secret Token (CS):</span>
                        <span class="text-slate-300 break-all"><?php echo htmlspecialchars($maskedSecret); ?></span>
                    </div>
                </div>
            </div>

            <!-- Fast Metrics Card -->
            <div class="bg-slate-800/50 border border-slate-700/50 p-6 rounded-xl backdrop-blur flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-signal text-emerald-400"></i> Integration Check
                    </h2>
                    <ul class="text-xs space-y-2.5 mt-2">
                        <li class="flex justify-between items-center py-1.5 border-b border-slate-700/50">
                            <span class="text-slate-400">ERP-as-Primary Engine:</span>
                            <span class="bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full font-bold">ACTIVE</span>
                        </li>
                        <li class="flex justify-between items-center py-1.5 border-b border-slate-700/50">
                            <span class="text-slate-400">cURL Timeout Threshold:</span>
                            <span class="text-slate-300 font-semibold font-mono">6 Seconds</span>
                        </li>
                        <li class="flex justify-between items-center py-1.5">
                            <span class="text-slate-400">Log Permission Status:</span>
                            <span class="<?php echo is_writable(dirname($logFile)) ? 'text-emerald-400' : 'text-rose-400'; ?> font-mono font-bold">
                                <?php echo is_writable(dirname($logFile)) ? 'Writable' : 'Read-Only'; ?>
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between items-center">
                    <span class="text-xs text-slate-500">Live Server Status:</span>
                    <span class="text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-md font-semibold font-mono">Online</span>
                </div>
            </div>
        </div>

        <!-- Alert Notification Panel -->
        <?php if ($actionError): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 px-4 py-3 rounded-lg mb-8 text-sm flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 mt-0.5 text-base"></i>
                <div>
                    <strong class="font-bold">Execution Warning:</strong>
                    <p class="mt-0.5 text-rose-200"><?php echo htmlspecialchars($actionError); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($actionResult): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-4 rounded-lg mb-8 text-sm">
                <div class="flex items-center gap-2 text-emerald-400 font-bold mb-3">
                    <i class="fa-solid fa-circle-check text-base"></i>
                    <span><?php echo htmlspecialchars($actionResult['message']); ?></span>
                </div>
                <?php if (isset($actionResult['data'])): ?>
                    <div class="bg-slate-900/80 p-3 rounded-lg border border-slate-800 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs font-mono">
                        <?php foreach ($actionResult['data'] as $key => $value): ?>
                            <div>
                                <span class="text-slate-500"><?php echo $key; ?>:</span>
                                <?php if ($key === 'Permalink'): ?>
                                    <a href="<?php echo $value; ?>" target="_blank" class="text-indigo-400 hover:underline break-all block"><?php echo $value; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                <?php else: ?>
                                    <span class="text-slate-200 block font-semibold"><?php echo htmlspecialchars($value); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Main Utility Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Look up Product SKU -->
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="h-8 w-8 bg-indigo-500/10 rounded-lg flex items-center justify-center text-indigo-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <h3 class="text-lg font-semibold">SKU Real-time Query</h3>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Query your live WooCommerce inventory instantly without leaving the ERP interface. Great for verifying item status.</p>
                </div>
                
                <form action="test_woo_sync.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="search_sku">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 font-semibold">Enter Target Product SKU / Code</label>
                        <div class="relative">
                            <input type="text" name="search_sku_val" placeholder="e.g. TEST-SKU-001" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-slate-700 hover:bg-slate-600 text-slate-100 font-semibold py-2 rounded-lg text-xs transition duration-150 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Query WooCommerce Api
                    </button>
                </form>
            </div>

            <!-- Trigger Live Sync -->
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-8 w-8 bg-emerald-500/10 rounded-lg flex items-center justify-center text-emerald-400">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <h3 class="text-lg font-semibold">Trigger Custom Sync Payload</h3>
                </div>
                <p class="text-xs text-slate-400 mb-4">Run a manual test synchronization with WooCommerce. If the SKU exists, it updates the details. Otherwise, it creates a new product.</p>

                <form action="test_woo_sync.php" method="POST" class="space-y-3">
                    <input type="hidden" name="action" value="custom_sync">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1 font-semibold">Product SKU (Unique ID)</label>
                            <input type="text" name="sync_sku" value="TEST-SKU-001" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 font-mono focus:outline-none focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1 font-semibold">Product Name</label>
                            <input type="text" name="sync_name" value="Falcon Luxury Blue Ink Pen" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1 font-semibold">Selling Price (LKR)</label>
                            <input type="number" step="0.01" name="sync_price" value="250.00" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 font-mono focus:outline-none focus:border-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1 font-semibold">Available Stock Level (ERP)</label>
                            <input type="number" name="sync_qty" value="45" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 font-mono focus:outline-none focus:border-indigo-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1 font-semibold">Product Sync Description</label>
                        <textarea name="sync_desc" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">Fine line executive pen with smooth ink output. Managed by Curtiss ERP.</textarea>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 rounded-lg text-xs transition duration-150 flex items-center justify-center gap-2 shadow-lg shadow-indigo-950/40">
                        <i class="fa-solid fa-paper-plane"></i> Execute Synchronize Action
                    </button>
                </form>
            </div>
        </div>

        <!-- Connection Logs Console -->
        <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-slate-900/80 border-b border-slate-800 px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h3 class="font-bold text-sm tracking-wide text-slate-200 uppercase flex items-center gap-2">
                        <i class="fa-solid fa-terminal text-slate-400"></i> Active Synchronization Console
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Real-time status tracking output for WooCommerce pipeline actions</p>
                </div>
                <form action="test_woo_sync.php" method="POST" onsubmit="return confirm('Clear local diagnostic sync logs?');">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="text-[10px] text-slate-400 hover:text-rose-400 font-semibold px-3 py-1.5 bg-slate-800 hover:bg-rose-950/30 border border-slate-700/60 rounded-md transition duration-150 flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can text-[9px]"></i> Clear System Log File
                    </button>
                </form>
            </div>
            <div class="p-6 font-mono text-[11px] space-y-2 max-h-80 overflow-y-auto bg-slate-950 text-slate-400">
                <?php if (empty($logs)): ?>
                    <p class="text-slate-600 italic py-4 text-center">No connection logs available. Trigger an action above to populate diagnostics.</p>
                <?php else: ?>
                    <?php foreach ($logs as $line): ?>
                        <?php 
                        $cleanedLine = htmlspecialchars(trim($line));
                        $isSuccess = strpos($cleanedLine, 'SUCCESS') !== false || strpos($cleanedLine, '✔') !== false;
                        $isError = strpos($cleanedLine, 'Error') !== false || strpos($cleanedLine, 'FAILED') !== false || strpos($cleanedLine, '✘') !== false;
                        
                        $lineClass = 'text-slate-400';
                        if ($isSuccess) $lineClass = 'text-emerald-400 bg-emerald-950/20 px-2 py-0.5 rounded border border-emerald-950/50 block';
                        if ($isError) $lineClass = 'text-rose-400 bg-rose-950/20 px-2 py-0.5 rounded border border-rose-950/50 block font-semibold';
                        ?>
                        <div class="<?php echo $lineClass; ?>">
                            <?php echo $cleanedLine; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>