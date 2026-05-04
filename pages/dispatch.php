<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';
requireRole(['admin', 'supervisor']); // Restricted to management

$dispatch_id = $_GET['id'] ?? null;
if (!$dispatch_id) {
    header("Location: pending_routes.php");
    exit;
}

// Fetch Dispatch Info
$stmt = $pdo->prepare("SELECT * FROM delivery_dispatches WHERE id = ?");
$stmt->execute([$dispatch_id]);
$dispatch = $stmt->fetch();

if (!$dispatch) {
    echo "Dispatch not found."; exit;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'save_dispatch') {
        $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;
        $partner_id = !empty($_POST['partner_id']) ? $_POST['partner_id'] : null;
        $vehicle_id = $_POST['vehicle_id'] ?: null;
        
        $pdo->beginTransaction();
        $upd = $pdo->prepare("UPDATE delivery_dispatches SET driver_id = ?, partner_id = ?, vehicle_id = ? WHERE id = ?");
        $upd->execute([$driver_id, $partner_id, $vehicle_id, $dispatch_id]);
        
        // Save selected collections
        $pdo->prepare("DELETE FROM dispatch_collections WHERE dispatch_id = ?")->execute([$dispatch_id]);
        if (!empty($_POST['collections'])) {
            $insCol = $pdo->prepare("INSERT INTO dispatch_collections (dispatch_id, customer_id) VALUES (?, ?)");
            foreach($_POST['collections'] as $cid) {
                $insCol->execute([$dispatch_id, $cid]);
            }
        }
        $pdo->commit();
        header("Location: dispatch.php?id=" . $dispatch_id . "&msg=saved");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'finalize_dispatch') {
        $pdo->prepare("UPDATE delivery_dispatches SET status = 'dispatched', assign_date = CURDATE() WHERE id = ?")->execute([$dispatch_id]);
        header("Location: dispatch.php?id=" . $dispatch_id . "&msg=dispatched");
        exit;
    }
}

// Data Fetching for UI
$employees = $pdo->query("SELECT id, name, role FROM employees WHERE status = 'Active'")->fetchAll();
$drivers = array_filter($employees, fn($e) => $e['role'] == 'Driver');
$helpers = array_filter($employees, fn($e) => in_array($e['role'], ['Helper', 'Partner']));

