<?php
declare(strict_types=1);

class RepPerformanceController extends Controller {
    private $perfModel;
    private $userModel;
    private $trackingModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->perfModel = $this->model('RepPerformance');
        $this->userModel = $this->model('User');
        $this->trackingModel = $this->model('RepTracking');
    }

    public function index() {
        $this->checkPermission('reptracking', 'view');

        // 1. Get all representatives for selector
        $db = new Database();
        $db->query("SELECT u.id, u.username, e.first_name, e.last_name 
                    FROM users u
                    LEFT JOIN employees e ON u.employee_id = e.id
                    WHERE u.role = 'Rep (Sales Representative)'
                    ORDER BY u.username ASC");
        $reps = $db->resultSet() ?: [];

        // 2. Resolve active filter parameters
        $repUserId = isset($_GET['rep_user_id']) && $_GET['rep_user_id'] !== '' ? intval($_GET['rep_user_id']) : 0;

        // Handle month/year filters (defaulting to the current month & year)
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $startDate = date("$year-$month-01");
        $endDate = date("$year-$month-t");

        $routeId = !empty($_GET['route_id']) ? intval($_GET['route_id']) : null;
        $areaId = !empty($_GET['area_id']) ? intval($_GET['area_id']) : null;
        $compareUserId = !empty($_GET['compare_user_id']) ? intval($_GET['compare_user_id']) : null;

        // 3. Retrieve routes and territory list
        $db->query("SELECT id, route_name FROM rep_daily_routes WHERE user_id = :uid ORDER BY route_name ASC");
        $db->bind(':uid', $repUserId);
        $routes = $db->resultSet() ?: [];

        $db->query("SELECT id, name FROM mca_areas ORDER BY name ASC");
        $areas = $db->resultSet() ?: [];

        // 4. Compute primary representative performance
        $perfData = [];
        $monthlyTrend = [];
        if ($repUserId > 0) {
            $perfData = $this->perfModel->calculatePerformance($repUserId, $startDate, $endDate, $routeId, $areaId);
            $monthlyTrend = $this->perfModel->getMonthlyTrend($repUserId, 6);
        } else {
            // Aggregate all reps for Total View
            $count = 0;
            $kpiScoresSum = [];

            foreach ($reps as $r) {
                $temp = $this->perfModel->calculatePerformance(intval($r->id), $startDate, $endDate, $routeId, $areaId);
                if (empty($perfData)) {
                    $perfData = $temp;
                    $kpiScoresSum = $temp['kpi_scores'] ?? [];
                } else {
                    foreach ($temp as $key => $val) {
                        if (is_numeric($val)) {
                            $perfData[$key] += $val;
                        }
                    }
                    if (isset($temp['kpi_scores'])) {
                        foreach ($temp['kpi_scores'] as $kKey => $kVal) {
                            if (!isset($kpiScoresSum[$kKey])) {
                                $kpiScoresSum[$kKey] = $kVal;
                            } else {
                                $kpiScoresSum[$kKey]['target'] += $kVal['target'] ?? 0;
                                $kpiScoresSum[$kKey]['actual'] += $kVal['actual'] ?? 0;
                                $kpiScoresSum[$kKey]['clamped_score'] += $kVal['clamped_score'] ?? 0;
                            }
                        }
                    }
                }
                $count++;
            }

            if ($count > 0 && !empty($perfData)) {
                $perfData['avg_invoice_value'] = $perfData['invoice_count'] > 0 ? $perfData['net_sales'] / $perfData['invoice_count'] : 0;
                $perfData['productive_visit_rate'] = $perfData['total_visited'] > 0 ? ($perfData['productive_visits'] / $perfData['total_visited']) * 100 : 0;
                $perfData['customer_conversion_rate'] = $perfData['total_visited'] > 0 ? ($perfData['active_customers'] / $perfData['total_visited']) * 100 : 0;
                $perfData['collection_efficiency'] = $perfData['total_sales'] > 0 ? ($perfData['total_collections'] / $perfData['total_sales']) * 100 : 0;
                $perfData['route_completion_rate'] = $perfData['total_routes'] > 0 ? ($perfData['completed_routes'] / $perfData['total_routes']) * 100 : 0;
                $perfData['avg_sales_per_route'] = $perfData['total_routes'] > 0 ? $perfData['net_sales'] / $perfData['total_routes'] : 0;
                $perfData['avg_customers_per_route'] = $perfData['total_routes'] > 0 ? $perfData['active_customers'] / $perfData['total_routes'] : 0;
                $perfData['avg_sales_per_visit'] = $perfData['total_visited'] > 0 ? $perfData['net_sales'] / $perfData['total_visited'] : 0;
                $perfData['expense_per_route'] = $perfData['total_routes'] > 0 ? $perfData['total_expenses'] / $perfData['total_routes'] : 0;
                $perfData['sales_to_expense_ratio'] = $perfData['net_sales'] > 0 ? ($perfData['total_expenses'] / $perfData['net_sales']) * 100 : 0;
                $perfData['avg_daily_sales'] = $perfData['active_route_days'] > 0 ? $perfData['net_sales'] / $perfData['active_route_days'] : 0;
                $perfData['avg_daily_visits'] = $perfData['active_route_days'] > 0 ? $perfData['total_visited'] / $perfData['active_route_days'] : 0;
                $perfData['avg_daily_collections'] = $perfData['active_route_days'] > 0 ? $perfData['total_collections'] / $perfData['active_route_days'] : 0;
                $perfData['sales_per_productive_visit'] = $perfData['productive_visits'] > 0 ? $perfData['net_sales'] / $perfData['productive_visits'] : 0;
                $perfData['sales_per_customer'] = $perfData['active_customers'] > 0 ? $perfData['net_sales'] / $perfData['active_customers'] : 0;
                $perfData['avg_sales_needed_per_day'] = ($perfData['sales_needed_for_target'] > 0 && $perfData['remaining_working_days'] > 0) ? ($perfData['sales_needed_for_target'] / $perfData['remaining_working_days']) : ($perfData['sales_needed_for_target'] > 0 ? $perfData['sales_needed_for_target'] : 0);
                $perfData['collection_target_pct'] = $perfData['collection_target_pct'] / $count;
                
                $perfData['overall_score'] = $perfData['overall_score'] / $count;

                foreach ($kpiScoresSum as $kKey => &$kVal) {
                    if ($kVal['target'] > 0) {
                        $kVal['achievement_pct'] = ($kVal['actual'] / $kVal['target']) * 100;
                    } else {
                        $kVal['achievement_pct'] = $kVal['actual'] > 0 ? 100.0 : 0.0;
                    }
                    $kVal['clamped_score'] = $kVal['clamped_score'] / $count;
                }
                $perfData['kpi_scores'] = $kpiScoresSum;
            }
        }

        // 5. Compute comparisons if requested
        $compareData = null;
        if ($compareUserId && $compareUserId > 0) {
            $compareData = $this->perfModel->calculatePerformance($compareUserId, $startDate, $endDate, null, $areaId);
        }

        // 6. Compute Team Average for comparison
        $teamAvg = [];
        if (!empty($reps)) {
            $sumSales = 0;
            $sumVisits = 0;
            $sumProdVisits = 0;
            $sumCollections = 0;
            $sumRoutes = 0;
            $sumNewCust = 0;
            $sumScore = 0;
            $count = 0;

            foreach ($reps as $r) {
                $temp = $this->perfModel->calculatePerformance(intval($r->id), $startDate, $endDate, null, $areaId);
                $sumSales += $temp['net_sales'];
                $sumVisits += $temp['total_visited'];
                $sumProdVisits += $temp['productive_visits'];
                $sumCollections += $temp['total_collections'];
                $sumRoutes += $temp['total_routes'];
                $sumNewCust += $temp['new_customers_added'];
                $sumScore += $temp['overall_score'];
                $count++;
            }

            $teamAvg = [
                'net_sales' => $count > 0 ? $sumSales / $count : 0,
                'total_visited' => $count > 0 ? $sumVisits / $count : 0,
                'productive_visits' => $count > 0 ? $sumProdVisits / $count : 0,
                'total_collections' => $count > 0 ? $sumCollections / $count : 0,
                'total_routes' => $count > 0 ? $sumRoutes / $count : 0,
                'new_customers_added' => $count > 0 ? $sumNewCust / $count : 0,
                'overall_score' => $count > 0 ? $sumScore / $count : 0,
            ];
        }

        // 7. Rankings/Leaderboard for Period
        $rankings = [];
        foreach ($reps as $r) {
            $temp = $this->perfModel->calculatePerformance(intval($r->id), $startDate, $endDate, null, $areaId);
            $rankings[] = [
                'id' => $r->id,
                'username' => $r->username,
                'first_name' => $r->first_name,
                'last_name' => $r->last_name,
                'score' => $temp['overall_score'],
                'net_sales' => $temp['net_sales'],
                'total_collections' => $temp['total_collections'],
                'conversion_rate' => $temp['customer_conversion_rate']
            ];
        }
        usort($rankings, fn($a, $b) => $b['score'] <=> $a['score']);

        $kpiConfigs = $this->perfModel->getKpiConfigs();

        $data = [
            'title' => 'Rep Performance & KPI Dashboard',
            'content_view' => 'rep_performance/index',
            'reps' => $reps,
            'routes' => $routes,
            'areas' => $areas,
            'selected_rep_id' => $repUserId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'selected_route_id' => $routeId,
            'selected_area_id' => $areaId,
            'compare_rep_id' => $compareUserId,
            'month' => $month,
            'year' => $year,
            'perf_data' => $perfData,
            'monthly_trend' => $monthlyTrend,
            'compare_data' => $compareData,
            'team_avg' => $teamAvg,
            'rankings' => $rankings,
            'kpi_configs' => $kpiConfigs,
            'csrf_token' => $this->generateCsrfToken(),
            'error' => $_SESSION['flash_error'] ?? '',
            'success' => $_SESSION['flash_success'] ?? ''
        ];

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $this->view('layouts/main', $data);
    }

    public function save_settings() {
        $this->checkPermission('reptracking', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $configs = $_POST['configs'] ?? [];
            if ($this->perfModel->updateKpiConfigs($configs)) {
                $this->logActivity('Update KPI Settings', 'Analytics', 'Updated performance targets and scoring weights.');
                $_SESSION['flash_success'] = 'KPI configurations and weights saved successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to update KPI weights settings.';
            }
        }
        header('Location: ' . APP_URL . '/repperformance');
        exit;
    }

    public function export($type) {
        $this->checkPermission('reptracking', 'view');
        
        $repUserId = intval($_GET['rep_user_id'] ?? 0);
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $routeId = !empty($_GET['route_id']) ? intval($_GET['route_id']) : null;
        $areaId = !empty($_GET['area_id']) ? intval($_GET['area_id']) : null;

        $perf = $this->perfModel->calculatePerformance($repUserId, $startDate, $endDate, $routeId, $areaId);

        // Define file name based on type
        $filename = "rep_performance_" . $type . "_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if ($type === 'kpi') {
            fputcsv($output, ['Performance Dimension / KPI', 'Weight (%)', 'Target', 'Actual', 'Achievement (%)', 'Weighted Contribution']);
            foreach ($perf['kpi_scores'] as $key => $sc) {
                fputcsv($output, [$sc['name'], $sc['weight'], $sc['target'], $sc['actual'], number_format($sc['achievement_pct'], 2), number_format($sc['contribution'], 2)]);
            }
            fputcsv($output, ['Overall Performance Score', '', '', '', '', number_format($perf['overall_score'], 2)]);
        } elseif ($type === 'sales') {
            fputcsv($output, ['Product / Category Sales Performance']);
            fputcsv($output, ['Category', 'Sales Amount (LKR)']);
            foreach ($perf['top_categories'] as $cat) {
                fputcsv($output, [$cat->category_name, $cat->total_sales]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Top Product Sales']);
            fputcsv($output, ['Product Name', 'Quantity Sold', 'Revenue (LKR)']);
            foreach ($perf['top_products'] as $prod) {
                fputcsv($output, [$prod->product_name, $prod->qty, $prod->total_sales]);
            }
        } elseif ($type === 'route') {
            fputcsv($output, ['Route Performance Metrics']);
            fputcsv($output, ['Metric Name', 'Value']);
            fputcsv($output, ['Total Routes Started', $perf['total_routes']]);
            fputcsv($output, ['Completed/Finalized Routes', $perf['completed_routes']]);
            fputcsv($output, ['Route Completion Rate (%)', number_format($perf['route_completion_rate'], 2)]);
            fputcsv($output, ['Average Sales per Route (LKR)', number_format($perf['avg_sales_per_route'], 2)]);
            fputcsv($output, ['Average Customers per Route', number_format($perf['avg_customers_per_route'], 2)]);
            fputcsv($output, ['Average Sales per Visit (LKR)', number_format($perf['avg_sales_per_visit'], 2)]);
        } elseif ($type === 'collection') {
            fputcsv($output, ['Collection Payout summary']);
            fputcsv($output, ['Payment Method', 'Amount Collected']);
            fputcsv($output, ['Cash Collections', number_format($perf['cash_collections'], 2)]);
            fputcsv($output, ['Cheque Collections', number_format($perf['cheque_collections'], 2)]);
            fputcsv($output, ['Bank Transfer Collections', number_format($perf['bank_collections'], 2)]);
            fputcsv($output, ['Total Collections', number_format($perf['total_collections'], 2)]);
            fputcsv($output, ['Collection Efficiency (%)', number_format($perf['collection_efficiency'], 2)]);
            fputcsv($output, ['Outstanding Receivables (LKR)', number_format($perf['outstanding_amount'], 2)]);
        } elseif ($type === 'customer') {
            fputcsv($output, ['Customer Engagement and Visit Coverage']);
            fputcsv($output, ['Metric Name', 'Value']);
            fputcsv($output, ['Total Visited Customers', $perf['total_visited']]);
            fputcsv($output, ['Productive Sales Visits', $perf['productive_visits']]);
            fputcsv($output, ['Unproductive Visits', $perf['unproductive_visits']]);
            fputcsv($output, ['New Customers Added', $perf['new_customers_added']]);
            fputcsv($output, ['Productive Visit Rate (%)', number_format($perf['productive_visit_rate'], 2)]);
            fputcsv($output, ['Conversion Rate (%)', number_format($perf['customer_conversion_rate'], 2)]);
        }
        
        fclose($output);
        exit;
    }

    public function save_targets() {
        $this->checkPermission('reptracking', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => intval($_POST['target_user_id'] ?? 0),
                'month' => $_POST['target_month'] ?? '00',
                'year' => $_POST['target_year'] ?? '0000',
                'sales_target' => floatval($_POST['sales_target'] ?? 0),
                'productive_visits_target' => floatval($_POST['productive_visits_target'] ?? 0),
                'total_visits_target' => floatval($_POST['total_visits_target'] ?? 0),
                'working_days_target' => floatval($_POST['working_days_target'] ?? 0),
                'collection_efficiency_target' => floatval($_POST['collection_efficiency_target'] ?? 0),
                'new_customers_target' => floatval($_POST['new_customers_target'] ?? 0),
                'credit_limit' => floatval($_POST['credit_limit'] ?? 0),
            ];
            
            if ($this->perfModel->saveRepTargets($data)) {
                $this->logActivity('Update Rep Targets', 'Analytics', "Updated performance targets for User ID {$data['user_id']} ({$data['month']}/{$data['year']})");
                $_SESSION['flash_success'] = 'Representative targets saved successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to save targets.';
            }
        }
        header('Location: ' . APP_URL . '/repperformance');
        exit;
    }
}
