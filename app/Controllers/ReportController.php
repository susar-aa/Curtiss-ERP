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

        $reportMetadata = $registry[$reportKey];

        // Fetch dynamic filter options from the database
        $db = new Database();

        $db->query("SELECT id, name FROM customers ORDER BY name ASC");
        $customers = $db->resultSet() ?: [];

        $db->query("SELECT id, name FROM vendors ORDER BY name ASC");
        $suppliers = $db->resultSet() ?: [];

        $db->query("SELECT id, name FROM items ORDER BY name ASC");
        $products = $db->resultSet() ?: [];

        $db->query("SELECT id, name FROM warehouses ORDER BY name ASC");
        $warehouses = $db->resultSet() ?: [];

        $db->query("SELECT id, route_name FROM rep_daily_routes ORDER BY route_name ASC");
        $routes = $db->resultSet() ?: [];

        $db->query("SELECT id, name FROM item_categories ORDER BY name ASC");
        $categories = $db->resultSet() ?: [];

        // Load saved views and scheduled reports for this user
        $userId = $_SESSION['user_id'];
        $savedViews = $this->engine->getSavedViews($userId, $reportKey);
        $scheduled = $this->engine->getScheduledReports($userId, $reportKey);

        $data = [
            'title' => $reportMetadata['title'] . ' - Viewer',
            'content_view' => 'reports/viewer',
            'reportKey' => $reportKey,
            'metadata' => $reportMetadata,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'routes' => $routes,
            'categories' => $categories,
            'savedViews' => $savedViews,
            'scheduled' => $scheduled
        ];

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
     * Save report view filters
     */
    public function save_view() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $reportKey = $_POST['report_key'] ?? '';
        $viewName = $_POST['view_name'] ?? '';
        $filters = $_POST['filters'] ?? [];

        $success = $this->engine->saveView($userId, $reportKey, $viewName, $filters);
        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Save automated scheduled report
     */
    public function save_schedule() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $reportKey = $_POST['report_key'] ?? '';
        $frequency = $_POST['frequency'] ?? '';
        $email = $_POST['email_recipient'] ?? '';
        $filters = $_POST['filters'] ?? [];

        $success = $this->engine->saveSchedule($userId, $reportKey, $frequency, $email, $filters);
        echo json_encode(['success' => $success]);
        exit;
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
                            $val = '$' . number_format((float)$val, 2);
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
     * Cron endpoint to process automated email report distribution
     */
    public function run_cron() {
        header('Content-Type: application/json');
        $processed = $this->engine->runScheduledReports();
        echo json_encode(['success' => true, 'processed_schedules_count' => $processed]);
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
        ];

        // Fetch data
        $dataResult = $this->engine->fetchData($reportKey, $filters, 1, 10000);
        $rows = $dataResult['rows'];
        $grandTotals = $dataResult['grand_totals'];

        // Get company settings
        $db = new Database();
        $db->query("SELECT * FROM company_settings LIMIT 1");
        $company = $db->single() ?: (object)[
            'company_name' => 'Falcon Stationary (Pvt) Ltd',
            'phone' => '', 'email' => '', 'address' => ''
        ];

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

        if (!empty($filters['customer'])) {
            $db->query("SELECT name FROM customers WHERE id = :id");
            $db->bind(':id', $filters['customer']);
            $row = $db->single();
            if ($row) $filterLabels['Customer'] = $row->name;
        }

        if (!empty($filters['supplier'])) {
            $db->query("SELECT name FROM vendors WHERE id = :id");
            $db->bind(':id', $filters['supplier']);
            $row = $db->single();
            if ($row) $filterLabels['Supplier'] = $row->name;
        }

        if (!empty($filters['product'])) {
            $db->query("SELECT name, item_code FROM items WHERE id = :id");
            $db->bind(':id', $filters['product']);
            $row = $db->single();
            if ($row) $filterLabels['Product'] = $row->item_code . ' - ' . $row->name;
        }

        if (!empty($filters['warehouse'])) {
            $db->query("SELECT name FROM warehouses WHERE id = :id");
            $db->bind(':id', $filters['warehouse']);
            $row = $db->single();
            if ($row) $filterLabels['Warehouse'] = $row->name;
        }

        if (!empty($filters['category'])) {
            $db->query("SELECT name FROM item_categories WHERE id = :id");
            $db->bind(':id', $filters['category']);
            $row = $db->single();
            if ($row) $filterLabels['Category'] = $row->name;
        }

        if (!empty($filters['route'])) {
            $db->query("SELECT route_name FROM rep_daily_routes WHERE id = :id");
            $db->bind(':id', $filters['route']);
            $row = $db->single();
            if ($row) $filterLabels['Route'] = $row->route_name;
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
}

