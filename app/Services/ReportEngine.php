<?php
/**
 * Curtiss ERP - Centralized Reporting Engine
 * Implements a dynamic, metadata-driven report processor.
 */
class ReportEngine {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public static function getCategories() {
        return [
            'inventory' => 'Inventory Reports',
            'sales' => 'Sales Reports',
            'procurement' => 'Procurement Reports',
            'customer' => 'Customer Reports',
            'supplier' => 'Supplier Reports',
            'finance' => 'Finance & Accounts Reports',
            'collection' => 'Collection & Payment Reports',
            'route' => 'Route & Distribution Reports',
            'management' => 'Management Reports'
        ];
    }

    public static function getReportsRegistry() {
        return [
            // 1. Inventory Reports
            'stock_summary' => [
                'title' => 'Stock Summary',
                'category' => 'inventory',
                'filters' => ['product', 'category', 'warehouse', 'brand'],
                'columns' => [
                    'item_code' => ['label' => 'Item Code', 'type' => 'text', 'sortable' => true],
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'sortable' => true, 'drilldown' => 'product'],
                    'category_name' => ['label' => 'Category', 'type' => 'text'],
                    'qty_on_hand' => ['label' => 'Stock Level', 'type' => 'number', 'align' => 'right', 'sortable' => true],
                    'cost_price' => ['label' => 'Cost Price', 'type' => 'currency', 'align' => 'right'],
                    'price' => ['label' => 'Retail Price', 'type' => 'currency', 'align' => 'right'],
                    'val_cost' => ['label' => 'Value (Cost)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'val_retail' => ['label' => 'Value (Retail)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.id, i.item_code, i.name, ic.name as category_name, i.brand, i.warehouse_id, i.category_id,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                                 COALESCE(i.cost_price, 0) as cost_price,
                                 COALESCE(i.price, 0) as price,
                                 (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.cost_price, 0)) as val_cost,
                                 (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.price, 0)) as val_retail
                          FROM items i
                          LEFT JOIN item_categories ic ON i.category_id = ic.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'stock_balance' => [
                'title' => 'Stock Balance Report',
                'category' => 'inventory',
                'filters' => ['product', 'warehouse', 'category', 'brand'],
                'columns' => [
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'sortable' => true, 'drilldown' => 'product'],
                    'warehouse_name' => ['label' => 'Warehouse', 'type' => 'text'],
                    'qty_on_hand' => ['label' => 'In Stock', 'type' => 'number', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.id as item_id, i.name, COALESCE(w.name, 'Main Warehouse') as warehouse_name, i.warehouse_id, i.category_id, i.brand,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand
                          FROM items i
                          LEFT JOIN warehouses w ON i.warehouse_id = w.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'stock_movement' => [
                'title' => 'Stock Movement Report',
                'category' => 'inventory',
                'filters' => ['date_range', 'product', 'warehouse', 'category'],
                'date_column' => 'sl.transaction_date',
                'columns' => [
                    'created_at' => ['label' => 'Date', 'type' => 'date', 'sortable' => true],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'activity_type' => ['label' => 'Type', 'type' => 'badge'],
                    'ref_doc' => ['label' => 'Document Ref', 'type' => 'text'],
                    'qty_change' => ['label' => 'Qty Change', 'type' => 'number', 'align' => 'right'],
                    'new_balance' => ['label' => 'New Balance', 'type' => 'number', 'align' => 'right']
                ],
                'sql' => "SELECT sl.transaction_date as created_at, i.name as product_name, sl.transaction_type as activity_type, 
                                 sl.reference_number as ref_doc, (sl.quantity_in - sl.quantity_out) as qty_change, sl.running_balance as new_balance,
                                 sl.item_id, sl.warehouse_id, i.category_id
                          FROM stock_ledger sl
                          JOIN items i ON sl.item_id = i.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'stock_ledger' => [
                'title' => 'Stock Ledger',
                'category' => 'inventory',
                'filters' => ['date_range', 'product', 'warehouse', 'category'],
                'date_column' => 'sl.transaction_date',
                'columns' => [
                    'created_at' => ['label' => 'Date & Time', 'type' => 'date', 'sortable' => true],
                    'item_code' => ['label' => 'SKU', 'type' => 'text'],
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'activity_type' => ['label' => 'Reference', 'type' => 'text'],
                    'qty_change' => ['label' => 'Qty Delta', 'type' => 'number', 'align' => 'right'],
                    'new_balance' => ['label' => 'Balance', 'type' => 'number', 'align' => 'right']
                ],
                'sql' => "SELECT sl.transaction_date as created_at, i.item_code, i.name, sl.reference_number as activity_type,
                                 (sl.quantity_in - sl.quantity_out) as qty_change, sl.running_balance as new_balance,
                                 sl.item_id, sl.warehouse_id, i.category_id
                          FROM stock_ledger sl
                          JOIN items i ON sl.item_id = i.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'stock_aging' => [
                'title' => 'Stock Aging Report',
                'category' => 'inventory',
                'filters' => ['product', 'warehouse', 'category', 'brand'],
                'columns' => [
                    'item_code' => ['label' => 'Code', 'type' => 'text'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty_on_hand' => ['label' => 'Qty On Hand', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'cost_price' => ['label' => 'Unit Cost', 'type' => 'currency', 'align' => 'right'],
                    'total_cost' => ['label' => 'Valuation', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'age_days' => ['label' => 'Holding Age (Days)', 'type' => 'number', 'align' => 'right']
                ],
                'sql' => "SELECT i.id as item_id, i.item_code, i.name as product_name, i.warehouse_id, i.category_id, i.brand,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                                 COALESCE(i.cost_price, 0) as cost_price,
                                 (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.cost_price, 0)) as total_cost,
                                 COALESCE(DATEDIFF(NOW(), (SELECT MAX(transaction_date) FROM stock_ledger WHERE item_id = i.id AND quantity_in > 0)), DATEDIFF(NOW(), i.created_at)) as age_days
                          FROM items i
                          WHERE COALESCE(i.quantity_on_hand, 0) > 0 /*WHERE_CLAUSE*/"
            ],
            'inventory_valuation' => [
                'title' => 'Inventory Valuation',
                'category' => 'inventory',
                'filters' => ['product', 'category', 'warehouse', 'brand'],
                'columns' => [
                    'item_code' => ['label' => 'SKU', 'type' => 'text'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty_on_hand' => ['label' => 'Physical Stock', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'avg_cost' => ['label' => 'Unit Valuation', 'type' => 'currency', 'align' => 'right'],
                    'total_valuation' => ['label' => 'Total Valuation', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.item_code, i.name as product_name, i.id as item_id, i.warehouse_id, i.category_id, i.brand,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                                 COALESCE(i.cost_price, 0) as avg_cost,
                                 (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.cost_price, 0)) as total_valuation
                          FROM items i
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'reorder_level' => [
                'title' => 'Reorder Level Report',
                'category' => 'inventory',
                'filters' => ['product', 'category', 'warehouse'],
                'columns' => [
                    'item_code' => ['label' => 'SKU', 'type' => 'text'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty_on_hand' => ['label' => 'Current Stock', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'reorder_point' => ['label' => 'Min Threshold', 'type' => 'number', 'align' => 'right'],
                    'deficit' => ['label' => 'Reorder Deficit', 'type' => 'number', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.item_code, i.name as product_name, i.id as item_id, i.category_id, i.warehouse_id,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                                 COALESCE(i.minimum_stock_level, i.alert_qty, 10) as reorder_point,
                                 (COALESCE(i.minimum_stock_level, i.alert_qty, 10) - COALESCE(i.quantity_on_hand, 0)) as deficit
                          FROM items i
                          WHERE COALESCE(i.quantity_on_hand, 0) <= COALESCE(i.minimum_stock_level, i.alert_qty, 10) /*WHERE_CLAUSE*/"
            ],
            'negative_stock' => [
                'title' => 'Negative Stock Report',
                'category' => 'inventory',
                'filters' => ['product', 'warehouse', 'category'],
                'columns' => [
                    'item_code' => ['label' => 'SKU', 'type' => 'text'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty_on_hand' => ['label' => 'Negative Stock Qty', 'type' => 'number', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.item_code, i.name as product_name, i.id as item_id, i.warehouse_id, i.category_id,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand
                          FROM items i
                          WHERE COALESCE(i.quantity_on_hand, 0) < 0 /*WHERE_CLAUSE*/"
            ],
            'damaged_stock' => [
                'title' => 'Damaged Stock / Adjustments',
                'category' => 'inventory',
                'filters' => ['date_range', 'product', 'warehouse', 'category'],
                'date_column' => 'sl.transaction_date',
                'columns' => [
                    'note_date' => ['label' => 'Date', 'type' => 'date'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty' => ['label' => 'Qty Damaged', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'cost' => ['label' => 'Cost Valuation', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'reason' => ['label' => 'Reason', 'type' => 'text']
                ],
                'sql' => "SELECT sl.transaction_date as note_date, i.name as product_name, 
                                 sl.quantity_out as qty,
                                 (sl.quantity_out * COALESCE(sl.unit_cost, i.cost_price, 0)) as cost,
                                 COALESCE(sl.remarks, 'Stock adjustment / damage write-off') as reason,
                                 sl.item_id, sl.warehouse_id, i.category_id
                          FROM stock_ledger sl
                          JOIN items i ON sl.item_id = i.id
                          WHERE sl.transaction_type IN ('Damage', 'Adjustment') AND sl.quantity_out > 0 /*WHERE_CLAUSE*/"
            ],
            'batch_lot' => [
                'title' => 'Batch / Lot Tracking Report',
                'category' => 'inventory',
                'filters' => ['product', 'status'],
                'columns' => [
                    'batch_code' => ['label' => 'Batch Code', 'type' => 'text'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'expiry_date' => ['label' => 'Expiry Date', 'type' => 'date'],
                    'qty' => ['label' => 'Qty On Hand', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'status' => ['label' => 'Status', 'type' => 'badge']
                ],
                'sql' => "SELECT CONCAT('BATCH-', sb.id) as batch_code, i.name as product_name, 
                                 DATE(DATE_ADD(sb.created_at, INTERVAL 1 YEAR)) as expiry_date, 
                                 sb.quantity_remaining as qty, 
                                 CASE WHEN sb.quantity_remaining <= 0 THEN 'Depleted' ELSE 'Active' END as status,
                                 sb.item_id
                          FROM stock_batches sb
                          JOIN items i ON sb.item_id = i.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'product_movement_analysis' => [
                'title' => 'Product Movement Analysis',
                'category' => 'inventory',
                'filters' => ['product', 'category', 'warehouse'],
                'columns' => [
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'inwards' => ['label' => 'Total Inwards', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'outwards' => ['label' => 'Total Outwards', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'net_change' => ['label' => 'Net Change', 'type' => 'number', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.name, i.id as item_id, i.category_id, i.warehouse_id,
                                 SUM(COALESCE(sl.quantity_in, 0)) as inwards, 
                                 SUM(COALESCE(sl.quantity_out, 0)) as outwards, 
                                 SUM(COALESCE(sl.quantity_in, 0) - COALESCE(sl.quantity_out, 0)) as net_change
                          FROM items i
                          LEFT JOIN stock_ledger sl ON i.id = sl.item_id
                          WHERE 1=1 /*WHERE_CLAUSE*/
                          GROUP BY i.id, i.name, i.category_id, i.warehouse_id"
            ],
            'fast_moving' => [
                'title' => 'Fast Moving Items',
                'category' => 'inventory',
                'filters' => ['date_range', 'category', 'brand'],
                'date_column' => 'i.invoice_date',
                'columns' => [
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'sales_count' => ['label' => 'Orders Count', 'type' => 'number', 'align' => 'right'],
                    'sales_qty' => ['label' => 'Qty Sold', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'revenue' => ['label' => 'Total Revenue', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT ii.description as name, it.category_id, it.brand,
                                 COUNT(DISTINCT i.id) as sales_count, 
                                 SUM(ii.quantity) as sales_qty, 
                                 SUM(ii.total) as revenue
                          FROM invoice_items ii
                          JOIN invoices i ON ii.invoice_id = i.id
                          LEFT JOIN items it ON ii.item_id = it.id
                          WHERE i.status != 'Voided' /*WHERE_CLAUSE*/
                          GROUP BY ii.item_id, ii.description, it.category_id, it.brand
                          ORDER BY sales_qty DESC"
            ],
            'slow_moving' => [
                'title' => 'Slow Moving Items',
                'category' => 'inventory',
                'filters' => ['category', 'brand', 'warehouse'],
                'columns' => [
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty_on_hand' => ['label' => 'In Stock', 'type' => 'number', 'align' => 'right'],
                    'days_since_sold' => ['label' => 'Days Since Last Sale', 'type' => 'number', 'align' => 'right']
                ],
                'sql' => "SELECT i.name, i.category_id, i.brand, i.warehouse_id,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                                 COALESCE(DATEDIFF(NOW(), (SELECT MAX(inv.invoice_date) FROM invoice_items ii JOIN invoices inv ON ii.invoice_id = inv.id WHERE ii.item_id = i.id AND inv.status != 'Voided')), 999) as days_since_sold
                          FROM items i
                          WHERE COALESCE(i.quantity_on_hand, 0) > 0 /*WHERE_CLAUSE*/
                          ORDER BY days_since_sold DESC"
            ],
            'dead_stock' => [
                'title' => 'Dead Stock Report',
                'category' => 'inventory',
                'filters' => ['category', 'brand', 'warehouse'],
                'columns' => [
                    'name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty_on_hand' => ['label' => 'Stock Level', 'type' => 'number', 'align' => 'right'],
                    'cost_value' => ['label' => 'Capital Blocked', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'days_dormant' => ['label' => 'Days Dormant', 'type' => 'number', 'align' => 'right']
                ],
                'sql' => "SELECT i.name, i.category_id, i.brand, i.warehouse_id,
                                 COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                                 (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.cost_price, 0)) as cost_value,
                                 COALESCE(DATEDIFF(NOW(), (SELECT MAX(inv.invoice_date) FROM invoice_items ii JOIN invoices inv ON ii.invoice_id = inv.id WHERE ii.item_id = i.id AND inv.status != 'Voided')), DATEDIFF(NOW(), i.created_at)) as days_dormant
                          FROM items i
                          WHERE COALESCE(i.quantity_on_hand, 0) > 0
                            AND NOT EXISTS (
                                SELECT 1 FROM invoice_items ii 
                                JOIN invoices inv ON ii.invoice_id = inv.id 
                                WHERE ii.item_id = i.id AND inv.invoice_date >= DATE_SUB(NOW(), INTERVAL 90 DAY) AND inv.status != 'Voided'
                            ) /*WHERE_CLAUSE*/
                          ORDER BY days_dormant DESC"
            ],
            'warehouse_stock' => [
                'title' => 'Warehouse Stock Report',
                'category' => 'inventory',
                'filters' => ['warehouse', 'category', 'brand'],
                'columns' => [
                    'warehouse_name' => ['label' => 'Warehouse', 'type' => 'text'],
                    'product_name' => ['label' => 'Product Name', 'type' => 'text', 'drilldown' => 'product'],
                    'qty' => ['label' => 'Qty On Hand', 'type' => 'number', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT COALESCE(w.name, 'Main Warehouse') as warehouse_name, i.name as product_name, COALESCE(i.quantity_on_hand, 0) as qty, i.warehouse_id, i.category_id, i.brand
                          FROM items i
                          LEFT JOIN warehouses w ON i.warehouse_id = w.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'stock_transfer' => [
                'title' => 'Stock Transfer Report',
                'category' => 'inventory',
                'filters' => ['date_range', 'warehouse'],
                'date_column' => 'wt.transfer_date',
                'columns' => [
                    'transfer_date' => ['label' => 'Date', 'type' => 'date'],
                    'transfer_no' => ['label' => 'Transfer Ref', 'type' => 'text'],
                    'from_wh' => ['label' => 'From Warehouse', 'type' => 'text'],
                    'to_wh' => ['label' => 'To Warehouse', 'type' => 'text'],
                    'product_name' => ['label' => 'Product', 'type' => 'text', 'drilldown' => 'product'],
                    'qty' => ['label' => 'Qty', 'type' => 'number', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT wt.transfer_date, wt.transfer_number as transfer_no, f.name as from_wh, t.name as to_wh, i.name as product_name, wt.qty,
                                 wt.from_warehouse_id, wt.to_warehouse_id, wt.item_id
                          FROM warehouse_transfers wt
                          JOIN items i ON wt.item_id = i.id
                          LEFT JOIN warehouses f ON wt.from_warehouse_id = f.id
                          LEFT JOIN warehouses t ON wt.to_warehouse_id = t.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],

            // 2. Sales Reports
            'sales_report' => [
                'title' => 'Sales Report',
                'category' => 'sales',
                'filters' => ['date_range', 'customer', 'rep', 'route', 'payment_method', 'status', 'vehicle', 'driver', 'partner', 'territory'],
                'date_column' => 'i.invoice_date',
                'columns' => [
                    'invoice_date' => ['label' => 'Date', 'type' => 'date', 'sortable' => true],
                    'invoice_number' => ['label' => 'Invoice Ref', 'type' => 'text', 'drilldown' => 'invoice', 'sortable' => true],
                    'customer_name' => ['label' => 'Customer', 'type' => 'text', 'drilldown' => 'customer', 'sortable' => true],
                    'subtotal' => ['label' => 'Subtotal', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'tax_amount' => ['label' => 'Tax', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'grand_total' => ['label' => 'Grand Total', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'status' => ['label' => 'Status', 'type' => 'badge']
                ],
                'sql' => "SELECT i.invoice_date, i.invoice_number, c.name as customer_name,
                                 i.total_amount as subtotal, i.tax_amount, i.id,
                                 (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as grand_total,
                                 i.status, i.customer_id, i.created_by, i.rep_route_id, c.territory
                          FROM invoices i
                          JOIN customers c ON i.customer_id = c.id
                          WHERE i.status != 'Voided' /*WHERE_CLAUSE*/"
            ],
            'sales_summary' => [
                'title' => 'Sales Summary',
                'category' => 'sales',
                'filters' => ['date_range', 'rep', 'route'],
                'date_column' => 'i.invoice_date',
                'columns' => [
                    'period_date' => ['label' => 'Date', 'type' => 'date', 'sortable' => true],
                    'invoice_count' => ['label' => 'Invoice Count', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'daily_total' => ['label' => 'Gross Sales', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT i.invoice_date as period_date, COUNT(*) as invoice_count,
                                 SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as daily_total,
                                 i.created_by, i.rep_route_id
                          FROM invoices i
                          WHERE i.status != 'Voided' /*WHERE_CLAUSE*/
                          GROUP BY i.invoice_date, i.created_by, i.rep_route_id"
            ],
            'sales_by_customer' => [
                'title' => 'Sales by Customer',
                'category' => 'sales',
                'filters' => ['date_range', 'customer', 'rep', 'route', 'territory', 'group'],
                'date_column' => 'i.invoice_date',
                'columns' => [
                    'customer_name' => ['label' => 'Customer Name', 'type' => 'text', 'drilldown' => 'customer', 'sortable' => true],
                    'phone' => ['label' => 'Phone', 'type' => 'text'],
                    'invoice_count' => ['label' => 'Invoices Count', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'total_sales' => ['label' => 'Total Sales', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'paid_amount' => ['label' => 'Receipts/Paid', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'outstanding' => ['label' => 'Net Outstanding', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT c.id, c.name as customer_name, c.phone, COUNT(i.id) as invoice_count,
                                 SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_sales,
                                 COALESCE((SELECT SUM(amount) FROM customer_payments WHERE customer_id = c.id AND status = 'Active'), 0) as paid_amount,
                                 (COALESCE(c.opening_balance, 0) + 
                                  COALESCE((SELECT SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) FROM invoices WHERE customer_id = c.id AND status != 'Voided'), 0) - 
                                  COALESCE((SELECT SUM(amount) FROM customer_payments WHERE customer_id = c.id AND status = 'Active'), 0) - 
                                  COALESCE((SELECT SUM(total_amount) FROM credit_notes WHERE customer_id = c.id), 0)) as outstanding,
                                 i.customer_id, i.created_by, i.rep_route_id, c.territory, c.customer_type
                          FROM invoices i
                          JOIN customers c ON i.customer_id = c.id
                          WHERE i.status != 'Voided' /*WHERE_CLAUSE*/
                          GROUP BY c.id, c.name, c.phone, c.territory, c.customer_type"
            ],
            'sales_by_item' => [
                'title' => 'Sales by Item',
                'category' => 'sales',
                'filters' => ['date_range', 'product', 'category', 'brand'],
                'date_column' => 'i.invoice_date',
                'columns' => [
                    'item_name' => ['label' => 'Item Name', 'type' => 'text', 'drilldown' => 'product', 'sortable' => true],
                    'total_qty' => ['label' => 'Quantity Sold', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'total_revenue' => ['label' => 'Revenue', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'total_cost' => ['label' => 'Total Cost', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'gross_profit' => ['label' => 'Gross Profit', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT ii.description as item_name, SUM(ii.quantity) as total_qty,
                                 SUM(
                                     COALESCE(
                                         (ii.total / NULLIF((SELECT SUM(total) FROM invoice_items WHERE invoice_id = i.id), 0)) 
                                         * (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)), 
                                         0
                                     )
                                 ) as total_revenue,
                                 SUM(ii.quantity * COALESCE(ii.cost_at_sale, 0)) as total_cost,
                                 SUM(
                                     COALESCE(
                                         (ii.total / NULLIF((SELECT SUM(total) FROM invoice_items WHERE invoice_id = i.id), 0)) 
                                         * (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0)), 
                                         0
                                     )
                                     - (ii.quantity * COALESCE(ii.cost_at_sale, 0))
                                 ) as gross_profit,
                                 ii.item_id, it.category_id, it.brand
                           FROM invoice_items ii
                           JOIN invoices i ON ii.invoice_id = i.id
                           JOIN customers c ON i.customer_id = c.id
                           LEFT JOIN items it ON ii.item_id = it.id
                           WHERE i.status != 'Voided' /*WHERE_CLAUSE*/
                           GROUP BY ii.description, ii.item_id, it.category_id, it.brand"
            ],

            // 3. Procurement Reports
            'purchase_order_report' => [
                'title' => 'Purchase Order Report',
                'category' => 'procurement',
                'filters' => ['date_range', 'supplier', 'status'],
                'date_column' => 'p.po_date',
                'columns' => [
                    'po_date' => ['label' => 'Date', 'type' => 'date'],
                    'po_number' => ['label' => 'PO Number', 'type' => 'text', 'drilldown' => 'po'],
                    'vendor_name' => ['label' => 'Supplier', 'type' => 'text', 'drilldown' => 'supplier'],
                    'total_amount' => ['label' => 'Amount', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'status' => ['label' => 'Status', 'type' => 'badge']
                ],
                'sql' => "SELECT p.po_date, p.po_number, v.name as vendor_name, p.total_amount, p.status, p.vendor_id, p.id
                          FROM purchase_orders p
                          JOIN vendors v ON p.vendor_id = v.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'grn_report' => [
                'title' => 'GRN Report',
                'category' => 'procurement',
                'filters' => ['date_range', 'supplier', 'status'],
                'date_column' => 'g.grn_date',
                'columns' => [
                    'grn_date' => ['label' => 'Date', 'type' => 'date'],
                    'grn_number' => ['label' => 'GRN Ref', 'type' => 'text', 'drilldown' => 'grn'],
                    'vendor_name' => ['label' => 'Supplier', 'type' => 'text', 'drilldown' => 'supplier'],
                    'total_value' => ['label' => 'Received Value', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'status' => ['label' => 'Status', 'type' => 'badge']
                ],
                'sql' => "SELECT g.grn_date, g.grn_number, v.name as vendor_name, g.status, g.vendor_id, g.id,
                                 COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items WHERE grn_id = g.id), 0) as total_value
                          FROM goods_receipt_notes g
                          JOIN vendors v ON g.vendor_id = v.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],

            // 4. Customer Reports
            'customer_outstanding' => [
                'title' => 'Customer Outstanding Report',
                'category' => 'customer',
                'filters' => ['customer', 'rep', 'route', 'territory', 'group'],
                'columns' => [
                    'customer_code' => ['label' => 'Customer Code', 'type' => 'text', 'sortable' => true],
                    'customer_name' => ['label' => 'Customer Name', 'type' => 'text', 'drilldown' => 'customer', 'sortable' => true],
                    'phone' => ['label' => 'Phone', 'type' => 'text'],
                    'territory' => ['label' => 'Territory', 'type' => 'text', 'sortable' => true],
                    'credit_limit' => ['label' => 'Credit Limit', 'type' => 'currency', 'align' => 'right'],
                    'opening_balance' => ['label' => 'Opening Bal', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'total_billed' => ['label' => 'Total Invoiced', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'total_paid' => ['label' => 'Total Receipts', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'total_credited' => ['label' => 'Credit Notes', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'outstanding_balance' => ['label' => 'Net Balance', 'type' => 'currency', 'align' => 'right', 'total' => 'sum', 'sortable' => true]
                ],
                'sql' => "SELECT c.id, CONCAT('CUS-', c.id) as customer_code, c.name as customer_name, c.phone, c.territory,
                                 COALESCE(c.credit_limit, 0) as credit_limit,
                                 COALESCE(c.opening_balance, 0) as opening_balance,
                                 COALESCE(inv.total_billed, 0) as total_billed,
                                 COALESCE(pmt.total_paid, 0) as total_paid,
                                 COALESCE(cn.total_credited, 0) as total_credited,
                                 (COALESCE(c.opening_balance, 0) + COALESCE(inv.total_billed, 0) - COALESCE(pmt.total_paid, 0) - COALESCE(cn.total_credited, 0)) as outstanding_balance,
                                 c.id as customer_id, c.customer_type
                          FROM customers c
                          LEFT JOIN (
                              SELECT customer_id, 
                                     SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_billed
                              FROM invoices 
                              WHERE status != 'Voided'
                              GROUP BY customer_id
                          ) inv ON c.id = inv.customer_id
                          LEFT JOIN (
                              SELECT customer_id, SUM(amount) as total_paid
                              FROM customer_payments 
                              WHERE status = 'Active'
                              GROUP BY customer_id
                          ) pmt ON c.id = pmt.customer_id
                          LEFT JOIN (
                              SELECT customer_id, SUM(total_amount) as total_credited
                              FROM credit_notes
                              GROUP BY customer_id
                          ) cn ON c.id = cn.customer_id
                          WHERE 1=1 /*WHERE_CLAUSE*/
                          HAVING (outstanding_balance > 0.01 OR outstanding_balance < -0.01)"
            ],
            'customer_aging' => [
                'title' => 'Customer Aging Report',
                'category' => 'customer',
                'filters' => ['customer', 'rep', 'route', 'territory', 'group'],
                'columns' => [
                    'customer_name' => ['label' => 'Customer Name', 'type' => 'text', 'drilldown' => 'customer', 'sortable' => true],
                    'current' => ['label' => 'Current (0-30d)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'thirty' => ['label' => '31 - 60 Days', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'sixty' => ['label' => '61 - 90 Days', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'ninety' => ['label' => '90+ Days', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'total' => ['label' => 'Total Outstanding', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT sub.id, sub.customer_name, sub.customer_id, sub.territory, sub.customer_type,
                                 GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)) as `current`,
                                 GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)))) as `thirty`,
                                 GREATEST(0, LEAST(sub.S_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)) - GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)))))) as `sixty`,
                                 (sub.TOTAL_BAL 
                                  - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL))
                                  - GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL))))
                                  - GREATEST(0, LEAST(sub.S_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL)) - GREATEST(0, LEAST(sub.T_inv, sub.TOTAL_BAL - GREATEST(0, LEAST(sub.C_inv, sub.TOTAL_BAL))))))
                                 ) as `ninety`,
                                 sub.TOTAL_BAL as total
                          FROM (
                              SELECT c.id, c.name as customer_name, c.id as customer_id, c.territory, c.customer_type,
                                     COALESCE(aging.current_bal, 0) as C_inv,
                                     COALESCE(aging.thirty_bal, 0) as T_inv,
                                     COALESCE(aging.sixty_bal, 0) as S_inv,
                                     (COALESCE(aging.ninety_bal, 0) + COALESCE(c.opening_balance, 0)) as N_inv,
                                     (COALESCE(c.opening_balance, 0) + COALESCE(inv.total_billed, 0) - COALESCE(pmt.total_paid, 0) - COALESCE(cn.total_credited, 0)) as TOTAL_BAL
                              FROM customers c
                              LEFT JOIN (
                                  SELECT customer_id, 
                                         SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_billed
                                  FROM invoices 
                                  WHERE status != 'Voided'
                                  GROUP BY customer_id
                              ) inv ON c.id = inv.customer_id
                              LEFT JOIN (
                                  SELECT customer_id, SUM(amount) as total_paid
                                  FROM customer_payments 
                                  WHERE status = 'Active'
                                  GROUP BY customer_id
                              ) pmt ON c.id = pmt.customer_id
                              LEFT JOIN (
                                  SELECT customer_id, SUM(total_amount) as total_credited
                                  FROM credit_notes
                                  GROUP BY customer_id
                              ) cn ON c.id = cn.customer_id
                              LEFT JOIN (
                                  SELECT customer_id,
                                         SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) <= 30 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as current_bal,
                                         SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) > 30 AND DATEDIFF(NOW(), invoice_date) <= 60 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as thirty_bal,
                                         SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) > 60 AND DATEDIFF(NOW(), invoice_date) <= 90 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as sixty_bal,
                                         SUM(CASE WHEN DATEDIFF(NOW(), invoice_date) > 90 THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END) as ninety_bal
                                  FROM invoices
                                  WHERE status != 'Paid' AND status != 'Voided'
                                  GROUP BY customer_id
                              ) aging ON c.id = aging.customer_id
                              WHERE 1=1 /*WHERE_CLAUSE*/
                          ) sub
                          WHERE sub.TOTAL_BAL > 0.01 OR sub.TOTAL_BAL < -0.01"
            ],
            'customer_statement' => [
                'title' => 'Customer Statement',
                'category' => 'customer',
                'filters' => ['date_range', 'customer'],
                'date_column' => 'i.date',
                'columns' => [
                    'date' => ['label' => 'Date', 'type' => 'date'],
                    'type' => ['label' => 'Type', 'type' => 'text'],
                    'ref' => ['label' => 'Reference', 'type' => 'text'],
                    'debit' => ['label' => 'Debit (Sales)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'credit' => ['label' => 'Credit (Payments)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'balance' => ['label' => 'Running Balance', 'type' => 'currency', 'align' => 'right']
                ],
                'sql' => "SELECT i.id, i.date, i.type, i.ref, i.debit, i.credit,
                                 SUM(i.debit - i.credit) OVER (ORDER BY i.date, i.ref) as balance,
                                 i.customer_id
                          FROM (
                              SELECT i2.id, i2.invoice_date as date, 'Invoice' as type, i2.invoice_number as ref, 
                                     (i2.total_amount - COALESCE(CASE WHEN i2.global_discount_type = '%' THEN (i2.total_amount * i2.global_discount_val / 100) ELSE i2.global_discount_val END, 0) + COALESCE(i2.tax_amount, 0)) as debit,
                                     0 as credit,
                                     i2.customer_id
                              FROM invoices i2
                              WHERE i2.status != 'Voided'
                              UNION ALL
                              SELECT cp.id, cp.payment_date as date, 'Payment' as type, 
                                     CONCAT('Pay: ', cp.payment_method, 
                                            IF(cp.payment_method = 'Cheque', 
                                               COALESCE((SELECT CONCAT(' #', c.cheque_number) FROM cheques c WHERE c.customer_id = cp.customer_id AND c.amount = cp.amount AND ABS(TIMESTAMPDIFF(SECOND, c.created_at, cp.created_at)) < 60 ORDER BY c.id DESC LIMIT 1), ''),
                                               IF(cp.reference != '', CONCAT(' (', cp.reference, ')'), '')
                                            )
                                     ) as ref,
                                     0 as debit,
                                     cp.amount as credit,
                                     cp.customer_id
                              FROM customer_payments cp
                              WHERE cp.status = 'Active'
                          ) as i
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],

            // 5. Supplier Reports
            'supplier_statement' => [
                'title' => 'Supplier Statement',
                'category' => 'supplier',
                'filters' => ['date_range', 'supplier'],
                'date_column' => 'i.date',
                'columns' => [
                    'date' => ['label' => 'Date', 'type' => 'date'],
                    'type' => ['label' => 'Type', 'type' => 'text'],
                    'ref' => ['label' => 'Reference', 'type' => 'text'],
                    'debit' => ['label' => 'Debit (Payments/Returns)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'credit' => ['label' => 'Credit (GRNs)', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'balance' => ['label' => 'Running Balance', 'type' => 'currency', 'align' => 'right']
                ],
                'sql' => "SELECT i.id, i.date, i.type, i.ref, i.debit, i.credit,
                                 SUM(i.credit - i.debit) OVER (ORDER BY i.date, i.ref) as balance,
                                 i.vendor_id
                          FROM (
                              SELECT grn.id, grn.grn_date as date, 'GRN' as type, grn.grn_number as ref,
                                     0 as debit,
                                     (SELECT COALESCE(SUM(total), 0) FROM grn_items WHERE grn_id = grn.id) as credit,
                                     grn.vendor_id
                              FROM goods_receipt_notes grn
                              WHERE grn.is_approved = 1
                              UNION ALL
                              SELECT sp.id, sp.payment_date as date, 'Payment' as type, 
                                     CONCAT('Pay: ', sp.payment_method, 
                                            IF(sp.payment_method = 'Cheque', 
                                               COALESCE((SELECT CONCAT(' #', c.cheque_number) FROM cheques c WHERE c.vendor_id = sp.vendor_id AND c.amount = sp.amount AND ABS(TIMESTAMPDIFF(SECOND, c.created_at, sp.created_at)) < 10 ORDER BY c.id DESC LIMIT 1), ''),
                                               IF(sp.reference != '', CONCAT(' (', sp.reference, ')'), '')
                                            )
                                     ) as ref,
                                     sp.amount as debit,
                                     0 as credit,
                                     sp.vendor_id
                              FROM supplier_payments sp
                              WHERE sp.status = 'Active'
                              UNION ALL
                              SELECT sr.id, sr.return_date as date, 'Supplier Return' as type, sr.return_number as ref,
                                     sr.total_amount as debit,
                                     0 as credit,
                                     sr.vendor_id
                              FROM supplier_returns sr
                              UNION ALL
                              SELECT e.id, e.expense_date as date, 'Expense' as type, CONCAT(e.reference, ' - ', e.description) as ref,
                                     e.amount as debit,
                                     0 as credit,
                                     e.vendor_id
                              FROM expenses e
                              WHERE e.vendor_id IS NOT NULL
                          ) as i
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'supplier_aging' => [
                'title' => 'Supplier Aging Report',
                'category' => 'supplier',
                'filters' => ['supplier'],
                'columns' => [
                    'vendor_name' => ['label' => 'Supplier', 'type' => 'text', 'drilldown' => 'supplier'],
                    'current' => ['label' => 'Current', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'thirty' => ['label' => '1 - 30 Days', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'sixty' => ['label' => '31 - 60 Days', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'older' => ['label' => '60+ Days', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'total' => ['label' => 'Total Payable', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT v.name as vendor_name, p.vendor_id,
                                 SUM(CASE WHEN DATEDIFF(NOW(), p.grn_date) <= 0 THEN (COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items WHERE grn_id = p.id), 0) - COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE grn_id = p.id AND is_reversed = 0), 0)) ELSE 0 END) as current,
                                 SUM(CASE WHEN DATEDIFF(NOW(), p.grn_date) > 0 AND DATEDIFF(NOW(), p.grn_date) <= 30 THEN (COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items WHERE grn_id = p.id), 0) - COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE grn_id = p.id AND is_reversed = 0), 0)) ELSE 0 END) as `thirty`,
                                 SUM(CASE WHEN DATEDIFF(NOW(), p.grn_date) > 30 AND DATEDIFF(NOW(), p.grn_date) <= 60 THEN (COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items WHERE grn_id = p.id), 0) - COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE grn_id = p.id AND is_reversed = 0), 0)) ELSE 0 END) as `sixty`,
                                 SUM(CASE WHEN DATEDIFF(NOW(), p.grn_date) > 60 THEN (COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items WHERE grn_id = p.id), 0) - COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE grn_id = p.id AND is_reversed = 0), 0)) ELSE 0 END) as `older`,
                                 SUM(COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items WHERE grn_id = p.id), 0) - COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE grn_id = p.id AND is_reversed = 0), 0)) as total
                          FROM goods_receipt_notes p
                          JOIN vendors v ON p.vendor_id = v.id
                          WHERE 1=1 /*WHERE_CLAUSE*/
                          GROUP BY v.id, v.name, p.vendor_id
                          HAVING total > 0"
            ],

            // 6. Finance & Accounts Reports
            'budget_vs_actual' => [
                'title' => 'Budget vs Actual Spending',
                'category' => 'finance',
                'columns' => [
                    'account_code' => ['label' => 'Account Code', 'type' => 'text', 'sortable' => true],
                    'account_name' => ['label' => 'Account Name', 'type' => 'text', 'sortable' => true],
                    'budget_amount' => ['label' => 'Budget Limit', 'type' => 'currency', 'align' => 'right', 'total' => 'sum', 'sortable' => true],
                    'actual_spent' => ['label' => 'Actual Spent', 'type' => 'currency', 'align' => 'right', 'total' => 'sum', 'sortable' => true],
                    'variance' => ['label' => 'Remaining Budget', 'type' => 'currency', 'align' => 'right', 'total' => 'sum', 'sortable' => true],
                    'usage_percent' => ['label' => 'Usage %', 'type' => 'text', 'align' => 'right']
                ],
                'sql' => "SELECT c.id, c.account_code, c.account_name,
                                 COALESCE(b.budget_amount, 0) as budget_amount,
                                 COALESCE((
                                     SELECT SUM(t.debit - t.credit)
                                     FROM transactions t
                                     JOIN journal_entries je ON t.journal_entry_id = je.id
                                     WHERE t.account_id = c.id AND je.status = 'Posted'
                                 ), 0) as actual_spent,
                                 (COALESCE(b.budget_amount, 0) - COALESCE((
                                     SELECT SUM(t.debit - t.credit)
                                     FROM transactions t
                                     JOIN journal_entries je ON t.journal_entry_id = je.id
                                     WHERE t.account_id = c.id AND je.status = 'Posted'
                                 ), 0)) as variance,
                                 CONCAT(FORMAT(CASE 
                                     WHEN COALESCE(b.budget_amount, 0) > 0 
                                     THEN (COALESCE((
                                         SELECT SUM(t.debit - t.credit)
                                         FROM transactions t
                                         JOIN journal_entries je ON t.journal_entry_id = je.id
                                         WHERE t.account_id = c.id AND je.status = 'Posted'
                                     ), 0) / b.budget_amount) * 100
                                     ELSE 0 
                                 END, 1), '%') as usage_percent
                          FROM chart_of_accounts c
                          LEFT JOIN budgets b ON c.id = b.account_id AND b.fiscal_year = YEAR(NOW())
                          WHERE c.account_type = 'Expense' /*WHERE_CLAUSE*/"
            ],
            'trial_balance' => [
                'title' => 'Trial Balance',
                'category' => 'finance',
                'filters' => ['date_range', 'tb_type'],
                'date_column' => 'je.entry_date',
                'columns' => [
                    'account_code' => ['label' => 'Account Code', 'type' => 'text'],
                    'account_name' => ['label' => 'Account Name', 'type' => 'text'],
                    'account_type' => ['label' => 'Type', 'type' => 'text'],
                    'debit' => ['label' => 'Debit', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'credit' => ['label' => 'Credit', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT c.account_code, c.account_name, c.account_type,
                                 SUM(COALESCE(t.debit, 0)) as debit,
                                 SUM(COALESCE(t.credit, 0)) as credit
                          FROM chart_of_accounts c
                          LEFT JOIN transactions t ON c.id = t.account_id
                          LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted' AND (:include_closing = 1 OR je.reference NOT LIKE 'YE-CLOSE-%')
                          WHERE 1=1 /*WHERE_CLAUSE*/
                          GROUP BY c.id, c.account_code, c.account_name, c.account_type"
            ],
            'profit_loss' => [
                'title' => 'Profit & Loss Statement',
                'category' => 'finance',
                'filters' => ['date_range'],
                'date_column' => 'je.entry_date',
                'columns' => [
                    'account_name' => ['label' => 'Account', 'type' => 'text'],
                    'account_type' => ['label' => 'Category', 'type' => 'text'],
                    'balance' => ['label' => 'Net Amount', 'type' => 'currency', 'align' => 'right']
                ],
                'sql' => "SELECT c.account_name, c.account_type,
                                 SUM(CASE WHEN c.account_type = 'Revenue' THEN (COALESCE(t.credit, 0) - COALESCE(t.debit, 0))
                                          WHEN c.account_type = 'Expense' THEN (COALESCE(t.debit, 0) - COALESCE(t.credit, 0))
                                          ELSE 0 END) as balance
                          FROM chart_of_accounts c
                          LEFT JOIN transactions t ON c.id = t.account_id
                          LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted' AND je.reference NOT LIKE 'YE-CLOSE-%'
                          WHERE c.account_type IN ('Revenue', 'Expense') /*WHERE_CLAUSE*/
                          GROUP BY c.id, c.account_name, c.account_type"
            ],
            'general_ledger' => [
                'title' => 'General Ledger',
                'category' => 'finance',
                'filters' => ['date_range'],
                'date_column' => 'je.entry_date',
                'columns' => [
                    'entry_date' => ['label' => 'Date', 'type' => 'date'],
                    'reference' => ['label' => 'Reference', 'type' => 'text'],
                    'account_name' => ['label' => 'Account', 'type' => 'text'],
                    'debit' => ['label' => 'Debit', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'credit' => ['label' => 'Credit', 'type' => 'currency', 'align' => 'right', 'total' => 'sum'],
                    'description' => ['label' => 'Description', 'type' => 'text']
                ],
                'sql' => "SELECT je.entry_date, je.reference, c.account_name, t.debit, t.credit, je.description
                          FROM transactions t
                          JOIN journal_entries je ON t.journal_entry_id = je.id
                          JOIN chart_of_accounts c ON t.account_id = c.id
                          WHERE 1=1 /*WHERE_CLAUSE*/"
            ],
            'balance_sheet' => [
                'title' => 'Balance Sheet',
                'category' => 'finance',
                'custom_render' => true
            ],
            'cash_flow' => [
                'title' => 'Statement of Cash Flows',
                'category' => 'finance',
                'custom_render' => true
            ],
            'multi_period_comparison' => [
                'title' => 'Multi-Period Comparison (YoY & MoM)',
                'category' => 'finance',
                'custom_render' => true
            ],

            // 7. Collection Reports
            'credit_collection' => [
                'title' => 'Collection Report',
                'category' => 'collection',
                'filters' => ['date_range', 'customer', 'rep', 'route', 'payment_method'],
                'date_column' => 'cp.payment_date',
                'columns' => [
                    'payment_date' => ['label' => 'Collection Date', 'type' => 'date'],
                    'customer_name' => ['label' => 'Customer', 'type' => 'text', 'drilldown' => 'customer'],
                    'payment_method' => ['label' => 'Method', 'type' => 'text'],
                    'reference' => ['label' => 'Ref', 'type' => 'text', 'drilldown' => 'payment'],
                    'amount' => ['label' => 'Collected Amount', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT cp.payment_date, c.name as customer_name, cp.payment_method, cp.reference, cp.amount,
                                 cp.customer_id, cp.created_by, cp.rep_route_id, cp.id
                          FROM customer_payments cp
                          JOIN customers c ON cp.customer_id = c.id
                          WHERE cp.status = 'Active' /*WHERE_CLAUSE*/"
            ],

            // 8. Route & Distribution Reports
            'route_performance' => [
                'title' => 'Route Performance Report',
                'category' => 'route',
                'filters' => ['date_range', 'route', 'rep', 'vehicle', 'driver'],
                'date_column' => 'r.start_time',
                'columns' => [
                    'route_name' => ['label' => 'Route', 'type' => 'text', 'drilldown' => 'route'],
                    'rep_name' => ['label' => 'Representative', 'type' => 'text', 'drilldown' => 'rep'],
                    'orders_count' => ['label' => 'Invoices issued', 'type' => 'number', 'align' => 'right', 'total' => 'sum'],
                    'route_sales' => ['label' => 'Sales Total', 'type' => 'currency', 'align' => 'right', 'total' => 'sum']
                ],
                'sql' => "SELECT r.route_name, r.id, r.user_id, r.start_time,
                                 COALESCE(CONCAT(e.first_name, ' ', e.last_name), u.username, 'N/A') as rep_name,
                                 COUNT(i.id) as orders_count,
                                 SUM(COALESCE(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0), 0)) as route_sales
                          FROM rep_daily_routes r
                          LEFT JOIN users u ON r.user_id = u.id
                          LEFT JOIN employees e ON u.employee_id = e.id
                          LEFT JOIN invoices i ON i.rep_route_id = r.id AND i.status != 'Voided'
                          WHERE 1=1 /*WHERE_CLAUSE*/
                          GROUP BY r.id, r.route_name, u.username, e.first_name, e.last_name, r.user_id, r.start_time"
            ],

            // 9. Management Reports
            'monthly_kpi' => [
                'title' => 'Monthly KPI Report',
                'category' => 'management',
                'columns' => [
                    'metric' => ['label' => 'Key Performance Indicator', 'type' => 'text'],
                    'target' => ['label' => 'Target', 'type' => 'text', 'align' => 'right'],
                    'actual' => ['label' => 'Actual Value', 'type' => 'text', 'align' => 'right'],
                    'variance' => ['label' => 'Variance', 'type' => 'text', 'align' => 'right']
                ],
                'sql' => "SELECT 'Monthly Sales Revenue' as metric, 'Rs. 1,000,000.00' as target, 
                                 CONCAT('Rs. ', FORMAT(COALESCE((SELECT SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) FROM invoices WHERE status != 'Voided' AND MONTH(invoice_date) = MONTH(NOW()) AND YEAR(invoice_date) = YEAR(NOW())), 0), 2)) as actual,
                                 CONCAT(FORMAT(COALESCE((SELECT SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) FROM invoices WHERE status != 'Voided' AND MONTH(invoice_date) = MONTH(NOW()) AND YEAR(invoice_date) = YEAR(NOW())), 0) - 1000000.00, 2)) as variance
                          UNION ALL
                          SELECT 'New Customers Registered' as metric, '50' as target, 
                                 CAST(COALESCE((SELECT COUNT(*) FROM customers WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())), 0) as CHAR) as actual,
                                 CAST(COALESCE((SELECT COUNT(*) FROM customers WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())), 0) - 50 as CHAR) as variance
                          UNION ALL
                          SELECT 'Outstanding Receivables' as metric, 'Rs. 200,000.00' as target, 
                                 CONCAT('Rs. ', FORMAT(COALESCE((SELECT SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) FROM invoices WHERE status != 'Paid' AND status != 'Voided'), 0), 2)) as actual,
                                 CONCAT(FORMAT(COALESCE((SELECT SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) FROM invoices WHERE status != 'Paid' AND status != 'Voided'), 0) - 200000.00, 2)) as variance
                          UNION ALL
                          SELECT 'Procurement (GRN) Value' as metric, 'Rs. 500,000.00' as target, 
                                 CONCAT('Rs. ', FORMAT(COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items), 0), 2)) as actual,
                                 CONCAT(FORMAT(COALESCE((SELECT SUM(quantity * unit_cost) FROM grn_items), 0) - 500000.00, 2)) as variance"
            ]
        ];
    }

    /**
     * Fetch the report dataset and format it server-side.
     */
    public function fetchData($reportKey, $filters = [], $page = 1, $limit = 50, $sortCol = null, $sortDir = 'ASC') {
        $registry = self::getReportsRegistry();
        if (!isset($registry[$reportKey])) {
            throw new Exception("Report code not registered: " . $reportKey);
        }

        $metadata = $registry[$reportKey];
        $offset = ($page - 1) * $limit;

        if (!isset($metadata['sql'])) {
            throw new Exception("SQL query not defined for report: " . $reportKey);
        }

        $baseSql = $metadata['sql'];
        $params = [];
        $clauses = [];

        // Apply dynamic filter clauses to the base SQL query
        $dateColumn = $metadata['date_column'] ?? null;
        if ($dateColumn) {
            if (isset($filters['start_date']) && !empty($filters['start_date'])) {
                $clauses[] = $dateColumn . " >= :start_date";
                $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
            }
            if (isset($filters['end_date']) && !empty($filters['end_date'])) {
                $clauses[] = $dateColumn . " <= :end_date";
                $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
            }
        }

        // 1. Customer Filter
        if (isset($filters['customer']) && !empty($filters['customer'])) {
            if (preg_match('/\bi\.customer_id\b/', $baseSql)) {
                $clauses[] = "i.customer_id = :customer";
            } elseif (preg_match('/\bcp\.customer_id\b/', $baseSql)) {
                $clauses[] = "cp.customer_id = :customer";
            } elseif (preg_match('/\bc\.id\b/', $baseSql)) {
                $clauses[] = "c.id = :customer";
            } elseif (strpos($baseSql, 'customer_id') !== false) {
                $clauses[] = "customer_id = :customer";
            }
            $params[':customer'] = $filters['customer'];
        }

        // 2. Supplier Filter
        if (isset($filters['supplier']) && !empty($filters['supplier'])) {
            if (preg_match('/\bp\.vendor_id\b/', $baseSql)) {
                $clauses[] = "p.vendor_id = :supplier";
            } elseif (preg_match('/\bg\.vendor_id\b/', $baseSql)) {
                $clauses[] = "g.vendor_id = :supplier";
            } elseif (preg_match('/\bi\.vendor_id\b/', $baseSql)) {
                $clauses[] = "i.vendor_id = :supplier";
            } elseif (preg_match('/\bv\.id\b/', $baseSql)) {
                $clauses[] = "v.id = :supplier";
            } elseif (strpos($baseSql, 'vendor_id') !== false) {
                $clauses[] = "vendor_id = :supplier";
            }
            $params[':supplier'] = $filters['supplier'];
        }

        // 3. Product Filter
        if (isset($filters['product']) && !empty($filters['product'])) {
            if (preg_match('/\bsl\.item_id\b/', $baseSql)) {
                $clauses[] = "sl.item_id = :product";
            } elseif (preg_match('/\bii\.item_id\b/', $baseSql)) {
                $clauses[] = "ii.item_id = :product";
            } elseif (preg_match('/\bwt\.item_id\b/', $baseSql)) {
                $clauses[] = "wt.item_id = :product";
            } elseif (preg_match('/\bsb\.item_id\b/', $baseSql)) {
                $clauses[] = "sb.item_id = :product";
            } elseif (preg_match('/\bi\.id\b/', $baseSql)) {
                $clauses[] = "i.id = :product";
            } elseif (strpos($baseSql, 'item_id') !== false) {
                $clauses[] = "item_id = :product";
            }
            $params[':product'] = $filters['product'];
        }

        // 4. Warehouse Filter
        if (isset($filters['warehouse']) && !empty($filters['warehouse'])) {
            if (preg_match('/\bi\.warehouse_id\b/', $baseSql)) {
                $clauses[] = "i.warehouse_id = :warehouse";
            } elseif (preg_match('/\bsl\.warehouse_id\b/', $baseSql)) {
                $clauses[] = "sl.warehouse_id = :warehouse";
            } elseif (preg_match('/\bwt\.from_warehouse_id\b/', $baseSql)) {
                $clauses[] = "(wt.from_warehouse_id = :warehouse OR wt.to_warehouse_id = :warehouse)";
            } elseif (strpos($baseSql, 'warehouse_id') !== false) {
                $clauses[] = "warehouse_id = :warehouse";
            }
            $params[':warehouse'] = $filters['warehouse'];
        }

        // 5. Category Filter
        if (isset($filters['category']) && !empty($filters['category'])) {
            if (preg_match('/\bi\.category_id\b/', $baseSql)) {
                $clauses[] = "i.category_id = :category";
            } elseif (preg_match('/\bit\.category_id\b/', $baseSql)) {
                $clauses[] = "it.category_id = :category";
            } elseif (strpos($baseSql, 'category_id') !== false) {
                $clauses[] = "category_id = :category";
            }
            $params[':category'] = $filters['category'];
        }

        // 6. Route Filter
        if (isset($filters['route']) && !empty($filters['route'])) {
            if (preg_match('/\bi\.rep_route_id\b/', $baseSql)) {
                $clauses[] = "i.rep_route_id = :route";
            } elseif (preg_match('/\bcp\.rep_route_id\b/', $baseSql)) {
                $clauses[] = "cp.rep_route_id = :route";
            } elseif (preg_match('/\br\.id\b/', $baseSql)) {
                $clauses[] = "r.id = :route";
            } elseif (strpos($baseSql, 'rep_route_id') !== false) {
                $clauses[] = "rep_route_id = :route";
            } elseif (strpos($baseSql, 'customers') !== false) {
                $clauses[] = "c.id IN (SELECT DISTINCT customer_id FROM invoices WHERE rep_route_id = :route)";
            }
            $params[':route'] = $filters['route'];
        }

        // 7. Sales Rep Filter
        if (isset($filters['rep']) && !empty($filters['rep'])) {
            if (preg_match('/\bi\.created_by\b/', $baseSql)) {
                $clauses[] = "i.created_by = :rep";
            } elseif (preg_match('/\bcp\.created_by\b/', $baseSql)) {
                $clauses[] = "(cp.created_by = :rep OR cp.rep_route_id IN (SELECT id FROM rep_daily_routes WHERE user_id = :rep) OR cp.customer_id IN (SELECT customer_id FROM invoices WHERE created_by = :rep))";
            } elseif (preg_match('/\br\.user_id\b/', $baseSql)) {
                $clauses[] = "r.user_id = :rep";
            } elseif (strpos($baseSql, 'customers') !== false) {
                $clauses[] = "c.id IN (SELECT DISTINCT customer_id FROM invoices WHERE created_by = :rep)";
            }
            $params[':rep'] = $filters['rep'];
        }

        // 8. Payment Method Filter
        if (isset($filters['payment_method']) && !empty($filters['payment_method'])) {
            if (preg_match('/\bcp\.payment_method\b/', $baseSql)) {
                $clauses[] = "cp.payment_method = :payment_method";
                $params[':payment_method'] = $filters['payment_method'];
            } elseif (preg_match('/\bi\.id\b/', $baseSql) && strpos($baseSql, 'invoices') !== false) {
                $clauses[] = "EXISTS (SELECT 1 FROM customer_payment_allocations cpa JOIN customer_payments cp ON cpa.customer_payment_id = cp.id WHERE cpa.invoice_id = i.id AND cp.payment_method = :payment_method AND cpa.is_reversed = 0)";
                $params[':payment_method'] = $filters['payment_method'];
            } elseif (strpos($baseSql, 'payment_method') !== false) {
                $clauses[] = "payment_method = :payment_method";
                $params[':payment_method'] = $filters['payment_method'];
            }
        }

        // 9. Status Filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            if (preg_match('/\bi\.status\b/', $baseSql)) {
                $clauses[] = "i.status = :status";
                $params[':status'] = $filters['status'];
            } elseif (preg_match('/\bp\.status\b/', $baseSql)) {
                $clauses[] = "p.status = :status";
                $params[':status'] = $filters['status'];
            } elseif (preg_match('/\bg\.status\b/', $baseSql)) {
                $clauses[] = "g.status = :status";
                $params[':status'] = $filters['status'];
            } elseif (preg_match('/\bsb\.quantity_remaining\b/', $baseSql)) {
                if ($filters['status'] === 'Active') {
                    $clauses[] = "sb.quantity_remaining > 0";
                } elseif ($filters['status'] === 'Depleted') {
                    $clauses[] = "sb.quantity_remaining <= 0";
                }
            } elseif (strpos($baseSql, 'status') !== false) {
                $clauses[] = "status = :status";
                $params[':status'] = $filters['status'];
            }
        }

        // 10. Brand Filter
        if (isset($filters['brand']) && !empty($filters['brand'])) {
            if (preg_match('/\bi\.brand\b/', $baseSql)) {
                $clauses[] = "i.brand = :brand";
                $params[':brand'] = $filters['brand'];
            } elseif (preg_match('/\bit\.brand\b/', $baseSql)) {
                $clauses[] = "it.brand = :brand";
                $params[':brand'] = $filters['brand'];
            } elseif (strpos($baseSql, 'brand') !== false) {
                $clauses[] = "brand = :brand";
                $params[':brand'] = $filters['brand'];
            }
        }

        // 11. Customer/Supplier Group Filter
        if (isset($filters['group']) && !empty($filters['group'])) {
            if (preg_match('/\bc\.customer_type\b/', $baseSql)) {
                $clauses[] = "c.customer_type = :group";
                $params[':group'] = $filters['group'];
            } elseif (strpos($baseSql, 'customer_type') !== false) {
                $clauses[] = "customer_type = :group";
                $params[':group'] = $filters['group'];
            }
        }

        // 12. Vehicle Filter
        if (isset($filters['vehicle']) && !empty($filters['vehicle'])) {
            if (preg_match('/\bi\.rep_route_id\b/', $baseSql)) {
                $clauses[] = "EXISTS (SELECT 1 FROM deliveries d WHERE d.rep_route_id = i.rep_route_id AND d.vehicle_number = :vehicle)";
                $params[':vehicle'] = $filters['vehicle'];
            } elseif (preg_match('/\br\.id\b/', $baseSql)) {
                $clauses[] = "EXISTS (SELECT 1 FROM deliveries d WHERE d.rep_route_id = r.id AND d.vehicle_number = :vehicle)";
                $params[':vehicle'] = $filters['vehicle'];
            }
        }

        // 13. Driver Filter
        if (isset($filters['driver']) && !empty($filters['driver'])) {
            if (preg_match('/\bi\.rep_route_id\b/', $baseSql)) {
                $clauses[] = "EXISTS (SELECT 1 FROM deliveries d WHERE d.rep_route_id = i.rep_route_id AND d.driver_name = :driver)";
                $params[':driver'] = $filters['driver'];
            } elseif (preg_match('/\br\.id\b/', $baseSql)) {
                $clauses[] = "EXISTS (SELECT 1 FROM deliveries d WHERE d.rep_route_id = r.id AND d.driver_name = :driver)";
                $params[':driver'] = $filters['driver'];
            }
        }

        // 14. Partner Filter
        if (isset($filters['partner']) && !empty($filters['partner'])) {
            if (preg_match('/\bi\.rep_route_id\b/', $baseSql)) {
                $clauses[] = "EXISTS (SELECT 1 FROM deliveries d WHERE d.rep_route_id = i.rep_route_id AND d.partner_name = :partner)";
                $params[':partner'] = $filters['partner'];
            } elseif (preg_match('/\br\.id\b/', $baseSql)) {
                $clauses[] = "EXISTS (SELECT 1 FROM deliveries d WHERE d.rep_route_id = r.id AND d.partner_name = :partner)";
                $params[':partner'] = $filters['partner'];
            }
        }

        // 15. Territory Filter
        if (isset($filters['territory']) && !empty($filters['territory'])) {
            if (preg_match('/\bc\.territory\b/', $baseSql)) {
                $clauses[] = "c.territory = :territory";
                $params[':territory'] = $filters['territory'];
            } elseif (strpos($baseSql, 'territory') !== false) {
                $clauses[] = "territory = :territory";
                $params[':territory'] = $filters['territory'];
            }
        }

        // 16. Trial Balance Type Filter (Pre-Closing vs Post-Closing)
        if (strpos($baseSql, ':include_closing') !== false) {
            $includeClosing = (isset($filters['tb_type']) && $filters['tb_type'] === 'post_closing') ? 1 : 0;
            $params[':include_closing'] = $includeClosing;
        }

        // Inject clauses precisely into /*WHERE_CLAUSE*/ or standard position
        if (!empty($clauses)) {
            $conditions = " AND " . implode(" AND ", $clauses);
            if (strpos($baseSql, '/*WHERE_CLAUSE*/') !== false) {
                $baseSql = str_replace('/*WHERE_CLAUSE*/', $conditions, $baseSql);
            } else {
                $pattern = '/\s+(GROUP\s+BY|HAVING|ORDER\s+BY)\b/i';
                if (preg_match($pattern, $baseSql, $m, PREG_OFFSET_CAPTURE)) {
                    $offset = $m[0][1];
                    $baseSql = substr($baseSql, 0, $offset) . $conditions . substr($baseSql, $offset);
                } else {
                    $baseSql .= $conditions;
                }
            }
        } else {
            $baseSql = str_replace('/*WHERE_CLAUSE*/', '', $baseSql);
        }

        // Live execution
        try {
            // Get Total Row count for server-side pagination
            $countSql = "SELECT COUNT(*) as cnt FROM (" . $baseSql . ") as temp_table";
            $this->db->query($countSql);
            foreach ($params as $k => $v) {
                if (strpos($countSql, $k) !== false) {
                    $this->db->bind($k, $v);
                }
            }
            $countRow = $this->db->single();
            $totalRows = $countRow ? (int)$countRow->cnt : 0;

            // Calculate Grand Totals dynamically on the full filtered dataset (not paginated)
            $grandTotals = [];
            $totalSelects = [];
            foreach ($metadata['columns'] as $colKey => $colDef) {
                if (isset($colDef['total']) && $colDef['total'] === 'sum') {
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $colKey)) {
                        $totalSelects[] = "SUM(" . $colKey . ") as sum_" . $colKey;
                    }
                }
            }
            if (!empty($totalSelects)) {
                $totalsSql = "SELECT " . implode(', ', $totalSelects) . " FROM (" . $baseSql . ") as totals_table";
                try {
                    $this->db->query($totalsSql);
                    foreach ($params as $k => $v) {
                        if (strpos($totalsSql, $k) !== false) {
                            $this->db->bind($k, $v);
                        }
                    }
                    $totalsRow = $this->db->single();
                    if ($totalsRow) {
                        foreach ($metadata['columns'] as $colKey => $colDef) {
                            if (isset($colDef['total']) && $colDef['total'] === 'sum') {
                                $grandTotals[$colKey] = floatval($totalsRow->{"sum_" . $colKey} ?? 0);
                            }
                        }
                    }
                } catch (PDOException $totalEx) {
                    error_log("ReportEngine failed to calculate full grand totals: " . $totalEx->getMessage());
                }
            }

            // Apply Sorting & Pagination
            if (!empty($sortCol) && isset($metadata['columns'][$sortCol]) && preg_match('/^[a-zA-Z0-9_\.]+$/', $sortCol)) {
                $baseSql .= " ORDER BY " . $sortCol . " " . ($sortDir === 'DESC' ? 'DESC' : 'ASC');
            }
            $baseSql .= " LIMIT :limit OFFSET :offset";

            $this->db->query($baseSql);
            foreach ($params as $k => $v) {
                if (strpos($baseSql, $k) !== false) {
                    $this->db->bind($k, $v);
                }
            }
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            $this->db->bind(':offset', $offset, PDO::PARAM_INT);
            $rows = $this->db->resultSet() ?: [];

            return [
                'rows' => $rows,
                'total_rows' => $totalRows,
                'grand_totals' => $grandTotals,
                'simulation' => false
            ];
        } catch (PDOException $ex) {
            error_log("ReportEngine Live Query Error: " . $ex->getMessage() . " | SQL: " . $baseSql . " | Params: " . json_encode($params));
            return [
                'rows' => [],
                'total_rows' => 0,
                'grand_totals' => [],
                'simulation' => false,
                'db_error' => $ex->getMessage() . " | SQL: " . $baseSql
            ];
        }
    }
}
