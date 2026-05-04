<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';
requireRole(['admin', 'supervisor']); // Restricted to management

// Handle Binding Action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'bind_sessions') {
    $session_ids = $_POST['session_ids'] ?? [];
    if (empty($session_ids)) {
        $error = "Please select at least one session to bind.";
    } else {
        try {
            $pdo->beginTransaction();
            // Generate unique dispatch reference
            $ref = 'DSP-' . date('Ymd') . '-' . rand(100, 999);
            
            $stmt = $pdo->prepare("INSERT INTO delivery_dispatches (dispatch_ref, status, created_at) VALUES (?, 'draft', NOW())");
            $stmt->execute([$ref]);
            $dispatch_id = $pdo->lastInsertId();
            
            $mapStmt = $pdo->prepare("INSERT INTO dispatch_sessions (dispatch_id, rep_session_id) VALUES (?, ?)");
            $updateOrderStmt = $pdo->prepare("UPDATE orders SET dispatch_id = ? WHERE rep_session_id = ?");
            $updateSessionStmt = $pdo->prepare("UPDATE rep_sessions SET status = 'dispatched' WHERE id = ?");

            foreach ($session_ids as $sid) {
                $mapStmt->execute([$dispatch_id, $sid]);
                $updateOrderStmt->execute([$dispatch_id, $sid]);
                $updateSessionStmt->execute([$sid]);
            }
            
            $pdo->commit();
            header("Location: dispatch.php?id=" . $dispatch_id);
            exit;
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Failed to bind sessions: " . $e->getMessage();
        }
    }
}

// Fetch Ended Sessions
$stmt = $pdo->prepare("
    SELECT rs.*, u.name as rep_name, r.name as route_name,
        (SELECT COUNT(id) FROM orders WHERE rep_session_id = rs.id) as order_count,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE rep_session_id = rs.id) as total_sales
    FROM rep_sessions rs
    JOIN users u ON rs.rep_id = u.id
    LEFT JOIN routes r ON rs.route_id = r.id
    WHERE rs.status = 'ended'
    ORDER BY rs.updated_at ASC
");
$stmt->execute();
$ended_sessions = $stmt->fetchAll();

// Get active dispatches for the sidebar list
$dispStmt = $pdo->prepare("
    SELECT id, dispatch_ref, status, DATE(created_at) as cdate 
    FROM delivery_dispatches 
    WHERE status IN ('draft', 'dispatched')
    ORDER BY id DESC
");
$dispStmt->execute();
$active_dispatches = $dispStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Routes & Dispatches - ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .session-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s;
        }
        .session-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .session-card.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .form-check-input.huge {
            width: 1.5em;
            height: 1.5em;
            cursor: pointer;
        }
        .dispatch-card {
            background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 8px;
            text-decoration: none; color: inherit; display: block;
        }
        .dispatch-card:hover { border-color: #94a3b8; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Left Side: Ended Sessions -->
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0 fw-bold">Pending Route Sessions</h4>
                    <span class="text-muted">Awaiting Dispatch</span>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (empty($ended_sessions)): ?>
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No pending sessions</h5>
                            <p class="text-muted small">All completed rep sessions have been dispatched.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" id="bindForm">
                        <input type="hidden" name="action" value="bind_sessions">
                        
                        <?php foreach($ended_sessions as $s): ?>
                        <label class="session-card" for="chk_<?php echo $s['id']; ?>">
                            <div class="flex-shrink-0">
                                <input type="checkbox" class="form-check-input huge session-checkbox" name="session_ids[]" value="<?php echo $s['id']; ?>" id="chk_<?php echo $s['id']; ?>">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($s['route_name'] ?: 'General Orders'); ?></h6>
                                <div class="text-muted small">
                                    <i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($s['rep_name']); ?> &nbsp;|&nbsp;
                                    <i class="bi bi-clock me-1"></i> <?php echo date('M d, Y h:i A', strtotime($s['updated_at'])); ?>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="fw-bold text-success font-monospace">Rs <?php echo number_format($s['total_sales'], 2); ?></div>
                                <div class="text-muted small"><?php echo $s['order_count']; ?> Orders</div>
                            </div>
                        </label>
                        <?php endforeach; ?>

                        <div class="card border-0 shadow-sm mt-4 sticky-bottom" style="bottom: 20px; z-index: 10;">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold" id="selCount">0</span> Sessions Selected
                                </div>
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnBind" disabled>
                                    Create Dispatch Manifest <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Right Side: Active Dispatches -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h6 class="fw-bold mb-0">Active Dispatches</h6>
                    </div>
                    <div class="card-body">
                        <?php if(empty($active_dispatches)): ?>
                            <p class="text-muted small text-center my-4">No active dispatches.</p>
                        <?php else: ?>
                            <?php foreach($active_dispatches as $d): ?>
                            <a href="dispatch.php?id=<?php echo $d['id']; ?>" class="dispatch-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark font-monospace"><?php echo $d['dispatch_ref']; ?></span>
                                    <?php if($d['status'] == 'draft'): ?>
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Dispatched</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted small mt-2"><i class="bi bi-calendar3 me-1"></i> <?php echo $d['cdate']; ?></div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const checkboxes = document.querySelectorAll('.session-checkbox');
        const btnBind = document.getElementById('btnBind');
        const selCount = document.getElementById('selCount');

        checkboxes.forEach(chk => {
            chk.addEventListener('change', function() {
                const card = this.closest('.session-card');
                if(this.checked) card.classList.add('selected');
                else card.classList.remove('selected');

                const checkedCount = document.querySelectorAll('.session-checkbox:checked').length;
                selCount.textContent = checkedCount;
                btnBind.disabled = checkedCount === 0;
            });
        });
    </script>
</body>
</html>
