<?php
/**
 * Curtiss ERP - Central Report Viewer Controller
 */
require_once '../app/Services/ReportEngine.php';

class ReportController extends Controller {
    private $engine;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->generateCsrfToken();
        $this->engine = new ReportEngine();
    }

    /**
     * Centralized Reporting Hub Dashboard
     */
    public function index() {
        $categories = ReportEngine::getCategories();
        $reports = ReportEngine::getReportsRegistry();

        // Group reports by category
        $groupedReports = [];
        foreach ($categories as $catKey => $catTitle) {
            $groupedReports[$catKey] = [];
        }
        foreach ($reports as $key => $rep) {
            $groupedReports[$rep['category']][$key] = $rep;
        }

        $data = [
            'title' => 'Central Reporting Hub',
            'content_view' => 'reports/index',
            'categories' => $categories,
            'grouped_reports' => $groupedReports
        ];
        $this->view('layouts/main', $data);
    }

    /**
     * Render the Reusable Central Report Viewer Page
     */
    public function viewer($reportKey = null) {
        if (!$reportKey) {
            header('Location: ' . APP_URL . '/report');
            exit;
        }

        $registry = ReportEngine::getReportsRegistry();
        if (!isset($registry[$reportKey])) {
            die("Report '$reportKey' is not registered.");
        }

        if ($reportKey === 'balance_sheet') {
            $companyModel = $this->model('Company');
            $company = $companyModel->getSettings();
            $reportModel = $this->model('Report');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            // Calculate Net Income (P&L Accounts: Revenue - Expense)
            $plAccounts = $reportModel->getAccountsByTypes(['Revenue', 'Expense'], $endDate);
            $netIncome = 0;
            foreach ($plAccounts as $acc) {
                if ($acc->account_type == 'Revenue') {
                    $netIncome += $acc->balance;
                } else {
                    $netIncome -= $acc->balance;
                }
            }

            // Get balance sheet accounts (Asset, Liability, Equity)
            $bsAccounts = $reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity'], $endDate);
            $assets = [];
            $liabilities = [];
            $equities = [];
            $totalAssets = 0;
            $totalLiabilities = 0;
            $totalEquity = 0;

            foreach ($bsAccounts as $acc) {
                if ($acc->account_type == 'Asset') {
                    $assets[] = $acc;
                    $totalAssets += $acc->balance;
                } elseif ($acc->account_type == 'Liability') {
                    $liabilities[] = $acc;
                    $totalLiabilities += $acc->balance;
                } elseif ($acc->account_type == 'Equity') {
                    $equities[] = $acc;
                    $totalEquity += $acc->balance;
                }
            }

            $totalEquity += $netIncome;
            $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

            $data = [
                'title' => 'Balance Sheet',
                'content_view' => 'reports/balance_sheet',
                'company' => $company,
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equities' => $equities,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'net_income' => $netIncome,
                'total_equity' => $totalEquity,
                'total_liabilities_equity' => $totalLiabilitiesAndEquity,
                'end_date' => $endDate
            ];
            $this->view('layouts/main', $data);
            return;
        }

        if ($reportKey === 'cash_flow') {
            $companyModel = $this->model('Company');
            $company = $companyModel->getSettings();
            $reportModel = $this->model('Report');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            $accounts = $reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'], $endDate);

            $netIncome = 0; $operating = []; $investing = []; $financing = []; $cashBalance = 0;

            foreach($accounts as $acc) {
                $nameStr = strtolower($acc->account_name);
                if ($acc->account_type == 'Revenue') { $netIncome += $acc->balance; }
                elseif ($acc->account_type == 'Expense') { $netIncome -= $acc->balance; }
                
                if ($acc->account_type == 'Asset') {
                    if (strpos($nameStr, 'cash') !== false || strpos($nameStr, 'bank') !== false) {
                        $cashBalance += $acc->balance; 
                    } elseif (strpos($nameStr, 'equipment') !== false || strpos($nameStr, 'asset') !== false) {
                        $investing[] = ['name' => 'Purchase of ' . $acc->account_name, 'amount' => -$acc->balance]; 
                    } else {
                        $operating[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => -$acc->balance]; 
                    }
                } elseif ($acc->account_type == 'Liability') {
                    $operating[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => $acc->balance]; 
                } elseif ($acc->account_type == 'Equity') {
                    if (strpos($nameStr, 'retained') === false) { 
                        $financing[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => $acc->balance];
                    }
                }
            }

            $data = [
                'title' => 'Statement of Cash Flows',
                'content_view' => 'reports/cash_flow',
                'company' => $company,
                'net_income' => $netIncome,
                'operating' => $operating,
                'investing' => $investing,
                'financing' => $financing,
                'ending_cash' => $cashBalance,
                'end_date' => $endDate
            ];
            $this->view('layouts/main', $data);
            return;
        }

        $reportModel = $this->model('Report');

        if ($reportKey === 'multi_period_comparison') {
            $companyModel = $this->model('Company');
            $company = $companyModel->getSettings();

            // Get selected dates or defaults
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $comparisonType = $_GET['comparison_type'] ?? 'mom';

            if ($comparisonType === 'yoy') {
                $compStartDate = date('Y-m-d', strtotime('-1 year', strtotime($startDate)));
                $compEndDate = date('Y-m-d', strtotime('-1 year', strtotime($endDate)));
            } else {
                $compStartDate = date('Y-m-d', strtotime('-1 month', strtotime($startDate)));
                $compEndDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate)));
            }

            $baseBalances = $reportModel->getComparativeBalances($startDate, $endDate);
            $compBalancesRaw = $reportModel->getComparativeBalancesById($compStartDate, $compEndDate);
            
            $compBalances = [];
            foreach ($compBalancesRaw as $cb) {
                $compBalances[$cb->id] = $cb->balance;
            }

            $comparisonData = [];
            foreach ($baseBalances as $bb) {
                $compVal = $compBalances[$bb->id] ?? 0.0;
                $variance = $bb->balance - $compVal;
                $pctChange = 0.0;
                if (round($compVal, 2) != 0.0) {
                    $pctChange = ($variance / abs($compVal)) * 100;
                } elseif (round($bb->balance, 2) != 0.0) {
                    $pctChange = 100.0;
                }

                $comparisonData[] = [
                    'account_code' => $bb->account_code,
                    'account_name' => $bb->account_name,
                    'account_type' => $bb->account_type,
                    'base_balance' => $bb->balance,
                    'comp_balance' => $compVal,
                    'variance' => $variance,
                    'pct_change' => $pctChange
                ];
            }

            $data = [
                'title' => 'Multi-Period Comparative Report',
                'content_view' => 'reports/multi_period_comparison',
                'company' => $company,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'comp_start_date' => $compStartDate,
                'comp_end_date' => $compEndDate,
                'comparison_type' => $comparisonType,
                'comparison_data' => $comparisonData
            ];
            $this->view('layouts/main', $data);
            return;
        }

        $reportMetadata = $registry[$reportKey];
        $filtersData = $reportModel->getReportFiltersData();

        $payment_methods = [
            (object)['id' => 'Cash', 'name' => 'Cash'],
            (object)['id' => 'Bank Transfer', 'name' => 'Bank Transfer'],
            (object)['id' => 'Cheque', 'name' => 'Cheque']
        ];

        $statuses = [
            (object)['id' => 'Active', 'name' => 'Active'],
            (object)['id' => 'Paid', 'name' => 'Paid'],
            (object)['id' => 'Unpaid', 'name' => 'Unpaid'],
            (object)['id' => 'Pending', 'name' => 'Pending'],
            (object)['id' => 'Cleared', 'name' => 'Cleared'],
            (object)['id' => 'Bounced', 'name' => 'Bounced'],
            (object)['id' => 'Voided', 'name' => 'Voided']
        ];

        $data = array_merge([
            'title' => $reportMetadata['title'] . ' - Viewer',
            'content_view' => 'reports/viewer',
            'reportKey' => $reportKey,
            'metadata' => $reportMetadata,
            'payment_methods' => $payment_methods,
            'statuses' => $statuses
        ], $filtersData);

        $this->view('layouts/main', $data);
    }

    /**
     * JSON API Endpoint to fetch paginated report data
     */
    public function fetch_data() {
        header('Content-Type: application/json');
        
        $reportKey = $_GET['report'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $sortCol = $_GET['sort_col'] ?? null;
        $sortDir = $_GET['sort_dir'] ?? 'ASC';

        // Extract active filters
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'customer' => $_GET['customer'] ?? null,
            'supplier' => $_GET['supplier'] ?? null,
            'product' => $_GET['product'] ?? null,
            'warehouse' => $_GET['warehouse'] ?? null,
            'route' => $_GET['route'] ?? null,
            'category' => $_GET['category'] ?? null,
            'rep' => $_GET['rep'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'status' => $_GET['status'] ?? null,
            'brand' => $_GET['brand'] ?? null,
            'group' => $_GET['group'] ?? null,
            'vehicle' => $_GET['vehicle'] ?? null,
            'driver' => $_GET['driver'] ?? null,
            'partner' => $_GET['partner'] ?? null,
            'territory' => $_GET['territory'] ?? null,
            'tb_type' => $_GET['tb_type'] ?? null,
        ];

        try {
            $result = $this->engine->fetchData($reportKey, $filters, $page, $limit, $sortCol, $sortDir);
            echo json_encode(array_merge(['success' => true], $result));
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX Endpoint for Side Panel Quick View
     */
    public function quick_view() {
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? '';
        $number = $_GET['number'] ?? '';

        if (empty($type)) {
            echo json_encode(['success' => false, 'message' => 'Type is required.']);
            exit;
        }

        try {
            $reportModel = $this->model('Report');
            $resData = $reportModel->getQuickViewData($type, $id, $number);
            echo json_encode($resData);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    protected function validateCsrf() {
        if (!parent::validateCsrf()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            if ($isAjax) {
                header('HTTP/1.1 403 Forbidden');
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'success' => false, 'message' => 'CSRF token validation failed.']);
                exit;
            } else {
                http_response_code(403);
                die("CSRF validation failed.");
            }
        }
        return true;
    }

    /**
     * Export Report to Excel, CSV, Word, PDF, JSON, XML
     */
    public function export($reportKey = null) {
        if (!$reportKey) {
            die("Report key is required.");
        }

        $registry = ReportEngine::getReportsRegistry();
        if (!isset($registry[$reportKey])) {
            die("Report not registered.");
        }

        $metadata = $registry[$reportKey];
        $format = $_GET['format'] ?? 'csv';

        // Extract filters
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'customer' => $_GET['customer'] ?? null,
            'supplier' => $_GET['supplier'] ?? null,
            'product' => $_GET['product'] ?? null,
            'warehouse' => $_GET['warehouse'] ?? null,
            'route' => $_GET['route'] ?? null,
            'category' => $_GET['category'] ?? null,
            'rep' => $_GET['rep'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'status' => $_GET['status'] ?? null,
            'brand' => $_GET['brand'] ?? null,
            'group' => $_GET['group'] ?? null,
            'vehicle' => $_GET['vehicle'] ?? null,
            'driver' => $_GET['driver'] ?? null,
            'partner' => $_GET['partner'] ?? null,
            'territory' => $_GET['territory'] ?? null,
            'tb_type' => $_GET['tb_type'] ?? null,
        ];

        // Fetch all matching data (large limit for exports)
        $dataResult = $this->engine->fetchData($reportKey, $filters, 1, 10000);
        $rows = $dataResult['rows'];

        $filename = strtolower(str_replace(' ', '_', $metadata['title'])) . '_' . date('Ymd_His');

        switch ($format) {
            case 'excel':
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
                
                // Output as structured HTML Table that Excel loads natively
                echo '<html><head><meta charset="utf-8"></head><body>';
                echo '<h2>' . htmlspecialchars($metadata['title']) . '</h2>';
                echo '<table border="1">';
                echo '<tr style="background:#e0e0e0; font-weight:bold;">';
                foreach ($metadata['columns'] as $c) {
                    echo '<th>' . htmlspecialchars($c['label']) . '</th>';
                }
                echo '</tr>';
                foreach ($rows as $r) {
                    echo '<tr>';
                    foreach ($metadata['columns'] as $colKey => $c) {
                        $val = $r->$colKey ?? '';
                        if ($c['type'] === 'currency') {
                            $val = 'Rs. ' . number_format((float)$val, 2);
                        }
                        echo '<td>' . htmlspecialchars($val) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table></body></html>';
                break;

            case 'word':
                header('Content-Type: application/msword');
                header('Content-Disposition: attachment; filename="' . $filename . '.doc"');
                
                echo '<html><head><meta charset="utf-8"></head><body>';
                echo '<h2>' . htmlspecialchars($metadata['title']) . '</h2>';
                echo '<table border="1" cellpadding="5">';
                echo '<tr style="background:#0066cc; color:#ffffff; font-weight:bold;">';
                foreach ($metadata['columns'] as $c) {
                    echo '<th>' . htmlspecialchars($c['label']) . '</th>';
                }
                echo '</tr>';
                foreach ($rows as $r) {
                    echo '<tr>';
                    foreach ($metadata['columns'] as $colKey => $c) {
                        echo '<td>' . htmlspecialchars($r->$colKey ?? '') . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table></body></html>';
                break;

            case 'json':
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $filename . '.json"');
                echo json_encode($rows, JSON_PRETTY_PRINT);
                break;

            case 'xml':
                header('Content-Type: text/xml');
                header('Content-Disposition: attachment; filename="' . $filename . '.xml"');
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo '<report>';
                echo '<title>' . htmlspecialchars($metadata['title']) . '</title>';
                echo '<data>';
                foreach ($rows as $r) {
                    echo '<row>';
                    foreach ($metadata['columns'] as $colKey => $c) {
                        echo '<' . $colKey . '>' . htmlspecialchars($r->$colKey ?? '') . '</' . $colKey . '>';
                    }
                    echo '</row>';
                }
                echo '</data>';
                echo '</report>';
                break;

            case 'csv':
            default:
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
                $out = fopen('php://output', 'w');

                // Output header columns
                $headers = [];
                foreach ($metadata['columns'] as $c) {
                    $headers[] = $c['label'];
                }
                fputcsv($out, $headers);

                // Output data rows
                foreach ($rows as $r) {
                    $vals = [];
                    foreach ($metadata['columns'] as $colKey => $c) {
                        $vals[] = $r->$colKey ?? '';
                    }
                    fputcsv($out, $vals);
                }
                fclose($out);
                break;
        }
        exit;
    }

    /**
     * Dedicated accounting-grade high-fidelity print output
     */
    public function print_report($reportKey = null) {
        if (!$reportKey) {
            die("Report key is required.");
        }

        $registry = ReportEngine::getReportsRegistry();
        if (!isset($registry[$reportKey])) {
            die("Report not registered.");
        }

        $metadata = $registry[$reportKey];

        // Extract filters
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'customer' => $_GET['customer'] ?? null,
            'supplier' => $_GET['supplier'] ?? null,
            'product' => $_GET['product'] ?? null,
            'warehouse' => $_GET['warehouse'] ?? null,
            'route' => $_GET['route'] ?? null,
            'category' => $_GET['category'] ?? null,
            'rep' => $_GET['rep'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'status' => $_GET['status'] ?? null,
            'brand' => $_GET['brand'] ?? null,
            'group' => $_GET['group'] ?? null,
            'vehicle' => $_GET['vehicle'] ?? null,
            'driver' => $_GET['driver'] ?? null,
            'partner' => $_GET['partner'] ?? null,
            'territory' => $_GET['territory'] ?? null,
            'tb_type' => $_GET['tb_type'] ?? null,
        ];

        // Fetch data
        $dataResult = $this->engine->fetchData($reportKey, $filters, 1, 10000);
        $rows = $dataResult['rows'];
        $grandTotals = $dataResult['grand_totals'];

        // Get company settings
        $reportModel = $this->model('Report');
        $company = $reportModel->getCompanySettings();

        // Resolve active filter values to descriptive labels
        $filterLabels = [];
        if (!empty($filters['start_date'])) {
            $filterLabels['From Date'] = date('d/m/Y', strtotime($filters['start_date']));
        } else {
            $filterLabels['From Date'] = date('01/m/Y');
        }

        if (!empty($filters['end_date'])) {
            $filterLabels['To Date'] = date('d/m/Y', strtotime($filters['end_date']));
        } else {
            $filterLabels['To Date'] = date('d/m/Y');
        }

        $resolvableFilters = [
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'product' => 'Product',
            'warehouse' => 'Warehouse',
            'category' => 'Category',
            'route' => 'Route',
            'rep' => 'Sales Rep',
            'driver' => 'Driver'
        ];

        foreach ($resolvableFilters as $filterKey => $label) {
            if (!empty($filters[$filterKey])) {
                $name = $reportModel->resolveEntityName($filterKey, $filters[$filterKey]);
                if ($name) {
                    $filterLabels[$label] = $name;
                }
            }
        }

        if (!empty($filters['payment_method'])) {
            $filterLabels['Payment Method'] = $filters['payment_method'];
        }

        if (!empty($filters['status'])) {
            $filterLabels['Status'] = $filters['status'];
        }

        if (!empty($filters['brand'])) {
            $filterLabels['Brand'] = $filters['brand'];
        }

        if (!empty($filters['group'])) {
            $filterLabels['Customer/Supplier Group'] = $filters['group'];
        }

        if (!empty($filters['vehicle'])) {
            $filterLabels['Vehicle'] = $filters['vehicle'];
        }

        if (!empty($filters['partner'])) {
            $filterLabels['Partner'] = $filters['partner'];
        }

        if (!empty($filters['territory'])) {
            $filterLabels['Territory'] = $filters['territory'];
        }

        if (!empty($filters['tb_type'])) {
            $filterLabels['Type'] = $filters['tb_type'] === 'post_closing' ? 'Post-Closing' : 'Pre-Closing';
        }



        $data = [
            'reportKey' => $reportKey,
            'metadata' => $metadata,
            'rows' => $rows,
            'grandTotals' => $grandTotals,
            'company' => $company,
            'filters' => $filters,
            'filterLabels' => $filterLabels
        ];

        // Load the dedicated printing template
        $this->view('reports/print', $data);
    }

    public function trial_balance() {
        $reportModel = $this->model('Report');
        $companyModel = $this->model('Company');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $accounts = $reportModel->getTrialBalanceData($endDate);
        $company = $companyModel->getSettings();
        
        $tbData = []; $totalDebit = 0; $totalCredit = 0;
        foreach($accounts as $acc) {
            $debit = 0; $credit = 0;
            if (in_array($acc->account_type, ['Asset', 'Expense'])) {
                if ($acc->balance >= 0) { $debit = $acc->balance; } else { $credit = abs($acc->balance); }
            } else {
                if ($acc->balance >= 0) { $credit = $acc->balance; } else { $debit = abs($acc->balance); }
            }
            $tbData[] = ['code' => $acc->account_code, 'name' => $acc->account_name, 'type' => $acc->account_type, 'debit' => $debit, 'credit' => $credit];
            $totalDebit += $debit; $totalCredit += $credit;
        }

        $data = [
            'title' => 'Trial Balance',
            'company' => $company,
            'tb_data' => $tbData,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'end_date' => $endDate,
            'dated' => true,
            'filter_action' => 'trial_balance'
        ];
        $this->view('reports/trial_balance', $data);
    }

    public function profit_loss() {
        $reportModel = $this->model('Report');
        $companyModel = $this->model('Company');
        $startDate = $_GET['start_date'] ?? date('Y-01-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $accounts = $reportModel->getAccountsByTypes(['Revenue', 'Expense'], $endDate, $startDate);
        $company = $companyModel->getSettings();

        $revenues = []; $expenses = []; $totalRevenue = 0; $totalExpense = 0;
        foreach($accounts as $acc) {
            if ($acc->account_type == 'Revenue') { $revenues[] = $acc; $totalRevenue += $acc->balance; } 
            elseif ($acc->account_type == 'Expense') { $expenses[] = $acc; $totalExpense += $acc->balance; }
        }
        $netIncome = $totalRevenue - $totalExpense;

        $data = [
            'title' => 'Profit & Loss',
            'company' => $company,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dated' => true,
            'filter_action' => 'profit_loss'
        ];
        $this->view('reports/profit_loss', $data);
    }

    public function balance_sheet() {
        $reportModel = $this->model('Report');
        $companyModel = $this->model('Company');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $plAccounts = $reportModel->getAccountsByTypes(['Revenue', 'Expense'], $endDate);
        $netIncome = 0;
        foreach($plAccounts as $acc) {
            if ($acc->account_type == 'Revenue') { $netIncome += $acc->balance; } else { $netIncome -= $acc->balance; }
        }

        $bsAccounts = $reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity'], $endDate);
        $company = $companyModel->getSettings();

        $assets = []; $liabilities = []; $equities = [];
        $totalAssets = 0; $totalLiabilities = 0; $totalEquity = 0;

        foreach($bsAccounts as $acc) {
            if ($acc->account_type == 'Asset') { $assets[] = $acc; $totalAssets += $acc->balance; } 
            elseif ($acc->account_type == 'Liability') { $liabilities[] = $acc; $totalLiabilities += $acc->balance; } 
            elseif ($acc->account_type == 'Equity') { $equities[] = $acc; $totalEquity += $acc->balance; }
        }

        $totalEquity += $netIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $data = [
            'title' => 'Balance Sheet', 'company' => $company, 'assets' => $assets, 'liabilities' => $liabilities, 'equities' => $equities,
            'total_assets' => $totalAssets, 'total_liabilities' => $totalLiabilities, 'total_equity_before_ni' => ($totalEquity - $netIncome),
            'net_income' => $netIncome, 'total_equity' => $totalEquity, 'total_liabilities_equity' => $totalLiabilitiesAndEquity,
            'end_date' => $endDate, 'dated' => true, 'filter_action' => 'balance_sheet'
        ];
        $this->view('reports/balance_sheet', $data);
    }

    public function cash_flow() {
        $reportModel = $this->model('Report');
        $companyModel = $this->model('Company');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $accounts = $reportModel->getAccountsByTypes(['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'], $endDate);
        $company = $companyModel->getSettings();

        $netIncome = 0; $operating = []; $investing = []; $financing = []; $cashBalance = 0;

        foreach($accounts as $acc) {
            $nameStr = strtolower($acc->account_name);
            if ($acc->account_type == 'Revenue') { $netIncome += $acc->balance; }
            elseif ($acc->account_type == 'Expense') { $netIncome -= $acc->balance; }
            
            if ($acc->account_type == 'Asset') {
                if (strpos($nameStr, 'cash') !== false || strpos($nameStr, 'bank') !== false) {
                    $cashBalance += $acc->balance; 
                } elseif (strpos($nameStr, 'equipment') !== false || strpos($nameStr, 'asset') !== false) {
                    $investing[] = ['name' => 'Purchase of ' . $acc->account_name, 'amount' => -$acc->balance]; 
                } else {
                    $operating[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => -$acc->balance]; 
                }
            } elseif ($acc->account_type == 'Liability') {
                $operating[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => $acc->balance]; 
            } elseif ($acc->account_type == 'Equity') {
                if (strpos($nameStr, 'retained') === false) { 
                    $financing[] = ['name' => 'Change in ' . $acc->account_name, 'amount' => $acc->balance];
                }
            }
        }

        $data = [
            'title' => 'Statement of Cash Flows',
            'company' => $company,
            'net_income' => $netIncome,
            'operating' => $operating,
            'investing' => $investing,
            'financing' => $financing,
            'ending_cash' => $cashBalance,
            'end_date' => $endDate,
            'dated' => true,
            'filter_action' => 'cash_flow'
        ];
        $this->view('reports/cash_flow', $data);
    }

    public function ar_aging() {
        $reportModel = $this->model('Report');
        $companyModel = $this->model('Company');
        $invoices = $reportModel->getARAging();
        $company = $companyModel->getSettings();

        $agingData = [];
        $totals = ['current' => 0, 'thirty' => 0, 'sixty' => 0, 'ninety' => 0, 'older' => 0, 'total' => 0];

        foreach ($invoices as $inv) {
            $cust = $inv->customer_name;
            if (!isset($agingData[$cust])) {
                $agingData[$cust] = ['current' => 0, 'thirty' => 0, 'sixty' => 0, 'ninety' => 0, 'older' => 0, 'total' => 0];
            }

            $amount = $inv->total_amount;
            $days = $inv->days_overdue;

            if ($days <= 0) { $agingData[$cust]['current'] += $amount; $totals['current'] += $amount; }
            elseif ($days <= 30) { $agingData[$cust]['thirty'] += $amount; $totals['thirty'] += $amount; }
            elseif ($days <= 60) { $agingData[$cust]['sixty'] += $amount; $totals['sixty'] += $amount; }
            elseif ($days <= 90) { $agingData[$cust]['ninety'] += $amount; $totals['ninety'] += $amount; }
            else { $agingData[$cust]['older'] += $amount; $totals['older'] += $amount; }

            $agingData[$cust]['total'] += $amount;
            $totals['total'] += $amount;
        }

        $data = ['title' => 'A/R Aging Summary', 'company' => $company, 'aging_data' => $agingData, 'totals' => $totals];
        $this->view('reports/ar_aging', $data);
    }

    public function fifo_profit() {
        $reportModel = $this->model('Report');
        $companyModel = $this->model('Company');
        $invoices = $reportModel->getFIFOSalesData();
        $company = $companyModel->getSettings();

        $data = [
            'title' => 'FIFO Profit & Margin Report',
            'company' => $company,
            'invoices' => $invoices
        ];
        $this->view('reports/fifo_profit', $data);
    }
}
