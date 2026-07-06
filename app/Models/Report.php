<?php
class Report {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /** Invoice grand total expression (subtotal − discount + tax). */
    public function invoiceGrandTotalExpr($alias = 'i') {
        return "({$alias}.total_amount - COALESCE(CASE WHEN {$alias}.global_discount_type = '%' 
                THEN ({$alias}.total_amount * {$alias}.global_discount_val / 100) 
                ELSE {$alias}.global_discount_val END, 0) + COALESCE({$alias}.tax_amount, 0))";
    }

    public function normalizeDateRange($start = null, $end = null) {
        $end = $end ?: date('Y-m-d');
        $start = $start ?: date('Y-m-d', strtotime('-30 days'));
        if (strtotime($start) > strtotime($end)) {
            [$start, $end] = [$end, $start];
        }
        return [$start, $end];
    }

    public function getTrialBalanceData($endDate = null) {
        if ($endDate) {
            $this->db->query("SELECT c.id, c.account_code, c.account_name, c.account_type,
                                     SUM(COALESCE(t.debit, 0)) as debit_sum,
                                     SUM(COALESCE(t.credit, 0)) as credit_sum,
                                     SUM(CASE WHEN c.account_type IN ('Asset', 'Expense') THEN (COALESCE(t.debit, 0) - COALESCE(t.credit, 0))
                                              ELSE (COALESCE(t.credit, 0) - COALESCE(t.debit, 0)) END) as balance
                              FROM chart_of_accounts c
                              LEFT JOIN transactions t ON c.id = t.account_id
                              LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted' AND je.entry_date <= :end_date AND je.reference NOT LIKE 'YE-CLOSE-%'
                              GROUP BY c.id, c.account_code, c.account_name, c.account_type
                              HAVING balance != 0
                              ORDER BY c.account_code ASC");
            $this->db->bind(':end_date', $endDate);
            return $this->db->resultSet();
        } else {
            $this->db->query("SELECT * FROM chart_of_accounts WHERE balance != 0 ORDER BY account_code ASC");
            return $this->db->resultSet();
        }
    }

    public function getAccountsByTypes($types, $endDate = null, $startDate = null) {
        $placeholders = str_repeat('?,', count($types) - 1) . '?';
        if ($endDate) {
            $hasStart = ($startDate && (in_array('Revenue', $types) || in_array('Expense', $types)));
            if ($hasStart) {
                $sql = "SELECT c.id, c.account_code, c.account_name, c.account_type, c.parent_id,
                               SUM(CASE WHEN c.account_type IN ('Asset', 'Expense') THEN (COALESCE(t.debit, 0) - COALESCE(t.credit, 0))
                                        ELSE (COALESCE(t.credit, 0) - COALESCE(t.debit, 0)) END) as balance
                        FROM chart_of_accounts c
                        LEFT JOIN transactions t ON c.id = t.account_id
                        LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted' 
                            AND je.entry_date BETWEEN ? AND ? AND je.reference NOT LIKE 'YE-CLOSE-%'
                        WHERE c.account_type IN ($placeholders)
                        GROUP BY c.id, c.account_code, c.account_name, c.account_type, c.parent_id
                        HAVING balance != 0
                        ORDER BY FIELD(c.account_type, 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'), c.account_code ASC";
                
                $params = array_merge([$startDate, $endDate], $types);
            } else {
                $sql = "SELECT c.id, c.account_code, c.account_name, c.account_type, c.parent_id,
                               SUM(CASE WHEN c.account_type IN ('Asset', 'Expense') THEN (COALESCE(t.debit, 0) - COALESCE(t.credit, 0))
                                        ELSE (COALESCE(t.credit, 0) - COALESCE(t.debit, 0)) END) as balance
                        FROM chart_of_accounts c
                        LEFT JOIN transactions t ON c.id = t.account_id
                        LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted' 
                            AND je.entry_date <= ? AND je.reference NOT LIKE 'YE-CLOSE-%'
                        WHERE c.account_type IN ($placeholders)
                        GROUP BY c.id, c.account_code, c.account_name, c.account_type, c.parent_id
                        HAVING balance != 0
                        ORDER BY FIELD(c.account_type, 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'), c.account_code ASC";
                
                $params = array_merge([$endDate], $types);
            }
            
            $this->db->query($sql);
            $this->db->stmt->execute($params);
            return $this->db->stmt->fetchAll(PDO::FETCH_OBJ);
        } else {
            $sql = "SELECT * FROM chart_of_accounts 
                    WHERE account_type IN ($placeholders) AND balance != 0 
                    ORDER BY FIELD(account_type, 'Asset', 'Liability', 'Equity', 'Revenue', 'Expense'), account_code ASC";
            $this->db->query($sql);
            $this->db->stmt->execute($types);
            return $this->db->stmt->fetchAll(PDO::FETCH_OBJ);
        }
    }

    public function getARAging() {
        $gt = $this->invoiceGrandTotalExpr('i');
        $this->db->query("SELECT i.id, i.invoice_number, i.due_date, i.invoice_date, i.status,
                                 {$gt} as total_amount,
                                 c.name as customer_name,
                                 DATEDIFF(CURRENT_DATE, i.due_date) as days_overdue
                          FROM invoices i
                          JOIN customers c ON i.customer_id = c.id
                          WHERE i.status IN ('Unpaid', 'Draft')
                          ORDER BY c.name ASC, i.due_date ASC");
        return $this->db->resultSet();
    }

    public function getFIFOSalesData($start = null, $end = null) {
        [$start, $end] = $this->normalizeDateRange($start, $end);
        $this->db->query("
            SELECT 
                'ERP Sales' as source,
                i.id as invoice_id,
                i.invoice_number,
                i.invoice_date as sale_date,
                ii.description as item_name,
                ii.quantity as qty,
                ii.unit_price as price,
                ii.total as revenue,
                COALESCE(ii.cost_at_sale, 0) as unit_cost,
                (ii.quantity * COALESCE(ii.cost_at_sale, 0)) as total_cost,
                (ii.total - (ii.quantity * COALESCE(ii.cost_at_sale, 0))) as profit
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.status != 'Voided' AND i.invoice_date BETWEEN :start AND :end
            ORDER BY sale_date DESC, invoice_number DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet() ?: [];
    }

    public function getSalesSummary($start, $end) {
        $gt = $this->invoiceGrandTotalExpr('i');
        $this->db->query("
            SELECT 
                COUNT(*) as invoice_count,
                COALESCE(SUM({$gt}), 0) as gross_sales,
                COALESCE(SUM(i.tax_amount), 0) as total_tax,
                COALESCE(SUM(CASE WHEN i.status = 'Paid' THEN {$gt} ELSE 0 END), 0) as paid_sales,
                COALESCE(SUM(CASE WHEN i.status = 'Unpaid' THEN {$gt} ELSE 0 END), 0) as unpaid_sales,
                COALESCE(SUM(CASE WHEN i.status = 'Voided' THEN 1 ELSE 0 END), 0) as voided_count
            FROM invoices i
            WHERE i.invoice_date BETWEEN :start AND :end AND i.status != 'Voided'
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $summary = $this->db->single();

        $this->db->query("
            SELECT i.invoice_date as period_date, COUNT(*) as cnt,
                   COALESCE(SUM({$gt}), 0) as daily_total
            FROM invoices i
            WHERE i.invoice_date BETWEEN :start AND :end AND i.status != 'Voided'
            GROUP BY i.invoice_date
            ORDER BY i.invoice_date ASC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $daily = $this->db->resultSet();

        return ['summary' => $summary, 'daily' => $daily];
    }

    public function getSalesByCustomer($start, $end) {
        $gt = $this->invoiceGrandTotalExpr('i');
        $this->db->query("
            SELECT c.id, c.name as customer_name, c.phone,
                   COUNT(i.id) as invoice_count,
                   COALESCE(SUM({$gt}), 0) as total_sales,
                   COALESCE(SUM(CASE WHEN i.status = 'Paid' THEN {$gt} ELSE 0 END), 0) as paid_amount,
                   COALESCE(SUM(CASE WHEN i.status = 'Unpaid' THEN {$gt} ELSE 0 END), 0) as outstanding
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.invoice_date BETWEEN :start AND :end AND i.status != 'Voided'
            GROUP BY c.id, c.name, c.phone
            ORDER BY total_sales DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet();
    }

    public function getSalesByProduct($start, $end) {
        $this->db->query("
            SELECT ii.description as item_name,
                   SUM(ii.quantity) as total_qty,
                   COALESCE(SUM(ii.total), 0) as total_revenue,
                   COALESCE(SUM(ii.quantity * COALESCE(ii.cost_at_sale, 0)), 0) as total_cost,
                   COALESCE(SUM(ii.total - (ii.quantity * COALESCE(ii.cost_at_sale, 0))), 0) as gross_profit
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.invoice_date BETWEEN :start AND :end AND i.status != 'Voided'
            GROUP BY ii.description
            ORDER BY total_revenue DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet();
    }

    public function getCollectionsReport($start, $end) {
        $this->db->query("
            SELECT cp.payment_date, cp.payment_method,
                   COUNT(*) as tx_count,
                   COALESCE(SUM(cp.amount), 0) as total_collected
            FROM customer_payments cp
            WHERE cp.payment_date BETWEEN :start AND :end
            GROUP BY cp.payment_date, cp.payment_method
            ORDER BY cp.payment_date DESC, cp.payment_method ASC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $lines = $this->db->resultSet();

        $this->db->query("
            SELECT cp.payment_method,
                   COUNT(*) as tx_count,
                   COALESCE(SUM(cp.amount), 0) as total_collected
            FROM customer_payments cp
            WHERE cp.payment_date BETWEEN :start AND :end
            GROUP BY cp.payment_method
            ORDER BY total_collected DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $byMethod = $this->db->resultSet();

        $this->db->query("
            SELECT cp.*, c.name as customer_name, u.username as collected_by
            FROM customer_payments cp
            JOIN customers c ON cp.customer_id = c.id
            LEFT JOIN users u ON cp.created_by = u.id
            WHERE cp.payment_date BETWEEN :start AND :end
            ORDER BY cp.payment_date DESC, cp.id DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $detail = $this->db->resultSet();

        return ['lines' => $lines, 'by_method' => $byMethod, 'detail' => $detail];
    }

    public function getPurchasesSummary($start, $end) {
        $this->db->query("
            SELECT v.name as vendor_name,
                   COUNT(DISTINCT p.id) as po_count,
                   COALESCE(SUM(p.total_amount), 0) as po_total,
                   COUNT(DISTINCT g.id) as grn_count,
                   COALESCE(SUM(gi.line_total), 0) as grn_received_value
            FROM vendors v
            LEFT JOIN purchase_orders p ON p.vendor_id = v.id 
                AND p.po_date BETWEEN :start AND :end
            LEFT JOIN goods_receipt_notes g ON g.vendor_id = v.id 
                AND g.grn_date BETWEEN :start2 AND :end2
            LEFT JOIN (
                SELECT grn_id, SUM(quantity * unit_cost) as line_total
                FROM grn_items GROUP BY grn_id
            ) gi ON gi.grn_id = g.id
            GROUP BY v.id, v.name
            HAVING po_count > 0 OR grn_count > 0
            ORDER BY grn_received_value DESC, po_total DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $this->db->bind(':start2', $start);
        $this->db->bind(':end2', $end);
        return $this->db->resultSet();
    }

    public function getGeneralLedger($start, $end) {
        $this->db->query("
            SELECT je.entry_date, je.reference, je.description, je.status,
                   c.account_code, c.account_name, c.account_type,
                   t.debit, t.credit,
                   u.username as posted_by
            FROM transactions t
            JOIN journal_entries je ON t.journal_entry_id = je.id
            JOIN chart_of_accounts c ON t.account_id = c.id
            LEFT JOIN users u ON je.created_by = u.id
            WHERE je.entry_date BETWEEN :start AND :end
            ORDER BY je.entry_date ASC, je.id ASC, t.id ASC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet();
    }

    public function getSalesByRep($start, $end) {
        $gt = $this->invoiceGrandTotalExpr('i');
        $this->db->query("
            SELECT r.id, r.route_name, r.start_time, r.end_time, r.status,
                   COALESCE(e.first_name, u.username) as rep_first,
                   COALESCE(e.last_name, '') as rep_last,
                   COUNT(i.id) as invoice_count,
                   COALESCE(SUM({$gt}), 0) as route_sales
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            LEFT JOIN invoices i ON i.rep_route_id = r.id AND i.status != 'Voided'
                AND i.invoice_date BETWEEN :start AND :end
            WHERE DATE(r.start_time) BETWEEN :start2 AND :end2
            GROUP BY r.id, r.route_name, r.start_time, r.end_time, r.status,
                     e.first_name, e.last_name, u.username
            ORDER BY r.start_time DESC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $this->db->bind(':start2', $start);
        $this->db->bind(':end2', $end);
        return $this->db->resultSet();
    }

    public function getTaxSummary($start, $end) {
        $gt = $this->invoiceGrandTotalExpr('i');
        $this->db->query("
            SELECT i.invoice_date,
                   COUNT(*) as invoice_count,
                   COALESCE(SUM(i.total_amount), 0) as subtotal,
                   COALESCE(SUM(CASE WHEN i.global_discount_type = '%' 
                       THEN i.total_amount * i.global_discount_val / 100 
                       ELSE i.global_discount_val END), 0) as total_discount,
                   COALESCE(SUM(i.tax_amount), 0) as total_tax,
                   COALESCE(SUM({$gt}), 0) as grand_total
            FROM invoices i
            WHERE i.invoice_date BETWEEN :start AND :end AND i.status != 'Voided'
            GROUP BY i.invoice_date
            ORDER BY i.invoice_date ASC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $daily = $this->db->resultSet();

        $this->db->query("
            SELECT COALESCE(SUM(i.tax_amount), 0) as total_tax,
                   COALESCE(SUM({$gt}), 0) as grand_total
            FROM invoices i
            WHERE i.invoice_date BETWEEN :start AND :end AND i.status != 'Voided'
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $totals = $this->db->single();

        return ['daily' => $daily, 'totals' => $totals];
    }

    public function getInventoryValuation() {
        $this->db->query("
            SELECT i.id, i.name, i.item_code,
                   COALESCE(ic.name, 'Uncategorized') as category_name,
                   COALESCE(i.quantity_on_hand, 0) as qty_on_hand,
                   COALESCE(i.cost_price, 0) as unit_cost,
                   COALESCE(i.price, 0) as selling_price,
                   (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.cost_price, 0)) as stock_value_cost,
                   (COALESCE(i.quantity_on_hand, 0) * COALESCE(i.price, 0)) as stock_value_retail
            FROM items i
            LEFT JOIN item_categories ic ON i.category_id = ic.id
            WHERE COALESCE(i.quantity_on_hand, 0) > 0
            ORDER BY category_name ASC, i.name ASC
        ");
        $rows = $this->db->resultSet();

        $totalCost = 0;
        $totalRetail = 0;
        foreach ($rows as $r) {
            $totalCost += $r->stock_value_cost;
            $totalRetail += $r->stock_value_retail;
        }
        return ['rows' => $rows, 'total_cost' => $totalCost, 'total_retail' => $totalRetail];
    }

    public function getPeriodProfitLoss($start, $end) {
        $this->db->query("
            SELECT c.id, c.account_code, c.account_name, c.account_type,
                   COALESCE(SUM(t.debit), 0) as total_debit,
                   COALESCE(SUM(t.credit), 0) as total_credit
            FROM transactions t
            JOIN journal_entries je ON t.journal_entry_id = je.id
            JOIN chart_of_accounts c ON t.account_id = c.id
            WHERE je.entry_date BETWEEN :start AND :end
              AND je.status = 'Posted'
              AND je.reference NOT LIKE 'YE-CLOSE-%'
              AND c.account_type IN ('Revenue', 'Expense')
            GROUP BY c.id, c.account_code, c.account_name, c.account_type
            ORDER BY FIELD(c.account_type, 'Revenue', 'Expense'), c.account_code ASC
        ");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet();
    }

    public function getComparativeBalances($start, $end) {
        $this->db->query("SELECT c.id, c.account_code, c.account_name, c.account_type,
                                 SUM(CASE WHEN c.account_type IN ('Asset', 'Expense') THEN (COALESCE(t.debit, 0) - COALESCE(t.credit, 0))
                                          ELSE (COALESCE(t.credit, 0) - COALESCE(t.debit, 0)) END) as balance
                          FROM chart_of_accounts c
                          LEFT JOIN transactions t ON c.id = t.account_id
                          LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted'
                              AND je.entry_date BETWEEN :start AND :end
                          GROUP BY c.id, c.account_code, c.account_name, c.account_type
                          ORDER BY c.account_code ASC");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet() ?: [];
    }

    public function getComparativeBalancesById($start, $end) {
        $this->db->query("SELECT c.id,
                                 SUM(CASE WHEN c.account_type IN ('Asset', 'Expense') THEN (COALESCE(t.debit, 0) - COALESCE(t.credit, 0))
                                          ELSE (COALESCE(t.credit, 0) - COALESCE(t.debit, 0)) END) as balance
                          FROM chart_of_accounts c
                          LEFT JOIN transactions t ON c.id = t.account_id
                          LEFT JOIN journal_entries je ON t.journal_entry_id = je.id AND je.status = 'Posted'
                              AND je.entry_date BETWEEN :start AND :end
                          GROUP BY c.id");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        return $this->db->resultSet() ?: [];
    }

    public function getCompanySettings() {
        $this->db->query("SELECT * FROM company_settings LIMIT 1");
        return $this->db->single() ?: (object)[
            'company_name' => 'Falcon Stationary (Pvt) Ltd',
            'phone' => '', 'email' => '', 'address' => ''
        ];
    }

    public function resolveEntityName($type, $id) {
        switch ($type) {
            case 'customer':
                $this->db->query("SELECT name FROM customers WHERE id = :id");
                break;
            case 'supplier':
                $this->db->query("SELECT name FROM vendors WHERE id = :id");
                break;
            case 'product':
                $this->db->query("SELECT name, item_code FROM items WHERE id = :id");
                $this->db->bind(':id', $id);
                $row = $this->db->single();
                return $row ? ($row->item_code . ' - ' . $row->name) : null;
            case 'warehouse':
                $this->db->query("SELECT name FROM warehouses WHERE id = :id");
                break;
            case 'category':
                $this->db->query("SELECT name FROM item_categories WHERE id = :id");
                break;
            case 'route':
                $this->db->query("SELECT route_name as name FROM rep_daily_routes WHERE id = :id");
                break;
            case 'rep':
                $this->db->query("SELECT username as name FROM users WHERE id = :id");
                break;
            case 'driver':
                $this->db->query("SELECT CONCAT(first_name, ' ', last_name) as name FROM employees WHERE id = :id");
                break;
            default:
                return null;
        }
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ? $row->name : null;
    }

    public function getReportFiltersData() {
        $data = [];
        
        $this->db->query("SELECT id, name FROM customers ORDER BY name ASC");
        $data['customers'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, name FROM vendors ORDER BY name ASC");
        $data['suppliers'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, name FROM items ORDER BY name ASC");
        $data['products'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, name FROM warehouses ORDER BY name ASC");
        $data['warehouses'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, route_name FROM rep_daily_routes ORDER BY route_name ASC");
        $data['routes'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, name FROM item_categories ORDER BY name ASC");
        $data['categories'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT DISTINCT brand FROM items WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        $data['brands'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT DISTINCT customer_type as name FROM customers WHERE customer_type IS NOT NULL AND customer_type != '' ORDER BY customer_type ASC");
        $data['groups'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, vehicle_number FROM vehicles WHERE status = 'Active' ORDER BY vehicle_number ASC");
        $data['vehicles'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE job_title = 'Driver' AND status = 'Active' ORDER BY first_name ASC");
        $data['drivers'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT DISTINCT partner_name as name FROM deliveries WHERE partner_name IS NOT NULL AND partner_name != '' ORDER BY partner_name ASC");
        $data['partners'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT DISTINCT territory FROM customers WHERE territory IS NOT NULL AND territory != '' ORDER BY territory ASC");
        $data['territories'] = $this->db->resultSet() ?: [];

        $this->db->query("SELECT id, username as name FROM users WHERE role = 'rep' ORDER BY username ASC");
        $data['reps'] = $this->db->resultSet() ?: [];

        return $data;
    }

    public function getQuickViewData($type, $id = null, $number = null) {
        $result = ['success' => false];
        switch ($type) {
            case 'customer':
                if ($id) {
                    $this->db->query("SELECT * FROM customers WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT * FROM customers WHERE name = :name LIMIT 1");
                    $this->db->bind(':name', $number);
                }
                $customer = $this->db->single();
                if (!$customer) {
                    $result['message'] = 'Customer not found.';
                    return $result;
                }

                $this->db->query("
                    SELECT 
                        (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) FROM invoices WHERE customer_id = :cid1 AND status != 'Voided') - 
                        (SELECT COALESCE(SUM(amount), 0) FROM customer_payments WHERE customer_id = :cid2 AND status = 'Active') - 
                        (SELECT COALESCE(SUM(total_amount), 0) FROM credit_notes WHERE customer_id = :cid3)
                        AS outstanding_balance
                ");
                $this->db->bind(':cid1', $customer->id);
                $this->db->bind(':cid2', $customer->id);
                $this->db->bind(':cid3', $customer->id);
                $balanceRow = $this->db->single();
                $outstanding = $balanceRow ? floatval($balanceRow->outstanding_balance) : 0.00;

                $this->db->query("
                    SELECT id, invoice_number, invoice_date, status, 
                           (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_amount 
                    FROM invoices 
                    WHERE customer_id = :cid 
                    ORDER BY invoice_date DESC, id DESC LIMIT 5
                ");
                $this->db->bind(':cid', $customer->id);
                $invoices = $this->db->resultSet() ?: [];

                $this->db->query("
                    SELECT id, payment_date, payment_method, reference, amount, status 
                    FROM customer_payments 
                    WHERE customer_id = :cid AND status = 'Active'
                    ORDER BY payment_date DESC, id DESC LIMIT 5
                ");
                $this->db->bind(':cid', $customer->id);
                $payments = $this->db->resultSet() ?: [];

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'territory' => $customer->territory,
                        'customer_type' => $customer->customer_type ?? 'Standard',
                        'outstanding_balance' => $outstanding
                    ],
                    'invoices' => $invoices,
                    'payments' => $payments
                ];

            case 'product':
                if ($id) {
                    $this->db->query("SELECT * FROM items WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT * FROM items WHERE name = :name OR item_code = :code LIMIT 1");
                    $this->db->bind(':name', $number);
                    $this->db->bind(':code', $number);
                }
                $product = $this->db->single();
                if (!$product) {
                    $result['message'] = 'Product not found.';
                    return $result;
                }

                $this->db->query("
                    SELECT w.name as warehouse_name, COALESCE(SUM(sl.quantity_in - sl.quantity_out), 0) as quantity 
                    FROM stock_ledger sl 
                    JOIN warehouses w ON sl.warehouse_id = w.id 
                    WHERE sl.item_id = :id 
                    GROUP BY w.id, w.name
                ");
                $this->db->bind(':id', $product->id);
                $warehouseStock = $this->db->resultSet() ?: [];

                $this->db->query("
                    SELECT sl.transaction_date as date, sl.reference_number as ref, sl.quantity_out as qty, sl.unit_cost, sl.total_value 
                    FROM stock_ledger sl 
                    WHERE sl.item_id = :id AND sl.quantity_out > 0 
                    ORDER BY sl.transaction_date DESC, sl.id DESC LIMIT 5
                ");
                $this->db->bind(':id', $product->id);
                $recentSales = $this->db->resultSet() ?: [];

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'item_code' => $product->item_code,
                        'brand' => $product->brand ?? 'N/A',
                        'price' => $product->selling_price ?? $product->price ?? 0.00,
                        'cost' => $product->cost ?? $product->cost_price ?? 0.00,
                        'qty_on_hand' => $product->quantity_on_hand
                    ],
                    'stock' => $warehouseStock,
                    'sales' => $recentSales
                ];

            case 'invoice':
                if ($id) {
                    $this->db->query("SELECT i.*, c.name as customer_name FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT i.*, c.name as customer_name FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.invoice_number = :num LIMIT 1");
                    $this->db->bind(':num', $number);
                }
                $invoice = $this->db->single();
                if (!$invoice) {
                    $result['message'] = 'Invoice not found.';
                    return $result;
                }

                $this->db->query("
                    SELECT ii.*, item.name as item_name, item.item_code 
                    FROM invoice_items ii 
                    JOIN items item ON ii.item_id = item.id 
                    WHERE ii.invoice_id = :id
                ");
                $this->db->bind(':id', $invoice->id);
                $items = $this->db->resultSet() ?: [];

                $this->db->query("
                    SELECT pa.amount, cp.payment_date, cp.payment_method, cp.reference 
                    FROM customer_payment_allocations pa 
                    JOIN customer_payments cp ON pa.customer_payment_id = cp.id 
                    WHERE pa.invoice_id = :id AND pa.is_reversed = 0
                ");
                $this->db->bind(':id', $invoice->id);
                $allocations = $this->db->resultSet() ?: [];

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'due_date' => $invoice->due_date,
                        'customer_name' => $invoice->customer_name,
                        'status' => $invoice->status,
                        'total' => $invoice->total_amount,
                        'discount' => $invoice->global_discount_val,
                        'tax' => $invoice->tax_amount,
                        'net_total' => ($invoice->total_amount - ($invoice->global_discount_type === '%' ? ($invoice->total_amount * $invoice->global_discount_val / 100) : $invoice->global_discount_val) + $invoice->tax_amount)
                    ],
                    'items' => $items,
                    'allocations' => $allocations
                ];

            case 'route':
                if ($id) {
                    $this->db->query("SELECT * FROM rep_daily_routes WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT * FROM rep_daily_routes WHERE route_name = :name LIMIT 1");
                    $this->db->bind(':name', $number);
                }
                $route = $this->db->single();
                if (!$route) {
                    $result['message'] = 'Route not found.';
                    return $result;
                }

                $this->db->query("SELECT COUNT(*) as cust_count FROM customers WHERE route_id = :rid OR territory = :route_name");
                $this->db->bind(':rid', $route->id);
                $this->db->bind(':route_name', $route->route_name);
                $custCountRow = $this->db->single();
                $custCount = $custCountRow ? intval($custCountRow->cust_count) : 0;

                $this->db->query("
                    SELECT COUNT(*) as inv_count, COALESCE(SUM(total_amount),0) as total_sales 
                    FROM invoices i 
                    JOIN customers c ON i.customer_id = c.id 
                    WHERE c.route_id = :rid OR c.territory = :route_name
                ");
                $this->db->bind(':rid', $route->id);
                $this->db->bind(':route_name', $route->route_name);
                $invStats = $this->db->single();
                $invCount = $invStats ? intval($invStats->inv_count) : 0;
                $totalSales = $invStats ? floatval($invStats->total_sales) : 0.00;

                $this->db->query("
                    SELECT COALESCE(SUM(cp.amount),0) as total_collections 
                    FROM customer_payments cp 
                    JOIN customers c ON cp.customer_id = c.id 
                    WHERE (c.route_id = :rid OR c.territory = :route_name) AND cp.status = 'Active'
                ");
                $this->db->bind(':rid', $route->id);
                $this->db->bind(':route_name', $route->route_name);
                $collRow = $this->db->single();
                $totalCollections = $collRow ? floatval($collRow->total_collections) : 0.00;

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $route->id,
                        'route_name' => $route->route_name,
                        'description' => $route->description ?? 'N/A',
                        'cust_count' => $custCount,
                        'inv_count' => $invCount,
                        'total_sales' => $totalSales,
                        'total_collections' => $totalCollections,
                        'outstanding' => $totalSales - $totalCollections
                    ]
                ];

            case 'supplier':
                if ($id) {
                    $this->db->query("SELECT * FROM vendors WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT * FROM vendors WHERE name = :name LIMIT 1");
                    $this->db->bind(':name', $number);
                }
                $supplier = $this->db->single();
                if (!$supplier) {
                    $result['message'] = 'Supplier not found.';
                    return $result;
                }

                $this->db->query("
                    SELECT 
                        (SELECT COALESCE(SUM(gri.total), 0) FROM grn_items gri JOIN goods_receipt_notes grn ON gri.grn_id = grn.id WHERE grn.vendor_id = :vid1 AND grn.is_approved = 1) - 
                        (SELECT COALESCE(SUM(amount), 0) FROM supplier_payments WHERE vendor_id = :vid2 AND status = 'Active') - 
                        (SELECT COALESCE(SUM(total_amount), 0) FROM supplier_returns WHERE vendor_id = :vid3) 
                        AS outstanding_balance
                ");
                $this->db->bind(':vid1', $supplier->id);
                $this->db->bind(':vid2', $supplier->id);
                $this->db->bind(':vid3', $supplier->id);
                $balanceRow = $this->db->single();
                $outstanding = $balanceRow ? floatval($balanceRow->outstanding_balance) : 0.00;

                $this->db->query("
                    SELECT grn.id, grn.grn_number, grn.grn_date, COALESCE(SUM(gri.total), 0) as total 
                    FROM goods_receipt_notes grn 
                    LEFT JOIN grn_items gri ON gri.grn_id = grn.id 
                    WHERE grn.vendor_id = :vid AND grn.is_approved = 1 
                    GROUP BY grn.id, grn.grn_number, grn.grn_date 
                    ORDER BY grn.grn_date DESC LIMIT 5
                ");
                $this->db->bind(':vid', $supplier->id);
                $grns = $this->db->resultSet() ?: [];

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                        'address' => $supplier->address,
                        'outstanding_balance' => $outstanding
                    ],
                    'grns' => $grns
                ];

            case 'po':
                if ($id) {
                    $this->db->query("SELECT p.*, v.name as supplier_name FROM purchase_orders p JOIN vendors v ON p.vendor_id = v.id WHERE p.id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT p.*, v.name as supplier_name FROM purchase_orders p JOIN vendors v ON p.vendor_id = v.id WHERE p.po_number = :num LIMIT 1");
                    $this->db->bind(':num', $number);
                }
                $po = $this->db->single();
                if (!$po) {
                    $result['message'] = 'PO not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $po->id,
                        'po_number' => $po->po_number,
                        'po_date' => $po->po_date ?? $po->created_at,
                        'supplier_name' => $po->supplier_name,
                        'status' => $po->status ?? 'Pending',
                        'total' => $po->total_amount ?? 0.00
                    ]
                ];

            case 'grn':
                if ($id) {
                    $this->db->query("SELECT g.*, v.name as supplier_name FROM goods_receipt_notes g JOIN vendors v ON g.vendor_id = v.id WHERE g.id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT g.*, v.name as supplier_name FROM goods_receipt_notes g JOIN vendors v ON g.vendor_id = v.id WHERE g.grn_number = :num LIMIT 1");
                    $this->db->bind(':num', $number);
                }
                $grn = $this->db->single();
                if (!$grn) {
                    $result['message'] = 'GRN not found.';
                    return $result;
                }
                $this->db->query("SELECT COALESCE(SUM(total), 0) as total FROM grn_items WHERE grn_id = :id");
                $this->db->bind(':id', $grn->id);
                $totRow = $this->db->single();
                $total = $totRow ? floatval($totRow->total) : 0.00;

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $grn->id,
                        'grn_number' => $grn->grn_number,
                        'grn_date' => $grn->grn_date,
                        'supplier_name' => $grn->supplier_name,
                        'is_approved' => $grn->is_approved,
                        'total' => $total
                    ]
                ];

            case 'payment':
                if ($id) {
                    $this->db->query("SELECT p.*, c.name as customer_name, ch.cheque_number, ch.bank_name as cheque_bank, ch.banking_date as cheque_date FROM customer_payments p JOIN customers c ON p.customer_id = c.id LEFT JOIN cheques ch ON ch.customer_id = p.customer_id AND ch.amount = p.amount AND ABS(TIMESTAMPDIFF(SECOND, ch.created_at, p.created_at)) < 60 WHERE p.id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $cleanRef = $number;
                    if (strpos($cleanRef, 'Pay: ') === 0) {
                        $cleanRef = substr($cleanRef, 5);
                    }
                    if (preg_match('/\((.*?)\)/', $cleanRef, $matches)) {
                        $cleanRef = $matches[1];
                    }
                    $this->db->query("SELECT p.*, c.name as customer_name, ch.cheque_number, ch.bank_name as cheque_bank, ch.banking_date as cheque_date FROM customer_payments p JOIN customers c ON p.customer_id = c.id LEFT JOIN cheques ch ON ch.customer_id = p.customer_id AND ch.amount = p.amount AND ABS(TIMESTAMPDIFF(SECOND, ch.created_at, p.created_at)) < 60 WHERE p.reference = :ref OR p.id = :refId LIMIT 1");
                    $this->db->bind(':ref', $cleanRef);
                    $this->db->bind(':refId', intval($cleanRef));
                }
                $payment = $this->db->single();
                if (!$payment && is_numeric($number)) {
                    $this->db->query("SELECT p.*, c.name as customer_name, ch.cheque_number, ch.bank_name as cheque_bank, ch.banking_date as cheque_date FROM customer_payments p JOIN customers c ON p.customer_id = c.id LEFT JOIN cheques ch ON ch.customer_id = p.customer_id AND ch.amount = p.amount AND ABS(TIMESTAMPDIFF(SECOND, ch.created_at, p.created_at)) < 60 WHERE p.id = :id LIMIT 1");
                    $this->db->bind(':id', intval($number));
                    $payment = $this->db->single();
                }
                if (!$payment) {
                    $result['message'] = 'Payment not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $payment->id,
                        'reference' => $payment->reference,
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'amount' => $payment->amount,
                        'customer_name' => $payment->customer_name,
                        'status' => $payment->status,
                        'notes' => $payment->notes ?? null,
                        'cheque_number' => $payment->cheque_number,
                        'cheque_bank' => $payment->cheque_bank,
                        'cheque_date' => $payment->cheque_date
                    ]
                ];

            case 'supplier_payment':
                if ($id) {
                    $this->db->query("SELECT p.*, v.name as supplier_name, v.email as supplier_email, v.phone as supplier_phone, v.address as supplier_address FROM supplier_payments p JOIN vendors v ON p.vendor_id = v.id WHERE p.id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $cleanRef = $number;
                    if (strpos($cleanRef, 'Pay: ') === 0) {
                        $cleanRef = substr($cleanRef, 5);
                    }
                    if (preg_match('/\((.*?)\)/', $cleanRef, $matches)) {
                        $cleanRef = $matches[1];
                    }
                    $this->db->query("SELECT p.*, v.name as supplier_name, v.email as supplier_email, v.phone as supplier_phone, v.address as supplier_address FROM supplier_payments p JOIN vendors v ON p.vendor_id = v.id WHERE p.reference = :ref OR p.id = :refId LIMIT 1");
                    $this->db->bind(':ref', $cleanRef);
                    $this->db->bind(':refId', intval($cleanRef));
                }
                $payment = $this->db->single();
                if (!$payment && is_numeric($number)) {
                    $this->db->query("SELECT p.*, v.name as supplier_name, v.email as supplier_email, v.phone as supplier_phone, v.address as supplier_address FROM supplier_payments p JOIN vendors v ON p.vendor_id = v.id WHERE p.id = :id LIMIT 1");
                    $this->db->bind(':id', intval($number));
                    $payment = $this->db->single();
                }
                if (!$payment) {
                    $result['message'] = 'Payment not found.';
                    return $result;
                }
                
                $cheque = null;
                if ($payment->payment_method === 'Cheque') {
                    $this->db->query("SELECT * FROM cheques WHERE vendor_id = :vid AND amount = :amt ORDER BY id DESC LIMIT 1");
                    $this->db->bind(':vid', $payment->vendor_id);
                    $this->db->bind(':amt', $payment->amount);
                    $cheque = $this->db->single();
                }

                return [
                    'success' => true,
                    'entity' => [
                        'id' => $payment->id,
                        'reference' => $payment->reference,
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'amount' => $payment->amount,
                        'supplier_name' => $payment->supplier_name,
                        'status' => $payment->status,
                        'notes' => $payment->notes,
                        'cheque_number' => $cheque ? $cheque->cheque_number : null,
                        'cheque_bank' => $cheque ? $cheque->bank_name : null,
                        'cheque_date' => $cheque ? $cheque->banking_date : null
                    ]
                ];

            case 'cheque':
                if ($id) {
                    $this->db->query("SELECT ch.*, c.name as customer_name FROM cheques ch LEFT JOIN customers c ON ch.customer_id = c.id WHERE ch.id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT ch.*, c.name as customer_name FROM cheques ch LEFT JOIN customers c ON ch.customer_id = c.id WHERE ch.cheque_number = :num LIMIT 1");
                    $this->db->bind(':num', $number);
                }
                $cheque = $this->db->single();
                if (!$cheque) {
                    $result['message'] = 'Cheque not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $cheque->id,
                        'cheque_number' => $cheque->cheque_number,
                        'bank_name' => $cheque->bank_name,
                        'amount' => $cheque->amount,
                        'banking_date' => $cheque->banking_date,
                        'customer_name' => $cheque->customer_name ?? 'N/A',
                        'status' => $cheque->status
                    ]
                ];

            case 'driver':
                if ($id) {
                    $this->db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, email, phone FROM employees WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, email, phone FROM employees WHERE CONCAT(first_name, ' ', last_name) = :name LIMIT 1");
                    $this->db->bind(':name', $number);
                }
                $driver = $this->db->single();
                if (!$driver) {
                    $result['message'] = 'Driver not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'email' => $driver->email ?? 'N/A',
                        'phone' => $driver->phone ?? 'N/A',
                        'role' => 'Driver'
                    ]
                ];

            case 'vehicle':
                if ($id) {
                    $this->db->query("SELECT * FROM vehicles WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT * FROM vehicles WHERE vehicle_number = :num LIMIT 1");
                    $this->db->bind(':num', $number);
                }
                $vehicle = $this->db->single();
                if (!$vehicle) {
                    $result['message'] = 'Vehicle not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $vehicle->id,
                        'vehicle_number' => $vehicle->vehicle_number,
                        'vehicle_type' => $vehicle->vehicle_type ?? 'N/A',
                        'status' => $vehicle->status
                    ]
                ];

            case 'rep':
                if ($id) {
                    $this->db->query("SELECT id, username, email FROM users WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT id, username, email FROM users WHERE username = :name LIMIT 1");
                    $this->db->bind(':name', $number);
                }
                $rep = $this->db->single();
                if (!$rep) {
                    $result['message'] = 'Sales Rep not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $rep->id,
                        'name' => $rep->username,
                        'email' => $rep->email ?? 'N/A',
                        'role' => 'Sales Representative'
                    ]
                ];

            case 'warehouse':
                if ($id) {
                    $this->db->query("SELECT * FROM warehouses WHERE id = :id");
                    $this->db->bind(':id', $id);
                } else {
                    $this->db->query("SELECT * FROM warehouses WHERE name = :name LIMIT 1");
                    $this->db->bind(':name', $number);
                }
                $warehouse = $this->db->single();
                if (!$warehouse) {
                    $result['message'] = 'Warehouse not found.';
                    return $result;
                }
                return [
                    'success' => true,
                    'entity' => [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'code' => $warehouse->code ?? 'N/A',
                        'address' => $warehouse->address ?? 'N/A'
                    ]
                ];
        }
        return $result;
    }
}
