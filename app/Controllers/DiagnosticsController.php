<?php
class DiagnosticsController extends Controller {

    private function checkAuth() {
        // Bypass if secret key matches
        if (isset($_GET['secret']) && $_GET['secret'] === 'curtiss_debug_123') {
            return true;
        }
        
        // Regular session check
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
            $role = strtolower($_SESSION['role'] ?? '');
            if (in_array($role, ['admin', 'accountant', 'office staff'])) {
                return true;
            }
        }
        
        return false;
    }

    public function index() {
        return $this->db();
    }

    public function db() {
        if (!$this->checkAuth()) {
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><title>Access Denied</title><style>body{font-family:sans-serif;background:#1e1e2e;color:#cdd6f4;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}div{background:#313244;padding:30px;border-radius:12px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.3);}</style></head><body><div><h2>Access Denied</h2><p>Provide the debug secret key in the URL (e.g., <code>?secret=curtiss_debug_123</code>) or login as admin.</p></div></body></html>";
            exit;
        }

        $dbStatus = 'Unknown';
        $dbError = null;
        $tablesAudit = [];
        $migrationStatus = [];
        
        // 1. Check DB connection
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ];
            $dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
            $dbStatus = 'Connected';
            
            // Audit Tables
            $tablesToAudit = [
                'customers' => ['id', 'name', 'phone', 'whatsapp', 'address', 'territory', 'latitude', 'longitude', 'opening_balance', 'credit_limit', 'customer_type', 'notes', 'status', 'uuid', 'updated_at'],
                'items' => ['id', 'woocommerce_product_id', 'item_code', 'name', 'category_id', 'price', 'quantity_on_hand', 'quantity_reserved', 'wholesale_price', 'variations_json', 'image_path', 'brand', 'status', 'cost_price', 'sample_code', 'updated_at'],
                'item_categories' => ['id', 'name', 'description', 'updated_at', 'status'],
                'mca_areas' => ['id', 'name', 'status', 'updated_at'],
                'customer_payments' => ['id', 'customer_id', 'amount', 'status', 'payment_date'],
                'migrations' => ['id', 'migration', 'executed_at'],
                'invoices' => ['id', 'invoice_number', 'customer_id', 'invoice_date', 'total_amount', 'status'],
                'invoice_items' => ['id', 'invoice_id', 'item_id', 'quantity', 'unit_price']
            ];

            foreach ($tablesToAudit as $table => $columns) {
                try {
                    $q = $dbh->query("SHOW TABLES LIKE '$table'");
                    if ($q->rowCount() === 0) {
                        $tablesAudit[$table] = ['exists' => false, 'columns' => []];
                    } else {
                        $existingColumns = [];
                        $colQuery = $dbh->query("SHOW COLUMNS FROM `$table`");
                        while ($col = $colQuery->fetch(PDO::FETCH_ASSOC)) {
                            $existingColumns[] = strtolower($col['Field']);
                        }
                        
                        $colAudit = [];
                        foreach ($columns as $c) {
                            $colAudit[$c] = in_array(strtolower($c), $existingColumns);
                        }
                        $tablesAudit[$table] = ['exists' => true, 'columns' => $colAudit];
                    }
                } catch (Exception $e) {
                    $tablesAudit[$table] = ['exists' => false, 'error' => $e->getMessage()];
                }
            }

            // Migration Status
            try {
                require_once dirname(__DIR__) . '/core/MigrationManager.php';
                $allMigrations = MigrationManager::getMigrations();
                
                $executed = [];
                $q = $dbh->query("SHOW TABLES LIKE 'migrations'");
                if ($q->rowCount() > 0) {
                    $stmt = $dbh->query("SELECT migration FROM migrations");
                    $executed = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
                }
                
                foreach ($allMigrations as $name => $sql) {
                    $migrationStatus[$name] = in_array($name, $executed);
                }
            } catch (Exception $e) {
                $migrationStatus['error'] = $e->getMessage();
            }

        } catch (Exception $e) {
            $dbStatus = 'Failed';
            $dbError = $e->getMessage();
        }

        // Tail logs
        $appErrors = $this->tailLog(dirname(dirname(__DIR__)) . '/app_errors.log', 40);
        $syncErrors = $this->tailLog(dirname(dirname(__DIR__)) . '/sync_errors.log', 40);

        // Render Page
        $this->renderDiagnosticsHtml($dbStatus, $dbError, $tablesAudit, $migrationStatus, $appErrors, $syncErrors);
    }

    public function get_db_stats() {
        if (!$this->checkAuth()) {
            http_response_code(403);
            echo "Unauthorized";
            exit;
        }
        header('Content-Type: text/plain');
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $dbh = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            echo "=== TABLE COUNTS ===\n";
            $tables = ['items', 'customers', 'invoices', 'customer_payments', 'item_categories', 'mca_areas', 'rep_daily_routes', 'invoice_items'];
            foreach ($tables as $t) {
                try {
                    $q = $dbh->query("SELECT COUNT(*) FROM `$t`");
                    echo "$t: " . $q->fetchColumn() . " rows\n";
                } catch (Exception $ex) {
                    echo "$t: ERROR - " . $ex->getMessage() . "\n";
                }
            }
            
            // Check items columns
            echo "\n=== ITEMS COLUMNS ===\n";
            try {
                $q = $dbh->query("SHOW COLUMNS FROM items");
                while ($col = $q->fetch(PDO::FETCH_ASSOC)) {
                    echo $col['Field'] . " (" . $col['Type'] . ")\n";
                }
            } catch (Exception $ex) {
                echo "Error: " . $ex->getMessage() . "\n";
            }
            
            // Check sample items row size
            echo "\n=== ITEMS SAMPLE DATA SIZE ===\n";
            try {
                $q = $dbh->query("SELECT * FROM items LIMIT 5");
                $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $index => $r) {
                    echo "Row $index: " . strlen(serialize($r)) . " bytes\n";
                }
            } catch (Exception $ex) {
                echo "Error: " . $ex->getMessage() . "\n";
            }
        } catch (Exception $e) {
            echo "Connection Error: " . $e->getMessage() . "\n";
        }
        exit;
    }

    public function fix() {
        if (!$this->checkAuth()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $report = [];
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $dbh = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // 1. Ensure migrations table exists
            $dbh->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) UNIQUE NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $report[] = "Ensured 'migrations' table exists.";

            // 2. Add columns manually if missing
            $missingColumnsFixes = [
                'customers' => [
                    'credit_limit' => "ALTER TABLE customers ADD COLUMN credit_limit DECIMAL(15,2) DEFAULT 0.00",
                    'customer_type' => "ALTER TABLE customers ADD COLUMN customer_type VARCHAR(50) DEFAULT 'Standard'",
                    'notes' => "ALTER TABLE customers ADD COLUMN notes TEXT NULL",
                    'opening_balance' => "ALTER TABLE customers ADD COLUMN opening_balance DECIMAL(15,2) DEFAULT 0.00",
                    'uuid' => "ALTER TABLE customers ADD COLUMN uuid VARCHAR(255) NULL",
                    'status' => "ALTER TABLE customers ADD COLUMN status VARCHAR(20) DEFAULT 'active'",
                    'updated_at' => "ALTER TABLE customers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
                ],
                'products' => [
                    'sku' => "ALTER TABLE products ADD COLUMN sku VARCHAR(100) NULL",
                    'sample_code' => "ALTER TABLE products ADD COLUMN sample_code VARCHAR(100) NULL",
                    'variations_json' => "ALTER TABLE products ADD COLUMN variations_json TEXT NULL",
                    'brand' => "ALTER TABLE products ADD COLUMN brand VARCHAR(100) NULL",
                    'description' => "ALTER TABLE products ADD COLUMN description TEXT NULL",
                    'status' => "ALTER TABLE products ADD COLUMN status VARCHAR(20) DEFAULT 'active'"
                ],
                'categories' => [
                    'status' => "ALTER TABLE categories ADD COLUMN status VARCHAR(20) DEFAULT 'active'"
                ],
                'server_routes' => [
                    'main_area_id' => "ALTER TABLE server_routes ADD COLUMN main_area_id INT DEFAULT 0",
                    'status' => "ALTER TABLE server_routes ADD COLUMN status VARCHAR(20) DEFAULT 'active'"
                ],
                'customer_payments' => [
                    'status' => "ALTER TABLE customer_payments ADD COLUMN status VARCHAR(20) DEFAULT 'Active'"
                ]
            ];

            foreach ($missingColumnsFixes as $table => $cols) {
                // Check existing columns
                $existing = [];
                $q = $dbh->query("SHOW COLUMNS FROM `$table`");
                while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                    $existing[] = strtolower($row['Field']);
                }

                foreach ($cols as $colName => $alterSql) {
                    if (!in_array(strtolower($colName), $existing)) {
                        try {
                            $dbh->exec($alterSql);
                            $report[] = "Successfully added column `$colName` to `$table`.";
                        } catch (Exception $colEx) {
                            $report[] = "Failed adding column `$colName` to `$table`: " . $colEx->getMessage();
                        }
                    }
                }
            }

            // 3. Run any remaining migrations through MigrationManager
            require_once dirname(__DIR__) . '/core/MigrationManager.php';
            MigrationManager::run($dbh);
            $report[] = "Executed MigrationManager::run(). All migrations completed/checked.";

            echo json_encode(['success' => true, 'report' => $report]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'report' => $report]);
        }
        exit;
    }

    public function clear_logs() {
        if (!$this->checkAuth()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $appLog = dirname(dirname(__DIR__)) . '/app_errors.log';
            $syncLog = dirname(dirname(__DIR__)) . '/sync_errors.log';

            if (file_exists($appLog)) @file_put_contents($appLog, "");
            if (file_exists($syncLog)) @file_put_contents($syncLog, "");

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function tailLog($filepath, $lines = 50) {
        if (!file_exists($filepath)) {
            return "Log file does not exist yet.";
        }
        $data = file_get_contents($filepath);
        if (empty($data)) {
            return "Log file is empty.";
        }
        $arr = explode("\n", trim($data));
        $arr = array_slice($arr, -$lines);
        return implode("\n", $arr);
    }

    private function renderDiagnosticsHtml($dbStatus, $dbError, $tablesAudit, $migrationStatus, $appErrors, $syncErrors) {
        $secretParam = isset($_GET['secret']) ? '?secret=' . htmlspecialchars($_GET['secret']) : '';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>System & Database Diagnostics</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
            <style>
                :root {
                    --bg-main: #0f0f16;
                    --bg-card: #181824;
                    --bg-input: #222235;
                    --text-main: #f1f1f7;
                    --text-muted: #8e8eaf;
                    --accent-primary: #5856d6;
                    --accent-success: #34c759;
                    --accent-warning: #ff9500;
                    --accent-danger: #ff3b30;
                    --accent-info: #007aff;
                    --border-color: #2e2e42;
                }
                body {
                    background-color: var(--bg-main);
                    color: var(--text-main);
                    font-family: 'Outfit', sans-serif;
                    margin: 0;
                    padding: 40px 20px;
                    box-sizing: border-box;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 40px;
                    border-bottom: 1px solid var(--border-color);
                    padding-bottom: 20px;
                }
                h1 {
                    font-weight: 700;
                    font-size: 2.2rem;
                    margin: 0;
                    background: linear-gradient(135deg, #ff2d55, #5856d6);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                .btn {
                    font-family: 'Outfit', sans-serif;
                    font-weight: 600;
                    padding: 12px 24px;
                    border-radius: 8px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-size: 0.95rem;
                    display: inline-block;
                    text-decoration: none;
                    text-align: center;
                    box-sizing: border-box;
                }
                .btn-primary {
                    background: linear-gradient(135deg, #5856d6, #007aff);
                    color: white;
                    box-shadow: 0 4px 15px rgba(88, 86, 214, 0.4);
                }
                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(88, 86, 214, 0.6);
                }
                .btn-danger {
                    background: transparent;
                    color: var(--accent-danger);
                    border: 1px solid var(--accent-danger);
                }
                .btn-danger:hover {
                    background: var(--accent-danger);
                    color: white;
                }
                .btn-secondary {
                    background: var(--bg-input);
                    color: var(--text-main);
                    border: 1px solid var(--border-color);
                }
                .btn-secondary:hover {
                    background: var(--border-color);
                }
                .grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 30px;
                    margin-bottom: 30px;
                }
                @media (max-width: 900px) {
                    .grid {
                        grid-template-columns: 1fr;
                    }
                }
                .card {
                    background-color: var(--bg-card);
                    border-radius: 12px;
                    border: 1px solid var(--border-color);
                    padding: 25px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                }
                .card-title {
                    font-size: 1.3rem;
                    font-weight: 600;
                    margin-top: 0;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .badge {
                    font-size: 0.8rem;
                    font-weight: 700;
                    padding: 5px 12px;
                    border-radius: 20px;
                    text-transform: uppercase;
                }
                .badge-success { background-color: rgba(52, 199, 89, 0.2); color: var(--accent-success); }
                .badge-danger { background-color: rgba(255, 59, 48, 0.2); color: var(--accent-danger); }
                .badge-warning { background-color: rgba(255, 149, 0, 0.2); color: var(--accent-warning); }
                
                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .info-table td, .info-table th {
                    padding: 12px 15px;
                    text-align: left;
                    border-bottom: 1px solid var(--border-color);
                }
                .info-table tr:last-child td {
                    border-bottom: none;
                }
                .col-pill {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 4px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    margin: 3px;
                }
                .col-present { background-color: rgba(52, 199, 89, 0.15); color: #34c759; }
                .col-missing { background-color: rgba(255, 59, 48, 0.15); color: #ff3b30; }

                .log-viewer {
                    background-color: #0c0c12;
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                    padding: 15px;
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 0.85rem;
                    line-height: 1.5;
                    color: #a9b1d6;
                    max-height: 350px;
                    overflow-y: auto;
                    white-space: pre-wrap;
                }
                .migration-list {
                    max-height: 400px;
                    overflow-y: auto;
                }
                .migration-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 15px;
                    border-bottom: 1px solid var(--border-color);
                }
                .migration-item:last-child {
                    border-bottom: none;
                }
                .action-bar {
                    display: flex;
                    gap: 15px;
                    margin-bottom: 30px;
                }
                .overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    z-index: 1000;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                }
                .spinner {
                    width: 50px;
                    height: 50px;
                    border: 5px solid var(--border-color);
                    border-top: 5px solid var(--accent-primary);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-bottom: 20px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <header>
                    <div>
                        <h1>Curtiss ERP Diagnostics</h1>
                        <p style="color: var(--text-muted); margin-top: 5px; margin-bottom: 0;">Real-time database schema alignment and error trace analysis</p>
                    </div>
                    <div class="action-bar" style="display: flex; gap: 10px;">
                        <a href="<?php echo APP_URL; ?>/diagnostics/anomalies<?php echo $secretParam; ?>" class="btn btn-secondary">Data Anomaly Report</a>
                        <button onclick="runFix()" class="btn btn-primary">Fix Database & Run Migrations</button>
                        <button onclick="clearLogs()" class="btn btn-danger">Clear Log Files</button>
                    </div>
                </header>

                <div class="grid">
                    <!-- DB Conn Card -->
                    <div class="card">
                        <div class="card-title">
                            Database Status
                            <span class="badge <?php echo $dbStatus === 'Connected' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $dbStatus; ?>
                            </span>
                        </div>
                        <table class="info-table">
                            <tr>
                                <td><strong>Database Host</strong></td>
                                <td><code><?php echo htmlspecialchars(DB_HOST); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Database Name</strong></td>
                                <td><code><?php echo htmlspecialchars(DB_NAME); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>User</strong></td>
                                <td><code><?php echo htmlspecialchars(DB_USER); ?></code></td>
                            </tr>
                            <?php if ($dbError): ?>
                                <tr>
                                    <td colspan="2" style="color: var(--accent-danger); font-size: 0.9rem;">
                                        <strong>Connection Error:</strong><br>
                                        <?php echo htmlspecialchars($dbError); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Migration Status Card -->
                    <div class="card">
                        <div class="card-title">
                            Migration Log Summary
                        </div>
                        <div class="migration-list">
                            <?php if (isset($migrationStatus['error'])): ?>
                                <p style="color: var(--accent-danger);"><?php echo htmlspecialchars($migrationStatus['error']); ?></p>
                            <?php else: ?>
                                <?php foreach ($migrationStatus as $mName => $status): ?>
                                    <div class="migration-item">
                                        <span style="font-size: 0.9rem; font-family: monospace;"><?php echo htmlspecialchars($mName); ?></span>
                                        <span class="badge <?php echo $status ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $status ? 'Executed' : 'Pending'; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Table Audits -->
                <div class="card" style="margin-bottom: 30px;">
                    <div class="card-title">Database Table & Column Audit</div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                        <?php foreach ($tablesAudit as $tbl => $audit): ?>
                            <div style="background: var(--bg-input); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <div style="font-weight: 600; margin-bottom: 10px; display: flex; justify-content: space-between;">
                                    <code><?php echo htmlspecialchars($tbl); ?></code>
                                    <span class="badge <?php echo $audit['exists'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $audit['exists'] ? 'EXISTS' : 'MISSING'; ?>
                                    </span>
                                </div>
                                <?php if ($audit['exists']): ?>
                                    <div style="margin-top: 10px;">
                                        <?php foreach ($audit['columns'] as $col => $present): ?>
                                            <span class="col-pill <?php echo $present ? 'col-present' : 'col-missing'; ?>">
                                                <?php echo htmlspecialchars($col); ?> <?php echo $present ? '✓' : '✗'; ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif (isset($audit['error'])): ?>
                                    <p style="font-size: 0.8rem; color: var(--accent-danger);"><?php echo htmlspecialchars($audit['error']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Logs Grid -->
                <div class="grid">
                    <div class="card">
                        <div class="card-title">Recent App Errors (app_errors.log)</div>
                        <div class="log-viewer"><?php echo htmlspecialchars($appErrors); ?></div>
                    </div>
                    <div class="card">
                        <div class="card-title">Recent Sync Errors (sync_errors.log)</div>
                        <div class="log-viewer"><?php echo htmlspecialchars($syncErrors); ?></div>
                    </div>
                </div>
            </div>

            <!-- Loading overlay -->
            <div id="loader" class="overlay">
                <div class="spinner"></div>
                <h3 id="loader-title">Executing Diagnostics Fix...</h3>
                <p style="color: var(--text-muted)">Please wait while SQL migrations are verified and executed.</p>
            </div>

            <script>
                function runFix() {
                    const loader = document.getElementById('loader');
                    const loaderTitle = document.getElementById('loader-title');
                    loaderTitle.innerText = "Reconciling Database Schema...";
                    loader.style.display = 'flex';

                    fetch('<?php echo APP_URL; ?>/diagnostics/fix<?php echo $secretParam; ?>', {
                        method: 'POST'
                    })
                    .then(res => res.json())
                    .then(data => {
                        loader.style.display = 'none';
                        if (data.success) {
                            alert("Database Schema Updated successfully!\n\nDetails:\n" + data.report.join("\n"));
                            window.location.reload();
                        } else {
                            alert("Error running DB fix:\n" + data.message + "\n\nDetails:\n" + (data.report ? data.report.join("\n") : ''));
                        }
                    })
                    .catch(err => {
                        loader.style.display = 'none';
                        alert("Network error: " + err.message);
                    });
                }

                function clearLogs() {
                    if (confirm("Are you sure you want to clear app_errors.log and sync_errors.log?")) {
                        fetch('<?php echo APP_URL; ?>/diagnostics/clear_logs<?php echo $secretParam; ?>', {
                            method: 'POST'
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert("Logs cleared successfully.");
                                window.location.reload();
                            } else {
                                alert("Error clearing logs: " + data.message);
                            }
                        });
                    }
                }
            </script>
        </body>
        </html>
        <?php
    }

    public function anomalies() {
        if (!$this->checkAuth()) {
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><title>Access Denied</title><style>body{font-family:sans-serif;background:#1e1e2e;color:#cdd6f4;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}div{background:#313244;padding:30px;border-radius:12px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.3);}</style></head><body><div><h2>Access Denied</h2><p>Provide the debug secret key in the URL (e.g., <code>?secret=curtiss_debug_123</code>) or login as admin.</p></div></body></html>";
            exit;
        }

        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ];
            $dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // 1. Orphaned Invoices
            $orphInvQ = $dbh->query("
                SELECT i.id, i.invoice_number, i.customer_id, i.invoice_date, i.total_amount, i.status 
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                WHERE c.id IS NULL
                ORDER BY i.id DESC
            ");
            $orphInvoices = $orphInvQ->fetchAll();

            // Try to resolve customer names from audit logs for orphaned invoices
            foreach ($orphInvoices as $inv) {
                $inv->resolved_name = 'Unknown (ID: ' . $inv->customer_id . ')';
                $stmt = $dbh->prepare("
                    SELECT description FROM audit_logs 
                    WHERE module = 'Customer' AND record_id = :id AND action = 'Add Customer' 
                    LIMIT 1
                ");
                $stmt->execute([':id' => $inv->customer_id]);
                $log = $stmt->fetch();
                if ($log) {
                    $inv->resolved_name = str_replace('Registered new customer profile via mobile sync: ', '', $log->description);
                    $inv->resolved_name = str_replace('Registered new customer profile: ', '', $inv->resolved_name);
                } else {
                    // Try update log
                    $stmt2 = $dbh->prepare("
                        SELECT description FROM audit_logs 
                        WHERE module = 'Customer' AND record_id = :id AND action = 'Update Customer' 
                        LIMIT 1
                    ");
                    $stmt2->execute([':id' => $inv->customer_id]);
                    $log2 = $stmt2->fetch();
                    if ($log2) {
                        $inv->resolved_name = str_replace('Updated customer profile via mobile sync: ', '', $log2->description);
                        $inv->resolved_name = str_replace('Updated profile details for Customer ID ' . $inv->customer_id . ' (', '', $inv->resolved_name);
                        $inv->resolved_name = rtrim($inv->resolved_name, ')');
                    }
                }
            }

            // 2. Orphaned Sales Orders
            $orphSoQ = $dbh->query("
                SELECT so.id, so.order_number, so.customer_id, so.customer_name, so.order_date, so.grand_total, so.status 
                FROM sales_orders so 
                LEFT JOIN customers c ON so.customer_id = c.id 
                WHERE c.id IS NULL
                ORDER BY so.id DESC
            ");
            $orphSalesOrders = $orphSoQ->fetchAll();

            // 3. Orphaned Customer Payments
            $orphPmtQ = $dbh->query("
                SELECT cp.id, cp.customer_id, cp.amount, cp.payment_date, cp.payment_method, cp.status 
                FROM customer_payments cp 
                LEFT JOIN customers c ON cp.customer_id = c.id 
                WHERE c.id IS NULL
                ORDER BY cp.id DESC
            ");
            $orphPayments = $orphPmtQ->fetchAll();
            foreach ($orphPayments as $pmt) {
                $pmt->resolved_name = 'Unknown (ID: ' . $pmt->customer_id . ')';
                $stmt = $dbh->prepare("
                    SELECT description FROM audit_logs 
                    WHERE module = 'Customer' AND record_id = :id AND action = 'Add Customer' 
                    LIMIT 1
                ");
                $stmt->execute([':id' => $pmt->customer_id]);
                $log = $stmt->fetch();
                if ($log) {
                    $pmt->resolved_name = str_replace('Registered new customer profile via mobile sync: ', '', $log->description);
                    $pmt->resolved_name = str_replace('Registered new customer profile: ', '', $pmt->resolved_name);
                }
            }

            // 4. Orphaned Cheques
            $orphChqQ = $dbh->query("
                SELECT ch.id, ch.customer_id, ch.amount, ch.cheque_number, ch.banking_date, ch.status 
                FROM cheques ch 
                LEFT JOIN customers c ON ch.customer_id = c.id 
                WHERE ch.customer_id IS NOT NULL AND c.id IS NULL
                ORDER BY ch.id DESC
            ");
            $orphCheques = $orphChqQ->fetchAll();

            // 5. Orphaned Credit Notes
            $orphCnQ = $dbh->query("
                SELECT cn.id, cn.customer_id, cn.total_amount, cn.created_at, cn.status 
                FROM credit_notes cn 
                LEFT JOIN customers c ON cn.customer_id = c.id 
                WHERE c.id IS NULL
                ORDER BY cn.id DESC
            ");
            $orphCreditNotes = $orphCnQ->fetchAll();

            // 6. Orphaned Pending Collections
            $orphPcQ = $dbh->query("
                SELECT pc.id, pc.customer_id, pc.amount, pc.payment_method, pc.created_at, pc.status 
                FROM pending_collections pc 
                LEFT JOIN customers c ON pc.customer_id = c.id 
                WHERE c.id IS NULL
                ORDER BY pc.id DESC
            ");
            $orphCollections = $orphPcQ->fetchAll();

            // 7. General anomalies
            $otherAnomalies = [];
            
            // Invoices with empty invoice_number
            $badInvNumQ = $dbh->query("SELECT id, customer_id, total_amount FROM invoices WHERE invoice_number IS NULL OR invoice_number = ''");
            while ($r = $badInvNumQ->fetch()) {
                $otherAnomalies[] = [
                    'type' => 'Invoice',
                    'id' => $r->id,
                    'issue' => 'Missing Invoice Number (Customer ID: ' . $r->customer_id . ', Amount: ' . $r->total_amount . ')'
                ];
            }

            // Sales orders with empty order_number
            $badSoNumQ = $dbh->query("SELECT id, customer_name, grand_total FROM sales_orders WHERE order_number IS NULL OR order_number = ''");
            while ($r = $badSoNumQ->fetch()) {
                $otherAnomalies[] = [
                    'type' => 'Sales Order',
                    'id' => $r->id,
                    'issue' => 'Missing Order Number (Customer: ' . $r->customer_name . ', Amount: ' . $r->grand_total . ')'
                ];
            }

            // Invoices with negative or zero total_amount
            $negInvQ = $dbh->query("SELECT id, invoice_number, total_amount FROM invoices WHERE total_amount <= 0");
            while ($r = $negInvQ->fetch()) {
                $otherAnomalies[] = [
                    'type' => 'Invoice',
                    'id' => $r->id,
                    'issue' => 'Zero or Negative Amount for Invoice ' . $r->invoice_number . ' (Amount: ' . $r->total_amount . ')'
                ];
            }

        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }

        $this->renderAnomaliesHtml($orphInvoices, $orphSalesOrders, $orphPayments, $orphCheques, $orphCreditNotes, $orphCollections, $otherAnomalies);
    }

    private function renderAnomaliesHtml($orphInvoices, $orphSalesOrders, $orphPayments, $orphCheques, $orphCreditNotes, $orphCollections, $otherAnomalies) {
        $secretParam = isset($_GET['secret']) ? '?secret=' . htmlspecialchars($_GET['secret']) : '';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Data Integrity & Anomaly Report</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
            <style>
                :root {
                    --bg-main: #0f0f16;
                    --bg-card: #181824;
                    --bg-input: #222235;
                    --text-main: #f1f1f7;
                    --text-muted: #8e8eaf;
                    --accent-primary: #5856d6;
                    --accent-success: #34c759;
                    --accent-warning: #ff9500;
                    --accent-danger: #ff3b30;
                    --accent-info: #007aff;
                    --border-color: #2e2e42;
                }
                body {
                    background-color: var(--bg-main);
                    color: var(--text-main);
                    font-family: 'Outfit', sans-serif;
                    margin: 0;
                    padding: 40px 20px;
                    box-sizing: border-box;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 40px;
                    border-bottom: 1px solid var(--border-color);
                    padding-bottom: 20px;
                }
                h1 {
                    font-weight: 700;
                    font-size: 2.2rem;
                    margin: 0;
                    background: linear-gradient(135deg, #ff9500, #ff3b30);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                .btn {
                    font-family: 'Outfit', sans-serif;
                    font-weight: 600;
                    padding: 12px 24px;
                    border-radius: 8px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-size: 0.95rem;
                    display: inline-block;
                    text-decoration: none;
                    text-align: center;
                    box-sizing: border-box;
                }
                .btn-primary {
                    background: linear-gradient(135deg, #ff9500, #ff3b30);
                    color: white;
                    box-shadow: 0 4px 15px rgba(255, 149, 0, 0.4);
                }
                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(255, 149, 0, 0.6);
                }
                .btn-secondary {
                    background: var(--bg-input);
                    color: var(--text-main);
                    border: 1px solid var(--border-color);
                }
                .btn-secondary:hover {
                    background: var(--border-color);
                }
                .grid-stats {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                    gap: 20px;
                    margin-bottom: 40px;
                }
                .stat-card {
                    background-color: var(--bg-card);
                    border: 1px solid var(--border-color);
                    border-radius: 12px;
                    padding: 20px;
                    text-align: center;
                    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
                }
                .stat-num {
                    font-size: 2.2rem;
                    font-weight: 700;
                    color: var(--accent-danger);
                    margin-bottom: 5px;
                }
                .stat-num.zero {
                    color: var(--accent-success);
                }
                .stat-label {
                    font-size: 0.9rem;
                    color: var(--text-muted);
                    font-weight: 600;
                }
                .card {
                    background-color: var(--bg-card);
                    border-radius: 12px;
                    border: 1px solid var(--border-color);
                    padding: 25px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                    margin-bottom: 40px;
                }
                .card-title {
                    font-size: 1.3rem;
                    font-weight: 600;
                    margin-top: 0;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .tabs-header {
                    display: flex;
                    gap: 10px;
                    border-bottom: 2px solid var(--border-color);
                    padding-bottom: 12px;
                    margin-bottom: 25px;
                    overflow-x: auto;
                }
                .tab-btn {
                    background: transparent;
                    color: var(--text-muted);
                    border: none;
                    font-family: 'Outfit', sans-serif;
                    font-size: 1rem;
                    font-weight: 600;
                    padding: 10px 18px;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                }
                .tab-btn:hover {
                    color: var(--text-main);
                    background: var(--bg-input);
                }
                .tab-btn.active {
                    color: white;
                    background: var(--accent-primary);
                }
                .tab-panel {
                    display: none;
                }
                .tab-panel.active {
                    display: block;
                }
                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .info-table th {
                    background-color: var(--bg-input);
                    color: var(--text-muted);
                    font-weight: 600;
                    font-size: 0.9rem;
                    padding: 12px 15px;
                    text-align: left;
                    border-bottom: 2px solid var(--border-color);
                }
                .info-table td {
                    padding: 12px 15px;
                    border-bottom: 1px solid var(--border-color);
                    font-size: 0.95rem;
                }
                .info-table tr:hover td {
                    background-color: rgba(255, 255, 255, 0.02);
                }
                .badge {
                    font-size: 0.8rem;
                    font-weight: 700;
                    padding: 4px 10px;
                    border-radius: 20px;
                }
                .badge-danger { background-color: rgba(255, 59, 48, 0.2); color: var(--accent-danger); }
                .badge-info { background-color: rgba(0, 122, 255, 0.2); color: var(--accent-info); }
                .code-id {
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 0.85rem;
                    background: var(--bg-input);
                    padding: 2px 6px;
                    border-radius: 4px;
                    color: var(--accent-warning);
                }
                .alert-banner {
                    background: rgba(255, 149, 0, 0.15);
                    border: 1px solid var(--accent-warning);
                    border-radius: 8px;
                    padding: 15px 20px;
                    margin-bottom: 30px;
                    font-size: 0.95rem;
                    line-height: 1.5;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <header>
                    <div>
                        <h1>Data Integrity & Anomaly Report</h1>
                        <p style="color: var(--text-muted); margin-top: 5px; margin-bottom: 0;">Identifies transaction data orphaned or broken due to customer list changes</p>
                    </div>
                    <div>
                        <a href="<?php echo APP_URL; ?>/diagnostics<?php echo $secretParam; ?>" class="btn btn-secondary">← Back to Diagnostics</a>
                    </div>
                </header>

                <div class="alert-banner">
                    <strong>Notice:</strong> This utility highlights database records that are currently orphaned (referencing customer IDs that do not exist in the <code>customers</code> table). This typically happens after the customers table is cleared and imported without preserving original IDs. You can use the SQL recovery script provided in your workspace to remap these IDs to the correct records.
                </div>

                <div class="grid-stats">
                    <div class="stat-card">
                        <div class="stat-num <?php echo count($orphInvoices) === 0 ? 'zero' : ''; ?>"><?php echo count($orphInvoices); ?></div>
                        <div class="stat-label">Orphaned Invoices</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num <?php echo count($orphSalesOrders) === 0 ? 'zero' : ''; ?>"><?php echo count($orphSalesOrders); ?></div>
                        <div class="stat-label">Orphaned Sales Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num <?php echo count($orphPayments) === 0 ? 'zero' : ''; ?>"><?php echo count($orphPayments); ?></div>
                        <div class="stat-label">Orphaned Payments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num <?php echo count($orphCheques) === 0 ? 'zero' : ''; ?>"><?php echo count($orphCheques); ?></div>
                        <div class="stat-label">Orphaned Cheques</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num <?php echo count($orphCreditNotes) === 0 ? 'zero' : ''; ?>"><?php echo count($orphCreditNotes); ?></div>
                        <div class="stat-label">Orphaned Credit Notes</div>
                    </div>
                </div>

                <div class="card">
                    <div class="tabs-header">
                        <button class="tab-btn active" onclick="showTab('invoices')">Orphaned Invoices (<?php echo count($orphInvoices); ?>)</button>
                        <button class="tab-btn" onclick="showTab('sales-orders')">Orphaned Sales Orders (<?php echo count($orphSalesOrders); ?>)</button>
                        <button class="tab-btn" onclick="showTab('payments')">Payments & Cheques (<?php echo count($orphPayments) + count($orphCheques); ?>)</button>
                        <button class="tab-btn" onclick="showTab('credits')">Credit Notes & Collections (<?php echo count($orphCreditNotes) + count($orphCollections); ?>)</button>
                        <button class="tab-btn" onclick="showTab('other')">Other Anomalies (<?php echo count($otherAnomalies); ?>)</button>
                    </div>

                    <!-- Invoices Panel -->
                    <div id="tab-invoices" class="tab-panel active">
                        <h3>Invoices with Missing Customers</h3>
                        <?php if (empty($orphInvoices)): ?>
                            <p style="color: var(--accent-success); font-weight: 600;">No orphaned invoices found! All invoices are linked to valid customer profiles.</p>
                        <?php else: ?>
                            <table class="info-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Invoice Number</th>
                                        <th>Old Customer ID</th>
                                        <th>Resolved Name (from logs)</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphInvoices as $inv): ?>
                                        <tr>
                                            <td><span class="code-id"><?php echo $inv->id; ?></span></td>
                                            <td><strong><?php echo htmlspecialchars($inv->invoice_number); ?></strong></td>
                                            <td><span class="code-id"><?php echo $inv->customer_id; ?></span></td>
                                            <td><em style="color: var(--accent-warning);"><?php echo htmlspecialchars($inv->resolved_name); ?></em></td>
                                            <td><?php echo htmlspecialchars($inv->invoice_date); ?></td>
                                            <td><strong>LKR <?php echo number_format($inv->total_amount, 2); ?></strong></td>
                                            <td><span class="badge badge-danger"><?php echo htmlspecialchars($inv->status); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Sales Orders Panel -->
                    <div id="tab-sales-orders" class="tab-panel">
                        <h3>Sales Orders with Missing Customers</h3>
                        <?php if (empty($orphSalesOrders)): ?>
                            <p style="color: var(--accent-success); font-weight: 600;">No orphaned sales orders found! All orders are linked to valid customer profiles.</p>
                        <?php else: ?>
                            <table class="info-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Order Number</th>
                                        <th>Old Customer ID</th>
                                        <th>Customer Shop Name</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphSalesOrders as $so): ?>
                                        <tr>
                                            <td><span class="code-id"><?php echo $so->id; ?></span></td>
                                            <td><strong><?php echo htmlspecialchars($so->order_number); ?></strong></td>
                                            <td><span class="code-id"><?php echo $so->customer_id; ?></span></td>
                                            <td><span style="color: var(--accent-success); font-weight: 600;"><?php echo htmlspecialchars($so->customer_name); ?></span></td>
                                            <td><?php echo htmlspecialchars($so->order_date); ?></td>
                                            <td><strong>LKR <?php echo number_format($so->grand_total, 2); ?></strong></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($so->status); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Payments & Cheques Panel -->
                    <div id="tab-payments" class="tab-panel">
                        <h3>Customer Payments with Missing Customers</h3>
                        <?php if (empty($orphPayments)): ?>
                            <p style="color: var(--accent-success); font-weight: 600; margin-bottom: 25px;">No orphaned customer payments found.</p>
                        <?php else: ?>
                            <table class="info-table" style="margin-bottom: 30px;">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Old Customer ID</th>
                                        <th>Resolved Name</th>
                                        <th>Payment Method</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphPayments as $pmt): ?>
                                        <tr>
                                            <td><span class="code-id"><?php echo $pmt->id; ?></span></td>
                                            <td><span class="code-id"><?php echo $pmt->customer_id; ?></span></td>
                                            <td><em style="color: var(--accent-warning);"><?php echo htmlspecialchars($pmt->resolved_name); ?></em></td>
                                            <td><?php echo htmlspecialchars($pmt->payment_method); ?></td>
                                            <td><?php echo htmlspecialchars($pmt->payment_date); ?></td>
                                            <td><strong>LKR <?php echo number_format($pmt->amount, 2); ?></strong></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($pmt->status); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <h3>Cheques with Missing Customers</h3>
                        <?php if (empty($orphCheques)): ?>
                            <p style="color: var(--accent-success); font-weight: 600;">No orphaned cheques found.</p>
                        <?php else: ?>
                            <table class="info-table">
                                <thead>
                                    <tr>
                                        <th>Cheque ID</th>
                                        <th>Cheque Number</th>
                                        <th>Old Customer ID</th>
                                        <th>Banking Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphCheques as $chq): ?>
                                        <tr>
                                            <td><span class="code-id"><?php echo $chq->id; ?></span></td>
                                            <td><strong><?php echo htmlspecialchars($chq->cheque_number); ?></strong></td>
                                            <td><span class="code-id"><?php echo $chq->customer_id; ?></span></td>
                                            <td><?php echo htmlspecialchars($chq->banking_date); ?></td>
                                            <td><strong>LKR <?php echo number_format($chq->amount, 2); ?></strong></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($chq->status); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Credit Notes & Collections Panel -->
                    <div id="tab-credits" class="tab-panel">
                        <h3>Credit Notes with Missing Customers</h3>
                        <?php if (empty($orphCreditNotes)): ?>
                            <p style="color: var(--accent-success); font-weight: 600; margin-bottom: 25px;">No orphaned credit notes found.</p>
                        <?php else: ?>
                            <table class="info-table" style="margin-bottom: 30px;">
                                <thead>
                                    <tr>
                                        <th>Credit Note ID</th>
                                        <th>Old Customer ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphCreditNotes as $cn): ?>
                                        <tr>
                                            <td><span class="code-id"><?php echo $cn->id; ?></span></td>
                                            <td><span class="code-id"><?php echo $cn->customer_id; ?></span></td>
                                            <td><?php echo htmlspecialchars($cn->created_at); ?></td>
                                            <td><strong>LKR <?php echo number_format($cn->total_amount, 2); ?></strong></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($cn->status); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <h3>Pending Collections with Missing Customers</h3>
                        <?php if (empty($orphCollections)): ?>
                            <p style="color: var(--accent-success); font-weight: 600;">No orphaned pending collections found.</p>
                        <?php else: ?>
                            <table class="info-table">
                                <thead>
                                    <tr>
                                        <th>Collection ID</th>
                                        <th>Old Customer ID</th>
                                        <th>Method</th>
                                        <th>Created At</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orphCollections as $pc): ?>
                                        <tr>
                                            <td><span class="code-id"><?php echo $pc->id; ?></span></td>
                                            <td><span class="code-id"><?php echo $pc->customer_id; ?></span></td>
                                            <td><?php echo htmlspecialchars($pc->payment_method); ?></td>
                                            <td><?php echo htmlspecialchars($pc->created_at); ?></td>
                                            <td><strong>LKR <?php echo number_format($pc->amount, 2); ?></strong></td>
                                            <td><span class="badge badge-info"><?php echo htmlspecialchars($pc->status); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Other Panel -->
                    <div id="tab-other" class="tab-panel">
                        <h3>General Data Inconsistencies</h3>
                        <?php if (empty($otherAnomalies)): ?>
                            <p style="color: var(--accent-success); font-weight: 600;">No general data anomalies found!</p>
                        <?php else: ?>
                            <table class="info-table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Record ID</th>
                                        <th>Issue Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($otherAnomalies as $anom): ?>
                                        <tr>
                                            <td><span class="badge badge-danger"><?php echo htmlspecialchars($anom['type']); ?></span></td>
                                            <td><span class="code-id"><?php echo $anom['id']; ?></span></td>
                                            <td style="color: var(--accent-warning);"><?php echo htmlspecialchars($anom['issue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <script>
                function showTab(tabId) {
                    // Hide all panels
                    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                    // Deactivate all buttons
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

                    // Show target panel
                    document.getElementById('tab-' + tabId).classList.add('active');
                    // Activate button
                    event.currentTarget.classList.add('active');
                }
            </script>
        </body>
        </html>
        <?php
    }
}
