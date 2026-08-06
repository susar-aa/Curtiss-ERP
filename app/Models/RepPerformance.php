<?php
declare(strict_types=1);

class RepPerformance {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all KPI configuration thresholds and weights
     */
    public function getKpiConfigs(): array {
        $this->db->query("SELECT * FROM rep_kpi_configs ORDER BY id ASC");
        return $this->db->resultSet() ?: [];
    }

    /**
     * Update weights, targets, and min/max score constraints
     */
    public function updateKpiConfigs(array $configs): bool {
        try {
            $this->db->beginTransaction();
            foreach ($configs as $kpiKey => $cfg) {
                $this->db->query("UPDATE rep_kpi_configs 
                                  SET weight = :weight, target_value = :target, min_score = :min_s, max_score = :max_s
                                  WHERE kpi_key = :kkey");
                $this->db->bind(':weight', floatval($cfg['weight'] ?? 0));
                $this->db->bind(':target', floatval($cfg['target_value'] ?? 0));
                $this->db->bind(':min_s', intval($cfg['min_score'] ?? 0));
                $this->db->bind(':max_s', intval($cfg['max_score'] ?? 100));
                $this->db->bind(':kkey', $kpiKey);
                $this->db->execute();
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Calculate all KPIs, scoring, and aggregates for a Sales Representative in a given period
     */
    public function calculatePerformance(
        int $repUserId, 
        string $startDate, 
        string $endDate, 
        ?int $routeId = null, 
        ?int $areaId = null
    ): array {
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));

        $month = date('m', strtotime($startDate));
        $year = date('Y', strtotime($startDate));
        $repTargets = $this->getRepTargets($repUserId, $month, $year);

        // 1. Resolve Rep Route IDs in period
        $routeQueryStr = "SELECT id, status FROM rep_daily_routes WHERE user_id = :uid AND DATE(start_time) BETWEEN :start AND :end";
        if ($routeId) {
            $routeQueryStr = "SELECT id, status FROM rep_daily_routes WHERE id = :route_id";
        }
        
        $this->db->query($routeQueryStr);
        $this->db->bind(':uid', $repUserId);
        if ($routeId) {
            $this->db->bind(':route_id', $routeId);
        } else {
            $this->db->bind(':start', $startDate);
            $this->db->bind(':end', $endDate);
        }
        $routes = $this->db->resultSet() ?: [];
        $routeIds = array_map(fn($r) => intval($r->id), $routes);
        
        // Fetch detailed routes listing
        $routesDetailSql = "SELECT * FROM rep_daily_routes WHERE user_id = :uid AND DATE(start_time) BETWEEN :start AND :end ORDER BY start_time DESC";
        if ($routeId) {
            $routesDetailSql = "SELECT * FROM rep_daily_routes WHERE id = :route_id";
        }
        $this->db->query($routesDetailSql);
        $this->db->bind(':uid', $repUserId);
        if ($routeId) {
            $this->db->bind(':route_id', $routeId);
        } else {
            $this->db->bind(':start', $startDate);
            $this->db->bind(':end', $endDate);
        }
        $routesDetail = $this->db->resultSet() ?: [];
        
        if (empty($routeIds)) {
            $routeIds = [0]; // avoid SQL error in empty list
        }

        $routeIdsPlaceholder = implode(',', $routeIds);

        // 2. Fetch Sales Invoices Grand Total Expression helper
        $invoiceTotalExpr = "SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0))";

        foreach ($routesDetail as $r) {
            $rId = intval($r->id);
            $this->db->query("SELECT {$invoiceTotalExpr} as route_sales FROM invoices i WHERE i.rep_route_id = :rid AND i.status != 'Voided'");
            $this->db->bind(':rid', $rId);
            $rsRow = $this->db->single();
            $r->sales = floatval($rsRow->route_sales ?? 0);
        }

        // KPI: Total Sales & Net Sales
        $salesSql = "SELECT {$invoiceTotalExpr} as total_sales, COUNT(i.id) as invoice_count 
                     FROM invoices i
                     LEFT JOIN customers c ON i.customer_id = c.id
                     WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                       AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $salesSql .= " AND c.mca_id = :area_id";
        }

        $this->db->query($salesSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $salesRow = $this->db->single();
        $totalSales = floatval($salesRow->total_sales ?? 0.00);
        $invoiceCount = intval($salesRow->invoice_count ?? 0);

        // Fetch Credit Notes (Returns) linked to these invoices
        $cnSql = "SELECT SUM(cn.total_amount) as total_returns 
                  FROM credit_notes cn
                  JOIN invoices i ON cn.invoice_id = i.id
                  LEFT JOIN customers c ON i.customer_id = c.id
                  WHERE i.rep_route_id IN ($routeIdsPlaceholder) 
                    AND cn.status IN ('Issued', 'Applied')
                    AND cn.note_date BETWEEN :start AND :end";
        if ($areaId) {
            $cnSql .= " AND c.mca_id = :area_id";
        }
        $this->db->query($cnSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $cnRow = $this->db->single();
        $totalReturns = floatval($cnRow->total_returns ?? 0.00);
        $netSales = $totalSales - $totalReturns;

        $avgInvoiceValue = $invoiceCount > 0 ? $netSales / $invoiceCount : 0.00;

        // KPI: Customer Visit coverage (Productive vs Unproductive)
        $prodVisitsSql = "SELECT COUNT(DISTINCT i.customer_id) as active_customers 
                          FROM invoices i
                          LEFT JOIN customers c ON i.customer_id = c.id
                          WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                            AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $prodVisitsSql .= " AND c.mca_id = :area_id";
        }
        $this->db->query($prodVisitsSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $prodVisitsRow = $this->db->single();
        // User definition: Productive Visits means total bills billed.
        $productiveVisits = $invoiceCount;

        $unprodVisitsSql = "SELECT COUNT(*) as unprod_count 
                            FROM unproductive_visits uv
                            LEFT JOIN customers c ON uv.customer_id = c.id
                            WHERE uv.rep_route_id IN ($routeIdsPlaceholder)
                              AND DATE(uv.visit_time) BETWEEN :start AND :end";
        if ($areaId) {
            $unprodVisitsSql .= " AND c.mca_id = :area_id";
        }
        $this->db->query($unprodVisitsSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $unprodVisitsRow = $this->db->single();
        $unproductiveVisits = intval($unprodVisitsRow->unprod_count ?? 0);

        $totalVisits = $productiveVisits + $unproductiveVisits;

        // Repeat customers count (customers with > 1 invoice in period)
        $repeatSql = "SELECT COUNT(*) as repeat_count FROM (
                          SELECT i.customer_id, COUNT(*) as cnt 
                          FROM invoices i
                          LEFT JOIN customers c ON i.customer_id = c.id
                          WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                            AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $repeatSql .= " AND c.mca_id = :area_id";
        }
        $repeatSql .= " GROUP BY i.customer_id HAVING cnt > 1) r";
        
        $this->db->query($repeatSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $repeatRow = $this->db->single();
        $repeatCustomers = intval($repeatRow->repeat_count ?? 0);

        // New Customers acquired
        $newCustSql = "SELECT COUNT(*) as new_count FROM customers WHERE created_by_user_id = :uid AND DATE(created_at) BETWEEN :start AND :end";
        if ($areaId) {
            $newCustSql .= " AND mca_id = :area_id";
        }
        $this->db->query($newCustSql);
        $this->db->bind(':uid', $repUserId);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $newCustRow = $this->db->single();
        $newCustomers = intval($newCustRow->new_count ?? 0);

        // Rates
        $productiveVisitRate = $totalVisits > 0 ? ($productiveVisits / $totalVisits) * 100 : 0.00;
        $customerConversionRate = $totalVisits > 0 ? ($productiveVisits / $totalVisits) * 100 : 0.00;

        // Route Performance
        $totalRoutes = count($routes);
        $completedRoutes = count(array_filter($routes, fn($r) => $r->status === 'Completed' || $r->status === 'Finalized'));
        $routeCompletionRate = $totalRoutes > 0 ? ($completedRoutes / $totalRoutes) * 100 : 0.00;

        $avgSalesPerRoute = $totalRoutes > 0 ? $netSales / $totalRoutes : 0.00;
        $avgCustomersPerRoute = $totalRoutes > 0 ? $productiveVisits / $totalRoutes : 0.00;
        $avgSalesPerVisit = $totalVisits > 0 ? $netSales / $totalVisits : 0.00;

        // Collections
        $collectionsSql = "SELECT payment_method, SUM(amount) as total_collected 
                           FROM customer_payments 
                           WHERE rep_route_id IN ($routeIdsPlaceholder) AND status != 'Reversed'
                             AND payment_date BETWEEN :start AND :end
                           GROUP BY payment_method";
        $this->db->query($collectionsSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $collectionRows = $this->db->resultSet() ?: [];

        $cashCollections = 0.00;
        $chequeCollections = 0.00;
        $bankCollections = 0.00;
        $totalCollections = 0.00;

        foreach ($collectionRows as $col) {
            $amt = floatval($col->total_collected);
            $totalCollections += $amt;
            if ($col->payment_method === 'Cash') {
                $cashCollections = $amt;
            } elseif ($col->payment_method === 'Cheque') {
                $chequeCollections = $amt;
            } elseif ($col->payment_method === 'Bank Transfer') {
                $bankCollections = $amt;
            }
        }

        $collectionEfficiency = $totalSales > 0 ? ($totalCollections / $totalSales) * 100 : 0.00;

        // Outstanding Receivables for Rep's Customers (unpaid/partially paid)
        $outstandingSql = "SELECT i.id,
                                  (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as bill_amt,
                                  COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE invoice_id = i.id AND is_reversed = 0), 0) as paid_amt
                           FROM invoices i
                           LEFT JOIN customers c ON i.customer_id = c.id
                           WHERE i.rep_route_id IN ($routeIdsPlaceholder) 
                             AND i.status != 'Voided' 
                             AND i.status != 'Paid'";
        if ($areaId) {
            $outstandingSql .= " AND c.mca_id = :area_id";
        }
        $this->db->query($outstandingSql);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $outstandingRows = $this->db->resultSet() ?: [];
        $totalOutstanding = 0.00;
        foreach ($outstandingRows as $row) {
            $totalOutstanding += max(0.00, floatval($row->bill_amt) - floatval($row->paid_amt));
        }
        $outstandingAmount = $totalOutstanding;

        // Expense & Profitability
        $expensesSql = "SELECT expense_type, SUM(amount) as exp_sum 
                        FROM route_expenses 
                        WHERE rep_route_id IN ($routeIdsPlaceholder)
                          AND DATE(expense_date) BETWEEN :start AND :end
                        GROUP BY expense_type";
        $this->db->query($expensesSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $expenseRows = $this->db->resultSet() ?: [];

        $totalExpenses = 0.00;
        $fuelExpenses = 0.00;
        foreach ($expenseRows as $exp) {
            $amt = floatval($exp->exp_sum);
            $totalExpenses += $amt;
            if (strtolower($exp->expense_type) === 'fuel') {
                $fuelExpenses = $amt;
            }
        }

        $otherExpenses = $totalExpenses - $fuelExpenses;
        $expensePerRoute = $totalRoutes > 0 ? $totalExpenses / $totalRoutes : 0.00;
        $netSalesAfterExpenses = $netSales - $totalExpenses;
        $salesToExpenseRatio = $netSales > 0 ? ($totalExpenses / $netSales) * 100 : 0.00;

        // Productivity
        $routeDaysSql = "SELECT COUNT(DISTINCT DATE(start_time)) as active_days 
                         FROM rep_daily_routes 
                         WHERE user_id = :uid AND DATE(start_time) BETWEEN :start AND :end";
        $this->db->query($routeDaysSql);
        $this->db->bind(':uid', $repUserId);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $routeDaysRow = $this->db->single();
        $activeRouteDays = intval($routeDaysRow->active_days ?? 0);

        // Working Days (defined as route count on month by user request)
        $workingDays = $activeRouteDays;

        $avgDailySales = $activeRouteDays > 0 ? $netSales / $activeRouteDays : 0.00;
        $avgDailyVisits = $activeRouteDays > 0 ? $totalVisits / $activeRouteDays : 0.00;
        $avgDailyCollections = $activeRouteDays > 0 ? $totalCollections / $activeRouteDays : 0.00;
        $salesPerProductiveVisit = $productiveVisits > 0 ? $netSales / $productiveVisits : 0.00;
        $salesPerCustomer = $productiveVisits > 0 ? $netSales / $productiveVisits : 0.00;

        // Top Categories
        $topCategoriesSql = "SELECT c.name as category_name, SUM(ii.total) as total_sales 
                             FROM invoice_items ii
                             JOIN invoices i ON ii.invoice_id = i.id
                             JOIN items it ON ii.item_id = it.id
                             JOIN item_categories c ON it.category_id = c.id
                             LEFT JOIN customers cust ON i.customer_id = cust.id
                             WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                               AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $topCategoriesSql .= " AND cust.mca_id = :area_id";
        }
        $topCategoriesSql .= " GROUP BY c.id ORDER BY total_sales DESC LIMIT 5";
        $this->db->query($topCategoriesSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $topCategories = $this->db->resultSet() ?: [];

        // Top Products
        $topProductsSql = "SELECT it.name as product_name, SUM(ii.quantity) as qty, SUM(ii.total) as total_sales 
                           FROM invoice_items ii
                           JOIN invoices i ON ii.invoice_id = i.id
                           JOIN items it ON ii.item_id = it.id
                           LEFT JOIN customers cust ON i.customer_id = cust.id
                           WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                             AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $topProductsSql .= " AND cust.mca_id = :area_id";
        }
        $topProductsSql .= " GROUP BY it.id ORDER BY total_sales DESC LIMIT 5";
        $this->db->query($topProductsSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $topProducts = $this->db->resultSet() ?: [];

        // Top Customers
        $topCustomersSql = "SELECT c.name as customer_name, COUNT(i.id) as bills, SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as total_sales 
                             FROM invoices i
                             JOIN customers c ON i.customer_id = c.id
                             WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                               AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $topCustomersSql .= " AND c.mca_id = :area_id";
        }
        $topCustomersSql .= " GROUP BY c.id ORDER BY total_sales DESC LIMIT 5";
        $this->db->query($topCustomersSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $topCustomers = $this->db->resultSet() ?: [];

        // Trends data (weekly or daily breakdown for charts)
        $salesTrendSql = "SELECT i.invoice_date as label, SUM(i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as sales_amount 
                          FROM invoices i
                          LEFT JOIN customers c ON i.customer_id = c.id
                          WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                            AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $salesTrendSql .= " AND c.mca_id = :area_id";
        }
        $salesTrendSql .= " GROUP BY i.invoice_date ORDER BY i.invoice_date ASC";
        $this->db->query($salesTrendSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $salesTrend = $this->db->resultSet() ?: [];

        $collTrendSql = "SELECT cp.payment_date as label, SUM(cp.amount) as col_amount 
                         FROM customer_payments cp
                         LEFT JOIN rep_daily_routes r ON cp.rep_route_id = r.id
                         WHERE cp.rep_route_id IN ($routeIdsPlaceholder) AND cp.status != 'Reversed'
                           AND cp.payment_date BETWEEN :start AND :end
                         GROUP BY cp.payment_date ORDER BY cp.payment_date ASC";
        $this->db->query($collTrendSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $collTrend = $this->db->resultSet() ?: [];

        // Recent Transactions
        $recentSalesSql = "SELECT i.*, c.name as customer_name,
                                  (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as true_amount
                           FROM invoices i
                           JOIN customers c ON i.customer_id = c.id
                           WHERE i.rep_route_id IN ($routeIdsPlaceholder) AND i.status != 'Voided'
                             AND i.invoice_date BETWEEN :start AND :end";
        if ($areaId) {
            $recentSalesSql .= " AND c.mca_id = :area_id";
        }
        $recentSalesSql .= " ORDER BY i.invoice_date DESC, i.id DESC LIMIT 10";
        $this->db->query($recentSalesSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $recentSales = $this->db->resultSet() ?: [];

        $recentCollectionsSql = "SELECT cp.*, c.name as customer_name
                                 FROM customer_payments cp
                                 JOIN customers c ON cp.customer_id = c.id
                                 WHERE cp.rep_route_id IN ($routeIdsPlaceholder) AND cp.status != 'Reversed'
                                   AND cp.payment_date BETWEEN :start AND :end";
        if ($areaId) {
            $recentCollectionsSql .= " AND c.mca_id = :area_id";
        }
        $recentCollectionsSql .= " ORDER BY cp.payment_date DESC, cp.id DESC LIMIT 10";
        $this->db->query($recentCollectionsSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $recentCollections = $this->db->resultSet() ?: [];

        $recentUnprodSql = "SELECT uv.*, c.name as customer_name
                            FROM unproductive_visits uv
                            JOIN customers c ON uv.customer_id = c.id
                            WHERE uv.rep_route_id IN ($routeIdsPlaceholder)
                              AND DATE(uv.visit_time) BETWEEN :start AND :end";
        if ($areaId) {
            $recentUnprodSql .= " AND c.mca_id = :area_id";
        }
        $recentUnprodSql .= " ORDER BY uv.visit_time DESC LIMIT 10";
        $this->db->query($recentUnprodSql);
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        if ($areaId) {
            $this->db->bind(':area_id', $areaId);
        }
        $recentUnprod = $this->db->resultSet() ?: [];

        // 3. Score Calculation based on configurator
        $kpiConfigs = $this->getKpiConfigs();
        $totalWeight = 0.00;
        $achievedWeightedScore = 0.00;
        
        $kpiScores = [];
        foreach ($kpiConfigs as $cfg) {
            $weight = floatval($cfg->weight);
            $target = floatval($cfg->target_value);
            $minScore = intval($cfg->min_score);
            $maxScore = intval($cfg->max_score);
            
            // Map keys to calculations (prioritizing rep-wise targets)
            $actual = 0.00;
            if ($cfg->kpi_key === 'sales_amount') {
                $target = floatval($repTargets->sales_target);
                $actual = $netSales;
            } elseif ($cfg->kpi_key === 'productive_visit_rate') {
                $target = floatval($repTargets->productive_visits_target);
                $actual = floatval($productiveVisits);
            } elseif ($cfg->kpi_key === 'total_visits') {
                $target = floatval($repTargets->total_visits_target);
                $actual = floatval($totalVisits);
            } elseif ($cfg->kpi_key === 'collection_efficiency') {
                $target = floatval($repTargets->collection_efficiency_target ?? 80.00);
                $actual = $collectionEfficiency;
            } elseif ($cfg->kpi_key === 'new_customers') {
                $target = floatval($repTargets->new_customers_target ?? 5);
                $actual = floatval($newCustomers);
            } elseif ($cfg->kpi_key === 'route_completion') {
                $target = floatval($repTargets->working_days_target);
                $actual = floatval($activeRouteDays);
            }

            // Automated Scoring logic (simplified, 0-100 max)
            $rawScore = 0;
            if ($target > 0) {
                $rawScore = ($actual / $target) * 100;
            } elseif ($actual > 0) {
                $rawScore = 100; // Over-achieved 0 target
            }

            // Cap the score at 100% implicitly
            $clampedScore = max(0, min(100, $rawScore));
            
            $weightedContrib = 0;
            if ($weight > 0) {
                $weightedContrib = ($clampedScore / 100) * $weight;
            }

            $kpiScores[$cfg->kpi_key] = [
                'kpi_key' => $cfg->kpi_key,
                'name' => $cfg->kpi_name,
                'weight' => $weight,
                'target' => $target,
                'actual' => $actual,
                'achievement_pct' => $clampedScore, // Renamed back for view compatibility
                'contribution' => $weightedContrib
            ];

            if ($weight > 0) {
                $totalWeight += $weight;
                $achievedWeightedScore += $weightedContrib;
            }
        }

        $overallPerformanceScore = $totalWeight > 0 ? ($achievedWeightedScore / ($totalWeight / 100)) : 0.00;

        // ---- PAYROLL & COMMISSIONS ENGINE ----
        $this->db->query("SELECT e.base_salary FROM users u LEFT JOIN employees e ON u.employee_id = e.id WHERE u.id = :uid");
        $this->db->bind(':uid', $repUserId);
        $empRow = $this->db->single();
        $baseSalary = $empRow ? floatval($empRow->base_salary) : 0.00;

        $this->db->query("SELECT * FROM company_settings LIMIT 1");
        $compSet = $this->db->single();
        if (!$compSet) {
            $compSet = (object)[
                'sales_commission_pct' => 0,
                'sales_incentive_min_value' => 0,
                'sales_incentive_pct' => 0,
                'sales_incentive_max_limit' => 0,
                'productive_visits_payout' => 0,
                'working_days_payout' => 0,
                'collection_efficiency_payout' => 0
            ];
        }

        $salesCommission = ($totalCollections * floatval($compSet->sales_commission_pct ?? 0)) / 100;
        
        $salesIncentive = 0;
        $minSalesVal = floatval($compSet->sales_incentive_min_value ?? 0);
        if ($netSales >= $minSalesVal && $minSalesVal > 0) {
            $salesIncentive = ($netSales * floatval($compSet->sales_incentive_pct ?? 0)) / 100;
            $maxLimit = floatval($compSet->sales_incentive_max_limit ?? 0);
            if ($salesIncentive > $maxLimit && $maxLimit > 0) {
                $salesIncentive = $maxLimit;
            }
        }
        
        $prodVisitsBonus = 0;
        $targetVisits = floatval($repTargets->productive_visits_target ?? 0);
        if ($productiveVisits >= $targetVisits && $targetVisits > 0) {
            $prodVisitsBonus = floatval($compSet->productive_visits_payout ?? 0);
        }

        $workDaysBonus = 0;
        $targetDays = floatval($repTargets->working_days_target ?? 0);
        if ($activeRouteDays >= $targetDays && $targetDays > 0) {
            $workDaysBonus = floatval($compSet->working_days_payout ?? 0);
        }

        $collBonus = 0;
        $collTarget = floatval($repTargets->collection_efficiency_target ?? 80.00);
        
        if ($collectionEfficiency >= $collTarget && $collTarget > 0) {
            $collBonus = floatval($compSet->collection_efficiency_payout ?? 0);
        }

        $salesTarget = floatval($repTargets->sales_target ?? 0);
        $salesNeeded = ($salesTarget > 0 && $netSales < $salesTarget) ? ($salesTarget - $netSales) : 0.00;
        $targetDays = floatval($repTargets->working_days_target ?? 0);
        $remainingDays = $targetDays - $activeRouteDays;
        $avgSalesNeededPerDay = ($salesNeeded > 0 && $remainingDays > 0) ? ($salesNeeded / $remainingDays) : ($salesNeeded > 0 ? $salesNeeded : 0.00);
        $collTargetPct = floatval($repTargets->collection_efficiency_target ?? 80.00);
        $targetCollectionAmount = ($outstandingAmount * $collTargetPct) / 100.0;
        $collectionsNeeded = ($outstandingAmount > 0 && $totalCollections < $targetCollectionAmount) ? ($targetCollectionAmount - $totalCollections) : 0.00;

        $totalEarnings = $baseSalary + $salesCommission + $salesIncentive + $prodVisitsBonus + $workDaysBonus + $collBonus;

        $payrollData = [
            'base_salary' => $baseSalary,
            'sales_commission' => $salesCommission,
            'sales_incentive' => $salesIncentive,
            'productive_visits_bonus' => $prodVisitsBonus,
            'working_days_bonus' => $workDaysBonus,
            'collection_bonus' => $collBonus,
            'total_earnings' => $totalEarnings,
            'settings' => $compSet
        ];

        return [
            // Target Requirement Metrics
            'sales_target' => $salesTarget,
            'sales_needed_for_target' => $salesNeeded,
            'working_days_target' => $targetDays,
            'remaining_working_days' => $remainingDays,
            'avg_sales_needed_per_day' => $avgSalesNeededPerDay,
            'collection_target_pct' => $collTargetPct,
            'target_collection_amount' => $targetCollectionAmount,
            'collections_needed_for_target' => $collectionsNeeded,
            // Totals
            'total_sales' => $totalSales,
            'total_returns' => $totalReturns,
            'net_sales' => $netSales,
            'invoice_count' => $invoiceCount,
            'avg_invoice_value' => $avgInvoiceValue,
            // Visits
            'total_visited' => $totalVisits,
            'productive_visits' => $productiveVisits,
            'unproductive_visits' => $unproductiveVisits,
            'new_customers_added' => $newCustomers,
            'active_customers' => $productiveVisits,
            'repeat_customers' => $repeatCustomers,
            'productive_visit_rate' => $productiveVisitRate,
            'customer_conversion_rate' => $customerConversionRate,
            // Routes
            'total_routes' => $totalRoutes,
            'completed_routes' => $completedRoutes,
            'route_completion_rate' => $routeCompletionRate,
            'avg_sales_per_route' => $avgSalesPerRoute,
            'avg_customers_per_route' => $avgCustomersPerRoute,
            'avg_sales_per_visit' => $avgSalesPerVisit,
            // Collections
            'total_collections' => $totalCollections,
            'cash_collections' => $cashCollections,
            'cheque_collections' => $chequeCollections,
            'bank_collections' => $bankCollections,
            'collection_efficiency' => $collectionEfficiency,
            'outstanding_amount' => $outstandingAmount,
            'total_outstanding' => $outstandingAmount,
            'credit_limit' => floatval($repTargets->credit_limit),
            // Expenses
            'total_expenses' => $totalExpenses,
            'fuel_expenses' => $fuelExpenses,
            'other_expenses' => $otherExpenses,
            'expense_per_route' => $expensePerRoute,
            'net_sales_after_expenses' => $netSalesAfterExpenses,
            'sales_to_expense_ratio' => $salesToExpenseRatio,
            // Productivity
            'working_days' => $workingDays,
            'active_route_days' => $activeRouteDays,
            'avg_daily_sales' => $avgDailySales,
            'avg_daily_visits' => $avgDailyVisits,
            'avg_daily_collections' => $avgDailyCollections,
            'sales_per_productive_visit' => $salesPerProductiveVisit,
            'sales_per_customer' => $salesPerCustomer,
            // Tables
            'top_categories' => $topCategories,
            'top_products' => $topProducts,
            'top_customers' => $topCustomers,
            'sales_trend' => $salesTrend,
            'collections_trend' => $collTrend,
            // Recent Grid Data
            'recent_sales' => $recentSales,
            'recent_collections' => $recentCollections,
            'recent_unprod' => $recentUnprod,
            // Scoring
            'kpi_scores' => $kpiScores,
            'routes_detail' => $routesDetail,
            'overall_score' => $overallPerformanceScore,
            'payroll' => $payrollData,
            'targets' => $repTargets
        ];
    }

    /**
     * Fetch targets for a specific representative for a given month and year.
     * Defaults to 0 if not set.
     */
    public function getRepTargets(int $userId, string $month, string $year): object {
        if ($userId > 0) {
            $this->db->query("SELECT * FROM rep_targets WHERE user_id = :uid AND month = :m AND year = :y LIMIT 1");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':m', $month);
            $this->db->bind(':y', $year);
            $row = $this->db->single();
            if ($row) {
                return $row;
            }
        }
        $this->db->query("SELECT * FROM rep_targets WHERE user_id = 0 AND month = '00' AND year = '0000' LIMIT 1");
        $row = $this->db->single();
        if ($row) {
            return $row;
        }
        
        // Return a default object with 0 values
        return (object)[
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
            'sales_target' => 0.00,
            'productive_visits_target' => 0,
            'total_visits_target' => 0,
            'working_days_target' => 0,
            'collection_efficiency_target' => 80.00,
            'new_customers_target' => 5,
            'credit_limit' => 0.00
        ];
    }

    /**
     * Save/update targets for a representative.
     */
    public function saveRepTargets(array $data): bool {
        $this->db->query("
            INSERT INTO rep_targets (user_id, month, year, sales_target, productive_visits_target, total_visits_target, working_days_target, collection_efficiency_target, new_customers_target, credit_limit)
            VALUES (:uid, :m, :y, :sales, :prod, :total, :days, :coll, :newc, :credit)
            ON DUPLICATE KEY UPDATE
                sales_target = VALUES(sales_target),
                productive_visits_target = VALUES(productive_visits_target),
                total_visits_target = VALUES(total_visits_target),
                working_days_target = VALUES(working_days_target),
                collection_efficiency_target = VALUES(collection_efficiency_target),
                new_customers_target = VALUES(new_customers_target),
                credit_limit = VALUES(credit_limit)
        ");
        $this->db->bind(':uid', intval($data['user_id']));
        $this->db->bind(':m', $data['month']);
        $this->db->bind(':y', $data['year']);
        $this->db->bind(':sales', floatval($data['sales_target']));
        $this->db->bind(':prod', intval($data['productive_visits_target']));
        $this->db->bind(':total', intval($data['total_visits_target']));
        $this->db->bind(':days', intval($data['working_days_target']));
        $this->db->bind(':coll', floatval($data['collection_efficiency_target'] ?? 80.00));
        $this->db->bind(':newc', intval($data['new_customers_target'] ?? 5));
        $this->db->bind(':credit', floatval($data['credit_limit']));
        
        return $this->db->execute();
    }

    /**
     * Get monthly aggregates trend for a representative over the last N months.
     */
    public function getMonthlyTrend(int $repUserId, int $limit = 6): array {
        $trends = [];
        for ($i = $limit - 1; $i >= 0; $i--) {
            // Get first and last day of that month
            $targetMonth = date('m', strtotime("-$i months"));
            $targetYear = date('Y', strtotime("-$i months"));
            
            $start = date("$targetYear-$targetMonth-01");
            $end = date("$targetYear-$targetMonth-t");
            
            $perf = $this->calculatePerformance($repUserId, $start, $end);
            
            $trends[] = [
                'label' => date('M Y', strtotime($start)),
                'net_sales' => $perf['net_sales'],
                'total_collections' => $perf['total_collections'],
                'overall_score' => $perf['overall_score']
            ];
        }
        return $trends;
    }
}
