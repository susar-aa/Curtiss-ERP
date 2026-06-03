<?php
// Smart Failsafe Data Fetcher
// Ensures the view never crashes even if the controller doesn't pass the exact data arrays.
$db = new Database();

// --- SELF-HEALING DATABASE SCHEMA MIGRATIONS ---
// Automatically adds missing columns in the background to prevent DB crash HTML outputs inside the JS block
try {
    $db->query("SHOW COLUMNS FROM items LIKE 'quantity_reserved'");
    if (!$db->single()) {
        $db->query("ALTER TABLE items ADD COLUMN quantity_reserved INT DEFAULT 0 AFTER quantity_on_hand");
        $db->execute();
    }
    
    $db->query("SHOW COLUMNS FROM item_variation_options LIKE 'quantity_reserved'");
    if (!$db->single()) {
        $db->query("ALTER TABLE item_variation_options ADD COLUMN quantity_reserved INT DEFAULT 0 AFTER quantity_on_hand");
        $db->execute();
    }

    $db->query("SHOW COLUMNS FROM invoice_items LIKE 'variation_option_id'");
    if (!$db->single()) {
        $db->query("ALTER TABLE invoice_items ADD COLUMN variation_option_id INT NULL DEFAULT NULL AFTER item_id");
        $db->execute();
    }
} catch (Exception $e) {
    // Silently capture any database schema exceptions to keep layout fluid
}

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
                        $vObj->quantity_on_hand = $v->qty ?? $item->quantity_on_hand ?? 0;
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
?>
<style>
    /* Full-Screen App Layout CSS */
    html, body { overflow: hidden; height: 100%; margin: 0; }
    
    .qb-wrapper {
        background-color: #f2f5f8;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #000;
        height: calc(100vh - 30px); /* Account for Mac top bar */
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 10px;
        box-sizing: border-box;
    }
    
    .qb-container {
        background: #fff;
        width: 100%;
        max-width: 1300px;
        margin: 0 auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 1px solid #b0c4de;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 10px 15px;
        box-sizing: border-box;
        overflow: hidden;
    }

    #invoiceForm {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .qb-title { font-size: 26px; color: #7994b5; font-weight: bold; margin: 0 0 10px 0; letter-spacing: -1px; }

    .qb-input { border: 1px solid #999; padding: 4px; font-size: 12px; font-family: Arial, sans-serif; box-sizing: border-box; }
    .qb-input:focus { border-color: #0066cc; outline: none; background: #f0f8ff;}

    .qb-box { border: 1px solid #999; background: #fff; }
    .qb-box-header { background: #7a7a7a; color: #fff; font-weight: bold; padding: 3px 6px; font-size: 11px; }

    .qb-grid-top { display: flex; justify-content: space-between; margin-bottom: 10px; flex-shrink: 0;}
    .qb-grid-mid { display: flex; border: 1px solid #999; margin-bottom: 10px; background:#fff; flex-shrink: 0;}
    .qb-mid-col { flex: 1; border-right: 1px solid #999; display: flex; flex-direction: column; }
    .qb-mid-col:last-child { border-right: none; }
    .qb-mid-header { background: #7a7a7a; color: #fff; padding: 3px; text-align: center; font-size: 11px; font-weight: bold;}
    .qb-mid-body { padding: 4px; }
    .qb-mid-body input, .qb-mid-body select { width: 100%; border: none; text-align: center; background: transparent; font-size: 12px;}

    .search-wrapper { position: relative; margin-bottom: 10px; flex-shrink: 0;}
    .item-search-bar { width: 100%; padding: 8px; border: 2px solid #7994b5; font-size: 13px; font-weight: bold; border-radius: 4px; box-sizing: border-box; background: #f0f8ff;}
    
    .search-results { position: absolute; top: 100%; left: 0; width: 100%; background: #fff; border: 1px solid #999; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .search-results li { padding: 8px 10px; cursor: pointer; list-style: none; border-bottom: 1px solid #eee; font-size: 13px; display: flex; justify-content: space-between;}
    .search-results li:hover { background: #0066cc; color: #fff; }

    /* The Scrollable Table Container */
    .table-scroll-container {
        flex: 1;
        overflow-y: auto;
        border: 1px solid #999;
        margin-bottom: 10px;
        background: #fff;
    }

    /* CRITICAL FIX: Fixed table-layout ensures strict obedience to percentage widths and prevents horizontal squishing of Rate */
    .qb-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .qb-table thead th { position: sticky; top: 0; background: #7a7a7a; color: #fff; padding: 4px; text-align: left; font-size: 11px; font-weight: bold; border-right: 1px solid #999; border-bottom: 1px solid #555; z-index: 10;}
    .qb-table td { padding: 2px; border-bottom: 1px solid #ccc; border-right: 1px solid #ccc; vertical-align: top;}
    .qb-table input { width: 100%; border: none; background: transparent; padding: 4px; font-size: 12px; font-family: Arial; box-sizing: border-box;}
    .qb-table input:focus { background: #f0f8ff; outline: 1px solid #0066cc; }
    .qb-table .num { text-align: right; }
    
    .discount-cell { display: flex; border: 1px solid transparent; border-radius: 2px; background: #fff; overflow: hidden; }
    .discount-cell:focus-within { border-color: #0066cc; outline: 1px solid #0066cc;}
    .discount-cell input { border: none; width: 60%; }
    .discount-cell select { border: none; border-left: 1px solid #ccc; width: 40%; padding: 2px; background: #f9f9f9; font-size: 12px;}

    .qb-footer { display: flex; justify-content: space-between; align-items: flex-end; flex-shrink: 0;}
    .qb-totals { text-align: right; font-size: 13px;}
    .qb-totals-row { display: flex; justify-content: flex-end; margin-bottom: 5px; gap: 15px;}
    .qb-totals-row strong { width: 120px; text-align: left; }
    .qb-totals-row span { width: 100px; text-align: right; font-family: Tahoma, sans-serif;}

    .qb-action-bar { background: #e8ecf0; padding: 10px; border-top: 1px solid #b0c4de; display: flex; justify-content: space-between; align-items: center; margin-top: 10px; flex-shrink: 0;}
    .qb-btn { background: #f0f0f0; border: 1px solid #999; padding: 5px 15px; cursor: pointer; font-size: 12px; border-radius: 2px; font-weight: bold;}
    .qb-btn:hover { background: #e0e0e0; }
    .qb-btn-primary { background: #e0eaf5; border-color: #7994b5; }
    
    .wa-btn { background: #25D366; color: #fff; border-color: #1da851; }
    .wa-btn:hover { background: #20ba56; }
    .acc-settings { display: none; } 

    /* PREVENT WHEEL SPINNER OVERLAP CLIPPING ON RIGHT-ALIGNED RATE FIELDS */
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
        <div style="padding: 10px; background:#ffebee; color:#c62828; border:1px solid #ef9a9a; margin-bottom:5px; font-weight:bold; flex-shrink:0;"><?= $data['error'] ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_success'])): ?>
        <div style="padding: 10px; background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; margin-bottom:5px; font-weight:bold; flex-shrink:0;">
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div style="padding: 10px; background:#ffebee; color:#c62828; border:1px solid #ef9a9a; margin-bottom:5px; font-weight:bold; flex-shrink:0;">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <?php
    $rep_route_id = $_GET['rep_route_id'] ?? (isset($inv->rep_route_id) ? $inv->rep_route_id : '');
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
        <div style="padding: 10px; background:#e0f7fa; color:#006064; border:1px solid #b2ebf2; margin-bottom:10px; font-weight:bold; border-radius: 4px; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
            <span>📍 Adding Invoice to Rep Route: <strong><?= htmlspecialchars($rep_route_name) ?></strong></span>
        </div>
    <?php endif; ?>

    <div class="qb-container">
        <form action="<?= APP_URL ?>/sales/store" method="POST" id="invoiceForm">
            <input type="hidden" name="type" value="<?= htmlspecialchars((string)($data['type'] ?? 'invoice')) ?>">
            <input type="hidden" name="rep_route_id" value="<?= htmlspecialchars((string)$rep_route_id) ?>">
            <?php if ($inv): ?>
                <input type="hidden" name="editing_invoice_id" value="<?= isset($inv->id) ? $inv->id : '' ?>">
            <?php endif; ?>
            
            <div class="qb-grid-top">
                <div style="width: 300px;">
                    <div class="qb-title"><?= htmlspecialchars($data['title'] ?? ($inv ? 'Edit Invoice' : 'New Invoice')) ?></div>
                    <div class="qb-box" style="position: relative;">
                        <div class="qb-box-header">Bill To</div>
                        <input type="hidden" name="customer_id" id="customerIdInput" required>
                        <input type="text" id="customerSearch" class="qb-input" style="width: 100%; border:none; border-bottom:1px solid #ccc; font-weight:bold; padding: 6px;" placeholder="🔍 Search Customer by Name, Route, Address..." onkeyup="filterCustomerSearch(event)" autocomplete="off" required>
                        <ul id="customerSearchResults" class="search-results" style="width: 100%;"></ul>
                        <textarea id="billToAddress" class="qb-input" style="width: 100%; height: 60px; border:none; resize:none;" readonly placeholder="Customer address will appear here..."></textarea>
                    </div>
                    
                    <!-- Real-time outstanding balance indicator -->
                    <div id="customerOutstanding" style="font-size: 11px; padding: 5px; border-radius: 4px; margin-top: 5px; display: none;"></div>
                </div>

                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <div class="qb-box" style="width: 120px;">
                        <div class="qb-box-header">Date</div>
                        <input type="date" name="invoice_date" class="qb-input" style="width: 100%; border:none; text-align:center;" value="<?= ($inv && isset($inv->invoice_date)) ? $inv->invoice_date : date('Y-m-d') ?>" required>
                    </div>
                    <div class="qb-box" style="width: 120px;">
                        <div class="qb-box-header">Invoice #</div>
                        <input type="text" name="invoice_number" class="qb-input" style="width: 100%; border:none; text-align:center;" value="<?= htmlspecialchars((string)($data['invoice_number'] ?? '')) ?>" <?= $inv ? 'readonly style="background:#eee;"' : '' ?> required>
                    </div>
                </div>
            </div>

            <div class="qb-grid-mid">
                <div class="qb-mid-col">
                    <div class="qb-mid-header">P.O. No.</div>
                    <div class="qb-mid-body"><input type="text" name="po_number" value="<?= ($inv && isset($inv->po_number)) ? htmlspecialchars((string)$inv->po_number) : '' ?>" placeholder="Optional"></div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Terms</div>
                    <div class="qb-mid-body">
                        <select name="payment_term_id" id="paymentTermSelect" onchange="calculateDueDateOffset()" style="width: 100%; border: none; text-align: center; background: transparent; font-size: 12px;">
                            <option value="">Select Term...</option>
                            <?php foreach($data['payment_terms'] as $term): ?>
                                <option value="<?= $term->id ?>" data-days="<?= $term->days_due ?>" <?= ($inv && isset($inv->payment_term_id) && $inv->payment_term_id == $term->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Due Date</div>
                    <div class="qb-mid-body"><input type="date" name="due_date" id="dueDate" value="<?= ($inv && isset($inv->due_date)) ? $inv->due_date : date('Y-m-d') ?>" required></div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Rep</div>
                    <div class="qb-mid-body">
                        <select name="rep_name">
                            <option value="">Select Rep...</option>
                            <?php foreach($reps as $rep): ?>
                                <option value="<?= htmlspecialchars((string)($rep->first_name . ' ' . $rep->last_name)) ?>" <?= ($inv && isset($inv->rep_name) && trim((string)$inv->rep_name) == trim((string)($rep->first_name . ' ' . $rep->last_name))) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)($rep->first_name . ' ' . $rep->last_name)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">MCA</div>
                    <div class="qb-mid-body"><input type="text" name="mca" id="displayMca" value="<?= ($inv && isset($inv->mca)) ? htmlspecialchars((string)$inv->mca) : '' ?>" placeholder="Editable Route Info"></div>
                </div>
                <div class="qb-mid-col">
                    <div class="qb-mid-header">Rep TP#</div>
                    <div class="qb-mid-body"><input type="text" name="rep_tp" id="displayPhone" value="<?= ($inv && isset($inv->rep_tp)) ? htmlspecialchars((string)$inv->rep_tp) : '' ?>" placeholder="Phone #"></div>
                </div>
            </div>

            <div class="search-wrapper">
                <input type="text" id="itemSearch" class="item-search-bar" placeholder="🔍 Search Catalog by Item Code or Name to add to invoice..." onkeyup="filterSearch(event)" autocomplete="off">
                <ul id="searchResults" class="search-results"></ul>
            </div>

            <div class="table-scroll-container">
                <table class="qb-table">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align:center;">#</th>
                            <th style="width: 12%;">Item Code</th>
                            <th style="width: 7%;">Qty</th>
                            <th style="width: 32%;">Description</th>
                            <th style="width: 13%; text-align:right;">Rate (Rs:)</th>
                            <th style="width: 14%; text-align:right;">Discount</th>
                            <th style="width: 14%; text-align:right;">Amount (Rs:)</th>
                            <th style="width: 28px; background:#c62828;"></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceBody">
                        <!-- Javascript will prepend rows here -->
                    </tbody>
                </table>
            </div>

            <div class="qb-footer">
                <div style="width: 350px;">
                    <div style="margin-bottom: 5px;">
                        <label style="font-weight:bold; display:block; margin-bottom:2px; font-size:11px;">Customer Message</label>
                        <select class="qb-input" style="width:100%; margin-bottom:5px;">
                            <option value="">Thank you for your business.</option>
                            <option value="">Please remit payment at your earliest convenience.</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:bold; display:block; margin-bottom:2px; font-size:11px;">Memo</label>
                        <input type="text" name="notes" value="<?= ($inv && isset($inv->notes)) ? htmlspecialchars((string)$inv->notes) : '' ?>" class="qb-input" style="width:100%;">
                    </div>
                </div>

                <div class="qb-totals">
                    <div class="qb-totals-row">
                        <strong>Subtotal LKR</strong>
                        <span id="subTotal">0.00</span>
                    </div>
                    <div class="qb-totals-row" style="align-items: center;">
                        <strong>Bill Discount</strong>
                        <div class="discount-cell" style="width: 100px; border-color:#ccc;">
                            <input type="number" name="global_discount_val" id="globalDiscVal" value="<?= ($inv && isset($inv->global_discount_val)) ? floatval($inv->global_discount_val) : '0' ?>" class="qb-input num" style="width:60px; border:none;" oninput="calcTotals()">
                            <select name="global_discount_type" id="globalDiscType" class="qb-input" style="width:40px; padding:0; border:none; border-left:1px solid #ccc; background:#f9f9f9;" onchange="calcTotals()">
                                <option value="Rs" <?= ($inv && isset($inv->global_discount_type) && $inv->global_discount_type == 'Rs') ? 'selected' : '' ?>>Rs</option>
                                <option value="%" <?= ($inv && isset($inv->global_discount_type) && $inv->global_discount_type == '%') ? 'selected' : '' ?>>%</option>
                            </select>
                        </div>
                    </div>
                    <div class="qb-totals-row" style="font-size: 14px; margin-top: 5px;">
                        <strong>Total LKR</strong>
                        <span id="grandTotal" style="font-weight: bold;">0.00</span>
                    </div>
                    <div class="qb-totals-row" style="font-size: 14px; margin-top: 5px;">
                        <strong style="color:#0066cc;">Balance Due LKR</strong>
                        <span id="balanceDue" style="font-weight: bold; color:#0066cc;">0.00</span>
                    </div>
                </div>
            </div>

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

            <div class="qb-action-bar">
                <div style="display:flex; gap: 10px; align-items:center;">
                    <span style="color:#2e7d32; font-weight:bold;">● System Ready</span>
                </div>
                <div style="display:flex; gap: 10px;">
                    <button type="submit" name="save_action" value="close" class="qb-btn qb-btn-primary">Save & Close</button>
                    <?php if (!$inv): ?>
                        <button type="submit" name="save_action" value="new" class="qb-btn">Save & New</button>
                        <button type="submit" name="save_action" value="print" class="qb-btn">Save & Print 🖨️</button>
                        <button type="submit" name="save_action" value="whatsapp" class="qb-btn wa-btn">Save & WhatsApp 💬</button>
                    <?php endif; ?>
                    <button type="button" class="qb-btn" onclick="window.location.href='<?= APP_URL ?>/sales'">Cancel</button>
                </div>
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
            $baseAvailable = ($item->quantity_on_hand ?? 0) - ($item->quantity_reserved ?? 0);
            ?>
            <?php if($hasVars): ?>
                { id: "<?= $item->id ?>|MIX|1", type: "<?= $item->type ?? 'Inventory' ?>", stock: <?= floatval($baseAvailable) ?>, code: "<?= htmlspecialchars((string)($item->item_code ?? '')) ?>", name: "<?= htmlspecialchars(addslashes((string)($item->name ?? ''))) ?> (MIX)", price: <?= floatval($item->price ?? 0) ?> },
                <?php foreach($item->variations as $var): ?>
                <?php $varAvailable = ($var->quantity_on_hand ?? 0) - ($var->quantity_reserved ?? 0); ?>
                { id: "<?= $item->id ?>|<?= $var->id ?>|0", type: "<?= $item->type ?? 'Inventory' ?>", stock: <?= floatval($varAvailable) ?>, code: "<?= htmlspecialchars(addslashes((string)($var->sku ?? $item->item_code ?? ''))) ?>", name: "<?= htmlspecialchars(addslashes((string)($item->name ?? ''))) ?> - <?= htmlspecialchars(addslashes((string)($var->variation_name ?? ''))) ?>: <?= htmlspecialchars(addslashes((string)($var->value_name ?? ''))) ?>", price: <?= floatval(isset($var->price) && $var->price > 0 ? $var->price : ($item->price ?? 0)) ?> },
                <?php endforeach; ?>
            <?php else: ?>
                { id: "<?= $item->id ?>|0|0", type: "<?= $item->type ?? 'Inventory' ?>", stock: <?= floatval($baseAvailable) ?>, code: "<?= htmlspecialchars((string)($item->item_code ?? '')) ?>", name: "<?= htmlspecialchars(addslashes((string)($item->name ?? ''))) ?>", price: <?= floatval($item->price ?? 0) ?> },
            <?php endif; ?>
        <?php endforeach; ?>
    ];

    const customers = [
        <?php foreach($customers as $c): ?>
        {
            id: "<?= $c->id ?>",
            name: <?= json_encode((string)($c->name ?? '')) ?>,
            phone: <?= json_encode((string)($c->phone ?? '')) ?>,
            address: <?= json_encode((string)($c->address ?? '')) ?>,
            mca: <?= json_encode((string)($c->mca_name ?? $c->territory ?? '')) ?>,
            outstanding: <?= floatval($c->outstanding_balance ?? 0) ?>
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
                        ${cust.phone ? `📞 ${escapeHtml(cust.phone)}<br>` : ''}
                        ${cust.mca ? `📍 Route: <strong>${escapeHtml(cust.mca)}</strong><br>` : ''}
                        ${cust.address ? `<span style="font-style: italic; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;">🏠 ${escapeHtml(cust.address)}</span>` : ''}
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

    function selectCustomer(cust) {
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

        // Handle the real-time outstanding balance
        const outBal = parseFloat(cust.outstanding) || 0;
        const outDiv = document.getElementById('customerOutstanding');
        
        if (cust.id !== "" && (outBal > 0.01 || outBal < -0.01)) {
            outDiv.style.display = 'block';
            if (outBal > 0) {
                outDiv.style.background = '#ffebee';
                outDiv.style.color = '#c62828';
                outDiv.style.border = '1px solid #ef9a9a';
                outDiv.innerHTML = `⚠ Previous Outstanding Balance: Rs <span>${outBal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
            } else {
                outDiv.style.background = '#e8f5e9';
                outDiv.style.color = '#2e7d32';
                outDiv.style.border = '1px solid #a5d6a7';
                outDiv.innerHTML = `✓ Available Credit (Overpaid): Rs <span>${Math.abs(outBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
            }
        } else {
            outDiv.style.display = 'none';
        }
    }

    function filterSearch(e) {
        const val = e.target.value.toLowerCase().trim();
        const resList = document.getElementById('searchResults');
        resList.innerHTML = '';
        if(!val) { resList.style.display = 'none'; return; }

        const filtered = catalog.filter(i => i.name.toLowerCase().includes(val) || i.code.toLowerCase().includes(val)).slice(0, 15);
        if(filtered.length === 0) { resList.style.display = 'none'; return; }

        filtered.forEach(item => {
            const li = document.createElement('li');
            
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
                <div><strong>${item.name}</strong><br><span style="font-size: 11px; color: #888;">SKU: ${item.code || 'N/A'} | ${stockBadge}</span></div>
                <div style="color: #0066cc; font-family: monospace; font-weight: bold; font-size: 14px;">Rs: ${item.price.toFixed(2)}</div>
            `;
            
            li.onclick = () => { 
                if (item.type !== 'Service' && item.stock <= 0) {
                    alert("Cannot add item! It is currently out of stock.");
                    return;
                }
                addItemRow(item); 
                e.target.value = ''; 
                resList.style.display = 'none'; 
                document.getElementById('itemSearch').focus(); 
            };
            resList.appendChild(li);
        });
        resList.style.display = 'block';
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
        document.querySelectorAll('#invoiceBody tr').forEach((tr, i) => {
            const cell = tr.querySelector('.line-row-num');
            if (cell) cell.textContent = i + 1;
        });
    }

    function addItemRow(itemOrId, code = null, qty = 1, desc = null, price = null, discVal = 0, discType = 'Rs') {
        let item = {};
        if (typeof itemOrId === 'object' && itemOrId !== null) {
            // Interactive UI addition from search selection
            item = itemOrId;
            qty = 1;
            desc = item.name;
            price = item.price;
            discVal = 0;
            discType = 'Rs';
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
        
        let stockHtml = item.type === 'Service' ? '' : `<br><span style="font-size: 10px; color: #888;">Stock: ${item.stock}</span>`;
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
            <td class="line-row-num" style="text-align:center; color:#888; font-weight:bold; vertical-align:middle;"></td>
            <td>
                <input type="text" value="${item.code || 'ITEM'}" readonly style="color:#666; font-size:12px; width: 100%; border:none; background:transparent;">
                <input type="hidden" name="item_selection[]" value="${item.id}">
            </td>
            <td style="text-align: center;">
                <input type="number" class="num" name="qty[]" value="${qty}" min="0.01" step="0.01" ${maxAttr} oninput="validateQty(this, ${item.type === 'Service' ? 'null' : item.stock}); calcTotals()" required style="width: 60px; margin-bottom: 2px;">
                ${stockHtml}
            </td>
            <td><input type="text" name="desc[]" value="${desc}" required style="width: 100%; border:none; background:transparent;"></td>
            <td><input type="number" class="num" name="price[]" value="${parseFloat(price).toFixed(2)}" step="0.01" min="0" oninput="calcTotals()" required style="width: 100%; text-align: right; box-sizing: border-box; padding-right: 4px;"></td>
            <td>
                <div class="discount-cell">
                    <input type="number" class="num" name="item_discount_val[]" value="${parseFloat(discVal).toFixed(2)}" step="0.01" oninput="calcTotals()">
                    <select name="item_discount_type[]" onchange="calcTotals()">
                        <option value="Rs" ${discType === 'Rs' ? 'selected' : ''}>Rs</option>
                        <option value="%" ${discType === '%' ? 'selected' : ''}>%</option>
                    </select>
                </div>
            </td>
            <td><input type="text" class="num line-total" value="0.00" readonly style="font-weight:bold; background: transparent; border: none; width: 100%; text-align: right; padding-right: 4px;"></td>
            <td style="text-align:center;"><button type="button" tabindex="-1" style="background:transparent; color:#c62828; border:none; cursor:pointer; font-weight:bold; font-size: 16px; padding: 4px;" onclick="this.closest('tr').remove(); renumberInvoiceRows(); calcTotals();">&times;</button></td>
        `;
        tbody.insertAdjacentElement('afterbegin', tr);
        renumberInvoiceRows();
        
        // Trigger validation immediately upon adding in case stock is 0
        const qtyInput = tr.querySelector('input[name="qty[]"]');
        validateQty(qtyInput, item.type === 'Service' ? null : item.stock);
        
        calcTotals();
        
        document.querySelector('.table-scroll-container').scrollTop = 0;
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
    });
</script>

<?php include '../app/Views/layouts/resilient_loader.php'; ?>