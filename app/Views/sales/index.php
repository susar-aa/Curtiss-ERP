<?php
// Smart Failsafe Data Fetcher
// Ensures the view never crashes even if the controller doesn't pass the exact data arrays.
$db = new Database();



$catalog_items = $data['catalog_items'] ?? $data['items'] ?? [];
if (empty($catalog_items)) {
    $db->query("SELECT * FROM items ORDER BY name ASC");
    $catalog_items = $db->resultSet();
}

// Enforce Wholesale B2B pricing preference and load variations from JSON if needed
foreach ($catalog_items as $key => $item) {
    $billingPrice = 0.00;
    if (is_object($item)) {
        if (isset($item->wholesale_price) && floatval($item->wholesale_price) > 0) {
            $billingPrice = floatval($item->wholesale_price);
        } elseif (isset($item->selling_price) && floatval($item->selling_price) > 0) {
            $billingPrice = floatval($item->selling_price);
        } elseif (isset($item->price) && floatval($item->price) > 0) {
            $billingPrice = floatval($item->price);
        } elseif (isset($item->regular_price) && floatval($item->regular_price) > 0) {
            $billingPrice = floatval($item->regular_price);
        }
        $item->price = $billingPrice;
        $item->selling_price = $billingPrice;
        $item->regular_price = $billingPrice;
        $item->wholesale_price = $billingPrice;

        if (!isset($item->variations) || empty($item->variations)) {
            $db->query("SELECT ivo.*, v.name as variation_name, vv.value_name 
                        FROM item_variation_options ivo
                        JOIN variations v ON ivo.variation_id = v.id
                        JOIN variation_values vv ON ivo.variation_value_id = vv.id
                        WHERE ivo.item_id = :id");
            $db->bind(':id', $item->id);
            $item->variations = $db->resultSet() ?: [];

            if (empty($item->variations) && !empty($item->variations_json)) {
                $decoded = json_decode($item->variations_json);
                if (is_array($decoded)) {
                    $item->variations = [];
                    foreach ($decoded as $v) {
                        $vObj = new stdClass();
                        $vObj->id = $v->id ?? 0;
                        $vObj->variation_name = 'Option';
                        $vObj->value_name = $v->attribute ?? '';
                        $vObj->sku = $v->sku ?? '';
                        $vObj->quantity_on_hand = $v->qty ?? $item->qty ?? $item->quantity_on_hand ?? 0;
                        $vObj->quantity_reserved = $v->quantity_reserved ?? 0;

                        $vPrice = 0.00;
                        if (isset($v->wholesale_price) && floatval($v->wholesale_price) > 0) {
                            $vPrice = floatval($v->wholesale_price);
                        } elseif (isset($v->price) && floatval($v->price) > 0) {
                            $vPrice = floatval($v->price);
                        } else {
                            $vPrice = $billingPrice;
                        }
                        $vObj->price = $vPrice;
                        $item->variations[] = $vObj;
                    }
                }
            } else if (!empty($item->variations)) {
                foreach ($item->variations as $var) {
                    $varPrice = 0.00;
                    if (isset($var->wholesale_price) && floatval($var->wholesale_price) > 0) {
                        $varPrice = floatval($var->wholesale_price);
                    } elseif (isset($var->price) && floatval($var->price) > 0) {
                        $varPrice = floatval($var->price);
                    } else {
                        $varPrice = $billingPrice;
                    }
                    $var->price = $varPrice;
                }
            }
        }
    } elseif (is_array($item)) {
        if (isset($item['wholesale_price']) && floatval($item['wholesale_price']) > 0) {
            $billingPrice = floatval($item['wholesale_price']);
        } elseif (isset($item['selling_price']) && floatval($item['selling_price']) > 0) {
            $billingPrice = floatval($item['selling_price']);
        } elseif (isset($item['price']) && floatval($item['price']) > 0) {
            $billingPrice = floatval($item['price']);
        } elseif (isset($item['regular_price']) && floatval($item['regular_price']) > 0) {
            $billingPrice = floatval($item['regular_price']);
        }
        $catalog_items[$key]['price'] = $billingPrice;
        $catalog_items[$key]['selling_price'] = $billingPrice;
        $catalog_items[$key]['regular_price'] = $billingPrice;
        $catalog_items[$key]['wholesale_price'] = $billingPrice;
    }
}

// Fetch Customers WITH Territory Name and Real-Time Outstanding Balance
$db->query("SELECT c.*, m.name as mca_name,
               (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) FROM invoices WHERE customer_id = c.id AND status != 'Voided') 
               - 
               (SELECT COALESCE(SUM(amount), 0) FROM customer_payments WHERE customer_id = c.id) 
               - 
               (SELECT COALESCE(SUM(total_amount), 0) FROM credit_notes WHERE customer_id = c.id) 
               AS outstanding_balance
            FROM customers c 
            LEFT JOIN mca_areas m ON c.mca_id = m.id 
            ORDER BY c.name ASC");
$customers = $db->resultSet();

$db->query("SELECT * FROM mca_areas ORDER BY name ASC");
$mcaAreas = $db->resultSet() ?: [];

// Fetch Employees specifically marked as Reps/Sales (or fallback to all active employees)
$db->query("SELECT * FROM employees WHERE status = 'Active' AND job_title = 'Rep' ORDER BY first_name ASC");
$reps = $db->resultSet();

// Automate Accounting Defaults
$assets = $data['assets'] ?? [];
if (empty($assets)) {
    $db->query("SELECT * FROM chart_of_accounts WHERE account_type = 'Asset' AND account_name LIKE '%Receivable%' LIMIT 1");
    $assets = $db->resultSet();
    if(empty($assets)) {
        $db->query("SELECT * FROM chart_of_accounts WHERE account_type = 'Asset' LIMIT 1");
        $assets = $db->resultSet();
    }
}

$revenues = $data['revenues'] ?? [];
if (empty($revenues)) {
    $db->query("SELECT * FROM chart_of_accounts WHERE account_type = 'Revenue' LIMIT 1");
    $revenues = $db->resultSet();
}

// Extract Invoice Parameters
$inv = $data['editing_invoice'] ?? null;
$editingItems = $data['editing_items'] ?? [];

// Determine back URL
$rep_route_id = $_GET['rep_route_id'] ?? $_GET['route_id'] ?? (isset($inv->rep_route_id) ? $inv->rep_route_id : '');
$backUrl = $_GET['back_url'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if (empty($backUrl) || strpos($backUrl, '/sales/create') !== false || strpos($backUrl, '/sales/store') !== false) {
    if (!empty($rep_route_id)) {
        $backUrl = APP_URL . '/RepTracking?route_id=' . $rep_route_id . '&filter=adjustments';
    } else {
        $backUrl = APP_URL . '/sales';
    }
}

// Previous & Next navigation IDs
$prevId = null;
$nextId = null;
$type = $data['type'] ?? 'invoice';
if ($inv && isset($inv->id)) {
    $currentId = intval($inv->id);
    if ($type === 'sales_order') {
        $db->query("SELECT id FROM sales_orders WHERE id < :id ORDER BY id DESC LIMIT 1");
        $db->bind(':id', $currentId);
        $row = $db->single();
        if ($row) $prevId = $row->id;

        $db->query("SELECT id FROM sales_orders WHERE id > :id ORDER BY id ASC LIMIT 1");
        $db->bind(':id', $currentId);
        $row = $db->single();
        if ($row) $nextId = $row->id;
    } else {
        $db->query("SELECT id FROM invoices WHERE id < :id AND status != 'Voided' ORDER BY id DESC LIMIT 1");
        $db->bind(':id', $currentId);
        $row = $db->single();
        if ($row) $prevId = $row->id;

        $db->query("SELECT id FROM invoices WHERE id > :id AND status != 'Voided' ORDER BY id ASC LIMIT 1");
        $db->bind(':id', $currentId);
        $row = $db->single();
        if ($row) $nextId = $row->id;
    }
} else {
    if ($type === 'sales_order') {
        $db->query("SELECT id FROM sales_orders ORDER BY id DESC LIMIT 1");
        $row = $db->single();
        if ($row) $prevId = $row->id;
    } else {
        $db->query("SELECT id FROM invoices WHERE status != 'Voided' ORDER BY id DESC LIMIT 1");
        $row = $db->single();
        if ($row) $prevId = $row->id;
    }
}
?>
<style>
    /* Full-Screen App Layout CSS */
    html, body { overflow: hidden; height: 100%; margin: 0; }
    
    :root {
        --bg-success: #e6f9ec;
        --text-success: #1e7e34;
        --text-danger: #d9534f;
        --border: #d1d1d6;
        --border-strong: #8e8e93;
        --surface-1: #f2f2f7;
        --surface-2: #ffffff;
        --text-primary: #1c1c1e;
        --text-secondary: #8e8e93;
        --text-accent: #007aff;
        --text-muted: #8e8e93;
    }
    
    .qb-wrapper {
        background-color: #f5f5f7;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 13px;
        color: var(--text-primary);
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 12px;
        box-sizing: border-box;
    }
    
    .qb-container {
        background: #fff;
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 0.5px solid var(--border);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        padding: 12px 16px;
        box-sizing: border-box;
        overflow: hidden;
    }

    #invoiceForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        gap: 10px;
    }

    .qb-title { font-size: 17px; color: var(--text-primary); font-weight: 600; margin: 0; }

    .qb-input-field {
        width: 100%;
        height: 26px;
        border: 0.5px solid var(--border);
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 12px;
        font-family: inherit;
        box-sizing: border-box;
        background: #fff;
        color: var(--text-primary);
        font-weight: 500;
        margin-top: 4px;
    }
    .qb-input-field:focus {
        border-color: var(--text-accent);
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.15);
    }
    .qb-input-field[readonly] {
        background: #f5f5f7;
        color: var(--text-secondary);
        cursor: not-allowed;
    }

    .qb-btn-header {
        height: 28px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 550;
        border: 0.5px solid var(--border);
        border-radius: 6px;
        background: #fff;
        color: var(--text-primary);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        transition: background 0.15s;
        box-sizing: border-box;
    }
    .qb-btn-header:hover:not(:disabled) {
        background: #f5f5f7;
    }
    .qb-btn-header:disabled {
        color: var(--text-muted);
        cursor: not-allowed;
        opacity: 0.6;
    }

    .qb-btn-header-success {
        height: 28px;
        padding: 0 14px;
        font-size: 12px;
        font-weight: 600;
        background: var(--bg-success);
        border: none;
        border-radius: 6px;
        color: var(--text-success);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: opacity 0.15s;
        box-sizing: border-box;
    }
    .qb-btn-header-success:hover {
        opacity: 0.95;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        border: 0.5px solid var(--border);
        border-radius: 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        padding: 0;
        margin: 4px 0 0 0;
        list-style: none;
    }
    .search-results li {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 0.5px solid var(--border);
        font-size: 12px;
    }
    .search-results li:last-child {
        border-bottom: none;
    }
    .search-results li:hover, .search-results li.active {
        background: #f2f2f7;
    }

    /* Scrollable Table Container */
    .table-scroll-container {
        flex: 1;
        overflow-y: auto;
        background: var(--surface-2);
        border: 0.5px solid var(--border);
        border-radius: 10px;
    }

    /* Fixed table-layout ensures strict obedience to percentage widths */
    .qb-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .qb-table thead th {
        position: sticky;
        top: 0;
        background: var(--surface-1);
        color: var(--text-secondary);
        padding: 8px 6px;
        text-align: left;
        font-size: 11px;
        font-weight: 550;
        border-bottom: 0.5px solid var(--border);
        z-index: 10;
    }
    .qb-table td {
        padding: 6px;
        border-bottom: 0.5px solid var(--border);
        vertical-align: middle;
        color: var(--text-primary);
        font-size: 12px;
    }
    .qb-table tr:hover {
        background-color: #fafafa;
    }
    .qb-table input {
        width: 100%;
        border: none;
        background: transparent;
        padding: 2px 4px;
        font-size: 12px;
        font-family: inherit;
        box-sizing: border-box;
    }
    .qb-table input:focus {
        background: #fff;
        outline: none;
        border-radius: 4px;
        box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.2);
    }
    .qb-table .num {
        text-align: right;
    }
    
    .discount-cell {
        display: flex;
        border: 0.5px solid var(--border);
        border-radius: 6px;
        background: #fff;
        overflow: hidden;
        height: 24px;
        align-items: center;
        box-sizing: border-box;
    }
    .discount-cell:focus-within {
        border-color: var(--text-accent);
        box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.15);
    }
    .discount-cell input {
        border: none;
        width: 60%;
        text-align: right;
        padding: 0 4px;
        height: 100%;
    }
    .discount-cell select {
        border: none;
        border-left: 0.5px solid var(--border);
        width: 40%;
        padding: 0;
        background: #f9f9f9;
        font-size: 11px;
        height: 100%;
        cursor: pointer;
    }
    .discount-cell select:focus {
        outline: none;
    }

    .acc-settings { display: none; } 

    /* Prevent number wheel spinners */
    .qb-wrapper input::-webkit-outer-spin-button,
    .qb-wrapper input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .qb-wrapper input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div class="qb-wrapper">
    <?php if(!empty($data['error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border:1px solid #ef9a9a; margin-bottom:5px; font-weight:bold; flex-shrink:0; border-radius: 6px;"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; margin-bottom:5px; font-weight:bold; flex-shrink:0; border-radius: 6px;">
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border:1px solid #ef9a9a; margin-bottom:5px; font-weight:bold; flex-shrink:0; border-radius: 6px;">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <?php
    $rep_route_id = $_GET['rep_route_id'] ?? $_GET['route_id'] ?? (isset($inv->rep_route_id) ? $inv->rep_route_id : '');
    $rep_route_name = '';
    if (!empty($rep_route_id)) {
        try {
            $db->query("SELECT r.route_name, e.first_name, e.last_name 
                        FROM rep_daily_routes r 
                        JOIN employees e ON r.user_id = e.user_id 
                        WHERE r.id = :id");
            $db->bind(':id', $rep_route_id);
            $routeInfo = $db->single();
            if ($routeInfo) {
                $rep_route_name = $routeInfo->route_name . ' (' . $routeInfo->first_name . ' ' . $routeInfo->last_name . ')';
            }
        } catch(Exception $e) {}
    }
    ?>

    <?php if (!empty($rep_route_name)): ?>
        <div style="padding: 8px 12px; background:#e0f7fa; color:#006064; border:1px solid #b2ebf2; margin-bottom:10px; font-weight:bold; border-radius: 6px; display: flex; align-items: center; gap: 8px; flex-shrink: 0; font-size: 12px;">
            <span><i class="ph ph-map-pin"></i> Adding Invoice to Rep Route: <strong><?= htmlspecialchars($rep_route_name) ?></strong></span>
        </div>
    <?php endif; ?>

    <div class="qb-container">
        <form action="<?= APP_URL ?>/sales/store" method="POST" id="invoiceForm">
            <input type="hidden" name="type" value="<?= htmlspecialchars((string)($data['type'] ?? 'invoice')) ?>">
            <input type="hidden" name="rep_route_id" value="<?= htmlspecialchars((string)$rep_route_id) ?>">
            <input type="hidden" name="from_sales_order_id" value="<?= htmlspecialchars((string)($data['from_sales_order_id'] ?? '0')) ?>">
            <input type="hidden" name="back_url" value="<?= htmlspecialchars($backUrl) ?>">
            <input type="hidden" name="mca" id="displayMca" value="<?= ($inv && isset($inv->mca)) ? htmlspecialchars((string)$inv->mca) : '' ?>">
            <?php if ($inv): ?>
                <input type="hidden" name="editing_invoice_id" value="<?= isset($inv->id) ? $inv->id : '' ?>">
            <?php endif; ?>
            
            <!-- macOS Style Navigation Header Bar -->
            <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:8px; border-bottom:0.5px solid var(--border); flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <a href="<?= htmlspecialchars($backUrl) ?>" style="height:28px; padding:0 10px; font-size:12px; border:0.5px solid var(--border); border-radius:6px; background:#fff; color:var(--text-primary); text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:555;"><i class="ph ph-arrow-left" style="font-size:13px;"></i> Back</a>
                    <h1 class="qb-title"><?= htmlspecialchars($data['title'] ?? ($inv ? 'Edit Invoice' : 'New Invoice')) ?></h1>
                </div>
                
                <div style="display:flex; gap:6px;">
                    <?php if ($prevId): ?>
                        <a href="<?= APP_URL ?>/sales/edit/<?= $prevId ?>?type=<?= $type ?><?= !empty($rep_route_id) ? '&route_id='.$rep_route_id : '' ?>&back_url=<?= urlencode($backUrl) ?>" class="qb-btn-header">Previous</a>
                    <?php else: ?>
                        <button type="button" class="qb-btn-header" disabled>Previous</button>
                    <?php endif; ?>

                    <?php if ($nextId): ?>
                        <a href="<?= APP_URL ?>/sales/edit/<?= $nextId ?>?type=<?= $type ?><?= !empty($rep_route_id) ? '&route_id='.$rep_route_id : '' ?>&back_url=<?= urlencode($backUrl) ?>" class="qb-btn-header">Next</a>
                    <?php else: ?>
                        <button type="button" class="qb-btn-header" disabled>Next</button>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>/sales/create?type=<?= $type ?><?= !empty($rep_route_id) ? '&route_id='.$rep_route_id : '' ?>&back_url=<?= urlencode($backUrl) ?>" class="qb-btn-header"><i class="ph ph-plus" style="font-size:12px;"></i> New</a>
                    
                    <button type="submit" name="save_action" value="close" class="qb-btn-header-success"><i class="ph ph-check" style="font-size:13px;"></i> Save and close</button>
                    
                    <?php if (!$inv): ?>
                        <button type="submit" name="save_action" value="new" class="qb-btn-header">Save & New</button>
                        <button type="submit" name="save_action" value="print" class="qb-btn-header">Save & Print</button>
                        <button type="submit" name="save_action" value="whatsapp" class="qb-btn-header" style="color:#25d366; border-color:#25d366;"><i class="ph ph-whatsapp-logo"></i> WhatsApp</button>
                    <?php endif; ?>
                    
                    <button type="button" class="qb-btn-header" onclick="window.location.href='<?= htmlspecialchars($backUrl) ?>'">Cancel</button>
                </div>
            </div>

            <!-- Metadata Cards Grid -->
            <div style="display:grid; grid-template-columns: 1.1fr 0.7fr 0.7fr 0.7fr 0.9fr; gap:10px; flex-shrink:0; margin-top: 5px;">

                <!-- Card 1: Bill To -->
                <div style="background:var(--surface-2); border:0.5px solid var(--border); border-radius:10px; padding:10px 12px; min-height: 120px; display: flex; flex-direction: column; justify-content: space-between; position: relative; box-sizing: border-box;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                        <span style="color:var(--text-secondary); font-size:11px; font-weight:550;">Bill to</span>
                        <button type="button" onclick="openNewCustomerModal()" style="height:20px; padding:0 8px; font-size:11px; background:var(--bg-success); border:none; color:var(--text-success); border-radius:4px; cursor: pointer; display: flex; align-items: center; gap: 2px; font-weight: 550;"><i class="ph ph-plus" style="font-size:11px;"></i> New</button>
                    </div>
                    
                    <input type="hidden" name="customer_id" id="customerIdInput" required>
                    
                    <!-- State A: Search (visible when no customer is selected) -->
                    <div id="customerSearchContainer" style="display: block; position: relative; width: 100%;">
                        <input type="text" id="customerSearch" class="qb-input-field" style="width: 100%; margin-top:0;" placeholder="Search Customer..." onkeyup="filterCustomerSearch(event)" autocomplete="off" required>
                        <ul id="customerSearchResults" class="search-results" style="width: 100%; top: 100%;"></ul>
                    </div>
                    
                    <!-- State B: Selected Customer Info (hidden by default) -->
                    <div id="customerDetailsContainer" style="display: none; flex: 1; flex-direction: column; justify-content: space-between; width: 100%;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                            <div style="overflow: hidden;">
                                <p id="selectedCustomerName" style="font-weight:600; font-size:13px; margin:0 0 2px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></p>
                                <p id="selectedCustomerAddress" style="color:var(--text-secondary); margin:0; font-size:11px; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"></p>
                            </div>
                            <button type="button" onclick="clearSelectedCustomer()" style="background: transparent; border: none; color: var(--text-secondary); cursor: pointer; font-size: 14px; padding: 2px; flex-shrink: 0;" title="Change Customer"><i class="ph ph-x-circle"></i></button>
                        </div>
                        
                        <!-- Customer Edit and Invoice History Buttons -->
                        <div id="customerOptionsContainer" style="display: none; gap: 6px; margin-top: auto; padding-top: 4px;">
                            <button type="button" onclick="openCustomerEdit()" class="qb-btn" style="flex:1; height:22px; font-size:11px; padding:0; display:inline-flex; align-items:center; justify-content:center; gap:4px; border-radius:6px; border: 0.5px solid var(--border); background: #fff; cursor: pointer; color: var(--text-primary); font-weight: 550;"><i class="ph ph-pencil-simple" style="font-size:12px;"></i> Edit</button>
                            <button type="button" onclick="openCustomerHistory()" class="qb-btn" style="flex:1; height:22px; font-size:11px; padding:0; display:inline-flex; align-items:center; justify-content:center; gap:4px; border-radius:6px; border: 0.5px solid var(--border); background: #fff; cursor: pointer; color: var(--text-primary); font-weight: 550;"><i class="ph ph-clock-counter-clockwise" style="font-size:12px;"></i> History</button>
                        </div>
                    </div>
                    
                    <textarea id="billToAddress" style="display:none;" readonly></textarea>
                </div>

                <!-- Card 2: Credit Limit & Outstanding -->
                <div style="background:var(--surface-2); border:0.5px solid var(--border); border-radius:10px; padding:10px 12px; display:flex; flex-direction:column; justify-content:center; min-height: 120px; box-sizing: border-box;">
                    <!-- Placeholder when no customer is selected -->
                    <div id="customerOutstandingPlaceholder" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; height: 100%; color: var(--text-secondary); gap: 4px;">
                        <i class="ph ph-user-focus" style="font-size: 20px;"></i>
                        <span style="font-size: 11px;">Select customer to view credit info</span>
                    </div>
                    
                    <!-- Active outstanding container -->
                    <div id="customerOutstanding" style="display: none; flex-direction: column; justify-content: center; gap: 4px; height: 100%;">
                        <!-- Populated via Javascript -->
                    </div>
                </div>

                <!-- Card 3: Date & Invoice # -->
                <div style="background:var(--surface-2); border:0.5px solid var(--border); border-radius:10px; padding:10px 12px; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; box-sizing: border-box;">
                    <div>
                        <span style="color:var(--text-secondary); font-size:11px; font-weight: 550;">Date</span>
                        <input type="date" name="invoice_date" class="qb-input-field" value="<?= ($inv && isset($inv->invoice_date)) ? $inv->invoice_date : date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <span style="color:var(--text-secondary); font-size:11px; font-weight: 550;">Invoice #</span>
                        <input type="text" name="invoice_number" class="qb-input-field" value="<?= htmlspecialchars((string)($data['invoice_number'] ?? '')) ?>" <?= $inv ? 'readonly' : '' ?> required>
                    </div>
                </div>

                <!-- Card 4: P.O. No & Rep TP # -->
                <div style="background:var(--surface-2); border:0.5px solid var(--border); border-radius:10px; padding:10px 12px; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; box-sizing: border-box;">
                    <div>
                        <span style="color:var(--text-secondary); font-size:11px; font-weight: 550;">P.O. no.</span>
                        <input type="text" name="po_number" class="qb-input-field" value="<?= ($inv && isset($inv->po_number)) ? htmlspecialchars((string)$inv->po_number) : '' ?>" placeholder="Optional">
                    </div>
                    <div>
                        <span style="color:var(--text-secondary); font-size:11px; font-weight: 550;">Rep TP #</span>
                        <input type="text" name="rep_tp" id="displayPhone" class="qb-input-field" value="<?= ($inv && isset($inv->rep_tp)) ? htmlspecialchars((string)$inv->rep_tp) : '' ?>" placeholder="Phone #">
                    </div>
                </div>

                <!-- Card 5: Terms & Due Date -->
                <div style="background:var(--surface-2); border:0.5px solid var(--border); border-radius:10px; padding:10px 12px; display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; box-sizing: border-box;">
                    <div>
                        <span style="color:var(--text-secondary); font-size:11px; font-weight: 550;">Terms</span>
                        <select name="payment_term_id" id="paymentTermSelect" onchange="calculateDueDateOffset()" class="qb-input-field">
                            <option value="">Select Term...</option>
                            <?php foreach($data['payment_terms'] as $term): ?>
                                <option value="<?= $term->id ?>" data-days="<?= $term->days_due ?>" <?= ($inv && isset($inv->payment_term_id) && $inv->payment_term_id == $term->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <span style="color:var(--text-secondary); font-size:11px; font-weight: 550;">Due date</span>
                        <input type="date" name="due_date" id="dueDate" class="qb-input-field" value="<?= ($inv && isset($inv->due_date)) ? $inv->due_date : date('Y-m-d') ?>" required>
                    </div>
                </div>

            </div>

            <!-- Search and Rep Selection Bar -->
            <div style="display:flex; gap:10px; align-items:center; flex-shrink:0; margin-top: 5px;">
                <div style="position:relative; flex:1;">
                    <i class="ph ph-magnifying-glass" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:14px; color:var(--text-muted);" aria-hidden="true"></i>
                    <input type="text" id="itemSearch" placeholder="Search catalog by item code or name, SKU, category, sample code..." style="width:100%; height:32px; padding-left:32px; font-size:12px; border:0.5px solid var(--border); border-radius:6px; box-sizing:border-box; font-weight: 550;" autocomplete="off" />
                    <ul id="searchResults" class="search-results" style="width: 100%; top: 100%;"></ul>
                </div>
                <div style="width:220px;">
                    <select name="rep_name" class="qb-input-field" style="height:32px; margin-top:0; font-weight: 550;">
                        <option value="">Select Rep...</option>
                        <?php foreach($reps as $rep): ?>
                            <option value="<?= htmlspecialchars((string)($rep->first_name . ' ' . $rep->last_name)) ?>" <?= ($inv && isset($inv->rep_name) && trim((string)$inv->rep_name) == trim((string)($rep->first_name . ' ' . $rep->last_name))) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($rep->first_name . ' ' . $rep->last_name)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Cart Table Area -->
            <div class="table-scroll-container">
                <table class="qb-table">
                    <colgroup>
                        <col style="width:35px">
                        <col style="width:12%">
                        <col style="width:8%">
                        <col>
                        <col style="width:12%">
                        <col style="width:14%">
                        <col style="width:14%">
                        <col style="width:60px">
                        <col style="width:35px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px 6px;">#</th>
                            <th style="text-align:left; padding:8px 6px;">Item code</th>
                            <th style="text-align:right; padding:8px 6px;">Qty</th>
                            <th style="text-align:left; padding:8px 6px;">Description</th>
                            <th style="text-align:right; padding:8px 6px;">Rate</th>
                            <th style="text-align:right; padding:8px 6px;">Discount</th>
                            <th style="text-align:right; padding:8px 6px;">Amount</th>
                            <th style="text-align:left; padding:8px 6px;">Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceBody">
                        <!-- Placeholder empty row initially shown -->
                        <tr id="emptyPlaceholderRow" style="border-top:0.5px solid var(--border);">
                            <td style="padding:8px 6px; color:var(--text-muted); text-align:left;">—</td>
                            <td style="padding:8px 6px; color:var(--text-muted);">—</td>
                            <td style="padding:8px 6px; text-align:right; color:var(--text-muted);">—</td>
                            <td style="padding:8px 6px; color:var(--text-muted);">Add item from search above</td>
                            <td style="padding:8px 6px; text-align:right; color:var(--text-muted);">—</td>
                            <td style="padding:8px 6px; text-align:right; color:var(--text-muted);">—</td>
                            <td style="padding:8px 6px; text-align:right; color:var(--text-muted);">—</td>
                            <td style="padding:8px 6px; color:var(--text-muted);">—</td>
                            <td style="padding:8px 6px;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Area: message, memo, and totals panel -->
            <div style="display:grid; grid-template-columns: 1fr 1fr 1.2fr; gap:12px; align-items:start; flex-shrink: 0;">
                <div>
                    <span style="color:var(--text-secondary); font-size:11px; font-weight: 550; display: block; margin-bottom: 4px;">Customer message</span>
                    <select name="customer_message" style="width:100%; height:28px; font-size:12px; border:0.5px solid var(--border); border-radius:6px; padding:0 8px; background:#fff; font-weight: 500;">
                        <option value="">Thank you for your business.</option>
                        <option value="">Please remit payment at your earliest convenience.</option>
                    </select>
                </div>
                <div>
                    <span style="color:var(--text-secondary); font-size:11px; font-weight: 550; display: block; margin-bottom: 4px;">Memo</span>
                    <input type="text" name="notes" value="<?= ($inv && isset($inv->notes)) ? htmlspecialchars((string)$inv->notes) : '' ?>" style="width:100%; height:28px; font-size:12px; border:0.5px solid var(--border); border-radius:6px; padding:0 8px; box-sizing:border-box; font-weight: 500;" />
                </div>

                <div style="background:var(--surface-1); border-radius:10px; padding:10px 14px; display:grid; grid-template-columns: 1fr auto; gap:6px 12px; align-items:center;">
                    <span style="color:var(--text-secondary); font-size:12px; font-weight: 500;">Subtotal LKR</span>
                    <span id="subTotal" style="font-weight:550; text-align:right; font-size:13px; font-family: monospace;">0.00</span>
                    
                    <span style="color:var(--text-secondary); font-size:12px; font-weight: 500;">Bill discount</span>
                    <div style="display:flex; gap:4px; justify-self:end; align-items: center;">
                        <input type="number" name="global_discount_val" id="globalDiscVal" value="<?= ($inv && isset($inv->global_discount_val)) ? floatval($inv->global_discount_val) : '0' ?>" oninput="calcTotals()" style="width:60px; height:24px; text-align:right; font-size:12px; border:0.5px solid var(--border); border-radius:4px; padding: 0 4px; font-weight: 550;" />
                        <select name="global_discount_type" id="globalDiscType" onchange="calcTotals()" style="width:46px; height:24px; font-size:12px; border:0.5px solid var(--border); border-radius:4px; background:#fff; font-weight: 550;">
                            <option value="Rs" <?= ($inv && isset($inv->global_discount_type) && $inv->global_discount_type == 'Rs') ? 'selected' : '' ?>>Rs</option>
                            <option value="%" <?= ($inv && isset($inv->global_discount_type) && $inv->global_discount_type == '%') ? 'selected' : '' ?>>%</option>
                        </select>
                    </div>
                    
                    <span style="font-weight:550; font-size:13px; border-top:0.5px solid var(--border); padding-top:6px;">Total LKR</span>
                    <span id="grandTotal" style="font-weight:600; font-size:15px; text-align:right; border-top:0.5px solid var(--border); padding-top:6px; font-family: monospace;">0.00</span>
                    
                    <span style="color:var(--text-accent); font-weight:550; font-size:13px;">Balance due LKR</span>
                    <span id="balanceDue" style="color:var(--text-accent); font-weight:600; font-size:15px; text-align:right; font-family: monospace;">0.00</span>
                </div>
            </div>

            <!-- Accounting setup (hidden receivables/revenue accounts) -->
            <div class="acc-settings">
                <select name="ar_account" required>
                    <?php foreach($assets ?? [] as $acc): ?>
                        <option value="<?= $acc->id ?>" <?= strpos(strtolower((string)($acc->account_name ?? '')), 'receivable') !== false ? 'selected' : '' ?>><?= htmlspecialchars((string)($acc->account_name ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="revenue_account" required>
                    <?php foreach($revenues ?? [] as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars((string)($acc->account_name ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- System ready status bar -->
            <div style="display:flex; align-items:center; gap:6px; color:var(--text-success); font-size:11px; padding-top:8px; border-top:0.5px solid var(--border); margin-top:5px; flex-shrink: 0; font-weight: 550;">
                <i class="ph ph-circle-fill" style="font-size:6px; vertical-align: middle;"></i> System ready
            </div>

        </form>
</div>
</div>

<!-- Modal backdrop for Registering New Customer -->
<div class="modal-backdrop" id="newCustomerModal" style="display: none; align-items: center; justify-content: center; z-index: 2000; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="modal-panel" style="max-width: 480px; width: 90%; background: #fff; border-radius: 6px; overflow: hidden; border: 1px solid #b0c4de; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: #2e7d32; color: #fff; padding: 10px 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
            <span>Register New Customer</span>
            <button type="button" onclick="closeNewCustomerModal()" style="background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer; font-weight:bold;">✕</button>
        </div>
        <form id="ajaxNewCustomerForm" onsubmit="submitAjaxNewCustomer(event)" style="margin: 0;">
            <div class="modal-body" style="padding: 15px; display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto;">
                <div>
                    <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Customer / Company Name <span style="color:red;">*</span></label>
                    <input type="text" name="name" required style="width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Phone Number</label>
                        <input type="text" name="phone" style="width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">WhatsApp Number</label>
                        <input type="text" name="whatsapp" style="width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Email Address</label>
                        <input type="email" name="email" style="width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Route / Area (MCA)</label>
                        <select name="mca_id" style="width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px; background: #fff;">
                            <option value="">Select Area...</option>
                            <?php foreach($mcaAreas as $area): ?>
                                <option value="<?= $area->id ?>"><?= htmlspecialchars($area->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; margin-bottom: 4px; display: block;">Billing Address</label>
                    <textarea name="address" style="width: 100%; height: 60px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 13px; resize: none;"></textarea>
                </div>
                <input type="hidden" name="latitude" value="">
                <input type="hidden" name="longitude" value="">
            </div>
            <div class="modal-footer" style="padding: 10px 15px; background: #f5f5f5; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="qb-btn" onclick="closeNewCustomerModal()" style="border:1px solid #ccc; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer;">Cancel</button>
                <button type="submit" class="qb-btn" style="background:#2e7d32; color:#fff; border-color:#2e7d32; padding:6px 14px; border-radius:4px; font-size:12px; cursor:pointer; font-weight: bold;">Add Customer</button>
            </div>
        </form>
    </div>
</div>

<?php if(isset($_GET['wa_id'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const waPhone = "<?= htmlspecialchars($_GET['wa_phone']) ?>";
        const waName = "<?= htmlspecialchars($_GET['wa_name']) ?>";
        const waId = "<?= htmlspecialchars($_GET['wa_id']) ?>";
        const publicInvoiceLink = "<?= APP_URL ?>/sales/show/" + waId;

        if (waPhone && waPhone.trim() !== '') {
            let cleanPhone = waPhone.replace(/[^\d+]/g, '');
            if(cleanPhone.startsWith('0')) {
                cleanPhone = '94' + cleanPhone.substring(1); 
            }
            
            const waMessage = `Dear ${waName},\n\nPlease find your invoice at the link below:\n${publicInvoiceLink}\n\nKindly review it and let us know if you have any questions.\n\nThank you.`;
            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(waMessage)}`;
            
            window.open(waUrl, '_blank');
        } else {
            alert("This customer does not have a valid WhatsApp or Phone number saved in their profile.");
        }
    });
</script>
<?php endif; ?>

<?php if(isset($_GET['print_id'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const printId = "<?= htmlspecialchars($_GET['print_id']) ?>";
        const printUrl = "<?= APP_URL ?>/sales/show/" + printId;
        window.open(printUrl, '_blank');
    });
</script>
<?php endif; ?>

<script>
    // Enhanced Catalog Data Engine providing Stock counts adjusted for reservation values
    const catalog = [
        <?php foreach($catalog_items as $item): ?>
            <?php 
            $hasVars = !empty($item->variations);
            $stockQty = isset($item->qty) ? $item->qty : ($item->quantity_on_hand ?? 0);
            $baseAvailable = $stockQty - ($item->quantity_reserved ?? 0);
            $catName = htmlspecialchars(addslashes((string)($item->category_name ?? '')));
            $sampleCode = htmlspecialchars(addslashes((string)($item->sample_code ?? '')));
            ?>
            <?php if($hasVars): ?>
                { id: "<?= $item->id ?>|MIX|1", type: "<?= $item->type ?? 'Inventory' ?>", stock: <?= floatval($baseAvailable) ?>, code: "<?= htmlspecialchars((string)($item->item_code ?? '')) ?>", name: "<?= htmlspecialchars(addslashes((string)($item->name ?? ''))) ?> (MIX)", price: <?= floatval($item->price ?? 0) ?>, category: "<?= $catName ?>", sample_code: "<?= $sampleCode ?>" },
                <?php foreach($item->variations as $var): ?>
                <?php $varAvailable = ($var->quantity_on_hand ?? 0) - ($var->quantity_reserved ?? 0); ?>
                { id: "<?= $item->id ?>|<?= $var->id ?>|0", type: "<?= $item->type ?? 'Inventory' ?>", stock: <?= floatval($varAvailable) ?>, code: "<?= htmlspecialchars(addslashes((string)($var->sku ?? $item->item_code ?? ''))) ?>", name: "<?= htmlspecialchars(addslashes((string)($item->name ?? ''))) ?> - <?= htmlspecialchars(addslashes((string)($var->variation_name ?? ''))) ?>: <?= htmlspecialchars(addslashes((string)($var->value_name ?? ''))) ?>", price: <?= floatval(isset($var->price) && $var->price > 0 ? $var->price : ($item->price ?? 0)) ?>, category: "<?= $catName ?>", sample_code: "<?= $sampleCode ?>" },
                <?php endforeach; ?>
            <?php else: ?>
                { id: "<?= $item->id ?>|0|0", type: "<?= $item->type ?? 'Inventory' ?>", stock: <?= floatval($baseAvailable) ?>, code: "<?= htmlspecialchars((string)($item->item_code ?? '')) ?>", name: "<?= htmlspecialchars(addslashes((string)($item->name ?? ''))) ?>", price: <?= floatval($item->price ?? 0) ?>, category: "<?= $catName ?>", sample_code: "<?= $sampleCode ?>" },
            <?php endif; ?>
        <?php endforeach; ?>
    ];

    // Customizable Discount Rules Engine Configuration
    const activeDiscountRules = <?= json_encode($data['active_discount_rules'] ?? []) ?>;
    const promptedDiscounts = {
        itemRules: {},
        billRules: {}
    };

    const customers = [
        <?php foreach($customers as $c): ?>
        {
            id: "<?= $c->id ?>",
            name: <?= json_encode((string)($c->name ?? '')) ?>,
            phone: <?= json_encode((string)($c->phone ?? '')) ?>,
            address: <?= json_encode((string)($c->address ?? '')) ?>,
            mca: <?= json_encode((string)($c->mca_name ?? $c->territory ?? '')) ?>,
            outstanding: <?= floatval($c->outstanding_balance ?? 0) ?>,
            credit_limit: <?= floatval($c->credit_limit ?? 0) ?>
        },
        <?php endforeach; ?>
    ];

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function filterCustomerSearch(e) {
        const val = e.target.value.toLowerCase().trim();
        const resList = document.getElementById('customerSearchResults');
        resList.innerHTML = '';
        if(!val) { resList.style.display = 'none'; return; }

        const filtered = customers.filter(c => 
            c.name.toLowerCase().includes(val) || 
            c.phone.toLowerCase().includes(val) || 
            c.mca.toLowerCase().includes(val) || 
            c.address.toLowerCase().includes(val)
        ).slice(0, 10);

        if(filtered.length === 0) {
            const li = document.createElement('li');
            li.style.padding = '8px 10px';
            li.style.color = '#888';
            li.innerText = 'No customers found';
            resList.appendChild(li);
            resList.style.display = 'block';
            return;
        }

        filtered.forEach(cust => {
            const li = document.createElement('li');
            li.style.padding = '8px 10px';
            li.style.borderBottom = '1px solid #eee';
            li.style.cursor = 'pointer';
            li.style.display = 'flex';
            li.style.flexDirection = 'column';
            
            li.innerHTML = `
                <div style="width: 100%;">
                    <strong style="font-size: 12px; color: #111;">${escapeHtml(cust.name)}</strong>
                    <div style="font-size: 10px; color: #666; margin-top: 3px; line-height: 1.3;">
                        ${cust.phone ? `<i class="ph ph-phone" style="font-size:11px;"></i> ${escapeHtml(cust.phone)}<br>` : ''}
                        ${cust.mca ? `<i class="ph ph-map-pin" style="font-size:11px;"></i> Route: <strong>${escapeHtml(cust.mca)}</strong><br>` : ''}
                        ${cust.address ? `<span style="font-style: italic; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;"><i class="ph ph-house" style="font-size:11px;"></i> ${escapeHtml(cust.address)}</span>` : ''}
                    </div>
                </div>
            `;
            
            li.onclick = () => {
                selectCustomer(cust);
            };
            resList.appendChild(li);
        });
        resList.style.display = 'block';
    }

    let selectedCustomerObj = null;

    function showCreditLimitBlockDialog(customerName, creditLimit, projected, exceeded) {
        const backdrop = document.createElement('div');
        backdrop.style.position = 'fixed';
        backdrop.style.top = '0';
        backdrop.style.left = '0';
        backdrop.style.width = '100%';
        backdrop.style.height = '100%';
        backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
        backdrop.style.backdropFilter = 'blur(4px)';
        backdrop.style.zIndex = '10000';
        backdrop.style.display = 'flex';
        backdrop.style.alignItems = 'center';
        backdrop.style.justifyContent = 'center';

        const panel = document.createElement('div');
        panel.style.backgroundColor = '#fff';
        panel.style.padding = '28px';
        panel.style.borderRadius = '12px';
        panel.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
        panel.style.width = '90%';
        panel.style.maxWidth = '450px';
        panel.style.fontFamily = 'system-ui, -apple-system, sans-serif';
        panel.style.border = '1px solid #fee2e2';

        const header = document.createElement('div');
        header.style.display = 'flex';
        header.style.alignItems = 'center';
        header.style.gap = '12px';
        header.style.marginBottom = '16px';
        header.innerHTML = `
            <div style="background-color: #fee2e2; color: #ef4444; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <h3 style="margin: 0; color: #991b1b; font-size: 18px; font-weight: 700;">Credit Limit Exceeded</h3>
        `;
        panel.appendChild(header);

        const body = document.createElement('div');
        body.style.fontSize = '14px';
        body.style.color = '#374151';
        body.style.lineHeight = '1.6';
        body.style.marginBottom = '24px';
        body.innerHTML = `
            <p style="margin: 0 0 12px 0;">This transaction has been blocked. Finalizing this invoice will exceed the credit limit for <strong>${escapeHtml(customerName)}</strong>.</p>
            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; display: grid; gap: 8px; font-family: monospace;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7280;">Credit Limit:</span>
                    <span style="font-weight: bold; color: #111827;">Rs. ${creditLimit.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7280;">Projected Balance:</span>
                    <span style="font-weight: bold; color: #991b1b;">Rs. ${projected.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 4px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 13px; color: #b91c1c; font-weight: bold;">
                    <span>Exceeded Amount:</span>
                    <span>Rs. ${exceeded.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
            </div>
        `;
        panel.appendChild(body);

        const footer = document.createElement('div');
        footer.style.display = 'flex';
        footer.style.justifyContent = 'flex-end';
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.innerText = 'Dismiss';
        closeBtn.style.padding = '8px 20px';
        closeBtn.style.backgroundColor = '#1f2937';
        closeBtn.style.color = '#fff';
        closeBtn.style.border = 'none';
        closeBtn.style.borderRadius = '6px';
        closeBtn.style.fontWeight = '600';
        closeBtn.style.cursor = 'pointer';
        closeBtn.style.fontSize = '13px';
        closeBtn.onclick = () => { backdrop.remove(); };
        footer.appendChild(closeBtn);
        panel.appendChild(footer);

        backdrop.appendChild(panel);
        document.body.appendChild(backdrop);
    }

    function updateProjectedOutstandingIndicator() {
        if (!selectedCustomerObj) return;
        
        const outBal = parseFloat(selectedCustomerObj.outstanding) || 0;
        const limit = parseFloat(selectedCustomerObj.credit_limit) || 0;
        
        const outDiv = document.getElementById('customerOutstanding');
        const outPlaceholder = document.getElementById('customerOutstandingPlaceholder');
        if (!outDiv) return;

        const subTotalText = document.getElementById('grandTotal').innerText.replace(/,/g, '').replace('Rs. ', '');
        const newTotal = parseFloat(subTotalText) || 0;
        
        let oldInvoiceTotal = 0;
        <?php if ($inv): ?>
            const oldSub = parseFloat("<?= isset($inv->total_amount) ? $inv->total_amount : 0 ?>") || 0;
            const oldDiscVal = parseFloat("<?= isset($inv->global_discount_val) ? $inv->global_discount_val : 0 ?>") || 0;
            const oldDiscType = "<?= isset($inv->global_discount_type) ? $inv->global_discount_type : 'Rs' ?>";
            const oldDisc = (oldDiscType === '%') ? (oldSub * oldDiscVal / 100) : oldDiscVal;
            oldInvoiceTotal = (oldSub - oldDisc) + (parseFloat("<?= isset($inv->tax_amount) ? $inv->tax_amount : 0 ?>") || 0);
        <?php endif; ?>
        
        const projected = outBal - oldInvoiceTotal + newTotal;

        if (outPlaceholder) outPlaceholder.style.display = 'none';
        outDiv.style.display = 'flex';
        
        let limitHtml = '';
        if (limit > 0) {
            limitHtml = `<span style="color:var(--text-primary); font-weight:550; font-size:13.5px;">Rs. ${limit.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        } else {
            limitHtml = `<span style="color:var(--text-success); font-weight:550; font-size:13.5px;">No limit</span>`;
        }

        let outHtml = '';
        if (outBal > 0.01) {
            outHtml = `<span style="color:var(--text-danger); font-weight:550; font-size:13.5px;">Rs. ${outBal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        } else if (outBal < -0.01) {
            outHtml = `<span style="color:var(--text-success); font-weight:550; font-size:13.5px;">Rs. ${Math.abs(outBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (Overpaid)</span>`;
        } else {
            outHtml = `<span style="color:var(--text-secondary); font-size:13.5px;">Rs. 0.00</span>`;
        }

        let remainingHtml = '';
        if (limit > 0) {
            const remaining = limit - projected;
            if (remaining >= 0) {
                remainingHtml = `<span style="color:var(--text-success); font-size:11px; font-weight:550; margin-top:2px;"><i class="ph ph-check-circle" style="font-size:11px;"></i> Remaining: Rs. ${remaining.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
            } else {
                remainingHtml = `<span style="color:var(--text-danger); font-size:11px; font-weight:600; margin-top:2px;"><i class="ph ph-warning-circle" style="font-size:11px;"></i> Over limit by: Rs. ${Math.abs(remaining).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
            }
        }

        outDiv.innerHTML = `
            <span style="color:var(--text-secondary); font-size:11px; font-weight:550;">Credit limit</span>
            ${limitHtml}
            <span style="color:var(--text-secondary); font-size:11px; margin-top:4px; font-weight:550;">Outstanding</span>
            ${outHtml}
            ${remainingHtml}
        `;
    }

    function selectCustomer(cust) {
        selectedCustomerObj = cust;
        document.getElementById('customerIdInput').value = cust.id;
        document.getElementById('customerSearch').value = cust.name;
        document.getElementById('customerSearchResults').style.display = 'none';
        
        document.getElementById('billToAddress').value = cust.address || '';
        
        const mcaInput = document.getElementById('displayMca');
        if(mcaInput) {
            mcaInput.value = cust.mca || '';
        }
        
        const phoneInput = document.getElementById('displayPhone');
        if(phoneInput) {
            phoneInput.value = cust.phone || '';
        }

        // Show customer details and hide search container in Card 1
        const searchContainer = document.getElementById('customerSearchContainer');
        const detailsContainer = document.getElementById('customerDetailsContainer');
        if (searchContainer) searchContainer.style.display = 'none';
        if (detailsContainer) {
            detailsContainer.style.display = 'flex';
            document.getElementById('selectedCustomerName').textContent = cust.name;
            document.getElementById('selectedCustomerAddress').textContent = cust.address || 'No address provided';
        }

        const optsContainer = document.getElementById('customerOptionsContainer');
        if(optsContainer) {
            optsContainer.style.display = 'flex';
        }

        updateProjectedOutstandingIndicator();
    }

    function clearSelectedCustomer() {
        selectedCustomerObj = null;
        document.getElementById('customerIdInput').value = '';
        document.getElementById('customerSearch').value = '';
        document.getElementById('billToAddress').value = '';
        
        const searchContainer = document.getElementById('customerSearchContainer');
        const detailsContainer = document.getElementById('customerDetailsContainer');
        if (searchContainer) searchContainer.style.display = 'block';
        if (detailsContainer) detailsContainer.style.display = 'none';
        
        const mcaInput = document.getElementById('displayMca');
        if(mcaInput) mcaInput.value = '';
        
        const phoneInput = document.getElementById('displayPhone');
        if(phoneInput) phoneInput.value = '';
        
        const outDiv = document.getElementById('customerOutstanding');
        const outPlaceholder = document.getElementById('customerOutstandingPlaceholder');
        if (outDiv) outDiv.style.display = 'none';
        if (outPlaceholder) outPlaceholder.style.display = 'flex';
        
        calcTotals();
    }

    function openCustomerEdit() {
        if (selectedCustomerObj && selectedCustomerObj.id) {
            window.open('<?= APP_URL ?>/customer/index/' + selectedCustomerObj.id + '?tab=profile', '_blank');
        }
    }

    function openCustomerHistory() {
        if (selectedCustomerObj && selectedCustomerObj.id) {
            window.open('<?= APP_URL ?>/customer/index/' + selectedCustomerObj.id + '?tab=invoices', '_blank');
        }
    }

    let activeSearchIndex = -1;

    document.addEventListener("DOMContentLoaded", function() {
        const itemSearch = document.getElementById('itemSearch');
        const resList = document.getElementById('searchResults');
        if (!itemSearch || !resList) return;

        itemSearch.addEventListener('input', function(e) {
            const val = this.value.toLowerCase().trim();
            resList.innerHTML = '';
            activeSearchIndex = -1;
            if(!val) { resList.style.display = 'none'; return; }

            const filtered = catalog.filter(i => 
                i.name.toLowerCase().includes(val) || 
                i.code.toLowerCase().includes(val) ||
                (i.category && i.category.toLowerCase().includes(val)) ||
                (i.sample_code && i.sample_code.toLowerCase().includes(val))
            ).slice(0, 15);

            if(filtered.length === 0) { 
                resList.style.display = 'none'; 
                return; 
            }

            filtered.forEach((item, index) => {
                const li = document.createElement('li');
                li.setAttribute('data-index', index);
                li.style.padding = '8px 10px';
                li.style.borderBottom = '1px solid #eee';
                li.style.cursor = 'pointer';
                li.style.display = 'flex';
                li.style.justifyContent = 'space-between';

                // Build Stock Badge
                let stockBadge = '';
                if (item.type === 'Service') {
                    stockBadge = `<span style="color:#0066cc; font-size: 11px; font-weight:bold;">Service</span>`;
                } else if (item.stock > 0) {
                    stockBadge = `<span style="color:#2e7d32; font-size: 11px; font-weight:bold;">Stock: ${item.stock}</span>`;
                } else {
                    stockBadge = `<span style="color:#c62828; font-size: 11px; font-weight:bold;">Out of Stock</span>`;
                }

                li.innerHTML = `
                    <div><strong>${item.name}</strong><br><span style="font-size: 11px; color: #888;">SKU: ${item.code || 'N/A'}${item.sample_code ? ' | Sample: ' + item.sample_code : ''} | ${stockBadge}</span></div>
                    <div style="color: #0066cc; font-family: monospace; font-weight: bold; font-size: 14px;">Rs: ${item.price.toFixed(2)}</div>
                `;
                
                li.addEventListener('click', function() {
                    selectSearchItem(item);
                });

                resList.appendChild(li);
            });
            resList.style.display = 'block';
        });

        itemSearch.addEventListener('keydown', function(e) {
            const items = resList.querySelectorAll('li');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSearchIndex++;
                if (activeSearchIndex >= items.length) activeSearchIndex = 0;
                highlightSearchItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSearchIndex--;
                if (activeSearchIndex < 0) activeSearchIndex = items.length - 1;
                highlightSearchItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSearchIndex >= 0 && activeSearchIndex < items.length) {
                    items[activeSearchIndex].click();
                } else if (items.length > 0) {
                    items[0].click();
                }
            }
        });
    });

    function highlightSearchItem(items) {
        items.forEach((item, index) => {
            if (index === activeSearchIndex) {
                item.style.backgroundColor = '#0066cc';
                item.style.color = '#fff';
                const priceDiv = item.querySelector('div:last-child');
                if (priceDiv) priceDiv.style.color = '#fff';
                const spanSku = item.querySelector('span');
                if (spanSku) spanSku.style.color = '#e2e8f0';
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.style.backgroundColor = '';
                item.style.color = '';
                const priceDiv = item.querySelector('div:last-child');
                if (priceDiv) priceDiv.style.color = '#0066cc';
                const spanSku = item.querySelector('span');
                if (spanSku) spanSku.style.color = '#888';
            }
        });
    }

    function selectSearchItem(item) {
        if (item.type !== 'Service' && item.stock <= 0) {
            alert("Cannot add item! It is currently out of stock.");
            return;
        }
        addItemRow(item); 
        const itemSearch = document.getElementById('itemSearch');
        if (itemSearch) {
            itemSearch.value = '';
        }
        document.getElementById('searchResults').style.display = 'none'; 
        activeSearchIndex = -1;
    }

    function openNewCustomerModal() {
        document.getElementById('newCustomerModal').style.display = 'flex';
        const form = document.getElementById('ajaxNewCustomerForm');
        if (form) form.reset();
    }

    function closeNewCustomerModal() {
        document.getElementById('newCustomerModal').style.display = 'none';
    }

    function submitAjaxNewCustomer(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch('<?= APP_URL ?>/customer/api_add_customer', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const newCust = {
                    id: String(data.customer.id),
                    name: data.customer.name,
                    phone: data.customer.phone,
                    address: data.customer.address,
                    mca: data.customer.mca || '',
                    outstanding: 0.00
                };
                customers.push(newCust);
                selectCustomer(newCust);
                closeNewCustomerModal();
                alert("Customer added and selected successfully!");
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            alert("Connection error! Failed to register customer.");
            console.error(err);
        });
    }

    document.addEventListener('click', function(e) {
        if(e.target.id !== 'itemSearch') { document.getElementById('searchResults').style.display = 'none'; }
        if(e.target.id !== 'customerSearch') { document.getElementById('customerSearchResults').style.display = 'none'; }
    });

    // Validates the quantity typed by user against available stock
    function validateQty(input, maxStock) {
        if (maxStock !== null) {
            let val = parseFloat(input.value);
            if (val > maxStock) {
                alert("Insufficient inventory! Available stock for this item is: " + maxStock);
                input.value = maxStock;
            }
        }
    }

    function renumberInvoiceRows() {
        const rows = document.querySelectorAll('#invoiceBody tr:not(#emptyPlaceholderRow)');
        const placeholder = document.getElementById('emptyPlaceholderRow');
        if (rows.length === 0) {
            if (placeholder) placeholder.style.display = '';
        } else {
            if (placeholder) placeholder.style.display = 'none';
            rows.forEach((tr, i) => {
                const cell = tr.querySelector('.line-row-num');
                if (cell) cell.textContent = i + 1;
            });
        }
    }

    function addItemRow(itemOrId, code = null, qty = 1, desc = null, price = null, discVal = 0, discType = 'Rs') {
        let item = {};
        let isDuplicate = false;
        if (typeof itemOrId === 'object' && itemOrId !== null) {
            // Interactive UI addition from search selection
            item = itemOrId;
            qty = 1;
            desc = item.name;
            price = item.price;
            discVal = 0;
            discType = 'Rs';

            // Check for duplicates in the existing invoice rows
            document.querySelectorAll('input[name="item_selection[]"]').forEach(input => {
                if (input.value === item.id) {
                    isDuplicate = true;
                }
            });
        } else {
            // Programmatic loading addition from existing edit items loop
            let matched = catalog.find(c => c.id === itemOrId);
            item = {
                id: itemOrId,
                code: code,
                name: desc,
                price: price,
                stock: matched ? matched.stock : 9999,
                type: matched ? matched.type : 'Inventory'
            };
        }

        const tbody = document.getElementById('invoiceBody');
        const tr = document.createElement('tr');
        tr.style.borderTop = '0.5px solid var(--border)';
        
        let maxAttr = item.type === 'Service' ? '' : `max="${item.stock}"`;

        // Diagnostic Console Trace Log
        console.log("[addItemRow] Appending line item details in Canvas workspace:", {
            item_id: item.id,
            sku: item.code || code,
            quantity: qty,
            name: desc,
            price_parsed: parseFloat(price).toFixed(2),
            item_discount: discVal,
            discount_type: discType
        });

        tr.innerHTML = `
            <td class="line-row-num" style="padding:6px; color:var(--text-muted); text-align:left; vertical-align:middle;"></td>
            <td style="padding:6px; vertical-align:middle;">
                <input type="text" value="${item.code || 'ITEM'}" readonly style="color:var(--text-primary); font-size:12px; width: 100%; border:none; background:transparent;">
                <input type="hidden" name="item_selection[]" value="${item.id}">
            </td>
            <td style="padding:6px; text-align: right; vertical-align:middle;">
                <input type="number" class="num" name="qty[]" value="${qty}" min="0.01" step="0.01" ${maxAttr} oninput="validateQty(this, ${item.type === 'Service' ? 'null' : item.stock}); calcTotals()" onkeydown="handleQtyKeydown(event, this)" required style="width: 100%; text-align: right; border: none; background: transparent; font-size: 12px;">
            </td>
            <td style="padding:6px; vertical-align:middle;"><input type="text" name="desc[]" value="${desc}" required style="width: 100%; border:none; background:transparent; font-size: 12px;"></td>
            <td style="padding:6px; text-align: right; vertical-align:middle;"><input type="number" class="num" name="price[]" value="${parseFloat(price).toFixed(2)}" step="0.01" min="0" oninput="calcTotals()" required style="width: 100%; text-align: right; border:none; background:transparent; font-size: 12px;"></td>
            <td style="padding:6px; vertical-align:middle;">
                <div class="discount-cell">
                    <input type="number" class="num" name="item_discount_val[]" value="${parseFloat(discVal).toFixed(2)}" step="0.01" oninput="calcTotals()">
                    <select name="item_discount_type[]" onchange="calcTotals()">
                        <option value="Rs" ${discType === 'Rs' ? 'selected' : ''}>Rs</option>
                        <option value="%" ${discType === '%' ? 'selected' : ''}>%</option>
                    </select>
                </div>
            </td>
            <td style="padding:6px; text-align: right; vertical-align:middle;"><input type="text" class="num line-total" value="0.00" readonly style="font-weight:550; background: transparent; border: none; width: 100%; text-align: right; font-size: 12px; color: var(--text-primary);"></td>
            <td style="padding:6px; color:var(--text-muted); font-size:11px; vertical-align:middle;">${item.type === 'Service' ? '—' : item.stock}</td>
            <td style="padding:6px; text-align:center; vertical-align:middle;"><button type="button" tabindex="-1" style="background:transparent; color:var(--text-danger); border:none; cursor:pointer; font-size: 14px; padding: 4px;" onclick="this.closest('tr').remove(); renumberInvoiceRows(); calcTotals();"><i class="ph ph-trash"></i></button></td>
        `;
        tbody.insertAdjacentElement('afterbegin', tr);
        renumberInvoiceRows();
        
        // Trigger validation immediately upon adding in case stock is 0
        const qtyInput = tr.querySelector('input[name="qty[]"]');
        validateQty(qtyInput, item.type === 'Service' ? null : item.stock);
        
        calcTotals();
        
        document.querySelector('.table-scroll-container').scrollTop = 0;

        if (typeof itemOrId === 'object' && itemOrId !== null) {
            setTimeout(() => {
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            }, 50);
        }

        if (isDuplicate) {
            showDuplicateWarning(item.name, null, () => {
                tr.remove();
                renumberInvoiceRows();
                calcTotals();
            });
        }
    }

    function showDuplicateWarning(itemName, onKeep, onRemove) {
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.style.position = 'fixed';
        backdrop.style.top = '0';
        backdrop.style.left = '0';
        backdrop.style.width = '100%';
        backdrop.style.height = '100%';
        backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        backdrop.style.zIndex = '9999';
        backdrop.style.display = 'flex';
        backdrop.style.alignItems = 'center';
        backdrop.style.justifyContent = 'center';

        // Create modal content panel
        const panel = document.createElement('div');
        panel.style.backgroundColor = '#fff';
        panel.style.padding = '24px';
        panel.style.borderRadius = '8px';
        panel.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.2)';
        panel.style.width = '90%';
        panel.style.maxWidth = '400px';
        panel.style.textAlign = 'center';
        panel.style.fontFamily = 'system-ui, -apple-system, sans-serif';

        // Add Icon / Title
        const title = document.createElement('h3');
        title.innerHTML = '<i class="ph ph-warning" style="color:#d97706; font-size:18px;"></i> Duplicate Product Warning';
        title.style.margin = '0 0 12px 0';
        title.style.color = '#d97706';
        title.style.fontSize = '18px';

        // Add message
        const msg = document.createElement('p');
        msg.innerHTML = `Product <strong>${itemName}</strong> is already added to this billing.<br><br>Do you want to keep both or remove the duplicate?`;
        msg.style.margin = '0 0 20px 0';
        msg.style.color = '#4b5563';
        msg.style.fontSize = '14px';
        msg.style.lineHeight = '1.5';

        // Create actions container
        const actions = document.createElement('div');
        actions.style.display = 'flex';
        actions.style.justifyContent = 'center';
        actions.style.gap = '12px';

        // Remove button
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = '<i class="ph ph-trash"></i> Remove Duplicate';
        removeBtn.style.padding = '8px 16px';
        removeBtn.style.border = '1px solid #dc2626';
        removeBtn.style.backgroundColor = '#fff';
        removeBtn.style.color = '#dc2626';
        removeBtn.style.borderRadius = '6px';
        removeBtn.style.fontWeight = '600';
        removeBtn.style.cursor = 'pointer';
        removeBtn.style.fontSize = '13px';
        removeBtn.onclick = () => {
            document.body.removeChild(backdrop);
            if (onRemove) onRemove();
        };

        // Keep button
        const keepBtn = document.createElement('button');
        keepBtn.type = 'button';
        keepBtn.innerHTML = '<i class="ph ph-check"></i> Keep Both';
        keepBtn.style.padding = '8px 16px';
        keepBtn.style.border = 'none';
        keepBtn.style.backgroundColor = '#10b981';
        keepBtn.style.color = '#fff';
        keepBtn.style.borderRadius = '6px';
        keepBtn.style.fontWeight = '600';
        keepBtn.style.cursor = 'pointer';
        keepBtn.style.fontSize = '13px';
        keepBtn.onclick = () => {
            document.body.removeChild(backdrop);
            if (onKeep) onKeep();
        };

        actions.appendChild(removeBtn);
        actions.appendChild(keepBtn);
        panel.appendChild(title);
        panel.appendChild(msg);
        panel.appendChild(actions);
        backdrop.appendChild(panel);
        document.body.appendChild(backdrop);
    }

    function handleQtyKeydown(event, input) {
        if (event.key === 'Enter') {
            event.preventDefault();
            // Focus back on search bar
            const itemSearch = document.getElementById('itemSearch');
            if (itemSearch) {
                itemSearch.focus();
                itemSearch.select();
            }
        }
    }

    function calcTotals() {
        let subTotal = 0;
        document.querySelectorAll('#invoiceBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const discVal = parseFloat(row.querySelector('input[name="item_discount_val[]"]').value) || 0;
            const discType = row.querySelector('select[name="item_discount_type[]"]').value;

            let rowGross = qty * price;
            let rowDisc = (discType === '%') ? (rowGross * discVal / 100) : discVal;
            let rowNet = rowGross - rowDisc;
            if (rowNet < 0) rowNet = 0; 
            
            const totalInput = row.querySelector('.line-total');
            if (totalInput) totalInput.value = rowNet.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            subTotal += rowNet;
        });

        const globalDiscVal = parseFloat(document.getElementById('globalDiscVal').value) || 0;
        const globalDiscType = document.getElementById('globalDiscType').value;
        let globalDisc = (globalDiscType === '%') ? (subTotal * globalDiscVal / 100) : globalDiscVal;
        
        let grandTotal = subTotal - globalDisc;
        if (grandTotal < 0) grandTotal = 0;

        // Diagnostic Console Trace Log for Calculations
        console.log("[calcTotals] Invoicing state sum totals mapped in Canvas:", {
            sub_total: subTotal,
            discount_deducted: globalDisc,
            grand_total: grandTotal
        });

        document.getElementById('subTotal').innerText = subTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('grandTotal').innerText = grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('balanceDue').innerText = grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Run Customizable Discount Checks
        checkDiscounts();

        // Update credit limit indicator dynamically
        updateProjectedOutstandingIndicator();
    }

    function showDiscountPrompt(title, message, onAccept, onReject) {
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.style.position = 'fixed';
        backdrop.style.top = '0';
        backdrop.style.left = '0';
        backdrop.style.width = '100%';
        backdrop.style.height = '100%';
        backdrop.style.backgroundColor = 'rgba(0,0,0,0.5)';
        backdrop.style.zIndex = '9999';
        backdrop.style.display = 'flex';
        backdrop.style.alignItems = 'center';
        backdrop.style.justifyContent = 'center';

        // Create modal panel
        const panel = document.createElement('div');
        panel.style.maxWidth = '420px';
        panel.style.width = '90%';
        panel.style.backgroundColor = '#fff';
        panel.style.borderRadius = '8px';
        panel.style.overflow = 'hidden';
        panel.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
        panel.style.border = '1px solid #cbd5e1';
        panel.style.fontFamily = 'Inter, sans-serif';

        // Header
        const header = document.createElement('div');
        header.style.backgroundColor = '#1e3a8a'; // Blue-900
        header.style.color = '#fff';
        header.style.padding = '14px 18px';
        header.style.fontWeight = 'bold';
        header.style.fontSize = '14px';
        header.style.display = 'flex';
        header.style.alignItems = 'center';
        header.style.gap = '8px';
        header.innerHTML = `<i class="fa-solid fa-gift text-amber-400"></i> ${title}`;
        panel.appendChild(header);

        // Body
        const body = document.createElement('div');
        body.style.padding = '22px 18px';
        body.style.fontSize = '13px';
        body.style.color = '#334155';
        body.style.lineHeight = '1.6';
        body.innerText = message;
        panel.appendChild(body);

        // Footer
        const footer = document.createElement('div');
        footer.style.padding = '12px 18px';
        footer.style.backgroundColor = '#f8fafc';
        footer.style.borderTop = '1px solid #e2e8f0';
        footer.style.display = 'flex';
        footer.style.justifyContent = 'flex-end';
        footer.style.gap = '10px';

        // Reject button
        const btnReject = document.createElement('button');
        btnReject.type = 'button';
        btnReject.style.padding = '8px 16px';
        btnReject.style.borderRadius = '6px';
        btnReject.style.border = '1px solid #cbd5e1';
        btnReject.style.backgroundColor = '#fff';
        btnReject.style.color = '#475569';
        btnReject.style.fontSize = '12px';
        btnReject.style.fontWeight = 'bold';
        btnReject.style.cursor = 'pointer';
        btnReject.style.transition = 'all 0.2s';
        btnReject.innerText = 'No, Thanks';
        btnReject.onclick = () => {
            backdrop.remove();
            if (onReject) onReject();
        };
        footer.appendChild(btnReject);

        // Accept button
        const btnAccept = document.createElement('button');
        btnAccept.type = 'button';
        btnAccept.style.padding = '8px 16px';
        btnAccept.style.borderRadius = '6px';
        btnAccept.style.border = 'none';
        btnAccept.style.backgroundColor = '#2563eb'; // Blue-600
        btnAccept.style.color = '#fff';
        btnAccept.style.fontSize = '12px';
        btnAccept.style.fontWeight = 'bold';
        btnAccept.style.cursor = 'pointer';
        btnAccept.style.transition = 'all 0.2s';
        btnAccept.innerText = 'Accept Offer';
        btnAccept.onclick = () => {
            backdrop.remove();
            if (onAccept) onAccept();
        };
        footer.appendChild(btnAccept);

        panel.appendChild(footer);
        backdrop.appendChild(panel);
        document.body.appendChild(backdrop);
    }

    function checkDiscounts() {
        if (!activeDiscountRules || activeDiscountRules.length === 0) return;

        // 1. Gather all current items and their quantities from the table
        const cartItems = {};
        document.querySelectorAll('#invoiceBody tr').forEach(row => {
            const itemId = row.querySelector('input[name="item_selection[]"]')?.value;
            const qty = parseFloat(row.querySelector('input[name="qty[]"]')?.value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]')?.value) || 0;
            
            if (itemId) {
                const baseItemId = itemId.split('|')[0];
                // Ignore free issue rows when counting standard billing quantities
                if (price > 0.001) {
                    cartItems[baseItemId] = (cartItems[baseItemId] || 0) + qty;
                }
            }
        });

        // 2. Process Item-wise rules
        activeDiscountRules.forEach(rule => {
            if (rule.rule_type === 'item_wise' && rule.target_item_id) {
                const targetItemId = String(rule.target_item_id);
                const totalQty = cartItems[targetItemId] || 0;

                if (totalQty > 0) {
                    // Find the best tier satisfied by the total QTY
                    let bestTier = null;
                    rule.tiers.forEach(tier => {
                        const min = parseFloat(tier.min_threshold);
                        if (totalQty >= min) {
                            if (!bestTier || min > parseFloat(bestTier.min_threshold)) {
                                bestTier = tier;
                            }
                        }
                    });

                    const promptKey = `${rule.id}-${targetItemId}`;
                    if (bestTier) {
                        const rewardQty = parseFloat(bestTier.reward_val);
                        // Check if we have prompted for this tier
                        if (promptedDiscounts.itemRules[promptKey] !== String(bestTier.id)) {
                            promptedDiscounts.itemRules[promptKey] = String(bestTier.id);

                            // Find the composite ID that triggered this to add the correct variant free issue
                            let sourceCompositeId = null;
                            let maxQtyForComp = 0;
                            document.querySelectorAll('#invoiceBody tr').forEach(row => {
                                const rowCompId = row.querySelector('input[name="item_selection[]"]')?.value;
                                const rowPrice = parseFloat(row.querySelector('input[name="price[]"]')?.value) || 0;
                                const rowQty = parseFloat(row.querySelector('input[name="qty[]"]')?.value) || 0;
                                if (rowCompId && rowCompId.split('|')[0] === targetItemId && rowPrice > 0.001) {
                                    if (rowQty > maxQtyForComp) {
                                        maxQtyForComp = rowQty;
                                        sourceCompositeId = rowCompId;
                                    }
                                }
                            });
                            
                            if (!sourceCompositeId) {
                                sourceCompositeId = `${targetItemId}|0|0`;
                            }

                            const targetItem = catalog.find(c => c.id === sourceCompositeId) || catalog.find(c => c.id.split('|')[0] === targetItemId);
                            const itemName = targetItem ? targetItem.name : 'Product';

                            showDiscountPrompt(
                                "Free Issue Promotion!",
                                `Your billed quantity (${totalQty}) for ${itemName} qualifies for ${rewardQty} free unit(s). Add this free issue to the bill?`,
                                function() {
                                    let foundFree = false;
                                    document.querySelectorAll('#invoiceBody tr').forEach(row => {
                                        const rowId = row.querySelector('input[name="item_selection[]"]')?.value;
                                        const rowPrice = parseFloat(row.querySelector('input[name="price[]"]')?.value) || 0;
                                        if (rowId === sourceCompositeId && rowPrice <= 0.001) {
                                            const qtyInput = row.querySelector('input[name="qty[]"]');
                                            if (qtyInput) {
                                                qtyInput.value = rewardQty;
                                                foundFree = true;
                                            }
                                        }
                                    });

                                    if (!foundFree) {
                                        addItemRow(
                                            sourceCompositeId,
                                            targetItem ? targetItem.code : 'FREE',
                                            rewardQty,
                                            `${itemName} [FREE ISSUE]`,
                                            0.00,
                                            0,
                                            'Rs'
                                        );
                                    }
                                    calcTotals();
                                },
                                function() {
                                    // Rejected
                                }
                            );
                        }
                    }
                }
            }
        });

        // 3. Process Bill-wise rules
        // Calculate subtotal BEFORE global discount is applied
        let subTotalBeforeGlobal = 0;
        document.querySelectorAll('#invoiceBody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            const discVal = parseFloat(row.querySelector('input[name="item_discount_val[]"]').value) || 0;
            const discType = row.querySelector('select[name="item_discount_type[]"]').value;

            let rowGross = qty * price;
            let rowDisc = (discType === '%') ? (rowGross * discVal / 100) : discVal;
            let rowNet = rowGross - rowDisc;
            if (rowNet < 0) rowNet = 0;
            subTotalBeforeGlobal += rowNet;
        });

        activeDiscountRules.forEach(rule => {
            if (rule.rule_type === 'bill_wise') {
                // Find the best tier satisfied by the subtotal
                let bestTier = null;
                rule.tiers.forEach(tier => {
                    const min = parseFloat(tier.min_threshold);
                    const max = tier.max_threshold ? parseFloat(tier.max_threshold) : Infinity;
                    if (subTotalBeforeGlobal >= min && subTotalBeforeGlobal <= max) {
                        if (!bestTier || min > parseFloat(bestTier.min_threshold)) {
                            bestTier = tier;
                        }
                    }
                });

                if (bestTier) {
                    const discountPct = parseFloat(bestTier.reward_val);
                    const promptKey = String(rule.id);
                    
                    if (promptedDiscounts.billRules[promptKey] !== String(bestTier.id)) {
                        promptedDiscounts.billRules[promptKey] = String(bestTier.id);

                        showDiscountPrompt(
                            "Bill Discount Promotion!",
                            `Your subtotal (Rs ${subTotalBeforeGlobal.toLocaleString('en-IN', {minimumFractionDigits: 2})}) qualifies for a ${discountPct}% global bill discount. Apply this discount to the bill?`,
                            function() {
                                const globSelect = document.getElementById('globalDiscType');
                                const globInput = document.getElementById('globalDiscVal');
                                if (globSelect && globInput) {
                                    globSelect.value = '%';
                                    globInput.value = discountPct;
                                    calcTotals();
                                }
                            },
                            function() {
                                // Rejected
                            }
                        );
                    }
                }
            }
        });
    }

    function calculateDueDateOffset() {
        const termSelect = document.getElementById('paymentTermSelect');
        const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
        const dueDateInput = document.getElementById('dueDate');
        
        if (!termSelect || !invoiceDateInput || !dueDateInput) return;
        
        const selectedOpt = termSelect.options[termSelect.selectedIndex];
        if (!selectedOpt || selectedOpt.value === '') return;
        
        const days = parseInt(selectedOpt.getAttribute('data-days')) || 0;
        const invDate = new Date(invoiceDateInput.value);
        if (isNaN(invDate.getTime())) return;
        
        invDate.setDate(invDate.getDate() + days);
        
        const year = invDate.getFullYear();
        const month = String(invDate.getMonth() + 1).padStart(2, '0');
        const day = String(invDate.getDate()).padStart(2, '0');
        
        dueDateInput.value = `${year}-${month}-${day}`;
    }

    // RUN INITIALIZATION HOOK TO PRE-POPULATE THE FORM IN EDIT MODE
    document.addEventListener("DOMContentLoaded", function() {
        const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
        if (invoiceDateInput) {
            invoiceDateInput.addEventListener('change', calculateDueDateOffset);
        }

        <?php if ($inv && isset($inv->customer_id)): ?>
            const initialCustomerId = "<?= $inv->customer_id ?>";
            const initialCust = customers.find(c => c.id === initialCustomerId);
            if (initialCust) {
                selectCustomer(initialCust);
            }
        <?php endif; ?>
        
        <?php if (!empty($editingItems)): ?>
            <?php foreach ($editingItems as $index => $ei): ?>
                <?php 
                $itemId = isset($ei->item_id) ? $ei->item_id : null;
                $varId = isset($ei->variation_option_id) ? $ei->variation_option_id : null;

                // BUGFIX FALLBACK: If item_id is NULL (typical for older mobile POS checkouts), 
                // dynamically reverse-map the record by matching description strings to restore proper editing context
                if (empty($itemId) && !empty($ei->description)) {
                    $descClean = trim((string)$ei->description);
                    if (strpos($descClean, ' - ') !== false) {
                        $parts = explode(' - ', $descClean);
                        $descClean = trim($parts[0]);
                    }
                    
                    $db->query("SELECT id, item_code FROM items WHERE name = :name LIMIT 1");
                    $db->bind(':name', $descClean);
                    $matchedItem = $db->single();
                    if ($matchedItem) {
                        $itemId = $matchedItem->id;
                        
                        if (strpos((string)$ei->description, ' - ') !== false) {
                            $suffix = trim(str_replace($descClean . ' - ', '', (string)$ei->description));
                            if (strpos($suffix, ': ') !== false) {
                                $suffixParts = explode(': ', $suffix);
                                $valName = trim($suffixParts[1] ?? '');
                                
                                $db->query("SELECT ivo.id FROM item_variation_options ivo
                                            JOIN variation_values vv ON ivo.variation_value_id = vv.id
                                            WHERE ivo.item_id = :item_id AND vv.value_name = :val_name LIMIT 1");
                                $db->bind(':item_id', $itemId);
                                $db->bind(':val_name', $valName);
                                $matchedVar = $db->single();
                                if ($matchedVar) {
                                    $varId = $matchedVar->id;
                                }
                            }
                        }
                    }
                }

                $compositeId = ($itemId ?: '0') . '|' . ($varId ?: '0') . '|0';
                
                $codeStr = 'ITEM';
                if ($itemId) {
                    $db->query("SELECT item_code FROM items WHERE id = :id");
                    $db->bind(':id', $itemId);
                    $itemRow = $db->single();
                    $codeStr = $itemRow ? $itemRow->item_code : 'ITEM';
                }
                ?>
                addItemRow(
                    "<?= $compositeId ?>", 
                    <?= json_encode($codeStr) ?>, 
                    <?= floatval(isset($ei->quantity) ? $ei->quantity : 0) ?>, 
                    <?= json_encode(isset($ei->description) ? $ei->description : '') ?>, 
                    <?= floatval(isset($ei->unit_price) ? $ei->unit_price : 0) ?>, 
                    <?= floatval(isset($ei->discount_value) ? $ei->discount_value : 0) ?>, 
                    <?= json_encode(isset($ei->discount_type) ? $ei->discount_type : 'Rs') ?>
                );
            <?php endforeach; ?>
            renumberInvoiceRows();
        <?php endif; ?>

        // Clean & targeted diagnostic session error tracer
        const serverError = <?= json_encode($data['error'] ?? '') ?>;
        if (serverError) {
            console.error("=== TRANSACTION ERROR ENCOUNTERED ===");
            console.error("Backend Error Message: ", serverError);
            console.error("=====================================");
        }

        // Form submit credit limit check
        const invForm = document.getElementById('invoiceForm');
        if (invForm) {
            invForm.addEventListener('submit', function(e) {
                // If it is a sales order, skip credit limit block as it doesn't impact physical inventory / ledger accounts immediately
                const formType = document.querySelector('input[name="type"]')?.value;
                if (formType === 'sales_order') {
                    // Check if route sales order (which is saved in invoices table with stock_status = 'reserved')
                    const repRouteId = document.querySelector('input[name="rep_route_id"]')?.value;
                    const editingInvId = document.querySelector('input[name="editing_invoice_id"]')?.value;
                    if (!repRouteId && !editingInvId) {
                        return true;
                    }
                }

                const customerId = document.getElementById('customerIdInput').value;
                const cust = customers.find(c => String(c.id) === String(customerId));
                if (cust) {
                    const creditLimit = parseFloat(cust.credit_limit) || 0;
                    if (creditLimit > 0) {
                        const outBal = parseFloat(cust.outstanding) || 0;
                        const subTotalText = document.getElementById('grandTotal').innerText.replace(/,/g, '');
                        const newTotal = parseFloat(subTotalText) || 0;
                        
                        let oldInvoiceTotal = 0;
                        <?php if ($inv): ?>
                            const oldSub = parseFloat("<?= isset($inv->total_amount) ? $inv->total_amount : 0 ?>") || 0;
                            const oldDiscVal = parseFloat("<?= isset($inv->global_discount_val) ? $inv->global_discount_val : 0 ?>") || 0;
                            const oldDiscType = "<?= isset($inv->global_discount_type) ? $inv->global_discount_type : 'Rs' ?>";
                            const oldDisc = (oldDiscType === '%') ? (oldSub * oldDiscVal / 100) : oldDiscVal;
                            oldInvoiceTotal = (oldSub - oldDisc) + (parseFloat("<?= isset($inv->tax_amount) ? $inv->tax_amount : 0 ?>") || 0);
                        <?php endif; ?>
                        
                        const projected = outBal - oldInvoiceTotal + newTotal;
                        if (projected > creditLimit) {
                            e.preventDefault();
                            const exceeded = projected - creditLimit;
                            showCreditLimitBlockDialog(cust.name, creditLimit, projected, exceeded);
                            return false;
                        }
                    }
                }
            });
        }
    });
</script>

<?php include '../app/Views/layouts/resilient_loader.php'; ?>