// Fetch Unified Loading Manifest
$manifestStmt = $pdo->prepare("
    SELECT p.id, p.name, p.sku, SUM(oi.quantity) as load_qty
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.dispatch_id = ?
    GROUP BY p.id, p.name, p.sku
");
$manifestStmt->execute([$dispatch_id]);
$manifest = $manifestStmt->fetchAll();

// Fetch Outstanding Customers on these routes
$customersStmt = $pdo->prepare("
    SELECT c.id, c.name, c.address, c.phone, 
           (SELECT COALESCE(SUM(total_amount - paid_amount),0) FROM orders WHERE customer_id = c.id AND payment_status != 'paid') as outstanding
    FROM customers c
    WHERE c.route_id IN (
        SELECT rs.route_id FROM dispatch_sessions ds JOIN rep_sessions rs ON ds.rep_session_id = rs.id WHERE ds.dispatch_id = ? AND rs.route_id IS NOT NULL
    )
    HAVING outstanding > 0
");
$customersStmt->execute([$dispatch_id]);
$outstanding_customers = $customersStmt->fetchAll();

$selectedColStmt = $pdo->prepare("SELECT customer_id FROM dispatch_collections WHERE dispatch_id = ?");
$selectedColStmt->execute([$dispatch_id]);
$selected_col = $selectedColStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch total orders assigned to this dispatch
$ordCount = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE dispatch_id = ?");
$ordCount->execute([$dispatch_id]);
$total_orders = $ordCount->fetchColumn();

// Fetch bound sessions
$sesStmt = $pdo->prepare("
    SELECT rs.id, u.name as rep_name, r.name as route_name 
    FROM dispatch_sessions ds
    JOIN rep_sessions rs ON ds.rep_session_id = rs.id
    JOIN users u ON rs.rep_id = u.id
    LEFT JOIN routes r ON rs.route_id = r.id
    WHERE ds.dispatch_id = ?
");
$sesStmt->execute([$dispatch_id]);
$bound_sessions = $sesStmt->fetchAll();

$is_readonly = in_array($dispatch['status'], ['dispatched', 'unloaded']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Dispatch <?php echo htmlspecialchars($dispatch['dispatch_ref']); ?> - ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .no-print, header, .navbar, .sidebar, .btn, .breadcrumb { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .container-fluid { padding: 0 !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body>
<div class="no-print">
    <?php include '../includes/header.php'; ?>
</div>

    <div class="container-fluid py-4">
        
        <!-- Print Header -->
        <div class="d-none d-print-block mb-4">
            <h2 class="fw-bold">DISPATCH MANIFEST: <?php echo $dispatch['dispatch_ref']; ?></h2>
            <div class="text-muted">Date: <?php echo date('Y-m-d'); ?> | Vehicle: <?php echo htmlspecialchars($dispatch['vehicle_id'] ?? 'N/A'); ?></div>
            <hr>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <a href="pending_routes.php" class="btn btn-light btn-sm mb-2"><i class="bi bi-arrow-left"></i> Back to Pending</a>
                <h4 class="mb-0 fw-bold">Dispatch: <?php echo htmlspecialchars($dispatch['dispatch_ref']); ?></h4>
                <div class="text-muted small mt-1">
                    Status: <span class="badge <?php echo $dispatch['status'] == 'draft' ? 'bg-warning text-dark' : 'bg-primary'; ?>"><?php echo strtoupper($dispatch['status']); ?></span>
                </div>
            </div>
            <div>
                <?php if ($dispatch['status'] == 'draft'): ?>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to finalize this dispatch? This will allow the driver to begin delivery.');">
                        <input type="hidden" name="action" value="finalize_dispatch">
                        <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-truck me-2"></i> Approve & Dispatch</button>
                    </form>
                <?php elseif ($dispatch['status'] == 'dispatched'): ?>
                    <a href="unload.php?id=<?php echo $dispatch_id; ?>" class="btn btn-danger fw-bold"><i class="bi bi-box-arrow-in-down me-2"></i> Unload & Finalize</a>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-secondary ms-2" onclick="window.print()"><i class="bi bi-printer"></i> Print Manifest</button>
            </div>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">Changes saved successfully!</div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: Details & Manifest -->
            <div class="col-md-7">
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-2">
                        <h6 class="fw-bold mb-0">Dispatch Configuration</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="save_dispatch">
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">Driver</label>
                                    <select name="driver_id" class="form-select" <?php echo $is_readonly ? 'disabled' : ''; ?>>
                                        <option value="">-- Select --</option>
                                        <?php foreach($drivers as $d): ?>
                                            <option value="<?php echo $d['id']; ?>" <?php echo $dispatch['driver_id'] == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">Helper</label>
                                    <select name="partner_id" class="form-select" <?php echo $is_readonly ? 'disabled' : ''; ?>>
                                        <option value="">-- Select --</option>
                                        <?php foreach($helpers as $h): ?>
                                            <option value="<?php echo $h['id']; ?>" <?php echo $dispatch['partner_id'] == $h['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">Vehicle No</label>
                                    <input type="text" name="vehicle_id" class="form-select" placeholder="e.g. WP-1234" value="<?php echo htmlspecialchars($dispatch['vehicle_id'] ?? ''); ?>" <?php echo $is_readonly ? 'readonly' : ''; ?>>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-3 mt-4 border-top pt-4">Credit Collections to Visit</h6>
                            <p class="text-muted small">Select customers on this route to instruct the driver to collect outstanding payments.</p>
                            
                            <div class="row g-3 mb-4">
                                <?php if(empty($outstanding_customers)): ?>
                                    <div class="col-12"><p class="text-muted">No outstanding customers on these routes.</p></div>
                                <?php else: ?>
                                    <?php foreach($outstanding_customers as $c): ?>
                                    <div class="col-md-6">
                                        <label class="d-flex align-items-center gap-3 p-3 border rounded <?php echo in_array($c['id'], $selected_col) ? 'bg-light border-primary' : ''; ?>" style="cursor: pointer;">
                                            <input type="checkbox" name="collections[]" value="<?php echo $c['id']; ?>" class="form-check-input mt-0" <?php echo in_array($c['id'], $selected_col) ? 'checked' : ''; ?> <?php echo $is_readonly ? 'disabled' : ''; ?>>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($c['name']); ?></h6>
                                                <div class="text-danger small fw-bold font-monospace mt-1">Ows: Rs <?php echo number_format($c['outstanding'], 2); ?></div>
                                            </div>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!$is_readonly): ?>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Configuration</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-2 d-flex justify-content-between">
                        <h6 class="fw-bold mb-0">Bound Sessions</h6>
                        <span class="badge bg-secondary"><?php echo $total_orders; ?> Total Invoices</span>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach($bound_sessions as $bs): ?>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span><i class="bi bi-signpost-split me-2 text-primary"></i> <?php echo htmlspecialchars($bs['route_name'] ?: 'General Orders'); ?></span>
                                    <span class="text-muted">Rep: <?php echo htmlspecialchars($bs['rep_name']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Right Column: Loading Manifest -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-uppercase letter-spacing-1">Loading Manifest</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th class="text-center">SKU</th>
                                        <th class="text-end pe-4">Load Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($manifest)): ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No items in manifest.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($manifest as $item): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td class="text-center text-muted small"><?php echo htmlspecialchars($item['sku']); ?></td>
                                            <td class="text-end pe-4 font-monospace fs-5"><?php echo $item['load_qty']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>